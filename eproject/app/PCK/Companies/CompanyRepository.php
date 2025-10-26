<?php namespace PCK\Companies;

use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Helpers\Parameter;
use PCK\Projects\Project;
use PCK\Helpers\DataTables;
use PCK\Base\BaseModuleRepository;
use PCK\Users\User;
use PCK\SystemModules\SystemModuleConfiguration;

class CompanyRepository extends BaseModuleRepository {

    private $company;
    private $contractGroupProjectUserRepository;

    public function __construct(Company $company, ContractGroupProjectUserRepository $contractGroupProjectUserRepository)
    {
        $this->company                            = $company;
        $this->contractGroupProjectUserRepository = $contractGroupProjectUserRepository;
    }

    /**
     * Get available companies listing.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all()
    {
        return $this->company
            ->orderBy('id', 'desc')
            ->where('confirmed', '=', true)
            ->get();
    }

    public function lists()
    {
        return Company::where('confirmed', '=', true)->orderBy('name')->lists('name', 'id');
    }

    /**
     * Get all contractors together with associated objects in an array.
     *
     * @param array $inputs
     * @param array $inputs
     * @param bool  $confirmed Show only confirmed (verified) companies.
     *
     * @return string
     */
    public function allInArray(array $inputs, $confirmed = true)
    {
        $idColumn      = "companies.id";
        $selectColumns = array( $idColumn );

        $companyColumns = array(
            'name'         => 1,
            'reference_no' => 3
        );

        $contractGroupCategoryColumns = array(
            'name' => 4
        );

        $allColumns = array(
            'companies'                 => $companyColumns,
            'contract_group_categories' => $contractGroupCategoryColumns
        );

        $companiesTableName = $this->company->getTable();
        $query              = \DB::table("{$companiesTableName} as companies");

        $datatable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $datatable->properties->query->leftJoin('contract_group_categories', 'companies.contract_group_category_id', '=', 'contract_group_categories.id');

        $datatable->properties->query->where('companies.confirmed', '=', $confirmed);

        if(SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT))
        {
            $datatable->properties->query->whereNull('companies.purge_date');
        }

        $datatable->addAllStatements();

        $results = $datatable->getResults();

        $dataArray = array();

