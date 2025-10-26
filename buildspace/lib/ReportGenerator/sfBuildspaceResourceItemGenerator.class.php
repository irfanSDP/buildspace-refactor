<?php

class sfBuildspaceResourceItemGenerator extends sfBuildspaceBQMasterFunction
{
    public $pageTitle;
    public $tradeIds;
    public $fontSize;
    public $headSettings;

    public $resource;

    const TOTAL_BILL_ITEM_PROPERTY   = 12;
    const ROW_BILL_ITEM_WASTAGE      = 7;
    const ROW_BILL_ITEM_TOTAL_QTY    = 8;
    const ROW_BILL_ITEM_TOTAL_COST   = 9;
    const ROW_BILL_ITEM_CLAIM_QTY    = 10;
    const ROW_BILL_ITEM_CLAIM_AMOUNT = 11;

    public function __construct($project = false, $resourceId, $tradeIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo         = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->project     = $project;

        $this->tradeIds    = $tradeIds;
        $sth = $this->pdo->prepare("SELECT id, name FROM ".ResourceTable::getInstance()->getTableName()." WHERE id = ".$resourceId);
        $sth->execute();

        $this->resource = $sth->fetch(PDO::FETCH_ASSOC);

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
        $pages                     = array();
        $tradeItemIds              = $this->tradeIds;
        $sumTotalQuantity          = 0;
        $sumTotalCost              = 0;
        $data                      = array();
        $results                   = array();
        $formulatedColumnConstants = array(BillBuildUpRateItem::FORMULATED_COLUMN_RATE, BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE);

        $totalPage = 0;

        if ( count($tradeItemIds) > 0 )
        {
            $claimQuantities = ($this->project->PostContract->exists()) ? PostContractStandardClaimTable::getClaimQuantities($this->project->PostContract) : array();
            $totalResourceClaimQuantities = array();

            $stmt = $this->pdo->prepare("SELECT DISTINCT bur.id, bur.resource_item_library_id, t.resource_trade_library_id, bur.bill_item_id FROM
            ".BillBuildUpRateResourceTable::getInstance()->getTableName()." AS r JOIN
            ".BillBuildUpRateResourceTradeTable::getInstance()->getTableName()." AS t ON t.build_up_rate_resource_id = r.id JOIN
            ".BillBuildUpRateItemTable::getInstance()->getTableName()." AS bur ON bur.build_up_rate_resource_trade_id = t.id JOIN
            ".BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName()." AS ifc ON bur.id = ifc.relation_id JOIN
            ".BillItemTable::getInstance()->getTableName()." AS i ON bur.bill_item_id = i.id JOIN
            ".BillElementTable::getInstance()->getTableName()." AS e ON i.element_id = e.id JOIN
            ".ProjectStructureTable::getInstance()->getTableName()." AS s ON e.project_structure_id = s.id
            WHERE bur.resource_item_library_id IN (".implode(', ', $tradeItemIds).") AND s.root_id = ".$this->project->id." AND r.resource_library_id = ".$this->resource['id']."
            AND bur.resource_item_library_id IS NOT NULL
            AND ifc.column_name = '".BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY."' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0
            AND r.deleted_at IS NULL AND t.deleted_at IS NULL AND bur.deleted_at IS NULL AND ifc.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL");

            $stmt->execute();

            $buildUpRateItemWithResourceItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($buildUpRateItemWithResourceItemIds) > 0)
            {
                $tradeIds               = array();
                $buildUpRateItemIds     = array();
                $resourceItemLibraryIds = array();

                foreach($buildUpRateItemWithResourceItemIds as $record)
                {
                    $tradeIds[]                                                     = $record['resource_trade_library_id'];
                    $buildUpRateItemIds[$record['resource_trade_library_id']][]     = $record['id'];
                    $resourceItemLibraryIds[$record['resource_trade_library_id']][] = $record['resource_item_library_id'];

                    $totalResourceClaimQuantities[$record['resource_item_library_id']] = ($totalResourceClaimQuantities[$record['resource_item_library_id']] ?? 0) + ($claimQuantities[$record['resource_item_library_id']][$record['bill_item_id']] ?? 0);
                }

                // query resource trade in order to get it's ordering and description
                $stmt = $this->pdo->prepare("SELECT t.id, t.description, t.priority FROM ".ResourceTradeTable::getInstance()->getTableName()." t
                WHERE id IN (".implode(',', array_unique($tradeIds)).") AND t.deleted_at IS NULL ORDER BY t.priority");

                $stmt->execute();

                $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ( $trades as $trade )
                {
                    $itemPages = array();

                    $totalCostAndQuantityByResourceItems = ResourceItemTable::calculateTotalForResourceAnalysis(array_unique($resourceItemLibraryIds[$trade['id']]), $this->resource['id'], $this->project->id);

                    if(!array_key_exists($trade['id'], $data))
                    {
                        $data[$trade['id']] = array();
                    }

                    $tradeInformation = array(
                        'id'            => 'trade-' . $trade['id'],
                        'description'   => $trade['description'],
                        'uom_symbol'    => '',
                        'type'          => 0,
                        'total_qty'     => 0,
                        'total_cost'    => 0,
                        'multi-rate'    => false,
                        'multi-wastage' => false,
                    );

                    foreach($formulatedColumnConstants as $formulatedColumnConstant)
                    {
                        $tradeInformation[$formulatedColumnConstant.'-value']       = '';
                        $tradeInformation[$formulatedColumnConstant.'-final_value'] = 0;
                        $tradeInformation[$formulatedColumnConstant.'-linked']      = false;
                        $tradeInformation[$formulatedColumnConstant.'-has_formula'] = false;
                    }

                    $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type, p.uom_id, p.level, p.priority, p.lft, uom.symbol AS uom_symbol
                    FROM ".ResourceItemTable::getInstance()->getTableName()." c
                    JOIN ".ResourceItemTable::getInstance()->getTableName()." p
                    ON c.lft BETWEEN p.lft AND p.rgt
                    LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                    WHERE c.root_id = p.root_id AND c.type <> ".ResourceItem::TYPE_HEADER."
                    AND c.id IN (".implode(',', array_unique($resourceItemLibraryIds[$trade['id']])).")
                    AND c.deleted_at IS NULL AND p.deleted_at IS NULL
                    ORDER BY p.root_id, p.priority, p.lft, p.level ASC");

                    $stmt->execute();

                    $resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $stmt = $this->pdo->prepare("SELECT bur.resource_item_library_id, ifc.column_name, ifc.final_value FROM
                    ".BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName()." AS ifc JOIN
                    ".BillBuildUpRateItemTable::getInstance()->getTableName()." AS bur ON ifc.relation_id = bur.id JOIN
                    ".BillItemTable::getInstance()->getTableName()." AS i ON bur.bill_item_id = i.id JOIN
                    ".BillElementTable::getInstance()->getTableName()." AS e ON i.element_id = e.id JOIN
                    ".ProjectStructureTable::getInstance()->getTableName()." AS s ON e.project_structure_id = s.id
                    WHERE s.root_id = ".$this->project->id." AND bur.id IN (".implode(',', array_unique($buildUpRateItemIds[$trade['id']])).")
                    AND (ifc.column_name = '".BillBuildUpRateItem::FORMULATED_COLUMN_RATE."' OR ifc.column_name = '".BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE."')
                    AND ifc.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
                    AND i.deleted_at IS NULL AND e.deleted_at IS NULL
                    GROUP BY bur.resource_item_library_id, ifc.column_name, ifc.final_value
                    ORDER BY bur.resource_item_library_id");

                    $stmt->execute();

                    $formulatedColumnNames = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                    $stmt->execute();
                    $formulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach($resourceItems as $key => $item)
                    {
                        $multiRate     = false;
                        $multiWastage  = false;
                        $totalQuantity = 0;
                        $totalCost     = 0;

                        foreach($formulatedColumnConstants as $formulatedColumnConstant)
                        {
                            $item[$formulatedColumnConstant.'-value']       = '';
                            $item[$formulatedColumnConstant.'-final_value'] = number_format(0, 2, '.', '');
                            $item[$formulatedColumnConstant.'-linked']      = false;
                            $item[$formulatedColumnConstant.'-has_formula'] = false;
                        }

                        if(array_key_exists($item['id'], $formulatedColumnNames))
                        {
                            $columnNames = array_count_values($formulatedColumnNames[$item['id']]);

                            if(array_key_exists(ResourceItem::FORMULATED_COLUMN_RATE, $columnNames) && $columnNames[ResourceItem::FORMULATED_COLUMN_RATE] > 1)
                            {
                                $item[ResourceItem::FORMULATED_COLUMN_RATE.'-value'] = '';
                                $item[ResourceItem::FORMULATED_COLUMN_RATE.'-final_value'] = 0;
                                $multiRate = true;
                            }
                            else
                            {
                                foreach($formulatedColumns as $formulatedColumn)
                                {
                                    $columnName = $formulatedColumn['column_name'];
                                    if($formulatedColumn['resource_item_library_id'] == $item['id'] and $columnName == ResourceItem::FORMULATED_COLUMN_RATE)
                                    {
                                        $finalValue = $formulatedColumn['final_value'] ? $formulatedColumn['final_value'] : number_format(0, 2, '.', '');
                                        $item[$columnName.'-value'] = $finalValue;
                                        $item[$columnName.'-final_value'] = $finalValue;

                                        break 1;
                                    }
                                }
                            }

                            if(array_key_exists(ResourceItem::FORMULATED_COLUMN_WASTAGE, $columnNames) && $columnNames[ResourceItem::FORMULATED_COLUMN_WASTAGE] > 1)
                            {
                                $item[ResourceItem::FORMULATED_COLUMN_WASTAGE.'-value'] = '';
                                $item[ResourceItem::FORMULATED_COLUMN_WASTAGE.'-final_value'] = 0;
                                $multiWastage = true;
                            }
                            else
                            {
                                foreach($formulatedColumns as $formulatedColumn)
                                {
                                    $columnName = $formulatedColumn['column_name'];
                                    if($formulatedColumn['resource_item_library_id'] == $item['id'] and $columnName == ResourceItem::FORMULATED_COLUMN_WASTAGE)
                                    {
                                        $finalValue = $formulatedColumn['final_value'] ? $formulatedColumn['final_value'] : number_format(0, 2, '.', '');
                                        $item[$columnName.'-value'] = $finalValue;
                                        $item[$columnName.'-final_value'] = $finalValue;

                                        break 1;
                                    }
                                }
                            }
                        }

                        if($item['type'] == ResourceItem::TYPE_WORK_ITEM && array_key_exists($item['id'], $totalCostAndQuantityByResourceItems))
                        {
                            $totalCost = $totalCostAndQuantityByResourceItems[$item['id']]['total_cost'];
                            $totalQuantity = $totalCostAndQuantityByResourceItems[$item['id']]['total_quantity'];
                        }

                        $item['total_qty']     = $totalQuantity;
                        $item['total_cost']    = $totalCost;
                        $item['multi-rate']    = $multiRate;
                        $item['multi-wastage'] = $multiWastage;

                        $item['claim_quantity'] = ($totalResourceClaimQuantities[$item['id']] ?? 0);
                        $item['claim_amount'] = $item['claim_quantity'] * $item[BillBuildUpRateItem::FORMULATED_COLUMN_RATE.'-final_value'];

                        $sumTotalQuantity += $totalQuantity;
                        $sumTotalCost     += $totalCost;

                        array_push($data[$trade['id']], $item);

                        unset($resourceItems[$key]);
                    }

                    $this->generateItemPages($data[$trade['id']], $tradeInformation, 1, $itemPages);

                    $page = array(
                        'description' => $tradeInformation['description'],
                        'item_pages' => SplFixedArray::fromArray($itemPages)
                    );

                    $totalPage+= count($itemPages);

                    $pages[$trade['id']] = $page;

                    unset($resourceItems, $tradeInformation);
                }
            }
        }
        else
        {
            $tradeInformation = array(
                'description' => ''
            );

            $itemPages = array();

            $this->generateItemPages(array(), $tradeInformation, 1, $itemPages);

            $page = array(
                'description' => '',
                'item_pages' => SplFixedArray::fromArray($itemPages)
            );

            $totalPage+= count($itemPages);

            $pages[0] = $page;
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
        $blankRow[self::ROW_BILL_ITEM_WASTAGE]          = null;
        $blankRow[self::ROW_BILL_ITEM_TOTAL_QTY]        = null;
        $blankRow[self::ROW_BILL_ITEM_TOTAL_COST]       = null;
        $blankRow[self::ROW_BILL_ITEM_CLAIM_QTY]        = null;
        $blankRow[self::ROW_BILL_ITEM_CLAIM_AMOUNT]     = null;

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
            $row[self::ROW_BILL_ITEM_WASTAGE]       = null;
            $row[self::ROW_BILL_ITEM_TOTAL_QTY]     = null;
            $row[self::ROW_BILL_ITEM_TOTAL_COST]    = null;
            $row[self::ROW_BILL_ITEM_CLAIM_QTY]     = null;
            $row[self::ROW_BILL_ITEM_CLAIM_AMOUNT]  = null;

            array_push($itemPages[$pageCount], $row);

            unset($row);
        }

        //blank row
        array_push($itemPages[$pageCount], $blankRow);

        $rowCount += count($occupiedRows)+1;//plus one blank row
        $itemIndex = 1;

        foreach($items as $x => $item)
        {
            $occupiedRows = ($items[$x]['type'] == ResourceItem::TYPE_NOID) ? Utilities::justifyHtmlString($items[$x]['description'], (strtoupper($items[$x]['description']) == $items[$x]['description']) ? $this->MAX_CHARACTERS - 10 : $this->MAX_CHARACTERS) : Utilities::justify($items[$x]['description'], (strtoupper($items[$x]['description']) == $items[$x]['description']) ? $this->MAX_CHARACTERS - 10 : $this->MAX_CHARACTERS);

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
                    if($key == 0 && $item['type'] != ResourceItem::TYPE_HEADER && $item['type'] != ResourceItem::TYPE_NOID)
                    {
                        $counterIndex++;
                    }

                    $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                    $row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0 && $item['type'] != ResourceItem::TYPE_HEADER && $item['type'] != ResourceItem::TYPE_NOID) ? $counterIndex : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL] = $item['level'];
                    $row[self::ROW_BILL_ITEM_TYPE] =  $item['type'];

                    if($key+1 == $occupiedRows->count() && $item['type'] != ResourceItem::TYPE_HEADER && $item['type'] != ResourceItem::TYPE_NOID)
                    {
                        $row[self::ROW_BILL_ITEM_ID]    = $item['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT]  = $item['uom_symbol'];
                        $row[self::ROW_BILL_ITEM_RATE]  = self::gridCurrencyRoundingFormat($item['rate-final_value']);
                        $row[self::ROW_BILL_ITEM_WASTAGE]       = self::gridCurrencyRoundingFormat($item['wastage-final_value']);
                        $row[self::ROW_BILL_ITEM_TOTAL_QTY]     = self::gridCurrencyRoundingFormat($item['total_qty']);
                        $row[self::ROW_BILL_ITEM_TOTAL_COST]    = self::gridCurrencyRoundingFormat($item['total_cost']);
                        $row[self::ROW_BILL_ITEM_CLAIM_QTY]     = self::gridCurrencyRoundingFormat($item['claim_quantity']);
                        $row[self::ROW_BILL_ITEM_CLAIM_AMOUNT]  = self::gridCurrencyRoundingFormat($item['claim_amount']);
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID]    = null;
                        $row[self::ROW_BILL_ITEM_UNIT]  = null;
                        $row[self::ROW_BILL_ITEM_RATE]  = null;
                        $row[self::ROW_BILL_ITEM_WASTAGE]      = null;
                        $row[self::ROW_BILL_ITEM_TOTAL_QTY]    = null;
                        $row[self::ROW_BILL_ITEM_TOTAL_COST]   = null;
                        $row[self::ROW_BILL_ITEM_CLAIM_QTY]    = null;
                        $row[self::ROW_BILL_ITEM_CLAIM_AMOUNT] = null;

                        if ( $key+1 == $occupiedRows->count() && $item['type'] == ResourceItem::TYPE_NOID )
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