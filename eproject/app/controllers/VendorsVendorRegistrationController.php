<?php

use PCK\Helpers\DBTransaction;
use Carbon\Carbon;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorRegistration\Section;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\FormBuilder\FormObjectMapping;
use PCK\FormBuilder\DynamicForm;
use PCK\VendorRegistration\Payment\VendorRegistrationPayment;
use PCK\VendorRegistration\VendorRegistrationRepository;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\FormBuilder\Elements\Element;
use PCK\FormBuilder\Elements\SystemModuleElement;
use PCK\CompanyPersonnel\CompanyPersonnel;
use PCK\SupplierCreditFacility\SupplierCreditFacility;
use PCK\Notifications\EmailNotifier;
use PCK\CompanyPersonnel\CompanyPersonnelSetting;
use PCK\SupplierCreditFacility\SupplierCreditFacilitySetting;
use PCK\TrackRecordProject\ProjectTrackRecordSetting;
use PCK\Companies\Company;
use PCK\Forms\VendorChangeVendorGroupForm;
use PCK\VendorRegistration\CompanyTemporaryDetail;

class VendorsVendorRegistrationController extends \BaseController {

    protected $emailNotifier;
    protected $vendorRegistrationRepository;
    protected $vendorGroupForm;

    public function __construct(EmailNotifier $emailNotifier, VendorRegistrationRepository $vendorRegistrationRepository, VendorChangeVendorGroupForm $vendorGroupForm)
    {
        $this->emailNotifier                = $emailNotifier;
        $this->vendorRegistrationRepository = $vendorRegistrationRepository;
        $this->vendorGroupForm              = $vendorGroupForm;
    }

    public function index()
    {
        $user    = \Confide::user();

        $vendorRegistration = $user->company->vendorRegistration;

        $isDraft = $vendorRegistration->isDraft();

        $formObjectMappping       = FormObjectMapping::findRecord($user->company->vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);
        $registrationFormRejected = $formObjectMappping && $formObjectMappping->dynamicForm && $formObjectMappping->dynamicForm->hasRejectedElements() && ($formObjectMappping->dynamicForm->status == DynamicForm::STATUS_DESIGN_APPROVED);

        $hasRejectedPreQualifications = VendorPreQualification::where('vendor_registration_id', '=', $user->company->vendorRegistration->id)
            ->where('status_id', '=', VendorPreQualification::STATUS_REJECTED)
            ->exists();

        $companyDetailsSection            = $user->company->vendorRegistration->getSection(Section::SECTION_COMPANY_DETAILS);
        $companyPersonnelSection          = $user->company->vendorRegistration->getSection(Section::SECTION_COMPANY_PERSONNEL);
        $projectTrackRecordSection        = $user->company->vendorRegistration->getSection(Section::SECTION_PROJECT_TRACK_RECORD);
        $supplierSection                  = $user->company->vendorRegistration->getSection(Section::SECTION_SUPPLIER_CREDIT_FACILITIES);
        $vendorRegistrationPaymentSection = $user->company->vendorRegistration->getSection(Section::SECTION_PAYMENT);

        $data = [
            [
                'id'          => 'vendor_details',
                'description' => trans('vendorManagement.companyDetails'),
                'hasErrors'   => $companyDetailsSection->amendmentsRequired(),
                'hasChanges'  => $companyDetailsSection->amendmentsMade(),
                'route:view'  => $isDraft ? route('vendor.registration.details.edit') : null,
            ],
            [
                'id'          => 'vendor_registration',
                'description' => trans('vendorManagement.vendorRegistration'),
                'hasErrors'   => $registrationFormRejected,
                'route:view'  => $isDraft ? route('vendor.registration.form.show') : null,
            ],
            [
                'id'          => 'company_personnel',
                'description' => trans('vendorManagement.companyPersonnel'),
                'hasErrors'   => $companyPersonnelSection->amendmentsRequired(),
                'hasChanges'  => $companyPersonnelSection->amendmentsMade(),
                'route:view'  => $isDraft ? route('vendors.vendorRegistration.companyPersonnel') : null,
            ],
            [
                'id'                        => 'project_track_record',
                'description'               => trans('vendorManagement.projectTrackRecord'),
                'hasErrors'                 => $projectTrackRecordSection->amendmentsRequired(),
                'hasChanges'                => $projectTrackRecordSection->amendmentsMade(),
                'isApplicable'              => $projectTrackRecordSection->is_section_applicable,
                'route:view'                => $isDraft ? route('vendors.vendorRegistration.projectTrackRecord') : null,
                'route:toggleApplicability' => $isDraft ? route('section.applicability.toggle', [$projectTrackRecordSection->id]) : null,
            ]
        ];

        if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
        {
            $data[] = [
                'id'          => 'vendor_pre_qualification',
                'description' => trans('vendorPreQualification.vendorPreQualification'),
                'hasErrors'   => $hasRejectedPreQualifications,
                'route:view'  => $isDraft ? route('vendors.vendorPreQualification.index') : null,
            ];
        }

        if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_SUPPLIER_CREDIT_FACILITIES'))
        {
            $data[] = [
                'id'                        => 'supplier_credit_facilities',
                'description'               => trans('vendorManagement.supplierCreditFacilities'),
                'hasErrors'                 => $supplierSection->amendmentsRequired(),
                'hasChanges'                => $supplierSection->amendmentsMade(),
                'isApplicable'              => $supplierSection->is_section_applicable,
                'route:view'                => $isDraft ? route('vendors.vendorRegistration.supplierCreditFacilities') : null,
                'route:toggleApplicability' => $isDraft ? route('section.applicability.toggle', [$supplierSection->id]) : null,
            ];
        }

        if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_VENDOR_PAYMENT'))
        {
            $data[] = [
                'id'          => 'vendor_registration_payment',
                'description' => trans('vendorManagement.vendorRegistrationPayment'),
                'hasErrors'   => $vendorRegistrationPaymentSection->amendmentsRequired(),
                'hasChanges'  => $vendorRegistrationPaymentSection->amendmentsMade(),
                'route:view'  => $isDraft ? route('vendor.registration.payment.index') : null,
            ];
        }

        $currentUser = \Confide::user();

        $canRenew = false;
        $canUpdateExistingRegistration = false;

        if($user->company->vendorRegistration->isLatestFinalized())
        {
            if($user->company->inRenewalPeriod())
            {
                $canRenew = true;
            }
            else
            {
                $canUpdateExistingRegistration = true;
            }
        }

        $canChangeVendorGroup = ($user->company->vendorRegistration->isFirst() && $user->company->vendorRegistration->isDraft());

        return View::make('vendor_registration.overview', compact('vendorRegistration', 'data', 'canRenew', 'canUpdateExistingRegistration', 'currentUser', 'canChangeVendorGroup'));
    }

