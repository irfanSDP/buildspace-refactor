<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\SystemModules\SystemModuleConfiguration;

class StartAndEndDigitalStarCycles extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'digital-star:start-and-end-cycles';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Starts and stops Digital Star cycles based on their start and end dates.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
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
		if (SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
		{
			\Log::info("Firing scheduled command", [
	            'class'   => get_class($this),
	            'command' => $this->name,
	        ]);

	        \Queue::push('PCK\DigitalStar\QueueJobs\StartAndEndDigitalStarCycles', [], 'default');
		}
	}
}
