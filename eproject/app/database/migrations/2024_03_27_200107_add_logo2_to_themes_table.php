<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogo2ToThemesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (Schema::hasColumn('theme_settings', 'logo'))
        {
            Schema::table('theme_settings', function (Blueprint $table) {
                $table->renameColumn('logo', 'logo1');
            });
        }

        if (! Schema::hasColumn('theme_settings', 'logo2'))
        {
            Schema::table('theme_settings', function (Blueprint $table) {
                $table->string('logo2')->nullable()->after('logo1');
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
        if (Schema::hasColumn('theme_settings', 'logo1'))
        {
            Schema::table('theme_settings', function (Blueprint $table) {
                $table->renameColumn('logo1', 'logo');
            });
        }

        if (Schema::hasColumn('theme_settings', 'logo2'))
        {
            Schema::table('theme_settings', function (Blueprint $table) {
                $table->dropColumn('logo2');
            });
        }
	}

}
