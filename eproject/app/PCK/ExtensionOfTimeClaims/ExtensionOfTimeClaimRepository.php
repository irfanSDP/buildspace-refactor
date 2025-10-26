<?php namespace PCK\ExtensionOfTimeClaims;

use PCK\Users\User;
use PCK\Base\Helpers;
use PCK\Forms\EOTClaimForm;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\ExtensionOfTimes\ExtensionOfTime;

class ExtensionOfTimeClaimRepository extends BaseModuleRepository {

	private $eotClaim;

	protected $events;

	public function __construct(ExtensionOfTimeClaim $eotClaim, Dispatcher $events)
	{
		$this->eotClaim = $eotClaim;
		$this->events   = $events;
	}

	public function add(User $user, ExtensionOfTime $eot, array $inputs)
	{
		$eotClaim                       = $this->eotClaim;
		$eotClaim->extension_of_time_id = $eot->id;
		$eotClaim->created_by           = $user->id;
		$eotClaim->subject              = $inputs['subject'];
		$eotClaim->message              = $inputs['message'];
		$eotClaim->days_claimed         = $inputs['days_claimed'];

		$eotClaim = $this->save($eotClaim);

		$this->saveAttachments($eotClaim, $inputs);

		$tabId = Helpers::generateTabLink($eotClaim->id, EOTClaimForm::accordianId);

		$this->sendEmailNotification($eot->project, $eot, [ Role::INSTRUCTION_ISSUER ], 'extension_of_time', 'eot.show', $tabId);
		$this->sendSystemNotification($eot->project, $eot, [ Role::INSTRUCTION_ISSUER ], 'extension_of_time', 'eot.show', $tabId);

		return $eotClaim;
	}

	public function save(ExtensionOfTimeClaim $instance)
	{
		$instance->save();

		return $instance;
	}

}