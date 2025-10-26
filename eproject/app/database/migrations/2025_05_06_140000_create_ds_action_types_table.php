<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsActionTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_action_types', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('slug', 50);
            $table->string('description');
			$table->timestamps();
		});

        $dateTime = new DateTime;

        DB::table('ds_action_types')->insert(
            [
                ['slug' => 'submitted', 'description' => 'Submitted', 'created_at' => $dateTime, 'updated_at' => $dateTime],
                ['slug' => 'submitted-to-processor', 'description' => 'Submitted To Processor', 'created_at' => $dateTime, 'updated_at' => $dateTime],
                ['slug' => 'submitted-for-approval', 'description' => 'Submitted For Approval', 'created_at' => $dateTime, 'updated_at' => $dateTime],
                ['slug' => 'rejected', 'description' => 'Rejected', 'created_at' => $dateTime, 'updated_at' => $dateTime],
                ['slug' => 'approved', 'description' => 'Approved', 'created_at' => $dateTime, 'updated_at' => $dateTime],
            ]
        );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_action_types');
	}

}
