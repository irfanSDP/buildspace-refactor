<?php

namespace DigitalStar;

use App;
use Carbon\Carbon;
use Controller;
use Input;
use PCK\DigitalStar\Evaluation\DsCycleScore;
use PCK\DigitalStar\Evaluation\DsEvaluation;
use PCK\DigitalStar\Evaluation\DsEvaluationForm;
use PCK\DigitalStar\Evaluation\DsEvaluationFormRemark;
use PCK\DigitalStar\Evaluation\DsRole;
use PCK\ModuleParameters\VendorManagement\VendorManagementGradeLevel;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\Verifier\Verifier;
use Request;
use Response;
use View;

class DsStarRatingController extends Controller
{
    protected $weightedNodeRepository;

    public function __construct(WeightedNodeRepository $weightedNodeRepository)
    {
        $this->weightedNodeRepository = $weightedNodeRepository;
    }

    public function list($companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = DsCycleScore::select(
                'ds_cycles.id as cycle_id',
                'ds_cycle_scores.id as cycle_score_id',
                'ds_cycle_scores.vendor_management_grade_level_id',
                'ds_cycle_scores.total_score as score',
                'ds_cycles.remarks as cycle'
            )
            ->join('ds_cycles', 'ds_cycles.id', '=', 'ds_cycle_scores.ds_cycle_id')
            ->where('ds_cycle_scores.company_id', '=', $companyId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'cycle':
                        if(strlen($val) > 0)
                        {
                            $model->where('ds_cycles.remarks', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $rowCount = $model->count();

        $records = $model->orderBy('ds_cycles.id', 'desc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach($records as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $gradeLevel = VendorManagementGradeLevel::find($record->vendor_management_grade_level_id);

            $data[] = [
                'id'                   => $record->cycle_score_id,
                'counter'              => $counter,
                'cycle'                => $record->cycle,
                'score'                => $record->score,
                'rating'                => $gradeLevel ? $gradeLevel->description : '-',
                'route:company'     => route('digital-star.star-rating.cycle.company', array($companyId, $record->cycle_id)),
                'route:project'    => route('digital-star.star-rating.cycle.project', array($companyId, $record->cycle_id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function company($companyId, $cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = DsEvaluationForm::select(
                'ds_evaluations.id AS evaluation_id',
                'ds_evaluations.ds_cycle_id AS cycle_id',
                'ds_evaluation_forms.id AS form_id',
                'companies.id AS company_id',
                'companies.name AS company_name'
            )
            ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
            ->join('companies', 'companies.id', '=', 'ds_evaluations.company_id')
            ->where('ds_evaluations.status_id', '=', DsEvaluation::STATUS_COMPLETED)
            ->where('ds_evaluation_forms.status_id', '=', DsEvaluationForm::STATUS_COMPLETED)
            ->where('ds_evaluations.company_id', '=', $companyId)
            ->where('ds_evaluations.ds_cycle_id', '=', $cycleId)
            ->whereNull('ds_evaluation_forms.project_id');

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
                    }
                }
            }
        }

        $model->groupBy('ds_evaluations.id', 'ds_evaluation_forms.id', 'companies.id');

        $rowCount = $model->count();

        $records = $model->orderBy('ds_evaluation_forms.id', 'DESC')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        if (! $records->isEmpty()) {
            $cycleScore = DsCycleScore::where('ds_cycle_id', '=', $cycleId)->where('company_id', '=', $companyId)->first();
            $vendorManagementGrade = $cycleScore->vendorManagementGradeLevel->grade;

            foreach ($records as $key => $record) {
                $counter = ($page - 1) * $limit + $key + 1;

                $form = DsEvaluationForm::find($record->form_id);
                $score = $form->weightedNode->getScore();

                $row = [
                    'counter' => $counter,
                    'id' => $record->form_id,
                    'company' => $record->company_name,
                    'score' => $score,
                    'rating' => (! is_null($score) && ! is_null($vendorManagementGrade)) ? $vendorManagementGrade->getGrade($score)->description : null,
                    'route:form_info' => route('digital-star.star-rating.cycle.form-info', array($record->company_id, $record->cycle_id, $record->form_id)),
                    'route:evaluation_log' => route('digital-star.log.evaluation', array($record->form_id)),
                    'route:verifier_log' => route('digital-star.log.verifier', array($record->form_id))
                ];

                $data[] = $row;
            }
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data,
        ]);
    }

    public function project($companyId, $cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = DsEvaluationForm::select(
                'ds_evaluations.id AS evaluation_id',
                'ds_evaluations.ds_cycle_id AS cycle_id',
                'ds_evaluation_forms.id AS form_id',
                'companies.id AS company_id',
                'companies.name AS company_name',
                'projects.id AS project_id',
                'projects.title AS project',
                'projects.reference AS contract_no'
            )
            ->join('projects', 'projects.id', '=', 'ds_evaluation_forms.project_id')
            ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
            ->join('companies', 'companies.id', '=', 'ds_evaluations.company_id')
            ->where('ds_evaluations.company_id', '=', $companyId)
            ->where('ds_evaluations.ds_cycle_id', '=', $cycleId)
            ->where('ds_evaluations.status_id', '=', DsEvaluation::STATUS_COMPLETED)
            ->where('ds_evaluation_forms.status_id', '=', DsEvaluationForm::STATUS_COMPLETED)
            ->whereNotNull('ds_evaluation_forms.project_id')
            ->whereNull('projects.deleted_at');

        if ($request->has('filters')) {
            foreach ($request->get('filters') as $filters) {
                if (!is_array($filters['value'])) {
                    $val = trim($filters['value']);

                    switch (trim(strtolower($filters['field']))) {
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
            $cycleScore = DsCycleScore::where('ds_cycle_id', '=', $cycleId)->where('company_id', '=', $companyId)->first();
            $vendorManagementGrade = $cycleScore->vendorManagementGradeLevel->grade;

            foreach ($records as $key => $record) {
                $counter = ($page - 1) * $limit + $key + 1;

                $form = DsEvaluationForm::find($record->form_id);
                $score = $form->weightedNode->getScore();

                $row = [
                    'counter' => $counter,
                    'id' => $record->form_id,
                    'contract_no' => $record->contract_no,
                    'project' => $record->project,
                    'score' => $score,
                    'rating' => (! is_null($score) && ! is_null($vendorManagementGrade)) ? $vendorManagementGrade->getGrade($score)->description : null,
                    'route:form_info' => route('digital-star.star-rating.cycle.form-info', array($record->company_id, $record->cycle_id, $record->form_id)),
                    'route:evaluation_log' => route('digital-star.log.evaluation', array($record->form_id)),
                    'route:verifier_log' => route('digital-star.log.verifier', array($record->form_id))
                ];

                $data[] = $row;
            }
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data,
        ]);
    }

    public function evaluationFormInfo($companyId, $cycleId, $formId)
    {
        $data = [];

        $evaluationForm = DsEvaluationForm::find($formId);
        if (! $evaluationForm) {
            return Response::json($data);
        }

        $weightedNode = $evaluationForm->weightedNode;
        if (! $weightedNode) {
            return Response::json($data);
        }

        $evaluation = $evaluationForm->evaluation;
        if (! $evaluation) {
            return Response::json($data);
        }

        $company = $evaluation->company;
        if (! $company) {
            return Response::json($data);
        }

        $vendorGroup = $company->contractGroupCategory;
        if (! $vendorGroup) {
            return Response::json($data);
        }

        $data['company'] = $company->name;
        $data['vendor_group'] = $vendorGroup->name;
        $data['form_name'] = $weightedNode->name;
        $data['status'] = DsEvaluationForm::getStatusText($evaluationForm->status_id);

        $project = $evaluationForm->project;
        if ($project) {
            $data['project'] = $project->title;
            $data['reference'] = $project->reference;
        } else {
            $data['project'] = '-';
            $data['reference'] = '-';
        }

        $evaluator = $evaluationForm->submittedForApprovalBy;
        if ($evaluator) {
            $data['evaluator'] = $evaluator->name;
        } else {
            $data['evaluator'] = '-';
        }

        $score = $evaluationForm->weightedNode->getScore();
        if (! is_null($score)) {
            $cycleScore = DsCycleScore::where('ds_cycle_id', '=', $cycleId)->where('company_id', '=', $companyId)->first();
            $vendorManagementGrade = $cycleScore->vendorManagementGradeLevel->grade;

            $data['score'] = $score;
            $data['rating'] = (! is_null($score) && !is_null($vendorManagementGrade)) ? $vendorManagementGrade->getGrade($score)->description : '-';
        } else {
            $data['score'] = '-';
            $data['rating'] = '-';
        }

        $data['evaluator_remarks'] = '-';
        $data['processor_remarks'] = '-';

        $evaluatorRole = DsRole::where('slug', '=', 'company-evaluator')->first();
        if ($evaluatorRole) {
            $evaluationRemarks = DsEvaluationFormRemark::where('ds_evaluation_form_id', '=', $evaluationForm->id)->where('ds_role_id', '=', $evaluatorRole->id)->first();
            $data['evaluator_remarks'] = $evaluationRemarks ? $evaluationRemarks->remarks : '-';
        }

        $processorRole = DsRole::where('slug', '=', 'company-processor')->first();
        if ($processorRole) {
            $processorRemarks = DsEvaluationFormRemark::where('ds_evaluation_form_id', '=', $evaluationForm->id)->where('ds_role_id', '=', $processorRole->id)->first();
            $data['processor_remarks'] = $processorRemarks ? $processorRemarks->remarks : '-';
        }

        $data['route:grid'] = route('digital-star.star-rating.cycle.form', array($companyId, $cycleId, $evaluationForm->id));

        return Response::json($data);
    }

    public function evaluationForm($companyId, $cycleId, $formId)
    {
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

        return Response::json($data);
    }

}