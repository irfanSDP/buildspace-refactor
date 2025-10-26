<?php namespace PCK\ProjectReport;

use Illuminate\Support\Facades\DB;
use PCK\Projects\Project;

class ProjectReportTypeMappingRepository
{
    public function getTitle($mappingId)
    {
        $record = ProjectReportTypeMapping::find($mappingId);
        if (! $record) {
            return '';
        }
        $reportType = $record->projectReportType;
        if (! $reportType) {
            return '';
        }
        return $reportType->title;
    }

    public function list(ProjectReportType $reportType)
    {
        $records = $reportType->mappings()->orderBy('project_type', 'ASC')->get();
        $data    = array();

        foreach($records as $record)
        {
            $projectType = $record->project_type == Project::TYPE_MAIN_PROJECT ? trans('projects.mainProject') : trans('projects.subPackage');

            $rowData = array();
            $rowData['id'] = $record->id;
            $rowData['project_report_type_id'] = $record->projectReportType->id;
            $rowData['project_type'] = $projectType;
            $rowData['template_id'] = $record->project_report_id;
            $rowData['template_title'] = is_null($record->projectReportTemplate) ? 'N/A' : $record->projectReportTemplate->title;
            $rowData['show_latest_rev'] = ($record->latest_rev) ? trans('general.yes') : trans('general.no');
            $rowData['latest_rev'] = $record->latest_rev;

            if (! $record->is_locked) {
                $rowData['route:bind'] = route('projectReport.type.mapping.bind', array($reportType->id, $record->id));
                $rowData['route:toggle_latest_rev'] = route('projectReport.type.mapping.toggleLatestRev', array($reportType->id, $record->id));
                $rowData['route:lock'] = route('projectReport.type.mapping.lock', array($reportType->id, $record->id));
            }

            $data[] = $rowData;
        }

        return $data;
    }
}