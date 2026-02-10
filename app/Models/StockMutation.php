<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Money\Money;
use Money\Currency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class StockMutation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'stockable_type',
        'stockable_id',
        'reference_type',
        'reference_id',
        'second_reference_type',
        'second_reference_id',
        'amount',
        'price',
        'total',
        'description',
    ];

    protected $with=['reference','second_reference'];

    /**
     * StockMutation constructor.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('stock.table', 'stock_mutations'));
    }

    /**
     * Relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function stockable()
    {
        return $this->morphTo();
    }

    /**
     * Relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function reference()
    {
        return $this->morphTo();
    }

    public function second_reference()
    {
        return $this->morphTo();
    }

    public function invoices(): HasMany
    {
        return $this->hasOne(Invoice::class,'invoice_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class,'ticket_id');
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->setTimezone('Asia/Jakarta')->format('d-M-Y H:i:s');;
    }

    public function getUpdateAtAttribute($date)
    {
        return Carbon::parse($date)->setTimezone('Asia/Jakarta')->format('d-M-Y H:i:s');;
    }

    public function journalHead()
    {
        return $this->belongsTo(JournalHead::class,'journal_head_id');
    }

    public function assignToJournalHead(JournalHead $journalhead): void
    {
        $journalhead->stockMutations()->save($this);
    }
    
    public function assignToConsumeJournal(ConsumeJournal $consumeJournal): void
    {
        $consumeJournal->stockMutations()->save($this);
    }

    public function assignToReceiveJournal(ReceiveJournal $receiveJournal): void
    {
        $receiveJournal->stockMutations()->save($this);
    }

    public function assignToTicket(Ticket $ticket): void
    {
        $this->second_reference()->associate($ticket);
        $this->save();
    }

    public function assignReference($reference) : void
    {
	$this->reference()->associate($reference);
	$this->save();
    }

    public function getTotalAttribute($value): Money
    {
        return new Money($value, new Currency(Config::get('gudang.currency')));
    }

    public function getPriceAttribute($value): Money
    {
        return new Money($value, new Currency(Config::get('gudang.currency')));
    }


    public function scopeReceives($query)
    {
        return $query->where('second_reference_type', 'App\Models\ReceiveJournal');
    }

    public function scopeConsumes($query)
    {
        return $query->where('second_reference_type', 'App\Models\ConsumeJournal');
    }

    public function getSumbyGroup(Request $request)
    {
        $data = DB::table('stock_mutations')->with('reference')
            ->groupBy($request->group)
            ->sum('in_stock', 'stock_value')
            ->get();
        
        return $data;
    }
}