    public function edit()
    {
        $user = \Confide::user();

        $companyStatus = $user->company->company_status;
        $cidbGrade     = $user->company->cidb_grade;
        $bimLevel      = $user->company->bimLevel;

        if( ! $user->company->vendorRegistration->isFirst() )
        {
            $temporaryDetail = CompanyTemporaryDetail::findRecord($user->company->vendorRegistration);

            $companyStatus = $temporaryDetail->company_status;
            $cidbGrade     = $temporaryDetail->cidb_grade;
            $bimLevel      = $temporaryDetail->bimLevel;
        }

        if(is_null($companyStatus))
        {
            \Flash::error(trans('vendorManagement.pleaseUpdateCompanyStatus'));

            return Redirect::back();
        }

        if($user->company->isContractor() && is_null($cidbGrade))
        {
            \Flash::error(trans('vendorManagement.pleaseUpdateCidbGrade'));

            return Redirect::back();
        }

        if($user->company->isConsultant() && is_null($bimLevel))
        {
            \Flash::error(trans('vendorManagement.pleaseUpdateBimLevel'));

            return Redirect::back();
        }

        $formObjectMapping = FormObjectMapping::findRecord($user->company->vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

        if(is_null($formObjectMapping))
        {
            \Flash::error(trans('vendorManagement.pleaseFillOutRegistrationForm'));

            return Redirect::back();
        }

        if($formObjectMapping && ! $formObjectMapping->dynamicForm->isProperlyFilled())
        {
            \Flash::error(trans('vendorManagement.pleaseCompleteTheRegistrationForm'));

            return Redirect::back();
        }

        $projectTrackRecordSection = $user->company->vendorRegistration->getSection(Section::SECTION_PROJECT_TRACK_RECORD);
        $projectTrackRecordSetting = ProjectTrackRecordSetting::first();

        if($projectTrackRecordSection->is_section_applicable && $projectTrackRecordSetting->project_detail_attachments)
        {
            if(!$this->projectTrackRecordAttachmentsCheck($user->company->vendorRegistration))
            {
                \Flash::error(trans('vendorManagement.projectTrackRecordAttachmentsRequired'));

                return Redirect::back();
            }
        }

        $supplierSection               = $user->company->vendorRegistration->getSection(Section::SECTION_SUPPLIER_CREDIT_FACILITIES);
        $supplierCreditFacilitySetting = SupplierCreditFacilitySetting::first();

        if($supplierSection->is_section_applicable && $supplierCreditFacilitySetting->has_attachments)
        {
            if(!$this->supplierCreditFacilityAttachmentsCheck($user->company->vendorRegistration))
            {
                \Flash::error(trans('vendorManagement.supplierCreditFacilityAttachmentsRequired'));

                return Redirect::back();
            }
        }

        $settings = VendorRegistrationAndPrequalificationModuleParameter::first();

        $companyDetails = $user->company->getVendorRegistrationCompanyDetails();

        $vendorRegistrationDetails = [];

        $mapping = FormObjectMapping::findRecord($user->company->vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

        if($mapping && ($form = $mapping->dynamicForm))
        {
            $formElementIds = $form->getAllFormElementIdsGroupedByType();

            $elementDisplayInfo = [];

            foreach($formElementIds[Element::ELEMENT_TYPE_ID] as $id)
            {
                $element = Element::findById($id);
                array_push($vendorRegistrationDetails, $element->getSavedValuesDisplay());
            }

            foreach($formElementIds[SystemModuleElement::ELEMENT_TYPE_ID] as $id)
            {
                $systemElement = SystemModuleElement::find($id);
                array_push($vendorRegistrationDetails, $systemElement->getSavedValuesDisplay());
            }
        }

        $companyPersonnels = CompanyPersonnel::getVendorRegistrationCompanyPersonnels($user->company);
        
        $companyPersonnelSetting = CompanyPersonnelSetting::first();

        $supplierCreditFacilities = SupplierCreditFacility::getSupplierCreditFacilities($user->company);

        $projectTrackRecords = TrackRecordProject::getVendorRegistrationProjectTrackRecords($user->company);

        $vendorRegistrationPayments = VendorRegistrationPayment::getVendorRegistrationPayments($user->company);

        $companyPersonnels = $user->company->vendorRegistration->getCompanyPersonnelDownloads();

        $preQualificationDownloads = $user->company->vendorRegistration->getPreQualificationDownloads();

        return View::make('vendor_registration.declaration', 
            compact(
                'settings',
                'companyDetails',
                'vendorRegistrationDetails',
                'companyPersonnels',
                'companyPersonnelSetting',
                'supplierCreditFacilities',
                'supplierCreditFacilitySetting',
                'projectTrackRecords',
                'vendorRegistrationPayments',
                'companyPersonnels',
                'preQualificationDownloads'
            )
        );
    }

    public function update()
    {
        if( empty(Input::get('confirm')) )
        {
            \Flash::error(trans('vendorManagement.declarationFormError'));

            return Redirect::back();
        }

        $user = \Confide::user();

        $companyStatus = $user->company->company_status;
        $cidbGrade     = $user->company->cidb_grade;
        $bimLevel      = $user->company->bimLevel;

        if( ! $user->company->vendorRegistration->isFirst() )
        {
            $temporaryDetail = CompanyTemporaryDetail::findRecord($user->company->vendorRegistration);

            $companyStatus = $temporaryDetail->company_status;
            $cidbGrade     = $temporaryDetail->cidb_grade;
            $bimLevel      = $temporaryDetail->bimLevel;
        }

        if(is_null($companyStatus))
        {
            \Flash::error(trans('vendorManagement.pleaseUpdateCompanyStatus'));

            return Redirect::route('vendors.vendorRegistration.index');
        }

        if($user->company->isContractor() && is_null($cidbGrade))
        {
            \Flash::error(trans('vendorManagement.pleaseUpdateCidbGrade'));

            return Redirect::route('vendors.vendorRegistration.index');
        }

        if($user->company->isConsultant() && is_null($bimLevel))
        {
            \Flash::error(trans('vendorManagement.pleaseUpdateBimLevel'));

            return Redirect::route('vendors.vendorRegistration.index');
        }

        $formObjectMapping = FormObjectMapping::findRecord($user->company->vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

        if(is_null($formObjectMapping))
        {
            \Flash::error(trans('vendorManagement.pleaseFillOutRegistrationForm'));

            return Redirect::route('vendors.vendorRegistration.index');
        }

        if($formObjectMapping && ! $formObjectMapping->dynamicForm->isProperlyFilled())
        {
            \Flash::error(trans('vendorManagement.pleaseCompleteTheRegistrationForm'));

            return Redirect::route('vendors.vendorRegistration.index');
        }

        $user->company->removeTemporaryLoginAccountValidity();

        $this->vendorRegistrationRepository->submitVendorRegistration($user->company->vendorRegistration, $user->id);

        $this->emailNotifier->sendVendorSubmitRegistrationFormApprovalRequiredNotification($user->company->vendorRegistration);

        $this->emailNotifier->sendVendorSubmitRegistrationFormNotification($user->company->vendorRegistration);

        \Flash::success(trans('vendorManagement.vendorRegistrationSubmissionSuccess'));

        return Redirect::route('vendors.vendorRegistration.index');
    }

    public function startUpdate()
    {
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $user = \Confide::user();

            $user->company->vendorRegistration->createNewRevision(false);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            \Log::error($e->getMessage());
            $transaction->rollback();
        }

        if($success)
        {
            \Flash::success(trans('forms.createdNewDraft'));
        }
        else
        {
            \Flash::error(trans('general.anErrorHasOccured'));
        }

        return Redirect::back();
    }

    public function startRenewal()
    {
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();
    
            $user = \Confide::user();

            $user->company->vendorRegistration->createNewRevision(true);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            \Log::error($e->getMessage());
            $transaction->rollback();
        }

        if($success)
        {
            \Flash::success(trans('vendorManagement.renewalApplicationStarted'));
        }
        else
        {
            \Flash::error(trans('general.anErrorHasOccured'));
        }

        return Redirect::back();
    }

    public function discardDraftRevision()
    {
        $user = \Confide::user();

        $vendorRegistration = $user->company->vendorRegistration;

        if($vendorRegistration->isDraft())
        {
            $vendorRegistration->delete();
        }

        \Flash::success(trans('forms.discardedChanges'));

        return Redirect::back();
    }

    public function toggleSectionApplicability($sectionId)
    {
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();
    
            $section = Section::find($sectionId);
            $section->is_section_applicable = !$section->is_section_applicable;
            $section->save();
    
            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }

        return Response::json(['success' => true]);
    }

