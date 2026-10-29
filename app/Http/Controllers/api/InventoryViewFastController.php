<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryViewFastController extends Controller
{
    public function show($id)
    {
        $inventory = Inventory::findOrFail($id);
        $type = $inventory->getMorphClass();
        // Active mutations INCLUDING archive summaries. Never sum archived originals again.
        $totals = DB::table('stock_mutations')
            ->where('stockable_type', $type)
            ->where('stockable_id', $inventory->id)
            ->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(amount),0) AS mutation_qty, COALESCE(SUM(total),0) AS mutation_value')
            ->first();

        return response()->json([
            'payload' => $inventory,
            'reconciliation' => $totals,
        ]);
    }

    public function mutations(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
        $type = $inventory->getMorphClass();
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
            ->where('sm.stockable_id', $inventory->id)
            ->where('sm.stockable_type', $type)
            ->whereNull('sm.deleted_at');

        if ($request->boolean('adjustment_only')) {
            $rows->where(function ($q) {
                $q->where('sm.description', 'like', 'Penyesuaian Stok%')
                  ->orWhere('jh.code', 'like', 'ADJ.STOCK.%');
            });
        }

        $result = $rows->select([
                'sm.id', 'sm.amount', 'sm.price', 'sm.total',
                'sm.description', 'sm.is_archive_summary', 'sm.journal_head_id',
                'sm.second_reference_type', 'sm.second_reference_id',
                'jh.code as journal_code', 'jh.post_date', 'sm.created_at',
                'cj.giver as consume_giver', 'cj.receiver as consume_receiver',
                'rj.giver as receive_giver', 'rj.receiver as receive_receiver',
            ])
            ->orderByDesc('sm.id')
            ->paginate($pageSize);

        $result->getCollection()->transform(function ($row) {
            $row->giver = $row->consume_giver ?: $row->receive_giver;
            $row->receiver = $row->consume_receiver ?: $row->receive_receiver;
            unset($row->consume_giver, $row->consume_receiver, $row->receive_giver, $row->receive_receiver);
            return $row;
        });

        return response()->json($result);
    }
}
