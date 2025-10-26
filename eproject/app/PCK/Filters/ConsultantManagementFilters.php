<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\Users\User;
use PCK\ModulePermission\ModulePermission;

class ConsultantManagementFilters
{
    private $user;

    public function __construct()
    {
        $this->user = \Confide::user();
    }

    public function validateConsultantManagementRoles()
    {
        if(!$this->user->hasConsultantManagementCompanyRoles())
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
        elseif($this->user->hasConsultantManagementCompanyRoles() && $this->user->isConsultantManagementParticipantConsultant() && !$this->user->isConsultantManagementConsultantUser())
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function validateConsultantPaymentsUserPermission()
    {
        if(!ModulePermission::hasPermission($this->user, ModulePermission::MODULE_ID_CONSULTANT_PAYMENT))
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }
}
