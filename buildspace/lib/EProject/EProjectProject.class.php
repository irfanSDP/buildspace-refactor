<?php

class EProjectProject extends BaseEProjectProject
{
    const STATUS_TYPE_DESIGN = 1;
    const STATUS_TYPE_DESIGN_TEXT = 'Design';

    const STATUS_TYPE_POST_CONTRACT = 4;
    const STATUS_TYPE_POST_CONTRACT_TEXT = 'Post Contract';

    const STATUS_TYPE_COMPLETED = 8;
    const STATUS_TYPE_COMPLETED_TEXT = 'Completed';

    const STATUS_TYPE_RECOMMENDATION_OF_TENDERER = 16;
    const STATUS_TYPE_RECOMMENDATION_OF_TENDERER_TEXT = 'Rec. of Tenderer';

    const STATUS_TYPE_LIST_OF_TENDERER = 32;
    const STATUS_TYPE_LIST_OF_TENDERER_TEXT = 'List of Tenderer';

    const STATUS_TYPE_CALLING_TENDER = 64;
    const STATUS_TYPE_CALLING_TENDER_TEXT = 'Calling Tender';

    const STATUS_TYPE_CLOSED_TENDER = 128;
    const STATUS_TYPE_CLOSED_TENDER_TEXT = 'Closed Tender';

    const STATUS_TYPE_E_BIDDING     = 256;
    const STATUS_TYPE_E_BIDDING_TEXT = 'E-Bidding';
    
    public function isSubProject()
    {
        return ( ! $this->isMainProject() );
    }

    public function isMainProject()
    {
        return ( is_null($this->parent_project_id) );
    }

    public function getLatestTender($hydrationMode=null)
    {
        $query = EProjectTenderTable::getInstance()
            ->createQuery('t')
            ->select('t.*')
            ->where('t.project_id = ?', $this->id)
            ->orderBy('t.count DESC')
            ->limit(1);

            if($hydrationMode)
            {
                $query->setHydrationMode($hydrationMode);
            }

        return $query->fetchOne();
    }

    public function getPostContractInfo()
    {
        if($this->status_id == EProjectProject::STATUS_TYPE_POST_CONTRACT || $this->status_id == EProjectProject::STATUS_TYPE_COMPLETED)
        {
            if($this->PAM2006ProjectDetail->id)
            {
                return $this->PAM2006ProjectDetail;
            }
            elseif($this->IndonesiaCivilContractInformation->id)
            {
                return $this->IndonesiaCivilContractInformation;
            }

            return null;
        }

        return null;
    }

    public function submitTendererRates(sfGuardUser $user, $pathToFile)
    {
        $client = new GuzzleHttp\Client(array(
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res = $client->post("buildspace/tenderer-rates/submit/project/{$this->id}/user/{$user->Profile->eproject_user_id}", [
                'multipart' => [
                    [
                        'name'     => 'ratesFile',
                        'contents' => fopen($pathToFile, 'r')
                    ],
                ]
            ]);

            if($res->getBody())
            {
                $response = json_decode($res->getBody());

                if( $response->success ) unlink($pathToFile);

                return [
                    'success'      => $response->success,
                    'errorMessage' => $response->errorMessage,
                ];
            }
            else
            {
                throw new Exception("No body returned from response");
            }
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }
}
