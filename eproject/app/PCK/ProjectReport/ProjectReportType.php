<?php namespace PCK\ProjectReport;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Users\User;

class ProjectReportType extends Model
{
    protected $table = 'project_report_types';

    protected static function boot()
    {
        parent::boot();

        static::created(function(self $model)
        {
            ProjectReportTypeMapping::initialize($model);
        });
    }

    public function mappings()
    {
        return $this->hasMany(ProjectReportTypeMapping::class, 'project_report_type_id');
    }

    public function lock()
    {
        if(!$this->is_locked)
        {
            $this->is_locked = true;
            $this->save();
        }
    }

    // only select mappings with bound templates
    public static function getProjectReportTypeWithMapping($projectTypeIdentifier)
    {
        return self::select(
            'project_report_types.id AS report_type_id',
            'project_report_types.title AS report_type_title',
            'mappings.id AS mapping_id',
            'mappings.project_type AS mapping_project_type',
            'mappings.latest_rev as mapping_latest_rev',
            'pr.id AS mapped_template_id',
            'pr.title AS mapped_template_title'
        )
        ->join('project_report_type_mappings AS mappings', 'mappings.project_report_type_id', '=', 'project_report_types.id')
        ->join('project_reports AS pr', 'pr.id', '=', 'mappings.project_report_id')
        ->where('mappings.project_type', '=', $projectTypeIdentifier)
        ->whereNotNull('mappings.project_report_id')
        ->orderBy('project_report_types.id', 'ASC')
        ->get();
    }

    // only select mappings with bound templates
    // where user has access
    public static function getUserAccessibleProjectReportTypes(Project $project, User $user, $projectTypeIdentifier, $permissionTypeList = array())
    {
        $query = self::select(
            'project_report_types.id AS report_type_id',
            'project_report_types.title AS report_type_title',
            'mappings.id AS mapping_id',
            'mappings.project_type AS mapping_project_type',
            'pr.id AS mapped_template_id',
            'pr.title AS mapped_template_title'
        )
        ->join('project_report_type_mappings AS mappings', 'mappings.project_report_type_id', '=', 'project_report_types.id')
        ->join('project_reports AS pr', 'pr.id', '=', 'mappings.project_report_id')
        ->join('project_report_user_permissions AS permissions', 'permissions.project_report_type_id', '=', 'project_report_types.id')
        ->where('mappings.project_type', '=', $projectTypeIdentifier)
        ->whereNotNull('mappings.project_report_id')
        ->where('permissions.project_id', $project->id)
        ->where('permissions.user_id', $user->id);

        if (! empty($permissionTypeList)) {
            $query->whereIn('permissions.identifier', $permissionTypeList);
        }
        return $query->orderBy('project_report_types.id', 'ASC')
            ->distinct()
            ->get();
    }
}