<?php namespace PCK\QueueJobs;

use GuzzleHttp\Client;
use Illuminate\Queue\Jobs\Job;

class UpdateLetterOfAwardStatus {

    public function fire(Job $job, array $data)
    {
        \Log::info('Starting update for Letter of Award status (project id = ' . $data['project']['id'] . ')');

        $success = false;

        try
        {
            $client = new Client([
                'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
                'base_uri' => getenv('BUILDSPACE_URL'),
            ]);

            $request = $client->post('eproject_api/updateLetterOfAwardStatus', array(
                'multipart' => [
                    [
                        'name'     => 'approved',
                        'contents' => $data['approved'],
                    ],
                    [
                        'name'     => 'project_id',
                        'contents' => $data['project']['id'],
                    ],
                    [
                        'name'     => 'user_identifier',
                        'contents' => base64_encode($data['userId']),
                    ],
                ]
            ));

            $response = json_decode($request->getBody()->getContents(), true);

            if( $success = $response['success'] )
            {
                \Log::info('Updated Letter of Award status (project id = ' . $data['project']['id'] . ')');

                $job->delete();
            }
            else
            {
                \Log::error($response['errorMsg']);
            }
        }
        catch(\Exception $e)
        {
            \Log::error('Letter of Award status update failed (project id = ' . $data['project']['id'] . '). Message -> ' . $e->getMessage());
        }

        \Log::info('Ended update for Letter of Award status (project id = ' . $data['project']['id'] . ')');

        return $success;
    }

}