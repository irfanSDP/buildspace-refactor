<?php namespace PCK\ExtensionOfTimeThirdLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\ExtensionOfTimes\ExtensionOfTime;
use PCK\Forms\EOTMessageThirdLevelArchitectForm;

class ExtensionOfTimeThirdLevelMessageRepository extends BaseModuleRepository {

	private $eotThirdLevelMessage;

	protected $events;

	public function __construct(ExtensionOfTimeThirdLevelMessage $eotThirdLevelMessage, Dispatcher $events)
	{
		$this->eotThirdLevelMessage = $eotThirdLevelMessage;
		$this->events               = $events;
	}

	public function checkLatestMessagePosterRole($eotId)
	{
		return $this->eotThirdLevelMessage->where('extension_of_time_id', '=', $eotId)
			->orderBy('id', 'desc')
			->first();
	}

	public function add(User $user, ExtensionOfTime $eot, array $inputs)
	{
		$message = $this->eotThirdLevelMessage;

		$message->extension_of_time_id = $eot->id;
		$message->created_by           = $user->id;
		$message->subject              = $inputs['subject'];
		$message->message              = $inputs['message'];

		if ( $user->hasCompanyProjectRole($eot->project, Role::INSTRUCTION_ISSUER) )
		{
			$message->deadline_to_comply_with = $inputs['deadline_to_comply_with'];
			$message->type                    = Role::INSTRUCTION_ISSUER;
			$sendToRole                       = Role::CONTRACTOR;
		}

		if ( $user->hasCompanyProjectRole($eot->project, Role::CONTRACTOR) )
		{
			$message->type = Role::CONTRACTOR;
			$sendToRole    = Role::INSTRUCTION_ISSUER;
		}

		$message = $this->save($message);

		$this->saveAttachments($message, $inputs);

		$tabId = Helpers::generateTabLink($message->id, EOTMessageThirdLevelArchitectForm::accordianId);

		$this->sendEmailNotification($eot->project, $eot, [ $sendToRole ], 'extension_of_time', 'eot.show', $tabId);
		$this->sendSystemNotification($eot->project, $eot, [ $sendToRole ], 'extension_of_time', 'eot.show', $tabId);

		return $message;
	}

	public function save(ExtensionOfTimeThirdLevelMessage $instance)
	{
		$instance->save();

		return $instance;
	}

}