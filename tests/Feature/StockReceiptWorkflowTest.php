<?php

namespace Tests\Feature;

use App\Http\Controllers\api\AdminReceiveJournalController;
use App\Models\Account;
use App\Models\Inventory;
use App\Models\JournalHead;
use App\Models\NonStockInventory;
use App\Models\ReceiveJournal;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockReceiptWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDatabaseName() !== 'gudangsjs_testing') {
            throw new \RuntimeException('Refusing to run outside gudangsjs_testing');
        }
        DB::beginTransaction();
        $admin = User::whereHas('roles', function ($query) {
            $query->whereRaw('LOWER(name) IN (?, ?)', ['super-admin', 'super admin']);
        })->first();
        if (!$admin) $this->markTestSkipped('No super-admin in copied test database');
        $this->actingAs($admin);
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) DB::rollBack();
        parent::tearDown();
    }

    private function fixture(): array
    {
        $token = uniqid('STOCK-');
        $receipt = ReceiveJournal::create(['giver' => 'TEST VENDOR', 'receiver' => 'TEST USER']);
        $head = new JournalHead(['code' => $token, 'post_date' => now()]);
        $receipt->journalhead()->save($head);
        $inventory = Inventory::create(['code' => $token, 'name' => $token, 'unit' => 'pcs',
            'in_stock' => 2, 'stock_value' => 2000, 'avg_value' => 1000]);
        $account = new Account(['code' => $token, 'name' => $token, 'type' => 'asset']);
        $account->currency = 'IDR';
        $account->balance = 0;
        $inventory->account()->save($account);
        $line = $inventory->increaseStock(2, ['price' => 1000, 'total' => 2000, 'description' => 'test']);
        $line->assignToReceiveJournal($receipt);
        $line->assignToJournalHead($head);
        $debit = $account->addDebit(2000, $inventory->name, now(), null, null, $token, $head->id);
        $debit->referencesObject($inventory);
        $payableAccount = Account::where('id', '!=', $account->id)->firstOrFail();
        $payable = $payableAccount->addCredit(2000, 'TEST PAYABLE', now(), null, null, $token, $head->id);
        $payable->referencesObject($receipt);
        return compact('receipt', 'head', 'inventory', 'account', 'line', 'debit', 'payable', 'payableAccount');
    }

    private function save(array $f, array $stockChanges = [], array $deletedStockIds = [], array $extraItems = [], array $deletedNonstockIds = []): void
    {
        $items = $deletedStockIds ? [] : [array_merge([
            'type' => 'stok', 'id' => $f['line']->id, 'name' => $f['inventory']->name,
            'unit' => 'pcs', 'qty' => 2, 'price' => 1000, 'memo' => 'test',
        ], $stockChanges)];
        $items = array_merge($items, $extraItems);
        app(AdminReceiveJournalController::class)->update(Request::create('/api/admin/inventory-receives/'.$f['receipt']->id, 'PUT', [
            'giver' => 'TEST VENDOR', 'receiver' => 'TEST USER',
            'items' => $items, 'deleted_stock_ids' => $deletedStockIds,
            'deleted_nonstock_ids' => $deletedNonstockIds,
        ]), $f['receipt']->id);
    }

    private function assertBalanced(array $f): void
    {
        $this->assertEquals(
            DB::table('accounting_account_transactions')->where('transaction_code', $f['head']->code)->sum('debit'),
            DB::table('accounting_account_transactions')->where('transaction_code', $f['head']->code)->sum('credit')
        );
    }

    public function test_quantity_edit_updates_stock_valuation_and_ledger(): void
    {
        $f = $this->fixture();
        $this->save($f, ['qty' => 3]);
        $this->assertSame(3, (int) $f['inventory']->fresh()->getRawOriginal('in_stock'));
        $this->assertSame(3000, (int) $f['inventory']->fresh()->getRawOriginal('stock_value'));
        $this->assertSame(1000, (int) $f['inventory']->fresh()->getRawOriginal('avg_value'));
        $this->assertSame(3000, (int) $f['line']->fresh()->getRawOriginal('total'));
        $this->assertSame(3000, (int) $f['debit']->fresh()->getRawOriginal('debit'));
        $this->assertSame(3000, (int) $f['payable']->fresh()->getRawOriginal('credit'));
        $this->assertBalanced($f);
    }

    public function test_price_edit_updates_weighted_average_and_ledger(): void
    {
        $f = $this->fixture();
        $this->save($f, ['price' => 1500]);
        $this->assertSame(3000, (int) $f['inventory']->fresh()->getRawOriginal('stock_value'));
        $this->assertSame(1500, (int) $f['inventory']->fresh()->getRawOriginal('avg_value'));
        $this->assertSame(3000, (int) $f['debit']->fresh()->getRawOriginal('debit'));
        $this->assertSame(3000, (int) $f['payable']->fresh()->getRawOriginal('credit'));
        $this->assertBalanced($f);
    }

    public function test_staged_deletion_updates_inventory_and_payable_on_save(): void
    {
        $f = $this->fixture();
        $other = NonStockInventory::create(['name' => 'TEST OTHER', 'amount' => 1, 'price' => 100,
            'subtotal' => 100, 'unit' => 'pcs']);
        $other->assignToReceiveJournal($f['receipt']);
        $other->assignToJournalHead($f['head']);
        $expenseAccount = Account::where('id', '!=', $f['account']->id)->firstOrFail();
        $expense = $expenseAccount->addDebit(100, 'TEST OTHER', now(), null, null, $f['head']->code, $f['head']->id);
        $expense->referencesObject($other);
        $f['payable']->credit = 2100;
        $f['payable']->save();
        $this->assertNotNull($f['line']->fresh()); // Staged in UI: no API call before Save.
        $this->save($f, [], [$f['line']->id], [[
            'type' => 'non-stok', 'id' => $other->id, 'name' => 'TEST OTHER',
            'unit' => 'pcs', 'qty' => 1, 'price' => 100, 'memo' => '',
        ]]);
        $this->assertNull($f['line']->fresh());
        $this->assertNotNull(StockMutation::withTrashed()->find($f['line']->id));
        $this->assertNull($f['debit']->fresh());
        $this->assertSame(0, (int) $f['inventory']->fresh()->getRawOriginal('in_stock'));
        $this->assertSame(0, (int) $f['inventory']->fresh()->getRawOriginal('stock_value'));
        $this->assertSame(100, (int) $f['payable']->fresh()->getRawOriginal('credit'));
        $this->assertBalanced($f);
    }

    public function test_later_stock_mutation_blocks_correction_and_rolls_back(): void
    {
        $f = $this->fixture();
        $f['inventory']->decreaseStock(1, ['price' => 1000, 'total' => -1000]);
        try {
            $this->save($f, ['qty' => 3]);
            $this->fail('Expected validation exception');
        } catch (ValidationException $expected) {
            $this->assertSame(2, (int) $f['line']->fresh()->getRawOriginal('amount'));
            $this->assertSame(2000, (int) $f['debit']->fresh()->getRawOriginal('debit'));
            $this->assertSame(2000, (int) $f['payable']->fresh()->getRawOriginal('credit'));
        }
    }

    public function test_invalid_payable_rolls_back_stock_edit(): void
    {
        $f = $this->fixture();
        $f['payable']->credit = 100;
        $f['payable']->save();
        try {
            $this->save($f, ['qty' => 1]);
            $this->fail('Expected validation exception');
        } catch (ValidationException $expected) {
            $this->assertSame(2, (int) $f['line']->fresh()->getRawOriginal('amount'));
            $this->assertSame(2000, (int) $f['inventory']->fresh()->getRawOriginal('stock_value'));
            $this->assertSame(2000, (int) $f['debit']->fresh()->getRawOriginal('debit'));
        }
    }

    public function test_fractional_stock_quantity_is_rejected(): void
    {
        $f = $this->fixture();
        $this->expectException(ValidationException::class);
        $this->save($f, ['qty' => 2.5]);
    }
}
