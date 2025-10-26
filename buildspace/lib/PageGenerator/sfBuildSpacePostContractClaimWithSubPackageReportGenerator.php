<?php

class sfBuildSpacePostContractClaimWithSubPackageReportGenerator {

	private $generatedSubPackageHeader = false;

	protected $postContract;

	protected $bills = array();

	public $DEFAULT_MAX_ROWS = 58;
	public $MAX_ROWS = 58;
	public $ADDITIONAL_DESC_MAX_ROWS = 3;
	public $ADDITIONAL_DESC_MAX_CHARACTERS = 90;

	protected $totalContractAmount = 0;
	protected $totalContractClaimAmount = 0;
	protected $totalSubPackageContractAmount = 0;
	protected $totalSubPackageClaimAmount = 0;

	const MAIN_CONTRACT_TEXT = 'Main Contract';
	const VO_TEXT = 'Variation Order';
	const MOS_TEXT = 'Material On Site';

	const MAX_CHARACTERS = 43;
	const TOTAL_SUMMARY_ITEM_PROPERTY = 10;
	const SUMMARY_ITEM_PROPERTY_CHAR_REF = 0;
	const SUMMARY_ITEM_PROPERTY_TITLE = 1;
	const SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE = 2;
	const SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL = 3;
	const SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT = 4;
	const SUMMARY_ITEM_PROPERTY_PERCENTAGE = 5;
	const SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD = 6;
	const SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC = 7;
	const SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE = 8;
	const SUMMARY_ITEM_PROPERTY_TYPE = 9;

	const TOTAL_ROW_TYPE = 1;
	const NORMAL_CONTRACT_TYPE = 2;
	const SUBPACKAGE_TYPE = 4;
	const SUBPACKAGE_HEADER_TYPE = 8;

	private $summaryItemCount = 1;
	private $contractAmountTotal = 0;
	private $claimedAmountTotal = 0;

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

		list( $headerRowCount, $headerRows ) = $this->generateHeader();

		$this->generateSummaryItemPages($this->bills, 0, $itemPages, $sumAmountPages, $headerRowCount);

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

		foreach ( $occupiedRows as $occupiedRow )
		{
			$row    = new SplFixedArray(1);
			$row[0] = $occupiedRow;

			array_push($header, $row);
		}

		$rowCount = count($occupiedRows);

		if ( strlen($this->title) > 0 )
		{
			$blankRow    = new SplFixedArray(1);
			$blankRow[0] = null;

			//blank row
			array_push($header, $blankRow);//starts with a blank row

			$rowCount += 1;
		}

