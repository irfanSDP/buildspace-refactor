<?php

/**
 * claimTransfer actions.
 *
 * @package    buildspace
 * @subpackage claimTransfer
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class claimTransferActions extends sfActions
{
    public function executeExportClaims(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $project = ProjectStructureTable::getProjectInformationByProjectId($request->getParameter('pid')) and
            $claimRevision = Doctrine_Core::getTable('PostContractClaimRevision')->find($request->getParameter('revision_id'))
        );

        $filename = $request->getParameter('filename');

        if(strlen($filename) <= 0) $filename = "{$project['structure']['title']}_v{$claimRevision->version}";

        $errorMsg = null;

        try
        {
            $count = 0;

            $filesToZip = array();

            $projectUniqueId = $project['mainInformation']['unique_id'];

            $sfProjectExport = new sfBuildspaceExportProjectXML($count . "_" . $project['structure']['id'], $projectUniqueId, ExportedFile::EXPORT_TYPE_CLAIM);

            $currentRevision = ProjectRevisionTable::getCurrentSelectedProjectRevisionFromBillId($project['structure']['root_id'], Doctrine_Core::HYDRATE_ARRAY);

            $sfProjectExport->process($project['structure'], $project['mainInformation'], null, array( $currentRevision ), $project['tenderAlternatives'], true);

            array_push($filesToZip, $sfProjectExport->getFileInformation());

            $sfClaimsExport = new sfBuildspaceExportClaimsXML(++$count . '_' . $project['structure']['id'], $sfProjectExport->uploadPath);
            $sfClaimsExport->process($claimRevision);

            array_push($filesToZip, $sfClaimsExport->getFileInformation());

            foreach($sfClaimsExport->attachmentFileInformation as $fileInfo)
            {
                array_push($filesToZip, $fileInfo);
            }

            $sfZipGenerator = new sfZipGenerator("ExportedClaims_{$project['structure']['id']}_v{$claimRevision->version}", null, 'ebqclaim', true, true);

            $sfZipGenerator->createZip($filesToZip);

            $fileInfo = $sfZipGenerator->getFileInfo();

            $fileSize     = filesize($fileInfo['pathToFile']);
            $fileContents = file_get_contents($fileInfo['pathToFile']);
            $mimeType     = Utilities::mimeContentType($fileInfo['pathToFile']);

            unlink($fileInfo['pathToFile']);

            $this->getResponse()->clearHttpHeaders();
            $this->getResponse()->setStatusCode(200);
            $this->getResponse()->setContentType($mimeType);
            $this->getResponse()->setHttpHeader(
                "Content-Disposition",
                "attachment; filename*=UTF-8''" . rawurlencode($filename) . "." . $fileInfo['extension']
            );
            $this->getResponse()->setHttpHeader('Content-Description', 'File Transfer');
            $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
            $this->getResponse()->setHttpHeader('Content-Length', $fileSize);
            $this->getResponse()->setHttpHeader('Cache-Control', 'public, must-revalidate');
            // if https then always give a Pragma header like this  to overwrite the "pragma: no-cache" header which
            // will hint IE8 from caching the file during download and leads to a download error!!!
            $this->getResponse()->setHttpHeader('Pragma', 'public');
            $this->getResponse()->sendHttpHeaders();

            if (ob_get_contents()) ob_end_flush();

            return $this->renderText($fileContents);
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => false,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeImportClaims(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $claimRevision = PostContractClaimRevisionTable::getInstance()->find($request->getParameter('revision_id'))
        );

        $actingUser = $request->hasParameter('user_id') ? Doctrine_Core::getTable('sfGuardUser')->find($request->getParameter('user_id')) : $this->getUser()->getGuardUser();
        $project    = $claimRevision->PostContract->ProjectStructure;

        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');
        $pathToFile     = null;
        $fileToUnzip    = [];

        foreach($request->getFiles() as $file)
        {
            if( is_readable($file['tmp_name']) )
            {
                $fileToUnzip['name'] = $newName = Utilities::massageText(date('dmY_H_i_s'));
                $fileToUnzip['ext']  = $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile          = $tempUploadPath . $newName . '.' . $ext;
                $fileToUnzip['path'] = $tempUploadPath;
                move_uploaded_file($file['tmp_name'], $pathToFile);
            }
            else
            {
                return $this->renderJson([
                    'running' => false
                ]);
            }
        }

        $logDir      = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'importPostContractClaimLog';
        $logFilename = $project->id.'-import_claim.yaml';

        $fileExists = file_exists($logDir.DIRECTORY_SEPARATOR.$logFilename);
        if($fileExists)
        {
            unlink($logDir.DIRECTORY_SEPARATOR.$logFilename);
        }

        $executedTimestamp = date('Y-m-d H:i:s');
        $yaml = sfYaml::dump([
            'revision_id' => $claimRevision->id,
            'project_id' => $project->id,
            'total_files' => 0,
            'total_imported_files' => 0,
            'executed_by' => $actingUser->id,
            'executed_at' => $executedTimestamp
        ]);
        
        file_put_contents($logDir.DIRECTORY_SEPARATOR.$logFilename, $yaml);

        $proc = new BackgroundProcess("exec php ".sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR."symfony bgprocess:import_post_contract_claim ".$fileToUnzip['name']." ".$fileToUnzip['ext']." '".$fileToUnzip['path']."' ".$claimRevision->id." ".$actingUser->id." 2>&1 | tee ".sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR."import_post_contract_claim-".$claimRevision->id.".log");
        $proc->run();

        return $this->renderJson([
            'running' => true
        ]);
    }

    public function executeGetImportClaimProgress(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $project = ProjectStructureTable::getInstance()->find((int)$request->getParameter('id'));

        $totalFiles = 0;
        $totalImportedFiles = 0;
        $claimRevision = null;

        if(!$project)
        {
            return $this->renderJson([
                'exists'               => false,
                'version'              => ($claimRevision) ? $claimRevision->version : 0,
                'total_files'          => $totalFiles,
                'total_imported_files' => $totalImportedFiles
            ]);
        }

        $logDir   = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'importPostContractClaimLog';
        $filename = $project->id."-import_claim.yaml";
        $exists   = file_exists($logDir.DIRECTORY_SEPARATOR.$filename);

        if($exists && $project)
        {
            $values             = sfYaml::load($logDir.DIRECTORY_SEPARATOR.$filename);
            $user               = sfGuardUserTable::getInstance()->find((int)$values['executed_by']);
            $claimRevision      = PostContractClaimRevisionTable::getInstance()->find((int)$values['revision_id']);
            $totalFiles         = (int)$values['total_files'];
            $totalImportedFiles = (int)$values['total_imported_files'];
        }

        return $this->renderJson([
            'exists'               => $exists,
            'version'              => ($claimRevision) ? $claimRevision->version : 0,
            'total_files'          => $totalFiles,
            'total_imported_files' => $totalImportedFiles
        ]);
    }
}