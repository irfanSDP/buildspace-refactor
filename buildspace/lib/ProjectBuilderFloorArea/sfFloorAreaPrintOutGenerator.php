<?php

class sfFloorAreaPrintOutGenerator extends sfBuildspaceBQMasterFunction {

	protected $billColumnSetting;

	protected $buildUpFloorAreaItems;

	protected $fontSize;

	protected $fontType;

	protected $headSettings;

	public $MAX_CHARACTERS          = 56;

	const ROW_BILL_ITEM_ID          = 0;
	const ROW_BILL_ITEM_ROW_IDX     = 1;
	const ROW_BILL_ITEM_DESCRIPTION = 2;
	const ROW_BILL_ITEM_FACTOR      = 3;
	const ROW_BILL_ITEM_TYPE        = 4;
	const ROW_BILL_ITEM_LENGTH      = 5;
	const ROW_BILL_ITEM_WIDTH       = 6;
	const ROW_BILL_ITEM_TOTAL       = 7;
	const ROW_BILL_ITEM_SIGN        = 8;

	private $itemIndex              = 1;

	public function __construct(BillColumnSetting $billColumnSetting, array $buildUpFloorAreaItems)
	{
		$this->billColumnSetting     = $billColumnSetting;
		$this->buildUpFloorAreaItems = $buildUpFloorAreaItems;
		$this->printSettings         = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($billColumnSetting->ProjectStructure->BillLayoutSetting->id, TRUE);
		$this->fontSize              = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType              = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings          = $this->printSettings['headSettings'];
		$this->pageFormat            = self::PAGE_FORMAT_A4;
		$this->project               = ProjectStructureTable::getInstance()->find($billColumnSetting->ProjectStructure->root_id);
		$this->currency              = $this->project->MainInformation->Currency;
		$this->orientation           = self::ORIENTATION_PORTRAIT;

		$this->setMaxCharactersPerLine();
	}

	public function generatePages()
	{
		$pageNumberDescription = 'Page No. ';
		$pages                 = array();
		$itemPages             = array();

		$this->generateBillBuildUpFloorAreaPages($this->buildUpFloorAreaItems, 1, array(), $itemPages);

		$pages = SplFixedArray::fromArray($itemPages, true);

		unset($itemPages);

		return $pages;
	}

	public function generateBillBuildUpFloorAreaPages($buildUpFloorAreaItems, $pageCount, $ancestors, &$itemPages, $newPage = false)
	{
		$itemPages[$pageCount] = array();
		$layoutSettings        = $this->printSettings['layoutSetting'];
		$maxRows               = $this->getMaxRows();
		$ancestors             = (is_array($ancestors) && count($ancestors)) ? $ancestors : array();

		$blankRow                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]          = -1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]     = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION] = null;//description
		$blankRow[self::ROW_BILL_ITEM_FACTOR]      = 0;//factor
		$blankRow[self::ROW_BILL_ITEM_TYPE]        = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_LENGTH]      = null;//length
		$blankRow[self::ROW_BILL_ITEM_WIDTH]       = null;//width
		$blankRow[self::ROW_BILL_ITEM_TOTAL]       = null;//total
		$blankRow[self::ROW_BILL_ITEM_SIGN]        = null;//sign

		//blank row
		array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
		$rowCount = 1;

		foreach($ancestors as $k => $row)
		{
			array_push($itemPages[$pageCount], $row);
			$rowCount += 1;
			unset($row);
		}

		$ancestors = array();

		foreach($buildUpFloorAreaItems as $x => $buildUpFloorAreaItem)
		{
			$occupiedRows = Utilities::justify($buildUpFloorAreaItems[$x]['description'], $this->MAX_CHARACTERS);

			if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
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
					$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
					$row[self::ROW_BILL_ITEM_ROW_IDX]     = ($key == 0) ? $this->itemIndex : null;
					$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
					$row[self::ROW_BILL_ITEM_ID]          = null;
					$row[self::ROW_BILL_ITEM_FACTOR]      = null;
					$row[self::ROW_BILL_ITEM_LENGTH]      = null;//length
					$row[self::ROW_BILL_ITEM_WIDTH]       = null;//width
					$row[self::ROW_BILL_ITEM_TOTAL]       = null;//total
					$row[self::ROW_BILL_ITEM_SIGN]        = $buildUpFloorAreaItem['sign_symbol'];//sign

					if($key+1 == $occupiedRows->count())
					{
						$row[self::ROW_BILL_ITEM_ID]     = $buildUpFloorAreaItem['id'];
						$row[self::ROW_BILL_ITEM_WIDTH]  = 0;
						$row[self::ROW_BILL_ITEM_FACTOR] = $buildUpFloorAreaItem['factor-final_value'];
						$row[self::ROW_BILL_ITEM_LENGTH] = $buildUpFloorAreaItem['length-final_value'];//length
						$row[self::ROW_BILL_ITEM_WIDTH]  = $buildUpFloorAreaItem['width-final_value'];//width
						$row[self::ROW_BILL_ITEM_TOTAL]  = $buildUpFloorAreaItem['total'];//total
					}

					array_push($itemPages[$pageCount], $row);

					unset($row);
				}

				//blank row
				array_push($itemPages[$pageCount], $blankRow);

				$rowCount++;//plus one blank row;
				$this->itemIndex++;
				$newPage = false;

				unset($buildUpFloorAreaItems[$x], $occupiedRows);
			}
			else
			{
				unset($occupiedRows);

				$pageCount++;
				$this->generateBillBuildUpFloorAreaPages($buildUpFloorAreaItems, $pageCount, $ancestors, $itemPages, true);
				break;
			}
		}
	}

	public function setMaxCharactersPerLine()
	{
		$this->MAX_CHARACTERS = 45;
	}

	public function getMaxRows()
	{
		return $maxRows = 60;
	}

	public function setDescriptionFormat($value)
	{
		$this->descriptionFormat = $value;
	}

	public function setPageFormat()
	{
		$width  = 595;
		$height = 800;

		$this->pageFormat = array(
			'page_format'       => self::PAGE_FORMAT_A4,
			'minimum-font-size' => $this->fontSize,
			'width'             => $width,
			'height'            => $height,
			'pdf_margin_top'    => 8,
			'pdf_margin_right'  => 8,
			'pdf_margin_bottom' => 3,
			'pdf_margin_left'   => 8
		);
	}

}