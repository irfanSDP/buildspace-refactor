<?php

use Illuminate\Support\Facades\DB;
use PCK\Vendor\Vendor;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\Forms\NominatedWatchListVendorForm;
use Carbon\Carbon;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\CycleScore;
use PCK\VendorPerformanceEvaluation\EvaluationScore;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyFormEvaluationLog;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationProcessorEditLog;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationProcessorEditDetail;
use PCK\VendorManagement\VendorManagementUserPermission;
use PCK\Verifier\Verifier;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Companies\Company;
use PCK\VendorCategory\VendorCategory;
use PCK\Subsidiaries\Subsidiary;
use PCK\Reports\VendorPerformanceEvaluationFormExcelGenerator;
use PCK\Reports\VendorListScoreExcelGenerator;
use PCK\VendorRegistration\VendorRegistration;
use PCK\Reports\VendorListScoreWithWorkSubCategoriesExcelGenerator;

class NominatedWatchListVendorsController extends \BaseController {

    protected $nominatedWatchListVendorForm;
    protected $weightedNodeRepository;

    public function __construct(NominatedWatchListVendorForm $nominatedWatchListVendorForm, WeightedNodeRepository $weightedNodeRepository)
    {
        $this->nominatedWatchListVendorForm = $nominatedWatchListVendorForm;
        $this->weightedNodeRepository       = $weightedNodeRepository;
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

        return View::make('vendor_management.lists.nominated_watch_list_vendors.index', compact('externalVendorGroupsFilterOptions'));
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

        $model = Vendor::select(
                'vendors.id',
                'vendors.type',
                'companies.id as company_id',
                'companies.name as company',
                'contract_group_categories.name as contract_group_category',
                'vendor_work_categories.name as vendor_work_category',
                'cycle_score.score',
                'cycle_score.deliberated_score',
                'cycles.id as cycle_id',
                'cycles.remarks as cycle',
                'vendors.vendor_work_category_id'
            )
            ->join('companies', 'companies.id', '=', 'vendors.company_id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->where(function($query){
                $query->where('vendors.type', '=', Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION);
                $query->orWhere('vendors.type', '=', Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST);
            })
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
            ->leftJoin('vendor_evaluation_cycle_scores as cycle_score', 'cycle_score.id', '=', 'vendors.vendor_evaluation_cycle_score_id')
            ->leftJoin('vendor_performance_evaluation_cycles as cycles', 'cycles.id', '=', 'cycle_score.vendor_performance_evaluation_cycle_id')
            ->whereIn('vendors.type', [Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION, Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST] )
            ->where('companies.confirmed', '=', true)
            ->where('vendor_work_categories.hidden', '=', false)
            ->where('contract_group_categories.hidden', '=', false)
            ->where('contract_group_categories.type', '=', ContractGroupCategory::TYPE_EXTERNAL)
            ->whereNull('companies.deactivated_at')
            ->whereNotNull('companies.activation_date');

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
                        if((int)$val > 0)
                        {
                            $model->where('contract_group_categories.id', '=', $val);
                        }
                        break;
                    case 'vendor_work_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_work_categories.name', 'ILIKE', '%'.$val.'%');
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
                    case 'vendor_code':
                        if(strlen($val) > 0)
                        {
                            $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
                            $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

                            $model->where(\DB::raw("'" . $vendorCodePrefix . "' || LPAD(companies.id::text, " . $vendorCodePadLength . ", '0')"), 'ILIKE', '%' . $val . '%');
                        }
                        break;
                    case 'from':
                        if($val === trans('vendorManagement.vendorPerformanceEvaluation'))
                        {
                            $model->where('vendors.type', '=', Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION);
                        }
                        elseif($val === trans('vendorManagement.watchList'))
                        {
                            $model->where('vendors.type', '=', Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST);
                        }
                        break;
                }
            }
        }

        $model->orderBy('companies.name', 'asc');
        $model->orderBy('vendor_work_categories.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $gradingSystem = VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;

        $vendorCategoriesByVendorWorkCategoryId = VendorCategory::select('vendor_categories.id as vendor_category_id', 'vendor_category_vendor_work_category.vendor_work_category_id', 'vendor_categories.name')
            ->join('vendor_category_vendor_work_category', 'vendor_category_vendor_work_category.vendor_category_id', '=', 'vendor_categories.id')
            ->whereIn('vendor_category_vendor_work_category.vendor_work_category_id', $records->lists('vendor_work_category_id'))
            ->orderBy('vendor_categories.name')
            ->get()
            ->groupBy('vendor_work_category_id');

        $canEdit = VendorManagementUserPermission::hasPermission($user, VendorManagementUserPermission::TYPE_NOMINATED_WATCH_LIST_EDIT);

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $vendorCategoriesArray = [];

            foreach($vendorCategoriesByVendorWorkCategoryId[$record->vendor_work_category_id] as $categories)
            {
                $vendorCategoriesArray[] = $categories['name'];
            }

            $data[] = [
                'id'                      => $record->id,
                'counter'                 => $counter,
                'vendor_code'             => Company::getVendorCodeFromId($record->company_id),
                'company'                 => $record->company,
                'contract_group_category' => $record->contract_group_category,
                'vendor_work_category'    => $record->vendor_work_category,
                'vendor_categories'       => $vendorCategoriesArray,
                'cycle'                   => $record->cycle,
                'score'                   => $record->score,
                'deliberated_score'       => $record->deliberated_score,
                'from'                    => $record->type == Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION ? trans('vendorManagement.vendorPerformanceEvaluation') : trans('vendorManagement.watchList'),
                'rating'                  => ($gradingSystem && $record->deliberated_score) ? $gradingSystem->getGrade($record->deliberated_score)->description : null,
                'route:vendor_profile'    => route('vendorProfile.show', array($record->company_id)),
                'can_edit'                => $canEdit,
                'route:edit'              => route('vendorManagement.nominatedWatchList.edit', array($record->id)),
                'route:evaluations'       => $record->cycle_id ? route('vendorManagement.nominatedWatchList.cycleEvaluations', array($record->id, $record->cycle_id)) : null,
                'disable_scores'          => is_null($record->cycle_id),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorPerformanceEvaluationCycleEvaluations($vendorId, $cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $vendor = Vendor::find($vendorId);

        $model = EvaluationScore::select(
                'vendor_evaluation_scores.id',
                'projects.reference',
                'projects.title',
                'vendor_evaluation_scores.score',
                'projects.subsidiary_id',
                'vendor_performance_evaluations.id as evaluation_id')
            ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_evaluation_scores.vendor_performance_evaluation_id')
            ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
            ->where('vendor_evaluation_scores.vendor_work_category_id', '=', $vendor->vendor_work_category_id)
            ->where('company_id', '=', $vendor->company_id)
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

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->id,
                'counter'       => $counter,
                'reference'     => $record->reference,
                'title'         => $record->title,
                'score'         => $record->score,
                'grade'         => $gradingSystem ? $gradingSystem->getGrade($record->score)->description : '-',
                'business_unit' => $rootSubsidiaries[$record->subsidiary_id]['name'],
                'route:forms'   => route('vendorManagement.nominatedWatchList.forms', array($vendorId, $record->evaluation_id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function evaluationForms($vendorId, $evaluationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $vendor = Vendor::find($vendorId);

        $model = VendorPerformanceEvaluationCompanyForm::select('vendor_performance_evaluation_company_forms.id', 'companies.name as evaluator', 'vendor_performance_evaluation_company_forms.score')
            ->join('companies', 'companies.id', '=', 'vendor_performance_evaluation_company_forms.evaluator_company_id')
            ->where('vendor_performance_evaluation_company_forms.vendor_work_category_id', '=', $vendor->vendor_work_category_id)
            ->where('company_id', '=', $vendor->company_id)
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

        $gradingSystem = VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;

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
                'route:evaluator_log' => route('vendorManagement.nominatedWatchList.form.evaluatorLog', array($vendorId, $record->id)),
                'route:verifier_log'  => route('vendorManagement.nominatedWatchList.form.verifierLog', array($vendorId, $record->id)),
                'route:edit_log'      => route('vendorManagement.nominatedWatchList.form.editLog', array($vendorId, $record->id)),
                'route:download'      => route('vendorManagement.nominatedWatchList.form.export', array($vendorId, $record->id)),
                'route:form_info'     => route('vendorManagement.nominatedWatchList.form.information', array($vendorId, $record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function evaluationFormEvaluationEvaluatorLog($vendorId, $companyFormId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorPerformanceEvaluationCompanyFormEvaluationLog::select('vendor_performance_evaluation_company_form_evaluation_logs.id', 'vendor_performance_evaluation_company_form_evaluation_logs.action_type', 'users.name')
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

    public function evaluationFormEvaluationVerifierLog($vendorId, $companyFormId)
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

    public function evaluationFormEvaluationEditLog($vendorId, $companyFormId)
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
                'route:details' => route('vendorManagement.nominatedWatchList.form.editDetailsLog', array($vendorId, $companyFormId, $record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function evaluationFormEvaluationEditDetailsLog($vendorId, $companyFormId, $editLogId)
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

    public function evaluationFormInformation($vendorId, $companyFormId)
    {
        $form = VendorPerformanceEvaluationCompanyForm::find($companyFormId);

        $grading = VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;

        return Response::json(array(
            'route:grid'           => route('vendorManagement.nominatedWatchList.form.show', array($vendorId, $form->id)),
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
            'route:attachments'    => route('vendorManagement.nominatedWatchList.form.attachments', array($vendorId, $form->id)),
        ));
    }

    public function evaluationForm($vendorId, $companyFormId)
    {
        $companyForm = VendorPerformanceEvaluationCompanyForm::find($companyFormId);

        $form = WeightedNode::find($companyForm->weighted_node_id);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        return Response::json($data);
    }

    public function evaluationFormAttachments($vendorId, $companyFormId)
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

    public function evaluationFormExport($vendorId, $companyFormId)
    {
        $form = VendorPerformanceEvaluationCompanyForm::find($companyFormId);

        $reportGenerator = new VendorPerformanceEvaluationFormExcelGenerator();

        $reportGenerator->setSpreadsheetTitle(trans('vendorManagement.vendorPerformanceEvaluation').' '.$form->company->name.' '.$form->vendorWorkCategory->name);

        $reportGenerator->addWorkSheet([$form], $form->company->name);

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
            WHERE v.type IN (" . implode(', ', [Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION, Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST]) . ")
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
            WHERE v.type IN (" . implode(', ', [Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION, Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST]) . ")
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
            WHERE v.type IN (" . implode(', ', [Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION, Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST]) . ")
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

    public function edit($vendorId)
    {
        $vendor = Vendor::find($vendorId);

        $watchListNomineeToActiveVendorListThresholdScore = VendorProfileModuleParameter::getValue('watch_list_nomineee_to_active_vendor_list_threshold_score');
        $watchListNomineeToWatchListThresholdScore = VendorProfileModuleParameter::getValue('watch_list_nomineee_to_watch_list_threshold_score');

        return View::make('vendor_management.lists.nominated_watch_list_vendors.edit', compact('vendor', 'watchListNomineeToActiveVendorListThresholdScore', 'watchListNomineeToWatchListThresholdScore'));
    }

    public function update($vendorId)
    {
        $this->nominatedWatchListVendorForm->validate(Input::all());

        $vendor = Vendor::find($vendorId);

        $company = $vendor->company;

        $vendorWorkCategoryId = $vendor->vendor_work_category_id;

        $vendor->score->deliberated_score = Input::get('deliberated_score');

        $vendor->score->save();

        if(Input::has('submit') && Input::get('submit') == 'to-active-vendor-list' && Input::get('deliberated_score') > VendorProfileModuleParameter::getValue('watch_list_nomineee_to_active_vendor_list_threshold_score'))
        {
            $vendor->moveToActiveVendorList();

            \Flash::success(trans('vendorManagement.pushedXToActiveVendorList', array('company' => $company->name)));
        }
        if(Input::has('submit') && Input::get('submit') == 'push-to-watch-list' && Input::get('deliberated_score') < VendorProfileModuleParameter::getValue('watch_list_nomineee_to_watch_list_threshold_score'))
        {
            $vendor->moveToWatchList();

            \Flash::success(trans('vendorManagement.pushedXToWatchList', array('company' => $company->name)));
        }

        return Redirect::route('vendorManagement.nominatedWatchList');
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
            ->whereIn('vendors.company_id', Company::where('vendor_status', '=', Company::VENDOR_STATUS_NOMINATED_WATCH_LIST)->lists('id'))
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

        $reportGenerator->setSpreadsheetTitle(trans('vendorManagement.nomineesForWatchList'));

        $reportGenerator->setFilters(Input::get('filters') ?? []);

        $reportGenerator->setCompanyIds(Company::where('vendor_status', '=', Company::VENDOR_STATUS_NOMINATED_WATCH_LIST)->lists('id'));

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
                      AND v.type IN (" . implode(', ', [Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION, Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST]) . ") 
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

        $reportGenerator->setSpreadsheetTitle(trans('vendorManagement.nominatedWatchListCategories'));

        $reportGenerator->setFilters(Input::get('filters') ?? []);

        $reportGenerator->setListType([Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION, Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST]);

        return $reportGenerator->generate();
    }
}