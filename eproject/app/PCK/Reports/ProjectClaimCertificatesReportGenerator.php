<?php namespace PCK\Reports;

use PCK\Buildspace\ClaimCertificate;
use PCK\Projects\Project;

class ProjectClaimCertificatesReportGenerator extends ReportGenerator {

    private $colSubsidiary                = 'B';
    private $colProjectTitle              = 'C';
    private $colContractor                = 'D';
    private $colSubContractWork           = 'E';
    private $colLetterOfAwardNumber       = 'F';
    private $colClaimNumber               = 'G';
    private $colCertificateDate           = 'H';
    private $colPeriodEnding              = 'I';
    private $colInvoiceNo                 = 'J';
    private $colInvoiceDate               = 'K';
    private $colWorkDone                  = 'L';
    private $colVariationOrder            = 'M';
    private $colRetention                 = 'N';
    private $colNetAmountCertified        = 'O';
    private $colProgressClaimTaxAmount    = 'P';
    private $colTotalAmountCertified      = 'Q';
    private $colWorkDonePercentage        = 'R';
    private $colAdvancePayment            = 'S';
    private $colDeposit                   = 'T';
    private $colMaterialOnSite            = 'U';
    private $colKongSiKong                = 'V';
    private $colMiscellaneousWorkOnBehalf = 'W';
    private $colMiscellaneousNetAmount    = 'X';
    private $colMiscellaneousTaxAmount    = 'Y';
    private $colMiscellaneousTotalAmount  = 'Z';
    private $colCreditDebitNote           = 'AA';
    private $colPurchaseOnBehalf          = 'AB';
    private $colOthersWorkOnBehalf        = 'AC';
    private $colPenalty                   = 'AD';
    private $colOthersNetAmount           = 'AE';
    private $colOthersTaxAmount           = 'AF';
    private $colOthersTotalAmount         = 'AG';
    private $colUtility                   = 'AH';
    private $colPermit                    = 'AI';
    private $colPaymentOnBehalfNetAmount  = 'AJ';
    private $colNetClaimAmount            = 'AK';

    private $projectCertificateIds = array();
    private $certificateInfo       = array();

