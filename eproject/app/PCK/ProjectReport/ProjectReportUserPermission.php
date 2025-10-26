<?php namespace PCK\ProjectReport;

use Illuminate\Database\Eloquent\Model;
use PCK\Statuses\FormStatus;
use PCK\Projects\Project;
use PCK\Users\User;

class ProjectReportUserPermission extends Model
{
    protected $table = 'project_report_user_permissions';

    const IDENTIFIER_SUBMIT_REPORT = 1;
    const IDENTIFIER_VERIFY_REPORT = 2;

    const IDENTIFIER_EDIT_REMINDER = 3;
    const IDENTIFIER_RECEIVE_REMINDER = 4;

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function projectReportType()
    {
        return $this->belongsTo(ProjectReportType::class);
    }

    public static function getTypeDescriptions($identifier = null)
    {
        $types = [
            self::IDENTIFIER_SUBMIT_REPORT => trans('projectReport.submitProjectReport'),
            self::IDENTIFIER_VERIFY_REPORT => trans('projectReport.verifyProjectReport'),
            self::IDENTIFIER_EDIT_REMINDER => trans('projectReportNotification.editReminder'),
            self::IDENTIFIER_RECEIVE_REMINDER => trans('projectReportNotification.receiveReminder'),
        ];

        return is_null($identifier) ? $types : $types[$identifier];
    }

    protected static function getRecord(Project $project, User $user, $projectReportTypeId, $identifier)
    {
        return static::where('project_id', $project->id)
            ->where('user_id', '=', $user->id)
            ->where('identifier', '=', $identifier)
            ->where('project_report_type_id', '=', $projectReportTypeId)
            ->first();
    }

    public static function hasPermission(Project $project, User $user, $projectReportTypeId, $identifier)
    {
        return self::getRecord($project, $user, $projectReportTypeId, $identifier) ? true : false;
    }

    public static function getLisOfUsersByIdentifier(Project $project, $projectReportTypeId, $identifier)
    {
        $userIds = self::where('project_id', $project->id)
            ->where('project_report_type_id', $projectReportTypeId)
            ->where('identifier', '=', $identifier)
            ->lists('user_id');

        return User::whereIn('id', $userIds)->get();
    }

    public static function hasProjectReportPermission(Project $project, User $user, $identifierList = array())
    {
        $query = self::where('project_id', $project->id)
            ->where('user_id', '=', $user->id);

        if (! empty($identifierList)) {
            $query->whereIn('identifier', $identifierList);
        }

        $count = $query->count();

        return $count > 0;
    }

    public static function hasProjectReportTypePermission(Project $project, User $user, $projectReportTypeId)
    {
        $recordCount = self::where('project_id', $project->id)
                        ->where('user_id', $user->id)
                        ->where('project_report_type_id', $projectReportTypeId)
                        ->count();

        return $recordCount > 0;
    }
}