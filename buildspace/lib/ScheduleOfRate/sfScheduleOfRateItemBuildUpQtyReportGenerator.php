<?php

class sfScheduleOfRateItemBuildUpQtyReportGenerator extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	protected $buildUpQuantityItems = array();

	/**
	 * @var ScheduleOfRate
	 */
	protected $scheduleOfRate;

	protected $resourceTrades = array();

	const ROW_BILL_ITEM_ID          = 0;
	const ROW_BILL_ITEM_ROW_IDX     = 1;
	const ROW_BILL_ITEM_DESCRIPTION = 2;
	const ROW_BILL_ITEM_NUMBER      = 3;
	const ROW_BILL_ITEM_TYPE        = 4;
	const ROW_BILL_ITEM_CONSTANT    = 5;
	const ROW_BILL_ITEM_QTY         = 6;
	const ROW_BILL_ITEM_UNIT        = 7;
	const ROW_BILL_ITEM_RATE        = 8;
	const ROW_BILL_ITEM_TOTAL       = 9;
	const ROW_BILL_ITEM_WASTAGE     = 10;
	const ROW_BILL_ITEM_LINE_TOTAL  = 11;

	const TOTAL_BILL_ITEM_PROPERTY  = 12;

	protected $pageCount            = 0;

	public function __construct(ScheduleOfRate $scheduleOfRate, $descriptionFormat = sfBuildspaceReportBillPageGenerator::DESC_FORMAT_FULL_LINE)
	{
		$this->scheduleOfRate    = $scheduleOfRate;
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

		// for manual type item
		if ( count($this->resourceTrades) == 0 )
		{
			$itemPages[1] = array();

			array_push($itemPages[1], $this->setBlankRow());
		}
		else
		{
			foreach ( $this->resourceTrades as $resourceTrade )
			{
				$ancestors = $this->setupResourceCategoryHeader($resourceTrade);

				if ( ! isset($this->buildUpQuantityItems[$resourceTrade['id']]) )
				{
					continue;
				}

				$this->generateBillPages($this->buildUpQuantityItems[$resourceTrade['id']], $ancestors, $itemPages, array());
			}
		}

		$pages = SplFixedArray::fromArray($itemPages);

		unset($itemPages, $this->resourceTrades, $this->buildUpQuantityItems);

		return $pages;
	}

	public function generateBillPages(Array $buildUpItems, $ancestors, &$itemPages, $billTotals)
	{
		if ( ! isset ($itemPages[$this->pageCount]) )
		{
			$itemPages[$this->pageCount] = array();

			//blank row
			array_push($itemPages[$this->pageCount], $this->setBlankRow());//starts with a blank row
		}

		$maxRows   = $this->getMaxRows();
		$ancestors = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

		foreach($ancestors as $k => $row)
		{
			array_push($itemPages[$this->pageCount], $row);
			$this->rowCount += 1;
			unset($row);
		}

		foreach($buildUpItems as $x => $item)
		{
			$occupiedRows = Utilities::justify($buildUpItems[$x]['description'], $this->MAX_CHARACTERS);

			if($this->descriptionFormat == sfBuildspaceReportBillPageGenerator::DESC_FORMAT_ONE_LINE)
			{
				$oneLineDesc = $occupiedRows[0];
				$occupiedRows = new SplFixedArray(1);
				$occupiedRows[0] = $oneLineDesc;
			}

			$this->rowCount += count($occupiedRows);

			if($this->rowCount <= $maxRows)
			{
				foreach($occupiedRows as $key => $occupiedRow)
				{
					$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
					$row[self::ROW_BILL_ITEM_ROW_IDX]     = ($key == 0) ? $this->itemIndex : null;
					$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
					$row[self::ROW_BILL_ITEM_TYPE]        = self::ROW_TYPE_BLANK;
					$row[self::ROW_BILL_ITEM_ID]          = null;
					$row[self::ROW_BILL_ITEM_NUMBER]      = null; // factor
					$row[self::ROW_BILL_ITEM_CONSTANT]    = null;
					$row[self::ROW_BILL_ITEM_QTY]         = null;
					$row[self::ROW_BILL_ITEM_UNIT]        = null;
					$row[self::ROW_BILL_ITEM_RATE]        = null;
					$row[self::ROW_BILL_ITEM_TOTAL]       = null; // total
					$row[self::ROW_BILL_ITEM_WASTAGE]     = null;
					$row[self::ROW_BILL_ITEM_LINE_TOTAL]  = null;

					if($key+1 == $occupiedRows->count())
					{
						$number   = Utilities::array_recursive_search($item['FormulatedColumns'], 'column_name', 'number');
						$constant = Utilities::array_recursive_search($item['FormulatedColumns'], 'column_name', 'constant');
						$quantity = Utilities::array_recursive_search($item['FormulatedColumns'], 'column_name', 'quantity');
						$rate     = Utilities::array_recursive_search($item['FormulatedColumns'], 'column_name', 'rate');
						$wastage  = Utilities::array_recursive_search($item['FormulatedColumns'], 'column_name', 'wastage');

						$row[self::ROW_BILL_ITEM_ID]         = $item['id'];
						$row[self::ROW_BILL_ITEM_NUMBER]     = ( isset($number[0]) ) ? $number[0] : 0;
						$row[self::ROW_BILL_ITEM_CONSTANT]   = ( isset($constant[0]) ) ? $constant[0] : 0;
						$row[self::ROW_BILL_ITEM_QTY]        = ( isset($quantity[0]) ) ? $quantity[0] : 0;
						$row[self::ROW_BILL_ITEM_RATE]       = ( isset($rate[0]) ) ? $rate[0] : 0;
						$row[self::ROW_BILL_ITEM_WASTAGE]    = ( isset($wastage[0]) ) ? $wastage[0] : 0;
						$row[self::ROW_BILL_ITEM_UNIT]       = isset($item['UnitOfMeasurement']) ? $item['UnitOfMeasurement']['symbol'] : NULL;
						$row[self::ROW_BILL_ITEM_TOTAL]      = $item['total'];
						$row[self::ROW_BILL_ITEM_LINE_TOTAL] = $item['line_total'];
						$row[self::ROW_BILL_ITEM_TYPE]       = 999999;

						unset($number, $constant, $quantity, $rate, $wastage);
					}

					array_push($itemPages[$this->pageCount], $row);

					unset($row);
				}

				//blank row
				array_push($itemPages[$this->pageCount], $this->setBlankRow());

				$this->rowCount++;//plus one blank row;
				$this->itemIndex++;

				unset($buildUpItems[$x], $occupiedRows);
			}
			else
			{
				unset($occupiedRows);

				$this->pageCount++;
				$this->resetPageRowCount();
				$this->generateBillPages($buildUpItems, $ancestors, $itemPages, $billTotals, true);
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

	public function setBuildUpQuantityItems(array $buildUpQuantityItems)
	{
		$this->buildUpQuantityItems = $buildUpQuantityItems;

		unset($buildUpQuantityItems);
	}

	public function setupBillItemHeader($billItem)
	{
		$itemRows = array();

		array_push($itemRows, $this->setBlankRow());

		$occupiedRows = Utilities::justify($billItem['description'], $this->MAX_CHARACTERS);

		foreach($occupiedRows as $key => $occupiedRow)
		{
			$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
			$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
			$row[self::ROW_BILL_ITEM_ID]          = null;
			$row[self::ROW_BILL_ITEM_RATE]        = null;
			$row[self::ROW_BILL_ITEM_TOTAL]       = null;
			$row[self::ROW_BILL_ITEM_WASTAGE]     = null;
			$row[self::ROW_BILL_ITEM_LINE_TOTAL]  = null;

			if($key+1 == $occupiedRows->count())
			{
				$row[self::ROW_BILL_ITEM_ID]         = $billItem['id'];
				$row[self::ROW_BILL_ITEM_LINE_TOTAL] = 0;
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
		return 28;
	}

	public function setResourceTrades($resourceTrades)
	{
		$this->resourceTrades = $resourceTrades;
	}

	public function resetPageRowCount()
	{
		return $this->rowCount = 1;
	}

	public function setupResourceCategoryHeader($resourceTrade)
	{
		$itemRows = array();

		$occupiedRows = Utilities::justify($resourceTrade['name'], $this->MAX_CHARACTERS);

		foreach($occupiedRows as $key => $occupiedRow)
		{
			$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
			$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
			$row[self::ROW_BILL_ITEM_ID]          = null;
			$row[self::ROW_BILL_ITEM_TYPE]        = ResourceItem::TYPE_HEADER;
			$row[self::ROW_BILL_ITEM_RATE]        = null;
			$row[self::ROW_BILL_ITEM_TOTAL]       = null;
			$row[self::ROW_BILL_ITEM_WASTAGE]     = null;
			$row[self::ROW_BILL_ITEM_LINE_TOTAL]  = null;

			array_push($itemRows, $row);

			unset($row);
		}

		//blank row
		array_push($itemRows, $this->setBlankRow());

		return $itemRows;
	}

	public function setBlankRow()
	{
		$blankRow                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]          = -1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]     = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION] = null;//description
		$blankRow[self::ROW_BILL_ITEM_TYPE]        = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_NUMBER]      = null;
		$blankRow[self::ROW_BILL_ITEM_CONSTANT]    = null;
		$blankRow[self::ROW_BILL_ITEM_QTY]         = null;
		$blankRow[self::ROW_BILL_ITEM_UNIT]        = null;
		$blankRow[self::ROW_BILL_ITEM_RATE]        = null;
		$blankRow[self::ROW_BILL_ITEM_TOTAL]       = null;
		$blankRow[self::ROW_BILL_ITEM_WASTAGE]     = null;
		$blankRow[self::ROW_BILL_ITEM_LINE_TOTAL]  = null;

		return $blankRow;
	}

}