<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Projects\Project;
use PCK\Subsidiaries\Subsidiary;

class CostDataProject extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_cost_data_project';

    protected $fillable = [ 'cost_data_id', 'project_structure_id', 'created_by', 'updated_by' ];


    public function costData()
    {
        return $this->belongsTo('PCK\Buildspace\CostData', 'cost_data_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Buildspace\User', 'created_by');
    }

    public function getEprojectCreatedByAttribute()
    {
        return $this->createdBy->Profile->getEProjectUser();
    }

    public function project()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    public static function syncEProjectProjects(CostData $costData, $eProjectProjectIds)
    {
        $user = \Confide::user();

        self::where('cost_data_id', '=', $costData->id)->delete();

        foreach($eProjectProjectIds as $eProjectProjectId)
        {
            self::create(array(
                'cost_data_id'         => $costData->id,
                'project_structure_id' => Project::find($eProjectProjectId)->getBsProjectMainInformation()->project_structure_id,
                'created_by'           => $user->getBsUser()->id,
                'updated_by'           => $user->getBsUser()->id,
            ));
        }
    }
}