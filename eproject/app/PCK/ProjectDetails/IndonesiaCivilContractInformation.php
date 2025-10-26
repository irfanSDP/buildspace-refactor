<?php namespace PCK\ProjectDetails;

use Carbon\Carbon;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class IndonesiaCivilContractInformation extends Model {

    use TimestampFormatterTrait;

    protected $fillable = array(
        'commencement_date',
        'completion_date',
        'contract_sum',
        'pre_defined_location_code_id'
    );

    protected $table = 'indonesia_civil_contract_information';

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function preDefinedLocationCode()
    {
        return $this->belongsTo('PCK\Buildspace\PreDefinedLocationCode','pre_defined_location_code_id');
    }
    
    public function getCommencementDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
    }

    public function getCompletionDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
    }

}