<?php

use PCK\FormOfTender\FormOfTenderRepository;
use PCK\MyCompanyProfiles\MyCompanyProfileRepository;
use PCK\Notifications\EmailNotifier;
use PCK\Users\UserRepository;
use PCK\Filters\TenderFilters;
use PCK\Tenders\TenderRepository;
use PCK\Projects\ProjectRepository;
use PCK\Companies\CompanyRepository;
use PCK\Forms\OpenTenderReTenderForm;
use PCK\Verifier\Verifier;
use PCK\ContractGroups\Types\Role;
use PCK\Tenders\Tender;
use PCK\Tenders\CompanyTender;
use PCK\Tenders\CompanyTenderTenderAlternative;
use PCK\Projects\Project;
use PCK\Companies\Company;
use GuzzleHttp\Client;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;
use PCK\Buildspace\TenderSetting as bsTenderSetting;
use PCK\Buildspace\TenderAlternative as bsTenderAlternative;
use PCK\Filters\OpenTenderFilters;
use PCK\EBiddings\EBidding;
use PCK\GeneralSettings\GeneralSetting;

class ProjectOpenTendersController extends \BaseController {

    private $tenderRepo;

    private $projectRepo;

    private $userRepo;

    private $openTenderReTenderForm;

    private $companyRepo;

    private $formOfTenderRepository;
    private $myCompanyProfileRepository;
    private $cgProjectUserRepo;
    private $emailNotifier;

    public function __construct(
        TenderRepository $tenderRepo,
        ProjectRepository $projectRepo,
        UserRepository $userRepo,
        OpenTenderReTenderForm $openTenderReTenderForm,
        CompanyRepository $companyRepo,
        FormOfTenderRepository $formOfTenderRepository,
        MyCompanyProfileRepository $myCompanyProfileRepository,
        ContractGroupProjectUserRepository $repo,
        EmailNotifier $emailNotifier
    )
    {
        $this->tenderRepo                 = $tenderRepo;
        $this->projectRepo                = $projectRepo;
        $this->userRepo                   = $userRepo;
        $this->openTenderReTenderForm     = $openTenderReTenderForm;
        $this->companyRepo                = $companyRepo;
        $this->formOfTenderRepository     = $formOfTenderRepository;
        $this->myCompanyProfileRepository = $myCompanyProfileRepository;
        $this->cgProjectUserRepo         = $repo;
        $this->emailNotifier              = $emailNotifier;
    }

    public function index($project)
    {
        $tenders = $this->tenderRepo->all($project, array( 'submittedTenderRateContractors' ));

        return View::make('open_tenders.index', compact('project', 'tenders'));
    }

