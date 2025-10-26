<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionGroupInspectionListCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inspection_group_inspection_list_category', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('inspection_group_id');
			$table->unsignedInteger('inspection_list_category_id');

			$table->foreign('inspection_group_id', 'inspection_group_inspection_list_category_group_id_fk')->references('id')->on('inspection_groups')->onDelete('cascade');
			$table->foreign('inspection_list_category_id', 'inspection_group_inspection_list_category_list_category_id_fk')->references('id')->on('inspection_list_categories')->onDelete('cascade');

			$table->index('inspection_group_id', 'inspection_group_inspection_list_category_group_id_idx');
			$table->index('inspection_list_category_id', 'inspection_group_inspection_list_category_list_category_id_idx');

			$table->unique(array('inspection_list_category_id'), 'inspection_group_list_category_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inspection_group_inspection_list_category');
	}

}
