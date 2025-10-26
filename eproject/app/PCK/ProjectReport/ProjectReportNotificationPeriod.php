<?php namespace PCK\ProjectReport;

use Illuminate\Database\Eloquent\Model;

class ProjectReportNotificationPeriod extends Model
{
    protected $table = 'project_report_notification_periods';

    protected $fillable = ['project_report_notification_id', 'period_value', 'period_type'];

    const PERIOD_DAYS = 1;
    const PERIOD_WEEKS = 2;
    const PERIOD_MONTHS = 3;
    const PERIOD_YEARS = 4;

    public function projectReportNotification()
    {
        return $this->belongsTo('PCK\ProjectReport\ProjectReportNotification', 'project_report_notification_id');
    }

    public static function getTypeLabel($type)
    {
        switch ($type) {
            case self::PERIOD_DAYS:
                return trans('projectReportNotification.periodDays');

            case self::PERIOD_WEEKS:
                return trans('projectReportNotification.periodWeeks');

            case self::PERIOD_MONTHS:
                return trans('projectReportNotification.periodMonths');

            case self::PERIOD_YEARS:
                return trans('projectReportNotification.periodYears');

            default:
                throw new \Exception('Invalid notification period');
        }
    }
}