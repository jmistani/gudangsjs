<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ReceiveJournal;
use App\Models\StockMutation;
use App\Models\Inventory;
use App\Models\NonStockInventory;
use App\Models\JournalHead;
use App\Models\AccountTransaction;
use App\Models\Account;
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
            'items' => ['required', 'array', 'min:1'],
            'deleted_nonstock_ids' => ['sometimes', 'array'],
            'deleted_nonstock_ids.*' => ['required', 'integer', 'distinct'],
            'deleted_stock_ids' => ['sometimes', 'array'],
            'deleted_stock_ids.*' => ['required', 'integer', 'distinct'],
            'items.*.type' => ['required', 'in:stok,non-stok'],
            'items.*.id' => ['required', 'integer'],
            'items.*.memo' => ['nullable', 'string', 'max:2000'],
            'items.*.name' => ['nullable', 'string', 'max:191'],
            'items.*.unit' => ['nullable', 'string', 'max:191'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.price' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($id, $data) {
            $record = ReceiveJournal::findOrFail($id);
            $head = $record->journalhead;
            if (!$head) abort(409, 'Jurnal penerimaan tidak ditemukan.');
            $stock = StockMutation::where('journal_head_id', $head->id)->lockForUpdate()->get();
            $nonstock = NonStockInventory::where('journal_head_id', $head->id)->lockForUpdate()->get();
            $expected = $stock->map(function ($x) { return 'stok:'.$x->id; })
                ->concat($nonstock->map(function ($x) { return 'non-stok:'.$x->id; }))->sort()->values()->all();
            $deletedIds = collect($data['deleted_nonstock_ids'] ?? []);
            $deletedStockIds = collect($data['deleted_stock_ids'] ?? []);
            $given = collect($data['items'])->map(function ($x) { return $x['type'].':'.$x['id']; })
                ->concat($deletedIds->map(function ($id) { return 'non-stok:'.$id; }))
                ->concat($deletedStockIds->map(function ($id) { return 'stok:'.$id; }))->sort()->values()->all();
            if ($expected !== $given) {
                throw ValidationException::withMessages(['items' => 'Detail barang telah berubah. Muat ulang halaman.']);
            }
            // Resolve non-stock expense postings before making any changes.
            // Older receipts use the journal head as their expense reference.
            $expenseMatches = [];
            foreach ($data['items'] as $item) {
                if ($item['type'] !== 'non-stok') continue;
                $line = $nonstock->firstWhere('id', $item['id']);
                $expenseMatches[$line->id] = $this->findNonStockExpense($head, $line);
            }

            // Validate stock changes before any database mutation.
            foreach ($data['items'] as $item) {
                $existing = $item['type'] === 'stok'
                    ? $stock->firstWhere('id', $item['id'])
                    : $nonstock->firstWhere('id', $item['id']);
                if (!$existing) {
                    throw ValidationException::withMessages(['items' => 'Item tidak ditemukan.']);
                }
                $qtyChanged = (float) $existing->getRawOriginal('amount') != (float) $item['qty'];
                $priceChanged = (int) $existing->getRawOriginal('price') !== (int) $item['price'];
                if ($item['type'] === 'stok' && ($qtyChanged || $priceChanged)) {
                    if ((float) $item['qty'] != floor((float) $item['qty'])) {
                        throw ValidationException::withMessages(['items' => 'Jumlah stok harus bilangan bulat.']);
                    }
                    $this->assertStockLineEditable($head, $existing);
                }
            }

            $record->giver = $data['giver'];
            $record->receiver = $data['receiver'];
            $record->save();
            foreach ($data['items'] as $item) {
                if ($item['type'] === 'stok') {
                    $line = $stock->firstWhere('id', $item['id']);
                    $qtyChanged = (float) $line->getRawOriginal('amount') != (float) $item['qty'];
                    $priceChanged = (int) $line->getRawOriginal('price') !== (int) $item['price'];
                    if ($qtyChanged || $priceChanged) {
                        $this->applyStockLineChange($head, $record, $line, (float) $item['qty'], (int) $item['price']);
                    }
                    $line->description = $item['memo'] ?? null;
                    $line->save();
                } else {
                    $line = $nonstock->firstWhere('id', $item['id']);
                    $expense = $expenseMatches[$line->id];
                    $oldTotal = (int) $line->getRawOriginal('subtotal');
                    $newTotal = (int) round((float) $item['qty'] * (int) $item['price']);
                    $delta = $newTotal - $oldTotal;
                    $payable = null;

                    $line->update([
                        'memo' => $item['memo'] ?? null,
                        'name' => $item['name'] ?? $line->name,
                        'unit' => $item['unit'] ?? $line->unit,
                        'amount' => $item['qty'],
                        'price' => $item['price'],
                        'subtotal' => $newTotal,
                    ]);

                    // Re-link legacy expense records to their exact line.
                    $expense->ref_class = NonStockInventory::class;
                    $expense->ref_class_id = $line->id;
                    $expense->debit = $newTotal;
                    $expense->memo = $line->name;
                    $expense->save();

                    if ($delta !== 0) {
                        $payable = $this->findReceiptPayable($head, $record);
                        $newPayableCredit = (int) $payable->getRawOriginal('credit') + $delta;
                        if ($newPayableCredit < 0) {
                            throw ValidationException::withMessages(['items' => 'Saldo hutang penerimaan menjadi negatif.']);
                        }
                        $payable->credit = $newPayableCredit;
                        $payable->save();
                    }

                    Account::findOrFail($expense->account_id)->resetCurrentBalances();
                    if ($payable) {
                        Account::findOrFail($payable->account_id)->resetCurrentBalances();
                    }
                }
            }

            foreach ($deletedStockIds as $deletedStockId) {
                $line = $stock->firstWhere('id', $deletedStockId);
                $this->assertStockLineEditable($head, $line);
                $this->applyStockLineChange($head, $record, $line, 0, 0, true);
            }

            // Apply staged deletions in the SAME transaction as all field edits.
            // If any posting cannot be reconciled, the entire save is rolled back.
            foreach ($deletedIds as $deletedId) {
                $line = $nonstock->firstWhere('id', $deletedId);
                $expense = $this->findNonStockExpense($head, $line);
                $payable = $this->findReceiptPayable($head, $record);
                $newCredit = (int) $payable->getRawOriginal('credit')
                    - (int) $line->getRawOriginal('subtotal');
                if ($newCredit < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Jumlah hutang vendor tidak mencukupi untuk menghapus baris ini.',
                    ]);
                }
                $expenseAccountId = $expense->account_id;
                $payableAccountId = $payable->account_id;
                $expense->delete();
                $line->delete();
                $payable->credit = $newCredit;
                $payable->save();
                Account::findOrFail($expenseAccountId)->resetCurrentBalances();
                Account::findOrFail($payableAccountId)->resetCurrentBalances();
            }
        }, 3);
        return $this->response->successResponse('OK', 'Detail penerimaan berhasil diperbarui.');
    }

    /**
     * Stock purchase corrections are safe only if no later mutation exists.
     * Later consumption or receipts may have used the old weighted average cost.
     */
    private function assertStockLineEditable(JournalHead $head, StockMutation $line): void
    {
        if ((float) $line->getRawOriginal('amount') <= 0
            || $line->stockable_type !== Inventory::class) {
            throw ValidationException::withMessages(['items' => 'Baris bukan penerimaan stok yang valid.']);
        }
        if (StockMutation::where('stockable_type', Inventory::class)
            ->where('stockable_id', $line->stockable_id)
            ->where('id', '>', $line->id)->exists()) {
            throw ValidationException::withMessages([
                'items' => 'Stok ini sudah memiliki mutasi setelah penerimaan. Koreksi memerlukan rekonsiliasi histori terlebih dahulu.',
            ]);
        }
        $this->findStockDebit($head, $line);
    }

    private function findStockDebit(JournalHead $head, StockMutation $line): AccountTransaction
    {
        $inventory = Inventory::findOrFail($line->stockable_id);
        $matches = AccountTransaction::where('journal_head_id', $head->id)
            ->where('ref_class', Inventory::class)
            ->where('ref_class_id', $inventory->id)
            ->where('account_id', $inventory->account->id)
            ->whereNotNull('debit')
            ->lockForUpdate()->get();
        // Legacy receipts may contain multiple rows for the same inventory.
        // Never guess which debit corresponds to which row.
        if ($matches->count() !== 1
            || StockMutation::where('journal_head_id', $head->id)
                ->where('stockable_type', Inventory::class)
                ->where('stockable_id', $inventory->id)->count() !== 1) {
            throw ValidationException::withMessages([
                'items' => 'Jurnal pembelian stok tidak dapat dicocokkan secara unik. Perlu pemeriksaan manual.',
            ]);
        }
        return $matches->first();
    }

    private function applyStockLineChange(
        JournalHead $head,
        ReceiveJournal $record,
        StockMutation $line,
        float $quantity,
        int $price,
        bool $delete = false
    ): void {
        $this->assertStockLineEditable($head, $line);
        $inventory = Inventory::whereKey($line->stockable_id)->lockForUpdate()->firstOrFail();
        $debit = $this->findStockDebit($head, $line);
        $payable = $this->findReceiptPayable($head, $record);
        $oldTotal = (int) $line->getRawOriginal('total');
        $oldQuantity = (float) $line->getRawOriginal('amount');
        $newTotal = $delete ? 0 : (int) round($quantity * $price);
        $newQuantity = $delete ? 0 : $quantity;
        $delta = $newTotal - $oldTotal;
        $newPayable = (int) $payable->getRawOriginal('credit') + $delta;
        $newStock = (float) $inventory->getRawOriginal('in_stock') + $newQuantity - $oldQuantity;
        $newValue = (int) $inventory->getRawOriginal('stock_value') + $delta;
        if ($newPayable < 0 || $newStock < 0 || $newValue < 0) {
            throw ValidationException::withMessages([
                'items' => 'Perubahan stok menyebabkan saldo stok, nilai stok, atau hutang negatif.',
            ]);
        }
        if ($delete) {
            $debit->delete();
            $line->delete(); // SoftDeletes retains the original mutation for audit.
        } else {
            $line->amount = $newQuantity;
            $line->price = $price;
            $line->total = $newTotal;
            $line->save();
            $debit->debit = $newTotal;
            $debit->save();
        }
        $payable->credit = $newPayable;
        $payable->save();
        $inventory->in_stock = $newStock;
        $inventory->stock_value = $newValue;
        $inventory->avg_value = $newStock > 0 ? (int) round($newValue / $newStock) : 0;
        $inventory->save();
        Account::findOrFail($debit->account_id)->resetCurrentBalances();
        Account::findOrFail($payable->account_id)->resetCurrentBalances();
    }

    private function findNonStockExpense(JournalHead $head, NonStockInventory $line)
    {
        $query = AccountTransaction::where('journal_head_id', $head->id)
            ->whereNotNull('debit')
            ->lockForUpdate();

        $linked = (clone $query)
            ->where('ref_class', NonStockInventory::class)
            ->where('ref_class_id', $line->id)
            ->get();

        if ($linked->count() === 1) return $linked->first();

        if ($linked->count() > 1) {
            throw ValidationException::withMessages(['items' => 'Terdapat beberapa jurnal biaya untuk baris yang sama.']);
        }

        // Legacy receipts referenced the header. Match only if the combination
        // of original memo and amount identifies one unique posting and one line.
        $amount = (int) $line->getRawOriginal('subtotal');
        $legacy = AccountTransaction::where('ref_class', JournalHead::class)
            ->where('ref_class_id', $head->id)
            ->where('transaction_code', $head->code)
            ->where('memo', $line->name)
            ->where('debit', $amount)
            ->lockForUpdate()
            ->get();

        $sameLines = NonStockInventory::where('journal_head_id', $head->id)
            ->where('name', $line->name)
            ->where('subtotal', $amount)
            ->count();

        if ($legacy->count() !== 1 || $sameLines !== 1) {
            throw ValidationException::withMessages([
                'items' => 'Jurnal biaya lama tidak dapat dicocokkan secara unik untuk '.$line->name.'. Perlu pemeriksaan manual.',
            ]);
        }

        return $legacy->first();
    }

    private function findReceiptPayable(JournalHead $head, ReceiveJournal $record)
    {
        $matches = AccountTransaction::where('journal_head_id', $head->id)
            ->where('ref_class', ReceiveJournal::class)
            ->where('ref_class_id', $record->id)
            ->whereNotNull('credit')
            ->lockForUpdate()
            ->get();

        if ($matches->count() !== 1) {
            throw ValidationException::withMessages([
                'items' => 'Jurnal hutang vendor tidak ditemukan secara unik.',
            ]);
        }
        return $matches->first();
    }

    public function deleteItem($id, $type, $itemId)
    {
        $this->authorizeAdmin();
        if (!in_array($type, ['stok', 'non-stok'], true)) abort(404);
        if ($type === 'stok') {
            return response()->json([
                'message' => 'Hapus baris stok masih dibatasi karena memengaruhi histori persediaan.',
            ], 409);
        }

        DB::transaction(function () use ($id, $itemId) {
            $record = ReceiveJournal::findOrFail($id);
            $head = $record->journalhead;
            if (!$head) abort(409, 'Jurnal penerimaan tidak ditemukan.');

            $line = NonStockInventory::where('journal_head_id', $head->id)
                ->lockForUpdate()
                ->findOrFail($itemId);

            $otherCount = StockMutation::where('journal_head_id', $head->id)->count()
                + NonStockInventory::where('journal_head_id', $head->id)->count();

            if ($otherCount <= 1) {
                throw ValidationException::withMessages([
                    'items' => 'Tidak dapat menghapus baris terakhir. Transaksi harus tetap memiliki barang.',
                ]);
            }

            $expense = $this->findNonStockExpense($head, $line);
            $payable = $this->findReceiptPayable($head, $record);
            $newCredit = (int) $payable->getRawOriginal('credit')
                - (int) $line->getRawOriginal('subtotal');

            if ($newCredit < 0) {
                throw ValidationException::withMessages([
                    'items' => 'Jumlah hutang vendor tidak mencukupi untuk menghapus baris ini.',
                ]);
            }

            $expenseAccountId = $expense->account_id;
            $payableAccountId = $payable->account_id;

            $expense->delete();
            $line->delete();
            $payable->credit = $newCredit;
            $payable->save();

            Account::findOrFail($expenseAccountId)->resetCurrentBalances();
            Account::findOrFail($payableAccountId)->resetCurrentBalances();
        }, 3);

        return $this->response->successResponse('OK', 'Baris non-stok dan jurnal biayanya berhasil dihapus.');
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
