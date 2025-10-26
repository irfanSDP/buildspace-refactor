<?php

use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryWeather;
use PCK\Forms\AddNewSiteManagementSiteDiaryWeatherForm;
use PCK\Weathers\Weather;

class SiteManagementSiteDiaryWeatherController extends \BaseController {

	public function __construct(AddNewSiteManagementSiteDiaryWeatherForm $addNewSiteManagementSiteDiaryWeatherForm)
	{
		$this->addNewSiteManagementSiteDiaryWeatherForm = $addNewSiteManagementSiteDiaryWeatherForm;
	}

	public function index($project, $siteDiaryId)
	{
		$weatherForms = SiteManagementSiteDiaryWeather::where("site_diary_id",$siteDiaryId)->get();
		$show = false;

		return View::make('site_management_site_diary.weather.index', array('project'=>$project, 'siteDiaryId' => $siteDiaryId, 'weatherForms'=>$weatherForms, 'show' => $show));
	}

	public function create($project,$siteDiaryId)
	{
		$weathers = Weather::all();

		return View::make('site_management_site_diary.weather.create', array('project'=>$project, 'siteDiaryId' => $siteDiaryId, 'weathers'=>$weathers));
	}

	public function store($project,$siteDiaryId)
	{
		$input = Input::all();
		$user = Confide::user();

		try
		{
			$this->addNewSiteManagementSiteDiaryWeatherForm->validate($input);
			$input["site_diary_id"] =  $siteDiaryId;

			$input = $this->processInput($input);

			$siteManagementSiteDiaryWeather = SiteManagementSiteDiaryWeather::create($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Weather Form is created successfully!");

        // return Redirect::route('site-management-site-diary-weather.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		return Redirect::route('site-management-site-diary.general-form.edit', array($project->id,$siteDiaryId, 'weather'));
	}

	public function edit($project,$siteDiaryId,$id)
	{
		$weatherForm = SiteManagementSiteDiaryWeather::find($id);
		$weathers = Weather::all();

		return View::make('site_management_site_diary.weather.edit', array('weatherForm' => $weatherForm, 'project'=>$project, 'siteDiaryId' => $siteDiaryId, 'weathers'=>$weathers));
	}

	public function update($project,$siteDiaryId,$id)
	{
		$input = Input::all();

		try
		{
			$this->addNewSiteManagementSiteDiaryWeatherForm->validate($input);
			$input = $this->processInput($input);

			$siteManagementSiteDiaryWeather = SiteManagementSiteDiaryWeather::where("id", $id)->update($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Weather Form is updated successfully!");

        // return Redirect::route('site-management-site-diary-weather.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		return Redirect::route('site-management-site-diary.general-form.edit', array($project->id,$siteDiaryId, 'weather'));
	}

	public function destroy($project, $siteDiaryId, $id)
	{
		try
		{
			$record = SiteManagementSiteDiaryWeather::find($id);
			$record->delete();
		}
		catch(Exception $e)
		{
			Flash::error("This object cannot be deleted.");

			return Redirect::route('site-management-site-diary-weather.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		}

		Flash::success("This object is successfully deleted!");

        // return Redirect::route('site-management-site-diary-weather.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		return Redirect::route('site-management-site-diary.general-form.edit', array($project->id,$siteDiaryId, 'weather'));

	}

	public function processInput($input)
	{
		foreach($input as $key => $value)
		{
			if($input[$key] == "")
			{
				$input[$key] = NULL;
			}

			if($key == "_method" || $key == "_token" || $key == "Submit" || $key == "form_type")
			{
				unset($input[$key]);
			}

			if($key == "general_date")
			{
				$input[$key] = date("Y-m-d H:i:s", strtotime($input[$key]));
			}
		}

		return $input;
	}



}
