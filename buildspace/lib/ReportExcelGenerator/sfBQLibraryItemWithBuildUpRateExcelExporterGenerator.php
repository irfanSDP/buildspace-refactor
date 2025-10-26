<?php 

class sfBQLibraryItemWithBuildUpRateExcelExporterGenerator extends sfBuildspaceExcelReportGenerator {

	private $bqItemInfo;
	private $bqItem;
	private $buildUpRateSummaryInfo;
	private $lastPage = false;

	private $pageNo = 1;

	public $colItem = 'B';
	public $colDescription = 'C';
	public $colNumber = 'D';
	public $colConstant = 'E';
	public $colQty = 'F';
	public $colUnit = 'G';
	public $colRate = 'H';
	public $colTotal = 'I';
	public $colWastage = 'J';
	public $colLineTotal = 'K';

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
		$prevItemLevel = '';

		$this->createNewPage($this->pageNo, false, 0);

		$this->createResourceItemHeader();

		foreach ( $pages as $i => $item )
		{
			if ( !$item )
			{
				continue;
			}

			$itemType = $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_TYPE];

			switch ($itemType)
			{
				case self::ROW_TYPE_BLANK:

					if ( $description != '' && $prevItemType != '' )
					{
						if ( $prevItemType == ResourceItem::TYPE_HEADER )
						{
							$this->newItem();

							parent::setChar($char);

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
						$char        = '';
					}
					break;

				case ResourceItem::TYPE_HEADER:
					$description .= $item[2] . "\n";
					$char .= $item[1];
					$prevItemType  = $item[4];
					$prevItemLevel = $item[3];

					break;

				default:
					$description .= $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
					$char .= $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_ROW_IDX];

					if ( $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_ID] )
					{
						$this->newItem();

						$this->setBuildUpItemRow($item, $description, $char);

						$description = '';
						$char        = '';
					}

