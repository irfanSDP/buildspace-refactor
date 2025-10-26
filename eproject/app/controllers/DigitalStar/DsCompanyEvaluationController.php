<?php

namespace DigitalStar;

use Illuminate\Support\Facades\Redirect;
use PCK\DigitalStar\Evaluation\DsEvaluation;
use PCK\DigitalStar\Evaluation\DsEvaluationForm;
use PCK\DigitalStar\Evaluation\DsEvaluationFormRemark;
use PCK\DigitalStar\Evaluation\DsEvaluationLog;
use PCK\DigitalStar\Evaluation\DsRole;
use PCK\Helpers\ModuleAttachment;
use PCK\Notifications\EmailNotifier;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\WeightedNode\WeightedNodeScore;
use Carbon\Carbon;
use Request;
use Response;
use View;

class DsCompanyEvaluationController extends \BaseController
{
    protected $weightedNodeRepository;
    protected $emailNotifier;

    public function __construct(WeightedNodeRepository $weightedNodeRepository, EmailNotifier $emailNotifier)
    {
        $this->weightedNodeRepository = $weightedNodeRepository;
        $this->emailNotifier = $emailNotifier;
    }

    public function index()
    {
        $statuses = DsEvaluationForm::getStatuses();
        unset($statuses[DsEvaluationForm::STATUS_COMPLETED]);

        return View::make('digital_star.evaluations.company.index', compact('statuses'));
    }

