<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryViewFastController extends Controller
{
    // Inventory header only: intentionally does not eager-load mutations.
    public function show($id)
    {
        return response()->json([
            'payload' => Inventory::findOrFail($id),
        ]);
    }

    // Only one page of active mutations; archives stay in the separate archive screen.
    public function mutations(Request $request, $id)
    {
        Inventory::whereKey($id)->firstOrFail(['id']);
        $pageSize = max(1, min(100, (int) $request->query('per_page', 25)));

        $rows = DB::table('stock_mutations as sm')
            ->leftJoin('journal_heads as jh', 'jh.id', '=', 'sm.journal_head_id')
            ->leftJoin('consume_journals as cj', function ($join) {
                $join->on('cj.id', '=', 'sm.second_reference_id')
                    ->where('sm.second_reference_type', '=', 'App\\Models\\ConsumeJournal');
            })
            ->leftJoin('receive_journals as rj', function ($join) {
                $join->on('rj.id', '=', 'sm.second_reference_id')
                    ->where('sm.second_reference_type', '=', 'App\\Models\\ReceiveJournal');
            })
            ->where('sm.stockable_id', $id)
            ->where('sm.stockable_type', 'App\\Models\\Inventory')
            ->whereNull('sm.deleted_at')
            ->select([
                'sm.id', 'sm.amount', 'sm.description', 'sm.second_reference_type',
                'sm.second_reference_id', 'jh.post_date', 'sm.created_at',
                'cj.giver as consume_giver', 'cj.receiver as consume_receiver',
                'rj.giver as receive_giver', 'rj.receiver as receive_receiver',
            ])
            ->orderByDesc('sm.id')
            ->paginate($pageSize);

        $rows->getCollection()->transform(function ($row) {
            $row->giver = $row->consume_giver ?: $row->receive_giver;
            $row->receiver = $row->consume_receiver ?: $row->receive_receiver;
            unset($row->consume_giver, $row->consume_receiver, $row->receive_giver, $row->receive_receiver);
            return $row;
        });

        return response()->json($rows);
    }
}
