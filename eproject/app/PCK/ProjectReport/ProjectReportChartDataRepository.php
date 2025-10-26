<?php namespace PCK\ProjectReport;

use Illuminate\Support\Facades\DB;
use PCK\Helpers\NumberHelper;
use PCK\ModulePermission\ModulePermissionRepository;
use PCK\Projects\Project;
use PCK\Subsidiaries\Subsidiary;

class ProjectReportChartDataRepository
{
    protected $chartData;
    protected $chart;
    protected $chartType;
    protected $mappingId;
    protected $latestRev;
    protected $grouping;
    protected $filters;
    protected $projectReportQueryData;
    protected $categoryCol;
    protected $valueCol;
    protected $accumulatedTotalValue;
    protected $hasAccumulatedValue;
    protected $projectIds;
    protected $subsidiaries;

    private $columnRepository;
    private $plotRepository;
    private $modulePermissionRepository;

    public function __construct(
        ProjectReportColumnRepository $columnRepository,
        ProjectReportChartPlotRepository $plotRepository,
        ModulePermissionRepository $modulePermissionRepository
    ) {
        $this->columnRepository = $columnRepository;
        $this->plotRepository = $plotRepository;
        $this->modulePermissionRepository = $modulePermissionRepository;
    }

    public function getFilters($chartId, $filters, $withSubsidiaries=false) {
        $this->filters = $filters;

        $this->chart = ProjectReportChart::where('id', $chartId)->first();
        $this->chartType = $this->chart->chart_type;

        $this->mappingId = $this->chart->project_report_type_mapping_id;
        $mapping = ProjectReportTypeMapping::where('id', $this->mappingId)->first();
        $this->latestRev = $mapping->latest_rev;

        $firstPlot = $this->chart->chartPlots->first();
        $this->categoryCol = $firstPlot->categoryColumn;
        $this->valueCol = $firstPlot->valueColumn;

        $subsidiariesList = $this->getSubsidiariesList();
        $this->subsidiaries = $subsidiariesList['selectedSubsidiaries'];

        $this->projectReportQueryData = array(
            'mapping_id' => $this->mappingId,
            'subsidiaries' => $this->subsidiaries
        );

        if ($withSubsidiaries) {
            $this->filters['options']['subsidiaries'] = $subsidiariesList['subsidiaries'];
            $this->filters['subsidiaries'] = $subsidiariesList['selectedSubsidiaries'];
        }

        if (empty($this->filters['grouping'])) {
            $this->grouping = $firstPlot->data_grouping;
            $this->filters['grouping'] = $this->grouping;
        } else {
            $this->grouping = $this->filters['grouping'];
        }
        $this->filters['options']['grouping'] = $this->plotRepository->getGroupingSelections($this->categoryCol->id);

        if (empty($this->filters['year'])) {
            $now = new \DateTime();
            $this->filters['year'] = $now->format('Y');
        }

        switch ($this->categoryCol->type) {
            case ProjectReportColumn::COLUMN_DATE:
                switch ($this->grouping) {
                    case ProjectReportChartPlot::GRP_MONTHLY:
                    case ProjectReportChartPlot::GRP_QUARTERLY:
                    case ProjectReportChartPlot::GRP_YEARLY:
                        $this->filters['options']['year'] = $this->getYears();
                        break;

                    default:
                        // Do nothing
                }
                break;

            default:
                // Do nothing
        }

        return $this->filters;
    }

