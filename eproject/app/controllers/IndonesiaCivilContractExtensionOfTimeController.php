<?php

use PCK\Clauses\Clause;
use PCK\Clauses\ClauseRepository;
use PCK\Forms\Contracts\IndonesiaCivilContract\ExtensionOfTimeForm;
use PCK\Forms\Contracts\IndonesiaCivilContract\ExtensionOfTimeResponseForm;
use PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstructionRepository;
use PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse;
use PCK\IndonesiaCivilContract\EarlyWarning\EarlyWarningRepository;
use PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTime;
use PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTimeRepository;
use PCK\Projects\Project;

class IndonesiaCivilContractExtensionOfTimeController extends \BaseController {

    private $clauseRepository;
    private $user;
    private $ewRepo;
    private $aiRepo;
    private $eotRepo;
    private $eotForm;
    private $eotResponseForm;

    public function __construct
    (
        ExtensionOfTimeRepository $eotRepo,
        ExtensionOfTimeForm $eotForm,
        ExtensionOfTimeResponseForm $eotResponseForm,
        EarlyWarningRepository $ewRepo,
        ArchitectInstructionRepository $aiRepo,
        ClauseRepository $clauseRepository
    )
    {
        $this->clauseRepository = $clauseRepository;
        $this->user             = Confide::user();
        $this->ewRepo           = $ewRepo;
        $this->aiRepo           = $aiRepo;
        $this->eotRepo          = $eotRepo;
        $this->eotForm          = $eotForm;
        $this->eotResponseForm  = $eotResponseForm;
    }

    public function index($project)
    {
        $eots = $this->eotRepo->all($project);

        return View::make('indonesia_civil_contract.extension_of_time.index', compact('project', 'eots'));
    }

    public function create(Project $project)
    {
        $clause = $this->clauseRepository->findItemsWithClauseByType($project->contract->id, Clause::TYPE_EXTENSION_OF_TIME);

        $ais = $this->aiRepo->selectList($project);

        $warnings = array();

        foreach($this->ewRepo->all($project) as $warning)
        {
            $warnings[ $warning->id ] = $warning->reference;
        }

        $preSelectedWarnings = array( Request::get('ew') );
        $preSelectedAI       = Request::get('ai');

        $uploadedFiles = $this->getAttachmentDetails();

        return View::make('indonesia_civil_contract.extension_of_time.create', compact('project', 'clause', 'warnings', 'preSelectedWarnings', 'ais', 'preSelectedAI', 'uploadedFiles'));
    }

    public function store(Project $project)
    {
        $input = Input::all();

        $this->eotForm->setProject($project);
        $this->eotForm->validate($input);

        $eot = $this->eotRepo->add($project, $input);

        Flash::success(trans('extensionOfTime.eotAdded', array( 'reference' => $eot->reference )));

        return Redirect::route('indonesiaCivilContract.extensionOfTime', array( $project->id ));
    }

    public function show($project, $eotId)
    {
        $eot = $this->eotRepo->findWithMessages($project, $eotId);

        $ais = $this->aiRepo->selectList($project);

        $warnings = array();

        foreach($this->ewRepo->all($project) as $warning)
        {
            $warnings[ $warning->id ] = $warning->reference;
        }

        $preSelectedWarnings = $eot->earlyWarnings->lists('id');

        $clause            = array();
        $selectedClauseIds = $eot->attachedClauses()->lists('origin_id');
        $uploadedFiles     = array();

        if( $eot->status == ExtensionOfTime::STATUS_DRAFT )
        {
            $clause = $this->clauseRepository->findItemsWithClauseByType($project->contract->id, Clause::TYPE_EXTENSION_OF_TIME);

            $uploadedFiles = $this->getAttachmentDetails($eot);
        }

        return View::make('indonesia_civil_contract.extension_of_time.show', compact('project', 'eot', 'clause', 'selectedClauseIds', 'uploadedFiles', 'warnings', 'preSelectedWarnings', 'ais'));
    }

    public function update(Project $project, $eotId)
    {
        $user   = $this->user;
        $inputs = Input::all();

        $eot = ExtensionOfTime::find($eotId);

        $this->eotForm->setProject($project);
        $this->eotForm->setModel($eot);
        $this->eotForm->validate($inputs);

        $this->eotRepo->update($user, $eot, $inputs);

        Flash::success(trans('extensionOfTime.eotUpdated', array( 'reference' => $eot->reference )));

        return Redirect::route('indonesiaCivilContract.extensionOfTime', array( $project->id ));
    }

    public function destroy($project, $eotId)
    {
        $eot = ExtensionOfTime::find($eotId);

        $this->eotRepo->delete($eot);

        Flash::success(trans('extensionOfTime.eotDeleted', array( 'reference' => $eot->reference )));

        return Redirect::route('indonesiaCivilContract.extensionOfTime', array( $project->id ));
    }

    public function submitDecisionResponse($project, $eotId)
    {
        $input = Input::all();

        $this->eotResponseForm->validate($input);

        $eot = ExtensionOfTime::find($eotId);

        $this->eotRepo->submitResponse($eot, $this->user, $input);

        Flash::success(trans('extensionOfTime.responseSubmitted', array( 'reference' => $eot->reference )));

        return Redirect::back();
    }

    public function submitPlainResponse($project, $eotId)
    {
        $input = Input::all();

        $input['type'] = ContractualClaimResponse::TYPE_PLAIN;

        $this->eotResponseForm->validate($input);

        $eot = ExtensionOfTime::find($eotId);

        $this->eotRepo->submitResponse($eot, $this->user, $input);

        Flash::success(trans('extensionOfTime.responseSubmitted', array( 'reference' => $eot->reference )));

        return Redirect::back();
    }

}