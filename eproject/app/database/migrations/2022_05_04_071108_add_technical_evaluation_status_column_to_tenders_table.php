<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Tenders\Tender;
use PCK\Verifier\Verifier;

class AddTechnicalEvaluationStatusColumnToTendersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tenders', function(Blueprint $table)
		{
			$table->smallInteger('technical_evaluation_status', false, true)->nullable();
		});

		$this->migrateData();
	}

	public function migrateData()
	{
		$tenders = Tender::whereHas('listOfTendererInformation', function($query){
			$query->where('technical_evaluation_required', '=', true);
		})->whereNotNull('technical_tender_closing_date')->get();

		$tenderRepository = \App::make('PCK\Tenders\TenderRepository');

		foreach($tenders as $tender)
		{
			\DB::statement('UPDATE tenders SET technical_evaluation_status = ' . $tenderRepository->determineTechnicalTenderEvaluationStatus($tender) . ' WHERE id = ' . $tender->id);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tenders', function(Blueprint $table)
		{
			$table->dropColumn('technical_evaluation_status');
		});
	}

}
