<?php 

class sfBuildspacePostContractReportPageItemWithClaimGenerator extends sfBuildspacePostContractReportPageItemGenerator
{
    public function generatePages($typeRef)
    {
        $pageNumberDescription = 'Page No. ';
        $pages                 = array();
        $this->typeRef         = $typeRef;
        $billStructure         = array();
        
        $this->revision = $revision  = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($this->project->PostContract);

        $elementTotals = $this->affectedElements;
        $elementGrandTotals = PostContractTable::getTotalClaimRateGroupByElement($this->bill->id, $typeRef, $revision, $this->project->PostContract->id);

        $totalPage = 0;

        foreach($this->affectedElements as $affectedElement)
        {
            $itemPages = array();
            $elementId  = $affectedElement['id'];

            $element = new BillElement();
            $element->id = $elementId;

            list(
                $billItems
            ) = BillItemTable::getDataForPrintingPreviewItemsByColumn($element, $this->bill, $revision, $this->project->PostContract->id, $typeRef, 'up_to_date_amount');

            if(count($billItems))
            {
                $elementInfo = array(
                    'description' => $affectedElement['description']
                );

                $this->generateBillItemPages($billItems, $elementInfo, 1, array(), $itemPages);

                $page = array(
                    'description' => $affectedElement['description'],
                    'item_pages' => SplFixedArray::fromArray($itemPages)
                );

                $totalPage+= count($itemPages);

                $pages[$elementId] = $page;
            }

            if(array_key_exists($elementId, $elementGrandTotals))
            {
                $prevAmount = $elementGrandTotals[$elementId][0]['prev_amount'];
                $currentAmount = $elementGrandTotals[$elementId][0]['current_amount'];
                $prevPercentage = $elementGrandTotals[$elementId][0]['prev_percentage'];
                $totalPerUnit = $elementGrandTotals[$elementId][0]['total_per_unit'];
                $upToDateAmount = $elementGrandTotals[$elementId][0]['up_to_date_amount'];

                $elementTotals[$elementId]['total_per_unit']        = $totalPerUnit;
                $elementTotals[$elementId]['prev_percentage']       = ($totalPerUnit > 0) ? number_format(($prevAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
                $elementTotals[$elementId]['prev_amount']           = $prevAmount;
                $elementTotals[$elementId]['current_percentage']    = ($totalPerUnit > 0) ? number_format(($currentAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
                $elementTotals[$elementId]['current_amount']        = $currentAmount;
                $elementTotals[$elementId]['up_to_date_percentage'] = ($totalPerUnit > 0) ? number_format(($upToDateAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
                $elementTotals[$elementId]['up_to_date_amount']     = $upToDateAmount;
                $elementTotals[$elementId]['up_to_date_qty']        = $elementGrandTotals[$elementId][0]['up_to_date_qty'];
            }

            $elementTotals[$elementId]['claim_type_ref_id'] = $typeRef->id;
            $elementTotals[$elementId]['relation_id']       = $this->bill->id;

            unset($itemPages, $element, $affectedElement, $billItems);
        }

        if(!count($pages))
        {
            $itemPages = array();

            $elementInfo = array(
                'description' => ''
            );

            $this->generateBillItemPages(array(), $elementInfo, 1, array(), $itemPages);

            $page = array(
                'description' => '',
                'item_pages' => SplFixedArray::fromArray($itemPages)
            );

            $totalPage+= count($itemPages);

            $pages[] = $page;

            unset($itemPages);
        }

        $this->elementTotals = $elementTotals;
        $this->totalPage     = $totalPage;

        return $pages;
    }
}