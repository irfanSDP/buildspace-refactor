<?php namespace PCK\Reports;

use PCK\Tenders\Tender;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference;
use PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption;
use PCK\TechnicalEvaluationItems\TechnicalEvaluationItem;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TechnicalEvaluationOverallSummaryReportGenerator extends ReportGenerator {

    private $colRef         = 'B';
    private $colSummaryItem = 'C';
    private $colWeighting   = 'D';
    private $colScore       = 'E';
    const STANDARD_GRAY = 'E8E8E8';

    public function generate(Tender $tender, TechnicalEvaluationSetReference $setReference)
    {
        $this->generateSummaryTable($tender, $setReference);

        $this->currentRow++;
        $this->currentRow++;
        $this->currentRow++;

        foreach($setReference->set->children as $aspect)
        {
            $this->generateAspectTable($tender, $setReference, $aspect);

            $this->currentRow++;
            $this->currentRow++;
            $this->currentRow++;
        }

        return $this->output($this->spreadsheet, trans('technicalEvaluation.techEvalOverallSummaryReport'));
    }

    protected function addTitle($title, $tenderers, $isSummary = true)
    {
        $endColumn = $this->getEndColumnIndexToBeOccupied(5, $tenderers);

        $this->activeSheet->setCellValue("{$this->colRef}{$this->currentRow}", $title);
        $this->activeSheet->mergeCells("{$this->colRef}{$this->currentRow}:{$endColumn}{$this->currentRow}");
        $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}")->applyFromArray($this->getTitleStyle($isSummary));

        $this->currentRow++;
    }

    private function generateSummaryTable(Tender $tender, TechnicalEvaluationSetReference $setReference)
    {
        $this->currentRow = 2;
        $numbering = 'A';
        $count = 0;
        
        $this->addTitle(trans('technicalEvaluation.summary'), $tender->selectedFinalContractors);
        $this->createHeader($tender->selectedFinalContractors);
        $this->setColumnWidths($tender->selectedFinalContractors);

        if(!$setReference->set->children->isEmpty())
        {
            foreach($setReference->set->children as $aspect)
            {
                $recordCount = $setReference->set->children->count();
                $isLastItem = (($count + 1) === $recordCount);
                $itemRowStyle = $isLastItem ? $this->getLastItemRowStyle() : $this->getItemRowStyle();

                $this->currentRow++;
    
                $this->activeSheet->setCellValue("{$this->colRef}{$this->currentRow}", $numbering);
                $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}")->applyFromArray($itemRowStyle);

                $this->activeSheet->setCellValue("{$this->colSummaryItem}{$this->currentRow}", $aspect->name);
                $this->activeSheet->getStyle("{$this->colSummaryItem}{$this->currentRow}")->applyFromArray($itemRowStyle);

                $this->activeSheet->setCellValue("{$this->colWeighting}{$this->currentRow}", ($aspect->value * 100) . '%');
                $this->activeSheet->getStyle("{$this->colWeighting}{$this->currentRow}")->applyFromArray($itemRowStyle);

                $this->activeSheet->setCellValue("{$this->colScore}{$this->currentRow}", number_format($aspect->value * 100, 2));
                $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($itemRowStyle);
    
                $currentCol = $this->colScore;
    
                foreach($tender->selectedFinalContractors as $tenderer)
                {
                    ++$currentCol;
                    $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", number_format((TechnicalEvaluationTendererOption::getTendererScore($tenderer, $aspect) * $aspect->value), 2));
                    $this->activeSheet->getStyle("{$currentCol}{$this->currentRow}")->applyFromArray($itemRowStyle);
                }
    
                $numbering++;
                $count++;
            }
        }
        else
        {
            $this->currentRow++;

            $startRow = $this->currentRow;

            $this->currentRow++;

            $endColumn = $this->getEndColumnIndexToBeOccupied(5, $tender->selectedFinalContractors);

            $this->activeSheet->mergeCells("{$this->colRef}{$startRow}:{$endColumn}{$this->currentRow}");
            $this->activeSheet->setCellValue("{$this->colRef}{$startRow}", trans('technicalEvaluation.noDataInTable'));
            $this->activeSheet->getStyle("{$this->colRef}{$startRow}:{$endColumn}{$this->currentRow}")->applyFromArray($this->getNoDataInTableStyles());
        }

        $this->createSummaryFooter($setReference, $tender->selectedFinalContractors);
    }

    private function generateAspectTable(Tender $tender, TechnicalEvaluationSetReference $setReference, TechnicalEvaluationItem $aspect)
    {
        $this->addTitle($aspect->name, $tender->selectedFinalContractors, false);
        $this->createHeader($tender->selectedFinalContractors, false);
        $this->setColumnWidths($tender->selectedFinalContractors);

        if(!$aspect->children->isEmpty())
        {
            $count = 0;
            $numbering = 1;
            $recordCount = $aspect->children->count();

            foreach($aspect->children as $criterion)
            {
                $isLastItem = (($count + 1) === $recordCount);
                $itemRowStyle = $isLastItem ? $this->getLastItemRowStyle() : $this->getItemRowStyle();

                $this->currentRow++;
                
                $this->activeSheet->setCellValue("{$this->colRef}{$this->currentRow}", $numbering);
                
                $this->activeSheet->mergeCells("{$this->colSummaryItem}{$this->currentRow}:{$this->colWeighting}{$this->currentRow}");
                $this->activeSheet->setCellValue("{$this->colSummaryItem}{$this->currentRow}", $criterion->name);

                $this->activeSheet->setCellValue("{$this->colScore}{$this->currentRow}", $criterion->value);

                $currentCol = $this->colScore;

                foreach($tender->selectedFinalContractors as $tenderer)
                {
                    ++$currentCol;
                    $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", number_format(TechnicalEvaluationTendererOption::getTendererScore($tenderer, $criterion), 2));
                }

                $numbering++;
                $count++;

                $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}:{$currentCol}{$this->currentRow}")->applyFromArray($itemRowStyle);
            }
        }
        else
        {
            $this->currentRow++;

            $startRow = $this->currentRow;

            $this->currentRow++;

            $endColumn = $this->getEndColumnIndexToBeOccupied(5, $tender->selectedFinalContractors);

            $this->activeSheet->mergeCells("{$this->colRef}{$startRow}:{$endColumn}{$this->currentRow}");
            $this->activeSheet->setCellValue("{$this->colRef}{$startRow}", trans('technicalEvaluation.noDataInTable'));
            $this->activeSheet->getStyle("{$this->colRef}{$startRow}:{$endColumn}{$this->currentRow}")->applyFromArray($this->getNoDataInTableStyles());
        }

        $this->createAspectTotalFooter($setReference, $aspect, $tender->selectedFinalContractors);
        $this->createAspectWeightingFooter($aspect, $tender->selectedFinalContractors);
        $this->createAspectOverallTotalFooter($aspect, $tender->selectedFinalContractors);
    }

    private function createHeader($tenderers, $isSummary = true)
    {
        $headerStartRow = $this->currentRow;

        $headers = [
            trans('technicalEvaluation.ref'),
            $isSummary ? trans('technicalEvaluation.summaryItems') : trans('technicalEvaluation.criteria'),
            trans('technicalEvaluation.weighting'),
            trans('technicalEvaluation.score'),
        ];

        $this->addHeaderColumns($headers, $this->colRef, $headerStartRow);

        $this->currentRow++;

        if($isSummary)
        {
            $this->activeSheet->mergeCells("{$this->colRef }{$headerStartRow}:{$this->colRef }{$this->currentRow}");
            $this->activeSheet->mergeCells("{$this->colSummaryItem}{$headerStartRow}:{$this->colSummaryItem}{$this->currentRow}");
            $this->activeSheet->getStyle("{$this->colSummaryItem}{$headerStartRow}:{$this->colSummaryItem}{$this->currentRow}")->getAlignment()->setWrapText(true);
            $this->activeSheet->mergeCells("{$this->colWeighting}{$headerStartRow}:{$this->colWeighting}{$this->currentRow}");
            $this->activeSheet->mergeCells("{$this->colScore}{$headerStartRow}:{$this->colScore}{$this->currentRow}");
        }
        else
        {
            $this->activeSheet->mergeCells("{$this->colRef }{$headerStartRow}:{$this->colRef }{$this->currentRow}");
            $this->activeSheet->mergeCells("{$this->colSummaryItem}{$headerStartRow}:{$this->colWeighting}{$this->currentRow}");
            $this->activeSheet->getStyle("{$this->colSummaryItem}{$headerStartRow}:{$this->colWeighting}{$this->currentRow}")->getAlignment()->setWrapText(true);
            $this->activeSheet->mergeCells("{$this->colScore}{$headerStartRow}:{$this->colScore}{$this->currentRow}");
        }
        
        $currentCol = $this->colScore;

        foreach($tenderers as $tenderer)
        {
            ++$currentCol;
            $this->addHeaderColumns($tenderer->name, $currentCol, $headerStartRow);
            $this->activeSheet->mergeCells("{$currentCol}{$headerStartRow}:{$currentCol}{$this->currentRow}");
            $this->activeSheet->getStyle("{$currentCol}{$headerStartRow}:{$currentCol}{$this->currentRow}")->getAlignment()->setWrapText(true);
        }

        $this->activeSheet->getStyle("{$this->colRef}{$headerStartRow}:{$currentCol}{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());
    }

    private function setColumnWidths($tenderers)
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colRef}")->setWidth(5);
        $this->activeSheet->getColumnDimension("{$this->colSummaryItem}")->setWidth(30);
        $this->activeSheet->getColumnDimension("{$this->colWeighting}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colScore}")->setWidth(22);

        $currentCol = $this->colScore;

        foreach($tenderers as $tenderer)
        {
            ++$currentCol;
            $this->activeSheet->getColumnDimension("{$currentCol}")->setWidth(45);
        }
    }

    private function createSummaryFooter(TechnicalEvaluationSetReference $setReference, $tenderers)
    {
        $this->currentRow++;

        $this->activeSheet->mergeCells("{$this->colRef}{$this->currentRow}:{$this->colSummaryItem}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$this->colRef}{$this->currentRow}", trans('technicalEvaluation.total'));
        $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}")->applyFromArray($this->getFooterTitleStyle(self::STANDARD_GRAY));

        $this->activeSheet->setCellValue("{$this->colWeighting}{$this->currentRow}", ($setReference->set->getChildrenValueTotal() * 100) . '%');
        $this->activeSheet->getStyle("{$this->colWeighting}{$this->currentRow}")->applyFromArray($this->getItemRowStyle(self::STANDARD_GRAY));
        
        $this->activeSheet->setCellValue("{$this->colScore}{$this->currentRow}", number_format(($setReference->set->getChildrenValueTotal() * 100), 2));
        $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($this->getItemRowStyle(self::STANDARD_GRAY));

        $currentCol = $this->colScore;

        foreach($tenderers as $tenderer)
        {
            ++$currentCol;
            $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", number_format(TechnicalEvaluationTendererOption::getTendererScore($tenderer, $setReference->set), 2));
        }

        $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}:{$currentCol}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle(self::STANDARD_GRAY));
    }

    private function createAspectTotalFooter(TechnicalEvaluationSetReference $setReference, TechnicalEvaluationItem $aspect, $tenderers)
    {
        $this->currentRow++;

        $this->activeSheet->mergeCells("{$this->colRef}{$this->currentRow}:{$this->colWeighting}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$this->colRef}{$this->currentRow}", trans('technicalEvaluation.total'));
        $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}")->applyFromArray($this->getFooterTitleStyle(self::STANDARD_GRAY));

        $this->activeSheet->setCellValue("{$this->colScore}{$this->currentRow}", number_format(($setReference->set->getChildrenValueTotal() * 100), 2));
        $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($this->getItemRowStyle(self::STANDARD_GRAY));

        $currentCol = $this->colScore;

        foreach($tenderers as $tenderer)
        {
            ++$currentCol;
            $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", number_format(TechnicalEvaluationTendererOption::getTendererScore($tenderer, $aspect), 2));
        }

        $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}:{$currentCol}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle(self::STANDARD_GRAY));
    }

    private function createAspectWeightingFooter(TechnicalEvaluationItem $aspect, $tenderers)
    {
        $this->currentRow++;

        $this->activeSheet->mergeCells("{$this->colRef}{$this->currentRow}:{$this->colWeighting}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$this->colRef}{$this->currentRow}", trans('technicalEvaluation.weighting'));
        $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}")->applyFromArray($this->getFooterTitleStyle(self::STANDARD_GRAY));

        $this->activeSheet->setCellValue("{$this->colScore}{$this->currentRow}", number_format($aspect->value, 2));
        $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($this->getItemRowStyle(self::STANDARD_GRAY));

        $currentCol = $this->colScore;

        foreach($tenderers as $tenderer)
        {
            ++$currentCol;
            $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", number_format($aspect->value, 2));
        }

        $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}:{$currentCol}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle(self::STANDARD_GRAY));
    }

    private function createAspectOverallTotalFooter(TechnicalEvaluationItem $aspect, $tenderers)
    {
        $this->currentRow++;

        $this->activeSheet->mergeCells("{$this->colRef}{$this->currentRow}:{$this->colWeighting}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$this->colRef}{$this->currentRow}", trans('technicalEvaluation.overallScore'));
        $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}")->applyFromArray($this->getFooterTitleStyle(self::STANDARD_GRAY));

        $this->activeSheet->setCellValue("{$this->colScore}{$this->currentRow}", number_format(($aspect->getChildrenValueTotal() * $aspect->value), 2) );
        $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($this->getItemRowStyle(self::STANDARD_GRAY));

        $currentCol = $this->colScore;

        foreach($tenderers as $tenderer)
        {
            ++$currentCol;
            $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", number_format((TechnicalEvaluationTendererOption::getTendererScore($tenderer, $aspect) * $aspect->value), 2));
        }

        $this->activeSheet->getStyle("{$this->colRef}{$this->currentRow}:{$currentCol}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle(self::STANDARD_GRAY));
    }

    private function getEndColumnIndexToBeOccupied($totalNumberOfFixedColumns, $tenderers)
    {
        foreach($tenderers as $tenderer)
        {
            $totalNumberOfFixedColumns++;
        }

        $endColumn = $this->colRef;

        for($i = 2; $i < $totalNumberOfFixedColumns; $i++)
        {
            $endColumn ++;
        }

        return $endColumn;
    }

    private function getNoDataInTableStyles()
    {
        return [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders'   => [
                'left'     => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => [ 'argb' => '000000' ],
                ],
                'right'    => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => [ 'argb' => '000000' ],
                ],
                'top'    => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => [ 'argb' => '000000' ],
                ],
                'bottom'    => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => [ 'argb' => '000000' ],
                ],
            ],
        ];
    }

    public function getTitleStyle($isSummary = true)
    {
        $titleStyle = parent::getTitleStyle();

        $titleStyle['fill'] = [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => [
                'argb' => $isSummary ? '6E587A' : '4C4F53',
            ]
        ];

        $titleStyle['font'] = ['color' => ['argb' => Color::COLOR_WHITE]];

        return $titleStyle;
    }

    public function getItemRowStyle($fillColor = null)
    {
        $itemRowStyle = parent::getItemRowStyle();

        if(!is_null($fillColor))
        {
            $itemRowStyle['fill'] = [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => $fillColor,
                ],
            ];
        }

        return $itemRowStyle;
    }

    public function getLastItemRowStyle($fillColor = null)
    {
        $lastItemRowStyle= parent::getLastItemRowStyle();

        if(!is_null($fillColor))
        {
            $lastItemRowStyle['fill'] = [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => $fillColor,
                ],
            ];
        }

        return $lastItemRowStyle;
    }

    private function getFooterTitleStyle($fillColor = null)
    {
        $footerTitleStyle = [
            'font'      => [
                'bold' => true
            ],
            'borders'   => [
                'left'     => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => [ 'argb' => '000000' ],
                ],
                'right'    => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => [ 'argb' => '000000' ],
                ],
                'top'    => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => [ 'argb' => '000000' ],
                ],
                'bottom'    => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => [ 'argb' => '000000' ],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ]
        ];

        if(!is_null($fillColor))
        {
            $footerTitleStyle['fill'] = [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => $fillColor,
                ],
            ];
        }

        return $footerTitleStyle;
    }
}

