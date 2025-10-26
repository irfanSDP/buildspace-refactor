<?php

abstract class SOQParser {

    protected $fileName;
    protected $itemList = array();
    protected $tradeList = array();
    protected $buildUpItemList = array();
    protected $defaultItemList = array();

    const ITEM_CONTAINER_NAME = 'RootTradeContainer';

    /**
     * Set File Name that will be processed
     *
     * @param $fileName
     */
    public function setFile($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return array
     */
    public function getItemList()
    {
        return $this->itemList;
    }

    public function getTradeList()
    {
        return $this->tradeList;
    }

    public function getBuildUpItemList()
    {
        return $this->buildUpItemList;
    }

    /**
     * @return array
     */
    public function getDefaultItemList()
    {
        return $this->defaultItemList;
    }

    /**
     * PHP XML Parser Function
     *
     * @return mixed
     */
    public function getArrayFromFile()
    {
        ini_set('memory_limit','256M');
        ini_set('max_execution_time', 0);

        $xmlReader = new XMLReader();
        $xmlReader->open($this->fileName);

        $doc = new DOMDocument;

        // move to the first <ITEM_CONTAINER_NAME /> node
        while ($xmlReader->read() && $xmlReader->name != self::ITEM_CONTAINER_NAME);

        // now that we're at the right depth, hop to the next <ITEM_CONTAINER_NAME/> until the end of the tree
        while ($xmlReader->name == self::ITEM_CONTAINER_NAME)
        {
            $node = simplexml_import_dom($doc->importNode($xmlReader->expand(), true));
            break;
        }

        $xmlReader->close();

        unset( $xmlReader, $doc );

        return json_encode($node);
    }

    /**
     * Doctrine's code to generate Tree Model
     *
     * @param $itemList
     * @return array
     */
    public function generateTreeModel(array $itemList)
    {
        $trees = array();

        // Node Stack. Used to help building the hierarchy
        $stack = array();

        $priority = 0;

        foreach ( $itemList as $item )
        {
            if($item['level'] == 0)
            {
                $rootId = $item['sourceId'];
                $priority++;
            }

            $item['root_id']     = $rootId;
            $item['priority']    = $priority;
            $item['lft']         = 1;
            $item['rgt']         = 2;
            $item['__children'] = array();

            // Number of stack items
            $l = count($stack);

            // Check if we're dealing with different levels
            while ($l > 0 && $stack[$l - 1]['level'] >= $item['level'])
            {
                array_pop($stack);
                $l --;
            }

            // Stack is empty (we are inspecting the root)
            if ( $l == 0 )
            {
                // Assigning the root child
                $i         = count($trees);
                $trees[$i] = $item;
                $stack[]   = &$trees[$i];
            }
            else
            {
                $item['lft'] = $stack[$l - 1]['rgt'];
                $item['rgt'] = $item['lft'] + 1;

                // Add child to parent
                $i                               = count($stack[$l - 1]['__children']);
                $stack[$l - 1]['__children'][$i] = $item;
                $stack[]                         = &$stack[$l - 1]['__children'][$i];

                $x = $l;
                while($x-1 >= 0)
                {
                    $stack[$x - 1]['rgt'] = $stack[$x - 1]['rgt'] + 2;
                    $x--;
                }
            }
        }

        unset( $stack );

        return $trees;
    }
}