<?php

class sfBuildspacePostContractSubPackagePrelimReportPageItemGenerator extends sfBuildspaceBQMasterFunction
{

	public $pageTitle;
	public $sortingType;
	public $itemIds;
	public $fontSize;
	public $headSettings;
	public $affectedElements;
	public $revision;
	public $typeRef;
	public $elementTotals;
	public $totalPage;

	const CLAIM_PREFIX                  = "Valuation No: ";

	const TOTAL_BILL_ITEM_PROPERTY      = 14;
	const ROW_CLAIM_INITIAL             = 8;
	const ROW_CLAIM_RECURRING           = 9;
	const ROW_CLAIM_FINAL               = 10;
	const ROW_CLAIM_TOTAL               = 11;
	const ROW_BILL_ITEM_CONTRACT_AMOUNT = 7;
	const ROW_BILL_ITEM_QTY_PER_UNIT    = 13;

	public function __construct(SubPackage $subPackage, ProjectStructure $bill, $affectedElements, $itemIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE, $type)
	{
		$this->pdo               = ProjectStructureTable::getInstance()->getConnection()->getDbh();
		$this->bill              = $bill;
		$this->subPackage        = $subPackage;
		$project                 = ProjectStructureTable::getInstance()->find($bill->root_id);

		$this->itemIds           = $itemIds;
		$this->affectedElements  = count($affectedElements) ? $affectedElements : array();

		$this->pageTitle         = $pageTitle;
		$this->currency          = $project->MainInformation->Currency;
		$this->descriptionFormat = $descriptionFormat;

		$this->setOrientationAndSize();

		$this->printSettings     = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, TRUE);
		$this->fontSize          = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType          = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings      = $this->printSettings['headSettings'];

		$this->type              = $type;

