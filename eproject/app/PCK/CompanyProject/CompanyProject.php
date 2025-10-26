<?php namespace PCK\CompanyProject;

use Illuminate\Database\Eloquent\Model;

class CompanyProject extends Model {

    protected $table = 'company_project';

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function role()
    {
        return $this->belongsTo('PCK\ContractGroups\ContractGroup', 'contract_group_id');
    }
}