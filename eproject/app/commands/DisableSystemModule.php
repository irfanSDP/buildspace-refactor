<?php

use Symfony\Component\Console\Input\InputArgument;
use PCK\SystemModules\SystemModuleConfiguration;

class DisableSystemModule extends SystemModuleCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'module:disable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Disables a system module.';

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
		if( $this->argument('module_id') )
		{
			$success = SystemModuleConfiguration::enable($this->argument('module_id'), false);

			if( $success )
			{
				$this->info("Module [".SystemModuleConfiguration::getModuleName($this->argument('module_id'))."] has been disabled.");
			}
			else
			{
				$this->error("Unable to dusabke module ({$this->argument('module_id')}). Please check that you have the correct module ID (use [ php artisan module:info ]).");
			}
		}
		else
		{
			$this->displayInfo();

			$enabledModules = [];

			foreach(SystemModuleConfiguration::where('is_enabled', '=', true)->orderBy('module_id', 'asc')->get() as $record)
			{
				$enabledModules[SystemModuleConfiguration::getModuleName($record->module_id)] = $record->module_id;
			}

			if( ! empty($enabledModules) )
			{
				$moduleId = $this->choice('Disable module:', $enabledModules);

				$success = SystemModuleConfiguration::enable($moduleId, false);

				if($success)
				{
					$this->info("Module [".SystemModuleConfiguration::getModuleName($moduleId)."] has been disabled.");
				}
				else
				{
					$this->error("Unable to disable module ({$moduleId})");
				}
			}
			else
			{
				$this->info('All modules are disabled');
			}
		}
	}

	/**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
                array('module_id', InputArgument::OPTIONAL, 'Module identifier. Check with [ php artisan module:info ].'),
        );
    }
}
