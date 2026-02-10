<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Throwable;

class ArchiveStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $inventoryId;
    public string $batchId;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(int $inventoryId)
    {
        $this->inventoryId = $inventoryId;
        // Use timestamp + random suffix to avoid collisions across multiple jobs
        $this->batchId = time() . '-' . Str::random(6);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1) Copy rows to archive in chunks and tag them with batchId
        DB::transaction(function () {
            // We'll copy in chunks to avoid memory issues
            DB::table('stock_mutations')
                ->where('stockable_id', $this->inventoryId)
                ->orderBy('id')
                ->chunkById(1000, function ($rows) {
                    $insertRows = [];
                    foreach ($rows as $r) {
                        $row = (array) $r;
                        // Preserve original id in archive (so we can trace later)
                        $row['archive_batch_id'] = $this->batchId;
                        $row['summary_id'] = null;
                        $insertRows[] = $row;
                    }

                    if (!empty($insertRows)) {
                        DB::table('stock_mutations_archive')->insert($insertRows);
                    }
                });
        });

        // 2) Compute sums from the newly inserted archived rows (per batch)
        $sum = DB::table('stock_mutations_archive')
            ->where('archive_batch_id', $this->batchId)
            ->selectRaw('SUM(amount) as total_amount, SUM(total) as total_value')
            ->first();

        $totalAmount = $sum->total_amount ?? 0;
        $totalValue = $sum->total_value ?? 0;

        // Weighted average price (guard against division by zero)
        $avgPrice = $totalAmount > 0 ? ($totalValue / $totalAmount) : 0;

        // 3) Insert summary row into stock_mutations (this is the visible summary replacement)
        $journalhead = new JournalHead;
        $journalhead->post_date = now();
        $journalhead->code = %this->batchId;
        $journalhead->save();
        $summaryId = DB::table('stock_mutations')->insertGetId([
            'stockable_type' => DB::table('stock_mutations_archive')
                ->where('archive_batch_id', $this->batchId)
                ->value('stockable_type') ?? null,
            'stockable_id' => $this->inventoryId,
            'reference_type' => null,
            'reference_id' => null,
            'second_reference_type' => null,
            'second_reference_id' => null,
            'amount' => $totalAmount,
            'description' => 'ARCHIVE SUMMARY (batch ' . $this->batchId . ')',
            'price' => $avgPrice,
            'total' => $totalValue,
            'journal_head_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
	$summaryId->journalhead()->save($journalhead);

        // 4) Update archive rows to reference summary_id
        DB::table('stock_mutations_archive')
            ->where('archive_batch_id', $this->batchId)
            ->update(['summary_id' => $summaryId]);

        // 5) Delete original rows from stock_mutations by referencing archived ids
        //    This uses a subquery: delete where id in (select id from archive where batch = X)
        DB::table('stock_mutations')
            ->whereIn('id', function ($query) {
                $query->select('id')
                    ->from('stock_mutations_archive')
                    ->where('archive_batch_id', $this->batchId);
            })->delete();
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Optionally: log, notify, or set an "error" flag on a batches table if you create one.
        \Log::error("ArchiveStockJob failed for inventory {$this->inventoryId}, batch {$this->batchId}: " . $exception->getMessage());
    }
}

