<?php

use Illuminate\Database\Eloquent\Collection;
use PCK\Companies\Company;
use PCK\Projects\Project;
use PCK\Tag\Tag;
use PCK\Tag\ObjectTag;
use PCK\CompanyProject\CompanyProject;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\CompanyPersonnel\CompanyPersonnel;
use PCK\SupplierCreditFacility\SupplierCreditFacility;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\FormBuilder\FormObjectMapping;
use PCK\FormBuilder\DynamicForm;
use PCK\FormBuilder\Elements\Element;
use PCK\FormBuilder\Elements\SystemModuleElement;
use Carbon\Carbon;
use PCK\MyCompanyProfiles\MyCompanyProfile;
use PCK\Vendor\Vendor;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorWorkSubcategory\VendorWorkSubcategory;
use PCK\VendorPreQualification\VendorGroupGrade;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\Helpers\Hierarchy\AdjacencyListNode;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\VendorDetailSetting\VendorDetailAttachmentSetting;
use PCK\VendorPerformanceEvaluation\CycleScore;
use PCK\VendorPerformanceEvaluation\EvaluationScore;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyFormEvaluationLog;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationProcessorEditLog;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationProcessorEditDetail;
use PCK\Verifier\Verifier;
use PCK\VendorManagement\VendorManagementUserPermission;

use PCK\FormBuilder\Elements\FileUpload;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorRegistration\VendorProfile;
use PCK\VendorRegistration\VendorProfileRemark;
use PCK\VendorRegistration\Section;
use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\ObjectField\ObjectField;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;
use PCK\Subsidiaries\Subsidiary;
use PCK\Reports\VendorPerformanceEvaluationFormExcelGenerator;
use PCK\Reports\VendorPreQualificationFormExcelGenerator;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use PCK\Helpers\StringOperations;

use PCK\Forms\VendorCompanyDetailsForm;
use PCK\Forms\VendorForm;
use PCK\Helpers\DBTransaction;
use PCK\ObjectLog\ObjectLog;
use PCK\BuildingInformationModelling\BuildingInformationModellingLevel;
use PCK\CIDBCodes\CIDBCode;
use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\LetterOfAward;
use PCK\ConsultantManagement\ConsultantManagementSubsidiary;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;

use PCK\CIDBGrades\CIDBGrade;
use PCK\VendorRegistration\VendorProfileRepository;

class VendorProfilesController extends \BaseController
{
    protected $companyDetailsForm;
    protected $vendorForm;
    protected $weightedNodeRepository;
    protected $vendorProfileRepository;

    public function __construct(VendorCompanyDetailsForm $companyDetailsForm, VendorForm $vendorForm, WeightedNodeRepository $weightedNodeRepository, VendorProfileRepository $vendorProfileRepository)
    {
        $this->companyDetailsForm     = $companyDetailsForm;
        $this->vendorForm             = $vendorForm;
        $this->weightedNodeRepository = $weightedNodeRepository;
        $this->vendorProfileRepository = $vendorProfileRepository;
    }

    public function index()
    {
        $contractGroups = ContractGroupCategory::select('id', 'name AS description')
            ->whereNotIn('name', ContractGroupCategory::getPrivateGroupNames())
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

        $allTags = Tag::where('category', '=', Tag::CATEGORY_VENDOR_PROFILE)
            ->orderBy('name', 'asc')
            ->get();

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

        $vendorStatusTextFilterOptions = [
            0 => trans('general.all'),
            Company::STATUS_ACTIVE      => trans('general.active'),
            Company::STATUS_EXPIRED     => trans('general.expired'),
            Company::STATUS_DEACTIVATED => trans('general.deactivated'),
        ];

        $globalPreQGrade = VendorRegistrationAndPrequalificationModuleParameter::first()->vendorManagementGrade;

        $gradeFilterOptions = [0 => trans('general.all')];

        if($globalPreQGrade)
        {
            foreach($globalPreQGrade->levels()->orderBy('score_upper_limit', 'asc')->get() as $gradeLevel)
            {
                $gradeFilterOptions[$gradeLevel->id] = $gradeLevel->description;
            }
        }

        $companyStatusDescriptions = Company::getCompanyStatusDescriptions();

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

        return View::make('vendor_profile.index', compact('contractGroups', 'externalVendorGroupsFilterOptions', 'allTags', 'statusFilterOptions', 'submissionTypeFilterOptions', 'gradeFilterOptions', 'vendorStatusTextFilterOptions', 'companyStatusDescriptions', 'cidbGradeFilterOptions', 'bimLevelFilterOptions'));
    }

