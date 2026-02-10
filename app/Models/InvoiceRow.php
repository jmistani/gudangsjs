<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',     //inventory, service/cost-item
        'qty',
        'price',
        'total-item',
        'subtotal',
        'discount',
        'tax',
        'other chargers',
        'total'
    ];
}
