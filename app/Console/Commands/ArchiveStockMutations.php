<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ArchiveStockJob;
use App\Jobs\RestoreArchiveJob;
use Illuminate\Support\Facades\DB;

class ArchiveStockMutations extends Command
{
    protected $signature = 'stock:archive 
        {target? : inventory id, "all", or omit for all} 
        {--reverse= : provide archive_batch_id to reverse}';

    protected $description = 'Dispatch archive jobs (by inventory id or all) or reverse by batch id.';

    public function handle()
    {
        // Reverse by batch id
        if ($batch = $this->option('reverse')) {
            $this->info("Dispatching RestoreArchiveJob for batch {$batch}...");
            RestoreArchiveJob::dispatch($batch);
            $this->info("Dispatched restore job for batch {$batch}.");
            return 0;
        }

        $target = $this->argument('target');

        if (is_null($target) || $target === 'all') {
            // dispatch jobs for each inventory id
            $this->info("Dispatching ArchiveStockJob for all inventory IDs...");

            $ids = DB::table('stock_mutations')
                ->select('stockable_id')
                ->distinct()
                ->pluck('stockable_id');

            foreach ($ids as $id) {
                ArchiveStockJob::dispatch((int) $id);
                $this->info("Dispatched ArchiveStockJob for inventory {$id}");
            }

            $this->info("All archive jobs dispatched.");
            return 0;
        }

        // Single inventory id
        $inventoryId = (int) $target;
        ArchiveStockJob::dispatch($inventoryId);
        $this->info("Dispatched ArchiveStockJob for inventory {$inventoryId}");

        return 0;
    }
}

