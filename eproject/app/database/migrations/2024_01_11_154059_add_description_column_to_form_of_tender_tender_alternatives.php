<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDescriptionColumnToFormOfTenderTenderAlternatives extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('form_of_tender_tender_alternatives', function(Blueprint $table)
		{
            $table->text('custom_description')->nullable()->after('tender_alternative_class_name');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('form_of_tender_tender_alternatives', function(Blueprint $table)
		{
            $table->dropColumn('custom_description');
		});
	}

}
