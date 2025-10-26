<?php

use Illuminate\Support\Facades\DB;
use PCK\Vendor\Vendor;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Companies\Company;
use Carbon\Carbon;
use PCK\Tag\ObjectTag;
use PCK\Tag\Tag;
use PCK\FormBuilder\DynamicForm;
use PCK\VendorRegistration\VendorRegistration;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\VendorPreQualification\VendorGroupGrade;
use PCK\BuildingInformationModelling\BuildingInformationModellingLevel;
use PCK\CIDBGrades\CIDBGrade;
use PCK\Reports\VendorListScoreExcelGenerator;
use PCK\Reports\VendorListScoreWithWorkSubCategoriesExcelGenerator;
use PCK\VendorCategory\VendorCategory;

class ActiveVendorsController extends \BaseController {

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

        $statusFilterOptions = [
            0 => trans('general.all'),
            VendorRegistration::STATUS_DRAFT => trans('forms.draft'),
            VendorRegistration::STATUS_SUBMITTED => trans('forms.submitted'),
            VendorRegistration::STATUS_PROCESSING => trans('forms.processing'),
            VendorRegistration::STATUS_PENDING_VERIFICATION => trans('forms.pendingForApproval'),
            VendorRegistration::STATUS_COMPLETED => trans('forms.completed'),
            VendorRegistration::STATUS_REJECTED => trans('forms.rejected'),
        ];

        $submissionTypeFilterOptions = [
            0 => trans('general.all'),
            VendorRegistration::SUBMISSION_TYPE_NEW => trans('vendorManagement.newRegistration'),
            VendorRegistration::SUBMISSION_TYPE_RENEWAL => trans('vendorManagement.renewal'),
            VendorRegistration::SUBMISSION_TYPE_UPDATE => trans('vendorManagement.update')
        ];

        $isQualifiedFilterOptions = [
            0 => trans('general.all'),
            "true" => trans('vendorManagement.qualified'),
            "false" => trans('vendorManagement.unqualified'),
        ];

        $cidbGradeFilterOptions[0] = trans('general.all');

        foreach(CIDBGrade::orderBy('id', 'ASC')->get() as $cidbGrade)
        {
            $cidbGradeFilterOptions[$cidbGrade->id] = $cidbGrade->grade;
        }

        $bimLevelFilterOptions[0] = trans('general.all');

        foreach(BuildingInformationModellingLevel::orderBy('id', 'ASC')->get() as $bimLevel)
        {
            $bimLevelFilterOptions[$bimLevel->id] = $bimLevel->name;
        }

