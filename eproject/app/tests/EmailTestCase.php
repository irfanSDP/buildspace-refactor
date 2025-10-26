<?php

use Illuminate\Support\Facades\Mail;

trait MailTracking {
    protected $emails = [];

    /** @before */
    public function setUpMailTracking()
    {
        Mail::getSwiftMailer()->registerPlugin(new TestingMailEventListener($this));
    }

    public function addEmail($email)
    {
        $this->emails[] = $email;
    }


    protected function seeEmailWasSent()
    {
        self::assertNotEmpty($this->emails, "No emails have been sent, expected at least 1.");

        return $this;
    }


}

class TestingMailEventListener implements Swift_Events_EventListener {

    protected $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function beforeSendPerformed($event)
    {
        $this->test->addEmail($event->getMessage());
    }
}

class EmailTestCase extends TestCase
{
    use MailTracking;

    public function testSendEmail()
    {
        Mail::send('emails.unit_test', ['content'=>'This is from unit testing!!'], function($message){
            $message->to('admin@global-pck.com')->subject('Unit testing');
        });

        $this->seeEmailWasSent();
    }
}
