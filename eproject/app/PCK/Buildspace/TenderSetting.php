<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use PCK\Companies\Company;

class TenderSetting extends Model
{
    protected $connection = 'buildspace';
    protected $table      = 'bs_tender_settings';

    public function projectStructure()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    public function bsCompany()
    {
        return $this->belongsTo('PCK\Buildspace\Company', 'awarded_company_id', 'id');
    }
}

