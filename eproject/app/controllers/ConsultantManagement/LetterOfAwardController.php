<?php namespace ConsultantManagement;

use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\LetterOfAwardTemplate;
use PCK\ConsultantManagement\LetterOfAward;
use PCK\ConsultantManagement\LetterOfAwardVerifier;
use PCK\ConsultantManagement\LetterOfAwardVerifierVersion;
use PCK\ConsultantManagement\LetterOfAwardSubsidiaryRunningNumber;
use PCK\ConsultantManagement\LetterOfAwardAttachment;
use PCK\Users\User;
use PCK\Companies\Company;
use PCK\Subsidiaries\Subsidiary;

use PCK\Helpers\Files;
use PCK\Helpers\PdfHelper;
use PCK\Notifications\EmailNotifier;
use PCK\ModulePermission\ModulePermission;

use PCK\ExternalApplication\Module\AwardedConsultant;

use PCK\Forms\ConsultantManagement\LetterOfAwardTemplateForm;
use PCK\Forms\ConsultantManagement\LetterOfAwardForm;
use PCK\Forms\ConsultantManagement\LetterOfAwardSubsidiaryRunningNumberForm;
use PCK\Forms\ConsultantManagement\LetterOfAwardVerifierForm;
use PCK\Forms\ConsultantManagement\GeneralVerifyForm;

class LetterOfAwardController extends \BaseController
{
    private $letterOfAwardTemplateForm;
    private $letterOfAwardVerifierForm;
    private $letterOfAwardForm;
    private $letterOfAwardSubsidiaryRuningNumberForm;
    private $generalVerifyForm;
    private $emailNotifier;

    public function __construct(LetterOfAwardTemplateForm $letterOfAwardTemplateForm,
    LetterOfAwardForm $letterOfAwardForm,
    LetterOfAwardSubsidiaryRunningNumberForm $letterOfAwardSubsidiaryRuningNumberForm,
    LetterOfAwardVerifierForm $letterOfAwardVerifierForm,
    GeneralVerifyForm $generalVerifyForm,
    EmailNotifier $emailNotifier)
    {
        $this->letterOfAwardTemplateForm = $letterOfAwardTemplateForm;
        $this->letterOfAwardVerifierForm = $letterOfAwardVerifierForm;
        $this->letterOfAwardForm = $letterOfAwardForm;
        $this->letterOfAwardSubsidiaryRuningNumberForm = $letterOfAwardSubsidiaryRuningNumberForm;
        $this->generalVerifyForm = $generalVerifyForm;
        $this->emailNotifier = $emailNotifier;
    }

    public function index(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user = \Confide::user();
        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        if(!$letterOfAward)
        {
            $letterOfAward = new LetterOfAward;
            $letterOfAward->vendor_category_rfp_id = $vendorCategoryRfp->id;
            $letterOfAward->status = LetterOfAward::STATUS_DRAFT;
            $letterOfAward->created_by = $user->id;
            $letterOfAward->updated_by = $user->id;

            $letterOfAward->save();
        }

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

        $verifiers = $verifiers->merge(ModulePermission::getUserList(ModulePermission::MODULE_ID_TOP_MANAGEMENT_VERIFIERS));

        $selectedVerifiers = LetterOfAwardVerifier::select("consultant_management_letter_of_award_verifiers.user_id AS id")
            ->where('consultant_management_letter_of_award_id', $letterOfAward->id)
            ->orderBy('consultant_management_letter_of_award_verifiers.id', 'asc')
            ->get();

        $letterOfAwardTemplates = LetterOfAwardTemplate::orderBy('title', 'asc')->get();

        $curentClauseNumber = 1;
        $numberingString = '';
        $clauseHtml = '';
        foreach ($letterOfAward->getStructuredClauses() as $clause)
        {
            $clauseHtml.= $this->renderClauses($clause, $curentClauseNumber, $numberingString);
            
            if($clause['displayNumbering'])
            {
                $curentClauseNumber++;
            }
        }

        if($letterOfAward->status == LetterOfAward::STATUS_DRAFT)
        {
            $view = \View::make('consultant_management.letter_of_award.edit', compact('vendorCategoryRfp', 'letterOfAward', 'letterOfAwardTemplates', 'selectedVerifiers', 'verifiers', 'user', 'clauseHtml'));
        }
        else
        {
            $view = \View::make('consultant_management.letter_of_award.index', compact('vendorCategoryRfp', 'letterOfAward', 'user', 'clauseHtml'));
        }

        return $view;
    }

