<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use App\Models\Ledger;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Kas;
use App\Models\RawMaterial;
use App\Models\RawMaterialReceive;
use App\Models\RawMaterialSend;
use App\Models\BankName;
use App\Models\Loan;
use App\Models\Producing;
use App\Models\RawMaterialReceiving;
use App\Models\RawMaterialSending;
use App\Models\RawMaterialConsuming;
use App\Models\InventoryConsuming;

class InventoryAccountSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        $name = 'Biaya Perbaikan/Pemeliharaan Pabrik';
        $type = 'expense';
        $code = 'BY.PERBAIKAN.PABRIK.SJS';
        $pabrikledger = CompanyCoa::create(['name' => $name, 'type' => $type, 'code' => $cde]);
        $pabrikledger->initAccount('IDR',$name,$code);

        $name = 'Biaya Produksi';
        $type = 'hpp';
        $code = 'BY.PRODUKSI';
        $pabrikledger = CompanyCoa::create(['name' => $name, 'type' => $type, 'code' => $cde]);
        $pabrikledger->initAccount('IDR',$name,$code);

        $name = 'Biaya Perbaikan/Pemeliharaan Kendaraan';
        $type = 'expense';
        $code = 'BY.PERBAIKAN.KENDARAAN.SJS';
        $kendaraanledger = CompanyCoa::create(['name' => $name, 'type' => $type, 'code' => $code]);

        $name = 'Biaya Perbaikan/Pemeliharaan Kendaraan PSL';
        $type = 'expense';
        $code = 'PSL.BY.PERBAIKAN.KENDARAAN';
        $kendaraanledgerpsl = CompanyCoa::create(['name' => $name, 'type' => $type, 'code' => $code]);
       
    }

}
