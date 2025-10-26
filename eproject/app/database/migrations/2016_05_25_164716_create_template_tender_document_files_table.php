<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateTenderDocumentFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_tender_document_files', function (Blueprint $table)
        {
            $table->increments('id')->unique();
            $table->string('filename');
            $table->text('description')->nullable();
            $table->unsignedInteger('cabinet_file_id')->index();
            $table->unsignedInteger('folder_id')->index();
            $table->unsignedInteger('parent_id')->nullable()->index(); // If this is a child file, it'll be referenced here.
            $table->timestamps();

            $table->foreign('cabinet_file_id')->references('id')->on('uploads')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('template_tender_document_folders')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('template_tender_document_files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('template_tender_document_files');
    }

}
