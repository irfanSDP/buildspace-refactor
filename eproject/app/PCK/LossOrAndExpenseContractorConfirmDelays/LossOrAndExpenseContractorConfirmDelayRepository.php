<?php namespace PCK\LossOrAndExpenseContractorConfirmDelays;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\Calendars\CalendarRepository;
use PCK\ContractGroups\Types\Role;
use PCK\LossOrAndExpenses\LossOrAndExpense;
use PCK\Forms\LOEContractorConfirmDelayForm;

class LossOrAndExpenseContractorConfirmDelayRepository extends BaseModuleRepository {

	private $loeContractorConfirmDelay;

	private $calendarRepo;

	protected $events;

	public function __construct(
		LossOrAndExpenseContractorConfirmDelay $loeContractorConfirmDelay,
		CalendarRepository $calendarRepo,
		Dispatcher $events
	)
	{
		$this->loeContractorConfirmDelay = $loeContractorConfirmDelay;
		$this->calendarRepo              = $calendarRepo;
		$this->events                    = $events;
	}

	public function add(User $user, LossOrAndExpense $loe, array $inputs)
	{
		$model                                 = $this->loeContractorConfirmDelay;
		$model->loss_or_and_expense_id         = $loe->id;
		$model->created_by                     = $user->id;
		$model->subject                        = $inputs['subject'];
		$model->message                        = $inputs['message'];
		$model->date_on_which_delay_is_over    = $inputs['date_on_which_delay_is_over'];
		$model->deadline_to_submit_final_claim = $this->calendarRepo->calculateFinalDate($loe->project, $inputs['date_on_which_delay_is_over'], $loe->project->pam2006Detail->deadline_submitting_final_claim_l_and_e);

		$model = $this->save($model);

		$this->saveAttachments($model, $inputs);

		$tabId = Helpers::generateTabLink($model->id, LOEContractorConfirmDelayForm::accordianId);

		$this->sendEmailNotification($loe->project, $loe, [ Role::INSTRUCTION_ISSUER ], 'loss_and_or_expense', 'loe.show', $tabId);
		$this->sendSystemNotification($loe->project, $loe, [ Role::INSTRUCTION_ISSUER ], 'loss_and_or_expense', 'loe.show', $tabId);

		return $model;
	}

	public function save(LossOrAndExpenseContractorConfirmDelay $instance)
	{
		$instance->save();

		return $instance;
	}

}