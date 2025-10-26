<?php namespace PCK\QueueJobs;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Queue\Jobs\Job;

class SyncContractorRatesIntoBuildSpace {

	private $carbon;

	public function __construct(Carbon $carbon)
	{
		$this->carbon = $carbon;
	}

	public function fire(Job $job, array $data)
	{
		$projectId             = $data['project_id'];
		$contractorReferenceId = $data['contractor_reference_id'];
		$filePath              = $data['filePath'];

		$response = $this->sendRequestToBuildSpace($projectId, $contractorReferenceId, $filePath);

		if ( $response->success )
		{
			\Log::info("Contractor Rates for Project ID: {$projectId} synced. Contractor Reference ID: {$contractorReferenceId} - {$response->errorMsg} at {$this->carbon}.");

			return $job->delete();
		}

		$message = "Error for Project ID: {$projectId} for Contractor Reference ID: {$contractorReferenceId} - {$response->errorMsg} at {$this->carbon}.";

        \Log::error($message);

		// log for error
		echo $message;
	}

    private function sendRequestToBuildSpace($projectId, $contractorReferenceId, $filePath)
    {
        $client = new Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => \Config::get('buildspace.BUILDSPACE_URL')
        ));

        $response = $client->post('eproject_api/importContractorRates', [
            'multipart' => [
                [
                    'name'     => 'eproject_project_id',
                    'contents' => $projectId
                ],[
                    'name'     => 'contractor_unique_id',
                    'contents' => $contractorReferenceId
                ],[
                    'name'     => 'rates_file',
                    'contents' => $filePath
                ]
            ]
        ]);

        return json_decode($response->getBody());
    }
}