<?php

namespace App\Http\Controllers\api;

use App\Models\Ledger;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
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
        $ledger = Ledger::where('name', 'LIKE', '%'. $namefilter . '%')
                        ->orderBy($orderby, $sortdesc)
                        ->paginate(request('perPage'),'*', 'page',request('page'));
        
        return $this->response->successResponse($ledger);
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
        $ledger = Ledger::latest()->get();

        return $this->response->successResponse($ledger);
    }

    public function fetchTickets(Request $request)
    {
        $ledger = Ledger::where('type','ticket')->latest()->get();

        return $this->response->successResponse($ledger);
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
            $ledger  = new Ledger;
            $ledger->fill($request->all());
            $ledger->save();
            DB::commit();
            return $this->response->successResponse($ledger);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
         
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ledger  $ledger
     * @return \Illuminate\Http\Response
     */
    public function show(Ledger $ledger)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ledger  $ledger
     * @return \Illuminate\Http\Response
     */
    public function edit(Ledger $ledger)
    {
        try {
            DB::beginTransaction();
            $ledger = Ledger::findOrFail($ledger);
            $ledger->fill($request->all());
            $ledger->save();

            DB::commit();

            return $this->response->successResponse($ledger);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ledger  $ledger
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ledger $ledger)
    {
        try {
            DB::beginTransaction();
            $ledger = Ledger::findOrFail($ledger);
            $ledger->fill($request->all());
            $ledger->save();

            DB::commit();

            return $this->response->successResponse($ledger);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ledger  $ledger
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ledger $ledger)
    {
        try {
            $ledger->delete();
            return $this->response->successResponse('OK');
        } catch (Exception $e) {
            return $this->response->failedResponseException($e);
        }
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->addHours(7)->format('d-M-Y H:i:s');;
    }

}
