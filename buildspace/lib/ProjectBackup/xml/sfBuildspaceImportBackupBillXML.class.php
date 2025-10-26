<?php

class sfBuildspaceImportBackupBillXML extends sfXMLReaderParser {
    public $parent;
    public $pdo;
    public $conn;
    public $userId;
    public $billId;
    public $layoutSettingId;
    public $columnSettingIds   = array();
    public $elementIds         = array();
    public $dimensionIds       = array();
    public $unitIds            = array();
    public $itemIds            = array();
    public $typeRefIds         = array();
    public $floorAreaIds       = array();
    public $bQtyIds            = array();
    public $brItemIds          = array();
    public $brResourceIds      = array();
    public $brTradeIds         = array();
    public $resourceLibraryIds = array();
    public $tradeLibraryIds    = array();
    public $revision;

    function __construct($userId, ProjectStructure $parent, $filename, $uploadPath = false, $extension = 'xml', $deleteFile = false, Doctrine_Connection $conn = null)
    {
        $this->parent = $parent;

        $this->conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();

        $this->pdo = $conn->getDbh();

        $this->userId = $userId;

        parent::__construct($filename, $uploadPath, $extension, $deleteFile);

        $this->revision = ProjectRevisionTable::getOriginalProjectRevisionFromBillId($this->parent->root_id, Doctrine_Core::HYDRATE_ARRAY);
    }

    public function process()
    {
        $billSetting = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_SETTING);

        if( $billSetting )
        {
            $billData = array(
                'title'    => $billSetting['title'],
                'type'     => ProjectStructure::TYPE_BILL,
                'priority' => 0,
                'root_id'  => $this->parent->root_id
            );

            if( ! $this->getBillByTitle($billData['title'], $billData['root_id']) )
            {
                $this->billId = $this->processBill($billData);

                $this->processBillSetting($billSetting);

                if( $billMarkupSetting = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_MARKUP_SETTING) )
                {
                    $this->processBillMarkupSetting($billMarkupSetting);
                    unset( $billMarkupSetting );
                }

                if( $billType = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_TYPE) )
                {
                    $this->processBillType($billType);
                    unset( $billType );
                }

