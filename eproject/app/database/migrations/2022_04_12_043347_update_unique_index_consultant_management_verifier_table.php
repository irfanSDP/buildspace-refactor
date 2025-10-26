<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUniqueIndexConsultantManagementVerifierTable extends Migration
{
    public function up()
    {
        $uniqueConstraints = [
            'consultant_management_recommendation_of_consultant_verifiers' => [
                'name' => 'consultant_management_roc_verifiers_unique',
                'column' => 'consultant_management_recommendation_of_consultant_id'
            ],
            'consultant_management_list_of_consultant_verifiers' => [
                'name'=> 'consultant_management_loc_verifiers_unique',
                'column' => 'consultant_management_list_of_consultant_id'
            ],
            'consultant_management_calling_rfp_verifiers' => [
                'name' => 'consultant_management_call_rfp_verifiers_unique',
                'column' => 'consultant_management_calling_rfp_id'
            ],
            'consultant_management_open_rfp_verifiers' => [
                'name' => 'cm_open_rfp_verifiers_unique',
                'column' => 'consultant_management_open_rfp_id'
            ],
            'consultant_management_rfp_resubmission_verifiers' => [
                'name' => 'cm_rfp_resubmission_verifiers_unique',
                'column' => 'consultant_management_open_rfp_id'
            ],
            'consultant_management_approval_document_verifiers' => [
                'name' => 'cmad_verifiers_unique',
                'column' => 'consultant_management_approval_document_id'
            ],
            'consultant_management_letter_of_award_verifiers' => [
                'name' => 'cmloa_verifiers_unique',
                'column' => 'consultant_management_letter_of_award_id'
            ]
        ];

        foreach($uniqueConstraints as $tableName => $uniqueConstraint)
        {
            $uniqueName = $uniqueConstraint['name'];
            Schema::table($tableName, function ($table) use($uniqueName) {
                $table->dropUnique($uniqueName);
            });
        }

        foreach($uniqueConstraints as $tableName => $uniqueConstraint)
        {
            \DB::statement("CREATE UNIQUE INDEX ".$uniqueConstraint['name']."
            ON ".$tableName."(".$uniqueConstraint['column'].", user_id)
            WHERE deleted_at IS NULL");
        }
    }

    public function down()
    {
        //no need to revoke changes
    }
}
