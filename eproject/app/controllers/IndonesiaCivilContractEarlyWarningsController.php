<?php

use PCK\Forms\Contracts\IndonesiaCivilContract\EarlyWarningForm;
use PCK\IndonesiaCivilContract\EarlyWarning\EarlyWarning;
use PCK\IndonesiaCivilContract\EarlyWarning\EarlyWarningRepository;
use PCK\Projects\Project;

class IndonesiaCivilContractEarlyWarningsController extends \BaseController {

    private $ewRepo;
    private $form;

    public function __construct
    (
        EarlyWarningRepository $ewRepo,
        EarlyWarningForm $form
    )
    {
        $this->ewRepo = $ewRepo;
        $this->form   = $form;
    }

    public function index($project)
    {
        $ews = $this->ewRepo->all($project);

        return View::make('indonesia_civil_contract.early_warnings.index', compact('project', 'ews'));
    }

    public function create($project)
    {
        return View::make('indonesia_civil_contract.early_warnings.create', compact('project', 'clause', 'requestsForInformation', 'uploadedFiles'));
    }

    public function store(Project $project)
    {
        $input = Input::all();

        $input['commencement_date'] = $project->getAppTimeZoneTime($input['commencement_date'] ?? null);

        $this->form->setProject($project);
        $this->form->validate($input);

        $ew = $this->ewRepo->add($project, $input);

        Flash::success(trans('earlyWarnings.warningIssued', array( 'reference' => $ew->reference )));

        return Redirect::route('indonesiaCivilContract.earlyWarning', array( $project->id ));
    }

    public function show($project, $ewId)
    {
        $ew = EarlyWarning::find($ewId);

        return View::make('indonesia_civil_contract.early_warnings.show', compact('project', 'ew'));
    }

}