<?php namespace PCK\CompanyTenderCallingTenderInformation;

use Illuminate\Database\Eloquent\Model;

class CompanyTenderCallingTenderInformation extends Model {

    protected $table = 'company_tender_calling_tender_information';

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

    public function tenderCallingTenderInformation()
    {
        return $this->belongsTo('PCK\TenderCallingTenderInformation\TenderCallingTenderInformation', 'tender_calling_tender_information_id');
    }

}