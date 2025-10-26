<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementOpenRfp;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfpAccountCode;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany;
use PCK\ConsultantManagement\ConsultantManagementSubsidiary;
use PCK\ConsultantManagement\ConsultantManagementRfpInterview;

use PCK\ConsultantManagement\ApprovalDocument;
use PCK\ConsultantManagement\ApprovalDocumentVerifier;
use PCK\ConsultantManagement\ApprovalDocumentVerifierVersion;
use PCK\ConsultantManagement\ApprovalDocumentSectionCDetails;
use PCK\ConsultantManagement\ApprovalDocumentSectionDDetails;
use PCK\ConsultantManagement\ApprovalDocumentSectionDServiceFee;
use PCK\ConsultantManagement\ApprovalDocumentSectionAppendixDetails;

use PCK\Users\User;
use PCK\Companies\Company;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\VendorPreQualification\VendorGroupGrade;
use PCK\ModulePermission\ModulePermission;
use PCK\Buildspace\AccountCode;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use PCK\Helpers\Files;

use PCK\Notifications\EmailNotifier;

use PCK\Forms\ConsultantManagement\ApprovalDocumentForm;
use PCK\Forms\ConsultantManagement\ApprovalDocumentVerifierForm;
use PCK\Forms\ConsultantManagement\GeneralVerifyForm;

class ConsultantManagementApprovalDocumentController extends \BaseController
{
    private $approvalDocumentForm;
    private $approvalDocumentVerifierForm;
    private $generalVerifyForm;
    private $emailNotifier;

    public function __construct(ApprovalDocumentForm $approvalDocumentForm, ApprovalDocumentVerifierForm $approvalDocumentVerifierForm, GeneralVerifyForm $generalVerifyForm, EmailNotifier $emailNotifier)
    {
        $this->approvalDocumentForm = $approvalDocumentForm;
        $this->approvalDocumentVerifierForm = $approvalDocumentVerifierForm;
        $this->generalVerifyForm = $generalVerifyForm;
        $this->emailNotifier = $emailNotifier;
    }

    public function index(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId)
    {
        $user = \Confide::user();

        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);

        $awardedConsultant = ConsultantManagementConsultantRfp::where('consultant_management_rfp_revision_id', '=', $openRfp->consultant_management_rfp_revision_id)
        ->where('awarded', '=', true)
        ->first();

        if(!$awardedConsultant)
        {
            \Flash::error("Please select a Consultant as an Awarded Consultant");

            return Redirect::route('consultant.management.open.rfp.show', [$vendorCategoryRfp->id, $openRfp->id]);
        }

        $awardedConsultantCompany = $awardedConsultant->company;

        $latestVendorRegistration = $awardedConsultantCompany->finalVendorRegistration;

        $vendorPreQualificationData = [];

        if($latestVendorRegistration)
        {
            $vendorPreQualifications = VendorPreQualification::select('vendor_pre_qualifications.*')
            ->where('vendor_registration_id', '=', $latestVendorRegistration->id)
            ->join('vendor_work_categories', 'vendor_pre_qualifications.vendor_work_category_id', '=', 'vendor_work_categories.id')
            ->whereNotNull('weighted_node_id')
            ->orderBy('vendor_work_categories.name', 'asc')
            ->get();

            $grading = VendorGroupGrade::getGradeByGroup($awardedConsultantCompany->contract_group_category_id);

            foreach($vendorPreQualifications as $vendorPreQualification)
            {
                $gradeLevel = null;

                if( $grading ) $gradeLevel = $grading->getGrade($vendorPreQualification->score);

                $vendorPreQualificationData[] = [
                    'vendor_work_category' => $vendorPreQualification->vendorWorkCategory->name,
                    'status'               => VendorPreQualification::getStatusText($vendorPreQualification->status_id),
                    'score'                => $vendorPreQualification->score,
                    'grade'                => $gradeLevel ? $gradeLevel->description : null
                ];
            }
        }

        $proposedFeeList = ConsultantManagementSubsidiary::select("consultant_management_subsidiaries.id AS subsidiary_id", "consultant_management_consultant_rfp.company_id",
        "consultant_management_consultant_rfp_proposed_fees.id AS proposed_fee_id", "consultant_management_consultant_rfp_common_information.updated_at AS submitted_at",
        "consultant_management_consultant_rfp_proposed_fees.proposed_fee_percentage", "consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount", "consultant_management_subsidiaries.total_construction_cost", "consultant_management_subsidiaries.total_landscape_cost")
        ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_subsidiary_id', '=', 'consultant_management_subsidiaries.id')
        ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->where('consultant_management_consultant_rfp.company_id', '=', $awardedConsultantCompany->id)
        ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
        ->where('consultant_management_subsidiaries.consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->groupBy(\DB::raw("consultant_management_subsidiaries.id, consultant_management_consultant_rfp.id, consultant_management_consultant_rfp_common_information.id, consultant_management_consultant_rfp_proposed_fees.id"))
        ->get()
        ->toArray();

        $proposedFeeAmountRecord = ConsultantManagementSubsidiary::select("consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount")
        ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_subsidiary_id', '=', 'consultant_management_subsidiaries.id')
        ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->where('consultant_management_consultant_rfp.company_id', '=', $awardedConsultantCompany->id)
        ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
        ->where('consultant_management_subsidiaries.consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->where('consultant_management_consultant_rfp.awarded', true)
        ->first();

        $proposedFeeAmount = $proposedFeeAmountRecord ? $proposedFeeAmountRecord->proposed_fee_amount : 0.0;

        $latestRfpInterview = ConsultantManagementRfpInterview::select("consultant_management_rfp_interviews.interview_date")
        ->join("consultant_management_rfp_interview_consultants", "consultant_management_rfp_interview_consultants.consultant_management_rfp_interview_id", "=", "consultant_management_rfp_interviews.id")
        ->where("consultant_management_rfp_interviews.vendor_category_rfp_id", "=", $vendorCategoryRfp->id)
        ->where("consultant_management_rfp_interview_consultants.company_id", "=", $awardedConsultant->company_id)
        ->orderBy("consultant_management_rfp_interviews.interview_date", "desc")
        ->first();

        $approvalDocument = $vendorCategoryRfp->approvalDocument;

        $companyRole = ConsultantManagementCompanyRole::select('consultant_management_company_roles.company_id AS company_id', 'consultant_management_company_roles.role')
        ->join('consultant_management_contracts', 'consultant_management_company_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->where('consultant_management_contracts.id', '=', $consultantManagementContract->id)
        ->whereRaw('consultant_management_company_roles.calling_rfp IS TRUE')
        ->first();

        $verifiers = User::select(\DB::raw("users.id, users.name"))
        ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
        ->whereRaw('consultant_management_user_roles.role = '.$companyRole->role.' AND consultant_management_user_roles.user_id = users.id')
        ->where('consultant_management_user_roles.consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->whereRaw('users.confirmed IS TRUE')
        ->whereRaw('users.account_blocked_status IS FALSE')
        ->orderBy('users.name', 'asc')
        ->get();

        $verifiers = $verifiers->merge(ModulePermission::getUserList(ModulePermission::MODULE_ID_TOP_MANAGEMENT_VERIFIERS));
        
        $selectedVerifiers = ($approvalDocument) ? ApprovalDocumentVerifier::select("consultant_management_approval_document_verifiers.user_id AS id")
            ->where('consultant_management_approval_document_id', $approvalDocument->id)
            ->orderBy('consultant_management_approval_document_verifiers.id', 'asc')
            ->get() : [];
        
        $request = Request::instance();

        $selectedWizardStep = 0;

        if($request->has('section') && in_array($request->get('section'), ['a', 'b', 'c', 'd', 'appendix']))
        {
            $selectedWizardStep = array_search($request->get('section'), ['a', 'b', 'c', 'd', 'appendix']);
        }

        return View::make('consultant_management.approval_document.index', compact('vendorCategoryRfp', 'consultantManagementContract', 'openRfp', 'awardedConsultant', 'proposedFeeList', 'latestRfpInterview', 'approvalDocument', 'latestVendorRegistration', 'vendorPreQualificationData', 'verifiers', 'selectedVerifiers', 'selectedWizardStep', 'user', 'proposedFeeAmount'));
    }

    public function store(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();

        $this->approvalDocumentForm->validate($request->all());

        $user = \Confide::user();

        $approvalDocument = $vendorCategoryRfp->approvalDocument;
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$request->get('open_rfp_id'));

        if(!$approvalDocument)
        {
            $approvalDocument = new ApprovalDocument;
            $approvalDocument->vendor_category_rfp_id = $vendorCategoryRfp->id;
            $approvalDocument->created_by = $user->id;
        }

        $approvalDocument->document_reference_no = trim($request->get('document_reference_no'));
        $approvalDocument->updated_by = $user->id;

        $approvalDocument->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id]);
    }

