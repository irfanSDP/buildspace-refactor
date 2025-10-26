<?php

use Symfony\Component\Console\Input\InputArgument;
use PCK\SystemModules\SystemModuleConfiguration;

class SystemModuleInfo extends SystemModuleCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'module:info';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Displays the info for system modules.';

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
		$this->displayInfo();
	}

}
