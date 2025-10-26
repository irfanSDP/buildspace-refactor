<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInspectionListCategoryAdditionalFieldsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inspection_list_category_additional_fields', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('inspection_list_category_id');
			$table->string('name')->nullable();
			$table->text('value')->nullable();
			$table->unsignedInteger('priority');
			$table->timestamps();

			$table->index('inspection_list_category_id');

			$table->foreign('inspection_list_category_id')->references('id')->on('inspection_list_categories')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inspection_list_category_additional_fields');
	}
}
