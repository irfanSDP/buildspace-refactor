<?php

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PCK\Base\Helpers;
use PCK\Vendor\Vendor;
use PCK\Companies\Company;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\Forms\NominatedWatchListVendorForm;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorDetailSetting\VendorDetailAttachmentSetting;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorManagement\VendorManagementUserPermission;
use PCK\Reports\VendorListScoreExcelGenerator;
use PCK\Reports\VendorListScoreWithWorkSubCategoriesExcelGenerator;

class WatchListVendorsController extends \BaseController {

    protected $nominatedWatchListVendorForm;

    public function __construct(NominatedWatchListVendorForm $nominatedWatchListVendorForm)
    {
        $this->nominatedWatchListVendorForm = $nominatedWatchListVendorForm;
    }

    public function index()
    {
        $contractGroups = ContractGroupCategory::select('id', 'name AS description')
            /*->whereNotIn('name', ContractGroupCategory::getPrivateGroupNames())*/
            ->where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)
            ->where('hidden', '=', false)
            ->orderBy('name', 'asc')
            ->get();

        $externalVendorGroupsFilterOptions = [];

        $externalVendorGroupsFilterOptions[0] = trans('general.all');

        foreach($contractGroups as $vendorGroup)
        {
            $externalVendorGroupsFilterOptions[$vendorGroup->id] = $vendorGroup->description;
        }

