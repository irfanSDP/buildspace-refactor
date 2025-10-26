<?php

class sfBuildspaceResourceTradeItemGenerator extends sfBuildspaceBQMasterFunction
{
    public $pageTitle;
    public $billItemIds;
    public $fontSize;
    public $headSettings;

    public $resource;

    const TOTAL_BILL_ITEM_PROPERTY      = 10;
    const ROW_BILL_ITEM_WASTAGE         = 7;
    const ROW_BILL_ITEM_TOTAL_QTY       = 8;
    const ROW_BILL_ITEM_TOTAL_COST      = 9;

    public function __construct($project = false, $resourceId, $tradeItemIds, $billItemIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo          = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $this->project      = $project;
        $this->tradeItemIds = $tradeItemIds;
        $this->billItemIds  = $billItemIds;

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
        $pages        = array();
        $tradeItemIds = $this->tradeItemIds;
        $billItemIds  = $this->billItemIds;
        $data         = array();

        $totalPage = 0;
        $itemPages = array();

        $resourceIdToDescription = array();
        $billElementIdToDescription = array();

        $billCount       = 1;

        $formulatedColumnConstants = array(
            BillBuildUpRateItem::FORMULATED_COLUMN_RATE,
            BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY,
            BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE,
        );

        if ( count($billItemIds) > 0 )
        {
            $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, uom.symbol AS uom_symbol, p.grand_total, p.grand_total_quantity, bur.resource_item_library_id, p.level, p.priority, p.lft
            FROM ".BillBuildUpRateResourceTable::getInstance()->getTableName()." AS r
            JOIN ".BillBuildUpRateItemTable::getInstance()->getTableName()." AS bur ON bur.build_up_rate_resource_id = r.id AND r.deleted_at IS NULL
            JOIN ".BillItemTable::getInstance()->getTableName()." c ON bur.bill_item_id = c.id AND bur.deleted_at IS NULL
            JOIN ".BillItemTable::getInstance()->getTableName()." p ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." AS uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON p.element_id = e.id
            JOIN ".ProjectStructureTable::getInstance()->getTableName()." s ON e.project_structure_id = s.id
            WHERE c.id IN (".implode(', ', $billItemIds).") AND s.root_id = ".$this->project->id."
            AND c.root_id = p.root_id AND c.element_id = p.element_id
            AND r.resource_library_id = ".$this->resource['id']." AND bur.resource_item_library_id IS NOT NULL
            AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
            AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL
            ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /*
             * select elements
             */
            $elementQuery = $this->pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority FROM
            ".BillElementTable::getInstance()->getTableName()." AS e JOIN
            ".BillItemTable::getInstance()->getTableName()." AS i ON i.element_id = e.id JOIN
            ".BillBuildUpRateResourceTable::getInstance()->getTableName()." AS r ON r.bill_item_id = i.id JOIN
            ".BillBuildUpRateItemTable::getInstance()->getTableName()." AS bur ON bur.build_up_rate_resource_id = r.id
            WHERE i.id IN (".implode(',', $billItemIds).") AND e.project_structure_id = :bill_id
            AND r.resource_library_id = ".$this->resource['id']." AND bur.resource_item_library_id = :resourceItemId
            AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND bur.deleted_at IS NULL ORDER BY e.priority ASC");

            $formulatedColumnQuery = $this->pdo->prepare("SELECT bur.bill_item_id, bur.uom_id, uom.symbol AS uom_symbol, ifc.column_name, ifc.value, ifc.final_value, ifc.linked
            FROM ".BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName()." AS ifc
            JOIN ".BillBuildUpRateItemTable::getInstance()->getTableName()." AS bur ON ifc.relation_id = bur.id
            LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." AS uom ON bur.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN ".BillBuildUpRateResourceTable::getInstance()->getTableName()." AS r ON bur.build_up_rate_resource_id = r.id
            JOIN ".BillItemTable::getInstance()->getTableName()." AS i ON r.bill_item_id = i.id
            JOIN ".BillElementTable::getInstance()->getTableName()." AS e ON i.element_id = e.id
            JOIN ".ProjectStructureTable::getInstance()->getTableName()." AS s ON e.project_structure_id = s.id
            WHERE i.id IN (".implode(',', $billItemIds).") AND s.root_id = ".$this->project->id."
            AND r.resource_library_id = ".$this->resource['id']." AND bur.resource_item_library_id = :resourceItemId
            AND ifc.column_name NOT IN ('".BillBuildUpRateItem::FORMULATED_COLUMN_NUMBER."', '".BillBuildUpRateItem::FORMULATED_COLUMN_CONSTANT."')
            AND s.deleted_at IS NULL AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND ifc.deleted_at IS NULL AND bur.deleted_at IS NULL ORDER BY bur.bill_item_id");

            $billQuery = $this->pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft FROM ".ProjectStructureTable::getInstance()->getTableName()." AS s JOIN
            ".BillElementTable::getInstance()->getTableName()." AS e ON e.project_structure_id = s.id JOIN
            ".BillItemTable::getInstance()->getTableName()." AS i ON i.element_id = e.id JOIN
            ".BillBuildUpRateResourceTable::getInstance()->getTableName()." AS r ON r.bill_item_id = i.id JOIN
            ".BillBuildUpRateItemTable::getInstance()->getTableName()." AS bur ON bur.build_up_rate_resource_id = r.id
            WHERE i.id IN (".implode(',', $billItemIds).") AND s.root_id = ".$this->project->id."
            AND r.resource_library_id = ".$this->resource['id']." AND bur.resource_item_library_id = :resourceItemId
            AND s.deleted_at IS NULL AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND bur.deleted_at IS NULL ORDER BY s.lft ASC");

            // get resource item's information and ordering
            $resourceItemsQuery = $this->pdo->prepare("SELECT DISTINCT ri.id, ri.description, ri.priority FROM ".ResourceItemTable::getInstance()->getTableName()." ri
            WHERE ri.id IN (".implode(', ', $tradeItemIds).") ORDER BY ri.priority");

            $resourceItemsQuery->execute();

            $resourceItems = $resourceItemsQuery->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $resourceItems as $resourceItem )
            {
                $totalCostAndQuantity = BillItemTable::calculateTotalForResourceAnalysis($this->resource['id'], $resourceItem['id'], null, false);

                if(!array_key_exists($resourceItem['id'], $data))
                {
                    $data[$resourceItem['id']] = array();
                }

                if(!array_key_exists($resourceItem['id'], $pages))
                {
                    $pages[$resourceItem['id']] = array();
                }

                $resourceIdToDescription[$resourceItem['id']] = $resourceItem['description'];

                $resourceItemInfo = array(
                    'id'          => 'tradeItem-'.$resourceItem['id'],
                    'description' => $resourceItem['description'],
                    'type'        => 'tradeItem',
                    'level'       => 0,
                    'uom_id'      => -1,
                    'uom_symbol'  => ''
                );

                foreach($formulatedColumnConstants as $formulatedColumnConstant)
                {
                    $resourceItemInfo[$formulatedColumnConstant.'-value']        = '';
                    $resourceItemInfo[$formulatedColumnConstant.'-final_value']  = 0;
                    $resourceItemInfo[$formulatedColumnConstant.'-linked']       = false;
                    $resourceItemInfo[$formulatedColumnConstant.'-has_formula']  = false;
                    $resourceItemInfo[$formulatedColumnConstant.'-has_build_up'] = false;
                }

                /*
                * get rate and wastage from build up rate item
                */

                $formulatedColumnQuery->execute(array('resourceItemId' => $resourceItem['id']));

                $formulatedColumnRecords = $formulatedColumnQuery->fetchAll(PDO::FETCH_ASSOC);

                /*
                 * select bills
                 */
                $billQuery->execute(array('resourceItemId' => $resourceItem['id']));

                $bills = $billQuery->fetchAll(PDO::FETCH_ASSOC);

                $buildUpRateItemId = null;
                $sumTotalQuantity  = 0;
                $sumTotalCost      = 0;

                $formulatedColumns = array();

                foreach($formulatedColumnRecords as $k => $formulatedColumn)
                {
                    if(!array_key_exists($formulatedColumn['bill_item_id'], $formulatedColumns))
                    {
                        $formulatedColumns[$formulatedColumn['bill_item_id']] = array();
                    }

                    $columnName = $formulatedColumn['column_name'];

                    $formulatedColumns[$formulatedColumn['bill_item_id']][] = array(
                        'column_name'              => $columnName,
                        'uom_symbol'               => $formulatedColumn['uom_symbol'],
                        $columnName.'-value'       => $formulatedColumn['final_value'],
                        $columnName.'-final_value' => $formulatedColumn['final_value'],
                        $columnName.'-linked'      => $formulatedColumn['linked']
                    );

                    unset($formulatedColumn, $formulatedColumnRecords[$k]);
                }

                foreach($bills as $bill)
                {
                    $elementQuery->execute(array(
                        'bill_id'        => $bill['id'],
                        'resourceItemId' => $resourceItem['id'],
                    ));

                    $elements = $elementQuery->fetchAll(PDO::FETCH_ASSOC);

                    foreach($elements as $element)
                    {
                        $itemPages = array();

                        $result = array(
                            'id'          => 'bill-'.$bill['id'].'-elem'.$element['id'].'-billcount'.$billCount,
                            'description' => $bill['title']." > ".$element['description'],
                            'type'        => -1,
                            'level'       => 0,
                            'uom_id'      => -1,
                            'uom_symbol'  => ''
                        );

                        $billElementIdToDescription[$result['id']] = $result['description'];

                        foreach($formulatedColumnConstants as $formulatedColumnConstant)
                        {
                            $result[$formulatedColumnConstant.'-value']        = '';
                            $result[$formulatedColumnConstant.'-final_value']  = 0;
                            $result[$formulatedColumnConstant.'-linked']       = false;
                            $result[$formulatedColumnConstant.'-has_formula']  = false;
                            $result[$formulatedColumnConstant.'-has_build_up'] = false;
                        }

                        if(!array_key_exists($result['id'], $data[$resourceItem['id']]))
                        {
                            $data[$resourceItem['id']][$result['id']] = array();
                        }

                        $billItem['id'] = -1;

                        foreach($items as $key => $item)
                        {
                            if($billItem['id'] != $item['id'] && $item['element_id'] == $element['id'] && $item['resource_item_library_id'] == $resourceItem['id'])
                            {
                                $billItem['id']                   = $item['id'].'-billcount'.$billCount;
                                $billItem['description']          = $item['description'];
                                $billItem['type']                 = $item['type'];
                                $billItem['grand_total']          = $item['grand_total'];
                                $billItem['grand_total_quantity'] = $item['grand_total_quantity'];
                                $billItem['level']                = $item['level'];

                                foreach($formulatedColumnConstants as $formulatedColumnConstant)
                                {
                                    $billItem[$formulatedColumnConstant.'-value']        = '';
                                    $billItem[$formulatedColumnConstant.'-final_value']  = 0;
                                    $billItem[$formulatedColumnConstant.'-linked']       = false;
                                    $billItem[$formulatedColumnConstant.'-has_formula']  = false;
                                    $billItem[$formulatedColumnConstant.'-has_build_up'] = false;
                                }

                                if(array_key_exists($item['id'], $formulatedColumns))
                                {
                                    foreach($formulatedColumns[$item['id']] as $formulatedColumn)
                                    {
                                        $columnName                           = $formulatedColumn['column_name'];
                                        $billItem['uom_symbol']               = $formulatedColumn['uom_symbol'];

                                        $billItem[$columnName.'-value']       = $formulatedColumn[$columnName.'-value'];
                                        $billItem[$columnName.'-final_value'] = $formulatedColumn[$columnName.'-final_value'];
                                        $billItem[$columnName.'-linked']      = $formulatedColumn[$columnName.'-linked'];
                                    }

                                    unset($formulatedColumn, $formulatedColumns[$item['id']]);
                                }

                                $totalQuantity = 0;
                                $totalCost     = 0;

                                if(array_key_exists($item['id'], $totalCostAndQuantity) and $item['grand_total_quantity'] != '' and $item['grand_total_quantity'] != 0 and $item['type'] != BillItem::TYPE_HEADER and $item['type'] != BillItem::TYPE_HEADER_N and $item['type'] != BillItem::TYPE_NOID)
                                {
                                    $totalCost     = $totalCostAndQuantity[$item['id']]['total_cost'];
                                    $totalQuantity = $totalCostAndQuantity[$item['id']]['total_quantity'];

                                    unset($totalCostAndQuantity[$item['id']]);
                                }

                                $billItem['total_qty'] = $totalQuantity;
                                $billItem['total_cost'] = $totalCost;

                                $sumTotalQuantity += $totalQuantity;
                                $sumTotalCost += $totalCost;

                                array_push($data[$resourceItem['id']][$result['id']], $billItem);

                                unset($item, $items[$key]);
                            }
                        }

                        $this->generateItemPages($data[$resourceItem['id']][$result['id']], $result, 1, $itemPages);

                        $page = array(
                            'description' => $result['description'],
                            'item_pages' => SplFixedArray::fromArray($itemPages)
                        );

                        $totalPage+= count($itemPages);

                        $pages[$resourceItem['id']][$result['id']] = $page;

                        unset($result);
                    }

                    unset($elements);

                    $billCount++;
                }

                unset($bills);
            }

            unset($resourceItems);
        }
        else
        {
            $result = array(
                'description' => ''
            );

            $this->generateItemPages(array(), $result, 1, $itemPages);

            $page = array(
                'description' => '',
                'item_pages' => SplFixedArray::fromArray($itemPages)
            );

            $totalPage+= count($itemPages);

            $pages[0][0] = $page;
        }

        $this->totalPage = $totalPage;
        $this->billElementIdToDescription = $billElementIdToDescription;
        $this->resourceIdToDescription = $resourceIdToDescription;

        return $pages;
    }

    public function generateItemPages(Array $items, $tradeInfo, $pageCount, &$itemPages, $counterIndex = 0)
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

        array_push($itemPages[$pageCount], $blankRow);

        $rowCount  = 1;
        $itemIndex = 1;

        foreach($items as $x => $item)
        {
            $occupiedRows = ($items[$x]['type'] == BillItem::TYPE_NOID) ? Utilities::justifyHtmlString($items[$x]['description'], (strtoupper($items[$x]['description']) == $items[$x]['description']) ? $this->MAX_CHARACTERS - 10 : $this->MAX_CHARACTERS) : Utilities::justify($items[$x]['description'], (strtoupper($items[$x]['description']) == $items[$x]['description']) ? $this->MAX_CHARACTERS - 10 : $this->MAX_CHARACTERS);

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

                    $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                    $row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0 && $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID) ? $counterIndex : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL] = $item['level'];
                    $row[self::ROW_BILL_ITEM_TYPE] =  $item['type'];

