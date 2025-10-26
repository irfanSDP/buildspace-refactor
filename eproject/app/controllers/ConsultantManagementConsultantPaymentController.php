<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany;
use PCK\ConsultantManagement\ConsultantManagementOpenRfp;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfp;
use PCK\ConsultantManagement\LetterOfAward;
use PCK\Companies\Company;

class ConsultantManagementConsultantPaymentController extends \BaseController
{
    public function __construct()
    {
        
    }

    public function index()
    {
        $user = \Confide::user();

        return View::make('consultant_management.consultant_payments.index', compact('user'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Company::select("companies.id AS id", "companies.name AS company_name", "companies.reference_no")
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'companies.id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_rfp_revisions.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
        ->join('consultant_management_letter_of_awards', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_letter_of_awards.vendor_category_rfp_id')
        ->join('consultant_management_consultant_rfp', function($join){
            $join->on('consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id');
            $join->on('consultant_management_consultant_rfp.company_id','=', 'companies.id');
            $join->on('consultant_management_consultant_rfp.awarded','=', \DB::raw('TRUE'));
        })
        ->join('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->where('consultant_management_open_rfp.status', '=', ConsultantManagementOpenRfp::STATUS_APPROVED)
        ->where('consultant_management_letter_of_awards.status', '=', LetterOfAward::STATUS_APPROVED);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'company_name':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->groupBy('companies.id')
        ->orderBy('companies.name', 'asc');

        $rowCount = $model->get()->count();

        $companyVendorCategories = Company::selectRaw("companies.id AS company_id, vendor_categories.id AS vendor_category_id, vendor_categories.name AS vendor_category_name, SUM(consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount) AS fee_amount")
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'companies.id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_rfp_revisions.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
        ->join('vendor_categories', 'consultant_management_vendor_categories_rfp.vendor_category_id', '=', 'vendor_categories.id')
        ->join('consultant_management_letter_of_awards', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_letter_of_awards.vendor_category_rfp_id')
        ->join('consultant_management_consultant_rfp', function($join){
            $join->on('consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id');
            $join->on('consultant_management_consultant_rfp.company_id','=', 'companies.id');
            $join->on('consultant_management_consultant_rfp.awarded','=', \DB::raw('TRUE'));
        })
        ->join('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id')
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->where('consultant_management_open_rfp.status', '=', ConsultantManagementOpenRfp::STATUS_APPROVED)
        ->where('consultant_management_letter_of_awards.status', '=', LetterOfAward::STATUS_APPROVED)
        ->groupBy('companies.id')
        ->groupBy('vendor_categories.id')
        ->get()
        ->toArray();

        $vendorCategories = [];
        $consultantFees = [];
        foreach($companyVendorCategories as $companyVendorCategory)
        {
            if(!array_key_exists($companyVendorCategory['company_id'], $vendorCategories))
            {
                $vendorCategories[$companyVendorCategory['company_id']] = [];
            }

            if(!array_key_exists($companyVendorCategory['company_id'], $consultantFees))
            {
                $consultantFees[$companyVendorCategory['company_id']] = 0;
            }

            $vendorCategories[$companyVendorCategory['company_id']][$companyVendorCategory['vendor_category_id']] = $companyVendorCategory['vendor_category_name'];

            $consultantFees[$companyVendorCategory['company_id']] += $companyVendorCategory['fee_amount'];
        }

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                => $record->id,
                'counter'           => $counter,
                'company_name'      => trim($record->company_name),
                'reference_no'      => trim($record->reference_no),
                'vendor_categories' => (array_key_exists($record->id, $vendorCategories)) ? array_values($vendorCategories[$record->id]) : [],
                'total_fees'        => (array_key_exists($record->id, $consultantFees)) ? $consultantFees[$record->id] : 0,
                'route:show'        => route('consultant.management.consultant.payments.show', [$record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function show($id)
    {
        $company = Company::findOrFail((int)$id);

        $user = \Confide::user();

        return View::make('consultant_management.consultant_payments.show', compact('company', 'user'));
    }

    public function consultantList($id)
    {
        $company = Company::findOrFail((int)$id);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementContract::select("consultant_management_contracts.id", "consultant_management_contracts.title", "consultant_management_contracts.reference_no", "subsidiaries.name AS business_unit",
        "consultant_management_letter_of_awards.reference_number AS loa_no", "consultant_management_letter_of_awards.updated_at AS loa_date", "vendor_categories.name AS vendor_category",
        "consultant_management_contracts.modified_currency_code", "countries.currency_code", "consultant_management_vendor_categories_rfp.id AS vendor_category_rfp_id")
        ->join('subsidiaries', 'consultant_management_contracts.subsidiary_id', '=', 'subsidiaries.id')
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->join('vendor_categories', 'consultant_management_vendor_categories_rfp.vendor_category_id', '=', 'vendor_categories.id')
        ->join('consultant_management_letter_of_awards', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_letter_of_awards.vendor_category_rfp_id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.vendor_category_rfp_id', '=', 'consultant_management_letter_of_awards.vendor_category_rfp_id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'companies.id')
        ->join('consultant_management_consultant_rfp', function($join){
            $join->on('consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id');
            $join->on('consultant_management_consultant_rfp.company_id','=', 'companies.id');
            $join->on('consultant_management_consultant_rfp.awarded','=', \DB::raw('TRUE'));
        })
        ->join('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('countries', 'consultant_management_contracts.country_id', '=', 'countries.id')
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->where('consultant_management_open_rfp.status', '=', ConsultantManagementOpenRfp::STATUS_APPROVED)
        ->where('consultant_management_letter_of_awards.status', '=', LetterOfAward::STATUS_APPROVED)
        ->where('companies.id', '=', $company->id);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'title':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'business_unit':
                        if(strlen($val) > 0)
                        {
                            $model->where('subsidiaries.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'loa_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_letter_of_awards.reference_number', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_contracts.created_at', 'desc');

        $rowCount = $model->get()->count();

        $feeRecords = ConsultantManagementContract::selectRaw("consultant_management_contracts.id, consultant_management_vendor_categories_rfp.id AS vendor_category_rfp_id, subsidiaries.name AS subsidiary_name, consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount AS fee_amount")
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->join('vendor_categories', 'consultant_management_vendor_categories_rfp.vendor_category_id', '=', 'vendor_categories.id')
        ->join('consultant_management_letter_of_awards', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_letter_of_awards.vendor_category_rfp_id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.vendor_category_rfp_id', '=', 'consultant_management_letter_of_awards.vendor_category_rfp_id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'companies.id')
        ->join('consultant_management_consultant_rfp', function($join){
            $join->on('consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id');
            $join->on('consultant_management_consultant_rfp.company_id','=', 'companies.id');
            $join->on('consultant_management_consultant_rfp.awarded','=', \DB::raw('TRUE'));
        })
        ->join('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id')
        ->join('consultant_management_subsidiaries', function($join){
            $join->on('consultant_management_subsidiaries.id', '=', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_subsidiary_id');
            $join->on('consultant_management_subsidiaries.consultant_management_contract_id','=', 'consultant_management_contracts.id');
        })
        ->join('subsidiaries', 'consultant_management_subsidiaries.subsidiary_id', '=', 'subsidiaries.id')
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->where('consultant_management_open_rfp.status', '=', ConsultantManagementOpenRfp::STATUS_APPROVED)
        ->where('consultant_management_letter_of_awards.status', '=', LetterOfAward::STATUS_APPROVED)
        ->where('companies.id', '=', $company->id)
        ->get()
        ->toArray();

        $consultantFees = [];

        foreach($feeRecords as $consultantFee)
        {
            if(!array_key_exists($consultantFee['id'], $consultantFees))
            {
                $consultantFees[$consultantFee['id']] = [];
            }

            if(!array_key_exists($consultantFee['vendor_category_rfp_id'], $consultantFees[$consultantFee['id']]))
            {
                $consultantFees[$consultantFee['id']][$consultantFee['vendor_category_rfp_id']] = [];
            }

            $consultantFees[$consultantFee['id']][$consultantFee['vendor_category_rfp_id']][] = $consultantFee;
        }

        unset($feeRecords);
        
        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'              => $record->id,
                'counter'         => $counter,
                'title'           => trim($record->title),
                'reference_no'    => trim($record->reference_no),
                'business_unit'   => trim($record->business_unit),
                'vendor_category' => trim($record->vendor_category),
                'loa_no'          => $record->loa_no,
                'loa_date'        => ($record->loa_date) ? date('d/m/Y', strtotime($record->loa_date)) : "",
                'currency_code'   => ($record->modified_currency_code) ? $record->modified_currency_code : $record->currency_code,
                'fees'            => (array_key_exists($record->id, $consultantFees) && array_key_exists($record->vendor_category_rfp_id, $consultantFees[$record->id])) ? $consultantFees[$record->id][$record->vendor_category_rfp_id] : [],
                'route:show'      => route('consultant.management.consultant.payments.consultant.details', [$record->vendor_category_rfp_id, $company->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function consultantDetails($vendorCategoryRfpId, $id)
    {
        $vendorCategoryRfp = ConsultantManagementVendorCategoryRfp::findOrFail((int)$vendorCategoryRfpId);
        $company = Company::findOrFail((int)$id);
        $user = \Confide::user();

        $rfpRevision = $vendorCategoryRfp->getLatestRfpRevision();
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $consultantRfp = ConsultantManagementConsultantRfp::where('consultant_management_rfp_revision_id', '=', $rfpRevision->id)
        ->where('company_id', '=', $company->id)
        ->first();

        return View::make('consultant_management.consultant_payments.details', compact('vendorCategoryRfp', 'consultantManagementContract', 'consultantRfp', 'company'));
    }
}