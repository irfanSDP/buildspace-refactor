<?php

use PCK\Projects\Project;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluator;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyFormEvaluationLog as CompanyFormEvaluationLog;
use PCK\VendorPerformanceEvaluation\FormChangeRequest;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationSetup;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;
use Carbon\Carbon;
use PCK\Notifications\EmailNotifier;
use PCK\Forms\VendorPerformanceEvaluationCompanyFormAttachmentsForm;
use PCK\Helpers\ModuleAttachment;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationProcessorEditLog;

class VendorPerformanceEvaluationCompanyFormsController extends \BaseController {

    protected $weightedNodeRepository;
    protected $emailNotifier;
    protected $vendorPerformanceEvaluationCompanyFormAttachmentsForm;

    public function __construct(WeightedNodeRepository $weightedNodeRepository, EmailNotifier $emailNotifier, VendorPerformanceEvaluationCompanyFormAttachmentsForm $vendorPerformanceEvaluationCompanyFormAttachmentsForm)
    {
        $this->weightedNodeRepository                                = $weightedNodeRepository;
        $this->emailNotifier                                         = $emailNotifier;
        $this->vendorPerformanceEvaluationCompanyFormAttachmentsForm = $vendorPerformanceEvaluationCompanyFormAttachmentsForm;
    }

    public function index($evaluationId)
    {
        $user = \Confide::user();

        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $formStatusFilterOptions = [
            0 => trans('general.all'),
            VendorPerformanceEvaluationCompanyForm::STATUS_DRAFT                => trans('forms.draft'),
            VendorPerformanceEvaluationCompanyForm::STATUS_SUBMITTED            => trans('forms.submitted'),
            VendorPerformanceEvaluationCompanyForm::STATUS_PENDING_VERIFICATION => trans('forms.pending'),
            VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED            => trans('forms.completed'),
        ];

        return View::make('vendor_performance_evaluation.evaluations.forms.index', compact('evaluation', 'formStatusFilterOptions'));
    }

    public function list($evaluationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $evaluatorCompany = $user->getAssignedCompany($evaluation->project);

        $model = VendorPerformanceEvaluationCompanyForm::select('vendor_performance_evaluation_company_forms.id', 'companies.name as company', 'weighted_nodes.name as form_name', 'vendor_work_categories.name as vendor_work_category', 'vendor_performance_evaluation_company_forms.status_id')
            ->where('vendor_performance_evaluation_id', '=', $evaluationId)
            ->where('evaluator_company_id', '=', $evaluatorCompany->id)
            ->whereNotNull('weighted_node_id')
            ->join('companies', 'companies.id', '=', 'vendor_performance_evaluation_company_forms.company_id')
            ->join('weighted_nodes', 'weighted_nodes.id', '=', 'vendor_performance_evaluation_company_forms.weighted_node_id')
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendor_performance_evaluation_company_forms.vendor_work_category_id');

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
                    case 'vendor_work_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_work_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'form':
                        if(strlen($val) > 0)
                        {
                            $model->where('weighted_nodes.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'status':
                        if($val > 0)
                        {
                            $model->where('vendor_performance_evaluation_company_forms.status_id', '=', $val);
                        }
                        break;
                }
            }
        }

