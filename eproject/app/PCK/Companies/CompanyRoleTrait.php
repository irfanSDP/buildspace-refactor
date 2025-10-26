<?php namespace PCK\Companies;

use PCK\CompanyProject\CompanyProject;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\Helpers\Parameter;
use PCK\Projects\Project;

trait CompanyRoleTrait {

    /**
     * Returns true if the company has the specified role in the project.
     *
     * @param Project   $project
     * @param int|array $role
     *
     * @return bool
     */
    public function hasProjectRole(Project $project, $role)
    {
        $roleIds = array();

        foreach(Parameter::toArray($role) as $roleGroup)
        {
            $roleIds[] = ContractGroup::getIdByGroup($roleGroup);
        }

        $isContractor = false;

        if( in_array(ContractGroup::getIdByGroup(Role::CONTRACTOR), $roleIds) )
        {
            $isContractor = $this->hasContractorProjectRole($project);
        }

        $companyProjectRelation = CompanyProject::where('project_id', '=', $project->id)
            ->where('company_id', '=', $this->id)
            ->whereIn('contract_group_id', $roleIds)
            ->first();

        return ( $isContractor || ( ! empty( $companyProjectRelation ) ) );
    }

    /**
     * Contractor-specific check.
     * Returns true if this company is currently selected as a Contractor.
     *
     * @param Project $project
     *
     * @return bool
     */
    public function hasContractorProjectRole(Project $project)
    {
        // For corrupted projects (that failed to be deleted completely).
        if( ! $project->latestTender ) return false;

        if( ! $project->latestTender->getTenderStageInformation() ) return false;

        $contractorIds = array();

        foreach($project->latestTender->getTenderStageInformation()->selectedContractors as $contractor)
        {
            $contractorIds[] = $contractor->id;
        }

        if( in_array($this->id, $contractorIds) ) return true;

        return false;
    }

    /**
     * Returns the contract group of the company for the project.
     *
     * @param Project $project
     *
     * @return null|ContractGroup
     */
    public function getContractGroup(Project $project)
    {
        $companyProject = CompanyProject::where('project_id', '=', $project->id)
            ->where('company_id', '=', $this->id)
            ->first();

        if( empty( $companyProject ) )
        {
            // Check for contractor.
            if( $this->hasProjectRole($project, Role::CONTRACTOR) ) return ContractGroup::find(ContractGroup::getIdByGroup(Role::CONTRACTOR));

            return null;
        }

        return ContractGroup::find($companyProject->contract_group_id);
    }

    /**
     * Returns true if newly created users of this company is to be given BuildSpace access by default.
     *
     * @return bool
     */
    public function giveDefaultAccessToBuildSpace()
    {
        return $this->contractGroupCategory->default_buildspace_access;
    }

}