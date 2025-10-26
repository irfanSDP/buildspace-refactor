<?php namespace PCK\FormBuilder\Elements;

use Carbon\Carbon;
use PCK\FormBuilder\FormElementMapping;
use PCK\FormBuilder\ElementAttribute;
use PCK\FormBuilder\ElementValue;
use PCK\FormBuilder\ElementRejection;

class DateTimePicker extends Element implements Elementable
{
    protected $validationRules = ['required'];

    const MODE_BOTH = 1;
    const MODE_DATE = 2;
    const MODE_TIME = 4;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $model)
        {
            $model->deleteRelatedModels();
        });
    }

    public static function getDateTimePickerModes()
    {
        return [
            self::MODE_BOTH => trans('formBuilder.dateAndTime'),
            self::MODE_DATE => trans('formBuilder.date'),
            self::MODE_TIME => trans('formBuilder.time'),
        ];
    }

    public static function getClassIdentifier()
    {
        return self::TYPE_DATE_TIME;
    }

    public function getValidationErrorMessage()
    {
        $name = self::ELEMENT_TYPE_ID . '_' . $this->id;
        
        $messages = [];
        $messages[$name . '.required'] = trans('formBuilder.elementisRequired', ['label' => $this->label]);

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

        if(isset($inputs['required']))
        {
            ElementAttribute::createNewAttribute($element, 'required', 'required');
        }

        ElementAttribute::createNewAttribute($element, 'mode', $inputs['mode']);

        return self::find($element->id);
    }

    public static function clone(self $originElement)
    {
        $newElement                     = new self();
        $newElement->parent_id          = $originElement->parent_id;
        $newElement->label              = $originElement->label;
        $newElement->instructions       = $originElement->instructions;
        $newElement->is_key_information = $originElement->is_key_information;
        $newElement->has_attachments    = $originElement->has_attachments;
        $newElement->priority           = $originElement->priority;
        $newElement->save();

        $newElement = self::find($newElement->id);

        foreach(ElementAttribute::getElementAttributes($originElement) as $name => $value)
        {
            ElementAttribute::createNewAttribute($newElement, $name, $value);
        }

        if($originElement->dynamicForm->isVendorSubmissionApproved() && !is_null($originElement->dynamicForm->origin_id))
        {
            $originElementSavedValueRecord = ElementValue::getSavedElementValueRecord($originElement);

            if($originElementSavedValueRecord)
            {
                ElementValue::createNewRecord($newElement, $originElementSavedValueRecord->value);
            }
        }

        $originElement->copyAttachmentsTo($newElement);

        return $newElement;
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

        ElementAttribute::updateAttribute($this, 'mode', $inputs['mode']);

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
            'element_type'             => self::ELEMENT_TYPE_ID,
            'mapping_id'               => FormElementMapping::getElementMappingByElement($this)->id,
            'attributes'               => [
                'name'     => self::ELEMENT_TYPE_ID . '_' . $this->id,
                'value'    => ElementValue::getSavedElementValue($this),
            ],
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
        $elementAttribute = ElementAttribute::findAttribute($this, 'mode');
        $mode = $elementAttribute->value;

        $value = null;

        switch($mode)
        {
            case self::MODE_BOTH:
                $value = Carbon::parse($savedElementValue)->format(\Config::get('dates.full_format'));
                break;
            case self::MODE_DATE:
                $value = Carbon::parse($savedElementValue)->format(\Config::get('dates.full_format_without_time'));
                break;
            case self::MODE_TIME:
                $value = Carbon::parse($savedElementValue)->format(\Config::get('dates.time_only'));
                break;
        }

        $duh = [
            'label'  => $this->label,
            'values' => ($savedElementValue != '') ? [$value] : [],
            'route_attachments' => route('form.column.section.element.attachments.list.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'attachments_count' => $this->attachments->count(),
            'enable_attachments' => $this->has_attachments,
        ];

        return $duh;
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

