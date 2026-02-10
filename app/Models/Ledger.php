<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Money\Money;
use Money\Currency;
use Carbon\Carbon;
use Kalnoy\Nestedset\NodeTrait;
/**
 * @property    Money $balance
 * @property    Carbon $updated_at
 * @property    Carbon $post_date
 * @property    Carbon $created_at
 */
class Ledger extends Model
{
    use NodeTrait;
        /**
     * @var string
     */
    protected $table = 'accounting_ledgers';
    protected $fillable = [
        'code',
        'name',
        'type',
        'status'
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get all of the posts for the country.
     */
    public function account_transactions(): HasManyThrough
    {
        return $this->hasManyThrough(AccountTransaction::class, Account::class);
    }

    public function getCurrentBalance(string $currency): Money
    {
        if ($this->type == 'asset' || $this->type == 'expense') {
            $balance = $this->account_transactions->sum('debit') - $this->account_transactions->sum('credit');
        } else {
            $balance = $this->account_transactions->sum('credit') - $this->account_transactions->sum('debit');
        }

        return new Money($balance, new Currency($currency));
    }

    public function getCurrentBalanceInDollars(): float
    {
        return $this->getCurrentBalance('IDR')->getAmount() / 100;
    }

    public function getTypeAttributes(): float
    {
        return $this->type;
    }

    public function open()
    {
        $this->status = 'open';
        $this->save();
    }

    public function close()
    {
        $this->status = 'closed';
        $this->save();
    }
}
