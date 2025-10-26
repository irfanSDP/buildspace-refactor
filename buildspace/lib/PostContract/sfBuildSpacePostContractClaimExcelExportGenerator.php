<?php

/**
 * @property mixed overallClaimCoordinates
 */
class sfBuildSpacePostContractClaimExcelExportGenerator {

	protected $objPHPExcel;

	protected $tenderCompany;

    protected $totalContractAmountCoordinates;

	protected $withNotListedItem;

	public function __construct(PostContract $postContract, array $revision, array $data, array $additionalAutoBills, $pageNoPrefix)
	{
		$this->objPHPExcel = new sfPhpExcel();
		$this->objPHPExcel->setActiveSheetIndex(0);

		//Set Project & Path Information
		$this->postContract        = $postContract;
		$this->project             = $this->postContract->ProjectStructure;
		$this->data                = $data;
		$this->additionalAutoBills = $additionalAutoBills;
		$this->revision            = $revision;
		$this->pageNoPrefix        = $pageNoPrefix;

		$this->setProperties();
	}

	private function setProperties()
	{
		$companyProfile = Doctrine_Core::getTable('myCompanyProfile')->find(1);

		$this->objPHPExcel->getProperties()->setCreator("Buildspace");
		$this->objPHPExcel->getProperties()->setLastModifiedBy(trim(sfContext::getInstance()->getUser()->getGuardUser()->Profile->name));
		$this->objPHPExcel->getProperties()->setTitle(trim($this->project->ProjectSummaryGeneralSetting->summary_title));
		$this->objPHPExcel->getProperties()->setSubject("Tender Summary");
		$this->objPHPExcel->getProperties()->setDescription(trim($this->project->ProjectSummaryGeneralSetting->project_title));
		$this->objPHPExcel->getProperties()->setKeywords("Buildspace Claim Summary");
		$this->objPHPExcel->getProperties()->setCategory("Buildspace Claim Summary");
		$this->objPHPExcel->getProperties()->setCompany(trim($companyProfile->name));
	}

	private function generateCells()
	{
		$page = $this->data;

		if($page['summary_items'] instanceof SplFixedArray)
		{
			$headerRowCount = 0;

			$this->objPHPExcel->getActiveSheet()->setTitle('Tender Summary');

			foreach($page['header'] as $header)
			{
				$headerRowCount++;

				$this->objPHPExcel->getActiveSheet()->setCellValue('A'.$headerRowCount, $header[0]);
				$this->objPHPExcel->getActiveSheet()->mergeCells("A{$headerRowCount}:E{$headerRowCount}");
				$this->objPHPExcel->getActiveSheet()->getStyle("A{$headerRowCount}:E{$headerRowCount}")->applyFromArray(array(
					'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
				));
			}

			$this->generateClaimInformation($headerRowCount);

			$this->objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
			$this->objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
			$this->objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

			$headerRowCount++;

			$this->createTableHeader($headerRowCount);

			$rowCount = $headerRowCount;

			$firstContractAmountCell = NULL;
			$firstClaimAmountCell    = NULL;

			$tableRowCounter = 0;

			foreach($page['summary_items'] as $summaryItems)
			{
				foreach($summaryItems as $summaryItem)
				{
					$tableRowCounter++;

					$rowCount = $tableRowCounter + $headerRowCount;

					$this->objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $summaryItem[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_CHAR_REF]);
					$this->objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, $summaryItem[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_TITLE]);
					$this->objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount, $summaryItem[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL]);
					$this->objPHPExcel->getActiveSheet()->setCellValue('E'.$rowCount, $summaryItem[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT]);

					// only bold the level row
					if ( $summaryItem[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_TYPE] != ProjectStructure::TYPE_BILL )
					{
						$this->objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->getFont()->setBold(true);
					}
					else
					{
						if ( ! empty($summaryItem[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_PERCENTAGE]) )
						{
							$this->objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, $summaryItem[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_PERCENTAGE] / 100);
						}

						$this->objPHPExcel->getActiveSheet()
						->getStyle('C'.$rowCount)
						->getNumberFormat()
						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

						$this->objPHPExcel->getActiveSheet()
						->getStyle('D'.$rowCount)
						->getNumberFormat()
						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

						$this->objPHPExcel->getActiveSheet()
						->getStyle('E'.$rowCount)
						->getNumberFormat()
						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
					}


