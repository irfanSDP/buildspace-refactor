<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequestForVariationGroupIdColumnRequestForVariationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\DB::statement("TRUNCATE TABLE request_for_variations CASCADE");

		Schema::table('request_for_variations', function(Blueprint $table)
		{
			$table->unsignedInteger('request_for_variation_user_permission_group_id');

			$table->index('request_for_variation_user_permission_group_id');

			$table->foreign('request_for_variation_user_permission_group_id')->references('id')->on('request_for_variation_user_permission_groups')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('request_for_variations', function(Blueprint $table)
		{
			$table->dropColumn('request_for_variation_user_permission_group_id');
		});
	}

}
