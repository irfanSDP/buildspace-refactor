<?php

use Illuminate\Support\Facades\DB;
use PCK\ContractLimits\ContractLimitRepository;
use PCK\Projects\Project;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository;
use PCK\TenderInterviews\TenderInterviewRepository;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformation;
use PCK\TenderReminder\SentTenderRemindersLog;
use PCK\TenderReminder\TenderReminder;
use PCK\Tenders\TenderStages;
use PCK\Users\UserRepository;
use PCK\Filters\TenderFilters;
use PCK\Tenders\TenderRepository;
use PCK\Companies\CompanyRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\TenderCallingTenderInformationForm;
use PCK\Forms\TenderListOfTendererInformationForm;
use PCK\Forms\TenderRecommendationOfTendererInformationForm;
use PCK\Forms\CompanyConfirmStatusForm;
use PCK\TenderCallingTenderInformation\TenderCallingTenderInformation;
use PCK\TenderCallingTenderInformation\TenderCallingTenderInformationRepository;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformationRepository;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformationRepository;
use PCK\Tenders\Tender;
use PCK\Tenders\OpenTenderPageInformation;
use PCK\Tenders\OpenTenderPersonInCharge;
use PCK\Tenders\OpenTenderAnnouncement;
use PCK\Tenders\OpenTenderTenderDocument;
use PCK\Tenders\OpenTenderTenderRequirement;
use PCK\Tenders\OpenTenderIndustryCode;
use PCK\Tenders\AcknowledgementLetter;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;
use PCK\ExpressionOfInterest\ExpressionOfInterestTokens;
use PCK\Companies\Company;
use PCK\Notifications\EmailNotifier;
use PCK\Notifications\SystemNotifier;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PCK\Settings\Language;
use PCK\ProcurementMethod\ProcurementMethod;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\SystemModules\SystemModuleConfiguration;
use PCK\Vendor\Vendor;
use PCK\CompanyPersonnel\CompanyPersonnel;
use PCK\Base\Helpers;
use PCK\Verifier\Verifier;
use PCK\GeneralSettings\GeneralSetting;

class ProjectTendersController extends \BaseController {

    private $tenderRepo;
    private $companyRepo;
    private $userRepo;
    private $tenderROTInformationRepo;
    private $tenderLOTInformationRepo;
    private $tenderCallingTenderInformationRepo;
    private $tenderROTInformationForm;
    private $tenderLOTInformationForm;
    private $tenderCallingTenderInformationForm;
    private $tenderInterviewRepository;
    private $contractLimitRepository;
    private $technicalEvaluationSetReferenceRepository;
    private $emailNotifier;
    private $systemNotifier;
    private $companyConfirmStatusForm;

    public function __construct(
        TenderRepository $tenderRepo,
        CompanyRepository $companyRepo,
        UserRepository $userRepo,
        TenderRecommendationOfTendererInformationRepository $tenderROTInformationRepo,
        TenderListOfTendererInformationRepository $tenderLOTInformationRepo,
        TenderCallingTenderInformationRepository $tenderCallingTenderInformationRepo,
        TenderRecommendationOfTendererInformationForm $tenderROTInformationForm,
        TenderListOfTendererInformationForm $tenderLOTInformationForm,
        TenderCallingTenderInformationForm $tenderCallingTenderInformationForm,
        TenderInterviewRepository $tenderInterviewRepository,
        ContractLimitRepository $contractLimitRepository,
        TechnicalEvaluationSetReferenceRepository $technicalEvaluationSetReferenceRepository,
        EmailNotifier $emailNotifier,
        SystemNotifier $systemNotifier,
        CompanyConfirmStatusForm $companyConfirmStatusForm
    )
    {
        $this->tenderRepo                                = $tenderRepo;
        $this->companyRepo                               = $companyRepo;
        $this->userRepo                                  = $userRepo;
        $this->tenderROTInformationRepo                  = $tenderROTInformationRepo;
        $this->tenderLOTInformationRepo                  = $tenderLOTInformationRepo;
        $this->tenderCallingTenderInformationRepo        = $tenderCallingTenderInformationRepo;
        $this->tenderROTInformationForm                  = $tenderROTInformationForm;
        $this->tenderLOTInformationForm                  = $tenderLOTInformationForm;
        $this->tenderCallingTenderInformationForm        = $tenderCallingTenderInformationForm;
        $this->tenderInterviewRepository                 = $tenderInterviewRepository;
        $this->contractLimitRepository                   = $contractLimitRepository;
        $this->technicalEvaluationSetReferenceRepository = $technicalEvaluationSetReferenceRepository;
        $this->emailNotifier                             = $emailNotifier;
        $this->systemNotifier                            = $systemNotifier;
        $this->companyConfirmStatusForm                  = $companyConfirmStatusForm;
    }

    /**
     * Returns the view for the list of tenders for the current project.
     *
     * @param $project
     *
     * @return \Illuminate\View\View
     */
    public function index($project)
    {
        $tenders = $this->tenderRepo->all($project);

        return View::make('tenders.index', compact('project', 'tenders'));
    }

