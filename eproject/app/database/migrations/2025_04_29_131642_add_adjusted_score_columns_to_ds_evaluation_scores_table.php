<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdjustedScoreColumnsToDsEvaluationScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('ds_evaluation_scores', 'company_score_original'))
        {
            Schema::table('ds_evaluation_scores', function(Blueprint $table)
            {
                $table->decimal('company_score_original', 5, 2)->unsigned()->default(0)->after('project_score');
            });
        }

        if (! Schema::hasColumn('ds_evaluation_scores', 'project_score_original'))
        {
            Schema::table('ds_evaluation_scores', function(Blueprint $table)
            {
                $table->decimal('project_score_original', 5, 2)->unsigned()->default(0)->after('company_score_original');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasColumn('ds_evaluation_scores', 'company_score_original'))
        {
            Schema::table('ds_evaluation_scores', function(Blueprint $table)
            {
                $table->dropColumn('company_score_original');
            });
        }

		if (Schema::hasColumn('ds_evaluation_scores', 'project_score_original'))
        {
            Schema::table('ds_evaluation_scores', function(Blueprint $table)
            {
                $table->dropColumn('project_score_original');
            });
        }
	}

}
