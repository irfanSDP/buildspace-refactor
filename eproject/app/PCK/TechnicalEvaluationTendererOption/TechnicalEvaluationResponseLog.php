<?php namespace PCK\TechnicalEvaluationTendererOption;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Companies\Company;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference;

class TechnicalEvaluationResponseLog extends Model {

    protected $table = 'technical_evaluation_response_log';

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function setReference()
    {
        return $this->belongsTo('PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference', 'set_reference_id');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public static function logThis(TechnicalEvaluationSetReference $setReference, Company $company)
    {
        $entry = new self;

        $entry->user_id          = \Confide::user()->id;
        $entry->set_reference_id = $setReference->id;
        $entry->company_id       = $company->id;

        return $entry->save();
    }

    public static function getLog(TechnicalEvaluationSetReference $setReference, Company $company)
    {
        return self::where('set_reference_id', '=', $setReference->id)
            ->where('company_id', '=', $company->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function getTendererSubmissionTime(TechnicalEvaluationSetReference $setReference, Company $company)
    {
        $log = self::getLog($setReference, $company);

        foreach($log as $logEntry)
        {
            if( $logEntry->user->getAssignedCompany($setReference->project, $setReference->created_at)->id == $company->id ) return Carbon::parse($logEntry->created_at)->format(\Config::get('dates.created_and_updated_at_formatting'));
        }

        return null;
    }

}