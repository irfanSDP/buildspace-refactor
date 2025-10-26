<?php namespace PCK\Inspections;

use Illuminate\Database\Eloquent\Model;

class InspectionGroup extends Model {

	protected $fillable = ['project_id', 'name'];

	public function project()
	{
		return $this->belongsTo('PCK\Projects\Project');
	}

	public function inspectionSubmitters()
	{
		return $this->hasMany('PCK\Inspections\InspectionSubmitter');
	}

	public function inspectionVerifiers()
	{
		return $this->hasMany('PCK\Inspections\InspectionVerifierTemplate');
	}

	public function inspectionGroupUsers()
	{
		return $this->hasMany('PCK\Inspections\InspectionGroupUser');
	}
}
