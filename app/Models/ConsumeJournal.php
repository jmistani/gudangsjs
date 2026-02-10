<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\StockMutation;
use App\Traits\ReferencedByStockMutations;
use App\Models\JournalHead;

class ConsumeJournal extends JournalHead
{
    use HasFactory, ReferencedByStockMutations;

    protected $fillable = ['receiver','giver'];
    protected $table = "consume_journals";
    protected $with = ['journalhead'];
     /**
     * @var array
     */
    protected $casts = [
        'post_date' => 'datetime',
    ];

    public function getPostDateAttribute($date)
    {
        return Carbon::parse($date)->setTimezone('Asia/Jakarta')->format('d-M-Y');;
    }

    public function stockMutations()
    {
        return $this->morphMany(StockMutation::class, 'second_reference');
    }

    public function nonstockInventories()
    {
        return $this->morphMany(NonStockInventory::class, 'reference');
    }

    public function scopeBetweenDate($date_start, $date_end)
    {
        return $this->whereBetween('post_date', $date_start, $date_end);
    }

    public function scopeUpToDate($date)
    {
        return $this->where('post_date', '<=', $date_end);
    }

    public function journalhead()
    {
        return $this->morphOne(JournalHead::class,'typeable');
    }

}