    public function store(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = \Request::instance();

        $this->letterOfAwardForm->validate($request->all());

        $letterOfAward = $vendorCategoryRfp->letterOfAward;
        $user = \Confide::user();

        $letterOfAward->content = trim($request->get('content'));
        $letterOfAward->updated_by = $user->id;

        $letterOfAward->save();

        \Flash::success(trans('forms.saved'));

        return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
    }

    public function contentUpdate(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request       = \Request::instance();
        $template      = LetterOfAwardTemplate::find((int)$request->get('id'));
        $user          = \Confide::user();
        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        if($letterOfAward->status != LetterOfAward::STATUS_DRAFT)
        {
            return \Response::json([
                'success' => false
            ]);
        }

        $letterhead = null;
        $signatory  = null;
        $clauses    = [];

        if($template)
        {
            $letterhead = $template->letterhead;
            $signatory  = $template->signatory;
            $clauses    = $template->getStructuredClauses(false);
        }

        \DB::beginTransaction();
        try
        {
            $letterOfAward->letterhead = $letterhead;
            $letterOfAward->signatory  = $signatory;

            $letterOfAward->clauses()->delete();

            if(!empty($clauses))
            {
                $sequenceNumber = 1;

                foreach($clauses as $clause)
                {
                    $letterOfAward->updateOrCreateClauses($clause, $sequenceNumber++);
                }
            }

            $letterOfAward->save();

            $success = true;
            
            \DB::commit();
        }
        catch(\Exception $e)
        {
            $success = false;
            \DB::rollBack();
        }

        return \Response::json([
            'success' => $success
        ]);
    }

    public function letterheadEdit(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        if($letterOfAward->status != LetterOfAward::STATUS_DRAFT)
        {
            return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
        }

        return \View::make('consultant_management.letter_of_award.section_edit', [
            'type'              => 'letterhead',
            'loa'               => $letterOfAward,
            'store'             => 'consultant.management.loa.letterhead.store',
            'vendorCategoryRfp' => $vendorCategoryRfp
        ]);
    }

    public function letterheadStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = \Request::instance();

        $letterOfAward = $vendorCategoryRfp->letterOfAward;
        $user          = \Confide::user();

        if($letterOfAward->status != LetterOfAward::STATUS_DRAFT)
        {
            return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
        }

        $letterOfAward->letterhead = trim($request->get('letterhead'));
        $letterOfAward->updated_by = $user->id;

        $letterOfAward->save();

        \Flash::success(trans('forms.saved'));

