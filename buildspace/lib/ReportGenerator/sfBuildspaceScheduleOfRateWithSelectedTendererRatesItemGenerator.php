<?php

class sfBuildspaceScheduleOfRateWithSelectedTendererRatesItemGenerator extends sfBuildspaceBQMasterFunction
{

    public $pageTitle;
    public $tradeIds;
    public $fontSize;
    public $headSettings;

    public $scheduleOfRate;

    const TOTAL_BILL_ITEM_PROPERTY      = 11;
    const ROW_BILL_ITEM_MARKUP          = 7;
    const ROW_BILL_ITEM_TOTAL_QTY       = 8;
    const ROW_BILL_ITEM_TOTAL_COST      = 9;
    const ROW_BILL_ITEM_MULTI_RATE      = 10;

    public function __construct($project = false, $scheduleOfRate, $tradeIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo         = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->project     = $project;

        $this->tradeIds    = $tradeIds;

        $this->scheduleOfRate = $scheduleOfRate;

        $this->pageTitle         = $pageTitle;
        $this->currency          = $this->project->MainInformation->Currency;
        $this->descriptionFormat = $descriptionFormat;

        $this->setOrientationAndSize();

        $this->printSettings  = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, TRUE);
        $this->fontSize       = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType       = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings   = $this->printSettings['headSettings'];

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $pages          = array();
        $tradeItemIds   = $this->tradeIds;
        $scheduleOfRate = $this->scheduleOfRate;
        $tradeIds       = array();
        $itemIds        = array();
        $items          = array();
        $totalPage      = 0;
        $awardedCompany = $this->project->TenderSetting->AwardedCompany;

