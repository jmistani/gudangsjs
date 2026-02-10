<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class RestoreArchiveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $batchId;
    public $tries = 3;

    public function __construct(string $batchId)
    {
        $this->batchId = $batchId;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            // 1) Get archive rows for the batch
            $archived = DB::table('stock_mutations_archive')
                ->where('archive_batch_id', $this->batchId)
                ->get();

            if ($archived->isEmpty()) {
                // Nothing to do
                return;
            }

            // 2) Determine summary_id (should be same across rows)
            $summaryId = $archived->first()->summary_id;

            // 3) Insert archived rows back into stock_mutations
            foreach ($archived as $row) {
                $data = (array) $row;

                // remove archive-only fields and metadata
                unset($data['archive_batch_id'], $data['summary_id']);

                // keep original id to restore exact IDs if desired:
                // If you want to preserve original IDs, include 'id' in insert.
                // If your DB uses auto-increment and you don't want to preserve ids, unset 'id'.
                // We'll preserve the original id to fully restore state:
                // (If this causes conflicts, consider adjusting strategy.)
                DB::table('stock_mutations')->insert($data);
            }

            // 4) Delete the summary row from stock_mutations (it was inserted during archive)
            if ($summaryId) {
                DB::table('stock_mutations')->where('id', $summaryId)->delete();
            } else {
                // Fallback: delete any summary row that matches our batch description
                DB::table('stock_mutations')
                    ->where('description', 'like', 'ARCHIVE SUMMARY (batch ' . $this->batchId . ')')
                    ->delete();
            }

            // 5) Delete archive rows
            DB::table('stock_mutations_archive')
                ->where('archive_batch_id', $this->batchId)
                ->delete();
        });
    }

    public function failed(Throwable $exception): void
    {
        \Log::error("RestoreArchiveJob failed for batch {$this->batchId}: " . $exception->getMessage());
    }
}

