<?php

class sfSubPackageClaimReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colDescription      = "B";
    public $colClaimNo          = "C";
    public $colContractAmount   = "D";
    public $colVoAmount         = "E";
    public $colWorkDone         = "F";
    public $colVoWorkDone       = "G";
    public $colCertifiedAmount  = "H";
    public $colProfitPercent    = "I";
    public $colProfitWorkDone   = "J";
    public $colProfitVoWorkDone = "K";
    public $colProfitTotal      = "L";

    const COL_NAME_CLAIM_NO              = 'Claim No.';
    const COL_NAME_VO_AMOUNT             = 'VO Amount';
    const COL_NAME_VO_WORKDONE           = 'VO Work Done';
    const COL_NAME_CERTIFIED_AMOUNT      = 'Amount Certified';
    const COL_NAME_ACCUMULATIVE          = 'Accumulative';
    const COL_NAME_PROFIT_AND_ATTENDANCE = 'Profit & Attendance';

    protected $billHeader   = 'NSC Packages';
    protected $subtitle;
    protected $topLeftTitle;

    protected $subPackages = array();

    function __construct($project = null, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : $project->title . '-' . $this->subtitle . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->subtitle = "{$project->title}";

        parent::__construct($project, $savePath, $filename, array());
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colDescription;
        $this->lastCol    = $this->colProfitTotal;
    }

    protected function getRows()
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $subProjects = ProjectStructureTable::getSubProjects($this->project);

        $subProjectLatestApprovedClaimRevisions = SubProjectLatestApprovedClaimRevisionTable::getLatestApprovedSubProjectClaimRevision($this->project->PostContract->getCurrentSelectedClaimRevision());

        $profitAndAttendancePercentages = NominatedSubContractorInformationTable::getProfitAndAttendancePercentages($this->project);

        foreach($subProjects as $subProject)
        {
            if(!array_key_exists($subProject->id, $subProjectLatestApprovedClaimRevisions)) continue;

            $latestClaimRevision = $subProjectLatestApprovedClaimRevisions[$subProject->id];

            $claimCertificate = $latestClaimRevision->ClaimCertificate;

            if(!$claimCertificate->exists()) continue;

            $profitAndAttendancePercent = $profitAndAttendancePercentages[$subProject->id] ?? 0;

            $multiplier = Utilities::divide($profitAndAttendancePercent, 100);

            $this->subPackages[] = [
                 'description'                        => $subProject->title,
                 'claim_no'                           => $latestClaimRevision->version,
                 'contract_amount'                    => $subProject->PostContract->getContractSum(),
                 'vo_amount'                          => $subProject->NewPostContractFormInformation->getVoOverallTotal($latestClaimRevision),
                 'accumulative_work_done'             => $workDone = $latestClaimRevision->getWorkDone(),
                 'accumulative_vo_work_done'          => $voWorkDone = $subProject->NewPostContractFormInformation->getVOWorkDoneAmount($latestClaimRevision),
                 'amount_certified'                   => $claimCertificate->amount_certified,
                 'profit_and_attendance_percent'      => $profitAndAttendancePercent,
                 'profit_and_attendance_work_done'    => $profitAndAttendanceWorkDone = $workDone * $multiplier,
                 'profit_and_attendance_vo_work_done' => $profitAndAttendanceVariationOrderWorkDone = $voWorkDone * $multiplier,
                 'profit_and_attendance_total'        => $profitAndAttendanceWorkDone + $profitAndAttendanceVariationOrderWorkDone,
            ];
        }
    }

    protected function processRow($row)
    {
        $this->newLine();
        $this->newLine();

        $this->currentCol = $this->colDescription;
        $this->activeSheet->setCellValue($this->currentCol . $this->currentRow, $row['description']);
        $this->setItemStyle();

        parent::setRegularValue($this->colClaimNo, $row['claim_no']);
        parent::setValue($this->colContractAmount, self::getNonZeroValue($row['contract_amount']));
        parent::setValue($this->colVoAmount, self::getNonZeroValue($row['vo_amount']));
        parent::setValue($this->colWorkDone, self::getNonZeroValue($row['accumulative_work_done']));
        parent::setValue($this->colVoWorkDone, self::getNonZeroValue($row['accumulative_vo_work_done']));
        parent::setValue($this->colCertifiedAmount, self::getNonZeroValue($row['amount_certified']));
        parent::setPercentageValue($this->colProfitPercent, self::getNonZeroValue($row['profit_and_attendance_percent']));
        parent::setValue($this->colProfitWorkDone, self::getNonZeroValue($row['profit_and_attendance_work_done']));
        parent::setValue($this->colProfitVoWorkDone, self::getNonZeroValue($row['profit_and_attendance_vo_work_done']));
        parent::setValue($this->colProfitTotal, self::getNonZeroValue($row['profit_and_attendance_total']));

        $this->activeSheet->getStyle($this->colClaimNo . $this->currentRow)->applyFromArray($this->getUnitStyle());
    }

    protected function processRows()
    {
        $this->getRows();

        $this->itemType = null; // For description row styling.

        foreach($this->subPackages as $subPackage)
        {
            $this->processRow($subPackage);
        }

        $this->newLine(true);
    }

    public function generateReport()
    {
        $this->startBillCounter();
        $this->createHeader();
        $this->setBillHeader($this->billHeader, $this->topLeftTitle, $this->subtitle);
        $this->processRows();
        $this->generateExcelFile();
    }

    public function createHeader($new = false)
    {
        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);

        $this->currentRow++;
        $firstRow = $this->currentRow;

        $this->activeSheet->setCellValue($this->colDescription . $firstRow, self::COL_NAME_DESCRIPTION);

        $this->currentCol = $this->colDescription;

        $this->currentCol++;

        $headerInfo = $this->addHeaderColumns(
            array(
                SELf::COL_NAME_CLAIM_NO,
                SELf::COL_NAME_CONTRACT_AMOUNT,
                SELf::COL_NAME_VO_AMOUNT,
                SELf::COL_NAME_ACCUMULATIVE => array(
                    SELf::COL_NAME_WORKDONE,
                    SELf::COL_NAME_VO_WORKDONE,
                ),
                SELf::COL_NAME_CERTIFIED_AMOUNT,
                SELf::COL_NAME_PROFIT_AND_ATTENDANCE => array(
                    SELf::COL_NAME_PERCENT,
                    SELf::COL_NAME_WORKDONE,
                    SELf::COL_NAME_VO_WORKDONE,
                    SELf::COL_NAME_TOTAL,
                ),
            ), $this->currentCol, $firstRow);

        $this->currentRow++;
        $this->mergeRows($this->colDescription, $firstRow);
        $this->mergeRows($this->colClaimNo, $firstRow);
        $this->mergeRows($this->colContractAmount, $firstRow);
        $this->mergeRows($this->colVoAmount, $firstRow);
        $this->mergeRows($this->colCertifiedAmount, $firstRow);

        //Set header styling
        $this->activeSheet->getStyle($this->firstCol . $firstRow . ':' . $this->lastCol . $firstRow)->applyFromArray($this->getColumnHeaderStyle());

        // For merged header rows
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    }
}