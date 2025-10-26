<?php

use PCK\Clauses\ClauseRepository;
use PCK\Calendars\CalendarRepository;
use PCK\Forms\AddNewArchitectInstructionForm;
use PCK\ArchitectInstructions\ArchitectInstruction;
use PCK\ArchitectInstructions\ArchitectInstructionRepository;

class ArchitectInstructionsController extends \BaseController {

    private $aiRepo;

    private $clauseRepository;

    private $calendarRepository;

    private $addForm;

    private $user;

    public function __construct(
        ArchitectInstructionRepository $aiRepo,
        ClauseRepository $clauseRepository,
        CalendarRepository $calendarRepository,
        AddNewArchitectInstructionForm $addForm
    )
    {
        $this->aiRepo             = $aiRepo;
        $this->clauseRepository   = $clauseRepository;
        $this->calendarRepository = $calendarRepository;
        $this->addForm            = $addForm;
        $this->user               = Confide::user();
    }

    /**
     * Display a listing of the Architect Instructions by DESC order
     *
     * @param $project
     *
     * @return Response
     */
    public function index($project)
    {
        $user = $this->user;
        $ais  = $this->aiRepo->all($project);

        return View::make('architect_instructions.index', compact('project', 'user', 'ais'));
    }

    /**
     * Show the form for creating a new Architect Instruction.
     *
     * @param $project
     *
     * @return Response
     */
    public function create($project)
    {
        $user          = $this->user;
        $clause        = $this->clauseRepository->findItemsWithClauseById(1);
        $events        = $this->calendarRepository->getEventsListing($project, $project->pam2006Detail->min_days_to_comply_with_ai);
        $isEditor      = $user->isEditor($project);
        $uploadedFiles = $this->getAttachmentDetails();

        JavaScript::put(compact('events'));

        return View::make('architect_instructions.create', compact('project', 'clause', 'user', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Store a newly created Architect Instruction in storage.
     *
     * @param $project
     *
     * @return Response
     */
    public function store($project)
    {
        $inputs = Input::all();

        $this->addForm->setProject($project);
        $this->addForm->setMinDaysToComplyFromToday($project->pam2006Detail->min_days_to_comply_with_ai);
        $this->addForm->validate($inputs);
        
        $inputs['deadline_to_comply']   = $project->getAppTimeZoneTime($inputs['deadline_to_comply'] ?? null);

        $ai = $this->aiRepo->add($project, $inputs);

        Flash::success("New AI ({$ai->reference}) successfully added!");

        return Redirect::route('ai', array( $project->id ));
    }

    /**
     * Display the specified Architect Instruction.
     *
     * @param $project
     * @param $aiId
     *
     * @return Response
     */
    public function show($project, $aiId)
    {
        $user              = $this->user;
        $ai                = $this->aiRepo->findWithMessages($project, $aiId);
        $events            = $this->calendarRepository->getEventsListing($ai->project, $ai->project->pam2006Detail->min_days_to_comply_with_ai);
        $isEditor          = $user->isEditor($project);
        $clause            = array();
        $selectedClauseIds = array();
        $uploadedFiles     = array();

        if( $ai->status == ArchitectInstruction::DRAFT_TEXT )
        {
            $clause = $this->clauseRepository->findItemsWithClauseById(1);

            foreach($ai->attachedClauses as $clauseItem)
            {
                $selectedClauseIds[] = $clauseItem['origin_id'];

                unset( $clauseItem );
            }

            $uploadedFiles = $this->getAttachmentDetails($ai);
        }

        JavaScript::put(compact('events'));

        return View::make('architect_instructions.show', compact('ai', 'clause', 'selectedClauseIds', 'uploadedFiles', 'user', 'isEditor'));
    }

    /**
     * Update the specified Architect Instruction in storage.
     *
     * @param $project
     * @param $aiId
     *
     * @return Response
     */
    public function update($project, $aiId)
    {
        $user = $this->user;
        $ai   = $this->aiRepo->find($project, $aiId);

        $inputs = Input::all();
        
        $this->addForm->setProject($ai->project);
        $this->addForm->setMinDaysToComplyFromToday($ai->project->pam2006Detail->min_days_to_comply_with_ai);
        $this->addForm->validate($inputs);

        $inputs['deadline_to_comply'] = $project->getAppTimeZoneTime($inputs['deadline_to_comply'] ?? null);
        
        $this->aiRepo->update($user, $ai, $inputs);

        Flash::success("AI ({$ai->reference}) has been successfully updated!");

        return Redirect::route('ai', array( $project->id ));
    }

    /**
     * Remove the specified Architect Instruction from storage.
     *
     * @param $project
     * @param $aiId
     *
     * @return Response
     */
    public function destroy($project, $aiId)
    {
        $ai = $this->aiRepo->find($project, $aiId);

        $this->aiRepo->delete($ai);

        \Flash::success("AI ({$ai->reference}) has been successfully deleted!");

        return Redirect::route('ai', array( $project->id ));
    }

}