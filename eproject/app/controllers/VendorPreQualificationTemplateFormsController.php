<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorPreQualification\TemplateForm;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;
use PCK\Forms\WeightedNodeForm;
use PCK\Verifier\VerifierRepository;
use PCK\Verifier\Verifier;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;
use PCK\ModuleParameters\VendorManagement\VendorManagementGradeLevel;
use PCK\Forms\VendorPrequalificationSelectGradeTemplateForm;
use PCK\Forms\VendorPreQualificationTemplateFormForm;
use PCK\Exceptions\ValidationException;
use PCK\Helpers\DBTransaction;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\Base\Helpers;
use PCK\VendorManagement\VendorManagementUserPermission;

class VendorPreQualificationTemplateFormsController extends \BaseController {

    protected $weightedNodeForm;
    protected $verifierRepository;
    protected $vendorPreqSelectGradeTemplateForm;
    protected $vendorPreQualificationTemplateFormForm;
    protected $weightedNodeRepository;

    public function __construct(VendorPreQualificationTemplateFormForm $vendorPreQualificationTemplateFormForm, WeightedNodeForm $weightedNodeForm, VerifierRepository $verifierRepository, VendorPrequalificationSelectGradeTemplateForm $vendorPreqSelectGradeTemplateForm, WeightedNodeRepository $weightedNodeRepository)
    {
        $this->weightedNodeForm                       = $weightedNodeForm;
        $this->verifierRepository                     = $verifierRepository;
        $this->vendorPreqSelectGradeTemplateForm      = $vendorPreqSelectGradeTemplateForm;
        $this->vendorPreQualificationTemplateFormForm = $vendorPreQualificationTemplateFormForm;
        $this->weightedNodeRepository                 = $weightedNodeRepository;
    }

    public function createForm($vendorGroupId, $vendorWorkCategoryId)
    {
        $vendorGroup = ContractGroupCategory::find($vendorGroupId);

        $vendorWorkCategory = VendorWorkCategory::find($vendorWorkCategoryId);

        return View::make('vendor_pre_qualification.forms.create', compact('vendorGroup', 'vendorWorkCategory'));
    }

    public function storeForm($vendorGroupId, $vendorWorkCategoryId)
    {
        $this->vendorPreQualificationTemplateFormForm->validate(Input::all());

        $vendorWorkCategory = VendorWorkCategory::find($vendorWorkCategoryId);

        $weightedNode = WeightedNode::create(array(
            'name' => Input::get('name'),
        ));

        $form = TemplateForm::create(array(
            'vendor_work_category_id' => $vendorWorkCategoryId,
            'weighted_node_id' => $weightedNode->id,
        ));

        return Redirect::route('vendorPreQualification.formLibrary.form.node', array($vendorGroupId, $vendorWorkCategoryId, $form->weighted_node_id));
    }

