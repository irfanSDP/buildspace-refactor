<?php

use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryVisitor;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryWeather;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryRejectedMaterial;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryMachinery;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryLabour;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryRepository;
use PCK\SiteManagement\SiteManagementUserPermission;
use PCK\Forms\AddNewSiteManagementSiteDiaryGeneralForm;
use PCK\Verifier\Verifier;
use PCK\Tenders\TenderRepository;
use PCK\Weathers\Weather;
use PCK\SiteManagement\Machinery;
use PCK\SiteManagement\Labour;
use PCK\SiteManagement\RejectedMaterial;
use PCK\Base\Helpers;

class SiteManagementSiteDiaryController extends \BaseController {

	private $tenderRepository;
	private $siteManagementSiteDiaryRepository;
	private $addNewSiteManagementSiteDiaryGeneralForm;

	public function __construct(TenderRepository $tenderRepository, 
		SiteManagementSiteDiaryRepository $siteManagementSiteDiaryRepository,
		AddNewSiteManagementSiteDiaryGeneralForm $addNewSiteManagementSiteDiaryGeneralForm)
	{
		$this->tenderRepository = $tenderRepository;
		$this->siteManagementSiteDiaryRepository = $siteManagementSiteDiaryRepository;
		$this->addNewSiteManagementSiteDiaryGeneralForm = $addNewSiteManagementSiteDiaryGeneralForm;
	}

	/**
	 * Display a listing of the resource.
	 * GET /sitemanagementsitediary
	 *
	 * @return Response
	 */
	public function index($project)
	{
		$user = Confide::user();

		$query = SiteManagementSiteDiaryGeneralFormResponse::where("project_id", $project->id);
		$records = $query->orderBy("id", "desc")->get();

		foreach($records as $record)
		{
			if(isset($record->general_date))
			{
				$record->general_date = Helpers::processDateFormat($record->general_date);
			}
			
			$record->status_text = SiteManagementSiteDiaryGeneralFormResponse::getStatusText($record->status);
		}

		return View::make('site_management_site_diary.index', array('records'=>$records, 'project'=>$project, 'user'=>$user));
	}

	public function create($project)
	{
		$days = SiteManagementSiteDiaryGeneralFormResponse::getDays();

		$verifiers = $this->siteManagementSiteDiaryRepository->getVerifiers($project);

		return View::make('site_management_site_diary.general.create', array('days' => $days, 'verifiers' => $verifiers,'project'=>$project));
	}

