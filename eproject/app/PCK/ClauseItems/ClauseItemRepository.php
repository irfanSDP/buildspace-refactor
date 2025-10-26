<?php namespace PCK\ClauseItems;

class ClauseItemRepository {

	/**
	 * @var ClauseItem
	 */
	private $item;

	public function __construct(ClauseItem $item)
	{
		$this->item = $item;
	}

	/**
	 * Get available ClauseItems listing
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function all()
	{
		return $this->item->orderBy('id', 'desc')->get();
	}

	/**
	 * Find item's related information by ID
	 *
	 * @param $id
	 * @return \Illuminate\Support\Collection|static
	 */
	public function find($id)
	{
		return $this->item->findOrFail($id);
	}


	public function findLastItem($clauseId)
	{
		return $this->item->where('clause_id', $clauseId)->orderBy('priority', 'desc')->first();
	}


	public function findItemByPriorityAndClauseId($clauseId, $priority)
	{
		return $this->item->where('clause_id', $clauseId)->where('priority', $priority)->first();
	}

	/**
	 * Create new record of item
	 *
	 * @param $inputs
	 * @return bool
	 */
	public function add(array $inputs)
	{
		$this->item->no          = $inputs['no'];
		$this->item->description = $inputs['description'];
		$this->item->clause_id   = $inputs['clause_id'];
		$this->item->priority    = $inputs['priority'];

		return $this->item->save();
	}

	/**
	 * Update existing record of selected item
	 *
	 * @param ClauseItem $item
	 * @param            $input
	 * @return ClauseItem
	 */
	public function update(ClauseItem $item, $input)
	{
		$item->no          = $input['no'];
		$item->description = $input['description'];
		$item->clause_id   = $input['clause_id'];

		$item->save();

		return $item;
	}

	public function setPriority(ClauseItem $item, $priority)
	{
		$item->priority = $priority;

		$item->save();

		return $item;
	}

}