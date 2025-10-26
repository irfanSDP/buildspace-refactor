<?php

/**
 * subPackageExportFile actions.
 *
 * @package    buildspace
 * @subpackage subPackageExportFile
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subPackageExportFileActions extends BaseActions {

    public function executeExportSubPackage(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            strlen($request->getParameter('filename')) > 0 and
            $subPackage = SubPackageTable::generateExportSubPackageInformation($request->getParameter('sid')) and
            $request->hasParameter('with_rate')
        );

        $withRate = strtolower($request->getParameter('with_rate')) == "true" ? true : false;
        $subContractor = $request->hasParameter('cid')  && !empty($request->getParameter('cid')) ? CompanyTable::getInstance()->find($request->getParameter('cid')) : null;

        try
        {
            $filesToZip = array();

            $breakdown = $subPackage['breakdown'];
            $projectId = $subPackage['structure']['id'];

            $count = 0;

            $projectUniqueId = $subPackage['mainInformation']['unique_id'] = SubPackageTable::generateSubPackageUniqueIdByProjectId($subPackage['id'], $projectId);

            $sfSubPackageExport = new sfBuildspaceExportSubPackageXML($count . "_" . $subPackage['structure']['id'], $projectUniqueId, ExportedFile::EXPORT_TYPE_SUB_PACKAGE, $subPackage['id']);

            $sfSubPackageExport->process($subPackage['structure'], $subPackage['mainInformation'], $subPackage['breakdown'], $subPackage['revisions'], true);

            array_push($filesToZip, $sfSubPackageExport->getFileInformation());

            $subPackageBillLayoutSetting = DoctrineQuery::create()
                ->select('l.*, lh.*, lp.*')
                ->from('SubPackageBillLayoutSetting l')
                ->leftJoin('l.SubPackageBillHeadSettings lh')
                ->leftJoin('l.SubPackageBillPhrase lp')
                ->where('l.sub_package_id = ?', $subPackage['id'])
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();

            /* Change BillLayout Setting Key */
            $subPackageBillLayoutSetting['BillHeadSettings'] = array();

            foreach ( $subPackageBillLayoutSetting['SubPackageBillHeadSettings'] as $v )
            {
                $v['bill_layout_setting_id'] = $subPackageBillLayoutSetting['id'];

                unset( $v['sub_package_bill_layout_setting_id'] );

                $subPackageBillLayoutSetting['BillHeadSettings'][] = $v;

                unset( $v );
            }

            $subPackageBillLayoutSetting['SubPackageBillPhrase']['bill_layout_setting_id'] = $subPackageBillLayoutSetting['id'];

            unset( $subPackageBillLayoutSetting['SubPackageBillPhrase']['sub_package_bill_layout_setting_id'] );

            $subPackageBillLayoutSetting['BillPhrase'] = $subPackageBillLayoutSetting['SubPackageBillPhrase'];

            unset( $subPackageBillLayoutSetting['SubPackageBillHeadSettings'], $subPackageBillLayoutSetting['SubPackageBillPhrase'] );

            foreach ( $breakdown as $structure )
            {
                $count ++;

                if ( $structure['type'] == ProjectStructure::TYPE_BILL )
                {
                    $billData = $this->getBillInformation($structure['id'], $subPackage['id'], $withRate, $subContractor);

                    $billData['billLayoutSetting'] = $subPackageBillLayoutSetting;

                    $billData['billLayoutSetting']['bill_id'] = $structure['id'];

                    $billLayoutSetting = DoctrineQuery::create()
                        ->select('l.page_no_prefix')
                        ->from('BillLayoutSetting l')
                        ->where('l.bill_id = ?', $structure['id'])
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->fetchOne();

                    $billData['billLayoutSetting']['page_no_prefix'] = $billLayoutSetting['page_no_prefix'];

                    unset( $billData['billLayoutSetting']['sub_package_id'] );

                    $sfBillExport = new sfBuildspaceExportSubPackageBillXML($count . '_' . $structure['title'], $sfSubPackageExport->uploadPath, $structure['id'], null, null, $withRate);

                    $sfBillExport->process($billData, true);

                    array_push($filesToZip, $sfBillExport->getFileInformation());

                    unset( $sfBillExport );
                    unset( $structure );
                    unset( $billData );
                }
            }

            unset( $sfSubPackageExport );

            $sfZipGenerator = new sfZipGenerator("SubPackage_" . $subPackage['id'], null, null, true, true);

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

            ob_end_flush();

            return $this->renderText($fileContents);
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'errorMsg' => $errorMsg, 'success' => false ));
    }

    protected function getBillInformation($billId, $subPackageId, $withRate = false, Company $subContractor=null)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $subPackage = SubPackageTable::getInstance()->find($subPackageId);

        $billStructure = array();
        
        //Get Bill and Its information
        $bill = DoctrineQuery::create()
            ->select('p.id, s.*, bt.*, m.*, l.*, lh.*, lp.*')
            ->from('ProjectStructure p')
            ->leftJoin('p.BillSetting s')
            ->leftJoin('p.BillMarkupSetting m')
            ->leftJoin('p.BillType bt')
            ->where('p.id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        $stmt = $pdo->prepare("SELECT DISTINCT stype.bill_column_setting_id, cs.id, COUNT(stype.bill_column_setting_id) AS quantity,
                cs.name, cs.project_structure_id, cs.use_original_quantity, cs.floor_area_use_metric, cs.floor_area_display_metric
                FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
                LEFT JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
                WHERE cs.project_structure_id = " . $billId . " AND stype.sub_package_id = " . $subPackage->id . "
                GROUP BY stype.bill_column_setting_id, cs.id, cs.use_original_quantity, cs.name, cs.project_structure_id, cs.floor_area_use_metric, cs.floor_area_display_metric
                ORDER BY stype.bill_column_setting_id");

        $stmt->execute();

        $billColumnSettings = array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC));

        // Add Sub Package Unit counters.
        foreach($billColumnSettings as $billColumnSettingId => $columnData)
        {
            $stmt = $pdo->prepare("SELECT stype.counter
                FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
                LEFT JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
                WHERE cs.project_structure_id = " . $billId . " AND stype.sub_package_id = " . $subPackage->id . "  AND cs.id = ".$billColumnSettingId."
                ORDER BY stype.bill_column_setting_id");

            $stmt->execute();

            $billColumnSettings[$billColumnSettingId]['counters'] = array_map('reset', $stmt->fetchAll(PDO::FETCH_ASSOC));
        }

        if ( !count($billColumnSettings) )
        {
            $stmt = $pdo->prepare("SELECT DISTINCT cs.id, cs.id AS id, 1 AS quantity, cs.name, cs.project_structure_id, cs.use_original_quantity,
                cs.floor_area_use_metric, cs.floor_area_display_metric FROM " . BillColumnSettingTable::getInstance()->getTableName() . " cs
                WHERE cs.project_structure_id = " . $billId . " ORDER BY cs.id");

            $stmt->execute();

            $billColumnSettings = array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC));
        }

        $columnSettingIds = Utilities::arrayValueRecursive('id', $billColumnSettings);

        $bill['BillColumnSettings'] = $billColumnSettings;

        $isPostContract = false;

        $sqlFieldCond = '(
            CASE 
                WHEN spsori.sub_package_id is not null THEN spsori.sub_package_id
                WHEN spri.sub_package_id is not null THEN spri.sub_package_id
            ELSE
                spbi.sub_package_id
            END
            ) AS sub_package_id';

        $stmtItem = $pdo->prepare("SELECT DISTINCT c.id AS bill_column_setting_id, c.use_original_quantity, e.priority, e.description, e.id AS element_id, i.id AS bill_item_id, rate.final_value AS final_value, $sqlFieldCond
        FROM " . SubPackageTable::getInstance()->getTableName() . " sp
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.root_id = sp.project_structure_id
        LEFT JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " AS spri ON spri.sub_package_id = sp.id
        LEFT JOIN " . SubPackageScheduleOfRateItemTable::getInstance()->getTableName() . " AS spsori ON spsori.sub_package_id = sp.id
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = bill.id
        JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " c ON c.project_structure_id = e.project_structure_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
        LEFT JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON bur.bill_item_id = i.id AND bur.resource_item_library_id = spri.resource_item_id AND bur.deleted_at IS NULL
        LEFT JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sifc ON sifc.relation_id = spsori.schedule_of_rate_item_id AND sifc.deleted_at IS NULL
        LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS rate ON rate.relation_id = i.id
        LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = sp.id
        WHERE sp.id =" . $subPackage->id . " AND sp.deleted_at IS NULL
        AND bill.id = " . $bill['id'] . " AND bill.deleted_at IS NULL
        AND NOT (spri.sub_package_id IS NULL AND spsori.sub_package_id IS NULL and spbi.sub_package_id IS NULL)
        AND (rate.relation_id = bur.bill_item_id OR rate.schedule_of_rate_item_formulated_column_id = sifc.id OR rate.relation_id = spbi.bill_item_id)
        AND rate.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "' AND rate.final_value <> 0 AND rate.deleted_at IS NULL
        AND i.type <> " . BillItem::TYPE_ITEM_NOT_LISTED . " AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND e.deleted_at IS NULL AND c.deleted_at IS NULL ORDER BY i.id");

        $stmtItem->execute();

        $records = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

        $elementArray = array();

        foreach ( $records as $record )
        {
            if ( !array_key_exists($record['element_id'], $elementArray) )
            {
                $elementArray[$record['element_id']] = array(
                    'id'                   => $record['element_id'],
                    'project_structure_id' => $bill['id'],
                    'description'          => $record['description'],
                    'priority'             => $record['priority']
                );
            }

            unset( $record );
        }

        if ( count($elementArray) )
        {
            foreach ( $elementArray as $element )
            {
                $elementObj = BillElementTable::getInstance()->find($element['id']);

                $totalCostByBillItems  = $subPackage->getEstimatedTotalByBillItems($elementObj);
                $totalCostByBillItems += $subPackage->getEstimatedTotalNoBuildUpByBillItems($elementObj);

                unset($elementObj);

                $result = array(
                    'id'                   => $element['id'],
                    'project_structure_id' => $element['project_structure_id'],
                    'description'          => $element['description'],
                    'priority'             => $element['priority'],
                    'items'                => []
                );

                $billItems = [];

                if ( !empty($totalCostByBillItems) )
                {
                    $billItemIds           = array_keys($totalCostByBillItems);
                    $postContractJoinTable = null;

                    if ( $isPostContract )
                    {
                        $postContractJoinTable = 'JOIN ' . PostContractBillItemRateTable::getInstance()->getTableName() . " pc ON (pc.bill_item_id = p.id)";
                    }

                    $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.description
                        FROM " . BillItemTable::getInstance()->getTableName() . " c
                        JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt {$postContractJoinTable}
                        WHERE c.root_id = p.root_id AND c.type != " . BillItem::TYPE_ITEM_NOT_LISTED . "
                        AND c.id IN (" . implode(',', $billItemIds) . ") AND c.element_id = " . $element['id'] . "
                        AND p.element_id = " . $element['id'] . " AND c.project_revision_deleted_at IS NULL
                        AND c.deleted_at IS NULL AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL");

                    $stmt->execute();

                    $finalItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $billItemIds = Utilities::arrayValueRecursive('id', $finalItems);

                    $billItems = DoctrineQuery::create()->select('c.id, c.description, c.type, c.uom_id, c.element_id, c.grand_total_after_markup, c.grand_total_quantity,
                        c.bill_ref_element_no, c.bill_ref_page_no, c.bill_ref_char, c.priority, c.root_id, c.lft, c.rgt, c.level, 1 AS project_revision_id, c.deleted_at_project_revision_id, c.project_revision_deleted_at,
                        uom.id, uom.name, uom.symbol, uom.type, type.id, type.bill_item_id, type.bill_column_setting_id, type.include, type.total_quantity,
                        type_fc.id, type_fc.relation_id, type_fc.column_name, type_fc.final_value, type_fc.created_at, ls.*, pc.bill_item_id,
                        pc.supply_rate, pc.wastage_percentage,
                        pc.wastage_amount, pc.labour_for_installation,
                        pc.other_cost, pc.profit_percentage,
                        pc.profit_amount, pc.total')
                        ->from('BillItem c')
                        ->leftJoin('c.BillItemTypeReferences type ON type.bill_item_id = c.id AND type.bill_column_setting_id IN (' . implode(',', $columnSettingIds) . ')')
                        ->leftJoin('c.PrimeCostRate pc')
                        ->leftJoin('type.FormulatedColumns type_fc')
                        ->leftJoin('c.UnitOfMeasurement uom')
                        ->whereIn('c.id', $billItemIds)
                        ->andWhere('c.deleted_at IS NULL')
                        ->orderBy('c.priority, c.lft, c.level')
                        ->fetchArray();

                    if ( $withRate )
                    {
                        /* Generate Rates & GrandTotal*/
                        if($subContractor && $subContractor instanceof Company)
                        {
                            $stmt = $pdo->prepare("SELECT rate.bill_item_id, rate.rate
                            FROM " .SubPackageBillItemRateTable::getInstance()->getTableName(). " rate
                            JOIN " .SubPackageCompanyTable::getInstance()->getTableName(). " c ON c.id = rate.sub_package_company_id
                            WHERE c.company_id = ".$subContractor->id." AND c.sub_package_id = " . $subPackage->id."
                            AND rate.bill_item_id IN (" . implode(',', $billItemIds) . ")");

                            $stmt->execute();

                            $rates = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                        }
                        else//estimation rates
                        {
                            $rates = [];

                            foreach($totalCostByBillItems as $billItemId => $data)
                            {
                                $rates[$billItemId] = $data['total_cost_after_conversion'];
                            }
                        }

                        foreach ( $billItems as $k => $billItem )
                        {
                            if ( !empty($billItem['BillItemTypeReferences']) )
                            {
                                $grandTotal = 0;
                                $totalPerUnit = 0;

                                foreach ( $billItem['BillItemTypeReferences'] as $key => $type )
                                {
                                    if ( !empty($type['FormulatedColumns']) )
                                    {
                                        $totalPerUnit = 0;

                                        foreach ( $type['FormulatedColumns'] as $type_fc )
                                        {
                                            $matchingQtyType = ( ( $type_fc['column_name'] == 'quantity_per_unit' ) && ( $bill['BillColumnSettings'][ $type['bill_column_setting_id'] ]['use_original_quantity'] ) ) || ( ( $type_fc['column_name'] == 'quantity_per_unit_remeasurement' ) && ( ! $bill['BillColumnSettings'][ $type['bill_column_setting_id'] ]['use_original_quantity'] ) );
                                            if( $matchingQtyType && array_key_exists($billItem['id'], $rates) )
                                            {
                                                $totalPerUnit += $type_fc['final_value'] * $rates[$billItem['id']];
                                            }

                                            unset( $type_fc );
                                        }
                                    }

                                    $totalPerType = $totalPerUnit * $billColumnSettings[$type['bill_column_setting_id']]['quantity'];

                                    $grandTotal += $totalPerType;

                                    $billItems[$k]['BillItemTypeReferences'][$key]['grand_total'] = $billItems[$k]['BillItemTypeReferences'][$key]['grand_total_after_markup'] = $totalPerType;

                                    unset( $type );
                                }

                                $billItems[$k]['grand_total'] = $billItems[$k]['grand_total_after_markup'] = $grandTotal;
                            }

                            $billItems[$k]['rate'] = ( array_key_exists($billItem['id'], $rates) ) ? $rates[$billItem['id']] : 0;

                            unset( $billItem );
                        }
                    }

                    unset( $records );
                }

                $result['items'] = $billItems;

                array_push($billStructure, $result);

                unset( $element );
            }
        }

        if ( $bill )
        {
            return array(
                'elementsAndItems'   => ( $elementArray && count($elementArray) > 0 ) ? $billStructure : null,
                'billSetting'        => $bill['BillSetting'],
                'billMarkupSetting'  => $bill['BillMarkupSetting'],
                'billColumnSettings' => $bill['BillColumnSettings'],
                'billType'           => $bill['BillType']
            );
        }
        else
        {
            return false;
        }
    }

    public function executeExportContractorRates(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            strlen($request->getParameter('filename')) > 0 and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $subPackage = SubPackageTable::generateExportSubPackageInformation($request->getParameter('sid')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $subPackageCompanyXref = SubPackageCompanyTable::getBySubPackageIdAndCompanyId($request->getParameter('sid'), $company->id)
        );

        $errorMsg = null;

        try
        {
            $count = 0;

            $filesToZip = array();

            unset( $subPackage['structure']['tender_origin_id'], $subPackage['mainInformation']['id'] );

            $projectUniqueId = $subPackage['mainInformation']['unique_id'] = SubPackageTable::generateSubPackageUniqueIdByProjectId($subPackage['id'], $project->id);

            $sfProjectExport = new sfBuildspaceExportSubPackageXML($count . "_" . $subPackage['structure']['id'], $projectUniqueId, ExportedFile::EXPORT_TYPE_RATES, $subPackage['id']);

            $sfProjectExport->process($subPackage['structure'], $subPackage['mainInformation'], null, null, true);

            array_push($filesToZip, $sfProjectExport->getFileInformation());

            foreach ( $subPackage['breakdown'] as $structure )
            {
                $count ++;

                if ( $structure['type'] == ProjectStructure::TYPE_BILL )
                {
                    $billData = SubPackageBillItemRateTable::getContractorBillRatesByBillId($structure['id'], $subPackageCompanyXref->id);

                    $sfBillExport = new sfBuildspaceExportContractorBillRatesXML($subPackageCompanyXref, $count . '_' . $structure['title'], $sfProjectExport->uploadPath, $structure['id']);

                    $sfBillExport->process($billData, true);

                    array_push($filesToZip, $sfBillExport->getFileInformation());
                }

                unset( $structure );
            }

            $sfZipGenerator = new sfZipGenerator("SubPackageRationalizedRate_" . $project->id, null, null, true, true);

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

            ob_end_flush();

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

}