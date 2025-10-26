<?php namespace PCK\Reports;

use Carbon\Carbon;
use PCK\Buildspace\ClaimCertificate;
use PCK\Buildspace\Project as ProjectStructure;
use PCK\Buildspace\ProjectCodeSetting;
use PCK\Buildspace\ItemCodeSetting;
use PCK\Buildspace\AccountCode;
use PCK\Buildspace\PostContractClaim;
use PCK\Buildspace\PostContractLetterOfAwardRententionSumModule;
use PCK\Buildspace\DebitCreditNoteClaim;
use PCK\Projects\Project;
use PCK\Subsidiaries\Subsidiary;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectsClaimCertificateDebitCreditNotesReport extends ReportGenerator
{
    const TITLE_LENGTH               = 25;

    private $colClaimNo              = 'B';
    private $colPeriodEnding         = 'C';
    private $colInvoiceNo            = 'D';
    private $colInvoiceDate          = 'E';
    private $colWorkDone             = 'F';
    private $colVariationOrder       = 'G';
    private $colRetention            = 'H';
    private $colNettAmountCertified  = 'I';
    private $colGST                  = 'J';
    private $colTotalAmountCertified = 'K';
    private $colDebitNote            = 'L';
    private $colCreditNote           = 'M';
    private $colCreditDebitNoteGST   = 'N';
    private $colNettAmountClaim      = 'O';
    private $colDate                 = 'P';
    private $colBank                 = 'Q';
    private $colReference            = 'R';
    private $colAmount               = 'S';
    private $colOutstanding          = 'T';

    private $dataByProject           = [];

    public function generate(array $projectIds)
    {
        $sheetIndex = 0;
        $projects = Project::whereIn('id', $projectIds)->orderBy('id', 'desc')->get();
        $this->populateDataByProject($projects);

        foreach($this->dataByProject as $projectId => $data)
        {
            $sheetTitle = (strlen($data['scope_of_work']) > self::TITLE_LENGTH) ? (substr($data['scope_of_work'], 0, self::TITLE_LENGTH) . '') : $data['scope_of_work'];

            if($sheetIndex == 0)
            {
                $this->activeSheet->setTitle($sheetTitle);
            }
            else
            {
                $newSheet = new Worksheet($this->spreadsheet, $sheetTitle."-".$sheetIndex);
                $this->spreadsheet->addSheet($newSheet);
                $this->spreadsheet->setActiveSheetIndex($sheetIndex);
                $this->activeSheet = $this->spreadsheet->getActiveSheet();
            }

            $this->populateTopSection($data);
            $this->createHeader($data['currency_code']);
            $this->setColumnWidths();

            ++ $this->currentRow;
            ++ $this->currentRow;

            $this->populateClaimCertificateTableRows(Project::find($projectId), $data['claim_certificates']);

            ++ $this->currentRow;

            $this->populateTotalsRow($data['totals']);

            ++ $sheetIndex;
        }

        $this->spreadsheet->setActiveSheetIndex(0);

        return $this->output($this->spreadsheet, trans('finance.claimCertCreditDebitNoteReport'));
    }

    private function populateTopSection($data)
    {
        $this->currentRow = 2;
        $col = 'B';
        $endCol = 'T';
        $nextAdjacentCol = 'D';
        $endNextAdjacentCol = 'T';

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", $data['root_subsidiary']);
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");

        ++ $this->currentRow;
        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.contractorsLedger'));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");

        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('general.asAt') . ' ' . Carbon::now()->format(\Config::get('dates.full_format_without_time_and_day')));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");

        ++ $this->currentRow;
        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", $data['awarded_contractor']);
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");

        ++ $this->currentRow;

        $endCol = 'C';

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.scopeOfWork'));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$nextAdjacentCol}{$this->currentRow}", $data['scope_of_work']);
        $this->activeSheet->mergeCells("{$nextAdjacentCol}{$this->currentRow}:{$endNextAdjacentCol}{$this->currentRow}");

        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.letterOfAward'));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$nextAdjacentCol}{$this->currentRow}", $data['letter_of_award_number']);
        $this->activeSheet->mergeCells("{$nextAdjacentCol}{$this->currentRow}:{$endNextAdjacentCol}{$this->currentRow}");

        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.dateOfCommencement'));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$nextAdjacentCol}{$this->currentRow}", $data['date_of_commencement']);
        $this->activeSheet->mergeCells("{$nextAdjacentCol}{$this->currentRow}:{$endNextAdjacentCol}{$this->currentRow}");

        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.dataOfCompletion'));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$nextAdjacentCol}{$this->currentRow}", $data['date_of_completion']);
        $this->activeSheet->mergeCells("{$nextAdjacentCol}{$this->currentRow}:{$endNextAdjacentCol}{$this->currentRow}");
        
        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.contractValue'));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$nextAdjacentCol}{$this->currentRow}",  $data['currency_code'] . ' ' . number_format($data['contract_value'], 2, '.', ','));
        $this->activeSheet->mergeCells("{$nextAdjacentCol}{$this->currentRow}:{$endNextAdjacentCol}{$this->currentRow}");

        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.limitOfRetentionSum') . '(' . ($data['retention_sum_limit_percentage'] + 0) . '%)');
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$nextAdjacentCol}{$this->currentRow}",  $data['currency_code'] . ' ' . number_format($data['retention_sum_limit'], 2, '.', ','));
        $this->activeSheet->mergeCells("{$nextAdjacentCol}{$this->currentRow}:{$endNextAdjacentCol}{$this->currentRow}");
        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.variationOrder'));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$nextAdjacentCol}{$this->currentRow}",  $data['currency_code'] . ' ' . number_format($data['variation_order'], 2, '.', ','));
        $this->activeSheet->mergeCells("{$nextAdjacentCol}{$this->currentRow}:{$endNextAdjacentCol}{$this->currentRow}");

        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.totalContractValue'));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");
        $this->activeSheet->setCellValue("{$nextAdjacentCol}{$this->currentRow}",  $data['currency_code'] . ' ' . number_format($data['total_contract_value'], 2, '.', ','));
        $this->activeSheet->mergeCells("{$nextAdjacentCol}{$this->currentRow}:{$endNextAdjacentCol}{$this->currentRow}");
        
        ++ $this->currentRow;

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.accountCodes'));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");

        foreach($data['item_codes'] as $itemCode)
        {
            $colCode = 'D';
            $endColCode = 'E';
            $tempCol = $nextAdjacentCol;

            $this->activeSheet->setCellValue("{$tempCol}{$this->currentRow}", $itemCode['code']);
            $this->activeSheet->mergeCells("{$colCode}{$this->currentRow}:{$endColCode}{$this->currentRow}");
            $this->activeSheet->getStyle("{$tempCol}:{$endColCode}")->getAlignment()->setHorizontal('left');

            $colDescription = 'F';
            $endColDescription = 'T';

            $this->activeSheet->setCellValue("{$colDescription}{$this->currentRow}", $itemCode['description']);
            $this->activeSheet->mergeCells("{$colDescription}{$this->currentRow}:{$endColDescription}{$this->currentRow}");

            ++ $this->currentRow;
        }

        $this->activeSheet->setCellValue("{$col}{$this->currentRow}", trans('finance.fsAllocation'));
        $this->activeSheet->mergeCells("{$col}{$this->currentRow}:{$endCol}{$this->currentRow}");

        foreach($data['fs_allocation'] as $allocation)
        {
            $tempCol = $nextAdjacentCol;
            $this->activeSheet->setCellValue("{$tempCol}{$this->currentRow}", $allocation);
            $this->activeSheet->mergeCells("{$nextAdjacentCol}{$this->currentRow}:{$endNextAdjacentCol}{$this->currentRow}");

            ++ $this->currentRow;
        }

        ++ $this->currentRow;
    }

    private function populateClaimCertificateTableRows(Project $project, $claimCertificateData)
    {
        $firstRow = $this->currentRow;

        foreach($claimCertificateData as $certData)
        {
            $this->activeSheet->setCellValue("{$this->colClaimNo}{$this->currentRow}", trans('finance.claim') . ' ' . trans('general.no') . $certData['claimRevision']);
            $this->activeSheet->setCellValue("{$this->colPeriodEnding}{$this->currentRow}", $certData['periodEnding']);
            $this->activeSheet->setCellValue("{$this->colInvoiceNo}{$this->currentRow}", $certData['invoiceNumber']);
            $this->activeSheet->setCellValue("{$this->colInvoiceDate}{$this->currentRow}", $certData['invoiceDate']);

            $this->setAmount("{$this->colWorkDone}{$this->currentRow}", $certData['currentTotalWorkDone']);
            $this->setAmount("{$this->colVariationOrder}{$this->currentRow}", $certData['currentVoWorkDone']);
            $this->setAmount("{$this->colRetention}{$this->currentRow}", $certData['currentRetentionSum']);
            $this->setAmount("{$this->colNettAmountCertified}{$this->currentRow}", $certData['nettAmountCertified']);

            $this->setAmount("{$this->colGST}{$this->currentRow}", $certData['amountCertifiedTaxAmount']);
            $this->setAmount("{$this->colTotalAmountCertified}{$this->currentRow}", $certData['amountCertifiedIncludingTax'], 2, '.', '');

            $this->setAmount("{$this->colDebitNote}{$this->currentRow}",$certData['currentTotalPDN']);
            $this->setAmount("{$this->colCreditNote}{$this->currentRow}", $certData['currentTotalPCN']);

            $this->setAmount("{$this->colCreditDebitNoteGST}{$this->currentRow}", $certData['otherThisClaimAfterGSTSubTotal']);
            $this->setAmount("{$this->colNettAmountClaim}{$this->currentRow}", $certData['netPayableAmountOverallTotal']);

            $this->setAmount("{$this->colOutstanding}{$this->currentRow}",$certData['balanceAmount']);

            if(empty($certData['paymentBreakdowns']))
            {
                $this->activeSheet->setCellValue("{$this->colBank}{$this->currentRow}", '-');
                $this->activeSheet->setCellValue("{$this->colReference}{$this->currentRow}", '-');
                $this->activeSheet->setCellValue("{$this->colDate}{$this->currentRow}", '-');
                $this->activeSheet->setCellValue("{$this->colAmount}{$this->currentRow}", '-');
            }
            else
            {
                $currRow = $this->currentRow;

                foreach($certData['paymentBreakdowns'] as $paymentBreakdown)
                {
                    $this->activeSheet->setCellValue("{$this->colBank}{$currRow}", $paymentBreakdown['bank']);
                    $this->activeSheet->setCellValue("{$this->colReference}{$currRow}", $paymentBreakdown['reference']);
                    $this->activeSheet->setCellValue("{$this->colDate}{$currRow}", Carbon::parse($project->getProjectTimeZoneTime($paymentBreakdown['date']))->format(\Config::get('dates.full_format_without_time_and_day')));
                    $this->setAmount("{$this->colAmount}{$currRow}", $paymentBreakdown['amount']);         
    
                    $currRow++;
                }

                $this->currentRow = ($currRow - 1);
            }

            $this->activeSheet->getStyle("{$this->colClaimNo}{$this->currentRow}:{$this->colOutstanding}{$this->currentRow}")->applyFromArray($this->rowBorderStyle());

            ++ $this->currentRow;
        }

        $stopAtRow = -- $this->currentRow;

        $this->activeSheet->getStyle("{$this->colClaimNo}{$firstRow}:{$this->colOutstanding}{$stopAtRow}")->applyFromArray($this->sidesBorderStyle());
    }

    private function populateTotalsRow($totals)
    {
        $this->setAmount("{$this->colWorkDone}{$this->currentRow}", $totals['totalWorkDoneWithoutVO']);
        $this->setAmount("{$this->colVariationOrder}{$this->currentRow}", $totals['totalVoWorkDone']);
        $this->setAmount("{$this->colRetention}{$this->currentRow}", $totals['totalRetentionSum']);
        $this->setAmount("{$this->colNettAmountCertified}{$this->currentRow}", $totals['totalNetAmountCertified']);
        $this->setAmount("{$this->colGST}{$this->currentRow}", $totals['totalAmountCertifiedTaxAmount']);
        $this->setAmount("{$this->colTotalAmountCertified}{$this->currentRow}", $totals['totalAmountCertifiedIncludingTax']);
        $this->setAmount("{$this->colDebitNote}{$this->currentRow}", $totals['totalDebitNote']);
        $this->setAmount("{$this->colCreditNote}{$this->currentRow}", $totals['totalCreditNote']);
        $this->setAmount("{$this->colCreditDebitNoteGST}{$this->currentRow}", $totals['totalOtherThisClaimAfterGSTSubTotal']);
        $this->setAmount("{$this->colNettAmountClaim}{$this->currentRow}", $totals['totalNetPayableAmountOverallTotal']);
        $this->setAmount("{$this->colAmount}{$this->currentRow}", $totals['totalPaidAmount']);

        $this->activeSheet->getStyle("{$this->colClaimNo}{$this->currentRow}:{$this->colOutstanding}{$this->currentRow}")->applyFromArray($this->sidesBorderStyle());
        $this->activeSheet->getStyle("{$this->colClaimNo}{$this->currentRow}:{$this->colOutstanding}{$this->currentRow}")->applyFromArray($this->rowBorderStyle());
        $this->activeSheet->getStyle("{$this->colClaimNo}{$this->currentRow}:{$this->colOutstanding}{$this->currentRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DFDFDF');
    }

    protected function createHeader($currencyCode)
    {
        $headerStartRow = $this->currentRow;

        $this->addHeaderColumns(array(
            trans('finance.progressClaimCertified') => array(
                trans('finance.progressClaim'),
                trans('finance.periodEnding'),
                trans('finance.invoiceNumber'),
                trans('finance.invoiceDate'),
                trans('finance.workDone') . '(' . $currencyCode . ')',
                trans('finance.variationOrder') . '(' . $currencyCode . ')',
                trans('finance.retention') . '(' . $currencyCode . ')',
                trans('finance.netAmountCertified') . '(' . $currencyCode . ')',
                trans('finance.taxAmount') . '(' . $currencyCode . ')',
                trans('finance.totalAmountCertified') . '(' . $currencyCode . ')',
                trans('finance.debitNote') . '(' . $currencyCode . ')',
                trans('finance.creditNote') . '(' . $currencyCode . ')',
                trans('finance.taxAmount') . '(' . $currencyCode . ')',
                trans('finance.netClaimAmount') . '(' . $currencyCode . ')',
            ),
            trans('finance.cheque') => array(
                trans('general.date'),
                trans('finance.pvNumber'),
                trans('finance.chequeNumber'),
                trans('finance.amount') . '(' . $currencyCode . ')',
            ),
            trans('general.balance') => array(
                trans('general.outstanding') . '(' . $currencyCode . ')',
            ),
        ), $this->colClaimNo, $headerStartRow);
    }

    protected function setColumnWidths()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colClaimNo}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colPeriodEnding}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colInvoiceNo}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colInvoiceDate}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colWorkDone}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colVariationOrder}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colRetention}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colNettAmountCertified}")->setWidth(30);
        $this->activeSheet->getColumnDimension("{$this->colGST}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colTotalAmountCertified}")->setWidth(30);
        $this->activeSheet->getColumnDimension("{$this->colDebitNote}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colCreditNote}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colCreditDebitNoteGST}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colNettAmountClaim}")->setWidth(25);
        $this->activeSheet->getColumnDimension("{$this->colDate}")->setWidth(40);
        $this->activeSheet->getColumnDimension("{$this->colBank}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colReference}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colAmount}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colOutstanding}")->setWidth(20);
    }

    private function populateDataByProject($projects)
    {
        foreach($projects as $project)
        {
            $approvedClaimCertificates     = $project->getBsProjectMainInformation()->projectStructure->getApprovedClaimCertificates()->reverse();
            $certificateIds                = $approvedClaimCertificates->lists('id');
            $claimCertificateInfo          = ClaimCertificate::getClaimCertInfo($certificateIds);Carbon::parse($project->getProjectTimeZoneTime($project->getBsProjectMainInformation()->projectStructure->letterOfAward->contract_period_from))->format(\Config::get('dates.full_format'));
            $maxRetentionSumPercentage     = $project->getBsProjectMainInformation()->projectStructure->letterOfAward->max_retention_sum;
            $includesVO                    = PostContractLetterOfAwardRententionSumModule::isIncluded($project->getBsProjectMainInformation()->projectStructure->letterOfAward->id, PostContractClaim::TYPE_VARIATION_ORDER);
            $finalApprovedClaimCertificate = $project->getBsProjectMainInformation()->projectStructure->getApprovedClaimCertificates()->first();
            
            $contractSum = 0;
            $contractValue = 0;
            $variationOrderTotal = 0;

            if($project->pam2006Detail)
            {
                $contractValue = $project->pam2006Detail->contract_sum;
                $contractSum = $contractValue;

                if($finalApprovedClaimCertificate && array_key_exists($finalApprovedClaimCertificate->id, $claimCertificateInfo))
                {
                    $variationOrderTotal = $claimCertificateInfo[$finalApprovedClaimCertificate->id]['voTotal'];
                    if($includesVO)
                    {
                        $contractSum = $contractSum + $variationOrderTotal;
                    }
                }
            }
            
            $retentionSumLimit             = $contractSum * ($maxRetentionSumPercentage / 100.0);
            $totalContractValue            = $contractSum;

            $awardedContractor = null;
            $this->dataByProject[$project->id]['awarded_contractor'] = null;
            if($project->latestTender && $awardedContractor = $project->latestTender->selectedFinalContractors()->wherePivot("selected_contractor", '=', true)->first())
            {
                $this->dataByProject[$project->id]['awarded_contractor'] = $awardedContractor->name;
            }

            $this->dataByProject[$project->id]['root_subsidiary']                = $project->subsidiary->parent ? $project->subsidiary->getParentsOfSubsidiary()[0]->name : $project->subsidiary->name;
            $this->dataByProject[$project->id]['scope_of_work']                  = $project->title;
            $this->dataByProject[$project->id]['letter_of_award_number']         = $project->getBsProjectMainInformation()->projectStructure->letterOfAward->code; 
            $this->dataByProject[$project->id]['date_of_commencement']           = Carbon::parse($project->getProjectTimeZoneTime($project->getBsProjectMainInformation()->projectStructure->letterOfAward->contract_period_from))->format(\Config::get('dates.full_format_without_time'));
            $this->dataByProject[$project->id]['date_of_completion']             = Carbon::parse($project->getProjectTimeZoneTime($project->getBsProjectMainInformation()->projectStructure->letterOfAward->contract_period_to))->format(\Config::get('dates.full_format_without_time'));
            $this->dataByProject[$project->id]['contract_value']                 = $contractValue;
            $this->dataByProject[$project->id]['retention_sum_limit_percentage'] = $maxRetentionSumPercentage;
            $this->dataByProject[$project->id]['retention_sum_limit']            = $retentionSumLimit;
            $this->dataByProject[$project->id]['variation_order']                = $variationOrderTotal;
            $this->dataByProject[$project->id]['total_contract_value']           = $totalContractValue;
            $this->dataByProject[$project->id]['fs_allocation']                  = $project->accountCodeSetting ? ($project->accountCodeSetting->isApproved() ? $this->constructSelectedPhaseSubsidiariesString($project->getBsProjectMainInformation()->projectStructure) : []) : [];
            $this->dataByProject[$project->id]['item_codes']                     = $project->accountCodeSetting ? ($project->accountCodeSetting->isApproved() ? $this->constructItemCodeString($project->getBsProjectMainInformation()->projectStructure) : []) : [];
            $this->dataByProject[$project->id]['currency_code']                  = $project->modified_currency_code;

            $claimCertificateData                = [];

            $totalWorkDoneWithoutVO              = 0.0;
            $totalVoWorkDone                     = 0.0;
            $totalRetentionSum                   = 0.0; 
            $totalNetAmountCertified             = 0.0;
            $totalAmountCertifiedTaxAmount       = 0.0;
            $totalAmountCertifiedIncludingTax    = 0.0;
            $totalDebitNote                      = 0.0;
            $totalCreditNote                     = 0.0;
            $totalOtherThisClaimAfterGSTSubTotal = 0.0;
            $totalNetPayableAmountOverallTotal   = 0.0;
            $totalPaidAmount                     = 0.0;

            foreach($approvedClaimCertificates as $claimCertificate)
            {
                $currentTotalWorkDoneWithoutVO = $claimCertificateInfo[$claimCertificate->id]['currentTotalWorkDone'] - $claimCertificateInfo[$claimCertificate->id]['currentVoWorkDone'];
                $totalCurrentPDN               = DebitCreditNoteClaim::getCreditDebitNoteTotalByType($claimCertificate->id, AccountCode::ACCOUNT_TYPE_PDN) ?? 0.0;
                $totalCurrentPCN               = DebitCreditNoteClaim::getCreditDebitNoteTotalByType($claimCertificate->id, AccountCode::ACCOUNT_TYPE_PCN) ?? 0.0;

                $totalWorkDoneWithoutVO              += $currentTotalWorkDoneWithoutVO;
                $totalVoWorkDone                     += $claimCertificateInfo[$claimCertificate->id]['currentVoWorkDone'];
                $totalRetentionSum                   += $claimCertificateInfo[$claimCertificate->id]['currentTotalRetention'];
                $totalNetAmountCertified             += $claimCertificateInfo[$claimCertificate->id]['amountCertified'];
                $totalAmountCertifiedTaxAmount       += $claimCertificateInfo[$claimCertificate->id]['amountCertifiedTaxAmount'];
                $totalAmountCertifiedIncludingTax    += $claimCertificateInfo[$claimCertificate->id]['amountCertifiedIncludingTax'];
                $totalDebitNote                      += $totalCurrentPDN;
                $totalCreditNote                     += $totalCurrentPCN;
                $totalOtherThisClaimAfterGSTSubTotal += $claimCertificateInfo[$claimCertificate->id]['otherThisClaimAfterGSTSubTotal'];
                $totalNetPayableAmountOverallTotal   += $claimCertificateInfo[$claimCertificate->id]['netPayableAmountOverallTotal'];
                $totalPaidAmount                     += $claimCertificate->paidAmount();

                array_push($claimCertificateData, [
                    'id'                             => $claimCertificate->id,
                    'claimRevision'                  => $claimCertificateInfo[$claimCertificate->id]['claimNo'],
                    'periodEnding'                   => $claimCertificateInfo[$claimCertificate->id]['periodEnding'],
                    'invoiceNumber'                  => $claimCertificate->claimCertificateInvoiceInformation ? $claimCertificate->claimCertificateInvoiceInformation->invoice_number : null,
                    'invoiceDate'                    => $claimCertificate->claimCertificateInvoiceInformation ? $claimCertificate->claimCertificateInvoiceInformation->invoice_date : null,
                    'currentTotalWorkDone'           => $currentTotalWorkDoneWithoutVO,
                    'currentVoWorkDone'              => $claimCertificateInfo[$claimCertificate->id]['currentVoWorkDone'],
                    'currentRetentionSum'            => $claimCertificateInfo[$claimCertificate->id]['currentTotalRetention'],
                    'nettAmountCertified'            => $claimCertificateInfo[$claimCertificate->id]['amountCertified'],
                    'amountCertifiedTaxAmount'       => $claimCertificateInfo[$claimCertificate->id]['amountCertifiedTaxAmount'],
                    'amountCertifiedIncludingTax'    => $claimCertificateInfo[$claimCertificate->id]['amountCertifiedIncludingTax'],
                    'currentTotalPDN'                => $totalCurrentPDN,
                    'currentTotalPCN'                => $totalCurrentPCN,
                    'otherThisClaimAfterGSTSubTotal' => $claimCertificateInfo[$claimCertificate->id]['otherThisClaimAfterGSTSubTotal'],
                    'netPayableAmountOverallTotal'   => $claimCertificateInfo[$claimCertificate->id]['netPayableAmountOverallTotal'],
                    'balanceAmount'                  => $totalContractValue - $totalNetAmountCertified,
                    'paymentBreakdowns'              => $claimCertificate->claimCertificatePayments->toArray(),
                ]);
            }

            $this->dataByProject[$project->id]['claim_certificates'] = $claimCertificateData;

            $this->dataByProject[$project->id]['totals']['totalWorkDoneWithoutVO'] = $totalWorkDoneWithoutVO;
            $this->dataByProject[$project->id]['totals']['totalVoWorkDone'] = $totalVoWorkDone;
            $this->dataByProject[$project->id]['totals']['totalRetentionSum'] = $totalRetentionSum;
            $this->dataByProject[$project->id]['totals']['totalNetAmountCertified'] = $totalNetAmountCertified;
            $this->dataByProject[$project->id]['totals']['totalAmountCertifiedTaxAmount'] = $totalAmountCertifiedTaxAmount;
            $this->dataByProject[$project->id]['totals']['totalAmountCertifiedIncludingTax'] = $totalAmountCertifiedIncludingTax;
            $this->dataByProject[$project->id]['totals']['totalDebitNote'] = $totalDebitNote;
            $this->dataByProject[$project->id]['totals']['totalCreditNote'] = $totalCreditNote;
            $this->dataByProject[$project->id]['totals']['totalOtherThisClaimAfterGSTSubTotal'] = $totalOtherThisClaimAfterGSTSubTotal;
            $this->dataByProject[$project->id]['totals']['totalNetPayableAmountOverallTotal'] = $totalNetPayableAmountOverallTotal;
            $this->dataByProject[$project->id]['totals']['totalPaidAmount'] = $totalPaidAmount;
        }
    }

    private function constructSelectedPhaseSubsidiariesString($projectStructure)
    {
        $projectCodeSettingPhaseSubsidiaries = ProjectCodeSetting::getSelectedSubsidiaries($projectStructure);

        if(empty($projectCodeSettingPhaseSubsidiaries)) return '';

        $subsidiaryIds = $projectCodeSettingPhaseSubsidiaries->lists('eproject_subsidiary_id');
        $sortedSubsidiaryIds = ProjectCodeSetting::getSortedSelectedProjectCodeSettingSubsidiary($subsidiaryIds);
        $subsidiaries = Subsidiary::whereIn('id', $sortedSubsidiaryIds)->get();

        return $subsidiaries->lists('name');
    }

    private function constructItemCodeString($projectStructure)
    {
        $itemCodeSettingIds = ItemCodeSetting::getItemCodeSettings($projectStructure)->lists('account_code_id');

        if(empty($itemCodeSettingIds)) return '';

        $accountCodes = AccountCode::whereIn('id', $itemCodeSettingIds)->get();
        $data         = [];

        foreach($accountCodes as $accountCode)
        {
            array_push($data, [
                'code' => $accountCode->code,
                'description' => $accountCode->description,
            ]);
        }

        return $data;
    }

    private function rowBorderStyle()
    {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
    }

    private function sidesBorderStyle()
    {
        return [
            'borders' => [
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
    }
}

