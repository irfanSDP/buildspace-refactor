<?php namespace PCK\LossOrAndExpenseSecondLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\LossOrAndExpenses\LossOrAndExpense;
use PCK\Forms\LOEMessageSecondLevelArchitectForm;

class LossOrAndExpenseSecondLevelMessageRepository extends BaseModuleRepository {

    private $loeSecondLevelMessage;

    protected $events;

    public function __construct(LossOrAndExpenseSecondLevelMessage $loeSecondLevelMessage, Dispatcher $events)
    {
        $this->loeSecondLevelMessage = $loeSecondLevelMessage;
        $this->events                = $events;
    }

    public function checkLatestMessagePosterRole($loeId)
    {
        return $this->loeSecondLevelMessage->where('loss_or_and_expense_id', '=', $loeId)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function add(User $user, LossOrAndExpense $loe, array $inputs)
    {
        $lastMessage = $this->checkLatestMessagePosterRole($loe->id);

        $message = $this->loeSecondLevelMessage;

        $message->loss_or_and_expense_id = $loe->id;
        $message->created_by             = $user->id;
        $message->subject                = $inputs['subject'];
        $message->message                = $inputs['message'];

        if( $user->hasCompanyProjectRole($loe->project, Role::INSTRUCTION_ISSUER) )
        {
            if( $inputs['decision'] == LossOrAndExpenseSecondLevelMessage::GRANT_DIFF_DEADLINE )
            {
                $message->grant_different_deadline = date('Y-m-d', strtotime($inputs['grant_different_deadline']));
            }
            elseif( $inputs['decision'] == LossOrAndExpenseSecondLevelMessage::EXTEND_DEADLINE )
            {
                $message->grant_different_deadline = date('Y-m-d', strtotime($lastMessage->requested_new_deadline));
            }

            $message->decision = $inputs['decision'];
            $sendToRole        = Role::CONTRACTOR;
        }

        if( $user->hasCompanyProjectRole($loe->project, Role::CONTRACTOR) )
        {
            $message->requested_new_deadline = date('Y-m-d', strtotime($inputs['requested_new_deadline']));
            $sendToRole                      = Role::INSTRUCTION_ISSUER;
        }
        else
        {
            if( $lastMessage )
            {
                $message->requested_new_deadline = date('Y-m-d', strtotime($lastMessage->requested_new_deadline));
            }
        }

        $message->type = $user->getAssignedCompany($loe->project)->getContractGroup($loe->project)->group;

        $message = $this->save($message);

        $this->saveAttachments($message, $inputs);

        $tabId = Helpers::generateTabLink($message->id, LOEMessageSecondLevelArchitectForm::accordianId);

        $this->sendEmailNotification($loe->project, $loe, [ $sendToRole ], 'loss_and_or_expense', 'loe.show', $tabId);
        $this->sendSystemNotification($loe->project, $loe, [ $sendToRole ], 'loss_and_or_expense', 'loe.show', $tabId);

        return $message;
    }

    private function save(LossOrAndExpenseSecondLevelMessage $instance)
    {
        $instance->save();

        return $instance;
    }

}