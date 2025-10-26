<?php namespace PCK\AdditionalExpenseThirdLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\AdditionalExpenses\AdditionalExpense;
use PCK\Forms\AEMessageThirdLevelArchitectQsForm;

class AdditionalExpenseThirdLevelMessageRepository extends BaseModuleRepository {

    private $aeThirdLevelMessage;

    protected $events;

    public function __construct(AdditionalExpenseThirdLevelMessage $aeThirdLevelMessage, Dispatcher $events)
    {
        $this->aeThirdLevelMessage = $aeThirdLevelMessage;
        $this->events              = $events;
    }

    public function checkLatestMessagePosterRole($aeId)
    {
        return $this->aeThirdLevelMessage->where('additional_expense_id', '=', $aeId)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function add(User $user, AdditionalExpense $ae, array $inputs)
    {
        $message    = $this->aeThirdLevelMessage;
        $sendToRole = [ Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER, Role::CONTRACTOR ];

        $message->additional_expense_id = $ae->id;
        $message->created_by            = $user->id;
        $message->subject               = $inputs['subject'];
        $message->message               = $inputs['message'];

        if( $user->hasCompanyProjectRole($ae->project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER )) )
        {
            $message->deadline_to_comply_with = $inputs['deadline_to_comply_with'];
        }

        $message->type = $user->getAssignedCompany($ae->project)->getContractGroup($ae->project)->group;

        $message = $this->save($message);

        $this->saveAttachments($message, $inputs);

        if( ( $key = array_search($message->type, $sendToRole) ) !== false )
        {
            unset( $sendToRole[ $key ] );
        }

        $tabId = Helpers::generateTabLink($message->id, AEMessageThirdLevelArchitectQsForm::accordianId);

        $this->sendEmailNotification($ae->project, $ae, $sendToRole, 'additional_expense', 'ae.show', $tabId);
        $this->sendSystemNotification($ae->project, $ae, $sendToRole, 'additional_expense', 'ae.show', $tabId);

        return $message;
    }

    public function save(AdditionalExpenseThirdLevelMessage $instance)
    {
        $instance->save();

        return $instance;
    }

}