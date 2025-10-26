<?php namespace PCK\Projects;

use PCK\Companies\Company;
use PCK\CompanyProject\CompanyProject;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

trait ContractGroupRelation {

    // will return QS role if there is no role selected
    public function getCallingTenderRole()
    {
        if( ! $this->contractGroupTenderDocumentPermission ) return Role::CLAIM_VERIFIER;

        return $this->contractGroupTenderDocumentPermission->contractGroup->group;
    }

    /**
     * Returns the company assigned to the group.
     *
     * @param $group
     *
     * @return null|Company
     */
    public function getCompanyByGroup($group)
    {
        $contractGroup = ContractGroup::where('group', '=', $group)->first();

        if( ! $record = CompanyProject::where('project_id', '=', $this->id)->where('contract_group_id', '=', $contractGroup->id)->first() ) return null;

        return $record->company;
    }

    /**
     * Returns contract groups assigned to the project.
     *
     * @param array $excludeGroups
     *
     * @return array
     */
    public function getAssignedGroups(array $excludeGroups = array())
    {
        $project = $this;

        $contractGroupRepository = \App::make('PCK\ContractGroups\ContractGroupRepository');

        $contractGroups = $contractGroupRepository->getGroupsByContractId($this, $excludeGroups);

        return array_filter($contractGroups, function($contractGroup) use ($project)
        {
            return CompanyProject::where('project_id', '=', $project->id)->where('contract_group_id', '=', $contractGroup->id)->first();
        });
    }

}