    /**
     * Returns the view for a tender.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function show(Project $project, $tenderId)
    {
        $user                              = \Confide::user();
        $tender                            = $this->tenderRepo->find($project, $tenderId);
        $isEditor                          = $user->isEditor($project);
        $tenderInterviewInformation        = $this->tenderInterviewRepository->findOrNewTenderInterviewInformationByTender($tenderId);

        $hasTechnicalEvaluationTemplate = $this->technicalEvaluationSetReferenceRepository->hasTemplate($project->workCategory);
        $setReference                   = $this->technicalEvaluationSetReferenceRepository->getSetReferenceByProject($project);
        $contractLimits                 = $this->technicalEvaluationSetReferenceRepository->getWorkCategoryContractLimits($project->workCategory);
        $completionPeriodMetricOptions  = TenderRecommendationOfTendererInformation::getCompletionPeriodMetrics();
        $procurementMethodOptions       = ProcurementMethod::orderBy('id', 'desc')->lists('name', 'id');

        $procurementMethodOptions = array( null => trans('forms.none') ) + $procurementMethodOptions;

        $isVendorManagementEnabled   = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);
        $selectContractorModalId     = ($tender->getTenderStage() == TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER) ? TenderRecommendationOfTendererInformation::MODAL_ID : TenderListOfTendererInformation::MODAL_ID;
        $saveSelectedContractorRoute = ($tender->getTenderStage() == TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER) ? route('projects.tender.rot_selected_contractors', [$project->id, $tender->id]) : route('projects.tender.lot_selected_contractors', [$project->id, $tender->id]);

        if($tender->recommendationOfTendererInformation)
        {
            $rotSelectedContractorIds                = $tender->recommendationOfTendererInformation->selectedContractors->lists('id');
            $rotDuplicateCompanyPersonnelsByCompany  = CompanyPersonnel::getDuplicateCompanyPersonnelsGroupByCompany($rotSelectedContractorIds);
            $rotDuplicateCompanyPersonnelsCompanyIds = array_column($rotDuplicateCompanyPersonnelsByCompany, 'company_id');
        }

        if($tender->listOfTendererInformation)
        {
            $lotSelectedContractorIds = [];

            foreach($tender->listOfTendererInformation->selectedContractors as $contractor)
            {
                if( ! is_null($contractor->pivot->deleted_at) ) continue;

                array_push($lotSelectedContractorIds, $contractor->id);
            }

            $lotDuplicateCompanyPersonnelsByCompany  = CompanyPersonnel::getDuplicateCompanyPersonnelsGroupByCompany($lotSelectedContractorIds);
            $lotDuplicateCompanyPersonnelsCompanyIds = array_column($lotDuplicateCompanyPersonnelsByCompany, 'company_id');
        }

        $verifiers = [];

        if($user->getAssignedCompany($project))
        {
            $verifiers = $user->getAssignedCompany($project)->getVerifierList($project, true);
        }

        $publicTenderEnabled = GeneralSetting::first() ? GeneralSetting::first()->view_tenders : false;

        $data = compact(
            'user',
            'project',
            'tender',
            'isEditor',
            'verifiers',
            'tenderInterviewInformation',
            'contractLimits',
            'hasTechnicalEvaluationTemplate',
            'setReference',
            'completionPeriodMetricOptions',
            'procurementMethodOptions',
            'isVendorManagementEnabled',
            'selectContractorModalId',
            'saveSelectedContractorRoute',
            'rotDuplicateCompanyPersonnelsCompanyIds',
            'lotDuplicateCompanyPersonnelsCompanyIds',
            'publicTenderEnabled'
        );

        $route                         = 'tenders.show';
        $useTopManagementVerifierRoute = false;

        if($tender->getTenderStageInformation() && $tender->getTenderStageInformation()->isBeingValidated() && $user->isTopManagementVerifier())
        {
            switch($tender->getTenderStage())
            {
                case TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER:
                    if(is_null($user->getAssignedCompany($project)) || $user->hasCompanyProjectRole($project, Role::GROUP_CONTRACT))
                    {
                        $useTopManagementVerifierRoute = true;
                    }
                    break;
                case TenderStages::TENDER_STAGE_LIST_OF_TENDERER:
                    if( ! $user->hasCompanyProjectRole($project, TenderFilters::getListOfTendererFormRole($project)) )
                    {
                        $useTopManagementVerifierRoute = true;
                    }
                    break;
                case TenderStages::TENDER_STAGE_CALLING_TENDER:
                    if( ! $user->hasCompanyProjectRole($project, $project->getCallingTenderRole()) )
                    {
                        $useTopManagementVerifierRoute = true;
                    }
                    break;
            }
        }

        if($useTopManagementVerifierRoute)
        {
            $route = 'tenders.top_verifier_show';

            unset($data['verifiers'], $data['tenderInterviewInformation'], $data['saveSelectedContractorRoute']);
        }

        return View::make($route, $data);
    }


    public function getListOfContractors(Project $project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        $selectedContractorIds = $tender->getTenderStageInformation()->selectedContractors->lists('id');

        $additionalClause = null;

        if( ! empty($selectedContractorIds) )
        {
            $additionalClause = " AND c.id NOT IN (" . implode(', ', $selectedContractorIds) . ")";
        }

        $query = "SELECT c.id, c.name, cgc.name AS vendor_group, 
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT wc.name) FILTER (WHERE wc.name IS NOT NULL)) AS work_categories, 
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT ws.name) FILTER (WHERE ws.name IS NOT NULL)) AS work_sub_categories, 
                  TRIM(ctry.country) AS country, TRIM(s.name) AS state 
                  FROM companies c 
                  INNER JOIN countries ctry ON ctry.id = c.country_id 
                  INNER JOIN states s ON s.id = c.state_id 
                  INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                  INNER JOIN contract_group_contract_group_category cgcgc ON cgcgc.contract_group_category_id = cgc.id 
                  INNER JOIN contract_groups cg ON cg.id = cgcgc.contract_group_id 
                  LEFT OUTER JOIN contractors con ON con.company_id = c.id 
                  LEFT OUTER JOIN contractor_work_category cwc ON cwc.contractor_id = con.id 
                  LEFT OUTER JOIN work_categories wc ON wc.id = cwc.work_category_id 
                  LEFT OUTER JOIN contractor_work_subcategory cws ON cws.contractor_id = con.id 
                  LEFT OUTER JOIN work_subcategories ws ON ws.id = cws.work_subcategory_id 
                  WHERE cg.group = " . Role::CONTRACTOR . " 
                  AND c.confirmed IS TRUE 
                  {$additionalClause} 
                  GROUP BY c.id, cgc.id, ctry.id, s.id 
                  ORDER BY c.name ASC;";

        $data = [];

        foreach(DB::select(DB::raw($query)) as $record)
        {
            array_push($data, [
                'id'                  => $record->id,
                'name'                => $record->name,
                'vendor_group'        => $record->vendor_group,
                'work_categories'     => is_null($record->work_categories) ? [] : json_decode($record->work_categories),
                'work_sub_categories' => is_null($record->work_sub_categories) ? [] : json_decode($record->work_sub_categories),
                'country'             => $record->country,
                'state'               => $record->state,
            ]);
        }

        return Response::json($data);
    }

    public function getListOfVMContractors(Project $project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        $selectedContractorIds = $tender->getTenderStageInformation()->selectedContractors->lists('id');

        $additionalClause = null;

        if( ! empty($selectedContractorIds) )
        {
            $additionalClause = " AND c.id NOT IN (" . implode(', ', $selectedContractorIds) . ")";
        }

        $query = "SELECT c.id, c.name, cgc.name AS vendor_group, 
                  CASE 
                      WHEN c.deactivated_at IS NOT NULL THEN '" . trans('general.deactivated') . "'  
                      WHEN c.expiry_date IS NOT NULL AND c.expiry_date < NOW() AND c.deactivation_date IS NULL THEN '" . trans('general.expired') . "' 
                      WHEN c.activation_date IS NOT NULL THEN '" . trans('general.active') . "' 
                      ELSE '" . trans('general.draft') . "' 
                  END AS vendor_status, 
                  c.company_status AS company_status, 
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT vc.id) FILTER (WHERE vc.id IS NOT NULL)) AS vendor_category_ids, 
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT vc.name) FILTER (WHERE vc.name IS NOT NULL)) AS vendor_categories, 
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT vwc.id) FILTER (WHERE vwc.id IS NOT NULL)) AS vendor_work_category_ids, 
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT vwc.name) FILTER (WHERE vwc.name IS NOT NULL)) AS vendor_work_categories, 
                  TRIM(ctry.country) AS country, TRIM(s.name) AS state 
                  FROM companies c 
                  INNER JOIN countries ctry ON ctry.id = c.country_id 
                  INNER JOIN states s ON s.id = c.state_id 
                  INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                  INNER JOIN contract_group_contract_group_category cgcgc ON cgcgc.contract_group_category_id = cgc.id 
                  INNER JOIN contract_groups cg ON cg.id = cgcgc.contract_group_id 
                  LEFT OUTER JOIN vendors v ON v.company_id = c.id 
                  LEFT OUTER JOIN vendor_work_categories vwc ON vwc.id = v.vendor_work_category_id AND vwc.hidden IS FALSE 
                  LEFT OUTER JOIN vendor_category_vendor_work_category vcvwc ON vcvwc.vendor_work_category_id = vwc.id 
                  LEFT OUTER JOIN company_vendor_category cvc ON cvc.company_id = c.id AND cvc.vendor_category_id = vcvwc.vendor_category_id 
                  LEFT OUTER JOIN vendor_categories vc ON vc.id = cvc.vendor_category_id 
                  LEFT OUTER JOIN vendor_evaluation_cycle_scores vecs ON vecs.id = v.vendor_evaluation_cycle_score_id 
                  WHERE cg.group = " . Role::CONTRACTOR . "
                  AND c.confirmed IS TRUE 
                  AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . " 
                  AND cgc.hidden IS FALSE 
                  {$additionalClause} 
                  GROUP BY c.id, cgc.id, ctry.id, s.id 
                  ORDER BY c.name ASC;";

        $records = DB::select(DB::raw($query));

        $companyIds = array_column($records, 'id');

        $trackRecordProjectVendorWorkSubCategories = Vendor::getTrackRecordProjectVendorWorkSubCategories($companyIds);

        $watchListVendorWorkCategories = Vendor::select('vendor_work_categories.id', 'vendor_work_categories.name', 'vendors.company_id')
            ->join('companies', 'companies.id', '=', 'vendors.company_id')
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
            ->whereIn('vendors.company_id', $companyIds)
            ->where('vendors.type', '=', Vendor::TYPE_WATCH_LIST)
            ->orderBy('vendor_work_categories.name')
            ->get()
            ->groupBy('company_id');

        $companyWatchListVendorWorkCategories = [];

        foreach($watchListVendorWorkCategories as $companyId => $vendorWorkCategories)
        {
            $companyWatchListVendorWorkCategories[$companyId] = [];

            foreach($vendorWorkCategories as $vendorWorkCategory)
            {
                $companyWatchListVendorWorkCategories[$companyId][$vendorWorkCategory->id] = $vendorWorkCategory->name;
            }
        }

        $data = [];

        foreach($records as $record)
        {
            $vendorWorkCategoryIds = is_null($record->vendor_work_category_ids) ? [] : json_decode($record->vendor_work_category_ids);

            $companyVendorSubWorkCategories = [];

            foreach($vendorWorkCategoryIds as $vendorWorkCategoryId)
            {
                $vendorWorkSubCategories = $trackRecordProjectVendorWorkSubCategories[$record->id][$vendorWorkCategoryId]['names'];

                if(is_null($vendorWorkSubCategories)) continue;

                foreach($vendorWorkSubCategories as $category)
                {
                    array_push($companyVendorSubWorkCategories, $category);
                }
            }

            array_push($data, [
                'id'                         => $record->id,
                'name'                       => $record->name,
                'vendor_group'               => $record->vendor_group,
                'vendor_status'              => $record->vendor_status,
                'company_status'             => is_null($record->company_status) ? null : Company::getCompanyStatusDescriptions($record->company_status),
                'vendor_categories'          => is_null($record->vendor_categories) ? null : implode(', ', json_decode($record->vendor_categories)),
                'vendor_work_categories'     => is_null($record->vendor_work_categories) ? null : implode(', ', json_decode($record->vendor_work_categories)),
                'vendor_sub_work_categories' => empty($companyVendorSubWorkCategories) ? null : implode(', ', $companyVendorSubWorkCategories),
                'watch_list_categories'      => $companyWatchListVendorWorkCategories[$record->id] ?? [],
                'country'                    => $record->country,
                'state'                      => $record->state,
            ]);
        }

        return Response::json($data);
    }

    public function getCompanyDuplicateCompanyPersonnels($project, $tenderId, $companyId)
    {
        $tender  = Tender::find($tenderId);
        $company = Company::find($companyId);

        $selectedContractorIds = $tender->getTenderStageInformation()->selectedContractors->lists('id');

        $duplicateCompanyPersonnelsByCompany = CompanyPersonnel::getDuplicateCompanyPersonnelsGroupByCompany($selectedContractorIds, true);

        $companyPersonnelIds = array_column($duplicateCompanyPersonnelsByCompany, 'company_personnel_id');

        $query = "SELECT c.id AS company_id, c.name AS company, cp.id, cp.name, cp.identification_number, cp.type 
                  FROM company_personnel cp 
                  INNER JOIN vendor_registrations vr ON cp.vendor_registration_id = vr.id
                  INNER JOIN companies c ON c.id = vr.company_id 
                  WHERE cp.id IN(" . implode(', ', $companyPersonnelIds) . ")
                  AND c.id <> {$companyId}
                  ORDER BY c.id ASC, cp.type ASC";

        $data = [];

        foreach(DB::select(DB::raw($query)) as $record)
        {
            array_push($data, [
                'name'                  => $record->name,
                'identification_number' => $record->identification_number,
                'type'                  => CompanyPersonnel::getCompanyPersonnelTypeDescription($record->type),
                'company'               => $record->company,
            ]);
        }

        return Response::json($data);
    }

    /**
     * Updates the Recommendation of Tenderer Information.
     * Save, submit, reject, confirm.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Laracasts\Validation\FormValidationException
     */
    public function updateROTInformation(Project $project, $tenderId)
    {
        $inputs = Input::all();
        $user   = \Confide::user();

        try
        {
            $this->tenderROTInformationForm->setParameters($project);
            $this->tenderROTInformationForm->validate($inputs);
        }
        catch(Laracasts\Validation\FormValidationException $e)
        {
            \Flash::error('Form Validation Error.');
            return Redirect::to(URL::previous())
            ->withErrors($e->getErrors())
            ->withInput();
        }
        catch(\PCK\Exceptions\ValidationException $e)
        {
            \Flash::error('Form Validation Error.');
            return Redirect::to(URL::previous())
            ->withErrors($e->getErrors())
            ->withInput();
        }
        
        $inputs['proposed_date_of_calling_tender'] = $project->getAppTimeZoneTime($inputs['proposed_date_of_calling_tender'] ?? null);
        $inputs['proposed_date_of_closing_tender'] = $project->getAppTimeZoneTime($inputs['proposed_date_of_closing_tender'] ?? null);
        $inputs['target_date_of_site_possession']  = $project->getAppTimeZoneTime($inputs['target_date_of_site_possession'] ?? null);
        $inputs['technical_tender_closing_date']   = $project->getAppTimeZoneTime($inputs['technical_tender_closing_date'] ?? null);

        $tender = $this->tenderRepo->find($project, $tenderId);

        DB::transaction(function() use ($project, $tender, $inputs)
        {
            $rotInformation = $this->tenderROTInformationRepo->saveROTInformation($tender, $inputs);

            if( $rotInformation->stillInProgress() )
            {
                $this->tenderROTInformationRepo->syncSelectedContractorCommitmentStatus($rotInformation, $inputs);
            }
            elseif( $rotInformation->isSubmitted() )
            {
                $role = array( TenderFilters::getListOfTendererFormRole($project) );

                // invalidate all pending emails' tokens
                $this->invalidatePendingExpressionOfInterestTokens($rotInformation->id, TenderRecommendationOfTendererInformation::class);

                // will send to all editors
                $this->tenderROTInformationRepo->sendEmailNotificationToEditors($tender, 'notifications.email.tender_rec_of_tenderer_submitted_email');

                $users = $this->userRepo->getProjectSelectedUsersByProjectAndRoles($project, array( $role ));

                $this->tenderROTInformationRepo->sendVerifierSystemNotification($tender, $users->toArray(), 'tender_rec_of_tenderer_submitted', 'projects.tender.show');

                $this->tenderLOTInformationRepo->cloneInformationToListOfTenderer($rotInformation);

                \Event::fire('system.updateProjectStatus', array( $project, Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER ));
                \Event::fire('system.updateTenderFormStatus', array( $tender, Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER ));
            }
        });

        $redirectUrl = (URL::previous() . '#' . TenderRecommendationOfTendererInformationForm::TAB_ID);

        if((array_key_exists('verification_confirm', $inputs) || array_key_exists('verification_reject', $inputs)) && is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier())
        {
            $redirectUrl = route('home.index');
        }

        \Flash::success('Successfully saved Rec. of Tenderer Information !');

        return Redirect::to($redirectUrl);
    }

