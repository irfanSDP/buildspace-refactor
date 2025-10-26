<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationTenderAnalysis\OpenTenderAwardRecommendationBillDetail;
use PCK\Tenders\SubmitTenderRate;
use PCK\Tenders\Services\GetTenderAmountFromImportedZip;
use PCK\Companies\Company;
use PCK\Projects\Project;
use PCK\Tenders\Tender;

class CreateAwardRecommendationBillDetails extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'project:create-award-recommendation-bill-details';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create Award Recommendation Bill Detail records for for a tender.';

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
		$tender = Tender::find($id = $this->argument('tender_id'));

		if(!$tender) {
			$this->error('Unable to find tender with id '.$id.'.');
			\Log::error('Unable to find tender with id '.$id.'.');
		    return;
		}

		\Log::info('Command: '.self::class." - [Tender id: {$id}] Starting process.");

		$project = $tender->project;

		$currentlySelectedTenderer = Company::find($tender->currently_selected_tenderer_id);

		if(!$currentlySelectedTenderer) {
			$this->error('No selected tenderer for tender with id '.$id.'.');
			\Log::info('No selected tenderer for tender with id '.$id.'.');
		    return;
		}

		$submittedBills = SubmitTenderRate::getIncludedBills($project, $tender, $currentlySelectedTenderer);

		$bsProjectMainInformation = $tender->project->getBsProjectMainInformation();

		$tenderAlternative = $bsProjectMainInformation->projectStructure->getAwardedTenderAlternative();

		$relevantBillIds = $tenderAlternative ? array_intersect($submittedBills->lists('id'), $tenderAlternative->tenderAlternativeBills->lists('project_structure_id')) : $submittedBills->lists('id');

		OpenTenderAwardRecommendationBillDetail::where('tender_id', $tender->id)->delete();

		$fileName = SubmitTenderRate::ratesFileName;

		$path = SubmitTenderRate::getContractorRatesUploadPath($project, $tender, $currentlySelectedTenderer) . "/{$fileName}";

		if(!file_exists($path)) {
			$this->error('No submitted rates (by the selected tenderer) for tender with id '.$id.'.');
		    return;
		}

		$service = new GetTenderAmountFromImportedZip($project, $tender, $currentlySelectedTenderer);

		$service->parseBillFiles();

		foreach($service->getParsedBillFileContents() as $billInfo)
		{
		    if(!in_array((int)$billInfo['contents']->attributes()->billId, $relevantBillIds)) continue;

		    $record = new OpenTenderAwardRecommendationBillDetail();
		    $record->tender_id = $tender->id;
		    $record->buildspace_bill_id = (int)$billInfo['contents']->attributes()->billId;

		    $record->bill_amount = 0.00;

		    if(!(is_null($billInfo['contents']->ELEMENTS->item)))
		    {
		    	foreach($billInfo['contents']->ELEMENTS->item as $item) {
		    	    $record->bill_amount += (float)$item->total_amount;
		    	}
		    }

		    $record->save();
		}

		\Log::info('Command: '.self::class." - [Tender id: {$id}] Records updated.");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('tender_id', InputArgument::REQUIRED, 'The tender id.'),
		);
	}
}
