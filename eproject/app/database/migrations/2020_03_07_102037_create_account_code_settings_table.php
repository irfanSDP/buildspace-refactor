<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\AccountCodeSettings\AccountCodeSetting;
use PCK\AccountCodeSettings\ApportionmentType;

class CreateAccountCodeSettingsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('account_code_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('apportionment_type_id')->default(ApportionmentType::orderBy('id', 'ASC')->first()->id);
			$table->unsignedInteger('account_group_id')->nullable();
			$table->unsignedInteger('submitted_for_approval_by')->nullable();
			$table->unsignedInteger('status')->default(AccountCodeSetting::STATUS_OPEN);
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->foreign('apportionment_type_id')->references('id')->on('apportionment_types');
			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('submitted_for_approval_by')->references('id')->on('users');
			$table->foreign('created_by')->references('id')->on('users');
			$table->foreign('updated_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('account_code_settings');
	}

}
