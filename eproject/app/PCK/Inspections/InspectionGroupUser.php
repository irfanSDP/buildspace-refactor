<?php namespace PCK\Inspections;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class InspectionGroupUser extends Model {

	public $timestamps = false;

	protected $fillable = ['inspection_role_id', 'inspection_group_id', 'user_id'];

	public function role()
	{
		return $this->belongsTo('PCK\Inspections\InspectionRole', 'inspection_role_id');
	}

	public function group()
	{
		return $this->belongsTo('PCK\Inspections\InspectionGroup', 'inspection_group_id');
	}

	public function user()
	{
		return $this->belongsTo('PCK\Users\User');
	}
}
