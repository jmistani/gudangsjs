<?php

namespace App\Http\Controllers\api;

use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;

class StockMutationController extends Controller
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
    public function index()
    {
        $namefilter = request('q') ? request('q') : "";
        $orderby = request('sortBy') ? request('sortBy') : 'id';
        $sortdesc = request('sortDesc') == 'true' ? 'desc' : 'asc';
        $vendor = Vendor::where('name', 'LIKE', '%'. $namefilter . '%')
                        ->orderBy($orderby, $sortdesc)
                        ->paginate(request('perPage'),'*', 'page',request('page'));
        
        return $this->response->successResponse($vendor);
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

    public function all(Request $request)
    {
        $vendor = Vendor::latest()->get();

        return $this->response->successResponse($vendor);
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
            $vendor  = new Vendor;
            $vendor->fill($request->all());
            $vendor->save();
            DB::commit();
            $vendor->initAccounts();
            return $this->response->successResponse($vendor);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
         
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function show(Vendor $vendor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function edit(Vendor $vendor)
    {
        try {
            DB::beginTransaction();
            $vendor = Vendor::findOrFail($vendor);
            $vendor->fill($request->all());
            $vendor->save();

            DB::commit();

            return $this->response->successResponse($vendor);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vendor $vendor)
    {
        try {
            DB::beginTransaction();
            $vendor = Vendor::findOrFail($vendor);
            $vendor->fill($request->all());
            $vendor->save();

            DB::commit();

            return $this->response->successResponse($vendor);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vendor $vendor)
    {
        try {
            $vendor->delete();
            return $this->response->successResponse('OK');
        } catch (Exception $e) {
            return $this->response->failedResponseException($e);
        }
    }
}
