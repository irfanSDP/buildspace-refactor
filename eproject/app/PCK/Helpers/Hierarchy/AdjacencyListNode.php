<?php namespace PCK\Helpers\Hierarchy;

class AdjacencyListNode {
    public $id;
    public $children;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function traverse(&$node, $callback, $childKey = 'children')
    {
        $node = call_user_func($callback, $node);

        if( empty($node[ $childKey ]) ) return;

        foreach($node[ $childKey ] as $key => $child)
        {
            self::traverse($node[$childKey][$key], $callback, $childKey);
        }
    }
}