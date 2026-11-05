<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\JournalHead;
use App\Models\NonStockInventory;
use App\Models\ReceiveJournal;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Run only against the disposable gudangsjs_testing database:
 * php artisan test --env=testing --filter=NonStockExpenseAccountingTest
 *
 * No RefreshDatabase, migrations, or persistent fixture changes are performed.
 * Each test is wrapped in a transaction and rolled back.
 */
class NonStockExpenseAccountingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDatabaseName() !== 'gudangsjs_testing') {
            throw new \RuntimeException('Refusing to run: DB must be gudangsjs_testing.');
        }

        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function fixture(bool $legacy = false, bool $duplicate = false): array
    {
        $expenseAccount = Account::query()->firstOrFail();
        $payableAccount = Account::query()->where('id', '!=', $expenseAccount->id)->firstOrFail();

        $receipt = ReceiveJournal::create(['giver' => 'TEST VENDOR', 'receiver' => 'TEST USER']);
        $head = new JournalHead();
        $head->code = 'TEST-NONSTOCK-'.uniqid();
        $head->post_date = now();
        $receipt->journalhead()->save($head);

        $line = NonStockInventory::create([
            'name' => 'TEST NONSTOCK EXPENSE',
            'amount' => 2,
            'price' => 1000,
            'subtotal' => 2000,
            'unit' => 'pcs',
            'memo' => 'automated test',
        ]);
        $line->assignToReceiveJournal($receipt);
        $line->assignToJournalHead($head);

        $expense = $expenseAccount->addDebit(2000, $line->name, now(), null, null, $head->code, $legacy ? null : $head->id);
        $expense->referencesObject($legacy ? $head : $line);

        $payable = $payableAccount->addCredit(2000, 'TEST PAYABLE', now(), null, null, $head->code, $head->id);
        $payable->referencesObject($receipt);

        if ($duplicate) {
            $extra = $expenseAccount->addDebit(2000, $line->name, now(), null, null, $head->code);
            $extra->referencesObject($head);
        }

        return compact('receipt', 'head', 'line', 'expense', 'payable', 'expenseAccount', 'payableAccount');
    }

    private function controller()
    {
        return app(\App\Http\Controllers\api\AdminReceiveJournalController::class);
    }

    private function invokePrivate(string $method, array $args)
    {
        $reflection = new \ReflectionMethod(get_class($this->controller()), $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs($this->controller(), $args);
    }

    public function test_new_expense_is_matched_by_exact_line(): void
    {
        $f = $this->fixture();
        $found = $this->invokePrivate('findNonStockExpense', [$f['head'], $f['line']]);
        $this->assertSame($f['expense']->id, $found->id);
        $this->assertSame(NonStockInventory::class, $found->ref_class);
    }

    public function test_legacy_expense_without_journal_head_id_is_matched(): void
    {
        $f = $this->fixture(true);
        $found = $this->invokePrivate('findNonStockExpense', [$f['head'], $f['line']]);
        $this->assertSame($f['expense']->id, $found->id);
    }

    public function test_ambiguous_legacy_expense_is_rejected(): void
    {
        $f = $this->fixture(true, true);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->invokePrivate('findNonStockExpense', [$f['head'], $f['line']]);
    }

    public function test_payable_is_matched_to_receipt(): void
    {
        $f = $this->fixture();
        $found = $this->invokePrivate('findReceiptPayable', [$f['head'], $f['receipt']]);
        $this->assertSame($f['payable']->id, $found->id);
    }

    public function test_original_fixture_is_balanced(): void
    {
        $f = $this->fixture();
        $this->assertSame(
            (int) $f['expense']->getRawOriginal('debit'),
            (int) $f['payable']->getRawOriginal('credit')
        );
    }
}
