<?php namespace PCK\QueueJobs;

use Illuminate\Queue\Jobs\Job;
use PCK\EmailAnnouncement\EmailAnnouncement;
use PCK\Helpers\Mailer;

class EmailAnnouncementSendAsync {

    protected $transaction;
    protected $batchSize = 10;

    public function fire(Job $job, array $data)
    {
        \Log::info("Sending email annoucements", [
            'class' => get_class($this),
            'model' => EmailAnnouncement::class,
            'id'    => $data['email_announcement_id'],
        ]);

        $emailAnnouncement = EmailAnnouncement::find($data['email_announcement_id']);

        if(is_null($emailAnnouncement))
        {
            return \Log::info("Invalid email announcement id");
        }

         $listOfRecipients = [];

        foreach($emailAnnouncement->activeRecipients as $recipient)
        {
            array_push($listOfRecipients, $recipient->user);
        }

        \Log::info("Sending to " . count($listOfRecipients) . " user(s).");

        try
        {
            $mailer = new Mailer($emailAnnouncement->subject, 'email_announcements.partials.email_announcement_message', [
                'messageContent' => $emailAnnouncement->message,
            ]);

            $mailer->setRecipients($listOfRecipients);

            foreach($emailAnnouncement->attachments as $attachment)
            {
                $fullpath = base_path() .$attachment->file->path . $attachment->file->filename;

                $mailer->addAttachment($fullpath, $attachment->file->filename);
            }

            $mailer->send();
        }
        catch(\Exception $e)
        {
            \Log::info("Encountered an exception while sending email announcements");
            \Log::info($e->getMessage());
            \Log::info($e->getTraceAsString());
        }
        finally
        {
            return $job->delete();
        }
    }
}