<?php namespace PCK\Defects;

use Illuminate\Database\Eloquent\Model;

class Defect extends Model {

	public function defectCategory(){

		return $this->belongsTo('PCK\Defects\DefectCategory','defect_category_id');
	}
}
