<?php

class sfSubPackageExcelExporter extends sfBuildspaceRFQExcelGenerator {

    public $fileInfo;

    public $subPackage;

    public $bill;

    protected $objPHPExcel;

    protected $activeSheet;

    protected $billDatas             = array();
    public $startRow                 = 3;
    public $colAmtStartRow           = 0;

    // information that will be needed to import
    public $colRowType               = 'A';
    public $colItemType              = 'B';
    public $colLevel                 = 'C';
    public $colItemId                = 'D';

    // table placement
    public $colItem                  = 'F';
    public $colDescription           = 'G';
    public $colUnit                  = 'H';
    public $colRate                  = 'I';

    // to pin point footer's amount row
    public $colTotalRow              = 0;
    public $colUnitRow               = 0;
    public $colAllUnitTotalRow       = 0;

    const SUBPACKAGECOMPANYHIDDENID  = 'A1';
    const BILLHIDDENID               = 'B1';
    const BILLCOLUMNSETTINGSHIDDENID = 'C1';

    public function __construct(SubPackageCompany $subPackageCompany, ProjectStructure $bill)
    {
        $this->subPackageCompany = $subPackageCompany;
        $this->bill              = $bill;
        $this->objPHPExcel       = new sfPhpExcel();
        $this->pdo               = $subPackageCompany->getTable()->getConnection()->getDbh();
        $this->savePath          = sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';
    }

    public function getBillData()
    {
        // get affected bill that is associated with provided sub package
        $this->billDatas          = SubPackageTable::getAffectedBillsBySubPackage($this->subPackageCompany->SubPackage, $this->bill->id);
        $this->billColumnSettings = $this->bill->BillColumnSettings;
        $this->billColumnsUnits   = $this->getColumnSettingsUnits();
    }

    public function generateFile()
    {
        if ( count($this->billDatas) == 0 )
        {
            throw new InvalidArgumentException('Sorry, currently there are no Bill(s) that can be exported.');
        }

        // use to differentiate bills by sheets
        $billIndex = 0;

        foreach ( $this->billDatas as $billId => $bill )
        {
            // get affected element(s), then item(s)
            $elements = SubPackageTable::getAffectedElementBySubPackageAndBillId($this->subPackageCompany->SubPackage, $billId);

            // if no affected element(s), then straight ignore and go to another bill
            if ( count($elements) == 0 )
            {
                break;
            }

            $itemRecords = SubPackageTable::getAffectedItemsBySubPackageAndBillIdAndElementIds($this->subPackageCompany->SubPackage, $billId, array_keys($elements));

            if ( count($itemRecords) == 0 )
            {
                break;
            }

            $billItemIds     = Utilities::arrayValueRecursive('bill_item_id', $itemRecords);
            $this->billItems = self::getAffectedBillItems($billId, array_keys($elements), $billItemIds);

            // after that start to pump those information into phpexcel to be processed
            // for each bill will have their own sheet, will try to link
            self::convertToExcel($billId, $elements, $this->billItems, $billIndex);

            unset($billId, $bill, $elements, $this->billItems);

            $billIndex++;
        }

        // write to Excel File
        return $this->fileInfo = $this->writeExcel();
    }

