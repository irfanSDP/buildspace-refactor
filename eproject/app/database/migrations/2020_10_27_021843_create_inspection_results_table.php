<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inspection_results', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('inspection_id');
			$table->unsignedInteger('inspection_role_id');
			$table->integer('status');
			$table->unsignedInteger('submitted_by')->nullable();
			$table->timestamp('submitted_at')->nullable();
			$table->timestamps();

			$table->foreign('inspection_id')->references('id')->on('inspections')->onDelete('cascade');
			$table->foreign('inspection_role_id')->references('id')->on('inspection_roles')->onDelete('cascade');
			$table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');

			$table->unique(array('inspection_id', 'inspection_role_id'));
			$table->index('inspection_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inspection_results');
	}

}
