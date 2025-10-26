<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Inspections\Inspection;
use PCK\Inspections\InspectionRole;
use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionGroupInspectionListCategory;
use PCK\Inspections\InspectionGroup;
use PCK\Inspections\InspectionGroupUser;
use PCK\Inspections\InspectionSubmitter;
use PCK\Inspections\InspectionVerifierTemplate;
use PCK\Inspections\RequestForInspection;
use PCK\Inspections\InspectionResult;
use PCK\Verifier\Verifier;

class InspectionFilters {

    public static function hasModuleAccess(Project $project, User $user)
    {
        $groupIds = InspectionGroup::where('project_id', '=', $project->id)->lists('id');

        $hasRole = self::hasModuleRole($project, $user, $groupIds);

        $isSubmitter = self::isModuleSubmitter($project, $user, $groupIds);

        $isSetToBeVerifier = self::isSetToBeVerifier($project, $user, $groupIds);

        $isAVerifier = self::isAVerifier($project, $user);

        return $hasRole || $isSubmitter || $isSetToBeVerifier || $isAVerifier;
    }

    public static function hasModuleRole(Project $project, User $user, array $groupIds = null)
    {
        if( is_null($groupIds) ) $groupIds = InspectionGroup::where('project_id', '=', $project->id)->lists('id');

        return ! InspectionGroupUser::whereIn('inspection_group_id', $groupIds)
            ->where('user_id', '=', $user->id)
            ->get()
            ->isEmpty();
    }

    public static function hasRole(Inspection $inspection, User $user)
    {
        $rootList = InspectionListCategory::where('inspection_list_id', '=', $inspection->requestForInspection->inspectionListCategory->inspection_list_id)
            ->where('lft', '<=', $inspection->requestForInspection->inspectionListCategory->lft)
            ->where('rgt', '>=', $inspection->requestForInspection->inspectionListCategory->rgt)
            ->where('depth', '=', 0)
            ->first();

        $rootListGroupPivot = InspectionGroupInspectionListCategory::where('inspection_list_category_id', '=', $rootList->id ?? 0)
            ->first();

        return ! InspectionGroupUser::where('inspection_group_id', '=', $rootListGroupPivot->inspection_group_id ?? 0)
            ->where('user_id', '=', $user->id)
            ->get()
            ->isEmpty();
    }

    public static function isModuleSubmitter(Project $project, User $user, array $groupIds = null)
    {
        if( is_null($groupIds) ) $groupIds = InspectionGroup::where('project_id', '=', $project->id)->lists('id');

        return ! InspectionSubmitter::whereIn('inspection_group_id', $groupIds)
            ->where('user_id', '=', $user->id)
            ->get()
            ->isEmpty();
    }

    public static function isSetToBeVerifier(Project $project, User $user, array $groupIds = null)
    {
        if( is_null($groupIds) ) $groupIds = InspectionGroup::where('project_id', '=', $project->id)->lists('id');

        return ! InspectionVerifierTemplate::whereIn('inspection_group_id', $groupIds)
            ->where('user_id', '=', $user->id)
            ->get()
            ->isEmpty();
    }

    public static function isAVerifier(Project $project, User $user)
    {
        $inspectionIds = Inspection::whereHas('requestForInspection', function($query) use ($project){
            $query->where('project_id', '=', $project->id);
        })
        ->lists('id');

        return ! Verifier::whereIn('object_id', $inspectionIds)
            ->where('object_type', '=', get_class(new Inspection))
            ->where('verifier_id', '=', $user->id)
            ->whereNull('approved')
            ->get()
            ->isEmpty();
    }

    public static function canRequestInspection(Project $project, User $user, array $groupIds = null)
    {
        if( is_null($groupIds) ) $groupIds = InspectionGroup::where('project_id', '=', $project->id)->lists('id');

        return ! InspectionGroupUser::whereIn('inspection_group_id', $groupIds)
            ->where('user_id', '=', $user->id)
            ->whereHas('role', function($query){
                $query->where('can_request_inspection', '=', true);
            })
            ->get()
            ->isEmpty();
    }

    public static function readyForSubmission(Inspection $inspection)
    {
        if( $inspection->status != Inspection::STATUS_IN_PROGRESS ) return false;

        $inspectionRoleIds = InspectionRole::where('project_id', '=', $inspection->requestForInspection->project_id)->lists('id');

        foreach($inspectionRoleIds as $roleId)
        {
            $inspectionResults = InspectionResult::where('inspection_id', '=', $inspection->id)
                ->where('inspection_role_id', '=', $roleId)
                ->where('status', '=', InspectionResult::STATUS_SUBMITTED)
                ->get();

            if( $inspectionResults->isEmpty() ) return false;
        }

        return true;
    }

    public function hasModuleAccessRoute(Route $route)
    {
        $project = $route->getParameter('projectId');

        $user = \Confide::user();

        if( ! self::hasModuleAccess($project, $user) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function canRequestInspectionRoute(Route $route)
    {
        $project = $route->getParameter('projectId');

        $inspectionId = $route->getParameter('inspectionId');

        $user = \Confide::user();

        if( $inspectionId )
        {
            $inspection = Inspection::find($inspectionId);

            $rootList = InspectionListCategory::where('inspection_list_id', '=', $inspection->requestForInspection->inspectionListCategory->inspection_list_id)
                ->where('lft', '<=', $inspection->requestForInspection->inspectionListCategory->lft)
                ->where('rgt', '>=', $inspection->requestForInspection->inspectionListCategory->rgt)
                ->where('depth', '=', 0)
                ->first();

            $rootListGroupPivot = InspectionGroupInspectionListCategory::where('inspection_list_category_id', '=', $rootList->id ?? 0)
                ->first();

            $groupIds = InspectionGroupUser::where('inspection_group_id', '=', $rootListGroupPivot->inspection_group_id ?? 0)
                ->where('user_id', '=', $user->id)
                ->whereHas('role', function($query){
                    $query->where('can_request_inspection', '=', true);
                })
                ->lists('inspection_group_id');
        }

        if( ! self::canRequestInspection($project, $user, $groupIds ?? null) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function requestForInspectionIsDraft(Route $route)
    {
        $requestForInspection = RequestForInspection::find($route->getParameter('requestForInspectionId'));

        if( ! $requestForInspection->isDraft() || ! in_array(\Confide::user()->id, $requestForInspection->latestInspection->getRequesters()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function isDraft(Route $route)
    {
        $inspection = Inspection::find($route->getParameter('inspectionId'));

        if( $inspection->status != Inspection::STATUS_DRAFT ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function isNotDraft(Route $route)
    {
        $inspection = Inspection::find($route->getParameter('inspectionId'));

        if( $inspection->status == Inspection::STATUS_DRAFT ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function hasInspectorRole(Route $route)
    {
        $inspection = Inspection::find($route->getParameter('inspectionId'));

        if( ! self::hasRole($inspection, \Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function readyForSubmissionRoute(Route $route)
    {
        $inspection = Inspection::find($route->getParameter('inspectionId'));

        if( ! self::readyForSubmission($inspection) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function isSubmitterRoute(Route $route)
    {
        $inspection = Inspection::find($route->getParameter('inspectionId'));

        if( ! in_array(\Confide::user()->id, $inspection->getSubmitters()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
}