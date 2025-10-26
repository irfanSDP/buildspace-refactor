<?php

class sfProjectItemAmtIncludingQty2ExcelGenerator extends sfBuildspaceExcelReportGenerator {

	private $pageNo = 1;

	public $colQty1 = 'D';
	public $colQty2 = 'E';
	public $colDiff = 'F';

	public $currentRow = 1;

	private $qty1TotalAmount = 0;
	private $qty2TotalAmount = 0;

	public function __construct(ProjectStructure $project, $printingPageTitle, $printSettings)
	{
		$filename       = ( $printingPageTitle ) ? $printingPageTitle : $project->title . '-' . date('dmY H_i_s');
		$savePath       = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';
		$this->currency = $project->MainInformation->Currency->currency_code;

		parent::__construct($project, $savePath, $filename, $printSettings);
	}

	public function process($pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
	{
		$this->setExcelParameter($lock, $withoutCents);

		$description   = '';
		$char          = '';
		$prevItemType  = '';
		$prevItemLevel = 0;

		$this->createSheet($header, $subTitle, $topLeftTitle);

		if ( !( $pages instanceof SplFixedArray ) )
		{
			return;
		}

		foreach ( $pages as $i => $page )
		{
			if ( !$page )
			{
				continue;
			}

			$lastPage = ( ( $i + 1 ) == $pages->count() ) ? true : false;

			$this->createNewPage($this->pageNo, false, 0);

			foreach ( $page as $item )
			{
				$itemType = $item[sfBillItemAmountIncludingQty2ReportGenerator::ROW_BILL_ITEM_TYPE];

				switch ($itemType)
				{
					case self::ROW_TYPE_BLANK:
						if ( $description != '' && $prevItemType != '' )
						{
							if ( $prevItemType == BillItem::TYPE_HEADER_N || $prevItemType == BillItem::TYPE_HEADER )
							{
								$this->newItem();

								$this->setItemHead($description, $prevItemType, $prevItemLevel, true);
							}

							$description = '';
						}

						break;

					case BillItem::TYPE_HEADER_N:
					case BillItem::TYPE_HEADER:
						$description .= $item[2] . "\n";
						$prevItemType  = $item[4];
						$prevItemLevel = $item[3];

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

			$this->createFooter($lastPage);

			$this->pageNo ++;
		}
	}

	public function createSheet($billHeader = null, $topLeftTitle = '', $subTitle = '')
	{
		$this->setActiveSheet(0);

		$this->startBillCounter();

		$this->setBillHeader($billHeader, $topLeftTitle, $subTitle);
	}

	public function createHeader($new = false)
	{
		$row = $this->currentRow;

		//set default column
		$this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_NO);
		$this->activeSheet->setCellValue($this->colDescription . $row, self::COL_NAME_DESCRIPTION);

		$this->activeSheet->setCellValue($this->colQty1 . $row, 'Amount');
		$this->activeSheet->mergeCells($this->colQty1 . $this->currentRow . ':' . $this->colQty2 . $this->currentRow);

		$this->activeSheet->setCellValue($this->colDiff . $row, 'Differences %');

		$this->currentRow ++;

		$this->activeSheet->setCellValue($this->colQty1 . $this->currentRow, 'Qty');
		$this->activeSheet->setCellValue($this->colQty2 . $this->currentRow, 'Qty 2');
		$this->activeSheet->getColumnDimension($this->colQty1)->setWidth(14);
		$this->activeSheet->getColumnDimension($this->colQty2)->setWidth(16);

		//Set header styling
		$this->activeSheet->getStyle($this->colItem . $row . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());
		$this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

		$this->activeSheet->mergeCells($this->colItem . $row . ':' . $this->colItem . $this->currentRow);
		$this->activeSheet->mergeCells($this->colDescription . $row . ':' . $this->colDescription . $this->currentRow);
		$this->activeSheet->mergeCells($this->colDiff . $row . ':' . $this->colDiff . $this->currentRow);

		//Set Column Sizing
		$this->activeSheet->getColumnDimension("A")->setWidth(1.3);
		$this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getColumnDimension($this->colDiff)->setWidth(14);
	}

	public function createFooterPageNo()
	{
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

	public function setBillHeader($billHeader = null, $topLeftTitle, $subTitle)
	{
		$billHeader = ( $billHeader ) ? $billHeader : $this->filename;

		//Set Top Header
		$this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $billHeader);
		$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);
		$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getProjectTitleStyle());
		$this->currentRow ++;

		//Set SubTitle
		$this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $subTitle);
		$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);
		$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getSubTitleStyle());
		$this->currentRow ++;

		$this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $topLeftTitle);
		$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->colDescription . $this->currentRow);
		$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->colDescription . $this->currentRow)->applyFromArray($this->getLeftTitleStyle());
		$this->currentRow ++;
	}

	public function startBillCounter()
	{
		$this->firstCol = $this->colItem;
		$this->lastCol  = $this->colDiff;
	}

	public function processItems($item)
	{
		$unit = $item ? $item[sfBillItemAmountIncludingQty2ReportGenerator::ROW_BILL_ITEM_UNIT] : '';

		parent::setUnit($unit);

		parent::setValue($this->colQty1, ( $item[sfBillItemAmountIncludingQty2ReportGenerator::QTY_1] == 0 ) ? null : $item[sfBillItemAmountIncludingQty2ReportGenerator::QTY_1]);

		parent::setValue($this->colQty2, ( $item[sfBillItemAmountIncludingQty2ReportGenerator::QTY_2] == 0 ) ? null : $item[sfBillItemAmountIncludingQty2ReportGenerator::QTY_2]);

		parent::setValue($this->colDiff, ( $item[sfBillItemAmountIncludingQty2ReportGenerator::DIFFERENCE] == 0 ) ? null : $item[sfBillItemAmountIncludingQty2ReportGenerator::DIFFERENCE]);
	}

	public function printGrandTotal()
	{
		$newLineStyle = array(
			'borders' => array(
				'vertical' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				),
				'outline'  => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				),
				'top'      => array(
					'style' => PHPExcel_Style_Border::BORDER_NONE,
					'color' => array( 'argb' => '000000' ),
				),
				'bottom'   => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				)
			)
		);

		$totalStyle = array(
			'font'      => array(
				'bold' => true
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				'wrapText'   => true
			)
		);

		$this->generateTotalRow($newLineStyle, $totalStyle);

		$this->generateNettRow($newLineStyle, $totalStyle);
	}

	public function setQty1TotalAmount($amount)
	{
		$this->qty1TotalAmount = $amount;
	}

	public function setQty2TotalAmount($amount)
	{
		$this->qty2TotalAmount = $amount;
	}

	public function printTotalText($title = false)
	{
		$this->activeSheet->setCellValue($this->colDescription . $this->currentRow, ( $title ) ? $title : "Total ({$this->currency}):");
	}

	public function printGrandTotalValue($style)
	{
		$this->activeSheet->getStyle($this->colQty1 . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);
	}

	private function generateTotalRow($newLineStyle, $totalStyle)
	{
		$this->activeSheet->getStyle($this->colDescription . $this->currentRow)->applyFromArray($totalStyle);

		$this->printTotalText();

		parent::setValue($this->colQty1, ( $this->qty1TotalAmount == 0 ) ? null : $this->qty1TotalAmount);
		parent::setValue($this->colQty2, ( $this->qty2TotalAmount == 0 ) ? null : $this->qty2TotalAmount);
		parent::setValue($this->colDiff, Utilities::percent($this->qty2TotalAmount - $this->qty1TotalAmount, $this->qty1TotalAmount));

		$this->activeSheet->getStyle($this->colQty1 . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($newLineStyle);

		$this->currentRow ++;
	}

	private function generateNettRow($newLineStyle, $totalStyle)
	{
		$this->activeSheet->getStyle($this->colDescription . $this->currentRow)->applyFromArray($totalStyle);
		$this->printTotalText("Nett Difference ({$this->currency}):");

		$this->activeSheet->mergeCells($this->colQty1 . $this->currentRow . ':' . $this->colQty2 . $this->currentRow);

		parent::setValue($this->colQty1, $this->qty2TotalAmount - $this->qty1TotalAmount);

		$this->activeSheet->getStyle($this->colQty1 . $this->currentRow . ":" . $this->colQty2 . $this->currentRow)->applyFromArray($newLineStyle);

		$this->currentRow ++;
	}

}