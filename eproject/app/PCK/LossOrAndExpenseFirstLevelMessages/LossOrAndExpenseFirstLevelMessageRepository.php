<?php namespace PCK\LossOrAndExpenseFirstLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\LossOrAndExpenses\LossOrAndExpense;
use PCK\Forms\LOEMessageFirstLevelArchitectForm;

class LossOrAndExpenseFirstLevelMessageRepository extends BaseModuleRepository {

    private $loeFirstMessage;

    protected $events;

    public function __construct(LossOrAndExpenseFirstLevelMessage $loeFirstMessage, Dispatcher $events)
    {
        $this->loeFirstMessage = $loeFirstMessage;
        $this->events          = $events;
    }

    public function checkLatestMessagePosterRole($loeId)
    {
        return $this->loeFirstMessage->where('loss_or_and_expense_id', '=', $loeId)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function add(User $user, LossOrAndExpense $loe, array $inputs)
    {
        $loeMessage = $this->loeFirstMessage;
        $sendToRole = Role::INSTRUCTION_ISSUER;

        $loeMessage->loss_or_and_expense_id = $loe->id;
        $loeMessage->created_by             = $user->id;
        $loeMessage->subject                = $inputs['subject'];
        $loeMessage->details                = $inputs['details'];

        if( $user->hasCompanyProjectRole($loe->project, Role::INSTRUCTION_ISSUER) )
        {
            $loeMessage->decision = $inputs['decision'];
            $sendToRole           = Role::CONTRACTOR;
        }

        $loeMessage->type = $user->getAssignedCompany($loe->project)->getContractGroup($loe->project)->group;

        $loeMessage = $this->save($loeMessage);

        $this->saveAttachments($loeMessage, $inputs);

        $tabId = Helpers::generateTabLink($loeMessage->id, LOEMessageFirstLevelArchitectForm::accordianId);

        $this->sendEmailNotification($loe->project, $loe, [ $sendToRole ], 'loss_and_or_expense', 'loe.show', $tabId);
        $this->sendSystemNotification($loe->project, $loe, [ $sendToRole ], 'loss_and_or_expense', 'loe.show', $tabId);

        return $loeMessage;
    }

    public function save(LossOrAndExpenseFirstLevelMessage $instance)
    {
        $instance->save();

        return $instance;
    }

}