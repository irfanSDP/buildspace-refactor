<?php namespace PCK\LossOrAndExpenseFourthLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\LossOrAndExpenses\LossOrAndExpense;
use PCK\Forms\LOEMessageFourthLevelArchitectQsForm;

class LossOrAndExpenseFourthLevelMessageRepository extends BaseModuleRepository {

    private $loeExpenseFourthLevelMessage;

    protected $events;

    public function __construct(LossOrAndExpenseFourthLevelMessage $loeExpenseFourthLevelMessage, Dispatcher $events)
    {
        $this->loeExpenseFourthLevelMessage = $loeExpenseFourthLevelMessage;
        $this->events                       = $events;
    }

    public function checkLatestMessagePosterRole($loeId)
    {
        return $this->loeExpenseFourthLevelMessage->where('loss_or_and_expense_id', '=', $loeId)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function checkLatestMessageByArchitect($loeId)
    {
        return $this->loeExpenseFourthLevelMessage->where('loss_or_and_expense_id', '=', $loeId)
            ->where('type', '=', Role::INSTRUCTION_ISSUER)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function add(User $user, LossOrAndExpense $loe, array $inputs)
    {
        $message    = $this->loeExpenseFourthLevelMessage;
        $sendToRole = [ Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER ];

        $message->loss_or_and_expense_id = $loe->id;
        $message->created_by             = $user->id;
        $message->subject                = $inputs['subject'];
        $message->message                = $inputs['message'];

        if( $user->hasCompanyProjectRole($loe->project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER )) )
        {
            if( $inputs['decision'] == LossOrAndExpenseFourthLevelMessage::GRANT_DIFF_AMOUNT )
            {
                $message->grant_different_amount = $inputs['grant_different_amount'];

                if( $user->hasCompanyProjectRole($loe->project, Role::INSTRUCTION_ISSUER) and $this->exceedClaimAmount($loe, $message) )
                {
                    $message->locked = true;
                }
            }
            elseif( $inputs['decision'] == LossOrAndExpenseFourthLevelMessage::GRANT )
            {
                $message->grant_different_amount = $loe->lossOrAndExpenseClaim->final_claim_amount;

                if( $user->hasCompanyProjectRole($loe->project, Role::INSTRUCTION_ISSUER) )
                {
                    $message->locked = true;
                }
            }

            $message->decision = $inputs['decision'];
        }

        $message->type = $user->getAssignedCompany($loe->project)->getContractGroup($loe->project)->group;

        $message = $this->save($message);

        $this->saveAttachments($message, $inputs);

        if( $message->type == Role::CLAIM_VERIFIER )
        {
            $sendToRole = [ Role::INSTRUCTION_ISSUER ];
        }
        elseif( $message->type == Role::INSTRUCTION_ISSUER )
        {
            $sendToRole = [ Role::CONTRACTOR, Role::CLAIM_VERIFIER ];
        }

        $tabId = Helpers::generateTabLink($message->id, LOEMessageFourthLevelArchitectQsForm::accordianId);

        $this->sendEmailNotification($loe->project, $loe, $sendToRole, 'loss_and_or_expense', 'loe.show', $tabId);
        $this->sendSystemNotification($loe->project, $loe, $sendToRole, 'loss_and_or_expense', 'loe.show', $tabId);

        return $message;
    }

    private function save(LossOrAndExpenseFourthLevelMessage $instance)
    {
        $instance->save();

        return $instance;
    }

    private function exceedClaimAmount(LossOrAndExpense $loe, LossOrAndExpenseFourthLevelMessage $message)
    {
        return $message->grant_different_amount >= $loe->lossOrAndExpenseClaim->final_claim_amount;
    }

}