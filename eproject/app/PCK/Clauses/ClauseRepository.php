<?php namespace PCK\Clauses;

class ClauseRepository {

    private $clause;

    public function __construct(Clause $clause)
    {
        $this->clause = $clause;
    }

    public function all()
    {
        return $this->clause->with('contract')->orderBy('id', 'desc')->get();
    }

    public function find($id)
    {
        return $this->clause->findOrFail($id);
    }

    public function findItemsWithClauseById($clauseId)
    {
        return $this->clause->with('items')->findOrFail($clauseId);
    }

    public function findItemsWithClauseByType($contractId, $type)
    {
        return $this->clause->with('items')->where('contract_id', '=', $contractId)->where('type', '=', $type)->first();
    }

    public function add(array $inputs)
    {
        $this->clause->contract_id = $inputs['contract_id'];
        $this->clause->name        = $inputs['name'];

        return $this->clause->save();
    }

    public function update(Clause $clause, $input)
    {
        $clause->contract_id = $input['contract_id'];
        $clause->name        = $input['name'];

        $clause->save();

        return $clause;
    }

}