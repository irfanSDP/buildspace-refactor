<?php

use PCK\ProjectModulePermission\ProjectModulePermission;
use PCK\ProjectModulePermission\ProjectModulePermissionRepository;
use PCK\Projects\Project;

class IndonesiaCivilContractUserPermissionsController extends \BaseController {

    private $repository;

    public function __construct(ProjectModulePermissionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Project $project)
    {
        $modules = array(
            ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_ARCHITECT_INSTRUCTION => trans('modulePermissions.architectInstructions'),
            ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_EXTENSION_OF_TIME     => trans('modulePermissions.extensionOfTime'),
            ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_LOSS_AND_EXPENSES     => trans('modulePermissions.lossAndExpenses'),
            ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_EARLY_WARNING         => trans('modulePermissions.earlyWarning'),
        );

        return View::make('indonesia_civil_contract.modulePermissions.modulePermissions', array(
            'modules' => $modules,
            'project' => $project,
        ));
    }

    public function getAssignedUsers(Project $project)
    {
        return Response::json($this->repository->getAssignedUsers($project, Input::all()));
    }

    public function getAssignableUsers(Project $project)
    {
        return Response::json($this->repository->getAssignableUsers($project, Input::all()));
    }

    public function assign(Project $project)
    {
        $success = $this->repository->assign($project, Input::get('users') ?? array(), Input::get('module_id'));

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function revoke(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->revoke($project, $userId, $moduleId);

        return Response::json(array(
            'success' => $success,
        ));
    }

}