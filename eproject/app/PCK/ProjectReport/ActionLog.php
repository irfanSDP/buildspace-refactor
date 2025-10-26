<?php namespace PCK\ProjectReport;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class ActionLog extends Model
{
    protected $table = 'project_report_action_logs';

    const CREATED_NEW_REVISION   = 1;
    const REPORT_SAVED           = 2;
    const SUBMITTED_FOR_APPROVAL = 4;

    public function projectReport()
    {
        return $this->belongsTo(ProjectReport::class, 'project_report_id');
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
            self::CREATED_NEW_REVISION   => trans('projectReport.createdNewRevsion'),
            self::REPORT_SAVED           => trans('projectReport.reportSaved'),
            self::SUBMITTED_FOR_APPROVAL => trans('projectReport.submittedForApproval'),
        ];

        return $actions[$this->action_type];
    }

    public static function logAction(ProjectReport $projectReport, $actionType)
    {
        $log                    = new self();
        $log->project_report_id = $projectReport->id;
        $log->action_type       = $actionType;
        $log->created_by        = \Confide::user()->id;
        $log->updated_by        = \Confide::user()->id;
        $log->save();

        return self::find($log->id);
    }
}
