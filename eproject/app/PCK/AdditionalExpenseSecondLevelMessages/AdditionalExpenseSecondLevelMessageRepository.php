<?php namespace PCK\AdditionalExpenseSecondLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\AdditionalExpenses\AdditionalExpense;
use PCK\Forms\AEMessageSecondLevelArchitectForm;

class AdditionalExpenseSecondLevelMessageRepository extends BaseModuleRepository {

    private $aeSecondLevelMessage;

    protected $events;

    public function __construct(AdditionalExpenseSecondLevelMessage $aeSecondLevelMessage, Dispatcher $events)
    {
        $this->aeSecondLevelMessage = $aeSecondLevelMessage;
        $this->events               = $events;
    }

    public function checkLatestMessagePosterRole($aeId)
    {
        return $this->aeSecondLevelMessage->where('additional_expense_id', '=', $aeId)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function add(User $user, AdditionalExpense $ae, array $inputs)
    {
        $lastMessage = $this->checkLatestMessagePosterRole($ae->id);
        $message     = $this->aeSecondLevelMessage;
        $sendToRole  = Role::INSTRUCTION_ISSUER;

        $message->additional_expense_id = $ae->id;
        $message->created_by            = $user->id;
        $message->subject               = $inputs['subject'];
        $message->message               = $inputs['message'];

        if( $user->hasCompanyProjectRole($ae->project, Role::INSTRUCTION_ISSUER) )
        {
            if( $inputs['decision'] == AdditionalExpenseSecondLevelMessage::GRANT_DIFF_DEADLINE )
            {
                $message->grant_different_deadline = date('Y-m-d', strtotime($inputs['grant_different_deadline']));
            }
            elseif( $inputs['decision'] == AdditionalExpenseSecondLevelMessage::EXTEND_DEADLINE )
            {
                $message->grant_different_deadline = date('Y-m-d', strtotime($lastMessage->requested_new_deadline));
            }

            $message->decision = $inputs['decision'];
            $sendToRole        = Role::CONTRACTOR;
        }

        if( $user->hasCompanyProjectRole($ae->project, Role::CONTRACTOR) )
        {
            $message->requested_new_deadline = date('Y-m-d', strtotime($inputs['requested_new_deadline']));
        }
        else
        {
            if( $lastMessage )
            {
                $message->requested_new_deadline = date('Y-m-d', strtotime($lastMessage->requested_new_deadline));
            }
        }

        $message->type = $user->getAssignedCompany($ae->project)->getContractGroup($ae->project)->group;

        $message = $this->save($message);

        $this->saveAttachments($message, $inputs);

        $tabId = Helpers::generateTabLink($message->id, AEMessageSecondLevelArchitectForm::accordianId);

        $this->sendEmailNotification($ae->project, $ae, [ $sendToRole ], 'additional_expense', 'ae.show', $tabId);
        $this->sendSystemNotification($ae->project, $ae, [ $sendToRole ], 'additional_expense', 'ae.show', $tabId);

        return $message;
    }

    private function save(AdditionalExpenseSecondLevelMessage $instance)
    {
        $instance->save();

        return $instance;
    }

}