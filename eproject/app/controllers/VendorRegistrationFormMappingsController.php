<?php

use PCK\Helpers\DBTransaction;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\BusinessEntityType\BusinessEntityType;
use PCK\Companies\Company;
use PCK\FormBuilder\DynamicForm;
use PCK\VendorRegistration\FormTemplateMapping\VendorRegistrationFormTemplateMapping;

class VendorRegistrationFormMappingsController extends Controller
{
    public function index()
    {
        return View::make('vendor_registration.bind_form_index'); 
    }

    public function getFormMappingRecords()
    {
        $mappingRecords = [];

        foreach(ContractGroupCategory::orderBy('id', 'ASC')->where('hidden', '=', false)->where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)->get() as $contractGroupCategory)
        {
            foreach(BusinessEntityType::where('hidden', false)->orderBy('id', 'ASC')->get() as $businessEntityType)
            {
                $formTemplateMapping = VendorRegistrationFormTemplateMapping::findRecord($contractGroupCategory, $businessEntityType);

                $data = [
                    'contract_group_category_id'   => $contractGroupCategory->id,
                    'contract_group_category_name' => $contractGroupCategory->name,
                    'business_entity_type_id'      => $businessEntityType->id,
                    'business_entity_type_name'    => $businessEntityType->name,
                ];

                if($formTemplateMapping)
                {
                    $data['id']                = $formTemplateMapping->id;
                    $data['dynamic_form_id']   = $formTemplateMapping->dynamicForm->id;
                    $data['dynamic_form_name'] = $formTemplateMapping->dynamicForm->name;
                    $data['revision']          = $formTemplateMapping->dynamicForm->revision;
                    $data['route_unlink']      = route('vendor.registration.form.mapping.form.unlink', [$formTemplateMapping->id]);
                }

                array_push($mappingRecords, $data);
            }

            // others
            $otherData = [
                'contract_group_category_id'   => $contractGroupCategory->id,
                'contract_group_category_name' => $contractGroupCategory->name,
                'business_entity_type_id'      => BusinessEntityType::OTHER,
                'business_entity_type_name'    => trans('forms.others'),
            ];

            $formTemplateMapping = VendorRegistrationFormTemplateMapping::findRecord($contractGroupCategory, null);

            if($formTemplateMapping)
            {
                $otherData['id']                = $formTemplateMapping->id;
                $otherData['dynamic_form_id']   = $formTemplateMapping->dynamicForm->id;
                $otherData['dynamic_form_name'] = $formTemplateMapping->dynamicForm->name;
                $otherData['revision']          = $formTemplateMapping->dynamicForm->revision;
                $otherData['route_unlink']      = route('vendor.registration.form.mapping.form.unlink', [$formTemplateMapping->id]);
            }

            array_push($mappingRecords, $otherData);
        }

        return $mappingRecords;
    }

    public function getFormSelections()
    {
        $formSelections = DynamicForm::getSelectableFormsByModule(DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

        return Response::json($formSelections);
    }

    public function linkForm()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $contractGroupCategory = ContractGroupCategory::find($inputs['contractGroupCategoryId']);
            $businessEntityType    = ctype_digit($inputs['businessEntityTypeId']) ? BusinessEntityType::find($inputs['businessEntityTypeId']) : null;
            $form                  = DynamicForm::find($inputs['formId']);

            VendorRegistrationFormTemplateMapping::linkForm($contractGroupCategory, $businessEntityType, $form);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function unlinkForm($mappingId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $record = VendorRegistrationFormTemplateMapping::find($mappingId);
            $record->delete();

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }
}