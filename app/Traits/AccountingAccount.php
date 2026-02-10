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

trait AccountingAccount
{
    public function account(): MorphOne
    {
        return $this->MorphOne(Account::class, 'morphed');
    }

    /**
     * Initialize a account for a given model object
     *
     * @param null|string $currency_code
     * @param null|string $ledger_id
     * @return mixed
     * @throws AccountAlreadyExists
     */
    public function initAccount(?string $currency_code = 'IDR', ?string $name = null, ?string $code = null)
    {
        $account = Account::where('name', $name)->first();
        if($account) {
            throw new AccountAlreadyExists;
        }
        if (!$this->account) {
            $account = new Account();
            $account->name = $name;
            $account->code = $code;
            $account->currency = $currency_code;
            $account->balance = 0;
            return $this->account()->save($account);
        }
    }
}
