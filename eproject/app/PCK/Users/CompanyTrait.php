<?php namespace PCK\Users;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use PCK\Companies\CompanyImportedUsersLog;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

trait CompanyTrait {

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function fosterCompanies()
    {
        return $this->belongsToMany('PCK\Companies\Company', 'company_imported_users', 'user_id', 'company_id');
    }

    public function hasCompany()
    {
        return is_null($this->company) ? false : true; 
    }

    public function getAllCompanies()
    {
        $companies = $this->fosterCompanies()->get();

        $companies->prepend($this->company);

        return $companies;
    }

    public function getAllCompanyIds()
    {
        $ids = $this->getFosterCompanyIds();

        if($this->company_id)
        {
            array_unshift($ids, $this->company_id);
        }

        return $ids;
    }

    public function getFosterCompanyIds()
    {
        return $this->fosterCompanies->lists('id');
    }

    /**
     * Returns true if the user has the privilege to import users into their own company.
     *
     * @return bool
     */
    public function canImportUsers()
    {
        if( $this->isSuperAdmin() ) return true;

        // Not allowed to import into user's own foster company.
        $contractGroupAllowed = $this->company->contractGroupCategory->includesContractGroups(array(
            ContractGroup::getIdByGroup(Role::PROJECT_OWNER),
            ContractGroup::getIdByGroup(Role::GROUP_CONTRACT),
            ContractGroup::getIdByGroup(Role::PROJECT_MANAGER),
        ));

        if( $contractGroupAllowed && $this->isGroupAdmin() ) return true;

        return false;
    }

    /**
     * Returns true if any of the user's companies' Contract Group Category is assigned to the Contract Groups.
     *
     * @param array $roles
     *
     * @return bool
     */
    public function hasCompanyRoles(array $roles)
    {
        if( $this->isSuperAdmin() ) return false;

        $roleIds = array();

        foreach($roles as $group)
        {
            $roleIds[] = ContractGroup::getIdByGroup($group);
        }

        foreach($this->getAllCompanies() as $company)
        {
            // This will be dependent on the project's contract type
            if( $company->contractGroupCategory && $company->contractGroupCategory->includesContractGroups($roleIds) ) return true;
        }

        return false;
    }

    public function canAddUser()
    {
        if( $this->isSuperAdmin() ) return true;

        $autonomousCompanyUserManagement = getenv('AUTONOMOUS_COMPANY_USER_MANAGEMENT') ? true : false;

        if( $this->isGroupAdmin() && $autonomousCompanyUserManagement ) return true;

        return false;
    }

    /**
     * Returns the company the user is assigned to at the specified time.
     *
     * @param $timestamp
     *
     * @return null
     */
    public function getHistoricCompany($timestamp)
    {
        if( ! $timestamp instanceof Carbon ) $timestamp = Carbon::parse($timestamp);

        $latestRecord = UserCompanyLog::where('user_id', '=', $this->id)
            ->where('created_at', '<=', $timestamp)
            ->orderBy('created_at', 'desc')
            ->first();

        if( ! $latestRecord ) return $this->company;

        return $latestRecord->company;
    }

    /**
     * Returns the foster companies the user is assigned to at the specified time.
     *
     * @param $timestamp
     *
     * @return Collection
     */
    public function getHistoricFosterCompanies($timestamp)
    {
        if( ! $timestamp instanceof Carbon ) $timestamp = Carbon::parse($timestamp);

        $log = CompanyImportedUsersLog::where('user_id', '=', $this->id)
            ->where('created_at', '<=', $timestamp)
            ->orderBy('created_at', 'desc')
            ->get();

        $output = new Collection;

        foreach(array_unique($log->lists('company_id')) as $companyId)
        {
            $companySpecificLog = $log->filter(function($logEntry) use ($companyId)
            {
                return $logEntry->company_id == $companyId;
            });

            $isIncluded = $companySpecificLog->first()->import;

            if( $isIncluded ) $output->push($companySpecificLog->first()->company);
        }

        return $output;
    }

    public function hasConsultantManagementCompanyRoles()
    {
        if( $this->isSuperAdmin() ) return false;

        $companyIds = [];

        foreach($this->getAllCompanies() as $company)
        {
            $companyIds[] = $company->id;
        }

        if($companyIds)
        {
            $count = \DB::table('consultant_management_roles_contract_group_categories')
            ->join('contract_group_categories', 'consultant_management_roles_contract_group_categories.contract_group_category_id', '=', 'contract_group_categories.id')
            ->join('companies', 'companies.contract_group_category_id', '=', 'contract_group_categories.id')
            ->whereIn('companies.id', $companyIds)
            ->count();

            return ($count);
        }

        return false;
    }

    public function isCompanyTypeInternal()
    {
        return $this->company && $this->company->contractGroupCategory->isTypeInternal();
    }

    public function isCompanyTypeExternal()
    {
        return $this->company && $this->company->contractGroupCategory->isTypeExternal();
    }
}