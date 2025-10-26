<?php

class sfBuildSpaceReportElementByTendererAndType extends sfBuildspaceBQMasterFunction {

	public $tendererIds;

	public $tenderers;

	public $pageTitle;

	public $elementIds;

	public $fontSize;

	public $headSettings;

	public $estimateOverAllTotal = array();

	public $contractorOverAllTotal = array();

	const ROW_BILL_ITEM_ESTIMATE_TOTALS = 6;
	const ROW_BILL_ITEM_CONTRACTOR_TOTALS = 7;

	public function __construct(ProjectStructure $bill, $tendererIds, $elementIds, $pageTitle, $desc, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
	{
		$this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

		$this->bill    = $bill;
		$this->project = ProjectStructureTable::getInstance()->find($bill->root_id);

		$this->elementIds         = $elementIds;
		$this->pageTitle          = $pageTitle;
		$this->currency           = $this->project->MainInformation->Currency;
		$this->tendererIds        = $tendererIds;
		$this->tenderers          = $this->getTenderer();
		$this->billColumnSettings = $bill->BillColumnSettings;
		$this->elements           = $this->getElements();

		$this->descriptionFormat = $descriptionFormat;

		$this->setOrientationAndSize();

		$this->elementsOrder = $this->getElementOrder();
		$this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, true);
		$this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings  = $this->printSettings['headSettings'];

		$this->contractorRates             = TenderCompanyTable::getContractorSingleUnitElementGrandTotalByBillAndElementsAndTenderers($bill, $this->elements, $tendererIds);
		$this->itemOriginalQuantityByTypes = BillItemTypeReferenceTable::getQtyByBillColumnSettingsIdAndElementIds($bill->BillColumnSettings->toArray(), $this->elements);
		$this->itemOriginalRates           = BillItemTable::getItemRatesByElementIds($this->elements);

		self::setMaxCharactersPerLine();

		$this->calculateTotalByElementAndTypes($this->contractorRates, $this->itemOriginalQuantityByTypes, $this->itemOriginalRates);
	}

	public function generatePages()
	{
		$itemPages = array();

		$this->generateBillElementPages($this->elements, 1, array(), $itemPages);

		$pages = SplFixedArray::fromArray($itemPages);

		unset( $itemPages, $elements );

		return $pages;
	}

