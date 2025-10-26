<?php

//require_once 'symfony.inc.php';

class sfProjectSummaryExcelGenerator
{
    protected $objPHPExcel;
    protected $tenderCompany;
    protected $withNotListedItem;

    function __construct(ProjectStructure $project, $tenderCompany=null, $withNotListedItem=true)
    {
        $this->objPHPExcel = new sfPhpExcel();
        $this->objPHPExcel->setActiveSheetIndex(0);

        //Set Project & Path Information
        $this->project = $project;

        $this->tenderCompany = $tenderCompany;

        $this->withNotListedItem = $withNotListedItem;

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
        $this->objPHPExcel->getProperties()->setKeywords("Buildspace Project Summary");
        $this->objPHPExcel->getProperties()->setCategory("Buildspace Project Summary");
        $this->objPHPExcel->getProperties()->setCompany(trim($companyProfile->name));
    }

    private function generateCells()
    {
        $summaryPageGenerator = new sfBuildspaceProjectSummaryGenerator($this->project, false, $this->tenderCompany, $this->withNotListedItem);

        $page = $summaryPageGenerator->generatePage();

        if($page['summary_items'] instanceof SplFixedArray)
        {
            $headerRowCount = 0;

            $this->objPHPExcel->getActiveSheet()->setTitle('Tender Summary');

            foreach($page['header'] as $header)
            {
                $headerRowCount++;
                $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$headerRowCount, $header[0]);
            }

            $this->objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

            $headerRowCount++;

            $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$headerRowCount, "Item");
            $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$headerRowCount, "Description");
            $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$headerRowCount, "Page");
            $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$headerRowCount, "Amount (".$this->project->MainInformation->Currency->currency_code.")");

            $this->objPHPExcel->getActiveSheet()->getStyle("A".$headerRowCount.":D".$headerRowCount)
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
                ->getStyle("A".$headerRowCount.":D".$headerRowCount)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $this->objPHPExcel->getActiveSheet()->getStyle("A1".":D".$headerRowCount)->getFont()->setBold(true);

            $rowCount = $headerRowCount;

            $firstAmountCell = null;

            $tableRowCounter = 0;

            foreach($page['summary_items'] as $summaryItems)
            {
                foreach($summaryItems as $summaryItem)
                {
                    $tableRowCounter++;

                    $rowCount = $tableRowCounter + $headerRowCount;

                    $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $summaryItem[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_CHAR_REF]);
                    $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, $summaryItem[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_TITLE]);
                    $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount, $summaryItem[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE]);
                    $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, $summaryItem[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_TOTAL_AMOUNT]);

                    $this->objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->getFont()->setBold($summaryItem[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD]);
                    $this->objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->getFont()->setItalic($summaryItem[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC]);
                    $this->objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->getFont()->setUnderline($summaryItem[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE]);

                    if($tableRowCounter == 1)
                    {
                        $firstAmountCell = "D".$rowCount;
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

                $this->objPHPExcel->getActiveSheet()
                    ->getStyle("C".($headerRowCount+1).":C".$rowCount)
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $this->objPHPExcel->getActiveSheet()
                    ->getStyle('D'.($headerRowCount+1).':D'.$rowCount)
                    ->getNumberFormat()
                    ->setFormatCode(
                        PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            }

            $this->objPHPExcel->getActiveSheet()->setCellValue('C'.($rowCount+1), "Total Amount");

            if($firstAmountCell)
            {
                $this->objPHPExcel->getActiveSheet()->setCellValue('D'.($rowCount+1), "=SUM(".$firstAmountCell.":D".$rowCount.")");

                $this->objPHPExcel->getActiveSheet()
                    ->getStyle('D'.($rowCount+1))
                    ->getNumberFormat()
                    ->setFormatCode(
                        PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            }

            $this->objPHPExcel->getActiveSheet()
                ->getStyle("C".($rowCount+1))
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $this->objPHPExcel->getActiveSheet()->getStyle("C".($rowCount+1).":D".($rowCount+1))->getFont()->setBold(true);

            $this->objPHPExcel->getActiveSheet()->getStyle("A".($rowCount+1).":D".($rowCount+1))
                ->applyFromArray(array(
                        'borders' => array(
                            'top' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array( 'argb' => '000000' ),
                            )
                        )
                    )
                );

            $this->objPHPExcel->getActiveSheet()->getStyle("A".$headerRowCount.":D".($rowCount+1))
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
}
