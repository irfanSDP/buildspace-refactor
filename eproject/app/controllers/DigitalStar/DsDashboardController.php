<?php

namespace DigitalStar;

use PCK\DigitalStar\Evaluation\DsCompanyScore;
use PCK\DigitalStar\Evaluation\DsCycle;
use PCK\DigitalStar\Evaluation\DsEvaluation;
use PCK\DigitalStar\Evaluation\DsEvaluationForm;
use PCK\DigitalStar\ModuleParameters\DsModuleParameter;
use PCK\Helpers\NumberHelper;

use App;
use Controller;
use Input;
use Request;
use Response;
use View;

class DsDashboardController extends Controller
{
    public function index()
    {
        return View::make('digital_star.dashboard.index');
    }

    public function getCharts()
    {
        if (!Request::ajax()) {
            App::abort(404);
        }

        $inputs = Input::all();

        $data = [];

        switch ($inputs['identifier']) {
            case 'vendorsByDsRating':   // Digital Star Rating
                $moduleParameter = DsModuleParameter::first();
                if (! $moduleParameter) {
                    $data = [
                        'labels' => [],
                        'series' => [],
                    ];
                    break;
                }
                $vendorManagementGrade = $moduleParameter->vendorManagementGrade;
                if (! $vendorManagementGrade) {
                    $data = [
                        'labels' => [],
                        'series' => [],
                    ];
                    break;
                }

                $levels = $vendorManagementGrade->levels()->orderBy('score_upper_limit', 'desc')->get();
                if ($levels->isEmpty()) {
                    $data = [
                        'labels' => [],
                        'series' => [],
                    ];
                    break;
                }

                $labels = [];
                $series = [];

                $gradeRanges = $vendorManagementGrade->getLevelRanges();

                foreach ($levels as $level)
                {
                    $range = $gradeRanges[$level->id];

                    $count = DsCompanyScore::where('score', '>=', $range['min'])
                        ->where('score', '<=', $range['max'])
                        ->count();

                    if ($count > 0)
                    {
                        $labels[] = $level->description;
                        $series[] = $count;
                    }
                }
                $data['labels'] = $labels;
                $data['series'] = $series;
                break;
        }

        return Response::json($data);
    }

    public function getStats($type)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $cycle = DsCycle::latest();
        if (! $cycle) {
            return Response::json([
                'last_page' => 0,
                'data' => [],
            ]);
        }

        $evaluations = DsEvaluation::where('ds_cycle_id', '=', $cycle->id)
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        if ($evaluations->isEmpty()) {
            return Response::json([
                'last_page' => 0,
                'data' => [],
            ]);
        }

        $rowCount = $evaluations->count();

        $data = [];

        foreach ($evaluations as $key => $evaluation) {
            if (! $evaluation->company) {
                continue;
            }

            $completedQuery = DsEvaluationForm::where('ds_evaluation_id', '=', $evaluation->id)
                ->where('status_id', '=', DsEvaluationForm::STATUS_COMPLETED);

            if ($type === 'project') {  // Project
                // Completed forms
                $completedQuery->whereNotNull('project_id');

                // List of forms with assigned project evaluators
                $projectEvaluatorSlug = 'project-evaluator';
                $projectForms = DsEvaluationForm::join('ds_evaluation_form_user_roles', 'ds_evaluation_form_user_roles.ds_evaluation_form_id', '=', 'ds_evaluation_forms.id')
                    ->join('ds_roles', 'ds_roles.id', '=', 'ds_evaluation_form_user_roles.ds_role_id')
                    ->where('ds_roles.slug', '=', $projectEvaluatorSlug)
                    ->where('ds_evaluation_forms.ds_evaluation_id', '=', $evaluation->id)
                    ->select('ds_evaluation_forms.id as form_id')
                    ->get();
                if ($projectForms->isEmpty()) {
                    $projectFormsIds = [];
                } else {
                    $projectFormsIds = $projectForms->lists('form_id');
                }

                // Not completed forms
                $notCompletedQuery = DsEvaluationForm::where('ds_evaluation_id', '=', $evaluation->id)
                    ->whereNotIn('status_id', [DsEvaluationForm::STATUS_COMPLETED])
                    ->whereIn('id', $projectFormsIds)
                    ->whereNotNull('project_id');
            } else {    // Company
                // Completed forms
                $completedQuery->whereNull('project_id');

                // Not completed forms
                $notCompletedQuery = DsEvaluationForm::where('ds_evaluation_id', '=', $evaluation->id)
                    ->whereNotIn('status_id', [DsEvaluationForm::STATUS_DRAFT, DsEvaluationForm::STATUS_COMPLETED])
                    ->whereNull('project_id');
            }

            $completed = $completedQuery->count();          // Completed forms
            $notCompleted = $notCompletedQuery->count();    // Not completed forms
            $totalForms = $completed + $notCompleted;       // Total forms

            $completionRate = $totalForms > 0 ? ($completed / $totalForms) * 100 : 0;

            $row = [
                'id' => $evaluation->id,
                'company' => $evaluation->company->name,
                'completed' => $completed,
                'completion_rate' => NumberHelper::formatNumber($completionRate),
                'pending' => $notCompleted,
            ];

            $data[] = $row;
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data,
        ]);
    }
}