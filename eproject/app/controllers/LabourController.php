<?php

use PCK\SiteManagement\Labour; 
use PCK\Forms\LabourForm;

class LabourController extends \BaseController {

	private $labourForm;

	public function __construct(LabourForm $labourForm)
	{
		$this->labourForm = $labourForm; 
	}


	public function index()
	{
		$labours = Labour::orderBy('id', 'asc')->get();

		return View::make('labours.index', array('labours' => $labours));
	}

	public function create()
	{
		return View::make('labours.create');
	}

	public function store()
	{
		$inputs = Input::all();

		try
		{
			$this->labourForm->validate($inputs);
			$record = new Labour;
			$record->name = $inputs['name'];
			$record->save();
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }
			
		Flash::success("Labour {$inputs['name']} is added successfully!");

		return Redirect::route('labours.index');
	}

	public function edit($id)
	{
		$labour = Labour::find($id);

		return View::make('labours.edit', array('labour' => $labour));
	}

	public function update($id)
	{
		$labour = Labour::find($id);
		$input = Input::all();

		try
		{
			$this->labourForm->ignoreId($labour->id);
			$this->labourForm->validate($input);
			$labour->name = $input['name'];
			$labour->save();
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		Flash::success("Labour {$input['name']} is updated!");

		return Redirect::route('labours.index');	
	}

	public function destroy($id)
	{
		$labour = Labour::find($id);

		try
		{
			$labour->delete();
		} 
		catch(Exception $e){

			Flash::error("Labour ({$labour->name}) cannot be deleted because it is used in other module.");

			return Redirect::route('labours.index');		
		}

		Flash::success("Labour ({$labour->name}) is deleted successfully!");

		return Redirect::route('labours.index');	
	}

}