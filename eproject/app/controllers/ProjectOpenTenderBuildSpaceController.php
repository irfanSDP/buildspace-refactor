<?php

use PCK\Tenders\Tender;
use PCK\Tenders\TenderRepository;

class ProjectOpenTenderBuildSpaceController extends \BaseController {

	private $tenderRepo;

	public function __construct(TenderRepository $tenderRepo)
	{
		$this->tenderRepo = $tenderRepo;
	}

	/**
	 * Always will get Project's latest Tender to be process
	 *
	 * @param $project
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function syncContractorRatesWithBuildSpace($project)
	{
		$project->syncBuildSpaceContractorRates();

		Flash::success('Successfully pushed all the rates by Contractors to BuildSpace. The latest changes will be applied around 5 to 10 minutes !');

		return Redirect::back();
	}

}