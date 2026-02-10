<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Inventory;
use App\Models\Ledger;
use App\Models\Ticket;
use App\Models\Account;
use App\Models\StockMutation;
use App\Models\JournalHead;
use App\Models\ConsumeJournal;
use App\Models\ReceiveJournal;
use App\Models\InventoryUnit;
use App\Models\InventoryGroup;
use App\Models\Vendor;
use App\Models\CompanyCoa;
use App\Models\NonStockInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Money\Money;
use Money\Currency;
use Carbon\Carbon;
use App\Exceptions\AccountAlreadyExists;
use App\Exceptions\TagAlreadyExists;
use App\Exceptions\TagNotFound;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class InventoryController extends Controller
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
        $tagFilter = request('tag') ? request('tag') : "";

        $bagian = request('bagian') ? request('bagian') : "semua";
        if($bagian == 'semua')
            $bagianFilter = "";
        else if($bagian == 'pabrik')
            $bagianFilter = "P.";
        else if($bagian == 'pengangkutan')
            $bagianFilter = "M.";
    
        $namefilter = request('q') ? request('q') : "";
        $orderby = request('sortBy') ? request('sortBy') : 'id';
        $sortdesc = request('sortDesc') == 'true' ? 'desc' : 'asc';
        $inventory = Inventory::where('name', 'LIKE', '%'. $namefilter . '%')
                    ->where('code', 'LIKE', $bagianFilter . '%');

        if($tagFilter != "")
                $inventory = $inventory->where('tags','LIKE','%'.$tagFilter.'%');

        $inventory = $inventory->orderBy($orderby, $sortdesc)
                        ->paginate(request('perPage'),'*', 'page',request('page'));
        
        return $this->response->successResponse($inventory);
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

    public function getStockDetails($inventory) {

    }

    public function all(Request $request)
    {
        $inventoryAll = Inventory::latest()->get();
        $inventoryAll = Inventory::with(['stockMutations', 'account'])->latest()->get();
        
        return $this->response->successResponse($inventoryAll);
    }

    public function inventoriesEdit(Request $request)
    {
        $inventoryAll = Inventory::select('code','name','unit','category')->where('id',$request['id'])->first();
        
        return $this->response->successResponse($inventoryAll);
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
            $inventory  = new Inventory;
            $inventory->fill($request->all());
            $inventory->save();
            $group = InventoryGroup::where('name',$request->category)->get()->first();
            $inventory->group()->associate($group);
            $inventory->save();
            $id = $inventory->id;
            $inventory->initAccount('IDR', "Stock ".$inventory->id, 'STOK.'.$inventory->id);
            DB::commit();
            return $this->response->successResponse($inventory);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
         
    }

    public function showa($inventory)
    {
        $inventory = Inventory::with(['stockMutations', 'account'])->get();
	foreach($inventory as $inv)
	{
	    foreach($inv->stockMutations as $key => $sm)
            {
		$sm->assignReference(JournalHead::find($sm->journal_head_id));
            }
	}
	
	return 'OK';
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function show($inventory)
    {
        $inventory = Inventory::with(['stockMutations', 'account'])->findOrFail($inventory);

        foreach($inventory->stockMutations as $key => $sm) {
            $sm->load('journalhead');
	    $sm->assignReference($sm->journalhead);
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
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function edit(Inventory $inventory)
    {
        try {
            DB::beginTransaction();
            $inventory = Inventory::findOrFail($inventory);
            $inventory->fill($request->all());
            $inventory->save();

            DB::commit();

            return $this->response->successResponse($inventory);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $inventory)
    {
        try {
            DB::beginTransaction();
            $inventory = Inventory::findOrFail($inventory);
            if($request['qty']) {
                array_pop('qty',$request);
            }
            if($request['price']) {
                array_pop('price',$request);
            }
            $inventory->fill($request->all());
            $inventory->save();

            DB::commit();

            return $this->response->successResponse($inventory);
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inventory $inventory)
    {
        try {
            $inventory->delete();
            return $this->response->successResponse('OK');
        } catch (Exception $e) {
            return $this->response->failedResponseException($e);
        }
    }

    public function addTag(Request $request)
    {
        try {
            DB::beginTransaction();
            $inventory = Inventory::findOrFail($request['id']);
            $inputTag = $request['tag'];
            $tags = $inventory->tags == null ? array() : json_decode($inventory->tags);
            if(in_array($inputTag, $tags))
                return $this->response->failedResponseException(new TagAlreadyExists());
            else {
                array_push($tags,$request['tag']);
                $inventory->tags = json_encode($tags);
                $inventory->save();
            }
            DB::commit();
            return $this->response->successResponse('OK');
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    public function removeTag(Request $request)
    {
        try {
            DB::beginTransaction();
            $inventory = Inventory::findOrFail(request('id'));
            $tags = json_decode($inventory->tags);
            $poskey = array_search(request('tag'), $tags);
            if( $poskey !== false) {
                array_splice($tags,$poskey,1);
                $inventory->tags = json_encode($tags);
                $inventory->save();
            }
            else {
                return $this->response->failedResponseException(new TagNotFound());
            }
            DB::commit();
            return $this->response->successResponse('OK');
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    public function clearTag(Request $request)
    {
        try {
            DB::beginTransaction();
            $inventory = Inventory::findOrFail($inventory);
            $inventory->tags = json_encode(array());
            $inventory->save();
            DB::commit();
            return $this->response->successResponse('OK');
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }
    
    public function editReceive(Request $request)
    {
        try {
            DB::beginTansaction();
            $receiveJournal = ReceiveJournal::with('stockMutations','nonStockInventories','journalhead')
                                        ->find($request['receiveJournalID']);
            $journalhead = $receiveJournal->journalhead;
            $oriStockMutations = $receiveJournal->stockMutations;
            $oriNonStockInventories = $receiveJournal->nonStockInventories;
            $oriAccountTransactions = AccountTransaction::where('transaction_code',$journalhead->code)->get();

            $receiveJournal->giver = $request['vendor']['name'];
            $receiveJournal->receiver = $request['receiver'];
            $receiveJournal->save();

            $journalhead->post_date = $request['post_date'];
            $receiveJournal->journalhead()->save($journalhead);

            $newstockmutations = [];
            $newnonstockmutations = [];
            $adjustInventory = [];
            $accountAffected = [];
            foreach($request['items'] as $item) {
                if($item['stock'] == "true") {
                    $newstockmutations[] = $item;
                }
                else {
                    $newnonstockmutations[] = $item;
                }
            }

            StockMutation::upsert();
            
            DB::commit();
            return $this->response->successResponse('OK');
        
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    public function editConsume(Request $request)
    {
        try {
            DB::beginTansaction();

            
            
            DB::commit();
            return $this->response->successResponse('OK');
        
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    public function receive(Request $request)
    {
        try{
            if(count(JournalHead::where('code',$request['receive_code'])->get()) > 0)
                throw new Exception("kode sudah terpakai");

            DB::beginTransaction();
            $journalhead = new JournalHead;
            $journalhead->post_date = $request['post_date'];
            $journalhead->code = $request['receive_code'];
            $journalhead->save();
            $receiveJournal = new ReceiveJournal;
            $receiveJournal->giver = $request['vendor']['name'];
            $receiveJournal->receiver = $request['receiver'];
            $receiveJournal->save();
            $receiveJournal->journalhead()->save($journalhead);
            foreach($request['items'] as $item) {
                $id = $receiveJournal->id;
                if($item['stock'] == "true") {
                    $inventory = Inventory::where('name',$item['inventory']['name'])->first();
                    $stockMutation = $inventory->addStock(
                        Carbon::createFromFormat('d.m.Y',$request['post_date']),
                        $item['qty'],
                        $item['price'],
                        $item['subtotal'],
                        $item['memo'],
                        Config::get('gudang.inv_purchase_receive'),
                        $request['receive_code'],
                    );
                    $stockMutation->assignToReceiveJournal($receiveJournal);
                    $journalhead->stockMutations()->save($stockMutation);
                    $inventory->in_stock += $item['qty'];
    
                    $stockValue = $inventory->stock_value->add(new Money($item['qty'] * $item['price'], new Currency(Config::get('gudang.currency'))));
                    if($inventory->in_stock != 0)
                        $avgValue = $stockValue->divide($inventory->in_stock);
                    else
                        $avgValue = new Money(0,Config::get('gudang.currency'));

                    $inventory->stock_value = $stockValue->getAmount();
                    $inventory->avg_value = $avgValue->getAmount();
                    
                    $inventory->save();
                }
                else {
                    $nsInventory = NonStockInventory::create([
                        'name'=> $item['inventory'],
                        'amount'=> $item['qty'],
                        'price'=> $item['price'],
                        'unit'=> $item['unit'],
                        'subtotal'=> $item['subtotal'],
                        'memo'=> $item['memo'],
                    ]);
                    $account = Account::where('name',$item['account'])->first();
                    $transaction = $account->addDebit(
                        $item['subtotal'],
                        $item['inventory'],
                        Carbon::createFromFormat('d.m.Y',$request['post_date']),
                        Config::get('gudang.inv_consumption'),
                        null,
                        $request['receive_code']
                    );
                    $transaction->referencesObject($journalhead);
                    $nsInventory->assignToReceiveJournal($receiveJournal);
                    $nsInventory->assignToJournalHead($journalhead);
                }

            }
            $vendor = Vendor::where('id',$request['vendor']['id'])->get()->first();
            $transaction = $vendor->addCreditPurchase(
                Carbon::createFromFormat('d.m.Y',$request['post_date']),
                $request['total'],
                'Penerimaan/Pembelian Barang',
                Config::get('inv_purchase_receive'),
                $request['receive_code']
                );
            $transaction->referencesObject($receiveJournal);
            $transaction->assignToJournalHead($journalhead);
            DB::commit();
            return $this->response->successResponse('OK');
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    public function consume(Request $request)
    {
        try{
            DB::beginTransaction();
            foreach($request['items'] as $item) {
                $journalhead = new JournalHead;
                $journalhead->post_date = $request['post_date'];
                $journalhead->code = $request['consume_code'];
                $journalhead->save();
                $consumeJournal = new ConsumeJournal;
                $consumeJournal->giver = $request['giver'];
                $consumeJournal->receiver = $request['receiver'];
                $consumeJournal->save();
                $consumeJournal->journalhead()->save($journalhead);
                $id = $consumeJournal->id;
                $inventory = Inventory::where('code',$item['inventory']['code'])->first();
                $stockMutation = $inventory->subtractStock(
                    Carbon::createFromFormat('d.m.Y',$request['post_date']),
                    $item['qty'],
                    $item['avg_value']['amount'],
                    $item['qty']*$item['avg_value']['amount'],
                    $item['memo'],
                    Config::get('gudang.inv_consumption'),
                    $request['consume_code']
                );
                if(array_key_exists('tiket',$item)) {
                    $stockMutation->assignToTicket(Ticket::where('id',$item['tiket']['id'])->get()->first());
                }
                $stockMutation->assignToConsumeJournal($consumeJournal);
                $journalhead->stockMutations()->save($stockMutation);
                $inventory->in_stock -= $item['qty'];
                $stockvalue = $inventory->stock_value->subtract(Money::IDR($item['avg_value']['amount'])->multiply($item['qty']));
                $inventory->stock_value = $stockvalue->getAmount();
                if($inventory->in_stock == 0)
                    $inventory->avg_value = 0;
                // $avgValue = $inventory->stock_value->divide($inventory->in_stock);
                // $inventory->avg_value = $avgValue->getAmount();
                $inventory->save();

                $companycoa= CompanyCoa::where('code',$request['account']['code'])->get()->first();
                $transaction = $companycoa->account->addDebit(
                    $item['qty']*$item['avg_value']['amount'],
                    $inventory->name,
                    Carbon::createFromFormat('d.m.Y',$request['post_date']),
                    Config::get('gudang.inv_consumption'),
                    null,
                    $request['consume_code']
                );
                $transaction->referencesObject($consumeJournal);
                $transaction->assignToJournalHead($journalhead);
            }

            DB::commit();
            return $this->response->successResponse('OK');
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    public function receives(Request $request)
    {

        $q = StockMutation::with('reference','second_reference','stockable')->receives()->latest()->get();

        return $this->response->successResponse($q);

    }

    public function receivesDB(Request $request)
    {

        $q = StockMutation::with('reference','second_reference','stockable')->receives()
		->join('journal_heads','journal_heads.id','stock_mutations.journal_head_id')->orderBy('journal_heads.post_date','desc')->limit(20)->get();

        return $this->response->successResponse($q);

    }

    public function consumes(Request $request)
    {
        $q = StockMutation::with('reference','second_reference','stockable')->consumes()->latest()->get();

        return $this->response->successResponse($q);
    }

    public function consumesDB(Request $request)
    {
        $q = StockMutation::with('reference','second_reference','stockable')->consumes()
		->join('journal_heads','journal_heads.id','stock_mutations.journal_head_id')->orderBy('journal_heads.post_date','desc')->limit(20)->get();

        return $this->response->successResponse($q);
    }

    public function lastTransaction(Request $request)
    {
        $limit = $request['limit'];
        $inventory = Inventory::with('stockMutations')
            ->where('id',$request['id'])->get()->map(function($inventory) use ($limit) {
                $inventory->setRelation('stockMutations', $inventory->stockMutations->take($limit));
                return $inventory;
            });
        return $this->response->successResponse($inventory);
    }


    public function transactionIndex()
    {
        $stockMutations = Inventory::where('id',request('id'))->first()->stockMutations();
        $orderby = request('sortBy') ? request('sortBy') : 'id';
        $sortdesc = request('sortDesc') == 'true' ? 'desc' : 'asc';
        $inventory = $stockMutations
                        ->orderBy($orderby, $sortdesc)
                        ->paginate(request('perPage'),'*', 'page',request('page'));
        
        return $this->response->successResponse($inventory);
    }

    public function formReceiveData()
    {
        $formOptions = [];
        $formOptions['helptextreceive'] = Config::get('helptext.terimabarang');
        $formOptions['account'] = Account::whereIn('code', function($query) {
		$query->selectRaw('code')->from('company_coas')->where('type','expense');
	})->get();
        $formOptions['satuan'] = InventoryUnit::orderBy('name','asc')->pluck('name');
        $formOptions['grups'] = InventoryGroup::orderBy('name','asc')->pluck('name');
        $formOptions['tags'] = Config::get('gudang.inv_units');

        return $this->response->successResponse($formOptions);
    }

    public function listFormData()
    {
        $formOptions = [];
        $formOptions['helptextreceive'] = Config::get('helptext.terimabarang');
        $formOptions['helptextconsume'] = Config::get('helptext.keluarbarang');
        $formOptions['account'] = Account::VendorPayable()->orderBy('name','asc')->get();
        $formOptions['satuan'] = InventoryUnit::orderBy('name','asc')->pluck('name');
        $formOptions['grups'] = InventoryGroup::orderBy('name','asc')->pluck('name');
        $formOptions['tags'] = Config::get('gudang.inv_units');

        return $this->response->successResponse($formOptions);
    }

    public function consumeFormData()
    {
        $formOptions = [];
        $formOptions['helptextconsume'] = Config::get('helptext.keluarbarang');
        $formOptions['satuan'] = InventoryUnit::orderBy('name','asc')->pluck('name');

        return $this->response->successResponse($formOptions);
    }


    public function addImage(Inventory $inventory, Request $request)
    {
        try {
            DB::beginTransaction();
                $inventory = Inventory::where('code', $inventory['code'])->first();
                $inventory->addMedia($request->file('image'))->toMediaCollection('inventory-images');
                $inventory->save();
            DB::commit();
            return $this->response->successResponse('OK');
        } catch (Exception $e) {
            DB::rollback();

            return $this->response->failedResponseException($e);
        }
    }

    public function recalculateStock($id=null)
    {
        if($id) {
            $inventory = Inventory::with('stockMutations')->where('id',$id)->get();
        }
        else {
            $inventory = Inventory::with('stockMutations')->latest()->get();
        }
        foreach($inventory as $i) {
            $i->recalculateStock();
        }

        if($id) {
            $inventory = Inventory::with(['stockMutations', 'account'])->where('id',$id)->get()->first();

            foreach($inventory->stockMutations as $key => $sm) {
                if($sm['reference_type'] == 'App\Models\ConsumeJournal') {
                    $inventory->stockMutations[$key]['ConsumeJournal'] = $sm->reference;
                }
                if($sm['reference_type'] == 'App\Models\ReceiveJournal') {
                    $inventory->stockMutations[$key]['ReceiveJournal'] = $sm->reference;
                }
            }
            
             return $this->response->successResponse($inventory);
        }
        return $this->response->successResponse(Inventory::with(['stockMutations','account'])->latest()->get());
    }
    
    public function assignTicket($stockMutationID, $ticketID) {
        try {
            $stockMutation = StockMutation::findOrFail($stockMutationID);

            $ticket=null;
            if($ticketID != null) {
                $ticket = Ticket::findOrFail($ticketID);
            }
            $stockMutation->assignToTicket($ticket);

            return $this->response->successResponse("OK");
        }
        catch (Exception $e)
        {
            return $this>failedResponse("failed");
        }
    }

    public function removeTicket($stockMutationID) {
        try {
            $stockMutation = StockMutation::findOrFail($stockMutationID);

            $stockMutation->second_reference()->dissociate();
            $stockMutation->save();

            return $this->response->successResponse("OK");
        }
        catch (Exception $e)
        {
            return $this>failedResponse($e);
        }
    }

    public function accountMutationReport($account,$date_start,$date_end)
    {
        try {
            // $data['per_date'] = $request('per_date');
            $reportdata['tanggal_mulai'] = $date_start;
            $reportdata['tanggal_akhir'] = $date_end;

            if($reportdata['tanggal_mulai'] && $reportdata['tanggal_akhir'])
            {
                
                $account = Account::with('transactions')
                                ->where('code',$account)->get()->first();
                $reportdata['account'] = $account->name;
                $reportdata['transactions'] = $account->transactions()
                                        ->where('post_date', '>=', $date_start)
                                        ->orderBy('post_date','desc')
                                        ->get();
                return view('pdfreport.account-mutation-perdate', $reportdata);
            }
        }
        catch (Exception $e)
        {
            return $this>failedResponse($e);
        }   
    }

    public function inventoryBalanceReport($date,$bygroup)
    {
        try {
            $data['bygroup'] = $bygroup;
            $data['tanggal'] = $date;

            if($data['tanggal'])
            {


                $stockmutations = StockMutation::selectRAW('inventories.category as groupname, 
                sum(stock_mutations.amount) as amount,
                sum(stock_mutations.total) as total
                ')->join('inventories', 'stock_mutations.stockable_id','inventories.id')
                ->whereHasMorph(
                    'reference',
                    '*',
                    function (Builder $query) use ($data) {
                        $query->where('post_date', '>', $data['tanggal'])->groupBy();
                    }
                )
                ->groupBy('category')
                ->get();

                if($data['bygroup'] == true)
                {
                    $data['reportData'] = Inventory::selectRaw('inventory_groups.name as groupname,
                    SUM(inventories.stock_value) as stock_value')
                    ->join('inventory_groups', 'inventories.inventory_group_id', '=', 'inventory_groups.id')
                    ->groupBy('groupname')
                    ->orderBy('groupname')
                    ->get();
                
                    $i=0;
                    foreach($data['reportData'] as $key =>$rd) {
                        $index = array_search($rd->groupname,Arr::pluck($stockmutations,'groupname'));
                        if($index > -1)
                        {
                            $data['reportData'][$i]->stock_value = ($rd->stock_value->subtract($stockmutations[$index]->total))->getAmount();
                        }
                        $i++;
                    }

                    return view('pdfreport.inventory-balance-perdate', $data);
                }
                else
                {
                    $data['reportData'] = Inventory::selectRaw('inventories.category as groupname, 
                    inventories.name as inventoryname,
                    inventories.in_stocK as in_stock, 
                    inventories.unit as unit, 
                    inventories.avg_value as avg_value, 
                    inventories.stock_value as stock_value
                    ')
                    ->orderBy('groupname')
                    ->get();
                
                    return view('pdfreport.inventory-balance-perdate',$data);
                }
            }
            else
            {
                return $this>failedResponse($e);
            }
        }
        catch (Exception $e)
        {
            return $this>failedResponse($e);
        }
    }

    public function inventoryMutationReport($date_start,$date_end)
    {
        try {
            // $data['per_date'] = $request('per_date');
            $data['tanggal_mulai'] = $date_start;
            $data['tanggal_akhir'] = $date_end;

            if($data['tanggal_mulai'] && $data['tanggal_akhir'])
            {
                $stockMutationsReceive = StockMutation::selectRAW('
                inventories.name as name, 
                inventories.category as groupname, 
                inventories.unit as unit,
                stock_mutations.amount as amount,
                stock_mutations.price as price,
                stock_mutations.total as total,
                journal_heads.post_date as post_date
                ')->join('inventories', 'stock_mutations.stockable_id','inventories.id')
                ->join('journal_heads','journal_heads.id','stock_mutations.journal_head_id')
                ->where('post_date', '>=', $data['tanggal_mulai'])
                ->where('post_date', '<=', $data['tanggal_akhir'])
                ->where('stock_mutations.second_reference_type', '=', 'App\Models\ReceiveJournal')
                ->orderBy('post_date','asc')
                ->get();
    
                $stockMutationsReceiveSum = StockMutation::selectRAW('
                inventories.name as name, 
                inventories.category as groupname, 
                inventories.unit as unit,
                SUM(stock_mutations.amount) as amount,
                ROUND(AVG(stock_mutations.price),0) as price,
                SUM(stock_mutations.total) as total
                ')->join('inventories', 'stock_mutations.stockable_id','inventories.id')
                ->join('journal_heads','journal_heads.id','stock_mutations.journal_head_id')
                ->where('post_date', '>=', $data['tanggal_mulai'])
                ->where('post_date', '<=', $data['tanggal_akhir'])
                ->where('stock_mutations.second_reference_type', '=', 'App\Models\ReceiveJournal')
                ->orderBy('groupname','asc')
                ->groupBy('name')
                ->get();

                $nonStockReceive = NonStockInventory::selectRAW('
                nonstock_inventories.name as name, 
                nonstock_inventories.amount as amount,
                nonstock_inventories.unit as unit,
                nonstock_inventories.price as price,
                nonstock_inventories.subtotal as subtotal,
                journal_heads.post_date as post_date
                ')
                ->join('journal_heads','journal_heads.id','nonstock_inventories.journal_head_id')
                ->where('post_date', '>=', $data['tanggal_mulai'])
                ->where('post_date', '<=', $data['tanggal_akhir'])
                ->orderBy('post_date','asc')
                ->get();

                $nonStockReceiveSum = NonStockInventory::selectRAW('
                nonstock_inventories.name as name, 
                SUM(nonstock_inventories.amount) as amount,
                nonstock_inventories.unit as unit,
                ROUND(AVG(nonstock_inventories.price),0) as price,
                SUM(nonstock_inventories.subtotal) as subtotal
                ')
                ->join('journal_heads','journal_heads.id','nonstock_inventories.journal_head_id')
                ->where('post_date', '>=', $data['tanggal_mulai'])
                ->where('post_date', '<=', $data['tanggal_akhir'])
                ->orderBy('post_date','asc')
                ->groupBy('name')
                ->get();

                $stockMutationsConsume = StockMutation::selectRAW('
                inventories.name as name, 
                inventories.category as groupname, 
                inventories.unit as unit,
                stock_mutations.amount as amount,
                stock_mutations.price as price,
                stock_mutations.total as total,
                journal_heads.post_date as post_date
                ')->join('inventories', 'stock_mutations.stockable_id','inventories.id')
                ->join('journal_heads','journal_heads.id','stock_mutations.journal_head_id')
                ->where('post_date', '>=', $data['tanggal_mulai'])
                ->where('post_date', '<=', $data['tanggal_akhir'])
                ->where('stock_mutations.second_reference_type', '=', 'App\Models\ConsumeJournal')
                ->orderBy('post_date','asc')
                ->get();
    
                $stockMutationsConsumeSum = StockMutation::selectRAW('
                inventories.name as name, 
                inventories.category as groupname, 
                inventories.unit as unit,
                SUM(stock_mutations.amount) as amount,
                ROUND(AVG(stock_mutations.price),0) as price,
                SUM(stock_mutations.total) as total
                ')->join('inventories', 'stock_mutations.stockable_id','inventories.id')
                ->join('journal_heads','journal_heads.id','stock_mutations.journal_head_id')
                ->where('post_date', '>=', $data['tanggal_mulai'])
                ->where('post_date', '<=', $data['tanggal_akhir'])
                ->where('stock_mutations.second_reference_type', '=', 'App\Models\ConsumeJournal')
                ->orderBy('groupname','asc')
                ->groupBy('name')
                ->get();
    
                $data['reportData']['receive'] = $stockMutationsReceive;
                $data['reportData']['receiveSum'] = $stockMutationsReceiveSum;
                $data['reportData']['consume'] = $stockMutationsConsume;
                $data['reportData']['consumeSum'] = $stockMutationsConsumeSum;
                $data['reportData']['nsreceive'] = $nonStockReceive;
                $data['reportData']['nsreceiveSum'] = $nonStockReceiveSum;
                
                // $data['reportData'] = Inventory::selectRaw('inventories.category as groupname, 
                // ANY_VALUE(inventories.name) as inventoryname,
                // inventories.in_stocK as in_stock, inventories.unit as unit, inventories.avg_value as avg_value, inventories.stock_value as stock_value')
                // ->orderBy('groupname')
                // ->get();
            
                return view('pdfreport.inventory-mutation-perdate',$data);
            
            }
            else
            {
                return $this>failedResponse($e);
            }
        }
        catch (Exception $e)
        {
            return $this>failedResponse($e);
        }
    }
}
