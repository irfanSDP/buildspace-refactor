<?php

use PCK\Weathers\Weather; 
use PCK\Forms\WeatherForm;

class WeatherController extends \BaseController {

	private $weatherForm;

	public function __construct(WeatherForm $weatherForm)
	{
		$this->weatherForm = $weatherForm; 
	}

	public function index()
	{
		$weathers = Weather::orderBy('id', 'asc')->get();

		return View::make('weathers.index', array('weathers' => $weathers)); 
	}

	public function create()
	{
		return View::make('weathers.create');
	}

	public function store()
	{
		$inputs = Input::all();

		try
		{
			$this->weatherForm->validate($inputs);
			$record = new Weather;
			$record->name = $inputs['name'];
			$record->save();
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }
			
		Flash::success("Weather {$inputs['name']} is added successfully!");

		return Redirect::to('weathers');

	}

	public function edit($id)
	{
		$weather = Weather::find($id);

		return View::make('weathers.edit', array('weather' => $weather));
	}

	public function update($id)
	{
		$weather = Weather::find($id);
		$input = Input::all();

		try
		{
			$this->weatherForm->ignoreId($weather->id);
			$this->weatherForm->validate($input);
			$weather->name = $input['name'];
			$weather->save();
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		Flash::success("Weather {$input['name']} is updated!");

		return Redirect::to('weathers');
	}

	public function destroy($id)
	{
		$weather = Weather::find($id);

		try
		{
			$weather->delete();
		} 
		catch(Exception $e){

			Flash::error("Weather ({$weather->name}) cannot be deleted because it is used in other module.");

			return Redirect::to('weathers');
		}

		Flash::success("Weather ({$weather->name}) is deleted successfully!");

		return Redirect::to('weathers');
	}

}