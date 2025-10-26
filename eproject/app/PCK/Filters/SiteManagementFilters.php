<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\SiteManagement\SiteManagementUserPermission;
use PCK\SiteManagement\SiteManagementDefect;
use PCK\SiteManagement\SiteManagementMCAR;
use PCK\ContractGroups\Types\Role;

class SiteManagementFilters
{
	public function hasSiteManagementUserManagementPermission(Route $route)
	{
		$project = $route->getParameter('projectId');
		$user    = \Confide::user();

        $buCompany = $project ? $project->getCompanyByGroup(Role::PROJECT_OWNER) : null;

        $hasPermission = $project->isMainProject() && $buCompany && in_array($user->id, $buCompany->getActiveUsers()->lists('id')) && $user->isGroupAdmin();

        if(!$hasPermission)
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
	}

	public function hasDefectPermission(Route $route)
	{
		$project = $route->getParameter('projectId');
		$user = \Confide::user();

		if(SiteManagementUserPermission::isAssigned(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project)||
			SiteManagementUserPermission::isProjectAssignedContractor($user,$project)) return;

        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
	}

	public function hasViewDefectFormPermission(Route $route)
	{
		$project = $route->getParameter('projectId');
		$form_id = $route->getParameter('id');
		$user = \Confide::user();

		if((SiteManagementUserPermission::isSiteUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project) &&
	        SiteManagementDefect::checkSubmittedUserCanRespond($user,$form_id)) ||
	        SiteManagementDefect::checkSubmittedUserCanRespond($user,$form_id) ||
	        SiteManagementDefect::checkAssignedPicCanRespond($user,$form_id) ||
	        SiteManagementUserPermission::isQsUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project) ||
	        SiteManagementUserPermission::isPmUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project) ||
	        SiteManagementDefect::isDefectAssignedContractor($user,$form_id)) return;

        \Flash::error(trans('filter.notAllowedToViewForm'));

        return \Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function hasViewMCARFormPermission(Route $route)
	{
		$project = $route->getParameter('projectId');
		$form_id = $route->getParameter('id');
		$user = \Confide::user();

		if(SiteManagementDefect::checkAssignedPicCanRespond($user,$form_id) || 
		   SiteManagementDefect::isDefectAssignedContractor($user,$form_id) ||
		   SiteManagementUserPermission::isPmUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project)) return;

        \Flash::error(trans('filter.notAllowedToViewForm'));

        return \Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function hasCreateMCARFormPermission(Route $route)
	{
		$project = $route->getParameter('projectId');
		$form_id = $route->getParameter('id');
		$user = \Confide::user();

		if((SiteManagementDefect::checkAssignedPicCanRespond($user,$form_id) || 
		   SiteManagementUserPermission::isPmUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project))
		   &&(!SiteManagementMCAR::checkRecordExists($form_id))) return;

        \Flash::error(trans('filter.notAllowedToSubmitForm'));

        return \Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function hasDefectProjectManagerPermission(Route $route)
	{
		$project = $route->getParameter('projectId');
		$form_id = $route->getParameter('id');
		$user = \Confide::user();

		if(SiteManagementUserPermission::isPmUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project)) return;

        \Flash::error(trans('filter.notAllowToAssignPIC'));

        return \Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function hasDailyLabourReportsPermission(Route $route)
	{
		$project = $route->getParameter('projectId');
		$user = \Confide::user();

		if(SiteManagementUserPermission::isAssigned(SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS, $user, $project)||
			SiteManagementUserPermission::isProjectAssignedContractor($user,$project)) return;

        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
	}

	public function hasSiteDiaryPermission(Route $route)
	{
		$project = $route->getParameter('projectId');
		$user = \Confide::user();

		if(SiteManagementUserPermission::isAssigned(SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY, $user, $project)||
			SiteManagementUserPermission::isProjectAssignedContractor($user,$project)) return;

        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
	}

	public function hasInstructionToContractorPermission(Route $route)
	{
		$project = $route->getParameter('projectId');
		$user = \Confide::user();

		if(SiteManagementUserPermission::isAssigned(SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR, $user, $project)||
			SiteManagementUserPermission::isProjectAssignedContractor($user,$project)) return;

        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
	}
}