<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropSoftdeleteFromEvaluationTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (Schema::hasColumn('ds_evaluations', 'deleted_at'))
        {
            // === Step 1: Loop through soft-deleted evaluations ===
            $evaluations = DB::table('ds_evaluations')->whereNotNull('deleted_at')->get();

            if (count($evaluations) > 0)
            {
                $evaluationIds = array_map(function ($row) {
                    return $row->id;
                }, $evaluations);

                foreach ($evaluations as $evaluation) {
                    $evaluationId = $evaluation->id;

                    // === Step 2: Get related evaluation forms ===
                    $forms = DB::table('ds_evaluation_forms')->where('ds_evaluation_id', $evaluationId)->get();

                    foreach ($forms as $form) {
                        $formId = $form->id;

                        // === Step 2.1: Delete form-related records ===
                        DB::table('ds_evaluation_form_remarks')->where('ds_evaluation_form_id', $formId)->delete();
                        DB::table('ds_evaluation_logs')->where('ds_evaluation_form_id', $formId)->delete();
                        DB::table('ds_evaluation_form_user_roles')->where('ds_evaluation_form_id', $formId)->delete();
                    }

                    // === Step 3: Delete evaluation-related scores ===
                    DB::table('ds_evaluation_scores')->where('ds_evaluation_id', $evaluationId)->delete();
                }

                // === Step 4: Drop soft delete columns ===
                Schema::table('ds_evaluation_forms', function (Blueprint $table) {
                    $table->dropColumn('deleted_at');
                });

                Schema::table('ds_evaluations', function(Blueprint $table) {
                    $table->dropColumn('deleted_at');
                });

                // === Step 5: Delete the forms ===
                DB::table('ds_evaluation_forms')->whereIn('ds_evaluation_id', $evaluationIds)->delete();

                // === Step 6: Finally delete the evaluation ===
                DB::table('ds_evaluations')->whereIn('id', $evaluationIds)->delete();
            }
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        // Soft deletes have been removed permanently.
        // Cannot restore deleted records.
	}

}
