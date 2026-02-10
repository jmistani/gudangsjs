<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Inventory;
use App\Models\ConsumeJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Money\Money;
use Money\Currency;
use Carbon\Carbon;
use PDF;

class ConsumeJournalValidationController extends Controller
{
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
            $inventory = ConsumeJournal::where("code", $code)->get();
            if(count($inventory) > 0)
                return $this->response->successResponse(['valid' => false, 'message' => 'Kode sudah terpakai']);
            else
                return $this->response->successResponse(['valid' => true, 'message' => 'OK']);
        } catch (Exception $e) {
            return $this->response->failedResponseException($e);
        }
    }
}
