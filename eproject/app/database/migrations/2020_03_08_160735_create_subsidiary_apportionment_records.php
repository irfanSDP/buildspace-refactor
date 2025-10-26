<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CreateSubsidiaryApportionmentRecords extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subsidiary_apportionment_records', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('subsidiary_id')->index();
			$table->unsignedInteger('apportionment_type_id')->index();
			$table->decimal('value', 19, 2);
			$table->boolean('is_locked')->default(false);
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->foreign('subsidiary_id')->references('id')->on('subsidiaries');
			$table->foreign('apportionment_type_id')->references('id')->on('apportionment_types');
			$table->foreign('created_by')->references('id')->on('users');
			$table->foreign('updated_by')->references('id')->on('users');

			$table->unique(['subsidiary_id', 'apportionment_type_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subsidiary_apportionment_records');
	}

}
