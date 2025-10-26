<?php namespace PCK\ContractGroupCategory;

use Illuminate\Database\Eloquent\Model;

class ContractGroupCategoryPrivilege extends Model {

    const DASHBOARD_SYSTEM_OVERVIEW       = 1;
    const DASHBOARD_PROJECT_DESIGN_STAGE  = 2;
    const DASHBOARD_PROJECT_TENDERING     = 4;
    const DASHBOARD_PROJECT_POST_CONTRACT = 8;

    protected $table    = 'contract_group_category_privileges';
    protected $fillable = [
        'contract_group_category_id',
        'identifier',
    ];

}