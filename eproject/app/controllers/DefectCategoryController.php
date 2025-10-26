<?php

use PCK\Defects\DefectCategory;
use PCK\Forms\DefectCategoryForm;
use PCK\Exceptions\ValidationException;

class DefectCategoryController extends \BaseController {

	private $defectCategoryForm;

	public function __construct(DefectCategoryForm $defectCategoryForm)
	{
		$this->defectCategoryForm = $defectCategoryForm; 
	}

	public function index()
	{
		$categories = DefectCategory::orderBy("id", "desc")->get();

		return View::make('defect_categories.index', array('categories' => $categories)); 
	}

	public function create()
	{
		return View::make('defect_categories.create');
	}

	public function store()
	{
		$input = Input::all();

		try
		{
			$this->defectCategoryForm->validate($input);
			$record = new DefectCategory;
			$record->name = $input['name'];
			$record->save();
		}
		catch(ValidationException $e)
		{
            // Flash::error($e->getErrors()->first());

            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }
			
		Flash::success("Category {$input['name']} successfully added!");

		return Redirect::to('defect-categories');

	}

	public function edit($id)
	{
		$category = DefectCategory::find($id);

		return View::make('defect_categories.edit', array('category' => $category));
	}

	public function update($id)
	{
		$category = DefectCategory::find($id);
		$input = Input::all();

		try
		{
			$this->defectCategoryForm->ignoreId($category->id);
			$this->defectCategoryForm->validate($input);
			$category->name = $input['name'];
			$category->save();
		}
		catch(ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		Flash::success("Category {$input['name']} successfully updated!");

		return Redirect::to('defect-categories');
	}

	public function destroy($id)
	{
		$category = DefectCategory::find($id);

		try
		{
			$category->delete();
		} 
		catch(Exception $e){

			Flash::error("Category ({$category->name}) cannot be deleted because it is used in other module.");

			return Redirect::to('defect-categories');
		}

		Flash::success("Category ({$category->name}) successfully deleted!");

		return Redirect::to('defect-categories');
	}

}