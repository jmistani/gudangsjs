<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Traits\AccountingAccount;
use App\Traits\HasStock;
use App\Traits\HasImage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Money\Money;
use Money\Currency;
use Illuminate\Support\Facades\Config;
use App\Models\ConsumeJournal;
use App\Models\StockMutation;
use App\Models\JournalHead;

class Inventory extends Model implements HasMedia
{
    use HasStock, AccountingAccount, SoftDeletes, InteractsWithMedia;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

    protected $fillable = [ 'code', 'name', 'category', 'unit', 'stock_value', 'avg_value', 'in_stock'];
    protected $table = 'inventories';
    protected $softCascade = ['stockMutations'];

    public static function getTableName()
    {
        return (new self())->getTable();
    }

    public function group()
    {
        return $this->belongsTo(InventoryGroup::class,'inventory_group_id');
    }


    public function recalculateStock()
    {
        $stock = 0;
        $avgvalue = Money::IDR(0);
        $totalvalue = Money::IDR(0);
        foreach($this->stockMutations as $sm) {
            $stock += $sm->amount;
            $totalvalue =  $totalvalue->add($sm->price->multiply($sm->amount));
        }
        if($stock != 0) {   
            $avgvalue = $totalvalue->divide($stock); 
        }
        else {
            $avgvalue = Money::IDR(0);
        }
        $this->in_stock = $stock;
        $this->stock_value = $totalvalue->getAmount();
        $this->account->resetCurrentBalances();
        $this->avg_value = $avgvalue->getAmount();
        $this->save();
    }

    public function addStock($date, $quantity, $price, $total, $memo = null, $type, $code)
    {
        $journalhead = JournalHead::with('typeable')->where('code',$code)->first();
        $transaction = $this->account->addDebit( $total, $memo, $date, (integer)$type, null, $code, $journalhead->id);
        $transaction->referencesObject($this);
        $transaction->assignToJournalHead($journalhead);
        return $this->increaseStock($quantity,array("description" => $memo, "price" => $price, "total" => $total));
    }

    public function subtractStock($date, $quantity, $price, $total, $memo = null, $type, $code)
    {
        $journalhead = JournalHead::with('typeable')->where('code',$code)->first();
        $transaction = $this->account->addCredit($total, $memo, $date, (integer)$type, null, $code, $journalhead->id);
        $transaction->referencesObject($this);
        $transaction->assignToJournalHead($journalhead);
        return $this->decreaseStock($quantity,array("description" => $memo, "price" => $price, "total" => (-1*$total)));
    }

    public function getAvgValueAttribute($value): Money
    {
        return new Money($value, new Currency(Config::get('gudang.currency')));
    }

    public function getStockValueAttribute($value): Money
    {
        return new Money($value, new Currency(Config::get('gudang.currency')));
    }

    public function getInStockAttribute($value)
    {
        return $value;
    }

    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    public static function boot() 
    {
  
        parent::boot();
  
        /**
         * Write code on Method
         *
         * @return response()
         */
        static::creating(function($item) {            
//            Log::info('Creating event call: '.$item); 
        });
  
        /** 
         * Write code on Method
         *
         * @return response()
         */
        static::created(function($item) {           
            /*
                Write Logic Here
            */ 
            Log::info('Created event call: '.$item);

        });
  
        /**
         * Write code on Method
         *
         * @return response()
         */
        static::updating(function($item) {            
            Log::info('Updating event call: '.$item);   
        });
  
        /**
         * Write code on Method
         *
         * @return response()
         */
        static::updated(function($item) {  
            /*
                Write Logic Here
            */    
  
            Log::info('Updated event call: '.$item);
        });
  
        /**
         * Write code on Method
         *
         * @return response()
         */
        static::deleted(function($item) {            
            Log::info('Deleted event call: '.$item); 
        });
    }
}
