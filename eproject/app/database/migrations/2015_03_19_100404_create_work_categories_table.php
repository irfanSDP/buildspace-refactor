<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('work_categories', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
			$table->timestamps();
		});

        Schema::create('contractor_work_category', function(Blueprint $table)
        {
            $table->integer('contractor_id')->unsigned();
            $table->foreign('contractor_id')
                ->references('id')
                ->on('contractors')
                ->onDelete('cascade');

            $table->integer('work_category_id')->unsigned();
            $table->foreign('work_category_id')
                ->references('id')
                ->on('work_categories')
                ->onDelete('cascade');

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
        Schema::drop('contractor_work_category');
        Schema::drop('work_categories');
	}

}
