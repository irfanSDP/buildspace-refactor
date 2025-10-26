<?php

class sfBuildspacePostContractSubPackageStandardBillReportPageElementGenerator extends sfBuildspaceBQMasterFunction {

	public $pageTitle;
	public $sortingType;
	public $elementIds;
	public $fontSize;
	public $headSettings;
	public $affectedElements;
	public $revision;
	public $typeRef;
	public $typeTotals = 0;

	const CLAIM_PREFIX                  = "Valuation No: ";

	const TOTAL_BILL_ITEM_PROPERTY      = 12;
	const ROW_CLAIM_PREVIOUS            = 9;
	const ROW_CLAIM_WORKDONE            = 10;
	const ROW_CLAIM_CURRENT             = 11;
	const ROW_BILL_ITEM_CONTRACT_AMOUNT = 8;

	public function __construct(SubPackage $subPackage, ProjectStructure $bill, $elementIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
	{
		$this->pdo               = ProjectStructureTable::getInstance()->getConnection()->getDbh();
		$this->bill              = $bill;
		$this->subPackage        = $subPackage;
		$project                 = ProjectStructureTable::getInstance()->find($bill->root_id);

		$this->elementIds        = $elementIds;
		$this->pageTitle         = $pageTitle;

		$this->currency          = $project->MainInformation->Currency;
		$this->descriptionFormat = $descriptionFormat;

		$this->setOrientationAndSize();

		$this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, TRUE);
		$this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings  = $this->printSettings['headSettings'];

		self::setMaxCharactersPerLine();
	}

