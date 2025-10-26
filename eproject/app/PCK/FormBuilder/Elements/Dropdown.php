<?php namespace PCK\FormBuilder\Elements;

use Illuminate\Support\Facades\DB;
use PCK\FormBuilder\FormColumnSection;
use PCK\FormBuilder\FormElementMapping;
use PCK\FormBuilder\ElementAttribute;
use PCK\FormBuilder\Elements\FormBuilderElementCommon;
use PCK\FormBuilder\ElementValue;
use PCK\FormBuilder\ElementRejection;

class Dropdown extends Element implements Elementable
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

    public static function getClassIdentifier()
    {
        return self::TYPE_DROPDOWN;
    }

    public function getValidationErrorMessage()
    {
        $name = self::ELEMENT_TYPE_ID . '_' . $this->id;
        
        $messages = [];
        $messages[$name . '.required'] = trans('formBuilder.elementisRequired', ['label' => $this->label]);

        return $messages;
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
                return 'required';
            }
        }

        return null;
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

        if($inputs['dropdown_select_type'] == FormBuilderElementCommon::DROPDOWN_MULTIPLE_SELECT)
        {
            ElementAttribute::createNewAttribute($parent, 'multiple', 'multiple');
        }

        if(isset($inputs['newItems']))
        {
            foreach($inputs['newItems'] as $label)
            {
                self::createNewChildElement($parent, $label);
            }
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

        $originElementSavedValueRecords = ElementValue::getSavedElementValueRecords($originElement);

        foreach(self::where('parent_id', $originElement->id)->orderBy('priority', 'ASC')->get() as $originChildElement)
        {
            $newChildElement = self::createNewChildElement($newParent, $originChildElement->label);

            if($originElement->dynamicForm->isVendorSubmissionApproved() && !is_null($originElement->dynamicForm->origin_id))
            {
                foreach($originElementSavedValueRecords as $originElementSavedValueRecord)
                {
                    if($originElementSavedValueRecord->value == $originChildElement->id)
                    {
                        ElementValue::createNewRecord($newParent, $newChildElement->id);
                    }
                }
            }
        }

        $originElement->copyAttachmentsTo($newParent);

        return $newParent;
    }

    public static function createNewChildElement(self $parent, $label)
    {
        $element                 = new self();
        $element->parent_id      = $parent->id;
        $element->label          = $label;
        $element->priority       = self::getChildNextFreePriority($parent);
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

        if($inputs['dropdown_select_type'] == FormBuilderElementCommon::DROPDOWN_SINGLE_SELECT)
        {
            ElementAttribute::deleteAttribute($this, 'multiple');
        }

        if($inputs['dropdown_select_type'] == FormBuilderElementCommon::DROPDOWN_MULTIPLE_SELECT)
        {
            ElementAttribute::createNewAttribute($this, 'multiple', 'multiple');
        }

        if(isset($inputs['existingItems']))
        {
            $existingItems = self::where('parent_id', $this->id)->orderBy('priority', 'ASC')->get();
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

        return self::find($this->id);
    }

    public static function getChildNextFreePriority(self $parent)
    {
        $latestRecord = self::where('parent_id', $parent->id)->orderBy('priority', 'DESC')->first();

        if(is_null($latestRecord)) return 0;

        return ($latestRecord->priority + 1);
    }

    public static function updateChildrenPriority(self $removedRecord)
    {
        $query = DB::raw('UPDATE ' . (new self)->getTable() . ' SET priority = (priority - 1) WHERE parent_id = ' . $removedRecord->parent_id . ' AND priority > ' . $removedRecord->priority . ';');

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

        $data['attributes']         = ElementAttribute::getElementAttributes($this);
        $data['attributes']['name'] = strtolower(self::ELEMENT_TYPE_ID . '_' . $this->id) . '[]';

        if($disabled)
        {
            $data['attributes']['disabled'] = 'disabled';
        }

        return $data;
    }

    public function getSelectionItems()
    {
        $savedElementValues = ElementValue::getSavedElementValues($this);
        $items              = [];

        foreach(self::where('parent_id', $this->id)->orderBy('priority', 'ASC')->get() as $item)
        {
            $attributes = [
                'value'   => $item->id,
            ];

            if(in_array($item->id, $savedElementValues))
            {
                $attributes['selected'] = 'selected';
            }

            array_push($items, [
                'label'      => $item->label,
                'attributes' => $attributes,
            ]);
        }

        return $items;
    }

    public function deleteRelatedModels()
    {
        $this->purgeChildren();
        ElementAttribute::purge($this);
        FormElementMapping::deleteElementMapping($this);
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

    public function saveFormValues($value)
    {
        ElementValue::syncMultipleElementValues($this, $value);
    }

    public function getSavedValuesDisplay()
    {
        $values = [];

        foreach(ElementValue::getSavedElementValues($this) as $id)
        {
            $selection = self::find($id);
            
            array_push($values, $selection->label);
        }

        return [
            'label'  => $this->label,
            'values' => $values,
            'route_attachments' => route('form.column.section.element.attachments.list.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'attachments_count' => $this->attachments->count(),
            'enable_attachments' => $this->has_attachments,
        ];
    }

    public function hasValuesChanged($newValues)
    {
        $elementValueRecords = ElementValue::getSavedElementValueRecords($this);
        $valueChanged        = false;

        $additionalNewValues   = array_diff($newValues, $elementValueRecords->lists('value'));      // in new values but not in original
        $removedOriginalValues = array_diff($elementValueRecords->lists('value'), $newValues);      // in original but not in new values

        return ((count($additionalNewValues) > 0) || (count($removedOriginalValues) > 0));
    }

    public function isFilled()
    {
        $savedElementValues = ElementValue::getSavedElementValues($this);

        return (count($savedElementValues) > 0);
    }
}

