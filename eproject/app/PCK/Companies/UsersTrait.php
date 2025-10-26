<?php namespace PCK\Companies;

use Confide;
use Illuminate\Database\Eloquent\Collection;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Tenders\TenderStages;
use PCK\ModulePermission\ModulePermission;

trait UsersTrait {

    public function companyAdmin()
    {
        return $this->hasOne('PCK\Users\User')
            ->where('is_admin', '=', true)
            ->orderBy('id', 'asc');
    }

    public function isCompanyAdmin(User $user)
    {
        $obj = $this->hasOne('PCK\Users\User')
            ->where('id', '=', $user->id)
            ->where('is_admin', '=', true)
            ->get();

        return (! $obj->isEmpty());
    }

    public function companyAdmins()
    {
        return $this->hasMany('PCK\Users\User')
            ->where('is_admin', '=', true)
            ->orderBy('id', 'asc');
    }

    public function users()
    {
        return $this->hasMany('PCK\Users\User')->orderBy('id', 'desc');
    }

    public function importedUsers()
    {
        return $this->belongsToMany('PCK\Users\User', 'company_imported_users', 'company_id', 'user_id');
    }

    /**
     * Returns a collection of all users associated with the company.
     *
     * @return Collection
     */
    public function getAllUsers($includeImportedUsers = true)
    {
        if($includeImportedUsers)
        {
            return $this->users->merge($this->importedUsers);
        }

        return $this->users;
    }

    /**
     * Returns a collection of all active users associated with the company.
     *
     * @return Collection
     */
    public function getActiveUsers($includeImportedUsers = true)
    {
        return $this->getAllUsers($includeImportedUsers)->filter(function($user)
        {
            return $user->isActive();
        });
    }

    /**
     * Returns the verifiers for a project.
     *
     * @param Project $project
     *
     * @return mixed
     */
    public function getVerifierList(Project $project, $includeTopManagementVerifiers = false)
    {
        $currentUser = Confide::user();

        $verifiers = $this->getActiveUsers()->reject(function($verifier) use ($project, $currentUser) {
            return ( ( $verifier->id == $currentUser->id ) || ( ! $verifier->assignedToProject($project) ) );
        });

        if($includeTopManagementVerifiers)
        {
            $verifiers = $verifiers->merge($project->getTopManagementVerifiersWithProjectAccess());
        }

        return $verifiers;
    }

}