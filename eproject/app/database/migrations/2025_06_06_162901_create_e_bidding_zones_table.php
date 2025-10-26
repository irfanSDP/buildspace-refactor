<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEBiddingZonesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('e_bidding_zones', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('e_bidding_id')->unsigned();
            $table->decimal('upper_limit', 19, 2)->unsigned()->default(0);
            $table->string('colour')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
			$table->timestamps();

            $table->foreign('e_bidding_id')->references('id')->on('e_biddings')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('e_bidding_zones');
	}

}
