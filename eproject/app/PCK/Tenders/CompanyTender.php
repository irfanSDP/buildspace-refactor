<?php namespace PCK\Tenders;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Tenders\Tender;
use PCK\Companies\Company;
use PCK\Tenders\CompanyTenderTenderAlternative;

class CompanyTender extends Model {
    
    protected $table = 'company_tender';

    public function tenderAlternatives()
    {
        $bsProjectMainInformation = $this->tender->project->getBsProjectMainInformation();

        $tenderAlternativeIds = ($bsProjectMainInformation) ? $bsProjectMainInformation->projectStructure->tenderAlternatives()->lists('id') : [];

        if(empty($tenderAlternativeIds))
        {
            //preset with non exists id so we can use the array to perform query
            $tenderAlternativeIds[] = -1;
        }

        return $this->hasMany(CompanyTenderTenderAlternative::class, 'company_tender_id', 'id')->whereIn('tender_alternative_id', $tenderAlternativeIds);
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}