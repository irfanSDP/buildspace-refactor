<?php namespace PCK\ProjectReport;

use Illuminate\Database\Eloquent\Model;

class ProjectReportNotificationRecipient extends Model
{
    protected $table = 'project_report_notification_recipients';

    public function notification()
    {
        return $this->belongsTo('PCK\ProjectReport\ProjectReportNotification', 'project_report_notification_id');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public static function scopeWithNotificationQuery($query)
    {
        return $query->whereHas('notification', function($query) {
            $query->whereHas('project', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->whereHas('projectReportTypeMapping', function($query) {
                    $query->whereHas('projectReportType', function($query) {
                        // Add conditions if needed, otherwise leave empty
                    });
                });
             })
            ->whereHas('user', function($query) {
                // Add conditions if needed, otherwise leave empty
            });
    }
}