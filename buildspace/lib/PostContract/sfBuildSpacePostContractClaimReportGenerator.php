<?php

/**
 * @property  title
 */
class sfBuildSpacePostContractClaimReportGenerator {

	protected $postContract;

	protected $bills = array();

	public $DEFAULT_MAX_ROWS = 58;
	public $MAX_ROWS = 58;
	public $ADDITIONAL_DESC_MAX_ROWS = 3;
	public $ADDITIONAL_DESC_MAX_CHARACTERS = 90;

	protected $overallContractAmount = 0;
	protected $overallTotalClaimAmount = 0;

	const MAX_CHARACTERS                             = 43;
	const TOTAL_SUMMARY_ITEM_PROPERTY                = 10;
	const SUMMARY_ITEM_PROPERTY_CHAR_REF             = 0;
	const SUMMARY_ITEM_PROPERTY_TITLE                = 1;
	const SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE  = 2;
	const SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL       = 3;
	const SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT = 4;
	const SUMMARY_ITEM_PROPERTY_PERCENTAGE           = 5;
	const SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD        = 6;
	const SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC      = 7;
	const SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE   = 8;
	const SUMMARY_ITEM_PROPERTY_TYPE                 = 9;

	public function __construct(PostContract $postContract, $bills)
	{
		$this->postContract = $postContract;
		$this->project      = $postContract->ProjectStructure;
		$this->bills        = $bills;
	}

	public function generatePage()
	{
		$itemPages      = array();
		$sumAmountPages = array();

		list($headerRowCount, $headerRows) = $this->generateHeader();

		$this->generateSummaryItemPages($this->bills, 0, $itemPages, $sumAmountPages, false, $headerRowCount, $this->project->ProjectSummaryGeneralSetting->continued_from_previous_page_text);

		return array(
			'header'           => SplFixedArray::fromArray($headerRows),
			'summary_items'    => SplFixedArray::fromArray($itemPages),
			'sum_amount_pages' => SplFixedArray::fromArray($sumAmountPages)
		);
	}

	private function generateHeader()
	{
		$occupiedRows = Utilities::justify($this->title, 80);

		$header = array();

		foreach($occupiedRows as $occupiedRow)
		{
			$row = new SplFixedArray(1);
			$row[0] = $occupiedRow;

			array_push($header, $row);
		}

		$rowCount = count($occupiedRows);

		if(strlen($this->title) > 0)
		{
			$blankRow = new SplFixedArray(1);
			$blankRow[0] = null;

			//blank row
			array_push($header, $blankRow);//starts with a blank row

			$rowCount += 1;
		}

		return array($rowCount, $header);
	}

	private function generateSummaryItemPages(Array $summaryItems, $pageCount, &$itemPages, &$sumAmountPages, $newPage=false, $headerRowCount=1, $continuedFromPreviousText=null, $summaryItemCharRef = 1)
	{
		$itemPages[$pageCount]      = array();
		$sumAmountPages[$pageCount] = 0;
		$sumAmount                  = $pageCount > 0 ? $sumAmountPages[$pageCount - 1] : 0;

		$blankRow                                                   = new SplFixedArray(self::TOTAL_SUMMARY_ITEM_PROPERTY);
		$blankRow[self::SUMMARY_ITEM_PROPERTY_CHAR_REF]             = NULL; //character reference
		$blankRow[self::SUMMARY_ITEM_PROPERTY_TITLE]                = NULL; //title
		$blankRow[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE]  = NULL; //last page summary
		$blankRow[self::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] = NULL; //total amount for bill
		$blankRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD]        = NULL;
		$blankRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC]      = NULL;
		$blankRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE]   = NULL;
		$blankRow[self::SUMMARY_ITEM_PROPERTY_TYPE]                 = NULL;

		//blank row
		array_push($itemPages[$pageCount], $blankRow);//starts with a blank row

		$rowCount = 1 + $headerRowCount;

		foreach($summaryItems as $x => $summaryItem)
		{
			if($newPage and $pageCount > 0)
			{
				$continueFromPreviousPageRow                                                   = new SplFixedArray(self::TOTAL_SUMMARY_ITEM_PROPERTY);
				$continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_CHAR_REF]             = NULL;
				$continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_TITLE]                = $continuedFromPreviousText;
				$continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE]  = NULL;
				$continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] = $sumAmountPages[$pageCount - 1]; //total amount from previous sumAmountPages
				$continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD]        = TRUE;
				$continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC]      = NULL;
				$continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE]   = TRUE;

				array_push($itemPages[$pageCount], $continueFromPreviousPageRow);

				//blank row
				array_push($itemPages[$pageCount], $blankRow);

				$rowCount += 2;
			}

			$occupiedRows = Utilities::justify($summaryItem['title'], self::MAX_CHARACTERS);
			$rowCount += count($occupiedRows);

			if($rowCount <= $this->MAX_ROWS)
			{
				foreach($occupiedRows as $key => $occupiedRow)
				{
					$row                                                   = new SplFixedArray(self::TOTAL_SUMMARY_ITEM_PROPERTY);
					$row[self::SUMMARY_ITEM_PROPERTY_CHAR_REF]             = NULL;
					$row[self::SUMMARY_ITEM_PROPERTY_TITLE]                = $occupiedRow; //title
					$row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD]        = FALSE;
					$row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC]      = FALSE;
					$row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE]   = FALSE;
					$row[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE]  = NULL; //last page summary
					$row[self::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] = NULL; //total amount for bill
					$row[self::SUMMARY_ITEM_PROPERTY_TYPE]                 = $summaryItem['type'];

					if($key+1 == $occupiedRows->count() and $summaryItem['type'] == ProjectStructure::TYPE_BILL)
					{
						$row[self::SUMMARY_ITEM_PROPERTY_CHAR_REF]             = $summaryItemCharRef;

						//total amount for bill
						$row[self::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] = $summaryItem['up_to_date_amount'] == 0 ? NULL : $summaryItem['up_to_date_amount'];
						$row[self::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL]       = $summaryItem['overall_total_after_markup'] == 0 ? NULL : $summaryItem['overall_total_after_markup'];
						$row[self::SUMMARY_ITEM_PROPERTY_PERCENTAGE]           = $summaryItem['up_to_date_percentage'] == 0 ? NULL : $summaryItem['up_to_date_percentage'];

						$this->overallContractAmount   += $row[self::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL];
						$this->overallTotalClaimAmount += $row[self::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT];

						$summaryItemCharRef++;
					}

					array_push($itemPages[$pageCount], $row);
				}

				//blank row
				array_push($itemPages[$pageCount], $blankRow);

				$rowCount++;//plus one blank row;

				$sumAmount += $summaryItem['up_to_date_amount'];

				$sumAmountPages[$pageCount] = $sumAmount;//always update to the total sum of amount for each items

				$newPage = false;

				unset($summaryItems[$x], $row);
			}
			else
			{
				$pageCount++;
				$this->generateSummaryItemPages($summaryItems, $pageCount, $itemPages, $sumAmountPages, true, $headerRowCount, $continuedFromPreviousText, $summaryItemCharRef);
				break;
			}
		}
	}

	public function getOverallContractAmount()
	{
		return $this->overallContractAmount;
	}

	public function getOverallTotalClaimAmount()
	{
		return $this->overallTotalClaimAmount;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

}