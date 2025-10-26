<?php

use PCK\Projects\Project;
use PCK\CompanyProject\CompanyProject;
use PCK\Companies\Company;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluator;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationSetup;
use PCK\VendorPerformanceEvaluation\TemplateForm;
use PCK\WeightedNode\WeightedNode;
use PCK\Forms\VendorPerformanceEvaluationVendorForm;
use Carbon\Carbon;

class VendorPerformanceEvaluationCycleEvaluationVendorsController extends \BaseController {

    protected $vendorPerformanceEvaluationVendorForm;

    public function __construct(VendorPerformanceEvaluationVendorForm $vendorPerformanceEvaluationVendorForm)
    {
        $this->vendorPerformanceEvaluationVendorForm = $vendorPerformanceEvaluationVendorForm;
    }

    public function index($cycleId, $evaluationId)
    {
        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $companyProject = CompanyProject::where('project_id', '=', $evaluation->project_id)->orderBy('contract_group_id')->get();

        $data = [];

        foreach($companyProject as $record)
        {
            $setup = VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $evaluation->id)
                ->where('company_id', '=', $record->company->id)
                ->first();

            $data[] = [
                'id' => $record->company->id,
                'name' => $record->company->name,
                'group' => $record->company->contractGroupCategory->name,
                'form' => ( $setup && $setup->weightedNode ) ? $setup->weightedNode->name : '',
                'route:edit' => route('vendorPerformanceEvaluation.cycles.evaluations.vendors.edit', array($cycleId, $evaluationId, $record->company->id)),
            ];
        }

        return View::make('vendor_performance_evaluation.cycles.evaluations.vendors.index', compact('evaluation', 'data'));
    }

    public function edit($cycleId, $evaluationId, $companyId)
    {
        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $company = Company::find($companyId);

        $templateForms = TemplateForm::where('contract_group_category_id', '=', $company->contract_group_category_id)->get();

        $formOptions = [];

        foreach($templateForms as $templateForm)
        {
            $formOptions[$templateForm->weighted_node_id] = $templateForm->weightedNode->name;
        }

        $existingSetup = VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $evaluation->id)
            ->where('company_id', '=', $company->id)
            ->first();
        
        $defaultTemplateForm = TemplateForm::where('contract_group_category_id', '=', $company->contract_group_category_id)
            ->where('project_status_id', '=', $evaluation->project_status_id)
            ->first();

        $selectedTemplateFormId = Input::old('form_id') ?? $existingSetup->template_node_id ?? $defaultTemplateForm->weighted_node_id ?? null;

        $selectedEvaluatorIds = VendorPerformanceEvaluator::where('vendor_performance_evaluation_id', '=', $evaluationId)
            ->where('company_id', '=', $company->id)
            ->lists('user_id');

        $data = [];

        foreach($company->getActiveUsers() as $user)
        {
            $data[] = [
                'id' => $user->id,
                'name' => $user->name
            ];

            $evaluatorIds[] = $user->id;
        }

        $selectedEvaluatorIds = Input::old('evaluator_ids') ?? $selectedEvaluatorIds;

        return View::make('vendor_performance_evaluation.cycles.evaluations.vendors.edit', compact(
            'cycleId',
            'evaluation',
            'company',
            'formOptions',
            'selectedTemplateFormId',
            'data',
            'evaluatorIds',
            'selectedEvaluatorIds'
        ));
    }

    public function update($cycleId, $evaluationId, $companyId)
    {
        $this->vendorPerformanceEvaluationVendorForm->validate(Input::all());

        $setup = VendorPerformanceEvaluationSetup::firstOrNew(array(
            'vendor_performance_evaluation_id' => $evaluationId,
            'company_id' => $companyId,
        ));

        $weightedNode = WeightedNode::find(Input::get('form_id'));

        $setup->template_node_id = $weightedNode->id;
        $setup->save();

        $evaluatorIds = VendorPerformanceEvaluator::where('vendor_performance_evaluation_id', '=', $evaluationId)
            ->where('company_id', '=', $companyId)
            ->lists('user_id');

        $idsToAssign   = array_diff(Input::get('evaluator_ids'), $evaluatorIds);
        $idsToUnassign = array_diff($evaluatorIds, Input::get('evaluator_ids'));

        foreach($idsToAssign as $userId)
        {
            VendorPerformanceEvaluator::create(array(
                'vendor_performance_evaluation_id' => $evaluationId,
                'company_id' => $companyId,
                'user_id' => $userId,
            ));
        }

        VendorPerformanceEvaluator::where('vendor_performance_evaluation_id', '=', $evaluationId)
            ->where('company_id', '=', $companyId)
            ->whereIn('user_id', $idsToUnassign)
            ->delete();

        return Redirect::route('vendorPerformanceEvaluation.cycles.evaluations.vendors.index', array($cycleId, $evaluationId));
    }
}