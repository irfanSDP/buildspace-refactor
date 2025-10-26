<?php namespace PCK\AdditionalExpenseFourthLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\AdditionalExpenses\AdditionalExpense;
use PCK\Forms\AEMessageFourthLevelArchitectQsForm;

class AdditionalExpenseFourthLevelMessageRepository extends BaseModuleRepository {

    private $aeExpenseFourthLevelMessage;

    protected $events;

    public function __construct(AdditionalExpenseFourthLevelMessage $aeExpenseFourthLevelMessage, Dispatcher $events)
    {
        $this->aeExpenseFourthLevelMessage = $aeExpenseFourthLevelMessage;
        $this->events                      = $events;
    }

    public function checkLatestMessagePosterRole($aeId)
    {
        return $this->aeExpenseFourthLevelMessage->where('additional_expense_id', '=', $aeId)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function checkLatestMessageByArchitect($aeId)
    {
        return $this->aeExpenseFourthLevelMessage->where('additional_expense_id', '=', $aeId)
            ->where('type', '=', Role::INSTRUCTION_ISSUER)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function add(User $user, AdditionalExpense $ae, array $inputs)
    {
        $message    = $this->aeExpenseFourthLevelMessage;
        $sendToRole = [ Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER ];

        $message->additional_expense_id = $ae->id;
        $message->created_by            = $user->id;
        $message->subject               = $inputs['subject'];
        $message->message               = $inputs['message'];

        if( $user->hasCompanyProjectRole($ae->project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER )) )
        {
            if( $inputs['decision'] == AdditionalExpenseFourthLevelMessage::GRANT_DIFF_AMOUNT )
            {
                $message->grant_different_amount = $inputs['grant_different_amount'];

                if( $user->hasCompanyProjectRole($ae->project, Role::INSTRUCTION_ISSUER) and $this->exceedClaimAmount($ae, $message) )
                {
                    $message->locked = true;
                }
            }
            elseif( $inputs['decision'] == AdditionalExpenseFourthLevelMessage::GRANT )
            {
                $message->grant_different_amount = $ae->additionalExpenseClaim->final_claim_amount;

                if( $user->hasCompanyProjectRole($ae->project, Role::INSTRUCTION_ISSUER) )
                {
                    $message->locked = true;
                }
            }

            $message->decision = $inputs['decision'];
        }

        $message->type = $user->getAssignedCompany($ae->project)->getContractGroup($ae->project)->group;

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

        $tabId = Helpers::generateTabLink($message->id, AEMessageFourthLevelArchitectQsForm::accordianId);

        $this->sendEmailNotification($ae->project, $ae, $sendToRole, 'additional_expense', 'ae.show', $tabId);
        $this->sendSystemNotification($ae->project, $ae, $sendToRole, 'additional_expense', 'ae.show', $tabId);

        return $message;
    }

    private function save(AdditionalExpenseFourthLevelMessage $instance)
    {
        $instance->save();

        return $instance;
    }

    private function exceedClaimAmount(AdditionalExpense $ae, AdditionalExpenseFourthLevelMessage $message)
    {
        return $message->grant_different_amount >= $ae->additionalExpenseClaim->final_claim_amount;
    }

}