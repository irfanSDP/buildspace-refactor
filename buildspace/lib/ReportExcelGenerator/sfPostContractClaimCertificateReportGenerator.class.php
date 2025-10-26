<?php

//require_once 'symfony.inc.php';

class sfPostContractClaimCertificateReportGenerator extends sfBuildspaceExcelReportGenerator
{
    protected $objPHPExcel;

    //For Fixed Column
    public $colCompanyName = 'A2';
    public $colCompanyAddress = 'A3';
    public $colCompanyTel = 'A5';
    public $colPersonInCharge = 'A7';
    public $colSubConWorks = 'A9';
    public $colRemark = 'A11';
    public $colProjectTitle = 'A12';
    public $colFax = 'D5';
    public $colProjectCode = 'F2';
    public $colReference = 'F3';
    public $colGSTRegistrationNo = 'F4';
    public $colDate = 'F5';
    public $colDueDate = 'F6';
    public $colClaimNo = 'F7';
    public $colFinal = 'F8';
    public $colPercentageCompletion = 'F9';
    public $colAccmPrevious = 'F10';
    public $colPreparedBy = 'F11';
    public $colWorksFromLA = 'F12';

    const COL_COMPANY_NAME = "Company Name :";
    const COL_COMPANY_ADDRESS = "Address :";
    const COL_COMPANY_TEL = "Tel :";
    const COL_PERSON_IN_CHARGE = "Person In Charge :";
    const COL_SUB_CON_WORKS = "Sub Con Works :";
    const COL_REMARK = "Remark :";
    const COL_PROJECT_TITLE = "Project Title :";
    const COL_FAX = "Fax :";
    const COL_PROJECT_CODE = "Project Code :";
    const COL_REFERENCE = "Reference :";
    const COL_GST_REGISTRATION_CODE = "GST Registration Code :";
    const COL_DATE = "Date :";
    const COL_DUE_DATE = "Due Date :";
    const COL_CLAIM_NO = "Claim No :";
    const COL_FINAL = "Final :";
    const COL_PERCENTAGE_COMPLETION = "Percentage Completion :";
    const COL_ACCM_PREVIOUS = "Accm Previous :";
    const COL_PREPARED_BY = "Prepared By :";
    const COL_WORKS_FROM_LA = "Works from LA :";

    // For Variable Column
    public $startRow = 14;
    public $currentRow;
    public $colItem = 'A';
    public $colContractSum = 'C';
    public $colPercentage = 'E';
    public $colWorkDone = 'F';
    public $colGST = 'G';
    public $colAmount = 'H';
    public $colContract = 'B';
    public $colBQSum = 'B';
    public $colVariationOrder = 'B';
    public $colTotal = 'B';
    public $colRetentionSum = 'B';
    public $colPreviousCertified = 'B';
    public $colReleaseRetentionAmount= 'B';
    public $colAmountCertified = 'B';
    public $colAccmTotal = 'C';
    public $colPreviousClaim = 'D';
    public $colSubTotal = 'E';
    public $colThisClaim = 'F';
    public $colMisc = 'B';
    public $colAdvancePayment = 'B';
    public $colDeposit = 'B';
    public $colMaterialOnSite = 'B';
    public $colKSK = 'B';
    public $colWOBMC= 'B';
    public $colPOB = 'B';
    public $colWOB = 'B';
    public $colPenalty = 'B';
    public $colPaymentOnBehalf = 'B';
    public $colWaterDeposit = 'B';
    public $colPermit = 'B';
    public $colOthers = 'B';

