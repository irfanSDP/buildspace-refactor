<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTenderDocumentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tender_documents', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('project_id')->index();
            $table->string('file_name');
            $table->string('original_file_name');
            $table->char('extension', 5);
            $table->char('mime_type', 50);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tender_documents');
    }

}