    public function getChartData($chartId, $filters) {
        $this->filters = $this->getFilters($chartId, $filters, true);

        $this->projectIds = $this->getProjectIds();

        $this->chartData = array(
            'id' => $this->chart->id,
            'title' => $this->chart->title,
        );
        switch ($this->chartType) {
            case ProjectReportChart::TABLE_CHART:
                $this->chartData['view'] = 'table';
                $this->chartData['chart_type'] = 'table';
                $this->chartData['data'] = $this->getData();
                $this->chartData['options'] = $this->getHeaders();
                break;

            case ProjectReportChart::GRAPH_CHART:
                $this->chartData['view'] = 'graph';
                $this->chartData['chart_type'] = 'graph';
                $data = $this->getData();
                $this->chartData['data'] = $data['series'];
                $this->chartData['options'] = array(
                    'chart' => array(
                        'height' => 350,
                        'type' => 'line',
                    ),
                    'stroke' => array(
                        'width' => $data['stroke_width'],
                    ),
                    'markers' => array(
                        'size' => 0,
                    ),
                    'title' => array(
                        'text' => $this->chart->title,
                    ),
                    'dataLabels' => array(
                        'enabled' => $data['data_labels']['enabled'],
                        'enabledOnSeries' => $data['data_labels']['series'],
                    ),
                    'xaxis' => array(
                        'categories' => $this->getHeaders(),
                    ),
                );
                break;

            case ProjectReportChart::PIE_CHART:
            case ProjectReportChart::DONUT_CHART:
                $this->chartData['view'] = 'pie';
                $this->chartData['data'] = $this->getData();
                $this->chartData['options'] = array(
                    'chart' => array(
                        'width' => '100%',
                        'type' => null,
                    ),
                    'title' => array(
                        'text' => $this->chart->title,
                    ),
                    'labels' => $this->getHeaders(),
                );
                if ($this->chartType === ProjectReportChart::PIE_CHART) {
                    $this->chartData['chart_type'] = 'pie';
                    $this->chartData['options']['chart']['type'] = 'pie';
                } else {
                    $this->chartData['chart_type'] = 'donut';
                    $this->chartData['options']['chart']['type'] = 'donut';
                }
                break;

            default:
                $this->chartData['view'] = 'unknown';
                $this->chartData['chart_type'] = 'unknown';
        }

        return $this->chartData;
    }

    private function getHeaders() {
        switch ($this->chartType) {
            case ProjectReportChart::TABLE_CHART:
                return $this->generateTableHeaders();
            case ProjectReportChart::GRAPH_CHART:
                return $this->generateGraphLabels();
            case ProjectReportChart::PIE_CHART:
            case ProjectReportChart::DONUT_CHART:
                return $this->generatePieLabels();
            default:
                return array();
        }
    }

    private function getData() {
        switch ($this->chartType) {
            case ProjectReportChart::TABLE_CHART:
                return $this->generateTableRows();
            case ProjectReportChart::GRAPH_CHART:
                $graphData = array(
                    'series' => $this->generateGraphPlots(),
                    'stroke_width' => array(),
                    'data_labels' => array(
                        'enabled' => false,
                        'series' => array()
                    ),
                );
                foreach ($graphData['series'] as $key => $dataSet) {
                    switch ($dataSet['type']) {
                        case 'line':
                            $graphData['stroke_width'][] = 4;
                            break;
                        case 'column':
                            $graphData['stroke_width'][] = 0;
                            break;
                        default:
                            // Do nothing
                    }
                    $graphData['data_labels']['series'][] = $key;
                }
                return $graphData;
            case ProjectReportChart::PIE_CHART:
            case ProjectReportChart::DONUT_CHART:
                return $this->generatePieSlices();
            default:
                return array();
        }
    }

    private function getTitle($column) {
        switch ($column->type) {
            case ProjectReportColumn::COLUMN_SYSTEM_PROJECT_TITLE:
                $title = trans('projects.projects');
                break;

            default:
                $title = $column->title;
        }
        return $title;
    }

    private function getMonths() {
        return array(trans('dates.january'), trans('dates.february'), trans('dates.march'),
            trans('dates.april'), trans('dates.may'), trans('dates.june'),
            trans('dates.july'), trans('dates.august'), trans('dates.september'),
            trans('dates.october'), trans('dates.november'), trans('dates.december')
        );
    }

    private function getQuarters() {
        return array(trans('dates.quarter1'), trans('dates.quarter2'), trans('dates.quarter3'), trans('dates.quarter4'));
    }

    private function getYears() {
        $years = ProjectReportColumn::where('reference_id', $this->categoryCol->reference_id)
            ->withProjectReportQueryForCharts($this->projectReportQueryData)
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM TO_DATE(content, \'YYYY-MM-DD\')) AS year')
            ->whereNotNull('content')
            ->orderBy('year', 'asc')
            ->get();

        if (count($years) > 0) {
            return $years->lists('year');
        } else {
            return array();
        }
    }

    private function getProjectProgressList() {
        $groupings = array();
        $selections = $this->columnRepository->getProjectProgressSelections();
        foreach (array_keys($selections) as $indexName) {
            $groupings[] = $indexName;
        }
        return $groupings;
    }

