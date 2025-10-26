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

class VendorPerformanceEvaluationVendorWorkCategoryScoresExcelGenerator extends ReportGenerator {

    protected $colVendorName = 'B';
    protected $colScore = 'C';
    protected $colVendorGroup = 'D';
    protected $colVendorCategory = 'E';
    protected $colVendorWorkCategory = 'F';

    protected $colFirst = 'B';
    protected $currentColumn;
    protected $sheetTitle;
    protected $data = [];

    public function __construct()
    {
        parent::__construct();

        $this->sheetTitle = trans('vendorManagement.vendorPerformanceEvaluationVendorWorkCategoryScores');

        $this->loadData();
    }

    protected function setColumnWidths()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colVendorName}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colScore}")->setWidth(8);
        $this->activeSheet->getColumnDimension("{$this->colVendorGroup}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorCategory}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorWorkCategory}")->setWidth(45);
    }

    protected function loadData()
    {
        $data = [];

        $latestCycle = Cycle::getLatestCompletedCycle();

        $records = CycleScore::select('companies.name as vendor',
                'companies.id as company_id',
                'contract_group_categories.name as vendor_group',
                'vendor_evaluation_cycle_scores.deliberated_score as score',
                'vendor_work_categories.name as vendor_work_category',
                'vendor_work_categories.id as vendor_work_category_id'
            )
            ->join('companies', 'companies.id', '=', 'vendor_evaluation_cycle_scores.company_id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendor_evaluation_cycle_scores.vendor_work_category_id')
            ->where('vendor_performance_evaluation_cycle_id', '=', $latestCycle->id)
            ->orderBy('companies.name')
            ->orderBy('vendor_work_categories.name')
            ->get();

        if($records->isEmpty()) return [];

        $companyIds = $records->lists('company_id');

        $vendorCategories = new \Illuminate\Database\Eloquent\Collection(\DB::select('SELECT * FROM company_vendor_category WHERE company_id IN ('. implode(',', $companyIds) .')'));

        $vendorCategories = $vendorCategories->groupBy('company_id')->toArray();

        $companyVendorCategoryIds = [];

        foreach($vendorCategories as $companyId => $vendorCategoryRecords)
        {
            $companyVendorCategoryIds[$companyId] = [];

            foreach($vendorCategoryRecords as $vendorCategoryRecord)
            {
                $companyVendorCategoryIds[$companyId][] = $vendorCategoryRecord->vendor_category_id;
            }
        }

        $vendorCategoriesByVendorWorkCategoryId = VendorCategory::select('vendor_categories.id as vendor_category_id', 'vendor_category_vendor_work_category.vendor_work_category_id', 'vendor_categories.name')
            ->join('vendor_category_vendor_work_category', 'vendor_category_vendor_work_category.vendor_category_id', '=', 'vendor_categories.id')
            ->whereIn('vendor_category_vendor_work_category.vendor_work_category_id', $records->lists('vendor_work_category_id'))
            ->orderBy('vendor_categories.name')
            ->get()
            ->groupBy('vendor_work_category_id');

        foreach($records as $record)
        {
            $companyVendorCategories = [];

            foreach($vendorCategoriesByVendorWorkCategoryId[$record->vendor_work_category_id] as $vendorCategory)
            {
                if(in_array($vendorCategory->vendor_category_id, $companyVendorCategoryIds[$record->company_id] ?? []))
                {
                    $companyVendorCategories[] = $vendorCategory->name;
                }
            }

            $this->data[] = [
                'vendor'               => $record->vendor,
                'vendor_group'         => $record->vendor_group,
                'vendor_work_category' => $record->vendor_work_category,
                'vendor_categories'    => implode(', ', $companyVendorCategories),
                'score'                => $record->score,
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
            $this->activeSheet->setCellValue("{$this->colVendorCategory}{$this->currentRow}", $record['vendor_categories']);
            $this->activeSheet->setCellValue("{$this->colVendorWorkCategory}{$this->currentRow}", $record['vendor_work_category']);

            $rowPosition = 'middle';

            if($key === (count($this->data)-1)) $rowPosition = 'bottom';
            elseif($key === 0) $rowPosition = 'top';

            $this->activeSheet->getStyle("{$this->colVendorName}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorGroup}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorCategory}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorWorkCategory}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));

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

        $this->activeSheet->setCellValue("B2", trans('vendorManagement.vendorWorkCategoryScores'));

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
        $this->activeSheet->setCellValue("{$this->colVendorWorkCategory}{$this->currentRow}", trans('vendorManagement.vendorWorkCategory'));

        $this->activeSheet->getStyle("{$this->colVendorName}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colVendorGroup}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colVendorCategory}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colVendorWorkCategory}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());

        $this->currentRow++;
    }
}