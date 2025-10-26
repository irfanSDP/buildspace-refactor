<?php

class sfElementReportByTypesGenerator extends sfBuildspaceExcelReportGenerator {

	public $colEstimate = "D";

	private $tenderer = array();

	private $estimateOverAllTotal = 0;

	private $contractorOverAllTotal = 0;

	private $billColumnSetting;

	public $currentPage = 1;

	public function __construct($project = null, $savePath = null, $filename = null, $printSettings)
	{
		$filename = ( $filename ) ? $filename : $this->bill->title . '-' . date('dmY H_i_s');
		$savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

		$this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

		parent::__construct($project, $savePath, $filename, $printSettings);
	}

	public function printGrandTotalValue($style)
	{
		$this->setValue($this->colEstimate, $this->estimateOverAllTotal[$this->billColumnSetting->id]);

		$currCol = $this->colEstimate;

		foreach ( $this->tenderer as $tenderer )
		{
			++ $currCol;

			$grandTotal = ( $this->contractorOverAllTotal && isset( $this->contractorOverAllTotal[$this->billColumnSetting->id][$tenderer['id']] ) && $this->contractorOverAllTotal[$this->billColumnSetting->id][$tenderer['id']] != 0 ) ? $this->contractorOverAllTotal[$this->billColumnSetting->id][$tenderer['id']] : 0;

			parent::setValue($currCol, $grandTotal);
		}

		$this->activeSheet->getStyle($this->colEstimate . $this->currentRow . ":" . $currCol . $this->currentRow)
			->applyFromArray($style);
	}

    public function printGrandTotal()
    {
        parent::printGrandTotal();

        $this->activeSheet->setCellValue($this->colDescription . $this->currentRow, "Units:");

        parent::setNormalQtyValue($this->colEstimate, $this->billColumnSetting->quantity);

        $this->mergeColumns($this->colEstimate, $this->currentRow, count($this->tenderer));

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
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
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

        $this->activeSheet->getStyle($this->colDescription . $this->currentRow)->applyFromArray($totalStyle);
        $this->activeSheet->getStyle($this->colEstimate . $this->currentRow)
            ->applyFromArray($newLineStyle);

        $this->currentRow++;

        $this->activeSheet->setCellValue($this->colDescription . $this->currentRow, "Final Total:");

        $this->setValue($this->colEstimate, $this->estimateOverAllTotal[ $this->billColumnSetting->id ] * $this->billColumnSetting->quantity);

        $currentColumn = $this->colEstimate;

        foreach($this->tenderer as $tenderer)
        {
            ++$currentColumn;

            $grandTotal = ( $this->contractorOverAllTotal && isset( $this->contractorOverAllTotal[ $this->billColumnSetting->id ][ $tenderer['id'] ] ) && $this->contractorOverAllTotal[ $this->billColumnSetting->id ][ $tenderer['id'] ] != 0 ) ? $this->contractorOverAllTotal[ $this->billColumnSetting->id ][ $tenderer['id'] ] * $this->billColumnSetting->quantity : 0;

            parent::setValue($currentColumn, $grandTotal);
        }

        $this->activeSheet->getStyle($this->colDescription . $this->currentRow)->applyFromArray($totalStyle);
        $this->activeSheet->getStyle($this->colEstimate . $this->currentRow . ":" . $currentColumn . $this->currentRow)
            ->applyFromArray($newLineStyle);

        $this->currentRow++;
    }

	public function startBillCounter()
	{
		$this->currentRow = $this->startRow;
		$this->firstCol   = $this->colItem;
		$this->lastCol    = $this->colEstimate;

		if ( count($this->tenderer) )
		{
			$currCol = $this->colEstimate;

			foreach ( $this->tenderer as $tenderer )
			{
				++ $currCol;
			}

			$this->lastCol = $currCol;
		}

		$this->currentElementNo = 0;
		$this->columnSetting    = null;
	}

	public function createHeader($new = false)
	{
		$this->activeSheet->setCellValue($this->colItem . $this->currentRow, "{$this->topLeftTitle} - {$this->billColumnSetting->name}");
		$this->activeSheet->mergeCells($this->colItem . "{$this->currentRow}:" . $this->colDescription . $this->currentRow);
		$this->activeSheet->getStyle($this->colItem . "{$this->currentRow}:" . $this->colDescription . $this->currentRow)->applyFromArray($this->getLeftTitleStyle());

		$this->currentRow ++;

		$row = $this->currentRow;

		//set default column
		$this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_NO);
		$this->activeSheet->setCellValue($this->colDescription . $row, self::COL_NAME_DESCRIPTION);
		$this->activeSheet->setCellValue($this->colEstimate . $row, self::COL_NAME_ESTIMATE);

		$this->createMultipleHeader();

