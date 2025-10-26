<?php

use Illuminate\Support\MessageBag;

use Carbon\Carbon;
use PCK\Projects\StatusType;
use PCK\Projects\Project;
use PCK\Projects\ProjectRepository;
use PCK\Tenders\Tender;
use PCK\Verifier\Verifier;
use PCK\Users\User;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformationUser;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformation;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformationUser;
use PCK\TenderCallingTenderInformation\TenderCallingTenderInformation;
use PCK\TenderCallingTenderInformation\TenderCallingTenderOfTendererInformationUser;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use PCK\TendererTechnicalEvaluationInformation\TechnicalEvaluation;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendation;
use PCK\LetterOfAward\LetterOfAward;
use PCK\RequestForInformation\RequestForInformationMessage;
use PCK\RiskRegister\RiskRegisterMessage;
use PCK\Buildspace\PostContractClaim;
use PCK\Buildspace\ContractManagementVerifier;
use PCK\Buildspace\ContractManagementClaimVerifier;
use PCK\Buildspace\NewPostContractFormInformation;
use PCK\Buildspace\ClaimCertificate;
use PCK\RequestForVariation\RequestForVariation;
use PCK\AccountCodeSettings\AccountCodeSetting;
use PCK\SiteManagement\SiteManagementDefectBackchargeDetail;
use PCK\Inspections\Inspection;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultantVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultantVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementOpenRfp;
use PCK\ConsultantManagement\ConsultantManagementOpenRfpVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementRfpResubmissionVerifierVersion;
use PCK\ConsultantManagement\ApprovalDocument;
use PCK\ConsultantManagement\ApprovalDocumentVerifierVersion;
use PCK\ConsultantManagement\LetterOfAward as ConsultantManagementLetterOfAward;
use PCK\ConsultantManagement\LetterOfAwardVerifierVersion;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse;
use PCK\DailyReport\DailyReport;
use PCK\InstructionsToContractors\InstructionsToContractor;

class MyProcessesController extends \BaseController {

    private $projectRepo;

    public function __construct(ProjectRepository $projectRepo)
    {
        $this->projectRepo = $projectRepo;
    }

    public function getProcessesCount()
    {
        $request = Request::instance();

        $request->merge(['getCountOnly' => true]);

        $itemCount = [
            'recommendationOfTenderer'             => $this->getRecommendationOfTendererList(),
            'listOfTenderer'                       => $this->getListOfTendererList(),
            'callingTender'                        => $this->getCallingTenderList(),
            'openTender'                           => $this->getOpenTenderList(),
            'technicalEvaluation'                  => $this->getTechnicalEvaluationList(),
            'technicalAssessment'                  => $this->getTechnicalAssessmentList(),
            'awardRecommendation'                  => $this->getAwardRecommendationList(),
            'letterOfAward'                        => $this->getLetterOfAwardList(),
            'tenderResubmission'                   => $this->getTenderResubmissionList(),
            'requestForInformationMessage'         => $this->getRequestForInformationMessageList(),
            'riskRegisterMessage'                  => $this->getRiskRegisterMessageList(),
            'publishToPostContract'                => $this->getPublishToPostContractList(),
            'waterDeposit'                         => $this->getPostContractClaimList(PostContractClaim::TYPE_WATER_DEPOSIT, null),
            'deposit'                              => $this->getPostContractClaimList(PostContractClaim::TYPE_DEPOSIT, null),
            'outOfContractItems'                   => $this->getPostContractClaimList(PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM, null),
            'purchaseOnBehalf'                     => $this->getPostContractClaimList(PostContractClaim::TYPE_PURCHASE_ON_BEHALF, null),
            'advancedPayment'                      => $this->getPostContractClaimList(PostContractClaim::TYPE_ADVANCED_PAYMENT, null),
            'workOnBehalf'                         => $this->getPostContractClaimList(PostContractClaim::TYPE_WORK_ON_BEHALF, null),
            'workOnBehalfBackCharge'               => $this->getPostContractClaimList(PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE, null),
            'penalty'                              => $this->getPostContractClaimList(PostContractClaim::TYPE_PENALTY, null),
            'permit'                               => $this->getPostContractClaimList(PostContractClaim::TYPE_PERMIT, null),
            'variationOrder'                       => $this->getPostContractClaimList(PostContractClaim::TYPE_VARIATION_ORDER, null),
            'materialOnSite'                       => $this->getPostContractClaimList(PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE, null),
            'claimCertificate'                     => $this->getClaimCertificateList(),
            'requestForVariation'                  => $this->getRequestForVariationList(),
            'accountCodeSetting'                   => $this->getAccountCodeSettingList(),
            'siteManagementDefectBackchargeDetail' => $this->getSiteManagementDefectBackchargeDetailList(),
            'requestForInspection'                 => $this->getRequestForInspectionList(),
            'siteDiary'                            => $this->getSiteManagementSiteDiaryList(),
            'instructionToContractor'              => $this->getInstructionToContractorList(),
            'dailyReport'                          => $this->getDailyReportList(),
            'vendorRegistration'                   => $this->getVendorRegistrationList(),
            'vendorEvaluation'                     => $this->getVendorEvaluationList(),
            'recommendationOfConsultant'           => $this->getRecommendationOfConsultantList(),
            'listOfConsultant'                     => $this->getListOfConsultantList(),
            'callingRfp'                           => $this->getCallingRfpList(),
            'openRfp'                              => $this->getOpenRfpList(),
            'rfpResubmission'                      => $this->getRfpResubmissionList(),
            'approvalDocument'                     => $this->getApprovalDocumentList(),
            'consultantManagementLetterOfAward'    => $this->getConsultantManagementLetterOfAwardList(),
        ];

        $tenderingCount =
            $itemCount['recommendationOfTenderer']+
            $itemCount['listOfTenderer']+
            $itemCount['callingTender']+
            $itemCount['openTender']+
            $itemCount['technicalEvaluation']+
            $itemCount['technicalAssessment']+
            $itemCount['awardRecommendation']+
            $itemCount['letterOfAward']+
            $itemCount['tenderResubmission']+
            $itemCount['requestForInformationMessage']+
            $itemCount['riskRegisterMessage'];

        $postContractCount =
            $itemCount['publishToPostContract']+
            $itemCount['waterDeposit']+
            $itemCount['deposit']+
            $itemCount['outOfContractItems']+
            $itemCount['purchaseOnBehalf']+
            $itemCount['advancedPayment']+
            $itemCount['workOnBehalf']+
            $itemCount['workOnBehalfBackCharge']+
            $itemCount['penalty']+
            $itemCount['permit']+
            $itemCount['variationOrder']+
            $itemCount['materialOnSite']+
            $itemCount['claimCertificate']+
            $itemCount['requestForVariation']+
            $itemCount['accountCodeSetting']+
            $itemCount['siteManagementDefectBackchargeDetail'];

        $siteModuleCount = $itemCount['requestForInspection'] + $itemCount['siteDiary'] + $itemCount['instructionToContractor'] + $itemCount['dailyReport'];

        $vendorManagementCount = $itemCount['vendorRegistration'] + $itemCount['vendorEvaluation'];

        $consultantManagementCount =
            $itemCount['recommendationOfConsultant']+
            $itemCount['listOfConsultant']+
            $itemCount['callingRfp']+
            $itemCount['openRfp']+
            $itemCount['rfpResubmission']+
            $itemCount['approvalDocument']+
            $itemCount['consultantManagementLetterOfAward'];

        $itemCount['all']                  = array_sum($itemCount);
        $itemCount['tendering']            = $tenderingCount;
        $itemCount['postContract']         = $postContractCount;
        $itemCount['siteModule']           = $siteModuleCount;
        $itemCount['vendorManagement']     = $vendorManagementCount;
        $itemCount['consultantManagement'] = $consultantManagementCount;

        return array_filter($itemCount, function($value){return $value > 0;});
    }

    public function getRecommendationOfTendererList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = TenderRecommendationOfTendererInformationUser::join('tender_rot_information', 'tender_rot_information.id', '=', 'tender_rot_information_user.tender_rot_information_id')
            ->where('tender_rot_information_user.user_id', $user->id)
            ->where('tender_rot_information_user.status', '!=', TenderRecommendationOfTendererInformationUser::USER_VERIFICATION_REJECTED)
            ->where('tender_rot_information.status', TenderRecommendationOfTendererInformation::NEED_VALIDATION)
            ->lists('tender_rot_information_user.tender_rot_information_id');

