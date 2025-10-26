<?php namespace PCK\VendorRegistration;

use PCK\Users\User;
use Carbon\Carbon;
use PCK\FormBuilder\FormObjectMapping;
use PCK\FormBuilder\DynamicForm;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\VendorRegistration\Payment\VendorRegistrationPayment;

class VendorRegistrationRepository {

    public function submitVendorRegistration($vendorRegistration, $submitterId = null)
    {
        // Submit all forms
        // -------------------
        // Vendor Registration
        $formObjectMapping = FormObjectMapping::findRecord($vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

        if($formObjectMapping)
        {
            $registrationForm                            = $formObjectMapping->dynamicForm;
            $registrationForm->status                    = DynamicForm::STATUS_VENDOR_SUBMITTED;
            $registrationForm->submission_status         = DynamicForm::SUBMISSION_STATUS_SUBMITTED;
            $registrationForm->submitted_for_approval_by = $submitterId;
            $registrationForm->save();
        }

        // Pre Qualification
        $relevantWorkCategoryIds = TrackRecordProject::where('vendor_registration_id', '=', $vendorRegistration->id)->lists('vendor_work_category_id');

        $vendorPreQualifications = VendorPreQualification::where('vendor_registration_id', '=', $vendorRegistration->id)
            ->whereIn('vendor_work_category_id', $relevantWorkCategoryIds)
            ->whereNotNull('weighted_node_id')
            ->update(array('status_id' => VendorPreQualification::STATUS_SUBMITTED));

        // Payment
        $vendorPaymentRegistration = VendorRegistrationPayment::getCurrentlySelectedPaymentMethodRecord($vendorRegistration->company);

        if($vendorPaymentRegistration)
        {
            $vendorPaymentRegistration->submitted = true;
            $vendorPaymentRegistration->submitted_date = Carbon::now();
            $vendorPaymentRegistration->save();
        }

        $vendorRegistration->submitted_at = Carbon::now();

        $vendorRegistration->status = $vendorRegistration->processor ? VendorRegistration::STATUS_PROCESSING: VendorRegistration::STATUS_SUBMITTED;
        $vendorRegistration->save();

        SubmissionLog::logAction($vendorRegistration, SubmissionLog::SUBMITTED);
    }
}