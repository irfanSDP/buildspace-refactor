<?php namespace PCK\ProjectReport;

use Illuminate\Database\Eloquent\Model;

class ProjectReportChartPlot extends Model
{
    protected $table = 'project_report_chart_plots';

    const LINE_PLOT = 1;
    const BAR_PLOT = 2;
    const PIE_PLOT = 3;
    const TABLE_PLOT = 4;
    const DONUT_PLOT = 5;
    const GRP_DAILY = 1;
    const GRP_MONTHLY = 2;
    const GRP_QUARTERLY = 3;
    const GRP_YEARLY = 4;
    const GRP_DEFAULT = 5;

    public function projectReportChart()
    {
        return $this->belongsTo('PCK\ProjectReport\ProjectReportChart', 'project_report_chart_id');
    }

    public function categoryColumn()
    {
        return $this->hasOne('PCK\ProjectReport\ProjectReportColumn', 'id', 'category_column_id');
    }

    public function valueColumn()
    {
        return $this->hasOne('PCK\ProjectReport\ProjectReportColumn', 'id', 'value_column_id');
    }

    public static function getTypeLabel($plotType)
    {
        switch($plotType)
        {
            case self::LINE_PLOT:
                $label = trans('projectReportChart.line');
                break;

            case self::BAR_PLOT:
                $label = trans('projectReportChart.bar');
                break;

            case self::PIE_PLOT:
                $label = trans('projectReportChart.pie');
                break;

            case self::DONUT_PLOT:
                $label = trans('projectReportChart.donut');
                break;

            case self::TABLE_PLOT:
                $label = trans('projectReportChart.table');
                break;

            default:
                throw new \Exception('Invalid plot type');
        }

        return $label;
    }

    public static function getGroupLabel($grouping)
    {
        switch($grouping)
        {
            case self::GRP_DAILY:
                $label = trans('projectReportChart.daily');
                break;

            case self::GRP_MONTHLY:
                $label = trans('projectReportChart.monthly');
                break;

            case self::GRP_QUARTERLY:
                $label = trans('projectReportChart.quarterly');
                break;

            case self::GRP_YEARLY:
                $label = trans('projectReportChart.yearly');
                break;

            case self::GRP_DEFAULT:
                $label = trans('projectReportChart.default');
                break;

            default:
                throw new \Exception('Invalid plot type');
        }

        return $label;
    }
}