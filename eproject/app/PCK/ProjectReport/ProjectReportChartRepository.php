<?php namespace PCK\ProjectReport;

class ProjectReportChartRepository
{
    public function getTypes()
    {
        return array(
            ProjectReportChart::TABLE_CHART => strtolower(trans('projectReportChart.table')),
            ProjectReportChart::GRAPH_CHART => strtolower(trans('projectReportChart.graph')),
            ProjectReportChart::PIE_CHART => strtolower(trans('projectReportChart.pie')),
            ProjectReportChart::DONUT_CHART => strtolower(trans('projectReportChart.donut')),
        );
    }

    public function getSelections()
    {
        $selections = array(
            ProjectReportChart::TABLE_CHART => trans('projectReportChart.table'),
            ProjectReportChart::GRAPH_CHART => trans('projectReportChart.graph'),
            ProjectReportChart::PIE_CHART => trans('projectReportChart.pie'),
            ProjectReportChart::DONUT_CHART => trans('projectReportChart.donut'),
        );
        //asort($selections);
        return $selections;
    }

    public static function getLabel($chartType)
    {
        return ProjectReportChart::getTypeLabel($chartType);
    }

    public static  function getIcon($chartType)
    {
        return ProjectReportChart::getTypeIcon($chartType);
    }

    public function getAllRecords($isLocked=null, $isPublished=null)
    {
        $query1 = ProjectReportChart::query();

        if (! is_null($isLocked)) {
            $query1->where('is_locked', (bool)$isLocked);
        }
        if (! is_null($isPublished)) {
            $query1->where('is_published', (bool)$isPublished);
        }

        return $query1->orderBy('order')->get();
    }

    public function getRecord($id)
    {
        return ProjectReportChart::where('id', $id)->first();
    }

    public function getRecordsByMappingId($mappingId)
    {
        return ProjectReportChart::where('project_report_type_mapping_id', $mappingId)->get();
    }

    public function createRecord($mappingId, $inputs)
    {
        $chartTitle = trim($inputs['title']);

        $record = new ProjectReportChart();
        $record->project_report_type_mapping_id = $mappingId;
        $record->chart_type = trim($inputs['chartType']);
        $record->title = ! empty($chartTitle) ? $chartTitle : self::getLabel($inputs['chartType']);
        $record->save();

        return $record->id;
    }

    public function updateRecord($recordId, $mappingId, $inputs)
    {
        $chartTitle = trim($inputs['title']);

        $record = $this->getRecord($recordId);

        //if (! $record->is_locked) {
            $record->project_report_type_mapping_id = $mappingId;
            $record->chart_type = trim($inputs['chartType']);
        //}
        $record->title = ! empty($chartTitle) ? $chartTitle : self::getLabel($inputs['chartType']);
        $record->save();

        return true;
    }

    public function deleteRecord($recordId) {
        $record = $this->getRecord($recordId);
        $record->delete();
        return true;
    }

    public function lockRecord($recordId, $lock=true) {
        $record = $this->getRecord($recordId);
        $record->is_locked = $lock;
        $record->save();

        return true;
    }

    public function publishRecord($recordId, $publish=true) {
        $record = $this->getRecord($recordId);
        /*if (! $record->is_locked) {
            return false;
        }*/

        $record->is_published = $publish;
        $record->save();

        return true;
    }

    public function updateOrder($recordId, $order) {
        ProjectReportChart::where('id', $recordId)->update(array('order' => $order));
        return true;
    }

}