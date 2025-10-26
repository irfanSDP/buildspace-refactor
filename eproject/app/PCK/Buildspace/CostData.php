<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Subsidiaries\Subsidiary;

class CostData extends Model {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_cost_data';

    protected $fillable = [ 'name', 'master_cost_data_id', 'subsidiary_id', 'cost_data_type_id', 'region_id', 'subregion_id', 'currency_id', 'tender_date', 'award_date', 'created_by', 'updated_by' ];

    protected static function boot()
    {
        parent::boot();

        static::created(function(self $bsCostData)
        {
            $costData = new \PCK\CostData\CostData;

            $costData->buildspace_origin_id = $bsCostData->id;
            $costData->save();
        });
    }

    public function getEProjectCostData()
    {
        return \PCK\CostData\CostData::where('buildspace_origin_id', '=', $this->id)->first();
    }

    public function getSubsidiary()
    {
        return Subsidiary::find($this->subsidiary_id);
    }

    public function master()
    {
        return $this->belongsTo('PCK\Buildspace\MasterCostData', 'master_cost_data_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Buildspace\User', 'created_by');
    }

    public function getEprojectCreatedByAttribute()
    {
        return $this->createdBy->Profile->getEProjectUser();
    }

    public function getEProjectProjectsAttribute()
    {
        $records = CostDataProject::has('project')->with('project')->where('cost_data_id', '=', $this->id)->get();

        $projects = new Collection();

        foreach($records as $record)
        {
            $projects->push($record->project->mainInformation->getEProjectProject());
        }

        return $projects;
    }

    public function type()
    {
        return $this->belongsTo('PCK\Buildspace\CostDataType', 'cost_data_type_id');
    }

    public function region()
    {
        return $this->belongsTo('PCK\Buildspace\Region');
    }

    public function subregion()
    {
        return $this->belongsTo('PCK\Buildspace\SubRegion');
    }

    public function currency()
    {
        return $this->belongsTo('PCK\Buildspace\Currency');
    }

    public function getAppLink()
    {
        return self::generateAppLink($this->id);
    }

    public static function generateAppLink($costDataId)
    {
        return getenv('COST_DATA_URL') . "?id={$costDataId}";
    }
}