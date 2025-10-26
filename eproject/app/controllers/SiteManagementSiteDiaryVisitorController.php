<?php

use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryVisitor;
use PCK\Forms\AddNewSiteManagementSiteDiaryVisitorForm;


class SiteManagementSiteDiaryVisitorController extends \BaseController {

	public function __construct(AddNewSiteManagementSiteDiaryVisitorForm $addNewSiteManagementSiteDiaryVisitorForm)
	{
		$this->addNewSiteManagementSiteDiaryVisitorForm = $addNewSiteManagementSiteDiaryVisitorForm;
	}


	public function index($project, $siteDiaryId)
	{
		$visitorForms = SiteManagementSiteDiaryVisitor::where("site_diary_id",$siteDiaryId)->get();
		$show = false;

		return View::make('site_management_site_diary.visitor.index', array('project'=>$project, 'siteDiaryId' => $siteDiaryId, 'visitorForms'=>$visitorForms, 'show' => $show));
	}

	public function create($project,$siteDiaryId)
	{
		return View::make('site_management_site_diary.visitor.create', array('project'=>$project, 'siteDiaryId' => $siteDiaryId));
	}

	public function store($project,$siteDiaryId)
	{
		$input = Input::all();
		$user = Confide::user();

		try
		{
			$this->addNewSiteManagementSiteDiaryVisitorForm->validate($input);
			$input["site_diary_id"] =  $siteDiaryId;

			$input = $this->processInput($input);

			$siteManagementSiteDiaryVisitor = SiteManagementSiteDiaryVisitor::create($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Visitor Form is created successfully!");

        // return Redirect::route('site-management-site-diary-visitor.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		return Redirect::route('site-management-site-diary.general-form.edit', array($project->id,$siteDiaryId, 'visitor'));
	}

	public function edit($project,$siteDiaryId,$id)
	{
		$visitorForm = SiteManagementSiteDiaryVisitor::find($id);

		return View::make('site_management_site_diary.visitor.edit', array('visitorForm' => $visitorForm, 'project'=>$project, 'siteDiaryId' => $siteDiaryId));
	}

	public function update($project,$siteDiaryId,$id)
	{
		$input = Input::all();

		try
		{
			$this->addNewSiteManagementSiteDiaryVisitorForm->validate($input);
			$input = $this->processInput($input);

			$siteManagementSiteDiaryVisitor = SiteManagementSiteDiaryVisitor::where("id", $id)->update($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Visitor Form is updated successfully!");

        // return Redirect::route('site-management-site-diary-visitor.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		return Redirect::route('site-management-site-diary.general-form.edit', array($project->id,$siteDiaryId, 'visitor'));

	}

	public function destroy($project, $siteDiaryId, $id)
	{
		try
		{
			$record = SiteManagementSiteDiaryVisitor::find($id);
			$record->delete();
		}
		catch(Exception $e)
		{
			Flash::error("This object cannot be deleted.");

			return Redirect::route('site-management-site-diary-visitor.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		}

		Flash::success("This object is successfully deleted!");

        // return Redirect::route('site-management-site-diary-visitor.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		return Redirect::route('site-management-site-diary.general-form.edit', array($project->id,$siteDiaryId, 'visitor'));

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
