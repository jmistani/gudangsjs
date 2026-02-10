<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AccountingAccount;
use App\Models\StockMutation;
use App\Models\inventoryConsume;
use App\Traits\SecondReferencedByStockMutations;

class Ticket extends Model
{
    use SecondReferencedByStockMutations;

    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'type',
        'nopolisi',
        'balance',
        'memo',
        'status',
    ];

    public function close() {
        $this->status = 'closed';
    }

    public function open() {
        $this->status = 'open';
    }

    public function addStockMutation(StockMutation $sm) :void {
        $this->associate($sm)->save();
    }
}
