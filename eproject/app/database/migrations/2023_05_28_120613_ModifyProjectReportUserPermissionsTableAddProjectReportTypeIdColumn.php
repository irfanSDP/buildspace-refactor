<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\ProjectReport\ProjectReportUserPermission;
use PCK\ProjectReport\ProjectReportRepository;
use PCK\ProjectReport\ProjectReportType;
use PCK\Projects\Project;

class ModifyProjectReportUserPermissionsTableAddProjectReportTypeIdColumn extends Migration
{
	public function up()
	{
		Schema::table('project_report_user_permissions', function(Blueprint $table)
        {
            $table->unsignedInteger('project_report_type_id')->nullable();
    
            $table->index('project_report_type_id');
    
            $table->foreign('project_report_type_id')->references('id')->on('project_report_types')->onDelete('cascade');

            $result = DB::select("
                select constraint_name
                from information_schema.constraint_column_usage
                where constraint_name = 'project_report_user_permissions_project_id_user_id_identifier_u'
            ");

            if( ! empty( $result ) )
            {
                $table->dropUnique('project_report_user_permissions_project_id_user_id_identifier_u');
            }
        });

        $this->migrateData();
	}

    private function migrateData()
    {
        $projectReportRepostiory = App::make(ProjectReportRepository::class);

        $userPermissionRecords = ProjectReportUserPermission::orderBy('project_id', 'ASC')
                                    ->orderBy('identifier', 'ASC')
                                    ->orderBy('user_id', 'ASC')
                                    ->get();

        foreach($userPermissionRecords as $record)
        {
            $existingRecord = true;

            $projectTypeIdentifier    = is_null($record->project->parent_project_id) ? Project::TYPE_MAIN_PROJECT : Project::TYPE_SUB_PACKAGE;
            $mappedProjectReportTypes = ProjectReportType::getProjectReportTypeWithMapping($projectTypeIdentifier);

            foreach($mappedProjectReportTypes as $type)
            {
                // update the existing record
                if($existingRecord)
                {
                    $record->project_report_type_id = $type->report_type_id;
                    $record->save();

                    $existingRecord = false;
                }
                else 
                {
                    $newRecord                         = new ProjectReportUserPermission();
                    $newRecord->project_id             = $record->project_id;
                    $newRecord->user_id                = $record->user_id;
                    $newRecord->identifier             = $record->identifier;
                    $newRecord->project_report_type_id = $type->report_type_id;
                    $newRecord->save();
                }
            }
        }
    }

	public function down()
	{
        Schema::table('project_report_user_permissions', function(Blueprint $table)
        {
		    $table->dropColumn('project_report_type_id');
        });
	}
}
