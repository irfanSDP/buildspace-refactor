<?php namespace PCK\Users;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\MessageBag;
use PCK\Helpers\DataTables;
use PCK\Projects\Project;
use PCK\Companies\Company;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;
use PCK\DocumentManagementFolders\DocumentManagementFolder;
use PCK\Buildspace\User as BsUser;
use PCK\Buildspace\UserProfile as BsUserProfile;
use PCK\Buildspace\UserGroup as BsUserGroup;
use PCK\Buildspace\ProjectUserPermission as BsProjectUserPermission;
use PCK\MyCompanyProfiles\MyCompanyProfile;
use PCK\SystemModules\SystemModuleConfiguration;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserRepository
 *
 * This service abstracts some interactions that occurs between Confide and
 * the Database.
 */
class UserRepository {

    private $user;

    private $contractGroupProjectUser;

    public function __construct(User $user, ContractGroupProjectUser $contractGroupProjectUser)
    {
        $this->user                     = $user;
        $this->contractGroupProjectUser = $contractGroupProjectUser;
    }

    public function find($id)
    {
        return User::find($id);
    }

    public function findByCompanyAndId(Company $company, $userId)
    {
        return $this->user->where('id', '=', $userId)
            ->where('company_id', '=', $company->id)
            ->firstOrFail();
    }

    public function getSelectedProjectUsersGroupByCompany(Project $project)
    {
        $data = array();

        $cgpuTableName = $this->contractGroupProjectUser->getTable();

        $query = $this->user->join("{$cgpuTableName} as cgpu", 'cgpu.user_id', '=', 'users.id')
            ->where('cgpu.project_id', '=', $project->id)
            ->whereRaw('users.confirmed IS TRUE')
            ->whereRaw('users.account_blocked_status IS FALSE')
            ->orderBy('users.company_id', 'ASC')
            ->orderBy('users.id', 'ASC');

        foreach($query->get() as $user)
        {
            $company = $user->getAssignedCompany($project);
            if( $company )
            {
                $data[ $company->id ][] = $user;
            }
        }

        return $data;
    }

    /**
     * Signup a new account with the given parameters
     *
     * @param  array $inputs Array containing 'username', 'email' and 'password'.
     * @param  bool $isVendorRegistration True to send the default confirmation email.
     *
     * @return  User User object that may or may not be saved successfully. Check the id to make sure.
     */
    public function signUp(array $inputs, $sendAccountConfirmationEmail = true)
    {
        $inputs['email'] = strtolower(trim($inputs['email']));

        if( BsUser::exists($inputs['email']) )
        {
            $user         = new User;
            $user->errors = new MessageBag();
            $user->errors->add('email', 'The credentials provided have already been used. Try with different credentials');

            return $user;
        }

        $user     = new User;
        $password = str_random(8);

        $user->{$inputs['relation_column']} = trim(array_get($inputs, $inputs['relation_column']));
        $user->name                         = trim(array_get($inputs, 'name'));
        $user->contact_number               = trim(array_get($inputs, 'contact_number'));
        $user->username                     = trim(array_get($inputs, 'email'));
        $user->email                        = trim(array_get($inputs, 'email'));
        $user->password                     = $password;
        $user->designation                  = trim(array_get($inputs, 'designation'));

        if( isset( $inputs['is_admin'] ) )
        {
            $user->is_admin = $inputs['is_admin'];
        }

        if( isset( $inputs['account_blocked_status'] ) )
        {
            $user->account_blocked_status = $inputs['account_blocked_status'];
        }

        if( isset( $inputs['allow_access_to_buildspace'] ) )
        {
            $user->allow_access_to_buildspace = $inputs['allow_access_to_buildspace'];
        }
        else
        {
            $company = Company::find($user->{$inputs['relation_column']});

            if( $company->giveDefaultAccessToBuildSpace() ) $user->allow_access_to_buildspace = true;
        }

        // The password confirmation will be removed from model
        // before saving. This field will be used in Ardent's
        // auto validation.
        $user->password_confirmation = $password;

        // Generate a random confirmation code
        $user->confirmation_code = md5(uniqid(mt_rand(), true));

        // Save if valid. Password field will be hashed before save

        $user->save();

        if($sendAccountConfirmationEmail && $user->exists)
        {
            \Event::fire('user.newlyRegistered', $user);
        }

        return $user;
    }