    public function verifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId, $approvalDocumentId)
    {
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);
        $approvalDocument = ApprovalDocument::findOrFail((int)$approvalDocumentId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ApprovalDocumentVerifierVersion::select("consultant_management_approval_document_verifier_versions.id AS id", "users.name AS name",
        "consultant_management_approval_document_verifier_versions.version", "consultant_management_approval_document_verifier_versions.status",
        "consultant_management_approval_document_verifier_versions.remarks", "consultant_management_approval_document_verifier_versions.updated_at")
        ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id', '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
        ->join('users', 'users.id', '=', 'consultant_management_approval_document_verifiers.user_id')
        ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
        ->where('consultant_management_approval_documents.id', '=', $approvalDocument->id)
        ->orderBy('consultant_management_approval_document_verifier_versions.version', 'desc')
        ->orderBy('consultant_management_approval_document_verifier_versions.id', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => trim($record->name),
                'remarks'      => trim($record->remarks),
                'version'      => $record->version,
                'status'       => $record->status,
                'status_txt'   => $record->getStatusText(),
                'updated_at'   => date('d/m/Y H:i:s', strtotime($record->updated_at))
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function verifierStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();

        $this->approvalDocumentVerifierForm->validate($request->all());

        $user = \Confide::user();

        $approvalDocument = ApprovalDocument::findOrFail($request->get('id'));
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$request->get('open_rfp_id'));

        if($request->has('verifiers') && is_array($request->get('verifiers')))
        {
            $verifierIds = array_unique(array_filter($request->get('verifiers')));
            
            if(!empty($verifierIds))
            {
                ApprovalDocumentVerifier::where('consultant_management_approval_document_id', $approvalDocument->id)
                ->delete();

                $data = [];
                foreach($verifierIds as $id)
                {
                    $data[] = [
                        'consultant_management_approval_document_id' => $approvalDocument->id,
                        'user_id'    => $id,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                ApprovalDocumentVerifier::insert($data);
            }
        }

        $recipientId = null;

        if($request->has('send_to_verify'))
        {
            $approvalDocument->status = ApprovalDocument::STATUS_APPROVAL;
            
            $approvalDocument->save();

            $latestVersion = ApprovalDocumentVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id', '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
            ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
            ->where('consultant_management_approval_documents.id', '=', $approvalDocument->id)
            ->groupBy('consultant_management_approval_documents.id')
            ->first();

            $version = ($latestVersion) ? $latestVersion->version + 1 : 1;

            $verifierIds = ApprovalDocumentVerifier::select("consultant_management_approval_document_verifiers.id", "consultant_management_approval_document_verifiers.user_id")
            ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
            ->where('consultant_management_approval_documents.id', '=', $approvalDocument->id)
            ->orderBy('consultant_management_approval_document_verifiers.id', 'asc')
            ->lists('user_id', 'id');

            $data = [];

            $count = 0;
            foreach($verifierIds as $verifierId => $userId)
            {
                $data[] = [
                    'consultant_management_approval_document_verifier_id' => $verifierId,
                    'user_id'    => $userId,
                    'version'    => $version,
                    'status'     => ApprovalDocumentVerifierVersion::STATUS_PENDING,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if($count==0)
                {
                    $recipientId = $userId;
                }

                $count++;
            }

            if($data)
            {
                ApprovalDocumentVerifierVersion::insert($data);
            }
        }

        if($recipientId && $recipient = User::find($recipientId))
        {
            $contract = $vendorCategoryRfp->consultantManagementContract;
            $content = [
                'subject' => "Consultant Management - Approval Document Verification (".$contract->Subsidiary->name.")",//need to move this to i10n
                'view' => 'consultant_management.email.pending_approval',
                'data' => [
                    'developmentPlanningTitle' => $contract->title,
                    'subsidiaryName' => $contract->Subsidiary->name,
                    'creator' => $user->name,
                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                    'moduleName' => 'Approval Document',
                    'route' => route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id])
                ]
            ];
            
            $this->emailNotifier->sendGeneralEmail($content, [$recipient]);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id]);
    }

    public function verify(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();

        $this->generalVerifyForm->validate($request->all());
        
        $user = \Confide::user();

        $approvalDocument = ApprovalDocument::findOrFail($request->get('id'));
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$request->get('open_rfp_id'));
        $contract = $vendorCategoryRfp->consultantManagementContract;

        $latestVersion = ApprovalDocumentVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id', '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
            ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
            ->where('consultant_management_approval_documents.id', '=', $approvalDocument->id)
            ->groupBy('consultant_management_approval_documents.id')
            ->first();

        if($latestVersion && $approvalDocument->needApprovalFromUser(Confide::user()) && ($request->has('approve') or $request->has('reject')))
        {
            $latestVerifierLogId = ApprovalDocumentVerifierVersion::select("consultant_management_approval_document_verifier_versions.id AS id")
            ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id', '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
            ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
            ->where('consultant_management_approval_documents.id', '=', $approvalDocument->id)
            ->where('consultant_management_approval_document_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_approval_document_verifiers.user_id', '=', $user->id)
            ->first();

            if($latestVerifierLogId)
            {
                $latestVerifierLog = ApprovalDocumentVerifierVersion::findOrFail($latestVerifierLogId->id);

                $status = ApprovalDocumentVerifierVersion::STATUS_PENDING;
                $content = [];
                $recipients = [];

                if($request->has('approve'))
                {
                    $status = ApprovalDocumentVerifierVersion::STATUS_APPROVED;

                    $nextVerifier = ApprovalDocumentVerifierVersion::select("consultant_management_approval_document_verifiers.id AS id", "users.name", "users.email", "users.id AS user_id", "consultant_management_approval_document_verifier_versions.status")
                        ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id', '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
                        ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
                        ->join('users', 'consultant_management_approval_document_verifiers.user_id', '=', 'users.id')
                        ->where('consultant_management_approval_documents.id', '=', $approvalDocument->id)
                        ->where('consultant_management_approval_document_verifier_versions.version', '=', $latestVersion->version)
                        ->where('consultant_management_approval_document_verifiers.id', '>', $latestVerifierLog->consultant_management_approval_document_verifier_id)
                        ->orderBy('consultant_management_approval_document_verifiers.id', 'asc')
                        ->first();

                    if(!$nextVerifier)
                    {
                        $approvalDocument->status = ApprovalDocument::STATUS_APPROVED;
                        $approvalDocument->save();

                        $recipients = User::select('users.*')
                            ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
                            ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$contract->id.'
                            AND consultant_management_user_roles.role = '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.'
                            AND consultant_management_user_roles.editor IS TRUE
                            AND users.confirmed IS TRUE
                            AND users.account_blocked_status IS FALSE')
                            ->groupBy('users.id')
                            ->get();

                        if(!empty($recipients))
                        {
                            $content = [
                                'subject' => "Consultant Management - Approval Document Approved (".$contract->Subsidiary->name.")",//need to move this to i10n
                                'view' => 'consultant_management.email.approved',
                                'data' => [
                                    'developmentPlanningTitle' => $contract->title,
                                    'subsidiaryName' => $contract->Subsidiary->name,
                                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                    'moduleName' => 'Approval Document',
                                    'route' => route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id])
                                ]
                            ];
                        }
                    }
                    else
                    {
                        $recipient = User::find($nextVerifier->user_id);
                        $recipients = [$recipient];

                        $content = [
                            'subject' => "Consultant Management - Approval Document Verification (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.pending_approval',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'creator' => $user->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'moduleName' => 'Approval Document',
                                'route' => route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id])
                            ]
                        ];
                    }
                }
                elseif($request->has('reject'))
                {
                    $status = ApprovalDocumentVerifierVersion::STATUS_REJECTED;

                    $approvalDocument->status = ApprovalDocument::STATUS_DRAFT;
                    $approvalDocument->save();

                    $recipients = User::select('users.*')
                        ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
                        ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$contract->id.'
                        AND consultant_management_user_roles.role = '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.'
                        AND consultant_management_user_roles.editor IS TRUE
                        AND users.confirmed IS TRUE
                        AND users.account_blocked_status IS FALSE')
                        ->groupBy('users.id')
                        ->get();

                    if(!empty($recipients))
                    {
                        $content = [
                            'subject' => "Consultant Management - Approval Document Rejected (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.rejected',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'creator' => $user->name,
                                'moduleName' => 'Approval Document',
                                'route' => route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id])
                            ]
                        ];
                    }
                }

                $latestVerifierLog->remarks    = trim($request->get('remarks'));
                $latestVerifierLog->status     = $status;
                $latestVerifierLog->updated_at = date('Y-m-d H:i:s');

                $latestVerifierLog->save();

                if(!empty($recipients) and !empty($content))
                {
                    $this->emailNotifier->sendGeneralEmail($content, $recipients);
                }
            }
        }

        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id]);
    }

    public function sectionAStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();

        $user = \Confide::user();

        $approvalDocument = $vendorCategoryRfp->approvalDocument;
        $sectionA         = $approvalDocument->sectionA;
        $openRfp          = ConsultantManagementOpenRfp::findOrFail((int)$request->get('open_rfp_id'));

        $sectionA->approving_authority = $request->get('approving_authority');
        $sectionA->updated_by          = $user->id;

        $sectionA->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id, 'section'=>'a']);
    }

    public function sectionBStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();

        $user = \Confide::user();

        $approvalDocument = $vendorCategoryRfp->approvalDocument;
        $sectionB         = $approvalDocument->sectionB;
        $openRfp          = ConsultantManagementOpenRfp::findOrFail((int)$request->get('open_rfp_id'));

        $sectionB->project_brief = trim($request->get('project_brief'));
        $sectionB->updated_by    = $user->id;

        $sectionB->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id, 'section'=>'b']);
    }

    public function sectionCStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();
        
        $user = \Confide::user();

        $approvalDocument               = $vendorCategoryRfp->approvalDocument;
        $sectionC                       = $approvalDocument->sectionC;
        $openRfp                        = ConsultantManagementOpenRfp::findOrFail((int)$request->get('open_rfp_id'));
        $consultantManagementSubsidiary = ConsultantManagementSubsidiary::findOrFail((int)$request->get('cm_subsidiary_id'));
        $company                        = Company::findOrFail((int)$request->get('cid'));

        $sectionCDetails = ApprovalDocumentSectionCDetails::where('consultant_management_approval_document_section_c_id', $sectionC->id)
        ->where('consultant_management_subsidiary_id', '=', $consultantManagementSubsidiary->id)
        ->where('company_id', '=', $company->id)
        ->first();

        if(!$sectionCDetails)
        {
            $sectionCDetails = new ApprovalDocumentSectionCDetails;
            $sectionCDetails->consultant_management_approval_document_section_c_id = $sectionC->id;
            $sectionCDetails->consultant_management_subsidiary_id = $consultantManagementSubsidiary->id;
            $sectionCDetails->company_id = $company->id;
            $sectionCDetails->created_by = $user->id;
        }

        $sectionCDetails->remarks = trim($request->get('remarks'));
        $sectionCDetails->updated_by = $user->id;

        $sectionCDetails->save();
        
        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id, 'section'=>'c']);
    }

    public function sectionCConsultantList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId, $consultantManagementSubsidiaryId)
    {
        $user = \Confide::user();
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);
        $consultantManagementSubsidiary = ConsultantManagementSubsidiary::findOrFail((int)$consultantManagementSubsidiaryId);

        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $approvalDocument             = $vendorCategoryRfp->approvalDocument;
        $sectionC                     = $approvalDocument->sectionC;

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $proposedFeeList = ConsultantManagementSubsidiary::select("consultant_management_subsidiaries.id AS subsidiary_id", "consultant_management_consultant_rfp.company_id",
        "consultant_management_consultant_rfp_proposed_fees.id AS proposed_fee_id",
        "consultant_management_consultant_rfp_proposed_fees.proposed_fee_percentage", "consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount", "consultant_management_subsidiaries.total_construction_cost", "consultant_management_subsidiaries.total_landscape_cost")
        ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_subsidiary_id', '=', 'consultant_management_subsidiaries.id')
        ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->where('consultant_management_subsidiaries.id', '=', $consultantManagementSubsidiary->id)
        ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
        ->where('consultant_management_subsidiaries.consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->groupBy(\DB::raw("consultant_management_subsidiaries.id, consultant_management_consultant_rfp.id, consultant_management_consultant_rfp_proposed_fees.id"))
        ->get()
        ->toArray();

        $proposedFees = [];
        foreach($proposedFeeList as $item)
        {
            $proposedFees[$item['company_id']] = [
                'id'         => $item['proposed_fee_id'],
                'percentage' => $item['proposed_fee_percentage'],
                'amount'     => $item['proposed_fee_amount']
            ];
        }

        $sectionCDetails = ApprovalDocumentSectionCDetails::select("companies.id AS company_id", "consultant_management_section_c_details.consultant_management_subsidiary_id",
        "consultant_management_section_c_details.remarks")
        ->join("consultant_management_approval_document_section_c", "consultant_management_approval_document_section_c.id", "=", "consultant_management_section_c_details.consultant_management_approval_document_section_c_id")
        ->join("consultant_management_approval_documents", "consultant_management_approval_documents.id", "=", "consultant_management_approval_document_section_c.consultant_management_approval_document_id")
        ->join("consultant_management_vendor_categories_rfp", "consultant_management_vendor_categories_rfp.id", "=", "consultant_management_approval_documents.vendor_category_rfp_id")
        ->join("companies", "companies.id", "=", "consultant_management_section_c_details.company_id")
        ->where("consultant_management_vendor_categories_rfp.id", "=", $vendorCategoryRfp->id)
        ->where("consultant_management_section_c_details.consultant_management_subsidiary_id", "=", $consultantManagementSubsidiary->id)
        ->where("consultant_management_section_c_details.consultant_management_approval_document_section_c_id", "=", $sectionC->id)
        ->lists("remarks", "company_id");

        $model = ConsultantManagementCallingRfpCompany::select("companies.id AS id", "companies.name AS company_name", "companies.reference_no", "consultant_management_consultant_rfp.awarded AS awarded",
        "consultant_management_consultant_rfp_common_information.updated_at AS submitted_at", "consultant_management_consultant_rfp_common_information.remarks AS remarks", "consultant_management_calling_rfp.closing_rfp_date")
        ->join('companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'companies.id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->leftJoin('consultant_management_consultant_rfp', function($join){
            $join->on('consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id');
            $join->on('consultant_management_consultant_rfp.company_id','=', 'companies.id');
        })
        ->leftJoin('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->orderBy('companies.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $item = [
                'id'                    => $record->id,
                'counter'               => $counter,
                'company_name'          => $record->company_name,
                'reference_no'          => $record->reference_no,
                'remarks'               => (array_key_exists($record->id, $sectionCDetails)) ? trim($sectionCDetails[$record->id]) : "",
                'awarded'               => ($record->awarded) ? $record->awarded : false,
                'proposed_fee_id'       => -1,
                'consultant_amount'     => number_format(0, 2, '.', ','),
                'consultant_percentage' => number_format(0, 2, '.', ','),
                'submitted_at'          => ($record->submitted_at) ? $consultantManagementContract->getAppTimeZoneTime(Carbon::parse($record->submitted_at)->format(\Config::get('dates.created_and_updated_at_formatting'))) : null
            ];

            if(array_key_exists($record->id, $proposedFees))
            {
                $item['proposed_fee_id']       = $proposedFees[$record->id]['id'];
                $item['consultant_amount']     = number_format($proposedFees[$record->id]['amount'], 2, '.', ',');
                $item['consultant_percentage'] = number_format($proposedFees[$record->id]['percentage'], 2, '.', '');

                unset($proposedFees[$record->id]);
            }
            
            $data[] = $item;
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function sectionDDetailsStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();
        
        $user = \Confide::user();

        $approvalDocument = $vendorCategoryRfp->approvalDocument;
        $sectionD         = $approvalDocument->sectionD;
        $openRfp          = ConsultantManagementOpenRfp::findOrFail((int)$request->get('open_rfp_id'));
        $company          = Company::findOrFail((int)$request->get('cid'));

        $sectionDDetails = ApprovalDocumentSectionDDetails::where('consultant_management_approval_document_section_d_id', $sectionD->id)
        ->where('company_id', '=', $company->id)
        ->first();

        if(!$sectionDDetails)
        {
            $sectionDDetails = new ApprovalDocumentSectionDDetails;
            $sectionDDetails->consultant_management_approval_document_section_d_id = $sectionD->id;
            $sectionDDetails->company_id = $company->id;
            $sectionDDetails->created_by = $user->id;
        }

        $sectionDDetails->scope_of_services = trim($request->get('scope_of_services'));
        $sectionDDetails->updated_by = $user->id;

        $sectionDDetails->save();
        
        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id, 'section'=>'d']);
    }

    public function sectionDServiceFeeStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();
        
        $user = \Confide::user();

        $approvalDocument               = $vendorCategoryRfp->approvalDocument;
        $sectionD                       = $approvalDocument->sectionD;
        $openRfp                        = ConsultantManagementOpenRfp::findOrFail((int)$request->get('open_rfp_id'));
        $consultantManagementSubsidiary = ConsultantManagementSubsidiary::findOrFail((int)$request->get('sid'));
        $company                        = Company::findOrFail((int)$request->get('cid'));

        $sectionDServiceFee = ApprovalDocumentSectionDServiceFee::where('consultant_management_approval_document_section_d_id', $sectionD->id)
        ->where('consultant_management_subsidiary_id', '=', $consultantManagementSubsidiary->id)
        ->where('company_id', '=', $company->id)
        ->first();
        
        if(!$sectionDServiceFee)
        {
            $sectionDServiceFee = new ApprovalDocumentSectionDServiceFee;
            $sectionDServiceFee->consultant_management_approval_document_section_d_id = $sectionD->id;
            $sectionDServiceFee->consultant_management_subsidiary_id = $consultantManagementSubsidiary->id;
            $sectionDServiceFee->company_id = $company->id;
            $sectionDServiceFee->created_by = $user->id;
        }

        $sectionDServiceFee->board_scale_of_fee = trim($request->get($consultantManagementSubsidiary->id.'-'.$company->id.'-board_scale_of_fee'));
        $sectionDServiceFee->notes              = trim($request->get($consultantManagementSubsidiary->id.'-'.$company->id.'-notes'));
        $sectionDServiceFee->updated_by         = $user->id;

        $sectionDServiceFee->save();
        
        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id, 'section'=>'d']);
    }

    public function sectionAppendixStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();
        
        $user = \Confide::user();

        $approvalDocument = $vendorCategoryRfp->approvalDocument;
        $sectionAppendix  = $approvalDocument->sectionAppendix;
        $openRfp          = ConsultantManagementOpenRfp::findOrFail((int)$request->get('open_rfp_id'));
        
        $appendixDetails = ApprovalDocumentSectionAppendixDetails::find($request->get('id'));

        if(!$appendixDetails)
        {
            $appendixDetails = new ApprovalDocumentSectionAppendixDetails;

            $appendixDetails->consultant_management_approval_document_section_appendix_id = $sectionAppendix->id;
            $appendixDetails->created_by = $user->id;
        }

        $appendixDetails->title      = trim($request->get('title'));
        $appendixDetails->updated_by = $user->id;
        
        $appendixDetails->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id, 'section'=>'appendix']);
    }

    public function sectionAppendixAttachmentUpload(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();
        
        $user = \Confide::user();

        $approvalDocument = $vendorCategoryRfp->approvalDocument;
        $sectionAppendix  = $approvalDocument->sectionAppendix;
        $openRfp          = ConsultantManagementOpenRfp::findOrFail((int)$request->get('open_rfp_id'));
        $appendixDetails  = ApprovalDocumentSectionAppendixDetails::findOrFail((int)$request->get('id'));

        if($request->hasFile('appendix_details-attachment'))
        {
            $attachmentFile = $request->file('appendix_details-attachment');

            $path = storage_path('consultant_management-appendix_attachments'.DIRECTORY_SEPARATOR.$appendixDetails->id);

            Files::mkdirIfDoesNotExist($path);

            $dir = new DirectoryIterator($path);
            // Deleting all the files in the list
            foreach ($dir as $fileinfo)
            {
                if (!$fileinfo->isDot() && $fileinfo->isFile())
                {
                    unlink($fileinfo->getPathname());
                }
            }

            $attachmentFile->move($path, $attachmentFile->getClientOriginalName());

            $appendixDetails->attachment_filename = $attachmentFile->getClientOriginalName();
            $appendixDetails->updated_by = $user->id;
            
            $appendixDetails->save();

            \Flash::success(trans('forms.saved'));
        }
        
        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id, 'section'=>'appendix']);
    }

    public function sectionAppendixAttachmentDownload(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $appendixDetailsId)
    {
        $appendixDetails = ApprovalDocumentSectionAppendixDetails::findOrFail((int)$appendixDetailsId);

        $path = storage_path('consultant_management-appendix_attachments'.DIRECTORY_SEPARATOR.$appendixDetails->id);

        $filepath = $path.DIRECTORY_SEPARATOR.$appendixDetails->attachment_filename;

        return Files::download($filepath, $appendixDetails->attachment_filename);
    }

    public function appendixDetailsDelete(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId, $appendixDetailsId)
    {
        $openRfp         = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);
        $appendixDetails = ApprovalDocumentSectionAppendixDetails::findOrFail((int)$appendixDetailsId);

        $user = \Confide::user();

        $path = storage_path('consultant_management-appendix_attachments'.DIRECTORY_SEPARATOR.$appendixDetails->id);

        if(!empty($appendixDetails->attachment_filename) && file_exists($path.DIRECTORY_SEPARATOR.$appendixDetails->attachment_filename))
        {
            unlink($path.DIRECTORY_SEPARATOR.$appendixDetails->attachment_filename);
        }

        $appendixDetails->delete();

        \Log::info("Delete consultant management appendix details [id: {$appendixDetailsId}]][user id:{$user->id}]");

        return Redirect::route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id, 'section'=>'appendix']);
    }

    public function sectionAppendixList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId)
    {
        $user = \Confide::user();
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);

        $approvalDocument = $vendorCategoryRfp->approvalDocument;
        $sectionAppendix  = $approvalDocument->sectionAppendix;

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ApprovalDocumentSectionAppendixDetails::select("consultant_management_section_appendix_details.id AS id", "consultant_management_section_appendix_details.title",
        "consultant_management_section_appendix_details.consultant_management_approval_document_section_appendix_id", "consultant_management_section_appendix_details.attachment_filename")
        ->join('consultant_management_approval_document_section_appendix', 'consultant_management_section_appendix_details.consultant_management_approval_document_section_appendix_id', '=', 'consultant_management_approval_document_section_appendix.id')
        ->join('consultant_management_approval_documents', 'consultant_management_approval_document_section_appendix.consultant_management_approval_document_id', '=', 'consultant_management_approval_documents.id')
        ->where('consultant_management_approval_document_section_appendix.id', '=', $sectionAppendix->id)
        ->where('consultant_management_approval_documents.id', '=', $approvalDocument->id);

        $model->orderBy('consultant_management_section_appendix_details.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $item = [
                'id'                  => $record->id,
                'counter'             => $counter,
                'title'               => trim($record->title),
                'attachment_filename' => $record->attachment_filename,
                'section_appendix_id' => $record->consultant_management_approval_document_section_appendix_id,
                'route:download'      => route('consultant.management.approval.document.section.appendix.attachment.download', [$vendorCategoryRfp->id, $record->id]),
                'route:delete'        => route('consultant.management.approval.document.section.appendix.details.delete', [$vendorCategoryRfp->id, $openRfp->id, $record->id]),

            ];
            
            $data[] = $item;
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function print(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId)
    {
        $user = \Confide::user();
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $approvalDocument = $vendorCategoryRfp->approvalDocument;
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);

        $awardedConsultant = ConsultantManagementConsultantRfp::where('consultant_management_rfp_revision_id', '=', $openRfp->consultant_management_rfp_revision_id)
        ->where('awarded', '=', true)
        ->first();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator("Buildspace");
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);

        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle("ApprovalDocument");
        $activeSheet->getColumnDimension('A')->setAutoSize(true);
        $activeSheet->getColumnDimension('B')->setAutoSize(true);

        $activeSheet->getStyle('B')->getNumberFormat()->setFormatCode('#');
        $activeSheet->getStyle('B')->getAlignment()->setHorizontal('left');

        if(file_exists(public_path('img/company-logo.png')))
        {
            $logoPath = public_path('img/company-logo.png');
            $companyName = \PCK\MyCompanyProfiles\MyCompanyProfile::all()->first()->name;
        }
        else
        {
            $logoPath = public_path('img/buildspace-login-logo.png');
            $companyName = 'BuildSpace eProject';
        }

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName($companyName);
        $drawing->setPath($logoPath); // put your path and image here
        $drawing->setCoordinates('A2');
        $drawing->setHeight(98);
        $drawing->setResizeProportional(true);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());

        $labelStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 12
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical'   => Alignment::VERTICAL_TOP
            ]
        ];

        $sectionLabelStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 16
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_TOP
            ]
        ];

        $activeSheet->setCellValue('A7', 'Document Ref No.');
        $activeSheet->getStyle('A7')->applyFromArray($labelStyle);
        $activeSheet->setCellValue('B7', $approvalDocument->document_reference_no);

        $activeSheet->mergeCells('B7:Z7');

        $rowIdx = $this->generateSectionAExcel($vendorCategoryRfp, $spreadsheet, $sectionLabelStyle, $labelStyle);
        
        $rowIdx++;

        $activeSheet->setCellValue('A'.$rowIdx, 'Section B - Project Description');
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($sectionLabelStyle);
        $activeSheet->mergeCells('A'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $activeSheet->setCellValue('A'.$rowIdx, 'Project Brief');
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
        $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setWrapText(true);
        $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        if(mb_strlen($approvalDocument->sectionB->project_brief) > 0)
        {
            $activeSheet->getRowDimension($rowIdx)->setRowHeight(48);
            $activeSheet->setCellValue('B'.$rowIdx, $approvalDocument->sectionB->project_brief);
        }
        $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $rowIdx = $this->generateSectionCExcel($vendorCategoryRfp, $openRfp, $spreadsheet, $rowIdx, $sectionLabelStyle, $labelStyle);

        $rowIdx++;

        $rowIdx = $this->generateSectionDExcel($vendorCategoryRfp, $openRfp, $spreadsheet, $rowIdx, $sectionLabelStyle, $labelStyle);

        $rowIdx++;

        $rowIdx = $this->generateSectionEExcel($vendorCategoryRfp, $openRfp, $spreadsheet, $rowIdx, $sectionLabelStyle, $labelStyle);

        $rowIdx++;

        $rowIdx = $this->generateSectionAppendixExcel($vendorCategoryRfp, $openRfp, $spreadsheet, $rowIdx, $sectionLabelStyle, $labelStyle);

        $writer = new Xlsx($spreadsheet);

        $filepath = Files::getTmpFileUri();

        $writer->save($filepath);

        $filename = 'Approval_Document-'.trim($approvalDocument->document_reference_no).'-'.date("dmYHis");

        return Files::download($filepath, "{$filename}.".Files::EXTENSION_EXCEL);
    }

    private function generateSectionDExcel(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, ConsultantManagementOpenRfp $openRfp, Spreadsheet &$spreadsheet, $rowIdx, $sectionLabelStyle, $labelStyle)
    {
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $approvalDocument = $vendorCategoryRfp->approvalDocument;

        $currencyCode = empty($consultantManagementContract->modified_currency_code) ? $consultantManagementContract->country->currency_code : $consultantManagementContract->modified_currency_code;

        $rowIdx++;

        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A'.$rowIdx, 'Section D - Details of Consultant Services Procurement');
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($sectionLabelStyle);
        $activeSheet->mergeCells('A'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $activeSheet->setCellValue('A'.$rowIdx, 'Consultant Category');
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
        $activeSheet->setCellValue('B'.$rowIdx, $vendorCategoryRfp->vendorCategory->name);
        $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $activeSheet->setCellValue('A'.$rowIdx, 'No. of Consultant Shortlisted');
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
        $activeSheet->setCellValue('B'.$rowIdx, $openRfp->shortlistedCompanies()->get()->count());
        $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $awardedConsultant = ConsultantManagementConsultantRfp::where('consultant_management_rfp_revision_id', '=', $openRfp->consultant_management_rfp_revision_id)
        ->where('awarded', '=', true)
        ->first();

        $latestRfpInterview = ConsultantManagementRfpInterview::select("consultant_management_rfp_interviews.interview_date")
        ->join("consultant_management_rfp_interview_consultants", "consultant_management_rfp_interview_consultants.consultant_management_rfp_interview_id", "=", "consultant_management_rfp_interviews.id")
        ->where("consultant_management_rfp_interviews.vendor_category_rfp_id", "=", $vendorCategoryRfp->id)
        ->where("consultant_management_rfp_interview_consultants.company_id", "=", $awardedConsultant->company_id)
        ->orderBy("consultant_management_rfp_interviews.interview_date", "desc")
        ->first();

        $activeSheet->setCellValue('A'.$rowIdx, 'Interview Date');
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
        $activeSheet->setCellValue('B'.$rowIdx, ($latestRfpInterview) ? Carbon::parse($latestRfpInterview->interview_date)->format('d-M-Y') : "-");
        $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $subSectionLabelStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 14
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical'   => Alignment::VERTICAL_TOP
            ]
        ];

        $tableHeaderStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'ffffff']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '187bcd']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_TOP
            ]
        ];

        $rowIdx++;

        $activeSheet->setCellValue('A'.$rowIdx, trans('vendorProfile.vendorProfile'));
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($subSectionLabelStyle);

        $rowIdx++;

        $activeSheet->setCellValue('B'.$rowIdx, 'Pre-Qualification Rating');
        $activeSheet->getStyle('B'.$rowIdx)->applyFromArray($tableHeaderStyle);
        $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $awardedConsultantCompany = $awardedConsultant->company;

        $latestVendorRegistration = $awardedConsultantCompany->finalVendorRegistration;

        $vendorPreQualificationData = [];

        if($latestVendorRegistration)
        {
            $vendorPreQualifications = VendorPreQualification::select('vendor_pre_qualifications.*')
            ->where('vendor_registration_id', '=', $latestVendorRegistration->id)
            ->join('vendor_work_categories', 'vendor_pre_qualifications.vendor_work_category_id', '=', 'vendor_work_categories.id')
            ->whereNotNull('weighted_node_id')
            ->orderBy('vendor_work_categories.name', 'asc')
            ->get();

            $grading = VendorGroupGrade::getGradeByGroup($awardedConsultantCompany->contract_group_category_id);

            foreach($vendorPreQualifications as $vendorPreQualification)
            {
                $gradeLevel = null;

                if( $grading ) $gradeLevel = $grading->getGrade($vendorPreQualification->score);

                $vendorPreQualificationData[] = [
                    'vendor_work_category' => $vendorPreQualification->vendorWorkCategory->name,
                    'status'               => VendorPreQualification::getStatusText($vendorPreQualification->status_id),
                    'score'                => $vendorPreQualification->score,
                    'grade'                => $gradeLevel ? $gradeLevel->description : null
                ];
            }
        }

        if(!empty($vendorPreQualificationData))
        {
            foreach($vendorPreQualificationData as $idx => $preQData)
            {
                if($idx > 0)
                    $rowIdx++;

                $activeSheet->setCellValue('A'.$rowIdx, trans('vendorManagement.vendorWorkCategories'));
                $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
                $activeSheet->setCellValue('B'.$rowIdx, $preQData['vendor_work_category']);
                $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

                $rowIdx++;

                $activeSheet->setCellValue('A'.$rowIdx, trans('vendorManagement.rating'));
                $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
                $activeSheet->setCellValue('B'.$rowIdx, $preQData['grade']);
                $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

                $rowIdx++;
            }
        }
        else
        {
            $activeSheet->setCellValue('B'.$rowIdx, trans('general.noRecordsFound'));
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;
        }

        $rowIdx++;
        
        $equities = [
            trans('vendorManagement.bumiputeraEquity') => $awardedConsultant->company->bumiputera_equity,
            trans('vendorManagement.nonBumiputeraEquity') => $awardedConsultant->company->non_bumiputera_equity,
            trans('vendorManagement.foreignerEquity') => $awardedConsultant->company->foreigner_equity
        ];

        foreach($equities as $title => $equity)
        {
            $activeSheet->setCellValue('A'.$rowIdx, $title." (%)");
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->getStyle('B'.$rowIdx)->getNumberFormat()->setFormatCode("#,##0.00");
            $activeSheet->setCellValue('B'.$rowIdx, $equity);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;
        }
        
        $registrationInfo = [
            trans('vendorManagement.bumiputera') => ($awardedConsultant->company->is_bumiputera) ? trans('forms.yes') : trans('forms.no'),
            trans('tenders.registrationStatus') => ($latestVendorRegistration) ? $latestVendorRegistration->statusText : "-",
            trans('vendorManagement.expiryDate') => ($awardedConsultant->company->expiry_date) ? Carbon::parse($awardedConsultant->company->expiry_date)->format('d/m/Y') : '-'
        ];

        foreach($registrationInfo as $title => $info)
        {
            $activeSheet->setCellValue('A'.$rowIdx, $title);
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, $info);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;
        }

        $rowIdx++;

        $activeSheet->setCellValue('A'.$rowIdx, "Latest Performance Evaluation Rating");
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($subSectionLabelStyle);

        $rowIdx++;

        $gradingSystem = PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;

        $activeSheet->setCellValue('A'.$rowIdx, "Overall");
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
        $activeSheet->setCellValue('B'.$rowIdx, ($gradingSystem) ? $gradingSystem->getGrade($awardedConsultant->company->getLatestPerformanceEvaluationAverageDeliberatedScore())->description : "-");
        $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $activeSheet->setCellValue('B'.$rowIdx, trans('vendorManagement.vendorWorkCategories'));
        $activeSheet->getStyle('B'.$rowIdx)->applyFromArray($tableHeaderStyle);
        $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        foreach($awardedConsultant->company->vendors as $idx => $vendor)
        {
            if($idx > 0)
                $rowIdx++;
            
            $vendorCycleScore = $vendor->getLatestPerformanceEvaluationCycleScore();

            $activeSheet->setCellValue('A'.$rowIdx, "Work Category");
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, $vendor->vendorWorkCategory->name);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, "Rating");
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, ($gradingSystem && $vendorCycleScore) ? $gradingSystem->getGrade($vendorCycleScore->deliberated_score)->description : "-");
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;
        }

        $rowIdx++;

        $sectionDDetails = $approvalDocument->sectionD->details()->where('company_id', '=', $awardedConsultant->company_id)->first();


        $activeSheet->setCellValue('A'.$rowIdx, "Scope of Services");
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
        $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setWrapText(true);
        $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        if($sectionDDetails && mb_strlen($sectionDDetails->scope_of_services) > 0)
        {
            $activeSheet->getRowDimension($rowIdx)->setRowHeight(48);
            $activeSheet->setCellValue('B'.$rowIdx, $sectionDDetails->scope_of_services);
        }
        $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $rowIdx++;

        $activeSheet->setCellValue('A'.$rowIdx, "Consultant Service Fee");
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($subSectionLabelStyle);

        $rowIdx++;

        $proposedFeeList = ConsultantManagementSubsidiary::select("consultant_management_subsidiaries.id AS subsidiary_id", "consultant_management_consultant_rfp.company_id",
        "consultant_management_consultant_rfp_proposed_fees.id AS proposed_fee_id", "consultant_management_consultant_rfp_common_information.updated_at AS submitted_at",
        "consultant_management_consultant_rfp_proposed_fees.proposed_fee_percentage", "consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount", "consultant_management_subsidiaries.total_construction_cost", "consultant_management_subsidiaries.total_landscape_cost")
        ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_subsidiary_id', '=', 'consultant_management_subsidiaries.id')
        ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->where('consultant_management_consultant_rfp.company_id', '=', $awardedConsultantCompany->id)
        ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
        ->where('consultant_management_subsidiaries.consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->groupBy(\DB::raw("consultant_management_subsidiaries.id, consultant_management_consultant_rfp.id, consultant_management_consultant_rfp_common_information.id, consultant_management_consultant_rfp_proposed_fees.id"))
        ->get()
        ->toArray();

        foreach($consultantManagementContract->consultantManagementSubsidiaries as $idx => $consultantManagementSubsidiary)
        {
            if($idx > 0)
                $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.phase'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, $consultantManagementSubsidiary->subsidiary->full_name);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.projectBudget')." (".$currencyCode.")");
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->getStyle('B'.$rowIdx)->getNumberFormat()->setFormatCode("#,##0.00");
            $activeSheet->setCellValue('B'.$rowIdx, $consultantManagementSubsidiary->project_budget);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, 'Board Scale of Fee');
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);

            $sectionDServiceFee = $approvalDocument->sectionD->consultantServiceFees()->where('consultant_management_subsidiary_id', '=', $consultantManagementSubsidiary->id)->where('company_id', '=', $awardedConsultant->company_id)->first();

            if($sectionDServiceFee)
            {
                $activeSheet->setCellValue('B'.$rowIdx, $sectionDServiceFee->board_scale_of_fee);
            }

            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('B'.$rowIdx, 'Awarded Consultant');
            $activeSheet->getStyle('B'.$rowIdx)->applyFromArray($tableHeaderStyle);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            foreach($proposedFeeList as $consultantProposedFee)
            {
                if($consultantProposedFee['company_id'] == $awardedConsultant->company_id && $consultantProposedFee['subsidiary_id'] == $consultantManagementSubsidiary->id)
                {
                    $activeSheet->setCellValue('A'.$rowIdx, trans('general.name'));
                    $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
                    $activeSheet->setCellValue('B'.$rowIdx, $awardedConsultant->company->name);
                    $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

                    $rowIdx++;

                    $activeSheet->setCellValue('A'.$rowIdx, trans('tenders.amount')." (".$currencyCode.")");
                    $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
                    $activeSheet->getStyle('B'.$rowIdx)->getNumberFormat()->setFormatCode("#,##0.00");
                    $activeSheet->setCellValue('B'.$rowIdx, $consultantProposedFee['proposed_fee_amount']);
                    $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

                    $rowIdx++;

                    $activeSheet->setCellValue('A'.$rowIdx, trans('general.proposedFee')." %");
                    $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
                    $activeSheet->getStyle('B'.$rowIdx)->getNumberFormat()->setFormatCode("#,##0.00");
                    $activeSheet->setCellValue('B'.$rowIdx, $consultantProposedFee['proposed_fee_percentage']);
                    $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

                    $rowIdx++;

                    $activeSheet->setCellValue('A'.$rowIdx, trans('tenders.submittedDate'));
                    $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
                    if($consultantProposedFee['submitted_at'])
                    {
                        $activeSheet->setCellValue('B'.$rowIdx, $consultantManagementContract->getAppTimeZoneTime(Carbon::parse($consultantProposedFee['submitted_at'])->format(\Config::get('dates.created_and_updated_at_formatting'))));
                    }
                    $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

                    $rowIdx++;
                }
            }
            
            $activeSheet->setCellValue('A'.$rowIdx, trans('costData.notes'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setWrapText(true);
            $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            if($sectionDServiceFee && mb_strlen($sectionDServiceFee->notes) > 0)
            {
                $activeSheet->getRowDimension($rowIdx)->setRowHeight(48);
                $activeSheet->setCellValue('B'.$rowIdx,$sectionDServiceFee->notes);
            }
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;
        }

        return $rowIdx;
    }

    private function generateSectionEExcel(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, ConsultantManagementOpenRfp $openRfp, Spreadsheet &$spreadsheet, $rowIdx, $sectionLabelStyle, $labelStyle)
    {
        $approvalDocument = $vendorCategoryRfp->approvalDocument;

        $rowIdx++;

        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A'.$rowIdx, 'Section E - Approval');
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($sectionLabelStyle);
        $activeSheet->mergeCells('A'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $activeSheet->setCellValue('B'.$rowIdx, 'Verifiers');
        $activeSheet->getStyle('B'.$rowIdx)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'ffffff']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '187bcd']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_TOP
            ]
        ]);
        $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;
        
        $verifiers = User::select("users.id", "users.name")
        ->join("consultant_management_approval_document_verifiers", "consultant_management_approval_document_verifiers.user_id", "=", "users.id")
        ->where('consultant_management_approval_document_id', $approvalDocument->id)
        ->orderBy('consultant_management_approval_document_verifiers.id', 'asc')
        ->get();

        foreach($verifiers as $idx => $verifier)
        {
            if($idx > 0)
                $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.name'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, $verifier->name);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;
        }

        return $rowIdx;
    }

    private function generateSectionAppendixExcel(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, ConsultantManagementOpenRfp $openRfp, Spreadsheet &$spreadsheet, $rowIdx, $sectionLabelStyle, $labelStyle)
    {
        $approvalDocument = $vendorCategoryRfp->approvalDocument;
        $sectionAppendix  = $approvalDocument->sectionAppendix;

        $rowIdx++;

        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A'.$rowIdx, 'Appendix');
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($sectionLabelStyle);
        $activeSheet->mergeCells('A'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $appendixes = ApprovalDocumentSectionAppendixDetails::select("consultant_management_section_appendix_details.id AS id", "consultant_management_section_appendix_details.title",
        "consultant_management_section_appendix_details.attachment_filename")
        ->join('consultant_management_approval_document_section_appendix', 'consultant_management_section_appendix_details.consultant_management_approval_document_section_appendix_id', '=', 'consultant_management_approval_document_section_appendix.id')
        ->join('consultant_management_approval_documents', 'consultant_management_approval_document_section_appendix.consultant_management_approval_document_id', '=', 'consultant_management_approval_documents.id')
        ->where('consultant_management_approval_document_section_appendix.id', '=', $sectionAppendix->id)
        ->where('consultant_management_approval_documents.id', '=', $approvalDocument->id)
        ->orderBy('consultant_management_section_appendix_details.created_at', 'desc')
        ->get()
        ->toArray();

        foreach($appendixes as $idx => $appendix)
        {
            if($idx > 0)
                $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.title'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setWrapText(true);
            $activeSheet->setCellValue('B'.$rowIdx, $appendix['title']);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, 'Uploaded Document');
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, $appendix['attachment_filename']);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;
        }

        return $rowIdx;
    }

    private function generateSectionCExcel(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, ConsultantManagementOpenRfp $openRfp, Spreadsheet &$spreadsheet, $rowIdx, $sectionLabelStyle, $labelStyle)
    {
        $rowIdx++;

        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $approvalDocument             = $vendorCategoryRfp->approvalDocument;
        $sectionC                     = $approvalDocument->sectionC;

        $currencyCode = empty($consultantManagementContract->modified_currency_code) ? $consultantManagementContract->country->currency_code : $consultantManagementContract->modified_currency_code;

        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A'.$rowIdx, 'Section C - Summary of Recommendation');
        $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($sectionLabelStyle);
        $activeSheet->mergeCells('A'.$rowIdx.':Z'.$rowIdx);

        $rowIdx++;

        $tableHeaderStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'ffffff']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '187bcd']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_TOP
            ]
        ];

        $consultants = ConsultantManagementCallingRfpCompany::select("companies.id AS id", "companies.name AS company_name", "companies.reference_no", "consultant_management_consultant_rfp.awarded AS awarded",
        "consultant_management_consultant_rfp_common_information.updated_at AS submitted_at", "consultant_management_consultant_rfp_common_information.remarks AS remarks", "consultant_management_calling_rfp.closing_rfp_date")
        ->join('companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'companies.id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->leftJoin('consultant_management_consultant_rfp', function($join){
            $join->on('consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id');
            $join->on('consultant_management_consultant_rfp.company_id','=', 'companies.id');
        })
        ->leftJoin('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->orderBy('companies.name', 'asc')
        ->get();

        foreach($consultantManagementContract->consultantManagementSubsidiaries as $consultantManagementSubsidiary)
        {
            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.phase'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setWrapText(true);
            $activeSheet->setCellValue('B'.$rowIdx, $consultantManagementSubsidiary->subsidiary->name);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.consultantCategories'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, $vendorCategoryRfp->vendorCategory->name);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.developmentType'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, $consultantManagementSubsidiary->developmentType->title);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('B'.$rowIdx, 'Consultants');
            $activeSheet->getStyle('B'.$rowIdx)->applyFromArray($tableHeaderStyle);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $proposedFeeList = ConsultantManagementSubsidiary::select("consultant_management_subsidiaries.id AS subsidiary_id", "consultant_management_consultant_rfp.company_id",
            "consultant_management_consultant_rfp_proposed_fees.id AS proposed_fee_id",
            "consultant_management_consultant_rfp_proposed_fees.proposed_fee_percentage", "consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount", "consultant_management_subsidiaries.total_construction_cost", "consultant_management_subsidiaries.total_landscape_cost")
            ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_subsidiary_id', '=', 'consultant_management_subsidiaries.id')
            ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
            ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
            ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
            ->where('consultant_management_subsidiaries.id', '=', $consultantManagementSubsidiary->id)
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->where('consultant_management_subsidiaries.consultant_management_contract_id', '=', $consultantManagementContract->id)
            ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
            ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
            ->groupBy(\DB::raw("consultant_management_subsidiaries.id, consultant_management_consultant_rfp.id, consultant_management_consultant_rfp_proposed_fees.id"))
            ->get()
            ->toArray();

            $proposedFees = [];
            foreach($proposedFeeList as $item)
            {
                $proposedFees[$item['company_id']] = [
                    'id'         => $item['proposed_fee_id'],
                    'percentage' => $item['proposed_fee_percentage'],
                    'amount'     => $item['proposed_fee_amount']
                ];
            }

            unset($proposedFeeList);
            
            $sectionCDetails = ApprovalDocumentSectionCDetails::select("companies.id AS company_id", "consultant_management_section_c_details.consultant_management_subsidiary_id",
            "consultant_management_section_c_details.remarks")
            ->join("consultant_management_approval_document_section_c", "consultant_management_approval_document_section_c.id", "=", "consultant_management_section_c_details.consultant_management_approval_document_section_c_id")
            ->join("consultant_management_approval_documents", "consultant_management_approval_documents.id", "=", "consultant_management_approval_document_section_c.consultant_management_approval_document_id")
            ->join("consultant_management_vendor_categories_rfp", "consultant_management_vendor_categories_rfp.id", "=", "consultant_management_approval_documents.vendor_category_rfp_id")
            ->join("companies", "companies.id", "=", "consultant_management_section_c_details.company_id")
            ->where("consultant_management_vendor_categories_rfp.id", "=", $vendorCategoryRfp->id)
            ->where("consultant_management_section_c_details.consultant_management_subsidiary_id", "=", $consultantManagementSubsidiary->id)
            ->where("consultant_management_section_c_details.consultant_management_approval_document_section_c_id", '=', $sectionC->id)
            ->lists("remarks", "company_id");

            foreach($consultants as $idx => $consultant)
            {
                if($idx)
                    $rowIdx++;

                $activeSheet->setCellValue('A'.$rowIdx, trans('general.name'));
                $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
                $activeSheet->setCellValue('B'.$rowIdx, $consultant->company_name);
                $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

                $rowIdx++;

                $activeSheet->setCellValue('A'.$rowIdx, trans('tenders.amount')." (".$currencyCode.")");
                $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
                if(array_key_exists($consultant->id, $proposedFees))
                {
                    $activeSheet->getStyle('B'.$rowIdx)->getNumberFormat()->setFormatCode("#,##0.00");
                    $activeSheet->setCellValue('B'.$rowIdx, $proposedFees[$consultant->id]['amount']);
                }
                $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

                $rowIdx++;

                $activeSheet->setCellValue('A'.$rowIdx, trans('general.proposedFee')." %");
                $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
                if(array_key_exists($consultant->id, $proposedFees))
                {
                    $activeSheet->getStyle('B'.$rowIdx)->getNumberFormat()->setFormatCode("#,##0.00");
                    $activeSheet->setCellValue('B'.$rowIdx, $proposedFees[$consultant->id]['percentage']);
                }
                $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

                $rowIdx++;

                $activeSheet->setCellValue('A'.$rowIdx, trans('general.remarks'));
                $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
                $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setWrapText(true);
                $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                if(array_key_exists($consultant->id, $sectionCDetails) && mb_strlen($sectionCDetails[$consultant->id]) > 0)
                {
                    $activeSheet->getRowDimension($rowIdx)->setRowHeight(48);
                    $activeSheet->setCellValue('B'.$rowIdx, $sectionCDetails[$consultant->id]);
                }
                $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

                $rowIdx++;
            }
        }

        return $rowIdx;
    }

    private function generateSectionAExcel(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, Spreadsheet &$spreadsheet, $sectionLabelStyle, $labelStyle)
    {
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $approvalDocument = $vendorCategoryRfp->approvalDocument;

        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A9', 'Section A - Project Information');
        $activeSheet->getStyle('A9')->applyFromArray($sectionLabelStyle);
        $activeSheet->mergeCells('A9:Z9');

        $activeSheet->setCellValue('A10', trans('projects.title'));
        $activeSheet->getStyle('A10')->applyFromArray($labelStyle);
        $activeSheet->getStyle('B10')->getAlignment()->setWrapText(true);
        $activeSheet->getStyle('B10')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $activeSheet->getRowDimension('10')->setRowHeight(48);
        $activeSheet->setCellValue('B10', $consultantManagementContract->title);
        $activeSheet->mergeCells('B10:Z10');

        $activeSheet->setCellValue('A11', trans('companies.referenceNo'));
        $activeSheet->getStyle('A11')->applyFromArray($labelStyle);
        $activeSheet->setCellValue('B11', $consultantManagementContract->reference_no);
        $activeSheet->mergeCells('B11:Z11');

        $activeSheet->setCellValue('A12', trans('general.subsidiaryTownship'));
        $activeSheet->getStyle('A12')->applyFromArray($labelStyle);
        $activeSheet->setCellValue('B12', $consultantManagementContract->subsidiary->name);
        $activeSheet->mergeCells('B12:Z12');

        $activeSheet->setCellValue('A13', trans('general.description'));
        $activeSheet->getStyle('A13')->applyFromArray($labelStyle);
        $activeSheet->getStyle('B13')->getAlignment()->setWrapText(true);
        $activeSheet->getStyle('B13')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        if(mb_strlen($consultantManagementContract->description) > 0)
        {
            $activeSheet->getRowDimension('13')->setRowHeight(48);
            $activeSheet->setCellValue('B13', $consultantManagementContract->description);
        }
        $activeSheet->mergeCells('B13:Z13');

        $rowIdx = 14;
        $currencyCode = empty($consultantManagementContract->modified_currency_code) ? $consultantManagementContract->country->currency_code : $consultantManagementContract->modified_currency_code;

        foreach($consultantManagementContract->consultantManagementSubsidiaries as $consultantManagementSubsidiary)
        {
            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.subsidiaryTownship')."/".trans('general.phase'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, $consultantManagementSubsidiary->subsidiary->full_name);
            $activeSheet->getStyle('B'.$rowIdx)->getAlignment()->setWrapText(true);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);
            
            $rowIdx++;

            if($vendorCategoryRfp->cost_type == PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LANDSCAPE_COST)
            {
                $title = trans('general.totalLandscapeCost');
                $amount = $consultantManagementSubsidiary->total_landscape_cost;
            }
            else
            {
                $title = trans('general.totalConstructionCost');
                $amount = $consultantManagementSubsidiary->total_construction_cost;
            }

            $activeSheet->setCellValue('A'.$rowIdx, $title." (".$currencyCode.")");
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->getStyle('B'.$rowIdx)->getNumberFormat()->setFormatCode("#,##0.00");
            $activeSheet->setCellValue('B'.$rowIdx, $amount);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.targetPlanningPermission'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, date('d/m/Y', strtotime($consultantManagementSubsidiary->planning_permission_date)));
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.targetBuildingPlan'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, date('d/m/Y', strtotime($consultantManagementSubsidiary->building_plan_date)));
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.targetLaunch'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, date('d/m/Y', strtotime($consultantManagementSubsidiary->launch_date)));
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, trans('general.developmentType'));
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, $consultantManagementSubsidiary->developmentType->title);
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;

            $activeSheet->setCellValue('A'.$rowIdx, 'Approving Authority');
            $activeSheet->getStyle('A'.$rowIdx)->applyFromArray($labelStyle);
            $activeSheet->setCellValue('B'.$rowIdx, $approvalDocument->sectionA->getApprovingAuthorityText());
            $activeSheet->mergeCells('B'.$rowIdx.':Z'.$rowIdx);

            $rowIdx++;
        }

        return $rowIdx;
    }

    public function appendixDetailsInfo(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $appendixDetailsId)
    {
        $appendixDetails = ApprovalDocumentSectionAppendixDetails::findOrFail((int)$appendixDetailsId);

        return Response::json($appendixDetails->toArray());
    }

    public function getAccountCodesList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $data = [];

        $records = ConsultantManagementVendorCategoryRfpAccountCode::select()
            ->where('vendor_category_rfp_id', $vendorCategoryRfp->id)
            ->get();

        $bsAccountCodes = AccountCode::whereIn('id', $records->lists('account_code_id'))->get();

        foreach($records as $record)
        {
            $accountCode = $bsAccountCodes->find($record->account_code_id);

            array_push($data, [
                'id'               => $record->id,
                'description'      => $accountCode->description,
                'amount'           => $record->amount,
                'accountCode'      => $accountCode->code,
                'taxCode'          => $accountCode->tax_code,
            ]);
        }

        return $data;
    }

    public function saveAccountCodeAmounts($vendorCategoryRfp)
    {
        $user = \Confide::user();

        $success       = false;
        $errorMessages = null;

        try
        {
            foreach(Input::get('account_code_amounts') as $amountInfo)
            {
                $record = ConsultantManagementVendorCategoryRfpAccountCode::find($amountInfo['id']);
                $record->amount = $amountInfo['amount'];
                $record->updated_by = $user->id;
                $record->save();
            }

            $success = true;
        }
        catch(\Exception $e)
        {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
        }

        return Response::json([
            'success'       => $success,
            'errorMessages' => $errorMessages,
        ]);
    }
}