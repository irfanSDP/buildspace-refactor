<?php

class sfFloorAreaExportExcelGenerator extends sfBuildspaceExcelReportGenerator {

	public $printingPageTitle;

	public $buildUpFloorAreaSummaries;

	public $colItem        = "B";
	public $colDescription = "C";
	public $colFactor      = "D";
	public $colLength      = "E";
	public $colWidth       = "F";
	public $colTotal       = "G";
	public $colSign        = 'H';

	public function __construct(ProjectStructure $project, Doctrine_Collection $buildUpFloorAreaSummaries, $printingPageTitle, $printSettings)
	{
		$this->project                   = $project;
		$this->printingPageTitle         = $printingPageTitle;
		$this->printSettings             = $printSettings;
		$this->buildUpFloorAreaSummaries = $buildUpFloorAreaSummaries[0];

		$filename                        = ( $printingPageTitle ) ? $printingPageTitle : $this->bill->title.'-'.date( 'dmY H_i_s' );
		$savePath                        = sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';

		parent::__construct( $project, $savePath, $filename, $printSettings );
	}

	public function startBillCounter()
	{
		$this->currentRow       = $this->startRow;
		$this->firstCol         = $this->colItem;
		$this->lastCol          = $this->colSign;
		$this->currentElementNo = 0;
		$this->columnSetting    = null;
	}

	public function createHeader( $new = false )
	{
		$row = $this->currentRow;

		//set default column
		$this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
		$this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );
		$this->activeSheet->setCellValue( $this->colFactor.$row, 'Factor' );
		$this->activeSheet->setCellValue( $this->colLength.$row, 'Length' );
		$this->activeSheet->setCellValue( $this->colWidth.$row, 'Width' );
		$this->activeSheet->setCellValue( $this->colTotal.$row, 'Total' );
		$this->activeSheet->setCellValue( $this->colSign.$row, '(+/-)' );

		$this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
		$this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
		$this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
		$this->activeSheet->getColumnDimension( $this->colFactor )->setWidth( 6 );
		$this->activeSheet->getColumnDimension( $this->colLength )->setWidth( 6 );
		$this->activeSheet->getColumnDimension( $this->colWidth )->setWidth( 6 );
		$this->activeSheet->getColumnDimension( $this->colTotal )->setWidth( 6 );

