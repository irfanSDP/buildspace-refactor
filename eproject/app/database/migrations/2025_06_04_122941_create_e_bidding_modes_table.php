<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEBiddingModesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('e_bidding_modes', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('slug')->unique();
            $table->string('description');
			$table->timestamps();
		});

        $dateTime = new DateTime;

        DB::table('e_bidding_modes')->insert([
            ['slug' => 'decrement', 'description' => 'Increment', 'created_at' => $dateTime, 'updated_at' => $dateTime],
            ['slug' => 'increment', 'description' => 'Decrement', 'created_at' => $dateTime, 'updated_at' => $dateTime],
            ['slug' => 'once', 'description' => 'Once', 'created_at' => $dateTime, 'updated_at' => $dateTime],
        ]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('e_bidding_modes');
	}

}
