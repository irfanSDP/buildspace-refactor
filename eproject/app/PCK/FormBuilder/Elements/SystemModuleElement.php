<?php namespace PCK\FormBuilder\Elements;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\FormBuilder\ElementDefinition;
use PCK\FormBuilder\ElementAttribute;
use PCK\FormBuilder\FormElementMapping;
use PCK\States\State;
use PCK\Countries\Country;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\WorkCategories\WorkCategory;
use PCK\FormBuilder\ElementValue;
use PCK\FormBuilder\ElementRejection;
use PCK\Base\ModuleAttachmentTrait;

class SystemModuleElement extends Model
{
    use ModuleAttachmentTrait;

    protected $table = 'system_module_elements';

    protected $validationRules = ['required'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $model)
        {
            $model->deleteRelatedModels();
        });
    }

    const ELEMENT_TYPE_ID = 'sys';

    public static function getElementTypesByIdentifer($identifier = null)
    {
        $mapping = [
            ElementDefinition::TYPE_RADIOBOX => trans('formBuilder.radiobox'),
            ElementDefinition::TYPE_CHECKBOX => trans('formBuilder.checkbox'),
            ElementDefinition::TYPE_DROPDOWN => trans('formBuilder.dropdown'),
        ];

        return is_null($identifier) ? $mapping : $mapping[$identifier];
    }

    public static function getSystemModuleElementTypeByIdentifier($identifier = null)
    {
        $mapping = [
            ElementDefinition::TYPE_STATE         => trans('formBuilder.state'),
            ElementDefinition::TYPE_COUNTRY       => trans('formBuilder.country'),
            ElementDefinition::TYPE_USER_TYPE     => trans('formBuilder.userType'),
            ElementDefinition::TYPE_WORK_CATEGORY => trans('formBuilder.workCategory'),
        ];

        return is_null($identifier) ? $mapping : $mapping[$identifier];
    }

    public function getValidationRulesString()
    {
        $elementAttributes = ElementAttribute::getElementAttributes($this);
        $rules             = [];

        foreach($elementAttributes as $name => $value)
        {
            if(!in_array($name, $this->validationRules)) continue;

            $rules[$name] = $value;
        }

        return implode('|', $rules);
    }

    public function elementDefinition()
    {
        return $this->belongsTo('PCK\FormBuilder\ElementDefinition', 'element_definition_id');
    }

    public function isKeyInformation()
    {
        return ($this->is_key_information == true);
    }

    public function getDynamicFormAttribute()
    {
        return FormElementMapping::getElementMappingByElement($this)->section->column->dynamicForm;
    }

    public static function createNewElement($inputs)
    {
        $class             = ElementDefinition::getSystemModuleClassNameByIdentifier($inputs['system_module_identifier']);
        $elementDefinition = ElementDefinition::findElementDefinition($inputs['element_render_identifier'], $class);

        $element                        = new self();
        $element->element_definition_id = $elementDefinition->id;
        $element->label                 = $inputs['label'];
        $element->instructions          = trim($inputs['instructions']);
        $element->is_key_information    = isset($inputs['key_information']);
        $element->has_attachments       = isset($inputs['attachments']);
        $element->save();

        $element = self::find($element->id);

        if(isset($inputs['required']))
        {
            ElementAttribute::createNewAttribute($element, 'required', 'required');
        }

        if(($inputs['element_render_identifier'] == ElementDefinition::TYPE_DROPDOWN) && ($inputs['dropdown_select_type'] == FormBuilderElementCommon::DROPDOWN_MULTIPLE_SELECT))
        {
            ElementAttribute::createNewAttribute($element, 'multiple', 'multiple');
        }

        return $element;
    }

    public static function clone(self $originElement)
    {
        $newElement                        = new self();
        $newElement->element_definition_id = $originElement->element_definition_id;
        $newElement->is_key_information    = $originElement->is_key_information;
        $newElement->has_attachments       = $originElement->has_attachments;
        $newElement->label                 = $originElement->label;
        $newElement->instructions          = $originElement->instructions;
        $newElement->save();

        $newElement = self::find($newElement->id);

        foreach(ElementAttribute::getElementAttributes($originElement) as $name => $value)
        {
            ElementAttribute::createNewAttribute($newElement, $name, $value);
        }

        $originElementSavedValueRecords = ElementValue::getSavedElementValueRecords($originElement);

        if($originElement->dynamicForm->isVendorSubmissionApproved() && !is_null($originElement->dynamicForm->origin_id))
        {
            foreach($originElementSavedValueRecords as $originElementSavedValueRecord)
            {
                ElementValue::createNewRecord($newElement, $originElementSavedValueRecord->value);
            }
        }

        $originElement->copyAttachmentsTo($newElement);

        return $newElement;
    }

    public function getElementDetails($withSelectionItems = true)
    {
        $data = [
            'id'                        => $this->id,
            'label'                     => $this->label,
            'instructions'              => $this->instructions,
            'displayInstructions'       => nl2br($this->instructions),
            'is_key_information'        => $this->is_key_information,
            'has_attachments'           => $this->has_attachments,
            'name'                      => self::ELEMENT_TYPE_ID . '_' . $this->id,
            'element_render_identifier' => $this->elementDefinition->element_render_identifier,
            'module_class_identifer'    => array_search($this->elementDefinition->module_class, ElementDefinition::getSystemModuleClassNameByIdentifier()),
            'element_type'              => self::ELEMENT_TYPE_ID,
            'mapping_id'                => FormElementMapping::getElementMappingByElement($this)->id,
            'route_getElement'          => route('form.column.section.element.details.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_update'              => route('form.column.section.element.update', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_delete'              => route('form.column.section.element.delete', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_get_rejection'       => route('element.rejection.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_save_rejection'      => route('element.rejection.save', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_delete_rejection'    => route('element.rejection.delete', [$this->id, self::ELEMENT_TYPE_ID]),
            'is_rejected'               => $this->isRejected(),
            'is_amended'                => $this->isAmended(),
            'route_attachment_count'    => route('form.column.section.element.attachments.count.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_attachment_list'     => route('form.column.section.element.attachments.list.get', [$this->id, self::ELEMENT_TYPE_ID]),
            'route_upload_attachments'  => route('form.column.section.element.attachments.update', [$this->id, self::ELEMENT_TYPE_ID]),
            'attachment_count'          => $this->attachments->count(),
        ];

        $isDropdown = ($this->elementDefinition->element_render_identifier == ElementDefinition::TYPE_DROPDOWN);

        $data['attributes'] = ElementAttribute::getElementAttributes($this);

        if($isDropdown)
        {
            $data['attributes']['name']     = self::ELEMENT_TYPE_ID . '_' . $this->id . '[]';
        }

        $disabled = ($this->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($this)));

        if($disabled)
        {
            $data['attributes']['disabled'] = 'disabled';
        }

        // for performance reasons, as dataset could be large
        if($withSelectionItems)
        {
            $data['children'] = $this->getSelectionItems();
        }

        return $data;
    }

    public function getSelectionItems()
    {
        $elementRenderIdentifier = $this->elementDefinition->element_render_identifier;
        $selectionItems          = [];

        switch($elementRenderIdentifier)
        {
            case ElementDefinition::TYPE_RADIOBOX:
                $selectionItems = $this->getRadioboxItemStructure();
            break;
            case ElementDefinition::TYPE_CHECKBOX:
                $selectionItems = $this->getCheckboxItemStructure();
            break;
            case ElementDefinition::TYPE_DROPDOWN:
                $selectionItems = $this->getDropdownItemStructure();
            break;
        }

        return $selectionItems;
    }

    public function getRadioboxItemStructure()
    {
        $savedElementValue   = ElementValue::getSavedElementValue($this);
        $systemModuleRecords = $this->getRecordsBySystemModule();
        $recordStructure     = [];

        $disabled = ($this->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($this)));

        foreach($systemModuleRecords as $record)
        {
            $attributes = [
                'name'  => strtolower(self::ELEMENT_TYPE_ID . '_' . $this->id),
                'value' => $record['id'],
            ];

            if($record['id'] == $savedElementValue)
            {
                $attributes['checked'] = 'checked';
            }

            if($disabled)
            {
                $attributes['disabled'] = 'disabled';
            }

            array_push($recordStructure, [
                'label'      => trim($record['name']),
                'attributes' => $attributes,
            ]);
        }

        return $recordStructure;
    }

    public function getCheckboxItemStructure()
    {
        $savedElementValues  = ElementValue::getSavedElementValues($this);
        $systemModuleRecords = $this->getRecordsBySystemModule();
        $recordStructure     = [];

        $disabled = ($this->dynamicForm->isVendorSubmissionStatusSubmitted() && is_null(ElementRejection::findRecordByElement($this)));

        foreach($systemModuleRecords as $record)
        {
            $attributes = [
                'name'  => strtolower(self::ELEMENT_TYPE_ID . '_' . $this->id) . '[]',
                'value' => $record['id'],
            ];

            if(in_array($record['id'], $savedElementValues))
            {
                $attributes['checked'] = 'checked';
            }

            if($disabled)
            {
                $attributes['disabled'] = 'disabled';
            }

            array_push($recordStructure, [
                'label'      => trim($record['name']),
                'attributes' => $attributes,
            ]);
        }

        return $recordStructure;
    }

    public function getDropdownItemStructure()
    {
        $savedElementValues  = ElementValue::getSavedElementValues($this);
        $systemModuleRecords = $this->getRecordsBySystemModule();
        $recordStructure     = [];

        foreach($systemModuleRecords as $record)
        {
            $attributes = [
                'value' => $record['id'],
            ];

            if(in_array($record['id'], $savedElementValues))
            {
                $attributes['selected'] = 'selected';
            }

            array_push($recordStructure, [
                'label'      => trim($record['name']),
                'attributes' => $attributes,
            ]);
        }

        return $recordStructure;
    }

    public function getRecordsBySystemModule()
    {
        $class   = $this->elementDefinition->module_class;
        $records = [];
    
        switch($class)
        {
            case State::class:
                foreach(State::orderBy('id', 'ASC')->get() as $state)
                {
                    array_push($records, [
                        'id'   => $state->id,
                        'name' => $state->name,
                    ]);
                }
            break;
            case Country::class:
                foreach(Country::orderBy('id', 'ASC')->get() as $country)
                {
                    array_push($records, [
                        'id'   => $country->id,
                        'name' => $country->country,
                    ]);
                }
            break;
            case ContractGroupCategory::class:
                foreach(ContractGroupCategory::orderBy('id', 'ASC')->where('hidden', false)->get() as $contractGroupCategory)
                {
                    array_push($records, [
                        'id'   => $contractGroupCategory->id,
                        'name' => $contractGroupCategory->name,
                    ]);
                }
            break;
            case WorkCategory::class:
                foreach(WorkCategory::orderBy('id', 'ASC')->get() as $workCategory)
                {
                    array_push($records, [
                        'id'   => $workCategory->id,
                        'name' => $workCategory->name,
                    ]);
                }
            break;
        }

        return $records;
    }

    public function getElementAttributes()
    {
        $elementAttributes = ElementAttribute::where('element_id', $this->id)->where('element_class', get_class($this))->orderBy('id', 'ASC')->get();
        $attributes        = [];

        foreach($elementAttributes as $attribute)
        {
            $attributes[$attribute->name] = $attribute->value;
        }

        return $attributes;
    }

    public function updateElement($inputs)
    {
        $this->label              = $inputs['label'];
        $this->instructions       = trim($inputs['instructions']);
        $this->is_key_information = isset($inputs['key_information']);
        $this->has_attachments    = isset($inputs['attachments']);
        $this->updated_by         = \Confide::user()->id;
        $this->save();

        if(isset($inputs['required']))
        {
            ElementAttribute::createNewAttribute($this, 'required', 'required');
        }
        else
        {
            ElementAttribute::deleteAttribute($this, 'required');
        }

        if($inputs['element_render_identifier'] == ElementDefinition::TYPE_DROPDOWN)
        {
            if($inputs['dropdown_select_type'] == FormBuilderElementCommon::DROPDOWN_MULTIPLE_SELECT)
            {
                ElementAttribute::createNewAttribute($this, 'multiple', 'multiple');
            }
            else
            {
                ElementAttribute::deleteAttribute($this, 'multiple');
            }
        }

        return $this;
    }

    public function deleteRelatedModels()
    {
        FormElementMapping::deleteElementMapping($this);
        ElementAttribute::purge($this);
        ElementRejection::deleteRecord($this);
        ElementValue::purgeElementValues($this);
    }

    public function saveFormValues($values)
    {
        if($this->elementDefinition->element_render_identifier == ElementDefinition::TYPE_RADIOBOX)
        {
            ElementValue::syncElementValue($this, $values);
        }
        else
        {
            ElementValue::syncMultipleElementValues($this, $values);
        }
    }

    public function getValidationErrorMessage()
    {
        $name = self::ELEMENT_TYPE_ID . '_' . $this->id;
        
        $messages = [];
        $messages[$name . '.required'] = trans('formBuilder.elementisRequired', ['label' => $this->label]);

        return $messages;
    }

    public function isRejected()
    {
        $record = ElementRejection::findRecordByElement($this);

        return !is_null($record);
    }

    public function isAmended()
    {
        $isAmended = false;
        $record    = ElementRejection::findRecordByElement($this);

        if(is_null($record)) return false;

        return $record->is_amended;
    }

    public function getSavedValuesDisplay()
    {
        $values = [];

        $class = $this->elementDefinition->module_class;

        $savedValues = ElementValue::getSavedElementValues($this);

        switch($this->elementDefinition->element_render_identifier)
        {
            case ElementDefinition::TYPE_RADIOBOX:
                foreach($this->getRadioboxItemStructure() as $structure)
                {
                    if(in_array($structure['attributes']['value'], $savedValues))
                    {
                        array_push($values, $structure['label']);
                    }
                }
            break;
            case ElementDefinition::TYPE_CHECKBOX:
                foreach($this->getCheckboxItemStructure() as $structure)
                {
                    if(in_array($structure['attributes']['value'], $savedValues))
                    {
                        array_push($values, $structure['label']);
                    }
                }
            break;
            case ElementDefinition::TYPE_DROPDOWN:
                foreach($this->getDropdownItemStructure() as $structure)
                {
                    if(in_array($structure['attributes']['value'], $savedValues))
                    {
                        array_push($values, $structure['label']);
                    }
                }
            break;
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
        if($this->elementDefinition->element_render_identifier == ElementDefinition::TYPE_RADIOBOX)
        {
            $originalValue = ElementValue::getSavedElementValue($this);
            $valueChanged  = false;

            if(trim($originalValue) == trim($newValues))
            {
                return false;
            }

            return true;
        }
        else
        {
            $elementValueRecords = ElementValue::getSavedElementValueRecords($this);
            $valueChanged        = false;

            $additionalNewValues   = array_diff($newValues, $elementValueRecords->lists('value'));      // in new values but not in original
            $removedOriginalValues = array_diff($elementValueRecords->lists('value'), $newValues);      // in original but not in new values

            return ((count($additionalNewValues) > 0) || (count($removedOriginalValues) > 0));
        }
    }

    public static function getElementValues(Array $elementIds)
    {
        if(count($elementIds) == 0) return [];

        $query = "SELECT sme.id AS element_id, ed.module_class, ARRAY_TO_JSON(ARRAY_AGG(ev.value)) AS values
                    FROM system_module_elements sme
                    INNER JOIN element_definitions ed ON ed.id = sme.element_definition_id 
                    INNER JOIN element_values ev ON ev.element_id = sme.id
                    WHERE sme.id IN (" . implode(', ', $elementIds) . ")
                    AND ev.element_class = '" . self::class . "'
                    GROUP BY sme.id, ed.module_class
                    ORDER BY sme.id ASC;";

        $queryResult = DB::select(DB::raw($query));

        $data = [];

        foreach($queryResult as $result)
        {
            $class = $result->module_class;

            $ids    = json_decode($result->values);
            $values = $class::getRecordsByIds($ids);

            $data[$result->element_id] = implode('; ', $values);
        }

        return $data;
    }

    public function isFilled()
    {
        $savedElementValues = ElementValue::getSavedElementValues($this);

        return (count($savedElementValues) > 0);
    }
}