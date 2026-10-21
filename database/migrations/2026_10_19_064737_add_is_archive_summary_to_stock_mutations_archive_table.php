<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsArchiveSummaryToStockMutationsArchiveTable extends Migration
{
    public function up()
    {
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
    }
}
