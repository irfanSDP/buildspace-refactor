<?php namespace PCK\Reports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PCK\Vendor\Vendor;
use PCK\Companies\Company;

class VendorListScoreExcelGenerator extends ReportGenerator {

    protected $colVendorName              = 'B';
    protected $colVendorCode              = 'C';
    protected $colVendorGroup             = 'D';
    protected $colVendorCategory          = 'E';
    protected $colVendorCategoryScore     = 'F';
    protected $colVendorWorkCategory      = 'G';
    protected $colVendorWorkCategoryScore = 'H';

    protected $colFirst = 'B';
    protected $currentColumn;
    protected $sheetTitle;
    protected $data = [];
    protected $filters;
    protected $companyIds = [];

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    public function setSpreadsheetTitle($sheetTitle)
    {
        $this->sheetTitle = $sheetTitle;
    }

    public function setCompanyIds($companyIds)
    {
        $this->companyIds = $companyIds;
    }

    protected function setColumnWidths()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colVendorName}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorCode}")->setWidth(10);
        $this->activeSheet->getColumnDimension("{$this->colVendorGroup}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorCategory}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorCategoryScore}")->setWidth(8);
        $this->activeSheet->getColumnDimension("{$this->colVendorWorkCategory}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorWorkCategoryScore}")->setWidth(8);
    }

    protected function loadData()
    {
        $model = Vendor::select(
                'vendors.id',
                'companies.id AS company_id',
                'companies.name AS company',
                'contract_group_categories.name AS contract_group_category',
                'vendor_categories.name AS vendor_category',
                'vendor_work_categories.name AS vendor_work_category',
                \DB::raw('ROUND(wc_score.vendor_category_score) AS vendor_category_score'),
                \DB::raw('ROUND(cycle_score.deliberated_score) AS deliberated_score')
            )
            ->join('companies', 'companies.id', '=', 'vendors.company_id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
            ->join('vendor_category_vendor_work_category', 'vendor_category_vendor_work_category.vendor_work_category_id', '=', 'vendors.vendor_work_category_id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
            ->join('vendor_evaluation_cycle_scores as cycle_score', 'cycle_score.id', '=', 'vendors.vendor_evaluation_cycle_score_id')
            ->join(\DB::raw(
                '(SELECT wc_score_s.company_id, wc_score_p.vendor_category_id, AVG(wc_score_s.deliberated_score) AS vendor_category_score
                FROM vendors wc_score_v
                JOIN vendor_evaluation_cycle_scores wc_score_s ON wc_score_s.id = wc_score_v.vendor_evaluation_cycle_score_id 
                JOIN vendor_category_vendor_work_category wc_score_p ON wc_score_p.vendor_work_category_id = wc_score_v.vendor_work_category_id 
                GROUP BY wc_score_s.company_id, wc_score_p.vendor_category_id) wc_score'), function($join){
                    $join->on('wc_score.company_id', '=', 'vendors.company_id');
                    $join->on('wc_score.vendor_category_id', '=', 'vendor_category_vendor_work_category.vendor_category_id');
                }
            )
            ->whereIn('vendors.company_id', $this->companyIds)
            ->orderBy('companies.name')
            ->orderBy('vendor_categories.name')
            ->orderBy('vendor_work_categories.name');

        if(!empty($this->filters))
        {
            foreach($this->filters as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'company':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'contract_group_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('contract_group_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_work_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_work_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_code':
                        if(strlen($val) > 0)
                        {
                            $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
                            $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

                            $model->where(\DB::raw("'" . $vendorCodePrefix . "' || LPAD(companies.id::text, " . $vendorCodePadLength . ", '0')"), 'ILIKE', '%' . $val . '%');
                        }
                        break;
                }
            }
        }

        $records = $model->get();

        foreach($records->all() as $key => $record)
        {
            $this->data[] = [
                'vendor_code'                => Company::getVendorCodeFromId($record->company_id),
                'company'                    => $record->company,
                'contract_group_category'    => $record->contract_group_category,
                'vendor_category'            => $record->vendor_category,
                'vendor_work_category'       => $record->vendor_work_category,
                'vendor_work_category_score' => $record->deliberated_score,
                'vendor_category_score'      => $record->vendor_category_score,
            ];
        }
    }

    public function generate()
    {
        $this->loadData();

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
            $this->activeSheet->setCellValue("{$this->colVendorName}{$this->currentRow}", $record['company']);
            $this->activeSheet->setCellValue("{$this->colVendorCode}{$this->currentRow}", $record['vendor_code']);
            $this->activeSheet->setCellValue("{$this->colVendorGroup}{$this->currentRow}", $record['contract_group_category']);
            $this->activeSheet->setCellValue("{$this->colVendorCategory}{$this->currentRow}", $record['vendor_category']);
            $this->activeSheet->setCellValue("{$this->colVendorCategoryScore}{$this->currentRow}", $record['vendor_category_score']);
            $this->activeSheet->setCellValue("{$this->colVendorWorkCategory}{$this->currentRow}", $record['vendor_work_category']);
            $this->activeSheet->setCellValue("{$this->colVendorWorkCategoryScore}{$this->currentRow}", $record['vendor_work_category_score']);

            $rowPosition = 'middle';

            if($key === (count($this->data)-1)) $rowPosition = 'bottom';
            elseif($key === 0) $rowPosition = 'top';

            $this->activeSheet->getStyle("{$this->colVendorName}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorCode}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorGroup}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorCategory}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorCategoryScore}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorWorkCategory}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));
            $this->activeSheet->getStyle("{$this->colVendorWorkCategoryScore}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));

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
        $this->activeSheet->setCellValue("B1", $this->sheetTitle);

        $this->activeSheet->mergeCells("B1:D1");

        $this->activeSheet->getStyle("B1")->applyFromArray(array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        ));

        $this->currentRow = 3;
    }

    public function generateHeaders()
    {
        $headerFirstRow = $this->currentRow;

        $this->addHeaderColumns([
            trans('vendorManagement.vendor'),
            trans('vendorManagement.vendorCode'),
            trans('vendorManagement.vendorGroup'),
            trans('vendorManagement.vendorCategory') => [
                trans('general.name'),
                trans('vendorManagement.score'),
            ],
            trans('vendorManagement.vendorWorkCategory') => [
                trans('general.name'),
                trans('vendorManagement.score'),
            ],
        ], $this->colFirst, $this->currentRow);

        $this->currentRow++;

        $this->activeSheet->mergeCells("{$this->colVendorName}{$headerFirstRow}:{$this->colVendorName}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colVendorCode}{$headerFirstRow}:{$this->colVendorCode}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colVendorGroup}{$headerFirstRow}:{$this->colVendorGroup}{$this->currentRow}");

        $this->activeSheet->getStyle("{$this->colVendorName}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colVendorCode}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colVendorGroup}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());

        $this->currentRow++;
    }
}