<?php

use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryRejectedMaterial;
use PCK\Forms\AddNewSiteManagementSiteDiaryRejectedMaterialForm;
use PCK\SiteManagement\RejectedMaterial;

class SiteManagementSiteDiaryRejectedMaterialController extends \BaseController {

	public function __construct(AddNewSiteManagementSiteDiaryRejectedMaterialForm $addNewSiteManagementSiteDiaryRejectedMaterialForm)
	{
		$this->addNewSiteManagementSiteDiaryRejectedMaterialForm = $addNewSiteManagementSiteDiaryRejectedMaterialForm;
	}

	public function index($project, $siteDiaryId)
	{
		$rejectedMaterialForms = SiteManagementSiteDiaryRejectedMaterial::where("site_diary_id",$siteDiaryId)->get();
		$show = false;

		return View::make('site_management_site_diary.rejected_material.index', array('project'=>$project, 'siteDiaryId' => $siteDiaryId, 'rejectedMaterialForms'=>$rejectedMaterialForms, 'show' => $show));
	}

	public function create($project,$siteDiaryId)
	{
		$rejected_materials = RejectedMaterial::all();

		return View::make('site_management_site_diary.rejected_material.create', array('project'=>$project, 'siteDiaryId' => $siteDiaryId, 'rejected_materials'=>$rejected_materials));
	}

	public function store($project,$siteDiaryId)
	{
		$input = Input::all();
		$user = Confide::user();

		try
		{
			$this->addNewSiteManagementSiteDiaryRejectedMaterialForm->validate($input);
			$input["site_diary_id"] =  $siteDiaryId;

			$input = $this->processInput($input);

			$siteManagementSiteDiaryRejectedMaterial = SiteManagementSiteDiaryRejectedMaterial::create($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Rejected Material Form is created successfully!");

        // return Redirect::route('site-management-site-diary-rejected_material.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		return Redirect::route('site-management-site-diary.general-form.edit', array($project->id,$siteDiaryId, 'rejected_material'));

	}

	public function edit($project,$siteDiaryId,$id)
	{
		$rejectedMaterialForm = SiteManagementSiteDiaryRejectedMaterial::find($id);
		$rejected_materials = RejectedMaterial::all();

		return View::make('site_management_site_diary.rejected_material.edit', array('rejectedMaterialForm' => $rejectedMaterialForm, 'project'=>$project, 'siteDiaryId' => $siteDiaryId, 'rejected_materials'=>$rejected_materials));
	}

	public function update($project,$siteDiaryId,$id)
	{
		$input = Input::all();

		try
		{
			$this->addNewSiteManagementSiteDiaryRejectedMaterialForm->validate($input);
			$input = $this->processInput($input);

			$SiteManagementSiteDiaryRejectedMaterial = SiteManagementSiteDiaryRejectedMaterial::where("id", $id)->update($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Rejected Material Form is updated successfully!");

        // return Redirect::route('site-management-site-diary-rejected_material.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		return Redirect::route('site-management-site-diary.general-form.edit', array($project->id,$siteDiaryId, 'rejected_material'));
	}

	public function destroy($project, $siteDiaryId, $id)
	{
		try
		{
			$record = SiteManagementSiteDiaryRejectedMaterial::find($id);
			$record->delete();
		}
		catch(Exception $e)
		{
			Flash::error("This object cannot be deleted.");

			return Redirect::route('site-management-site-diary-rejected_material.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		}

		Flash::success("This object is successfully deleted!");

        // return Redirect::route('site-management-site-diary-rejected_material.index', array('projectId' => $project->id, 'siteDiaryId' => $siteDiaryId));
		return Redirect::route('site-management-site-diary.general-form.edit', array($project->id,$siteDiaryId, 'rejected_material'));

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

			if(strpos($key, "time"))
			{
				$input[$key] = date("H:i",strtotime($input[$key]));
			}
		}

		return $input;
	}


}
