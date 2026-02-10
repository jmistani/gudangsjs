<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Inventory;
use App\Models\InventoryGroup;
use App\Models\InventoryUnit;
use App\Models\ConsumeJournal;
use App\Models\ReceiveJournal;
use App\Models\Ticket;
use App\Models\Supplier;
use App\Models\User;
use App\Models\JournalHead;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Money\Money;
use Money\Currency;
use Carbon\Carbon;
use App\Exceptions\AccountAlreadyExists;
use App\Exceptions\TagAlreadyExists;
use App\Exceptions\TagNotFound;
use Illuminate\Support\Facades\Config;

class ValidationController extends Controller
{
   private $response;

   public function __construct(ResponseHelper $response)
   {
       $this->response = $response;
   }

   public function usernameValidation($username)
   {
       try {
           if(!$username)
              return $this->response->successResponse(['valid' => false, 'message' => 'Masukkan Username']);
           $user = User::where("username", $username)->get();
           if(count($user) > 0)
               return $this->response->successResponse(['valid' => false, 'message' => 'Username sudah terpakai']);
           else
               return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
       } catch (Exception $e) {
           return $this->response->failedResponseException($e);
       }
   }

   public function kodeAccount($code)
   {
       try {
           if(!$code)
              return $this->response->successResponse(['valid' => false, 'message' => 'Masukkan Kode']);
           $account = Account::where("code", $code)->get();
           if(count($account) > 0)
               return $this->response->successResponse(['valid' => false, 'message' => 'Kode sudah terpakai']);
           else
               return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
       } catch (Exception $e) {
           return $this->response->failedResponseException($e);
       }
   }

   public function kodeBarang($code)
   {
       try {
           if(!$code)
              return $this->response->successResponse(['valid' => false, 'message' => 'Masukkan Kode']);
           $inventory = Inventory::where("code", $code)->get();
           if(count($inventory) > 0)
               return $this->response->successResponse(['valid' => false, 'message' => 'Kode sudah terpakai']);
           else
               return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
       } catch (Exception $e) {
           return $this->response->failedResponseException($e);
       }
   }

   public function kodeInvConsume($code)
   {
       try {
           if(!$code)
              return $this->response->successResponse(['valid' => false, 'message' => 'Masukkan Kode']);
           $inventory = JournalHead::where("code", $code)->get();
           if(count($inventory) > 0)
               return $this->response->successResponse(['valid' => false, 'message' => 'Kode sudah terpakai']);
           else
               return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
       } catch (Exception $e) {
           return $this->response->failedResponseException($e);
       }
   }
   public function kodeInvReceive($code)
   {
       try {
           if(!$code)
              return $this->response->successResponse(['valid' => false, 'message' => 'Masukkan Kode']);
           $inventory = JournalHead::where("code", $code)->get();
           if(count($inventory) > 0)
               return $this->response->successResponse(['valid' => false, 'message' => 'Kode sudah terpakai']);
           else
               return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
       } catch (Exception $e) {
           return $this->response->failedResponseException($e);
       }
   }

   public function stokBarang($id,$qty)
   {
       try {
           if(!$id)
              return $this->response->successResponse(['valid' => false, 'message' => 'Masukkan id']);
           $inventory  = Inventory::where("id", $id)->first();
 
           if(!$inventory)
               return $this->response->successResponse(['valid' => false, 'message' => 'id tidak ditemukan']);
 
            if($inventory->in_stock < $qty)
               return $this->response->successResponse(['valid' => false, 'message' => 'Stok tidak cukup']);
           else
               return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
       } catch (Exception $e) {
           return $this->response->failedResponseException($e);
       }
   }

   public function uniqueTicketName($name)
   {
    try {
        if(!$name)
           return $this->response->successResponse(['valid' => false, 'message' => 'Masukkan Nama']);
        $ticket = Ticket::where("name", $name)->get();
        if(count($ticket) > 0)
            return $this->response->successResponse(['valid' => false, 'message' => 'Nama tiket sudah terpakai']);
        else
            return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
    } catch (Exception $e) {
        return $this->response->failedResponseException($e);
    }
   }

   public function uniqueSupplierName($name)
   {
    try {
        if(!$name)
           return $this->response->successResponse(['valid' => false, 'message' => 'Masukkan Nama']);
        $supplier = Supplier::where("name", $name)->get();
        if(count($supplier) > 0)
            return $this->response->successResponse(['valid' => false, 'message' => 'Nama supplier sudah terpakai']);
        else
            return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
    } catch (Exception $e) {
        return $this->response->failedResponseException($e);
    }
   }

   public function uniqueGroupBarang($name)
   {
    try {
        if(!$name)
           return $this->response->successResponse(['valid' => false, 'message' => 'Masukkan Grup Barang']);
        $invgroup = InventoryGroup::where("name", $name)->get();
        if(count($invgroup) > 0)
            return $this->response->successResponse(['valid' => false, 'message' => 'Nama Grup duplikat']);
        else
            return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
    } catch (Exception $e) {
        return $this->response->failedResponseException($e);
    }
   }

   public function uniqueUnitBarang($name)
   {
        try {
            if(!$name)
                return $this->response->successResponse(['valid' => false, 'message' => 'Masukkan Unit Barang']);
            $invunit = InventoryUnit::where("name", $name)->get();
            if(count($invunit) > 0)
                return $this->response->successResponse(['valid' => false, 'message' => 'Nama Unit duplikat']);
            else
                return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
            } catch (Exception $e) {
            return $this->response->failedResponseException($e);
        }
   }


}