    public function updateROTBudget(Project $project, $tenderId)
    {
        $inputs = Input::all();

        $tender = $this->tenderRepo->find($project, $tenderId);
        $budget = (array_key_exists('budget', $inputs)) ? str_replace(",","", $inputs['budget']) : 0;
        $budget = (float)$budget;

        if($budget == 0)
        {
            \Flash::error('Budget cannot be set as zero!');

            return Redirect::back();
        }

        $rot = $tender->recommendationOfTendererInformation;
        $rot->budget = $budget;

        $rot->save();

         \Flash::success('Successfully saved Rec. of Tenderer Budget!');

        return Redirect::back();
    }
    /**
     * Get data using AJAX and updates selected company for ROT information.
     * Does NOT return a view, the ajax call handles the redirects/reloads.
     *
     * @param $project
     * @param $tenderId
     *
     * @return string
     */
    public function syncROTSelectedContractors($project, $tenderId)
    {
        $contractors = array();

        foreach(Input::get('checkedContractors') as $contractorId)
        {
            $contractors[] = $contractorId;
        }

        $tender = $this->tenderRepo->find($project, $tenderId);

        $this->tenderROTInformationRepo->syncSelectedCompanyForROTInformation($tender->recommendationOfTendererInformation, array( 'contractors' => $contractors ));

        \Flash::success('Successfully updated Rec. of Tenderer Selected Contractor(s) List !');

        return TenderRecommendationOfTendererInformationForm::TAB_ID;
    }

