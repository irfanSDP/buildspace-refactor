<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultantCompany;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultantVerifier;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultantVerifierVersion;
use PCK\ConsultantManagement\ConsultantUser;

use PCK\Vendor\Vendor;
use PCK\CompanyPersonnel\CompanyPersonnel;
use PCK\VendorRegistration\VendorRegistration;
use PCK\Companies\Company;
use PCK\Users\User;

use PCK\Notifications\EmailNotifier;

use PCK\Forms\ConsultantManagement\RecommendationOfConsultantForm;
use PCK\Forms\ConsultantManagement\GeneralVerifyForm;

class ConsultantManagementRecommendationOfConsultantController extends \BaseController
{
    private $recommendationOfConsultantForm;
    private $generalVerifyForm;

    public function __construct(RecommendationOfConsultantForm $recommendationOfConsultantForm, GeneralVerifyForm $generalVerifyForm, EmailNotifier $emailNotifier)
    {
        $this->recommendationOfConsultantForm = $recommendationOfConsultantForm;
        $this->generalVerifyForm = $generalVerifyForm;
        $this->emailNotifier = $emailNotifier;
    }

    public function index(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $recommendationOfConsultant = $vendorCategoryRfp->recommendationOfConsultant;

        $verifiers = User::select(\DB::raw("users.id, users.name"))
        ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
        ->whereRaw('consultant_management_user_roles.role = '.ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT.'
        AND consultant_management_user_roles.user_id = users.id')
        ->where('consultant_management_user_roles.consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->whereRaw('users.confirmed IS TRUE')
        ->whereRaw('users.account_blocked_status IS FALSE')
        ->orderBy('users.name', 'asc')
        ->get();
        
        $selectedVerifiers = [];

        if($recommendationOfConsultant)
        {
            $selectedVerifiers = ConsultantManagementRecommendationOfConsultantVerifier::select("consultant_management_recommendation_of_consultant_verifiers.user_id AS id")
            ->where('consultant_management_recommendation_of_consultant_id', $recommendationOfConsultant->id)
            ->orderBy('consultant_management_recommendation_of_consultant_verifiers.id', 'asc')
            ->get();
        }
        
        return View::make('consultant_management.recommendation_of_consultant.index', compact('recommendationOfConsultant', 'vendorCategoryRfp', 'consultantManagementContract', 'verifiers', 'selectedVerifiers'));
    }

    public function store(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->recommendationOfConsultantForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

        $recommendationOfConsultant = ConsultantManagementRecommendationOfConsultant::find($inputs['id']);

        if(!$recommendationOfConsultant)
        {
            $recommendationOfConsultant = new ConsultantManagementRecommendationOfConsultant();

            $recommendationOfConsultant->vendor_category_rfp_id = $vendorCategoryRfp->id;
            $recommendationOfConsultant->created_by = $user->id;
        }

        $recommendationOfConsultant->calling_rfp_proposed_date = $consultantManagementContract->getAppTimeZoneTime($inputs['calling_rfp_proposed_date']);
        $recommendationOfConsultant->closing_rfp_proposed_date = $consultantManagementContract->getAppTimeZoneTime($inputs['closing_rfp_proposed_date']);
        $recommendationOfConsultant->proposed_fee              = $inputs['proposed_fee'];
        $recommendationOfConsultant->remarks                   = $inputs['remarks'];
        $recommendationOfConsultant->updated_by                = $user->id;

        $recommendationOfConsultant->save();

        if(array_key_exists('verifiers', $inputs) && is_array($inputs['verifiers']))
        {
            $verifierIds = array_unique(array_filter($inputs['verifiers']));

            if(!empty($verifierIds))
            {
                ConsultantManagementRecommendationOfConsultantVerifier::where('consultant_management_recommendation_of_consultant_id', $recommendationOfConsultant->id)
                ->delete();

                $data = [];
                foreach($verifierIds as $id)
                {
                    $data[] = [
                        'consultant_management_recommendation_of_consultant_id' => $recommendationOfConsultant->id,
                        'user_id'    => $id,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                ConsultantManagementRecommendationOfConsultantVerifier::insert($data);
            }
        }

        $recipientId = null;

        if(array_key_exists('send_to_verify', $inputs))
        {
            $recommendationOfConsultant->status = ConsultantManagementRecommendationOfConsultant::STATUS_APPROVAL;
            
            $recommendationOfConsultant->save();

            $latestVersion = ConsultantManagementRecommendationOfConsultantVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
            ->where('consultant_management_recommendation_of_consultants.id', '=', $recommendationOfConsultant->id)
            ->groupBy('consultant_management_recommendation_of_consultants.id')
            ->first();

            $version = ($latestVersion) ? $latestVersion->version + 1 : 1;

            $verifierIds = ConsultantManagementRecommendationOfConsultantVerifier::select("consultant_management_recommendation_of_consultant_verifiers.id", "consultant_management_recommendation_of_consultant_verifiers.user_id")
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
            ->where('consultant_management_recommendation_of_consultants.id', '=', $recommendationOfConsultant->id)
            ->orderBy('consultant_management_recommendation_of_consultant_verifiers.id', 'asc')
            ->lists('user_id', 'id');

            $data = [];

            $count = 0;
            foreach($verifierIds as $verifierId => $userId)
            {
                $data[] = [
                    'consultant_management_recommendation_of_consultant_verifier_id' => $verifierId,
                    'user_id'    => $userId,
                    'version'    => $version,
                    'status'     => ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_PENDING,
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
                ConsultantManagementRecommendationOfConsultantVerifierVersion::insert($data);
            }
        }

        if($recipientId && $recipient = User::find($recipientId))
        {
            $contract = $vendorCategoryRfp->consultantManagementContract;
            $content = [
                'subject' => "Consultant Management - Recommendation of Consultant Approval  (".$contract->Subsidiary->name.")",//need to move this to i10n
                'view' => 'consultant_management.email.pending_approval',
                'data' => [
                    'developmentPlanningTitle' => $contract->title,
                    'subsidiaryName' => $contract->Subsidiary->name,
                    'creator' => $user->name,
                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                    'moduleName' => 'Recommendation of Consultant',
                    'route' => route('consultant.management.roc.index', [$vendorCategoryRfp->id])
                ]
            ];
            
            $this->emailNotifier->sendGeneralEmail($content, [$recipient]);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.roc.index', [$vendorCategoryRfp->id]);
    }

    public function verifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementRecommendationOfConsultantVerifierVersion::select("consultant_management_roc_verifier_versions.id AS id", "users.name AS name",
        "consultant_management_roc_verifier_versions.version", "consultant_management_roc_verifier_versions.status", "consultant_management_roc_verifier_versions.remarks", "consultant_management_roc_verifier_versions.updated_at")
        ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
        ->join('users', 'users.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.user_id')
        ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
        ->where('consultant_management_recommendation_of_consultants.vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->orderBy('consultant_management_roc_verifier_versions.version', 'desc')
        ->orderBy('consultant_management_roc_verifier_versions.id', 'asc');

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

    public function selectedConsultantList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $companyPersonnels = Company::select("company_personnel.id AS id", "companies.id AS company_id", "companies.name as company_name", "company_personnel.name AS name", "company_personnel.identification_number")
        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
        ->join('consultant_management_recommendation_of_consultant_companies', 'consultant_management_recommendation_of_consultant_companies.company_id', '=', 'vendors.company_id')
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_recommendation_of_consultant_companies.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
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
        ->where('consultant_management_vendor_categories_rfp.id', '=', $vendorCategoryRfp->id)
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

        $model = Company::select("companies.id AS id", "companies.name AS name", "companies.reference_no", "consultant_management_recommendation_of_consultant_companies.status")
        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
        ->join('consultant_management_recommendation_of_consultant_companies', 'consultant_management_recommendation_of_consultant_companies.company_id', '=', 'vendors.company_id')
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_recommendation_of_consultant_companies.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
        ->where('companies.confirmed', '=', true)
        ->where('consultant_management_vendor_categories_rfp.id', '=', $vendorCategoryRfp->id)
        ->orderBy('companies.name', 'asc')
        ->groupBy(\DB::raw('companies.id, consultant_management_recommendation_of_consultant_companies.id'));

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
                'status_txt'          => ConsultantManagementRecommendationOfConsultantCompany::getStatusTextByStatus($record->status),
                'duplicate_directors' => array_key_exists($record->id, $duplicateDirectors) ? array_values($duplicateDirectors[$record->id]) : [],
                'route:update'        => route('consultant.management.roc.select.consultant.update', [$vendorCategoryRfp->id, $record->id]),
                'route:delete'        => route('consultant.management.roc.select.consultant.delete', [$vendorCategoryRfp->id, $record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function consultantList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
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
                FROM consultant_management_recommendation_of_consultant_companies
                WHERE consultant_management_recommendation_of_consultant_companies.vendor_category_rfp_id = ".$vendorCategoryRfp->id."
                AND consultant_management_recommendation_of_consultant_companies.company_id = companies.id
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

    public function selectConsultantStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user   = \Confide::user();
        $inputs = Input::all();

        if(array_key_exists('consultants', $inputs) && !empty($inputs['consultants']))
        {
            $selectedConsultantIds = Company::select("companies.id AS id")
            ->join('vendors', 'companies.id', '=', 'vendors.company_id')
            ->join('consultant_management_recommendation_of_consultant_companies', 'consultant_management_recommendation_of_consultant_companies.company_id', '=', 'vendors.company_id')
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultant_companies.vendor_category_rfp_id', '=', 'consultant_management_recommendation_of_consultants.vendor_category_rfp_id')
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_recommendation_of_consultants.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
            ->where('companies.confirmed', '=', true)
            ->where('consultant_management_vendor_categories_rfp.id', '=', $vendorCategoryRfp->id)
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
                        'vendor_category_rfp_id' => $vendorCategoryRfp->id,
                        'company_id' => $id,
                        'status'     => ConsultantManagementRecommendationOfConsultantCompany::STATUS_PENDING,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                ConsultantManagementRecommendationOfConsultantCompany::insert($data);

                ConsultantUser::createConsultantUserFromCompanyIds($filteredConsultantIds, $user);
            }
        }

        return Response::json([
            'success' => true
        ]);
    }

    public function selectConsultantUpdate(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $user     = \Confide::user();
        $inputs   = Input::all();
        $company  = Company::findOrFail((int)$companyId);
        $statuses = [
            ConsultantManagementRecommendationOfConsultantCompany::STATUS_PENDING,
            ConsultantManagementRecommendationOfConsultantCompany::STATUS_YES,
            ConsultantManagementRecommendationOfConsultantCompany::STATUS_NO
        ];

        $consultantCompany = ConsultantManagementRecommendationOfConsultantCompany::where('vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
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

    public function verify(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->generalVerifyForm->validate(Input::all());
        
        $user   = \Confide::user();
        $inputs = Input::all();

        $recommendationOfConsultant = $vendorCategoryRfp->recommendationOfConsultant;

        $latestVersion = ConsultantManagementRecommendationOfConsultantVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
            ->where('consultant_management_recommendation_of_consultants.id', '=', $recommendationOfConsultant->id)
            ->groupBy('consultant_management_recommendation_of_consultants.id')
            ->first();

        if($latestVersion && $recommendationOfConsultant->needApprovalFromUser(Confide::user()) && (array_key_exists('approve', $inputs) or array_key_exists('reject', $inputs)))
        {
            $latestVerifierLogId = ConsultantManagementRecommendationOfConsultantVerifierVersion::select("consultant_management_roc_verifier_versions.id AS id")
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
            ->where('consultant_management_recommendation_of_consultants.id', '=', $recommendationOfConsultant->id)
            ->where('consultant_management_roc_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_recommendation_of_consultant_verifiers.user_id', '=', $user->id)
            ->first();

            if($latestVerifierLogId)
            {
                $latestVerifierLog = ConsultantManagementRecommendationOfConsultantVerifierVersion::findOrFail($latestVerifierLogId->id);

                $status = ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_PENDING;

                $contract = $vendorCategoryRfp->consultantManagementContract;
                $content = [];
                $recipients = [];
                
                if(array_key_exists('approve', $inputs))
                {
                    $status = ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_APPROVED;

                    $nextVerifier = ConsultantManagementRecommendationOfConsultantVerifierVersion::select("consultant_management_recommendation_of_consultant_verifiers.id AS id", "users.name", "users.email", "users.id AS user_id", "consultant_management_roc_verifier_versions.status")
                        ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
                        ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
                        ->join('users', 'consultant_management_recommendation_of_consultant_verifiers.user_id', '=', 'users.id')
                        ->where('consultant_management_recommendation_of_consultants.id', '=', $recommendationOfConsultant->id)
                        ->where('consultant_management_roc_verifier_versions.version', '=', $latestVersion->version)
                        ->where('consultant_management_recommendation_of_consultant_verifiers.id', '>', $latestVerifierLog->consultant_management_recommendation_of_consultant_verifier_id)
                        ->orderBy('consultant_management_recommendation_of_consultant_verifiers.id', 'asc')
                        ->first();
                    
                    if(!$nextVerifier)
                    {
                        $recommendationOfConsultant->status = ConsultantManagementRecommendationOfConsultant::STATUS_APPROVED;
                        $recommendationOfConsultant->save();

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
                                'subject' => "Consultant Management - Recommendation of Consultant Approved  (".$contract->Subsidiary->name.")",//need to move this to i10n
                                'view' => 'consultant_management.email.approved',
                                'data' => [
                                    'developmentPlanningTitle' => $contract->title,
                                    'subsidiaryName' => $contract->Subsidiary->name,
                                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                    'moduleName' => 'Recommendation of Consultant',
                                    'route' => route('consultant.management.roc.index', [$vendorCategoryRfp->id])
                                ]
                            ];
                        }
                    }
                    else
                    {
                        $recipient = User::find($nextVerifier->user_id);
                        $recipients = [$recipient];

                        $content = [
                            'subject' => "Consultant Management - Recommendation of Consultant Approval  (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.pending_approval',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'creator' => $user->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'moduleName' => 'Recommendation of Consultant',
                                'route' => route('consultant.management.roc.index', $vendorCategoryRfp->id)
                                ]
                        ];
                        
                    }
                }
                elseif(array_key_exists('reject', $inputs))
                {
                    $status = ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_REJECTED;

                    $recommendationOfConsultant->status = ConsultantManagementRecommendationOfConsultant::STATUS_DRAFT;
                    $recommendationOfConsultant->save();

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
                            'subject' => "Consultant Management - Recommendation of Consultant Rejected  (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.rejected',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'creator' => $user->name,
                                'moduleName' => 'Recommendation of Consultant',
                                'route' => route('consultant.management.roc.index', [$vendorCategoryRfp->id])
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

        return Redirect::route('consultant.management.roc.index', $vendorCategoryRfp->id);
    }

    public function selectConsultantDelete(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $user    = \Confide::user();
        $company = Company::findOrFail((int)$companyId);

        $consultantCompany = ConsultantManagementRecommendationOfConsultantCompany::where('vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->where('company_id', '=', $company->id)
        ->first();

        if($consultantCompany && (!$vendorCategoryRfp->recommendationOfConsultant || ($vendorCategoryRfp->recommendationOfConsultant && $vendorCategoryRfp->recommendationOfConsultant->editableByUser($user))))
        {

            $consultantCompany->delete();

            \Log::info("Remove consultant from recommendation of consultant [vendor category rfp id: {$vendorCategoryRfp->id}][company id:{$company->id}]");
        }

        return Redirect::route('consultant.management.roc.index', $vendorCategoryRfp->id);
    }
}