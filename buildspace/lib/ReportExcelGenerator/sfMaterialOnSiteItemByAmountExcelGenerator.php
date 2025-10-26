<?php

class sfMaterialOnSiteItemByAmountExcelGenerator extends sfBuildspaceExcelReportGenerator {

	private $mos = array();

	private $mosPrintSettings = array();

	private $isLastPage = false;

	private $mosTotal = 0;

	private $mosTotalAfterReduction = 0;

	private $pageNo = 1;

	public $currentRow = 1;

	public $colItem = 'B';
	public $colDescription = 'C';
	public $colUnit = 'D';
	public $colQty = 'E';
	public $colRate = 'F';
	public $colAmount = 'G';

	public function __construct(ProjectStructure $project, $printingPageTitle, $mosPrintSettings)
	{
		$filename = ( $printingPageTitle ) ? $printingPageTitle : $project->title . '-' . date('dmY H_i_s');
		$savePath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

		parent::__construct($project, $savePath, $filename, $mosPrintSettings);
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

			$lastItemKey        = count($itemPage);
			$lastItemKeyCounter = 1;

			foreach ( $itemPage as $item )
			{
				$lastItemKeyCounter ++;

				$itemType = $item[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_TYPE];

				switch ($itemType)
				{
					case ResourceItem::TYPE_HEADER:
						$description = $item[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";

						$this->newItem();

						$this->setResourceItem($item, $description, $char, $itemType);

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
						$description .= $item[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
						$char .= $item[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_ROW_IDX];

						if ( $item[0] )
						{
							$this->newItem();

							$this->setResourceItem($item, $description, $char, $itemType);

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

			$this->createFooter($this->isLastPage);

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
		$this->activeSheet->setCellValue($this->colItem . $row, 'No');
		$this->activeSheet->setCellValue($this->colDescription . $row, 'Description');
		$this->activeSheet->setCellValue($this->colUnit . $row, 'Unit');
		$this->activeSheet->setCellValue($this->colQty . $row, 'Qty');
		$this->activeSheet->setCellValue($this->colRate . $row, 'Rate');
		$this->activeSheet->setCellValue($this->colAmount . $row, 'Amount');

		// Set Column Width
		$this->activeSheet->getColumnDimension("A")->setWidth(1.3);
		$this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);

		$this->activeSheet->getColumnDimension($this->colUnit)->setWidth(8);
		$this->activeSheet->getColumnDimension($this->colQty)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colRate)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colAmount)->setWidth(12);

		$this->activeSheet->getStyle("{$this->firstCol}{$row}:{$this->lastCol}{$row}")->applyFromArray($this->getColumnHeaderStyle());
	}

	public function setResourceItem($item, $description, $char, $itemType)
	{
		$row = $this->currentRow;

		$this->activeSheet->setCellValue($this->colItem . $row, $char);
		$this->activeSheet->getStyle($this->colItem . $row)->applyFromArray($this->getNoStyle());

		$this->activeSheet->setCellValue($this->colDescription . $row, $description);
		$this->activeSheet->getStyle($this->colDescription . $row)->applyFromArray($this->getDescriptionStyling($itemType));

		$this->activeSheet->setCellValue($this->colUnit . $row, $item[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_UNIT]);
		$this->activeSheet->getStyle($this->colUnit . $row)->applyFromArray($this->getUnitStyle($item));

		$this->activeSheet->setCellValue($this->colQty . $row, $item[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_BALANCE_QTY]);
		$this->activeSheet->getStyle($this->colQty . $row)->applyFromArray($this->getRateStyling($item));
		$this->activeSheet->getStyle($this->colQty . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colRate . $row, $item[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_RATE]);
		$this->activeSheet->getStyle($this->colRate . $row)->applyFromArray($this->getRateStyling($item));
		$this->activeSheet->getStyle($this->colRate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colAmount . $row, $item[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_AMOUNT]);
		$this->activeSheet->getStyle($this->colAmount . $row)->applyFromArray($this->getRateStyling($item));
		$this->activeSheet->getStyle($this->colAmount . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
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

		$previousCol = PHPExcel_Cell::columnIndexFromString($this->colAmount);
		$previousCol = PHPExcel_Cell::stringFromColumnIndex($previousCol - 2);

		if ( $this->mos['reduction_percentage'] > 0 or $this->mos['reduction_percentage'] < 0 )
		{
			$this->generateTotalRow($previousCol, $totalStyle, $newLineStyle);
			$this->generateTotalAfterReductionRow($previousCol, $totalStyle, $newLineStyle);
		}

		$this->generateFinalQuantityRow($previousCol, $totalStyle, $newLineStyle);
	}

	public function setBillHeader($billHeader = null, $topLeftTitle = null, $subTitle = null)
	{
		$billHeader = ( $billHeader ) ? $billHeader : $this->filename;
		$billHeader = Utilities::truncateString($billHeader, 280);

		//Set Top Header
		$this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $billHeader);
		$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);
		$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getProjectTitleStyle());

		$this->currentRow ++;

		// will set additional header information from MOS Print Setting
		if ( !empty( $this->mosPrintSettings['site_belonging_address'] ) or !empty( $this->mosPrintSettings['original_finished_date'] ) )
		{
			$this->currentRow ++;

			$this->activeSheet->setCellValue($this->firstCol . $this->currentRow, Utilities::truncateString($this->mosPrintSettings['site_belonging_address'], 100));
			$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->colUnit . $this->currentRow);
			$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->colUnit . $this->currentRow)->applyFromArray($this->getHeaderStyle());

