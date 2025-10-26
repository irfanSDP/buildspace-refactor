<?php namespace PCK\Filters;

use App;
use Illuminate\Routing\Route;
use PCK\ContractGroups\Types\Role;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\Projects\Project;
use PCK\TenderDocumentFolders\TenderDocumentFile;
use PCK\TenderDocumentFolders\TenderDocumentFolder;

class TenderDocumentFilters {

    private function hasAccessToModule(Project $project)
    {
        $user = \Confide::user();

        $hasRoleAccess = $user->hasCompanyProjectRole($project, array(
            Role::GROUP_CONTRACT,
            Role::PROJECT_OWNER,
            $project->getCallingTenderRole(),
            Role::CONTRACTOR,
        ));

        return ( $user->isSuperAdmin() || $hasRoleAccess );
    }

    private function isEditor(Project $project)
    {
        $user = \Confide::user();

        $hasRole  = ( $user->isSuperAdmin() || $user->hasCompanyProjectRole($project, $project->getCallingTenderRole()) );
        $isEditor = $user->isEditor($project);

        return ( $hasRole && $isEditor );
    }

    public function folderAccess(Route $route)
    {
        $project = $route->getParameter('projectId');
        $folder  = TenderDocumentFolder::find($route->getParameter('folderId'));

        if( $folder->project->id != $project->id ) App::abort(404);

        if( ! $this->hasAccessToModule($project) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function folderDownload(Route $route)
    {
        $project = $route->getParameter('projectId');
        $folder  = TenderDocumentFolder::find($route->getParameter('folderId'));

        if( $folder->project->id != $project->id ) App::abort(404);

        if( ! $this->hasAccessToModule($project) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));

        $user = \Confide::user();

        $hasRoleAccess = $user->hasCompanyProjectRole($project, array(
            Role::GROUP_CONTRACT,
            Role::PROJECT_OWNER,
            $project->getCallingTenderRole(),
            Role::CONTRACTOR,
        ));

        if( ! $hasRoleAccess ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function folderModify(Route $route)
    {
        $this->folderAccess($route);

        $project = $route->getParameter('projectId');

        if( ! $this->isEditor($project) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function fileAccess(Route $route)
    {
        $project = $route->getParameter('projectId');
        $file    = TenderDocumentFile::find($route->getParameter('fileId'));

        if( is_null($project) || is_null($file)) App::abort(404);

        if( $file->folder->project->id != $project->id ) App::abort(404);

        if( ! $this->hasAccessToModule($project) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function fileDownload(Route $route)
    {
        $project = $route->getParameter('projectId');
        $file    = TenderDocumentFile::find($route->getParameter('fileId'));

        if( $file->folder->project->id != $project->id ) App::abort(404);

        if( ! $this->hasAccessToModule($project) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));

        $user = \Confide::user();

        $hasRoleAccess = $user->hasCompanyProjectRole($project, array(
            Role::GROUP_CONTRACT,
            Role::PROJECT_OWNER,
            $project->getCallingTenderRole(),
            Role::CONTRACTOR,
        ));

        if( ! $hasRoleAccess ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function fileModify(Route $route)
    {
        $this->fileAccess($route);

        $project = $route->getParameter('projectId');

        if( ! $this->isEditor($project) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

}