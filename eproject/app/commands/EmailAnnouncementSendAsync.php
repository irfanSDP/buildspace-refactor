<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use PCK\EmailAnnouncement\EmailAnnouncement;
use PCK\QueueJobs\EmailAnnouncementSendAsync as QueueJobsEmailAnnouncementSendAsync;

class EmailAnnouncementSendAsync extends Command
{
    protected $name = 'system:send-email-announcement-async';
    protected $description = 'To send email announcements asynchronously';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        \Log::info("Firing scheduled command", [
            'class'   => get_class($this),
            'command' => $this->name,
        ]);

        $emailAnnouncementId = $this->argument('email_announcement_id');

        $emailAnnouncement = EmailAnnouncement::find($emailAnnouncementId);

        if(is_null($emailAnnouncement))
        {
            return $this->error("Invalid email announcement id");
        }

        \Queue::push(QueueJobsEmailAnnouncementSendAsync::class, ['email_announcement_id' => $emailAnnouncementId], 'default');

        \Log::info("Scheduled command has been fired", [
            'class'   => get_class($this),
            'command' => $this->name,
        ]);
    }

    protected function getArguments()
    {
        return [
            ['email_announcement_id', InputArgument::REQUIRED, 'Email Announcement ID']
        ];
    }
}
