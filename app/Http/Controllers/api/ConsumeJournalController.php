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

class ConsumeJournalController extends Controller
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
        $codeFilter = request('q') ? request('q') : "";
        $orderby = request('sortBy') ? request('sortBy') : 'id';
        $sortdesc = request('sortDesc') == 'true' ? 'desc' : 'asc';
        $consumejournal = ConsumeJournal::with('journalhead','stockMutations')
                        ->where('code', 'LIKE', '%'. $codeFilter . '%');

        $consumejournal = $consumejournal->orderBy($orderby, $sortdesc)
                        ->paginate(request('perPage'),'*', 'page',request('page'));
        
        return $this->response->successResponse($consumejournal);    
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
        $inventoryconsume = ConsumeJournal::with('stockMutations')->latest()->get();

        return $this->response->successResponse($inventoryconsume);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ConsumeJournal  $invConsume
     * @return \Illuminate\Http\Response
     */
    public function show(ConsumeJournal $invConsume)
    {
        $inventory = ConsumeJournal::with(['stockMutations', 'account'])->findOrFail($inventory);

        foreach($inventory->stockMutations as $key => $sm) {
            $sm->load('journalhead');
            if($sm['second_reference_type'] == 'App\Models\ConsumeJournal') {
                $inventory->stockMutations[$key]['journalhead'] = $sm->journalhead;
            }
            if($sm['second_reference_type'] == 'App\Models\ReceiveJournal') {
                $inventory->stockMutations[$key]['journalhead'] = $sm->journalhead;
            }
        }
        
         return $this->response->successResponse($inventory);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ConsumeJournal  $invConsume
     * @return \Illuminate\Http\Response
     */
    public function edit(ConsumeJournal $invConsume)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ConsumeJournal  $invConsume
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConsumeJournal $invConsume)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ConsumeJournal  $invConsume
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConsumeJournal $invConsume)
    {
        //
    }

    public function getRouteKeyName()
    {
        return 'code';
    }

    public function showPDF($id)
    {
        $data['consumeData'] = ConsumeJournal::with('stockMutations')->where('id', $id)->first();

        return view('pdfreport.inventory-consume',$data);
        $pdf = PDF::loadView('pdfreport.inventory-consume',$data);
        //return base64_encode($pdf->output());
//        return $pdf->download();
//         $filename = 'inventory-consume-'.$id.'.pdf';
//         return $this->response->pdfFileResponse(
//                  [
//                     'pdf' => base64_encode($pdf->output()),
//                     'filename' => $filename,
//                 ],
//                 200);
     }

    public function codeExists($code)
    {
        $invconsume = ConsumeJournal::where('code',$code)->first();

        if($invconsume != null)
            return true;
        return false;        
    }
}
