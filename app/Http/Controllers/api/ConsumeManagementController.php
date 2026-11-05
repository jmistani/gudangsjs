<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\ConsumeJournal;
use App\Models\Inventory;
use App\Models\JournalHead;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;

class ConsumeManagementController extends Controller
{
    private $response;

    public function __construct(ResponseHelper $response)
    {
        $this->response = $response;
    }

    private function authorizeSuperAdmin()
    {
        $user = auth()->user();

        if (!$user || !$user->hasAnyRole(['super-admin', 'super admin', 'Super Admin'])) {
            return $this->response->unprocessableResponse('Hanya Super Admin yang dapat mengelola data Keluar Barang.', 403);
        }

        return null;
    }

    private function formattedDate(JournalHead $journalHead)
    {
        $raw = $journalHead->getRawOriginal('post_date');

        return $raw
            ? Carbon::parse($raw)->format('d.m.Y')
            : '';
    }

    private function mapListItem(ConsumeJournal $consume)
    {
        $journalHead = $consume->journalhead;
        $mutations = $journalHead
            ? $journalHead->stockMutations
            : collect();

        $total = $mutations->sum(function ($mutation) {
            return abs((int) $mutation->getRawOriginal('total'));
        });

        return [
            'id' => $consume->id,
            'code' => $journalHead ? $journalHead->code : '',
            'post_date' => $journalHead ? $this->formattedDate($journalHead) : '',
            'giver' => $consume->giver,
            'receiver' => $consume->receiver,
            'item_count' => $mutations->count(),
            'total' => $total,
        ];
    }

    private function loadConsume($id)
    {
        return ConsumeJournal::with([
            'journalhead.stockMutations.stockable',
            'journalhead.accountTransactions',
        ])->findOrFail($id);
    }

