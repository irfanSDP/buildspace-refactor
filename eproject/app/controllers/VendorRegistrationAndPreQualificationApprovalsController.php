<?php

use Carbon\Carbon;
use PCK\Base\Helpers;
use PCK\CIDBCodes\CIDBCode;
use PCK\CIDBGrades\CIDBGrade;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorRegistration\Section;
use PCK\Verifier\Verifier;
use PCK\Verifier\VerifierRepository;
use PCK\FormBuilder\FormObjectMapping;
use PCK\FormBuilder\DynamicForm;
use PCK\VendorRegistration\Payment\VendorRegistrationPayment;
use PCK\CompanyPersonnel\CompanyPersonnel;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\SupplierCreditFacility\SupplierCreditFacility;
use PCK\VendorDetailSetting\VendorDetailSetting;
use PCK\VendorDetailSetting\VendorDetailAttachmentSetting;
use PCK\ObjectField\ObjectField;
use PCK\Helpers\ModuleAttachment;
use PCK\CompanyPersonnel\CompanyPersonnelSetting;
use PCK\SupplierCreditFacility\SupplierCreditFacilitySetting;
use PCK\TrackRecordProject\ProjectTrackRecordSetting;
use PCK\VendorRegistration\CompanyTemporaryDetail;
use PCK\Notifications\EmailNotifier;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\VendorRegistration\VendorCategoryTemporaryRecord;
use PCK\VendorManagement\InstructionSetting;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Companies\Company;
use PCK\VendorRegistration\SubmissionLog;
use PCK\Helpers\DBTransaction;
use PCK\Users\User;
use PCK\Users\UserCompanyLog;
use PCK\VendorRegistration\ProcessorDeleteCompanyLog;
use PCK\VendorManagement\VendorManagementUserPermission;
use PCK\Forms\VendorRegistrationAssignProcessorForm;

class VendorRegistrationAndPreQualificationApprovalsController extends \BaseController {

    protected $verifierRepository;
    protected $emailNotifier;
    protected $vendorRegistrationAssignProcessorForm;

    public function __construct(VerifierRepository $verifierRepository, EmailNotifier $emailNotifier, VendorRegistrationAssignProcessorForm $vendorRegistrationAssignProcessorForm)
    {
        $this->verifierRepository                    = $verifierRepository;
        $this->emailNotifier                         = $emailNotifier;
        $this->vendorRegistrationAssignProcessorForm = $vendorRegistrationAssignProcessorForm;
    }

