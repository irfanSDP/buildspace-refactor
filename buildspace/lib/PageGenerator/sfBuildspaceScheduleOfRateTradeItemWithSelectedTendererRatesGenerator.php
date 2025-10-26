<?php

class sfBuildspaceScheduleOfRateTradeItemWithSelectedTendererRatesGenerator extends sfBuildspaceBQMasterFunction
{

    public $pageTitle;
    public $billItemIds;
    public $fontSize;
    public $headSettings;

    public $scheduleOfRate;
    public $scheduleOfRateTrade;

    const TOTAL_BILL_ITEM_PROPERTY      = 11;
    const ROW_BILL_ITEM_MARKUP          = 7;
    const ROW_BILL_ITEM_TOTAL_QTY       = 8;
    const ROW_BILL_ITEM_TOTAL_COST      = 9;
    const ROW_BILL_ITEM_MULTI_RATE      = 10;

    public function __construct($project = false, $scheduleOfRate, $scheduleOfRateTrade, $tradeItemIds, $billItemIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo                 = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $this->project             = $project;
        $this->tradeItemIds        = $tradeItemIds;
        $this->billItemIds         = $billItemIds;
        $this->scheduleOfRate      = $scheduleOfRate;
        $this->scheduleOfRateTrade = $scheduleOfRateTrade;
        $this->pageTitle           = $pageTitle;
        $this->currency            = $this->project->MainInformation->Currency;
        $this->descriptionFormat   = $descriptionFormat;

        $this->setOrientationAndSize();

        $this->printSettings  = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, TRUE);
        $this->fontSize       = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType       = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings   = $this->printSettings['headSettings'];

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $awardedCompanyId   = $this->project->TenderSetting->awarded_company_id;
        $tradeItemIds       = $this->tradeItemIds;
        $billItemIds        = $this->billItemIds;
        $pages              = array();
        $printPreviewItems  = array();
        $sorItemIds         = array();
        $tendererRates      = array();
        $tendererGrandTotal = array();
        $billCount          = 0;
        $totalPage          = 0;

        $tradeIdToDescription       = array();
        $billElementIdToDescription = array();

        $formulatedColumnConstants = array(
            BillItem::FORMULATED_COLUMN_RATE,
            BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE
        );

        if ( ! empty($billItemIds) )
        {
            $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.grand_total, p.grand_total_quantity, p.level, p.priority, e.priority AS element_priority, p.lft, uom.symbol AS uom_symbol, ifc.relation_id
			FROM ".BillItemTable::getInstance()->getTableName()." c
			JOIN ".BillItemTable::getInstance()->getTableName()." p ON c.lft BETWEEN p.lft AND p.rgt
			LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." AS uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
			JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS bifc ON c.id = bifc.relation_id
			JOIN ".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
			JOIN ".BillElementTable::getInstance()->getTableName()." AS e ON p.element_id = e.id
			JOIN ".ProjectStructureTable::getInstance()->getTableName()." AS s ON e.project_structure_id = s.id
			WHERE c.id IN (".implode(', ', $billItemIds).") AND ifc.relation_id IN (".implode(',', $tradeItemIds).")
			AND s.root_id = ".$this->project->id."
			AND c.root_id = p.root_id AND c.element_id = p.element_id
			AND bifc.column_name = '".BillItem::FORMULATED_COLUMN_RATE."'
			AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
			AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
			AND e.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND s.deleted_at IS NULL
			ORDER BY e.priority, p.priority, p.lft, p.level ASC");

            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $items as $item )
            {
                $sorItemIds[$item['relation_id']] = $item['relation_id'];
            }

