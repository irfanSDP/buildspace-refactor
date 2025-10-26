<?php

class sfScheduleOfRateBillExportExcel extends sfBuildspaceBQExcelGenerator
{

    const ROW_BILL_ITEM_ESTIMATION_RATE = 6;

    protected $colEstimationRate;
    protected $colRate = 5;

    public function __construct(ProjectStructure $bill)
    {
        $this->pdo = $bill->getTable()->getConnection()->getDbh();

        parent::__construct($bill);
    }

    private function processBillByElementId(Array $elementIds)
    {
        // get bill's printout setting
        $this->printSettings = ScheduleOfRateBillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($this->bill->ScheduleOfRateBillLayoutSetting->id, true);

        $this->elementsOrder = $this->getElementOrder();

        $this->fontType = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->fontSize = $this->printSettings['layoutSetting']['fontSize'];

        $this->headSettings = $this->printSettings['headSettings'];
        $this->currency     = $this->project->MainInformation->Currency;
        $this->orientation  = self::ORIENTATION_PORTRAIT;
        $this->pageFormat   = $this->setPageFormat();

        self::setItemDescriptionMaxCharactersPerLine();

        $this->billStructure = $this->queryBillStructure($elementIds);

        $this->setupBill();
    }

    public function setupBill()
    {
        $lastColumnNumber = $this->colRate;
        $lastColumn       = $lastColumnNumber;

        $this->colRowType        = Utilities::generateCharFromNumber($lastColumn + 1, true);
        $this->colItemType       = Utilities::generateCharFromNumber($lastColumn + 2, true);
        $this->colLeft           = Utilities::generateCharFromNumber($lastColumn + 3, true);
        $this->colRight          = Utilities::generateCharFromNumber($lastColumn + 4, true);
        $this->colLevel          = Utilities::generateCharFromNumber($lastColumn + 5, true);
        $this->colEstimationRate = $lastColumn + 6;

        $this->startBillCounter();

        $this->setBillProperties();
    }

    protected function setBillProperties()
    {
        $this->objPHPExcel->getProperties()
            ->setCustomProperty(self::EXCEL_PROPERTIES_TITLE, $this->bill->ScheduleOfRateBill->title);

        $this->objPHPExcel->getProperties()
            ->setCustomProperty(self::EXCEL_PROPERTIES_DESCRIPTION, $this->bill->ScheduleOfRateBill->description);

        $this->objPHPExcel->getProperties()
            ->setCustomProperty(self::EXCEL_PROPERTIES_PROJECT_STRUCTURE_TYPE, (int) $this->bill->type);

        $this->objPHPExcel->getProperties()
            ->setCustomProperty(self::EXCEL_PROPERTIES_UNIT_TYPE, (int) $this->bill->ScheduleOfRateBill->unit_type);
    }

