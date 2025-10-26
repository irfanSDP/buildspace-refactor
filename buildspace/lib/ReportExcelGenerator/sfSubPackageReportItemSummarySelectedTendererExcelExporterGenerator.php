<?php

class sfSubPackageReportItemSummarySelectedTendererExcelExporterGenerator extends sfBuildspaceExcelReportGenerator {

	private $totalPage = 0;
	private $pageNo = 1;
	private $estimateTotal = 0;
	private $subConAmountTotal = 0;
	private $differenceAmtTotal = 0;
	private $lastPage = false;
	private $selectedSubCon = null;
	private $currentBill = null;

	public $colItem = 'B';
	public $colDescription = 'C';
	public $colUnit = 'D';
	public $colEstimate = 'E';
	public $colSubConAmount = 'F';
	public $colDifferencePercent = 'G';
	public $colDifferenceAmt = 'H';

	public $currentRow = 1;

	public $lastCol;

	public function __construct(ProjectStructure $project, $printingPageTitle, $printSettings)
	{
		$filename = ( $printingPageTitle ) ? $printingPageTitle : $project->title . '-' . date('dmY H_i_s');
		$savePath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

		parent::__construct($project, $savePath, $filename, $printSettings);
	}

	public function process($pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
	{
		if ( !( $pages instanceof SplFixedArray ) )
		{
			return;
		}

		$this->setExcelParameter($lock, $withoutCents);

		$this->createSheet($header, $subTitle, $topLeftTitle);

		$description   = '';
		$char          = '';
		$prevItemType  = '';
		$prevItemLevel = 0;

		foreach ( $pages as $i => $page )
		{
			if ( !$page )
			{
				continue;
			}

			$this->createNewPage($this->pageNo, false, 0);

			$itemPage           = $page;
			$lastItemKeyCounter = 1;

			foreach ( $itemPage as $item )
			{
				$lastItemKeyCounter ++;

				$itemType = $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_TYPE];

				switch ($itemType)
				{
					case self::ROW_TYPE_BLANK:

						if ( $description != '' && $prevItemType != '' )
						{
							if ( $prevItemType == BillItem::TYPE_HEADER_N || $prevItemType == BillItem::TYPE_HEADER )
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
					case self::ROW_TYPE_ELEMENT:

						if ( strpos($item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION], $this->printSettings['layoutSetting']['contdPrefix']) !== false )
						{
							$this->setElementTitle($this->printSettings['layoutSetting']['contdPrefix']);
						}
						else
						{
							$this->setElement(array( 'description' => $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION] ));
						}

						break;
					case BillItem::TYPE_HEADER_N:
					case BillItem::TYPE_HEADER:

						$description .= $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
						$prevItemType  = $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_TYPE];
						$prevItemLevel = $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_LEVEL];

						break;

					case BillItem::TYPE_ITEM_LUMP_SUM:
					case BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE:

						$description .= $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";

						if ( $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_ID] )
						{
							$this->newItem();

							$this->setBillItem($item, $description, $char);

							$this->setUnit($item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_UNIT]);

							$this->setRate('-');

							$description = '';

