<?php

namespace DigitalStar;

use Input;
use Carbon\Carbon;
use PCK\Helpers\DBTransaction;
use PCK\Helpers\NumberHelper;
use Redirect;
use Request;
use Response;
use View;

use PCK\Companies\Company;
use PCK\CompanyProject\CompanyProject;
use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\LetterOfAward;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\DigitalStar\Evaluation\DsEvaluation;
use PCK\DigitalStar\Evaluation\DsEvaluationForm;
use PCK\DigitalStar\Evaluation\DsEvaluationFormUserRole;
use PCK\DigitalStar\Evaluation\DsRole;
use PCK\Notifications\EmailNotifier;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\VendorManagement\VendorManagementUserPermission;
use PCK\WeightedNode\WeightedNode;
use PCK\DigitalStar\TemplateForm\DsTemplateForm;
use PCK\DigitalStar\Evaluation\DsCycleTemplateForm;

class DsSetupProjectEvaluatorController extends \BaseController
{
    protected $emailNotifier;

    public function __construct(EmailNotifier $emailNotifier)
    {
        $this->emailNotifier = $emailNotifier;
    }

    public function index($evaluationId, $projectId)
    {
        $evaluation = DsEvaluation::find($evaluationId);
        $company = Company::find($evaluation->company_id);
        $project = Project::find($projectId);

        $existingRecord = DsEvaluationForm::where('ds_evaluation_id', $evaluation->id)
                                            ->where('project_id', $project->id)
                                            ->first();

        $weightedNodeRoot = WeightedNode::find($existingRecord->weighted_node_id);

        $weightedNodes= WeightedNode::where('root_id', $weightedNodeRoot->id)->get();

        $projectCycleForm = DsCycleTemplateForm::where('ds_cycle_id', $evaluation->ds_cycle_id)
                                        ->where('type', 'project')
                                        ->first();

        if(count($weightedNodes) == 1)
        {
            $weightedNodeRoot->copyChildren($projectCycleForm->templateForm->weightedNode);
        }

        return View::make('digital_star.setups.evaluations.evaluators.project.index', compact('evaluation', 'company', 'project'));
    }

