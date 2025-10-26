<?php
class sfBillExportExcel extends sfBuildspaceBQExcelGenerator
{
    private $selectedElementIds;

    //const TOTAL_BILL_ITEM_PROPERTY    = 9;
    
    public function __construct(ProjectStructure $bill, Array $elementIds, $withRate = false, $withQuantity = false)
    {
        if($bill->getType() != ProjectStructure::TYPE_BILL)
        {
            throw new Exception('Parameter passed is not type BILL');
        }

        $this->pdo                  = $bill->getTable()->getConnection()->getDbh();
        $this->withRate             = $withRate;
        $this->withQuantity         = $withQuantity;
        $this->selectedElementIds   = $elementIds;
        $this->originalBillRevision = ProjectRevisionTable::getOriginalProjectRevisionFromBillId($bill->root_id);

        parent::__construct($bill);
    }

    private function processBill()
    {
        // get bill's printout setting
        $printSettings             = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($this->bill->BillLayoutSetting->id, TRUE);
        $this->printSettings       = $printSettings;

        $this->elementsOrder       = $this->getElementOrder();

        $numberOfBillColumns       = count($this->columnSettings);

        $this->fontType            = self::setFontType($printSettings['layoutSetting']['fontTypeName']);
        $this->fontSize            = $printSettings['layoutSetting']['fontSize'];

        $this->headSettings        = $this->printSettings['headSettings'];
        $this->currency            = $this->project->MainInformation->Currency;
        $this->numberOfBillColumns = $numberOfBillColumns;
        $this->orientation         = $numberOfBillColumns > 1 ? self::ORIENTATION_LANDSCAPE : self::ORIENTATION_PORTRAIT;
        $this->pageFormat          = $this->setPageFormat(self::PAGE_FORMAT_A4);

        self::setMaxCharactersPerLine($this->printSettings['layoutSetting']['printAmountOnly']);

        $this->billStructure = $this->queryBillStructure();

        $this->setupBill();
    }

