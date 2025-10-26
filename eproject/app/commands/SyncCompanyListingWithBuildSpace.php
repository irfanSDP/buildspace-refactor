<?php

use Illuminate\Console\Command;
use PCK\Commands\SyncCompanyListingWithBuildSpaceService;

class SyncCompanyListingWithBuildSpace extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'system:sync-company-listing-with-buildspace';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync Company\'s Listing with BuildSpace\'s database';

	/**
	 * @var SyncCompanyListingWithBuildSpaceService
	 */
	private $service;

	/**
	 * Create a new command instance.
	 * @param SyncCompanyListingWithBuildSpaceService $service
	 */
	public function __construct(SyncCompanyListingWithBuildSpaceService $service)
	{
		parent::__construct();

		$this->service = $service;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->service->handle();

		$this->output->write('Successfully sync company\'s listing with BuildSpace\'s database');
	}

}