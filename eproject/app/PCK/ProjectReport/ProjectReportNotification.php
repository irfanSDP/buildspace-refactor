<?php namespace PCK\ProjectReport;

use Illuminate\Database\Eloquent\Model;

class ProjectReportNotification extends Model
{
    protected $table = 'project_report_notifications';

    const TYPE_REMINDER = 1;

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project', 'project_id');
    }

    public function projectReportTypeMapping()
    {
        return $this->belongsTo('PCK\ProjectReport\ProjectReportTypeMapping', 'project_report_type_mapping_id');
    }

    public function categoryColumn()
    {
        return $this->belongsTo('PCK\ProjectReport\ProjectReportColumn', 'category_column_id');
    }

    public function periods()
    {
        return $this->hasMany('PCK\ProjectReport\ProjectReportNotificationPeriod', 'project_report_notification_id');
    }

    public function content()
    {
        return $this->hasOne('PCK\ProjectReport\ProjectReportNotificationContent', 'project_report_notification_id');
    }

    public static function getTypeLabel($type)
    {
        switch($type) {
            case self::TYPE_REMINDER:
                return trans('projectReportNotification.typeReminder');

            default:
                throw new \Exception('Invalid notification type');
        }
    }
}