    public function process(Array $elementIds, $lock = false, $withRate = false)
    {
        $this->processBillByElementId($elementIds);

        $elementCount = $this->elementsOrder;

        $billStructure = $this->billStructure;

        //Initiate Excel
        parent::setExcelParameter($lock, $withRate, false);

        $billHeader = $this->bill->title;

        foreach ($billStructure as $billStructureIdx => $element)
        {
            $this->startElement($element);

            $this->currentRow = $this->startRow;

            if ($billStructureIdx == 0)
            {
                $workSheet = $this->objPHPExcel->getActiveSheet();
            }
            else
            {
                $workSheet = $this->objPHPExcel->createSheet($billStructureIdx);

                $this->objPHPExcel->setActiveSheetIndex($billStructureIdx);
            }

            $workSheet->setTitle('Element ' . ( $billStructureIdx + 1 ));

            $this->setBillHeader($billHeader);

            $itemPages = array();

            $collectionPages = array();

            $elemCount = $elementCount[$element['id']]['order'];

            $elementInfo = array(
                'description'   => $element['description'],
                'element_count' => $elemCount
            );

            $this->generateBillItemPages($element['items'], $elementInfo, 1, array(), $itemPages);

            $description   = '';
            $char          = '';
            $prevItemType  = '';
            $prevLft       = null;
            $prevRgt       = null;
            $prevItemLevel = 0;

            foreach ($itemPages as $pageNo => $page)
            {
                $this->createNewPage($pageNo);

                foreach ($page as $item)
                {
                    $itemType = $item[self::ROW_BILL_ITEM_TYPE];

                    switch ($itemType)
                    {
                        case self::ROW_TYPE_BLANK:

                            if ($description != '' && $prevItemType != '')
                            {
                                if ($prevItemType == ScheduleOfRateBillItem::TYPE_HEADER)
                                {
                                    parent::newItem();

                                    $isHeadNew = ( strpos($description,
                                            $this->printSettings['layoutSetting']['contdPrefix']) !== false ) ? false : true;

                                    parent::setItemHead($description, $prevItemType, $prevLft, $prevRgt, $prevItemLevel,
                                        $isHeadNew);
                                }

                                $description = '';
                            }
                            break;
                        case self::ROW_TYPE_ELEMENT:
                            parent::setElementDescription($item);
                            break;
                        case ScheduleOfRateBillItem::TYPE_HEADER:
                            $description .= trim($item[self::ROW_BILL_ITEM_DESCRIPTION]) . "\n";
                            $prevItemType  = $item[self::ROW_BILL_ITEM_TYPE];
                            $prevItemLevel = $item[self::ROW_BILL_ITEM_LEVEL];
                            $prevLft       = $item->offsetExists(self::ROW_BILL_ITEM_LEFT) ? $item[self::ROW_BILL_ITEM_LEFT] : null;
                            $prevRgt       = $item->offsetExists(self::ROW_BILL_ITEM_RIGHT) ? $item[self::ROW_BILL_ITEM_RIGHT] : null;

                            break;
                        default:
                            $description .= trim($item[self::ROW_BILL_ITEM_DESCRIPTION]) . "\n";

                            $char .= $item[self::ROW_BILL_ITEM_ROW_IDX];

                            if ($item[self::ROW_BILL_ITEM_ID])
                            {
                                parent::newItem();

                                parent::setItem($description, $itemType, $item[self::ROW_BILL_ITEM_LEFT],
                                    $item[self::ROW_BILL_ITEM_RIGHT], $item[self::ROW_BILL_ITEM_LEVEL]);

                                parent::setUnit($item[self::ROW_BILL_ITEM_UNIT]);

                                parent::setChar($char);

                                $this->setEstimationRate($item[self::ROW_BILL_ITEM_ESTIMATION_RATE]);

                                $description = '';

                                $char = '';
                            }

                            break;
                    }
                }
            }

            $this->createFooter();

            $this->protectSheet();
            $this->hideColumn();

            unset( $itemPages, $collectionPages );
        }

        $this->objPHPExcel->setActiveSheetIndex();
    }

