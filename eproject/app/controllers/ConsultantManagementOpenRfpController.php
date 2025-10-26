<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementSubsidiary;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfpAccountCode;
use PCK\ConsultantManagement\ConsultantManagementOpenRfp;
use PCK\ConsultantManagement\ConsultantManagementOpenRfpVerifier;
use PCK\ConsultantManagement\ConsultantManagementOpenRfpVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementRfpResubmissionVerifier;
use PCK\ConsultantManagement\ConsultantManagementRfpResubmissionVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfp;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfpCommonInformation;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfpProposedFee;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;
use PCK\ConsultantManagement\ConsultantManagementRfpRevision;
use PCK\ConsultantManagement\ConsultantManagementConsultantAttachment;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfpAttachment;
use PCK\Users\User;
use PCK\Companies\Company;

use PCK\Notifications\EmailNotifier;

use PCK\Forms\ConsultantManagement\OpenRfpVerifierForm;
use PCK\Forms\ConsultantManagement\OpenRfpResubmissionVerifierForm;
use PCK\Forms\ConsultantManagement\OpenRfpResubmissionVerifyForm;
use PCK\Forms\ConsultantManagement\GeneralVerifyForm;

class ConsultantManagementOpenRfpController extends \BaseController
{
    private $openRfpVerifierForm;
    private $openRfpResubmissionVerifierForm;
    private $openRfpResubmissionVerifyForm;
    private $generalVerifyForm;
    private $emailNotifier;

    public function __construct(OpenRfpVerifierForm $openRfpVerifierForm, OpenRfpResubmissionVerifierForm $openRfpResubmissionVerifierForm, OpenRfpResubmissionVerifyForm $openRfpResubmissionVerifyForm, GeneralVerifyForm $generalVerifyForm, EmailNotifier $emailNotifier)
    {
        $this->openRfpVerifierForm = $openRfpVerifierForm;
        $this->openRfpResubmissionVerifierForm = $openRfpResubmissionVerifierForm;
        $this->openRfpResubmissionVerifyForm = $openRfpResubmissionVerifyForm;
        $this->generalVerifyForm = $generalVerifyForm;
        $this->emailNotifier = $emailNotifier;
    }

    public function index(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user = \Confide::user();

        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

        return View::make('consultant_management.open_rfp.index', compact('vendorCategoryRfp', 'consultantManagementContract', 'user'));
    }

    public function list(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user = \Confide::user();
        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $submissionList = ConsultantManagementConsultantRfpCommonInformation::select(\DB::raw("consultant_management_rfp_revisions.id, COUNT(consultant_management_consultant_rfp_common_information.id) AS total_submission"))
        ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
        ->where('consultant_management_vendor_categories_rfp.id', '=', $vendorCategoryRfp->id)
        ->groupBy("consultant_management_rfp_revisions.id")
        ->lists('total_submission', 'id');

        $model = ConsultantManagementOpenRfp::select("consultant_management_open_rfp.id AS id", "consultant_management_open_rfp.status AS status",
        "consultant_management_rfp_revisions.revision AS revision", "consultant_management_rfp_revisions.id AS revision_id", "consultant_management_calling_rfp.closing_rfp_date")
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
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
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => ($record->revision > 1) ? 'RFP Resubmission' : 'RFP',
                'revision'             => $record->revision,
                'total_rfp_submission' => (array_key_exists($record->revision_id, $submissionList)) ? $submissionList[$record->revision_id] : 0,
                'status'               => $record->status,
                'status_txt'           => $record->getStatusText(),
                'closing_date'         => $consultantManagementContract->getAppTimeZoneTime(Carbon::parse($record->closing_rfp_date)->format(\Config::get('dates.created_and_updated_at_formatting'))),
                'route:show'           => route('consultant.management.open.rfp.show', [$vendorCategoryRfp->id, $record->id]),
                'route:verifier'           => route('consultant.management.open.rfp.verifier', [$vendorCategoryRfp->id, $record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function show(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId)
    {
        $user = \Confide::user();

        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);

        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;
        
        $noOfResubmissionVerifier = ConsultantManagementRfpResubmissionVerifier::where('consultant_management_open_rfp_id', $openRfp->id)->count();

        $showResubmissionVerifierLog = ($noOfResubmissionVerifier > 0);
        
        return View::make('consultant_management.open_rfp.show', compact('consultantManagementContract', 'vendorCategoryRfp', 'openRfp', 'user', 'showResubmissionVerifierLog'));
    }

    public function consultantList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId)
    {
        $user = \Confide::user();
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);
        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $proposedFeeList = ConsultantManagementSubsidiary::select("consultant_management_subsidiaries.id AS subsidiary_id", "consultant_management_consultant_rfp.company_id",
        "consultant_management_consultant_rfp_proposed_fees.proposed_fee_percentage", "consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount", "consultant_management_subsidiaries.total_construction_cost", "consultant_management_subsidiaries.total_landscape_cost")
        ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_subsidiary_id', '=', 'consultant_management_subsidiaries.id')
        ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
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
            if(!array_key_exists($item['company_id'], $proposedFees))
            {
                $proposedFees[$item['company_id']] = [];
            }

            $proposedFees[$item['company_id']][] = [
                'subsidiary_id' => $item['subsidiary_id'],
                'percentage' => $item['proposed_fee_percentage'],
                'amount' => $item['proposed_fee_amount']
            ];
        }

