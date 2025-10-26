<?php

class sfBuildspaceScheduleOfRateItemCostAnalysisGenerator extends sfBuildspaceBQMasterFunction {
    public $pageTitle;
    public $tradeIds;
    public $fontSize;
    public $headSettings;

    public $scheduleOfRate;
    public $scheduleOfRateItemCosts           = array();
    public $scheduleOfRateTradeResourceTotals = array();
    public $pageCount                         = 0;

    const TOTAL_BILL_ITEM_PROPERTY  = 9;
    const ROW_BILL_ITEM_ID          = 0;
    const ROW_BILL_ITEM_ROW_IDX     = 1;
    const ROW_BILL_ITEM_DESCRIPTION = 2;
    const ROW_BILL_ITEM_LEVEL       = 3;
    const ROW_BILL_ITEM_TYPE        = 4;
    const ROW_BILL_ITEM_UNIT        = 5;
    const ROW_BILL_ITEM_RATE        = 6;
    const ROW_BILL_ITEM_TOTAL_QTY   = 7;
    const ROW_BILL_ITEM_MULTI_RATE  = 8;

    public function __construct($project = false, $scheduleOfRate, $tradeIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->project = $project;

        $this->tradeIds = $tradeIds;

        $this->scheduleOfRate = $scheduleOfRate;

        $this->calculateItemCosts();

        $this->pageTitle         = $pageTitle;
        $this->currency          = $this->project->MainInformation->Currency;
        $this->descriptionFormat = $descriptionFormat;

        $this->setOrientationAndSize();

        $this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
        $this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings  = $this->printSettings['headSettings'];

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $pages = array();

        $totalPage = 0;

        if( count($this->tradeIds) <= 0 )
        {
            return $pages;
        }

        $scheduleOfRateItemsByTrades = ScheduleOfRateTradeTable::getScheduleOfRateItemsByProject($this->project, $this->tradeIds);

        $trades = DoctrineQuery::create()
            ->select('t.id, t.description')
            ->from('ScheduleOfRateTrade t')
            ->whereIn('t.id', $this->tradeIds)
            ->orderBy('t.priority')
            ->fetchArray();


        foreach($trades as $trade)
        {
            $itemPages = array();

            $tradeInfo = array(
                'id'          => 'trade-' . $trade['id'],
                'description' => $trade['description'],
                'type'        => 0,
                'uom_id'      => -1,
                'uom_symbol'  => '',
                'total_qty'   => 0,
            );

            // start new page for each trade
            $this->generateItemPages($scheduleOfRateItemsByTrades[$trade['id']], $tradeInfo, ++$this->pageCount, array(), $itemPages);

            $page = array(
                'description' => $tradeInfo['description'],
                'item_pages'  => SplFixedArray::fromArray($itemPages)
            );

            $totalPage += count($itemPages);

            $pages[ $trade['id'] ] = $page;
        }

        $this->totalPage = $totalPage;

        return $pages;
    }

    public function generateItemPages(array $scheduleOfRateItems, $tradeInfo, $pageCount, $ancestors, &$itemPages)
    {
        $this->rowCount          = 0;
        $itemPages[ $pageCount ] = array();

        self::addBlankRow($itemPages[ $pageCount ]);

        $this->currentElement = $tradeInfo;
        self::addElement($itemPages[ $pageCount ]);

        self::addBlankRow($itemPages[ $pageCount ]);

        $rowIndexCounter = 0;

        foreach($scheduleOfRateItems as $key => $scheduleOfRateItem)
        {
            self::addItem($itemPages[ $pageCount ], $scheduleOfRateItem, $rowIndexCounter);

            self::addBlankRow($itemPages[ $pageCount ]);
        }
    }

    protected function addItem(&$itemPage, $item, &$rowIndexCounter)
    {
        foreach($descriptionRows = self::splitDescription($item) as $key => $descriptionRow)
        {
            $id        = null;
            $rowIdx    = null;
            $level     = $item['level'];
            $type      = $item['type'];
            $unit      = null;
            $rate      = 0;
            $totalQty  = 0;
            $multiRate = false;

            // If is last descriptionRow
            if( $key + 1 === $descriptionRows->count() )
            {
                // If is an item.
                if( $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID )
                {
                    $rowIndexCounter++;
                    $rowIdx = $rowIndexCounter;
                }

                $id        = $item['id'];
                $unit      = $item['uom_symbol'];
                $rate      = ( $item['multi-rate'] ) ? 0 : self::gridCurrencyRoundingFormat($item['rate-final_value']);
                $totalQty  = self::gridCurrencyRoundingFormat($item['total_qty']);
                $multiRate = ( $item['multi-rate'] ) ? true : false;
            }

            self::addRow($itemPage, $id, $rowIdx, $descriptionRow, $level, $type, $unit, $rate, $totalQty, $multiRate);
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

    protected function addRow(&$itemPage, $id, $rowIdx, $description, $level, $type, $unit, $rate, $totalQty, $multiRate)
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
        $row[ self::ROW_BILL_ITEM_MULTI_RATE ]  = $multiRate;

        array_push($itemPage, $row);

        $this->rowCount++;
    }

    protected function addBlankRow(&$itemPage)
    {
        self::addRow($itemPage, -1, null, null, 0, self::ROW_TYPE_BLANK, null, null, null, null);
    }

    protected function addElement(&$itemPage)
    {
        self::addRow($itemPage, -1, null, $this->currentElement['description'], 0, self::ROW_TYPE_ELEMENT, null, null, null, null);
    }

    private function calculateItemCosts()
    {
        $trade = null;

        $this->scheduleOfRateItemCosts = ScheduleOfRateTradeTable::getCostAnalysisByProject($this->project, $this->tradeIds);

        foreach($this->tradeIds as $tradeId)
        {
            $tradeProfitTotal = 0;

            $this->scheduleOfRateTradeResourceTotals[ $tradeId ] = array(
                'resources' => array(),
                'profit'    => null,
                'noBuildUp' => 0,
                'profitFromBillMarkup' => 0
            );

            foreach($this->scheduleOfRateItemCosts[ $tradeId ] as $itemCost)
            {
                $tradeProfitTotal += $itemCost['profit']['total'];

                foreach($itemCost['resources'] as $resourceName => $resourceCost)
                {
                    if( ! array_key_exists($resourceName, $this->scheduleOfRateTradeResourceTotals[ $tradeId ]['resources']) )
                    {
                        $this->scheduleOfRateTradeResourceTotals[ $tradeId ]['resources'][ $resourceName ]['total']        = 0;
                        $this->scheduleOfRateTradeResourceTotals[ $tradeId ]['resources'][ $resourceName ]['wastageTotal'] = 0;
                    }

                    $this->scheduleOfRateTradeResourceTotals[ $tradeId ]['resources'][ $resourceName ]['total'] += $resourceCost['total'];
                    $this->scheduleOfRateTradeResourceTotals[ $tradeId ]['resources'][ $resourceName ]['wastageTotal'] += $resourceCost['wastageTotal'];
                }

                if(array_key_exists('noBuildUp', $itemCost))
                {
                    $this->scheduleOfRateTradeResourceTotals[ $tradeId ]['noBuildUp'] += $itemCost['noBuildUp']['total'];
                }

                if(array_key_exists('profitFromBillMarkup', $itemCost))
                {
                    $this->scheduleOfRateTradeResourceTotals[ $tradeId ]['profitFromBillMarkup'] += $itemCost['profitFromBillMarkup'];
                }
            }

            $this->scheduleOfRateTradeResourceTotals[ $tradeId ]['profit'] = $tradeProfitTotal;
        }
    }

}