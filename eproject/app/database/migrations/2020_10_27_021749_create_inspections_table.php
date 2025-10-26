<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inspections', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('request_for_inspection_id');
			$table->unsignedInteger('revision')->default(0);
			$table->text('comments')->nullable();
			$table->timestamp('ready_for_inspection_date')->nullable();
			$table->integer('status');
			$table->integer('decision')->nullable();
			$table->timestamps();

			$table->foreign('request_for_inspection_id')->references('id')->on('request_for_inspections')->onDelete('cascade');

			$table->unique(array('request_for_inspection_id', 'revision'));
			$table->index('request_for_inspection_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inspections');
	}

}
