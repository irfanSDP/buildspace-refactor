<?php namespace PCK\ExtensionOfTimeFourthLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\ExtensionOfTimes\ExtensionOfTime;
use PCK\Forms\EOTMessageFourthLevelArchitectForm;

class ExtensionOfTimeFourthLevelMessageRepository extends BaseModuleRepository {

	private $eotFourthLevelMessage;

	protected $events;

	public function __construct(ExtensionOfTimeFourthLevelMessage $eotSecondLevelMessage, Dispatcher $events)
	{
		$this->eotFourthLevelMessage = $eotSecondLevelMessage;
		$this->events                = $events;
	}

	public function checkLatestMessagePosterRole($eotId)
	{
		return $this->eotFourthLevelMessage->where('extension_of_time_id', '=', $eotId)
			->orderBy('id', 'desc')
			->first();
	}

	public function add(User $user, ExtensionOfTime $eot, array $inputs)
	{
		$message = $this->eotFourthLevelMessage;

		$message->extension_of_time_id = $eot->id;
		$message->created_by           = $user->id;
		$message->subject              = $inputs['subject'];
		$message->message              = $inputs['message'];

		if ( $user->hasCompanyProjectRole($eot->project, Role::INSTRUCTION_ISSUER) )
		{
			if ( $inputs['decision'] == ExtensionOfTimeFourthLevelMessage::GRANT_DIFF_DEADLINE )
			{
				$message->grant_different_days = $inputs['grant_different_days'];

				if ( $message->grant_different_days >= $eot->extensionOfTimeClaim->days_claimed )
				{
					$message->locked = true;
				}
			}
			elseif ( $inputs['decision'] == ExtensionOfTimeFourthLevelMessage::EXTEND_DEADLINE )
			{
				$message->grant_different_days = $eot->extensionOfTimeClaim->days_claimed;
				$message->locked               = true;
			}

			$message->decision = $inputs['decision'];
			$message->type     = Role::INSTRUCTION_ISSUER;
			$sendToRole        = Role::CONTRACTOR;
		}

		if ( $user->hasCompanyProjectRole($eot->project, Role::CONTRACTOR) )
		{
			$message->type = Role::CONTRACTOR;
			$sendToRole    = Role::INSTRUCTION_ISSUER;
		}

		$message = $this->save($message);

		$this->saveAttachments($message, $inputs);

		$tabId = Helpers::generateTabLink($message->id, EOTMessageFourthLevelArchitectForm::accordianId);

		$this->sendEmailNotification($eot->project, $eot, [ $sendToRole ], 'extension_of_time', 'eot.show', $tabId);
		$this->sendSystemNotification($eot->project, $eot, [ $sendToRole ], 'extension_of_time', 'eot.show', $tabId);

		return $message;
	}

	public function save(ExtensionOfTimeFourthLevelMessage $message)
	{
		$message->save();

		return $message;
	}

}