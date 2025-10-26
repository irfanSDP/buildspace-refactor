<?php

use Carbon\Carbon;
use PCK\Clauses\ClauseRepository;
use PCK\Calendars\CalendarRepository;
use PCK\Forms\AddNewAdditionalExpenseForm;
use PCK\AdditionalExpenses\AdditionalExpense;
use PCK\AdditionalExpenses\AdditionalExpenseRepository;
use PCK\ArchitectInstructions\ArchitectInstructionRepository;

class AdditionalExpensesController extends \BaseController {

    private $aiRepo;

    private $aeRepo;

    private $clauseRepository;

    private $calendarRepository;

    private $addNewForm;

    public function __construct(
        ArchitectInstructionRepository $aiRepo,
        AdditionalExpenseRepository $aeRepo,
        ClauseRepository $clauseRepository,
        CalendarRepository $calendarRepository,
        AddNewAdditionalExpenseForm $addNewForm
    )
    {
        $this->aiRepo             = $aiRepo;
        $this->aeRepo             = $aeRepo;
        $this->clauseRepository   = $clauseRepository;
        $this->addNewForm         = $addNewForm;
        $this->calendarRepository = $calendarRepository;
        $this->user               = \Confide::user();
    }

    /**
     * Display a listing of the Additional Expense.
     *
     * @param $project
     *
     * @return Response
     */
    public function index($project)
    {
        $user = $this->user;
        $aes  = $this->aeRepo->all($project);

        return View::make('additional_expenses.index', compact('project', 'user', 'aes'));
    }

    /**
     * Show the form for creating a new Additional Expense.
     *
     * @param      $project
     * @param null $aeId
     *
     * @return Response
     */
    public function create($project, $aeId = null)
    {
        $user          = $this->user;
        $clause        = $this->clauseRepository->findItemsWithClauseById(3);
        $events        = $this->calendarRepository->getEventsListing($project);
        $isEditor      = $user->isEditor($project);
        $uploadedFiles = $this->getAttachmentDetails();

        if( $aeId )
        {
            $ai = $this->aiRepo->find($project, $aeId);
        }
        else
        {
            $ai = $this->aiRepo->selectList($project);
        }

        $datesCalculateURL    = route('dates.calculateDates', [ $project->id ]);
        $getAIDeadlineDateURL = route('ae.getAIDeadlineDateURL', [ $project->id ]);

        JavaScript::put(compact('events', 'datesCalculateURL', 'getAIDeadlineDateURL'));

        return View::make('additional_expenses.create', compact('user', 'project', 'clause', 'ai', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Store a newly created Additional Expense in storage.
     *
     * @param      $project
     * @param null $aeId
     *
     * @return Response
     */
    public function store($project, $aeId = null)
    {
        $user = $this->user;

        if( $aeId )
        {
            $this->aiRepo->find($project, $aeId);
        }

        $inputs = Input::all();

        $this->addNewForm->validate($inputs);

        $inputs['commencement_date_of_event'] = $project->getAppTimeZoneTime($inputs['commencement_date_of_event'] ?? null);

        $ae = $this->aeRepo->add($project, $user, $inputs);

        \Flash::success("New AE ({$ae->subject}) successfully added!");

        return Redirect::route('ae', array( $project->id ));
    }

    /**
     * Display the specified Additional Expense.
     *
     * @param $project
     * @param $aeId
     *
     * @return Response
     */
    public function show($project, $aeId)
    {
        $user              = $this->user;
        $ae                = $this->aeRepo->findWithMessages($project, $aeId);
        $ai                = $this->aiRepo->selectList($project);
        $events            = $this->calendarRepository->getEventsListing($ae->project);
        $isEditor          = $user->isEditor($project);
        $clause            = array();
        $uploadedFiles     = array();
        $selectedClauseIds = array();

        if( $ae->status == AdditionalExpense::DRAFT_TEXT )
        {
            $clause = $this->clauseRepository->findItemsWithClauseById(3);

            foreach($ae->attachedClauses as $clauseItem)
            {
                $selectedClauseIds[] = $clauseItem['id'];

                unset( $clauseItem );
            }

            $uploadedFiles = $this->getAttachmentDetails($ae);
        }

        $fourthMessageRepo          = App::make('PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessageRepository');
        $lastArchitectFourthMessage = $fourthMessageRepo->checkLatestMessageByArchitect($ae->id);

        $datesCalculateURL    = route('dates.calculateDates', [ $project->id ]);
        $getAIDeadlineDateURL = route('ae.getAIDeadlineDateURL', [ $project->id ]);

        JavaScript::put(compact('events', 'datesCalculateURL', 'getAIDeadlineDateURL'));

        return View::make('additional_expenses.show', compact('user', 'clause', 'selectedClauseIds', 'ae', 'ai', 'lastArchitectFourthMessage', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Update the specified Additional Expense in storage.
     *
     * @param $project
     * @param $aeId
     *
     * @throws \Laracasts\Validation\FormValidationException
     * @return Response
     */
    public function update($project, $aeId)
    {
        $user = $this->user;
        $ae   = $this->aeRepo->find($project, $aeId);

        $inputs = Input::all();

        $this->addNewForm->validate($inputs);

        $inputs['commencement_date_of_event'] = $project->getAppTimeZoneTime($inputs['commencement_date_of_event'] ?? null);

        $ae = $this->aeRepo->update($ae, $user, $inputs);

        \Flash::success("AE ({$ae->subject}) successfully updated!");

        return Redirect::route('ae', array( $project->id ));
    }

    /**
     * Remove the specified Additional Expense from storage.
     *
     * @param $project
     * @param $aeId
     *
     * @return Response
     */
    public function destroy($project, $aeId)
    {
        $ae = $this->aeRepo->find($project, $aeId);

        $this->aeRepo->delete($ae);

        \Flash::success("AE ({$ae->subject}) successfully deleted!");

        return Redirect::route('ae', array( $project->id ));
    }

    public function getDeadLineToComply($project)
    {
        $inputs = Input::all();

        $ai = $this->aiRepo->find($project, $inputs['aiId']);

        // will calculate deadline for submitting the notice of intention to claim L and E
        $ai['new_deadline']   = $this->aiRepo->calculateDeadLine($ai, $ai->project->pam2006Detail->deadline_submitting_note_of_intention_claim_ae);
        $ai['new_created_at'] = Carbon::parse($ai->created_at)->format('d-M-Y');

        return $ai;
    }

}