<?php

class sfBuildspaceBQContractorPageGenerator extends sfBuildspaceBQPageGenerator
{
    public $tenderCompany;

    public function __construct(ProjectStructure $bill, $element, $tenderCompany, $withNotListedItem = false)
    {
        $this->tenderCompany       = $tenderCompany;
        $this->withNotListedItem   = $withNotListedItem;

        parent::__construct( $bill, $element );
    }

    protected function queryBillStructure()
    {
        $pdo = $this->pdo;
        $bill = $this->bill;
        $billElement = $this->billElement;
        $billStructure = array();

        $elementSqlPart = $billElement instanceof BillElement ? "AND e.id = ".$billElement->id : null;

        $stmt = $pdo->prepare("SELECT e.id, e.description FROM ".BillElementTable::getInstance()->getTableName()." e
        WHERE e.project_structure_id = ".$bill->id." ".$elementSqlPart." AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($this->withNotListedItem)
        {
            $sqlFieldCond = '(
                CASE c.type WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.' THEN 
                    CASE inl.description IS NULL 
                    WHEN TRUE 
                    THEN null 
                    ELSE inl.description 
                    END
                ELSE 
                    c.description 
                    END
                ) AS description,
                (CASE c.type WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.' THEN 
                    CASE 
                    inl_uom.symbol IS NULL 
                    WHEN TRUE 
                    THEN null 
                    ELSE inl_uom.symbol 
                    END
                ELSE 
                    uom.symbol 
                    END
                ) AS uom,
                (CASE c.type WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.' THEN 
                    CASE 
                    inl_uom.id IS NULL 
                    WHEN TRUE 
                    THEN null 
                    ELSE inl_uom.id 
                    END
                ELSE 
                    uom.id 
                    END
                ) AS uom_id';

            $whereClause = '';

            $nlTable = "LEFT JOIN ".TenderBillItemNotListedTable::getInstance()->getTableName()." inl ON inl.bill_item_id = c.id AND inl.tender_company_id = ".$this->tenderCompany->id."
                LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." inl_uom ON inl.uom_id = inl_uom.id AND inl_uom.deleted_at IS NULL";
        }
        else
        {
            $sqlFieldCond = 'c.description, uom.symbol as uom, uom.id as uom_id';

            $whereClause = 'AND c.type <> '.BillItem::TYPE_ITEM_NOT_LISTED;

            $nlTable = '';
        }

        foreach($elements as $element)
        {
            $result = array(
                'id'          => $element['id'],
                'description' => $element['description'],
                'items'       => array()
            );

            $stmt = $pdo->prepare("SELECT c.id, c.element_id, c.type, COALESCE(tenderer_rate.grand_total, 0) AS grand_total_after_markup, c.bill_ref_element_no, c.bill_ref_page_no, c.bill_ref_char, c.lft, c.rgt, c.root_id, c.level, ".$sqlFieldCond."
                FROM ".BillItemTable::getInstance()->getTableName()." c ".$nlTable."
                LEFT JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." tenderer_rate ON tenderer_rate.bill_item_id = c.id AND tenderer_rate.tender_company_id = ".$this->tenderCompany->id."
                LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE c.element_id = ".$element['id']." AND c.deleted_at IS NULL
                AND c.project_revision_deleted_at IS NULL ".$whereClause." ORDER BY c.priority, c.lft, c.level");

            $stmt->execute();

            $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result['items'] = $billItems;

            array_push($billStructure, $result);

            unset($element, $billItems);
        }

        return $billStructure;
    }

    protected function getLumpSumPercent()
    {
        $pdo  = $this->pdo;
        $bill = $this->bill;

        $stmt = $pdo->prepare("SELECT i.id, c.percentage, rate.tender_company_id FROM ".TenderBillItemLumpSumPercentageTable::getInstance()->getTableName()." c
            JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON rate.id = c.tender_bill_item_rate_id
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON (rate.bill_item_id = i.id AND i.type = ".BillItem::TYPE_ITEM_LUMP_SUM_PERCENT.")
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            WHERE e.project_structure_id = ".$bill->id." AND i.project_revision_deleted_at IS NULL
            AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

        $lumpSumpPercents = array();

        foreach($records as $billItemId => $tenderCompanyRecords)
        {
            $lumpSumpPercents[$billItemId] = array();

            foreach($tenderCompanyRecords as $tenderCompanyRecord)
            {
                $lumpSumpPercents[$billItemId][$tenderCompanyRecord['tender_company_id']] = $tenderCompanyRecord['percentage'];
            }
        }

        return $lumpSumpPercents;
    }

    protected function getItemQuantities()
    {
        $pdo           = $this->pdo;
        $bill          = $this->bill;
        $tenderCompany = $this->tenderCompany;

        $billStructure   = $this->billStructure;
        $implodedItemIds = null;
        $result = array();

        foreach($billStructure as $element)
        {
            if(count($element['items']) == 0)
                continue;//we skip element with empty items

            $itemIds = Utilities::arrayValueRecursive('id', $element['items']);

            if(is_array($itemIds))
            {
                $implodedItemIds .= implode(',', $itemIds);
                $implodedItemIds .= ",";
            }
        }

        $implodedItemIds = rtrim($implodedItemIds, ",");

        if($this->printGrandTotalQty)
        {
            if($this->withNotListedItem)
            {
                $sqlFieldCond = '(
                    CASE i.type WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.' THEN
                        CASE COALESCE(SUM(inl_qty.final_value * columnSetting.quantity), 0)
                        WHEN 0
                        THEN 0
                        ELSE COALESCE(SUM(inl_qty.final_value * columnSetting.quantity), 0)
                        END
                    ELSE
                        COALESCE(i.grand_total_quantity, 0)
                        END
                    ) AS value';

                $nlTable = "LEFT JOIN ".TenderBillItemNotListedTable::getInstance()->getTableName()." inl ON inl.bill_item_id = i.id AND inl.tender_company_id = ".$tenderCompany->id."
                    LEFT JOIN ".TenderBillItemNotListedQuantityTable::getInstance()->getTableName()." inl_qty ON inl_qty.tender_bill_item_not_listed_id = inl.id
                    LEFT JOIN ".BillColumnSettingTable::getInstance()->getTableName()." columnSetting ON inl_qty.bill_column_setting_id = columnSetting.id";
            }
            else
            {
                $sqlFieldCond = 'COALESCE(i.grand_total_quantity, 0) AS value';

                $nlTable = "";
            }

            if ( ! empty($implodedItemIds) )
            {
                $stmt = $pdo->prepare("SELECT i.id, ".$sqlFieldCond." FROM ".BillItemTable::getInstance()->getTableName()." i ".$nlTable."
                    WHERE i.id IN (".$implodedItemIds.") AND i.deleted_at IS NULL GROUP BY i.id");

                $stmt->execute();

                $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                $result = $quantities;

                unset($quantities);
            }
            else
            {
                $result[] = 0;
            }
        }
        else
        {
            foreach($bill->BillColumnSettings as $column)
            {
                $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                if($this->withNotListedItem)
                {
                    $sqlFieldCond = '(
                    CASE i.type WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.' THEN
                        CASE COALESCE(inl_qty.final_value, 0)
                        WHEN 0
                        THEN 0
                        ELSE COALESCE(inl_qty.final_value, 0)
                        END
                    ELSE
                        COALESCE(fc.final_value, 0)
                        END
                    ) AS value';

                    $nlTable = "LEFT JOIN ".TenderBillItemNotListedTable::getInstance()->getTableName()." inl ON inl.bill_item_id = i.id AND inl.tender_company_id = ".$tenderCompany->id."
                    LEFT JOIN ".TenderBillItemNotListedQuantityTable::getInstance()->getTableName()." inl_qty ON inl_qty.tender_bill_item_not_listed_id = inl.id AND inl_qty.bill_column_setting_id = ".$column->id;
                }
                else
                {
                    $sqlFieldCond = 'COALESCE(fc.final_value, 0) AS value';

                    $nlTable = "";
                }

                if ( ! empty($implodedItemIds) )
                {
                    $stmt = $pdo->prepare("SELECT i.id, ".$sqlFieldCond." FROM ".BillItemTable::getInstance()->getTableName()." i ".$nlTable."
                    LEFT JOIN ".BillItemTypeReferenceTable::getInstance()->getTableName()." r ON r.bill_item_id = i.id  AND r.bill_column_setting_id = ".$column->id." AND r.include IS TRUE
                    LEFT JOIN ".BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName()." fc ON fc.relation_id = r.id AND fc.column_name = '".$quantityFieldName."'
                    WHERE i.id IN (".$implodedItemIds.") AND fc.deleted_at IS NULL AND r.deleted_at IS NULL");

                    $stmt->execute();

                    $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                    $result[$column->id] = $quantities;
                }
                else
                {
                    $result[$column->id] = 0;
                }
            }
        }

        return $result;
    }

    protected function getItemIncludeStatus()
    {
        $pdo = $this->pdo;
        $bill = $this->bill;

        $billStructure = $this->billStructure;
        $implodedItemIds = null;
        $result = array();

        foreach($billStructure as $idx => $element)
        {
            if(count($element['items']) == 0)
                continue;//we skip element with empty items

            $itemIds = Utilities::arrayValueRecursive('id', $element['items']);

            if(is_array($itemIds))
            {
                $implodedItemIds .= implode(',', $itemIds);
                $implodedItemIds .= ",";
            }
        }

        $implodedItemIds = rtrim($implodedItemIds, ",");

		foreach($this->bill->BillColumnSettings->toArray() as $column)
        {
        	if($this->withNotListedItem)
            {
                $nlTable = "LEFT JOIN ".TenderBillItemNotListedTable::getInstance()->getTableName()." inl ON inl.bill_item_id = r.id
                    LEFT JOIN ".TenderBillItemNotListedQuantityTable::getInstance()->getTableName()." inl_qty ON inl_qty.tender_bill_item_not_listed_id = inl.id AND inl_qty.bill_column_setting_id = ".$column['id'];
            }
            else
            {
                $nlTable = "";
            }
			
            if ( ! empty($implodedItemIds) )
            {
                $stmt = $this->pdo->prepare("SELECT r.bill_item_id, r.include FROM ".BillItemTypeReferenceTable::getInstance()->getTableName()." r ".$nlTable."
                WHERE r.bill_item_id IN (".$implodedItemIds.") AND r.bill_column_setting_id = ".$column['id']."
                AND r.deleted_at IS NULL");

                $stmt->execute();

                $includeStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $result[$column['id']] = $includeStatus;
            }
            else
            {
                $result[$column['id']] = null;
            }
        }

        return $result;
    }

    protected function getRatesAfterMarkup()
    {
        $pdo = $this->pdo;

        $bill = $this->bill;

        $tenderCompany = $this->tenderCompany;

        //Get Contractor Rates
        $stmt = $pdo->prepare("SELECT t.bill_item_id, COALESCE(t.rate, 0) AS value FROM ".TenderBillItemRateTable::getInstance()->getTableName()." t
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON t.bill_item_id = i.id
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            WHERE e.project_structure_id = ".$bill->id." AND t.tender_company_id = ".$tenderCompany->id."
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        $result = array_map('reset', $result);

        return $result;
    }

    public function generatePrimeCostRateRows($billItemId)
    {
        $primeCostRate = TenderBillItemPrimeCostRateTable::getByTenderCompanyAndBillItemId($billItemId, $this->tenderCompany->id, Doctrine_Core::HYDRATE_ARRAY);

        $header                                                     = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $header[self::ROW_BILL_ITEM_ID]                             = null;//id
        $header[self::ROW_BILL_ITEM_ROW_IDX]                        = null;//row index
        $header[self::ROW_BILL_ITEM_DESCRIPTION]                    = 'Rate Per No.';//description
        $header[self::ROW_BILL_ITEM_LEVEL]                          = -1;//level -1 means pc rate header
        $header[self::ROW_BILL_ITEM_TYPE]                           = self::ROW_TYPE_PC_RATE;//type
        $header[self::ROW_BILL_ITEM_UNIT]                           = null;//unit
        $header[self::ROW_BILL_ITEM_RATE]                           = null;//rate
        $header[self::ROW_BILL_ITEM_QTY_PER_UNIT]                   = null;//amount
        $header[self::ROW_BILL_ITEM_INCLUDE]                        = null;//include

        $supplyRateArr                                              = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $supplyRateArr[self::ROW_BILL_ITEM_ID]                      = null;//id
        $supplyRateArr[self::ROW_BILL_ITEM_ROW_IDX]                 = null;//row index
        $supplyRateArr[self::ROW_BILL_ITEM_DESCRIPTION]             = BillItem::ITEM_TYPE_PC_SUPPLIER_RATE_TEXT;//description
        $supplyRateArr[self::ROW_BILL_ITEM_LEVEL]                   = 0;//level -1 means pc rate header
        $supplyRateArr[self::ROW_BILL_ITEM_TYPE]                    = self::ROW_TYPE_PC_RATE;//type
        $supplyRateArr[self::ROW_BILL_ITEM_UNIT]                    = null;//unit
        $supplyRateArr[self::ROW_BILL_ITEM_RATE]                    = null;//rate
        $supplyRateArr[self::ROW_BILL_ITEM_QTY_PER_UNIT]            = $primeCostRate ? $primeCostRate['supply_rate'] : 0;//amount
        $supplyRateArr[self::ROW_BILL_ITEM_INCLUDE]                 = null;//include

        $wastageArr                                                 = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $wastageArr[self::ROW_BILL_ITEM_ID]                         = null;//id
        $wastageArr[self::ROW_BILL_ITEM_ROW_IDX]                    = null;//row index
        $wastageArr[self::ROW_BILL_ITEM_DESCRIPTION]                = 'Wastage';//description
        $wastageArr[self::ROW_BILL_ITEM_LEVEL]                      = 0;//level -1 means pc rate header
        $wastageArr[self::ROW_BILL_ITEM_TYPE]                       = self::ROW_TYPE_PC_RATE;//type
        $wastageArr[self::ROW_BILL_ITEM_UNIT]                       = null;//unit
        $wastageArr[self::ROW_BILL_ITEM_RATE]                       = $primeCostRate ? $primeCostRate['wastage_percentage'] : 0;//rate
        $wastageArr[self::ROW_BILL_ITEM_QTY_PER_UNIT]               = $primeCostRate ? $primeCostRate['wastage_amount'] : 0;//amount
        $wastageArr[self::ROW_BILL_ITEM_INCLUDE]                    = null;//include

        $labourForInstallationArr                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $labourForInstallationArr[self::ROW_BILL_ITEM_ID]           = null;//id
        $labourForInstallationArr[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
        $labourForInstallationArr[self::ROW_BILL_ITEM_DESCRIPTION]  = 'Labour for Installation';//description
        $labourForInstallationArr[self::ROW_BILL_ITEM_LEVEL]        = 0;//level -1 means pc rate header
        $labourForInstallationArr[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_PC_RATE;//type
        $labourForInstallationArr[self::ROW_BILL_ITEM_UNIT]         = null;//unit
        $labourForInstallationArr[self::ROW_BILL_ITEM_RATE]         = null;//rate
        $labourForInstallationArr[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $primeCostRate ? $primeCostRate['labour_for_installation'] : 0;//amount
        $labourForInstallationArr[self::ROW_BILL_ITEM_INCLUDE]      = null;//include

        $otherCostArr                                               = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $otherCostArr[self::ROW_BILL_ITEM_ID]                       = null;//id
        $otherCostArr[self::ROW_BILL_ITEM_ROW_IDX]                  = null;//row index
        $otherCostArr[self::ROW_BILL_ITEM_DESCRIPTION]              = 'Other Cost';//description
        $otherCostArr[self::ROW_BILL_ITEM_LEVEL]                    = 0;//level -1 means pc rate header
        $otherCostArr[self::ROW_BILL_ITEM_TYPE]                     = self::ROW_TYPE_PC_RATE;//type
        $otherCostArr[self::ROW_BILL_ITEM_UNIT]                     = null;//unit
        $otherCostArr[self::ROW_BILL_ITEM_RATE]                     = null;//rate
        $otherCostArr[self::ROW_BILL_ITEM_QTY_PER_UNIT]             = $primeCostRate ? $primeCostRate['other_cost'] : 0;//amount
        $otherCostArr[self::ROW_BILL_ITEM_INCLUDE]                  = null;//include

        $profitArr                                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $profitArr[self::ROW_BILL_ITEM_ID]                          = null;//id
        $profitArr[self::ROW_BILL_ITEM_ROW_IDX]                     = null;//row index
        $profitArr[self::ROW_BILL_ITEM_DESCRIPTION]                 = 'Profit';//description
        $profitArr[self::ROW_BILL_ITEM_LEVEL]                       = 0;//level -1 means pc rate header
        $profitArr[self::ROW_BILL_ITEM_TYPE]                        = self::ROW_TYPE_PC_RATE;//type
        $profitArr[self::ROW_BILL_ITEM_UNIT]                        = null;//unit
        $profitArr[self::ROW_BILL_ITEM_RATE]                        = $primeCostRate ? $primeCostRate['profit_percentage'] : 0;//rate
        $profitArr[self::ROW_BILL_ITEM_QTY_PER_UNIT]                = $primeCostRate ? $primeCostRate['profit_amount'] : 0;//amount
        $profitArr[self::ROW_BILL_ITEM_INCLUDE]                     = null;//include

        $totalArr                                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $totalArr[self::ROW_BILL_ITEM_ID]                           = null;//id
        $totalArr[self::ROW_BILL_ITEM_ROW_IDX]                      = null;//row index
        $totalArr[self::ROW_BILL_ITEM_DESCRIPTION]                  = 'Total';//description
        $totalArr[self::ROW_BILL_ITEM_LEVEL]                        = -2;//level -2 means pc rate total
        $totalArr[self::ROW_BILL_ITEM_TYPE]                         = self::ROW_TYPE_PC_RATE;//type
        $totalArr[self::ROW_BILL_ITEM_UNIT]                         = null;//unit
        $totalArr[self::ROW_BILL_ITEM_RATE]                         = null;//rate
        $totalArr[self::ROW_BILL_ITEM_QTY_PER_UNIT]                 = $primeCostRate ? $primeCostRate['total'] : 0;
        $totalArr[self::ROW_BILL_ITEM_INCLUDE]                      = null;//include

        $rows = new SplFixedArray(7);
        $rows->offsetSet(0, $header);
        $rows->offsetSet(1, $supplyRateArr);
        $rows->offsetSet(2, $wastageArr);
        $rows->offsetSet(3, $labourForInstallationArr);
        $rows->offsetSet(4, $otherCostArr);
        $rows->offsetSet(5, $profitArr);
        $rows->offsetSet(6, $totalArr);

        return $rows;
    }
}