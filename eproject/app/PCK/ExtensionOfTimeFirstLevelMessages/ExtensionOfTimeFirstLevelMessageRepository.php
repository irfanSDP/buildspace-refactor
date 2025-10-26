<?php namespace PCK\ExtensionOfTimeFirstLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\ExtensionOfTimes\ExtensionOfTime;
use PCK\Forms\EOTMessageFirstLevelArchitectForm;

class ExtensionOfTimeFirstLevelMessageRepository extends BaseModuleRepository {

	private $eotFirstLevelMessage;

	protected $events;

	public function __construct(ExtensionOfTimeFirstLevelMessage $eotFirstLevelMessage, Dispatcher $events)
	{
		$this->eotFirstLevelMessage = $eotFirstLevelMessage;
		$this->events               = $events;
	}

	public function checkLatestMessagePosterRole($eotId)
	{
		return $this->eotFirstLevelMessage->where('extension_of_time_id', '=', $eotId)
			->orderBy('id', 'desc')
			->first();
	}

	public function add(User $user, ExtensionOfTime $eot, array $inputs)
	{
		$eotMessage = $this->eotFirstLevelMessage;

		$eotMessage->extension_of_time_id = $eot->id;
		$eotMessage->created_by           = $user->id;
		$eotMessage->subject              = $inputs['subject'];
		$eotMessage->details              = $inputs['details'];

		if ( $user->hasCompanyProjectRole($eot->project, Role::INSTRUCTION_ISSUER) )
		{
			$eotMessage->decision = $inputs['decision'];
			$eotMessage->type     = Role::INSTRUCTION_ISSUER;
			$sendToRole           = Role::CONTRACTOR;
		}

		if ( $user->hasCompanyProjectRole($eot->project, Role::CONTRACTOR) )
		{
			$eotMessage->type = Role::CONTRACTOR;
			$sendToRole       = Role::INSTRUCTION_ISSUER;
		}

		$eotMessage = $this->save($eotMessage);

		$this->saveAttachments($eotMessage, $inputs);

		$tabId = Helpers::generateTabLink($eotMessage->id, EOTMessageFirstLevelArchitectForm::accordianId);

		$this->sendEmailNotification($eot->project, $eot, [ $sendToRole ], 'extension_of_time', 'eot.show', $tabId);
		$this->sendSystemNotification($eot->project, $eot, [ $sendToRole ], 'extension_of_time', 'eot.show', $tabId);

		return $eotMessage;
	}

	public function save(ExtensionOfTimeFirstLevelMessage $eotMessage)
	{
		$eotMessage->save();

		return $eotMessage;
	}

}