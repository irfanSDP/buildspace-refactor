<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormOfTenderTenderAlternativesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('form_of_tender_tender_alternatives', function(Blueprint $table)
		{
			$table->increments('id');

            $table->unsignedInteger('tender_id');
            $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('cascade');

            $table->string('tender_alternative_class_name');

            $table->boolean('show')->default(true);

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
		Schema::drop('form_of_tender_tender_alternatives');
	}

}