        return View::make('vendor_management.lists.watch_list_vendors.index', compact('externalVendorGroupsFilterOptions'));
    }

    public function list()
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Company::select('companies.*', 'contract_group_categories.name AS contract_group_category_name',
            DB::raw('ARRAY_TO_JSON(ARRAY_AGG(vendor_categories.id)) AS vendor_category_id'), 
            DB::raw('ARRAY_TO_JSON(ARRAY_AGG(vendor_categories.name)) AS vendor_category_name'),
            'vendor_work_categories.id AS vendor_work_category_id', 'vendor_work_categories.name AS vendor_work_category_name',
            'vendors.watch_list_entry_date', 'vendors.watch_list_release_date', 'vendors.id AS vendor_id', 'vendor_profiles.id AS vendor_profile_id')
            ->join('vendors', 'vendors.company_id', '=', 'companies.id')
            ->join('vendor_work_categories', 'vendors.vendor_work_category_id', '=', 'vendor_work_categories.id')
            ->join('contract_group_categories', 'companies.contract_group_category_id', '=', 'contract_group_categories.id')
            ->join('vendor_category_vendor_work_category', 'vendor_category_vendor_work_category.vendor_work_category_id', '=', 'vendor_work_categories.id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
            ->join('vendor_profiles', 'vendor_profiles.company_id', '=', 'companies.id');

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);
                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
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
                    case 'contract_group_category_name':
                        if((int)$val > 0)
                        {
                            $model->where('contract_group_categories.id', '=', $val);
                        }
                        break;
                    case 'vendor_category_name':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_work_category_name':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_work_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->where('vendors.type', '=', Vendor::TYPE_WATCH_LIST )
            ->where('companies.confirmed', '=', true)
            ->whereNull('companies.deactivated_at')
            ->whereNotNull('companies.activation_date')
            ->where('vendor_work_categories.hidden', '=', false)
            ->where('vendor_categories.hidden', '=', false)
            ->where('contract_group_categories.type' , '=', ContractGroupCategory::TYPE_EXTERNAL)
            ->where('contract_group_categories.hidden', '=', false)
            ->orderBy('companies.name', 'asc')
            ->orderBy('vendor_work_categories.code', 'asc')
            ->orderBy('vendor_work_categories.name', 'asc')
            ->groupBy(\DB::raw('companies.id, contract_group_categories.id, vendors.id, vendor_work_categories.id, vendor_profiles.id'));

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $canEdit = VendorManagementUserPermission::hasPermission($user, VendorManagementUserPermission::TYPE_WATCH_LIST_EDIT);

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $entryDate = Carbon::parse($record->watch_list_entry_date);
            $releaseDate = Carbon::parse($record->watch_list_release_date);

            $data[] = [
                'id'                           => $record->vendor_work_category_id,
                'counter'                      => $counter,
                'name'                         => $record->name,
                'vendor_code'                  => $record->getVendorCode(),
                'reference_no'                 => $record->reference_no,
                'contract_group_category_name' => $record->contract_group_category_name,
                'vendor_category_name'         => implode(', ', json_decode($record->vendor_category_name)),
                'vendor_work_category_name'    => $record->vendor_work_category_name,
                'entry_date'                   => $entryDate->format(\Config::get('dates.standard')),
                'release_date'                 => $releaseDate->format(\Config::get('dates.standard')),
                'days_in_watch_list'           => Helpers::getYearMonthDayDiffString($entryDate, Carbon::now()),
                'days_to_release'              => Helpers::getYearMonthDayDiffString($releaseDate, Carbon::now()),
                'days_to_release_passed'       => $releaseDate->isPast(),
                'can_edit'                     => $canEdit,
                'route:edit'                   => route('vendorManagement.watchList.edit', [$record->vendor_id]),
                'route:view'                   => route('vendorProfile.show', [$record->id]),
                'route:remarks'                => route('vendorManagement.watchList.vendorProfile.remarks.ajax.list', [$record->vendor_profile_id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function edit($vendorId)
    {
        $vendor      = Vendor::findOrFail($vendorId);
        $entryDate   = Carbon::parse($vendor->watch_list_entry_date);
        $releaseDate = Carbon::parse($vendor->watch_list_release_date);

        $vendorDetailsAttachmentSetting = VendorDetailAttachmentSetting::first();

        return View::make('vendor_management.lists.watch_list_vendors.edit', compact('vendor', 'entryDate', 'releaseDate', 'vendorDetailsAttachmentSetting'));
    }

    public function update($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);

        $company = $vendor->company;

        $vendor->moveToNominatedWatchList();

        \Flash::success(trans('vendorManagement.pushedXToNomineesForWatchList', ['company' => $company->name]));

        return Redirect::route('vendorManagement.watchList.index');
    }

    public function scoresList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

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
            ->whereIn('vendors.company_id', Company::where('vendor_status', '=', Company::VENDOR_STATUS_WATCH_LIST)->lists('id'))
            ->orderBy('companies.name')
            ->orderBy('vendor_categories.name')
            ->orderBy('vendor_work_categories.name');

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
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

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                         => $record->id,
                'counter'                    => $counter,
                'vendor_code'                => Company::getVendorCodeFromId($record->company_id),
                'company'                    => $record->company,
                'contract_group_category'    => $record->contract_group_category,
                'vendor_category'            => $record->vendor_category,
                'vendor_work_category'       => $record->vendor_work_category,
                'vendor_work_category_score' => $record->deliberated_score,
                'vendor_category_score'      => $record->vendor_category_score,
                'route:vendor_profile'       => route('vendorProfile.show', array($record->company_id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function scoresExport()
    {
        $reportGenerator = new VendorListScoreExcelGenerator();

        $reportGenerator->setSpreadsheetTitle(trans('vendorManagement.watchList'));

        $reportGenerator->setFilters(Input::get('filters') ?? []);

        $reportGenerator->setCompanyIds(Company::where('vendor_status', '=', Company::VENDOR_STATUS_WATCH_LIST)->lists('id'));

        return $reportGenerator->generate();
    }

    public function scoresWithSubWorkCategoriesList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $companyNameFilter             = null;
        $contractGroupCategoryFilter   = null;
        $vendorWorkCategoryFilter      = null;
        $vendorCategoryFilter          = null;
        $vendorCodeFilter              = null;
        $vendorSubWorkCategoriesFilter = null;

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
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
                      AND v.type = " . Vendor::TYPE_WATCH_LIST . "  
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
                  ORDER BY bc.contract_group_category_id ASC, bc.company_id ASC, bc.vendor_category_id ASC, bc.vendor_work_category_id ASC ";

        $offset = $limit * ($page - 1);

        $rowCount = count(DB::select(DB::raw($query)));

        $query .= " LIMIT {$limit} OFFSET {$offset};";

        $queryResults = DB::select(DB::raw($query));

        $data = [];

        foreach($queryResults as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'counter'                    => $counter,
                'vendor_code'                => Company::getVendorCodeFromId($record->company_id),
                'company'                    => $record->company,
                'contract_group_category'    => $record->contract_group_category,
                'vendor_category'            => $record->vendor_category,
                'vendor_category_score'      => $record->vendor_category_score,
                'vendor_work_category'       => $record->vendor_work_category,
                'vendor_work_category_score' => $record->vendor_work_category_score,
                'vendor_sub_work_categories' => is_null($record->vendor_work_subcategories) ? null : implode(', ', json_decode($record->vendor_work_subcategories)),
                'route:vendor_profile'       => route('vendorProfile.show', array($record->company_id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function scoresWithSubWorkCategoriesExport()
    {
        ini_set('memory_limit','2048M');
        ini_set('max_execution_time', '0');

        $reportGenerator = new VendorListScoreWithWorkSubCategoriesExcelGenerator();

        $reportGenerator->setSpreadsheetTitle(trans('vendorManagement.watchListCategories'));

        $reportGenerator->setFilters(Input::get('filters') ?? []);

        $reportGenerator->setListType(Vendor::TYPE_WATCH_LIST);

        return $reportGenerator->generate();
    }

    public function contractGroupCategoriesSummary()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $filterClauses = "";
        $bindings = [];

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'contract_group_category':
                        if(strlen($val) > 0)
                        {
                            $filterClauses .= " AND cgc.name ILIKE ?";
                            $bindings[] = "%{$val}%";
                        }
                        break;
                }
            }
        }

        $query = "SELECT cgc.id, cgc.name as contract_group_category, count(distinct(c.id)) AS count
            FROM companies c 
            INNER JOIN vendors v ON v.company_id = c.id
            INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id
            WHERE v.type = " . Vendor::TYPE_WATCH_LIST . "
            AND c.confirmed IS TRUE
            AND c.deactivated_at IS NULL
            AND c.activation_date IS NOT NULL
            AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
            AND cgc.hidden IS FALSE
            {$filterClauses}
            GROUP BY cgc.id, cgc.id
            ORDER by cgc.name ASC";

        $results = DB::select(DB::raw($query), $bindings);

        $rowCount = count($results);

        $offset = $limit * ($page - 1);

        $query .= " LIMIT {$limit} OFFSET {$offset}";

        $results = DB::select(DB::raw($query), $bindings);

        $data = [];

        foreach($results as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                      => $record->id,
                'counter'                 => $counter,
                'contract_group_category' => $record->contract_group_category,
                'count'                   => $record->count,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorCategoriesSummary()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $filterClauses = "";
        $bindings = [];

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'contract_group_category':
                        if(strlen($val) > 0)
                        {
                            $filterClauses .= " AND cgc.name ILIKE ?";
                            $bindings[] = "%{$val}%";
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $filterClauses .= " AND vc.name ILIKE ?";
                            $bindings[] = "%{$val}%";
                        }
                        break;
                }
            }
        }

        $query = "SELECT vc.id, vc.name as vendor_category, cgc.name as contract_group_category, count(distinct(c.id)) AS count
            FROM companies c 
            INNER JOIN vendors v ON v.company_id = c.id
            INNER JOIN vendor_work_categories vwc ON vwc.id = v.vendor_work_category_id 
            INNER JOIN vendor_category_vendor_work_category pivot ON pivot.vendor_work_category_id = v.vendor_work_category_id 
            INNER JOIN vendor_categories vc ON vc.id = pivot.vendor_category_id 
            INNER JOIN contract_group_categories cgc ON cgc.id = vc.contract_group_category_id and cgc.id = c.contract_group_category_id
            WHERE v.type = " . Vendor::TYPE_WATCH_LIST . "
            AND c.confirmed IS TRUE
            AND c.deactivated_at IS NULL
            AND c.activation_date IS NOT NULL
            AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
            AND cgc.hidden IS FALSE
            AND vc.hidden IS FALSE
            AND vwc.hidden IS FALSE
            {$filterClauses}
            GROUP BY vc.id, cgc.id
            ORDER by vc.name ASC";

        $results = DB::select(DB::raw($query), $bindings);

        $rowCount = count($results);

        $offset = $limit * ($page - 1);

        $query .= " LIMIT {$limit} OFFSET {$offset}";

        $results = DB::select(DB::raw($query), $bindings);

        $data = [];

        foreach($results as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                      => $record->id,
                'counter'                 => $counter,
                'vendor_category'         => $record->vendor_category,
                'contract_group_category' => $record->contract_group_category,
                'count'                   => $record->count,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorWorkCategoriesSummary()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $filterClauses = "";
        $bindings = [];

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'contract_group_category':
                        if(strlen($val) > 0)
                        {
                            $vendorWorkCategoryIds = \DB::table('vendor_category_vendor_work_category')
                                ->select('vendor_category_vendor_work_category.vendor_work_category_id')
                                ->join('vendor_categories', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
                                ->join('contract_group_categories', 'contract_group_categories.id', '=', 'vendor_categories.contract_group_category_id')
                                ->where('contract_group_categories.name', 'ILIKE', '%'.$val.'%')
                                ->lists('vendor_category_vendor_work_category.vendor_work_category_id');

                            if(!empty($vendorWorkCategoryIds))
                            {
                                $filterClauses .= " AND vwc.id IN (".implode(',', array_fill(0, count($vendorWorkCategoryIds), '?')).")";
                                $bindings = array_merge($bindings, $vendorWorkCategoryIds);
                            }
                            else
                            {
                                $filterClauses .= " AND FALSE";
                            }
                        }
                        break;
                    case 'vendor_categories':
                        if(strlen($val) > 0)
                        {
                            $vendorWorkCategoryIds = \DB::table('vendor_category_vendor_work_category')
                                ->select('vendor_category_vendor_work_category.vendor_work_category_id')
                                ->join('vendor_categories', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
                                ->where('vendor_categories.name', 'ILIKE', '%'.$val.'%')
                                ->lists('vendor_category_vendor_work_category.vendor_work_category_id');

                            if(!empty($vendorWorkCategoryIds))
                            {
                                $filterClauses .= " AND vwc.id IN (".implode(',', array_fill(0, count($vendorWorkCategoryIds), '?')).")";
                                $bindings = array_merge($bindings, $vendorWorkCategoryIds);
                            }
                            else
                            {
                                $filterClauses .= " AND FALSE";
                            }
                        }
                        break;
                    case 'vendor_work_category':
                        if(strlen($val) > 0)
                        {
                            $filterClauses .= " AND vwc.name ILIKE ?";
                            $bindings[] = "%{$val}%";
                        }
                        break;
                }
            }
        }

        $query = "SELECT vwc.id, vwc.name as vendor_work_category, count(distinct(c.id)) AS count
            FROM companies c 
            INNER JOIN vendors v ON v.company_id = c.id
            INNER JOIN vendor_work_categories vwc ON vwc.id = v.vendor_work_category_id 
            INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
            WHERE v.type = " . Vendor::TYPE_WATCH_LIST . "
            AND c.confirmed IS TRUE
            AND c.deactivated_at IS NULL
            AND c.activation_date IS NOT NULL
            AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
            AND cgc.hidden IS FALSE
            AND vwc.hidden IS FALSE
            {$filterClauses}
            GROUP BY vwc.id
            ORDER by vwc.name ASC";

        $results = DB::select(DB::raw($query), $bindings);

        $rowCount = count($results);

        $offset = $limit * ($page - 1);

        $query .= " LIMIT {$limit} OFFSET {$offset}";

        $results = DB::select(DB::raw($query), $bindings);

        $vendorCategoriesByVendorWorkCategoryId = VendorCategory::select('vendor_categories.id as vendor_category_id', 'vendor_category_vendor_work_category.vendor_work_category_id', 'vendor_categories.name as vendor_category', 'contract_group_categories.name as contract_group_category')
            ->join('vendor_category_vendor_work_category', 'vendor_category_vendor_work_category.vendor_category_id', '=', 'vendor_categories.id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'vendor_categories.contract_group_category_id')
            ->whereIn('vendor_category_vendor_work_category.vendor_work_category_id', array_column($results, 'id'))
            ->orderBy('vendor_categories.name')
            ->get()
            ->groupBy('vendor_work_category_id');

        $data = [];

        foreach($results as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $vendorCategoriesArray        = [];
            $contractGroupCategoriesArray = [];

            foreach($vendorCategoriesByVendorWorkCategoryId[$record->id] as $categories)
            {
                $vendorCategoriesArray[]        = $categories['vendor_category'];
                $contractGroupCategoriesArray[] = $categories['contract_group_category'];
            }

            $data[] = [
                'id'                        => $record->id,
                'counter'                   => $counter,
                'vendor_work_category'      => $record->vendor_work_category,
                'vendor_categories'         => $vendorCategoriesArray,
                'contract_group_categories' => $contractGroupCategoriesArray,
                'count'                     => $record->count,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }
}