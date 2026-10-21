<?php

namespace App\Jobs;

use App\Models\Inventory;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Money\Currency;
use Money\Money;
use RuntimeException;

class ArchiveStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    public $startDate;
    public $endDate;
    public $inventoryId;

    public function __construct(
        string $startDate,
        string $endDate,
        int $inventoryId
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->inventoryId = $inventoryId;
    }

    public function handle()
    {
        $start = $this->parseDate($this->startDate);
        $end = $this->parseDate($this->endDate);

        if ($start->gt($end)) {
            throw new InvalidArgumentException(
                'Start date must be on or before end date.'
            );
        }

        if ($this->inventoryId <= 0) {
            throw new InvalidArgumentException(
                'Inventory ID must be positive.'
            );
        }

        $endExclusive = $end->addDay();

        $result = DB::transaction(function () use (
            $start,
            $end,
            $endExclusive
        ) {
            $inventory = Inventory::query()
                ->whereKey($this->inventoryId)
                ->lockForUpdate()
                ->firstOrFail();

            $stockableType = $inventory->getMorphClass();

            $rows = DB::table('stock_mutations as sm')
                ->join(
                    'journal_heads as jh',
                    'jh.id',
                    '=',
                    'sm.journal_head_id'
                )
                ->where('sm.stockable_type', $stockableType)
                ->where('sm.stockable_id', $this->inventoryId)
                ->whereNull('sm.deleted_at')
                ->where('sm.is_archive_summary', false)
                ->where(
                    'jh.post_date',
                    '>=',
                    $start->toDateString()
                )
                ->where(
                    'jh.post_date',
                    '<',
                    $endExclusive->toDateString()
                )
                ->select('sm.*')
                ->orderBy('sm.id')
                ->lockForUpdate()
                ->get();

            if ($rows->isEmpty()) {
                return null;
            }

            $currency = new Currency(config('gudang.currency'));

            // These columns are signed BIGINTs.
            // Money performs exact integer arithmetic without float sums.
            $quantity = new Money('0', $currency);
            $value = new Money('0', $currency);

            foreach ($rows as $row) {
                if ($row->amount === null || $row->total === null) {
                    throw new RuntimeException(
                        "Mutation {$row->id} has a null amount or total."
                    );
                }

                $quantity = $quantity->add(
                    new Money((string) $row->amount, $currency)
                );

                $value = $value->add(
                    new Money((string) $row->total, $currency)
                );
            }

            $netQuantity = $quantity->getAmount();

            $average = $netQuantity !== '0'
                ? $value->divide($netQuantity)
                : new Money('0', $currency);

            // Original IDs are preserved in the archive.
            // The first selected ID also identifies this archive batch.
            $batchId = $rows->first()->id;

            if (
                DB::table('stock_mutations_archive')
                    ->where('archive_batch_id', $batchId)
                    ->exists()
            ) {
                throw new RuntimeException(
                    "Archive batch {$batchId} already exists."
                );
            }

            $summaryId = DB::table('stock_mutations')
                ->insertGetId([
                    'stockable_type' => $stockableType,
                    'stockable_id' => $this->inventoryId,

                    'reference_type' => null,
                    'reference_id' => null,
                    'second_reference_type' => null,
                    'second_reference_id' => null,

                    'amount' => $netQuantity,
                    'price' => $average->getAmount(),
                    'total' => $value->getAmount(),

                    'description' =>
                        "ARCHIVE SUMMARY (batch {$batchId}, "
                        . "{$this->startDate} to {$this->endDate})",

                    'journal_head_id' => null,
                    'is_archive_summary' => true,

                    'created_at' => $end->endOfDay(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ]);

            foreach ($rows->chunk(250) as $chunk) {
                $archiveRows = [];

                foreach ($chunk as $row) {
                    $data = (array) $row;

                    $data['archive_batch_id'] = $batchId;
                    $data['summary_id'] = $summaryId;

                    $archiveRows[] = $data;
                }

                // A duplicate ID or any insert failure rolls back
                // the entire archive operation.
                DB::table('stock_mutations_archive')
                    ->insert($archiveRows);

                // Permanently remove only the copied originals.
                $deleted = DB::table('stock_mutations')
                    ->where('stockable_type', $stockableType)
                    ->where('stockable_id', $this->inventoryId)
                    ->where('is_archive_summary', false)
                    ->whereNull('deleted_at')
                    ->whereIn('id', $chunk->pluck('id')->all())
                    ->delete();

                if ($deleted !== $chunk->count()) {
                    throw new RuntimeException(
                        'Archived and deleted row counts do not match.'
                    );
                }
            }

            // Do not change inventories.in_stock, stock_value,
            // or avg_value: the summary replaces the same net totals.
            return [
                'inventory_id' => $this->inventoryId,
                'batch_id' => $batchId,
                'summary_id' => $summaryId,
                'archived_count' => $rows->count(),
                'quantity' => $netQuantity,
                'value' => $value->getAmount(),
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
            ];
        });

        // Log completion only after the transaction commits.
        if ($result === null) {
            Log::info('No stock mutations to archive', [
                'inventory_id' => $this->inventoryId,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
            ]);

            return;
        }

        Log::info('Stock archive completed', $result);
    }

    private function parseDate(string $input): CarbonImmutable
    {
        try {
            $date = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                $input
            );
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException(
                'Invalid date. Use YYYY-MM-DD.',
                0,
                $exception
            );
        }

        if (!$date || $date->format('Y-m-d') !== $input) {
            throw new InvalidArgumentException(
                'Invalid date. Use YYYY-MM-DD.'
            );
        }

        return $date;
    }
}