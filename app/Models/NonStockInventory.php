<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\StockMutation;
use App\Traits\ReferencedByStockMutations;
use Money\Money;
use Money\Currency;
use Illuminate\Support\Facades\Config;

class NonStockInventory extends Model
{
    use HasFactory, ReferencedByStockMutations;

    protected $fillable = ['name','amount','price','unit','subtotal','memo'];
    protected $table = "nonstock_inventories";

    public function reference()
    {
        return $this->morphTo();
    }

    public function journalHead()
    {
        return $this->belongsTo(JournalHead::class,'journal_head_id');
    }

    public function assignToReceiveJournal(ReceiveJournal $inventoryReceive): void
    {
        $inventoryReceive->nonstockInventories()->save($this);
    }
    
    public function assignToJournalHead(JournalHead $journalhead): void
    {
        $journalhead->nonstockInventories()->save($this);
    }
    
    public function getPriceAttribute($value): Money
    {
        return new Money($value, new Currency(Config::get('gudang.currency')));
    }

    public function getSubtotalAttribute($value): Money
    {
        return new Money($value, new Currency(Config::get('gudang.currency')));
    }


}
