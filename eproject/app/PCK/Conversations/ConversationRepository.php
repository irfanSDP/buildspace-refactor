<?php namespace PCK\Conversations;

use Carbon\Carbon;
use PCK\Users\User;
use PCK\Projects\Project;
use Illuminate\Http\Request;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\ContractGroup;

class ConversationRepository extends BaseModuleRepository {

    private $conversation;

    private $request;

    protected $events;

    public function __construct(Conversation $conversation, Request $request, Dispatcher $events)
    {
        $this->conversation = $conversation;
        $this->request      = $request;
        $this->events       = $events;
    }

    public function getUnreadCount(Project $project, ContractGroup $contractGroup, $messageType = Conversation::INBOX)
    {
        $query = $this->conversation
            ->where('project_id', '=', $project->id)
            ->whereHas('viewerGroups', function($q) use ($contractGroup)
            {
                $q->where('contract_group_id', $contractGroup->id);
                $q->where('read', false);
            });

        if( $messageType == Conversation::INBOX )
        {
            $this->buildGetInboxConversationQuery($query, $contractGroup);
        }
        else
        {
            $query->where('status', '=', Conversation::SENT);
            $query->where('send_by_contract_group_id', '=', $contractGroup->id);
        }

        return $query->count();
    }

    public function all(Project $project, ContractGroup $contractGroup)
    {
        $query = $this->conversation
            ->with(array(
                'createdBy', 'viewerGroups' => function($q) use ($contractGroup)
                {
                    $q->where('contract_group_id', $contractGroup->id);
                }
            ))
            ->where('project_id', '=', $project->id)
            ->whereHas('viewerGroups', function($q) use ($contractGroup)
            {
                $q->where('contract_group_id', $contractGroup->id);
            });

        $messageType = $this->request->get('messageType');

        if( $messageType == Conversation::INBOX )
        {
            $this->buildGetInboxConversationQuery($query, $contractGroup);
        }
        elseif( $messageType == Conversation::DRAFT )
        {
            $query->where('status', '=', Conversation::DRAFT);
            $query->where('send_by_contract_group_id', '=', $contractGroup->id);
        }
        else
        {
            $query->where('status', '=', Conversation::SENT);
            $query->where('send_by_contract_group_id', '=', $contractGroup->id);
        }

        // Filter by fields.
        $query->where('subject', 'ilike', '%' . $this->request->get('subject') . '%');
        $query->where('message', 'ilike', '%' . $this->request->get('message') . '%');
        $query->whereHas('createdBy', function($q)
        {
            $q->where('name', 'ilike', '%' . $this->request->get('author') . '%');
        });

        if( ! empty( $searchString = $this->request->get('purpose_of_issue') ) )
        {
            $matches = array();
            foreach(Conversation::getSelectDropDownListing() as $value => $text)
            {
                if( stristr($text, $searchString) ) $matches[] = $value;
            }
            $query->where(function($query) use ($matches)
            {
                $query->whereIn('purpose_of_issued', $matches);

                if( in_array(Conversation::NONE, $matches) ) $query->orWhereNull('purpose_of_issued');
            });
        }

        return $query->orderBy('updated_at', 'desc')->paginate(10);
    }

    public function find(Project $project, $messageId, ContractGroup $contractGroup)
    {
        return $this->conversation
            ->where('id', '=', $messageId)
            ->where('project_id', '=', $project->id)
            ->with('replyMessages.createdBy')
            ->whereHas('viewerGroups', function($q) use ($messageId, $contractGroup)
            {
                $q->where('contract_group_id', '=', $contractGroup->id);
                $q->where('conversation_id', $messageId);
            })
            ->firstOrFail();
    }

    public function add(Project $project, User $user, array $inputs)
    {
        $userGroup = $user->getAssignedCompany($project)->getContractGroup($project);

        $object                            = $this->conversation;
        $object->project_id                = $project->id;
        $object->created_by                = $user->id;
        $object->subject                   = $inputs['subject'];
        $object->message                   = $inputs['message'];
        $object->purpose_of_issued         = ( $inputs['purpose_of_issued'] > 0 ) ? $inputs['purpose_of_issued'] : null;
        $object->status                    = Conversation::SENT;
        $object->send_by_contract_group_id = $userGroup->id;

        if( ! empty( $inputs['deadline_to_reply'] ) )
        {
            $object->deadline_to_reply = $inputs['deadline_to_reply'];
        }

        if( $user->isEditor($project) and isset( $inputs['draft'] ) )
        {
            $object->status = Conversation::DRAFT;
        }

        $object = $this->save($object);

        $sharedGroups = empty( $inputs['to_viewer'] ) ? array() : $inputs['to_viewer'];

        // will add current user's group to the sharedGroups as well
        $sharedGroups[] = $userGroup->id;
        $sharedGroups   = array_unique($sharedGroups);

        // will attach which group(s) that will be receiving this conversation
        $object->viewerGroups()->sync($sharedGroups);

        // update current posted group read status to read if current conversation is not in draft mode
        if( $object->status != Conversation::DRAFT )
        {
            $this->updateReadStatus($object, $userGroup);
        }

        $this->saveAttachments($object, $inputs);

        if( $object->status != Conversation::DRAFT_TEXT )
        {
            $this->sendNotificationsTo($object);
        }

        return $object;
    }

