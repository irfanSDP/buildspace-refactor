<?php

use PCK\VendorRegistration\VendorRegistration;
use PCK\FormBuilder\FormObjectMapping;
use PCK\FormBuilder\DynamicForm;
use PCK\ObjectLog\ObjectLog;
use PCK\VendorManagement\VendorManagementUserPermission;

class VendorRegistrationApprovalsController extends Controller
{
    public function vendorRegistrationFormShow($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        try
        {
            $form       = FormObjectMapping::findRecord($vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER)->dynamicForm;
            $user       = \Confide::user();
            $canProcess = $vendorRegistration->processor && $vendorRegistration->processor->user_id == $user->id && $vendorRegistration->isProcessing();

            return View::make('form_builder.vendor_registration_show', [
                'form'                       => $form,
                'canApproveVendorSubmission' => $form->isVendorSubmitted(),
                'vendorRegistration'         => $vendorRegistration,
                'formSubmitRoute'            => route('vendor.form.submit', [$form->id]),
                'vendorCanSubmitForm'        => null,
                'hasRejectedElements'        => $form->hasRejectedElements(),
                'processerCanEditForm'       => $vendorRegistration->isProcessing() && VendorManagementUserPermission::hasPermission($user, VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION),
                'canProcess'                 => $canProcess,
                'isVendor'                   => false,
                'backRoute'                  => route('vendorManagement.approval.registrationAndPreQualification.show', [$vendorRegistration->id]),
                'nextRoute'                  => route('vendorManagement.approval.companyPersonnel', [$vendorRegistration->id]),
            ]);
        }
        catch(Exception $e)
        {
            Flash::warning(trans('vendorManagement.companyHasYetToInitiateVendorRegistrationForm', ['company' => $vendorRegistration->company->name]));
        }

        return Redirect::back();
    }

    public function rejectVendorFormSubmission($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $form         = FormObjectMapping::findRecord($vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER)->dynamicForm;
        $form->status = DynamicForm::STATUS_DESIGN_APPROVED;
        $form->save();

        return Redirect::to(route('vendorManagement.approval.registrationAndPreQualification.show', [$vendorRegistration->id]));
    }

    public function getActionLogs($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $actionLogs = ObjectLog::getActionLogs($vendorRegistration, ObjectLog::MODULE_VENDOR_REGISTRATION_REGISTRATION_FORM);
        
        return Response::json($actionLogs);
    } 
}