    /**
     * Deletes a contractor from the Recommendation of Tenderer.
     *
     * @param $project
     * @param $tenderId
     * @param $contractorId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteROTContractor($project, $tenderId, $contractorId)
    {
        $tender = $this->tenderRepo->find($project, $tenderId);
        $tenderStageId = $tender->recommendationOfTendererInformation->id;
        $tenderStageType = TenderRecommendationOfTendererInformation::class;

        $this->tenderROTInformationRepo->deleteROTContractor($tender, $contractorId);

        $this->deleteExpressionOfInterestTokens($tenderStageId, $tenderStageType, $contractorId);

        \Flash::success('Successfully updated Rec. of Tenderer Selected Contractor(s) List !');

        return Redirect::to(URL::previous() . '#' . TenderRecommendationOfTendererInformationForm::TAB_ID);
    }

    public function showOpenTender(Project $project, $tenderId, $form)
    {
        $user           = \Confide::user();
        $tender         = $this->tenderRepo->find($project, $tenderId);
        $checklistItems = $project->getProgressChecklist($project->tenders->first());

        foreach($checklistItems as $item)
        {       
            if($item["description"] == '"Calling Tender" form submitted' || $item["description"] == '"List of Tenderer" form submitted')
            {
                continue;
            }     

            if(!$item['checked'])
            {
                Flash::error("Please complete the progress checklist before proceeding with open tender mode");
                return Redirect::to(route('projects.tender.show', array( $tender->project->id, $tender->id )) . '#s2');            
            }
        }

		$personInChargeRecords = OpenTenderPersonInCharge::where("tender_id",$tenderId)->orderBy("id", "ASC")->get();
        $announcementRecords   = OpenTenderAnnouncement::where("tender_id",$tenderId)->orderBy("id", "ASC")->get();
        $documentRecords       = OpenTenderTenderDocument::where("tender_id",$tenderId)->orderBy("id", "ASC")->get();
        $industryCodeRecords   = OpenTenderIndustryCode::where("tender_id",$tenderId)->orderBy("id", "ASC")->get();

        foreach($documentRecords as $documentRecord)
        {
            if($this->getAttachmentDetails($documentRecord))
            {
                $documentRecord->attachmentsCount = $this->getAttachmentDetails($documentRecord)->count();
            }
            else
            {
                $documentRecord->attachmentsCount = 0;
            }
        }
	
        $subsidiary = $project->subsidiary;
        $rootSubsidiary = $subsidiary->getTopParentSubsidiary('root')->name;

        $openTenderInfo        = OpenTenderPageInformation::where("tender_id", $tenderId)->first();
        $openTenderRequirement = OpenTenderTenderRequirement::where("tender_id", $tenderId)->first();

        if(!$project->open_tender)
        {
            $tender = $this->updateTenderStatusToCallingTender($project,$tender);
        }

        $tender->listOfTendererInformation->update(["status" => TenderListOfTendererInformation::SUBMISSION]);

        $isCurrentVerifier	= false;
        $verifierLogs       = [];
        $isVerified         = false;
        $approvalStatus     = OpenTenderPageInformation::STATUS_OPEN;
        $disabled           = false;

        $verifiers = [];
        
        if($openTenderInfo)
        {
            $approvalStatus     = $openTenderInfo->status;
            $disabled           = ($approvalStatus == OpenTenderPageInformation::STATUS_PENDING_FOR_APPROVAL) ? true : false;

            $isCurrentVerifier	= Verifier::isCurrentVerifier(\Confide::user(), $openTenderInfo);
            $verifierLogs       = Verifier::getAssignedVerifierRecords($openTenderInfo, true);

            if($openTenderInfo->status == OpenTenderPageInformation::STATUS_APPROVED || $openTenderInfo->status == OpenTenderPageInformation::STATUS_REJECT)
            {
                $isVerified = true;
            }

            if($user->getAssignedCompany($project))
            {
                $verifiers = $user->getAssignedCompany($project)->getVerifierList($project, true);
            }
        }
        
        $data = compact(
            'project',
            'tender',
            'user',
            'rootSubsidiary',
            'openTenderInfo',
            'openTenderRequirement',
            'personInChargeRecords',
            'announcementRecords',
            'documentRecords',
            'industryCodeRecords',
            'tenderId',
            'form',
            'verifiers',
            'isCurrentVerifier',
            'verifierLogs',
            'isVerified',
            'approvalStatus',
            'disabled'
        );
        
        return View::make("tenders.open_tender", $data);
    }

    public function updateTenderStatusToCallingTender($project,$tender)
    {
        $project->open_tender           = true; // set project as open tender mode
        $project->status_id             = Project::STATUS_TYPE_CALLING_TENDER;
        $project->current_tender_status = Project::STATUS_TYPE_CALLING_TENDER;
        $project->save();

        $tender->current_form_type = Project::STATUS_TYPE_CALLING_TENDER;
        $tender->save();

        $tender->listOfTendererInformation->update(["status" => TenderListOfTendererInformation::SUBMISSION]);

        if($tender->listOfTendererInformation->allowCopyTechnicalEvaluationSetReferences())
        {
            $this->technicalEvaluationSetReferenceRepository->copy($project, $tender->listOfTendererInformation->contractLimit);
        }

        $this->tenderCallingTenderInformationRepo->cloneInformationToCallingTender($tender->listOfTendererInformation);

        if($tender->callingTenderInformation->stillInProgress())
        {
            $this->tenderRepo->updateTenderStartAndClosingDate($tender, $tender->callingTenderInformation);
            $tender->callingTenderInformation->update(["status" => TenderListOfTendererInformation::SUBMISSION]);
        }

        return $tender;
    }

    public function updateOpenTenderInfoPage(Project $project, $tenderId)
    {
        $inputs = Input::all();     

        $inputs["tender_id"] = $tenderId;
        $inputs["project_id"] = $project->id;
        $inputs["created_by"] = \Confide::user()->id;

        $inputs = OpenTenderPageInformation::processInput($inputs);

        $inputs["special_permission"] = isset($inputs["special_permission"]) ? true : false;
        $inputs["local_company_only"] = isset($inputs["local_company_only"]) ? true : false;

        $tender = $this->tenderRepo->find($project, $tenderId);

        if($tender->listOfTendererInformation->isSubmitted())
        {
            $tender->listOfTendererInformation->date_of_calling_tender = $inputs["calling_date"];
            $tender->listOfTendererInformation->date_of_closing_tender = $inputs["closing_date"];
            $tender->listOfTendererInformation->save();

            $tender->callingTenderInformation->date_of_calling_tender = $inputs["calling_date"];
            $tender->callingTenderInformation->date_of_closing_tender = $inputs["closing_date"];
            $tender->callingTenderInformation->save();
        }

        $openTenderInfo = OpenTenderPageInformation::where("tender_id", $tenderId)->first();

        try{
            if($openTenderInfo)
            {
                OpenTenderPageInformation::where("tender_id", $tenderId)->update($inputs);
            }
            else
            {
                OpenTenderPageInformation::create($inputs);
            }

        }catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }
        
        Flash::success("Form is saved successfully!");

		return Redirect::route('projects.tender.open_tender.get', array($project->id, $tenderId, "tenderInfo"));
    }

    public function updateOpenTenderRequirements(Project $project, $tenderId)
    {
        $inputs = Input::all();     

        $inputs["tender_id"] = $tenderId;
        $inputs["created_by"] = \Confide::user()->id;

        $inputs = Helpers::processInput($inputs);

        $tender = $this->tenderRepo->find($project, $tenderId);


        $openTenderRequirement = OpenTenderTenderRequirement::where("tender_id", $tenderId)->first();

        try{
            if($openTenderRequirement)
            {
                OpenTenderTenderRequirement::where("tender_id", $tenderId)->update($inputs);
            }
            else
            {
                OpenTenderTenderRequirement::create($inputs);
            }

        }catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }
        
        Flash::success("Form is saved successfully!");

		return Redirect::route('projects.tender.open_tender.get', array($project->id, $tenderId, "tenderRequirements"));

    }

    public function approveOpenTenderInfoPage(Project $project, $tenderId, $id)
    {
		$input = Input::all();

		$openTenderInfo = OpenTenderPageInformation::find($id);

		if(isset($input["verifiers"]))
		{
			$this->submitForApproval($openTenderInfo,$input);
		}

		Flash::success("Form is submitted for approval!");

		return Redirect::route('projects.tender.open_tender.get', array($project->id, $tenderId, "tenderInfo"));
	}

    public function submitForApproval(OpenTenderPageInformation $openTenderInfo, $input)
    {
        $verifiers = array_filter($input['verifiers'], function($value)
        {
            return $value != "";
        });

        $openTenderInfo->submitted_for_approval_by = \Confide::user()->id;

        if( empty( $verifiers ) )
        {
            $openTenderInfo->status = OpenTenderPageInformation::STATUS_APPROVED;
            $openTenderInfo->save();

			Verifier::setVerifierAsApproved(\Confide::user(), $openTenderInfo);
        }
        else
        {
            Verifier::setVerifiers($verifiers, $openTenderInfo);

            $openTenderInfo->status = OpenTenderPageInformation::STATUS_PENDING_FOR_APPROVAL;
            $openTenderInfo->save();

            Verifier::sendPendingNotification($openTenderInfo);
        }
    }

    public function sendEmailNotificationToPendingContractorsForPayment(Project $project, $tenderId) {

        $tender = $this->tenderRepo->find($project, $tenderId);

        foreach($tender->listOfTendererInformation->selectedContractors as $contractor)
        {
            if($contractor->pivot->status === ContractorCommitmentStatus::PENDING)
            {
                foreach($contractor->companyAdmins as $admin)
                {
                    $this->emailNotifier->sendEmailNotificationToPendingContractorsForPayment($tender, 'notifications.email.tender_list_of_tenderer_request_for_payment_email', $admin);
                }
            }
        }

        return $tender->listOfTendererInformation->selectedContractors;
    }

    // Register interest
    public function insertContractorIntoListOfTendererAsPending()
    {
        $input   = Input::all();

        $project = Project::find($input["project_id"]);
        $tender  = $this->tenderRepo->find($project, $input["tender_id"]);

        try{
            $selectedContractors = $tender->listOfTendererInformation->selectedContractors;
            $selectedContractor = $selectedContractors->find($input["company_id"]);
            if (! $selectedContractor)
            {   // Not in list
                $tender->listOfTendererInformation->selectedContractors()->attach($input["company_id"], array(
                    'added_by_gcd' => false,
                    'status' => ContractorCommitmentStatus::PENDING
                ));
            } else {    // Already in list
                \Flash::warning(trans('projectOpenTenderBM.interestToTenderDuplicate'));
            }
        }catch (Exception $e)
        {
            return Response::json([
                'success' => false,
                'error'   => $e->getMessage()
            ]);
        }

        \Flash::success(trans('projectOpenTenderBM.interestToTenderSuccess'));

        return Response::json([
            'success' => true,
            'data' => $tender
        ]);
    }
    

    // Add contractor to the list of tenderer (After payment has been made)
    public function insertContractorIntoTenderDetails()
    {
        $input   = Input::all();

        $project = Project::find($input["project_id"]);
        $tender  = $this->tenderRepo->find($project, $input["tender_id"]);

        try{
            $contractor = $tender->listOfTendererInformation->selectedContractors()->where("company_id", $input["company_id"])->first();

            if($contractor)
            {
                $tender->listOfTendererInformation->selectedContractors()->detach($input["company_id"]);    
                $tender->callingTenderInformation->selectedContractors()->detach($input["company_id"]);      
            }
            
            $tender->listOfTendererInformation->selectedContractors()->attach($input["company_id"], array( 
                'added_by_gcd' => false,
                'status' => ContractorCommitmentStatus::OK
            ));
            
            $tender->callingTenderInformation->selectedContractors()->attach($input["company_id"], ['status' => ContractorCommitmentStatus::OK]);

        }catch (Exception $e)
        {
            return Response::json([
                'success' => false,
                'error'   => $e->getMessage()
            ]);
        }

        return Response::json([
            'success' => true,
            'data' => $tender
        ]);
    }

    /**
     * Updates the List Of Tenderer Information.
     * Save, submit, reject, confirm.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLOTInformation($project, $tenderId)
    {
        $inputs = Input::all();
        $user   = \Confide::user();

        $inputs['technical_tender_closing_date'] = $project->getAppTimeZoneTime($inputs['technical_tender_closing_date'] ?? null);
        $inputs['date_of_calling_tender']        = $project->getAppTimeZoneTime($inputs['date_of_calling_tender'] ?? null);
        $inputs['date_of_closing_tender']        = $project->getAppTimeZoneTime($inputs['date_of_closing_tender'] ?? null);

        $tender = $this->tenderRepo->find($project, $tenderId);

        if( $tender->listOfTendererInformation->stillInProgress() )
        {
            // This is just to redirect the user back to the page AND the correct tab.
            try
            {
                $this->tenderLOTInformationForm->setParameters($project);
                $this->tenderLOTInformationForm->validate($inputs);
            }
            catch(Laracasts\Validation\FormValidationException $e)
            {
                \Flash::error('Form Validation Error.');
                return Redirect::to(URL::previous() . '#' . TenderListOfTendererInformationForm::TAB_ID)
                    ->withErrors($e->getErrors())
                    ->withInput();
            }
            catch(\PCK\Exceptions\ValidationException $e)
            {
                \Flash::error('Form Validation Error.');
                return Redirect::to(URL::previous() . '#' . TenderListOfTendererInformationForm::TAB_ID)
                    ->withErrors($e->getErrors())
                    ->withInput();
            }
        }

        DB::transaction(function() use ($project, $tender, $inputs)
        {
            $lotInformation = $this->tenderLOTInformationRepo->saveLOTInformation($tender, $inputs);

            $this->tenderLOTInformationRepo->syncSelectedContractorCommitmentStatus($lotInformation, $inputs);

            if( $lotInformation->stillInProgress() )
            {
                $this->tenderLOTInformationRepo->syncSelectedContractorRemark($lotInformation, $inputs);
            }
            elseif( $lotInformation->isSubmitted() )
            {
                $role = array( $project->getCallingTenderRole() );

                // invalidate all pending emails' tokens
                $this->invalidatePendingExpressionOfInterestTokens($lotInformation->id, TenderListOfTendererInformation::class);

                // Technical Evaluation.
                if($lotInformation->allowCopyTechnicalEvaluationSetReferences())
                {
                    $this->technicalEvaluationSetReferenceRepository->copy($project, $lotInformation->contractLimit);
                }

                // will send to all editors
                $this->tenderLOTInformationRepo->sendEmailNotificationToEditors($tender, 'notifications.email.tender_list_of_tenderer_submitted_email');

                $users = $this->userRepo->getProjectSelectedUsersByProjectAndRoles($project, array( $role ));

                $this->tenderLOTInformationRepo->sendVerifierSystemNotification($tender, $users->toArray(), 'tender_list_of_tenderer_submitted', 'projects.tender.show', '#s2');

                $this->tenderCallingTenderInformationRepo->cloneInformationToCallingTender($lotInformation);

                \Event::fire('system.updateProjectStatus', array( $project, Project::STATUS_TYPE_LIST_OF_TENDERER ));
                \Event::fire('system.updateTenderFormStatus', array( $tender, Project::STATUS_TYPE_LIST_OF_TENDERER ));
            }
        });

        $redirectUrl = (URL::previous() . '#' . TenderListOfTendererInformationForm::TAB_ID);

        if((array_key_exists('verification_confirm', $inputs) || array_key_exists('verification_reject', $inputs)) && $user->isTopManagementVerifier())
        {
            $redirectUrl = route('home.index');
        }

        \Flash::success('Successfully saved List of Tenderer Information!');

        return Redirect::to($redirectUrl);
    }

    /**
     * Get data using AJAX and updates selected company for LOT information
     * Does NOT return a view, the ajax call handles the redirects/reloads
     *
     * @param $project
     * @param $tenderId
     *
     * @return string
     */
    public function syncLOTSelectedContractors($project, $tenderId)
    {
        $contractors = array();

        foreach(Input::get('checkedContractors') as $contractorId)
        {
            $contractors[] = $contractorId;
        }

        $tender = $this->tenderRepo->find($project, $tenderId);

        $this->tenderLOTInformationRepo->syncSelectedCompanyForLOTInformation($tender->listOfTendererInformation, array( 'contractors' => $contractors ));

        \Flash::success('Successfully updated List of Tenderer Selected Contractor(s) List !');

        return TenderListOfTendererInformationForm::TAB_ID;
    }

