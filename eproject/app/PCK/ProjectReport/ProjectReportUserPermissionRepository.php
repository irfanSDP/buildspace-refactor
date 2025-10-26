<?php namespace PCK\ProjectReport;

use PCK\Projects\Project;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;
use PCK\Users\User;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

class ProjectReportUserPermissionRepository
{
    public function getProjectReportTypesListing(Project $project)
    {
        $projectTypeIdentifier = is_null($project->parent_project_id) ? Project::TYPE_MAIN_PROJECT : Project::TYPE_SUB_PACKAGE;
        $projectReportTypes    = ProjectReportType::getProjectReportTypeWithMapping($projectTypeIdentifier);

        $data = [];

        foreach($projectReportTypes as $reportType)
        {
            $temp = [
                'id' => $reportType['report_type_id'],
                'title' => $reportType['report_type_title'],
                'report_type_id' => $reportType['report_type_id'],
                'submitter' => [
                    'enable' => true,
                    'identifier' => ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT,
                    'count' => $this->getAssignedUsersCount($project, $reportType['report_type_id'], ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT),
                ],
                'verifier' => [
                    'enable' => true,
                    'identifier' => ProjectReportUserPermission::IDENTIFIER_VERIFY_REPORT,
                    'count' => $this->getAssignedUsersCount($project, $reportType['report_type_id'], ProjectReportUserPermission::IDENTIFIER_VERIFY_REPORT),
                ],
            ];

            if ($reportType['mapping_latest_rev'])
            {
                $temp['editor'] = [
                    'enable' => true,
                    'identifier' => ProjectReportUserPermission::IDENTIFIER_EDIT_REMINDER,
                    'count' => $this->getAssignedUsersCount($project, $reportType['report_type_id'], ProjectReportUserPermission::IDENTIFIER_EDIT_REMINDER),
                ];
                $temp['receiver'] = [
                    'enable' => true,
                    'identifier' => ProjectReportUserPermission::IDENTIFIER_RECEIVE_REMINDER,
                    'count' => $this->getAssignedUsersCount($project, $reportType['report_type_id'], ProjectReportUserPermission::IDENTIFIER_RECEIVE_REMINDER),
                ];
            } else {
                $temp['editor'] = [
                    'enable' => false,
                    'identifier' => ProjectReportUserPermission::IDENTIFIER_EDIT_REMINDER,
                    'count' => 0,
                ];
                $temp['receiver'] = [
                    'enable' => false,
                    'identifier' => ProjectReportUserPermission::IDENTIFIER_RECEIVE_REMINDER,
                    'count' => 0,
                ];
            }

            $data[] = $temp;
        }

        return $data;
    }

    private function getAssignedUsersCount(Project $project, $projectReportTypeId, $identifier)
    {
        return $project->projectReportUserPermissions()
                ->where('project_report_type_id', $projectReportTypeId)
                ->where('identifier', $identifier)
                ->count();
    }

    public function getAssignedUsers(Project $project, $projectReportTypeId, $identifier)
    {
        $records = $project->projectReportUserPermissions()
                    ->where('project_report_type_id', $projectReportTypeId)
                    ->where('identifier', $identifier)
                    ->orderBy('user_id', 'ASC')
                    ->get();

        $data = [];

        foreach($records as $record)
        {
            array_push($data, [
                'id'           => $record->id,
                'project_id'   => $record->project_id,
                'identifier'   => $record->identifier,
                'user_id'      => $record->user->id,
                'user_name'    => $record->user->name,
                'user_email'   => $record->user->email,
            ]);
        }

        return $data;
    }

    public function getAssignableUsers(Project $project, $projectReportTypeId, $identifer)
    {
        // get all active users of assigned companies
        $selectedCompaniesUserIds = [];

        foreach($project->selectedCompanies as $selectedCompany)
        {
            $selectedCompaniesUserIds = array_merge($selectedCompaniesUserIds, $selectedCompany->getActiveUsers()->lists('id'));
        }

        $cgProjectUserRepo = \App::make(ContractGroupProjectUserRepository::class);

        $contractGroups = ContractGroup::findByGroups(Role::getAllRoles());

        $projectUserIds = [];

        foreach($contractGroups as $contractGroup)
        {
            $companyProjectUsers   = $cgProjectUserRepo->getAssignedUsersByProjectAndContractGroup($project, $contractGroup);
            $companyProjectUserIds = array_keys($companyProjectUsers);

            foreach($companyProjectUserIds as $userId)
            {
                if (!in_array($userId, $selectedCompaniesUserIds))
                {
                    continue;
                }

                array_push($projectUserIds, $userId);
            }
        }

        $data = [];

        $assignedUserIds = ProjectReportUserPermission::where('project_id', $project->id)
                            ->where('identifier', $identifer)
                            ->where('project_report_type_id', $projectReportTypeId)
                            ->lists('user_id');
        
        $projectUsers = User::whereIn('id', $projectUserIds)->get()->reject(function($user) use ($assignedUserIds) {
            return in_array($user->id, $assignedUserIds);
        });

        foreach($projectUsers as $user)
        {
            array_push($data, [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ]);
        }

        return $data;
    }

    public function grant(Project $project, $projectReportTypeId, $userIds, $identifer)
    {
        foreach($userIds as $userId)
        {
            $record                         = new ProjectReportUserPermission();
            $record->project_id             = $project->id;
            $record->user_id                = $userId;
            $record->identifier             = $identifer;
            $record->project_report_type_id = $projectReportTypeId;
            $record->save();
        }

        return true;
    }
}