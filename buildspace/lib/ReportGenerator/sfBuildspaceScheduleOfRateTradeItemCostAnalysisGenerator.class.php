<?php

class sfBuildspaceScheduleOfRateTradeItemCostAnalysisGenerator extends sfBuildspaceBQMasterFunction {
    public $pageTitle;
    public $billItemIds;
    public $fontSize;
    public $headSettings;

    public $scheduleOfRate;
    public $scheduleOfRateTrade;
    public $billItemResourceRates;
    public $scheduleOfRatesNoBuildUp;
    public $profitFromBillMarkup;

    private $sorItemIds = array();

    const TOTAL_BILL_ITEM_PROPERTY  = 8;
    const ROW_BILL_ITEM_ID          = 0;
    const ROW_BILL_ITEM_ROW_IDX     = 1;
    const ROW_BILL_ITEM_DESCRIPTION = 2;
    const ROW_BILL_ITEM_LEVEL       = 3;
    const ROW_BILL_ITEM_TYPE        = 4;
    const ROW_BILL_ITEM_UNIT        = 5;
    const ROW_BILL_ITEM_RATE        = 6;
    const ROW_BILL_ITEM_TOTAL_QTY   = 7;

    public function __construct($project = false, ScheduleOfRate $scheduleOfRate, ScheduleOfRateTrade $scheduleOfRateTrade, $tradeItemIds, $billItemIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo                      = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $this->project                  = $project;
        $this->tradeItemIds             = $tradeItemIds;
        $this->billItemIds              = $billItemIds;
        $this->scheduleOfRate           = $scheduleOfRate;
        $this->scheduleOfRateTrade      = $scheduleOfRateTrade;
        $this->pageTitle                = $pageTitle;
        $this->currency                 = $this->project->MainInformation->Currency;
        $this->descriptionFormat        = $descriptionFormat;
        $this->billItemResourceRates    = BillItemTable::getBillItemResourceRates($this->billItemIds);
        $this->scheduleOfRatesNoBuildUp = ScheduleOfRateTradeTable::getScheduleOfRatesWithNoBuildUp($this->billItemIds, $scheduleOfRateTrade);
        $this->profitFromBillMarkup     = ScheduleOfRateTradeTable::getProfitFromBillMarkupByScheduleOfRateTrade($this->billItemIds, $scheduleOfRateTrade);

        $this->setOrientationAndSize();

        $this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
        $this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings  = $this->printSettings['headSettings'];

        self::setMaxCharactersPerLine();
    }

    public function generateItemPages(Array $items, $tradeInfo, $pageCount, $ancestors, &$itemPages, $rowIndexCounter = 0, $newPage = false)
    {
        $this->rowCount          = 0;
        $itemPages[ $pageCount ] = array();

        self::addBlankRow($itemPages[ $pageCount ]);

        self::addElement($itemPages[ $pageCount ]);

        self::addBlankRow($itemPages[ $pageCount ]);

        foreach($items as $x => $item)
        {
            self::addItem($itemPages[ $pageCount ], $item, $rowIndexCounter);

            self::addBlankRow($itemPages[ $pageCount ]);
        }
    }

    protected function addElement(&$itemPage)
    {
        self::addRow($itemPage, -1, null, $this->currentElement['description'], 0, self::ROW_TYPE_ELEMENT, null, null, null);
    }

    protected function addItem(&$itemPage, $item, &$rowIndexCounter)
    {
        foreach($descriptionRows = self::splitDescription($item) as $key => $descriptionRow)
        {
            $id       = null;
            $rowIdx   = null;
            $level    = $item['level'];
            $type     = $item['type'];
            $unit     = null;
            $rate     = 0;
            $totalQty = 0;

            // If is last descriptionRow
            if( $key + 1 === $descriptionRows->count() )
            {
                // If is an item.
                if( $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID )
                {
                    $rowIndexCounter++;
                    $rowIdx = $rowIndexCounter;
                }

                $id       = $item['id'];
                $unit     = $item['uom_symbol'];
                $rate     = self::gridCurrencyRoundingFormat($item['rate-final_value']);
                $totalQty = self::gridCurrencyRoundingFormat($item['grand_total_quantity']);
            }

            self::addRow($itemPage, $id, $rowIdx, $descriptionRow, $level, $type, $unit, $rate, $totalQty);
        }
    }

