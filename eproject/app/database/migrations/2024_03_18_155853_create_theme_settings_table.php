<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThemeSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('theme_settings', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('logo')->nullable();
			$table->string('bg_colour')->nullable();
            $table->boolean('active')->default(false);
			$table->timestamps();
		});

        $created_at = date('Y-m-d H:i:s');

        DB::table('theme_settings')->insert(array(
            'active' => true,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ));
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('theme_settings');
	}

}