		self::setMaxCharactersPerLine();
	}

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

		foreach($this->affectedElements as $elementId => $affectedElement)
		{
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
				$billItems, $billItemTypeReferences, $billItemTypeRefFormulatedColumns, $initialCostings, $timeBasedCostings, $prevTimeBasedCostings, $workBasedCostings, $prevWorkBasedCostings, $finalCostings, $includeInitialCostings, $includeFinalCostings
			) = SubPackagePostContractBillItemRateTable::getPrintPreviewDataStructureForPrelimBillItemList($this->subPackage, $fakeObjectElement, $bill, json_encode($this->itemIds));

			unset($fakeObjectElement);

			foreach($billItems as $billItem)
			{
				$billItem['item_total']           = $billItem['grand_total'];
				$billItem['bill_ref']             = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                 = (string)$billItem['type'];
				$billItem['uom_id']               = $billItem['uom_id'] > 0 ? (string)$billItem['uom_id'] : '-1';
				$billItem['relation_id']          = $elementId;
				$billItem['linked']               = false;
				$billItem['markup_rounding_type'] = $roundingType;
				$billItem['has_note']             = ($billItem['note'] != null && $billItem['note'] != '') ? true : false;
				$billItem['item_total']           = Utilities::prelimRounding($billItem['item_total']);
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

				SubPackagePreliminariesClaimTable::calculateClaimRates($selectedClaimProjectRevision, $billItem, $claimProjectRevision, $initialCostings, $finalCostings, $timeBasedCostings, $workBasedCostings, $prevTimeBasedCostings, $prevWorkBasedCostings, $includeInitialCostings, $includeFinalCostings);

				if ($billItem['id'] > 0)
				{
					array_push($items, $billItem);
				}

				$elementTotals[$elementId]['grand_total']          += isset($billItem['grand_total']) ? $billItem['grand_total'] : 0;
				$elementTotals[$elementId]['initial-amount']       += $billItem['initial-amount'];
				$elementTotals[$elementId]['recurring-amount']     += $billItem['recurring-amount'];
				$elementTotals[$elementId]['final-amount']         += $billItem['final-amount'];
				$elementTotals[$elementId]['upToDateClaim-amount'] += $billItem['upToDateClaim-amount'];
				$elementTotals[$elementId]['currentClaim-amount']  += $billItem['currentClaim-amount'];

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

			$totalPage += count($itemPages);

			$pages[$elementId] = $page;

			unset($itemPages, $items, $element, $affectedElement, $billItems);
		}

		$this->totalPage     = $totalPage;
		$this->elementTotals = $elementTotals;

		return $pages;
	}

	public function generateBillItemPages(Array $billItems, $elementInfo, $pageCount, $ancestors, &$itemPages)
	{
		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();
		$ancestors             = (is_array($ancestors) && count($ancestors)) ? $ancestors : array();

		$blankRow                                       = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]               = -1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]          = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]      = null;//description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]            = 0;//level
		$blankRow[self::ROW_BILL_ITEM_TYPE]             = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_UNIT]             = null;//unit
		$blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT]     = null;//unit
		$blankRow[self::ROW_BILL_ITEM_RATE]             = null;         //unit
		$blankRow[self::ROW_BILL_ITEM_CONTRACT_AMOUNT]  = null;
		$blankRow[self::ROW_CLAIM_INITIAL]              = null;
		$blankRow[self::ROW_CLAIM_RECURRING]            = null;
		$blankRow[self::ROW_CLAIM_FINAL]                = null;
		$blankRow[self::ROW_CLAIM_TOTAL]                = null;

		//blank row
		array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
		$rowCount = 1;

		$occupiedRows = Utilities::justify($elementInfo['description'], $this->MAX_CHARACTERS);

		if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
		{
			$oneLineDesc = $occupiedRows[0];
			$occupiedRows = new SplFixedArray(1);
			$occupiedRows[0] = $oneLineDesc;
		}

		foreach($occupiedRows as $occupiedRow)
		{
			$row                                      = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
			$row[self::ROW_BILL_ITEM_ID]              = -1; //id
			$row[self::ROW_BILL_ITEM_ROW_IDX]         = NULL; //row index
			$row[self::ROW_BILL_ITEM_DESCRIPTION]     = $occupiedRow; //description
			$row[self::ROW_BILL_ITEM_LEVEL]           = 0; //level
			$row[self::ROW_BILL_ITEM_TYPE]            = self::ROW_TYPE_ELEMENT; //type
			$row[self::ROW_BILL_ITEM_UNIT]            = NULL; //unit
			$row[self::ROW_BILL_ITEM_QTY_PER_UNIT]    = NULL; //unit
			$row[self::ROW_BILL_ITEM_RATE]            = NULL; //unit
			$row[self::ROW_BILL_ITEM_CONTRACT_AMOUNT] = NULL;
			$row[self::ROW_CLAIM_INITIAL]             = NULL;
			$row[self::ROW_CLAIM_RECURRING]           = NULL;
			$row[self::ROW_CLAIM_FINAL]               = NULL;
			$row[self::ROW_CLAIM_TOTAL]               = NULL;

			array_push($itemPages[$pageCount], $row);

			unset($row);
		}

		//blank row
		array_push($itemPages[$pageCount], $blankRow);

		$rowCount += count($occupiedRows)+1;//plus one blank row

		foreach($ancestors as $row)
		{
			array_push($itemPages[$pageCount], $row);
			$rowCount += 1;
			unset($row);
		}

		$ancestors = array();
		$itemIndex    = 1;
		$counterIndex = 0;//display item's index in BQ

		foreach($billItems as $x => $billItem)
		{
			$occupiedRows = ($billItems[$x]['type'] == BillItem::TYPE_ITEM_HTML_EDITOR or $billItems[$x]['type'] == BillItem::TYPE_NOID) ? Utilities::justifyHtmlString($billItems[$x]['description'], $this->MAX_CHARACTERS) : Utilities::justify($billItems[$x]['description'], $this->MAX_CHARACTERS);

			if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
			{
				$oneLineDesc = $occupiedRows[0];
				$occupiedRows = new SplFixedArray(1);
				$occupiedRows[0] = $oneLineDesc;
			}

			$rowCount += count($occupiedRows);

			if($rowCount <= $maxRows)
			{
				foreach($occupiedRows as $key => $occupiedRow)
				{
					if($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID)
					{
						$counterIndex++;
					}

					$row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

					$row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID) ? $billItem['bill_ref_element_no'].'/'.$billItem['bill_ref_page_no'].' '.$billItem['bill_ref_char'] : null;
					$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
					$row[self::ROW_BILL_ITEM_LEVEL] = $billItem['level'];
					$row[self::ROW_BILL_ITEM_TYPE] = $billItem['type'];

					if($key+1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID)
					{
						$row[self::ROW_BILL_ITEM_ID]              = $billItem['id']; //only work item will have id set so we can use it to display rates and quantities
						$row[self::ROW_BILL_ITEM_UNIT]            = $billItem['uom_symbol'];
						$row[self::ROW_BILL_ITEM_CONTRACT_AMOUNT] = self::gridCurrencyRoundingFormat(isset($billItem['grand_total']) ? $billItem['grand_total'] : 0);
						$row[self::ROW_BILL_ITEM_RATE]            = self::gridCurrencyRoundingFormat($billItem['rate']);
						$row[self::ROW_BILL_ITEM_QTY_PER_UNIT]    = self::gridCurrencyRoundingFormat($billItem['qty-qty_per_unit']);
						$row[self::ROW_CLAIM_INITIAL]             = array('amount' => $billItem['initial-amount'], 'percentage' => $billItem['initial-percentage']);
						$row[self::ROW_CLAIM_RECURRING]           = array('percentage' => $billItem['recurring-percentage'], 'amount' => $billItem['recurring-amount']);
						$row[self::ROW_CLAIM_FINAL]               = array('percentage' => $billItem['final-percentage'], 'amount' => $billItem['final-amount']);
						$row[self::ROW_CLAIM_TOTAL]               = array('percentage' => $billItem['upToDateClaim-percentage'], 'amount' => $billItem['upToDateClaim-amount']);

						if ( $this->type == 'currentClaim-amount' )
						{
							$row[self::ROW_CLAIM_TOTAL] = array('percentage' => $billItem['currentClaim-percentage'], 'amount' => $billItem['currentClaim-amount']);
						}
					}
					else
					{
						$row[self::ROW_BILL_ITEM_ID]              = NULL;
						$row[self::ROW_BILL_ITEM_UNIT]            = NULL; //unit
						$row[self::ROW_BILL_ITEM_QTY_PER_UNIT]    = NULL; //unit
						$row[self::ROW_BILL_ITEM_RATE]            = NULL; //unit
						$row[self::ROW_BILL_ITEM_CONTRACT_AMOUNT] = NULL;
						$row[self::ROW_CLAIM_INITIAL]             = NULL;
						$row[self::ROW_CLAIM_RECURRING]           = NULL;
						$row[self::ROW_CLAIM_FINAL]               = NULL;
						$row[self::ROW_CLAIM_TOTAL]               = NULL;

						if ( $key+1 == $occupiedRows->count() && $billItem['type'] == BillItem::TYPE_NOID )
						{
							$row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom_symbol'];//unit
						}
					}

					array_push($itemPages[$pageCount], $row);

					unset($row);
				}

				//blank row
				array_push($itemPages[$pageCount], $blankRow);

				$rowCount++;//plus one blank row;
				$itemIndex++;

				unset($billItems[$x], $occupiedRows);
			}
			else
			{
				unset($occupiedRows);

				$pageCount++;
				$this->generateBillItemPages($billItems, $elementInfo, $pageCount, $ancestors, $itemPages);
				break;
			}
		}
	}

	protected function setOrientationAndSize()
	{
		$this->orientation = self::ORIENTATION_PORTRAIT;
		$this->setPageFormat($this->generatePageFormat());
	}

	public function setPageFormat( $pageFormat )
	{
		$this->pageFormat = $pageFormat;
	}

	protected function generatePageFormat()
	{
		$width  = 595;
		$height = 800;

		return $pf = array(
			'page_format'       => self::PAGE_FORMAT_A4,
			'minimum-font-size' => $this->fontSize,
			'width'             => $width,
			'height'            => $height,
			'pdf_margin_top'    => 8,
			'pdf_margin_right'  => 8,
			'pdf_margin_bottom' => 3,
			'pdf_margin_left'   => 8
		);
	}

	public function setMaxCharactersPerLine()
	{
		$this->MAX_CHARACTERS = 45;
	}

	public function getMaxRows()
	{
		return $maxRows = 65;
	}

}