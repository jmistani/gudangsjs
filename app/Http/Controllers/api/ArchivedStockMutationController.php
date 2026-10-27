<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ArchivedStockMutationController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inventory_id' => 'nullable|integer|min:1',
            'archive_batch_id' => 'nullable|integer|min:1',
            'date_start' => 'nullable|date_format:Y-m-d',
            'date_end' => 'nullable|date_format:Y-m-d|after_or_equal:date_start',
            'search' => 'nullable|string|max:191',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Parameter filter tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $query = DB::table('stock_mutations_archive as sma')
            ->leftJoin('inventories as i', 'i.id', '=', 'sma.stockable_id')
            ->leftJoin('archive_journals as aj', 'aj.id', '=', 'sma.archive_batch_id')
            ->leftJoin('journal_heads as jh', 'jh.id', '=', 'sma.journal_head_id')
            ->where(function ($q) {
                $q->where('sma.is_archive_summary', 0)
                  ->orWhereNull('sma.is_archive_summary');
            });

        if ($request->filled('inventory_id')) {
            $query->where('sma.stockable_id', (int) $request->input('inventory_id'));
        }
        if ($request->filled('archive_batch_id')) {
            $query->where('sma.archive_batch_id', (int) $request->input('archive_batch_id'));
        }
        if ($request->filled('date_start')) {
            $query->whereDate('aj.created_at', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->whereDate('aj.created_at', '<=', $request->input('date_end'));
        }
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $search = '%' . addcslashes($term, '%_\\') . '%';
            $query->where(function ($q) use ($search, $term) {
                $q->where('i.code', 'like', $search)
                  ->orWhere('i.name', 'like', $search)
                  ->orWhere('sma.description', 'like', $search);
                if (ctype_digit($term)) {
                    $id = (int) $term;
                    $q->orWhere('sma.stockable_id', $id)
                      ->orWhere('sma.id', $id)
                      ->orWhere('sma.archive_batch_id', $id);
                }
            });
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $paginator = $query->select([
            'sma.id', 'sma.stockable_id as inventory_id', 'i.code as inventory_code',
            'i.name as inventory_name', 'sma.amount', 'sma.price', 'sma.total',
            'sma.description', 'sma.second_reference_type', 'sma.second_reference_id',
            'sma.archive_batch_id', 'sma.summary_id', 'jh.post_date',
            'aj.created_at as archive_date', 'aj.range_start', 'aj.range_end',
        ])->orderByRaw('aj.created_at IS NULL ASC')
          ->orderBy('aj.created_at', 'desc')
          ->orderBy('sma.id', 'desc')
          ->paginate($perPage);

        $batches = DB::table('archive_journals')
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get(['id', 'range_start', 'range_end', 'created_at']);

        $unassignedCount = DB::table('stock_mutations_archive')
            ->whereNull('archive_batch_id')
            ->where('is_archive_summary', 0)
            ->when($request->filled('inventory_id'), function ($q) use ($request) {
                $q->where('stockable_id', (int) $request->input('inventory_id'));
            })
            ->count();

        return response()->json([
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'last_page' => $paginator->lastPage(),
            'batches' => $batches,
            'unassigned_count' => $unassignedCount,
        ]);
    }
}
