<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AccountingAccount;
use App\Traits\ReferencedByStockMutations;
use App\Models\StockMutation;

class CompanyCoa extends Model
{
    use AccountingAccount, ReferencedByStockMutations;
    
    protected $table = "company_coas";
    public $timestamps = true;
    protected $fillable = [
        'code',
        'name',
        'type',
    ];

    protected $with=['account'];

    public function scopeExpenses($query)
    {
        return $query->where('type','expense');
    }

    public function scopeHPPs($query)
    {
        return $query->where('type','hpp');
    }
}
