<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Helpers\VpeHelper;
use PCK\Projects\Project;
use PCK\VendorPerformanceEvaluation\TemplateForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\Forms\VendorPerformanceEvaluationFormForm;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;
use PCK\Forms\VendorPrequalificationSelectGradeTemplateForm;
use PCK\Helpers\DBTransaction;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;

class VendorPerformanceEvaluationTemplateFormsController extends \BaseController {

	protected $weightedNodeRepository;
	protected $vendorPerformanceEvaluationFormForm;
	protected $vendorPreqSelectGradeTemplateForm;

	public function __construct(WeightedNodeRepository $weightedNodeRepository, VendorPerformanceEvaluationFormForm $vendorPerformanceEvaluationFormForm, VendorPrequalificationSelectGradeTemplateForm $vendorPreqSelectGradeTemplateForm)
	{
		$this->weightedNodeRepository 			   = $weightedNodeRepository;
		$this->vendorPerformanceEvaluationFormForm = $vendorPerformanceEvaluationFormForm;
		$this->vendorPreqSelectGradeTemplateForm   = $vendorPreqSelectGradeTemplateForm;
	}

	public function index()
	{
		$data = [];

		/*$forms = TemplateForm::whereRaw(\DB::raw('id = original_form_id'))
			->orderBy('id', 'desc')
			->get();*/
        $forms = TemplateForm::orderBy('id', 'desc')->get();

		foreach($forms as $form)
		{
			$currentEditingForm = TemplateForm::getCurrentEditingForm($form->original_form_id);

			$templateForm = TemplateForm::getTemplateForm($form->original_form_id);

			$data[] = [
				'id' 		   		=> $form->id,
				'name' 		   		=> $currentEditingForm->weightedNode->name,
				'vendorGroup'  		=> $currentEditingForm->contractGroupCategory->name,
				'projectStage' 		=> VendorPerformanceEvaluation::getProjectStageName($currentEditingForm->project_status_id),
				'status' 			=> TemplateForm::getStatusText($currentEditingForm->status_id),
				'route:form' 		=> route('vendorPerformanceEvaluation.templateForm.nodes', array($currentEditingForm->id, $currentEditingForm->weighted_node_id)),
				'route:edit' 		=> $currentEditingForm->isDraft() ? route('vendorPerformanceEvaluation.templateForms.edit', array($currentEditingForm->id)) : null,
				'route:approval'    => $currentEditingForm->isDraft() ? route('vendorPerformanceEvaluation.templateForms.approval', array($currentEditingForm->id)) : null,
				'route:template'    => ! is_null($templateForm) ? route('vendorPerformanceEvaluation.templateForms.template', array($templateForm->id)) : null,
				'route:newRevision' => ( ! is_null($currentEditingForm) && $currentEditingForm->isCompleted() ) ? route('vendorPerformanceEvaluation.templateForms.newRevision', array($currentEditingForm->id)) : null,
				'route:clone' 	    => route('vendorPerformanceEvaluation.templateForms.clone', array($currentEditingForm->id)),
				'route:grade' 	    => ($templateForm && $templateForm->isCompleted()) ? route('vendorPerformanceEvaluation.templateForms.grade', array($templateForm->id)) : null,
			];			
		}

		return View::make('vendor_performance_evaluation.template_forms.index', compact('data'));
	}

