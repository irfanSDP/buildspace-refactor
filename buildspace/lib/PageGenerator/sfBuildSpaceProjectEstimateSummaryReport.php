<?php

class sfBuildSpaceProjectEstimateSummaryReport extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	const TOTAL_BILL_PROPERTY = 11;
	const ROW_BILL = 0;
	const ROW_BILL_ROW_IDX = 1;
	const ROW_BILL_DESCRIPTION = 2;
	const ROW_BILL_LEVEL = 3;
	const ROW_BILL_TYPE = 4;
	const ROW_BILL_ORIGINAL_AMOUNT = 5;
	const ROW_BILL_TOTAL_MARKUP_PERCENT = 6;
	const ROW_BILL_TOTAL_MARKUP = 7;
	const ROW_BILL_OVERALL_TOTAL = 8;
	const ROW_BILL_PROJECT_PERCENT = 9;
	const ROW_BILL_SUM_TOTAL = 10;

	public $originalAmountTotal = 0;
	public $totalMarkUpPercent = 0;
	public $totalMarkUpTotal = 0;
	public $overallTotalTotal = 0;
	public $projectPercentTotal = 0;
	public $finalTotalMarkUpPercent = 0;

	protected $bills;

	private $currentBillNo = 1;

	public function __construct(ProjectStructure $project, $descriptionFormat = sfBuildspaceReportBillPageGenerator::DESC_FORMAT_FULL_LINE)
	{
		$this->project           = $project;
		$this->printSettings     = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
		$this->fontSize          = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType          = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings      = $this->printSettings['headSettings'];
		$this->descriptionFormat = $descriptionFormat;
		$this->currency          = $project->MainInformation->Currency->currency_code;
	}

	public function setBillRecords($bills)
	{
		$this->bills = $bills;
	}

	public function generatePages()
	{
		$billPages = array();

		$this->setMaxCharactersPerLine();

		$this->itemIndex = 1;

		$this->generateBillPages($this->bills, 1, array(), $billPages, array());

		$pages = SplFixedArray::fromArray($billPages);

		$this->calculateFinalTotalMarkupPercentage();

		unset( $billPages, $this->bills );

		return $pages;
	}

	public function generateBillPages(Array $bills, $pageCount, $ancestors, &$billPages, $billTotals)
	{
		$billPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();
		$ancestors             = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

		$blankRow                                      = new SplFixedArray(self::TOTAL_BILL_PROPERTY);
		$blankRow[self::ROW_BILL]                      = - 1;//id
		$blankRow[self::ROW_BILL_ROW_IDX]              = null;//row index
		$blankRow[self::ROW_BILL_DESCRIPTION]          = null;//description
		$blankRow[self::ROW_BILL_LEVEL]                = 0;//level
		$blankRow[self::ROW_BILL_TYPE]                 = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ORIGINAL_AMOUNT]      = null;
		$blankRow[self::ROW_BILL_TOTAL_MARKUP_PERCENT] = null;
		$blankRow[self::ROW_BILL_TOTAL_MARKUP]         = null;
		$blankRow[self::ROW_BILL_OVERALL_TOTAL]        = null;
		$blankRow[self::ROW_BILL_PROJECT_PERCENT]      = null;

		//blank row
		array_push($billPages[$pageCount], $blankRow);//starts with a blank row
		$rowCount = 1;

		foreach ( $ancestors as $k => $row )
		{
			array_push($billPages[$pageCount], $row);
			$rowCount += 1;
			unset( $row );
		}

		$ancestors = array();

		foreach ( $bills as $x => $bill )
		{
			$occupiedRows = Utilities::justify($bills[$x]['title'], $this->MAX_CHARACTERS);

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
				$this->generateBillPages($bills, $pageCount, $ancestors, $billPages, $billTotals, true);
				break;
			}

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				$row                                      = new SplFixedArray(self::TOTAL_BILL_PROPERTY);
				$row[self::ROW_BILL_ROW_IDX]              = null;
				$row[self::ROW_BILL_TYPE]                 = $bill['type'];
				$row[self::ROW_BILL_DESCRIPTION]          = $occupiedRow;
				$row[self::ROW_BILL]                      = null;
				$row[self::ROW_BILL_ORIGINAL_AMOUNT]      = 0;
				$row[self::ROW_BILL_TOTAL_MARKUP_PERCENT] = null;
				$row[self::ROW_BILL_TOTAL_MARKUP]         = null;
				$row[self::ROW_BILL_OVERALL_TOTAL]        = null;
				$row[self::ROW_BILL_PROJECT_PERCENT]      = null;
				$row[self::ROW_BILL_SUM_TOTAL]            = 0;

				if ( $key + 1 == $occupiedRows->count() )
				{
					$billNo = null;

					if ( $bill['type'] == ProjectStructure::TYPE_BILL )
					{
						$billNo = $this->currentBillNo;

						$this->currentBillNo ++;
					}

					$row[self::ROW_BILL]                      = $bill['id'];
					$row[self::ROW_BILL_ROW_IDX]              = $billNo;
					$row[self::ROW_BILL_ORIGINAL_AMOUNT]      = $bill['original_total'];
					$row[self::ROW_BILL_TOTAL_MARKUP_PERCENT] = $bill['original_total'] != 0 ? ( $bill['overall_total_after_markup'] - $bill['original_total'] ) / $bill['original_total'] * 100 : 0;
					$row[self::ROW_BILL_TOTAL_MARKUP]         = $bill['overall_total_after_markup'] - $bill['original_total'];
					$row[self::ROW_BILL_OVERALL_TOTAL]        = $bill['overall_total_after_markup'];
					$row[self::ROW_BILL_PROJECT_PERCENT]      = $bill['bill_sum_total'] != 0 ? $bill['overall_total_after_markup'] / $bill['bill_sum_total'] * 100 : 0;
					$row[self::ROW_BILL_SUM_TOTAL]            = $bill['bill_sum_total'];

					$this->originalAmountTotal += $row[self::ROW_BILL_ORIGINAL_AMOUNT];
					$this->totalMarkUpTotal += $row[self::ROW_BILL_TOTAL_MARKUP];
					$this->overallTotalTotal += $row[self::ROW_BILL_OVERALL_TOTAL];
					$this->projectPercentTotal += $row[self::ROW_BILL_PROJECT_PERCENT];
				}

				array_push($billPages[$pageCount], $row);

				unset( $row );
			}

			//blank row
			array_push($billPages[$pageCount], $blankRow);

			$rowCount ++;//plus one blank row;
			$this->itemIndex ++;

			unset( $bills[$x], $occupiedRows );
		}
	}

	public function setOrientationAndSize($orientation = false, $pageFormat = false)
	{
		if ( $orientation )
		{
			$this->orientation = $orientation;
			$this->setPageFormat($this->generatePageFormat(( $pageFormat ) ? $pageFormat : self::PAGE_FORMAT_A4));

			return;
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

	public function getMaxRows()
	{
		return 72;
	}

	private function calculateFinalTotalMarkupPercentage()
	{
		$this->finalTotalMarkUpPercent = ( $this->overallTotalTotal - $this->originalAmountTotal ) / $this->originalAmountTotal * 100;
	}

}