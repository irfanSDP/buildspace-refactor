<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionVerifierTemplateTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inspection_verifier_template', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('inspection_group_id');
			$table->unsignedInteger('user_id');
			$table->integer('priority');
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
		Schema::drop('inspection_verifier_template');
	}

}
