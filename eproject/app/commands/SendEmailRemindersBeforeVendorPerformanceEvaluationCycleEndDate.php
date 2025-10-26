<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;
use PCK\Base\Helpers;
use Carbon\Carbon;
use PCK\Companies\Company;
use PCK\SystemModules\SystemModuleConfiguration;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\Notifications\EmailNotifier;

class SendEmailRemindersBeforeVendorPerformanceEvaluationCycleEndDate extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vendor-management:send-email-reminders-before-vpe-cycle-end-date';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sends Email Reminders to BU Editors Before VPE Cycle End Date';

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
		if(SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT) && VendorPerformanceEvaluationModuleParameter::first()->email_reminder_before_cycle_end_date)
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
		if( ! Cycle::hasOngoingCycle() )
		{
			\Log::info('No on-going VPE cycle at the moment.');

			return false;
		}

		$vpeModuleParameter = VendorPerformanceEvaluationModuleParameter::first();

		$cycle = Cycle::where('is_completed', false)->first();

		foreach($cycle->evaluations as $vpe)
		{
			$endDate = Helpers::getTimeBefore(Carbon::parse($vpe->end_date), $vpeModuleParameter->email_reminder_before_cycle_end_date_value, VendorPerformanceEvaluationModuleParameter::getHelperClassUnit($vpeModuleParameter->email_reminder_before_cycle_end_date_unit));

			$pendingVpes = $vpe->companyForms->reject(function($form) {
				return $form->isCompleted();
			});

			if($endDate->isPast() && ($pendingVpes->count() > 0))
			{
				\Log::info("Sending email reminders for VPE : [{$vpe->id}]");
				$this->emailNotifier->sendRemainderEmailsBeforeVpeCycleEndDate($vpe);
			}
		}
	}
}
