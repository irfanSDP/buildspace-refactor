<?php

use Carbon\Carbon;
use PCK\Projects\Project;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\FormChangeRequest;
use PCK\VendorPerformanceEvaluation\FormChangeLog;
use PCK\VendorPerformanceEvaluation\RemovalRequest;
use PCK\VendorCategory\VendorCategory;
use PCK\Forms\VendorPerformanceEvaluationCycleForm;
use PCK\Forms\VendorPerformanceEvaluationCycleAddProjectForm;
use PCK\CompanyProject\CompanyProject;
use PCK\Helpers\DBTransaction;

class VendorPerformanceEvaluationCyclesController extends \BaseController {

    protected $vendorPerformanceEvaluationCycleForm;
    protected $vendorPerformanceEvaluationCycleAddProjectForm;

    public function __construct(VendorPerformanceEvaluationCycleForm $vendorPerformanceEvaluationCycleForm, VendorPerformanceEvaluationCycleAddProjectForm $vendorPerformanceEvaluationCycleAddProjectForm)
    {
        $this->vendorPerformanceEvaluationCycleForm           = $vendorPerformanceEvaluationCycleForm;
        $this->vendorPerformanceEvaluationCycleAddProjectForm = $vendorPerformanceEvaluationCycleAddProjectForm;
    }

