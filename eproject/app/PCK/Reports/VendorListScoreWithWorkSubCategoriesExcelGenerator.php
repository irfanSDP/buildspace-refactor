<?php namespace PCK\Reports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PCK\Vendor\Vendor;
use PCK\Companies\Company;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorRegistration\VendorRegistration;

class VendorListScoreWithWorkSubCategoriesExcelGenerator extends ReportGenerator {

    protected $colVendorName              = 'B';
    protected $colVendorCode              = 'C';
    protected $colVendorGroup             = 'D';
    protected $colVendorCategory          = 'E';
    protected $colVendorCategoryScore     = 'F';
    protected $colVendorWorkCategory      = 'G';
    protected $colVendorWorkCategoryScore = 'H';
    protected $colVendorSubWorkCategories = 'I';

    protected $colFirst = 'B';
    protected $currentColumn;
    protected $sheetTitle;
    protected $data = [];
    protected $filters;
    protected $listType;

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    public function setSpreadsheetTitle($sheetTitle)
    {
        $this->sheetTitle = $sheetTitle;
    }

    public function setListType($listType)
    {
        $this->listType = $listType;
    }

    public function getListType()
    {
        if(is_array($this->listType))
        {
            return implode(', ', $this->listType);
        }

        return $this->listType;
    }

