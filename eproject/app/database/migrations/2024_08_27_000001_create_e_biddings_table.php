<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEBiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 */
	public function up()
	{
		// Creates the news table
		Schema::create('e_biddings', function (Blueprint $table)
		{
			$table->increments('id');

            // Project fields
            $table->unsignedInteger('project_id');
			$table->foreign('project_id')->references('id')->on('projects');

            // Timing Rules
            $table->dateTime('preview_start_time')->nullable();
			$table->boolean('reminder_preview_start_time')->nullable();
            $table->dateTime('bidding_start_time')->nullable();
			$table->boolean('reminder_bidding_start_time')->nullable();
            $table->integer('duration_hours')->nullable();  // Represents hours
            $table->integer('duration_minutes')->nullable();  // Represents minutes

            $table->integer('start_overtime')->nullable(); // Represents minutes
            $table->integer('overtime_period')->nullable(); // Represents minutes

            // Bidding Rules
            $table->boolean('set_budget')->default(false);
            $table->decimal('budget', 19, 2)->nullable(); // Represents RM
            $table->boolean('bid_decrement_percent')->default(true);
            $table->decimal('decrement_percent', 5, 2)->nullable();
            $table->boolean('bid_decrement_value')->default(true);
            $table->decimal('decrement_value', 19, 2)->nullable(); // Represents RM

			// status
			$table->integer('status');

			// Created
            $table->unsignedInteger('created_by');
			$table->foreign('created_by')->references('id')->on('users');

            // Timestamps
            $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down()
	{
		Schema::drop('e_biddings');
	}

}