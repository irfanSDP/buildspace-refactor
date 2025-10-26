<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\Helpers\StringOperations;
Use PCK\FormBuilder\DynamicForm;
use PCK\FormBuilder\ElementAttribute;
use PCK\FormBuilder\Elements\Element;
use PCK\FormBuilder\Elements\SystemModuleElement;
use PCK\FormBuilder\Elements\FileUpload;
use PCK\FormBuilder\ElementRejection;
use PCK\FormBuilder\FormColumnSection;

class DynamicFormSubmitForm extends CustomFormValidator
{
    public function formDataCleanUp($formData)
    {
        $cleanedFormData = [];

        foreach($formData as $key => $value)
        {
            if(!is_array($value))
            {
                $cleanedFormData[$key] = $value;
            }
            else
            {
                foreach($value as $v)
                {
                    if(is_null($v) || ($v == '')) continue;
                    
                    $cleanedFormData[$key][] = $v;
                }
            }
        }

        return $cleanedFormData;
    }

    protected function setRules($formData)
    {
        $section            = FormColumnSection::find($formData['sectionId']);
        $sectionElements    = $section->getAllFormElementIdsGroupedByType();
        $customFormElements = $sectionElements[Element::ELEMENT_TYPE_ID];
        $systemFormElements = $sectionElements[SystemModuleElement::ELEMENT_TYPE_ID];

        unset($formData['_token'], $formData['formId']);

        foreach($customFormElements as $elementId)
        {
            $element = Element::findById($elementId);

            if($element->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($element))) continue;

            if(get_class($element) == FileUpload::class) continue;

            $rulesString = $element->getValidationRulesString();

            if(!is_null($rulesString) && ($rulesString != ''))
            {
                $this->rules[Element::ELEMENT_TYPE_ID . '_' . $element->id] = $rulesString;
            }

            foreach($element->getValidationErrorMessage() as $key => $messages)
            {
                $this->messages[$key] = $messages;
            }
        }

        foreach($systemFormElements as $elementId)
        {
            $element = SystemModuleElement::find($elementId);

            if($element->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($element))) continue;

            $rulesString = $element->getValidationRulesString();

            if(!is_null($rulesString) && ($rulesString != ''))
            {
                $this->rules[SystemModuleElement::ELEMENT_TYPE_ID . '_' . $element->id] = $rulesString;
            }

            foreach($element->getValidationErrorMessage() as $key => $messages)
            {
                $this->messages[$key] = $messages;
            }
        }
    }

    public function postParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        $section            = FormColumnSection::find($formData['sectionId']);
        $sectionElements    = $section->getAllFormElementIdsGroupedByType();
        $customFormElements = $sectionElements[Element::ELEMENT_TYPE_ID];
        $systemFormElements = $sectionElements[SystemModuleElement::ELEMENT_TYPE_ID];
        
        foreach($customFormElements as $elementId)
        {
            $element = Element::findById($elementId);

            if((get_class($element) == FileUpload::class) && (ElementAttribute::findAttribute($element, 'required')))
            {
                if($element->attachments->count() == 0)
                {
                    $messageBag->add(Element::ELEMENT_TYPE_ID . '_' . $element->id, trans('formBuilder.filesRequired', ['label' => $element->label]));
                }
            }
            else
            {
                if($element->has_attachments && ElementAttribute::findAttribute($element, 'required') && ($element->attachments->count() == 0))
                {
                    $messageBag->add(Element::ELEMENT_TYPE_ID . '_' . $element->id, trans('formBuilder.filesRequired', ['label' => $element->label]));
                }
            }
        }

        foreach($systemFormElements as $elementId)
        {
            $element = SystemModuleElement::find($elementId);

            if($element->has_attachments && ElementAttribute::findAttribute($element, 'required') && ($element->attachments->count() == 0))
            {
                $messageBag->add(SystemModuleElement::ELEMENT_TYPE_ID . '_' . $element->id, trans('formBuilder.filesRequired', ['label' => $element->label]));
            }
        }

        return $messageBag;
    }
}

