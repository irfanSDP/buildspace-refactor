<?php

use Carbon\Carbon;
use PCK\Clauses\ClauseRepository;
use PCK\Calendars\CalendarRepository;
use PCK\Forms\AddNewLossAndOrExpenseForm;
use PCK\LossOrAndExpenses\LossOrAndExpense;
use PCK\LossOrAndExpenses\LossOrAndExpenseRepository;
use PCK\ArchitectInstructions\ArchitectInstructionRepository;

class LossAndOrExpensesController extends \BaseController {

    private $aiRepo;

    private $loeRepo;

    private $clauseRepository;

    private $calendarRepository;

    private $user;

    private $addNewForm;

    public function __construct(
        ArchitectInstructionRepository $aiRepo,
        LossOrAndExpenseRepository $loeRepo,
        ClauseRepository $clauseRepository,
        CalendarRepository $calendarRepository,
        AddNewLossAndOrExpenseForm $addNewForm
    )
    {
        $this->aiRepo             = $aiRepo;
        $this->loeRepo            = $loeRepo;
        $this->clauseRepository   = $clauseRepository;
        $this->calendarRepository = $calendarRepository;
        $this->addNewForm         = $addNewForm;
        $this->user               = \Confide::user();
    }

    /**
     * Display a listing of the Loss Or/And Expense.
     *
     * @param $project
     *
     * @return Response
     */
    public function index($project)
    {
        $user = $this->user;
        $loes = $this->loeRepo->all($project);

        return View::make('loss_and_or_expenses.index', compact('project', 'user', 'loes'));
    }

    /**
     * Show the form for creating a new Loss Or/And Expense.
     *
     * @param $project
     * @param $aiId
     *
     * @return Response
     */
    public function create($project, $aiId = null)
    {
        $user          = $this->user;
        $clause        = $this->clauseRepository->findItemsWithClauseById(2);
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
        $getAIDeadlineDateURL = route('loe.getAIDeadlineDateURL', [ $project->id ]);

        JavaScript::put(compact('events', 'datesCalculateURL', 'getAIDeadlineDateURL'));

        return View::make('loss_and_or_expenses.create', compact('user', 'project', 'clause', 'ai', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Store a newly created Loss Or/And Expense in storage.
     *
     * @param      $project
     * @param null $aiId
     *
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

        $loe = $this->loeRepo->add($project, $user, $inputs);

        \Flash::success("New L &amp; E ({$loe->subject}) successfully added!");

        return Redirect::route('loe', array( $project->id ));
    }

    /**
     * Display the specified Loss Or/And Expense.
     *
     * @param $project
     * @param $loeId
     *
     * @return Response
     */
    public function show($project, $loeId)
    {
        $user              = $this->user;
        $loe               = $this->loeRepo->findWithMessages($project, $loeId);
        $ai                = $this->aiRepo->selectList($project);
        $events            = $this->calendarRepository->getEventsListing($loe->project);
        $isEditor          = $user->isEditor($project);
        $uploadedFiles     = array();
        $clause            = array();
        $selectedClauseIds = array();

        if( $loe->status == LossOrAndExpense::DRAFT_TEXT )
        {
            $clause = $this->clauseRepository->findItemsWithClauseById(2);

            foreach($loe->attachedClauses as $clauseItem)
            {
                $selectedClauseIds[] = $clauseItem['id'];

                unset( $clauseItem );
            }

            $uploadedFiles = $this->getAttachmentDetails($loe);
        }

        $fourthMessageRepo          = App::make('PCK\LossOrAndExpenseFourthLevelMessages\LossOrAndExpenseFourthLevelMessageRepository');
        $lastArchitectFourthMessage = $fourthMessageRepo->checkLatestMessageByArchitect($loe->id);

        $datesCalculateURL    = route('dates.calculateDates', [ $project->id ]);
        $getAIDeadlineDateURL = route('loe.getAIDeadlineDateURL', [ $project->id ]);

        JavaScript::put(compact('events', 'datesCalculateURL', 'getAIDeadlineDateURL'));

        return View::make('loss_and_or_expenses.show', compact('user', 'clause', 'selectedClauseIds', 'loe', 'ai', 'lastArchitectFourthMessage', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Update the specified Loss Or/And Expense in storage.
     *
     * @param $project
     * @param $loeId
     *
     * @return Response
     */
    public function update($project, $loeId)
    {
        $user = $this->user;
        $loe  = $this->loeRepo->find($project, $loeId);

        $inputs = Input::all();

        $this->addNewForm->validate($inputs);

        $inputs['commencement_date_of_event'] = $project->getAppTimeZoneTime($inputs['commencement_date_of_event'] ?? null);

        $loe = $this->loeRepo->update($loe, $user, $inputs);

        \Flash::success("L &amp; E ({$loe->subject}) successfully updated!");

        return Redirect::route('loe', array( $project->id ));
    }

    /**
     * Remove the specified Loss Or/And Expense from storage.
     *
     * @param $project
     * @param $loeId
     *
     * @return Response
     */
    public function destroy($project, $loeId)
    {
        $loe = $this->loeRepo->find($project, $loeId);

        $this->loeRepo->delete($loe);

        \Flash::success("L &amp; E ({$loe->subject}) successfully deleted!");

        return Redirect::route('loe', array( $project->id ));
    }

    public function getDeadLineToComply($project)
    {
        $inputs = Input::all();

        $ai = $this->aiRepo->find($project, $inputs['aiId']);

        // will calculate deadline for submitting the notice of intention to claim L and E
        $ai['new_deadline']   = $this->aiRepo->calculateDeadLine($ai, $ai->project->pam2006Detail->deadline_submitting_note_of_intention_claim_l_and_e);
        $ai['new_created_at'] = Carbon::parse($ai->created_at)->format('d-M-Y');

        return $ai;
    }

}