<?php

class sfBQLibraryImportBuildspaceExcel extends sfImportExcelBuildspace
{
    private $allowedItemTypes = array(
        BQItem::TYPE_HEADER,
        BQItem::TYPE_WORK_ITEM,
        BQItem::TYPE_NOID
    );

    public function saveIntoLibrary(BQLibrary $library, Array $selectElementIds, $withRate = false, Doctrine_Connection $conn = null)
    {
        $conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();

        if ( $this->buildspaceSheets->getSize() === count($selectElementIds) )
        {
            $bsSheets = $this->buildspaceSheets;
        }
        else
        {
            $bsSheets = new SplFixedArray(0);

            foreach ( $this->buildspaceSheets as $key => $sheet )
            {
                if ( in_array($key, $selectElementIds) )//element id is actually sheet index
                {
                    $bsSheets->setSize($bsSheets->getSize() + 1);
                    $bsSheets[$bsSheets->getSize() - 1] = $sheet;
                }
            }
        }

        $userId          = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $elementPriority = BQElement::getMaxPriorityByLibraryId($library->id) + 1;

        $stmt = new sfImportExcelStatementGenerator($conn);

        $stmt->createInsert(
            BQElementTable::getInstance()->getTableName(),
            array( 'description', 'library_id', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
        );

        $items = array();

        foreach ( $bsSheets as $sheet )
        {
            $elementInfo = $sheet->getElementInfo();

            $stmt->addRecord(array( pg_escape_string((string) $elementInfo['description']), $library->id, $elementPriority, 'NOW()', 'NOW()', $userId, $userId ), (int) $elementInfo['id']);

            $items[$elementInfo['id']] = $sheet->getDataStructure()->toArray();

            $elementPriority ++;
        }

        $stmt->save();

        $importedElementToElementIds = $stmt->returningIds;

        $this->saveLibraryItems($items, $importedElementToElementIds, $withRate, $conn);
    }

    private function saveLibraryItems(Array $items, Array $savedElementIds, $withRate = false, Doctrine_Connection $conn = null)
    {
        $conn   = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $stmt   = new sfImportExcelStatementGenerator($conn);

        $rootId   = null;
        $priority = 0;

        // will get existing unit first
        $unitGenerator = new ScheduleOfQuantityUnitGetter($conn);

        $availableUnits = $unitGenerator->getAvailableUnitOfMeasurements();

        $childrenForRoots = array();

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

        $ratesList = new SplFixedArray(0);

        foreach ( $items as $elementId => $itemData )
        {
            foreach ( $itemData as $item )
            {
                if ( $item['level'] == 0 )
                {
                    $rootId = $item['id'];
                    $priority ++;
                }

                $childrenForRoots[$rootId][] = $item['id'];

                if ( $withRate AND is_numeric($item['rate']) )
                {
                    $ratesList->setSize($ratesList->getSize() + 1);
                    $ratesList[$ratesList->getSize() - 1] = array(
                        'original_item_id' => $item['id'],
                        'value'            => $item['rate']
                    );
                }

                // will help convert to normal item type if detected not allowed item when importing
                if ( !in_array($item['type'], $this->allowedItemTypes) )
                {
                    $item['type']       = BQItem::TYPE_WORK_ITEM;
                    $item['uom_symbol'] = null;
                }

                if ( !is_null($item['uom_symbol']) && strlen($item['uom_symbol']) > 0 && !array_key_exists(strtolower($item['uom_symbol']), $availableUnits) )
                {
                    // we will insert the new uom symbol
                    $availableUnits = $unitGenerator->insertNewUnitOfMeasurementWithoutDimension($availableUnits, $item['uom_symbol']);
                }

                $uomId = ( !is_null($item['uom_symbol']) && strlen($item['uom_symbol']) > 0 ) ? $availableUnits[strtolower($item['uom_symbol'])] : null;

                $stmt->addRecord(array(
                    $savedElementIds[(int) $elementId],
                    pg_escape_string((string) $item['description']),
                    (int) $item['type'],
                    $uomId,
                    $item['level'],
                    null,
                    $item['lft'],
                    $item['rgt'],
                    $priority,
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId
                ), $item['id']);
            }
        }

        $stmt->save();

        $importedItemToItemIds = $stmt->returningIds;

        $this->reassignRootIds($childrenForRoots, $importedItemToItemIds, $conn);

        if ( $ratesList->getSize() > 0 )
        {
            $this->saveBQLibraryItemRates($ratesList, $importedItemToItemIds, $conn);
        }
    }

    private function reassignRootIds(Array $roots, Array $importedItemToItemIds, Doctrine_Connection $conn = null)
    {
        $conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $pdo  = $conn->getDbh();

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

    private function saveBQLibraryItemRates(SplFixedArray $rates, Array $importedItemToItemIds, Doctrine_Connection $conn=null)
    {
        $conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();

        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $stmt   = new sfImportExcelStatementGenerator($conn);

        $stmt->createInsert(
            BQItemFormulatedColumnTable::getInstance()->getTableName(),
            array( 'relation_id', 'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by' )
        );

        foreach ( $rates as $rate )
        {
            if ( array_key_exists($rate['original_item_id'], $importedItemToItemIds) )
            {
                $stmt->addRecord(array(
                    $importedItemToItemIds[$rate['original_item_id']],
                    BQItem::FORMULATED_COLUMN_RATE,
                    $rate['value'],
                    $rate['value'],
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId
                ));
            }
        }

        $stmt->save();
    }

}