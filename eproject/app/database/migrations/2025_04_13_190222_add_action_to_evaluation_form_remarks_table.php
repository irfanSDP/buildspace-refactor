<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActionToEvaluationFormRemarksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('ds_evaluation_form_remarks', 'action'))
        {
            Schema::table('ds_evaluation_form_remarks', function (Blueprint $table) {
                $table->integer('action')->default(1)->after('company_id');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasColumn('ds_evaluation_form_remarks', 'action'))
        {
            Schema::table('ds_evaluation_form_remarks', function (Blueprint $table) {
                $table->dropColumn('action');
            });
        }
	}

}
