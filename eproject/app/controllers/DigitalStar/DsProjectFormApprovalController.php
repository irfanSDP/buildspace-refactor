<?php

namespace DigitalStar;

use Confide;
use DB;
use Input;
use PCK\DigitalStar\Evaluation\DsCycle;
use PCK\DigitalStar\Evaluation\DsEvaluation;
use PCK\DigitalStar\Evaluation\DsEvaluationForm;
use PCK\DigitalStar\Evaluation\DsEvaluationFormRemark;
use PCK\DigitalStar\Evaluation\DsEvaluationFormUserRole;
use PCK\DigitalStar\Evaluation\DsEvaluationLog;
use PCK\DigitalStar\Evaluation\DsRole;
use PCK\DigitalStar\ModuleParameters\DsModuleParameter;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\WeightedNode\WeightedNode;
use PCK\Verifier\Verifier;
use PCK\Verifier\VerifierRepository;
use PCK\Notifications\EmailNotifier;
use Redirect;
use Request;
use Response;
use View;

class DsProjectFormApprovalController extends \BaseController
{

    protected $weightedNodeRepository;
    protected $verifierRepository;
    protected $emailNotifier;

    public function __construct(WeightedNodeRepository $weightedNodeRepository, VerifierRepository $verifierRepository, EmailNotifier $emailNotifier)
    {
        $this->weightedNodeRepository = $weightedNodeRepository;
        $this->verifierRepository = $verifierRepository;
        $this->emailNotifier = $emailNotifier;
    }

    public function index()
    {
        $statuses = DsEvaluationForm::getStatuses();

        unset($statuses[DsEvaluationForm::STATUS_COMPLETED]);

        $routePrefix = Request::segment(4);  // Can be either "assign-verifiers" or "approve"
        if ($routePrefix === 'assign-verifiers') {
            $listRoute = route('digital-star.approval.project.assign-verifiers.list');
        } elseif ($routePrefix === 'approve') {
            $listRoute = route('digital-star.approval.project.approve.list');
        } else {
            return Redirect::route('digital-star.approval.project.index');
        }

        return View::make('digital_star.approvals.project.index', compact('statuses', 'listRoute'));
    }