    protected function setOrientationAndSize($orientation = false, $pageFormat = false)
    {
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
    }

    public function setPageFormat($pageFormat)
    {
        $this->pageFormat = $pageFormat;
    }

    protected function generatePageFormat($format)
    {
        $width = 595;

        $height = 800;

        return $pf = array(
            'page_format'       => self::PAGE_FORMAT_A4,
            'minimum-font-size' => $this->fontSize,
            'width'             => $width,
            'height'            => $height,
            'pdf_margin_top'    => 8,
            'pdf_margin_right'  => 8,
            'pdf_margin_bottom' => 3,
            'pdf_margin_left'   => 8
        );
    }

    public function setMaxCharactersPerLine()
    {
        $this->MAX_CHARACTERS = 45;
    }

    public function getMaxRows()
    {
        return $maxRows = 63;
    }

    protected function addRow(&$itemPage, $id, $rowIdx, $description, $level, $type, $unit, $rate, $totalQty)
    {
        $row                                    = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $row[ self::ROW_BILL_ITEM_ID ]          = $id;
        $row[ self::ROW_BILL_ITEM_ROW_IDX ]     = $rowIdx;
        $row[ self::ROW_BILL_ITEM_DESCRIPTION ] = $description;
        $row[ self::ROW_BILL_ITEM_LEVEL ]       = $level;
        $row[ self::ROW_BILL_ITEM_TYPE ]        = $type;
        $row[ self::ROW_BILL_ITEM_UNIT ]        = $unit;
        $row[ self::ROW_BILL_ITEM_RATE ]        = $rate;
        $row[ self::ROW_BILL_ITEM_TOTAL_QTY ]   = $totalQty;

        array_push($itemPage, $row);

        $this->rowCount++;
    }

    protected function addBlankRow(&$itemPage)
    {
        self::addRow($itemPage, -1, null, null, 0, self::ROW_TYPE_BLANK, null, null, null);
    }

