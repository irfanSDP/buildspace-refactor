<?php namespace PCK\ProjectModulePermission;

use PCK\Helpers\DataTables;
use PCK\Projects\Project;
use PCK\Users\User;

class ProjectModulePermissionRepository {

    public function getAssignableUsers(Project $project, array $inputs)
    {
        $idColumn      = 'users.id';
        $selectColumns = array( $idColumn, 'users.name' );

        $assignedCompanies = $project->selectedCompanies->reject(function($company) use ($project)
        {
            return $company->hasContractorProjectRole($project);
        })->lists('id');

        $userColumns = array(
            'name'  => 1,
            'email' => 2
        );

        $companyColumns = array(
            'name' => 3
        );

        $allColumns = array(
            'users'     => $userColumns,
            'companies' => $companyColumns
        );

        $moduleId = \Input::get('module_identifier');

        $currentlyAssignedUserIds = ProjectModulePermission::where('project_id', '=', $project->id)->where('module_identifier', '=', $moduleId)->get()->lists('user_id');

        $query = \DB::table("users as users");

        $datatable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $datatable->properties->query->join('companies', 'companies.id', '=', 'users.company_id');
        $datatable->properties->query->where('companies.confirmed', '=', true);
        $datatable->properties->query->whereIn('companies.id', $assignedCompanies);
        $datatable->properties->query->whereNotIn('users.id', $currentlyAssignedUserIds);

        $datatable->addAllStatements();

        $results = $datatable->getResults();

        $dataArray = array();

        foreach($results as $arrayIndex => $arrayItem)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );
            $record  = User::find($arrayItem->id);

            $dataArray[] = array(
                'indexNo'     => $indexNo,
                'id'          => $record->id,
                'name'        => $record->name,
                'email'       => $record->email,
                'companyName' => $record->company->name,
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function getAssignedUsers(Project $project, array $inputs)
    {
        $idColumn      = 'users.id';
        $selectColumns = array( $idColumn, 'users.name' );

        $userColumns = array(
            'name'  => 1,
            'email' => 2
        );

        $companyColumns = array(
            'name' => 3
        );

        $allColumns = array(
            'users'     => $userColumns,
            'companies' => $companyColumns
        );

        $moduleId = \Input::get('module_identifier');

        $currentlyAssignedUserIds = ProjectModulePermission::where('project_id', '=', $project->id)->where('module_identifier', '=', $moduleId)->get()->lists('user_id');

        $query = \DB::table("users as users");

        $datatable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $datatable->properties->query->join('companies', 'companies.id', '=', 'users.company_id');
        $datatable->properties->query->where('companies.confirmed', '=', true);
        $datatable->properties->query->whereIn('users.id', $currentlyAssignedUserIds);

        $datatable->addAllStatements();

        $results = $datatable->getResults();

        $dataArray = array();

        foreach($results as $arrayIndex => $arrayItem)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );
            $record  = User::find($arrayItem->id);

            $dataArray[] = array(
                'indexNo'      => $indexNo,
                'id'           => $record->id,
                'name'         => $record->name,
                'email'        => $record->email,
                'companyName'  => $record->company->name,
                'route:revoke' => route('indonesiaCivilContract.permissions.revoke', array( $project->id, $record->id, $moduleId )),
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function assign(Project $project, array $userIds, $moduleId)
    {
        foreach($userIds as $userId)
        {
            ProjectModulePermission::grant($project, User::find($userId), $moduleId);
        }

        return true;
    }

    public function revoke(Project $project, $userId, $moduleId)
    {
        return ProjectModulePermission::revoke($project, User::find($userId), $moduleId);
    }

}