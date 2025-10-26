<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterConsultantManagementVerifiersTablesAsSoftDelete extends Migration
{
    public function up()
    {
        $uniqueNames = [
            'consultant_management_recommendation_of_consultant_verifiers' => 'consultant_management_roc_verifiers_unique',
            'consultant_management_list_of_consultant_verifiers' => 'consultant_management_loc_verifiers_unique',
            'consultant_management_calling_rfp_verifiers' => 'consultant_management_call_rfp_verifiers_unique',
            'consultant_management_open_rfp_verifiers' => 'cm_open_rfp_verifiers_unique',
            'consultant_management_rfp_resubmission_verifiers' => 'cm_rfp_resubmission_verifiers_unique'
        ];

        foreach($uniqueNames as $tableName => $uniqueName)
        {
            Schema::table($tableName, function ($table) use($uniqueName) {
                $table->dropUnique($uniqueName);
            });
        }

        $tableNames = [
            'consultant_management_recommendation_of_consultant_verifiers',
            'consultant_management_list_of_consultant_verifiers',
            'consultant_management_calling_rfp_verifiers',
            'consultant_management_open_rfp_verifiers',
            'consultant_management_rfp_resubmission_verifiers'
        ];

        foreach($tableNames as $tableName)
        {
            Schema::table($tableName, function ($table) {
                $table->softDeletes();
            });
        }

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
            ]
        ];

        foreach($uniqueConstraints as $tableName => $uniqueConstraint)
        {
            Schema::table($tableName, function ($table) use($uniqueConstraint) {
                $table->unique([$uniqueConstraint['column'], 'user_id', 'deleted_at'], $uniqueConstraint['name']);
            });
        }
    }

    public function down()
    {
        //no need to revoke changes
    }
}
