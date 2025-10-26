<?php

/**
 * tenderingExportFile actions.
 *
 * @package    buildspace
 * @subpackage tenderingExportFile
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class tenderingExportFileActions extends BaseActions
{

    public function executeGetFileByProject(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url', 'Tag' ));

        $uploadUrl = 'http' . ( $request->isSecure() ? 's' : '' ) . '://' . $request->getHost() . public_path('uploads') . "/";

        $exportedFiles = DoctrineQuery::create()->select('f.id, f.filename, f.extension, f.file_type, f.export_type, f.updated_at, p.id, p.title')
            ->from('ExportedFile f')
            ->leftJoin('f.ProjectStructure p')
            ->where('f.project_structure_id = ?', $project->id)
            ->addOrderBy('f.id DESC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach ($exportedFiles as $key => $file)
        {
            $exportedFiles[$key]['file_type']    = ExportedFileTable::getFileTypeText($file['file_type']);
            $exportedFiles[$key]['export_type']  = ExportedFileTable::getExportTypeText($file['export_type']);
            $exportedFiles[$key]['filename']     = $file['filename'] . $file['extension'];
            $exportedFiles[$key]['bill_title']   = $file['ProjectStructure']['title'];
            $exportedFiles[$key]['downloadPath'] = '<a target="_blank" href="' . $uploadUrl . $exportedFiles[$key]['filename'] . '"> Download </a>';
            $exportedFiles[$key]['updated_at']   = date('d/m/Y H:i', strtotime($file['updated_at']));

            unset( $exportedFiles[$key]['ProjectStructure'] );
        }

        array_push($exportedFiles, array(
            'id'           => Constants::GRID_LAST_ROW,
            'bill_title'   => '',
            'filename'     => '',
            'extension'    => '',
            'file_type'    => ExportedFile::FILE_TYPE_ZIP_TEXT,
            'export_type'  => '',
            'downloadPath' => '',
            'updated_at'   => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $exportedFiles
        ));
    }

    public function executeDeleteFile(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $exportedFile = Doctrine_Core::getTable('ExportedFile')->find($request->getParameter('id')));

        $items      = array();
        $uploadPath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';
        $filename   = $exportedFile->filename . $exportedFile->extension;
        $fullPath   = $uploadPath . DIRECTORY_SEPARATOR . $filename;

        $errorMsg = null;

        try
        {
            if (is_readable($fullPath))
            {
                unlink($fullPath);
            }

            array_push($items, array( 'id' => $exportedFile->id ));

            $exportedFile->delete();

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'errorMsg' => $errorMsg, 'success' => $success, 'items' => $items ));
    }

    public function executeExportByProject(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            strlen($request->getParameter('filename')) > 0 and
            $project = ProjectStructureTable::getProjectInformationByProjectId($request->getParameter('id'))
        );

        $errorMsg = null;

        try
        {
            $filesToZip = array();

            $count = 0;

            $projectUniqueId = $project['mainInformation']['unique_id'];

            $sfProjectExport = new sfBuildspaceExportProjectXML($count . "_" . $project['structure']['id'],
                $projectUniqueId, ExportedFile::EXPORT_TYPE_TENDER);

            $sfProjectExport->process($project['structure'], $project['mainInformation'], $project['breakdown'], $project['revisions'], $project['tenderAlternatives'], true);

            array_push($filesToZip, $sfProjectExport->getFileInformation());

            $breakdown = $project['breakdown'];
            $projectId = $project['structure']['id'];

            unset( $project );

            foreach ($breakdown as $structure)
            {
                $count ++;

                $sfBillExport = null;
                $billData     = null;

                if ($structure['type'] == ProjectStructure::TYPE_BILL)
                {
                    $billData = $this->getBillInformation($structure['id']);

                    $sfBillExport = new sfBuildspaceExportBillXML($count . '_' . $structure['title'],
                        $sfProjectExport->uploadPath, $structure['id']);
                }
                else if ($structure['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL)
                {
                    $billData = $this->getSupplyOfMaterialBillInformation($structure['id']);

                    $sfBillExport = new sfBuildspaceExportSupplyOfMaterialBillXML($count . '_' . $structure['title'],
                        $sfProjectExport->uploadPath, $structure['id']);
                }
                else if ($structure['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL)
                {
                    $billData = $this->getScheduleOfRateBillInformation($structure['id']);

                    $sfBillExport = new sfBuildspaceExportScheduleOfRateBillXML($count . '_' . $structure['title'],
                        $sfProjectExport->uploadPath, $structure['id']);
                }

                if(is_object($sfBillExport) and is_array($billData))
                {
                    $sfBillExport->process($billData, true);

                    array_push($filesToZip, $sfBillExport->getFileInformation());

                    unset( $sfBillExport, $structure, $billData );
                }
            }

            unset( $sfProjectExport );

            $sfZipGenerator = new sfZipGenerator("Project_" . $projectId, null, null, true, true);

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

            ob_end_clean();

            return $this->renderText($fileContents);
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'errorMsg' => $errorMsg,
            'success'  => false
        ));
    }

    public function executeExportRatesByProject(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            strlen($request->getParameter('filename')) > 0 and
            $project = ProjectStructureTable::getProjectInformationByProjectId($request->getParameter('id'))
        );

        $errorMsg = null;

        try
        {
            $projectObject = Doctrine_Core::getTable('ProjectStructure')->find($project['structure']['id']);

            $projectObject->updateProjectElementAndItemTotalAfterMarkup();

            $filesToZip = array();

            $count = 0;

            $extractedProjectIds = ProjectStructureTable::extractOriginId($project['structure']['tender_origin_id']);

            $projectId = $project['structure']['id'];

            $project['structure']['tender_amount']                               = ProjectStructureTable::getOverallTotalForProject($projectId);
            $project['structure']['tender_amount_except_prime_cost_provisional'] = ProjectStructureTable::getOverallTotalForProjectWithoutPrimeCostAndProvisionalBill($projectId);
            $project['structure']['tender_som_amount']                           = SupplyOfMaterialTable::getTotalAmount($projectId);
            $project['structure']['id']                                          = $extractedProjectIds['origin_id'];

            unset( $project['structure']['tender_origin_id'], $project['mainInformation']['id'] );

            $projectUniqueId = $project['mainInformation']['unique_id'];

            if ($extractedProjectIds['sub_package_id'] > 0)
            {
                $sfProjectExport = new sfBuildspaceExportSubPackageXML($count . "_" . $project['structure']['id'],
                    $projectUniqueId, ExportedFile::EXPORT_TYPE_SUB_PACKAGE_RATES,
                    $extractedProjectIds['sub_package_id']);
            }
            else
            {
                $sfProjectExport = new sfBuildspaceExportProjectXML($count . "_" . $project['structure']['id'], $projectUniqueId, ExportedFile::EXPORT_TYPE_RATES);
            }

            $currentRevision = ProjectRevisionTable::getLatestProjectRevisionFromBillId($project['structure']['root_id'], Doctrine_Core::HYDRATE_ARRAY);

            $sfProjectExport->process($project['structure'], $project['mainInformation'], null, array( $currentRevision ), $project['tenderAlternatives'], true);

            array_push($filesToZip, $sfProjectExport->getFileInformation());

            foreach ($project['breakdown'] as $k => $structure)
            {
                $count ++;

                $sfBillExport = null;
                $billData     = null;

                if ($structure['type'] == ProjectStructure::TYPE_BILL)
                {
                    $billData = $this->getBillRates($structure['id']);

                    $extractedBillIds = ProjectStructureTable::extractOriginId($structure['tender_origin_id']);

                    $sfBillExport = new sfBuildspaceExportBillRatesXML($count . '_' . $structure['title'],
                        $sfProjectExport->uploadPath, $extractedBillIds['origin_id']);

                    $sfBillExport->specifyBillColumnSettingUnits(SubPackageUnitInformationTable::getUnits($structure['id']));
                }
                else if ($structure['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL)
                {
                    $billData = $this->getSupplyOfMaterialBillRates($structure['id']);

                    $extractedBillIds = ProjectStructureTable::extractOriginId($structure['tender_origin_id']);

                    $sfBillExport = new sfBuildspaceExportSupplyOfMaterialBillRatesXML($count . '_' . $structure['title'],
                        $sfProjectExport->uploadPath, $extractedBillIds['origin_id']);
                }
                else if ($structure['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL)
                {
                    $billData = $this->getScheduleOfRateBillRates($structure['id']);

                    $extractedBillIds = ProjectStructureTable::extractOriginId($structure['tender_origin_id']);

                    $sfBillExport = new sfBuildspaceExportScheduleOfRateBillRatesXML($count . '_' . $structure['title'],
                        $sfProjectExport->uploadPath, $extractedBillIds['origin_id']);
                }

                if(is_object($sfBillExport) and is_array($billData))
                {
                    $sfBillExport->process($billData, true);

                    array_push($filesToZip, $sfBillExport->getFileInformation());

                    unset( $sfBillExport, $structure, $billData );
                }
            }

            $sfZipGenerator = new sfZipGenerator("Rates_" . $projectId, null, null, true, true);

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
                "attachment; filename*=UTF-8''" . rawurlencode($request->getParameter('filename')) . '.tr'
            );
            $this->getResponse()->setHttpHeader('Content-Description', 'File Transfer');
            $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
            $this->getResponse()->setHttpHeader('Content-Length', $fileSize);
            $this->getResponse()->setHttpHeader('Cache-Control', 'public, must-revalidate');
            // if https then always give a Pragma header like this  to overwrite the "pragma: no-cache" header which
            // will hint IE8 from caching the file during download and leads to a download error!!!
            $this->getResponse()->setHttpHeader('Pragma', 'public');
            $this->getResponse()->sendHttpHeaders();

            ob_end_clean();

            return $this->renderText($fileContents);
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'errorMsg' => $errorMsg,
            'success'  => false
        ));
    }

    public function executeExportAddendum(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            strlen($request->getParameter('filename')) > 0 and
            $project = ProjectStructureTable::getProjectInformationByProjectId($request->getParameter('id')) and
            $revision = DoctrineQuery::create()
                ->select('r.*')
                ->from('ProjectRevision r')
                ->where('r.id = ?', $request->getParameter('rid'))
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne()
        );

        $errorMsg = null;

        try
        {
            $filesToZip = array();

            $projectUniqueId = $project['mainInformation']['unique_id'];

            if ($revision['version'] == 0)
            {
                $count = 0;

                $sfProjectExport = new sfBuildspaceExportProjectXML($count . "_" . $project['structure']['id'], $projectUniqueId, ExportedFile::EXPORT_TYPE_TENDER);

                $sfProjectExport->process($project['structure'], $project['mainInformation'], $project['breakdown'], array( $revision ), $project['tenderAlternatives'], true);

                array_push($filesToZip, $sfProjectExport->getFileInformation());

                foreach ($project['breakdown'] as $structure)
                {
                    $count ++;

                    if ($structure['type'] == ProjectStructure::TYPE_BILL)
                    {
                        $billData = $this->getBillInformation($structure['id'], $revision['id']);

                        $sfBillExport = new sfBuildspaceExportBillXML($count . '_' . $structure['title'],
                            $sfProjectExport->uploadPath, $structure['id']);

                        $sfBillExport->process($billData, true);

                        array_push($filesToZip, $sfBillExport->getFileInformation());
                    }
                }
            }
            else
            {
                $count = 0;

                $sfProjectExport = new sfBuildspaceExportProjectXML($count . "_" . $project['structure']['id'], $projectUniqueId, ExportedFile::EXPORT_TYPE_ADDENDUM);

                $sfProjectExport->process($project['structure'], $project['mainInformation'], $project['breakdown'], array( $revision ), $project['tenderAlternatives'], true);

                array_push($filesToZip, $sfProjectExport->getFileInformation());

                foreach ($project['breakdown'] as $structure)
                {
                    $count ++;

                    if ($structure['type'] == ProjectStructure::TYPE_BILL)
                    {
                        $billData = $this->getBillRevisionData($structure['id'], $revision['id']);

                        $sfBillExport = new sfBuildspaceExportBillAddendumXML($count . '_' . $structure['title'],
                            $sfProjectExport->uploadPath, $structure['id'], $revision);

                        $sfBillExport->process($billData, true);

                        array_push($filesToZip, $sfBillExport->getFileInformation());
                    }
                }
            }

            $sfZipGenerator = new sfZipGenerator($revision['revision'], null, null, true, true);

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

            ob_end_clean();

            return $this->renderText($fileContents);
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'errorMsg' => $errorMsg,
            'success'  => false
        ));
    }

    public function executeExportAllAddendum(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = ProjectStructureTable::getProjectInformationByProjectId($request->getParameter('id')));

        $errorMsg = null;
        $success  = false;

        foreach ($project['revisions'] as $revision)
        {
            if ($revision['version'] > 0)
            {
                //If Not Original Bill Then Process
                try
                {
                    $filesToZip = array();

                    $count = 0;

                    $projectUniqueId = $project['mainInformation']['unique_id'];

                    $sfProjectExport = new sfBuildspaceExportProjectXML($count . "_" . $project['structure']['id'], $projectUniqueId, ExportedFile::EXPORT_TYPE_ADDENDUM);

                    $sfProjectExport->process($project['structure'], $project['mainInformation'], $project['breakdown'], array( $revision ), $project['tenderAlternatives'], true);

                    array_push($filesToZip, $sfProjectExport->getFileInformation());

                    foreach ($project['breakdown'] as $k => $structure)
                    {
                        $count ++;

                        if ($structure['type'] == ProjectStructure::TYPE_BILL)
                        {
                            $billData = $this->getBillRevisionData($structure['id'], $revision['id']);

                            $sfBillExport = new sfBuildspaceExportBillAddendumXML($count . '_' . $structure['title'],
                                $sfProjectExport->uploadPath, $structure['id'], $revision);

                            $sfBillExport->process($billData, true);

                            array_push($filesToZip, $sfBillExport->getFileInformation());
                        }
                    }

                    $sfZipGenerator = new sfZipGenerator($revision['revision'], null, null, true, true);

                    $sfZipGenerator->createZip($filesToZip);

                    $fileInfo = $sfZipGenerator->getFileInfo();

                    $saveFile = DoctrineQuery::create()->select('f.id, f.filename, f.extension, f.file_type, f.export_type, f.updated_at')
                        ->from('ExportedFile f')
                        ->where('f.filename = ?', $fileInfo['filename'])
                        ->fetchOne();

                    if (!$saveFile)
                    {
                        $saveFile = new ExportedFile();
                    }

                    $saveFile->filename             = $fileInfo['filename'];
                    $saveFile->extension            = $fileInfo['extension'];
                    $saveFile->file_type            = ExportedFile::FILE_TYPE_ZIP;
                    $saveFile->export_type          = ExportedFile::EXPORT_TYPE_ADDENDUM;
                    $saveFile->project_structure_id = $project['structure']['id'];
                    $saveFile->save();

                    $success = true;
                } catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                    $success  = false;
                }
            }
        }

        return $this->renderJson(array(
            'errorMsg' => $errorMsg,
            'success'  => $success
        ));
    }

    protected function getBillRates($billId)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        //Get BillMarkupSetting
        $billMarkupSetting = DoctrineQuery::create()
            ->select('m.bill_markup_enabled, m.bill_markup_percentage, m.bill_markup_amount, m.element_markup_enabled, m.item_markup_enabled, m.rounding_type')
            ->from('BillMarkupSetting m')
            ->where('m.project_structure_id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        //Get Element List
        $stmt = $pdo->prepare("SELECT e.id, e.project_structure_id, e.tender_origin_id, markup.final_value AS markup_percentage FROM " . BillElementTable::getInstance()->getTableName() . " e
        LEFT JOIN " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " markup ON markup.relation_id = e.id AND markup.deleted_at IS NULL AND markup.column_name ='" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute(array(
            'project_structure_id' => $billId
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $billStructure = array(
            'total_amount'     => 0,
            'elementsAndItems' => array()
        );

        if (count($elements))
        {
            //Get Root Items
            $stmt = $pdo->prepare("SELECT i.element_id, i.id, i.tender_origin_id FROM " . BillItemTable::getInstance()->getTableName() . " i
            WHERE i.id = i.root_id AND i.element_id IN (SELECT e.id FROM " . BillElementTable::getInstance()->getTableName() . " e
            WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL ORDER BY e.priority)
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL ORDER BY i.priority");

            $stmt->execute(array(
                'project_structure_id' => $billId
            ));

            $roots = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

            //Excluded Item Type
            $excludedItemType = array( BillItem::TYPE_HEADER, BillItem::TYPE_NOID, BillItem::TYPE_HEADER_N );

            foreach ($elements as $element)
            {
                $elementTotalAmount = 0;

                $markupInfo = array(
                    'bill_markup_enabled'       => $billMarkupSetting['bill_markup_enabled'],
                    'bill_markup_percentage'    => $billMarkupSetting['bill_markup_percentage'],
                    'element_markup_enabled'    => $billMarkupSetting['element_markup_enabled'],
                    'element_markup_percentage' => ( $element['markup_percentage'] != null && $element['markup_percentage'] != '' ) ? $element['markup_percentage'] : 0,
                    'item_markup_enabled'       => $billMarkupSetting['item_markup_enabled'],
                    'rounding_type'             => $billMarkupSetting['rounding_type'],
                );

                $elementArrayOfIds = ProjectStructureTable::extractOriginId($element['tender_origin_id']);

                $result = array(
                    'id'           => $elementArrayOfIds['origin_id'],
                    'total_amount' => 0,
                    'items'        => array()
                );

                if (array_key_exists($element['id'], $roots) && $roots[$element['id']])
                {
                    $rootIds = $roots[$element['id']];

                    $stmt = $pdo->prepare("SELECT c.id, c.type, c.tender_origin_id, c.uom_id, c.description, c.grand_total_after_markup AS grand_total, ifc.final_value AS rate, markup.final_value as markup_percentage FROM " . BillItemTable::getInstance()->getTableName() . " p
                            JOIN " . BillItemTable::getInstance()->getTableName() . " c ON c.lft BETWEEN p.lft AND p.rgt
                            LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " ifc ON ifc.relation_id = c.id AND ifc.deleted_at IS NULL AND ifc.column_name ='" . BillItem::FORMULATED_COLUMN_RATE . "'
                            LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " markup ON markup.relation_id = c.id AND markup.deleted_at IS NULL AND markup.column_name ='" . BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
                            WHERE p.id IN (" . implode(',', $rootIds) . ") AND p.id = c.root_id AND c.project_revision_deleted_at IS NULL
                            AND c.deleted_at IS NULL AND c.type NOT IN (" . implode(',',
                            $excludedItemType) . ") ORDER BY p.priority, c.lft");

                    $stmt->execute();

                    $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($billItems))
                    {
                        foreach ($billItems as $k => $item)
                        {
                            $itemArrayOfIds = ProjectStructureTable::extractOriginId($item['tender_origin_id']);

                            $billItems[$k]['origin_id'] = $billItems[$k]['id'];

                            $billItems[$k]['id'] = $itemArrayOfIds['origin_id'];

                            unset( $billItems[$k]['tender_origin_id'] );

                            $billItems[$k]['rate'] = BillItemTable::calculateRateAfterMarkup($item['rate'],
                                $item['markup_percentage'], $markupInfo);

                            $elementTotalAmount += $item['grand_total'];
                        }
                    }

                    $result['items'] = $billItems;
                }

                unset( $element );

                $result['total_amount'] = $elementTotalAmount;

                $billStructure['total_amount'] += $elementTotalAmount;

                array_push($billStructure['elementsAndItems'], $result);
            }
        }

        return $billStructure;
    }

    protected function getSupplyOfMaterialBillRates($billId)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $billStructure = array(
            'total_amount'     => 0,
            'elementsAndItems' => array()
        );

        //Get Element List
        $stmt = $pdo->prepare("SELECT e.id, e.project_structure_id, e.tender_origin_id
        FROM " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL
        ORDER BY e.priority");

        $stmt->execute(array(
            'project_structure_id' => $billId
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($elements))
        {
            //Get Root Items
            $stmt = $pdo->prepare("SELECT i.element_id, i.id, i.tender_origin_id
            FROM " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " i
            WHERE i.id = i.root_id AND i.element_id
            IN (SELECT e.id FROM " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e
            WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL ORDER BY e.priority)
            AND i.deleted_at IS NULL
            ORDER BY i.priority");

            $stmt->execute(array(
                'project_structure_id' => $billId
            ));

            $roots = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

            //Excluded Item Type
            $excludedItemType = array( SupplyOfMaterialItem::TYPE_HEADER, SupplyOfMaterialItem::TYPE_HEADER_N );

            foreach ($elements as $element)
            {
                $elementTotalAmount = 0;

                $elementArrayOfIds = ProjectStructureTable::extractOriginId($element['tender_origin_id']);

                $result = array(
                    'id'           => $elementArrayOfIds['origin_id'],
                    'total_amount' => 0,
                    'items'        => array()
                );

                if (array_key_exists($element['id'], $roots) && $roots[$element['id']])
                {
                    $rootIds = $roots[$element['id']];

                    $stmt = $pdo->prepare("SELECT c.id, c.type, c.tender_origin_id, c.uom_id, c.description,
                    c.supply_rate, c.contractor_supply_rate, c.estimated_qty, c.percentage_of_wastage, c.difference,
                    c.amount
                    FROM " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " p
                    JOIN " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " c ON c.lft BETWEEN p.lft AND p.rgt
                    WHERE p.id IN (" . implode(',', $rootIds) . ") AND p.id = c.root_id AND c.deleted_at IS NULL
                    AND c.type NOT IN (" . implode(',', $excludedItemType) . ")
                    ORDER BY p.priority, c.lft");

                    $stmt->execute();

                    $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($billItems as $k => $item)
                    {
                        $itemArrayOfIds = ProjectStructureTable::extractOriginId($item['tender_origin_id']);

                        $billItems[$k]['origin_id'] = $billItems[$k]['id'];

                        $billItems[$k]['id'] = $itemArrayOfIds['origin_id'];

                        unset( $billItems[$k]['tender_origin_id'] );

                        $elementTotalAmount += $item['amount'];
                    }

                    $result['items'] = $billItems;
                }

                unset( $element );

                $result['total_amount'] = $elementTotalAmount;

                $billStructure['total_amount'] += $elementTotalAmount;

                array_push($billStructure['elementsAndItems'], $result);
            }
        }

        return $billStructure;
    }

    protected function getScheduleOfRateBillRates($billId)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $billStructure = array(
            'total_amount'     => 0,
            'elementsAndItems' => array()
        );

        //Get Element List
        $stmt = $pdo->prepare("SELECT e.id, e.project_structure_id, e.tender_origin_id
        FROM " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL
        ORDER BY e.priority");

        $stmt->execute(array(
            'project_structure_id' => $billId
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($elements))
        {
            //Get Root Items
            $stmt = $pdo->prepare("SELECT i.element_id, i.id, i.tender_origin_id
            FROM " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " i
            WHERE i.id = i.root_id AND i.element_id
            IN (SELECT e.id FROM " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " e
            WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL ORDER BY e.priority)
            AND i.deleted_at IS NULL
            ORDER BY i.priority");

            $stmt->execute(array(
                'project_structure_id' => $billId
            ));

            $roots = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

            //Excluded Item Type
            $excludedItemType = array( ScheduleOfRateBillItem::TYPE_HEADER, ScheduleOfRateBillItem::TYPE_HEADER_N );

            foreach ($elements as $element)
            {
                $elementArrayOfIds = ProjectStructureTable::extractOriginId($element['tender_origin_id']);

                $result = array(
                    'id'           => $elementArrayOfIds['origin_id'],
                    'items'        => array()
                );

                if (array_key_exists($element['id'], $roots) && $roots[$element['id']])
                {
                    $rootIds = $roots[$element['id']];

                    $stmt = $pdo->prepare("SELECT c.id, c.type, c.tender_origin_id, c.uom_id, c.description, c.contractor_rate
                    FROM " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " p
                    JOIN " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " c ON c.lft BETWEEN p.lft AND p.rgt
                    WHERE p.id IN (" . implode(',', $rootIds) . ") AND p.id = c.root_id AND c.deleted_at IS NULL
                    AND c.type NOT IN (" . implode(',', $excludedItemType) . ")
                    ORDER BY p.priority, c.lft");

                    $stmt->execute();

                    $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($billItems as $k => $item)
                    {
                        $itemArrayOfIds = ProjectStructureTable::extractOriginId($item['tender_origin_id']);

                        $billItems[$k]['origin_id'] = $billItems[$k]['id'];

                        $billItems[$k]['id'] = $itemArrayOfIds['origin_id'];

                        unset( $billItems[$k]['tender_origin_id'] );
                    }

                    $result['items'] = $billItems;
                }

                unset( $element );

                //$billStructure['total_amount'] += $elementTotalAmount;

                array_push($billStructure['elementsAndItems'], $result);
            }
        }

        return $billStructure;
    }

    protected function getBillInformation($billId, $revisionId = false)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $billStructure = array();

        $sqlExt = null;

        //Get Bill and Its information
        $bill = DoctrineQuery::create()
            ->select('p.id, s.*, bcs.*, bt.*, m.*, l.*, lh.*, lp.*')
            ->from('ProjectStructure p')
            ->leftJoin('p.BillSetting s')
            ->leftJoin('p.BillMarkupSetting m')
            ->leftJoin('p.BillColumnSettings bcs')
            ->leftJoin('p.BillType bt')
            ->leftJoin('p.BillLayoutSetting l')
            ->leftJoin('l.BillHeadSettings lh')
            ->leftJoin('l.BillPhrase lp')
            ->where('p.id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        //Get Element List
        $stmt = $pdo->prepare("SELECT e.id, e.project_structure_id, e.description, e.priority FROM " . BillElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute(array(
            'project_structure_id' => $bill['id']
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($revisionId)
        {
            $currentRevision = DoctrineQuery::create()
                ->select('r.id, r.project_structure_id, r.revision, r.version')
                ->from('ProjectRevision r')
                ->where('r.id = ?', $revisionId)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();

            $previousRevision = DoctrineQuery::create()
                ->select('r.id, r.project_structure_id, r.revision, r.version')
                ->from('ProjectRevision r')
                ->where('r.version = ?', $currentRevision['version'] - 1)
                ->andWhere('r.project_structure_id = ?', $currentRevision['project_structure_id'])
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();
        }
        else
        {
            $currentRevision  = false;
            $previousRevision = false;
        }

        if (count($elements))
        {
            foreach ($elements as $element)
            {
                $result = array(
                    'id'                   => $element['id'],
                    'project_structure_id' => $element['project_structure_id'],
                    'description'          => $element['description'],
                    'priority'             => $element['priority'],
                    'items'                => array()
                );

                //Get Bill Pages Information
                $query = DoctrineQuery::create()->select('p.id, p.element_id, p.page_no, p.revision_id, p.new_revision_id, i.id, i.bill_page_id, i.bill_item_id, i.new_item_from_new_revision')
                    ->from('BillPage p')
                    ->leftJoin('p.Items i');

                if ($revisionId)
                {
                    if ($currentRevision && $previousRevision)
                    {
                        $query->where('(p.new_revision_id = ? OR p.revision_id = ?) AND p.element_id = ?',
                            array( $currentRevision['id'], $previousRevision['id'], $element['id'] ));

                        $result['billPages'] = $query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();
                    }
                }
                else
                {
                    $result['billPages'] = $query->whereIn('p.element_id', $element['id'])
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();
                }

                //Get Collection Pages Information
                $query = DoctrineQuery::create()->select('c.id, c.element_id, c.revision_id, c.page_no')
                    ->from('BillCollectionPage c');

                if ($revisionId)
                {
                    if ($currentRevision && $previousRevision)
                    {
                        $query->where('c.revision_id = ? AND c.element_id = ?',
                            array( $previousRevision['id'], $element['id'] ));

                        $result['collectionPages'] = $query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();
                    }
                }
                else
                {
                    $result['collectionPages'] = $query->whereIn('c.element_id', $element['id'])
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();
                }

                $query = DoctrineQuery::create()->select('c.id, c.description, c.type, c.uom_id, c.element_id, c.grand_total_after_markup, c.grand_total_quantity,
                    c.bill_ref_element_no, c.bill_ref_page_no, c.bill_ref_char, c.priority, c.root_id, c.lft, c.rgt, c.level, c.project_revision_id, c.deleted_at_project_revision_id, c.project_revision_deleted_at,
                    uom.id, uom.name, uom.symbol, uom.type, type.id, type.bill_item_id, type.bill_column_setting_id, type.include, type.total_quantity,
                    type_fc.id, type_fc.relation_id, type_fc.column_name, type_fc.final_value, type_fc.created_at, ls.*, pc.supply_rate, pc.bill_item_id')
                    ->from('BillItem c')
                    ->leftJoin('c.BillItemTypeReferences type')
                    ->leftJoin('c.LumpSumPercentage ls')
                    ->leftJoin('c.PrimeCostRate pc')
                    ->leftJoin('type.FormulatedColumns type_fc')
                    ->leftJoin('c.UnitOfMeasurement uom')
                    ->where('c.element_id = ? ', $element['id'])
                    ->andWhere('c.deleted_at IS NULL')
                    ->orderBy('c.priority, c.lft, c.level')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

                if ($revisionId)
                {
                    $query->addWhere('c.project_revision_id = ? OR c.deleted_at_project_revision_id = ?',
                        array( $revisionId, $revisionId ));
                }


                $billItems = $query->execute();

                $result['items'] = $billItems;

                array_push($billStructure, $result);

                unset( $element );
            }
        }

        if ($bill)
        {
            return array(
                'elementsAndItems'   => ( $elements && count($elements) > 0 ) ? $billStructure : null,
                'billSetting'        => $bill['BillSetting'],
                'billMarkupSetting'  => $bill['BillMarkupSetting'],
                'billColumnSettings' => $bill['BillColumnSettings'],
                'billType'           => $bill['BillType'],
                'billLayoutSetting'  => $bill['BillLayoutSetting']
            );
        }
        else
        {
            return false;
        }
    }

    protected function getSupplyOfMaterialBillInformation($billId)
    {
        $pdo           = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $billStructure = array();

        //Get Bill and Its information
        $bill = DoctrineQuery::create()
            ->select('p.id, s.*, l.*, lh.*, lp.*')
            ->from('ProjectStructure p')
            ->leftJoin('p.SupplyOfMaterial s')
            ->leftJoin('p.SupplyOfMaterialLayoutSetting l')
            ->leftJoin('l.SOMBillHeadSettings lh')
            ->leftJoin('l.SOMBillPhrase lp')
            ->where('p.id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        //Get Element List
        $stmt = $pdo->prepare("SELECT e.id, e.project_structure_id, e.description, e.priority
        FROM " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL
        ORDER BY e.priority");

        $stmt->execute(array(
            'project_structure_id' => $bill['id']
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($elements as $element)
        {
            $result = array(
                'id'                   => $element['id'],
                'project_structure_id' => $element['project_structure_id'],
                'description'          => $element['description'],
                'priority'             => $element['priority'],
                'items'                => array()
            );

            $result['items'] = DoctrineQuery::create()
                ->select('c.id, c.description, c.type, c.uom_id, c.element_id, c.priority, c.supply_rate,
                c.root_id, c.lft, c.rgt, c.level, uom.id, uom.name, uom.symbol, uom.type')
                ->from('SupplyOfMaterialItem c')
                ->leftJoin('c.UnitOfMeasurement uom')
                ->where('c.element_id = ? ', $element['id'])
                ->andWhere('c.deleted_at IS NULL')
                ->orderBy('c.priority, c.lft, c.level')
                ->fetchArray();

            $billStructure[] = $result;

            unset( $element );
        }

        if (!$bill)
        {
            return false;
        }

        return array(
            'elementsAndItems'  => ( $elements && count($elements) > 0 ) ? $billStructure : null,
            'billSetting'       => $bill['SupplyOfMaterial'],
            'billLayoutSetting' => $bill['SupplyOfMaterialLayoutSetting']
        );
    }

    protected function getScheduleOfRateBillInformation($billId)
    {
        $pdo           = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $billStructure = array();

        //Get Bill and Its information
        $bill = DoctrineQuery::create()
            ->select('p.id, s.*, l.*, lh.*, lp.*')
            ->from('ProjectStructure p')
            ->leftJoin('p.ScheduleOfRateBill s')
            ->leftJoin('p.ScheduleOfRateBillLayoutSetting l')
            ->leftJoin('l.ScheduleOfRateBillLayoutHeadSettings lh')
            ->leftJoin('l.ScheduleOfRateBillLayoutPhrase lp')
            ->where('p.id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        //Get Element List
        $stmt = $pdo->prepare("SELECT e.id, e.project_structure_id, e.description, e.priority
        FROM " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL
        ORDER BY e.priority");

        $stmt->execute(array(
            'project_structure_id' => $bill['id']
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($elements as $element)
        {
            $result = array(
                'id'                   => $element['id'],
                'project_structure_id' => $element['project_structure_id'],
                'description'          => $element['description'],
                'priority'             => $element['priority'],
                'items'                => array()
            );

            $result['items'] = DoctrineQuery::create()
                ->select('c.id, c.description, c.type, c.uom_id, c.element_id, c.priority,
                c.root_id, c.lft, c.rgt, c.level, uom.id, uom.name, uom.symbol, uom.type')
                ->from('ScheduleOfRateBillItem c')
                ->leftJoin('c.UnitOfMeasurement uom')
                ->where('c.element_id = ? ', $element['id'])
                ->andWhere('c.deleted_at IS NULL')
                ->orderBy('c.priority, c.lft, c.level')
                ->fetchArray();

            $billStructure[] = $result;

            unset( $element );
        }

        if (!$bill)
        {
            return false;
        }

        return array(
            'elementsAndItems'  => ( $elements && count($elements) > 0 ) ? $billStructure : null,
            'billSetting'       => $bill['ScheduleOfRateBill'],
            'billLayoutSetting' => $bill['ScheduleOfRateBillLayoutSetting']
        );
    }

    protected function getBillRevisionData($billId, $revisionId)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $billStructure = array();

        //Get Bill and Its information
        $bill = DoctrineQuery::create()
            ->select('p.id, bt.*, l.*, lh.*, lp.*')
            ->from('ProjectStructure p')
            ->leftJoin('p.BillLayoutSetting l')
            ->leftJoin('l.BillHeadSettings lh')
            ->leftJoin('l.BillPhrase lp')
            ->where('p.id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        //Get Bill and Its information
        $billColumnSettings = DoctrineQuery::create()
            ->select('bcs.*')
            ->from('BillColumnSetting bcs')
            ->where('bcs.project_structure_id = ?', $billId)
            ->fetchArray();

        $billType = DoctrineQuery::create()
            ->select('bt.*')
            ->from('BillType bt')
            ->where('bt.project_structure_id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        $billMarkupSetting = DoctrineQuery::create()
            ->select('m.bill_markup_enabled, m.bill_markup_percentage, m.bill_markup_amount, m.element_markup_enabled, m.item_markup_enabled, m.rounding_type')
            ->from('BillMarkupSetting m')
            ->where('m.project_structure_id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        $currentRevision = DoctrineQuery::create()
            ->select('r.id, r.project_structure_id, r.revision, r.version')
            ->from('ProjectRevision r')
            ->where('r.id = ?', $revisionId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        $previousRevision = DoctrineQuery::create()
            ->select('r.id, r.project_structure_id, r.revision, r.version')
            ->from('ProjectRevision r')
            ->where('r.version = ?', $currentRevision['version'] - 1)
            ->andWhere('r.project_structure_id = ?', $currentRevision['project_structure_id'])
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        $stmt = $pdo->prepare("SELECT r.id FROM " . ProjectRevisionTable::getInstance()->getTableName() . " r
            WHERE r.version < " . $currentRevision['version'] . " AND r.project_structure_id = " . $currentRevision['project_structure_id']);

        $stmt->execute();

        $previousRevisionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        //Get Element List
        $stmt = $pdo->prepare("SELECT e.id, e.project_structure_id, e.description, e.priority FROM " . BillElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute(array(
            'project_structure_id' => $billId
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($elements)
        {
            $stmt = $pdo->prepare("SELECT i.element_id, i.id FROM " . BillItemTable::getInstance()->getTableName() . " i
            WHERE i.id = i.root_id AND i.element_id IN (SELECT e.id FROM " . BillElementTable::getInstance()->getTableName() . " e
            WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL ORDER BY e.priority) AND i.deleted_at IS NULL ORDER BY i.priority");

            $stmt->execute(array(
                'project_structure_id' => $billId
            ));

            $roots = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

            foreach ($elements as $element)
            {
                $result = array(
                    'id'                   => $element['id'],
                    'project_structure_id' => $element['project_structure_id'],
                    'description'          => $element['description'],
                    'priority'             => $element['priority'],
                    'items'                => array(),
                    'itemsToUpdate'        => array(),
                    'priorityToUpdate'     => array()
                );

                //Get Bill Pages Information
                $result['billPages'] = DoctrineQuery::create()->select('p.id, p.element_id, p.page_no, p.revision_id, p.new_revision_id, i.id, i.bill_page_id, i.bill_item_id, i.new_item_from_new_revision')
                    ->from('BillPage p')
                    ->leftJoin('p.Items i')
                    ->where('(p.new_revision_id = ? OR p.revision_id = ?) AND p.element_id = ?',
                        array( $currentRevision['id'], $previousRevision['id'], $element['id'] ))
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                //Get Collection Pages Information
                $result['collectionPages'] = DoctrineQuery::create()->select('c.id, c.element_id, c.revision_id, c.page_no')
                    ->from('BillCollectionPage c')
                    ->whereIn('c.element_id', $element['id'])
                    ->andWhere('c.revision_id = ?', $previousRevision['id'])
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                if (array_key_exists($element['id'], $roots) && $roots[$element['id']])
                {
                    $rootIds         = $roots[$element['id']];
                    $implodedRootIds = implode(',', $rootIds);
                    $billItemIds     = array();
                    $affectedRootIds = array();

                    $stmt = $pdo->prepare("SELECT p.id, c.id FROM " . BillItemTable::getInstance()->getTableName() . " p
                        JOIN " . BillItemTable::getInstance()->getTableName() . " c ON c.lft BETWEEN p.lft AND p.rgt
                        WHERE p.id IN (" . $implodedRootIds . ") AND p.id = c.root_id AND
                        (c.project_revision_id = :revision_id OR c.deleted_at_project_revision_id = :revision_id)
                        AND c.deleted_at IS NULL ORDER BY p.priority, c.lft");

                    $stmt->execute(array(
                        'revision_id' => $revisionId
                    ));

                    $affectedRootIdAndItems = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

                    foreach ($affectedRootIdAndItems as $rootId => $billIds)
                    {
                        $billItemIds = array_merge($billItemIds, $billIds);

                        array_push($affectedRootIds, $rootId);
                    }

                    $sqlCond = null;

                    //Get Node To Update
                    if (count($affectedRootIds))
                    {
                        $stmt = $pdo->prepare("SELECT c.id, c.priority, c.lft, c.rgt, c.level FROM " . BillItemTable::getInstance()->getTableName() . " c
                        WHERE c.root_id IN (" . implode(',', $affectedRootIds) . ") AND
                        (c.project_revision_id != :revision_id OR c.deleted_at_project_revision_id != :revision_id)
                        AND c.deleted_at IS NULL ORDER BY c.lft");

                        $stmt->execute(array(
                            'revision_id' => $revisionId
                        ));

                        $result['itemsToUpdate'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $sqlCond = 'AND c.id NOT IN (' . implode(',', $affectedRootIds) . ')';
                    }

                    if (count($previousRevisionIds) && count($billItemIds))
                    {
                        //Get Priority to update by previous version
                        $stmt = $pdo->prepare("SELECT c.id, c.priority, c.lft, c.rgt, c.level FROM " . BillItemTable::getInstance()->getTableName() . " c
                            LEFT JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.id = c.element_id
                            WHERE c.id = c.root_id " . $sqlCond . " AND (c.project_revision_id IN (" . implode(',',
                                $previousRevisionIds) . ") OR c.project_revision_id = " . $currentRevision['id'] . ")
                            AND e.id = " . $element['id'] . " AND c.deleted_at IS NULL ORDER BY c.priority");

                        $stmt->execute();

                        $result['priorityToUpdate'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }

                    if (count($billItemIds))
                    {
                        $result['items'] = DoctrineQuery::create()->select('c.id, c.description, c.type, c.uom_id, c.element_id, c.grand_total_after_markup, c.grand_total_quantity,
                            c.bill_ref_element_no, c.bill_ref_page_no, c.bill_ref_char, c.priority, c.root_id, c.lft, c.rgt, c.level, c.project_revision_id, c.deleted_at_project_revision_id, c.project_revision_deleted_at,
                            uom.id, uom.name, uom.symbol, uom.type, type.id, type.bill_item_id, type.bill_column_setting_id, type.include, type.total_quantity, 
                            type_fc.id, type_fc.relation_id, type_fc.column_name, type_fc.final_value, type_fc.created_at, ls.*, pc.supply_rate, pc.bill_item_id')
                            ->from('BillItem c')
                            ->leftJoin('c.BillItemTypeReferences type')
                            ->leftJoin('c.LumpSumPercentage ls')
                            ->leftJoin('c.PrimeCostRate pc')
                            ->leftJoin('type.FormulatedColumns type_fc')
                            ->leftJoin('c.UnitOfMeasurement uom')
                            ->whereIn('c.id', $billItemIds)
                            ->andWhere('c.deleted_at IS NULL')
                            ->orderBy('c.priority, c.lft, c.level')
                            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();
                    }
                }

                unset( $element );

                array_push($billStructure, $result);
            }
        }

        if ($elements)
        {
            return array(
                'elementsAndItems'   => ( $elements && count($elements) > 0 ) ? $billStructure : null,
                'billColumnSettings' => $billColumnSettings,
                'billMarkupSetting'  => $billMarkupSetting,
                'billLayoutSetting'  => $bill['BillLayoutSetting'],
                'billType'           => $billType
            );
        }
        else
        {
            return false;
        }
    }

    protected function getLumpSumPercent($billId)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, c.bill_item_id, c.rate, c.percentage, c.amount FROM " . BillItemLumpSumPercentageTable::getInstance()->getTableName() . " c
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON (c.bill_item_id = i.id AND i.type = " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . ")
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            WHERE e.project_structure_id = " . $billId . "
            AND c.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
    }

    protected function getPrimeCostRates($billId)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, c.bill_item_id, c.supply_rate, c.wastage_percentage, c.wastage_amount, c.labour_for_installation, c.other_cost, c.profit_percentage, c.profit_amount, c.total FROM " . BillItemPrimeCostRateTable::getInstance()->getTableName() . " c
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON (c.bill_item_id = i.id AND i.type = " . BillItem::TYPE_ITEM_PC_RATE . ")
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            WHERE e.project_structure_id = " . $billId . "
            AND c.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
    }

}
