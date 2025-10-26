<?php namespace PCK\Users;

trait UserSuperAdminTrait {

    public function isSuperAdmin()
    {
        return $this->attributes['is_super_admin'] ?? false;
    }

    public static function getSuperAdminIds()
    {
        return self::where('is_super_admin', '=', true)->lists('id');
    }

}