<?php

class sfImportExcelBuildspace {

    protected $objPHPExcel;

    protected $buildspaceSheets;

    protected $billColumnSettings;

    public function __construct($filename)
    {
        try
        {
            libxml_use_internal_errors(true);

            $inputFileType = PHPExcel_IOFactory::identify($filename);
            $objReader     = PHPExcel_IOFactory::createReader($inputFileType);

            $objReader->setReadDataOnly(true);

            $this->objPHPExcel = $objReader->load($filename);

            $this->buildspaceSheets = new SplFixedArray($this->objPHPExcel->getSheetCount());

            $this->billColumnSettings = new SplFixedArray(0);
        }
        catch (Exception $e)
        {
            throw new Exception('Error loading file "' . $filename . '": ' . $e->getMessage());
        }
    }

    public function process()
    {
        try
        {
            $this->validate();

            foreach ( $this->objPHPExcel->getProperties()->getCustomProperties() as $customProperty )
            {
                if ( strpos($customProperty, sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BILL_COLUMN_SETTING . '_') !== false )
                {
                    preg_match_all('!\d+(?:\.\d+)?!', $customProperty, $matches);
                    $idx = array_map('reset', $matches);

                    $this->billColumnSettings->setSize($this->billColumnSettings->getSize() + 1);
                    $this->billColumnSettings[$this->billColumnSettings->getSize() - 1] = array(
                        'name' => $this->objPHPExcel->getProperties()->getCustomPropertyValue($customProperty),
                        'qty'  => $this->objPHPExcel->getProperties()->getCustomPropertyValue(sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BILL_COLUMN_SETTING_QTY . "_" . $idx[0])
                    );
                }
            }

            foreach ( $this->objPHPExcel->getAllSheets() as $idx => $sheet )
            {
                $this->buildspaceSheets[$idx] = new sfImportExcelBuildspaceSheet($sheet, $this->billColumnSettings);
            }
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }

    private function validate()
    {
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE]                       = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_DESCRIPTION]                 = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_PROJECT_STRUCTURE_TYPE]      = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BILL_TYPE]                   = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BUILD_UP_QTY_ROUNDING_TYPE]  = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BUILD_UP_RATE_ROUNDING_TYPE] = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_UNIT_TYPE]                   = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_ELEMENT_MARKUP_ENABLED]      = null;

        $count                        = 0;
        $hasBillColumnSettingProps    = false;
        $hasBillColumnSettingQtyProps = false;

        foreach ( $this->objPHPExcel->getProperties()->getCustomProperties() as $customProperty )
        {
            if ( array_key_exists($customProperty, $billInfo) )
            {
                $count ++;
            }

            if ( strpos($customProperty, sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BILL_COLUMN_SETTING . '_') !== false )
            {
                $hasBillColumnSettingProps = true;
            }

            if ( strpos($customProperty, sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BILL_COLUMN_SETTING_QTY . '_') !== false )
            {
                $hasBillColumnSettingQtyProps = true;
            }
        }

        if ( $count != count($billInfo) or !$hasBillColumnSettingProps or !$hasBillColumnSettingQtyProps )
        {
            throw new Exception('Invalid excel file');
        }
    }

    public function saveAsNewBill(ProjectStructure $projectStructure, Array $selectElementIds, $withRate = false, $withQty = false, $withBillRef = false, Doctrine_Connection $conn = null)
    {
        $conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();

        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE]                       = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_DESCRIPTION]                 = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_PROJECT_STRUCTURE_TYPE]      = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BILL_TYPE]                   = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BUILD_UP_QTY_ROUNDING_TYPE]  = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BUILD_UP_RATE_ROUNDING_TYPE] = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_UNIT_TYPE]                   = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_ELEMENT_MARKUP_ENABLED]      = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_ITEM_MARKUP_ENABLED]         = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_MARKUP_ROUNDING_TYPE]        = null;

        foreach ( $this->objPHPExcel->getProperties()->getCustomProperties() as $customProperty )
        {
            if ( array_key_exists($customProperty, $billInfo) )
            {
                $billInfo[$customProperty] = $this->objPHPExcel->getProperties()->getCustomPropertyValue($customProperty);
            }
        }

        $bill = $this->createBill($billInfo, $projectStructure, $conn);

        $billSetting = new BillSetting();

        $billSetting->project_structure_id            = $bill->id;
        $billSetting->title                           = $bill->title;
        $billSetting->description                     = $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_DESCRIPTION];
        $billSetting->unit_type                       = $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_UNIT_TYPE];
        $billSetting->build_up_quantity_rounding_type = $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BUILD_UP_QTY_ROUNDING_TYPE];
        $billSetting->build_up_rate_rounding_type     = $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BUILD_UP_RATE_ROUNDING_TYPE];

        $billSetting->save($conn);

        $defaultPrintingSetting = BillLayoutSettingTable::getInstance()->find(1);
        $defaultSetting         = $defaultPrintingSetting->toArray();

        // get global default printing setting
        $billPhraseSetting = $defaultPrintingSetting->getBillPhrase()->toArray();
        $headSettings      = $defaultPrintingSetting->getBillHeadSettings()->toArray();

        BillLayoutSettingTable::cloneExistingPrintingLayoutSettingsForBill($bill->id, $defaultSetting, $billPhraseSetting, $headSettings);

        $this->saveIntoBill($bill, $selectElementIds, $withRate, $withQty, $withBillRef, $conn);
    }

    private function createBill(Array $billInfo, ProjectStructure $projectStructure, Doctrine_Connection $conn = null)
    {
        $conn   = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');

        $count = DoctrineQuery::create()->select('s.id, s.title')->from('ProjectStructure s')
            ->where('LOWER(s.title) = ?', strtolower($billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE]))
            ->andWhere('s.root_id = ?', $projectStructure->root_id)
            ->count();

        if ( $count > 0 )
        {
            $count += DoctrineQuery::create()->select('s.id, s.title')->from('ProjectStructure s')
                ->where('LOWER(s.title) LIKE ?', strtolower($billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE]) . ' (%')
                ->andWhere('s.root_id = ?', $projectStructure->root_id)
                ->count();
        }

        $title = $count > 0 ? $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE] . " (" . ( $count ) . ")" : $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE];

        $pdo = $conn->getDbh();

        $stmt = $pdo->prepare("INSERT INTO " . ProjectStructureTable::getInstance()->getTableName() . "
        (title, type, root_id, created_at, updated_at, created_by, updated_by)
        VALUES
        ('" . pg_escape_string(trim($title)) . "', " . (int) $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_PROJECT_STRUCTURE_TYPE] . ", " . $projectStructure->root_id . ", NOW(), NOW(), " . $userId . ", " . $userId . ") RETURNING id");

        $stmt->execute();

        $returnedId = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        $bill = Doctrine_Core::getTable('ProjectStructure')->find($returnedId);

        if ( $projectStructure->node->isRoot() or $projectStructure->type == ProjectStructure::TYPE_LEVEL )
        {
            $bill->node->insertAsFirstChildOf($projectStructure);
        }
        else
        {
            $bill->node->insertAsNextSiblingOf($projectStructure);
        }

        $stmt = $pdo->prepare("INSERT INTO " . BillTypeTable::getInstance()->getTableName() . "
        (project_structure_id, type, status, created_at, updated_at, created_by, updated_by)
        VALUES
        (" . $bill->id . ", " . (int) $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BILL_TYPE] . ", " . BillType::STATUS_OPEN . ", NOW(), NOW(), " . $userId . ", " . $userId . ")");

        $stmt->execute();

        $this->createBillColumnSettingsForBill($bill, $conn);

        $this->createBillMarkupSettingForBill(
            $bill,
            $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_ELEMENT_MARKUP_ENABLED],
            $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_ITEM_MARKUP_ENABLED],
            $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_MARKUP_ROUNDING_TYPE],
            $conn
        );

        return $bill;
    }

    private function createBillColumnSettingsForBill(ProjectStructure $bill, Doctrine_Connection $conn = null)
    {
        foreach ( $this->billColumnSettings as $data )
        {
            $billColumnSetting                       = new BillColumnSetting();
            $billColumnSetting->name                 = $data['name'];
            $billColumnSetting->quantity             = $data['qty'];
            $billColumnSetting->project_structure_id = $bill->id;

            $billColumnSetting->save($conn);
        }
    }

    private function createBillMarkupSettingForBill(ProjectStructure $bill, $elementMarkupEnabled, $itemMarkupEnabled, $markupRoundingType, Doctrine_Connection $conn = null)
    {
        $conn   = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');

        $pdo = $conn->getDbh();

        $elementMarkupEnabled = $elementMarkupEnabled ? 'TRUE' : 'FALSE';
        $itemMarkupEnabled    = $itemMarkupEnabled ? 'TRUE' : 'FALSE';

        $stmt = $pdo->prepare("INSERT INTO " . BillMarkupSettingTable::getInstance()->getTableName() . "
        (project_structure_id, element_markup_enabled, item_markup_enabled, rounding_type, created_at, updated_at, created_by, updated_by)
        VALUES
        (" . $bill->id . ", " . $elementMarkupEnabled . ", " . $itemMarkupEnabled . ", " . (int) $markupRoundingType . ", NOW(), NOW(), " . $userId . ", " . $userId . ")");

        $stmt->execute();
    }

    public function saveIntoBill(ProjectStructure $bill, Array $selectElementIds, $withRate = false, $withQty = false, $withBillRef = false, Doctrine_Connection $conn = null)
    {
        $conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();

        if ( $this->buildspaceSheets->getSize() == count($selectElementIds) )
        {
            $buildspaceSheets = $this->buildspaceSheets;
        }
        else
        {
            $buildspaceSheets = new SplFixedArray(0);

            foreach ( $this->buildspaceSheets as $key => $sheet )
            {
                if ( in_array($key, $selectElementIds) )//element id is actually sheet index
                {
                    $buildspaceSheets->setSize($buildspaceSheets->getSize() + 1);
                    $buildspaceSheets[$buildspaceSheets->getSize() - 1] = $sheet;
                }
            }
        }

        $userId          = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $elementPriority = BillElement::getMaxPriorityByBillId($bill->id) + 1;

        $stmt = new sfImportExcelStatementGenerator($conn);

        $stmt->createInsert(
            BillElementTable::getInstance()->getTableName(),
            array( 'description', 'project_structure_id', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
        );

        $items = array();

        foreach ( $buildspaceSheets as $sheet )
        {
            $elementInfo = $sheet->getElementInfo();

            $stmt->addRecord(array( pg_escape_string((string) $elementInfo['description']), $bill->id, $elementPriority, 'NOW()', 'NOW()', $userId, $userId ), (int) $elementInfo['id']);

            $items[$elementInfo['id']] = $sheet->getDataStructure()->toArray();

            $elementPriority ++;
        }

        $stmt->save();

        $importedElementToElementIds = $stmt->returningIds;

        $this->saveBillItems($bill, $items, $importedElementToElementIds, $withRate, $withQty, $withBillRef, $conn);

    }

    private function saveBillItems(ProjectStructure $bill, Array $items, Array $savedElementIds, $withRate = false, $withQty = false, $withBillRef = false, Doctrine_Connection $conn = null)
    {
        $conn   = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $stmt   = new sfImportExcelStatementGenerator($conn);

        //Get Current Bill Revision Id
        $projectRevision = ProjectRevisionTable::getLatestProjectRevisionFromBillId($bill->root_id);

        $rootId   = null;
        $priority = 0;

        // will get existing unit first
        $unitGenerator = new ScheduleOfQuantityUnitGetter($conn);

        $availableUnits = $unitGenerator->getAvailableUnitOfMeasurements();

        $childrenForRoots = array();

        $stmt->createInsert(
            BillItemTable::getInstance()->getTableName(),
            array(
                'element_id',
                'bill_ref_char',
                'description',
                'type',
                'uom_id',
                'grand_total_quantity',
                'grand_total',
                'grand_total_after_markup',
                'level',
                'root_id',
                'lft',
                'rgt',
                'priority',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by',
                'project_revision_id'
            )
        );

        $billItemTypeRefList = new SplFixedArray(0);
        $ratesList           = new SplFixedArray(0);
        $pcRateItemsList     = new SplFixedArray(0);
        $lumpSumPercentList  = new SplFixedArray(0);
        $billColumnSettings  = $bill->BillColumnSettings;

        foreach ( $items as $elementId => $itemData )
        {
            foreach ( $itemData as $item )
            {
                $billRef = $withBillRef ? $item['bill_ref'] : null;

                if ( $item['level'] == 0 )
                {
                    $rootId = $item['id'];
                    $priority ++;
                }

                switch ((int) $item['type'])
                {
                    case BillItem::TYPE_ITEM_PC_RATE:
                        $pcRateItemsList->setSize($pcRateItemsList->getSize() + 1);
                        $pcRateItemsList[$pcRateItemsList->getSize() - 1] = array(
                            'original_item_id'        => $item['id'],
                            'supply_rate'             => $item['pc_supply_rate'],
                            'wastage_percentage'      => $item['pc_wastage_percentage'],
                            'wastage_amount'          => $item['pc_wastage_amount'],
                            'labour_for_installation' => $item['pc_labour_for_installation'],
                            'other_cost'              => $item['pc_other_cost'],
                            'profit_percentage'       => $item['pc_profit_percentage'],
                            'profit_amount'           => $item['pc_profit_amount'],
                            'total'                   => $item['pc_total']
                        );
                        break;
                    case BillItem::TYPE_ITEM_LUMP_SUM_PERCENT:
                        $lumpSumPercentList->setSize($lumpSumPercentList->getSize() + 1);
                        $lumpSumPercentList[$lumpSumPercentList->getSize() - 1] = array(
                            'original_item_id' => $item['id'],
                            'rate'             => $item['ls_percent_rate'],
                            'percentage'       => $item['ls_percent_percentage'],
                            'amount'           => $item['ls_percent_amount']
                        );
                        break;
                    default:
                        break;
                }

                $childrenForRoots[$rootId][] = $item['id'];

                $grandTotalPerUnit = 0;
                $grandTotal        = 0;
                $grandTotalQty     = 0;

                if ( $withRate )
                {
                    if ( is_numeric($item['rate']) )
                    {
                        $ratesList->setSize($ratesList->getSize() + 1);
                        $ratesList[$ratesList->getSize() - 1] = array(
                            'original_item_id' => $item['id'],
                            'value'            => $item['rate']
                        );
                    }
                }

                if ( $withQty )
                {
                    foreach ( $billColumnSettings as $key => $billColumnSetting )
                    {
                        $columnName = BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT;

                        if ( !$billColumnSetting->use_original_quantity )
                        {
                            $columnName = BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;
                        }

                        if(!isset($item['quantity_per_unit-final_value-' . $key]) and (int) $item['type'] != BillItem::TYPE_HEADER and (int) $item['type'] != BillItem::TYPE_HEADER_N and (int) $item['type'] != BillItem::TYPE_NOID and (int) $item['type'] != BillItem::TYPE_ITEM_RATE_ONLY)
                        {
                            $qty = NULL;
                        }
                        else
                        {
                            $qty = isset($item['quantity_per_unit-final_value-' . $key]) ? $item['quantity_per_unit-final_value-' . $key] : 0;
                        }

                        $itemTotalQty = $qty * $billColumnSetting->quantity;
                        $grandTotalQty += $itemTotalQty;
                        $itemTotal = 0;

                        if ( $withRate && $withQty )
                        {
                            $itemTotalPerUnit = $item['rate'] * $qty;
                            $itemTotal        = $itemTotalPerUnit * $billColumnSetting->quantity;

                            $grandTotalPerUnit += $itemTotalPerUnit;
                            $grandTotal += $itemTotal;
                        }

                        $billItemTypeRefList->setSize($billItemTypeRefList->getSize() + 1);
                        $billItemTypeRefList[$billItemTypeRefList->getSize() - 1] = array(
                            'original_item_id'       => $item['id'],
                            'qty'                    => $qty,
                            'column_name'            => $columnName,
                            'bill_column_setting_id' => $billColumnSetting->id,
                            'total_qty'              => $itemTotalQty,
                            'total'                  => $itemTotal
                        );
                    }
                }

                if ( !is_null($item['uom_symbol']) && strlen($item['uom_symbol']) > 0 && !array_key_exists(strtolower($item['uom_symbol']), $availableUnits) )
                {
                    // we will insert the new uom symbol
                    $availableUnits = $unitGenerator->insertNewUnitOfMeasurementWithoutDimension($availableUnits, $item['uom_symbol']);
                }

                $uomId = ( !is_null($item['uom_symbol']) && strlen($item['uom_symbol']) > 0 ) ? $availableUnits[strtolower($item['uom_symbol'])] : null;

                $stmt->addRecord(array(
                    $savedElementIds[(int) $elementId],
                    $billRef,
                    pg_escape_string((string) $item['description']),
                    (int) $item['type'],
                    $uomId,
                    $grandTotalQty,
                    $grandTotal,
                    $grandTotal,
                    $item['level'],
                    null,
                    $item['lft'],
                    $item['rgt'],
                    $priority,
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId,
                    $projectRevision['id']
                ), $item['id']);
            }
        }

        $stmt->save();

        $importedItemToItemIds = $stmt->returningIds;

        $this->reassignRootIds($childrenForRoots, $importedItemToItemIds, $conn);

        if ( $billItemTypeRefList->getSize() > 0 )
        {
            $this->saveBillItemTypeReferences($billItemTypeRefList, $importedItemToItemIds);
        }

        if ( $ratesList->getSize() > 0 )
        {
            $createLog = ($withRate && $withQty) ? true : false;
            $this->saveBillItemRates($bill, $ratesList, $importedItemToItemIds, $createLog, $conn);
        }

        if ( $pcRateItemsList->getSize() > 0 )
        {
            $this->savePrimeCostRates($pcRateItemsList, $importedItemToItemIds, $conn);
        }

        if ( $lumpSumPercentList->getSize() > 0 )
        {
            $this->saveLumpSumPercentItems($lumpSumPercentList, $importedItemToItemIds, $conn);
        }
    }

    private function saveLumpSumPercentItems(SplFixedArray $lumpSumPercentList, Array $importedItemToItemIds, Doctrine_Connection $conn = null)
    {
        $conn   = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $stmt   = new sfImportExcelStatementGenerator($conn);

        $stmt->createInsert(
            BillItemLumpSumPercentageTable::getInstance()->getTableName(),
            array(
                'bill_item_id',
                'rate',
                'percentage',
                'amount',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by'
            )
        );

        foreach ( $lumpSumPercentList as $data )
        {
            if ( array_key_exists($data['original_item_id'], $importedItemToItemIds) )
            {
                $stmt->addRecord(array(
                    $importedItemToItemIds[$data['original_item_id']],
                    $data['rate'],
                    $data['percentage'],
                    $data['amount'],
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId
                ));
            }
        }

        $stmt->save();
    }

    private function savePrimeCostRates(SplFixedArray $pcRates, Array $importedItemToItemIds, Doctrine_Connection $conn = null)
    {
        $conn   = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $stmt   = new sfImportExcelStatementGenerator($conn);

        $stmt->createInsert(
            BillItemPrimeCostRateTable::getInstance()->getTableName(),
            array(
                'bill_item_id',
                'supply_rate',
                'wastage_percentage',
                'wastage_amount',
                'labour_for_installation',
                'other_cost',
                'profit_percentage',
                'profit_amount',
                'total',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by'
            )
        );

        foreach ( $pcRates as $pcRate )
        {
            if ( array_key_exists($pcRate['original_item_id'], $importedItemToItemIds) )
            {
                $stmt->addRecord(array(
                    $importedItemToItemIds[$pcRate['original_item_id']],
                    $pcRate['supply_rate'],
                    $pcRate['wastage_percentage'],
                    $pcRate['wastage_amount'],
                    $pcRate['labour_for_installation'],
                    $pcRate['other_cost'],
                    $pcRate['profit_percentage'],
                    $pcRate['profit_amount'],
                    $pcRate['total'],
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId
                ));
            }
        }

        $stmt->save();
    }

    private function saveBillItemRates(ProjectStructure $bill, SplFixedArray $rates, Array $importedItemToItemIds, $createLog=false, Doctrine_Connection $conn = null)
    {
        $conn   = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $stmt   = new sfImportExcelStatementGenerator($conn);

        $stmt->createInsert(
            BillItemFormulatedColumnTable::getInstance()->getTableName(),
            array( 'relation_id', 'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by' )
        );

        $rateLogData = array();

        foreach ( $rates as $rate )
        {
            if ( array_key_exists($rate['original_item_id'], $importedItemToItemIds) )
            {
                $stmt->addRecord(array(
                    $importedItemToItemIds[$rate['original_item_id']],
                    BillItem::FORMULATED_COLUMN_RATE,
                    $rate['value'],
                    $rate['value'],
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId
                ));

                if($createLog)
                    $rateLogData[(int) $importedItemToItemIds[$rate['original_item_id']]] = (double)$rate['value'];
            }
        }

        $stmt->save();

        if($createLog)
            BillItemRateLogTable::insertBatchLogByBillId($bill->id, $rateLogData);
    }

    private function saveBillItemTypeReferences(SplFixedArray $billItemTypeReferences, Array $importedItemToItemIds, Doctrine_Connection $conn = null)
    {
        $conn   = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $stmt   = new sfImportExcelStatementGenerator($conn);

        //Create Bill Item Type Reference
        $stmt->createInsert(
            BillItemTypeReferenceTable::getInstance()->getTableName(),
            array( 'bill_item_id', 'bill_column_setting_id', 'include', 'total_quantity', 'grand_total', 'grand_total_after_markup', 'created_at', 'updated_at', 'created_by', 'updated_by' )
        );

        foreach ( $billItemTypeReferences as $key => $billItemTypeReference )
        {
            if ( array_key_exists($billItemTypeReference['original_item_id'], $importedItemToItemIds) )
            {
                $stmt->addRecord(array(
                    $importedItemToItemIds[$billItemTypeReference['original_item_id']],
                    $billItemTypeReference['bill_column_setting_id'],
                    (is_null($billItemTypeReference['qty'])) ? 'FALSE' : 'TRUE',
                    $billItemTypeReference['total_qty'],
                    $billItemTypeReference['total'],
                    $billItemTypeReference['total'],
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId
                ), $key);
            }
        }

        $stmt->save();

        $returnedIds = $stmt->returningIds;

        $stmt->createInsert(
            BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName(),
            array( 'relation_id', 'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by' )
        );

        foreach ( $billItemTypeReferences as $key => $billItemTypeReference )
        {
            if ( array_key_exists($key, $returnedIds) )
            {
                $stmt->addRecord(array(
                    $returnedIds[$key],
                    $billItemTypeReference['column_name'],
                    $billItemTypeReference['qty'],
                    $billItemTypeReference['qty'],
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId
                ));
            }
        }

        $stmt->save();
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
            $stmt = $pdo->prepare("UPDATE " . BillItemTable::getInstance()->getTableName() . " SET root_id = cast(virtual_table.root_id AS int)
            FROM
            (SELECT UNNEST(ARRAY[" . implode(",", $itemIds) . "]) AS id,
                UNNEST(ARRAY['" . implode("','", $rootIds) . "']) AS root_id
            ) AS virtual_table WHERE " . BillItemTable::getInstance()->getTableName() . ".id = virtual_table.id
            AND " . BillItemTable::getInstance()->getTableName() . ".root_id IS NULL");

            $stmt->execute();
        }
    }

    public function getBillColumnSettings()
    {
        return $this->billColumnSettings->toArray();
    }

    public function getPreviewFormatData()
    {
        $data = array();

        $billColumnSettings = $this->getBillColumnSettings();

        if ( $this->buildspaceSheets instanceof SplFixedArray )
        {
            $data['bill_info'] = array(
                'bill_column_settings' => $billColumnSettings
            );

            foreach ( $this->buildspaceSheets as $key => $sheet )
            {
                $dataStructure = $sheet->getDataStructure()->toArray();

                foreach ( $dataStructure as $idx => $item )
                {
                    $dataStructure[$idx]['type']                           = (string) $item['type'];
                    $dataStructure[$idx]['rate-final_value']               = $item['rate'];
                    $dataStructure[$idx]['rate-value']                     = $item['rate'];
                    $dataStructure[$idx]['rate-has_formula']               = false;
                    $dataStructure[$idx]['rate-has_build_up']              = false;
                    $dataStructure[$idx]['quantity_per_unit-final_value']  = $item['quantity_per_unit-final_value-0'];
                    $dataStructure[$idx]['quantity_per_unit-value']        = $item['quantity_per_unit-final_value-0'];
                    $dataStructure[$idx]['quantity_per_unit-has_formula']  = false;
                    $dataStructure[$idx]['quantity_per_unit-has_build_up'] = false;

                    unset( $dataStructure[$idx]['rate'] );
                }

                $data['elements'][$key] = array(
                    'info'  => $sheet->getElementInfo(),
                    'items' => $dataStructure
                );
            }
        }

        return $data;
    }
}

class sfImportExcelBuildspaceSheet {

    private $sheet;
    private $highestRow;
    private $highestColumn;
    private $excelProperties;
    private $billColumnSettings;

    private $firstCol;
    private $colDescription;
    private $colUnit;
    private $colRate;
    private $colQty;
    private $colAmount;
    private $lastCol;

    private $dataStructure;
    private $elementInfo;

    const ROW_BILL_REF = 0;
    const ROW_DESCRIPTION = 1;
    const ROW_UNIT = 2;

    public function __construct(PHPExcel_Worksheet $sheet, SplFixedArray $billColumnSettings)
    {
        $this->excelProperties = $sheet->getParent()->getProperties();
        $this->sheet           = $sheet;
        $this->highestRow      = $sheet->getHighestRow();
        $this->highestColumn   = $sheet->getHighestColumn();

        $this->dataStructure = new SplFixedArray(0);

        $this->billColumnSettings = $billColumnSettings;

        $this->prepare();

        $this->createDataStructure();
    }

    private function prepare()
    {
        $this->colRate = $this->billColumnSettings->getSize() > 1 ? 5 : 6;

        $this->firstCol       = $this->colItem = Utilities::generateCharFromNumber(2, true);
        $this->colDescription = Utilities::generateCharFromNumber(3, true);
        $this->colUnit        = Utilities::generateCharFromNumber(4, true);

        if ( $this->billColumnSettings->getSize() == 1 )
        {
            $this->colQty    = Utilities::generateCharFromNumber(5, true);
            $this->colAmount = Utilities::generateCharFromNumber(7, true);
        }

        //each column setting has qty and rate and at the end we need to add 2 more columns for total qty and total amount (multitype columns)
        $this->lastCol = $this->billColumnSettings->getSize() > 1 ? $this->colRate + ( $this->billColumnSettings->getSize() * 2 ) + 2 : $this->colRate + 1;
    }

    private function createDataStructure()
    {
        $startRow = $this->billColumnSettings->getSize() > 1 ? 6 : 5;

        for ( $row = $startRow; $row <= $this->highestRow; $row ++ )
        {
            $rangeArray = $this->sheet->rangeToArray($this->firstCol . $row . ':' . $this->highestColumn . $row, null, false, false, false);

            $isEmpty = false;

            //to check either all columns in row are empty or not. We skip if all columns in row are empty
            foreach ( $rangeArray[0] as $arr )
            {
                if ( empty( $arr ) )
                {
                    $isEmpty = true;
                }
                else
                {
                    $isEmpty = false;
                    break;//even if one column is not empty we will insert it into data struct
                }
            }

            if ( !$isEmpty )
            {
                $this->dataStructure->setSize($this->dataStructure->getSize() + 1);
                $this->dataStructure[$this->dataStructure->getSize() - 1] = $rangeArray[0];
            }
        }

        $this->massageDataStructure();
    }

    private function massageDataStructure()
    {
        $colRowType  = $this->lastCol;
        $colItemType = $colRowType + 1;
        $colLft      = $colItemType + 1;
        $colRgt      = $colLft + 1;
        $colLevel    = $colRgt + 1;

        $items = new SplFixedArray(0);

        $rateIdx = $this->colRate - 2;

        foreach ( $this->dataStructure as $idx => $data )
        {
            $item = array(
                'id'          => $idx . '-' . $this->sheet->getParent()->getIndex($this->sheet),
                'description' => preg_replace('!\s+!', ' ', trim($data[self::ROW_DESCRIPTION]))
            );

            switch (strtolower($data[$colRowType]))
            {
                case 'element':
                    if ( !is_array($this->elementInfo) )
                    {
                        $item['id']        = $this->sheet->getParent()->getIndex($this->sheet);
                        $this->elementInfo = $item;
                    }
                    break;
                case 'item':

                    $colQty = $this->billColumnSettings->getSize() > 1 ? $rateIdx + 1 : $rateIdx - 1;

                    switch ($data[$colItemType])
                    {
                        case BillItem::TYPE_ITEM_PC_RATE:
                            preg_match_all('!\d+(?:\.\d+)?!', $this->dataStructure[$idx + 2][self::ROW_DESCRIPTION], $matches);
                            $wastagePercentage = array_map('floatval', $matches[0]);

                            preg_match_all('!\d+(?:\.\d+)?!', $this->dataStructure[$idx + 5][self::ROW_DESCRIPTION], $matches);
                            $profitPercentage = array_map('floatval', $matches[0]);

                            $item['pc_supply_rate']             = $this->dataStructure[$idx + 1][$rateIdx] ? $this->dataStructure[$idx + 1][$rateIdx] : 0;
                            $item['pc_wastage_percentage']      = $wastagePercentage ? $wastagePercentage[0] : 0;
                            $item['pc_wastage_amount']          = $this->dataStructure[$idx + 2][$rateIdx] ? $this->dataStructure[$idx + 2][$rateIdx] : 0;
                            $item['pc_labour_for_installation'] = $this->dataStructure[$idx + 3][$rateIdx] ? $this->dataStructure[$idx + 3][$rateIdx] : 0;
                            $item['pc_other_cost']              = $this->dataStructure[$idx + 4][$rateIdx] ? $this->dataStructure[$idx + 4][$rateIdx] : 0;
                            $item['pc_profit_percentage']       = $profitPercentage ? $profitPercentage[0] : 0;
                            $item['pc_profit_amount']           = $this->dataStructure[$idx + 5][$rateIdx] ? $this->dataStructure[$idx + 5][$rateIdx] : 0;
                            $item['pc_total']                   = $data[$rateIdx];
                            break;
                        case BillItem::TYPE_ITEM_LUMP_SUM_PERCENT:
                            preg_match_all('!\d+(?:\.\d+)?!', $this->dataStructure[$idx][$this->lastCol - 1], $matches);
                            $lumpSumValues = array_map('floatval', $matches[0]);

                            $item['ls_percent_rate']       = $lumpSumValues ? $lumpSumValues[0] : 0;
                            $item['ls_percent_percentage'] = $lumpSumValues ? $lumpSumValues[1] : 0;
                            $item['ls_percent_amount']     = $lumpSumValues ? $lumpSumValues[0] * ( $lumpSumValues[1] / 100 ) : 0;
                            break;
                        default:
                            break;
                    }

                    $item['bill_ref']   = $data[self::ROW_BILL_REF];
                    $item['uom_symbol'] = $data[self::ROW_UNIT];
                    $item['rate']       = $data[$rateIdx];
                    $item['type']       = $data[$colItemType];
                    $item['lft']        = $data[$colLft];
                    $item['rgt']        = $data[$colRgt];
                    $item['level']      = $data[$colLevel];

                    foreach ( $this->billColumnSettings as $key => $billColumnSetting )
                    {
                        $item['quantity_per_unit-final_value-' . $key] = ( $data[$colItemType] == BillItem::TYPE_ITEM_RATE_ONLY ) ? 0 : $data[$colQty];
                        $item['total_per_unit-' . $key]                = $data[$colQty + 1];

                        $colQty = $colQty + 2;
                    }

                    $colQty = $this->billColumnSettings->getSize() > 1 ? $colQty : $rateIdx - 1;

                    $item['total_qty'] = $data[$colQty];
                    $item['total']     = $data[$colQty + 2];

                    $items->setSize($items->getSize() + 1);
                    $items[$items->getSize() - 1] = $item;

                    break;
                default:
                    break;
            }
        }

        $this->elementInfo['count'] = $items->getSize();
        $this->elementInfo['error'] = 0;

        $this->dataStructure = $items;

        unset( $items );
    }

    public function getElementInfo()
    {
        return $this->elementInfo;
    }

    public function getDataStructure()
    {
        return $this->dataStructure;
    }

}