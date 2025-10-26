<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('system_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('allow_other_business_entity_types')->default(false);
			$table->boolean('allow_other_property_developers')->default(false);
			$table->boolean('allow_other_vpe_project_removal_reasons')->default(false);
			$table->timestamps();
		});

		\PCK\Settings\SystemSettings::create([]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('system_settings');
	}

}
