<?php

use Illuminate\Support\Facades\DB;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;
use PCK\Projects\Project;
use PCK\EBiddings\EBidding;

class ProjectGroupsController extends \BaseController {

    private $cgProjectUserRepo;

    public function __construct(ContractGroupProjectUserRepository $repo)
    {
        $this->cgProjectUserRepo = $repo;
    }

    public function edit($project)
    {
        $user          = Confide::user();
        $company       = $user->getAssignedCompany($project);

        $contractGroup = null;
        $assignedUsers = [];
        $users         = [];
        $importedUsers = [];

        if($company)
        {
            $contractGroup = $company->getContractGroup($project);

            $assignedUsers = $this->cgProjectUserRepo->getAssignedUsersByProjectAndContractGroup($project, $contractGroup);

            $users = $company->users->reject(function($user)
            {
                return ( ! $user->confirmed );
            })
            ->reject(function($user)
            {
                return $user->account_blocked_status;
            });

            $importedUsers = $company->importedUsers->reject(function($user)
            {
                return ( ! $user->confirmed );
            })
            ->reject(function($user)
            {
                return $user->account_blocked_status;
            });

            $blockedUsers = $company->users->reject(function($user)
            {
                return ( ! $user->confirmed );
            })
            ->filter(function($user)
            {
                return $user->account_blocked_status;
            });

            $blockedImportedUsers = $company->importedUsers->reject(function($user)
            {
                return ( ! $user->confirmed );
            })
            ->filter(function($user)
            {
                return $user->account_blocked_status;
            });
        }

        $eBidding = EBidding::where('project_id',$project->id)->first();
        if ($eBidding) {
            $eBiddingIsApproved = $eBidding->isApproved();
        }
        // if the users is currently assigned to other group, don't bother to show it
        return View::make('project_groups.edit', array(
            'eBidding'             => $eBidding,
            'project'              => $project,
            'company'              => $company,
            'contractGroup'        => $contractGroup,
            'users'                => $users,
            'assignedUsers'        => $assignedUsers,
            'importedUsers'        => $importedUsers,
            'blockedUsers'         => $blockedUsers,
            'blockedImportedUsers' => $blockedImportedUsers,
        ));
    }

    public function update(Project $project)
    {
        $user          = Confide::user();
        $contractGroup = $user->getAssignedCompany($project)->getContractGroup($project);
        $inputs        = Input::all();

        // will batch insert new records
        $this->cgProjectUserRepo->insertByBatchRoles($project, $contractGroup, $inputs);

        // sync permission to buildspace
        $this->cgProjectUserRepo->syncBuildspaceProjectUserPermissions($project, $contractGroup);

        $groupName = $project->getRoleName($contractGroup->group);

        Flash::success("Updated Group ({$groupName}) Permission");

        // will display flash message to remind user of successfully added user to current selected group
        return Redirect::back();
    }
}