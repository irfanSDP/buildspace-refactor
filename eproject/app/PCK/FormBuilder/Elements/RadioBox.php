<?php namespace PCK\FormBuilder\Elements;

use Illuminate\Support\Facades\DB;
use PCK\FormBuilder\ElementAttribute;
use PCK\FormBuilder\FormColumnSection;
use PCK\FormBuilder\FormElementMapping;
use PCK\FormBuilder\ElementValue;
use PCK\FormBuilder\AdditionalElementValue;
use PCK\FormBuilder\ElementRejection;

class RadioBox extends Element implements Elementable
{
    protected $validationRules = ['required'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $model)
        {
            if($model->isParent())
            {
                $model->deleteRelatedModels();
            }

            if($model->isChild())
            {
                ElementAttribute::purge($model);
            }
        });

        static::deleted(function (self $model)
        {
            if($model->isChild())
            {
                self::updateChildrenPriority($model);
            }
        });
    }

    public function isParent()
    {
        return is_null($this->parent_id);
    }

    public function isChild()
    {
        return !is_null($this->parent_id);
    }

    public function isOtherOption()
    {
        return $this->is_other_option;
    }

    public static function getClassIdentifier()
    {
        return self::TYPE_RADIO;
    }

    public function getValidationErrorMessage()
    {
        $name = self::ELEMENT_TYPE_ID . '_' . $this->id;
        
        $messages                      = [];
        $messages[$name . '.required'] = trans('formBuilder.elementisRequired', ['label' => $this->label]);

        return $messages;
    }

    public static function createNewElement($inputs)
    {
        $parent                     = new self();
        $parent->parent_id          = null;
        $parent->label              = $inputs['label'];
        $parent->instructions       = trim($inputs['instructions']);
        $parent->is_key_information = isset($inputs['key_information']);
        $parent->has_attachments    = isset($inputs['attachments']);
        $parent->priority           = 0;
        $parent->save();

        $parent = self::find($parent->id);

        if(isset($inputs['required']))
        {
            ElementAttribute::createNewAttribute($parent, 'required', 'required');
        }

        if(isset($inputs['newItems']))
        {
            foreach($inputs['newItems'] as $label)
            {
                self::createNewChildElement($parent, $label);
            }
        }

        if(isset($inputs['otherOption']))
        {
            self::createNewChildElement($parent, trans('formBuilder.other'), true);
        }

        return $parent;
    }

    public static function clone(self $originElement)
    {
        $newParent                     = new self();
        $newParent->parent_id          = $originElement->parent_id;
        $newParent->label              = $originElement->label;
        $newParent->instructions       = $originElement->instructions;
        $newParent->is_key_information = $originElement->is_key_information;
        $newParent->has_attachments    = $originElement->has_attachments;
        $newParent->priority           = $originElement->priority;
        $newParent->save();

        $newParent = self::find($newParent->id);

        foreach(ElementAttribute::getElementAttributes($originElement) as $name => $value)
        {
            ElementAttribute::createNewAttribute($newParent, $name, $value);
        }

        $originElementSavedValueRecord = ElementValue::getSavedElementValueRecord($originElement);

        foreach(self::where('parent_id', $originElement->id)->orderBy('priority', 'ASC')->get() as $originChildElement)
        {
            $newChildElement = self::createNewChildElement($newParent, $originChildElement->label, $originChildElement->is_other_option);

            if($originElement->dynamicForm->isVendorSubmissionApproved() && !is_null($originElement->dynamicForm->origin_id))
            {
                if($originElementSavedValueRecord && $originElementSavedValueRecord->value == $originChildElement->id)
                {
                    ElementValue::createNewRecord($newParent, $newChildElement->id);

                    if($originChildElement->isOtherOption())
                    {
                        AdditionalElementValue::createOrUpdateRecord($newParent, $originElementSavedValueRecord->additionalValue->value);
                    }
                }
            }
        }

        $originElement->copyAttachmentsTo($newParent);

        return $newParent;
    }

    public static function createNewChildElement(self $parent, $label, $isOtherOption = false)
    {
        $element                  = new self();
        $element->parent_id       = $parent->id;
        $element->label           = $label;
        $element->is_other_option = $isOtherOption;
        $element->priority        = self::getChildNextFreePriority($parent);
        $element->save();

        return self::find($element->id);
    }

    public function updateElement($inputs)
    {
        $this->label              = $inputs['label'];
        $this->instructions       = trim($inputs['instructions']);
        $this->is_key_information = isset($inputs['key_information']);
        $this->has_attachments    = isset($inputs['attachments']);
        $this->save();

        if(isset($inputs['required']))
        {
            ElementAttribute::createNewAttribute($this, 'required', 'required');
        }
        else
        {
            ElementAttribute::deleteAttribute($this, 'required');
        }

        if(isset($inputs['existingItems']))
        {
            $existingItems = self::where('parent_id', $this->id)->where('is_other_option', false)->orderBy('priority', 'ASC')->get();
            $existingItemIds = $existingItems->lists('id');
    
            $toBeDeletedItemIds = array_diff($existingItemIds, array_keys($inputs['existingItems']));
    
            foreach($inputs['existingItems'] as $id => $label)
            {
                $item = self::find($id);
                $item->label = $label;
                $item->save();
            }

            foreach($toBeDeletedItemIds as $id)
            {
                $item = self::find($id);

                ElementAttribute::purge($item);

                $item->delete();
            }
        }
        else
        {
            $this->purgeChildren();
        }
        
        if(isset($inputs['newItems']))
        {
            foreach($inputs['newItems'] as $label)
            {
                self::createNewChildElement($this, $label);
            }
        }

        $otherOptionChild = $this->getOtherOptionChild();

        if(isset($inputs['otherOption']))
        {
            if(is_null($otherOptionChild))
            {
                self::createNewChildElement($this, trans('formBuilder.other'), true);
            }
        }
        else
        {
            if($otherOptionChild)
            {
                $otherOptionChild->delete();
            }
        }

        // update postion of other option child
        $otherOptionChild = $this->getOtherOptionChild();

        if($otherOptionChild)
        {
            $otherOptionChild->priority = self::getChildNextFreePriority($this);
            $otherOptionChild->save();
        }

        return self::find($this->id);
    }

    public function getOtherOptionChild()
    {
        return self::where('parent_id', $this->id)->where('is_other_option', true)->first();
    }

    public static function getChildNextFreePriority(self $parent)
    {
        $latestRecord = self::where('parent_id', $parent->id)->where('is_other_option', false)->orderBy('priority', 'DESC')->first();

        if(is_null($latestRecord)) return 0;

        return ($latestRecord->priority + 1);
    }

    public static function updateChildrenPriority(self $removedRecord)
    {
        $query = DB::raw('UPDATE ' . (new self)->getTable() . ' SET priority = (priority - 1) WHERE parent_id = ' . $removedRecord->parent_id . ' AND priority > ' . $removedRecord->priority . ' AND is_other_option = FALSE;');

        DB::update($query);
    }

    public function getElementDetails()
    {
        $disabled = ($this->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($this)));

        $data = [
            'id'                       => $this->id,
            'label'                    => $this->label,
            'instructions'             => $this->instructions,
            'displayInstructions'      => nl2br($this->instructions),
            'is_key_information'       => $this->is_key_information,
            'has_attachments'          => $this->has_attachments,
            'name'                     => strtolower(self::ELEMENT_TYPE_ID . '_' . $this->id),
            'class_identifier'         => self::getClassIdentifier(),
            'attributes'               => ElementAttribute::getElementAttributes($this),
            'children'                 => $this->getSelectionItems(),
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

        if($disabled)
        {
            $data['attributes']['disabled'] = 'disabled';
        }

        return $data;
    }

    public function getSelectionItems()
    {
        $savedElementValue = ElementValue::getSavedElementValue($this);
        $items             = [];

        $disabled = ($this->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($this)));

        foreach(self::where('parent_id', $this->id)->orderBy('priority', 'ASC')->get() as $item)
        {
            $attributes = [
                'value'   => $item->id,
                'name'    => strtolower(self::ELEMENT_TYPE_ID . '_' . $this->id),
            ];

            if($item->id == $savedElementValue)
            {
                $attributes['checked'] = 'checked';
            }

            if($disabled)
            {
                $attributes['disabled'] = 'disabled';
            }

            $temp = [
                'label'         => $item->label,
                'isOtherOption' => $item->is_other_option,
                'attributes'    => $attributes,
            ];

            if($item->isOtherOption())
            {
                $elementValueRecord = ElementValue::getSavedElementValueRecord($this);

                $temp['otherOption']['name']  = strtolower(self::ELEMENT_TYPE_ID . '_' . $this->id . '_other');
                $temp['otherOption']['value'] = ($elementValueRecord && $elementValueRecord->additionalValue) ? $elementValueRecord->additionalValue->value : '';

                if($disabled)
                {
                    $temp['otherOption']['disabled'] = 'disabled';
                }
            }

            array_push($items, $temp);
        }

        return $items;
    }


    public function deleteRelatedModels()
    {
        $this->purgeChildren();
        FormElementMapping::deleteElementMapping($this);
        ElementAttribute::purge($this);
        ElementRejection::deleteRecord($this);
        ElementValue::purgeElementValues($this);
    }

    public function purgeChildren()
    {
        foreach(self::where('parent_id', $this->id)->orderBy('priority', 'ASC')->get() as $child)
        {
            $child->delete();
        }
    }

    public function saveFormValues($value, $otherOptionValue = null)
    {
        ElementValue::syncElementValue($this, $value);

        // value is the selected radiobox element id
        $selectedElement = self::find($value);

        if($selectedElement->isOtherOption())
        {
            AdditionalElementValue::createOrUpdateRecord($this, $otherOptionValue);
        }
        else
        {
            AdditionalElementValue::wipeRecord($this);
        }
    }

    public function getSavedValuesDisplay()
    {
        $savedElementValueRecord = ElementValue::getSavedElementValueRecord($this);

        $savedElementSelection = $savedElementValueRecord ? self::find($savedElementValueRecord->value) : null;

        $value = null;

        if($savedElementSelection)
        {
            if($savedElementSelection->isOtherOption())
            {
                $value = ElementValue::getSavedElementValueRecord($this)->additionalValue->value;
            }
            else
            {
                $value = $savedElementSelection->label;
            }
        }

        return [
            'label'  => $this->label,
            'values' => is_null($value) ? [] : [$value],
            'route_attachments' => route('form.column.section.element.attachments.list.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'attachments_count' => $this->attachments->count(),
            'enable_attachments' => $this->has_attachments,
        ];
    }

    public function hasValuesChanged($newValue, $otherOptionValue = null)
    {
        $originalValue = ElementValue::getSavedElementValue($this);
        $valueChanged  = false;

        if(trim($originalValue) == trim($newValue))
        {
            $originalElement = self::find($originalValue);

            if($originalElement->isOtherOption())
            {
                $originalElementValue = ElementValue::getSavedElementValueRecord($this);
                $valueChanged         = ($originalElementValue->additionalValue->value == $otherOptionValue) ? false : true;
            }
        }
        else
        {
            $valueChanged = true;
        }

        return $valueChanged;
    }

    public function isFilled()
    {
        $savedElementValueRecord = ElementValue::getSavedElementValueRecord($this);

        return !is_null($savedElementValueRecord);
    }
}

