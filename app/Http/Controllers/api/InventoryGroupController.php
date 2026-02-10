<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class InventoryGroupController extends Controller
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
        $inventoryGroups = DB::table('inventory_groups');

        if (request('q')) {
            $inventoryGroups->where('name', 'LIKE', '%'. request('q') .'%');
        }

        $inventoryGroups = $inventoryGroups->latest()->paginate();

        return $this->response->successResponse($inventoryGroups);
    }

    public function all()
    {
        $inventoryGroups = InventoryGroup::latest()->get();

        return $this->response->successResponse($inventoryGroups);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $inventoryGroup = new InventoryGroup;
            $inventoryGroup->fill($request->all());
            $inventoryGroup->save();
            DB::commit();

//            create_activity(self::CREATE, $inventoryGroup);

            return $this->response->successResponse($inventoryGroup);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponse($e);
        }
    }

    public function show($inventoryGroup)
    {
        $inventoryGroup = InventoryGroup::findOrFail($inventoryGroup);

        return $this->response->successResponse($inventoryGroup);
    }

    public function update(Request $request, $inventoryGroup)
    {
        try {
            DB::beginTransaction();
            $inventoryGroup = InventoryGroup::findOrFail($inventoryGroup);
            $inventoryGroup->fill($request->all());
            $inventoryGroup->save();
            DB::commit();

//            create_activity(self::UPDATE, $inventoryGroup);

            return $this->response->successResponse($inventoryGroup);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponse($e);
        }
   }

    public function destroy($inventoryGroup)
    {
//        create_activity(self::DELETE, InventoryGroup::find($inventoryGroup));
        InventoryGroup::destroy($inventoryGroup);

        return $this->response->successResponse('OK');
    }
}
