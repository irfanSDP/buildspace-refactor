<?php

use GuzzleHttp\Client;

abstract class BaseActions extends sfActions
{
    protected function getModuleProjectData(ProjectStructure $project, $status = null, $checkUserPermission = true)
    {
        $user = $this->getUser()->getGuardUser();

        $records = ProjectStructureTable::getProjectsByUser($user, $status ?? $project->MainInformation->status, $checkUserPermission);

        foreach($records as $record)
        {
            if($project->id == $record['id']) return $record;
        }

        return null;
    }

    protected function sendExportExcelHeader($filename, $tmpFile)
    {
        $filename     = $filename . '.xlsx';
        $fileSize     = filesize($tmpFile);
        $fileContents = file_get_contents($tmpFile);
        unlink($tmpFile);

        $this->getResponse()->clearHttpHeaders();
        $this->getResponse()->setStatusCode(200);
        $this->getResponse()->setContentType('application/vnd.ms-excel');
        $this->getResponse()->setHttpHeader(
            'Content-Disposition',
            "attachment; filename=\"$filename\""
        );
        $this->getResponse()->setHttpHeader(
            "Content-Disposition",
            "attachment; filename*=UTF-8''" . rawurlencode($filename)
        );
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Content-Length', $fileSize);

        return $this->renderText($fileContents);
    }

    /**
     * Convert the given array of data into a JSON response.
     *
     * <code>return $this->renderJson(array('username' => 'john'))</code>
     *
     * @param array $data Data to encode as JSON
     *
     * @return sfView::NONE
     */
    public function renderJson($data)
    {
        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode($data));

        return sfView::NONE;
    }

    protected function isFormValid(sfWebRequest $request, sfForm $form)
    {
        if ( $request->isMethod('post') )
        {
            $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        }

        return $form->isValid() ? true : false;
    }

    protected function createNewPDFGenerator(sfBuildspaceBQMasterFunction $reportGenerator)
    {
        return new WkHtmlToPdf(array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportGenerator->getMarginTop(),
            'margin-right'   => $reportGenerator->getMarginRight(),
            'margin-bottom'  => $reportGenerator->getMarginBottom(),
            'margin-left'    => $reportGenerator->getMarginLeft(),
            'page-size'      => $reportGenerator->getPageSize(),
            'orientation'    => $reportGenerator->getOrientation()
        ));
    }

    protected function getBQStyling()
    {
        return file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
    }

    protected function uploadFileToEProject(ProjectMainInformation $mainInfo, array $fileInfo, $apiLink, $addendumVersion = null)
    {
        // get current user's eProject User ID
        $userOriginId = $this->getUser()->getProfile()->eproject_user_id;

        $projectOriginId = $mainInfo->eproject_origin_id;

        // $projectOriginId has been set to null before, no identifiable cause.
        if(empty($projectOriginId))
        {
            throw new Exception('Column eproject_origin_id is null (in ' . ProjectMainInformationTable::getInstance()->getTableName() . ' table), project does not exist in eproject.');
        }

        $client = new Client(array(
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res = $client->post($apiLink."/".$projectOriginId, [
                'multipart' => [
                    [
                        'name'     => 'posted_by',
                        'contents' => $userOriginId
                    ],[
                        'name'     => 'file',
                        'contents' => fopen($fileInfo['pathToFile'], 'r')
                    ],[
                        'name'     => 'addendumVersion',
                        'contents' => $addendumVersion
                    ]
                ]
            ]);

            unlink($fileInfo['pathToFile']);

            return $res;
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }
}
