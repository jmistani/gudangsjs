<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_mutations_archive', function (Blueprint $table) {

            // Add reversible archive fields
            $table->bigInteger('archive_batch_id')->nullable()->index()->after('deleted_at');
            $table->bigInteger('summary_id')->nullable()->index()->after('archive_batch_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_mutations_archive', function (Blueprint $table) {
            $table->dropColumn(['archive_batch_id', 'summary_id']);
        });
    }
};

