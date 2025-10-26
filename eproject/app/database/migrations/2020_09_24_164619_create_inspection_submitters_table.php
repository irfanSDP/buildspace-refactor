<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionSubmittersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inspection_submitters', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('inspection_group_id');
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->foreign('inspection_group_id')->references('id')->on('inspection_groups')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

			$table->index('user_id');
			$table->index(array('inspection_group_id', 'user_id'));
			$table->unique(array('inspection_group_id', 'user_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inspection_submitters');
	}

}
