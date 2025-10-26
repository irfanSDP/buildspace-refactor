<?php

use PCK\CompanyPersonnel\CompanyPersonnel;
use PCK\Forms\CompanyPersonnelForm;
use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\VendorRegistration\Section;
use PCK\ObjectField\ObjectField;
use Carbon\Carbon;
use PCK\CompanyPersonnel\CompanyPersonnelSetting;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorManagement\InstructionSetting;
use PCK\ObjectLog\ObjectLog;

class CompanyPersonnelController extends \BaseController {

    protected $companyPersonnelForm;

    public function __construct(CompanyPersonnelForm $companyPersonnelForm)
    {
        $this->companyPersonnelForm = $companyPersonnelForm;
    }

    public function index()
    {
        $user = \Confide::user();

        $records = CompanyPersonnel::where('vendor_registration_id', '=', $user->company->vendorRegistration->id)
            ->orderBy('id', 'desc')
            ->get();

        $directorsData     = [];
        $shareholdersData  = [];
        $headOfCompanyData = [];

        foreach($records as $record)
        {
            $row = [
                'id'                    => $record->id,
                'name'                  => $record->name,
                'identification_number' => $record->identification_number,
                'email_address'         => $record->email_address,
                'contact_number'        => $record->contact_number,
                'years_of_experience'   => $record->years_of_experience,
                'designation'           => $record->designation,
                'amount_of_share'       => $record->amount_of_share,
                'holding_percentage'    => $record->holding_percentage,
                'route:edit'            => route('vendors.vendorRegistration.companyPersonnel.edit', array($record->id)),
                'route:delete'          => route('vendors.vendorRegistration.companyPersonnel.destroy', array($record->id)),
                'deletable'             => true,
            ];

            switch($record->type)
            {
                case CompanyPersonnel::TYPE_DIRECTOR:
                    $directorsData[] = $row;
                    break;
                case CompanyPersonnel::TYPE_SHAREHOLDERS:
                    $shareholdersData[] = $row;
                    break;
                case CompanyPersonnel::TYPE_HEAD_OF_COMPANY:
                    $headOfCompanyData[] = $row;
                    break;
            }
        }

        $section = $user->company->vendorRegistration->getSection(Section::SECTION_COMPANY_PERSONNEL);

        $directorUploadedFiles      = $this->getAttachmentDetails(ObjectField::findOrCreateNew($user->company->vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR));
        $shareholderUploadedFiles   = $this->getAttachmentDetails(ObjectField::findOrCreateNew($user->company->vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER));
        $headOfCompanyUploadedFiles = $this->getAttachmentDetails(ObjectField::findOrCreateNew($user->company->vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD));

        $setting = CompanyPersonnelSetting::first();

        $instructionSettings = InstructionSetting::first();

        return View::make('vendor_registration.company_personnel.index', compact('directorsData', 'shareholdersData', 'headOfCompanyData', 'section', 'directorUploadedFiles', 'shareholderUploadedFiles', 'headOfCompanyUploadedFiles', 'setting', 'instructionSettings'));
    }

    public function create()
    {
        $typeOptions = [
            CompanyPersonnel::TYPE_DIRECTOR        => trans('vendorManagement.director'),
            CompanyPersonnel::TYPE_SHAREHOLDERS    => trans('vendorManagement.shareholder'),
            CompanyPersonnel::TYPE_HEAD_OF_COMPANY => trans('vendorManagement.headOfCompany'),
        ];

        return View::make('vendor_registration.company_personnel.create', compact('typeOptions'));
    }

    public function processorCreate($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $typeOptions = [
            CompanyPersonnel::TYPE_DIRECTOR        => trans('vendorManagement.director'),
            CompanyPersonnel::TYPE_SHAREHOLDERS    => trans('vendorManagement.shareholder'),
            CompanyPersonnel::TYPE_HEAD_OF_COMPANY => trans('vendorManagement.headOfCompany'),
        ];

        return View::make('vendor_management.approval.company_personnel.create', compact('typeOptions', 'vendorRegistration'));
    }

