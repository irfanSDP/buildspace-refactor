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

class DsCompanyFormApprovalController extends \BaseController
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

        /*$externalVendorGroups = ContractGroupCategory::where('hidden', false)->where('type', ContractGroupCategory::TYPE_EXTERNAL)->get();

        $externalVendorGroupsFilterOptions = [];

        $externalVendorGroupsFilterOptions[0] = trans('general.all');

        foreach ($externalVendorGroups as $vendorGroup) {
            $externalVendorGroupsFilterOptions[$vendorGroup->id] = $vendorGroup->name;
        }*/

        $routePrefix = Request::segment(4);  // Can be either "assign-verifiers" or "approve"
        if ($routePrefix === 'assign-verifiers') {
            $listRoute = route('digital-star.approval.company.assign-verifiers.list');
        } elseif ($routePrefix === 'approve') {
            $listRoute = route('digital-star.approval.company.approve.list');
        } else {
            return Redirect::route('digital-star.approval.company.index');
        }

        return View::make('digital_star.approvals.company.index', compact('statuses', 'listRoute'));
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
            'contract_group_categories.id as vendor_group_id',
            'contract_group_categories.name as vendor_group'
        )
            ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
            ->join('companies', 'companies.id', '=', 'ds_evaluations.company_id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->where('contract_group_categories.hidden', '=', false)
            ->where('ds_evaluations.ds_cycle_id', '=', $latestCycle->id)
            ->where('ds_evaluations.status_id', '=', DsEvaluation::STATUS_IN_PROGRESS)
            ->whereIn('ds_evaluation_forms.status_id', $formStatusList)
            ->whereNull('ds_evaluation_forms.project_id');

        $user = \Confide::user();
        $includeFormIds = [];

        if ($routePrefix === 'assign-verifiers') {
            $role = DsRole::where('slug', '=', 'company-processor')->first();
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

                        case 'vendor_group':
                            if (strlen($val) > 0) {
                                $model->where('contract_group_categories.name', 'ILIKE', '%' . $val . '%');
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

        $model->groupBy('ds_evaluations.id', 'ds_evaluation_forms.id', 'companies.id', 'contract_group_categories.id');

        $rowCount = $model->count();

        $records = $model->orderBy('ds_evaluation_forms.id', 'DESC')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        if (! $records->isEmpty()) {
            $activeCycleVpeIds = array_unique($records->lists('evaluation_id'));
            $companyIds = array_unique($records->lists('company_id'));

            // Score from current cycle
            $activeVpeScoreResults = DsEvaluationForm::select(
                    'ds_evaluations.id AS evaluation_id',
                    'ds_evaluation_forms.id AS form_id',
                    'companies.id AS company_id',
                    'contract_group_categories.id as vendor_group_id',
                    'ds_evaluation_forms.score'
                )
                ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
                ->join('companies', 'companies.id', '=', 'ds_evaluations.company_id')
                ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
                ->where('ds_evaluations.ds_cycle_id', '=', $latestCycle->id)
                ->where('contract_group_categories.hidden', '=', false)
                ->whereIn('companies.id', $companyIds)
                ->whereIn('ds_evaluations.id', $activeCycleVpeIds)
                ->whereNull('ds_evaluation_forms.project_id')
                ->orderBy('ds_evaluations.id')
                ->orderBy('companies.id')
                ->orderBy('contract_group_categories.id')
                ->get();

            $vpeModuleParameter = DsModuleParameter::first();
            $vpeGlobalGrade = is_null($vpeModuleParameter->vendorManagementGrade) ? null : $vpeModuleParameter->vendorManagementGrade;

            $activeVpeScores = [];

            foreach ($activeVpeScoreResults as $score) {
                $activeVpeScores[$score->company_id][$score->vendor_group_id]['score'] = $score->score;
                $activeVpeScores[$score->company_id][$score->vendor_group_id]['grade'] = (!is_null($score->score) && !is_null($vpeGlobalGrade)) ? $vpeGlobalGrade->getGrade($score->score)->description : null;
            }

            // Score from previous cycles
            $companyIdList = implode(', ', $companyIds);
            $latestCycleId = $latestCycle->id;
            $completedStatus = DsEvaluation::STATUS_COMPLETED;
            $formCompletedStatus = DsEvaluationForm::STATUS_COMPLETED;

            $historicalVpeScoresQuery = "WITH historical_vpes AS (
                    SELECT 
                        ds_evaluations.id as evaluation_id, 
                        ds_evaluations.ds_cycle_id as cycle_id,
                        ds_evaluations.company_id,
                        ds_evaluation_forms.id as form_id, 
                        ds_evaluation_forms.score,
                        contract_group_categories.id as vendor_group_id,
                        RANK() OVER (PARTITION BY ds_evaluations.company_id ORDER BY ds_evaluations.id DESC) AS rank
                    FROM ds_evaluations
                    JOIN ds_evaluation_forms ON ds_evaluation_forms.ds_evaluation_id = ds_evaluations.id
                    JOIN companies ON companies.id = ds_evaluations.company_id
                    JOIN contract_group_categories ON contract_group_categories.id = companies.contract_group_category_id
                    WHERE 
                        ds_evaluations.company_id IN ($companyIdList)
                        AND ds_evaluations.status_id = $completedStatus
                        AND ds_evaluation_forms.status_id = $formCompletedStatus
                        AND ds_evaluation_forms.project_id IS NULL
                        AND ds_evaluations.ds_cycle_id < $latestCycleId
                )
                SELECT 
                    hvpe.cycle_id, 
                    hvpe.evaluation_id, 
                    hvpe.form_id, 
                    hvpe.company_id, 
                    hvpe.vendor_group_id, 
                    hvpe.score
                FROM historical_vpes hvpe
                WHERE 
                    hvpe.rank <= 2
                ORDER BY 
                    hvpe.cycle_id DESC, 
                    hvpe.evaluation_id ASC, 
                    hvpe.company_id ASC, 
                    hvpe.vendor_group_id ASC";

            $historicalVpeScoreResults = DB::select(DB::raw($historicalVpeScoresQuery));

            $historicalVpeScores = [];

            foreach ($historicalVpeScoreResults as $score) {
                if (is_null($score->score)) continue;

                $historicalVpeScores[$score->company_id][$score->vendor_group_id][] = [
                    'cycle' => $score->cycle_id,
                    'score' => $score->score,
                    'grade' => (!is_null($score->score) && !is_null($vpeGlobalGrade)) ? $vpeGlobalGrade->getGrade($score->score)->description : null,
                ];
            }

            // Score from current and previous cycles
            $allVpeScores = [];

            foreach ($activeVpeScores as $companyId => $scoreRecords) {
                foreach ($scoreRecords as $vendorGroupId => $score) {
                    $allVpeScores[$companyId][$vendorGroupId]['score_0'] = $score['score'];
                    $allVpeScores[$companyId][$vendorGroupId]['grade_0'] = $score['grade'];

                    if (isset($historicalVpeScores[$companyId][$vendorGroupId])) {
                        foreach ($historicalVpeScores[$companyId][$vendorGroupId] as $key => $historicalScore) {
                            $allVpeScores[$companyId][$vendorGroupId]['score_' . ($key + 1)] = $historicalVpeScores[$companyId][$vendorGroupId][$key]['score'];
                            $allVpeScores[$companyId][$vendorGroupId]['grade_' . ($key + 1)] = $historicalVpeScores[$companyId][$vendorGroupId][$key]['grade'];
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
                    'vendor_group' => $record->vendor_group,
                    'status' => DsEvaluationForm::getStatusText($record->form_status_id),
                ];

                if ($routePrefix === 'assign-verifiers') {
                    $row['route:edit'] = route('digital-star.approval.company.assign-verifiers.edit', [$record->form_id]);
                } elseif ($routePrefix === 'approve') {
                    $row['route:edit'] = route('digital-star.approval.company.approve.edit', [$record->form_id]);
                }

                $row['score_0'] = null;
                $row['grade_0'] = null;
                $row['score_1'] = null;
                $row['grade_1'] = null;
                $row['score_2'] = null;
                $row['grade_2'] = null;

                for ($i = 0; $i < 3; $i++) {
                    if (! isset($allVpeScores[$record->company_id][$record->vendor_group_id]['score_' . $i])) continue;

                    $row['score_' . $i] = $allVpeScores[$record->company_id][$record->vendor_group_id]['score_' . $i];
                    $row['grade_' . $i] = $allVpeScores[$record->company_id][$record->vendor_group_id]['grade_' . $i];
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
                $node['route:getDownloads'] = route('digital-star.evaluation.company.node.downloads', array($evaluationForm->ds_evaluation_id, $evaluationForm->id, $node['nodeId']));
            }
            return $node;
        }, '_children');

        //$files = $evaluationForm->attachments;

        $excludedIds = WeightedNode::where('root_id', '=', $weightedNode->id)->where('is_excluded', '=', true)->lists('id', 'id');

        $evaluatorRole = DsRole::where('slug', '=', 'company-evaluator')->first();
        if (! $evaluatorRole) {
            \Flash::error(trans('errors.anErrorHasOccured'));
            return Redirect::back();
        }
        $processorRole = DsRole::where('slug', '=', 'company-processor')->first();
        if (! $processorRole) {
            \Flash::error(trans('errors.anErrorHasOccured'));
            return Redirect::back();
        }

        $isProcessor = DsEvaluationFormUserRole::where('ds_evaluation_form_id', '=', $formId)
            ->where('ds_role_id', '=', $processorRole->id)
            ->where('user_id', '=', $user->id)
            ->exists();

        $canAssignVerifiers = ($isProcessor && $evaluationForm->status_id == DsEvaluationForm::STATUS_SUBMITTED);

        $listOfVerifiers = $evaluationForm->getListOfVerifiers($formId);

        $canApproveOrReject = ($evaluationForm->isPendingVerification() && Verifier::isCurrentVerifier($user, $evaluationForm));

        $verifierLogs = Verifier::getAssignedVerifierRecords($evaluationForm);

        $vpeScore = $evaluationForm->score;

        $remarks = [];

        $evaluationRemarks = DsEvaluationFormRemark::where('ds_evaluation_form_id', '=', $evaluationForm->id)->where('ds_role_id', '=', $evaluatorRole->id)->first();
        $remarks['evaluator'] = $evaluationRemarks ? $evaluationRemarks->remarks : null;

        $processorRemarks = DsEvaluationFormRemark::where('ds_evaluation_form_id', '=', $evaluationForm->id)->where('ds_role_id', '=', $processorRole->id)->first();
        $remarks['processor'] = $processorRemarks ? $processorRemarks->remarks : null;

        return View::make('digital_star.approvals.company.edit', compact('evaluationForm', 'data', 'excludedIds', 'canAssignVerifiers', 'listOfVerifiers', 'canApproveOrReject', 'verifierLogs', 'vpeScore', 'remarks'));
    }

    public function update($formId)
    {
        $evaluationForm = DsEvaluationForm::find($formId);
        $user = \Confide::user();
        $role = DsRole::where('slug', '=', 'company-processor')->first();
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

            $this->emailNotifier->sendDsNotificationFormRejectedByProcessor($evaluationForm);   // Email notification
        } elseif (Input::get('submit') == 'approve') {
            Verifier::setVerifiers(Input::get('verifiers'), $evaluationForm);
            $this->verifierRepository->executeFollowUp($evaluationForm);    // Escalate to verifiers (Includes: Email notification)

            $evaluationForm->status_id = DsEvaluationForm::STATUS_PENDING_VERIFICATION;
            $evaluationForm->submitted_for_approval_by = Confide::user()->id;
            $evaluationForm->save();

            DsEvaluationLog::logAction($evaluationForm->id, 'submitted-for-approval', $role->id, $user);

            if (Verifier::isCurrentVerifier($user, $evaluationForm)) {
                return Redirect::route('digital-star.approval.company.approve.edit', array($evaluationForm->id));
            }
        }

        return Redirect::route('digital-star.approval.company.assign-verifiers.edit', array($evaluationForm->id));
    }

}