    /**
     * Deletes a contractor from the List of Tenderers.
     *
     * @param $project
     * @param $tenderId
     * @param $contractorId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteLOTContractor($project, $tenderId, $contractorId)
    {
        $tender = $this->tenderRepo->find($project, $tenderId);
        $tenderStageId = $tender->listOfTendererInformation->id;
        $tenderStageType = TenderListOfTendererInformation::class;

        $this->tenderLOTInformationRepo->deleteLOTContractor($tender, $contractorId);

        $this->deleteExpressionOfInterestTokens($tenderStageId, $tenderStageType, $contractorId);

        \Flash::success('Successfully updated List of Tenderer Selected Contractor(s) List !');

        return Redirect::to(URL::previous() . '#' . TenderListOfTendererInformationForm::TAB_ID);
    }

    /**
     * Re-enables a previously deleted contractor form the List of Tenderers.
     *
     * @param $project
     * @param $tenderId
     * @param $contractorId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reEnableLOTContractor($project, $tenderId, $contractorId)
    {
        $tender = $this->tenderRepo->find($project, $tenderId);

        $this->tenderLOTInformationRepo->reEnableLOTContractor($tender, $contractorId);

        \Flash::success('Successfully updated List of Tenderer Selected Contractor(s) List !');

        return Redirect::to(URL::previous() . '#' . TenderListOfTendererInformationForm::TAB_ID);
    }

    /**
     * Updates the Calling Tender Information.
     * Save, submit, reject, confirm.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCallingTenderInformation($project, $tenderId)
    {
        $inputs = Input::all();
        $user   = \Confide::user();

        $inputs['technical_tender_closing_date'] = $project->getAppTimeZoneTime($inputs['technical_tender_closing_date'] ?? null);
        $inputs['date_of_calling_tender']        = $project->getAppTimeZoneTime($inputs['date_of_calling_tender'] ?? null);
        $inputs['date_of_closing_tender']        = $project->getAppTimeZoneTime($inputs['date_of_closing_tender'] ?? null);

        $tender = $this->tenderRepo->find($project, $tenderId);

        if( $tender->callingTenderInformation->stillInProgress() OR isset( $inputs['dates_extension'] ) )
        {
            try
            {
                $this->tenderCallingTenderInformationForm->setParameters($tender);
                $this->tenderCallingTenderInformationForm->validate($inputs);
            }
            catch(Laracasts\Validation\FormValidationException $e)
            {
                return Redirect::to(URL::previous() . '#' . TenderCallingTenderInformationForm::TAB_ID)
                    ->withErrors($e->getErrors())
                    ->withInput();
            }
            catch(\PCK\Exceptions\ValidationException $e)
            {
                Flash::error($e->getErrors()->first());

                return Redirect::to(URL::previous() . '#' . TenderCallingTenderInformationForm::TAB_ID)
                    ->withErrors($e->getErrors())
                    ->withInput();
            }
        }

        DB::transaction(function() use ($project, $tender, $inputs)
        {
            $previousStatus = $tender->callingTenderInformation->getOriginal('status');

            $callingTenderInformation = $this->tenderCallingTenderInformationRepo->saveCallingTenderInformation($tender, $inputs);

            if( ($previousStatus !== TenderCallingTenderInformation::SUBMISSION && $callingTenderInformation->stillInProgress()) || $tender->callingTenderInformation->allowEditableContractorStatus(\Confide::user()) )
            {
                $this->tenderCallingTenderInformationRepo->syncSelectedContractorStatus($callingTenderInformation, $inputs);
            }

            $roles = array( Role::PROJECT_OWNER, TenderFilters::getListOfTendererFormRole($project) );

            if( $callingTenderInformation->extendingDateAllowed() )
            {
                $this->tenderRepo->updateTenderStartAndClosingDate($tender, $callingTenderInformation, $roles);

                $this->tenderRepo->cloneSelectedFinalContractors($tender, $callingTenderInformation);

                // will revert back Project's status to Calling Tender once the date has been extended
                \Event::fire('system.updateProjectStatus', array( $project, $tender->hasClosed() ? Project::STATUS_TYPE_CLOSED_TENDER : Project::STATUS_TYPE_CALLING_TENDER ));
                \Event::fire('system.updateTenderFormStatus', array( $tender, $tender->hasClosed() ? Project::STATUS_TYPE_CLOSED_TENDER : Project::STATUS_TYPE_CALLING_TENDER ));
                \Event::fire('system.updateTechnicalEvaluationStatus', array($tender));
            }
            // If previously (before form submission) was not submitted or being validated, and is currently submitted.
            elseif( ! in_array($previousStatus, array( TenderCallingTenderInformation::SUBMISSION, TenderCallingTenderInformation::EXTEND_DATE_VALIDATION_IN_PROGRESS )) AND $callingTenderInformation->isSubmitted() )
            {
                $this->tenderRepo->updateTenderStartAndClosingDate($tender, $callingTenderInformation);

                $this->tenderRepo->cloneSelectedFinalContractors($tender, $callingTenderInformation);

                \Event::fire('system.updateProjectStatus', array( $project, $tender->hasClosed() ? Project::STATUS_TYPE_CLOSED_TENDER : Project::STATUS_TYPE_CALLING_TENDER ));
                \Event::fire('system.updateTenderFormStatus', array( $tender, $tender->hasClosed() ? Project::STATUS_TYPE_CLOSED_TENDER : Project::STATUS_TYPE_CALLING_TENDER ));
                \Event::fire('system.updateTechnicalEvaluationStatus', array($tender));

                $tender->load('selectedFinalContractors');

                // will get the final selected contractor IDS and then search for it's admin user,
                // so that system can blast email notification to them
                $companyAdminUsers = $this->userRepo->getAdminUserByCompanyIds($tender->selectedFinalContractors->lists('id'));

                $this->tenderRepo->sendContractorAdminUserEmailNotification($tender, $companyAdminUsers->toArray(), 'calling_tender_success_inform_company_admin', 'projects.submitTender');

                // inform editor for Rec of Tenderer and List of Tenderer as well
                $this->emailNotifier->sendCallingTenderSubmittedNotification($tender);

                $users = $this->userRepo->getProjectSelectedUsersByProjectAndRoles($project, $roles);

                $this->tenderROTInformationRepo->sendVerifierSystemNotification($tender, $users->toArray(), 'calling_tender_submitted', 'projects.tender.show', '#s3');
            }
        });

        $redirectUrl = (URL::previous() . '#' . TenderCallingTenderInformationForm::TAB_ID);

        if((array_key_exists('verification_confirm', $inputs) || array_key_exists('verification_reject', $inputs)) && $user->isTopManagementVerifier())
        {
            $redirectUrl = route('home.index');
        }

        \Flash::success('Successfully saved Calling Tender Information !');

        return Redirect::to($redirectUrl);
    }

    /**
     * reminder button function call
     * sends email reminders and system notifications to current verifiers
     */
    public function sendTenderReminderEmail() {
        $tenderStage = Input::get('tenderStage');
        $tenderId = Input::get('tenderId');
        $tender = Tender::find($tenderId);

        if($tenderStage == TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER) {
            $tenderProcess = $tender->recommendationOfTendererInformation;
            $this->emailNotifier->sendROTTenderVerificationEmail($tender, $tenderProcess);
            $this->systemNotifier->sendTenderVerificationNotification($tender, $tenderProcess,  'recommendation_of_tenderer', '#s1');
        }

        if($tenderStage == TenderStages::TENDER_STAGE_LIST_OF_TENDERER) {
            $tenderProcess = $tender->listOfTendererInformation;
            $this->emailNotifier->sendLOTTenderVerificationEmail($tender, $tenderProcess);
            $this->systemNotifier->sendTenderVerificationNotification($tender, $tenderProcess, 'list_of_tenderer', '#s2');
        }

        if($tenderStage == TenderStages::TENDER_STAGE_CALLING_TENDER) {
            $tenderProcess = $tender->callingTenderInformation;
            $isExtend = $tender->callingTenderInformation->extendingDateInProgress();
            $viewName = $isExtend ? 'calling_tender_extend_dateline' : 'calling_tender';
            $this->emailNotifier->sendCTTenderVerificationEmail($tender, $tenderProcess, $viewName);
            $this->systemNotifier->sendTenderVerificationNotification($tender, $tenderProcess, $viewName, '#s3');
        }
    }

