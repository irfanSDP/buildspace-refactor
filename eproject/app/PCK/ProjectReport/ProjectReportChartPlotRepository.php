<?php namespace PCK\ProjectReport;

class ProjectReportChartPlotRepository
{
    public function getPlotSelections(ProjectReportChart $chart) {
        $selections = array();

        switch ($chart->chart_type) {
            case ProjectReportChart::TABLE_CHART:
                $selections = array(
                    ProjectReportChartPlot::TABLE_PLOT => $this->getTypeLabel(ProjectReportChartPlot::TABLE_PLOT)
                );
                break;

            case ProjectReportChart::GRAPH_CHART:
                $selections = array(
                    ProjectReportChartPlot::LINE_PLOT => $this->getTypeLabel(ProjectReportChartPlot::LINE_PLOT),
                    ProjectReportChartPlot::BAR_PLOT => $this->getTypeLabel(ProjectReportChartPlot::BAR_PLOT)
                );
                break;

            case ProjectReportChart::PIE_CHART:
                $selections = array(ProjectReportChartPlot::PIE_PLOT => $this->getTypeLabel(ProjectReportChartPlot::PIE_PLOT));
                break;

            case ProjectReportChart::DONUT_CHART:
                $selections = array(ProjectReportChartPlot::DONUT_PLOT => $this->getTypeLabel(ProjectReportChartPlot::DONUT_PLOT));
                break;

            default:
                $selections = array();
        }
        return $selections;
    }

    public function getCategorySelections(ProjectReportChart $chart) {
        $selections = array();
        $template = $chart->projectReportTypeMapping->projectReportTemplate;

        $categoryColumns = $template->columns()
            ->whereIn('type', array(
                ProjectReportColumn::COLUMN_DATE,
                ProjectReportColumn::COLUMN_PROJECT_PROGRESS,
                ProjectReportColumn::COLUMN_WORK_CATEGORY,
                //ProjectReportColumn::COLUMN_SYSTEM_PROJECT_STATUS,
            ))
            ->get();

        if (! empty($categoryColumns)) {
            foreach ($categoryColumns as $categoryColumn) {
                $selections[$categoryColumn->id] = $categoryColumn->getColumnTitle();
            }
        }
        return $selections;
    }

    public function getValueSelections($chartId, $categoryColumnId) {
        $selections = array();

        $chart = ProjectReportChart::where('id', $chartId)->first();
        $template = $chart->projectReportTypeMapping->projectReportTemplate;

        $column = ProjectReportColumn::where('id', $categoryColumnId)->first();

        switch ($column->type) {
            case ProjectReportColumn::COLUMN_DATE:
                $columnList = array(
                    /*ProjectReportColumn::COLUMN_SYSTEM_PROJECT_CONTRACT_SUM,
                    ProjectReportColumn::COLUMN_SYSTEM_PROJECT_TOTAL_WORK_DONE,
                    ProjectReportColumn::COLUMN_SYSTEM_PROJECT_BILL_TOTAL,
                    ProjectReportColumn::COLUMN_SYSTEM_PROJECT_VO_TOTAL,*/
                    ProjectReportColumn::COLUMN_NUMBER
                );
                break;

            case ProjectReportColumn::COLUMN_PROJECT_PROGRESS:
            case ProjectReportColumn::COLUMN_WORK_CATEGORY:
                $columnList = array(
                    //ProjectReportColumn::COLUMN_WORK_CATEGORY,
                    ProjectReportColumn::COLUMN_SYSTEM_PROJECT_TITLE,
                );
                break;

            default:
                $columnList = array();
        }

        $valueColumns = $template->columns()
            ->whereIn('type', $columnList)
            ->get();

        if (! empty($valueColumns)) {
            foreach ($valueColumns as $valueColumn) {
                $selections[$valueColumn->id] = $valueColumn->getColumnTitle();
            }
        }
        return $selections;
    }

    public function getAccumulativeOption($valueColumnId) {
        $column = ProjectReportColumn::where('id', $valueColumnId)->first();
        if (in_array($column->type, array(ProjectReportColumn::COLUMN_NUMBER))) {
            return true;
        } else {
            return false;
        }
    }

