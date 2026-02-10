<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'vendor',
        'date',
        'tax',
        'discount',
        'other-charges',
        'row-total',
        'total',
        'image'
    ];

    public function stockMutations(): BelongToMany
    {
        return $this->belongsToMany(AccountTransaction::class,'invoice_stock_mutation', 'invoice_id', 'stock_mutation_id');
    }
}
