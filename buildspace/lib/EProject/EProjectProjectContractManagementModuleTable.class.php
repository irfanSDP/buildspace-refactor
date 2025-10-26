<?php

use GuzzleHttp\Client;

class EProjectProjectContractManagementModuleTable extends Doctrine_Table {
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EProjectProjectContractManagementModule');
    }

    public static function getContractManagementVerifiers(ProjectMainInformation $mainInfo, int $moduleIdentifier)
    {
        $projectOriginId = $mainInfo->eproject_origin_id;

        $selectedUsers = array();

        if( ! $projectOriginId ) return $selectedUsers;

        // $projectOriginId has been set to null before, no identifiable cause.
        if( empty( $projectOriginId ) )
        {
            throw new Exception('Column eproject_origin_id is null (in ' . ProjectMainInformationTable::getInstance()->getTableName() . ' table)');
        }

        $client = new Client(array(
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res = $client->post("contract-management/verifiers/get/project/" . $projectOriginId . "/module/" . $moduleIdentifier);

            $eProjectUserIds = json_decode($res->getBody())->userIds;

            foreach($eProjectUserIds as $eProjectUserId => $verifierInfo)
            {
                $eProjectUser = EProjectUserTable::getInstance()->find($eProjectUserId);

                $selectedUsers[ $eProjectUser->getBuildSpaceUser()->user_id ] = array(
                    'id' => $eProjectUser->getBuildSpaceUser()->user_id,
                    'days_to_verify' => $verifierInfo->days_to_verify,
                );
            }

            return $selectedUsers;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

}
