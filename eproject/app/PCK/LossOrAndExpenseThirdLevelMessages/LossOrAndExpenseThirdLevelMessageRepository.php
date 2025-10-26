<?php namespace PCK\LossOrAndExpenseThirdLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\LossOrAndExpenses\LossOrAndExpense;
use PCK\Forms\LOEMessageThirdLevelArchitectQsForm;

class LossOrAndExpenseThirdLevelMessageRepository extends BaseModuleRepository {

    private $loeThirdLevelMessage;

    protected $events;

    public function __construct(LossOrAndExpenseThirdLevelMessage $loeThirdLevelMessage, Dispatcher $events)
    {
        $this->loeThirdLevelMessage = $loeThirdLevelMessage;
        $this->events               = $events;
    }

    public function checkLatestMessagePosterRole($loeId)
    {
        return $this->loeThirdLevelMessage->where('loss_or_and_expense_id', '=', $loeId)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function add(User $user, LossOrAndExpense $loe, array $inputs)
    {
        $message    = $this->loeThirdLevelMessage;
        $sendToRole = [ Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER ];

        $message->loss_or_and_expense_id = $loe->id;
        $message->created_by             = $user->id;
        $message->subject                = $inputs['subject'];
        $message->message                = $inputs['message'];

        if( $user->hasCompanyProjectRole($loe->project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER )) )
        {
            $message->deadline_to_comply_with = $inputs['deadline_to_comply_with'];
            $sendToRole                       = [ Role::CONTRACTOR ];
        }

        $message->type = $user->getAssignedCompany($loe->project)->getContractGroup($loe->project)->group;

        $message = $this->save($message);

        $this->saveAttachments($message, $inputs);

        $tabId = Helpers::generateTabLink($message->id, LOEMessageThirdLevelArchitectQsForm::accordianId);

        $this->sendEmailNotification($loe->project, $loe, $sendToRole, 'loss_and_or_expense', 'loe.show', $tabId);
        $this->sendSystemNotification($loe->project, $loe, $sendToRole, 'loss_and_or_expense', 'loe.show', $tabId);

        return $message;
    }

    public function save(LossOrAndExpenseThirdLevelMessage $instance)
    {
        $instance->save();

        return $instance;
    }

}