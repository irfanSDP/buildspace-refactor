<?php

class sfBillItemAmountIncludingQty2ReportGenerator extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	protected $bill;

	protected $currentColumn = array();

	protected $items = array();

	public $currentQtyOneAmount = 0;
	public $currentQtyTwoAmount = 0;

	const QTY_1 = 6;
	const QTY_2 = 8;
	const DIFFERENCE = 7;

	public function __construct(ProjectStructure $project, ProjectStructure $bill, $descriptionFormat = sfBuildspaceReportBillPageGenerator::DESC_FORMAT_FULL_LINE)
	{
		$this->bill              = $bill;
		$this->printSettings     = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
		$this->fontSize          = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType          = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings      = $this->printSettings['headSettings'];
		$this->descriptionFormat = $descriptionFormat;
		$this->currency          = $project->MainInformation->Currency;

		unset( $bill, $descriptionFormat );
	}

	public function generatePages()
	{
		$itemPages = array();
		$items     = $this->items;

		$this->itemIndex           = 1;
		$this->currentQtyOneAmount = 0;
		$this->currentQtyTwoAmount = 0;

		$this->setMaxCharactersPerLine();

		$this->generateBillPages($items, 1, $itemPages);

		$pages = SplFixedArray::fromArray($itemPages);

		unset( $itemPages, $items );

		return $pages;
	}

	public function generateBillPages(Array $items, $pageCount, array &$itemPages)
	{
		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();

		//blank row
		array_push($itemPages[$pageCount], $this->generateBlankRow());//starts with a blank row

		$rowCount = 1;

		foreach ( $items as $x => &$item )
		{
			$maxCharacters = $this->MAX_CHARACTERS;

			/*
			* Create extra rows for BillItem::TYPE_ITEM_PC_RATE;
			*/
			if ( $item['type'] == BillItem::TYPE_ITEM_PC_RATE )
			{
				$primeCostRateRows = $this->generatePrimeCostRateRows($item['id']);

				$rowCount += count($primeCostRateRows);
			}

			$occupiedRows = Utilities::justify($item['description'], $maxCharacters);

			if ( $this->descriptionFormat == sfBuildspaceReportBillPageGenerator::DESC_FORMAT_ONE_LINE )
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
				$this->generateBillPages($items, $pageCount, $itemPages);
				break;
			}

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
				$row[self::ROW_BILL_ITEM_ROW_IDX]     = ( $key == 0 && $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID ) ? $item['bill_ref_element_no'] . '/' . $item['bill_ref_page_no'] . ' ' . $item['bill_ref_char'] : null;
				$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
				$row[self::ROW_BILL_ITEM_ID]          = null;
				$row[self::DIFFERENCE]                = null;
				$row[self::QTY_1]                     = null;
				$row[self::QTY_2]                     = null;
				$row[self::ROW_BILL_ITEM_TYPE]        = $item['type'];
				$row[self::ROW_BILL_ITEM_LEVEL]       = $item['level'];
				$row[self::ROW_BILL_ITEM_UNIT]        = null;

				if ( $key + 1 == $occupiedRows->count() && $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID )
				{
					$row[self::ROW_BILL_ITEM_ID]   = $item['id'];
					$row[self::DIFFERENCE]         = $item[$this->currentColumn->id . '-total_per_unit_difference'];
					$row[self::QTY_1]              = $item[$this->currentColumn->id . '-total_per_unit'];
					$row[self::QTY_2]              = $item[$this->currentColumn->id . '-remeasure_total_per_unit'];
					$row[self::ROW_BILL_ITEM_UNIT] = $item['uom_symbol'];

					$this->currentQtyOneAmount += $row[self::QTY_1];
					$this->currentQtyTwoAmount += $row[self::QTY_2];
				}

				array_push($itemPages[$pageCount], $row);

				unset( $row );
			}

			if ( $item['type'] == BillItem::TYPE_ITEM_PC_RATE )
			{
				foreach ( $primeCostRateRows as $primeCostRateRow )
				{
					array_push($itemPages[$pageCount], $primeCostRateRow);
				}
			}

			//blank row
			array_push($itemPages[$pageCount], $this->generateBlankRow());

			$rowCount ++;//plus one blank row;

			unset( $item, $items[$x], $occupiedRows );
		}
	}

	public function setOrientationAndSize($orientation = false, $pageFormat = false)
	{
		if ( $orientation )
		{
			$this->orientation = $orientation;
			$this->setPageFormat($this->generatePageFormat(( $pageFormat ) ? $pageFormat : self::PAGE_FORMAT_A4));
		}
		else
		{
			$this->orientation = self::ORIENTATION_LANDSCAPE;
			$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A3));
		}
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
					'pdf_margin_bottom' => 1,
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
					'pdf_margin_bottom' => 1,
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
				break;
		}

		return $pf;
	}

	public function getMaxRows()
	{
		return 52;
	}

	/**
	 * @return SplFixedArray
	 */
	private function generateBlankRow()
	{
		$blankRow                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]          = - 1; //id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]     = null; //row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION] = null; //description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]       = 0; //level
		$blankRow[self::ROW_BILL_ITEM_TYPE]        = self::ROW_TYPE_BLANK; //type
		$blankRow[self::QTY_1]                     = null; // factor
		$blankRow[self::QTY_2]                     = null;
		$blankRow[self::DIFFERENCE]                = null; // total

		return $blankRow;
	}

	public function setItems($items)
	{
		$this->items = $items;
	}

	public function setCurrentColumn(BillColumnSetting $column)
	{
		$this->currentColumn = $column;
	}

}