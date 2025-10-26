<?php

class sfBillItemBuildUpRateExcelExporterGenerator extends sfBuildspaceExcelReportGenerator {

	protected $columnDimensions = array();
	protected $buildUpQuantitySummaryInfo = array();
	protected $ratePerUnit = array();
	private $pageNo = 1;

	public $colItem = "B";
	public $colDescription = "C";
	public $colNumber = "D";
	public $colConstant = "E";
	public $colQty = "F";
	public $colUnit = "G";
	public $colRate = "H";
	public $colTotal = "I";
	public $colWastage = "J";
	public $colLineTotal = "K";

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

		$description = '';
		$char        = '';

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

			// will create bill item header first
			$this->createBillItemHeader();

			$lastItemKey        = count($page);
			$lastItemKeyCounter = 1;

			foreach ( $page as $item )
			{
				$lastItemKeyCounter ++;

				$itemType = $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_TYPE];

				switch ($itemType)
				{
					case ResourceItem::TYPE_HEADER:
						$description = $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";

						$this->newItem();

						$this->setBuildUpItem($item, $description, $char, $itemType);

						if ( $lastItemKey != $lastItemKeyCounter )
						{
							$this->newLine();
						}

						$description = '';
						$char        = '';
						break;

					case self::ROW_TYPE_BLANK:
						break;

					default:
						$description .= $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
						$char .= $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_ROW_IDX];

						if ( $item[0] )
						{
							$this->newItem();

							$this->setBuildUpItem($item, $description, $char, $itemType);

							if ( $lastItemKey != $lastItemKeyCounter )
							{
								$this->newLine();
							}

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

	public function createBillItemHeader()
	{
		$description = '';
		$char        = '';

		foreach ( $this->billItemInfo as $item )
		{
			$itemType = $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_TYPE];

			switch ($itemType)
			{
				case self::ROW_TYPE_BLANK:
					$this->newLine();
					break;

				default:
					$description .= $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
					$char .= $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_ROW_IDX];

					if ( $item[0] )
					{
						$this->setBillItemDescriptionRow(strip_tags($description), $char, $itemType);

						$description = '';
						$char        = '';
					}

					break;
			}
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
		$this->activeSheet->setCellValue($this->colItem . $row, 'No');
		$this->activeSheet->setCellValue($this->colDescription . $row, 'Description');
		$this->activeSheet->setCellValue($this->colNumber . $row, 'Number');
		$this->activeSheet->setCellValue($this->colConstant . $row, 'Constant');
		$this->activeSheet->setCellValue($this->colQty . $row, 'Qty');
		$this->activeSheet->setCellValue($this->colUnit . $row, 'Unit');
		$this->activeSheet->setCellValue($this->colRate . $row, 'Rate');
		$this->activeSheet->setCellValue($this->colTotal . $row, 'Total');
		$this->activeSheet->setCellValue($this->colWastage . $row, 'Wastage %');
		$this->activeSheet->setCellValue($this->colLineTotal . $row, 'Line Total');

		// Set Column Width
		$this->activeSheet->getColumnDimension("A")->setWidth(1.3);
		$this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);

