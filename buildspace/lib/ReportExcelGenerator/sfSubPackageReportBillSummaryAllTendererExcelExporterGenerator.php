<?php

class sfSubPackageReportBillSummaryAllTendererExcelExporterGenerator extends sfBuildspaceExcelReportGenerator {

	protected $subConsBillTotals = array();
	protected $subCons = array();
	protected $subConTotalAmount = array();

	private $pageNo = 1;
	private $estimateTotal = 0;
	private $lastPage = false;

	public $colItem = "B";
	public $colDescription = "C";
	public $colEstimate = "D";

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

			$itemPage           = $page;
			$lastItemKey        = count($itemPage);
			$lastItemKeyCounter = 1;

			foreach ( $itemPage as $item )
			{
				$lastItemKeyCounter ++;

				$itemType = $item[sfSubPackageBillSummaryAllTendererPageGenerator::ROW_BILL_ITEM_TYPE];

				switch ($itemType)
				{
					case self::ROW_TYPE_BLANK:
						break;

					default:
						$description .= $item[sfSubPackageBillSummaryAllTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
						$char .= $item[sfSubPackageBillSummaryAllTendererPageGenerator::ROW_BILL_ITEM_ROW_IDX];

						if ( $item[0] )
						{
							$this->newItem();

							$this->setBill($item, $description, $char, $itemType);

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
		$row               = $this->currentRow;
		$lastDynamicColumn = $this->colEstimate;

		//set default column
		$this->activeSheet->setCellValue($this->colItem . $row, 'No');
		$this->activeSheet->setCellValue($this->colDescription . $row, 'Description');
		$this->activeSheet->setCellValue($this->colEstimate . $row, 'Estimate');

		foreach ( $this->subCons as $subCon )
		{
			$lastDynamicColumn ++;

			$subConName = CompanyTable::formatCompanyName($subCon);

			if ( isset( $subCon['selected'] ) AND $subCon['selected'] )
			{
				// set the selected tenderer a blue marker
				$objRichText = new PHPExcel_RichText();
				$objBold     = $objRichText->createTextRun('*' . CompanyTable::formatCompanyName($subCon));
				$objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

				$subConName = $objRichText;
			}

			$this->activeSheet->setCellValue($lastDynamicColumn . $row, $subConName);
			$this->activeSheet->getColumnDimension($lastDynamicColumn)->setWidth(12);
		}

		$this->lastCol = $lastDynamicColumn;

		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getStyle($this->colDescription)->applyFromArray($this->getDescriptionStyling());

		$this->activeSheet->getColumnDimension($this->colEstimate)->setWidth(12);

		$this->activeSheet->getStyle("{$this->firstCol}{$row}:{$this->lastCol}{$row}")->applyFromArray($this->getColumnHeaderStyle());
	}

	public function setBill($item, $description, $char)
	{
		$listOfRates       = array();
		$row               = $this->currentRow;
		$lastDynamicColumn = $this->colEstimate;
		$billId            = $item[sfSubPackageBillSummaryAllTendererPageGenerator::ROW_BILL_ITEM_ID];

		$this->activeSheet->setCellValue($this->colItem . $row, $char);
		$this->activeSheet->getStyle($this->colItem . $row)->applyFromArray($this->getNoStyle());

		$this->activeSheet->setCellValue($this->colDescription . $row, $description);

		$this->activeSheet->setCellValue($this->colEstimate . $row, $item[sfSubPackageBillSummaryAllTendererPageGenerator::ROW_BILL_ITEM_ESTIMATE_AMOUNT]);
		$this->activeSheet->getStyle($this->colEstimate . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colEstimate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->estimateTotal += $item[sfSubPackageBillSummaryAllTendererPageGenerator::ROW_BILL_ITEM_ESTIMATE_AMOUNT];

        foreach($this->subCons as $subCon)
        {
            if( array_key_exists($subCon['id'], $this->subConsBillTotals) && array_key_exists($billId, $this->subConsBillTotals[ $subCon['id'] ]) )
            {
                $listOfRates[] = $this->subConsBillTotals[ $subCon['id'] ][ $billId ];
            }
        }

		$lowestRate      = count($listOfRates) ? min($listOfRates) : 0;
		$highestRate     = count($listOfRates) ? max($listOfRates) : 0;

		$lowestRateFromList  = (int)array_search($lowestRate, $listOfRates);
		$highestRateFromList = (int)array_search($highestRate, $listOfRates);

		$lowestSubConId  = array_key_exists($lowestRateFromList, $this->subCons) ? $this->subCons[$lowestRateFromList]['id'] : null;
		$highestSubConId = array_key_exists($highestRateFromList, $this->subCons) ? $this->subCons[$highestRateFromList]['id'] : null;

		foreach ( $this->subCons as $subCon )
		{
			$lastDynamicColumn ++;

            $amount = isset( $this->subConsBillTotals[ $subCon['id'] ][ $billId ] ) ? $this->subConsBillTotals[ $subCon['id'] ][ $billId ] : 0;

			if ( $lowestSubConId == $highestSubConId )
			{
				parent::setValue($lastDynamicColumn, $amount);
			}
			else
			{
				if ( $subCon['id'] == $lowestSubConId )
				{
					parent::setLowestValue($lastDynamicColumn, $amount);
				}
				else if ( $subCon['id'] == $highestSubConId )
				{
					parent::setHighestValue($lastDynamicColumn, $amount);
				}
				else
				{
					parent::setValue($lastDynamicColumn, $amount);
				}
			}

			$this->subConTotalAmount[$subCon['id']] += $amount;
		}
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
		$this->activeSheet->setCellValue($this->colDescription . $this->currentRow, ( $title ) ? $title : "Total ({$this->getCurrency()}):");
	}

	public function printGrandTotalValue($style)
	{
		$row               = $this->currentRow;
		$lastDynamicColumn = $this->colEstimate;

		$this->activeSheet->setCellValue($this->colEstimate . $this->currentRow, $this->estimateTotal);
		$this->activeSheet->getStyle($this->colEstimate . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colEstimate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		foreach ( $this->subCons as $subCon )
		{
			$lastDynamicColumn ++;

			$this->activeSheet->setCellValue($lastDynamicColumn . $row, $this->subConTotalAmount[$subCon['id']]);
			$this->activeSheet->getStyle($lastDynamicColumn . $row)->applyFromArray($this->getRateStyling());
			$this->activeSheet->getStyle($lastDynamicColumn . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());
		}

		$this->activeSheet->getStyle($this->colEstimate . $row . ":" . $this->lastCol . $row)->applyFromArray($style);
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
		$this->firstCol    = $this->colItem;
		$lastDynamicColumn = $this->colEstimate;

		array_walk($this->subCons, function () use (&$lastDynamicColumn)
		{
			$lastDynamicColumn ++;
		});

		$this->lastCol = $lastDynamicColumn;
	}

	public function setSubCons(array $newSubCons)
	{
		$this->subCons = $newSubCons;

		foreach ( $this->subCons as $subCon )
		{
			$this->subConTotalAmount[$subCon['id']] = 0;
		}
	}

	public function setSubConBillTotal($subConsBillTotals)
	{
		$this->subConsBillTotals = $subConsBillTotals;
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

	public function setTotalPage($totalPage)
	{
		$this->totalPage = $totalPage;
	}

	public function setLastPage($lastPage)
	{
		$this->lastPage = $lastPage;
	}

	public function setCurrency($getCurrency)
	{
		$this->currency = $getCurrency;
	}

}