    public function show(Project $project, $tenderId)
    {
        $user     = \Confide::user();
        $tender   = $this->tenderRepo->find($project, $tenderId);
        if (! $tender) {    // Record not found
            Flash::error(trans('errors.recordNotFound'));
            return Redirect::route('projects.openTender.index', $project->id);
        }

        $isEditor = $user->isEditor($project);
        $eBidding = EBidding::where('project_id',$project->id)->first();

        $shortlistedTendererIds            = $this->getShortlistedTenderersId($tender->tendererTechnicalEvaluationInformation, true);
        $isTechnicalAssessmentFormApproved = $this->getTechnicalAssessmentFormApprovalStatus($tender);
        $includedTenderAlternatives        = $this->formOfTenderRepository->getIncludedTenderAlternativesByFormOfTenderId($tender->formOfTender->id);
        $isProjectOwnerOrGCD               = \Confide::user()->hasCompanyProjectRole($project, array( Role::PROJECT_OWNER, Role::GROUP_CONTRACT ));
        $isLatestTender                    = $project->latestTender->id == $tender->id;
        $previousTender                    = Tender::where('project_id', $project->id)->where('count', ($tender->count - 1))->first();
        $previousAwardRecommendation       = is_null($previousTender) ? null :$previousTender->openTenderAwardRecommendtion;
        $awardRecommendation               = $tender->openTenderAwardRecommendtion;

        $tendererData = [];

        $bsProjectMainInformation = $tender->project->getBsProjectMainInformation();

        $bsTenderAlternativeIds = ($bsProjectMainInformation) ? $bsProjectMainInformation->projectStructure->tenderAlternatives()->lists('id') : [];

        $companyTenderTenderAlternatives = [];

        if(!empty($bsTenderAlternativeIds))
        {
            $companyTenderIds = [];
            foreach($tender->selectedFinalContractors as $company)
            {
                $companyTenderIds[] = $company->pivot->id;
            }

            if(!empty($companyTenderIds))
            {
                $records = CompanyTenderTenderAlternative::whereIn('company_tender_id', $companyTenderIds)->whereIn('tender_alternative_id', $bsTenderAlternativeIds)->get()->toArray();

                foreach($records as $record)
                {
                    if(!array_key_exists($record['company_tender_id'], $companyTenderTenderAlternatives))
                    {
                        $companyTenderTenderAlternatives[$record['company_tender_id']] = [];
                    }

                    $companyTenderTenderAlternatives[$record['company_tender_id']][$record['tender_alternative_id']] = $record;
                }
            }
        }

        $currencyCode = $project->modified_currency_code;

        foreach($tender->selectedFinalContractors as $tenderer)
        {
            $generator = new \PCK\TenderAlternatives\TenderAlternativeGenerator($tender, $tenderer->pivot);

            if( $tenderer->pivot['submitted'] )
            {
                $tenderAlternativeData = $generator->generateAllAfterContractorInput($includedTenderAlternatives);
            }
            else
            {
                $tenderAlternativeData = $generator->generateAllBeforeContractorInput($includedTenderAlternatives);
            }
            
            foreach($tenderAlternativeData as $k1 => $tenderAlternatives)
            {
                foreach($tenderAlternatives as $k2 => $tenderAlternative)
                {
                    if(!array_key_exists($tenderer->id.'-'.$tenderAlternative['tender_alternative_id'], $tendererData))
                    {
                        if($tenderAlternative['tender_alternative_id'] > 0)
                        {
                            $data = null;
                            if(array_key_exists($tenderer->pivot->id, $companyTenderTenderAlternatives) && array_key_exists($tenderAlternative['tender_alternative_id'], $companyTenderTenderAlternatives[$tenderer->pivot->id]))
                            {
                                $data = $companyTenderTenderAlternatives[$tenderer->pivot->id][$tenderAlternative['tender_alternative_id']];
                            }

                            $earnestMoney               = ($data) ? $data['earnest_money'] : false;
                            $remarks                    = ($data && $data['remarks']) ? $data['remarks'] : "";
                            $contractorDiscount         = ($data) ? $data['discounted_amount'] : 0;
                            $contractorCompletionPeriod = ($data) ? $data['completion_period'] + 0 : 0;
                            $contractorAdjustment       = 0;

                            if($data)
                            {
                                $contractorAdjustment = ((float)$data['contractor_adjustment_percentage']) ? $data['contractor_adjustment_percentage'] : $data['contractor_adjustment_amount'];
                                $contractorAdjustment = ((float)$data['contractor_adjustment_percentage']) ? number_format($contractorAdjustment, 2, '.', ',').' %' : '('.$currencyCode.') '.number_format($contractorAdjustment, 2, '.', ',');
                            }
                        }
                        else
                        {
                            $earnestMoney               = $tenderer->pivot->earnest_money;
                            $remarks                    = $tenderer->pivot->remarks ?? "";
                            $contractorDiscount         = $tenderer->pivot->discounted_amount;
                            $contractorCompletionPeriod = $tenderer->pivot->completion_period + 0;
                            $contractorAdjustment       = ((float)$tenderer->pivot->contractor_adjustment_percentage) ? $tenderer->pivot->contractor_adjustment_percentage : $tenderer->pivot->contractor_adjustment_amount;
                            $contractorAdjustment       = ((float)$tenderer->pivot->contractor_adjustment_percentage) ? number_format($contractorAdjustment, 2, '.', ',').' %' : '('.$currencyCode.') '.number_format($contractorAdjustment, 2, '.', ',');

                        }

                        $tendererData[$tenderer->id.'-'.$tenderAlternative['tender_alternative_id']] = [
                            'id'                             => $tenderer->id.'_'.$tenderAlternative['tender_alternative_id'],
                            'tenderer_id'                    => $tenderer->id,
                            'tenderer'                       => $tenderer->name,
                            'tender_alternative_id'          => $tenderAlternative['tender_alternative_id'],
                            'tender_alternative_title'       => $tenderAlternative['tender_alternative_title'],
                            'submitted_at'                   => $tenderer->pivot->isSubmitted() ? $project->getProjectTimeZoneTime($tenderer->pivot->submitted_at) : null,
                            'earnest_money'                  => $earnestMoney,
                            'remarks'                        => $remarks,
                            'attachments_count'              => $tenderer->pivot->attachments->count(),
                            'form_of_tender_print_route'     => route('form_of_tender.contractorInput.print', array( $project->id, $tender->id, $tenderer->id )),
                            'tender_rates'                   => $tenderer->pivot->rates ? route('projects.openTender.downloadRatesFile', array( $project->id, $tender->id, $tenderer->id )) : null,
                            'contractors_discount'           => ($contractorDiscount) ? number_format($contractorDiscount, 2, '.', ',') : number_format($contractorDiscount, 0, '.', ','),
                            'project_incentive'              => number_format($tender->listOfTendererInformation->project_incentive_percentage, 2, '.', ','),
                            'contractors_completion_period'  => $contractorCompletionPeriod,
                            'contractors_adjustment'         => $contractorAdjustment,
                            'is_currently_selected_tenderer' => ($tenderer->id == $tender->currently_selected_tenderer_id && $tenderAlternative['tender_alternative_is_awarded']),
                            'is_shortlisted'                 => in_array($tenderer->id, $shortlistedTendererIds),
                            'is_final_selected_tenderer'     => $isLatestTender && $project->onPostContractStages() && $tenderer->pivot->selected_contractor
                        ];
                    }

                    $tendererData[$tenderer->id.'-'.$tenderAlternative['tender_alternative_id']]["tender_alternative_{$k1}_amount"] = number_format($tenderAlternatives[$k2]['amount'], 2, '.', ',');
                    $tendererData[$tenderer->id.'-'.$tenderAlternative['tender_alternative_id']]["tender_alternative_{$k1}_period"] = $tenderAlternatives[$k2]['period'];
                }
            }
        }
        
        $tendererData = array_values($tendererData);

        JavaScript::put([
            "tendererData" => $tendererData
        ]);

        $canEdit = true;

        if($user->isTopManagementVerifier() && ! $user->hasCompanyProjectRole($project, OpenTenderFilters::accessRoles($project)))
        {
            $canEdit = false;
        }

        $canViewAwardRecommendation = $awardRecommendation && $awardRecommendation->isApproved();

        $showEnableEbidding = $this->showEnableEbidding($project, $tender); // Show Enable E-Bidding button? (default: false)

        return View::make('open_tenders.show', array(
            'project'                           => $project,
            'user'                              => $user,
            'tender'                            => $tender,
            'previousTender'                    => $previousTender,
            'isEditor'                          => $isEditor,
            'includedTenderAlternatives'        => $includedTenderAlternatives,
            'shortlistedTendererIds'            => $shortlistedTendererIds,
            'isTechnicalAssessmentFormApproved' => $isTechnicalAssessmentFormApproved,
            'isProjectOwnerOrGCD'               => $isProjectOwnerOrGCD,
            'canViewAwardRecommendation'        => $canViewAwardRecommendation,
            'isLatestTender'                    => $isLatestTender,
            'previousAwardRecommendation'       => $previousAwardRecommendation,
            'awardRecommendation'               => $awardRecommendation,
            'canEdit'                           => $canEdit,
            'eBidding'                          => $eBidding,
            'showEnableEbidding'                => $showEnableEbidding
        ));
    }

