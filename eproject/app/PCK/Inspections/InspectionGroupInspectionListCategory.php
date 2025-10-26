<?php namespace PCK\Inspections;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class InspectionGroupInspectionListCategory extends Model {

	protected $table = 'inspection_group_inspection_list_category';

	protected $fillable = ['inspection_group_id', 'inspection_list_category_id'];

	public $timestamps = false;

	public function group()
	{
		return $this->belongsTo('PCK\Inspections\InspectionGroup', 'inspection_group_id');
	}
}
