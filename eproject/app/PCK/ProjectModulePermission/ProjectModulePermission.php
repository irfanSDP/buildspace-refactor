<?php namespace PCK\ProjectModulePermission;

use Confide;
use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Users\User;

class ProjectModulePermission extends Model {

    protected $table = 'project_module_permissions';

    protected $fillable = [
        'project_id',
        'user_id',
        'module_identifier',
    ];

    const MODULE_ID_INDONESIA_CIVIL_CONTRACT_ARCHITECT_INSTRUCTION = 1;
    const MODULE_ID_INDONESIA_CIVIL_CONTRACT_EXTENSION_OF_TIME     = 2;
    const MODULE_ID_INDONESIA_CIVIL_CONTRACT_LOSS_AND_EXPENSES     = 3;
    const MODULE_ID_INDONESIA_CIVIL_CONTRACT_EARLY_WARNING         = 4;

    protected static function getRecord(Project $project, User $user, $moduleId)
    {
        return static::where('project_id', '=', $project->id)
            ->where('user_id', '=', $user->id)
            ->where('module_identifier', '=', $moduleId)
            ->first();
    }

    public static function isAssigned(Project $project, User $user, $moduleId)
    {
        return static::getRecord($project, $user, $moduleId) ? true : false;
    }

    public static function getAssigned(Project $project, $moduleId)
    {
        $userIds = static::where('project_id', '=', $project->id)
            ->where('module_identifier', '=', $moduleId)
            ->lists('user_id');

        return User::whereIn('id', $userIds)->get();
    }

    public static function getVerifiers(Project $project, $moduleId)
    {
        $user = Confide::user();

        return ProjectModulePermission::getAssigned($project, $moduleId)
            ->reject(function($object) use ($user)
            {
                return $object->id == $user->id;
            });
    }

    public static function grant(Project $project, User $user, $moduleId)
    {
        if( static::isAssigned($project, $user, $moduleId) )
        {
            // Update record timestamp.
            static::getRecord($project, $user, $moduleId)->touch();

            return true;
        }

        $record = new static(array( 'project_id' => $project->id, 'user_id' => $user->id, 'module_identifier' => $moduleId ));

        return $record->save();
    }

    public static function revoke(Project $project, User $user, $moduleId)
    {
        if( ! static::isAssigned($project, $user, $moduleId) ) return true;

        $record = static::getRecord($project, $user, $moduleId);

        return $record->delete();
    }

}