	public function generatePages($typeRef)
	{
		$typeItem       = $this->typeRef = $typeRef;
		$elements       = array();
		$this->revision = $revision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($this->subPackage);

		if ( count($this->elementIds) > 0 )
		{
			$stmt = $this->pdo->prepare("SELECT e.id, e.description, e.note, COALESCE(SUM(rate.single_unit_grand_total), 0) AS overall_total_after_markup
			FROM ".SubPackagePostContractBillItemRateTable::getInstance()->getTableName()." rate
			JOIN ".BillItemTable::getInstance()->getTableName()." i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN ".ProjectStructureTable::getInstance()->getTableName()." b ON b.id =  e.project_structure_id AND b.deleted_at IS NULL
			WHERE e.id IN (".implode(', ', $this->elementIds).") AND b.id = ".$this->bill->id."
			AND rate.sub_package_id = ".$this->subPackage->id." GROUP BY e.id ORDER BY e.priority ASC");

			$stmt->execute();
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$elementGrandTotals = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($this->bill->id, $typeItem, $revision, $this->subPackage->id);
		}

		foreach($elements as $key => $element)
		{
			$elementId = $element['id'];

			if(array_key_exists($elementId, $elementGrandTotals))
			{
				$prevAmount     = $elementGrandTotals[$elementId][0]['prev_amount'];
				$currentAmount  = $elementGrandTotals[$elementId][0]['current_amount'];
				$totalPerUnit   = $elementGrandTotals[$elementId][0]['total_per_unit'];
				$upToDateAmount = $elementGrandTotals[$elementId][0]['up_to_date_amount'];

				$elements[$key]['total_per_unit']        = $totalPerUnit;
				$elements[$key]['prev_percentage']       = ($totalPerUnit > 0) ? number_format(($prevAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
				$elements[$key]['prev_amount']           = $prevAmount;
				$elements[$key]['current_percentage']    = ($totalPerUnit > 0) ? number_format(($currentAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
				$elements[$key]['current_amount']        = $currentAmount;
				$elements[$key]['up_to_date_percentage'] = ($totalPerUnit > 0) ? number_format(($upToDateAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
				$elements[$key]['up_to_date_amount']     = $upToDateAmount;
				$elements[$key]['up_to_date_qty']        = $elementGrandTotals[$elementId][0]['up_to_date_qty'];
			}
		}

		$this->generateBillElementPages($elements, 1, array(), $itemPages);

		$pages = SplFixedArray::fromArray($itemPages);

		$this->typeTotals = (count($this->elementIds)) ? SubPackagePostContractStandardClaimTable::getTotalClaimRateByTypeAndElementIds($this->bill, $this->elementIds, $typeItem, $revision, $this->subPackage) : array();

		return $pages;
	}

	public function generateBillElementPages(Array $billElements, $pageCount, $ancestors, &$itemPages)
	{
		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();
		$ancestors             = (is_array($ancestors) && count($ancestors)) ? $ancestors : array();

		$blankRow                                      = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]              = -1; //id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]         = NULL; //row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]     = NULL; //description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]           = 0; //level
		$blankRow[self::ROW_BILL_ITEM_TYPE]            = self::ROW_TYPE_BLANK; //type
		$blankRow[self::ROW_BILL_ITEM_UNIT]            = NULL; //unit
		$blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT]    = NULL; //unit
		$blankRow[self::ROW_BILL_ITEM_RATE]            = NULL; //unit
		$blankRow[self::ROW_BILL_ITEM_CONTRACT_AMOUNT] = NULL;
		$blankRow[self::ROW_CLAIM_WORKDONE]            = NULL;
		$blankRow[self::ROW_CLAIM_PREVIOUS]            = NULL;
		$blankRow[self::ROW_CLAIM_CURRENT]             = NULL;

		//blank row
		array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
		$rowCount = 1;

		foreach($ancestors as $row)
		{
			array_push($itemPages[$pageCount], $row);
			$rowCount += 1;
			unset($row);
		}

		$ancestors = array();
		$itemIndex    = 1;
		$counterIndex = 0;//display item's index in BQ

		foreach($billElements as $x => $billElement)
		{
			$occupiedRows = Utilities::justify($billElements[$x]['description'], $this->MAX_CHARACTERS);

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
					if($key == 0)
					{
						$counterIndex++;
					}

					$row                                      = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
					$row[self::ROW_BILL_ITEM_ROW_IDX]         = ($key == 0) ? $counterIndex : NULL;
					$row[self::ROW_BILL_ITEM_DESCRIPTION]     = $occupiedRow;
					$row[self::ROW_BILL_ITEM_LEVEL]           = NULL;
					$row[self::ROW_BILL_ITEM_TYPE]            = NULL;
					$row[self::ROW_BILL_ITEM_ID]              = NULL;
					$row[self::ROW_BILL_ITEM_UNIT]            = NULL; //unit
					$row[self::ROW_BILL_ITEM_QTY_PER_UNIT]    = NULL; //unit
					$row[self::ROW_BILL_ITEM_RATE]            = NULL; //unit
					$row[self::ROW_BILL_ITEM_CONTRACT_AMOUNT] = NULL;
					$row[self::ROW_CLAIM_WORKDONE]            = NULL;
					$row[self::ROW_CLAIM_PREVIOUS]            = NULL;
					$row[self::ROW_CLAIM_CURRENT]             = NULL;

					if($key+1 == $occupiedRows->count())
					{
						$row[self::ROW_BILL_ITEM_ID]    = $billElement['id'];//only work item will have id set so we can use it to display rates and quantities
						$row[self::ROW_BILL_ITEM_UNIT]  = null;
						$row[self::ROW_BILL_ITEM_CONTRACT_AMOUNT] = self::gridCurrencyRoundingFormat((array_key_exists('total_per_unit', $billElement)) ? $billElement['total_per_unit'] : 0);
						$row[self::ROW_BILL_ITEM_RATE]  = null;
						$row[self::ROW_BILL_ITEM_QTY_PER_UNIT]  = null;
						$row[self::ROW_CLAIM_WORKDONE]  = array(
							'up_to_date_percentage' => (array_key_exists('up_to_date_percentage', $billElement)) ? $billElement['up_to_date_percentage'] : 0,
							'up_to_date_amount' => (array_key_exists('up_to_date_amount', $billElement)) ? $billElement['up_to_date_amount'] : 0,
							'up_to_date_qty' => (array_key_exists('up_to_date_qty', $billElement)) ? $billElement['up_to_date_qty'] : 0
						);
						$row[self::ROW_CLAIM_PREVIOUS]  = array(
							'prev_percentage' => (array_key_exists('prev_percentage', $billElement)) ? $billElement['prev_percentage'] : 0,
							'prev_amount' => (array_key_exists('prev_amount', $billElement)) ? $billElement['prev_amount'] : 0
						);
						$row[self::ROW_CLAIM_CURRENT]   = array(
							'current_percentage' => (array_key_exists('prev_percentage', $billElement)) ? $billElement['current_percentage'] : 0,
							'current_amount' => (array_key_exists('prev_percentage', $billElement)) ? $billElement['current_amount'] : 0
						);
					}

					array_push($itemPages[$pageCount], $row);

					unset($row);
				}

				//blank row
				array_push($itemPages[$pageCount], $blankRow);

				$rowCount++;//plus one blank row;
				$itemIndex++;

				unset($billElements[$x], $occupiedRows);
			}
			else
			{
				unset($occupiedRows);

				$pageCount++;
				$this->generateBillElementPages($billElements, $pageCount, $ancestors, $itemPages);
				break;
			}
		}
	}

	protected function setOrientationAndSize()
	{
		$this->orientation = self::ORIENTATION_PORTRAIT;
		$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
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
			'pdf_margin_right'  => 10,
			'pdf_margin_bottom' => 3,
			'pdf_margin_left'   => 10
		);
	}

	public function setMaxCharactersPerLine()
	{
		$this->MAX_CHARACTERS = 48;
	}

	public function getMaxRows()
	{
		return $maxRows = 60;
	}

}