<?php

class sfBuildSpaceStockInDOItemReportExcelGenerator extends sfPostContractElementReportGenerator {

	public $currentPageNo = 1;

	public $colUnit = 'D';

	public $colInvoiceQty = 'E';

	public $colDOQty = 'F';

	public function process($itemPages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage = 0)
	{
		$description   = '';
		$char          = '';
		$prevItemType  = '';
		$prevItemLevel = 0;

		$this->createSheet($header, $subTitle, $topLeftTitle);

		foreach ( $itemPages as $page )
		{
			if ( empty( $page ) )
			{
				continue;
			}

			$this->createNewPage($this->currentPageNo);

			foreach ( $page as $item )
			{
				$itemType = $item[4];

				switch ($itemType)
				{
					case ResourceItem::TYPE_HEADER:

						$description .= $item[2] . "\n";
						$prevItemType  = $item[4];
						$prevItemLevel = $item[3];

						break;

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

			$this->currentPageNo ++;
		}
	}

	public function createSheet($billHeader = null, $topLeftTitle = '', $subTitle = '')
	{
		$this->setBillHeader($billHeader, $topLeftTitle, $subTitle);
	}

	public function finishExportProcess()
	{
		$this->fileInfo = $this->writeExcel();
	}

	public function startBillCounter()
	{
		$this->currentRow = $this->startRow;
		$this->firstCol   = $this->colItem;
		$this->lastCol    = $this->colDOQty;

		$this->currentElementNo = 0;
		$this->columnSetting    = null;
	}

	public function createHeader($new = false)
	{
		$row = $this->currentRow;

		//set default column
		$this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_NO);
		$this->activeSheet->setCellValue($this->colDescription . $row, self::COL_NAME_DESCRIPTION);
		$this->activeSheet->setCellValue($this->colUnit . $row, self::COL_NAME_UNIT);
		$this->activeSheet->setCellValue($this->colInvoiceQty . $row, 'Invoice Qty');
		$this->activeSheet->setCellValue($this->colDOQty . $row, 'DO Qty');

		// Set header styling
		$this->activeSheet->getStyle($this->colItem . $row . ':' . $this->colDOQty . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());
		$this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

		$this->activeSheet->mergeCells($this->colItem . $row . ':' . $this->colItem . $this->currentRow);
		$this->activeSheet->mergeCells($this->colDescription . $row . ':' . $this->colDescription . $this->currentRow);

		//Set Column Sizing
		$this->activeSheet->getColumnDimension("A")->setWidth(1.3);
		$this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getColumnDimension($this->colUnit)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colInvoiceQty)->setWidth(8);
		$this->activeSheet->getColumnDimension($this->colDOQty)->setWidth(8);
	}

	public function processItems($item)
	{
		$this->setUnit($item[sfBuildSpaceStockInDOItemReportPageGenerator::ROW_BILL_ITEM_UNIT]);

		parent::setValue($this->colInvoiceQty, ( !empty( $item[sfBuildSpaceStockInDOItemReportPageGenerator::ROW_BILL_ITEM_INVOICE_QTY] ) ) ? number_format($item[sfBuildSpaceStockInDOItemReportPageGenerator::ROW_BILL_ITEM_INVOICE_QTY], 2) : null);

		parent::setValue($this->colDOQty, ( !empty( $item[sfBuildSpaceStockInDOItemReportPageGenerator::ROW_BILL_ITEM_DO_QTY] ) ) ? number_format($item[sfBuildSpaceStockInDOItemReportPageGenerator::ROW_BILL_ITEM_DO_QTY], 2) : null);
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

}