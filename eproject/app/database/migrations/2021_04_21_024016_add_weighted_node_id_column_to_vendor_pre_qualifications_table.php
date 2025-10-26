<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWeightedNodeIdColumnToVendorPreQualificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_pre_qualifications', function(Blueprint $table)
		{
			$table->unsignedInteger('weighted_node_id')->nullable();
			$table->foreign('weighted_node_id')->references('id')->on('weighted_nodes')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_pre_qualifications', function(Blueprint $table)
		{
			$table->dropColumn('weighted_node_id');
		});
	}

}