            $stmt = $this->pdo->prepare("SELECT bifc.relation_id, bifc.column_name, bifc.final_value, bifc.linked, bifc.has_build_up
			FROM ".ProjectStructureTable::getInstance()->getTableName()." AS s
			JOIN ".BillElementTable::getInstance()->getTableName()." AS e ON s.id = e.project_structure_id
			JOIN ".BillItemTable::getInstance()->getTableName()." i  ON i.element_id = e.id
			JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS bifc ON i.id = bifc.relation_id
			JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS bifc2 ON bifc.relation_id = bifc2.relation_id
			JOIN ".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS ifc ON bifc2.schedule_of_rate_item_formulated_column_id = ifc.id
			WHERE s.root_id = ".$this->project->id."
			AND ifc.relation_id IN (".implode(', ', $sorItemIds).") AND bifc.column_name <> '".BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT."'
			AND s.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
			AND e.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bifc2.deleted_at IS NULL
			ORDER BY ifc.relation_id ASC");

            $stmt->execute();

            $formulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /*
            * select bills
            */
            $stmt = $this->pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft FROM ".ProjectStructureTable::getInstance()->getTableName()." AS s JOIN
			".BillElementTable::getInstance()->getTableName()." AS be ON s.id = be.project_structure_id JOIN
			".BillItemTable::getInstance()->getTableName()." AS bi ON be.id = bi.element_id JOIN
			".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS bifc ON bi.id = bifc.relation_id JOIN
			".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
			WHERE s.root_id = ".$this->project->id." AND ifc.relation_id IN (".implode(', ', $sorItemIds).")
			AND bifc.column_name = '".BillItem::FORMULATED_COLUMN_RATE."'
			AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
			AND s.deleted_at IS NULL ORDER BY s.lft ASC");

            $stmt->execute();

            $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT tc.company_id, r.bill_item_id, r.rate, r.grand_total
			FROM ".TenderBillItemRateTable::getInstance()->getTableName()." r
			JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON r.tender_company_id = tc.id
			WHERE tc.project_structure_id = ".$this->project->id. " AND tc.company_id = ".$awardedCompanyId." AND tc.show IS TRUE");

            $stmt->execute();

            $tendererRateRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($tendererRateRecords as $record)
            {
                if(!array_key_exists($record['company_id'], $tendererRates))
                {
                    $tendererRates[$record['company_id']]      = array();
                    $tendererGrandTotal[$record['company_id']] = array();
                }

                if(!array_key_exists($record['bill_item_id'], $tendererRates[$record['company_id']]))
                {
                    $tendererRates[$record['company_id']][$record['bill_item_id']]      = 0;
                    $tendererGrandTotal[$record['company_id']][$record['bill_item_id']] = 0;
                }

                $tendererRates[$record['company_id']][$record['bill_item_id']]      = $record['rate'];
                $tendererGrandTotal[$record['company_id']][$record['bill_item_id']] = $record['grand_total'];

                unset($record);
            }

            // get schedule of rate trade item's information
            $stmt = $this->pdo->prepare("SELECT t.id, t.description, t.root_id, t.lft, t.priority, t.level
			FROM ".ScheduleOfRateItemTable::getInstance()->getTableName()." t
			WHERE t.id IN (".implode(', ', $sorItemIds).") AND t.trade_id = ".$this->scheduleOfRateTrade->id." AND t.deleted_at IS NULL
			ORDER BY t.root_id, t.lft, t.priority, t.level ASC");

            $stmt->execute();

            $tradeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $tradeItems as $tradeItem )
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

                $tradeIdToDescription[$tradeItem['id']] = $tradeItem['description'];

                if(!array_key_exists($tradeItem['id'], $printPreviewItems))
                {
                    $printPreviewItems[$tradeItem['id']] = array();
                }

                if(!array_key_exists($tradeItem['id'], $pages))
                {
                    $pages[$tradeItem['id']] = array();
                }

                foreach($formulatedColumnConstants as $formulatedColumnConstant)
                {
                    $tradeItemInfo[$formulatedColumnConstant.'-value']        = '';
                    $tradeItemInfo[$formulatedColumnConstant.'-final_value']  = 0;
                    $tradeItemInfo[$formulatedColumnConstant.'-linked']       = false;
                    $tradeItemInfo[$formulatedColumnConstant.'-has_formula']  = false;
                    $tradeItemInfo[$formulatedColumnConstant.'-has_build_up'] = false;
                }

                foreach($bills as $bill)
                {
                    $stmt = $this->pdo->prepare("SELECT DISTINCT be.id, be.description, be.priority FROM
					".BillElementTable::getInstance()->getTableName()." AS be JOIN
					".BillItemTable::getInstance()->getTableName()." AS bi ON be.id = bi.element_id JOIN
					".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS bifc ON bi.id = bifc.relation_id JOIN
					".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
					WHERE be.project_structure_id = ".$bill['id']." AND ifc.relation_id = ".$tradeItem['id']."
					AND bifc.column_name = '".BillItem::FORMULATED_COLUMN_RATE."'
					AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
					ORDER BY be.priority ASC");

                    $stmt->execute();

                    $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach($elements as $element)
                    {
                        $itemPages = array();

                        $result = array(
                            'id'               => 'bill-'.$bill['id'].'-elem'.$element['id'].'-billCount-'.$billCount,
                            'description'      => $bill['title']." > ".$element['description'],
                            'type'             => -1,
                            'level'            => 0,
                            'uom_id'           => -1,
                            'uom_symbol'       => '',
                            'rate-value'       => 0,
                            'rate-final_value' => 0,
                        );

                        $billElementIdToDescription[$result['id']] = $result['description'];

                        foreach($formulatedColumnConstants as $formulatedColumnConstant)
                        {
                            $result[$formulatedColumnConstant.'-value']       = '';
                            $result[$formulatedColumnConstant.'-final_value'] = 0;
                            $result[$formulatedColumnConstant.'-linked']      = false;
                            $result[$formulatedColumnConstant.'-has_formula'] = false;
                        }

                        if(!array_key_exists($result['id'], $printPreviewItems[$tradeItem['id']]))
                        {
                            $printPreviewItems[$tradeItem['id']][$result['id']] = array();
                        }

                        $billItem = array( 'id' => - 1 );

                        foreach($items as $k => $item)
                        {
                            if($billItem['id'] != $item['id'] && $item['relation_id'] == $tradeItem['id'] && $item['element_id'] == $element['id'])
                            {
                                $grandTotal = number_format(0, 2, '.', '');

                                if(array_key_exists($awardedCompanyId, $tendererGrandTotal) and array_key_exists($item['id'], $tendererGrandTotal[$awardedCompanyId]))
                                {
                                    $grandTotal = $tendererGrandTotal[$awardedCompanyId][$item['id']];
                                }

                                $billItem['id']                   = $item['id'].'-billCount-'.$billCount;
                                $billItem['description']          = $item['description'];
                                $billItem['type']                 = $item['type'];
                                $billItem['grand_total']          = $grandTotal;
                                $billItem['grand_total_quantity'] = $item['grand_total_quantity'];
                                $billItem['level']                = $item['level'];
                                $billItem['uom_symbol']           = $item['uom_id'] > 0 ? $item['uom_symbol'] : '';

                                foreach($formulatedColumnConstants as $formulatedColumnConstant)
                                {
                                    $billItem[$formulatedColumnConstant.'-value']        = '';
                                    $billItem[$formulatedColumnConstant.'-final_value']  = 0;
                                    $billItem[$formulatedColumnConstant.'-linked']       = false;
                                    $billItem[$formulatedColumnConstant.'-has_formula']  = false;
                                    $billItem[$formulatedColumnConstant.'-has_build_up'] = false;
                                }

                                foreach($formulatedColumns as $key => $formulatedColumn)
                                {
                                    if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                                    {
                                        $rate = number_format(0, 2, '.', '');

                                        if(array_key_exists($awardedCompanyId, $tendererRates) and array_key_exists($item['id'], $tendererRates[$awardedCompanyId]))
                                        {
                                            $rate = $tendererRates[$awardedCompanyId][$item['id']];
                                        }

                                        $columnName                            = $formulatedColumn['column_name'];
                                        $billItem[$columnName.'-value']        = $rate;
                                        $billItem[$columnName.'-final_value']  = $rate;
                                        $billItem[$columnName.'-linked']       = false;
                                        $billItem[$columnName.'-has_formula']  = false;
                                        $billItem[$columnName.'-has_build_up'] = false;
                                    }
                                    else if($formulatedColumn['relation_id'] == $item['id'])
                                    {
                                        $columnName                            = $formulatedColumn['column_name'];
                                        $billItem[$columnName.'-value']        = $formulatedColumn['final_value'];
                                        $billItem[$columnName.'-final_value']  = $formulatedColumn['final_value'];
                                        $billItem[$columnName.'-linked']       = $formulatedColumn['linked'];
                                        $billItem[$columnName.'-has_formula']  = false;
                                        $billItem[$columnName.'-has_build_up'] = $formulatedColumn['has_build_up'];

                                        unset($formulatedColumn, $formulatedColumns[$key]);
                                    }
                                }

                                array_push($printPreviewItems[$tradeItem['id']][$result['id']], $billItem);

                                unset($items[$k], $item);
                            }
                        }

                        if ( ! empty($printPreviewItems[$tradeItem['id']][$result['id']]) )
                        {
                            $this->generateItemPages($printPreviewItems[$tradeItem['id']][$result['id']], $result, 1, $itemPages);

                            $page = array(
                                'description' => $result['description'],
                                'item_pages'  => SplFixedArray::fromArray($itemPages)
                            );

                            $totalPage+= count($itemPages);

                            $pages[$tradeItem['id']][$result['id']] = $page;
                        }
                    }

                    $billCount++;
                }

                unset($tradeItemInfo);
            }
        }

