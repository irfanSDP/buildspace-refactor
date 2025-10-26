<?php namespace PCK\VendorRegistration\FormTemplateMapping;

use Illuminate\Database\Eloquent\Model;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\BusinessEntityType\BusinessEntityType;
use PCK\FormBuilder\DynamicForm;

class VendorRegistrationFormTemplateMapping extends Model
{
    protected $table = 'vendor_registration_form_template_mappings';

    public function contractGroupCategory()
    {
        return $this->belongsTo('PCK\ContractGroupCategory\ContractGroupCategory', 'contract_group_category_id');
    }

    public function businessEntityType()
    {
        return $this->belongsTo('PCK\BusinessEntityType\BusinessEntityType', 'business_entity_type_id');
    }

    public function dynamicForm()
    {
        return $this->belongsTo('PCK\FormBuilder\DynamicForm', 'dynamic_form_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'updated_by');
    }

    public static function findRecord(ContractGroupCategory $contractGroupCategory, BusinessEntityType $businessEntityType = null)
    {
        $query = self::where('contract_group_category_id', $contractGroupCategory->id);

        if(is_null($businessEntityType))
        {
            $query->whereNull('business_entity_type_id');
        }
        else
        {
            $query->where('business_entity_type_id', $businessEntityType->id);
        }

        return $query->first();
    }

    public static function linkForm(ContractGroupCategory $contractGroupCategory, BusinessEntityType $businessEntityType = null, DynamicForm $form)
    {
        $record = self::findRecord($contractGroupCategory, $businessEntityType);

        if(is_null($record))
        {
            $record                             = new self();
            $record->contract_group_category_id = $contractGroupCategory->id;
            $record->business_entity_type_id    = is_null($businessEntityType) ? null : $businessEntityType->id;
            $record->created_by                 = \Confide::user()->id;
        }

        $record->dynamic_form_id = $form->id;
        $record->updated_by      = \Confide::user()->id;
        $record->save();

        return self::find($record->id);
    }

    public static function updateMappedFormToLatestRevision(DynamicForm $form)
    {
        $records = self::where('dynamic_form_id', $form->getPreviousRevisionForm()->id)->get();

        foreach($records as $record)
        {
            $record->dynamic_form_id = $form->id;
            $record->save();
        }
    }
}