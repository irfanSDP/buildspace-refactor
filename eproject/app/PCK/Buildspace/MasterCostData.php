<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class MasterCostData extends Model {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_master_cost_data';

    protected $fillable = [ 'name', 'created_by' ];

    public function createdBy()
    {
        return $this->belongsTo('PCK\Buildspace\User', 'created_by');
    }

    public function getEprojectCreatedByAttribute()
    {
        return $this->createdBy->Profile->getEProjectUser();
    }

    public function canBeDeleted()
    {
        return CostData::where('master_cost_data_id', '=', $this->id)->count() < 1;
    }

    public function getAppLink()
    {
        return self::generateAppLink($this->id);
    }

    public static function generateAppLink($costDataId)
    {
        return getenv('COST_DATA_URL') . "?id={$costDataId}&isMaster=1";
    }
}