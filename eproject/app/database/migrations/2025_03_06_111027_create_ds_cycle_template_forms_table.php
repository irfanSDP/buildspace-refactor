<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsCycleTemplateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_cycle_template_forms', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('ds_cycle_id')->unsigned();
            $table->integer('ds_template_form_id')->unsigned()->nullable();
            $table->string('type');
			$table->timestamps();

            $table->foreign('ds_cycle_id')->references('id')->on('ds_cycles')->onDelete('cascade');
            $table->foreign('ds_template_form_id')->references('id')->on('ds_template_forms')->onDelete('cascade');

            $table->index(['ds_cycle_id', 'type']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_cycle_template_forms');
	}

}