    public function getGroupingSelections($categoryColumnId) {
        $column = ProjectReportColumn::where('id', $categoryColumnId)->first();

        switch ($column->type) {
            case ProjectReportColumn::COLUMN_DATE:
                $selections = array(
                    ProjectReportChartPlot::GRP_MONTHLY => $this->getGroupLabel(ProjectReportChartPlot::GRP_MONTHLY),
                    ProjectReportChartPlot::GRP_QUARTERLY => $this->getGroupLabel(ProjectReportChartPlot::GRP_QUARTERLY),
                    ProjectReportChartPlot::GRP_YEARLY => $this->getGroupLabel(ProjectReportChartPlot::GRP_YEARLY)
                );
                break;

            case ProjectReportColumn::COLUMN_PROJECT_PROGRESS:
            case ProjectReportColumn::COLUMN_WORK_CATEGORY:
                $selections = array(
                    ProjectReportChartPlot::GRP_DEFAULT => $this->getGroupLabel(ProjectReportChartPlot::GRP_DEFAULT)
                );
                break;

            default:
                $selections = array();
        }
        return $selections;
    }

    public function getSelections(ProjectReportChart $chart)
    {
        return array(
            'plot_types' => $this->getPlotSelections($chart),
            'category_columns' => $this->getCategorySelections($chart)
        );
    }

    public static function getTypeLabel($plotType)
    {
        return ProjectReportChartPlot::getTypeLabel($plotType);
    }

    public static function getGroupLabel($plotType)
    {
        return ProjectReportChartPlot::getGroupLabel($plotType);
    }

    public function getTotalPlots($chartId)
    {
        return ProjectReportChartPlot::where('project_report_chart_id', $chartId)->count();
    }

    public function getAllRecords($chartId)
    {
        return ProjectReportChartPlot::where('project_report_chart_id', $chartId)->get();
    }

    public function getRecord($id)
    {
        return ProjectReportChartPlot::where('id', $id)->first();
    }

    public function createRecord($chartId, $inputs)
    {
        $record = new ProjectReportChartPlot();
        $record->project_report_chart_id = $chartId;
        $record->category_column_id = $inputs['categoryColumn'];
        $record->value_column_id = $inputs['valueColumn'];
        $record->plot_type = $inputs['plotType'];
        $record->data_grouping = $inputs['dataGrouping'];
        $record->is_accumulated = isset($inputs['isAccumulated']) ? $inputs['isAccumulated'] : 0;
        $record->save();

        return $record->id;
    }

    public function updateRecord($recordId, $inputs)
    {
        $record = $this->getRecord($recordId);
        $record->category_column_id = $inputs['categoryColumn'];
        $record->value_column_id = $inputs['valueColumn'];
        $record->plot_type = $inputs['plotType'];
        $record->data_grouping = $inputs['dataGrouping'];
        $record->is_accumulated = isset($inputs['isAccumulated']) ? $inputs['isAccumulated'] : 0;
        $record->save();

        return true;
    }

    public function syncRecords($recordId)
    {
        $record = $this->getRecord($recordId);

        $plots = ProjectReportChartPlot::where('id', '!=', $record->id)
            ->where('project_report_chart_id', $record->project_report_chart_id)
            ->get();

        if (count($plots) > 0) {    // Other plots for chart exist
            // Sync data grouping and category column with other plots in the same chart
            foreach ($plots as $plot) {
                $updateData = array();

                if ($plot->category_column_id != $record->category_column_id) {
                    $updateData['category_column_id'] = $record->category_column_id;
                }
                if ($plot->data_grouping != $record->data_grouping) {
                    $updateData['data_grouping'] = $record->data_grouping;
                }

                if (! empty($updateData)) {
                    $updateData['value_column_id'] = $record->value_column_id;
                    $updateData['is_accumulated'] = $record->is_accumulated;
                    ProjectReportChartPlot::where('id', $plot->id)->update($updateData);
                }
            }
        }
        return true;
    }

    public function deleteRecord($recordId) {
        $record = $this->getRecord($recordId);
        $record->delete();
        return true;
    }

    public function deleteAllRecords($chartId) {
        ProjectReportChartPlot::where('project_report_chart_id', $chartId)->delete();
        return true;
    }

}