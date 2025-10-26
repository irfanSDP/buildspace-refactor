<?php

use GuzzleHttp\Client;

class Notifications {

    protected static function send($eprojectUrlSubdirectory, array $params)
    {
        $client = new Client(array(
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        $postParams = array();

        foreach($params as $name => $content)
        {
            if( is_array($content) )
            {
                foreach($content as $key => $item)
                {
                    $postParams[] = array(
                        'name' => "{$name}[$key]",
                        'contents' => $item
                    );
                }
            }
            else
            {
                $postParams[] = array(
                    'name' => $name,
                    'contents' => $content
                );
            }
        }

        try
        {
            $res = $client->post($eprojectUrlSubdirectory, [
                'multipart' => $postParams,
            ]);

            $success = json_decode($res->getBody())->success;
        }
        catch(Exception $e)
        {
            throw $e;
        }

        return $success;
    }

    public static function sendNewClaimRevisionInitiatedNotifications($claimRevisionId)
    {
        return self::send('buildspace/notifications/new-claim-revision-initiated', array('claim_revision_id' => $claimRevisionId));
    }

    public static function sendContractorClaimSubmittedNotifications($claimRevisionId)
    {
        return self::send('buildspace/notifications/claim-submitted', array('claim_revision_id' => $claimRevisionId));
    }

    public static function sendClaimApprovedNotifications($claimRevisionId)
    {
        return self::send('buildspace/notifications/claim-approved', array('claim_revision_id' => $claimRevisionId));
    }
}