    /**
     * Sends email notifications to the selected contractors.
     *
     * @return array|null
     */
    public function sendTenderReminder()
    {
        $user         = Confide::user();
        $input        = Input::all();
        $tenderStage  = Input::get('tenderStage');
        $emailDetails = array(
            'emailMessage'   => Input::get('emailMessage'),
            'employerName'   => Input::get('employerName'),
            'sendCopyToSelf' => ( Input::get('sendCopyToSelf') == 'true' ) ? true : false,
        );

        $selectedContractors = array();

        if( Input::get('selectedContractors') != null )
        {
            $selectedContractors = Input::get('selectedContractors');
        }

        $projectId = $input['projectId'];
        $tenderId  = $input['tenderId'];

        $tender = \PCK\Tenders\Tender::find($tenderId);

        TenderReminder::saveDraft($tenderId, Input::get('emailMessage'));

        // get company admin of contractors, if any
        // if not, get companyId to tell user that the message could not be delivered
        $contractorCompanyAdminsId = array();
        $contractorsWithAdmin      = array();
        $contractorsWithoutAdmin   = array();

        foreach($selectedContractors as $companyId)
        {
            $company = Company::find($companyId);
            $companyAdmin = $company->companyAdmin;

            if( isset( $companyAdmin ) )
            {
                array_push($contractorCompanyAdminsId, $companyAdmin->id);
                array_push($contractorsWithAdmin, $this->companyRepo->find($companyId)->name);
            }
            else
            {
                array_push($contractorsWithoutAdmin, $this->companyRepo->find($companyId)->name);
            }
        }

        // for each and every selected company, get all the admins
        $allCompanyAdmins = $this->getAllCompanyAdmins($selectedContractors);

        if( $tenderStage == TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER )
        {
            $tenderStageId = $tender->recommendationOfTendererInformation->id;
            $tenderStageType = TenderRecommendationOfTendererInformation::class;

            foreach($selectedContractors as $contractor) {
                $this->deleteExpressionOfInterestTokens($tenderStageId, $tenderStageType, $contractor);
            }

            $this->tenderROTInformationRepo->sendEmailNotificationToSelectedContractors($allCompanyAdmins, $projectId, $tenderId, $emailDetails);
        }

        if( $tenderStage == TenderStages::TENDER_STAGE_LIST_OF_TENDERER )
        {
            $tenderStageId = $tender->listOfTendererInformation->id;
            $tenderStageType = TenderListOfTendererInformation::class;

            foreach($selectedContractors as $contractor) {
                $this->deleteExpressionOfInterestTokens($tenderStageId, $tenderStageType, $contractor);
            }

            $this->tenderLOTInformationRepo->sendEmailNotificationToSelectedContractors($allCompanyAdmins, $projectId, $tenderId, $emailDetails);
        }

        if( $tenderStage == TenderStages::TENDER_STAGE_CALLING_TENDER )
        {
            $this->tenderCallingTenderInformationRepo->sendEmailNotificationToSelectedContractors($contractorCompanyAdminsId, $projectId, $tenderId, $emailDetails);
        }

        SentTenderRemindersLog::log($user, $tender);

        return array(
            'contractorsWithAdmin'    => $contractorsWithAdmin,
            'contractorsWithoutAdmin' => $contractorsWithoutAdmin
        );
    }

