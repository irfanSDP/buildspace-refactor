<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkCategoryIdColumnToTemplateTenderDocumentFoldersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template_tender_document_folders', function(Blueprint $table)
        {
            $table->unsignedInteger('work_category_id')->nullable()->default(null);

            $table->foreign('work_category_id')->references('id')->on('work_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('template_tender_document_folders', function(Blueprint $table)
        {
            $table->dropColumn('work_category_id');
        });
    }

}