        $generalTotalAttachments = Company::select(\DB::raw('companies.id, COUNT(consultant_management_consultant_attachments.id) AS total_attachment'))
        ->join('consultant_management_consultant_attachments', 'consultant_management_consultant_attachments.company_id', '=', 'companies.id')
        ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_attachments.id')
        ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
        ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
        ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantAttachment')
        ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
        ->where('consultant_management_consultant_attachments.vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->groupBy('companies.id')
        ->lists('total_attachment', 'id');

        $rfpTotalAttachments = Company::select(\DB::raw('companies.id, COUNT(consultant_management_consultant_rfp_attachments.id) AS total_attachment'))
        ->join('consultant_management_consultant_rfp_attachments', 'consultant_management_consultant_rfp_attachments.company_id', '=', 'companies.id')
        ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_rfp_attachments.id')
        ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
        ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
        ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantRfpAttachment')
        ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
        ->where('consultant_management_consultant_rfp_attachments.vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->groupBy('companies.id')
        ->lists('total_attachment', 'id');

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
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'company_name':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('companies.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $generalTotalAttachment = array_key_exists($record->id, $generalTotalAttachments) ? $generalTotalAttachments[$record->id] : 0;
            $rfpTotalAttachment = array_key_exists($record->id, $rfpTotalAttachments) ? $rfpTotalAttachments[$record->id] : 0;

            $item = [
                'id'                    => $record->id,
                'counter'               => $counter,
                'company_name'          => $record->company_name,
                'reference_no'          => $record->reference_no,
                'remarks'               => trim($record->remarks),
                'awarded'               => ($record->awarded) ? $record->awarded : false,
                'attachments_count'     => ($generalTotalAttachment + $rfpTotalAttachment),
                'closing_date'          => $consultantManagementContract->getAppTimeZoneTime(Carbon::parse($record->closing_rfp_date)->format(\Config::get('dates.created_and_updated_at_formatting'))),
                'submitted_at'          => ($record->submitted_at) ? $consultantManagementContract->getAppTimeZoneTime(Carbon::parse($record->submitted_at)->format(\Config::get('dates.created_and_updated_at_formatting'))) : null,
                'route:attachment-list' => route('consultant.management.consultant.attachment.uploaded.ajax.list', [$vendorCategoryRfp->id, $record->id]),
                'route:show'            => route('consultant.management.open.rfp.show', [$vendorCategoryRfp->id, $record->id]),
            ];

            if(array_key_exists($record->id, $proposedFees))
            {
                foreach($proposedFees[$record->id] as $proposedFeeItem)
                {
                    $item['consultant_'.$proposedFeeItem['subsidiary_id'].'_amount'] = number_format($proposedFeeItem['amount'], 2, '.', ',');
                    $item['consultant_'.$proposedFeeItem['subsidiary_id'].'_percentage'] = number_format($proposedFeeItem['percentage'], 2, '.', '');
                }

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

    public function verifier(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId)
    {
        $user = \Confide::user();

        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);

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
        
        $selectedVerifiers = ConsultantManagementOpenRfpVerifier::select("consultant_management_open_rfp_verifiers.user_id AS id")
            ->where('consultant_management_open_rfp_id', $openRfp->id)
            ->orderBy('consultant_management_open_rfp_verifiers.id', 'asc')
            ->get();

        return View::make('consultant_management.open_rfp.verifier', compact('consultantManagementContract', 'vendorCategoryRfp', 'openRfp' , 'verifiers', 'selectedVerifiers', 'user'));
    }

    public function verifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId)
    {
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementOpenRfpVerifierVersion::select("consultant_management_open_rfp_verifier_versions.id AS id", "users.name AS name",
        "consultant_management_open_rfp_verifier_versions.version", "consultant_management_open_rfp_verifier_versions.status", "consultant_management_open_rfp_verifier_versions.remarks", "consultant_management_open_rfp_verifier_versions.updated_at")
        ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
        ->join('users', 'users.id', '=', 'consultant_management_open_rfp_verifiers.user_id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
        ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
        ->orderBy('consultant_management_open_rfp_verifier_versions.version', 'desc')
        ->orderBy('consultant_management_open_rfp_verifier_versions.id', 'asc');

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

    public function verifierUpdate(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->openRfpVerifierForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $openRfp = ConsultantManagementOpenRfp::findOrFail($inputs['id']);
        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

        if(array_key_exists('verifiers', $inputs) && is_array($inputs['verifiers']))
        {
            $verifierIds = array_unique(array_filter($inputs['verifiers']));

            if(!empty($verifierIds))
            {
                ConsultantManagementOpenRfpVerifier::where('consultant_management_open_rfp_id', $openRfp->id)
                ->delete();

                $data = [];
                foreach($verifierIds as $id)
                {
                    $data[] = [
                        'consultant_management_open_rfp_id' => $openRfp->id,
                        'user_id'    => $id,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                ConsultantManagementOpenRfpVerifier::insert($data);
            }
        }

        if(array_key_exists('send_to_verify', $inputs))
        {
            $openRfp->status = ConsultantManagementOpenRfp::STATUS_APPROVAL;
            
            $openRfp->save();

            $latestVersion = ConsultantManagementOpenRfpVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->groupBy('consultant_management_open_rfp.id')
            ->first();

            $version = ($latestVersion) ? $latestVersion->version + 1 : 1;

            $verifierIds = ConsultantManagementOpenRfpVerifier::select("consultant_management_open_rfp_verifiers.id", "consultant_management_open_rfp_verifiers.user_id")
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->orderBy('consultant_management_open_rfp_verifiers.id', 'asc')
            ->lists('user_id', 'id');

            $data = [];
            $recipients = [];

            foreach($verifierIds as $verifierId => $userId)
            {
                $data[] = [
                    'consultant_management_open_rfp_verifier_id' => $verifierId,
                    'user_id'    => $userId,
                    'version'    => $version,
                    'status'     => ConsultantManagementOpenRfpVerifierVersion::STATUS_PENDING,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $recipient = User::find($userId);
                $recipients[] = $recipient;
            }

            if(!empty($recipients))
            {
                $content = [
                    'subject' => "Consultant Management - RFP Opening Verification (".$consultantManagementContract->Subsidiary->name.")",//need to move this to i10n
                    'view' => 'consultant_management.email.pending_approval',
                    'data' => [
                        'developmentPlanningTitle' => $consultantManagementContract->title,
                        'subsidiaryName' => $consultantManagementContract->Subsidiary->name,
                        'creator' => $user->name,
                        'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                        'moduleName' => 'RFP Opening',
                        'route' => route('consultant.management.open.rfp.verifier', [$vendorCategoryRfp->id, $openRfp->id])
                        ]
                ];

                $this->emailNotifier->sendGeneralEmail($content, $recipients);
            }

            if($data)
            {
                ConsultantManagementOpenRfpVerifierVersion::insert($data);
            }
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.open.rfp.verifier', [$vendorCategoryRfp->id, $openRfp->id]);
    }

    public function verify(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->generalVerifyForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$inputs['id']);

        $latestVersion = ConsultantManagementOpenRfpVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->groupBy('consultant_management_open_rfp.id')
            ->first();

        if($latestVersion && $openRfp->needApprovalFromUser($user) && (array_key_exists('approve', $inputs) or array_key_exists('reject', $inputs)))
        {
            $latestVerifierLogId = ConsultantManagementOpenRfpVerifierVersion::select("consultant_management_open_rfp_verifier_versions.id AS id")
            ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->where('consultant_management_open_rfp_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_open_rfp_verifiers.user_id', '=', $user->id)
            ->first();

            if($latestVerifierLogId)
            {
                $latestVerifierLog = ConsultantManagementOpenRfpVerifierVersion::findOrFail($latestVerifierLogId->id);

                $status = ConsultantManagementOpenRfpVerifierVersion::STATUS_PENDING;

                $contract = $vendorCategoryRfp->consultantManagementContract;

                if(array_key_exists('approve', $inputs))
                {
                    $status = ConsultantManagementOpenRfpVerifierVersion::STATUS_APPROVED;

                    $pendingVerification = ConsultantManagementOpenRfpVerifierVersion::select("consultant_management_open_rfp_verifiers.id AS id", "consultant_management_open_rfp_verifier_versions.status")
                        ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
                        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
                        ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
                        ->where('consultant_management_open_rfp_verifier_versions.version', '=', $latestVersion->version)
                        ->where('consultant_management_open_rfp_verifiers.id', '<>', $latestVerifierLog->consultant_management_open_rfp_verifier_id)
                        ->where('consultant_management_open_rfp_verifier_versions.status', '<>', ConsultantManagementOpenRfpVerifierVersion::STATUS_APPROVED)
                        ->count();
                    
                    if(!$pendingVerification)
                    {
                        $openRfp->status = ConsultantManagementOpenRfp::STATUS_APPROVED;
                        $openRfp->save();

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
                                'subject' => "Consultant Management - RFP Opening Approved (".$contract->Subsidiary->name.")",//need to move this to i10n
                                'view' => 'consultant_management.email.approved',
                                'data' => [
                                    'developmentPlanningTitle' => $contract->title,
                                    'subsidiaryName' => $contract->Subsidiary->name,
                                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                    'moduleName' => 'RFP Opening',
                                    'route' => route('consultant.management.open.rfp.show', [$vendorCategoryRfp->id, $openRfp->id])
                                ]
                            ];

                            $this->emailNotifier->sendGeneralEmail($content, $recipients);
                        }
                    }
                }
                elseif(array_key_exists('reject', $inputs))
                {
                    $status = ConsultantManagementOpenRfpVerifierVersion::STATUS_REJECTED;

                    $openRfp->status = ConsultantManagementOpenRfp::STATUS_DRAFT;
                    $openRfp->save();

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
                            'subject' => "Consultant Management - RFP Opening Rejected (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.rejected',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'creator' => $user->name,
                                'moduleName' => 'RFP Opening',
                                'route' => route('consultant.management.open.rfp.show', [$vendorCategoryRfp->id, $openRfp->id])
                                ]
                        ];

                        $this->emailNotifier->sendGeneralEmail($content, $recipients);
                    }
                }

                $latestVerifierLog->remarks    = trim($inputs['remarks']);
                $latestVerifierLog->status     = $status;
                $latestVerifierLog->updated_at = date('Y-m-d H:i:s');

                $latestVerifierLog->save();
            }
        }

        return Redirect::route('consultant.management.open.rfp.verifier', [$vendorCategoryRfp->id, $openRfp->id]);
    }

    public function resubmission(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId)
    {
        $user = \Confide::user();

        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);

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
        
        $selectedVerifiers = ConsultantManagementRfpResubmissionVerifier::select("consultant_management_rfp_resubmission_verifiers.user_id AS id")
            ->where('consultant_management_open_rfp_id', $openRfp->id)
            ->orderBy('consultant_management_rfp_resubmission_verifiers.id', 'asc')
            ->get();

        return View::make('consultant_management.open_rfp.resubmission_verifier', compact('consultantManagementContract', 'vendorCategoryRfp', 'openRfp' , 'verifiers', 'selectedVerifiers', 'user'));
    }

    public function resubmissionVerifierUpdate(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->openRfpResubmissionVerifierForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $openRfp = ConsultantManagementOpenRfp::findOrFail($inputs['id']);
        $consultantManagementContract =  $vendorCategoryRfp->consultantManagementContract;

        $openRfp->updated_by       = $user->id;
        $openRfp->updated_at       = date('Y-m-d H:i:s');

        $openRfp->save();

        if(array_key_exists('verifiers', $inputs) && is_array($inputs['verifiers']))
        {
            $verifierIds = array_unique(array_filter($inputs['verifiers']));
            
            if(!empty($verifierIds))
            {
                ConsultantManagementRfpResubmissionVerifier::where('consultant_management_open_rfp_id', $openRfp->id)
                ->delete();

                $data = [];
                foreach($verifierIds as $id)
                {
                    $data[] = [
                        'consultant_management_open_rfp_id' => $openRfp->id,
                        'user_id'    => $id,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                ConsultantManagementRfpResubmissionVerifier::insert($data);
            }
        }

        if(array_key_exists('send_to_verify', $inputs))
        {
            $openRfp->status = ConsultantManagementOpenRfp::STATUS_RESUBMISSION_APPROVAL;
            
            $openRfp->save();

            $latestVersion = ConsultantManagementRfpResubmissionVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id', '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->groupBy('consultant_management_open_rfp.id')
            ->first();

            $version = ($latestVersion) ? $latestVersion->version + 1 : 1;

            $verifierIds = ConsultantManagementRfpResubmissionVerifier::select("consultant_management_rfp_resubmission_verifiers.id", "consultant_management_rfp_resubmission_verifiers.user_id")
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->orderBy('consultant_management_rfp_resubmission_verifiers.id', 'asc')
            ->lists('user_id', 'id');

            $data = [];
            $recipientId = null;
            $count = 0;

            foreach($verifierIds as $verifierId => $userId)
            {
                $data[] = [
                    'consultant_management_rfp_resubmission_verifier_id' => $verifierId,
                    'user_id'    => $userId,
                    'version'    => $version,
                    'status'     => ConsultantManagementRfpResubmissionVerifierVersion::STATUS_PENDING,
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
                ConsultantManagementRfpResubmissionVerifierVersion::insert($data);
            }

            if($recipientId && $recipient = User::find($recipientId))
            {
                $content = [
                    'subject' => "Consultant Management - RFP Resubmission Approval (".$consultantManagementContract->Subsidiary->name.")",//need to move this to i10n
                    'view' => 'consultant_management.email.pending_approval',
                    'data' => [
                        'developmentPlanningTitle' => $consultantManagementContract->title,
                        'subsidiaryName' => $consultantManagementContract->Subsidiary->name,
                        'creator' => $user->name,
                        'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                        'moduleName' => 'RFP Resubmission',
                        'route' => route('consultant.management.open.rfp.show', [$vendorCategoryRfp->id, $openRfp->id])
                    ]
                ];
                
                $this->emailNotifier->sendGeneralEmail($content, [$recipient]);
            }
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.open.rfp.resubmission', [$vendorCategoryRfp->id, $openRfp->id]);
    }

    public function resubmissionVerify(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->openRfpResubmissionVerifyForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $openRfp  = ConsultantManagementOpenRfp::findOrFail((int)$inputs['id']);
        $contract = $vendorCategoryRfp->consultantManagementContract;

        $latestVersion = ConsultantManagementRfpResubmissionVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id', '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->groupBy('consultant_management_open_rfp.id')
            ->first();

        if($latestVersion && $openRfp->needResubmissionApprovalFromUser(Confide::user()) && (array_key_exists('approve', $inputs) or array_key_exists('reject', $inputs)))
        {
            $latestVerifierLogId = ConsultantManagementRfpResubmissionVerifierVersion::select("consultant_management_rfp_resubmission_verifier_versions.id AS id")
            ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id', '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->where('consultant_management_rfp_resubmission_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_rfp_resubmission_verifiers.user_id', '=', $user->id)
            ->first();

            if($latestVerifierLogId)
            {
                $latestVerifierLog = ConsultantManagementRfpResubmissionVerifierVersion::findOrFail($latestVerifierLogId->id);

                $status = ConsultantManagementRfpResubmissionVerifierVersion::STATUS_PENDING;
                $content = [];
                $recipients = [];

                if(array_key_exists('approve', $inputs))
                {
                    $status = ConsultantManagementRfpResubmissionVerifierVersion::STATUS_APPROVED;

                    $nextVerifier = ConsultantManagementRfpResubmissionVerifierVersion::select("consultant_management_rfp_resubmission_verifiers.id AS id", "users.name", "users.email", "users.id AS user_id", "consultant_management_rfp_resubmission_verifier_versions.status")
                        ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id', '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
                        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
                        ->join('users', 'consultant_management_rfp_resubmission_verifiers.user_id', '=', 'users.id')
                        ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
                        ->where('consultant_management_rfp_resubmission_verifier_versions.version', '=', $latestVersion->version)
                        ->where('consultant_management_rfp_resubmission_verifiers.id', '>', $latestVerifierLog->consultant_management_rfp_resubmission_verifier_id)
                        ->orderBy('consultant_management_rfp_resubmission_verifiers.id', 'asc')
                        ->first();
                    
                    if(!$nextVerifier)
                    {
                        $rfpRevision = new ConsultantManagementRfpRevision;

                        $rfpRevision->vendor_category_rfp_id = $vendorCategoryRfp->id;
                        $rfpRevision->created_by = $user->id;
                        $rfpRevision->updated_by = $user->id;
                        $rfpRevision->created_at = date('Y-m-d H:i:s');
                        $rfpRevision->updated_at = date('Y-m-d H:i:s');

                        $rfpRevision->save();

                        $openRfp->status = ConsultantManagementOpenRfp::STATUS_RESUBMISSION_APPROVED;
                        $openRfp->save();

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
                                'subject' => "Consultant Management - RFP Resubmission Approved (".$contract->Subsidiary->name.")",//need to move this to i10n
                                'view' => 'consultant_management.email.approved',
                                'data' => [
                                    'developmentPlanningTitle' => $contract->title,
                                    'subsidiaryName' => $contract->Subsidiary->name,
                                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                    'moduleName' => 'RFP Resubmission',
                                    'route' => route('consultant.management.open.rfp.show', [$vendorCategoryRfp->id, $openRfp->id])
                                ]
                            ];

                            $this->emailNotifier->sendGeneralEmail($content, $recipients);
                        }
                    }
                    else
                    {
                        $recipient = User::find($nextVerifier->user_id);
                        $recipients = [$recipient];

                        $content = [
                            'subject' => "Consultant Management - RFP Resubmission Approval (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.pending_approval',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'creator' => $user->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'moduleName' => 'RFP Resubmission',
                                'route' => route('consultant.management.open.rfp.show', [$vendorCategoryRfp->id, $openRfp->id])
                            ]
                        ];
                    }
                }
                elseif(array_key_exists('reject', $inputs))
                {
                    $status = ConsultantManagementRfpResubmissionVerifierVersion::STATUS_REJECTED;

                    $openRfp->status = ConsultantManagementOpenRfp::STATUS_APPROVED;//back to approved rfp
                    $openRfp->save();

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
                            'subject' => "Consultant Management - RFP Resubmission Rejected (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.rejected',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'creator' => $user->name,
                                'moduleName' => 'RFP Resubmission',
                                'route' => route('consultant.management.open.rfp.show', [$vendorCategoryRfp->id, $openRfp->id])
                                ]
                        ];

                        $this->emailNotifier->sendGeneralEmail($content, $recipients);
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

        if(array_key_exists('reject', $inputs))
        {
            return Redirect::route('consultant.management.open.rfp.resubmission', [$vendorCategoryRfp->id, $openRfp->id]);
        }
        else
        {
            return Redirect::route('consultant.management.open.rfp.show', [$vendorCategoryRfp->id, $openRfp->id]);
        }
    }

    public function resubmissionVerifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $openRfpId)
    {
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$openRfpId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementRfpResubmissionVerifierVersion::select("consultant_management_rfp_resubmission_verifier_versions.id AS id", "users.name AS name",
        "consultant_management_rfp_resubmission_verifier_versions.version", "consultant_management_rfp_resubmission_verifier_versions.status", "consultant_management_rfp_resubmission_verifier_versions.remarks", "consultant_management_rfp_resubmission_verifier_versions.updated_at")
        ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id', '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
        ->join('users', 'users.id', '=', 'consultant_management_rfp_resubmission_verifiers.user_id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
        ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
        ->orderBy('consultant_management_rfp_resubmission_verifier_versions.version', 'desc')
        ->orderBy('consultant_management_rfp_resubmission_verifier_versions.id', 'asc');

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

    public function awardConsultant(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user   = \Confide::user();
        $inputs = Input::all();

        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$inputs['id']);
        $company = Company::findOrFail((int)$inputs['cid']);

        ConsultantManagementConsultantRfp::where('consultant_management_rfp_revision_id', '=', $openRfp->consultant_management_rfp_revision_id)
        ->update(['awarded'=> false]);//reset awarded

        $consultantRfp = ConsultantManagementConsultantRfp::where('consultant_management_rfp_revision_id', '=', $openRfp->consultant_management_rfp_revision_id)
        ->where('company_id', '=', $company->id)
        ->first();

        if($consultantRfp)
        {
            $consultantRfp->awarded = true;
            $consultantRfp->updated_by = $user->id;
            $consultantRfp->updated_at = date('Y-m-d H:i:s');

            $consultantRfp->save();
        }

        ConsultantManagementVendorCategoryRfpAccountCode::where('vendor_category_rfp_id', $vendorCategoryRfp->id)
            ->update([
                'amount'     => 0,
                'updated_by' => $user->id,
            ]);

        return Response::json([
            'success' => 'success'
        ]);
    }
}