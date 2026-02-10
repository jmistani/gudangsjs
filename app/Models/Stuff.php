<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stuff extends Model
{
    protected $guarded = ['id'];
    protected $fillable = [
        'name', 'stuff_type_id', 'origin', 'description', 'fee', 'coa_id'
    ];

    public function stuffType()
    {
        return $this->belongsTo(StuffType::class);
    }

    public function mutation()
    {
        return $this->hasMany(StuffMutation::class);
    }

    public function coa_master()
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }

    public function rawMaterialPrice()
    {
        return $this->hasMany(rawMaterialPrice::class, 'coa_id');
    }
}