    private function getAllCompanyAdmins(array $selectedContractors) {
        $allCompanyAdmins = array();

        foreach($selectedContractors as $companyId){
            $company       = Company::find($companyId);
            $companyAdmins = array();

            foreach($company->companyAdmins as $admin) {
                array_push($allCompanyAdmins, $admin);
            }
        }

        return $allCompanyAdmins;
    }

    /**
     * Saves the Tender Reminder Message as a draft.
     *
     * @param Project $project
     * @param         $tenderId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveTenderReminderDraft(Project $project, $tenderId)
    {
        $success = TenderReminder::saveDraft($tenderId, Input::get('message'));

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function saveTenderAcknowledgementLetterDraft(Project $project, $tenderId)
    {
        $inputs  = Input::all();
        $success = AcknowledgementLetter::saveDraft($tenderId, $inputs);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function checkEnableStatus(Project $project, $tenderId)
    {
        $tender                = Tender::find($tenderId);
        $acknowledgementLetter = $tender->acknowledgementLetter ? AcknowledgementLetter::find($tender->acknowledgementLetter->id) : null;

        $result = isset( $acknowledgementLetter ) ? $acknowledgementLetter->enable_letter : null;

        return Response::json(array(
            'result' => $result,
        ));
    }

    /**
     * Returns view of the unauthenticated form
     * for the email recipients to update the commitment status.
     *
     * @param $key
     *
     * @return \Illuminate\View\View
     */
    public function confirmStatus($key)
    {
        $listOptions = array();

        $expressionOfInterest = ExpressionOfInterestTokens::where('token', $key)->first();

        if( !$expressionOfInterest ) return View::make('errors/invalid_token');

        $user                     = Confide::user();
        $userLocale               = $user ? $user->settings->language->code : getenv('DEFAULT_LANGUAGE_CODE');
        $tenderStage = $expressionOfInterest->tenderstageable_type;

        if( $tenderStage === TenderRecommendationOfTendererInformation::class)
        {
            $listOptions = ContractorCommitmentStatus::getRecommendOfTendererDropDownListing($userLocale);
            unset($listOptions[ContractorCommitmentStatus::PENDING]);
        }
        else if ($tenderStage === TenderListOfTendererInformation::class)
        {
            $listOptions = ContractorCommitmentStatus::getListOfTendererDropDownListing($userLocale);
            unset($listOptions[ContractorCommitmentStatus::PENDING]);
        }
        else if ($tenderStage === TenderCallingTenderInformation::class)
        {
            $listOptions = ContractorCommitmentStatus::getCallingTenderDropDownListing($userLocale);
        }

        $languages                = Language::getLanguageListing();
        $translatedTextByLanguage = $this->getExpressionOfInterestTranslations(array_keys($languages));

        return View::make('unauthenticated_forms.confirmStatus', array(
            'listOptions'      => $listOptions,
            'route'            => 'contractors.confirmStatusSubmit',
            'project'          => $expressionOfInterest->tenderstageable->tender->project,
            'key'              => $key,
            'user'             => $user,
            'userLocale'       => $userLocale,
            'translatedText'   => json_encode($translatedTextByLanguage),
            'languages'        => $languages,
        ));
    }

