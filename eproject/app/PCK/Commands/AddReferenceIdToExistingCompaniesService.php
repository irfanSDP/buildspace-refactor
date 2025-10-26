<?php namespace PCK\Commands;

use PCK\Base\Helpers;
use PCK\Companies\Company;
use PCK\Companies\CompanyRepository;
use Illuminate\Database\Eloquent\Collection;

class AddReferenceIdToExistingCompaniesService {

	private $companyRepo;

	public function __construct(CompanyRepository $companyRepo)
	{
		$this->companyRepo = $companyRepo;
	}

	public function handle()
	{
		$companies = $this->companyRepo->getCompaniesWithoutReferenceId();

		foreach ( $companies->chunk(100) as $chunkCompanies )
		{
			$this->assignReferenceId($chunkCompanies);
		}
	}

	private function assignReferenceId(Collection $companies)
	{
		$data = array();

		foreach ( $companies as $company )
		{
			$data[] = array(
				'id'           => $company->id,
				'reference_id' => str_random(Company::REFERENCE_ID_LENGTH),
			);
		}

		if ( count($data) )
		{
			Helpers::updateBatch('companies', $data);
		}
	}

}