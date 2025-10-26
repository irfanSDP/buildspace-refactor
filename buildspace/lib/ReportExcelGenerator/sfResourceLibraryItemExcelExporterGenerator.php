<?php

class sfResourceLibraryItemExcelExporterGenerator extends sfBuildspaceExcelReportGenerator {

	private $pageNo = 1;

	public $colItem = 'B';
	public $colDescription = 'C';
	public $colConstant = 'D';
	public $colUnit = 'E';
	public $colRate = 'F';
	public $colWastage = 'G';

	public $currentRow = 1;

	public $lastCol;

	public function __construct($printingPageTitle, $printSettings)
	{
		$filename = $printingPageTitle;
		$savePath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

		parent::__construct(null, $savePath, $filename, $printSettings);
	}

	public function process($pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
	{
		$this->setExcelParameter($lock, $withoutCents);

		$this->createSheet($header, $subTitle, $topLeftTitle);

		$description   = '';
		$char          = '';
		$prevItemType  = '';
		$prevItemLevel = 0;

		$this->createNewPage($this->pageNo, false, 0);

		foreach ( $pages as $i => $item )
		{
			if ( !$item )
			{
				continue;
			}

			$itemType = $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_TYPE];

			switch ($itemType)
			{
				case self::ROW_TYPE_BLANK:

					if ( $description != '' && $prevItemType != '' )
					{
						if ( $prevItemType == ResourceItem::TYPE_HEADER )
						{
							$this->newItem();

							if ( strpos($description, $this->printSettings['layoutSetting']['contdPrefix']) !== false )
							{
								$this->setItemHead($description, $prevItemType, $prevItemLevel);
							}
							else
							{
								$this->setItemHead($description, $prevItemType, $prevItemLevel, true);
							}
						}

						$description = '';
					}
					break;

				case ResourceItem::TYPE_HEADER:
					$description .= $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
					$prevItemType  = $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_TYPE];
					$prevItemLevel = $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_LEVEL];

					break;

				default:
					$description .= $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
					$char .= $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_ROW_IDX];

					if ( $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_ID] )
					{
						$this->newItem();

						$this->setResourceItem($item, $description, $char);

						$description = '';
						$char        = '';
					}

					break;
			}
		}

		$this->createFooter(false);

		$this->pageNo ++;
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
		$this->activeSheet->setCellValue($this->colItem . $row, 'No');
		$this->activeSheet->setCellValue($this->colDescription . $row, 'Description');
		$this->activeSheet->setCellValue($this->colConstant . $row, 'Constant');
		$this->activeSheet->setCellValue($this->colUnit . $row, 'Unit');
		$this->activeSheet->setCellValue($this->colRate . $row, 'Rate');
		$this->activeSheet->setCellValue($this->colWastage . $row, 'Wastage %');

		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getStyle($this->colDescription)->applyFromArray($this->getDescriptionStyling());

		$this->activeSheet->getColumnDimension($this->colUnit)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colRate)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colWastage)->setWidth(12);

		$this->activeSheet->getStyle("{$this->firstCol}{$row}:{$this->lastCol}{$row}")->applyFromArray($this->getColumnHeaderStyle());
	}

	public function setResourceItem($item, $description, $char)
	{
		$row         = $this->currentRow;
		$factorValue = null;
		$totalValue  = null;

		$this->activeSheet->setCellValue($this->colItem . $row, $char);
		$this->activeSheet->getStyle($this->colItem . $row)->applyFromArray($this->getNoStyle());

		$this->activeSheet->setCellValue($this->colDescription . $row, $description);

		$this->activeSheet->setCellValue($this->colConstant . $row, $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_CONSTANT]['value']);
		$this->activeSheet->getStyle($this->colConstant . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colConstant . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colUnit . $row, $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_UNIT]);
		$this->activeSheet->getStyle($this->colUnit . $row)->applyFromArray($this->getUnitStyle());

		$this->activeSheet->setCellValue($this->colRate . $row, $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_RATE]['value']);
		$this->activeSheet->getStyle($this->colRate . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colRate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colWastage . $row, $item[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_WASTAGE]['value']);
		$this->activeSheet->getStyle($this->colWastage . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colWastage . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
	}

	public function createFooter($printGrandTotal = false)
	{
		$this->newLine(true);

		$this->currentRow ++;

		if ( $printGrandTotal )
		{
			$this->printGrandTotal();
		}

		if ( $this->currentPage >= 1 )
		{
			$this->activeSheet->setBreak($this->colDescription . $this->currentRow, PHPExcel_Worksheet::BREAK_ROW);

			$this->createFooterPageNo();
		}

		if ( $printGrandTotal || $this->currentPage >= 1 )
		{
			$this->currentRow += 2;
		}
	}

	public function createFooterPageNo()
	{
		$this->currentRow ++;

		$location = $this->colItem . $this->currentRow;

		$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);

		$this->currentRow ++;

		$text = "Page {$this->currentPage}";

		$pageNoStyle = array(
			'font' => array(
				'bold' => true
			)
		);

		$this->activeSheet->setCellValue($location, $text);
		$this->activeSheet->getStyle($location)->applyFromArray($pageNoStyle);
	}

	public function printTotalText($title = false)
	{
	}

	public function printGrandTotalValue($style)
	{
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
		$this->lastCol  = $this->colWastage;
	}

	public function getDescriptionStyling()
	{
		return array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);
	}

	public function setTitle($title = null)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Text' ));

		return $this->activeSheet->setTitle(truncate_text('Item(s) (Selected)'));
	}

}