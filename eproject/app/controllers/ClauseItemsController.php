<?php

use PCK\Clauses\ClauseRepository;
use PCK\ClauseItems\ClauseItemRepository;

class ClauseItemsController extends \BaseController {

    private $clauseRepo;

    private $clauseItemRepo;

    public function __construct(ClauseRepository $clauseRepo, ClauseItemRepository $clauseItemRepo)
    {
        $this->clauseRepo     = $clauseRepo;
        $this->clauseItemRepo = $clauseItemRepo;
    }

    public function index($contractId, $clauseId)
    {
        $contract = \PCK\Contracts\Contract::find($contractId);

        $clause = $this->clauseRepo->findItemsWithClauseById($clauseId);

        return View::make('clause_items.index', compact('contract', 'clause'));
    }

    public function create($contractId, $clauseId)
    {
        $currentUser = \Confide::user();
        $contract    = \PCK\Contracts\Contract::find($contractId);
        $clause      = $this->clauseRepo->find($clauseId);

        return View::make('clause_items.create', array(
            'contract'    => $contract,
            'pageTitle'   => 'Create New Clause Item',
            'currentUser' => $currentUser,
            'clause'      => $clause,
        ));
    }

    public function store($contractId, $clauseId)
    {
        $clause   = $this->clauseRepo->find($clauseId);
        $lastItem = $this->clauseItemRepo->findLastItem($clauseId);

        $input              = Input::all();
        $input['clause_id'] = $clause->id;
        $input['priority']  = ( $lastItem ) ? $lastItem->priority + 1 : 0;

        $this->clauseItemRepo->add($input);

        Flash::success("Item {$input['no']} successfully added!");

        return Redirect::route('clauses.items.index', array( $contractId, $clause->id ));
    }

    public function edit($contractId, $clauseId, $itemId)
    {
        $user     = \Confide::user();
        $contract = \PCK\Contracts\Contract::find($contractId);
        $clause   = $this->clauseRepo->find($clauseId);
        $item     = $this->clauseItemRepo->find($itemId);

        return View::make('clause_items.edit', compact('user', 'contract', 'clause', 'item'));
    }

    public function update($contractId, $clauseId, $itemId)
    {
        $clause             = $this->clauseRepo->find($clauseId);
        $item               = $this->clauseItemRepo->find($itemId);
        $input              = Input::all();
        $input['clause_id'] = $clause->id;

        $item = $this->clauseItemRepo->update($item, $input);

        Flash::success("Item {$item->no} successfully updated!");

        return Redirect::route('clauses.items.index', array( $contractId, $clause->id ));
    }

    public function moveToUp($contractId, $clauseId, $itemId)
    {
        $item         = $this->clauseItemRepo->find($itemId);
        $previousItem = $this->clauseItemRepo->findItemByPriorityAndClauseId($clauseId, $item->priority - 1);

        $this->clauseItemRepo->setPriority($previousItem, $item->priority);

        $item = $this->clauseItemRepo->setPriority($item, $item->priority - 1);

        Flash::success("Clause Item '{$item->no}' successfully updated!");

        return Redirect::route('clauses.items.index', array( $contractId, $clauseId ));
    }

    public function moveToBottom($contractId, $clauseId, $itemId)
    {
        $item     = $this->clauseItemRepo->find($itemId);
        $nextItem = $this->clauseItemRepo->findItemByPriorityAndClauseId($clauseId, $item->priority + 1);

        $this->clauseItemRepo->setPriority($nextItem, $item->priority);

        $item = $this->clauseItemRepo->setPriority($item, $item->priority + 1);

        Flash::success("Clause Item '{$item->no}' successfully updated!");

        return Redirect::route('clauses.items.index', array( $contractId, $clauseId ));
    }

}