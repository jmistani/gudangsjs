<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Money\Money;
use Money\Currency;
use Carbon\Carbon;
use App\Models\Ledger;

/**
 * @property    Money $balance
 * @property    string $currency
 * @property    Carbon $updated_at
 * @property    Carbon $post_date
 * @property    Carbon $created_at
 */
class Account extends Model
{
    /**
     * @var string
     */
    protected $table = 'accounting_accounts';
    protected $fillable = ['code','name','type'];

    public function morphed(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeExpenses($query)
    {
	return $query->whereRelation('morphed','type','like','%');
    }

    public function scopeInventory($query)
    {
        return $query->where('morphed_type', 'App\Models\Inventory');
    }

    public function scopeVendorPayable($query)
    {
        return $query->where('morphed_type', 'App\Models\Vendor')
                    ->where('code','LIKE','%'.'HTG'.'%');
    }

    public function scopeVendorReceivable($query)
    {
        return $query->where('morphed_type', 'App\Models\Vendor')
                    ->where('code', 'LIKE', '%'.'PTG'.'%');
    }

    public function addCredit(
        $value,
        string $memo = null,
        Carbon $post_date = null,
        int $transaction_type = null,
        string $transaction_group = null,
        $transaction_code = null,
        $journal_head_id = null
    ): AccountTransaction {
        $value = is_a($value, Money::class)
            ? $value
            : new Money($value, new Currency($this->currency));
        return $this->doPost($value, null, $memo, $post_date, $transaction_type, $transaction_group, $transaction_code, $journal_head_id);
    }


    public function addDebit(
        $value,
        string $memo = null,
        Carbon $post_date = null,
        int $transaction_type = null,
        string $transaction_group = null,
        $transaction_code = null,
        $journal_head_id = null
    ): AccountTransaction {

        $value = is_a($value, Money::class)
            ? $value
            : new Money($value, new Currency($this->currency));
        return $this->doPost(null, $value, $memo, $post_date, $transaction_type, $transaction_group, $transaction_code, $journal_head_id);
    }

    private function doPost(
        Money $credit = null,
        Money $debit = null,
        string $memo = null,
        Carbon $post_date = null,
        String $transaction_type = null,
        string $transaction_group = null,
        $transaction_code = null,
        $journal_head_id = null
    ): AccountTransaction {
        $transaction = new AccountTransaction;
        $transaction->credit = $credit ? $credit->getAmount() : null;
        $transaction->debit = $debit ? $debit->getAmount() : null;
        $currency_code = $credit
            ? $credit->getCurrency()->getCode()
            : $debit->getCurrency()->getCode();
        $transaction->memo = $memo;
        $transaction->currency = $currency_code;
        $transaction->post_date = $post_date ?: Carbon::now();
        $transaction->transaction_type = $transaction_type;
        $transaction->transaction_group = $transaction_group;
        $transaction->transaction_code = $transaction_code;
        $transaction->journal_head_id = $journal_head_id;
        $this->transactions()->save($transaction);
        $this->resetCurrentBalances();
        return $transaction;
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'updated_at'
    ];

    protected static function boot()
    {
        parent::boot();
        static::created(function (Account $account) {
            $account->resetCurrentBalances();
        });

        parent::boot();
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function assignToLedger(Ledger $ledger): void
    {
        $ledger->accounts()->save($this);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class,'account_id');
    }

    public function resetCurrentBalances(): Money
    {
        $this->balance = $this->getBalance();
        $this->save();
        return $this->balance;
    }

    /**
     * @param Money|float $value
     */
    protected function getBalanceAttribute($value): Money
    {
        return new Money($value, new Currency($this->currency));
    }

    /**
     * @param Money|float $value
     */
    protected function setBalanceAttribute($value): void
    {
        $value = is_a($value, Money::class)
            ? $value
            : new Money($value, new Currency($this->currency));
        $this->attributes['balance'] = $value ? (int)$value->getAmount() : null;
    }

    /**
     * Get the debit only balance of the account based on a given date.
     */
    public function getDebitBalanceOn(Carbon $date): Money
    {
        $balance = $this->transactions()->where('post_date', '<=', $date)->sum('debit') ?: 0;
        return new Money($balance, new Currency($this->currency));

    }

    public function transactionsReferencingObjectQuery(Model $object): HasMany
    {
        return $this
            ->transactions()
            ->where('ref_class', get_class($object))
            ->where('ref_class_id', $object->id);
    }

    /**
     * Get the credit only balance of the account based on a given date.
     */
    public function getCreditBalanceOn(Carbon $date): Money
    {
        $balance = $this->transactions()->where('post_date', '<=', $date)->sum('credit') ?: 0;
        return new Money($balance, new Currency($this->currency));
    }

    /**
     * Get the balance of the account based on a given date.
     */
    public function getBalanceOn(Carbon $date): Money
    {
        return $this->getCreditBalanceOn($date)->subtract($this->getDebitBalanceOn($date));
    }

    /**
     * Get the balance of the account as of right now, excluding future transactions.
     */
    public function getCurrentBalance(): Money
    {
        return $this->getBalanceOn(Carbon::now());
    }

    /**
     * Get the balance of the account.  This "could" include future dates.
     */
    public function getBalance(): Money
    {
        if ($this->transactions()->count() > 0) {
            $balance = $this->transactions()->sum('credit') - $this->transactions()->sum('debit');
        } else {
            $balance = 0;
        }

        return new Money($balance, new Currency($this->currency));
    }

    /**
     * Get the balance of the account in dollars.  This "could" include future dates.
     * @return float|int
     */
    public function getCurrentBalanceInDollars()
    {
        return $this->getCurrentBalance()->getAmount() / 100;
    }

    /**
     * Get balance
     * @return float|int
     */
    public function getBalanceInDollars()
    {
        return $this->getBalance()->getAmount() / 100;
    }

    public function credit(
        $value,
        string $memo = null,
        Carbon $post_date = null,
        string $transaction_group = null
    ): AccountTransaction {
        $value = is_a($value, Money::class)
            ? $value
            : new Money($value, new Currency($this->currency));
        return $this->post($value, null, $memo, $post_date, $transaction_group);
    }

    public function debit(
        $value,
        string $memo = null,
        Carbon $post_date = null,
        $transaction_group = null
    ): AccountTransaction {
        $value = is_a($value, Money::class)
            ? $value
            : new Money($value, new Currency($this->currency));
        return $this->post(null, $value, $memo, $post_date, $transaction_group);
    }

    /**
     * Credit a account by a given dollar amount
     * @param Money|float $value
     * @param string  $memo
     * @param Carbon $post_date
     * @return AccountTransaction
     */
    public function creditDollars($value, string $memo = null, Carbon $post_date = null): AccountTransaction
    {
        $value = (int)($value * 100);
        return $this->credit($value, $memo, $post_date);
    }

    /**
     * Debit a account by a given dollar amount
     * @param Money|float $value
     * @param string $memo
     * @param Carbon $post_date
     * @return AccountTransaction
     */
    public function debitDollars($value, string $memo = null, Carbon $post_date = null): AccountTransaction
    {
        $value = (int)($value * 100);
        return $this->debit($value, $memo, $post_date);
    }

    /**
     * Calculate the dollar amount debited to a account today
     * @return float|int
     */
    public function getDollarsDebitedToday()
    {
        $today = Carbon::now();
        return $this->getDollarsDebitedOn($today);
    }

    /**
     * Calculate the dollar amount credited to a account today
     * @return float|int
     */
    public function getDollarsCreditedToday()
    {
        $today = Carbon::now();
        return $this->getDollarsCreditedOn($today);
    }

    /**
     * Calculate the dollar amount debited to a account on a given day
     * @param Carbon $date
     * @return float|int
     */
    public function getDollarsDebitedOn(Carbon $date)
    {
        return $this
                ->transactions()
                ->whereBetween('post_date', [
                    $date->copy()->startOfDay(),
                    $date->copy()->endOfDay()
                ])
                ->sum('debit') / 100;
    }

    /**
     * Calculate the dollar amount credited to a account on a given day
     * @param Carbon $date
     * @return float|int
     */
    public function getDollarsCreditedOn(Carbon $date)
    {
        return $this
                ->transactions()
                ->whereBetween('post_date', [
                    $date->copy()->startOfDay(),
                    $date->copy()->endOfDay()
                ])
                ->sum('credit') / 100;
    }

    private function post(
        Money $credit = null,
        Money $debit = null,
        string $memo = null,
        Carbon $post_date = null,
        string $transaction_group = null
    ): AccountTransaction {
        $transaction = new AccountTransaction;
        $transaction->credit = $credit ? $credit->getAmount() : null;
        $transaction->debit = $debit ? $debit->getAmount() : null;
        $currency_code = $credit
            ? $credit->getCurrency()->getCode()
            : $debit->getCurrency()->getCode();
        $transaction->memo = $memo;
        $transaction->currency = $currency_code;
        $transaction->post_date = $post_date ?: Carbon::now();
        $transaction->transaction_group = $transaction_group;
        $this->transactions()->save($transaction);
        return $transaction;
    }
}