			$this->activeSheet->setCellValue($this->colQty . $this->currentRow, Utilities::truncateString($this->mosPrintSettings['original_finished_date'], 100));
			$this->activeSheet->mergeCells($this->colQty . $this->currentRow . ':' . $this->colAmount . $this->currentRow);
			$this->activeSheet->getStyle($this->colQty . $this->currentRow . ':' . $this->colAmount . $this->currentRow)->applyFromArray($this->getHeaderStyle());
		}

		if ( !empty( $this->mosPrintSettings['contract_duration'] ) or !empty( $this->mosPrintSettings['contract_original_amount'] ) )
		{
			$this->currentRow ++;

			$this->activeSheet->setCellValue($this->firstCol . $this->currentRow, Utilities::truncateString($this->mosPrintSettings['contract_duration'], 100));
			$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->colUnit . $this->currentRow);
			$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->colUnit . $this->currentRow)->applyFromArray($this->getHeaderStyle());

			$this->activeSheet->setCellValue($this->colQty . $this->currentRow, Utilities::truncateString($this->mosPrintSettings['contract_original_amount'], 100));
			$this->activeSheet->mergeCells($this->colQty . $this->currentRow . ':' . $this->colAmount . $this->currentRow);
			$this->activeSheet->getStyle($this->colQty . $this->currentRow . ':' . $this->colAmount . $this->currentRow)->applyFromArray($this->getHeaderStyle());
		}

		if ( !empty( $this->mosPrintSettings['payment_revision_no'] ) or !empty( $this->mosPrintSettings['evaluation_date'] ) )
		{
			$this->currentRow ++;

			$this->activeSheet->setCellValue($this->firstCol . $this->currentRow, Utilities::truncateString($this->mosPrintSettings['payment_revision_no'], 100));
			$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->colUnit . $this->currentRow);
			$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->colUnit . $this->currentRow)->applyFromArray($this->getHeaderStyle());

			$this->activeSheet->setCellValue($this->colQty . $this->currentRow, Utilities::truncateString($this->mosPrintSettings['evaluation_date'], 100));
			$this->activeSheet->mergeCells($this->colQty . $this->currentRow . ':' . $this->colAmount . $this->currentRow);
			$this->activeSheet->getStyle($this->colQty . $this->currentRow . ':' . $this->colAmount . $this->currentRow)->applyFromArray($this->getHeaderStyle());
		}

		// add empty row to separate table
		$this->currentRow ++;

		$this->currentRow ++;
	}

	public function startBillCounter()
	{
		$this->firstCol = $this->colItem;
		$this->lastCol  = $this->colAmount;
	}

	public function generateTotalRow($previousCol, $totalStyle, $newLineStyle)
	{
		$value = $this->mosTotal;

		$this->activeSheet->getStyle($previousCol . $this->currentRow)->applyFromArray($totalStyle);
		$this->activeSheet->setCellValue($previousCol . $this->currentRow, $this->mosPrintSettings['total_text']);

		$this->activeSheet->getStyle($this->colAmount . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colAmount . $this->currentRow, $value);

		$this->activeSheet->getStyle($this->colAmount . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colAmount . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

	public function generateTotalAfterReductionRow($previousCol, $totalStyle, $newLineStyle)
	{
		$formattedPercentage          = number_format($this->mos['reduction_percentage'], 2);
		$percentage                   = $this->mos['reduction_percentage'] / 100;
		$this->mosTotalAfterReduction = $this->mosTotal * $percentage;

		$value = "-{$this->mosTotalAfterReduction}";

		$this->activeSheet->getStyle($previousCol . $this->currentRow)->applyFromArray($totalStyle);
		$this->activeSheet->setCellValue($previousCol . $this->currentRow, "{$formattedPercentage} {$this->mosPrintSettings['percentage_of_material_on_site_text']}");

		$this->activeSheet->getStyle($this->colAmount . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colAmount . $this->currentRow, $value);

		$this->activeSheet->getStyle($this->colAmount . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colAmount . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

	public function generateFinalQuantityRow($previousCol, $totalStyle, $newLineStyle)
	{
		$value = $this->mosTotal - $this->mosTotalAfterReduction;

		$this->activeSheet->getStyle($previousCol . $this->currentRow)->applyFromArray($totalStyle);
		$this->activeSheet->setCellValue($previousCol . $this->currentRow, $this->mosPrintSettings['carried_to_final_summary_text']);

		$this->activeSheet->getStyle($this->colAmount . $this->currentRow)->applyFromArray($newLineStyle);
		$this->activeSheet->setCellValue($this->colAmount . $this->currentRow, $value);

		$this->activeSheet->getStyle($this->colAmount . $this->currentRow)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colAmount . $this->currentRow)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		$this->currentRow ++;
	}

	public function getDescriptionStyling($itemType)
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

	public function getHeaderStyle()
	{
		return array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
		);
	}

	public function getProjectTitleStyle()
	{
		return array(
			'font'      => array(
				'bold' => true
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
		);
	}

	public function setMOS($mos)
	{
		$this->mos = $mos;
	}

	public function isLastPage($isLastPage)
	{
		$this->isLastPage = $isLastPage;
	}

	public function setMOSPrintSettings($mosPrintSettings)
	{
		$this->mosPrintSettings = $mosPrintSettings;
	}

	public function setMOSTotal($mosTotal)
	{
		$this->mosTotal = $mosTotal;
	}

}