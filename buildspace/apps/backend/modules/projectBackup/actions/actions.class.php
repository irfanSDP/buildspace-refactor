<?php

/**
 * projectBackup actions.
 *
 * @package    buildspace
 * @subpackage projectBackup
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectBackupActions extends BaseActions {

    public function executeIndex(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            strlen($request->getParameter('filename')) > 0 and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid'))
        );

        $errorMsg   = null;
        $filePrefix = null;

        try
        {
            $exportType = null;

            switch ($structure->type)
            {
                case ProjectStructure::TYPE_BILL:
                    $exportType = ExportedFile::EXPORT_TYPE_BACKUP_BILL;
                    $filePrefix = ExportedFile::FILE_PREFIX_BILL;
                    $sfBackup   = new sfBuildspaceBackupBillXML('bill-' . $structure->id, $structure, $exportType);
                    break;
                default:
                    throw new Exception('Invalid Structure Type');
            }

            $sfBackup->process();

            /* Generate Zip File */
            $sfZipGenerator = new sfZipGenerator("Backup_" . $filePrefix . '_' . $structure->id, null, null, true, true);

            $sfZipGenerator->createZip(array( $sfBackup->getFileInformation() ));

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
                "attachment; filename*=UTF-8''" . rawurlencode($request->getParameter('filename')) . "." . $fileInfo['extension']
            );
            $this->getResponse()->setHttpHeader('Content-Description', 'File Transfer');
            $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
            $this->getResponse()->setHttpHeader('Content-Length', $fileSize);
            $this->getResponse()->setHttpHeader('Cache-Control', 'public, must-revalidate');
            // if https then always give a Pragma header like this  to overwrite the "pragma: no-cache" header which
            // will hint IE8 from caching the file during download and leads to a download error!!!
            $this->getResponse()->setHttpHeader('Pragma', 'public');
            $this->getResponse()->sendHttpHeaders();

            ob_end_flush();

            return $this->renderText($fileContents);
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => false, 'errorMsg' => $errorMsg ));
    }

    public function executeUploadBill(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');

        $projectInformation = null;
        $projectBreakdown   = null;
        $errorMsg           = null;
        $pathToFile         = null;
        $fileToUnzip        = array();

        foreach ( $request->getFiles() as $file )
        {
            if ( is_readable($file['tmp_name']) )
            {
                $fileToUnzip['name'] = $newName = Utilities::massageText(date('dmY_H_i_s'));
                $fileToUnzip['ext']  = $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile          = $tempUploadPath . $newName . '.' . $ext;

                $fileToUnzip['path'] = $tempUploadPath;
                move_uploaded_file($file['tmp_name'], $pathToFile);
            }
            else
            {
                $success = false;
            }
        }

        $fileInfo     = array();
        $elementArray = array();
        $billInfo     = array();

        try
        {
            if ( count($fileToUnzip) )
            {
                $allowed = array( 'ebq', 'EBQ' );

                if ( !in_array($fileToUnzip['ext'], $allowed) )
                {
                    throw new Exception('Invalid file type');
                }

                $sfZipGenerator = new sfZipGenerator($fileToUnzip['name'], $fileToUnzip['path'], $fileToUnzip['ext'], true, true);

                $extractedFiles = $sfZipGenerator->unzip();

                $extractDir = $sfZipGenerator->extractDir;

                $count = 0;

                if ( count($extractedFiles) )
                {
                    foreach ( $extractedFiles as $file )
                    {
                        if ( $count == 0 )
                        {
                            $xmlParser = new sfBuildspaceXMLParser($file['filename'], $extractDir);

                            $xmlParser->read();

                            if ( $xmlParser->xml->attributes()->exportType == ExportedFile::EXPORT_TYPE_BACKUP_BILL )
                            {
                                $xmlData = $xmlParser->getProcessedData();

                                $elementObj = ( $xmlData->{sfBuildspaceBackupBillXML::TAG_BILL_ELEMENT}->count() > 0 ) ? $xmlData->{sfBuildspaceBackupBillXML::TAG_BILL_ELEMENT}->children() : false;

                                $billInfo['title'] = $xmlParser->xml->attributes()->title;

                                $elementCounter = 1;

                                foreach ( $elementObj as $element )
                                {
                                    array_push($elementArray, array(
                                        'id'          => (int) $elementCounter,
                                        'description' => (string) $element->description
                                    ));

                                    $elementCounter ++;
                                }
                            }
                        }

                        $count ++;
                    }

                    //Generate Temp File Info
                    $xmlGen = new sfBuildspaceFileInfoXML('temp_file_info', $extractDir);

                    $xmlGen->process($extractDir, $extractedFiles, true);

                    $fileInfo['uploadPath'] = $extractDir;
                    $fileInfo['filename']   = $xmlGen->filename;
                    $fileInfo['extension']  = $xmlGen->extension;
                }

                $success = true;
            }
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'errorMsg'     => $errorMsg,
            'success'      => $success,
            'billInfo'     => $billInfo,
            'tempFileInfo' => $fileInfo,
            'elements'     => array(
                'identifier' => 'id',
                'items'      => $elementArray
            )
        ));
    }

    public function executeImportBill(sfWebRequest $request)
    {
        $this->forward404Unless(
            $parent = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('parent_id')) and
            $request->hasParameter('filename') and
            $request->hasParameter('extension') and
            $request->hasParameter('uploadPath')
        );

        $filename   = $request->getParameter('filename');
        $extension  = $request->getParameter('extension');
        $uploadPath = $request->getParameter('uploadPath');

        $errorMsg = null;

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        try
        {
            $con->beginTransaction();

            $sfImport = new sfBuildspaceXMLParser($filename, $uploadPath, $extension);

            $sfImport->read();

            $fileInfo = $sfImport->getProcessedData();

            unset( $sfImport );

            $fileInfo->attributes()->extractDir;

            $userId = $this->getUser()->getGuardUser()->id;

            $count = 0;

            foreach ( $fileInfo->{sfBuildspaceFileInfoXML::TAG_FILES}->children() as $file )
            {
                $file = $file->children();

                if ( $count == 0 )
                {
                    $importBackup = new sfBuildspaceImportBackupBillXML($userId, $parent, $file->filename, $file->dirname, $file->extension, false, $con);

                    $importBackup->process();
                }

                $count ++;
            }

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'errorMsg' => $errorMsg,
            'success'  => $success
        ));
    }

}