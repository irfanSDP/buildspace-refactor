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

class SendEmailRemindersForPendingApprovalTasks extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'system:send-email-reminders-for-pending-approval-tasks';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sends email reminders to users with pending approval tasks.';

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

		$users = User::where('is_super_admin', false)->where('confirmed', true)->where('account_blocked_status', false)->orderBy('id', 'ASC')->get();
		
		foreach($users as $user)
		{
			$hasConsultantManagementPendingReviews = false;

			foreach($user->getConsultantManagementPendingReviews() as $categoryReviews)
            {
                if( ! empty($categoryReviews) )
				{
					$hasConsultantManagementPendingReviews = true;

					break;
				}
            }

			if($user->hasPendingReviews(false) || $hasConsultantManagementPendingReviews)
			{
				\Log::info("Sending email reminders to user : [{$user->id}] $user->name ({$user->email})");
				$this->info("Sending email reminders to user : [{$user->id}] $user->name ({$user->email})");

				$content = [
					'subject' => trans('general.pendingTasksReminder'),
					'view'    => 'notifications.email.user_pending_tasks',
					'data'    => [],
				];
				
				$this->emailNotifier->sendGeneralEmail($content, [$user]);
			}
		}

		\Log::info(get_class($this) . ' completed.');
		$this->info(get_class($this) . ' completed.');
	}
}