        $this->totalPage                  = $totalPage;
        $this->billElementIdToDescription = $billElementIdToDescription;
        $this->tradeIdToDescription       = $tradeIdToDescription;

        return $pages;
    }

    public function generateItemPages(Array $items, $tradeInfo, $pageCount, &$itemPages, $counterIndex = 0)
    {
        $itemPages[$pageCount] = array();
        $maxRows               = $this->getMaxRows();

        $blankRow                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $blankRow[self::ROW_BILL_ITEM_ID]          = - 1; //id
        $blankRow[self::ROW_BILL_ITEM_ROW_IDX]     = null; //row index
        $blankRow[self::ROW_BILL_ITEM_DESCRIPTION] = null; //description
        $blankRow[self::ROW_BILL_ITEM_LEVEL]       = 0; //level
        $blankRow[self::ROW_BILL_ITEM_TYPE]        = self::ROW_TYPE_BLANK; //type
        $blankRow[self::ROW_BILL_ITEM_UNIT]        = null;
        $blankRow[self::ROW_BILL_ITEM_RATE]        = null;
        $blankRow[self::ROW_BILL_ITEM_MARKUP]      = null;
        $blankRow[self::ROW_BILL_ITEM_TOTAL_QTY]   = null;
        $blankRow[self::ROW_BILL_ITEM_TOTAL_COST]  = null;

        array_push($itemPages[$pageCount], $blankRow);

        $rowCount  = 1;
        $itemIndex = 1;

        foreach($items as $x => $item)
        {
            $occupiedRows = ($items[$x]['type'] == BillItem::TYPE_ITEM_HTML_EDITOR or $items[$x]['type'] == BillItem::TYPE_NOID) ? Utilities::justifyHtmlString($items[$x]['description'], $this->MAX_CHARACTERS) : Utilities::justify($items[$x]['description'], $this->MAX_CHARACTERS);

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
                    if($key == 0 && $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID)
                    {
                        $counterIndex++;
                    }

                    $row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
                    $row[self::ROW_BILL_ITEM_ROW_IDX]     = ( $key == 0 && $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID ) ? $counterIndex : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL]       = $item['level'];
                    $row[self::ROW_BILL_ITEM_TYPE]        = $item['type'];
                    $row[self::ROW_BILL_ITEM_ID]          = null;
                    $row[self::ROW_BILL_ITEM_UNIT]        = null;
                    $row[self::ROW_BILL_ITEM_RATE]        = null;
                    $row[self::ROW_BILL_ITEM_MARKUP]      = null;
                    $row[self::ROW_BILL_ITEM_TOTAL_QTY]   = null;
                    $row[self::ROW_BILL_ITEM_TOTAL_COST]  = null;

                    if($key+1 == $occupiedRows->count() && $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID)
                    {
                        $row[self::ROW_BILL_ITEM_ID]         = $item['id']; //only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT]       = $item['uom_symbol'];
                        $row[self::ROW_BILL_ITEM_RATE]       = self::gridCurrencyRoundingFormat($item['rate-final_value']);
                        $row[self::ROW_BILL_ITEM_MARKUP]     = self::gridCurrencyRoundingFormat($item['markup_percentage-final_value']);
                        $row[self::ROW_BILL_ITEM_TOTAL_QTY]  = self::gridCurrencyRoundingFormat($item['grand_total_quantity']);
                        $row[self::ROW_BILL_ITEM_TOTAL_COST] = self::gridCurrencyRoundingFormat($item['grand_total']);
                    }
                    else if ( $key+1 == $occupiedRows->count() && $item['type'] == BillItem::TYPE_NOID )
                    {
                        $row[self::ROW_BILL_ITEM_UNIT] = $item['uom_symbol'];//unit
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

    protected function setOrientationAndSize()
    {
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
    }

    public function setPageFormat( $pageFormat )
    {
        $this->pageFormat = $pageFormat;
    }

    protected function generatePageFormat()
    {
        $width  = 595;
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

} 