    private function getWorkCategories() {
        $groupings = array();
        $uniqueIds = array();
        $columns = ProjectReportColumn::where('reference_id', $this->categoryCol->reference_id)
            ->withProjectReportQueryForCharts($this->projectReportQueryData)
            ->distinct('project_report_id')
            ->get();

        if ($this->latestRev) {
            $columns = $columns->filter(function($column) {
                $latestApprovedProjectReport = ProjectReport::latestApprovedProjectReport($column->projectReport->project, $column->projectReport->projectReportTypeMapping);
                return $column->project_report_id === $latestApprovedProjectReport->id;
            });
        }

        foreach ($columns as $column) {
            $workCategory = $column->projectReport->project->workCategory;
            if (! in_array($workCategory->id, $uniqueIds)) {
                $uniqueIds[] = $workCategory->id;
                $groupings[] = array('id' => $workCategory->id, 'name' => $workCategory->name);
            }
        }
        return $groupings;
    }

    private function getContentsList() {
        $contents = ProjectReportColumn::where('reference_id', $this->categoryCol->reference_id)
            ->withProjectReportQueryForCharts($this->projectReportQueryData)
            ->selectRaw('DISTINCT content AS content')
            ->orderBy('content', 'asc')
            ->get();

        if (count($contents) > 0) {
            return $contents->lists('content');
        } else {
            return array();
        }
    }

    private function getSubsidiariesList() {
        // Fetch the PDO instance and prepare the query
        $dbh = \DB::getPdo();
        $stmt = $dbh->prepare("WITH RECURSIVE tree AS (
                SELECT id, ARRAY[]::integer[] AS path
                FROM subsidiaries
                WHERE parent_id IS NULL
                UNION ALL
                SELECT subsidiaries.id, subsidiaries.id || tree.path || subsidiaries.parent_id
                FROM subsidiaries, tree
                WHERE subsidiaries.parent_id = tree.id
            )
            SELECT DISTINCT
                CASE WHEN (btrim(tree.path::text, '{}') IS NULL OR btrim(tree.path::text, '{}') = '') THEN subsidiaries.id::text ELSE btrim(tree.path::text, '{}') END AS path
            FROM tree
                     JOIN subsidiaries ON subsidiaries.id = tree.id
                     JOIN projects on projects.subsidiary_id = subsidiaries.id
                     JOIN states ON states.id = projects.state_id
                     JOIN companies ON companies.id = subsidiaries.company_id
                     JOIN project_reports ON project_reports.project_id = projects.id
                     JOIN project_report_charts ON project_report_charts.project_report_type_mapping_id = project_reports.project_report_type_mapping_id
                WHERE project_report_charts.id = :chart_id
                AND project_reports.status = :status
                AND project_reports.approved_date IS NOT NULL
                AND projects.deleted_at IS NULL");

        // Execute the query with the parameter
        $stmt->execute(array('chart_id' => $this->chart->id, 'status' => ProjectReport::STATUS_COMPLETED));

        // Fetch the results
        $subsidiaryIdRows = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        // Initialize an array to store subsidiary IDs
        $subsidiaryIds = array();

        // Process the rows and merge the IDs
        foreach($subsidiaryIdRows as $subsidiaryIdRow) {
            if ($subsidiaryIdRow) {
                $subsidiaryIds = array_merge($subsidiaryIds, explode(',', $subsidiaryIdRow));
            }
        }

        // Get the current user's module permission for subsidiaries
        $userSubsidiaryIds = $this->modulePermissionRepository->getUserSubsidiaryIds($this->modulePermissionRepository->getModuleId('project_report_charts'));

        // Filter subsidiary IDs to include only those the user has permission for
        $subsidiaryIds = array_intersect($subsidiaryIds, $userSubsidiaryIds);

        if (! empty($this->filters['subsidiaries'])) {  // Has selected subsidiaries
            // Filter to include only selected subsidiaries (which user has permission for)
            $selectedSubsidiaries = array_intersect($this->filters['subsidiaries'], $subsidiaryIds);
        } else {    // Has not selected any subsidiaries
            // Default to select all (which user has permission for)
            $selectedSubsidiaries = $subsidiaryIds;
        }

        $subsidiaries = array();
        if (! empty($subsidiaryIds)) {
            $subsidiaryIds = array_unique($subsidiaryIds);

            $subsidiaries = Subsidiary::join('companies AS c', 'c.id', '=', 'subsidiaries.company_id')
                ->whereIn('subsidiaries.id', $subsidiaryIds)
                ->select('subsidiaries.id', 'subsidiaries.name', 'subsidiaries.parent_id', 'c.id AS company_id', 'c.name AS company_name')
                ->groupBy('subsidiaries.id')
                ->groupBy('c.id')
                ->orderBy('subsidiaries.parent_id', 'DESC')
                ->get()
                ->toArray();

            $subsidiaries = $this->transformTree($subsidiaries);

            foreach ($subsidiaries as $key => $subsidiary) {
                if (in_array($subsidiary['id'], $selectedSubsidiaries)) {
                    $subsidiaries[$key]['selected'] = true;
                } else {
                    $subsidiaries[$key]['selected'] = false;
                }
            }
        }
        return array(
            'subsidiaries' => $subsidiaries,
            'selectedSubsidiaries' => $selectedSubsidiaries
        );
    }

