<?php namespace PCK\AdditionalExpenseContractorConfirmDelays;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\Calendars\CalendarRepository;
use PCK\ContractGroups\Types\Role;
use PCK\AdditionalExpenses\AdditionalExpense;
use PCK\Forms\AEContractorConfirmDelayForm;

class AdditionalExpenseContractorConfirmDelayRepository extends BaseModuleRepository {

	private $aeContractorConfirmDelay;

	private $calendarRepo;

	protected $events;

	public function __construct(
		AdditionalExpenseContractorConfirmDelay $aeContractorConfirmDelay,
		CalendarRepository $calendarRepo,
		Dispatcher $events
	)
	{
		$this->aeContractorConfirmDelay = $aeContractorConfirmDelay;
		$this->calendarRepo             = $calendarRepo;
		$this->events                   = $events;
	}

	public function add(User $user, AdditionalExpense $ae, array $inputs)
	{
		$model                                 = $this->aeContractorConfirmDelay;
		$model->additional_expense_id          = $ae->id;
		$model->created_by                     = $user->id;
		$model->subject                        = $inputs['subject'];
		$model->message                        = $inputs['message'];
		$model->date_on_which_delay_is_over    = $inputs['date_on_which_delay_is_over'];
		$model->deadline_to_submit_final_claim = $this->calendarRepo->calculateFinalDate($ae->project, $inputs['date_on_which_delay_is_over'], $ae->project->pam2006Detail->deadline_submitting_final_claim_ae);

		$model = $this->save($model);

		$this->saveAttachments($model, $inputs);

		$tabId = Helpers::generateTabLink($model->id, AEContractorConfirmDelayForm::accordianId);

		$this->sendEmailNotification($ae->project, $ae, [ Role::INSTRUCTION_ISSUER ], 'additional_expense', 'ae.show', $tabId);
		$this->sendSystemNotification($ae->project, $ae, [ Role::INSTRUCTION_ISSUER ], 'additional_expense', 'ae.show', $tabId);

		return $model;
	}

	public function save(AdditionalExpenseContractorConfirmDelay $instance)
	{
		$instance->save();

		return $instance;
	}

}