    private function showEnableEbidding($project, $tender)
    {
        $showEnableEbidding = false;                                // Show Enable E-Bidding button? (default: false)
        $isLatestTender = $project->latestTender->id == $tender->id;
        $isClosedTender = $project->isCurrentTenderStatusClosed();  // Closed Tender status
        $eBiddingNotEnabled = ! $project->e_bidding;                // E-Bidding not yet enabled for project
        $eBiddingModuleEnabled = GeneralSetting::count() > 0 && GeneralSetting::first()->enable_e_bidding;  // E-Bidding module enabled?

        $submittedAwardRecommendation = false;
        $awardRecommendation = $tender->openTenderAwardRecommendtion;
        if ($awardRecommendation) {
            if ($awardRecommendation->isPendingForApproval() || $awardRecommendation->isApproved() || \PCK\Verifier\Verifier::isBeingVerified($awardRecommendation)) {
                $submittedAwardRecommendation = true;
            }
        }

        if ($isLatestTender && $isClosedTender && $eBiddingNotEnabled && $eBiddingModuleEnabled && ! $submittedAwardRecommendation) {
            $selectedSubmittedTenderRateContractor = $tender->submittedTenderRateContractors()->where('company_id', $tender->currently_selected_tenderer_id)->first();
            if ($selectedSubmittedTenderRateContractor) {
                $showEnableEbidding = true; // Show Enable E-Bidding button
            }
        }
        return $showEnableEbidding;
    }

    private function getTechnicalAssessmentFormApprovalStatus($tender)
    {
        if( is_null($tender->technicalEvaluation) ) return null;

        return Verifier::isApproved($tender->technicalEvaluation);
    }

    private function getShortlistedTenderersId($techEvalInfo, $flag)
    {
        $tendererIds = array();

        $records = $techEvalInfo->reject(function($obj) use ($flag)
        {
            return $obj->shortlisted != $flag;
        });

        foreach($records as $record)
        {
            array_push($tendererIds, $record->company_id);
        }

        return $tendererIds;
    }

    public function showReTender($project, $tenderId)
    {
        $user      = \Confide::user();
        $tender    = $this->tenderRepo->find($project, $tenderId);

        $company       = $user->getAssignedCompany($project);
        $contractGroup = $company->getContractGroup($project);
        $assignedUsers = $this->cgProjectUserRepo->getAssignedUsersByProjectAndContractGroup($project, $contractGroup);
        unset($assignedUsers[$user->id]);   //exclude current user

        $companyVerifiers = $user->getAssignedCompany($project)->getVerifierList($project);

        // editors only
        $verifiers = $companyVerifiers->filter(function($verifier) use ($assignedUsers) {
            return array_key_exists($verifier->id, $assignedUsers) && $assignedUsers[$verifier->id];   // check whether verifier is an editor
        });

        $isEditor = $user->isEditor($project);

        return View::make('open_tenders.retender_form', compact('user', 'project', 'tender', 'verifiers', 'isEditor'));
    }

    public function postReTender($project, $tenderId)
    {
        $inputs = Input::all();
        $tender = $this->tenderRepo->find($project, $tenderId);

        if( isset($inputs['send_to_verify']) || $tender->stillInProgress() )
        {
            $this->openTenderReTenderForm->setTender($tender);
            $this->openTenderReTenderForm->validate($inputs);
        }

        DB::transaction(function() use ($project, $tender, $inputs)
        {
            $this->tenderRepo->updateReTenderVerificationStatus($tender, $inputs);

            if( $tender->isSubmitted() )
            {
                if( ! $newTender = $this->tenderRepo->createNewReTender($tender) )
                {
                    Flash::error('Sorry, this tender cannot be revised, as the last tender is not yet finished!');

                    return Redirect::route('projects.openTender.show', array( $project->id, $tender->id ));
                }

                Flash::success(trans('tenders.tenderResubmissionSuccessful') . ": {$newTender->current_tender_name} !");

                return Redirect::route('projects.tender.index', array( $project->id ));
            }
        });

        \Flash::success("Successfully updated " . trans("tenders.tenderResubmission") . " Verification status for {$tender->current_tender_name} !");

        return Redirect::back();
    }

    public function assignOTVerifiersForm($project, $tenderId)
    {
        $user              = \Confide::user();
        $tender            = $this->tenderRepo->find($project, $tenderId);
        $selectedCompanies = $project->selectedCompanies;
        $selectedUsers     = $this->userRepo->getSelectedProjectUsersGroupByCompany($project);
        $selectedVerifiers = $tender->openTenderVerifiers->lists('id');
        $isEditor          = $user->isEditor($project);

        return View::make('open_tenders.open_tender_select_verifiers_form', compact('project', 'tender', 'selectedCompanies', 'selectedUsers', 'selectedVerifiers', 'isEditor', 'user'));
    }

