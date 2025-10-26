<?php namespace PCK\Projects;

use PCK\Users\User;

trait SubProjectTrait {

    public function isImport()
    {
        return $this->isSubProject();
    }

    public function isSubProject()
    {
        return ( ! $this->isMainProject() );
    }

    public function isMainProject()
    {
        return ( is_null($this->parent_project_id) );
    }

    public function subProjects()
    {
        return $this->hasMany('PCK\Projects\Project', 'parent_project_id', 'id')->orderBy('id', 'desc');
    }

    public function parentProject()
    {
        return $this->belongsTo('PCK\Projects\Project', 'parent_project_id', 'id');
    }
    
    public function getAssignedSubProjects(User $user)
    {
        return $this->subProjects->filter(function($project) use ($user)
        {
            if( $user->isSuperAdmin() ) return true;

            return ! is_null($user->getAssignedCompany($project));
        });
    }

}