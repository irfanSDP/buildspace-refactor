<?php namespace PCK\ProjectReport;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\ProjectReport\ProjectReportType;
use PCK\ProjectReport\ProjectReport;

class ProjectReportTypeMapping extends Model
{
    protected $table = 'project_report_type_mappings';

    public static function initialize(ProjectReportType $reportType)
    {
        foreach([Project::TYPE_MAIN_PROJECT, Project::TYPE_SUB_PACKAGE] as $projectType)
        {
            $record                         = new self;
            $record->project_report_type_id = $reportType->id;
            $record->project_type           = $projectType;
            $record->save();
        }
    }

    public function projectReportType()
    {
        return $this->belongsTo(ProjectReportType::class, 'project_report_type_id');
    }

    public function projectReportTemplate()
    {
        return $this->belongsTo(ProjectReport::class, 'project_report_id');
    }

    public function lock()
    {
        if (! $this->is_locked)
        {
            $this->is_locked = true;
            $this->save();
        }
    }

    public static function updateMappedTemplateToLatestRevision(ProjectReport $projectReport)
    {
        $ids = ProjectReport::where('root_id', $projectReport->root->id)
            ->where('status', ProjectReport::STATUS_COMPLETED)
            ->where('id', '!=', $projectReport->id)
            ->orderBy('revision', 'DESC')
            ->get()
            ->lists('id');

        $outdatedMappings = self::whereIn('project_report_id', $ids)->orderBy('id', 'ASC')->get();

        foreach($outdatedMappings as $outdatedMapping)
        {
            $outdatedMapping->project_report_id = $projectReport->id;
            $outdatedMapping->save();
        }
    }
}