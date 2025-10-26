<?php namespace PCK\AdditionalExpenseFirstLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\AdditionalExpenses\AdditionalExpense;
use PCK\Forms\AEMessageFirstLevelArchitectForm;

class AdditionalExpenseFirstLevelMessageRepository extends BaseModuleRepository {

    private   $aeFirstMessage;
    protected $events;

    public function __construct(AdditionalExpenseFirstLevelMessage $aeFirstMessage, Dispatcher $events)
    {
        $this->aeFirstMessage = $aeFirstMessage;
        $this->events         = $events;
    }

    public function checkLatestMessagePosterRole($aeId)
    {
        return $this->aeFirstMessage->where('additional_expense_id', '=', $aeId)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function add(User $user, AdditionalExpense $ae, array $inputs)
    {
        $aeMessage  = $this->aeFirstMessage;
        $sendToRole = Role::INSTRUCTION_ISSUER;

        $aeMessage->additional_expense_id = $ae->id;
        $aeMessage->created_by            = $user->id;
        $aeMessage->subject               = $inputs['subject'];
        $aeMessage->details               = $inputs['details'];

        if( $user->hasCompanyProjectRole($ae->project, Role::INSTRUCTION_ISSUER) )
        {
            $aeMessage->decision = $inputs['decision'];
            $sendToRole          = Role::CONTRACTOR;
        }

        $aeMessage->type = $user->getAssignedCompany($ae->project)->getContractGroup($ae->project)->group;

        $aeMessage = $this->save($aeMessage);

        $this->saveAttachments($aeMessage, $inputs);

        $tabId = Helpers::generateTabLink($aeMessage->id, AEMessageFirstLevelArchitectForm::accordianId);

        $this->sendEmailNotification($ae->project, $ae, [ $sendToRole ], 'additional_expense', 'ae.show', $tabId);
        $this->sendSystemNotification($ae->project, $ae, [ $sendToRole ], 'additional_expense', 'ae.show', $tabId);

        return $aeMessage;
    }

    public function save(AdditionalExpenseFirstLevelMessage $instance)
    {
        $instance->save();

        return $instance;
    }

}