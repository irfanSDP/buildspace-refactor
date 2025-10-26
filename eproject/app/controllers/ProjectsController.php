<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\MessageBag;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;
use PCK\Forms\ProjectImportForm;
use PCK\Forms\SkipToPostContractForm;
use PCK\Helpers\Files;
use PCK\Projects\Project;
use PCK\Tenders\Tender;
use PCK\Contracts\Contract;
use PCK\Forms\ProjectFormDesign;
use PCK\Forms\ProjectDeleteForm;
use PCK\Subsidiaries\Subsidiary;
use PCK\Subsidiaries\SubsidiaryRepository;
use PCK\Tenders\TenderRepository;
use PCK\Projects\ProjectRepository;
use PCK\WorkCategories\WorkCategory;
use PCK\Forms\ProjectFormCompletion;
use PCK\Companies\CompanyRepository;
use PCK\Calendars\CalendarRepository;
use PCK\Forms\ProjectFormPostContract;
use PCK\ExtensionOfTimes\ExtensionOfTimeRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PCK\DailyLabourReports\ProjectLabourRate;
use PCK\Buildspace\PreDefinedLocationCode;
use PCK\ContractGroups\Types\Role;
use PCK\ContractGroups\ContractGroup;
use PCK\DefectCategoryTradeMapping\DefectCategoryPreDefinedLocationCode;
use PCK\LetterOfAward\LetterOfAwardRepository;
use PCK\RequestForVariation\RequestForVariationRepository;
use PCK\LetterOfAward\LetterOfAwardTemplateSelectionRepository;
use PCK\FormOfTender\FormOfTenderTemplateSelectionRepository;
use PCK\FormOfTender\FormOfTenderRepository;
use PCK\GeneralSettings\GeneralSetting;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;
use PCK\Tenders\OpenTenderPageInformation;
use PCK\Tenders\openTenderTenderRequirement;
use PCK\OpenTenderNews\OpenTenderNews; 
use PCK\OpenTenderBanners\OpenTenderBanners;
use PCK\Projects\ProjectProgressChecklist;

use PCK\Orders\OrderRepository;
use PCK\PaymentGateway\PaymentGatewayRepository;
use PCK\PaymentGateway\PaymentGatewaySettingRepository;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;

class ProjectsController extends \BaseController {

    private $projectRepo;
    private $tenderRepo;
    private $calendarRepo;
    private $projectFormDesign;
    private $projectDeleteForm;
    private $projectFormPostContract;
    private $projectFormCompletion;
    private $companyRepo;
    private $subsidiaryRepository;
    private $contractGroupProjectUserRepository;
    private $skipToPostContractForm;
    private $eotRepo;
    private $projectImportForm;
    private $requestForVariationRepository;
    private $letterOfAwardTemplateSelectionRepository;
    private $letterOfAwardRepository;
    private $formOfTenderTemplateSelectionRepository;
    private $formOfTenderRepository;
    private $orderRepository;
    private $paymentGatewayRepository;
    private $paymentGatewaySettingRepository;

    public function __construct(
        ProjectRepository $projectRepo,
        TenderRepository $tenderRepo,
        CalendarRepository $calendarRepo,
        CompanyRepository $companyRepo,
        ProjectFormDesign $projectFormDesign,
        ProjectDeleteForm $projectDeleteForm,
        ProjectImportForm $projectImportForm,
        ProjectFormPostContract $projectFormPostContract,
        ProjectFormCompletion $projectFormCompletion,
        SubsidiaryRepository $subsidiaryRepository,
        ContractGroupProjectUserRepository $contractGroupProjectUserRepository,
        SkipToPostContractForm $skipToPostContractForm,
        ExtensionOfTimeRepository $eotRepo,
        RequestForVariationRepository $requestForVariationRepository,
        LetterOfAwardTemplateSelectionRepository $letterOfAwardTemplateSelectionRepository,
        LetterOfAwardRepository $letterOfAwardRepository,
        FormOfTenderTemplateSelectionRepository $formOfTenderTemplateSelectionRepository,
        FormOfTenderRepository $formOfTenderRepository,
        OrderRepository $orderRepository,
        PaymentGatewayRepository $paymentGatewayRepository,
        PaymentGatewaySettingRepository $paymentGatewaySettingRepository
    )
    {
        $this->projectRepo                              = $projectRepo;
        $this->tenderRepo                               = $tenderRepo;
        $this->calendarRepo                             = $calendarRepo;
        $this->companyRepo                              = $companyRepo;
        $this->projectFormDesign                        = $projectFormDesign;
        $this->projectDeleteForm                        = $projectDeleteForm;
        $this->projectFormPostContract                  = $projectFormPostContract;
        $this->projectFormCompletion                    = $projectFormCompletion;
        $this->subsidiaryRepository                     = $subsidiaryRepository;
        $this->contractGroupProjectUserRepository       = $contractGroupProjectUserRepository;
        $this->skipToPostContractForm                   = $skipToPostContractForm;
        $this->eotRepo                                  = $eotRepo;
        $this->projectImportForm                        = $projectImportForm;
        $this->requestForVariationRepository            = $requestForVariationRepository;
        $this->letterOfAwardTemplateSelectionRepository = $letterOfAwardTemplateSelectionRepository;
        $this->letterOfAwardRepository                  = $letterOfAwardRepository;
        $this->formOfTenderTemplateSelectionRepository  = $formOfTenderTemplateSelectionRepository;
        $this->formOfTenderRepository                   = $formOfTenderRepository;
        $this->orderRepository                          = $orderRepository;
        $this->paymentGatewayRepository                 = $paymentGatewayRepository;
        $this->paymentGatewaySettingRepository          = $paymentGatewaySettingRepository;
    }

