<?php

/**
 * supplyOfMaterialImportFile actions.
 *
 * @package    buildspace
 * @subpackage supplyOfMaterialImportFile
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class supplyOfMaterialImportFileActions extends BaseActions
{

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

        foreach ($request->getFiles() as $file)
        {
            if (is_readable($file['tmp_name']))
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
            $sfImport = new sfBuildspaceExcelParser($newName, $ext, $tempUploadPath, true, false);

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

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = ( $sfImport->error ) ? false : $success;
        $returnData['errorMsg'] = ( $sfImport->error ) ? $sfImport->errorMsg : $errorMsg;
        $returnData['columns']  = $columns;

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
            $sfImport = new sfImportExcelNormal($filename, $extension, $tempUploadPath, true);

            //Set Col Item Value Based on Selection
            $sfImport->colDescriptionFrom = $request->getParameter('colDescriptionFrom');
            $sfImport->colDescriptionTo   = $request->getParameter('colDescriptionTo');
            $sfImport->colUnit            = $request->getParameter('colUnit');
            $sfImport->colRate            = $request->getParameter('colRate');
            $sfImport->colQty             = null;

            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $returnData = array(
                'filename'   => $sfImport->sfExportXML->filename,
                'uploadPath' => $sfImport->sfExportXML->uploadPath,
                'extension'  => $sfImport->sfExportXML->extension,
                'excelType'  => $sfImport->excelType,
                'elements'   => array()
            );

            foreach ($data as $element)
            {
                if (empty( $element['_child'] ))
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

                foreach ($items as $item)
                {
                    array_push($returnData['items'][$element['id']], $item);
                }
            }

            $success = true;
        } catch (Exception $e)
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
        $elementIds = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
        $withRate   = ( $request->getParameter('with_rate') == 'true' ) ? true : false;

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

        //Get First Bill Item Type
        $priority = SupplyOfMaterialElementTable::getMaxPriorityByBillId($bill->id) + 1;

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        try
        {
            $con->beginTransaction();

            $stmt = new sfImportExcelStatementGenerator();

            $stmt->createInsert(
                SupplyOfMaterialElementTable::getInstance()->getTableName(),
                array(
                    'description',
                    'project_structure_id',
                    'priority',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by'
                )
            );

            foreach ($billElements->children() as $importedElement)
            {
                $elementId = (int) $importedElement->id;

                $description = html_entity_decode((string) $importedElement->description);

                if (in_array($elementId, $elementIds))
                {
                    $stmt->addRecord(
                        array( $description, $bill->id, $priority, 'NOW()', 'NOW()', $userId, $userId ),
                        $elementId
                    );

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
            $currentPriority           = - 1;
            $currentElementId          = null;

            // will get existing unit first
            $unitGenerator = new ScheduleOfQuantityUnitGetter($con);

            $availableUnits = $unitGenerator->getAvailableUnitOfMeasurements();

            //Process Root Items
            foreach ($billItems->children() as $importedItem)
            {
                $asRoot = null;

                if (in_array($importedItem->elementId, $elementIds))
                {
                    $elementId   = $importedElementToElementIds[(int) $importedItem->elementId];
                    $description = html_entity_decode((string) $importedItem->description);

                    if ( !isset( $importedItem->new_symbol ) or strlen($importedItem->new_symbol) > 10)//any char more than 10 chars will be considered as non uom symbol
                    {
                        $uomId = ( (int) $importedItem->uom_id > 0 ) ? (int) $importedItem->uom_id : null;
                    }
                    else
                    {
                        if (!isset( $availableUnits[strtolower($importedItem->new_symbol)] ))
                        {
                            // we will insert the new uom symbol
                            $availableUnits = $unitGenerator->insertNewUnitOfMeasurementWithoutDimension($availableUnits,
                                $importedItem->new_symbol);
                        }

                        $uomId = $availableUnits[strtolower($importedItem->new_symbol)];
                    }

                    $type           = (int) $importedItem->type;
                    $level          = (int) $importedItem->level;
                    $originalItemId = (int) $importedItem->id;
                    $rate           = ( $importedItem->{'rate-value'} && $importedItem->{'rate-value'} != '' ) ? number_format((float) $importedItem->{'rate-value'},
                        2, '.', '') : null;

                    if ((int) $importedItem->level == 0)
                    {
                        if ($elementId != $currentElementId)
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

                    if ($asRoot)
                    {
                        $stmt->createInsert(
                            SupplyOfMaterialItemTable::getInstance()->getTableName(),
                            array(
                                'element_id',
                                'description',
                                'type',
                                'uom_id',
                                'supply_rate',
                                'level',
                                'root_id',
                                'lft',
                                'rgt',
                                'priority',
                                'created_at',
                                'updated_at',
                                'created_by',
                                'updated_by',
                            )
                        );

                        $stmt->addRecord(array(
                            $elementId,
                            trim($description),
                            $type,
                            $uomId,
                            ( $withRate ) ? $rate : 0,
                            $level,
                            $rootId,
                            1,
                            2,
                            $priority,
                            'NOW()',
                            'NOW()',
                            $userId,
                            $userId,
                        ));

                        $stmt->save();

                        $returningId = $stmt->returningIds[0];

                        $stmt->setAsRoot(false, $returningId);

                        $importedItemToItemIds[(string) $importedItem->id] = $itemId = $returningId;
                    }
                    else
                    {
                        $originalItemsToSave[$originalItemId] = array(
                            $elementId,
                            trim($description),
                            $type,
                            $uomId,
                            ( $withRate ) ? $rate : 0,
                            $level,
                            $originalRootId,
                            1,
                            2,
                            $priority,
                            'NOW()',
                            'NOW()',
                            $userId,
                            $userId,
                        );
                    }
                }

                unset( $importedItem );
            }

            unset( $billItems );

            $stmt->createInsert(
                SupplyOfMaterialItemTable::getInstance()->getTableName(),
                array(
                    'element_id',
                    'description',
                    'type',
                    'uom_id',
                    'supply_rate',
                    'level',
                    'root_id',
                    'lft',
                    'rgt',
                    'priority',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by',
                )
            );

            $originalRootIdToItemIds = array();

            function checkRootId(&$originalItemIdsToRootId, $itemRootId)
            {
                if (array_key_exists($itemRootId, $originalItemIdsToRootId))
                {
                    $originalRootId = $originalItemIdsToRootId[$itemRootId];

                    return $originalRootId = checkRootId($originalItemIdsToRootId, $originalRootId);
                }

                return $itemRootId;
            }

            if (count($originalItemsToSave))
            {
                $rootIdKey   = 6;
                $priorityKey = 9;

                foreach ($originalItemsToSave as $originalItemId => $item)
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

            foreach ($originalRootIdToItemIds as $rootId => $itemIds)
            {
                $newRootId = $importedItemToItemIds[$rootId];

                foreach ($itemIds as $key => $itemId)
                {
                    $rootIdToItemIds[$newRootId][$key] = $importedItemToItemIds[$itemId];
                }

                unset( $itemIds );
            }

            unset( $originalRootIdToItemIds );

            /* Experimental */
            //Rebuilding Back After Tree Insert
            $stmt->rebuildItemTreeStructureByElementIds('SupplyOfMaterialItem',
                SupplyOfMaterialItemTable::getInstance()->getTableName(),
                $importedElementToElementIds, $rootIdToItemIds);

            $con->commit();

            $success = true;
        } catch (Exception $e)
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

    public function executeImportBuildSpaceExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $errorMsg       = null;

        $success    = true;
        $newName    = null;
        $ext        = null;
        $pathToFile = null;

        foreach ($request->getFiles() as $file)
        {
            if (is_readable($file['tmp_name']))
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

        if ($success && $newName && $ext && $pathToFile)
        {
            try
            {
                $allowed = array( 'xls', 'xlsx', 'XLS', 'XLSX' );

                if (!in_array($ext, $allowed))
                {
                    throw new Exception('Invalid file type');
                }

                $sfImport = new sfImportSupplyOfMaterialExcelBuildspace($pathToFile);

                $sfImport->process();

                $data = $sfImport->getPreviewFormatData();

                $returnData = array(
                    'filename'  => $newName,
                    'extension' => $ext,
                    'elements'  => array(),
                );

                foreach ($data['elements'] as $idx => $item)
                {
                    $returnData['elements'][$idx] = $item['info'];
                    $returnData['items'][$idx]    = $item['items'];
                }

                $success = true;
            } catch (Exception $e)
            {
                $errorMsg = $e->getMessage();
                $success  = false;
            }
        }

        $returnData['success']  = $success;
        $returnData['errorMsg'] = $errorMsg;

        return $this->renderJson($returnData);
    }

    public function executeSaveImportedBuildSpaceExcel(sfWebRequest $request)
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
        $elementIds = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
        $withRate   = ( $request->getParameter('with_rate') == 'true' ) ? true : false;
        $asNewBill  = ( $request->getParameter('as_new') == 'true' ) ? true : false;

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        try
        {
            if (!is_readable($pathToFile))
            {
                throw new Exception('Uploaded file ' . $filename . ' is unreadable');
            }

            $con->beginTransaction();

            $sfImport = new sfImportSupplyOfMaterialExcelBuildspace($pathToFile);

            $sfImport->process();

            if ($asNewBill)
            {
                $sfImport->saveAsNewBill($bill, $elementIds, $withRate, $con);
            }
            else
            {
                $sfImport->saveIntoBill($bill, $elementIds, $withRate, $con);
            }

            $con->commit();

            $success = true;

        } catch (Exception $e)
        {
            $con->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

}