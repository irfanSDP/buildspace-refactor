<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBusinessEntityTypeColumnToCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->unsignedInteger('business_entity_type_id')->nullable();
			$table->string('business_entity_type_name')->nullable();

			$table->foreign('business_entity_type_id')->references('id')->on('business_entity_types')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->dropColumn('business_entity_type_id');
			$table->dropColumn('business_entity_type_name');
		});
	}

}
