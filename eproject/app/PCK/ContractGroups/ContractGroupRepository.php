<?php namespace PCK\ContractGroups;

use PCK\Projects\Project;
use PCK\Contracts\Contract;

class ContractGroupRepository {

    private $contractGroup;

    public function __construct(ContractGroup $contractGroup)
    {
        $this->contractGroup = $contractGroup;
    }

    public function all(array $excludedGroups = array())
    {
        return ContractGroup::whereNotIn('group', $excludedGroups)->get()->sortByDesc('id');
    }

    public function findById($contractGroupId)
    {
        return $this->contractGroup->findOrFail($contractGroupId);
    }

    public function findByIds($ids)
    {
        $groups = array();

        foreach($ids as $id)
        {
            $groups[] = $this->findById($id);
        }

        return $groups;
    }

    public function getGroupsByContractId(Project $project, array $excludeGroups = array())
    {
        $data = array();

        $groups = $this->contractGroup->orderBy('id')->get();

        foreach($groups as $group)
        {
            if( $excludeGroups AND in_array($group->group, $excludeGroups) ) continue;

            $data[ $group['name'] ] = $group;
        }

        ksort($data);

        return $data;
    }

    public function getGroupByGroupKeyAndContractId($groupKey)
    {
        return $this->contractGroup->where('group', '=', $groupKey)
            ->firstOrFail();
    }

}