    public function update(Conversation $conversation, User $user, array $inputs)
    {
        $conversation->created_by        = $user->id;
        $conversation->created_at        = Carbon::now();
        $conversation->subject           = $inputs['subject'];
        $conversation->message           = $inputs['message'];
        $conversation->purpose_of_issued = ( $inputs['purpose_of_issued'] > 0 ) ? $inputs['purpose_of_issued'] : null;
        $conversation->status            = Conversation::SENT;

        if( ! empty( $inputs['deadline_to_reply'] ) )
        {
            $conversation->deadline_to_reply = $inputs['deadline_to_reply'];
        }

        if( $user->isEditor($conversation->project) and isset( $inputs['draft'] ) )
        {
            $conversation->status = Conversation::DRAFT;
        }

        $conversation = $this->save($conversation);

        $sharedGroups = empty( $inputs['to_viewer'] ) ? array() : $inputs['to_viewer'];

        // will add current user's group to the sharedGroups as well
        $sharedGroups[] = $conversation->send_by_contract_group_id;
        $sharedGroups   = array_unique($sharedGroups);

        // will attach which group(s) that will be receiving this conversation
        $conversation->viewerGroups()->sync($sharedGroups);

        // update current posted group read status to read if current conversation is not in draft mode
        if( $conversation->status != Conversation::DRAFT )
        {
            $this->updateReadStatus($conversation, $conversation->contractGroup);
        }

        $this->saveAttachments($conversation, $inputs);

        if( $conversation->status != Conversation::DRAFT_TEXT )
        {
            $this->sendNotificationsTo($conversation);
        }

        return $conversation;
    }

    /**
     * Update current conversation's read status to true for current view Conversation.
     * Only will affect current viewer's group
     *
     * @param Conversation  $conversation
     * @param ContractGroup $contractGroup
     */
    public function updateReadStatus(Conversation $conversation, ContractGroup $contractGroup)
    {
        $newConversation = $this->conversation
            ->where('id', '=', $conversation->id)
            ->where('project_id', '=', $conversation->project_id)
            ->with(array(
                'viewerGroups' => function($q) use ($conversation, $contractGroup)
                {
                    $q->where('contract_group_id', '=', $contractGroup->id);
                    $q->where('conversation_id', $conversation->id);
                }
            ))
            ->firstOrFail();

        // will update the read status for current viewed conversation to true
        $this->updatePivotReadStatus($newConversation, true);
    }

    /**
     * Update current conversation's read status to false for newly submitted replies.
     * Only will affect non current poster's group
     *
     * @param Conversation  $conversation
     * @param ContractGroup $contractGroup
     */
    public function updateUnreadStatus(Conversation $conversation, ContractGroup $contractGroup)
    {
        $newConversation = $this->conversation
            ->where('id', '=', $conversation->id)
            ->where('project_id', '=', $conversation->project_id)
            ->with(array(
                'viewerGroups' => function($q) use ($conversation, $contractGroup)
                {
                    $q->where('contract_group_id', '<>', $contractGroup->id);
                    $q->where('conversation_id', $conversation->id);
                }
            ))
            ->firstOrFail();

        // will update the read status for current viewed conversation to false
        $this->updatePivotReadStatus($newConversation, false);
    }

    public function updatePivotReadStatus(Conversation $conversation, $readStatus = false)
    {
        foreach($conversation->viewerGroups as $viewerGroup)
        {
            $viewerGroup->pivot->read = $readStatus;
            $viewerGroup->pivot->save();
        }
    }

    public function getSelectedGroupIds(Conversation $conversation)
    {
        $data   = array();
        $groups = $conversation->viewerGroups;

        foreach($groups as $group)
        {
            $data[] = $group->id;
        }

        return $data;
    }

    private function buildGetInboxConversationQuery(&$query, ContractGroup $contractGroup)
    {
        $query->where('status', '=', Conversation::SENT);
        $query->where('send_by_contract_group_id', '<>', $contractGroup->id);
    }

    private function save(Conversation $object)
    {
        $object->save();

        return $object;
    }

    public function delete(Conversation $conversation)
    {
        $conversation->delete();

        return $conversation;
    }

    public function sendNotificationsTo(Conversation $object, $senderGroupId = null)
    {
        $object->load('viewerGroups');

        $affectedGroups = [];

        foreach($object->viewerGroups as $group)
        {
            // will not send email to the conversation starter
            if( $group->id === $object->send_by_contract_group_id )
            {
                continue;
            }

            // will not send email to the replier as well
            if( isset( $senderGroupId ) and $group->id === $senderGroupId )
            {
                continue;
            }

            // only attach group to non replying group
            $affectedGroups[] = $group->group;
        }

        if( empty( $affectedGroups ) )
        {
            return false;
        }

        $this->sendEmailNotification($object->project, $object, $affectedGroups, 'messaging_inbox', 'projects.show');
        $this->sendSystemNotification($object->project, $object, $affectedGroups, 'messaging_inbox', 'projects.show');

        return true;
    }

}