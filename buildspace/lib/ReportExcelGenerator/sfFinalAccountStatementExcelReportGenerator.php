<?php

class sfFinalAccountStatementExcelReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colBorderLeft       = "B";
    public $colLabel            = "B";
    public $colDescription      = "C";
    public $colVoTotalOmission  = "C";
    public $colVoTotalAddition  = "D";
    public $colTotalAmount      = "F";
    public $colFirstItemColumn  = "B";
    public $colSecondItemColumn = "E";
    public $colBorderRight      = "F";

    const STATEMENT_OF_FINAL_ACCOUNT           = 'STATEMENT OF FINAL ACCOUNT';
    const MAIN_CONTRACTOR                      = 'MAIN CONTRACTOR';
    const JOB_TITLE                            = 'JOB TITLE';
    const ORIGINAL_CONTRACT_SUM                = 'ORIGINAL CONTRACT SUM';
    const VARIATION_ORDER                      = 'Variation Order';
    const TOTAL_OMISSION                       = 'Total Omission';
    const TOTAL_ADDITION                       = 'Total Addition';
    const AMOUNT                               = 'Amount';
    const FINAL_CONTRACT_SUM                   = 'FINAL CONTRACT SUM';
    const CLAUSE                               = "We, the undersigned, do hereby declare that we have checked the amount to the Final Contract Sum of @amount in respect of work's executed on the above Contract and confirm that we have no further claims under the Contract, Subject to any deduction the Employer is empowered to make under the Conditions of the Contract.";
    const DECLARATION                          = "We hereby declare that this Statement of Final Accounts in respect of works executed on the above Contract is agreed by us.";
    const NAME_OF_SIGNATORY                    = "Name of Signatory";
    const DATE                                 = "Date";
    const COMPANY_STAMP                        = "Company Stamp";
    const SIGNATURE_OF_WITNESS                 = "Signature of Witness";
    const NAME_OF_WITNESS                      = "Name of Witness";
    const CONTRACTOR                           = "Contractor";
    const ARCHITECT                            = "Architect";
    const MECHANICAL_AND_ELECTRICAL_CONSULTANT = "M&E Consultant";
    const QUANTITY_SURVEYOR                    = "Quantity Surveyor";
    const CIVIL_AND_STRUCTURAL_CONSULTANT      = "C&S Consultant";
    const EMPLOYER                             = "Employer";

    protected $title;
    protected $voTotalOmission;
    protected $voTotalAddition;
    protected $voTotalAmount;
    protected $originalContractSum;
    protected $finalContractSum;
    protected $contractorCompany;
    protected $currencyCode;
    protected $currencyName;
    protected $clause;

    function __construct($project = null, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : $project->title . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->project = $project;

        $this->calculateFinalAccountVariables();

        $this->startBillCounter();

        parent::__construct($project, $savePath, $filename, array());
    }

    protected function calculateFinalAccountVariables()
    {
        $this->currencyCode = $this->project->MainInformation->Currency->currency_code;
        $this->currencyName = $this->project->MainInformation->Currency->currency_name;

        $voOmission          = $this->project->getVariationOrderOmissionTotal();
        $voAddition          = $this->project->getVariationOrderAdditionTotal();
        $voTotalAmount       = $this->project->getVariationOrderOverallTotal();
        $originalContractSum = $this->project->PostContract->getContractSum();
        $finalContractSum    = $originalContractSum + $voTotalAmount;

        $this->title               = $this->project->MainInformation->getEProjectProject()->getLatestTender()->getFormOfTenderHeader();
        $this->voTotalOmission     = $this->currencyCode . " " . number_format($voOmission, 2);
        $this->voTotalAddition     = $this->currencyCode . " " . number_format($voAddition, 2);
        $this->voTotalAmount       = $this->currencyCode . " " . number_format($voTotalAmount, 2);
        $this->originalContractSum = $this->currencyCode . " " . number_format($originalContractSum, 2);
        $this->finalContractSum    = $this->currencyCode . " " . number_format($finalContractSum, 2);
        $this->contractorCompany   = ($contractor = $this->project->getSelectedContractor()) ? $contractor->name : '';

        $this->clause = self::CLAUSE;

        $clauseTextParts = explode('@amount', $this->clause);

        $numToTextConverter = new NumberToTextConverter();

        $objRichText = new PHPExcel_RichText();
        $objRichText->createText($clauseTextParts[0]);

        $objBold = $objRichText->createTextRun($numToTextConverter->customisedToCurrency($finalContractSum, $this->currencyName));
        $objBold->getFont()->setBold(true);

        $objRichText->createText($clauseTextParts[1]);

        $this->clause = $objRichText;
    }

    /**
     * Starts the bill counter.
     * This sets the first row and currentRow to the starting.
     * Also determines the first and last column.
     */
    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colLabel;
        $this->lastCol    = $this->colTotalAmount;
    }

    protected function setColumnDimensions()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colLabel)->setWidth(25);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(25);
        $this->activeSheet->getColumnDimension($this->colVoTotalAddition)->setWidth(25);
        $this->activeSheet->getColumnDimension($this->colSecondItemColumn)->setWidth(25);
        $this->activeSheet->getColumnDimension($this->colTotalAmount)->setWidth(25);
    }

    public function generateReport()
    {
        $this->setColumnDimensions();

        $this->activeSheet->setCellValue($this->colLabel . $this->currentRow, self::STATEMENT_OF_FINAL_ACCOUNT);
        $this->activeSheet->mergeCells($this->colLabel . $this->currentRow . ':' . $this->lastCol . $this->currentRow);

        $this->addLeftRightBorder();
        $this->activeSheet->getStyle($this->colLabel . $this->currentRow)->applyFromArray($this->getBoldStyle());
        $this->activeSheet->getStyle("{$this->colBorderLeft}{$this->currentRow}:{$this->colBorderRight}{$this->currentRow}")->applyFromArray($this->getTopBorderStyle());
        $this->activeSheet->getStyle("{$this->colBorderLeft}{$this->currentRow}:{$this->colBorderRight}{$this->currentRow}")->applyFromArray($this->getBottomBorderStyle());

        $this->currentRow++;
        $this->addLeftRightBorder();

        $this->activeSheet->setCellValue($this->colLabel . $this->currentRow, self::MAIN_CONTRACTOR);
        $this->activeSheet->getStyle($this->colLabel . $this->currentRow)->applyFromArray($this->getBoldLabelStyle());

        $this->activeSheet->mergeCells($this->colDescription . $this->currentRow . ':' . $this->lastCol . $this->currentRow);

        $this->currentRow++;
        $this->addLeftRightBorder();
        $this->currentRow++;
        $this->addLeftRightBorder();

        $this->activeSheet->setCellValue($this->colLabel . $this->currentRow, self::JOB_TITLE);
        $this->activeSheet->getStyle($this->colLabel . $this->currentRow)->applyFromArray($this->getBoldLabelStyle());

        $this->activeSheet->setCellValue($this->colDescription . $this->currentRow, $this->title);
        $this->activeSheet->mergeCells($this->colDescription . $this->currentRow . ':' . $this->lastCol . $this->currentRow);
        $this->activeSheet->getStyle($this->colDescription . $this->currentRow)->applyFromArray($this->getBoldUnderlineStyle());

        $this->currentRow++;
        $this->addLeftRightBorder();
        $this->currentRow++;
        $this->addLeftRightBorder();
        $this->activeSheet->setCellValue($this->colTotalAmount . $this->currentRow, self::AMOUNT . " ({$this->currencyCode})");
        $this->activeSheet->getStyle($this->colTotalAmount . $this->currentRow)->applyFromArray($this->getBoldUnderlineStyle());
        $this->activeSheet->getStyle($this->colTotalAmount . $this->currentRow)->applyFromArray($this->getLabelStyle());

        $this->currentRow++;
        $this->addLeftRightBorder();

        $this->activeSheet->setCellValue($this->colLabel . $this->currentRow, self::ORIGINAL_CONTRACT_SUM);
        $this->activeSheet->getStyle($this->colLabel . $this->currentRow)->applyFromArray($this->getBoldLabelStyle());

        $this->activeSheet->setCellValue($this->colTotalAmount . $this->currentRow, $this->originalContractSum);
        $this->activeSheet->getStyle($this->colTotalAmount . $this->currentRow)->applyFromArray($this->getLabelStyle());

        $this->currentRow++;
        $this->addLeftRightBorder();
        $this->currentRow++;
        $this->addLeftRightBorder();

        $this->activeSheet->setCellValue($this->colVoTotalOmission . $this->currentRow, self::TOTAL_OMISSION);
        $this->activeSheet->setCellValue($this->colVoTotalAddition . $this->currentRow, self::TOTAL_ADDITION);

        $this->activeSheet->getStyle($this->colVoTotalOmission . $this->currentRow)->applyFromArray($this->getBoldUnderlineStyle());
        $this->activeSheet->getStyle($this->colVoTotalOmission . $this->currentRow)->applyFromArray($this->getLabelStyle());
        $this->activeSheet->getStyle($this->colVoTotalAddition . $this->currentRow)->applyFromArray($this->getBoldUnderlineStyle());
        $this->activeSheet->getStyle($this->colVoTotalAddition . $this->currentRow)->applyFromArray($this->getLabelStyle());

        $this->currentRow++;
        $this->addLeftRightBorder();

        $this->activeSheet->setCellValue($this->colLabel . $this->currentRow, self::VARIATION_ORDER);
        $this->activeSheet->getStyle($this->colLabel . $this->currentRow)->applyFromArray($this->getBoldLabelStyle());

        $this->activeSheet->setCellValue($this->colVoTotalOmission . $this->currentRow, $this->voTotalOmission);
        $this->activeSheet->getStyle($this->colVoTotalOmission . $this->currentRow)->applyFromArray($this->getLabelStyle());

        $this->activeSheet->setCellValue($this->colVoTotalAddition . $this->currentRow, $this->voTotalAddition);
        $this->activeSheet->getStyle($this->colVoTotalAddition . $this->currentRow)->applyFromArray($this->getLabelStyle());

        $this->activeSheet->setCellValue($this->colTotalAmount . $this->currentRow, $this->voTotalAmount);
        $this->activeSheet->getStyle($this->colTotalAmount . $this->currentRow)->applyFromArray($this->getLabelStyle());

        $this->currentRow++;
        $this->addLeftRightBorder();

        $this->currentRow++;
        $this->addLeftRightBorder();

        $this->activeSheet->setCellValue($this->colLabel . $this->currentRow, self::FINAL_CONTRACT_SUM);
        $this->activeSheet->getStyle($this->colLabel . $this->currentRow)->applyFromArray($this->getBoldLabelStyle());

        $this->activeSheet->setCellValue($this->colTotalAmount . $this->currentRow, $this->finalContractSum);
        $this->activeSheet->getStyle($this->colTotalAmount . $this->currentRow)->applyFromArray($this->getLabelStyle());
        $this->activeSheet->getStyle($this->colTotalAmount . $this->currentRow)->applyFromArray($this->getDoubleTopBorderStyle());

        $this->currentRow++;
        $this->addLeftRightBorder();
        $this->currentRow++;
        $this->addLeftRightBorder();

        $this->activeSheet->setCellValue($this->colLabel . $this->currentRow, $this->clause);
        $this->activeSheet->mergeCells($this->colLabel . $this->currentRow . ':' . $this->lastCol . $this->currentRow);
        $this->activeSheet->getStyle($this->colLabel . $this->currentRow)->applyFromArray($this->getLabelStyle());
        $this->activeSheet->getStyle("{$this->colBorderLeft}{$this->currentRow}:{$this->colBorderRight}{$this->currentRow}")->applyFromArray($this->getTopBorderStyle());

        $this->currentRow++;
        $this->addLeftRightBorder();
        $this->currentRow++;
        $this->addLeftRightBorder();
        $this->currentRow++;
        $this->addLeftRightBorder();

        $signatoryRow = $this->currentRow;

        $this->activeSheet->setCellValue($this->colFirstItemColumn . $this->currentRow, $this->contractorCompany);
        $this->addSignatoryRows($this->colFirstItemColumn, $this->currentRow, self::CONTRACTOR);
        $this->currentRow = $this->addWitnessRows($this->colSecondItemColumn, $this->currentRow);

        while( $signatoryRow != $this->currentRow )
        {
            $signatoryRow++;
            $this->addLeftRightBorder($signatoryRow);
        }

        $this->currentRow++;
        $this->addLeftRightBorder();
        $this->activeSheet->getStyle("{$this->colBorderLeft}{$this->currentRow}:{$this->colBorderRight}{$this->currentRow}")->applyFromArray($this->getBottomBorderStyle());

        $this->currentRow++;
        $this->currentRow++;

        $this->activeSheet->setCellValue($this->colFirstItemColumn . $this->currentRow, self::DECLARATION);
        $this->activeSheet->mergeCells($this->colFirstItemColumn . $this->currentRow . ':' . $this->lastCol . $this->currentRow);

        $this->currentRow++;
        $this->currentRow++;

        $this->addSignatoryFields();

        $this->generateExcelFile();
    }

    protected function addSignatoryFields()
    {
        $this->addSignatoryRows($this->colFirstItemColumn, $this->currentRow, self::ARCHITECT);
        $this->currentRow = $this->addSignatoryRows($this->colSecondItemColumn, $this->currentRow, self::QUANTITY_SURVEYOR);

        $this->currentRow++;
        $this->currentRow++;

        $this->addSignatoryRows($this->colFirstItemColumn, $this->currentRow, self::MECHANICAL_AND_ELECTRICAL_CONSULTANT);
        $this->currentRow = $this->addSignatoryRows($this->colSecondItemColumn, $this->currentRow, self::CIVIL_AND_STRUCTURAL_CONSULTANT);

        $this->currentRow++;
        $this->currentRow++;

        $this->addSignatoryRows($this->colFirstItemColumn, $this->currentRow, self::EMPLOYER);
        $this->currentRow = $this->addSignatoryRows($this->colSecondItemColumn, $this->currentRow, self::EMPLOYER);

        $this->currentRow++;
        $this->currentRow++;

        $this->addSignatoryRows($this->colFirstItemColumn, $this->currentRow, self::EMPLOYER);
        $this->currentRow = $this->addSignatoryRows($this->colSecondItemColumn, $this->currentRow, self::EMPLOYER);

        $this->currentRow++;
        $this->currentRow++;

        $this->addSignatoryRows($this->colFirstItemColumn, $this->currentRow, self::EMPLOYER);
        $this->currentRow = $this->addSignatoryRows($this->colSecondItemColumn, $this->currentRow, self::EMPLOYER);
    }

    protected function addSignatoryRows($column, $startRow, $companyRole)
    {
        $currentRow = $startRow;

        $this->activeSheet->getStyle($column . $currentRow)->applyFromArray($this->getManualInputStyle());
        $currentRow++;
        $this->activeSheet->setCellValue($column . $currentRow, "({$companyRole})");
        $this->activeSheet->getStyle($column . $currentRow)->applyFromArray($this->getLabelStyle());
        $currentRow++;
        $this->activeSheet->setCellValue($column . $currentRow, self::NAME_OF_SIGNATORY);
        $this->activeSheet->getStyle($column . $currentRow)->applyFromArray($this->getLabelStyle());
        $this->activeSheet->getStyle($this->getNextColumn($column) . $currentRow)->applyFromArray($this->getManualInputStyle());
        $currentRow++;
        $this->activeSheet->setCellValue($column . $currentRow, self::DATE);
        $this->activeSheet->getStyle($column . $currentRow)->applyFromArray($this->getLabelStyle());
        $this->activeSheet->getStyle($this->getNextColumn($column) . $currentRow)->applyFromArray($this->getManualInputStyle());

        return $currentRow;
    }

    protected function addWitnessRows($column, $startRow)
    {
        $currentRow = $startRow;

        $this->activeSheet->setCellValue($column . $currentRow, self::COMPANY_STAMP);
        $this->activeSheet->getStyle($column . $currentRow)->applyFromArray($this->getLabelStyle());
        $this->activeSheet->getStyle($this->getNextColumn($column) . $currentRow)->applyFromArray($this->getManualInputStyle());
        $currentRow++;
        $this->activeSheet->setCellValue($column . $currentRow, self::SIGNATURE_OF_WITNESS);
        $this->activeSheet->getStyle($column . $currentRow)->applyFromArray($this->getLabelStyle());
        $this->activeSheet->getStyle($this->getNextColumn($column) . $currentRow)->applyFromArray($this->getManualInputStyle());
        $currentRow++;
        $this->activeSheet->setCellValue($column . $currentRow, self::NAME_OF_WITNESS);
        $this->activeSheet->getStyle($column . $currentRow)->applyFromArray($this->getLabelStyle());
        $this->activeSheet->getStyle($this->getNextColumn($column) . $currentRow)->applyFromArray($this->getManualInputStyle());
        $currentRow++;
        $this->activeSheet->setCellValue($column . $currentRow, self::DATE);
        $this->activeSheet->getStyle($column . $currentRow)->applyFromArray($this->getLabelStyle());
        $this->activeSheet->getStyle($this->getNextColumn($column) . $currentRow)->applyFromArray($this->getManualInputStyle());

        return $currentRow;
    }

    protected function getLabelStyle()
    {
        return array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        );
    }

    protected function getBoldStyle()
    {
        return array(
            'font'      => array(
                'bold' => true,
            ),
            'alignment' => array(
                'wrapText' => true
            )
        );
    }

    protected function getBoldLabelStyle()
    {
        return array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        );
    }

    protected function getBoldUnderlineStyle()
    {
        return array(
            'font'      => array(
                'bold'      => true,
                'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE,

            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'wrapText'   => true
            )
        );
    }

    protected function getManualInputStyle()
    {
        return array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DASHED,
                )
            )
        );
    }

    protected function getTopBorderStyle()
    {
        return array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK,
                )
            )
        );
    }

    protected function getBottomBorderStyle()
    {
        return array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK,
                )
            )
        );
    }

    protected function getDoubleTopBorderStyle()
    {
        return array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                )
            )
        );
    }

    protected function addLeftRightBorder($row = null)
    {
        if( ! $row ) $row = $this->currentRow;

        $this->activeSheet->getStyle($this->colBorderLeft . $row)->applyFromArray(array(
            'borders' => array(
                'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK,
                )
            )
        ));

        $this->activeSheet->getStyle($this->colBorderRight . $row)->applyFromArray(array(
            'borders' => array(
                'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK,
                )
            )
        ));
    }

}