	public function create()
	{
		$projectStatuses = array(
			VendorPerformanceEvaluation::PROJECT_STAGE_DESIGN 					=> VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::PROJECT_STAGE_DESIGN),
			VendorPerformanceEvaluation::PROJECT_STAGE_CONSTRUCTION 			=> VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::PROJECT_STAGE_CONSTRUCTION),
			VendorPerformanceEvaluation::PROJECT_STAGE_DEFECTS_LIABILITY_PERIOD => VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::PROJECT_STAGE_DEFECTS_LIABILITY_PERIOD),
		);

		$vendorGroupOptions = ContractGroupCategory::orderBy('name', 'asc')
			->where('hidden', '=', false)
			->lists('name', 'id');

		return View::make('vendor_performance_evaluation.template_forms.create', compact('projectStatuses', 'vendorGroupOptions'));
	}

	public function store()
	{
		$this->vendorPerformanceEvaluationFormForm->validate(Input::all());

	    $weightedNode = WeightedNode::create(array(
	    	'name' => Input::get('name'),
	    ));

        $vmGrade = VpeHelper::getCurrentVendorManagementGrade();
        if (empty($vmGrade)) {
            \Flash::error('Please set vendor management grade before creating new revision');
            return Redirect::back();
        }

	    $form = TemplateForm::create(array(
	    	'contract_group_category_id' => Input::get("contract_group_category_id"),
	    	'weighted_node_id' 			 => $weightedNode->id,
	    	'project_status_id'          => Input::get('project_status_id'),
            'vendor_management_grade_id' => $vmGrade->id
	    ));

	    return Redirect::route('vendorPerformanceEvaluation.templateForm.nodes', array($form->id, $weightedNode->id));
	}

	public function edit($templateFormId)
	{
		$templateForm = TemplateForm::find($templateFormId);
        /*if ($templateForm) {
			$templateForm->delete();

		}
		return Redirect::back();*/

		$templateForm->name = $templateForm->weightedNode->name;

		$projectStatuses = array(
			VendorPerformanceEvaluation::PROJECT_STAGE_DESIGN 					=> VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::PROJECT_STAGE_DESIGN),
			VendorPerformanceEvaluation::PROJECT_STAGE_CONSTRUCTION 			=> VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::PROJECT_STAGE_CONSTRUCTION),
			VendorPerformanceEvaluation::PROJECT_STAGE_DEFECTS_LIABILITY_PERIOD => VendorPerformanceEvaluation::getProjectStageName(VendorPerformanceEvaluation::PROJECT_STAGE_DEFECTS_LIABILITY_PERIOD),
		);

		$vendorGroupOptions = ContractGroupCategory::orderBy('name', 'asc')
			->where('hidden', '=', false)
			->lists('name', 'id');

		return View::make('vendor_performance_evaluation.template_forms.edit', compact('templateForm', 'projectStatuses', 'vendorGroupOptions'));
	}

	public function update($templateFormId)
	{
		$templateForm = TemplateForm::find($templateFormId);

		$this->vendorPerformanceEvaluationFormForm->templateForm = $templateForm;

		$this->vendorPerformanceEvaluationFormForm->validate(Input::all());

		$templateForm->weightedNode->name = Input::get('name');
		$templateForm->weightedNode->save();

	    $templateForm->update(array(
	    	'contract_group_category_id' => Input::get("contract_group_category_id"),
	    	'project_status_id'          => Input::get('project_status_id'),
	    ));

	    return Redirect::route('vendorPerformanceEvaluation.templateForms');
	}

	public function approval($templateFormId)
	{
		$templateForm = TemplateForm::find($templateFormId);

		$data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($templateForm->weightedNode)];

		return View::make('vendor_performance_evaluation.template_forms.approval', compact('templateForm', 'data'));
	}

	public function approve($templateFormId)
	{
		$templateForm = TemplateForm::find($templateFormId);

		$templateForm->status_id = TemplateForm::STATUS_COMPLETED;

		$templateForm->save();

		\Flash::success(trans('forms.formFinalized'));

		return Redirect::back();
	}

	public function template($templateFormId)
	{
	    $templateForm = TemplateForm::find($templateFormId);

	    $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($templateForm->weightedNode)];

	    return View::make('vendor_performance_evaluation.template_forms.show', compact('templateForm', 'data'));
	}

	public function newRevision($templateFormId)
	{
        $vmGrade = VpeHelper::getCurrentVendorManagementGrade();
        if (empty($vmGrade)) {
            \Flash::error('Please set vendor management grade before creating new revision');
            return Redirect::back();
        }

        $templateForm = TemplateForm::find($templateFormId);
        if (empty($templateForm->vendor_management_grade_id)) {
            $templateForm->vendor_management_grade_id = $vmGrade->id;
            $templateForm->save();
            $templateForm = TemplateForm::find($templateFormId);
        }

        $clonedNode = $templateForm->weightedNode->clone();

	    $newForm = TemplateForm::create(array(
	        'contract_group_category_id' => $templateForm->contract_group_category_id,
	        'weighted_node_id'  		 => $clonedNode->id,
	        'revision' 					 => $templateForm->revision+1,
	        'project_status_id' 		 => $templateForm->project_status_id,
	        'original_form_id'  		 => $templateForm->original_form_id,
            'vendor_management_grade_id' => $vmGrade->id
	    ));

	    $templateForm->vendorManagementGrade->copyAndAttach($newForm, false);

	    return Redirect::route('vendorPerformanceEvaluation.templateForm.nodes', array($newForm->id, $clonedNode->id));
	}

	public function clone($templateFormId)
	{
        $vmGrade = VpeHelper::getCurrentVendorManagementGrade();
        if (empty($vmGrade)) {
            \Flash::error('Please set vendor management grade before creating new revision');
            return Redirect::back();
        }

        $templateForm = TemplateForm::find($templateFormId);
        if (empty($templateForm->vendor_management_grade_id)) {
            $templateForm->vendor_management_grade_id = $vmGrade->id;
            $templateForm->save();
            $templateForm = TemplateForm::find($templateFormId);
        }

	    $clonedNode = $templateForm->weightedNode->clone();

	    $clonedNode->name = $clonedNode->name . " " . trans('general.copy');

	    $clonedNode->save();

	    $newForm = TemplateForm::create(array(
	        'contract_group_category_id' => $templateForm->contract_group_category_id,
	        'weighted_node_id'  		 => $clonedNode->id,
	        'revision' 					 => 0,
	        'project_status_id' 		 => $templateForm->project_status_id,
            'vendor_management_grade_id' => $vmGrade->id
	    ));

	    \Flash::success(trans('forms.formCloned'));

	    return Redirect::route('vendorPerformanceEvaluation.templateForms.edit', array($newForm->id));
	}

	public function grade($templateFormId)
	{
	    $templateForm = TemplateForm::find($templateFormId);

	    $gradeTemplates = VendorManagementGrade::getGradeTemplates();

	    return View::make('vendor_performance_evaluation.template_forms.grade', [
	        'templateForm'       => $templateForm,
	        'gradeTemplates'     => $gradeTemplates,
	    ]);
	}

	public function updateGrade($templateFormId)
	{
	    $inputs  = Input::all();
	    
	    $this->vendorPreqSelectGradeTemplateForm->validate($inputs);

	    $templateForm = TemplateForm::find($templateFormId);

	    $vendorManagementGradeTemplate = VendorManagementGrade::find($inputs['grade_template']);

	    $vendorManagementGradeTemplate->copyAndAttach($templateForm);

	    \Flash::success(trans('vendorManagement.addedGrading'));

	    return Redirect::back();
	}
}
