<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTenderAlternativeInformationColumnsIntoTenderRotInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tender_rot_information', function (Blueprint $table)
		{
			$table->integer('completion_period')->default(0);
			$table->decimal('project_incentive_percentage')->nullable()->default(null);
			$table->boolean('allow_contractor_propose_own_completion_period')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tender_rot_information', function (Blueprint $table)
		{
			$table->dropColumn(array(
				'completion_period',
				'project_incentive_percentage',
				'allow_contractor_propose_own_completion_period'
			));
		});
	}

}