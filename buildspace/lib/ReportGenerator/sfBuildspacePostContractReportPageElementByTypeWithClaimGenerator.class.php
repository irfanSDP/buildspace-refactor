<?php

class sfBuildspacePostContractReportPageElementByTypeWithClaimGenerator extends sfBuildspacePostContractReportPageElementByTypeGenerator
{
    public $billColumnSettings = array();
    public $unitNames = array();

    public function generatePages($ids = null)
    {
        $pageNumberDescription      = 'Page No. ';
        $pages                      = array();
        $bill                       = $this->bill;
        $postContract               = $this->project->PostContract;
        $elementGrandTotals         = array();
        $unitNames                  = array();

        $this->revision = $revision  = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $elementIds = array_map(function ($ar) {return $ar['id'];}, $elements);

        $contractAmountByElements = PostContractStandardClaimTable::getOverallTotalByBillElementId($elementIds, $revision);

        $totalClaimRates = PostContractTable::getTotalClaimRateGroupByElementAndTypeRef($bill->id, $revision, $postContract->id);

        foreach($totalClaimRates as $totalClaimRate)
        {
            $elementGrandTotals[$totalClaimRate['bill_column_setting_id']][$totalClaimRate['claim_type_ref_id']][$totalClaimRate['element_id']] = array(
                'prev_amount' => $totalClaimRate['prev_amount'],
                'up_to_date_amount' => $totalClaimRate['up_to_date_amount'],
                'up_to_date_qty' => $totalClaimRate['up_to_date_qty'],
                'current_amount' => $totalClaimRate['current_amount'],
                'total_per_unit' => $totalClaimRate['total_per_unit'],
                'prev_percentage' => $totalClaimRate['prev_percentage'],
                'up_to_date_percentage' => $totalClaimRate['up_to_date_percentage'],
                'current_percentage' => $totalClaimRate['current_percentage'],
                'total_up_to_date_amount' => $totalClaimRate['total_up_to_date_amount']
            );

            $unitNames[$totalClaimRate['bill_column_setting_id']][$totalClaimRate['counter']] = (strlen($totalClaimRate['new_name']) > 0) ? $totalClaimRate['new_name'] : "Unit ".$totalClaimRate['counter'];
        }

        $elementTotals    = array();
        $this->typeTotals = array();

        foreach ( $bill->BillColumnSettings as $billColumnSetting )
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

                if(isset($elementGrandTotals[$billColumnSetting['id']]))
                {
                    foreach ( $elementGrandTotals[$billColumnSetting['id']] as $typeId => $elementGrandTotal )
                    {
                        if(isset($elementTotals[$element['id']]) && isset($elementTotals[$element['id']][$billColumnSetting['id']]))
                        {
                            $elementTotals[$element['id']][$billColumnSetting['id']]['unit_total'][$typeId]['unit_total_percentage'] = 0;
                        }
                    }
                }
            }

            // use PostContractStandardClaimTypeReference's if available
            if ( isset($elementGrandTotals[$billColumnSetting['id']]) )
            {
                if(!isset($this->billColumnSettings[$billColumnSetting['id']]))
                {
                    $this->billColumnSettings[$billColumnSetting['id']] = $billColumnSetting;
                }

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

                    foreach($elementGrandTotal as $elemId => $data)
                    {
                        if(isset($elementTotals[$elemId]))
                        {
                            $elementTotals[$elemId][$billColumnSetting['id']]['unit_total'][$typeId]['unit_total_percentage'] = $elementGrandTotal[$elemId]['up_to_date_percentage'];
                            $elementTotals[$elemId][$billColumnSetting['id']]['type_total_up_to_date_amount'] += $elementGrandTotal[$elemId]['up_to_date_amount'];
                        }

                        $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['total_up_to_date_amount'] = (isset($elementGrandTotal[$elemId])) ? $elementGrandTotal[$elemId]['total_up_to_date_amount'] : 0;
                        $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['up_to_date_amount']+= (isset($elementGrandTotal[$elemId])) ? $elementGrandTotal[$elemId]['up_to_date_amount'] : 0;
                        $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['total_per_unit']+= (isset($elementGrandTotal[$elemId])) ? $elementGrandTotal[$elemId]['total_per_unit'] : 0;
                    }

                    $unitPercentage = !empty($this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['total_up_to_date_amount']) ? ($this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['up_to_date_amount'] / $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['total_up_to_date_amount'] * 100) : 0;

                    $this->typeTotals[$billColumnSetting['id']]['unit_totals'][$typeId]['up_to_date_percentage'] = $unitPercentage;

                    $typeQuantityCounter++;
                }

                //we overwrite bill column settings unit qty to number of unit with claim
                $this->billColumnSettings[$billColumnSetting['id']]['quantity'] = $typeQuantityCounter;
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

        unset($contractAmountByElements);

        $this->elementTotals = $elementTotals;

        $this->unitNames = $unitNames;

        $this->generateBillElementPages($elements, 1, array(), $itemPages);

        return SplFixedArray::fromArray($itemPages);
    }
}