	public function store($project)
	{
		$input = Input::all();

		$user = Confide::user();

		try
		{
			$this->addNewSiteManagementSiteDiaryGeneralForm->validate($input);

			$input["submitted_by"] =  $user->id;
			$input["project_id"]   = $project->id;

			$input = $this->processInput($input);

			$siteManagementSiteDiaryGeneralFormResponse = SiteManagementSiteDiaryGeneralFormResponse::create($input);

			if(isset($input["verifiers"]))
			{
				$this->siteManagementSiteDiaryRepository->submitForApproval($siteManagementSiteDiaryGeneralFormResponse,$input);
			}

		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("General Form is created successfully!");

        return Redirect::route('site-management-site-diary.index', array('projectId' => $project->id));
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /sitemanagementsitediary/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($project,$id,$form)
	{
		$generalForm = SiteManagementSiteDiaryGeneralFormResponse::find($id);
		$approvalStatus = $generalForm->status;

		$weatherForms = SiteManagementSiteDiaryWeather::where("site_diary_id",$id)->get();
		$rejectedMaterialForms = SiteManagementSiteDiaryRejectedMaterial::where("site_diary_id",$id)->get();
		$visitorForms = SiteManagementSiteDiaryVisitor::where("site_diary_id",$id)->get();

		$labourForms = SiteManagementSiteDiaryLabour::where("site_diary_id",$id)->get();
		$machineryForms = SiteManagementSiteDiaryMachinery::where("site_diary_id",$id)->get();

		$machinery = Machinery::all();
		$labours   = Labour::all();

		$labourFormArray = [];
		$machineryFormArray = [];

		foreach($labourForms as $labourForm)
		{
			$labourFormArray[$labourForm->labour_id] = $labourForm->value;
		}

		foreach($machineryForms as $machineryForm)
		{
			$machineryFormArray[$machineryForm->machinery_id] = $machineryForm->value;
		}

		$verifiers = $this->siteManagementSiteDiaryRepository->getVerifiers($project);

		if(isset($generalForm["general_date"]))
		{
			$generalForm["general_date"] = Helpers::processDateFormat($generalForm["general_date"]);
		}

		$days = SiteManagementSiteDiaryGeneralFormResponse::getDays();
		$show = false;

		return View::make('site_management_site_diary.edit', array('generalForm'=>$generalForm, 'approvalStatus' => $approvalStatus, 'labours' => $labours, 'machinery' => $machinery,'labourFormArray' => $labourFormArray, 'machineryFormArray' => $machineryFormArray, 'verifiers' => $verifiers, 'form' => $form, 'siteDiaryId' =>$id, 'show' => $show,'days' => $days, 'weatherForms'=>$weatherForms, 'rejectedMaterialForms'=>$rejectedMaterialForms, 'visitorForms'=> $visitorForms, 'project'=>$project));
	}

	public function show($project,$id)
	{
		$generalForm = SiteManagementSiteDiaryGeneralFormResponse::find($id);
		$weatherForms = SiteManagementSiteDiaryWeather::where("site_diary_id",$id)->get();
		$rejectedMaterialForms = SiteManagementSiteDiaryRejectedMaterial::where("site_diary_id",$id)->get();
		$visitorForms = SiteManagementSiteDiaryVisitor::where("site_diary_id",$id)->get();

		$labourForms = SiteManagementSiteDiaryLabour::where("site_diary_id",$id)->get();
		$machineryForms = SiteManagementSiteDiaryMachinery::where("site_diary_id",$id)->get();

		$machinery = Machinery::all();
		$labours   = Labour::all();

		$labourFormArray = [];
		$machineryFormArray = [];

		foreach($labourForms as $labourForm)
		{
			$labourFormArray[$labourForm->labour_id] = $labourForm->value;
		}

		foreach($machineryForms as $machineryForm)
		{
			$machineryFormArray[$machineryForm->machinery_id] = $machineryForm->value;
		}

		if(isset($generalForm["general_date"]))
		{
			$generalForm["general_date"] = Helpers::processDateFormat($generalForm["general_date"]);
		}

		$days = SiteManagementSiteDiaryGeneralFormResponse::getDays();

		$isCurrentVerifier	= Verifier::isCurrentVerifier(\Confide::user(), $generalForm);
		$verifierLogs       = Verifier::getAssignedVerifierRecords($generalForm, true);

		$isVerified = false;
		$show = true;

		if($generalForm->status == SiteManagementSiteDiaryGeneralFormResponse::STATUS_APPROVED || $generalForm->status == SiteManagementSiteDiaryGeneralFormResponse::STATUS_REJECT)
		{
			$isVerified = true;
		}

		return View::make('site_management_site_diary.show', array('generalForm'=>$generalForm, 'labours' => $labours, 'machinery' => $machinery,'labourFormArray' => $labourFormArray, 'machineryFormArray' => $machineryFormArray, 'show' => $show, 'isVerified'=>$isVerified, 'verifierLogs' => $verifierLogs, 'isCurrentVerifier'=>$isCurrentVerifier,'siteDiaryId' =>$id, 'days' => $days, 'weatherForms'=>$weatherForms, 'rejectedMaterialForms'=>$rejectedMaterialForms, 'visitorForms'=> $visitorForms, 'project'=>$project));
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /sitemanagementsitediary/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($project,$id)
	{
		$input = Input::all();
		$form = "general";

		$siteManagementSiteDiaryGeneralFormResponse = SiteManagementSiteDiaryGeneralFormResponse::find($id);

		try
		{
			switch($input["form_type"])
			{
				case "general" 			 : 	$this->addNewSiteManagementSiteDiaryGeneralForm->validate($input); 
											$input = $this->processInput($input);

											$siteManagementSiteDiaryGeneralFormResponse->where("id", $id)->update($input);
											break;
				case "labour" 		     :  $this->siteManagementSiteDiaryRepository->insertIntoSiteManagementSiteDiaryLabour($id,$input,$project);
											$form = "labour";
											break;
				case "machinery" 	     :  $this->siteManagementSiteDiaryRepository->insertIntoSiteManagementSiteDiaryMachinery($id,$input,$project);
											$form = "machinery";
											break;
				default 				 :  break;
			}
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		Flash::success("Form is updated successfully!");
		
		return Redirect::route('site-management-site-diary.general-form.edit', array($project->id, $id, $form));
	}

		/**
	 * Remove the specified resource from storage.
	 * DELETE /sitemanagementsitediary/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($project, $id)
	{
		try
		{
			$record = SiteManagementSiteDiaryGeneralFormResponse::find($id);
			$record->delete();
		}
		catch(Exception $e)
		{
			Flash::error("This object cannot be deleted.");

			return Redirect::route('site-management-site-diary.index', array('projectId' => $project->id));
		}

		Flash::success("This object is successfully deleted!");

        return Redirect::route('site-management-site-diary.index', array('projectId' => $project->id));
	}

	public function submitGeneralFormForApproval($project, $id)
	{
		$input = Input::all();

		$siteManagementSiteDiaryGeneralFormResponse = SiteManagementSiteDiaryGeneralFormResponse::find($id);

		if(isset($input["verifiers"]))
		{
			$this->siteManagementSiteDiaryRepository->submitForApproval($siteManagementSiteDiaryGeneralFormResponse,$input);
		}

		Flash::success("Form is submitted for approval!");

		return Redirect::route('site-management-site-diary.index', array('projectId' => $project->id));
	}

	public function processInput($input)
	{
		foreach($input as $key => $value)
		{
			if($input[$key] == "")
			{
				$input[$key] = NULL;
			}

			if($key == "_method" || $key == "_token" || $key == "Submit" || $key == "Save" || $key == "form_type")
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

	public function getDayFromCalendar()
	{
		$input = Input::all();
		$date  = $input["date"];

		$day = Helpers::convertDateToDay($date);

		return $day;
	}
}