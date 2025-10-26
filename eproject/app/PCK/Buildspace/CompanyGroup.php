<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class CompanyGroup extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_company_group';

    protected $primaryKey = 'company_id';

    public function company()
    {
        return $this->belongsTo('PCK\Buildspace\Company', 'company_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo('PCK\Buildspace\Group', 'group_id', 'id');
    }

}