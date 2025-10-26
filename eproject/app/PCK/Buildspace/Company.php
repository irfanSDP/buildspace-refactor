<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use PCK\Companies\Company as EprojectCompany;

class Company extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_companies';

    protected $primaryKey = 'reference_id';

    public function companyGroup()
    {
        return $this->hasOne('PCK\Buildspace\CompanyGroup', 'company_id', 'id');
    }

    public function getEprojectCompany()
    {
        return EprojectCompany::where('reference_id', $this->reference_id)->first();
    }
}