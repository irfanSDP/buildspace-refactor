<?php 

class sfPostContractRemeasurementItemBuildUpQtyExcelGenerator extends sfBuildspaceExcelReportGenerator {

	protected $dimensions = array();
	protected $billItemInfo = array();
	protected $buildUpQuantitySummaryInfo = array();
	protected $quantityPerUnit = null;
	protected $billItemUOM = null;

	private $pageNo = 1;

	public $colItem = "B";
	public $colDescription = "C";
	public $colFactor = "D";
	public $colTotal = null;
	public $colSign = null;

	public function __construct(ProjectStructure $project, $printingPageTitle, $printSettings)
	{
		$filename         = ( $printingPageTitle ) ? $printingPageTitle : $project->title . '-' . date('dmY H_i_s');
		$savePath         = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';
		$this->currentRow = 1;

		parent::__construct($project, $savePath, $filename, $printSettings);
	}

	public function process($pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
	{
		$this->setExcelParameter($lock, $withoutCents);

		$description = '';
		$char        = '';

		$this->createSheet($header, $subTitle, $topLeftTitle);

		foreach ( $pages as $i => $page )
		{
			if ( !$page )
			{
				continue;
			}

			$this->createNewPage($this->pageNo, false, 0);

			$itemPage = $page;

			// will create bill item header first
			$this->createBillItemHeader();

			$lastItemKey        = count($itemPage);
			$lastItemKeyCounter = 1;

			foreach ( $itemPage as $item )
			{
				$lastItemKeyCounter ++;

				$itemType = $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_TYPE];

				switch ($itemType)
				{
					case sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_SOQ_HEADER_TYPE:
						$description = $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";

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
						$description .= $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
						$char .= $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_ROW_IDX];

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

			$this->createFooter(false);

			$this->pageNo ++;
		}
	}

	public function createBillItemHeader()
	{
		$description = '';
		$char        = '';

		foreach ( $this->billItemInfo as $item )
		{
			$itemType = $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_TYPE];

			switch ($itemType)
			{
				case self::ROW_TYPE_BLANK:
					$this->newLine();
					break;

				default:
					$description .= $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
					$char .= $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_ROW_IDX];

					if ( $item[0] )
					{
						$this->setBillItem($item, strip_tags($description), $char, $itemType);

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
		$row               = $this->currentRow;
		$lastDynamicColumn = $this->colFactor;

		//set default column
		$this->activeSheet->setCellValue($this->colItem . $row, 'No');
		$this->activeSheet->setCellValue($this->colDescription . $row, 'Description');
		$this->activeSheet->setCellValue($this->colFactor . $row, 'Factor');

		foreach ( $this->dimensions as $columnDimension )
		{
			$lastDynamicColumn ++;

			$this->activeSheet->setCellValue($lastDynamicColumn . $row, $columnDimension['name']);
			$this->activeSheet->getColumnDimension($lastDynamicColumn)->setWidth(12);
		}

		$this->activeSheet->setCellValue($this->colTotal . $row, 'Total');
		$this->activeSheet->setCellValue($this->colSign . $row, 'Sign');

		// Set Column Width
		$this->activeSheet->getColumnDimension("A")->setWidth(1.3);
		$this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);

		$this->activeSheet->getColumnDimension($this->colFactor)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colTotal)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colSign)->setWidth(12);

