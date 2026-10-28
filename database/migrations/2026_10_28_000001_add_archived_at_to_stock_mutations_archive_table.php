<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArchivedAtToStockMutationsArchiveTable extends Migration
{
    public function up()
    {
        Schema::table('stock_mutations_archive', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('stock_mutations_archive', function (Blueprint $table) {
            $table->dropIndex(['archived_at']);
            $table->dropColumn('archived_at');
        });
    }
}
