<?php

namespace DigitalStar;

use Carbon\Carbon;
use Confide;
use Input;
use PCK\DigitalStar\Evaluation\DsEvaluationForm;
use PCK\DigitalStar\Evaluation\DsEvaluationLog;
use PCK\Verifier\Verifier;
use Redirect;
use Request;
use Response;
use View;

class DsLogController extends \BaseController
{
    /*private function canViewLog($formId)
    {
        $user = \Confide::user();
        $evaluationForm = DsEvaluationForm::find($formId);
        $canApproveOrReject = ($evaluationForm->isPendingVerification() && Verifier::isCurrentVerifier($user, $evaluationForm));
    }*/

    public function evaluationLog($formId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = DsEvaluationLog::select(
                'ds_evaluation_logs.id as logId',
                'ds_evaluation_logs.created_at as actionDate',
                'users.name as actionBy',
                'ds_action_types.slug as actionTypeSlug'
            )
            ->join('users', 'users.id', '=', 'ds_evaluation_logs.user_id')
            ->join('ds_roles', 'ds_roles.id', '=', 'ds_evaluation_logs.ds_role_id')
            ->join('ds_action_types', 'ds_action_types.id', '=', 'ds_evaluation_logs.ds_action_type_id')
            ->where('ds_evaluation_logs.ds_evaluation_form_id', '=', $formId);

        if ($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
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

        $rowCount = $model->count();

        $records = $model->orderBy('ds_evaluation_logs.id', 'DESC')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach($records as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $actionType = '';

            switch ($record->actionTypeSlug)
            {
                case 'submitted':
                    $actionType = trans('digitalStar/actionTypes.submitted');
                    break;

                case 'submitted-to-processor':
                    $actionType = trans('digitalStar/actionTypes.submittedToProcessor');
                    break;

                case 'submitted-for-approval':
                    $actionType = trans('digitalStar/actionTypes.submittedForApproval');
                    break;

                case 'rejected':
                    $actionType = trans('digitalStar/actionTypes.rejected');
                    break;

                case 'verified':
                    $actionType = trans('digitalStar/actionTypes.verified');
                    break;

                default:
                    // Do nothing
            }

            $data[] = [
                'id'            => $record->logId,
                'counter'       => $counter,
                'actionBy'      => $record->actionBy,
                'actionType'    => $actionType,
                'actionDate'    => Carbon::parse($record->actionDate)->format(\Config::get('dates.created_at')),
            ];


        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function verifierLog($formId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Verifier::select('verifiers.id', 'verifiers.approved', 'verifiers.verified_at', 'verifiers.remarks', 'users.name')
            ->join('users', 'users.id', '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', '=', $formId)
            ->where('verifiers.object_type', '=', DsEvaluationForm::class);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
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

        $rowCount = $model->count();

        $records = $model->orderBy('verifiers.sequence_number', 'asc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach($records as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'          => $record->id,
                'counter'     => $counter,
                'name'        => $record->name,
                'approved'    => $record->approved,
                'verified_at' => Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')),
                'remarks'     => $record->remarks,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }
}