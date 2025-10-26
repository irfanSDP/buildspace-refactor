<?php namespace PCK\FormBuilder;

use Carbon\Carbon;
use PCK\Users\User;
use PCK\FormBuilder\Elements\Element;
use PCK\FormBuilder\Elements\SystemModuleElement;
use PCK\FormBuilder\ElementValue;
use PCK\Helpers\StringOperations;
use PCK\FormBuilder\Elements\RadioBox;
use PCK\FormBuilder\Elements\CheckBox;
use PCK\FormBuilder\ElementRejection;
use PCK\FormBuilder\FormColumnSection;

class DynamicFormRepository
{
    public function getAllFormTemplates($moduleIdentifier)
    {
        $formTemplates = [];

        foreach(DynamicForm::where('is_template', true)->where('revision', 0)->where('module_identifier', $moduleIdentifier)->orderBy('id', 'ASC')->get() as $formTemplate)
        {
            $latestRevisionForm = DynamicForm::where('root_id', $formTemplate->root_id)->orderBy('revision', 'DESC')->first();

            $data = [
                'id'                 => $latestRevisionForm->id,
                'name'               => $latestRevisionForm->name,
                'revision'           => $latestRevisionForm->revision,
                'status'             => $latestRevisionForm->status,
                'route_new_revision' => route('form.new.revision.create', [$latestRevisionForm->id]),
                'route_clone'        => route('form.clone', [$latestRevisionForm->id]),
            ];

            if($latestRevisionForm->isOpenForEditing())
            {
                $data['route_show']   = route('form.designer.show', [$latestRevisionForm->id]);
                $data['route_update'] = route('form.update', [$latestRevisionForm->id]);
                $data['route_delete'] = route('form.delete', [$latestRevisionForm->id]);
            }
    
            if($latestRevisionForm->isDesignPendingForApproval())
            {
                $data['route_show'] = route('form.designer.show', [$latestRevisionForm->id]);
            }

            if($latestRevisionForm->isFormDesignApproved())
            {
                $data['route_show'] = route('form.designer.show', [$latestRevisionForm->id]);
            }

            if($latestRevisionForm->isRevisedForm())
            {
                $data['route_previous_revision_forms'] = route('previous.revision.forms.get', [$latestRevisionForm->id]);
            }

            array_push($formTemplates, $data);
        }

        return $formTemplates;
    }

    public function store($inputs)
    {
        // create template forms
        return DynamicForm::createNewForm($inputs['name'], $inputs['moduleIdentifier'], false);
    }

    public function createNewRevision(DynamicForm $originForm)
    {
        return DynamicForm::createNewRevisedForm($originForm);
    }

    public function update($formId, $inputs)
    {
        $form       = DynamicForm::find($formId);
        $form->name = $inputs['name'];
        $form->save();

        return DynamicForm::find($formId);
    }

    public function clone(DynamicForm $originForm, $inputs)
    {
        // clone template forms
        $form = $originForm->clone($inputs['name'], $inputs['moduleIdentifier'], false);
    }

    public function saveFormInputs(DynamicForm $form, $inputs, $isVendor)
    {
        unset($inputs['_token']);
        $submittedElementIds[Element::ELEMENT_TYPE_ID]             = [];
        $submittedElementIds[SystemModuleElement::ELEMENT_TYPE_ID] = [];

        $keyElementValuesChanged = false;

        foreach($inputs as $key => $value)
        {
            if(StringOperations::endsWith($key, 'other')) continue;

            $splitStrings  = explode('_', $key);
            $elementMarker = $splitStrings[0];
            $elementId     = $splitStrings[1];
            
            if(StringOperations::startsWith($key, Element::ELEMENT_TYPE_ID))
            {
                $element = Element::findById($elementId);

                // radiobox and checkbox might have 'other' option
                if(in_array(get_class($element), [RadioBox::class, CheckBox::class]))
                {
                    $otherOptionKey   = Element::ELEMENT_TYPE_ID . '_' . $element->id . '_other';
                    $otherOptionValue = array_key_exists($otherOptionKey, $inputs) ? $inputs[$otherOptionKey] : null;

                    if($form->isRenewalForm() && $element->isKeyInformation() && $element->hasValuesChanged($value, $otherOptionValue) && (!$keyElementValuesChanged))
                    {
                        $keyElementValuesChanged = true;

                        $form->renewal_approval_required = true;
                        $form->save();
                    }
                    
                    $element->saveFormValues($value, $otherOptionValue);
                }
                else
                {
                    if($form->isRenewalForm() && $element->isKeyInformation() && $element->hasValuesChanged($value) && (!$keyElementValuesChanged))
                    {
                        $keyElementValuesChanged = true;

                        $form->renewal_approval_required = true;
                        $form->save();
                    }

                    $element->saveFormValues($value);
                }

                if($isVendor)
                {
                    ElementRejection::markAsAmeded($element);
                }

                array_push($submittedElementIds[Element::ELEMENT_TYPE_ID], $element->id);
            }
            else
            {
                $systemModuleElement = SystemModuleElement::find($elementId);

                if($form->isRenewalForm() && $systemModuleElement->isKeyInformation() && $systemModuleElement->hasValuesChanged($value) && (!$keyElementValuesChanged))
                {
                    $keyElementValuesChanged = true;

                    $form->renewal_approval_required = true;
                    $form->save();
                }

                $systemModuleElement->saveFormValues($value);

                if($isVendor)
                {
                    ElementRejection::markAsAmeded($systemModuleElement);
                }

                array_push($submittedElementIds[SystemModuleElement::ELEMENT_TYPE_ID], $systemModuleElement->id);
            }
        }

        // clear up old data
        // situation where checkboxes and dropdows have no options selected (nothing is sent to the server), so syncing doesn't work, manual deletion required
        $formElementIdsGroupedByType = $form->getAllFormElementIdsGroupedByType();

        $customElementIdsToPurgeValues       = array_diff($formElementIdsGroupedByType[Element::ELEMENT_TYPE_ID], $submittedElementIds[Element::ELEMENT_TYPE_ID]);
        $systemModuleElementIdsToPurgeValues = array_diff($formElementIdsGroupedByType[SystemModuleElement::ELEMENT_TYPE_ID], $submittedElementIds[SystemModuleElement::ELEMENT_TYPE_ID]);

        foreach($customElementIdsToPurgeValues as $elementId)
        {
            $element = Element::findById($elementId);

            if($element->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($element))) continue;

            ElementValue::purgeElementValues($element);
        }

