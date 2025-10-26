<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\FormBuilder\DynamicForm;
use PCK\FormBuilder\FormColumn;
use PCK\FormBuilder\FormColumnSection;
use PCK\FormBuilder\FormElementMapping;
use PCK\FormBuilder\Elements\Element;
use PCK\FormBuilder\Elements\SystemModuleElement;

class FormBuilderFilters
{
    public function formReadyForSubmissionByVendor(Route $route)
    {
        $form                         = DynamicForm::find($route->getParameter('formId'));
        $formIsLatestRevision         = ($form->id == $form->getLatestFormRevision()->id);
        $formReadyForVendorSubmission = $formIsLatestRevision && $form->isFormDesignApproved();

        // form is approved and is latest approved revision form
        if(!$formReadyForVendorSubmission)
        {
            throw new InvalidAccessLevelException(trans('formBuilder.operationNotPossible'));
        }
    }

    public function formCanBeEdited(Route $route)
    {
        $form = DynamicForm::find($route->getParameter('formId'));

        if(!$form->isOpenForEditing())
        {
            throw new InvalidAccessLevelException(trans('formBuilder.operationNotPossible'));
        }
    }

    public function formCanBeEditedAjax(Route $route)
    {
        $form = DynamicForm::find($route->getParameter('formId'));

        if(!$form->isOpenForEditing())
        {
            return [
                'success' => false,
                'errors'  => trans('formBuilder.cannotBeEditedAtTheMoment'),
            ];
        }
    }

    public function formCanCreateRevision(Route $route)
    {
        $form = DynamicForm::find($route->getParameter('formId'));

        if(!$form->isFormDesignApproved())
        {
            return [
                'success' => false,
                'errors'  => trans('formBuilder.cannotCreateRevisionMsg'),
            ];
        }
    }

    public function columnCanBeEdited(Route $route)
    {
        $column = FormColumn::find($route->getParameter('columnId'));

        if(!$column->dynamicForm->isOpenForEditing())
        {
            return [
                'success' => false,
                'errors'  => trans('formBuilder.cannotBeEditedAtTheMoment'),
            ];
        }
    }

    public function sectionCanBeEdited(Route $route)
    {
        $section = FormColumnSection::find($route->getParameter('sectionId'));

        if(!$section->column->dynamicForm->isOpenForEditing())
        {
            throw new InvalidAccessLevelException(trans('formBuilder.operationNotPossible'));
        }
    }

    public function sectionCanBeEditedAjax(Route $route)
    {
        $section = FormColumnSection::find($route->getParameter('sectionId'));
        
        if(!$section->column->dynamicForm->isOpenForEditing())
        {
            return [
                'success' => false,
                'errors'  => trans('formBuilder.cannotBeEditedAtTheMoment'),
            ];
        }
    }

    public function elementCanBeEdited(Route $route)
    {
        $elementId   = $route->getParameter('elementId');
        $elementType = $route->getParameter('elementType');
        $mapping     = null;

        if($elementType == Element::ELEMENT_TYPE_ID)
        {
            $mapping = FormElementMapping::where('element_id', $elementId)->where('element_class', '!=', SystemModuleElement::class)->first();
        }
        else
        {
            $mapping = FormElementMapping::where('element_id', $elementId)->where('element_class', SystemModuleElement::class)->first();
        }

        if(!$mapping->section->column->dynamicForm->isOpenForEditing())
        {
            return [
                'success' => false,
                'errors'  => trans('formBuilder.cannotBeEditedAtTheMoment'),
            ];
        }
    }

    public function formCanSubmitForApproval(Route $route)
    {
        $form = DynamicForm::find($route->getParameter('formId'));

        if(!$form->isOpenForEditing())
        {
            throw new InvalidAccessLevelException(trans('formBuilder.operationNotPossible'));
        }
    }

    public function formIsBeingApproved(Route $route)
    {
        $form = DynamicForm::find($route->getParameter('formId'));

        if(!$form->isDesignPendingForApproval())
        {
            throw new InvalidAccessLevelException(trans('formBuilder.operationNotPossible'));
        }
    }
}

