<?php

use PCK\SiteManagement\RejectedMaterial; 
use PCK\Forms\RejectedMaterialForm;

class RejectedMaterialController extends \BaseController {

	private $rejectedMaterialForm;

	public function __construct(RejectedMaterialForm $rejectedMaterialForm)
	{
		$this->rejectedMaterialForm = $rejectedMaterialForm; 
	}


	public function index()
	{
		$rejected_materials = RejectedMaterial::orderBy('id', 'asc')->get();

		return View::make('rejected_materials.index', array('rejected_materials' => $rejected_materials)); 
	}

	public function create()
	{
		return View::make('rejected_materials.create');
	}

	public function store()
	{
		$inputs = Input::all();

		try
		{
			$this->rejectedMaterialForm->validate($inputs);
			$record = new RejectedMaterial;
			$record->name = $inputs['name'];
			$record->save();
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }
			
		Flash::success("RejectedMaterial {$inputs['name']} is added successfully!");

		return Redirect::route('rejected-materials.index');
	}

	public function edit($id)
	{
		$rejectedMaterial = RejectedMaterial::find($id);

		return View::make('rejected_materials.edit', array('rejectedMaterial' => $rejectedMaterial));
	}

	public function update($id)
	{
		$rejectedMaterial = RejectedMaterial::find($id);
		$input = Input::all();

		try
		{
			$this->rejectedMaterialForm->ignoreId($rejectedMaterial->id);
			$this->rejectedMaterialForm->validate($input);
			$rejectedMaterial->name = $input['name'];
			$rejectedMaterial->save();
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		Flash::success("RejectedMaterial {$input['name']} is updated!");

		return Redirect::route('rejected-materials.index');	
	}

	public function destroy($id)
	{
		$rejectedMaterial = RejectedMaterial::find($id);

		try
		{
			$rejectedMaterial->delete();
		} 
		catch(Exception $e){

			Flash::error("RejectedMaterial ({$rejectedMaterial->name}) cannot be deleted because it is used in other module.");

			return Redirect::route('rejected-materials.index');		
		}

		Flash::success("RejectedMaterial ({$rejectedMaterial->name}) is deleted successfully!");

		return Redirect::route('rejected-materials.index');	}

}