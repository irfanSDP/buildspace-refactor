<?php

/**
 * requestForQuotationExportFile actions.
 *
 * @package    buildspace
 * @subpackage requestForQuotationExportFile
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class requestForQuotationExportFileActions extends BaseActions {

    public function executeGetExcelFileNameFormToken(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) AND
            $supplier = Doctrine_Core::getTable('RFQSupplier')->find($request->getParameter('rfqSupplierId'))
        );

        $form = new BaseForm();

        return $this->renderJson(array( '_csrf_token' => $form->getCSRFToken() ));
    }

    public function executeExportExcel(sfWebRequest $request)
    {
        // export resource head, item, item ID, but only attach RFQ ITEM ID to item
        // might implement the export button infront of supplier's list

        // import will be reside inside the supplier's RFQ Item list grid
        // or will be based on selection of supplier

        // importing will preview the rates submitted by the supplier
        // after user acknowledge the rates, then only start the import process

        // when importing, existing rates for RFQ item will be erased and replace
        // with a new one

        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getPostParameter('rfqId')) AND
            $supplier = Doctrine_Core::getTable('RFQSupplier')->find($request->getPostParameter('rfqSupplierId'))
        );

        session_write_close();

        // get associated RFQ's Item listing
        $rfqItems = RFQItemTable::getItemListingByRFQId($rfq->id);

        foreach ( $rfqItems as $rfqItem )
        {
            $rfqItemsId[$rfqItem['resource_item_id']] = $rfqItem['resource_item_id'];

            $rfqFromDbItems[$rfqItem['resource_item_id']] = array(
                'rfqItemId'       => $rfqItem['id'],
                'quantity'        => number_format((float) $rfqItem['quantity'], 2, '.', ''),
                'rfqItemRemarkId' => $rfqItem['remark_id'],
                'remarks'         => $rfqItem['remark'],
            );
        }

        if ( !isset ( $rfqItemsId ) OR count($rfqItemsId) == 0 )
        {
            return $this->renderJson(array( 'errorMsg' => 'Sorry, currently there are no item(s) to be exported.', 'success' => false, 'fileUrl' => null ));
        }

        $rfqTreeItems = RFQItemTable::getHierarchyItemListingFromResourceLibraryByRFQItemIds($rfqItemsId, $rfqFromDbItems);

        unset( $rfqFromDbItems );

        $fileName       = $request->getPostParameter('fileName');
        $currentUser    = $this->getUser()->getProfile();
        $currentCompany = Doctrine_Core::getTable('myCompanyProfile')->find(1);
        $currentCompany = ( $currentCompany ) ? $currentCompany : new myCompanyProfile();

        $sfRFQExporter = new sfRequestForQuotationExportExcel($rfq, $supplier, $currentCompany, $currentUser, null, $fileName);
        $sfRFQExporter->process($rfqTreeItems);

        $errorMsg = null;

        try
        {
            sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url' ));

            $uploadPath = 'uploads' . DIRECTORY_SEPARATOR;

            // Write Excel File
            $fileInfo = $sfRFQExporter->fileInfo;
            $success  = true;

            $fileUrl = public_path($uploadPath . $fileInfo['filename'] . $fileInfo['extension'], true);
        }
        catch (Exception $e)
        {
            $fileUrl  = null;
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'errorMsg' => $errorMsg, 'success' => $success, 'fileUrl' => $fileUrl ));
    }

    public function executeGetImportExcelToken(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) AND
            $supplier = Doctrine_Core::getTable('RFQSupplier')->find($request->getParameter('rfqSupplierId'))
        );

        $form = new BaseForm();

        return $this->renderJson(array( '_csrf_token' => $form->getCSRFToken() ));
    }

    public function executeImportRFQSupplierRates(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) AND
            $supplier = Doctrine_Core::getTable('RFQSupplier')->find($request->getParameter('rfqSupplierId'))
        );

        session_write_close();

        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

        foreach ( $request->getFiles() as $file )
        {
            if ( is_readable($file['fileUpload']['tmp_name']) )
            {
                // Later to do some checking Here FileType ETC.
                $newName    = date('dmY_H_i_s');
                $ext        = pathinfo($file['fileUpload']['name'], PATHINFO_EXTENSION);
                $pathToFile = $tempUploadPath . $newName . '.' . $ext;

                //Move Uploaded Files to temp folder
                move_uploaded_file($file['fileUpload']['tmp_name'], $pathToFile);
            }

            break;
        }

        $sfImport = new sfRFQImportExcel($rfq, $supplier, $newName, $ext, $tempUploadPath);

        try
        {
            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $currentUser = $this->getUser()->getGuardUser()->toArray();

            RFQItemRateTable::deleteExistingRateIfAvailableByRFQSupplier($supplier);

            RFQItemRateTable::insertImportedSupplierRatesFromExcel($currentUser, $data);

            $errorMsg = array();
            $success  = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = ( $sfImport->error ) ? false : $success;
        $returnData['errorMsg'] = ( $sfImport->error ) ? $sfImport->errorMsg : $errorMsg;

        return $this->renderJson($returnData);
    }

}