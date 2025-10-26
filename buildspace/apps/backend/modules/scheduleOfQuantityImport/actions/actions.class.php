<?php

/**
 * scheduleOfQuantityImport actions.
 *
 * @package    buildspace
 * @subpackage scheduleOfQuantityImport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class scheduleOfQuantityImportActions extends BaseActions {

	public function executeGetImportFilePermission(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$scheduleOfQuantity = Doctrine_Core::getTable('ScheduleOfQuantity')->find($request->getParameter('scheduleOfQuantityId'))
		);

		$form = new BaseForm();

		return $this->renderJson(array( '_csrf_token' => $form->getCSRFToken() ));
	}

    public function executeImportXMLFileFromCubit(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') AND
            $scheduleOfQuantity = Doctrine_Core::getTable('ScheduleOfQuantity')->find($request->getParameter('scheduleOfQuantityId'))
        );

        session_write_close();

        sfConfig::set('sf_web_debug', false);

        $conn           = $scheduleOfQuantity->getTable()->getConnection();
        $success        = true;
        $errorMsg       = null;
        $fileName       = 'TakeoffJob.xml';
        $folderName     = md5(time());
        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR;

        try
        {
            $conn->beginTransaction();

            mkdir($tempUploadPath);

            foreach ( $request->getFiles() as $file )
            {
                if ( !is_readable($file['fileUpload']['tmp_name']) )
                {
                    throw new InvalidArgumentException('Uploaded File is not readable.');
                }

                // Later to do some checking Here FileType ETC.
                $newName    = date('dmY_H_i_s');
                $ext        = pathinfo($file['fileUpload']['name'], PATHINFO_EXTENSION);
                $pathToFile = $tempUploadPath . $newName . '.' . $ext;

                if (!in_array(strtolower($ext), ScheduleOfQuantity::CUBIT_EXTENSIONS) )
                {
                    throw new Exception('Invalid Cubit File!');
                }

                //Move Uploaded Files to temp folder
                move_uploaded_file($file['fileUpload']['tmp_name'], $pathToFile);

                $zip = new ZipArchive;
                $res = $zip->open($pathToFile);

                if ( $res != true )
                {
                    throw new Exception('Invalid Cubit File!');
                }

                $zip->extractTo($tempUploadPath);
                $zip->close();

                break;
            }

            if ( !is_readable($tempUploadPath . $fileName) )
            {
                throw new InvalidArgumentException('Invalid Cubit File!');
            }

            $parser = new CubitXMLParser();
            $parser->setFile($tempUploadPath . $fileName);
            $parser->parseFileIntoArray();

            $importer = new ScheduleOfQuantityXMLImporter($conn, $scheduleOfQuantity, new ScheduleOfQuantityUnitGetter($conn), $parser, ScheduleOfQuantity::IDENTIFIER_TYPE_CUBIT_TEXT);
            $importer->importDataIntoDb();

            $conn->commit();
        }
        catch (Exception $e)
        {
            $conn->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        Utilities::delTree($tempUploadPath);

        $returnData['success']  = $success;
        $returnData['errorMsg'] = $errorMsg;

        return $this->renderJson($returnData);
    }

}