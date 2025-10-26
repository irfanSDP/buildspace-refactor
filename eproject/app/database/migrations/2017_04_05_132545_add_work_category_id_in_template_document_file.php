<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkCategoryIdInTemplateDocumentFile extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('template_tender_document_files', function (Blueprint $table)
        {
            $table->unsignedInteger('work_category_id')->nullable();

            $table->index('work_category_id');
        });

        Schema::table('template_tender_document_files', function (Blueprint $table)
        {
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
		Schema::table('template_tender_document_files', function(Blueprint $table)
		{
            $table->dropColumn('work_category_id');
        });
	}

}
