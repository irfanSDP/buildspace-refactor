<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;

use PCK\DigitalStar\Evaluation\DsEvaluation;

class DsEvaluationGenerateFormsWhenInProgress extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'digital-star:ds-evaluation-generate-forms-when-in-progress';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generates forms for evaluations that are currently in progress';

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

		$evaluation = DsEvaluation::find($id = $this->argument('ds_evaluation_id'));
		$evaluation->generateFormsWhenInProgress();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	*/
	protected function getArguments()
	{
		return array(
			array('ds_evaluation_id', InputArgument::REQUIRED, 'DS Evaluation ID'),
		);
	}

}
