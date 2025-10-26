<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('status_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('status_code')->unique();
            $table->string('status_text');
            $table->timestamps();
        });

        $now = \Carbon\Carbon::now();

        DB::table('status_types')->insert([
            ['status_code' => 1, 'status_text' => 'Design', 'created_at' => $now, 'updated_at' => $now],
			['status_code' => 4, 'status_text' => 'Post Contract', 'created_at' => $now, 'updated_at' => $now],
			['status_code' => 8, 'status_text' => 'Completed', 'created_at' => $now, 'updated_at' => $now],
			['status_code' => 16, 'status_text' => 'Rec. of Tenderer', 'created_at' => $now, 'updated_at' => $now],
			['status_code' => 32, 'status_text' => 'List of Tenderer', 'created_at' => $now, 'updated_at' => $now],
			['status_code' => 64, 'status_text' => 'Calling Tender', 'created_at' => $now, 'updated_at' => $now],
			['status_code' => 128, 'status_text' => 'Closed Tender', 'created_at' => $now, 'updated_at' => $now],
        ]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('status_types');
	}

}
