<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsCyclesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_cycles', function(Blueprint $table)
		{
			$table->increments('id');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->boolean('is_completed')->default(false);
            $table->integer('vendor_management_grade_id')->unsigned()->nullable();
            $table->text('remarks')->nullable();
			$table->timestamps();

            $table->foreign('vendor_management_grade_id')->references('id')->on('vendor_management_grades')->onDelete('set null');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_cycles');
	}

}
