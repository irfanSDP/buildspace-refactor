<?php

class sfBuildspacePostContractSubPackageReportPageElementByTypeWithClaimGenerator extends sfBuildspacePostContractSubPackageReportPageElementByTypeGenerator {

    public function generatePages($ids = null)
    {
        $elementGrandTotals = array();
        $gridStructure      = array();
        $unitNames          = array();
        $this->revision     = $revision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($this->subPackage);

        $elements = $this->getElements();

        $elementIds = array_map(function ($ar) {return $ar['id'];}, $elements);

        $contractAmountByElements = SubPackagePostContractStandardClaimTable::getOverallTotalByBillElementId($elementIds, $revision);

        $billColumnSettingAndTypeHasClaims = array();

        foreach ($this->getTypeItems() as $typeItem )
        {
            $object                         = new PostContractStandardClaimTypeReference();
            $object->id                     = $typeItem['id'];
            $object->bill_column_setting_id = $typeItem['bill_column_setting_id'];
            $object->post_contract_id       = $typeItem['post_contract_id'];

            $elementGrandTotals[$typeItem['bill_column_setting_id']][$typeItem['id']] = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($this->bill->id, $object, $revision, $this->subPackage->id);

            // get only type with up to date claims
            foreach ($elementGrandTotals[$typeItem['bill_column_setting_id']][$typeItem['id']] as $elementGrandTotal)
            {
                if ( isset($elementGrandTotal[0]) AND !empty($elementGrandTotal[0]['up_to_date_amount']) )
                {
                    if(!isset($billColumnSettingAndTypeHasClaims[$typeItem['bill_column_setting_id'].'-'.$typeItem['id']]))
                    {
                        $billColumnSettingAndTypeHasClaims[$typeItem['bill_column_setting_id'].'-'.$typeItem['id']] = true;
                    }

                    $unitNames[$typeItem['bill_column_setting_id']][$typeItem['counter']] = (strlen($typeItem['new_name']) > 0) ? $typeItem['new_name'] : "Unit ".$typeItem['counter'];

                    $billColumns[$typeItem['bill_column_setting_id']] = array(
                        'id'   => $typeItem['bill_column_setting_id'],
                        'name' => $typeItem['bill_column_name'],
                    );

                    // to be use from the front-end to generate dynamic table columns
                    $gridStructure[$typeItem['bill_column_setting_id']][] = array(
                        'id'       => $typeItem['id'],
                        'new_name' => ($typeItem['new_name']) ? $typeItem['new_name'] : 'Unit ' . $typeItem['counter'],
                    );
                }
            }

            unset($typeItem, $object);
        }

        $elementTotals    = array();
        $this->typeTotals = array();

        foreach ( $this->bill->BillColumnSettings as $billColumnSetting )
        {
            $typeQuantityCounter = 0;

            $this->typeTotals[$billColumnSetting['id']]['total_per_unit'] = 0;
            $this->typeTotals[$billColumnSetting['id']]['up_to_date_percentage'] = 0;
            $this->typeTotals[$billColumnSetting['id']]['up_to_date_amount'] = 0;
            $this->typeTotals[$billColumnSetting['id']]['unit_totals'] = array();

            // assign default variable
            foreach($elements as $element)
            {
                $elementTotals[$element['id']][$billColumnSetting['id']]['grand_total'] = (isset($contractAmountByElements[$element['id']]) && isset($contractAmountByElements[$element['id']][$billColumnSetting['id']])) ? $contractAmountByElements[$element['id']][$billColumnSetting['id']] : 0;
                $elementTotals[$element['id']][$billColumnSetting['id']]['type_total_percentage']        = 0;
                $elementTotals[$element['id']][$billColumnSetting['id']]['type_total_up_to_date_amount'] = 0;
            }

            // use PostContractStandardClaimTypeReference's if available
            if ( isset($elementGrandTotals[$billColumnSetting['id']]) )
            {
                foreach ( $elementGrandTotals[$billColumnSetting['id']] as $typeId => $elementGrandTotal )
                {
                    if(!array_key_exists($typeId, $this->typeTotals[$billColumnSetting['id']]['unit_totals']) && isset($billColumnSettingAndTypeHasClaims[$billColumnSetting['id'].'-'.$typeId]))
                    {
                        $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId] = array(
                            'up_to_date_amount' => 0,
                            'total_per_unit'    => 0,
                            'up_to_date_percentage' => 0
                        );
                    }

                    foreach($elementGrandTotal as $elemId => $data)
                    {
                        if(isset($billColumnSettingAndTypeHasClaims[$billColumnSetting['id'].'-'.$typeId]))
                        {
                            if(isset($elementTotals[$elemId]))
                            {
                                $elementTotals[$elemId][$billColumnSetting['id']]['unit_total'][$typeId]['unit_total_percentage'] = $elementGrandTotal[$elemId][0]['up_to_date_percentage'];
                                $elementTotals[$elemId][$billColumnSetting['id']]['type_total_up_to_date_amount'] += $elementGrandTotal[$elemId][0]['up_to_date_amount'];
                            }

                            $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['up_to_date_amount']+= (isset($elementGrandTotal[$elemId])) ? $elementGrandTotal[$elemId][0]['up_to_date_amount'] : 0;
                            $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['total_per_unit']+= (isset($elementGrandTotal[$elemId])) ? $elementGrandTotal[$elemId][0]['total_per_unit'] : 0;
                        }
                        else
                            break;
                    }

                    if(isset($billColumnSettingAndTypeHasClaims[$billColumnSetting['id'].'-'.$typeId]))
                    {
                        $unitPercentage = !empty($this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['total_per_unit']) ? ($this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['up_to_date_amount'] / $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['total_per_unit'] * 100) : 0;

                        $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['up_to_date_percentage'] = $unitPercentage;

                        $typeQuantityCounter++;
                    }

                }
            }

            // calculate percentage
            foreach($elements as $element)
            {
                // by element's overall
                if (!empty($elementTotals[$element['id']][$billColumnSetting['id']]['type_total_up_to_date_amount']))
                {
                    $elementTotals[$element['id']][$billColumnSetting['id']]['type_total_percentage']  = Utilities::prelimRounding(Utilities::percent($elementTotals[$element['id']][$billColumnSetting['id']]['type_total_up_to_date_amount'], $elementTotals[$element['id']][$billColumnSetting['id']]['grand_total']));
                }

                $this->typeTotals[$billColumnSetting['id']]['up_to_date_amount'] += $elementTotals[$element['id']][$billColumnSetting['id']]['type_total_up_to_date_amount'];
                $this->typeTotals[$billColumnSetting['id']]['total_per_unit']+= $elementTotals[$element['id']][$billColumnSetting['id']]['grand_total'];
            }

            $this->typeTotals[$billColumnSetting['id']]['up_to_date_percentage'] = !empty($this->typeTotals[$billColumnSetting['id']]['total_per_unit']) ? ($this->typeTotals[$billColumnSetting['id']]['up_to_date_amount']/$this->typeTotals[$billColumnSetting['id']]['total_per_unit'] * 100) : 0;

            unset($billColumnSetting);
        }

        unset($contractAmountByElements, $billColumnSettingAndTypeHasClaims);

        $this->elementTotals = $elementTotals;

        $this->unitNames = $unitNames;

        $this->generateBillElementPages($elements, 1, array(), $itemPages);

        $pages = SplFixedArray::fromArray($itemPages);

        return $pages;
    }

}