    public function convertToExcel($billId, array $elements, array $billItems, $billIndex)
    {
        $this->setActiveSheet($billIndex);
        $this->getActiveSheet();
        $this->startBillCounter();
        $this->hideColumn();
        $this->protectSheet();

        $this->setHiddenSubPackageCompanyId();
        $this->setHiddenExportedBillId();
        $this->setHiddenExportedAffectedBillColumnSettings();

        foreach ( $elements as $elementId => $elementInfo )
        {
            $newBillItems = array();
            $itemPages    = array();

            foreach ( $billItems as $key => $billItem )
            {
                if ( $billItem['element_id'] == $elementId )
                {
                    $newBillItems[] = $billItem;
                    unset($billItems[$key]);
                }
            }

            $this->generateBillItemPages($newBillItems, $elementInfo, 1, array(), $itemPages);

            $description = '';
            $char        = '';

            $this->createNewPage();

            $this->newLine();

            $this->colAmtStartRow = $this->currentRow;

            foreach($itemPages as $pageNo => $page)
            {
                foreach($page as $item)
                {
                    $itemType = $item[4];

                    switch($itemType)
                    {
                        case self::ROW_TYPE_BLANK:
                            if($description != '' && $prevItemType != '')
                            {
                                if ($prevItemType == BillItem::TYPE_HEADER)
                                {
                                    $this->newItem();
                                    $this->setItemHead( $description,  $prevItemType, $item[3], true );
                                }

                                $description = '';

                                $this->newLine();
                            }
                        break;

                        case self::ROW_TYPE_ELEMENT:
                            $this->setElement(array('description' => $item[2]));
                        break;

                        case BillItem::TYPE_HEADER:
                            $description.= $item[2];
                            $prevItemType = $item[4];
                        break;

                        default:
                            $description.= $item[2];

                            $char.= $item[9];

                            if($item[0])
                            {
                                $this->newItem();

                                $this->setRowItem( $item[0], $description, $itemType , $item[3]);

                                $this->setUnit( $item[5] );

                                $this->setChar( $item[9] );

                                $this->setEditableRate();

                                $this->processItems($item);

                                $description = '';
                                $char        = '';

                                $this->newLine();
                            }
                        break;
                    }
                }
            }

            // need to close the page based on each element
            $this->createFooter();

            unset($itemPages, $collectionPages);

            unset($newBillItems);
        }

        unset($elements, $billItems);

        $this->wordWrapDescriptionColumn();
    }

    private function wordWrapDescriptionColumn()
    {
        $colDescription = $this->colDescription;

        $this->objPHPExcel->getActiveSheet()->getStyle("{$colDescription}1:{$colDescription}{$this->currentRow}")->getAlignment()->setWrapText(true);
    }

    private function generateBillItemPages(Array $billItems, $elementInfo, $pageCount, $ancestors, &$itemPages, $newPage = false)
    {
        $itemPages[$pageCount] = array();

        $blankRow    = new SplFixedArray(9);
        $blankRow[0] = -1;//id
        $blankRow[1] = null;//row index
        $blankRow[2] = null;//description
        $blankRow[3] = 0;//level
        $blankRow[4] = self::ROW_TYPE_BLANK;//type
        $blankRow[5] = null;//unit
        $blankRow[6] = null;//rate
        $blankRow[7] = null;//quantity per unit
        $blankRow[8] = null;//include

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row

        /*
         * Always display element description at start of every page.
         */
        $row    = new SplFixedArray(9);
        $row[0] = -1;//id
        $row[1] = null;//row index
        $row[2] = $elementInfo['description'];//description
        $row[3] = 0;//level
        $row[4] = self::ROW_TYPE_ELEMENT;//type
        $row[5] = null;//unit
        $row[6] = null;//rate
        $row[7] = null;//quantity per unit
        $row[8] = null;//include

        array_push($itemPages[$pageCount], $row);

        //blank row
        array_push($itemPages[$pageCount], $blankRow);

        $itemIndex    = 1;
        $counterIndex = 0;//display item's index in BQ

        foreach($billItems as $x => $billItem)
        {
            $ancestors = $billItem['level'] == 0 ? array() : $ancestors;

            if ($billItem['type'] == BillItem::TYPE_HEADER)
            {
                $row    = new SplFixedArray(9);
                $row[0] = $billItem['id'];//id
                $row[1] = null;//row index
                $row[2] = $billItem['description'];//description
                $row[3] = $billItem['level'];//level
                $row[4] = $billItem['type'];//type
                $row[5] = null;//unit
                $row[6] = null;//rate
                $row[7] = null;//qty per unit
                $row[8] = null;//include

                $ancestors[$billItem['level']] = $row;

                $ancestors = array_splice($ancestors, 0, $billItem['level']+1);
            }

            $row    = new SplFixedArray(11);
            $row[1] = NULL;
            $row[2] = $billItems[$x]['description'];
            $row[3] = $billItem['level'];
            $row[4] = $billItem['type'];
            $row[0] = null;
            $row[5] = null;//unit
            $row[6] = null;//rate
            $row[7] = null;//qty per unit
            $row[8] = true;// include
            $row[9] = $billItem['bill_ref'];

            if($billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_NOID)
            {
                $row[0]  = $billItem['id'];
                $row[5]  = $billItem['uom_symbol'];
                $row[6]  = 0;
                $row[7]  = array($billItem['grand_total_quantity']);
                $row[8]  = true;
            }

            if ($billItem['type'] == BillItem::TYPE_NOID )
            {
                $row[5] = $billItem['uom_symbol'];//unit
            }

            array_push($itemPages[$pageCount], $row);

            //blank row
            array_push($itemPages[$pageCount], $blankRow);

            $itemIndex++;

            unset($billItems[$x], $row);
        }
    }

