<?php

class sfResourceLibrarySupplierRatesReportGenerator extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	protected $supplierRates = array();

	protected $selectedSupplierRates = array();

	const ROW_BILL_ITEM_ID = 0;
	const ROW_BILL_ITEM_ROW_IDX = 1;
	const ROW_BILL_ITEM_DESCRIPTION = 2;
	const ROW_BILL_PROJECT_TITLE = 3;
	const ROW_BILL_ITEM_TYPE = 4;
	const ROW_BILL_ITEM_COUNTRY = 5;
	const ROW_BILL_ITEM_STATE = 6;
	const ROW_BILL_ITEM_IS_SELECTED = 7;
	const ROW_BILL_ITEM_RATE = 8;
	const ROW_BILL_ITEM_TOTAL = 9;
	const ROW_BILL_ITEM_REMARKS = 10;
	const ROW_BILL_ITEM_LAST_UPDATED = 11;

	const TOTAL_BILL_ITEM_PROPERTY  = 12;

	protected $pageCount            = 0;

	public function __construct($descriptionFormat = sfBuildspaceReportBillPageGenerator::DESC_FORMAT_FULL_LINE)
	{
		$this->descriptionFormat = $descriptionFormat;
		$this->printSettings     = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, TRUE);
		$this->fontSize          = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType          = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings      = $this->printSettings['headSettings'];
		$this->descriptionFormat = $descriptionFormat;
		$this->currency          = NULL;
	}

	public function generatePages()
	{
		$itemPages       = array();
		$this->pageCount = 0;
		$this->itemIndex = 1;

		$this->resetPageRowCount();
		$this->setMaxCharactersPerLine();

		$this->generateBillPages($this->supplierRates, $itemPages, array());

		$pages = SplFixedArray::fromArray($itemPages);

		unset($itemPages, $this->supplierRates);

		return $pages;
	}

	public function generateBillPages(Array $supplierRates, &$itemPages, $billTotals)
	{
		if ( ! isset ($itemPages[$this->pageCount]) )
		{
			$itemPages[$this->pageCount] = array();

			//blank row
			array_push($itemPages[$this->pageCount], $this->setBlankRow());//starts with a blank row
		}

		$maxRows = $this->getMaxRows();

		foreach($supplierRates as $x => $item)
		{
			$occupiedRows = Utilities::justify($supplierRates[$x]['company_name'], $this->MAX_CHARACTERS);

			if($this->descriptionFormat == sfBuildspaceReportBillPageGenerator::DESC_FORMAT_ONE_LINE)
			{
				$oneLineDesc     = $occupiedRows[0];
				$occupiedRows    = new SplFixedArray(1);
				$occupiedRows[0] = $oneLineDesc;
			}

			$this->rowCount += count($occupiedRows);

			if($this->rowCount <= $maxRows)
			{
				foreach($occupiedRows as $key => $occupiedRow)
				{
					$row                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
					$row[self::ROW_BILL_ITEM_ROW_IDX]      = ( $key == 0 ) ? $this->itemIndex : null;
					$row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;
					$row[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK;
					$row[self::ROW_BILL_ITEM_ID]           = null;
					$row[self::ROW_BILL_PROJECT_TITLE]     = null; // factor
					$row[self::ROW_BILL_ITEM_COUNTRY]      = null;
					$row[self::ROW_BILL_ITEM_STATE]        = null;
					$row[self::ROW_BILL_ITEM_IS_SELECTED]  = false;
					$row[self::ROW_BILL_ITEM_RATE]         = null;
					$row[self::ROW_BILL_ITEM_REMARKS]      = null;
					$row[self::ROW_BILL_ITEM_LAST_UPDATED] = null;

					if($key+1 == $occupiedRows->count())
					{
						$row[self::ROW_BILL_ITEM_ID]           = $item['id'];
						$row[self::ROW_BILL_PROJECT_TITLE]     = $item['project_title'];
						$row[self::ROW_BILL_ITEM_COUNTRY]      = $item['country'];
						$row[self::ROW_BILL_ITEM_STATE]        = $item['state'];
						$row[self::ROW_BILL_ITEM_RATE]         = $item['rate'];
						$row[self::ROW_BILL_ITEM_REMARKS]      = $item['remarks'];
						$row[self::ROW_BILL_ITEM_IS_SELECTED]  = in_array($item['id'], $this->selectedSupplierRates) ? true : false;
						$row[self::ROW_BILL_ITEM_LAST_UPDATED] = $item['rate_last_updated_at'];
						$row[self::ROW_BILL_ITEM_TYPE]         = 99999;

						unset($number, $constant, $quantity, $rate, $wastage);
					}

					array_push($itemPages[$this->pageCount], $row);

					unset($row);
				}

				//blank row
				array_push($itemPages[$this->pageCount], $this->setBlankRow());

				$this->rowCount++;//plus one blank row;
				$this->itemIndex++;

				unset($supplierRates[$x], $occupiedRows);
			}
			else
			{
				unset($occupiedRows);

				$this->pageCount++;
				$this->resetPageRowCount();
				$this->generateBillPages($supplierRates, $itemPages, $billTotals, true);
				break;
			}
		}
	}

	public function setOrientationAndSize($orientation = false, $pageFormat = false)
	{
		if($orientation)
		{
			$this->orientation = $orientation;
			$this->setPageFormat($this->generatePageFormat( ($pageFormat) ? $pageFormat : self::PAGE_FORMAT_A4 ));
		}
		else
		{
			$this->orientation = self::ORIENTATION_LANDSCAPE;
			$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A3));
		}
	}

	protected function generatePageFormat($format)
	{
		switch(strtoupper($format))
		{
			/*
			*  For now we only handle A4 format. If there's necessity to handle other page
			* format we need to add to this method
			*/
			case self::PAGE_FORMAT_A4 :
				$width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
				$pf = array(
					'page_format'       => self::PAGE_FORMAT_A4,
					'minimum-font-size' => $this->fontSize,
					'width'             => $width,
					'height'            => $height,
					'pdf_margin_top'    => 8,
					'pdf_margin_right'  => 10,
					'pdf_margin_bottom' => 1,
					'pdf_margin_left'   => 10
				);
				break;
			case self::PAGE_FORMAT_A3 :
				$width = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 1000;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 1000 : 800;
				$pf = array(
					'page_format'       => self::PAGE_FORMAT_A3,
					'minimum-font-size' => $this->fontSize,
					'width'             => $width,
					'height'            => $height,
					'pdf_margin_top'    => 8,
					'pdf_margin_right'  => 10,
					'pdf_margin_bottom' => 1,
					'pdf_margin_left'   => 10
				);
				break;
			// DEFAULT ISO A4
			default:
				$width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
				$pf = array(
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
		}

		return $pf;
	}

	public function setSupplierRates(array $supplierRates)
	{
		$this->supplierRates = $supplierRates;

		unset($supplierRates);
	}

	public function setSelectedSupplierRatesInfo(array $selectedSupplierRatesBySelectedId)
	{
		$this->selectedSupplierRates = $selectedSupplierRatesBySelectedId;

		unset($selectedSupplierRatesBySelectedId);
	}

	public function setupResourceItemHeader($billItem)
	{
		$itemRows = array();

		array_push($itemRows, $this->setBlankRow());

		$occupiedRows = Utilities::justify($billItem['description'], $this->MAX_CHARACTERS);

		foreach($occupiedRows as $key => $occupiedRow)
		{
			$row                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
			$row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;
			$row[self::ROW_BILL_ITEM_ID]           = null;
			$row[self::ROW_BILL_ITEM_RATE]         = null;
			$row[self::ROW_BILL_ITEM_REMARKS]      = null;
			$row[self::ROW_BILL_ITEM_LAST_UPDATED] = null;

			if($key+1 == $occupiedRows->count())
			{
				$row[self::ROW_BILL_ITEM_ID] = $billItem['id'];
			}

			array_push($itemRows, $row);

			unset($row);
		}

		//blank row
		array_push($itemRows, $this->setBlankRow());

		return $itemRows;
	}

	public function setMaxCharactersPerLine()
	{
		if ( $this->fontSize == 10 )
		{
			$this->MAX_CHARACTERS = 64;
		}
		else
		{
			$this->MAX_CHARACTERS = 56;
		}

	}

	public function getMaxRows()
	{
		return 35;
	}

	public function resetPageRowCount()
	{
		return $this->rowCount = 1;
	}

	public function setBlankRow()
	{
		$blankRow                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]           = - 1; //id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null; //row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null; //description
		$blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK; //type
		$blankRow[self::ROW_BILL_PROJECT_TITLE]     = null;
		$blankRow[self::ROW_BILL_ITEM_COUNTRY]      = null;
		$blankRow[self::ROW_BILL_ITEM_STATE]        = null;
		$blankRow[self::ROW_BILL_ITEM_IS_SELECTED]  = false;
		$blankRow[self::ROW_BILL_ITEM_RATE]         = null;
		$blankRow[self::ROW_BILL_ITEM_REMARKS]      = null;
		$blankRow[self::ROW_BILL_ITEM_LAST_UPDATED] = null;

		return $blankRow;
	}

}