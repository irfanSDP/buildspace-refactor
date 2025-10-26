<?php

use PCK\SiteManagement\SiteManagementUserPermissionRepository;
use PCK\SiteManagement\SiteManagementDefect;
use PCK\Projects\Project;
use PCK\Verifier\Verifier;

class SiteManagementUserPermissionsController extends \BaseController {

	private $repository;

	public function __construct(SiteManagementUserPermissionRepository $repository){

		$this->repository = $repository;

	}

	public function index(Project $project)
    {
        $modules = $this->repository->getModules();

        return View::make('site_management_user_permissions.index', array(
            'modules' => $modules,
            'project' => $project,
        ));
    }

    public function getAssignableUsers(Project $project)
    {
        return Response::json($this->repository->getAssignableUsers($project, Input::all()));
    }

    public function getDefectAssignedUsers(Project $project)
    {
        return Response::json($this->repository->getDefectAssignedUsers($project, Input::all()));
    }

    public function getDailyLabourReportsAssignedUsers(Project $project)
    {
        return Response::json($this->repository->getDailyLabourReportsAssignedUsers($project, Input::all()));
    }

    public function getUpdateSiteProgressAssignedUsers(Project $project)
    {
        return Response::json($this->repository->getUpdateSiteProgressAssignedUsers($project, Input::all()));
    }

    public function getSiteDiaryAssignedUsers(Project $project)
    {
        return Response::json($this->repository->getSiteDiaryAssignedUsers($project, Input::all()));
    }

    public function getInstructionToContractorAssignedUsers(Project $project)
    {
        return Response::json($this->repository->getInstructionToContractorAssignedUsers($project, Input::all()));
    }
    public function getDailyReportAssignedUsers(Project $project)
    {
        return Response::json($this->repository->getDailyReportAssignedUsers($project, Input::all()));
    }

    public function assign(Project $project)
    {
        $success = $this->repository->assign(Input::get('users') ?? array(), Input::get('module_id'), $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function revoke(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->revoke($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function toggleSiteStatus(Project $project, $userId, $moduleId)
    {
    	$success = $this->repository->toggleSiteStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function toggleClientStatus(Project $project, $userId, $moduleId)
    {
    	$success = $this->repository->toggleClientStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function togglePmStatus(Project $project, $userId, $moduleId)
    {
    	$success = $this->repository->togglePmStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));

    }

    public function toggleQsStatus(Project $project, $userId, $moduleId)
    {
    	$success = $this->repository->toggleQsStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function toggleEditorStatus(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->toggleEditorStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function toggleRateEditorStatus(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->toggleRateEditorStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function toggleViewerStatus(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->toggleViewerStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function toggleVerifierStatus(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->toggleVerifierStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function toggleSubmitterStatus(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->toggleSubmitterStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function toggleViewerCheckboxStatus(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->toggleViewerCheckboxStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function toggleVerifierCheckboxStatus(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->toggleVerifierCheckboxStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function toggleSubmitterCheckboxStatus(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->toggleSubmitterCheckboxStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

}