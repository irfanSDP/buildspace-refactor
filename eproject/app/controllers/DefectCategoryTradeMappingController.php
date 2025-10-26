<?php

use PCK\Buildspace\PreDefinedLocationCode;
use PCK\Defects\DefectCategory;
use PCK\DefectCategoryTradeMapping\DefectCategoryPreDefinedLocationCode;

class DefectCategoryTradeMappingController extends \BaseController {

	public function index()
	{
		$trades = PreDefinedLocationCode::where('level', '0')->get();
		$categories = DefectCategory::all();

		return View::make('defect_category_trade_mapping.match', array('trades'=> $trades, 'categories' => $categories)); 
	}

	public function store()
	{
		$input = Input::all();

		$records = DefectCategoryPreDefinedLocationCode::all();

		$trade_inputs = array();

		foreach ($input['category'] as $trade => $categories)
		{
			$trade_inputs[] = $trade;
			$trade = PreDefinedLocationCode::find($trade);
			$trade->defectCategories()->sync($categories);
		}

		DefectCategoryPreDefinedLocationCode::whereNotIn("pre_defined_location_code_id", $trade_inputs)->delete();

		Flash::success("Trade to Category Mapping is successfull!");

		return Redirect::to(URL::previous());
	}
}
