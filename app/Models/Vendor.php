<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\VendorPayableExists;
use App\Exceptions\VendorReceivableExists;

class Vendor extends Model
{
    use HasFactory;
    protected $fillable = [ 'name', 'address', 'contact'];
    protected $table = 'vendors';

    public function __construct()
    {
    }

    public function accounts()
    {
        return $this->MorphMany(Account::class, 'morphed');
    }

    public function initAccounts()
    {       
        $hutang = CompanyCoa::where('name', 'Hutang '. $this->name)->first();
        if($hutang) {
            throw new VendorPayableExists;
        }
        if (!$hutang) {
            $name = 'Hutang ' . $this->name;
            $code = 'HTG.' . strtoupper(preg_replace('/\s*/', '', $name));
            $hutang = CompanyCoa::create([
                'name' => $name, 
                'type' => 'payable', 
                'code' => $code
            ]);
            $account = $hutang->initAccount('IDR', $name, $code);
            $this->accounts()->save($account);
        }

        $piutang = CompanyCoa::where('name','Piutang '. $this->name)->first();
        if($piutang) {
            throw new VendorReceivableExists;
        }
        if (!$piutang) {
            $name = 'Piutang '. $this->name;
            $code = 'PTG.' . strtoupper(preg_replace('/\s*/', '', $name));
            $piutang = CompanyCoa::create([
                'name' => $name, 
                'type' => 'receivable', 
                'code' => $code
            ]);
            $account = $piutang->initAccount('IDR', $name, $code);
            $this->accounts()->save($account);
        }
    }

    public function payable()
    {
        return $this->accounts()->where('code', 'LIKE', '%'.'HTG'.'%')->get()->first();
    }

    public function receivable()
    {
        return $this->accounts()->where('code', 'LIKE', '%'.'PTG'.'%')->get()->first();
    }

    public function addCreditPurchase($date, $total, $memo = null, $type, $code=null)
    {
        return $this->payable()->addCredit($total, $memo, $date, null, null, $code);
    }

    public function addCashPurchase($date, $total, $memo = null, $type, $code=null, $cashaccount)
    {
        return $cashaccount()->addCredit($total, $memo, $date, null, null, $code);
    }

    public function addPayment($date, $quantity, $price, $total, $memo = null, $type, $code=null)
    {
        $this->payable()->addDebit($total, $memo, $date, (integer)$type, null, $code);
        return $this->decreaseStock($quantity,array("price" => $price, "total" => $total));
    }

    public function addDownPayment($date, $quantity, $price, $total, $memo = null, $type, $code=null)
    {
        $this->receivable->addDebit($total, $memo, $date, (integer)$type, null, $code);
        return $this->increaseStock($quantity,array("price" => $price, "total" => $total));
    }

}
