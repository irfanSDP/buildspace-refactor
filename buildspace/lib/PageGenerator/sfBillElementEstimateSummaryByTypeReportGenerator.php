<?php

class sfBillElementEstimateSummaryByTypeReportGenerator extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	const ROW_BILL_ITEM_ID             = 0;
	const ROW_BILL_ITEM_ROW_IDX        = 1;
	const ROW_BILL_ITEM_DESCRIPTION    = 2;
	const ROW_BILL_ITEM_LEVEL          = 3;
	const ROW_BILL_ITEM_TYPE           = 4;
	const ROW_BILL_ITEM_TOTAL_PER_UNIT = 5;
	const ROW_BILL_ITEM_PERCENTAGE     = 6;
	const ROW_BILL_ITEM_TOTAL_COST     = 7;

	protected $bill;

	protected $percentageTotal         = 0;
	protected $totalCostTotal          = 0;
	protected $overallElementTotal     = 0;

	public function __construct(ProjectStructure $bill, $descriptionFormat = sfBuildspaceReportBillPageGenerator::DESC_FORMAT_FULL_LINE)
	{
		$this->bill              = $bill;
		$this->printSettings     = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, TRUE);
		$this->fontSize          = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType          = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings      = $this->printSettings['headSettings'];
		$this->descriptionFormat = $descriptionFormat;
		$this->currency          = $bill->MainInformation->Currency;
	}

	public function generatePages()
	{
		$pageNumberDescription = 'Page No. ';
		$pages                 = array();
		$elementPages          = array();

		$this->percentageTotal     = 0;
		$this->totalCostTotal      = 0;
		$this->overallElementTotal = 0;

		$this->setMaxCharactersPerLine();

		$this->itemIndex = 1;

		$this->generateElementPages($this->elements, 1, array(), $elementPages, array());

		$pages = SplFixedArray::fromArray($elementPages);

		unset($elementPages, $this->elements);

		return $pages;
	}

	public function generateElementPages(Array $elements, $pageCount, $ancestors, &$elementPages, $billTotals, $newPage = false)
	{
		$elementPages[$pageCount] = array();
		$layoutSettings           = $this->printSettings['layoutSetting'];
		$maxRows                  = $this->getMaxRows();
		$ancestors                = (is_array($ancestors) && count($ancestors)) ? $ancestors : array();

		$blankRow                                     = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]             = -1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]        = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]    = null;//description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]          = 0;//level
		$blankRow[self::ROW_BILL_ITEM_TYPE]           = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_TOTAL_PER_UNIT] = NULL;

		//blank row
		array_push($elementPages[$pageCount], $blankRow);//starts with a blank row
		$rowCount = 1;

		foreach($ancestors as $k => $row)
		{
			array_push($elementPages[$pageCount], $row);
			$rowCount += 1;
			unset($row);
		}

		$ancestors = array();

		foreach($elements as $x => $element)
		{
			$occupiedRows = Utilities::justify($elements[$x]['description'], $this->MAX_CHARACTERS);

			if($this->descriptionFormat == sfBuildspaceReportBillPageGenerator::DESC_FORMAT_ONE_LINE)
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
					$row                                     = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
					$row[self::ROW_BILL_ITEM_ROW_IDX]        = ($key == 0) ? $element['priority'] : null;
					$row[self::ROW_BILL_ITEM_DESCRIPTION]    = $occupiedRow;
					$row[self::ROW_BILL_ITEM_ID]             = null;
					$row[self::ROW_BILL_ITEM_TOTAL_PER_UNIT] = 0;

					if($key+1 == $occupiedRows->count())
					{
						$totalPerUnit    = $element[$this->column->id.'-total_per_unit'];
						$total           = $element[$this->column->id.'-total'];
						$elementSumTotal = $element[$this->column->id.'-element_sum_total'];

						$row[self::ROW_BILL_ITEM_ID]             = $element['id'];
						$row[self::ROW_BILL_ITEM_TOTAL_PER_UNIT] = Utilities::prelimRounding($totalPerUnit);
						$row[self::ROW_BILL_ITEM_PERCENTAGE]     = Utilities::prelimRounding(($elementSumTotal == 0) ? 0 : $total / $elementSumTotal * 100);
						$row[self::ROW_BILL_ITEM_TOTAL_COST]     = Utilities::prelimRounding($this->column->getTotalCostPerFloorArea($totalPerUnit));

						$this->percentageTotal     += $row[self::ROW_BILL_ITEM_PERCENTAGE];
						$this->totalCostTotal      += $row[self::ROW_BILL_ITEM_TOTAL_COST];
						$this->overallElementTotal += $row[self::ROW_BILL_ITEM_TOTAL_PER_UNIT];

						unset($totalPerUnit, $total, $elementSumTotal);
					}

					array_push($elementPages[$pageCount], $row);

					unset($row);
				}

				//blank row
				array_push($elementPages[$pageCount], $blankRow);

				$rowCount++;//plus one blank row;
				$this->itemIndex++;
				$newPage = false;

				unset($elements[$x], $occupiedRows);
			}
			else
			{
				unset($occupiedRows);

				$pageCount++;
				$this->generateElementPages($elements, $pageCount, $ancestors, $elementPages, $billTotals, true);
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
			// $count = count($this->tendererIds);

			// if($count <= 4)
			// {
			// 	$this->orientation = ($count <= 1) ? self::ORIENTATION_PORTRAIT : self::ORIENTATION_LANDSCAPE;
			// 	$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
			// }
			// else
			// {
			// 	$this->orientation = self::ORIENTATION_LANDSCAPE;
			// 	$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A3));
			// }

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
		return 58;
	}

	public function setElements($elements)
	{
		$this->elements = $elements;
	}

	public function setBillColumn($column)
	{
		$this->column = $column;
	}

	public function getPercentageTotal()
	{
		return $this->percentageTotal;
	}

	public function getTotalCostTotal()
	{
		return $this->totalCostTotal;
	}

	public function getOverallElementTotal()
	{
		return $this->overallElementTotal;
	}

}