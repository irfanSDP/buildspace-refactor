<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FlushDsEvaluationTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        // If table exists...
        if (Schema::hasTable('ds_project_evaluators')) {
            // Drop the table - No longer needed -> Integrated into ds_evaluation_form_user_roles
            Schema::drop('ds_project_evaluators');
        }

        // If table exists...
        if (Schema::hasTable('ds_processors')) {
            // Drop the table - No longer needed -> Integrated into ds_evaluation_form_user_roles
            Schema::drop('ds_processors');
        }

        if (Schema::hasTable('ds_evaluations')) {
            $records = DB::table('ds_evaluations')->select('id')->get();
            foreach ($records as $record) {
                DB::table('ds_evaluations')->where('id', $record->id)->delete();
            }
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Do nothing
	}

}
