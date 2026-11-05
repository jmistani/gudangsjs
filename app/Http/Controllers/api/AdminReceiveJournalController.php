<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ReceiveJournal;
use App\Models\StockMutation;
use App\Models\NonStockInventory;
use App\Models\JournalHead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminReceiveJournalController extends Controller
{
    private $response;

    public function __construct(ResponseHelper $response)
    {
        $this->response = $response;
    }

    private function authorizeAdmin()
    {
        $user = auth()->user();
        if (!$user || !$user->roles()->whereRaw('LOWER(name) IN (?, ?)', ['super-admin', 'super admin'])->exists()) {
            abort(403, 'Hanya Super Admin dapat mengelola penerimaan barang.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();
        $query = ReceiveJournal::with('journalhead')->orderByDesc('id');
        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(function ($q) use ($term) {
                $q->where('giver', 'like', "%{$term}%")
                  ->orWhere('receiver', 'like', "%{$term}%")
                  ->orWhereHas('journalhead', function ($head) use ($term) {
                      $head->where('code', 'like', "%{$term}%");
                  });
            });
        }
        $page = $query->paginate(min(100, max(1, (int) $request->get('perPage', 20))));
        $page->getCollection()->transform(function ($record) {
            $head = $record->journalhead;
            $stock = $head ? StockMutation::where('journal_head_id', $head->id)->get() : collect();
            $nonstock = $head ? NonStockInventory::where('journal_head_id', $head->id)->get() : collect();
            return [
                'id' => $record->id,
                'code' => optional($head)->code,
                'post_date' => $head ? Carbon::parse($head->getRawOriginal('post_date'))->format('Y-m-d') : null,
                'giver' => $record->giver,
                'receiver' => $record->receiver,
                'item_count' => $stock->count() + $nonstock->count(),
                'total' => $stock->sum(function ($x) { return abs((int) $x->getRawOriginal('total')); })
                    + $nonstock->sum(function ($x) { return abs((int) $x->getRawOriginal('subtotal')); }),
            ];
        });
        return $this->response->successResponse($page);
    }

    public function show($id)
    {
        $this->authorizeAdmin();
        $record = ReceiveJournal::with('journalhead')->findOrFail($id);
        $head = $record->journalhead;
        if (!$head) abort(409, 'Jurnal penerimaan tidak ditemukan.');

        $stock = StockMutation::with('stockable')->where('journal_head_id', $head->id)->get()
            ->map(function ($x) {
                return [
                    'type' => 'stok',
                    'id' => $x->id,
                    'name' => optional($x->stockable)->name,
                    'code' => optional($x->stockable)->code,
                    'unit' => optional($x->stockable)->unit,
                    'qty' => (float) $x->getRawOriginal('amount'),
                    'price' => (int) $x->getRawOriginal('price'),
                    'subtotal' => (int) $x->getRawOriginal('total'),
                    'memo' => $x->description,
                ];
            });

        $nonstock = NonStockInventory::where('journal_head_id', $head->id)->get()
            ->map(function ($x) {
                return [
                    'type' => 'non-stok',
                    'id' => $x->id,
                    'name' => $x->name,
                    'code' => '-',
                    'unit' => $x->unit,
                    'qty' => (float) $x->getRawOriginal('amount'),
                    'price' => (int) $x->getRawOriginal('price'),
                    'subtotal' => (int) $x->getRawOriginal('subtotal'),
                    'memo' => $x->memo,
                ];
            });

        return $this->response->successResponse([
            'id' => $record->id,
            'code' => $head->code,
            'post_date' => Carbon::parse($head->getRawOriginal('post_date'))->format('Y-m-d'),
            'giver' => $record->giver,
            'receiver' => $record->receiver,
            'items' => $stock->concat($nonstock)->values(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'giver' => ['required', 'string', 'max:191'],
            'receiver' => ['required', 'string', 'max:191'],
            'items' => ['required', 'array'],
            'items.*.type' => ['required', 'in:stok,non-stok'],
            'items.*.id' => ['required', 'integer'],
            'items.*.memo' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($id, $data) {
            $record = ReceiveJournal::findOrFail($id);
            $head = $record->journalhead;
            if (!$head) abort(409, 'Jurnal penerimaan tidak ditemukan.');
            $stock = StockMutation::where('journal_head_id', $head->id)->lockForUpdate()->get();
            $nonstock = NonStockInventory::where('journal_head_id', $head->id)->lockForUpdate()->get();
            $expected = $stock->map(function ($x) { return 'stok:'.$x->id; })
                ->concat($nonstock->map(function ($x) { return 'non-stok:'.$x->id; }))->sort()->values()->all();
            $given = collect($data['items'])->map(function ($x) { return $x['type'].':'.$x['id']; })->sort()->values()->all();
            if ($expected !== $given) {
                throw ValidationException::withMessages(['items' => 'Detail barang telah berubah. Muat ulang halaman.']);
            }
            $record->giver = $data['giver'];
            $record->receiver = $data['receiver'];
            $record->save();
            foreach ($data['items'] as $item) {
                if ($item['type'] === 'stok') {
                    $stock->firstWhere('id', $item['id'])->update(['description' => $item['memo'] ?? null]);
                } else {
                    $nonstock->firstWhere('id', $item['id'])->update(['memo' => $item['memo'] ?? null]);
                }
            }
        }, 3);
        return $this->response->successResponse('OK', 'Detail penerimaan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();
        // Receipts may have already been consumed and may have vendor payable and
        // non-stock accounting postings. A direct delete would corrupt balances.
        return response()->json([
            'message' => 'Penghapusan penerimaan dinonaktifkan sampai pembalikan stok, hutang vendor, dan jurnal non-stok dapat diverifikasi.',
        ], 409);
    }
}
