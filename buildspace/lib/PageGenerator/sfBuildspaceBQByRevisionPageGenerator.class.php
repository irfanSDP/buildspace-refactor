<?php

class sfBuildspaceBQByRevisionPageGenerator extends sfBuildspaceBQPageGenerator
{
    protected $selectedProjectRevision;

    public function __construct(ProjectStructure $bill, BillElement $element=null)
    {
        $project = $bill->root_id == $bill->id ? $bill : ProjectStructureTable::getInstance()->find($bill->root_id);

        $this->selectedProjectRevision = $project->getCurrentSelectedProjectRevision();

        parent::__construct( $bill, $element );
    }

    protected function queryBillStructure()
    {
        $billStructure = [];

        $elementSqlPart = $this->billElement instanceof BillElement ? "AND e.id = ".$this->billElement->id : null;

        $stmt = $this->pdo->prepare("SELECT e.id, e.description FROM ".BillElementTable::getInstance()->getTableName()." e
        WHERE e.project_structure_id = ".$this->bill->id." ".$elementSqlPart." AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $result = [
                'id'          => $element['id'],
                'description' => $element['description'],
                'items'       => []
            ];

            $stmt = $this->pdo->prepare("SELECT c.id, c.description, c.element_id, c.type,
                COALESCE(c.grand_total_after_markup, 0) AS grand_total_after_markup, c.bill_ref_element_no,
                c.bill_ref_page_no, c.bill_ref_char, c.uom_id, c.lft, c.rgt, c.root_id, c.level, uom.symbol AS uom
                FROM ".BillItemTable::getInstance()->getTableName()." c
                LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE c.element_id = ".$element['id']." AND c.project_revision_id = ".$this->selectedProjectRevision->id."
                AND c.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level");

            $stmt->execute();

            $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result['items'] = $billItems;

            array_push($billStructure, $result);

            unset($element, $billItems);
        }

        return $billStructure;
    }

    protected function getItemQuantities()
    {
        $implodedItemIds = null;
        $result = [];

        foreach($this->billStructure as $element)
        {
            if(count($element['items']) == 0)
                continue;//we skip element with empty items

            $itemIds = Utilities::arrayValueRecursive('id', $element['items']);

            if(is_array($itemIds))
            {
                $implodedItemIds .= implode(',', $itemIds);
                $implodedItemIds .= ",";
            }

            unset($element, $itemIds);
        }

        $implodedItemIds = rtrim($implodedItemIds, ",");

        if($this->printGrandTotalQty)
        {
            if ( ! empty($implodedItemIds) )
            {
                $stmt = $this->pdo->prepare("SELECT i.id, i.grand_total_quantity AS value FROM ".BillItemTable::getInstance()->getTableName()." i
                WHERE i.id IN (".$implodedItemIds.") AND i.project_revision_id = ".$this->selectedProjectRevision->id." AND i.grand_total_quantity <> 0 AND i.deleted_at IS NULL");

                $stmt->execute();

                $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                $result = $quantities;

                unset($quantities);
            }
        }
        else
        {
            foreach($this->bill->BillColumnSettings->toArray() as $column)
            {
                $quantityFieldName = $column['use_original_quantity'] ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                if ( ! empty($implodedItemIds) )
                {
                    $stmt = $this->pdo->prepare("SELECT i.id, COALESCE(fc.final_value, 0) AS value
                    FROM ".BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName()." fc
                    JOIN ".BillItemTypeReferenceTable::getInstance()->getTableName()." r ON fc.relation_id = r.id
                    JOIN ".BillItemTable::getInstance()->getTableName()." i ON r.bill_item_id = i.id
                    WHERE i.id IN (".$implodedItemIds.") AND r.bill_column_setting_id = ".$column['id']."
                    AND i.project_revision_id = ".$this->selectedProjectRevision->id."
                    AND r.include IS TRUE AND fc.column_name = '".$quantityFieldName."' AND fc.final_value <> 0
                    AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                    $stmt->execute();

                    $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                    $result[$column['id']] = $quantities;

                    unset($quantities);
                }
                else
                {
                    $result[$column['id']] = 0;
                }
            }
        }

        return $result;
    }

    protected function getLumpSumPercent()
    {
        $stmt = $this->pdo->prepare("SELECT i.id, c.percentage
            FROM ".BillItemLumpSumPercentageTable::getInstance()->getTableName()." c
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON (c.bill_item_id = i.id AND i.type = ".BillItem::TYPE_ITEM_LUMP_SUM_PERCENT.")
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            WHERE e.project_structure_id = ".$this->bill->id."
            AND c.deleted_at IS NULL AND i.project_revision_id = ".$this->selectedProjectRevision->id."
            AND i.deleted_at IS NULL AND e.deleted_at IS NULL
            ORDER BY i.id");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
    }

    protected function getRatesAfterMarkup()
    {
        $elementMarkupResults = [];
        $rateInfoColumns      = [];

        if($this->bill->BillMarkupSetting->element_markup_enabled)
        {
            $stmt = $this->pdo->prepare("SELECT e.id, COALESCE(c.final_value, 0) as value
                FROM ".BillElementFormulatedColumnTable::getInstance()->getTableName()." c
                JOIN ".BillElementTable::getInstance()->getTableName()." e ON c.relation_id = e.id
                WHERE e.project_structure_id = ".$this->bill->id." AND c.column_name = '".BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE."'
                AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

            $stmt->execute();

            $elementMarkupResults = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
        }

        $stmt = $this->pdo->prepare("SELECT c.relation_id, i.element_id, c.column_name, COALESCE(c.final_value, 0) AS value
            FROM ".BillItemFormulatedColumnTable::getInstance()->getTableName()." c
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON c.relation_id = i.id
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            WHERE e.project_structure_id = ".$this->bill->id." AND (c.column_name = '".BillItem::FORMULATED_COLUMN_RATE."' OR c.column_name = '".BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE."')
            AND c.deleted_at IS NULL AND i.project_revision_id = ".$this->selectedProjectRevision->id."
            AND i.deleted_at IS NULL AND e.deleted_at IS NULL
            ORDER BY i.id");

        $stmt->execute();

        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($columns as $column)
        {
            $markupSettingsInfo = array(
                'bill_markup_enabled'       => $this->bill->BillMarkupSetting->bill_markup_enabled,
                'bill_markup_percentage'    => $this->bill->BillMarkupSetting->bill_markup_percentage,
                'element_markup_enabled'    => $this->bill->BillMarkupSetting->element_markup_enabled,
                'element_markup_percentage' => array_key_exists($column['element_id'], $elementMarkupResults) ? $elementMarkupResults[$column['element_id']][0] : 0,
                'item_markup_enabled'       => $this->bill->BillMarkupSetting->item_markup_enabled,
                'rounding_type'             => $this->bill->BillMarkupSetting->rounding_type
            );

            $rateInfoColumns[$column['relation_id']]['markup_setting_info'] = $markupSettingsInfo;
            $rateInfoColumns[$column['relation_id']][$column['column_name']] = $column['value'];

            unset($column);
        }

        $result = [];

        foreach($rateInfoColumns as $itemId => $column)
        {
            $markupPercentage = array_key_exists(BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE, $column) ? $column[BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE] : 0;
            $rate = array_key_exists(BillItem::FORMULATED_COLUMN_RATE, $column) ? $column[BillItem::FORMULATED_COLUMN_RATE] : 0;

            $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $markupPercentage, $column['markup_setting_info']);

            $result[$itemId] = number_format($rateAfterMarkup, 2, '.', '');
        }

        unset($columns, $rateInfoColumns);

        return $result;
    }
}