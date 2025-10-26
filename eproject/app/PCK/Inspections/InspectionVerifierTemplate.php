<?php namespace PCK\Inspections;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class InspectionVerifierTemplate extends Model {

	public $table = 'inspection_verifier_template';

	protected $fillable = ['inspection_group_id', 'user_id', 'priority'];

	public function group()
	{
		return $this->belongsTo('PCK\Inspections\InspectionGroup', 'inspection_group_id');
	}

	public function user()
	{
		return $this->belongsTo('PCK\Users\User');
	}
}
