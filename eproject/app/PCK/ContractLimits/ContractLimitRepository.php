<?php namespace PCK\ContractLimits;

class ContractLimitRepository {

    /**
     * Returns all Contract Limits.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all()
    {
        return ContractLimit::all();
    }

    /**
     * Find by id.
     *
     * @param $id
     *
     * @return \Illuminate\Support\Collection|null|static
     */
    public function find($id)
    {
        return ContractLimit::find($id);
    }

    /**
     * Find by limit.
     *
     * @param $limit
     *
     * @return mixed
     */
    public function findByLimit($limit)
    {
        return ContractLimit::where('limit', '=', trim($limit))->first();
    }

    /**
     * Get all resources.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll()
    {
        return ContractLimit::all();
    }

    public function store(array $input)
    {
        $limit = trim($input['limit']);

        if( empty( $limit ) ) return null;

        $contractLimit        = new ContractLimit;
        $contractLimit->limit = $input['limit'];

        $contractLimit->save();

        return $contractLimit;
    }

    public function saveOrFindContractLimit($input)
    {
        $createNewContractLimit = ( $input['setExisting'] == 'false' );

        $contractLimit = $input['contractLimitId'] ? $this->find($input['contractLimitId']) : null;

        if( $createNewContractLimit && ( ! $contractLimit = $this->findByLimit($input['contractLimit']) ) )
        {
            $contractLimit = $this->store(array( 'limit' => $input['contractLimit'] ));
        }

        return $contractLimit;
    }

}