    /**
     * Updates the contractor's commitment status
     *
     * @param $key
     *
     * @return int
     */
    public function confirmStatusSubmit($key)
    {
        $input   = Input::all();
        $success = false;

        $input['remarks'] = trim($input['remarks']);

        $this->companyConfirmStatusForm->validate($input);

        if( ! $this->companyConfirmStatusForm->success )
        {
            $errors = $this->companyConfirmStatusForm->getErrors();

            Flash::error(trans('tenders.remarksAreRequiredWhenRejecting'));

            return Redirect::back();
        }

        $expressionOfInterest = ExpressionOfInterestTokens::where('token', $key)->first();

        if( !$expressionOfInterest ) return View::make('errors/invalid_token');

        $tenderStage = $expressionOfInterest->tenderstageable_type;

        if( $tenderStage === TenderRecommendationOfTendererInformation::class)
        {
            $success  = $this->tenderROTInformationRepo->updateCompanyTenderROTInfoConfirmationStatusUnauthenticated($expressionOfInterest, $input, $key);
            $this->deleteExpressionOfInterestTokens($expressionOfInterest->tenderstageable_id, $expressionOfInterest->tenderstageable_type, $expressionOfInterest->company_id);
        }
        else if ($tenderStage === TenderListOfTendererInformation::class)
        {
            $success  = $this->tenderLOTInformationRepo->updateCompanyTenderLOTInfoConfirmationStatusUnauthenticated($expressionOfInterest, $input, $key);
            $this->deleteExpressionOfInterestTokens($expressionOfInterest->tenderstageable_id, $expressionOfInterest->tenderstageable_type, $expressionOfInterest->company_id);
        }
        else if ($tenderStage === TenderCallingTenderInformation::class)
        {
            $success  = $this->tenderCallingTenderInformationRepo->updateCompanyTenderCallingTenderInfoConfirmationStatusUnauthenticated($key, $input);
        }

        return View::make('unauthenticated_forms.statusConfirmed', array(
            'project'          => $expressionOfInterest->tenderstageable->tender->project,
            'success'          => $success,
            'translatedText'   => json_encode($this->getExpressionOfInterestTranslations(array($input['selectedLocale']))),
            'userLocale'       => $input['selectedLocale'],
        ));
    }

    private function getExpressionOfInterestTranslations(array $languageIds)
    {
        foreach($languageIds as $languageId)
        {
            $translations[$languageId] = array(
                'languageLabel'                  => trans('settings.language', [], 'messages', $languageId),
                'pleaseConfirmInterestToTender'  => trans('tenders.pleaseConfirmInterestToTender', [], 'messages', $languageId),
                'currentlyLoggedInAs'            => trans('projects.currentlyLoggedInAs',[], 'messages', $languageId),
                'project'                        => trans('projects.project', [], 'messages', $languageId),
                'descriptionOfWork'              => trans('projects.descriptionOfWork', [], 'messages', $languageId),
                'statusConfirmationIsSuccessful' => trans('projects.statusConfirmationIsSuccessful', [], 'messages', $languageId),
                'commitmentYes'                  => ContractorCommitmentStatus::getText(ContractorCommitmentStatus::OK, $languageId),
                'commitmentNo'                   => ContractorCommitmentStatus::getText(ContractorCommitmentStatus::REJECT, $languageId),
            );
        }

        return $translations;
    }

    private function deleteExpressionOfInterestTokens($tenderStageId, $tenderStageType, $companyId) {
        \DB::table('expression_of_interest_tokens')
            ->where('tenderstageable_id', $tenderStageId)
            ->where('tenderstageable_type', $tenderStageType)
            ->where('company_id', $companyId)
            ->delete();
    }

    private function invalidatePendingExpressionOfInterestTokens($tenderStageId, $tenderStageType) {
        \DB::table('expression_of_interest_tokens')
            ->where('tenderstageable_id', $tenderStageId)
            ->where('tenderstageable_type', $tenderStageType)
            ->delete();
    }

    /**
     * Gets the log of the contractor's commitment status
     *
     * @param $project
     * @param $tenderId
     *
     * @return array
     */
    public function getContractorsCommitmentStatusLog($project, $tenderId)
    {
        $input       = Input::all();
        $companyId   = $input['companyId'];
        $tenderStage = $input['tenderStage'];
        $log = array();

        if( $tenderStage == TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER )
        {
            $log = $this->tenderROTInformationRepo->getContractorsCommitmentStatusLog($tenderId, $companyId);
        }
        if( $tenderStage == TenderStages::TENDER_STAGE_LIST_OF_TENDERER )
        {
            $log = $this->tenderLOTInformationRepo->getContractorsCommitmentStatusLog($tenderId, $companyId);
        }
        elseif( $tenderStage == TenderStages::TENDER_STAGE_CALLING_TENDER )
        {
            $log = $this->tenderCallingTenderInformationRepo->getContractorsCommitmentStatusLog($tenderId, $companyId);
        }

        foreach($log as $key => $logEntry)
        {
            $log[$key]['date'] = $project->getProjectTimeZoneTime($logEntry['date']);
        }

        return $log;
    }

    public function exportListOfTendererInfoToExcel(Project $project, $tenderId)
    {
        $tender = Tender::find($tenderId);
        $spreadsheet = $this->tenderRepo->generateListOfTendererExcelSpreadsheetInfo($project, $tender);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="List of Tenderers Information.xlsx"');

        try
        {
            $writer->save("php://output");
        }
        catch(Exception $e) {
            echo $e->getMessage();
        }
    }
}