    /**
     * Stops the current Open Tender Verification process and
     * reassigns the Open Tender verifiers.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reassignOTVerifiers($project, $tenderId)
    {
        $this->tenderRepo->reassignOTVerifiers($this->tenderRepo->find($project, $tenderId));

        Flash::success('Reassigned verifiers.');

        return Redirect::back();
    }

    public function processOTVerifiersForm($project, $tenderId)
    {
        $inputs = Input::all();
        $tender = $this->tenderRepo->find($project, $tenderId);

        DB::transaction(function() use ($project, $tender, $inputs)
        {
            $this->tenderRepo->syncSelectedOpenTenderVerifiers($tender, $inputs);

            $this->tenderRepo->updateTenderOpenTenderStatus($tender, $inputs);

            Flash::success("Successfully updated Tender ({$tender->current_tender_name}) Open Tender Verifier(s) Selection !");

            if( $tender->openTenderIsBeingValidated() )
            {
                // will send an email with link to inform selected verifiers to go to the link
                // and verify the decision that they want to do with the tender
                Event::fire('system.notifyOpenTenderVerifiers', array( $tender ));
            }
        });

        return Redirect::back();
    }

    // will need to add filter to check selected verifier to view the form only
    public function showOTVerifierDecisionForm($project, $tenderId)
    {
        $user   = \Confide::user();
        $tender = $this->tenderRepo->find($project, $tenderId);

        if( $tender->isTenderOpen() )
        {
            return View::make('open_tenders.partials.submitted_message', compact('user', 'project', 'tender'));
        }

        return View::make('open_tenders.verifier_decision_form', compact('user', 'project', 'tender'));
    }

    public function processOTVerifierDecisionForm($project, $tenderId)
    {
        $inputs = Input::all();
        $tender = $this->tenderRepo->find($project, $tenderId);

        DB::transaction(function() use ($project, $tender, $inputs)
        {
            $tender = $this->tenderRepo->updateTenderOpenTenderStatus($tender, $inputs);

            if( $tender->openTenderIsSubmitted() )
            {
                $this->tenderRepo->updateToOpenTenderStatus($project, $tender);

                Flash::success("Successfully updated Tender ({$tender->current_tender_name}) Status!");
            }
            else
            {
                Flash::success('Thank you for verifying!');
            }
        });

        return Redirect::back();
    }

    public function resendOTVerifierEmail($project, $tenderId, $receiverId)
    {
        $tender = $this->tenderRepo->find($project, $tenderId);
        $user   = $this->tenderRepo->getOpenTenderVerifierDetail($tender, $receiverId);

        if( ! $user )
        {
            Flash::error('Sorry, we cannot process your request to send the Verification Email because the verifier is non-existent.');

            return Redirect::back();
        }

        Event::fire('system.notifyOpenTenderVerifiers', array( $tender, $user ));

        Flash::success("Successfully send Open Verification Email to {$user->email}.");

        return Redirect::back();
    }

    public function viewOTVerifierLogs($project, $tenderId)
    {
        $tender = $this->tenderRepo->find($project, $tenderId);

        return View::make('open_tenders.open_tender_verifier_log', compact('tender', 'project'));
    }

    /**
     * Creates the Tenderers' Report
     *
     * @param $project
     *
     * @return \Illuminate\View\View
     */
    public function tendererReport($project)
    {
        return View::make('projects.tendererReport', compact('project'));
    }

    public function tendererReportList($project)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Company::select('companies.id', 'companies.name', 'company_tender.tender_amount', 'company_tender.updated_at', 'company_tender.submitted')
            ->join('company_tender', 'company_tender.company_id', '=', 'companies.id')
            ->join('tenders', 'tenders.id', '=', 'company_tender.tender_id')
            ->join('projects', 'projects.id', '=', 'tenders.project_id')
            ->where('tenders.id', '=', $project->latestTender->id)
            ->whereNull('projects.deleted_at');

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
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
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

