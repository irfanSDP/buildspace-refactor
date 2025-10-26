<?php

/**
 * supplyOfMaterialExportFile actions.
 *
 * @package    buildspace
 * @subpackage supplyOfMaterialExportFile
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class supplyOfMaterialExportFileActions extends sfActions
{

    public function executeExportExcelByElement(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $bill->type == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL and
            strlen($request->getParameter('filename')) > 0 and
            strlen($request->getParameter('eids')) > 0
        );

        $elementIds = explode(',', $request->getParameter('eids'));
        $withRate   = ( $request->getParameter('wr') == 'true' ) ? true : false;

        //Initiate sfBillExport
        $sfBillExport = new sfSupplyOfMaterialBillExportExcel($bill);

        //process
        $sfBillExport->process($elementIds, null, $withRate);

        $errorMsg = null;

        try
        {
            $tmpFile = $sfBillExport->write('Excel2007');

            $fileSize     = filesize($tmpFile);
            $fileContents = file_get_contents($tmpFile);
            $mimeType     = Utilities::mimeContentType($tmpFile);

            unlink($tmpFile);

            $this->getResponse()->clearHttpHeaders();
            $this->getResponse()->setStatusCode(200);
            $this->getResponse()->setContentType($mimeType);
            $this->getResponse()->setHttpHeader(
                "Content-Disposition",
                "attachment; filename*=UTF-8''" . rawurlencode($request->getParameter('filename')) . ".xlsx"
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
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'errorMsg' => $errorMsg, 'success' => $success ));
    }

}