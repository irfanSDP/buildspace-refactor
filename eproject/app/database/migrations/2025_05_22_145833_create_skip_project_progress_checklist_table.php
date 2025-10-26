<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkipProjectProgressChecklistTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_progress_checklists', function(Blueprint $table)
		{
			$table->increments('id');
            $table->boolean('skip_bq_prepared_published_to_tendering')->default(false);
			$table->boolean('skip_tender_document_uploaded')->default(false);
            $table->boolean('skip_form_of_tender_edited')->default(false);
            $table->boolean('skip_rot_form_submitted')->default(false);
            $table->boolean('skip_lot_form_submitted')->default(false);
            $table->boolean('skip_calling_tender_form_submitted')->default(false);
			$table->unsignedInteger('project_id')->index();

			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
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
		Schema::drop('project_progress_checklists');
	}

}