    private function getProjectIds() {
        $projectIds = array();

        $columns = ProjectReportColumn::where('reference_id', $this->categoryCol->reference_id)
            ->withProjectReportQueryForCharts($this->projectReportQueryData)
            ->distinct('project_report_id')
            ->get();

        foreach ($columns as $column) {
            $project = $column->projectReport->project;
            if (! in_array($project->id, $projectIds)) {
                $projectIds[] = $project->id;
            }
        }
        return $projectIds;
    }

    private function transformTree($treeArray, $parentId=null) {
        $output = array();

        foreach ($treeArray as $node) {
            if ($node['parent_id'] == $parentId) {
                $children = $this->transformTree($treeArray, $node['id']);

                if ($children) {
                    $node['_children'] = $children;
                }
                $output[] = $node;

                unset($node);
            }
        }

        return $output;
    }

    private function generateTableHeaders() {
        $headers = array();
        $baseHeader = array(
            'title' => '',
            'field' => 'valueColumnTitle',
            'cssClass' => 'text-center text-middle',
            'headerSort' => false,
            'headerFilter' => 'input',
            'headerFilterPlaceholder' => trans('general.filter'),
            'width' => 160,
        );

        $headers[] = $baseHeader;

        switch ($this->categoryCol->type) {
            case ProjectReportColumn::COLUMN_DATE:
                switch ($this->grouping) {
                    case ProjectReportChartPlot::GRP_MONTHLY:
                        $groupings = $this->getMonths();
                        break;
                    case ProjectReportChartPlot::GRP_QUARTERLY:
                        $groupings = $this->getQuarters();
                        break;
                    case ProjectReportChartPlot::GRP_YEARLY:
                        $groupings = $this->getYears();
                        break;

                    default:
                        // Do nothing
                }
                break;

            case ProjectReportColumn::COLUMN_PROJECT_PROGRESS:
                $progressStatuses = $this->getProjectProgressList();
                if (count($progressStatuses) > 0) {
                    $groupings = array();
                    foreach ($progressStatuses as $progressStatus) {
                        $groupings[] = ProjectReportColumn::getProjectProgressLabel($progressStatus);
                    }
                }
                break;

            case ProjectReportColumn::COLUMN_WORK_CATEGORY:
                $workCategories = $this->getWorkCategories();
                if (count($workCategories) > 0) {
                    $groupings = array();
                    foreach ($workCategories as $workCategory) {
                        $groupings[] = $workCategory['name'];
                    }
                }
                break;

            default:
                // Do nothing
        }

        if (! empty($groupings)) {
            foreach ($groupings as $key => $grouping) {
                $headers[] = array(
                    'title' => $grouping,
                    'field' => 'grp_' . ($key + 1),
                    'cssClass' => 'text-center text-middle',
                    'hozAlign' => 'center',
                    'headerSort' => false
                );
            }
        }
        return $headers;
    }

