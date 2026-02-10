<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\StockMutation;
use App\Models\NonstockInventories;
use App\Models\JournalHead;

class ReceiveJournal extends JournalHead
{
    use HasFactory;

    protected $fillable = ['receiver','giver'];
    protected $table = "receive_journals";
    /**
     * @var array
     */
    protected $casts = [
        'post_date' => 'datetime',
    ];

    public function stockMutations()
    {
        return $this->morphMany(StockMutation::class, 'second_reference');
    }

    public function nonstockInventories()
    {
        return $this->hasMany(NonStockInventory::class);
    }

    public function getPostDateAttribute($date)
    {
//        return Carbon::parse($date)->setTimezone('Asia/Jakarta')->format('d-M-Y');;
	return $this->journalhead->post_date;
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