    public function index(Request $request)
    {
        if ($blocked = $this->authorizeSuperAdmin()) {
            return $blocked;
        }

        $query = ConsumeJournal::with('journalhead.stockMutations')
            ->orderByDesc('id');

        $q = trim((string) $request->query('q', ''));

        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder
                    ->where('giver', 'LIKE', "%{$q}%")
                    ->orWhere('receiver', 'LIKE', "%{$q}%")
                    ->orWhereHas('journalhead', function ($journal) use ($q) {
                        $journal->where('code', 'LIKE', "%{$q}%");
                    });
            });
        }

        $perPage = min(max((int) $request->query('perPage', 20), 1), 100);
        $result = $query->paginate($perPage);

        $result->getCollection()->transform(function ($consume) {
            return $this->mapListItem($consume);
        });

        return $this->response->successResponse($result);
    }

    public function show($id)
    {
        if ($blocked = $this->authorizeSuperAdmin()) {
            return $blocked;
        }

        $consume = $this->loadConsume($id);
        $journalHead = $consume->journalhead;

        if (!$journalHead) {
            return $this->response->unprocessableResponse('Journal Keluar Barang tidak ditemukan.', 404);
        }

        $items = $journalHead->stockMutations->map(function ($mutation) {
            $inventory = $mutation->stockable;

            return [
                'mutation_id' => $mutation->id,
                'inventory_id' => $inventory ? $inventory->id : null,
                'code' => $inventory ? $inventory->code : '',
                'name' => $inventory ? $inventory->name : 'Barang tidak ditemukan',
                'unit' => $inventory ? $inventory->unit : '',
                'qty' => abs((float) $mutation->getRawOriginal('amount')),
                'price' => abs((int) $mutation->getRawOriginal('price')),
                'subtotal' => abs((int) $mutation->getRawOriginal('total')),
                'memo' => $mutation->description,
            ];
        })->values();

        return $this->response->successResponse([
            'id' => $consume->id,
            'code' => $journalHead->code,
            'post_date' => $this->formattedDate($journalHead),
            'giver' => $consume->giver,
            'receiver' => $consume->receiver,
            'items' => $items,
            'total' => $items->sum('subtotal'),
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($blocked = $this->authorizeSuperAdmin()) {
            return $blocked;
        }

        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:100'],
            'post_date' => ['required', 'date_format:d.m.Y'],
            'giver' => ['required', 'string', 'max:255'],
            'receiver' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.mutation_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.memo' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data edit tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $id) {
                $consume = ConsumeJournal::lockForUpdate()->findOrFail($id);
                $journalHead = $consume->journalhead;

                if (!$journalHead) {
                    throw new \RuntimeException('Journal Keluar Barang tidak ditemukan.');
                }

                $duplicate = JournalHead::where('code', $request->code)
                    ->where('id', '!=', $journalHead->id)
                    ->exists();

                if ($duplicate) {
                    throw new \RuntimeException('Kode Keluar Barang sudah digunakan.');
                }

                $oldMutations = StockMutation::where('journal_head_id', $journalHead->id)
                    ->with('stockable')
                    ->lockForUpdate()
                    ->get();

                $oldById = $oldMutations->keyBy('id');
                $submittedIds = collect($request->items)->pluck('mutation_id')->map(function ($value) {
                    return (int) $value;
                });

                if ($submittedIds->count() !== $submittedIds->unique()->count()) {
                    throw new \RuntimeException('Barang yang sama tidak boleh dikirim lebih dari satu baris.');
                }

                foreach ($submittedIds as $mutationId) {
                    if (!$oldById->has($mutationId)) {
                        throw new \RuntimeException('Detail mutasi Keluar Barang tidak valid.');
                    }
                }

                $newItems = collect($request->items)->map(function ($item) use ($oldById) {
                    $mutation = $oldById->get((int) $item['mutation_id']);

                    return [
                        'mutation' => $mutation,
                        'inventory_id' => $mutation->stockable_id,
                        'qty' => (float) $item['qty'],
                        'memo' => trim((string) ($item['memo'] ?? '')),
                        'price' => abs((int) $mutation->getRawOriginal('price')),
                    ];
                });

                // Capture the expense account before replacing journal transactions.
                $expenseTransaction = AccountTransaction::where('journal_head_id', $journalHead->id)
                    ->whereNotNull('debit')
                    ->where('debit', '>', 0)
                    ->orderBy('created_at')
                    ->first();

                if (!$expenseTransaction) {
                    throw new \RuntimeException('Akun biaya transaksi lama tidak ditemukan. Edit dibatalkan untuk menjaga integritas jurnal.');
                }

                $expenseAccount = Account::find($expenseTransaction->account_id);

                if (!$expenseAccount) {
                    throw new \RuntimeException('Akun biaya transaksi lama tidak ditemukan.');
                }

                // Reverse the existing stock impact first.
                $affectedInventoryIds = [];

                foreach ($oldMutations as $mutation) {
                    $inventory = $mutation->stockable;

                    if (!$inventory) {
                        throw new \RuntimeException('Inventory pada salah satu mutasi tidak ditemukan.');
                    }

                    $oldAmount = (float) $mutation->getRawOriginal('amount');
                    $oldTotal = (int) $mutation->getRawOriginal('total');

                    $inventory->in_stock = (float) $inventory->getRawOriginal('in_stock') - $oldAmount;
                    $inventory->stock_value = (int) $inventory->getRawOriginal('stock_value') - $oldTotal;
                    $inventory->save();

                    $affectedInventoryIds[$inventory->id] = true;
                }

                // Validate the replacement quantities against stock after reversal.
                $requestedByInventory = $newItems->groupBy('inventory_id')->map(function ($rows) {
                    return $rows->sum('qty');
                });

                foreach ($requestedByInventory as $inventoryId => $requestedQty) {
                    $inventory = Inventory::lockForUpdate()->find($inventoryId);

                    if (!$inventory) {
                        throw new \RuntimeException('Inventory tidak ditemukan.');
                    }

                    if ($requestedQty > (float) $inventory->getRawOriginal('in_stock')) {
                        throw new \RuntimeException(
                            'Stok tidak cukup untuk ' . $inventory->name .
                            '. Tersedia setelah pembalikan: ' . $inventory->getRawOriginal('in_stock')
                        );
                    }

                    $affectedInventoryIds[$inventory->id] = true;
                }

                // Remove the old accounting and stock records. StockMutation uses SoftDeletes,
                // so its historical row remains recoverable from the archive.
                AccountTransaction::where('journal_head_id', $journalHead->id)->delete();

                foreach ($oldMutations as $mutation) {
                    $mutation->delete();
                }

                $postDate = Carbon::createFromFormat('d.m.Y', $request->post_date);

                $journalHead->code = $request->code;
                $journalHead->post_date = $postDate;
                $journalHead->save();

                $consume->giver = $request->giver;
                $consume->receiver = $request->receiver;
                $consume->save();

                $total = 0;

                foreach ($newItems as $line) {
                    $inventory = Inventory::lockForUpdate()->findOrFail($line['inventory_id']);
                    $qty = $line['qty'];
                    $price = $line['price'];
                    $subtotal = (int) round($qty * $price);

                    $stockMutation = $inventory->subtractStock(
                        $postDate,
                        $qty,
                        $price,
                        $subtotal,
                        $line['memo'],
                        Config::get('gudang.inv_consumption'),
                        $request->code
                    );

                    $stockMutation->assignToConsumeJournal($consume);
                    $journalHead->stockMutations()->save($stockMutation);

                    $inventory->in_stock = (float) $inventory->getRawOriginal('in_stock') - $qty;
                    $inventory->stock_value = (int) $inventory->getRawOriginal('stock_value') - $subtotal;

                    if ((float) $inventory->in_stock == 0) {
                        $inventory->avg_value = 0;
                    }

                    $inventory->save();

                    $total += $subtotal;
                }

                $transaction = $expenseAccount->addDebit(
                    $total,
                    $newItems->first()['mutation']->stockable->name,
                    $postDate,
                    Config::get('gudang.inv_consumption'),
                    null,
                    $request->code,
                    $journalHead->id
                );

                $transaction->referencesObject($consume);
                $transaction->assignToJournalHead($journalHead);

                foreach (array_keys($affectedInventoryIds) as $inventoryId) {
                    $inventory = Inventory::find($inventoryId);
                    if ($inventory && $inventory->account) {
                        $inventory->account->resetCurrentBalances();
                    }
                }

                $expenseAccount->resetCurrentBalances();
            }, 3);

        } catch (\Throwable $e) {
            return $this->response->failedResponseException($e);
        }

        return $this->response->successResponse('OK', 'Data Keluar Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if ($blocked = $this->authorizeSuperAdmin()) {
            return $blocked;
        }

        try {
            DB::transaction(function () use ($id) {
                $consume = ConsumeJournal::lockForUpdate()->findOrFail($id);
                $journalHead = $consume->journalhead;

                if (!$journalHead) {
                    throw new \RuntimeException('Journal Keluar Barang tidak ditemukan.');
                }

                $mutations = StockMutation::where('journal_head_id', $journalHead->id)
                    ->with('stockable')
                    ->lockForUpdate()
                    ->get();

                $affectedInventoryIds = [];

                foreach ($mutations as $mutation) {
                    $inventory = $mutation->stockable;

                    if (!$inventory) {
                        throw new \RuntimeException('Inventory pada salah satu mutasi tidak ditemukan.');
                    }

                    $oldAmount = (float) $mutation->getRawOriginal('amount');
                    $oldTotal = (int) $mutation->getRawOriginal('total');

                    $inventory->in_stock = (float) $inventory->getRawOriginal('in_stock') - $oldAmount;
                    $inventory->stock_value = (int) $inventory->getRawOriginal('stock_value') - $oldTotal;
                    $inventory->save();

                    $affectedInventoryIds[$inventory->id] = true;
                    $mutation->delete();
                }

                AccountTransaction::where('journal_head_id', $journalHead->id)->delete();

                $consume->delete();
                $journalHead->delete();

                foreach (array_keys($affectedInventoryIds) as $inventoryId) {
                    $inventory = Inventory::find($inventoryId);
                    if ($inventory && $inventory->account) {
                        $inventory->account->resetCurrentBalances();
                    }
                }
            }, 3);

        } catch (\Throwable $e) {
            return $this->response->failedResponseException($e);
        }

        return $this->response->successResponse('OK', 'Data Keluar Barang berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        if ($blocked = $this->authorizeSuperAdmin()) {
            return $blocked;
        }

        $validator = Validator::make($request->all(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Tidak ada data Keluar Barang yang dipilih.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->ids as $id) {
                    $this->deleteConsumeWithoutAuthorization((int) $id);
                }
            }, 3);
        } catch (\Throwable $e) {
            return $this->response->failedResponseException($e);
        }

        return $this->response->successResponse('OK', 'Data Keluar Barang terpilih berhasil dihapus.');
    }

    private function deleteConsumeWithoutAuthorization($id)
    {
        $consume = ConsumeJournal::lockForUpdate()->findOrFail($id);
        $journalHead = $consume->journalhead;

        if (!$journalHead) {
            throw new \RuntimeException('Journal Keluar Barang tidak ditemukan.');
        }

        $mutations = StockMutation::where('journal_head_id', $journalHead->id)
            ->with('stockable')
            ->lockForUpdate()
            ->get();

        $affectedInventoryIds = [];

        foreach ($mutations as $mutation) {
            $inventory = $mutation->stockable;

            if (!$inventory) {
                throw new \RuntimeException('Inventory pada salah satu mutasi tidak ditemukan.');
            }

            $oldAmount = (float) $mutation->getRawOriginal('amount');
            $oldTotal = (int) $mutation->getRawOriginal('total');

            $inventory->in_stock = (float) $inventory->getRawOriginal('in_stock') - $oldAmount;
            $inventory->stock_value = (int) $inventory->getRawOriginal('stock_value') - $oldTotal;
            $inventory->save();

            $affectedInventoryIds[$inventory->id] = true;
            $mutation->delete();
        }

        AccountTransaction::where('journal_head_id', $journalHead->id)->delete();

        $consume->delete();
        $journalHead->delete();

        foreach (array_keys($affectedInventoryIds) as $inventoryId) {
            $inventory = Inventory::find($inventoryId);
            if ($inventory && $inventory->account) {
                $inventory->account->resetCurrentBalances();
            }
        }
    }
}