                    if($key+1 == $occupiedRows->count() && $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID)
                    {
                        $row[self::ROW_BILL_ITEM_ID]    = $item['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT]  = $item['uom_symbol'];
                        $row[self::ROW_BILL_ITEM_RATE]  = self::gridCurrencyRoundingFormat($item['rate-final_value']);
                        $row[self::ROW_BILL_ITEM_WASTAGE]       = self::gridCurrencyRoundingFormat($item['wastage-final_value']);
                        $row[self::ROW_BILL_ITEM_TOTAL_QTY]     = self::gridCurrencyRoundingFormat($item['total_qty']);
                        $row[self::ROW_BILL_ITEM_TOTAL_COST]    = self::gridCurrencyRoundingFormat($item['total_cost']);

                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID]    = null;
                        $row[self::ROW_BILL_ITEM_UNIT]  = null;
                        $row[self::ROW_BILL_ITEM_RATE]  = null;
                        $row[self::ROW_BILL_ITEM_WASTAGE]      = null;
                        $row[self::ROW_BILL_ITEM_TOTAL_QTY]    = null;
                        $row[self::ROW_BILL_ITEM_TOTAL_COST]   = null;

                        if ( $key+1 == $occupiedRows->count() && $item['type'] == BillItem::TYPE_NOID )
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