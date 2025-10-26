<?php namespace PCK\AdditionalExpenseInterimClaims;

use PCK\Users\User;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\AdditionalExpenses\AdditionalExpense;

class AdditionalExpenseInterimClaimRepository extends BaseModuleRepository {

	private $additionalExpenseInterimClaim;

	protected $events;

	public function __construct(AdditionalExpenseInterimClaim $additionalExpenseInterimClaim, Dispatcher $events)
	{
		$this->additionalExpenseInterimClaim = $additionalExpenseInterimClaim;
		$this->events                        = $events;
	}

	public function add(AdditionalExpense $ae, User $user, array $inputs)
	{
		$ic = $this->additionalExpenseInterimClaim;

		$ic->additional_expense_id = $ae->id;
		$ic->interim_claim_id      = $inputs['interim_claim_id'];
		$ic->created_by            = $user->id;

		$ic = $this->save($ic);

		$this->saveAttachments($ic, $inputs);

		$this->sendEmailNotification($ae->project, $ae, [ Role::CONTRACTOR, Role::CLAIM_VERIFIER ], 'additional_expense', 'ae.show');
		$this->sendSystemNotification($ae->project, $ae, [ Role::CONTRACTOR, Role::CLAIM_VERIFIER ], 'additional_expense', 'ae.show');

		return $ic;
	}

	private function save(AdditionalExpenseInterimClaim $ic)
	{
		$ic->save();

		return $ic;
	}

}