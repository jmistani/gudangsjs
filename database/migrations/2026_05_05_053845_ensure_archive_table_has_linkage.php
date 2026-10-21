<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureArchiveTableHasLinkage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
	Schema::table('stock_mutations_archive', function (Blueprint $table) {
		$table->unsignedBigInteger('archive_batch_id')->index();
    		$table->unsignedBigInteger('summary_id')->nullable()->index();
	});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
