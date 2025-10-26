<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTitleColumnToStringInProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::statement('ALTER TABLE projects ALTER COLUMN title TYPE varchar');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        //might have undesirable effects, like chopping a title into half
//        DB::statement('ALTER TABLE projects ALTER COLUMN title TYPE varchar(255)');
	}

}