        return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
    }

    public function signatoryEdit(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        if($letterOfAward->status != LetterOfAward::STATUS_DRAFT)
        {
            return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
        }

        return \View::make('consultant_management.letter_of_award.section_edit', [
            'type'              => 'signatory',
            'loa'               => $letterOfAward,
            'store'             => 'consultant.management.loa.signatory.store',
            'vendorCategoryRfp' => $vendorCategoryRfp
        ]);
    }

    public function signatoryStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = \Request::instance();

        $letterOfAward = $vendorCategoryRfp->letterOfAward;
        $user          = \Confide::user();

        if($letterOfAward->status != LetterOfAward::STATUS_DRAFT)
        {
            return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
        }

        $letterOfAward->signatory = trim($request->get('signatory'));
        $letterOfAward->updated_by = $user->id;

        $letterOfAward->save();

        \Flash::success(trans('forms.saved'));

        return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
    }

    public function clauseEdit(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        if($letterOfAward->status != LetterOfAward::STATUS_DRAFT)
        {
            return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
        }

        return \View::make('consultant_management.letter_of_award.section_edit', [
            'type'              => 'clause',
            'loa'               => $letterOfAward,
            'store'             => 'consultant.management.loa.clause.store',
            'clauses'           => json_encode($letterOfAward->getStructuredClauses()),
            'vendorCategoryRfp' => $vendorCategoryRfp
        ]);
    }

    public function clauseStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = \Request::instance();

        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        if($letterOfAward->status != LetterOfAward::STATUS_DRAFT)
        {
            return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
        }

        $inactiveClauses = $request->has('inactiveClauses') ? $request->get('inactiveClauses') : [];
        $clauses         = $request->has('clauses') ?  $request->get('clauses') : [];
        $sequenceNumber  = 1;

        foreach($inactiveClauses as $inactiveClause)
        {
            $isExistingClause = array_key_exists('id', $inactiveClause);

            if( $isExistingClause )
            {
                $letterOfAward->deleteClauses($inactiveClause);
            }
        }

        foreach($clauses as $clause)
        {
            $letterOfAward->updateOrCreateClauses($clause, $sequenceNumber++);
        }

        return \Response::json([
            'url' => route('consultant.management.loa.index', [ $vendorCategoryRfp->id ]),
        ]);
    }

    public function preview(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        return $this->generatePdf($letterOfAward, "Print Preview");
    }

    public function print(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        if($letterOfAward->status != LetterOfAward::STATUS_APPROVED)
        {
            return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
        }

        return $this->generatePdf($letterOfAward);
    }

    public function verifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        $request = \Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = LetterOfAwardVerifierVersion::select("consultant_management_letter_of_award_verifier_versions.id AS id", "users.name AS name",
        "consultant_management_letter_of_award_verifier_versions.version", "consultant_management_letter_of_award_verifier_versions.status",
        "consultant_management_letter_of_award_verifier_versions.remarks", "consultant_management_letter_of_award_verifier_versions.updated_at")
        ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id', '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
        ->join('users', 'users.id', '=', 'consultant_management_letter_of_award_verifiers.user_id')
        ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
        ->where('consultant_management_letter_of_awards.id', '=', $letterOfAward->id)
        ->orderBy('consultant_management_letter_of_award_verifier_versions.version', 'desc')
        ->orderBy('consultant_management_letter_of_award_verifier_versions.id', 'asc');

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

        return \Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function verifierStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = \Request::instance();

        $this->letterOfAwardVerifierForm->validate($request->all());

        $letterOfAward = $vendorCategoryRfp->letterOfAward;
        $user = \Confide::user();

        if($request->has('verifiers') && is_array($request->get('verifiers')))
        {
            $verifierIds = array_unique(array_filter($request->get('verifiers')));
            
            if(!empty($verifierIds))
            {
                LetterOfAwardVerifier::where('consultant_management_letter_of_award_id', $letterOfAward->id)
                ->delete();

                $data = [];
                foreach($verifierIds as $id)
                {
                    $data[] = [
                        'consultant_management_letter_of_award_id' => $letterOfAward->id,
                        'user_id'    => $id,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                LetterOfAwardVerifier::insert($data);
            }
        }

        $recipientId = null;

        if($request->has('send_to_verify'))
        {
            $letterOfAward->status = LetterOfAward::STATUS_APPROVAL;
            
            $letterOfAward->save();

            $latestVersion = LetterOfAwardVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id', '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
            ->where('consultant_management_letter_of_awards.id', '=', $letterOfAward->id)
            ->groupBy('consultant_management_letter_of_awards.id')
            ->first();

            $version = ($latestVersion) ? $latestVersion->version + 1 : 1;

            $verifierIds = LetterOfAwardVerifier::select("consultant_management_letter_of_award_verifiers.id", "consultant_management_letter_of_award_verifiers.user_id")
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
            ->where('consultant_management_letter_of_awards.id', '=', $letterOfAward->id)
            ->orderBy('consultant_management_letter_of_award_verifiers.id', 'asc')
            ->lists('user_id', 'id');

            $data = [];

            $count = 0;
            foreach($verifierIds as $verifierId => $userId)
            {
                $data[] = [
                    'consultant_management_letter_of_award_verifier_id' => $verifierId,
                    'user_id'    => $userId,
                    'version'    => $version,
                    'status'     => LetterOfAwardVerifierVersion::STATUS_PENDING,
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
                LetterOfAwardVerifierVersion::insert($data);
            }
        }

        if($recipientId && $recipient = User::find($recipientId))
        {
            $contract = $vendorCategoryRfp->consultantManagementContract;
            $content = [
                'subject' => "Consultant Management - Letter of Award Verification (".$contract->Subsidiary->name.")",//need to move this to i10n
                'view' => 'consultant_management.email.pending_approval',
                'data' => [
                    'developmentPlanningTitle' => $contract->title,
                    'subsidiaryName' => $contract->Subsidiary->name,
                    'creator' => $user->name,
                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                    'moduleName' => 'Letter of Award',
                    'route' => route('consultant.management.loa.index', [$vendorCategoryRfp->id])
                ]
            ];
            
            $this->emailNotifier->sendGeneralEmail($content, [$recipient]);
        }

        \Flash::success(trans('forms.saved'));

        return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
    }

    public function verify(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = \Request::instance();

        $this->generalVerifyForm->validate($request->all());
        
        $letterOfAward = $vendorCategoryRfp->letterOfAward;
        $contract = $vendorCategoryRfp->consultantManagementContract;
        $user = \Confide::user();

        $latestVersion = LetterOfAwardVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id', '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
            ->where('consultant_management_letter_of_awards.id', '=', $letterOfAward->id)
            ->groupBy('consultant_management_letter_of_awards.id')
            ->first();

        if($latestVersion && $letterOfAward->needApprovalFromUser(\Confide::user()) && ($request->has('approve') or $request->has('reject')))
        {
            $latestVerifierLogId = LetterOfAwardVerifierVersion::select("consultant_management_letter_of_award_verifier_versions.id AS id")
            ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id', '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
            ->where('consultant_management_letter_of_awards.id', '=', $letterOfAward->id)
            ->where('consultant_management_letter_of_award_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_letter_of_award_verifiers.user_id', '=', $user->id)
            ->first();

            if($latestVerifierLogId)
            {
                $latestVerifierLog = LetterOfAwardVerifierVersion::findOrFail($latestVerifierLogId->id);

                $status = LetterOfAwardVerifierVersion::STATUS_PENDING;
                $content = [];
                $recipients = [];

                if($request->has('approve'))
                {
                    $status = LetterOfAwardVerifierVersion::STATUS_APPROVED;

                    $nextVerifier = LetterOfAwardVerifierVersion::select("consultant_management_letter_of_award_verifiers.id AS id", "users.name", "users.email", "users.id AS user_id", "consultant_management_letter_of_award_verifier_versions.status")
                        ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id', '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
                        ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
                        ->join('users', 'consultant_management_letter_of_award_verifiers.user_id', '=', 'users.id')
                        ->where('consultant_management_letter_of_awards.id', '=', $letterOfAward->id)
                        ->where('consultant_management_letter_of_award_verifier_versions.version', '=', $latestVersion->version)
                        ->where('consultant_management_letter_of_award_verifiers.id', '>', $latestVerifierLog->consultant_management_letter_of_award_verifier_id)
                        ->orderBy('consultant_management_letter_of_award_verifiers.id', 'asc')
                        ->first();

                    if(!$nextVerifier)
                    {
                        $letterOfAward->status = LetterOfAward::STATUS_APPROVED;
                        $letterOfAward->updated_by = $user->id;
                        $letterOfAward->save();

                        \Queue::push('PCK\QueueJobs\ExternalOutboundAPI', [
                            'module' => 'AwardedConsultant',
                            'loa_id' => $letterOfAward->id,
                        ], 'ext_app_outbound');

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
                                'subject' => "Consultant Management - Letter of Award Approved (".$contract->Subsidiary->name.")",//need to move this to i10n
                                'view' => 'consultant_management.email.approved',
                                'data' => [
                                    'developmentPlanningTitle' => $contract->title,
                                    'subsidiaryName' => $contract->Subsidiary->name,
                                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                    'moduleName' => 'Letter of Award',
                                    'route' => route('consultant.management.loa.index', [$vendorCategoryRfp->id])
                                ]
                            ];
                        }
                    }
                    else
                    {
                        $recipient = User::find($nextVerifier->user_id);
                        $recipients = [$recipient];

                        $content = [
                            'subject' => "Consultant Management - Letter of Award Verification (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.pending_approval',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'creator' => $user->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'moduleName' => 'Letter of Award',
                                'route' => route('consultant.management.loa.index', [$vendorCategoryRfp->id])
                            ]
                        ];
                    }
                }
                elseif($request->has('reject'))
                {
                    $status = LetterOfAwardVerifierVersion::STATUS_REJECTED;

                    $letterOfAward->status = LetterOfAward::STATUS_DRAFT;
                    $letterOfAward->updated_by = $user->id;
                    $letterOfAward->save();

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
                            'subject' => "Consultant Management - Letter of Award Rejected (".$contract->Subsidiary->name.")",//need to move this to i10n
                            'view' => 'consultant_management.email.rejected',
                            'data' => [
                                'developmentPlanningTitle' => $contract->title,
                                'subsidiaryName' => $contract->Subsidiary->name,
                                'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                                'creator' => $user->name,
                                'moduleName' => 'Letter of Award',
                                'route' => route('consultant.management.loa.index', [$vendorCategoryRfp->id])
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

        return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
    }

    public function templateIndex()
    {
        $user = \Confide::user();

        return \View::make('consultant_management.letter_of_award.template.index', compact('user'));
    }

    public function templateList()
    {
        $user = \Confide::user();

        $request = \Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = LetterOfAwardTemplate::select("consultant_management_letter_of_award_templates.id AS id",
        "consultant_management_letter_of_award_templates.title",
        "consultant_management_letter_of_award_templates.created_at");

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'title':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_letter_of_award_templates.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_letter_of_award_templates.created_at', 'desc');

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
                'title'      => trim($record->title),
                'created_at' => Carbon::parse($record->created_at)->format('d/m/Y'),
                'route:show' => route('consultant.management.loa.templates.show', [$record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return \Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function templateShow($id)
    {
        $user = \Confide::user();
        $template = LetterOfAwardTemplate::findOrFail((int)$id);

        $curentClauseNumber = 1;
        $numberingString = '';
        $clauseHtml = '';
        foreach ($template->getStructuredClauses() as $clause)
        {
            $clauseHtml.= $this->renderClauses($clause, $curentClauseNumber, $numberingString);
            
            if($clause['displayNumbering'])
            {
                $curentClauseNumber++;
            }
        }

        return \View::make('consultant_management.letter_of_award.template.show', compact('template', 'user', 'clauseHtml'));
    }

    public function templateCreate()
    {
        $template = null;

        return \View::make('consultant_management.letter_of_award.template.edit', [
            'type'  => 'template',
            'loa'   => $template,
            'store' => 'consultant.management.loa.templates.store'
        ]);
    }

    public function templateEdit($id)
    {
        $template = LetterOfAwardTemplate::findOrFail((int)$id);

        return \View::make('consultant_management.letter_of_award.template.edit', [
            'type'  => 'template',
            'loa'   => $template,
            'store' => 'consultant.management.loa.templates.store'
        ]);
    }

    public function templateStore()
    {
        $request = \Request::instance();

        $this->letterOfAwardTemplateForm->validate($request->all());

        $user     = \Confide::user();
        $template = LetterOfAwardTemplate::find($request->get('id'));

        if(!$template)
        {
            $template = new LetterOfAwardTemplate();

            $template->created_by = $user->id;
        }

        $template->title      = trim($request->get('title'));
        $template->updated_by = $user->id;

        $template->save();

        \Flash::success(trans('forms.saved'));

        return \Redirect::route('consultant.management.loa.templates.show', [$template->id]);
    }

    public function templateDelete($id)
    {
        $template = LetterOfAwardTemplate::findOrFail((int)$id);
        $user = \Confide::user();

        if($template->deletable())
        {
            $template->delete();

            \Log::info("Delete consultant management LOA Template [template id: {$id}]][user id:{$user->id}]");
        }

        return \Redirect::route('consultant.management.loa.templates.index');
    }

    public function templateLetterheadEdit($id)
    {
        $template = LetterOfAwardTemplate::findOrFail((int)$id);

        return \View::make('consultant_management.letter_of_award.template.edit', [
            'type'  => 'letterhead',
            'loa'   => $template,
            'store' => 'consultant.management.loa.templates.letterhead.store'
        ]);
    }

    public function templateLetterheadStore()
    {
        $request = \Request::instance();

        $template = LetterOfAwardTemplate::findOrFail((int)$request->get('id'));
        $user     = \Confide::user();

        $template->letterhead = trim($request->get('letterhead'));
        $template->updated_by = $user->id;

        $template->save();

        \Flash::success(trans('forms.saved'));

        return \Redirect::route('consultant.management.loa.templates.show', [$template->id]);
    }

    public function templateSignatoryEdit($id)
    {
        $template = LetterOfAwardTemplate::findOrFail((int)$id);

        return \View::make('consultant_management.letter_of_award.template.edit', [
            'type'  => 'signatory',
            'loa'   => $template,
            'store' => 'consultant.management.loa.templates.signatory.store'
        ]);
    }

    public function templateSignatoryStore()
    {
        $request = \Request::instance();

        $template = LetterOfAwardTemplate::findOrFail((int)$request->get('id'));
        $user     = \Confide::user();

        $template->signatory = trim($request->get('signatory'));
        $template->updated_by = $user->id;

        $template->save();

        \Flash::success(trans('forms.saved'));

        return \Redirect::route('consultant.management.loa.templates.show', [$template->id]);
    }

    public function templateClauseEdit($id)
    {
        $template = LetterOfAwardTemplate::findOrFail((int)$id);

        return \View::make('consultant_management.letter_of_award.template.edit', [
            'type'    => 'clause',
            'loa'     => $template,
            'store'   => 'consultant.management.loa.templates.clause.store',
            'clauses' => json_encode($template->getStructuredClauses())
        ]);
    }

    public function templateClauseStore()
    {
        $request = \Request::instance();

        $template = LetterOfAwardTemplate::findOrFail((int)$request->get('id'));

        $inactiveClauses = $request->has('inactiveClauses') ? $request->get('inactiveClauses') : [];
        $clauses         = $request->has('clauses') ?  $request->get('clauses') : [];
        $sequenceNumber  = 1;

        foreach($inactiveClauses as $inactiveClause)
        {
            $isExistingClause = array_key_exists('id', $inactiveClause);

            if( $isExistingClause )
            {
                $template->deleteClauses($inactiveClause);
            }
        }

        foreach($clauses as $clause)
        {
            $template->updateOrCreateClauses($clause, $sequenceNumber++);
        }

        return \Response::json([
            'url' => route('consultant.management.loa.templates.show', [ $template->id ]),
        ]);
    }

    private function renderClauses($clause, $curentClauseNumber, $numberingString)
    {
        $isRootClause = empty($clause['parentId']);
        $hasChildren  = !empty($clause['children']);

        if ($isRootClause)
        {
            $numberingString = $curentClauseNumber;
        }
        else
        {
            $numberingString .= '.' . $curentClauseNumber;
        }

        $html = '<tr>';
        if($clause['displayNumbering'])
        {
            $trailingNumber = $isRootClause ? '.0' : '';
            $classA = $isRootClause ? 'bolded root-clause-spacing' : 'child-clause-spacing';
            $classB = $isRootClause ? 'root-clause-spacing' : 'child-clause-spacing';
            $html .= '<td class="parent-clause-numbering '.$classA.'">'.$numberingString.$trailingNumber.'</td>';
            $html .= '<td class="contents '.$classB.'">'.$clause['content'].'</td>';
        }
        else
        {
            $html .= '<td class="contents no-left-padding" colspan="2">'.$clause['content'].'</td>';
        }
        
        $html .= '</tr>';
        
        if($hasChildren)
        {
            $childClauseNumber = 1;
            
            foreach($clause['children'] as $childClause)
            {
                $html .= $this->renderClauses($childClause, $childClauseNumber, $numberingString);
                
                if($childClause['displayNumbering'])
                {
                    $childClauseNumber++;
                }
            }
        }

        return $html;
    }

    public function templatePreview($id)
    {
        $template = LetterOfAwardTemplate::findOrFail((int)$id);

        return $this->generatePdf($template, "Template Print Preview");
    }

    protected function generatePdf($letterOfAward, $watermark=null)
    {
        $curentClauseNumber = 1;
        $numberingString = '';
        $clauseHtml = '';
        foreach ($letterOfAward->getStructuredClauses() as $clause)
        {
            $clauseHtml.= $this->renderClauses($clause, $curentClauseNumber, $numberingString);
            
            if($clause['displayNumbering'])
            {
                $curentClauseNumber++;
            }
        }

        $data = [
            'signatory'     => $letterOfAward->signatory,
            'clauseHtml'    => $clauseHtml,
            'printSettings' => [
                'clause_font_size' => 15,
            ]
        ];

        $pdfHelper = new PdfHelper('consultant_management.letter_of_award.print.layout', $data);

        $headerView = \View::make('consultant_management.letter_of_award.print.header_layout_style', [
            'fontSize'    => 15,
            'letterhead'  => $letterOfAward->letterhead,
            'referenceNo' => ($letterOfAward instanceof LetterOfAward) ? $letterOfAward->reference_number : null,
            'watermark'   => $watermark
        ]);

        $headerHeightInPixels = 50;
        $marginTop            = 15 + $headerHeightInPixels / 4;
        $marginBottom         = 20;
        $marginLeft           = 15;
        $marginRight          = 15;

        $marginTopOption    = ' --margin-top ' . $marginTop;
        $marginBottomOption = ' --margin-bottom ' . $marginBottom;
        $marginLeftOption   = ' --margin-left ' . $marginLeft;
        $marginRightOption  = ' --margin-right ' . $marginRight;

        $headerSpacing = ($letterOfAward instanceof LetterOfAward) ? 3 : 5;//make space for LOA ref no. Template does not have ref no
        $headerOptions = ' --header-spacing ' . $headerSpacing;

        $footerOptions = ' --footer-font-size 10 --footer-right "Page [page] of [topage]"';

        $headerView = PdfHelper::removeBreaksFromHtml($headerView);

        $pdfHelper->setHeaderHtml($headerView);
        $pdfHelper->setOptions(' --encoding utf-8  --disable-smart-shrinking ' . $marginTopOption . $marginBottomOption . $marginRightOption . $marginLeftOption . $headerOptions . $footerOptions);

        return $pdfHelper->printPDF();
    }

    public function runningNumberIndex()
    {
        $user = \Confide::user();

        return \View::make('consultant_management.letter_of_award.running_number.index', compact('user'));
    }

    public function runningNumberList()
    {
        $user = \Confide::user();

        $request = \Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = LetterOfAwardSubsidiaryRunningNumber::select("subsidiaries.id", "subsidiaries.name", "subsidiaries.identifier",
        "consultant_management_loa_subsidiary_running_numbers.next_running_number", "consultant_management_loa_subsidiary_running_numbers.created_at")
        ->join('subsidiaries', 'consultant_management_loa_subsidiary_running_numbers.subsidiary_id', '=', 'subsidiaries.id');

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('subsidiaries.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'code':
                        if(strlen($val) > 0)
                        {
                            $model->where('subsidiaries.identifier', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('subsidiaries.name', 'asc');

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
                'code'                => trim($record->identifier),
                'next_running_number' => $record->next_running_number,
                'created_at'          => Carbon::parse($record->created_at)->format('d/m/Y'),
                'route:show'          => route('consultant.management.loa.running.number.show', [$record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return \Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function runningNumberShow($id)
    {
        $subsidiaryRunningNumber = LetterOfAwardSubsidiaryRunningNumber::findOrFail((int)$id);

        return \View::make('consultant_management.letter_of_award.running_number.show', compact('subsidiaryRunningNumber'));
    }

    public function runningNumberCreate()
    {
        $subsidiaryRunningNumber = null;

        $subsidiaryIds = LetterOfAwardSubsidiaryRunningNumber::lists('subsidiary_id');

        $rootSubsidiaries = Subsidiary::where(function ($query) {
            $query->whereNull('parent_id')->orWhere('parent_id', '=', \DB::raw('id'));
        });

        if(!empty($subsidiaryIds))
        {
            $rootSubsidiaries->whereNotIn('id', $subsidiaryIds);
        }

        $subsidiaries = $rootSubsidiaries->orderBy('name', 'asc')->lists('name', 'id');

        return \View::make('consultant_management.letter_of_award.running_number.edit', compact('subsidiaryRunningNumber', 'subsidiaries'));
    }

    public function runningNumberEdit($id)
    {
        $subsidiaryRunningNumber = LetterOfAwardSubsidiaryRunningNumber::findOrFail((int)$id);
        $subsidiaries = [];

        return \View::make('consultant_management.letter_of_award.running_number.edit', compact('subsidiaryRunningNumber', 'subsidiaries'));
    }

    public function runningNumberStore()
    {
        $request = \Request::instance();

        $this->letterOfAwardSubsidiaryRuningNumberForm->validate($request->all());

        $user                    = \Confide::user();
        $subsidiaryRunningNumber = LetterOfAwardSubsidiaryRunningNumber::find($request->get('id'));

        if(!$subsidiaryRunningNumber)
        {
            $subsidiaryRunningNumber = new LetterOfAwardSubsidiaryRunningNumber();

            $subsidiaryRunningNumber->subsidiary_id = $request->get('subsidiary_id');
            $subsidiaryRunningNumber->created_by    = $user->id;
        }

        $subsidiaryRunningNumber->next_running_number = $request->get('next_running_number');
        $subsidiaryRunningNumber->updated_by = $user->id;

        $subsidiaryRunningNumber->save();

        \Flash::success(trans('forms.saved'));

        return \Redirect::route('consultant.management.loa.running.number.show', [$subsidiaryRunningNumber->subsidiary_id]);
    }

    public function attachmentStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = \Request::instance();
        
        $user = \Confide::user();

        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        $attachment = LetterOfAwardAttachment::find($request->get('id'));

        if(!$attachment)
        {
            $attachment = new LetterOfAwardAttachment;

            $attachment->consultant_management_letter_of_award_id = $letterOfAward->id;
            $attachment->created_by = $user->id;
        }

        $attachment->title      = trim($request->get('title'));
        $attachment->updated_by = $user->id;
        
        $attachment->save();

        \Flash::success(trans('forms.saved'));

        return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
    }

    public function attachmentList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user = \Confide::user();

        $letterOfAward = $vendorCategoryRfp->letterOfAward;

        $request = \Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = LetterOfAwardAttachment::select("consultant_management_letter_of_award_attachments.id AS id", "consultant_management_letter_of_award_attachments.title",
        "consultant_management_letter_of_award_attachments.consultant_management_letter_of_award_id", "consultant_management_letter_of_award_attachments.attachment_filename")
        ->where('consultant_management_letter_of_award_attachments.consultant_management_letter_of_award_id', '=', $letterOfAward->id)
        ->orderBy('consultant_management_letter_of_award_attachments.created_at', 'desc');

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
                'route:download'      => route('consultant.management.loa.attachment.download', [$vendorCategoryRfp->id, $record->id]),
                'route:delete'        => route('consultant.management.loa.attachment.delete', [$vendorCategoryRfp->id, $record->id]),
            ];
            
            $data[] = $item;
        }

        $totalPages = ceil( $rowCount / $limit );

        return \Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function attachmentInfo(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $id)
    {
        $attachment = LetterOfAwardAttachment::findOrFail((int)$id);

        return \Response::json($attachment->toArray());
    }

    public function attachmentUpload(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = \Request::instance();
        
        $user = \Confide::user();

        $loaAttachment  = LetterOfAwardAttachment::findOrFail((int)$request->get('id'));

        if($request->hasFile('loa_attachment-upload-file'))
        {
            $attachmentFile = $request->file('loa_attachment-upload-file');

            $path = storage_path('consultant_management-loa_attachments'.DIRECTORY_SEPARATOR.$loaAttachment->id);

            Files::mkdirIfDoesNotExist($path);

            $dir = new \DirectoryIterator($path);
            // Deleting all the files in the list
            foreach ($dir as $fileinfo)
            {
                if (!$fileinfo->isDot() && $fileinfo->isFile())
                {
                    unlink($fileinfo->getPathname());
                }
            }

            $attachmentFile->move($path, $attachmentFile->getClientOriginalName());

            $loaAttachment->attachment_filename = $attachmentFile->getClientOriginalName();
            $loaAttachment->updated_by = $user->id;
            
            $loaAttachment->save();

            \Flash::success(trans('forms.saved'));
        }
        
        return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
    }

    public function attachmentDownload(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $id)
    {
        $loaAttachment = LetterOfAwardAttachment::findOrFail((int)$id);

        $path = storage_path('consultant_management-loa_attachments'.DIRECTORY_SEPARATOR.$loaAttachment->id);

        $filepath = $path.DIRECTORY_SEPARATOR.$loaAttachment->attachment_filename;

        return Files::download($filepath, $loaAttachment->attachment_filename);
    }

    public function attachmentDelete(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $id)
    {
        $loaAttachment = LetterOfAwardAttachment::findOrFail((int)$id);

        $user = \Confide::user();

        $path = storage_path('consultant_management-loa_attachments'.DIRECTORY_SEPARATOR.$loaAttachment->id);

        if(!empty($loaAttachment->attachment_filename) && file_exists($path.DIRECTORY_SEPARATOR.$loaAttachment->attachment_filename))
        {
            unlink($path.DIRECTORY_SEPARATOR.$loaAttachment->attachment_filename);
        }

        $loaAttachment->delete();

        \Log::info("Delete consultant management loa attachment [id: {$id}]][user id:{$user->id}]");

        return \Redirect::route('consultant.management.loa.index', [$vendorCategoryRfp->id]);
    }
}