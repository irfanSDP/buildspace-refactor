<?php namespace PCK\Reports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VendorPerformanceEvaluationSetupsGenerator extends ReportGenerator {

    protected $colReference       = 'B';
    protected $colProjectTitle    = 'C';
    protected $colBusinessUnit    = 'D';
    protected $colProjectStage    = 'E';
    protected $colStartDate       = 'F';
    protected $colEndDate         = 'G';
    protected $colStatus          = 'H';
    protected $colAssignedVendors = 'I';

    protected $colFirst = 'B';
    protected $currentColumn;
    protected $sheetTitle;
    protected $data = [];
    protected $cycle;

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setCycle($cycle)
    {
        $this->cycle = $cycle;

        $this->sheetTitle = $cycle->remarks;

        if(empty($cycle->remarks)) $this->sheetTitle = trans('vendorManagement.vendorPerformanceEvaluationCycle');
    }

    protected function setColumnWidths()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colReference}")->setWidth(30);
        $this->activeSheet->getColumnDimension("{$this->colProjectTitle}")->setWidth(50);
        $this->activeSheet->getColumnDimension("{$this->colBusinessUnit}")->setWidth(30);
        $this->activeSheet->getColumnDimension("{$this->colProjectStage}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colStartDate}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colEndDate}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colStatus}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colAssignedVendors}")->setWidth(20);
    }

    public function generate()
    {
        $this->generateTitle();

        $this->generateHeaders();

        $this->process();

        return $this->output($this->spreadsheet, $this->sheetTitle);
    }

    protected function process()
    {
        $this->setColumnWidths();

        foreach($this->data as $key => $record)
        {
            $this->activeSheet->setCellValue("{$this->colReference}{$this->currentRow}", $record->reference);
            $this->activeSheet->setCellValue("{$this->colProjectTitle}{$this->currentRow}", $record->title);
            $this->activeSheet->setCellValue("{$this->colBusinessUnit}{$this->currentRow}", $record->business_unit);
            $this->activeSheet->setCellValue("{$this->colProjectStage}{$this->currentRow}", $record->project_stage);
            $this->activeSheet->setCellValue("{$this->colStartDate}{$this->currentRow}", $record->start_date);
            $this->activeSheet->setCellValue("{$this->colEndDate}{$this->currentRow}", $record->end_date);
            $this->activeSheet->setCellValue("{$this->colStatus}{$this->currentRow}", $record->status);
            $this->activeSheet->setCellValue("{$this->colAssignedVendors}{$this->currentRow}", "{$record->assigned_company_number}/{$record->total_company_number}");

            $rowPosition = 'middle';

            if($key === (count($this->data)-1)) $rowPosition = 'bottom';
            elseif($key === 0) $rowPosition = 'top';

            $this->activeSheet->getStyle("{$this->colReference}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colProjectTitle}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colBusinessUnit}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colProjectStage}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colStartDate}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colEndDate}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colStatus}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colAssignedVendors}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));

            $this->currentRow++;
        }
    }

    protected function getHeaderStyle()
    {
        return array(
            'font'      => array(
                'bold' => true
            ),
            'borders'   => array(
                'allBorders' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        );
    }

    protected function getRowStyle($position = 'middle')
    {
        $style = array(
            'borders'   => array(
                'left' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
                'right' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        );

        if($position == 'top')
        {
            $style['borders']['top'] = array(
                'borderStyle' => Border::BORDER_THIN,
                'color'       => array( 'argb' => '000000' ),
            );
        }
        elseif($position == 'bottom')
        {
            $style['borders']['bottom'] = array(
                'borderStyle' => Border::BORDER_THIN,
                'color'       => array( 'argb' => '000000' ),
            );
        }

        return $style;
    }

    public function generateTitle()
    {
        $this->activeSheet->setCellValue("B1", trans('vendorManagement.vendorPerformanceEvaluations'));

        $this->activeSheet->mergeCells("B1:I1");

        $this->activeSheet->setCellValue("B2", $this->sheetTitle);

        $this->activeSheet->mergeCells("B2:I2");

        $this->activeSheet->getStyle("B1:B2")->applyFromArray(array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        ));

        $this->currentRow = 4;
    }

    public function generateHeaders()
    {
        $headerFirstRow = $this->currentRow;

        $this->addHeaderColumns([
            trans('projects.reference'),
            trans('projects.title'),
            trans('projects.businessUnit'),
            trans('vendorManagement.projectStage'),
            trans('vendorManagement.startDate'),
            trans('vendorManagement.endDate'),
            trans('vendorManagement.status'),
            trans('vendorManagement.assignedVendors'),
        ], $this->colFirst, $this->currentRow);

        $this->currentRow++;
    }
}