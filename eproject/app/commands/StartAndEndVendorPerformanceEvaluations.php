<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\SystemModules\SystemModuleConfiguration;

class StartAndEndVendorPerformanceEvaluations extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'system:start-and-end-vendor-performance-evaluations';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Starts and stops Vendor Performance Evaluations based on their start and end dates.';

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
		if(SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT))
		{
			\Log::info("Firing scheduled command", [
	            'class'   => get_class($this),
	            'command' => $this->name,
	        ]);

	        \Queue::push('PCK\QueueJobs\StartAndEndVendorPerformanceEvaluations', [], 'default');
		}
	}
}
