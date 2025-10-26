<?php

use Illuminate\Console\Command;
use PCK\SystemModules\SystemModuleConfiguration;

abstract class SystemModuleCommand extends Command {

	protected function displayInfo()
	{
		$this->info(" Module ID | Enabled | Name");
		$this->info("-----------|---------|------------------");

		$initiatedModules = [];

		foreach(SystemModuleConfiguration::orderBy('module_id', 'asc')->get() as $record)
		{
			$this->printInfo($record->module_id, $record->is_enabled);

			$initiatedModules[] = $record->module_id;
		}

		foreach(SystemModuleConfiguration::getModuleIds() as $moduleId)
		{
			if(in_array($moduleId, $initiatedModules)) continue;

			$this->printInfo($moduleId, false);
		}

		$this->info("");
	}

	protected function printInfo($moduleId, $isEnabled)
	{
		$this->info(" " . str_pad($moduleId, 9, ' ', STR_PAD_LEFT) . " | " . ( $isEnabled ? 'true   ' : 'false  ' ) . " | " . SystemModuleConfiguration::getModuleName($moduleId));
	}

}