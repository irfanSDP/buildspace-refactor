<?php namespace PCK\Inspections;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class InspectionList extends Model
{
    protected $table    = 'inspection_lists';
    protected $fillable = ['project_id', 'name', 'priority', 'created_at', 'updated_at'];

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function isTemplate()
    {
        return is_null($this->project_id);
    }

    public function inspectionListCategories()
    {
        return $this->hasMany('PCK\Inspections\InspectionListCategory', 'inspection_list_id');
    }

    public static function getNextFreePriority($projectId)
    {
        $query = null;

        if($projectId)
        {
            $query = self::where('project_id', $projectId);
        }
        else
        {
            $query = self::whereNull('project_id');
        }

        $record = $query->orderBy('priority', 'DESC')->first();

        if(is_null($record)) return 0;

        return ($record->priority + 1);
    }

    public static function updatePriority($removedRecord)
    {
        $query = null;

        if(is_null($removedRecord->project_id))
        {
            $query = self::whereNull('project_id');
        }
        else
        {
            $query = self::where('project_id', $removedRecord->project_id);
        }

        $records = $query->where('priority', '>', $removedRecord->priority)->get();

        foreach($records as $record)
        {
            $record->priority = ($record->priority - 1);
            $record->save();
        }
    }
}

