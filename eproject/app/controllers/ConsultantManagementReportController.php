<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementSubsidiary;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultant;
use PCK\ConsultantManagement\LetterOfAward;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use PCK\Helpers\StringOperations;

class ConsultantManagementReportController extends \BaseController
{
    public function index()
    {
        $user = \Confide::user();

        return View::make('consultant_management.reports.index', compact('user'));
    }

    public function list()
    {
        $request = Request::instance();

        $model = $this->reportModel();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 100;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $budgetVsFee = ($record->proposed_fee_amount) ? ($record->project_budget - $record->proposed_fee_amount) : 0;

            $data[] = [
                'id'                   => $record->subsidiary_id,
                'counter'              => $counter,
                'reference_no'         => $record->reference_no,
                'company_name'         => trim($record->name),
                'subsidiary_name'      => trim($record->subsidiary_name),
                'vendor_category_name' => trim($record->vendor_category_name),
                'roc_approved_date'    => ($record->roc_approved_date) ? Carbon::parse($record->roc_approved_date)->format('d/m/Y') : '-',
                'construction_cost'    => number_format($record->total_construction_cost, 2, '.', ','),
                'landscape_cost'       => number_format($record->total_landscape_cost, 2, '.', ','),
                'budget'               => number_format($record->project_budget, 2, '.', ','),
                'fee_amount'           => ($record->proposed_fee_amount) ? number_format($record->proposed_fee_amount, 2, '.', ',') : number_format(0, 2, '.', ','),
                'fee_percentage'       => ($record->proposed_fee_percentage) ? number_format($record->proposed_fee_percentage, 2, '.', ',') : number_format(0, 2, '.', ','),
                'budget_vs_fee'        => $budgetVsFee,
                'status'               => $record->getStatusText(),
                'loa_date'             => ($record->loa_date) ? Carbon::parse($record->loa_date)->format('d/m/Y') : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function exportExcel()
    {
        ini_set('memory_limit','2048M');
        ini_set('max_execution_time', '0'); // for infinite time of execution 

        $collection = $this->reportModel()->get();

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'ffffff']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '187bcd']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ]
        ];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator("Buildspace");

        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle(trans('general.consultantManagementReports'));
        $activeSheet->setAutoFilter('A1:L1');

        $headers = [
            trans('companies.referenceNo'),
            trans('vendorManagement.consultant'),
            trans('general.consultantCategories'),
            trans('general.subsidiaryTownship'),
            "Rec of Consultant Approved Date",
            trans('general.totalConstructionCost'),
            trans('general.totalLandscapeCost'),
            trans('tenders.budget'),
            "Fee",
            "Fee %",
            "Budget vs Fee",
            "RFP Status",
            "Letter of Award Date"
        ];

        $headerCount = 1;
        foreach($headers as $key => $val)
        {
            $cell = StringOperations::numberToAlphabet($headerCount)."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);

            $activeSheet->getColumnDimension(StringOperations::numberToAlphabet($headerCount))->setAutoSize(true);
            
            $headerCount++;
        }

        $records = [];
        foreach($collection as $idx => $record)
        {
            $budgetVsFee = ($record->proposed_fee_amount) ? ($record->project_budget - $record->proposed_fee_amount) : 0;

            $rocApprovedDate = null;
            $loaDate = null;

            if($record->roc_approved_date)
                $rocApprovedDate = Carbon::parse($record->roc_approved_date)->format(\Config::get('dates.standard'));
            
            if($record->loa_date)
                $loaDate = Carbon::parse($record->loa_date)->format(\Config::get('dates.standard'));
            
            $records[] = [
                $record->reference_no,
                $record->name,
                $record->subsidiary_name,
                $record->vendor_category_name,
                $rocApprovedDate,
                $record->total_construction_cost,
                $record->total_landscape_cost,
                $record->project_budget,
                ($record->proposed_fee_amount) ? $record->proposed_fee_amount : 0,
                ($record->proposed_fee_percentage) ? $record->proposed_fee_percentage : 0,
                $budgetVsFee,
                $record->getStatusText(),
                $loaDate
            ];

            unset($collection[$idx]);
        }

        unset($collection);

        foreach(['A', 'B', 'C', 'D'] as $column)
        {
            $activeSheet->getColumnDimension($column)->setAutoSize(false);
            $activeSheet->getColumnDimension($column)->setWidth(32);
        }

        $activeSheet->getStyle('F:K')->getNumberFormat()->setFormatCode("#,##0.00");

        $activeSheet->fromArray($records, null, 'A2');

        $writer = new Xlsx($spreadsheet);

        $filepath = \PCK\Helpers\Files::getTmpFileUri();

        $writer->save($filepath);

        $filename = 'consultant_management_reports-'.date("dmYHis");

        return \PCK\Helpers\Files::download($filepath, "{$filename}.".\PCK\Helpers\Files::EXTENSION_EXCEL);
    }

    protected function reportModel()
    {
        $request = Request::instance();

        $model = ConsultantManagementVendorCategoryRfp::select("consultant_management_vendor_categories_rfp.id AS id", "consultant_management_subsidiaries.id AS subsidiary_id", "companies.name", "contract.reference_no", "subsidiaries.name AS subsidiary_name",
        "vc.name AS vendor_category_name", "roc.updated_at AS roc_approved_date", "consultant_management_subsidiaries.total_construction_cost", "consultant_management_subsidiaries.total_landscape_cost",
        "consultant_management_subsidiaries.project_budget", "fee.proposed_fee_amount", "fee.proposed_fee_percentage", "loa.updated_at AS loa_date")
        ->join('consultant_management_contracts AS contract', 'contract.id', '=', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id')
        ->join('consultant_management_subsidiaries', 'contract.id', '=', 'consultant_management_subsidiaries.consultant_management_contract_id')
        ->join('vendor_categories AS vc', 'vc.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
        ->join('subsidiaries', 'subsidiaries.id', '=', 'consultant_management_subsidiaries.subsidiary_id')
        ->join('consultant_management_rfp_revisions AS rev', 'rev.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
        ->join(\DB::raw('(
            SELECT rfp.id, MAX(rev2.revision) AS revision
            FROM consultant_management_vendor_categories_rfp rfp
            JOIN consultant_management_rfp_revisions rev2 ON rfp.id = rev2.vendor_category_rfp_id
            GROUP BY rfp.id
        ) max_rfp_revisions'), function($join){
            $join->on('max_rfp_revisions.id', '=', 'consultant_management_vendor_categories_rfp.id');
            $join->on('max_rfp_revisions.revision','=', 'rev.revision');
        })
        ->join('consultant_management_consultant_rfp AS consultant', 'rev.id', '=', 'consultant.consultant_management_rfp_revision_id')
        ->join('companies', 'companies.id', '=', 'consultant.company_id')
        ->leftJoin('consultant_management_recommendation_of_consultants AS roc', function($join){
            $join->on('roc.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id');
            $join->on('roc.status','=', \DB::raw(ConsultantManagementRecommendationOfConsultant::STATUS_APPROVED));
        })
        ->leftJoin('consultant_management_consultant_rfp_proposed_fees AS fee', function($join){
            $join->on('fee.consultant_management_consultant_rfp_id', '=', 'consultant.id');
            $join->on('fee.consultant_management_subsidiary_id','=', 'consultant_management_subsidiaries.id');
        })
        ->leftJoin('consultant_management_letter_of_awards AS loa', function($join){
            $join->on('loa.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id');
            $join->on('loa.status','=', \DB::raw(LetterOfAward::STATUS_APPROVED));
        });

        if(strlen(trim($request->get('criteria_search_str'))) > 0)
        {
            $searchStr = '%'.urldecode(trim($request->get('criteria_search_str'))).'%';
            
            switch($request->get('search_criteria'))
            {
                case 'consultant_name':
                    $model->where('companies.name', 'ILIKE', $searchStr);
                    break;
                case 'reference_no':
                    $model->where('contract.reference_no', 'ILIKE', $searchStr);
                    break;
                case 'vendor_category':
                    $model->where('vc.name', 'ILIKE', $searchStr);
                    break;
                case 'subsidiary_name':
                    $model->where('subsidiaries.name', 'ILIKE', $searchStr);
                    break;
            }
        }

        if(($request->has('roc_approved_date_from') and strlen(trim($request->get('roc_approved_date_from'))) > 0) and ($request->has('roc_approved_date_to') and strlen(trim($request->get('roc_approved_date_to'))) > 0))
        {
            $rocApprovedDateFrom = date('Y-m-d', strtotime($request->get('roc_approved_date_from')));
            $rocApprovedDateTo = date('Y-m-d', strtotime(trim($request->get('roc_approved_date_to'))));
            
            $model->whereRaw('roc.updated_at BETWEEN ? AND ?', [$rocApprovedDateFrom, $rocApprovedDateTo]);
        }

        if(($request->has('loa_date_from') and strlen(trim($request->get('loa_date_from'))) > 0) and ($request->has('loa_date_to') and strlen(trim($request->get('loa_date_to'))) > 0))
        {
            $loaDateFrom = date('Y-m-d', strtotime($request->get('loa_date_from')));
            $loaDateTo = date('Y-m-d', strtotime(trim($request->get('loa_date_to'))));
            
            $model->whereRaw('loa.updated_at BETWEEN ? AND ?', [$loaDateFrom, $loaDateTo]);
        }

        $model->orderBy(\DB::raw('contract.created_at, contract.id desc, consultant_management_vendor_categories_rfp.id'), 'asc');

        return $model;
    }
}