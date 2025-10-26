<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormOfTenderClausesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('form_of_tender_clauses', function(Blueprint $table)
		{
			$table->increments('id');

            $table->text('clause');

            $table->unsignedInteger('parent_id')->default(0);

            $table->unsignedInteger('sequence_number')->default(0);

            $table->unsignedInteger('tender_id');
            $table->foreign('tender_id')->references('id')->on('tenders');

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
		Schema::drop('form_of_tender_clauses');
	}

}