    public function index()
    {
        $canAddCycle = ! Cycle::hasOngoingCycle();

        return View::make('vendor_performance_evaluation.cycles.index', compact('canAddCycle'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Cycle::select('vendor_performance_evaluation_cycles.id',
                'vendor_performance_evaluation_cycles.start_date',
                'vendor_performance_evaluation_cycles.end_date',
                'vendor_performance_evaluation_cycles.is_completed',
                'vendor_performance_evaluation_cycles.remarks')
            ->orderBy('vendor_performance_evaluation_cycles.id', 'desc');

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
                'start_date'                 => Carbon::parse($record->start_date)->format(\Config::get('dates.submitted_at')),
                'end_date'                   => Carbon::parse($record->end_date)->format(\Config::get('dates.submitted_at')),
                'completed'                  => $record->is_completed,
                'remarks'                    => $record->remarks,
                'route:edit'                 => route('vendorPerformanceEvaluation.cycle.edit', array($record->id)),
                'route:projects'             => route('vendorPerformanceEvaluation.setups.index') . "?cycle={$record->id}",
                'route:form_change_requests' => route('vendorPerformanceEvaluation.cycle.formChangeRequests', array($record->id)),
                'route:form_changes'         => route('vendorPerformanceEvaluation.cycle.formChanges', array($record->id)),
                'route:removal_requests'     => route('vendorPerformanceEvaluation.cycle.removalRequests', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function formChangeRequests($cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = FormChangeRequest::select('vendor_performance_evaluation_form_change_requests.id',
                'vendor_performance_evaluation_form_change_requests.created_at',
                'users.name as requested_by',
                'projects.title as project',
                'projects.reference as reference',
                'companies.name as company',
                'vendor_work_categories.name as vendor_work_category',
                'vendor_work_categories.id as vendor_work_category_id',
                'vendor_performance_evaluation_form_change_requests.remarks'
            )
            ->join('users', 'users.id', '=', 'vendor_performance_evaluation_form_change_requests.user_id')
            ->join('companies', 'companies.id', '=', 'users.company_id')
            ->join('vendor_performance_evaluation_setups', 'vendor_performance_evaluation_setups.id', '=', 'vendor_performance_evaluation_form_change_requests.vendor_performance_evaluation_setup_id')
            ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_performance_evaluation_setups.vendor_performance_evaluation_id')
            ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendor_performance_evaluation_setups.vendor_work_category_id')
            ->where('vendor_performance_evaluations.vendor_performance_evaluation_cycle_id', '=', $cycleId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'requested_by':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'company':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'project':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
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
                }
            }
        }

        $model->orderBy('vendor_performance_evaluation_form_change_requests.created_at', 'desc');

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

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $vendorCategoriesArray = [];

            foreach($vendorCategoriesByVendorWorkCategoryId[$record->vendor_work_category_id] as $categories)
            {
                $vendorCategoriesArray[] = $categories['name'];
            }

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'requested_by'         => $record->requested_by,
                'requested_at'         => Carbon::parse($record->created_at)->format(\Config::get('dates.submitted_at')),
                'company'              => $record->company,
                'project'              => $record->project,
                'reference'            => $record->reference,
                'vendor_work_category' => $record->vendor_work_category,
                'vendor_categories'    => $vendorCategoriesArray,
                'remarks'              => $record->remarks,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function formChanges($cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = FormChangeLog::select('vendor_performance_evaluation_form_change_logs.id',
                'vendor_performance_evaluation_form_change_logs.created_at',
                'users.name as updated_by',
                'projects.title as project',
                'projects.reference as reference',
                'companies.name as company',
                'vendor_work_categories.name as vendor_work_category',
                'vendor_work_categories.id as vendor_work_category_id',
                'old_node.name as old_form',
                'new_node.name as new_form'
            )
            ->join('users', 'users.id', '=', 'vendor_performance_evaluation_form_change_logs.user_id')
            ->join('vendor_performance_evaluation_setups', 'vendor_performance_evaluation_setups.id', '=', 'vendor_performance_evaluation_form_change_logs.vendor_performance_evaluation_setup_id')
            ->join('companies', 'companies.id', '=', 'vendor_performance_evaluation_setups.company_id')
            ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_performance_evaluation_setups.vendor_performance_evaluation_id')
            ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendor_performance_evaluation_setups.vendor_work_category_id')
            ->leftJoin('weighted_nodes as old_node', 'old_node.id', '=', 'vendor_performance_evaluation_form_change_logs.old_template_node_id')
            ->leftJoin('weighted_nodes as new_node', 'new_node.id', '=', 'vendor_performance_evaluation_form_change_logs.new_template_node_id')
            ->where('vendor_performance_evaluations.vendor_performance_evaluation_cycle_id', '=', $cycleId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'updated_by':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'company':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'project':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'old_form':
                        if(strlen($val) > 0)
                        {
                            $model->where('old_node.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'new_form':
                        if(strlen($val) > 0)
                        {
                            $model->where('new_node.name', 'ILIKE', '%'.$val.'%');
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
                }
            }
        }

        $model->orderBy('vendor_performance_evaluation_form_change_logs.created_at', 'desc');

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

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $vendorCategoriesArray = [];

            foreach($vendorCategoriesByVendorWorkCategoryId[$record->vendor_work_category_id] as $categories)
            {
                $vendorCategoriesArray[] = $categories['name'];
            }

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'updated_by'           => $record->updated_by,
                'updated_at'           => Carbon::parse($record->created_at)->format(\Config::get('dates.submitted_at')),
                'company'              => $record->company,
                'project'              => $record->project,
                'reference'            => $record->reference,
                'vendor_work_category' => $record->vendor_work_category,
                'vendor_categories'    => $vendorCategoriesArray,
                'old_form'             => $record->old_form ?? trans('vendorManagement.noForm'),
                'new_form'             => $record->new_form ?? trans('vendorManagement.noForm'),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function removalRequests($cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = RemovalRequest::withTrashed()->select('vendor_performance_evaluation_removal_requests.id',
                'vendor_performance_evaluation_removal_requests.created_at as requested_at',
                'vendor_performance_evaluation_removal_requests.removed_at',
                'vendor_performance_evaluation_removal_requests.deleted_at',
                'vendor_performance_evaluation_removal_requests.evaluation_removed',
                'vendor_performance_evaluation_removal_requests.dismissal_remarks',
                'vendor_performance_evaluation_removal_requests.request_remarks',
                'vendor_performance_evaluation_removal_requests.vendor_performance_evaluation_project_removal_reason_text as reason_custom',
                'vendor_performance_evaluation_project_removal_reasons.name as reason_listed',
                'users.name as requested_by',
                'attendant.name as attended_by',
                'projects.title as project',
                'projects.reference as reference',
                'companies.name as company'
            )
            ->join('users', 'users.id', '=', 'vendor_performance_evaluation_removal_requests.user_id')
            ->leftJoin('users as attendant', 'attendant.id', '=', 'vendor_performance_evaluation_removal_requests.action_by')
            ->join('companies', 'companies.id', '=', 'users.company_id')
            ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_performance_evaluation_removal_requests.vendor_performance_evaluation_id')
            ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
            ->leftJoin('vendor_performance_evaluation_project_removal_reasons', 'vendor_performance_evaluation_project_removal_reasons.id', '=', 'vendor_performance_evaluation_removal_requests.vendor_performance_evaluation_project_removal_reason_id')
            ->where('vendor_performance_evaluations.vendor_performance_evaluation_cycle_id', '=', $cycleId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'requested_by':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'attended_by':
                        if(strlen($val) > 0)
                        {
                            $model->where('attendant.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'company':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'project':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'removed_at':
                        if($val == 1)
                        {
                            $model->whereNotNull('vendor_performance_evaluation_removal_requests.removed_at');
                        }
                        elseif($val == 2)
                        {
                            $model->whereNull('vendor_performance_evaluation_removal_requests.removed_at');
                        }
                        break;
                    case 'dismissed_at':
                        if($val == 1)
                        {
                            $model->whereNotNull('vendor_performance_evaluation_removal_requests.deleted_at');
                        }
                        elseif($val == 2)
                        {
                            $model->whereNull('vendor_performance_evaluation_removal_requests.deleted_at');
                        }
                        break;
                    case 'responded':
                        if($val == 1)
                        {
                            $model->where(function($query){
                                $query->whereNotNull('vendor_performance_evaluation_removal_requests.removed_at')
                                    ->orWhereNotNull('vendor_performance_evaluation_removal_requests.deleted_at');
                            });
                        }
                        elseif($val == 2)
                        {
                            $model->where(function($query){
                                $query->whereNull('vendor_performance_evaluation_removal_requests.removed_at')
                                    ->whereNull('vendor_performance_evaluation_removal_requests.deleted_at');
                            });
                        }
                        break;
                }
            }
        }

        $model->orderBy('vendor_performance_evaluation_removal_requests.created_at', 'desc');

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
                'requested_by'       => $record->requested_by,
                'attended_by'        => $record->attended_by,
                'requested_at'       => Carbon::parse($record->requested_at)->format(\Config::get('dates.submitted_at')),
                'removed_at'         => $record->evaluation_removed ? Carbon::parse($record->removed_at)->format(\Config::get('dates.submitted_at')) : '',
                'dismissed_at'       => $record->deleted_at ? Carbon::parse($record->deleted_at)->format(\Config::get('dates.submitted_at')) : '',
                'company'            => $record->company,
                'project'            => $record->project,
                'reference'          => $record->reference,
                'evaluation_removed' => $record->evaluation_removed,
                'request_remarks'    => $record->request_remarks,
                'dismissal_remarks'  => $record->dismissal_remarks,
                'reason'             => empty($record->reason_listed) ? $record->reason_custom : $record->reason_listed,
                'responded'          => $record->deleted_at || $record->removed_at,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function create()
    {
        return View::make('vendor_performance_evaluation.cycles.create');
    }

    public function store()
    {
        $input = Input::all();

        $this->vendorPerformanceEvaluationCycleForm->validate($input);

        $lastCycle = Cycle::select('vendor_performance_evaluation_cycles.id')->orderBy('vendor_performance_evaluation_cycles.id', 'desc')->first();

        if ($lastCycle) {
            $totalVpe = VendorPerformanceEvaluation::select('vendor_performance_evaluation_cycle_id')->where('vendor_performance_evaluation_cycle_id', '=', $lastCycle->id)->count();
        } else {
            $totalVpe = 0;
        }

        $cycle = Cycle::create($input);

        if ($totalVpe > 0) {
            //set_time_limit(0);  // Remove execution time limit : Temporary solution as script takes a long time to complete.
            set_time_limit(86400); // 86400 seconds = 24 hours

            $skip = 0;
            $limit = 50;
            $pages = ceil($totalVpe / $limit);

            for ($i=1; $i <= $pages; $i++) {
                $vpes = VendorPerformanceEvaluation::where('vendor_performance_evaluation_cycle_id', '=', $lastCycle->id)->orderBy('project_id', 'asc')->skip($skip)
                    ->take($limit)
                    ->get();

                foreach ($vpes as $vpe) {
                    // Clone VPE record and set new cycle ID and dates
                    $newVpe = new VendorPerformanceEvaluation(array(
                        'vendor_performance_evaluation_cycle_id' => $cycle->id,
                        'project_id' => $vpe->project_id,
                        'project_status_id' => $vpe->project_status_id,
                        'start_date' => $cycle->start_date,
                        'end_date' => $cycle->end_date,
                        'type' => $vpe->type,
                    ));

                    //$newVpe->skipSyncVendorWorkCategorySetups = true;
                    $newVpe->save();
                }

                $skip += $limit;
                unset($vpes);   // Clear the variable to free memory
            }
        }

        return Redirect::route('vendorPerformanceEvaluation.cycle.edit', array($cycle->id));
    }

    public function edit($cycleId)
    {
        $cycle = Cycle::find($cycleId);

        $cycle->start_date = Carbon::parse($cycle->start_date)->format(\Config::get('dates.submitted_at'));
        $cycle->end_date   = Carbon::parse($cycle->end_date)->format(\Config::get('dates.submitted_at'));

        // we assume here that all draft project evaluations in a cycle have the same evaluation type
        // temporary approach
        $evaluation = VendorPerformanceEvaluation::has('project')
            ->where('vendor_performance_evaluation_cycle_id', '=', $cycleId)
            ->where('status_id', '=', VendorPerformanceEvaluation::STATUS_DRAFT)
            ->first();

        $evaluationType = $evaluation->type ?? VendorPerformanceEvaluation::TYPE_180;

        return View::make('vendor_performance_evaluation.cycles.edit', compact('cycle', 'evaluationType'));
    }

    public function update($cycleId)
    {
        $input = Input::all();

        $this->vendorPerformanceEvaluationCycleForm->setUpdateMode();

        $this->vendorPerformanceEvaluationCycleForm->validate($input);

        $cycle = Cycle::find($cycleId);

        $transaction = new DBTransaction;

        $transaction->begin();

        try
        {
            $user = \Confide::user();

            $cycle->update($input);

            $cycle->evaluations()->where('status_id', '=', VendorPerformanceEvaluation::STATUS_DRAFT)->update(['type' => (isset($input['type_180']) ? VendorPerformanceEvaluation::TYPE_180 : VendorPerformanceEvaluation::TYPE_360), 'updated_by' => $user->id]);

            $cycle->evaluations()->where('status_id', '!=', VendorPerformanceEvaluation::STATUS_COMPLETED)->update(['start_date' => $input['start_date'], 'end_date' => $input['end_date'], 'updated_by' => $user->id]);

            \Queue::push('PCK\QueueJobs\StartAndEndVendorPerformanceEvaluations', [], 'default');

            \Flash::success(trans('vendorManagement.formSavedSuccessfully'));

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            \Flash::error(trans('vendorManagement.formValidationErrorChangesNotSaved'));
        }

        return Redirect::back();
    }

    public function assignedProjects($cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorPerformanceEvaluation::select(
                'projects.id',
                'projects.title',
                'projects.reference',
                'companies.name as business_unit',
                'vendor_performance_evaluations.start_date',
                'vendor_performance_evaluations.end_date',
                'vendor_performance_evaluations.project_status_id',
                'vendor_performance_evaluations.status_id',
                'vendor_performance_evaluations.type'
            )
            ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
            ->join('companies', 'companies.id', '=', 'projects.business_unit_id')
            ->where('vendor_performance_evaluation_cycle_id', '=', $cycleId);

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'business_unit':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'status':
                        if((int)$val > 0)
                        {
                            $model->where('vendor_performance_evaluations.status_id', '=', $val);
                        }
                        break;
                    case 'type':
                        if((int)$val > 0)
                        {
                            $model->where('vendor_performance_evaluations.type', '=', $val);
                        }
                        break;
                }
            }
        }

        $model->orderBy('vendor_performance_evaluations.start_date', 'asc')
            ->orderBy('projects.title', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'business_unit'        => $record->business_unit,
                'start_date'           => Carbon::parse($record->start_date)->format(\Config::get('dates.submitted_at')),
                'end_date'             => Carbon::parse($record->end_date)->format(\Config::get('dates.submitted_at')),
                'project_stage'        => VendorPerformanceEvaluation::getProjectStageName($record->project_status_id),
                'can_delete'           => $record->status_id != VendorPerformanceEvaluation::STATUS_COMPLETED,
                'status'               => VendorPerformanceEvaluation::getStatusText($record->status_id),
                'type'                 => $record->type == VendorPerformanceEvaluation::TYPE_180 ? trans('vendorPerformanceEvaluation.type180') : trans('vendorPerformanceEvaluation.type360'),
                'route:remove_project' => route('vendorPerformanceEvaluation.cycle.removeProject', [$cycleId, $record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function unassignedProjects($cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $includedProjectIds = VendorPerformanceEvaluation::has('project')
            ->where('vendor_performance_evaluation_cycle_id', '=', $cycleId)
            ->lists('project_id');

        $model = Project::select(
                'projects.id',
                'projects.title',
                'projects.reference',
                'companies.name as business_unit'
            )
            ->join('companies', 'companies.id', '=', 'projects.business_unit_id')
            ->whereNotIn('projects.id', $includedProjectIds);

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'business_unit':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
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

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                => $record->id,
                'counter'           => $counter,
                'title'             => $record->title,
                'reference'         => $record->reference,
                'business_unit'     => $record->business_unit,
                'project_stage'     => VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::determineProjectStage($record)),
                'route:add_project' => route('vendorPerformanceEvaluation.cycle.addProject', [$cycleId, $record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function addProject($cycleId, $project)
    {
        $success = false;

        $transaction = new DBTransaction;

        $transaction->begin();

        try
        {
            $data = [
                'cycle_id'   => $cycleId,
                'project_id' => $project->id,
            ];

            $this->vendorPerformanceEvaluationCycleAddProjectForm->validate($data);

            if($this->vendorPerformanceEvaluationCycleAddProjectForm->success)
            {
                $cycle = Cycle::find($cycleId);

                VendorPerformanceEvaluation::create([
                    'vendor_performance_evaluation_cycle_id' => $cycleId,
                    'project_id'                             => $project->id,
                    'project_status_id'                      => VendorPerformanceEvaluation::determineProjectStage($project),
                    'start_date'                             => $cycle->start_date,
                    'end_date'                               => $cycle->end_date,
                    'type'                                   => Input::get('evaluation_type'),
                ]);

                $transaction->commit();

                $success = true;
            }
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
        }

        return array(
            'success' => $success,
            'errors'  => $this->vendorPerformanceEvaluationCycleAddProjectForm->getErrorMessages(),
        );
    }

    public function removeProject($cycleId, $project)
    {
        $success  = false;
        $errorMsg = null;

        try
        {
            $evaluation = VendorPerformanceEvaluation::where('vendor_performance_evaluation_cycle_id', '=', $cycleId)
                ->where('project_id', '=', $project->id)
                ->where('status_id', '!=', VendorPerformanceEvaluation::STATUS_COMPLETED)
                ->first();

            if($evaluation) $evaluation->delete();

            $success = true;
        }
        catch(\Exception $e)
        {
            $errorMsg = $e->getMessage();

            \Log::error($e->getMessage());
        }

        return array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
        );
    }
}