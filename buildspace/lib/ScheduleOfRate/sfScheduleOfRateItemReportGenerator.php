<?php

/**
 * @property int itemIndex
 */
class sfScheduleOfRateItemReportGenerator extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	protected $scheduleOfRate;

	protected $items;

	const TOTAL_BILL_ITEM_PROPERTY = 9;
	const ROW_BILL_ITEM_ID = 0;
	const ROW_BILL_ITEM_ROW_IDX = 1;
	const ROW_BILL_ITEM_DESCRIPTION = 2;
	const ROW_BILL_ITEM_TYPE = 4;
	const ROW_FORMULATED_COLUMNS = 5;
	const ROW_BILL_ITEM_RATE = 6;
	const ROW_BILL_ITEM_UNIT = 8;

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

	public function setItems($items)
	{
		$this->items = $items;

		unset($items);
	}

	public function generatePages()
	{
		$itemPages = array();

		$this->itemIndex = 1;

		$this->setMaxCharactersPerLine();

		$this->generateBillPages($this->items, 1, array(), $itemPages);

		$pages = SplFixedArray::fromArray($itemPages);

		unset($itemPages, $this->items);

		return $pages;
	}

	public function generateBillPages(Array $billItems, $pageCount, $ancestors, &$itemPages)
	{
		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();
		$ancestors             = (is_array($ancestors) && count($ancestors)) ? $ancestors : array();

		$blankRow                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]           = -1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null;//description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
		$blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_UNIT]         = null;
		$blankRow[self::ROW_BILL_ITEM_RATE]         = null;

		//blank row
		array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
		$rowCount = 1;

		foreach($ancestors as $k => $row)
		{
			array_push($itemPages[$pageCount], $row);
			$rowCount += 1;
			unset($row);
		}

		$ancestors    = array();
		$itemIndex    = 1;
		$counterIndex = 0; //display item's index in BQ

		foreach($billItems as $x => $sorItem)
		{
			$occupiedRows = Utilities::justify($sorItem['description'], $this->MAX_CHARACTERS);

			if ($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
			{
				$oneLineDesc     = $occupiedRows[0];
				$occupiedRows    = new SplFixedArray(1);
				$occupiedRows[0] = $oneLineDesc;
			}

			$rowCount += count($occupiedRows);

			if($rowCount <= $maxRows)
			{
				foreach($occupiedRows as $key => $occupiedRow)
				{
					if($key == 0 && $sorItem['type'] != ScheduleOfRateItem::TYPE_HEADER)
					{
						$counterIndex++;
					}

					$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
					$row[self::ROW_BILL_ITEM_ID]          = - 1; //id
					$row[self::ROW_BILL_ITEM_ROW_IDX]     = ($key == 0 && $sorItem['type'] != ScheduleOfRateItem::TYPE_HEADER) ? $counterIndex : null;
					$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow; //description
					$row[self::ROW_BILL_ITEM_LEVEL]       = $sorItem['level'];
					$row[self::ROW_BILL_ITEM_TYPE]        = $sorItem['type'];
					$row[self::ROW_BILL_ITEM_UNIT]        = null;
					$row[self::ROW_BILL_ITEM_RATE]        = null;

					if($key+1 == $occupiedRows->count() && $sorItem['type'] != ScheduleOfRateItem::TYPE_HEADER)
					{
						$row[self::ROW_BILL_ITEM_ID]      = $sorItem['id'];
						$row[self::ROW_BILL_ITEM_UNIT]    = $sorItem['uom_symbol'];
						$row[self::ROW_BILL_ITEM_RATE]    = $sorItem['rate'];
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
				$this->generateBillPages($billItems, $pageCount, $ancestors, $itemPages);
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

	public function getMaxRows()
	{
		$row = 56;

		return $row;
	}

	public function getPrintSetting()
	{
		return BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, TRUE);
	}

}