        $submitterRelevantRecordIds = TenderRecommendationOfTendererInformation::where('updated_by', $user->id)
            ->where('status', TenderRecommendationOfTendererInformation::NEED_VALIDATION)
            ->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = TenderRecommendationOfTendererInformation::select(
                'tender_rot_information.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'tenders.id as tender_id',
                'tender_rot_information.updated_at',
                'submitters.name as submitted_by',
                'tender_rot_information_user.user_id as current_verifier_id',
                'first_tender_rot_information_user.user_id as first_verifier_id',
                'users.name as verifier_name',
                'tender_form_verifier_logs.updated_at as latest_verifier_log_updated_at'
            )
            ->join('tenders', 'tender_rot_information.tender_id' , '=', 'tenders.id')
            ->join('projects', 'tenders.project_id', '=', 'projects.id')
            ->leftJoin(\DB::raw(
                "(select tender_rot_information_id, min(id) as current_verifier_record_id
                from tender_rot_information_user
                where status = ".TenderRecommendationOfTendererInformationUser::USER_VERIFICATION_IN_PROGRESS."
                group by tender_rot_information_id) current_tender_rot_information_user_ids"
            ), 'current_tender_rot_information_user_ids.tender_rot_information_id', '=', 'tender_rot_information.id')
            ->leftJoin('tender_rot_information_user', 'tender_rot_information_user.id' , '=', 'current_tender_rot_information_user_ids.current_verifier_record_id')
            ->leftJoin('users', 'users.id' , '=', 'tender_rot_information_user.user_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'tender_rot_information.updated_by')
            ->leftJoin(\DB::raw(
                "(select tender_rot_information_id, min(id) as first_verifier_record_id
                from tender_rot_information_user
                where status != ".TenderRecommendationOfTendererInformationUser::USER_VERIFICATION_REJECTED."
                group by tender_rot_information_id) first_verifier_record"
            ), 'first_verifier_record.tender_rot_information_id', '=', 'tender_rot_information.id')
            ->leftJoin('tender_rot_information_user as first_tender_rot_information_user', 'first_tender_rot_information_user.id' , '=', 'first_verifier_record.first_verifier_record_id')
            ->leftJoin(\DB::raw(
                "(select loggable_id, max(id) as record_id
                from tender_form_verifier_logs
                where loggable_type = '".TenderRecommendationOfTendererInformation::class."'
                group by loggable_type, loggable_id) verifier_logs"
            ), 'verifier_logs.loggable_id', '=', 'tender_rot_information.id')
            ->leftJoin('tender_form_verifier_logs', 'tender_form_verifier_logs.id' , '=', 'verifier_logs.record_id')
            ->whereIn('tender_rot_information.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tender_rot_information.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        $isTopManagementVerifier = $user->isTopManagementVerifier();

        $projects = Project::whereIn('id', $records->lists('project_id'))->get();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->latest_verifier_log_updated_at;

            $project = $projects->find($record->project_id);

            $viewRoute = route('projects.tender.show', array($project->id, $record->tender_id)) . '#s1';

            if(is_null($user->getAssignedCompany($project)) && $isTopManagementVerifier) $viewRoute = route('topManagementVerifiers.projects.tender.show', array($project->id, $record->tender_id)) . '#s1';

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => $viewRoute,
                'route:verifiers'      => route('home.myProcesses.recommendationOfTenderer.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRecommendationOfTendererVerifierList($tenderRecommendationOfTendererInformationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = TenderRecommendationOfTendererInformationUser::select(
                'tender_rot_information_user.id',
                'users.name',
                'tender_rot_information_user.status',
                'tender_rot_information_user.updated_at'
            )
            ->join('users', 'users.id' , '=', 'tender_rot_information_user.user_id')
            ->where('tender_rot_information_user.tender_rot_information_id', $tenderRecommendationOfTendererInformationId)
            ->where('tender_rot_information_user.status', '!=', TenderRecommendationOfTendererInformationUser::USER_VERIFICATION_REJECTED);

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tender_rot_information_user.id', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = ($record->status == TenderRecommendationOfTendererInformationUser::USER_VERIFICATION_CONFIRMED) ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getListOfTendererList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = TenderListOfTendererInformationUser::join('tender_lot_information', 'tender_lot_information.id', '=', 'tender_lot_information_user.tender_lot_information_id')
            ->where('tender_lot_information_user.user_id', $user->id)
            ->where('tender_lot_information_user.status', '!=', TenderListOfTendererInformationUser::USER_VERIFICATION_REJECTED)
            ->where('tender_lot_information.status', TenderListOfTendererInformation::NEED_VALIDATION)
            ->lists('tender_lot_information_user.tender_lot_information_id');

        $submitterRelevantRecordIds = TenderListOfTendererInformation::where('updated_by', $user->id)
            ->where('status', TenderListOfTendererInformation::NEED_VALIDATION)
            ->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = TenderListOfTendererInformation::select(
                'tender_lot_information.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'tenders.id as tender_id',
                'tender_lot_information.updated_at',
                'submitters.name as submitted_by',
                'tender_lot_information_user.user_id as current_verifier_id',
                'first_tender_lot_information_user.user_id as first_verifier_id',
                'users.name as verifier_name',
                'tender_form_verifier_logs.updated_at as latest_verifier_log_updated_at'
            )
            ->join('tenders', 'tender_lot_information.tender_id' , '=', 'tenders.id')
            ->join('projects', 'tenders.project_id', '=', 'projects.id')
            ->leftJoin(\DB::raw(
                "(select tender_lot_information_id, min(id) as current_verifier_record_id
                from tender_lot_information_user
                where status = ".TenderListOfTendererInformationUser::USER_VERIFICATION_IN_PROGRESS."
                group by tender_lot_information_id) current_tender_lot_information_user_ids"
            ), 'current_tender_lot_information_user_ids.tender_lot_information_id', '=', 'tender_lot_information.id')
            ->leftJoin('tender_lot_information_user', 'tender_lot_information_user.id' , '=', 'current_tender_lot_information_user_ids.current_verifier_record_id')
            ->leftJoin('users', 'users.id' , '=', 'tender_lot_information_user.user_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'tender_lot_information.updated_by')
            ->leftJoin(\DB::raw(
                "(select tender_lot_information_id, min(id) as first_verifier_record_id
                from tender_lot_information_user
                where status != ".TenderListOfTendererInformationUser::USER_VERIFICATION_REJECTED."
                group by tender_lot_information_id) first_verifier_record"
            ), 'first_verifier_record.tender_lot_information_id', '=', 'tender_lot_information.id')
            ->leftJoin('tender_lot_information_user as first_tender_lot_information_user', 'first_tender_lot_information_user.id' , '=', 'first_verifier_record.first_verifier_record_id')
            ->leftJoin(\DB::raw(
                "(select loggable_id, max(id) as record_id
                from tender_form_verifier_logs
                where loggable_type = '".TenderListOfTendererInformation::class."'
                group by loggable_type, loggable_id) verifier_logs"
            ), 'verifier_logs.loggable_id', '=', 'tender_lot_information.id')
            ->leftJoin('tender_form_verifier_logs', 'tender_form_verifier_logs.id' , '=', 'verifier_logs.record_id')
            ->whereIn('tender_lot_information.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tender_lot_information.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        $isTopManagementVerifier = $user->isTopManagementVerifier();

        $projects = Project::whereIn('id', $records->lists('project_id'))->get();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->latest_verifier_log_updated_at;

            $project = $projects->find($record->project_id);

            $viewRoute = route('projects.tender.show', array($project->id, $record->tender_id)) . '#s2';

            if(is_null($user->getAssignedCompany($project)) && $isTopManagementVerifier) $viewRoute = route('topManagementVerifiers.projects.tender.show', array($project->id, $record->tender_id)) . '#s2';

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => $viewRoute,
                'route:verifiers'      => route('home.myProcesses.listOfTenderer.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getListOfTendererVerifierList($tenderListOfTendererInformationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = TenderListOfTendererInformationUser::select(
                'tender_lot_information_user.id',
                'users.name',
                'tender_lot_information_user.status',
                'tender_lot_information_user.updated_at'
            )
            ->join('users', 'users.id' , '=', 'tender_lot_information_user.user_id')
            ->where('tender_lot_information_user.tender_lot_information_id', $tenderListOfTendererInformationId)
            ->where('tender_lot_information_user.status', '!=', TenderListOfTendererInformationUser::USER_VERIFICATION_REJECTED);

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tender_lot_information_user.id', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = ($record->status == TenderListOfTendererInformationUser::USER_VERIFICATION_CONFIRMED) ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getCallingTenderList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = TenderCallingTenderOfTendererInformationUser::join('tender_calling_tender_information', 'tender_calling_tender_information.id', '=', 'tender_calling_tender_information_user.tender_calling_tender_information_id')
            ->where('tender_calling_tender_information_user.user_id', $user->id)
            ->where('tender_calling_tender_information_user.status', '!=', TenderCallingTenderOfTendererInformationUser::USER_VERIFICATION_REJECTED)
            ->whereIn('tender_calling_tender_information.status', [TenderCallingTenderInformation::NEED_VALIDATION, TenderCallingTenderInformation::EXTEND_DATE_VALIDATION_IN_PROGRESS])
            ->lists('tender_calling_tender_information_user.tender_calling_tender_information_id');

        $submitterRelevantRecordIds = TenderCallingTenderInformation::where('updated_by', $user->id)
            ->whereIn('status', [TenderCallingTenderInformation::NEED_VALIDATION, TenderCallingTenderInformation::EXTEND_DATE_VALIDATION_IN_PROGRESS])
            ->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = TenderCallingTenderInformation::select(
                'tender_calling_tender_information.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'tenders.id as tender_id',
                'tender_calling_tender_information.updated_at',
                'submitters.name as submitted_by',
                'tender_calling_tender_information_user.user_id as current_verifier_id',
                'first_tender_calling_tender_information_user.user_id as first_verifier_id',
                'users.name as verifier_name',
                'tender_form_verifier_logs.updated_at as latest_verifier_log_updated_at'
            )
            ->join('tenders', 'tender_calling_tender_information.tender_id' , '=', 'tenders.id')
            ->join('projects', 'tenders.project_id', '=', 'projects.id')
            ->leftJoin(\DB::raw(
                "(select tender_calling_tender_information_id, min(id) as current_verifier_record_id
                from tender_calling_tender_information_user
                where status = ".TenderCallingTenderOfTendererInformationUser::USER_VERIFICATION_IN_PROGRESS."
                group by tender_calling_tender_information_id) current_tender_calling_tender_information_user_ids"
            ), 'current_tender_calling_tender_information_user_ids.tender_calling_tender_information_id', '=', 'tender_calling_tender_information.id')
            ->leftJoin('tender_calling_tender_information_user', 'tender_calling_tender_information_user.id' , '=', 'current_tender_calling_tender_information_user_ids.current_verifier_record_id')
            ->leftJoin('users', 'users.id' , '=', 'tender_calling_tender_information_user.user_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'tender_calling_tender_information.updated_by')
            ->leftJoin(\DB::raw(
                "(select tender_calling_tender_information_id, min(id) as first_verifier_record_id
                from tender_calling_tender_information_user
                where status != ".TenderCallingTenderOfTendererInformationUser::USER_VERIFICATION_REJECTED."
                group by tender_calling_tender_information_id) first_verifier_record"
            ), 'first_verifier_record.tender_calling_tender_information_id', '=', 'tender_calling_tender_information.id')
            ->leftJoin('tender_calling_tender_information_user as first_tender_calling_tender_information_user', 'first_tender_calling_tender_information_user.id' , '=', 'first_verifier_record.first_verifier_record_id')
            ->leftJoin(\DB::raw(
                "(select loggable_id, max(id) as record_id
                from tender_form_verifier_logs
                where loggable_type = '".TenderCallingTenderInformation::class."'
                group by loggable_type, loggable_id) verifier_logs"
            ), 'verifier_logs.loggable_id', '=', 'tender_calling_tender_information.id')
            ->leftJoin('tender_form_verifier_logs', 'tender_form_verifier_logs.id' , '=', 'verifier_logs.record_id')
            ->whereIn('tender_calling_tender_information.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tender_calling_tender_information.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        $isTopManagementVerifier = $user->isTopManagementVerifier();

        $projects = Project::whereIn('id', $records->lists('project_id'))->get();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->latest_verifier_log_updated_at;

            $project = $projects->find($record->project_id);

            $viewRoute = route('projects.tender.show', array($project->id, $record->tender_id)) . '#s3';

            if(is_null($user->getAssignedCompany($project)) && $isTopManagementVerifier) $viewRoute = route('topManagementVerifiers.projects.tender.show', array($project->id, $record->tender_id)) . '#s2';

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => $viewRoute,
                'route:verifiers'      => route('home.myProcesses.callingTender.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getCallingTenderVerifierList($tenderCallingTenderInformationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = TenderCallingTenderOfTendererInformationUser::select(
                'tender_calling_tender_information_user.id',
                'users.name',
                'tender_calling_tender_information_user.status',
                'tender_calling_tender_information_user.updated_at'
            )
            ->join('users', 'users.id' , '=', 'tender_calling_tender_information_user.user_id')
            ->where('tender_calling_tender_information_user.tender_calling_tender_information_id', $tenderCallingTenderInformationId)
            ->where('tender_calling_tender_information_user.status', '!=', TenderCallingTenderOfTendererInformationUser::USER_VERIFICATION_REJECTED);

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tender_calling_tender_information_user.id', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = ($record->status == TenderCallingTenderOfTendererInformationUser::USER_VERIFICATION_CONFIRMED) ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getOpenTenderList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = \DB::table('tender_user_verifier_open_tender')
            ->select('tender_id')
            ->join('tenders', 'tenders.id', '=', 'tender_user_verifier_open_tender.tender_id')
            ->where('tenders.open_tender_verification_status', Tender::NEED_VALIDATION)
            ->where('tender_user_verifier_open_tender.user_id', '=', $user->id)
            ->where('tender_user_verifier_open_tender.status', '=', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
            ->lists('tender_user_verifier_open_tender.tender_id');

        $submitterRelevantRecordIds = Tender::where('updated_by', $user->id)
            ->where('open_tender_verification_status', Tender::NEED_VALIDATION)
            ->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = Tender::select(
                'tenders.id as id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'tenders.updated_at',
                'submitters.name as submitted_by',
                \DB::raw('coalesce(total_verifiers.count, 0) as total_verifiers'),
                \DB::raw('coalesce(completed_verifiers.count, 0) as completed_verifiers')
            )
            ->with('project')
            ->join('projects', 'tenders.project_id', '=', 'projects.id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'tenders.updated_by')
            ->leftJoin(\DB::raw(
                "(select count(*), tender_id
                from tender_user_verifier_open_tender
                group by tender_id) total_verifiers"
            ), 'total_verifiers.tender_id', '=', 'tenders.id')
            ->leftJoin(\DB::raw(
                "(select count(*), tender_id
                from tender_user_verifier_open_tender
                where status != ".FormLevelStatus::USER_VERIFICATION_IN_PROGRESS."
                group by tender_id) completed_verifiers"
            ), 'completed_verifiers.tender_id', '=', 'tenders.id')
            ->whereIn('tenders.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tenders.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $project = $record->project;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'total_verifiers'      => $record->total_verifiers,
                'completed_verifiers'  => $record->completed_verifiers,
                'route:view'           => route('projects.openTender.accessToVerifierDecisionForm', array($record->project_id, 'tenderId' => $record->id)),
                'route:verifiers'      => route('home.myProcesses.openTender.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getOpenTenderVerifierList($tenderId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = \DB::table('tender_user_verifier_open_tender')
            ->select(
                'tender_user_verifier_open_tender.id',
                'tender_user_verifier_open_tender.status',
                'users.name',
                'tender_user_verifier_open_tender.updated_at as verified_at'
            )
            ->leftJoin('users', 'users.id' , '=', 'tender_user_verifier_open_tender.user_id')
            ->where('tender_user_verifier_open_tender.tender_id', '=', $tenderId);

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tender_user_verifier_open_tender.status', 'desc');

        $rowCount = count($model->get());

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = ($record->status == FormLevelStatus::USER_VERIFICATION_CONFIRMED) ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getTechnicalEvaluationList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = \DB::table('tender_user_technical_evaluation_verifier')
            ->select('tender_id')
            ->join('tenders', 'tenders.id', '=', 'tender_user_technical_evaluation_verifier.tender_id')
            ->where('tenders.technical_evaluation_verification_status', FormLevelStatus::NEED_VALIDATION)
            ->where('tender_user_technical_evaluation_verifier.user_id', '=', $user->id)
            ->where('tender_user_technical_evaluation_verifier.status', '=', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
            ->lists('tender_user_technical_evaluation_verifier.tender_id');

        $submitterRelevantRecordIds = Tender::where('updated_by', $user->id)
            ->where('technical_evaluation_verification_status', FormLevelStatus::NEED_VALIDATION)
            ->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = Tender::select(
                'tenders.id as id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'tenders.updated_at',
                'submitters.name as submitted_by',
                \DB::raw('coalesce(total_verifiers.count, 0) as total_verifiers'),
                \DB::raw('coalesce(completed_verifiers.count, 0) as completed_verifiers')
            )
            ->with('project')
            ->join('projects', 'tenders.project_id', '=', 'projects.id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'tenders.updated_by')
            ->leftJoin(\DB::raw(
                "(select count(*), tender_id
                from tender_user_technical_evaluation_verifier
                group by tender_id) total_verifiers"
            ), 'total_verifiers.tender_id', '=', 'tenders.id')
            ->leftJoin(\DB::raw(
                "(select count(*), tender_id
                from tender_user_technical_evaluation_verifier
                where status != ".FormLevelStatus::USER_VERIFICATION_IN_PROGRESS."
                group by tender_id) completed_verifiers"
            ), 'completed_verifiers.tender_id', '=', 'tenders.id')
            ->whereIn('tenders.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tenders.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $project = $record->project;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'total_verifiers'      => $record->total_verifiers,
                'completed_verifiers'  => $record->completed_verifiers,
                'route:view'           => route('projects.technicalEvaluation.accessToVerifierDecisionForm', array($record->project_id, $record->id)),
                'route:verifiers'      => route('home.myProcesses.technicalEvaluation.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getTechnicalEvaluationVerifierList($tenderId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = \DB::table('tender_user_technical_evaluation_verifier')
            ->select(
                'tender_user_technical_evaluation_verifier.id',
                'tender_user_technical_evaluation_verifier.status',
                'users.name',
                'tender_user_technical_evaluation_verifier.updated_at as verified_at'
            )
            ->leftJoin('users', 'users.id' , '=', 'tender_user_technical_evaluation_verifier.user_id')
            ->where('tender_user_technical_evaluation_verifier.tender_id', '=', $tenderId);

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tender_user_technical_evaluation_verifier.status', 'desc');

        $rowCount = count($model->get());

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = ($record->status == FormLevelStatus::USER_VERIFICATION_CONFIRMED) ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getTechnicalAssessmentList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('technical_evaluations.id')
            ->join('technical_evaluations', function($join){
                $join->on('verifiers.object_id', '=', 'technical_evaluations.id');
                $join->on(\DB::raw('verifiers.object_type = \''.TechnicalEvaluation::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', TechnicalEvaluation::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = technical_evaluations.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', TechnicalEvaluation::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = technical_evaluations.id');
            })
            ->lists('technical_evaluations.id');

        $submitterRelevantRecordIds = TechnicalEvaluation::select('technical_evaluations.id')
            ->where('submitted_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', TechnicalEvaluation::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = technical_evaluations.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', TechnicalEvaluation::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = technical_evaluations.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = TechnicalEvaluation::select(
                'technical_evaluations.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'tenders.id as tender_id',
                'technical_evaluations.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('tenders', 'technical_evaluations.tender_id' , '=', 'tenders.id')
            ->join('projects', 'tenders.project_id', '=', 'projects.id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'technical_evaluations.submitted_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'technical_evaluations.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.TechnicalEvaluation::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'technical_evaluations.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.TechnicalEvaluation::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'technical_evaluations.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.TechnicalEvaluation::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'technical_evaluations.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.TechnicalEvaluation::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'technical_evaluations.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.TechnicalEvaluation::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'technical_evaluations.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.TechnicalEvaluation::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('technical_evaluations.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('technical_evaluations.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('technicalEvaluation.assessment.confirm', array($record->project_id, $record->tender_id)),
                'route:verifiers'      => route('home.myProcesses.technicalAssessment.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getTechnicalAssessmentVerifierList($technicalEvaluationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $technicalEvaluationId)
            ->where('verifiers.object_type', TechnicalEvaluation::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getAwardRecommendationList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('open_tender_award_recommendation.id')
            ->join('open_tender_award_recommendation', function($join){
                $join->on('verifiers.object_id', '=', 'open_tender_award_recommendation.id');
                $join->on(\DB::raw('verifiers.object_type = \''.OpenTenderAwardRecommendation::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', OpenTenderAwardRecommendation::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = open_tender_award_recommendation.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', OpenTenderAwardRecommendation::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = open_tender_award_recommendation.id');
            })
            ->lists('open_tender_award_recommendation.id');

        $submitterRelevantRecordIds = OpenTenderAwardRecommendation::select('open_tender_award_recommendation.id')
            ->where('submitted_for_verification_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', OpenTenderAwardRecommendation::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = open_tender_award_recommendation.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', OpenTenderAwardRecommendation::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = open_tender_award_recommendation.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = OpenTenderAwardRecommendation::select(
                'open_tender_award_recommendation.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'tenders.id as tender_id',
                'open_tender_award_recommendation.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('tenders', 'open_tender_award_recommendation.tender_id' , '=', 'tenders.id')
            ->join('projects', 'tenders.project_id', '=', 'projects.id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'open_tender_award_recommendation.submitted_for_verification_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'open_tender_award_recommendation.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.OpenTenderAwardRecommendation::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'open_tender_award_recommendation.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.OpenTenderAwardRecommendation::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'open_tender_award_recommendation.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.OpenTenderAwardRecommendation::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'open_tender_award_recommendation.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.OpenTenderAwardRecommendation::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'open_tender_award_recommendation.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.OpenTenderAwardRecommendation::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'open_tender_award_recommendation.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.OpenTenderAwardRecommendation::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('open_tender_award_recommendation.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('open_tender_award_recommendation.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        $isTopManagementVerifier = $user->isTopManagementVerifier();

        $projects = Project::whereIn('id', $records->lists('project_id'))->get();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $project = $projects->find($record->project_id);

            $viewRoute = route('open_tender.award_recommendation.report.show', array($project->id, $record->tender_id));

            if(is_null($user->getAssignedCompany($project)) && $isTopManagementVerifier) $viewRoute = route('topManagementVerifiers.open_tender.award_recommendation.report.show', array($project->id, $record->tender_id));

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => $viewRoute,
                'route:verifiers'      => route('home.myProcesses.awardRecommendation.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getAwardRecommendationVerifierList($openTenderAwardRecommendationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $openTenderAwardRecommendationId)
            ->where('verifiers.object_type', OpenTenderAwardRecommendation::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getLetterOfAwardList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('letter_of_awards.id')
            ->join('letter_of_awards', function($join){
                $join->on('verifiers.object_id', '=', 'letter_of_awards.id');
                $join->on(\DB::raw('verifiers.object_type = \''.LetterOfAward::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', LetterOfAward::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = letter_of_awards.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', LetterOfAward::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = letter_of_awards.id');
            })
            ->lists('letter_of_awards.id');

        $submitterRelevantRecordIds = LetterOfAward::select('letter_of_awards.id')
            ->where('submitted_for_approval_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', LetterOfAward::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = letter_of_awards.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', LetterOfAward::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = letter_of_awards.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = LetterOfAward::select(
                'letter_of_awards.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'letter_of_awards.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('projects', 'letter_of_awards.project_id', '=', 'projects.id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'letter_of_awards.submitted_for_approval_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'letter_of_awards.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.LetterOfAward::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'letter_of_awards.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.LetterOfAward::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'letter_of_awards.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.LetterOfAward::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'letter_of_awards.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.LetterOfAward::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'letter_of_awards.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.LetterOfAward::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'letter_of_awards.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.LetterOfAward::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('letter_of_awards.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('letter_of_awards.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('letterOfAward.index', array($record->project_id)),
                'route:verifiers'      => route('home.myProcesses.letterOfAward.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getLetterOfAwardVerifierList($letterOfAwardId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $letterOfAwardId)
            ->where('verifiers.object_type', LetterOfAward::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getTenderResubmissionList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Tender::select('tenders.id')
            ->join('tender_user_verifier_retender', 'tender_user_verifier_retender.tender_id', '=', 'tenders.id')
            ->where('tender_user_verifier_retender.user_id', $user->id)
            ->where('tender_user_verifier_retender.status', '=', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
            ->where('tenders.retender_verification_status', Tender::NEED_VALIDATION)
            ->lists('tenders.id');

        $submitterRelevantRecordIds = Tender::where('request_retender_by', $user->id)
            ->where('retender_verification_status', Tender::NEED_VALIDATION)
            ->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = Tender::select(
                'tenders.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'tenders.id as tender_id',
                'tenders.request_retender_at as updated_at',
                'submitters.name as submitted_by',
                'tender_user_verifier_retender.user_id as current_verifier_id',
                'first_tender_user_verifier_retender.user_id as first_verifier_id',
                'users.name as verifier_name',
                'tender_form_verifier_logs.updated_at as latest_verifier_log_updated_at'
            )
            ->join('projects', 'tenders.project_id', '=', 'projects.id')
            ->leftJoin(\DB::raw(
                "(select tender_id, min(id) as current_verifier_record_id
                from tender_user_verifier_retender
                where status = ".FormLevelStatus::USER_VERIFICATION_IN_PROGRESS."
                group by tender_id) current_tender_user_verifier_retender_ids"
            ), 'current_tender_user_verifier_retender_ids.tender_id', '=', 'tenders.id')
            ->leftJoin('tender_user_verifier_retender', 'tender_user_verifier_retender.id' , '=', 'current_tender_user_verifier_retender_ids.current_verifier_record_id')
            ->leftJoin('users', 'users.id' , '=', 'tender_user_verifier_retender.user_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'tenders.request_retender_by')
            ->leftJoin(\DB::raw(
                "(select tender_id, min(id) as first_verifier_record_id
                from tender_user_verifier_retender
                where status != ".FormLevelStatus::USER_VERIFICATION_REJECTED."
                group by tender_id) first_verifier_record"
            ), 'first_verifier_record.tender_id', '=', 'tenders.id')
            ->leftJoin('tender_user_verifier_retender as first_tender_user_verifier_retender', 'first_tender_user_verifier_retender.id' , '=', 'first_verifier_record.first_verifier_record_id')
            ->leftJoin(\DB::raw(
                "(select loggable_id, max(id) as record_id
                from tender_form_verifier_logs
                where loggable_type = '".Tender::class."'
                group by loggable_type, loggable_id) verifier_logs"
            ), 'verifier_logs.loggable_id', '=', 'tenders.id')
            ->leftJoin('tender_form_verifier_logs', 'tender_form_verifier_logs.id' , '=', 'verifier_logs.record_id')
            ->whereIn('tenders.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tenders.request_retender_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->latest_verifier_log_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('projects.openTender.reTender', array($record->project_id, $record->tender_id)),
                'route:verifiers'      => route('home.myProcesses.tenderResubmission.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getTenderResubmissionVerifierList($tenderId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = \DB::table('tender_user_verifier_retender')
            ->select(
                'tender_user_verifier_retender.id',
                'users.name',
                'tender_user_verifier_retender.status',
                'tender_user_verifier_retender.updated_at'
            )
            ->join('users', 'users.id' , '=', 'tender_user_verifier_retender.user_id')
            ->where('tender_user_verifier_retender.tender_id', $tenderId)
            ->where('tender_user_verifier_retender.status', '!=', FormLevelStatus::USER_VERIFICATION_REJECTED);

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('tender_user_verifier_retender.id', 'asc');

        $rowCount = count($model->get());

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = ($record->status == FormLevelStatus::USER_VERIFICATION_CONFIRMED) ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRequestForInformationMessageList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('request_for_information_messages.id')
            ->join('request_for_information_messages', function($join){
                $join->on('verifiers.object_id', '=', 'request_for_information_messages.id');
                $join->on(\DB::raw('verifiers.object_type = \''.RequestForInformationMessage::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RequestForInformationMessage::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = request_for_information_messages.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RequestForInformationMessage::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = request_for_information_messages.id');
            })
            ->lists('request_for_information_messages.id');

        $submitterRelevantRecordIds = RequestForInformationMessage::select('request_for_information_messages.id')
            ->where('composed_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RequestForInformationMessage::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = request_for_information_messages.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RequestForInformationMessage::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = request_for_information_messages.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = RequestForInformationMessage::select(
                'request_for_information_messages.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'request_for_information_messages.document_control_object_id',
                'request_for_information_messages.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('document_control_objects', 'document_control_objects.id', '=', 'request_for_information_messages.document_control_object_id')
            ->join('projects', 'projects.id', '=', 'document_control_objects.project_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'request_for_information_messages.composed_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'request_for_information_messages.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.RequestForInformationMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'request_for_information_messages.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.RequestForInformationMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'request_for_information_messages.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.RequestForInformationMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'request_for_information_messages.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.RequestForInformationMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'request_for_information_messages.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.RequestForInformationMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'request_for_information_messages.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.RequestForInformationMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('request_for_information_messages.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('request_for_information_messages.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('requestForInformation.show', array($record->project_id, $record->document_control_object_id)),
                'route:verifiers'      => route('home.myProcesses.requestForInformationMessage.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRequestForInformationMessageVerifierList($requestForInformationMessage)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $requestForInformationMessage)
            ->where('verifiers.object_type', RequestForInformationMessage::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRiskRegisterMessageList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('risk_register_messages.id')
            ->join('risk_register_messages', function($join){
                $join->on('verifiers.object_id', '=', 'risk_register_messages.id');
                $join->on(\DB::raw('verifiers.object_type = \''.RiskRegisterMessage::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RiskRegisterMessage::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = risk_register_messages.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RiskRegisterMessage::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = risk_register_messages.id');
            })
            ->lists('risk_register_messages.id');

        $submitterRelevantRecordIds = RiskRegisterMessage::select('risk_register_messages.id')
            ->where('composed_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RiskRegisterMessage::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = risk_register_messages.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RiskRegisterMessage::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = risk_register_messages.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = RiskRegisterMessage::select(
                'risk_register_messages.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'risk_register_messages.document_control_object_id',
                'risk_register_messages.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('document_control_objects', 'document_control_objects.id', '=', 'risk_register_messages.document_control_object_id')
            ->join('projects', 'projects.id', '=', 'document_control_objects.project_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'risk_register_messages.composed_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'risk_register_messages.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.RiskRegisterMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'risk_register_messages.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.RiskRegisterMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'risk_register_messages.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.RiskRegisterMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'risk_register_messages.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.RiskRegisterMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'risk_register_messages.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.RiskRegisterMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'risk_register_messages.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.RiskRegisterMessage::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('risk_register_messages.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('risk_register_messages.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('riskRegister.show', array($record->project_id, $record->document_control_object_id)),
                'route:verifiers'      => route('home.myProcesses.riskRegisterMessage.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRiskRegisterMessageVerifierList($riskRegisterMessageId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $riskRegisterMessageId)
            ->where('verifiers.object_type', RiskRegisterMessage::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getPublishToPostContractList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $bsUser = $user->getBsUser();

        $verifierRelevantRecordIds = ContractManagementVerifier::select('bs_contract_management_verifiers.project_structure_id')
            ->join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_contract_management_verifiers.project_structure_id')
            ->whereNull('bs_project_structures.deleted_at')
            ->where('bs_contract_management_verifiers.user_id', $bsUser->id)
            ->where('bs_contract_management_verifiers.module_identifier', PostContractClaim::TYPE_LETTER_OF_AWARD)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_verifiers as contract_management_verifiers_1')
                    ->where('module_identifier', '=', PostContractClaim::TYPE_LETTER_OF_AWARD)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('contract_management_verifiers_1.project_structure_id = bs_contract_management_verifiers.project_structure_id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_verifiers as contract_management_verifiers_2')
                    ->where('module_identifier', '=', PostContractClaim::TYPE_LETTER_OF_AWARD)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('contract_management_verifiers_2.project_structure_id = bs_contract_management_verifiers.project_structure_id');
            })
            ->lists('bs_contract_management_verifiers.project_structure_id');

        $submitterRelevantRecordIds = ContractManagementVerifier::select('bs_contract_management_verifiers.project_structure_id')
            ->join('bs_new_post_contract_form_information', 'bs_new_post_contract_form_information.project_structure_id', '=', 'bs_contract_management_verifiers.project_structure_id')
            ->join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_contract_management_verifiers.project_structure_id')
            ->whereNull('bs_project_structures.deleted_at')
            ->where('bs_new_post_contract_form_information.updated_by', $bsUser->id)
            ->where('bs_contract_management_verifiers.module_identifier', PostContractClaim::TYPE_LETTER_OF_AWARD)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_verifiers as contract_management_verifiers_1')
                    ->where('module_identifier', '=', PostContractClaim::TYPE_LETTER_OF_AWARD)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('contract_management_verifiers_1.project_structure_id = bs_contract_management_verifiers.project_structure_id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_verifiers as contract_management_verifiers_2')
                    ->where('module_identifier', '=', PostContractClaim::TYPE_LETTER_OF_AWARD)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('contract_management_verifiers_2.project_structure_id = bs_contract_management_verifiers.project_structure_id');
            })
            ->lists('bs_contract_management_verifiers.project_structure_id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = NewPostContractFormInformation::select(
                'bs_new_post_contract_form_information.id',
                'bs_project_main_information.eproject_origin_id',
                'bs_project_structures.title',
                'bs_new_post_contract_form_information.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.user_id as current_verifier_id',
                'first_verifiers.user_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_new_post_contract_form_information.project_structure_id')
            ->join('bs_project_main_information', 'bs_project_main_information.project_structure_id', '=', 'bs_project_structures.id')
            ->leftJoin('bs_sf_guard_user_profile as submitters', 'submitters.user_id' , '=', 'bs_new_post_contract_form_information.updated_by')
            ->leftJoin(\DB::raw(
                "(select project_structure_id, min(sequence_number) as sequence_number
                from bs_contract_management_verifiers
                where approved is null
                and deleted_at is null
                and module_identifier = " . PostContractClaim::TYPE_LETTER_OF_AWARD . "
                group by project_structure_id) current_verifier_sequence_number"
                ), 'current_verifier_sequence_number.project_structure_id', '=', 'bs_project_structures.id')
            ->leftJoin('bs_contract_management_verifiers as current_verifiers', function($join){
                $join->on('current_verifiers.project_structure_id', '=', 'current_verifier_sequence_number.project_structure_id');
                $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
            })
            ->leftJoin('bs_sf_guard_user_profile as users', 'users.user_id' , '=', 'current_verifiers.user_id')
            ->leftJoin(\DB::raw(
                "(select project_structure_id, min(sequence_number) as sequence_number
                from bs_contract_management_verifiers
                where deleted_at is null
                and module_identifier = " . PostContractClaim::TYPE_LETTER_OF_AWARD . "
                group by project_structure_id) first_verifier_sequence_number"
                ), 'first_verifier_sequence_number.project_structure_id', '=', 'bs_project_structures.id')
            ->leftJoin('bs_contract_management_verifiers as first_verifiers', function($join){
                $join->on('first_verifiers.project_structure_id', '=', 'first_verifier_sequence_number.project_structure_id');
                $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
            })
            ->leftJoin(\DB::raw(
                "(select project_structure_id, max(sequence_number) as sequence_number
                from bs_contract_management_verifiers
                where approved = true
                and deleted_at is null
                and module_identifier = " . PostContractClaim::TYPE_LETTER_OF_AWARD . "
                group by project_structure_id) previous_verifier_sequence_number"
                ), 'previous_verifier_sequence_number.project_structure_id', '=', 'bs_project_structures.id')
            ->leftJoin('bs_contract_management_verifiers as previous_verifiers', function($join){
                $join->on('previous_verifiers.project_structure_id', '=', 'previous_verifier_sequence_number.project_structure_id');
                $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
            })
            ->whereIn('bs_project_structures.id', $relevantRecordIds)
            ->whereNull('bs_project_structures.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('bs_project_structures.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $projectIds = Project::where('projects.reference', 'ILIKE', '%'.$val.'%')->lists('id');

                            $model->whereIn('bs_project_main_information.eproject_origin_id', $projectIds);
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('bs_new_post_contract_form_information.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        $projects = Project::select('id', 'reference')->whereIn('id', $records->lists('eproject_origin_id'))->get();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $projects->find($record->eproject_origin_id)->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('contractManagement.letterOfAward.index', array($record->eproject_origin_id)),
                'route:verifiers'      => route('home.myProcesses.publishToPostContract.verifiers', array($record->eproject_origin_id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getPublishToPostContractVerifierList($project)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $bsUser = $user->getBsUser();

        $model = ContractManagementVerifier::select(
                'bs_contract_management_verifiers.id',
                'bs_sf_guard_user_profile.name',
                'bs_contract_management_verifiers.approved',
                'bs_contract_management_verifiers.verified_at'
            )
            ->join('bs_sf_guard_user_profile', 'bs_sf_guard_user_profile.user_id' , '=', 'bs_contract_management_verifiers.user_id')
            ->join('bs_project_main_information', 'bs_project_main_information.project_structure_id', '=', 'bs_contract_management_verifiers.project_structure_id')
            ->where('bs_project_main_information.eproject_origin_id', '=', $project->id)
            ->where('bs_contract_management_verifiers.module_identifier', PostContractClaim::TYPE_LETTER_OF_AWARD)
            ->whereNull('bs_contract_management_verifiers.deleted_at');

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
                            $model->where('bs_sf_guard_user_profile.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('bs_contract_management_verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    protected function getPostContractClaimList($moduleIdentifier, $verifierRouteName)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $bsUser = $user->getBsUser();

        $verifierRelevantRecordIds = ContractManagementClaimVerifier::select('bs_contract_management_claim_verifiers.object_id')
            ->join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_contract_management_claim_verifiers.project_structure_id')
            ->whereNull('bs_project_structures.deleted_at')
            ->where('bs_contract_management_claim_verifiers.user_id', $bsUser->id)
            ->where('bs_contract_management_claim_verifiers.module_identifier', $moduleIdentifier)
            ->whereExists(function($query) use ($moduleIdentifier){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_claim_verifiers as bs_contract_management_claim_verifiers_1')
                    ->where('module_identifier', '=', $moduleIdentifier)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('bs_contract_management_claim_verifiers_1.object_id = bs_contract_management_claim_verifiers.object_id');
            })
            ->whereNotExists(function($query) use ($moduleIdentifier){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_claim_verifiers as bs_contract_management_claim_verifiers_2')
                    ->where('module_identifier', '=', $moduleIdentifier)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('bs_contract_management_claim_verifiers_2.object_id = bs_contract_management_claim_verifiers.object_id');
            })
            ->lists('bs_contract_management_claim_verifiers.object_id');

        $submitterRelevantRecordIds = ContractManagementClaimVerifier::select('bs_contract_management_claim_verifiers.object_id')
            ->join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_contract_management_claim_verifiers.project_structure_id')
            ->whereNull('bs_project_structures.deleted_at')
            ->join('bs_post_contract_claims', function($join) use ($moduleIdentifier){
                $join->on('bs_post_contract_claims.id', '=', 'bs_contract_management_claim_verifiers.object_id');
                $join->on(\DB::raw("bs_contract_management_claim_verifiers.module_identifier = {$moduleIdentifier}"), \DB::raw(''), \DB::raw(''));
            })
            ->where('bs_post_contract_claims.updated_by', $bsUser->id)
            ->where('bs_contract_management_claim_verifiers.module_identifier', $moduleIdentifier)
            ->whereExists(function($query) use ($moduleIdentifier){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_claim_verifiers as bs_contract_management_claim_verifiers_1')
                    ->where('module_identifier', '=', $moduleIdentifier)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('bs_contract_management_claim_verifiers_1.object_id = bs_contract_management_claim_verifiers.object_id');
            })
            ->whereNotExists(function($query) use ($moduleIdentifier){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_claim_verifiers as bs_contract_management_claim_verifiers_2')
                    ->where('module_identifier', '=', $moduleIdentifier)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('bs_contract_management_claim_verifiers_2.object_id = bs_contract_management_claim_verifiers.object_id');
            })
            ->lists('bs_contract_management_claim_verifiers.object_id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = PostContractClaim::select(
                'bs_post_contract_claims.id',
                'bs_project_main_information.eproject_origin_id',
                'bs_project_structures.title',
                'bs_post_contract_claims.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.user_id as current_verifier_id',
                'first_verifiers.user_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_post_contract_claims.project_structure_id')
            ->join('bs_project_main_information', 'bs_project_main_information.project_structure_id', '=', 'bs_project_structures.id')
            ->leftJoin('bs_sf_guard_user_profile as submitters', 'submitters.user_id' , '=', 'bs_post_contract_claims.updated_by')
            ->leftJoin(\DB::raw(
                "(select object_id, min(sequence_number) as sequence_number
                from bs_contract_management_claim_verifiers
                where approved is null
                and deleted_at is null
                and module_identifier = {$moduleIdentifier}
                group by object_id) current_verifier_sequence_number"
                ), 'current_verifier_sequence_number.object_id', '=', 'bs_post_contract_claims.id')
            ->leftJoin('bs_contract_management_claim_verifiers as current_verifiers', function($join) use ($moduleIdentifier){
                $join->on('current_verifiers.object_id', '=', 'current_verifier_sequence_number.object_id');
                $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                $join->on(\DB::raw("current_verifiers.module_identifier = {$moduleIdentifier}"), \DB::raw(''), \DB::raw(''));
            })
            ->leftJoin('bs_sf_guard_user_profile as users', 'users.user_id' , '=', 'current_verifiers.user_id')
            ->leftJoin(\DB::raw(
                "(select object_id, min(sequence_number) as sequence_number
                from bs_contract_management_claim_verifiers
                where deleted_at is null
                and module_identifier = {$moduleIdentifier}
                group by object_id) first_verifier_sequence_number"
                ), 'first_verifier_sequence_number.object_id', '=', 'bs_post_contract_claims.id')
            ->leftJoin('bs_contract_management_claim_verifiers as first_verifiers', function($join) use ($moduleIdentifier){
                $join->on('first_verifiers.object_id', '=', 'first_verifier_sequence_number.object_id');
                $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                $join->on(\DB::raw("first_verifiers.module_identifier = {$moduleIdentifier}"), \DB::raw(''), \DB::raw(''));
            })
            ->leftJoin(\DB::raw(
                "(select object_id, max(sequence_number) as sequence_number
                from bs_contract_management_claim_verifiers
                where approved = true
                and deleted_at is null
                and module_identifier = {$moduleIdentifier}
                group by object_id) previous_verifier_sequence_number"
                ), 'previous_verifier_sequence_number.object_id', '=', 'bs_post_contract_claims.id')
            ->leftJoin('bs_contract_management_claim_verifiers as previous_verifiers', function($join) use ($moduleIdentifier){
                $join->on('previous_verifiers.object_id', '=', 'previous_verifier_sequence_number.object_id');
                $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                $join->on(\DB::raw("previous_verifiers.module_identifier = {$moduleIdentifier}"), \DB::raw(''), \DB::raw(''));
            })
            ->whereIn('bs_post_contract_claims.id', $relevantRecordIds)
            ->whereNull('bs_post_contract_claims.deleted_at')
            ->whereNull('bs_project_structures.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('bs_project_structures.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $projectIds = Project::where('projects.reference', 'ILIKE', '%'.$val.'%')->lists('id');

                            $model->whereIn('bs_project_main_information.eproject_origin_id', $projectIds);
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('bs_post_contract_claims.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        $projects = Project::select('id', 'reference')->whereIn('id', $records->lists('eproject_origin_id'))->get();

        $isTopManagementVerifier = $user->isTopManagementVerifier();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $isVerifierWithoutProjectAccess = is_null($user->getAssignedCompany($projects->find($record->eproject_origin_id))) && $isTopManagementVerifier;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $projects->find($record->eproject_origin_id)->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => $this->getPostContractClaimRoute($record->eproject_origin_id, $moduleIdentifier, $record->id, $isVerifierWithoutProjectAccess),
                'route:verifiers'      => route($verifierRouteName, array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    protected function getPostContractClaimRoute($projectId, $moduleIdentifier, $objectId, $isVerifierWithoutProjectAccess)
    {
        switch($moduleIdentifier)
        {
            case PostContractClaim::TYPE_WATER_DEPOSIT:
                $route = route('contractManagement.waterDeposit.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_DEPOSIT:
                $route = route('contractManagement.deposit.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM:
                $route = route('contractManagement.outOfContractItems.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_PURCHASE_ON_BEHALF:
                $route = route('contractManagement.purchaseOnBehalf.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_ADVANCED_PAYMENT:
                $route = route('contractManagement.advancedPayment.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_WORK_ON_BEHALF:
                $route = route('contractManagement.workOnBehalf.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE:
                $route = route('contractManagement.workOnBehalfBackCharge.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_PENALTY:
                $route = route('contractManagement.penalty.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_PERMIT:
                $route = route('contractManagement.permit.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                $route = route('contractManagement.materialOnSite.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_VARIATION_ORDER:
                $route = $isVerifierWithoutProjectAccess ? route('topManagementVerifiers.contractManagement.variationOrder.index', array( $projectId )) : route('contractManagement.variationOrder.index', array( $projectId ));
                break;
            case PostContractClaim::TYPE_CLAIM_CERTIFICATE:
                $route = $isVerifierWithoutProjectAccess ? route('topManagementVerifiers.contractManagement.claimCertificate.index', array( $projectId )) : route('contractManagement.claimCertificate.index', array( $projectId ));
                break;
            default:
                throw new \Exception('Invalid module');
        }

        return "{$route}#{$objectId}";
    }

    protected function getPostContractClaimVerifierList($moduleIdentifier, $objectId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $bsUser = $user->getBsUser();

        $model = ContractManagementClaimVerifier::select(
                'bs_contract_management_claim_verifiers.id',
                'bs_sf_guard_user_profile.name',
                'bs_contract_management_claim_verifiers.approved',
                'bs_contract_management_claim_verifiers.verified_at'
            )
            ->join('bs_sf_guard_user_profile', 'bs_sf_guard_user_profile.user_id' , '=', 'bs_contract_management_claim_verifiers.user_id')
            ->where('bs_contract_management_claim_verifiers.module_identifier', $moduleIdentifier)
            ->where('bs_contract_management_claim_verifiers.object_id', $objectId)
            ->whereNull('bs_contract_management_claim_verifiers.deleted_at');

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
                            $model->where('bs_sf_guard_user_profile.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('bs_contract_management_claim_verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getWaterDepositList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_WATER_DEPOSIT, 'home.myProcesses.waterDeposit.verifiers');
    }

    public function getWaterDepositVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_WATER_DEPOSIT, $objectId);
    }

    public function getDepositList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_DEPOSIT, 'home.myProcesses.deposit.verifiers');
    }

    public function getDepositVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_DEPOSIT, $objectId);
    }

    public function getOutOfContractItemsList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM, 'home.myProcesses.outOfContractItems.verifiers');
    }

    public function getOutOfContractItemsVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM, $objectId);
    }

    public function getPurchaseOnBehalfList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_PURCHASE_ON_BEHALF, 'home.myProcesses.purchaseOnBehalf.verifiers');
    }

    public function getPurchaseOnBehalfVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_PURCHASE_ON_BEHALF, $objectId);
    }

    public function getAdvancedPaymentList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_ADVANCED_PAYMENT, 'home.myProcesses.advancedPayment.verifiers');
    }

    public function getAdvancedPaymentVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_ADVANCED_PAYMENT, $objectId);
    }

    public function getWorkOnBehalfList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_WORK_ON_BEHALF, 'home.myProcesses.workOnBehalf.verifiers');
    }

    public function getWorkOnBehalfVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_WORK_ON_BEHALF, $objectId);
    }

    public function getWorkOnBehalfBackChargeList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE, 'home.myProcesses.workOnBehalfBackCharge.verifiers');
    }

    public function getWorkOnBehalfBackChargeVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE, $objectId);
    }

    public function getPenaltyList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_PENALTY, 'home.myProcesses.penalty.verifiers');
    }

    public function getPenaltyVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_PENALTY, $objectId);
    }

    public function getPermitList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_PERMIT, 'home.myProcesses.permit.verifiers');
    }

    public function getPermitVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_PERMIT, $objectId);
    }

    public function getVariationOrderList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_VARIATION_ORDER, 'home.myProcesses.variationOrder.verifiers');
    }

    public function getVariationOrderVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_VARIATION_ORDER, $objectId);
    }

    public function getMaterialOnSiteList()
    {
        return $this->getPostContractClaimList(PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE, 'home.myProcesses.materialOnSite.verifiers');
    }

    public function getMaterialOnSiteVerifierList($objectId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE, $objectId);
    }

    public function getClaimCertificateList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $bsUser = $user->getBsUser();

        $verifierRelevantRecordIds = ClaimCertificate::select('bs_claim_certificates.id')
            ->join('bs_contract_management_claim_verifiers', function($join){
                $join->on('bs_contract_management_claim_verifiers.object_id', '=', 'bs_claim_certificates.id');
                $join->on(\DB::raw("bs_contract_management_claim_verifiers.module_identifier = " . PostContractClaim::TYPE_CLAIM_CERTIFICATE), \DB::raw(''), \DB::raw(''));
            })
            ->join('bs_post_contract_claim_revisions', 'bs_post_contract_claim_revisions.id', '=', 'bs_claim_certificates.post_contract_claim_revision_id')
            ->join('bs_post_contracts', 'bs_post_contracts.id', '=', 'bs_post_contract_claim_revisions.post_contract_id')
            ->join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_post_contracts.project_structure_id')
            ->whereNull('bs_project_structures.deleted_at')
            ->where('bs_contract_management_claim_verifiers.user_id', $bsUser->id)
            ->where('bs_contract_management_claim_verifiers.module_identifier', PostContractClaim::TYPE_CLAIM_CERTIFICATE)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_claim_verifiers as bs_contract_management_claim_verifiers_1')
                    ->where('module_identifier', '=', PostContractClaim::TYPE_CLAIM_CERTIFICATE)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('bs_contract_management_claim_verifiers_1.object_id = bs_claim_certificates.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_claim_verifiers as bs_contract_management_claim_verifiers_2')
                    ->where('module_identifier', '=', PostContractClaim::TYPE_CLAIM_CERTIFICATE)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('bs_contract_management_claim_verifiers_2.object_id = bs_claim_certificates.id');
            })
            ->lists('bs_claim_certificates.id');

        $submitterRelevantRecordIds = ClaimCertificate::select('bs_claim_certificates.id')
            ->join('bs_post_contract_claim_revisions', 'bs_post_contract_claim_revisions.id', '=', 'bs_claim_certificates.post_contract_claim_revision_id')
            ->join('bs_post_contracts', 'bs_post_contracts.id', '=', 'bs_post_contract_claim_revisions.post_contract_id')
            ->join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_post_contracts.project_structure_id')
            ->whereNull('bs_project_structures.deleted_at')
            ->where('bs_claim_certificates.updated_by', '=', $bsUser->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_claim_verifiers as bs_contract_management_claim_verifiers_1')
                    ->where('module_identifier', '=', PostContractClaim::TYPE_CLAIM_CERTIFICATE)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('bs_contract_management_claim_verifiers_1.object_id = bs_claim_certificates.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('bs_contract_management_claim_verifiers as bs_contract_management_claim_verifiers_2')
                    ->where('module_identifier', '=', PostContractClaim::TYPE_CLAIM_CERTIFICATE)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('bs_contract_management_claim_verifiers_2.object_id = bs_claim_certificates.id');
            })
            ->lists('bs_claim_certificates.id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = ClaimCertificate::select(
                'bs_claim_certificates.id',
                'bs_project_main_information.eproject_origin_id',
                'bs_project_structures.title',
                'bs_claim_certificates.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.user_id as current_verifier_id',
                'first_verifiers.user_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('bs_post_contract_claim_revisions', 'bs_post_contract_claim_revisions.id', '=', 'bs_claim_certificates.post_contract_claim_revision_id')
            ->join('bs_post_contracts', 'bs_post_contracts.id', '=', 'bs_post_contract_claim_revisions.post_contract_id')
            ->join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_post_contracts.project_structure_id')
            ->join('bs_project_main_information', 'bs_project_main_information.project_structure_id', '=', 'bs_project_structures.id')
            ->leftJoin('bs_sf_guard_user_profile as submitters', 'submitters.user_id' , '=', 'bs_claim_certificates.updated_by')
            ->leftJoin(\DB::raw(
                "(select object_id, min(sequence_number) as sequence_number
                from bs_contract_management_claim_verifiers
                where approved is null
                and deleted_at is null
                and module_identifier = " . PostContractClaim::TYPE_CLAIM_CERTIFICATE . "
                group by object_id) current_verifier_sequence_number"
                ), 'current_verifier_sequence_number.object_id', '=', 'bs_claim_certificates.id')
            ->leftJoin('bs_contract_management_claim_verifiers as current_verifiers', function($join){
                $join->on('current_verifiers.object_id', '=', 'current_verifier_sequence_number.object_id');
                $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                $join->on(\DB::raw("current_verifiers.module_identifier = " . PostContractClaim::TYPE_CLAIM_CERTIFICATE), \DB::raw(''), \DB::raw(''));
            })
            ->leftJoin('bs_sf_guard_user_profile as users', 'users.user_id' , '=', 'current_verifiers.user_id')
            ->leftJoin(\DB::raw(
                "(select object_id, min(sequence_number) as sequence_number
                from bs_contract_management_claim_verifiers
                where deleted_at is null
                and module_identifier = " . PostContractClaim::TYPE_CLAIM_CERTIFICATE . "
                group by object_id) first_verifier_sequence_number"
                ), 'first_verifier_sequence_number.object_id', '=', 'bs_claim_certificates.id')
            ->leftJoin('bs_contract_management_claim_verifiers as first_verifiers', function($join){
                $join->on('first_verifiers.object_id', '=', 'first_verifier_sequence_number.object_id');
                $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                $join->on(\DB::raw("first_verifiers.module_identifier = " . PostContractClaim::TYPE_CLAIM_CERTIFICATE), \DB::raw(''), \DB::raw(''));
            })
            ->leftJoin(\DB::raw(
                "(select object_id, max(sequence_number) as sequence_number
                from bs_contract_management_claim_verifiers
                where approved = true
                and deleted_at is null
                and module_identifier = " . PostContractClaim::TYPE_CLAIM_CERTIFICATE . "
                group by object_id) previous_verifier_sequence_number"
                ), 'previous_verifier_sequence_number.object_id', '=', 'bs_claim_certificates.id')
            ->leftJoin('bs_contract_management_claim_verifiers as previous_verifiers', function($join){
                $join->on('previous_verifiers.object_id', '=', 'previous_verifier_sequence_number.object_id');
                $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                $join->on(\DB::raw("previous_verifiers.module_identifier = " . PostContractClaim::TYPE_CLAIM_CERTIFICATE), \DB::raw(''), \DB::raw(''));
            })
            ->whereIn('bs_claim_certificates.id', $relevantRecordIds)
            ->whereNull('bs_project_structures.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('bs_project_structures.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $projectIds = Project::where('projects.reference', 'ILIKE', '%'.$val.'%')->lists('id');

                            $model->whereIn('bs_project_main_information.eproject_origin_id', $projectIds);
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('bs_claim_certificates.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        $projects = Project::select('id', 'reference')->whereIn('id', $records->lists('eproject_origin_id'))->get();

        $isTopManagementVerifier = $user->isTopManagementVerifier();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $viewRoute = route('contractManagement.claimCertificate.index', array( $record->eproject_origin_id ));

            if(is_null($user->getAssignedCompany($projects->find($record->eproject_origin_id))) && $isTopManagementVerifier) $viewRoute = route('topManagementVerifiers.contractManagement.claimCertificate.index', array( $record->eproject_origin_id ));

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $projects->find($record->eproject_origin_id)->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => $viewRoute,
                'route:verifiers'      => route('home.myProcesses.claimCertificate.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getClaimCertificateVerifierList($claimCertificateId)
    {
        return $this->getPostContractClaimVerifierList(PostContractClaim::TYPE_CLAIM_CERTIFICATE, $claimCertificateId);
    }

    public function getRequestForVariationList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('request_for_variations.id')
            ->join('request_for_variations', function($join){
                $join->on('verifiers.object_id', '=', 'request_for_variations.id');
                $join->on(\DB::raw('verifiers.object_type = \''.RequestForVariation::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereNull('request_for_variations.deleted_at')
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RequestForVariation::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = request_for_variations.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RequestForVariation::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = request_for_variations.id');
            })
            ->lists('request_for_variations.id');

        $submitterRelevantRecordIds = RequestForVariation::select('request_for_variations.id')
            ->where('submitted_by', $user->id)
            ->whereNull('request_for_variations.deleted_at')
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RequestForVariation::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = request_for_variations.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', RequestForVariation::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = request_for_variations.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = RequestForVariation::select(
                'request_for_variations.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'request_for_variations.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->with('project')
            ->join('projects', 'projects.id', '=', 'request_for_variations.project_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'request_for_variations.submitted_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'request_for_variations.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.RequestForVariation::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'request_for_variations.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.RequestForVariation::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'request_for_variations.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.RequestForVariation::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'request_for_variations.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.RequestForVariation::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'request_for_variations.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.RequestForVariation::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'request_for_variations.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.RequestForVariation::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('request_for_variations.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at')
            ->whereNull('request_for_variations.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('request_for_variations.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        $isTopManagementVerifier = $user->isTopManagementVerifier();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $viewRoute = null;

            if($user->getAssignedCompany($record->project))
            {
                $viewRoute = route('requestForVariation.form.show', [$record->project_id, $record->id]);
            }
            else if($user->isTopManagementVerifier())
            {
                $viewRoute = route('topManagementVerifiers.requestForVariation.form.show', [$record->project_id, $record->id]);
            }

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => $viewRoute,
                'route:verifiers'      => route('home.myProcesses.requestForVariation.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRequestForVariationVerifierList($requestForVariationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $requestForVariationId)
            ->where('verifiers.object_type', RequestForVariation::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getAccountCodeSettingList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('account_code_settings.id')
            ->join('account_code_settings', function($join){
                $join->on('verifiers.object_id', '=', 'account_code_settings.id');
                $join->on(\DB::raw('verifiers.object_type = \''.AccountCodeSetting::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', AccountCodeSetting::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = account_code_settings.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', AccountCodeSetting::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = account_code_settings.id');
            })
            ->lists('account_code_settings.id');

        $submitterRelevantRecordIds = AccountCodeSetting::select('account_code_settings.id')
            ->where('submitted_for_approval_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', AccountCodeSetting::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = account_code_settings.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', AccountCodeSetting::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = account_code_settings.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = AccountCodeSetting::select(
                'account_code_settings.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'account_code_settings.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('projects', 'projects.id', '=', 'account_code_settings.project_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'account_code_settings.submitted_for_approval_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'account_code_settings.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.AccountCodeSetting::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'account_code_settings.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.AccountCodeSetting::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'account_code_settings.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.AccountCodeSetting::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'account_code_settings.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.AccountCodeSetting::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'account_code_settings.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.AccountCodeSetting::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'account_code_settings.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.AccountCodeSetting::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('account_code_settings.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('account_code_settings.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('finance.account.code.settings.show', [$record->project_id]),
                'route:verifiers'      => route('home.myProcesses.accountCodeSetting.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getAccountCodeSettingVerifierList($accountCodeSettingId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $accountCodeSettingId)
            ->where('verifiers.object_type', AccountCodeSetting::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getSiteManagementSiteDiaryList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('site_management_site_diary_general_form_responses.id')
            ->join('site_management_site_diary_general_form_responses', function($join){
                $join->on('verifiers.object_id', '=', 'site_management_site_diary_general_form_responses.id');
                $join->on(\DB::raw('verifiers.object_type = \''.SiteManagementSiteDiaryGeneralFormResponse::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', SiteManagementSiteDiaryGeneralFormResponse::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = site_management_site_diary_general_form_responses.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', SiteManagementSiteDiaryGeneralFormResponse::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = site_management_site_diary_general_form_responses.id');
            })
            ->lists('site_management_site_diary_general_form_responses.id');

        $submitterRelevantRecordIds = SiteManagementSiteDiaryGeneralFormResponse::select('site_management_site_diary_general_form_responses.id')
            ->where('site_management_site_diary_general_form_responses.submitted_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', SiteManagementSiteDiaryGeneralFormResponse::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = site_management_site_diary_general_form_responses.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', SiteManagementSiteDiaryGeneralFormResponse::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = site_management_site_diary_general_form_responses.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = SiteManagementSiteDiaryGeneralFormResponse::select(
                'site_management_site_diary_general_form_responses.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'site_management_site_diary_general_form_responses.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('projects', 'projects.id', '=', 'site_management_site_diary_general_form_responses.project_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'site_management_site_diary_general_form_responses.submitted_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'site_management_site_diary_general_form_responses.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.SiteManagementSiteDiaryGeneralFormResponse::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'site_management_site_diary_general_form_responses.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.SiteManagementSiteDiaryGeneralFormResponse::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'site_management_site_diary_general_form_responses.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.SiteManagementSiteDiaryGeneralFormResponse::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'site_management_site_diary_general_form_responses.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.SiteManagementSiteDiaryGeneralFormResponse::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'site_management_site_diary_general_form_responses.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.SiteManagementSiteDiaryGeneralFormResponse::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'site_management_site_diary_general_form_responses.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.SiteManagementSiteDiaryGeneralFormResponse::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('site_management_site_diary_general_form_responses.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('site_management_site_diary_general_form_responses.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('site-management-site-diary.general-form.show', [$record->project_id, $record->id]),
                'route:verifiers'      => route('home.myProcesses.siteManagementSiteDiaryList.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getSiteManagementSiteDiaryVerifierList($siteManagementSiteDiaryGeneralFormResponseId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $siteManagementSiteDiaryGeneralFormResponseId)
            ->where('verifiers.object_type', SiteManagementSiteDiaryGeneralFormResponse::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
        
    }

    public function getInstructionToContractorList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('instructions_to_contractors.id')
            ->join('instructions_to_contractors', function($join){
                $join->on('verifiers.object_id', '=', 'instructions_to_contractors.id');
                $join->on(\DB::raw('verifiers.object_type = \''.InstructionsToContractor::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', InstructionsToContractor::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = instructions_to_contractors.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', InstructionsToContractor::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = instructions_to_contractors.id');
            })
            ->lists('instructions_to_contractors.id');

        $submitterRelevantRecordIds = InstructionsToContractor::select('instructions_to_contractors.id')
            ->where('instructions_to_contractors.submitted_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', InstructionsToContractor::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = instructions_to_contractors.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', InstructionsToContractor::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = instructions_to_contractors.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = InstructionsToContractor::select(
                'instructions_to_contractors.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'instructions_to_contractors.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('projects', 'projects.id', '=', 'instructions_to_contractors.project_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'instructions_to_contractors.submitted_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'instructions_to_contractors.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.InstructionsToContractor::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'instructions_to_contractors.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.InstructionsToContractor::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'instructions_to_contractors.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.InstructionsToContractor::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'instructions_to_contractors.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.InstructionsToContractor::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'instructions_to_contractors.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.InstructionsToContractor::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'instructions_to_contractors.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.InstructionsToContractor::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('instructions_to_contractors.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('instructions_to_contractors.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('instruction-to-contractor.show', [$record->project_id, $record->id]),
                'route:verifiers'      => route('home.myProcesses.instructionToContractor.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getInstructionToContractorVerifierList($instructionToContractorId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $instructionToContractorId)
            ->where('verifiers.object_type', InstructionsToContractor::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
        
    }

    public function getDailyReportList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('daily_report.id')
            ->join('daily_report', function($join){
                $join->on('verifiers.object_id', '=', 'daily_report.id');
                $join->on(\DB::raw('verifiers.object_type = \''.DailyReport::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', DailyReport::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = daily_report.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', DailyReport::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = daily_report.id');
            })
            ->lists('daily_report.id');

        $submitterRelevantRecordIds = DailyReport::select('daily_report.id')
            ->where('daily_report.submitted_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', DailyReport::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = daily_report.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', DailyReport::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = daily_report.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = DailyReport::select(
                'daily_report.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'daily_report.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('projects', 'projects.id', '=', 'daily_report.project_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'daily_report.submitted_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'daily_report.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.DailyReport::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'daily_report.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.DailyReport::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'daily_report.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.DailyReport::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'daily_report.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.DailyReport::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'daily_report.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.DailyReport::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'daily_report.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.DailyReport::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('daily_report.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('daily_report.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('daily-report.show', [$record->project_id, $record->id]),
                'route:verifiers'      => route('home.myProcesses.dailyReportList.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getDailyReportVerifierList($dailyReportId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $dailyReportId)
            ->where('verifiers.object_type', DailyReport::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
        
    }

    public function getSiteManagementDefectBackchargeDetailList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('site_management_defect_backcharge_details.id')
            ->join('site_management_defect_backcharge_details', function($join){
                $join->on('verifiers.object_id', '=', 'site_management_defect_backcharge_details.id');
                $join->on(\DB::raw('verifiers.object_type = \''.SiteManagementDefectBackchargeDetail::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', SiteManagementDefectBackchargeDetail::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = site_management_defect_backcharge_details.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', SiteManagementDefectBackchargeDetail::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = site_management_defect_backcharge_details.id');
            })
            ->lists('site_management_defect_backcharge_details.id');

        $submitterRelevantRecordIds = SiteManagementDefectBackchargeDetail::select('site_management_defect_backcharge_details.id')
            ->where('user_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', SiteManagementDefectBackchargeDetail::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = site_management_defect_backcharge_details.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', SiteManagementDefectBackchargeDetail::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = site_management_defect_backcharge_details.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = SiteManagementDefectBackchargeDetail::select(
                'site_management_defect_backcharge_details.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'site_management_defect_backcharge_details.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('site_management_defects', 'site_management_defects.id', '=', 'site_management_defect_backcharge_details.site_management_defect_id')
            ->join('projects', 'projects.id', '=', 'site_management_defects.project_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'site_management_defect_backcharge_details.user_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'site_management_defect_backcharge_details.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.SiteManagementDefectBackchargeDetail::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'site_management_defect_backcharge_details.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.SiteManagementDefectBackchargeDetail::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'site_management_defect_backcharge_details.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.SiteManagementDefectBackchargeDetail::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'site_management_defect_backcharge_details.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.SiteManagementDefectBackchargeDetail::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'site_management_defect_backcharge_details.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.SiteManagementDefectBackchargeDetail::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'site_management_defect_backcharge_details.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.SiteManagementDefectBackchargeDetail::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('site_management_defect_backcharge_details.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('site_management_defect_backcharge_details.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('site-management-defect.showBackcharge', [$record->project_id, $record->id]),
                'route:verifiers'      => route('home.myProcesses.siteManagementDefectBackchargeDetail.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getSiteManagementDefectBackchargeDetailVerifierList($siteManagementDefectBackchargeDetailId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $siteManagementDefectBackchargeDetailId)
            ->where('verifiers.object_type', SiteManagementDefectBackchargeDetail::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRequestForInspectionList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('inspections.id')
            ->join('inspections', function($join){
                $join->on('verifiers.object_id', '=', 'inspections.id');
                $join->on(\DB::raw('verifiers.object_type = \''.Inspection::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->join('request_for_inspections', 'request_for_inspections.id', '=', 'inspections.request_for_inspection_id')
            ->join('projects', 'projects.id', '=', 'request_for_inspections.project_id')
            ->whereNull('projects.deleted_at')
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', Inspection::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = inspections.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', Inspection::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = inspections.id');
            })
            ->lists('inspections.id');

        $submitterRelevantRecordIds = Inspection::select('inspections.id')
            ->join('request_for_inspections', 'request_for_inspections.id', '=', 'inspections.request_for_inspection_id')
            ->join('projects', 'projects.id', '=', 'request_for_inspections.project_id')
            ->whereNull('projects.deleted_at')
            ->where('request_for_inspections.submitted_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', Inspection::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = inspections.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', Inspection::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = inspections.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = Inspection::select(
                'inspections.id',
                'projects.id as project_id',
                'projects.reference',
                'projects.title',
                'inspections.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'inspections.request_for_inspection_id',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->with('requestForInspection')
            ->join('request_for_inspections', 'request_for_inspections.id', '=', 'inspections.request_for_inspection_id')
            ->join('projects', 'projects.id', '=', 'request_for_inspections.project_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'request_for_inspections.submitted_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'inspections.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.Inspection::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'inspections.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.Inspection::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'inspections.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.Inspection::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'inspections.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.Inspection::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'inspections.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.Inspection::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'inspections.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.Inspection::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('inspections.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('inspections.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => $record->getRoute(),
                'route:verifiers'      => route('home.myProcesses.requestForInspection.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRequestForInspectionVerifierList($inspectionId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $inspectionId)
            ->where('verifiers.object_type', Inspection::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getVendorRegistrationList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('vendor_registrations.id')
            ->join('vendor_registrations', function($join){
                $join->on('verifiers.object_id', '=', 'vendor_registrations.id');
                $join->on(\DB::raw('verifiers.object_type = \''.VendorRegistration::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->whereNull('vendor_registrations.deleted_at')
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', VendorRegistration::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = vendor_registrations.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', VendorRegistration::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = vendor_registrations.id');
            })
            ->lists('vendor_registrations.id');

        $submitterRelevantRecordIds = VendorRegistration::select('vendor_registrations.id')
            ->whereNull('vendor_registrations.deleted_at')
            ->join('vendor_registration_processors', 'vendor_registration_processors.vendor_registration_id', '=', 'vendor_registrations.id')
            ->where('vendor_registration_processors.user_id', $user->id)
            ->whereNull('vendor_registration_processors.deleted_at')
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', VendorRegistration::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = vendor_registrations.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', VendorRegistration::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = vendor_registrations.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = VendorRegistration::select(
                'vendor_registrations.id',
                'companies.name as company_name',
                'vendor_registrations.submitted_at as updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('companies', 'companies.id', '=', 'vendor_registrations.company_id')
            ->join('vendor_registration_processors', 'vendor_registration_processors.vendor_registration_id', '=', 'vendor_registrations.id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'vendor_registration_processors.user_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'vendor_registrations.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.VendorRegistration::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'vendor_registrations.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.VendorRegistration::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'vendor_registrations.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.VendorRegistration::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'vendor_registrations.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.VendorRegistration::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'vendor_registrations.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.VendorRegistration::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'vendor_registrations.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.VendorRegistration::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('vendor_registrations.id', $relevantRecordIds)
            ->whereNull('vendor_registration_processors.deleted_at')
            ->whereNull('vendor_registrations.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('vendor_registrations.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'company_name'         => $record->company_name,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('vendorManagement.approval.registrationAndPreQualification.show', $record->id),
                'route:verifiers'      => route('home.myProcesses.vendorRegistration.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getVendorRegistrationVerifierList($vendorRegistrationId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $vendorRegistrationId)
            ->where('verifiers.object_type', VendorRegistration::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getVendorEvaluationList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = Verifier::select('vendor_performance_evaluation_company_forms.id')
            ->join('vendor_performance_evaluation_company_forms', function($join){
                $join->on('verifiers.object_id', '=', 'vendor_performance_evaluation_company_forms.id');
                $join->on(\DB::raw('verifiers.object_type = \''.VendorPerformanceEvaluationCompanyForm::class.'\''), \DB::raw(''), \DB::raw(''));
            })
            ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_performance_evaluation_company_forms.vendor_performance_evaluation_id')
            ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
            ->whereNull('projects.deleted_at')
            ->whereNull('vendor_performance_evaluations.deleted_at')
            ->whereNull('vendor_performance_evaluation_company_forms.deleted_at')
            ->where('verifiers.verifier_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', VendorPerformanceEvaluationCompanyForm::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = vendor_performance_evaluation_company_forms.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', VendorPerformanceEvaluationCompanyForm::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = vendor_performance_evaluation_company_forms.id');
            })
            ->lists('vendor_performance_evaluation_company_forms.id');

        $submitterRelevantRecordIds = VendorPerformanceEvaluationCompanyForm::select('vendor_performance_evaluation_company_forms.id')
            ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_performance_evaluation_company_forms.vendor_performance_evaluation_id')
            ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
            ->whereNull('projects.deleted_at')
            ->whereNull('vendor_performance_evaluations.deleted_at')
            ->whereNull('vendor_performance_evaluation_company_forms.deleted_at')
            ->where('vendor_performance_evaluation_company_forms.submitted_for_approval_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', VendorPerformanceEvaluationCompanyForm::class)
                    ->whereNull('approved')
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = vendor_performance_evaluation_company_forms.id');
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw('1'))
                    ->from('verifiers')
                    ->where('object_type', '=', VendorPerformanceEvaluationCompanyForm::class)
                    ->where('approved', '=', false)
                    ->whereNull('deleted_at')
                    ->whereRaw('verifiers.object_id = vendor_performance_evaluation_company_forms.id');
            })->lists('id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = VendorPerformanceEvaluationCompanyForm::select(
                'vendor_performance_evaluation_company_forms.id',
                'projects.reference',
                'projects.title',
                'companies.name as company_name',
                'vendor_performance_evaluation_company_forms.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.verifier_id as current_verifier_id',
                'first_verifiers.verifier_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.verified_at as previous_verifier_updated_at'
            )
            ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_performance_evaluation_company_forms.vendor_performance_evaluation_id')
            ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
            ->join('companies', 'companies.id', '=', 'vendor_performance_evaluation_company_forms.company_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'vendor_performance_evaluation_company_forms.submitted_for_approval_by')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where approved is null
                and deleted_at is null
                group by object_id, object_type) current_verifier_sequence_number"
                ), function($join){
                    $join->on('current_verifier_sequence_number.object_id', '=', 'vendor_performance_evaluation_company_forms.id');
                    $join->on(\DB::raw('current_verifier_sequence_number.object_type = \''.VendorPerformanceEvaluationCompanyForm::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as current_verifiers', function($join){
                    $join->on('current_verifiers.object_id', '=', 'vendor_performance_evaluation_company_forms.id');
                    $join->on(\DB::raw('current_verifiers.object_type = \''.VendorPerformanceEvaluationCompanyForm::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('current_verifiers.sequence_number', '=', 'current_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('current_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.verifier_id')
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, min(sequence_number) as sequence_number
                from verifiers
                where deleted_at is null
                group by object_id, object_type) first_verifier_sequence_number"
                ), function($join){
                    $join->on('first_verifier_sequence_number.object_id', '=', 'vendor_performance_evaluation_company_forms.id');
                    $join->on(\DB::raw('first_verifier_sequence_number.object_type = \''.VendorPerformanceEvaluationCompanyForm::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as first_verifiers', function($join){
                    $join->on('first_verifiers.object_id', '=', 'vendor_performance_evaluation_company_forms.id');
                    $join->on(\DB::raw('first_verifiers.object_type = \''.VendorPerformanceEvaluationCompanyForm::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('first_verifiers.sequence_number', '=', 'first_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('first_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin(\DB::raw(
                "(select object_id, object_type, max(sequence_number) as sequence_number
                from verifiers
                where approved = true
                and deleted_at is null
                group by object_id, object_type) previous_verifier_sequence_number"
                ), function($join){
                    $join->on('previous_verifier_sequence_number.object_id', '=', 'vendor_performance_evaluation_company_forms.id');
                    $join->on(\DB::raw('previous_verifier_sequence_number.object_type = \''.VendorPerformanceEvaluationCompanyForm::class.'\''), \DB::raw(''), \DB::raw(''));
                })
            ->leftJoin('verifiers as previous_verifiers', function($join){
                    $join->on('previous_verifiers.object_id', '=', 'vendor_performance_evaluation_company_forms.id');
                    $join->on(\DB::raw('previous_verifiers.object_type = \''.VendorPerformanceEvaluationCompanyForm::class.'\''), \DB::raw(''), \DB::raw(''));
                    $join->on('previous_verifiers.sequence_number', '=', 'previous_verifier_sequence_number.sequence_number');
                    $join->on(\DB::raw('previous_verifiers.deleted_at is null'), \DB::raw(''), \DB::raw(''));
                })
            ->whereIn('vendor_performance_evaluation_company_forms.id', $relevantRecordIds)
            ->whereNull('projects.deleted_at')
            ->whereNull('vendor_performance_evaluations.deleted_at')
            ->whereNull('vendor_performance_evaluation_company_forms.deleted_at');

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'company_name':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('vendor_performance_evaluation_company_forms.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference'            => $record->reference,
                'company_name'         => $record->company_name,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('vendorPerformanceEvaluation.companyForms.approval.edit', [$record->id]),
                'route:verifiers'      => route('home.myProcesses.vendorEvaluation.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getVendorEvaluationVerifierList($companyFormId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = Verifier::select(
                'verifiers.id',
                'users.name',
                'verifiers.approved',
                'verifiers.verified_at'
            )
            ->join('users', 'users.id' , '=', 'verifiers.verifier_id')
            ->where('verifiers.object_id', $companyFormId)
            ->where('verifiers.object_type', VendorPerformanceEvaluationCompanyForm::class)
            ->whereNull('verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('verifiers.sequence_number', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $record->approved,
                'verified_at'  => $record->approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRecommendationOfConsultantList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = ConsultantManagementRecommendationOfConsultantVerifierVersion::select('consultant_management_recommendation_of_consultants.id')
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
            ->whereNull('consultant_management_recommendation_of_consultant_verifiers.deleted_at')
            ->where('consultant_management_roc_verifier_versions.user_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_roc_verifier_versions vv
                    JOIN consultant_management_recommendation_of_consultant_verifiers v ON v.id = vv.consultant_management_recommendation_of_consultant_verifier_id
                    WHERE vv.status = " . ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_recommendation_of_consultant_id = consultant_management_recommendation_of_consultants.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_roc_verifier_versions vv
                    JOIN consultant_management_recommendation_of_consultant_verifiers v ON v.id = vv.consultant_management_recommendation_of_consultant_verifier_id
                    WHERE vv.status = " . ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_recommendation_of_consultant_id = consultant_management_recommendation_of_consultants.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_recommendation_of_consultants.id');

        $submitterRelevantRecordIds = ConsultantManagementRecommendationOfConsultantVerifierVersion::select('consultant_management_recommendation_of_consultants.id')
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
            ->whereNull('consultant_management_recommendation_of_consultant_verifiers.deleted_at')
            ->where('consultant_management_recommendation_of_consultants.updated_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_roc_verifier_versions vv
                    JOIN consultant_management_recommendation_of_consultant_verifiers v ON v.id = vv.consultant_management_recommendation_of_consultant_verifier_id
                    WHERE vv.status = " . ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_recommendation_of_consultant_id = consultant_management_recommendation_of_consultants.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_roc_verifier_versions vv
                    JOIN consultant_management_recommendation_of_consultant_verifiers v ON v.id = vv.consultant_management_recommendation_of_consultant_verifier_id
                    WHERE vv.status = " . ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_recommendation_of_consultant_id = consultant_management_recommendation_of_consultants.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_recommendation_of_consultants.id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = ConsultantManagementRecommendationOfConsultant::select(
                'consultant_management_recommendation_of_consultants.id',
                'consultant_management_vendor_categories_rfp.id as rfp_id',
                'consultant_management_contracts.reference_no',
                'consultant_management_contracts.title',
                'consultant_management_contracts.id as contract_id',
                'vendor_categories.name as vendor_category',
                'consultant_management_recommendation_of_consultants.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.user_id as current_verifier_id',
                'first_verifiers.user_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.updated_at as previous_verifier_updated_at'
            )
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_recommendation_of_consultants.vendor_category_rfp_id')
            ->join('consultant_management_contracts', 'consultant_management_contracts.id', '=', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'consultant_management_recommendation_of_consultants.updated_by')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_recommendation_of_consultant_id as id, min(v.id) as verifier_id
                    from consultant_management_recommendation_of_consultant_verifiers v
                    join consultant_management_roc_verifier_versions vv on vv.consultant_management_recommendation_of_consultant_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_PENDING . "
                    group by v.consultant_management_recommendation_of_consultant_id
                    ) current_verifier_ids"
                ), 'current_verifier_ids.id', '=', 'consultant_management_recommendation_of_consultants.id')
            ->leftJoin('consultant_management_recommendation_of_consultant_verifiers as current_verifiers', 'current_verifiers.id', '=', 'current_verifier_ids.verifier_id')
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.user_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_recommendation_of_consultant_id as id, min(v.id) as verifier_id
                    from consultant_management_recommendation_of_consultant_verifiers v
                    join consultant_management_roc_verifier_versions vv on vv.consultant_management_recommendation_of_consultant_verifier_id = v.id
                    where v.deleted_at is null
                    group by v.consultant_management_recommendation_of_consultant_id
                    ) first_verifier_ids"
                ), 'first_verifier_ids.id', '=', 'consultant_management_recommendation_of_consultants.id')
            ->leftJoin('consultant_management_recommendation_of_consultant_verifiers as first_verifiers', 'first_verifiers.id', '=', 'first_verifier_ids.verifier_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_recommendation_of_consultant_id as id, max(v.id) as verifier_id
                    from consultant_management_recommendation_of_consultant_verifiers v
                    join consultant_management_roc_verifier_versions vv on vv.consultant_management_recommendation_of_consultant_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_APPROVED . "
                    group by v.consultant_management_recommendation_of_consultant_id
                    ) previous_verifier_ids"
                ), 'previous_verifier_ids.id', '=', 'consultant_management_recommendation_of_consultants.id')
            ->leftJoin('consultant_management_recommendation_of_consultant_verifiers as previous_verifiers', 'previous_verifiers.id', '=', 'previous_verifier_ids.verifier_id')
            ->whereIn('consultant_management_recommendation_of_consultants.id', $relevantRecordIds);

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_recommendation_of_consultants.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference_no'         => $record->reference_no,
                'vendor_category'      => $record->vendor_category,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('consultant.management.roc.index', [$record->rfp_id]),
                'route:verifiers'      => route('home.myProcesses.recommendationOfConsultant.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRecommendationOfConsultantVerifierList($recommendationOfConsultantId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = ConsultantManagementRecommendationOfConsultantVerifierVersion::select(
                'consultant_management_roc_verifier_versions.id',
                'users.name',
                'consultant_management_roc_verifier_versions.status',
                'consultant_management_roc_verifier_versions.updated_at as verified_at'
            )
            ->join('users', 'users.id' , '=', 'consultant_management_roc_verifier_versions.user_id')
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id' , '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
            ->where('consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id', $recommendationOfConsultantId)
            ->whereNull('consultant_management_recommendation_of_consultant_verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_roc_verifier_versions.id', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = $record->status == ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_APPROVED ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getListOfConsultantList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = ConsultantManagementListOfConsultantVerifierVersion::select('consultant_management_list_of_consultants.id')
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->whereNull('consultant_management_list_of_consultant_verifiers.deleted_at')
            ->where('consultant_management_loc_verifier_versions.user_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_loc_verifier_versions vv
                    JOIN consultant_management_list_of_consultant_verifiers v ON v.id = vv.consultant_management_list_of_consultant_verifier_id
                    WHERE vv.status = " . ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_list_of_consultant_id = consultant_management_list_of_consultants.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_loc_verifier_versions vv
                    JOIN consultant_management_list_of_consultant_verifiers v ON v.id = vv.consultant_management_list_of_consultant_verifier_id
                    WHERE vv.status = " . ConsultantManagementListOfConsultantVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_list_of_consultant_id = consultant_management_list_of_consultants.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_list_of_consultants.id');

        $submitterRelevantRecordIds = ConsultantManagementListOfConsultantVerifierVersion::select('consultant_management_list_of_consultants.id')
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->whereNull('consultant_management_list_of_consultant_verifiers.deleted_at')
            ->where('consultant_management_list_of_consultants.updated_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_loc_verifier_versions vv
                    JOIN consultant_management_list_of_consultant_verifiers v ON v.id = vv.consultant_management_list_of_consultant_verifier_id
                    WHERE vv.status = " . ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_list_of_consultant_id = consultant_management_list_of_consultants.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_loc_verifier_versions vv
                    JOIN consultant_management_list_of_consultant_verifiers v ON v.id = vv.consultant_management_list_of_consultant_verifier_id
                    WHERE vv.status = " . ConsultantManagementListOfConsultantVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_list_of_consultant_id = consultant_management_list_of_consultants.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_list_of_consultants.id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = ConsultantManagementListOfConsultant::select(
                'consultant_management_list_of_consultants.id',
                'consultant_management_vendor_categories_rfp.id as rfp_id',
                'consultant_management_contracts.reference_no',
                'consultant_management_contracts.title',
                'consultant_management_contracts.id as contract_id',
                'vendor_categories.name as vendor_category',
                'consultant_management_list_of_consultants.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.user_id as current_verifier_id',
                'first_verifiers.user_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.updated_at as previous_verifier_updated_at'
            )
            ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_list_of_consultants.consultant_management_rfp_revision_id')
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
            ->join('consultant_management_contracts', 'consultant_management_contracts.id', '=', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'consultant_management_list_of_consultants.updated_by')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_list_of_consultant_id as id, min(v.id) as verifier_id
                    from consultant_management_list_of_consultant_verifiers v
                    join consultant_management_loc_verifier_versions vv on vv.consultant_management_list_of_consultant_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING . "
                    group by v.consultant_management_list_of_consultant_id
                    ) current_verifier_ids"
                ), 'current_verifier_ids.id', '=', 'consultant_management_list_of_consultants.id')
            ->leftJoin('consultant_management_list_of_consultant_verifiers as current_verifiers', 'current_verifiers.id', '=', 'current_verifier_ids.verifier_id')
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.user_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_list_of_consultant_id as id, min(v.id) as verifier_id
                    from consultant_management_list_of_consultant_verifiers v
                    join consultant_management_loc_verifier_versions vv on vv.consultant_management_list_of_consultant_verifier_id = v.id
                    where v.deleted_at is null
                    group by v.consultant_management_list_of_consultant_id
                    ) first_verifier_ids"
                ), 'first_verifier_ids.id', '=', 'consultant_management_list_of_consultants.id')
            ->leftJoin('consultant_management_list_of_consultant_verifiers as first_verifiers', 'first_verifiers.id', '=', 'first_verifier_ids.verifier_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_list_of_consultant_id as id, max(v.id) as verifier_id
                    from consultant_management_list_of_consultant_verifiers v
                    join consultant_management_loc_verifier_versions vv on vv.consultant_management_list_of_consultant_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ConsultantManagementListOfConsultantVerifierVersion::STATUS_APPROVED . "
                    group by v.consultant_management_list_of_consultant_id
                    ) previous_verifier_ids"
                ), 'previous_verifier_ids.id', '=', 'consultant_management_list_of_consultants.id')
            ->leftJoin('consultant_management_list_of_consultant_verifiers as previous_verifiers', 'previous_verifiers.id', '=', 'previous_verifier_ids.verifier_id')
            ->whereIn('consultant_management_list_of_consultants.id', $relevantRecordIds);

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_list_of_consultants.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference_no'         => $record->reference_no,
                'vendor_category'      => $record->vendor_category,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('consultant.management.loc.index', [$record->rfp_id]),
                'route:verifiers'      => route('home.myProcesses.listOfConsultant.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getListOfConsultantVerifierList($listOfConsultantId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = ConsultantManagementListOfConsultantVerifierVersion::select(
                'consultant_management_loc_verifier_versions.id',
                'users.name',
                'consultant_management_loc_verifier_versions.status',
                'consultant_management_loc_verifier_versions.updated_at as verified_at'
            )
            ->join('users', 'users.id' , '=', 'consultant_management_loc_verifier_versions.user_id')
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id' , '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->where('consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id', $listOfConsultantId)
            ->whereNull('consultant_management_list_of_consultant_verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_loc_verifier_versions.id', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = $record->status == ConsultantManagementListOfConsultantVerifierVersion::STATUS_APPROVED ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getCallingRfpList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = ConsultantManagementCallingRfpVerifierVersion::select('consultant_management_calling_rfp.id')
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->whereNull('consultant_management_calling_rfp_verifiers.deleted_at')
            ->where('consultant_management_call_rfp_verifier_versions.user_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_call_rfp_verifier_versions vv
                    JOIN consultant_management_calling_rfp_verifiers v ON v.id = vv.consultant_management_calling_rfp_verifier_id
                    WHERE vv.status = " . ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_calling_rfp_id = consultant_management_calling_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_call_rfp_verifier_versions vv
                    JOIN consultant_management_calling_rfp_verifiers v ON v.id = vv.consultant_management_calling_rfp_verifier_id
                    WHERE vv.status = " . ConsultantManagementCallingRfpVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_calling_rfp_id = consultant_management_calling_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_calling_rfp.id');

        $submitterRelevantRecordIds = ConsultantManagementCallingRfpVerifierVersion::select('consultant_management_calling_rfp.id')
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->whereNull('consultant_management_calling_rfp_verifiers.deleted_at')
            ->where('consultant_management_calling_rfp.updated_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_call_rfp_verifier_versions vv
                    JOIN consultant_management_calling_rfp_verifiers v ON v.id = vv.consultant_management_calling_rfp_verifier_id
                    WHERE vv.status = " . ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_calling_rfp_id = consultant_management_calling_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_call_rfp_verifier_versions vv
                    JOIN consultant_management_calling_rfp_verifiers v ON v.id = vv.consultant_management_calling_rfp_verifier_id
                    WHERE vv.status = " . ConsultantManagementCallingRfpVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_calling_rfp_id = consultant_management_calling_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_calling_rfp.id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = ConsultantManagementCallingRfp::select(
                'consultant_management_calling_rfp.id',
                'consultant_management_vendor_categories_rfp.id as rfp_id',
                'consultant_management_contracts.reference_no',
                'consultant_management_contracts.title',
                'consultant_management_contracts.id as contract_id',
                'vendor_categories.name as vendor_category',
                'consultant_management_calling_rfp.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.user_id as current_verifier_id',
                'first_verifiers.user_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.updated_at as previous_verifier_updated_at'
            )
            ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
            ->join('consultant_management_contracts', 'consultant_management_contracts.id', '=', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'consultant_management_calling_rfp.updated_by')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_calling_rfp_id as id, min(v.id) as verifier_id
                    from consultant_management_calling_rfp_verifiers v
                    join consultant_management_call_rfp_verifier_versions vv on vv.consultant_management_calling_rfp_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING . "
                    group by v.consultant_management_calling_rfp_id
                    ) current_verifier_ids"
                ), 'current_verifier_ids.id', '=', 'consultant_management_calling_rfp.id')
            ->leftJoin('consultant_management_calling_rfp_verifiers as current_verifiers', 'current_verifiers.id', '=', 'current_verifier_ids.verifier_id')
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.user_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_calling_rfp_id as id, min(v.id) as verifier_id
                    from consultant_management_calling_rfp_verifiers v
                    join consultant_management_call_rfp_verifier_versions vv on vv.consultant_management_calling_rfp_verifier_id = v.id
                    where v.deleted_at is null
                    group by v.consultant_management_calling_rfp_id
                    ) first_verifier_ids"
                ), 'first_verifier_ids.id', '=', 'consultant_management_calling_rfp.id')
            ->leftJoin('consultant_management_calling_rfp_verifiers as first_verifiers', 'first_verifiers.id', '=', 'first_verifier_ids.verifier_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_calling_rfp_id as id, max(v.id) as verifier_id
                    from consultant_management_calling_rfp_verifiers v
                    join consultant_management_call_rfp_verifier_versions vv on vv.consultant_management_calling_rfp_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ConsultantManagementCallingRfpVerifierVersion::STATUS_APPROVED . "
                    group by v.consultant_management_calling_rfp_id
                    ) previous_verifier_ids"
                ), 'previous_verifier_ids.id', '=', 'consultant_management_calling_rfp.id')
            ->leftJoin('consultant_management_calling_rfp_verifiers as previous_verifiers', 'previous_verifiers.id', '=', 'previous_verifier_ids.verifier_id')
            ->whereIn('consultant_management_calling_rfp.id', $relevantRecordIds);

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_calling_rfp.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference_no'         => $record->reference_no,
                'vendor_category'      => $record->vendor_category,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('consultant.management.calling.rfp.index', [$record->rfp_id]),
                'route:verifiers'      => route('home.myProcesses.callingRfp.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getCallingRfpVerifierList($callingRfpId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = ConsultantManagementCallingRfpVerifierVersion::select(
                'consultant_management_call_rfp_verifier_versions.id',
                'users.name',
                'consultant_management_call_rfp_verifier_versions.status',
                'consultant_management_call_rfp_verifier_versions.updated_at as verified_at'
            )
            ->join('users', 'users.id' , '=', 'consultant_management_call_rfp_verifier_versions.user_id')
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id' , '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->where('consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id', $callingRfpId)
            ->whereNull('consultant_management_calling_rfp_verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_call_rfp_verifier_versions.id', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = $record->status == ConsultantManagementCallingRfpVerifierVersion::STATUS_APPROVED ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getOpenRfpList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = ConsultantManagementOpenRfpVerifierVersion::select('consultant_management_open_rfp.id')
            ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
            ->whereNull('consultant_management_open_rfp_verifiers.deleted_at')
            ->where('consultant_management_open_rfp_verifier_versions.user_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_open_rfp_verifier_versions vv
                    JOIN consultant_management_open_rfp_verifiers v ON v.id = vv.consultant_management_open_rfp_verifier_id
                    WHERE vv.status = " . ConsultantManagementOpenRfpVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_open_rfp_id = consultant_management_open_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_open_rfp_verifier_versions vv
                    JOIN consultant_management_open_rfp_verifiers v ON v.id = vv.consultant_management_open_rfp_verifier_id
                    WHERE vv.status = " . ConsultantManagementOpenRfpVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_open_rfp_id = consultant_management_open_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_open_rfp.id');

        $submitterRelevantRecordIds = ConsultantManagementOpenRfpVerifierVersion::select('consultant_management_open_rfp.id')
            ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
            ->whereNull('consultant_management_open_rfp_verifiers.deleted_at')
            ->where('consultant_management_open_rfp.updated_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_open_rfp_verifier_versions vv
                    JOIN consultant_management_open_rfp_verifiers v ON v.id = vv.consultant_management_open_rfp_verifier_id
                    WHERE vv.status = " . ConsultantManagementOpenRfpVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_open_rfp_id = consultant_management_open_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_open_rfp_verifier_versions vv
                    JOIN consultant_management_open_rfp_verifiers v ON v.id = vv.consultant_management_open_rfp_verifier_id
                    WHERE vv.status = " . ConsultantManagementOpenRfpVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_open_rfp_id = consultant_management_open_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_open_rfp.id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = ConsultantManagementOpenRfp::select(
                'consultant_management_open_rfp.id',
                'consultant_management_vendor_categories_rfp.id as rfp_id',
                'consultant_management_contracts.reference_no',
                'consultant_management_contracts.title',
                'consultant_management_contracts.id as contract_id',
                'vendor_categories.name as vendor_category',
                'consultant_management_open_rfp.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.user_id as current_verifier_id',
                'first_verifiers.user_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.updated_at as previous_verifier_updated_at'
            )
            ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
            ->join('consultant_management_contracts', 'consultant_management_contracts.id', '=', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'consultant_management_open_rfp.updated_by')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_open_rfp_id as id, min(v.id) as verifier_id
                    from consultant_management_open_rfp_verifiers v
                    join consultant_management_open_rfp_verifier_versions vv on vv.consultant_management_open_rfp_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ConsultantManagementOpenRfpVerifierVersion::STATUS_PENDING . "
                    group by v.consultant_management_open_rfp_id
                    ) current_verifier_ids"
                ), 'current_verifier_ids.id', '=', 'consultant_management_open_rfp.id')
            ->leftJoin('consultant_management_open_rfp_verifiers as current_verifiers', 'current_verifiers.id', '=', 'current_verifier_ids.verifier_id')
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.user_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_open_rfp_id as id, min(v.id) as verifier_id
                    from consultant_management_open_rfp_verifiers v
                    join consultant_management_open_rfp_verifier_versions vv on vv.consultant_management_open_rfp_verifier_id = v.id
                    where v.deleted_at is null
                    group by v.consultant_management_open_rfp_id
                    ) first_verifier_ids"
                ), 'first_verifier_ids.id', '=', 'consultant_management_open_rfp.id')
            ->leftJoin('consultant_management_open_rfp_verifiers as first_verifiers', 'first_verifiers.id', '=', 'first_verifier_ids.verifier_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_open_rfp_id as id, max(v.id) as verifier_id
                    from consultant_management_open_rfp_verifiers v
                    join consultant_management_open_rfp_verifier_versions vv on vv.consultant_management_open_rfp_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ConsultantManagementOpenRfpVerifierVersion::STATUS_APPROVED . "
                    group by v.consultant_management_open_rfp_id
                    ) previous_verifier_ids"
                ), 'previous_verifier_ids.id', '=', 'consultant_management_open_rfp.id')
            ->leftJoin('consultant_management_open_rfp_verifiers as previous_verifiers', 'previous_verifiers.id', '=', 'previous_verifier_ids.verifier_id')
            ->whereIn('consultant_management_open_rfp.id', $relevantRecordIds);

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_open_rfp.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference_no'         => $record->reference_no,
                'vendor_category'      => $record->vendor_category,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('consultant.management.open.rfp.show', [$record->rfp_id, $record->id]),
                'route:verifiers'      => route('home.myProcesses.openRfp.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getOpenRfpVerifierList($openRfpId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = ConsultantManagementOpenRfpVerifierVersion::select(
                'consultant_management_open_rfp_verifier_versions.id',
                'users.name',
                'consultant_management_open_rfp_verifier_versions.status',
                'consultant_management_open_rfp_verifier_versions.updated_at as verified_at'
            )
            ->join('users', 'users.id' , '=', 'consultant_management_open_rfp_verifier_versions.user_id')
            ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id' , '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
            ->where('consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id', $openRfpId)
            ->whereNull('consultant_management_open_rfp_verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_open_rfp_verifier_versions.id', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = $record->status == ConsultantManagementOpenRfpVerifierVersion::STATUS_APPROVED ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRfpResubmissionList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = ConsultantManagementRfpResubmissionVerifierVersion::select('consultant_management_open_rfp.id')
            ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id', '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
            ->whereNull('consultant_management_rfp_resubmission_verifiers.deleted_at')
            ->where('consultant_management_rfp_resubmission_verifier_versions.user_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_rfp_resubmission_verifier_versions vv
                    JOIN consultant_management_rfp_resubmission_verifiers v ON v.id = vv.consultant_management_rfp_resubmission_verifier_id
                    WHERE vv.status = " . ConsultantManagementRfpResubmissionVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_open_rfp_id = consultant_management_open_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_rfp_resubmission_verifier_versions vv
                    JOIN consultant_management_rfp_resubmission_verifiers v ON v.id = vv.consultant_management_rfp_resubmission_verifier_id
                    WHERE vv.status = " . ConsultantManagementRfpResubmissionVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_open_rfp_id = consultant_management_open_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_open_rfp.id');

        $submitterRelevantRecordIds = ConsultantManagementRfpResubmissionVerifierVersion::select('consultant_management_open_rfp.id')
            ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id', '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
            ->whereNull('consultant_management_rfp_resubmission_verifiers.deleted_at')
            ->where('consultant_management_open_rfp.updated_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_rfp_resubmission_verifier_versions vv
                    JOIN consultant_management_rfp_resubmission_verifiers v ON v.id = vv.consultant_management_rfp_resubmission_verifier_id
                    WHERE vv.status = " . ConsultantManagementRfpResubmissionVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_open_rfp_id = consultant_management_open_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_rfp_resubmission_verifier_versions vv
                    JOIN consultant_management_rfp_resubmission_verifiers v ON v.id = vv.consultant_management_rfp_resubmission_verifier_id
                    WHERE vv.status = " . ConsultantManagementRfpResubmissionVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_open_rfp_id = consultant_management_open_rfp.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_open_rfp.id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = ConsultantManagementOpenRfp::select(
                'consultant_management_open_rfp.id',
                'consultant_management_vendor_categories_rfp.id as rfp_id',
                'consultant_management_contracts.reference_no',
                'consultant_management_contracts.title',
                'consultant_management_contracts.id as contract_id',
                'vendor_categories.name as vendor_category',
                'consultant_management_open_rfp.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.user_id as current_verifier_id',
                'first_verifiers.user_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.updated_at as previous_verifier_updated_at'
            )
            ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
            ->join('consultant_management_contracts', 'consultant_management_contracts.id', '=', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'consultant_management_open_rfp.updated_by')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_open_rfp_id as id, min(v.id) as verifier_id
                    from consultant_management_rfp_resubmission_verifiers v
                    join consultant_management_rfp_resubmission_verifier_versions vv on vv.consultant_management_rfp_resubmission_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ConsultantManagementRfpResubmissionVerifierVersion::STATUS_PENDING . "
                    group by v.consultant_management_open_rfp_id
                    ) current_verifier_ids"
                ), 'current_verifier_ids.id', '=', 'consultant_management_open_rfp.id')
            ->leftJoin('consultant_management_rfp_resubmission_verifiers as current_verifiers', 'current_verifiers.id', '=', 'current_verifier_ids.verifier_id')
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.user_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_open_rfp_id as id, min(v.id) as verifier_id
                    from consultant_management_rfp_resubmission_verifiers v
                    join consultant_management_rfp_resubmission_verifier_versions vv on vv.consultant_management_rfp_resubmission_verifier_id = v.id
                    where v.deleted_at is null
                    group by v.consultant_management_open_rfp_id
                    ) first_verifier_ids"
                ), 'first_verifier_ids.id', '=', 'consultant_management_open_rfp.id')
            ->leftJoin('consultant_management_rfp_resubmission_verifiers as first_verifiers', 'first_verifiers.id', '=', 'first_verifier_ids.verifier_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_open_rfp_id as id, max(v.id) as verifier_id
                    from consultant_management_rfp_resubmission_verifiers v
                    join consultant_management_rfp_resubmission_verifier_versions vv on vv.consultant_management_rfp_resubmission_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ConsultantManagementRfpResubmissionVerifierVersion::STATUS_APPROVED . "
                    group by v.consultant_management_open_rfp_id
                    ) previous_verifier_ids"
                ), 'previous_verifier_ids.id', '=', 'consultant_management_open_rfp.id')
            ->leftJoin('consultant_management_rfp_resubmission_verifiers as previous_verifiers', 'previous_verifiers.id', '=', 'previous_verifier_ids.verifier_id')
            ->whereIn('consultant_management_open_rfp.id', $relevantRecordIds);

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_open_rfp.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference_no'         => $record->reference_no,
                'vendor_category'      => $record->vendor_category,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('consultant.management.open.rfp.resubmission', [$record->rfp_id, $record->id]),
                'route:verifiers'      => route('home.myProcesses.openRfp.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRfpResubmissionVerifierList($openRfpId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = ConsultantManagementRfpResubmissionVerifierVersion::select(
                'consultant_management_rfp_resubmission_verifier_versions.id',
                'users.name',
                'consultant_management_rfp_resubmission_verifier_versions.status',
                'consultant_management_rfp_resubmission_verifier_versions.updated_at as verified_at'
            )
            ->join('users', 'users.id' , '=', 'consultant_management_rfp_resubmission_verifier_versions.user_id')
            ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id' , '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
            ->where('consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id', $openRfpId)
            ->whereNull('consultant_management_rfp_resubmission_verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_rfp_resubmission_verifier_versions.id', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = $record->status == ConsultantManagementRfpResubmissionVerifierVersion::STATUS_APPROVED ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getApprovalDocumentList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = ApprovalDocumentVerifierVersion::select('consultant_management_approval_documents.id')
            ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id', '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
            ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
            ->whereNull('consultant_management_approval_document_verifiers.deleted_at')
            ->where('consultant_management_approval_document_verifier_versions.user_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_approval_document_verifier_versions vv
                    JOIN consultant_management_approval_document_verifiers v ON v.id = vv.consultant_management_approval_document_verifier_id
                    WHERE vv.status = " . ApprovalDocumentVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_approval_document_id = consultant_management_approval_documents.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_approval_document_verifier_versions vv
                    JOIN consultant_management_approval_document_verifiers v ON v.id = vv.consultant_management_approval_document_verifier_id
                    WHERE vv.status = " . ApprovalDocumentVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_approval_document_id = consultant_management_approval_documents.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_approval_documents.id');

        $submitterRelevantRecordIds = ApprovalDocumentVerifierVersion::select('consultant_management_approval_documents.id')
            ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id', '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
            ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
            ->whereNull('consultant_management_approval_document_verifiers.deleted_at')
            ->where('consultant_management_approval_documents.updated_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_approval_document_verifier_versions vv
                    JOIN consultant_management_approval_document_verifiers v ON v.id = vv.consultant_management_approval_document_verifier_id
                    WHERE vv.status = " . ApprovalDocumentVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_approval_document_id = consultant_management_approval_documents.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_approval_document_verifier_versions vv
                    JOIN consultant_management_approval_document_verifiers v ON v.id = vv.consultant_management_approval_document_verifier_id
                    WHERE vv.status = " . ApprovalDocumentVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_approval_document_id = consultant_management_approval_documents.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_approval_documents.id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = ApprovalDocument::select(
                'consultant_management_approval_documents.id',
                'consultant_management_vendor_categories_rfp.id as rfp_id',
                'consultant_management_contracts.reference_no',
                'consultant_management_contracts.title',
                'consultant_management_contracts.id as contract_id',
                'vendor_categories.name as vendor_category',
                'consultant_management_approval_documents.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.user_id as current_verifier_id',
                'first_verifiers.user_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.updated_at as previous_verifier_updated_at'
            )
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_approval_documents.vendor_category_rfp_id')
            ->join('consultant_management_contracts', 'consultant_management_contracts.id', '=', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'consultant_management_approval_documents.updated_by')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_approval_document_id as id, min(v.id) as verifier_id
                    from consultant_management_approval_document_verifiers v
                    join consultant_management_approval_document_verifier_versions vv on vv.consultant_management_approval_document_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ApprovalDocumentVerifierVersion::STATUS_PENDING . "
                    group by v.consultant_management_approval_document_id
                    ) current_verifier_ids"
                ), 'current_verifier_ids.id', '=', 'consultant_management_approval_documents.id')
            ->leftJoin('consultant_management_approval_document_verifiers as current_verifiers', 'current_verifiers.id', '=', 'current_verifier_ids.verifier_id')
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.user_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_approval_document_id as id, min(v.id) as verifier_id
                    from consultant_management_approval_document_verifiers v
                    join consultant_management_approval_document_verifier_versions vv on vv.consultant_management_approval_document_verifier_id = v.id
                    where v.deleted_at is null
                    group by v.consultant_management_approval_document_id
                    ) first_verifier_ids"
                ), 'first_verifier_ids.id', '=', 'consultant_management_approval_documents.id')
            ->leftJoin('consultant_management_approval_document_verifiers as first_verifiers', 'first_verifiers.id', '=', 'first_verifier_ids.verifier_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_approval_document_id as id, max(v.id) as verifier_id
                    from consultant_management_approval_document_verifiers v
                    join consultant_management_approval_document_verifier_versions vv on vv.consultant_management_approval_document_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . ApprovalDocumentVerifierVersion::STATUS_APPROVED . "
                    group by v.consultant_management_approval_document_id
                    ) previous_verifier_ids"
                ), 'previous_verifier_ids.id', '=', 'consultant_management_approval_documents.id')
            ->leftJoin('consultant_management_approval_document_verifiers as previous_verifiers', 'previous_verifiers.id', '=', 'previous_verifier_ids.verifier_id')
            ->whereIn('consultant_management_approval_documents.id', $relevantRecordIds);

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_approval_documents.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference_no'         => $record->reference_no,
                'vendor_category'      => $record->vendor_category,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('consultant.management.contracts.contract.show', [$record->contract_id]),
                'route:verifiers'      => route('home.myProcesses.approvalDocument.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getApprovalDocumentVerifierList($approvalDocumentId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = ApprovalDocumentVerifierVersion::select(
                'consultant_management_approval_document_verifier_versions.id',
                'users.name',
                'consultant_management_approval_document_verifier_versions.status',
                'consultant_management_approval_document_verifier_versions.updated_at as verified_at'
            )
            ->join('users', 'users.id' , '=', 'consultant_management_approval_document_verifier_versions.user_id')
            ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id' , '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
            ->where('consultant_management_approval_document_verifiers.consultant_management_approval_document_id', $approvalDocumentId)
            ->whereNull('consultant_management_approval_document_verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_approval_document_verifier_versions.id', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = $record->status == ApprovalDocumentVerifierVersion::STATUS_APPROVED ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getConsultantManagementLetterOfAwardList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $verifierRelevantRecordIds = LetterOfAwardVerifierVersion::select('consultant_management_letter_of_awards.id')
            ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id', '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
            ->whereNull('consultant_management_letter_of_award_verifiers.deleted_at')
            ->where('consultant_management_letter_of_award_verifier_versions.user_id', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_letter_of_award_verifier_versions vv
                    JOIN consultant_management_letter_of_award_verifiers v ON v.id = vv.consultant_management_letter_of_award_verifier_id
                    WHERE vv.status = " . LetterOfAwardVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_letter_of_award_id = consultant_management_letter_of_awards.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_letter_of_award_verifier_versions vv
                    JOIN consultant_management_letter_of_award_verifiers v ON v.id = vv.consultant_management_letter_of_award_verifier_id
                    WHERE vv.status = " . LetterOfAwardVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_letter_of_award_id = consultant_management_letter_of_awards.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_letter_of_awards.id');

        $submitterRelevantRecordIds = LetterOfAwardVerifierVersion::select('consultant_management_letter_of_awards.id')
            ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id', '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
            ->whereNull('consultant_management_letter_of_award_verifiers.deleted_at')
            ->where('consultant_management_letter_of_awards.updated_by', $user->id)
            ->whereExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_letter_of_award_verifier_versions vv
                    JOIN consultant_management_letter_of_award_verifiers v ON v.id = vv.consultant_management_letter_of_award_verifier_id
                    WHERE vv.status = " . LetterOfAwardVerifierVersion::STATUS_PENDING . "
                    AND v.consultant_management_letter_of_award_id = consultant_management_letter_of_awards.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->whereNotExists(function($query){
                $query->select(\DB::raw(
                    "1
                    FROM consultant_management_letter_of_award_verifier_versions vv
                    JOIN consultant_management_letter_of_award_verifiers v ON v.id = vv.consultant_management_letter_of_award_verifier_id
                    WHERE vv.status = " . LetterOfAwardVerifierVersion::STATUS_REJECTED . "
                    AND v.consultant_management_letter_of_award_id = consultant_management_letter_of_awards.id
                    AND v.deleted_at IS NULL"
                ));
            })
            ->lists('consultant_management_letter_of_awards.id');

        $relevantRecordIds = array_unique(array_merge($verifierRelevantRecordIds, $submitterRelevantRecordIds));

        $model = ConsultantManagementLetterOfAward::select(
                'consultant_management_letter_of_awards.id',
                'consultant_management_vendor_categories_rfp.id as rfp_id',
                'consultant_management_contracts.reference_no',
                'consultant_management_contracts.title',
                'consultant_management_contracts.id as contract_id',
                'vendor_categories.name as vendor_category',
                'consultant_management_letter_of_awards.updated_at',
                'submitters.name as submitted_by',
                'current_verifiers.user_id as current_verifier_id',
                'first_verifiers.user_id as first_verifier_id',
                'users.name as verifier_name',
                'previous_verifiers.updated_at as previous_verifier_updated_at'
            )
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_letter_of_awards.vendor_category_rfp_id')
            ->join('consultant_management_contracts', 'consultant_management_contracts.id', '=', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
            ->leftJoin('users as submitters', 'submitters.id' , '=', 'consultant_management_letter_of_awards.updated_by')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_letter_of_award_id as id, min(v.id) as verifier_id
                    from consultant_management_letter_of_award_verifiers v
                    join consultant_management_letter_of_award_verifier_versions vv on vv.consultant_management_letter_of_award_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . LetterOfAwardVerifierVersion::STATUS_PENDING . "
                    group by v.consultant_management_letter_of_award_id
                    ) current_verifier_ids"
                ), 'current_verifier_ids.id', '=', 'consultant_management_letter_of_awards.id')
            ->leftJoin('consultant_management_letter_of_award_verifiers as current_verifiers', 'current_verifiers.id', '=', 'current_verifier_ids.verifier_id')
            ->leftJoin('users', 'users.id' , '=', 'current_verifiers.user_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_letter_of_award_id as id, min(v.id) as verifier_id
                    from consultant_management_letter_of_award_verifiers v
                    join consultant_management_letter_of_award_verifier_versions vv on vv.consultant_management_letter_of_award_verifier_id = v.id
                    where v.deleted_at is null
                    group by v.consultant_management_letter_of_award_id
                    ) first_verifier_ids"
                ), 'first_verifier_ids.id', '=', 'consultant_management_letter_of_awards.id')
            ->leftJoin('consultant_management_letter_of_award_verifiers as first_verifiers', 'first_verifiers.id', '=', 'first_verifier_ids.verifier_id')
            ->leftJoin(\DB::raw("(
                    select v.consultant_management_letter_of_award_id as id, max(v.id) as verifier_id
                    from consultant_management_letter_of_award_verifiers v
                    join consultant_management_letter_of_award_verifier_versions vv on vv.consultant_management_letter_of_award_verifier_id = v.id
                    where v.deleted_at is null
                    and vv.status = " . LetterOfAwardVerifierVersion::STATUS_APPROVED . "
                    group by v.consultant_management_letter_of_award_id
                    ) previous_verifier_ids"
                ), 'previous_verifier_ids.id', '=', 'consultant_management_letter_of_awards.id')
            ->leftJoin('consultant_management_letter_of_award_verifiers as previous_verifiers', 'previous_verifiers.id', '=', 'previous_verifier_ids.verifier_id')
            ->whereIn('consultant_management_letter_of_awards.id', $relevantRecordIds);

        if($request->get('getCountOnly')) return $model->count();

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
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'current_verifier':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_letter_of_awards.updated_at', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        $now = Carbon::now();

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $turnStartTime = ($record->current_verifier_id == $record->first_verifier_id) ? $record->updated_at : $record->previous_verifier_updated_at;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'title'                => $record->title,
                'reference_no'         => $record->reference_no,
                'vendor_category'      => $record->vendor_category,
                'submitted_at'         => Carbon::parse($record->updated_at)->format(\Config::get('dates.created_at')),
                'submitted_by'         => $record->submitted_by,
                'days_from_submission' => Carbon::parse($record->updated_at)->diffInDays($now),
                'days_pending'         => is_null($record->current_verifier_id) ? '-' : Carbon::parse($turnStartTime)->diffInDays($now),
                'current_verifier'     => is_null($record->current_verifier_id) ? '-' : $record->verifier_name,
                'route:view'           => route('consultant.management.contracts.contract.show', [$record->contract_id]),
                'route:verifiers'      => route('home.myProcesses.letterOfAward.verifiers', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getConsultantManagementLetterOfAwardVerifierList($letterOfAwardId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();

        $model = LetterOfAwardVerifierVersion::select(
                'consultant_management_letter_of_award_verifier_versions.id',
                'users.name',
                'consultant_management_letter_of_award_verifier_versions.status',
                'consultant_management_letter_of_award_verifier_versions.updated_at as verified_at'
            )
            ->join('users', 'users.id' , '=', 'consultant_management_letter_of_award_verifier_versions.user_id')
            ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id' , '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
            ->where('consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id', $letterOfAwardId)
            ->whereNull('consultant_management_letter_of_award_verifiers.deleted_at');

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
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_letter_of_award_verifier_versions.id', 'asc');

        $rowCount = $model->get()->count();

        $model->skip($limit * ($page - 1))->take($limit);

        $records = $model->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $approved = $record->status == LetterOfAwardVerifierVersion::STATUS_APPROVED ? true : null;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'approved'     => $approved,
                'verified_at'  => $approved ? Carbon::parse($record->verified_at)->format(\Config::get('dates.created_at')) : '-',
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }
}