    function __construct(ClaimCertificate $claimCertificate)
    {
        $this->objPHPExcel = new sfPhpExcel();
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->setGlobalStyling();
        $this->protectSheet();

        $project = $claimCertificate->PostContractClaimRevision->PostContract->ProjectStructure;

        $newPostContractFormInformation = $project->NewPostContractFormInformation;

        $claimRevision = $claimCertificate->PostContractClaimRevision;

        $currencyCode = $project->MainInformation->Currency->currency_code;

        if($project->MainInformation->getEProjectProject()->parent_project_id)
        {
            $mainProjectTitle =  Doctrine_Core::getTable('EProjectProject')->find(intval($project->MainInformation->getEProjectProject()->parent_project_id))->title;

            $subPackageTitle = $project->MainInformation->getEProjectProject()->title;
        }
        else
        {
            $mainProjectTitle = $project->MainInformation->getEProjectProject()->title;
            $subPackageTitle = '';
        }

        $claimCertificateInfo = $claimCertificate->getClaimCertInfo();

        $data = $claimCertificate->toArray();

        $this->currencyCode 			      = $currencyCode;
        $this->companyName                    = $project->MainInformation->getEProjectProject()->Subsidiary->name;
        $this->contractorName	              = $project->TenderSetting->AwardedCompany->name;
        $this->contractorAddr                 = $project->TenderSetting->AwardedCompany->address;
        $this->contractorTel                  = $project->TenderSetting->AwardedCompany->phone_number;
        $this->fax                            = $project->TenderSetting->AwardedCompany->fax_number;
        $this->contractorPIC                  = $project->TenderSetting->AwardedCompany->contact_person_name;
        $this->claimNo                	      = (string)$claimCertificate->PostContractClaimRevision->version;
        $this->personInCharge                 = $data['person_in_charge'];
        $this->remark                         = $data['acc_remarks'];
        $this->subPackageTitle                = $subPackageTitle;
        $this->projectTitle                   = $mainProjectTitle;
        $this->projectCode                    = $project->MainInformation->getEProjectProject()->reference;
        $this->letterOfAwardNo                = (string)$newPostContractFormInformation->form_number;
        $this->reference					  = $newPostContractFormInformation->reference;
        $this->worksfromLA                    = NewPostContractFormInformationTable::getSubPackageWork($newPostContractFormInformation->id, 2)['name'];
        $this->billTotal 					  = (string) PostContractTable::getOverallTotalByProjectId($project->id);
        $this->voOverallTotal 				  = (string) $claimCertificate->getVariationOrderOverallTotalByClaimCertificate($project);
        $this->contractSum           		  = $this->billTotal + $this->voOverallTotal;
        $this->totalWorkDone                  = (string) $newPostContractFormInformation->getWorkDoneAmount($claimRevision);
        $this->billWorkDone                   = (string) $newPostContractFormInformation->getPostContractBillClaimWorkDoneAmount($claimRevision);
        $this->voWorkDone					  = (string) $newPostContractFormInformation->getVOWorkDoneAmount($claimRevision);
        $this->completionPercentage           = round(($this->totalWorkDone / $this->contractSum) * 100, 2). "%";
        $this->retentionSumByTax              = $newPostContractFormInformation->getRetentionSum($claimRevision);
        $this->releaseRetentionAmount         = round($data['release_retention_amount'], 2);
        $this->retentionTaxPercentage         = $data['retention_tax_percentage'];
        $this->previousCertified              = $claimCertificateInfo['cumulativePreviousAmountCertified'];
        $this->date                           = date("d/m/Y");
        $this->dueDate                        = date("d/m/Y", strtotime($data['due_date']));
        $this->taxPercentage                  = $data['tax_percentage'];

        $this->advancePaymentOverallTotal 	  = $claimCertificate->getPostContractClaimOverallTotalByClaimCertificate($project, PostContractClaim::TYPE_ADVANCED_PAYMENT);
        $this->depositOverallTotal 	  		  = $claimCertificate->getPostContractClaimOverallTotalByClaimCertificate($project, PostContractClaim::TYPE_DEPOSIT);
        $this->materialOnSiteOverallTotal 	  = $claimCertificate->getPostContractClaimOverallTotalByClaimCertificate($project, PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE);
        $this->kskOverallTotal 	  		      = $claimCertificate->getPostContractClaimOverallTotalByClaimCertificate($project, PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM);
        $this->wobMCOverallTotal 	 		  = $claimCertificate->getPostContractClaimOverallTotalByClaimCertificate($project, PostContractClaim::TYPE_WORK_ON_BEHALF);
        $this->pobOverallTotal 	  			  = $claimCertificate->getPostContractClaimOverallTotalByClaimCertificate($project, PostContractClaim::TYPE_PURCHASE_ON_BEHALF);
        $this->wobOverallTotal 	  			  = $claimCertificate->getPostContractClaimOverallTotalByClaimCertificate($project, PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE);
        $this->penaltyOverallTotal 	 		  = $claimCertificate->getPostContractClaimOverallTotalByClaimCertificate($project, PostContractClaim::TYPE_PENALTY);
        $this->waterDepositOverallTotal 	  = $claimCertificate->getPostContractClaimOverallTotalByClaimCertificate($project, PostContractClaim::TYPE_WATER_DEPOSIT);
        $this->permitOverallTotal 	  		  = $claimCertificate->getPostContractClaimOverallTotalByClaimCertificate($project, PostContractClaim::TYPE_PERMIT);

        $this->advancePaymentPreviousClaim 	  = $claimCertificate->getPostContractPreviousClaimTotalSecondLevel($project,PostContractClaim::TYPE_ADVANCED_PAYMENT);
        $this->depositPreviousClaim 		  = $claimCertificate->getPostContractPreviousClaimTotalSecondLevel($project,PostContractClaim::TYPE_DEPOSIT);
        $this->materialOnSitePreviousClaim 	  = $claimCertificate->getPostContractPreviousClaimTotalSecondLevel($project, PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE);
        $this->kskPreviousClaim 	  		  = $claimCertificate->getPostContractPreviousClaimTotalFirstLevel($project, PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM);
        $this->wobMCPreviousClaim 	  		  = $claimCertificate->getPostContractPreviousClaimTotalFirstLevel($project, PostContractClaim::TYPE_WORK_ON_BEHALF);
        $this->pobPreviousClaim 	  	      = $claimCertificate->getPostContractPreviousClaimTotalSecondLevel($project, PostContractClaim::TYPE_PURCHASE_ON_BEHALF);
        $this->wobPreviousClaim 	  		  = $claimCertificate->getPostContractPreviousClaimTotalFirstLevel($project, PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE);
        $this->penaltyPreviousClaim 	      = $claimCertificate->getPostContractPreviousClaimTotalFirstLevel($project, PostContractClaim::TYPE_PENALTY);
        $this->waterDepositPreviousClaim 	  = $claimCertificate->getPostContractPreviousClaimTotalFirstLevel($project, PostContractClaim::TYPE_WATER_DEPOSIT);
        $this->permitPreviousClaim 	  		  = $claimCertificate->getPostContractPreviousClaimTotalSecondLevel($project, PostContractClaim::TYPE_PERMIT);

        $this->advancePaymentThisClaim 		  = $claimCertificate->getPostContractClaimThisClaim($project,PostContractClaim::TYPE_ADVANCED_PAYMENT);
        $this->depositThisClaim 			  = $claimCertificate->getPostContractClaimThisClaim($project,PostContractClaim::TYPE_DEPOSIT);
        $this->materialOnSiteThisClaim 	  	  = $claimCertificate->getPostContractClaimThisClaim($project, PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE);
        $this->kskThisClaim 	  			  = $claimCertificate->getPostContractClaimThisClaimFirstLevel($project, PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM);
        $this->wobMCThisClaim 	  			  = $claimCertificate->getPostContractClaimThisClaimFirstLevel($project, PostContractClaim::TYPE_WORK_ON_BEHALF);
        $this->pobThisClaim 	  			  = $claimCertificate->getPostContractClaimThisClaimSecondLevel($project, PostContractClaim::TYPE_PURCHASE_ON_BEHALF);
        $this->wobThisClaim 	 		      = $claimCertificate->getPostContractClaimThisClaimFirstLevel($project, PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE);
        $this->penaltyThisClaim 	  		  = $claimCertificate->getPostContractClaimThisClaimFirstLevel($project, PostContractClaim::TYPE_PENALTY);
        $this->waterDepositThisClaim 	  	  = $claimCertificate->getPostContractClaimThisClaimFirstLevel($project, PostContractClaim::TYPE_WATER_DEPOSIT);
        $this->permitThisClaim 	  		      = $claimCertificate->getPostContractClaimThisClaim($project, PostContractClaim::TYPE_PERMIT);
    }

