<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkSubcategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('work_subcategories', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
			$table->timestamps();
		});

        Schema::create('contractor_work_subcategory', function(Blueprint $table)
        {
            $table->integer('contractor_id')->unsigned();
            $table->foreign('contractor_id')
                ->references('id')
                ->on('contractors')
                ->onDelete('cascade');

            $table->integer('work_subcategory_id')->unsigned();
            $table->foreign('work_subcategory_id')
                ->references('id')
                ->on('work_subcategories')
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
        Schema::drop('contractor_work_subcategory');
        Schema::drop('work_subcategories');
	}

}
