<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Non-destructive schema reconciliation for stock archive features.
 * Does not rewrite existing migrations, mutate stock balances, or post journals.
 */
class SyncStockArchiveSchema extends Migration
{
    public function up()
    {
        foreach (['inventories', 'stock_mutations', 'stock_mutations_archive', 'journal_heads'] as $table) {
            if (!Schema::hasTable($table)) {
                throw new RuntimeException("Required table {$table} is missing. Restore schema first.");
            }
        }

        $this->addColumn('stock_mutations', 'is_archive_summary', function (Blueprint $table) {
            $table->boolean('is_archive_summary')->default(false);
        });

        $this->addColumn('stock_mutations_archive', 'archive_batch_id', function (Blueprint $table) {
            $table->bigInteger('archive_batch_id')->nullable();
        });
        $this->addColumn('stock_mutations_archive', 'summary_id', function (Blueprint $table) {
            $table->bigInteger('summary_id')->nullable();
        });
        $this->addColumn('stock_mutations_archive', 'is_archive_summary', function (Blueprint $table) {
            $table->boolean('is_archive_summary')->default(false);
        });
        $this->addColumn('stock_mutations_archive', 'archived_at', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable();
        });

        $this->addIndex('stock_mutations', 'stock_mutations_is_archive_summary_index', ['is_archive_summary']);
        $this->addIndex('stock_mutations_archive', 'stock_mutations_archive_archive_batch_id_index', ['archive_batch_id']);
        $this->addIndex('stock_mutations_archive', 'stock_mutations_archive_summary_id_index', ['summary_id']);
        $this->addIndex('stock_mutations_archive', 'stock_mutations_archive_archived_at_index', ['archived_at']);
    }

    public function down()
    {
        // Intentional no-op: rollback must not remove archive columns or historical records.
        // To undo newly created indexes/columns, review a dedicated manual migration.
    }

    private function addColumn($tableName, $columnName, callable $definition)
    {
        if (!Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, $definition);
        }
    }

    private function addIndex($tableName, $indexName, array $columns)
    {
        $indexExists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();
        if (!$indexExists) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }
}
