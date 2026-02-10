<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AccountingAccount;
use Carbon\Carbon;
use Money\Money;
use Money\Currency;

class Gudang extends Model
{
    use HasFactory, AccountingAccount;

    protected $fillable = [
        'code',
        'name',
        'currency',
        'location',
        'connection'
    ];

}