    public function generatePages()
    {
        $pages = array();

        $printPreviewItems = array();
        $billCount         = 0;

        $totalPage = 0;

        $tradeIdToDescription       = array();
        $billElementIdToDescription = array();

        $formulatedColumnConstants = array(
            BillItem::FORMULATED_COLUMN_RATE,
            BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE
        );

        if( count($this->billItemIds) > 0 )
        {
            $items = $this->getItems();

            foreach($items as $item)
            {
                $this->sorItemIds[ $item['relation_id'] ] = $item['relation_id'];
            }

            $formulatedColumns = $this->getFormulatedColumns();

            $bills = $this->getBills();

            $tradeItems = $this->getTradeItems();

            foreach($tradeItems as $tradeItem)
            {
                $tradeItemInfo = array(
                    'id'                   => 'tradeItemId-' . $tradeItem['id'],
                    'description'          => $tradeItem['description'],
                    'type'                 => BillItem::PROJECT_ANALYZER_TRADE_ITEM,
                    'level'                => 0,
                    'uom_id'               => -1,
                    'uom_symbol'           => '',
                    'grand_total_quantity' => 0,
                    'grand_total'          => 0,
                    'rate-value'           => 0,
                    'rate-final_value'     => 0,
                    'multi-rate'           => false,
                );

                $tradeIdToDescription[ $tradeItem['id'] ] = $tradeItem['description'];

                if( ! array_key_exists($tradeItem['id'], $printPreviewItems) )
                {
                    $printPreviewItems[ $tradeItem['id'] ] = array();
                }

                if( ! array_key_exists($tradeItem['id'], $pages) )
                {
                    $pages[ $tradeItem['id'] ] = array();
                }

                foreach($formulatedColumnConstants as $formulatedColumnConstant)
                {
                    $tradeItemInfo[ $formulatedColumnConstant . '-value' ]        = '';
                    $tradeItemInfo[ $formulatedColumnConstant . '-final_value' ]  = 0;
                    $tradeItemInfo[ $formulatedColumnConstant . '-linked' ]       = false;
                    $tradeItemInfo[ $formulatedColumnConstant . '-has_formula' ]  = false;
                    $tradeItemInfo[ $formulatedColumnConstant . '-has_build_up' ] = false;
                }

                foreach($bills as $bill)
                {
                    $elements = $this->getElements($bill, $tradeItem);

                    foreach($elements as $element)
                    {
                        $itemPages = array();

                        $result = array(
                            'id'               => 'bill-' . $bill['id'] . '-elem' . $element['id'] . '-billCount-' . $billCount,
                            'description'      => $bill['title'] . " > " . $element['description'],
                            'type'             => self::ROW_TYPE_ELEMENT,
                            'level'            => 0,
                            'uom_id'           => -1,
                            'uom_symbol'       => '',
                            'rate-value'       => 0,
                            'rate-final_value' => 0,
                        );

                        $this->currentElement = $result;

                        $billElementIdToDescription[ $result['id'] ] = $result['description'];

                        foreach($formulatedColumnConstants as $formulatedColumnConstant)
                        {
                            $result[ $formulatedColumnConstant . '-value' ]       = '';
                            $result[ $formulatedColumnConstant . '-final_value' ] = 0;
                            $result[ $formulatedColumnConstant . '-linked' ]      = false;
                            $result[ $formulatedColumnConstant . '-has_formula' ] = false;
                        }

                        if( ! array_key_exists($result['id'], $printPreviewItems[ $tradeItem['id'] ]) )
                        {
                            $printPreviewItems[ $tradeItem['id'] ][ $result['id'] ] = array();
                        }

                        $billItem = array( 'id' => -1 );

                        foreach($items as $k => $item)
                        {
                            if( $billItem['id'] != $item['id'] && $item['relation_id'] == $tradeItem['id'] && $item['element_id'] == $element['id'] )
                            {
                                $billItem['id']                   = $item['id'];
                                $billItem['description']          = $item['description'];
                                $billItem['type']                 = $item['type'];
                                $billItem['grand_total']          = $item['grand_total'];
                                $billItem['grand_total_quantity'] = $item['grand_total_quantity'];
                                $billItem['level']                = $item['level'];
                                $billItem['uom_symbol']           = $item['uom_id'] > 0 ? $item['uom_symbol'] : '';

                                foreach($formulatedColumnConstants as $formulatedColumnConstant)
                                {
                                    $billItem[ $formulatedColumnConstant . '-value' ]        = '';
                                    $billItem[ $formulatedColumnConstant . '-final_value' ]  = 0;
                                    $billItem[ $formulatedColumnConstant . '-linked' ]       = false;
                                    $billItem[ $formulatedColumnConstant . '-has_formula' ]  = false;
                                    $billItem[ $formulatedColumnConstant . '-has_build_up' ] = false;
                                }

                                foreach($formulatedColumns as $key => $formulatedColumn)
                                {
                                    if( $formulatedColumn['relation_id'] == $item['id'] )
                                    {
                                        $columnName                                = $formulatedColumn['column_name'];
                                        $billItem[ $columnName . '-value' ]        = $formulatedColumn['final_value'];
                                        $billItem[ $columnName . '-final_value' ]  = $formulatedColumn['final_value'];
                                        $billItem[ $columnName . '-linked' ]       = $formulatedColumn['linked'];
                                        $billItem[ $columnName . '-has_formula' ]  = false;
                                        $billItem[ $columnName . '-has_build_up' ] = $formulatedColumn['has_build_up'];

                                        unset( $formulatedColumn, $formulatedColumns[ $key ] );
                                    }
                                }

                                array_push($printPreviewItems[ $tradeItem['id'] ][ $result['id'] ], $billItem);

                                unset( $items[ $k ], $item );
                            }
                        }

                        if( ! empty( $printPreviewItems[ $tradeItem['id'] ][ $result['id'] ] ) )
                        {
                            $this->generateItemPages($printPreviewItems[ $tradeItem['id'] ][ $result['id'] ], $result, 1, array(), $itemPages);

                            $page = array(
                                'description' => $result['description'],
                                'item_pages'  => SplFixedArray::fromArray($itemPages)
                            );

                            $totalPage += count($itemPages);

                            $pages[ $tradeItem['id'] ][ $result['id'] ] = $page;
                        }
                    }

                    $billCount++;
                }

                unset( $tradeItemInfo );
            }
        }

        $this->totalPage                  = $totalPage;
        $this->billElementIdToDescription = $billElementIdToDescription;
        $this->tradeIdToDescription       = $tradeIdToDescription;

        return $pages;
    }

