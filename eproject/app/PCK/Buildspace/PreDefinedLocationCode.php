<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PreDefinedLocationCode extends Model {

	use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_pre_defined_location_codes';

    public function defectCategories()
    {
        return $this->belongsToMany('PCK\Defects\DefectCategory', 'defect_category_pre_defined_location_code')->withTimestamps();
    }
    
}