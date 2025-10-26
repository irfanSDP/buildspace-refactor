<?php

class sfBuildSpaceStockInInvoiceItemReportPageGenerator extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	protected $items = array();

	protected $resourceTrades = array();

	const ROW_BILL_ITEM_ID = 0;
	const ROW_BILL_ITEM_ROW_IDX = 1;
	const ROW_BILL_ITEM_DESCRIPTION = 2;
	const ROW_BILL_ITEM_QTY_PER_UNIT = 3;
	const ROW_BILL_ITEM_TYPE = 4;
	const ROW_BILL_ITEM_TOTAL_WITH_TAX = 5;
	const ROW_BILL_ITEM_UNIT = 7;
	const ROW_BILL_ITEM_DO_QTY = 8;
	const ROW_BILL_ITEM_DISCOUNT = 10;
	const ROW_BILL_ITEM_BALANCE_QTY = 13;
	const ROW_BILL_ITEM_TOTAL_WITHOUT_TAX = 14;
	const ROW_BILL_ITEM_TAX = 15;

	const TOTAL_BILL_ITEM_PROPERTY = 16;

	protected $pageCount = 0;

	public function __construct($descriptionFormat = sfBuildspaceReportBillPageGenerator::DESC_FORMAT_FULL_LINE)
	{
		$this->descriptionFormat = $descriptionFormat;
		$this->printSettings     = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
		$this->fontSize          = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType          = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings      = $this->printSettings['headSettings'];
		$this->descriptionFormat = $descriptionFormat;
		$this->currency          = null;
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

		unset( $itemPages, $this->resourceTrades, $this->items );

		return $pages;
	}

	public function generateBillPages(Array $items, &$itemPages, $billTotals)
	{
		if ( !isset ( $itemPages[$this->pageCount] ) )
		{
			$itemPages[$this->pageCount] = array();

			//blank row
			array_push($itemPages[$this->pageCount], $this->setBlankRow());//starts with a blank row
		}

		$maxRows = $this->getMaxRows();

		foreach ( $items as $x => $item )
		{
			$occupiedRows = Utilities::justify(strip_tags($items[$x]['description']), $this->MAX_CHARACTERS);

			if ( $this->descriptionFormat == sfBuildspaceReportBillPageGenerator::DESC_FORMAT_ONE_LINE )
			{
				$oneLineDesc     = $occupiedRows[0];
				$occupiedRows    = new SplFixedArray(1);
				$occupiedRows[0] = $oneLineDesc;
			}

			$this->rowCount += count($occupiedRows);

			if ( $this->rowCount >= $maxRows )
			{
				unset( $occupiedRows );

				$this->pageCount ++;
				$this->resetPageRowCount();
				$this->generateBillPages($items, $itemPages, $billTotals, true);
				break;
			}

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				if ( $key == 0 && $item['type'] != ResourceItem::TYPE_HEADER )
				{
					$this->itemIndex ++;
				}

				$row                                        = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
				$row[self::ROW_BILL_ITEM_ROW_IDX]           = ( $key == 0 && $item['type'] != ResourceItem::TYPE_HEADER ) ? $this->itemIndex : null;
				$row[self::ROW_BILL_ITEM_DESCRIPTION]       = $occupiedRow;
				$row[self::ROW_BILL_ITEM_TYPE]              = $item['type'];
				$row[self::ROW_BILL_ITEM_ID]                = null;
				$row[self::ROW_BILL_ITEM_TOTAL_WITH_TAX]    = null;
				$row[self::ROW_BILL_ITEM_TOTAL_WITHOUT_TAX] = null;
				$row[self::ROW_BILL_ITEM_UNIT]              = null;
				$row[self::ROW_BILL_ITEM_DO_QTY]            = null;

				if ( $key + 1 == $occupiedRows->count() )
				{
					$row[self::ROW_BILL_ITEM_ID]                = $item['id'];
					$row[self::ROW_BILL_ITEM_QTY_PER_UNIT]      = $item['quantity'];
					$row[self::ROW_BILL_ITEM_TOTAL_WITH_TAX]    = $item['total'];
					$row[self::ROW_BILL_ITEM_TOTAL_WITHOUT_TAX] = $item['total_without_tax'];
					$row[self::ROW_BILL_ITEM_DO_QTY]            = $item['doQuantity'];
					$row[self::ROW_BILL_ITEM_BALANCE_QTY]       = $item['balanceQuantity'];
					$row[self::ROW_BILL_ITEM_UNIT]              = $item['uom'];
					$row[self::ROW_BILL_ITEM_DISCOUNT]          = $item['discount_percentage'];
					$row[self::ROW_BILL_ITEM_TAX]               = $item['tax_percentage'];
					$row[self::ROW_BILL_ITEM_RATE]              = $item['rates'];
				}

				array_push($itemPages[$this->pageCount], $row);

				unset( $row );
			}

			//blank row
			array_push($itemPages[$this->pageCount], $this->setBlankRow());

			$this->rowCount ++;//plus one blank row;

			unset( $items[$x], $occupiedRows );
		}
	}

	public function setOrientationAndSize($orientation = false, $pageFormat = false)
	{
		if ( $orientation )
		{
			$this->orientation = $orientation;
			$this->setPageFormat($this->generatePageFormat(( $pageFormat ) ? $pageFormat : self::PAGE_FORMAT_A4));

			return null;
		}

		$this->orientation = self::ORIENTATION_LANDSCAPE;
		$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A3));
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

	public function setItems(array $buildUpQuantityItems)
	{
		$this->items = $buildUpQuantityItems;

		unset( $buildUpQuantityItems );
	}

	public function setMaxCharactersPerLine()
	{
		if ( $this->fontSize == 10 )
		{
			return $this->MAX_CHARACTERS = 64;
		}

		return $this->MAX_CHARACTERS = 56;
	}

	public function getMaxRows()
	{
		return 50;
	}

	public function resetPageRowCount()
	{
		return $this->rowCount = 1;
	}

	public function setBlankRow()
	{
		$blankRow                                     = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]             = - 1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]        = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]    = null;//description
		$blankRow[self::ROW_BILL_ITEM_TYPE]           = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_TOTAL_WITH_TAX] = null;
		$blankRow[self::ROW_BILL_ITEM_UNIT]           = null;
		$blankRow[self::ROW_BILL_ITEM_DO_QTY]         = null;
		$blankRow[self::ROW_BILL_ITEM_BALANCE_QTY]    = null;
		$blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT]   = null;

		return $blankRow;
	}

}