<?php

use PCK\SupplierCreditFacility\SupplierCreditFacility;
use PCK\VendorRegistration\Section;
use PCK\Forms\SupplierCreditFacilityForm;
use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\SupplierCreditFacility\SupplierCreditFacilitySetting;
use PCK\VendorManagement\InstructionSetting;
use PCK\VendorRegistration\VendorRegistration;
use PCK\ObjectLog\ObjectLog;

class SupplierCreditFacilitiesController extends \BaseController {

    protected $supplierCreditFacilityForm;

    public function __construct(SupplierCreditFacilityForm $supplierCreditFacilityForm)
    {
        $this->supplierCreditFacilityForm = $supplierCreditFacilityForm;
    }

    public function index()
    {
        $user = \Confide::user();

        $records = SupplierCreditFacility::where('vendor_registration_id', '=', $user->company->vendorRegistration->id)
            ->orderBy('id', 'desc')
            ->get();

        $data = [];

        foreach($records as $record)
        {
            $data[] = [
                'id'                => $record->id,
                'name'              => $record->supplier_name,
                'facilities'        => $record->credit_facilities,
                'route:edit'        => route('vendors.vendorRegistration.supplierCreditFacilities.edit', array($record->id)),
                'route:delete'      => route('vendors.vendorRegistration.supplierCreditFacilities.destroy', array($record->id)),
                'route:attachments' => route('vendors.vendorRegistration.supplierCreditFacilities.attachments.get', array($record->id)),
                'attachments_count' => $record->attachments->count(),
                'deletable'         => true,
            ];
        }

        $data[] = [];

        $section = $user->company->vendorRegistration->getSection(Section::SECTION_SUPPLIER_CREDIT_FACILITIES);

        $setting = SupplierCreditFacilitySetting::first();

        $instructionSettings = InstructionSetting::first();

        return View::make('vendor_registration.supplier_credit_facilities.index', compact('data', 'section', 'setting', 'instructionSettings'));
    }

    public function create()
    {
        $setting = SupplierCreditFacilitySetting::first();

        return View::make('vendor_registration.supplier_credit_facilities.create', compact('setting'));
    }

    public function processorCreate($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);
        $setting            = SupplierCreditFacilitySetting::first();

