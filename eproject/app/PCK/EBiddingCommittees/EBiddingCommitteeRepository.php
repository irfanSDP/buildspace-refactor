<?php namespace PCK\EBiddingCommittees;

use PCK\Companies\Company;
use PCK\Projects\Project;
use PCK\ContractGroups\Types\Role;
use PCK\ContractGroups\ContractGroup;
use PCK\Users\User;

class EBiddingCommitteeRepository {

    /**
     * @var EBiddingCommittee
     */
    private $instance;

    public function __construct(EBiddingCommittee $instance)
    {
        $this->instance = $instance;
    }

    public function getAssignedCommittee(Project $project)
    {
        $data = array();

        $results   = EBiddingCommittee::where('project_id',$project->id)->get();

        foreach($results as $result)
        {
            $data[ $result->user_id ] = $result->is_committee;
        }

        return $data;
    }
    
    public function getAssignedCommitteeByName(Project $project, ContractGroup $contractGroup)
    {
        $data = array();

        $results = \DB::table($this->instance->getTable())
            ->where('contract_group_id', '=', $contractGroup->id)
            ->where('project_id', '=', $project->id)
            ->get();

        foreach($results as $result)
        {
            $user = User::find($result->user_id);
            $data[ $user->name ] = $result->is_committee;
        }

        return $data;
    }

    public function getAssignedEditor(Project $project, ContractGroup $contractGroup)
    {
        $data = array();

        $results = \DB::table($this->instance->getTable())
            ->where('contract_group_id', '=', $contractGroup->id)
            ->where('project_id', '=', $project->id)
            ->get();

        foreach($results as $result)
        {
            $data[ $result->user_id ] = $result->is_editor;
        }

        return $data;
    }

    // public function getAssignedCommittee(Project $project, ContractGroup $contractGroup)
    // {
    //     $data = array();

    //     $results = \DB::table($this->instance->getTable())
    //         ->where('contract_group_id', '=', $contractGroup->id)
    //         ->where('project_id', '=', $project->id)
    //         ->get();

    //     foreach($results as $result)
    //     {
    //         $data[ $result->user_id ] = $result->is_committee;
    //     }

    //     return $data;
    // }

    public function insertByRolesEbidding(Project $project, array $inputs)
    {

        // will delete existing records if available
        $this->deleteExistingRolesRecord($project);

        $data     = array();
        $ownerIds = array();
        
        // will get selected group project owner first, then only array unique for selected user to enter normal user
        $uniqueUserIds = array_unique(array_merge(
            isset($inputs['is_committee']) ? $inputs['is_committee'] : []
        ));

        foreach ($uniqueUserIds as $userId) {
            $user = User::find($userId);
            $contractGroup = $user->getAssignedCompany($project)->getContractGroup($project);

            $isCommittee = isset($inputs['is_committee']) && in_array($userId, $inputs['is_committee']);
            $ownerIds[] = $userId;

            $data[] = array(
                'contract_group_id' => $contractGroup->id,
                'project_id' => $project->id,
                'user_id' => $userId,
                'is_committee' => $isCommittee,
                'created_at' => 'NOW()',
                'updated_at' => 'NOW()'
            );
        }

        if( ! empty( $data ) )
        {
            return \DB::table($this->instance->getTable())->insert($data);
        }

        return false;
    }

    public function deleteExistingRolesRecord(Project $project)
    {
        return \DB::table($this->instance->getTable())
            ->where('project_id', '=', $project->id)
            ->delete();
    }

}