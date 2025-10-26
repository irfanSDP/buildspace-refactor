<?php namespace PCK\CompanyTenderRecommendationOfTendererInformation;

use Illuminate\Database\Eloquent\Model;

class CompanyTenderRecommendationOfTendererInformation extends Model {

    protected $table = 'company_tender_rot_information';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $model)
        {
            foreach($model->contractorsCommitmentStatusLog as $log)
            {
                $log->delete();
            }
        });
    }

    public function contractorsCommitmentStatusLog()
    {
        return $this->morphMany('PCK\ContractorsCommitmentStatusLogs\ContractorsCommitmentStatusLog', 'loggable')
            ->orderBy('id', 'ASC');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function tenderROTInformation()
    {
        return $this->belongsTo('PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation', 'tender_rot_information_id');
    }

}