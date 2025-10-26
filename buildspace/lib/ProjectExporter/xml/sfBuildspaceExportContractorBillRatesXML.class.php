<?php
class sfBuildspaceExportContractorBillRatesXML extends sfBuildspaceExportBillRatesXML
{
    public $tenderCompany;

    function __construct( $tenderCompany, $filename = null, $uploadPath = null, $billId, $extension = null, $deleteFile = null ) 
    {   
        $this->tenderCompany = $tenderCompany;

        parent::__construct( $filename, $uploadPath, $billId, $extension, $deleteFile );
    }

    public function getItemLumpSumPercentage($itemId )
    {
        $sql = "SELECT  ls.rate, ls.percentage, ls.amount 
        FROM ".TenderBillItemLumpSumPercentageTable::getInstance()->getTableName()." ls
        LEFT JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON ls.tender_bill_item_rate_id = rate.id
        WHERE rate.bill_item_id = :item_id AND rate.tender_company_id = :tc_id";

        $params = array(
            'item_id' => $itemId,
            'tc_id'   => $this->tenderCompany->id
        );

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return $itemLumpSumPercentage = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItemPrimeCost( $itemId )
    {
        $sql = "SELECT pc.supply_rate, pc.wastage_percentage, pc.wastage_amount, pc.labour_for_installation, pc.other_cost,
        pc.profit_percentage, pc.profit_amount, pc.total FROM ".TenderBillItemPrimeCostRateTable::getInstance()->getTableName()." pc
        LEFT JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON pc.tender_bill_item_rate_id = rate.id 
        WHERE rate.bill_item_id = :item_id AND rate.tender_company_id = :tc_id";

        $params = array(
            'item_id' => $itemId,
            'tc_id'   => $this->tenderCompany->id
        );

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return $itemPrimeCost = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItemTypeRef($itemId)
    {
        $sql = "SELECT id, name, quantity FROM ".BillColumnSettingTable::getInstance()->getTableName()." c 
        WHERE c.project_structure_id = :project_structure_id AND c.deleted_at IS NULL";

        $params = array(
            'project_structure_id' => $this->billId
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $billColumnSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $columnIds = (count($billColumnSettings)) ? Utilities::arrayValueRecursive('id', $billColumnSettings) : array();

        if(count($columnIds))
        {
            $sql = "SELECT inlq.bill_column_setting_id, inl.bill_item_id, inl.description, inl.tender_company_id, inlq.final_value FROM ".TenderBillItemNotListedQuantityTable::getInstance()->getTableName()." inlq
            LEFT JOIN  ".TenderBillItemNotListedTable::getInstance()->getTableName()." inl ON inl.id = inlq.tender_bill_item_not_listed_id
            WHERE inlq.bill_column_setting_id IN (".implode(',', $columnIds).") AND inl.bill_item_id = ".$itemId." AND inl.tender_company_id = ".$this->tenderCompany->id;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $inlQuantities = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

            $typeRefsItems = array();

            if(count($inlQuantities))
            {
                foreach($inlQuantities as $columnId => $inlQuantity)
                {
                    $typeRefsItem = array(
                        'bill_item_id' => $inlQuantity[0]['bill_item_id'],
                        'bill_column_setting_id' => $columnId,
                        'FormulatedColumns' => array(),
                        'BillItem' => array('id' => $inlQuantity[0]['bill_item_id'], 'tender_origin_id' => null),
                        'BillColumnSetting' => array('id' => $columnId, 'tender_origin_id' => null)
                    );

                    array_push($typeRefsItem['FormulatedColumns'], array(
                        'column_name' => BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT,
                        'final_value' => $inlQuantity[0]['final_value']
                    ));

                    array_push($typeRefsItems, $typeRefsItem);
                }
            }

            return $typeRefsItems;
        }

        return false;
    }

    public function processTypeRef( $typeRefs )
    {
        $this->createTypeRefTag();

        foreach($typeRefs as $typeRef)
        {
            $typeFc = array();

            if((array_key_exists('FormulatedColumns', $typeRef)) && count($typeRef['FormulatedColumns'] > 0))
            {
                $typeFc = $typeRef['FormulatedColumns'];

                unset($typeRef['FormulatedColumns']);
            }

            if(array_key_exists('BillColumnSetting', $typeRef) && count($typeRef['BillColumnSetting']))
            {
                $billColumnSettingOriginalId = $typeRef['BillColumnSetting']['id'];

                $typeRef['bill_column_setting_id'] = $billColumnSettingOriginalId;

                unset($typeRef['BillColumnSetting']);
            }

            if(array_key_exists('BillItem', $typeRef) && count($typeRef['BillItem']))
            {
                $billItemOriginalId = $typeRef['BillItem']['id'];

                $typeRef['bill_item_id'] = $billItemOriginalId;

                unset($typeRef['BillItem']);
            }

            $this->addTypeRefChildren( $typeRef );

            $count = 0;
            
            foreach($typeFc as $fc)
            {
                $this->createQtyTag( $fc, $count );

                $count++;
            }
        }
    }
}