    public function setGlobalStyling()
    {
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    }

    private function generateCells()
    {
        $this->objPHPExcel->getActiveSheet()->setTitle('Claim Certificate');

        $this->objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
        $this->objPHPExcel->getActiveSheet()->mergeCells('F1:G1');

        $this->objPHPExcel->getActiveSheet()->setCellValue('A1', $this->companyName);
        $this->objPHPExcel->getActiveSheet()->setCellValue('F1', "Certificate of Payment");

        $this->objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($this->getColumnHeaderStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($this->getTopHeaderStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle('F1:G1')->applyFromArray($this->getColumnHeaderStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle('F1:G1')->applyFromArray($this->getTopHeaderStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle('A2:G12')->applyFromArray($this->getNewLineStyle());

        $this->objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colCompanyName, self::COL_COMPANY_NAME);
        $this->objPHPExcel->getActiveSheet()->setCellValue('B2', $this->contractorName);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colCompanyAddress, self::COL_COMPANY_ADDRESS);
        $this->objPHPExcel->getActiveSheet()->setCellValue('B3', $this->contractorAddr);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colCompanyTel, self::COL_COMPANY_TEL);
        $this->objPHPExcel->getActiveSheet()->setCellValue('B5', $this->contractorTel);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPersonInCharge, self::COL_PERSON_IN_CHARGE);
        $this->objPHPExcel->getActiveSheet()->setCellValue('B7', $this->contractorPIC);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colSubConWorks, self::COL_SUB_CON_WORKS);
        $this->objPHPExcel->getActiveSheet()->setCellValue('B9', $this->subPackageTitle);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colRemark, self::COL_REMARK);
        $this->objPHPExcel->getActiveSheet()->setCellValue('B11', $this->remark);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colProjectTitle, self::COL_PROJECT_TITLE);
        $this->objPHPExcel->getActiveSheet()->setCellValue('B12', $this->projectTitle);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colFax, self::COL_FAX);
        $this->objPHPExcel->getActiveSheet()->setCellValue('E5', $this->fax);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colProjectCode, self::COL_PROJECT_CODE);
        $this->objPHPExcel->getActiveSheet()->setCellValue('G2', $this->projectCode);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colReference, self::COL_REFERENCE);
        $this->objPHPExcel->getActiveSheet()->setCellValue('G3', $this->reference);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGSTRegistrationNo, self::COL_GST_REGISTRATION_CODE);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colDate, self::COL_DATE);
        $this->objPHPExcel->getActiveSheet()->setCellValue('G5', $this->date);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colDueDate, self::COL_DUE_DATE);
        $this->objPHPExcel->getActiveSheet()->setCellValue('G6', $this->dueDate);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colClaimNo, self::COL_CLAIM_NO);
        $this->objPHPExcel->getActiveSheet()->setCellValue('G7', $this->claimNo);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPercentageCompletion, self::COL_PERCENTAGE_COMPLETION);
        $this->objPHPExcel->getActiveSheet()->setCellValue('G9', $this->completionPercentage);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmPrevious, self::COL_ACCM_PREVIOUS);
        $this->objPHPExcel->getActiveSheet()->setCellValue('G10', $this->previousCertified);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreparedBy, self::COL_PREPARED_BY);
        $this->objPHPExcel->getActiveSheet()->setCellValue('G11', $this->personInCharge);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWorksFromLA, self::COL_WORKS_FROM_LA);
        $this->objPHPExcel->getActiveSheet()->setCellValue('G12', $this->worksfromLA);

        $this->createPostContractInformationHeader();
        $this->createPostContractInformationCells();
    }

    public function startCounter()
    {
        $this->currentRow = $this->startRow;
    }

    public function createPostContractInformationHeader()
    {
        $this->startCounter();

        $row = $this->currentRow;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colContractSum . $row, "Contract Sum (".$this->currencyCode.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPercentage . $row, "%");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWorkDone . $row, "Work Done (".$this->currencyCode.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "GST ".$this->taxPercentage." %");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAmount . $row, "Amount (".$this->currencyCode.")");

        $this->objPHPExcel->getActiveSheet()->getColumnDimension($this->colContractSum)->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getNewLineStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getColumnHeaderStyle());

        $this->currentRow ++;
    }

    public function createPostContractInformationCells()
    {
        $format = $this->getNumberFormatStandard();

        $row = $this->currentRow;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colItem . $row, "A");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colBQSum . $row, "Bill Total");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colContractSum . $row, $this->billTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWorkDone . $row, $this->billWorkDone);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colContractSum . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colWorkDone . $row)->getNumberFormat()->applyFromArray($format);
        $billTotalWorkDoneRow = $row;
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colVariationOrder . $row, "Variation Order");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colContractSum . $row, $this->voOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWorkDone . $row, $this->voWorkDone);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colContractSum . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colWorkDone . $row)->getNumberFormat()->applyFromArray($format);
        $variationOrderWorkDoneRow = $row;
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colTotal . $row, "Total");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colContractSum . $row,  "=". $this->colContractSum . $billTotalWorkDoneRow . "+" . $this->colContractSum . $variationOrderWorkDoneRow);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPercentage . $row,  "= ROUND(". $this->colWorkDone . $row . "/" . $this->colContractSum . $row . "* 100,2)");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWorkDone . $row, "=". $this->colWorkDone . $billTotalWorkDoneRow . "+" . $this->colWorkDone . $variationOrderWorkDoneRow);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colContractSum . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colWorkDone . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colGST . $row)->applyFromArray($this->getTotalStyle());
        $workDoneTotalRow = $row;
        $row += 2;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colRetentionSum . $row, "Retention Sum");

        $retentionSum = 0;

        foreach($this->retentionSumByTax as $tax => $amount)
        {
            $retentionSum += $amount;
        }

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWorkDone . $row, $retentionSum);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colWorkDone . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colGST . $row)->applyFromArray($this->getReductionStyle());
        $retentionSumRow = $row;
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colReleaseRetentionAmount . $row, "Release Retention");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWorkDone . $row, $this->releaseRetentionAmount . " ( " .$this->retentionTaxPercentage. "% ) ");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=(".$this->releaseRetentionAmount . "*".$this->retentionTaxPercentage."/100)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colWorkDone . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colGST . $row)->getNumberFormat()->applyFromArray($format);
        $releaseRetentionRow = $row;
        $row ++;

        $totalAmountRow = $row;

        $row += 2;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousCertified . $row, "Previous Certified");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWorkDone . $row, $this->previousCertified);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colWorkDone . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colWorkDone . $row)->applyFromArray($this->getReductionStyle());
        $previousCertifiedRow = $row;
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colTotal . $totalAmountRow, "Total Amount");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWorkDone . $totalAmountRow, "=". $this->colWorkDone . $workDoneTotalRow . "-" . $this->colWorkDone . $retentionSumRow ."-" .$this->colWorkDone . $previousCertifiedRow);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $totalAmountRow, "=(".$this->colThisClaim . $totalAmountRow . "*".$this->taxPercentage."/100)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $totalAmountRow. ":" .$this->colGST . $totalAmountRow)->applyFromArray($this->getTotalStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colWorkDone . $totalAmountRow)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colGST . $totalAmountRow)->getNumberFormat()->applyFromArray($format);

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAmountCertified . $row, "Amount Certified");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWorkDone . $row, "=". $this->colWorkDone . $workDoneTotalRow . "-" . $this->colWorkDone . $retentionSumRow ."-" .$this->colWorkDone . $previousCertifiedRow . "+" .$this->releaseRetentionAmount);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=SUM(".$this->colGST . $totalAmountRow.":".$this->colGST . $releaseRetentionRow.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAmount . $row, "=SUM(".$this->colWorkDone . $row.":".$this->colGST . $row.")");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getTotalStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colWorkDone . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colGST . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAmount . $row)->getNumberFormat()->applyFromArray($format);
        $amountCertifiedRow = $row;
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colItem . $row, "B");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colMisc . $row, "Misc");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, "Accm Total (".$this->currencyCode.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, "Previous Claim (".$this->currencyCode.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, "This Claim (".$this->currencyCode.")");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getNewLineStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getColumnHeaderStyle());
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAdvancePayment . $row, "Advance Payment");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, $this->advancePaymentOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, $this->advancePaymentPreviousClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, $this->advancePaymentThisClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=(".$this->colThisClaim . $row . "*".$this->taxPercentage."/100)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAccmTotal . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->applyFromArray($this->getReductionStyle());
        $advancePaymentRow = $row;
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colDeposit . $row, "Deposit");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, $this->depositOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, $this->depositPreviousClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, $this->depositThisClaim);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAccmTotal . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->applyFromArray($this->getReductionStyle());
        $row ++ ;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colMaterialOnSite . $row, "Material On Site");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, $this->materialOnSiteOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, $this->materialOnSitePreviousClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, $this->materialOnSiteThisClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=(".$this->colThisClaim . $row . "*".$this->taxPercentage."/100)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAccmTotal . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->applyFromArray($this->getReductionStyle());
        $row ++ ;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colKSK . $row, "KSK");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, $this->kskOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, $this->kskPreviousClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, $this->kskThisClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=(".$this->colThisClaim . $row . "*".$this->taxPercentage."/100)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAccmTotal . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $row ++ ;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWOBMC . $row, "WOB ( M/C )");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, $this->wobMCOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, $this->wobMCPreviousClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, $this->wobMCThisClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=(".$this->colThisClaim . $row . "*".$this->taxPercentage."/100)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAccmTotal . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $wobRow = $row;
        $row ++ ;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colSubTotal . $row, "Sub Total (".$this->currencyCode.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, "=SUM(".$this->colThisClaim . $advancePaymentRow.":".$this->colThisClaim . $wobRow.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=SUM(".$this->colGST . $advancePaymentRow.":".$this->colGST . $wobRow.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAmount . $row, "=SUM(".$this->colThisClaim . $row.":".$this->colGST . $row.")");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colGST . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAmount . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPercentage . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getTotalStyle());
        $progressClaimSubTotalRow = $row;
        $row ++ ;

        $this->objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':'.'E'.$row);
        $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$row, "Tax Invoice By Sub Contractor");

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, "=ROUND(SUM(". $this->colThisClaim . $amountCertifiedRow .",".$this->colThisClaim . $progressClaimSubTotalRow ."),2)");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=ROUND(SUM(".$this->colGST . $amountCertifiedRow.",".$this->colGST . $progressClaimSubTotalRow."),2)");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAmount . $row, "=ROUND(SUM(".$this->colThisClaim . $row.":".$this->colGST . $row."),2)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colGST . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAmount . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle('A'.$row.':'.'H'.$row)->applyFromArray($this->getColumnHeaderStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle('A'.$row.':'.'H'.$row)->applyFromArray($this->getTopHeaderStyle());
        $subConTaxInvoiceRow = $row;
        $row ++ ;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colItem . $row, "C");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colOthers . $row, "Others");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getNewLineStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getColumnHeaderStyle());
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPOB . $row, "POB");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, $this->pobOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, $this->pobPreviousClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, $this->pobThisClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=(".$this->colThisClaim . $row . "*".$this->taxPercentage."/100)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAccmTotal . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->applyFromArray($this->getReductionStyle());
        $pobRow = $row;
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWOB . $row, "WOB");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, $this->wobOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, $this->wobPreviousClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, $this->wobThisClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=(".$this->colThisClaim . $row . "*".$this->taxPercentage."/100)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAccmTotal . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPenalty . $row, "Penalty");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, $this->penaltyOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, $this->penaltyPreviousClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, $this->penaltyThisClaim);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAccmTotal . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $penaltyRow = $row;
        $row ++;

        $this->objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':'.'E'.$row);
        $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$row, "Tax Invoice By ".$this->companyName);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, "=SUM(".$this->colThisClaim . $pobRow.":".$this->colThisClaim . $penaltyRow.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=ROUND(SUM(".$this->colGST . $pobRow.":".$this->colGST . $penaltyRow."),2)");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAmount . $row, "=ROUND(SUM(".$this->colThisClaim . $row.":".$this->colGST . $row."),2)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colGST . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAmount . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPercentage . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getTotalStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle('A'.$row.':'.'H'.$row)->applyFromArray($this->getColumnHeaderStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle('A'.$row.':'.'H'.$row)->applyFromArray($this->getTopHeaderStyle());
        $taxInvoiceMetrioRow = $row;
        $row ++ ;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colItem . $row, "D");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPaymentOnBehalf . $row, "Payment On Behalf");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getNewLineStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getColumnHeaderStyle());
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colWaterDeposit . $row, "Utility");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, $this->waterDepositOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, $this->waterDepositPreviousClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, $this->waterDepositThisClaim);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAccmTotal . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $waterDepositRow = $row;
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPermit . $row, "Permit");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccmTotal . $row, $this->permitOverallTotal);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colPreviousClaim . $row, $this->permitPreviousClaim);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, $this->permitThisClaim);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAccmTotal . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPreviousClaim . $row)->applyFromArray($this->getReductionStyle());
        $permitRow = $row;
        $row ++;

        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colSubTotal . $row, "Sub Total (".$this->currencyCode.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, "=SUM(".$this->colThisClaim . $waterDepositRow.":".$this->colThisClaim . $permitRow.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=SUM(".$this->colGST . $waterDepositRow.":".$this->colGST . $permitRow.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAmount . $row, "=SUM(".$this->colThisClaim . $row.":".$this->colGST . $row.")");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colGST . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAmount . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colPercentage . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getTotalStyle());
        $paymentOnBehalfSubTotalRow = $row;
        $row ++ ;

        // $this->objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "%");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('B' . $row, "Cert");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('C' . $row, "Misc");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('D' . $row, "OTH");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('E' . $row, "POB");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('F' . $row, "Total");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('G' . $row, "GST");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('H' . $row, "Total (Incl GST)");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colSubTotal . $row, "Net Payable Amount (".$this->currencyCode.")");
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colThisClaim . $row, "=". $this->colThisClaim . $subConTaxInvoiceRow . "-" . $this->colThisClaim . $taxInvoiceMetrioRow . "+" .$this->colThisClaim . $paymentOnBehalfSubTotalRow);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colGST . $row, "=". $this->colGST . $subConTaxInvoiceRow . "-" . $this->colGST . $taxInvoiceMetrioRow . "+" .$this->colGST . $paymentOnBehalfSubTotalRow);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAmount . $row, "=ROUND(SUM(".$this->colThisClaim . $row.":".$this->colGST . $row."),2)");
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colThisClaim . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colGST . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colAmount . $row)->getNumberFormat()->applyFromArray($format);
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getNewLineStyle());
        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $row. ":" .$this->colAmount . $row)->applyFromArray($this->getColumnHeaderStyle());
        $row ++;

        $this->objPHPExcel->getActiveSheet()->getStyle($this->colItem . $this->startRow. ":" .$this->colAmount . $row)->applyFromArray($this->getNewLineStyle());
        $row += 2;

        // $this->objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Cert No");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('B' . $row, "Retention Sum");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('C' . $row, "Amount Certified");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('D' . $row, "Advance Payment");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('E' . $row, "Deposit");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('F' . $row, "Material On Site");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('G' . $row, "KSK");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('H' . $row, "WOB ( M/C )");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('I' . $row, "POB ( Pur )");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('J' . $row, "WOB ( S/C )");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('K' . $row, "Penalty");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('L' . $row, "Payment On Behalf");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('M' . $row, "Recommended Amount");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('N' . $row, "Cheque Amount");
        // $this->objPHPExcel->getActiveSheet()->setCellValue('O' . $row, "Payment Date");

        // $this->objPHPExcel->getActiveSheet()->getStyle("A" . $row. ":O" . $row)->applyFromArray($this->getNewLineStyle());
        // $this->objPHPExcel->getActiveSheet()->getStyle("A" . $row. ":O" . $row)->applyFromArray($this->getColumnHeaderStyle());
        // $row ++;

        // $this->objPHPExcel->getActiveSheet()->getStyle("A" . $row. ":O" . $row)->applyFromArray($this->getNewLineStyle());
        // $row ++;

        $this->currentRow = $row;
    }

    public function getNumberFormatStandard()
    {
        if ( !$this->withoutCents )
        {
            $format = array(
                'code' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
        }
        else
        {
            $format = array(
                'code' => '#,##0'
            );
        }

        return $format;
    }

    public function protectSheet()
    {
        $this->objPHPExcel->getActiveSheet()->getProtection()->setSheet($this->lock);
        $this->objPHPExcel->getActiveSheet()->getProtection()->setPassword("Buildspace");
    }

    public function getColumnHeaderStyle()
    {
        $columnHeadStyle = array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical'   => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )

        );

        return $columnHeadStyle;
    }

    public function getTotalStyle()
    {
        $totalStyle = array(
            'font'      => array(
                'bold' => true
            ),
            'fill'      => array(
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array( 'rgb' => 'E4D4A3' )
            ),
            'font'      => array(
                'bold'  => true,
                'color' => array( 'rgb' => '000000' )
            )
        );

        return $totalStyle;
    }

    public function getReductionStyle()
    {
        $style = array(
            'font'      => array(
                'bold' => true
            ),
            'font'      => array(
                'bold'  => true,
                'color' => array( 'rgb' => 'FF0000' )
            )
        );

        return $style;
    }

    public function getNewLineStyle($bottom = false)
    {
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
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'bottom'   => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                )
            )
        );

        if ( $bottom )
        {
            $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
            $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );
        }

        return $newLineStyle;
    }

    public function getTopHeaderStyle()
    {
        $style = array(
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
            ),
            'fill'      => array(
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array( 'rgb' => 'A3E4D7' )
            ),
            'font'      => array(
                'bold'  => true,
                'color' => array( 'rgb' => '000000' )
            )
        );

        return $style;
    }

    public function write()
    {
        $this->generateCells();

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $tmpName = md5(date('dmYHis'));
        $tmpFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.$tmpName;

        $objWriter->setPreCalculateFormulas();
        $objWriter->save($tmpFile);

        unset($this->objPHPExcel);

        return $tmpFile;
    }
}
