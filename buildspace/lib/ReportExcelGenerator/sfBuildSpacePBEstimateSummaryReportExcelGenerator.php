<?php

class sfBuildSpacePBEstimateSummaryReportExcelGenerator extends sfPostContractElementReportGenerator {

	public $colOriginalAmt = 'D';

	public $colTotalMarkUpPercent = 'E';

	public $colTotalMarkUp = 'F';

	public $colOverallTotal = 'G';

	public $projectPercent = 'H';

	private $isLastPage = false;

	private $totalOriginalAmt = 0;

	private $totalMarkUpPercent = 0;

	private $totalMarkup = 0;

	private $totalOverallTotal = 0;

	private $totalProjectPercent = 100;

	public function process($itemPages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage = 0)
	{
		$this->setExcelParameter($lock, $withoutCents);

		$description  = '';
		$char         = '';
		$prevItemType = '';

		$this->createSheet($header, $subTitle, $topLeftTitle);

		foreach ( $itemPages as $page )
		{
			if ( empty( $page ) )
			{
				continue;
			}

			$this->createNewPage(true);

			foreach ( $page as $item )
			{
				$itemType = $item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_TYPE];

				switch ($itemType)
				{
					case ProjectStructure::TYPE_LEVEL:
						$this->setItemHead($item[2], $itemType, $item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_LEVEL]);
						break;

					case self::ROW_TYPE_BLANK:
						if ( $description != '' && $prevItemType != '' )
						{
							$description = '';
						}
						break;
					default:
						$description .= $item[2] . "\n";
						$char .= $item[1];

						if ( $item[0] )
						{
							$this->newItem();

							$this->setItem($description, $itemType, $item[3], $char);

							$this->processItems($item);

							$description = '';
							$char        = '';
						}

						break;
				}
			}

			$this->createFooter(false);
		}
	}

	public function finishExportProcess()
	{
		$this->fileInfo = $this->writeExcel();
	}

	public function startBillCounter()
	{
		$this->currentRow = $this->startRow;
		$this->firstCol   = $this->colItem;
		$this->lastCol    = $this->projectPercent;

		$this->currentElementNo = 0;
		$this->columnSetting    = null;
	}

	public function createHeader($new = false)
	{
		$row = $this->currentRow;

		//set default column
		$this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_NO);
		$this->activeSheet->setCellValue($this->colDescription . $row, 'Element Description');
		$this->activeSheet->setCellValue($this->colOriginalAmt . $row, 'Original Amount');
		$this->activeSheet->setCellValue($this->colTotalMarkUpPercent . $row, 'Total Markup (%)');
		$this->activeSheet->setCellValue($this->colTotalMarkUp . $row, 'Total Mark Up (' . $this->getCurrency() . ')');
		$this->activeSheet->setCellValue($this->colOverallTotal . $row, 'Overall Total');
		$this->activeSheet->setCellValue($this->projectPercent . $row, '% Project');

		// Set header styling
		$this->activeSheet->getStyle($this->colItem . $row . ':' . $this->projectPercent . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());
		$this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

		$this->activeSheet->mergeCells($this->colItem . $row . ':' . $this->colItem . $this->currentRow);
		$this->activeSheet->mergeCells($this->colDescription . $row . ':' . $this->colDescription . $this->currentRow);

		//Set Column Sizing
		$this->activeSheet->getColumnDimension("A")->setWidth(1.3);
		$this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getColumnDimension($this->colOriginalAmt)->setWidth(16);
		$this->activeSheet->getColumnDimension($this->colTotalMarkUpPercent)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colTotalMarkUp)->setWidth(16);
		$this->activeSheet->getColumnDimension($this->colOverallTotal)->setWidth(16);
		$this->activeSheet->getColumnDimension($this->projectPercent)->setWidth(12);
	}

	public function processItems($item)
	{
		parent::setValue($this->colOriginalAmt, ( !empty( $item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_ORIGINAL_AMOUNT] ) ) ? number_format($item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_ORIGINAL_AMOUNT], 2) : null);

		parent::setValue($this->colTotalMarkUpPercent, ( !empty( $item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_TOTAL_MARKUP_PERCENT] ) ) ? number_format($item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_TOTAL_MARKUP_PERCENT], 2) . '%' : null);

		parent::setValue($this->colTotalMarkUp, ( !empty( $item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_TOTAL_MARKUP] ) ) ? number_format($item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_TOTAL_MARKUP], 2) : null);

		parent::setValue($this->colOverallTotal, ( !empty( $item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_OVERALL_TOTAL] ) ) ? number_format($item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_OVERALL_TOTAL], 2) : null);

		parent::setValue($this->projectPercent, ( !empty( $item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_PROJECT_PERCENT] ) ) ? number_format($item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_PROJECT_PERCENT], 2) . '%' : null);

		$this->totalOriginalAmt += $item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_ORIGINAL_AMOUNT];
		$this->totalMarkup += $item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_TOTAL_MARKUP];
		$this->totalOverallTotal += $item[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_OVERALL_TOTAL];
	}

	public function createFooterPageNo()
	{
		if ( $this->isLastPage )
		{
			$this->calculateFinalTotalMarkupPercentage();

			$this->pageConclusion();
		}

		$this->currentRow ++;

		$coord = $this->colItem . $this->currentRow;
		$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);

		$this->currentRow ++;

		$text = 'Page ' . $this->currentPage;

		$pageNoStyle = array(
			'font' => array(
				'bold' => true
			)
		);

		$this->activeSheet->setCellValue($coord, $text);
		$this->activeSheet->getStyle($coord)->applyFromArray($pageNoStyle);
	}

	private function pageConclusion()
	{
		$row = $this->currentRow;

		$this->activeSheet->setCellValue($this->colDescription . $row, 'Total');

		parent::setValue($this->colOriginalAmt, ( !empty( $this->totalOriginalAmt ) ) ? number_format($this->totalOriginalAmt, 2) : null);

		parent::setValue($this->colTotalMarkUpPercent, ( !empty( $this->totalMarkUpPercent ) ) ? number_format($this->totalMarkUpPercent, 2) . '%' : null);

		parent::setValue($this->colTotalMarkUp, ( !empty( $this->totalMarkup ) ) ? number_format($this->totalMarkup, 2) : null);

		parent::setValue($this->colOverallTotal, ( !empty( $this->totalOverallTotal ) ) ? number_format($this->totalOverallTotal, 2) : null);

		parent::setValue($this->projectPercent, $this->totalProjectPercent . '%');

		$this->activeSheet->getStyle($this->colDescription . $row)->applyFromArray(array(
			'font'      => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			)
		));

		$this->activeSheet->getStyle($this->colOriginalAmt . $row . ':' . $this->projectPercent . $this->currentRow)->applyFromArray($this->getPageConclusionStyling());

		$this->currentRow ++;

		$this->totalOriginalAmt += 0;
		$this->totalMarkUpPercent += 0;
		$this->totalMarkup += 0;
		$this->totalOverallTotal += 0;
	}

	public function isLastPage($isLastPage)
	{
		$this->isLastPage = $isLastPage;
	}

	public function getPageConclusionStyling()
	{
		return array(
			'borders'   => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				),
			),
			'font'      => array(
				'bold' => true
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			)

		);
	}

	private function calculateFinalTotalMarkupPercentage()
	{
		$this->totalMarkUpPercent = ( $this->totalOverallTotal - $this->totalOriginalAmt ) / $this->totalOriginalAmt * 100;
	}

}