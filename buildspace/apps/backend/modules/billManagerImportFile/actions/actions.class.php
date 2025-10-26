<?php

/**
 * billManagerImportFile actions.
 *
 * @package    buildspace
 * @subpackage billManagerImportFile
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class billManagerImportFileActions extends BaseActions {

    public function executePreviewImportedFile(sfWebRequest $request)
    {
        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid'))
        );

        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $success        = null;
        $errorMsg       = null;
        $columns        = array();

        foreach ( $request->getFiles() as $file )
        {
            if ( is_readable($file['tmp_name']) )
            {
                // Later to do some checking Here FileType ETC.
                //generate new Temporary Name
                $newName    = date('dmY_H_i_s');
                $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile = $tempUploadPath . $newName . '.' . $ext;

                //Move Uploaded Files to temp folder
                move_uploaded_file($file['tmp_name'], $pathToFile);

            }
        }

        try
        {
            $sfImport = new sfBuildspaceExcelParser($newName, $ext, $tempUploadPath, true, false);//startRead(${filename}, ${uploadPath}, ${extension}, ${generateXML})

            $data = $sfImport->processPreviewData();

            $colData = $sfImport->colSlugArray;

            $returnData = array(
                'fileName'    => $sfImport->filename,
                'extension'   => $sfImport->extension,
                'excelType'   => $sfImport->excelType,
                'preview'     => true,
                'previewData' => $data,
                'colData'     => $colData
            );

            // get type(s) specified by user for this current targeted bill
            $billColumnSettings = $bill->BillColumnSettings;

            foreach ( $billColumnSettings as $billColumnSetting )
            {
                $columns[$billColumnSetting->id] = array(
                    'name' => $billColumnSetting->name,
                    'qty'  => $billColumnSetting->quantity
                );

                unset( $billColumnSetting );
            }

            unset( $billColumnSettings );

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = ( $sfImport->error ) ? false : $success;
        $returnData['errorMsg'] = ( $sfImport->error ) ? $sfImport->errorMsg : $errorMsg;
        $returnData['columns']  = $columns;

        return $this->renderJson($returnData);
    }

    public function executeDeleteTempFile(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $uploadPath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp';
        $filename   = $request->getParameter('filename');
        $extension  = $request->getParameter('extension');
        $pathToFile = $uploadPath . DIRECTORY_SEPARATOR . $filename . '.' . $extension;
        $errorMsg   = null;

        try
        {
            if ( is_readable($pathToFile) )
            {
                unlink($pathToFile);
            }

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'errorMsg' => $errorMsg, 'success' => $success ));
    }

    public function executeImportBuildspaceExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $errorMsg       = null;

        $success    = true;
        $newName    = null;
        $ext        = null;
        $pathToFile = null;

        foreach ( $request->getFiles() as $file )
        {
            if ( is_readable($file['tmp_name']) )
            {
                // Later to do some checking Here FileType ETC.
                $newName    = date('dmY_H_i_s');
                $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile = $tempUploadPath . $newName . '.' . $ext;

                //Move Uploaded Files to temp folder
                move_uploaded_file($file['tmp_name'], $pathToFile);
            }
            else
            {
                $success = false;
            }
        }

        if ( $success && $newName && $ext && $pathToFile )
        {
            try
            {
                $allowed = array( 'xls', 'xlsx', 'XLS', 'XLSX' );

                if ( !in_array($ext, $allowed) )
                {
                    throw new Exception('Invalid file type');
                }

                $sfImport = new sfImportExcelBuildspace($pathToFile);

                $sfImport->process();

                $data = $sfImport->getPreviewFormatData();

                $returnData = array(
                    'filename'  => $newName,
                    'extension' => $ext,
                    'elements'  => array(),
                    'columns'   => $data['bill_info']['bill_column_settings']
                );

                foreach ( $data['elements'] as $idx => $item )
                {
                    $returnData['elements'][$idx] = $item['info'];
                    $returnData['items'][$idx]    = $item['items'];
                }

                $success = true;
            }
            catch (Exception $e)
            {
                $errorMsg = $e->getMessage();
                $success  = false;
            }
        }

        $returnData['success']  = $success;
        $returnData['errorMsg'] = $errorMsg;

        return $this->renderJson($returnData);
    }

    public function executeImportBuildsoftExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

        $errorMsg = null;

        foreach ( $request->getFiles() as $file )
        {
            if ( is_readable($file['tmp_name']) )
            {
                // Later to do some checking Here FileType ETC.
                //generate new Temporary Name
                $newName    = date('dmY_H_i_s');
                $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile = $tempUploadPath . $newName . '.' . $ext;

                //Move Uploaded Files to temp folder
                move_uploaded_file($file['tmp_name'], $pathToFile);

            }
        }

        try
        {
            $sfImport = new sfImportExcelBuildsoft($newName, $ext, $tempUploadPath, true);//startRead(${filename}, ${uploadPath}, ${extension}, ${generateXML})

            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $returnData = array(
                'filename'   => $sfImport->sfExportXML->filename,
                'uploadPath' => $sfImport->sfExportXML->uploadPath,
                'extension'  => $sfImport->sfExportXML->extension,
                'excelType'  => $sfImport->excelType,
                'elements'   => array()
            );

            foreach ( $data as $element )
            {
                array_push($returnData['elements'], array(
                    'id'          => $element['id'],
                    'description' => $element['description'],
                    'count'       => $sfImport->elementItemCount[$element['id']],
                    'error'       => $sfImport->elementErrorCount[$element['id']]
                ));

                $items                               = $element['_child'];
                $returnData['items'][$element['id']] = array();

                foreach ( $items as $item )
                {
                    array_push($returnData['items'][$element['id']], $item);
                }
            }

            $success = true;
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

    public function executeImportExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            ( $request->getParameter('filename') )
        );

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $errorMsg       = null;
        $filename       = $request->getParameter('filename');
        $extension      = $request->getParameter('extension');
        $columns        = array();

        try
        {
            $sfImport = new sfImportExcelNormal($filename, $extension, $tempUploadPath, true);//startRead(${filename}, ${uploadPath}, ${extension}, ${generateXML})
            $sfImport->setBillColumnSettings($bill->BillColumnSettings);

            //Set Col Item Value Based on Selection
            $sfImport->colItem            = $request->getParameter('colItem');
            $sfImport->colDescriptionFrom = $request->getParameter('colDescriptionFrom');
            $sfImport->colDescriptionTo   = $request->getParameter('colDescriptionTo');
            $sfImport->colUnit            = $request->getParameter('colUnit');
            $sfImport->colRate            = $request->getParameter('colRate');
            $sfImport->colQty             = $request->getParameter('colQty');
            $sfImport->colAmount          = $request->getParameter('colAmount');

            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $returnData = array(
                'filename'   => $sfImport->sfExportXML->filename,
                'uploadPath' => $sfImport->sfExportXML->uploadPath,
                'extension'  => $sfImport->sfExportXML->extension,
                'excelType'  => $sfImport->excelType,
                'elements'   => array()
            );

            foreach ( $data as $element )
            {
                if ( empty( $element['_child'] ) )
                {
                    continue;
                }

                array_push($returnData['elements'], array(
                    'id'          => $element['id'],
                    'description' => $element['description'],
                    'count'       => $sfImport->elementItemCount[$element['id']],
                    'error'       => $sfImport->elementErrorCount[$element['id']]
                ));

                $items                               = $element['_child'];
                $returnData['items'][$element['id']] = array();

                foreach ( $items as $item )
                {
                    array_push($returnData['items'][$element['id']], $item);
                }
            }

            // get type(s) specified by user for this current targeted bill
            $billColumnSettings = $bill->BillColumnSettings;

            foreach ( $billColumnSettings as $billColumnSetting )
            {
                $columns[$billColumnSetting->id] = array(
                    'name' => $billColumnSetting->name,
                    'qty'  => $billColumnSetting->quantity
                );

                unset( $billColumnSetting );
            }

            unset( $billColumnSettings );

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = ( $sfImport->error ) ? false : $success;
        $returnData['errorMsg'] = ( $sfImport->error ) ? $sfImport->errorMsg : $errorMsg;
        $returnData['columns']  = $columns;

        return $this->renderJson($returnData);
    }

    public function executeSaveImportedExcel(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        sfConfig::set('sf_web_debug', false);

        set_time_limit(0);

        $errorMsg = null;

        //explode Element to imports
        $elementIds         = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
        $billColumnSettings = $bill->BillColumnSettings;
        $withRate           = ( $request->getParameter('with_rate') == 'true' ) ? true : false;
        $withQuantity       = ( $request->getParameter('with_quantity') == 'true' ) ? true : false;
        $withBillRef        = ( $request->getParameter('with_billRef') == 'true' ) ? true : false;

        //get XML Temporary File Information
        $filename   = $request->getParameter('filename');
        $uploadPath = $request->getParameter('uploadPath');

        //Initiate xmlParser
        $xmlParser = new sfBuildspaceXMLParser($filename, $uploadPath, null, true);

        //read xmlParser
        $xmlParser->read();

        //Get XML Processed Data
        $loadedXML    = $xmlParser->getProcessedData();
        $billElements = $loadedXML->ELEMENTS;
        $billItems    = $loadedXML->ITEMS;

        //get Last Priority for current Element
        //Get Current User Information
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');

        //Get Current Bill Revision Id
        $projectRevision   = ProjectRevisionTable::getLatestProjectRevisionFromBillId($bill->root_id);
        $projectRevisionId = $projectRevision['id'];

        //Get First Bill Item Type
        $columnId            = $billColumnSettings[0]['id'];
        $useOriginalQuantity = $billColumnSettings[0]['use_original_quantity'];
        $noOfUnit            = $billColumnSettings[0]['quantity'];
        $priority            = BillElement::getMaxPriorityByBillId($bill->id) + 1;

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        try
        {
            $con->beginTransaction();

            $stmt = new sfImportExcelStatementGenerator();

            $stmt->createInsert(
                BillElementTable::getInstance()->getTableName(),
                array( 'description', 'project_structure_id', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
            );

            foreach ( $billElements->children() as $importedElement )
            {
                $elementId = (int) $importedElement->id;

                $description = html_entity_decode((string) $importedElement->description);

                if ( in_array($elementId, $elementIds) )
                {

                    $stmt->addRecord(array( $description, $bill->id, $priority, 'NOW()', 'NOW()', $userId, $userId ), $elementId);

                    $priority ++;
                }

                unset( $importedElement );
            }

            unset( $billElements );

            $stmt->save();

            $importedElementToElementIds = $stmt->returningIds;

            $importedItemToItemIds     = array();
            $rootOriginalIdsToPriority = array();
            $originalItemsToSave       = array();
            $originalItemIdsToRootId   = array();
            $ratesToSave               = array();
            $qtyToSave                 = array();
            $currentPriority           = - 1;
            $currentElementId          = null;

            // will get existing unit first
            $unitGenerator = new ScheduleOfQuantityUnitGetter($con);

            $availableUnits = $unitGenerator->getAvailableUnitOfMeasurements();

            //Process Root Items
            foreach ( $billItems->children() as $importedItem )
            {
                $previousItem = null;
                $asRoot       = null;

                if ( in_array($importedItem->elementId, $elementIds) )
                {
                    $elementId   = $importedElementToElementIds[(int) $importedItem->elementId];
                    $description = html_entity_decode((string) $importedItem->description);

                    if ( !isset( $importedItem->new_symbol ) or strlen($importedItem->new_symbol) > 10)//any char more than 10 chars will be considered as non uom symbol
                    {
                        $uomId = ( (int) $importedItem->uom_id > 0 ) ? (int) $importedItem->uom_id : null;
                    }
                    else
                    {
                        if ( !isset( $availableUnits[strtolower($importedItem->new_symbol)] ) )
                        {
                            // we will insert the new uom symbol
                            $availableUnits = $unitGenerator->insertNewUnitOfMeasurementWithoutDimension($availableUnits, $importedItem->new_symbol);
                        }

                        $uomId = $availableUnits[strtolower($importedItem->new_symbol)];
                    }

                    $grandTotalPerUnit  = 0;
                    $grandTotal         = 0;
                    $grandTotalQuantity = 0;
                    $type               = (int) $importedItem->type;
                    $level              = (int) $importedItem->level;
                    $originalItemId     = (int) $importedItem->id;
                    $billRef            = (string) $importedItem->bill_ref;
                    $rate               = ( $importedItem->{'rate-value'} && $importedItem->{'rate-value'} != '' ) ? number_format((float) $importedItem->{'rate-value'}, 2, '.', '') : null;

                    if ( $withRate && (int) $importedItem->type != BillItem::TYPE_HEADER && (int) $importedItem->type != BillItem::TYPE_HEADER_N )
                    {
                        array_push($ratesToSave, array(
                            $originalItemId, BillItem::FORMULATED_COLUMN_RATE, $rate, $rate, 'NOW()', 'NOW()', $userId, $userId
                        ));
                    }

                    if ( (int) $importedItem->level == 0 )
                    {
                        if ( $elementId != $currentElementId )
                        {
                            $currentPriority  = 0;
                            $currentElementId = $elementId;
                        }
                        else
                        {
                            $currentPriority ++;
                        }

                        //Set As Root and set root Id to null
                        $asRoot                                     = true;
                        $rootId                                     = null;
                        $rootOriginalIdsToPriority[$originalItemId] = $priority = $currentPriority;
                    }
                    else
                    {
                        $rootId                                   = null;
                        $originalRootId                           = (int) $importedItem->root_id;
                        $originalItemIdsToRootId[$originalItemId] = $originalRootId;
                    }

                    if ( $withQuantity && (int) $importedItem->type != BillItem::TYPE_HEADER && (int) $importedItem->type != BillItem::TYPE_HEADER_N )
                    {
                        if ( !isset( $importedItem->has_multiple_type_qty ) )
                        {
                            $fieldName = BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT;

                            if ( !$useOriginalQuantity )
                            {
                                $fieldName = BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;
                            }

                            $quantity           = ( $importedItem->{'quantity_per_unit-value'} && $importedItem->{'quantity_per_unit-value'} != '' ) ? (float) $importedItem->{'quantity_per_unit-value'} : null;
                            $grandTotalPerUnit  = $rate * $quantity;
                            $grandTotal         = ( $withRate ) ? number_format($grandTotalPerUnit, 2, '.', '') * $noOfUnit : 0;
                            $grandTotalQuantity = $quantity * $noOfUnit;

                            $typeTotal = ( $withRate ) ? $grandTotal : 0;

                            array_push($qtyToSave, array(
                                'quantity'   => $quantity,
                                'field_name' => $fieldName,
                                'type_ref'   => array( $originalItemId, $columnId, $grandTotalQuantity, $typeTotal, $typeTotal, 'NOW()', 'NOW()', $userId, $userId )
                            ));
                        }
                        else
                        {
                            foreach ( $billColumnSettings as $billColumnSetting )
                            {
                                $fieldName = BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT;

                                if ( !$billColumnSetting->use_original_quantity )
                                {
                                    $fieldName = BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;
                                }

                                $noOfUnit              = $billColumnSetting->quantity;
                                $quantity              = isset( $importedItem->{"quantity_per_unit-value-{$billColumnSetting->id}"} ) ? (float) $importedItem->{"quantity_per_unit-value-{$billColumnSetting->id}"} : null;
                                $itemGrandTotalPerUnit = $rate * $quantity;
                                $itemTotalQuantity     = $quantity * $noOfUnit;
                                $itemGrandTotal        = ( $withRate ) ? $itemGrandTotalPerUnit * $noOfUnit : 0;

                                array_push($qtyToSave, array(
                                    'quantity'   => $quantity,
                                    'field_name' => $fieldName,
                                    'type_ref'   => array( $originalItemId, $billColumnSetting->id, $itemTotalQuantity, $itemGrandTotal, $itemGrandTotal, 'NOW()', 'NOW()', $userId, $userId )
                                ));

                                $grandTotalPerUnit += $itemGrandTotalPerUnit;
                                $grandTotal += $itemGrandTotal;
                                $grandTotalQuantity += $itemTotalQuantity;

                                unset( $itemTotalQuantity );
                            }
                        }
                    }

                    if ( $asRoot )
                    {
                        if ( $withBillRef )
                        {
                            $stmt->createInsert(
                                BillItemTable::getInstance()->getTableName(),
                                array( 'element_id', 'bill_ref_char', 'description', 'type', 'uom_id', 'grand_total_quantity', 'grand_total', 'grand_total_after_markup', 'level', 'root_id', 'lft', 'rgt', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by', 'project_revision_id' )
                            );

                            $stmt->addRecord(array( $elementId, $billRef, trim($description), $type, $uomId, $grandTotalQuantity, $grandTotal, $grandTotal, $level, $rootId, 1, 2, $priority, 'NOW()', 'NOW()', $userId, $userId, $projectRevisionId ));
                        }
                        else
                        {
                            $stmt->createInsert(
                                BillItemTable::getInstance()->getTableName(),
                                array( 'element_id', 'description', 'type', 'uom_id', 'grand_total_quantity', 'grand_total', 'grand_total_after_markup', 'level', 'root_id', 'lft', 'rgt', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by', 'project_revision_id' )
                            );

                            $stmt->addRecord(array( $elementId, trim($description), $type, $uomId, $grandTotalQuantity, $grandTotal, $grandTotal, $level, $rootId, 1, 2, $priority, 'NOW()', 'NOW()', $userId, $userId, $projectRevisionId ));
                        }

                        $stmt->save();

                        $returningId = $stmt->returningIds[0];

                        $stmt->setAsRoot(false, $returningId);

                        $importedItemToItemIds[(string) $importedItem->id] = $itemId = $returningId;
                    }
                    else
                    {
                        if ( $withBillRef )
                        {
                            $originalItemsToSave[$originalItemId] = array( $elementId, $billRef, trim($description), $type, $uomId, $grandTotalQuantity, $grandTotal, $grandTotal, $level, $originalRootId, 1, 2, $priority, 'NOW()', 'NOW()', $userId, $userId, $projectRevisionId );
                        }
                        else
                        {
                            $originalItemsToSave[$originalItemId] = array( $elementId, trim($description), $type, $uomId, $grandTotalQuantity, $grandTotal, $grandTotal, $level, $originalRootId, 1, 2, $priority, 'NOW()', 'NOW()', $userId, $userId, $projectRevisionId );
                        }
                    }
                }

                unset( $importedItem );
            }

            unset( $billItems );

            if ( $withBillRef )
            {
                $stmt->createInsert(
                    BillItemTable::getInstance()->getTableName(),
                    array( 'element_id', 'bill_ref_char', 'description', 'type', 'uom_id', 'grand_total_quantity', 'grand_total', 'grand_total_after_markup', 'level', 'root_id', 'lft', 'rgt', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by', 'project_revision_id' )
                );
            }
            else
            {
                $stmt->createInsert(
                    BillItemTable::getInstance()->getTableName(),
                    array( 'element_id', 'description', 'type', 'uom_id', 'grand_total_quantity', 'grand_total', 'grand_total_after_markup', 'level', 'root_id', 'lft', 'rgt', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by', 'project_revision_id' )
                );
            }

            $originalRootIdToItemIds = array();

            function checkRootId(&$originalItemIdsToRootId, $itemRootId)
            {
                if ( array_key_exists($itemRootId, $originalItemIdsToRootId) )
                {
                    $originalRootId = $originalItemIdsToRootId[$itemRootId];

                    return $originalRootId = checkRootId($originalItemIdsToRootId, $originalRootId);
                }
                else
                {
                    return $itemRootId;
                }
            }

            if ( count($originalItemsToSave) )
            {
                $rootIdKey   = 8;
                $priorityKey = 11;

                if ( $withBillRef )
                {
                    $rootIdKey ++;
                    $priorityKey ++;
                }

                foreach ( $originalItemsToSave as $originalItemId => $item )
                {
                    $itemRootId = $item[$rootIdKey];

                    $originalRootIdToItemIds[$itemRootId][] = $originalItemId;

                    $originalRootId = checkRootId($originalItemIdsToRootId, $itemRootId);

                    $rootId   = $importedItemToItemIds[$originalRootId];
                    $priority = $rootOriginalIdsToPriority[$originalRootId];

                    $item[$rootIdKey]   = $rootId;
                    $item[$priorityKey] = $priority;

                    $stmt->addRecord($item, $originalItemId);

                    unset( $item );
                }

                $stmt->save();

                $importedItemToItemIds = $importedItemToItemIds + $stmt->returningIds;
            }

            $rootIdToItemIds = array();

            foreach ( $originalRootIdToItemIds as $rootId => $itemIds )
            {
                $newRootId = $importedItemToItemIds[$rootId];

                foreach ( $itemIds as $key => $itemId )
                {
                    $rootIdToItemIds[$newRootId][$key] = $importedItemToItemIds[$itemId];
                }

                unset( $itemIds );
            }

            unset( $originalRootIdToItemIds );

            /* Experimental */
            //Rebuilding Back After Tree Insert
            $stmt->rebuildItemTreeStructureByElementIds('BillItem', BillItemTable::getInstance()->getTableName(), $importedElementToElementIds, $rootIdToItemIds);

            if ( count($ratesToSave) )
            {
                //Save Qty & Rates
                $stmt->createInsert(
                    BillItemFormulatedColumnTable::getInstance()->getTableName(),
                    array( 'relation_id', 'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by' )
                );

                $rateLogData = array();

                foreach ( $ratesToSave as $rate )
                {
                    $rate[0] = $importedItemToItemIds[$rate[0]];

                    $stmt->addRecord($rate);

                    if($withRate && $withQuantity)
                        $rateLogData[(int) $rate[0]] = (double)$rate[3];

                    unset( $rate );
                }

                $stmt->save();

                if($withRate && $withQuantity)
                    BillItemRateLogTable::insertBatchLogByBillId($bill->id, $rateLogData);
            }

            unset( $ratesToSave );

            if ( count($qtyToSave) )
            {
                foreach ( $qtyToSave as $item )
                {
                    $typeRef = $item['type_ref'];

                    $typeRef[0] = $importedItemToItemIds[$typeRef[0]];

                    $qty = $item['quantity'];

                    $fieldName = $item['field_name'];

                    //Create Bill Item Type Reference
                    $stmt->createInsert(
                        BillItemTypeReferenceTable::getInstance()->getTableName(),
                        array( 'bill_item_id', 'bill_column_setting_id', 'total_quantity', 'grand_total', 'grand_total_after_markup', 'created_at', 'updated_at', 'created_by', 'updated_by' )
                    );

                    $stmt->addRecord($typeRef);

                    $stmt->save();

                    $billItemTypeRefId = $stmt->returningIds[0];

                    $stmt->createInsert(
                        BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName(),
                        array( 'relation_id', 'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by' )
                    );

                    $stmt->addRecord(array( $billItemTypeRefId, $fieldName, $qty, $qty, 'NOW()', 'NOW()', $userId, $userId ));

                    $stmt->save();

                    unset( $item );
                }
            }

            unset( $qtyToSave );

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        //end xmlParser
        $xmlParser->endReader();

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeSaveImportedBuildspaceExcel(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        sfConfig::set('sf_web_debug', false);

        set_time_limit(0);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $filename       = $request->getParameter('filename');
        $pathToFile     = $tempUploadPath . $filename;

        $errorMsg = null;

        //explode Element to imports
        $elementIds  = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
        $withRate    = ( $request->getParameter('with_rate') == 'true' ) ? true : false;
        $withQty     = ( $request->getParameter('with_quantity') == 'true' ) ? true : false;
        $withBillRef = ( $request->getParameter('with_billRef') == 'true' ) ? true : false;
        $asNewBill   = ( $request->getParameter('as_new') == 'true' ) ? true : false;

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        try
        {
            if ( is_readable($pathToFile) )
            {
                $sfImport = new sfImportExcelBuildspace($pathToFile);

                $sfImport->process();

                if ( $asNewBill )
                {
                    $sfImport->saveAsNewBill($bill, $elementIds, $withRate, $withQty, $withBillRef, $con);
                }
                else
                {
                    $sfImport->saveIntoBill($bill, $elementIds, $withRate, $withQty, $withBillRef, $con);
                }

                $success = true;
            }
            else
            {
                throw new Exception('Uploaded file ' . $filename . ' is unreadable');
            }
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

}