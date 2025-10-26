<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRequestForVariationFilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_for_variation_files', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('request_for_variation_id');
			$table->string('filename');
			$table->integer('cabinet_file_id');
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
		Schema::drop('request_for_variation_files');
	}

}
