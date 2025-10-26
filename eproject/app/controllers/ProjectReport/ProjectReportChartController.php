<?php namespace ProjectReport;

use Illuminate\Http\Request;
use PCK\ProjectReport\ProjectReportChartRepository;
use PCK\ProjectReport\ProjectReportChartDataRepository;

class ProjectReportChartController extends \Controller
{
    private $chartRepository;
    private $chartDataRepository;

    public function __construct(
        ProjectReportChartRepository $chartRepository,
        ProjectReportChartDataRepository $chartDataRepository
    ) {
        $this->chartRepository = $chartRepository;
        $this->chartDataRepository = $chartDataRepository;
    }

    public function index()
    {
        return \View::make('project_report.chart.index');
    }

    public function getList()
    {
        $data = array();

        $records = $this->chartRepository->getAllRecords(null, true);

        foreach($records as $record)
        {
            $row = array(
                'id' => $record->id,
                'title' => $record->title,
                'chart_type' => $this->chartRepository->getLabel($record->chart_type),
                'report_type' => $record->projectReportTypeMapping->projectReportType->title,
                'route:show' =>route('projectReport.charts.show', array($record->id))
            );

            $data[] = $row;
        }

        return \Response::json($data);
    }

    public function getFiltersData()
    {
        $filters = array('year' => null, 'grouping' => null, 'project_id' => null, 'subsidiaries' => array());
        $request = \Request::instance();

        if ($request->has('filter_year')) {
            if (is_numeric($request->input('filter_year'))) {
                $filters['year'] = $request->input('filter_year');
            }
        }
        if ($request->has('filter_grouping')) {
            if (is_numeric($request->input('filter_grouping'))) {
                $filters['grouping'] = $request->input('filter_grouping');
            }
        }
        if ($request->has('filter_subsidiaries')) {
            $subsidiaries = $request->input('filter_subsidiaries');

            // Split the comma-separated string into an array and filter out empty values
            if (is_array($subsidiaries)) {
                $filters['subsidiaries'] = array_filter($subsidiaries, function($value) {
                    return !empty($value);
                });
            } else {
                $filters['subsidiaries'] = array_filter(explode(',', $subsidiaries), function($value) {
                    return !empty($value);
                });
            }
        }
        return $filters;
    }

    public function getFilters($chartId=null, $withSubsidiaries=false)
    {
        $filters = $this->getFiltersData();
        return $this->chartDataRepository->getFilters($chartId, $filters, $withSubsidiaries);
    }

    public function getSubsidiariesFilter($chartId)
    {
        $filters = $this->getFilters($chartId, true);
        return \Response::json($filters['options']['subsidiaries']);
    }

    public function getChartData($chartId)
    {
        $filters = $this->getFiltersData();
        return $this->chartDataRepository->getChartData($chartId, $filters);
    }

    public function show($chartId)
    {
        $record = $this->chartRepository->getRecord($chartId);
        $record->filters = $this->getFilters($record->id);
        $record->chart_type_label = strtolower($this->chartRepository->getLabel($record->chart_type));

        return \View::make('project_report.chart.show', compact('record'));
    }

    public function showAll()
    {
        $records = $this->chartRepository->getAllRecords(null, true);

        foreach ($records as $record) {
            $record->filters = $this->getFilters($record->id);
            $record->chart_type_label = strtolower($this->chartRepository->getLabel($record->chart_type));

            $record->chart_icon = $this->chartRepository->getIcon($record->chart_type);
        }

        return \View::make('project_report.chart.show_all', compact('records'));
    }
}