        if ( ! empty($tradeItemIds) )
        {
            $stmt = $this->pdo->prepare("SELECT DISTINCT i.id, i.trade_id FROM ".ScheduleOfRateItemTable::getInstance()->getTableName()." AS i JOIN
            ".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS ifc ON i.id = ifc.relation_id JOIN
            ".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
            ".BillItemTable::getInstance()->getTableName()." AS bi ON bifc.relation_id = bi.id JOIN
            ".BillElementTable::getInstance()->getTableName()." AS be ON bi.element_id = be.id JOIN
            ".ProjectStructureTable::getInstance()->getTableName()." AS s ON be.project_structure_id = s.id
            WHERE s.root_id = ".$this->project->id." AND i.id IN (".implode(', ', $tradeItemIds).")
            AND bifc.column_name = '".BillItem::FORMULATED_COLUMN_RATE."'
            AND i.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
            AND bi.deleted_at IS NULL AND be.deleted_at IS NULL AND s.deleted_at IS NULL");

            $stmt->execute(array());

            $scheduleOfRateItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ( count($scheduleOfRateItemIds) > 0 )
            {
                foreach ( $scheduleOfRateItemIds as $scheduleOfRateItemId )
                {
                    $tradeIds[$scheduleOfRateItemId['trade_id']]  = $scheduleOfRateItemId['trade_id'];
                    $itemIds[$scheduleOfRateItemId['trade_id']][] = $scheduleOfRateItemId['id'];
                }

                $stmt = $this->pdo->prepare("SELECT id, description FROM ".ScheduleOfRateTradeTable::getInstance()->getTableName()." WHERE id IN
                (SELECT DISTINCT t.id FROM ".ScheduleOfRateTradeTable::getInstance()->getTableName()." AS t JOIN
                ".ScheduleOfRateItemTable::getInstance()->getTableName()." AS i ON t.id = i.trade_id JOIN
                ".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS ifc ON i.id = ifc.relation_id JOIN
                ".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
                ".BillItemTable::getInstance()->getTableName()." AS bi ON bifc.relation_id = bi.id JOIN
                ".BillElementTable::getInstance()->getTableName()." AS be ON bi.element_id = be.id JOIN
                ".ProjectStructureTable::getInstance()->getTableName()." AS s ON be.project_structure_id = s.id
                WHERE t.id IN (".implode(', ', $tradeIds).") AND s.root_id = ".$this->project->id." AND t.schedule_of_rate_id IN (".$scheduleOfRate->id.") AND bifc.column_name = '".BillItem::FORMULATED_COLUMN_RATE."'
                AND i.deleted_at IS NULL
                AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
                AND be.deleted_at IS NULL AND s.deleted_at IS NULL) AND schedule_of_rate_id = ".$scheduleOfRate->id." AND deleted_at IS NULL ORDER BY id");

                $stmt->execute();

                $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ( $trades as $trade )
                {
                    $tradeInfo = array(
                        'id'                      => 'trade-' . $trade['id'],
                        'description'             => $trade['description'],
                        'type'                    => 0,
                        'uom_id'                  => -1,
                        'uom_symbol'              => '',
                        'multi-rate'              => false,
                        'multi-item_markup'       => false,
                        'total_qty'               => 0,
                        'total_cost'              => 0,
                        'view_bill_item_all'      => -1,
                        'view_bill_item_drill_in' => -1
                    );

                    if(!array_key_exists($trade['id'], $items))
                    {
                        $items[$trade['id']] = array();
                    }

                    foreach(array('rate', 'item_markup') as $formulatedColumnConstant)
                    {
                        $tradeInfo[$formulatedColumnConstant.'-value']       = '';
                        $tradeInfo[$formulatedColumnConstant.'-final_value'] = 0;
                        $tradeInfo[$formulatedColumnConstant.'-linked']      = false;
                        $tradeInfo[$formulatedColumnConstant.'-has_formula'] = false;
                    }

                    $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type, p.uom_id,
                    p.level, p.priority, p.lft, uom.symbol AS uom_symbol
                    FROM ".ScheduleOfRateItemTable::getInstance()->getTableName()." c
                    JOIN ".ScheduleOfRateItemTable::getInstance()->getTableName()." p ON c.lft BETWEEN p.lft AND p.rgt
                    LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                    WHERE c.root_id = p.root_id AND c.type <> ".ScheduleOfRateItem::TYPE_HEADER."
                    AND c.id IN (".implode(',', $itemIds[$trade['id']]).") AND p.trade_id = ".$trade['id']."
                    AND c.deleted_at IS NULL AND p.deleted_at IS NULL
                    ORDER BY p.priority, p.lft, p.level ASC");

                    $stmt->execute(array());

                    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach($records as $key => $record)
                    {
                        $multiItemMarkup = false;
                        $multiRate       = false;
                        $totalQty        = 0;
                        $totalCost       = 0;

                        foreach(array('rate', 'item_markup') as $formulatedColumnConstant)
                        {
                            $records[$key][$formulatedColumnConstant.'-value']       = '';
                            $records[$key][$formulatedColumnConstant.'-final_value'] = 0;
                            $records[$key][$formulatedColumnConstant.'-linked']      = false;
                            $records[$key][$formulatedColumnConstant.'-has_formula'] = false;
                        }

                        /*
                        * getting bill item markup and sor rate
                        */
                        if($record['type'] == ScheduleOfRateItem::TYPE_WORK_ITEM)
                        {
                            $stmt = $this->pdo->prepare("SELECT DISTINCT COALESCE(markup_column.final_value, 0) AS value
                            FROM ".ProjectStructureTable::getInstance()->getTableName()." AS s
                            JOIN ".BillElementTable::getInstance()->getTableName()." AS be ON be.project_structure_id = s.id
                            JOIN ".BillItemTable::getInstance()->getTableName()." AS bi ON bi.element_id = be.id
                            JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." markup_column ON markup_column.relation_id = bi.id
                            JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." ifc
                            ON markup_column.relation_id = ifc.relation_id
                            JOIN ".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS sorifc
                            ON ifc.schedule_of_rate_item_formulated_column_id = sorifc.id
                            WHERE s.root_id = ".$this->project->id." AND sorifc.relation_id = ".$record['id']." AND markup_column.column_name = '".BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE."'
                            AND s.deleted_at IS NULL AND be.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL
                            AND markup_column.deleted_at IS NULL AND ifc.deleted_at IS NULL AND sorifc.deleted_at IS NULL");

                            $stmt->execute(array());

                            if($stmt->rowCount() > 1)
                            {
                                $multiItemMarkup = true;
                            }
                            else
                            {
                                $markup = $stmt->fetch(PDO::FETCH_ASSOC);

                                $records[$key]['item_markup-value'] = $markup['value'];
                                $records[$key]['item_markup-final_value'] = $markup['value'];
                            }

                            $stmt = $this->pdo->prepare("SELECT DISTINCT COALESCE(r.rate, 0) AS value
							FROM ".BillItemTable::getInstance()->getTableName()." AS bi
							JOIN ".BillElementTable::getInstance()->getTableName()." AS be ON bi.element_id = be.id
							JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." ifc ON ifc.relation_id = bi.id
							JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." r ON r.bill_item_id = bi.id
							JOIN ".ProjectStructureTable::getInstance()->getTableName()." AS s ON be.project_structure_id = s.id
							JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON
							(r.tender_company_id = tc.id AND tc.project_structure_id = s.root_id)
							JOIN ".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS sorifc
							ON ifc.schedule_of_rate_item_formulated_column_id = sorifc.id
							WHERE r.rate <> 0 AND s.root_id = ".$this->project->id."
							AND sorifc.relation_id = ".$record['id']." AND ifc.column_name = '".BillItem::FORMULATED_COLUMN_RATE."'
							AND tc.company_id = ".$awardedCompany->id." AND tc.show IS TRUE
							AND s.deleted_at IS NULL AND be.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL
							AND ifc.deleted_at IS NULL AND sorifc.deleted_at IS NULL");

                            $stmt->execute(array());

                            if($stmt->rowCount() > 1)
                            {
                                $multiRate = true;
                            }
                            else
                            {
                                $rate = $stmt->fetch(PDO::FETCH_ASSOC);

                                $records[$key]['rate-value']       = $rate['value'];
                                $records[$key]['rate-final_value'] = $rate['value'];
                            }

                            list($totalQty, $totalCost) = ScheduleOfRateItemTable::calculateTotalCostForAnalysisWithSelectedTendererRates($record['id'], $this->project->id, $awardedCompany->id);
                        }

                        $records[$key]['view_bill_item_all']      = $record['id'];
                        $records[$key]['view_bill_item_drill_in'] = $record['id'];
                        $records[$key]['multi-rate']              = $multiRate;
                        $records[$key]['multi-item_markup']       = $multiItemMarkup;
                        $records[$key]['total_qty']               = $totalQty;
                        $records[$key]['total_cost']              = $totalCost;

                        array_push($items[$trade['id']], $records[$key]);
                    }

                    $itemPages = array();

                    $this->generateItemPages($items[$trade['id']], $tradeInfo, 1, $itemPages);

                    $page = array(
                        'description' => $tradeInfo['description'],
                        'item_pages' => SplFixedArray::fromArray($itemPages)
                    );

                    $totalPage+= count($itemPages);

                    $pages[$trade['id']] = $page;

                    unset($records, $itemIds[$trade['id']], $tradeInfo);
                }
            }
        }

        $this->totalPage = $totalPage;

        return $pages;
    }

    public function generateItemPages(Array $items, $tradeInfo, $pageCount, &$itemPages, $counterIndex=0)
    {
        $itemPages[$pageCount] = array();
        $maxRows               = $this->getMaxRows();

        $blankRow = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $blankRow[self::ROW_BILL_ITEM_ID]               = -1;   //id
        $blankRow[self::ROW_BILL_ITEM_ROW_IDX]          = null; //row index
        $blankRow[self::ROW_BILL_ITEM_DESCRIPTION]      = null; //description
        $blankRow[self::ROW_BILL_ITEM_LEVEL]            = 0;    //level
        $blankRow[self::ROW_BILL_ITEM_TYPE]             = self::ROW_TYPE_BLANK;//type
        $blankRow[self::ROW_BILL_ITEM_UNIT]             = null;
        $blankRow[self::ROW_BILL_ITEM_RATE]             = null;
        $blankRow[self::ROW_BILL_ITEM_MARKUP]             = null;
        $blankRow[self::ROW_BILL_ITEM_TOTAL_QTY]        = null;
        $blankRow[self::ROW_BILL_ITEM_TOTAL_COST]       = null;
        $blankRow[self::ROW_BILL_ITEM_MULTI_RATE]       = false;

        array_push($itemPages[$pageCount], $blankRow);

        $rowCount = 1;

        $occupiedRows = Utilities::justify($tradeInfo['description'], $this->MAX_CHARACTERS);

        if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
        {
            $oneLineDesc = $occupiedRows[0];
            $occupiedRows = new SplFixedArray(1);
            $occupiedRows[0] = $oneLineDesc;
        }

        foreach($occupiedRows as $occupiedRow)
        {
            $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
            $row[self::ROW_BILL_ITEM_ID]            = -1;//id
            $row[self::ROW_BILL_ITEM_ROW_IDX]       = null;//row index
            $row[self::ROW_BILL_ITEM_DESCRIPTION]   = $occupiedRow;//description
            $row[self::ROW_BILL_ITEM_LEVEL]         = 0;//level
            $row[self::ROW_BILL_ITEM_TYPE]          = self::ROW_TYPE_ELEMENT;//type
            $row[self::ROW_BILL_ITEM_UNIT]          = null;
            $row[self::ROW_BILL_ITEM_RATE]          = null;
            $row[self::ROW_BILL_ITEM_MARKUP]           = null;
            $row[self::ROW_BILL_ITEM_TOTAL_QTY]     = null;
            $row[self::ROW_BILL_ITEM_TOTAL_COST]    = null;
            $row[self::ROW_BILL_ITEM_MULTI_RATE]    = false;

            array_push($itemPages[$pageCount], $row);

            unset($row);
        }

        //blank row
        array_push($itemPages[$pageCount], $blankRow);

        $rowCount += count($occupiedRows)+1;//plus one blank row

        $itemIndex    = 1;

        foreach($items as $x => $item)
        {
            $occupiedRows = ($items[$x]['type'] == ScheduleOfRateItem::TYPE_NOID) ? Utilities::justifyHtmlString($items[$x]['description'], (strtoupper($items[$x]['description']) == $items[$x]['description']) ? $this->MAX_CHARACTERS - 10 : $this->MAX_CHARACTERS) : Utilities::justify($items[$x]['description'], (strtoupper($items[$x]['description']) == $items[$x]['description']) ? $this->MAX_CHARACTERS - 10 : $this->MAX_CHARACTERS);

            if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
            {
                $oneLineDesc = $occupiedRows[0];
                $occupiedRows = new SplFixedArray(1);
                $occupiedRows[0] = $oneLineDesc;
            }

            $rowCount += count($occupiedRows);

            if($rowCount <= $maxRows)
            {
                foreach($occupiedRows as $key => $occupiedRow)
                {
                    if($key == 0 && $item['type'] != ScheduleOfRateItem::TYPE_HEADER && $item['type'] != ScheduleOfRateItem::TYPE_NOID)
                    {
                        $counterIndex++;
                    }

                    $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                    $row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0 && $item['type'] != ScheduleOfRateItem::TYPE_HEADER && $item['type'] != ScheduleOfRateItem::TYPE_NOID) ? $counterIndex : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL] = $item['level'];
                    $row[self::ROW_BILL_ITEM_TYPE] =  $item['type'];

                    if($key+1 == $occupiedRows->count() && $item['type'] != ScheduleOfRateItem::TYPE_HEADER && $item['type'] != ScheduleOfRateItem::TYPE_NOID)
                    {
                        $row[self::ROW_BILL_ITEM_ID]    = $item['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT]  = $item['uom_symbol'];
                        $row[self::ROW_BILL_ITEM_RATE]  = ($item['multi-rate']) ? 0 : self::gridCurrencyRoundingFormat($item['rate-final_value']);
                        $row[self::ROW_BILL_ITEM_MARKUP]        = self::gridCurrencyRoundingFormat($item['item_markup-final_value']);
                        $row[self::ROW_BILL_ITEM_TOTAL_QTY]     = self::gridCurrencyRoundingFormat($item['total_qty']);
                        $row[self::ROW_BILL_ITEM_TOTAL_COST]    = self::gridCurrencyRoundingFormat($item['total_cost']);
                        $row[self::ROW_BILL_ITEM_MULTI_RATE]    = ($item['multi-rate']) ? true : false;
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID]    = null;
                        $row[self::ROW_BILL_ITEM_UNIT]  = null;
                        $row[self::ROW_BILL_ITEM_RATE]  = null;
                        $row[self::ROW_BILL_ITEM_MARKUP]       = null;
                        $row[self::ROW_BILL_ITEM_TOTAL_QTY]    = null;
                        $row[self::ROW_BILL_ITEM_TOTAL_COST]   = null;
                        $row[self::ROW_BILL_ITEM_MULTI_RATE]   = false;

                        if ( $key+1 == $occupiedRows->count() && $item['type'] == ScheduleOfRateItem::TYPE_NOID )
                        {
                            $row[self::ROW_BILL_ITEM_UNIT] = $item['uom_symbol'];//unit
                        }
                    }

                    array_push($itemPages[$pageCount], $row);

                    unset($row);
                }

                //blank row
                array_push($itemPages[$pageCount], $blankRow);

                $rowCount++;//plus one blank row;
                $itemIndex++;
                unset($items[$x], $occupiedRows);
            }
            else
            {
                unset($occupiedRows);

                $pageCount++;
                $this->generateItemPages($items, $tradeInfo, $pageCount, $itemPages, $counterIndex);
                break;
            }
        }
    }

    protected function setOrientationAndSize($orientation = false, $pageFormat = false)
    {
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
    }

    public function setPageFormat( $pageFormat )
    {
        $this->pageFormat = $pageFormat;
    }

    protected function generatePageFormat($format)
    {
        $width = 595;

        $height = 800;

        return $pf = array(
            'page_format' => self::PAGE_FORMAT_A4,
            'minimum-font-size' => $this->fontSize,
            'width' => $width,
            'height' => $height,
            'pdf_margin_top' => 8,
            'pdf_margin_right' => 8,
            'pdf_margin_bottom' => 3,
            'pdf_margin_left' => 8
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

}