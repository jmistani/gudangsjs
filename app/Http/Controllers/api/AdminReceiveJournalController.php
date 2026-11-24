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

    private function approvalKey($id): string
    {
        return 'receive-approval:'.auth()->id().':'.$id;
    }

    private function approvalDigest(Request $request): string
    {
        return hash('sha256', json_encode($request->except(['otp']), JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }

    public function requestApproval(Request $request, $id)
    {
        $this->authorizeAdmin();
        $record = ReceiveJournal::with('journalhead')->findOrFail($id);
        $email = config('services.receive_approval.email');
        abort_unless(filter_var($email, FILTER_VALIDATE_EMAIL), 503, 'Email persetujuan admin belum dikonfigurasi.');
        $key = $this->approvalKey($id);
        $cache = \Illuminate\Support\Facades\Cache::store();
        abort_unless($cache->add($key.':cooldown', true, 60), 429, 'Tunggu 60 detik sebelum meminta kode baru.');
        $otp = (string) random_int(100000, 999999);
        $expiry = max(60, (int) config('services.receive_approval.expiry', 3600));
        $cache->forget($key);
        $payload = $request->except(['otp']);
        $head = $record->journalhead;
        abort_unless($head, 409, 'Jurnal penerimaan tidak ditemukan.');
        $request->validate([
            'giver' => 'required|string|max:191',
            'receiver' => 'required|string|max:191',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:stok,non-stok',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|numeric|gt:0',
            'items.*.price' => 'required|integer|min:0',
            'items.*.memo' => 'nullable|string|max:2000',
            'items.*.name' => 'nullable|string|max:191',
            'items.*.unit' => 'nullable|string|max:191',
            'deleted_stock_ids' => 'sometimes|array',
            'deleted_stock_ids.*' => 'integer|distinct',
            'deleted_nonstock_ids' => 'sometimes|array',
            'deleted_nonstock_ids.*' => 'integer|distinct',
        ]);
        $money = function ($value) { return 'Rp '.number_format($value, 0, ',', '.'); };
        $changes = [];
        $before = 0;
        $after = 0;
        foreach (['giver' => 'Vendor', 'receiver' => 'Diterima oleh'] as $field => $label) {
            if ((string) $record->$field !== (string) $payload[$field]) {
                $changes[] = $label.': '.$record->$field.' -> '.$payload[$field];
            }
        }
        $seen = [];
        foreach (['stok' => StockMutation::with('stockable')->where('journal_head_id', $head->id)->get(), 'non-stok' => NonStockInventory::where('journal_head_id', $head->id)->get()] as $type => $lines) {
            foreach ($lines as $line) {
                $keyLine = $type.':'.$line->id;
                $oldQty = (float) $line->getRawOriginal('amount');
                $oldPrice = (int) $line->getRawOriginal('price');
                $oldTotal = (int) $line->getRawOriginal($type === 'stok' ? 'total' : 'subtotal');
                $name = $type === 'stok' ? optional($line->stockable)->name : $line->name;
                $unit = $type === 'stok' ? optional($line->stockable)->unit : $line->unit;
                $before += $oldTotal;
                $matches = collect($payload['items'])->filter(function ($item) use ($type, $line) {
                    return $item['type'] === $type && (int) $item['id'] === (int) $line->id;
                });
                $deleted = in_array((int) $line->id, array_map('intval', $payload[$type === 'stok' ? 'deleted_stock_ids' : 'deleted_nonstock_ids'] ?? []), true);
                abort_unless($matches->count() + (int) $deleted === 1, 422, 'Detail barang tidak cocok. Muat ulang halaman.');
                $seen[] = $keyLine;
                if ($deleted) {
                    $changes[] = "HAPUS BARIS: ".$name." (".$type.")\nJumlah: ".$oldQty." ".$unit." -> 0\nHarga satuan: ".$money($oldPrice)."\nSubtotal: ".$money($oldTotal)." -> Rp 0";
                    continue;
                }
                $item = $matches->first();
                $newQty = (float) $item['qty'];
                $newPrice = (int) $item['price'];
                $newTotal = ($newQty == $oldQty && $newPrice === $oldPrice && $type === 'stok') ? $oldTotal : (int) round($newQty * $newPrice);
                $after += $newTotal;
                $details = [];
                if ($newQty != $oldQty) $details[] = 'Jumlah: '.$oldQty.' -> '.$newQty.' '.$unit;
                if ($newPrice !== $oldPrice) $details[] = 'Harga satuan: '.$money($oldPrice).' -> '.$money($newPrice);
                if ($newTotal !== $oldTotal) $details[] = 'Subtotal: '.$money($oldTotal).' -> '.$money($newTotal);
                $oldMemo = (string) ($type === 'stok' ? $line->description : $line->memo);
                if ($oldMemo !== (string) ($item['memo'] ?? '')) $details[] = 'Keterangan: '.$oldMemo.' -> '.($item['memo'] ?? '');
                if ($type === 'non-stok') {
                    foreach (['name' => 'Nama', 'unit' => 'Satuan'] as $field => $label) {
                        if (isset($item[$field]) && (string) $line->$field !== (string) $item[$field]) $details[] = $label.': '.$line->$field.' -> '.$item[$field];
                    }
                }
                if ($details) $changes[] = $name.' ('.$type.')'."\n".implode("\n", $details);
            }
        }
        $submitted = collect($payload['items'])->map(function ($item) { return $item['type'].':'.$item['id']; })->all();
        foreach (['stok' => 'deleted_stock_ids', 'non-stok' => 'deleted_nonstock_ids'] as $type => $field) {
            foreach ($payload[$field] ?? [] as $lineId) $submitted[] = $type.':'.$lineId;
        }
        sort($submitted); sort($seen);
        abort_unless($submitted === $seen, 422, 'Detail barang tidak cocok. Muat ulang halaman.');
        abort_unless(count($changes), 422, 'Tidak ada perubahan untuk disetujui.');
        $body = "ERP Gudang SJS\nPERMINTAAN PERSETUJUAN PERUBAHAN PENERIMAAN\n\n"
            .'Transaksi: '.$head->code."\nVendor: ".$record->giver."\nDiajukan oleh: ".(auth()->user()->name ?? auth()->id())." (ID ".auth()->id().")\n\n"
            ."DETAIL PERUBAHAN (SEBELUM -> SESUDAH)\n".implode("\n\n", $changes)
            ."\n\nTotal sebelum: ".$money($before)."\nTotal sesudah: ".$money($after)."\nSelisih: ".$money($after-$before)
            ."\n\nKode persetujuan: ".$otp."\nBerlaku: ".($expiry/60)." menit\n\nBerikan kode hanya jika seluruh perubahan telah diperiksa dan disetujui.";
        \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($email, $head) {
            $message->to($email)->subject('Persetujuan perubahan penerimaan - '.$head->code);
        });
        $cache->put($key, ['hash' => \Illuminate\Support\Facades\Hash::make($otp), 'digest' => $this->approvalDigest($request), 'attempts' => 0, 'expires' => time()+$expiry], $expiry);
        return $this->response->successResponse('OK', 'Kode persetujuan dikirim ke email admin.');
    }

    private function consumeApproval(Request $request, $id): void
    {
        $request->validate(['otp' => ['required', 'digits:6']]);
        $key = $this->approvalKey($id);
        $cache = \Illuminate\Support\Facades\Cache::store();
        $cache->lock($key.':lock', 15)->block(5, function () use ($cache, $key, $request) {
            $entry = $cache->get($key);
            abort_unless($entry && $entry['expires'] > time(), 422, 'Kode persetujuan tidak tersedia atau kedaluwarsa.');
            abort_unless(hash_equals($entry['digest'], $this->approvalDigest($request)), 422, 'Usulan berubah. Minta kode baru.');
            if (!\Illuminate\Support\Facades\Hash::check((string) $request->otp, $entry['hash'])) {
                $entry['attempts']++;
                if ($entry['attempts'] >= 3) $cache->forget($key);
                else $cache->put($key, $entry, max(1, $entry['expires'] - time()));
                abort(422, 'Kode persetujuan salah.');
            }
            $cache->forget($key);
        });
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

        $this->consumeApproval($request, $id);
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
        abort(409, 'Gunakan Simpan Perubahan dengan kode persetujuan admin.');
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
