<?php

/**
 * subPackageExportExcelReporting actions.
 *
 * @package    buildspace
 * @subpackage subPackageExportExcelReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subPackageExportExcelReportingActions extends BaseActions {

    public function executeExportExcelBillSummarySelectedTenderer(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        if( empty ( $subPackage->selected_company_id ) )
        {
            throw new InvalidArgumentException('Please select a company before re-printing again.');
        }

        $selectedSubCon = Doctrine_Core::getTable('Company')->find($subPackage->selected_company_id);
        $billIds = json_decode($request->getParameter('selectedRows'), true);
        $billSplFixedArray = new SplFixedArray(1);
        $totalEstimateAmount = 0;
        $totalSelectedContractorAmount = 0;

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        if( ! empty( $billIds ) )
        {
            $billArray = SubPackageTable::getBillsBySubPackageAndBillIds($subPackage, $billIds);

            $contractorTotals = SubPackageBillItemRateTable::getBillContractorTotals($subPackage, $billIds);
            $estimateTotals = SubPackageBillItemRateTable::getBillEstimateTotals($subPackage, $billIds);

            foreach($estimateTotals as $billId => $billTotal)
            {
                $totalEstimateAmount += $billTotal;
            }

            $billSplFixedArray = new SplFixedArray(count($billArray));//plus 1 for last empty row in grid

            $count = 0;

            foreach($billArray as $billId => $bill)
            {
                $bill = array(
                    'id'         => $billId,
                    'title'      => $bill['title'],
                    'est_amount' => $estimateTotals[ $billId ]
                );

                foreach($contractorTotals as $contractorId => $contractorBillTotals)
                {
                    if( array_key_exists($billId, $contractorBillTotals) )
                    {
                        $amount = $contractorBillTotals[ $billId ];

                        $bill[ 'total_amount-' . $contractorId ] = $amount;
                        $bill[ 'difference_amount-' . $contractorId ] = $amount - $bill['est_amount'];
                        $bill[ 'difference_percentage-' . $contractorId ] = 0;

                        if( $bill['est_amount'] != 0 )
                        {
                            $bill[ 'difference_percentage-' . $contractorId ] = Utilities::prelimRounding(Utilities::percent($bill[ 'difference_amount-' . $contractorId ], $bill['est_amount']));
                        }

                        if( $contractorId == $selectedSubCon->id )
                        {
                            $totalSelectedContractorAmount += $amount;
                        }
                    }

                }

                $billSplFixedArray[ $count ] = $bill;

                unset( $bill );

                $count++;
            }
        }

        $reportGenerator = new sfSubPackageBillSummarySelectedTendererPageGenerator($subPackage, $billSplFixedArray, $descriptionFormat);
        $reportGenerator->setSelectedSubCon($selectedSubCon);

        $pages = $reportGenerator->generatePages();
        $pageCount = 1;

        $excelGenerator = new sfSubPackageReportBillSummarySelectedTendererExcelExporterGenerator($subPackage->ProjectStructure, $printingPageTitle, $reportGenerator->getPrintSettings());

        if( ! ( $pages instanceof SplFixedArray ) )
        {
            $excelGenerator->generateExcelFile();

            return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
        }

        $excelGenerator->setTotalPage(count($pages) - 1);
        $excelGenerator->setCurrency($reportGenerator->getCurrency());
        $excelGenerator->setSelectedSubCon($selectedSubCon);

        foreach($pages as $page)
        {
            if( empty( $page ) )
            {
                continue;
            }

            $excelGenerator->setLastPage(( $pageCount == $pages->count() - 1 ) ? true : false);

            $excelGenerator->process($pages, false, $printingPageTitle, null, $subPackage->ProjectStructure->title, $printNoCents, null);

            $pageCount++;
        }

        $excelGenerator->generateExcelFile();

        return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
    }

    public function executeExportExcelBillSummaryForAllTenderer(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $billIds = json_decode($request->getParameter('selectedRows'), true);
        $billArray = array();
        $newSubCons = array();
        $contractorTotals = array();
        $totalEstimateAmount = 0;
        $billSplFixedArray = new SplFixedArray(1);

        $sortBy = $request->getParameter('sortingType');
        $selectedCompanyId = $subPackage->selected_company_id > 0 ? $subPackage->selected_company_id : -1;
        $selectedSubCon = Doctrine_Core::getTable('Company')->find($selectedCompanyId);
        $subCons = SubPackageCompanyTable::getSubConCompany($subPackage, $selectedCompanyId, $sortBy);

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        if( $selectedSubCon )
        {
            $arraySelectedSubCon = $selectedSubCon->toArray();
            $arraySelectedSubCon['selected'] = true;

            array_push($newSubCons, $arraySelectedSubCon);
        }

        array_walk($subCons, function ($subCon) use (&$newSubCons)
        {
            $newSubCons[] = $subCon;
        });

        unset( $selectedSubCon, $subCons );

        if( ! empty( $billIds ) )
        {
            $billArray = SubPackageTable::getBillsBySubPackageAndBillIds($subPackage, $billIds);

            $contractorTotals = SubPackageBillItemRateTable::getBillContractorTotals($subPackage, $billIds);
            $estimateTotals = SubPackageBillItemRateTable::getBillEstimateTotals($subPackage, $billIds);

            foreach($estimateTotals as $billId => $billTotal)
            {
                $totalEstimateAmount += $billTotal;
            }

            $billSplFixedArray = new SplFixedArray(count($billArray));//plus 1 for last empty row in grid

            $count = 0;

            foreach($billArray as $billId => $bill)
            {
                $bill = array(
                    'id'         => $billId,
                    'title'      => $bill['title'],
                    'est_amount' => $estimateTotals[ $billId ],
                    'type'       => $bill['type'],
                );

                $billSplFixedArray[ $count ] = $bill;

                unset( $bill );

                $count++;
            }
        }

        unset( $billArray );

        $reportGenerator = new sfSubPackageBillSummaryAllTendererPageGenerator($subPackage, $billSplFixedArray, $descriptionFormat);
        $pages = $reportGenerator->generatePages();

        $excelGenerator = new sfSubPackageReportBillSummaryAllTendererExcelExporterGenerator($subPackage->ProjectStructure, $printingPageTitle, $reportGenerator->getPrintSettings());

        if( ! ( $pages instanceof SplFixedArray ) )
        {
            $excelGenerator->generateExcelFile();

            return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
        }

        $pageCount = 1;

        $excelGenerator->setSubCons($newSubCons);
        $excelGenerator->setSubConBillTotal($contractorTotals);
        $excelGenerator->setTotalPage(count($pages) - 1);
        $excelGenerator->setCurrency($reportGenerator->getCurrency());

        foreach($pages as $page)
        {
            if( empty( $page ) )
            {
                continue;
            }

            $excelGenerator->setLastPage(( $pageCount == $pages->count() - 1 ) ? true : false);

            $excelGenerator->process($pages, false, $printingPageTitle, null, $subPackage->ProjectStructure->title, $printNoCents, null);

            $pageCount++;
        }

        $excelGenerator->generateExcelFile();

        return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
    }

    public function executeExportExcelItemRateForSelectedTenderer(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
        );

        session_write_close();

        $selectedCompanyId = $subPackage->selected_company_id > 0 ? $subPackage->selected_company_id : -1;
        $selectedSubCon = Doctrine_Core::getTable('Company')->find($selectedCompanyId);

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $billItemIds = json_decode($request->getParameter('selectedRows'), true);
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
        $projectMainInfo = $project->MainInformation;
        $isPostContract = ( $projectMainInfo->status == ProjectMainInformation::STATUS_POSTCONTRACT );
        $elementIds = array();
        $temporarilyBillItemsContainer = array();
        $quantityPerTypes = array();
        $elements = array();
        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        if( ! empty( $billItemIds ) )
        {
            $records = SubPackageTable::getRatesBySubPackageAndBillAndItemIds($subPackage, $bill, $billItemIds);

            foreach($records as $record)
            {
                $elementIds[ $record['element_id'] ] = $record['element_id'];
            }

            $subConRates = SubPackageBillItemRateTable::getSubConRatesBySubPackageAndItemIds($subPackage, $billItemIds);

            $billRefSelector = 'p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char';
            $postContractJoinTable = null;

            if( $isPostContract )
            {
                $billRefSelector = 'pc.bill_ref_element_no, pc.bill_ref_page_no, pc.bill_ref_char';
                $postContractJoinTable = 'JOIN ' . PostContractBillItemRateTable::getInstance()->getTableName() . " pc ON (pc.bill_item_id = p.id)";
            }

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, uom.symbol AS uom_symbol, p.grand_total, p.grand_total_quantity, p.level, p.priority, p.lft, {$billRefSelector}
			FROM " . BillItemTable::getInstance()->getTableName() . " c
			JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt {$postContractJoinTable}
			LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
			WHERE c.root_id = p.root_id AND c.type != " . BillItem::TYPE_ITEM_NOT_LISTED . "
			AND c.id IN (" . implode(',', $billItemIds) . ")
			AND c.project_revision_deleted_at IS NULL
			AND c.deleted_at IS NULL AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
			ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

            $stmt->execute();
            $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $elements = BillElementTable::getElementsByElementIds($elementIds);

            if( ! empty( $records ) )
            {
                // will start to construct query to get quantity per type
                $getQuantityPerTypes = BillItemTypeReferenceFormulatedColumnTable::getQuantityPerType($records);

                foreach($getQuantityPerTypes as $quantityPerType)
                {
                    $quantityPerTypes[ $quantityPerType['bill_item_id'] ][ $quantityPerType['bill_column_setting_id'] ] = $quantityPerType['value'];

                    unset( $quantityPerType );
                }

                unset( $getQuantityPerTypes );

                // process all the bill item(s) before passing to be merge with element level
                foreach($records as $recordKey => &$record)
                {
                    foreach($billItems as $key => &$billItem)
                    {
                        $billItems[ $key ]['bill_ref'] = BillItemTable::generateBillRef($pageNoPrefix, $billItems[ $key ]['bill_ref_element_no'], $billItems[ $key ]['bill_ref_page_no'], $billItems[ $key ]['bill_ref_char']);

                        if( ! array_key_exists($record['bill_column_setting_id'] . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value', $billItem)
                            && ! array_key_exists($record['bill_column_setting_id'] . '-total_per_unit', $billItem)
                        )
                        {
                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value' ] = 0;
                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-total_per_unit' ] = 0;
                        }

                        if( ! array_key_exists('rate-value', $billItem) )
                        {
                            $billItems[ $key ]['rate-value'] = 0;
                            $billItems[ $key ]['type'] = (string)$billItems[ $key ]['type'];
                        }

                        if( $record['bill_item_id'] == $billItems[ $key ]['id'] )
                        {
                            $quantityPerType = isset( $quantityPerTypes[ $billItem['id'] ][ $record['bill_column_setting_id'] ] ) ? $quantityPerTypes[ $billItem['id'] ][ $record['bill_column_setting_id'] ] : 0;

                            $billItems[ $key ]['rate-value'] = $record['final_value'];

                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value' ] = $quantityPerType;
                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-total_per_unit' ] = $record['final_value'] * $quantityPerType;
                        }

                        foreach($subConRates as $subConRate)
                        {
                            if( ! array_key_exists($subConRate['company_id'] . '-rate-value', $billItems[ $key ]) and $billItems[ $key ]['type'] != BillItem::TYPE_HEADER and $billItems[ $key ]['type'] != BillItem::TYPE_HEADER_N and $billItems[ $key ]['type'] != BillItem::TYPE_NOID )
                            {
                                $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] = 0;
                            }

                            if( $subConRate['bill_item_id'] == $billItems[ $key ]['id'] )
                            {
                                $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] = $subConRate['rate'];
                            }

                            $rateValue = isset( $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] ) ? $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] : 0;

                            $billItems[ $key ][ $subConRate['company_id'] . '-difference_amount' ] = $rateValue - $billItems[ $key ]['rate-value'];
                            $billItems[ $key ][ $subConRate['company_id'] . '-difference_percentage' ] = 0;

                            if( $rateValue != 0 )
                            {
                                $billItems[ $key ][ $subConRate['company_id'] . '-difference_percentage' ] = Utilities::prelimRounding(Utilities::percent($billItems[ $key ][ $subConRate['company_id'] . '-difference_amount' ], $billItems[ $key ]['rate-value']));
                            }
                        }

                        $temporarilyBillItemsContainer[ $billItems[ $key ]['element_id'] ][ $billItems[ $key ]['id'] ] = $billItem;

                        unset( $billItem );
                    }

                    unset( $record, $records[ $recordKey ] );
                }

                unset( $records );
            }
        }

        $reportGenerator = new sfSubPackageItemRateSelectedTendererPageGenerator($subPackage, $bill, $elements, $temporarilyBillItemsContainer, $descriptionFormat);
        $reportGenerator->setSubCon($selectedSubCon);

        $pages = $reportGenerator->generatePages();
        $pageCount = 1;

        $excelGenerator = new sfSubPackageReportItemSummarySelectedTendererExcelExporterGenerator($subPackage->ProjectStructure, $printingPageTitle, $reportGenerator->getPrintSettings());

        $excelGenerator->setSelectedSubCon($selectedSubCon);
        $excelGenerator->setCurrency($reportGenerator->getCurrency());
        $excelGenerator->setTotalPage($reportGenerator->totalPage);

        foreach($pages as $page)
        {
            $excelGenerator->setBill($page['description']);

            for($i = 1; $i <= $page['item_pages']->count(); $i++)
            {
                if( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $excelGenerator->setLastPage(( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false);

                    $excelGenerator->process($page['item_pages'], false, $printingPageTitle, null, $subPackage->ProjectStructure->title, $printNoCents, null);

                    $page['item_pages']->offsetUnset($i);

                    $pageCount++;

                    unset( $newPage );
                }
            }
        }

        $excelGenerator->generateExcelFile();

        return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
    }

    public function executeExportExcelItemRateAndTotalForAllTenderer(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
        );

        session_write_close();

        $billItemIds = json_decode($request->getParameter('selectedRows'), true);
        $projectMainInfo = $project->MainInformation;
        $isPostContract = ( $projectMainInfo->status == ProjectMainInformation::STATUS_POSTCONTRACT );
        $elements = array();
        $temporarilyBillItemsContainer = array();
        $newSubCons = array();

        $sortBy = $request->getParameter('sortingType');
        $selectedCompanyId = $subPackage->selected_company_id > 0 ? $subPackage->selected_company_id : -1;
        $selectedSubCon = Doctrine_Core::getTable('Company')->find($selectedCompanyId);
        $subCons = SubPackageCompanyTable::getSubConCompany($subPackage, $selectedCompanyId, $sortBy);

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        if( $selectedSubCon )
        {
            $arraySelectedSubCon = $selectedSubCon->toArray();
            $arraySelectedSubCon['selected'] = true;

            array_push($newSubCons, $arraySelectedSubCon);
        }

        array_walk($subCons, function ($subCon) use (&$newSubCons)
        {
            $newSubCons[] = $subCon;
        });

        unset( $selectedSubCon, $subCons );

        $reportGenerator = new sfSubPackageItemRateAllTendererPageGenerator($subPackage, $bill, $elements, $temporarilyBillItemsContainer, $descriptionFormat, $billItemIds);
        $reportGenerator->setSubCons($newSubCons);

        $pages = $reportGenerator->generatePages();
        $pageCount = 1;

        $excelGenerator = new sfSubPackageReportItemAllTendererExcelExporterGenerator($subPackage->ProjectStructure, $printingPageTitle, $reportGenerator->getPrintSettings());

        $itemQuantities = SubPackageBillItemRateTable::getItemQuantities($subPackage, $bill, $billItemIds);
        $excelGenerator->setParameters($reportGenerator->estimateElementTotal, $newSubCons, $reportGenerator->contractorElementTotals, $reportGenerator->getContractorRates(), $itemQuantities);

        $excelGenerator->setTotalPage($reportGenerator->totalPage);
        $excelGenerator->setCurrency($reportGenerator->getCurrency());

        foreach($pages as $elementId => $page)
        {
            $excelGenerator->setBill($page['description']);

            if( $page['item_pages'] instanceof SplFixedArray )
            {
                $excelGenerator->setCurrentElementId($elementId);

                $excelGenerator->process($page['item_pages'], false, $printingPageTitle, null, $subPackage->ProjectStructure->title, $printNoCents, null);

                $pageCount++;
            }
        }

        $excelGenerator->generateExcelFile();

        return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
    }

}