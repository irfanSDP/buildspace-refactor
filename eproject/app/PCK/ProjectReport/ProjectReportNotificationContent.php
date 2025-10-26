<?php namespace PCK\ProjectReport;

use Illuminate\Database\Eloquent\Model;

class ProjectReportNotificationContent extends Model
{
    protected $table = 'project_report_notification_contents';

    public function projectReportNotification()
    {
        return $this->belongsTo('PCK\ProjectReport\ProjectReportNotification', 'project_report_notification_id');
    }
}