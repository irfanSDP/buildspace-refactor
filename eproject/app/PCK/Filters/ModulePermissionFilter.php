<?php namespace PCK\Filters;

use PCK\ModulePermission\ModulePermission;
use PCK\Users\User;

class ModulePermissionFilter {

    private static function extractModuleIds($moduleIds)
    {
        return explode('&', $moduleIds);
    }

    public static function isPermittedInAny(User $user, $moduleIds)
    {
        foreach(static::extractModuleIds($moduleIds) as $moduleId)
        {
            if( ModulePermission::hasPermission($user, $moduleId) ) return true;
        }

        return false;
    }

}