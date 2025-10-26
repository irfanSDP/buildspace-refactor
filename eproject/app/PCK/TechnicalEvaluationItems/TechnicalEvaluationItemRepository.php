<?php namespace PCK\TechnicalEvaluationItems;

use PCK\TechnicalEvaluationItems\TechnicalEvaluationItem as Item;

class TechnicalEvaluationItemRepository {

    /**
     * Creates and saves a new resource.
     *
     * @param      $input
     * @param bool $returnResource
     *
     * @return bool
     */
    public function store($input, $returnResource = false)
    {
        $resource = $this->createNew($input);

        if( ! $returnResource ) return $resource->save();

        $resource->save();

        return $resource;
    }

    /**
     * Creates a new resource without saving it.
     *
     * @param $input
     *
     * @return TechnicalEvaluationItem
     */
    public function createNew($input)
    {
        $parent = Item::find($input['parentId']);

        $resource = new Item();

        $resource->name      = $input['name'];
        $resource->value     = $input['value'];
        $resource->parent_id = $parent->id;
        $resource->type      = $parent->getChildrenType();

        return $resource;
    }

    /**
     * Updates the resource.
     *
     * @param $input
     *
     * @return bool
     */
    public function update($input)
    {
        $resource = Item::find($input['id']);

        $resource->name  = $input['name'];
        $resource->value = $input['value'];

        return $resource->save();
    }

    /**
     * Deletes a resource.
     *
     * @param $id
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete($id)
    {
        return Item::find($id)->delete();
    }

}