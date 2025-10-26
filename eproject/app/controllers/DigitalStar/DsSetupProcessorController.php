<?php

namespace DigitalStar;

use Input;
use Carbon\Carbon;
use PCK\Helpers\DBTransaction;
use Redirect;
use Request;
use Response;
use View;

use PCK\Companies\Company;
use PCK\DigitalStar\Evaluation\DsEvaluation;
use PCK\DigitalStar\Evaluation\DsEvaluationForm;
use PCK\DigitalStar\Evaluation\DsEvaluationFormUserRole;
use PCK\DigitalStar\Evaluation\DsRole;
use PCK\Notifications\EmailNotifier;
use PCK\Users\User;
use PCK\VendorManagement\VendorManagementUserPermission;

class DsSetupProcessorController extends \BaseController
{
    protected $emailNotifier;

    public function __construct(EmailNotifier $emailNotifier)
    {
        $this->emailNotifier = $emailNotifier;
    }

    public function index($evaluationId)
    {
        $evaluation = DsEvaluation::find($evaluationId);
        $company = Company::find($evaluation->company_id);

        return View::make('digital_star.setups.evaluations.processors.index', compact('evaluation', 'company'));
    }

    public function assigned($evaluationId)
    {
        $evaluation = DsEvaluation::find($evaluationId);
        if (! $evaluation) {
            return Response::json([
                'last_page' => 1,
                'data' => [],
            ]);
        }

        $evaluation->generateFormsWhenInProgress();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = DsEvaluationForm::select(
            'users.id as user_id',
            'users.name as user_name',
            'users.email as user_email',
            'companies.id as company_id',
            'companies.name as company_name',
            'contract_group_categories.name as vendor_group'
        )
        ->join('ds_evaluation_form_user_roles', 'ds_evaluation_form_user_roles.ds_evaluation_form_id', '=', 'ds_evaluation_forms.id')
        ->join('ds_roles', 'ds_roles.id', '=', 'ds_evaluation_form_user_roles.ds_role_id')
        ->join('users', 'users.id', '=', 'ds_evaluation_form_user_roles.user_id')
        ->join('companies', 'companies.id', '=', 'users.company_id')
        ->join('contract_group_categories', 'companies.contract_group_category_id', '=', 'contract_group_categories.id')
        ->where('ds_roles.slug', '=', 'company-processor')
        ->where('ds_evaluation_forms.ds_evaluation_id', '=', $evaluation->id)
        ->whereNull('ds_evaluation_forms.project_id');

        if ($request->has('filters')) {
            foreach ($request->get('filters') as $filters) {
                if (!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch (trim(strtolower($filters['field']))) {
                    case 'user_name':
                        if (strlen($val) > 0) {
                            $model->where('users.name', 'ILIKE', '%' . $val . '%');
                        }
                        break;

                    case 'user_email':
                        if (strlen($val) > 0) {
                            $model->where('users.email', 'ILIKE', '%' . $val . '%');
                        }
                        break;

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
                }
            }
        }

        $rowCount = $model->count();

        $records = $model->orderBy('users.name', 'asc')
            ->orderBy('companies.name', 'asc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach ($records->all() as $key => $record) {
            $counter = ($page - 1) * $limit + $key + 1;

            $data[] = [
                'id' => $record->user_id,
                'counter' => $counter,
                'user_name' => $record->user_name,
                'user_email' => $record->user_email,
                'company_id' => $record->company_id,
                'company_name' => $record->company_name,
                'vendor_group' => $record->vendor_group,
                'route:unassign' => route('digital-star.setups.processors.company.unassign', array($evaluation->id)),
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }

    public function unassigned($evaluationId)
    {
        $evaluation = DsEvaluation::find($evaluationId);
        if (! $evaluation) {
            return Response::json([
                'last_page' => 1,
                'data' => []
            ]);
        }

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $userIds = VendorManagementUserPermission::getUserIds(VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_COMPANY);

        $excludeIds = DsEvaluationForm::select('ds_evaluation_form_user_roles.user_id')
            ->join('ds_evaluation_form_user_roles', 'ds_evaluation_form_user_roles.ds_evaluation_form_id', '=', 'ds_evaluation_forms.id')
            ->join('ds_roles', 'ds_roles.id', '=', 'ds_evaluation_form_user_roles.ds_role_id')
            ->where('ds_evaluation_forms.ds_evaluation_id', '=', $evaluation->id)
            ->whereNull('ds_evaluation_forms.project_id')
            ->where('ds_roles.slug', '=', 'company-processor')
            ->lists('user_id');

        $model = User::select(
            'users.id as user_id',
            'users.name as user_name',
            'users.email as user_email',
            'companies.id as company_id',
            'companies.name as company_name',
            'contract_group_categories.name as vendor_group'
        )
        ->join('companies', 'companies.id', '=', 'users.company_id')
        ->join('contract_group_categories', 'companies.contract_group_category_id', '=', 'contract_group_categories.id')
        ->whereNotIn('users.id', $excludeIds)
        ->whereIn('users.id', $userIds);

        if ($request->has('filters')) {
            foreach ($request->get('filters') as $filters) {
                if (!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch (trim(strtolower($filters['field']))) {
                    case 'user_name':
                        if (strlen($val) > 0) {
                            $model->where('users.name', 'ILIKE', '%' . $val . '%');
                        }
                        break;

                    case 'user_email':
                        if (strlen($val) > 0) {
                            $model->where('users.email', 'ILIKE', '%' . $val . '%');
                        }
                        break;

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
                }
            }
        }

        $rowCount = $model->count();

        $records = $model->orderBy('users.name', 'asc')
            ->orderBy('companies.name', 'asc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach ($records->all() as $key => $record) {
            $counter = ($page - 1) * $limit + $key + 1;

            $data[] = [
                'id' => $record->user_id,
                'counter' => $counter,
                'user_name' => $record->user_name,
                'user_email' => $record->user_email,
                'company_id' => $record->company_id,
                'company_name' => $record->company_name,
                'vendor_group' => $record->vendor_group,
                'route:assign' => route('digital-star.setups.processors.company.assign', array($evaluation->id)),
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }

    public function assign($evaluationId)
    {
        $success = false;

        $request = Request::instance();
        if (! $request->has('uid')) {
            return array(
                'success' => $success,
                'errors' => array(
                    'msg' => trans('errors.anErrorHasOccurred'),
                    'code' => 'missing_uid',
                    'error' => 'User ID is missing in the request'
                )
            );
        }
        $role = DsRole::where('slug', '=', 'company-processor')->first();
        if (! $role) {
            return array(
                'success' => $success,
                'errors' => array(
                    'msg' => trans('errors.anErrorHasOccurred'),
                    'code' => 'missing_role',
                    'error' => 'company-processor role not found'
                )
            );
        }
        $form = DsEvaluationForm::where('ds_evaluation_id', '=', $evaluationId)->whereNull('project_id')->first();
        if (! $form) {
            return array(
                'success' => $success,
                'errors' => array(
                    'msg' => trans('errors.anErrorHasOccurred'),
                    'code' => 'missing_form',
                    'error' => 'Evaluation form not found for the given evaluation ID'
                )
            );
        }
        $assignee = User::find($request->input('uid'));
        if (! $assignee) {
            return array(
                'success' => $success,
                'errors' => array(
                    'msg' => trans('errors.anErrorHasOccurred'),
                    'code' => 'assignee_not_found',
                    'error' => 'Assignee user ID not found'
                )
            );
        }

        $transaction = new DBTransaction;
        $transaction->begin();
        $transactionError = null;

        try {
            $exists = DsEvaluationFormUserRole::select('user_id')
                ->where('ds_evaluation_form_id', '=', $form->id)
                ->where('ds_role_id', '=', $role->id)
                ->where('user_id', '=', $request->input('uid'))
                ->exists();
            if (! $exists) {
                $data = [
                    'ds_evaluation_form_id' => $form->id,
                    'ds_role_id' => $role->id,
                    'user_id' => $request->input('uid')
                ];

                $company = $assignee->company;
                if ($company) {
                    $data['company_id'] = $company->id;
                }

                DsEvaluationFormUserRole::create($data);

                $transaction->commit();

                $this->emailNotifier->sendDsNotificationFormAssignedToProcessor($form, $assignee);   // Email notification
            }

            $success = true;
        } catch (\Exception $e) {
            $transaction->rollback();

            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            $transactionError = $e->getMessage();
        }

        return array(
            'success' => $success,
            'errors' => array(
                'msg' => trans('errors.anErrorHasOccurred'),
                'code' => 'transaction_failed',
                'error' => $transactionError
            )
        );
    }

    public function unassign($evaluationId)
    {
        $success = false;
        $errorMsg = null;

        $request = Request::instance();
        if (! $request->has('uid')) {
            return array(
                'success' => $success,
                'errors' => array(
                    'msg' => trans('errors.anErrorHasOccurred'),
                    'code' => 'missing_uid',
                    'error' => 'User ID is missing in the request'
                )
            );
        }
        $role = DsRole::where('slug', '=', 'company-processor')->first();
        if (! $role) {
            return array(
                'success' => $success,
                'errors' => array(
                    'msg' => trans('errors.anErrorHasOccurred'),
                    'code' => 'missing_role',
                    'error' => 'company-processor role not found'
                )
            );
        }
        $form = DsEvaluationForm::where('ds_evaluation_id', '=', $evaluationId)->whereNull('project_id')->first();
        if (! $form) {
            return array(
                'success' => $success,
                'errors' => array(
                    'msg' => trans('errors.anErrorHasOccurred'),
                    'code' => 'missing_form',
                    'error' => 'Evaluation form not found for the given evaluation ID'
                )
            );
        }

        $transactionError = null;

        try {
            $record = DsEvaluationFormUserRole::where('ds_evaluation_form_id', '=', $form->id)
                ->where('ds_role_id', '=', $role->id)
                ->where('user_id', '=', $request->input('uid'))
                ->first();

            if ($record) $record->delete();

            $success = true;
        } catch (\Exception $e) {
            $transactionError = $e->getMessage();

            \Log::error($transactionError);
        }

        return array(
            'success' => $success,
            'errors' => array(
                'msg' => trans('errors.anErrorHasOccurred'),
                'code' => 'transaction_failed',
                'error' => $transactionError
            )
        );
    }
}