<?php

/**
 * rationalizeRate actions.
 *
 * @package    buildspace
 * @subpackage rationalizeRate
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class rationalizeRateActions extends BaseActions {

    public function executeImportRationalizedRates(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $request->isMethod('post') and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');
        $success        = null;
        $errorMsg       = null;

        TenderBillItemRationalizedRatesTable::flushExistingRatesByProjectId($project->id);
        TenderBillElementRationalizedGrandTotalTable::flushExistingRatesByProjectId($project->id);
        TenderBillItemNotListedRationalizedTable::flushExistingByProjectId($project->id);

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

        $con = ProjectStructureTable::getInstance()->getConnection();

        try
        {
            if ( count($fileToUnzip) )
            {
                $con->beginTransaction();

                $sfZipGenerator = new sfZipGenerator($fileToUnzip['name'], $fileToUnzip['path'], $fileToUnzip['ext'], true, true);

                $extractedFiles = $sfZipGenerator->unzip();

                $extractDir = $sfZipGenerator->extractDir;

                $count = 0;

                $userId = $this->getUser()->getGuardUser()->id;

                if ( count($extractedFiles) )
                {
                    foreach ( $extractedFiles as $file )
                    {
                        if ( $count == 0 )
                        {
                            $importer = new sfBuildspaceXMLParser($file['filename'], $extractDir, null, false);

                            $importer->read();

                            $xmlData = $importer->getProcessedData();

                            if(!empty($xmlData->{sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES}))
                            {
                                $tenderAlternatives = $xmlData->{sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES}->children();
                                $originalProject = $xmlData->{sfBuildspaceExportProjectXML::TAG_ROOT}->children();
                                
                                foreach($tenderAlternatives as $tenderAlternative)
                                {
                                    if((int)$tenderAlternative->is_awarded)
                                    {
                                        $tenderOriginId = ProjectStructureTable::generateTenderOriginId($xmlData->attributes()->buildspaceId, (int) $tenderAlternative->id, (int)$originalProject->id);

                                        $tenderAlternativeObj = Doctrine_Query::create()
                                            ->from('TenderAlternative t')
                                            ->where('t.tender_origin_id = ?', $tenderOriginId)
                                            ->fetchOne();
                                        
                                        if($tenderAlternativeObj)
                                        {
                                            $pdo = $con->getDbh();

                                            $stmt = $pdo->prepare("UPDATE " . TenderAlternativeTable::getInstance()->getTableName() . "
                                            SET is_awarded = FALSE
                                            WHERE id <> ".$tenderAlternativeObj->id." AND project_structure_id = ".$project->id);

                                            $stmt->execute([]);
                                            
                                            $tenderAlternativeObj->is_awarded = true;
                                            $tenderAlternativeObj->save($con);
                                        }
                                    }
                                }
                            }
                            
                            if ( $project->MainInformation->unique_id != $xmlData->attributes()->uniqueId )
                            {
                                throw new Exception(ProjectMainInformation::ERROR_MSG_WRONG_PROJECT_RATES);
                            }

                            if ( $xmlData->attributes()->exportType != ExportedFile::EXPORT_TYPE_RATES )
                            {
                                throw new Exception(ExportedFile::ERROR_MSG_WRONG_RATES_FILE);
                            }
                        }
                        else
                        {
                            if ( (int) $xmlData->attributes()->subPackageId > 0 )
                            {
                                $importer = new sfBuildspaceImportSubPackageRationalizedBillRatesXML($userId, $project->toArray(), $file['filename'], $extractDir, null, false);

                                $importer->setSubPackageId((int) $xmlData->attributes()->subPackageId);
                            }
                            else
                            {
                                $importer = new sfBuildspaceImportRationalizedBillRatesXML($userId, $project->toArray(), $file['filename'], $extractDir, null, false);
                            }

                            $importer->process();
                        }

                        $count ++;
                    }
                }

                $con->commit();

                $success = true;
            }
        }
        catch (Exception $e)
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

    public function executeGetProjectBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $records = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', ProjectStructure::TYPE_BILL)
            ->addOrderBy('s.lft ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $count = 0;

        $form = new BaseForm();

        $projectSumTotal = ProjectStructureTable::getOverallTotalForProject($project->id);

        $rationalizedRates = TenderBillItemRationalizedRatesTable::getOverallBillTotalByProject($project->id);

        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);

        foreach ( $records as $key => $record )
        {
            $count = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

            if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
            {
                $records[$key]['bill_type']                  = $record['BillType']['type'];
                $records[$key]['bill_status']                = $record['BillType']['status'];
                $records[$key]['overall_total_after_markup'] = (array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;

                if ( array_key_exists($record['id'], $rationalizedRates['bill']) )
                {
                    $records[$key]['rationalized_overall_total_after_markup'] = $rationalizedRates['bill'][$record['id']];
                }
                else
                {
                    $records[$key]['rationalized_overall_total_after_markup'] = 0;
                }
            }
            else if ( $record['type'] == ProjectStructure::TYPE_ROOT )
            {
                $records[$key]['overall_total_after_markup']              = $projectSumTotal;
                $records[$key]['rationalized_overall_total_after_markup'] = $rationalizedRates['project_total'];
            }

            $records[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $records[$key]['BillLayoutSetting'] );
            unset( $records[$key]['BillType'] );
            unset( $records[$key]['BillColumnSettings'] );
        }

        $records[] = [
            'id'                                      => Constants::GRID_LAST_ROW,
            'title'                                   => "",
            'type'                                    => 1,
            'level'                                   => 0,
            'count'                                   => null,
            'overall_total_after_markup'              => 0,
            'rationalized_overall_total_after_markup' => 0,
            '_csrf_token'                             => $form->getCSRFToken()
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetTenderAlternatives(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $pdo = TenderAlternativeTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT DISTINCT ta.id, ta.title, ta.description, ta.project_structure_id, ta.is_awarded, ta.project_revision_id, ta.created_at, ta.deleted_at_project_revision_id, ta.project_revision_deleted_at
            FROM " . TenderAlternativeTable::getInstance()->getTableName() . " ta
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON ta.project_structure_id = p.id
            WHERE p.id = " . $project->id . "
            AND p.deleted_at IS NULL AND ta.deleted_at IS NULL AND ta.project_revision_deleted_at IS NULL
            ORDER BY ta.created_at ASC");
        
        $stmt->execute();
        $tenderAlternatives = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        $items[] = [
            'id'                                      => -9999,
            'count'                                   => null,
            'title'                                   => $project->MainInformation->title,
            'rationalized_overall_total_after_markup' => 0,
            'overall_total_after_markup'              => 0,
            'level'                                   => 0,
            '_csrf_token'                             => $form->getCSRFToken()
        ];

        $overallTotalAfterMarkupRecords = TenderAlternativeTable::getOverallTotalForTenderAlternatives($project);
        $rationalizedRates = TenderBillItemRationalizedRatesTable::getTenderAlternativeOverallBillTotalByProject($project);

        $count = 1;
        foreach($tenderAlternatives as $idx => $tenderAlternative)
        {
            $tenderAlternatives[$idx]['rationalized_overall_total_after_markup'] = (array_key_exists($tenderAlternative['id'], $rationalizedRates)) ? $rationalizedRates[$tenderAlternative['id']] : 0;
            $tenderAlternatives[$idx]['overall_total_after_markup'] = (array_key_exists($tenderAlternative['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$tenderAlternative['id']] : 0;
            $tenderAlternatives[$idx]['level']                      = 1;
            $tenderAlternatives[$idx]['count']                      = $count++;
            $tenderAlternatives[$idx]['_csrf_token']                = $form->getCSRFToken();

            $items[] = $tenderAlternatives[$idx];
        }

        $items[] = [
            'id'                                      => Constants::GRID_LAST_ROW,
            'count'                                   => null,
            'title'                                   => "",
            'rationalized_overall_total_after_markup' => 0,
            'overall_total_after_markup'              => 0,
            'level'                                   => 0,
            '_csrf_token'                             => $form->getCSRFToken()
        ];

        return $this->renderJson([
            'identifier' => 'id',
            'items'      => $items
        ]);
    }

    public function executeGetTenderAlternativeBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('id'))
        );

        $project = $tenderAlternative->ProjectStructure;
        $records = $tenderAlternative->getAssignedBills();

        $count = 0;

        $form = new BaseForm();

        $rationalizedRates = $tenderAlternative->getOverallRationalizedBillTotal();
        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);

        foreach ( $records as $key => $record )
        {
            $count = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

            if ( $record['type'] == ProjectStructure::TYPE_BILL )
            {
                $records[$key]['overall_total_after_markup'] = (array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;

                if ( array_key_exists($record['id'], $rationalizedRates['bill']) )
                {
                    $records[$key]['rationalized_overall_total_after_markup'] = $rationalizedRates['bill'][$record['id']];
                }
                else
                {
                    $records[$key]['rationalized_overall_total_after_markup'] = 0;
                }
            }
            else if ( $record['type'] == ProjectStructure::TYPE_ROOT )
            {
                $records[$key]['overall_total_after_markup']              = $tenderAlternative->getOverallTotal();
                $records[$key]['rationalized_overall_total_after_markup'] = $rationalizedRates['project_total'];
            }

            $records[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        $records[] = [
            'id'                                      => Constants::GRID_LAST_ROW,
            'title'                                   => "",
            'type'                                    => 1,
            'level'                                   => 0,
            'count'                                   => null,
            'overall_total_after_markup'              => 0,
            'rationalized_overall_total_after_markup' => 0,
            '_csrf_token'                             => $form->getCSRFToken()
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $elements = DoctrineQuery::create()->select('e.id, e.description, fc.column_name, fc.value, fc.final_value')
            ->from('BillElement e')->leftJoin('e.FormulatedColumns fc')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        $billMarkupSetting = $bill->BillMarkupSetting;

        //We get All Element Sum Group By Element Here so that we don't have to reapeat query within element loop
        $markupSettingsInfo = array(
            'bill_markup_enabled'    => $billMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage' => $billMarkupSetting->bill_markup_percentage,
            'element_markup_enabled' => $billMarkupSetting->element_markup_enabled,
            'item_markup_enabled'    => $billMarkupSetting->item_markup_enabled,
            'rounding_type'          => $billMarkupSetting->rounding_type > 0 ? $billMarkupSetting->rounding_type : BillMarkupSetting::ROUNDING_TYPE_DISABLED
        );

        $elementSumByBillColumnSetting = array();

        $rationalizedRates = TenderBillItemRationalizedRatesTable::getRationalizedGrandTotalByBillId($bill->id);

        //we get sum of elements total by bill column setting so we won't keep on calling the same query in element list loop
        foreach ( $bill->BillColumnSettings as $column )
        {
            //Get Element Total Rates
            $ElementTotalRates                          = ProjectStructureTable::getTotalItemRateByAndBillColumnSettingIdGroupByElement($bill, $column);
            $elementSumByBillColumnSetting[$column->id] = $ElementTotalRates['grandTotalElement'];
            $totalRateByBillColumnSetting[$column->id]  = $ElementTotalRates['elementToRates'];
            unset( $column );
        }

        foreach ( $elements as $key => $element )
        {
            $elements[$key]['markup_rounding_type'] = $markupSettingsInfo['rounding_type'];
            $overallTotalAfterMarkup                = 0;

            foreach ( $bill->BillColumnSettings as $column )
            {
                $total = $totalRateByBillColumnSetting[$column->id][$element['id']][0]['total_rate_after_markup'];
                $overallTotalAfterMarkup += $total;

                unset( $column );
            }

            unset( $elements[$key]['FormulatedColumns'] );

            if ( array_key_exists($element['id'], $rationalizedRates['element']) )
            {
                $elements[$key]['rationalized_overall_total_after_markup'] = $rationalizedRates['element'][$element['id']];
            }
            else
            {
                $elements[$key]['rationalized_overall_total_after_markup'] = 0;
            }

            $elements[$key]['overall_total_after_markup'] = $overallTotalAfterMarkup;
            $elements[$key]['_csrf_token']                = $form->getCSRFToken();
        }

        $defaultLastRow = array(
            'id'                         => Constants::GRID_LAST_ROW,
            'description'                => '',
            'overall_total_after_markup' => 0,
            'relation_id'                => $bill->id,
            '_csrf_token'                => $form->getCSRFToken()
        );

        array_push($elements, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $pdo                     = $bill->getTable()->getConnection()->getDbh();
        $form                    = new BaseForm();
        $items                   = array();
        $elementMarkupPercentage = 0;
        $pageNoPrefix            = $bill->BillLayoutSetting->page_no_prefix;

        /*
         * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
         */
        if ( $bill->BillMarkupSetting->element_markup_enabled )
        {
            $sql = "SELECT COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
                JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
                WHERE e.id = " . $element->id . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
                AND c.deleted_at IS NULL AND e.deleted_at IS NULL";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
            $elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;
        }

        $roundingType = $bill->BillMarkupSetting->rounding_type;

        $markupSettingsInfo = array(
            'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
            'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
            'element_markup_percentage' => $elementMarkupPercentage,
            'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
            'rounding_type'             => $roundingType
        );

        list(
            $billItems, $formulatedColumns, $quantityPerUnitByColumns,
            $billItemTypeReferences, $billItemTypeRefFormulatedColumns
            ) = BillItemTable::getDataStructureForBillItemList($element, $bill);

        $rationalizedRates = TenderBillItemRationalizedRatesTable::getAllRationalizedRatesByElementId($element->id);

        //Get Rationalized BillItemNotListed
        $sql = "SELECT  r.bill_item_id, r.description, uom.id AS uom_id, uom.symbol AS uom_symbol FROM " . TenderBillItemNotListedRationalizedTable::getInstance()->getTableName() . " r
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON r.bill_item_id = i.id AND i.deleted_at IS NULL
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON r.uom_id = uom.id AND uom.deleted_at IS NULL
            LEFT JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id AND e.deleted_at IS NULL
            WHERE e.id = " . $element->id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rationalizedItems = array_map('current', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC));

        //Get Rationalized BillItemNotListed
        $sql = "SELECT  r.bill_item_id, q.bill_column_setting_id, COALESCE(q.final_value,0) as value
            FROM " . TenderBillItemNotListedRationalizedQuantityTable::getInstance()->getTableName() . " q
            LEFT JOIN " . TenderBillItemNotListedRationalizedTable::getInstance()->getTableName() . " r ON r.id = q.tender_bill_not_listed_item_rationalized_id
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON r.bill_item_id = i.id AND i.deleted_at IS NULL
            LEFT JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id AND e.deleted_at IS NULL
            WHERE e.id = " . $element->id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rationalizedQuantities = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        if ( count($rationalizedQuantities) && count($rationalizedItems) )
        {
            foreach ( $rationalizedItems as $itemId => $item )
            {
                if ( array_key_exists($itemId, $rationalizedQuantities) )
                {
                    foreach ( $rationalizedQuantities[$itemId] as $k => $qty )
                    {
                        $rationalizedItems[$itemId]['quantities'][$qty['bill_column_setting_id']] = $qty['value'];
                    }
                }

                if ( array_key_exists($itemId, $rationalizedRates) )
                {
                    $rationalizedItems[$itemId]['rate']        = $rationalizedRates[$itemId][0]['rate'];
                    $rationalizedItems[$itemId]['grand_total'] = $rationalizedRates[$itemId][0]['grand_total'];
                }
            }
        }

        unset( $rationalizedQuantities );
        $rationalizedCount = 0;

        foreach ( $billItems as $billItem )
        {
            $rate                  = 0;
            $rateAfterMarkup       = 0;
            $itemMarkupPercentage  = 0;
            $grandTotalAfterMarkup = 0;
            $rationalizedNotListed = false;
            $rationalizedCount ++;

            $billItem['bill_ref']                    = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
            $billItem['type']                        = (string) $billItem['type'];
            $billItem['uom_id']                      = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
            $billItem['relation_id']                 = $element->id;
            $billItem['linked']                      = false;
            $billItem['_csrf_token']                 = $form->getCSRFToken();
            $billItem['project_revision_deleted_at'] = is_null($billItem['project_revision_deleted_at']) ? false : $billItem['project_revision_deleted_at'];

            if ( array_key_exists($billItem['id'], $formulatedColumns) )
            {
                $itemFormulatedColumns = $formulatedColumns[$billItem['id']];

                foreach ( $itemFormulatedColumns as $formulatedColumn )
                {
                    if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                    {
                        $rate = $formulatedColumn['final_value'];
                    }

                    if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
                    {
                        $itemMarkupPercentage = $formulatedColumn['final_value'];
                    }
                }

                unset( $formulatedColumns[$billItem['id']], $itemFormulatedColumns );

                $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
            }


            $billItem['rate_after_markup'] = $rateAfterMarkup;

            foreach ( $bill->BillColumnSettings as $column )
            {
                $quantityPerUnit = 0;

                if ( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($billItem['id'], $quantityPerUnitByColumns[$column->id]) )
                {
                    $quantityPerUnit = $quantityPerUnitByColumns[$column->id][$billItem['id']][0];
                    unset( $quantityPerUnitByColumns[$column->id][$billItem['id']] );
                }

                $total = 0;

                if ( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$column->id]) )
                {
                    $totalPerUnit = $rateAfterMarkup * $quantityPerUnit;
                    $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                    unset( $billItemTypeReferences[$column->id][$billItem['id']] );
                }

                $grandTotalAfterMarkup += $total;

            }

            $billItem['grand_total_after_markup'] = $grandTotalAfterMarkup;

            if ( array_key_exists($billItem['id'], $rationalizedRates) )
            {
                $billItem['rationalized_grand_total_after_markup'] = $rationalizedRates[$billItem['id']][0]['grand_total'];
                $billItem['rationalized_rate-value']               = $rationalizedRates[$billItem['id']][0]['rate'];
            }

            if ( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED )
            {
                $billItem['rationalized_grand_total_quantity']     = 0;
                $billItem['rationalized_rate-value']               = 0;
                $billItem['rationalized_grand_total_after_markup'] = 0;
                $billItem['rationalized_grand_total_quantity']     = 0;

                if ( count($rationalizedItems) && array_key_exists($billItem['id'], $rationalizedItems) )
                {
                    $rationalizedNotListed = $billItem;

                    $rationalizedNotListed['grand_total_after_markup'] = 0;
                    $rationalizedNotListed['rate_after_markup']        = 0;
                    $rationalizedNotListed['grand_total_quantity']     = 0;
                    $rationalizedNotListed['rationalized_rate-value']  = 0;

                    $totalQty = 0;

                    foreach ( $bill->BillColumnSettings as $column )
                    {
                        if ( array_key_exists($column->id, $rationalizedItems[$billItem['id']]['quantities']) )
                        {
                            $quantityPerUnit = $rationalizedItems[$billItem['id']]['quantities'][$column->id];
                        }
                        else
                        {
                            $quantityPerUnit = 0;
                        }

                        $totalQty += $quantityPerUnit * $column['quantity'];

                    }

                    $rationalizedNotListed['rationalized_grand_total_quantity']     = $totalQty;
                    $rationalizedNotListed['id']                                    = $billItem['id'] . '-' . $rationalizedCount;
                    $rationalizedNotListed['description']                           = $rationalizedItems[$billItem['id']]['description'];
                    $rationalizedNotListed['uom_id']                                = $rationalizedItems[$billItem['id']]['uom_id'];
                    $rationalizedNotListed['uom_symbol']                            = $rationalizedItems[$billItem['id']]['uom_symbol'];
                    $rationalizedNotListed['level']                                 = $rationalizedNotListed['level'] + 1;
                    $rationalizedNotListed['rationalized_rate-value']               = $rationalizedItems[$billItem['id']]['rate'];
                    $rationalizedNotListed['rationalized_grand_total_after_markup'] = $rationalizedItems[$billItem['id']]['grand_total'];

                }
            }
            else
            {
                $billItem['rationalized_grand_total_quantity'] = $billItem['grand_total_quantity'];
            }

            array_push($items, $billItem);

            if ( $rationalizedNotListed )
            {
                array_push($items, $rationalizedNotListed);
            }

            unset( $billItem );
        }

        unset( $billItems );

        $defaultLastRow = array(
            'id'                                    => Constants::GRID_LAST_ROW,
            'bill_ref'                              => '',
            'description'                           => '',
            'type'                                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                                => '-1',
            'uom_symbol'                            => '',
            'relation_id'                           => $element->id,
            'level'                                 => 0,
            'linked'                                => false,
            'rate_after_markup'                     => 0,
            'grand_total_after_markup'              => 0,
            'rationalized_grand_total_after_markup' => 0,
            'rationalized_rate-value'               => 0,
            '_csrf_token'                           => $form->getCSRFToken()
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeBillItemRateUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id'))
        );

        $item       = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));
        $fieldName  = $request->getParameter('attr_name');
        $fieldValue = trim($request->getParameter('val'));
        $rowData    = array();

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldAttr        = explode('-', $fieldName);
            $isContractorRate = false;

            if ( count($fieldAttr) > 1 )
            {
                if ( $fieldAttr[1] == 'rate' )
                {
                    $project          = Doctrine_Core::getTable('ProjectStructure')->find($item->Element->ProjectStructure->root_id);
                    $isContractorRate = true;

                    $this->forward404Unless(
                        $contractor = Doctrine_Core::getTable('Company')->find($fieldAttr[0]) and
                        $tenderCompany = TenderCompanyTable::getByProjectIdAndCompanyId($project->id, $contractor->id)
                    );

                    if ( !$contractorRate = $tenderCompany->getBillItemRateByBillItemId($item->id) )
                    {
                        $contractorRate                    = new TenderBillItemRate();
                        $contractorRate->tender_company_id = $tenderCompany;
                        $contractorRate->bill_item_id      = $item->id;
                        $contractorRate->save();
                    }
                }
            }

            if ( $isContractorRate )
            {
                $rate                 = (double) $fieldValue;
                $contractorRate->rate = number_format($rate, 2, '.', '');

                $contractorRate->save($con);
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            if ( $isContractorRate )
            {
                $rowData['id']                                          = $item->id;
                $rowData[$contractor->id . '-rate-value']               = $contractorRate->rate;
                $rowData[$contractor->id . '-grand_total_after_markup'] = $contractorRate->grand_total;
            }
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeLumpSumPercentageForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $tenderCompany = DoctrineQuery::create()->select('tc.id, tc.project_structure_id, tc.company_id')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $project->id)
            ->andWhere('tc.company_id = ?', $company->id)
            ->fetchOne();

        $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));

        if ( !$billItemRate = $tenderCompany->getBillItemRateByBillItemId($billItem->id) )
        {
            $billItemRate                    = new TenderBillItemRate();
            $billItemRate->tender_company_id = $tenderCompany->id;
            $billItemRate->bill_item_id      = $billItem->id;
            $billItemRate->save();
        }

        $form = new TenderBillItemLumpSumPercentageForm($billItemRate->LumpSumPercentage);

        $data = array(
            'tender_bill_item_lump_sum_percentage[tender_bill_item_rate_id]' => $form->getObject()->id,
            'tender_bill_item_lump_sum_percentage[rate]'                     => number_format($form->getObject()->rate, 2, '.', ''),
            'tender_bill_item_lump_sum_percentage[percentage]'               => number_format($form->getObject()->percentage, 2, '.', ''),
            'tender_bill_item_lump_sum_percentage[amount]'                   => number_format($form->getObject()->amount, 2, '.', ''),
            'tender_bill_item_lump_sum_percentage[_csrf_token]'              => $form->getCSRFToken()
        );

        return $this->renderJson($data);
    }

    public function executeLumpSumPercentageUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $tenderCompany = DoctrineQuery::create()->select('tc.id, tc.project_structure_id, tc.company_id')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $project->id)
            ->andWhere('tc.company_id = ?', $company->id)
            ->fetchOne();

        $billItem     = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));
        $billItemRate = $tenderCompany->getBillItemRateByBillItemId($billItem->id);

        $form = new TenderBillItemLumpSumPercentageForm($billItemRate->LumpSumPercentage);

        if ( $this->isFormValid($request, $form) )
        {
            $lumpSumPercentage = $form->save();

            $rowData['id']                                       = $billItem->id;
            $rowData[$company->id . '-rate-value']               = $lumpSumPercentage->TenderBillItemRate->rate;
            $rowData[$company->id . '-grand_total_after_markup'] = $lumpSumPercentage->TenderBillItemRate->grand_total;

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'data' => $rowData ));
    }

    public function executePrimeCostRateForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $tenderCompany = DoctrineQuery::create()->select('tc.id, tc.project_structure_id, tc.company_id')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $project->id)
            ->andWhere('tc.company_id = ?', $company->id)
            ->fetchOne();

        $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));

        if ( !$billItemRate = $tenderCompany->getBillItemRateByBillItemId($billItem->id) )
        {
            $billItemRate                    = new TenderBillItemRate();
            $billItemRate->tender_company_id = $tenderCompany->id;
            $billItemRate->bill_item_id      = $billItem->id;
            $billItemRate->save();
        }

        $form = new TenderBillItemPrimeCostRateForm($billItemRate->PrimeCostRate);

        $data = array(
            'tender_bill_item_prime_cost_rate[supply_rate]'             => number_format($form->getObject()->supply_rate, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[wastage_percentage]'      => number_format($form->getObject()->wastage_percentage, 3, '.', ''),
            'tender_bill_item_prime_cost_rate[wastage_amount]'          => number_format($form->getObject()->wastage_amount, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[labour_for_installation]' => number_format($form->getObject()->labour_for_installation, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[other_cost]'              => number_format($form->getObject()->other_cost, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[profit_percentage]'       => number_format($form->getObject()->profit_percentage, 3, '.', ''),
            'tender_bill_item_prime_cost_rate[profit_amount]'           => number_format($form->getObject()->profit_amount, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[total]'                   => number_format($form->getObject()->total, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[_csrf_token]'             => $form->getCSRFToken()
        );

        return $this->renderJson($data);
    }

    public function executePrimeCostRateUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $tenderCompany = DoctrineQuery::create()->select('tc.id, tc.project_structure_id, tc.company_id')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $project->id)
            ->andWhere('tc.company_id = ?', $company->id)
            ->fetchOne();

        $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));

        if ( !$billItemRate = $tenderCompany->getBillItemRateByBillItemId($billItem->id) )
        {
            $billItemRate                    = new TenderBillItemRate();
            $billItemRate->tender_company_id = $tenderCompany->id;
            $billItemRate->bill_item_id      = $billItem->id;
            $billItemRate->save();
        }

        $form = new TenderBillItemPrimeCostRateForm($billItemRate->PrimeCostRate);

        if ( $this->isFormValid($request, $form) )
        {
            $primeCostRate = $form->save();

            $rowData['id']                                       = $billItem->id;
            $rowData[$company->id . '-rate-value']               = $primeCostRate->TenderBillItemRate->rate;
            $rowData[$company->id . '-grand_total_after_markup'] = $primeCostRate->TenderBillItemRate->grand_total;

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'data' => $rowData ));
    }

    public function executeGetTenderInfo(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $pdo = $project->getTable()->getConnection()->getDbh();

        $form = new BaseForm();

        $tenderSetting = $project->TenderSetting->toArray();

        $data['tender_setting'] = $tenderSetting;

        switch ($tenderSetting['contractor_sort_by'])
        {
            case TenderSetting::SORT_CONTRACTOR_NAME_ASC:
                $sqlOrder = "c.name ASC";
                break;
            case TenderSetting::SORT_CONTRACTOR_NAME_DESC:
                $sqlOrder = "c.name DESC";
                break;
            case TenderSetting::SORT_CONTRACTOR_HIGHEST_LOWEST:
                $sqlOrder = "total DESC";
                break;
            case TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST:
                $sqlOrder = "total ASC";
                break;
            default:
                throw new Exception('invalid sort option');
        }

        $awardedCompanyId = $tenderSetting['awarded_company_id'] > 0 ? $tenderSetting['awarded_company_id'] : - 1;

        $sql = "SELECT c.id, c.name, xref.show, COALESCE(SUM(r.grand_total), 0) AS total
        FROM " . CompanyTable::getInstance()->getTableName() . " c
        JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
        LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
        WHERE xref.project_structure_id = " . $project->id . "
        AND c.id <> " . $awardedCompanyId . " AND xref.show IS TRUE
        AND c.deleted_at IS NULL GROUP BY c.id, xref.show ORDER BY " . $sqlOrder;

        $stmt = $pdo->prepare($sql);

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $companies = array();

        if ( $tenderSetting['awarded_company_id'] > 0 )
        {
            $awardedCompany = $project->TenderSetting->AwardedCompany;

            $companySetting = DoctrineQuery::create()->select('s.id, s.show')
                ->from('TenderCompany s')
                ->where('s.company_id = ?', $awardedCompany->id)
                ->andWhere('s.project_structure_id = ?', $project->id)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();

            if ( $companySetting['show'] )
            {
                $company = array(
                    'id'          => $awardedCompany->id,
                    'name'        => $awardedCompany->name,
                    'show'        => $companySetting['show'],
                    '_csrf_token' => $form->getCSRFToken(),
                    'awarded'     => true
                );

                array_push($companies, $company);
                unset( $company );
            }

            unset( $awardedCompany );
        }

        foreach ( $records as $key => $record )
        {
            $record['_csrf_token'] = $form->getCSRFToken();
            $record['awarded']     = false;

            array_push($companies, $record);
            unset( $records[$key], $record );
        }

        $data['tender_companies'] = $companies;

        return $this->renderJson($data);
    }

}