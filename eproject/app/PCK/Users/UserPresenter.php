<?php namespace PCK\Users;

use Laracasts\Presenter\Presenter;
use PCK\Projects\Project;

class UserPresenter extends Presenter {

    private $user;

    public function __construct(User $user)
    {
        parent::__construct($user);

        $this->user = $user;
    }

    public function byWhoAndRole(Project $project, $timestamp = null)
    {
        $contractGroup = null;

        if( $assignedCompany = $this->user->getAssignedCompany($project, $timestamp) ) $contractGroup = $assignedCompany->getContractGroup($project);

        if( ! $contractGroup )
        {
            return "{$this->user->name} (" . trans('groupTypes.unassigned') . ")";
        }

        $groupName = $project->getRoleName($contractGroup->group);

        return "{$this->user->name} ({$groupName})";
    }

}