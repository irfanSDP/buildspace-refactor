<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Carbon\Carbon;
use PCK\DigitalStar\Evaluation\DsCycleScore;

class CreateDsCompanyScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_company_scores', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->decimal('score', 5, 2)->unsigned()->default(0);
			$table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
		});

        $this->updateScores();
	}

    public function updateScores()
    {
        // Get list of companies from cycle scores
        $cycleScores = DsCycleScore::select('company_id')->orderBy('company_id')->groupBy('company_id')->get();

        if ($cycleScores->isEmpty()) {
            return;
        }
        $companyIds = $cycleScores->lists('company_id');

        $now = Carbon::now();

        foreach($companyIds as $companyId)
        {
            // Get the latest cycle score for the company
            $latestCycleScore = DsCycleScore::where('company_id', $companyId)->orderBy('ds_cycle_id', 'desc')->first();

            // Store the score in the ds_company_scores table
            DB::table('ds_company_scores')->insert([
                'company_id' => $companyId,
                'score' => $latestCycleScore->total_score,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_company_scores');
	}

}