    public function index()
    {
        $user         = Confide::user();
        $subsidiaries = $this->subsidiaryRepository->getRelevantSubsidiaries($user)->lists('fullName', 'id');

        $subsidiariesTree = Subsidiary::getSubsidiariesTree();

        $projectStatuses = [
            0 => trans('general.all'),
            Project::STATUS_TYPE_DESIGN => Project::STATUS_TYPE_DESIGN_TEXT,
            Project::STATUS_TYPE_POST_CONTRACT => Project::STATUS_TYPE_POST_CONTRACT_TEXT,
            Project::STATUS_TYPE_COMPLETED => Project::STATUS_TYPE_COMPLETED_TEXT,
            Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER => Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER_TEXT,
            Project::STATUS_TYPE_LIST_OF_TENDERER => Project::STATUS_TYPE_LIST_OF_TENDERER_TEXT,
            Project::STATUS_TYPE_CALLING_TENDER => Project::STATUS_TYPE_CALLING_TENDER_TEXT,
            Project::STATUS_TYPE_CLOSED_TENDER => Project::STATUS_TYPE_CLOSED_TENDER_TEXT,
            Project::STATUS_TYPE_E_BIDDING => Project::STATUS_TYPE_E_BIDDING_TEXT
        ];

        JavaScript::put(array( 'subsidiariesTree' => $subsidiariesTree ));

        return View::make('projects.index', compact('user', 'subsidiaries', 'projectStatuses'));
    }

    public function updateProjectProgressChecklist($project)
    {
        $input = Input::all();

        $progressCheckList = ProjectProgressChecklist::where('project_id', $project->id)->first();

        if( !$progressCheckList )
        {
            $progressCheckList = ProjectProgressChecklist::create([
                'project_id' => $project->id,
            ]);
        }  

        switch ($input["stage"])
        {
            case 'skip_bq_prepared_published_to_tendering':
                $progressCheckList->skip_bq_prepared_published_to_tendering = $input["skip"];
                break;
            case 'skip_tender_document_uploaded':
                $progressCheckList->skip_tender_document_uploaded = $input["skip"];
                break;
            case 'skip_form_of_tender_edited':
                $progressCheckList->skip_form_of_tender_edited = $input["skip"];
                break;
            case 'skip_rot_form_submitted':
                $progressCheckList->skip_rot_form_submitted = $input["skip"];
                break;
            case 'skip_lot_form_submitted':
                $progressCheckList->skip_lot_form_submitted = $input["skip"];
                break;
            case 'skip_calling_tender_form_submitted':
                $progressCheckList->skip_calling_tender_form_submitted = $input["skip"];
                break;
            case 'skip_project_addendum_finalised':
                $progressCheckList->skip_project_addendum_finalised = $input["skip"];
                break;
        }

        $progressCheckList->save();

        return $progressCheckList;
    }

    public function ajaxList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = Confide::user();

        $projectIds = $this->projectRepo->getVisibleProjectIds($user);

        $projectIds = (empty($projectIds)) ? [-1] : $projectIds;

        $model = Project::whereIn('projects.id', $projectIds)
        ->with('subProjects');
        
        $includeMainProject = false;
        $includeSubProject  = false;
        $includeOpenTender  = false;

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(is_array($filters['value']) or strlen($filters['value'])==0)
                {
                    continue;
                }

