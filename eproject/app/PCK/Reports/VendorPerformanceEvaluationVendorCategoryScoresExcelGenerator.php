<?php namespace PCK\Reports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PCK\Helpers\StringOperations;
use PCK\WeightedNode\WeightedNode;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\CycleScore;
use PCK\VendorCategory\VendorCategory;

class VendorPerformanceEvaluationVendorCategoryScoresExcelGenerator extends ReportGenerator {

    protected $colVendorName = 'B';
    protected $colScore = 'C';
    protected $colVendorGroup = 'D';
    protected $colVendorCategory = 'E';

    protected $colFirst = 'B';
    protected $currentColumn;
    protected $sheetTitle;
    protected $data = [];

    public function __construct()
    {
        parent::__construct();

        $this->sheetTitle = trans('vendorManagement.vendorPerformanceEvaluationVendorCategoryScores');

        $this->loadData();
    }

    protected function setColumnWidths()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colVendorName}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colScore}")->setWidth(8);
        $this->activeSheet->getColumnDimension("{$this->colVendorGroup}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorCategory}")->setWidth(45);
    }

    protected function loadData()
    {
        $data = [];

        $latestCycle = Cycle::getLatestCompletedCycle();

        $records = CycleScore::select('companies.name as vendor',
                'companies.id as company_id',
                'contract_group_categories.name as vendor_group',
                'vendor_categories.name as vendor_category',
                'vendor_categories.id as vendor_category_id',
                \DB::raw('ROUND(AVG(vendor_evaluation_cycle_scores.deliberated_score), 2) AS score')
            )
            ->join('companies', 'companies.id', '=', 'vendor_evaluation_cycle_scores.company_id')
            ->join('company_vendor_category', 'company_vendor_category.company_id', '=', 'companies.id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendor_evaluation_cycle_scores.vendor_work_category_id')
            ->join('vendor_category_vendor_work_category', 'vendor_category_vendor_work_category.vendor_work_category_id', '=', 'vendor_work_categories.id')
            ->join('vendor_categories', function($join){
                $join->on('vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id');
                $join->on('vendor_categories.id', '=', 'company_vendor_category.vendor_category_id');
            })
            ->where('vendor_performance_evaluation_cycle_id', '=', $latestCycle->id)
            ->groupBy('companies.name', 'companies.id', 'vendor_categories.name', 'vendor_categories.id', 'contract_group_categories.name')
            ->orderBy('companies.name')
            ->orderBy('vendor_categories.name')
            ->get();

        if($records->isEmpty()) return [];

        foreach($records as $record)
        {
            $this->data[] = [
                'vendor'          => $record->vendor,
                'vendor_group'    => $record->vendor_group,
                'vendor_category' => $record->vendor_category,
                'score'           => $record->score,
            ];
        }
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
            $this->activeSheet->setCellValue("{$this->colVendorName}{$this->currentRow}", $record['vendor']);
            $this->activeSheet->setCellValue("{$this->colScore}{$this->currentRow}", $record['score']);
            $this->activeSheet->setCellValue("{$this->colVendorGroup}{$this->currentRow}", $record['vendor_group']);
            $this->activeSheet->setCellValue("{$this->colVendorCategory}{$this->currentRow}", $record['vendor_category']);

            $rowPosition = 'middle';

            if($key === (count($this->data)-1)) $rowPosition = 'bottom';
            elseif($key === 0) $rowPosition = 'top';

            $this->activeSheet->getStyle("{$this->colVendorName}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorGroup}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorCategory}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));

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
        $this->activeSheet->setCellValue("B1", trans('vendorManagement.vendorPerformanceEvaluation'));

        $this->activeSheet->mergeCells("B1:E1");

        $this->activeSheet->setCellValue("B2", trans('vendorManagement.vendorCategoryScores'));

        $this->activeSheet->mergeCells("B2:E2");

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
        $this->activeSheet->setCellValue("{$this->colVendorName}{$this->currentRow}", trans('vendorManagement.vendor'));
        $this->activeSheet->setCellValue("{$this->colScore}{$this->currentRow}", trans('vendorManagement.score'));
        $this->activeSheet->setCellValue("{$this->colVendorGroup}{$this->currentRow}", trans('vendorManagement.vendorGroup'));
        $this->activeSheet->setCellValue("{$this->colVendorCategory}{$this->currentRow}", trans('vendorManagement.vendorCategory'));

        $this->activeSheet->getStyle("{$this->colVendorName}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colVendorGroup}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colVendorCategory}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());

        $this->currentRow++;
    }
}