    private function generateTableRows() {
        $plots = $this->chart->chartPlots;
        $rows = array();

        foreach ($plots as $plot) {
            $valueCol = $plot->valueColumn;
            $row = array('valueColumnTitle' => $this->getTitle($valueCol));
            if ($plot->is_accumulated) {
                $row['valueColumnTitle'] .= ' (' . trans('projectReportChart.accumulative') . ')';
            }
            $this->accumulatedTotalValue = 0;

            switch ($this->categoryCol->type) {
                case ProjectReportColumn::COLUMN_DATE:
                    switch ($this->grouping) {
                        case ProjectReportChartPlot::GRP_MONTHLY:
                            $groupings = $this->getMonths();
                            break;

                        case ProjectReportChartPlot::GRP_QUARTERLY:
                            $groupings = $this->getQuarters();
                            break;

                        case ProjectReportChartPlot::GRP_YEARLY:
                            $groupings = $this->getYears();
                            break;

                        default:
                            $groupings = array();
                    }
                    foreach ($groupings as $index => $grouping) {
                        $groupIndex = $index + 1;
                        $field = 'grp_' . $groupIndex;

                        $categoryColumns = $this->handleDateCategory($groupIndex, $grouping);

                        switch ($valueCol->type) {
                            case ProjectReportColumn::COLUMN_NUMBER:
                                $row["$field"] = NumberHelper::formatNumber($this->handleNumberValue($categoryColumns, $valueCol->reference_id, $plot->is_accumulated));
                                break;

                            default:
                                // Do nothing
                        }
                    }
                    break;

                case ProjectReportColumn::COLUMN_PROJECT_PROGRESS:
                    $groupings = $this->getProjectProgressList();
                    foreach ($groupings as $index => $grouping) {
                        $groupIndex = $index + 1;
                        $field = 'grp_' . $groupIndex;

                        $row["$field"] = $this->handleProjectProgress($grouping);
                    }
                    break;

                case ProjectReportColumn::COLUMN_WORK_CATEGORY:
                    $groupings = $this->getWorkCategories();
                    foreach ($groupings as $index => $grouping) {
                        $groupIndex = $index + 1;
                        $field = 'grp_' . $groupIndex;

                        $row["$field"] = $this->handleWorkCategory($grouping['id']);
                    }
                    break;

                default:
                    return array();
            }
            $rows[] = $row;
        }
        return array_values($rows); // Reindex array to ensure proper JSON format if necessary
    }

    private function generateGraphLabels() {
        switch ($this->categoryCol->type) {
            case ProjectReportColumn::COLUMN_DATE:
                switch ($this->grouping) {
                    case ProjectReportChartPlot::GRP_MONTHLY:
                        $groupings = $this->getMonths();
                        break;
                    case ProjectReportChartPlot::GRP_QUARTERLY:
                        $groupings = $this->getQuarters();
                        break;
                    case ProjectReportChartPlot::GRP_YEARLY:
                        $groupings = $this->getYears();
                        break;

                    default:
                        // Do nothing
                }
                break;

            case ProjectReportColumn::COLUMN_PROJECT_PROGRESS:
                $progressStatuses = $this->getProjectProgressList();
                if (count($progressStatuses) > 0) {
                    $groupings = array();
                    foreach ($progressStatuses as $progressStatus) {
                        $groupings[] = ProjectReportColumn::getProjectProgressLabel($progressStatus);
                    }
                }
                break;

            case ProjectReportColumn::COLUMN_WORK_CATEGORY:
                $workCategories = $this->getWorkCategories();
                if (count($workCategories) > 0) {
                    foreach ($workCategories as $workCategory) {
                        $groupings[] = $workCategory['name'];
                    }
                }
                break;

            default:
                // Do nothing
        }

        if (! empty($groupings)) {
            $labels = array();
            foreach ($groupings as $key => $grouping) {
                $labels[] = $grouping;
            }
            return $labels;
        } else {
            return array();
        }
    }

