<?php

namespace App\Http\Controllers\api;

use App\Models\Account;
use App\Models\Ledger;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyCoa;

class AccountController extends Controller
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
        $account = Account::where('name', 'LIKE', '%'. $namefilter . '%')
                        ->orderBy($orderby, $sortdesc)
                        ->paginate(request('perPage'),'*', 'page',request('page'));
        
        return $this->response->successResponse($account);
    }

    public function listFormData()
    {
        $typeOptions = array('kas','hpp','equity','asset', 'payable', 'receivable', 'liability','expense','profitloss');
        $formOptions = [];
        $formOptions['type'] = $typeOptions;

        return $this->response->successResponse($formOptions);
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
        $account = Account::latest()->get();

        return $this->response->successResponse($account);
    }

    public function fetchTickets(Request $request)
    {
        $account = Account::where('name', 'like', 'Tiket*')->latest()->get();

        return $this->response->successResponse($account);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // try {
        //     DB::beginTransaction();
        //     $account  = new Account;
        //     $account->fill($request->all());
        //     $account->save();
        //     DB::commit();
        //     return $this->response->successResponse($account);
        // } catch (Exception $e) {
        //     DB::rollback();

        //     return $this->response->failedResponseException($e);
        // }
        try {
            DB::beginTransaction();
            $code = $request['code'];
            $name = $request['name'];
            $type = $request['type'];
            $companycoa = CompanyCoa::create(['code'=>$code,'name'=>$name, 'type'=>$type]);
            $companycoa->initAccount('IDR', $name, $code);
            DB::commit();
            return $this->response->successResponse($companycoa);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    public function expenses(Request $request)
    {
        $companycoas = CompanyCoa::Expenses()->get();

        return $this->response->successResponse($companycoas);
    }

    public function hpps(Request $request)
    {
        $companycoas = CompanyCoa::HPPs()->get();

        return $this->response->successResponse(array_map(function ($object) {
            return $this->account;
        },$companycoas));
    }

    public function tickets(Request $request)
    {
        $ledgers = Ledger::where('type', 'ticket')->get();
        $accounts = array();
        $i=0;
        foreach ($ledgers as $ledger) {
            if($ledger->accounts->count() > 0) {
                for($j=0; $j< $ledger->accounts->count(); $j++) {
                    $accounts[$i] = $ledger->accounts[$j];
                    $i++;
                }
            }
        }
        return $this->response->successResponse($accounts);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function edit(Account $account)
    {
        try {
            DB::beginTransaction();
            $account = Account::findOrFail($account);
            $account->fill($request->all());
            $account->save();

            DB::commit();

            return $this->response->successResponse($account);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Account $account)
    {
        try {
            DB::beginTransaction();
            $account = Account::findOrFail($account);
            $account->fill($request->all());
            $account->save();

            DB::commit();

            return $this->response->successResponse($account);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        try {
            $account->delete();
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
