<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceSystemTendersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_system_tenders', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('tender_id');
            $table->boolean('is_synced')->default(false);
            $table->timestamps();

            $table->foreign('tender_id')->references('id')->on('tenders');

            $table->index('tender_id');
            $table->unique('tender_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('invoice_system_tenders');
    }

}
