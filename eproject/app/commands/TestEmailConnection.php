<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TestEmailConnection extends Command
{
    protected $name = 'test:email-connection';
    protected $description = 'To test email connnection based on the config in env';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        try
        {
            $transport = Swift_SmtpTransport::newInstance(getenv('MAIL_HOST'), getenv('MAIL_PORT'), getenv('MAIL_ENCRYPTION'));
            $transport->setUsername(getenv('MAIL_USERNAME'));
            $transport->setPassword(getenv('MAIL_PASSWORD'));
            $mailer = \Swift_Mailer::newInstance($transport);
            $mailer->getTransport()->start();
            
            return $this->info("Success >> Successfully connected!");
        }
        catch (Swift_TransportException $e)
        {
            return $this->error("Error >> ".$e->getMessage());
        } catch (Exception $e)
        {
            return $this->error("Error >> ".$e->getMessage());
        }
    }
}