        return View::make('vendor_management.lists.active_vendors.index', compact('externalVendorGroupsFilterOptions', 'statusFilterOptions', 'submissionTypeFilterOptions', 'isQualifiedFilterOptions', 'cidbGradeFilterOptions', 'bimLevelFilterOptions'));
    }

    public function list()
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $request = Request::instance();

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        $vendorWorkCategoryQualification = (is_null($request->get('vendor_work_category_qualification')) || $request->get('vendor_work_category_qualification') == '') ? null : $request->get('vendor_work_category_qualification');
        $vendorActiveStatus              = (is_null($request->get('vendor_active_status')) || $request->get('vendor_active_status') == '') ? null : $request->get('vendor_active_status');

        $model = Company::select('companies.*', 'contract_group_categories.name as vendor_group', DB::RAW("ARRAY_TO_JSON(ARRAY_AGG(vendor_work_categories.name) FILTER (WHERE vendor_work_categories.name IS NOT NULL)) AS vendor_work_categories"))
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
                FROM vendor_registrations
                WHERE status = ".VendorRegistration::STATUS_COMPLETED."
                AND deleted_at IS NULL
                GROUP BY company_id) vr"), 'vr.company_id', '=', 'companies.id')
            ->join(\DB::raw("(SELECT id as vr_final_id, company_id, revision
                FROM vendor_registrations
                WHERE status = ".VendorRegistration::STATUS_COMPLETED."
                AND deleted_at IS NULL) vr_final"), function($join){
                    $join->on('vr_final.company_id', '=', 'vr.company_id');
                    $join->on('vr_final.revision', '=', 'vr.revision');
                })
            ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
                FROM vendor_registrations
                WHERE deleted_at IS NULL
                GROUP BY company_id) vr_latest"), 'vr_latest.company_id', '=', 'companies.id')
            ->join(\DB::raw("(SELECT status, submission_type, company_id, revision
                FROM vendor_registrations
                WHERE deleted_at IS NULL) vr_status"), function($join){
                    $join->on('vr_status.company_id', '=', 'vr_latest.company_id');
                    $join->on('vr_status.revision', '=', 'vr_latest.revision');
                }
            )
            ->leftJoin(\DB::raw("(SELECT vendor_registration_id, ROUND(AVG(vendor_pre_qualifications.score)) AS avg_score
                FROM vendor_pre_qualifications
                WHERE deleted_at IS NULL
                GROUP BY vendor_registration_id) preq"), 'preq.vendor_registration_id', '=', 'vr_final.vr_final_id'
            )
            ->leftJoin('vendors', function($join) {
                $join->on('vendors.company_id', '=', 'companies.id');
                $join->on(\DB::raw("vendors.type = " . Vendor::TYPE_ACTIVE), \DB::raw(''), \DB::raw(''));
            })
            ->leftJoin('vendor_work_categories', function($join) {
                $join->on('vendor_work_categories.id', '=', 'vendors.vendor_work_category_id');
                $join->on(\DB::raw('vendor_work_categories.hidden IS FALSE'), \DB::raw(''), \DB::raw(''));
            });

        $tagNameSql= null;
        $vendorWorkCategoriesNameSql = null;

        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'vendor_code':
                        if(strlen($val) > 0)
                        {
                            $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
                            $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

                            $model->where(DB::raw("'" . $vendorCodePrefix . "' || LPAD(companies.id::text, " . $vendorCodePadLength . ", '0')"), 'ILIKE', '%' . $val . '%');
                        }
                        break;
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_group':
                        if((int)$val > 0)
                        {
                            $model->where('contract_group_categories.id', '=', $val);
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'tags':
                        if(strlen($val) > 0)
                        {
                            $tagNameSql = " AND tags.name ILIKE '%".$val."%' ";
                        }
                        break;
                    case 'vendor_work_categories':
                        if(strlen($val) > 0)
                        {
                            $vendorWorkCategoriesNameSql = " STRING_AGG(DISTINCT vendor_work_categories.name, ', ') FILTER (WHERE vendor_work_categories.name IS NOT NULL) ILIKE '%{$val}%' ";
                        }
                        break;
                    case 'status':
                        if((int)$val > 0)
                        {
                            $model->where('vr_status.status', (int)$val);
                        }
                        break;
                    case 'submission_type':
                        if((int)$val > 0)
                        {
                            if($val == VendorRegistration::SUBMISSION_TYPE_NEW)
                            {
                                $model->where('vr_status.submission_type', VendorRegistration::SUBMISSION_TYPE_NEW);
                            }
                            elseif($val == VendorRegistration::SUBMISSION_TYPE_UPDATE)
                            {
                                $model->where('vr_status.submission_type', VendorRegistration::SUBMISSION_TYPE_UPDATE);
                            }
                            else
                            {
                                $model->where('vr_status.submission_type', VendorRegistration::SUBMISSION_TYPE_RENEWAL);
                            }
                        }
                        break;
                    case 'cidbgrade':
                        if((int) $val > 0)
                        {
                            $model->where('companies.cidb_grade', (int) $val);
                        }
                        break;
                    case 'biminformation':
                        if((int) $val > 0)
                        {
                            $model->where('companies.bim_level_id', (int) $val);
                        }
                        break;
                }
            }
        }

        if($vendorActiveStatus)
        {
            if($vendorActiveStatus == 'active')
            {
                $model->where('companies.expiry_date', '>', \DB::raw('NOW()'));
            }
            elseif($vendorActiveStatus = 'expired')
            {
                $model->where('companies.expiry_date', '<', \DB::raw('NOW()'));
            }
        }

        if($tagNameSql)
        {
            $model->whereRaw("
                EXISTS (
                    SELECT object_tags.object_id
                    FROM object_tags
                    JOIN tags ON object_tags.tag_id = tags.id AND tags.category = ".Tag::CATEGORY_VENDOR_PROFILE."
                    WHERE object_tags.object_class = '".get_class(new Company)."'
                    AND object_tags.object_id = companies.id
                    ".$tagNameSql."
                )
            ");
        }

        if(!is_null($vendorWorkCategoryQualification))
        {
            $isVendorQualifiedFlag = ($vendorWorkCategoryQualification == 'yes') ? 'TRUE' : 'FALSE';

            $model->whereRaw("(CASE WHEN vendors.is_qualified IS NOT NULL THEN vendors.is_qualified IS {$isVendorQualifiedFlag} END)");
        }

        $model->where('companies.confirmed', '=', true)
        ->whereNull('companies.deactivated_at')
        ->whereNotNull('companies.activation_date')
        ->where('contract_group_categories.type', '=', ContractGroupCategory::TYPE_EXTERNAL)
        ->where('contract_group_categories.hidden', '=',  false)
        ->groupBy('companies.id', 'contract_group_categories.id');

        if(!is_null($vendorWorkCategoriesNameSql))
        {
            $model->havingRaw($vendorWorkCategoriesNameSql);
        }

        $model->orderBy('companies.name', 'asc')
        ->orderBy('contract_group_categories.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        $preQualificationGradeFilterOptions = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $vendorWorkcategoriesArray = is_null($record->vendor_work_categories) ? [] : json_decode($record->vendor_work_categories);

            $objectTagNames = ObjectTag::getTagNames($record, Tag::CATEGORY_VENDOR_PROFILE);

            if(!array_key_exists($record->contract_group_category_id, $preQualificationGradeFilterOptions))
            {
                $vendorPreQGrade = VendorGroupGrade::getGradeByGroup($record->contract_group_category_id);

                $preQualificationGradeFilterOptions[$record->contract_group_category_id] = [0 => trans('general.all')];

                if($vendorPreQGrade)
                {
                    foreach($vendorPreQGrade->levels()->orderBy('score_upper_limit', 'asc')->get() as $gradeLevel)
                    {
                        $preQualificationGradeFilterOptions[$record->contract_group_category_id][$gradeLevel->id] = $gradeLevel->description;
                    }
                }
            }

            $cidbGrade = NULL;

            if($record->cidb_grade)
            {
                if(CIDBGrade::find($record->cidb_grade))
                {
                    $cidbGrade = CIDBGrade::find($record->cidb_grade)->grade;
                }
            }

            $data[] = [
                'id'                        => $record->id,
                'counter'                   => $counter,
                'name'                      => $record->name,
                'vendor_code'               => $record->getVendorCode(),
                'vendor_group'              => $record->vendor_group,
                'reference_no'              => $record->reference_no,
                'status'                    => $record->vendorRegistration->status_text,
                'submission_type'           => $record->vendorRegistration->submission_type_text,
                'vendorWorkCategoriesArray' => $vendorWorkcategoriesArray,
                'tags'                      => implode(' ', $objectTagNames),
                'tagsArray'                 => $objectTagNames,
                'expiry_date'               => $record->expiry_date ? Carbon::parse($record->expiry_date)->format(\Config::get('dates.standard')) : null,
                'route:view'                => route('vendorProfile.show', array($record->id)),
                'route:breakdown'           => route('vendorManagement.activeVendorList.vendors.ajax.list', array($record->id)),
                'route:reminder'            => route('vendorManagement.renewalReminder', array($record->id)),
                'route:update-reminder'     => route('vendorManagement.updateReminder', array($record->id)),
                'cidbGrade'                 => is_null($record->cidb_grade) ? null : $cidbGrade,
                'bimInformation'            => is_null($record->bimLevel) ? null : $record->bimLevel->name,
                'preq_grade_filter_options' => $preQualificationGradeFilterOptions[$record->contract_group_category_id],
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function breakdownList($companyId)
    {
        $request = Request::instance();

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        $model = Vendor::select('vendors.id', 'vendors.is_qualified', 'vendor_pre_qualifications.score as pre_qualification_score', 'vendors.vendor_work_category_id', 'vendor_evaluation_cycle_scores.deliberated_score')
            ->where('vendors.company_id', '=', $companyId)
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
            ->join('companies', 'companies.id', '=', 'vendors.company_id')
            ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
                FROM vendor_registrations
                WHERE status = ".VendorRegistration::STATUS_COMPLETED."
                AND deleted_at IS NULL
                GROUP BY company_id) vr"), 'vr.company_id', '=', 'companies.id')
            ->join(\DB::raw("(SELECT id, company_id, revision
                FROM vendor_registrations
                WHERE status = ".VendorRegistration::STATUS_COMPLETED."
                AND deleted_at IS NULL) vr_final"), function($join){
                    $join->on('vr_final.company_id', '=', 'vr.company_id');
                    $join->on('vr_final.revision', '=', 'vr.revision');
                })
            ->leftJoin('vendor_pre_qualifications', function($join){
                $join->on('vendor_pre_qualifications.vendor_registration_id', '=', 'vr_final.id');
                $join->on('vendor_pre_qualifications.vendor_work_category_id', '=', 'vendors.vendor_work_category_id');
                $join->whereNull("vendor_pre_qualifications.deleted_at");
            })
            ->leftJoin('vendor_evaluation_cycle_scores', 'vendor_evaluation_cycle_scores.id', '=', 'vendors.vendor_evaluation_cycle_score_id')
            ->where('type', '=', Vendor::TYPE_ACTIVE)
            ->orderBy('is_qualified', 'asc')
            ->orderBy('vendors.vendor_work_category_id', 'desc');

        $company = Company::find($companyId);

        $vendorPreQGrade = VendorGroupGrade::getGradeByGroup($company->contract_group_category_id);

        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;
                $val = trim($filters['value']);
                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_work_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'pre_qualification_grade':
                        if((int)$val > 0)
                        {
                            $ranges = $vendorPreQGrade->getLevelRanges();

                            $model->where('vendor_pre_qualifications.score', '>', $ranges[$val]['min']);
                            $model->where('vendor_pre_qualifications.score', '<=', $ranges[$val]['max']);
                        }
                        break;
                    case 'is_qualified':
                        if($val === "true")
                        {
                            $model->where('vendors.is_qualified', '=', true);
                        }
                        elseif($val === "false")
                        {
                            $model->where('vendors.is_qualified', '=', false);
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

        foreach($records as $record)
        {
            $data[] = [
                'id'                        => $record->id,
                'name'                      => $record->vendorWorkCategory->name,
                'score'                     => $record->deliberated_score,
                'pre_qualification_score'   => $record->pre_qualification_score,
                'pre_qualification_grade'   => $record->pre_qualification_score && $vendorPreQGrade ? $vendorPreQGrade->getGrade($record->pre_qualification_score)->description : null,
                'is_qualified'              => $record->is_qualified ? trans('general.yes') : trans('general.no'),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function scoresList()
    {
        $request = Request::instance();

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

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
            ->whereIn('vendors.company_id', Company::where('vendor_status', '=', Company::VENDOR_STATUS_ACTIVE)->lists('id'))
            ->orderBy('companies.name')
            ->orderBy('vendor_categories.name')
            ->orderBy('vendor_work_categories.name');

        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
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

        $reportGenerator->setSpreadsheetTitle(trans('vendorManagement.activeVendorList'));

        $reportGenerator->setFilters(Input::get('filters') ?? []);

        $reportGenerator->setCompanyIds(Company::where('vendor_status', '=', Company::VENDOR_STATUS_ACTIVE)->lists('id'));

        return $reportGenerator->generate();
    }

    public function scoresWithSubWorkCategoriesList()
    {
        $request = Request::instance();

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        $companyNameFilter             = null;
        $contractGroupCategoryFilter   = null;
        $vendorWorkCategoryFilter      = null;
        $vendorCategoryFilter          = null;
        $vendorCodeFilter              = null;
        $vendorSubWorkCategoriesFilter = null;

        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
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
                      AND v.type = " . Vendor::TYPE_ACTIVE . "  
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

        $reportGenerator->setSpreadsheetTitle(trans('vendorManagement.approvedVendorListCategories'));

        $reportGenerator->setFilters(Input::get('filters') ?? []);

        $reportGenerator->setListType(Vendor::TYPE_ACTIVE);

        return $reportGenerator->generate();
    }

    public function contractGroupCategoriesSummary()
    {
        $request = Request::instance();

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        $filterClauses = "";
        $bindings = [];

        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
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
            WHERE v.type = " . Vendor::TYPE_ACTIVE . "
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

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        $filterClauses = "";
        $bindings = [];

        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
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
            WHERE v.type = " . Vendor::TYPE_ACTIVE . "
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

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        $filterClauses = "";
        $bindings = [];

        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
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
            WHERE v.type = " . Vendor::TYPE_ACTIVE . "
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