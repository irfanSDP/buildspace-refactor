<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_categories', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('contract_group_category_id');
			$table->string('name')->unique();
			$table->string('code', 50)->unique();
			$table->integer('target')->default(0);
			$table->boolean('hidden')->default(false);
			$table->timestamps();

			$table->foreign('contract_group_category_id')->references('id')->on('contract_group_categories')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_categories');
	}

}
