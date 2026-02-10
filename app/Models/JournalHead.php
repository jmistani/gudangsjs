<?php



namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Money\Money;
use Money\Currency;
use Carbon\Carbon;
use Kalnoy\Nestedset\NodeTrait;

class JournalHead extends Model
{

   protected $fillable = ['code','post_date'];
   protected $casts = [
       'post_date' => 'datetime',
   ];
   protected $table='journal_heads';

   public function name()
   {
      return (new ReflectionClasss($this))->getShortName();
   }

   public function typeable()
   {
      return $this->morphTo();
   }

   public function getPostDateAttribute($date)
   {
       return Carbon::parse($date)->setTimezone('Asia/Jakarta')->format('d-M-Y');;
   }

   public function assignToReceiveJournal(ReceiveJournal $receiveJournal): void
   {
       $receiveJournal->journalhead()->save($this);
   }

   public function assignToConsumeJournal(ConsumeJournal $consumeJournal): void
   {
       $consumeJournal->journalhead()->save($this);
   }

   public function stockMutations()
   {
      return $this->hasMany(StockMutation::class);
   }

   public function nonstockInventories()
   {
       return $this->hasMany(NonStockInventory::class);
   }

   public function accountTransactions()
   {
       return $this->hasMany(AccountTransaction::class);
   }

   public function scopeBetweenDate($date_start, $date_end)
   {
       return $this->whereBetween('post_date', $date_start, $date_end);
   }

   public function scopeUpToDate($date)
   {
       return $this->where('post_date', '<=', $date_end);
   }
   public function account_transactions()
   {
      return $this->morphToMany(AccountTransaction::class,'ref_class');
   }
}