    public function list()
    {
        $routePrefix = Request::segment(4);  // Can be either "assign-verifiers" or "approve"
        switch ($routePrefix) {
            case 'assign-verifiers':
                $formStatusList = [DsEvaluationForm::STATUS_SUBMITTED];
                break;
            case 'approve':
                $formStatusList = [DsEvaluationForm::STATUS_PENDING_VERIFICATION];
                break;
            default:
                return Response::json([
                    'last_page' => 0,
                    'data' => [],
                ]);
        }

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $latestCycle = DsCycle::latestActive();

        if (is_null($latestCycle)) {
            return Response::json([
                'last_page' => 0,
                'data' => [],
            ]);
        }

        $model = DsEvaluationForm::select(
                'ds_evaluations.id AS evaluation_id',
                'ds_evaluation_forms.id AS form_id',
                'ds_evaluation_forms.status_id AS form_status_id',
                'companies.id AS company_id',
                'companies.name AS company_name',
                'projects.id AS project_id',
                'projects.title AS project',
                'projects.reference AS contract_no'
            )
            ->join('projects', 'projects.id', '=', 'ds_evaluation_forms.project_id')
            ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
            ->join('companies', 'companies.id', '=', 'ds_evaluations.company_id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->where('contract_group_categories.hidden', '=', false)
            ->where('ds_evaluations.ds_cycle_id', '=', $latestCycle->id)
            ->where('ds_evaluations.status_id', '=', DsEvaluation::STATUS_IN_PROGRESS)
            ->whereIn('ds_evaluation_forms.status_id', $formStatusList)
            ->whereNotNull('ds_evaluation_forms.project_id')
            ->whereNull('projects.deleted_at');

        $user = \Confide::user();
        $includeFormIds = [];

        if ($routePrefix === 'assign-verifiers') {
            $role = DsRole::where('slug', '=', 'project-evaluator')->first();
            $userRoleForms = DsEvaluationFormUserRole::where('user_id', '=', $user->id)->where('ds_role_id', '=', $role->id)->get();
            if (count($userRoleForms) > 0) {
                $userRoleFormIds = $userRoleForms->lists('ds_evaluation_form_id');
                $includeFormIds = array_merge($includeFormIds, $userRoleFormIds);
            }
        }
        if ($routePrefix === 'approve') {
            $evalForm = DsEvaluationForm::first();
            if ($evalForm) {
                $pendingVerifications = Verifier::where('object_type', '=', get_class($evalForm))
                    ->where('verifier_id' , '=', $user->id)
                    ->whereNull('approved')
                    ->get();
                if (count($pendingVerifications) > 0) {
                    $objectIds = $pendingVerifications->lists('object_id');
                    $includeFormIds = array_merge($includeFormIds, $objectIds);
                }
            }
        }

        $includeFormIds = array_unique($includeFormIds);
        $model->whereIn('ds_evaluation_forms.id', $includeFormIds);

        if ($request->has('filters')) {
            foreach ($request->get('filters') as $filters) {
                if (!is_array($filters['value'])) {
                    $val = trim($filters['value']);

                    switch (trim(strtolower($filters['field']))) {
                        case 'company':
                            if (strlen($val) > 0) {
                                $model->where('companies.name', 'ILIKE', '%' . $val . '%');
                            }
                            break;

                        case 'project':
                            if (strlen($val) > 0) {
                                $model->where('projects.title', 'ILIKE', '%' . $val . '%');
                            }
                            break;

                        case 'contract_no':
                            if (strlen($val) > 0) {
                                $model->where('projects.reference', 'ILIKE', '%' . $val . '%');
                            }
                            break;

                        case 'status':
                            if ($val > 0) {
                                $model->where('ds_evaluation_forms.status_id', '=', $val);
                            }
                            break;
                    }
                }
            }
        }

        $model->groupBy('ds_evaluations.id', 'ds_evaluation_forms.id', 'companies.id', 'projects.id');

        $rowCount = $model->count();

        $records = $model->orderBy('ds_evaluation_forms.id', 'DESC')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        if (! $records->isEmpty()) {
            $activeCycleVpeIds = array_unique($records->lists('evaluation_id'));
            $projectIds = array_unique($records->lists('project_id'));

            // Score from current cycle
            $activeVpeScoreResults = DsEvaluationForm::select(
                    'ds_evaluations.id AS evaluation_id',
                    'ds_evaluation_forms.id AS form_id',
                    'ds_evaluations.company_id',
                    'ds_evaluation_forms.project_id',
                    'ds_evaluation_forms.score'
                )
                ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
                ->where('ds_evaluations.ds_cycle_id', '=', $latestCycle->id)
                ->whereIn('ds_evaluation_forms.project_id', $projectIds)
                ->whereIn('ds_evaluations.id', $activeCycleVpeIds)
                ->whereNotNull('ds_evaluation_forms.project_id')
                ->orderBy('ds_evaluations.id')
                ->orderBy('ds_evaluations.company_id')
                ->orderBy('ds_evaluation_forms.project_id')
                ->get();

            $vpeModuleParameter = DsModuleParameter::first();
            $vpeGlobalGrade = is_null($vpeModuleParameter->vendorManagementGrade) ? null : $vpeModuleParameter->vendorManagementGrade;

            $activeVpeScores = [];

            foreach ($activeVpeScoreResults as $score) {
                $activeVpeScores[$score->company_id][$score->project_id]['score'] = $score->score;
                $activeVpeScores[$score->company_id][$score->project_id]['grade'] = (!is_null($score->score) && !is_null($vpeGlobalGrade)) ? $vpeGlobalGrade->getGrade($score->score)->description : null;
            }

            // Score from previous cycles
            $projectIdList = implode(', ', $projectIds);
            $latestCycleId = $latestCycle->id;
            $completedStatus = DsEvaluation::STATUS_COMPLETED;
            $formCompletedStatus = DsEvaluationForm::STATUS_COMPLETED;

            $historicalVpeScoresQuery = "WITH historical_vpes AS (
                    SELECT 
                        ds_evaluations.id as evaluation_id, 
                        ds_evaluations.ds_cycle_id as cycle_id,
                        ds_evaluation_forms.id as form_id, 
                        ds_evaluations.company_id,
                        ds_evaluation_forms.project_id,
                        ds_evaluation_forms.score,
                        RANK() OVER (PARTITION BY ds_evaluations.company_id ORDER BY ds_evaluations.id DESC) AS rank
                    FROM ds_evaluations
                    JOIN ds_evaluation_forms ON ds_evaluation_forms.ds_evaluation_id = ds_evaluations.id
                    WHERE 
                        ds_evaluation_forms.project_id IN ($projectIdList)
                        AND ds_evaluations.status_id = $completedStatus
                        AND ds_evaluation_forms.status_id = $formCompletedStatus
                        AND ds_evaluation_forms.project_id IS NOT NULL
                        AND ds_evaluations.ds_cycle_id < $latestCycleId
                )
                SELECT 
                    hvpe.cycle_id, 
                    hvpe.evaluation_id, 
                    hvpe.form_id, 
                    hvpe.company_id, 
                    hvpe.project_id, 
                    hvpe.score
                FROM historical_vpes hvpe
                WHERE 
                    hvpe.rank <= 2
                ORDER BY 
                    hvpe.cycle_id DESC, 
                    hvpe.evaluation_id ASC, 
                    hvpe.company_id ASC, 
                    hvpe.project_id ASC";

            $historicalVpeScoreResults = DB::select(DB::raw($historicalVpeScoresQuery));

            $historicalVpeScores = [];

            foreach ($historicalVpeScoreResults as $score) {
                if (is_null($score->score)) continue;

                $historicalVpeScores[$score->company_id][$score->project_id][] = [
                    'cycle' => $score->cycle_id,
                    'score' => $score->score,
                    'grade' => (!is_null($score->score) && !is_null($vpeGlobalGrade)) ? $vpeGlobalGrade->getGrade($score->score)->description : null,
                ];
            }

            // Score from current and previous cycles
            $allVpeScores = [];

            foreach ($activeVpeScores as $companyId => $scoreRecords) {
                foreach ($scoreRecords as $projectId => $score) {
                    $allVpeScores[$companyId][$projectId]['score_0'] = $score['score'];
                    $allVpeScores[$companyId][$projectId]['grade_0'] = $score['grade'];

                    if (isset($historicalVpeScores[$companyId][$projectId])) {
                        foreach ($historicalVpeScores[$companyId][$projectId] as $key => $historicalScore) {
                            $allVpeScores[$companyId][$projectId]['score_' . ($key + 1)] = $historicalVpeScores[$companyId][$projectId][$key]['score'];
                            $allVpeScores[$companyId][$projectId]['grade_' . ($key + 1)] = $historicalVpeScores[$companyId][$projectId][$key]['grade'];
                        }
                    }
                }
            }

            // Merge current and previous score records
            $data = [];

            foreach ($records as $key => $record) {
                $counter = ($page - 1) * $limit + $key + 1;

                $row = [
                    'counter' => $counter,
                    'vpe_id' => $record->evaluation_id,
                    'id' => $record->form_id,
                    'company_id' => $record->company_id,
                    'company' => $record->company_name,
                    'project' => $record->project,
                    'contract_no' => $record->contract_no,
                    'status' => DsEvaluationForm::getStatusText($record->form_status_id),
                ];

                if ($routePrefix === 'assign-verifiers') {
                    $row['route:edit'] = route('digital-star.approval.project.assign-verifiers.edit', [$record->form_id]);
                } elseif ($routePrefix === 'approve') {
                    $row['route:edit'] = route('digital-star.approval.project.approve.edit', [$record->form_id]);
                }

                $row['score_0'] = null;
                $row['grade_0'] = null;
                $row['score_1'] = null;
                $row['grade_1'] = null;
                $row['score_2'] = null;
                $row['grade_2'] = null;

                for ($i = 0; $i < 3; $i++) {
                    if (! isset($allVpeScores[$record->company_id][$record->project_id]['score_' . $i])) continue;

                    $row['score_' . $i] = $allVpeScores[$record->company_id][$record->project_id]['score_' . $i];
                    $row['grade_' . $i] = $allVpeScores[$record->company_id][$record->project_id]['grade_' . $i];
                }

                $data[] = $row;
            }
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data,
        ]);
    }

    public function edit($formId)
    {
        $user = \Confide::user();

        $evaluationForm = DsEvaluationForm::find($formId);

        $weightedNode = WeightedNode::find($evaluationForm->weighted_node_id);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($weightedNode)];

        \PCK\Helpers\Hierarchy\AdjacencyListNode::traverse($data[0], function($node) use ($evaluationForm) {
            if(isset($node['hasScores']) && $node['hasScores'])
            {
                $node['route:getDownloads'] = route('digital-star.evaluation.project.node.downloads', array($evaluationForm->ds_evaluation_id, $evaluationForm->id, $node['nodeId']));
            }
            return $node;
        }, '_children');

        //$files = $evaluationForm->attachments;

        $excludedIds = WeightedNode::where('root_id', '=', $weightedNode->id)->where('is_excluded', '=', true)->lists('id', 'id');

        $evaluatorRole = DsRole::where('slug', '=', 'project-evaluator')->first();
        if (! $evaluatorRole) {
            \Flash::error(trans('errors.anErrorHasOccurred'));
            return Redirect::back();
        }
        $isProjectEvaluator = DsEvaluationFormUserRole::where('ds_evaluation_form_id', '=', $formId)
            ->where('ds_role_id', '=', $evaluatorRole->id)
            ->where('user_id', '=', $user->id)
            ->exists();

        $canAssignVerifiers = ($isProjectEvaluator && $evaluationForm->status_id == DsEvaluationForm::STATUS_SUBMITTED);

        $listOfVerifiers = $evaluationForm->getListOfVerifiers($formId);

        $canApproveOrReject = ($evaluationForm->isPendingVerification() && Verifier::isCurrentVerifier($user, $evaluationForm));

        $verifierLogs = Verifier::getAssignedVerifierRecords($evaluationForm);

        $vpeScore = $evaluationForm->score;

        $remarks = [];

        $evaluationRemarks = DsEvaluationFormRemark::where('ds_evaluation_form_id', '=', $evaluationForm->id)->where('ds_role_id', '=', $evaluatorRole->id)->where('action', '=', DsEvaluationFormRemark::ACTION_SUBMIT)->first();
        $remarks['evaluator'] = $evaluationRemarks ? $evaluationRemarks->remarks : null;

        $processorRemarks = DsEvaluationFormRemark::where('ds_evaluation_form_id', '=', $evaluationForm->id)->where('ds_role_id', '=', $evaluatorRole->id)->where('action', '=', DsEvaluationFormRemark::ACTION_REJECT)->first();
        $remarks['processor'] = $processorRemarks ? $processorRemarks->remarks : null;

        return View::make('digital_star.approvals.project.edit', compact('evaluationForm', 'data', 'excludedIds', 'canAssignVerifiers', 'listOfVerifiers', 'canApproveOrReject', 'verifierLogs', 'vpeScore', 'remarks'));
    }

    public function update($formId)
    {
        $evaluationForm = DsEvaluationForm::find($formId);
        $user = \Confide::user();
        $role = DsRole::where('slug', '=', 'project-evaluator')->first();
        if (! $role) {
            \Flash::error(trans('errors.anErrorHasOccured'));
            return Redirect::back();
        }

        if (Input::get('submit') == 'reject') {
            $formRemarks = DsEvaluationFormRemark::firstOrCreate([
                'ds_evaluation_form_id' => $formId,
                'ds_role_id' => $role->id,
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'action' => DsEvaluationFormRemark::ACTION_REJECT
            ]);
            $remarks = trim(Input::get('remarks'));

            $formRemarks->remarks = ! empty($remarks) ? $remarks : null;
            $formRemarks->save();

            $evaluationForm->status_id = DsEvaluationForm::STATUS_DRAFT;
            $evaluationForm->save();

            DsEvaluationLog::logAction($evaluationForm->id, 'rejected', $role->id, $user);

            //$this->emailNotifier->sendSubmitterRejectedVpeFormNotification($evaluationForm);
        } elseif (Input::get('submit') == 'approve') {
            Verifier::setVerifiers(Input::get('verifiers'), $evaluationForm);
            $this->verifierRepository->executeFollowUp($evaluationForm);

            $evaluationForm->status_id = DsEvaluationForm::STATUS_PENDING_VERIFICATION;
            $evaluationForm->submitted_for_approval_by = Confide::user()->id;
            $evaluationForm->save();

            DsEvaluationLog::logAction($evaluationForm->id, 'submitted-for-approval', $role->id, $user);

            if (Verifier::isCurrentVerifier($user, $evaluationForm)) {
                return Redirect::route('digital-star.approval.project.approve.edit', array($evaluationForm->id));
            }
        }

        return Redirect::route('digital-star.approval.project.assign-verifiers.edit', array($evaluationForm->id));
    }

}