<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpVerifier;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;
use PCK\Companies\Company;
use PCK\Users\User;
use PCK\VendorRegistration\VendorRegistration;
use PCK\CompanyPersonnel\CompanyPersonnel;
use PCK\ConsultantManagement\ApprovalDocument;

use PCK\Notifications\EmailNotifier;

use PCK\Forms\ConsultantManagement\CallingRfpForm;
use PCK\Forms\ConsultantManagement\GeneralVerifyForm;

class ConsultantManagementCallingRfpController extends \BaseController
{
    private $callingRfpForm;
    private $generalVerifyForm;
    private $emailNotifier;

    public function __construct(CallingRfpForm $callingRfpForm, GeneralVerifyForm $generalVerifyForm, EmailNotifier $emailNotifier)
    {
        $this->callingRfpForm    = $callingRfpForm;
        $this->generalVerifyForm = $generalVerifyForm;
        $this->emailNotifier     = $emailNotifier;
    }

    public function index(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        return View::make('consultant_management.calling_rfp.index', compact('vendorCategoryRfp'));
    }

    public function list(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementCallingRfp::select("consultant_management_calling_rfp.id AS id", "consultant_management_calling_rfp.status AS status",
        "consultant_management_rfp_revisions.revision AS revision",
        "consultant_management_calling_rfp.created_at AS created_at", "consultant_management_calling_rfp.updated_at AS updated_at")
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
        ->where('consultant_management_vendor_categories_rfp.id', '=', $vendorCategoryRfp->id)
        ->orderBy('consultant_management_rfp_revisions.revision', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'         => $record->id,
                'counter'    => $counter,
                'title'      => ($record->revision > 1) ? 'Calling RFP Resubmission' : 'Calling RFP',
                'revision'   => $record->revision,
                'status'     => $record->status,
                'status_txt' => $record->getStatusText(),
                'created_at' => Carbon::parse($record->created_at)->format('d/m/Y'),
                'updated_at' => Carbon::parse($record->updated_at)->format('d/m/Y'),
                'route:show' => route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function show(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId)
    {
        $user = \Confide::user();

        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);
        
        $listOfConsultant  = $callingRfp->consultantManagementRfpRevision->listOfConsultant;

        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

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
        
        $selectedVerifiers = ConsultantManagementCallingRfpVerifier::select("consultant_management_calling_rfp_verifiers.user_id AS id")
            ->where('consultant_management_calling_rfp_id', $callingRfp->id)
            ->orderBy('consultant_management_calling_rfp_verifiers.id', 'asc')
            ->get();

        return View::make('consultant_management.calling_rfp.show', compact('consultantManagementContract', 'vendorCategoryRfp', 'listOfConsultant', 'callingRfp', 'user', 'verifiers', 'selectedVerifiers'));
    }

    public function store(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->callingRfpForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $callingRfp = ConsultantManagementCallingRfp::findOrFail($inputs['id']);
        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

        if($callingRfp->status != ConsultantManagementCallingRfp::STATUS_DRAFT)
        {
            \Flash::error('Calling RFP cannot be saved because it is not in DRAFT');
            return Redirect::route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id]);
        }

        $callingRfp->calling_rfp_date = $consultantManagementContract->getAppTimeZoneTime($inputs['calling_rfp_date']);
        $callingRfp->closing_rfp_date = $consultantManagementContract->getAppTimeZoneTime($inputs['closing_rfp_date']);
        $callingRfp->updated_by       = $user->id;
        $callingRfp->updated_at       = date('Y-m-d H:i:s');

        $callingRfp->save();

        $isExtended = ($callingRfp->is_extend);

