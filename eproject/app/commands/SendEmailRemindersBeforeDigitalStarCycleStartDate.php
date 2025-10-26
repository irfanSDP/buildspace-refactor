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

class SendEmailRemindersBeforeDigitalStarCycleStartDate extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'digital-star:send-email-reminders-before-cycle-start-date';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sends email reminders to evaluators before cycle start date';

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
        $isModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR);

        $moduleParameter = DsModuleParameter::first();
        $emailReminderEnabled = $moduleParameter && $moduleParameter->submissionReminders()->exists();

        if ($isModuleEnabled && $emailReminderEnabled)
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
        $reminderSettings = $moduleParameter->submissionReminders; // Get related reminder days

        if ($reminderSettings->isEmpty())
        {
            \Log::info('No submission reminder settings configured.');
            return false;
        }

		$cycle = DsCycle::where('is_completed', false)->first();
        $evaluations = $cycle->evaluations;

        foreach ($evaluations as $evaluation)
        {
            $startDate = Carbon::parse($evaluation->start_date);

            foreach ($reminderSettings as $setting)
            {
                $reminderDate = Helpers::getTimeBefore(
                    $startDate,
                    $setting->number_of_days_before,
                    DsModuleParameter::getHelperClassUnit(DsModuleParameter::DAY)
                );

                if (Carbon::now()->gte($reminderDate))
                {
                    $pendingForms = $evaluation->forms->reject(function($form) {
                        return $form->isCompleted();
                    });

                    if ($pendingForms->count() > 0)
                    {
                        foreach ($pendingForms as $evaluationForm)
                        {
                            \Log::info("Sending start-cycle reminder for Digital Star: [{$evaluationForm->id}]");
                            $this->emailNotifier->sendDsCycleReminderEmail($evaluationForm);
                        }
                    }
                }
            }
        }
	}
}