	/*
	 * We use SplFixedArray as data structure to boost performance. Since associative array cannot be used in SplFixedArray, we have to use indexes
	 * to get values. Below are indexes and what they represent as their values
	 *
	 * $row:
	 * 0 - id
	 * 1 - row index
	 * 2 - description
	 * 3 - level
	 * 4 - type
	 * 5 - unit
	 * 6 - rate
	 * 7 - quantity per unit by bill column settings
	 * 8 - include (bill column types)
	 */
	public function generateBillElementPages(Array $billElements, $pageCount, $ancestors, &$itemPages)
	{
		$elementTotals = array();

		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();
		$ancestors             = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

		$blankRow                                        = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]                = - 1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]           = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]       = null;//description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]             = 0;//level
		$blankRow[self::ROW_BILL_ITEM_TYPE]              = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_ESTIMATE_TOTALS]   = null;
		$blankRow[self::ROW_BILL_ITEM_CONTRACTOR_TOTALS] = null;

		//blank row
		array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
		$rowCount = 1;

		foreach ( $ancestors as $k => $row )
		{
			array_push($itemPages[$pageCount], $row);
			$rowCount += 1;
			unset( $row );
		}

		$ancestors = array();

		$itemIndex = 1;

		foreach ( $billElements as $x => $billElement )
		{
			$occupiedRows = Utilities::justify($billElements[$x]['description'], $this->MAX_CHARACTERS);

			if ( $this->descriptionFormat == self::DESC_FORMAT_ONE_LINE )
			{
				$oneLineDesc     = $occupiedRows[0];
				$occupiedRows    = new SplFixedArray(1);
				$occupiedRows[0] = $oneLineDesc;
			}

			$rowCount += count($occupiedRows);

			if ( $rowCount >= $maxRows )
			{
				unset( $occupiedRows );

				$pageCount ++;
				$this->generateBillElementPages($billElements, $pageCount, $ancestors, $itemPages, $elementTotals, true);
				break;
			}

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				$row                                        = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
				$row[self::ROW_BILL_ITEM_ROW_IDX]           = ( $key == 0 ) ? $itemIndex : null;
				$row[self::ROW_BILL_ITEM_DESCRIPTION]       = $occupiedRow;
				$row[self::ROW_BILL_ITEM_ID]                = null;
				$row[self::ROW_BILL_ITEM_ESTIMATE_TOTALS]   = null;
				$row[self::ROW_BILL_ITEM_CONTRACTOR_TOTALS] = null;

				if ( $key + 1 === $occupiedRows->count() )
				{
					$row[self::ROW_BILL_ITEM_ID]                = $billElement['id'];
					$row[self::ROW_BILL_ITEM_ESTIMATE_TOTALS]   = isset( $billElement['estimate_total'] ) ? $billElement['estimate_total'] : array();
					$row[self::ROW_BILL_ITEM_CONTRACTOR_TOTALS] = isset( $billElement['contractor_total'] ) ? $billElement['contractor_total'] : array();

					// calculate estimate overall total
					foreach ( $row[self::ROW_BILL_ITEM_ESTIMATE_TOTALS] as $billColumnSettingId => $estimateTotal )
					{
						if ( !isset( $this->estimateOverAllTotal[$billColumnSettingId] ) )
						{
							$this->estimateOverAllTotal[$billColumnSettingId] = 0;
						}

						$this->estimateOverAllTotal[$billColumnSettingId] += $estimateTotal;
					}

					// calculate contractor's overall total
					foreach ( $row[self::ROW_BILL_ITEM_CONTRACTOR_TOTALS] as $billColumnSettingId => $contractorArrayAmount )
					{
						foreach ( $contractorArrayAmount as $contractorId => $contractorTotal )
						{
							if ( !isset( $this->contractorOverAllTotal[$billColumnSettingId][$contractorId] ) )
							{
								$this->contractorOverAllTotal[$billColumnSettingId][$contractorId] = 0;
							}

							$this->contractorOverAllTotal[$billColumnSettingId][$contractorId] += $contractorTotal;
						}
					}
				}

				array_push($itemPages[$pageCount], $row);

				unset( $row );
			}

			//blank row
			array_push($itemPages[$pageCount], $blankRow);

			$rowCount ++;//plus one blank row;
			$itemIndex ++;

			unset( $billElements[$x], $occupiedRows );
		}
	}

	protected function setOrientationAndSize($orientation = false, $pageFormat = false)
	{
		if ( $orientation )
		{
			$this->orientation = $orientation;
			$this->setPageFormat($this->generatePageFormat(( $pageFormat ) ? $pageFormat : self::PAGE_FORMAT_A4));
		}
		else
		{
			$count = count($this->tendererIds);

			if ( $count <= 4 )
			{
				$this->orientation = ( $count <= 1 ) ? self::ORIENTATION_PORTRAIT : self::ORIENTATION_LANDSCAPE;
				$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
			}
			else
			{
				$this->orientation = self::ORIENTATION_LANDSCAPE;
				$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A3));
			}
		}
	}

	public function setPageFormat($pageFormat)
	{
		$this->pageFormat = $pageFormat;
	}

	protected function generatePageFormat($format)
	{
		switch (strtoupper($format))
		{
			/*
				*  For now we only handle A4 format. If there's necessity to handle other page
				* format we need to add to this method
				*/
			case self::PAGE_FORMAT_A4 :
				$width  = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
				$pf     = array(
					'page_format'       => self::PAGE_FORMAT_A4,
					'minimum-font-size' => $this->fontSize,
					'width'             => $width,
					'height'            => $height,
					'pdf_margin_top'    => 8,
					'pdf_margin_right'  => 10,
					'pdf_margin_bottom' => 3,
					'pdf_margin_left'   => 10
				);
				break;
			case self::PAGE_FORMAT_A3 :
				$width  = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 1000;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 1000 : 800;
				$pf     = array(
					'page_format'       => self::PAGE_FORMAT_A3,
					'minimum-font-size' => $this->fontSize,
					'width'             => $width,
					'height'            => $height,
					'pdf_margin_top'    => 8,
					'pdf_margin_right'  => 10,
					'pdf_margin_bottom' => 3,
					'pdf_margin_left'   => 10
				);
				break;
			// DEFAULT ISO A4
			default:
				$width  = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
				$pf     = array(
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

		return $pf;
	}

	public function setMaxCharactersPerLine()
	{
		$this->MAX_CHARACTERS = 56;

		if ( $this->fontSize == 10 )
		{
			$this->MAX_CHARACTERS = 64;
		}
	}

	public function getMaxRows()
	{
		$pageFormat = $this->getPageFormat();

		switch ($pageFormat['page_format'])
		{
			case self::PAGE_FORMAT_A4:
				if ( $this->orientation == self::ORIENTATION_PORTRAIT )
				{
					if ( count($this->tenderers) )
					{
						if ( count($this->tenderers) <= 1 )
						{
							$maxRows = 52;
						}
						else
						{
							$maxRows = 62;
						}
					}
					else
					{
						$maxRows = 52;
					}
				}
				else
				{
					$maxRows = 32;
				}
				break;
			default:
				$maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 107 : 53;
		}

		return $maxRows;
	}

	public function getElements()
	{
		if ( count($this->elementIds) === 0 )
		{
			return array();
		}

		return DoctrineQuery::create()
			->select('e.id, e.description, fc.column_name, fc.value, fc.final_value')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $this->bill->id)
			->andWhereIn('e.id', $this->elementIds)
			->addOrderBy('e.priority ASC')
			->fetchArray();
	}

	public function getTenderer()
	{
		$tenderer = array();

		if ( count($this->tendererIds) )
		{
			$stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, t.original_tender_value AS grand_total
            FROM " . TenderSettingTable::getInstance()->getTableName() . " t
            JOIN " . CompanyTable::getInstance()->getTableName() . " c ON c.id = t.awarded_company_id
            WHERE t.project_structure_id = " . $this->bill->root_id . " AND c.id IN (" . implode(',', $this->tendererIds) . ")");

			$stmt->execute();
			$selectedTenderer = $stmt->fetch(PDO::FETCH_ASSOC);

			if ( $selectedTenderer )
			{
				$selectedTenderer['selected'] = true;

				$tenderer[] = $selectedTenderer;
			}

			$companySqlStatement = ( $selectedTenderer['id'] > 0 ) ? "AND c.id <> " . $selectedTenderer['id'] : null;

			if ( count($this->elementIds) )
			{
				$stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, xref.id AS tender_company_id, xref.show,
				COALESCE(SUM(r.grand_total), 0) AS total
				FROM " . CompanyTable::getInstance()->getTableName() . " c
				JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
				LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
				WHERE xref.project_structure_id = " . $this->project->id . "
				AND c.id IN (" . implode(', ', $this->tendererIds) . ") {$companySqlStatement}
				AND c.deleted_at IS NULL GROUP BY c.id, xref.show, xref.id ORDER BY c.id ASC");

				$stmt->execute();

				foreach ( $stmt->fetchAll(PDO::FETCH_ASSOC) as $contractor )
				{
					$tenderer[] = $contractor;
				}
			}
		}

		return $tenderer;
	}

	private function calculateTotalByElementAndTypes($contractorRates, $itemOriginalQuantityByTypes, $itemOriginalRates)
	{
		foreach ( $this->billColumnSettings as $column )
		{
			$data['unitTypes'][] = array(
				'id'   => $column->id,
				'name' => $column->name
			);

			foreach ( $this->elements as $key => $element )
			{
				$elementId            = $element['id'];
				$itemQuantities       = $itemOriginalQuantityByTypes[$column->id][$elementId];
				$elementEstimateTotal = 0;

				// will calculate item's estimate amount first
				foreach ( $itemQuantities as $itemId => $itemQuantity )
				{
					$itemRate = isset( $itemOriginalRates[$itemId] ) ? $itemOriginalRates[$itemId] : 0;

					$elementEstimateTotal += $itemQuantity * $itemRate;
				}

				$this->elements[$key]['estimate_total'][$column->id] = $elementEstimateTotal;

				// after that only count contractor's total
				foreach ( $contractorRates as $contractorId => $contractor )
				{
					$contractorTotal = 0;

					foreach ( $itemQuantities as $itemId => $itemQuantity )
					{
						if ( !isset( $contractor[$element['id']][$itemId] ) )
						{
							continue;
						}

						$contractorRate = $contractor[$element['id']][$itemId];

						$contractorTotal += $itemQuantity * $contractorRate;
					}

					$this->elements[$key]['contractor_total'][$column->id][$contractorId] = $contractorTotal;
				}
			}
		}
	}

}