<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ArchiveStockJob;
use App\Jobs\RestoreArchiveStockJob;
use Carbon\Carbon;

class ArchiveStockMutations extends Command
{
    protected $signature = 'stock:archive
        {start?}
        {end?}
        {inventory_id?}
        {--reverse=}';

    protected $description = 'Archive or restore stock mutations';

    public function handle()
    {
        if ($batch = $this->option('reverse')) {
            RestoreArchiveStockJob::dispatch($batch);

            $this->info("Restore job dispatched for batch {$batch}");
            return 0;
        }

        $start = $this->argument('start');
        $end = $this->argument('end');
        $inventoryId = $this->argument('inventory_id');

        if (!$start || !$end || !$inventoryId) {
            $this->error(
                'Usage: stock:archive START END INVENTORY_ID'
            );
            return 1;
        }

        try {
            $startDate = Carbon::createFromFormat('!Y-m-d', $start);
            $endDate = Carbon::createFromFormat('!Y-m-d', $end);

            if (!$startDate || !$endDate || $startDate->gt($endDate)) {
                throw new \InvalidArgumentException('Invalid date range');
            }
        } catch (\Throwable $e) {
            $this->error('Invalid dates. Use YYYY-MM-DD.');
            return 1;
        }

        if (!ctype_digit((string) $inventoryId)
            || (int) $inventoryId <= 0) {
            $this->error('Invalid inventory ID');
            return 1;
        }

        if (!$this->confirm(
            "Archive inventory {$inventoryId} from {$start} to {$end}?"
        )) {
            return 0;
        }

        ArchiveStockJob::dispatch(
            $start,
            $end,
            (int) $inventoryId
        );

        $this->info("Archive job dispatched for inventory {$inventoryId}");

        return 0;
    }
}
