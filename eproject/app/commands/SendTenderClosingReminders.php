<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\Base\Helpers;
use Carbon\Carbon;
use PCK\Users\User;
use PCK\Notifications\EmailNotifier;
use PCK\Projects\Project;
use PCK\EmailSettings\EmailReminderSetting;

class SendTenderClosingReminders extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'system:send-tender-closing-reminders';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sends email reminders to users before tender closing date.';

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
		return $scheduler->daily()->hours(10);
    }

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		\Log::info("Firing scheduled command", [
			'class'   => get_class($this),
			'command' => $this->name,
		]);

		$projects = Project::where('status_id', Project::STATUS_TYPE_CALLING_TENDER)->orderBy('id', 'ASC')->get();

		foreach($projects as $project)
		{
			$tenderReminderSendingDate = $this->getTenderReminderSendingDate(Carbon::parse($project->latestTender->callingTenderInformation->date_of_closing_tender));

			if($tenderReminderSendingDate->isFuture()) continue;

			$this->emailNotifier->sendReminderEmailsBeforeCallingTenderClosingDate($project);
		}
	}

	private function getTenderReminderSendingDate($tenderClosingDate)
    {
        switch(EmailReminderSetting::getValue('tender_reminder_before_closing_date_unit'))
        {
            case EmailReminderSetting::DAY:
                $validityPeriodUnit = 'days';
                break;
            case EmailReminderSetting::WEEK:
                $validityPeriodUnit = 'weeks';
                break;
            case EmailReminderSetting::MONTH:
                $validityPeriodUnit = 'months';
                break;
            default:
                throw new \Exception("Invalid time unit");
        }

        return Helpers::getTimeBefore($tenderClosingDate, EmailReminderSetting::getValue('tender_reminder_before_closing_date_value'), $validityPeriodUnit);
    }
}
