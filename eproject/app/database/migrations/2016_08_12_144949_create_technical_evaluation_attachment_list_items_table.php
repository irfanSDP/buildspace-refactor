<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicalEvaluationAttachmentListItemsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technical_evaluation_attachment_list_items', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('set_reference_id');
            $table->string('description');
            $table->boolean('compulsory')->default(true);
            $table->timestamps();

            $table->foreign('set_reference_id')->references('id')->on('technical_evaluation_set_references');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('technical_evaluation_attachment_list_items');
    }

}
