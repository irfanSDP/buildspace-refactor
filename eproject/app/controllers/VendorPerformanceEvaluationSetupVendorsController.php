<?php

use Carbon\Carbon;
use PCK\Helpers\DBTransaction;
use PCK\Projects\Project;
use PCK\CompanyProject\CompanyProject;
use PCK\Companies\Company;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluator;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationSetup;
use PCK\VendorPerformanceEvaluation\TemplateForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\WeightedNode\WeightedNode;
use PCK\Forms\VendorPerformanceEvaluationVendorForm;
use PCK\Forms\VendorPerformanceEvaluationSetupCompanyFormChangeForm;
use PCK\ContractGroups\Types\Role;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;
use PCK\Notifications\EmailNotifier;
use PCK\Users\User;

class VendorPerformanceEvaluationSetupVendorsController extends \BaseController {

    protected $vendorPerformanceEvaluationVendorForm;
    protected $vendorPerformanceEvaluationSetupCompanyFormChangeForm;
    protected $cgProjectUserRepo;
    protected $emailNotifier;

    public function __construct(VendorPerformanceEvaluationVendorForm $vendorPerformanceEvaluationVendorForm, VendorPerformanceEvaluationSetupCompanyFormChangeForm $vendorPerformanceEvaluationSetupCompanyFormChangeForm, ContractGroupProjectUserRepository $repo, EmailNotifier $emailNotifier)
    {
        $this->vendorPerformanceEvaluationVendorForm            = $vendorPerformanceEvaluationVendorForm;
        $this->vendorPerformanceEvaluationSetupCompanyFormChangeForm = $vendorPerformanceEvaluationSetupCompanyFormChangeForm;
        $this->cgProjectUserRepo                                = $repo;
        $this->emailNotifier                                    = $emailNotifier;
    }

    public function index($evaluationId)
    {
        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        return View::make('vendor_performance_evaluation.setups.evaluations.vendors.index', compact('evaluation'));
    }