    public function index()
    {
        $statusFilterOptions = [
            0 => trans('general.all'),
            VendorRegistration::STATUS_DRAFT => trans('forms.draft'),
            VendorRegistration::STATUS_SUBMITTED => trans('forms.submitted'),
            VendorRegistration::STATUS_PROCESSING => trans('forms.processing'),
            VendorRegistration::STATUS_PENDING_VERIFICATION => trans('forms.pendingForApproval'),
            VendorRegistration::STATUS_COMPLETED => trans('forms.completed'),
            VendorRegistration::STATUS_REJECTED => trans('forms.rejected'),
            VendorRegistration::STATUS_EXPIRED => trans('forms.expired'),
        ];

        $submissionTypeFilterOptions = [
            0 => trans('general.all'),
            VendorRegistration::SUBMISSION_TYPE_NEW => trans('vendorManagement.newRegistration'),
            VendorRegistration::SUBMISSION_TYPE_RENEWAL => trans('vendorManagement.renewal'),
            VendorRegistration::SUBMISSION_TYPE_UPDATE => trans('vendorManagement.update')
        ];

        $externalVendorGroups = ContractGroupCategory::where('hidden', false)->where('type', ContractGroupCategory::TYPE_EXTERNAL)->get();

        $externalVendorGroupsFilterOptions = [];

        $externalVendorGroupsFilterOptions[0] = trans('general.all');

        foreach($externalVendorGroups as $vendorGroup)
        {
            $externalVendorGroupsFilterOptions[$vendorGroup->id] = $vendorGroup->name;
        }

        return View::make('vendor_management.approval.registration_and_pre_qualification.index', compact('statusFilterOptions', 'submissionTypeFilterOptions', 'externalVendorGroupsFilterOptions'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $query = "WITH ongoing_vendor_registrations AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY company_id ORDER BY revision DESC) AS rank, * FROM vendor_registrations WHERE deleted_at IS NULL AND status != " . VendorRegistration::STATUS_COMPLETED . "
                  )
                  SELECT vr.id, c.name as company_name, c.reference_no, c.purge_date, cgc.id vendor_group_id, cgc.name as vendor_group, ARRAY_TO_JSON(ARRAY_AGG(vc.name)) as vendor_categories, vr.submitted_at, u.name AS processor_name,
                  CASE 
                      WHEN (c.purge_date IS NOT NULL AND c.purge_date < NOW()) THEN '" . VendorRegistration::STATUS_EXPIRED . "'
                      ELSE vr.status
                  END AS status,
                  CASE 
                      WHEN (vr.submission_type = " . VendorRegistration::SUBMISSION_TYPE_NEW . ") THEN '" . trans('vendorManagement.newRegistration') . "'
                      WHEN (vr.submission_type = " . VendorRegistration::SUBMISSION_TYPE_RENEWAL . ") THEN '" . trans('vendorManagement.renewal') . "'
                      ELSE '" . trans('vendorManagement.update') . "'
                  END AS submission_type_text
                  FROM ongoing_vendor_registrations vr
                  INNER JOIN companies c ON c.id = vr.company_id
                  INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                  LEFT OUTER JOIN company_vendor_category cvc ON cvc.company_id = c.id
                  LEFT OUTER JOIN vendor_categories vc ON vc.id = cvc.vendor_category_id 
                  LEFT OUTER JOIN vendor_registration_processors vrp ON vrp.vendor_registration_id = vr.id AND vrp.deleted_at IS NULL
                  LEFT OUTER JOIN users u ON u.id = vrp.user_id
                  WHERE vr.rank = 1
                  AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
                  AND cgc.hidden IS FALSE ";

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!is_array($filters['value']))//tabulator will send select type filter in form of array upon clicking. we are only interested in single selection
                {
                    $val = trim($filters['value']);
                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'company':
                            if(strlen($val) > 0)
                            {
                                $query .= "AND c.name ILIKE '%{$val}%' ";
                            }
                            break;
                        case 'reference_no':
                            if(strlen($val) > 0)
                            {
                                $query .= "AND c.reference_no ILIKE '%{$val}%' ";
                            }
                            break;
                        case 'vendor_group':
                            if((int)$val > 0)
                            {
                                $query .= "AND cgc.id = {$val} ";
                            }
                            break;
                        case 'vendor_categories':
                            if(strlen($val) > 0)
                            {
                                $query .= "AND c.reference_no ILIKE '%{$val}%' ";
                            }
                            break;
                        case 'status':
                            if((int)$val > 0)
                            {
                                if($val == VendorRegistration::STATUS_EXPIRED)
                                {
                                    $query .= "AND c.purge_date IS NOT NULL AND c.purge_date < NOW() ";
                                }
                                else
                                {
                                    $query .= "AND vr.status = " . (int) $val . " ";
                                }
                            }
                            break;
                        case 'submission_type':
                            if((int)$val > 0)
                            {
                                $query .= "AND vr.submission_type = {$val} ";
                            }
                            break;
                        case 'processor':
                            if(strlen($val) > 0)
                            {
                                $query .= "AND u.name ILIKE '%{$val}%' ";
                            }
                            break;
                    }
                }
            }
        }

        $query .= "GROUP BY vr.id, c.id, cgc.id, vr.submitted_at, vr.submission_type, vr.status, u.id ";
        $query .= "ORDER BY c.id ASC ";

        $rowCount = count(DB::select(DB::raw($query)));

        $query .= "LIMIT {$limit} OFFSET " . $limit * ($page - 1) . ";";

        $vendorRegistrations = DB::select(DB::raw($query));

        $data = [];

        foreach($vendorRegistrations as $key => $vendorRegistration)
        {
            $assignableStatuses = [VendorRegistration::STATUS_SUBMITTED, VendorRegistration::STATUS_PROCESSING];

            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                => $vendorRegistration->id,
                'counter'           => $counter,
                'company'           => $vendorRegistration->company_name,
                'reference_no'      => $vendorRegistration->reference_no,
                'vendor_group'      => $vendorRegistration->vendor_group,
                'vendor_categories' => empty(json_decode($vendorRegistration->vendor_categories)) ? trans('general.notAvailable') : implode(', ', json_decode($vendorRegistration->vendor_categories)),
                'status'            => VendorRegistration::getStatusText($vendorRegistration->status),
                'expiry_date'       => is_null($vendorRegistration->purge_date) ? '-' : Carbon::parse($vendorRegistration->purge_date)->format(\Config::get('dates.standard')),
                'expiry_alert'      => is_null($vendorRegistration->purge_date) ? false : Carbon::parse($vendorRegistration->purge_date)->subDays(7)->lt(Carbon::now()),
                'submission_type'   => $vendorRegistration->submission_type_text,
                'submitted_date'    => is_null($vendorRegistration->submitted_at) ? null : Carbon::parse($vendorRegistration->submitted_at)->format(\Config::get('dates.full_format')),
                'processor'         => $vendorRegistration->processor_name,
                'route:view'        => route('vendorManagement.approval.registrationAndPreQualification.show', $vendorRegistration->id),
                'route:assign'      => in_array($vendorRegistration->status, $assignableStatuses) ? route('vendorManagement.approval.registrationAndPreQualification.assignForm', $vendorRegistration->id) : null,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function show($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $formObjectMappping       = FormObjectMapping::findRecord($vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);
        $registrationFormRejected = $formObjectMappping && $formObjectMappping->dynamicForm && $formObjectMappping->dynamicForm->hasRejectedElements() && ($formObjectMappping->dynamicForm->status == DynamicForm::STATUS_DESIGN_APPROVED);

        $hasRejectedPreQualifications = VendorPreQualification::where('vendor_registration_id', '=', $vendorRegistration->id)
            ->where('status_id', '=', VendorPreQualification::STATUS_REJECTED)
            ->exists();

        $companyDetailsSection = $vendorRegistration->getSection(Section::SECTION_COMPANY_DETAILS);
        $companyPersonnelSection = $vendorRegistration->getSection(Section::SECTION_COMPANY_PERSONNEL);
        $projectTrackRecordSection = $vendorRegistration->getSection(Section::SECTION_PROJECT_TRACK_RECORD);
        $supplierSection = $vendorRegistration->getSection(Section::SECTION_SUPPLIER_CREDIT_FACILITIES);
        $vendorRegistrationPaymentSection  = $vendorRegistration->getSection(Section::SECTION_PAYMENT);
        $vendorRegistrationPaymentRejected = $vendorRegistrationPaymentSection->isRejected();

        $data = [
            [
                'id'          => 'vendor_details',
                'description' => trans('vendorManagement.companyDetails'),
                'hasErrors'   => $companyDetailsSection->amendmentsRequired(),
                'hasChanges'  => $companyDetailsSection->amendmentsMade(),
                'route:view'  => route('vendorManagement.approval.companyDetails', array($vendorRegistrationId)),
            ],
            [
                'id'          => 'vendor_registration',
                'description' => trans('vendorManagement.vendorRegistration'),
                'hasErrors'   => $registrationFormRejected,
                'route:view'  => route('vendorManagement.approval.registration', array($vendorRegistrationId)),
            ],
            [
                'id'          => 'company_personnel',
                'description' => trans('vendorManagement.companyPersonnel'),
                'hasErrors'   => $companyPersonnelSection->amendmentsRequired(),
                'hasChanges'  => $companyPersonnelSection->amendmentsMade(),
                'route:view'  => route('vendorManagement.approval.companyPersonnel', array($vendorRegistrationId)),
            ],
            [
                'id'          => 'project_track_record',
                'description' => trans('vendorManagement.projectTrackRecord'),
                'hasErrors'   => $projectTrackRecordSection->amendmentsRequired(),
                'hasChanges'  => $projectTrackRecordSection->amendmentsMade(),
                'route:view'  => route('vendorManagement.approval.projectTrackRecord', array($vendorRegistrationId)),
            ]
        ];

        if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
        {
            $data[] = [
                'id'          => 'vendor_pre_qualification',
                'description' => trans('vendorPreQualification.vendorPreQualification'),
                'hasErrors'   => $hasRejectedPreQualifications,
                'route:view'  => route('vendorManagement.approval.preQualification', array($vendorRegistrationId)),
            ];
        }

        if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_SUPPLIER_CREDIT_FACILITIES'))
        {
            $data[] = [
                'id'          => 'supplier_credit_facilities',
                'description' => trans('vendorManagement.supplierCreditFacilities'),
                'hasErrors'   => $supplierSection->amendmentsRequired(),
                'hasChanges'  => $supplierSection->amendmentsMade(),
                'route:view'  => route('vendorManagement.approval.supplierCreditFacilities', array($vendorRegistrationId)),
            ];
        }

        if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_VENDOR_PAYMENT'))
        {
            $data[] = [
                'id'          => 'vendor_registration_payment',
                'description' => trans('vendorManagement.vendorRegistrationPayment'),
                'hasErrors'   => $vendorRegistrationPaymentSection->amendmentsRequired(),
                'hasChanges'  => $vendorRegistrationPaymentSection->amendmentsMade(),
                'route:view'  => route('vendorManagement.approval.payment', array($vendorRegistration->company->id)),
            ];
        }

        $user = \Confide::user();

        $canProcess = $vendorRegistration->processor && ($vendorRegistration->processor->user_id == $user->id) && $vendorRegistration->isProcessing();

        $isVerifier = Verifier::isCurrentVerifier($user, $vendorRegistration);

        $mustReject = $hasRejectedPreQualifications ||
            $registrationFormRejected ||
            $vendorRegistrationPaymentRejected ||
            $companyDetailsSection->isRejected() ||
            $companyPersonnelSection->isRejected() ||
            $projectTrackRecordSection->isRejected() ||
            $supplierSection->isRejected();

        $canApprove = !$mustReject;

        $verifiers = VendorManagementUserPermission::getUsers(VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION)
            ->merge(VendorManagementUserPermission::getUsers(VendorManagementUserPermission::TYPE_VENDOR_REGISTRATION_VERIFIER));

        foreach($verifiers as $key => $verifier)
        {
            if($verifier->id == $user->id) unset($verifiers[$key]);
        }

        $assignedVerifierRecords = Verifier::getAssignedVerifierRecords($vendorRegistration, true);

        $canBeDeleted = $vendorRegistration->isFirst() && (! $vendorRegistration->isCompleted()) && ($vendorRegistration->processor && $vendorRegistration->processor->user_id == $user->id) && $vendorRegistration->isProcessing();

        return View::make('vendor_management.approval.registration_and_pre_qualification.show', compact('data', 'vendorRegistration', 'isVerifier', 'canApprove', 'verifiers', 'canProcess', 'assignedVerifierRecords', 'canBeDeleted'));
    }

    public function submit($vendorRegistrationId)
    {
        $user = \Confide::user();

        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        if(Input::get('submit') == 'approve')
        {
            if(Input::get('verifiers'))
            {
                $verifierIds = array_filter(Input::get('verifiers'), function($id) {
                    if((trim($id) != '')) return $id;
                });
    
                $vendorRegistration->status = VendorRegistration::STATUS_PENDING_VERIFICATION;
                $vendorRegistration->save();
    
                $vendorRegistration->processor->updateRemarks(Input::get('remarks'));
    
                $this->verifierRepository->setVerifiers($verifierIds, $vendorRegistration);
                $this->verifierRepository->executeFollowUp($vendorRegistration);
    
                $verifiersInLine = Verifier::getVerifiersInLine($vendorRegistration);
    
                if($verifiersInLine && ($verifiersInLine->count() > 0))
                {
                    $this->emailNotifier->sendVendorRegistrationSubmittedForApprovalNotification($vendorRegistration, [$verifiersInLine->first()->id]);
    
                    SubmissionLog::logAction($vendorRegistration, SubmissionLog::SUBMITTED_FOR_APPROVAL);
                }
                else
                {
                    if($vendorRegistration->isSubmissionTypeNew())
                    {
                        $this->emailNotifier->sendVendorRegistrationSuccessfulNotification($vendorRegistration);
                    }
                    else
                    {
                        $this->emailNotifier->sendVendorRegistrationUpdateOrRenewalApprovedNotification($vendorRegistration);
                    }
                    
                    SubmissionLog::logAction($vendorRegistration, SubmissionLog::APPROVED);
                }
            }
            else
            {
                $vendorRegistration->status = VendorRegistration::STATUS_COMPLETED;
                $vendorRegistration->save();
                
                Verifier::setVerifierAsApproved(\Confide::user(), $vendorRegistration);
                $this->verifierRepository->executeFollowUp($vendorRegistration);

                if($vendorRegistration->isSubmissionTypeNew())
                {
                    $this->emailNotifier->sendVendorRegistrationSuccessfulNotification($vendorRegistration);
                }
                else
                {
                    $this->emailNotifier->sendVendorRegistrationUpdateOrRenewalApprovedNotification($vendorRegistration);
                }
                
                SubmissionLog::logAction($vendorRegistration, SubmissionLog::APPROVED);
            }

            \Flash::success(trans('forms.approveSuccessful'));

        }
        elseif(Input::get('submit') == 'reject')
        {
            $vendorRegistration->rejectApplication();

            $vendorRegistration->processor->updateRemarks(Input::get('remarks'));

            VendorPreQualification::syncLatestForms($vendorRegistration);

            if($vendorRegistration->isSubmissionTypeNew() || $vendorRegistration->isSubmissionTypeUpdate() || $vendorRegistration->isSubmissionTypeRenewal())
            {
                $this->emailNotifier->sendRejectVendorRegistrationFormNotification($vendorRegistration);
            }

            SubmissionLog::logAction($vendorRegistration, SubmissionLog::REJECTED);

            \Flash::success(trans('forms.rejectSuccessful'));
        }

        return Redirect::route('vendorManagement.approval.registrationAndPreQualification');
    }

    public function approve($vendorRegistrationId)
    {
        $user = \Confide::user();

        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        if(Input::has('remarks')) $this->verifierRepository->updateVerifierRemarks($vendorRegistration, Input::get('remarks'));

        if(Input::get('submit') == 'approve')
        {
            $this->verifierRepository->approve($vendorRegistration, true);

            $verifiersInLine = Verifier::getVerifiersInLine($vendorRegistration);

            if($verifiersInLine && ($verifiersInLine->count() > 0))
            {
                $this->emailNotifier->sendVendorRegistrationSubmittedForApprovalNotification($vendorRegistration, [$verifiersInLine->first()->id]);
            }
            else
            {
                if($vendorRegistration->isSubmissionTypeNew())
                {
                    $this->emailNotifier->sendVendorRegistrationSuccessfulNotification($vendorRegistration);
                }
                else
                {
                    $this->emailNotifier->sendVendorRegistrationUpdateOrRenewalApprovedNotification($vendorRegistration);
                }
            }

            \Flash::success(trans('forms.approveSuccessful'));
        }
        elseif(Input::get('submit') == 'reject')
        {
            $this->verifierRepository->approve($vendorRegistration, false);

            // send to processor only
            $this->emailNotifier->sendVerifierRejectedVendorRegistrationNotification($vendorRegistration);

            \Flash::success(trans('forms.rejectSuccessful'));
        }

        return Redirect::back();
    }

    public function getCompanyDetailsAttachmentsList($vendorRegistrationId, $field)
	{
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);
        $object             = ObjectField::findOrCreateNew($vendorRegistration->company, $field);
		$uploadedFiles      = $this->getAttachmentDetails($object);

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

    public function getProcessorAttachmentsList($vendorRegistrationId, $field)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);
        $object             = ObjectField::findOrCreateNew($vendorRegistration, $field);
		$uploadedFiles      = $this->getAttachmentDetails($object);

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

    public function uploadProcessorAttachments($vendorRegistrationId, $field)
    {
        $inputs             = Input::all();
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);
        $object             = ObjectField::findOrCreateNew($vendorRegistration, $field);

        ModuleAttachment::saveAttachments($object, $inputs);

		return array(
			'success' => true,
		);
    }

    public function getProcessorAttachmentsCount($vendorRegistrationId, $field)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);
        $object             = ObjectField::findOrCreateNew($vendorRegistration, $field);

        return Response::json([
            'name'            => $field,
            'attachmentCount' => count($this->getAttachmentDetails($object)),
        ]);
    }

    public function companyDetails($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);
        $settings           = VendorDetailSetting::first();
        $attachmentSettings = VendorDetailAttachmentSetting::first();
        $section            = $vendorRegistration->getSection(Section::SECTION_COMPANY_DETAILS);

        $company = $vendorRegistration->company;

        if($temporaryDetails = CompanyTemporaryDetail::findRecord($vendorRegistration))
        {
            $company = $temporaryDetails->getCompanyWithDraftData();
        }

        $canReject = !$section->isRejected() && (($vendorRegistration->isProcessing() && $vendorRegistration->processor && $vendorRegistration->processor->user_id == \Confide::user()->id) || ($vendorRegistration->isPendingVerification() && Verifier::isCurrentVerifier(\Confide::user(), $vendorRegistration)));

        $multipleVendorCategories  = VendorRegistrationAndPrequalificationModuleParameter::getValue('allow_only_one_comp_to_reg_under_multi_vendor_category');

        $vendorCategories = $company->contractGroupCategory->vendorCategories()->orderBy('id', 'ASC')->get();

        $selectedVendorCategoryIds = Input::old('vendor_category_id') ?? $company->vendorCategories()->lists('id');

        $temporaryVendorCagetoryIds = VendorCategoryTemporaryRecord::getTemporaryVendorCategoryIds($company->vendorRegistration);

        if(count($temporaryVendorCagetoryIds) > 0)
        {
            $selectedVendorCategoryIds = $temporaryVendorCagetoryIds;
        }

        $view = $canReject ? 'edit' : 'show';

        $companyStatusDescriptions = Company::getCompanyStatusDescriptions();

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

        $selectedCidbCodeIds = [];

        if($company->cidbCodes)
        {
            foreach($company->cidbCodes as $cidbCode)
            {
                $selectedCidbCodeIds[] = $cidbCode->id;
            }
        }

        $cidb_grades = CIDBGrade::orderBy('id', 'ASC')->get();

        return View::make('vendor_management.approval.company_details.' . $view, [
            'vendorRegistration'        => $vendorRegistration,
            'company'                   => $company,
            'section'                   => $section,
            'canReject'                 => $canReject,
            'settings'                  => $settings,
            'attachmentSettings'        => $attachmentSettings,
            'multipleVendorCategories'  => $multipleVendorCategories,
            'vendorCategories'          => $vendorCategories,
            'selectedVendorCategoryIds' => $selectedVendorCategoryIds,
            'companyStatusDescriptions' => $companyStatusDescriptions,
            'cidbCodeParents'           => $cidbCodeParents,
            'cidbCodes'                 => $cidbCodes,
            'selectedCidbCodeIds'       => $selectedCidbCodeIds,
            'cidb_grades'               => $cidb_grades,

        ]);
    }

    public function companyPersonnel($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $user = \Confide::user();

        $records = CompanyPersonnel::where('vendor_registration_id', '=', $vendorRegistration->id)
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
                'route:edit'            => route('vendorManagement.approval.companyPersonnel.edit', array($record->id)),
                'route:delete'          => route('vendorManagement.approval.companyPersonnel.destroy', array($record->id)),
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

        $section = $vendorRegistration->getSection(Section::SECTION_COMPANY_PERSONNEL);

        $setting = CompanyPersonnelSetting::first();

        $instructionSettings = InstructionSetting::first();

        $canReject = !$section->isRejected() && (($vendorRegistration->isProcessing() && $vendorRegistration->processor && $vendorRegistration->processor->user_id == $user->id) || ($vendorRegistration->isPendingVerification() && Verifier::isCurrentVerifier($user, $vendorRegistration)));
        
        $canUploadProcessorAttachments = ($vendorRegistration->isProcessing() && $vendorRegistration->processor && $vendorRegistration->processor->user_id == $user->id);

        $directorUploadedFiles      = $this->getAttachmentDetails(ObjectField::findOrCreateNew($vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR));
        $shareholderUploadedFiles   = $this->getAttachmentDetails(ObjectField::findOrCreateNew($vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER));
        $headOfCompanyUploadedFiles = $this->getAttachmentDetails(ObjectField::findOrCreateNew($vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD));

        return View::make('vendor_management.approval.company_personnel.show', compact('vendorRegistration', 'directorsData', 'shareholdersData', 'headOfCompanyData', 'section', 'setting', 'instructionSettings', 'canReject', 'directorUploadedFiles', 'shareholderUploadedFiles', 'headOfCompanyUploadedFiles', 'canUploadProcessorAttachments'));
    }

    public function projectTrackRecord($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $user = \Confide::user();

        $records = TrackRecordProject::where('vendor_registration_id', '=', $vendorRegistration->id)
            ->orderBy('id', 'desc')
            ->get();

        $completedProjectsData = [];
        $currentProjectsData = [];

        foreach($records as $record)
        {
            $vendorWorkSubcategories = [];

            foreach($record->trackRecordProjectVendorWorkSubcategories as $trackRecordProjectVendorWorkSubcategory)
            {
                array_push($vendorWorkSubcategories, $trackRecordProjectVendorWorkSubcategory->vendorWorkSubcategory->name);
            }

            $row = [
                'id'                           => $record->id,
                'title'                        => $record->title,
                'propertyDeveloper'            => $record->propertyDeveloper ? $record->propertyDeveloper->name : $record->property_developer_text,
                'vendorCategory'               => $record->vendorCategory->name,
                'vendorWorkCategory'           => $record->vendorWorkCategory->name,
                'vendorSubWorkCategory'        => (count($vendorWorkSubcategories) > 0) ? implode(', ', $vendorWorkSubcategories) : '-',
                'route:edit'                   => route('vendorManagement.approval.projectTrackRecord.edit', $record->id),
                'route:delete'                 => route('vendorManagement.approval.projectTrackRecord.delete', $record->id),
                'year_of_site_possession'      => Carbon::parse($record->year_of_site_possession)->format('Y'),
                'year_of_completion'           => Carbon::parse($record->year_of_completion)->format('Y'),
                'qlassic_year_of_achievement'  => is_null($record->qlassic_year_of_achievement) ? null : Carbon::parse($record->qlassic_year_of_achievement)->format('Y'),
                'conquas_year_of_achievement'  => is_null($record->conquas_year_of_achievement) ? null : Carbon::parse($record->conquas_year_of_achievement)->format('Y'),
                'year_of_recognition_awards'   => is_null($record->year_of_recognition_awards) ? null : Carbon::parse($record->year_of_recognition_awards)->format('Y'),
                'has_qlassic_or_conquas_score' => $record->has_qlassic_or_conquas_score,
                'awards_received'              => $record->awards_received,
                'qlassic_score'                => $record->qlassic_score,
                'conquas_score'                => $record->conquas_score,
                'project_amount'               => $record->project_amount,
                'currency'                     => $record->country->currency_code,
                'project_amount_remarks'       => $record->project_amount_remarks,
                'shassic_score'                => $record->shassic_score,
                'remarks'                      => $record->remarks,
                'route:getDownloads'           => route('vendorManagement.approval.projectTrackRecord.downloads.get', array($record->id)),
                'attachments_count'            => $record->attachments->count(),
            ];

            switch($record->type)
            {
                case TrackRecordProject::TYPE_CURRENT:
                    $currentProjectsData[] = $row;
                    break;
                case TrackRecordProject::TYPE_COMPLETED:
                    $completedProjectsData[] = $row;
                    break;
                default:
                    throw new Exception("Invalid type");
            }
        }

        $section = $vendorRegistration->getSection(Section::SECTION_PROJECT_TRACK_RECORD);

        $setting = ProjectTrackRecordSetting::first();

        $instructionSettings = InstructionSetting::first();

        $canReject = !$section->isRejected() && (($vendorRegistration->isProcessing() && $vendorRegistration->processor && $vendorRegistration->processor->user_id == $user->id) || ($vendorRegistration->isPendingVerification() && Verifier::isCurrentVerifier($user, $vendorRegistration)));

        $canUploadProcessorAttachments = ($vendorRegistration->isProcessing() && $vendorRegistration->processor && $vendorRegistration->processor->user_id == $user->id);

        return View::make('vendor_management.approval.project_track_record.show', compact('vendorRegistration', 'completedProjectsData', 'currentProjectsData', 'section', 'setting', 'instructionSettings', 'canReject', 'canUploadProcessorAttachments'));
    }

    public function supplierCreditFacilities($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $user = \Confide::user();

        $records = SupplierCreditFacility::where('vendor_registration_id', '=', $vendorRegistration->id)
            ->orderBy('id', 'desc')
            ->get();

        $data = [];

        foreach($records as $record)
        {
            $data[] = [
                'id'                => $record->id,
                'name'              => $record->supplier_name,
                'facilities'        => $record->credit_facilities,
                'route:edit'        => route('vendorManagement.approval.supplierCreditFacilities.edit', array($record->id)),
                'route:delete'      => route('vendorManagement.approval.supplierCreditFacilities.destroy', array($record->id)),
                'deletable'         => true,
                'route_attachments' => route('vendors.vendorRegistration.supplierCreditFacilities.attachments.get', array($record->id)),
                'attachmentsCount'  => $record->attachments->count(),
            ];
        }

        $data[] = [];

        $section = $vendorRegistration->getSection(Section::SECTION_SUPPLIER_CREDIT_FACILITIES);

        $setting = SupplierCreditFacilitySetting::first();

        $instructionSettings = InstructionSetting::first();

        $canReject = !$section->isRejected() && (($vendorRegistration->isProcessing() && $vendorRegistration->processor && $vendorRegistration->processor->user_id == $user->id) || ($vendorRegistration->isPendingVerification() && Verifier::isCurrentVerifier($user, $vendorRegistration)));

        $canUploadProcessorAttachments = ($vendorRegistration->isProcessing() && $vendorRegistration->processor && $vendorRegistration->processor->user_id == $user->id);

        return View::make('vendor_management.approval.supplier_credit_facilities.show', compact('vendorRegistration', 'data', 'section', 'setting', 'instructionSettings', 'canReject', 'canUploadProcessorAttachments'));
    }

    public function companyDetailsReject($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $section = $vendorRegistration->getSection(Section::SECTION_COMPANY_DETAILS);

        $section->status_id = Section::STATUS_REJECTED;
        $section->amendment_status = Section::AMENDMENT_STATUS_REQUIRED;
        $section->amendment_remarks = Input::get('reject_remarks');

        $section->save();

        \Flash::success(trans('forms.rejectSuccessful'));

        return Redirect::back();
    }

    public function companyPersonnelReject($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $section = $vendorRegistration->getSection(Section::SECTION_COMPANY_PERSONNEL);

        $section->status_id = Section::STATUS_REJECTED;
        $section->amendment_status = Section::AMENDMENT_STATUS_REQUIRED;
        $section->amendment_remarks = Input::get('amendment_remarks');

        $section->save();

        \Flash::success(trans('forms.rejectSuccessful'));

        return Redirect::back();
    }

    public function projectTrackRecordReject($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $section = $vendorRegistration->getSection(Section::SECTION_PROJECT_TRACK_RECORD);

        $section->status_id = Section::STATUS_REJECTED;
        $section->amendment_status = Section::AMENDMENT_STATUS_REQUIRED;
        $section->amendment_remarks = Input::get('amendment_remarks');

        $section->save();

        \Flash::success(trans('forms.rejectSuccessful'));

        return Redirect::back();
    }

    public function supplierCreditFacilitiesReject($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $section = $vendorRegistration->getSection(Section::SECTION_SUPPLIER_CREDIT_FACILITIES);

        $section->status_id = Section::STATUS_REJECTED;
        $section->amendment_status = Section::AMENDMENT_STATUS_REQUIRED;
        $section->amendment_remarks = Input::get('amendment_remarks');

        $section->save();

        \Flash::success(trans('forms.rejectSuccessful'));

        return Redirect::back();
    }

    public function assignForm($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $processorIds = VendorManagementUserPermission::getPermissionUsers()[VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION];

        $currentProcessorId = $vendorRegistration->processor ? $vendorRegistration->processor->user_id : null;

        if(!is_null($currentProcessorId)) array_push($processorIds, $currentProcessorId);

        $processorNames = User::whereIn('id', $processorIds)
            ->lists('name', 'id');

        return View::make('vendor_management.approval.process', compact('vendorRegistration', 'currentProcessorId', 'processorNames'));
    }

    public function assignableProcessorsList($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $records = User::whereIn('id', VendorManagementUserPermission::getPermissionUsers()[VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION])
            ->where('name', 'ilike', '%'.trim(Request::get('term')).'%')
            ->orderBy('name')
            ->lists('name', 'id');

        $results = [];

        foreach($records as $id => $name)
        {
            $option = [
                'id' => $id,
                'text' => $name
            ];

            $results[] = $option;
        }

        return Response::json([
            'results' => $results,
        ]);
    }

    public function assign($vendorRegistrationId)
    {
        $input = Input::all();

        $input['vendor_registration_id'] = $vendorRegistrationId;

        $this->vendorRegistrationAssignProcessorForm->validate($input);

        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $vendorRegistration->status = VendorRegistration::STATUS_PROCESSING;

        $vendorRegistration->save();

        $vendorRegistration->setProcessor($input['processor_id']);

        SubmissionLog::logAction($vendorRegistration, SubmissionLog::PROCESSING);

        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        \Flash::success(trans('vendorManagement.companyXIsNowProcessedByUserY', array('company' => $vendorRegistration->company->name, 'user' => $vendorRegistration->processor->user->name)));

        if(\Confide::user()->id === $vendorRegistration->processor->user_id)
        {
            return Redirect::route('vendorManagement.approval.registrationAndPreQualification.show', array($vendorRegistration->id));
        }

        return Redirect::back();
    }

    public function getSubmissionLogs($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $data = [];
        
        foreach($vendorRegistration->submissionLogs()->orderBy('id', 'DESC')->get() as $log)
        {
            array_push($data, [
                'user'     => $log->createdBy->name,
                'dateTime' => Carbon::parse($log->created_at)->format(\Config::get('dates.full_format')),
                'action'   => $log->getActionDescription(),
            ]);
        }

        return Response::json($data);
    }

    public function deleteCompany($vendorRegistrationId)
    {
        $success = false;

        $transaction = new DBTransaction();
        $transaction->begin();

        try
        {
            $vendorRegistration = VendorRegistration::find($vendorRegistrationId);
            $company            = $vendorRegistration->company;

            $company->flushRelatedVendorRegistrationData();

            UserCompanyLog::where('company_id', '=', $company->id)->delete();

            $this->emailNotifier->sendCompanyDeletionEmails($company);

            foreach($company->users as $user)
            {
                $user->delete();
            }

            $company->load('users');

            $company->delete();

            ProcessorDeleteCompanyLog::logAction($company);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            \Log::error($e->getMessage());
        }

        if($success)
        {
            Flash::success(trans('vendorManagement.vendorRegistrationDeletedSuccessfully'));

            return Redirect::route('vendorManagement.approval.registrationAndPreQualification');
        }
        else
        {
            Flash::error(trans('vendorManagement.vendorRegistrationNotDeleted'));

            return Redirect::back();
        }
    }

    public function getProcessorDeleteCompanyLogs()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $query = "SELECT pdcl.name, pdcl.reference_no, cgc.name AS vendor_group, u.name AS processor
                  FROM processor_delete_company_logs pdcl 
                  INNER JOIN contract_group_categories cgc on cgc.id = pdcl.contract_group_category_id 
                  INNER JOIN users u on u.id = pdcl.created_by 
                  WHERE cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . " ";
          
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!is_array($filters['value']))
                {
                    $val = trim($filters['value']);

                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'company':
                            if(strlen($val) > 0)
                            {
                                $query .= "AND pdcl.name ILIKE '%{$val}%' ";
                            }
                            break;
                        case 'reference_no':
                            if(strlen($val) > 0)
                            {
                                $query .= "AND pdcl.reference_no ILIKE '%{$val}%' ";
                            }
                            break;
                        case 'vendor_group':
                            if((int)$val > 0)
                            {
                                $query .= "AND cgc.id = {$val} ";
                            }
                            break;
                        case 'processor':
                            if(strlen($val) > 0)
                            {
                                $query .= "AND u.name ILIKE '%{$val}%' ";
                            }
                            break;
                    }
                }
            }
        }

        $query .= "ORDER BY pdcl.id ASC ";

        $rowCount = count(DB::select(DB::raw($query)));

        $query .= "LIMIT {$limit} OFFSET " . $limit * ($page - 1) . ";";

        $queryResults = DB::select(DB::raw($query));

        $data = [];

        foreach($queryResults as $key => $result)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'counter'           => $counter,
                'company'           => $result->name,
                'reference_no'      => $result->reference_no,
                'vendor_group'      => $result->vendor_group,
                'processor'         => $result->processor,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }
}