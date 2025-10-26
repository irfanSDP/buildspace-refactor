<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\CompanyPersonnel\CompanyPersonnelSetting;

class CreateCompanyPersonnelSettingsTable extends Migration
{
	public function up()
	{
		Schema::create('company_personnel_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('has_attachments')->default(false);
			$table->timestamps();
		});

		if(is_null(CompanyPersonnelSetting::first()))
		{
			$record = new CompanyPersonnelSetting();
			$record->save();
		}
	}

	public function down()
	{
		Schema::drop('company_personnel_settings');
	}
}
