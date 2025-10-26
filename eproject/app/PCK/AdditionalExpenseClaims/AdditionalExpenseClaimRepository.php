<?php namespace PCK\AdditionalExpenseClaims;

use PCK\Users\User;
use PCK\Base\Helpers;
use PCK\Forms\AEClaimForm;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\AdditionalExpenses\AdditionalExpense;

class AdditionalExpenseClaimRepository extends BaseModuleRepository {

	private $aeClaim;

	protected $events;

	public function __construct(AdditionalExpenseClaim $aeClaim, Dispatcher $events)
	{
		$this->aeClaim = $aeClaim;
		$this->events  = $events;
	}

	public function add(User $user, AdditionalExpense $ae, array $inputs)
	{
		$loeClaim                        = $this->aeClaim;
		$loeClaim->additional_expense_id = $ae->id;
		$loeClaim->created_by            = $user->id;
		$loeClaim->subject               = $inputs['subject'];
		$loeClaim->message               = $inputs['message'];
		$loeClaim->final_claim_amount    = $inputs['final_claim_amount'];

		$loeClaim = $this->save($loeClaim);

		$this->saveAttachments($loeClaim, $inputs);

		$tabId = Helpers::generateTabLink($loeClaim->id, AEClaimForm::accordianId);

		$this->sendEmailNotification($ae->project, $ae, [ Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER ], 'additional_expense', 'ae.show', $tabId);
		$this->sendSystemNotification($ae->project, $ae, [ Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER ], 'additional_expense', 'ae.show', $tabId);

		return $loeClaim;
	}

	private function save(AdditionalExpenseClaim $instance)
	{
		$instance->save();

		return $instance;
	}

}