<?php namespace PCK\Inspections;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Users\User;

class InspectionResult extends Model{

    protected $fillable = [
        'inspection_id',
        'inspection_role_id',
        'status',
    ];

    const STATUS_IN_PROGRESS = 1;
    const STATUS_SUBMITTED   = 2;

    public function inspectionItemResults()
    {
        return $this->hasMany('PCK\Inspections\InspectionItemResult');
    }

    public function isInProgress()
    {
        return ($this->status == self::STATUS_IN_PROGRESS);
    }

    public function isSubmitted()
    {
        return ($this->status == self::STATUS_SUBMITTED);
    }

    public function role()
    {
        return $this->belongsTo('PCK\Inspections\InspectionRole', 'inspection_role_id');
    }

    public function submitter()
    {
        return $this->belongsTo('PCK\Users\User', 'submitted_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $object)
        {
            if( ! $object->status ) $object->status = self::STATUS_IN_PROGRESS;
        });
    }
}