        $model->orderBy('companies.name', 'asc')
            ->orderBy('vendor_work_categories.name', 'asc');

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
                'company'              => $record->company,
                'vendor_work_category' => $record->vendor_work_category,
                'form'                 => $record->form_name,
                'status'               => VendorPerformanceEvaluationCompanyForm::getStatusText($record->status_id),
                'route:edit'           => route('vendorPerformanceEvaluation.evaluations.forms.edit', array($evaluationId, $record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function edit($evaluationId, $formId)
    {
        $user = \Confide::user();

        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $evaluatorCompany = $user->getAssignedCompany($evaluation->project);

        $companyForm = VendorPerformanceEvaluationCompanyForm::find($formId);

        $form = WeightedNode::find($companyForm->weighted_node_id);

        $nodeIdsBySelectedScoreIds = WeightedNodeScore::getNodeIdsBySelectedScoreIds($form->id);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        $allNodeIds = WeightedNode::where('root_id', '=', $form->id)->lists('id');

        $readOnly = !$companyForm->isDraft();

        $uploadedFiles = $this->getAttachmentDetails($companyForm);

        $excludedIds = WeightedNode::where('root_id', '=', $form->id)->where('is_excluded', '=', true)->lists('id', 'id');

        $vpeScore = $companyForm->score;

        return View::make('vendor_performance_evaluation.evaluations.forms.edit', compact('evaluation', 'companyForm', 'data', 'readOnly', 'uploadedFiles', 'nodeIdsBySelectedScoreIds', 'excludedIds', 'vpeScore'));
    }

    public function update($evaluationId, $formId)
    {
        $input = Input::all();

        $companyForm = VendorPerformanceEvaluationCompanyForm::find($formId);

        $form = $companyForm->weightedNode;

        // originally data, before any changes take place
        $originalSelectedScoreNodes = $this->weightedNodeRepository->getSelectedScoreNodes($form);
        $originalNodesWithScores   = $this->weightedNodeRepository->getNodesWithScores($form);

        WeightedNode::where('root_id', '=', $form->id)
            ->whereIn('id', $input['excluded_ids'] ?? [])
            ->update(['is_excluded' => true]);

        WeightedNode::where('root_id', '=', $form->id)
            ->whereNotIn('id', $input['excluded_ids'] ?? [])
            ->update(['is_excluded' => false]);

        $companyForm->evaluator_remarks = trim($input['evaluator_remarks']);

        $companyForm->save();

        ModuleAttachment::saveAttachments($companyForm, $input);

        $formSubmitType = $input['submit_type'];

        $excludedIds = [];

        if(isset($input['excluded_ids']))
        {
            foreach($input['excluded_ids'] as $key => $id)
            {
                array_push($excludedIds, $id);
            }
        }

        unset($input['_token'], $input['submit_type'], $input['evaluator_remarks'], $input['file'], $input['uploaded_files'], $input['excluded_ids']);

        foreach($input as $nodeId => $scoreId)
        {
            $node = WeightedNode::find($nodeId);

            if($node->root_id != $form->id) continue;

            $score = WeightedNodeScore::find($scoreId);

            if($score->node_id != $node->id) continue;

            WeightedNodeScore::select($score->id);
        }

        $processorEditLogDetails = [];

        // update score column
        $companyForm->load('weightedNode');

        $companyForm->score = $companyForm->weightedNode->getScore();
        $companyForm->save();

        // during "processing"
        if($companyForm->isSubmitted())
        {
            $companyForm->load('weightedNode');

            $form = $companyForm->weightedNode;

            $nodesWithScore = $this->weightedNodeRepository->getNodesWithScores($form);

            foreach($nodesWithScore as $node)
            {
                // true if node has score selected or excluded
                $hasScoreSelectedOrMarkedExcluded = array_key_exists($node['nodeId'], $input) || in_array($node['nodeId'], $excludedIds);

                if( ! $hasScoreSelectedOrMarkedExcluded ) continue;

                $isMarkedAsExcluded = in_array($node['nodeId'], $excludedIds);

                array_push($processorEditLogDetails, [
                    'weighted_node_id'          => $node['nodeId'],
                    'previous_score_id'         => array_key_exists($node['nodeId'], $originalSelectedScoreNodes) && ( ! $originalNodesWithScores[$node['nodeId']]['is_excluded'] ) ? $originalSelectedScoreNodes[$node['nodeId']] : null,
                    'is_previous_node_excluded' => array_key_exists($node['nodeId'], $originalNodesWithScores) ? $originalNodesWithScores[$node['nodeId']]['is_excluded'] : null,
                    'current_score_id'          => $isMarkedAsExcluded ? null : $input[$node['nodeId']],
                    'is_current_node_excluded'  => in_array($node['nodeId'], $excludedIds),
                ]);   
            }

            if(count($processorEditLogDetails) > 0)
            {
                VendorPerformanceEvaluationProcessorEditLog::createLog($companyForm, $processorEditLogDetails);
            }

            return Redirect::route('vendorPerformanceEvaluation.companyForms.approval.edit', array($companyForm->id));
        }

        \Flash::success(trans('forms.savedFormX', ['name' => $form->name]));

        if($formSubmitType == 'submit')
        {
            $this->vendorPerformanceEvaluationCompanyFormAttachmentsForm->setCompanyForm($companyForm);
            $this->vendorPerformanceEvaluationCompanyFormAttachmentsForm->validate(Input::all());

            $companyForm->status_id = VendorPerformanceEvaluationCompanyForm::STATUS_SUBMITTED;

            $companyForm->save();

            CompanyFormEvaluationLog::logAction($companyForm, CompanyFormEvaluationLog::SUBMITTED);

            $this->emailNotifier->sendEvaluatorSubmittedVpeFormNotification($companyForm);

            return Redirect::route('vendorPerformanceEvaluation.companyForms.approval.edit', array($companyForm->id));
        }

        return Redirect::route('vendorPerformanceEvaluation.evaluations.forms.edit', array($evaluationId, $companyForm->id));
    }

    public function formChangeRequest($evaluationId, $formId)
    {
        $inputs      = Input::all();
        $companyForm = VendorPerformanceEvaluationCompanyForm::find($formId);
        $remarks     = ($inputs['remarks'] == '') ? null : $inputs['remarks'];

        $user = \Confide::user();

        $setup = VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $evaluationId)
            ->where('company_id', '=', $companyForm->company_id)
            ->where('vendor_work_category_id', '=', $companyForm->vendor_work_category_id)
            ->first();

        FormChangeRequest::create([
            'vendor_performance_evaluation_setup_id' => $setup->id,
            'user_id'                                => $user->id,
            'remarks'                                => $remarks,
        ]);

        $this->emailNotifier->rejectVendorPerformanceEvaluationForm($companyForm, $remarks);

        return array('success' => true);
    }

    public function getLiveVpeScore($evaluationId, $formId)
    {
        $inputs = Input::all();

        $companyForm = VendorPerformanceEvaluationCompanyForm::find($formId);

        $selectedScoreIds = isset($inputs['selectedScoreIds']) ? array_values($inputs['selectedScoreIds']) : [];
        $exludedScoreIds  = isset($inputs['excludedScoreIds']) ? array_keys($inputs['excludedScoreIds']) : [];

        $vpeScore = $companyForm->weightedNode->calculateScore($selectedScoreIds, $exludedScoreIds);

        return Response::json(['vpeScore' => $vpeScore]);
    }
}