    private function generateGraphPlots() {
        $plots = $this->chart->chartPlots;
        $series = array();

        foreach ($plots as $plot) {
            $this->accumulatedTotalValue = 0;

            $valueCol = $plot->valueColumn;
            $data = array('name' => $this->getTitle($valueCol), 'data' => array());
            if ($plot->is_accumulated) {
                $data['name'] .= ' (' . trans('projectReportChart.accumulative') . ')';
            }
            switch ($plot->plot_type) {
                case ProjectReportChartPlot::LINE_PLOT:
                    $data['type'] = 'line';
                    break;

                case ProjectReportChartPlot::BAR_PLOT:
                    $data['type'] = 'column';
                    break;

                default:
                    $data['type'] = 'unknown';
            }

            switch ($this->categoryCol->type) {
                case ProjectReportColumn::COLUMN_DATE:
                    switch ($this->grouping) {
                        case ProjectReportChartPlot::GRP_MONTHLY:
                            $groupings = $this->getMonths();
                            break;

                        case ProjectReportChartPlot::GRP_QUARTERLY:
                            $groupings = $this->getQuarters();
                            break;

                        case ProjectReportChartPlot::GRP_YEARLY:
                            $groupings = $this->getYears();
                            break;

                        default:
                            $groupings = array();
                    }
                    foreach ($groupings as $index => $grouping) {
                        $groupIndex = $index + 1;

                        $categoryColumns = $this->handleDateCategory($groupIndex, $grouping);

                        switch ($valueCol->type) {
                            case ProjectReportColumn::COLUMN_NUMBER:
                                $data['data'][] = $this->handleNumberValue($categoryColumns, $valueCol->reference_id, $plot->is_accumulated);
                                break;

                            default:
                                // Do nothing
                        }
                    }
                    break;

                case ProjectReportColumn::COLUMN_PROJECT_PROGRESS:
                    $groupings = $this->getProjectProgressList();
                    foreach ($groupings as $index => $grouping) {
                        $data['data'][] = $this->handleProjectProgress($grouping);
                    }
                    break;

                case ProjectReportColumn::COLUMN_WORK_CATEGORY:
                    $groupings = $this->getWorkCategories();
                    foreach ($groupings as $index => $grouping) {
                        $data['data'][] = $this->handleWorkCategory($grouping['id']);
                    }
                    break;

                default:
                    return array();
            }
            $series[] = $data;
        }

        return array_values($series); // Reindex array to ensure proper JSON format if necessary
    }

    private function generatePieLabels() {
        switch ($this->categoryCol->type) {
            case ProjectReportColumn::COLUMN_DATE:
                switch ($this->grouping) {
                    case ProjectReportChartPlot::GRP_MONTHLY:
                        $groupings = $this->getMonths();
                        break;
                    case ProjectReportChartPlot::GRP_QUARTERLY:
                        $groupings = $this->getQuarters();
                        break;
                    case ProjectReportChartPlot::GRP_YEARLY:
                        $groupings = $this->getYears();
                        break;

                    default:
                        // Do nothing
                }
                break;

            case ProjectReportColumn::COLUMN_PROJECT_PROGRESS:
                $groupings = $this->columnRepository->getProjectProgressSelections();
                break;

            case ProjectReportColumn::COLUMN_WORK_CATEGORY:
                $workCategories = $this->getWorkCategories();
                if (count($workCategories) > 0) {
                    foreach ($workCategories as $workCategory) {
                        $groupings[] = $workCategory['name'];
                    }
                }
                break;

            default:
                // Do nothing
        }

        if (! empty($groupings)) {
            $labels = array();
            foreach ($groupings as $key => $grouping) {
                $labels[] = $grouping;
            }
            return $labels;
        } else {
            return array();
        }
    }

    private function generatePieSlices() {
        $plots = $this->chart->chartPlots;
        $plot = $plots->first();
        $series = array();

        $this->accumulatedTotalValue = 0;

        $valueCol = $plot->valueColumn;

        switch ($this->categoryCol->type) {
            case ProjectReportColumn::COLUMN_DATE:
                switch ($this->grouping) {
                    case ProjectReportChartPlot::GRP_MONTHLY:
                        $groupings = $this->getMonths();
                        break;

                    case ProjectReportChartPlot::GRP_QUARTERLY:
                        $groupings = $this->getQuarters();
                        break;

                    case ProjectReportChartPlot::GRP_YEARLY:
                        $groupings = $this->getYears();
                        break;

                    default:
                        $groupings = array();
                }
                foreach ($groupings as $index => $grouping) {
                    $groupIndex = $index + 1;

                    $categoryColumns = $this->handleDateCategory($groupIndex, $grouping);

                    switch ($valueCol->type) {
                        case ProjectReportColumn::COLUMN_NUMBER:
                            $series[] = $this->handleNumberValue($categoryColumns, $valueCol->reference_id, $plot->is_accumulated);
                            break;

                        default:
                            // Do nothing
                    }
                }
                break;

            case ProjectReportColumn::COLUMN_PROJECT_PROGRESS:
                $groupings = $this->getProjectProgressList();
                foreach ($groupings as $index => $grouping) {
                    $count = $this->handleProjectProgress($grouping);
                    $series[] = $count;
                }
                break;

            case ProjectReportColumn::COLUMN_WORK_CATEGORY:
                $groupings = $this->getWorkCategories();
                foreach ($groupings as $index => $grouping) {
                    $count = $this->handleWorkCategory($grouping['id']);
                    $series[] = $count;
                }
                break;

            default:
                return array();
        }
        return array_values($series); // Reindex array to ensure proper JSON format if necessary
    }