        foreach($results as $arrayIndex => $arrayItem)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );
            $record  = $this->find($arrayItem->id);

            $dataArray[] = array(
                'indexNo'                             => $indexNo,
                'companyName'                         => $record->name,
                'mainContact'                         => $record->main_contact,
                'contractGroupCategory'               => $record->contractGroupCategory ? $record->contractGroupCategory->name : null,
                'referenceNo'                         => $record->reference_no,
                'email'                               => $record->email,
                'createdAt'                           => $record->created_at,
                'route:companies.users'               => route('companies.users', array( $record->id )),
                'route:companies.edit'                => route('companies.edit', array( $record->id )),
                'route:companies.delete'              => route('companies.delete', array( $record->id )),
                'route:companies.verification.show'   => route('companies.verification.show', array( $record->id )),
                'route:companies.verification.delete' => route('companies.verification.delete', array( $record->id )),
                'route:companies.verify'              => route('companies.verify', array( $record->id )),
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    /**
     * Find company's related information by ID
     *
     * @param $id
     *
     * @return Company
     */
    public function find($id)
    {
        return $this->company->findOrFail($id);
    }

    public function getByRoles($roles)
    {
        return Company::whereHas('contractGroupCategory', function($query) use ($roles)
        {
            $query->join('contract_group_contract_group_category', 'contract_group_contract_group_category.contract_group_category_id', '=', 'contract_group_categories.id')
                ->join('contract_groups', 'contract_groups.id', '=', 'contract_group_contract_group_category.contract_group_id')
                ->whereIn('contract_groups.group', $roles);
        })
            ->where('confirmed', '=', true)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function allNonContractors()
    {
        return $this->getByRoles(Role::getRolesExcept(Role::CONTRACTOR));
    }

    /**
     * Find company's related information by ROLE
     * currently too specific for contractors?
     *
     * @param $role
     *
     * @return mixed
     */
    public function findWithRoleType($role)
    {
        return Company::whereHas('contractGroupCategory', function($query) use ($role)
        {
            $query->join('contract_group_contract_group_category', 'contract_group_contract_group_category.contract_group_category_id', '=', 'contract_group_categories.id')
                ->join('contract_groups', 'contract_groups.id', '=', 'contract_group_contract_group_category.contract_group_id')
                ->where('contract_groups.group', '=', $role);
        })
            ->with(array(
                'contractor',
                'contractor.workCategories',
                'contractor.workSubcategories',
                'country',
                'state'
            ))
            ->where('confirmed', '=', true)
            ->get();
    }

    /**
     * Find user(s) related with company by ID
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|static
     */
    public function findUsersWithCompanyId($id)
    {
        return $this->company->with('users')->findOrFail($id);
    }

    /**
     * Create new record of company.
     *
     * @param array $inputs
     * @param bool  $confirmed True if the company is verified.
     *
     * @return mixed|Company
     */
    public function add(array $inputs, $confirmed = false)
    {
        $this->company = $this->insertData($this->company, $inputs);

        $this->company->confirmed = $confirmed;

        $this->company->save();

        if(isset($inputs['vendor_category_id']))
        {
            $this->company->vendorCategories()->sync($inputs['vendor_category_id']);
        }

        $this->saveAttachments($this->company, $inputs);

        return $this->company;
    }

    /**
     * Generic function to insert data into the Company object
     *
     * @param       $company
     * @param array $inputs
     *
     * @return mixed
     */
    public function insertData(Company $company, array $inputs)
    {
        $company->name                       = trim($inputs['name']);
        $company->address                    = trim($inputs['address']);
        $company->main_contact               = trim($inputs['main_contact']);
        $company->email                      = trim($inputs['email']);
        $company->telephone_number           = trim($inputs['telephone_number']);
        $company->fax_number                 = trim($inputs['fax_number']);
        $company->contract_group_category_id = $inputs['contract_group_category_id'];
        $company->country_id                 = $inputs['country_id'];
        $company->state_id                   = $inputs['state_id'];
        $company->reference_no               = trim($inputs['reference_no']);
        $company->tax_registration_no        = empty( trim($inputs['tax_registration_no']) ) ? null : $inputs['tax_registration_no'];

        if(isset($inputs['business_entity_type_id']))
        {
            if($inputs['business_entity_type_id'] == 'other')
            {
                $company->business_entity_type_id = null;
                $company->business_entity_type_name  = trim($inputs['business_entity_type_other']);
            }
            else
            {
                $company->business_entity_type_id = $inputs['business_entity_type_id'];
                $company->business_entity_type_name = null;
            }
        }

        return $company;
    }

    /**
     * Update existing record of selected company
     * and attach any attachments
     *
     * @param Company $company
     * @param         $inputs
     *
     * @return Company
     */
    public function update(Company $company, array $inputs)
    {
        $company = $this->insertData($company, $inputs);

        $company->save();

        if(isset($inputs['vendor_category_id']))
        {
            $company->vendorCategories()->sync($inputs['vendor_category_id']);
        }

        $this->saveAttachments($company, $inputs);

        return $company;
    }

    /**
     * Deletes (detaches) all contract group types from the company
     * Deletes:
     *  -contractor
     *
     * @param $company
     */
    public function deleteAllContractGroupTypes(Company $company)
    {
        if( $company->contractor )
        {
            $company->contractor->delete();
        }
    }

    public function getTendersByCompanyAndProject(Company $company, Project $project, $tenderId = null)
    {
        return $this->company->with(array(
            'tenders' => function($q) use ($project, $tenderId)
            {
                $q->where('project_id', '=', $project->id);

                if( $tenderId )
                {
                    $q->where('tender_id', '=', $tenderId);
                }
            }
        ))->findOrFail($company->id);
    }

    public function getCompaniesWithRoles(Project $project, array $roles = array())
    {
        $query = \DB::table("companies as c");

        return $query->join('company_project as cp', 'cp.company_id', '=', 'c.id')
            ->join('contract_groups as cg', 'cg.id', '=', 'cp.contract_group_id')
            ->whereIn('cg.group', $roles)
            ->where('cp.project_id', '=', $project->id)
            ->get(array( 'c.id' ));
    }

    public function getCompaniesNotInReferenceId(array $notIn)
    {
        return $this->company->whereNotIn('reference_id', $notIn)
            ->get();
    }

    /**
     * Adds users as imported users for the company.
     *
     * @param Company $company
     * @param         $userIds
     *
     * @return bool
     */
    public function importUsers(Company $company, $userIds)
    {
        foreach(Parameter::toArray($userIds) as $userId)
        {
            $this->importUser($company, User::find($userId));
        }

        return true;
    }

    /**
     * Adds the user as an imported user for the company.
     *
     * @param Company $company
     * @param User    $user
     *
     * @return bool
     */
    public function importUser(Company $company, User $user)
    {
        // Check if the user is under the company.
        if( $user->company->id == $company->id )
        {
            return false;
        }

        // Check if user is already imported.
        if( in_array($company->id, $user->getFosterCompanyIds()) )
        {
            return false;
        }

        // Check if the user is from the same contract group category
        if( $user->company->contractGroupCategory->id != $company->contractGroupCategory->id )
        {
            return false;
        }

        // Import the user.
        $user->fosterCompanies()->save($company, array(
            'created_at' => 'now()',
            'updated_at' => 'now()',
        ));

        // adds user to Buildspace Group
        if($company->getBsCompany()->companyGroup)
        {
            $bsGroup = $company->getBsCompany()->companyGroup->group;
            $bsGroup->addBsUser($user->getBsUser());
        }

        CompanyImportedUsersLog::log($company, $user, true);

        $user->load('fosterCompanies');

        return true;
    }

    /**
     * Remove the user as an imported user of the company.
     *
     * @param Company $company
     * @param User    $user
     *
     * @return bool
     */
    public function deportUser(Company $company, User $user)
    {
        // Check if user is still imported.
        if( ! in_array($company->id, $user->getFosterCompanyIds()) )
        {
            return false;
        }

        // Unset editor and verifier privileges.
        foreach($company->projects as $project)
        {
            $this->contractGroupProjectUserRepository->removeRole($project, $user);
        }

        $company->importedUsers()->detach($user->id);

        CompanyImportedUsersLog::log($company, $user, false);

        if($bsGroup = $company->getBsCompany()->companyGroup)
        {
            $bsGroup = $company->getBsCompany()->companyGroup->group;
            $bsGroup->removeBsUser($user->getBsUser());
        }

        return true;
    }

    public function getUsersWithPendingTasks(Company $company)
    {
        $usersWithPendingTasks = [];
        $allCompanyUsers       = $company->users->merge($company->importedUsers);

        foreach($allCompanyUsers as $user)
        {
            if($user->isTransferable()) continue;

            array_push($usersWithPendingTasks,[
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ]);
        }

        return $usersWithPendingTasks;
    }

    public function companyUserHasNoPendingTasks(Company $company, User $user)
    {
        if(count($this->getCompanyUserPendingTenderingTasks($company, $user)) > 0) return false;
        if(count($this->getCompanyUserPendingPostContractTasks($company, $user)) > 0) return false;
        if(count($this->getCompanyUserPendingSiteModuleTasks($company, $user)) > 0) return false;
        if(count($this->getCompanyUserLetterOfAwardUserPermissions($company, $user)) > 0) return false;
        if(count($this->getCompanyUserRequestForVariationUserPermissions($company, $user)) > 0) return false;
        if(count($this->getCompanyUserContractManagementUserPermissions($company, $user)) > 0) return false;
        if(count($this->getCompanyUserSiteManagementUserPermissions($company, $user)) > 0) return false;
        if(count($this->getCompanyUserRequestForInspectionUserPermissions($company, $user)) > 0) return false;

        return true;
    }

    public function getCompanyUserPendingTenderingTasks(Company $company, User $user)
    {
        $tasks = [];

        foreach($user->getListOfTenderingPendingReviewTasks(true) as $task)
        {
            if($task['company_id'] != $company->id) continue;

            array_push($tasks, $task);
        }

        return $tasks;
    }

    public function getCompanyUserPendingPostContractTasks(Company $company, User $user)
    {
        $tasks = [];

        foreach($user->getListOfPostContractPendingReviewTasks(true) as $task)
        {
            if($task['company_id'] != $company->id) continue;

            array_push($tasks, $task);
        }

        return $tasks;
    }

    public function getCompanyUserPendingSiteModuleTasks(Company $company, User $user)
    {
        $tasks = [];

        foreach($user->getListOfSiteModulePendingReviewTasks(true) as $task)
        {
            if($task['company_id'] != $company->id) continue;

            array_push($tasks, $task);
        }

        return $tasks;
    }

    public function getCompanyUserLetterOfAwardUserPermissions(Company $company, User $user)
    {
        $tasks = [];

        foreach($user->getUserPermissionsInLetterOfAward() as $task)
        {
            if($task['company_id'] != $company->id) continue;

            array_push($tasks, $task);
        }

        return $tasks;
    }

    public function getCompanyUserRequestForVariationUserPermissions(Company $company, User $user)
    {
        $permissions = [];

        foreach($user->getUserPermissionsInRequestOfVariation() as $permission)
        {
            if($permission['company_id'] != $company->id) continue;

            array_push($permissions, $permission);
        }

        return $permissions;
    }

    public function getCompanyUserContractManagementUserPermissions(Company $company, User $user)
    {
        $permissions = [];

        foreach($user->getUserPermissionsInContractManagement() as $permission)
        {
            if($permission['company_id'] != $company->id) continue;

            array_push($permissions, $permission);
        }

        return $permissions;
    }

    public function getCompanyUserSiteManagementUserPermissions(Company $company, User $user)
    {
        $permissions = [];

        foreach($user->getUserPermissionsInSiteManagement() as $permission)
        {
            if($permission['company_id'] != $company->id) continue;

            array_push($permissions, $permission);
        }

        return $permissions;
    }

    public function getCompanyUserRequestForInspectionUserPermissions(Company $company, User $user)
    {
        $permissions = [];

        foreach($user->getUserPermissionsInRequestForInspection() as $permission)
        {
            if($permission['company_id'] != $company->id) continue;

            array_push($permissions, $permission);
        }

        return $permissions;
    }

    public function getVendorPerformanceEvaluationApprovals(Company $company, User $user)
    {
        $records = [];

        foreach($user->getPendingVendorPerformanceEvaluationFormApprovals() as $record)
        {
            if($record['company_id'] != $company->id) continue;

            array_push($records, $record);
        }

        return $records;
    }
}