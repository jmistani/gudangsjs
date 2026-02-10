<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    const CREATE = 'Tambah Tiket';
    const UPDATE = 'Update Tiket';
    const DELETE = 'Hapus Tiket';

    private $response;

    public function __construct(ResponseHelper $response)
    {
        $this->response = $response;
    }

    public function index()
    {
        $typeFilter = request('type') ? request('type') : "";
        $namefilter = request('q') ? request('q') : "";
        $orderby = request('sortBy') ? request('sortBy') : 'id';
        $sortdesc = request('sortDesc') == 'true' ? 'desc' : 'asc';
        $ticket = Ticket::with('stockMutations')->where('name', 'LIKE', '%'. $namefilter . '%')
                    ->where('type', 'LIKE', '%'. strtolower($typeFilter) . '%');

        $ticket = $ticket->orderBy($orderby, $sortdesc)
                        ->paginate(request('perPage'),'*', 'page',request('page'));
        
        return $this->response->successResponse($ticket);
    }

    public function all()
    {
        $tickets = Ticket::latest()->get();

        return $this->response->successResponse($tickets);
    }

    public function allopen()
    {
        $tickets = Ticket::where('status','open')->latest()->get();

        return $this->response->successResponse($tickets);
    }

    public function allclosed()
    {
        $tickets = Ticket::where('status','closed')->latest()->get();

        return $this->response->successResponse($tickets);
    }

    public function close($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->close();
        $ticket->save();

        return $this->response->successResponse("OK");
    }
    // public function allTicketName()
    // {
    //     $tickets = TicketName::all();

    //     return $this->response->successResponse($tickets);
    // }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = new Ticket;
            $ticket->fill($request->all());
//            $ticket->status = "open";
            $ticket->save();
            DB::commit();

            // create_activity(self::CREATE, $ticket);

            return $this->response->successResponse($ticket);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponse($e);
        }
    }

    public function show($ticket)
    {
        $ticket = Ticket::with('stockMutations.stockable')->findOrFail($ticket);

        return $this->response->successResponse($ticket);
    }

    public function update(Request $request, $ticket)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($ticket);
            $ticket->fill($request->all());
            $ticket->save();
            DB::commit();

            // create_activity(self::UPDATE, $ticket);

            return $this->response->successResponse($ticket);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponse($e);
        }
   }
   

    public function destroy($ticket)
    {
        // create_activity(self::DELETE, Ticket::find($ticket));
        Ticket::destroy($ticket);

        return $this->response->successResponse('OK');
    }



    public function showPDF($id)
    {
        $data['ticketData'] = Ticket::with('stockMutations.reference')->where('id', $id)->first();

        return view('pdfreport.ticket',$data);
        $pdf = PDF::loadView('pdfreport.ticket',$data);
        //return base64_encode($pdf->output());
//        return $pdf->download();
//         $filename = 'inventory-consume-'.$id.'.pdf';
//         return $this->response->pdfFileResponse(
//                  [
//                     'pdf' => base64_encode($pdf->output()),
//                     'filename' => $filename,
//                 ],
//                 200);
     }}
