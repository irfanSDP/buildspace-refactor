<?php

class sfBQLibraryImportXML extends sfXMLReaderParser
{
    protected $conn;
    protected $library;
    protected $withRate;

    protected $preparedElements = array();
    protected $preparedItems = array();
    protected $preparedRates = array();

    const TAG_ELEMENT = "ELEMENTS";
    const TAG_ITEM = "ITEMS";

    function __construct(BQLibrary $library, $filename, $uploadPath, $extension, $withRate=false)
    {
        $this->library = $library;
        $this->withRate = $withRate;

        parent::__construct( $filename, $uploadPath, $extension, true );
    }

    public function process(Array $elementIds)
    {
        $structure = $this->generateStructure();

        foreach($structure as $data)
        {
            if(isset($data['element']) && !empty($data['element']) && in_array($data['element']['id'], $elementIds))
            {
                $this->prepareElement($data['element']);

                if(isset($data['items']) && !empty($data['items']))
                {
                    $this->prepareItems($data['items']);
                }
            }
        }
    }

    public function save(Doctrine_Connection $conn=null)
    {
        $this->conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();

        $importedElementToElementIds = $this->saveElements();

        $this->saveItems($importedElementToElementIds);
    }

    private function saveElements()
    {
        $userId          = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $elementPriority = BQElement::getMaxPriorityByLibraryId($this->library->id) + 1;

        $stmt = new sfImportExcelStatementGenerator($this->conn);

        $stmt->createInsert(
            BQElementTable::getInstance()->getTableName(),
            array( 'description', 'library_id', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
        );

        foreach ( $this->preparedElements as $id => $element )
        {
            $stmt->addRecord(array( pg_escape_string((string) $element['description']), $this->library->id, $elementPriority, 'NOW()', 'NOW()', $userId, $userId ), $id);

            $elementPriority ++;
        }

        $stmt->save();

        return $stmt->returningIds;
    }

    private function saveItems(Array $savedElementIds)
    {
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $stmt   = new sfImportExcelStatementGenerator($this->conn);

        $stmt->createInsert(
            BQItemTable::getInstance()->getTableName(),
            array(
                'element_id',
                'description',
                'type',
                'uom_id',
                'level',
                'root_id',
                'lft',
                'rgt',
                'priority',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by'
            )
        );

        $childrenForRoots = array();

        foreach($this->preparedItems as $elementId => $items)
        {
            foreach($items as $id => $item)
            {
                if(!array_key_exists($item['root_id'], $childrenForRoots))
                {
                    $childrenForRoots[$item['root_id']] = array();
                }

                $childrenForRoots[$item['root_id']][] = $id;

                $stmt->addRecord(array(
                    $savedElementIds[(int) $elementId],
                    pg_escape_string((string) $item['description']),
                    (int) $item['type'],
                    $item['uom_id'],
                    $item['level'],
                    null,
                    $item['lft'],
                    $item['rgt'],
                    $item['priority'],
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId
                ), $id);
            }
        }

        $stmt->save();

        $importedItemToItemIds = $stmt->returningIds;

        $this->reassignRootIds($childrenForRoots, $importedItemToItemIds);

        if($this->withRate)
        {
            $this->saveBQItemRates($importedItemToItemIds);
        }
    }

    private function saveBQItemRates(Array $savedItemIds)
    {
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $stmt   = new sfImportExcelStatementGenerator($this->conn);

        $stmt->createInsert(
            BQItemFormulatedColumnTable::getInstance()->getTableName(),
            array( 'relation_id', 'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by' )
        );

        foreach($this->preparedRates as $itemId => $rate)
        {
            if ( array_key_exists($itemId, $savedItemIds) )
            {
                $stmt->addRecord(array(
                    $savedItemIds[$itemId],
                    BQItem::FORMULATED_COLUMN_RATE,
                    $rate,
                    $rate,
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId
                ));
            }
        }

        $stmt->save();
    }

    private function generateStructure()
    {
        $elementSet = $this->getBySingleTag(sfBQLibraryImportXML::TAG_ELEMENT);

        $itemSet = $this->getBySingleTag(sfBQLibraryImportXML::TAG_ITEM);

        $structure = array();

        if(is_array($elementSet) && isset($elementSet['item']))
        {
            if(isset($elementSet['item']['rowType']))
            {
                $elementSet['item'] = array($elementSet['item']);
            }

            if(isset($itemSet['item']['rowType']))
            {
                $itemSet['item'] = array($itemSet['item']);
            }

            foreach($elementSet['item'] as $element)
            {
                if(strtolower($element['rowType']) == 'element')
                {
                    $structure[] = array(
                        'element' => $this->processElement($element),
                        'items'    => $this->processItemByElementId($itemSet['item'], $element['id'])
                    );
                }
            }
        }

        return $structure;
    }

    private function prepareElement(Array $data)
    {
        $this->preparedElements[$data['id']] = $data;
    }

    private function prepareItems(Array $data)
    {
        foreach($data as $item)
        {
            if(!array_key_exists($item['element_id'], $this->preparedItems))
            {
                $this->preparedItems[$item['element_id']] = array();
            }

            if($this->withRate AND is_numeric($item['rate']) )
            {
                $this->preparedRates[$item['id']] = $item['rate'];
            }

            $uomId = (!empty($item['uom_id']) && $item['uom_id'] > 0) ? $item['uom_id'] : null;

            $this->preparedItems[$item['element_id']][$item['id']] = array(
                'root_id'     => $item['root_id'],
                'description' => pg_escape_string((string) $item['description']),
                'type'        => (int) $item['type'],
                'uom_id'      => $uomId,
                'level'       => $item['level'],
                'lft'         => $item['lft'],
                'rgt'         => $item['rgt'],
                'priority'    => $item['priority']
            );

            if(isset($item['__children']) && !empty($item['__children']))
            {
                $this->prepareItems($item['__children']);
            }
        }
    }

    private function processElement(Array $elementData)
    {
        return array(
            'id'          => $elementData['id'],
            'description' => $elementData['description']
        );
    }

    private function processItemByElementId(Array $items, $elementId)
    {
        $priority = 0;
        $stack = array();
        $trees = array();

        foreach($items as $itemXML)
        {
            if($itemXML['elementId'] != $elementId)
                continue;

            if($itemXML['level'] == 0)
            {
                $rootId = $itemXML['id'];
                $priority++;
            }

            $item['id']          = $itemXML['id'];
            $item['description'] = $itemXML['description'];
            $item['uom_id']      = $itemXML['uom_id'];
            $item['type']        = $itemXML['type'];
            $item['rate']        = $itemXML['rate-final_value'];
            $item['level']       = $itemXML['level'];
            $item['element_id']  = $elementId;
            $item['root_id']     = $rootId;
            $item['priority']    = $priority;
            $item['lft']         = 1;
            $item['rgt']         = 2;
            $item['__children']  = array();

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

        return $trees;
    }

    private function reassignRootIds(Array $roots, Array $importedItemToItemIds)
    {
        $pdo  = $this->conn->getDbh();

        $rootIds = array();
        $itemIds = array();

        foreach ( $roots as $rootId => $root )
        {
            foreach ( $root as $itemId )
            {
                if ( array_key_exists($rootId, $importedItemToItemIds) && array_key_exists($itemId, $importedItemToItemIds) )
                {
                    $itemIds[] = $importedItemToItemIds[$itemId];
                    $rootIds[] = $importedItemToItemIds[$rootId];
                }
            }
        }

        if ( $rootIds && $itemIds )
        {
            $stmt = $pdo->prepare("UPDATE " . BQItemTable::getInstance()->getTableName() . " SET root_id = cast(virtual_table.root_id AS int)
            FROM
            (SELECT UNNEST(ARRAY[" . implode(",", $itemIds) . "]) AS id,
                UNNEST(ARRAY['" . implode("','", $rootIds) . "']) AS root_id
            ) AS virtual_table WHERE " . BQItemTable::getInstance()->getTableName() . ".id = virtual_table.id
            AND " . BQItemTable::getInstance()->getTableName() . ".root_id IS NULL");

            $stmt->execute();
        }
    }
}
