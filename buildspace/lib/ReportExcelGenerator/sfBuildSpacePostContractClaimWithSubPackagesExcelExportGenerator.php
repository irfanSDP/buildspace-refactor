<?php

class sfBuildSpacePostContractClaimWithSubPackagesExcelExportGenerator {

	protected $objPHPExcel;

	protected $tenderCompany;

	protected $withNotListedItem;

	private $overallContractAmt = 0;

	private $overallContractClaimAmt = 0;

	private $subPackageContractAmt = 0;

	private $subPackageClaimAmt = 0;

	public function __construct(PostContract $postContract, array $revision, array $data, $pageNoPrefix)
	{
		$this->objPHPExcel = new sfPhpExcel();
		$this->objPHPExcel->setActiveSheetIndex(0);

		//Set Project & Path Information
		$this->postContract = $postContract;
		$this->project      = $this->postContract->ProjectStructure;
		$this->data         = $data;
		$this->revision     = $revision;
		$this->pageNoPrefix = $pageNoPrefix;

		$this->setProperties();
	}

	private function setProperties()
	{
		$companyProfile = Doctrine_Core::getTable('myCompanyProfile')->find(1);

		$this->objPHPExcel->getProperties()->setCreator("BuildSpace");
		$this->objPHPExcel->getProperties()->setLastModifiedBy(trim(sfContext::getInstance()->getUser()->getGuardUser()->Profile->name));
		$this->objPHPExcel->getProperties()->setTitle(trim($this->project->ProjectSummaryGeneralSetting->summary_title));
		$this->objPHPExcel->getProperties()->setSubject("Tender Summary");
		$this->objPHPExcel->getProperties()->setDescription(trim($this->project->ProjectSummaryGeneralSetting->project_title));
		$this->objPHPExcel->getProperties()->setKeywords('BuildSpace Claim Summary with Sub Packages');
		$this->objPHPExcel->getProperties()->setCategory('BuildSpace Claim Summary with Sub Packages');
		$this->objPHPExcel->getProperties()->setCompany(trim($companyProfile->name));
	}

