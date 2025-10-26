<?php namespace PCK\Buildspace;

class CompanyRepository {

	private $company;

	public function __construct(Company $company)
	{
		$this->company = $company;
	}

	public function getAllCompaniesReferenceId()
	{
		return $this->company
			->whereNotNull('reference_id')
			->get(array('reference_id'));
	}

}