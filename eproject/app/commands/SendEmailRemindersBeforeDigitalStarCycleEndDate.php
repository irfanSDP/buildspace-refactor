<?php

use Carbon\Carbon;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use PCK\Base\Helpers;
use PCK\DigitalStar\Evaluation\DsCycle;
use PCK\DigitalStar\ModuleParameters\DsModuleParameter;
use PCK\Notifications\EmailNotifier;
use PCK\SystemModules\SystemModuleConfiguration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SendEmailRemindersBeforeDigitalStarCycleEndDate extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'digital-star:send-email-reminders-before-cycle-end-date';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sends email reminders to evaluators before cycle end date';

	protected $emailNotifier;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(EmailNotifier $emailNotifier)
	{
		parent::__construct();
		$this->emailNotifier = $emailNotifier;
	}

	/**
     * When a command should run
     *
     * @param Scheduler $scheduler
     * @return \Indatus\Dispatcher\Scheduling\Schedulable
     */
    public function schedule(Schedulable $scheduler)
    {
        return $scheduler->daily();
    }

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		if (SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR) && DsModuleParameter::first()->email_reminder_before_cycle_end_date)
        {
            \Log::info("Firing scheduled command", [
                'class'   => get_class($this),
                'command' => $this->name,
            ]);

			$this->sendEmailReminders();
        }
	}

	protected function sendEmailReminders()
	{
		if (! DsCycle::hasOngoingCycle())
		{
			\Log::info('No on-going Digital Star cycle at the moment.');

			return false;
		}

		$moduleParameter = DsModuleParameter::first();

		$cycle = DsCycle::where('is_completed', false)->first();
        $evaluations = $cycle->evaluations;

        foreach($evaluations as $evaluation)
        {
            $endDate = Helpers::getTimeBefore(Carbon::parse($evaluation->end_date), $moduleParameter->email_reminder_before_cycle_end_date_value, DsModuleParameter::getHelperClassUnit($moduleParameter->email_reminder_before_cycle_end_date_unit));

            $pendingForms = $evaluation->forms->reject(function($form) {
                return $form->isCompleted();
            });

            if($endDate->isPast() && ($pendingForms->count() > 0))
            {
                foreach ($pendingForms as $evaluationForm) {
                    \Log::info("Sending email reminders for Digital Star : [{$evaluationForm->id}]");
                    $this->emailNotifier->sendDsCycleReminderEmail($evaluationForm, 'end');
                }
            }
        }
	}
}