    public function update(User $user, $inputs)
    {
        $currentUser = \Confide::user();

        $user->name           = trim(array_get($inputs, 'name'));
        $user->contact_number = trim(array_get($inputs, 'contact_number'));

        $accessToBuildSpace = array_get($inputs, 'allow_access_to_buildspace');
        $accessToBuildSpace = is_null($accessToBuildSpace) ? false : $accessToBuildSpace;

        $accessToGP = array_get($inputs, 'allow_access_to_gp');
        $accessToGP = is_null($accessToGP) ? false : $accessToGP;

        $adminAccessToGP = array_get($inputs, 'is_gp_admin');
        $adminAccessToGP = is_null($adminAccessToGP) ? false : $adminAccessToGP;

        if( $currentUser->isSuperAdmin() || $currentUser->isGroupAdmin() )
        {
            $isGroupAdministrator = array_get($inputs, 'is_admin');
            $isGroupAdministrator = is_null($isGroupAdministrator) ? false : $isGroupAdministrator;

            $user->is_admin = $isGroupAdministrator;
        }

        if( $currentUser->isSuperAdmin() )
        {
            $blockedAccount = array_get($inputs, 'account_blocked_status');
            $blockedAccount = is_null($blockedAccount) ? false : $blockedAccount;

            $user->account_blocked_status     = $blockedAccount;
            $user->allow_access_to_buildspace = $accessToBuildSpace;
            $user->allow_access_to_gp = $accessToGP;
            $user->is_gp_admin = $adminAccessToGP;

            if ($accessToGP == true || $adminAccessToGP == true) 
            {
                $token = Str::random(64);
                $user->gp_access_token = $token;
            } 
            else 
            {
                $user->gp_access_token = null;

            }
        }

        $user->designation = trim(array_get($inputs, 'designation'));

        $this->save($user);

        $user = User::find($user->id);

        if($user->account_blocked_status)
        {
            BsProjectUserPermission::revokeAccessFromAllBuildspaceProjects($user->getBsUser());
            BsUserGroup::removeBsUserFromAllGroups($user->getBsUser());
        }
        else
        {
            $allCompanies = $user->getAllCompanies();

            foreach($allCompanies as $company)
            {
                if(is_null($company->getBsCompany()->companyGroup)) continue;

                $bsGroup = $company->getBsCompany()->companyGroup->group;
                $bsGroup->addBsUser($user->getBsUser());
            }
        }

        return $user;
    }

    /**
     * Attempts to login with the given credentials.
     *
     * @param  array $inputs Array containing the credentials (email/username and password)
     *
     * @return  bool Success?
     */
    public function login(array $inputs)
    {
        if( ! isset( $inputs['password'] ) )
        {
            $inputs['password'] = null;
        }

        return \Confide::logAttempt($inputs, \Config::get('confide::signup_confirm'));
    }

    /**
     * Checks if the credentials has been throttled by too
     * much failed login attempts
     *
     * @param $inputs
     *
     * @return bool Is throttled
     * @internal param array $credentials Array containing the credentials (email/username and password)
     *
     */
    public function isThrottled(array $inputs)
    {
        return \Confide::isThrottled($inputs);
    }

    /**
     * Checks if the given credentials corresponds to a user that exists but
     * is not confirmed
     *
     * @param $inputs
     *
     * @return bool Exists and is not confirmed?
     * @internal param array $credentials Array containing the credentials (email/username and password)
     *
     */
    public function existsButNotConfirmed(array $inputs)
    {
        $user = \Confide::getUserByEmailOrUsername($inputs);

        if( $user )
        {
            $correctPassword = \Hash::check(
                isset( $inputs['password'] ) ? $inputs['password'] : false,
                $user->password
            );

            return ( ! $user->confirmed && $correctPassword );
        }
    }

    /**
     * Resets a password of a user. The $input['token'] will tell which user.
     *
     * @param  array $inputs Array containing 'token', 'password' and 'password_confirmation' keys.
     *
     * @return  bool Success
     */
    public function resetPassword($inputs)
    {
        $result = false;
        $user   = \Confide::userByResetPasswordToken($inputs['token']);

        if( $user )
        {
            $user->password              = $inputs['password'];
            $user->password_confirmation = $inputs['password_confirmation'];
            $result                      = $this->save($user);
        }

        // If result is positive, destroy token
        if( $result )
        {
            \Confide::destroyForgotPasswordToken($inputs['token']);
        }

        return $result;
    }

