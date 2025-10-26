<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateMcarNumberColumnFromSiteManagementMcarTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_mcar', function (Blueprint $table)
		{	
			$table->dropColumn('mcar_number');
		});

		Schema::table('site_management_mcar', function (Blueprint $table)
		{	
			$table->string('mcar_number')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('site_management_mcar', function (Blueprint $table)
		{	
			$table->dropColumn('mcar_number');
		});

		Schema::table('site_management_mcar', function (Blueprint $table)
		{	
			$table->unsignedInteger('mcar_number')->index();
		});
	}

}
