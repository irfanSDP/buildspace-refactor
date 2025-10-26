<?php namespace PCK\ExtensionOfTimeSecondLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\ExtensionOfTimes\ExtensionOfTime;
use PCK\Forms\EOTMessageSecondLevelArchitectForm;

class ExtensionOfTimeSecondLevelMessageRepository extends BaseModuleRepository {

	private $eotSecondLevelMessage;

	protected $events;

	public function __construct(ExtensionOfTimeSecondLevelMessage $eotSecondLevelMessage, Dispatcher $events)
	{
		$this->eotSecondLevelMessage = $eotSecondLevelMessage;
		$this->events                = $events;
	}

	public function checkLatestMessagePosterRole($eotId)
	{
		return $this->eotSecondLevelMessage->where('extension_of_time_id', '=', $eotId)
			->orderBy('id', 'desc')
			->first();
	}

	public function add(User $user, ExtensionOfTime $eot, array $inputs)
	{
		$lastMessage = $this->checkLatestMessagePosterRole($eot->id);

		$message = $this->eotSecondLevelMessage;

		$message->extension_of_time_id = $eot->id;
		$message->created_by           = $user->id;
		$message->subject              = $inputs['subject'];
		$message->message              = $inputs['message'];

		if ( $user->hasCompanyProjectRole($eot->project, Role::INSTRUCTION_ISSUER) )
		{
			if ( $inputs['decision'] == ExtensionOfTimeSecondLevelMessage::GRANT_DIFF_DEADLINE )
			{
				$message->grant_different_deadline = date('Y-m-d', strtotime($inputs['grant_different_deadline']));
			}
			elseif ( $inputs['decision'] == ExtensionOfTimeSecondLevelMessage::EXTEND_DEADLINE )
			{
				$message->grant_different_deadline = date('Y-m-d', strtotime($lastMessage->requested_new_deadline));
			}

			$message->decision = $inputs['decision'];
			$message->type     = Role::INSTRUCTION_ISSUER;
			$sendToRole        = Role::CONTRACTOR;
		}

		if ( $user->hasCompanyProjectRole($eot->project, Role::CONTRACTOR) )
		{
			$message->requested_new_deadline = date('Y-m-d', strtotime($inputs['requested_new_deadline']));
			$message->type                   = Role::CONTRACTOR;
			$sendToRole                      = Role::INSTRUCTION_ISSUER;
		}
		else
		{
			if ( $lastMessage )
			{
				$message->requested_new_deadline = date('Y-m-d', strtotime($lastMessage->requested_new_deadline));
			}
		}

		$message = $this->save($message);

		$this->saveAttachments($message, $inputs);

		$tabId = Helpers::generateTabLink($message->id, EOTMessageSecondLevelArchitectForm::accordianId);

		$this->sendEmailNotification($eot->project, $eot, [ $sendToRole ], 'extension_of_time', 'eot.show', $tabId);
		$this->sendSystemNotification($eot->project, $eot, [ $sendToRole ], 'extension_of_time', 'eot.show', $tabId);

		return $message;
	}

	public function save(ExtensionOfTimeSecondLevelMessage $message)
	{
		$message->save();

		return $message;
	}

}