    private function setEstimationRate( $estimationRate )
    {
        $coord = Utilities::generateCharFromNumber($this->colEstimationRate, true).$this->currentRow;

        $this->objPHPExcel->getActiveSheet()->getStyle($coord)->getNumberFormat()->setFormatCode("#,##0.00");
        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $estimationRate );
        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $this->getQtyStyle() );
    }

    private function generateBillItemPages(
        Array $billItems,
        $elementInfo,
        $pageCount,
        $ancestors,
        &$itemPages,
        $newPage = false
    ) {
        $itemPages[$pageCount] = array();
        $layoutSettings        = $this->printSettings['layoutSetting'];
        $maxRows               = $this->getMaxRows();

        $blankRow                                   = new SplFixedArray(9);
        $blankRow[self::ROW_BILL_ITEM_ID]           = - 1;//id
        $blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
        $blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null;//description
        $blankRow[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
        $blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK;//type
        $blankRow[self::ROW_BILL_ITEM_UNIT]         = null;//unit
        $blankRow[self::ROW_BILL_ITEM_ESTIMATION_RATE]  = null;//rate
        $blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//quantity per unit
        $blankRow[self::ROW_BILL_ITEM_INCLUDE]      = null;//include

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
        $rowCount = 1;

        /*
         * Always display element description at start of every page.
         */
        $descriptionCont = $pageCount > 1 ? $layoutSettings['contdPrefix'] : null;

        if ($this->printSettings['layoutSetting']['printContdEndDesc'])
        {
            $occupiedRows = Utilities::justify($elementInfo['description'] . " " . $descriptionCont,
                $this->MAX_CHARACTERS);
        }
        else
        {
            $occupiedRows = Utilities::justify($descriptionCont . " " . $elementInfo['description'],
                $this->MAX_CHARACTERS);
        }

        foreach ($occupiedRows as $idx => $occupiedRow)
        {
            if ($occupiedRows->count() == 1)
            {
                $elemDescRow = self::ELEMENT_DESC_LAST_ROW;
            }
            else
            {
                if ($idx == 0)
                {
                    $elemDescRow = self::ELEMENT_DESC_FIRST_ROW;
                }
                elseif (( $idx + 1 ) == count($occupiedRows))
                {
                    $elemDescRow = self::ELEMENT_DESC_LAST_ROW;
                }
                else
                {
                    $elemDescRow = null;
                }
            }

            $row                                   = new SplFixedArray(9);
            $row[self::ROW_BILL_ITEM_ID]           = - 1;//id
            $row[self::ROW_BILL_ITEM_ROW_IDX]      = $elemDescRow;
            $row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;//description
            $row[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
            $row[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_ELEMENT;//type
            $row[self::ROW_BILL_ITEM_UNIT]         = null;//unit
            $row[self::ROW_BILL_ITEM_ESTIMATION_RATE]  = null;//rate
            $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//quantity per unit
            $row[self::ROW_BILL_ITEM_INCLUDE]      = null;//include

            array_push($itemPages[$pageCount], $row);
        }

        //blank row
        array_push($itemPages[$pageCount], $blankRow);

        $rowCount += count($occupiedRows) + 1;//plus one blank row

        $itemIndex    = 1;
        $counterIndex = 0;//display item's index in BQ

        foreach ($billItems as $x => $billItem)
        {
            $ancestors = $billItem['level'] == 0 ? array() : $ancestors;

            if ($billItem['type'] == ScheduleOfRateBillItem::TYPE_HEADER)
            {
                $row                                   = new SplFixedArray(12);
                $row[self::ROW_BILL_ITEM_ID]           = $billItem['id'];//id
                $row[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
                $row[self::ROW_BILL_ITEM_DESCRIPTION]  = $billItem['description'];//description
                $row[self::ROW_BILL_ITEM_LEVEL]        = $billItem['level'];//level
                $row[self::ROW_BILL_ITEM_TYPE]         = $billItem['type'];//type
                $row[self::ROW_BILL_ITEM_UNIT]         = $billItem['lft']; //set lft info (only for ancestor)
                $row[self::ROW_BILL_ITEM_ESTIMATION_RATE]  = $billItem['rgt']; //set rgt info (only for ancestor)
                $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $billItem['root_id']; //set root_id info (only for )
                $row[self::ROW_BILL_ITEM_INCLUDE]      = null;//include
                $row[self::ROW_BILL_ITEM_LEFT]         = $billItem['lft'];
                $row[self::ROW_BILL_ITEM_RIGHT]        = $billItem['rgt'];

                $ancestors[$billItem['level']] = $row;

                $ancestors = array_splice($ancestors, 0, $billItem['level'] + 1);
            }

            /*
             * To get all ancestors from previous page so we can display it as continued headers
             * before we print out the item
             */
            if ($pageCount > 1 and $itemIndex == 1 and $billItem['level'] != 0)
            {
                // if detected current looped item is same level with ancestor, then overwrite it
                $this->unsetHeadThatIsSameLevelWithItemOnNextPage($ancestors, $billItem);

                foreach ($ancestors as $ancestor)
                {
                    if ($ancestor[self::ROW_BILL_ITEM_ID] == $billItem['id'])
                    {
                        $rowCount ++;
                        continue;
                    }

                    $descriptionCont = $pageCount > 1 ? $layoutSettings['contdPrefix'] : null;

                    if ($this->printSettings['layoutSetting']['printContdEndDesc'])
                    {
                        $occupiedRows = Utilities::justify($ancestor[self::ROW_BILL_ITEM_DESCRIPTION] . " " . $descriptionCont,
                            $this->MAX_CHARACTERS);
                    }
                    else
                    {
                        $occupiedRows = Utilities::justify($descriptionCont . " " . $ancestor[self::ROW_BILL_ITEM_DESCRIPTION],
                            $this->MAX_CHARACTERS);
                    }

                    if ($ancestor[self::ROW_BILL_ITEM_TYPE] == ScheduleOfRateBillItem::TYPE_HEADER)
                    {
                        foreach ($occupiedRows as $occupiedRow)
                        {
                            $row                                   = new SplFixedArray(12);
                            $row[self::ROW_BILL_ITEM_ID]           = $ancestor[self::ROW_BILL_ITEM_ID];//id
                            $row[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
                            $row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;//description
                            $row[self::ROW_BILL_ITEM_LEVEL]        = $ancestor[self::ROW_BILL_ITEM_LEVEL];//level
                            $row[self::ROW_BILL_ITEM_TYPE]         = $ancestor[self::ROW_BILL_ITEM_TYPE];//type
                            $row[self::ROW_BILL_ITEM_UNIT]         = null;//unit
                            $row[self::ROW_BILL_ITEM_ESTIMATION_RATE]  = null;//rate
                            $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//quantity per unit
                            $row[self::ROW_BILL_ITEM_INCLUDE]      = true;//include
                            $row[self::ROW_BILL_ITEM_LEFT]         = $billItem['lft'];
                            $row[self::ROW_BILL_ITEM_RIGHT]        = $billItem['rgt'];

                            array_push($itemPages[$pageCount], $row);

                            $rowCount ++;
                        }

                        //blank row
                        array_push($itemPages[$pageCount], $blankRow);
                        $rowCount ++;

                        unset( $occupiedRow, $occupiedRows, $ancestor );
                    }
                }
            }

            $occupiedRows = $this->calculateBQItemDescription($billItem);
            $rowCount += count($occupiedRows);

            if ($rowCount <= $maxRows)
            {
                foreach ($occupiedRows as $key => $occupiedRow)
                {
                    if ($key == 0 && $billItem['type'] != ScheduleOfRateBillItem::TYPE_HEADER)
                    {
                        $counterIndex ++;
                    }

                    $row = new SplFixedArray(12);

                    $row[self::ROW_BILL_ITEM_ROW_IDX]      = ( $key == 0 && $billItem['type'] != ScheduleOfRateBillItem::TYPE_HEADER ) ? Utilities::generateCharFromNumber($counterIndex,
                        $this->printSettings['layoutSetting']['includeIandO']) : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEFT]         = $billItem['lft'];
                    $row[self::ROW_BILL_ITEM_RIGHT]        = $billItem['rgt'];
                    $row[self::ROW_BILL_ITEM_LEVEL]        = $billItem['level'];
                    $row[self::ROW_BILL_ITEM_TYPE]         = $billItem['type'];
                    $row[self::ROW_BILL_ITEM_ID]           = null;
                    $row[self::ROW_BILL_ITEM_UNIT]         = null;//unit
                    $row[self::ROW_BILL_ITEM_ESTIMATION_RATE]  = null;//rate
                    $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//qty per unit
                    $row[self::ROW_BILL_ITEM_INCLUDE]      = true;// include

                    if ($key + 1 == $occupiedRows->count() && $billItem['type'] != ScheduleOfRateBillItem::TYPE_HEADER)
                    {
                        $row[self::ROW_BILL_ITEM_ID]              = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT]            = $billItem['uom'];
                        $row[self::ROW_BILL_ITEM_ESTIMATION_RATE] = self::gridCurrencyRoundingFormat($billItem['estimation_rate']);

                        $quantityPerUnit = array();

                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $quantityPerUnit;
                    }

                    array_push($itemPages[$pageCount], $row);
                }

                //blank row
                array_push($itemPages[$pageCount], $blankRow);

                $rowCount ++;//plus one blank row;
                $itemIndex ++;
                $newPage = false;

                unset( $billItems[$x], $row );
            }
            else
            {
                $pageCount ++;
                $this->generateBillItemPages($billItems, $elementInfo, $pageCount, $ancestors, $itemPages, true);
                break;
            }
        }
    }

    public function getMaxRows()
    {
        return 72;
    }

    protected function setPageFormat()
    {
        return array(
            'page_format'       => self::PAGE_FORMAT_A4,
            'minimum-font-size' => $this->fontSize,
            'width'             => $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800,
            'height'            => $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595,
            'pdf_margin_top'    => 8,
            'pdf_margin_right'  => 4,
            'pdf_margin_bottom' => 3,
            'pdf_margin_left'   => 24
        );
    }

    public function setItemDescriptionMaxCharactersPerLine()
    {
        switch ($this->fontSize)
        {
            case 10 :
                $this->MAX_CHARACTERS = 51;
                break;
            case 11:
                $this->MAX_CHARACTERS = 44;
                break;
            case 12:
                $this->MAX_CHARACTERS = 43;
                break;
            default:
                $this->MAX_CHARACTERS = 43;
                break;

        }
    }

    public function queryBillStructure(array $elementIds)
    {
        $billStructure = array();

        $stmt = $this->pdo->prepare("SELECT e.id, e.description FROM " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = " . $this->bill->id . " AND e.id IN (" . implode(',', $elementIds) . ")
        AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($elements as $element)
        {
            $result = array(
                'id'          => $element['id'],
                'description' => $element['description'],
                'items'       => array()
            );

            $stmt = $this->pdo->prepare("SELECT c.id, c.description, c.element_id, c.type,
                COALESCE(c.estimation_rate, 0) AS estimation_rate, c.uom_id, c.lft, c.rgt, c.root_id, c.level, uom.symbol AS uom
                FROM " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " c
                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE c.element_id = " . $element['id'] . "
                AND c.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level");

            $stmt->execute();

            $result['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            array_push($billStructure, $result);

            unset( $element, $billItems );
        }

        return $billStructure;
    }

    public function getElementOrder()
    {
        $stmt = $this->pdo->prepare("SELECT e.id, e.description FROM " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " e
            WHERE e.project_structure_id = " . $this->bill->id . " AND e.deleted_at IS NULL ORDER BY e.priority ASC");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $elementsOrder = array();
        $counter       = 1;

        foreach ($result as $element)
        {
            $elementsOrder[$element['id']] = array(
                'description' => $element['description'],
                'order'       => $counter
            );

            $counter ++;
        }

        return $elementsOrder;
    }

    public function createHeader($new = false)
    {
        $row = $this->currentRow;

        //set default column
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colItem . $row, 'Item');
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colDescription . $row, 'Description');
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colUnit . $row, 'Unit');

        //reset Character Counter
        $this->currentChar = 'A';

        if ($new)
        {
            $this->excelType = self::EXCEL_TYPE_SINGLE;

            $this->createSingleTypeHeader();
        }

        //Set Column Width
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(1.3);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension($this->colItem)->setWidth(6);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension($this->colDescription)->setWidth(45);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension($this->colUnit)->setWidth(6);

        //hide estimation rate
        $this->objPHPExcel->getActiveSheet()->getColumnDimension(Utilities::generateCharFromNumber($this->colEstimationRate,true))->setVisible(false);

        $this->objPHPExcel->getActiveSheet()->getColumnDimension(Utilities::generateCharFromNumber($this->colRate,true))->setWidth(8);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->firstCol . '1:' . $this->lastCol . '1')->applyFromArray($this->getProjectTitleStyle());
        $this->objPHPExcel->getActiveSheet()->mergeCells($this->firstCol . '1:' . $this->lastCol . '1');
        $this->currentRow ++;
    }

    public function createNewPage($pageNo = null)
    {
        if (!$pageNo)
        {
            return;
        }

        //create Footer
        $this->createFooter();

        //create new header
        $this->createHeader(true);

        //Update Current Page Counter
        $this->currentPage = $pageNo;

        $this->newLine();
    }

    /*
        Generate single type column Header
    */
    public function createSingleTypeHeader()
    {
        $row        = $this->currentRow;
        $rateColumn = Utilities::generateCharFromNumber($this->colRate, true);

        $this->objPHPExcel->getActiveSheet()->setCellValue($rateColumn . $row, 'Rate');
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row . ':' . $rateColumn . $row)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    }

    protected function startBillCounter()
    {
        $this->firstCol       = $this->colItem = Utilities::generateCharFromNumber(2, true);
        $this->colDescription = Utilities::generateCharFromNumber(3, true);
        $this->colUnit        = Utilities::generateCharFromNumber(4, true);

        //each column setting has qty and rate and at the end we need to add 2 more columns for total qty and total amount (multitype columns)
        $this->lastCol = Utilities::generateCharFromNumber($this->colRate, true);

        $this->currentElementNo = 0;
    }

}