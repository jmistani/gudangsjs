<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class InventoryUnitController extends Controller
{
    const CREATE = 'Tambah Grup Inventaris';
    const UPDATE = 'Update Grup Inventaris';
    const DELETE = 'Hapus Grup Inventaris';

    private $response;

    public function __construct(ResponseHelper $response)
    {
        $this->response = $response;
    }

    public function index()
    {
        $inventoryUnits = DB::table('inventory_units');

        if (request('q')) {
            $inventoryUnits->where('name', 'LIKE', '%'. request('q') .'%');
        }

        $inventoryUnits = $inventoryUnits->latest()->paginate();

        return $this->response->successResponse($inventoryUnits);
    }

    public function all()
    {
        $inventoryUnits = InventoryUnit::latest()->get();

        return $this->response->successResponse($inventoryUnits);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $inventoryUnit = new InventoryUnit;
            $inventoryUnit->fill($request->all());
            $inventoryUnit->save();
            DB::commit();

//            create_activity(self::CREATE, $inventoryUnit);

            return $this->response->successResponse($inventoryUnit);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponse($e);
        }
    }

    public function show($inventoryUnit)
    {
        $inventoryUnit = InventoryUnit::findOrFail($inventoryUnit);

        return $this->response->successResponse($inventoryUnit);
    }

    public function update(Request $request, $inventoryUnit)
    {
        try {
            DB::beginTransaction();
            $inventoryUnit = InventoryUnit::findOrFail($inventoryUnit);
            $inventoryUnit->fill($request->all());
            $inventoryUnit->save();
            DB::commit();

//            create_activity(self::UPDATE, $inventoryUnit);

            return $this->response->successResponse($inventoryUnit);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponse($e);
        }
   }

    public function destroy($inventoryUnit)
    {
//        create_activity(self::DELETE, InventoryUnit::find($inventoryUnit));
        InventoryUnit::destroy($inventoryUnit);

        return $this->response->successResponse('OK');
    }
}