					if($tableRowCounter == 1)
					{
						$firstContractAmountCell = "C".$rowCount;
						$firstClaimAmountCell    = "E".$rowCount;
					}

					unset($summaryItem);
				}

				unset($summaryItems);
			}

			if($rowCount > $headerRowCount)
			{
				$this->objPHPExcel->getActiveSheet()
				->getStyle("A".($headerRowCount+1).":A".$rowCount)
				->getAlignment()
				->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

				$this->objPHPExcel->getActiveSheet()->getStyle("A".($headerRowCount+1).":A".$rowCount)->getFont()->setBold(true);
			}

			$this->objPHPExcel->getActiveSheet()->setCellValue('B'.($rowCount+1), "Total Contract Amount ({$this->project->MainInformation->Currency->currency_code})");
			$this->objPHPExcel->getActiveSheet()
			->getStyle('B'.($rowCount+1))
			->getAlignment()
			->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

			$this->createFormulaCellForOverallContractTotal($firstContractAmountCell, $rowCount);

			$this->createFormulaCellForOverallClaimTotal($firstClaimAmountCell, $rowCount);

			$this->createFormulaCellForClaimPercentage($rowCount);

			$this->objPHPExcel->getActiveSheet()->getStyle("B".($rowCount+1).":E".($rowCount+1))->getFont()->setBold(true);

			$this->objPHPExcel->getActiveSheet()->getStyle("A".($rowCount+1).":E".($rowCount+1))
			->applyFromArray(array(
					'borders' => array(
						'top' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
							'color' => array( 'argb' => '000000' ),
						)
					)
				)
			);

			$this->objPHPExcel->getActiveSheet()->getStyle("A".($headerRowCount-1).":E".($rowCount+1))
			->applyFromArray(array(
				'borders' => array(
					'vertical' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array( 'argb' => '000000' ),
					),
					'outline' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array( 'argb' => '000000' ),
					),
					'top' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array( 'argb' => '000000' ),
					),
					'bottom' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array( 'argb' => '000000' ),
					)
				)
			));

			$this->createAdditionalInformation($rowCount);
		}

		unset($page, $summaryPageGenerator);
	}

	public function write()
	{
		$this->generateCells();

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$tmpName = md5(date('dmYHis'));
		$tmpFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.$tmpName;

		$objWriter->save($tmpFile);

		unset($this->objPHPExcel);

		return $tmpFile;
	}

	private function generateClaimInformation(&$headerRowCount)
	{
		$headerRowCount++;

		$this->objPHPExcel->getActiveSheet()->setCellValue('A'.$headerRowCount, 'Summary');

		$this->objPHPExcel->getActiveSheet()->setCellValue('D'.$headerRowCount, 'Interim Valuation No: ' . $this->revision['version']);
		$this->objPHPExcel->getActiveSheet()->mergeCells("D{$headerRowCount}:E{$headerRowCount}");
		$this->objPHPExcel->getActiveSheet()->getStyle("D{$headerRowCount}:E{$headerRowCount}")->applyFromArray(array(
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
		));

		$headerRowCount++;

		$this->objPHPExcel->getActiveSheet()->setCellValue('D'.$headerRowCount, 'Date of Printing: ' . date('d/M/Y'));
		$this->objPHPExcel->getActiveSheet()->mergeCells("D{$headerRowCount}:E{$headerRowCount}");
		$this->objPHPExcel->getActiveSheet()->getStyle("D{$headerRowCount}:E{$headerRowCount}")->applyFromArray(array(
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
		));

		$headerRowCount++;
	}

	/**
	 * @param $headerRowCount
	 */
	private function createTableHeader(&$headerRowCount)
	{
		$this->objPHPExcel->getActiveSheet()->mergeCells("D{$headerRowCount}:E{$headerRowCount}");
		$this->objPHPExcel->getActiveSheet()->setCellValue("D{$headerRowCount}", 'Work Done');

		$this->objPHPExcel->getActiveSheet()->getStyle("D{$headerRowCount}:E{$headerRowCount}")
		->applyFromArray(array(
				'borders' => array(
					'bottom' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('argb' => '000000'),
					)
				)
			)
		);

		$headerRowCount++;

		$additionalTableRow = $headerRowCount;
		$additionalTableRow --;

		$this->objPHPExcel->getActiveSheet()->setCellValue('A' . $additionalTableRow, "Item");
		$this->objPHPExcel->getActiveSheet()->setCellValue('B' . $additionalTableRow, "Description");
		$this->objPHPExcel->getActiveSheet()->setCellValue('C' . $additionalTableRow, "Contract Amount");
		$this->objPHPExcel->getActiveSheet()->setCellValue('D' . $headerRowCount, "%");
		$this->objPHPExcel->getActiveSheet()->setCellValue('E' . $headerRowCount, "Amount (" . $this->project->MainInformation->Currency->currency_code . ")");

		$this->objPHPExcel->getActiveSheet()->mergeCells("A{$additionalTableRow}:A{$headerRowCount}");
		$this->objPHPExcel->getActiveSheet()->mergeCells("B{$additionalTableRow}:B{$headerRowCount}");
		$this->objPHPExcel->getActiveSheet()->mergeCells("C{$additionalTableRow}:C{$headerRowCount}");

		$this->objPHPExcel->getActiveSheet()->getStyle("A" . $headerRowCount . ":E" . $headerRowCount)
		->applyFromArray(array(
				'borders' => array(
					'bottom' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('argb' => '000000'),
					)
				)
			)
		);

		$this->objPHPExcel->getActiveSheet()
		->getStyle("A" . $additionalTableRow . ":E" . $headerRowCount)
		->getAlignment()
		->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$this->objPHPExcel->getActiveSheet()
		->getStyle("A" . $additionalTableRow . ":E" . $headerRowCount)
		->getAlignment()
		->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		$this->objPHPExcel->getActiveSheet()->getStyle("A1" . ":E" . $headerRowCount)->getFont()->setBold(TRUE);
	}

	/**
	 * @param $firstContractAmountCell
	 * @param $rowCount
	 */
	private function createFormulaCellForOverallContractTotal($firstContractAmountCell, $rowCount)
	{
        $this->totalContractAmountCoordinates = 'C' . ( $rowCount + 1 );

		if ($firstContractAmountCell)
		{
            $this->objPHPExcel->getActiveSheet()->setCellValue($this->totalContractAmountCoordinates, "=SUM(" . $firstContractAmountCell . ":C" . $rowCount . ")");

			$this->objPHPExcel->getActiveSheet()
			->getStyle('C' . ($rowCount + 1))
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		}
	}

	/**
	 * @param $firstClaimAmountCell
	 * @param $rowCount
	 */
	private function createFormulaCellForOverallClaimTotal($firstClaimAmountCell, $rowCount)
	{
		$this->overallClaimCoordinates = 'E' . ($rowCount + 1);

		if ($firstClaimAmountCell)
		{
			$this->objPHPExcel->getActiveSheet()->setCellValue($this->overallClaimCoordinates, "=SUM(" . $firstClaimAmountCell . ":E" . $rowCount . ")");

			$this->objPHPExcel->getActiveSheet()
			->getStyle('E' . ($rowCount + 1))
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		}
	}

	/**
	 * @param $rowCount
	 */
	private function createFormulaCellForClaimPercentage($rowCount)
	{
		$additionalRowCount = $rowCount;
		$additionalRowCount++;

		$this->objPHPExcel->getActiveSheet()->setCellValue('D' . ($rowCount + 1), "=(E{$additionalRowCount}/C{$additionalRowCount})");

		$this->objPHPExcel->getActiveSheet()
		->getStyle('D' . ($rowCount + 1))
		->getNumberFormat()
		->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
	}

	/**
	 * @param $rowCount
	 */
	private function createAdditionalInformation(&$rowCount)
	{
		// create additional information
		$rowCount = $rowCount + 3;

		$additionalInformationRowCount = $rowCount;

		$this->objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, "Additional");
		$this->objPHPExcel->getActiveSheet()->getStyle('B' . $rowCount)->getFont()->setBold(TRUE);

		$rowCount ++;
		$rowCount ++;

        $voOverallTotalAfterMarkupCoordinates = 'C' . $rowCount;
        $voUpToDatePercentageCoordinates = 'D' . $rowCount;
        $voUpToDateAmountCoordinates = 'E' . $rowCount;

        $this->objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, '1) ' . PostContractClaim::TYPE_VARIATION_ORDER_TEXT);
        $this->objPHPExcel->getActiveSheet()->setCellValue($voOverallTotalAfterMarkupCoordinates, $this->additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['overall_total_after_markup']);
        $this->objPHPExcel->getActiveSheet()->getStyle($voOverallTotalAfterMarkupCoordinates)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $this->objPHPExcel->getActiveSheet()->setCellValue($voUpToDatePercentageCoordinates, $this->additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['up_to_date_percentage']/100);
        $this->objPHPExcel->getActiveSheet()->getStyle($voUpToDatePercentageCoordinates)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

        $this->objPHPExcel->getActiveSheet()->setCellValue($voUpToDateAmountCoordinates, $this->additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['up_to_date_amount']);
        $this->objPHPExcel->getActiveSheet()->getStyle($voUpToDateAmountCoordinates)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

		$rowCount ++;
		$rowCount ++;

		$mosCoordinates = 'E' . $rowCount;

		$this->objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, '2) ' . PostContractClaim::TYPE_MATERIAL_ON_SITE_TEXT);
		$this->objPHPExcel->getActiveSheet()->setCellValue($mosCoordinates, $this->additionalAutoBills[PostContractClaim::TYPE_MATERIAL_ON_SITE]['overall_total_after_markup']);
		$this->objPHPExcel->getActiveSheet()->getStyle($mosCoordinates)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

		$rowCount ++;
		$rowCount ++;

		$this->objPHPExcel->getActiveSheet()->getStyle("A" . ($additionalInformationRowCount - 1) . ":E" . ($rowCount))
		->applyFromArray(array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => '000000'),
				),
				'top'     => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => '000000'),
				),
				'bottom'  => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => '000000'),
				)
			)
		));

		$rowCount ++;

		// create total carried to certificate
        $this->objPHPExcel->getActiveSheet()->getStyle("B{$rowCount}")->applyFromArray(array(
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
		));
		$this->objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, 'TOTAL CARRIED TO CERTIFICATE ('.$this->project->MainInformation->Currency->currency_code.')');
		$this->objPHPExcel->getActiveSheet()->getStyle('B' . $rowCount)->getFont()->setBold(TRUE);

		$this->objPHPExcel->getActiveSheet()->mergeCells("D{$rowCount}:E{$rowCount}");

        // Total Contract Amount.
        $this->objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, '=SUM('.$this->totalContractAmountCoordinates.'+'.$voOverallTotalAfterMarkupCoordinates.')');

        // Claim Amount.
        $this->objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, '=SUM('.$this->overallClaimCoordinates.'+'.$voUpToDateAmountCoordinates.'+'.$mosCoordinates.')');
        $this->objPHPExcel->getActiveSheet()->getStyle('D' . $rowCount)->getFont()->setBold(TRUE);
        $this->objPHPExcel->getActiveSheet()->getStyle('D' . $rowCount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

		$this->objPHPExcel->getActiveSheet()->getStyle("A" . ($rowCount) . ":E" . ($rowCount))
		->applyFromArray(array(
			'borders' => array(
				'vertical' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				),
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				),
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				),
				'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				)
			)
		));
	}

}