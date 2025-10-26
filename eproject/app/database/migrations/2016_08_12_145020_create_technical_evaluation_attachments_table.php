<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicalEvaluationAttachmentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technical_evaluation_attachments', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('upload_id');
            $table->string('filename');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('item_id')->references('id')->on('technical_evaluation_attachment_list_items');
            $table->foreign('upload_id')->references('id')->on('uploads');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('technical_evaluation_attachments');
    }

}