					break;
			}
		}

		$this->createFooter($this->lastPage);

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
		$this->activeSheet->setCellValue($this->colNumber . $row, 'Number');
		$this->activeSheet->setCellValue($this->colConstant . $row, 'Constant');
		$this->activeSheet->setCellValue($this->colQty . $row, 'Qty');
		$this->activeSheet->setCellValue($this->colUnit . $row, 'Unit');
		$this->activeSheet->setCellValue($this->colRate . $row, 'Rate');
		$this->activeSheet->setCellValue($this->colTotal . $row, 'Total');
		$this->activeSheet->setCellValue($this->colWastage . $row, 'Wastage');
		$this->activeSheet->setCellValue($this->colLineTotal . $row, 'Line Total');

		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getStyle($this->colDescription)->applyFromArray($this->getDescriptionStyling());

		$this->activeSheet->getColumnDimension($this->colNumber)->setWidth(18);
		$this->activeSheet->getColumnDimension($this->colConstant)->setWidth(18);
		$this->activeSheet->getColumnDimension($this->colQty)->setWidth(18);
		$this->activeSheet->getColumnDimension($this->colUnit)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colRate)->setWidth(18);
		$this->activeSheet->getColumnDimension($this->colTotal)->setWidth(18);
		$this->activeSheet->getColumnDimension($this->colWastage)->setWidth(18);
		$this->activeSheet->getColumnDimension($this->colLineTotal)->setWidth(18);

		$this->activeSheet->getStyle("{$this->firstCol}{$row}:{$this->lastCol}{$row}")->applyFromArray($this->getColumnHeaderStyle());
	}

	public function setBuildUpItemRow($item, $description, $char)
	{
		$row         = $this->currentRow;
		$factorValue = null;
		$totalValue  = null;

		$this->activeSheet->setCellValue($this->colItem . $row, $char);
		$this->activeSheet->getStyle($this->colItem . $row)->applyFromArray($this->getNoStyle());

		$this->activeSheet->setCellValue($this->colDescription . $row, $description);

		$this->activeSheet->setCellValue($this->colNumber . $row, $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_NUMBER]);
		$this->activeSheet->getStyle($this->colNumber . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colNumber . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colConstant . $row, $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_CONSTANT]['final_value']);
		$this->activeSheet->getStyle($this->colConstant . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colConstant . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colQty . $row, $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_QTY]['final_value']);
		$this->activeSheet->getStyle($this->colQty . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colQty . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colUnit . $row, $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_UNIT]);
		$this->activeSheet->getStyle($this->colUnit . $row)->applyFromArray($this->getUnitStyle());

		$this->activeSheet->setCellValue($this->colRate . $row, $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_RATE]['final_value']);
		$this->activeSheet->getStyle($this->colRate . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colRate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colTotal . $row, $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_TOTAL]);
		$this->activeSheet->getStyle($this->colTotal . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colTotal . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colWastage . $row, $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_WASTAGE]['final_value']);
		$this->activeSheet->getStyle($this->colWastage . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colWastage . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colLineTotal . $row, $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_LINE_TOTAL]);
		$this->activeSheet->getStyle($this->colLineTotal . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colLineTotal . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
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

	public function createResourceItemHeader()
	{
		$description = '';

		foreach ( $this->bqItemInfo as $item )
		{
			$itemType = $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_TYPE];

			switch ($itemType)
			{
				case self::ROW_TYPE_BLANK:
					$this->newLine();
					break;

				default:
					$description .= $item[sfBQLibraryItemBuildUpRateReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";

					if ( $item[0] )
					{
						$this->setScheduleOfRateItemRow(strip_tags($description));

						$description = '';
					}

					break;
			}
		}
	}

	public function startBillCounter()
	{
		$this->firstCol = $this->colItem;
		$this->lastCol  = $this->colLineTotal;
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

	public function getProjectNameStyling()
	{
		return array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);
	}

	public function setTitle($title = null)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Text' ));

		return $this->activeSheet->setTitle(truncate_text('Item(s) (Selected)'));
	}

	public function setBQLibraryItemInfo($bqItemInfo)
	{
		$this->bqItemInfo = $bqItemInfo;
	}

	public function setBQLibraryItem($bqItem)
	{
		$this->bqItem = $bqItem;
	}

	public function setBuildUpRateSummaryInfo($buildUpRateSummaryInfo)
	{
		$this->buildUpRateSummaryInfo = $buildUpRateSummaryInfo;
	}

	public function setIsLastPage($lastPage)
	{
		$this->lastPage = $lastPage;
	}

	private function setScheduleOfRateItemRow($description)
	{
		$row = $this->currentRow;

		$styling['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
		$styling['borders']['bottom']['color'] = array( 'argb' => '000000' );

		$this->activeSheet->setCellValue($this->colDescription . $row, $description);

		$this->activeSheet->setCellValue($this->colUnit . $row, $this->bqItem['uom_symbol']);
		$this->activeSheet->getStyle($this->colUnit . $row)->applyFromArray($this->getUnitStyle());

		$this->activeSheet->setCellValue($this->colLineTotal . $row, $this->bqItem['total_rate']);
		$this->activeSheet->getStyle($this->colLineTotal . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colLineTotal . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->getStyle("{$this->firstCol}{$row}:{$this->lastCol}{$row}")->applyFromArray($styling);
	}

	public function printGrandTotal()
	{
		$previousCol = PHPExcel_Cell::columnIndexFromString($this->colLineTotal);
		$previousCol = PHPExcel_Cell::stringFromColumnIndex($previousCol - 2);

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
					'color' => array( 'argb' => 'FFFFFF' ),
				),
				'bottom'   => array(
					'style' => PHPExcel_Style_Border::BORDER_NONE,
					'color' => array( 'argb' => 'FFFFFF' ),
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

		$newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
		$newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );

		$this->generateFinalTotalCostRow($previousCol, $totalStyle, $newLineStyle);

		if ( isset( $this->buildUpRateSummaryInfo['apply_conversion_factor'] ) AND $this->buildUpRateSummaryInfo['apply_conversion_factor'] )
		{
			$this->generateConversionFactorRow($previousCol, $totalStyle, $newLineStyle);
		}

		$this->generateMarkUpPercentRow($previousCol, $totalStyle, $newLineStyle);
	}

	private function generateFinalTotalCostRow($previousCol, $totalStyle, $newLineStyle)
	{
		$value = null;

		if ( $this->buildUpRateSummaryInfo['total_cost'] != 0 )
		{
			$value = $this->buildUpRateSummaryInfo['total_cost'];
		}

		$this->activeSheet->getStyle($previousCol . $this->currentRow)->applyFromArray($totalStyle);
		$this->activeSheet->setCellValue($previousCol . $this->currentRow, 'Total Cost');
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colLineTotal . $this->currentRow, $value);
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

	private function generateConversionFactorRow($previousCol, $totalStyle, $newLineStyle)
	{
		$value = null;

		if ( $this->buildUpRateSummaryInfo['conversion_factor_amount'] != 0 )
		{
			$value = $this->buildUpRateSummaryInfo['conversion_factor_amount'];
		}

		$this->activeSheet->getStyle($previousCol . $this->currentRow)->applyFromArray($totalStyle);
		$this->activeSheet->setCellValue($previousCol . $this->currentRow, 'Conversion Factor (' . $this->buildUpRateSummaryInfo['conversion_factor_operator'] . ')');
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colLineTotal . $this->currentRow, $value);
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

	private function generateMarkUpPercentRow($previousCol, $totalStyle, $newLineStyle)
	{
		$value = null;

		if ( $this->buildUpRateSummaryInfo['markup'] != 0 )
		{
			$value = $this->buildUpRateSummaryInfo['markup'];
		}

		$this->activeSheet->getStyle($previousCol . $this->currentRow)->applyFromArray($totalStyle);
		$this->activeSheet->setCellValue($previousCol . $this->currentRow, 'Mark Up %');
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colLineTotal . $this->currentRow, $value);
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

}