    public function projects($evaluationId)
    {
        $evaluation = DsEvaluation::find($evaluationId);
        if (! $evaluation) {
            return Response::json([
                'last_page' => 1,
                'data' => []
            ]);
        }

        $evaluation->generateFormsWhenInProgress();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $projectIds = [];

        $records = CompanyProject::where('company_id', '=', $evaluation->company_id)
            ->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::CONTRACTOR))
            ->orderBy('id', 'desc')
            ->get();

        foreach($records as $record) {
            if (! $record->project) continue;

            $projectIds[] = $record->project->id;
        }

        $consultantManagementContracts = ConsultantManagementContract::select('consultant_management_contracts.id')
            ->distinct()
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
            ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
            ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
            ->where('consultant_management_consultant_rfp.company_id', $evaluation->company_id)
            ->whereRaw('consultant_management_consultant_rfp.awarded IS TRUE')
            ->where('consultant_management_letter_of_awards.status', LetterOfAward::STATUS_APPROVED)
            ->get();

        foreach($consultantManagementContracts as $contract) {
            $projectIds[] = $contract->id;
        }

        $model = Project::select(
                'projects.id as project_id',
                'projects.title',
                'projects.reference',
                'projects.status_id',
                'ds_evaluation_forms.id as evaluation_form_id'
            )
            ->join('company_project', 'company_project.project_id', '=', 'projects.id')
            ->join('ds_evaluation_forms', 'ds_evaluation_forms.project_id', '=', 'company_project.project_id')
            ->where('company_project.company_id', '=', $evaluation->company_id)
            ->whereIn('projects.id', $projectIds)
            ->whereNull('projects.deleted_at')
            ->where('ds_evaluation_forms.ds_evaluation_id', '=', $evaluation->id);

        if ($request->has('filters')) {
            foreach ($request->get('filters') as $filters) {
                if (!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch (trim(strtolower($filters['field']))) {
                    case 'title':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;

                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $rowCount = $model->count();

        $records = $model->orderBy('projects.title', 'asc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach ($records as $key => $record) {
            $counter = ($page - 1) * $limit + $key + 1;

            $totalAssigned = DsEvaluationForm::select('ds_evaluation_form_user_roles.id')
                ->join('ds_evaluation_form_user_roles', 'ds_evaluation_form_user_roles.ds_evaluation_form_id', '=', 'ds_evaluation_forms.id')
                ->join('ds_roles', 'ds_roles.id', '=', 'ds_evaluation_form_user_roles.ds_role_id')
                ->where('ds_roles.slug', '=', 'project-evaluator')
                ->where('ds_evaluation_forms.id', '=', $record->evaluation_form_id)
                ->count();

            $data[] = [
                'id'            => $record->project_id,
                'counter'       => $counter,
                'title'         => $record->title,
                'reference'     => $record->reference,
                'status'        => Project::getStatusText($record->status_id),
                'totalAssigned' => NumberHelper::formatNumber($totalAssigned, 0),
                'route:project' => route('digital-star.setups.evaluators.project.index', [$evaluation->id, $record->project_id]),
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data,
        ]);
    }

    public function assigned($evaluationId, $projectId)
    {
        $evaluation = DsEvaluation::find($evaluationId);
        if (! $evaluation) {
            return Response::json([
                'last_page' => 1,
                'data' => [],
            ]);
        }

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
        ->where('ds_roles.slug', '=', 'project-evaluator')
        ->where('ds_evaluation_forms.ds_evaluation_id', '=', $evaluation->id)
        ->where('ds_evaluation_forms.project_id', '=', $projectId);

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
                'route:unassign' => route('digital-star.setups.evaluators.project.unassign', array($evaluation->id, $projectId)),
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }

    public function unassigned($evaluationId, $projectId)
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

        $userIds = VendorManagementUserPermission::getUserIds(VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT);

        $excludeIds = DsEvaluationForm::select('ds_evaluation_form_user_roles.user_id')
            ->join('ds_evaluation_form_user_roles', 'ds_evaluation_form_user_roles.ds_evaluation_form_id', '=', 'ds_evaluation_forms.id')
            ->join('ds_roles', 'ds_roles.id', '=', 'ds_evaluation_form_user_roles.ds_role_id')
            ->where('ds_evaluation_forms.ds_evaluation_id', '=', $evaluation->id)
            ->where('ds_evaluation_forms.project_id', '=', $projectId)
            ->where('ds_roles.slug', '=', 'project-evaluator')
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
                'route:assign' => route('digital-star.setups.evaluators.project.assign', array($evaluation->id, $projectId)),
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }

    public function assign($evaluationId, $projectId)
    {
        $success = false;

        $request = Request::instance();
        if (! $request->has('uid')) {
            return array(
                'success' => $success,
                'errors' => trans('errors.anErrorHasOccurred'),
            );
        }
        $role = DsRole::where('slug', '=', 'project-evaluator')->first();
        if (! $role) {
            return array(
                'success' => $success,
                'errors' => trans('errors.anErrorHasOccurred'),
            );
        }
        $form = DsEvaluationForm::where('ds_evaluation_id', '=', $evaluationId)->where('project_id', '=', $projectId)->first();
        if (! $form) {
            return array(
                'success' => $success,
                'errors' => trans('errors.anErrorHasOccurred'),
            );
        }
        $assignee = User::find($request->input('uid'));
        if (! $assignee) {
            return array(
                'success' => $success,
                'errors' => trans('errors.anErrorHasOccurred'),
            );
        }

        $transaction = new DBTransaction;
        $transaction->begin();

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

                $this->emailNotifier->sendDsNotificationFormAssignedToEvaluator($form, $assignee);   // Email notification
            }

            $success = true;
        } catch (\Exception $e) {
            $transaction->rollback();

            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
        }

        return array(
            'success' => $success,
            'errors' => trans('errors.anErrorHasOccurred'),
        );
    }

    public function unassign($evaluationId, $projectId)
    {
        $success = false;
        $errorMsg = null;

        $request = Request::instance();
        if (! $request->has('uid')) {
            return array(
                'success' => $success
            );
        }
        $role = DsRole::where('slug', '=', 'project-evaluator')->first();
        if (! $role) {
            return array(
                'success' => $success,
                'errors' => trans('errors.anErrorHasOccurred'),
            );
        }
        $form = DsEvaluationForm::where('ds_evaluation_id', '=', $evaluationId)->where('project_id', '=', $projectId)->first();
        if (! $form) {
            return array(
                'success' => $success,
                'errors' => trans('errors.anErrorHasOccurred'),
            );
        }

        try {
            $record = DsEvaluationFormUserRole::where('ds_evaluation_form_id', '=', $form->id)
                ->where('ds_role_id', '=', $role->id)
                ->where('user_id', '=', $request->input('uid'))
                ->first();

            if ($record) $record->delete();

            $success = true;
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();

            \Log::error($e->getMessage());
        }

        return array(
            'success' => $success,
            'errorMsg' => $errorMsg,
        );
    }
}