    public function store()
    {
        $user = \Confide::user();

        $input = Input::get();

        $this->companyPersonnelForm->setVendorRegistration($user->company->vendorRegistration);
        $this->companyPersonnelForm->validate($input);

        $input['vendor_registration_id'] = $user->company->vendorRegistration->id;

        $companyPersonnel = CompanyPersonnel::createOrUpdateRecord($input);

        if($user->company->vendorRegistration->isDraft() && $user->company->vendorRegistration->processor)
        {
            $this->markSectionAsAmended($user->company->vendorRegistration);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendors.vendorRegistration.companyPersonnel');
    }

    public function processorStore($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $input = Input::get();

        $this->companyPersonnelForm->setVendorRegistration($vendorRegistration);
        $this->companyPersonnelForm->validate($input);

        $input['vendor_registration_id'] = $vendorRegistration->id;

        $companyPersonnel = CompanyPersonnel::createOrUpdateRecord($input);

        ObjectLog::recordAction($vendorRegistration, ObjectLog::ACTION_CREATE, ObjectLog::MODULE_VENDOR_REGISTRATION_COMPANY_PERSONNEL);

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorManagement.approval.companyPersonnel', [$companyPersonnel->vendorRegistration->id]);
    }

    public function edit($companyPersonnelId)
    {
        $companyPersonnel = CompanyPersonnel::find($companyPersonnelId);

        $typeOptions = [
            CompanyPersonnel::TYPE_DIRECTOR        => trans('vendorManagement.director'),
            CompanyPersonnel::TYPE_SHAREHOLDERS    => trans('vendorManagement.shareholder'),
            CompanyPersonnel::TYPE_HEAD_OF_COMPANY => trans('vendorManagement.headOfCompany'),
        ];

        return View::make('vendor_registration.company_personnel.edit', compact('companyPersonnel', 'typeOptions'));
    }

    public function processorEdit($companyPersonnelId)
    {
        $companyPersonnel = CompanyPersonnel::find($companyPersonnelId);

        $typeOptions = [
            CompanyPersonnel::TYPE_DIRECTOR        => trans('vendorManagement.director'),
            CompanyPersonnel::TYPE_SHAREHOLDERS    => trans('vendorManagement.shareholder'),
            CompanyPersonnel::TYPE_HEAD_OF_COMPANY => trans('vendorManagement.headOfCompany'),
        ];

        $vendorRegistration = $companyPersonnel->vendorRegistration;

        return View::make('vendor_management.approval.company_personnel.edit', compact('companyPersonnel', 'typeOptions', 'vendorRegistration'));
    }

    public function update($companyPersonnelId)
    {
        $input = Input::get();

        $user = \Confide::user();

        $companyPersonnel = CompanyPersonnel::find($companyPersonnelId);

        $this->companyPersonnelForm->setVendorRegistration($companyPersonnel->vendorRegistration);
        $this->companyPersonnelForm->setCompanyPersonnel($companyPersonnel);
        $this->companyPersonnelForm->validate($input);

        $companyPersonnel = CompanyPersonnel::createOrUpdateRecord($input, $companyPersonnel);

        $isVendor = ($companyPersonnel->vendorRegistration->company->id == $user->company->id);

        if( ! $isVendor )
        {
            ObjectLog::recordAction($companyPersonnel->vendorRegistration, ObjectLog::ACTION_EDIT, ObjectLog::MODULE_VENDOR_REGISTRATION_COMPANY_PERSONNEL);
        }

        \Flash::success(trans('forms.saved'));

        if($companyPersonnel->vendorRegistration->isProcessing())
        {
            return Redirect::route('vendorManagement.approval.companyPersonnel', [$companyPersonnel->vendorRegistration->id]);
        }

        if($companyPersonnel->vendorRegistration->isDraft() && $companyPersonnel->vendorRegistration->processor)
        {
            $this->markSectionAsAmended($companyPersonnel->vendorRegistration);
        }

        return Redirect::route('vendors.vendorRegistration.companyPersonnel');
    }

    public function destroy($recordId)
    {
        try
        {
            $user   = \Confide::user();
            $record = CompanyPersonnel::find($recordId);

            $record->delete();

            $isVendor = ($record->vendorRegistration->company->id == $user->company->id);

            if( ! $isVendor )
            {
                ObjectLog::recordAction($record->vendorRegistration, ObjectLog::ACTION_DELETE, ObjectLog::MODULE_VENDOR_REGISTRATION_COMPANY_PERSONNEL);
            }

            if($record->vendorRegistration->isDraft() && $record->vendorRegistration->processor)
            {
                $this->markSectionAsAmended($record->vendorRegistration);
            }

            \Flash::success(trans('forms.deleted'));
        }
        catch(\Exception $e)
        {
            \Flash::error(trans('forms.cannotBeDeleted:inUse'));
        }

        return Redirect::back();
    }

    public function markSectionAsAmended(VendorRegistration $vendorRegistration)
    {
        $section = $vendorRegistration->getSection(Section::SECTION_COMPANY_PERSONNEL);

        $section->amendment_status = Section::AMENDMENT_STATUS_MADE;
        $section->save();

        return true;
    }

    public function resolve($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $section = $vendorRegistration->getSection(Section::SECTION_COMPANY_PERSONNEL);

        $section->amendment_status  = Section::AMENDMENT_STATUS_NOT_REQUIRED;
        $section->amendment_remarks = ''; 
        $section->save();

        return Redirect::back();
    }

    public function getAttachmentListByCompanyPersonnelType($vendorRegistrationId, $type)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);
        $objectField        = ObjectField::findOrCreateNew($vendorRegistration, $type);