    public function list($evaluationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $model = CompanyProject::select('company_project.id', 'companies.name as company', 'company_project.company_id', 'contract_group_categories.name as contract_group_category')
            ->where('project_id', '=', $evaluation->project_id)
            ->join('companies', 'companies.id', '=', 'company_project.company_id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id');

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
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'group':
                        if(strlen($val) > 0)
                        {
                            $model->where('contract_group_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('companies.name', 'asc')
            ->orderBy('contract_group_categories.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'         => $record->id,
                'counter'    => $counter,
                'name'       => $record->company,
                'group'      => $record->contract_group_category,
                'route:edit' => route('vendorPerformanceEvaluation.setups.evaluations.vendors.edit', array($evaluationId, $record->company_id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function edit($evaluationId, $companyId)
    {
        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $company = Company::find($companyId);

        $selectedEvaluatorIds = VendorPerformanceEvaluator::where('vendor_performance_evaluation_id', '=', $evaluationId)
            ->where('company_id', '=', $company->id)
            ->lists('user_id');

        $data = [];

        $contractGroup = $company->getContractGroup($evaluation->project);
        $assignedUsers = $this->cgProjectUserRepo->getAssignedUsersByProjectAndContractGroup($evaluation->project, $contractGroup);

        $viewerIds = array_keys($assignedUsers);

        $viewers = $company->getActiveUsers()->filter(function($user) use ($viewerIds) {
            return in_array($user->id, $viewerIds);
        });

        foreach($viewers as $user)
        {
            $data[] = [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ];

            $evaluatorIds[] = $user->id;
        }

        $selectedEvaluatorIds = Input::old('evaluator_ids') ?? $selectedEvaluatorIds;

        $isBuCompany = $company->hasProjectRole($evaluation->project, Role::PROJECT_OWNER);

        $canAssignForm       = $isBuCompany ? false : true;
        $canAssignEvaluators = true;

        if($evaluation->type == VendorPerformanceEvaluation::TYPE_180)
        {
            $canAssignEvaluators = $isBuCompany ? true : false;
        }

        return View::make('vendor_performance_evaluation.setups.evaluations.vendors.edit', compact(
            'evaluation',
            'company',
            'data',
            'evaluatorIds',
            'selectedEvaluatorIds',
            'canAssignForm',
            'canAssignEvaluators'
        ));
    }

    public function update($evaluationId, $companyId)
    {
        $inputs = Input::all();

        $evaluation = VendorPerformanceEvaluation::find($evaluationId);
        $company    = Company::find($companyId);
        
        $this->vendorPerformanceEvaluationVendorForm->setEvaluation($evaluation);
        $this->vendorPerformanceEvaluationVendorForm->setCompany($company);
        $this->vendorPerformanceEvaluationVendorForm->validate($inputs);

        $evaluatorIds = VendorPerformanceEvaluator::where('vendor_performance_evaluation_id', '=', $evaluationId)
            ->where('company_id', '=', $companyId)
            ->lists('user_id');

        $selectedEvaluatorIds = isset($inputs['evaluator_ids']) ? $inputs['evaluator_ids'] : [];

        $idsToAssign   = array_diff($selectedEvaluatorIds, $evaluatorIds);
        $idsToUnassign = array_diff($evaluatorIds, $selectedEvaluatorIds);

        foreach($idsToAssign as $userId)
        {
            VendorPerformanceEvaluator::create(array(
                'vendor_performance_evaluation_id' => $evaluationId,
                'company_id'                       => $companyId,
                'user_id'                          => $userId,
            ));
        }

        VendorPerformanceEvaluator::where('vendor_performance_evaluation_id', '=', $evaluationId)
            ->where('company_id', '=', $companyId)
            ->whereIn('user_id', $idsToUnassign)
            ->delete();

        $newlyAssignedUsers = User::whereIn('id', $idsToAssign)->get();

        $this->emailNotifier->sendVpeUsersAssignedAsEvaluators($evaluation, $newlyAssignedUsers);

        return Redirect::route('vendorPerformanceEvaluation.setups.evaluations.vendors.index', array($evaluationId));
    }

    public function listForms($evaluationId, $companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorPerformanceEvaluationSetup::select("vendor_performance_evaluation_setups.id", "vendor_performance_evaluation_setups.company_id", DB::raw("ARRAY_TO_JSON(ARRAY(SELECT DISTINCT * FROM UNNEST(ARRAY_AGG(vendor_categories.name)) AS res ORDER BY res ASC)) AS vendor_category"), "vendor_work_categories.name as vendor_work_category", "weighted_nodes.name as form_name", "vendor_performance_evaluation_setups.template_node_id")
        ->where('vendor_performance_evaluation_setups.vendor_performance_evaluation_id', '=', $evaluationId)
        ->where('vendor_performance_evaluation_setups.company_id', '=', $companyId)
        ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendor_performance_evaluation_setups.vendor_work_category_id')
        ->join('vendor_category_vendor_work_category', 'vendor_category_vendor_work_category.vendor_work_category_id' , '=', 'vendor_work_categories.id')
        ->join('vendor_categories', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
        ->leftJoin('weighted_nodes', 'weighted_nodes.id', '=', 'vendor_performance_evaluation_setups.template_node_id');

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
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
                }
            }
        }

        $model->groupBy('vendor_performance_evaluation_setups.id', 'vendor_work_categories.id', 'weighted_nodes.id', 'vendor_performance_evaluation_setups.id');
        $model->orderBy('vendor_work_categories.name', 'asc');

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
                'vendor_category'      => implode(',', json_decode($record->vendor_category)),
                'vendor_work_category' => $record->vendor_work_category,
                'form'                 => $record->form_name,
                'route:update'         => route('vendorPerformanceEvaluation.setups.evaluations.forms.update', array($evaluationId, $companyId, $record->id)),
                'route:delete'         => route('vendorPerformanceEvaluation.setups.evaluations.forms.delete', array($evaluationId, $companyId, $record->id)),
                'can_delete'           => $record->template_node_id,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function listFormsOptions($evaluationId, $companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $company = Company::find($companyId);

        $model = TemplateForm::select("weighted_nodes.id", "weighted_nodes.name as name")
        ->where('contract_group_category_id', '=', $company->contract_group_category_id)
        ->where('current_selected_revision', '=', true)
        ->whereNotNull('vendor_management_grade_id')
        ->join('weighted_nodes', 'weighted_nodes.id', '=', 'vendor_performance_evaluation_template_forms.weighted_node_id');

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
                            $model->where('weighted_nodes.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('weighted_nodes.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'      => $record->id,
                'counter' => $counter,
                'name'    => $record->name,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function updateForm($evaluationId, $companyId, $setupId)
    {
        $success = false;
        $errorMessage = null;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->vendorPerformanceEvaluationSetupCompanyFormChangeForm->validate(['id' => $setupId]);

            $setup = VendorPerformanceEvaluationSetup::find($setupId);

            $previousTemplateNodeId = $setup->template_node_id;

            $weightedNode = WeightedNode::find(Input::get('id'));
            $templateForm = TemplateForm::where('weighted_node_id', '=', $weightedNode->id)->first();
            $gradingClone = $templateForm->vendorManagementGrade->clone();

            $setup->template_node_id           = $weightedNode->id;
            $setup->vendor_management_grade_id = $gradingClone->id;

            $setup->save();

            if($setup->vendorPerformanceEvaluation->isStatus(VendorPerformanceEvaluation::STATUS_IN_PROGRESS) && $previousTemplateNodeId != $setup->template_node_id)
            {
                VendorPerformanceEvaluationCompanyForm::renewForms($setup->vendorPerformanceEvaluation, $setup->company);
            }

            $transaction->commit();

            $success = true;
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            $errorMessage = trans('forms.anErrorOccured');

            if(!$this->vendorPerformanceEvaluationSetupCompanyFormChangeForm->success)
            {
                $errorMessage = $this->vendorPerformanceEvaluationSetupCompanyFormChangeForm->getErrors()->first();
            }
        }

        if($success)
        {
            $this->emailNotifier->sendVendorAssignedVpeFormNotifications($setup);
        }

        return [
            'success'      => $success,
            'errorMessage' => $errorMessage,
        ];
    }

    public function deleteForm($evaluationId, $companyId, $setupId)
    {
        $success = false;
        $errorMessage = null;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->vendorPerformanceEvaluationSetupCompanyFormChangeForm->validate(['id' => $setupId]);

            $setup = VendorPerformanceEvaluationSetup::find($setupId);

            $previousTemplateNodeId = $setup->template_node_id;

            $grade = $setup->vendorManagementGrade;

            $setup->template_node_id           = null;
            $setup->vendor_management_grade_id = null;

            $setup->save();

            $grade->delete();

            if($setup->vendorPerformanceEvaluation->isStatus(VendorPerformanceEvaluation::STATUS_IN_PROGRESS) && $previousTemplateNodeId != $setup->template_node_id)
            {
                VendorPerformanceEvaluationCompanyForm::renewForms($setup->vendorPerformanceEvaluation, $setup->company);
            }

            $transaction->commit();

            $success = true;
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            $errorMessage = trans('forms.anErrorOccured');

            if(!$this->vendorPerformanceEvaluationSetupCompanyFormChangeForm->success)
            {
                $errorMessage = $this->vendorPerformanceEvaluationSetupCompanyFormChangeForm->getErrors()->first();
            }
        }

        return [
            'success'      => $success,
            'errorMessage' => $errorMessage,
        ];
    }
}