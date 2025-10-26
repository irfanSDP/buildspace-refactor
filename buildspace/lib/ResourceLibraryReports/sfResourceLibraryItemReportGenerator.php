<?php

class sfResourceLibraryItemReportGenerator extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	protected $items = array();

	protected $resourceTrades = array();

	const ROW_BILL_ITEM_ID          = 0;
	const ROW_BILL_ITEM_ROW_IDX     = 1;
	const ROW_BILL_ITEM_DESCRIPTION = 2;
	const ROW_BILL_ITEM_TYPE        = 4;
	const ROW_BILL_ITEM_CONSTANT    = 5;
	const ROW_BILL_ITEM_UNIT        = 7;
	const ROW_BILL_ITEM_RATE        = 8;
	const ROW_BILL_ITEM_WASTAGE     = 10;

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
		$this->itemIndex = 0;

		$this->resetPageRowCount();
		$this->setMaxCharactersPerLine();

		$this->generateBillPages($this->items, $itemPages, array());

		$pages = SplFixedArray::fromArray($itemPages);

		unset($itemPages, $this->resourceTrades, $this->items);

		return $pages;
	}

	public function generateBillPages(Array $buildUpItems, &$itemPages, $billTotals)
	{
		if ( ! isset ($itemPages[$this->pageCount]) )
		{
			$itemPages[$this->pageCount] = array();

			//blank row
			array_push($itemPages[$this->pageCount], $this->setBlankRow());//starts with a blank row
		}

		$maxRows = $this->getMaxRows();

		foreach($buildUpItems as $x => $item)
		{
			$occupiedRows = Utilities::justify($buildUpItems[$x]['description'], $this->MAX_CHARACTERS);

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
					if($key == 0 && $item['type'] != ResourceItem::TYPE_HEADER)
					{
						$this->itemIndex++;
					}

					$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
					$row[self::ROW_BILL_ITEM_ROW_IDX]     = ($key == 0 && $item['type'] != ResourceItem::TYPE_HEADER) ? $this->itemIndex : null;
					$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
					$row[self::ROW_BILL_ITEM_TYPE]        = $item['type'];
					$row[self::ROW_BILL_ITEM_ID]          = null;
					$row[self::ROW_BILL_ITEM_CONSTANT]    = null;
					$row[self::ROW_BILL_ITEM_UNIT]        = null;
					$row[self::ROW_BILL_ITEM_RATE]        = null;
					$row[self::ROW_BILL_ITEM_WASTAGE]     = null;

					if($key+1 == $occupiedRows->count())
					{
						$constant = isset($item['constant-final_value']) ? $item['constant-final_value'] : 0;
						$rate     = isset($item['rate-final_value']) ? $item['rate-final_value'] : 0;
						$wastage  = isset($item['wastage-final_value']) ? $item['wastage-final_value'] : 0;

						$constantValue = array(
							'value'       => $constant,
							'has_formula' => ( $item['constant-value'] != $item['constant-final_value'] ) ? true : false,
						);

						$rateValue = array(
							'value'       => $rate,
							'has_formula' => ( $item['rate-value'] != $item['rate-final_value'] ) ? true : false,
						);

						$wastageValue = array(
							'value'       => $wastage,
							'has_formula' => ( $item['wastage-value'] != $item['wastage-final_value'] ) ? true : false,
						);

						$row[self::ROW_BILL_ITEM_ID]       = $item['id'];
						$row[self::ROW_BILL_ITEM_CONSTANT] = $constantValue;
						$row[self::ROW_BILL_ITEM_RATE]     = $rateValue;
						$row[self::ROW_BILL_ITEM_WASTAGE]  = $wastageValue;
						$row[self::ROW_BILL_ITEM_UNIT]     = $item['uom_symbol'];

						unset($constantValue, $rateValue, $wastageValue, $number, $constant, $quantity, $rate, $wastage);
					}

					array_push($itemPages[$this->pageCount], $row);

					unset($row);
				}

				//blank row
				array_push($itemPages[$this->pageCount], $this->setBlankRow());

				$this->rowCount++;//plus one blank row;

				unset($buildUpItems[$x], $occupiedRows);
			}
			else
			{
				unset($occupiedRows);

				$this->pageCount++;
				$this->resetPageRowCount();
				$this->generateBillPages($buildUpItems, $itemPages, $billTotals, true);
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

	public function setItems(array $buildUpQuantityItems)
	{
		$this->items = $buildUpQuantityItems;

		unset($buildUpQuantityItems);
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
		return 34;
	}

	public function resetPageRowCount()
	{
		return $this->rowCount = 1;
	}

	public function setBlankRow()
	{
		$blankRow                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]          = -1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]     = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION] = null;//description
		$blankRow[self::ROW_BILL_ITEM_TYPE]        = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_CONSTANT]    = null;
		$blankRow[self::ROW_BILL_ITEM_UNIT]        = null;
		$blankRow[self::ROW_BILL_ITEM_RATE]        = null;
		$blankRow[self::ROW_BILL_ITEM_WASTAGE]     = null;

		return $blankRow;
	}

}