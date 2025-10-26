<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveNotNullConstraintOnTenderIdOnTenderAlternativesPositionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('tender_alternatives_position', function(Blueprint $table)
        {
            DB::statement('ALTER TABLE tender_alternatives_position ALTER COLUMN tender_id DROP NOT NULL');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('tender_alternatives_position', function(Blueprint $table)
        {
            // Delete it.
            \PCK\FormOfTender\TenderAlternativesPosition::whereNull('tender_id')->delete();

            DB::statement('ALTER TABLE tender_alternatives_position ALTER COLUMN tender_id SET NOT NULL');
        });
	}

}
