<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use PCK\Buildspace\Project as BsProject;
use PCK\BUildspace\User as BsUser;

class ProjectUserPermission extends Model {
    
    const STATUS_PROJECT_BUILDER    = 1;
    const STATUS_TENDERING          = 2;
    const STATUS_POST_CONTRACT      = 4;
    const STATUS_PROJECT_MANAGEMENT = 8;

    protected $connection = 'buildspace';
    protected $table      = 'bs_project_user_permissions';

    protected static function boot()
    {
        parent::boot();
    }

    public function User()
    {
        return $this->hasOne('PCK\Buildspace\User', 'id', 'user_id');
    }

    public static function getAssignedUserIdsByProjectAndStatus(BsProject $project, $projectStatus)
    {
        return \DB::connection('buildspace')->table('bs_project_user_permissions AS p')
            ->select("p.user_id AS id")
            ->join('bs_sf_guard_user AS u', 'u.id', '=', 'p.user_id')
            ->where('p.project_structure_id', '=', $project->id)
            ->where('p.project_status', '=', $projectStatus)
            ->where('u.is_super_admin', '=', false)
            ->where('u.is_active', '=', true)
            ->whereNull('u.deleted_at')
            ->orderBy('u.id', 'asc')
            ->lists('id');
    }

    public static function revokeAccessFromAllBuildspaceProjects(BsUser $user)
    {
        self::where('user_id', $user->id)->delete();
    }
}