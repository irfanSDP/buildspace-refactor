<?php namespace PCK\LossOrAndExpenseClaims;

use PCK\Users\User;
use PCK\Base\Helpers;
use PCK\Forms\LOEClaimForm;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\LossOrAndExpenses\LossOrAndExpense;

class LossOrAndExpenseClaimRepository extends BaseModuleRepository {

	private $loeClaim;

	protected $events;

	public function __construct(LossOrAndExpenseClaim $loeClaim, Dispatcher $events)
	{
		$this->loeClaim = $loeClaim;
		$this->events   = $events;
	}

	public function add(User $user, LossOrAndExpense $loe, array $inputs)
	{
		$loeClaim                         = $this->loeClaim;
		$loeClaim->loss_or_and_expense_id = $loe->id;
		$loeClaim->created_by             = $user->id;
		$loeClaim->subject                = $inputs['subject'];
		$loeClaim->message                = $inputs['message'];
		$loeClaim->final_claim_amount     = $inputs['final_claim_amount'];

		$loeClaim = $this->save($loeClaim);

		$this->saveAttachments($loeClaim, $inputs);

		$tabId = Helpers::generateTabLink($loeClaim->id, LOEClaimForm::accordianId);

		$this->sendEmailNotification($loe->project, $loe, [ Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER ], 'loss_and_or_expense', 'loe.show', $tabId);
		$this->sendSystemNotification($loe->project, $loe, [ Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER ], 'loss_and_or_expense', 'loe.show', $tabId);

		return $loeClaim;
	}

	private function save(LossOrAndExpenseClaim $instance)
	{
		$instance->save();

		return $instance;
	}

} 