            $data[] = [
                'id'                                     => $record->id,
                'counter'                                => $counter,
                'name'                                   => $record->name,
                'amount'                                 => number_format($record->tender_amount,2,".",","),
                'is_submitted'                           => $record->submitted,
                'submitted_date'                         => $project->getProjectTimeZoneTime($record->updated_at)->format(\Config::get('dates.submitted_at')),
                'count:withdrawn_tenders'                => $record->getWithdrawnTenders()->count(),
                'count:participated_tenders'             => $record->getParticipatedLatestTenders()->count(),
                'count:ongoing_projects'                 => $record->ongoingProjects->count(),
                'count:completed_projects'               => $record->completedProjects->count(),
                'route:withdrawn_tenders'                => route('projects.openTender.report.withdrawnTenders.list', [$project->id, $record->id]),
                'route:participated_tenders'             => route('projects.openTender.report.participatedTenders.list', [$project->id, $record->id]),
                'route:ongoing_projects'                 => route('projects.openTender.report.ongoingProjects.list', [$project->id, $record->id]),
                'route:completed_projects'               => route('projects.openTender.report.completedProjects.list', [$project->id, $record->id]),
                'route:ongoing_projects_contract_sums'   => route('projects.openTender.report.ongoingProjects.totalContractSums.list', [$project->id, $record->id]),
                'route:completed_projects_contract_sums' => route('projects.openTender.report.completedProjects.totalContractSums.list', [$project->id, $record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function exportTendererReport($project)
    {
        $reportGenerator = new \PCK\Reports\TenderersReportGenerator();

        return $reportGenerator->generate($project);
    }

    public function tendererReportWithdrawnTendersList($project, $companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $company = Company::find($companyId);

        $model = Project::select('projects.id', 'projects.title')
            ->whereIn('projects.id', $company->getWithdrawnTenders()->lists('project_id', 'project_id'))
            ->whereNull('projects.deleted_at');

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
                }
            }
        }

        $model->orderBy('projects.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'      => $record->id,
                'counter' => $counter,
                'title'   => $record->title,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function tendererReportParticipatedTendersList($project, $companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $company = Company::find($companyId);

        $model = Project::select('projects.id', 'projects.title', 'projects.country_id', 'projects.modified_currency_code', 'tenders.tender_closing_date',
                \DB::raw('CASE WHEN pam_2006_project_details.id IS NOT NULL
                    THEN pam_2006_project_details.contract_sum
                    ELSE indonesia_civil_contract_information.contract_sum
                    END as contract_sum'))
            ->join('tenders', 'tenders.project_id', '=', 'projects.id')
            ->leftJoin('pam_2006_project_details', 'pam_2006_project_details.project_id', '=', 'projects.id')
            ->leftJoin('indonesia_civil_contract_information', 'indonesia_civil_contract_information.project_id', '=', 'projects.id')
            ->join('countries', 'countries.id', '=', 'projects.country_id')
            ->whereIn('tenders.id', $company->getParticipatedLatestTenders()->lists('id'))
            ->whereNull('projects.deleted_at');

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
                    case 'currency_code':
                        if(strlen($val) > 0)
                        {
                            $model->whereRaw(\DB::raw("CASE
                                WHEN projects.modified_currency_code IS NULL
                                    THEN countries.currency_code ILIKE '%".$val."%'
                                ELSE projects.modified_currency_code ILIKE '%".$val."%'
                                END"));
                        }
                        break;
                }
            }
        }

        $model->orderBy('projects.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->id,
                'counter'       => $counter,
                'title'         => $record->title,
                'amount'        => number_format($record->contract_sum,2,".",","),
                'currency_code' => $record->modified_currency_code,
                'closing_date'  => \Carbon\Carbon::parse($record->tender_closing_date)->format(\Config::get('dates.submitted_at')),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function tendererReportOngoingProjectsList($project, $companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $company = Company::find($companyId);

        $model = Project::select('projects.id', 'projects.title', 'projects.modified_currency_code', 'projects.country_id',
                \DB::raw('CASE WHEN pam_2006_project_details.id IS NOT NULL
                    THEN pam_2006_project_details.contract_sum
                    ELSE indonesia_civil_contract_information.contract_sum
                    END as contract_sum'),
                \DB::raw('CASE WHEN pam_2006_project_details.id IS NOT NULL
                    THEN pam_2006_project_details.commencement_date
                    ELSE indonesia_civil_contract_information.commencement_date
                    END as commencement_date'))
            ->leftJoin('pam_2006_project_details', 'pam_2006_project_details.project_id', '=', 'projects.id')
            ->leftJoin('indonesia_civil_contract_information', 'indonesia_civil_contract_information.project_id', '=', 'projects.id')
            ->join('countries', 'countries.id', '=', 'projects.country_id')
            ->whereIn('projects.id', $company->ongoingProjects->lists('id'))
            ->whereNull('projects.deleted_at');

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
                    case 'currency_code':
                        if(strlen($val) > 0)
                        {
                            $model->whereRaw(\DB::raw("CASE
                                WHEN projects.modified_currency_code IS NULL
                                    THEN countries.currency_code ILIKE '%".$val."%'
                                ELSE projects.modified_currency_code ILIKE '%".$val."%'
                                END"));
                        }
                        break;
                }
            }
        }

        $model->orderBy('projects.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                => $record->id,
                'counter'           => $counter,
                'title'             => $record->title,
                'amount'            => number_format($record->contract_sum,2,".",","),
                'currency_code'     => $record->modified_currency_code,
                'commencement_date' => \Carbon\Carbon::parse($record->commencement_date)->format(\Config::get('dates.submitted_at')),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function tendererReportCompletedProjectsList($project, $companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $company = Company::find($companyId);

        $model = Project::select('projects.id', 'projects.title', 'projects.modified_currency_code', 'projects.country_id',
                \DB::raw('CASE WHEN pam_2006_project_details.id IS NOT NULL
                    THEN pam_2006_project_details.contract_sum
                    ELSE indonesia_civil_contract_information.contract_sum
                    END as contract_sum'),
                \DB::raw('CASE WHEN pam_2006_project_details.id IS NOT NULL
                    THEN pam_2006_project_details.commencement_date
                    ELSE indonesia_civil_contract_information.commencement_date
                    END as commencement_date'))
            ->leftJoin('pam_2006_project_details', 'pam_2006_project_details.project_id', '=', 'projects.id')
            ->leftJoin('indonesia_civil_contract_information', 'indonesia_civil_contract_information.project_id', '=', 'projects.id')
            ->join('countries', 'countries.id', '=', 'projects.country_id')
            ->whereIn('projects.id', $company->completedProjects->lists('id'))
            ->whereNull('projects.deleted_at');

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
                    case 'currency_code':
                        if(strlen($val) > 0)
                        {
                            $model->whereRaw(\DB::raw("CASE
                                WHEN projects.modified_currency_code IS NULL
                                    THEN countries.currency_code ILIKE '%".$val."%'
                                ELSE projects.modified_currency_code ILIKE '%".$val."%'
                                END"));
                        }
                        break;
                }
            }
        }

        $model->orderBy('projects.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'              => $record->id,
                'counter'         => $counter,
                'title'           => $record->title,
                'amount'          => number_format($record->contract_sum,2,".",","),
                'currency_code'   => $record->modified_currency_code,
                'completion_date' => \Carbon\Carbon::parse($record->completion_date)->format(\Config::get('dates.submitted_at')),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function tendererReportOngoingProjectsTotalContractSumList($project, $companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $company = Company::find($companyId);

        $model = Project::select(
                \DB::raw('SUM(CASE WHEN pam_2006_project_details.id IS NOT NULL
                    THEN pam_2006_project_details.contract_sum
                    ELSE indonesia_civil_contract_information.contract_sum
                    END) as contract_sum'),
                \DB::raw('CASE WHEN projects.modified_currency_code IS NOT NULL
                    THEN projects.modified_currency_code
                    ELSE countries.currency_code
                    END as project_currency_code'))
            ->leftJoin('pam_2006_project_details', 'pam_2006_project_details.project_id', '=', 'projects.id')
            ->leftJoin('indonesia_civil_contract_information', 'indonesia_civil_contract_information.project_id', '=', 'projects.id')
            ->join('countries', 'countries.id', '=', 'projects.country_id')
            ->whereIn('projects.id', $company->ongoingProjects->lists('id'))
            ->whereNull('projects.deleted_at')
            ->groupBy(\DB::raw('project_currency_code'));

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'currency_code':
                        if(strlen($val) > 0)
                        {
                            $model->whereRaw(\DB::raw("CASE
                                WHEN projects.modified_currency_code IS NULL
                                    THEN countries.currency_code ILIKE '%".$val."%'
                                ELSE projects.modified_currency_code ILIKE '%".$val."%'
                                END"));
                        }
                        break;
                }
            }
        }

        $model->orderBy('project_currency_code', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->project_currency_code,
                'counter'       => $counter,
                'title'         => $record->title,
                'amount'        => number_format($record->contract_sum,2,".",","),
                'currency_code' => $record->project_currency_code,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function tendererReportCompletedProjectsTotalContractSumList($project, $companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $company = Company::find($companyId);

        $model = Project::select(
                \DB::raw('SUM(CASE WHEN pam_2006_project_details.id IS NOT NULL
                    THEN pam_2006_project_details.contract_sum
                    ELSE indonesia_civil_contract_information.contract_sum
                    END) as contract_sum'),
                \DB::raw('CASE WHEN projects.modified_currency_code IS NOT NULL
                    THEN projects.modified_currency_code
                    ELSE countries.currency_code
                    END as project_currency_code'))
            ->leftJoin('pam_2006_project_details', 'pam_2006_project_details.project_id', '=', 'projects.id')
            ->leftJoin('indonesia_civil_contract_information', 'indonesia_civil_contract_information.project_id', '=', 'projects.id')
            ->join('countries', 'countries.id', '=', 'projects.country_id')
            ->whereIn('projects.id', $company->completedProjects->lists('id'))
            ->whereNull('projects.deleted_at')
            ->groupBy(\DB::raw('project_currency_code'));

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'currency_code':
                        if(strlen($val) > 0)
                        {
                            $model->whereRaw(\DB::raw("CASE
                                WHEN projects.modified_currency_code IS NULL
                                    THEN countries.currency_code ILIKE '%".$val."%'
                                ELSE projects.modified_currency_code ILIKE '%".$val."%'
                                END"));
                        }
                        break;
                }
            }
        }

        $model->orderBy('project_currency_code', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->project_currency_code,
                'counter'       => $counter,
                'title'         => $record->title,
                'amount'        => number_format($record->contract_sum,2,".",","),
                'currency_code' => $record->project_currency_code,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function downloadTenderRatesFile($project, $tenderId, $contractorId)
    {
        $company = $this->companyRepo->find($contractorId);
        $tender  = $this->tenderRepo->find($project, $tenderId);

        $file_name = PCK\Tenders\SubmitTenderRate::ratesFileName;

        $file = PCK\Tenders\SubmitTenderRate::getContractorRatesUploadPath($project, $tender, $company) . "/{$file_name}";

        return Response::download($file, null, array(
            'Content-Type: application/force-download'
        ));
    }

    /**
     * Updates the Submit Tender Rate's remark (company_tender table).
     *
     * @param $project
     * @param $tenderId
     *
     * @return string
     */
    public function updateSubmitTenderRateRemarks($project, $tenderId)
    {
        if( Request::ajax() )
        {
            $inputs = Input::get('data');

            $tender = Tender::find($tenderId);
            $company = Company::find($inputs['id']);

            $success = $this->tenderRepo->updateSubmitTenderRateRemarks($tender, $company, (int)$inputs['taid'], $inputs['remarks']);
            $message = null;
        }
        else
        {
            $success = false;
            $message = 'Forbidden access';
        }

        return json_encode(array(
            'success' => $success,
            'message' => $message
        ));
    }

    /**
     * Update the Submit Tender Rate's earnest money (whether it is paid or not) (company_tender table).
     *
     * @param $project
     * @param $tenderId
     *
     * @return string
     */
    public function updateSubmitTenderRateEarnestMoney($project, $tenderId)
    {
        if( Request::ajax())
        {
            $inputs = Input::get('data');

            $tender = Tender::find($tenderId);
            $company = Company::find($inputs['id']);

            $success = $this->tenderRepo->updateSubmitTenderRateEarnestMoney($tender, $company, (int)$inputs['taid'], $inputs['earnestMoney']);
            $message = null;
        }
        else
        {
            $success = false;
            $message = 'Forbidden access';
        }

        return json_encode([
            'success' => $success,
            'message' => $message
        ]);
    }

    /**
     * Updates the Tender's validity period (measured in number of days).
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTenderValidityPeriod($project, $tenderId)
    {
        $this->tenderRepo->updateTenderValidityPeriod($tenderId, Input::get('validity_period_in_days'));

        return Redirect::to(route('projects.openTender.show', array( $project->id, $tenderId )));
    }

    /**
     * Generates a Record of Tender Opening printout.
     *
     * @param $project
     * @param $tenderId
     *
     * @return bool
     */
    public function record($project, $tenderId)
    {
        $tender                            = $this->tenderRepo->find($project, $tenderId);
        $includedTenderAlternatives        = $this->formOfTenderRepository->getIncludedTenderAlternativesByFormOfTenderId($tender->formOfTender->id);
        $isTechnicalAssessmentFormApproved = $this->getTechnicalAssessmentFormApprovalStatus($tender);

        foreach($tender->submittedTenderRateContractors as $company)
        {
            $generator = new \PCK\TenderAlternatives\TenderAlternativeGenerator($tender, $company->pivot);

            $tenderAlternativeData = $generator->generateAllAfterContractorInput($includedTenderAlternatives);

            $company->tenderAlternativeData = $tenderAlternativeData;
        }

        $tender->submittedTenderRateContractors = $this->tenderRepo->sortSubmittedTenderRateContractorsByTenderAlternativeAmount($tender->submittedTenderRateContractors, 1);

        $shortlistedTendererIds = $this->getShortlistedTenderersId($tender->tendererTechnicalEvaluationInformation, true);

        $selectedVerifiers = $tender->openTenderVerifiersApproved()->orderBy('created_at', 'asc')->get();

        foreach($selectedVerifiers as $verifier)
        {
            $log           = $this->tenderRepo->getOpenTenderVerifierLogByTenderAndVerifierId($tenderId, $verifier->id);
            $verifier->log = $log;
        }

        $bsProjectMainInformation = $project->getBsProjectMainInformation();

        $pteBudgetRecords = [];

        if($bsProjectMainInformation->projectStructure->tenderAlternatives->count())
        {
            $records = $this->getBuildspaceOverallTotalByTenderAlternatives($project);

            foreach($bsProjectMainInformation->projectStructure->tenderAlternatives as $tenderAlternative)
            {
                $pteBudgetRecords[] = [
                    'title' => $tenderAlternative->title,
                    'total' => array_key_exists($tenderAlternative->id, $records) ? $records[$tenderAlternative->id] : 0
                ];
            }
        }
        else
        {
            $bsOverallProjectTotal = $this->getBuildspaceOverallProjectTotal($project);

            $pteBudgetRecords[] = [
                'title' => '',
                'total' => $bsOverallProjectTotal
            ];
        }

        PDF::setOptions('--header-spacing 5 --margin-top 10 --orientation landscape');

        return PDF::html('open_tenders.print.opening_tender_record', array(
            'project'                           => $project,
            'tender'                            => $tender,
            'includedTenderAlternatives'        => $includedTenderAlternatives,
            'selectedVerifiers'                 => $selectedVerifiers,
            'companyLogoSrc'                    => $this->myCompanyProfileRepository->find()->company_logo_path ? public_path() . $this->myCompanyProfileRepository->find()->company_logo_path : '',
            'shortlistedTendererIds'            => $shortlistedTendererIds,
            'isTechnicalAssessmentFormApproved' => $isTechnicalAssessmentFormApproved,
            'pteBudgetRecords'                  => $pteBudgetRecords
        ));
    }

    public function exportExcelOpenTenderForm(Project $project, $tenderId)
    {
        $tender                            = $this->tenderRepo->find($project, $tenderId);
        $includedTenderAlternatives        = $this->formOfTenderRepository->getIncludedTenderAlternativesClassNamesByTenderId($tenderId);
        $isTechnicalAssessmentFormApproved = $this->getTechnicalAssessmentFormApprovalStatus($tender);
        $shortlistedTendererIds            = $this->getShortlistedTenderersId($tender->tendererTechnicalEvaluationInformation, true);

        $bsProjectMainInformation = $project->getBsProjectMainInformation();

        $pteBudgetRecords = [];

        if($bsProjectMainInformation->projectStructure->tenderAlternatives->count())
        {
            $records = $this->getBuildspaceOverallTotalByTenderAlternatives($project);

            foreach($bsProjectMainInformation->projectStructure->tenderAlternatives as $tenderAlternative)
            {
                $pteBudgetRecords[] = [
                    'title' => $tenderAlternative->title,
                    'total' => array_key_exists($tenderAlternative->id, $records) ? $records[$tenderAlternative->id] : 0
                ];
            }
        }
        else
        {
            $bsOverallProjectTotal = $this->getBuildspaceOverallProjectTotal($project);

            $pteBudgetRecords[] = [
                'title' => '',
                'total' => $bsOverallProjectTotal
            ];
        }

        $reportGenerator = new \PCK\Reports\OpenTenderFormReportGenerator($tender, $isTechnicalAssessmentFormApproved, $shortlistedTendererIds, $pteBudgetRecords);

        return $reportGenerator->generate();
    }

    public function saveCurrentlySelectedTenderer($project, $tenderId)
    {
        $inputs = Input::all();

        $success = false;

        $tender = Tender::find($tenderId);
        $company = Company::find($inputs['contractorId']);
        if($tender && $company)
        {
            $bsProjectMainInformation  = $project->getBsProjectMainInformation();
            if($bsProjectMainInformation && $bsProject = $bsProjectMainInformation->projectStructure)
            {
                $bsCompany     = $company->getBsCompany();
                $tenderSetting = $bsProject->tenderSetting;

                if($tenderSetting && $bsCompany)
                {
                    $tenderSetting->awarded_company_id = $bsCompany->id;
                    $tenderSetting->save();

                    $tenderAlternativeId = (isset($inputs['tenderAlternativeId']) && (int)$inputs['tenderAlternativeId'] > 0) ? (int)$inputs['tenderAlternativeId'] : 0;
                    if($tenderAlternative = bsTenderAlternative::find($tenderAlternativeId))
                    {
                        //reset any previous awarded tender alternative
                        bsTenderAlternative::where('project_structure_id', $bsProject->id)->update(['is_awarded'=>false]);

                        /* we need to do this from query builder because in L4 there is no refresh method to reload the object class.
                         * We need to reload tenderAlterntive object since we run query builder to reset all tenderAlternative is_awarded to false.
                         */
                        bsTenderAlternative::where('id', $tenderAlternative->id)->update(['is_awarded'=>true]);

                        //reset company_tender values because company_tender values should be coming from the selected tender alternative values
                        CompanyTender::where('tender_id', $tender->id)->update([
                            'tender_amount' => 0,
                            'other_bill_type_amount_except_prime_cost_provisional' => 0,
                            'supply_of_material_amount' => 0,
                            'original_tender_amount' => 0,
                            'discounted_percentage' => 0,
                            'discounted_amount' => 0,
                            'completion_period' => 0,
                            'contractor_adjustment_amount' => 0,
                            'contractor_adjustment_percentage' => 0,
                            'earnest_money' => false,
                            'selected_contractor' => false
                        ]);

                        $companyTender = CompanyTender::where('tender_id', $tender->id)->where('company_id', $company->id)->first();
                        $companyTenderTenderAlternative = ($companyTender) ? CompanyTenderTenderAlternative::where('company_tender_id', $companyTender->id)->where('tender_alternative_id', $tenderAlternative->id)->first() : null;

                        //we set the company_tender values based on the selected tender alternative values
                        if($companyTenderTenderAlternative)
                        {
                            $companyTender->tender_amount = $companyTenderTenderAlternative->tender_amount;
                            $companyTender->other_bill_type_amount_except_prime_cost_provisional = $companyTenderTenderAlternative->other_bill_type_amount_except_prime_cost_provisional;
                            $companyTender->supply_of_material_amount = $companyTenderTenderAlternative->supply_of_material_amount;
                            $companyTender->original_tender_amount = $companyTenderTenderAlternative->original_tender_amount;
                            $companyTender->discounted_percentage = $companyTenderTenderAlternative->discounted_percentage;
                            $companyTender->discounted_amount = $companyTenderTenderAlternative->discounted_amount;
                            $companyTender->completion_period = $companyTenderTenderAlternative->completion_period;
                            $companyTender->contractor_adjustment_amount = $companyTenderTenderAlternative->contractor_adjustment_amount;
                            $companyTender->contractor_adjustment_percentage = $companyTenderTenderAlternative->contractor_adjustment_percentage;
                            $companyTender->earnest_money = $companyTenderTenderAlternative->earnest_money;
                            $companyTender->selected_contractor = true;
                            $companyTender->remarks = trim($companyTenderTenderAlternative->remarks);

                            $companyTender->save();
                        }
                    }
                }
            }

            $tender->currently_selected_tenderer_id = $company->id;
            $tender->save();

            \Artisan::call("project:create-award-recommendation-bill-details", ['tender_id' => $tender->id]);

            $success = true;
        }

        $showEnableEbidding = false;
        if ($success) {
            $showEnableEbidding = $this->showEnableEbidding($project, $tender); // Show Enable E-Bidding button? (default: false)
        }

        return Response::json([
            'success' => $success,
            'showEnableEbidding' => $showEnableEbidding
        ]);
    }

    private function getBuildspaceOverallProjectTotal(Project $project)
    {
        $projectStructureId  = $project->getBsProjectMainInformation()->project_structure_id;
        $projectOverallTotal = null;

        $client = new Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => getenv('BUILDSPACE_URL'),
        ));

        try
        {
            $response = $client->post('eproject_api/getProjectOverallTotal', array(
                'form_params' => array(
                    'projectStructureId' => $projectStructureId,
                )
            ));

            $response            = json_decode($response->getBody());
            $projectOverallTotal = $response->overallProjectTotal;
        }
        catch(Exception $e)
        {
            \Log::info("Get overall project total fails. [project_structure_id: { $projectStructureId }] => {$e->getMessage()}");
        }

        return $projectOverallTotal;
    }

    private function getBuildspaceOverallTotalByTenderAlternatives(Project $project)
    {
        $projectStructureId  = $project->getBsProjectMainInformation()->project_structure_id;
        $records = [];

        $client = new Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => getenv('BUILDSPACE_URL'),
        ));

        try
        {
            $response = $client->post('eproject_api/getOverallTotalByTenderAlternatives', array(
                'form_params' => array(
                    'projectStructureId' => $projectStructureId,
                )
            ));

            $response = json_decode($response->getBody());
            $records  = json_decode(json_encode($response->records), true);
        }
        catch(Exception $e)
        {
            \Log::info("Get overall total by tender alternatives fails. [project_structure_id: { $projectStructureId }] => {$e->getMessage()}");
        }

        return $records;
    }
}