							$char = '';
						}

						break;
					default:
						$description .= $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
						$char .= $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_ROW_IDX];

						if ( $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_ID] )
						{
							$this->newItem();

							$this->setBillItem($item, $description, $char);

							$this->processItems($item);

							$description = '';
							$char        = '';
						}

						break;
				}
			}

			$this->createFooter($this->lastPage);

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
		$this->activeSheet->setCellValue($this->colEstimate . $row, 'Estimate');
		$this->activeSheet->setCellValue($this->colSubConAmount . $row, '*' . CompanyTable::formatCompanyName($this->selectedSubCon));
		$this->activeSheet->setCellValue($this->colDifferencePercent . $row, 'Different %');
		$this->activeSheet->setCellValue($this->colDifferenceAmt . $row, 'Different (Amount)');

		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getStyle($this->colDescription)->applyFromArray($this->getDescriptionStyling());

		$this->activeSheet->getColumnDimension($this->colEstimate)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colSubConAmount)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colDifferencePercent)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colDifferenceAmt)->setWidth(12);

		$this->activeSheet->getStyle("{$this->firstCol}{$row}:{$this->lastCol}{$row}")->applyFromArray($this->getColumnHeaderStyle());
	}

	public function setBillItem($item, $description, $char)
	{
		$row         = $this->currentRow;
		$factorValue = null;
		$totalValue  = null;

		$this->activeSheet->setCellValue($this->colItem . $row, $char);
		$this->activeSheet->getStyle($this->colItem . $row)->applyFromArray($this->getNoStyle());

		$this->activeSheet->setCellValue($this->colDescription . $row, $description);

		$this->activeSheet->setCellValue($this->colUnit . $row, $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_UNIT]);
		$this->activeSheet->getStyle($this->colUnit . $row)->applyFromArray($this->getUnitStyle());

		$this->activeSheet->setCellValue($this->colEstimate . $row, $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_RATE]);
		$this->activeSheet->getStyle($this->colEstimate . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colEstimate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colSubConAmount . $row, $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_SUB_CON_RATE]);
		$this->activeSheet->getStyle($this->colSubConAmount . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colSubConAmount . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colDifferencePercent . $row, $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_DIFF_PERCENT]);
		$this->activeSheet->getStyle($this->colDifferencePercent . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colDifferencePercent . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colDifferenceAmt . $row, $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_DIFF_AMT]);
		$this->activeSheet->getStyle($this->colDifferenceAmt . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colDifferenceAmt . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->estimateTotal += $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_RATE];
		$this->subConAmountTotal += $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_SUB_CON_RATE];
		$this->differenceAmtTotal += $item[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_DIFF_AMT];
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

		$text = "Page {$this->currentPage} of {$this->totalPage}";

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
		$this->activeSheet->mergeCells($this->colDescription . $this->currentRow . ':' . $this->colUnit . $this->currentRow);
		$this->activeSheet->setCellValue($this->colDescription . $this->currentRow, ( $title ) ? $title : "{$this->currentBill} ({$this->getCurrency()}):");
	}

	public function printGrandTotalValue($style)
	{
		$row = $this->currentRow;

		$diffAmt        = $this->subConAmountTotal - $this->estimateTotal;
		$diffPercentage = 0;

		if ( $this->estimateTotal != 0 )
		{
			$diffPercentage = Utilities::prelimRounding(Utilities::percent($diffAmt, $this->estimateTotal));
		}

		$this->activeSheet->setCellValue($this->colEstimate . $row, $this->estimateTotal);
		$this->activeSheet->getStyle($this->colEstimate . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colEstimate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colSubConAmount . $row, $this->subConAmountTotal);
		$this->activeSheet->getStyle($this->colSubConAmount . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colSubConAmount . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colDifferencePercent . $row, $diffPercentage);
		$this->activeSheet->getStyle($this->colDifferencePercent . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colDifferencePercent . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colDifferenceAmt . $row, $this->differenceAmtTotal);
		$this->activeSheet->getStyle($this->colDifferenceAmt . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colDifferenceAmt . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->getStyle($this->colEstimate . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);
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
		$this->lastCol  = $this->colDifferenceAmt;
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

	public function setBill($bill)
	{
		$this->currentBill = $bill;
	}

	public function setLastPage($lastPage)
	{
		$this->lastPage = $lastPage;
	}

	public function setCurrency($getCurrency)
	{
		$this->currency = $getCurrency;
	}

	public function setSelectedSubCon($selectedSubCon)
	{
		$this->selectedSubCon = $selectedSubCon;
	}

	public function setTotalPage($totalPage)
	{
		$this->totalPage = $totalPage;
	}

}