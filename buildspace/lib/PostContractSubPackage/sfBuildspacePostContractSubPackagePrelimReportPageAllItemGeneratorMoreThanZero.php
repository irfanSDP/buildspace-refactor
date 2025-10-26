<?php

class sfBuildspacePostContractSubPackagePrelimReportPageAllItemGeneratorMoreThanZero extends sfBuildspacePostContractSubPackagePrelimReportPageItemGenerator
{

	public function generatePages()
	{
		$pages         = array();
		$bill          = $this->bill;
		$roundingType  = $bill->BillMarkupSetting->rounding_type;
		$column        = $bill->BillColumnSettings->toArray();
		$pageNoPrefix  = $bill->BillLayoutSetting->page_no_prefix;
		$elementTotals = array();
		$totalPage     = 0;

		$claimProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentProjectRevision($this->subPackage);
		$this->revision = $selectedClaimProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($this->subPackage);

		foreach($this->affectedElements as $affectedElement)
		{
			$elementId  = $affectedElement['id'];

			if(!array_key_exists($elementId, $elementTotals))
			{
				$elementTotals[$elementId] = array(
					'grand_total'              => 0,
					'initial-amount'           => 0,
					'initial-percentage'       => 0,
					'recurring-amount'         => 0,
					'recurring-percentage'     => 0,
					'final-amount'             => 0,
					'final-percentage'         => 0,
					'upToDateClaim-amount'     => 0,
					'upToDateClaim-percentage' => 0,
					'currentClaim-amount'      => 0,
					'currentClaim-percentage'  => 0,
				);
			}

			$items                 = array();
			$itemPages             = array();
			$fakeObjectElement     = new BillElement();
			$fakeObjectElement->id = $elementId;

			list(
				$billItems, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = SubPackagePostContractBillItemRateTable::getPrintingPreviewDataStructureForPrelimBillItemListByClaimType($this->subPackage, $fakeObjectElement, $bill, 'currentClaim-amount');

			unset($fakeObjectElement);

			foreach($billItems as $billItem)
			{
				$billItem['bill_ref']             = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                 = (string)$billItem['type'];
				$billItem['uom_id']               = $billItem['uom_id'] > 0 ? (string)$billItem['uom_id'] : '-1';
				$billItem['relation_id']          = $elementId;
				$billItem['linked']               = false;
				$billItem['markup_rounding_type'] = $roundingType;
				$billItem['has_note']             = ($billItem['note'] != null && $billItem['note'] != '') ? true : false;
				$billItem['claim_at_revision_id'] = (! empty($billItem['claim_at_revision_id'])) ? $billItem['claim_at_revision_id'] : $claimProjectRevision['id'];

				$billItem['rate']             = Utilities::prelimRounding($billItem['rate']);
				$billItem['qty-qty_per_unit'] = 0;
				$billItem['qty-has_build_up'] = false;
				$billItem['qty-column_id']    = $column[0]['id'];

				if(array_key_exists($column[0]['id'], $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$column[0]['id']]))
				{
					$billItemTypeRef = $billItemTypeReferences[$column[0]['id']][$billItem['id']];

					unset($billItemTypeReferences[$column[0]['id']][$billItem['id']]);

					if(array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns))
					{
						foreach($billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn)
						{
							$billItem['qty-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];

							unset($billItemTypeRefFormulatedColumn);
						}
					}

					unset($billItemTypeRef);
				}

				if ($billItem['id'] > 0)
				{
					array_push($items, $billItem);
				}

				$elementTotals[$elementId]['grand_total']          += isset($billItem['grand_total']) ? $billItem['grand_total'] : 0;
				$elementTotals[$elementId]['initial-amount']       += isset($billItem['initial-amount']) ? $billItem['initial-amount'] : 0;
				$elementTotals[$elementId]['recurring-amount']     += isset($billItem['recurring-amount']) ? $billItem['recurring-amount'] : 0;
				$elementTotals[$elementId]['final-amount']         += isset($billItem['final-amount']) ? $billItem['final-amount'] : 0;
				$elementTotals[$elementId]['upToDateClaim-amount'] += isset($billItem['upToDateClaim-amount']) ? $billItem['upToDateClaim-amount'] : 0;
				$elementTotals[$elementId]['currentClaim-amount']  += isset($billItem['currentClaim-amount']) ? $billItem['currentClaim-amount'] : 0;

				unset($billItem);
			}

			$elementTotals[$elementId]['initial-percentage'] = ($elementTotals[$elementId]['initial-amount'] > 0) ? ($elementTotals[$elementId]['initial-amount'] / $elementTotals[$elementId]['grand_total'] * 100) : 0;
			$elementTotals[$elementId]['recurring-percentage'] = ($elementTotals[$elementId]['recurring-amount'] > 0) ? ($elementTotals[$elementId]['recurring-amount'] / $elementTotals[$elementId]['grand_total'] * 100) : 0;
			$elementTotals[$elementId]['final-percentage'] = ($elementTotals[$elementId]['final-amount'] > 0) ? ($elementTotals[$elementId]['final-amount'] / $elementTotals[$elementId]['grand_total'] * 100) : 0;
			$elementTotals[$elementId]['upToDateClaim-percentage'] = ($elementTotals[$elementId]['upToDateClaim-amount'] > 0) ? ($elementTotals[$elementId]['upToDateClaim-amount'] / $elementTotals[$elementId]['grand_total'] * 100) : 0;
			$elementTotals[$elementId]['currentClaim-percentage'] = ($elementTotals[$elementId]['currentClaim-amount'] > 0) ? ($elementTotals[$elementId]['currentClaim-amount'] / $elementTotals[$elementId]['grand_total'] * 100) : 0;

			$elementInfo = array(
				'description' => $affectedElement['description']
			);

			$this->generateBillItemPages($items, $elementInfo, 1, array(), $itemPages);

			$page = array(
				'description' => $affectedElement['description'],
				'item_pages'  => SplFixedArray::fromArray($itemPages)
			);

			$totalPage+= count($itemPages);

			$pages[$elementId] = $page;

			unset($itemPages, $items, $element, $affectedElement, $billItems);
		}

		$this->totalPage     = $totalPage;
		$this->elementTotals = $elementTotals;

		return $pages;
	}

}