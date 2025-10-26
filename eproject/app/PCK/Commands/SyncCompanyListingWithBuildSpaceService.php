<?php namespace PCK\Commands;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PCK\Companies\CompanyRepository;
use PCK\Buildspace\CompanyRepository as BSCompanyRepository;

class SyncCompanyListingWithBuildSpaceService {

	private $bsCompanyRepo;

	private $companyRepo;

	public function __construct(BSCompanyRepository $bsCompanyRepo, CompanyRepository $companyRepo)
	{
		$this->bsCompanyRepo = $bsCompanyRepo;
		$this->companyRepo   = $companyRepo;
	}

	public function handle()
	{
		$bsCompanies       = $this->getBuildSpaceCompanyListing();
		$eProjectCompanies = $this->getEProjectCompanyListing($bsCompanies);

		foreach ( $eProjectCompanies->chunk(100) as $chunkCompanies )
		{
			$this->createNewCompaniesIntoBuildSpace($chunkCompanies);
		}
	}

	private function getBuildSpaceCompanyListing()
	{
		$bsCompanies = $this->bsCompanyRepo->getAllCompaniesReferenceId();

		return $bsCompanies->lists('reference_id');
	}

	private function getEProjectCompanyListing(array $notIn)
	{
		return $this->companyRepo->getCompaniesNotInReferenceId($notIn);
	}

	private function createNewCompaniesIntoBuildSpace(Collection $companies)
	{
		$timestamp = new Carbon();

		foreach ( $companies as $company )
		{
			// Force record update
			$company->updated_at = $timestamp;
			$company->save();
		}
	}

}