    /**
     * Simply saves the given instance
     *
     * @param  User $instance
     *
     * @return  bool Success
     */
    public function save(User $instance)
    {
        return $instance->save();
    }

    /**
     * Update user's own profile information
     *
     * @param $user
     * @param $inputs
     *
     * @return \PCK\Users\User
     */
    public function updateMyProfile(User $user, array $inputs)
    {
        $user->name           = $inputs['name'];
        $user->contact_number = $inputs['contact_number'];

        $this->save($user);

        $this->updatePassword($user, $inputs);

        $bsUserProfile = BsUserProfile::where('bs_sf_guard_user_profile.eproject_user_id', $user->id)
            ->where('bs_sf_guard_user_profile.deleted_at', null)
            ->first();

        if( $bsUserProfile )
        {
            $bsUserProfile->name        = $user->name;
            $bsUserProfile->contact_num = $user->contact_number;

            $bsUserProfile->save();

            $bsUser             = $bsUserProfile->User;
            $bsUser->first_name = $user->name;

            $bsUser->save();
        }

        return $user;
    }

    public function updatePassword(User $user, $input)
    {
        if( ! empty( $input['password'] ) )
        {
            $user->password              = $input['password'];
            $user->password_confirmation = $input['password_confirmation'];
        }

        $this->save($user);
    }

    public function getConfirmedUsers()
    {
        return $this->user->where('confirmed', '=', true)
            ->where('account_blocked_status', '=', false)
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getProjectSelectedUsersByProjectAndRoles(Project $project, array $roles, $onlyAssignedCompanies = true)
    {
        $users = $this->user->join('contract_group_project_users as cgpu', 'cgpu.user_id', '=', 'users.id')
            ->join('contract_groups as cg', 'cgpu.contract_group_id', '=', 'cg.id')
            ->where('cgpu.project_id', $project->id)
            ->whereIn('cg.group', $roles)
            ->get([ 'users.id', 'users.name', 'users.email' ]);

        if( $onlyAssignedCompanies ) $users = self::filterAssignedUsers($project, $users);

        return $users;
    }

    public function getProjectGroupOwnersByProjectAndRoles(Project $project, array $roles, $onlyAssignedCompanies = true)
    {
        $users = $this->user->join('contract_group_project_users as cgpu', 'cgpu.user_id', '=', 'users.id')
            ->join('contract_groups as cg', 'cgpu.contract_group_id', '=', 'cg.id')
            ->where('cgpu.project_id', $project->id)
            ->whereIn('cg.group', $roles)
            ->where('cgpu.is_contract_group_project_owner', true)
            ->get([ 'users.id', 'users.name', 'users.email' ]);

        if( $onlyAssignedCompanies ) $users = self::filterAssignedUsers($project, $users);

        return $users;
    }

    public function getCompanyAdminByProjectAndRoles(Project $project, array $roles, $onlyAssignedCompanies = true)
    {
        $users = User::join('companies as c', 'c.id', '=', 'users.company_id')
            ->join('contract_group_categories as cgc', 'c.contract_group_category_id', '=', 'cgc.id')
            ->join('contract_group_contract_group_category as cg_cgc', 'cg_cgc.contract_group_category_id', '=', 'cgc.id')
            ->join('contract_groups as cg', 'cg.id', '=', 'cg_cgc.contract_group_id')
            ->join('company_project as cp', 'cp.company_id', '=', 'c.id')
            ->where('cp.project_id', $project->id)
            ->whereIn('cg.group', $roles)
            ->where('users.is_admin', true)
            ->distinct()
            ->get([ 'users.id', 'users.name', 'users.email' ]);

        if( $onlyAssignedCompanies ) $users = self::filterAssignedUsers($project, $users);

        return $users;
    }

    public function getProjectDocumentSelectedGroupUsers(Project $project, DocumentManagementFolder $model)
    {
        $groupIds = array();

        $contractGroups = $model->contractGroups;

        foreach($contractGroups as $contractGroup)
        {
            $groupIds[] = $contractGroup->id;
        }

        if( empty( $groupIds ) )
        {
            return array();
        }

        return $this->user->join('contract_group_project_users as cgpu', 'cgpu.user_id', '=', 'users.id')
            ->where('cgpu.project_id', $project->id)
            ->whereIn('cgpu.contract_group_id', $groupIds)
            ->get([ 'users.id', 'users.name', 'users.email' ]);
    }

    public function sendNewlyGeneratedPasswordEmail($code)
    {
        $confideRepo = \App::make('Zizaco\Confide\EloquentRepository');
        $user        = $confideRepo->getUserByIdentity([ 'confirmation_code' => $code ]);
        $newPassword = str_random(8);

        $user->password              = $newPassword;
        $user->password_confirmation = $newPassword;

        // Generate a new confirmation code so that the previous one will expired.
        // To prevent double submission of confirmation's email.
        $user->confirmation_code = md5(uniqid(mt_rand(), true));

        $user->save();

        $user->password_updated_at = null;

        $user->save();

        $companyLogoPath = public_path().MyCompanyProfile::getLogoPath();

        if(!file_exists($companyLogoPath)) $companyLogoPath = null;

        $data['name']               = $user->name;
        $data['password']           = $newPassword;
        $data['loginEmail']         = $user->email;
        $data['accessToBuildSpace'] = $user->allow_access_to_buildspace;
        $data['companyLogoPath']    = $companyLogoPath;

        \Log::info('Queueing email for login credentials: ' . $user->id . ' (' . $user->username . ')');

        $vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);

        $subject = trans('email.eTenderLoginPassword');

        if($vendorManagementModuleEnabled && $user->company->contractGroupCategory->isTypeExternal())
        {
            $subject = getenv('VENDOR_REGISTRATION_ACCOUNT_CONFIRMATION_SUBJECT') ? getenv('VENDOR_REGISTRATION_ACCOUNT_CONFIRMATION_SUBJECT') : trans('email.eTenderLoginPassword');
        }

        \Mail::queue('emails.auth.send_generated_password', $data, function($message) use ($user, $subject)
        {
            $message->to($user->email, $user->name)->subject($subject);
        });

        \Log::info('Queued email for login credentials: ' . $user->id . ' (' . $user->username . ')');
    }

