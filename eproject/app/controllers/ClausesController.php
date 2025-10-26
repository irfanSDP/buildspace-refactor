<?php

use PCK\Forms\ClauseForm;
use PCK\Contracts\Contract;
use PCK\Clauses\ClauseRepository;

class ClausesController extends \BaseController {

    private $clauseRepo;

    private $clauseForm;

    public function __construct(ClauseRepository $clauseRepo, ClauseForm $clauseForm)
    {
        $this->clauseRepo = $clauseRepo;
        $this->clauseForm = $clauseForm;
    }

    public function index($contractId)
    {
        $contract = Contract::find($contractId);
        $clauses  = $contract->clauses;

        return View::make('clauses.index', compact('contract', 'clauses'));
    }

    public function create($contractId)
    {
        $contract      = \PCK\Contracts\Contract::find($contractId);
        $contractTypes = Contract::orderBy('name', 'asc')->lists('name', 'id');

        return View::make('clauses.create', compact('contract', 'contractTypes'));
    }

    public function store($contractId)
    {
        $input = Input::all();

        $this->clauseForm->validate($input);

        $this->clauseRepo->add($input);

        return Redirect::route('clauses.index', array( $contractId ));
    }

    public function edit($contractId, $id)
    {
        $user          = \Confide::user();
        $contract      = Contract::find($contractId);
        $clause        = $this->clauseRepo->find($id);
        $contractTypes = Contract::orderBy('name', 'asc')->lists('name', 'id');

        return View::make('clauses.edit', compact('user', 'contract', 'clause', 'contractTypes'));
    }

    public function update($contractId, $id)
    {
        $clause = $this->clauseRepo->find($id);

        $input = Input::all();

        $this->clauseForm->validate($input);

        $clause = $this->clauseRepo->update($clause, $input);

        Flash::success("Clause {$clause->name} successfully updated!");

        return Redirect::route('clauses', array( $contractId ));
    }

}