        $uploadedFiles = $this->getAttachmentDetails($objectField);

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

    public function getAttachmentsList($companyPersonnelId)
	{
        $companyPersonnel = CompanyPersonnel::find($companyPersonnelId);

		$uploadedFiles = $this->getAttachmentDetails($companyPersonnel);

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

    public function directorsUpload()
    {
        $input = Input::get();

        $user = \Confide::user();

        $objectField = ObjectField::findOrCreateNew($user->company->vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR);

        ModuleAttachment::saveAttachments($objectField, $input);

        if($user->company->vendorRegistration->isDraft() && $user->company->vendorRegistration->processor)
        {
            $this->markSectionAsAmended($user->company->vendorRegistration);
        }

        \Flash::success(trans('forms.uploadSuccessful'));

        return Redirect::back();
    }

    public function shareholdersUpload()
    {
        $input = Input::get();

        $user = \Confide::user();

        $objectField = ObjectField::findOrCreateNew($user->company->vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER);

        ModuleAttachment::saveAttachments($objectField, $input);

        if($user->company->vendorRegistration->isDraft() && $user->company->vendorRegistration->processor)
        {
            $this->markSectionAsAmended($user->company->vendorRegistration);
        }

        \Flash::success(trans('forms.uploadSuccessful'));

        return Redirect::back();
    }

    public function companyHeadsUpload()
    {
        $input = Input::get();

        $user = \Confide::user();

        $objectField = ObjectField::findOrCreateNew($user->company->vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD);

        ModuleAttachment::saveAttachments($objectField, $input);

        if($user->company->vendorRegistration->isDraft() && $user->company->vendorRegistration->processor)
        {
            $this->markSectionAsAmended($user->company->vendorRegistration);
        }

        \Flash::success(trans('forms.uploadSuccessful'));

        return Redirect::back();
    }

    public function processorDirectorsUpload($vendorRegistrationId)
    {
        $input = Input::get();

        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $objectField = ObjectField::findOrCreateNew($vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR);

        ModuleAttachment::saveAttachments($objectField, $input);

        \Flash::success(trans('forms.uploadSuccessful'));

        return Redirect::back();
    }

    public function processorShareholdersUpload($vendorRegistrationId)
    {
        $input = Input::get();

        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $objectField = ObjectField::findOrCreateNew($vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER);

        ModuleAttachment::saveAttachments($objectField, $input);

        \Flash::success(trans('forms.uploadSuccessful'));

        return Redirect::back();
    }

    public function processorCompanyHeadsUpload($vendorRegistrationId)
    {
        $input = Input::get();

        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $objectField = ObjectField::findOrCreateNew($vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD);

        ModuleAttachment::saveAttachments($objectField, $input);

        \Flash::success(trans('forms.uploadSuccessful'));

        return Redirect::back();
    }

    public function getDirectorsDownload()
    {
        $user = \Confide::user();

        $data = array();

        $objectField = ObjectField::findOrCreateNew($user->company->vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR);

        foreach($objectField->getAttachmentDetails() as $upload)
        {
            $data[] = array(
                'filename'    => $upload->filename,
                'download_url' => $upload->download_url,
                'uploaded_by'  => $upload->createdBy->name,
                'uploaded_at'  => Carbon::parse($upload->created_at)->format(\Config::get('dates.created_at')),
            );
        }

        return $data;
    }

    public function getShareholdersDownload()
    {
        $user = \Confide::user();

        $data = array();

        $objectField = ObjectField::findOrCreateNew($user->company->vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER);

        foreach($objectField->getAttachmentDetails() as $upload)
        {
            $data[] = array(
                'filename'    => $upload->filename,
                'download_url' => $upload->download_url,
                'uploaded_by'  => $upload->createdBy->name,
                'uploaded_at'  => Carbon::parse($upload->created_at)->format(\Config::get('dates.created_at')),
            );
        }

        return $data;
    }

    public function getCompanyHeadsDownload()
    {
        $user = \Confide::user();

        $data = array();

        $objectField = ObjectField::findOrCreateNew($user->company->vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD);

        foreach($objectField->getAttachmentDetails() as $upload)
        {
            $data[] = array(
                'filename'    => $upload->filename,
                'download_url' => $upload->download_url,
                'uploaded_by'  => $upload->createdBy->name,
                'uploaded_at'  => Carbon::parse($upload->created_at)->format(\Config::get('dates.created_at')),
            );
        }

        return $data;
    }

    public function getActionLogs($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $actionLogs = ObjectLog::getActionLogs($vendorRegistration, ObjectLog::MODULE_VENDOR_REGISTRATION_COMPANY_PERSONNEL);
        
        return Response::json($actionLogs);
    }
}