    public function getAffectedBillItems($billId, array $elementIds, array $billItemIds)
    {
        $pageNoPrefix = $this->bill->BillLayoutSetting->page_no_prefix;

        $billItems = array();

        $sqlFieldCond = '(
            CASE 
                WHEN spsori.sub_package_id is not null THEN spsori.sub_package_id
                WHEN spri.sub_package_id is not null THEN spri.sub_package_id
            ELSE
                spbi.sub_package_id
            END
            ) AS sub_package_id';

        $stmtItem = $this->pdo->prepare("SELECT DISTINCT c.id AS bill_column_setting_id, c.use_original_quantity, i.id AS bill_item_id, rate.final_value AS final_value, $sqlFieldCond
        FROM ".SubPackageTable::getInstance()->getTableName()." sp
        LEFT JOIN ".ProjectStructureTable::getInstance()->getTableName()." bill ON bill.root_id = sp.project_structure_id
        LEFT JOIN ".SubPackageResourceItemTable::getInstance()->getTableName()." AS spri ON spri.sub_package_id = sp.id
        LEFT JOIN ".SubPackageScheduleOfRateItemTable::getInstance()->getTableName()." AS spsori ON spsori.sub_package_id = sp.id
        JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = bill.id
        LEFT JOIN ".BillColumnSettingTable::getInstance()->getTableName()." c ON c.project_structure_id = e.project_structure_id
        JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id
        LEFT JOIN ".BillBuildUpRateItemTable::getInstance()->getTableName()." bur ON bur.bill_item_id = i.id AND bur.resource_item_library_id = spri.resource_item_id AND bur.deleted_at IS NULL
        LEFT JOIN ".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS sifc ON sifc.relation_id = spsori.schedule_of_rate_item_id AND sifc.deleted_at IS NULL
        LEFT JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS rate ON rate.relation_id = i.id
        LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = sp.id
        WHERE sp.id =".$this->subPackageCompany->SubPackage->id." AND sp.deleted_at IS NULL
        AND bill.id = ".$billId." AND bill.deleted_at IS NULL
        AND e.id IN (".implode(',', $elementIds).") AND e.deleted_at IS NULL
        AND NOT (spri.sub_package_id IS NULL AND spsori.sub_package_id IS NULL and spbi.sub_package_id IS NULL)
        AND (rate.relation_id = bur.bill_item_id OR rate.schedule_of_rate_item_formulated_column_id = sifc.id OR rate.relation_id = spbi.bill_item_id)
        AND rate.column_name = '".BillItem::FORMULATED_COLUMN_RATE."' AND rate.final_value <> 0 AND rate.deleted_at IS NULL
        AND i.type <> ".BillItem::TYPE_ITEM_NOT_LISTED." AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND c.deleted_at IS NULL ORDER BY i.id");

        $stmtItem->execute();
        $records = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

        if ( count($records) == 0 )
        {
            return $billItems;
        }

        $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, uom.symbol AS uom_symbol, p.grand_total, p.grand_total_quantity, p.level, p.priority, p.lft, p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char
        FROM ".BillItemTable::getInstance()->getTableName()." c
        JOIN ".BillItemTable::getInstance()->getTableName()." p ON c.lft BETWEEN p.lft AND p.rgt
        LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
        WHERE c.root_id = p.root_id AND c.type != ".BillItem::TYPE_ITEM_NOT_LISTED."
        AND c.id IN (".implode(',', $billItemIds).")
        AND c.element_id IN (".implode(',', $elementIds).") AND p.element_id IN (".implode(',', $elementIds).") AND c.project_revision_deleted_at IS NULL
        AND c.deleted_at IS NULL AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
        ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

        $stmt->execute();
        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $records as $record )
        {
            foreach ( $billItems as $billItemKey => $billItem )
            {
                if($record['bill_item_id'] == $billItem['id'])
                {
                    $quantityFieldName = $record['use_original_quantity'] ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                    $stmt = $this->pdo->prepare("SELECT COALESCE(fc.final_value, 0) AS value
                    FROM ".BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName()." fc
                    JOIN ".BillItemTypeReferenceTable::getInstance()->getTableName()." r ON fc.relation_id = r.id
                    WHERE r.bill_item_id = ".$record['bill_item_id']." AND r.bill_column_setting_id = ".$record['bill_column_setting_id']."
                    AND r.include IS TRUE AND fc.column_name = '".$quantityFieldName."' AND fc.final_value <> 0
                    AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                    $stmt->execute();
                    $quantityPerType = $stmt->fetch(PDO::FETCH_COLUMN, 0);

                    $billItems[$billItemKey][$record['bill_column_setting_id'].'-'.BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT.'-value'] = $quantityPerType;
                    $billItems[$billItemKey][$record['bill_column_setting_id'].'-total_per_unit'] = $record['final_value'] * $quantityPerType;
                }

                $billItems[$billItemKey]['bill_ref'] = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);

                unset($billItem);
            }
        }

