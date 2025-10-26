<?php

use Carbon\Carbon;
use PCK\Clauses\ClauseRepository;
use PCK\Calendars\CalendarRepository;
use PCK\Forms\AddNewExtensionOfTimeForm;
use PCK\ExtensionOfTimes\ExtensionOfTime;
use PCK\ExtensionOfTimes\ExtensionOfTimeRepository;
use PCK\ArchitectInstructions\ArchitectInstructionRepository;

class ExtensionOfTimesController extends \BaseController {

    private $aiRepo;

    private $eotRepo;

    private $clauseRepository;

    private $calendarRepository;

    private $addNewForm;

    private $user;

    public function __construct(
        ArchitectInstructionRepository $aiRepo,
        ExtensionOfTimeRepository $eotRepo,
        ClauseRepository $clauseRepository,
        CalendarRepository $calendarRepository,
        AddNewExtensionOfTimeForm $addNewForm
    )
    {
        $this->aiRepo             = $aiRepo;
        $this->eotRepo            = $eotRepo;
        $this->clauseRepository   = $clauseRepository;
        $this->calendarRepository = $calendarRepository;
        $this->addNewForm         = $addNewForm;
        $this->user               = \Confide::user();
    }

    /**
     * Display a listing of the Extension Of Times.
     *
     * @param $project
     *
     * @return Response
     */
    public function index($project)
    {
        $user = $this->user;
        $eots = $this->eotRepo->all($project);

        return View::make('extension_of_times.index', compact('project', 'user', 'eots'));
    }

    /**
     * Show the form for creating a new Extension Of Times.
     *
     * @param $project
     * @param $aiId
     *
     * @return Response
     */
    public function create($project, $aiId = null)
    {
        $user          = $this->user;
        $clause        = $this->clauseRepository->findItemsWithClauseById(4);
        $events        = $this->calendarRepository->getEventsListing($project);
        $isEditor      = $user->isEditor($project);
        $uploadedFiles = $this->getAttachmentDetails();

        if( $aiId )
        {
            $ai = $this->aiRepo->find($project, $aiId);
        }
        else
        {
            $ai = $this->aiRepo->selectList($project);
        }

        $datesCalculateURL    = route('dates.calculateDates', [ $project->id ]);
        $getAIDeadlineDateURL = route('eot.getAIDeadlineDateURL', [ $project->id ]);

        JavaScript::put(compact('events', 'datesCalculateURL', 'getAIDeadlineDateURL'));

        return View::make('extension_of_times.create', compact('user', 'project', 'clause', 'ai', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Store a newly created resource in Extension Of Times.
     *
     * @param $project
     * @param $aiId
     *
     * @throws \Laracasts\Validation\FormValidationException
     * @return Response
     */
    public function store($project, $aiId = null)
    {
        $user = $this->user;

        if( $aiId )
        {
            $this->aiRepo->find($project, $aiId);
        }

        $inputs = Input::all();

        $this->addNewForm->validate($inputs);
        
        $inputs['commencement_date_of_event'] = $project->getAppTimeZoneTime($inputs['commencement_date_of_event'] ?? null);

        $eot = $this->eotRepo->add($project, $user, $inputs);

        \Flash::success("New EOT ({$eot->subject}) successfully added!");

        return Redirect::route('eot', array( $project->id ));
    }

    /**
     * Display the specified Extension Of Time.
     *
     * @param $project
     * @param $eotId
     *
     * @return Response
     */
    public function show($project, $eotId)
    {
        $user              = $this->user;
        $ai                = $this->aiRepo->selectList($project);
        $eot               = $this->eotRepo->findWithMessages($project, $eotId);
        $isEditor          = $user->isEditor($eot->project);
        $events            = $this->calendarRepository->getEventsListing($eot->project);
        $uploadedFiles     = array();
        $clause            = array();
        $selectedClauseIds = array();

        if( $eot->status == ExtensionOfTime::DRAFT_TEXT )
        {
            $clause = $this->clauseRepository->findItemsWithClauseById(4);

            foreach($eot->attachedClauses as $clauseItem)
            {
                $selectedClauseIds[] = $clauseItem['id'];

                unset( $clauseItem );
            }

            $uploadedFiles = $this->getAttachmentDetails($eot);
        }

        $datesCalculateURL    = route('dates.calculateDates', [ $project->id ]);
        $getAIDeadlineDateURL = route('eot.getAIDeadlineDateURL', [ $project->id ]);

        JavaScript::put(compact('events', 'datesCalculateURL', 'getAIDeadlineDateURL'));

        return View::make('extension_of_times.show', compact('user', 'clause', 'selectedClauseIds', 'eot', 'ai', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Update the specified resource in Extension Of Time.
     *
     * @param $project
     * @param $eotId
     *
     * @return Response
     */
    public function update($project, $eotId)
    {
        $user = $this->user;
        $eot  = $this->eotRepo->find($project, $eotId);

        $inputs = Input::all();

        $this->addNewForm->validate($inputs);

        $inputs['commencement_date_of_event'] = $project->getAppTimeZoneTime($inputs['commencement_date_of_event'] ?? null);

        $eot = $this->eotRepo->update($eot, $user, $inputs);

        \Flash::success("EOT ({$eot->subject}) successfully updated!");

        return Redirect::route('eot', array( $project->id ));
    }

    /**
     * Remove the specified Extension Of Time from storage.
     *
     * @param $project
     * @param $eotId
     *
     * @return Response
     */
    public function destroy($project, $eotId)
    {
        $eot = $this->eotRepo->find($project, $eotId);

        $this->eotRepo->delete($eot);

        \Flash::success("EOT ({$eot->subject}) successfully deleted!");

        return Redirect::route('eot', array( $project->id ));
    }

    public function getDeadLineToComply($project)
    {
        $inputs = Input::all();

        $ai = $this->aiRepo->find($project, $inputs['aiId']);

        // will calculate deadline for submitting the notice of intention to claim EOT
        $ai['new_deadline']   = $this->aiRepo->calculateDeadLine($ai, $ai->project->pam2006Detail->deadline_submitting_notice_of_intention_claim_eot);
        $ai['new_created_at'] = Carbon::parse($ai->created_at)->format('d-M-Y');

        return $ai;
    }

}