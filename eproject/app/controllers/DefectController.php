<?php

use PCK\Defects\Defect;
use PCK\Forms\DefectForm;

class DefectController extends \BaseController {

	private $defectCategoryForm; 

	public function __construct(DefectForm $defectForm){

		$this->defectForm = $defectForm;

	}

	public function index($defectCategoryId)
	{
		$defects = Defect::where('defect_category_id', $defectCategoryId)->get();

		return View::make('defects.index', array('defects' => $defects, 'defectCategoryId'=>$defectCategoryId)); 
	}

	public function create($defectCategoryId)
	{
		return View::make('defects.create',array('defectCategoryId'=>$defectCategoryId));
	}

	public function store($defectCategoryId)
	{
		$input = Input::all();

		$input['defect_category_id'] = $defectCategoryId;

        try
        {
			$this->defectForm->validate($input);
			$defect = new Defect;
			$defect->name = $input['name']; 
			$defect->defect_category_id = $defectCategoryId;
			$defect->save(); 
        }
        catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Defect ({$input['name']}) successfully created!");

		return Redirect::route('defect-categories.defects', array('defectCategoryId' => $defectCategoryId));
	}

	public function edit($defectCategoryId,$id)
	{
		$defect = Defect::find($id);

		return View::make('defects.edit', array('defect'=>$defect, 'defectCategoryId' => $defectCategoryId));
	}

	public function update($defectCategoryId,$id)
	{
		$defect = Defect::find($id);
		$input = Input::all();

		$input['defect_category_id'] = $defectCategoryId;

		try
		{
			$this->defectForm->ignoreId($defect->id);
			$this->defectForm->validate($input);
			$defect->name = $input['name'];
			$defect->save();
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		Flash::success("Defect ({$input['name']}) successfully updated!");

		return Redirect::route('defect-categories.defects', array('defectCategoryId' => $defectCategoryId));
	}

	public function destroy($defectCategoryId,$id)
	{
		try
		{
			$defect = Defect::find($id);
			$defect->delete();
		}
		catch(Exception $e)
		{
			Flash::error("Defect ({$defect->name}) cannot be deleted.");

			return Redirect::route('defect-categories.defects', array('defectCategoryId' => $defectCategoryId));
		}

		Flash::success("Defect ({$defect->name}) is successfully deleted!");

		return Redirect::route('defect-categories.defects', array('defectCategoryId' => $defectCategoryId));
	}
}