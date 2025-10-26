<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColour2ToThemesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (Schema::hasColumn('theme_settings', 'bg_colour'))
        {
            Schema::table('theme_settings', function (Blueprint $table) {
                $table->renameColumn('bg_colour', 'theme_colour1');
            });
        }

        if (! Schema::hasColumn('theme_settings', 'theme_colour2'))
        {
            Schema::table('theme_settings', function (Blueprint $table) {
                $table->string('theme_colour2')->nullable()->after('theme_colour1');
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
        if (Schema::hasColumn('theme_settings', 'theme_colour1'))
        {
            Schema::table('theme_settings', function (Blueprint $table) {
                $table->renameColumn('theme_colour1', 'bg_colour');
            });
        }

        if (Schema::hasColumn('theme_settings', 'theme_colour2'))
        {
            Schema::table('theme_settings', function (Blueprint $table) {
                $table->dropColumn('theme_colour2');
            });
        }
	}
}
