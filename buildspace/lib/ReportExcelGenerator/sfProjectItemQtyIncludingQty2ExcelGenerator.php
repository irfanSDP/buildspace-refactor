<?php

class sfProjectItemQtyIncludingQty2ExcelGenerator extends sfBuildspaceExcelReportGenerator {

	private $pageNo = 1;

	public $colUnit = 'D';
	public $colQty1 = 'E';
	public $colQty2 = 'F';
	public $colDiff = 'G';

	public $currentRow = 1;

	public function __construct(ProjectStructure $project, $printingPageTitle, $printSettings)
	{
		$filename = ( $printingPageTitle ) ? $printingPageTitle : $project->title . '-' . date('dmY H_i_s');
		$savePath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

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
				$itemType = $item[sfBillItemQtyIncludingQty2ReportGenerator::ROW_BILL_ITEM_TYPE];

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
		$this->activeSheet->setCellValue($this->colUnit . $row, self::COL_NAME_UNIT);

		$this->activeSheet->setCellValue($this->colQty1 . $row, 'Quantities');
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
		$this->activeSheet->mergeCells($this->colUnit . $row . ':' . $this->colUnit . $this->currentRow);
		$this->activeSheet->mergeCells($this->colDiff . $row . ':' . $this->colDiff . $this->currentRow);

		//Set Column Sizing
		$this->activeSheet->getColumnDimension("A")->setWidth(1.3);
		$this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getColumnDimension($this->colUnit)->setWidth(14);
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
		$unit = $item ? $item[sfBillItemQtyIncludingQty2ReportGenerator::ROW_BILL_ITEM_UNIT] : '';

		parent::setUnit($unit);

		parent::setValue($this->colQty1, ( $item[sfBillItemQtyIncludingQty2ReportGenerator::QTY_1] == 0 ) ? null : $item[sfBillItemQtyIncludingQty2ReportGenerator::QTY_1]);

		parent::setValue($this->colQty2, ( $item[sfBillItemQtyIncludingQty2ReportGenerator::QTY_2] == 0 ) ? null : $item[sfBillItemQtyIncludingQty2ReportGenerator::QTY_2]);

		parent::setValue($this->colDiff, ( $item[sfBillItemQtyIncludingQty2ReportGenerator::DIFFERENCE] == 0 ) ? null : $item[sfBillItemQtyIncludingQty2ReportGenerator::DIFFERENCE]);
	}

	public function printGrandTotal()
	{
		return false;
	}

}