<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Money\Currency;
use Money\Money;
use RuntimeException;

class ReconcileInventoryMutations extends Command
{
    protected $signature = 'stock:reconcile-mutations
        {--execute : Apply changes; otherwise preview}
        {--inventory= : Process one inventory ID}
        {--recalculate : Recalculate stored inventory quantity/value/average after adjustments}
        {--post-ledger : Post GL balance correction (requires --execute and --offset-account)}
        {--offset-account= : Accounting account ID for balancing ledger postings}';

    protected $description = 'Reconcile mutation totals to inventory balances, optionally recalculate inventory and post balanced GL corrections';

    public function handle()
    {
        $execute = (bool) $this->option('execute');
        $recalculate = (bool) $this->option('recalculate');
        $postLedger = (bool) $this->option('post-ledger');
        $single = $this->option('inventory');
        $offset = $this->option('offset-account');
        if ($single !== null && (!ctype_digit((string) $single) || (int) $single < 1)) {
            $this->error('Invalid inventory ID.'); return 1;
        }
        if ($postLedger && (!$execute || !$offset || !ctype_digit((string) $offset) || (int) $offset < 1)) {
            $this->error('--post-ledger requires --execute and --offset-account=ID.'); return 1;
        }
        if ($offset && !ctype_digit((string) $offset)) {
            $this->error('Invalid offset account ID.'); return 1;
        }
        if ($execute && config('app.env') === 'production' && !app()->isDownForMaintenance()) {
            $this->error('Enable maintenance mode and stop all stock writers and queue workers first.'); return 1;
        }
        if ($execute && !$this->confirm('Database backup complete, inventory balances approved, and (if selected) GL offset account approved?')) return 0;

        $type = (new Inventory())->getMorphClass();
        $counter = ['stock' => 0, 'ledger' => 0];
        $query = Inventory::query()->select('id')->orderBy('id');
        if ($single !== null) $query->whereKey((int) $single);
        $query->chunkById(100, function ($items) use ($type, $execute, $recalculate, $postLedger, $offset, &$counter) {
            foreach ($items as $item) {
                $work = function () use ($item, $type, $execute, $recalculate, $postLedger, $offset, &$counter) {
                    $inv = DB::table('inventories')->where('id', $item->id)->whereNull('deleted_at')->lockForUpdate()->first();
                    if (!$inv) return;
                    $sum = $this->mutationSum($type, $inv->id);
                    $dq = (int) $inv->in_stock - (int) $sum->qty;
                    $dv = (int) $inv->stock_value - (int) $sum->val;
                    $price = (int) $inv->avg_value;
                    if ($price < 0) throw new RuntimeException("Negative average value for {$inv->id}");
                    $qtyValue = $dq * $price;
                    $extraValue = $dv - $qtyValue;
                    $account = null;
                    $ledgerDelta = 0;
                    if ($postLedger) {
                        $account = DB::table('accounting_accounts')
                            ->where('morphed_type', $type)->where('morphed_id', $inv->id)
                            ->lockForUpdate()->first();
                        if (!$account) throw new RuntimeException("Inventory {$inv->id} has no linked GL account");
                        if ((int) $account->id === (int) $offset) throw new RuntimeException('Offset cannot equal inventory account');
                        $offsetRow = DB::table('accounting_accounts')->where('id', (int) $offset)->lockForUpdate()->first();
                        if (!$offsetRow) throw new RuntimeException("Offset account {$offset} does not exist");
                        if ($account->currency !== $offsetRow->currency) throw new RuntimeException('Currency mismatch on GL accounts');
                        if ($account->currency !== config('gudang.currency', 'IDR')) throw new RuntimeException('Unexpected inventory account currency');
                        // Inventory account is normally debit-balance. Compare to target stock_value,
                        // NOT to mutation correction amount (which may already be posted historically).
                        $ledgerSum = DB::table('accounting_account_transactions')->where('account_id', $account->id)
                            ->whereNull('deleted_at')
                            ->selectRaw('COALESCE(SUM(debit),0) AS debits, COALESCE(SUM(credit),0) AS credits')->first();
                        $ledgerBookValue = (int) $ledgerSum->debits - (int) $ledgerSum->credits;
                        $ledgerDelta = (int) $inv->stock_value - $ledgerBookValue;
                    }
                    if (!$dq && !$dv && !$ledgerDelta) return;
                    $this->line("#{$inv->id} {$inv->code}: qtyDelta={$dq} valueDelta={$dv} avg={$price} GLDelta={$ledgerDelta}");
                    if (!$dq && !$dv) { /* GL-only repair */ }
                    else $counter['stock']++;
                    if ($ledgerDelta) $counter['ledger']++;
                    if (!$execute) return;

                    $now = now();
                    $code = 'ADJ.STOCK.' . $inv->id . '.' . Str::upper(Str::random(10));
                    $head = DB::table('journal_heads')->insertGetId([
                        'code' => $code, 'post_date' => $now, 'typeable_id' => null,
                        'typeable_type' => null, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                    $base = [
                        'stockable_type' => $type, 'stockable_id' => $inv->id,
                        'reference_type' => null, 'reference_id' => null,
                        'second_reference_type' => null, 'second_reference_id' => null,
                        'journal_head_id' => $head, 'is_archive_summary' => 0,
                        'deleted_at' => null, 'created_at' => $now, 'updated_at' => $now,
                    ];
                    if ($dq) DB::table('stock_mutations')->insert(array_merge($base, [
                        'amount' => $dq, 'price' => $price, 'total' => $qtyValue,
                        'description' => 'Penyesuaian Stok',
                    ]));
                    if ($extraValue) DB::table('stock_mutations')->insert(array_merge($base, [
                        'amount' => 0, 'price' => $price, 'total' => $extraValue,
                        'description' => 'Penyesuaian Stok - Nilai',
                    ]));
                    $after = $this->mutationSum($type, $inv->id);
                    if ((int) $after->qty !== (int) $inv->in_stock || (int) $after->val !== (int) $inv->stock_value) {
                        throw new RuntimeException("Stock reconciliation failed inventory {$inv->id}");
                    }
                    if ($recalculate) {
                        // Explicit stock recalculation without Inventory::recalculateStock(),
                        // which also resets an accounting balance and loads all mutations.
                        $avg = (int) $after->qty !== 0
                            ? (int) (new Money((string) $after->val, new Currency(config('gudang.currency', 'IDR'))))
                                ->divide((string) $after->qty)->getAmount()
                            : 0;
                        DB::table('inventories')->where('id', $inv->id)->update([
                            'in_stock' => $after->qty, 'stock_value' => $after->val,
                            'avg_value' => $avg, 'updated_at' => $now,
                        ]);
                    }
                    if ($ledgerDelta) {
                        $amount = abs($ledgerDelta);
                        $memo = 'Penyesuaian Stok - GL; inventory ' . $inv->id;
                        $this->insertLedgerLine($head, $account->id, $ledgerDelta > 0 ? $amount : null,
                            $ledgerDelta < 0 ? $amount : null, $account->currency, $memo, $code, $now, $inv->id);
                        $this->insertLedgerLine($head, (int) $offset, $ledgerDelta < 0 ? $amount : null,
                            $ledgerDelta > 0 ? $amount : null, $account->currency, $memo, $code, $now, $inv->id);
                        foreach ([$account->id, (int) $offset] as $accountId) {
                            $s = DB::table('accounting_account_transactions')->where('account_id', $accountId)->whereNull('deleted_at')
                                ->selectRaw('COALESCE(SUM(credit),0) AS credit, COALESCE(SUM(debit),0) AS debit')->first();
                            // This ERP stores accounting_accounts.balance = credits - debits.
                            DB::table('accounting_accounts')->where('id', $accountId)->update([
                                'balance' => (int) $s->credit - (int) $s->debit, 'updated_at' => $now,
                            ]);
                        }
                        $s = DB::table('accounting_account_transactions')->where('account_id', $account->id)->whereNull('deleted_at')
                            ->selectRaw('COALESCE(SUM(debit),0) AS debit, COALESCE(SUM(credit),0) AS credit')->first();
                        if ((int) $s->debit - (int) $s->credit !== (int) $inv->stock_value)
                            throw new RuntimeException("Ledger reconciliation failed inventory {$inv->id}");
                    }
                };
                if ($execute) DB::transaction($work, 3); else $work();
            }
        });
        $this->info('Stock adjustments: ' . $counter['stock'] . '; GL adjustments: ' . $counter['ledger']);
        return 0;
    }

    private function mutationSum($type, $inventoryId)
    {
        return DB::table('stock_mutations')->where('stockable_type', $type)
            ->where('stockable_id', $inventoryId)->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(amount),0) AS qty, COALESCE(SUM(total),0) AS val')->first();
    }

    private function insertLedgerLine($head, $accountId, $debit, $credit, $currency, $memo, $code, $now, $inventoryId)
    {
        DB::table('accounting_account_transactions')->insert([
            'id' => (string) Str::uuid(), 'transaction_group' => null,
            'account_id' => $accountId, 'debit' => $debit, 'credit' => $credit,
            'currency' => $currency, 'memo' => $memo, 'tags' => null,
            'ref_class' => Inventory::class, 'ref_class_id' => $inventoryId,
            'created_at' => $now, 'updated_at' => $now, 'post_date' => $now,
            'deleted_at' => null, 'transaction_type' => null,
            'transaction_code' => $code, 'journal_head_id' => $head,
        ]);
    }
}
