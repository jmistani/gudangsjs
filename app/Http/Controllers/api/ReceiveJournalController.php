<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Inventory;
use App\Models\ReceiveJournal;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Money\Money;
use Money\Currency;
use Carbon\Carbon;
use PDF;

class ReceiveJournalController extends Controller
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
        $receivejournal = ReceiveJournal::with('journalhead','stockMutations');
        $receivejournal = $receivejournal->orderBy($orderby, $sortdesc)
                        ->paginate(request('perPage'),'*', 'page',request('page'));
        
        return $this->response->successResponse($receivejournal);    
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
        $inventoryreceive = ReceiveJournal::with('stockMutations')->latest()->get();

        return $this->response->successResponse($inventoryreceive);
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
     * @param  \App\Models\ReceiveJournal  $invReceive
     * @return \Illuminate\Http\Response
     */
    public function show($receivejournal)
    {
        $rj = ReceiveJournal::with(['stockMutations', 'journalhead','nonstockInventories'])->findOrFail($receivejournal);

         $data['post_date'] = $rj->journalhead->post_date;
         $data['receive_code'] = $rj->journalhead->code;
         $data['vendor'] = Vendor::where('name',$rj->giver)->get()->first();
         $data['receiver'] = $rj->receiver;

         $data['items'] = [];
         $data['total'] = 0;
        foreach($rj->stockMutations as $key => $sm) {
            $subtotal = $sm->price->getAmount() * $sm->amount;
            $data['items'][] = array( 
                'stock' => "true",
                'inventory' => $sm->stockable,
                'qty' => $sm->amount,
                'unit' => $sm->stockable->unit,
                'price' => $sm->price->getAmount(),
                'subtotal' => $subtotal,
                'memo' => $sm->memo
            );
            $data['total'] = $data['total'] + $subtotal;
        }

        foreach($rj->nonstockInventories as $key => $ns) {
            $subtotal = $sm->price->getAmount() * $sm->amount;
            $data['items'][] = array(
                'stock' => "false", 
                'inventory' => $ns->name,
                'qty' => $ns->amount,
                'unit' => $ns->unit,
                'price' => $ns->price->getAmount(),
                'subtotal' => $subtotal,
                'memo' => $ns->memo
            );
            $data['total'] = $data['total'] + $subtotal;
        }

        // foreach($data->stockMutations as $key => $sm) {
        //     $sm->load('journalhead');
        //     if($sm['second_reference_type'] == 'App\Models\ConsumeJournal') {
        //         $inventory->stockMutations[$key]['journalhead'] = $sm->journalhead;
        //     }
        //     if($sm['second_reference_type'] == 'App\Models\ReceiveJournal') {
        //         $inventory->stockMutations[$key]['journalhead'] = $sm->journalhead;
        //     }
        // }
        
         return $this->response->successResponse($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReceiveJournal  $invReceive
     * @return \Illuminate\Http\Response
     */
    public function edit(ReceiveJournal $invReceive)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReceiveJournal  $invReceive
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReceiveJournal $invReceive)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReceiveJournal  $invReceive
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReceiveJournal $invReceive)
    {
        //
    }

    public function showPDF($id)
    {
        $data['receiveData'] = ReceiveJournal::with('stockMutations','nonstockInventories')->where('id', $id)->first();

        return view('pdfreport.inventory-receive',$data);
        $pdf = PDF::loadView('pdfreport.inventory-receive',$data);
        //return base64_encode($pdf->output());
//        return $pdf->download();
//         $filename = 'inventory-receive-'.$id.'.pdf';
//         return $this->response->pdfFileResponse(
//                  [
//                     'pdf' => base64_encode($pdf->output()),
//                     'filename' => $filename,
//                 ],
//                 200);
    }

    public function codeExists($code)
    {
        $invreceive = ReceiveJournal::where('code',$code)->first();

        if($invreceive != null)
            return true;
        return false;        
    }
}