        return View::make('vendor_management.approval.supplier_credit_facilities.create', compact('setting', 'vendorRegistration'));
    }

    public function store()
    {
        $user = \Confide::user();

        $input = Input::get();

        $this->supplierCreditFacilityForm->validate($input);

        $input['vendor_registration_id'] = \Confide::user()->company->vendorRegistration->id;

        $supplierCreditFacility = SupplierCreditFacility::create($input);

        ModuleAttachment::saveAttachments($supplierCreditFacility, $input);

        if($supplierCreditFacility->vendorRegistration->isDraft() && $supplierCreditFacility->vendorRegistration->processor)
        {
            $this->markSectionAsAmended($supplierCreditFacility->vendorRegistration);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendors.vendorRegistration.supplierCreditFacilities');
    }

    public function processorStore($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $input = Input::get();

        $this->supplierCreditFacilityForm->validate($input);

        $input['vendor_registration_id'] = $vendorRegistration->id;

        $supplierCreditFacility = SupplierCreditFacility::create($input);

        ModuleAttachment::saveAttachments($supplierCreditFacility, $input);

        ObjectLog::recordAction($vendorRegistration, ObjectLog::ACTION_CREATE, ObjectLog::MODULE_VENDOR_REGISTEATION_SUPPLIER_CREDIT_FACILITY);

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorManagement.approval.supplierCreditFacilities', [$vendorRegistration->id]);
    }

    public function edit($supplierCreditFacilityId)
    {
        $supplierCreditFacility = SupplierCreditFacility::find($supplierCreditFacilityId);

        $uploadedFiles = $this->getAttachmentDetails($supplierCreditFacility);

        $setting = SupplierCreditFacilitySetting::first();

        return View::make('vendor_registration.supplier_credit_facilities.edit', compact('supplierCreditFacility', 'uploadedFiles', 'setting'));
    }

    public function processorEdit($supplierCreditFacilityId)
    {
        $supplierCreditFacility = SupplierCreditFacility::find($supplierCreditFacilityId);

        $vendorRegistration = $supplierCreditFacility->vendorRegistration;

        $uploadedFiles = $this->getAttachmentDetails($supplierCreditFacility);

        $setting = SupplierCreditFacilitySetting::first();

        return View::make('vendor_management.approval.supplier_credit_facilities.edit', compact('supplierCreditFacility', 'uploadedFiles', 'setting', 'vendorRegistration'));
    }

    public function update($supplierCreditFacilityId)
    {
        $input = Input::get();

        $this->supplierCreditFacilityForm->validate($input);

        $supplierCreditFacility = SupplierCreditFacility::find($supplierCreditFacilityId);

        $supplierCreditFacility->update($input);

        ModuleAttachment::saveAttachments($supplierCreditFacility, $input);

        \Flash::success(trans('forms.saved'));

        if($supplierCreditFacility->vendorRegistration->isProcessing())
        {
            ObjectLog::recordAction($supplierCreditFacility->vendorRegistration, ObjectLog::ACTION_EDIT, ObjectLog::MODULE_VENDOR_REGISTEATION_SUPPLIER_CREDIT_FACILITY);

            return Redirect::route('vendorManagement.approval.supplierCreditFacilities', [$supplierCreditFacility->vendorRegistration->id]);
        }

        if($supplierCreditFacility->vendorRegistration->isDraft() && $supplierCreditFacility->vendorRegistration->processor)
        {
            $this->markSectionAsAmended($supplierCreditFacility->vendorRegistration);
        }

        return Redirect::route('vendors.vendorRegistration.supplierCreditFacilities');
    }

    public function destroy($recordId)
    {
        try
        {
            $record = SupplierCreditFacility::find($recordId);
            $record->delete();

            if($record->vendorRegistration->isDraft() && $record->vendorRegistration->processor)
            {
                $this->markSectionAsAmended($record->vendorRegistration);
            }

            $user = \Confide::user();

            $isVendor = ($record->vendorRegistration->company->id == $user->company->id);

            if( ! $isVendor )
            {
                ObjectLog::recordAction($record->vendorRegistration, ObjectLog::ACTION_DELETE, ObjectLog::MODULE_VENDOR_REGISTEATION_SUPPLIER_CREDIT_FACILITY);
            }

            \Flash::success(trans('forms.deleted'));
        }
        catch(\Exception $e)
        {
            \Flash::error(trans('forms.cannotBeDeleted:inUse'));
        }

        return Redirect::back();
    }

    public function resolve($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $section = $vendorRegistration->getSection(Section::SECTION_SUPPLIER_CREDIT_FACILITIES);

        $section->amendment_status  = Section::AMENDMENT_STATUS_NOT_REQUIRED;
        $section->amendment_remarks = '';
        $section->save();

        return Redirect::back();
    }

    public function markSectionAsAmended(VendorRegistration $vendorRegistration)
    {
        $section = $vendorRegistration->getSection(Section::SECTION_SUPPLIER_CREDIT_FACILITIES);

        $section->amendment_status = Section::AMENDMENT_STATUS_MADE;
        $section->save();

        return true;
    }

    public function getAttachmentsList($supplierCreditFacilityId)
	{
        $supplierCreditFacility = SupplierCreditFacility::find($supplierCreditFacilityId);

		$uploadedFiles = $this->getAttachmentDetails($supplierCreditFacility);

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

    public function getActionLogs($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $actionLogs = ObjectLog::getActionLogs($vendorRegistration, ObjectLog::MODULE_VENDOR_REGISTEATION_SUPPLIER_CREDIT_FACILITY);
        
        return Response::json($actionLogs);
    }
}