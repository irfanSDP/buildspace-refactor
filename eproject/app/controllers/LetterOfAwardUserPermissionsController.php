<?php

use PCK\Projects\Project;
use PCK\LetterOfAward\LetterOfAwardUserPermission;
use PCK\LetterOfAward\LetterOfAwardUserPermissionRepository;

class LetterOfAwardUserPermissionsController extends BaseController {

    private $letterOfAwardUserPermissionRepository;

    public function __construct(LetterOfAwardUserPermissionRepository $letterOfAwardUserPermissionRepository) {
        $this->letterOfAwardUserPermissionRepository = $letterOfAwardUserPermissionRepository;
    }

    public function index(Project $project) {
        $modules = LetterOfAwardUserPermission::getRoleNameByModuleId();

        return View::make('letter_of_award.userPermissions.index', [
            'project'           => $project,
            'modules'           => $modules,
        ]);
    }

    public function getAssignableUsers(Project $project) {
        return Response::json($this->letterOfAwardUserPermissionRepository->getAssignableUsers($project, Input::all()));
    }

    public function getAssignedUsers(Project $project) {
        return Response::json($this->letterOfAwardUserPermissionRepository->getAssignedUsers($project, Input::all()));
    }

    public function assign(Project $project) {
        $success = $this->letterOfAwardUserPermissionRepository->assign($project, Input::all());

        return Response::json([
            'success' => $success
        ]);
    }

    public function toggleEditorStatus(Project $project, $userId, $moduleId) {
        $success = $this->letterOfAwardUserPermissionRepository->toggleEditorStatus($project, $userId, $moduleId);
        
        return Response::json([
            'success' => $success
        ]);
    }

    public function revoke(Project $project, $userId, $moduleId) {
        $success = $this->letterOfAwardUserPermissionRepository->revoke($project, $userId, $moduleId);

        return Response::json([
            'success' => $success,
        ]);
    }
}

