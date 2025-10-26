<?php namespace PCK\Reports;

use PCK\Tenders\Tender;
use PCK\Projects\Project;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class OpenTenderFormReportGenerator extends ReportGenerator {

    const FRONT_FIXED_COLUMN_COUNT = 2;
    const REAR_FIXED_COLUMN_COUNT = 2;
    const BLANK_SPACE_LENGTH = 60;
    const BLANK_SPACE_CHAR = '_';

    private $formOfTenderRepository;
    private $tenderRepository;
    private $tender;
    private $isTechnicalAssessmentFormApproved;
    private $shortlistedTendererIds;
    private $pteBudgetRecords;

    private $colItem = 'B';
    private $colTenderer = 'C';
    private $colEarnestMoney = null;
    private $colRemarks = null;

    public function __construct(Tender $tender, $isTechnicalAssessmentFormApproved, $shortlistedTendererIds, Array $pteBudgetRecords)
    {
        parent::__construct();

        $this->formOfTenderRepository = \App::make('PCK\FormOfTender\FormOfTenderRepository');
        $this->tenderRepository = \App::make('PCK\Tenders\TenderRepository');
        $this->tender = $tender;
        $this->isTechnicalAssessmentFormApproved = $isTechnicalAssessmentFormApproved;
        $this->shortlistedTendererIds = $shortlistedTendererIds;
        $this->pteBudgetRecords = $pteBudgetRecords;
    }

    public function generate() {
        $this->currentRow = 2;
        $numbering = 'A';

        $includedTenderAlternatives = $this->formOfTenderRepository->getIncludedTenderAlternativesByFormOfTenderId($this->tender->id);
        $endCol = $this->getEndColumnIndexToBeOccupied(self::FRONT_FIXED_COLUMN_COUNT, self::REAR_FIXED_COLUMN_COUNT, $includedTenderAlternatives);

        $this->createTopSection($this->tender->project, $endCol);

        $this->currentRow++;
        $this->currentRow++;

        $this->createTable($includedTenderAlternatives, $endCol);

        $this->createConsultantPTERow($this->tender->project, $includedTenderAlternatives);

        $this->currentRow++;
        $this->currentRow++;

        $this->createTenderDetailsSection();

        $this->currentRow++;

        $this->createVerifiersSection($endCol);

        $this->currentRow++;

        $this->createDeclarationSection($endCol);

        $this->setColumnWidths($includedTenderAlternatives);

        return $this->output($this->spreadsheet, trans('tenders.openTenderForm'));
    }

    private function createTopSection(Project $project, $endCol)
    {
        $subsidiary = ($project->subsidiary)? $project->subsidiary->name : false;

        $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", 'Employer');

        $this->activeSheet->mergeCells("{$this->colTenderer}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$this->colTenderer}{$this->currentRow}", $subsidiary ? : $project->businessUnit->name);
        
        $this->currentRow++;

        $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", 'Project');

        $this->activeSheet->mergeCells("{$this->colTenderer}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$this->colTenderer}{$this->currentRow}", $project->title);
    }

    private function createTable($includedTenderAlternatives, $endCol)
    {
        $this->addTitle('RECORD OF TENDER OPENING', $includedTenderAlternatives, $endCol);
        $this->createHeader($includedTenderAlternatives, $this->tender->project, $endCol);

        $count = 0;
        $recordCount = count($this->shortlistedTendererIds);

        foreach($this->tender->submittedTenderRateContractors as $company)
        {
            $generator = new \PCK\TenderAlternatives\TenderAlternativeGenerator($this->tender, $company->pivot);
            $tenderAlternativeData = $generator->generateAllAfterContractorInput($includedTenderAlternatives);
            $company->tenderAlternativeData = $tenderAlternativeData;
        }

        $this->tender->submittedTenderRateContractors = $this->tenderRepository->sortSubmittedTenderRateContractorsByTenderAlternativeAmount($this->tender->submittedTenderRateContractors, 1);

        $bsProjectMainInformation = $this->tender->project->getBsProjectMainInformation();

        $bsTenderAlternativeIds = ($bsProjectMainInformation) ? $bsProjectMainInformation->projectStructure->tenderAlternatives()->lists('id') : [];

        $companyTenderTenderAlternatives = [];

        if(!empty($bsTenderAlternativeIds))
        {
            $companyTenderIds = [];
            foreach($this->tender->selectedFinalContractors as $company)
            {
                $companyTenderIds[] = $company->pivot->id;
            }

            if(!empty($companyTenderIds))
            {
                $records = \PCK\Tenders\CompanyTenderTenderAlternative::whereIn('company_tender_id', $companyTenderIds)->whereIn('tender_alternative_id', $bsTenderAlternativeIds)->get()->toArray();

                foreach($records as $record)
                {
                    if(!array_key_exists($record['company_tender_id'], $companyTenderTenderAlternatives))
                    {
                        $companyTenderTenderAlternatives[$record['company_tender_id']] = [];
                    }

                    $companyTenderTenderAlternatives[$record['company_tender_id']][$record['tender_alternative_id']] = $record;
                }
            }
        }

        foreach($this->tender->submittedTenderRateContractors as $company)
        {
            $isNotShortListed = !in_array($company->id, $this->shortlistedTendererIds);

            if($this->isTechnicalAssessmentFormApproved && $isNotShortListed) continue;

            $isLastItem = (($count + 1)  === $recordCount);
            $itemRowStyle = $isLastItem ? $this->getLastItemRowStyle() : $this->getItemRowStyle();

            $tableRows = [];

            foreach($company->tenderAlternativeData as $k => $records)
            {
                foreach($records as $tenderAlternative)
                {
                    if(array_key_exists($company->pivot->id, $companyTenderTenderAlternatives) && array_key_exists($tenderAlternative['tender_alternative_id'], $companyTenderTenderAlternatives[$company->pivot->id]))
                    {
                        $data = $companyTenderTenderAlternatives[$company->pivot->id][$tenderAlternative['tender_alternative_id']];

                        $earnestMoney               = $data['earnest_money'];
                        $remarks                    = $data['remarks'] ?? "";
                        $contractorDiscount         = $data['discounted_amount'];
                        $contractorCompletionPeriod = $data['completion_period'] + 0;
                        $contractorAdjustment       = ((float)$data['contractor_adjustment_percentage']) ? $data['contractor_adjustment_percentage'] : $data['contractor_adjustment_amount'];
                    }
                    else
                    {
                        $earnestMoney               = $company->pivot->earnest_money;
                        $remarks                    = $company->pivot->remarks ?? "";
                        $contractorDiscount         = $company->pivot->discounted_amount;
                        $contractorCompletionPeriod = $company->pivot->completion_period + 0;
                        $contractorAdjustment       = ((float)$company->pivot->contractor_adjustment_percentage) ? $company->pivot->contractor_adjustment_percentage : $company->pivot->contractor_adjustment_amount;
                    }

                    if(!array_key_exists($tenderAlternative['tender_alternative_id'], $tableRows))
                    {
                        $tableRows[$tenderAlternative['tender_alternative_id']] = [
                            'title'         => $tenderAlternative['tender_alternative_title'],
                            'earnest_money' => $earnestMoney,
                            'remarks'       => $remarks,
                            'columns'       => []
                        ];

                        for($x=0;$x<count($company->tenderAlternativeData);$x++)
                        {
                            $tableRows[$tenderAlternative['tender_alternative_id']]['columns'][$x] = [
                                'amount' => 0,
                                'period' => 0
                            ];
                        }
                    }

                    if(array_key_exists($k, $tableRows[$tenderAlternative['tender_alternative_id']]['columns']))
                    {
                        $tableRows[$tenderAlternative['tender_alternative_id']]['columns'][$k] = [
                            'amount' => $tenderAlternative['amount'],
                            'period' => $tenderAlternative['period']
                        ];
                    }
                }
            }

            foreach($tableRows as $row)
            {
                $this->currentRow++;

                $this->activeSheet->getStyle("{$this->colItem}{$this->currentRow}:{$this->colRemarks}{$this->currentRow}")->applyFromArray($this->getItemRowStyle());

                $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", ++$count);
                $this->activeSheet->getStyle("{$this->colItem}{$this->currentRow}")->applyFromArray($itemRowStyle);

                $txt = ($row['title']) ? $company->name."\r ".$row['title'] : $company->name;
                $this->activeSheet->setCellValue("{$this->colTenderer}{$this->currentRow}", $txt);
                $this->activeSheet->getStyle("{$this->colTenderer}{$this->currentRow}")->applyFromArray($itemRowStyle);
                $this->activeSheet->getStyle("{$this->colTenderer}{$this->currentRow}")->getAlignment()->setWrapText(true);

                $currentCol = $this->colTenderer;

                foreach($row['columns'] as $column)
                {
                    ++ $currentCol;

                    $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", $column['amount']);
                    $this->activeSheet->getStyle("{$currentCol}{$this->currentRow}")->applyFromArray($itemRowStyle);
                    $this->activeSheet->getStyle("{$currentCol}{$this->currentRow}")->getNumberFormat()->setFormatCode("#,##0.00");

                    ++ $currentCol;

                    $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", $column['period']);
                    $this->activeSheet->getStyle("{$currentCol}{$this->currentRow}")->applyFromArray($itemRowStyle);
                }

                $this->activeSheet->setCellValue("{$this->colEarnestMoney}{$this->currentRow}", $row['earnest_money'] ? 'Y' : 'N');
                $this->activeSheet->getStyle("{$this->colEarnestMoney}{$this->currentRow}")->applyFromArray($itemRowStyle);

                $this->activeSheet->setCellValue("{$this->colRemarks}{$this->currentRow}", $row['remarks']);
                $this->activeSheet->getStyle("{$this->colRemarks}{$this->currentRow}")->applyFromArray($itemRowStyle);
            }
        }

        $this->currentRow++;

        $this->activeSheet->getStyle("{$this->colItem}{$this->currentRow}:{$this->colRemarks}{$this->currentRow}")->applyFromArray($this->getItemRowStyle());
    }

    private function createConsultantPTERow($project, $includedTenderAlternatives)
    {
        foreach($this->pteBudgetRecords as $pteBudgetRecord)
        {
            $this->currentRow++;

            //$this->activeSheet->mergeCells("{$this->colItem}{$this->currentRow}:{$this->colTenderer}{$this->currentRow}");
            $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", '');
            $this->activeSheet->getStyle("{$this->colItem}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle());
            $currentCol = $this->colItem;

            $currentCol++;

            $txt = "PTE (".$project->modified_currency_code.")";

            if(strlen($pteBudgetRecord['title']) > 0)
            {
                $wizard = new \PhpOffice\PhpSpreadsheet\Helper\Html;
                $txt = $wizard->toRichTextObject("PTE / Budget <b>".$pteBudgetRecord['title']."</b> (".$project->modified_currency_code.")");
            }
            
            $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", $txt);
            $this->activeSheet->getStyle("{$currentCol}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle());

            $currentCol = $this->colTenderer;
            $appendedAtTenderAlternativeA = false;

            foreach($includedTenderAlternatives as $tenderAlternative)
            {
                ++$currentCol;

                $this->activeSheet->mergeCells("{$currentCol}{$this->currentRow}:{$currentCol}{$this->currentRow}");
                $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", $pteBudgetRecord['total']);
                $this->activeSheet->getStyle("{$currentCol}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle());
                $this->activeSheet->getStyle("{$currentCol}{$this->currentRow}")->getNumberFormat()->setFormatCode("#,##0.00");

                ++$currentCol;

                $this->activeSheet->mergeCells("{$currentCol}{$this->currentRow}:{$currentCol}{$this->currentRow}");
                $this->activeSheet->setCellValue("{$currentCol}{$this->currentRow}", '');
                $this->activeSheet->getStyle("{$currentCol}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle());

                $appendedAtTenderAlternativeA = true;
            }

            $this->activeSheet->mergeCells("{$this->colEarnestMoney}{$this->currentRow}:{$this->colEarnestMoney}{$this->currentRow}");
            $this->activeSheet->setCellValue("{$this->colEarnestMoney}{$this->currentRow}", '');
            $this->activeSheet->getStyle("{$this->colEarnestMoney}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle());

            $this->activeSheet->mergeCells("{$this->colRemarks}{$this->currentRow}:{$this->colRemarks}{$this->currentRow}");
            $this->activeSheet->setCellValue("{$this->colRemarks}{$this->currentRow}", '');
            $this->activeSheet->getStyle("{$this->colRemarks}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle());
        }
    }

    private function createTenderDetailsSection()
    {
        $this->activeSheet->mergeCells("{$this->colItem}{$this->currentRow}:{$this->colTenderer}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", $this->tender->current_tender_name);

        $col = $this->colTenderer;
        $fromCol = ++$col;
        $untilCol = ++$col;
        $preservedRowIndex = $this->currentRow;
        
        $this->activeSheet->mergeCells("{$fromCol}{$this->currentRow}:{$untilCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$fromCol}{$this->currentRow}", 'Date of Tender Calling : ' . \Carbon\Carbon::parse($this->tender->project->getProjectTimeZoneTime($this->tender->tender_starting_date))->format(\Config::get('dates.standard')));
    
        $this->currentRow++;

        $this->activeSheet->mergeCells("{$fromCol}{$this->currentRow}:{$untilCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$fromCol}{$this->currentRow}", 'Tender Valid Until : ' . \Carbon\Carbon::parse($this->tender->project->getProjectTimeZoneTime($this->tender->validUntil()))->format(\Config::get('dates.standard')));
    
        $this->currentRow++;

        $fromCol = ++$col;
        $untilCol = ++$col;

        $this->activeSheet->mergeCells("{$fromCol}{$preservedRowIndex}:{$untilCol}{$preservedRowIndex}");
        $this->activeSheet->setCellValue("{$fromCol}{$preservedRowIndex}", 'Date of Tender Closing : ' . \Carbon\Carbon::parse($this->tender->project->getProjectTimeZoneTime($this->tender->tender_closing_date))->format(\Config::get('dates.standard')));
    }

    private function createVerifiersSection($endCol)
    {
        $subsidiary = ($this->tender->project->subsidiary)? $this->tender->project->subsidiary->name : false;

        $this->activeSheet->mergeCells("{$this->colItem}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", 'The tenders as listed above were opened in our presence on this day.');
    
        $this->currentRow++;

        $selectedVerifiers = $this->tender->openTenderVerifiersApproved()->orderBy('created_at', 'asc')->get();

        foreach($selectedVerifiers as $verifier)
        {
            $verifier->log = $this->tenderRepository->getOpenTenderVerifierLogByTenderAndVerifierId($this->tender->id, $verifier->id);
            $this->currentRow++;

            $this->activeSheet->mergeCells("{$this->colItem}{$this->currentRow}:{$endCol}{$this->currentRow}");
            $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", 'Name : ' . $verifier->name);

            $this->currentRow++;

            $company = ($verifier->hasCompanyProjectRole($this->tender->project, \PCK\ContractGroups\Types\Role::PROJECT_OWNER) && $subsidiary) ? $subsidiary : $verifier->company->name;

            $this->activeSheet->mergeCells("{$this->colItem}{$this->currentRow}:{$endCol}{$this->currentRow}");
            $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", 'Company : ' . $company);
            $this->currentRow++;

            $this->activeSheet->mergeCells("{$this->colItem}{$this->currentRow}:{$endCol}{$this->currentRow}");
            $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", 'Date : ' . \Carbon\Carbon::parse($this->tender->project->getProjectTimeZoneTime($verifier->log->created_at))->format(\Config::get('dates.created_and_updated_at_formatting')));
            
            $this->currentRow++;
        }
    }

    private function createDeclarationSection($endCol)
    {
        $declarationLine1 = "We, {$this->createBlankSpace()} shall prepare and submit a Tender Report to the Employer on or before{$this->createBlankSpace(self::BLANK_SPACE_CHAR, self::BLANK_SPACE_LENGTH / 2)}.";
        $declarationLine2 = "We, {$this->createBlankSpace()} confirm to have taken custory of all Earnest Money for safekeeping.";

        $this->currentRow++;

        $this->activeSheet->mergeCells("{$this->colItem}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", $declarationLine1);

        $this->currentRow++;

        $this->activeSheet->mergeCells("{$this->colItem}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", $declarationLine2);
    }

    private function createBlankSpace($char = self::BLANK_SPACE_CHAR, $length = self::BLANK_SPACE_LENGTH)
    {
        return str_repeat($char, $length);
    }

    private function addTitle($title, $includedTenderAlternatives, $endCol)
    {
        $this->activeSheet->setCellValue("{$this->colItem}{$this->currentRow}", $title);
        $this->activeSheet->mergeCells("{$this->colItem}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->getStyle("{$this->colItem}{$this->currentRow}")->applyFromArray($this->getTitleStyle());

        $this->currentRow++;
    }

    private function createHeader($includedTenderAlternatives, $project, $endCol)
    {
        $headerStartRow = $this->currentRow;

        $ascLetters = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z'
        ];

        $headers = [];

        array_push($headers, 'Item');
        array_push($headers, 'Tenderer\'s Name');

        $isFirstClause = true;

        foreach($includedTenderAlternatives as $key => $tenderAlternative)
        {
            if($isFirstClause)
            {
                $displayText = 'Base Tender';
            }
            else
            {
                $displayText = array_key_exists(($key - 1), $ascLetters) ? 'Tender Alternative ' . $ascLetters[$key - 1] : 'Tender Alternative';
            }

            $headers[$displayText] = array(
                'Tender Amount (' . $project->modified_currency_code . ')',
                'Completion Period (' . $project->completion_period_metric . ')',
            );
            
            $isFirstClause = false;
        }

        array_push($headers, 'Earnest Money');
        array_push($headers, 'Remarks');

        $this->addHeaderColumns($headers, $this->colItem, $headerStartRow);

        $this->currentRow++;

        $this->activeSheet->mergeCells("{$this->colItem}{$headerStartRow}:{$this->colItem}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colTenderer}{$headerStartRow}:{$this->colTenderer}{$this->currentRow}");

        $this->colRemarks = $endCol;
        $this->colEarnestMoney = $this->getPreviousColumn(++$endCol);
        
        $this->activeSheet->mergeCells("{$this->colEarnestMoney}{$headerStartRow}:{$this->colEarnestMoney}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colRemarks}{$headerStartRow}:{$this->colRemarks}{$this->currentRow}");

        $this->activeSheet->getStyle("{$this->colItem}{$headerStartRow}:{$this->colRemarks}{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());
    }

    private function setColumnWidths($includedTenderAlternatives)
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colItem}")->setWidth(10);
        $this->activeSheet->getColumnDimension("{$this->colTenderer}")->setWidth(70);

        $currentCol = $this->colTenderer;

        foreach($includedTenderAlternatives as $tenderAlternative)
        {
            $currentCol++;
            $this->activeSheet->getColumnDimension("{$currentCol}")->setWidth(25);

            $currentCol++;
            $this->activeSheet->getColumnDimension("{$currentCol}")->setWidth(30);
        }

        $this->activeSheet->getColumnDimension("{$this->colEarnestMoney}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colRemarks}")->setWidth(50);
    }

    private function getEndColumnIndexToBeOccupied($frontFixedColumnCount, $rearFixedColumnCount, $includedTenderAlternatives)
    {
        $totalTenderAlternativesColumn = count($includedTenderAlternatives) * 2;    //each tender alternative needs 2 columns
        $totalColumnCount = $frontFixedColumnCount + $totalTenderAlternativesColumn + $rearFixedColumnCount;
        $totalColumnCountWithoutRearFixedColumns = $frontFixedColumnCount + $totalTenderAlternativesColumn;
        $rearFixedColumns = [];

        $endCol = $this->colItem;

        for($i = 2; $i <= $totalColumnCount; $i++)
        {
            $endCol ++;
        }

        return $endCol;
    }

    public function getTitleStyle($isSummary = true)
    {
        $titleStyle = parent::getTitleStyle();

        $titleStyle['fill'] = [
            'fillType' => Fill::FILL_SOLID,
            'color' => [
                'argb' => '4C4F53',
            ]
        ];

        $titleStyle['font'] = ['color' => ['argb' => Color::COLOR_WHITE]];

        return $titleStyle;
    }
}

