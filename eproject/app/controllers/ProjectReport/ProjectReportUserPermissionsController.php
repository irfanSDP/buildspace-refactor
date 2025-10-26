<?php namespace ProjectReport;

use PCK\ProjectReport\ProjectReportUserPermission;
use PCK\ProjectReport\ProjectReportUserPermissionRepository;
use PCK\ProjectReport\ProjectReportRepository;
use PCK\Projects\Project;

class ProjectReportUserPermissionsController extends \Controller
{
    private $repository;
    private $projectReportRepository;

    public function __construct(ProjectReportUserPermissionRepository $repository, ProjectReportRepository $projectReportRepository)
    {
        $this->repository              = $repository;
        $this->projectReportRepository = $projectReportRepository;
    }

    public function index(Project $project)
    {
        return \View::make('project_report.user_permission.index', [
            'project' => $project,
        ]);
    }

    public function projectReportTypesList(Project $project)
    {
        $records = $this->repository->getProjectReportTypesListing($project);

        return \Response::json($records);
    }

    public function getAssignedUsers(Project $project)
    {
        $inputs              = \Input::all();
        $projectReportTypeId = $inputs['reportTypeId'];
        $identifier          = $inputs['identifier'];

        $assignedUsers = $this->repository->getAssignedUsers($project, $projectReportTypeId, $identifier);

        return \Response::json($assignedUsers);
    }

    public function getAssignableUsers(Project $project)
    {
        $inputs              = \Input::all();
        $projectReportTypeId = $inputs['reportTypeId'];
        $identifier          = $inputs['identifier'];

        $assignableUsers = $this->repository->getAssignableUsers($project, $projectReportTypeId, $identifier);

        return \Response::json($assignableUsers);
    }

    public function grant(Project $project)
    {
        $success = false;
        $errors  = null;

        try
        {
            $user                = \Confide::user();
            $inputs              = \Input::all();
            $projectReportTypeId = $inputs['reportTypeId'];
            $identifier          = $inputs['identifier'];
            $userIds             = $inputs['userIds'];
        
            $success = $this->repository->grant($project, $projectReportTypeId, $userIds, $identifier);
        }
        catch(\Exception $e)
        {
            $errors = $e->getMessage();
        }

        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function checkFuturePendingTasks(Project $project)
    {
        $inputs           = \Input::all();
        $userPermissionId = $inputs['userPermissionId'];
        $userPermission   = ProjectReportUserPermission::find($userPermissionId);

        $futurePendingTasks = $this->projectReportRepository->getPendingApprovalProjectReports($userPermission->user, true, $userPermission->project);

        return \Response::json([
            'hasPendingTasks' => count($futurePendingTasks) > 0,
        ]);
    }

    public function revoke(Project $project)
    {
        $success = false;
        $errors  = null;

        try
        {
            $inputs           = \Input::all();
            $userPermissionId = $inputs['userPermissionId'];
            $userPermission   = ProjectReportUserPermission::find($userPermissionId);

            if($userPermission)
            {
                $userPermission->delete();
            }

            $success = true;
        }
        catch(\Exception $e)
        {
            $errors = $e->getMessage();
        }

        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }
}