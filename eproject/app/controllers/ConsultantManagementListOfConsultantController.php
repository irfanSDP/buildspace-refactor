<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultantCompany;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultantVerifier;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultantVerifierVersion;
use PCK\ConsultantManagement\ConsultantUser;

use PCK\Vendor\Vendor;
use PCK\CompanyPersonnel\CompanyPersonnel;
use PCK\VendorRegistration\VendorRegistration;
use PCK\Companies\Company;
use PCK\Users\User;
use PCK\ModulePermission\ModulePermission;

use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\ObjectField\ObjectField;
use PCK\Base\Upload;

use PCK\Notifications\EmailNotifier;

use PCK\Forms\ConsultantManagement\ListOfConsultantForm;
use PCK\Forms\ConsultantManagement\GeneralVerifyForm;

class ConsultantManagementListOfConsultantController extends \BaseController
{
    private $listOfConsultantForm;
    private $generalVerifyForm;
    private $emailNotifier;

    public function __construct(ListOfConsultantForm $listOfConsultantForm, GeneralVerifyForm $generalVerifyForm, EmailNotifier $emailNotifier)
    {
        $this->listOfConsultantForm = $listOfConsultantForm;
        $this->generalVerifyForm = $generalVerifyForm;
        $this->emailNotifier = $emailNotifier;
    }

    public function index(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        return View::make('consultant_management.list_of_consultant.index', compact('vendorCategoryRfp'));
    }