    public function list()
    {
        $user = \Confide::user();

        $model = DsEvaluationForm::select(
                'companies.id as company_id',
                'companies.name as company_name',
                'contract_group_categories.name as vendor_group',
                'ds_evaluations.id as evaluation_id',
                'ds_evaluations.start_date',
                'ds_evaluations.end_date',
                'ds_evaluations.status_id as status_id',
                'ds_evaluation_forms.id as form_id',
                'ds_evaluation_forms.status_id as form_status_id'
            )
            ->distinct()
            ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
            ->join('companies', 'companies.id', '=', 'ds_evaluations.company_id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->leftJoin('users', 'users.company_id', '=', 'companies.id')
            ->whereIn('ds_evaluation_forms.status_id', [
                DsEvaluationForm::STATUS_DRAFT,
                DsEvaluationForm::STATUS_SUBMITTED,
            ])
            ->where('ds_evaluations.status_id', '=', DsEvaluation::STATUS_IN_PROGRESS)
            ->where('ds_evaluations.start_date', '<=', 'now()')
            ->whereNull('ds_evaluation_forms.project_id')
            ->where(function($query) use ($user) {
                $query->where(function($sub) use ($user) {
                    // Admin
                    $isCompanyEvaluator = $user->isGroupAdmin();
                    if ($isCompanyEvaluator) {
                        $sub->where('ds_evaluations.company_id', $user->company_id)
                            ->where('users.is_admin', true);
                    }
                });
            });

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

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
                            $model->where('ds_evaluation_forms.status_id', '=', $val);
                        }
                        break;
                }
            }
        }

        //$processorRole = DsRole::where('slug', '=', 'company-processor')->first();

        $rowCount = $model->count();

        $records = $model->orderBy('ds_evaluations.start_date', 'asc')
            ->orderBy('companies.name', 'asc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach ($records->all() as $key => $record) {
            //$counter = ($page - 1) * $limit + $key + 1;

            $row = [
                'counter' => $key + 1,
                'id' => $record->evaluation_id,
                'company_name' => $record->company_name,
                'vendor_group' => $record->vendor_group,
                'start_date' => Carbon::parse($record->start_date)->format(\Config::get('dates.submitted_at')),
                'end_date' => Carbon::parse($record->end_date)->format(\Config::get('dates.submitted_at')),
                'status' => DsEvaluationForm::getStatusText($record->form_status_id),
            ];

            /*if ($record->form_status_id === DsEvaluationForm::STATUS_SUBMITTED) {    // Form has already been evaluated and submitted
                if ($processorRole) {
                    $isCompanyProcessor = \PCK\DigitalStar\Evaluation\DsEvaluationFormUserRole::where('ds_evaluation_form_id', $record->form_id)
                        ->where('ds_role_id', $processorRole->id)
                        ->where('user_id', $user->id)
                        ->exists();
                } else {
                    $isCompanyProcessor = false;
                }

                if ($isCompanyProcessor) {  // Is processor -> Link to select verifier
                    $row['route:evaluation_form'] = route('digital-star.approval.company.assign-verifiers.edit', array($record->form_id));
                } else {    // Is not processor -> Link to view evaluated form
                    $row['route:evaluation_form'] = route('digital-star.evaluation.company.edit', array($record->evaluation_id, $record->form_id));
                }
            } else {*/
                $row['route:evaluation_form'] = route('digital-star.evaluation.company.edit', array($record->evaluation_id, $record->form_id));
            //}

            $data[] = $row;
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }

    public function edit($evaluationId, $evaluationFormId)
    {
        $evaluation = DsEvaluation::find($evaluationId);
        $evaluatedCompany = $evaluation->company;

        $evaluationForm = DsEvaluationForm::find($evaluationFormId);
        if (! $evaluationForm) {
            \Flash::error(trans('errors.noRecordsFound'));
            return \Redirect::back();
        }

        $evaluatorRole = DsRole::where('slug', '=', 'company-evaluator')->first();
        if (! $evaluatorRole) {
            \Flash::error(trans('errors.anErrorHasOccured'));
            return Redirect::back();
        }

        $processorRole = DsRole::where('slug', '=', 'company-processor')->first();
        if ($processorRole) {
            $user = \Confide::user();
            $isCompanyProcessor = \PCK\DigitalStar\Evaluation\DsEvaluationFormUserRole::where('ds_evaluation_form_id', $evaluationForm->id)
                ->where('ds_role_id', $processorRole->id)
                ->where('user_id', $user->id)
                ->exists();
        } else {
            $isCompanyProcessor = false;
        }
        if ($isCompanyProcessor && ! $evaluationForm->isDraft()) {
            $canViewScore = true;
        } else {
            $canViewScore = false;
        }

        $weightedNode = WeightedNode::find($evaluationForm->weighted_node_id);

        $nodeIdsBySelectedScoreIds = WeightedNodeScore::getNodeIdsBySelectedScoreIds($weightedNode->id);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($weightedNode)];

        $readOnly = !$evaluationForm->isDraft();

        \PCK\Helpers\Hierarchy\AdjacencyListNode::traverse($data[0], function($node) use ($evaluationForm, $readOnly) {
            if(isset($node['hasScores']) && $node['hasScores'])
            {
                if($readOnly)
                {
                    $node['route:getDownloads'] = route('digital-star.evaluation.company.node.downloads', array($evaluationForm->ds_evaluation_id, $evaluationForm->id, $node['nodeId']));
                }
                else
                {
                    $node['route:getUploads'] = route('digital-star.evaluation.company.node.uploads', array($evaluationForm->ds_evaluation_id, $evaluationForm->id, $node['nodeId']));
                    $node['route:doUpload']   = route('digital-star.evaluation.company.node.doUpload', array($evaluationForm->ds_evaluation_id, $evaluationForm->id, $node['nodeId']));
                }
            }
            return $node;
        }, '_children');

        $uploadedFiles = $this->getAttachmentDetails($evaluationForm);

        $excludedIds = WeightedNode::where('root_id', '=', $weightedNode->id)->where('is_excluded', '=', true)->lists('id', 'id');

        $score = $evaluationForm->score;

        $remarks = [];

        $evaluationRemarks = DsEvaluationFormRemark::where('ds_evaluation_form_id', '=', $evaluationForm->id)->where('ds_role_id', '=', $evaluatorRole->id)->first();
        $remarks['evaluator'] = $evaluationRemarks ? $evaluationRemarks->remarks : null;

        $processorRemarks = DsEvaluationFormRemark::where('ds_evaluation_form_id', '=', $evaluationForm->id)->where('ds_role_id', '=', $processorRole->id)->first();
        $remarks['processor'] = $processorRemarks ? $processorRemarks->remarks : null;

        return View::make('digital_star.evaluations.company.edit', compact('evaluation', 'evaluatedCompany', 'evaluationForm', 'data', 'readOnly', 'uploadedFiles', 'nodeIdsBySelectedScoreIds', 'excludedIds', 'score', 'remarks', 'canViewScore'));
    }

    public function getScore($evaluationId, $evaluationFormId)
    {
        $request = Request::instance();
        $inputs = $request->all();

        $selectedScoreIds = isset($inputs['selectedScoreIds']) ? array_values($inputs['selectedScoreIds']) : [];
        $excludedScoreIds  = isset($inputs['excludedScoreIds']) ? array_keys($inputs['excludedScoreIds']) : [];

        $evaluationForm = DsEvaluationForm::find($evaluationFormId);

        $score = $evaluationForm->weightedNode->calculateScore($selectedScoreIds, $excludedScoreIds);

        return Response::json(['score' => $score]);
    }

    public function update($evaluationId, $evaluationFormId)
    {
        $role = DsRole::where('slug', '=', 'company-evaluator')->first();
        if (! $role) {
            \Flash::error(trans('errors.anErrorHasOccured'));
            return Redirect::back();
        }

        $request = Request::instance();
        $input = $request->all();

        $evaluationForm = DsEvaluationForm::find($evaluationFormId);

        $weightedNode = $evaluationForm->weightedNode;

        // original data, before any changes take place
        //$originalSelectedScoreNodes = $this->weightedNodeRepository->getSelectedScoreNodes($weightedNode);
        //$originalNodesWithScores   = $this->weightedNodeRepository->getNodesWithScores($weightedNode);

        WeightedNode::where('root_id', '=', $weightedNode->id)
            ->whereIn('id', $input['excluded_ids'] ?? [])
            ->update(['is_excluded' => true]);

        WeightedNode::where('root_id', '=', $weightedNode->id)
            ->whereNotIn('id', $input['excluded_ids'] ?? [])
            ->update(['is_excluded' => false]);

        $evaluationForm->save();

        $user = \Confide::user();

        $formRemarks = DsEvaluationFormRemark::firstOrCreate([
            'ds_evaluation_form_id' => $evaluationFormId,
            'ds_role_id' => $role->id,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'action' => DsEvaluationFormRemark::ACTION_SUBMIT
        ]);
        $remarks = trim($input['evaluator_remarks']);

        $formRemarks->remarks = ! empty($remarks) ? $remarks : null;
        $formRemarks->save();

        ModuleAttachment::saveAttachments($evaluationForm, $input);

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

            if($node->root_id != $weightedNode->id) continue;

            $score = WeightedNodeScore::find($scoreId);

            if($score->node_id != $node->id) continue;

            WeightedNodeScore::select($score->id);
        }

        //$processorEditLogDetails = [];

        // update score column
        $evaluationForm->load('weightedNode');

        $evaluationForm->score = $evaluationForm->weightedNode->getScore();
        $evaluationForm->save();

        // during "processing"
        if($evaluationForm->isSubmitted())
        {
            $evaluationForm->load('weightedNode');

            $weightedNode = $evaluationForm->weightedNode;

            $nodesWithScore = $this->weightedNodeRepository->getNodesWithScores($weightedNode);

            foreach($nodesWithScore as $node)
            {
                // true if node has score selected or excluded
                $hasScoreSelectedOrMarkedExcluded = array_key_exists($node['nodeId'], $input) || in_array($node['nodeId'], $excludedIds);

                if( ! $hasScoreSelectedOrMarkedExcluded ) continue;

                $isMarkedAsExcluded = in_array($node['nodeId'], $excludedIds);

                /*array_push($processorEditLogDetails, [
                    'weighted_node_id'          => $node['nodeId'],
                    'previous_score_id'         => array_key_exists($node['nodeId'], $originalSelectedScoreNodes) && ( ! $originalNodesWithScores[$node['nodeId']]['is_excluded'] ) ? $originalSelectedScoreNodes[$node['nodeId']] : null,
                    'is_previous_node_excluded' => array_key_exists($node['nodeId'], $originalNodesWithScores) ? $originalNodesWithScores[$node['nodeId']]['is_excluded'] : null,
                    'current_score_id'          => $isMarkedAsExcluded ? null : $input[$node['nodeId']],
                    'is_current_node_excluded'  => in_array($node['nodeId'], $excludedIds),
                ]);*/
            }

            /*if(count($processorEditLogDetails) > 0)
            {
                VendorPerformanceEvaluationProcessorEditLog::createLog($evaluationForm, $processorEditLogDetails);
            }*/

            return \Redirect::route('digital-star.evaluation.company.edit', array($evaluationId, $evaluationForm->id));
        }

        \Flash::success(trans('forms.savedFormX', ['name' => $weightedNode->name]));

        if ($formSubmitType == 'submit')
        {
            //$this->vendorPerformanceEvaluationCompanyFormAttachmentsForm->setCompanyForm($evaluationForm);
            //$this->vendorPerformanceEvaluationCompanyFormAttachmentsForm->validate(Input::all());

            $evaluationForm->status_id = DsEvaluationForm::STATUS_SUBMITTED;

            $evaluationForm->save();

            DsEvaluationLog::logAction($evaluationForm->id, 'submitted-to-processor', $role->id, $user);

            $this->emailNotifier->sendDsNotificationFormSubmittedByEvaluator($evaluationForm);  // Email notification

            return \Redirect::route('digital-star.evaluation.company.edit', array($evaluationId, $evaluationForm->id));
        }

        return \Redirect::route('digital-star.evaluation.company.edit', array($evaluationId, $evaluationForm->id));
    }

    public function getDownloads($evaluationId, $evaluationFormId, $nodeId) {
        $data = array();

        $node = WeightedNode::find($nodeId);

        foreach($node->getAttachmentDetails() as $upload)
        {
            $data[] = array(
                'filename'    => $upload->filename,
                'download_url' => $upload->download_url,
                'uploaded_by'  => $upload->createdBy->name,
                'uploaded_at'  => Carbon::parse($upload->created_at)->format(\Config::get('dates.created_at')),
            );
        }

        return $data;
    }

    public function getUploads($evaluationId, $evaluationFormId, $nodeId) {
        $node = WeightedNode::find($nodeId);

        $uploadedFiles = $this->getAttachmentDetails($node);

        $data = array();

        foreach($uploadedFiles as $file)
        {
            $file['imgSrc']      = $file->generateThumbnailURL();
            $file['deleteRoute'] = $file->generateGeneralDeleteURL();
            $file['createdAt']   = Carbon::parse($file->created_at)->format(\Config::get('dates.created_at'));
            $file['size']        = \PCK\Base\Helpers::formatBytes($file->size);

            $data[] = $file;
        }

        return $data;
    }

    public function doUpload($evaluationId, $evaluationFormId, $nodeId) {
        $request = Request::instance();
        $input = $request->all();

        $node = WeightedNode::find($nodeId);

        \PCK\Helpers\ModuleAttachment::saveAttachments($node, $input);

        return array(
            'success' => true,
        );
    }
}