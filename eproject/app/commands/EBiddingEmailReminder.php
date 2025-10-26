<?php
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Mail\Mailer;

use Carbon\Carbon;
use PCK\Helpers\NumberHelper;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\EBiddings\EBidding;
use PCK\EBiddingCommittees\EBiddingCommittee; 
use PCK\EmailReminder\EmailReminder;
use PCK\EmailReminder\EmailReminderRecipient;
use PCK\EBiddings\EBiddingConsoleRepository;


class EBiddingEmailReminder extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'system:send-email-reminders-for-ebidding';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
    protected $description = 'Sends out email reminders to users about upcoming eBidding events.';

	protected $emailReminder;
    protected $ebiddingConsoleRepository;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(EmailReminder $emailReminder, EBiddingConsoleRepository $ebiddingConsoleRepository)
	{
		parent::__construct();
		$this->emailReminder = $emailReminder;
        $this->ebiddingConsoleRepository = $ebiddingConsoleRepository;
	}

	/**
     * When a command should run
     *
     * @param Scheduler $scheduler
     * @return \Indatus\Dispatcher\Scheduling\Schedulable
     */
	public function schedule(Schedulable $scheduler)
	{
		return $scheduler->everyMinutes(1);
	}

	public function fire()
	{        
		/*\Log::info("Firing scheduled command", [
            'class'   => get_class($this),
            'command' => $this->name,
        ]);*/

        $projects = Project::where('status_id', Project::STATUS_TYPE_E_BIDDING)->where('e_bidding',true)->get();

        foreach ($projects as $project)
        {
            // Preview reminder
            $eBiddingPreview = $this->getEBiddingReminder($project->id, 'reminder_preview_start_time', 'preview_start_time');

            if($eBiddingPreview)
            {
                // Get email reminder with draft status
                $previewReminder = EmailReminder::where('ebidding_id',$eBiddingPreview->id)->where('status_preview_start_time', EmailReminder::DRAFT)->first();

                if($previewReminder)
                {
                    \Log::info("Firing scheduled command", [
                        'class'   => get_class($this),
                        'command' => $this->name,
                        'subject' => 'Preview start time',
                    ]);

                    $this->sendEmail($project, $previewReminder->id, 1);    // Send email for preview reminder
                    $previewReminder->status_preview_start_time = EmailReminder::SENT;    // Update status to sent
                    $previewReminder->save();
                }
            }

            // Bidding reminder
            $eBiddingStart = $this->getEBiddingReminder($project->id, 'reminder_bidding_start_time', 'bidding_start_time');

            if($eBiddingStart)
            {
                // Get email reminder with draft status
                $biddingReminder = EmailReminder::where('ebidding_id',$eBiddingStart->id)->where('status_bidding_start_time', EmailReminder::DRAFT)->first();

                if($biddingReminder)
                {
                    \Log::info("Firing scheduled command", [
                        'class'   => get_class($this),
                        'command' => $this->name,
                        'subject' => 'Bidding start time',
                    ]);

                    $this->sendEmail($project, $biddingReminder->id, 2);    // Send email for bidding reminder
                    $biddingReminder->status_bidding_start_time = EmailReminder::SENT;    // Update status to sent
                    $biddingReminder->save();
                }
            }
       
        }
	}

    private function getEBiddingReminder($projectId, $reminderField, $timeField) {
        $currentTime = Carbon::now();
        $reminderMinutes = 10;
        $futureTime = $currentTime->copy()->addMinutes($reminderMinutes);

        return EBidding::where('project_id', $projectId)
            ->where($reminderField, true)
            ->where($timeField, '>', $currentTime)
            ->where($timeField, '<=', $futureTime)
            ->first();
    }
	
	private function sendEmail($project, $emailReminderId, $template=1)
	{
        $eBidding = EBidding::where('project_id', $project->id)->first();
		$emailReminder = EmailReminder::find($emailReminderId);

		// Fetch eBidding committees for the project
		$eBiddingCommittees = EBiddingCommittee::where('project_id', $project->id)->where('is_committee', true)->get();

        // Recipients: Committee members
		foreach ($eBiddingCommittees as $eBiddingCommittee) {
            if (! EmailReminderRecipient::where('email_reminder_id', $emailReminderId)->where('user_id', $eBiddingCommittee->user_id)->exists()) {
                // Add committee member as recipient if not already added
                $recipientCommittee = new EmailReminderRecipient();
                $recipientCommittee->email_reminder_id = $emailReminderId;
                $recipientCommittee->user_id = $eBiddingCommittee->user_id;
                $recipientCommittee->role = EmailReminderRecipient::ROLE_COMMITTEE;
                $recipientCommittee->save();
            }
		}

        // Recipients: Contractors (admins)
        $contractors = $this->ebiddingConsoleRepository->getRankings($eBidding->id);
        foreach ($contractors as $contractor) {
            // Get admins of the contractor's company
            $contractorAdmins = User::where('company_id', $contractor->company_id)->where('is_admin', true)->select('id')->get();

            foreach ($contractorAdmins as $contractorAdmin) {
                if (! EmailReminderRecipient::where('email_reminder_id', $emailReminderId)->where('user_id', $contractorAdmin->id)->exists()) {
                    // Add contractor admin as recipient if not already added
                    $recipientContractor = new EmailReminderRecipient();
                    $recipientContractor->email_reminder_id = $emailReminderId;
                    $recipientContractor->user_id = $contractorAdmin->id;
                    $recipientContractor->role = EmailReminderRecipient::ROLE_BIDDER;
                    $recipientContractor->save();
                }
            }
        }
	
		// Now send the email
        switch ($template) {
            case 2: // Bidding reminder
                $subject = $emailReminder->subject2;
                $message = $emailReminder->message2;
                break;

            default:    // Preview reminder
                $subject = $emailReminder->subject;
                $message = $emailReminder->message;
        }

		$queue = 'default';
		$view = 'open_tenders.e_biddings.email_reminders.partials.email_reminder_message';

        $bidderLink = "\n\n" . link_to_route('e-bidding.console.show', trans('eBiddingReminder.linkDescriptionBidder'), ['eBiddingId' => $eBidding->id]);
        $committeeLink = "\n\n" . link_to_route('projects.e_bidding.index', trans('eBiddingReminder.linkDescriptionCommittee'), ['project_id' => $project->id]);

		// Loop through each recipient and queue an email for them
        $recipients = $emailReminder->recipients;
        if ($recipients->isEmpty()) {
            return;
        }

		foreach ($recipients as $recipient) {
            $user = $recipient->user;
            if (! $user) {  // User not found -> Skip this recipient
                continue;
            }
            $email = $user->email;  // Recipient email
            $content = $message;    // Message content

            switch ($recipient->role) {
                case EmailReminderRecipient::ROLE_BIDDER:       // Bidder
                    $content .= $bidderLink;                    // Add link to access eBidding console
                    break;

                case EmailReminderRecipient::ROLE_COMMITTEE:    // Committee member
                    $content .= $committeeLink;                 // Add link to view eBidding details
                    break;

                default:
                    // Do nothing
            }
            $viewData = ['messageContent' => $content];

			Mail::queueOn($queue, $view, $viewData, function ($mail) use ($email, $subject) {
                $mail->to($email)
						->subject($subject);
			});
		}
	}
	
}