    public function list(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementListOfConsultant::select("consultant_management_list_of_consultants.id AS id", "consultant_management_list_of_consultants.status AS status",
        "consultant_management_rfp_revisions.revision AS revision",
        "consultant_management_list_of_consultants.created_at AS created_at", "consultant_management_list_of_consultants.updated_at AS updated_at")
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_list_of_consultants.consultant_management_rfp_revision_id')
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
                'title'      => ($record->revision > 1) ? 'List of Consultant Resubmission' : 'List of Consultant',
                'revision'   => $record->revision,
                'status'     => $record->status,
                'status_txt' => $record->getStatusText(),
                'created_at' => Carbon::parse($record->created_at)->format('d/m/Y'),
                'updated_at' => Carbon::parse($record->updated_at)->format('d/m/Y'),
                'route:show' => route('consultant.management.loc.show', [$vendorCategoryRfp->id, $record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function show(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $listOfConsultantId)
    {
        $user = \Confide::user();

        $listOfConsultant = ConsultantManagementListOfConsultant::findOrFail((int)$listOfConsultantId);

        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

        $verifiers = User::select(\DB::raw("users.id, users.name"))
        ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
        ->whereRaw('consultant_management_user_roles.role = '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.'
        AND consultant_management_user_roles.user_id = users.id')
        ->where('consultant_management_user_roles.consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->whereRaw('users.confirmed IS TRUE')
        ->whereRaw('users.account_blocked_status IS FALSE')
        ->orderBy('users.name', 'asc')
        ->get();

        $verifiers = $verifiers->merge(ModulePermission::getUserList(ModulePermission::MODULE_ID_TOP_MANAGEMENT_VERIFIERS));

        $selectedVerifiers = ConsultantManagementListOfConsultantVerifier::select("consultant_management_list_of_consultant_verifiers.user_id AS id")
            ->where('consultant_management_list_of_consultant_id', $listOfConsultant->id)
            ->orderBy('consultant_management_list_of_consultant_verifiers.id', 'asc')
            ->get();

        return View::make('consultant_management.list_of_consultant.show', compact('consultantManagementContract', 'vendorCategoryRfp', 'listOfConsultant', 'user', 'verifiers', 'selectedVerifiers'));
    }

    public function store(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->listOfConsultantForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $listOfConsultant = ConsultantManagementListOfConsultant::findOrFail($inputs['id']);
        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

        if($listOfConsultant->status != ConsultantManagementListOfConsultant::STATUS_DRAFT)
        {
            \Flash::error('List of Consultant cannot be saved because it is not in DRAFT');
            return Redirect::route('consultant.management.loc.show', [$vendorCategoryRfp->id, $listOfConsultant->id]);
        }

        $listOfConsultant->calling_rfp_date = $consultantManagementContract->getAppTimeZoneTime($inputs['calling_rfp_date']);
        $listOfConsultant->closing_rfp_date = $consultantManagementContract->getAppTimeZoneTime($inputs['closing_rfp_date']);
        $listOfConsultant->proposed_fee     = $inputs['proposed_fee'];
        $listOfConsultant->remarks          = $inputs['remarks'];
        $listOfConsultant->updated_by       = $user->id;
        $listOfConsultant->updated_at       = date('Y-m-d H:i:s');

        $listOfConsultant->save();

        if(array_key_exists('verifiers', $inputs) && is_array($inputs['verifiers']))
        {
            $verifierIds = array_unique(array_filter($inputs['verifiers']));

            if(!empty($verifierIds))
            {
                ConsultantManagementListOfConsultantVerifier::where('consultant_management_list_of_consultant_id', $listOfConsultant->id)
                ->delete();

                $data = [];
                foreach($verifierIds as $id)
                {
                    $data[] = [
                        'consultant_management_list_of_consultant_id' => $listOfConsultant->id,
                        'user_id'    => $id,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                ConsultantManagementListOfConsultantVerifier::insert($data);
            }
        }

        $recipientId = null;

        if(array_key_exists('send_to_verify', $inputs))
        {
            $listOfConsultant->status = ConsultantManagementListOfConsultant::STATUS_APPROVAL;
            
            $listOfConsultant->save();

            $latestVersion = ConsultantManagementListOfConsultantVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
            ->groupBy('consultant_management_list_of_consultants.id')
            ->first();

            $version = ($latestVersion) ? $latestVersion->version + 1 : 1;

            $verifierIds = ConsultantManagementListOfConsultantVerifier::select("consultant_management_list_of_consultant_verifiers.id", "consultant_management_list_of_consultant_verifiers.user_id")
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
            ->orderBy('consultant_management_list_of_consultant_verifiers.id', 'asc')
            ->lists('user_id', 'id');

            $data = [];

            $count = 0;
            foreach($verifierIds as $verifierId => $userId)
            {
                $data[] = [
                    'consultant_management_list_of_consultant_verifier_id' => $verifierId,
                    'user_id'    => $userId,
                    'version'    => $version,
                    'status'     => ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING,
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
                ConsultantManagementListOfConsultantVerifierVersion::insert($data);
            }
        }

        if($recipientId && $recipient = User::find($recipientId))
        {
            $contract = $vendorCategoryRfp->consultantManagementContract;
            $content = [
                'subject' => "Consultant Management - List of Consultant Approval  (".$contract->Subsidiary->name.")",//need to move this to i10n
                'view' => 'consultant_management.email.pending_approval',
                'data' => [
                    'developmentPlanningTitle' => $contract->title,
                    'subsidiaryName' => $contract->Subsidiary->name,
                    'creator' => $user->name,
                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                    'moduleName' => 'List of Consultant',
                    'route' => route('consultant.management.loc.show', [$vendorCategoryRfp->id, $listOfConsultant->id])
                ]
            ];
            
            $this->emailNotifier->sendGeneralEmail($content, [$recipient]);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.loc.show', [$vendorCategoryRfp->id, $listOfConsultant->id]);
    }

    public function verify(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->generalVerifyForm->validate(Input::all());
        
        $user   = \Confide::user();
        $inputs = Input::all();

        $listOfConsultant = ConsultantManagementListOfConsultant::findOrFail((int)$inputs['id']);

        $latestVersion = ConsultantManagementListOfConsultantVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
            ->groupBy('consultant_management_list_of_consultants.id')
            ->first();

        if($latestVersion && $listOfConsultant->needApprovalFromUser(Confide::user()) && (array_key_exists('approve', $inputs) or array_key_exists('reject', $inputs)))
        {
            $latestVerifierLogId = ConsultantManagementListOfConsultantVerifierVersion::select("consultant_management_loc_verifier_versions.id AS id")
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
            ->where('consultant_management_loc_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_list_of_consultant_verifiers.user_id', '=', $user->id)
            ->first();

            if($latestVerifierLogId)
            {
                $latestVerifierLog = ConsultantManagementListOfConsultantVerifierVersion::findOrFail($latestVerifierLogId->id);

                $status = ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING;

                $contract = $vendorCategoryRfp->consultantManagementContract;
                $content = [];
                $recipients = [];

                if(array_key_exists('approve', $inputs))
                {
                    $status = ConsultantManagementListOfConsultantVerifierVersion::STATUS_APPROVED;

                    $nextVerifier = ConsultantManagementListOfConsultantVerifierVersion::select("consultant_management_list_of_consultant_verifiers.id AS id", "users.name", "users.email", "users.id AS user_id", "consultant_management_loc_verifier_versions.status")
                        ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
                        ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
                        ->join('users', 'consultant_management_list_of_consultant_verifiers.user_id', '=', 'users.id')
                        ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
                        ->where('consultant_management_loc_verifier_versions.version', '=', $latestVersion->version)
                        ->where('consultant_management_list_of_consultant_verifiers.id', '>', $latestVerifierLog->consultant_management_list_of_consultant_verifier_id)
                        ->orderBy('consultant_management_list_of_consultant_verifiers.id', 'asc')
                        ->first();

                    if(!$nextVerifier)
                    {
                        $listOfConsultant->status = ConsultantManagementListOfConsultant::STATUS_APPROVED;
                        $listOfConsultant->save();

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
                            $content = [
                                'subject' => "Consultant Management - List of Consultant Approved  (".$contract->Subsidiary->name.")",//need to move this to i10n
                                'view' => 'consultant_management.email.approved',
                                'data' => [
                                    'developmentPlanningTitle' => $contract->title,
                                    'subsidiaryName' => $contract->Subsidiary->name,
                                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                    'moduleName' => 'List of Consultant',
                                    'route' => route('consultant.management.loc.show', [$vendorCategoryRfp->id, $listOfConsultant->id])
                                ]
                            ];
                        }
                    }
                    else
                    {
                        $recipient = User::find($nextVerifier->user_id);
                        $recipients = [$recipient];

                        $content = [
                            'subject' => "Consultant Management - List of Consultant Approval  (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.pending_approval',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'creator' => $user->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'moduleName' => 'List of Consultant',
                                'route' => route('consultant.management.loc.show', [$vendorCategoryRfp->id, $listOfConsultant->id])
                                ]
                        ];
                    }
                }
                elseif(array_key_exists('reject', $inputs))
                {
                    $status = ConsultantManagementListOfConsultantVerifierVersion::STATUS_REJECTED;

                    $listOfConsultant->status = ConsultantManagementListOfConsultant::STATUS_DRAFT;
                    $listOfConsultant->save();

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
                        $content = [
                            'subject' => "Consultant Management - List of Consultant Rejected  (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.rejected',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'creator' => $user->name,
                                'moduleName' => 'List of Consultant',
                                'route' => route('consultant.management.loc.show', [$vendorCategoryRfp->id, $listOfConsultant->id])
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

        return Redirect::route('consultant.management.loc.show', [$vendorCategoryRfp->id, $listOfConsultant->id]);
    }

    public function selectedConsultantList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $listOfConsultantId)
    {
        $listOfConsultant = ConsultantManagementListOfConsultant::findOrFail((int)$listOfConsultantId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $companyPersonnels = Company::select("company_personnel.id AS id", "companies.id AS company_id", "companies.name as company_name", "company_personnel.name AS name", "company_personnel.identification_number")
        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
        ->join('consultant_management_list_of_consultant_companies', 'consultant_management_list_of_consultant_companies.company_id', '=', 'vendors.company_id')
        ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultant_companies.consultant_management_list_of_consultant_id', '=', 'consultant_management_list_of_consultants.id')
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
        ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
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

        $model = Company::select("companies.id AS id", "companies.name AS name", "companies.reference_no", "consultant_management_list_of_consultant_companies.status")
        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
        ->join('consultant_management_list_of_consultant_companies', 'consultant_management_list_of_consultant_companies.company_id', '=', 'vendors.company_id')
        ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultant_companies.consultant_management_list_of_consultant_id', '=', 'consultant_management_list_of_consultants.id')
        ->where('companies.confirmed', '=', true)
        ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
        ->orderBy('companies.name', 'asc')
        ->groupBy(\DB::raw('companies.id, consultant_management_list_of_consultant_companies.id'));

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
                'status_txt'          => ConsultantManagementListOfConsultantCompany::getStatusTextByStatus($record->status),
                'duplicate_directors' => array_key_exists($record->id, $duplicateDirectors) ? array_values($duplicateDirectors[$record->id]) : [],
                'route:update'        => route('consultant.management.loc.select.consultant.update', [$vendorCategoryRfp->id, $listOfConsultant->id]),
                'route:delete'        => route('consultant.management.loc.select.consultant.delete', [$vendorCategoryRfp->id, $listOfConsultant->id, $record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function consultantList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $listOfConsultantId)
    {
        $listOfConsultant = ConsultantManagementListOfConsultant::findOrFail((int)$listOfConsultantId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Company::select("companies.id AS id", "companies.name AS name", "companies.reference_no")
        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
        ->join('company_vendor_category', 'company_vendor_category.company_id', '=', 'companies.id')
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.vendor_category_id', '=', 'company_vendor_category.vendor_category_id')
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
            })
        ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
            FROM vendor_registrations
            WHERE deleted_at IS NULL
            GROUP BY company_id) vr_latest"), 'vr_latest.company_id', '=', 'companies.id')
        ->join(\DB::raw("(SELECT status, submission_type, company_id, revision
            FROM vendor_registrations
            WHERE deleted_at IS NULL) vr_status"), function($join){
                $join->on('vr_status.company_id', '=', 'vr_latest.company_id');
                $join->on('vr_status.revision', '=', 'vr_latest.revision');
            }
        );

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                $searchStr = '%'.urldecode($val).'%';

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', $searchStr);
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.reference_no', 'ILIKE', $searchStr);
                        }
                        break;
                    case 'vendor_code':
                        if(strlen($val) > 0)
                        {
                            $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
                            $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

                            $model->where(\DB::raw("'" . $vendorCodePrefix . "' || LPAD(companies.id::text, " . $vendorCodePadLength . ", '0')"), 'ILIKE', $searchStr);
                        }
                        break;
                }
            }
        }

        $model->whereRaw("
            NOT EXISTS (
                SELECT 1
                FROM consultant_management_list_of_consultant_companies
                WHERE consultant_management_list_of_consultant_companies.consultant_management_list_of_consultant_id = ".$listOfConsultant->id."
                AND consultant_management_list_of_consultant_companies.company_id = companies.id
            )
        ");

        $model->where('companies.confirmed', '=', true)
        ->where('consultant_management_vendor_categories_rfp.id', '=', $vendorCategoryRfp->id)
        ->where('vendors.type', '=', Vendor::TYPE_ACTIVE)
        ->whereNull('companies.deactivated_at')
        ->whereNotNull('activation_date')
        ->orderBy('companies.name', 'asc')
        ->groupBy(\DB::raw('companies.id'));
        
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
                'reference_no' => trim($record->reference_no),
                'vendor_code'  => $record->getVendorCode()
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function selectConsultantStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $listOfConsultantId)
    {
        $listOfConsultant = ConsultantManagementListOfConsultant::findOrFail((int)$listOfConsultantId);

        $user   = \Confide::user();
        $inputs = Input::all();

        if(array_key_exists('consultants', $inputs) && !empty($inputs['consultants']))
        {
            $selectedConsultantIds = Company::select("companies.id AS id")
            ->join('vendors', 'companies.id', '=', 'vendors.company_id')
            ->join('consultant_management_list_of_consultant_companies', 'consultant_management_list_of_consultant_companies.company_id', '=', 'vendors.company_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultant_companies.consultant_management_list_of_consultant_id', '=', 'consultant_management_list_of_consultants.id')
            ->where('companies.confirmed', '=', true)
            ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
            ->orderBy('companies.name', 'asc')
            ->groupBy(\DB::raw('companies.id'))
            ->lists('id');

            $filteredConsultantIds = array_diff($inputs['consultants'], $selectedConsultantIds);

            if(!empty($filteredConsultantIds))
            {
                $data = [];
                foreach($filteredConsultantIds as $id)
                {
                    $data[] = [
                        'consultant_management_list_of_consultant_id' => $listOfConsultant->id,
                        'company_id' => $id,
                        'status'     => ConsultantManagementListOfConsultantCompany::STATUS_PENDING,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                ConsultantManagementListOfConsultantCompany::insert($data);

                ConsultantUser::createConsultantUserFromCompanyIds($filteredConsultantIds, $user);
            }
        }

        return Response::json([
            'success' => true
        ]);
    }

    public function selectConsultantUpdate(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $listOfConsultantId)
    {
        $listOfConsultant = ConsultantManagementListOfConsultant::findOrFail((int)$listOfConsultantId);

        $user     = \Confide::user();
        $inputs   = Input::all();
        $company  = Company::findOrFail((int)$inputs['cid']);
        $statuses = [
            ConsultantManagementListOfConsultantCompany::STATUS_PENDING,
            ConsultantManagementListOfConsultantCompany::STATUS_YES,
            ConsultantManagementListOfConsultantCompany::STATUS_NO
        ];

        $consultantCompany = ConsultantManagementListOfConsultantCompany::where('consultant_management_list_of_consultant_id', '=', $listOfConsultant->id)
        ->where('company_id', '=', $company->id)
        ->first();

        $updated = false;
        $item    = [];

        if($consultantCompany && array_key_exists('val', $inputs) && in_array((int)$inputs['val'], $statuses))
        {
            $consultantCompany->status = (int)$inputs['val'];
            $consultantCompany->save();

            $item = [
                'status_txt' => $consultantCompany->getStatusText(),
                'status'     => $consultantCompany->status
            ];

            $updated = true;
        }

        return Response::json([
            'updated' => $updated,
            'item'    => $item
        ]);
    }

    public function selectConsultantDelete(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $listOfConsultantId, $companyId)
    {
        $listOfConsultant = ConsultantManagementListOfConsultant::findOrFail((int)$listOfConsultantId);

        $user    = \Confide::user();
        $company = Company::findOrFail((int)$companyId);

        $consultantCompany = ConsultantManagementListOfConsultantCompany::where('consultant_management_list_of_consultant_id', '=', $listOfConsultant->id)
        ->where('company_id', '=', $company->id)
        ->first();

        if($consultantCompany && $listOfConsultant->editableByUser($user))
        {

            $consultantCompany->delete();

            \Log::info("Remove consultant from list of consultant [list of consultant id: {$listOfConsultant->id}][company id:{$company->id}]");
        }

        return Redirect::route('consultant.management.loc.show', [$vendorCategoryRfp->id, $listOfConsultant->id]);
    }

    public function verifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $listOfConsultantId)
    {
        $listOfConsultant = ConsultantManagementListOfConsultant::findOrFail((int)$listOfConsultantId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementListOfConsultantVerifierVersion::select("consultant_management_loc_verifier_versions.id AS id", "users.name AS name",
        "consultant_management_loc_verifier_versions.version", "consultant_management_loc_verifier_versions.status", "consultant_management_loc_verifier_versions.remarks", "consultant_management_loc_verifier_versions.updated_at")
        ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
        ->join('users', 'users.id', '=', 'consultant_management_list_of_consultant_verifiers.user_id')
        ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
        ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
        ->orderBy('consultant_management_loc_verifier_versions.version', 'desc')
        ->orderBy('consultant_management_loc_verifier_versions.id', 'asc');

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

    public function attachmentCount($id, $field)
    {
        $loc    = ConsultantManagementListOfConsultant::findOrFail($id);
        $object = ObjectField::findOrCreateNew($loc, $field);

        return Response::json([
            'phase_id'        => $loc->id,
            'field'           => $field,
            'attachmentCount' => count($this->getAttachmentDetails($object)),
        ]);
    }

    public function attachmentList($id, $field)
    {
        $loc           = ConsultantManagementListOfConsultant::findOrFail($id);
        $object        = ObjectField::findOrCreateNew($loc, $field);
        $uploadedFiles = $this->getAttachmentDetails($object);

        $data = [];

        foreach($uploadedFiles as $file)
        {
            $file['imgSrc']      = $file->generateThumbnailURL();
            $file['deleteRoute'] = $file->generateDeleteURL();
            $file['size']	     = Helpers::formatBytes($file->size);
            $file['deleteRoute'] = route('consultant.management.list.of.consultant.attachment.delete', [$loc->id, $field, $file->id]);

            $data[] = $file;
        }

        return $data;
    }

    public function attachmentStore($id, $field)
    {
        $inputs = Input::all();
        $loc    = ConsultantManagementListOfConsultant::findOrFail($id);
        $object = ObjectField::findOrCreateNew($loc, $field);

        ModuleAttachment::saveAttachments($object, $inputs);

        return [
            'success' => true
        ];
    }

    public function attachmentDelete($id, $field, $fileId)
    {
        $loc     = ConsultantManagementListOfConsultant::findOrFail($id);
        $upload  = Upload::findOrFail($fileId);
        $success = false;

        try
        {
            $upload->delete();

            $success = true;
        }
        catch(\Exception $e)
        {
            $success = false;
        }

        return [
            'success' => $success,
            'count_url' => route('consultant.management.list.of.consultant.attachment.count', [$loc->id, $field])
        ];
    }
}