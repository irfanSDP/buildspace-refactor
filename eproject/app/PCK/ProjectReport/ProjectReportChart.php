<?php namespace PCK\ProjectReport;

use Illuminate\Database\Eloquent\Model;

class ProjectReportChart extends Model
{
    protected $table = 'project_report_charts';

    const GRAPH_CHART = 1;
    const PIE_CHART = 2;
    const TABLE_CHART = 3;
    const DONUT_CHART = 4;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->order = self::max('order') + 1;
        });
    }

    public function projectReportTypeMapping()
    {
        return $this->belongsTo('PCK\ProjectReport\ProjectReportTypeMapping', 'project_report_type_mapping_id');
    }

    public function chartPlots()
    {
        return $this->hasMany('PCK\ProjectReport\ProjectReportChartPlot', 'project_report_chart_id');
    }

    public function getPlotGroup($label = false)
    {
        $firstPlot = $this->chartPlots()->first();

        if ($firstPlot) {
            if ($label) {
                return ProjectReportChartPlot::getGroupLabel($firstPlot->data_grouping);
            } else {
                return $firstPlot->data_grouping;
            }
        } else {
            if ($label) {
                return trans('general.none');
            } else {
                return null;
            }
        }
    }

    public static function getTypeLabel($chartType)
    {
        switch($chartType)
        {
            case self::GRAPH_CHART:
                $label = trans('projectReportChart.graph');
                break;

            case self::PIE_CHART:
                $label = trans('projectReportChart.pie');
                break;

            case self::DONUT_CHART:
                $label = trans('projectReportChart.donut');
                break;

            case self::TABLE_CHART:
                $label = trans('projectReportChart.table');
                break;

            default:
                throw new \Exception('Invalid chart type');
        }

        return $label;
    }

    public static function getTypeIcon($chartType)
    {
        switch($chartType)
        {
            case self::GRAPH_CHART:
                $icon = 'fa fa-chart-line';
                break;

            case self::PIE_CHART:
            case self::DONUT_CHART:
                $icon = 'fa fa-chart-pie';
                break;

            case self::TABLE_CHART:
                $icon = 'fa fa-table';
                break;

            default:
                $icon = '';
        }

        return $icon;
    }
}