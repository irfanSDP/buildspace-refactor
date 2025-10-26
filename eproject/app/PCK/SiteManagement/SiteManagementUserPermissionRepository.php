<?php namespace PCK\SiteManagement;

use PCK\Helpers\DataTables;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission;

class SiteManagementUserPermissionRepository {

	 public function getAssignableUsers(Project $project, array $inputs)
    {

        $moduleId = \Input::get('module_identifier');

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

        $currentlyAssignedUserIds = SiteManagementUserPermission::where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->get()
            ->lists('user_id');

        $userIds = array();

        foreach($project->selectedCompanies()->where('confirmed', '=', true)->get() as $company)
        {
            $userIds = array_merge($userIds, $company->getAllUsers()->lists('id'));
        }

        $query = \DB::table("users as users");

        $datatable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $datatable->properties->query->join('companies', 'companies.id', '=', 'users.company_id');

        $query->where('users.confirmed', '=', true);
        $query->whereIn('users.id', $userIds);
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

    public function getDefectAssignedUsers(Project $project, array $inputs)
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

        $currentlyAssignedUserIds = SiteManagementUserPermission::where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->get()
            ->lists('user_id');

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

            $siteManagementPermission = SiteManagementUserPermission::where('user_id', '=', $arrayItem->id)
                ->where('module_identifier', '=', $moduleId)
                ->where('project_id', '=', $project->id)
                ->first();

            $dataArray[] = array(
                'indexNo'                    => $indexNo,
                'id'                         => $record->id,
                'name'                       => $record->name,
                'email'                      => $record->email,
                'companyName'                => $record->company->name,
                'site'                       => $siteManagementPermission->site,
                'qa_qs_client'               => $siteManagementPermission->qa_qc_client,
                'pm'                         => $siteManagementPermission->pm,
                'qs'                         => $siteManagementPermission->qs,
                'route:revoke'               => route('site-management.permissions.revoke', array( $project->id, $record->id, $moduleId )),
                'route:toggleSiteStatus'     => route('site-management.permissions.site.toggle', array( $project->id, $record->id, $moduleId )),
                'route:toggleClientStatus'   => route('site-management.permissions.client.toggle', array( $project->id, $record->id, $moduleId )),
                'route:togglePmStatus'   	 => route('site-management.permissions.pm.toggle', array( $project->id, $record->id, $moduleId )),
                'route:toggleQsStatus'   	 => route('site-management.permissions.qs.toggle', array( $project->id, $record->id, $moduleId ))
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function getDailyLabourReportsAssignedUsers(Project $project, array $inputs)
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

        $currentlyAssignedUserIds = SiteManagementUserPermission::where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->get()
            ->lists('user_id');

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
                'indexNo'                    => $indexNo,
                'id'                         => $record->id,
                'name'                       => $record->name,
                'email'                      => $record->email,
                'companyName'                => $record->company->name,
                'editor'                     => SiteManagementUserPermission::isEditor($moduleId, $record, $project),
                'viewer'                     => SiteManagementUserPermission::isViewer($moduleId, $record, $project),
                'route:revoke'               => route('site-management.permissions.revoke', array( $project->id, $record->id, $moduleId )),
                'route:toggleEditorStatus'   => route('site-management.permissions.editor.toggle', array( $project->id, $record->id, $moduleId )),
                'route:toggleViewerStatus'   => route('site-management.permissions.viewer.toggle', array( $project->id, $record->id, $moduleId ))
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function getUpdateSiteProgressAssignedUsers(Project $project, array $inputs)
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

        $currentlyAssignedUserIds = SiteManagementUserPermission::where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->get()
            ->lists('user_id');

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
                'indexNo'                    => $indexNo,
                'id'                         => $record->id,
                'name'                       => $record->name,
                'email'                      => $record->email,
                'companyName'                => $record->company->name,
                'route:revoke'               => route('site-management.permissions.revoke', array( $project->id, $record->id, $moduleId ))
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function getSiteDiaryAssignedUsers(Project $project, array $inputs)
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

        $currentlyAssignedUserIds = SiteManagementUserPermission::where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->get()
            ->lists('user_id');

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
                'indexNo'                    => $indexNo,
                'id'                         => $record->id,
                'name'                       => $record->name,
                'email'                      => $record->email,
                'companyName'                => $record->company->name,
                'submitter'                  => SiteManagementUserPermission::isSubmitter($moduleId, $record, $project),
                'verifier'                   => SiteManagementUserPermission::isVerifier($moduleId, $record, $project),
                'viewer'                     => SiteManagementUserPermission::isViewer($moduleId, $record, $project),
                'route:revoke'               => route('site-management.permissions.revoke', array( $project->id, $record->id, $moduleId )),
                'route:toggleViewerStatus'   => route('site-management.permissions.viewer.checkbox.toggle', array( $project->id, $record->id, $moduleId )),
                'route:toggleVerifierStatus' => route('site-management.permissions.verifier.checkbox.toggle', array( $project->id, $record->id, $moduleId )),
                'route:toggleSubmitterStatus'=> route('site-management.permissions.submitter.checkbox.toggle', array( $project->id, $record->id, $moduleId ))
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function getInstructionToContractorAssignedUsers(Project $project, array $inputs)
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

        $currentlyAssignedUserIds = SiteManagementUserPermission::where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->get()
            ->lists('user_id');

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
                'indexNo'                    => $indexNo,
                'id'                         => $record->id,
                'name'                       => $record->name,
                'email'                      => $record->email,
                'companyName'                => $record->company->name,
                'submitter'                  => SiteManagementUserPermission::isSubmitter($moduleId, $record, $project),
                'verifier'                   => SiteManagementUserPermission::isVerifier($moduleId, $record, $project),
                'viewer'                     => SiteManagementUserPermission::isViewer($moduleId, $record, $project),
                'route:revoke'               => route('site-management.permissions.revoke', array( $project->id, $record->id, $moduleId )),
                'route:toggleViewerStatus'   => route('site-management.permissions.viewer.checkbox.toggle', array( $project->id, $record->id, $moduleId )),
                'route:toggleVerifierStatus' => route('site-management.permissions.verifier.checkbox.toggle', array( $project->id, $record->id, $moduleId )),
                'route:toggleSubmitterStatus'=> route('site-management.permissions.submitter.checkbox.toggle', array( $project->id, $record->id, $moduleId ))
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function getDailyReportAssignedUsers(Project $project, array $inputs)
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

        $currentlyAssignedUserIds = SiteManagementUserPermission::where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->get()
            ->lists('user_id');

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
                'indexNo'                    => $indexNo,
                'id'                         => $record->id,
                'name'                       => $record->name,
                'email'                      => $record->email,
                'companyName'                => $record->company->name,
                'submitter'                  => SiteManagementUserPermission::isSubmitter($moduleId, $record, $project),
                'verifier'                   => SiteManagementUserPermission::isVerifier($moduleId, $record, $project),
                'viewer'                     => SiteManagementUserPermission::isViewer($moduleId, $record, $project),
                'route:revoke'               => route('site-management.permissions.revoke', array( $project->id, $record->id, $moduleId )),
                'route:toggleViewerStatus'   => route('site-management.permissions.viewer.checkbox.toggle', array( $project->id, $record->id, $moduleId )),
                'route:toggleVerifierStatus' => route('site-management.permissions.verifier.checkbox.toggle', array( $project->id, $record->id, $moduleId )),
                'route:toggleSubmitterStatus'=> route('site-management.permissions.submitter.checkbox.toggle', array( $project->id, $record->id, $moduleId ))
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function getModules(){

    	return SiteManagementUserPermission::getModuleNames();

    }

    public function assign(array $userIds, $moduleId, Project $project)
    {
        foreach($userIds as $userId)
        {
            SiteManagementUserPermission::assign($moduleId, User::find($userId), $project);
        }

        return true;
    }

    public function revoke($userId, $moduleId, Project $project)
    {
        return SiteManagementUserPermission::unAssign($moduleId, User::find($userId), $project);
    }

    public function toggleSiteStatus($userId, $moduleId, $project)
    {
    	$record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->site = ( ! $record->site );
        $record->qa_qc_client = false;
        $record->pm = false;
        $record->qs = false;
        $record->is_editor = false;
        $record->is_rate_editor = false;
        $record->is_viewer = false;
        $record->is_submitter = false;
        $record->is_verifier = false;

        return $record->save();
    }

    public function toggleClientStatus($userId, $moduleId, $project)
    {
    	$record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->qa_qc_client = ( ! $record->qa_qc_client );
        $record->pm = false;
        $record->qs = false;
        $record->site = false;
        $record->is_editor = false;
        $record->is_rate_editor = false;
        $record->is_viewer = false;
        $record->is_submitter = false;
        $record->is_verifier = false;

        return $record->save();
    }

    public function togglePmStatus($userId, $moduleId, $project)
    {
    	$record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->pm = ( ! $record->pm );
        $record->qa_qc_client = false;
        $record->site = false;
        $record->qs = false;
        $record->is_editor = false;
        $record->is_rate_editor = false;
        $record->is_viewer = false;
        $record->is_submitter = false;
        $record->is_verifier = false;

        return $record->save();
    }

    public function toggleQsStatus($userId, $moduleId, $project)
    {
    	$record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->qs = ( ! $record->qs );
        $record->qa_qc_client = false;
        $record->pm = false;
        $record->site = false;
        $record->is_editor = false;
        $record->is_rate_editor = false;
        $record->is_viewer = false;
        $record->is_submitter = false;
        $record->is_verifier = false;

        return $record->save();
    }

    public function toggleEditorStatus($userId, $moduleId, $project)
    {
        $record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->is_editor = ( ! $record->is_editor );
        
        $record->is_rate_editor = false;
        $record->is_viewer = false;
        $record->is_submitter = false;
        $record->is_verifier = false;
        $record->qa_qc_client = false;
        $record->pm = false;
        $record->site = false;
        $record->qs = false;

        return $record->save();
    }

    public function toggleViewerStatus($userId, $moduleId, $project)
    {
        $record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->is_viewer = ( ! $record->is_viewer );
        $record->is_submitter = false;
        $record->is_verifier = false;
        $record->is_editor = false;
        $record->is_rate_editor = false;
        $record->qa_qc_client = false;
        $record->pm = false;
        $record->site = false;
        $record->qs = false;

        return $record->save();
    }

    public function toggleVerifierStatus($userId, $moduleId, $project)
    {
        $record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->is_verifier = ( ! $record->is_verifier );
        $record->is_submitter = false;
        $record->is_viewer = false;
        $record->is_editor = false;
        $record->is_rate_editor = false;
        $record->qa_qc_client = false;
        $record->pm = false;
        $record->site = false;
        $record->qs = false;

        return $record->save();
    }

    public function toggleSubmitterStatus($userId, $moduleId, $project)
    {
        $record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->is_submitter = ( ! $record->is_submitter );
        $record->is_verifier = false;
        $record->is_viewer = false;
        $record->is_editor = false;
        $record->is_rate_editor = false;
        $record->qa_qc_client = false;
        $record->pm = false;
        $record->site = false;
        $record->qs = false;

        return $record->save();
    }

    public function toggleViewerCheckboxStatus($userId, $moduleId, $project)
    {
        $record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->is_viewer = ( ! $record->is_viewer );

        return $record->save();
    }

    public function toggleVerifierCheckboxStatus($userId, $moduleId, $project)
    {
        $record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->is_verifier = ( ! $record->is_verifier );

        return $record->save();
    }

    public function toggleSubmitterCheckboxStatus($userId, $moduleId, $project)
    {
        $record = SiteManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->is_submitter = ( ! $record->is_submitter );

        return $record->save();
    }

}