    public function cloneForm($vendorGroupId, $vendorWorkCategoryId)
    {
        $vendorGroup = ContractGroupCategory::find($vendorGroupId);

        $vendorWorkCategory = VendorWorkCategory::find($vendorWorkCategoryId);

        // Template forms
        $templateForms = array();

        $relevantWorkCategoryIds = \DB::table('vendor_categories')
            ->join('vendor_category_vendor_work_category', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
            ->where('vendor_categories.contract_group_category_id', '=', $vendorGroupId)
            ->lists('vendor_work_category_id');

        $vendorWorkCategories = VendorWorkCategory::whereIn('id', $relevantWorkCategoryIds)
            ->where('hidden', '=', false)
            ->orderBy('name', 'asc')
            ->get();

        foreach($vendorWorkCategories as $r)
        {
            $form = TemplateForm::getCurrentEditingForm($r->id);

            if (! is_null($form)) {
                $templateForms[] = array(
                    'id' => $form->id,
                    'name' => $form->weightedNode->name
                );
            }
        }

        return View::make('vendor_pre_qualification.forms.clone', compact('vendorGroup', 'vendorWorkCategory', 'templateForms'));
    }

    public function saveCloneForm($vendorGroupId, $vendorWorkCategoryId)
    {
        $this->vendorPreQualificationTemplateFormForm->validate(Input::all());

        $templateForm = TemplateForm::find(Input::get('template_form'));
        if (! $templateForm) {
            return Redirect::back();
        }

        $clonedNode = $templateForm->weightedNode->clone();

        $clonedNode->name = Input::get('name');
        $clonedNode->save();

        $newForm = TemplateForm::create(array(
            'vendor_work_category_id' => $vendorWorkCategoryId,
            'weighted_node_id' => $clonedNode->id,
        ));

        return Redirect::route('vendorPreQualification.formLibrary.form.node', array($vendorGroupId, $vendorWorkCategoryId, $newForm->weighted_node_id));
    }

    public function editForm($vendorGroupId, $vendorWorkCategoryId, $formId)
    {
        $vendorGroup = ContractGroupCategory::find($vendorGroupId);

        $vendorWorkCategory = VendorWorkCategory::find($vendorWorkCategoryId);

        $form = TemplateForm::find($formId);

        return View::make('vendor_pre_qualification.forms.edit', compact('vendorGroup', 'vendorWorkCategory', 'form'));
    }

    public function updateForm($vendorGroupId, $vendorWorkCategoryId, $formId)
    {
        $this->vendorPreQualificationTemplateFormForm->validate(Input::all());

        $vendorWorkCategory = VendorWorkCategory::find($vendorWorkCategoryId);

        $form = TemplateForm::find($formId);

        $form->weightedNode->name = Input::get('name');

        $form->weightedNode->save();

        return Redirect::route('vendorPreQualification.formLibrary.vendorWorkCategories.index', array($vendorGroupId, $vendorWorkCategoryId, $form->id));
    }

    public function index($vendorGroupId, $vendorWorkCategoryId, $parentNodeId)
    {
        $templateForm = TemplateForm::getCurrentEditingForm($vendorWorkCategoryId);

        $parentNode = WeightedNode::find($parentNodeId);

        $ancestors = [];

        foreach($parentNode->ancestorsAndSelf()->orderBy('lft', 'asc')->get() as $ancestorNode)
        {
            $ancestors[] = [
                'name' => $ancestorNode->name,
                'route' => route('vendorPreQualification.formLibrary.form.node', array($vendorGroupId, $vendorWorkCategoryId, $ancestorNode->id)),
            ];
        }

        $vendorGroup = ContractGroupCategory::find($vendorGroupId);

        $vendorWorkCategory = VendorWorkCategory::find($vendorWorkCategoryId);

        $parentNode = WeightedNode::find($parentNodeId);

        return View::make('vendor_pre_qualification.nodes.index', compact('vendorGroup', 'vendorWorkCategory', 'templateForm', 'parentNode', 'ancestors'));
    }

    public function list($vendorGroupId, $vendorWorkCategoryId, $parentNodeId)
    {
        $templateForm = TemplateForm::getCurrentEditingForm($vendorWorkCategoryId);

        $data = [];

        $parentNode = WeightedNode::find($parentNodeId);

        $totalWeight = $parentNode->children()->sum('weight');

        $isDraft = $templateForm->isDraft();

        foreach($parentNode->children->sortBy('name')->sortByDesc('weight') as $child)
        {
            $data[] = [
                'id'                             => $child->id,
                'name'                           => $child->name,
                'weight'                         => $child->weight,
                'percentage'                     => round(Helpers::divide($child->weight, $totalWeight)*100,2),
                'route:next'                     => route('vendorPreQualification.formLibrary.form.node', array($vendorGroupId, $vendorWorkCategoryId, $child->id)),
                'can_go_next'                    => $child->scores->isEmpty(),
                'route:scores'                   => route('vendorPreQualification.formLibrary.form.node.scores', array($vendorGroupId, $vendorWorkCategoryId, $child->id)),
                'can_add_score'                  => $child->children->isEmpty(),
                'can_edit'                       => $isDraft,
                'deletable'                      => $isDraft,
                'route:delete'                   => route('vendorPreQualification.formLibrary.form.node.delete', array($vendorGroupId, $vendorWorkCategoryId, $parentNodeId, $child->id)),
                'amendments_required'            => $child->amendments_required,
                'descendent_amendments_required' => $child->descendentAmendmentRequired(),
                'remarks'                        => $child->remarks,
            ];
        }

        return Response::json($data);
    }

    public function storeOrUpdate($vendorGroupId, $vendorWorkCategoryId, $parentNodeId)
    {
        $request = Request::instance();

        $this->weightedNodeForm->validate($input = $request->all());

        if($this->weightedNodeForm->success)
        {
            if($input['node_id'] == -1)
            {
                $parentNode = WeightedNode::find($parentNodeId);

                unset($input['node_id']);

                $input['root_id']   = $parentNode->root_id;
                $input['parent_id'] = $parentNode->id;

                $newNode = WeightedNode::create($input);

                $newNode->makeChildOf($parentNode);
            }
            else
            {
                $node = WeightedNode::find($input['node_id']);

                unset($input['node_id']);

                $input['amendments_required'] = false;

                $node->update($input);
            }
        }

        return array(
            'success' => $this->weightedNodeForm->success,
            'errors'  => $this->weightedNodeForm->getErrorMessages(),
        );
    }

    public function destroy($vendorGroupId, $vendorWorkCategoryId, $parentNodeId, $nodeId)
    {
        try
        {
            $node = WeightedNode::find($nodeId);
            $node->delete();

            \Flash::success(trans('forms.deleted'));
        }
        catch(\Exception $e)
        {
            \Flash::error(trans('forms.cannotBeDeleted:inUse'));
        }

        return Redirect::route('vendorPreQualification.formLibrary.form.node', array($vendorGroupId, $vendorWorkCategoryId, $parentNodeId));
    }

    public function approval($vendorGroupId, $vendorWorkCategoryId)
    {
        $vendorGroup = ContractGroupCategory::find($vendorGroupId);

        $vendorWorkCategory = VendorWorkCategory::find($vendorWorkCategoryId);

        $templateForm = TemplateForm::getCurrentEditingForm($vendorWorkCategoryId);

        $form = $templateForm->weightedNode;

        $flatData = $this->weightedNodeRepository->getWeightedNodeFlatDataStructure($form);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        $editable = $templateForm->isPendingVerification() && Verifier::isCurrentVerifier(\Confide::user(), $templateForm);

        $verifiers = VendorManagementUserPermission::getUsers(VendorManagementUserPermission::TYPE_FORM_TEMPLATES);

        return View::make('vendor_pre_qualification.form_approval', compact('vendorGroup', 'vendorWorkCategory', 'templateForm', 'form', 'data', 'flatData', 'editable', 'verifiers'));
    }

    public function submitForApproval($vendorGroupId, $vendorWorkCategoryId)
    {
        $templateForm = TemplateForm::getCurrentEditingForm($vendorWorkCategoryId);

        $templateForm->status_id = TemplateForm::STATUS_PENDING_VERIFICATION;

        $templateForm->save();

        $this->verifierRepository->setVerifiers(Input::get('verifiers') ?? [], $templateForm);
        $this->verifierRepository->executeFollowUp($templateForm);

        return Redirect::back();
    }

    public function verify($vendorGroupId, $vendorWorkCategoryId)
    {
        if(Input::get('submit') == 'reject' || Input::get('submit') == 'save')
        {
            WeightedNode::updateRemarks(Input::get('node_remarks'));
            WeightedNodeScore::updateRemarks(Input::get('score_remarks'));
        }
        elseif(Input::get('submit') == 'approve')
        {
            WeightedNode::flushRemarks(Input::get('node_remarks'));
            WeightedNodeScore::flushRemarks(Input::get('score_remarks'));
        }

        $templateForm = TemplateForm::getCurrentEditingForm($vendorWorkCategoryId);

        if(Input::get('submit') == 'reject')
        {
            $this->verifierRepository->approve($templateForm, false);
        }
        elseif(Input::get('submit') == 'approve')
        {
            $this->verifierRepository->approve($templateForm, true);
        }

        return Redirect::back();
    }

    public function template($vendorGroupId, $vendorWorkCategoryId)
    {
        $vendorGroup = ContractGroupCategory::find($vendorGroupId);

        $vendorWorkCategory = VendorWorkCategory::find($vendorWorkCategoryId);

        $templateForm = TemplateForm::getTemplateForm($vendorWorkCategoryId);

        $form = $templateForm->weightedNode;

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        return View::make('vendor_pre_qualification.form_view', compact('vendorGroup', 'vendorWorkCategory', 'templateForm', 'form', 'data'));
    }

    public function newRevision($vendorGroupId, $vendorWorkCategoryId)
    {
        $templateForm = TemplateForm::getTemplateForm($vendorWorkCategoryId);

        if($templateForm && $templateForm->weightedNode)
        {
            $clonedNode = $templateForm->weightedNode->clone();

            $newForm = TemplateForm::create(array(
                'vendor_work_category_id' => $vendorWorkCategoryId,
                'weighted_node_id' => $clonedNode->id,
                'revision' => $templateForm->revision+1,
            ));
        }

        return Redirect::route('vendorPreQualification.formLibrary.form.node', array($vendorGroupId, $vendorWorkCategoryId, $newForm->weighted_node_id));
    }
}