    public function projectTrackRecordAttachmentsCheck(VendorRegistration $vendorRegistration)
    {
        $records = TrackRecordProject::where('vendor_registration_id', $vendorRegistration->id)->get();
        $pass    = true; 

        foreach($records as $record)
        {
            if($record->attachments->count() > 0) continue;

            $pass = false;

            break;
        }

        return $pass;
    }

    public function supplierCreditFacilityAttachmentsCheck(VendorRegistration $vendorRegistration)
    {
        $records = SupplierCreditFacility::where('vendor_registration_id', $vendorRegistration->id)->get();
        $pass    = true;

        foreach($records as $record)
        {
            if($record->attachments->count() > 0) continue;

            $pass = false;

            break;
        }

        return $pass;
    }

    public function vendorGroupEdit()
    {
        $user               = \Confide::user();
        $vendorRegistration = $user->company->vendorRegistration;
        $company            = $user->company;

        $multipleVendorCategories = VendorRegistrationAndPrequalificationModuleParameter::getValue('allow_only_one_comp_to_reg_under_multi_vendor_category');

        $urlContractGroupCategories = route('registration.externalVendors.contractGroupCategories');
        $urlVendorCategories        = route('registration.vendorCategories');

        JavaScript::put(compact('urlContractGroupCategories', 'urlVendorCategories'));

        return View::make('vendor_registration.vendor_group_edit', compact('user', 'company', 'multipleVendorCategories'));
    }

    public function vendorGroupUpdate()
    {
        $success = false;
        $inputs  = Input::all();
        $user    = \Confide::user();
        $company = $user->company;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->vendorGroupForm->validate($inputs);

            $vendorGroupChanged = (trim($inputs['contract_group_category_id']) != trim($company->contract_group_category_id));

            if($vendorGroupChanged)
            {
                $company->contract_group_category_id = trim($inputs['contract_group_category_id']);
                $company->cidb_grade                 = null;
                $company->bim_level_id               = null;
                $company->save();

                // reload company model
                $company = Company::find($company->id);

                $company->flushRelatedVendorRegistrationData();
    
                FormObjectMapping::createAndBindVendorRegistrationForm($company);
            }

            $company->vendorCategories()->sync($inputs['vendor_category_id']);

            $transaction->commit();

            $success = true;

            \Flash::success(trans('vendorManagement.vendorGroupChangedSuccessfully'));
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            \Log::error($e->getMessage());
        }
        catch(FormValidationException $e)
        {
            \Flash::error(trans('forms.formValidationError'));

            return Redirect::back()->withErrors($e->getErrors(), 'company')->withInput(Input::all());        
        }

        return Redirect::route('vendors.vendorRegistration.index');
    }
}