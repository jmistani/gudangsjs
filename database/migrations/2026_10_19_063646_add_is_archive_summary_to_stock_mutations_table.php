<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsArchiveSummaryToStockMutationsTable extends Migration
{
    public function up()
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->boolean('is_archive_summary')
                ->default(false)
                ->index();
        });

        Schema::table('stock_mutations_archive', function (Blueprint $table) {
            $table->boolean('is_archive_summary')
                ->default(false);
        });
    }

    public function down()
    {
        Schema::table('stock_mutations_archive', function (Blueprint $table) {
            $table->dropColumn('is_archive_summary');
        });

        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->dropIndex(['is_archive_summary']);
            $table->dropColumn('is_archive_summary');
        });
    }
}