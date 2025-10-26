<?php

class sfBuildSpacePBSelectedElementsEstimateSummaryByTypesReportExcelGenerator extends sfBuildspaceExcelReportGenerator {

	public $colPercent = 'D';

	public $colCostMetric = 'E';

	public $colTotal = 'F';

	private $isLastPage = false;

	private $totalPercentage = 0;

	private $totalCost = 0;

	private $total = 0;

	private $costMetric;

	public $currentRow = 1;

	public function createSheet($billHeader = null, $topLeftTitle = '', $subTitle = '')
	{
		$this->setActiveSheet();

		$this->startBillCounter();

		$this->setBillHeader($billHeader, $topLeftTitle, $subTitle);
	}

	public function setBillHeader($billHeader = null, $topLeftTitle, $subTitle)
	{
		$billHeader = ( $billHeader ) ? $billHeader : $this->filename;

		//Set Top Header
		$this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $billHeader);
		$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);
		$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getProjectTitleStyle());

		$this->currentRow++;

		//Set SubTitle
		$this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $subTitle);
		$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);
		$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getSubTitleStyle());

		$this->currentRow++;

		$this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $topLeftTitle);
		$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->colDescription . $this->currentRow);
		$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->colDescription . $this->currentRow)->applyFromArray($this->getLeftTitleStyle());

		$this->currentRow++;
	}

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

			$this->currentPage++;

			$this->createNewPage($this->currentPage);

			foreach ( $page as $item )
			{
				$itemType = $item[4];

				switch ($itemType)
				{
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

	public function createNewPage($pageNo = null, $printGrandTotal = false, $printFooter = false)
	{
		if ( !$pageNo )
		{
			return;
		}

		if ( $printFooter )
		{
			$this->createFooter($printGrandTotal);
		}

		$this->createHeader(true);

		$this->newLine();
	}

	public function finishExportProcess()
	{
		$this->fileInfo = $this->writeExcel();
	}

	public function startBillCounter()
	{
		$this->firstCol = $this->colItem;
		$this->lastCol  = $this->colTotal;

		$this->currentElementNo = 0;
		$this->columnSetting    = null;
	}

	public function createHeader($new = false)
	{
		$row = $this->currentRow;

		//set default column
		$this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_NO);
		$this->activeSheet->setCellValue($this->colDescription . $row, 'Element Description');
		$this->activeSheet->setCellValue($this->colPercent . $row, '%');
		$this->activeSheet->setCellValue($this->colCostMetric . $row, $this->costMetric);
		$this->activeSheet->setCellValue($this->colTotal . $row, 'Total');

		// Set header styling
		$this->activeSheet->getStyle($this->colItem . $row . ':' . $this->colTotal . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());
		$this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

		$this->activeSheet->mergeCells($this->colItem . $row . ':' . $this->colItem . $this->currentRow);
		$this->activeSheet->mergeCells($this->colDescription . $row . ':' . $this->colDescription . $this->currentRow);

		//Set Column Sizing
		$this->activeSheet->getColumnDimension("A")->setWidth(1.3);
		$this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getColumnDimension($this->colPercent)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colCostMetric)->setWidth(16);
		$this->activeSheet->getColumnDimension($this->colTotal)->setWidth(16);
	}

	public function processItems($item)
	{
		parent::setValue($this->colPercent, ( !empty( $item[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_PERCENTAGE] ) ) ? number_format($item[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_PERCENTAGE], 2) . '%' : null);

		parent::setValue($this->colCostMetric, ( !empty( $item[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_TOTAL_COST] ) ) ? number_format($item[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_TOTAL_COST], 2) : null);

		parent::setValue($this->colTotal, ( !empty( $item[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_TOTAL_PER_UNIT] ) ) ? number_format($item[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_TOTAL_PER_UNIT], 2) : null);

		$this->totalPercentage += $item[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_PERCENTAGE];
		$this->totalCost += $item[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_TOTAL_COST];
		$this->total += $item[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_TOTAL_PER_UNIT];
	}

	public function createFooterPageNo()
	{
		if ( $this->isLastPage )
		{
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

		parent::setValue($this->colPercent, ( !empty( $this->totalPercentage ) ) ? number_format($this->totalPercentage, 2) . '%' : null);

		parent::setValue($this->colCostMetric, ( !empty( $this->totalCost ) ) ? number_format($this->totalCost, 2) : null);

		parent::setValue($this->colTotal, ( !empty( $this->total ) ) ? number_format($this->total, 2) : null);

		$this->activeSheet->getStyle($this->colDescription . $row)->applyFromArray(array(
			'font'      => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			)
		));

		$this->activeSheet->getStyle($this->colPercent . $row . ':' . $this->colTotal . $this->currentRow)->applyFromArray($this->getPageConclusionStyling());

		$this->currentRow ++;

		$this->totalPercentage = 0;
		$this->totalCost       = 0;
		$this->total           = 0;
	}

	public function setCostMetric($costMetric)
	{
		$this->costMetric = $costMetric;
	}

	public function isLastPage($isLastPage)
	{
		$this->isLastPage = $isLastPage;
	}

	public function getPageConclusionStyling()
	{
		$columnHeadStyle = array(
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

		return $columnHeadStyle;
	}

}