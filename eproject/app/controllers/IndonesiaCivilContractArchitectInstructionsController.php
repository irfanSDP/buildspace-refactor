<?php

use PCK\Clauses\Clause;
use PCK\Clauses\ClauseRepository;
use PCK\Forms\Contracts\IndonesiaCivilContract\AddNewArchitectInstructionForm;
use PCK\Forms\Contracts\IndonesiaCivilContract\ArchitectInstructionResponseForm;
use PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstruction;
use PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstructionRepository;
use PCK\ProjectModulePermission\ProjectModulePermission;
use PCK\Projects\Project;

class IndonesiaCivilContractArchitectInstructionsController extends \BaseController {

    private $aiRepo;
    private $clauseRepository;
    private $addForm;
    private $user;
    private $responseForm;
    private $verifierController;

    public function __construct
    (
        ArchitectInstructionRepository $aiRepo,
        ClauseRepository $clauseRepository,
        AddNewArchitectInstructionForm $addForm,
        ArchitectInstructionResponseForm $responseForm,
        VerifierController $verifierController
    )
    {
        $this->aiRepo             = $aiRepo;
        $this->clauseRepository   = $clauseRepository;
        $this->addForm            = $addForm;
        $this->user               = Confide::user();
        $this->responseForm       = $responseForm;
        $this->verifierController = $verifierController;
    }

    public function index($project)
    {
        $ais = $this->aiRepo->all($project);

        return View::make('indonesia_civil_contract.architect_instructions.index', compact('project', 'ais'));
    }

    public function create($project)
    {
        $clause = $this->clauseRepository->findItemsWithClauseByType($project->contract->id, Clause::TYPE_ARCHITECT_INSTRUCTION);

        $verifiers = ProjectModulePermission::getVerifiers($project, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_ARCHITECT_INSTRUCTION);

        $requestsForInformation = array();

        foreach($project->getVisibleRequestsForInformation() as $request)
        {
            $requestsForInformation[ $request->id ] = $request->reference;
        }

        $uploadedFiles = $this->getAttachmentDetails();

        return View::make('indonesia_civil_contract.architect_instructions.create', compact('project', 'clause', 'requestsForInformation', 'uploadedFiles', 'verifiers'));
    }

    public function store(Project $project)
    {
        $input = Input::all();

        $inputs['deadline_to_comply'] = $project->getAppTimeZoneTime($inputs['deadline_to_comply'] ?? null);

        $this->addForm->setProject($project);
        $this->addForm->validate($input);

        $ai = $this->aiRepo->add($project, $input);

        if( $ai->status == ArchitectInstruction::STATUS_PENDING ) $this->verifierController->executeFollowUp($ai);

        Flash::success(trans('architectInstructions.aiAdded', array( 'reference' => $ai->reference )));

        return Redirect::route('indonesiaCivilContract.architectInstructions', array( $project->id ));
    }

    public function show($project, $aiId)
    {
        $user                   = $this->user;
        $ai                     = $this->aiRepo->findWithMessages($project, $aiId);
        $requestsForInformation = array();

        foreach($project->getVisibleRequestsForInformation() as $request)
        {
            $requestsForInformation[ $request->id ] = $request->reference;
        }

        $preSelectedRfi = $ai->requestsForInformation->lists('id');

        $isEditor          = $user->isEditor($project);
        $clause            = array();
        $selectedClauseIds = $ai->attachedClauses()->lists('origin_id');
        $uploadedFiles     = array();

        $selectedVerifiers = array();

        foreach($verifierRecords = \PCK\Verifier\Verifier::getAssignedVerifierRecords($ai, true) as $record)
        {
            if( $record->deleted_at ) continue;

            $selectedVerifiers[] = $record->verifier;
        }

        if( $ai->status == ArchitectInstruction::STATUS_DRAFT )
        {
            $clause = $this->clauseRepository->findItemsWithClauseByType($project->contract->id, Clause::TYPE_ARCHITECT_INSTRUCTION);

            $uploadedFiles = $this->getAttachmentDetails($ai);
        }

        $verifiers = ProjectModulePermission::getVerifiers($project, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_ARCHITECT_INSTRUCTION);

        return View::make('indonesia_civil_contract.architect_instructions.show', compact('project', 'ai', 'clause', 'selectedClauseIds', 'uploadedFiles', 'requestsForInformation', 'user', 'isEditor', 'preSelectedRfi', 'selectedVerifiers', 'verifiers', 'verifierRecords'));
    }

    public function update($project, $aiId)
    {
        $user   = $this->user;
        $inputs = Input::all();

        $inputs['deadline_to_comply'] = $project->getAppTimeZoneTime($inputs['deadline_to_comply'] ?? null);

        $ai = $this->aiRepo->find($project, $aiId);

        $this->addForm->setProject($project);
        $this->addForm->setModel($ai);
        $this->addForm->validate($inputs);

        $this->aiRepo->update($user, $ai, $inputs);

        if( $ai->status == ArchitectInstruction::STATUS_PENDING ) $this->verifierController->executeFollowUp($ai);

        Flash::success(trans('architectInstructions.aiUpdated', array( 'reference' => $ai->reference )));

        return Redirect::route('indonesiaCivilContract.architectInstructions', array( $project->id ));
    }

    public function destroy($project, $aiId)
    {
        $ai = $this->aiRepo->find($project, $aiId);

        $this->aiRepo->delete($ai);

        Flash::success(trans('architectInstructions.aiDeleted', array( 'reference' => $ai->reference )));

        return Redirect::route('indonesiaCivilContract.architectInstructions', array( $project->id ));
    }

    public function submitResponse($project, $aiId)
    {
        $input = Input::all();

        $this->responseForm->validate($input);

        $ai = $this->aiRepo->find($project, $aiId);

        $this->aiRepo->submitResponse($ai, $this->user, $input);

        Flash::success(trans('architectInstructions.responseSubmitted', array( 'reference' => $ai->reference )));

        return Redirect::back();
    }

}