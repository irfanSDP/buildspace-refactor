<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalColumnsRequestForVariationUserPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\DB::table('request_for_variation_user_permissions')->truncate();

		Schema::table('request_for_variation_user_permissions', function(Blueprint $table)
		{
			$table->dropColumn('project_id');

			$table->boolean('can_view_cost_estimate')->default(false);
			$table->boolean('can_view_vo_report')->default(false);
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
		Schema::table('request_for_variation_user_permissions', function(Blueprint $table)
		{
			$table->dropColumn('can_view_cost_estimate');
			$table->dropColumn('can_view_vo_report');
			$table->dropColumn('request_for_variation_user_permission_group_id');

			$table->integer('project_id');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
		});
	}

}