        if(array_key_exists('verifiers', $inputs) && is_array($inputs['verifiers']))
        {
            $verifierIds = array_unique(array_filter($inputs['verifiers']));

            if(!empty($verifierIds))
            {
                ConsultantManagementCallingRfpVerifier::where('consultant_management_calling_rfp_id', $callingRfp->id)
                ->delete();

                $data = [];
                foreach($verifierIds as $id)
                {
                    $data[] = [
                        'consultant_management_calling_rfp_id' => $callingRfp->id,
                        'user_id'    => $id,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                ConsultantManagementCallingRfpVerifier::insert($data);
            }
        }

        $recipientId = null;

        if(array_key_exists('send_to_verify', $inputs))
        {
            $callingRfp->status = ConsultantManagementCallingRfp::STATUS_APPROVAL;
            
            $callingRfp->save();

            $latestVersion = ConsultantManagementCallingRfpVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
            ->groupBy('consultant_management_calling_rfp.id')
            ->first();

            $version = ($latestVersion) ? $latestVersion->version + 1 : 1;

            $verifierIds = ConsultantManagementCallingRfpVerifier::select("consultant_management_calling_rfp_verifiers.id", "consultant_management_calling_rfp_verifiers.user_id")
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
            ->orderBy('consultant_management_calling_rfp_verifiers.id', 'asc')
            ->lists('user_id', 'id');

            $data = [];

            $count = 0;
            foreach($verifierIds as $verifierId => $userId)
            {
                $data[] = [
                    'consultant_management_calling_rfp_verifier_id' => $verifierId,
                    'user_id'    => $userId,
                    'version'    => $version,
                    'status'     => ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING,
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
                ConsultantManagementCallingRfpVerifierVersion::insert($data);
            }
        }

        if($recipientId && $recipient = User::find($recipientId))
        {
            $title = ($isExtended) ? "Extension of Calling RFP" : "Calling RFP";
            $contract = $vendorCategoryRfp->consultantManagementContract;
            $content = [
                'subject' => "Consultant Management - ".$title." Approval (".$contract->Subsidiary->name.")",//need to move this to i10n
                'view'    => 'consultant_management.email.pending_approval',
                'data'    => [
                    'developmentPlanningTitle' => $contract->title,
                    'subsidiaryName'           => $contract->Subsidiary->name,
                    'creator'                  => $user->name,
                    'vendorCategoryName'       => $vendorCategoryRfp->vendorCategory->name,
                    'moduleName'               => $title,
                    'callingRfpDate'           => $callingRfp->calling_rfp_date,
                    'closingRfpDate'           => $callingRfp->closing_rfp_date,
                    'route'                    => route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id])
                ]
            ];
            
            $this->emailNotifier->sendGeneralEmail($content, [$recipient]);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id]);
    }

    public function verify(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->generalVerifyForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$inputs['id']);

        $isExtended = ($callingRfp->is_extend);

        $latestVersion = ConsultantManagementCallingRfpVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
            ->groupBy('consultant_management_calling_rfp.id')
            ->first();

        if($latestVersion && $callingRfp->needApprovalFromUser(Confide::user()) && (array_key_exists('approve', $inputs) or array_key_exists('reject', $inputs)))
        {
            $latestVerifierLogId = ConsultantManagementCallingRfpVerifierVersion::select("consultant_management_call_rfp_verifier_versions.id AS id")
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
            ->where('consultant_management_call_rfp_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_calling_rfp_verifiers.user_id', '=', $user->id)
            ->first();

            if($latestVerifierLogId)
            {
                $latestVerifierLog = ConsultantManagementCallingRfpVerifierVersion::findOrFail($latestVerifierLogId->id);

                $status = ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING;

                $contract = $vendorCategoryRfp->consultantManagementContract;
                $content = [];
                $recipients = [];

                if(array_key_exists('approve', $inputs))
                {
                    $status = ConsultantManagementCallingRfpVerifierVersion::STATUS_APPROVED;

                    $nextVerifier = ConsultantManagementCallingRfpVerifierVersion::select("consultant_management_calling_rfp_verifiers.id AS id", "users.name", "users.email", "users.id AS user_id", "consultant_management_call_rfp_verifier_versions.status")
                        ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
                        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
                        ->join('users', 'consultant_management_calling_rfp_verifiers.user_id', '=', 'users.id')
                        ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
                        ->where('consultant_management_call_rfp_verifier_versions.version', '=', $latestVersion->version)
                        ->where('consultant_management_calling_rfp_verifiers.id', '>', $latestVerifierLog->consultant_management_calling_rfp_verifier_id)
                        ->orderBy('consultant_management_calling_rfp_verifiers.id', 'asc')
                        ->first();
                    
                    if(!$nextVerifier)
                    {
                        $callingRfp->status = ConsultantManagementCallingRfp::STATUS_APPROVED;
                        $callingRfp->is_extend = false;//just reset extend flag in case this is for extension verification
                        $callingRfp->save();

                        $recipients = User::select('users.*')
                            ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
                            ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$contract->id.'
                            AND consultant_management_user_roles.role IN ('.ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT.', '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.')
                            AND consultant_management_user_roles.editor IS TRUE
                            AND users.confirmed IS TRUE
                            AND users.account_blocked_status IS FALSE')
                            ->groupBy('users.id')
                            ->get();

                        if(!empty($recipients))
                        {
                            $title = ($isExtended) ? "Extension of Calling RFP" : "Calling RFP";
                            $content = [
                                'subject' => "Consultant Management - ".$title." Approved (".$contract->Subsidiary->name.")",//need to move this to i10n
                                'view'    => 'consultant_management.email.approved',
                                'data'    => [
                                    'developmentPlanningTitle' => $contract->title,
                                    'subsidiaryName'           => $contract->Subsidiary->name,
                                    'vendorCategoryName'       => $vendorCategoryRfp->vendorCategory->name,
                                    'moduleName'               => $title,
                                    'callingRfpDate'           => $callingRfp->calling_rfp_date,
                                    'closingRfpDate'           => $callingRfp->closing_rfp_date,
                                    'route'                    => route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id])
                                ]
                            ];
                        }

                        $consultantRecipients = User::select("users.*")
                        ->join('consultant_management_consultant_users', 'users.id', '=', 'consultant_management_consultant_users.user_id')
                        ->join('companies', 'users.company_id', '=', 'companies.id')
                        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
                        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'vendors.company_id')
                        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
                        ->where('companies.confirmed', '=', true)
                        ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
                        ->orderBy('companies.name', 'asc')
                        ->groupBy(\DB::raw('users.id, companies.id'))
                        ->get();

                        if(!empty($consultantRecipients))
                        {
                            if($isExtended)
                            {
                                $contentTxt = "The extension for the above RFP has been Extended";
                            }
                            else
                            {
                                $contentTxt = "Your company has been invited to participate into the above RFP";
                            }

                            $consultantContent = [
                                'subject' => "Calling RFP Invitation  (".$contract->Subsidiary->name.")",//need to move this to i10n
                                'view'    => 'consultant_management.email.consultant_invitation',
                                'data'    => [
                                    'developmentPlanningTitle' => $contract->title,
                                    'subsidiaryName'           => $contract->Subsidiary->name,
                                    'vendorCategoryName'       => $vendorCategoryRfp->vendorCategory->name,
                                    'contentTxt'               => $contentTxt,
                                    'callingRfpDate'           => $callingRfp->calling_rfp_date,
                                    'closingRfpDate'           => $callingRfp->closing_rfp_date,
                                    'route'                    => route('consultant.management.consultant.calling.rfp.show', [$callingRfp->id])
                                ]
                            ];

                            $this->emailNotifier->sendGeneralEmail($consultantContent, $consultantRecipients);
                        }
                    }
                    else
                    {
                        $recipient = User::find($nextVerifier->user_id);
                        $recipients = [$recipient];

                        $title = ($isExtended) ? "Extension of Calling RFP" : "Calling RFP";

                        $content = [
                            'subject' => "Consultant Management - ".$title." Approval  (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view'    => 'consultant_management.email.pending_approval',
                            'data'    => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName'           => $contract->Subsidiary->name,
                                'creator'                  => $user->name,
                                'vendorCategoryName'       => $vendorCategoryRfp->vendorCategory->name,
                                'moduleName'               => $title,
                                'callingRfpDate'           => $callingRfp->calling_rfp_date,
                                'closingRfpDate'           => $callingRfp->closing_rfp_date,
                                'route'                    => route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id])
                            ]
                        ];
                    }
                }
                elseif(array_key_exists('reject', $inputs))
                {
                    $status = ConsultantManagementCallingRfpVerifierVersion::STATUS_REJECTED;

                    $callingRfp->status = ConsultantManagementCallingRfp::STATUS_DRAFT;
                    $callingRfp->save();

                    $recipients = User::select('users.*')
                        ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
                        ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$contract->id.'
                        AND consultant_management_user_roles.role IN ('.ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT.', '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.')
                        AND consultant_management_user_roles.editor IS TRUE
                        AND users.confirmed IS TRUE
                        AND users.account_blocked_status IS FALSE')
                        ->groupBy('users.id')
                        ->get();

                    if(!empty($recipients))
                    {
                        $title = ($isExtended) ? "Extension of Calling RFP" : "Calling RFP";
                        $content = [
                            'subject' => "Consultant Management - ".$title." Rejected  (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view'    => 'consultant_management.email.rejected',
                            'data'    => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName'           => $contract->Subsidiary->name,
                                'vendorCategoryName'       => $vendorCategoryRfp->vendorCategory->name,
                                'creator'                  => $user->name,
                                'moduleName'               => $title,
                                'callingRfpDate'           => $callingRfp->calling_rfp_date,
                                'closingRfpDate'           => $callingRfp->closing_rfp_date,
                                'route'                    => route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id])
                            ]
                        ];
                    }
                }

                $latestVerifierLog->remarks    = trim($inputs['remarks']);
                $latestVerifierLog->status     = $status;
                $latestVerifierLog->updated_at = date('Y-m-d H:i:s');

                $latestVerifierLog->save();

                if(!empty($recipients) and !empty($content))
                {
                    $this->emailNotifier->sendGeneralEmail($content, $recipients);
                }
            }
        }

        return Redirect::route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id]);
    }

    public function selectedConsultantList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId)
    {
        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $companyPersonnels = Company::select("company_personnel.id AS id", "companies.id AS company_id", "companies.name as company_name", "company_personnel.name AS name", "company_personnel.identification_number")
        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'vendors.company_id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
            FROM vendor_registrations
            WHERE status = ".VendorRegistration::STATUS_COMPLETED."
            AND deleted_at IS NULL
            GROUP BY company_id) vr"), 'vr.company_id', '=', 'companies.id')
        ->join(\DB::raw("(SELECT id as vr_final_id, company_id, revision
            FROM vendor_registrations
            WHERE status = ".VendorRegistration::STATUS_COMPLETED."
            AND deleted_at IS NULL) vr_final"), function($join){
                $join->on('vr_final.company_id', '=', 'vr.company_id');
                $join->on('vr_final.revision', '=', 'vr.revision');
            }
        )
        ->join('company_personnel', 'vr_final.vr_final_id', '=', 'company_personnel.vendor_registration_id')
        ->where('companies.confirmed', '=', true)
        ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
        ->where('company_personnel.type', '=', CompanyPersonnel::TYPE_DIRECTOR)
        ->whereRaw('LENGTH(company_personnel.name) > 0')
        ->whereRaw('LENGTH(company_personnel.identification_number) > 0')
        ->orderBy('companies.name', 'asc')
        ->groupBy(\DB::raw('companies.id, company_personnel.id'))
        ->get()
        ->toArray();

        $directors = [];

        $pattern = '/\s*/m';
        $replace = '';

        $duplicateDirectors = [];

        foreach($companyPersonnels as $companyPersonnelA)
        {
            $idNoA = preg_replace($pattern, $replace, trim($companyPersonnelA['identification_number']));

            foreach($companyPersonnels as $companyPersonnelB)
            {
                $idNoB = preg_replace($pattern, $replace, trim($companyPersonnelB['identification_number']));

                if($companyPersonnelA['company_id'] != $companyPersonnelB['company_id'] && $idNoA == $idNoB)
                {
                    if(!array_key_exists($companyPersonnelA['company_id'], $duplicateDirectors))
                    {
                        $duplicateDirectors[$companyPersonnelA['company_id']] = [];
                    }

                    if(!array_key_exists($companyPersonnelB['company_id'], $duplicateDirectors))
                    {
                        $duplicateDirectors[$companyPersonnelB['company_id']] = [];
                    }

                    if(!array_key_exists($companyPersonnelA['id'], $duplicateDirectors[$companyPersonnelB['company_id']]))
                    {
                        $duplicateDirectors[$companyPersonnelB['company_id']][$companyPersonnelA['id']] = $companyPersonnelA;
                    }

                    if(!array_key_exists($companyPersonnelB['id'], $duplicateDirectors[$companyPersonnelA['company_id']]))
                    {
                        $duplicateDirectors[$companyPersonnelA['company_id']][$companyPersonnelB['id']] = $companyPersonnelB;
                    }
                }
            }
        }

        unset($companyPersonnels);

        $model = Company::select("companies.id AS id", "companies.name AS name", "companies.reference_no", "consultant_management_calling_rfp_companies.status")
        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'vendors.company_id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->where('companies.confirmed', '=', true)
        ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
        ->orderBy('companies.name', 'asc')
        ->groupBy(\DB::raw('companies.id, consultant_management_calling_rfp_companies.id'));

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                  => $record->id,
                'counter'             => $counter,
                'name'                => trim($record->name),
                'reference_no'        => trim($record->reference_no),
                'vendor_code'         => $record->getVendorCode(),
                'status'              => $record->status,
                'status_txt'          => ConsultantManagementCallingRfpCompany::getStatusTextByStatus($record->status),
                'duplicate_directors' => array_key_exists($record->id, $duplicateDirectors) ? array_values($duplicateDirectors[$record->id]) : [],
                'route:update'        => route('consultant.management.calling.rfp.select.consultant.update', [$vendorCategoryRfp->id, $callingRfp->id]),
                'route:questionnaire' => route('consultant.management.consultant.questionnaire.show', [$vendorCategoryRfp->id, $record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function verifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId)
    {
        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementCallingRfpVerifierVersion::select("consultant_management_call_rfp_verifier_versions.id AS id", "users.name AS name",
        "consultant_management_call_rfp_verifier_versions.version", "consultant_management_call_rfp_verifier_versions.status", "consultant_management_call_rfp_verifier_versions.remarks", "consultant_management_call_rfp_verifier_versions.updated_at")
        ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
        ->join('users', 'users.id', '=', 'consultant_management_calling_rfp_verifiers.user_id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
        ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
        ->orderBy('consultant_management_call_rfp_verifier_versions.version', 'desc')
        ->orderBy('consultant_management_call_rfp_verifier_versions.id', 'asc');

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

    public function extendShow(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId)
    {
        $user                         = \Confide::user();
        $callingRfp                   = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);
        $listOfConsultant             = $callingRfp->consultantManagementRfpRevision->listOfConsultant;
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        if(!$callingRfp->extendableByUser($user))
        {
            return Redirect::route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id]);
        }

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
        
        $selectedVerifiers = ConsultantManagementCallingRfpVerifier::select("consultant_management_calling_rfp_verifiers.user_id AS id")
            ->where('consultant_management_calling_rfp_id', $callingRfp->id)
            ->orderBy('consultant_management_calling_rfp_verifiers.id', 'asc')
            ->get();

        return View::make('consultant_management.calling_rfp.extend.edit', compact('consultantManagementContract', 'vendorCategoryRfp', 'listOfConsultant', 'callingRfp', 'user', 'verifiers', 'selectedVerifiers'));
    }

    public function extendStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->callingRfpForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $callingRfp = ConsultantManagementCallingRfp::findOrFail($inputs['id']);

        if(!$callingRfp->extendableByUser($user))
        {
            \Flash::error('Calling RFP cannot be extended');
            return Redirect::route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id]);
        }
        
        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

        $callingRfp->calling_rfp_date = $consultantManagementContract->getAppTimeZoneTime($inputs['calling_rfp_date']);
        $callingRfp->closing_rfp_date = $consultantManagementContract->getAppTimeZoneTime($inputs['closing_rfp_date']);
        $callingRfp->is_extend        = true;

        if(array_key_exists('verifiers', $inputs) && is_array($inputs['verifiers']))
        {
            $verifierIds = array_unique(array_filter($inputs['verifiers']));

            if(!empty($verifierIds))
            {
                ConsultantManagementCallingRfpVerifier::where('consultant_management_calling_rfp_id', $callingRfp->id)
                ->delete();

                $data = [];
                foreach($verifierIds as $id)
                {
                    $data[] = [
                        'consultant_management_calling_rfp_id' => $callingRfp->id,
                        'user_id'    => $id,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                ConsultantManagementCallingRfpVerifier::insert($data);
            }
        }

        $recipientId = null;

        if(array_key_exists('send_to_verify', $inputs))
        {
            $callingRfp->status = ConsultantManagementCallingRfp::STATUS_APPROVAL;
            
            $latestVersion = ConsultantManagementCallingRfpVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
            ->groupBy('consultant_management_calling_rfp.id')
            ->first();

            $version = ($latestVersion) ? $latestVersion->version + 1 : 1;

            $verifierIds = ConsultantManagementCallingRfpVerifier::select("consultant_management_calling_rfp_verifiers.id", "consultant_management_calling_rfp_verifiers.user_id")
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
            ->orderBy('consultant_management_calling_rfp_verifiers.id', 'asc')
            ->lists('user_id', 'id');

            $data = [];

            $count = 0;
            foreach($verifierIds as $verifierId => $userId)
            {
                $data[] = [
                    'consultant_management_calling_rfp_verifier_id' => $verifierId,
                    'user_id'    => $userId,
                    'version'    => $version,
                    'status'     => ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING,
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
                ConsultantManagementCallingRfpVerifierVersion::insert($data);
            }

            if($recipientId && $recipient = User::find($recipientId))
            {
                $contract = $vendorCategoryRfp->consultantManagementContract;
                $content = [
                    'subject' => "Consultant Management - Extension of Calling RFP Approval (".$contract->Subsidiary->name.")",//need to move this to i10n
                    'view' => 'consultant_management.email.pending_approval',
                    'data' => [
                        'developmentPlanningTitle' => $contract->title,
                        'subsidiaryName' => $contract->Subsidiary->name,
                        'creator' => $user->name,
                        'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                        'moduleName' => 'Extension of Calling RFP',
                        'route' => route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id])
                    ]
                ];
                
                $this->emailNotifier->sendGeneralEmail($content, [$recipient]);
            }
        }
        else
        {
            $callingRfp->status = ConsultantManagementCallingRfp::STATUS_DRAFT;
        }

        $callingRfp->updated_by = $user->id;
        $callingRfp->updated_at = date('Y-m-d H:i:s');

        $callingRfp->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.calling.rfp.show', [$vendorCategoryRfp->id, $callingRfp->id]);
    }
}