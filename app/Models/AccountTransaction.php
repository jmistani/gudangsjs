<?php


declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Money\Money;
use Money\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * Class AccountTransaction
 *
 * @property    string $account_id
 * @property    int $debit
 * @property    int $credit
 * @property    string $currency
 * @property    string memo
 * @property    \Carbon\Carbon $post_date
 * @property    \Carbon\Carbon $updated_at
 * @property    \Carbon\Carbon $created_at
 */
class AccountTransaction extends Model
{

    /**
     * @var string
     */
    protected $table = 'accounting_account_transactions';

    /**
     * Currency.
     *
     * @var string $currency
     */
    protected $currency;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $guarded=['id'];

    /**
     * @var array
     */
    protected $casts = [
        'post_date' => 'datetime',
        'tags' => 'array',
    ];

    /**
     * Boot.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($transaction) {
            $transaction->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        });

//        static::saved(function ($transaction) {
//            $transaction->account->resetCurrentBalances();
//        });

        static::deleted(function ($transaction) {
            $transaction->account->resetCurrentBalances();
        });

        parent::boot();
    }

    /**
     * NewAccount relation.
     */
    public function account()
    {
        return $this->belongsTo(NewAccount::class);
    }   
    /**
     * Set reference object.
     *
     * @param Model $object
     * @return AccountTransaction
     */
    public function referencesObject($object)
   {
        $this->ref_class    = get_class($object);
        $this->ref_class_id = $object->id;
        $this->save();
        return $this;
    }

    public function getDebitAttribute($value): Money
    {
        return new Money($value, new Currency(Config::get('gudang.currency')));
    }

    public function getCreditAttribute($value): Money
    {
        return new Money($value, new Currency(Config::get('gudang.currency')));
    }
    /**
     * Get reference object.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Model|Model[]|null
     */
    public function getReferencedObject()
    {
        /**
         * @var Model $_class
         */
        $_class = new $this->ref_class;
        return $_class->find($this->ref_class_id);
    }

    /**
     * Set currency.
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    public function scopeBetweenDate($date_start,$date_end)
    {
        return $this->whereDate('post_date', '>=', $date_start)
                ->whereDate('post_date', '<=', $date_end);
    }


    public function journalHead()
    {
        return $this->belongsTo(JournalHead::class,'journal_head_id');
    }

    public function assignToJournalHead(JournalHead $journalhead): void
    {
        $journalhead->accountTransactions()->save($this);
    }
    
}


