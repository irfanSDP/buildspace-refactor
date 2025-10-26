<?php

class sfRemeasurementTypesElementReportGenerator extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	const TOTAL_BILL_ITEM_PROPERTY = 7;
	const ROW_BILL_ITEM_ID = 0;
	const ROW_BILL_ITEM_ROW_IDX = 1;
	const ROW_BILL_ITEM_DESCRIPTION = 2;
	const ROW_BILL_ITEM_OMISSION = 5;
	const ROW_BILL_ITEM_ADDITION = 6;

	public $totalOmission = 0;
	public $totalAddition = 0;

	public function __construct(PostContract $postContract, ProjectStructure $bill, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
	{
		$this->postContract      = $postContract;
		$this->bill              = $bill;
		$this->descriptionFormat = $descriptionFormat;
		$this->currency          = $postContract->ProjectStructure->MainInformation->Currency->currency_code;

		$this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
		$this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings  = $this->printSettings['headSettings'];

		$this->setOrientationAndSize(self::ORIENTATION_PORTRAIT, self::PAGE_FORMAT_A4);
	}

	public function setElements($elements)
	{
		$this->elements = $elements;
	}

	public function generatePages()
	{
		$itemPages           = array();
		$this->itemIndex     = 1;
		$this->totalOmission = 0;
		$this->totalAddition = 0;

		$this->setMaxCharactersPerLine();
		$this->generateTypeElementsPages($this->elements, 1, array(), $itemPages);

		$pages = SplFixedArray::fromArray($itemPages);

		unset( $itemPages, $bills );

		return $pages;
	}

	public function generateTypeElementsPages(Array $elements, $pageCount, $ancestors, &$itemPages, $newPage = false)
	{
		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();
		$ancestors             = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

		$blankRow                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]          = - 1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]     = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION] = null;//description
		$blankRow[self::ROW_BILL_ITEM_TYPE]        = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_OMISSION]    = null;
		$blankRow[self::ROW_BILL_ITEM_ADDITION]    = null;

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

		foreach ( $elements as $x => $item )
		{
			$occupiedRows = Utilities::justify($elements[$x]['description'], $this->MAX_CHARACTERS);

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
				$this->generateTypeElementsPages($elements, $pageCount, $ancestors, $itemPages, true);
				break;
			}

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
				$row[self::ROW_BILL_ITEM_ROW_IDX]     = ( $key == 0 ) ? $this->itemIndex : null;
				$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
				$row[self::ROW_BILL_ITEM_ID]          = null;
				$row[self::ROW_BILL_ITEM_OMISSION]    = null;
				$row[self::ROW_BILL_ITEM_ADDITION]    = null;

				if ( $key + 1 == $occupiedRows->count() )
				{
					$row[self::ROW_BILL_ITEM_ID]       = $item['id'];
					$row[self::ROW_BILL_ITEM_OMISSION] = Utilities::prelimRounding($item['omission']);
					$row[self::ROW_BILL_ITEM_ADDITION] = Utilities::prelimRounding($item['addition']);
					$row[self::ROW_BILL_ITEM_TYPE]     = 9999;

					$this->totalOmission += $row[self::ROW_BILL_ITEM_OMISSION];
					$this->totalAddition += $row[self::ROW_BILL_ITEM_ADDITION];
				}

				array_push($itemPages[$pageCount], $row);

				unset( $row );
			}

			//blank row
			array_push($itemPages[$pageCount], $blankRow);

			$rowCount ++;//plus one blank row;
			$this->itemIndex ++;

			unset( $elements[$x], $occupiedRows );
		}
	}

	protected function setOrientationAndSize($orientation = false, $pageFormat = false)
	{
		$this->orientation = $orientation;

		$this->setPageFormat($this->generatePageFormat(( $pageFormat ) ? $pageFormat : self::PAGE_FORMAT_A4));
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
		}

		return $pf;
	}

	public function getMaxRows()
	{
		$maxRows    = 35;
		$pageFormat = $this->getPageFormat();

		switch ($pageFormat['page_format'])
		{
			case self::PAGE_FORMAT_A4:
				if ( $this->orientation == self::ORIENTATION_PORTRAIT )
				{
					$maxRows = 55;
				}
				break;
			default:
				$maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 110 : 55;
		}

		return $maxRows;
	}

}