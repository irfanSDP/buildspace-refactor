<?php

class sfResourceLibrarySupplierRateExcelExporterGenerator extends sfBuildspaceExcelReportGenerator {

	protected $resourceItemInfo;
	protected $resourceItem;

	private $pageNo = 1;

	public $colItem = 'B';
	public $colSupplier = 'C';
	public $colProject = 'D';
	public $colCountry = 'E';
	public $colState = 'F';
	public $colRate = 'G';
	public $colRemarks = 'H';
	public $colDate = 'I';

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

		$description = '';
		$char        = '';

		$this->createNewPage($this->pageNo, false, 0);

		$this->createResourceItemHeader();

		foreach ( $pages as $i => $item )
		{
			if ( !$item )
			{
				continue;
			}

			$itemType = $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_TYPE];

			switch ($itemType)
			{
				case self::ROW_TYPE_BLANK:

					if ( $description != '' )
					{
						$description = '';
					}

					break;

				default:
					$description .= $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";
					$char .= $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_ROW_IDX];

					if ( $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_ID] )
					{
						$this->newItem();

						$this->setSupplierRow($item, $description, $char);

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
		$this->activeSheet->setCellValue($this->colSupplier . $row, 'Supplier');
		$this->activeSheet->setCellValue($this->colProject . $row, 'Project');
		$this->activeSheet->setCellValue($this->colCountry . $row, 'Country');
		$this->activeSheet->setCellValue($this->colState . $row, 'State');
		$this->activeSheet->setCellValue($this->colRate . $row, 'Rate');
		$this->activeSheet->setCellValue($this->colRemarks . $row, 'Remarks');
		$this->activeSheet->setCellValue($this->colDate . $row, 'Date');

		$this->activeSheet->getColumnDimension($this->colSupplier)->setWidth(45);
		$this->activeSheet->getColumnDimension($this->colProject)->setWidth(45);
		$this->activeSheet->getStyle($this->colSupplier)->applyFromArray($this->getDescriptionStyling());

		$this->activeSheet->getColumnDimension($this->colCountry)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colState)->setWidth(12);
		$this->activeSheet->getColumnDimension($this->colProject)->setWidth(30);
		$this->activeSheet->getColumnDimension($this->colRemarks)->setWidth(20);

		$this->activeSheet->getStyle("{$this->firstCol}{$row}:{$this->lastCol}{$row}")->applyFromArray($this->getColumnHeaderStyle());
	}

	public function setSupplierRow($item, $description, $char)
	{
		$row         = $this->currentRow;
		$factorValue = null;
		$totalValue  = null;

		$this->activeSheet->setCellValue($this->colItem . $row, $char);
		$this->activeSheet->getStyle($this->colItem . $row)->applyFromArray($this->getNoStyle());

		$this->activeSheet->setCellValue($this->colSupplier . $row, $description);

		$this->activeSheet->setCellValue($this->colProject . $row, $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_PROJECT_TITLE]);
		$this->activeSheet->getStyle($this->colProject . $row)->applyFromArray($this->getProjectNameStyling());

		$this->activeSheet->setCellValue($this->colCountry . $row, $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_COUNTRY]);
		$this->activeSheet->getStyle($this->colCountry . $row)->applyFromArray($this->getProjectNameStyling());

		$this->activeSheet->setCellValue($this->colState . $row, $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_STATE]);
		$this->activeSheet->getStyle($this->colState . $row)->applyFromArray($this->getProjectNameStyling());

		$this->activeSheet->setCellValue($this->colRate . $row, $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_RATE]);
		$this->activeSheet->getStyle($this->colRate . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colRate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colRemarks . $row, $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_REMARKS]);
		$this->activeSheet->getStyle($this->colRemarks . $row)->applyFromArray($this->getProjectNameStyling());

		$this->activeSheet->setCellValue($this->colDate . $row, date('d-m-Y', strtotime($item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_LAST_UPDATED])));
		$this->activeSheet->getStyle($this->colDate . $row)->applyFromArray($this->getProjectNameStyling());

		if ( $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_IS_SELECTED] )
		{
			$this->activeSheet->getStyle("{$this->colItem}{$row}:{$this->colDate}{$row}")->applyFromArray($this->setSelectedSupplierStyling());
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
			$this->activeSheet->setBreak($this->colSupplier . $this->currentRow, PHPExcel_Worksheet::BREAK_ROW);

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
		$this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->colSupplier . $this->currentRow);
		$this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->colSupplier . $this->currentRow)->applyFromArray($this->getLeftTitleStyle());
		$this->currentRow ++;
	}

	public function createResourceItemHeader()
	{
		$description = '';

		foreach ( $this->resourceItemInfo as $item )
		{
			$itemType = $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_TYPE];

			switch ($itemType)
			{
				case self::ROW_TYPE_BLANK:
					$this->newLine();
					break;

				default:
					$description .= $item[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_DESCRIPTION] . "\n";

					if ( $item[0] )
					{
						$this->setResourceItemRow(strip_tags($description));

						$description = '';
					}

					break;
			}
		}
	}

	public function startBillCounter()
	{
		$this->firstCol = $this->colItem;
		$this->lastCol  = $this->colDate;
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

	private function setSelectedSupplierStyling()
	{
		return array(
			'font' => array(
				'color' => array( 'rgb' => '0000FF' ),
			)
		);
	}

	public function setResourceItemInfo($resourceItemInfo)
	{
		$this->resourceItemInfo = $resourceItemInfo;
	}

	public function setResourceItem($resourceItem)
	{
		$this->resourceItem = $resourceItem;
	}

	private function setResourceItemRow($description)
	{
		$row = $this->currentRow;

		$styling['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
		$styling['borders']['bottom']['color'] = array( 'argb' => '000000' );

		$this->activeSheet->setCellValue($this->colSupplier . $row, $description);

		$this->activeSheet->setCellValue($this->colState . $row, $this->resourceItem['uom_symbol']);
		$this->activeSheet->getStyle($this->colState . $row)->applyFromArray($this->getProjectNameStyling());

		$this->activeSheet->setCellValue($this->colRate . $row, $this->resourceItem['total_rate']);
		$this->activeSheet->getStyle($this->colRate . $row)->applyFromArray($this->getRateStyling());
		$this->activeSheet->getStyle($this->colRate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

		$this->activeSheet->setCellValue($this->colRemarks . $row, ResourceItemSelectedRateTable::getSortingTypeText($this->resourceItem['sorting_type']));
		$this->activeSheet->getStyle($this->colRemarks . $row)->applyFromArray($this->getProjectNameStyling());

		$this->activeSheet->getStyle("{$this->colItem}{$row}:{$this->colDate}{$row}")->applyFromArray($styling);
	}

}