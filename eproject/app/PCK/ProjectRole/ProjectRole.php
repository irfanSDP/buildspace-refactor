<?php namespace PCK\ProjectRole;

use Illuminate\Database\Eloquent\Model;
use PCK\ContractGroups\ContractGroup;
use PCK\Projects\Project;

class ProjectRole extends Model {

    protected $fillable = [ 'project_id', 'contract_group_id', 'name' ];

    public function project()
    {
        $this->belongsTo('PCK\Projects\Project');
    }

    public function contractGroup()
    {
        $this->belongsTo('PCK\ContractGroups\ContractGroup');
    }

    public static function getRecord(Project $project, $group)
    {
        return self::where('project_id', '=', $project->id)
            ->where('contract_group_id', '=', ContractGroup::getIdByGroup($group))
            ->first();
    }

    public static function getRoleName(Project $project, $group)
    {
        if( $record = self::getRecord($project, $group) ) return $record->name;

        return ContractGroup::find(ContractGroup::getIdByGroup($group)->name);
    }

    public static function setRoleName(Project $project, $group, $name)
    {
        if( ! $record = self::getRecord($project, $group) ) return false;

        $record->name = $name;

        return $record->save();
    }

    public static function initialise(Project $project, $groups = array())
    {
        if( ! empty( $groups ) )
        {
            $contractGroups = ContractGroup::whereIn('group', $groups)->get();
        }
        else
        {
            $contractGroups = ContractGroup::all();
        }

        foreach($contractGroups as $contractGroup)
        {
            if( ProjectRole::getRecord($project, $contractGroup->group) ) continue;

            ProjectRole::create(array(
                'project_id'        => $project->id,
                'contract_group_id' => $contractGroup->id,
                'name'              => $contractGroup->name
            ));
        }
    }

}