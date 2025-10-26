<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateTenderDocumentFoldersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_tender_document_folders', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('root_id')->index();
            $table->unsignedInteger('parent_id')->nullable()->index();
            $table->unsignedInteger('lft')->index();
            $table->unsignedInteger('rgt')->index();
            $table->unsignedInteger('depth');
            $table->string('name', 255);

            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('template_tender_document_folders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('template_tender_document_folders');
    }

}
