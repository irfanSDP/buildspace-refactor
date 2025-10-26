<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterTableChangeDescriptionColumnNullableOnInspectionListItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('inspection_list_items', function(Blueprint $table)
		{
			DB::statement('ALTER TABLE inspection_list_items ALTER COLUMN description DROP NOT NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('inspection_list_items', function(Blueprint $table)
		{
			DB::statement('ALTER TABLE inspection_list_items ALTER COLUMN description SET NOT NULL');
		});
	}

}
