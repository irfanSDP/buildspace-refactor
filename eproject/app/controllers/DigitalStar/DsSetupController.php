<?php

namespace DigitalStar;

use Carbon\Carbon;
use Input;
use PCK\DigitalStar\Evaluation\DsCycle;
use PCK\DigitalStar\Evaluation\DsEvaluation;
use PCK\DigitalStar\Evaluation\DsEvaluationForm;
use PCK\DigitalStar\Forms\DsSetupForm;
use PCK\Notifications\EmailNotifier;
use PCK\Reports\VendorPerformanceEvaluationSetupsGenerator;
use Redirect;
use Request;
use Response;
use View;

class DsSetupController extends \BaseController
{
    protected $dsSetupForm;
    protected $emailNotifier;

    public function __construct(DsSetupForm $dsSetupForm, EmailNotifier $emailNotifier)
    {
        $this->dsSetupForm = $dsSetupForm;
        $this->emailNotifier = $emailNotifier;
    }

    public function index()
    {
        $cycle = DsCycle::where('is_completed', '=', false)->first();

        if (Input::has('cycle')) {
            $cycle = DsCycle::find(Input::get('cycle'));
        }

        $statusFilterOptions = [
            0 => trans('general.all'),
            DsEvaluation::STATUS_DRAFT => DsEvaluation::getStatusText(DsEvaluation::STATUS_DRAFT),
            DsEvaluation::STATUS_IN_PROGRESS => DsEvaluation::getStatusText(DsEvaluation::STATUS_IN_PROGRESS),
            DsEvaluation::STATUS_COMPLETED => DsEvaluation::getStatusText(DsEvaluation::STATUS_COMPLETED),
        ];

        return View::make('digital_star.setups.evaluations.index', compact('cycle', 'statusFilterOptions'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = DsEvaluation::select(
            'ds_evaluations.id as ds_evaluation_id',
            'companies.id as company_id',
            'companies.name as company_name',
            'contract_group_categories.name as vendor_group',
            'ds_evaluations.start_date',
            'ds_evaluations.end_date',
            'ds_evaluations.status_id'
        )
        ->join('companies', 'companies.id', '=', 'ds_evaluations.company_id')
        ->join('contract_group_categories', 'companies.contract_group_category_id', '=', 'contract_group_categories.id');

        if (Input::has('cycle')) {
            $cycle = DsCycle::find(Input::get('cycle'));

            if ($cycle) {
                $model->where('ds_evaluations.ds_cycle_id', '=', $cycle->id);
            }
        } else {
            $model->whereHas('cycle', function($q){
                $q->where('is_completed', '=', false);
            });
        }

        if (Input::has('evaluations')) {
            $model->whereIn('ds_evaluations.id', Input::get('evaluations'));
        }

        if ($request->has('filters')) {
            foreach ($request->get('filters') as $filters) {
                if (!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch (trim(strtolower($filters['field']))) {
                    case 'company_name':
                        if (strlen($val) > 0) {
                            $model->where('companies.name', 'ILIKE', '%' . $val . '%');
                        }
                        break;

                    case 'vendor_group':
                        if (strlen($val) > 0) {
                            $model->where('contract_group_categories.name', 'ILIKE', '%' . $val . '%');
                        }
                        break;

                    case 'status':
                        if ((int)$val > 0) {
                            $model->where('ds_evaluations.status_id', '=', $val);
                        }
                        break;
                }
            }
        }

        $rowCount = $model->count();

        $records = $model->orderBy('ds_evaluations.start_date', 'asc')
            ->orderBy('companies.name', 'asc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach ($records->all() as $key => $record) {
            $counter = ($page - 1) * $limit + $key + 1;

            $data[] = [
                'id' => $record->company_id,
                'counter' => $counter,
                'company_name' => $record->company_name,
                'vendor_group' => $record->vendor_group,
                'start_date' => Carbon::parse($record->start_date)->format(\Config::get('dates.submitted_at')),
                'end_date' => Carbon::parse($record->end_date)->format(\Config::get('dates.submitted_at')),
                'status' => DsEvaluation::getStatusText($record->status_id),
                'route:evaluators' => route('digital-star.setups.evaluators.index', array($record->ds_evaluation_id)),
                'route:processors' => route('digital-star.setups.processors.company.index', array($record->ds_evaluation_id)),
                'route:send_form_assigned_email' => route('digital-star.setups.notification.form-assigned.email.send', [$record->ds_evaluation_id]),
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }

    public function edit($evaluationId)
    {
        // ...
    }

    public function update($evaluationId)
    {
        // ...
    }

    public function sendFormAssignedEmailNotification($evaluationId)
    {
        $errors = null;
        $success = false;

        try {
            $evaluation = DsEvaluation::find($evaluationId);

            if ($evaluation) {
                $latestCycle = DsCycle::latestActive();

                if ($evaluation->ds_cycle_id === $latestCycle->id) {    // Is latest cycle
                    $evaluationForms = DsEvaluationForm::where('ds_evaluation_id', $evaluation->id)->where('status_id', DsEvaluationForm::STATUS_DRAFT)->get();

                    $notificationSent = 0;
                    foreach ($evaluationForms as $evaluationForm) {
                        $this->emailNotifier->sendDsNotificationFormAssignedToEvaluator($evaluationForm);   // Email notification
                        $notificationSent++;
                    }

                    if ($notificationSent > 0) {
                        $success = true;
                    }
                }
            }
        } catch (\Exception $e) {
            $errors = $e->getMessage();
        }

        return Response::json([
            'errors' => $errors,
            'success' => $success,
        ]);
    }
}