<?php

use PCK\DailyLabourReports\DailyLabourReport;
use PCK\DailyLabourReports\DailyLabourReportRepository;
use PCK\DailyLabourReports\DailyLabourReportLabourRate;
use PCK\DailyLabourReports\ProjectLabourRate;
use PCK\Forms\DailyLabourReportForm;
use PCK\Weathers\Weather;
use PCK\Buildspace\PreDefinedLocationCode;
use PCK\Buildspace\Project as BsProjectStructure;
use PCK\Buildspace\ProjectStructureLocationCode;
use PCK\Buildspace\BillColumnSetting;
use PCK\Buildspace\ProjectMainInformation as BsProjectMainInformation;
use PCK\Projects\Project;
use PCK\Tenders\TenderRepository;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;
use PCK\Base\Upload;
use PCK\Contracts\Contract;
use PCK\ProjectDetails\PAM2006ProjectDetail;
use PCK\ProjectDetails\IndonesiaCivilContractInformation;
use PCK\Exceptions\ValidationException;

class DailyLabourReportsController extends \BaseController {

	private $dailyLabourReportRepository;
	private $tenderRepository;
	private $form;

	public function __construct(TenderRepository $tenderRepository, DailyLabourReportRepository $dailyLabourReportRepository, DailyLabourReportForm $form)
	{
		$this->tenderRepository = $tenderRepository;
		$this->dailyLabourReportRepository = $dailyLabourReportRepository;
		$this->form = $form;
	}

	public function index($project)
	{
		$user = Confide::user();

		$query = DailyLabourReportRepository::processQuery($user, $project);

		$records = $query->orderBy("id", "desc")->get();

		return View::make('daily_labour_reports.index', array('records'=>$records, 'project'=>$project, 'user'=>$user));
	}

	public function create($project)
	{
		$buildspaceProject = $project->getBsProjectMainInformation();

		$locations = ProjectStructureLocationCode::where("project_structure_id",$buildspaceProject->project_structure_id)
													->where("level", "0")
													->orderBy("lft", "asc")
												 	->orderBy("priority", "asc")
													->get();

		$trades = ProjectLabourRate::getProjectTrades($project);

		$weathers = Weather::all();

		return View::make('daily_labour_reports.create', array('weathers'=>$weathers,'project'=>$project,'locations'=>$locations, 'trades' => $trades));
	}

	public function store($project)
	{
		$inputs = Input::all();

        $inputs['date'] = $project->getAppTimeZoneTime(\Carbon\Carbon::parse($inputs['date']) ?? null);

		try
		{
			$this->form->validate($inputs);
			$this->dailyLabourReportRepository->store($project, $inputs);

		}
		catch (ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }

        Flash::success("Daily Labour Report is successfully created!");

        return Redirect::route('daily-labour-report.index', array('projectId' => $project->id));
	}

	public function show($project, $formId)
	{
		$dailyLabourReport = DailyLabourReport::find($formId);

		$uploadedFilesId = ModuleUploadedFile::where('uploadable_type', get_class($dailyLabourReport))->where('uploadable_id', $dailyLabourReport->id)->lists('upload_id');

		$uploadedItems = Upload::whereIn('id', $uploadedFilesId)->get();

		return View::make('daily_labour_reports.show', array('dailyLabourReport'=> $dailyLabourReport, 'project'=>$project, 'uploadedItems'=> $uploadedItems));
	}

	public function populateProjectLabourRate($project)
	{
		$input = Input::all();
		return (!empty($input['id']) && $input['id'] > 0) ? ProjectLabourRate::getProjectLabourRateRecords($project, $input['id']) : [];
	}

	public function populatePostContractProjectLabourRate($project)
	{
		$input = Input::all();
		return (!empty($input['id']) && $input['id'] > 0) ? ProjectLabourRate::getProjectLabourRateRecords($project, $input['id']) : [];
	}

	public function populateContractor($project)
	{
		$input = Input::all();
		return (!empty($input['id']) && $input['id'] > 0) ? ProjectLabourRate::getMappedContractorId($project, $input['id']) : [];
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
}
