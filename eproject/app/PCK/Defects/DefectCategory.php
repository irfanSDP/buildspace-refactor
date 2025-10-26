<?php namespace PCK\Defects;

use Illuminate\Database\Eloquent\Model;

class DefectCategory extends Model {

	public function defects(){

		return $this->hasMany('PCK\Defects\Defect');
	}
}
