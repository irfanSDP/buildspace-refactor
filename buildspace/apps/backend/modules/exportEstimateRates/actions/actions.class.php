<?php

/**
 * exportEstimateRates actions.
 *
 * @package    buildspace
 * @subpackage exportEstimateRates
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class exportEstimateRatesActions extends sfActions
{
	public function executeExportEstimateRatesByProject(sfWebRequest $request)
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

	        $projectId = $project['structure']['id'];

	        $project['structure']['tender_amount']                               = ProjectStructureTable::getOverallTotalForProject($projectId);
	        $project['structure']['tender_amount_except_prime_cost_provisional'] = ProjectStructureTable::getOverallTotalForProjectWithoutPrimeCostAndProvisionalBill($projectId);
	        $project['structure']['tender_som_amount']                           = SupplyOfMaterialTable::getTotalAmount($projectId);

	        unset( $project['structure']['tender_origin_id'], $project['mainInformation']['id'] );

	        $projectUniqueId = $project['mainInformation']['unique_id'];

            $sfProjectExport = new sfBuildspaceExportProjectXML($count . "_" . $project['structure']['id'],
                $projectUniqueId, ExportedFile::EXPORT_TYPE_RATES);

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
	                $billData = $this->getEstimateBillRates($structure['id']);

	                $sfBillExport = new sfBuildspaceExportEstimateBillRatesXML($count . '_' . $structure['title'],
	                    $sfProjectExport->uploadPath, $structure['id']);
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
	            "attachment; filename*=UTF-8''" . rawurlencode($request->getParameter('filename')) . '.er'
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

    protected function getEstimateBillRates($billId)
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

                $result = array(
                    'id'           => $element['id'],
                    'total_amount' => 0,
                    'items'        => array()
                );

                if (array_key_exists($element['id'], $roots) && $roots[$element['id']])
                {
                    $rootIds = $roots[$element['id']];

                    $stmt = $pdo->prepare("SELECT c.id, c.type, c.uom_id, c.description, c.grand_total_after_markup AS grand_total, ifc.final_value AS rate, markup.final_value as markup_percentage FROM " . BillItemTable::getInstance()->getTableName() . " p
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
                            $billItems[$k]['rate'] = BillItemTable::calculateRateAfterMarkup($item['rate'],
                                $item['markup_percentage'], $markupInfo);

                            $elementTotalAmount += $item['grand_total'];

                            unset($billItems[$k]['markup_percentage']);
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
}
