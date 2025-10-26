<?php namespace PCK\DefectCategoryTradeMapping;

use Illuminate\Database\Eloquent\Model;

class DefectCategoryPreDefinedLocationCode extends Model {

	protected $table = 'defect_category_pre_defined_location_code';

	public static function getRecord($trade_id, $defect_category_id)
	{
		return static::where('pre_defined_location_code_id', '=', $trade_id)
            ->where('defect_category_id', '=', $defect_category_id)
            ->first();
	}

	public static function recordExists($trade_id, $defect_category_id)
	{
		return static::getRecord($trade_id, $defect_category_id) ? true : false;
	}

}