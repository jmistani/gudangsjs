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
            'transaction_date_start' => 'nullable|date_format:Y-m-d',
            'transaction_date_end' => 'nullable|date_format:Y-m-d|after_or_equal:transaction_date_start',
            'search' => 'nullable|string|max:191',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:archive_date,archive_batch_id,batch_period,inventory,post_date,masuk,keluar,price,total,reference,description',
            'sort_desc' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Parameter filter tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $query = DB::table('stock_mutations_archive as sma')
            ->leftJoin('inventories as i', 'i.id', '=', 'sma.stockable_id')
            ->leftJoin('archive_journals as aj', 'aj.id', '=', 'sma.archive_batch_id')
            ->leftJoin('journal_heads as jh', 'jh.id', '=', 'sma.journal_head_id')
            ->where('sma.is_archive_summary', 0);

        if ($request->filled('inventory_id')) {
            $query->where('sma.stockable_id', (int) $request->input('inventory_id'));
        }
        if ($request->filled('archive_batch_id')) {
            $query->where('sma.archive_batch_id', (int) $request->input('archive_batch_id'));
        }
        if ($request->filled('date_start')) {
            $query->whereDate('sma.archived_at', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->whereDate('sma.archived_at', '<=', $request->input('date_end'));
        }
        if ($request->filled('transaction_date_start')) {
            $query->whereDate('jh.post_date', '>=', $request->input('transaction_date_start'));
        }
        if ($request->filled('transaction_date_end')) {
            $query->whereDate('jh.post_date', '<=', $request->input('transaction_date_end'));
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

        // Whitelist SQL expressions to prevent arbitrary ORDER BY injection.
        $sortColumns = [
            'archive_date' => 'sma.archived_at',
            'archive_batch_id' => 'sma.archive_batch_id',
            'batch_period' => 'aj.range_start',
            'inventory' => 'i.name',
            'post_date' => 'jh.post_date',
            'masuk' => 'CASE WHEN sma.amount > 0 THEN sma.amount ELSE 0 END',
            'keluar' => 'CASE WHEN sma.amount < 0 THEN -sma.amount ELSE 0 END',
            'price' => 'sma.price',
            'total' => 'sma.total',
            'reference' => 'sma.second_reference_type',
            'description' => 'sma.description',
        ];
        $sortBy = $request->input('sort_by', 'archive_date');
        $sortDirection = (string) $request->input('sort_desc', '1') === '1' ? 'desc' : 'asc';
        $sortExpression = $sortColumns[$sortBy];

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $paginator = $query->select([
            'sma.id', 'sma.stockable_id as inventory_id', 'i.code as inventory_code',
            'i.name as inventory_name', 'sma.amount', 'sma.price', 'sma.total',
            'sma.description', 'sma.second_reference_type', 'sma.second_reference_id',
            'sma.archive_batch_id', 'sma.summary_id', 'jh.post_date',
            'sma.archived_at as archive_date', 'aj.range_start', 'aj.range_end',
        ])->orderByRaw($sortExpression . ' IS NULL ASC')
          ->orderByRaw($sortExpression . ' ' . $sortDirection)
          ->orderBy('sma.id', 'desc')
          ->paginate($perPage);

        // Batch IDs are assigned by ArchiveStockJob, not recorded in archive_journals.
        $batches = DB::table('stock_mutations_archive')
            ->select('archive_batch_id as id')
            ->selectRaw('MIN(archived_at) as created_at')
            ->whereNotNull('archive_batch_id')
            ->where('is_archive_summary', 0)
            ->when($request->filled('inventory_id'), function ($q) use ($request) {
                $q->where('stockable_id', (int) $request->input('inventory_id'));
            })
            ->groupBy('archive_batch_id')
            ->orderByRaw('MIN(archived_at) IS NULL ASC')
            ->orderByRaw('MIN(archived_at) DESC')
            ->limit(200)
            ->get();

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
