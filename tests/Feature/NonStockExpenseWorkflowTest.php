<?php

namespace Tests\Feature;

use App\Http\Controllers\api\AdminReceiveJournalController;
use App\Models\Account;
use App\Models\JournalHead;
use App\Models\NonStockInventory;
use App\Models\ReceiveJournal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Full non-stock accounting workflow tests against the isolated copied database.
 * Each test rolls back its fixtures and all accounting changes.
 */
class NonStockExpenseWorkflowTest extends TestCase
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
        if (!$admin) {
            $this->markTestSkipped('No super-admin in copied test database');
        }
        $this->actingAs($admin);
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) DB::rollBack();
        parent::tearDown();
    }

    private function fixture(int $lineCount = 1, bool $legacy = false): array
    {
        $expenseAccount = Account::firstOrFail();
        $payableAccount = Account::where('id', '!=', $expenseAccount->id)->firstOrFail();
        $receipt = ReceiveJournal::create(['giver' => 'TEST VENDOR', 'receiver' => 'TEST USER']);
        $head = new JournalHead();
        $head->code = 'TEST-WORKFLOW-'.uniqid();
        $head->post_date = now();
        $receipt->journalhead()->save($head);
        $lines = [];
        $expenses = [];
        for ($i = 0; $i < $lineCount; $i++) {
            $line = NonStockInventory::create([
                'name' => 'TEST EXPENSE '.$i, 'amount' => 2, 'price' => 1000,
                'subtotal' => 2000, 'unit' => 'pcs', 'memo' => 'test',
            ]);
            $line->assignToReceiveJournal($receipt);
            $line->assignToJournalHead($head);
            $expense = $expenseAccount->addDebit(2000, $line->name, now(), null, null, $head->code, $legacy ? null : $head->id);
            $expense->referencesObject($legacy ? $head : $line);
            $lines[] = $line;
            $expenses[] = $expense;
        }
        $payable = $payableAccount->addCredit(2000 * $lineCount, 'TEST PAYABLE', now(), null, null, $head->code, $head->id);
        $payable->referencesObject($receipt);
        return compact('receipt', 'head', 'lines', 'expenses', 'payable', 'expenseAccount', 'payableAccount');
    }

    private function updateReceipt(array $fixture, array $changes = []): void
    {
        $items = [];
        foreach ($fixture['lines'] as $line) {
            $items[] = array_merge([
                'type' => 'non-stok', 'id' => $line->id, 'name' => $line->name,
                'unit' => 'pcs', 'qty' => 2, 'price' => 1000, 'memo' => 'test',
            ], $changes[$line->id] ?? []);
        }
        app(AdminReceiveJournalController::class)->update(
            Request::create('/api/admin/inventory-receives/'.$fixture['receipt']->id, 'PUT', [
                'giver' => 'TEST VENDOR', 'receiver' => 'TEST USER', 'items' => $items,
            ]),
            $fixture['receipt']->id
        );
    }

    private function assertBalanced(array $f): void
    {
        $debits = DB::table('accounting_account_transactions')
            ->where('transaction_code', $f['head']->code)->sum('debit');
        $credits = DB::table('accounting_account_transactions')
            ->where('transaction_code', $f['head']->code)->sum('credit');
        $this->assertEquals($debits, $credits);
    }

    public function test_quantity_edit_updates_expense_and_payable(): void
    {
        $f = $this->fixture();
        $this->updateReceipt($f, [$f['lines'][0]->id => ['qty' => 3]]);
        $this->assertSame(3000, (int) $f['lines'][0]->fresh()->getRawOriginal('subtotal'));
        $this->assertSame(3000, (int) $f['expenses'][0]->fresh()->getRawOriginal('debit'));
        $this->assertSame(3000, (int) $f['payable']->fresh()->getRawOriginal('credit'));
        $this->assertBalanced($f);
    }

    public function test_price_edit_updates_expense_and_payable(): void
    {
        $f = $this->fixture();
        $this->updateReceipt($f, [$f['lines'][0]->id => ['price' => 1500]]);
        $this->assertSame(3000, (int) $f['expenses'][0]->fresh()->getRawOriginal('debit'));
        $this->assertSame(3000, (int) $f['payable']->fresh()->getRawOriginal('credit'));
        $this->assertBalanced($f);
    }

    public function test_deleting_one_line_removes_its_expense_and_reduces_payable(): void
    {
        $f = $this->fixture(2);
        app(AdminReceiveJournalController::class)->deleteItem($f['receipt']->id, 'non-stok', $f['lines'][0]->id);
        $this->assertNull($f['lines'][0]->fresh());
        $this->assertNull($f['expenses'][0]->fresh());
        $this->assertSame(2000, (int) $f['payable']->fresh()->getRawOriginal('credit'));
        $this->assertBalanced($f);
    }

    public function test_multiple_row_edits_adjust_payable_by_total_delta(): void
    {
        $f = $this->fixture(2);
        $this->updateReceipt($f, [
            $f['lines'][0]->id => ['qty' => 3],
            $f['lines'][1]->id => ['price' => 500],
        ]);
        $this->assertSame(3000, (int) $f['expenses'][0]->fresh()->getRawOriginal('debit'));
        $this->assertSame(1000, (int) $f['expenses'][1]->fresh()->getRawOriginal('debit'));
        $this->assertSame(4000, (int) $f['payable']->fresh()->getRawOriginal('credit'));
        $this->assertBalanced($f);
    }

    public function test_invalid_negative_payable_rolls_back_every_change(): void
    {
        $f = $this->fixture();
        // Simulate a payable whose current credit is insufficient for the reduction.
        $f['payable']->credit = 100;
        $f['payable']->save();

        try {
            $this->updateReceipt($f, [$f['lines'][0]->id => ['qty' => 1]]);
            $this->fail('Expected a validation exception');
        } catch (ValidationException $expected) {
            $this->assertSame(2000, (int) $f['lines'][0]->fresh()->getRawOriginal('subtotal'));
            $this->assertSame(2000, (int) $f['expenses'][0]->fresh()->getRawOriginal('debit'));
            $this->assertSame(100, (int) $f['payable']->fresh()->getRawOriginal('credit'));
        }
    }
}