                $val = trim($filters['value']);
                switch(trim(strtolower($filters['field'])))
                {
                    case 'ismainproject':
                        if((int)$val > 0)
                        {
                            $includeMainProject = true;
                        }
                        break;
                    case 'issubproject':
                        if((int)$val > 0)
                        {
                            $includeSubProject = true;
                        }
                        break;
                    case 'isopentender':
                        if((int)$val > 0)
                        {
                            $includeOpenTender = true;
                        }
                        break;
                    case 'subsidiaryid':
                        if((int)$val > 0)
                        {
                            $relevantSubsidiaryIds = Subsidiary::getSelfAndDescendantIds([(int)$val])[(int)$val];
                            $model->whereIn('projects.subsidiary_id', $relevantSubsidiaryIds);
                            //$model->where('projects.subsidiary_id', (int)$val);
                        }
                        break;
                    case 'projecttitle':
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
                    case 'projectstatus':
                        if((int)$val > 0)
                        {
                            $model->where('projects.status_id', (int)$val);
                        }
                        break;
                }
            }
        }

        if(!$includeMainProject or !$includeSubProject)
        {
            if($includeMainProject)
            {
                $model->whereNull('projects.parent_project_id');
            }

            if($includeSubProject)
            {
                $model->whereNotNull('projects.parent_project_id');
            }
        }

        if($includeOpenTender)
        {
            $model->where("open_tender", true);
        }

        $model->orderBy('projects.created_at', 'projects.desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        foreach($records->all() as $key => $project)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $visibleSubProjects = $project->subProjects->filter(function($subProject) use ($projectIds)
            {
                return in_array($subProject->id, $projectIds);
            });

            $data[] = [
                'counter'                          => $counter,
                'reference'                        => $project->reference,
                'projectTitle'                     => $project->title,
                'projectStatus'                    => Project::getStatusById($project->status_id),
                'subsidiaryId'                     => $project->subsidiary_id,
                'isSubProject'                     => $project->isSubProject(),
                'isMainProject'                    => $project->isMainProject(),
                'subProjectCount'                  => $visibleSubProjects->count(),
                'projectCreatedAt'                 => Carbon::parse($project->created_at)->format(\Config::get('dates.submission_date_formatting')),
                'country'                          => $project->country->country ?: 'N/A',
                'state'                            => $project->state->name ?: 'N/A',
                'contractName'                     => $project->contract->name,
                'route:projects.show'              => route('projects.show', array( $project->id )),
                'route:projects.subPackages.index' => route('projects.subPackages.index', array( $project->id )),
                'route:projects.delete'            => route('projects.delete', array( $project->id )),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * GET /projects/create
     *
     * @return Response
     */
    public function create()
    {
        $user                   = Confide::user();
        $contractTypes          = array( -1 => trans('forms.select') ) + Contract::orderBy('name', 'asc')->lists('name', 'id');
        $defaultStatus          = Project::getDefaultStatusText();
        $workCategories         = array( -1 => trans('forms.select') ) + WorkCategory::where('enabled', true)->orderBy('name')->lists('name', 'id');
        $urlCountry             = route('country');
        $urlStates              = route('country.states');
        $stateId                = Input::old('state_id', null);
        $letterOfAwardTemplates = $this->letterOfAwardTemplateSelectionRepository->getAllTemplates();
        $formOfTenderTemplates  = $this->formOfTenderTemplateSelectionRepository->getAllTemplates();

        // init values are for the default values for the form (on page load).
        $initRunningNumber = 1;
        $initSuffix        = Project::generateContractNumberSuffix();

        $companyId = $user->company_id;

        $filterByCompany = false;
       
        if(GeneralSetting::count() > 0)
        {
            $filterByCompany = GeneralSetting::first()->view_own_created_subsidiary;
        }
        if($filterByCompany)
        {
            $subsidiaries = $this->subsidiaryRepository->getHierarchicalCollection()
                            ->filter(function($subsidiary) use ($companyId) {return $subsidiary->company_id == $companyId;})
                            ->lists('fullName', 'id');
        }
        else
        {
            $subsidiaries = $this->subsidiaryRepository->getHierarchicalCollection()->lists('fullName', 'id');
        }

        JavaScript::put(compact('urlCountry', 'urlStates', 'stateId'));

        return View::make('projects.create', array(
            'user'                   => $user,
            'contractTypes'          => $contractTypes,
            'defaultStatus'          => $defaultStatus,
            'workCategories'         => $workCategories,
            'subsidiaries'           => $subsidiaries,
            'initRunningNumber'      => $initRunningNumber,
            'initSuffix'             => $initSuffix,
            'letterOfAwardTemplates' => $letterOfAwardTemplates,
            'formOfTenderTemplates'  => $formOfTenderTemplates,
            'companyId'              => $companyId,
            'filterByCompany'        => $filterByCompany,
        ));
    }

    public function generateRunningNumber()
    {
        if( Input::get('subsidiary_id') < 1 ) return null;

        return $this->projectRepo->getLatestRunningNumber(Subsidiary::find(Input::get('subsidiary_id')), Input::get('reference_suffix'));
    }

    public function generateContractNumber()
    {
        $runningNumberPrefix    = 'C';
        $subsidiaryIdentifier   = Subsidiary::find(Input::get('subsidiary_id'))->identifier ?? null;
        $workCategoryIdentifier = WorkCategory::find(Input::get('work_category_id'))->identifier ?? null;
        $runningNumber          = str_pad(Input::get('running_number'), 3, '0', STR_PAD_LEFT);
        $referenceSuffix        = Input::get('reference_suffix');

        $contractNumber = "{$subsidiaryIdentifier}/{$workCategoryIdentifier}/{$runningNumberPrefix}{$runningNumber}/{$referenceSuffix}";

        return Response::json(array(
            'contract_number' => $contractNumber,
        ));
    }

    public function store()
    {
        $inputs = Input::all();

        $this->projectFormDesign->validate($inputs);

        $errors = $this->projectRepo->createRunningNumberUniquenessValidator($inputs);

        if( ! $errors->isEmpty() )
        {
            return Redirect::back()->withErrors($errors)->withInput();
        }

        $inputs["open_tender"] = isset($inputs["open_tender"]) ? true : false;

        $project = $this->addProject($inputs);
        $tender  = $this->projectRepo->generateTender($project);

        $this->letterOfAwardRepository->createEntry($project, $inputs['letter_of_award_template_id']);
        $this->formOfTenderRepository->createNewResources($tender, $inputs['form_of_tender_template_id']);

        Flash::success("Project ({$project->title}) successfully created!");

        return Redirect::route('projects.company.assignment', array( $project->id ));
    }

    private function addProject(array $inputs)
    {
        $user = Confide::user();

        $project = $this->projectRepo->add($user, $inputs);

        $this->contractGroupProjectUserRepository->assignCompanyAdmins($project, $user->company);

        return $project;
    }

    public function ajaxGetProjectScheduleCostTimeData($id)
    {
        $client = new \GuzzleHttp\Client();
        $res    = $client->request('GET', getenv('BUILDSPACE_URL') . "eproject_api/getAccumulativeCost/id/" . $id);

        return $res->getBody();
    }

    public function show(Project $project)
    {
        $includeFutureTasks         = false;
        $user                       = Confide::user();
        $events                     = $this->calendarRepo->getEventsListing($project);
        $dashBoardData              = array();
        $projectSchedules           = array();
        $eotDays                    = 0;
        $expectedCompletionDate     = "";
        $allPendingReviews          = $user->getPendingReviews($includeFutureTasks, $project);
        $pendingUserReviews         = $allPendingReviews['postContract'];
        $pendingTenderProcesses     = $allPendingReviews['tendering'];
        $pendingSiteModuleProcesses = $allPendingReviews['siteModule'];
        $canSendEmailNotifications  = $user->canSendEmailNotifications($project);

        $buCompany  = $project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))->first();
        $gcdCompany = $project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::GROUP_CONTRACT))->first();

        $editorIds = [];

        if($buCompany)
        {
            $editorIds = $buCompany->getProjectEditors($project)->lists('id');
            
            if($gcdCompany)
            {
                $editorIds = array_merge($editorIds, $gcdCompany->getProjectEditors($project)->lists('id'));
            }
        }

        $isBuOrGcdEditor = in_array($user->id, $editorIds);

        if( $project->isPostContract() && ( ! $user->isSuperAdmin() ) && $user->getAssignedCompany($project)->contractGroupCategory->hasPrivilege(\PCK\ContractGroupCategory\ContractGroupCategoryPrivilege::DASHBOARD_PROJECT_POST_CONTRACT) )
        {
            $client = new \GuzzleHttp\Client();
            $res    = $client->request('GET', getenv('BUILDSPACE_URL') . "eproject_api/getTotalClaimAndContractAmountInfo/epid/" . $project->id, array( 'verify' => false ));

            $dashBoardData = json_decode($res->getBody());
            
            $projectSchedules = [];

            if($project->getBsProjectMainInformation())
            {
                $records = $project->getBsProjectMainInformation()->getProjectScheduleList();

                foreach($records as $record)
                {
                    $projectSchedules[$record->id] = $record->title;
                }
            }

            foreach($this->eotRepo->all($project) as $eot)
            {
                $eotDays += $eot->days_granted;
            }

            if( $project->pam2006Detail ) $expectedCompletionDate = date("d-M-Y", strtotime("+" . $eotDays . "days", strtotime($project->pam2006Detail->completion_date)));
        }

        JavaScript::put(array(
            'getUnreadMessagesCount'     => route('messages.unreadMessageCount', array( $project->id )),
            'getMessagesURL'             => route('messages', array( $project->id )),
            'createMessageURL'           => route('message.create', array( $project->id )),
            'getEmailNotificationsURL'   => route('email_notifications', array( $project->id )),
            'createEmailNotificationURL' => route('email_notifications.create', array( $project->id )),
            'events'                     => $events,
        ));

        return View::make('projects.show', compact(
            'project',
            'user',
            'dashBoardData',
            'projectSchedules',
            'eotDays',
            'expectedCompletionDate',
            'pendingUserReviews',
            'pendingTenderProcesses',
            'pendingSiteModuleProcesses',
            'canSendEmailNotifications',
            'isBuOrGcdEditor'
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $project
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($project)
    {
        try
        {
            $this->projectDeleteForm->validate(['id' => $project->id]);

            $this->projectRepo->delete($project);

            Flash::success("Project ({$project->short_title}) has been successfully deleted.");
        }
        catch(Exception $e)
        {
            Flash::error("Project ({$project->title}) cannot be deleted as it is being used in another module.");

            if(!$this->projectDeleteForm->getErrors()->isEmpty())
            {
                Flash::error($this->projectDeleteForm->getErrors()->first());
            }
        }

        return Redirect::back();
    }

    public function postContractCreate($project)
    {
        $user       = Confide::user();
        $tender     = $project->latestTender;
        $contractor = $this->companyRepo->find($tender->currently_selected_tenderer_id);

        $mappedTrades = DefectCategoryPreDefinedLocationCode::lists("pre_defined_location_code_id");

        $trades = PreDefinedLocationCode::where('level', '0')->whereIn('id', $mappedTrades)->get();

        return View::make('projects.postContractCreate', compact('project', 'user', 'contractor', 'trades'));
    }

    public function postContractStore($project)
    {
        $inputs = Input::all();

        $inputs['commencement_date'] = $project->getAppTimeZoneTime($inputs['commencement_date'] ?? null);
        $inputs['completion_date']   = $project->getAppTimeZoneTime($inputs['completion_date'] ?? null);

        $user = Confide::user();

        $this->projectFormPostContract->setProject($project);
        $this->projectFormPostContract->validate($inputs);

        // will cancel out all access for latest tender's selected final contractor's login
        $this->tenderRepo->cancelAccessToSelectedContractors($project->latestTender);

        // set selected contractor
        $this->tenderRepo->setSelectedContractor($project->latestTender, $inputs);

        $this->projectRepo->savePostContractInformation($project, $inputs);

        ProjectLabourRate::saveProjectLabourRateRecords($project, $inputs, $user);

        $this->projectRepo->assignFinalContractor($project, $inputs['contractorId'], true);

        Flash::success('Published to Post Contract');

        return Redirect::route('projects.show', array( $project->id ));
    }

    /**
     * Return a view for the 'project completion date' form
     *
     * @param $project
     *
     * @return \Illuminate\View\View
     */
    public function completionCreate($project)
    {
        return View::make('projects.completionCreate', compact('project'));
    }

    /**
     * Updates the project's completion date using ProjectRepository (projectRepo)
     *
     * @param $project
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Laracasts\Validation\FormValidationException
     */
    public function completionStore($project)
    {
        $input = Input::all();

        $input['completion_date'] = $project->getAppTimeZoneTime($input['completion_date'] ?? null);

        $this->projectFormCompletion->validate($input);

        $this->projectRepo->updateCompletionDate($project, $input);

        Flash::success("Published to Completion");

        return Redirect::route('projects.index');
    }

    public function manualUpdateCallingTenderStatus()
    {
        \Artisan::call('system:update-calling-tender-to-closed-tender');

        return 'Successfully updated Calling Tender\'s Project to Closed Tender!';
    }

    /**
     * Toggles contractor's access.
     *
     * @param $project
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleContractorAccess($project)
    {
        $project->contractor_access_enabled = ( ! $project->contractor_access_enabled );
        $project->save();

        Flash::success("Updated contractor access.");

        return Redirect::back();
    }

    /**
     * Toggles contractor's access to contractual claim modules.
     *
     * @param $project
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleContractorContractualClaimAccess($project)
    {
        $project->contractor_contractual_claim_access_enabled = ( ! $project->contractor_contractual_claim_access_enabled );
        $project->save();

        Flash::success("Updated contractor contractual claim access.");

        return Redirect::back();
    }

    public function skipToPostContractConfirmation($project)
    {
        $contractors = $this->companyRepo->findWithRoleType(\PCK\ContractGroups\Types\Role::CONTRACTOR);

        $mappedTrades = DefectCategoryPreDefinedLocationCode::lists("pre_defined_location_code_id");

        $trades = PreDefinedLocationCode::where('level', '0')->whereIn('id', $mappedTrades)->get();

        return View::make('projects.skipToPostContract', array(
            'project'     => $project,
            'contractors' => $contractors,
            'trades'      => $trades,
        ));
    }

    public function skipToPostContract(Project $project)
    {
        $input = Input::all();

        $input['commencement_date'] = $project->getAppTimeZoneTime($input['commencement_date'] ?? null);
        $input['completion_date']   = $project->getAppTimeZoneTime($input['completion_date'] ?? null);

        $errors = null;

        $user = Confide::user();

        $this->projectFormPostContract->setProject($project);
        $this->projectFormPostContract->validate($input);

        try
        {
            $this->skipToPostContractForm->validate($input);

            $success = $project->skipToStage(Project::STATUS_TYPE_POST_CONTRACT, ['selectedContractorId' => $input['contractor_id'], 'postContractFormInput' => $input]);

            if( ! $success ) {
                throw new Exception('Unable to skip to post contract.');
            }

            ProjectLabourRate::saveProjectLabourRateRecords($project, $input, $user);
        }
        catch(\Laracasts\Validation\FormValidationException $e)
        {
            $errors = $e->getErrors();
        }
        catch(\PCK\Exceptions\ValidationException $e)
        {
            $errors = $e->getErrors();
        }
        catch(Exception $e)
        {
            $v = \Validator::make([], []);
            // add an error
            $v->errors()->add('id', $e->getMessage());
            $errors = $v->errors();
        }

        if( $errors )
        {
            Flash::error($errors->first());

            return Redirect::back()->withErrors($errors)->withInput();
        }

        Flash::success("Skipped to Post Contract");

        return Redirect::route('projects.show', array( $project->id ));
    }

    public function postContractInfoEdit(Project $project)
    {
        if(!$project->pam2006Detail)
        {
            return Redirect::route('projects.show', [$project->id]);
        }

        return View::make('projects.postContractEdit', compact('project'));
    }

    public function postContractInfoStore(Project $project)
    {
        if(!$project->pam2006Detail)
        {
            return Redirect::route('projects.show', [$project->id]);
        }

        $request = Request::instance();

        try
        {
            $this->projectFormPostContract->setProject($project);
            $this->projectFormPostContract->validate($request->all());
        }
        catch(\Exception $e)
        {
            return Redirect::back()
                ->withErrors($this->projectFormPostContract->getErrors())
                ->withInput(Input::all());
        }

        $pam2006Detail = $project->pam2006Detail;

        $pam2006Detail->min_days_to_comply_with_ai = $request->get('min_days_to_comply_with_ai');
        $pam2006Detail->deadline_submitting_notice_of_intention_claim_eot = $request->get('deadline_submitting_notice_of_intention_claim_eot');
        $pam2006Detail->deadline_submitting_final_claim_eot = $request->get('deadline_submitting_final_claim_eot');
        $pam2006Detail->deadline_architect_request_info_from_contractor_eot_claim = $request->get('deadline_architect_request_info_from_contractor_eot_claim');
        $pam2006Detail->deadline_architect_decide_on_contractor_eot_claim = $request->get('deadline_architect_decide_on_contractor_eot_claim');
        $pam2006Detail->deadline_submitting_note_of_intention_claim_l_and_e = $request->get('deadline_submitting_note_of_intention_claim_l_and_e');
        $pam2006Detail->deadline_submitting_final_claim_l_and_e = $request->get('deadline_submitting_final_claim_l_and_e');
        $pam2006Detail->deadline_submitting_note_of_intention_claim_ae = $request->get('deadline_submitting_note_of_intention_claim_ae');
        $pam2006Detail->deadline_submitting_final_claim_ae = $request->get('deadline_submitting_final_claim_ae');
        $pam2006Detail->period_of_architect_issue_interim_certificate = $request->get('period_of_architect_issue_interim_certificate');
        $pam2006Detail->percentage_value_of_materials_and_goods_included_in_certificate = $request->get('percentage_value_of_materials_and_goods_included_in_certificate');
        $pam2006Detail->percentage_of_certified_value_retained = $request->get('percentage_of_certified_value_retained');
        $pam2006Detail->limit_retention_fund = $request->get('limit_retention_fund');
        $pam2006Detail->liquidate_damages = ($request->get('liquidate_damages')) ? $request->get('liquidate_damages') : null;
        $pam2006Detail->interim_claim_interval = $request->get('interim_claim_interval');
        $pam2006Detail->amount_performance_bond = ($request->get('amount_performance_bond')) ? $request->get('amount_performance_bond') : null;
        $pam2006Detail->period_of_honouring_certificate = $request->get('period_of_honouring_certificate');
        $pam2006Detail->cpc_date = ($request->get('cpc_date')) ? date('Y-m-d', strtotime($request->get('cpc_date'))) : null;
        $pam2006Detail->extension_of_time_date = ($request->get('extension_of_time_date')) ? date('Y-m-d', strtotime($request->get('extension_of_time_date'))) : null;
        $pam2006Detail->certificate_of_making_good_defect_date = ($request->get('certificate_of_making_good_defect_date')) ? date('Y-m-d', strtotime($request->get('certificate_of_making_good_defect_date'))) : null;
        $pam2006Detail->cnc_date = ($request->get('cnc_date')) ? date('Y-m-d', strtotime($request->get('cnc_date'))) : null;
        $pam2006Detail->performance_bond_validity_date = ($request->get('performance_bond_validity_date')) ? date('Y-m-d', strtotime($request->get('performance_bond_validity_date'))) : null;
        $pam2006Detail->insurance_policy_coverage_date = ($request->get('insurance_policy_coverage_date')) ? date('Y-m-d', strtotime($request->get('insurance_policy_coverage_date'))) : null;
        $pam2006Detail->defect_liability_period = $request->get('defect_liability_period');
        $pam2006Detail->defect_liability_period_unit = $request->get('defect_liability_period_unit');

        $pam2006Detail->save();

        Flash::success("Successfully updated Post Contract Information");

        return Redirect::route('projects.show', [$project->id]);
    }

    /**
     * Checks if the given reference (Contract Number) is available for a new project.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkContractNumberAvailability()
    {
        return Response::json(array( 'available' => ( ( ! empty( trim(Input::get('reference')) ) ) && ( ! $this->projectRepo->referenceExists(Input::get('reference')) ) ) ));
    }

    public function checkRunningNumberAvailability()
    {
        $runningNumber = Input::get('running_number');

        $exists = \PCK\Helpers\Tables::comboExists('projects', array(
            'deleted_at'       => null,
            'subsidiary_id'    => Input::get('subsidiary_id'),
            'reference_suffix' => Input::get('reference_suffix'),
            'running_number'   => empty( $runningNumber ) ? -1 : $runningNumber,
        ));

        return Response::json(array( 'available' => ( ! $exists ) ));
    }

    public function subPackagesIndex(Project $project)
    {
        return View::make('projects.subPackage.index', array( 'project' => $project ));
    }

    public function getSubPackagesList(Project $project)
    {
        $user        = Confide::user();
        $subPackages = $project->getAssignedSubProjects($user);

        $records = [];

        foreach($subPackages as $subPackage)
        {
            $records[] = [
                'id'           => $subPackage->id,
                'reference'    => $subPackage->reference,
                'title'        => $subPackage->title,
                'short_title'  => $subPackage->short_title,
                'created_at'   => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($subPackage->created_at))->format(\Config::get('dates.submission_date_formatting')),
                'country'      => $subPackage->country->country,
                'state'        => $subPackage->state->name,
                'status'       => \PCK\Projects\Project::getStatusById($subPackage->status_id),
                'route_show'   => route('projects.show', [$subPackage->id]),
                'route_delete' => $user->isSuperAdmin() ? route('projects.delete', [$subPackage->id]) : ""
            ];
        }

        return Response::json($records);
    }

    public function subPackagesCreate(Project $project)
    {
        $user            = Confide::user();
        $contractTypes   = Contract::orderBy('name', 'asc')->lists('name', 'id');
        $defaultStatus   = Project::getDefaultStatusText();
        $fixedSubsidiary = $project->subsidiary;
        $workCategories  = WorkCategory::orderBy('name')->lists('name', 'id');
        $urlCountry      = route('country');
        $urlStates       = route('country.states');
        $stateId         = Input::old('state_id', null);

        // init values are for the default values for the form (on page load).
        $initSuffix        = Project::generateContractNumberSuffix();
        $initRunningNumber = $this->projectRepo->getLatestRunningNumber($project->subsidiary, $initSuffix);

        $templateProject = new Project(array(
            'country_id'       => $project->country_id,
            'contract_id'      => $project->contract_id,
            'state_id'         => $project->state_id,
            'work_category_id' => $project->work_category_id,
        ));

        JavaScript::put(compact('urlCountry', 'urlStates', 'stateId'));

        return View::make('projects.subPackage.create', array(
            'project'           => $project,
            'templateProject'   => $templateProject,
            'user'              => $user,
            'contractTypes'     => $contractTypes,
            'defaultStatus'     => $defaultStatus,
            'workCategories'    => $workCategories,
            'fixedSubsidiary'   => $fixedSubsidiary,
            'initRunningNumber' => $initRunningNumber,
            'initSuffix'        => $initSuffix,
        ));
    }

    public function subPackagesStore(Project $parentProject)
    {
        $user   = Confide::user();
        $inputs = Input::all();
        $file   = Input::file('ebqFile');

        $inputs['subsidiary_id']     = $parentProject->subsidiary_id;
        $inputs['contract_id']       = $parentProject->contract_id;
        $inputs['parent_project_id'] = $parentProject->id;

        $this->projectImportForm->validate($inputs);

        $errors = $this->projectRepo->createRunningNumberUniquenessValidator($inputs);

        if( ! $errors->isEmpty() )
        {
            return Redirect::back()->withErrors($errors)->withInput();
        }

        try
        {
            $project = $this->projectRepo->import($user, $inputs, $parentProject, $file);
        }
        catch(Exception $exception)
        {
            Flash::error("Sub Project could not be created");

            Log::error($exception->getMessage());

            return Redirect::back()->withInput();
        }

        if( ! $project )
        {
            Flash::error("Sub Project could not be created");

            return Redirect::back()->withInput();
        }

        Flash::success("Sub Project ({$project->title}) successfully created!");

        return Redirect::route('projects.company.assignment', array( $project->id ));
    }

    private function validateEbqFile($file)
    {
        $errors = new MessageBag();

        if( ! $file )
        {
            Flash::error(trans('files.noFileUploaded'));

            $errors->add('ebqFile', trans('files.noFileUploaded'));

            return $errors;
        }

        if( ! Files::hasExtension(Files::EXTENSION_EBQ, $file) )
        {
            Flash::error(trans('files.extensionMismatchEbq'));

            $errors->add('ebqFile', trans('files.extensionMismatchEbq'));
        }

        return $errors;
    }

    public function mainProject()
    {
        $currentTime       = Carbon::now();
        $openTenderNews    = OpenTenderNews::orderBy('id', 'asc')
        ->where('status', 1)
        ->where('start_time', '<=', $currentTime)
        ->where('end_time', '>=', $currentTime)
        ->take(10)
        ->get();
    
        $openTenderBanners = OpenTenderBanners::orderBy('display_order')
        ->orderBy('id', 'asc')
        ->where('start_time', '<=', $currentTime)
        ->where('end_time', '>=', $currentTime)
        ->get();
        
        // $data = [];
        // foreach($openTenderBanners as $banner)
        // {
        //     $data[] = $banner->image;
        // }
        // dd($data);

        return View::make('open_tenders.project_tender.main_project', array(
            'openTenderNews'    => $openTenderNews,
            'openTenderBanners' => $openTenderBanners,
        ));
    }

    public function listNews()
    {

        return View::make('open_tenders.project_tender.list_news');
    }

    public function detailNews($id)
    {
        $openTenderNews = OpenTenderNews::find($id);

        return View::make('open_tenders.project_tender.detail_news', array(
            'openTenderNews'=> $openTenderNews,
        ));
    }

    public function ajaxListNews()
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

		// $openTenderNews = OpenTenderNews::orderBy('id', 'asc')->get();
        $currentTime = Carbon::now();
		$openTenderNews = OpenTenderNews::orderBy('id', 'asc')->where('status', 1)->where('start_time', '<=', $currentTime)->where('end_time', '>=', $currentTime)->get();

        $data = [];
        foreach($openTenderNews as $news)
        {
            // $subsidiary = $news->subsidiary;
            // $rootSubsidiary = $subsidiary->getTopParentSubsidiary('root')->name;
            $data[] = [
                'id'               => $news->id,
                'description'      => $news->description,
                'status'           => 'Aktif', //already filter for data with status 1 only
                'department'       => $news->subsidiary->name,
                'start_time'       => $news->start_time,
                'end_time'         => $news->end_time,
                'created_at'       => $news->created_at,
                'created_by'       => $news->created_by,
            ];
        }
        return Response::json([
            'data' => $data,
        ]);
    }

    public function checkIsCompanyRegisteredWithIndustryCodeVendorCategory($company, $tender)
    {
        $companyVendorCategoryIds = $company->vendorCategories->lists('id'); 
        $tenderIndustryCodes = $tender->openTenderIndustryCodes;

        foreach($tenderIndustryCodes as $tenderIndustryCode)
        {
            if(!in_array($tenderIndustryCode->vendor_category_id, $companyVendorCategoryIds))
            {
                return false; // This tender industry code is not in company's Vendor Categories
            }
        }

        return true;
    }

    public function checkIsCompanyRegisteredWithCIDBCode($company, $tender)
    {
        $companyCidbCodeIds = $company->cidbCodes->lists('id');
        $tenderIndustryCodes = $tender->openTenderIndustryCodes;

        foreach($tenderIndustryCodes as $tenderIndustryCode)
        {
            if(!in_array($tenderIndustryCode->cidb_code_id, $companyCidbCodeIds))
            {
                return false; // This tender industry code is not in company's CIDB codes
            }
        }

        return true; // All tender industry codes exist in company's CIDB codes
    }

    public function detailProject($Id)
    {
        $info           = [];
        $grades         = [];
        $codes          = [];
        $requirement    = [];
        $pic            = [];
        $documents      = [];
        $announcements  = [];
        $project        = Project::findOrFail($Id);
        $tender         = Tender::where('project_id', $Id)->first();
        $user           = Confide::user();

        if (! $tender) {
            Flash::error(trans('errors.recordNotFound'));
            return Redirect::route('open_tenders.main_project');
        }

        $subsidiary = $project->subsidiary;
        $rootSubsidiary = $subsidiary->getTopParentSubsidiary('root')->name;
        
        $information   = $tender->openTenderPageInformation;
        $requirement   = $tender->openTenderTenderRequirement;
        $isCompanyVendorCategoryRegistered = false;
        $isCompanyCodeRegistered = false;

        if($user)
        {
            $isCompanyVendorCategoryRegistered = $this->checkIsCompanyRegisteredWithIndustryCodeVendorCategory($user->company, $tender);
            $isCompanyCodeRegistered = $this->checkIsCompanyRegisteredWithCIDBCode($user->company, $tender);
        }

        if (! $information) {
            Flash::error(trans('errors.recordNotFound'));
            return Redirect::route('open_tenders.main_project');
        }

        $address =  $information->briefing_address;
        $encodedAddress = urlencode($address);
        $googleMapsLink = "https://www.google.com/maps/search/?api=1&query=" . $encodedAddress;

        $info = [
            'id'                       => $project ? $project->id : null,
            'title'                    => $project ? $project->title : null,
            'open_tender_type'         => $project ? $rootSubsidiary : null,
            'description'              => $requirement ? $requirement->description : null,
            'open_tender_number'       => $information->open_tender_number,
            'open_tender_price'        => $information->open_tender_price,
            'calling_date'             => $information->calling_date,
            'open_tender_date_from'    => $information->open_tender_date_from,
            'open_tender_date_to'      => $information->open_tender_date_to,
            'closing_date'             => $information->closing_date,
            'briefing_time'            => $information->briefing_time,
            'briefing_address'         => $information->briefing_address,
            'deliver_address'          => $information->deliver_address,
            'special_permission'       => $information->special_permission,
            'local_company_only'       => $information->local_company_only,
            'googleMapsLink'           => $googleMapsLink,
        ];

        $openTenderIndustryCodes = $tender->openTenderIndustryCodes;
        foreach($openTenderIndustryCodes as $openTenderIndustryCode)
        {
            $cidbCode = $openTenderIndustryCode->cidbCode;
            $codes[] = [
                'code' => $cidbCode->code,
                'desc' => $cidbCode->description,
            ];
        }

        $openTenderIndustryCodes = $tender->openTenderIndustryCodes;
        foreach($openTenderIndustryCodes as $openTenderIndustryCode)
        {
            $cidbGrade = $openTenderIndustryCode->cidbGrade;
            $grades[] = [
                'grade' => $cidbGrade->description,
            ];
        }

        $openTenderPersonsInCharges = $tender->openTenderPersonInCharges;
        foreach($openTenderPersonsInCharges as $openTenderPersonInCharges)
        {
            $pic[] = [
                'name'         => $openTenderPersonInCharges->name,
                'email'        => $openTenderPersonInCharges->email,
                'phone_number' => $openTenderPersonInCharges->phone_number,
                'department'   => $openTenderPersonInCharges->department,
            ];
        }

        $openTenderTenderDocuments = $tender->openTenderTenderDocuments;
        foreach($openTenderTenderDocuments as $openTenderTenderDocument)
        {
            $documents = [];
            $description = $openTenderTenderDocument->description;
            $moduleUploadFiles = ModuleUploadedFile::where('uploadable_id', $openTenderTenderDocument->id)
            ->where('uploadable_type', 'PCK\Tenders\OpenTenderTenderDocument')
            ->get();

            $count = $moduleUploadFiles->count();

            foreach ($moduleUploadFiles as $moduleUploadFile) {
                $file = $moduleUploadFile->file;
        
                // Remove the 'public/' prefix from the path
                $path = str_replace('/public/', '/', $file->path . $file->filename);
        
                $documents[] = [
                    'filename' => $file->filename,
                    'path'     => $path,
                ];
            }

            $openTenderDocuments[] = [
                'description' => $description,
                'count'       => $count,
                'documents'   => $documents,
            ];
        }

        $openTenderAnnouncements = $tender->openTenderAnnouncements;
        foreach($openTenderAnnouncements as $announcement)
        {
            $announcements[] = [
                'description' => $announcement->description,
            ];
        }

        $langLocale = 'ms';
        $tenderData = ['tender' => true, 'interest' => false, 'pg' => false, 'html' => null];

        if (! $user) {
            // User not logged in
            $tenderData['html'] = trans('auth.loginRequired', [], 'messages', $langLocale);
        } elseif (empty($user->company_id) || ! $information->isApproved() || ! $information->isActive() || ! $information->priceIsValid()) {
            // User not allowed to view tender
            $tenderData['tender'] = false;
        } else {
            // Requires special permission to view tender ?
            if ($information->special_permission) { // Yes
                $tenderData['interest'] = true;

                $selectedContractors = $tender->listOfTendererInformation->selectedContractors;
                $selectedContractor = $selectedContractors->find($user->company_id);
                if ($selectedContractor) {  // Has registered interest -> Check status
                    $selectedContractorStatus = $selectedContractor->pivot->status;

                    switch ($selectedContractorStatus) {
                        case ContractorCommitmentStatus::OK:    // Approved
                            $tenderData['interest'] = false;

                            if($isCompanyVendorCategoryRegistered && $isCompanyCodeRegistered)
                            {
                                $tenderData['pg'] = true;   // Allow payment gateway
                            }
                            elseif (!$isCompanyVendorCategoryRegistered) 
                            {
                                $tenderData['html'] = trans('projectOpenTenderBM.vendorKategori');
                            } 
                            elseif (!$isCompanyCodeRegistered) 
                            {
                                $tenderData['html'] = trans('projectOpenTenderBM.Kodbidang');
                            }
                            break;

                        case ContractorCommitmentStatus::PENDING:   // Pending
                            $tenderData['html'] = trans('projectOpenTenderBM.interestToTenderDuplicate');
                            break;

                        case ContractorCommitmentStatus::REJECT:    // Rejected
                            $tenderData['html'] = trans('projectOpenTenderBM.interestToTenderRejected');
                            break;

                        default:    // Unknown
                            // Do nothing
                    }
                } else {    // Has not registered interest
                    if($isCompanyVendorCategoryRegistered && $isCompanyCodeRegistered)
                    {
                        $tenderData['html'] = View::make('open_tenders.partials.interest_to_tender', [
                            'interestUrl' => base64_encode(route('open_tender.lot_insert_contractor')),
                            'projectId' => $project->id,
                            'tenderId' => $tender->id,
                            'companyId' => $user->company_id,
                        ])->render();
                    }
                    elseif (!$isCompanyVendorCategoryRegistered) 
                    {
                        $tenderData['html'] = trans('projectOpenTenderBM.vendorKategori');
                    } 
                    elseif (!$isCompanyCodeRegistered) 
                    {
                        $tenderData['html'] = trans('projectOpenTenderBM.Kodbidang');
                    }
                }
            } else {    // No -> Allow payment gateway
                if($isCompanyVendorCategoryRegistered && $isCompanyCodeRegistered)
                {
                    $tenderData['pg'] = true;   // Allow payment gateway
                }
                elseif (!$isCompanyVendorCategoryRegistered) 
                {
                    $tenderData['html'] = trans('projectOpenTenderBM.vendorKategori');
                } 
                elseif (!$isCompanyCodeRegistered) 
                {
                    $tenderData['html'] = trans('projectOpenTenderBM.Kodbidang');
                }
            }

            // Allow payment gateway ?
            if ($tenderData['pg']) {
                if (! $this->orderRepository->getOrderByProjectTender($user->id, $project->id, $tender->id)) {  // Order with successful payment for tender not found
                    $pgSetting = $this->paymentGatewaySettingRepository->getDefaultGateway(true);
                    if ($pgSetting) {
                        $pgBtnHtml = $this->paymentGatewayRepository->getPayButton($pgSetting->payment_gateway);
                        if ($pgBtnHtml) {
                            // Payment gateway button
                            $tenderData['html'] = View::make('payments.gateway.partials.pg-btn-container', [
                                'paymentGatewayBtnData' => [
                                    'pg' => base64_encode($pgSetting->payment_gateway),
                                    'd' => Crypt::encrypt(serialize([
                                        'lang' => $langLocale,
                                        'type' => \PCK\Orders\OrderItem::TYPE_OPEN_TENDER,
                                        'projectId' => $project->id,
                                        'tenderId' => $tender->id,
                                        'origin' => \PCK\Orders\Order::ORIGIN_TENDER,
                                    ])),
                                    'lnk' => base64_encode(route('api.payment-gateway.html.payment-form')),
                                    'html' => $pgBtnHtml
                                ]
                            ])->render();
                        }
                    }
                } else {    // User has already paid for tender
                    $tenderData['html'] = trans('projectOpenTenderBM.tenderPaymentExist');
                }
            }
        }

        return View::make('open_tenders.project_tender.detail_project', array(
            'info'               => $info,
            'requirement'        => $requirement,
            'openTenderDocuments'          => $openTenderDocuments,
            'codes'              => $codes,
            'grades'             => $grades,
            'pic'                => $pic,
            'announcements'      => $announcements,
            'tenderData'         => $tenderData,
            'isCompanyCodeRegistered'=> $isCompanyCodeRegistered
        ));
    }

    public function mainList()
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $currentTime = Carbon::now();
        $projects = Project::where('open_tender', 1)->get();

        $data = [];
        foreach($projects as $project)
        {
            $codes   = [];
            $grades  = [];
            $tenders = Tender::where('project_id', $project->id)->get();

            $subsidiary = $project->subsidiary;
            $rootSubsidiary = $subsidiary->getTopParentSubsidiary('root')->name;

            foreach($tenders as $tender)
            {
                $information = OpenTenderPageInformation::where('tender_id', $tender->id)->where('open_tender_date_from', '<=', $currentTime)->first();

                if ($information && $information->status == 4) {
                    $openTenderIndustryCodes = $tender->openTenderIndustryCodes;
                    foreach($openTenderIndustryCodes as $openTenderIndustryCode)
                    {
                        $cidbCode = $openTenderIndustryCode->cidbCode;
                        $codes[] = [
                            'code' => $cidbCode->code, 
                            'desc' => $cidbCode->description,
                        ];
                    }
            
                    $openTenderIndustryCodes = $tender->openTenderIndustryCodes;
                    foreach($openTenderIndustryCodes as $openTenderIndustryCode)
                    {
                        $cidbGrade = $openTenderIndustryCode->cidbGrade;
                        $grades[] = [
                            'grade' => $cidbGrade->description,
                        ];
                    }

                    if($information->open_tender_status == 1)
                    {
                        $open_tender_status = 'Aktif';
                    }
                    elseif($information->open_tender_status == 2){
                        $open_tender_status = 'Dibatalkan';
                    }

                    $data[] = [
                        'id'                       => $project ? $project->id : null,
                        'tajuk'                    => $project ? $project->title : null,
                        'petender'                 => $project ? $rootSubsidiary : null,
                        'no_tender'                => $information->open_tender_number,
                        'harga_dokumen'            => $information->open_tender_price,
                        'tarikh_jual'              => Carbon::parse($information->calling_date)->format(\Config::get('dates.full_format')),
                        'tarikh_iklan_mula'        => Carbon::parse($information->open_tender_date_from)->format(\Config::get('dates.full_format')),
                        'tarikh_iklan_akhir'       => Carbon::parse($information->open_tender_date_to)->format(\Config::get('dates.full_format')),
                        'tarikh_tutup'             => Carbon::parse($information->closing_date)->format(\Config::get('dates.full_format')),
                        'tempat_hantar'            => $information->deliver_address,
                        'tarikh_taklimat'          => Carbon::parse($information->briefing_time)->format(\Config::get('dates.full_format')),
                        'tempat_taklimat'          => $information->briefing_address,
                        'kebenaran_khas'           => $information->special_permission,
                        'syarikat_bumiputera_shj'  => $information->local_company_only,
                        'status'                   => $open_tender_status,
                        'codes'                    => $codes,
                        'grades'                   => $grades,
                    ];
                };

            }

        }
        return Response::json([
            'data' => $data,
        ]);
    }
}