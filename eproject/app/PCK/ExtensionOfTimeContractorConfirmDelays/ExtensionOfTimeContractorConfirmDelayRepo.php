<?php namespace PCK\ExtensionOfTimeContractorConfirmDelays;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\Calendars\CalendarRepository;
use PCK\ContractGroups\Types\Role;
use PCK\ExtensionOfTimes\ExtensionOfTime;
use PCK\Forms\EOTContractorConfirmDelayForm;

class ExtensionOfTimeContractorConfirmDelayRepo extends BaseModuleRepository {

	private $eotContractorConfirmDelay;

	private $calendarRepo;

	protected $events;

	public function __construct(
		ExtensionOfTimeContractorConfirmDelay $eotContractorConfirmDelay,
		CalendarRepository $calendarRepo,
		Dispatcher $events
	)
	{
		$this->eotContractorConfirmDelay = $eotContractorConfirmDelay;
		$this->calendarRepo              = $calendarRepo;
		$this->events                    = $events;
	}

	public function add(User $user, ExtensionOfTime $eot, array $inputs)
	{
		$model                                     = $this->eotContractorConfirmDelay;
		$model->extension_of_time_id               = $eot->id;
		$model->created_by                         = $user->id;
		$model->subject                            = $inputs['subject'];
		$model->message                            = $inputs['message'];
		$model->date_on_which_delay_is_over        = $inputs['date_on_which_delay_is_over'];
		$model->deadline_to_submit_final_eot_claim = $this->calendarRepo->calculateFinalDate($eot->project, $inputs['date_on_which_delay_is_over'], $eot->project->pam2006Detail->deadline_submitting_final_claim_eot);

		$model = $this->save($model);

		$this->saveAttachments($model, $inputs);

		$tabId = Helpers::generateTabLink($model->id, EOTContractorConfirmDelayForm::accordianId);

		$this->sendEmailNotification($eot->project, $eot, [ Role::INSTRUCTION_ISSUER ], 'extension_of_time', 'eot.show', $tabId);
		$this->sendSystemNotification($eot->project, $eot, [ Role::INSTRUCTION_ISSUER ], 'extension_of_time', 'eot.show', $tabId);

		return $model;
	}

	public function save(ExtensionOfTimeContractorConfirmDelay $instance)
	{
		$instance->save();

		return $instance;
	}

}