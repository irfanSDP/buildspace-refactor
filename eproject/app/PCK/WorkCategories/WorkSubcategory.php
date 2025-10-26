<?php namespace PCK\WorkCategories;

use Illuminate\Database\Eloquent\Model;

class WorkSubcategory extends Model {

    const UNSPECIFIED_RECORD_NAME = 'Unspecified';

	protected $fillable = [
		'name'
	];

	public function contractors()
	{
		return $this->belongsToMany('PCK\Contractors\Contractor');
	}

}