    public function process($lock = false)
    {
        $this->lock = $lock;

        $this->processBill();

        $elementCount       = $this->elementsOrder;
        $billStructure      = $this->billStructure;
        $billColumnSettings = $this->getBillColumnSettings();

        $ratesAfterMarkup  = $this->withRate ? $this->getRatesAfterMarkup() : [];
        $lumpSumPercents   = $this->getLumpSumPercent();
        $itemQuantities    = $this->withQuantity ? $this->getItemQuantities() : [];
        $itemIncludeStatus = $this->getItemIncludeStatus();

        $billHeader = $this->bill->title;

        foreach($billStructure as $billStructureIdx => $element)
        {
            $this->startElement($element);

            $this->currentRow = $this->startRow;

            if($billStructureIdx == 0)
            {
                $workSheet = $this->objPHPExcel->getActiveSheet();
            }
            else
            {
                $workSheet = $this->objPHPExcel->createSheet($billStructureIdx);

                $this->objPHPExcel->setActiveSheetIndex($billStructureIdx);
            }

            $workSheet->setTitle('Element '.($billStructureIdx+1));

            $this->setBillHeader($billHeader);

            $itemPages = [];

            $collectionPages = [];

            $elemCount = $elementCount[$element['id']]['order'];

            $elementInfo = [
                'id'            => $element['id'],
                'description'   => $element['description'],
                'element_count' => $elemCount
            ];

            $this->generateBillItemPages($element['items'], $billColumnSettings, $elementInfo, 1, [], $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities);

            $description = '';
            $char = '';
            $prevItemType = '';
            $prevLft = null;
            $prevRgt = null;
            $prevItemLevel = 0;

            foreach($itemPages as $pageNo => $page)
            {
                $this->createNewPage($pageNo);

                foreach($page as $item)
                {
                    $itemType = $item[self::ROW_BILL_ITEM_TYPE];

                    switch($itemType)
                    {
                        case self::ROW_TYPE_BLANK:

                            if($description != '' && $prevItemType != '')
                            {
                                if($prevItemType == BillItem::TYPE_HEADER_N || $prevItemType == BillItem::TYPE_HEADER)
                                {
                                    parent::newItem();

                                    $isHeadNew = (strpos($description,$this->printSettings['layoutSetting']['contdPrefix']) !== false) ? false : true;

                                    parent::setItemHead( $description,  $prevItemType, $prevLft, $prevRgt, $prevItemLevel, $isHeadNew );
                                }

                                $description = '';
                            }
                            break;
                        case self::ROW_TYPE_ELEMENT:
                            parent::setElementDescription($item);
                            break;
                        case BillItem::TYPE_HEADER_N:
                        case BillItem::TYPE_HEADER:

                            $description  .= trim($item[self::ROW_BILL_ITEM_DESCRIPTION])."\n";
                            $prevItemType  = $item[self::ROW_BILL_ITEM_TYPE];
                            $prevItemLevel = $item[self::ROW_BILL_ITEM_LEVEL];
                            $prevLft       = $item->offsetExists(self::ROW_BILL_ITEM_LEFT) ? $item[self::ROW_BILL_ITEM_LEFT] : null;
                            $prevRgt       = $item->offsetExists(self::ROW_BILL_ITEM_RIGHT) ? $item[self::ROW_BILL_ITEM_RIGHT] : null;

                            break;
                        case self::ROW_TYPE_PC_RATE:

                            $description = $item[self::ROW_BILL_ITEM_DESCRIPTION];

                            if($item[self::ROW_BILL_ITEM_LEVEL] == -2)
                            {
                                $this->newItem();
                            }

                            if(!($item[self::ROW_BILL_ITEM_LEVEL] == -1 || $item[self::ROW_BILL_ITEM_LEVEL] == -2))
                            { //means header
                                if($item[self::ROW_BILL_ITEM_RATE])
                                {
                                    $description.=' ('.number_format($item[self::ROW_BILL_ITEM_RATE], 2, '.', '').'%)';
                                }

                                $this->setSubRow( $description, false, $item[self::ROW_BILL_ITEM_QTY_PER_UNIT] );
                            }

                            $description = '';
                            break;
                        case BillItem::TYPE_ITEM_LUMP_SUM_PERCENT:
                            $description.=$item[self::ROW_BILL_ITEM_DESCRIPTION]."\n";

                            $char.=$item[self::ROW_BILL_ITEM_ROW_IDX];

                            if($item[self::ROW_BILL_ITEM_ID])
                            {
                                parent::newItem();

                                parent::setItem( $description,  $itemType , $item[self::ROW_BILL_ITEM_LEFT], $item[self::ROW_BILL_ITEM_RIGHT], $item[self::ROW_BILL_ITEM_LEVEL]);

                                parent::setUnit( $item[self::ROW_BILL_ITEM_UNIT] );

                                parent::setChar( $char );

                                if(array_key_exists($item[self::ROW_BILL_ITEM_ID], $lumpSumPercents))
                                {
                                    parent::setLumpSumPercentInfo($item[self::ROW_BILL_ITEM_RATE], $lumpSumPercents[$item[self::ROW_BILL_ITEM_ID]][0]);
                                }

                                parent::setRate( ($this->withRate) ? $item[self::ROW_BILL_ITEM_RATE] : null );

                                if($this->withQuantity)
                                {
                                    parent::setQuantity( $item[self::ROW_BILL_ITEM_QTY_PER_UNIT], $item[self::ROW_BILL_ITEM_INCLUDE] );
                                }

                                parent::setAmount();

                                $description = '';

                                $char = '';
                            }
                        break;
                        default:

                            $description.= trim($item[self::ROW_BILL_ITEM_DESCRIPTION])."\n";

                            $char.=$item[self::ROW_BILL_ITEM_ROW_IDX];

                            if($item[self::ROW_BILL_ITEM_ID])
                            {
                                parent::newItem();

                                parent::setItem( $description,  $itemType , $item[self::ROW_BILL_ITEM_LEFT], $item[self::ROW_BILL_ITEM_RIGHT], $item[self::ROW_BILL_ITEM_LEVEL]);

                                parent::setUnit( $item[self::ROW_BILL_ITEM_UNIT] );

                                parent::setChar( $char );

                                parent::setRate( ($this->withRate) ? $item[self::ROW_BILL_ITEM_RATE] : null);

                                if($item[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_RATE_ONLY)
                                {
                                    $overridingValue = "Rate-Only";

                                    parent::setQuantity( $item[self::ROW_BILL_ITEM_QTY_PER_UNIT], $item[self::ROW_BILL_ITEM_INCLUDE], $overridingValue );
                                }
                                else
                                {
                                    if($this->withQuantity)
                                    {
                                        parent::setQuantity( $item[self::ROW_BILL_ITEM_QTY_PER_UNIT], $item[self::ROW_BILL_ITEM_INCLUDE] );
                                    }

                                    parent::setAmount();
                                }

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

            unset($itemPages, $collectionPages);
        }

        $this->updateBillReferences();

        $this->objPHPExcel->setActiveSheetIndex();
    }

    protected function queryBillStructure()
    {
        $pdo           = $this->pdo;
        $bill          = $this->bill;
        $elementIds    = $this->selectedElementIds;
        $billStructure = new SplFixedArray(0);

        if(empty($elementIds))
            return $billStructure;
        
        $stmt = $pdo->prepare( "SELECT e.id, e.description FROM ".BillElementTable::getInstance()->getTableName()." e
        WHERE e.project_structure_id = ".$bill->id." AND e.id IN (".implode(',', $elementIds).")
        AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $result = [
                'id' => $element['id'],
                'description' => $element['description'],
                'items' => []
            ];

            $stmt = $this->pdo->prepare("SELECT c.id, c.description, c.element_id, c.type,
                COALESCE(c.grand_total_after_markup, 0) AS grand_total_after_markup, c.bill_ref_element_no,
                c.bill_ref_page_no, c.bill_ref_char, c.uom_id, c.lft, c.rgt, c.root_id, c.level, uom.symbol AS uom
                FROM ".BillItemTable::getInstance()->getTableName()." c
                LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE c.element_id = ".$element['id']." AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
                ORDER BY c.priority, c.lft, c.level");
            
            $stmt->execute();

            $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result['items'] = $billItems;

            unset($element);
            $billStructure->setSize($billStructure->getSize()+1);
            $billStructure[$billStructure->getSize()-1] = $result;
        }

        return $billStructure;
    }

    protected function addElementDescription($elementInfo, $pageCount, &$itemPages, $descriptionCont)
    {
        if( $this->printSettings['layoutSetting']['printContdEndDesc'] )
        {
            $occupiedRows = Utilities::justify($elementInfo['description'] . " " . $descriptionCont, $this->MAX_CHARACTERS);
        }
        else
        {
            $occupiedRows = Utilities::justify($descriptionCont . " " . $elementInfo['description'], $this->MAX_CHARACTERS);
        }

        $rowCount = count($occupiedRows);

        foreach($occupiedRows as $key => $occupiedRow)
        {
            $rowIdx = null;

             if( $key == 0 ) $rowIdx = self::ELEMENT_DESC_FIRST_ROW;

             if( $key + 1 == $rowCount ) $rowIdx = self::ELEMENT_DESC_LAST_ROW;

            $this->addRowToItemPage($itemPages[ $pageCount ], -1, $rowIdx, $occupiedRow, 0, self::ROW_TYPE_ELEMENT, null, null);
        }

        return $occupiedRows;
    }

}
?>
