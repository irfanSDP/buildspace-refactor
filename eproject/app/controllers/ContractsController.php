<?php

use PCK\Forms\ClauseForm;
use PCK\Contracts\Contract;
use PCK\Clauses\ClauseRepository;

class ContractsController extends \BaseController {

    private $clauseRepo;

    private $clauseForm;

    public function __construct(ClauseRepository $clauseRepo, ClauseForm $clauseForm)
    {
        $this->clauseRepo = $clauseRepo;
        $this->clauseForm = $clauseForm;
    }

    public function index()
    {
        return View::make('contracts.index', array(
            'contracts' => Contract::orderBy('id', 'desc')->get(),
        ));
    }

}