    private function handleDateCategory($groupIndex, $grouping) {
        switch ($this->grouping) {
            case ProjectReportChartPlot::GRP_MONTHLY:
                return ProjectReportColumn::where('reference_id', $this->categoryCol->reference_id)
                    ->whereRaw('EXTRACT(MONTH FROM TO_DATE(content, \'YYYY-MM-DD\')) = EXTRACT(MONTH FROM TO_DATE(?, \'Month\')) AND EXTRACT(YEAR FROM TO_DATE(content, \'YYYY-MM-DD\')) = ?', array($grouping, $this->filters['year']))
                    ->withProjectReportQueryForCharts($this->projectReportQueryData)
                    ->get();

            case ProjectReportChartPlot::GRP_QUARTERLY:
                return ProjectReportColumn::where('reference_id', $this->categoryCol->reference_id)
                    ->whereRaw('EXTRACT(QUARTER FROM TO_DATE(content, \'YYYY-MM-DD\')) = ? AND EXTRACT(YEAR FROM TO_DATE(content, \'YYYY-MM-DD\')) = ?', array($groupIndex, $this->filters['year']))
                    ->withProjectReportQueryForCharts($this->projectReportQueryData)
                    ->get();

            case ProjectReportChartPlot::GRP_YEARLY:
                return ProjectReportColumn::where('reference_id', $this->categoryCol->reference_id)
                    ->whereRaw('EXTRACT(YEAR FROM TO_DATE(content, \'YYYY-MM-DD\')) = ?', array($grouping))
                    ->withProjectReportQueryForCharts($this->projectReportQueryData)
                    ->get();
        }
    }

    private function handleWorkCategory($workCategoryId) {
        return Project::whereIn('id', $this->projectIds)->where('work_category_id', $workCategoryId)->count();
    }

    private function handleProjectProgress($progressStatus) {
        return $this->handleTextValue(strtolower($progressStatus));
    }

    private function handleTextValue($text) {
        $query = ProjectReportColumn::where('reference_id', $this->categoryCol->reference_id)
            ->where('content', $text)
            ->withProjectReportQueryForCharts($this->projectReportQueryData);

        if ($this->latestRev) {
            $count = 0;
            $columns = $query->get();

            foreach ($columns as $column) {
                $latestApprovedProjectReport = ProjectReport::latestApprovedProjectReport($column->projectReport->project, $column->projectReport->projectReportTypeMapping);

                if ($column->project_report_id === $latestApprovedProjectReport->id) {
                    $count++;
                }
            }
            return $count;
        } else {
            return $query->count();
        }
    }

    private function handleNumberValue($categoryColumns, $referenceId, $isAccumulated) {
        $totalVal = 0;

        foreach ($categoryColumns as $categoryColumn) {
            $valCol = ProjectReportColumn::where('reference_id', $referenceId)->where('project_report_id', $categoryColumn->project_report_id)->first();

            if ($this->latestRev) {
                $latestApprovedProjectReport = ProjectReport::latestApprovedProjectReport($categoryColumn->projectReport->project, $categoryColumn->projectReport->projectReportTypeMapping);

                if ($valCol->project_report_id !== $latestApprovedProjectReport->id) {
                    continue;
                }
            }

            $totalVal = $totalVal + (float)$valCol->content;
        }

        if ($isAccumulated) {
            if (! $this->hasAccumulatedValue) {
                $this->hasAccumulatedValue = true;
            }
            $this->accumulatedTotalValue += $totalVal;
            return $this->accumulatedTotalValue;
        } else {
            return $totalVal;
        }
    }

}