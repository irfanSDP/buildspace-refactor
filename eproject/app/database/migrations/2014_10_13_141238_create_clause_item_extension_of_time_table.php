<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClauseItemExtensionOfTimeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('clause_item_extension_of_time', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('clause_item_id')->index();
			$table->unsignedInteger('extension_of_time_id')->index();
			$table->timestamps();

			$table->foreign('clause_item_id')->references('id')->on('clause_items')->onDelete('cascade');
			$table->foreign('extension_of_time_id')->references('id')->on('extension_of_times')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('clause_item_extension_of_time');
	}

}