    protected function setColumnWidths()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colSubsidiary}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colProjectTitle}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colContractor}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colSubContractWork}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colLetterOfAwardNumber}")->setWidth(13);
        $this->activeSheet->getColumnDimension("{$this->colClaimNumber}")->setWidth(13);
        $this->activeSheet->getColumnDimension("{$this->colCertificateDate}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colPeriodEnding}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colInvoiceNo}")->setWidth(13);
        $this->activeSheet->getColumnDimension("{$this->colInvoiceDate}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colWorkDone}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colVariationOrder}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colRetention}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colNetAmountCertified}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colProgressClaimTaxAmount}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colTotalAmountCertified}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colWorkDonePercentage}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colAdvancePayment}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colDeposit}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colMaterialOnSite}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colKongSiKong}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colMiscellaneousWorkOnBehalf}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colMiscellaneousNetAmount}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colMiscellaneousTaxAmount}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colMiscellaneousTotalAmount}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colCreditDebitNote}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colPurchaseOnBehalf}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colOthersWorkOnBehalf}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colPenalty}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colOthersNetAmount}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colOthersTaxAmount}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colOthersTotalAmount}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colUtility}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colPermit}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colPaymentOnBehalfNetAmount}")->setWidth(22);
        $this->activeSheet->getColumnDimension("{$this->colNetClaimAmount}")->setWidth(22);
    }

    protected function getClaimCertInformation(array $projectIds)
    {
        $includeAllProjectClaimCerts = count($projectIds) == 1;

        $projects = Project::whereIn('id', $projectIds)->orderBy('id', 'desc')->get();

        $certificateIds = array();

        foreach($projects as $project)
        {
            $approvedClaimCertificates = $project->getBsProjectMainInformation()->projectStructure->getApprovedClaimCertificates();

            if( $includeAllProjectClaimCerts )
            {
                $approvedClaimCertificates = $approvedClaimCertificates->reverse();

                foreach($approvedClaimCertificates as $claimCertificate)
                {
                    $this->projectCertificateIds[ $project->id ][] = $claimCertificate->id;
                    $certificateIds[]                              = $claimCertificate->id;
                }
            }
            elseif( $latestApprovedClaimCertificate = $approvedClaimCertificates->first() )
            {
                $this->projectCertificateIds[ $project->id ][] = $latestApprovedClaimCertificate->id;
                $certificateIds[]                              = $latestApprovedClaimCertificate->id;
            }
        }

        $this->certificateInfo = ClaimCertificate::getClaimCertInfo($certificateIds);
    }

    public function generate(array $projectIds)
    {
        $this->getClaimCertInformation($projectIds);

        $this->currentRow = 2;

        $this->addTitle();
        $this->createHeader();

        foreach($this->projectCertificateIds as $projectId => $projectCertificateIds)
        {
            $this->currentRow++;
            $this->activeSheet->getStyle("{$this->colSubsidiary}{$this->currentRow}:{$this->colNetClaimAmount}{$this->currentRow}")->applyFromArray($this->getItemRowStyle());

            foreach($projectCertificateIds as $certificateId)
            {
                $this->currentRow++;
                $this->activeSheet->getStyle("{$this->colSubsidiary}{$this->currentRow}:{$this->colNetClaimAmount}{$this->currentRow}")->applyFromArray($this->getItemRowStyle());

                $this->activeSheet->setCellValue("{$this->colSubsidiary}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['companyName']);
                $this->activeSheet->setCellValue("{$this->colProjectTitle}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['projectTitle']);
                $this->activeSheet->setCellValue("{$this->colContractor}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['contractorName']);
                $this->activeSheet->setCellValue("{$this->colSubContractWork}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['subPackageTitle']);
                $this->activeSheet->setCellValue("{$this->colLetterOfAwardNumber}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['letterOfAwardNo']);
                $this->activeSheet->setCellValue("{$this->colClaimNumber}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['claimNo']);
                $this->activeSheet->setCellValue("{$this->colCertificateDate}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['certificateDate']);
                $this->activeSheet->setCellValue("{$this->colPeriodEnding}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['periodEnding']);
                $this->activeSheet->setCellValue("{$this->colInvoiceNo}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['invoiceNo']);
                $this->activeSheet->setCellValue("{$this->colInvoiceDate}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['invoiceDate']);
                $this->setAmount("{$this->colWorkDone}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['billWorkDone']);
                $this->setAmount("{$this->colVariationOrder}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['voWorkDone']);
                $this->setAmount("{$this->colRetention}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['cumulativeRetentionSum']);
                $this->setAmount("{$this->colNetAmountCertified}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['amountCertified']);
                $this->setAmount("{$this->colProgressClaimTaxAmount}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['amountCertifiedTaxAmount']);
                $this->setAmount("{$this->colTotalAmountCertified}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['amountCertifiedIncludingTax']);
                $this->setAmount("{$this->colWorkDonePercentage}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['completionPercentage']);
                $this->setAmount("{$this->colAdvancePayment}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['advancePaymentThisClaim']);
                $this->setAmount("{$this->colDeposit}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['depositThisClaim']);
                $this->setAmount("{$this->colMaterialOnSite}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['materialOnSiteThisClaim']);
                $this->setAmount("{$this->colKongSiKong}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['kskThisClaim']);
                $this->setAmount("{$this->colMiscellaneousWorkOnBehalf}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['wobMCThisClaim']);
                $this->setAmount("{$this->colMiscellaneousNetAmount}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['miscThisClaimSubTotal']);
                $this->setAmount("{$this->colMiscellaneousTaxAmount}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['miscThisClaimAfterGSTSubTotal']);
                $this->setAmount("{$this->colMiscellaneousTotalAmount}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['miscThisClaimOverallTotal']);
                $this->setAmount("{$this->colCreditDebitNote}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['debitCreditNoteThisClaim']);
                $this->setAmount("{$this->colPurchaseOnBehalf}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['pobThisClaim']);
                $this->setAmount("{$this->colOthersWorkOnBehalf}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['wobThisClaim']);
                $this->setAmount("{$this->colPenalty}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['penaltyThisClaim']);
                $this->setAmount("{$this->colOthersNetAmount}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['otherThisClaimSubTotal']);
                $this->setAmount("{$this->colOthersTaxAmount}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['otherThisClaimAfterGSTSubTotal']);
                $this->setAmount("{$this->colOthersTotalAmount}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['otherThisClaimOverallTotal']);
                $this->setAmount("{$this->colUtility}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['waterDepositThisClaim']);
                $this->setAmount("{$this->colPermit}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['permitThisClaim']);
                $this->setAmount("{$this->colPaymentOnBehalfNetAmount}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['paymentOnBehalfThisClaimSubTotal']);
                $this->setAmount("{$this->colNetClaimAmount}{$this->currentRow}", $this->certificateInfo[ $certificateId ]['netPayableAmount']);
            }
        }

        $this->currentRow++;
        $this->activeSheet->getStyle("{$this->colSubsidiary}{$this->currentRow}:{$this->colNetClaimAmount}{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle());

        $this->setColumnWidths();

        return $this->output($this->spreadsheet, trans('finance.claimCertificatesReport'));
    }

    protected function addTitle()
    {
        $this->activeSheet->setCellValue("{$this->colSubsidiary}{$this->currentRow}", trans('finance.claimCertificatesReport'));
        $this->activeSheet->mergeCells("{$this->colSubsidiary}{$this->currentRow}:{$this->colNetClaimAmount}{$this->currentRow}");
        $this->activeSheet->getStyle("{$this->colSubsidiary}{$this->currentRow}")->applyFromArray($this->getTitleStyle());

        $this->currentRow++;
        $this->currentRow++;
    }

    protected function createHeader()
    {
        $headerStartRow = $this->currentRow;

        $this->addHeaderColumns(array(
            trans('subsidiaries.subsidiary'),
            trans('projects.title'),
            trans('finance.contractor'),
            trans('finance.subContractWork'),
            trans('finance.letterOfAwardNo'),
            trans('finance.progressClaimCertified') => array(
                trans('finance.claimNo'),
                trans('finance.certificateDate'),
                trans('finance.periodEnding'),
                trans('finance.invoiceNumber'),
                trans('finance.invoiceDate'),
                trans('finance.workDone'),
                trans('finance.variationOrder'),
                trans('finance.retention'),
                trans('finance.netAmountCertified'),
                trans('finance.taxAmount'),
                trans('finance.totalAmountCertified'),
                trans('finance.workDonePercentage'),
            ),
            trans('finance.miscellaneous')          => array(
                trans('finance.advancePayment'),
                trans('finance.deposit'),
                trans('finance.materialOnSite'),
                trans('finance.kongSiKong'),
                trans('finance.workOnBehalf'),
                trans('finance.netAmount'),
                trans('finance.taxAmount'),
                trans('finance.totalAmount'),
            ),
            trans('finance.others')                 => array(
                trans('finance.creditDebitNote'),
                trans('finance.purchaseOnBehalf'),
                trans('finance.workOnBehalf'),
                trans('finance.penalty'),
                trans('finance.netAmount'),
                trans('finance.taxAmount'),
                trans('finance.totalAmount'),
            ),
            trans('finance.paymentOnBehalf')        => array(
                trans('finance.utility'),
                trans('finance.permit'),
                trans('finance.netAmount'),
            ),
            trans('finance.netClaimAmount'),
        ), $this->colSubsidiary, $headerStartRow);

        $this->currentRow++;

        $this->activeSheet->mergeCells("{$this->colSubsidiary}{$headerStartRow}:{$this->colSubsidiary}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colProjectTitle}{$headerStartRow}:{$this->colProjectTitle}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colContractor}{$headerStartRow}:{$this->colContractor}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colSubContractWork}{$headerStartRow}:{$this->colSubContractWork}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colLetterOfAwardNumber}{$headerStartRow}:{$this->colLetterOfAwardNumber}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colNetClaimAmount}{$headerStartRow}:{$this->colNetClaimAmount}{$this->currentRow}");

        $this->activeSheet->getStyle("{$this->colSubsidiary}{$headerStartRow}:{$this->colNetClaimAmount}{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());
    }
}