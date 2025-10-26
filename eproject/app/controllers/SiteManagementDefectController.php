<?php

use Carbon\Carbon;
use PCK\Buildspace\PreDefinedLocationCode;
use PCK\Buildspace\Project as BsProjectStructure;
use PCK\Buildspace\ProjectStructureLocationCode;
use PCK\Buildspace\BillColumnSetting;
use PCK\Defects\DefectCategory;
use PCK\Defects\Defect;
use PCK\SiteManagement\SiteManagementDefect;
use PCK\SiteManagement\SiteManagementDefectRepository;
use PCK\SiteManagement\SiteManagementDefectFormResponse;
use PCK\SiteManagement\SiteManagementDefectBackchargeDetail;
use PCK\SiteManagement\SiteManagementUserPermission;
use PCK\SiteManagement\SiteManagementMCAR;
use PCK\SiteManagement\SiteManagementMCARFormResponse;
use PCK\DailyLabourReports\ProjectLabourRate;
use PCK\DefectCategoryTradeMapping\DefectCategoryPreDefinedLocationCode;
use PCK\Tenders\TenderRepository;
use PCK\Forms\AddNewSiteManagementDefectForm;
use PCK\Forms\AddNewSiteManagementDefectFormResponse;
use PCK\Forms\AddNewDefectBackchargeDetailForm;
use PCK\Forms\AddNewSiteManagementMCARForm;
use PCK\Forms\AddNewSiteManagementMCARFormResponse;
use PCK\Forms\AddNewSiteManagementMCARVerifyForm;
use PCK\Projects\Project;
use PCK\Verifier\Verifier;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;
use PCK\Base\Upload;

class SiteManagementDefectController extends \BaseController {

	private $tenderRepository;
	private $siteManagementDefectRepository;
	private $addNewSiteManagementDefectForm;
	private $addNewSiteManagementDefectFormResponse;
	private $addNewDefectBackchargeDetailForm;
	private $addNewSiteManagementMCARForm;
	private $addNewSiteManagementMCARFormResponse;
	private $addNewSiteManagementMCARVerifyForm;

	public function __construct(TenderRepository $tenderRepository,
		SiteManagementDefectRepository $siteManagementDefectRepository,
		AddNewSiteManagementDefectForm $addNewSiteManagementDefectForm,
		AddNewSiteManagementDefectFormResponse $addNewSiteManagementDefectFormResponse,
		AddNewDefectBackchargeDetailForm $addNewDefectBackchargeDetailForm,
		AddNewSiteManagementMCARForm $addNewSiteManagementMCARForm,
		AddNewSiteManagementMCARFormResponse $addNewSiteManagementMCARFormResponse,
		AddNewSiteManagementMCARVerifyForm $addNewSiteManagementMCARVerifyForm,
		VerifierController $verifierController)
	{
		$this->tenderRepository = $tenderRepository;
		$this->siteManagementDefectRepository = $siteManagementDefectRepository;
		$this->addNewSiteManagementDefectForm = $addNewSiteManagementDefectForm;
		$this->addNewSiteManagementDefectFormResponse = $addNewSiteManagementDefectFormResponse;
		$this->addNewDefectBackchargeDetailForm = $addNewDefectBackchargeDetailForm;
		$this->addNewSiteManagementMCARForm = $addNewSiteManagementMCARForm;
		$this->addNewSiteManagementMCARFormResponse = $addNewSiteManagementMCARFormResponse;
		$this->verifierController = $verifierController;
		$this->addNewSiteManagementMCARVerifyForm = $addNewSiteManagementMCARVerifyForm;
	}

	public function index($project)
	{
		$user = Confide::user();

		$query = SiteManagementDefectRepository::processQuery($user, $project);

		$records = $query->orderBy("id", "desc")->get();

		return View::make('site_management_defect.index', array('records'=>$records, 'project'=>$project, 'user'=>$user));
	}

