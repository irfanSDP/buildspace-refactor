<?php

class sfBuildSpaceStockOutItemReportExcelGenerator extends sfPostContractElementReportGenerator {

	private $currentPageNo = 1;

	public $colUnit = "D";

	public $colTotalCostWithoutTax = "E";

	public $colTotalCostWithTax = "F";

	public $colTotalCostDiff = "G";

	public $colDOQty = "H";

	public $colStockOutQty = "I";

	public $colBalanceQty = "J";

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
		$this->lastCol    = $this->colBalanceQty;

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
		$this->activeSheet->setCellValue($this->colTotalCostWithoutTax . $row, 'Total Cost without Tax');
		$this->activeSheet->setCellValue($this->colTotalCostWithTax . $row, 'Total Cost with Tax');
		$this->activeSheet->setCellValue($this->colTotalCostDiff . $row, 'Total GST Amount');
		$this->activeSheet->setCellValue($this->colDOQty . $row, 'DO Qty');
		$this->activeSheet->setCellValue($this->colStockOutQty . $row, 'Stock Out Qty');
		$this->activeSheet->setCellValue($this->colBalanceQty . $row, 'Balance Qty');

		// Set header styling
		$this->activeSheet->getStyle($this->colItem . $row . ':' . $this->colBalanceQty . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());
		$this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

		$this->activeSheet->mergeCells($this->colItem . $row . ':' . $this->colItem . $this->currentRow);
		$this->activeSheet->mergeCells($this->colDescription . $row . ':' . $this->colDescription . $this->currentRow);

		//Set Column Sizing
		$this->activeSheet->getColumnDimension("A")->setWidth(1.3);
		$this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getColumnDimension($this->colUnit)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colTotalCostWithoutTax)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colTotalCostWithTax)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colTotalCostDiff)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colDOQty)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colStockOutQty)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colBalanceQty)->setWidth(12);
	}

	public function processItems($item)
	{
		$this->setUnit($item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_UNIT]);

		parent::setValue($this->colTotalCostWithoutTax, ( !empty( $item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_TOTAL_COST_WITHOUT_TAX] ) ) ? number_format($item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_TOTAL_COST_WITHOUT_TAX], 2) : null);

		parent::setValue($this->colTotalCostWithTax, ( !empty( $item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_TOTAL_COST_WITH_TAX] ) ) ? number_format($item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_TOTAL_COST_WITH_TAX], 2) : null);

		$totalCostDiff = $item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_TOTAL_COST_WITH_TAX] - $item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_TOTAL_COST_WITHOUT_TAX];

		parent::setValue($this->colTotalCostDiff, ( !empty( $totalCostDiff ) ) ? number_format($totalCostDiff, 2) : null);

		parent::setValue($this->colDOQty, ( !empty( $item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_DO_QTY] ) ) ? number_format($item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_DO_QTY], 2) : null);

		parent::setValue($this->colStockOutQty, ( !empty( $item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_STOCK_OUT_QTY] ) ) ? number_format($item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_STOCK_OUT_QTY], 2) : null);

		parent::setValue($this->colBalanceQty, ( !empty( $item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_BALANCE_QTY] ) ) ? number_format($item[sfBuildSpaceStockOutItemReportPageGenerator::ROW_BILL_ITEM_BALANCE_QTY], 2) : null);
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