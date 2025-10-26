<?php

use Carbon\Carbon;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorRegistration\Section;
use PCK\VendorRegistration\Payment\VendorRegistrationPayment;
use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\ObjectField\ObjectField;
use PCK\Companies\Company;
use PCK\VendorManagement\InstructionSetting;

class VendorRegistrationPaymentApprovalsController extends \BaseController
{
    public function index($companyId)
    {
        $company                   = Company::find($companyId);
        $vendorRegistrationPayment = VendorRegistrationPayment::getCurrentlySelectedPaymentMethodRecord($company);

        $vendorRegistrationPaymentSection = $company->vendorRegistration->getSection(Section::SECTION_PAYMENT);

        $instructionSettings = InstructionSetting::first();

        return View::make('vendor_registration.payment_approval', [
            'vendorRegistration'               => $company->vendorRegistration,
            'vendorRegistrationPayment'        => $vendorRegistrationPayment,
            'vendorRegistrationPaymentSection' => $vendorRegistrationPaymentSection,
            'submittedDate'                    => !$vendorRegistrationPayment || is_null($vendorRegistrationPayment->submitted_date) ? null : Carbon::parse($vendorRegistrationPayment->submitted_date)->format(\Config::get('dates.full_format_without_time')),
            'paidDate'                         => !$vendorRegistrationPayment || is_null($vendorRegistrationPayment->paid_date) ? null : Carbon::parse($vendorRegistrationPayment->paid_date)->format(\Config::get('dates.full_format_without_time')),
            'completedDate'                    => !$vendorRegistrationPayment || is_null($vendorRegistrationPayment->successful_date) ? null : Carbon::parse($vendorRegistrationPayment->successful_date)->format(\Config::get('dates.full_format_without_time')),
            'instructionSettings'              => $instructionSettings,
        ]);
    }

    public function getAllRecordsByCompany($companyId)
    {
        $company                   = Company::find($companyId);
        $vendorRegistationPayments = VendorRegistrationPayment::getAllRecordsByCompany($company);

        $records = [];

        foreach($vendorRegistationPayments as $record)
        {
            array_push($records, [
                'id'                          => $record->id,
                'bank_name'                   => $record->paymentSetting->name,
                'bank_account_number'         => $record->paymentSetting->account_number,
                'virtual_bank_account_number' => $record->getVirtualAccountNumber(),
                'uploaded_file_count'         => $record->attachments->count(),
                'route_get_uploaded_files'    => route('vendorManagement.approval.payment.uploaded.attachments.get', [$company->id, $record->id]),
            ]);
        }

        return Response::json($records);
    }

    public function getUploadedAttachments($companyId, $paymentId)
	{
        $vendorRegistrationPayment = VendorRegistrationPayment::find($paymentId);

        return $this->getAttachmentDetails($vendorRegistrationPayment);
	}

    public function vendorRegistrationPaymentUpdate($companyId, $paymentId)
    {
        $vendorRegistrationPayment = VendorRegistrationPayment::find($paymentId);

        $inputs = Input::all();
        
        if($vendorRegistrationPayment->isPaid())
        {
            $vendorRegistrationPayment->successful_date = Carbon::parse($inputs['successful_date']);
            $vendorRegistrationPayment->successful      = true;
        }
        else
        {
            $vendorRegistrationPayment->paid_date = Carbon::parse($inputs['paid_date']);
            $vendorRegistrationPayment->paid      = true;
        }

        $vendorRegistrationPayment->save();

        return Redirect::back();
    }

    public function vendorRegistrationPaymentSectionReject($companyId)
    {
        $company = Company::find($companyId);

        $vendorRegistrationPaymentSection = $company->vendorRegistration->getSection(Section::SECTION_PAYMENT);

        $vendorRegistrationPaymentSection->status_id = Section::STATUS_REJECTED;
        $vendorRegistrationPaymentSection->amendment_status = Section::AMENDMENT_STATUS_REQUIRED;
        $vendorRegistrationPaymentSection->amendment_remarks = Input::get('amendment_remarks');

        $vendorRegistrationPaymentSection->save();

        \Flash::success(trans('forms.rejectSuccessful'));

        return Redirect::back();
    }

    public function getPaymentAdditionalAttachments($companyId, $paymentId, $field)
    {
        $vendorRegistrationPayment = VendorRegistrationPayment::find($paymentId);

        $object = ObjectField::findOrCreateNew($vendorRegistrationPayment, $field);

        $uploadedFiles = $this->getAttachmentDetails($object);

		$data = array();

		foreach($uploadedFiles as $file)
		{
			$file['imgSrc']      = $file->generateThumbnailURL();
			$file['deleteRoute'] = $file->generateDeleteURL();
			$file['size']	     = Helpers::formatBytes($file->size);

			$data[] = $file;
		}

		return $data;
    }

    public function getPaymentAdditionalAttachmentsCount($companyId, $paymentId, $field)
    {
        $vendorRegistrationPayment = VendorRegistrationPayment::find($paymentId);

        $object = ObjectField::findOrCreateNew($vendorRegistrationPayment, $field);

        return Response::json([
            'field'           => $field,
            'attachmentCount' => count($this->getAttachmentDetails($object)),
        ]);
    }

    public function uploadPaymentAdditionalAttachments($companyId, $paymentId, $field)
    {
        $vendorRegistrationPayment = VendorRegistrationPayment::find($paymentId);

        $inputs = Input::all();

        $object = ObjectField::findOrCreateNew($vendorRegistrationPayment, $field);

        ModuleAttachment::saveAttachments($object, $inputs);

		return array(
			'success' => true,
		);
    }

    public function resolve($companyId)
    {
        $company = Company::find($companyId);
        $section = $company->vendorRegistration->getSection(Section::SECTION_PAYMENT);

        $section->status_id         = Section::STATUS_DRAFT;
        $section->amendment_status  = Section::AMENDMENT_STATUS_NOT_REQUIRED;
        $section->amendment_remarks = '';
        $section->save();

        return Redirect::back();
    }
}