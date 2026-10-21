<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archive_journals', function (Blueprint $table) {
            $table->unsignedBigInteger('id'); // NOT auto increment
            $table->date('range_start');
            $table->date('range_end');
            $table->timestamps(); // created_at & updated_at
	    $table->index(['range_start', 'range_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_journals');
    }
};
