<?php

use PCK\Projects\ProjectRepository;
use PCK\Calendars\CalendarRepository;
use PCK\Forms\AddNewInterimClaimForm;
use PCK\ContractGroups\Types\Role;
use PCK\InterimClaims\InterimClaimRepository;
use PCK\Forms\InterimClaimAdditionalInformationContractorForm;
use PCK\Forms\InterimClaimAdditionalInformationArchitectQsForm;
use PCK\InterimClaimInformation\InterimClaimInformationRepository;

class InterimClaimsController extends \BaseController {

    private $projectRepo;

    private $icRepo;

    private $icInformationRepository;

    private $calendarRepository;

    private $addNewForm;

    private $icAdditionalInformationContractorForm;

    private $icAdditionalInformationArchitectQsForm;

    public function __construct(
        ProjectRepository $projectRepo,
        InterimClaimRepository $icRepo,
        InterimClaimInformationRepository $icInformationRepository,
        CalendarRepository $calendarRepository,
        AddNewInterimClaimForm $addNewForm,
        InterimClaimAdditionalInformationContractorForm $icAdditionalInformationContractorForm,
        InterimClaimAdditionalInformationArchitectQsForm $icAdditionalInformationArchitectQsForm
    )
    {
        $this->projectRepo                            = $projectRepo;
        $this->icRepo                                 = $icRepo;
        $this->icInformationRepository                = $icInformationRepository;
        $this->calendarRepository                     = $calendarRepository;
        $this->addNewForm                             = $addNewForm;
        $this->user                                   = Confide::user();
        $this->icAdditionalInformationContractorForm  = $icAdditionalInformationContractorForm;
        $this->icAdditionalInformationArchitectQsForm = $icAdditionalInformationArchitectQsForm;
    }

    /**
     * Display a listing of the Interim Claim.
     *
     * @param $project
     *
     * @return Response
     */
    public function index($project)
    {
        $user     = $this->user;
        $ics      = $this->icRepo->all($project);
        $isEditor = $user->isEditor($project);

        return View::make('interim_claims.index', compact('project', 'user', 'ics', 'isEditor'));
    }

    /**
     * Show the form for creating a new Interim Claim.
     *
     * @param $project
     *
     * @return Response
     */
    public function create($project)
    {
        $user          = $this->user;
        $claimCounter  = $this->icRepo->getMaxClaimCounter($project) + 1;
        $calendarRepo  = $this->calendarRepository;
        $events        = $calendarRepo->getEventsListing($project);
        $isEditor      = $user->isEditor($project);
        $uploadedFiles = $this->getAttachmentDetails();
        $ic            = null;

        JavaScript::put(compact('events'));

        return View::make('interim_claims.create', compact('user', 'ic', 'project', 'claimCounter', 'uploadedFiles', 'calendarRepo', 'isEditor'));
    }

    /**
     * Store a newly created Interim Claim in storage.
     *
     * @param $project
     *
     * @return Response
     */
    public function store($project)
    {
        $inputs = Input::all();
        $user   = $this->user;

        $this->addNewForm->validate($inputs);

        $ic = $this->icRepo->add($project, $user, $inputs);

        \Flash::success("New IC ({$ic->claim_no}) successfully added!");

        return Redirect::route('ic', array( $project->id ));
    }

    /**
     * Display the specified Interim Claim.
     *
     * @param $project
     * @param $icId
     *
     * @return Response
     */
    public function show($project, $icId)
    {
        $user                  = $this->user;
        $project               = $this->projectRepo->findWithSelectedCompanies($project->id);
        $previousIc            = $this->icRepo->findPreviousClaim($project);
        $ic                    = $this->icRepo->find($project, $icId);
        $previousGrantedAmount = $this->icRepo->getAllGrantedAmount($project, $ic);
        $isEditor              = $user->isEditor($project);
        $uploadedFiles         = $this->getAttachmentDetails($ic);
        $calendarRepo          = $this->calendarRepository;
        $events                = $calendarRepo->getEventsListing($project);

        JavaScript::put(compact('events'));

        return View::make('interim_claims.show', compact('user', 'project', 'previousIc', 'previousGrantedAmount', 'ic', 'uploadedFiles', 'calendarRepo', 'isEditor'));
    }

    public function createNewAdditionalInformation($project, $icId)
    {
        $inputs = Input::all();
        $user   = Confide::user();
        $ic     = $this->icRepo->find($project, $icId);

        $inputs['date']                = $project->getAppTimeZoneTime($inputs['date'] ?? null);
        $inputs['date_of_certificate'] = $project->getAppTimeZoneTime($inputs['date_of_certificate'] ?? null);

        if( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $this->icAdditionalInformationContractorForm->validate($inputs);
        }
        else
        {
            $this->icAdditionalInformationArchitectQsForm->validate($inputs);
        }

        $this->icInformationRepository->add($ic, $user, $inputs);

        return Redirect::route('ic.show', array( $project->id, $icId ));
    }

    public function generatePrintOutForAdditionalInformation($project, $iccId)
    {
        $ici                   = $this->icInformationRepository->find($iccId);
        $previousGrantedAmount = $this->icRepo->getAllGrantedAmount($project, $ici->interimClaim);

        return PDF::html('interim_claims.print_out.interim_certificate', compact('ici', 'previousGrantedAmount'));
    }

}