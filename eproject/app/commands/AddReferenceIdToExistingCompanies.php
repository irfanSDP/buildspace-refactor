<?php

use Illuminate\Console\Command;
use PCK\Commands\AddReferenceIdToExistingCompaniesService;

class AddReferenceIdToExistingCompanies extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'system:add-reference-id-to-existing-companies';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add reference ID to existing Companies.';

	/**
	 * @var AddReferenceIdToExistingCompaniesService
	 */
	private $service;

	/**
	 * Create a new command instance.
	 * @param AddReferenceIdToExistingCompaniesService $service
	 */
	public function __construct(AddReferenceIdToExistingCompaniesService $service)
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

		$this->output->write('Successfully added Reference ID to existing Companies !');
	}

}