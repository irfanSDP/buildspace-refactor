<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropDeletedAtColumnAtRequestForVariationCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request_for_variation_categories', function (Blueprint $table)
		{
			$table->dropSoftDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('request_for_variation_categories', function (Blueprint $table)
		{
			$table->softDeletes();
		});
	}

}
