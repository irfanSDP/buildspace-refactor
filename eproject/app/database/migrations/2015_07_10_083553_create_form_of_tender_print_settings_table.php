<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormOfTenderPrintSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('form_of_tender_print_settings', function(Blueprint $table)
		{
			$table->increments('id');

            $table->unsignedInteger('tender_id');
            $table->foreign('tender_id')->references('id')->on('tenders');

            $table->unsignedInteger('margin_top');
            $table->unsignedInteger('margin_bottom');
            $table->unsignedInteger('margin_left');
            $table->unsignedInteger('margin_right');

            $table->boolean('include_header_line');
            $table->unsignedInteger('header_spacing');

            $table->string('footer_text');
            $table->unsignedInteger('footer_font_size');

            $table->boolean('is_template')->default(false);

            $table->index('tender_id');

            $table->timestamps();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('form_of_tender_print_settings');
	}

}
