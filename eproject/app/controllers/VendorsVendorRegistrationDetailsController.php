<?php

use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorRegistration\Section;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\FormBuilder\FormObjectMapping;
use PCK\FormBuilder\DynamicForm;
use PCK\VendorRegistration\Payment\VendorRegistrationPayment;
use PCK\ObjectField\ObjectField;
use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\VendorDetailSetting\VendorDetailSetting;
use PCK\Forms\VendorRegistationCompanyDetailsForm;
use PCK\VendorDetailSetting\VendorDetailAttachmentSetting;
use PCK\VendorRegistration\CompanyTemporaryDetail;
use PCK\VendorRegistration\VendorCategoryTemporaryRecord;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\Companies\Company;
use PCK\ObjectLog\ObjectLog;
use PCK\ContractGroupCategory\ContractGroupCategory;
use Laracasts\Validation\FormValidationException;
use PCK\CIDBCodes\CIDBCode;
use PCK\CIDBGrades\CIDBGrade;

class VendorsVendorRegistrationDetailsController extends \BaseController {

    private $companyDetailsForm;

    public function __construct(VendorRegistationCompanyDetailsForm $companyDetailsForm)
    {
        $this->companyDetailsForm = $companyDetailsForm;
    }

    public function edit()
    {
        $user               = \Confide::user();
        $company            = $user->company;
        $settings           = VendorDetailSetting::first();
        $attachmentSettings = VendorDetailAttachmentSetting::first();

        $section = $company->vendorRegistration->getSection(Section::SECTION_COMPANY_DETAILS);

        $multipleVendorCategories  = VendorRegistrationAndPrequalificationModuleParameter::getValue('allow_only_one_comp_to_reg_under_multi_vendor_category');

        if($temporaryDetails = CompanyTemporaryDetail::findRecord($user->company->vendorRegistration))
        {
            $company = $temporaryDetails->getCompanyWithDraftData();
        }

        $vendorCategories = $company->contractGroupCategory->vendorCategories()->orderBy('id', 'ASC')->get();

        $selectedVendorCategoryIds = Input::old('vendor_category_id') ?? $company->vendorCategories()->lists('id');

        $selectedCidbCodeIds = [];

        if($company->cidbCodes)
        {
            foreach($company->cidbCodes as $cidbCode)
            {
                $selectedCidbCodeIds[] = $cidbCode->id;
            }
        }

        $temporaryVendorCagetoryIds = VendorCategoryTemporaryRecord::getTemporaryVendorCategoryIds($user->company->vendorRegistration);

        if(count($temporaryVendorCagetoryIds) > 0)
        {
            $selectedVendorCategoryIds = $temporaryVendorCagetoryIds;
        }

        if(CIDBGrade::count() > 0)
        {
            $cidb_grades = CIDBGrade::orderBy('id', 'ASC')->get();
        }

        $cidbCodeParents = [];

        if(CIDBCode::count() > 0)
        {
            $cidbCodeParents = CIDBCode::where("parent_id", null)->orderBy('id', 'ASC')->get();

            if ($cidbCodeParents) 
            {
                foreach ($cidbCodeParents as $cidbCodeParent) 
                {
                    $cidbCodeParent->children = CIDBCode::where("parent_id", $cidbCodeParent->id)->orderBy('id', 'ASC')->get();

                    if ($cidbCodeParent->children) 
                    {
                        foreach ($cidbCodeParent->children as $cidbCodeChildren) 
                        {
                            $cidbCodeChildren->subChildren = CIDBCode::where("parent_id", $cidbCodeChildren->id)->orderBy('id', 'ASC')->get();
                        }
                    }
                }
            }
        }

        $cidbCodes = CIDBCode::getCidbCodes();
       
        $companyStatusDescriptions = Company::getCompanyStatusDescriptions();

        $urlCountry = route('country');
        $urlStates  = route('country.states');
        $stateId    = Input::old('state_id', $company->state_id);
        $countryId  = Input::old('country_id', $company->country_id);

        JavaScript::put(compact('urlCountry', 'urlStates', 'countryId', 'stateId'));

        return View::make('vendor_registration.vendor_details.edit', [
            'settings'                  => $settings,
            'attachmentSettings'        => $attachmentSettings,
            'company'                   => $company,
            'section'                   => $section,
            'multipleVendorCategories'  => $multipleVendorCategories,
            'vendorCategories'          => $vendorCategories,
            'selectedVendorCategoryIds' => $selectedVendorCategoryIds,
            'companyStatusDescriptions' => $companyStatusDescriptions,
            'cidb_grades'               => $cidb_grades,
            'cidbCodeParents'           => $cidbCodeParents,
            'cidbCodes'                 => $cidbCodes,
            'selectedCidbCodeIds'       => $selectedCidbCodeIds,
        ]);
    }

    public function update()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $user     = \Confide::user();
            $company  = Company::find($inputs['company_id']);
            $isVendor = ($user->company && $company->id == $user->company->id);

            $this->companyDetailsForm->setCompany($company);

