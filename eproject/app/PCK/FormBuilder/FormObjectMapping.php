<?php namespace PCK\FormBuilder;

use Illuminate\Database\Eloquent\Model;
use PCK\VendorRegistration\FormTemplateMapping\VendorRegistrationFormTemplateMapping;
use PCK\Companies\Company;

class FormObjectMapping extends Model
{
    protected $table = 'form_object_mappings';

    public function dynamicForm()
    {
        return $this->belongsTo('PCK\FormBuilder\DynamicForm', 'dynamic_form_id');
    }

    public static function findRecord($object, $moduleIdentifier)
    {
        $record = \DB::table('form_object_mappings AS map')
                ->join('dynamic_forms AS form', 'form.id', '=', 'map.dynamic_form_id')
                ->select('map.id')
                ->where('form.module_identifier', '=', $moduleIdentifier)
                ->where('object_id', $object->id)
                ->where('object_class', get_class($object))
                ->first();

        return $record ? self::find($record->id) : null;
    }

    public static function bindFormToObject(DynamicForm $form, $object)
    {
        $record = self::findRecord($object, $form->module_identifier);

        if(is_null($record))
        {
            $record                  = new self();
            $record->object_id       = $object->id;
            $record->object_class    = get_class($object);
            $record->dynamic_form_id = $form->id;
            $record->created_by      = \Confide::user()->id;
            $record->updated_by      = \Confide::user()->id;
            $record->save();
        }

        return self::find($record->id);
    }

    public static function flushRecord($object, $moduleIdentifier)
    {
        $record = self::findRecord($object, $moduleIdentifier);

        if($record)
        {
            if($record->dynamicForm)
            {
                $record->dynamicForm->delete();
            }

            $record->delete();
        }
    }

    public static function createAndBindVendorRegistrationForm(Company $company)
    {
        $vendorRegistrationFormTemplateMapping = VendorRegistrationFormTemplateMapping::findRecord($company->contractGroupCategory, $company->businessEntityType);

        $newForm = $vendorRegistrationFormTemplateMapping->dynamicForm->clone($vendorRegistrationFormTemplateMapping->dynamicForm->name, $vendorRegistrationFormTemplateMapping->dynamicForm->module_identifier, true);

        $formObjectMapping = self::bindFormToObject($newForm, $company->vendorRegistration);

        return $newForm;
    }
}