        return $billItems;
    }

    public function hideColumn()
    {
        // hide Columns
        $this->activeSheet->getColumnDimension( $this->colRowType )->setVisible( false );
        $this->activeSheet->getColumnDimension( $this->colItemType )->setVisible( false );
        $this->activeSheet->getColumnDimension( $this->colLevel )->setVisible( false );
        $this->activeSheet->getColumnDimension( $this->colItemId )->setVisible( false );

        // for RFQ and RFQ's Supplier's importing
        // $this->activeSheet->getColumnDimension( $this->colImportMetaInfo )->setVisible( false );
    }

    public function setHiddenSubPackageCompanyId()
    {
        $this->activeSheet->setCellValue( self::SUBPACKAGECOMPANYHIDDENID, $this->subPackageCompany->id );
    }

    public function setHiddenExportedBillId()
    {
        $this->activeSheet->setCellValue( self::BILLHIDDENID, $this->bill->id );
    }

    public function setHiddenExportedAffectedBillColumnSettings()
    {
        $billColumnSettings = array();

        foreach($this->billColumnSettings as $column)
        {
            if ( ! array_key_exists($column['id'], $this->billColumnsUnits) )
            {
                continue;
            }

            $billColumnSettings[] = $column['id'];
        }

        $this->activeSheet->setCellValue( self::BILLCOLUMNSETTINGSHIDDENID, serialize($billColumnSettings));
    }

    public function setActiveSheet($sheetIndex = 0)
    {
        $this->objPHPExcel->setActiveSheetIndex( $sheetIndex );
    }

    public function getActiveSheet()
    {
        $this->activeSheet = $this->objPHPExcel->getActiveSheet();
    }

    public function createNewPage($pageNo = NULL)
    {
        $this->createHeader(true);
    }

    public function createHeader( $new = false )
    {
        $row = $currentRow = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, "Item" );
        $this->activeSheet->setCellValue( $this->colDescription.$row, "Description" );
        $this->activeSheet->setCellValue( $this->colUnit.$row, "Unit" );
        $this->activeSheet->setCellValue( $this->colRate.$row, 'Rate' );

        $currCol = $this->colRate;

        if(count($this->billColumnsUnits))
        {
            foreach ( $this->billColumnSettings as $column )
            {
                if ( ! array_key_exists($column['id'], $this->billColumnsUnits) )
                {
                    continue;
                }

                $currentRow = $startRow = $row;

                $currCol++;
                $lastCol = $currCol;

                $mergeBillColumn = PHPExcel_Cell::stringFromColumnIndex(PHPExcel_Cell::columnIndexFromString($currCol));

                //setup first header row
                $this->activeSheet->setCellValue( $currCol.$currentRow, $column['name'] );
                $this->activeSheet->mergeCells( $currCol.$currentRow.':'.$mergeBillColumn.$currentRow );

                //Setup Second Header Row
                $currentRow++;
                $firstCol = $currCol;
                $secondRow = $currentRow;

                $this->activeSheet->setCellValue( $currCol.$currentRow, 'Qty' );
                $this->activeSheet->getColumnDimension( $currCol )->setWidth( 15 );

                $currCol++;
                $secondCol = $currCol;

                $this->activeSheet->setCellValue( $secondCol.$currentRow, 'Amount' );
                $this->activeSheet->getColumnDimension( $currCol )->setWidth( 15 );
            }
        }

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$currCol.($currentRow) )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );

        $this->activeSheet->mergeCells( $this->colItem.$this->currentRow.':'.$this->colItem.$currentRow );
        $this->activeSheet->mergeCells( $this->colDescription.$this->currentRow.':'.$this->colDescription.$currentRow );
        $this->activeSheet->mergeCells( $this->colUnit.$this->currentRow.':'.$this->colUnit.$currentRow );
        $this->activeSheet->mergeCells( $this->colRate.$this->currentRow.':'.$this->colRate.$currentRow );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
        $this->activeSheet->getColumnDimension( $this->colUnit )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colRate )->setWidth( 28 );

        if(count($this->billColumnsUnits))
        {
            $this->currentRow+=2;
        }
        else
        {
            $this->currentRow++;
        }
    }

    public function startBillCounter()
    {
        $this->currentRow       = $this->startRow;
        $this->firstCol         = $this->colItem;
        $this->lastCol          = $this->colRate;
        $this->currentElementNo = 0;
        $this->columnSetting    = null;

        if(count($this->billColumnsUnits))
        {
            $currCol = $this->colRate;

            foreach($this->billColumnSettings as $column)
            {
                if ( ! array_key_exists($column['id'], $this->billColumnsUnits) )
                {
                    continue;
                }

                for($i=1; $i<=2; $i++)
                {
                    $currCol++;
                }
            }

            $this->lastCol = $currCol;
        }
    }

    public function createFooter()
    {
        $this->newLine( true );

        $this->printTotalFooter();
        $this->activeSheet->setBreak( $this->colDescription.$this->currentRow , PHPExcel_Worksheet::BREAK_ROW );

        $this->currentRow+=2;
    }

    public function setRowItem( $itemId, $description, $itemType, $itemLvl )
    {
        $coord          = $this->colDescription.$this->currentRow;
        $rowTypeCoord   = $this->colRowType.$this->currentRow;
        $itemTypeCoord  = $this->colItemType.$this->currentRow;
        $levelCoord     = $this->colLevel.$this->currentRow;
        $rfqItemIdCoord = $this->colItemId.$this->currentRow;
        $this->itemType = $itemType;

        $this->activeSheet->setCellValue( $coord, $description );

        $this->setItemStyle();

        $this->activeSheet->setCellValue( $rowTypeCoord, self::ROW_TYPE_ITEM_TEXT );
        $this->activeSheet->setCellValue( $itemTypeCoord, BillItemTable::getItemTypeText($itemType) );
        $this->activeSheet->setCellValue( $levelCoord, $itemLvl );
        $this->activeSheet->setCellValue( $rfqItemIdCoord, $itemId );
    }

    public function setEditableRate()
    {
        $coord = $this->colRate.$this->currentRow;
        $style = $this->getEditableRateStyle();

        $this->activeSheet->getStyle($coord)->applyFromArray( $style );
        $this->activeSheet->getStyle($coord)->getNumberFormat()->setFormatCode('#,##0.00_-');
        $this->unlockCell($coord);
    }

    public function getEditableRateStyle()
    {
        return array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => $this->editableCellColor)
            ),
        );
    }

    public function getRateStyle()
    {
        return array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );
    }

    public function getColumnSettingsUnits()
    {
        $billColumnSettingIds = array();

        foreach ( $this->billColumnSettings as $billColumnSetting )
        {
            $billColumnSettingIds[] = $billColumnSetting['id'];
        }

        $stmt = $this->pdo->prepare("SELECT r.bill_column_setting_id, COALESCE(COUNT(r.id), 0) FROM ".SubPackageTypeReferenceTable::getInstance()->getTableName()." r
        JOIN ".SubPackageCompanyTable::getInstance()->getTableName()." spc ON spc.sub_package_id = r.sub_package_id
        WHERE spc.id = ".$this->subPackageCompany->id." AND r.bill_column_setting_id IN (".implode(',', $billColumnSettingIds).") GROUP BY r.bill_column_setting_id");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
    }

    public function processItems($item)
    {
        $currCol = $this->colRate;

        foreach ( $this->billColumnSettings as $column )
        {
            if ( ! array_key_exists($column['id'], $this->billColumnsUnits) )
            {
                continue;
            }

            $currCol++;

            $value = NULL;

            if ( $array = Utilities::array_recursive_search($this->billItems, 'id', $item[0]) AND isset($array[0][$column['id'].'-'.BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT.'-value']) )
            {
                $value = $array[0][$column['id'].'-'.BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT.'-value'];
            }

            $this->setColumnQtyValue($currCol, $value);

            $currCol++;

            $this->setColumnAmount($currCol);
        }
    }

    public function setColumnQtyValue($column, $value)
    {
        $coord = $column.$this->currentRow;

        $style = $this->getRateStyle();

        $format = $this->getCurrencyFormat();

        $this->activeSheet->setCellValue( $coord, $value );

        $this->activeSheet->getStyle( $coord )->applyFromArray( $style );

        $this->activeSheet->getStyle( $coord )->getNumberFormat()->applyFromArray( $format );
    }

    public function setColumnAmount($column, $value = null)
    {
        // to get rate column
        $rateCoord = $this->colRate.$this->currentRow;

        // to get quantity column
        $qtyCoord = PHPExcel_Cell::stringFromColumnIndex(PHPExcel_Cell::columnIndexFromString($column) - 2).$this->currentRow;

        $coord  = $column.$this->currentRow;
        $style  = $this->getRateStyle();
        $format = $this->getCurrencyFormat();

        if ( is_null($value) )
        {
            //Set Formula
            $value = '=ROUND('.$rateCoord.'*'.$qtyCoord.', 2)';
        }

        $this->activeSheet->getStyle( $coord )->getNumberFormat()->applyFromArray( $format );

        $this->activeSheet->setCellValue( $coord, $value );

        $this->activeSheet->getStyle( $coord )->applyFromArray( $style );
    }

    public function setSingleUnitColumnAmount($column)
    {
        $mergeQtyCol         = PHPExcel_Cell::stringFromColumnIndex(PHPExcel_Cell::columnIndexFromString($column) - 2).$this->currentRow;

        $lastAmtRowColumnRow = $this->currentRow-1;

        $amtColumn           = $column.$this->colAmtStartRow;
        $lastAmtRowColumn    = $column.$lastAmtRowColumnRow;
        $style               = $this->getRateStyle();
        $format              = $this->getCurrencyFormat();

        $this->activeSheet->getStyle( $mergeQtyCol )->getNumberFormat()->applyFromArray( $format );
        $this->activeSheet->setCellValue( $mergeQtyCol, "=SUM({$amtColumn}:{$lastAmtRowColumn})" );
        $this->activeSheet->mergeCells( $mergeQtyCol.':'.$column.$this->currentRow );
        $this->activeSheet->getStyle( $mergeQtyCol )->applyFromArray( $style );
    }

    public function setSingleColumnUnits($column, $units)
    {
        $mergeQtyCol = PHPExcel_Cell::stringFromColumnIndex(PHPExcel_Cell::columnIndexFromString($column) - 2).$this->currentRow;
        $amtColumn   = $column.$this->colAmtStartRow;
        $style       = $this->getNumberFormatStandard();

        $this->activeSheet->setCellValue( $mergeQtyCol, $units );
        $this->activeSheet->mergeCells( $mergeQtyCol.':'.$column.$this->currentRow );
        $this->activeSheet->getStyle( $mergeQtyCol )->applyFromArray( $style );
    }

    public function setGrandTotalForColumn($column)
    {
        $mergeQtyCol = PHPExcel_Cell::stringFromColumnIndex(PHPExcel_Cell::columnIndexFromString($column) - 2);
        $totalColumn = $mergeQtyCol.$this->colTotalRow;
        $qtyColumn   = $mergeQtyCol.$this->colUnitRow;
        $style       = $this->getRateStyle();
        $format      = $this->getCurrencyFormat();

        $this->activeSheet->setCellValue( $mergeQtyCol.$this->currentRow, "=ROUND({$totalColumn}*{$qtyColumn}, 2)" );
        $this->activeSheet->mergeCells( $mergeQtyCol.$this->currentRow.':'.$column.$this->currentRow );
        $this->activeSheet->getStyle( $mergeQtyCol.$this->currentRow )->getNumberFormat()->applyFromArray( $format );
        $this->activeSheet->getStyle( $mergeQtyCol.$this->currentRow )->applyFromArray( $style );
    }

    public function getNumberFormatStandard()
    {
        return array('code' => '#,##0');
    }

    public function getCurrencyFormat()
    {
        return array('code' => '#,##0.00');
    }

    public function printTotalFooter()
    {
        $this->printColSingleUnitTotalValue();
        $this->printColUnits();
        $this->printColGrandTotal();
        $this->printGrandTotal();
    }

    public function printColSingleUnitTotalValue()
    {
        if(count($this->billColumnsUnits) == 0) return;

        $totalStyle = array(
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'wrapText' => true
            )
        );

        $this->colTotalRow = $this->currentRow;

        $this->activeSheet->getStyle( $this->colRate.$this->currentRow )->applyFromArray( $totalStyle );
        $this->activeSheet->setCellValue( $this->colRate.$this->currentRow, "Total per Unit" );

        $currCol = $this->colRate;

        foreach($this->billColumnSettings as $column)
        {
            if ( ! array_key_exists($column['id'], $this->billColumnsUnits) )
            {
                continue;
            }

            for($i=1; $i<=2; $i++)
            {
                $currCol++;
            }

            $this->setSingleUnitColumnAmount($currCol);
        }

        $newLineStyle = array(
            'borders' => array(
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                )
            )
        );

        $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );

        $this->activeSheet->getStyle( $this->colRate.$this->currentRow.":".$this->lastCol.$this->currentRow )->applyFromArray( $newLineStyle );

        $this->currentRow++;
    }

    public function printColUnits()
    {
        if(count($this->billColumnsUnits) == 0) return;

        $totalStyle = array(
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'wrapText' => true
            )
        );

        $this->colUnitRow = $this->currentRow;

        $this->activeSheet->getStyle( $this->colRate.$this->currentRow )->applyFromArray( $totalStyle );
        $this->activeSheet->setCellValue( $this->colRate.$this->currentRow, "Total Unit(s) X" );

        $currCol = $this->colRate;

        foreach($this->billColumnSettings as $column)
        {
            if ( ! array_key_exists($column['id'], $this->billColumnsUnits) )
            {
                continue;
            }

            $units = $this->billColumnsUnits[$column['id']][0];

            for($i=1; $i<=2; $i++)
            {
                $currCol++;
            }

            $this->setSingleColumnUnits($currCol, $units);
        }

        $newLineStyle = array(
            'borders' => array(
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                )
            )
        );

        $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );

        $this->activeSheet->getStyle( $this->colRate.$this->currentRow.":".$this->lastCol.$this->currentRow )->applyFromArray( $newLineStyle );

        $this->currentRow++;
    }

    public function printColGrandTotal()
    {
        if(count($this->billColumnsUnits) == 0) return;

        $totalStyle = array(
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'wrapText' => true
            )
        );

        $this->activeSheet->getStyle( $this->colRate.$this->currentRow )->applyFromArray( $totalStyle );
        $this->activeSheet->setCellValue( $this->colRate.$this->currentRow, "Total per Type" );

        $this->colAllUnitTotalRow = $this->currentRow;
        $currCol                  = $this->colRate;

        foreach($this->billColumnSettings as $column)
        {
            if ( ! array_key_exists($column['id'], $this->billColumnsUnits) )
            {
                continue;
            }

            $units = $this->billColumnsUnits[$column['id']][0];

            for($i=1; $i<=2; $i++)
            {
                $currCol++;
            }

            $this->setGrandTotalForColumn($currCol);
        }

        $newLineStyle = array(
            'borders' => array(
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                )
            )
        );

        $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );

        $this->activeSheet->getStyle( $this->colRate.$this->currentRow.":".$this->lastCol.$this->currentRow )->applyFromArray( $newLineStyle );

        $this->currentRow++;
    }

    public function printGrandTotal()
    {
        if(count($this->billColumnsUnits) == 0) return;

        $totalStyle = array(
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'wrapText' => true
            )
        );

        $this->activeSheet->getStyle( $this->colRate.$this->currentRow )->applyFromArray( $totalStyle );
        $this->activeSheet->setCellValue( $this->colRate.$this->currentRow, "Total Amount To Form Of Tender" );

        $columns = array();
        $currCol = $this->colRate;

        foreach($this->billColumnSettings as $column)
        {
            if ( ! array_key_exists($column['id'], $this->billColumnsUnits) )
            {
                continue;
            }

            for($i=1; $i<=2; $i++)
            {
                $currCol++;

                if ( $i % 2 != 0 )
                {
                    $columns[] = $currCol.$this->colAllUnitTotalRow;
                }
            }
        }

        $grandTotalCol = $this->colRate;
        $grandTotalCol++;

        $this->activeSheet->setCellValue( $grandTotalCol.$this->currentRow, "=ROUND(".implode('+', $columns).", 2)" );
        $this->activeSheet->mergeCells( $grandTotalCol.$this->currentRow.':'.$this->lastCol.$this->currentRow );
        $this->activeSheet->getStyle( $grandTotalCol.$this->currentRow )->getNumberFormat()->applyFromArray( $this->getCurrencyFormat() );
        $this->activeSheet->getStyle( $grandTotalCol.$this->currentRow )->applyFromArray( $this->getRateStyle() );

        $newLineStyle = array(
            'borders' => array(
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                )
            )
        );

        $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );

        $this->activeSheet->getStyle( $this->colRate.$this->currentRow.":".$this->lastCol.$this->currentRow )->applyFromArray( $newLineStyle );

        $this->currentRow++;
    }

    public function setFileName($filename)
    {
        $this->filename = $filename;
    }

}