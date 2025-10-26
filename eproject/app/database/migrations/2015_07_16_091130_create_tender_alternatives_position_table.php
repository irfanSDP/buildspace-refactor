<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenderAlternativesPositionTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tender_alternatives_position', function (Blueprint $table)
        {
            $table->increments('id');

            $table->unsignedInteger('tender_id')->nullable();
            $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('cascade');

            $table->unsignedInteger('position');

            $table->boolean('is_template')->default(false);

            $table->unique(array( 'tender_id', 'position' ));

            $table->index('tender_id');

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
        Schema::drop('tender_alternatives_position');
    }

}
