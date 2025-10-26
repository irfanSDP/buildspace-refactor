<?php namespace PCK\Projects;

use PCK\ProjectRole\ProjectRole;

trait ProjectRoleTrait {

    public function getRoleName(int $group)
    {
        return ProjectRole::getRoleName($this, $group);
    }

    public function projectRoles()
    {
        return $this->hasMany('PCK\ProjectRole\ProjectRole');
    }

}