    protected function setColumnWidths()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colVendorName}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorCode}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colVendorGroup}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorCategory}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorCategoryScore}")->setWidth(8);
        $this->activeSheet->getColumnDimension("{$this->colVendorWorkCategory}")->setWidth(45);
        $this->activeSheet->getColumnDimension("{$this->colVendorWorkCategoryScore}")->setWidth(8);
        $this->activeSheet->getColumnDimension("{$this->colVendorSubWorkCategories}")->setWidth(45);
    }

    protected function loadData()
    {
        $companyNameFilter             = null;
        $contractGroupCategoryFilter   = null;
        $vendorWorkCategoryFilter      = null;
        $vendorCategoryFilter          = null;
        $vendorCodeFilter              = null;
        $vendorSubWorkCategoriesFilter = null;

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
                            $companyNameFilter = " AND c.name ILIKE '%{$val}%' ";
                        }
                        break;
                    case 'contract_group_category':
                        if(strlen($val) > 0)
                        {
                            $contractGroupCategoryFilter = " AND cgc.name ILIKE '%{$val}%' ";
                        }
                        break;
                    case 'vendor_work_category':
                        if(strlen($val) > 0)
                        {
                            $vendorWorkCategoryFilter = " AND vwc.name ILIKE '%$val%' ";
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $vendorCategoryFilter = " AND vc.name ILIKE '%$val%' ";
                        }
                        break;
                    case 'vendor_code':
                        if(strlen($val) > 0)
                        {
                            $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
                            $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

                            $vendorCodeFilter = " AND '" . $vendorCodePrefix . "' || LPAD(c.id::text, " . $vendorCodePadLength . ", '0') ILIKE '%$val%' ";
                        }
                        break;
                    case 'vendor_sub_work_categories';
                        if(strlen($val) > 0)
                        {
                            $vendorSubWorkCategoriesFilter = " HAVING STRING_AGG(DISTINCT vws.name, ', ') FILTER (WHERE vws.name IS NOT NULL) ILIKE '%{$val}%' ";
                        }
                        break;
                }
            }
        }

        $query = "WITH base_cte AS (
                      SELECT c.id AS company_id, c.name AS company, cgc.id AS contract_group_category_id, cgc.name AS contract_group_category, 
                      vc.id AS vendor_category_id, vc.name AS vendor_category, ROUND(AVG(vecs.deliberated_score) OVER (PARTITION BY c.id, vc.id)) AS vendor_category_score, 
                      vwc.id AS vendor_work_category_id, vwc.name AS vendor_work_category, ROUND(vecs.deliberated_score) AS vendor_work_category_score 
                      FROM companies c 
                      INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                      INNER JOIN vendors v ON v.company_id = c.id 
                      INNER JOIN vendor_work_categories vwc ON vwc.id = v.vendor_work_category_id 
                      INNER JOIN vendor_category_vendor_work_category vcvwc ON vcvwc.vendor_work_category_id = vwc.id 
                      LEFT OUTER JOIN company_vendor_category cvc ON cvc.company_id = c.id AND cvc.vendor_category_id = vcvwc.vendor_category_id 
                      LEFT OUTER JOIN vendor_categories vc ON vc.id = cvc.vendor_category_id 
                      LEFT OUTER JOIN vendor_evaluation_cycle_scores vecs ON vecs.id = v.vendor_evaluation_cycle_score_id 
                      WHERE c.confirmed IS TRUE 
                      AND c.deactivated_at IS NULL 
                      AND c.activation_date IS NOT NULL 
                      AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . " 
                      AND cgc.hidden IS FALSE 
                      AND (CASE WHEN vc.id IS NOT NULL THEN vc.hidden IS FALSE ELSE TRUE END) 
                      AND vwc.hidden IS FALSE 
                      AND v.type IN (" . $this->getListType() . ")  
                      {$companyNameFilter} 
                      {$contractGroupCategoryFilter} 
                      {$vendorWorkCategoryFilter} 
                      {$vendorCategoryFilter} 
                      {$vendorCodeFilter} 
                      ORDER BY c.name ASC, cgc.id ASC, vc.id ASC, vwc.id ASC
                  ),
                  final_vendor_registrations AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY company_id ORDER BY revision DESC) AS RANK, *  
                      FROM vendor_registrations 
                      WHERE company_id IN (SELECT company_id FROM base_cte) 
                      AND deleted_at IS NULL 
                      AND status = " . VendorRegistration::STATUS_COMPLETED . "
                  ),
                  track_record_projects_cte AS (
                      SELECT c.id AS company_id, t.*
                      FROM track_record_projects t
                      INNER JOIN final_vendor_registrations vr ON vr.id = t.vendor_registration_id 
                      INNER JOIN companies c ON c.id = vr.company_id
                      WHERE vr.rank = 1
                  )
                  SELECT bc.company_id, bc.company, bc.contract_group_category_id, bc.contract_group_category, bc.vendor_category, bc.vendor_category_score, bc.vendor_work_category, bc.vendor_work_category_score,
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT trpvws.vendor_work_subcategory_id) FILTER (WHERE trpvws.vendor_work_subcategory_id IS NOT NULL)) AS vendor_work_subcategory_ids, 
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT vws.name) FILTER (WHERE vws.name IS NOT NULL)) AS vendor_work_subcategories
                  FROM base_cte bc
                  LEFT OUTER JOIN track_record_projects_cte trp ON trp.company_id = bc.company_id AND trp.vendor_work_category_id = bc.vendor_work_category_id
                  LEFT OUTER JOIN track_record_project_vendor_work_subcategories trpvws ON trpvws.track_record_project_id = trp.id 
                  LEFT OUTER JOIN vendor_work_subcategories vws ON vws.id = trpvws.vendor_work_subcategory_id 
                  GROUP BY bc.contract_group_category_id, bc.company_id, bc.company, bc.contract_group_category, bc.vendor_category_id, bc.vendor_category, bc.vendor_category_score, bc.vendor_work_category_id, bc.vendor_work_category, bc.vendor_work_category_score 
                  {$vendorSubWorkCategoriesFilter}
                  ORDER BY bc.contract_group_category_id ASC, bc.company_id ASC, bc.vendor_category_id ASC, bc.vendor_work_category_id ASC;";

        $queryResults = DB::select(DB::raw($query));

        foreach($queryResults as $key => $record)
        {
            $this->data[] = [
                'vendor_code'                => Company::getVendorCodeFromId($record->company_id),
                'company'                    => $record->company,
                'contract_group_category'    => $record->contract_group_category,
                'vendor_category'            => $record->vendor_category,
                'vendor_category_score'      => $record->vendor_category_score,
                'vendor_work_category'       => $record->vendor_work_category,
                'vendor_work_category_score' => $record->vendor_work_category_score,
                'vendor_sub_work_categories' => is_null($record->vendor_work_subcategories) ? null : implode(', ', json_decode($record->vendor_work_subcategories)),
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
            $this->activeSheet->setCellValue("{$this->colVendorSubWorkCategories}{$this->currentRow}", $record['vendor_sub_work_categories']);

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
            $this->activeSheet->getStyle("{$this->colVendorSubWorkCategories}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));

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
            trans('vendorManagement.vendorSubWorkCategories'),
        ], $this->colFirst, $this->currentRow);

        $this->currentRow++;

        $this->activeSheet->mergeCells("{$this->colVendorName}{$headerFirstRow}:{$this->colVendorName}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colVendorCode}{$headerFirstRow}:{$this->colVendorCode}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colVendorGroup}{$headerFirstRow}:{$this->colVendorGroup}{$this->currentRow}");
        $this->activeSheet->mergeCells("{$this->colVendorSubWorkCategories}{$headerFirstRow}:{$this->colVendorSubWorkCategories}{$this->currentRow}");

        $this->activeSheet->getStyle("{$this->colVendorName}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colVendorCode}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colVendorGroup}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());
        $this->activeSheet->getStyle("{$this->colVendorSubWorkCategories}{$this->currentRow}")->applyFromArray($this->getHeaderStyle());

        $this->currentRow++;
    }
}