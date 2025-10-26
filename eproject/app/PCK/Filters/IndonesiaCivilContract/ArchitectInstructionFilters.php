<?php namespace PCK\Filters\IndonesiaCivilContract;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstruction;
use PCK\ProjectModulePermission\ProjectModulePermission;

class ArchitectInstructionFilters {

    public function isEditor(Route $route)
    {
        $project = $route->getParameter('projectId');

        $user = \Confide::user();

        if( ! ProjectModulePermission::isAssigned($project, $user, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_ARCHITECT_INSTRUCTION) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function isVisible(Route $route)
    {
        $ai = ArchitectInstruction::find($route->getParameter('aiId'));

        $user = \Confide::user();

        if( ! $ai->isVisible($user) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function canRespond(Route $route)
    {
        $ai = ArchitectInstruction::find($route->getParameter('aiId'));

        $user = \Confide::user();

        if( ! $ai->canRespond($user) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
}