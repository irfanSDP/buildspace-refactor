<?php

class sfBuildspacePostContractSubPackageReportPageElementByTypeSelectedUnits extends sfBuildspacePostContractSubPackageReportPageElementByTypeGenerator {

    public $unitNames = array();

    public function generatePages($ids = array())
    {
        $elementGrandTotals = array();
        $gridStructure      = array();
        $typeIdsFiltered    = array();
        $typeItems          = array();
        $unitNames          = array();
        $typeIds            = $ids;
        $this->revision     = $revision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($this->subPackage);

        $elements = $this->getElements();

        if(count($typeIds))
        {
            foreach( $typeIds as $typeId )
            {
                $explodedTypeId = explode('-', $typeId);

                if ( count($explodedTypeId) > 1 )
                {
                    $billColumnSettingId = $explodedTypeId[0];
                    $count               = $explodedTypeId[1];

                    if ( is_numeric($billColumnSettingId) AND is_numeric($count) )
                    {
                        $typeIdsFiltered[] = array($billColumnSettingId, $count);
                    }
                }

                unset($explodedTypeId);
            }
        }

        if ( count($typeIdsFiltered) > 0 )
        {
            $dynamicWhere = array();

            foreach ( $typeIdsFiltered as $typeId )
            {
                $dynamicWhere[] = "(stype.bill_column_setting_id = {$typeId[0]} AND stype.counter = {$typeId[1]})";
            }

            $stmt = $this->pdo->prepare("SELECT DISTINCT type_ref.id, stype.bill_column_setting_id, cs.name as bill_column_name,
			type_ref.new_name, stype.sub_package_id, stype.counter, type_ref.post_contract_id
			FROM ".SubPackageTypeReferenceTable::getInstance()->getTableName()." stype
			JOIN ".PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName()." type_ref
			ON type_ref.bill_column_setting_id = stype.bill_column_setting_id AND type_ref.counter = stype.counter
			JOIN ".BillColumnSettingTable::getInstance()->getTableName()." cs ON cs.id = stype.bill_column_setting_id
			WHERE cs.project_structure_id = ".$this->bill->id."
			AND stype.sub_package_id = ".$this->subPackage->id." AND (".implode(' OR ', $dynamicWhere).")
			ORDER BY stype.bill_column_setting_id, stype.counter ASC");

            $stmt->execute();

            $typeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            unset($dynamicWhere);
        }

        foreach ( $typeItems as $typeItem )
        {
            $object                         = new PostContractStandardClaimTypeReference();
            $object->id                     = $typeItem['id'];
            $object->bill_column_setting_id = $typeItem['bill_column_setting_id'];
            $object->post_contract_id       = $typeItem['post_contract_id'];

            $elementGrandTotals[$typeItem['bill_column_setting_id']][$typeItem['id']] = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($this->bill->id, $object, $revision, $this->subPackage->id);

            $billColumns[$typeItem['bill_column_setting_id']] = array(
                'id'   => $typeItem['bill_column_setting_id'],
                'name' => $typeItem['bill_column_name'],
            );

            // to be use from the front-end to generate dynamic table columns
            $gridStructure[$typeItem['bill_column_setting_id']][] = array(
                'id'       => $typeItem['id'],
                'new_name' => ($typeItem['new_name']) ? $typeItem['new_name'] : 'Unit ' . $typeItem['counter'],
            );

            $unitNames[$typeItem['bill_column_setting_id']][$typeItem['counter']] = (strlen($typeItem['new_name']) > 0) ? $typeItem['new_name'] : "Unit ".$typeItem['counter'];

            unset($object);
        }

        unset($typeItems);

        $elementTotals    = array();//$elements;
        $this->typeTotals = array();

        foreach ( $this->bill->BillColumnSettings as $billColumnSetting )
        {
            $this->typeTotals[$billColumnSetting['id']]['total_per_unit'] = 0;
            $this->typeTotals[$billColumnSetting['id']]['up_to_date_percentage'] = 0;
            $this->typeTotals[$billColumnSetting['id']]['up_to_date_amount'] = 0;
            $this->typeTotals[$billColumnSetting['id']]['unit_totals'] = array();

            // assign default variable
            foreach($elements as $element)
            {
                $elementId = $element['id'];

                $elementTotals[$elementId][$billColumnSetting['id']]['grand_total']                  = 0;
                $elementTotals[$elementId][$billColumnSetting['id']]['type_total_percentage']        = 0;
                $elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'] = 0;
            }

            // use PostContractStandardClaimTypeReference's if available
            if ( isset($elementGrandTotals[$billColumnSetting['id']]) )
            {
                foreach ( $elementGrandTotals[$billColumnSetting['id']] as $typeId => $elementGrandTotal )
                {
                    if(!array_key_exists($typeId, $this->typeTotals[$billColumnSetting['id']]['unit_totals']))
                    {
                        $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId] = array(
                            'up_to_date_amount' => 0,
                            'total_per_unit'    => 0,
                            'up_to_date_percentage' => 0
                        );
                    }

                    foreach($elements as $element)
                    {
                        if ( isset($elementGrandTotal[$element['id']]) )
                        {
                            $elementTotals[$element['id']][$billColumnSetting['id']]['unit_total'][$typeId]['unit_total_percentage'] = $elementGrandTotal[$element['id']][0]['up_to_date_percentage'];
                            $elementTotals[$element['id']][$billColumnSetting['id']]['type_total_up_to_date_amount'] += $elementGrandTotal[$element['id']][0]['up_to_date_amount'];
                        }

                        $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['up_to_date_amount']+= (isset($elementGrandTotal[$element['id']])) ? $elementGrandTotal[$element['id']][0]['up_to_date_amount'] : 0;
                        $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['total_per_unit']+= (isset($elementGrandTotal[$element['id']])) ? $elementGrandTotal[$element['id']][0]['total_per_unit'] : 0;
                    }

                    $unitPercentage = ($this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['total_per_unit'] > 0) ? ($this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['up_to_date_amount'] / $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['total_per_unit'] * 100) : 0;

                    $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['up_to_date_percentage'] = $unitPercentage;
                }
            }

            unset($billColumnSetting);
        }

        if($elements)
        {
            // Get Type List for all units so we can get contract amount
            $stmt = $this->pdo->prepare("SELECT type_ref.id, stype.bill_column_setting_id, cs.name as bill_column_name,
                type_ref.new_name, stype.sub_package_id, stype.counter, type_ref.post_contract_id
                FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
                JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " type_ref
                ON type_ref.bill_column_setting_id = stype.bill_column_setting_id AND type_ref.counter = stype.counter
                JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
                WHERE cs.project_structure_id = " . $this->bill->id . " AND stype.sub_package_id = " . $this->subPackage->id . "
                ORDER BY stype.bill_column_setting_id, stype.counter ASC");

            $stmt->execute();

            $typeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $elementGrandTotals = array();

            foreach ( $typeItems as $typeItem )
            {
                $obj                         = new PostContractStandardClaimTypeReference();
                $obj->id                     = $typeItem['id'];
                $obj->bill_column_setting_id = $typeItem['bill_column_setting_id'];
                $obj->post_contract_id       = $typeItem['post_contract_id'];

                $elementGrandTotals[$typeItem['bill_column_setting_id']][$typeItem['id']] = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($this->bill->id, $obj, $revision, $this->subPackage->id);
            }

            foreach ( $this->bill->BillColumnSettings as $billColumnSetting )
            {
                // use PostContractStandardClaimTypeReference's if available
                if ( isset( $elementGrandTotals[$billColumnSetting['id']] ) )
                {
                    foreach ( $elementGrandTotals[$billColumnSetting['id']] as $elementGrandTotal )
                    {
                        foreach ( $elements as $element )
                        {
                            if ( isset( $elementGrandTotal[$element['id']] ) )
                            {
                                $elementTotals[$element['id']][$billColumnSetting['id']]['grand_total'] += $elementGrandTotal[$element['id']][0]['total_per_unit'];
                            }
                        }
                    }
                }

                // calculate percentage
                foreach ( $elements as $element )
                {
                    // by element's overall
                    if ( $elementTotals[$element['id']][$billColumnSetting['id']]['type_total_up_to_date_amount'] > 0 )
                    {
                        $elementTotals[$element['id']][$billColumnSetting['id']]['type_total_percentage']  = Utilities::prelimRounding(Utilities::percent($elementTotals[$element['id']][$billColumnSetting['id']]['type_total_up_to_date_amount'], $elementTotals[$element['id']][$billColumnSetting['id']]['grand_total']));
                    }

                    $this->typeTotals[$billColumnSetting['id']]['up_to_date_amount'] += $elementTotals[$element['id']][$billColumnSetting['id']]['type_total_up_to_date_amount'];
                    $this->typeTotals[$billColumnSetting['id']]['total_per_unit']+= $elementTotals[$element['id']][$billColumnSetting['id']]['grand_total'];
                }

                $this->typeTotals[$billColumnSetting['id']]['up_to_date_percentage'] = ($this->typeTotals[$billColumnSetting['id']]['total_per_unit'] > 0) ? ($this->typeTotals[$billColumnSetting['id']]['up_to_date_amount']/$this->typeTotals[$billColumnSetting['id']]['total_per_unit'] * 100) : 0;

                unset( $billColumnSetting );
            }
        }

        $this->elementTotals = $elementTotals;
        $this->unitNames = $unitNames;

        $this->generateBillElementPages($elements, 1, array(), $itemPages);

        $pages = SplFixedArray::fromArray($itemPages);

        return $pages;
    }

}