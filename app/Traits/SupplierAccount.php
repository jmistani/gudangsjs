<?php

//declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Helpers\ReponseHelper;
use Money\Money;
use Money\Currency;
use Carbon\Carbon;
use App\Exceptions\AccountAlreadyExists;

trait SupplierAccount
{
    public function accounts()
    {
        return $this->MorphMany(Account::class, 'morphed');
    }
    
    /**
     * Initialize a account for a given model object
     *
     * @param null|string $currency_code
     * @param null|string $ledger_id
     * @return mixed
     * @throws AccountAlreadyExists
     */
    public function initAccounts(?string $currency_code = 'IDR')
    {
        $accounts = [];
        $details = [
            ['name' => 'Piutang']
        ];
        foreach($details as $detail) {
            $account = new Account();
            $account->ledger_id = $detail['ledger_id'];
            $account->name = $detail['name'];
            $account->code = $detail['code'];
            $account->currency = $currency_code;
            $account->balance = 0;
            $this->accounts()->save($account);
        }
    }

    public function getReceivableAttribute()
    {
        $account = $this->accounts()->filter(function ($item) {
            return str_contains(ucase($item->name),"PIUTANG");
        });
        
        return $account[0];
    }

    public function getPayableAttribute()
    {
        $account = $this->accounts()->filter(function ($item) {
            return str_contains(ucase($item->name),"HUTANG");
        });
        
        return $account[0];
    }
}
