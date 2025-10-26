<?php namespace PCK\SiteManagement;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Users\User;
use Illuminate\Database\Eloquent\Collection;

class SiteManagementUserPermission extends Model{

	protected $table = 'site_management_user_permissions';

	protected $fillable = [ 'module_identifier', 'user_id', 'project_id', 'is_viewer'];

    const MODULE_IDENTIFIER_DEFECT = 1;
    const MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS = 2;
    const MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS = 3;
    const MODULE_IDENTIFIER_SITE_DIARY           = 4;
    const MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR  = 5;
    const MODULE_IDENTIFIER_DAILY_REPORT  = 6;


    const USER_TYPE_SITE = 1;
    const USER_TYPE_QA_QC_CLIENT = 2;
    const USER_TYPE_PM = 4;
    const USER_TYPE_QS = 8;

    public function user()
    {
        return $this->belongsTo('PCK\Users\User','user_id');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project', 'project_id');
    }
    
    public static function getModuleNames($moduleIdentifier = null)
    {
        $moduleNames = array(
            self::MODULE_IDENTIFIER_DEFECT    => 'Defect',
            self::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS => 'Daily Labour Reports',
            self::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS => 'Update Site Progress',
            self::MODULE_IDENTIFIER_SITE_DIARY => 'Site Diary',
            self::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR => "Instruction To Contractor",
            self::MODULE_IDENTIFIER_DAILY_REPORT => "Daily Report"
        );

        if( $moduleIdentifier ) return $moduleNames[ $moduleIdentifier ];

        return $moduleNames;
    }

    public static function getUserList($moduleId, $projectId)
    {
        $users = new Collection();

        foreach(static::where('module_identifier', '=', $moduleId)->where('project_id', $projectId)->get() as $record)
        {
            $users->add($record->user);
        }

        return $users;
    }

	public static function isAssigned($moduleIdentifier, User $user, Project $project)
    {
        $record = static::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->first();

        return ( $record ? true : false );
    }

    public static function isProjectAssignedContractor(User $user, $project)
    {
        if( $user->isSuperAdmin() ) return false;

        $subProjects = Project::where("parent_project_id", $project->id)->get();

        foreach($subProjects as $subProject)
        {
            $subContractor = $subProject->getSelectedContractor();

            if($subContractor == NULL)
            {
                continue;
            }

            if($subContractor->id == $user->company->id)
            {
                return true;
            }

        }

        $contractor = $project->getSelectedContractor();

        if($contractor != NULL && $contractor->id == $user->company->id)
        {
            return true;
        }

        return false;
    }

    public static function getAssignedPms(Project $project, $moduleId)
    {
        $userIds = static::where('project_id', '=', $project->id)
            ->where('module_identifier', '=', $moduleId)
            ->where('pm', '=', true)
            ->lists('user_id');

        return User::whereIn('id', $userIds)->get();
    }

    public static function getAssignedPmAndPic(Project $project, $moduleId)
    {
        $userIds = static::where('project_id', '=', $project->id)
            ->where('module_identifier', '=', $moduleId)
            ->where('pm', '=', true)
            ->orwhere('site','=', true)
            ->lists('user_id');

        return User::whereIn('id', $userIds)->get();
    }

    public static function getAssignedQs(Project $project, $moduleId)
    {
        $userIds = static::where('project_id', '=', $project->id)
            ->where('module_identifier', '=', $moduleId)
            ->where('qs', '=', true)
            ->lists('user_id');

        return User::whereIn('id', $userIds)->get();
    }

    public static function getAssignedVerifiers(Project $project, $moduleId)
    {
        $userIds = static::where('project_id', '=', $project->id)
            ->where('module_identifier', '=', $moduleId)
            ->where('is_verifier', '=', true)
            ->lists('user_id');

        return User::whereIn('id', $userIds)->get();
    }

    public static function getAssignedSubmitters(Project $project, $moduleId)
    {
        $userIds = static::where('project_id', '=', $project->id)
            ->where('module_identifier', '=', $moduleId)
            ->where('is_submitter', '=', true)
            ->lists('user_id');

        return User::whereIn('id', $userIds)->get();
    }

    public static function assign($moduleIdentifier, User $user, Project $project)
    {
        self::firstOrCreate(array(
            'module_identifier' => $moduleIdentifier,
            'user_id'           => $user->id,
            'project_id'        => $project->id,
            'is_viewer'         => true
        ));

        return true;
    }

    public static function unAssign($moduleIdentifier, User $user, Project $project)
    {
        $record = self::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->first();

        return $record->delete();
    }

    public static function isSiteUser($moduleIdentifier, User $user, Project $project)
    {
        $record = self::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->where('site', '=', true)
            ->first();

        return ( ! is_null($record) );
    }

    public static function isClientUser($moduleIdentifier, User $user, Project $project)
    {
        $record = self::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->where('qa_qc_client', '=', true)
            ->first();

        return ( ! is_null($record) );
    }

    public static function isPmUser($moduleIdentifier, User $user, Project $project)
    {
        $record = self::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->where('pm', '=', true)
            ->first();

        return ( ! is_null($record) );
    }

    public static function isQsUser($moduleIdentifier, User $user, Project $project)
    {
        $record = self::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->where('qs', '=', true)
            ->first();

        return ( ! is_null($record) );
    }

    public static function isEditor($moduleIdentifier, User $user, Project $project)
    {
        $record = self::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->where('is_editor', '=', true)
            ->first();

        return ( ! is_null($record) );
    }


    public static function isViewer($moduleIdentifier, User $user, Project $project)
    {
        $record = self::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->where('is_viewer', '=', true)
            ->first();

        return ( ! is_null($record) );
    }

    public static function isSubmitter($moduleIdentifier, User $user, Project $project)
    {
        $record = self::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->where('is_submitter', '=', true)
            ->first();

        return ( ! is_null($record) );
    }

    public static function isVerifier($moduleIdentifier, User $user, Project $project)
    {
        $record = self::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->where('is_verifier', '=', true)
            ->first();

        return ( ! is_null($record) );
    }

    public static function getModulesOfAssignedUsers(User $user, $project = null)
    {
        $records = [];
        $query = \DB::table(with(new self)->getTable() .' AS smup')
                    ->join(with(new Project)->getTable() . ' AS p', 'smup.project_id', '=', 'p.id')
                    ->select('smup.id')
                    ->where('smup.user_id', '=', $user->id)
                    ->whereNull('p.deleted_at');

        if($project)
        {
            $query->where('p.id', '=', $project->id);
        }
        
        foreach($query->get() as $result)
        {
            array_push($records, self::find($result->id));
        }

        return $records;
    }
}
