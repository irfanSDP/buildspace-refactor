<?php namespace PCK\FormBuilder\Elements;

use PCK\FormBuilder\FormElementMapping;
use PCK\FormBuilder\ElementAttribute;
use PCK\FormBuilder\ElementValue;
use PCK\FormBuilder\ElementRejection;

class URLTextBox extends Element implements Elementable
{
    protected $validationRules = ['required', 'url'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $model)
        {
            $model->deleteRelatedModels();
        });
    }

    public static function getClassIdentifier()
    {
        return self::TYPE_URL;
    }

    public function getValidationRulesString()
    {
        $elementAttributes = ElementAttribute::getElementAttributes($this);
        $rules             = [];

        foreach($elementAttributes as $name => $value)
        {
            if(!in_array($name, $this->validationRules)) continue;

            if($name == 'required')
            {
                array_push($rules, $value);
            }
            else
            {
                array_push($rules, ($name . ':' . $value));
            }
        }

        array_push($rules, 'url');

        return implode('|', $rules);
    }

    public function getValidationErrorMessage()
    {
        $name = self::ELEMENT_TYPE_ID . '_' . $this->id;
        
        $messages = [];
        $messages[$name . '.required'] = trans('formBuilder.elementisRequired', ['label' => $this->label]);
        $messages[$name . '.url']      = trans('formBuilder.mustBeValidUrl', ['label' => $this->label]);

        return $messages;
    }

    public static function createNewElement($inputs)
    {
        $element                     = new self();
        $element->parent_id          = null;
        $element->label              = $inputs['label'];
        $element->instructions       = trim($inputs['instructions']);
        $element->is_key_information = isset($inputs['key_information']);
        $element->has_attachments    = isset($inputs['attachments']);
        $element->priority           = 0;
        $element->save();

        $element = self::find($element->id);

        if(isset($inputs['required']))
        {
            ElementAttribute::createNewAttribute($element, 'required', 'required');
        }

        return $element;
    }

    public static function clone(self $originElement)
    {
        $element                     = new self();
        $element->parent_id          = $originElement->parent_id;
        $element->label              = $originElement->label;
        $element->instructions       = $originElement->instructions;
        $element->is_key_information = $originElement->is_key_information;
        $element->has_attachments    = $originElement->has_attachments;
        $element->priority           = $originElement->priority;
        $element->save();

        $element = self::find($element->id);

        foreach(ElementAttribute::getElementAttributes($originElement) as $name => $value)
        {
            ElementAttribute::createNewAttribute($element, $name, $value);
        }

        if($originElement->dynamicForm->isVendorSubmissionApproved() && !is_null($originElement->dynamicForm->origin_id))
        {
            $originElementSavedValueRecord = ElementValue::getSavedElementValueRecord($originElement);

            if($originElementSavedValueRecord)
            {
                ElementValue::createNewRecord($element, $originElementSavedValueRecord->value);
            }
        }

        $originElement->copyAttachmentsTo($element);

        return $element;
    }

    public  function updateElement($inputs)
    {
        $this->label              = $inputs['label'];
        $this->instructions       = trim($inputs['instructions']);
        $this->is_key_information = isset($inputs['key_information']);
        $this->has_attachments    = isset($inputs['attachments']);
        $this->save();

        $element = self::find($this->id);

        if(isset($inputs['required']))
        {
            ElementAttribute::createNewAttribute($element, 'required', 'required');
        }
        else
        {
            ElementAttribute::deleteAttribute($element, 'required');
        }

        return $element;
    }

    public function getElementDetails()
    {
        $data = [
            'id'                       => $this->id,
            'label'                    => $this->label,
            'instructions'             => $this->instructions,
            'displayInstructions'      => nl2br($this->instructions),
            'is_key_information'       => $this->is_key_information,
            'has_attachments'          => $this->has_attachments,
            'class_identifier'         => self::getClassIdentifier(),
            'attributes'               => [
                'type'     => 'url',
                'name'     => strtolower(self::ELEMENT_TYPE_ID . '_' . $this->id),
                'value'    => ElementValue::getSavedElementValue($this),
            ],
            'element_type'             => self::ELEMENT_TYPE_ID,
            'mapping_id'               => FormElementMapping::getElementMappingByElement($this)->id,
            'route_getElement'         => route('form.column.section.element.details.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_update'             => route('form.column.section.element.update', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_delete'             => route('form.column.section.element.delete', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_get_rejection'      => route('element.rejection.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_save_rejection'     => route('element.rejection.save', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_delete_rejection'   => route('element.rejection.delete', [$this->id, self::ELEMENT_TYPE_ID]),
            'is_rejected'              => $this->isRejected(),
            'is_amended'               => $this->isAmended(),
            'route_attachment_count'   => route('form.column.section.element.attachments.count.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_attachment_list'    => route('form.column.section.element.attachments.list.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_upload_attachments' => route('form.column.section.element.attachments.update', [$this->id, self::ELEMENT_TYPE_ID]),
            'attachment_count'         => $this->attachments->count(),
        ];

        foreach(ElementAttribute::getElementAttributes($this) as $name => $value)
        {
            $data['attributes'][$name] = $value;
        }

        if($this->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($this)))
        {
            $data['attributes']['disabled'] = 'disabled';
        }

        return $data;
    }

    public function deleteRelatedModels()
    {
        FormElementMapping::deleteElementMapping($this);
        ElementAttribute::purge($this);
        ElementRejection::deleteRecord($this);
        ElementValue::purgeElementValues($this);
    }

    public function saveFormValues($value)
    {
        ElementValue::syncElementValue($this, trim($value));
    }

    public function getSavedValuesDisplay()
    {
        $savedElementValue = ElementValue::getSavedElementValue($this);

        return [
            'label'  => $this->label,
            'values' => ($savedElementValue != '') ? [$savedElementValue] : [],
            'route_attachments' => route('form.column.section.element.attachments.list.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'attachments_count' => $this->attachments->count(),
            'enable_attachments' => $this->has_attachments,
        ];
    }

    public function hasValuesChanged($newValue)
    {
        $originalValue = ElementValue::getSavedElementValue($this);

        if(trim($originalValue) == trim($newValue))
        {
            return false;
        }

        return true;
    }

    public function isFilled()
    {
        $savedElementValue = ElementValue::getSavedElementValue($this);
        
        return ($savedElementValue != '');
    }
}

