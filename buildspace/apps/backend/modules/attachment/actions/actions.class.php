<?php

/**
 * attachment actions.
 *
 * @package    buildspace
 * @subpackage attachment
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class attachmentActions extends BaseActions
{
    public function executeGetAttachments(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest()
        );

        $itemId = $request->getParameter('item_id');
        $itemClass = $request->getParameter('item_class'); 

        $data = array();
        $form = new CsrfForm();

        foreach(AttachmentsTable::getAttachments($itemId, $itemClass) as $key => $info)
        {
            $extension = '';

            if( ! empty( $info['extension'] ) ) $extension .= ".{$info['extension']}";

            // Todo: check if file exists.

            array_push($data, array(
                'id'          => $info['id'],
                'name'        => $info['filename'] . $extension,
                'file_path'   => 'attachment/downloadAttachment/id/'.$info['id'],//this is actually the url path instead of actual/absolute file path
                'updated_by'  => $info['name'],
                'updated_at'  => date('d/m/Y g:i a', strtotime($info['updated_at'])),
                'delete'      => 'remove',
                '_csrf_token' => $form->getCSRFToken(),
            ));
        }

        array_push($data, array(
            'id'          => Constants::GRID_LAST_ROW,
            'name'        => '',
            'file_path'   => '',
            'updated_by'  => '',
            'updated_at'  => '',
            'delete'      => '',
            '_csrf_token' => $form->getCSRFToken(),
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeDownloadAttachment(sfWebRequest $request)
    {
        $this->forward404Unless(
            $attachmentRecord = Doctrine_Core::getTable('Attachments')->find($request->getParameter('id'))
        );

        $filePath = sfConfig::get('sf_upload_dir') . substr($attachmentRecord->filepath, strlen(DIRECTORY_SEPARATOR . 'uploads'));

        if (file_exists($filePath))
        {
            $mimeType = mime_content_type($filePath);

            $response = $this->getResponse();
            $response->clearHttpHeaders();
            $response->setContentType($mimeType);
            $response->setHttpHeader('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"');
            $response->setHttpHeader('Content-Description', 'Buildspace Attachment Download');
            $response->setHttpHeader('Content-Transfer-Encoding', 'binary');
            $response->setHttpHeader('Content-Length', filesize($filePath));
            $response->setHttpHeader('Cache-Control', 'public, must-revalidate');
            // if https then always give a Pragma header like this  to overwrite the "pragma: no-cache" header which
            // will hint IE8 from caching the file during download and leads to a download error!!!
            $response->setHttpHeader('Pragma', 'public');
            //$response->setContent(file_get_contents($filePath)); # will produce a memory limit exhausted error
            $response->sendHttpHeaders();

            ob_end_flush();
            return $this->renderText(readfile($filePath));
        }
        
        return sfView::NONE;
    }

    public function executeUploadAttachment(sfWebRequest $request)
    {
        $user = $this->getUser()->getGuardUser();

        $itemId = $request->getParameter('item_id');
        $itemClass = $request->getParameter('item_class'); 

        $success  = null;
        $errorMsg = null;

        try
        {
            foreach($request->getFiles() as $file)
            {
                if( ! is_readable($file['tmp_name']) )
                {
                    $success = false;
                    $errorMsg = 'File unreadable';
                    break;
                }

                $filename = Utilities::sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME));
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

                $absolutePathToFile = AttachmentsTable::createAbsolutePath($itemClass, $itemId, $filename, $extension);

                move_uploaded_file($file['tmp_name'], $absolutePathToFile);

                $relativePathToFile = AttachmentsTable::getUploadPath($absolutePathToFile);

                AttachmentsTable::saveAttachment($itemId, $itemClass, $relativePathToFile, $filename, $extension, $user);

                $success = true;
            }
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeDeleteAttachment(sfWebRequest $request)
    {
        $this->forward404Unless(
            $attachmentRecord = Doctrine_Core::getTable('Attachments')->find($request->getParameter('id'))
        );

        // Todo: Check for permission.

        $form = new CsrfForm();
        $success  = false;
        $errorMsg = null;

        if ( $this->isFormValid($request, $form) )
        {
            try
            {
                $success =true;
                $pathToFile = sfConfig::get('sf_upload_dir') . substr($attachmentRecord->filepath, strlen(DIRECTORY_SEPARATOR . 'uploads'));
                unlink($pathToFile);
                AttachmentsTable::deleteAttachment($attachmentRecord->id);
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();
            }
        }
        else
        {
            $errorMsg  = $form->getErrors();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }
}