		$this->activeSheet->getStyle( "{$this->firstCol}{$this->currentRow}:{$this->lastCol}{$this->currentRow}" )->applyFromArray( $this->getColumnHeaderStyle() );
		$this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
	}

	public function process( $pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
	{
		$this->totalPage = $totalPage;
		$this->setExcelParameter( $lock, $withoutCents );

		$description   = '';
		$char          = '';
		$prevItemType  = '';
		$prevItemLevel = 0;
		$pageNo        = 1;

		$this->createSheet($header, $subTitle, $topLeftTitle);

		if($pages instanceof SplFixedArray)
		{
			foreach($pages as $i => $page)
			{
				if ( ! $page ) continue;

				$lastPage = (($i+1) == $pages->count()) ? true : false;

				$this->createNewPage($pageNo, false, 0);

				$itemPage = $page;

				foreach($itemPage as $item)
				{
					$itemType = $item[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_TYPE];

					switch($itemType)
					{
						case self::ROW_TYPE_BLANK:
						break;

						default:
							$description .= $item[2]."\n";
							$char        .= $item[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_ROW_IDX];

							if($item[0])
							{
								$this->newItem();

								$this->setBuildUpItem( $item, $description, $char, $itemType );

								$this->processItems($item);

								$description = '';
								$char        = '';
							}

						break;
					}
				}

				$this->createFooter($lastPage);

				$pageNo++;
			}
		}

		//write to Excel File
		$this->fileInfo = $this->writeExcel();
	}

	public function setBuildUpItem( $item, $description, $char = NULL, $itemType )
	{
		$coord          = $this->colDescription.$this->currentRow;
		$this->itemType = $itemType;

		$this->activeSheet->setCellValue( $coord, $description );
		$this->activeSheet->getStyle( $coord )->applyFromArray( $this->getDescriptionStyling($item) );

		if ( $char )
		{
			$this->setValue( $this->colItem, $char );
			$this->activeSheet->getStyle( $this->colItem.$this->currentRow )->applyFromArray( $this->getRefNoStyling() );
			$this->activeSheet->getStyle( $this->colItem.$this->currentRow )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
		}

		$this->setItemStyle();
	}

	public function processItems($item)
	{
		$this->setValue( $this->colFactor, $item[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_FACTOR] );
		$this->activeSheet->getStyle( $this->colFactor.$this->currentRow )->applyFromArray( $this->getNumberStyling($item) );

		$this->setValue( $this->colLength, $item[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_LENGTH] );
		$this->activeSheet->getStyle( $this->colLength.$this->currentRow )->applyFromArray( $this->getNumberStyling($item) );

		$this->setValue( $this->colWidth, $item[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_WIDTH] );
		$this->activeSheet->getStyle( $this->colWidth.$this->currentRow )->applyFromArray( $this->getNumberStyling($item) );

		$this->setValue( $this->colTotal, $item[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_TOTAL] );
		$this->activeSheet->getStyle( $this->colTotal.$this->currentRow )->applyFromArray( $this->getNumberStyling($item) );

		$this->setValue( $this->colSign, $item[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_SIGN] );
		$this->activeSheet->getStyle( $this->colSign.$this->currentRow )->applyFromArray( $this->getSignStyling($item) );
	}

	public function printGrandTotal()
	{
		$totalStyle = array(
			'font' => array(
				'bold' => true
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				'wrapText' => true
			)
		);

		$format = $this->getNumberFormatStandard();

		$this->createTotalFloorArea($totalStyle, $format);
		$this->createConversionFactor($totalStyle, $format);
		$this->createConversionFactorAmount($totalStyle, $format);
		$this->createFinalFloorArea($totalStyle, $format);
	}

	public function createFooterPageNo()
	{
		$this->currentRow++;

		$coord = $this->colItem.$this->currentRow;
		$this->activeSheet->mergeCells( $this->firstCol.$this->currentRow.':'.$this->lastCol.$this->currentRow );

		$this->currentRow++;

		$text = 'Page '.$this->currentPage.' Of '.$this->totalPage;

		$pageNoStyle = array(
			'font' => array(
				'bold' => true
			)
		);

		$this->activeSheet->setCellValue( $coord, $text );
		$this->activeSheet->getStyle( $coord )->applyFromArray( $pageNoStyle );
	}

	public function createTotalFloorArea($totalStyle, $format)
	{
		$this->activeSheet->getStyle( $this->colDescription.$this->currentRow )->applyFromArray( $totalStyle );
		$this->activeSheet->setCellValue( $this->colDescription.$this->currentRow, "Total Floor Area:" );
		$this->activeSheet->setCellValue( $this->colTotal.$this->currentRow, $this->buildUpFloorAreaSummaries->total_floor_area );
		$this->activeSheet->getStyle( $this->colTotal.$this->currentRow )->getNumberFormat()->applyFromArray( $format );
		$this->currentRow++;
	}

	public function createConversionFactor($totalStyle, $format)
	{
		$this->activeSheet->getStyle( $this->colDescription.$this->currentRow )->applyFromArray( $totalStyle );
		$this->activeSheet->setCellValue( $this->colDescription.$this->currentRow, "Conversion Factor:" );
		$this->activeSheet->setCellValue( $this->colTotal.$this->currentRow, $this->buildUpFloorAreaSummaries->conversion_factor_operator );
		$this->currentRow++;
	}

	public function createConversionFactorAmount($totalStyle, $format)
	{
		$this->activeSheet->getStyle( $this->colDescription.$this->currentRow )->applyFromArray( $totalStyle );
		$this->activeSheet->setCellValue( $this->colDescription.$this->currentRow, "Conversion Factor Amount:" );
		$this->activeSheet->setCellValue( $this->colTotal.$this->currentRow, $this->buildUpFloorAreaSummaries->conversion_factor_amount );
		$this->activeSheet->getStyle( $this->colTotal.$this->currentRow )->getNumberFormat()->applyFromArray( $format );
		$this->currentRow++;
	}

	public function createFinalFloorArea($totalStyle, $format)
	{
		$this->activeSheet->getStyle( $this->colDescription.$this->currentRow )->applyFromArray( $totalStyle );
		$this->activeSheet->setCellValue( $this->colDescription.$this->currentRow, "Final Floor Area:" );
		$this->activeSheet->setCellValue( $this->colTotal.$this->currentRow, $this->buildUpFloorAreaSummaries->final_floor_area );
		$this->activeSheet->getStyle( $this->colTotal.$this->currentRow )->getNumberFormat()->applyFromArray( $format );
		$this->currentRow++;
	}

	public function getRefNoStyling()
	{
		return array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			),
		);
	}

	public function getDescriptionStyling($item)
	{
		$color = ( $item[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_SIGN] == '-' ) ? 'FF0000' : '000000';

		return array(
			'font' => array(
				'color' => array('rgb' => $color),
			),
		);
	}

	public function getNumberStyling($item)
	{
		$color = ( $item[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_SIGN] == '-' ) ? 'FF0000' : '000000';

		return array(
			'font' => array(
				'color' => array('rgb' => $color),
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			),
		);
	}

	public function getSignStyling($item)
	{
		$color = ( $item[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_SIGN] == '-' ) ? 'FF0000' : '000000';

		return array(
			'font' => array(
				'color' => array('rgb' => $color),
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			),
		);
	}

}