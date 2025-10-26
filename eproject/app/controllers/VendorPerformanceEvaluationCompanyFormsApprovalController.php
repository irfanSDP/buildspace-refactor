<?php

use PCK\Projects\Project;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluator;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationProcessorEditLog;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationProcessorEditDetail;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyFormEvaluationLog as CompanyFormEvaluationLog;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;
use Carbon\Carbon;
use PCK\Verifier\Verifier;
use PCK\Users\User;
use PCK\ContractGroups\Types\Role;
use PCK\Verifier\VerifierRepository;
use PCK\Helpers\StringOperations;
use PCK\Notifications\EmailNotifier;

class VendorPerformanceEvaluationCompanyFormsApprovalController extends \BaseController {

    protected $weightedNodeRepository;
    protected $verifierRepository;
    protected $emailNotifier;

    public function __construct(WeightedNodeRepository $weightedNodeRepository, VerifierRepository $verifierRepository, EmailNotifier $emailNotifier)
    {
        $this->weightedNodeRepository = $weightedNodeRepository;
        $this->verifierRepository     = $verifierRepository;
        $this->emailNotifier          = $emailNotifier;
    }

    public function index()
    {
        $statuses = VendorPerformanceEvaluationCompanyForm::getStatuses();

        unset($statuses[VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED]);

        $externalVendorGroups = ContractGroupCategory::where('hidden', false)->where('type', ContractGroupCategory::TYPE_EXTERNAL)->get();

        $externalVendorGroupsFilterOptions = [];

        $externalVendorGroupsFilterOptions[0] = trans('general.all');

        foreach($externalVendorGroups as $vendorGroup)
        {
            $externalVendorGroupsFilterOptions[$vendorGroup->id] = $vendorGroup->name;
        }

        return View::make('vendor_performance_evaluation.approvals.index', compact('statuses', 'externalVendorGroupsFilterOptions'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $latestCycle = Cycle::latestActive();

        if(is_null($latestCycle))
        {
            return Response::json([
                'last_page' => 0,
                'data'      => [],
            ]);
        }

        $user = \Confide::user();

        $userCompanyIds = $user->getAllCompanyIds();

        $offset = $limit * ($page - 1);

        // projects for latest cycle only
        $mainQuery = "SELECT vpe.id, vpecf.id AS form_id, p.id AS project_id, p.title AS project, c.id AS company_id, c.name AS company, vpecf.evaluator_company_id, vpecf.status_id, cgc.id AS vendor_group_id, cgc.name AS vendor_group, 
              ARRAY_TO_JSON(ARRAY(SELECT DISTINCT * FROM UNNEST(ARRAY_AGG(vc.id)) AS res ORDER BY res ASC)) AS vendor_category_id, 
              ARRAY_TO_JSON(ARRAY(SELECT DISTINCT * FROM UNNEST(ARRAY_AGG(vc.name)) AS res ORDER BY res ASC)) AS vendor_category, 
              vwc.id AS vendor_work_category_id, vwc.name AS vendor_work_category
              FROM vendor_performance_evaluation_company_forms vpecf 
              INNER JOIN vendor_performance_evaluations vpe ON vpe.id = vpecf.vendor_performance_evaluation_id 
              INNER JOIN vendor_performance_evaluation_cycles vpec ON vpec.id = vpe.vendor_performance_evaluation_cycle_id 
              INNER JOIN projects p ON p.id = vpe.project_id 
              INNER JOIN companies c ON c.id = vpecf.company_id
              INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
              INNER JOIN vendor_work_categories vwc ON vwc.id = vpecf.vendor_work_category_id
              INNER JOIN vendor_category_vendor_work_category vcvwc ON vcvwc.vendor_work_category_id = vwc.id 
              INNER JOIN vendor_categories vc ON vc.id = vcvwc.vendor_category_id
              INNER JOIN contract_group_project_users cgpu ON cgpu.project_id = p.id AND cgpu.user_id = {$user->id}
              WHERE vpecf.deleted_at IS NULL
              AND vpe.deleted_at IS NULL
              AND p.deleted_at IS NULL
              AND vpec.id = {$latestCycle->id}
              AND vpecf.evaluator_company_id IN (" . implode(', ', $userCompanyIds) . ")
              AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
              AND vpe.status_id = ".VendorPerformanceEvaluation::STATUS_IN_PROGRESS."
              AND vpecf.status_id IN (" . implode(', ', [VendorPerformanceEvaluationCompanyForm::STATUS_SUBMITTED, VendorPerformanceEvaluationCompanyForm::STATUS_PENDING_VERIFICATION]) . ")
              AND cgc.hidden IS FALSE
              AND vc.hidden IS FALSE
              AND vwc.hidden IS FALSE";

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!is_array($filters['value']))
                {
                    $val = trim($filters['value']);

                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'project':
                            if(strlen($val) > 0)
                            {
                                $mainQuery .= " AND p.title ILIKE '%{$val}%'";
                            }
                            break;
                        case 'company':
                            if(strlen($val) > 0)
                            {
                                $mainQuery .= " AND c.name ILIKE '%{$val}%'";
                            }
                            break;
                        case 'status':
                            if($val > 0)
                            {
                                $mainQuery .= " AND vpecf.status_id = {$val}";
                            }
                            break;
                        case 'vendor_group':
                            if($val > 0)
                            {
                                $mainQuery .= " AND cgc.id = {$val}";
                            }
                            break;
                        case 'vendor_category':
                            if(strlen($val) > 0)
                            {
                                $mainQuery .= " AND vc.name ILIKE '%{$val}%'";
                            }
                            break;
                        case 'vendor_work_category':
                            if(strlen($val) > 0)
                            {
                                $mainQuery .= " AND vwc.name ILIKE '%{$val}%'";
                            }
                            break;
                    }
                }
            }
        }

        $mainQuery .= " GROUP BY vpe.id, vpecf.id, p.id, c.id, cgc.id, vwc.id";

        $rowCount = count(DB::select(DB::raw($mainQuery)));

        $mainQuery .= " ORDER BY vpecf.id DESC LIMIT {$limit} OFFSET {$offset};";

        $mainResults = DB::select(DB::raw($mainQuery));

        $projectIds = array_unique(array_column($mainResults, 'project_id'));

        $data = [];

        if(!empty($projectIds))
        {
            $activeCycleVpeIds = array_unique(array_column($mainResults, 'id'));
            $companyIds        = array_unique(array_column($mainResults, 'company_id'));

            // current vpe scores
            $activeVpeScoresQuery = "SELECT vpe.id, vpe.project_id, vpecf.id AS form_id, vpecf.company_id, vpecf.vendor_work_category_id, vpecf.score
                                     FROM vendor_performance_evaluations vpe 
                                     INNER JOIN vendor_performance_evaluation_company_forms vpecf ON vpecf.vendor_performance_evaluation_id = vpe.id
                                     WHERE project_id IN (" . implode(', ', $projectIds) . ")
                                     AND vpe.id IN(" . implode(', ', $activeCycleVpeIds) . ")
                                     AND vpe.deleted_at IS NULL
                                     AND vpecf.deleted_at IS NULL
                                     AND vendor_performance_evaluation_cycle_id = {$latestCycle->id}
                                     ORDER BY vpe.id ASC, vpecf.company_id ASC, vpe.project_id ASC, vpecf.vendor_work_category_id ASC;";

            $vpeModuleParameter = VendorPerformanceEvaluationModuleParameter::first();
            $vpeGlobalGrade     = is_null($vpeModuleParameter->vendorManagementGrade) ? null : $vpeModuleParameter->vendorManagementGrade;

            $activeVpeScores = [];

            foreach(DB::select(DB::raw($activeVpeScoresQuery)) as $score)
            {
                $activeVpeScores[$score->project_id][$score->company_id][$score->vendor_work_category_id]['score'] = $score->score;
                $activeVpeScores[$score->project_id][$score->company_id][$score->vendor_work_category_id]['grade'] = (!is_null($score->score) && !is_null($vpeGlobalGrade )) ? $vpeGlobalGrade->getGrade($score->score)->description : null;
            }

            // historical vpe scores
            $historicalVpeScoresQuery = "WITH historical_vpes AS (
                                             SELECT *, RANK() OVER (PARTITION BY project_id ORDER BY id DESC) AS rank
                                             FROM vendor_performance_evaluations
                                             WHERE project_id IN (" . implode(', ', $projectIds) . ")
                                             AND deleted_at IS NULL
                                             AND vendor_performance_evaluation_cycle_id < {$latestCycle->id}
                                             ORDER BY project_id ASC, id DESC
                                         )
                                         SELECT hvpe.id, hvpe.project_id, hvpe.vendor_performance_evaluation_cycle_id, vpecf.id AS form_id, vpecf.company_id, vpecf.vendor_work_category_id, vpecf.score
                                         FROM historical_vpes hvpe
                                         INNER JOIN vendor_performance_evaluation_company_forms vpecf ON vpecf.vendor_performance_evaluation_id = hvpe.id
                                         WHERE hvpe.rank <= 2
                                         AND hvpe.status_id = " . VendorPerformanceEvaluation::STATUS_COMPLETED . "
                                         AND vpecf.status_id = " . VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED . "
                                         AND vpecf.deleted_at IS NULL
                                         AND vpecf.company_id IN (" . implode(', ', $companyIds) . ")
                                         ORDER BY hvpe.vendor_performance_evaluation_cycle_id DESC, hvpe.id ASC, vpecf.company_id ASC, hvpe.project_id ASC, vpecf.vendor_work_category_id ASC;";

            $historicalVpeScores = [];

            foreach(DB::select(DB::raw($historicalVpeScoresQuery)) as $score)
            {
                if(is_null($score->score)) continue;

                $historicalVpeScores[$score->project_id][$score->company_id][$score->vendor_work_category_id][] = [
                    'cycle' => $score->vendor_performance_evaluation_cycle_id,
                    'score' => $score->score,
                    'grade' => (!is_null($score->score) && !is_null($vpeGlobalGrade )) ? $vpeGlobalGrade->getGrade($score->score)->description : null,
                ];
            }

            // combine current and historical
            $allVpeScores = [];

            foreach($activeVpeScores as $projectId => $companyRecords)
            {
                foreach($companyRecords as $companyId => $scoreRecords)
                {
                    foreach($scoreRecords as $vendorWorkCategoryId => $score)
                    {
                        $allVpeScores[$projectId][$companyId][$vendorWorkCategoryId]['score_0'] = $score['score'];
                        $allVpeScores[$projectId][$companyId][$vendorWorkCategoryId]['grade_0'] = $score['grade'];

                        if(isset($historicalVpeScores[$projectId][$companyId][$vendorWorkCategoryId]))
                        {
                            foreach($historicalVpeScores[$projectId][$companyId][$vendorWorkCategoryId] as $key => $historicalScore)
                            {
                                $allVpeScores[$projectId][$companyId][$vendorWorkCategoryId]['score_' . ($key + 1)] = $historicalVpeScores[$projectId][$companyId][$vendorWorkCategoryId][$key]['score'];
                                $allVpeScores[$projectId][$companyId][$vendorWorkCategoryId]['grade_' . ($key + 1)] = $historicalVpeScores[$projectId][$companyId][$vendorWorkCategoryId][$key]['grade'];
                            }
                        }
                    }
                }
            }

            // merge current and historical scores into main query results
            $data = [];

            foreach($mainResults as $key => $mainResult)
            {
                $counter = ($page-1) * $limit + $key + 1;

                $row = [
                    'counter'              => $counter,
                    'vpe_id'               => $mainResult->id,
                    'id'                   => $mainResult->form_id,
                    'project_id'           => $mainResult->project_id,
                    'project'              => StringOperations::shorten($mainResult->project, 100),
                    'company_id'           => $mainResult->company_id,
                    'company'              => $mainResult->company,
                    'status'               => VendorPerformanceEvaluationCompanyForm::getStatusText($mainResult->status_id),
                    'vendor_group'         => $mainResult->vendor_group,
                    'vendor_category'      => implode(', ', json_decode($mainResult->vendor_category)),
                    'vendor_work_category' => $mainResult->vendor_work_category,
                    'route:edit'           => route('vendorPerformanceEvaluation.companyForms.approval.edit', [$mainResult->form_id]),
                ];

                $row['score_0'] = null;
                $row['grade_0'] = null;
                $row['score_1'] = null;
                $row['grade_1'] = null;
                $row['score_2'] = null;
                $row['grade_2'] = null;

                for($i = 0; $i < 3; $i++)
                {
                    if( ! isset($allVpeScores[$mainResult->project_id][$mainResult->company_id][$mainResult->vendor_work_category_id]['score_' . $i]) ) continue;
                    
                    $row['score_' . $i] = $allVpeScores[$mainResult->project_id][$mainResult->company_id][$mainResult->vendor_work_category_id]['score_' . $i];
                    $row['grade_' . $i] = $allVpeScores[$mainResult->project_id][$mainResult->company_id][$mainResult->vendor_work_category_id]['grade_' . $i];
                }

                $data[] = $row;
            }
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data,
        ]);
    }

    public function edit($formId)
    {
        $user = \Confide::user();

        $companyForm = VendorPerformanceEvaluationCompanyForm::find($formId);

        $form = WeightedNode::find($companyForm->weighted_node_id);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        $files = $companyForm->attachments;

        $excludedIds = WeightedNode::where('root_id', '=', $form->id)->where('is_excluded', '=', true)->lists('id', 'id');

        $canAssignVerifiers = ($companyForm->status_id == VendorPerformanceEvaluationCompanyForm::STATUS_SUBMITTED);

        $listOfVerifiers = $companyForm->getListOfVerifiers();

        $canApproveOrReject = ($companyForm->isPendingVerification() && Verifier::isCurrentVerifier($user, $companyForm));

        $verifierLogs = Verifier::getAssignedVerifierRecords($companyForm);

        $vpeScore = $companyForm->score;

        return View::make('vendor_performance_evaluation.approvals.view', compact('companyForm', 'data', 'files', 'excludedIds', 'canAssignVerifiers', 'listOfVerifiers', 'canApproveOrReject', 'verifierLogs', 'vpeScore'));
    }

    public function submitterEdit($formId)
    {
        $companyForm = VendorPerformanceEvaluationCompanyForm::find($formId);

        $form = WeightedNode::find($companyForm->weighted_node_id);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        $evaluation = $companyForm->vendorPerformanceEvaluation;

        $nodeIdsBySelectedScoreIds = WeightedNodeScore::getNodeIdsBySelectedScoreIds($form->id);

        $excludedIds = WeightedNode::where('root_id', '=', $form->id)->where('is_excluded', '=', true)->lists('id', 'id');

        $uploadedFiles = $this->getAttachmentDetails($companyForm);

        $vpeScore = $companyForm->score;

        return View::make('vendor_performance_evaluation.approvals.edit', compact('evaluation', 'companyForm', 'data', 'uploadedFiles', 'nodeIdsBySelectedScoreIds', 'excludedIds', 'vpeScore'));
    }

    public function update($formId)
    {
        $companyForm = VendorPerformanceEvaluationCompanyForm::find($formId);

        if(Input::get('submit') == 'reject')
        {
            $companyForm->processor_remarks = trim(Input::get('remarks'));
            $companyForm->status_id         = VendorPerformanceEvaluationCompanyForm::STATUS_DRAFT;
            $companyForm->save();

            CompanyFormEvaluationLog::logAction($companyForm, CompanyFormEvaluationLog::REJECTED);

            $this->emailNotifier->sendSubmitterRejectedVpeFormNotification($companyForm);

            return Redirect::route('vendorPerformanceEvaluation.evaluations.forms.edit', array($companyForm->vendor_performance_evaluation_id, $companyForm->id));
        }
        elseif(Input::get('submit') == 'approve')
        {
            Verifier::setVerifiers(Input::get('verifiers'), $companyForm);
            $this->verifierRepository->executeFollowUp($companyForm);

            $companyForm->status_id                 = VendorPerformanceEvaluationCompanyForm::STATUS_PENDING_VERIFICATION;
            $companyForm->submitted_for_approval_by = Confide::user()->id;

            $companyForm->save();

            CompanyFormEvaluationLog::logAction($companyForm, CompanyFormEvaluationLog::VERIFIED);
        }

        return Redirect::back();
    }

    public function getProcessorEditLogs($formId)
    {
        $companyForm = VendorPerformanceEvaluationCompanyForm::find($formId);

        $logs = VendorPerformanceEvaluationProcessorEditLog::getEditLogs($companyForm);

        return Response::json($logs);
    }

    public function getEditDetails($formId, $editLogId)
    {
        $companyForm = VendorPerformanceEvaluationCompanyForm::find($formId);

        $editLog = VendorPerformanceEvaluationProcessorEditLog::find($editLogId);

        $details = VendorPerformanceEvaluationProcessorEditDetail::getEditDetails($editLog);

        return Response::json($details);
    }

    public function getCompanyFormEvaluationLogs($formId)
    {
        $companyForm = VendorPerformanceEvaluationCompanyForm::find($formId);
        
        $logs = CompanyFormEvaluationLog::getEvaluationLogs($companyForm);

        return Response::json($logs);
    }
}