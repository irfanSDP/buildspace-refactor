<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateTenderDocumentFolderWorkCategoryTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_tender_document_folder_work_category', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('template_tender_document_folder_id');
            $table->unsignedInteger('work_category_id');
            $table->timestamps();

            $table->foreign('template_tender_document_folder_id')->references('id')->on('template_tender_document_folders');
            $table->foreign('work_category_id', 'template_tender_doc_folder_wc_work_category_id_fk')->references('id')->on('work_categories');

            $table->unique('work_category_id', 'template_tender_doc_folder_wc_work_category_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('template_tender_document_folder_work_category');
    }

}
