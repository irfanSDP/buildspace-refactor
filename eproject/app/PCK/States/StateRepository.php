<?php namespace PCK\States;

class StateRepository {

	/**
	 * @var State
	 */
	private $state;

	public function __construct(State $state)
	{
		$this->state = $state;
	}

	/**
	 * Get available States listing
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function all()
	{
		return $this->state->orderBy('id', 'desc')->get();
	}

	/**
	 * Find state's related information by ID
	 *
	 * @param $id
	 * @return \Illuminate\Support\Collection|static
	 */
	public function find($id)
	{
		return $this->state->findOrFail($id);
	}

	/**
	 * Create new record of state
	 *
	 * @param $inputs
	 * @return bool
	 */
	public function add(array $inputs)
	{
		$this->state->name       = $inputs['name'];
		$this->state->timezone   = $inputs['timezone'];
		$this->state->country_id = $inputs['country_id'];

		return $this->state->save();
	}

	/**
	 * Update existing record of selected state
	 *
	 * @param State   $state
	 * @param         $input
	 * @return State
	 */
	public function update(State $state, $input)
	{
		$state->name       = $input['name'];
		$state->timezone   = $input['timezone'];
		$state->country_id = $input['country_id'];

		$state->save();

		return $state;
	}

}