<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddColumnToThemesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (! Schema::hasColumn('theme_settings', 'bg_image'))
        {
            Schema::table('theme_settings', function (Blueprint $table) {
                $table->string('bg_image')->nullable();
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
		if (Schema::hasColumn('theme_settings', 'bg_image'))
        {
            Schema::table('theme_settings', function (Blueprint $table) {
                $table->dropColumn('bg_image');
            });
        }
	}

}
