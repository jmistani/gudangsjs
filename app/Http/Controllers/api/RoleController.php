<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{

    private $response;

    public function __construct(ResponseHelper $response)
    {
        $this->response = $response;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
        $role = Role::latest();
        if ($request->q) {
            $role = $role->where('name', 'LIKE', '%'. $request->q .'%');
        }
        if ($request->sortBy) {
            $role = $role->orderBy($request->sortBy, $request->sortDesc ? 'DESC' : 'ASC');
        }
        $role = $role->paginate($request->perPage, '*', 'page', $request->page);

        return $this->response->successResponse($role);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function all()
    {
        $role = Role::latest()->get();

        return $this->response->successResponse($role);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $role  = new Role;
            $role->name = $request->name;
            $role->guard_name = $request->guard_name;
            $role->save();
            DB::commit();

            return $this->response->successResponse($role);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        try {
            DB::beginTransaction();
            $role = Role::findOrFail($role);
            if($request->name)
            $role->name = $request->name;
            if($request->guard_name)
            $role->guard_name = $request->guard_name;
            $role->save();

            DB::commit();

            return $this->response->successResponse($role);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        try {
            DB::beginTransaction();
            $role = Role::findOrFail($role);
            if($request->name)
            $role->name = $request->name;
            if($request->guard_name)
            $role->guard_name = $request->guard_name;
            $role->save();

            DB::commit();

            return $this->response->successResponse($role);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        try {
            $role->delete();
            return $this->response->successResponse('OK');
        } catch (Exception $e) {
            return $this->response->failedResponseException($e);
        }
    }

    public function exist($roleName)
    {
        try {
            $role = Role::where("name", $roleName)->get();
            if(count($role) > 0)
                return $this->response->successResponse('EXIST');
            else
                return $this->response->successResponse('NOT EXIST');
        } catch (Exception $e) {
            return $this->response->failedResponseException($e);
        }
    }
}
