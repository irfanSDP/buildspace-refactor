<?php namespace PCK\ConversationReplyMessages;

use PCK\Users\User;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\Conversations\Conversation;
use PCK\Conversations\ConversationRepository;

class ConversationReplyMessageRepository extends BaseModuleRepository {

    private $crMessage;

    private $conversationRepo;

    protected $events;

    public function __construct
    (
        ConversationReplyMessage $crMessage,
        ConversationRepository $conversationRepo,
        Dispatcher $events
    )
    {
        $this->crMessage        = $crMessage;
        $this->conversationRepo = $conversationRepo;
        $this->events           = $events;
    }

    public function add(Conversation $conversation, User $user, array $inputs)
    {
        $object                  = $this->crMessage;
        $object->conversation_id = $conversation->id;
        $object->created_by      = $user->id;
        $object->message         = $inputs['message'];
        $object->status          = 1;

        $object = $this->save($object);

        $this->saveAttachments($object, $inputs);

        // load back newly saved attachments
        $object->load('attachments');

        $contractGroup = $user->getAssignedCompany($conversation->project)->getContractGroup($conversation->project);

        // send notification to the conversation starter
        if( $conversation->send_by_contract_group_id != $contractGroup->id )
        {
            $this->sendEmailNotification($conversation->project, $conversation, [ $conversation->contractGroup->group ], 'messaging_sent', 'projects.show');
            $this->sendSystemNotification($conversation->project, $conversation, [ $conversation->contractGroup->group ], 'messaging_sent', 'projects.show');
        }

        $this->conversationRepo->sendNotificationsTo($conversation, $contractGroup->id);

        return $object;
    }

    public function save(ConversationReplyMessage $object)
    {
        $object->save();

        return $object;
    }

}