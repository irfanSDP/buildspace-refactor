<?php

use Symfony\Component\Console\Input\InputArgument;
use PCK\SystemModules\SystemModuleConfiguration;

class EnableSystemModule extends SystemModuleCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'module:enable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Enables a system module.';

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
			$success = SystemModuleConfiguration::enable($this->argument('module_id'), true);

			if( $success )
			{
				$this->info("Module [".SystemModuleConfiguration::getModuleName($this->argument('module_id'))."] has been enabled.");
			}
			else
			{
				$this->error("Unable to enable module ({$this->argument('module_id')}). Please check that you have the correct module ID (use [ php artisan module:info ]).");
			}
		}
		else
		{
			$this->displayInfo();

			$disabledModules = [];

			foreach(SystemModuleConfiguration::where('is_enabled', '=', false)->orderBy('module_id', 'asc')->get() as $record)
			{
				$disabledModules[SystemModuleConfiguration::getModuleName($record->module_id)] = $record->module_id;
			}

			if( ! empty($disabledModules) )
			{
				$moduleId = $this->choice('Enable module:', $disabledModules);

				$success = SystemModuleConfiguration::enable($moduleId, true);

				if($success)
				{
					$this->info("Module [".SystemModuleConfiguration::getModuleName($moduleId)."] has been enabled.");
				}
				else
				{
					$this->error("Unable to enable module ({$moduleId})");
				}
			}
			else
			{
				$this->info('All modules are enabled');
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
