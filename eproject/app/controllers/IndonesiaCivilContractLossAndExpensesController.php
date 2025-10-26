<?php

use PCK\Clauses\Clause;
use PCK\Clauses\ClauseRepository;
use PCK\Forms\Contracts\IndonesiaCivilContract\LossAndExpensesForm;
use PCK\Forms\Contracts\IndonesiaCivilContract\LossAndExpensesResponseForm;
use PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstructionRepository;
use PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse;
use PCK\IndonesiaCivilContract\EarlyWarning\EarlyWarningRepository;
use PCK\IndonesiaCivilContract\LossAndExpense\LossAndExpense;
use PCK\IndonesiaCivilContract\LossAndExpense\LossAndExpenseRepository;
use PCK\Projects\Project;

class IndonesiaCivilContractLossAndExpensesController extends \BaseController {

    private $clauseRepository;
    private $user;
    private $leRepo;
    private $ewRepo;
    private $leForm;
    private $leResponseForm;
    private $aiRepo;

    public function __construct
    (
        LossAndExpenseRepository $leRepo,
        EarlyWarningRepository $ewRepo,
        LossAndExpensesForm $leForm,
        LossAndExpensesResponseForm $leResponseForm,
        ArchitectInstructionRepository $aiRepo,
        ClauseRepository $clauseRepository
    )
    {
        $this->clauseRepository = $clauseRepository;
        $this->user             = Confide::user();
        $this->leRepo           = $leRepo;
        $this->ewRepo           = $ewRepo;
        $this->leForm           = $leForm;
        $this->leResponseForm   = $leResponseForm;
        $this->aiRepo           = $aiRepo;
    }

    public function index($project)
    {
        $les = $this->leRepo->all($project);

        return View::make('indonesia_civil_contract.loss_and_expenses.index', compact('project', 'les'));
    }

    public function create(Project $project)
    {
        $clause = $this->clauseRepository->findItemsWithClauseByType($project->contract->id, Clause::TYPE_LOSS_AND_EXPENSES);

        $ais = $this->aiRepo->selectList($project);

        $warnings = array();

        foreach($this->ewRepo->all($project) as $warning)
        {
            $warnings[ $warning->id ] = $warning->reference;
        }

        $preSelectedWarnings = array( Request::get('ew') );
        $preSelectedAI       = Request::get('ai');

        $uploadedFiles = $this->getAttachmentDetails();

        return View::make('indonesia_civil_contract.loss_and_expenses.create', compact('project', 'clause', 'warnings', 'preSelectedWarnings', 'ais', 'preSelectedAI', 'uploadedFiles'));
    }

    public function store(Project $project)
    {
        $input = Input::all();

        $this->leForm->setProject($project);
        $this->leForm->validate($input);

        $le = $this->leRepo->add($project, $input);

        Flash::success(trans('lossAndExpenses.leAdded', array( 'reference' => $le->reference )));

        return Redirect::route('indonesiaCivilContract.lossOrAndExpenses', array( $project->id ));
    }

    public function show($project, $leId)
    {
        $le = $this->leRepo->findWithMessages($project, $leId);

        $ais = $this->aiRepo->selectList($project);

        $warnings = array();

        foreach($this->ewRepo->all($project) as $warning)
        {
            $warnings[ $warning->id ] = $warning->reference;
        }

        $preSelectedWarnings = $le->earlyWarnings->lists('id');

        $clause            = array();
        $selectedClauseIds = $le->attachedClauses()->lists('origin_id');
        $uploadedFiles     = array();

        if( $le->status == LossAndExpense::STATUS_DRAFT )
        {
            $clause = $this->clauseRepository->findItemsWithClauseByType($project->contract->id, Clause::TYPE_LOSS_AND_EXPENSES);

            $uploadedFiles = $this->getAttachmentDetails($le);
        }

        return View::make('indonesia_civil_contract.loss_and_expenses.show', compact('project', 'le', 'clause', 'selectedClauseIds', 'uploadedFiles', 'warnings', 'preSelectedWarnings', 'ais'));
    }

    public function update($project, $leId)
    {
        $user   = $this->user;
        $inputs = Input::all();

        $le = LossAndExpense::find($leId);

        $this->leForm->setProject($project);
        $this->leForm->setModel($le);
        $this->leForm->validate($inputs);

        $this->leRepo->update($user, $le, $inputs);

        Flash::success(trans('lossAndExpenses.leUpdated', array( 'reference' => $le->reference )));

        return Redirect::route('indonesiaCivilContract.lossOrAndExpenses', array( $project->id ));
    }

    public function destroy($project, $leId)
    {
        $le = LossAndExpense::find($leId);

        $this->leRepo->delete($le);

        Flash::success(trans('lossAndExpenses.leDeleted', array( 'reference' => $le->reference )));

        return Redirect::route('indonesiaCivilContract.lossOrAndExpenses', array( $project->id ));
    }

    public function submitDecisionResponse($project, $leId)
    {
        $input = Input::all();

        $this->leResponseForm->validate($input);

        $le = LossAndExpense::find($leId);

        $this->leRepo->submitResponse($le, $this->user, $input);

        Flash::success(trans('lossAndExpenses.responseSubmitted', array( 'reference' => $le->reference )));

        return Redirect::back();
    }

    public function submitPlainResponse($project, $leId)
    {
        $input = Input::all();

        $input['type'] = ContractualClaimResponse::TYPE_PLAIN;

        $this->leResponseForm->validate($input);

        $le = LossAndExpense::find($leId);

        $this->leRepo->submitResponse($le, $this->user, $input);

        Flash::success(trans('lossAndExpenses.responseSubmitted', array( 'reference' => $le->reference )));

        return Redirect::back();
    }

}