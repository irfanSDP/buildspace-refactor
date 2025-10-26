<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMenusTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('menus', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('contract_id')->index();
			$table->string('name');
			$table->string('icon_class');
			$table->string('route_name')->nullable();
			$table->integer('priority')->default(1);
			$table->timestamps();

			$table->foreign('contract_id')->references('id')->on('contracts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('menus');
	}

}