	private function generateCells()
	{
		$page = $this->data;

		if ( $page['summary_items'] instanceof SplFixedArray )
		{
			$headerRowCount = 0;

			$this->objPHPExcel->getActiveSheet()->setTitle('Tender Summary');

			foreach ( $page['header'] as $header )
			{
				$headerRowCount ++;

				$this->objPHPExcel->getActiveSheet()->setCellValue('A' . $headerRowCount, $header[0]);
				$this->objPHPExcel->getActiveSheet()->mergeCells("A{$headerRowCount}:E{$headerRowCount}");
				$this->objPHPExcel->getActiveSheet()->getStyle("A{$headerRowCount}:E{$headerRowCount}")->applyFromArray(array(
					'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER )
				));
			}

			$this->objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
			$this->objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
			$this->objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

			$headerRowCount ++;

			$this->createTableHeader($headerRowCount);

			$rowCount = $headerRowCount;

			$tableRowCounter = 0;

			foreach ( $page['summary_items'] as $summaryItems )
			{
				foreach ( $summaryItems as $summaryItem )
				{
					$tableRowCounter ++;

					$rowCount = $tableRowCounter + $headerRowCount;

					if ( $summaryItem[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_TYPE] == sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUBPACKAGE_HEADER_TYPE )
					{
						$this->objPHPExcel->getActiveSheet()->mergeCells("A" . ( $rowCount ) . ":E" . ( $rowCount ));

						$this->objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, $summaryItem[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_TITLE]);

						$this->objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount)->getFont()->setBold(true);
						$this->objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

						$this->objPHPExcel->getActiveSheet()->getStyle("A" . ( $rowCount ) . ":E" . ( $rowCount ))->applyFromArray(array(
							'borders' => array(
								'top'    => array(
									'style' => PHPExcel_Style_Border::BORDER_THIN,
									'color' => array( 'argb' => '000000' ),
								),
								'bottom' => array(
									'style' => PHPExcel_Style_Border::BORDER_THIN,
									'color' => array( 'argb' => '000000' ),
								)
							)
						));

						continue;
					}

					$this->objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, $summaryItem[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_CHAR_REF]);

					$this->objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

					$this->objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, $summaryItem[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_TITLE]);
					$this->objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $summaryItem[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL]);
					$this->objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCount, $summaryItem[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT]);

					// style total column
					if ( $summaryItem[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_TYPE] == sfBuildSpacePostContractClaimWithSubPackageReportGenerator::TOTAL_ROW_TYPE )
					{
						$this->objPHPExcel->getActiveSheet()->getStyle('B' . $rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

						$this->objPHPExcel->getActiveSheet()->getStyle("A" . ( $rowCount ) . ":E" . ( $rowCount ))->applyFromArray(array(
							'borders' => array(
								'top'    => array(
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

					if ( !empty( $summaryItem[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_PERCENTAGE] ) )
					{
						$this->objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, $summaryItem[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_PERCENTAGE]);
					}

					$this->objPHPExcel->getActiveSheet()
						->getStyle('C' . $rowCount)
						->getNumberFormat()
						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

					$this->objPHPExcel->getActiveSheet()
						->getStyle('D' . $rowCount)
						->getNumberFormat()
						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

					$this->objPHPExcel->getActiveSheet()
						->getStyle('E' . $rowCount)
						->getNumberFormat()
						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

					unset( $summaryItem );
				}

				unset( $summaryItems );
			}

			$this->generateTotalSubPackagesRow($rowCount);
			$this->generateNettRow($rowCount);

			$this->objPHPExcel->getActiveSheet()->getStyle("A" . ( $headerRowCount - 1 ) . ":E" . ( $rowCount + 1 ))->applyFromArray(array(
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
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array( 'argb' => '000000' ),
					),
					'bottom'   => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array( 'argb' => '000000' ),
					)
				)
			));
		}

		unset( $page, $summaryPageGenerator );
	}

	public function write()
	{
		$this->generateCells();

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$tmpName   = md5(date('dmYHis'));
		$tmpFile   = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpName;

		$objWriter->save($tmpFile);

		unset( $this->objPHPExcel );

		return $tmpFile;
	}

	/**
	 * @param $headerRowCount
	 */
	private function createTableHeader(&$headerRowCount)
	{
		$this->objPHPExcel->getActiveSheet()->mergeCells("D{$headerRowCount}:E{$headerRowCount}");
		$this->objPHPExcel->getActiveSheet()->setCellValue("D{$headerRowCount}", 'Total Claimed');

		$this->objPHPExcel->getActiveSheet()->getStyle("D{$headerRowCount}:E{$headerRowCount}")->applyFromArray(array(
			'borders' => array(
				'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				)
			)
		));

		$headerRowCount ++;

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
							'color' => array( 'argb' => '000000' ),
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

		$this->objPHPExcel->getActiveSheet()->getStyle("A1" . ":E" . $headerRowCount)->getFont()->setBold(true);
	}

	private function generateTotalSubPackagesRow(&$rowCount)
	{
		$rowCount = $rowCount + 1;

		$this->objPHPExcel->getActiveSheet()
			->setCellValue('B' . ( $rowCount ), "Total Sub Packages ({$this->project->MainInformation->Currency->currency_code})");

		$this->objPHPExcel->getActiveSheet()
			->getStyle('B' . ( $rowCount ))
			->getAlignment()
			->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

		$this->objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $this->subPackageContractAmt);
		$this->objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCount, $this->subPackageClaimAmt);

		$this->objPHPExcel->getActiveSheet()->getStyle("B" . ( $rowCount ) . ":E" . ( $rowCount ))->getFont()->setBold(true);

		$this->objPHPExcel->getActiveSheet()->getStyle("A" . ( $rowCount ) . ":E" . ( $rowCount ))->applyFromArray(array(
			'borders' => array(
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				)
			)
		));

		$this->objPHPExcel->getActiveSheet()
			->getStyle('C' . $rowCount)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

		$this->objPHPExcel->getActiveSheet()
			->getStyle('D' . $rowCount)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

		$this->objPHPExcel->getActiveSheet()
			->getStyle('E' . $rowCount)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	}

	private function generateNettRow($rowCount)
	{
		$rowCount = $rowCount + 1;

		$this->objPHPExcel->getActiveSheet()
			->setCellValue('B' . ( $rowCount ), "Nett ({$this->project->MainInformation->Currency->currency_code})");

		$this->objPHPExcel->getActiveSheet()
			->getStyle('B' . ( $rowCount ))
			->getAlignment()
			->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

		$this->objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $this->overallContractAmt - $this->subPackageContractAmt);
		$this->objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCount, $this->overallContractClaimAmt - $this->subPackageClaimAmt);

		$this->objPHPExcel->getActiveSheet()->getStyle("B" . ( $rowCount ) . ":E" . ( $rowCount ))->getFont()->setBold(true);

		$this->objPHPExcel->getActiveSheet()->getStyle("A" . ( $rowCount ) . ":E" . ( $rowCount ))->applyFromArray(array(
			'borders' => array(
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'argb' => '000000' ),
				)
			)
		));

		$this->objPHPExcel->getActiveSheet()
			->getStyle('C' . $rowCount)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

		$this->objPHPExcel->getActiveSheet()
			->getStyle('D' . $rowCount)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

		$this->objPHPExcel->getActiveSheet()
			->getStyle('E' . $rowCount)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	}

	public function setOverallContractAmt($value)
	{
		$this->overallContractAmt = $value;
	}

	public function setOverallContractClaimAmt($value)
	{
		$this->overallContractClaimAmt = $value;
	}

	public function setSubPackageContractAmt($value)
	{
		$this->subPackageContractAmt = $value;
	}

	public function setSubPackageClaimAmt($value)
	{
		$this->subPackageClaimAmt = $value;
	}

}