            if($isVendor)
            {
                $this->companyDetailsForm->setContextAsVendor();
            }

            $this->companyDetailsForm->ignoreUnique($company->id);
            $this->companyDetailsForm->validate($inputs);

            if($company->vendorRegistration->isFirst())
            {
                $company->name                  = trim($inputs['name']);
                $company->address               = trim($inputs['address']);
                $company->main_contact          = trim($inputs['main_contact']);
                $company->tax_registration_no   = trim($inputs['tax_registration_number']);
                $company->email                 = trim($inputs['email']);
                $company->telephone_number      = trim($inputs['telephone_number']);
                $company->fax_number            = trim($inputs['fax_number']);

                if($isVendor)
                {
                    $company->country_id   = $inputs['country_id'];
                    $company->state_id     = $inputs['state_id'];
                    $company->reference_no = trim($inputs['reference_no']);
                }

                $company->company_status        = $inputs['company_status'];
                $company->bumiputera_equity     = trim($inputs['bumiputera_equity']);
                $company->non_bumiputera_equity = trim($inputs['non_bumiputera_equity']);
                $company->foreigner_equity      = trim($inputs['foreigner_equity']);
                $company->cidb_grade            = isset($inputs['cidb_grade']) ? $inputs['cidb_grade'] : null;
                $company->bim_level_id          = isset($inputs['bim_level_id']) ? $inputs['bim_level_id'] : null;
                $company->save();

                if(isset($inputs['vendor_category_id']))
                {
                    $company->vendorCategories()->sync($inputs['vendor_category_id']);
                }

                if(isset($inputs['cidb_code_id']))
                {
                    $company->cidbCodes()->sync($inputs['cidb_code_id']);
                }
            }
            else
            {

                $temporaryDetails = CompanyTemporaryDetail::findRecord($company->vendorRegistration);
                $temporaryDetails->updateValues($inputs);

                $company->cidb_grade = isset($inputs['cidb_grade']) ? $inputs['cidb_grade'] : null;
                $company->save();

                if(isset($inputs['cidb_code_id']))
                {
                    $company->cidbCodes()->sync($inputs['cidb_code_id']);
                }

                if(isset($inputs['vendor_category_id']))
                {
                    VendorCategoryTemporaryRecord::syncValues($company->vendorRegistration, $inputs['vendor_category_id']);
                }
            }

            // only run on re-submission after rejection by processor
            if($company->vendorRegistration->isDraft() && $company->vendorRegistration->processor)
            {
                $section = $company->vendorRegistration->getSection(Section::SECTION_COMPANY_DETAILS);

                $section->amendment_status = Section::AMENDMENT_STATUS_MADE;
                $section->save();
            }

            if( ! $isVendor )
            {
                ObjectLog::recordAction($company, ObjectLog::ACTION_EDIT, ObjectLog::MODULE_VENDOR_REGISTRATION_COMPANY_DETAILS);
            }

            $success = true;
        }
        catch(FormValidationException $e)
        {
            $errors = $e->getErrors();
        }
        catch(Exception $e)
        {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            Flash::error(trans('forms.anErrorOccured'));

            return Redirect::back();
        }

        if($success)
        {
            Flash::success(trans('forms.formSubmitSuccessful'));

            if($company->vendorRegistration->isProcessing())
            {
                return Redirect::route('vendorManagement.approval.registrationAndPreQualification.show', [$company->vendorRegistration->id]);
            }

            return Redirect::back();
        }
        else
        {
            Flash::error(trans('forms.formValidationError'));

            return Redirect::to(URL::previous())->withErrors($errors)->withArrayInput($inputs);
        }
    }

    public function attachmentsUpdate($companyId, $field)
    {
        $company = Company::find($companyId);
        $inputs  = Input::all();
        $object  = ObjectField::findOrCreateNew($company, $field);

        ModuleAttachment::saveAttachments($object, $inputs);

		return array(
			'success' => true,
		);
    }

    public function getAttachmentCount($companyId, $field)
    {
        $company = Company::find($companyId);
        $object  = ObjectField::findOrCreateNew($company, $field);

        return Response::json([
            'field'           => $field,
            'attachmentCount' => count($this->getAttachmentDetails($object)),
        ]);
    }

    public function getAttachmentsList($companyId, $field)
	{
        $company       = Company::find($companyId);
        $object        = ObjectField::findOrCreateNew($company, $field);
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

    public function resolve($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $section = $vendorRegistration->getSection(Section::SECTION_COMPANY_DETAILS);

        $section->amendment_status  = Section::AMENDMENT_STATUS_NOT_REQUIRED;
        $section->amendment_remarks = '';
        $section->save();

        return Redirect::back();
    }

    public function getActionLogs($companyId)
    {
        $company = Company::find($companyId);

        $actionLogs = ObjectLog::getActionLogs($company, ObjectLog::MODULE_VENDOR_REGISTRATION_COMPANY_DETAILS);
        
        return Response::json($actionLogs);
    }
}