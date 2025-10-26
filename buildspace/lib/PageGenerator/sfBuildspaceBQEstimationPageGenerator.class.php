<?php

class sfBuildspaceBQEstimationPageGenerator extends sfBuildspaceBQPageGenerator
{
    public function __construct(ProjectStructure $bill, $element, $withNotListedItem = false)
    {
        $this->withNotListedItem   = $withNotListedItem;

        parent::__construct( $bill, $element );
    }

    protected function queryBillStructure()
    {
        $pdo = $this->pdo;
        $bill = $this->bill;
        $billElement = $this->billElement;
        $billStructure = [];

        $elementSqlPart = $billElement instanceof BillElement ? "AND e.id = ".$billElement->id : null;

        $stmt = $pdo->prepare("SELECT e.id, e.description FROM ".BillElementTable::getInstance()->getTableName()." e
        WHERE e.project_structure_id = ".$bill->id." ".$elementSqlPart." AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($this->withNotListedItem)
        {
            $whereClause = '';
        }
        else
        {
            $whereClause = 'AND c.type <> '.BillItem::TYPE_ITEM_NOT_LISTED;
        }

        foreach($elements as $element)
        {
            $result = [
                'id'          => $element['id'],
                'description' => $element['description'],
                'items'       => []
            ];

            $stmt = $pdo->prepare("SELECT c.id, c.element_id, c.type, c.lft, c.rgt, c.root_id,
                COALESCE(c.grand_total_after_markup, 0) AS grand_total_after_markup, c.bill_ref_element_no,
                c.bill_ref_page_no, c.bill_ref_char, c.level, c.description, uom.symbol as uom, uom.id as uom_id
                FROM ".BillItemTable::getInstance()->getTableName()." c
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


    protected function getRatesAfterMarkup()
    {
        $pdo = $this->pdo;
        $bill = $this->bill;
        $elementMarkupResults = [];

        $billMarkupSetting = $bill->BillMarkupSetting;

        $rateInfoColumns = [];

        if($bill->BillMarkupSetting->element_markup_enabled)
        {
            $stmt = $pdo->prepare("SELECT e.id, COALESCE(c.final_value, 0) as value FROM ".BillElementFormulatedColumnTable::getInstance()->getTableName()." c
                JOIN ".BillElementTable::getInstance()->getTableName()." e ON c.relation_id = e.id
                WHERE e.project_structure_id = ".$bill->id." AND c.column_name = '".BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE."'
                AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

            $stmt->execute();

            $elementMarkupResults = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
        }

        $stmt = $pdo->prepare("SELECT c.relation_id, i.element_id, c.column_name, COALESCE(c.final_value, 0) AS value FROM ".BillItemFormulatedColumnTable::getInstance()->getTableName()." c
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON c.relation_id = i.id
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            WHERE e.project_structure_id = ".$bill->id." AND (c.column_name = '".BillItem::FORMULATED_COLUMN_RATE."' OR c.column_name = '".BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE."')
            AND c.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($columns as $column)
        {
            $rateInfoColumns[$column['relation_id']]['markup_setting_info'] = array(
                'bill_markup_enabled'       => $billMarkupSetting->bill_markup_enabled,
                'bill_markup_percentage'    => $billMarkupSetting->bill_markup_percentage,
                'element_markup_enabled'    => $billMarkupSetting->element_markup_enabled,
                'element_markup_percentage' => array_key_exists($column['element_id'], $elementMarkupResults) ? $elementMarkupResults[$column['element_id']][0] : 0,
                'item_markup_enabled'       => $billMarkupSetting->item_markup_enabled,
                'rounding_type'             => $billMarkupSetting->rounding_type
            );

            $rateInfoColumns[$column['relation_id']][$column['column_name']] = $column['value'];
        }

        $result = [];

        foreach($rateInfoColumns as $itemId => $column)
        {
            $markupPercentage = array_key_exists(BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE, $column) ? $column[BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE] : 0;
            $rate = array_key_exists(BillItem::FORMULATED_COLUMN_RATE, $column) ? $column[BillItem::FORMULATED_COLUMN_RATE] : 0;

            $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $markupPercentage, $column['markup_setting_info']);

            $result[$itemId] = number_format($rateAfterMarkup, 2, '.', '');
        }

        unset($rateInfoColumns);

        return $result;
    }
}