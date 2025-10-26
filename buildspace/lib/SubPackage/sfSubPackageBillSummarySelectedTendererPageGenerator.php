<?php

class sfSubPackageBillSummarySelectedTendererPageGenerator extends sbSubPackageReportBillSummaryBaseGenerator {

	use sfBuildspaceReportPageFormat;

	public $pageTitle;
	public $sortingType;
	public $billIds;
	public $fontSize;
	public $headSettings;

	public $tendererIds = array();
	public $tenderers = array();
	public $subCon = array();

	public $totalEstimateAmt = 0;
	public $totalSubConAmt = 0;

	const TOTAL_BILL_ITEM_PROPERTY = 9;
	const ROW_BILL_ITEM_ID = 0;
	const ROW_BILL_ITEM_ROW_IDX = 1;
	const ROW_BILL_ITEM_DESCRIPTION = 2;
	const ROW_BILL_ITEM_LEVEL = 3;
	const ROW_BILL_ITEM_AMOUNT = 4;
	const ROW_BILL_ITEM_DIFF_PERCENT = 5;
	const ROW_BILL_ITEM_DIFF_AMOUNT = 6;
	const ROW_BILL_ITEM_ESTIMATE_AMOUNT = 7;
	const ROW_BILL_ITEM_TYPE = 8;

	public function __construct($subPackage, $billSplFixedArray, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
	{
		$this->pdo               = SubPackageTable::getInstance()->getConnection()->getDbh();
		$this->subPackage        = $subPackage;
		$this->billSplFixedArray = $billSplFixedArray;
		$this->currency          = $subPackage->ProjectStructure->MainInformation->Currency->currency_code;
		$this->descriptionFormat = $descriptionFormat;

		$this->setOrientationAndSize(self::ORIENTATION_LANDSCAPE, self::PAGE_FORMAT_A4);

		$this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
		$this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings  = $this->printSettings['headSettings'];

		self::setMaxCharactersPerLine();
	}

	public function generatePages()
	{
		$itemPages = array();

		$this->generateBillPages($this->billSplFixedArray, 1, array(), $itemPages);

		$pages = SplFixedArray::fromArray($itemPages);

		unset( $itemPages, $bills );

		return $pages;
	}

	public function generateBillPages(SplFixedArray $billBills, $pageCount, $ancestors, &$itemPages, $newPage = false)
	{
		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();
		$ancestors             = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

		$blankRow                                      = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]              = - 1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]         = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]     = null;//description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]           = 0;//level
		$blankRow[self::ROW_BILL_ITEM_AMOUNT]          = null;//amount
		$blankRow[self::ROW_BILL_ITEM_DIFF_PERCENT]    = null;//diff percentage
		$blankRow[self::ROW_BILL_ITEM_DIFF_AMOUNT]     = null;//diff amount
		$blankRow[self::ROW_BILL_ITEM_ESTIMATE_AMOUNT] = null;
		$blankRow[self::ROW_BILL_ITEM_TYPE]            = self::ROW_TYPE_BLANK;

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

		foreach ( $billBills as $x => $billBill )
		{
			$occupiedRows = Utilities::justify($billBills[$x]['title'], $this->MAX_CHARACTERS);

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
				$this->generateBillPages($billBills, $pageCount, $ancestors, $itemPages, true);
				break;
			}

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				$row                                      = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
				$row[self::ROW_BILL_ITEM_ROW_IDX]         = ( $key == 0 ) ? $itemIndex : null;
				$row[self::ROW_BILL_ITEM_DESCRIPTION]     = $occupiedRow;
				$row[self::ROW_BILL_ITEM_ID]              = null;
				$row[self::ROW_BILL_ITEM_DIFF_PERCENT]    = null;//diff percentage
				$row[self::ROW_BILL_ITEM_DIFF_AMOUNT]     = null;//diff amount
				$row[self::ROW_BILL_ITEM_ESTIMATE_AMOUNT] = null;
				$row[self::ROW_BILL_ITEM_TYPE]            = null;

				if ( $key + 1 == $occupiedRows->count() )
				{
					$row[self::ROW_BILL_ITEM_TYPE]            = 123;
					$row[self::ROW_BILL_ITEM_ID]              = $billBill['id'];
					$row[self::ROW_BILL_ITEM_ESTIMATE_AMOUNT] = $billBill['est_amount'];
					$row[self::ROW_BILL_ITEM_AMOUNT]          = self::gridCurrencyRoundingFormat(isset( $billBill["total_amount-{$this->subCon->id}"] ) ? $billBill["total_amount-{$this->subCon->id}"] : 0);
					$row[self::ROW_BILL_ITEM_DIFF_PERCENT]    = self::gridCurrencyRoundingFormat(isset( $billBill["difference_percentage-{$this->subCon->id}"] ) ? $billBill["difference_percentage-{$this->subCon->id}"] : 0);
					$row[self::ROW_BILL_ITEM_DIFF_AMOUNT]     = self::gridCurrencyRoundingFormat(isset( $billBill["difference_amount-{$this->subCon->id}"] ) ? $billBill["difference_amount-{$this->subCon->id}"] : 0);

					$this->totalEstimateAmt += $row[self::ROW_BILL_ITEM_ESTIMATE_AMOUNT];
					$this->totalSubConAmt += $row[self::ROW_BILL_ITEM_AMOUNT];
				}

				array_push($itemPages[$pageCount], $row);

				unset( $row );
			}

			//blank row
			array_push($itemPages[$pageCount], $blankRow);

			$rowCount ++;//plus one blank row;
			$itemIndex ++;

			unset( $billBills[$x], $occupiedRows );
		}
	}

	public function setSelectedSubCon(Company $subCon)
	{
		$this->subCon = $subCon;
	}

}