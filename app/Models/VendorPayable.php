<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AccountingAccount;
use App\Models\Vendor;

class VendorPayable extends Model
{
    use HasFactory, AccountingAccount;
    protected $fillable = ['name'];
    protected $table = 'vendor_payables';

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