    public function getAdminUserByCompanyIds(array $companyIds)
    {
        return $this->user->join('companies as c', 'c.id', '=', 'users.company_id')
            ->whereIn('c.id', $companyIds)
            ->where('users.is_admin', true)
            ->get([ 'users.id', 'users.name', 'users.email', 'c.name as company_name' ]);
    }

    /**
     * Returns Users from all companies of a Contract Group Category.
     *
     * @param         $inputs
     * @param Company $company
     *
     * @return array
     */
    public function getImportableUsers($inputs, Company $company)
    {
        if( ! $company->contractGroupCategory )
        {
            return array();
        }

        $idColumn      = "users.id";
        $selectColumns = array( $idColumn );

        $userColumns    = array(
            'name'  => 1,
            'email' => 2,
        );
        $companyColumns = array(
            'name'         => 3,
            'reference_no' => 4
        );

        $allColumns = array(
            'users'     => $userColumns,
            'companies' => $companyColumns
        );

        $usersTableName = $this->user->getTable();
        $query          = \DB::table("{$usersTableName} as users");

        $dataTable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $dataTable->properties->query->join('companies', 'companies.id', '=', 'users.company_id')
            ->leftJoin('company_imported_users', 'company_imported_users.user_id', '=', 'users.id')
            ->where('companies.contract_group_category_id', '=', $company->contractGroupCategory->id)
            ->where('companies.confirmed', '=', true)
            ->where('companies.id', '!=', $company->id)
            ->where(function($query) use ($company)
            {
                $query->where('company_imported_users.company_id', '!=', $company->id)
                    ->orWhereNull('company_imported_users.company_id');
            });

        $dataTable->addAllStatements();

        $results = $dataTable->getResults();

        $dataArray = array();

        foreach($results as $arrayIndex => $arrayItem)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $dataTable->properties->pagingOffset );
            $record  = User::find($arrayItem->id);

            $dataArray[] = array(
                'indexNo'                => $indexNo,
                'id'                     => $record->id,
                'name'                   => $record->name,
                'email'                  => $record->email,
                'companyName'            => $record->company->name,
                'companyReferenceNumber' => $record->company->reference_no,
            );
        }

        return $dataTable->dataTableResponse($dataArray);
    }

    /**
     * Returns users whose company are assigned to the project.
     *
     * @param Project $project
     * @param         $users
     *
     * @return Collection
     */
    public static function filterAssignedUsers(Project $project, $users)
    {
        $assignedUsers = new Collection();

        foreach($users as $user)
        {
            $user = User::find($user->id);

            if( $user->getAssignedCompany($project) ) $assignedUsers->push($user);
        }

        return $assignedUsers;
    }

}
