<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use PCK\Notifications\EmailNotifier;

use PCK\ProjectReport\ProjectReportNotificationRepository;

class SendProjectReportReminders extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'project-report:send-reminders';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sends reminders for project reports';

	protected $emailNotifier;
    protected $projectReportNotificationRepository;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(EmailNotifier $emailNotifier, ProjectReportNotificationRepository $projectReportNotificationRepository)
    {
        parent::__construct();
        $this->emailNotifier = $emailNotifier;
        $this->projectReportNotificationRepository = $projectReportNotificationRepository;
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
        \Log::info('Firing scheduled command', [
            'class'   => get_class($this),
            'command' => $this->name,
        ]);

        $this->processReminders();

        return 0;
	}

    protected function processReminders()
    {
        $records = $this->projectReportNotificationRepository->getAllRecords(['isPublished' => true]);

        foreach ($records as $record) {
            $sendNotification = $this->projectReportNotificationRepository->checkNotifyDates($record);
            if ($sendNotification) {
                $this->sendEmailReminders($record);
            }
        }
    }

	protected function sendEmailReminders($record)
	{
        $this->emailNotifier->sendProjectReportReminder($record);
	}
}
