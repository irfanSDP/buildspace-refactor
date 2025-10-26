<?php

use PCK\ContractManagementModule\UserPermission\ContractManagementUserPermissionRepository;
use PCK\ContractManagementModule\ProjectContractManagementModule;
use PCK\Projects\Project;
use PCK\Verifier\Verifier;
use PCK\Users\User;

class ContractManagementModulePermissionsController extends \BaseController {

    private $repository;

    public function __construct(ContractManagementUserPermissionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Project $project)
    {
        $modules = ProjectContractManagementModule::getModuleNames();

        $assignVerifierRoutesByModule = [];

        foreach($modules as $moduleId => $moduleName)
        {
            $assignVerifierRoutesByModule[$moduleId] = route('contractManagement.permissions.verifiers.index', [$project->id, $moduleId]);
        }

        return View::make('contractManagement.userPermissions.index', array(
            'modules'                      => $modules,
            'project'                      => $project,
            'assignVerifierRoutesByModule' => json_encode($assignVerifierRoutesByModule),
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
        $success = $this->repository->assign(Input::get('users') ?? array(), Input::get('module_id'), $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function revoke(Project $project, $userId, $moduleId)
    {
        $title   = null;
        $message = null;
        $success = false;

        try 
        {
            $user = User::find($userId);
            $record = ProjectContractManagementModule::getRecord($project->id, $moduleId);
            $isAVerifier = Verifier::isAVerifier($user, $record);

            if($isAVerifier)
            {
                $success = false;
            }
            else
            {
                $success = $this->repository->revoke($userId, $moduleId, $project);
            }

            if (!$success)
            {
                $title = trans('contractManagement.userCantBeRemoved') . '.';
                $message = trans('contractManagement.userAssignedAsAVerifier') . '.';
            }
        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return Response::json(array(
            'success' => $success,
            'title'   => $title,
            'message' => $message,
        ));
    }

    public function toggleVerifierStatus(Project $project, $userId, $moduleId)
    {
        $success = $this->repository->toggleVerifierStatus($userId, $moduleId, $project);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function verifiersIndex(Project $project, $moduleId)
    {
        $verifiers = $this->repository->getVerifierList($project, $moduleId);

        $projectModule   = ProjectContractManagementModule::getRecord($project->id, $moduleId);
        $verifierRecords = Verifier::getAssignedVerifierRecords($projectModule);

        return View::make('contractManagement.userPermissions.assignVerifiers', array(
            'moduleName'      => ProjectContractManagementModule::getModuleNames($moduleId),
            'moduleId'        => $moduleId,
            'project'         => $project,
            'verifiers'       => $verifiers,
            'verifierRecords' => $verifierRecords,
        ));
    }

    public function verifiersAssign(Project $project, $moduleId)
    {
        $verifiers = Input::get('verifiers') ?? array();

        $verifiers = array_unique($verifiers);

        $moduleName = ProjectContractManagementModule::getModuleNames($moduleId);

        if( empty( $verifiers ) )
        {
            Flash::error("[$moduleName] " . trans('contractManagement.noVerifiersAssigned'));

            return Redirect::back();
        }

        Verifier::setVerifiers(Input::get('verifiers'), ProjectContractManagementModule::getRecord($project->id, $moduleId));

        Flash::success("[$moduleName] " . trans('contractManagement.verifiersAssigned'));

        return Redirect::back();
    }

    public function verifiersReset(Project $project, $moduleId)
    {
        Verifier::deleteLog(ProjectContractManagementModule::getRecord($project->id, $moduleId));

        $moduleName = ProjectContractManagementModule::getModuleNames($moduleId);

        Flash::success("[$moduleName] " . trans('contractManagement.verifiersReset'));

        return Redirect::back();
    }

}