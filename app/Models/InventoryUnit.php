<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryUnit extends Model
{

    protected $guarded = ['id'];
    protected $fillable = [
        'name',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
