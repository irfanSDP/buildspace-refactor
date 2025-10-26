<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemModuleConfigurationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('system_module_configurations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('module_id')->unique();
			$table->boolean('is_enabled')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('system_module_configurations');
	}

}
