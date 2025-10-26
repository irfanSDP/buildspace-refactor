<?php

class sfBuildspaceVOItemsWithClaimReportGenerator extends sfBuildspaceBQMasterFunction
{
    public $pageTitle;
    public $fontSize;
    public $headSettings;

    public $variationTotals;

    const CLAIM_PREFIX  = "Valuation No: ";

    const TOTAL_BILL_ITEM_PROPERTY  = 13;
    const ROW_NET                   = 9;
    const ROW_CLAIM_PREVIOUS        = 10;
    const ROW_CLAIM_WORKDONE        = 11;
    const ROW_CLAIM_CURRENT         = 12;

    public function __construct($project = false, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo      = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $this->project  = $project;

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
        $pages      = array();
        $data    = array();
        $voIds   = array();
        $itemIds = array();
        $variationTotals = array();

        $totalPage = 0;

        // get the claim item(s) that is currently got up to date claim
        $stmt = $this->pdo->prepare("SELECT DISTINCT i.id AS variation_order_item_id, vo.id as variation_order_id,
        CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
            THEN -1 * ABS(ci.current_amount)
            ELSE ci.current_amount
        END AS current_amount,
        CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
            THEN -1 * ABS(ci.current_percentage)
            ELSE ci.current_percentage
        END AS current_percentage,
        CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
            THEN -1 * ABS(ci.up_to_date_amount)
            ELSE ci.up_to_date_amount
        END AS up_to_date_amount,
        CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
            THEN -1 * ABS(ci.up_to_date_percentage)
            ELSE ci.up_to_date_percentage
        END AS up_to_date_percentage,
        CASE WHEN ((pcvoi.rate * pcvoi.addition_quantity) - (pcvoi.rate * pcvoi.omission_quantity) < 0)
            THEN -1 * ABS(pci.up_to_date_amount)
            ELSE pci.up_to_date_amount
        END AS previous_amount,
        CASE WHEN ((pcvoi.rate * pcvoi.addition_quantity) - (pcvoi.rate * pcvoi.omission_quantity) < 0)
            THEN -1 * ABS(pci.up_to_date_percentage)
            ELSE pci.up_to_date_percentage
        END AS previous_percentage
        FROM ".VariationOrderItemTable::getInstance()->getTableName()." i
        JOIN ".VariationOrderClaimTable::getInstance()->getTableName()." c ON i.variation_order_id = c.variation_order_id
        JOIN ".VariationOrderTable::getInstance()->getTableName()." vo ON (vo.id = c.variation_order_id AND vo.deleted_at IS NULL)
        JOIN ".VariationOrderClaimItemTable::getInstance()->getTableName()." ci ON ci.variation_order_claim_id = c.id AND ci.variation_order_item_id = i.id
        LEFT JOIN ".VariationOrderClaimTable::getInstance()->getTableName()." pc ON pc.variation_order_id = c.variation_order_id AND pc.revision = c.revision - 1
        LEFT JOIN ".VariationOrderClaimItemTable::getInstance()->getTableName()." pci ON pci.variation_order_claim_id = pc.id AND pci.variation_order_item_id = i.id
        LEFT JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " pcvoi ON pci.variation_order_item_id = pcvoi.id
        WHERE vo.project_structure_id = ".$this->project->id." AND ci.up_to_date_amount <> 0
        AND i.deleted_at IS NULL AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND ci.deleted_at IS NULL
        AND pc.deleted_at IS NULL AND pci.deleted_at IS NULL");

        $stmt->execute();
        $claimItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // assign item id(s) and claim id(s) into array in order to correctly query based on variation_order_id
        // for the query below
        foreach ( $claimItems as $claimItem )
        {
            $itemIds[$claimItem['variation_order_id']][] = $claimItem['variation_order_item_id'];
            $voIds[$claimItem['variation_order_id']]     = $claimItem['variation_order_id'];
        }

        if ( count($voIds) > 0 )
        {
            // get VO's information
            $stmt = $this->pdo->prepare("SELECT vo.id, vo.description FROM ".VariationOrderTable::getInstance()->getTableName()." vo
            WHERE vo.id IN (".implode(',', $voIds).") AND vo.project_structure_id = ".$this->project->id." AND vo.deleted_at IS NULL
            ORDER BY vo.priority");

            $stmt->execute();
            $variationOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // we will start first with variation order so that we can correctly separate item(s) by vos
            foreach ( $variationOrders as $voIdx => $variationOrder )
            {
                if ( ! isset($itemIds[$variationOrder['id']]) AND count($itemIds[$variationOrder['id']]) == 0 )
                {
                    continue;
                }

                $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.variation_order_id, p.description, p.type, p.lft, p.level, p.total_unit, p.rate,
                p.bill_ref, p.bill_item_id, p.omission_quantity, p.has_omission_build_up_quantity,
                p.addition_quantity, p.has_addition_build_up_quantity, uom.id AS uom_id, uom.symbol AS uom_symbol,
                p.priority, p.lft, p.level
                FROM ".VariationOrderItemTable::getInstance()->getTableName()." i
                JOIN ".VariationOrderItemTable::getInstance()->getTableName()." p ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
                JOIN ".VariationOrderTable::getInstance()->getTableName()." vo ON (p.variation_order_id = vo.id AND vo.deleted_at IS NULL)
                LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE vo.project_structure_id = ".$this->project->id." AND i.id IN (".implode(',', $itemIds[$variationOrder['id']]).")
                AND i.root_id = p.root_id AND i.type <> ".VariationOrderItem::TYPE_HEADER."
                ORDER BY p.priority, p.lft, p.level");

                $stmt->execute();
                $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // append variation order's table row header if there is item(s) available
                if ( count($variationOrderItems) > 0 )
                {
                    $voInformation = array(
                        'id'                             => "vo-{$variationOrder['id']}",
                        'description'                    => $variationOrder['description'],
                        'bill_ref'                       => '',
                        'total_unit'                     => '',
                        'bill_item_id'                   => -1,
                        'type'                           => (string) 0,
                        'uom_id'                         => '-1',
                        'uom_symbol'                     => '',
                        'updated_at'                     => '-',
                        'level'                          => 0,
                        'rate-value'                     => 0,
                        'omission_quantity-value'        => 0,
                        'has_omission_build_up_quantity' => false,
                        'addition_quantity-value'        => 0,
                        'has_addition_build_up_quantity' => false,
                        'previous_percentage-value'      => 0,
                        'previous_amount-value'          => 0,
                        'current_percentage-value'       => 0,
                        'current_amount-value'           => 0,
                        'up_to_date_percentage-value'    => 0,
                        'up_to_date_amount-value'        => 0,
                    );
                }

                if(!array_key_exists($variationOrder['id'], $variationTotals))
                {
                    $variationTotals[$variationOrder['id']] = array(
                        'net' => 0,
                        'previous_amount' => 0,
                        'workdone_amount' => 0,
                        'current_amount' => 0
                    );
                }

                if(!array_key_exists($variationOrder['id'], $data))
                {
                    $data[$variationOrder['id']] = array();
                }

                if(!array_key_exists($variationOrder['id'], $pages))
                {
                    $pages[$variationOrder['id']] = array();
                }

                foreach($variationOrderItems as $key => $variationOrderItem)
                {
                    $variationOrderItem['omission_quantity-value']     = $variationOrderItem['omission_quantity'];
                    $variationOrderItem['addition_quantity-value']     = $variationOrderItem['addition_quantity'];
                    $variationOrderItem['rate-value']                  = $variationOrderItem['rate'];
                    $variationOrderItem['type']                        = (string) $variationOrderItem['type'];
                    $variationOrderItem['uom_id']                      = $variationOrderItem['uom_id'] > 0 ? (string)$variationOrderItem['uom_id'] : '-1';
                    $variationOrderItem['uom_symbol']                  = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_symbol'] : '';

                    $variationOrderItem['previous_percentage-value']   = number_format(0,2,'.','');
                    $variationOrderItem['previous_amount-value']       = number_format(0,2,'.','');
                    $variationOrderItem['current_percentage-value']    = number_format(0,2,'.','');
                    $variationOrderItem['current_amount-value']        = number_format(0,2,'.','');
                    $variationOrderItem['up_to_date_percentage-value'] = number_format(0,2,'.','');
                    $variationOrderItem['up_to_date_amount-value']     = number_format(0,2,'.','');

                    foreach($claimItems as $claimItem)
                    {
                        if ($claimItem['variation_order_item_id'] == $variationOrderItem['id'])
                        {
                            $variationOrderItem['previous_percentage-value']   = $claimItem['previous_percentage'];
                            $variationOrderItem['previous_amount-value']       = $claimItem['previous_amount'];
                            $variationOrderItem['current_percentage-value']    = $claimItem['current_percentage'];
                            $variationOrderItem['current_amount-value']        = $claimItem['current_amount'];
                            $variationOrderItem['up_to_date_percentage-value'] = $claimItem['up_to_date_percentage'];
                            $variationOrderItem['up_to_date_amount-value']     = $claimItem['up_to_date_amount'];

                            unset($claimItem);
                        }
                    }

                    $omissionTotal = $variationOrderItem["omission_quantity-value"] * $variationOrderItem["rate-value"] * $variationOrderItem['total_unit'];
                    $additionTotal = $variationOrderItem["addition_quantity-value"] * $variationOrderItem["rate-value"] * $variationOrderItem['total_unit'];

                    $previousAmount = $variationOrderItem['previous_amount-value'];
                    $currentAmount  = $variationOrderItem['current_amount-value'];
                    $upToDateAmount = $variationOrderItem['up_to_date_amount-value'];

                    if($additionTotal - $omissionTotal < 0)
                    {
                        $previousAmount = abs($previousAmount) * -1;
                        $currentAmount  = abs($currentAmount) * -1;
                        $upToDateAmount = abs($upToDateAmount) * -1;
                    }

                    $variationTotals[$variationOrder['id']]['net']+= floatval(round(($additionTotal + $omissionTotal), 2));
                    $variationTotals[$variationOrder['id']]['previous_amount']+= floatval(round($previousAmount,2));
                    $variationTotals[$variationOrder['id']]['current_amount']+= floatval(round($currentAmount,2));
                    $variationTotals[$variationOrder['id']]['workdone_amount']+= floatval(round($upToDateAmount,2));
                    
                    $data[$variationOrder['id']][] = $variationOrderItem;

                    unset($variationOrderItem, $variationOrderItems[$key]);
                }

                $itemPages  = array();

                $this->generateItemPages($data[$variationOrder['id']], $voInformation, 1, $itemPages);

                $page = array(
                    'description' => $voInformation['description'],
                    'item_pages' => SplFixedArray::fromArray($itemPages)
                );

                $totalPage+= count($itemPages);

                $pages[$variationOrder['id']] = $page;

                unset($variationOrderItems);
            }

            unset($variationOrders, $itemIds, $voIds);
        }
        else
        {
            $itemPages = array();

            $this->generateItemPages(array(), null, 1, $itemPages);

            $page = array(
                'description' => "",
                'element_count' => 1,
                'item_pages' => SplFixedArray::fromArray($itemPages)
            );

            $totalPage+= count($itemPages);

            $pages[0] = $page;
        }

        $this->totalPage = $totalPage;
        $this->variationTotals = $variationTotals;

        return $pages;
    }

    public function generateItemPages(Array $voItems, $tradeInfo, $pageCount, &$itemPages)
    {
        $itemPages[$pageCount] = array();
        $maxRows               = self::getMaxRows();

        $blankRow                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $blankRow[self::ROW_BILL_ITEM_ID]           = -1;//id
        $blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
        $blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null;//description
        $blankRow[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
        $blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK;
        $blankRow[self::ROW_BILL_ITEM_UNIT]         = null;
        $blankRow[self::ROW_BILL_ITEM_RATE]         = null;
        $blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;
        $blankRow[self::ROW_BILL_ITEM_INCLUDE]      = null;
        $blankRow[self::ROW_NET]                    = null;
        $blankRow[self::ROW_CLAIM_PREVIOUS]         = null;
        $blankRow[self::ROW_CLAIM_WORKDONE]         = null;
        $blankRow[self::ROW_CLAIM_CURRENT]          = null;

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
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
            $row[self::ROW_BILL_ITEM_ID] = -1;//id
            $row[self::ROW_BILL_ITEM_ROW_IDX] = null;//row index
            $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;//description
            $row[self::ROW_BILL_ITEM_LEVEL] = 0;//level
            $row[self::ROW_BILL_ITEM_TYPE] = self::ROW_TYPE_ELEMENT;//type
            $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
            $row[self::ROW_BILL_ITEM_RATE] = null;//rate
            $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;
            $row[self::ROW_BILL_ITEM_INCLUDE] = null;//include
            $row[self::ROW_NET]               = null;
            $row[self::ROW_CLAIM_PREVIOUS]    = null;
            $row[self::ROW_CLAIM_WORKDONE]    = null;
            $row[self::ROW_CLAIM_CURRENT]     = null;

            array_push($itemPages[$pageCount], $row);

            unset($row);
        }

        //blank row
        array_push($itemPages[$pageCount], $blankRow);

        $rowCount += $occupiedRows->count()+1;//plus one blank row

        $counterIndex = 0;//display item's index in BQ

        foreach($voItems as $x => $voItem)
        {
            $descriptionBillRef = (strlen($voItem['bill_ref'])) ? '<b>('.$voItem['bill_ref'].') - </b>' : '';
            $occupiedRows = Utilities::justify($descriptionBillRef.$voItems[$x]['description'], $this->MAX_CHARACTERS);

            if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
            {
                $oneLineDesc = $occupiedRows[0];
                $occupiedRows = new SplFixedArray(1);
                $occupiedRows[0] = $oneLineDesc;
            }

            $rowCount += $occupiedRows->count();

            if($rowCount <= $maxRows)
            {
                foreach($occupiedRows as $key => $occupiedRow)
                {
                    if($key == 0 && $voItem['type'] != VariationOrderItem::TYPE_HEADER)
                    {
                        $counterIndex++;
                    }

                    $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                    $row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0 && $voItem['type'] != VariationOrderItem::TYPE_HEADER) ? Utilities::generateCharFromNumber($counterIndex, $this->printSettings['layoutSetting']['includeIandO']) : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL] = $voItem['level'];
                    $row[self::ROW_BILL_ITEM_TYPE]  = $voItem['type'];

                    if($key+1 == $occupiedRows->count() && $voItem['type'] != VariationOrderItem::TYPE_HEADER)
                    {
                        $omissionTotal = $voItem["omission_quantity-value"] * $voItem["rate-value"] * $voItem['total_unit'];
                        $additionTotal = $voItem["addition_quantity-value"] * $voItem["rate-value"] * $voItem['total_unit'];

                        $row[self::ROW_BILL_ITEM_ID]   = $voItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT] = $voItem['uom_symbol'];
                        $row[self::ROW_BILL_ITEM_RATE] = $voItem['rate'];
                        $row[self::ROW_NET]            = $additionTotal - $omissionTotal;

                        $row[self::ROW_CLAIM_PREVIOUS] = array(
                            'percentage' => (array_key_exists('previous_percentage-value', $voItem)) ? $voItem['previous_percentage-value'] : 0,
                            'amount' => (array_key_exists('previous_amount-value', $voItem)) ? $voItem['previous_amount-value'] : 0
                        );

                        $row[self::ROW_CLAIM_WORKDONE] = array(
                            'percentage' => (array_key_exists('up_to_date_percentage-value', $voItem)) ? $voItem['up_to_date_percentage-value'] : 0,
                            'amount' => (array_key_exists('up_to_date_amount-value', $voItem)) ? $voItem['up_to_date_amount-value'] : 0
                        );

                        $row[self::ROW_CLAIM_CURRENT]  = array(
                            'percentage' => (array_key_exists('current_percentage-value', $voItem)) ? $voItem['current_percentage-value'] : 0,
                            'amount' => (array_key_exists('current_amount-value', $voItem)) ? $voItem['current_amount-value'] : 0
                        );
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID]   = null;
                        $row[self::ROW_BILL_ITEM_UNIT] = null;
                        $row[self::ROW_BILL_ITEM_RATE] = null;
                        $row[self::ROW_NET]               = null;
                        $row[self::ROW_CLAIM_PREVIOUS]    = null;
                        $row[self::ROW_CLAIM_WORKDONE]    = null;
                        $row[self::ROW_CLAIM_CURRENT]     = null;
                    }

                    array_push($itemPages[$pageCount], $row);

                    unset($row);
                }

                //blank row
                array_push($itemPages[$pageCount], $blankRow);

                $rowCount++;//plus one blank row;

                unset($voItems[$x], $occupiedRows);
            }
            else
            {
                unset($occupiedRows);

                $pageCount++;
                $this->generateItemPages($voItems, $tradeInfo, $pageCount, $itemPages);
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
        return array(
            'page_format' => self::PAGE_FORMAT_A4,
            'minimum-font-size' => $this->fontSize,
            'width' => 595,
            'height' => 800,
            'pdf_margin_top' => 8,
            'pdf_margin_right' => 10,
            'pdf_margin_bottom' => 3,
            'pdf_margin_left' => 10
        );
    }

    public function setMaxCharactersPerLine()
    {
        $this->MAX_CHARACTERS = 48;
    }

    public function getMaxRows()
    {
        return 77;
    }
}