	public function create($project)
	{
		$buildspaceProject = $project->getBsProjectMainInformation();

		$locations = ProjectStructureLocationCode::where("project_structure_id",$buildspaceProject->project_structure_id)
													->where("level", "0")
													->orderBy("lft", "asc")
												 	->orderBy("priority", "asc")
													->get();

		$sub_projects = Project::where("parent_project_id", $project->id)->get();

		$trades = ProjectLabourRate::getProjectTrades($project);

		$categories = DefectCategory::all();

		return View::make('site_management_defect.create', array('project'=>$project,'locations'=>$locations, 'trades' => $trades));
	}

	public function store($project)
	{
		$input = Input::all();

		$user = Confide::user();

		try
		{
			$this->addNewSiteManagementDefectForm->validate($input);
			$siteManagementDefect = $this->siteManagementDefectRepository->store($project, $input);

			$this->siteManagementDefectRepository->sendDefectFormSubmitNotification($project,$siteManagementDefect);

		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Defect Form is created successfully!");

        return Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function populateCategory($project)
	{
		$input = Input::all();
		$categoryArray = [];

        if(!empty($input['id']) && $input['id'] > 0)
        {
            $preDefinedLocationCode = PreDefinedLocationCode::find($input['id']);
            $records = DefectCategoryPreDefinedLocationCode::where("pre_defined_location_code_id", $preDefinedLocationCode->id)->get();
        }
		else
        {
            $records = [];
        }

        foreach ($records as $record)
        {
            $categoryArray[] = DefectCategory::find($record->defect_category_id);
        }

		return $categoryArray;
	}

	public function populateDefect($project)
	{
		$input = Input::all();
        $defectArray = [];

        if(!empty($input['id']) && $input['id'] > 0)
        {
            $defectCategory = DefectCategory::find($input['id']);
    		$defectArray = Defect::where("defect_category_id", $defectCategory->id)->get();
        }

		return $defectArray;
	}

	public function getLocationByLevel($project)
	{
		$input = Input::all();

        if(!empty($input['id']) && $input['id'] > 0)
        {
            $location = ProjectStructureLocationCode::find($input['id']);
            $incrementLevel = $location->level + 1;
            $nextLocation = ProjectStructureLocationCode::where("root_id", $location->root_id)
                ->where("lft", ">", $location->lft)
                ->where("rgt", "<", $location->rgt)
                ->where("level", $incrementLevel)
                ->orderBy("lft", "asc")
                ->orderBy("priority", "asc")
                ->get();
        }
        else
        {
            $incrementLevel = 0;
            $nextLocation   = [];
        }

		$data['currentLevel'] = $incrementLevel;
		$data['nextLocation'] = $nextLocation;

		return $data;
	}

	public function destroy($project, $form_id)
	{
		try
		{
			$record = SiteManagementDefect::find($form_id);
			$record->delete();
		}
		catch(Exception $e)
		{
			Flash::error("This form cannot be deleted.");

			return Redirect::route('site-management-defect.index', array('projectId' => $project->id));
		}

		Flash::success("This form is successfully deleted!");

		return Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function getResponse($project, $form_id)
	{
		$siteManagementDefect = SiteManagementDefect::find($form_id);
		$projectId = $project->id;

		$user = Confide::user();

		$uploadedFilesId = ModuleUploadedFile::where('uploadable_type', get_class($siteManagementDefect))->where('uploadable_id', $siteManagementDefect->id)->lists('upload_id');

		$uploadedItems = Upload::whereIn('id', $uploadedFilesId)->get();

		$verifiers = SiteManagementUserPermission::getAssignedPms($project,SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT);

		return View::make('site_management_defect.respond', array('record'=>$siteManagementDefect, 'responses'=>$siteManagementDefect->siteManagementDefectFormResponses, 'project'=>$project,'form_id'=>$form_id, 'user'=>$user, 'backcharges'=> $siteManagementDefect->siteManagementDefectBackchargeDetails, 'verifiers'=> $verifiers, 'uploadedItems'=>$uploadedItems));
	}

	public function storeResponse($project, $form_id)
	{
		$input = Input::all();

		$user = Confide::user();

		try
		{
			$this->addNewSiteManagementDefectFormResponse->validate($input);
			$this->siteManagementDefectRepository->storeDefectFormResponse($form_id, $input);

			$record = SiteManagementDefect::find($form_id);

			if(SiteManagementUserPermission::isSiteUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project))
			{
				$this->siteManagementDefectRepository->sendPicRepliedNotification($project,$record);
			}

			switch ($input['response'])
	        {
	            case SiteManagementDefectFormResponse::RESPONSE_ACCEPT:
	                $record->status_id = SiteManagementDefect::STATUS_CLOSED;
	                $this->siteManagementDefectRepository->sendDefectFormClosedNotification($project,$record);
	            break;

	            case SiteManagementDefectFormResponse::RESPONSE_REJECT:
	            	$countReject = $record->count_reject;

	            	$record->count_reject = $countReject +1;
	            	$record->status_id = SiteManagementDefect::STATUS_REJECT;
	            break;

	            case SiteManagementDefectFormResponse::RESPONSE_BACKCHARGE:
	                $record->status_id = SiteManagementDefect::STATUS_BACKCHARGE;
	                $this->siteManagementDefectRepository->sendBackchargeNotification($project,$record);
	            break;

	            case SiteManagementDefectFormResponse::RESPONSE_MCAR:
	                $record->mcar_status = SiteManagementMCAR::MCAR_SUBMIT_FORM;
	                if(!empty($record->pic_user_id))
	                {
	                	$this->siteManagementDefectRepository->sendMcarActivatedNotification($project,$record);
	                }
	            break;

	            case SiteManagementDefectFormResponse::RESPONSE_RESPOND:
	                $record->status_id = SiteManagementDefect::STATUS_RESPONDED;
	                $this->siteManagementDefectRepository->sendContractorRepliedNotification($project,$record);
	            break;
	        }

	        $record->save();
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Your response is recorded.");

        return Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function storeBackcharge($project, $form_id)
	{
		$input = Input::all();
		$user = Confide::user();

		try
		{
			$this->addNewDefectBackchargeDetailForm->validate($input);

			$defectBackchargeDetail = new SiteManagementDefectBackchargeDetail;
			$defectBackchargeDetail->machinery = $input['machinery'];
			$defectBackchargeDetail->material = $input['material'];
			$defectBackchargeDetail->labour = $input['labour'];
			$defectBackchargeDetail->total = $input['total'];
			$defectBackchargeDetail->user_id = $user->id;
			$defectBackchargeDetail->status_id = SiteManagementDefectBackchargeDetail::STATUS_BACKCHARGE_PENDING;
			$defectBackchargeDetail->site_management_defect_id = $form_id;
			$defectBackchargeDetail->save();

			$record = SiteManagementDefect::find($form_id);
			$record->status_id = SiteManagementDefect::STATUS_BACKCHARGE_PENDING;
			$record->save();

			$hasVerifiers = false;
			foreach($input['verifiers'] as $verifier)
			{
				if(!empty($verifier))
				{
					$hasVerifiers = true;
					break;
				}
			}

			if($hasVerifiers)
			{
				Verifier::setVerifiers($input['verifiers'] ?? array(), $defectBackchargeDetail);
				$this->verifierController->executeFollowUp($defectBackchargeDetail);
			}
			else
			{
				$this->siteManagementDefectRepository->sendWithoutVerifierNotification($project,$record);
				$defectBackchargeDetail->status_id = SiteManagementDefectBackchargeDetail::STATUS_BACKCHARGE_SUBMITTED;
				$defectBackchargeDetail->save();

				$record->status_id = SiteManagementDefect::STATUS_BACKCHARGE_SUBMITTED;
				$record->save();
			}
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Thank you! Your response is recorded.");

        return Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function showBackcharge($project, $backchargeId)
	{
		$defectBackchargeDetail = SiteManagementDefectBackchargeDetail::find($backchargeId);

		$user = Confide::user();

		foreach($verifierRecords = \PCK\Verifier\Verifier::getAssignedVerifierRecords($defectBackchargeDetail) as $record)
        {
            $selectedVerifiers[] = $record->verifier;
        }

        $verifiers = SiteManagementUserPermission::getAssignedPms($project,SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT);

        return View::make('site_management_defect.showBackcharge', array('defectBackchargeDetail'=>$defectBackchargeDetail,
						  'project'=>$project,'user'=>$user, 'verifiers'=> $verifiers, 'selectedVerifiers'=> $selectedVerifiers, 'verifierRecords'=>$verifierRecords));
	}

	public function assignPIC($project, $form_id)
	{
		$record = SiteManagementDefect::find($form_id);
		$user = Confide::user();

		$uploadedFilesId = ModuleUploadedFile::where('uploadable_type', get_class($record))->where('uploadable_id', $record->id)->lists('upload_id');

		$uploadedItems = Upload::whereIn('id', $uploadedFilesId)->get();

		$siteUsers = SiteManagementUserPermission::where("project_id",$project->id)
					->where('site', '=', true)
					->where('module_identifier', '=', SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT)
					->get();

		return View::make('site_management_defect.assignPIC', array('record'=>$record, 'project'=>$project,'form_id'=>$form_id, 'user'=>$user,'siteUsers'=>$siteUsers, 'uploadedItems'=>$uploadedItems));

	}

	public function postAssignPIC($project, $form_id)
	{
		$record = SiteManagementDefect::find($form_id);
		$input = Input::all();

		$rules = array(
	        'site' => 'required',
	    );

	    $validator = Validator::make($input, $rules);

	    if ($validator->fails())
	    {
	    	$messages = $validator->messages();
	        return Redirect::to(URL::previous())
                ->withErrors($messages);
	    }

		$record->pic_user_id = $input['site'];
		$record->save();

		$this->siteManagementDefectRepository->sendPicAssignedNotification($project,$record);

		Flash::success("Defect PIC is assigned.");

        return Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function createMCAR($project, $form_id)
	{
		$user = Confide::user();

		$record = SiteManagementDefect::find($form_id);

		$contractor = $record->company;

		$mcarNumber = $this->siteManagementDefectRepository->generateUniqueMcarNumber();

		return View::make('site_management_defect.createMCAR', array('project'=>$project,'form_id'=>$form_id, 'mcarNumber'=>$mcarNumber, 'user'=>$user, 'contractor'=> $contractor));
	}

	public function postCreateMCAR($project, $form_id)
	{
		$input = Input::all();

		$user = Confide::user();

		try
		{
			$this->addNewSiteManagementMCARForm->validate($input);

			$siteManagementMCAR = new SiteManagementMCAR;
			$siteManagementMCAR->project_id = $input['project'];
			$siteManagementMCAR->mcar_number = $input['mcar_number'];
			$siteManagementMCAR->contractor_id = $input['sub_con']??NULL;
			$siteManagementMCAR->work_description = $input['work_description'];
			$siteManagementMCAR->remark = $input['remark'];
			$siteManagementMCAR->site_management_defect_id = $form_id;
			$siteManagementMCAR->submitted_user_id = $user->id;
			$siteManagementMCAR->save();

			$siteManagementDefect = SiteManagementDefect::find($form_id);
			$siteManagementDefect->mcar_status = SiteManagementMCAR::MCAR_PENDING_REPLY;
			$siteManagementDefect->save();

			$this->siteManagementDefectRepository->sendMcarSubmittedNotification($project,$siteManagementDefect);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Thank you! Your response is recorded.");

        return Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function replyMCAR($project, $form_id)
	{
		$siteManagementMCAR = SiteManagementMCAR::where("site_management_defect_id",$form_id)->first();
		$user = Confide::user();

		return View::make('site_management_defect.replyMCAR', array('record'=>$siteManagementMCAR,'response'=>$siteManagementMCAR->MCARFormResponse,'project'=>$project,'form_id'=>$form_id, 'user'=>$user));

	}

	public function postReplyMCAR($project, $form_id)
	{
		$input = Input::all();
		$user = Confide::user();
		$siteManagementMCAR = SiteManagementMCAR::where("site_management_defect_id",$form_id)->first();

        $input['commitment_date'] = $project->getAppTimeZoneTime(\Carbon\Carbon::parse($input['commitment_date']) ?? null);

		try
		{
			$this->addNewSiteManagementMCARFormResponse->validate($input);
			$siteManagementMCARFormResponse = new SiteManagementMCARFormResponse;
			$siteManagementMCARFormResponse->cause = $input['cause'];
			$siteManagementMCARFormResponse->action = $input['action'];
			$siteManagementMCARFormResponse->applicable = $input['applicable'];
			$siteManagementMCARFormResponse->corrective = $input['corrective']??NULL;
			$siteManagementMCARFormResponse->commitment_date = $input['commitment_date'];
			$siteManagementMCARFormResponse->submitted_user_id = $user->id;
			$siteManagementMCARFormResponse->site_management_defect_id = $form_id;
			$siteManagementMCARFormResponse->site_management_mcar_id = $siteManagementMCAR->id;
			$siteManagementMCARFormResponse->save();

			$siteManagementDefect = SiteManagementDefect::find($form_id);
			$siteManagementDefect->mcar_status = SiteManagementMCAR::MCAR_PENDING_VERIFY;
			$siteManagementDefect->save();

			$this->siteManagementDefectRepository->sendMcarRepliedNotification($project,$siteManagementDefect);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Thank you! Your response is recorded.");

        return Redirect::route('site-management-defect.index', array('projectId' => $project->id));
	}

	public function verifyMCAR($project, $form_id)
	{
		$input = Input::all();

        $input['reinspection_date'] = $project->getAppTimeZoneTime(\Carbon\Carbon::parse($input['reinspection_date']) ?? null);

		$user = Confide::user();

		if(empty($input['reinspection_date']))
		{
			$reinspection_date = NULL;
		}
		else
		{
			$reinspection_date = $input['reinspection_date'];
		}

		try
		{
			$this->addNewSiteManagementMCARVerifyForm->validate($input);

			$siteManagementMCAR = SiteManagementMCAR::where("site_management_defect_id",$form_id)->first();

			$siteManagementMCAR->MCARFormResponse->comment = $input['comment'];
			$siteManagementMCAR->MCARFormResponse->satisfactory = $input['satisfactory'];
			$siteManagementMCAR->MCARFormResponse->reinspection_date = $reinspection_date;
			$siteManagementMCAR->MCARFormResponse->verified = true;
			$siteManagementMCAR->MCARFormResponse->verified_at = Carbon::now();
			$siteManagementMCAR->MCARFormResponse->verifier_id = $user->id;
			$siteManagementMCAR->MCARFormResponse->save();

			$siteManagementDefect = SiteManagementDefect::find($form_id);
		    $siteManagementDefect->mcar_status = SiteManagementMCAR::MCAR_VERIFIED;
			$siteManagementDefect->save();

			$this->siteManagementDefectRepository->sendMcarSiteVerifiedNotification($project,$siteManagementDefect);

			Flash::success("Thank you! Your response is recorded.");

	        return Redirect::route('site-management-defect.index', array('projectId' => $project->id));
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }
	}

	public function printMCAR($project, $form_id)
	{
		$siteManagementMCAR = SiteManagementMCAR::where("site_management_defect_id",$form_id)->first();

        return PDF::html('site_management_defect.printMCAR', array(
            'MCARRecord'         => $siteManagementMCAR,
            'MCARFormResponse'   => $siteManagementMCAR->MCARFormResponse,
            'project'			 => $project
        ));
	}
}
