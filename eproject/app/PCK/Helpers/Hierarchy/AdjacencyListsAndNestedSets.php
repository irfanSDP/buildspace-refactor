<?php namespace PCK\Helpers\Hierarchy;

use Illuminate\Database\Eloquent\Model;

class AdjacencyListsAndNestedSets extends Model {

    const ASCENDING = 0;
    const DESCENDING = 1;

    const DEFAULT_CHILD_KEY_NAME = 'children';

    public $adjacencyList;
    public $nestedSet;

    public $rootId;

    /**
     * Setter method for the adjacency list.
     *
     * @param $adjacencyList
     */
    public function setAdjacencyList($adjacencyList)
    {
        $this->adjacencyList = $adjacencyList;
    }

    /**
     * Converts the set adjacency list into a nested set format.
     *
     * @param $rootId
     * @param $startIndex
     *
     * @return array
     */
    public function convertAdjacencyListToNestedSet($rootId, $startIndex)
    {
        $nestedSets = array();

        $this->setRightOf($startIndex, $nestedSets, 1, $rootId, $this->adjacencyList);

        return $nestedSets;
    }

    /**
     * Returns the end index of the subset.
     *
     * @param $rootId
     * @param $startIndex
     *
     * @return mixed
     */
    public function getEndIndex($rootId, $startIndex)
    {
        $nestedSets = array();

        return $this->setRightOf($startIndex, $nestedSets, 1, $rootId, $this->adjacencyList);
    }

    /**
     * Set the values (e.g. lft, rgt, depth etc.) for each corresponding adjacency list item.
     *
     * @param $startIndex
     * @param $nestedSets
     * @param $currentDepth
     * @param $parentId
     * @param $itemList
     *
     * @return mixed
     */
    private function setRightOf($startIndex, &$nestedSets, $currentDepth, $parentId, $itemList)
    {
        foreach($itemList as $key => $item)
        {
            $itemLft = $startIndex;
            if( ! $this->hasChildren($item) )
            {
                $itemRgt = $itemLft + 1;
            }
            else
            {
                $childStartIndex = $itemLft + 1;
                $itemRgt = $this->setRightOf($childStartIndex, $nestedSets, $currentDepth + 1, $item['id'], $this->getChildren($item)) + 1;
            }

            $nestedSet = array(
                'id'       => $item['id'],
                'lft'      => $itemLft,
                'rgt'      => $itemRgt,
                'depth'    => $currentDepth,
                'parentId' => $parentId
            );

            array_push($nestedSets, $nestedSet);

            $endIndex = $itemRgt;

            // start index for the next item/iteration
            $startIndex = $endIndex + 1;
        }

        // If there are no items, set the end index to be the end index (rgt) of the previous set
        if( ! isset( $endIndex ) )
        {
            $endIndex = $startIndex - 1;
        }

        return $endIndex;
    }

    /**
     * Checks if the item has a child attached to the item.
     *
     * @param        $item
     * @param string $childKeyName
     *
     * @return bool
     */
    private function hasChildren($item, $childKeyName = self::DEFAULT_CHILD_KEY_NAME)
    {
        return array_key_exists($childKeyName, $item);
    }

    /**
     * Returns all children attached to the item.
     *
     * @param        $item
     * @param string $childKeyName
     *
     * @return array
     */
    private function getChildren($item, $childKeyName = self::DEFAULT_CHILD_KEY_NAME)
    {
        if( $this->hasChildren($item, $childKeyName) )
        {
            return $item[ $childKeyName ];
        }

        return array();
    }

    /**
     * Setter to set the array of the nested set.
     *
     * @param       $rootId
     * @param array $nestedSet
     *
     * @throws \Exception
     */
    public function setNestedSet($rootId, array $nestedSet)
    {
        $this->nestedSetIsValid($nestedSet);

        $this->rootId = $rootId;
        $this->nestedSet = $nestedSet;
    }

    /**
     * Checks if the nested set is usable and not corrupted.
     *
     * TODO: check that all lfts and rgts are unique
     *
     * @param $nestedSet
     *
     * @throws \Exception
     */
    public function nestedSetIsValid($nestedSet)
    {
        foreach($nestedSet as $nestedSetItem)
        {
            if( ! $nestedSetItem instanceof NestedSetNode )
            {
                throw new \Exception('Object must be of type "NestedSetNode"');
            }
        }
    }

    /*
     * NestedSet to AdjacencyList functions
     * */

    /**
     * Convert the nested set to an adjacency list.
     *
     * @return array
     */
    public function convertNestedSetToAdjacencyList()
    {
        return $this->getChildrenByParentId($this->rootId);
    }

    /**
     * Get the children by the id of its parent.
     *
     * @param $parentId
     *
     * @return array
     */
    private function getChildrenByParentId($parentId)
    {
        $items = array();

        foreach($this->nestedSet as $key => $set)
        {
            $this->setCurrentLevelItems($items, $key, $set, $parentId);

            $this->setNextLevelItems($items);
        }

        return $items;
    }

    /**
     * Set items for the current level.
     *
     * @param $items
     * @param $key
     * @param $set
     * @param $parentId
     */
    private function setCurrentLevelItems(&$items, $key, $set, $parentId)
    {
        if( $set->parentId == $parentId )
        {
            $item = array(
                'id'   => $set->id,
                'data' => $set->data
            );
            array_push($items, $item);

            // Remove set from nestedSets to prevent it from being processed again.
            unset( $this->nestedSet[ $key ] );
        }
    }

    /**
     * Set items for the next level (children).
     *
     * @param $items
     */
    protected function setNextLevelItems(&$items)
    {
        foreach($items as $itemKey => $item)
        {
            $children = $this->getChildrenByParentId($item['id']);

            if( ! empty( $children ) )
            {
                $items[ $itemKey ]['children'] = $children;
            }
        }
    }

}