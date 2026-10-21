<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RestoreArchiveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $batchId;

    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    public function handle()
    {
        DB::transaction(function () {

            // 1. Get ALL archived rows for batch
            $rows = DB::table('stock_mutations_archive')
                ->where('archive_batch_id', $this->batchId)
                ->get();

            if ($rows->isEmpty()) {
                return;
            }

            // 2. Restore ALL fields exactly
            foreach ($rows as $row) {
                $data = (array) $row;

                // remove archive-only fields
                unset($data['archive_batch_id'], $data['summary_id']);

                DB::table('stock_mutations')->insert($data);
            }

            // 3. Delete summary row
            $summaryId = $rows->first()->summary_id;

            if ($summaryId) {
                DB::table('stock_mutations')
                    ->where('id', $summaryId)
                    ->delete();
            }

            // 4. DELETE archive rows (CRITICAL FIX)
            DB::table('stock_mutations_archive')
                ->where('archive_batch_id', $this->batchId)
                ->delete();
        });
    }
}
