<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\FormBuilder\DynamicForm;

class AlterDynamicFormsTableAddSubmissionStatusColumn extends Migration
{
	public function up()
	{
		Schema::table('dynamic_forms', function(Blueprint $table)
		{
			$table->integer('submission_status')->default(DynamicForm::SUBMISSION_STATUS_INITIAL);
		});
	}

	public function down()
	{
		Schema::table('dynamic_forms', function(Blueprint $table)
		{
			$table->dropColumn('submission_status');
		});
	}

}
