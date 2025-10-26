<?php namespace PCK\LossOrAndExpenseInterimClaims;

use PCK\Users\User;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\LossOrAndExpenses\LossOrAndExpense;

class LossOrAndExpenseInterimClaimRepository extends BaseModuleRepository {

	private $lossOrAndExpenseInterimClaim;

	protected $events;

	public function __construct(LossOrAndExpenseInterimClaim $lossOrAndExpenseInterimClaim, Dispatcher $events)
	{
		$this->lossOrAndExpenseInterimClaim = $lossOrAndExpenseInterimClaim;
		$this->events                       = $events;
	}

	public function add(LossOrAndExpense $loe, User $user, array $inputs)
	{
		$ic = $this->lossOrAndExpenseInterimClaim;

		$ic->loss_or_and_expense_id = $loe->id;
		$ic->interim_claim_id       = $inputs['interim_claim_id'];
		$ic->created_by             = $user->id;

		$ic = $this->save($ic);

		$this->saveAttachments($ic, $inputs);

		$this->sendEmailNotification($loe->project, $loe, [ Role::CONTRACTOR, Role::CLAIM_VERIFIER ], 'loss_and_or_expense', 'loe.show');
		$this->sendSystemNotification($loe->project, $loe, [ Role::CONTRACTOR, Role::CLAIM_VERIFIER ], 'loss_and_or_expense', 'loe.show');

		return $ic;
	}

	private function save(LossOrAndExpenseInterimClaim $ic)
	{
		$ic->save();

		return $ic;
	}

}