		$this->activeSheet->getStyle("{$this->firstCol}{$row}:{$this->lastCol}{$row}")->applyFromArray($this->getColumnHeaderStyle());
	}

	public function setBillItem($item, $description, $char, $itemType)
	{
		$row               = $this->currentRow;
		$lastDynamicColumn = $this->colFactor;
		$refStyling        = $this->getNoStyle();
		$descStyling       = $this->getDescriptionStyling($item, $itemType);
		$rateStyling       = $this->getRateStyling($item);

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

		$this->activeSheet->setCellValue($this->colFactor . $row, $this->billItemUOM);
		$this->activeSheet->getStyle($this->colFactor . $row)->applyFromArray($refStyling);

		foreach ( $this->dimensions as $dimension )
		{
			$lastDynamicColumn ++;

			$value = null;

			if ( isset( $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_FORMULATED_COLUMNS][$dimension['id'] . '-dimension_column'] ) )
			{
				$value = $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_FORMULATED_COLUMNS][$dimension['id'] . '-dimension_column'];
			}

			$this->activeSheet->setCellValue($lastDynamicColumn . $row, $value);
			$this->activeSheet->getStyle($lastDynamicColumn . $row)->applyFromArray($rateStyling);
			$this->activeSheet->getStyle($lastDynamicColumn . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		}

		$quantity = $this->quantityPerUnit;

		if ( isset( $this->buildUpQuantitySummaryInfo['apply_conversion_factor'] ) AND $this->buildUpQuantitySummaryInfo['apply_conversion_factor'] )
		{
			$quantity = $this->buildUpQuantitySummaryInfo['final_quantity'];
		}

		$this->activeSheet->setCellValue($this->colTotal . $row, $quantity);
		$this->activeSheet->getStyle($this->colTotal . $row)->applyFromArray($rateStyling);
		$this->activeSheet->getStyle($this->colTotal . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colSign . $row, null);
		$this->activeSheet->getStyle($this->colSign . $row)->applyFromArray(array(
			'borders' => array(
				'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				),
			)
		));
	}

	public function setBuildUpItem($item, $description, $char, $itemType)
	{
		$row               = $this->currentRow;
		$lastDynamicColumn = $this->colFactor;
		$factorValue       = null;
		$totalValue        = null;

		if ( isset( $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_FACTOR]['final_value'] ) AND $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_FACTOR]['final_value'] != 0 )
		{
			$factorValue = $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_FACTOR]['final_value'];
		}

		if ( isset( $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_TOTAL] ) AND $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_TOTAL] != 0 )
		{
			$totalValue = $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_TOTAL];
		}

		$this->activeSheet->setCellValue($this->colItem . $row, $char);
		$this->activeSheet->getStyle($this->colItem . $row)->applyFromArray($this->getNoStyle());

		$this->activeSheet->setCellValue($this->colDescription . $row, $description);
		$this->activeSheet->getStyle($this->colDescription . $row)->applyFromArray($this->getDescriptionStyling($item, $itemType));

		if ( $itemType == sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_SOQ_MEASUREMENT_TYPE )
		{
			$this->activeSheet->getStyle($this->colDescription . $row)->getAlignment()->setIndent(1);
		}

		$this->activeSheet->setCellValue($this->colFactor . $row, $factorValue);
		$this->activeSheet->getStyle($this->colFactor . $row)->applyFromArray($this->getRateStyling($item));
		$this->activeSheet->getStyle($this->colFactor . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		foreach ( $this->dimensions as $columnDimension )
		{
			$lastDynamicColumn ++;

			$value = null;

			if ( isset( $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_FORMULATED_COLUMNS][$columnDimension['id'] . '-dimension_column']['final_value'] ) AND $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_FORMULATED_COLUMNS][$columnDimension['id'] . '-dimension_column']['final_value'] != 0 )
			{
				$value = $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_FORMULATED_COLUMNS][$columnDimension['id'] . '-dimension_column']['final_value'];
			}

			$this->activeSheet->setCellValue($lastDynamicColumn . $row, $value);
			$this->activeSheet->getStyle($lastDynamicColumn . $row)->applyFromArray($this->getRateStyling($item));
			$this->activeSheet->getStyle($lastDynamicColumn . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		}

		$this->activeSheet->setCellValue($this->colTotal . $row, $totalValue);
		$this->activeSheet->getStyle($this->colTotal . $row)->applyFromArray($this->getRateStyling($item));
		$this->activeSheet->getStyle($this->colTotal . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colSign . $row, $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_SIGN]);
		$this->activeSheet->getStyle($this->colSign . $row)->applyFromArray($this->getNoStyle());
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
		if ( isset( $this->buildUpQuantitySummaryInfo['apply_conversion_factor'] ) AND $this->buildUpQuantitySummaryInfo['apply_conversion_factor'] )
		{
			$previousCol = PHPExcel_Cell::columnIndexFromString($this->colTotal);
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

			$this->generateConversionFactorRow($previousCol, $totalStyle, $newLineStyle);
			$this->generateFinalQuantityRow($previousCol, $totalStyle, $newLineStyle);
		}
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
		$currentColumn  = $this->colFactor;

		foreach ( $this->dimensions as $columnDimension )
		{
			$currentColumn ++;
		}

		// add one column for total
		$currentColumn ++;

		$this->colTotal = $currentColumn;

		// add one column for sign
		$currentColumn ++;

		$this->colSign = $currentColumn;
		$this->lastCol = $this->colSign;
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
		$this->activeSheet->getStyle($this->colTotal . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colTotal . $this->currentRow, $value);
		$this->activeSheet->getStyle($this->colTotal . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colTotal . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

	public function generateFinalQuantityRow($previousCol, $totalStyle, $newLineStyle)
	{
		$value = null;

		if ( $this->buildUpQuantitySummaryInfo['final_quantity'] != 0 )
		{
			$value = $this->buildUpQuantitySummaryInfo['final_quantity'];
		}

		$this->activeSheet->getStyle($previousCol . $this->currentRow)->applyFromArray($totalStyle);
		$this->activeSheet->setCellValue($previousCol . $this->currentRow, 'Final Quantity');
		$this->activeSheet->getStyle($this->colTotal . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colTotal . $this->currentRow, $value);
		$this->activeSheet->getStyle($this->colTotal . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colTotal . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

	public function setDimensions($dimensions)
	{
		$this->dimensions = $dimensions;
	}

	public function setBuildUpQuantitySummaryInfo($buildUpQuantitySummaryInfo)
	{
		$this->buildUpQuantitySummaryInfo = $buildUpQuantitySummaryInfo;
	}

	public function setQuantityPerUnit($quantityPerUnit)
	{
		$this->quantityPerUnit = $quantityPerUnit;
	}

	public function setBillItemInfo($billItemInfo)
	{
		$this->billItemInfo = $billItemInfo;
	}

	public function setBillItemUOM($billItemUOM)
	{
		$this->billItemUOM = $billItemUOM;
	}

	public function getDescriptionStyling($item = null, $itemType)
	{
		$color = ( isset( $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_SIGN] ) AND $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_SIGN] == '-' ) ? 'FF0000' : '000000';

		$bold = ( $itemType == sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_SOQ_ITEM_TYPE OR $itemType == sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_SOQ_HEADER_TYPE ) ? true : false;

		$underline = ( $itemType == sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_SOQ_HEADER_TYPE ) ? PHPExcel_Style_Font::UNDERLINE_SINGLE : false;

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

	public function getRateStyling($item = null)
	{
		$color = ( isset( $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_SIGN] ) AND $item[sfPostContractRemeasurementItemBuildUpQtyReportGenerator::ROW_BILL_ITEM_SIGN] == '-' ) ? 'FF0000' : '000000';

		return array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font'      => array(
				'color' => array( 'rgb' => $color ),
			),
		);
	}

}