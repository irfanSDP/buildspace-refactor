<?php namespace PCK\Users;

use Carbon\Carbon;
use PCK\Buildspace\CompanyGroup as BsCompanyGroup;
use PCK\Buildspace\Group as BsGroup;
use PCK\Buildspace\User as BsUser;
use PCK\Buildspace\UserProfile as BsUserProfile;
use PCK\Buildspace\ProjectUserPermission as BsProjectUserPermission;
use PCK\ContractGroups\Types\Role;
use PCK\Projects\Project;

trait BsUserTrait {

    public function getBsUserProfile()
    {
        return BsUserProfile::where('bs_sf_guard_user_profile.eproject_user_id', $this->id)
            ->where('bs_sf_guard_user_profile.deleted_at', null)
            ->first();
    }

    public function getBsUser()
    {
        if( $bsUserProfile = $this->getBsUserProfile() ) return $bsUserProfile->User;

        return $this->createBsUser()->User;
    }

    public function createBsUser()
    {
        $bsUserProfile = $this->updateBsUser();

        if( ( ! $this->isSuperAdmin() ) && $bsGroup = $this->getBsCompanyGroup() )
        {
            $bsGroup->addBsUser($bsUserProfile->User);
        }
        elseif( $this->isSuperAdmin() )
        {
            $bsGroup = BsGroup::createNewGroup($this, $this->username);
            $bsGroup->addBsUser($bsUserProfile->User);
        }

        return $bsUserProfile;
    }

    private function updateBsUser()
    {
        $bsUserProfile = $this->getBsUserProfile();

        if( ! $bsUserProfile )
        {
            $bsUser = new BsUser();

            $bsUser->first_name    = $this->name;
            $bsUser->username      = $this->username;
            $bsUser->email_address = $this->email;

            $bsUser->save();

            $bsUserProfile = new BsUserProfile();

            $bsUserProfile->user_id          = $bsUser->id;
            $bsUserProfile->eproject_user_id = $this->id;
            $bsUserProfile->name             = $this->name;
            $bsUserProfile->contact_num      = $this->contact_number;

            $bsUserProfile->save();
        }
        else
        {
            $bsUserProfile->name        = $this->name;
            $bsUserProfile->contact_num = $this->contact_number;

            $bsUserProfile->save();

            $bsUser             = $bsUserProfile->User;
            $bsUser->first_name = $this->name;

            $bsUser->save();
        }

        return $bsUserProfile;
    }

    /**
     * Returns a BuildSpace Group.
     * Creates a BuildSpace Group if it does not exist.
     *
     * @return BsGroup
     */
    public function getBsCompanyGroup()
    {
        if( $this->isSuperAdmin() )
        {
            return null;
        }

        $bsCompany      = $this->company->getBsCompany();
        $bsCompanyGroup = BsCompanyGroup::where('company_id', '=', $bsCompany->id)->first();

        if( ! $bsCompanyGroup )
        {
            $bsGroup = BsGroup::createNewGroup($this, $bsCompany->name);

            // Create relation
            $bsCompanyGroup             = new BsCompanyGroup();
            $bsCompanyGroup->company_id = $bsCompany->id;
            $bsCompanyGroup->group_id   = $bsGroup->id;
            $bsCompanyGroup->timestamps = false;
            $bsCompanyGroup->save();
        }

        return BsGroup::find($bsCompanyGroup->group_id);
    }

    private function deleteBsUser()
    {
        $bsUserIds = BsUserProfile::withTrashed()
            ->where('eproject_user_id', $this->id)
            ->get()
            ->lists('user_id');

        BsUserProfile::withTrashed()
            ->where('eproject_user_id', $this->id)
            ->forceDelete();

        BsUser::withTrashed()
            ->whereIn('id', $bsUserIds)
            ->forceDelete();
    }

    public function canAccessBqEditor(Project $project)
    {
        if( ! $project->latestTender ) return false;

        $currentTimestamp = Carbon::now();

        if( $currentTimestamp->lt(Carbon::parse($project->latestTender->tender_starting_date)) ) return false;

        if( $currentTimestamp->gte(Carbon::parse($project->latestTender->tender_closing_date)) ) return false;

        if( ! $this->hasCompanyProjectRole($project, Role::CONTRACTOR) ) return false;

        return true;
    }

    public function hasBuildspaceProjectUserPermission(Project $project, $projectStatus)
    {
        $bsUser = $this->getBsUser();

        if( $bsUser->is_super_admin ) return true;

        $bsProject = $project->getBsProjectMainInformation()->projectStructure;

        return in_array($bsUser->id, BsProjectUserPermission::getAssignedUserIdsByProjectAndStatus($bsProject, $projectStatus));
    }

    public function hasBuildspaceMenuItemAccess($menuItemName)
    {
        $bsUser = $this->getBsUser();

        if ( $bsUser->is_super_admin ) return true;

        $results = \DB::connection('buildspace')->table('bs_sf_guard_user_group AS ug')
            ->select("*")
            ->join('bs_sf_guard_group_menu AS gm', 'gm.group_id', '=', 'ug.group_id')
            ->join('bs_menus AS m', 'm.id', '=', 'gm.menu_id')
            ->where('ug.user_id', '=', $bsUser->id)
            ->where('m.sysname', '=', $menuItemName)
            ->count();

        return $results > 0;
    }
}