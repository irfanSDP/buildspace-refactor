<?php namespace PCK\VendorPerformanceEvaluation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class VendorPerformanceEvaluationCompanyFormEvaluationLog extends Model
{
    protected $table = 'vendor_performance_evaluation_company_form_evaluation_logs';

    const SUBMITTED  = 1;
    const REJECTED   = 2;
    const VERIFIED   = 4;

    public function vendorPerformanceEvaluationCompanyForm()
    {
        return $this->belongsTo(VendorPerformanceEvaluationCompanyForm::class, 'vendor_performance_evaluation_company_form_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActionDescription()
    {
        $actions = [
            self::SUBMITTED  => trans('vendorPerformanceEvaluation.evaluationSubmitted'),
            self::REJECTED   => trans('vendorPerformanceEvaluation.evaluationRejected'),
            self::VERIFIED   => trans('vendorPerformanceEvaluation.evaluationVerified'),
        ];

        return $actions[$this->action_type];
    }

    public static function logAction(VendorPerformanceEvaluationCompanyForm $companyForm, $actionType)
    {
        $log                                                = new self();
        $log->vendor_performance_evaluation_company_form_id = $companyForm->id;
        $log->action_type                                   = $actionType;
        $log->created_by                                    = \Confide::user()->id;
        $log->updated_by                                    = \Confide::user()->id;
        $log->save();

        return self::find($log->id);
    }

    public static function getEvaluationLogs(VendorPerformanceEvaluationCompanyForm $companyForm)
    {
        $records = $companyForm->vendorPerformanceEvaluationCompanyFormEvaluationLogs()->orderBy('id', 'DESC')->get();

        $logs = [];

        foreach($records as $record)
        {
            array_push($logs, [
                'id'                 => $record->id,
                'created_by'         => $record->createdBy->name,
                'action'             => $record->getActionDescription(),
                'time_stamp'         => Carbon::parse($record->created_at)->format(\Config::get('dates.created_at')),
            ]);
        }

        return $logs;
    }
}