    private function getItems()
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.grand_total, p.grand_total_quantity, p.level, p.priority, e.priority AS element_priority, p.lft, uom.symbol AS uom_symbol, ifc.relation_id
            FROM " . BillItemTable::getInstance()->getTableName() . " c
            JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " AS uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON c.id = bifc.relation_id
            JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON p.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
            WHERE c.id IN (" . implode(', ', $this->billItemIds) . ") AND ifc.relation_id IN (" . implode(',', $this->tradeItemIds) . ")
            AND s.root_id = " . $this->project->id . "
            AND c.root_id = p.root_id AND c.element_id = p.element_id
            AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
            AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
            AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
            AND e.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND s.deleted_at IS NULL
            ORDER BY e.priority, p.priority, p.lft, p.level ASC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getFormulatedColumns()
    {
        $stmt = $this->pdo->prepare("SELECT bifc.relation_id, bifc.column_name, bifc.final_value, bifc.linked, bifc.has_build_up
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
            JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON s.id = e.project_structure_id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i  ON i.element_id = e.id
            JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON i.id = bifc.relation_id
            JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc2 ON bifc.relation_id = bifc2.relation_id
            JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc2.schedule_of_rate_item_formulated_column_id = ifc.id
            WHERE s.root_id = " . $this->project->id . "
            AND ifc.relation_id IN (" . implode(', ', $this->sorItemIds) . ") AND bifc.column_name <> '" . BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT . "'
            AND s.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND e.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bifc2.deleted_at IS NULL
            ORDER BY ifc.relation_id ASC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getBills()
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s JOIN
            " . BillElementTable::getInstance()->getTableName() . " AS be ON s.id = be.project_structure_id JOIN
            " . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id JOIN
            " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON bi.id = bifc.relation_id JOIN
            " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
            WHERE s.root_id = " . $this->project->id . " AND ifc.relation_id IN (" . implode(', ', $this->sorItemIds) . ")
            AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
            AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
            AND s.deleted_at IS NULL ORDER BY s.lft ASC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTradeItems()
    {
        // get schedule of rate trade item's information
        $stmt = $this->pdo->prepare("SELECT t.id, t.description, t.root_id, t.lft, t.priority, t.level FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " t
            WHERE t.id IN (" . implode(', ', $this->sorItemIds) . ") AND t.trade_id = " . $this->scheduleOfRateTrade->id . " AND t.deleted_at IS NULL
            ORDER BY t.root_id, t.lft, t.priority, t.level ASC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getElements($bill, $tradeItem)
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT be.id, be.description, be.priority FROM
                    " . BillElementTable::getInstance()->getTableName() . " AS be JOIN
                    " . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id JOIN
                    " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON bi.id = bifc.relation_id JOIN
                    " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
                    WHERE be.project_structure_id = " . $bill['id'] . " AND ifc.relation_id = " . $tradeItem['id'] . "
                    AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
                    AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
                    ORDER BY be.priority ASC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}