		$this->activeSheet->getColumnDimension($this->colNumber)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colConstant)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colQty)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colRate)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colTotal)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colWastage)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colLineTotal)->setWidth(12);

		$this->activeSheet->getStyle("{$this->firstCol}{$row}:{$this->lastCol}{$row}")->applyFromArray($this->getColumnHeaderStyle());
	}

	public function setBillItemDescriptionRow($description, $char, $itemType)
	{
		$row         = $this->currentRow;
		$refStyling  = $this->getNoStyle();
		$descStyling = $this->getDescriptionStyling();
		$rateStyling = $this->getRateStyling();

		$refStyling['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
		$refStyling['borders']['bottom']['color'] = array( 'argb' => '000000' );

		$descStyling['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
		$descStyling['borders']['bottom']['color'] = array( 'argb' => '000000' );

		$rateStyling['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
		$rateStyling['borders']['bottom']['color'] = array( 'argb' => '000000' );

		$this->activeSheet->setCellValue($this->colItem . $row, $char);
		$this->activeSheet->getStyle($this->colItem . $row)->applyFromArray($refStyling);

		$this->activeSheet->setCellValue($this->colDescription . $row, $description);
		$this->activeSheet->getStyle($this->colDescription . $row)->applyFromArray($descStyling);

		$this->activeSheet->getStyle($this->colNumber . $row)->applyFromArray($descStyling);
		$this->activeSheet->getStyle($this->colConstant . $row)->applyFromArray($descStyling);
		$this->activeSheet->getStyle($this->colQty . $row)->applyFromArray($descStyling);

		$this->activeSheet->setCellValue($this->colUnit . $row, $this->billItemUOM);
		$this->activeSheet->getStyle($this->colUnit . $row)->applyFromArray($refStyling);

		$this->activeSheet->getStyle($this->colRate . $row)->applyFromArray($descStyling);
		$this->activeSheet->getStyle($this->colTotal . $row)->applyFromArray($descStyling);
		$this->activeSheet->getStyle($this->colWastage . $row)->applyFromArray($descStyling);

		$rate = $this->ratePerUnit;

		if ( isset( $this->buildUpQuantitySummaryInfo['apply_conversion_factor'] ) AND $this->buildUpQuantitySummaryInfo['apply_conversion_factor'] )
		{
			$rate = $this->buildUpQuantitySummaryInfo['final_cost'];
		}

		$this->activeSheet->setCellValue($this->colLineTotal . $row, $rate);
		$this->activeSheet->getStyle($this->colLineTotal . $row)->applyFromArray($rateStyling);
		$this->activeSheet->getStyle($this->colLineTotal . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
	}

	public function setBuildUpItem($item, $description, $char, $itemType)
	{
		$row         = $this->currentRow;
		$factorValue = null;
		$totalValue  = null;

		$this->activeSheet->setCellValue($this->colItem . $row, $char);
		$this->activeSheet->getStyle($this->colItem . $row)->applyFromArray($this->getNoStyle());

		$this->activeSheet->setCellValue($this->colDescription . $row, $description);
		$this->activeSheet->getStyle($this->colDescription . $row)->applyFromArray($this->getDescriptionStyling($itemType));

		$this->activeSheet->setCellValue($this->colNumber . $row, $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_NUMBER]);
		$this->activeSheet->getStyle($this->colNumber . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colNumber . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colConstant . $row, $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_CONSTANT]['final_value']);
		$this->activeSheet->getStyle($this->colConstant . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colConstant . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colQty . $row, $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_QTY]['final_value']);
		$this->activeSheet->getStyle($this->colQty . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colQty . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colUnit . $row, $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_UNIT]);
		$this->activeSheet->getStyle($this->colUnit . $row)->applyFromArray($this->getNoStyle());

		$this->activeSheet->setCellValue($this->colRate . $row, $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_RATE]['final_value']);
		$this->activeSheet->getStyle($this->colRate . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colRate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colTotal . $row, $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_TOTAL]);
		$this->activeSheet->getStyle($this->colTotal . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colTotal . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colWastage . $row, $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_WASTAGE]['final_value']);
		$this->activeSheet->getStyle($this->colWastage . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colWastage . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colLineTotal . $row, $item[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_LINE_TOTAL]);
		$this->activeSheet->getStyle($this->colLineTotal . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colLineTotal . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
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

		if ( isset( $this->buildUpQuantitySummaryInfo['apply_conversion_factor'] ) AND $this->buildUpQuantitySummaryInfo['apply_conversion_factor'] )
		{
			$this->generateConversionFactorRow($previousCol, $totalStyle, $newLineStyle);
		}

		$this->generateMarkUpPercentRow($previousCol, $totalStyle, $newLineStyle);
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
		$this->lastCol  = $this->colLineTotal;
	}

	public function generateMarkUpPercentRow($previousCol, $totalStyle, $newLineStyle)
	{
		$value = null;

		if ( $this->buildUpQuantitySummaryInfo['markup'] != 0 )
		{
			$value = $this->buildUpQuantitySummaryInfo['markup'];
		}

		$this->activeSheet->getStyle($previousCol . $this->currentRow)->applyFromArray($totalStyle);
		$this->activeSheet->setCellValue($previousCol . $this->currentRow, 'Mark Up %');
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colLineTotal . $this->currentRow, $value);
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

	public function generateConversionFactorRow($previousCol, $totalStyle, $newLineStyle)
	{
		$value = null;

		if ( $this->buildUpQuantitySummaryInfo['conversion_factor_amount'] != 0 )
		{
			$value = $this->buildUpQuantitySummaryInfo['conversion_factor_amount'];
		}

		$this->activeSheet->getStyle($previousCol . $this->currentRow)->applyFromArray($totalStyle);
		$this->activeSheet->setCellValue($previousCol . $this->currentRow, 'Conversion Factor (' . $this->buildUpQuantitySummaryInfo['conversion_factor_operator'] . ')');
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colLineTotal . $this->currentRow, $value);
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

	public function generateFinalTotalCostRow($previousCol, $totalStyle, $newLineStyle)
	{
		$value = null;

		if ( $this->buildUpQuantitySummaryInfo['total_cost'] != 0 )
		{
			$value = $this->buildUpQuantitySummaryInfo['total_cost'];
		}

		$this->activeSheet->getStyle($previousCol . $this->currentRow)->applyFromArray($totalStyle);
		$this->activeSheet->setCellValue($previousCol . $this->currentRow, 'Total Cost');
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colLineTotal . $this->currentRow, $value);
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colLineTotal . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

	public function setColumnDimensions($columnDimensions)
	{
		$this->columnDimensions = $columnDimensions;
	}

	public function setBuildUpQuantitySummaryInfo($buildUpQuantitySummaryInfo)
	{
		$this->buildUpQuantitySummaryInfo = $buildUpQuantitySummaryInfo;
	}

	public function setRate($rate)
	{
		$this->ratePerUnit = $rate;
	}

	public function setBillItemInfo($billItemInfo)
	{
		$this->billItemInfo = $billItemInfo;
	}

	public function setBillItemUOM($billItemUOM)
	{
		$this->billItemUOM = $billItemUOM;
	}

	public function getDescriptionStyling($itemType = null)
	{
		$color     = '000000';
		$bold      = ( $itemType == ResourceItem::TYPE_HEADER ) ? true : false;
		$underline = ( $itemType == ResourceItem::TYPE_HEADER ) ? true : false;

		return array(
			'font'      => array(
				'color'     => array( 'rgb' => $color ),
				'bold'      => $bold,
				'underline' => $underline,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'wrapText'   => true
			),
		);
	}

}