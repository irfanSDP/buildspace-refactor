<?php

use Illuminate\Support\Facades\DB;
use PCK\Helpers\DBTransaction;
use PCK\FormBuilder\FormObjectMapping;
use PCK\FormBuilder\DynamicForm;
use PCK\VendorRegistration\FormTemplateMapping\VendorRegistrationFormTemplateMapping;

class FormObjectMappingController extends Controller
{
    private $repository;

    public function vendorRegistrationFormShow()
    {
        $user = \Confide::user();

        // update or renewal
        // form has been created during update or renewal activation
        if(!$user->company->vendorRegistration->isFirst())
        {
            $formObjectMapping = FormObjectMapping::findRecord($user->company->vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);
            
            $vendorCanSubmitForm = $formObjectMapping->dynamicForm->isFormDesignApproved();

            return View::make('form_builder.vendor_registration_show', [
                'vendorRegistration'         => $user->company->vendorRegistration,
                'form'                       => $formObjectMapping->dynamicForm,
                'hasRejectedElements'        => $formObjectMapping->dynamicForm->hasRejectedElements(),
                'canApproveVendorSubmission' => false,
                'vendorCanSubmitForm'        => $vendorCanSubmitForm,
                'formSubmitRoute'            => route('vendor.form.submit', [$formObjectMapping->dynamicForm->id]),
                'isVendor'                   => true,
                'backRoute'                  => route('vendors.vendorRegistration.index'),
                'nextRoute'                  => route('vendors.vendorRegistration.companyPersonnel'),
            ]);
        }

        // for fresh registration
        $vendorRegistrationFormTemplateMapping = VendorRegistrationFormTemplateMapping::findRecord($user->company->contractGroupCategory, $user->company->businessEntityType);

        if($vendorRegistrationFormTemplateMapping)
        {
            try
            {
                $transaction = new DBTransaction();
                $transaction->begin();

                $formObjectMapping = FormObjectMapping::findRecord($user->company->vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

                if(is_null($formObjectMapping))
                {
                    $newForm           = $vendorRegistrationFormTemplateMapping->dynamicForm->clone($vendorRegistrationFormTemplateMapping->dynamicForm->name, $vendorRegistrationFormTemplateMapping->dynamicForm->module_identifier, true);
                    $formObjectMapping = FormObjectMapping::bindFormToObject($newForm, $user->company->vendorRegistration);
                }

                $transaction->commit();

                $formObjectMapping->load('dynamicForm');

                $vendorCanSubmitForm = $formObjectMapping->dynamicForm->isFormDesignApproved();

                return View::make('form_builder.vendor_registration_show', [
                    'vendorRegistration'         => $user->company->vendorRegistration,
                    'form'                       => $formObjectMapping->dynamicForm,
                    'hasRejectedElements'        => $formObjectMapping->dynamicForm->hasRejectedElements(),
                    'canApproveVendorSubmission' => false,
                    'vendorCanSubmitForm'        => $vendorCanSubmitForm,
                    'formSubmitRoute'            => route('vendor.form.submit', [$formObjectMapping->dynamicForm->id]),
                    'isVendor'                   => true,
                    'backRoute'                  => route('vendors.vendorRegistration.index'),
                    'nextRoute'                  => route('vendors.vendorRegistration.companyPersonnel'),
                ]);
            }
            catch(Exception $e)
            {
                $transaction->rollback();
                $errors = $e->getMessage();

                Flash::error($errors);

                return Redirect::back();
            }
        }
        else
        {
            Flash::error(trans('formBuilder.registrationFormConfigError'));
            
            return Redirect::back();
        }
    }
}