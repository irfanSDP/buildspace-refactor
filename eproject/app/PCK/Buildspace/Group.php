<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use PCK\Buildspace\Company as BsCompany;
use PCK\Buildspace\User as BsUser;
use PCK\Buildspace\UserGroup as BsUserGroup;
use PCK\Buildspace\Menu as BsMenu;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\Users\User;

class Group extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_sf_guard_group';

    public function users()
    {
        return $this->belongsToMany('PCK\Buildspace\User', with(new BsUserGroup)->getTable(), 'group_id', 'user_id');
    }

    public static function generateUniqueGroupName($groupName)
    {
        if( self::groupNameIsUnique($groupName) )
        {
            return $groupName;
        }

        while( ! self::groupNameIsUnique($groupName = ($groupName . ' (auto-generated: ' . str_random(4) . ')')) )
        {
            // Keep looping until a unique name is generated.
        }

        return $groupName;
    }

    /**
     * Returns true if the group name is unique.
     *
     * @param $groupName
     *
     * @return bool
     */
    private static function groupNameIsUnique($groupName)
    {
        return ( self::where('name', '=', $groupName)->count() == 0 );
    }

    /**
     * Adds the user to the group.
     *
     * @param BsUser $bsUser
     *
     * @return BsUserGroup
     */
    public function addBsUser(BsUser $bsUser)
    {
        $bsUserGroup = BsUserGroup::where('user_id', '=', $bsUser->id)
            ->where('group_id', '=', $this->id)
            ->first();

        if( ! $bsUserGroup )
        {
            \DB::connection($this->connection)
                ->table(with(new BsUserGroup)->getTable())
                ->insert(array(
                    'user_id'    => $bsUser->id,
                    'group_id'   => $this->id,
                    'created_at' => 'NOW()',
                    'updated_at' => 'NOW()',
                ));

            $bsUserGroup = BsUserGroup::where('user_id', '=', $bsUser->id)
                ->where('group_id', '=', $this->id)
                ->first();
        }

        return $bsUserGroup;
    }

    public function removeBsUser(BsUser $bsUser)
    {
        $bsUserGroup = BsUserGroup::where('user_id', '=', $bsUser->id)
            ->where('group_id', '=', $this->id)
            ->first();

        $success = false;

        if( $bsUserGroup )
        {
            $success = BsUserGroup::where('user_id', '=', $bsUser->id)
                ->where('group_id', '=', $this->id)
                ->delete();
        }

        return $success;
    }

    protected static function getUserDefaultMenu(User $user)
    {
        if( $user->isSuperAdmin() )
        {
            $menuItems = BsMenu::whereIn('title', array(
                BsMenu::TITLE_ADMINISTRATION,
                BsMenu::TITLE_SYSTEM_ADMINISTRATION,
            ))->get();
        }
        elseif( $user->company->contractGroupCategory->includesContractGroups(array(ContractGroup::getIdByGroup(Role::PROJECT_OWNER), ContractGroup::getIdByGroup(Role::GROUP_CONTRACT))) )
        {
            $menuItems = BsMenu::all()->reject(function($menuItem){
                return $menuItem->title == BsMenu::TITLE_SYSTEM_ADMINISTRATION;
            });
        }
        else
        {
            $menuItems = BsMenu::whereIn('title', array(
                BsMenu::TITLE_PROJECT_BUILDER,
                BsMenu::TITLE_TENDERING,
                BsMenu::TITLE_POST_CONTRACT,
                BsMenu::TITLE_REPORTS,
                BsMenu::TITLE_PROJECT_BUILDER_REPORT,
                BsMenu::TITLE_TENDERING_REPORT,
                BsMenu::TITLE_POST_CONTRACT_REPORT,
            ))->get();
        }

        return $menuItems;
    }

    public function setMenuPermissions(User $user)
    {
        $rows = array();

        foreach(self::getUserDefaultMenu($user) as $menuItem)
        {
            $rows[] = array(
                'group_id'   => $this->id,
                'menu_id'    => $menuItem->id,
                'created_at' => 'NOW()',
                'updated_at' => 'NOW()',
            );
        }

        // return \DB::connection($this->connection)
        //     ->table('bs_sf_guard_group_menu')
        //     ->insert($rows);
    }

    public static function createNewGroup(User $user, $groupName)
    {
        // Create group
        $bsGroup                 = new self();
        $bsGroup->is_super_admin = false;
        $bsGroup->name           = self::generateUniqueGroupName($groupName);
        $bsGroup->save();

        // Grant newly created group all menu permissions.
        $bsGroup->setMenuPermissions($user);

        return $bsGroup;
    }

}