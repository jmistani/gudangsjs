<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\CompanyCoa;
use App\Models\ConsumeJournal;
use App\Models\Inventory;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminConsumeJournalController extends Controller
{
    private $response;

    public function __construct(ResponseHelper $response)
    {
        $this->response = $response;
    }

    private function authorizeSuperAdmin()
    {
        $user = auth()->user();

        if (!$user || !$user->roles()
            ->whereRaw('LOWER(name) IN (?, ?)', ['super-admin', 'super admin'])
            ->exists()) {
            abort(403, 'Akses hanya untuk Super Admin.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();

        $query = ConsumeJournal::query()
            ->with('journalhead')
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $q = trim($request->query('q'));

            $query->where(function ($builder) use ($q) {
                $builder
                    ->where('giver', 'LIKE', "%{$q}%")
                    ->orWhere('receiver', 'LIKE', "%{$q}%")
                    ->orWhereHas('journalhead', function ($journal) use ($q) {
                        $journal->where('code', 'LIKE', "%{$q}%");
                    });
            });
        }

        if ($request->filled('date_start')) {
            $query->whereHas('journalhead', function ($journal) use ($request) {
                $journal->whereDate('post_date', '>=', $request->date_start);
            });
        }

        if ($request->filled('date_end')) {
            $query->whereHas('journalhead', function ($journal) use ($request) {
                $journal->whereDate('post_date', '<=', $request->date_end);
            });
        }

        $perPage = min(max((int) $request->get('perPage', 25), 1), 100);
        $records = $query->paginate($perPage);

        $records->getCollection()->transform(function ($consume) {
            $journalHead = $consume->journalhead;

            $mutations = $journalHead
                ? StockMutation::where('journal_head_id', $journalHead->id)->get()
                : collect();

            return [
                'id' => $consume->id,
                'code' => $journalHead ? $journalHead->code : null,
                'post_date' => $journalHead
                    ? Carbon::parse($journalHead->getRawOriginal('post_date'))->format('Y-m-d')
                    : null,
                'giver' => $consume->giver,
                'receiver' => $consume->receiver,
                'item_count' => $mutations->count(),
                'total' => $mutations->sum(function ($mutation) {
                    return abs((int) $mutation->getRawOriginal('total'));
                }),
            ];
        });

        return $this->response->successResponse($records);
    }

    public function show($id)
    {
        $this->authorizeSuperAdmin();

        $consume = ConsumeJournal::with('journalhead')->findOrFail($id);
        $journalHead = $consume->journalhead;

        if (!$journalHead) {
            return $this->response->notFoundResponse('Journal head pengeluaran tidak ditemukan.');
        }

        $mutations = StockMutation::with('stockable')
            ->where('journal_head_id', $journalHead->id)
            ->orderBy('id')
            ->get();

        $accountTransaction = AccountTransaction::where('journal_head_id', $journalHead->id)
            ->whereNotNull('debit')
            ->orderBy('id')
            ->first();

        $expenseAccount = $accountTransaction
            ? Account::find($accountTransaction->account_id)
            : null;

        return $this->response->successResponse([
            'id' => $consume->id,
            'journal_head_id' => $journalHead->id,
            'code' => $journalHead->code,
            'post_date' => Carbon::parse($journalHead->getRawOriginal('post_date'))->format('Y-m-d'),
            'giver' => $consume->giver,
            'receiver' => $consume->receiver,
            'account_code' => optional($expenseAccount)->code,
            'account_name' => optional($expenseAccount)->name,
            'items' => $mutations->map(function ($mutation) {
                return [
                    'mutation_id' => $mutation->id,
                    'inventory_id' => $mutation->stockable_id,
                    'code' => optional($mutation->stockable)->code,
                    'name' => optional($mutation->stockable)->name,
                    'unit' => optional($mutation->stockable)->unit,
                    'qty' => abs((float) $mutation->getRawOriginal('amount')),
                    'price' => abs((int) $mutation->getRawOriginal('price')),
                    'subtotal' => abs((int) $mutation->getRawOriginal('total')),
                    'memo' => $mutation->description,
                    'second_reference_type' => $mutation->second_reference_type,
                    'second_reference_id' => $mutation->second_reference_id,
                ];
            })->values(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'post_date' => ['required', 'date'],
            'giver' => ['required', 'string', 'max:255'],
            'receiver' => ['required', 'string', 'max:255'],
            'account_code' => ['required', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.mutation_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.memo' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $id) {
            $consume = ConsumeJournal::findOrFail($id);
            $journalHead = $consume->journalhead;

            if (!$journalHead) {
                throw new \RuntimeException('Journal head pengeluaran tidak ditemukan.');
            }

            $mutations = StockMutation::where('journal_head_id', $journalHead->id)
                ->lockForUpdate()
                ->get();

            $oldExpenseTransaction = AccountTransaction::where('journal_head_id', $journalHead->id)
                ->whereNotNull('debit')
                ->orderBy('id')
                ->first();
            $oldExpenseAccount = $oldExpenseTransaction
                ? Account::find($oldExpenseTransaction->account_id)
                : null;

            if ($mutations->isEmpty()) {
                throw new \RuntimeException('Detail pengeluaran tidak ditemukan.');
            }

            $postedMutationIds = $mutations->pluck('id')->sort()->values()->all();
            $requestMutationIds = collect($data['items'])
                ->pluck('mutation_id')
                ->map(function ($id) { return (int) $id; })
                ->sort()
                ->values()
                ->all();

            if ($postedMutationIds !== $requestMutationIds) {
                throw ValidationException::withMessages([
                    'items' => 'Daftar barang tidak boleh ditambah atau dihapus dari mode edit ini. Hanya jumlah dan keterangan yang dapat diubah.',
                ]);
            }

            if (count($requestMutationIds) !== count(array_unique($requestMutationIds))) {
                throw ValidationException::withMessages([
                    'items' => 'Detail barang duplikat.',
                ]);
            }

            $duplicateCode = \App\Models\JournalHead::where('code', $data['code'])
                ->where('id', '!=', $journalHead->id)
                ->exists();

            if ($duplicateCode) {
                throw ValidationException::withMessages([
                    'code' => 'Kode transaksi sudah digunakan.',
                ]);
            }

            $accountCoa = CompanyCoa::where('code', $data['account_code'])->first();

            if (!$accountCoa || !$accountCoa->account) {
                throw ValidationException::withMessages([
                    'account_code' => 'Akun biaya tidak ditemukan.',
                ]);
            }

            $updates = collect($data['items'])->keyBy(function ($item) {
                return (int) $item['mutation_id'];
            });

            // The old transaction is reversed first. This makes the new quantity
            // validation and stock calculation independent of the old quantity.
            foreach ($mutations as $mutation) {
                $inventory = Inventory::findOrFail($mutation->stockable_id);
                $oldAmount = (int) $mutation->getRawOriginal('amount');
                $oldTotal = (int) $mutation->getRawOriginal('total');

                $inventory->in_stock -= $oldAmount;
                $inventory->stock_value = (int) $inventory->getRawOriginal('stock_value') - $oldTotal;
                $inventory->save();

                $mutation->delete();
            }

            DB::table('accounting_account_transactions')
                ->where('journal_head_id', $journalHead->id)
                ->delete();

            if ($oldExpenseAccount) {
                $oldExpenseAccount->resetCurrentBalances();
            }

            $journalHead->code = $data['code'];
            $journalHead->post_date = Carbon::parse($data['post_date']);
            $journalHead->save();

            $consume->giver = $data['giver'];
            $consume->receiver = $data['receiver'];
            $consume->save();

            foreach ($mutations as $oldMutation) {
                $inventory = Inventory::findOrFail($oldMutation->stockable_id);
                $item = $updates->get((int) $oldMutation->id);

                $qty = (float) $item['qty'];
                $price = abs((int) $oldMutation->getRawOriginal('price'));
                $total = (int) round($qty * $price);
                $memo = isset($item['memo']) ? trim((string) $item['memo']) : null;

                if ($qty > $inventory->in_stock) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$inventory->name} tidak cukup untuk jumlah {$qty}.",
                    ]);
                }

                $stockMutation = $inventory->subtractStock(
                    Carbon::parse($data['post_date']),
                    $qty,
                    $price,
                    $total,
                    $memo,
                    config('gudang.inv_consumption'),
                    $data['code']
                );

                $journalHead->stockMutations()->save($stockMutation);

                // Preserve an existing ticket reference if this mutation had one.
                if ($oldMutation->second_reference_type === 'App\\Models\\Ticket'
                    && $oldMutation->second_reference_id) {
                    $stockMutation->second_reference_type = $oldMutation->second_reference_type;
                    $stockMutation->second_reference_id = $oldMutation->second_reference_id;
                    $stockMutation->save();
                } else {
                    $stockMutation->assignToConsumeJournal($consume);
                }

                $inventory->in_stock -= $qty;
                $inventory->stock_value = (int) $inventory->getRawOriginal('stock_value') - $total;
                $inventory->save();

                $transaction = $accountCoa->account->addDebit(
                    $total,
                    $inventory->name,
                    Carbon::parse($data['post_date']),
                    config('gudang.inv_consumption'),
                    null,
                    $data['code'],
                    $journalHead->id
                );

                $transaction->referencesObject($consume);
                $transaction->assignToJournalHead($journalHead);
            }

            // Recalculate all affected inventory balances from active mutations.
            $inventoryIds = $mutations->pluck('stockable_id')->unique();
            foreach ($inventoryIds as $inventoryId) {
                $inventory = Inventory::findOrFail($inventoryId);
                $inventory->recalculateStock();
            }
        }, 3);

        return $this->response->successResponse('OK', 'Data pengeluaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->authorizeSuperAdmin();

        DB::transaction(function () use ($id) {
            $consume = ConsumeJournal::findOrFail($id);
            $journalHead = $consume->journalhead;

            if (!$journalHead) {
                throw new \RuntimeException('Journal head pengeluaran tidak ditemukan.');
            }

            $mutations = StockMutation::where('journal_head_id', $journalHead->id)
                ->lockForUpdate()
                ->get();

            $oldExpenseTransaction = AccountTransaction::where('journal_head_id', $journalHead->id)
                ->whereNotNull('debit')
                ->orderBy('id')
                ->first();
            $oldExpenseAccount = $oldExpenseTransaction
                ? Account::find($oldExpenseTransaction->account_id)
                : null;

            foreach ($mutations as $mutation) {
                $inventory = Inventory::findOrFail($mutation->stockable_id);
                $oldAmount = (int) $mutation->getRawOriginal('amount');
                $oldTotal = (int) $mutation->getRawOriginal('total');

                $inventory->in_stock -= $oldAmount;
                $inventory->stock_value = (int) $inventory->getRawOriginal('stock_value') - $oldTotal;
                $inventory->save();

                $mutation->delete();
            }

            DB::table('accounting_account_transactions')
                ->where('journal_head_id', $journalHead->id)
                ->delete();

            if ($oldExpenseAccount) {
                $oldExpenseAccount->resetCurrentBalances();
            }

            $journalHead->delete();
            $consume->delete();

            foreach ($mutations->pluck('stockable_id')->unique() as $inventoryId) {
                Inventory::find($inventoryId)->recalculateStock();
            }
        }, 3);

        return $this->response->successResponse('OK', 'Data pengeluaran berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['ids'] as $id) {
                $this->deleteRecord((int) $id);
            }
        }, 3);

        return $this->response->successResponse('OK', 'Data pengeluaran terpilih berhasil dihapus.');
    }

    private function deleteRecord($id)
    {
        $consume = ConsumeJournal::findOrFail($id);
        $journalHead = $consume->journalhead;

        if (!$journalHead) {
            throw new \RuntimeException('Journal head pengeluaran tidak ditemukan.');
        }

        $mutations = StockMutation::where('journal_head_id', $journalHead->id)
            ->lockForUpdate()
            ->get();

        $oldExpenseTransaction = AccountTransaction::where('journal_head_id', $journalHead->id)
            ->whereNotNull('debit')
            ->orderBy('id')
            ->first();
        $oldExpenseAccount = $oldExpenseTransaction
            ? Account::find($oldExpenseTransaction->account_id)
            : null;

        foreach ($mutations as $mutation) {
            $inventory = Inventory::findOrFail($mutation->stockable_id);
            $inventory->in_stock -= (int) $mutation->getRawOriginal('amount');
            $inventory->stock_value = (int) $inventory->getRawOriginal('stock_value') - (int) $mutation->getRawOriginal('total');
            $inventory->save();
            $mutation->delete();
        }

        DB::table('accounting_account_transactions')
            ->where('journal_head_id', $journalHead->id)
            ->delete();

        if ($oldExpenseAccount) {
            $oldExpenseAccount->resetCurrentBalances();
        }

        $journalHead->delete();
        $consume->delete();

        foreach ($mutations->pluck('stockable_id')->unique() as $inventoryId) {
            Inventory::find($inventoryId)->recalculateStock();
        }
    }
}
