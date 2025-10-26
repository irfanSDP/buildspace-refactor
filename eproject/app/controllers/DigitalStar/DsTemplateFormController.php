<?php

namespace DigitalStar;

use Input;
use PCK\DigitalStar\Forms\DsTemplateFormForm;
use PCK\DigitalStar\TemplateForm\DsTemplateForm;
use PCK\Forms\VendorPrequalificationSelectGradeTemplateForm;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeRepository;
use Redirect;
use View;

class DsTemplateFormController extends \BaseController
{
    protected $weightedNodeRepository;
    protected $dsTemplateFormForm;
    protected $vendorPreqSelectGradeTemplateForm;

    public function __construct(
        WeightedNodeRepository $weightedNodeRepository,
        DsTemplateFormForm $dsTemplateFormForm,
        VendorPrequalificationSelectGradeTemplateForm $vendorPreqSelectGradeTemplateForm
    ) {
        $this->weightedNodeRepository = $weightedNodeRepository;
        $this->dsTemplateFormForm = $dsTemplateFormForm;
        $this->vendorPreqSelectGradeTemplateForm = $vendorPreqSelectGradeTemplateForm;
    }

    public function index()
    {
        $data = [];

        $forms = DsTemplateForm::orderBy('id', 'desc')->get();

        foreach ($forms as $form) {
            $currentEditingForm = DsTemplateForm::getCurrentEditingForm($form->original_form_id);

            $templateForm = DsTemplateForm::getTemplateForm($form->original_form_id);

            $data[] = [
                'id' => $form->id,
                'name' => $currentEditingForm->weightedNode->name,
                'status' => DsTemplateForm::getStatusText($currentEditingForm->status_id),
                'route:form' => route('digital-star.templateForm.nodes', array($currentEditingForm->id, $currentEditingForm->weighted_node_id)),
                'route:edit' => $currentEditingForm->isDraft() ? route('digital-star.templateForm.edit', array($currentEditingForm->id)) : null,
                'route:approval' => $currentEditingForm->isDraft() ? route('digital-star.templateForm.approval', array($currentEditingForm->id)) : null,
                'route:template' => !is_null($templateForm) ? route('digital-star.templateForm.template', array($templateForm->id)) : null,
                'route:clone' => route('digital-star.templateForm.clone', array($currentEditingForm->id)),
            ];
        }

        return View::make('digital_star.template_forms.index', compact('data'));
    }

    public function create()
    {
        return View::make('digital_star.template_forms.create');
    }

    public function store()
    {
        $this->dsTemplateFormForm->validate(Input::all());

        $weightedNode = WeightedNode::create(array(
            'name' => Input::get('name'),
        ));

        $form = DsTemplateForm::create(array(
            'weighted_node_id' => $weightedNode->id,
        ));

        return Redirect::route('digital-star.templateForm.nodes', array($form->id, $weightedNode->id));
    }

    public function edit($templateFormId)
    {
        $templateForm = DsTemplateForm::find($templateFormId);
        $templateForm->name = $templateForm->weightedNode->name;

        return View::make('digital_star.template_forms.edit', compact('templateForm'));
    }

    public function update($templateFormId)
    {
        $templateForm = DsTemplateForm::find($templateFormId);

        $this->dsTemplateFormForm->templateForm = $templateForm;

        $this->dsTemplateFormForm->validate(Input::all());

        $templateForm->weightedNode->name = Input::get('name');
        $templateForm->weightedNode->save();

        return Redirect::route('digital-star.templateForm');
    }

    public function approval($templateFormId)
    {
        $templateForm = DsTemplateForm::find($templateFormId);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($templateForm->weightedNode)];

        return View::make('digital_star.template_forms.approval', compact('templateForm', 'data'));
    }

    public function approve($templateFormId)
    {
        $templateForm = DsTemplateForm::find($templateFormId);
        $templateForm->status_id = DsTemplateForm::STATUS_COMPLETED;
        $templateForm->save();

        \Flash::success(trans('forms.formFinalized'));

        return Redirect::back();
    }

    public function template($templateFormId)
    {
        $templateForm = DsTemplateForm::find($templateFormId);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($templateForm->weightedNode)];

        return View::make('digital_star.template_forms.show', compact('templateForm', 'data'));
    }

    public function newRevision($templateFormId)
    {
        $templateForm = DsTemplateForm::find($templateFormId);

        $clonedNode = $templateForm->weightedNode->clone();

        $newForm = DsTemplateForm::create(array(
            'weighted_node_id' => $clonedNode->id,
            'revision' => $templateForm->revision + 1,
            'original_form_id' => $templateForm->original_form_id,
        ));

        return Redirect::route('digital-star.templateForm.nodes', array($newForm->id, $clonedNode->id));
    }

    public function clone($templateFormId)
    {
        $templateForm = DsTemplateForm::find($templateFormId);

        $clonedNode = $templateForm->weightedNode->clone();
        $clonedNode->name = $clonedNode->name . ' ' . trans('general.copy');
        $clonedNode->save();

        $newForm = DsTemplateForm::create(array(
            'weighted_node_id' => $clonedNode->id,
            'revision' => 0,
        ));

        \Flash::success(trans('forms.formCloned'));

        return Redirect::route('digital-star.templateForm.edit', array($newForm->id));
    }
}
