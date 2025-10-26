<?php namespace PCK\CompanyTenderListOfTendererInformation;

use Illuminate\Database\Eloquent\Model;

class CompanyTenderListOfTendererInformation extends Model {

    protected $table = 'company_tender_lot_information';

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

    public function tenderLOTInformation()
    {
        return $this->belongsTo('PCK\TenderListOfTendererInformation\TenderListOfTendererInformation', 'tender_lot_information_id');
    }

}