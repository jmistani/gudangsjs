<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vendor;
use App\Traits\AccountingAccount;

class VendorReceivable extends Model
{
    use HasFactory, AccountingAccount;
    protected $fillable = ['name'];
    protected $table = 'vendor_receivables';

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
