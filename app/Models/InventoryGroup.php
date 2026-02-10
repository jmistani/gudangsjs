<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryGroup extends Model
{

    protected $guarded = ['id'];
    protected $fillable = [
        'name',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
