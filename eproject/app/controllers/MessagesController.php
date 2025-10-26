<?php

use PCK\Conversations\Conversation;
use PCK\Forms\ConversationReplyForm;
use PCK\Forms\AddNewConversationForm;
use PCK\Calendars\CalendarRepository;
use PCK\Conversations\ConversationRepository;
use PCK\ContractGroups\ContractGroupRepository;
use PCK\ConversationReplyMessages\ConversationReplyMessageRepository;

class MessagesController extends \BaseController {

    private $contractGroupRepo;
    private $conversationRepo;
    private $conversationReplyMessageRepo;
    private $calendarRepo;
    private $addNewConversationForm;
    private $conversationReplyForm;

    public function __construct(
        ContractGroupRepository $contractGroupRepo,
        ConversationRepository $conversationRepo,
        ConversationReplyMessageRepository $conversationReplyMessageRepo,
        CalendarRepository $calendarRepo,
        AddNewConversationForm $addNewConversationForm,
        ConversationReplyForm $conversationReplyForm
    )
    {
        $this->contractGroupRepo            = $contractGroupRepo;
        $this->conversationRepo             = $conversationRepo;
        $this->conversationReplyMessageRepo = $conversationReplyMessageRepo;
        $this->calendarRepo                 = $calendarRepo;
        $this->addNewConversationForm       = $addNewConversationForm;
        $this->conversationReplyForm        = $conversationReplyForm;
    }

    public function foldersUnreadMessageCount($project)
    {
        $user          = Confide::user();
        $contractGroup = $user->getAssignedCompany($project)->getContractGroup($project);
        $inboxCounts   = $this->conversationRepo->getUnreadCount($project, $contractGroup);
        $sendCounts    = $this->conversationRepo->getUnreadCount($project, $contractGroup, Conversation::SENT);

        return Response::json(compact('inboxCounts', 'sendCounts'));
    }

    public function index($project)
    {
        $user          = Confide::user();
        $contractGroup = $user->getAssignedCompany($project)->getContractGroup($project);

        $conversations = $this->conversationRepo->all($project, $contractGroup);

        return View::make('messages.partials.listing', compact('conversations'))->render();
    }

    /**
     * Show the form for creating a new Message.
     *
     * @param $project
     *
     * @return Response
     */
    public function create($project)
    {
        $user      = \Confide::user();
        $userGroup = $user->getAssignedCompany($project)->getContractGroup($project);
        $groups    = $project->getAssignedGroups(array( $userGroup->group ));
        $events    = $this->calendarRepo->getEventsListing($project);

        JavaScript::put(compact('events'));

        return View::make('messages.create', compact('user', 'project', 'userGroup', 'groups'))->render();
    }

    /**
     * Store a newly created Message in storage.
     *
     * @param $project
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($project)
    {
        $user   = Confide::user();
        $inputs = Input::all();

        $inputs['deadline_to_reply'] = $project->getAppTimeZoneTime($inputs['deadline_to_reply'] ?? null);

        $this->addNewConversationForm->validate($inputs);

        $this->conversationRepo->add($project, $user, $inputs);

        $success = true;

        return Response::json(compact('success'));
    }

    /**
     * Display the specified Message.
     *
     * @param $project
     * @param $messageId
     *
     * @return Response
     */
    public function show($project, $messageId)
    {
        $user          = \Confide::user();
        $contractGroup = $user->getAssignedCompany($project)->getContractGroup($project);
        $conversation  = $this->conversationRepo->find($project, $messageId, $contractGroup);

        $this->conversationRepo->updateReadStatus($conversation, $contractGroup);

        return View::make('messages.show', compact('project', 'user', 'conversation'))->render();
    }

    /**
     * Show the form for editing the specified Message.
     *
     * @param $project
     * @param $messageId
     *
     * @return Response
     */
    public function edit($project, $messageId)
    {
        $user             = \Confide::user();
        $userGroup        = $user->getAssignedCompany($project)->getContractGroup($project);
        $groups           = $project->getAssignedGroups(array( $userGroup->group ));
        $conversation     = $this->conversationRepo->find($project, $messageId, $userGroup);
        $selectedGroupIds = $this->conversationRepo->getSelectedGroupIds($conversation);
        $events           = $this->calendarRepo->getEventsListing($project);
        $uploadedFiles    = $this->getAttachmentDetails($conversation);

        JavaScript::put(compact('events'));

        return View::make('messages.edit', compact('user', 'project', 'conversation', 'userGroup', 'groups', 'selectedGroupIds', 'uploadedFiles'))->render();
    }

    /**
     * Update the specified Message in storage.
     *
     * @param $project
     * @param $messageId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($project, $messageId)
    {
        $user         = Confide::user();
        $userGroup    = $user->getAssignedCompany($project)->getContractGroup($project);
        $conversation = $this->conversationRepo->find($project, $messageId, $userGroup);
        $inputs       = Input::all();

        $inputs['deadline_to_reply'] = $project->getAppTimeZoneTime($inputs['deadline_to_reply'] ?? null);

        $this->addNewConversationForm->validate($inputs);

        $this->conversationRepo->update($conversation, $user, $inputs);

        $success = true;

        return Response::json(compact('success'));
    }

    public function replyMessage($project, $messageId)
    {
        $user          = \Confide::user();
        $contractGroup = $user->getAssignedCompany($project)->getContractGroup($project);
        $conversation  = $this->conversationRepo->find($project, $messageId, $contractGroup);
        $inputs        = Input::all();
        $isNewMessage  = true;

        $this->conversationReplyForm->validate($inputs);

        $replyMessage = $this->conversationReplyMessageRepo->add($conversation, $user, $inputs);

        $this->conversationRepo->updateUnreadStatus($conversation, $contractGroup);

        return View::make('messages.partials.reply', compact('replyMessage', 'isNewMessage'))->render();
    }

    /**
     * Remove the specified Message from storage.
     *
     * @param $project
     * @param $messageId
     *
     * @return Response
     */
    public function destroy($project, $messageId)
    {
        $user          = \Confide::user();
        $contractGroup = $user->getAssignedCompany($project)->getContractGroup($project);
        $conversation  = $this->conversationRepo->find($project, $messageId, $contractGroup);

        $this->conversationRepo->delete($conversation);

        return Redirect::route('projects.show', $project->id);
    }

}