    public function list()
    {
        $request = Request::instance();

        list($data, $totalPages) = $this->vendorProfileRepository->list($request);

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    // TODO temporarily kept for reference
    public function list_old()
    {
        $request = Request::instance();

        /*
         * Laravel 4 paginate() sucks. It does not handle paginate by page
         * This should be made into Paginator repository class but for now
         * I just implement it in controller. We should move this into its
         * own class and refactor all our listing queries to use this pagination
         * method.
         */
        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Company::select("companies.id AS id", "companies.name", "companies.activation_date", \DB::raw("TO_CHAR(companies.activation_date, 'DD/MM/YYYY') as activation_date_formatted"), \DB::raw("TO_CHAR(companies.expiry_date, 'DD/MM/YYYY') as expiry_date_formatted"), "companies.expiry_date", "companies.deactivated_at", "companies.reference_no", "states.id AS state_id", "countries.id AS country_id", \DB::raw("ROUND(AVG(vendor_pre_qualifications.score),0) AS avg_score"), "companies.vendor_status", "contract_group_categories.name as vendor_group", "companies.cidb_grade AS cidb_grade", "building_information_modelling_levels.name AS bim_level")
        ->where('companies.confirmed', '=', true)
        ->where('contract_group_categories.hidden', '=', false)
        ->where('contract_group_categories.type', '=', ContractGroupCategory::TYPE_EXTERNAL)
        ->with('vendorProfile')
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
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->leftJoin('company_vendor_category', 'company_vendor_category.company_id', '=', 'companies.id')
        ->leftJoin(\DB::raw("(SELECT * FROM vendor_categories WHERE hidden IS FALSE) vendor_categories"), 'vendor_categories.id', '=', 'company_vendor_category.vendor_category_id')
        ->leftJoin('building_information_modelling_levels', 'building_information_modelling_levels.id', '=', 'companies.bim_level_id')
        ->leftJoin('vendors', 'vendors.company_id', '=', 'companies.id')
        ->leftJoin('vendor_work_categories', function($join){
            $join->on('vendor_work_categories.id', '=', 'vendors.vendor_work_category_id');
            $join->on('vendor_work_categories.hidden', \DB::raw('IS NOT'), \DB::raw('TRUE'));
        })
        ->leftJoin('states', 'companies.state_id', '=', 'states.id')
        ->leftJoin('countries', 'companies.country_id', '=', 'countries.id')
        ->leftJoin('vendor_pre_qualifications', 'vendor_pre_qualifications.vendor_registration_id', '=', 'vr_final.id');

        $tagIds = [];
        $tagNameSql = null;

        //advanced search
        if(strlen(trim($request->get('criteria_search_str'))) > 0)
        {
            $searchStr = '%'.urldecode(trim($request->get('criteria_search_str'))).'%';
            
            switch($request->get('search_criteria'))
            {
                case 'company_name':
                    $model->where('companies.name', 'ILIKE', $searchStr);
                    break;
                case 'reference_no':
                    $model->where('companies.reference_no', 'ILIKE', $searchStr);
                    break;
                case 'vendor_category':
                    if(trim($request->get('criteria_search_str')) == '(blanks)')
                    {
                        $model->whereRaw("NOT EXISTS (
                            SELECT company_vendor_category.company_id
                            FROM company_vendor_category
                            JOIN vendor_categories ON company_vendor_category.vendor_category_id = vendor_categories.id
                            WHERE company_vendor_category.company_id = companies.id
                            AND vendor_categories.contract_group_category_id = companies.contract_group_category_id
                            AND vendor_categories.hidden IS FALSE
                            GROUP BY company_vendor_category.company_id
                        )");
                    }
                    else
                    {
                        $model->whereRaw("EXISTS (
                            SELECT company_vendor_category.company_id
                            FROM company_vendor_category
                            JOIN vendor_categories ON company_vendor_category.vendor_category_id = vendor_categories.id
                            WHERE company_vendor_category.company_id = companies.id
                            AND vendor_categories.contract_group_category_id = companies.contract_group_category_id
                            AND vendor_categories.name ILIKE ?
                            AND vendor_categories.hidden IS FALSE
                            GROUP BY company_vendor_category.company_id
                        )", [$searchStr] );
                    }
                    break;
                case 'vendor_work_category':
                    if(trim($request->get('criteria_search_str')) == '(blanks)')
                    {
                        $model->whereRaw("NOT EXISTS (
                            SELECT vendors.company_id
                            FROM vendors
                            JOIN vendor_work_categories ON vendors.vendor_work_category_id = vendor_work_categories.id
                            WHERE vendors.company_id = companies.id
                            AND vendor_work_categories.hidden IS FALSE
                            GROUP BY vendors.company_id
                        )");
                    }else{
                        $model->whereRaw("EXISTS (
                            SELECT vendors.company_id
                            FROM vendors
                            JOIN vendor_work_categories ON vendors.vendor_work_category_id = vendor_work_categories.id
                            WHERE vendors.company_id = companies.id
                            AND vendor_work_categories.name ILIKE ?
                            AND vendor_work_categories.hidden IS FALSE
                            GROUP BY vendors.company_id
                        )", [$searchStr] );
                    }
                    
                    break;
                case 'state':
                    $model->where('states.name', 'ILIKE', $searchStr);
                    break;
            }
        }

        if($request->has('contract_group_category_id') && (int)$request->get('contract_group_category_id') > 0)
        {
            $model->where('companies.contract_group_category_id', '=', (int)$request->get('contract_group_category_id'));
        }

        if($request->has('company_status'))
        {
            $model->whereIn('companies.company_status', $request->get('company_status'));
        }

        if(($request->has('activation_date_from') and strlen(trim($request->get('activation_date_from'))) > 0) and ($request->has('activation_date_to') and strlen(trim($request->get('activation_date_to'))) > 0))
        {
            $activationDateFrom = date('Y-m-d', strtotime(trim($request->get('activation_date_from'))));
            $activationDateTo = date('Y-m-d', strtotime(trim($request->get('activation_date_to'))));

            $model->whereRaw('companies.activation_date BETWEEN ? AND ?', [$activationDateFrom, $activationDateTo]);
        }

        if(($request->has('expiry_date_from') and strlen(trim($request->get('expiry_date_from'))) > 0) and ($request->has('expiry_date_to') and strlen(trim($request->get('expiry_date_to'))) > 0))
        {
            $expiryDateFrom = date('Y-m-d', strtotime(trim($request->get('expiry_date_from'))));
            $expiryDateTo = date('Y-m-d', strtotime(trim($request->get('expiry_date_to'))));

            $model->whereRaw('companies.expiry_date BETWEEN ? AND ?', [$expiryDateFrom, $expiryDateTo]);
        }

        if(($request->has('deactivation_date_from') and strlen(trim($request->get('deactivation_date_from'))) > 0) and ($request->has('deactivation_date_to') and strlen(trim($request->get('deactivation_date_to'))) > 0))
        {
            $deactivationDateFrom = date('Y-m-d', strtotime(trim($request->get('deactivation_date_from'))));
            $deactivationDateTo = date('Y-m-d', strtotime(trim($request->get('deactivation_date_to'))));

            $model->whereRaw('companies.deactivation_date BETWEEN ? AND ?', [$deactivationDateFrom, $deactivationDateTo]);
        }

        if($request->has('vendor_status') && strlen(trim($request->get('vendor_status')) > 0))
        {
            switch(trim($request->get('vendor_status')))
            {
                case Company::STATUS_ACTIVE:
                    $model->whereNotNull('activation_date')->where(function($query) {
                        $query->whereNull('expiry_date')->orWhere('expiry_date', '>', 'NOW()');
                    })->whereNull('deactivated_at');
                    break;
                case Company::STATUS_EXPIRED:
                    $model->whereNotNull('expiry_date')->where('expiry_date', '<=', 'NOW()')->whereNull('deactivated_at');
                    break;
                case Company::STATUS_DEACTIVATED:
                    $model->whereNotNull('activation_date')->whereNotNull('deactivated_at');
                    break;
            }
        }

        $globalPreQGrade = VendorRegistrationAndPrequalificationModuleParameter::first()->vendorManagementGrade;

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
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

                            $model->where(\DB::raw("'" . $vendorCodePrefix . "' || LPAD(companies.id::text, " . $vendorCodePadLength . ", '0')"), 'ILIKE', '%' . $val . '%');
                        }
                        break;
                    case 'vendor_group':
                        if((int)$val > 0)
                        {
                            $model->where('contract_group_categories.id', '=', $val);
                        }
                        break;
                    case 'activationdate':
                        if(strlen($val) > 0)
                        {
                            $model->where(\DB::raw("TO_CHAR(companies.activation_date, 'DD/MM/YYYY')"), 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'expirydate':
                        if(strlen($val) > 0)
                        {
                            $model->where(\DB::raw("TO_CHAR(companies.expiry_date, 'DD/MM/YYYY')"), 'ILIKE', '%'.$val.'%');
                        }
                        break;
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
                    case 'state':
                        if(strlen($val) > 0)
                        {
                            $model->where('states.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'country':
                        if(strlen($val) > 0)
                        {
                            $model->where('countries.country', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'tids':
                        if(strlen($val) > 0)
                        {
                            $tagIds[] = (int)$val;
                        }
                        break;
                    case 'tags':
                        if(strlen($val) > 0)
                        {
                            $tagNameSql = " AND tags.name ILIKE '%".$val."%' ";
                        }
                        break;
                    case 'vendor_status_text':
                        if((int)$val > 0)
                        {
                            switch($val)
                            {
                                case Company::STATUS_ACTIVE:
                                    $model->whereNotNull('activation_date')->where(function($query) {
                                        $query->whereNull('expiry_date')->orWhere('expiry_date', '>', 'NOW()');
                                    })->whereNull('deactivated_at');
                                    break;
                                case Company::STATUS_EXPIRED:
                                    $model->whereNotNull('expiry_date')->where('expiry_date', '<=', 'NOW()')->whereNull('deactivated_at');
                                    break;
                                case Company::STATUS_DEACTIVATED:
                                    $model->whereNotNull('activation_date')->whereNotNull('deactivated_at');
                                    break;
                            }
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
                    case 'prequalificationgrade':
                        if((int)$val > 0)
                        {
                            $ranges = $globalPreQGrade->getLevelRanges();

                            $model->having(\DB::raw("ROUND(AVG(vendor_pre_qualifications.score))"), '>', $ranges[$val]['min']);
                            $model->having(\DB::raw("ROUND(AVG(vendor_pre_qualifications.score))"), '<=', $ranges[$val]['max']);
                        }
                        break;
                    case 'cidbgrade':
                        if($val)
                        {
                            $cidbGrade = CIDBGrade::where("grade",$val)->first();

                            $model->where('companies.cidb_grade', (int) $cidbGrade->id);
                        }
                        break;
                    case 'biminformation':
                        if((int) $val > 0)
                        {
                            $model->where('companies.bim_level_id', (int) $val);
                        }
                        else
                        {
                            if($val)
                            {
                                $bimLevel = BuildingInformationModellingLevel::where("name", $val)->first();

                                if($bimLevel)
                                {
                                    $model->where('companies.bim_level_id', (int) $bimLevel->id);
                                }
                            }
                        }
                        break;
                }
            }
        }

        if($tagIds or $tagNameSql)
        {
            $tagIdsSql = null;

            if($tagIds)
            {
                $tagIds = Tag::whereIn('id', $tagIds)->lists('id');

                if(!empty($tagIds))
                {
                    $tagIdsSql = " AND tags.id IN (".implode(',', $tagIds).") ";
                }
            }

            $model->whereRaw("
                EXISTS (
                    SELECT object_tags.object_id
                    FROM object_tags
                    JOIN tags ON object_tags.tag_id = tags.id AND tags.category = ".Tag::CATEGORY_VENDOR_PROFILE."
                    WHERE object_tags.object_class = '".get_class(new Company)."'
                    AND object_tags.object_id = companies.id
                    ".$tagIdsSql."
                    ".$tagNameSql."
                )
            ");
        }

        $model->orderBy('companies.id', 'desc')
        ->orderBy('companies.name', 'desc')
        ->groupBy(\DB::raw('companies.id, vr.company_id, vr.revision, vr_status.status, vr_status.submission_type, states.id, countries.id, contract_group_categories.name, building_information_modelling_levels.id'));

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $objectTagNames = ObjectTag::getTagNames($record, Tag::CATEGORY_VENDOR_PROFILE);
            $counter = ($page-1) * $limit + $key + 1;

            $cidbGrade = NULL;

            if($record->cidb_grade)
            {
                if(CIDBGrade::find($record->cidb_grade))
                {
                    $cidbGrade = CIDBGrade::find($record->cidb_grade)->grade;
                }
            }

            $data[] = [
                'id'                      => $record->id,
                'counter'                 => $counter,
                'name'                    => $record->name,
                'vendor_code'             => $record->getVendorCode(),
                'reference_no'            => $record->reference_no,
                'state'                   => $record->State->name,
                'country'                 => $record->Country->country,
                'route:show'              => $record->vendorProfile ? route('vendorProfile.show', array($record->id)) : null,
                'tagging'                 => null,
                'preQualification'        => null,
                'appraisalScore'          => null,
                'bimLevel'                => null,
                'activationDate'          => $record->activation_date_formatted,
                'expiryDate'              => $record->expiry_date_formatted,
                'reputableDevelopers'     => null,
                'vendor_group'            => $record->vendor_group,
                'vendorSubworkCategory'   => null,
                'remarks'                 => null,
                'vendor_status_text'      => $record->getStatusText(),
                'vendor_status'           => $record->getStatus(),
                'tags'                    => implode(' ', $objectTagNames),
                'tagsArray'               => $objectTagNames,
                'cidbGrade'               => is_null($record->cidb_grade) ? null : $cidbGrade,
                'bimInformation'          => $record->bim_level,
                'status'                  => $record->vendorRegistration->status_text,
                'submission_type_text'    => $record->vendorRegistration->submission_type_text,
                'submission_type'         => $record->vendorRegistration->submission_type,
                'pre_qualification_score' => $record->avg_score,
                'pre_qualification_grade' => $record->avg_score && $globalPreQGrade ? $globalPreQGrade->getGrade($record->avg_score)->description : null,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function show($companyId)
    {
        $user    = \Confide::user();
        $company = Company::findOrFail($companyId);

        $allTags = Tag::where('category', '=', Tag::CATEGORY_VENDOR_PROFILE)
            ->orderBy('name', 'asc')
            ->get();

        $objectTagIds = ObjectTag::getTagIds($company, Tag::CATEGORY_VENDOR_PROFILE);

        $vendorRegistrationDetails = [];

        $formObjectMappping = FormObjectMapping::findRecord($company->finalVendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

        if($formObjectMappping)
        {
            $formElementIds = $formObjectMappping->dynamicForm->getAllFormElementIdsGroupedByType();

            $elementDisplayInfo = [];
    
            foreach($formElementIds[Element::ELEMENT_TYPE_ID] as $id)
            {
                $element = Element::findById($id);
                array_push($vendorRegistrationDetails, $element->getSavedValuesDisplay());
            }
    
            foreach($formElementIds[SystemModuleElement::ELEMENT_TYPE_ID] as $id)
            {
                $systemElement = SystemModuleElement::find($id);
                array_push($vendorRegistrationDetails, $systemElement->getSavedValuesDisplay());
            }
        }

        $vendorProfile = $company->vendorProfile;

        $me = \Auth::user();
        $isVendor            = ($me->company && $company->id == $me->company->id);
        $isVendorProfileUser = VendorManagementUserPermission::hasPermission($user, VendorManagementUserPermission::TYPE_VENDOR_PROFILE_EDIT);

        if ($me->isSuperAdmin()) {
            $isInternalVendor = true;
        } elseif ($me->company) {
            $isInternalVendor = ($me->company->contractGroupCategory->isTypeInternal());
        } else {
            $isInternalVendor = false;
        }

        $vendorDetailsAttachmentSetting = VendorDetailAttachmentSetting::first();

        $assignedVerifierRecords = Verifier::getAssignedVerifierRecords($company->finalVendorRegistration, true);

        return View::make('vendor_profile.show', compact(
            'user',
            'company',
            'allTags',
            'objectTagIds',
            'vendorRegistrationDetails',
            'vendorProfile',
            'isVendor',
            'isVendorProfileUser',
            'isInternalVendor',
            'vendorDetailsAttachmentSetting',
            'assignedVerifierRecords'
        ));
    }

    public function edit($companyId)
    {
        $user    = \Confide::user();
        $company = Company::findOrFail($companyId);

        $urlCountry = route('country');
        $urlStates  = route('country.states');
        $countryId  = Input::old('country_id', $company->country_id);
        $stateId    = Input::old('state_id', $company->state_id);

        $urlContractGroupCategories = route('registration.externalVendors.contractGroupCategories');
        $urlVendorCategories        = route('registration.vendorCategories');

        $allTags = Tag::where('category', '=', Tag::CATEGORY_VENDOR_PROFILE)
            ->orderBy('name', 'asc')
            ->get();

        $objectTagIds = ObjectTag::getTagIds($company, Tag::CATEGORY_VENDOR_PROFILE);

        $multipleVendorCategories = VendorRegistrationAndPrequalificationModuleParameter::getValue('allow_only_one_comp_to_reg_under_multi_vendor_category');

        JavaScript::put(compact('urlCountry', 'urlStates', 'countryId', 'stateId', 'urlContractGroupCategories', 'urlVendorCategories'));

        $companyStatusDescriptions = Company::getCompanyStatusDescriptions();

        if(CIDBGrade::count() > 0)
        {
            $cidb_grades = CIDBGrade::orderBy('id', 'ASC')->get();
        }

        if(CIDBCode::count() > 0)
        {

            $cidbCodeParents = CIDBCode::where("parent_id", null)->orderBy('id', 'ASC')->get();

            if ($cidbCodeParents) 
            {
                foreach ($cidbCodeParents as $cidbCodeParent) 
                {
                    $cidbCodeParent->children = CIDBCode::where("parent_id", $cidbCodeParent->id)->orderBy('id', 'ASC')->get();

                    if ($cidbCodeParent->children) 
                    {
                        foreach ($cidbCodeParent->children as $cidbCodeChildren) 
                        {
                            $cidbCodeChildren->subChildren = CIDBCode::where("parent_id", $cidbCodeChildren->id)->orderBy('id', 'ASC')->get();
                        }
                    }
                }
            }
        }

        $selectedVendorCategoryIds = Input::old('vendor_category_id') ?? $company->vendorCategories()->lists('id');

        $selectedCidbCodeIds = [];

        foreach($company->cidbCodes as $cidbCode)
        {
            $selectedCidbCodeIds[] = $cidbCode->id;
        }

        $cidbCodes = CIDBCode::getCidbCodes();

        return View::make('vendor_profile.edit', compact(
            'user',
            'company',
            'allTags',
            'objectTagIds',
            'multipleVendorCategories',
            'companyStatusDescriptions', 
            'cidbCodeParents',
            'selectedVendorCategoryIds',
            'cidbCodes',
            'cidb_grades',
            'selectedCidbCodeIds'
        ));
    }

    public function companyDetailsStore()
    {
        $request = Request::instance();

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $user    = \Confide::user();
            $company = Company::findOrFail($request->get('id'));

            $this->companyDetailsForm->setCompany($company);
            $this->companyDetailsForm->validate($request->all());

            //only handles update for now
            if($company)
            {
                $vendorGroupChanged = (trim($request->get('contract_group_category_id')) != trim($company->contract_group_category_id));

                $company->name = mb_strtoupper(trim($request->get('name')));
                $company->reference_no = mb_strtoupper(trim($request->get('reference_no')));
                $company->address = $request->get('address');
                $company->country_id = (int)$request->get('country_id');
                $company->state_id = (int)$request->get('state_id');
                $company->contract_group_category_id = (int)$request->get('contract_group_category_id');
                $company->tax_registration_no = trim($request->get('tax_registration_no'));
                $company->main_contact = mb_strtoupper(trim($request->get('main_contact')));
                $company->email = trim($request->get('email'));
                $company->telephone_number = trim($request->get('telephone_number'));
                $company->fax_number = trim($request->get('fax_number'));
                $company->cidb_grade = $request->has('cidb_grade') ? $request->get('cidb_grade') : null;
                $company->bim_level_id = $request->has('bim_level_id') ? $request->get('bim_level_id') : null;
                $company->company_status = $request->get('company_status');
                $company->bumiputera_equity = $request->get('bumiputera_equity');
                $company->non_bumiputera_equity = $request->get('non_bumiputera_equity');
                $company->foreigner_equity = $request->get('foreigner_equity');

                if($request->get('activation_date'))
                {
                    $company->activation_date = date('Y-m-d H:i:s', strtotime($request->get('activation_date')));
                }

                if($request->get('expiry_date'))
                {
                    $company->expiry_date = date('Y-m-d H:i:s', strtotime($request->get('expiry_date')));
                }

                $company->save();

                // reload company model
                $company = Company::find($company->id);

                if($vendorGroupChanged)
                {
                    $company->flushRelatedVendorRegistrationData();

                    FormObjectMapping::createAndBindVendorRegistrationForm($company);
                }

                $company->vendorCategories()->sync($request->get('vendor_category_id'));

                if($request->get('cidb_code_id'))
                {
                    $company->cidbCodes()->sync($request->get('cidb_code_id'));
                }

            }

            ObjectLog::recordAction($company->vendorProfile, ObjectLog::ACTION_EDIT);

            $transaction->commit();

            \Log::info("Update company details from vendor profile module [company id:{$company->id}][user id:{$user->id}]");

            \Flash::success(trans('forms.saved'));
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            \Flash::error(trans('forms.customValidationError'));

            return Redirect::back()
                ->withErrors($this->companyDetailsForm->getErrors(), 'company')
                ->withInput(Input::all());
        }

        return Redirect::route('vendorProfile.show', [$company->id]);
    }

    public function vendorStore()
    {
        $request = Request::instance();

        try
        {
            $this->vendorForm->validate($request->all());
        }
        catch(\Exception $e)
        {
            $errors = [];
            foreach($this->vendorForm->getErrors()->toArray() as $key => $errorMsg)
            {
                $errors[] = [
                    'key' => $key,
                    'msg' => $errorMsg[0]
                ];
            }

            return Response::json([
                'success' => false,
                'errors' => $errors
            ]);
        }

        if($request->has('vendor'))
        {
            $req = $request->get('vendor');
        
            $user    = \Confide::user();
            $company = Company::findOrFail($request->get('cid'));
            $vendor = Vendor::find($request->get('id'));

            $vendorWorkCategory = VendorWorkCategory::findOrFail($req['vendor_work_category_id']);
            
            $isNew = false;
            if(!$vendor)
            {
                $isNew = true;
                $vendor = new Vendor();
                $vendor->company_id = $company->id;
             }

            $vendor->is_qualified = array_key_exists('is_qualified', $req);
            $vendor->vendor_work_category_id = $vendorWorkCategory->id;

            $vendor->type = $req['type'];
            
            if($req['type'] == Vendor::TYPE_WATCH_LIST)
            {
                $vendor->watch_list_entry_date = date('Y-m-d H:i:s', strtotime($req['watch_list_entry_date']));
                $vendor->watch_list_release_date = date('Y-m-d H:i:s', strtotime($req['watch_list_release_date']));
            }
            else
            {
                $vendor->watch_list_entry_date = null;
                $vendor->watch_list_release_date = null;
            }

            $vendor->save();

            if($isNew)
            {
                //need to reset the type for new record because the type is overwrite in boot function when creating new record
                $vendor->type = $req['type'];
                $vendor->save();
            }

            $company->syncVendorWorkCategorySetups();

            ObjectLog::recordAction($company->vendorProfile, ObjectLog::ACTION_EDIT);
            
            \Log::info("Update vendor work categories from vendor profile module [company id:{$company->id}][vendor work category id:{$vendorWorkCategory->id}][user id:{$user->id}]");
        }
        
        return Response::json([
            'success' => true,
            'errors'  => []
        ]);
    }

    public function vendorEdit($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);

        $vendorCategory = VendorCategory::select("vendor_categories.id AS id", "vendor_categories.name AS name", "vendor_work_categories.id AS vendor_work_category_id")
        ->join('company_vendor_category', 'company_vendor_category.vendor_category_id', '=', 'vendor_categories.id')
        ->join('companies', 'companies.id', '=', 'company_vendor_category.company_id')
        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
        ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
        ->join('vendor_category_vendor_work_category', function($join){
            $join->on('vendor_category_vendor_work_category.vendor_category_id', '=', 'vendor_categories.id');
            $join->on('vendor_category_vendor_work_category.vendor_work_category_id','=', 'vendor_work_categories.id');
        })
        ->where('companies.id', $vendor->company_id)
        ->where('vendor_work_categories.id', $vendor->vendor_work_category_id)
        ->orderBy('vendor_categories.name', 'asc')
        ->first();

        return Response::json([
            'id' => $vendor->id,
            'is_qualified' => $vendor->is_qualified,
            'vendor_category_id' => ($vendorCategory) ? $vendorCategory->id : -1,
            'vendor_work_category_id' => $vendor->vendor_work_category_id,
            'vendor_work_category_name' => $vendor->vendorWorkCategory->name,
            'company_id' => $vendor->company_id,
            'type' => $vendor->type,
            'watch_list_entry_date' => ($vendor->type == Vendor::TYPE_WATCH_LIST && $vendor->watch_list_entry_date) ? date('Y-m-d', strtotime($vendor->watch_list_entry_date)) : null,
            'watch_list_release_date' => ($vendor->type == Vendor::TYPE_WATCH_LIST && $vendor->watch_list_release_date) ? date('Y-m-d', strtotime($vendor->watch_list_release_date)) : null
        ]);
    }

    public function vendorDelete($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $company = Company::findOrFail($vendor->company_id);
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendor->vendor_work_category_id);
        $user = \Confide::user();

        if($vendor->canBeDeleted())
        {
            $vendor->delete();

            $company->syncVendorWorkCategorySetups();

            ObjectLog::recordAction($company->vendorProfile, ObjectLog::ACTION_EDIT);

            \Log::info("Delete vendor from vendor profile module [vendor id: {$vendorId}] company id:{$company->id}][vendor work category id:{$vendorWorkCategory->id}][user id:{$user->id}]");
        }

        return Response::json([
            'success' => true
        ]);
    }

    public function deactivate($companyId)
    {
        $company = Company::findOrFail($companyId);
        $user = \Confide::user();

        $request = Request::instance();

        $company->expiry_date = date('Y-m-d H:i:s', strtotime($request->get('expiry_date')));
        $company->deactivation_date = date('Y-m-d H:i:s', strtotime($request->get('deactivation_date')));
        $company->deactivated_at = date('Y-m-d H:i:s', strtotime($request->get('deactivation_date')));

        $company->save();

        ObjectLog::recordAction($company->vendorProfile, ObjectLog::ACTION_EDIT);

        \Log::info("Deactivate company from vendor profile module company id:{$company->id}][user id:{$user->id}]");

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorProfile.show', [$company->id]);
    }

    public function activate($companyId)
    {
        $company = Company::findOrFail($companyId);
        $user = \Confide::user();

        $company->deactivation_date = null;
        $company->deactivated_at = null;

        $company->save();

        ObjectLog::recordAction($company->vendorProfile, ObjectLog::ACTION_EDIT);

        \Log::info("Activate company from vendor profile module company id:{$company->id}][user id:{$user->id}]");

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorProfile.show', [$company->id]);
    }

    public function vendorList($companyId)
    {
        $company = Company::findOrFail($companyId);
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Vendor::select("vendors.id AS id", "companies.id AS company_id", "vendor_work_categories.id as vendor_work_category_id",
        "vendor_work_categories.name AS vendor_work_category_name", "vendors.type", "vendors.is_qualified", "vendors.vendor_evaluation_cycle_score_id")
        ->join('companies', 'companies.id', '=', 'vendors.company_id')
        ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
        ->where('companies.id', $company->id)
        ->orderBy('vendors.is_qualified', 'desc')
        ->orderBy('vendor_work_categories.code', 'asc')
        ->orderBy('vendor_work_categories.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        $vendorCategoryRecords = VendorCategory::select("vendor_categories.id AS id", "vendor_categories.name AS name", "vendor_work_categories.id AS vendor_work_category_id")
        ->join('company_vendor_category', 'company_vendor_category.vendor_category_id', '=', 'vendor_categories.id')
        ->join('companies', 'companies.id', '=', 'company_vendor_category.company_id')
        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
        ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
        ->join('vendor_category_vendor_work_category', function($join){
            $join->on('vendor_category_vendor_work_category.vendor_category_id', '=', 'vendor_categories.id');
            $join->on('vendor_category_vendor_work_category.vendor_work_category_id','=', 'vendor_work_categories.id');
        })
        ->where('companies.id', $company->id)
        ->orderBy('vendor_categories.code', 'asc')
        ->orderBy('vendor_categories.name', 'asc')
        ->get();

        $vendorCategories = [];
        foreach($vendorCategoryRecords as $vendorCategoryRecord)
        {
            if(!array_key_exists($vendorCategoryRecord->id, $vendorCategories))
            {
                $vendorCategories[$vendorCategoryRecord->id] = [
                    'name' => $vendorCategoryRecord->name,
                    'work_categories' => []
                ];
            }

            $vendorCategories[$vendorCategoryRecord->id]['work_categories'][] = $vendorCategoryRecord->vendor_work_category_id;
        }

        $trackRecordProjectVendorWorkSubCategories = Vendor::getTrackRecordProjectVendorWorkSubCategories([$company->id]);

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;
            $vendorCategoryNames = [];

            foreach($vendorCategories as $vendorCategoryId => $vendorCategory)
            {
                if(in_array($record->vendor_work_category_id, $vendorCategory['work_categories']))
                {
                    $vendorCategoryNames[] = $vendorCategory['name'];
                }
            }

            $vendorWorkSubCategories = $trackRecordProjectVendorWorkSubCategories[$record->company_id][$record->vendor_work_category_id];

            $data[] = [
                'id'                        => $record->id,
                'counter'                   => $counter,
                'company_id'                => $record->company_id,
                'vendor_categories'         => $vendorCategoryNames,
                'vendor_work_category_id'   => $record->vendor_work_category_id,
                'vendor_work_category_name' => $record->vendor_work_category_name,
                'vendor_work_subcategories' => is_null($vendorWorkSubCategories['names']) ? [] : $vendorWorkSubCategories['names'],
                'qualified'                 => ($record->is_qualified) ? trans('general.yes') : trans('general.no'),
                'status'                    => $record->type_name,
                'can_be_deleted'            => $record->canBeDeleted()
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function archivedStorage($companyId)
    {
        $company = Company::findOrFail($companyId);

        $request = Request::instance();

        $path = ($request->has('path') && strlen($request->get('path')) > 0) ? $request->get('path') : null;

        $items = [];
        $directories = [];
        $files = [];

        if(!$path)//first level - main directories
        {
            list($directories, $files) = $company->getArchivedStorage();

            if($company->hasExternalAppAttachments())
            {
                $directories[] = [
                    'id' => 'EXT_APP_ATTCH',
                    'basename' => getenv('EXTERNAL_APP_ATTACHMENT_LABEL'),
                    'dirname' => getenv('EXTERNAL_APP_ATTACHMENT_PATH'),
                    'path' => getenv('EXTERNAL_APP_ATTACHMENT_PATH'),
                    'type' => 'dir',
                    'extension' => 'Folder'
                ];
            }

            if($company->hasExternalAppCompanyAttachments())
            {
                $directories[] = [
                    'id' => 'EXT_APP_COMP_ATTCH',
                    'basename' => getenv('EXTERNAL_APP_COMPANY_ATTACHMENT_LABEL'),
                    'dirname' => getenv('EXTERNAL_APP_COMPANY_ATTACHMENT_PATH'),
                    'path' => getenv('EXTERNAL_APP_COMPANY_ATTACHMENT_PATH'),
                    'type' => 'dir',
                    'extension' => 'Folder'
                ];
            }
        }
        elseif($request->has('id') && ($path == getenv('EXTERNAL_APP_ATTACHMENT_PATH') || $path == getenv('EXTERNAL_APP_COMPANY_ATTACHMENT_PATH')))
        {
            switch($request->get('id'))
            {
                case 'EXT_APP_ATTCH':
                    $files = $company->getExternalAppAttachments();
                    break;
                case 'EXT_APP_COMP_ATTCH':
                    $files = $company->getExternalAppCompanyAttachments();
                    break;
            }
        }
        else
        {
            list($directories, $files) = $company->getArchivedStorage($path);
        }

        foreach($directories as $directory)
        {
            $items[] = $directory;
        }

        foreach($files as $file)
        {
            $items[] = $file;
        }

        return Response::json($items);
    }

    public function archivedStorageDownload($companyId)
    {
        $company = Company::findOrFail($companyId);

        $request = Request::instance();

        $path = ($request->has('path') && strlen($request->get('path')) > 0) ? $request->get('path') : null;

        $filename = trim($request->get('filename'));
        $extension = trim($request->get('ext'));

        if($request->has('id') && ($request->get('id') == 'EXT_APP_ATTCH' || $request->get('id') == 'EXT_APP_COMP_ATTCH'))
        {
            $filepath = $path;
        }
        else
        {
            $filepath = storage_path().DIRECTORY_SEPARATOR."vendor-archived".DIRECTORY_SEPARATOR.$company->id.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$filename;
        }

        return \PCK\Helpers\Files::download($filepath, basename($filename, ".".$extension).".".$extension);
    }
    
    public function awardedProjects($companyId)
    {
        $data = [];

        $records = CompanyProject::where('company_id', '=', $companyId)
            ->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::CONTRACTOR))
            ->orderBy('id', 'desc')
            ->get();

        $totalContractSums = [];

        foreach($records as $record)
        {
            if( ! $record->project ) continue;

            $contractSum = 0;

            if( $postContract = $record->project->getBsProjectMainInformation()->projectStructure->postContract ) $contractSum = $postContract->getContractSum();

            $data[] = [
                'id'          => $record->project->id,
                'name'        => $record->project->title,
                'status'      => Project::getStatusText($record->project->status_id),
                'contractSum' => number_format($contractSum, 2),
                'currency'    => $record->project->modified_currency_code,
                'showDetails' => false,
            ];

            $totalContractSums[ $record->project->modified_currency_code ] = $totalContractSums[ $record->project->modified_currency_code ] ?? 0;

            $totalContractSums[ $record->project->modified_currency_code ] += $contractSum;
        }

        // consultant management projects
        $consultantManagementContracts = ConsultantManagementContract::select('consultant_management_contracts.*')
                                            ->distinct()
                                            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
                                            ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
                                            ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
                                            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
                                            ->where('consultant_management_consultant_rfp.company_id', $companyId)
                                            ->whereRaw('consultant_management_consultant_rfp.awarded IS TRUE')
                                            ->where('consultant_management_letter_of_awards.status', LetterOfAward::STATUS_APPROVED)
                                            ->get();

        $proposedFeeList = ConsultantManagementSubsidiary::select("consultant_management_subsidiaries.consultant_management_contract_id AS contract_id", \DB::raw("SUM(consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount) AS proposed_fee"))
                            ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_subsidiary_id', '=', 'consultant_management_subsidiaries.id')
                            ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
                            ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id')
                            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.vendor_category_rfp_id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
                            ->where('consultant_management_consultant_rfp.company_id', '=', $companyId)
                            ->whereIn('consultant_management_subsidiaries.consultant_management_contract_id', $consultantManagementContracts->lists('id'))
                            ->whereRaw('consultant_management_consultant_rfp.awarded IS TRUE')
                            ->where('consultant_management_letter_of_awards.status', LetterOfAward::STATUS_APPROVED)
                            ->groupBy('consultant_management_subsidiaries.consultant_management_contract_id')
                            ->lists('proposed_fee', 'contract_id');

        foreach($consultantManagementContracts as $contract)
        {
            $proposedFeeSum  = array_key_exists($contract->id, $proposedFeeList) ? $proposedFeeList[$contract->id] : 0.0;
            $currencyCode    = empty($contract->modified_currency_code) ? $contract->country->currency_code : $contract->modified_currency_code;

            $data[] = [
                'id'             => $contract->id,
                'name'           => $contract->title,
                'status'         => trans('general.awarded'),
                'contractSum'    => number_format($proposedFeeSum, 2),
                'currency'       => $currencyCode,
                'showDetails'    => true,
                'showDetailsUrl' => route('consultant.contract.details.get', [$companyId, $contract->id]),
            ];

            $totalContractSums[ $currencyCode ] = $totalContractSums[ $currencyCode ] ?? 0;

            $totalContractSums[ $currencyCode ] += $proposedFeeSum;
        }

        $formattedTotalContractSums = [];

        foreach($totalContractSums as $currencyCode => $totalContractSum)
        {
            $totalContractSums[ $currencyCode ] = number_format($totalContractSum, 2);
        }

        ksort($totalContractSums);

        return Response::json([
            'data'        => $data,
            'contractSum' => $totalContractSums,
        ]);
    }

    public function completedProjects($companyId)
    {
        $data = [];

        $records = CompanyProject::where('company_id', '=', $companyId)
            ->whereHas('project', function($q){
                $q->where('status_id', '=', Project::STATUS_TYPE_COMPLETED);
            })
            ->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::CONTRACTOR))
            ->orderBy('id', 'desc')
            ->get();

        $totalContractSums = [];

        foreach($records as $record)
        {
            if( ! $record->project ) continue;

            $contractSum = 0;

            if( $postContract = $record->project->getBsProjectMainInformation()->projectStructure->postContract ) $contractSum = $postContract->getContractSum();

            $data[] = [
                'id'          => $record->project->id,
                'name'        => $record->project->title,
                'contractSum' => number_format($contractSum, 2),
                'currency'    => $record->project->modified_currency_code,
            ];

            $totalContractSums[ $record->project->modified_currency_code ] = $totalContractSums[ $record->project->modified_currency_code ] ?? 0;

            $totalContractSums[ $record->project->modified_currency_code ] += $contractSum;
        }

        $formattedTotalContractSums = [];

        foreach($totalContractSums as $currencyCode => $totalContractSum)
        {
            $totalContractSums[ $currencyCode ] = number_format($totalContractSum, 2);
        }

        ksort($totalContractSums);

        return Response::json([
            'data'        => $data,
            'contractSum' => $totalContractSums,
        ]);
    }

    public function syncTags($companyId)
    {
        $success  = false;
        $errorMsg = null;

        try
        {
            \PCK\Tag\ObjectTag::syncTags(Company::find($companyId), Tag::CATEGORY_VENDOR_PROFILE, Input::get('tags') ?? []);

            $success = true;
        }
        catch(\Exception $e)
        {
            $errorMsg = trans('general.somethingWentWrong');
            $errorMsg = $e->getMessage();
        }

        return Response::json([
            'success'  => $success,
            'errorMsg' => $errorMsg,
        ]);
    }

    public function companyPersonnelList($companyId, $type)
    {
        $company = Company::findOrFail($companyId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = CompanyPersonnel::select("company_personnel.*")
                    ->where('company_personnel.vendor_registration_id', '=', $company->finalVendorRegistration->id)
                    ->where('company_personnel.type', '=', $type);

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
                            $model->where('company_personnel.name', 'ILIKE', "%{$val}%");
                        }
                        break;
                    case 'identification_number':
                        if(strlen($val) > 0)
                        {
                            $model->where('company_personnel.identification_number', 'ILIKE', "%{$val}%");
                        }
                        break;
                    case 'email_address':
                        if(strlen($val) > 0)
                        {
                            $model->where('company_personnel.email_address', 'ILIKE', "%{$val}%");
                        }
                        break;
                    case 'contact_number':
                        if(strlen($val) > 0)
                        {
                            $model->where('company_personnel.contact_number', 'ILIKE', "%{$val}%");
                        }
                        break;
                    case 'years_of_experience':
                        if(strlen($val) > 0)
                        {
                            $model->where('company_personnel.years_of_experience', 'ILIKE', "%{$val}%");
                        }
                        break;
                    case 'designation':
                        if(strlen($val) > 0)
                        {
                            $model->where('company_personnel.designation', 'ILIKE', "%{$val}%");
                        }
                        break;
                    case 'amount_of_share':
                        if(strlen($val) > 0)
                        {
                            $model->where('company_personnel.amount_of_share', 'ILIKE', "%{$val}%");
                        }
                        break;
                    case 'holding_percentage':
                        if(strlen($val) > 0)
                        {
                            $model->where('company_personnel.holding_percentage', 'ILIKE', "%{$val}%");
                        }
                        break;
                }
            }
        }

        $model->orderBy('company_personnel.name', 'DESC');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                    => $record->id,
                'counter'               => $counter,
                'name'                  => $record->name,
                'identification_number' => $record->identification_number,
                'email_address'         => $record->email_address,
                'contact_number'        => $record->contact_number,
                'years_of_experience'   => $record->years_of_experience,
                'designation'           => $record->designation,
                'amount_of_share'       => $record->amount_of_share,
                'holding_percentage'    => $record->holding_percentage,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function trackRecordList($companyId, $type)
    {
        $company = Company::findOrFail($companyId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = TrackRecordProject::select("track_record_projects.*", "vendor_categories.name AS vendor_category_name",
        "vendor_work_categories.name AS vendor_work_category_name",
        "property_developers.name AS property_developer_name")
        ->join('vendor_registrations', 'vendor_registrations.id', '=', 'track_record_projects.vendor_registration_id')
        ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
            FROM vendor_registrations
            WHERE status = ".VendorRegistration::STATUS_COMPLETED."
            AND deleted_at IS NULL
            GROUP BY company_id) vr"), function($join){
            $join->on('vr.company_id', '=', 'vendor_registrations.company_id');
            $join->on('vr.revision', '=', 'vendor_registrations.revision');
        })
        ->join('companies', 'companies.id', '=', 'vr.company_id')
        ->join('vendor_categories', 'vendor_categories.id', '=', 'track_record_projects.vendor_category_id')
        ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'track_record_projects.vendor_work_category_id')
        ->leftJoin('property_developers', 'property_developers.id', '=', 'track_record_projects.property_developer_id')
        ->where('companies.id', '=', $company->id)
        ->where('track_record_projects.type', '=', $type)
        ->whereNull('vendor_registrations.deleted_at')
        ->orderBy('track_record_projects.title', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $projectTrackRecordIds = array_column($records->toArray(), 'id');

        if(count($projectTrackRecordIds) > 0)
        {
            $vendorSubWorkCategoryQuery = "SELECT trp.id, CASE WHEN TRIM(STRING_AGG(vws.name, ',')) IS NULL THEN '-' ELSE TRIM(STRING_AGG(vws.name, ',')) END AS vendor_sub_work_category
                                            FROM track_record_projects trp
                                            INNER JOIN vendor_categories vc ON vc.id = trp.vendor_category_id 
                                            INNER JOIN vendor_work_categories vwc ON vwc.id = trp.vendor_work_category_id 
                                            LEFT OUTER JOIN track_record_project_vendor_work_subcategories trpvws ON trpvws.track_record_project_id = trp.id
                                            LEFT OUTER JOIN vendor_work_subcategories vws ON vws.id = trpvws.vendor_work_subcategory_id 
                                            WHERE trp.id IN (" . implode(', ', $projectTrackRecordIds) . ")
                                            GROUP BY trp.id
                                            ORDER BY trp.id ASC";
    
            $vendorWorkSubCategories = DB::select(DB::raw($vendorSubWorkCategoryQuery));
    
            $vendoSubWorkCategortResults = [];
    
            foreach(\DB::select(\DB::raw($vendorSubWorkCategoryQuery)) as $result)
            {
                $vendoSubWorkCategortResults[$result->id] = $result->vendor_sub_work_category;
            }
        }

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $propertyDeveloperTxt = $record->property_developer_id ? trim($record->property_developer_name) : trim($record->property_developer_text);

            $yearOfSitePossession = Carbon::parse($record->year_of_site_possession)->format('Y');
            $yearOfCompletion = Carbon::parse($record->year_of_completion)->format('Y');

            $data[] = [
                'id'                           => $record->id,
                'counter'                      => $counter,
                'title'                        => $record->title,
                'property_developer_name'      => ($propertyDeveloperTxt) ? mb_strtoupper($propertyDeveloperTxt) : "-",
                'vendor_category_name'         => $record->vendor_category_name,
                'vendor_work_category_name'    => $record->vendor_work_category_name,
                'vendor_work_subcategory_name' => array_key_exists($record->id, $vendoSubWorkCategortResults) ? $vendoSubWorkCategortResults[$record->id] : '-',
                'year_of_site_possession'      => ((int)$yearOfSitePossession > 1980) ? $yearOfSitePossession : "-",
                'year_of_completion'           => ((int)$yearOfCompletion > 1980) ? $yearOfCompletion : "-",
                'qlassic_year_of_achievement'  => Carbon::parse($record->qlassic_year_of_achievement)->format('Y'),
                'conquas_year_of_achievement'  => Carbon::parse($record->conquas_year_of_achievement)->format('Y'),
                'year_of_recognition_awards'   => Carbon::parse($record->year_of_recognition_awards)->format('Y'),
                'has_qlassic_or_conquas_score' => $record->has_qlassic_or_conquas_score,
                'awards_received'              => $record->awards_received,
                'qlassic_score'                => $record->qlassic_score,
                'shassic_score'                => $record->shassic_score,
                'conquas_score'                => $record->conquas_score,
                'project_amount'               => $record->project_amount,
                'currency'                     => $record->country->currency_code,
                'project_amount_remarks'       => $record->project_amount_remarks,
                'remarks'                      => $record->remarks,
                'attachmentsCount'             => $record->attachments->count(),
                'route:getDownloads'           => route('vendorProfile.projectTrackRecord.downloads.get', [$record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorPrequalifictionList($companyId)
    {
        $company = Company::findOrFail($companyId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $vendorPreQualifications = VendorPreQualification::where('vendor_registration_id', '=', $company->finalVendorRegistration->id)
            ->whereNotNull('weighted_node_id')
            ->orderBy('id', 'ASC')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $grading = VendorRegistrationAndPrequalificationModuleParameter::first()->vendorManagementGrade;

        $data = [];

        foreach($vendorPreQualifications as $key => $vendorPreQualification)
        {
            $gradeLevel = null;

            if( $grading ) $gradeLevel = $grading->getGrade($vendorPreQualification->score);

            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                 => $vendorPreQualification->id,
                'counter'            => $counter,
                'form'               => $vendorPreQualification->weightedNode->name,
                'vendorWorkCategory' => $vendorPreQualification->vendorWorkCategory->name,
                'score'              => $vendorPreQualification->score,
                'grade'              => $gradeLevel ? $gradeLevel->description : null,
                'remarks'            => $gradeLevel ? $gradeLevel->definition : null,
                'route:details'      => route('vendorProfile.vendor.prequalification.details', [$vendorPreQualification->id]),
                'route:download'     => route('vendorProfile.vendorPreQualification.form.export', [$companyId, $vendorPreQualification->id]),
            ];
        }

        $rowCount = $vendorPreQualifications->count();

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorPrequalifictionDetails($vendorPreQualificationId)
    {
        $vendorPreQualification = VendorPreQualification::find($vendorPreQualificationId);

        $form = $vendorPreQualification->weightedNode;

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        AdjacencyListNode::traverse($data[0], function($node){
            if(isset($node['hasScores']) && $node['hasScores']) $node['route:getDownloads'] = route('preQualification.node.downloads', array($node['nodeId']));
            return $node;
        }, '_children');

        return Response::json($data);
    }

    public function supplierCreditFacilityList($companyId)
    {
        $company = Company::findOrFail($companyId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = SupplierCreditFacility::select("supplier_credit_facilities.*")
        ->join('vendor_registrations', 'vendor_registrations.id', '=', 'supplier_credit_facilities.vendor_registration_id')
        ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
            FROM vendor_registrations
            WHERE status = ".VendorRegistration::STATUS_COMPLETED."
            AND deleted_at IS NULL
            GROUP BY company_id) vr"), function($join){
            $join->on('vr.company_id', '=', 'vendor_registrations.company_id');
            $join->on('vr.revision', '=', 'vendor_registrations.revision');
        })
        ->join('companies', 'companies.id', '=', 'vr.company_id')
        ->where('companies.id', '=', $company->id)
        ->orderBy('supplier_credit_facilities.supplier_name', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                 => $record->id,
                'counter'            => $counter,
                'name'               => mb_strtoupper($record->supplier_name),
                'facilities'         => $record->credit_facilities,
                'attachmentsCount'   => $record->attachments->count(),
                'route:getDownloads' => route('vendorProfile.supplierCreditFacilities.attachments.get', [$record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function certificate($companyId)
    {
        $companyLogoPath  = getenv('APPLICATION_URL').'/'.MyCompanyProfile::getLogoPath();

        $companyProfile = MyCompanyProfile::first();

        $vendor = Company::find($companyId);

        return PDF::html('print.vendor_registration_certificate', compact('companyProfile', 'companyLogoPath', 'vendor'));
    }

    public function getAttachmentsCount($vendorProfileId, $field)
    {
        $vendorProfile = VendorProfile::find($vendorProfileId);
        $object        = ObjectField::findOrCreateNew($vendorProfile, $field);

        return Response::json([
            'field'           => $field,
            'attachmentCount' => count($this->getAttachmentDetails($object)),
        ]);
    }

    public function getAttachmentsList($vendorProfileId, $field)
    {
        $vendorProfile = VendorProfile::find($vendorProfileId);
        $object        = ObjectField::findOrCreateNew($vendorProfile, $field);
		$uploadedFiles = $this->getAttachmentDetails($object);

		$data = array();

		foreach($uploadedFiles as $file)
		{
			$file['imgSrc']      = $file->generateThumbnailURL();
			$file['deleteRoute'] = $file->generateDeleteURL();
			$file['size']	     = Helpers::formatBytes($file->size);

			$data[] = $file;
		}

		return $data;
    }

    public function updateAttachments($vendorProfileId, $field)
    {
        $inputs        = Input::all();
        $vendorProfile = VendorProfile::find($vendorProfileId);
        $object        = ObjectField::findOrCreateNew($vendorProfile, $field);

        ModuleAttachment::saveAttachments($object, $inputs);

		return array(
			'success' => true,
		);
    }

    public function remarks($vendorProfileId)
    {
        $vendorProfile = VendorProfile::findOrFail($vendorProfileId);

        $request = Request::instance();

        $data = [];

        foreach($vendorProfile->remarkList()->orderBy('created_at', 'DESC')->get() as $remark)
        {
            $data[] = [
                'id'           => $remark->id,
                'content'      => $remark->content,
                'created_at'   => Carbon::parse($remark->created_at)->format('d F Y g:i a'),
                'updated_at'   => Carbon::parse($remark->updated_at)->format('d F Y g:i a'),
                'created_by'   => ($remark->created_by) ? $remark->createdBy->name : 'N/A',
                'updated_by'   => ($remark->updated_by) ? $remark->updatedBy->name : 'N/A',
                'route:update' => route('vendorProfile.remarks.update', [$remark->id]),
                'route:delete' => route('vendorProfile.remarks.delete', [$remark->id]),
            ];
        }

        return Response::json($data);
    }

    public function saveRemarks($vendorProfileId)
    {
        $request = Request::instance();
        $success = false;
        $errors  = null;

        try
        {
            $user          = Confide::user();
            $vendorProfile = VendorProfile::findOrFail($vendorProfileId);

            $remarks = new VendorProfileRemark;

            $remarks->vendor_profile_id = $vendorProfile->id;
            $remarks->content           = trim($request->get('remarks'));
            $remarks->created_by        = $user->id;
            $remarks->updated_by        = $user->id;

            $remarks->save();

            $success = true;
        }
        catch(Exception $e)
        {
            $errors = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function updateRemarks($remarkId)
    {
        $request = Request::instance();
        $success = false;
        $errors  = null;

        try
        {
            $remarks = VendorProfileRemark::find($remarkId);
            $remarks->content = trim($request->get('remarks'));
            $remarks->save();

            $success = true;
        }
        catch(Exception $e)
        {
            $errors = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function deleteRemarks($remarkId)
    {
        $success = false;
        $errors  = null;

        try
        {
            $remarks = VendorProfileRemark::find($remarkId);

            if($remarks)
            {
                $remarks->delete();
            }

            $success = true;
        }
        catch(Exception $e)
        {
            $errors = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function exportExcel($type)
    {
        ini_set('memory_limit','2048M');
        ini_set('max_execution_time', '0'); // for infinite time of execution 

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Company::select("companies.*", "vr.revision AS vendor_registration_revision", "vr_status.submission_type", "vr_status.status", \DB::raw("ROUND(AVG(vendor_pre_qualifications.score),0) AS avg_score"), "companies.vendor_status", "companies.cidb_grade AS cidb_grade", "building_information_modelling_levels.name AS bim_level")
        ->where('companies.confirmed', '=', true)
        ->where('contract_group_categories.hidden', '=', false)
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
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->leftJoin('building_information_modelling_levels', 'building_information_modelling_levels.id', '=', 'companies.bim_level_id')
        ->leftJoin('vendors', 'vendors.company_id', '=', 'companies.id')
        ->leftJoin('vendor_work_categories', function($join){
            $join->on('vendor_work_categories.id', '=', 'vendors.vendor_work_category_id');
            $join->on('vendor_work_categories.hidden', \DB::raw('IS NOT'), \DB::raw('TRUE'));
        })
        ->leftJoin('states', 'companies.state_id', '=', 'states.id')
        ->leftJoin('countries', 'companies.country_id', '=', 'countries.id')
        ->leftJoin('vendor_pre_qualifications', 'vendor_pre_qualifications.vendor_registration_id', '=', 'vr_final.id');

        $tagIds = [];
        $tagNameSql = null;

        //advanced search
        if(strlen(trim($request->get('criteria_search_str'))) > 0)
        {
            $searchStr = '%'.urldecode(trim($request->get('criteria_search_str'))).'%';
            
            switch($request->get('search_criteria'))
            {
                case 'company_name':
                    $model->where('companies.name', 'ILIKE', $searchStr);
                    break;
                case 'reference_no':
                    $model->where('companies.reference_no', 'ILIKE', $searchStr);
                    break;
                case 'vendor_category':
                    if(trim($request->get('criteria_search_str')) == '(blanks)')
                    {
                        $model->whereRaw("NOT EXISTS (
                            SELECT company_vendor_category.company_id
                            FROM company_vendor_category
                            JOIN vendor_categories ON company_vendor_category.vendor_category_id = vendor_categories.id
                            WHERE company_vendor_category.company_id = companies.id
                            AND vendor_categories.contract_group_category_id = companies.contract_group_category_id
                            AND vendor_categories.hidden IS FALSE
                            GROUP BY company_vendor_category.company_id
                        )");
                    }
                    else
                    {
                        $model->whereRaw("EXISTS (
                            SELECT company_vendor_category.company_id
                            FROM company_vendor_category
                            JOIN vendor_categories ON company_vendor_category.vendor_category_id = vendor_categories.id
                            WHERE company_vendor_category.company_id = companies.id
                            AND vendor_categories.contract_group_category_id = companies.contract_group_category_id
                            AND vendor_categories.name ILIKE ?
                            AND vendor_categories.hidden IS FALSE
                            GROUP BY company_vendor_category.company_id
                        )", [$searchStr] );
                    }
                    break;
                case 'vendor_work_category':
                    if(trim($request->get('criteria_search_str')) == '(blanks)')
                    {
                        $model->whereRaw("NOT EXISTS (
                            SELECT vendors.company_id
                            FROM vendors
                            JOIN vendor_work_categories ON vendors.vendor_work_category_id = vendor_work_categories.id
                            WHERE vendors.company_id = companies.id
                            AND vendor_work_categories.hidden IS FALSE
                            GROUP BY vendors.company_id
                        )");
                    }else{
                        $model->whereRaw("EXISTS (
                            SELECT vendors.company_id
                            FROM vendors
                            JOIN vendor_work_categories ON vendors.vendor_work_category_id = vendor_work_categories.id
                            WHERE vendors.company_id = companies.id
                            AND vendor_work_categories.name ILIKE ?
                            AND vendor_work_categories.hidden IS FALSE
                            GROUP BY vendors.company_id
                        )", [$searchStr] );
                    }
                    
                    break;
                case 'state':
                    $model->where('states.name', 'ILIKE', $searchStr);
                    break;
            }
        }

        if($request->has('contract_group_category_id') && (int)$request->get('contract_group_category_id') > 0)
        {
            $model->where('companies.contract_group_category_id', '=', (int)$request->get('contract_group_category_id'));
        }

        if($request->has('company_status'))
        {
            $model->whereIn('companies.company_status', $request->get('company_status'));
        }

        if(($request->has('activation_date_from') and strlen(trim($request->get('activation_date_from'))) > 0) and ($request->has('activation_date_to') and strlen(trim($request->get('activation_date_to'))) > 0))
        {
            $activationDateFrom = date('Y-m-d', strtotime(trim($request->get('activation_date_from'))));
            $activationDateTo = date('Y-m-d', strtotime(trim($request->get('activation_date_to'))));

            $model->whereRaw('companies.activation_date BETWEEN ? AND ?', [$activationDateFrom, $activationDateTo]);
        }

        if(($request->has('expiry_date_from') and strlen(trim($request->get('expiry_date_from'))) > 0) and ($request->has('expiry_date_to') and strlen(trim($request->get('expiry_date_to'))) > 0))
        {
            $expiryDateFrom = date('Y-m-d', strtotime(trim($request->get('expiry_date_from'))));
            $expiryDateTo = date('Y-m-d', strtotime(trim($request->get('expiry_date_to'))));

            $model->whereRaw('companies.expiry_date BETWEEN ? AND ?', [$expiryDateFrom, $expiryDateTo]);
        }

        if(($request->has('deactivation_date_from') and strlen(trim($request->get('deactivation_date_from'))) > 0) and ($request->has('deactivation_date_to') and strlen(trim($request->get('deactivation_date_to'))) > 0))
        {
            $deactivationDateFrom = date('Y-m-d', strtotime(trim($request->get('deactivation_date_from'))));
            $deactivationDateTo = date('Y-m-d', strtotime(trim($request->get('deactivation_date_to'))));

            $model->whereRaw('companies.deactivation_date BETWEEN ? AND ?', [$deactivationDateFrom, $deactivationDateTo]);
        }

        if($request->has('vendor_status') && strlen(trim($request->get('vendor_status')) > 0))
        {
            switch(trim($request->get('vendor_status')))
            {
                case Company::STATUS_ACTIVE:
                    $model->whereNotNull('activation_date')->where(function($query) {
                        $query->whereNull('expiry_date')->orWhere('expiry_date', '>', 'NOW()');
                    })->whereNull('deactivated_at');
                    break;
                case Company::STATUS_EXPIRED:
                    $model->whereNotNull('expiry_date')->where('expiry_date', '<=', 'NOW()')->whereNull('deactivated_at');
                    break;
                case Company::STATUS_DEACTIVATED:
                    $model->whereNotNull('activation_date')->whereNotNull('deactivated_at');
                    break;
            }
        }

        $globalPreQGrade = VendorRegistrationAndPrequalificationModuleParameter::first()->vendorManagementGrade;

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);
                switch(trim(strtolower($filters['field'])))
                {
                    case 'vendor_code':
                        if(strlen($val) > 0)
                        {
                            $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
                            $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

                            $model->where(\DB::raw("'" . $vendorCodePrefix . "' || LPAD(companies.id::text, " . $vendorCodePadLength . ", '0')"), 'ILIKE', '%' . $val . '%');
                        }
                        break;
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
                    case 'state':
                        if(strlen($val) > 0)
                        {
                            $model->where('states.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'country':
                        if(strlen($val) > 0)
                        {
                            $model->where('countries.country', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'tids':
                        if(strlen($val) > 0)
                        {
                            $tagIds[] = (int)$val;
                        }
                        break;
                    case 'tags':
                        if(strlen($val) > 0)
                        {
                            $tagNameSql = " AND tags.name ILIKE '%".$val."%' ";
                        }
                        break;
                    case 'vendor_status_text': 
                        if((int)$val > 0)
                        {
                            switch($val)
                            {
                                case Company::STATUS_ACTIVE:
                                    $model->whereNotNull('activation_date')->where(function($query) {
                                        $query->whereNull('expiry_date')->orWhere('expiry_date', '>', 'NOW()');
                                    })->whereNull('deactivated_at');
                                    break;
                                case Company::STATUS_EXPIRED:
                                    $model->whereNotNull('expiry_date')->where('expiry_date', '<=', 'NOW()')->whereNull('deactivated_at');
                                    break;
                                case Company::STATUS_DEACTIVATED:
                                    $model->whereNotNull('activation_date')->whereNotNull('deactivated_at');
                                    break;
                            }
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
                    case 'prequalificationgrade':
                        if((int)$val > 0)
                        {
                            $ranges = $globalPreQGrade->getLevelRanges();

                            $model->having(\DB::raw("ROUND(AVG(vendor_pre_qualifications.score))"), '>', $ranges[$val]['min']);
                            $model->having(\DB::raw("ROUND(AVG(vendor_pre_qualifications.score))"), '<=', $ranges[$val]['max']);
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

        if($tagIds or $tagNameSql)
        {
            $tagIdsSql = null;

            if($tagIds)
            {
                $tagIds = Tag::whereIn('id', $tagIds)->lists('id');

                if(!empty($tagIds))
                {
                    $tagIdsSql = " AND tags.id IN (".implode(',', $tagIds).") ";
                }
            }

            $model->whereRaw("
                EXISTS (
                    SELECT object_tags.object_id
                    FROM object_tags
                    JOIN tags ON object_tags.tag_id = tags.id AND tags.category = ".Tag::CATEGORY_VENDOR_PROFILE."
                    WHERE object_tags.object_class = '".get_class(new Company)."'
                    AND object_tags.object_id = companies.id
                    ".$tagIdsSql."
                    ".$tagNameSql."
                )
            ");
        }
        
        $records = $model->orderBy('companies.id', 'desc')
        ->orderBy('companies.name', 'desc')
        ->groupBy(\DB::raw('companies.id, vr.company_id, vr.revision, vr_status.status, vr_status.submission_type, states.id, countries.id, building_information_modelling_levels.id'))
        ->get();

        $companyIds = array_column($records->toArray(), 'id');

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

        $excelTitle = null;

        switch($type)
        {
            case 'default':
                $this->generateVendorProfileData($spreadsheet, $companyIds, $records, $headerStyle);

                $excelTitle = trans('vendorProfile.vendorProfiles');
                break;
            case 'projectTrackRecord':
                $this->generateProjectTrackRecordData($spreadsheet, $companyIds, $headerStyle);

                $excelTitle = trans('vendorManagement.projectTrackRecord');
                break;
            case 'supplierCreditFacilities':
                $this->generateSupplierCreditFacilities($spreadsheet, $companyIds, $headerStyle);

                $excelTitle = trans('vendorManagement.supplierCreditFacility');
                break;
            case 'vendorRegistrationForm':
                $this->generateDynamicFormSheets($spreadsheet, $companyIds, $headerStyle);

                $excelTitle = trans('vendorProfile.vendorRegistrationForm');
                break;
        }

        $writer = new Xlsx($spreadsheet);

        $filepath = \PCK\Helpers\Files::getTmpFileUri();

        $writer->save($filepath);

        $filename = $excelTitle . '-'.date("dmYHis");

        return \PCK\Helpers\Files::download($filepath, "{$filename}.".\PCK\Helpers\Files::EXTENSION_EXCEL);
    }

    protected function generateVendorProfileData(Spreadsheet $spreadsheet, Array $companyIds, Collection $records, $headerStyle)
    {
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle(trans('vendorManagement.vendorDetails'));
        $activeSheet->setAutoFilter('A1:Z1');

        $headers = [
            trans('vendorManagement.vendorCode'),
            trans('vendorManagement.vendorName'),
            "Vendor Status",
            trans('vendorManagement.rocNumber'),
            "Business Entity",
            "Main Contact",
            "Email",
            "Telephone No.",
            "Fax No.",
            "Address",
            "Country",
            "State",
            "Tax Registration No.",
            trans('vendorManagement.companyStatus'),
            "Bumiputera Equity",
            "Non Bumiputera Equity",
            "Foreigner Equity",
            "Activation Date",
            "Expiry Date",
            "Deactivation Date",
            trans('companies.cidbGrade'),
            trans('companies.bimInformation'),
            "Submission Status",
            "Submission Type",
            "Pre Q Score",
            "Pre Q Grade",
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

        $companyIds = $this->generateVendorDetailsSheet($activeSheet, $records);

        $activeSheet = new Worksheet($spreadsheet, 'Company Categories');
        $spreadsheet->addSheet($activeSheet);
        $activeSheet->setAutoFilter('A1:J1');

        $headers = [
            trans('vendorManagement.vendorCode'),
            trans('vendorManagement.vendorName'),
            trans('vendorManagement.rocNumber'),
            "Code",
            "Vendor Group",
            "Vendor Category",
            "Vendor Work Category",
            "Vendor Work Sub Category",
            "Pre Qualification Score",
            "Pre Qualification Grade"
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

        $this->generateVendorCategoriesSheet($activeSheet, $companyIds);

        $activeSheet = new Worksheet($spreadsheet, 'Company Personnel');
        $spreadsheet->addSheet($activeSheet);
        $activeSheet->setAutoFilter('A1:L1');

        $headers = [
            trans('vendorManagement.vendorCode'),
            trans('vendorManagement.vendorName'),
            trans('vendorManagement.rocNumber'),
            trans('general.name'),
            "IC No./Passport",
            "Type",
            "Email",
            "Contact No.",
            "Years of Experience",
            "Designation",
            "Amount of Share",
            "Holding Percentage"
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

        $this->generateCompanyPersonnelSheet($activeSheet, $companyIds);
    }

    protected function generateProjectTrackRecordData(Spreadsheet $spreadsheet, Array $companyIds, $headerStyle)
    {
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle(trans('vendorManagement.projectTrackRecord'));
        $activeSheet->setAutoFilter('A1:R1');

        $headers = [
            trans('vendorManagement.vendorCode'),
            trans('vendorManagement.vendorName'),
            trans('vendorManagement.rocNumber'),
            trans('vendorManagement.projectTitle'),
            trans('general.type'),
            trans('propertyDevelopers.propertyDeveloper'),
            trans('vendorManagement.vendorCategory'),
            trans('vendorManagement.vendorWorkCategory'),
            trans('vendorManagement.vendorSubWorkCategory'),
            trans('vendorManagement.projectAmount'),
            trans('currencies.currencyCode'),
            trans('vendorManagement.projectAmountRemarks'),
            trans('vendorManagement.yearOfSitePosession'),
            trans('vendorManagement.yearOfCompletion'),
            trans('vendorManagement.qlassicOrConquasScore'),
            trans('vendorManagement.qlassicScore'),
            trans('vendorManagement.qlassicYearOfAchievement'),
            trans('vendorManagement.yearOfAwardsReceived'),
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

        $this->generateProjectTrackRecordSheet($activeSheet, $companyIds);
    }

    protected function generateSupplierCreditFacilities(Spreadsheet $spreadsheet, Array $companyIds, $headerStyle)
    {
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle(trans('vendorManagement.supplierCreditFacility'));
        $activeSheet->setAutoFilter('A1:E1');

        $headers = [
            trans('vendorManagement.vendorCode'),
            trans('vendorManagement.vendorName'),
            trans('vendorManagement.rocNumber'),
            trans('vendorManagement.supplierName'),
            trans('vendorManagement.creditFacilities'),
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

        $this->generateSupplierCreditFacilitySheet($activeSheet, $companyIds);
    }

    protected function generateVendorDetailsSheet(Worksheet &$workSheet, Collection $collection)
    {
        $companyIds = [];

        $vendorCodePrefix    = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
        $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

        $globalPreQGrade = VendorRegistrationAndPrequalificationModuleParameter::first()->vendorManagementGrade;

        $records = [];
        foreach($collection as $idx => $record)
        {
            $companyIds[] = $record->id;

            $vendorCode = $vendorCodePrefix . str_pad($record->id, $vendorCodePadLength, 0, STR_PAD_LEFT);

            if($record->business_entity_type_id)
            {
                $businessEntity = mb_strtoupper($record->businessEntityType->name);
            }
            else
            {
                $businessEntity = mb_strtoupper($record->business_entity_type_name);
            }

            $address = str_replace("\n", " ", $record->address);

            $activationDate = null;
            $expiryDate = null;
            $deactivationDate = null;

            if($record->activation_date)
                $activationDate = Carbon::parse($record->activation_date)->format(\Config::get('dates.standard'));
            
            if($record->expiry_date)
                $expiryDate = Carbon::parse($record->expiry_date)->format(\Config::get('dates.standard'));

            if($record->deactivation_date)
                $deactivationDate = Carbon::parse($record->deactivation_date)->format(\Config::get('dates.standard'));

            $cidbGrade      = is_null($record->cidb_grade) ? null : CIDBGrade::find($record->cidb_grade)->grade;
            $bimInformation = is_null($record->bim_level) ? null : $record->bim_level;

            $cidbGrade = 

            $records[] = [
                $vendorCode,
                mb_strtoupper($record->name),
                $record->getStatusText(),
                mb_strtoupper($record->reference_no),
                $businessEntity,
                $record->main_contact,
                $record->email,
                $record->telephone_number,
                $record->fax_number,
                $address,
                mb_strtoupper($record->country->country),
                mb_strtoupper($record->state->name),
                $record->tax_registration_no,
                $record->company_status ? Company::getCompanyStatusDescriptions($record->company_status) : '-',
                $record->bumiputera_equity,
                $record->non_bumiputera_equity,
                $record->foreigner_equity,
                $activationDate,
                $expiryDate,
                $deactivationDate,
                $cidbGrade,
                $bimInformation,
                VendorRegistration::getStatusText($record->status),
                VendorRegistration::getSubmissionTypeText($record->submission_type),
                $record->avg_score,
                ($record->avg_score && $globalPreQGrade) ? $globalPreQGrade->getGrade($record->avg_score)->description : null,
            ];

            unset($collection[$idx]);
        }

        foreach(['E', 'F', 'G', 'H', 'I', 'K', 'L'] as $column)
        {
            $workSheet->getColumnDimension($column)->setAutoSize(false);
            $workSheet->getColumnDimension($column)->setWidth(32);
        }

        $workSheet->getStyle('H')->getNumberFormat()->setFormatCode('#');
        $workSheet->getStyle('H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $workSheet->getStyle('H')->getNumberFormat()->setFormatCode("#");//have to set this format. if not excel will display in scientific notation https://github.com/PHPOffice/PhpSpreadsheet/issues/357
        
        $workSheet->getStyle('O:Q')->getNumberFormat()->setFormatCode("#,##0.00");

        $workSheet->fromArray($records, null, 'A2');

        return $companyIds;
    }

    protected function generateVendorCategoriesSheet(Worksheet &$workSheet, Array $companyIds)
    {
        if(empty($companyIds))
        {
            return false;
        }
        
        $masterVendorWorkCategoryRecords = VendorWorkCategory::select('vendor_work_categories.id', 'vendor_work_categories.code', 'vendor_work_categories.name')
        ->orderBy('vendor_work_categories.code', 'asc')
        ->get()
        ->toArray();

        $masterVendorWorkCategories = [];
        foreach($masterVendorWorkCategoryRecords as $record)
        {
            $masterVendorWorkCategories[$record['id']] = [
                'code' => $record['code'],
                'name' => $record['name']
            ];
        }

        $vendors = Company::select('vendor_categories.id AS vendor_category_id', 'companies.id AS company_id', 'vendors.id AS vendor_id')
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->join('vendor_categories', 'vendor_categories.contract_group_category_id', '=', 'contract_group_categories.id')
        ->join('company_vendor_category', function($join){
            $join->on('company_vendor_category.company_id', '=', 'companies.id');
            $join->on('company_vendor_category.vendor_category_id','=', 'vendor_categories.id');
        })
        ->join('vendors', 'vendors.company_id', '=', 'companies.id')
        ->whereIn('companies.id', $companyIds)
        ->orderBy('companies.id', 'desc')
        ->orderBy('vendor_categories.code', 'asc')
        ->get()
        ->toArray();

        $vendorPreQualifications = \DB::select(\DB::raw("
            SELECT vr.company_id, preq.vendor_work_category_id, preq.score
            FROM vendor_pre_qualifications preq
            JOIN vendor_registrations vr ON vr.id = preq.vendor_registration_id
            JOIN (
                SELECT max(revision) AS revision, company_id
                FROM vendor_registrations
                WHERE status = ".PCK\VendorRegistration\VendorRegistration::STATUS_COMPLETED."
                AND deleted_at IS NULL
                GROUP BY company_id
            ) vr_latest ON vr_latest.company_id = vr.company_id
            WHERE vr.status = ".PCK\VendorRegistration\VendorRegistration::STATUS_COMPLETED."
        "));

        $vendorPreQualificationScores = [];

        foreach($vendorPreQualifications as $preQ)
        {
            if(!array_key_exists($preQ->company_id, $vendorPreQualificationScores)) $vendorPreQualificationScores[$preQ->company_id] = [];

            $vendorPreQualificationScores[$preQ->company_id][$preQ->vendor_work_category_id] = $preQ->score;
        }

        $companyVendorCategories = [];
        foreach($vendors as $vendor)
        {
            if(!array_key_exists($vendor['company_id'], $companyVendorCategories))
            {
                $companyVendorCategories[$vendor['company_id']] = [];
            }

            $companyVendorCategories[$vendor['company_id']][$vendor['vendor_category_id']] = $vendor['vendor_category_id'];
        }

        unset($vendors);
        
        $vendorWorkCategories = Company::select('vendor_categories.id AS vendor_category_id', 'vendor_work_categories.id AS vendor_work_category_id', 'companies.id AS company_id', 'vendors.id AS vendor_id')
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->join('vendor_categories', 'vendor_categories.contract_group_category_id', '=', 'contract_group_categories.id')
        ->join('company_vendor_category', function($join){
            $join->on('company_vendor_category.company_id', '=', 'companies.id');
            $join->on('company_vendor_category.vendor_category_id','=', 'vendor_categories.id');
        })
        ->join('vendors', 'vendors.company_id', '=', 'companies.id')
        ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
        ->join('vendor_category_vendor_work_category', function($join){
            $join->on('vendor_category_vendor_work_category.vendor_category_id', '=', 'vendor_categories.id');
            $join->on('vendor_category_vendor_work_category.vendor_work_category_id','=', 'vendor_work_categories.id');
        })
        ->whereIn('companies.id', $companyIds)
        ->orderBy('companies.id', 'desc')
        ->orderBy('vendor_categories.code', 'asc')
        ->get()
        ->toArray();

        $companyVendorWorkCategories = [];
        foreach($vendorWorkCategories as $vendorWorkCategory)
        {
            if(!array_key_exists($vendorWorkCategory['vendor_id'], $companyVendorWorkCategories))
            {
                $companyVendorWorkCategories[$vendorWorkCategory['vendor_id']] = [];
            }

            if(!array_key_exists($vendorWorkCategory['vendor_category_id'], $companyVendorWorkCategories[$vendorWorkCategory['vendor_id']]))
            {
                $companyVendorWorkCategories[$vendorWorkCategory['vendor_id']][$vendorWorkCategory['vendor_category_id']] = [];
            }

            $companyVendorWorkCategories[$vendorWorkCategory['vendor_id']][$vendorWorkCategory['vendor_category_id']][] = $vendorWorkCategory['vendor_work_category_id'];
        }

        unset($vendorWorkCategories);


        $vendorRecords = Company::select('vendors.id', 'vendors.company_id', 'vendors.vendor_work_category_id',
        'companies.id AS company_id', 'vendor_categories.id AS vendor_category_id',
        'vendor_work_categories.id AS vendor_work_category_id', 'vendor_work_categories.code AS vendor_work_category_code', 'vendor_work_categories.name AS vendor_work_category_name',
        'vendor_category_vendor_work_category.vendor_category_id AS x_vendor_category_id')
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->join('vendor_categories', 'vendor_categories.contract_group_category_id', '=', 'contract_group_categories.id')
        ->join('company_vendor_category', function($join){
            $join->on('company_vendor_category.company_id', '=', 'companies.id');
            $join->on('company_vendor_category.vendor_category_id','=', 'vendor_categories.id');
        })
        ->join('vendors', 'vendors.company_id', '=', 'company_vendor_category.company_id')
        ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
        ->join('vendor_category_vendor_work_category', 'vendor_work_categories.id', '=', 'vendor_category_vendor_work_category.vendor_work_category_id')//only to vendor_work-category because there might be vendor that was assign to the wrong vendor_category
        ->whereIn('companies.id', $companyIds)
        ->orderBy('companies.id', 'desc')
        ->orderBy('vendor_categories.code', 'asc')
        ->orderBy('vendor_work_categories.code', 'asc')
        ->get()
        ->toArray();

        $vendors = [];
        foreach($vendorRecords as $record)
        {
            if(!array_key_exists($record['company_id'], $vendors))
            {
                $vendors[$record['company_id']] = [];
            }

            if(!array_key_exists($record['x_vendor_category_id'], $vendors[$record['company_id']]))
            {
                $vendors[$record['company_id']][$record['x_vendor_category_id']] = [];
            }

            $vendors[$record['company_id']][$record['x_vendor_category_id']][$record['id']] = [
                'vendor_work_category_id' => $record['vendor_work_category_id'],
                'code' => $record['vendor_work_category_code'],
                'name' => $record['vendor_work_category_name']
            ];
        }

        unset($vendorRecords);

        $companies = Company::select('companies.id AS company_id', 'companies.name AS company_name', 'companies.reference_no',
        'contract_group_categories.name AS contract_group_category_name',
        'vendor_categories.id AS vendor_category_id', 'vendor_categories.code AS vendor_category_code', 'vendor_categories.name AS vendor_category_name')
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->join('vendor_categories', 'vendor_categories.contract_group_category_id', '=', 'contract_group_categories.id')
        ->join('company_vendor_category', function($join){
            $join->on('company_vendor_category.company_id', '=', 'companies.id');
            $join->on('company_vendor_category.vendor_category_id','=', 'vendor_categories.id');
        })
        ->whereIn('companies.id', $companyIds)
        ->orderBy('companies.id', 'desc')
        ->orderBy('vendor_categories.code', 'asc')
        ->get()
        ->toArray();

        $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
        $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

        $globalPreQGrade = VendorRegistrationAndPrequalificationModuleParameter::first()->vendorManagementGrade;

        $trackRecordProjectVendorWorkSubCategories = Vendor::getTrackRecordProjectVendorWorkSubCategories($companyIds);

        $records = [];

        foreach($companies as $company)
        {
            $vendorCode = $vendorCodePrefix . str_pad($company['company_id'], $vendorCodePadLength, 0, STR_PAD_LEFT);

            if(array_key_exists($company['company_id'], $companyVendorCategories) &&
            array_key_exists($company['vendor_category_id'], $companyVendorCategories[$company['company_id']])
            )
            {
                $records[] = [
                    $vendorCode,
                    mb_strtoupper($company['company_name']),
                    mb_strtoupper($company['reference_no']),
                    $company['vendor_category_code'],
                    $company['contract_group_category_name'],
                    $company['vendor_category_name'],
                    null,
                    null,
                    null,
                    null,
                ];

                unset($companyVendorCategories[$company['company_id']][$company['vendor_category_id']]);
            }

            if(!array_key_exists($company['company_id'], $vendors))
                continue;
            
            if(array_key_exists($company['vendor_category_id'], $vendors[$company['company_id']]))
            {
                foreach($vendors[$company['company_id']][$company['vendor_category_id']] as $vendorId => $vendor)
                {
                    $code = null;
                    $level2 = null;
                    $level3 = null;
                    $preQScore = null;
                    $preQGrade = null;

                    if((array_key_exists($vendorId, $companyVendorWorkCategories) &&
                    array_key_exists($company['vendor_category_id'], $companyVendorWorkCategories[$vendorId]) && in_array($vendor['vendor_work_category_id'], $companyVendorWorkCategories[$vendorId][$company['vendor_category_id']])))
                    {
                        $level2 = $company['vendor_category_name'];
                    }

                    if(array_key_exists($vendor['vendor_work_category_id'], $masterVendorWorkCategories))
                    {
                        $code = $masterVendorWorkCategories[$vendor['vendor_work_category_id']]['code'];
                        $level3 = $masterVendorWorkCategories[$vendor['vendor_work_category_id']]['name'];
                        $preQScore = $vendorPreQualificationScores[$company['company_id']][$vendor['vendor_work_category_id']] ?? null;
                        $preQGrade = ($preQScore && $globalPreQGrade) ? $globalPreQGrade->getGrade($preQScore)->description : null;
                    }

                    $records[] = [
                        $vendorCode,
                        mb_strtoupper($company['company_name']),
                        mb_strtoupper($company['reference_no']),
                        $code,
                        $company['contract_group_category_name'],
                        $level2,
                        $level3,
                        null,
                        $preQScore,
                        $preQGrade,
                    ];

                    if(isset($trackRecordProjectVendorWorkSubCategories[$company['company_id']][$vendor['vendor_work_category_id']]))
                    {
                        $code = null;
                        $level2 = null;
                        $level3 = null;
                        $preQScore = null;
                        $preQGrade = null;

                        if(array_key_exists($vendor['vendor_work_category_id'], $masterVendorWorkCategories))
                        {
                            $level3 = $masterVendorWorkCategories[$vendor['vendor_work_category_id']]['name'];
                            $preQScore = $vendorPreQualificationScores[$company['company_id']][$vendor['vendor_work_category_id']] ?? null;
                            $preQGrade = ($preQScore && $globalPreQGrade) ? $globalPreQGrade->getGrade($preQScore)->description : null;
                        }

                        if((array_key_exists($vendorId, $companyVendorWorkCategories) &&
                        array_key_exists($company['vendor_category_id'], $companyVendorWorkCategories[$vendorId]) && in_array($vendor['vendor_work_category_id'], $companyVendorWorkCategories[$vendorId][$company['vendor_category_id']])))
                        {
                            $level2 = $company['vendor_category_name'];
                        }

                        $vendorSubWorkCategoryCode = is_null($trackRecordProjectVendorWorkSubCategories[$company['company_id']][$vendor['vendor_work_category_id']]['codes']) ? null : implode(', ', $trackRecordProjectVendorWorkSubCategories[$company['company_id']][$vendor['vendor_work_category_id']]['codes']);
                        $vendorSubWorkCategoryName = is_null($trackRecordProjectVendorWorkSubCategories[$company['company_id']][$vendor['vendor_work_category_id']]['names']) ? null : implode(', ', $trackRecordProjectVendorWorkSubCategories[$company['company_id']][$vendor['vendor_work_category_id']]['names']);

                        $records[] = [
                            $vendorCode,
                            mb_strtoupper($company['company_name']),
                            mb_strtoupper($company['reference_no']),
                            $vendorSubWorkCategoryCode,
                            $company['contract_group_category_name'],
                            $level2,
                            $level3,
                            $vendorSubWorkCategoryName,
                            $preQScore,
                            $preQGrade,
                        ];
                    }
                }
            }
        }

        unset($companyVendorWorkCategories, $masterVendorWorkCategories);

        foreach(['F', 'G', 'H'] as $column)
        {
            $workSheet->getColumnDimension($column)->setAutoSize(false);
            $workSheet->getColumnDimension($column)->setWidth(42);
        }

        $workSheet->getColumnDimension('C')->setAutoSize(false);
        $workSheet->getColumnDimension('C')->setWidth(28);

        $workSheet->fromArray($records, null, 'A2');

        return true;
    }

    protected function generateCompanyPersonnelSheet(Worksheet &$workSheet, Array $companyIds)
    {
        if(empty($companyIds))
        {
            return false;
        }
        
        $records = CompanyPersonnel::select('company_personnel.*', \DB::raw('TO_CHAR(company_personnel.created_at, \'YYYY-MM-DD HH24:MI:SS\') AS created_at'), \DB::raw('TO_CHAR(company_personnel.updated_at, \'YYYY-MM-DD HH24:MI:SS\') AS updated_at'), 'vendor_registrations.id AS vendor_registration_id',
        'companies.id AS company_id', 'companies.name AS company_name', 'companies.reference_no')
        ->join('vendor_registrations', 'vendor_registrations.id', '=', 'company_personnel.vendor_registration_id')
        ->join('companies', 'companies.id', '=', 'vendor_registrations.company_id')
        ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
            FROM vendor_registrations
            WHERE status = ".VendorRegistration::STATUS_COMPLETED."
            AND deleted_at IS NULL
            GROUP BY company_id) vr"), 'vr.company_id', '=', 'companies.id')
        ->whereRaw('vendor_registrations.revision = vr.revision')
        ->whereNull('vendor_registrations.deleted_at')
        ->whereIn('companies.id', $companyIds)
        ->orderBy('companies.id', 'desc')
        ->orderBy('company_personnel.name', 'desc')
        ->get()
        ->toArray();

        $companyPersonnel = [];

        $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
        $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

        foreach($records as $idx => $record)
        {
            $vendorCode = $vendorCodePrefix . str_pad($record['company_id'], $vendorCodePadLength, 0, STR_PAD_LEFT);

            switch($record['type'])
            {
                case CompanyPersonnel::TYPE_DIRECTOR:
                    $type = 'Director';
                    break;
                case CompanyPersonnel::TYPE_SHAREHOLDERS:
                    $type = 'Shareholder';
                    break;
                case CompanyPersonnel::TYPE_HEAD_OF_COMPANY:
                    $type = 'Head of Company/Owner';
                    break;
                default:
                    $type = '-';
            }

            $companyPersonnel[] = [
                $vendorCode,
                $record['company_name'],
                $record['reference_no'],
                $record['name'],
                $record['identification_number'],
                $type,
                $record['email_address'],
                $record['contact_number'],
                $record['years_of_experience'],
                $record['designation'],
                $record['amount_of_share'],
                $record['holding_percentage']
            ];

            unset($records[$idx]);
        }

        unset($records);

        foreach(['D', 'G', 'H', 'I', 'J'] as $column)
        {
            $workSheet->getColumnDimension($column)->setAutoSize(false);
            $workSheet->getColumnDimension($column)->setWidth(32);
        }

        $workSheet->getStyle('E')->getNumberFormat()->setFormatCode('#');
        $workSheet->getStyle('E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $workSheet->getStyle('E')->getNumberFormat()->setFormatCode("#");//have to set this format. if not excel will display in scientific notation https://github.com/PHPOffice/PhpSpreadsheet/issues/357
        
        $workSheet->getStyle('H')->getNumberFormat()->setFormatCode('#');
        $workSheet->getStyle('H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $workSheet->getStyle('H')->getNumberFormat()->setFormatCode("#");//have to set this format. if not excel will display in scientific notation https://github.com/PHPOffice/PhpSpreadsheet/issues/357

        $workSheet->getStyle('L')->getNumberFormat()->setFormatCode("#,##0.00");

        $workSheet->fromArray($companyPersonnel, null, 'A2');

        return true;
    }

    protected function generateProjectTrackRecordSheet(Worksheet &$workSheet, Array $companyIds)
    {
        if(empty($companyIds))
        {
            return false;
        }

        $vendorCodePrefix    = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
        $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

        $records = [];

        $mainQuery = "SELECT c.id AS company_id, c.name AS company_name, c.reference_no as roc, trp.id AS project_track_record_id, trp.title project_title, 
                        CASE WHEN trp.type = " . TrackRecordProject::TYPE_CURRENT . " THEN '" . trans('vendorManagement.currentProject') . "' ELSE '" . trans('vendorManagement.completedProject') . "' END AS type,
                        CASE WHEN pd.id IS NULL THEN '-' ELSE pd.name END AS property_developer, vc.name AS vendor_category, vwc.name AS vendor_work_category, trp.project_amount, ctry.currency_code, trp.project_amount_remarks, 
                        TO_CHAR(trp.year_of_site_possession, 'DD/MM/YYYY') AS year_of_site_possession, TO_CHAR(trp.year_of_completion, 'DD/MM/YYYY') AS year_of_completion,
                        CASE WHEN trp.has_qlassic_or_conquas_score THEN '" . trans('general.yes') . "' ELSE '" . trans('general.no') . "' END AS has_qlassic_or_conquas_score, trp.qlassic_score, 
                        TO_CHAR(trp.qlassic_year_of_achievement, 'DD/MM/YYYY') AS qlassic_year_of_achievement, TO_CHAR(trp.year_of_recognition_awards, 'DD/MM/YYYY') AS year_of_recognition_awards
                        FROM companies c 
                        INNER JOIN vendor_registrations vr ON vr.company_id = c.id
                        INNER JOIN (SELECT MAX(revision) AS revision, company_id
                                    FROM vendor_registrations
                                    WHERE STATUS = " . VendorRegistration::STATUS_COMPLETED . "
                                    AND deleted_at IS NULL
                                    GROUP BY company_id
                                    ) vrsub ON vrsub.company_id = c.id AND vrsub.revision = vr.revision
                        INNER JOIN track_record_projects trp ON trp.vendor_registration_id = vr.id
                        INNER JOIN vendor_categories vc ON vc.id = trp.vendor_category_id 
                        INNER JOIN vendor_work_categories vwc ON vwc.id = trp.vendor_work_category_id 
                        INNER JOIN countries ctry ON ctry.id = trp.country_id 
                        LEFT OUTER JOIN property_developers pd ON pd.id = trp.property_developer_id
                        WHERE vr.deleted_at IS NULL
                        AND c.id IN (" . implode(', ', $companyIds) . ")
                        ORDER BY c.id ASC, vr.revision ASC, trp.type ASC, trp.id ASC;";

        $projectTrackRecords = array_map(function ($object) {
            return (array) $object;
        }, \DB::select(\DB::raw($mainQuery)));

        $projectTrackRecordIds = array_column($projectTrackRecords, 'project_track_record_id');

        $vendorSubWorkCategoryQuery = "SELECT trp.id, CASE WHEN TRIM(STRING_AGG(vws.name, ', ')) IS NULL THEN '-' ELSE TRIM(STRING_AGG(vws.name, ', ')) END AS vendor_sub_work_category
                                        FROM track_record_projects trp
                                        INNER JOIN vendor_categories vc ON vc.id = trp.vendor_category_id 
                                        INNER JOIN vendor_work_categories vwc ON vwc.id = trp.vendor_work_category_id 
                                        LEFT OUTER JOIN track_record_project_vendor_work_subcategories trpvws ON trpvws.track_record_project_id = trp.id
                                        LEFT OUTER JOIN vendor_work_subcategories vws ON vws.id = trpvws.vendor_work_subcategory_id 
                                        WHERE trp.id IN (" . implode(', ', $projectTrackRecordIds) . ")
                                        GROUP BY trp.id
                                        ORDER BY trp.id ASC";

        $vendoSubWorkCategortResults = [];

        foreach(\DB::select(\DB::raw($vendorSubWorkCategoryQuery)) as $result)
        {
            $vendoSubWorkCategortResults[$result->id] = $result->vendor_sub_work_category;
        }

        foreach($projectTrackRecords as $projectTrackRecord)
        {
            $vendorCode = $vendorCodePrefix . str_pad($projectTrackRecord['company_id'], $vendorCodePadLength, 0, STR_PAD_LEFT);

            array_push($records, [
                'code'                         => $vendorCode,
                'company_name'                 => $projectTrackRecord['company_name'],
                'roc'                          => $projectTrackRecord['roc'],
                'project_title'                => $projectTrackRecord['project_title'],
                'type'                         => $projectTrackRecord['type'],
                'property_developer'           => $projectTrackRecord['property_developer'],
                'vendor_category'              => $projectTrackRecord['vendor_category'],
                'vendor_work_category'         => $projectTrackRecord['vendor_work_category'],
                'vendor_sub_work_category'     => $vendoSubWorkCategortResults[$projectTrackRecord['project_track_record_id']],
                'project_amount'               => $projectTrackRecord['project_amount'],
                'currency_code'                => $projectTrackRecord['currency_code'],
                'project_amount_remarks'       => $projectTrackRecord['project_amount_remarks'],
                'year_of_site_possession'      => $projectTrackRecord['year_of_site_possession'],
                'year_of_completion'           => $projectTrackRecord['year_of_completion'],
                'has_qlassic_or_conquas_score' => $projectTrackRecord['has_qlassic_or_conquas_score'],
                'qlassic_score'                => $projectTrackRecord['qlassic_score'],
                'qlassic_year_of_achievement'  => $projectTrackRecord['qlassic_year_of_achievement'],
                'year_of_recognition_awards'   => $projectTrackRecord['year_of_recognition_awards'],
            ]);

            unset($projectTrackRecord);
        }

        unset($projectTrackRecords);

        $workSheet->getStyle('J')->getNumberFormat()->setFormatCode("#,##0.00");

        $workSheet->fromArray($records, null, 'A2');

        return true;
    }

    protected function generateSupplierCreditFacilitySheet(Worksheet &$workSheet, Array $companyIds)
    {
        if(empty($companyIds))
        {
            return false;
        }

        $vendorCodePrefix    = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
        $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

        $records = [];

        $query = "SELECT c.id AS company_id, c.name AS company_name, c.reference_no AS roc, scf.id AS supplier_credit_facility_id, scf.supplier_name, scf.credit_facilities
                    FROM companies c 
                    INNER JOIN vendor_registrations vr on vr.company_id = c.id
                    INNER JOIN (SELECT max(revision) as revision, company_id
                                FROM vendor_registrations
                                WHERE STATUS = " . VendorRegistration::STATUS_COMPLETED . "
                                AND deleted_at IS NULL
                                GROUP BY company_id
                                ) vrsub ON vrsub.company_id = c.id AND vrsub.revision = vr.revision
                    INNER JOIN supplier_credit_facilities scf ON scf.vendor_registration_id = vr.id
                    WHERE vr.deleted_at IS NULL
                    AND c.id IN (" . implode(', ', $companyIds) . ")
                    ORDER BY c.id ASC, scf.id ASC;";

        $supplierCreditFacilities = array_map(function ($object) {
            return (array) $object;
        }, \DB::select(\DB::raw($query)));

        $record = [];

        foreach($supplierCreditFacilities as $supplierCreditFacility)
        {
            $vendorCode = $vendorCodePrefix . str_pad($supplierCreditFacility['company_id'], $vendorCodePadLength, 0, STR_PAD_LEFT);

            array_push($records, [
                'code'              => $vendorCode,
                'company_name'      => $supplierCreditFacility['company_name'],
                'roc'               => $supplierCreditFacility['roc'],
                'supplier_name'     => $supplierCreditFacility['supplier_name'],
                'credit_facilities' => $supplierCreditFacility['credit_facilities'],
            ]);

            unset($supplierCreditFacility);
        }

        unset($supplierCreditFacilities);

        $workSheet->getStyle('E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $workSheet->fromArray($records, null, 'A2');

        return true;
    }

    public function generateDynamicFormSheets(Spreadsheet $spreadsheet, Array $companyIds, $headerStyle)
    {
        if(empty($companyIds))
        {
            return false;
        }
        $vendorCodePrefix    = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
        $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

        // get all completed vendor registration forms
        $query = "SELECT c.id AS company_id, c.contract_group_category_id AS vendor_group_id, cgc.name AS vendor_group, c.name AS company_name, c.reference_no AS roc, fom.dynamic_form_id
                    FROM companies c 
                    INNER JOIN vendor_registrations vr ON vr.company_id = c.id
                    INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id
                    INNER JOIN (SELECT max(revision) AS revision, company_id
                                FROM vendor_registrations
                                WHERE STATUS = " . VendorRegistration::STATUS_COMPLETED . "
                                AND deleted_at IS NULL
                                GROUP BY company_id
                                ) vrsub ON vrsub.company_id = c.id AND vrsub.revision = vr.revision 
                    INNER JOIN form_object_mappings fom ON fom.object_id = vr.id AND fom.object_class = '" . VendorRegistration::class . "'
                    WHERE vr.deleted_at IS NULL
                    AND cgc.hidden IS FALSE
                    AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
                    AND vr.status = " . VendorRegistration::STATUS_COMPLETED . "
                    AND c.id IN (" . implode(', ', $companyIds) . ")
                    ORDER BY c.id ASC;";

        $queryResults = DB::select(DB::raw($query));

        $dynamicFormIds = array_column($queryResults, 'dynamic_form_id');

        if(count($dynamicFormIds) == 0) return false;
        
        $dynamicFormElementValues = DynamicForm::getDynamicFormElementValues($dynamicFormIds);

        $formCompanyGroupings = [];

        foreach($queryResults as $result)
        {
            $vendorCode = $vendorCodePrefix . str_pad($result->company_id, $vendorCodePadLength, 0, STR_PAD_LEFT);

            $formCompanyGroupings[$result->dynamic_form_id]['vendor_code']     = $vendorCode;
            $formCompanyGroupings[$result->dynamic_form_id]['company_id']      = $result->company_id;
            $formCompanyGroupings[$result->dynamic_form_id]['company_name']    = $result->company_name;
            $formCompanyGroupings[$result->dynamic_form_id]['roc']             = $result->roc;
            $formCompanyGroupings[$result->dynamic_form_id]['vendor_group_id'] = $result->vendor_group_id;
            $formCompanyGroupings[$result->dynamic_form_id]['vendor_group']    = $result->vendor_group;
        }

        // find out which template form a vendor registration form is cloned from
        $query = "WITH RECURSIVE form_template_relation AS (
                            SELECT 
                            id,
                            name,
                            origin_id,
                            revision,
                            0 AS level,
                            id::TEXT AS path,
                            array[id]::INTEGER[] AS path_array
                            FROM dynamic_forms 
                            WHERE id IN (" . implode(', ', $dynamicFormIds) . ")
                        UNION ALL
                            SELECT
                            df.id,
                            df.name,
                            df.origin_id,
                            df.revision,
                            ftr.level + 1 AS level,
                            (ftr.path || '->' || df.id::TEXT) AS path,
                            array_append(ftr.path_array, df.id::INTEGER) AS path_array
                            FROM dynamic_forms df 
                            INNER JOIN form_template_relation ftr ON ftr.origin_id = df.id
                    )
                    SELECT 
                    ftr.id AS template_id,
                    ftr.name AS template_name,
                    ftr.revision AS template_revision,
                    STRING_AGG(ftr.path_array[1]::TEXT, ',')  AS dynamic_form_ids
                    FROM form_template_relation ftr
                    WHERE ftr.origin_id IS NULL
                    GROUP BY ftr.id, ftr.name, ftr.revision
                    ORDER BY ftr.id ASC;";

        $queryResults = DB::select(DB::raw($query));

        $templateFormId = implode(', ', array_column($queryResults, 'template_id'));

        // get template form element id and their classes
        // for arrangement purpose
        $templateElementLabelArrangementsQuery = "SELECT df.id AS template_id, fem.element_id, fem.element_class
                                                    FROM dynamic_forms df
                                                    INNER JOIN form_columns fc ON fc.dynamic_form_id = df.id
                                                    INNER JOIN form_column_sections fcs ON fcs.form_column_id = fc.id
                                                    INNER JOIN form_element_mappings fem ON fem.form_column_section_id = fcs.id AND fem.element_class NOT IN ('" . FileUpload::class ."')
                                                    WHERE df.id IN (" . $templateFormId . ")
                                                    ORDER BY df.id ASC, fc.priority ASC, fcs.priority ASC, fem.priority ASC;";

        $templateElementLabelArrangements = DB::select(DB::raw($templateElementLabelArrangementsQuery));

        $customTemplateElementIds = [];
        $systemTemplateElementIds = [];

        $templateHeaders = [];

        foreach($templateElementLabelArrangements as $result)
        {
            if($result->element_class == SystemModuleElement::class)
            {
                $systemTemplateElementIds[] = $result->element_id;
            }
            else
            {
                $customTemplateElementIds[] = $result->element_id;
            }

            $templateHeaders[$result->template_id][$result->element_id]['element_class'] = $result->element_class;
            $templateHeaders[$result->template_id][$result->element_id]['element_id']    = $result->element_id;
            $templateHeaders[$result->template_id][$result->element_id]['element_label'] = null;
        }

        if(count($customTemplateElementIds) > 0)
        {
            $customElementLabelQuery = "SELECT id AS element_id, label AS element_label FROM elements WHERE id IN (" . implode(', ', $customTemplateElementIds) . ");";
    
            $customElementLabelResults = DB::select(DB::raw($customElementLabelQuery));
    
            $customElementLabels = [];
    
            foreach($customElementLabelResults as $result)
            {
                $customElementLabels[$result->element_id] = $result->element_label;
            }
    
            foreach($templateHeaders as $templateId => $templateData)
            {
                foreach($templateData as $elementId => $elementData)
                {
                    if($templateHeaders[$templateId][$elementId]['element_class'] == SystemModuleElement::class) continue;
    
                    $templateHeaders[$templateId][$elementId]['element_label'] = array_key_exists($elementId, $customElementLabels) ? $customElementLabels[$elementId] : null;
                }
            }
        }

        if(count($systemTemplateElementIds) > 0)
        {
            $systemElementLabelQuery = "SELECT id AS element_id, label AS element_label FROM system_module_elements WHERE id IN (" . implode(', ', $systemTemplateElementIds) . ");";
    
            $systemElementLabelResults = DB::select(DB::raw($systemElementLabelQuery));
    
            $systemElementLabels = [];
    
            foreach($systemElementLabelResults as $result)
            {
                $systemElementLabels[$result->element_id] = $result->element_label;
            }
    
            foreach($templateHeaders as $templateId => $templateData)
            {
                foreach($templateData as $elementId => $elementData)
                {
                    if($templateHeaders[$templateId][$elementId]['element_class'] != SystemModuleElement::class) continue;
    
                    $templateHeaders[$templateId][$elementId]['element_label'] = array_key_exists($elementId, $systemElementLabels) ? $systemElementLabels[$elementId] : null;
                }
            }
        }

        $templateFormGroupings = [];
        $structuredSheetData   = [];

        foreach($queryResults as $result)
        {
            $revisionText      = ($result->template_revision == 0) ? '' : ' R' . $result->template_revision;
            $maxShetNameLength = (31 - strlen($revisionText));    //max 31 char only

            $templateFormData = [];

            $templateFormData['tab_name']            = substr($result->template_name, 0, $maxShetNameLength) . $revisionText;
            $templateFormData['template_name']       = $result->template_name . $revisionText;
            $templateFormData['template_id']         = $result->template_id;
            $templateFormData['template_name']       = $result->template_name;
            $templateFormData['template_revision']   = $result->template_revision;
            $templateFormData['dynamic_form_data']   = [];

            $dynamicFormIds = explode(',', $result->dynamic_form_ids);

            foreach($dynamicFormIds as $dynamicFormId)
            {
                array_push($templateFormData['dynamic_form_data'], [
                    'vendor_code'         => $formCompanyGroupings[$dynamicFormId]['vendor_code'],
                    'company_id'          => $formCompanyGroupings[$dynamicFormId]['company_id'],
                    'company_name'        => $formCompanyGroupings[$dynamicFormId]['company_name'],
                    'roc'                 => $formCompanyGroupings[$dynamicFormId]['roc'],
                    'vendor_group_id'     => $formCompanyGroupings[$dynamicFormId]['vendor_group_id'],
                    'vendor_group'        => $formCompanyGroupings[$dynamicFormId]['vendor_group'],
                    'dynamic_form_id'     => $dynamicFormId,
                    'dynamic_form_values' => array_key_exists($dynamicFormId, $dynamicFormElementValues) ? $dynamicFormElementValues[$dynamicFormId] : [],
                ]);
            }

            array_push($structuredSheetData, $templateFormData);
        }

        foreach($structuredSheetData as $index => $sheetData)
        {
            $exportSheetData = [];

            if($index == 0)
            {
                $activeSheet = $spreadsheet->getActiveSheet();
                $activeSheet->setTitle($sheetData['tab_name']);
            }
            else
            {
                $activeSheet = new Worksheet($spreadsheet, $sheetData['tab_name']);
                $spreadsheet->addSheet($activeSheet);
            }

            $headers = [
                trans('vendorManagement.vendorCode'),
                trans('vendorManagement.vendorName'),
                trans('vendorManagement.rocNumber'),
                trans('vendorManagement.vendorGroup'),
            ];
            
            // append column headers according to form templates
            foreach($templateHeaders[$sheetData['template_id']] as $header)
            {
                array_push($headers, $header['element_label']);
            }

            // set autofilter according to number of headers
            $endColumn = 'A';

            for($i = 1; $i < count($headers); $i++)
            {
                ++ $endColumn;
            }

            $activeSheet->setAutoFilter('A1:' . $endColumn . '1');

            $headerCount = 1;

            foreach($headers as $key => $val)
            {
                $cell = StringOperations::numberToAlphabet($headerCount)."1";
                $activeSheet->setCellValue($cell, $val);
                $activeSheet->getStyle($cell)->applyFromArray($headerStyle);
    
                $activeSheet->getColumnDimension(StringOperations::numberToAlphabet($headerCount))->setAutoSize(true);
                
                $headerCount++;
            }

            foreach($sheetData['dynamic_form_data'] as $formTemplate)
            {
                $rowData     = [];

                $rowData['vendor_code']   = $formTemplate['vendor_code'];
                $rowData['company_name']  = $formTemplate['company_name'];
                $rowData['roc']           = $formTemplate['roc'];
                $rowData['vendor_group']  = $formTemplate['vendor_group'];

                foreach($formTemplate['dynamic_form_values'] as $elementId => $formValues)
                {
                    $rowData[$elementId] = $formValues['element_value'];
                }

                array_push($exportSheetData, $rowData);
            }

            $activeSheet->fromArray($exportSheetData, null, 'A2');
        }
    }

    public function getActionsLogs($vendorProfileId)
    {
        $vendorProfile = VendorProfile::find($vendorProfileId);

        $actionLogs = ObjectLog::getActionLogs($vendorProfile);
        
        return Response::json($actionLogs);
    }

    public function vendorPerformanceEvaluationLatest($companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = CycleScore::select('vendor_evaluation_cycle_scores.id', 'vendor_work_categories.id as vendor_work_category_id', 'vendor_work_categories.name as vendor_work_category', 'vendor_evaluation_cycle_scores.score', 'vendor_evaluation_cycle_scores.deliberated_score', 'vendor_performance_evaluation_cycles.remarks as cycle', 'vendor_performance_evaluation_cycles.id as cycle_id')
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendor_evaluation_cycle_scores.vendor_work_category_id')
            ->join('vendor_performance_evaluation_cycles', 'vendor_performance_evaluation_cycles.id', '=', 'vendor_evaluation_cycle_scores.vendor_performance_evaluation_cycle_id')
            ->join(\DB::raw("(SELECT s.id, rank() over (partition by s.vendor_work_category_id order by c.created_at desc) ranking
                FROM vendor_evaluation_cycle_scores s
                JOIN vendor_performance_evaluation_cycles c ON c.id = s.vendor_performance_evaluation_cycle_id
                where s.company_id = {$companyId}) latest_cycle_scores"), 'latest_cycle_scores.id', '=', 'vendor_evaluation_cycle_scores.id')
            ->where('latest_cycle_scores.ranking', '=', 1);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'vendor_work_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_work_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'cycle':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_performance_evaluation_cycles.remarks', 'ILIKE', '%'.$val.'%');
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

                            $model->whereIn('vendor_work_categories.id', $vendorWorkCategoryIds);
                        }
                        break;
                }
            }
        }

        $model->orderBy('vendor_work_categories.name');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $vendorCategoriesByVendorWorkCategoryId = VendorCategory::select('vendor_categories.id as vendor_category_id', 'vendor_category_vendor_work_category.vendor_work_category_id', 'vendor_categories.name')
            ->join('vendor_category_vendor_work_category', 'vendor_category_vendor_work_category.vendor_category_id', '=', 'vendor_categories.id')
            ->whereIn('vendor_category_vendor_work_category.vendor_work_category_id', $records->lists('vendor_work_category_id'))
            ->orderBy('vendor_categories.name')
            ->get()
            ->groupBy('vendor_work_category_id');

        $data = [];

        $cycleIds = $records->lists('cycle_id');

        $cycles = [];

        foreach(Cycle::whereIn('id', $cycleIds)->get() as $cycle)
        {
            $cycles[$cycle->id] = $cycle;
        }

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $cycle = array_key_exists($record->cycle_id, $cycles) ? $cycles[$record->cycle_id] : null;
            $gradingSystem = ($cycle && $cycle->vendorManagementGrade) ? $cycle->vendorManagementGrade : null;

            $vendorCategoriesArray = [];

            foreach($vendorCategoriesByVendorWorkCategoryId[$record->vendor_work_category_id] as $categories)
            {
                $vendorCategoriesArray[] = $categories['name'];
            }

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'cycle'                => $record->cycle,
                'vendor_categories'    => $vendorCategoriesArray,
                'vendor_work_category' => $record->vendor_work_category,
                'score'                => $record->score,
                'grade'                => $gradingSystem ? $gradingSystem->getGrade($record->score)->description : '-',
                'deliberated_score'    => $record->deliberated_score,
                'deliberated_grade'    => $gradingSystem ? $gradingSystem->getGrade($record->deliberated_score)->description : '-',
                'route:historical'     => route('vendorProfile.vendorPerformanceEvaluation.historic', array($companyId, $record->vendor_work_category_id)),
                'route:evaluations'    => route('vendorProfile.vendorPerformanceEvaluation.cycleEvaluations', array($companyId, $record->vendor_work_category_id, $record->cycle_id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorPerformanceEvaluationHistoric($companyId, $vendorWorkCategoryId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = CycleScore::select('vendor_evaluation_cycle_scores.id', 'vendor_evaluation_cycle_scores.score', 'vendor_evaluation_cycle_scores.deliberated_score', 'vendor_performance_evaluation_cycles.remarks as cycle', 'vendor_performance_evaluation_cycles.id as cycle_id')
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendor_evaluation_cycle_scores.vendor_work_category_id')
            ->join('vendor_performance_evaluation_cycles', 'vendor_performance_evaluation_cycles.id', '=', 'vendor_evaluation_cycle_scores.vendor_performance_evaluation_cycle_id')
            ->where('vendor_work_categories.id', '=', $vendorWorkCategoryId)
            ->where('company_id', '=', $companyId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'cycle':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_performance_evaluation_cycles.remarks', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('vendor_performance_evaluation_cycles.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $gradingSystem = VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;

        $data = [];

        $cycleIds = $records->lists('cycle_id');

        $cycles = [];

        foreach(Cycle::whereIn('id', $cycleIds)->get() as $cycle)
        {
            $cycles[$cycle->id] = $cycle;
        }

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $cycle = array_key_exists($record->cycle_id, $cycles) ? $cycles[$record->cycle_id] : null;
            $gradingSystem = ($cycle && $cycle->vendorManagementGrade) ? $cycle->vendorManagementGrade : null;

            $data[] = [
                'id'                => $record->id,
                'counter'           => $counter,
                'cycle'             => $record->cycle,
                'score'             => $record->score,
                'grade'             => $gradingSystem ? $gradingSystem->getGrade($record->score)->description : '-',
                'deliberated_score' => $record->deliberated_score,
                'deliberated_grade' => $gradingSystem ? $gradingSystem->getGrade($record->deliberated_score)->description : '-',
                'route:evaluations' => route('vendorProfile.vendorPerformanceEvaluation.cycleEvaluations', array($companyId, $vendorWorkCategoryId, $record->cycle_id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorPerformanceEvaluationCycleEvaluations($companyId, $vendorWorkCategoryId, $cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = EvaluationScore::select('vendor_evaluation_scores.id', 'projects.reference', 'projects.title', 'vendor_evaluation_scores.score', 'projects.subsidiary_id', 'vendor_performance_evaluations.id as evaluation_id', 'vendor_performance_evaluations.vendor_performance_evaluation_cycle_id AS cycle_id')
            ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_evaluation_scores.vendor_performance_evaluation_id')
            ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
            ->where('vendor_evaluation_scores.vendor_work_category_id', '=', $vendorWorkCategoryId)
            ->where('company_id', '=', $companyId)
            ->where('vendor_performance_evaluations.vendor_performance_evaluation_cycle_id', '=', $cycleId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'title':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'business_unit':
                        if(strlen($val) > 0)
                        {
                            $subsidiaryIds = Subsidiary::select('id')
                                ->whereNull('parent_id')
                                ->where('name', 'ILIKE', '%'.$val.'%')
                                ->lists('id');

                            $matchingRootSubsidiariesAndDescendants = Subsidiary::getSelfAndDescendantIds($subsidiaryIds);

                            $selfAndDescendantSubsidiaryIds = [];

                            foreach($matchingRootSubsidiariesAndDescendants as $descendantIds)
                            {
                                $selfAndDescendantSubsidiaryIds = array_merge($selfAndDescendantSubsidiaryIds, $descendantIds);
                            }

                            $model->whereIn('projects.subsidiary_id', $selfAndDescendantSubsidiaryIds);
                        }
                        break;
                }
            }
        }

        $model->orderBy('projects.title', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $gradingSystem = VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;

        $rootSubsidiaries = Subsidiary::getTopParentsGroupedBySubsidiaryIds($records->lists('subsidiary_id'));

        $data = [];

        $cycleIds = $records->lists('cycle_id');

        $cycles = [];

        foreach(Cycle::whereIn('id', $cycleIds)->get() as $cycle)
        {
            $cycles[$cycle->id] = $cycle;
        }

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $cycle = array_key_exists($record->cycle_id, $cycles) ? $cycles[$record->cycle_id] : null;
            $gradingSystem = ($cycle && $cycle->vendorManagementGrade) ? $cycle->vendorManagementGrade : null;

            $data[] = [
                'id'            => $record->id,
                'cycle_id'      => $record->cycle_id,
                'counter'       => $counter,
                'reference'     => $record->reference,
                'title'         => $record->title,
                'score'         => $record->score,
                'grade'         => $gradingSystem ? $gradingSystem->getGrade($record->score)->description : '-',
                'business_unit' => $rootSubsidiaries[$record->subsidiary_id]['name'],
                'route:forms'   => route('vendorProfile.vendorPerformanceEvaluation.forms', array($companyId, $vendorWorkCategoryId, $record->evaluation_id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function evaluationForms($companyId, $vendorWorkCategoryId, $evaluationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorPerformanceEvaluationCompanyForm::select('vendor_performance_evaluation_company_forms.id', 'companies.name as evaluator', 'vendor_performance_evaluation_company_forms.score')
            ->join('companies', 'companies.id', '=', 'vendor_performance_evaluation_company_forms.evaluator_company_id')
            ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_performance_evaluation_company_forms.vendor_performance_evaluation_id')
            ->where('company_id', '=', $companyId)
            ->where('vendor_performance_evaluation_company_forms.status_id', '=', VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED)
            ->where('vendor_performance_evaluation_company_forms.vendor_performance_evaluation_id', '=', $evaluationId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'evaluator':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('companies.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $vendorPerformanceEvaluation = VendorPerformanceEvaluation::find($evaluationId);

        $gradingSystem = $vendorPerformanceEvaluation->cycle->vendorManagementGrade ? $vendorPerformanceEvaluation->cycle->vendorManagementGrade : null;

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                  => $record->id,
                'counter'             => $counter,
                'evaluator'           => $record->evaluator,
                'score'               => $record->score,
                'grade'               => $gradingSystem ? $gradingSystem->getGrade($record->score)->description : '-',
                'route:evaluator_log' => route('vendorProfile.vendorPerformanceEvaluation.form.evaluatorLog', array($companyId, $record->id)),
                'route:verifier_log'  => route('vendorProfile.vendorPerformanceEvaluation.form.verifierLog', array($companyId, $record->id)),
                'route:edit_log'      => route('vendorProfile.vendorPerformanceEvaluation.form.editLog', array($companyId, $record->id)),
                'route:download'      => route('vendorProfile.vendorPerformanceEvaluation.form.export', array($companyId, $record->id)),
                'route:form_info'     => route('vendorProfile.vendorPerformanceEvaluation.form.information', array($companyId, $record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function evaluationFormEvaluationEvaluatorLog($companyId, $companyFormId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorPerformanceEvaluationCompanyFormEvaluationLog::select('vendor_performance_evaluation_company_form_evaluation_logs.id', 'vendor_performance_evaluation_company_form_evaluation_logs.action_type', 'vendor_performance_evaluation_company_form_evaluation_logs.created_at', 'users.name')
            ->join('users', 'users.id', '=', 'vendor_performance_evaluation_company_form_evaluation_logs.created_by')
            ->where('vendor_performance_evaluation_company_form_evaluation_logs.vendor_performance_evaluation_company_form_id', '=', $companyFormId);

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('vendor_performance_evaluation_company_form_evaluation_logs.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'         => $record->id,
                'counter'    => $counter,
                'evaluator'  => $record->name,
                'action'     => $record->getActionDescription(),
                'created_at' => Carbon::parse($record->created_at)->format(\Config::get('dates.created_at')),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function evaluationFormEvaluationVerifierLog($companyId, $companyFormId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Verifier::select('verifiers.id', 'verifiers.approved', 'verifiers.verified_at', 'verifiers.remarks', 'users.name')
            ->join('users', 'users.id', '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', '=', $companyFormId)
            ->where('verifiers.object_type', '=', VendorPerformanceEvaluationCompanyForm::class);

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'          => $record->id,
                'counter'     => $counter,
                'name'        => $record->name,
                'approved'    => $record->approved,
                'verified_at' => Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')),
                'remarks'     => $record->remarks,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function evaluationFormEvaluationEditLog($companyId, $companyFormId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorPerformanceEvaluationProcessorEditLog::select('vendor_performance_evaluation_processor_edit_logs.id', 'users.name', 'vendor_performance_evaluation_processor_edit_logs.created_at')
            ->join('users', 'users.id', '=', 'vendor_performance_evaluation_processor_edit_logs.user_id')
            ->where('vendor_performance_evaluation_processor_edit_logs.vendor_performance_evaluation_company_form_id', '=', $companyFormId);

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('vendor_performance_evaluation_processor_edit_logs.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->id,
                'counter'       => $counter,
                'name'          => $record->name,
                'created_at'    => Carbon::parse($record->created_at)->format(\Config::get('dates.created_at')),
                'route:details' => route('vendorProfile.vendorPerformanceEvaluation.form.editDetailsLog', array($companyId, $companyFormId, $record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function evaluationFormEvaluationEditDetailsLog($companyId, $companyFormId, $editLogId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorPerformanceEvaluationProcessorEditDetail::select(
                'vendor_performance_evaluation_processor_edit_details.id',
                'weighted_nodes.name as node_name',
                'previous_score.name as previous_score_name',
                'previous_score.value as previous_score_value',
                'vendor_performance_evaluation_processor_edit_details.is_previous_node_excluded as previous_score_excluded',
                'current_score.name as current_score_name',
                'current_score.value as current_score_value',
                'vendor_performance_evaluation_processor_edit_details.is_current_node_excluded as current_score_excluded'
            )
            ->join('weighted_nodes', 'weighted_nodes.id', '=', 'vendor_performance_evaluation_processor_edit_details.weighted_node_id')
            ->leftJoin('weighted_node_scores as previous_score', 'previous_score.id', '=', 'vendor_performance_evaluation_processor_edit_details.previous_score_id')
            ->leftJoin('weighted_node_scores as current_score', 'current_score.id', '=', 'vendor_performance_evaluation_processor_edit_details.current_score_id')
            ->where('vendor_performance_evaluation_processor_edit_details.vendor_performance_evaluation_processor_edit_log_id', '=', $editLogId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'node_name':
                        if(strlen($val) > 0)
                        {
                            $model->where('weighted_nodes.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('vendor_performance_evaluation_processor_edit_details.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                      => $record->id,
                'counter'                 => $counter,
                'node_name'               => $record->node_name,
                'previous_score_name'     => (is_null($record->previous_score_name) || $record->previous_score_excluded) ? trans('general.notAvailable') : $record->previous_score_name,
                'previous_score_value'    => (is_null($record->previous_score_value) || $record->previous_score_excluded) ? trans('general.notAvailable') : $record->previous_score_value,
                'previous_score_excluded' => $record->previous_score_excluded ? trans('forms.notApplicable') : '-',
                'current_score_name'      => (is_null($record->current_score_name) || $record->current_score_excluded) ? trans('general.notAvailable') : $record->current_score_name,
                'current_score_value'     => (is_null($record->current_score_value) || $record->current_score_excluded) ? trans('general.notAvailable') : $record->current_score_value,
                'current_score_excluded'  => $record->current_score_excluded ? trans('forms.notApplicable') : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function evaluationFormInformation($companyId, $companyFormId)
    {
        $form = VendorPerformanceEvaluationCompanyForm::find($companyFormId);

        $grading = $form->vendorPerformanceEvaluation->cycle->vendorManagementGrade ? $form->vendorPerformanceEvaluation->cycle->vendorManagementGrade : null;

        return Response::json(array(
            'route:grid'           => route('vendorProfile.vendorPerformanceEvaluation.form.show', array($companyId, $form->id)),
            'project_reference'    => $form->vendorPerformanceEvaluation->project->reference,
            'project'              => $form->vendorPerformanceEvaluation->project->title,
            'company'              => $form->company->name,
            'vendor_work_category' => $form->vendorWorkCategory->name,
            'form_name'            => $form->weightedNode->name,
            'status'               => VendorPerformanceEvaluationCompanyForm::getStatusText($form->status_id),
            'evaluator'            => $form->evaluatorCompany->name,
            'score'                => $form->score,
            'rating'               => $grading ? $grading->getGrade($form->score)->description : '',
            'remarks'              => empty($form->evaluator_remarks) ? trans('general.noRemarks') : $form->evaluator_remarks,
            'route:attachments'    => route('vendorProfile.vendorPerformanceEvaluation.form.attachments', array($companyId, $form->id)),
        ));
    }

    public function evaluationForm($companyId, $companyFormId)
    {
        $companyForm = VendorPerformanceEvaluationCompanyForm::find($companyFormId);

        $form = WeightedNode::find($companyForm->weighted_node_id);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        return Response::json($data);
    }

    public function evaluationFormAttachments($companyId, $companyFormId)
    {
        $request = Request::instance();

        $form = VendorPerformanceEvaluationCompanyForm::find($companyFormId);

        $data = [];

        foreach($form->getAttachmentDetails() as $upload)
        {
            $data[] = array(
                'filename'     => $upload->filename,
                'download_url' => $upload->download_url,
                'uploaded_by'  => $upload->createdBy->name,
                'uploaded_at'  => Carbon::parse($upload->created_at)->format(\Config::get('dates.created_at')),
            );
        }

        return Response::json($data);
    }

    public function evaluationFormExport($companyId, $companyFormId)
    {
        $form = VendorPerformanceEvaluationCompanyForm::find($companyFormId);

        $reportGenerator = new VendorPerformanceEvaluationFormExcelGenerator();

        $reportGenerator->setSpreadsheetTitle(trans('vendorManagement.vendorPerformanceEvaluation').' '.$form->company->name.' '.$form->vendorWorkCategory->name);

        $reportGenerator->addWorkSheet([$form], $form->company->name);

        return $reportGenerator->generate();
    }

    public function preQualificationFormExport($companyId, $vendorPreQualificationId)
    {
        $form = VendorPreQualification::find($vendorPreQualificationId);

        $reportGenerator = new VendorPreQualificationFormExcelGenerator();

        $reportGenerator->setSpreadsheetTitle(trans('vendorManagement.vendorPreQualification').' '.$form->vendorRegistration->company->name.' '.$form->vendorWorkCategory->name);

        $reportGenerator->addWorkSheet([$form], $form->vendorRegistration->company->name);

        return $reportGenerator->generate();
    }

    public function getVendorRegistrationRemarkLogs($companyId)
    {
        $company = Company::find($companyId);

        $data = [
            [
                'section'  => trans('vendorManagement.vendorRegistration'),
                'remarks'  => nl2br(trim($company->finalVendorRegistration->getProcessorRemarks())),
                'dateTime' => Carbon::parse($company->finalVendorRegistration->updated_at)->format(\Config::get('dates.full_format')),
            ],
            [
                'section'  => trans('vendorManagement.companyDetails'),
                'remarks'  => nl2br(trim($company->finalVendorRegistration->getSection(Section::SECTION_COMPANY_DETAILS)->amendment_remarks)),
                'dateTime' => Carbon::parse($company->finalVendorRegistration->getSection(Section::SECTION_COMPANY_DETAILS)->updated_at)->format(\Config::get('dates.full_format')),
            ],
            [
                'section'  => trans('vendorManagement.companyPersonnel'),
                'remarks'  => nl2br(trim($company->finalVendorRegistration->getSection(Section::SECTION_COMPANY_PERSONNEL)->amendment_remarks)),
                'dateTime' => Carbon::parse($company->finalVendorRegistration->getSection(Section::SECTION_COMPANY_PERSONNEL)->updated_at)->format(\Config::get('dates.full_format')),
            ],
            [
                'section'  => trans('vendorManagement.projectTrackRecord'),
                'remarks'  => nl2br(trim($company->finalVendorRegistration->getSection(Section::SECTION_PROJECT_TRACK_RECORD)->amendment_remarks)),
                'dateTime' => Carbon::parse($company->finalVendorRegistration->getSection(Section::SECTION_PROJECT_TRACK_RECORD)->updated_at)->format(\Config::get('dates.full_format')),
            ],
            [
                'section'  => trans('vendorManagement.supplierCreditFacilities'),
                'remarks'  => nl2br(trim($company->finalVendorRegistration->getSection(Section::SECTION_SUPPLIER_CREDIT_FACILITIES)->amendment_remarks)),
                'dateTime' => Carbon::parse($company->finalVendorRegistration->getSection(Section::SECTION_SUPPLIER_CREDIT_FACILITIES)->updated_at)->format(\Config::get('dates.full_format')),
            ],
            [
                'section'  => trans('vendorManagement.vendorRegistrationPayment'),
                'remarks'  => nl2br(trim($company->finalVendorRegistration->getSection(Section::SECTION_PAYMENT)->amendment_remarks)),
                'dateTime' => Carbon::parse($company->finalVendorRegistration->getSection(Section::SECTION_PAYMENT)->updated_at)->format(\Config::get('dates.full_format')),
            ],
        ];

        return Response::json($data);
    }

    public function getConsultantContractDetails($companyId, $contractId)
    {
        $contract     = ConsultantManagementContract::find($contractId);
        $currencyCode = empty($contract->modified_currency_code) ? $contract->country->currency_code : $contract->modified_currency_code;

        $details = ConsultantManagementSubsidiary::select("consultant_management_subsidiaries.consultant_management_contract_id AS contract_id", "consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount AS proposed_fee", "vendor_categories.name as vendor_category")
                    ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_subsidiary_id', '=', 'consultant_management_subsidiaries.id')
                    ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
                    ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id')
                    ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.vendor_category_rfp_id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
                    ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_letter_of_awards.vendor_category_rfp_id')
                    ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
                    ->where('consultant_management_consultant_rfp.company_id', '=', $companyId)
                    ->where('consultant_management_subsidiaries.consultant_management_contract_id', $contract->id)
                    ->whereRaw('consultant_management_consultant_rfp.awarded IS TRUE')
                    ->where('consultant_management_letter_of_awards.status', LetterOfAward::STATUS_APPROVED)
                    ->whereRaw('vendor_categories.hidden IS FALSE')
                    ->get()
                    ->toArray();

        $data = [];
        $sum  = 0.0;

        $data['currency_code'] = $currencyCode;

        foreach($details as $detail)
        {
            $data['data'][] = [
                'currency_code'   => $currencyCode,
                'proposed_fee'    => number_format($detail['proposed_fee'], 2),
                'vendor_category' => $detail['vendor_category'],
            ];

            $sum += $detail['proposed_fee'];
        }

        $data['sum'] = number_format($sum, 2);

        return Response::json($data);
    }
}