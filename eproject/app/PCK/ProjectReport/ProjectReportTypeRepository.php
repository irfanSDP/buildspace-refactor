<?php namespace PCK\ProjectReport;

use Illuminate\Support\Facades\DB;

class ProjectReportTypeRepository
{
    public function listMappings()
    {
        $records = ProjectReportType::orderBy('id', 'ASC')->get();
        $data    = [];

        foreach($records as $record)
        {
            $temp = [
                'id'           => $record->id,
                'title'        => $record->title,
                'is_locked'    => $record->is_locked,
                'route:show'   => route('projectReport.type.mapping.index', [$record->id]),
                'route:update' => route('projectReport.type.update', [$record->id]),
            ];

            if(!$record->is_locked)
            {
                $temp['route:delete'] = route('projectReport.type.delete', [$record->id]);
            }

            array_push($data, $temp);
        }

        return $data;
    }

    public function createNewMapping($title)
    {
        $reportType                    = new ProjectReportType();
        $reportType->title             = trim($title);
        $reportType->is_locked         = false;
        $reportType->save();

        return ProjectReportType::find($reportType->id);
    }

    public function updateMapping(ProjectReportType $reportType, $title)
    {
        $reportType->title = $title;
        $reportType->save();

        return ProjectReportType::find($reportType->id);
    }
}