<?php namespace PCK\Helpers;

use PCK\Users\User;

class Mailer {

    protected $attachments = [];
    protected $recipients  = [];
    protected $subject;
    protected $view;
    protected $viewData;
    protected $queue;
    protected $private     = false;
    protected $ccList      = [];
    protected $bccList     = [];

    public function __construct($subject, $view, $viewData = array())
    {
        $this->queue    = 'default';
        $this->subject  = $subject;
        $this->view     = $view;
        $this->viewData = $viewData;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function addAttachment($pathToAttachment, $filename = null)
    {
        if( $filename )
        {
            $this->attachments[ $filename ] = $pathToAttachment;
        }
        else
        {
            $this->attachments[] = $pathToAttachment;
        }
    }

    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }

    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    public function setPrivacy($private)
    {
        $this->private = $private;
    }

    public function setCCList($emails)
    {
        $this->ccList = $emails;
    }

    public function setBCCList($emails)
    {
        $this->bccList = $emails;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function setViewDataItem($key, $value)
    {
        $this->viewData[ $key ] = $value;
    }

    public function setViewData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->setViewDataItem($key, $value);
        }
    }

    protected function pushToQueue($recipient)
    {
        if(empty($recipient->email))
        {
            return false;
        }

        $this->viewData['recipientName']   = $recipient->name;
        $this->viewData['recipientLocale'] = $recipient->settings ? $recipient->settings->language->code : getenv('DEFAULT_LANGUAGE_CODE');

        $subject     = $this->subject;
        $private     = $this->private;
        $attachments = $this->attachments;
        $ccList      = $this->ccList;
        $bccList     = $this->bccList;

        \Log::info("Queueing mail: To [$recipient->email], subject [$subject]");

        \Mail::queueOn(
            $this->queue,
            $this->view,
            $this->viewData,
            function($message) use ($recipient, $subject, $private, $attachments, $ccList, $bccList)
            {
                $message->to($recipient->email, $recipient->name)
                    ->subject($subject);

                foreach($attachments as $filename => $attachment)
                {
                    $options = [ 'mime' => 'application/pdf' ];

                    if( $filename ) $options['as'] = $filename;

                    $message->attach($attachment, $options);
                }

                foreach($ccList as $email) $message->cc($email);

                foreach($bccList as $email) $message->bcc($email);

                if( ( ! $private ) && ( ! empty( getenv('cc_list') ) ) ) $message->cc(explode(',', getenv('cc_list')));
                if( ( ! $private ) && ( ! empty( getenv('bcc_list') ) ) ) $message->bcc(explode(',', getenv('bcc_list')));
            }
        );

        \Log::info("Queued mail: To [$recipient->email], subject [$this->subject]");
    }

    public function send()
    {
        foreach($this->recipients as $recipient)
        {
            if( $recipient->account_blocked_status )
            {
                \Log::info("Mail not queued: User is blocked [$recipient->email], subject [$this->subject]");
                continue;
            }

            if(empty($recipient->email))
            {
                \Log::info("Mail not queued: Empty email address for user [$recipient->name], subject [$this->subject]");
                continue;
            }
            
            $this->pushToQueue($recipient);
        }
    }

    /**
     * @deprecated
     * Queues an email message.
     *
     * @param       $queue
     * @param       $view
     * @param User  $recipient
     * @param       $subject
     * @param array $viewData
     * @param bool  $privateMessage
     */
    public static function queue($queue, $view, User $recipient, $subject, $viewData = array(), $privateMessage = false)
    {
        if( $recipient->account_blocked_status )
        {
            \Log::info("Mail not queued: User is blocked [$recipient->email], subject [$subject]");
            return;
        }

        if(empty($recipient->email))
        {
            \Log::info("Mail not queued: Empty email address for user [$recipient->name], subject [$subject]");
            return;
        }
        
        $viewData['recipientName'] = $recipient->name;
        $viewData['recipientLocale'] = $recipient->settings->language->code;

        \Log::info("Queueing mail: To [$recipient->email], subject [$subject]");

        \Mail::queueOn(
            $queue,
            $view,
            $viewData,
            function($message) use ($recipient, $subject, $privateMessage)
            {
                $message->to($recipient->email, $recipient->name)
                    ->subject($subject);

                if( ( ! $privateMessage ) && ( ! empty( getenv('cc_list') ) ) ) $message->cc(explode(',', getenv('cc_list')));
                if( ( ! $privateMessage ) && ( ! empty( getenv('bcc_list') ) ) ) $message->bcc(explode(',', getenv('bcc_list')));
            }
        );

        \Log::info("Queued mail: To [$recipient->email], subject [$subject]");
    }

    /**
     * @deprecated
     * Queues email messages.
     *
     * @param       $recipients
     * @param       $queue
     * @param       $view
     * @param       $subject
     * @param array $viewData
     */
    public static function queueMultiple($recipients, $queue, $view, $subject, $viewData = array())
    {
        $recipientIds = [];

        foreach($recipients as $recipient)
        {
            if(in_array($recipient->id, $recipientIds)) continue;

            self::queue($queue, $view, $recipient, $subject, $viewData);

            $recipientIds[] = $recipient->id;
        }
    }

}