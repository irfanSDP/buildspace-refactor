<?php

use Carbon\Carbon;
use PCK\Projects\Project;
use PCK\Subsidiaries\Subsidiary;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\CompanyProject\CompanyProject;
use PCK\Forms\VendorPerformanceEvaluationForm;
use PCK\Notifications\EmailNotifier;
use PCK\Reports\VendorPerformanceEvaluationSetupsGenerator;

class VendorPerformanceEvaluationSetupsController extends \BaseController {

    protected $vendorPerformanceEvaluationForm;
    protected $emailNotifier;

    public function __construct(VendorPerformanceEvaluationForm $vendorPerformanceEvaluationForm, EmailNotifier $emailNotifier)
    {
        $this->vendorPerformanceEvaluationForm = $vendorPerformanceEvaluationForm;
        $this->emailNotifier                   = $emailNotifier;
    }

    public function index()
    {
        $request = Request::instance();

        $cycle = Cycle::where('is_completed', '=', false)->first();

        if(Input::has('cycle'))
        {
            $cycle = Cycle::find(Input::get('cycle'));
        }

        $statusFilterOptions = [
            0                                               => trans('general.all'),
            VendorPerformanceEvaluation::STATUS_DRAFT       => VendorPerformanceEvaluation::getStatusText(VendorPerformanceEvaluation::STATUS_DRAFT),
            VendorPerformanceEvaluation::STATUS_IN_PROGRESS => VendorPerformanceEvaluation::getStatusText(VendorPerformanceEvaluation::STATUS_IN_PROGRESS),
            VendorPerformanceEvaluation::STATUS_COMPLETED   => VendorPerformanceEvaluation::getStatusText(VendorPerformanceEvaluation::STATUS_COMPLETED),
        ];

        $projectStageFilterOptions = [
            0                                                                   => trans('general.all'),
            VendorPerformanceEvaluation::PROJECT_STAGE_DESIGN                   => VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::PROJECT_STAGE_DESIGN),
            VendorPerformanceEvaluation::PROJECT_STAGE_CONSTRUCTION             => VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::PROJECT_STAGE_CONSTRUCTION),
            VendorPerformanceEvaluation::PROJECT_STAGE_DEFECTS_LIABILITY_PERIOD => VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::PROJECT_STAGE_DEFECTS_LIABILITY_PERIOD),
            VendorPerformanceEvaluation::PROJECT_STAGE_COMPLETED                => VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::PROJECT_STAGE_COMPLETED),
        ];

        $assignedVendorsFilterOptions = [
            0 => trans('general.all'),
            1 => trans('vendorManagement.notAllAssigned'),
            2 => trans('vendorManagement.allAssigned'),
        ];

        return View::make('vendor_performance_evaluation.setups.evaluations.index', compact('cycle', 'statusFilterOptions', 'projectStageFilterOptions', 'assignedVendorsFilterOptions'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorPerformanceEvaluation::select(
                'p.title',
                'p.reference',
                'p.subsidiary_id',
                'vendor_performance_evaluations.project_status_id',
                'vendor_performance_evaluations.start_date',
                'vendor_performance_evaluations.end_date',
                'vendor_performance_evaluations.status_id',
                'vendor_performance_evaluations.id',
                \DB::raw('coalesce(total_company_number, 0) as total_company_number'),
                \DB::raw('coalesce(assigned_company_number, 0) as assigned_company_number')
            )
            ->join('projects as p', 'p.id', '=', 'vendor_performance_evaluations.project_id')
            ->leftJoin(\DB::raw(
                "(select vendor_performance_evaluation_id, coalesce(count(distinct(company_id)), 0) as total_company_number
                from vendor_performance_evaluation_setups s
                group by vendor_performance_evaluation_id) total_companies"
            ), 'total_companies.vendor_performance_evaluation_id', '=', 'vendor_performance_evaluations.id')
            ->leftJoin(\DB::raw(
                "(select vendor_performance_evaluation_id, coalesce(count(distinct(company_id)), 0) as assigned_company_number
                from vendor_performance_evaluation_setups s
                where exists(
                    select 1
                    from vendor_performance_evaluation_setups s1
                    where s1.template_node_id is not null
                    and s1.company_id = s.company_id
                    and s1.vendor_performance_evaluation_id  = s.vendor_performance_evaluation_id
                )
                group by vendor_performance_evaluation_id) assigned_companies"
            ), 'assigned_companies.vendor_performance_evaluation_id', '=', 'vendor_performance_evaluations.id')
            ->whereNull('p.deleted_at');

        if(Input::has('cycle'))
        {
            $cycle = Cycle::find(Input::get('cycle'));

            if($cycle) $model->where('vendor_performance_evaluations.vendor_performance_evaluation_cycle_id', '=', $cycle->id);
        }
        else
        {
            $model->whereHas('cycle', function($q){
                $q->where('is_completed', '=', false);
            });
        }

        if(Input::has('evaluations'))
        {
            $model->whereIn('vendor_performance_evaluations.id', Input::get('evaluations'));
        }

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
                            $model->where('p.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('p.reference', 'ILIKE', '%'.$val.'%');
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

                            $model->whereIn('p.subsidiary_id', $selfAndDescendantSubsidiaryIds);
                        }
                        break;
                    case 'project_stage':
                        if((int)$val > 0)
                        {
                            $model->where('vendor_performance_evaluations.project_status_id', '=', $val);
                        }
                        break;
                    case 'status':
                        if((int)$val > 0)
                        {
                            $model->where('vendor_performance_evaluations.status_id', '=', $val);
                        }
                        break;
                    case 'assigned_vendors':
                        if((int)$val > 0)
                        {
                            switch((int)$val)
                            {
                                case 1:
                                    $model->whereRaw('coalesce(assigned_company_number, 0) != coalesce(total_company_number, 0)');
                                    break;
                                case 2:
                                    $model->whereRaw('coalesce(assigned_company_number, 0) = coalesce(total_company_number, 0)');
                                    break;
                            }
                        }
                        break;
                }
            }
        }

        $model->orderBy('start_date', 'asc');
        $model->orderBy('title', 'asc');

        $rowCount = $model->get()->count();

        if($limit > 0) $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $rootSubsidiaries = Subsidiary::getTopParentsGroupedBySubsidiaryIds($model->lists('subsidiary_id'));

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                                   => $record->id,
                'counter'                              => $counter,
                'title'                                => $record->title,
                'reference'                            => $record->reference,
                'business_unit'                        => $rootSubsidiaries[$record->subsidiary_id]['name'] ?? '',
                'project_stage'                        => VendorPerformanceEvaluation::getProjectStageName($record->project_status_id),
                'start_date'                           => Carbon::parse($record->start_date)->format(\Config::get('dates.submitted_at')),
                'end_date'                             => Carbon::parse($record->end_date)->format(\Config::get('dates.submitted_at')),
                'status'                               => VendorPerformanceEvaluation::getStatusText($record->status_id),
                'total_company_number'                 => $record->total_company_number,
                'assigned_company_number'              => $record->assigned_company_number,
                'route:vendors'                        => route('vendorPerformanceEvaluation.setups.evaluations.vendors.index', array($record->id)),
                'route:edit'                           => route('vendorPerformanceEvaluation.setups.edit', array($record->id)),
                'route:resend_vpe_form_assigned_email' => route('vendorPerformanceEvaluation.setups.vpe.form.assigned.email.send', [$record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function edit($evaluationId)
    {
        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $evaluation->start_date = Carbon::parse($evaluation->start_date)->format(\Config::get('dates.submitted_at'));
        $evaluation->end_date   = Carbon::parse($evaluation->end_date)->format(\Config::get('dates.submitted_at'));

        return View::make('vendor_performance_evaluation.evaluations.edit', compact('evaluation'));
    }

    public function update($evaluationId)
    {
        $input = Input::all();

        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $this->vendorPerformanceEvaluationForm->setEvaluation($evaluation);
        $this->vendorPerformanceEvaluationForm->validate($input);

        $evaluation->update($input);

        if(Carbon::parse($evaluation->start_date)->isPast())
        {
            \Flash::success(trans('vendorManagement.evaluationProcessStarted'));
        }
        elseif(Carbon::parse($evaluation->end_date)->isPast())
        {
            \Flash::success(trans('vendorManagement.evaluationProcessEnded'));
        }

        return Redirect::route('vendorPerformanceEvaluation.setups.index');
    }

    public function resendVpeFormAssignedEmailNotification($evaluationId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $evaluation = VendorPerformanceEvaluation::find($evaluationId);

            $this->emailNotifier->sendVendorAssignedVpeFormReminderNotifications($evaluation);

            $success = true;
        }
        catch(Exception $e)
        {
            $errors = $e->getMessage();
        }

        return Response::json([
            'errors'  => $errors,
            'success' => $success,
        ]);
    }

    public function listExport()
    {
        $reportGenerator = new VendorPerformanceEvaluationSetupsGenerator();

        $request = Request::instance();
        $request->merge(['size' => -1]);

        $cycle = Cycle::where('is_completed', '=', false)->first();

        if(Input::has('cycle'))
        {
            $cycle = Cycle::find(Input::get('cycle'));
        }

        $reportGenerator->setData($this->list()->getData()->data);
        $reportGenerator->setCycle($cycle);

        return $reportGenerator->generate();
    }
}