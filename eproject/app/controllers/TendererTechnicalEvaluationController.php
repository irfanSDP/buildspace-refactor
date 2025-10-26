<?php

use PCK\Companies\Company;
use Carbon\Carbon;
use PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationResponseLog;
use PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository;
use PCK\Tenders\TenderRepository;

class TendererTechnicalEvaluationController extends \BaseController {

    private $setReferenceRepository;
    private $tenderRepository;

    public function __construct(TechnicalEvaluationSetReferenceRepository $setReferenceRepository, TenderRepository $tenderRepository)
    {
        $this->setReferenceRepository = $setReferenceRepository;
        $this->tenderRepository       = $tenderRepository;
    }

    public function getFormResponses($project, $tenderId, $companyId)
    {
        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);

        $tender       = $this->tenderRepository->find($project, $tenderId);
        $tenderer     = Company::findOrFail($companyId);
        $formRoute    = route((Confide::user()->company_id == $tenderer->id) ? 'technicalEvaluation.form.update' : 'technicalEvaluation.form.update.foreign', [$project->id, $tenderer->id]);

        return Response::json([
            'company_name'             => $tenderer->name,
            'technical_evaluation_set' => $setReference->getCompleteSet(),
            'selected_options'         => TechnicalEvaluationTendererOption::getTendererOptionIds($tenderer, $setReference->set),
            'option_remarks'           => TechnicalEvaluationTendererOption::getAllOptionRemarks($tenderer, $setReference->set),
            'submission_date'          => $setReference->getAttachmentsSubmissionTime($tenderer),
            'form_route'               => $formRoute,
            'log_route'                => route('technicalEvaluation.formResponses.log', [$project->id, $tender->id, $tenderer->id]),
        ]);
    }

    public function getFormResponseLog($project, $tenderId, $companyId)
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);

        $model = TechnicalEvaluationResponseLog::select('technical_evaluation_response_log.id', 'users.name', 'technical_evaluation_response_log.created_at')
            ->join('users', 'users.id', '=', 'technical_evaluation_response_log.user_id')
            ->where('technical_evaluation_response_log.set_reference_id', '=', $setReference->id)
            ->where('technical_evaluation_response_log.company_id', '=', $companyId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('technical_evaluation_response_log.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'        => $record->id,
                'counter'   => $counter,
                'name'      => $record->name,
                'timestamp' => Carbon::parse($record->created_at)->format(\Config::get('dates.created_at')),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }
}