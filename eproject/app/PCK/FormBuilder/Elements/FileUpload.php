<?php namespace PCK\FormBuilder\Elements;

use PCK\FormBuilder\FormElementMapping;
use PCK\FormBuilder\ElementAttribute;
use PCK\FormBuilder\ElementValue;
use PCK\FormBuilder\ElementRejection;

class FileUpload extends Element implements Elementable
{
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
        return self::TYPE_FILE_UPLOAD;
    }

    public static function createNewElement($inputs)
    {
        $element                     = new self();
        $element->parent_id          = null;
        $element->label              = $inputs['label'];
        $element->instructions       = trim($inputs['instructions']);
        $element->is_key_information = isset($inputs['key_information']);
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
        $element->priority           = $originElement->priority;
        $element->save();

        $element = self::find($element->id);

        foreach(ElementAttribute::getElementAttributes($originElement) as $name => $value)
        {
            ElementAttribute::createNewAttribute($element, $name, $value);
        }

        $originElement->copyAttachmentsTo($element);

        return $element;
    }

    public function updateElement($inputs)
    {
        $this->label              = $inputs['label'];
        $this->instructions       = trim($inputs['instructions']);
        $this->is_key_information = isset($inputs['key_information']);
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
            'class_identifier'         => self::getClassIdentifier(),
            'element_type'             => self::ELEMENT_TYPE_ID,
            'attributes'               => [
                'name' => strtolower(self::ELEMENT_TYPE_ID . '_' . $this->id),
            ],
            'mapping_id'               => FormElementMapping::getElementMappingByElement($this)->id,
            'attachment_count'         => $this->attachments->count(),
            'upload_button_name'       => strtolower(self::ELEMENT_TYPE_ID . '_' . $this->id),
            'route_getElement'         => route('form.column.section.element.details.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_update'             => route('form.column.section.element.update', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_delete'             => route('form.column.section.element.delete', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_attachment_count'   => route('form.column.section.element.attachments.count.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_attachment_list'    => route('form.column.section.element.attachments.list.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_upload_attachments' => route('form.column.section.element.attachments.update', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_get_rejection'      => route('element.rejection.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_save_rejection'     => route('element.rejection.save', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_delete_rejection'   => route('element.rejection.delete', [$this->id, self::ELEMENT_TYPE_ID]),
            'is_rejected'              => $this->isRejected(),
            'is_amended'               => $this->isAmended(),
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
    }

    public function getSavedValuesDisplay()
    {
        return [
            'label'  => $this->label,
            'attachments' => [
                'attachments_count' => $this->attachments->count(),
                'route_attachments' => route('form.column.section.element.attachments.list.get', [$this->id, self::ELEMENT_TYPE_ID]),
            ],
        ];
    }

    public function hasValuesChanged($newValue)
    {
        return false;
    }
}

