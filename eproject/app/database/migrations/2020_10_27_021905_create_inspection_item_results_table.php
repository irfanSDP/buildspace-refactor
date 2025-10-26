<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionItemResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inspection_item_results', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('inspection_result_id');
			$table->unsignedInteger('inspection_list_item_id');
			$table->decimal('progress_status', 5, 2)->default(0);
			$table->text('remarks')->default("");
			$table->timestamps();

			$table->foreign('inspection_result_id')->references('id')->on('inspection_results')->onDelete('cascade');
			$table->foreign('inspection_list_item_id')->references('id')->on('inspection_list_items')->onDelete('cascade');

			$table->unique(array('inspection_result_id', 'inspection_list_item_id'));
			$table->index('inspection_result_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inspection_item_results');
	}

}