        foreach($systemModuleElementIdsToPurgeValues as $elementId)
        {
            $element = SystemModuleElement::find($elementId);

            if($element->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($element))) continue;

            ElementValue::purgeElementValues($element);
        }
    }

    public function saveSectionInputs(FormColumnSection $section, $inputs, $isVendor)
    {
        unset($inputs['_token']);
        $submittedElementIds[Element::ELEMENT_TYPE_ID]             = [];
        $submittedElementIds[SystemModuleElement::ELEMENT_TYPE_ID] = [];

        $keyElementValuesChanged = false;

        $form = $section->column->dynamicForm;

        foreach($inputs as $key => $value)
        {
            if(StringOperations::endsWith($key, 'other')) continue;

            $splitStrings  = explode('_', $key);
            $elementMarker = $splitStrings[0];
            $elementId     = $splitStrings[1];
            
            if(StringOperations::startsWith($key, Element::ELEMENT_TYPE_ID))
            {
                $element = Element::findById($elementId);

                // radiobox and checkbox might have 'other' option
                if(in_array(get_class($element), [RadioBox::class, CheckBox::class]))
                {
                    $otherOptionKey   = Element::ELEMENT_TYPE_ID . '_' . $element->id . '_other';
                    $otherOptionValue = array_key_exists($otherOptionKey, $inputs) ? $inputs[$otherOptionKey] : null;

                    if($form->isRenewalForm() && $element->isKeyInformation() && $element->hasValuesChanged($value, $otherOptionValue) && (!$keyElementValuesChanged))
                    {
                        $keyElementValuesChanged = true;

                        $form->renewal_approval_required = true;
                        $form->save();
                    }
                    
                    $element->saveFormValues($value, $otherOptionValue);
                }
                else
                {
                    if($form->isRenewalForm() && $element->isKeyInformation() && $element->hasValuesChanged($value) && (!$keyElementValuesChanged))
                    {
                        $keyElementValuesChanged = true;

                        $form->renewal_approval_required = true;
                        $form->save();
                    }

                    $element->saveFormValues($value);
                }

                if($isVendor)
                {
                    ElementRejection::markAsAmeded($element);
                }

                array_push($submittedElementIds[Element::ELEMENT_TYPE_ID], $element->id);
            }
            else
            {
                $systemModuleElement = SystemModuleElement::find($elementId);

                if($form->isRenewalForm() && $systemModuleElement->isKeyInformation() && $systemModuleElement->hasValuesChanged($value) && (!$keyElementValuesChanged))
                {
                    $keyElementValuesChanged = true;

                    $form->renewal_approval_required = true;
                    $form->save();
                }

                $systemModuleElement->saveFormValues($value);

                if($isVendor)
                {
                    ElementRejection::markAsAmeded($systemModuleElement);
                }

                array_push($submittedElementIds[SystemModuleElement::ELEMENT_TYPE_ID], $systemModuleElement->id);
            }
        }

        // clear up old data
        // situation where checkboxes and dropdows have no options selected (nothing is sent to the server), so syncing doesn't work, manual deletion required
        $formElementIdsGroupedByType = $section->getAllFormElementIdsGroupedByType();

        $customElementIdsToPurgeValues       = array_diff($formElementIdsGroupedByType[Element::ELEMENT_TYPE_ID], $submittedElementIds[Element::ELEMENT_TYPE_ID]);
        $systemModuleElementIdsToPurgeValues = array_diff($formElementIdsGroupedByType[SystemModuleElement::ELEMENT_TYPE_ID], $submittedElementIds[SystemModuleElement::ELEMENT_TYPE_ID]);

        foreach($customElementIdsToPurgeValues as $elementId)
        {
            $element = Element::findById($elementId);

            if($element->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($element))) continue;

            ElementValue::purgeElementValues($element);
        }

        foreach($systemModuleElementIdsToPurgeValues as $elementId)
        {
            $element = SystemModuleElement::find($elementId);

            if($element->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($element))) continue;

            ElementValue::purgeElementValues($element);
        }
    }

    public function getPreviousRevisionForms(DynamicForm $form)
    {
        $previousForms = [];

        foreach(DynamicForm::where('root_id', $form->root_id)->where('revision', '<', $form->revision)->orderBy('revision', 'DESC')->get() as $previousForm)
        {
            array_push($previousForms, [
                'id'         => $previousForm->id,
                'name'       => $previousForm->name,
                'revision'   => $previousForm->revision,
                'route_show' => route('form.designer.show', [$previousForm->id]),
            ]);
        }

        return $previousForms;
    }
}

