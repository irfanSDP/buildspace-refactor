<?php namespace PCK\Tenders;

use Carbon\Carbon;
use PCK\Helpers\ModelOperations;
use PCK\Projects\Project;
use PCK\Companies\Company;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use PCK\TenderInterviews\TenderInterview;

class OpenTenderPersonInCharge extends Model {

    protected $fillable = ['tender_id','created_by','name','email','phone_number', 'department'];

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }
}