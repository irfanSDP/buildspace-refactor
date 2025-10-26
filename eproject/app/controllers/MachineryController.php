<?php

use PCK\SiteManagement\Machinery; 
use PCK\Forms\MachineryForm;

class MachineryController extends \BaseController {

	private $machineryForm;

	public function __construct(MachineryForm $machineryForm)
	{
		$this->machineryForm = $machineryForm; 
	}


	public function index()
	{
		$machinery = Machinery::orderBy('id', 'asc')->get();

		return View::make('machinery.index', array('machinery' => $machinery)); 
	}

	public function create()
	{
		return View::make('machinery.create');
	}

	public function store()
	{
		$inputs = Input::all();

		try
		{
			$this->machineryForm->validate($inputs);
			$record = new Machinery;
			$record->name = $inputs['name'];
			$record->save();
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }
			
		Flash::success("Machinery {$inputs['name']} is added successfully!");

		return Redirect::route('machinery.index');
	}

	public function edit($id)
	{
		$machinery = Machinery::find($id);

		return View::make('machinery.edit', array('machinery' => $machinery));
	}

	public function update($id)
	{
		$machinery = Machinery::find($id);
		$input = Input::all();

		try
		{
			$this->machineryForm->ignoreId($machinery->id);
			$this->machineryForm->validate($input);
			$machinery->name = $input['name'];
			$machinery->save();
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		Flash::success("Machinery {$input['name']} is updated!");

		return Redirect::route('machinery.index');	
	}

	public function destroy($id)
	{
		$machinery = Machinery::find($id);

		try
		{
			$machinery->delete();
		} 
		catch(Exception $e){

			Flash::error("Machinery ({$machinery->name}) cannot be deleted because it is used in other module.");

			return Redirect::route('machinery.index');		
		}

		Flash::success("Machinery ({$machinery->name}) is deleted successfully!");

		return Redirect::route('machinery.index');	}

}