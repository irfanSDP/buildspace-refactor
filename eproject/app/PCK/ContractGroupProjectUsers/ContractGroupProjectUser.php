<?php namespace PCK\ContractGroupProjectUsers;

use Illuminate\Database\Eloquent\Model;

/**
 * Editors and Verifiers
 *
 * Editors->is_contract_group_project_owners = true;
 * Verifiers->is_contract_group_project_owners = false;
 *
 */
class ContractGroupProjectUser extends Model {

    protected $table = 'contract_group_project_users';

    public function contractGroup()
    {
        return $this->belongsTo('PCK\ContractGroups\ContractGroup');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

}