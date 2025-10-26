<?php namespace PCK\Helpers\Hierarchy;

class NestedSetNode {
    public $lft;
    public $rgt;
    public $depth;
    public $rootId;
    public $parentId;
    public $id;
    public $data;

    public function __construct($id, $rootId, $parentId, $lft, $rgt, $depth)
    {
        $this->id = $id;
        $this->rootId = $rootId;
        $this->parentId = $parentId;
        $this->lft = $lft;
        $this->rgt = $rgt;
        $this->depth = $depth;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }
}

