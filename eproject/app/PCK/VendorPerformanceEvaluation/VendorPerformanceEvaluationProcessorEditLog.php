<?php namespace PCK\VendorPerformanceEvaluation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class VendorPerformanceEvaluationProcessorEditLog extends Model
{
    protected $table = 'vendor_performance_evaluation_processor_edit_logs';

    public function vendorPerformanceEvaluationForm()
    {
        return $this->belongsTo(VendorPerformanceEvaluationCompanyForm::class, 'vendor_performance_evaluation_company_form_id');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processorEditDetails()
    {
        return $this->hasMany(VendorPerformanceEvaluationProcessorEditDetail::class, 'vendor_performance_evaluation_processor_edit_log_id');
    }

    public static function createLog(VendorPerformanceEvaluationCompanyForm $vendorPerformancEvaluationCompanyForm, array $editDetails)
    {
        $record = new self();
        $record->vendor_performance_evaluation_company_form_id = $vendorPerformancEvaluationCompanyForm->id;
        $record->user_id                                       = \Confide::user()->id;
        $record->save();

        $record = self::find($record->id);

        VendorPerformanceEvaluationProcessorEditDetail::createDetails($record, $editDetails);

        return $record;
    }

    public static function getEditLogs(VendorPerformanceEvaluationCompanyForm $form)
    {
        $records = self::where('vendor_performance_evaluation_company_form_id', $form->id)->orderBy('id', 'DESC')->get();

        $logs = [];

        foreach($records as $record)
        {
            array_push($logs, [
                'id'                 => $record->id,
                'edited_by'          => $record->editedBy->name,
                'time_stamp'         => Carbon::parse($record->created_at)->format(\Config::get('dates.created_at')),
                'route_edit_details' => route('vendorPerformanceEvaluation.processor.edit.details.get', [$form->id, $record->id]),
            ]);
        }

        return $logs;
    }
}