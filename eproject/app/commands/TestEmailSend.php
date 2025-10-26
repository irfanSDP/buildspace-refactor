<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Mail\Mailer;

class TestEmailSend extends Command
{
    protected $name = 'test:email-send';
    protected $description = 'To test sending email to the given address';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $emailAddress = $this->argument('email_address');

        if(empty($emailAddress) or !filter_var($emailAddress, FILTER_VALIDATE_EMAIL))
        {
            return $this->error("Email address is invalid");
        }

        $queue = 'default';
        $view = [];
        $viewData = [];

        Mail::queueOn(
            $queue,
            $view,
            $viewData,
            function($message) use ($emailAddress)
            {
                $message->to($emailAddress, 'Test User')
                ->subject("Test email from Eproject console")
                ->setBody("Hi, this is not a spam. It is just a test email!");
            }
        );
    }

    protected function getArguments()
    {
        return [
            ['email_address', InputArgument::REQUIRED, 'Email address']
        ];
    }
}