                if( $billLayoutSetting = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_LAYOUT_SETTING) )
                {
                    $this->layoutSettingId = $this->processBillLayoutSetting($billLayoutSetting);
                    unset( $billLayoutSetting );
                }

                if( $billLayoutPhrase = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_LAYOUT_PHRASE) )
                {
                    $this->processBillPhrase($billLayoutPhrase);
                    unset( $billLayoutPhrase );
                }

                $billHeadSetting = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_LAYOUT_HEAD_SETTING);

                if( count($billHeadSetting['ITEM']) )
                {
                    $this->processBillHeadSetting($billHeadSetting);
                }

                $billColumnSettingData = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_COLUMN_SETTING);

                if( is_array($billColumnSettingData) && count($billColumnSettingData) )
                {
                    $this->columnSettingIds = $this->processBillColumnSetting($billColumnSettingData);
                }

                $floorAreaItems = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_BUILD_UP_FLOOR_AREA);

                if( is_array($floorAreaItems) && count($floorAreaItems) )
                {
                    $this->floorAreaIds = $this->processFloorArea($floorAreaItems);

                    unset( $floorAreaItems );
                }

                $floorAreaFC = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_BUILD_UP_FLOOR_AREA_FC);

                if( is_array($floorAreaFC) && count($floorAreaFC) )
                {
                    $this->processFloorAreaFC($floorAreaFC);
                }

                $floorAreaSummary = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_BUILD_UP_FLOOR_AREA_SUMMARY);

                if( is_array($floorAreaSummary) && count($floorAreaSummary) )
                {
                    $this->processFloorAreaSummary($floorAreaSummary);
                }

                $elementData = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ELEMENT);

                if( is_array($elementData) && count($elementData) )
                {
                    $this->elementIds = $this->processBillElement($elementData);
                }

                $elementFC = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ELEMENT_FC);

                if( is_array($elementFC) && count($elementFC) )
                {
                    $this->processElementFC($elementFC);

                    unset( $elementFC );
                }

                $dimensionData = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_DIMENSION);

                if( is_array($dimensionData) && count($dimensionData) )
                {
                    $this->dimensionIds = $this->processDimensions($dimensionData);
                }

                $unitData = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_UOM);

                if( is_array($unitData) && count($unitData) )
                {
                    $this->unitIds = $this->processUnit($unitData);
                }

                $billItems = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM);

                if( is_array($billItems) && count($billItems) )
                {
                    $this->itemIds = $this->processItem($billItems);
                }

                $lsItems = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_LS_PERCENT);

                if( is_array($lsItems) && count($lsItems) )
                {
                    $this->processLSItems($lsItems);
                }

                $pcItems = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_PC_RATE);

                if( is_array($pcItems) && count($pcItems) )
                {
                    $this->processPCItems($pcItems);
                }

                $typeReferences = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_TYPE_REF);

                if( is_array($typeReferences) && count($typeReferences) )
                {
                    $this->typeRefIds = $this->processTypeRef($typeReferences);
                }

                $typeFC = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_TYPE_REF_FC);

                if( is_array($typeFC) && count($typeFC) )
                {
                    $this->processTypeFC($typeFC);
                }

                $itemFC = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_FC);

                if( is_array($itemFC) && count($itemFC) )
                {
                    $this->processItemFC($itemFC);
                }

                $buildUpQty = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_BUILD_UP_QTY);

                if( is_array($buildUpQty) && count($buildUpQty) )
                {
                    $this->bQtyIds = $this->processBQty($buildUpQty);
                }

                $bQtyFC = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_BUILD_UP_QTY_FC);

                if( is_array($bQtyFC) && count($bQtyFC) )
                {
                    $this->processBQtyFC($bQtyFC);
                }

                $buildUpQtySummary = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_BUILD_UP_SUMMARY);

                if( is_array($buildUpQtySummary) && count($buildUpQtySummary) )
                {
                    $this->processBQtySummary($buildUpQtySummary);
                }

                $brResources = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_BUILD_UP_RATE_RESOURCE);

                if( is_array($brResources) && count($brResources) )
                {
                    $this->brResourceIds = $this->processBRResources($brResources);
                }

                $brTrades = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_BUILD_UP_RATE_TRADE);

                if( is_array($brTrades) && count($brTrades) )
                {
                    $this->brTradeIds = $this->processBRTrades($brTrades);
                }

                $brItems = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_BUILD_UP_RATE);

                if( is_array($brItems) && count($brItems) )
                {
                    $this->brItemIds = $this->processBRItems($brItems);
                }

                $brItemFCs = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_BUILD_UP_RATE_FC);

                if( is_array($brItemFCs) && count($brItemFCs) )
                {
                    $this->processBRItemFC($brItemFCs);
                }

                $brSummary = $this->getBySingleTag(sfBuildspaceBackupBillXML::TAG_BILL_ITEM_BUILD_UP_RATE_SUMMARY);

                if( is_array($brSummary) && count($brSummary) )
                {
                    $this->processBRSummary($brSummary);
                }
            }
            else
            {
                throw new Exception("Bill With The Same Name Already Exists", 1);
            }
        }
    }

    public function processBill(Array $billData)
    {
        if( $this->parent->type == ProjectStructure::TYPE_ROOT || $this->parent->type == ProjectStructure::TYPE_LEVEL )
        {
            $billData['lft']   = $this->parent->lft + 1;
            $billData['level'] = $this->parent->level + 1;
        }
        else
        {
            $billData['lft']   = $this->parent->rgt + 1;
            $billData['level'] = $this->parent->level;
        }

        $billData['rgt']        = $billData['lft'] + 1;
        $billData['created_at'] = 'NOW';
        $billData['updated_at'] = 'NOW';
        $billData['created_by'] = $this->userId;
        $billData['updated_by'] = $this->userId;

        $this->updateProjectStructureLeftTree($billData['lft'], $billData['root_id']);
        $this->updateProjectStructureRightTree($billData['lft'], $billData['root_id']);

        $stmt = new sfImportStatementGenerator();

        $data = $this->generateDataStructure($billData);
        unset( $billData );

        $stmt->createInsert(ProjectStructureTable::getInstance()->getTableName(), $data['structure']);

        $stmt->addRecord($data['value']);

        $stmt->save();

        return $stmt->returningIds[0];
    }

    public function processBillSetting(Array $billSettingData)
    {
        if( array_key_exists('id', $billSettingData) )
        {
            unset( $billSettingData['id'] );
        }

        $billSettingData['project_structure_id'] = $this->billId;
        $billSettingData['created_at']           = 'NOW';
        $billSettingData['updated_at']           = 'NOW';
        $billSettingData['created_by']           = $this->userId;
        $billSettingData['updated_by']           = $this->userId;

        $data = $this->generateDataStructure($billSettingData);
        unset( $billSettingData );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillSettingTable::getInstance()->getTableName(), $data['structure']);

        $stmt->addRecord($data['value']);

        $stmt->save();

        return $stmt->returningIds[0];
    }

    public function processBillMarkupSetting(Array $billMarkupSettingData)
    {
        if( array_key_exists('id', $billMarkupSettingData) )
        {
            unset( $billMarkupSettingData['id'] );
        }

        $billMarkupSettingData['bill_markup_enabled']    = ( $billMarkupSettingData['bill_markup_enabled'] != null ) ? $billMarkupSettingData['bill_markup_enabled'] : 0;
        $billMarkupSettingData['bill_markup_percentage'] = ( $billMarkupSettingData['bill_markup_percentage'] != null ) ? $billMarkupSettingData['bill_markup_percentage'] : 0;
        $billMarkupSettingData['bill_markup_amount']     = ( $billMarkupSettingData['bill_markup_amount'] != null ) ? $billMarkupSettingData['bill_markup_amount'] : 0;
        $billMarkupSettingData['element_markup_enabled'] = ( $billMarkupSettingData['element_markup_enabled'] != null ) ? $billMarkupSettingData['element_markup_enabled'] : 0;
        $billMarkupSettingData['item_markup_enabled']    = ( $billMarkupSettingData['item_markup_enabled'] != null ) ? $billMarkupSettingData['item_markup_enabled'] : 0;
        $billMarkupSettingData['rounding_type']          = ( $billMarkupSettingData['rounding_type'] != null ) ? $billMarkupSettingData['rounding_type'] : 0;
        $billMarkupSettingData['project_structure_id']   = $this->billId;
        $billMarkupSettingData['created_at']             = 'NOW';
        $billMarkupSettingData['updated_at']             = 'NOW';
        $billMarkupSettingData['created_by']             = $this->userId;
        $billMarkupSettingData['updated_by']             = $this->userId;

        $data = $this->generateDataStructure($billMarkupSettingData);
        unset( $billMarkupSettingData );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillMarkupSettingTable::getInstance()->getTableName(), $data['structure']);

        $stmt->addRecord($data['value']);

        $stmt->save();

        return $stmt->returningIds[0];
    }

    public function processBillType(Array $billTypeData)
    {
        if( array_key_exists('id', $billTypeData) )
        {
            unset( $billTypeData['id'] );
        }

        $billTypeData['project_structure_id'] = $this->billId;
        $billTypeData['created_at']           = 'NOW';
        $billTypeData['updated_at']           = 'NOW';
        $billTypeData['created_by']           = $this->userId;
        $billTypeData['updated_by']           = $this->userId;

        $data = $this->generateDataStructure($billTypeData);
        unset( $billTypeData );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillTypeTable::getInstance()->getTableName(), $data['structure']);

        $stmt->addRecord($data['value']);

        $stmt->save();

        return $stmt->returningIds[0];
    }

    public function processBillLayoutSetting(Array $layoutSettingData)
    {
        if( array_key_exists('id', $layoutSettingData) )
        {
            unset( $layoutSettingData['id'] );
        }

        $layoutSettingData['page_no_prefix'] = ( $layoutSettingData['page_no_prefix'] != null ) ? $layoutSettingData['page_no_prefix'] : '';
        $layoutSettingData['bill_id']        = $this->billId;
        $layoutSettingData['created_at']     = 'NOW';
        $layoutSettingData['updated_at']     = 'NOW';
        $layoutSettingData['created_by']     = $this->userId;
        $layoutSettingData['updated_by']     = $this->userId;

        $data = $this->generateDataStructure($layoutSettingData, 'false');
        unset( $layoutSettingData );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillLayoutSettingTable::getInstance()->getTableName(), $data['structure']);

        $stmt->addRecord($data['value']);

        $stmt->save();

        return $stmt->returningIds[0];
    }

    public function processBillPhrase(Array $billPhraseData)
    {
        if( array_key_exists('id', $billPhraseData) )
        {
            unset( $billPhraseData['id'] );
        }

        $billPhraseData['element_note_top_left_row1']  = ( $billPhraseData['element_note_top_left_row1'] != null ) ? $billPhraseData['element_note_top_left_row1'] : '';
        $billPhraseData['element_note_top_left_row2']  = ( $billPhraseData['element_note_top_left_row2'] != null ) ? $billPhraseData['element_note_top_left_row2'] : '';
        $billPhraseData['element_note_top_right_row1'] = ( $billPhraseData['element_note_top_right_row1'] != null ) ? $billPhraseData['element_note_top_right_row1'] : '';
        $billPhraseData['element_note_bot_left_row1']  = ( $billPhraseData['element_note_bot_left_row1'] != null ) ? $billPhraseData['element_note_bot_left_row1'] : '';
        $billPhraseData['element_note_bot_left_row2']  = ( $billPhraseData['element_note_bot_left_row2'] != null ) ? $billPhraseData['element_note_bot_left_row2'] : '';
        $billPhraseData['element_note_bot_right_row1'] = ( $billPhraseData['element_note_bot_right_row1'] != null ) ? $billPhraseData['element_note_bot_right_row1'] : '';
        $billPhraseData['element_note_bot_right_row2'] = ( $billPhraseData['element_note_bot_right_row2'] != null ) ? $billPhraseData['element_note_bot_right_row2'] : '';

        $billPhraseData['bill_layout_setting_id'] = $this->layoutSettingId;
        $billPhraseData['created_at']             = 'NOW';
        $billPhraseData['updated_at']             = 'NOW';
        $billPhraseData['created_by']             = $this->userId;
        $billPhraseData['updated_by']             = $this->userId;

        $data = $this->generateDataStructure($billPhraseData, 'false');
        unset( $billPhraseData );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillLayoutPhraseTable::getInstance()->getTableName(), $data['structure']);

        $stmt->addRecord($data['value']);

        $stmt->save();

        return $stmt->returningIds[0];
    }

    public function processBillHeadSetting(Array $headSettingData)
    {
        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillLayoutHeadSettingTable::getInstance()->getTableName(), array(
            'bill_layout_setting_id', 'head', 'bold', 'underline', 'italic', 'created_at',
            'updated_at', 'created_by', 'updated_by'
        ));

        foreach($headSettingData['ITEM'] as $head)
        {
            if( array_key_exists('id', $head) )
            {
                unset( $head['id'] );
            }

            $stmt->addRecord(array(
                $this->layoutSettingId,
                $head['head'],
                ( $head['bold'] ) ? $head['bold'] : 0,
                ( $head['underline'] ) ? $head['underline'] : 0,
                ( $head['italic'] ) ? $head['italic'] : 0,
                'NOW()', 'NOW()', $this->userId, $this->userId
            ));

            unset( $head );
        }

        $stmt->save();
    }

    public function processBillColumnSetting(Array $columnSettingData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillColumnSettingTable::getInstance()->getTableName(), array(
            'name', 'quantity', 'project_structure_id', 'remeasurement_quantity_enabled', 'use_original_quantity',
            'total_floor_area_m2', 'total_floor_area_ft2', 'floor_area_has_build_up', 'floor_area_use_metric',
            'floor_area_display_metric', 'show_estimated_total_cost', 'is_hidden', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        $originalId = null;

        foreach($columnSettingData as $columnData)
        {
            if( ! array_key_exists(0, $columnData) )
            {
                $tmp           = $columnData;
                $columnData    = array();
                $columnData[0] = $tmp;
            }

            foreach($columnData as $column)
            {
                if( array_key_exists('id', $column) )
                {
                    $originalId = $column['id'];
                    unset( $column['id'] );
                }

                $stmt->addRecord(array(
                    $column['name'],
                    $column['quantity'],
                    $this->billId,
                    ( $column['remeasurement_quantity_enabled'] ) ? $column['remeasurement_quantity_enabled'] : 0,
                    ( $column['use_original_quantity'] ) ? $column['use_original_quantity'] : 0,
                    $column['total_floor_area_m2'],
                    $column['total_floor_area_ft2'],
                    ( $column['floor_area_has_build_up'] ) ? $column['floor_area_has_build_up'] : 0,
                    ( $column['floor_area_use_metric'] ) ? $column['floor_area_use_metric'] : 0,
                    ( $column['floor_area_display_metric'] ) ? $column['floor_area_display_metric'] : 0,
                    ( $column['show_estimated_total_cost'] ) ? $column['show_estimated_total_cost'] : 0,
                    ( $column['is_hidden'] ) ? $columnData['is_hidden'] : 0,
                    'NOW()', 'NOW()', $this->userId, $this->userId
                ), $originalId);

                unset( $column );
            }

            unset( $columnData );
        }

        $stmt->save();
        $stmt->records     = new SplFixedArray(0);
        $stmt->originalIds = new SplFixedArray(0);

        return $stmt->returningIds;
    }

    public function processBillElement(Array $elementData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillElementTable::getInstance()->getTableName(), array(
            'description', 'note', 'project_structure_id', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        $originalId = null;

        foreach($elementData as $elements)
        {
            if( ! array_key_exists(0, $elements) )
            {
                $tmp         = $elements;
                $elements    = array();
                $elements[0] = $tmp;
            }

            foreach($elements as $element)
            {
                if( array_key_exists('id', $element) )
                {
                    $originalId = $element['id'];
                    unset( $element['id'] );
                }

                $stmt->addRecord(array(
                    $element['description'],
                    ( $element['note'] ) ? $element['note'] : null,
                    $this->billId,
                    ( $element['priority'] ) ? $element['priority'] : 0,
                    'NOW()', 'NOW()', $this->userId, $this->userId
                ), $originalId);

                unset( $element );
            }

            unset( $elements );
        }

        $stmt->save();
        $stmt->records     = new SplFixedArray(0);
        $stmt->originalIds = new SplFixedArray(0);

        return $stmt->returningIds;
    }

    public function processDimensions(Array $dimensionData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(DimensionTable::getInstance()->getTableName(), array(
            'name', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        $existingDimensionIds     = array();
        $existingDimensionByNames = new SplFixedArray(0);

        foreach($dimensionData as $i => $dimensions)
        {
            if( ! array_key_exists(0, $dimensions) )
            {
                $tmp           = $dimensions;
                $dimensions    = array();
                $dimensions[0] = $tmp;

                $dimensionData[ $i ] = $dimensions;
            }

            foreach($dimensions as $dimension)
            {
                $existingDimension = null;

                if( ! in_array($dimension['name'], $existingDimensionByNames->toArray()) )
                {
                    $existingDimensionByNames->setSize($existingDimensionByNames->getSize() + 1);

                    $existingDimensionByNames[ $existingDimensionByNames->getSize() - 1 ] = strtolower($dimension['name']);
                }
            }
        }

        $existingDimensions = $this->getDimensionsByNames($existingDimensionByNames);

        unset( $existingDimensionByNames );

        foreach($dimensionData as $dimensions)
        {
            foreach($dimensions as $dimension)
            {
                if( array_key_exists(strtolower($dimension['name']), $existingDimensions) )
                {
                    $existingDimensionIds[ $dimension['id'] ] = $existingDimensions[ strtolower($dimension['name']) ];
                }
                else
                {
                    if( array_key_exists('id', $dimension) )
                    {
                        $originalId = $dimension['id'];
                        unset( $dimension['id'] );

                        $stmt->addRecord(array(
                            $dimension['name'],
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }
                }

                unset( $dimension );
            }
        }

        unset( $dimensionData );

        if( count($stmt->records) )
        {
            $stmt->save();

            $result = $existingDimensionIds + $stmt->returningIds;
        }
        else
        {
            $result = $existingDimensionIds;
        }

        return $result;
    }

    public function processUnit($unitData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(UnitOfMeasurementTable::getInstance()->getTableName(), array(
            'name', 'symbol', 'display', 'type', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        $existingUnitIds = array();

        $unitDimensionXref = array();

        $symbols = new SplFixedArray(0);

        foreach($unitData as $i => $units)
        {
            if( ! array_key_exists(0, $units) )
            {
                $tmp      = $units;
                $units    = array();
                $units[0] = $tmp;

                $unitData[ $i ] = $units;
            }

            foreach($units as $unit)
            {
                $existingUnit = null;

                if( ! is_array($unit['symbol']) )
                {
                    if( ! in_array($unit['symbol'], $symbols->toArray()) )
                    {
                        $symbols->setSize($symbols->getSize() + 1);

                        $symbols[ $symbols->getSize() - 1 ] = strtolower($unit['symbol']);
                    }
                }
            }
        }

        $existingSymbols = $this->getUnitsBySymbols($symbols);

        foreach($unitData as $units)
        {
            foreach($units as $unit)
            {
                if( ! is_array($unit['symbol']) )
                {
                    if( $existingSymbols and array_key_exists(strtolower($unit['symbol']), $existingSymbols) )
                    {
                        $existingUnitIds[ $unit['id'] ] = $existingSymbols[ strtolower($unit['symbol']) ];
                    }
                    else
                    {
                        if( array_key_exists('id', $unit) )
                        {
                            $originalId = $unit['id'];
                            unset( $unit['id'] );

                            if( array_key_exists('DIMENSION', $unit) )
                            {
                                $unitDimensionXref[ $originalId ] = $unit['DIMENSION'];
                                unset( $unit['DIMENSION'] );
                            }

                            $stmt->addRecord(array(
                                $unit['name'],
                                $unit['symbol'],
                                1,
                                $unit['type'],
                                'NOW()', 'NOW()', $this->userId, $this->userId
                            ), $originalId);
                        }
                    }
                }

                unset( $unit );
            }

            unset( $units, $existingSymbols );
        }

        unset( $unitData );

        if( count($stmt->records) )
        {
            $stmt->save();

            $result = $existingUnitIds + $stmt->returningIds;
        }
        else
        {
            $result = $existingUnitIds;
        }

        return $result;
    }

    public function processItem(Array $billItemData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $structure = array(
            'description', 'note', 'type', 'element_id', 'uom_id', 'grand_total_quantity', 'grand_total',
            'grand_total_after_markup', 'bill_ref_element_no', 'bill_ref_page_no', 'bill_ref_char', 'priority',
            'root_id', 'lft', 'rgt', 'level', 'project_revision_id', 'created_at', 'updated_at', 'created_by', 'updated_by'
        );

        $stmt->createInsert(BillItemTable::getInstance()->getTableName(), $structure);

        $rootIds    = array();
        $originalId = -1;

        foreach($billItemData as $items)
        {
            if( array_key_exists(0, $items) && is_array($items[0]) )
            {
                //Multidimensional
                foreach($items as $item)
                {
                    if( array_key_exists('id', $item) )
                    {
                        $originalId = $item['id'];
                        unset( $item['id'] );
                    }

                    $unitId    = ( ! is_array($item['uom_id']) && array_key_exists($item['uom_id'], $this->unitIds) ) ? $this->unitIds[ $item['uom_id'] ] : null;
                    $elementId = ( array_key_exists($item['element_id'], $this->elementIds) ) ? $this->elementIds[ $item['element_id'] ] : null;

                    if( $item['root_id'] == $originalId )
                    {
                        $rootStmt = new sfImportExcelStatementGenerator();

                        $rootStmt->createInsert(BillItemTable::getInstance()->getTableName(), $structure);

                        $rootStmt->addRecord(array(
                            ( $item['description'] ) ? $item['description'] : null, ( $item['note'] ) ? $item['note'] : null, $item['type'], $elementId,
                            $unitId, ( $item['grand_total_quantity'] ) ? $item['grand_total_quantity'] : 0, ( $item['grand_total'] ) ? $item['grand_total'] : 0,
                            ( $item['grand_total_after_markup'] ) ? $item['grand_total_after_markup'] : 0, ( $item['bill_ref_element_no'] ) ? $item['bill_ref_element_no'] : null,
                            ( $item['bill_ref_page_no'] ) ? $item['bill_ref_page_no'] : null, ( $item['bill_ref_char'] ) ? $item['bill_ref_char'] : null,
                            $item['priority'], null, $item['lft'], $item['rgt'], $item['level'], $this->revision['id'],
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ));

                        $rootStmt->save();

                        $rootIds[ $originalId ] = $rootStmt->returningIds[0];

                        $rootStmt->updateRootId($rootIds[ $originalId ], $rootIds[ $originalId ]);
                    }
                    else
                    {
                        $stmt->addRecord(array(
                            ( $item['description'] ) ? $item['description'] : null, ( $item['note'] ) ? $item['note'] : null, $item['type'], $elementId,
                            $unitId, ( $item['grand_total_quantity'] ) ? $item['grand_total_quantity'] : 0, ( $item['grand_total'] ) ? $item['grand_total'] : 0,
                            ( $item['grand_total_after_markup'] ) ? $item['grand_total_after_markup'] : 0, ( $item['bill_ref_element_no'] ) ? $item['bill_ref_element_no'] : null,
                            ( $item['bill_ref_page_no'] ) ? $item['bill_ref_page_no'] : null, ( $item['bill_ref_char'] ) ? $item['bill_ref_char'] : null,
                            $item['priority'], $rootIds[ $item['root_id'] ], $item['lft'], $item['rgt'], $item['level'], $this->revision['id'],
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }

                    unset( $item );
                }

                unset( $items );
            }
            else
            {
                //Single
                if( array_key_exists('id', $items) )
                {
                    $originalId = $items['id'];
                    unset( $items['id'] );
                }

                $unitId    = ( ! is_array($items['uom_id']) && array_key_exists($items['uom_id'], $this->unitIds) ) ? $this->unitIds[ $items['uom_id'] ] : null;
                $elementId = ( array_key_exists($items['element_id'], $this->elementIds) ) ? $this->elementIds[ $items['element_id'] ] : null;

                if( $items['type'] == BillItem::TYPE_HEADER )
                {
                    $rootStmt = new sfImportExcelStatementGenerator();

                    $rootStmt->createInsert(BillItemTable::getInstance()->getTableName(), $structure);

                    $rootStmt->addRecord(array(
                        ( $items['description'] ) ? $items['description'] : null, ( $items['note'] ) ? $items['note'] : null, $items['type'], $elementId,
                        $unitId, ( $items['grand_total_quantity'] ) ? $items['grand_total_quantity'] : 0, ( $items['grand_total'] ) ? $items['grand_total'] : 0,
                        ( $items['grand_total_after_markup'] ) ? $items['grand_total_after_markup'] : 0, ( $items['bill_ref_element_no'] ) ? $items['bill_ref_element_no'] : null,
                        ( $items['bill_ref_page_no'] ) ? $items['bill_ref_page_no'] : null, ( $items['bill_ref_char'] ) ? $items['bill_ref_char'] : null,
                        $items['priority'], null, $items['lft'], $items['rgt'], $items['level'], $this->revision['id'],
                        'NOW()', 'NOW()', $this->userId, $this->userId
                    ));

                    $rootStmt->save();

                    $rootIds[ $originalId ] = $rootStmt->returningIds[0];

                    $rootStmt->updateRootId($rootIds[ $originalId ], $rootIds[ $originalId ]);
                }
                else
                {
                    $rootId = ( array_key_exists($items['root_id'], $rootIds) ) ? $rootIds[ $items['root_id'] ] : null;

                    $stmt->addRecord(array(
                        ( $items['description'] ) ? $items['description'] : null, ( $items['note'] ) ? $items['note'] : null, $items['type'], $elementId,
                        $unitId, ( $items['grand_total_quantity'] ) ? $items['grand_total_quantity'] : 0, ( $items['grand_total'] ) ? $items['grand_total'] : 0,
                        ( $items['grand_total_after_markup'] ) ? $items['grand_total_after_markup'] : 0, ( $items['bill_ref_element_no'] ) ? $items['bill_ref_element_no'] : null,
                        ( $items['bill_ref_page_no'] ) ? $items['bill_ref_page_no'] : null, ( $items['bill_ref_char'] ) ? $items['bill_ref_char'] : null,
                        $items['priority'], $rootId, $items['lft'], $items['rgt'], $items['level'], $this->revision['id'],
                        'NOW()', 'NOW()', $this->userId, $this->userId
                    ), $originalId);
                }
            }
        }

        if( count($stmt->records) )
        {
            $stmt->save();

            $result = $rootIds + $stmt->returningIds;
        }
        else
        {
            $result = $rootIds;
        }

        return $result;
    }

    public function processFloorArea(Array $floorAreaData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillBuildUpFloorAreaItemTable::getInstance()->getTableName(), array(
            'bill_column_setting_id', 'description', 'total', 'sign',
            'priority', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        foreach($floorAreaData as $i => $floorAreas)
        {
            if( ! array_key_exists(0, $floorAreas) )
            {
                $tmp           = $floorAreas;
                $floorAreas    = array();
                $floorAreas[0] = $tmp;

                $floorAreaData[ $i ] = $floorAreas;
            }
        }

        foreach($floorAreaData as $floorAreas)
        {
            foreach($floorAreas as $fItem)
            {
                if( array_key_exists('id', $fItem) )
                {
                    $originalId = $fItem['id'];
                    unset( $fItem['id'] );

                    if( ! is_array($fItem['bill_column_setting_id']) && array_key_exists($fItem['bill_column_setting_id'], $this->columnSettingIds) )
                    {
                        $stmt->addRecord(array(
                            $this->columnSettingIds[ $fItem['bill_column_setting_id'] ],
                            ( $fItem['description'] ) ? $fItem['description'] : null,
                            ( $fItem['total'] ) ? $fItem['total'] : 0,
                            ( $fItem['sign'] ) ? $fItem['sign'] : null,
                            ( $fItem['priority'] ) ? $fItem['priority'] : 0,
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }
                }

                unset( $fItem );
            }
        }

        $stmt->save();

        return $stmt->returningIds;
    }

    public function processFloorAreaFC(Array $floorAreaFcData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillBuildUpFloorAreaFormulatedColumnTable::getInstance()->getTableName(), array(
            'relation_id', 'column_name', 'value', 'final_value',
            'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        $originalRelationIdToNodeId = array();

        $originalNodePointer = new SplFixedArray(0);

        foreach($floorAreaFcData as $i => $floorAreaFcs)
        {
            if( ! array_key_exists(0, $floorAreaFcs) )
            {
                if( array_key_exists('id', $floorAreaFcs) && $floorAreaFcs['column_name'] )
                {
                    if( ! array_key_exists($floorAreaFcs['relation_id'] . "-" . $floorAreaFcs['column_name'], $originalRelationIdToNodeId) )
                    {
                        $originalRelationIdToNodeId[ $floorAreaFcs['relation_id'] . "-" . $floorAreaFcs['column_name'] ] = $floorAreaFcs['id'];
                    }
                }

                $tmp             = $floorAreaFcs;
                $floorAreaFcs    = array();
                $floorAreaFcs[0] = $tmp;

                $floorAreaFcData[ $i ] = $floorAreaFcs;
            }
            else
            {
                foreach($floorAreaFcs as $fc)
                {
                    if( array_key_exists('id', $fc) && $fc['column_name'] )
                    {
                        if( ! array_key_exists($fc['relation_id'] . "-" . $fc['column_name'], $originalRelationIdToNodeId) )
                        {
                            $originalRelationIdToNodeId[ $fc['relation_id'] . "-" . $fc['column_name'] ] = $fc['id'];
                        }
                    }
                }

            }
        }

        foreach($floorAreaFcData as $floorAreaFcs)
        {
            foreach($floorAreaFcs as $fc)
            {
                if( array_key_exists('id', $fc) )
                {
                    $originalId = $fc['id'];

                    unset( $fc['id'] );

                    if( ! array_key_exists($fc['relation_id'], $originalRelationIdToNodeId) )
                    {
                        $originalRelationIdToNodeId[ $fc['relation_id'] ] = $originalId;
                    }

                    if( $matchesFormula = $this->hasReferencesInFormula($fc['value']) )
                    {
                        $fc['value'] = $this->remapRowLinking($matchesFormula, $originalId, $fc['value'], $fc['column_name'], $this->floorAreaIds, $originalRelationIdToNodeId, $originalNodePointer);
                    }

                    if( ! is_array($fc['relation_id']) && array_key_exists($fc['relation_id'], $this->floorAreaIds) )
                    {
                        $stmt->addRecord(array(
                            $this->floorAreaIds[ $fc['relation_id'] ],
                            ( $fc['column_name'] ) ? $fc['column_name'] : null,
                            ( $fc['value'] ) ? $fc['value'] : 0,
                            ( $fc['final_value'] ) ? $fc['final_value'] : 0,
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }
                }

                unset( $fc );
            }

            unset( $floorAreaFcs );
        }

        unset( $originalRelationIdToNodeId );

        $stmt->save();

        $returningIds = $stmt->returningIds;

        if( $originalNodePointer->count() > 0 )
        {
            $this->insertEdgesData($stmt, BillBuildUpFloorAreaEdgeTable::getInstance(), $originalNodePointer, $returningIds);
        }

        return $returningIds;
    }

    public function processFloorAreaSummary(Array $floorAreaSummaryData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillBuildUpFloorAreaSummaryTable::getInstance()->getTableName(), array(
            'bill_column_setting_id', 'total_floor_area', 'final_floor_area', 'apply_conversion_factor',
            'conversion_factor_operator', 'conversion_factor_amount', 'rounding_type',
            'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        foreach($floorAreaSummaryData as $summaries)
        {
            if( array_key_exists(0, $summaries) && is_array($summaries[0]) )
            {
                //Multidimensional
                foreach($summaries as $summary)
                {
                    if( array_key_exists('id', $summary) )
                    {
                        $originalId = $summary['id'];
                        unset( $summary['id'] );
                    }

                    $columnId = ( ! is_array($summary['bill_column_setting_id']) && array_key_exists($summary['bill_column_setting_id'], $this->columnSettingIds) ) ? $this->columnSettingIds[ $summary['bill_column_setting_id'] ] : null;

                    $stmt->addRecord(array(
                        $columnId,
                        ( $summary['total_floor_area'] ) ? $summary['total_floor_area'] : 0,
                        ( $summary['final_floor_area'] ) ? $summary['final_floor_area'] : 0,
                        ( $summary['apply_conversion_factor'] ) ? $summary['apply_conversion_factor'] : null,
                        ( $summary['conversion_factor_operator'] ) ? $summary['conversion_factor_operator'] : null,
                        ( $summary['conversion_factor_amount'] ) ? $summary['conversion_factor_amount'] : 0,
                        ( $summary['rounding_type'] ) ? $summary['rounding_type'] : null,
                        'NOW()', 'NOW()', $this->userId, $this->userId
                    ), $originalId);

                    unset( $summary );
                }

                unset( $summaries );
            }
            else
            {
                //Single
                if( array_key_exists('id', $summaries) )
                {
                    $originalId = $summaries['id'];
                    unset( $summaries['id'] );
                }

                $columnId = ( ! is_array($summaries['bill_column_setting_id']) && array_key_exists($summaries['bill_column_setting_id'], $this->columnSettingIds) ) ? $this->columnSettingIds[ $summaries['bill_column_setting_id'] ] : null;

                $stmt->addRecord(array(
                    $columnId,
                    ( $summaries['total_floor_area'] ) ? $summaries['total_floor_area'] : 0,
                    ( $summaries['final_floor_area'] ) ? $summaries['final_floor_area'] : 0,
                    ( $summaries['apply_conversion_factor'] ) ? $summaries['apply_conversion_factor'] : null,
                    ( $summaries['conversion_factor_operator'] ) ? $summaries['conversion_factor_operator'] : null,
                    ( $summaries['conversion_factor_amount'] ) ? $summaries['conversion_factor_amount'] : 0,
                    ( $summaries['rounding_type'] ) ? $summaries['rounding_type'] : null,
                    'NOW()', 'NOW()', $this->userId, $this->userId
                ), $originalId);
            }
        }

        $stmt->save();

        return $stmt->returningIds;
    }

    public function processLSItems(Array $lsItemData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillItemLumpSumPercentageTable::getInstance()->getTableName(), array(
            'bill_item_id', 'rate', 'percentage', 'amount', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        foreach($lsItemData as $lsItems)
        {
            if( array_key_exists(0, $lsItems) && is_array($lsItems[0]) )
            {
                //Multidimensional
                foreach($lsItems as $lsItem)
                {
                    if( array_key_exists('id', $lsItem) )
                    {
                        $originalId = $lsItem['id'];

                        unset( $lsItem['id'] );
                    }

                    $itemId = ( ! is_array($lsItem['bill_item_id']) && array_key_exists($lsItem['bill_item_id'], $this->itemIds) ) ? $this->itemIds[ $lsItem['bill_item_id'] ] : null;

                    $stmt->addRecord(array(
                        $itemId, ( $lsItem['rate'] ) ? $lsItem['rate'] : 0,
                        ( $lsItem['percentage'] ) ? $lsItem['percentage'] : 0,
                        ( $lsItem['amount'] ) ? $lsItem['amount'] : 0,
                        'NOW()', 'NOW()', $this->userId, $this->userId
                    ), $originalId);

                    unset( $lsItem );
                }

                unset( $lsItems );
            }
            else
            {
                //Single
                if( array_key_exists('id', $lsItems) )
                {
                    $originalId = $lsItems['id'];
                    unset( $lsItems['id'] );
                }

                $itemId = ( ! is_array($lsItems['bill_item_id']) && array_key_exists($lsItems['bill_item_id'], $this->itemIds) ) ? $this->itemIds[ $lsItems['bill_item_id'] ] : null;

                $stmt->addRecord(array(
                    $itemId, ( $lsItems['rate'] ) ? $lsItems['rate'] : 0,
                    ( $lsItems['percentage'] ) ? $lsItems['percentage'] : 0,
                    ( $lsItems['amount'] ) ? $lsItems['amount'] : 0,
                    'NOW()', 'NOW()', $this->userId, $this->userId
                ), $originalId);

                unset( $lsItems );
            }
        }

        $stmt->save();

        return $stmt->returningIds;
    }

    public function processPCItems(Array $pcItemData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillItemPrimeCostRateTable::getInstance()->getTableName(), array(
            'bill_item_id', 'supply_rate', 'wastage_percentage', 'wastage_amount', 'labour_for_installation',
            'other_cost', 'profit_percentage', 'profit_amount', 'total', 'created_at', 'updated_at',
            'created_by', 'updated_by'
        ));

        foreach($pcItemData as $pcItems)
        {
            if( array_key_exists(0, $pcItems) && is_array($pcItems[0]) )
            {
                //Multidimensional
                foreach($pcItems as $pcItem)
                {
                    if( array_key_exists('id', $pcItem) )
                    {
                        $originalId = $pcItem['id'];

                        unset( $pcItem['id'] );
                    }

                    $itemId = ( ! is_array($pcItem['bill_item_id']) && array_key_exists($pcItem['bill_item_id'], $this->itemIds) ) ? $this->itemIds[ $pcItem['bill_item_id'] ] : null;

                    $stmt->addRecord(array(
                        $itemId,
                        ( $pcItem['supply_rate'] ) ? $pcItem['supply_rate'] : 0,
                        ( $pcItem['wastage_percentage'] ) ? $pcItem['wastage_percentage'] : 0,
                        ( $pcItem['wastage_amount'] ) ? $pcItem['wastage_amount'] : 0,
                        ( $pcItem['labour_for_installation'] ) ? $pcItem['labour_for_installation'] : 0,
                        ( $pcItem['other_cost'] ) ? $pcItem['other_cost'] : 0,
                        ( $pcItem['profit_percentage'] ) ? $pcItem['profit_percentage'] : 0,
                        ( $pcItem['profit_amount'] ) ? $pcItem['profit_amount'] : 0,
                        ( $pcItem['total'] ) ? $pcItem['total'] : 0,
                        'NOW()', 'NOW()', $this->userId, $this->userId
                    ), $originalId);

                    unset( $pcItem );
                }

                unset( $pcItems );
            }
            else
            {
                //Single
                if( array_key_exists('id', $pcItems) )
                {
                    $originalId = $pcItems['id'];
                    unset( $pcItems['id'] );
                }

                $itemId = ( ! is_array($pcItems['bill_item_id']) && array_key_exists($pcItems['bill_item_id'], $this->itemIds) ) ? $this->itemIds[ $pcItems['bill_item_id'] ] : null;

                $stmt->addRecord(array(
                    $itemId,
                    ( $pcItems['supply_rate'] ) ? $pcItems['supply_rate'] : 0,
                    ( $pcItems['wastage_percentage'] ) ? $pcItems['wastage_percentage'] : 0,
                    ( $pcItems['wastage_amount'] ) ? $pcItems['wastage_amount'] : 0,
                    ( $pcItems['labour_for_installation'] ) ? $pcItems['labour_for_installation'] : 0,
                    ( $pcItems['other_cost'] ) ? $pcItems['other_cost'] : 0,
                    ( $pcItems['profit_percentage'] ) ? $pcItems['profit_percentage'] : 0,
                    ( $pcItems['profit_amount'] ) ? $pcItems['profit_amount'] : 0,
                    ( $pcItems['total'] ) ? $pcItems['total'] : 0,
                    'NOW()', 'NOW()', $this->userId, $this->userId
                ), $originalId);

                unset( $lsItems );
            }
        }

        $stmt->save();

        return $stmt->returningIds;
    }

    public function processTypeRef(Array $typeRefData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillItemTypeReferenceTable::getInstance()->getTableName(), array(
            'bill_item_id', 'bill_column_setting_id', 'include', 'quantity_per_unit_difference', 'total_quantity',
            'grand_total', 'grand_total_after_markup', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        foreach($typeRefData as $typeRefs)
        {
            if( array_key_exists(0, $typeRefs) && is_array($typeRefs[0]) )
            {
                //Multidimensional
                foreach($typeRefs as $k => $type)
                {
                    if( array_key_exists('id', $type) )
                    {
                        $originalId = $type['id'];
                        unset( $type['id'] );
                    }

                    $itemId   = ( ! is_array($type['bill_item_id']) && array_key_exists($type['bill_item_id'], $this->itemIds) ) ? $this->itemIds[ $type['bill_item_id'] ] : null;
                    $columnId = ( ! is_array($type['bill_column_setting_id']) && array_key_exists($type['bill_column_setting_id'], $this->columnSettingIds) ) ? $this->columnSettingIds[ $type['bill_column_setting_id'] ] : null;

                    $stmt->addRecord(array(
                        $itemId, $columnId,
                        ( $type['include'] ) ? $type['include'] : 0,
                        ( $type['quantity_per_unit_difference'] ) ? $type['quantity_per_unit_difference'] : 0,
                        ( $type['total_quantity'] ) ? $type['total_quantity'] : 0,
                        ( $type['grand_total'] ) ? $type['grand_total'] : 0,
                        ( $type['grand_total_after_markup'] ) ? $type['grand_total_after_markup'] : 0,
                        'NOW()', 'NOW()', $this->userId, $this->userId
                    ), $originalId);

                    unset( $type );
                }

                unset( $typeRefs );
            }
            else
            {
                //Single
                if( array_key_exists('id', $typeRefs) )
                {
                    $originalId = $typeRefs['id'];
                    unset( $typeRefs['id'] );
                }

                $itemId   = ( ! is_array($typeRefs['bill_item_id']) && array_key_exists($typeRefs['bill_item_id'], $this->itemIds) ) ? $this->itemIds[ $typeRefs['bill_item_id'] ] : null;
                $columnId = ( ! is_array($typeRefs['bill_column_setting_id']) && array_key_exists($typeRefs['bill_column_setting_id'], $this->columnSettingIds) ) ? $this->columnSettingIds[ $typeRefs['bill_column_setting_id'] ] : null;

                $stmt->addRecord(array(
                    $itemId, $columnId,
                    ( $typeRefs['include'] ) ? $typeRefs['include'] : 0,
                    ( $typeRefs['quantity_per_unit_difference'] ) ? $typeRefs['quantity_per_unit_difference'] : 0,
                    ( $typeRefs['total_quantity'] ) ? $typeRefs['total_quantity'] : 0,
                    ( $typeRefs['grand_total'] ) ? $typeRefs['grand_total'] : 0,
                    ( $typeRefs['grand_total_after_markup'] ) ? $typeRefs['grand_total_after_markup'] : 0,
                    'NOW()', 'NOW()', $this->userId, $this->userId
                ), $originalId);
            }
        }

        $stmt->save();

        return $stmt->returningIds;
    }

    public function processTypeFC(Array $typeFcData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName(), array(
            'linked', 'has_build_up', 'relation_id', 'column_name', 'value', 'final_value',
            'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        $originalRelationIdToNodeId = array();

        $originalNodePointer = new SplFixedArray(0);

        foreach($typeFcData as $i => $typeFcs)
        {
            if( ! array_key_exists(0, $typeFcs) )
            {
                if( array_key_exists('id', $typeFcs) && $typeFcs['column_name'] )
                {
                    if( ! array_key_exists($typeFcs['relation_id'] . "-" . $typeFcs['column_name'], $originalRelationIdToNodeId) )
                    {
                        $originalRelationIdToNodeId[ $typeFcs['relation_id'] . "-" . $typeFcs['column_name'] ] = $typeFcs['id'];
                    }
                }

                $tmp        = $typeFcs;
                $typeFcs    = array();
                $typeFcs[0] = $tmp;

                $typeFcData[ $i ] = $typeFcs;
            }
            else
            {
                foreach($typeFcs as $fc)
                {
                    if( array_key_exists('id', $fc) && $fc['column_name'] )
                    {
                        if( ! array_key_exists($fc['relation_id'] . "-" . $fc['column_name'], $originalRelationIdToNodeId) )
                        {
                            $originalRelationIdToNodeId[ $fc['relation_id'] . "-" . $fc['column_name'] ] = $fc['id'];
                        }
                    }
                }

            }
        }

        foreach($typeFcData as $typeFcs)
        {
            foreach($typeFcs as $fc)
            {
                if( array_key_exists('id', $fc) )
                {
                    $originalId = $fc['id'];
                    unset( $fc['id'] );

                    if( $matchesFormula = $this->hasReferencesInFormula($fc['value']) )
                    {
                        $fc['value'] = $this->remapRowLinking($matchesFormula, $originalId, $fc['value'], $fc['column_name'], $this->typeRefIds, $originalRelationIdToNodeId, $originalNodePointer);
                    }

                    if( ! is_array($fc['relation_id']) && array_key_exists($fc['relation_id'], $this->typeRefIds) )
                    {
                        $stmt->addRecord(array(
                            ( $fc['linked'] ) ? $fc['linked'] : 0,
                            ( $fc['has_build_up'] ) ? $fc['has_build_up'] : 0,
                            $this->typeRefIds[ $fc['relation_id'] ],
                            ( $fc['column_name'] ) ? $fc['column_name'] : null,
                            ( $fc['value'] ) ? $fc['value'] : 0,
                            ( $fc['final_value'] ) ? $fc['final_value'] : 0,
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }
                }

                unset( $fc );
            }

            unset( $typeFcs );
        }

        unset( $originalRelationIdToNodeId );

        $stmt->save();

        $returningIds = $stmt->returningIds;

        if( $originalNodePointer->count() > 0 )
        {
            $this->insertEdgesData($stmt, BillItemTypeReferenceEdgeTable::getInstance(), $originalNodePointer, $returningIds);
        }

        return $returningIds;
    }

    public function processElementFC(Array $elementFcData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillElementFormulatedColumnTable::getInstance()->getTableName(), array(
            'relation_id', 'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        $originalRelationIdToNodeId = array();

        $originalNodePointer = new SplFixedArray(0);

        foreach($elementFcData as $i => $elementFcs)
        {
            if( ! array_key_exists(0, $elementFcs) )
            {
                if( array_key_exists('id', $elementFcs) && $elementFcs['column_name'] )
                {
                    if( ! array_key_exists($elementFcs['relation_id'] . "-" . $elementFcs['column_name'], $originalRelationIdToNodeId) )
                    {
                        $originalRelationIdToNodeId[ $elementFcs['relation_id'] . "-" . $elementFcs['column_name'] ] = $elementFcs['id'];
                    }
                }

                $tmp           = $elementFcs;
                $elementFcs    = array();
                $elementFcs[0] = $tmp;

                $elementFcData[ $i ] = $elementFcs;
            }
            else
            {
                foreach($elementFcs as $fc)
                {
                    if( array_key_exists('id', $fc) && $fc['column_name'] )
                    {
                        if( ! array_key_exists($fc['relation_id'] . "-" . $fc['column_name'], $originalRelationIdToNodeId) )
                        {
                            $originalRelationIdToNodeId[ $fc['relation_id'] . "-" . $fc['column_name'] ] = $fc['id'];
                        }
                    }
                }

            }
        }

        foreach($elementFcData as $elementFcs)
        {
            foreach($elementFcs as $fc)
            {
                if( array_key_exists('id', $fc) )
                {
                    $originalId = $fc['id'];

                    unset( $fc['id'] );

                    if( ! array_key_exists($fc['relation_id'], $originalRelationIdToNodeId) )
                    {
                        $originalRelationIdToNodeId[ $fc['relation_id'] ] = $originalId;
                    }

                    if( $matchesFormula = $this->hasReferencesInFormula($fc['value']) )
                    {
                        $fc['value'] = $this->remapRowLinking($matchesFormula, $originalId, $fc['value'], $fc['column_name'], $this->elementIds, $originalRelationIdToNodeId, $originalNodePointer);
                    }

                    if( ! is_array($fc['relation_id']) && array_key_exists($fc['relation_id'], $this->elementIds) )
                    {
                        $stmt->addRecord(array(
                            $this->elementIds[ $fc['relation_id'] ],
                            ( $fc['column_name'] ) ? $fc['column_name'] : null,
                            ( $fc['value'] ) ? $fc['value'] : 0,
                            ( $fc['final_value'] ) ? $fc['final_value'] : 0,
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }
                }

                unset( $fc );
            }

            unset( $elementFcs );
        }

        unset( $originalRelationIdToNodeId );

        $stmt->save();

        $returningIds = $stmt->returningIds;

        if( $originalNodePointer->count() > 0 )
        {
            $this->insertEdgesData($stmt, BillElementEdgeTable::getInstance(), $originalNodePointer, $returningIds);
        }

        return $returningIds;
    }

    public function processItemFC(Array $itemFcData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillItemFormulatedColumnTable::getInstance()->getTableName(), array(
            'linked', 'has_build_up', 'relation_id', 'schedule_of_rate_item_formulated_column_id',
            'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        $originalRelationIdToNodeId = array();

        $originalNodePointer = new SplFixedArray(0);

        foreach($itemFcData as $i => $itemFcs)
        {
            if( ! array_key_exists(0, $itemFcs) )
            {
                if( array_key_exists('id', $itemFcs) && $itemFcs['column_name'] )
                {
                    if( ! array_key_exists($itemFcs['relation_id'] . "-" . $itemFcs['column_name'], $originalRelationIdToNodeId) )
                    {
                        $originalRelationIdToNodeId[ $itemFcs['relation_id'] . "-" . $itemFcs['column_name'] ] = $itemFcs['id'];
                    }
                }

                $tmp        = $itemFcs;
                $itemFcs    = array();
                $itemFcs[0] = $tmp;

                $itemFcData[ $i ] = $itemFcs;
            }
            else
            {
                foreach($itemFcs as $fc)
                {
                    if( array_key_exists('id', $fc) && $fc['column_name'] )
                    {
                        if( ! array_key_exists($fc['relation_id'] . "-" . $fc['column_name'], $originalRelationIdToNodeId) )
                        {
                            $originalRelationIdToNodeId[ $fc['relation_id'] . "-" . $fc['column_name'] ] = $fc['id'];
                        }
                    }
                }

            }
        }

        $rateLogData = array();

        foreach($itemFcData as $itemFcs)
        {
            foreach($itemFcs as $fc)
            {
                if( array_key_exists('id', $fc) )
                {
                    $originalId = $fc['id'];

                    unset( $fc['id'] );

                    if( $matchesFormula = $this->hasReferencesInFormula($fc['value']) )
                    {
                        $fc['value'] = $this->remapRowLinking($matchesFormula, $originalId, $fc['value'], $fc['column_name'], $this->itemIds, $originalRelationIdToNodeId, $originalNodePointer);
                    }

                    if( ! is_array($fc['relation_id']) && array_key_exists($fc['relation_id'], $this->itemIds) )
                    {
                        $stmt->addRecord(array(
                            ( $fc['linked'] ) ? $fc['linked'] : 0,
                            ( $fc['has_build_up'] ) ? $fc['has_build_up'] : 0,
                            $this->itemIds[ $fc['relation_id'] ],
                            ( $fc['schedule_of_rate_item_formulated_column_id'] ) ? $fc['schedule_of_rate_item_formulated_column_id'] : null,
                            ( $fc['column_name'] ) ? $fc['column_name'] : null,
                            ( $fc['value'] ) ? $fc['value'] : 0,
                            ( $fc['final_value'] ) ? $fc['final_value'] : 0,
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);

                        if( ! empty( $fc['column_name'] ) && $fc['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                        {
                            $rateLogData[ (int)$this->itemIds[ $fc['relation_id'] ] ] = ( $fc['final_value'] ) ? (double)$fc['final_value'] : 0;
                        }
                    }
                }

                unset( $fc );
            }

            unset( $itemFcs );
        }

        unset( $originalRelationIdToNodeId );

        $stmt->save();

        $returningIds = $stmt->returningIds;

        if( $originalNodePointer->count() > 0 )
        {
            $this->insertEdgesData($stmt, BillItemEdgeTable::getInstance(), $originalNodePointer, $returningIds);
        }

        BillItemRateLogTable::insertBatchLogByBillId($this->billId, $rateLogData);

        return $returningIds;
    }

    public function processBQty(Array $bQtyData)
    {
        $returnedIds = array();

        $stmt = new sfImportExcelStatementGenerator();

        foreach($bQtyData as $i => $bQtys)
        {
            if( ! array_key_exists(0, $bQtys) )
            {
                $tmp      = $bQtys;
                $bQtys    = array();
                $bQtys[0] = $tmp;

                $bQtyData[ $i ] = $bQtys;
            }
        }

        $chunkedItems = array_chunk($bQtyData['ITEM'], 300);

        unset( $bQtyData );

        foreach($chunkedItems as $chunkedItem)
        {
            $stmt->createInsert(BillBuildUpQuantityItemTable::getInstance()->getTableName(), array(
                'bill_item_id', 'bill_column_setting_id', 'description', 'total', 'sign', 'priority',
                'type', 'created_at', 'updated_at', 'created_by', 'updated_by'
            ));

            foreach($chunkedItem as $qty)
            {
                if( array_key_exists('id', $qty) )
                {
                    $originalId = $qty['id'];
                    unset( $qty['id'] );

                    $itemId   = ( ! is_array($qty['bill_item_id']) && array_key_exists($qty['bill_item_id'], $this->itemIds) ) ? $this->itemIds[ $qty['bill_item_id'] ] : null;
                    $columnId = ( ! is_array($qty['bill_column_setting_id']) && array_key_exists($qty['bill_column_setting_id'], $this->columnSettingIds) ) ? $this->columnSettingIds[ $qty['bill_column_setting_id'] ] : null;

                    if( $itemId && $columnId )
                    {
                        $stmt->addRecord(array(
                            $itemId, $columnId, ( $qty['description'] ) ? $qty['description'] : null,
                            ( $qty['total'] ) ? $qty['total'] : 0,
                            ( $qty['sign'] ) ? $qty['sign'] : 0,
                            ( $qty['priority'] ) ? $qty['priority'] : 0,
                            $qty['type'],
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }
                }

                unset( $qty );
            }

            $stmt->save();
            $stmt->records     = new SplFixedArray(0);
            $stmt->originalIds = new SplFixedArray(0);

            $returnedIds += $stmt->returningIds;

            $stmt->resetRecord();
        }

        unset( $chunkedItems );

        return $returnedIds;
    }

    public function processBQtyFC(Array $bQtyFcData)
    {
        $returnedIds = array();

        $stmt = new sfImportExcelStatementGenerator();

        $originalRelationIdToNodeId = array();

        $originalNodePointer = new SplFixedArray(0);

        foreach($bQtyFcData as $i => $bQtyFcs)
        {
            if( ! array_key_exists(0, $bQtyFcs) )
            {
                if( array_key_exists('id', $bQtyFcs) && $bQtyFcs['column_name'] )
                {
                    if( ! array_key_exists($bQtyFcs['relation_id'] . "-" . $bQtyFcs['column_name'], $originalRelationIdToNodeId) )
                    {
                        $originalRelationIdToNodeId[ $bQtyFcs['relation_id'] . "-" . $bQtyFcs['column_name'] ] = $bQtyFcs['id'];
                    }
                }

                $tmp        = $bQtyFcs;
                $bQtyFcs    = array();
                $bQtyFcs[0] = $tmp;

                $bQtyFcData[ $i ] = $bQtyFcs;
            }
            else
            {
                foreach($bQtyFcs as $fc)
                {
                    if( array_key_exists('id', $fc) && $fc['column_name'] )
                    {
                        if( ! array_key_exists($fc['relation_id'] . "-" . $fc['column_name'], $originalRelationIdToNodeId) )
                        {
                            $originalRelationIdToNodeId[ $fc['relation_id'] . "-" . $fc['column_name'] ] = $fc['id'];
                        }
                    }
                }

            }
        }

        $chunkedItems = array_chunk($bQtyFcData['ITEM'], 300);

        unset( $bQtyFcData );

        foreach($chunkedItems as $chunkedItem)
        {
            $stmt->createInsert(BillBuildUpQuantityFormulatedColumnTable::getInstance()->getTableName(), array(
                'relation_id', 'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by'
            ));

            foreach($chunkedItem as $fc)
            {
                if( array_key_exists('id', $fc) )
                {
                    $originalId = $fc['id'];
                    unset( $fc['id'] );

                    $extract = explode('-', $fc['column_name']);

                    if( count($extract) == 2 )
                    {
                        $columnName = implode('-', array(
                            $this->dimensionIds[ $extract[0] ],
                            $extract[1]
                        ));
                    }
                    else
                    {
                        $columnName = $fc['column_name'];
                    }

                    if( $matchesFormula = $this->hasReferencesInFormula($fc['value']) )
                    {
                        $fc['value'] = $this->remapRowLinking($matchesFormula, $originalId, $fc['value'], $columnName, $this->bQtyIds, $originalRelationIdToNodeId, $originalNodePointer);
                    }

                    if( ! is_array($fc['relation_id']) && array_key_exists($fc['relation_id'], $this->bQtyIds) )
                    {
                        $stmt->addRecord(array(
                            $this->bQtyIds[ $fc['relation_id'] ],
                            ( $columnName ) ? $columnName : null,
                            ( $fc['value'] ) ? $fc['value'] : 0,
                            ( $fc['final_value'] ) ? $fc['final_value'] : 0,
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }

                }

                unset( $fc );
            }

            $stmt->save();
            $stmt->records     = new SplFixedArray(0);
            $stmt->originalIds = new SplFixedArray(0);

            $returnedIds += $stmt->returningIds;

            $stmt->resetRecord();
        }

        unset( $chunkedItems, $originalRelationIdToNodeId );

        if( $originalNodePointer->count() > 0 )
        {
            $this->insertEdgesData($stmt, BillBuildUpQuantityEdgeTable::getInstance(), $originalNodePointer, $returnedIds);
        }

        return $returnedIds;
    }

    public function processBQtySummary(Array $bQtySummaryData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillBuildUpQuantitySummaryTable::getInstance()->getTableName(), array(
            'bill_item_id', 'bill_column_setting_id', 'linked_total_quantity', 'total_quantity', 'final_quantity',
            'apply_conversion_factor', 'conversion_factor_operator', 'conversion_factor_amount', 'rounding_type', 'type',
            'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        foreach($bQtySummaryData as $i => $summaries)
        {
            if( ! array_key_exists(0, $summaries) )
            {
                $tmp          = $summaries;
                $summaries    = array();
                $summaries[0] = $tmp;

                $bQtySummaryData[ $i ] = $summaries;
            }
        }

        foreach($bQtySummaryData as $summaries)
        {
            foreach($summaries as $summary)
            {
                if( array_key_exists('id', $summary) )
                {
                    $originalId = $summary['id'];
                    unset( $summary['id'] );

                    $itemId   = ( ! is_array($summary['bill_item_id']) && array_key_exists($summary['bill_item_id'], $this->itemIds) ) ? $this->itemIds[ $summary['bill_item_id'] ] : null;
                    $columnId = ( ! is_array($summary['bill_column_setting_id']) && array_key_exists($summary['bill_column_setting_id'], $this->columnSettingIds) ) ? $this->columnSettingIds[ $summary['bill_column_setting_id'] ] : null;

                    if( $itemId && $columnId )
                    {
                        $stmt->addRecord(array(
                            $itemId, $columnId, ( $summary['linked_total_quantity'] ) ? $summary['linked_total_quantity'] : 0,
                            ( $summary['total_quantity'] ) ? $summary['total_quantity'] : null,
                            ( $summary['final_quantity'] ) ? $summary['final_quantity'] : null,
                            ( $summary['apply_conversion_factor'] ) ? $summary['apply_conversion_factor'] : null,
                            ( $summary['conversion_factor_operator'] ) ? $summary['conversion_factor_operator'] : null,
                            ( $summary['conversion_factor_amount'] ) ? $summary['conversion_factor_amount'] : 0,
                            ( $summary['rounding_type'] ) ? $summary['rounding_type'] : null,
                            ( $summary['type'] ) ? $summary['type'] : null,
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }
                }

                unset( $summary );
            }
        }

        unset( $bQtySummaryData );

        $stmt->save();

        return $stmt->returningIds;
    }

    public function processBRResources(Array $brResourcesData)
    {
        $resourceIds        = new SplFixedArray(0);
        $resourceLibraryIds = array();

        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillBuildUpRateResourceTable::getInstance()->getTableName(), array(
            'name', 'bill_item_id', 'resource_library_id', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        if( ! array_key_exists(0, $brResourcesData['ITEM']) )
        {
            $tmp                        = $brResourcesData['ITEM'];
            $brResourcesData['ITEM']    = array();
            $brResourcesData['ITEM'][0] = $tmp;
        }

        foreach($brResourcesData['ITEM'] as $resource)
        {
            if( ! in_array($resource['resource_library_id'], $resourceIds->toArray()) )
            {
                $resourceIds->setSize($resourceIds->getSize() + 1);

                $resourceIds[ $resourceIds->getSize() - 1 ] = $resource['resource_library_id'];
            }
        }

        $existingResourceLibraries = $this->getResourceByLibraryIds($resourceIds);

        unset( $resourceIds );

        foreach($brResourcesData['ITEM'] as $resource)
        {
            $libraryExist = false;

            if( array_key_exists('id', $resource) )
            {
                $originalId = $resource['id'];

                unset( $resource['id'] );

                $billItemId = $this->itemIds[ $resource['bill_item_id'] ];

                $resourceLibraryId = null;

                if( $existingResourceLibraries and array_key_exists($resource['resource_library_id'], $existingResourceLibraries) )
                {
                    $resourceLibraryIds[ $resource['resource_library_id'] ] = $existingResourceLibraries[ $resource['resource_library_id'] ];
                    $libraryExist                                           = true;
                    $resourceLibraryId                                      = $existingResourceLibraries[ $resource['resource_library_id'] ];
                }

                if( $libraryExist and $resourceLibraryId )
                {
                    $stmt->addRecord(array(
                        $resource['name'], $billItemId, $resourceLibraryId,
                        'NOW()', 'NOW()', $this->userId, $this->userId
                    ), $originalId);
                }
            }

            unset( $resource );
        }

        $this->resourceLibraryIds = $resourceLibraryIds;

        $stmt->save();

        return $stmt->returningIds;
    }

    public function processBRTrades(Array $brTradesData)
    {
        $tradeLibraryIds = array();

        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillBuildUpRateResourceTradeTable::getInstance()->getTableName(), array(
            'description', 'bill_item_id', 'build_up_rate_resource_id', 'resource_trade_library_id', 'priority',
            'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        if( ! array_key_exists(0, $brTradesData['ITEM']) )
        {
            $tmp                     = $brTradesData['ITEM'];
            $brTradesData['ITEM']    = array();
            $brTradesData['ITEM'][0] = $tmp;
        }

        $resourceTradeLibraryIds = new SplFixedArray(0);

        foreach($brTradesData['ITEM'] as $trade)
        {
            if( array_key_exists($trade['build_up_rate_resource_id'], $this->brResourceIds) )
            {
                if( ! in_array($trade['resource_trade_library_id'], $resourceTradeLibraryIds->toArray()) )
                {
                    $resourceTradeLibraryIds->setSize($resourceTradeLibraryIds->getSize() + 1);

                    $resourceTradeLibraryIds[ $resourceTradeLibraryIds->getSize() - 1 ] = $trade['resource_trade_library_id'];
                }
            }
        }

        $existingResourceTradeLibraries = $this->getResourceTradeByTradeIds($resourceTradeLibraryIds);

        unset( $resourceTradeLibraryIds );

        foreach($brTradesData['ITEM'] as $trade)
        {
            if( array_key_exists('id', $trade) )
            {
                $originalId = $trade['id'];

                unset( $trade['id'] );

                $billItemId = $this->itemIds[ $trade['bill_item_id'] ];

                if( array_key_exists($trade['build_up_rate_resource_id'], $this->brResourceIds) )
                {
                    $brResourceId = $this->brResourceIds[ $trade['build_up_rate_resource_id'] ];

                    $resourceTradeLibraryId = null;

                    if( $existingResourceTradeLibraries and array_key_exists($trade['resource_trade_library_id'], $existingResourceTradeLibraries) )
                    {
                        $resourceTradeLibraryId                                 = $existingResourceTradeLibraries[ $trade['resource_trade_library_id'] ];
                        $tradeLibraryIds[ $trade['resource_trade_library_id'] ] = $resourceTradeLibraryId;
                    }

                    if( $billItemId )
                    {
                        $stmt->addRecord(array(
                            $trade['description'], $billItemId, $brResourceId, $resourceTradeLibraryId,
                            $trade['priority'], 'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }
                }
            }

            unset( $trade );
        }

        unset( $brTradesData );

        $this->tradeLibraryIds = $tradeLibraryIds;

        $stmt->save();

        return $stmt->returningIds;
    }

    public function processBRItems(Array $brItemsData)
    {
        $returnedIds = array();

        $stmt = new sfImportExcelStatementGenerator();

        if( ! array_key_exists(0, $brItemsData['ITEM']) )
        {
            $tmp                    = $brItemsData['ITEM'];
            $brItemsData['ITEM']    = array();
            $brItemsData['ITEM'][0] = $tmp;
        }

        $resourceItemLibraryIds = new SplFixedArray(0);

        foreach($brItemsData['ITEM'] as $item)
        {
            $billItemId = $this->itemIds[ $item['bill_item_id'] ];

            $brResourceId = ( ! is_array($item['build_up_rate_resource_id']) && $item['build_up_rate_resource_id'] && array_key_exists($item['build_up_rate_resource_id'], $this->brResourceIds) ) ? $this->brResourceIds[ $item['build_up_rate_resource_id'] ] : false;

            $resourceItemLibraryId = null;

            if( $billItemId && $brResourceId )
            {
                if( ! is_array($item['resource_item_library_id']) )
                {
                    if( ! in_array($item['resource_item_library_id'], $resourceItemLibraryIds->toArray()) )
                    {
                        $resourceItemLibraryIds->setSize($resourceItemLibraryIds->getSize() + 1);

                        $resourceItemLibraryIds[ $resourceItemLibraryIds->getSize() - 1 ] = $item['resource_item_library_id'];
                    }
                }
            }
        }

        $existingResourceItemLibraries = $this->getResourceItemLibraryIds($resourceItemLibraryIds);

        unset( $resourceItemLibraryIds );

        $chunkedItems = array_chunk($brItemsData['ITEM'], 500);

        unset( $brItemsData );

        foreach($chunkedItems as $chunkedItem)
        {
            $stmt->createInsert(BillBuildUpRateItemTable::getInstance()->getTableName(), array(
                'bill_item_id', 'build_up_rate_resource_id', 'build_up_rate_resource_trade_id', 'resource_item_library_id',
                'description', 'total', 'line_total', 'uom_id', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by'
            ));

            foreach($chunkedItem as $brItem)
            {
                if( array_key_exists('id', $brItem) )
                {
                    $originalId = $brItem['id'];

                    unset( $brItem['id'] );

                    $billItemId = $this->itemIds[ $brItem['bill_item_id'] ];

                    $brResourceId = ( ! is_array($brItem['build_up_rate_resource_id']) && $brItem['build_up_rate_resource_id'] && array_key_exists($brItem['build_up_rate_resource_id'], $this->brResourceIds) ) ? $this->brResourceIds[ $brItem['build_up_rate_resource_id'] ] : false;

                    $resourceItemLibraryId = null;

                    if( $billItemId && $brResourceId )
                    {
                        $brTradeId = ( ! is_array($brItem['build_up_rate_resource_trade_id']) && $brItem['build_up_rate_resource_trade_id'] ) ? $this->brTradeIds[ $brItem['build_up_rate_resource_trade_id'] ] : null;

                        if( ! is_array($brItem['resource_item_library_id']) )
                        {
                            if( array_key_exists($brItem['resource_item_library_id'], $existingResourceItemLibraries) )
                            {
                                $resourceItemLibraryId = $existingResourceItemLibraries[ $brItem['resource_item_library_id'] ];
                            }
                        }

                        $unitId = ( ! is_array($brItem['uom_id']) && array_key_exists($brItem['uom_id'], $this->unitIds) ) ? $this->unitIds[ $brItem['uom_id'] ] : null;

                        $stmt->addRecord(array(
                            $billItemId, $brResourceId, $brTradeId, $resourceItemLibraryId,
                            ( ! is_array($brItem['description']) ) ? $brItem['description'] : null,
                            $brItem['total'], $brItem['line_total'], $unitId, $brItem['priority'], 'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }

                    unset( $brItem, $resourceItemLibrary );
                }
            }

            $stmt->save();
            $stmt->records     = new SplFixedArray(0);
            $stmt->originalIds = new SplFixedArray(0);

            $returnedIds += $stmt->returningIds;

            $stmt->resetRecord();
        }

        unset( $chunkedItems );

        return $returnedIds;
    }

    public function processBRItemFC(Array $brItemFcData)
    {
        $originalRelationIdToNodeId = array();

        $originalNodePointer = new SplFixedArray(0);

        foreach($brItemFcData as $i => $brItemFcs)
        {
            if( ! array_key_exists(0, $brItemFcs) )
            {
                if( array_key_exists('id', $brItemFcs) && $brItemFcs['column_name'] )
                {
                    if( ! array_key_exists($brItemFcs['relation_id'] . "-" . $brItemFcs['column_name'], $originalRelationIdToNodeId) )
                    {
                        $originalRelationIdToNodeId[ $brItemFcs['relation_id'] . "-" . $brItemFcs['column_name'] ] = $brItemFcs['id'];
                    }
                }

                $tmp          = $brItemFcs;
                $brItemFcs    = array();
                $brItemFcs[0] = $tmp;

                $brItemFcData[ $i ] = $brItemFcs;
            }
            else
            {
                foreach($brItemFcs as $fc)
                {
                    if( array_key_exists('id', $fc) && $fc['column_name'] )
                    {
                        if( ! array_key_exists($fc['relation_id'] . "-" . $fc['column_name'], $originalRelationIdToNodeId) )
                        {
                            $originalRelationIdToNodeId[ $fc['relation_id'] . "-" . $fc['column_name'] ] = $fc['id'];
                        }
                    }
                }

            }
        }

        $chunkedItems = array_chunk($brItemFcData['ITEM'], 200);

        unset( $brItemFcData );

        $originalId = null;

        $returnedIds = array();

        $stmt = new sfImportExcelStatementGenerator();

        foreach($chunkedItems as $chunkedItem)
        {
            $stmt->createInsert(BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName(), array(
                'linked', 'relation_id', 'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by'
            ));

            foreach($chunkedItem as $fc)
            {
                if( array_key_exists('id', $fc) )
                {
                    $originalId = $fc['id'];

                    unset( $fc['id'] );

                    if( $matchesFormula = $this->hasReferencesInFormula($fc['value']) )
                    {
                        $fc['value'] = $this->remapRowLinking($matchesFormula, $originalId, $fc['value'], $fc['column_name'], $this->brItemIds, $originalRelationIdToNodeId, $originalNodePointer);
                    }

                    if( ! is_array($fc['relation_id']) && array_key_exists($fc['relation_id'], $this->brItemIds) )
                    {
                        $stmt->addRecord(array(
                            ( $fc['linked'] ) ? $fc['linked'] : 0,
                            $this->brItemIds[ $fc['relation_id'] ],
                            ( $fc['column_name'] ) ? $fc['column_name'] : null,
                            ( $fc['value'] ) ? $fc['value'] : 0,
                            ( $fc['final_value'] ) ? $fc['final_value'] : 0,
                            'NOW()', 'NOW()', $this->userId, $this->userId
                        ), $originalId);
                    }

                    unset( $fc );
                }
            }

            $stmt->save();
            $stmt->records     = new SplFixedArray(0);
            $stmt->originalIds = new SplFixedArray(0);

            $returnedIds += $stmt->returningIds;

            $stmt->resetRecord();
        }

        unset( $chunkedItems, $originalRelationIdToNodeId );

        if( $originalNodePointer->count() > 0 )
        {
            $this->insertEdgesData($stmt, BillBuildUpRateEdgeTable::getInstance(), $originalNodePointer, $returnedIds);
        }

        return $returnedIds;
    }

    public function processBRSummary(Array $brSummaryData)
    {
        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(BillBuildUpRateSummaryTable::getInstance()->getTableName(), array(
            'bill_item_id', 'total_cost', 'markup', 'final_cost', 'apply_conversion_factor',
            'conversion_factor_amount', 'conversion_factor_operator', 'rounding_type', 'conversion_factor_uom_id',
            'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        if( ! array_key_exists(0, $brSummaryData['ITEM']) )
        {
            $tmp                      = $brSummaryData['ITEM'];
            $brSummaryData['ITEM']    = array();
            $brSummaryData['ITEM'][0] = $tmp;
        }

        foreach($brSummaryData['ITEM'] as $summary)
        {
            if( array_key_exists('id', $summary) )
            {
                $originalId = $summary['id'];

                unset( $summary['id'] );

                $itemId = ( ! is_array($summary['bill_item_id']) && array_key_exists($summary['bill_item_id'], $this->itemIds) ) ? $this->itemIds[ $summary['bill_item_id'] ] : null;

                $unitId = ( ! is_array($summary['conversion_factor_uom_id']) && array_key_exists($summary['conversion_factor_uom_id'], $this->unitIds) ) ? $this->unitIds[ $summary['conversion_factor_uom_id'] ] : null;

                $stmt->addRecord(array(
                    $itemId,
                    ( $summary['total_cost'] ) ? $summary['total_cost'] : 0,
                    ( $summary['markup'] ) ? $summary['markup'] : 0,
                    ( $summary['final_cost'] ) ? $summary['final_cost'] : 0,
                    ( $summary['apply_conversion_factor'] ) ? $summary['apply_conversion_factor'] : 0,
                    ( $summary['conversion_factor_amount'] ) ? $summary['conversion_factor_amount'] : null,
                    ( $summary['conversion_factor_operator'] ) ? $summary['conversion_factor_operator'] : null,
                    ( $summary['rounding_type'] ) ? $summary['rounding_type'] : null,
                    $unitId,
                    'NOW()', 'NOW()', $this->userId, $this->userId
                ), $originalId);
            }

            unset( $summary );
        }

        unset( $brSummaryData );

        $stmt->save();

        return $stmt->returningIds;
    }

    public function getResourceByLibraryIds(SplFixedArray $libraryIds)
    {
        if( $libraryIds->count() == 0 )
            return array();

        $stmt = $this->pdo->prepare("SELECT DISTINCT id, id FROM " . ResourceTable::getInstance()->getTableName() . " r
            WHERE r.id IN (" . implode(",", $libraryIds->toArray()) . ")");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getResourceTradeByTradeIds(SplFixedArray $tradeIds)
    {
        if( $tradeIds->count() == 0 )
            return array();

        $stmt = $this->pdo->prepare("SELECT DISTINCT id, id FROM " . ResourceTradeTable::getInstance()->getTableName() . " t
            WHERE t.id IN (" . implode(",", $tradeIds->toArray()) . ")");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getResourceItemLibraryIds(SplFixedArray $itemLibraryIds)
    {
        if( $itemLibraryIds->count() == 0 )
            return array();

        $stmt = $this->pdo->prepare("SELECT id, id FROM " . ResourceItemTable::getInstance()->getTableName() . " i
            WHERE i.id IN (" . implode(",", $itemLibraryIds->toArray()) . ")");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getUnitsBySymbols(SplFixedArray $symbols)
    {
        if( $symbols->count() == 0 )
            return null;

        $in  = str_repeat('?,', $symbols->count() - 1) . '?';

        $array = array_map('strtolower', $symbols->toArray());

        $stmt = $this->pdo->prepare("SELECT LOWER(symbol), id FROM " . UnitOfMeasurementTable::getInstance()->getTableName() . " u
            WHERE u.deleted_at IS NULL AND u.display IS TRUE AND LOWER(u.symbol) IN (" . $in . ") ORDER BY u.id DESC");

        $stmt->execute($array);

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getDimensionsByNames(SplFixedArray $names)
    {
        if( $names->count() == 0 )
            return null;

        $in  = str_repeat('?,', $names->count() - 1) . '?';

        $array = array_map('strtolower', $names->toArray());

        $stmt = $this->pdo->prepare("SELECT LOWER(name), id FROM " . DimensionTable::getInstance()->getTableName() . " d
            WHERE d.deleted_at IS NULL AND LOWER(d.name) IN (".$in.") ORDER BY d.id DESC");

        $stmt->execute($array);

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function insertEdgesData(sfImportExcelStatementGenerator $stmt, Doctrine_Table $tableClass, SplFixedArray $originalNodePointer, Array $idList)
    {
        $stmt->createInsert($tableClass->getTableName(), array(
            'node_from', 'node_to', 'column_name', 'created_at', 'updated_at', 'created_by', 'updated_by'
        ));

        foreach($originalNodePointer as $pointer)
        {
            if( array_key_exists($pointer['from'], $idList) && array_key_exists($pointer['to'], $idList) )
            {
                $stmt->addRecord(array(
                    $idList[ $pointer['from'] ], $idList[ $pointer['to'] ],
                    $pointer['column_name'], 'NOW()', 'NOW()', $this->userId, $this->userId
                ));
            }
        }

        $stmt->save();
    }

    private function remapRowLinking(Array $matchesFormula, $originalId, $value, $columnName, Array $relationIdList, Array $originalRelationIdToNodeId, SplFixedArray $originalNodePointer)
    {
        foreach($matchesFormula as $matchesValue)
        {
            $originalRelationId = str_ireplace('r', '', $matchesValue);

            $newValue = null;

            if( array_key_exists($originalRelationId, $relationIdList) )
            {
                $newValue = str_replace($originalRelationId, $relationIdList[ $originalRelationId ], $matchesValue);
            }

            $value = str_replace($matchesValue, $newValue, $value);

            if( $columnName && array_key_exists($originalRelationId . "-" . $columnName, $originalRelationIdToNodeId) )
            {
                $originalNodePointer->setSize($originalNodePointer->getSize() + 1);

                $originalNodePointer[ $originalNodePointer->getSize() - 1 ] = array(
                    'from'        => $originalId,
                    'to'          => $originalRelationIdToNodeId[ $originalRelationId . "-" . $columnName ],
                    'column_name' => $columnName
                );
            }
        }

        return $value;
    }

    public function generateDataStructure($data, $defaultData = 'null')
    {
        $structure = array(
            'structure' => array(),
            'value'     => array()
        );

        switch($defaultData)
        {
            case "null" :
                $defaultData = null;
                break;
            case "false":
                $defaultData = 0;
                break;
            case "true" :
                $defaultData = 1;
                break;
            default:
                $defaultData = null;
                break;
        }

        foreach($data as $k => $value)
        {
            array_push($structure['structure'], $k);
            array_push($structure['value'], ( ! is_array($value) ) ? $value : $defaultData);
        }

        return $structure;
    }

    public function getBillByTitle($title, $rootId)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM " . ProjectStructureTable::getInstance()->getTableName() . " c
            WHERE c.deleted_at IS NULL AND LOWER(c.title) LIKE :title AND c.root_id = :rootId");

        $stmt->execute(array(
            ':title'  => strtolower($title),
            ':rootId' => $rootId
        ));

        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProjectStructureLeftTree($leftTreeNo, $rootId)
    {
        $this->pdo->prepare("UPDATE " . ProjectStructureTable::getInstance()->getTableName() . " c
            SET lft = (lft) + 2 WHERE lft >= " . $leftTreeNo . " AND root_id = " . $rootId)
            ->execute();
    }

    public function updateProjectStructureRightTree($rightTreeNo, $rootId)
    {
        $this->pdo->prepare("UPDATE " . ProjectStructureTable::getInstance()->getTableName() . " c
            SET rgt = (rgt) + 2 WHERE rgt >= " . $rightTreeNo . " AND root_id = " . $rootId)
            ->execute();
    }

    private function hasReferencesInFormula($formula)
    {
        if( ! is_string($formula) )
            return false;

        $pattern = '/r[\d{1,}]+/i';

        $match = preg_match_all($pattern, $formula, $matches, PREG_PATTERN_ORDER);

        return $match ? $matches[0] : false;
    }
}
