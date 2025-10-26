<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_roles', function(Blueprint $table)
		{
            $table->increments('id');
            $table->string('slug');
            $table->string('description');
            $table->timestamps();
		});

        DB::table('ds_roles')->insert([
            ['slug' => 'company-evaluator', 'description' => 'Company Evaluator', 'created_at' => new DateTime, 'updated_at' => new DateTime],
            ['slug' => 'company-processor', 'description' => 'Company Processor', 'created_at' => new DateTime, 'updated_at' => new DateTime],
            ['slug' => 'project-evaluator', 'description' => 'Project Evaluator', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('ds_roles');
	}

}