		return array( $rowCount, $header );
	}

	private function generateSummaryItemPages(Array $summaryItems, $pageCount, &$itemPages, &$sumAmountPages, $headerRowCount = 1, $summaryItemCharRef = 1)
	{
		$itemPages[$pageCount]      = array();
		$sumAmountPages[$pageCount] = 0;

		$blankRow                                                   = new SplFixedArray(self::TOTAL_SUMMARY_ITEM_PROPERTY);
		$blankRow[self::SUMMARY_ITEM_PROPERTY_CHAR_REF]             = null; //character reference
		$blankRow[self::SUMMARY_ITEM_PROPERTY_TITLE]                = null; //title
		$blankRow[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE]  = null; //last page summary
		$blankRow[self::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] = null;
		$blankRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD]        = null;
		$blankRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC]      = null;
		$blankRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE]   = null;
		$blankRow[self::SUMMARY_ITEM_PROPERTY_TYPE]                 = null;

		//blank row
		array_push($itemPages[$pageCount], $blankRow);//starts with a blank row

		$rowCount = 1 + $headerRowCount;

		foreach ( $summaryItems as $x => $summaryItem )
		{
			$occupiedRows = Utilities::justify($summaryItem['title'], self::MAX_CHARACTERS);
			$rowCount += count($occupiedRows);

			if ( $rowCount >= $this->MAX_ROWS )
			{
				$pageCount ++;

				$this->generateSummaryItemPages($summaryItems, $pageCount, $itemPages, $sumAmountPages, $headerRowCount, $summaryItemCharRef);

				break;
			}

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				$row                                                   = new SplFixedArray(self::TOTAL_SUMMARY_ITEM_PROPERTY);
				$row[self::SUMMARY_ITEM_PROPERTY_CHAR_REF]             = null;
				$row[self::SUMMARY_ITEM_PROPERTY_TITLE]                = $occupiedRow; //title
				$row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD]        = false;
				$row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC]      = false;
				$row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE]   = false;
				$row[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE]  = null; //last page summary
				$row[self::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] = ( !empty( $summaryItem['claimed_total'] ) ) ? $summaryItem['claimed_total'] : 0;
				$row[self::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL]       = ( !empty( $summaryItem['standard_bill_amount'] ) ) ? $summaryItem['standard_bill_amount'] : 0;
				$row[self::SUMMARY_ITEM_PROPERTY_PERCENTAGE]           = ( !empty( $summaryItem['claimed_percentage'] ) ) ? $summaryItem['claimed_percentage'] : 0;
				$row[self::SUMMARY_ITEM_PROPERTY_CHAR_REF]             = $summaryItemCharRef;

				$this->contractAmountTotal += $summaryItem['standard_bill_amount'];
				$this->claimedAmountTotal += $summaryItem['claimed_total'];

				$summaryItemCharRef ++;

				array_push($itemPages[$pageCount], $row);
			}

			//blank row
			array_push($itemPages[$pageCount], $blankRow);

			$this->summaryItemCount ++;

			if ( $summaryItem['title'] !== self::MOS_TEXT )
			{
				$rowCount ++;
			}
			else
			{
				$row                                                   = new SplFixedArray(self::TOTAL_SUMMARY_ITEM_PROPERTY);
				$row[self::SUMMARY_ITEM_PROPERTY_CHAR_REF]             = null;
				$row[self::SUMMARY_ITEM_PROPERTY_TITLE]                = "TOTAL ({$this->project->MainInformation->Currency->currency_code})";
				$row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD]        = false;
				$row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC]      = false;
				$row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE]   = false;
				$row[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE]  = null; //last page summary
				$row[self::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] = $this->claimedAmountTotal;
				$row[self::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL]       = $this->contractAmountTotal;
				$row[self::SUMMARY_ITEM_PROPERTY_PERCENTAGE]           = 0;
				$row[self::SUMMARY_ITEM_PROPERTY_TYPE]                 = self::TOTAL_ROW_TYPE;

				if ( $summaryItem['type'] == sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUBPACKAGE_TYPE )
				{
					$this->totalSubPackageContractAmount += $this->contractAmountTotal;
					$this->totalSubPackageClaimAmount += $this->claimedAmountTotal;
				}
				else
				{
					$this->totalContractAmount += $this->contractAmountTotal;
					$this->totalContractClaimAmount += $this->claimedAmountTotal;
				}

				array_push($itemPages[$pageCount], $row);

				$rowCount ++;

				if ( !$this->generatedSubPackageHeader )
				{
					$spHeader                                                   = new SplFixedArray(self::TOTAL_SUMMARY_ITEM_PROPERTY);
					$spHeader[self::SUMMARY_ITEM_PROPERTY_CHAR_REF]             = null;
					$spHeader[self::SUMMARY_ITEM_PROPERTY_TITLE]                = 'SUB PACKAGES';
					$spHeader[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD]        = false;
					$spHeader[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC]      = false;
					$spHeader[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE]   = false;
					$spHeader[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE]  = null; //last page summary
					$spHeader[self::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] = 0;
					$spHeader[self::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL]       = 0;
					$spHeader[self::SUMMARY_ITEM_PROPERTY_PERCENTAGE]           = 0;
					$spHeader[self::SUMMARY_ITEM_PROPERTY_TYPE]                 = self::SUBPACKAGE_HEADER_TYPE;

					array_push($itemPages[$pageCount], $spHeader);

					$rowCount ++;

					array_push($itemPages[$pageCount], $blankRow);

					$rowCount ++;

					$this->generatedSubPackageHeader = true;
				}
				else
				{
					array_push($itemPages[$pageCount], $blankRow);

					$rowCount ++;
				}

				$this->summaryItemCount = 1;

				$this->contractAmountTotal = 0;
				$this->claimedAmountTotal  = 0;
			}

			unset( $summaryItems[$x], $row );
		}
	}

	public function getTotalContractAmount()
	{
		return $this->totalContractAmount;
	}

	public function getContractTotalClaimAmount()
	{
		return $this->totalContractClaimAmount;
	}

	public function getTotalSubPackageContractAmount()
	{
		return $this->totalSubPackageContractAmount;
	}

	public function getSubPackageTotalClaimAmount()
	{
		return $this->totalSubPackageClaimAmount;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

}