		//Set Column Sizing
		$this->activeSheet->getColumnDimension("A")->setWidth(1.3);
		$this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
		$this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
		$this->activeSheet->getColumnDimension($this->colEstimate)->setWidth(15);
	}

	public function createMultipleHeader()
	{
		$row     = $this->currentRow;
		$currCol = $this->colEstimate;

		if ( count($this->tenderer) )
		{
			foreach ( $this->tenderer as $tenderer )
			{
				++ $currCol;

				$tendererName = ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'];

				if ( isset( $tenderer['selected'] ) AND $tenderer['selected'] )
				{
					// set the selected tenderer a blue marker
					$objRichText = new PHPExcel_RichText();
					$objBold     = $objRichText->createTextRun(( ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'] ));
					$objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

					$tendererName = $objRichText;
				}

				$this->activeSheet->setCellValue($currCol . $row, $tendererName);
				$this->activeSheet->getColumnDimension($currCol)->setWidth(15);
			}
		}

		//Set header styling
		$this->activeSheet->getStyle($this->colItem . $row . ':' . $currCol . $row)->applyFromArray($this->getColumnHeaderStyle());
		$this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	}

	public function processItems($item)
	{
		$billColumnSettingId = $this->billColumnSetting->id;

		parent::setValue($this->colEstimate, $item[sfBuildSpaceReportElementByTendererAndType::ROW_BILL_ITEM_ESTIMATE_TOTALS][$billColumnSettingId]);

		if ( count($this->tenderer) )
		{
			$currCol = $this->colEstimate;

			foreach ( $this->tenderer as $tenderer )
			{
				++ $currCol;

				$tendererId = $tenderer['id'];

				$value = isset( $item[sfBuildSpaceReportElementByTendererAndType::ROW_BILL_ITEM_CONTRACTOR_TOTALS][$billColumnSettingId][$tendererId] ) ? $item[sfBuildSpaceReportElementByTendererAndType::ROW_BILL_ITEM_CONTRACTOR_TOTALS][$billColumnSettingId][$tendererId] : 0;

				parent::setValue($currCol, $value);
			}
		}
	}

	public function endWritingExcelProcess()
	{
		$this->fileInfo = $this->writeExcel();
	}

	public function setTenderer($tenderer)
	{
		$this->tenderer = $tenderer;
	}

	public function setEstimateOverAllTotal($estimateOverAllTotal)
	{
		$this->estimateOverAllTotal = $estimateOverAllTotal;
	}

	public function setContractorOverAllTotal($contractorOverAllTotal)
	{
		$this->contractorOverAllTotal = $contractorOverAllTotal;
	}

	public function setCurrentBillColumnSetting($billColumnSetting)
	{
		$this->billColumnSetting = $billColumnSetting;
	}

	public function setupExportExcelPage($lock = false, $header, $topLeftTitle, $withoutCents, $totalPage)
	{
		$this->setExcelParameter($lock, $withoutCents);

		$this->totalPage = $totalPage;

		$this->createSheet($header, null, $topLeftTitle);
	}

	public function process($itemPages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
	{
		$this->topLeftTitle = $subTitle;

		$description   = '';
		$char          = '';
		$prevItemType  = '';
		$prevItemLevel = 0;

		foreach ( $itemPages as $page )
		{
			if ( count($page) )
			{
				$this->createNewPage($this->currentPage);

				foreach ( $page as $item )
				{
					$itemType = $item[4];

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

							if ( strpos($item[2], $this->printSettings['layoutSetting']['contdPrefix']) !== false )
							{
								$this->setElementTitle($this->printSettings['layoutSetting']['contdPrefix']);
							}
							else
							{
								$this->setElement(array( 'description' => $item[2] ));
							}

							break;
						case BillItem::TYPE_HEADER_N:
						case BillItem::TYPE_HEADER:

							$description .= $item[2] . "\n";
							$prevItemType  = $item[4];
							$prevItemLevel = $item[3];

							break;

						case BillItem::TYPE_ITEM_LUMP_SUM:
						case BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE:

							$description .= $item[2] . "\n";

							if ( $item[0] )
							{
								$this->newItem();

								$this->setItem($description, $itemType, $item[3]);

								$this->setUnit($item[5]);

								$this->setRate('-');

								$this->setQuantity('-', $item[8], '-');

								if ( $itemType == BillItem::TYPE_ITEM_LUMP_SUM )
								{
									$this->setAmount($item[6]);
								}

								$description = '';

								$char = '';
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

				$this->createFooter(true);

				$this->currentPage ++;
			}
		}
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

	public function setBillHeader($billHeader = null, $topLeftTitle, $subTitle)
	{
		$billHeader = ( $billHeader ) ? $billHeader : $this->filename;

		//Set Top Header
		$this->activeSheet->setCellValue($this->firstCol . "1", $billHeader);
		$this->activeSheet->mergeCells($this->firstCol . '1:' . $this->lastCol . '1');
		$this->activeSheet->getStyle($this->firstCol . '1:' . $this->lastCol . '1')->applyFromArray($this->getProjectTitleStyle());

		//Set SubTitle
		$this->activeSheet->setCellValue($this->firstCol . "2", $subTitle);
		$this->activeSheet->mergeCells($this->firstCol . '2:' . $this->lastCol . '2');
		$this->activeSheet->getStyle($this->firstCol . '2:' . $this->lastCol . '2')->applyFromArray($this->getSubTitleStyle());
	}

	public function createNewPage($pageNo = null, $printGrandTotal = false, $printFooter = false)
	{
		if ( !$pageNo )
		{
			return;
		}

		if ( $printFooter )
		{
			$this->createFooter($printGrandTotal);
		}

		$this->createHeader(true);

		$this->currentPage = $pageNo;

		$this->newLine();
	}

}