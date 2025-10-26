<?php namespace PCK\ContractManagementModule\UserPermission;

use Illuminate\Database\Eloquent\Model;
use PCK\ContractGroups\Types\Role;
use PCK\Projects\Project;
use PCK\Users\User;

class ContractManagementUserPermission extends Model {

    protected $fillable = [ 'module_identifier', 'user_id', 'project_id' ];

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project', 'project_id');
    }

    public static function isAssigned($moduleIdentifier, User $user, Project $project)
    {
        $record = static::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->first();

        return ( $record ? true : false );
    }

    public static function assign($moduleIdentifier, User $user, Project $project)
    {
        $record = self::firstOrNew(array(
            'module_identifier' => $moduleIdentifier,
            'user_id'           => $user->id,
            'project_id'        => $project->id,
        ));

        $record->is_verifier = true;

        return $record->save();
    }

    public static function unAssign($moduleIdentifier, User $user, Project $project)
    {
        $record = self::where('module_identifier', '=', $moduleIdentifier)
            ->where('user_id', '=', $user->id)
            ->where('project_id', '=', $project->id)
            ->first();

        return $record->delete();
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

    public static function isUserManager(User $user, Project $project)
    {
        if( ! $user->hasCompanyProjectRole($project, Role::PROJECT_OWNER) ) return false;

        if( ! $user->isGroupAdmin() ) return false;

        return true;
    }

    public static function getModulesOfAssignedUsers(User $user, $project = null)
    {
        $records = [];
        $query = \DB::table(with(new self)->getTable() .' AS cmup')
                    ->join(with(new Project)->getTable() . ' AS p', 'cmup.project_id', '=', 'p.id')
                    ->select('cmup.id')
                    ->where('cmup.user_id', '=', $user->id)
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