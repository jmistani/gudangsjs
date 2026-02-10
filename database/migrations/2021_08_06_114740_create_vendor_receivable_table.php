<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorReceivableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_receivables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',191);
            $table->integer('vendor_id')->unsigned();
            //rest of fields then...
            $table->foreign('vendor_id')
                ->references('id')->on('vendors')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendor_receivables');
    }
}
