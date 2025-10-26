<?php

use PCK\ContractGroups\Types\Role;
use PCK\Calendars\CalendarRepository;
use PCK\Forms\AddNewEngineerInstructionForm;
use PCK\Forms\EngineerInstructionArchitectUpdateForm;
use PCK\EngineerInstructions\EngineerInstructionRepository;
use PCK\ArchitectInstructions\ArchitectInstructionRepository;

class EngineerInstructionsController extends \BaseController {

    private $eiRepo;

    private $aiRepo;

    private $calendarRepository;

    private $addForm;

    private $eiArchitectUpdateForm;

    public function __construct(
        EngineerInstructionRepository $eiRepo,
        ArchitectInstructionRepository $aiRepo,
        CalendarRepository $calendarRepository,
        AddNewEngineerInstructionForm $addForm,
        EngineerInstructionArchitectUpdateForm $eiArchitectUpdateForm
    )
    {
        $this->eiRepo                = $eiRepo;
        $this->aiRepo                = $aiRepo;
        $this->addForm               = $addForm;
        $this->calendarRepository    = $calendarRepository;
        $this->eiArchitectUpdateForm = $eiArchitectUpdateForm;
        $this->user                  = \Confide::user();
    }

    /**
     * Display a listing of the Engineer Instruction.
     *
     * @param $project
     *
     * @return Response
     */
    public function index($project)
    {
        $user = $this->user;
        $eis  = $this->eiRepo->all($project);

        return View::make('engineer_instructions.index', compact('project', 'user', 'eis'));
    }

    /**
     * Show the form for creating a new Engineer Instruction.
     *
     * @param $project
     *
     * @return Response
     */
    public function create($project)
    {
        $user          = $this->user;
        $events        = $this->calendarRepository->getEventsListing($project);
        $isEditor      = $user->isEditor($project);
        $uploadedFiles = $this->getAttachmentDetails();

        JavaScript::put(compact('events'));

        return View::make('engineer_instructions.create', compact('user', 'project', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Store a newly created Engineer Instruction in storage.
     *
     * @param $project
     *
     * @return Response
     */
    public function store($project)
    {
        $user = $this->user;

        $inputs = Input::all();

        $this->addForm->validate($inputs);

        $inputs['deadline_to_comply_with'] = $project->getAppTimeZoneTime($inputs['deadline_to_comply_with'] ?? null);

        $ei = $this->eiRepo->add($project, $user, $inputs);

        \Flash::success("New EI ({$ei->subject}) successfully added!");

        return Redirect::route('ei', array( $project->id ));
    }

    /**
     * Display the specified Engineer Instruction.
     *
     * @param $project
     * @param $eiId
     *
     * @return Response
     */
    public function show($project, $eiId)
    {
        $user          = $this->user;
        $ei            = $this->eiRepo->find($project, $eiId);
        $events        = $this->calendarRepository->getEventsListing($ei->project);
        $isEditor      = $user->isEditor($project);
        $uploadedFiles = $this->getAttachmentDetails($ei);
        $selectedAIIds = array();
        $ais           = array();

        if( $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
        {
            $ais           = $this->aiRepo->getWithStatusNotDraft($project);
            $selectedAIIds = $this->eiRepo->getAffectedArchitectInstructionIds($ei);
        }

        JavaScript::put(compact('events'));

        return View::make('engineer_instructions.show', compact('user', 'ei', 'ais', 'selectedAIIds', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Update the specified Engineer Instruction in storage.
     *
     * @param $project
     * @param $eiId
     *
     * @return Response
     */
    public function update($project, $eiId)
    {
        $user = $this->user;
        $ei   = $this->eiRepo->find($project, $eiId);

        $inputs = Input::all();

        $this->addForm->validate($inputs);

        $inputs['deadline_to_comply_with'] = $project->getAppTimeZoneTime($inputs['deadline_to_comply_with'] ?? null);

        $ei = $this->eiRepo->update($ei, $user, $inputs);

        \Flash::success("EI ({$ei->subject}) successfully updated!");

        return Redirect::route('ei', array( $project->id ));
    }

    /**
     * Update the specified Engineer Instruction's record to attach AI in storage.
     *
     * @param $project
     * @param $eiId
     *
     * @return Response
     */
    public function architectUpdate($project, $eiId)
    {
        $ei = $this->eiRepo->find($project, $eiId);

        $inputs = Input::all();

        $this->eiArchitectUpdateForm->validate($inputs);

        $ei = $this->eiRepo->updateAILink($ei, $inputs);

        \Flash::success("EI ({$ei->subject}) successfully updated!");

        return Redirect::route('ei', array( $project->id ));
    }

    /**
     * Remove the specified Engineer Instruction from storage.
     *
     * @param $project
     * @param $eiId
     *
     * @return Response
     */
    public function destroy($project, $eiId)
    {
        $ei = $this->eiRepo->find($project, $eiId);

        $this->eiRepo->delete($ei);

        \Flash::success("EI ({$ei->subject}) successfully deleted!");

        return Redirect::route('ei', array( $project->id ));
    }

}