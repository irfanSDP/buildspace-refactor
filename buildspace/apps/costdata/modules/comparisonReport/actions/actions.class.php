<?php

/**
 * comparisonReport actions.
 *
 * @package    buildspace
 * @subpackage comparisonReport
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class comparisonReportActions extends BaseActions
{
    public function executeExport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        switch($request->getParameter('level'))
        {
            case MasterCostDataItem::ITEM_LEVEL_WORK_CATEGORY:
                $reportGenerator = new sfCostDataWorkCategoryComparisonExcelReportGenerator($costData);
                break;
            case MasterCostDataItem::ITEM_LEVEL_ELEMENT:
                $reportGenerator = new sfCostDataElementComparisonExcelReportGenerator($costData);
                break;
            default:
                $reportGenerator = new sfCostDataProjectOverallCostingComparisonExcelReportGenerator($costData);
        }

        $reportGenerator->setParameters($request->getParameter('selected_ids') ?? array(), $request->getParameter('parent_id') ?? 0);

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }

    public function executeOverallProjectCostingPreview(sfWebRequest $request)
    {
        $this->forward404Unless(
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id'))
        );

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $masterCostData = $costData->MasterCostData;

        $selectedIds = $request->hasParameter('selected_ids') ? explode(',', $request->getParameter('selected_ids')) : array();

        $records = CostDataItemTable::getItemList($costData, null);

        $masterItemIds = array_column($records, 'id');

        $costDatas = array($costData);

        $costDataIds = array_merge([$costData->id], $selectedIds);

        if(!empty($selectedIds))
        {
            $costDataCollection = DoctrineQuery::create()
                ->select('*')
                ->from('CostData')
                ->whereIn('id', $selectedIds)
                ->orderBy('created_at ASC')
                ->execute();

            foreach($costDataCollection as $costDataObject)
            {
                $costDatas[] = $costDataObject;
            }
        }

        $itemValues                             = array();
        $itemSums                               = array();
        $provisionalSums                        = array();
        $totalSums                              = array();
        $overallProjectCostingParticularSummary = array();

        foreach($costDatas as $costData)
        {
            $itemValues[$costData->id]      = CostDataItemTable::getOverallCostingItemValues($costData, $masterItemIds);
            $itemSums[$costData->id]        = CostDataItemTable::getItemSum($costData, $masterItemIds);
            $provisionalSums[$costData->id] = CostDataProvisionalSumItemTable::getTotalSum($costData);

            $totalSums[$costData->id] = array(
                'approved_total'        => $itemSums[$costData->id]['approved_sum'] + $provisionalSums[$costData->id]['approved_sum'],
                'awarded_total'         => $itemSums[$costData->id]['awarded_sum'] + $provisionalSums[$costData->id]['awarded_sum'],
                'adjusted_total'        => $itemSums[$costData->id]['adjusted_sum'] + $provisionalSums[$costData->id]['adjusted_sum'],
                'variation_order_total' => $itemSums[$costData->id]['variation_order_sum'] + $provisionalSums[$costData->id]['variation_order_sum'],
            );

            $overallProjectCostingParticularSummary[$costData->id] = CostDataParticularTable::getOverallProjectCostingSummary($costData);
        }

        $stmt = $pdo->prepare("
            SELECT p.id FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            WHERE cd.id = {$costData->id}
            AND p.is_used_for_cost_comparison = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $costComparisonParticulars = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach($records as $key => $item)
        {
            $records[$key]['type'] = MasterCostData::ITEM_TYPE_STANDARD;

            foreach($costDatas as $costData)
            {
                $records[$key]["{$costData->id}_approved_cost"]        = $itemValues[$costData->id][$item['id']]['approved_cost'];
                $records[$key]["{$costData->id}_approved_percentage"]  = Utilities::percent($itemValues[$costData->id][$item['id']]['approved_cost'], $totalSums[$costData->id]['approved_total']);
                $records[$key]["{$costData->id}_awarded_cost"]         = $itemValues[$costData->id][$item['id']]['awarded_cost'];
                $records[$key]["{$costData->id}_awarded_percentage"]   = Utilities::percent($itemValues[$costData->id][$item['id']]['awarded_cost'], $totalSums[$costData->id]['awarded_total']);
                $records[$key]["{$costData->id}_adjusted_cost"]        = $itemValues[$costData->id][$item['id']]['adjusted_cost'];
                $records[$key]["{$costData->id}_adjusted_percentage"]  = Utilities::percent($itemValues[$costData->id][$item['id']]['adjusted_cost'], $totalSums[$costData->id]['adjusted_total']);
                $records[$key]["{$costData->id}_variation_order_cost"] = $itemValues[$costData->id][$item['id']]['variation_order_cost'];
                $records[$key]["{$costData->id}_remarks"]              = $itemValues[$costData->id][$item['id']]['remarks'];

                foreach($costComparisonParticulars as $particularId)
                {
                    $records[$key]["{$costData->id}_approved_{$particularId}"] = $itemValues[$costData->id][$item['id']]['approved-'.$particularId];
                    $records[$key]["{$costData->id}_awarded_{$particularId}"]  = $itemValues[$costData->id][$item['id']]['awarded-'.$particularId];
                    $records[$key]["{$costData->id}_adjusted_{$particularId}"] = $itemValues[$costData->id][$item['id']]['adjusted-'.$particularId];
                }
            }
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'type'        => MasterCostData::ITEM_TYPE_STANDARD,
        ));

        $provisionalSumRow = array(
            'id'          => 'provisional_sum',
            'description' => 'Provisional Sum',
            'type'        => MasterCostData::ITEM_TYPE_PROVISIONAL_SUM,
        );

        foreach($costDatas as $costData)
        {
            $nodelessItemRemarks = CostDataNodelessItemRemarkTable::getRemarks($costData->id);

            $provisionalSumRow["{$costData->id}_approved_cost"]        = $provisionalSums[$costData->id]['approved_sum'];
            $provisionalSumRow["{$costData->id}_approved_percentage"]  = Utilities::percent($provisionalSums[$costData->id]['approved_sum'], $totalSums[$costData->id]['approved_total']);
            $provisionalSumRow["{$costData->id}_awarded_cost"]         = $provisionalSums[$costData->id]['awarded_sum'];
            $provisionalSumRow["{$costData->id}_awarded_percentage"]   = Utilities::percent($provisionalSums[$costData->id]['awarded_sum'], $totalSums[$costData->id]['awarded_total']);
            $provisionalSumRow["{$costData->id}_adjusted_cost"]        = $provisionalSums[$costData->id]['adjusted_sum'];
            $provisionalSumRow["{$costData->id}_adjusted_percentage"]  = Utilities::percent($provisionalSums[$costData->id]['adjusted_sum'], $totalSums[$costData->id]['adjusted_total']);
            $provisionalSumRow["{$costData->id}_variation_order_cost"] = $provisionalSums[$costData->id]['variation_order_sum'];
            $provisionalSumRow["{$costData->id}_remarks"]              = $nodelessItemRemarks[MasterCostData::ITEM_TYPE_PROVISIONAL_SUM];
        }

        array_unshift($records, $provisionalSumRow);

        $totalRow = array(
            'id'          => 'total',
            'description' => 'Total',
            'type'        => 'summary',
        );

        foreach($costDatas as $costData)
        {
            $totalRow["{$costData->id}_approved_cost"]        = $totalSums[$costData->id]['approved_total'];
            $totalRow["{$costData->id}_approved_percentage"]  = $totalSums[$costData->id]['approved_total'] != 0 ? 100 : null;
            $totalRow["{$costData->id}_awarded_cost"]         = $totalSums[$costData->id]['awarded_total'];
            $totalRow["{$costData->id}_awarded_percentage"]   = $totalSums[$costData->id]['awarded_total'] != 0 ? 100 : null;
            $totalRow["{$costData->id}_variation_order_cost"] = $totalSums[$costData->id]['variation_order_total'];
            $totalRow["{$costData->id}_adjusted_cost"]        = $totalSums[$costData->id]['adjusted_total'];
            $totalRow["{$costData->id}_adjusted_percentage"]  = $totalSums[$costData->id]['adjusted_total'] != 0 ? 100 : null;

            foreach($costComparisonParticulars as $particularId)
            {
                $totalRow["{$costData->id}_approved_{$particularId}"] = array_sum(array_column($records, "{$costData->id}_approved_{$particularId}"));
                $totalRow["{$costData->id}_awarded_{$particularId}"] = array_sum(array_column($records, "{$costData->id}_awarded_{$particularId}"));
                $totalRow["{$costData->id}_adjusted_{$particularId}"] = array_sum(array_column($records, "{$costData->id}_adjusted_{$particularId}"));
            }
        }

        $records[] = $totalRow;

        $stmt = $pdo->prepare("
            SELECT p.id, p.description, p.summary_description
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            WHERE p.master_cost_data_id = {$masterCostData->id}
            AND p.is_summary_displayed = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $summaryParticulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT p.id, cd.id as cost_data_id, COALESCE(cdp.value, 0) AS value 
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            WHERE p.master_cost_data_id = {$masterCostData->id}
            AND cd.id IN (".implode(',', $costDataIds).")
            AND p.is_summary_displayed = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $particularValueRecords = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

        $particularValues = [];

        foreach($particularValueRecords as $particularId => $costDataValues)
        {
            $particularValues[$particularId] = [];

            foreach($costDataValues as $costDataValue)
            {
                $particularValues[$particularId][$costDataValue['cost_data_id']] = $costDataValue['value'];
            }
        }

        foreach($summaryParticulars as $particular)
        {
            $particularSummaryRow = [
                'id'          => 'particular-'.$particular['id'],
                'description' => empty($particular['summary_description']) ? "Total Cost/{$particular['description']}" : $particular['summary_description'],
                'type'        => 'summary',
            ];

            foreach($costDatas as $costData)
            {
                $particularValue = $particularValues[$particular['id']][$costData->id];

                $particularSummaryRow["{$costData->id}_approved_cost"]       = Utilities::divide($overallProjectCostingParticularSummary[$costData->id][$particular['id']]['approved_cost'], $particularValue);
                $particularSummaryRow["{$costData->id}_approved_percentage"] = null;
                $particularSummaryRow["{$costData->id}_awarded_cost"]        = Utilities::divide($overallProjectCostingParticularSummary[$costData->id][$particular['id']]['awarded_cost'], $particularValue);
                $particularSummaryRow["{$costData->id}_awarded_percentage"]  = null;
                $particularSummaryRow["{$costData->id}_adjusted_cost"]       = Utilities::divide($overallProjectCostingParticularSummary[$costData->id][$particular['id']]['adjusted_cost'], $particularValue);
                $particularSummaryRow["{$costData->id}_adjusted_percentage"] = null;
                $particularSummaryRow["{$costData->id}_variation_order_cost"] = Utilities::divide($overallProjectCostingParticularSummary[$costData->id][$particular['id']]['variation_order_cost'], $particularValue);
            }

            array_push($records, $particularSummaryRow);
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeWorkCategoryPreview(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterProjectCostingItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('parent_id'))
        );

        $masterCostData = $costData->MasterCostData;

        $selectedIds = $request->hasParameter('selected_ids') ? explode(',', $request->getParameter('selected_ids')) : array();

        $costDataIds = array_merge([$costData->id], $selectedIds);

        $items = CostDataItemTable::getItemList($costData, $masterProjectCostingItem->id);

        $masterItemIds = array_column($items, 'id');

        $costDatas = array($costData);

        if(!empty($selectedIds))
        {
            $costDataCollection = DoctrineQuery::create()
                ->select('*')
                ->from('CostData')
                ->whereIn('id', $selectedIds)
                ->orderBy('created_at ASC')
                ->execute();

            foreach($costDataCollection as $costDataObject)
            {
                $costDatas[] = $costDataObject;
            }
        }

        $itemValues = array();
        $itemSums = array();

        foreach($costDatas as $costData)
        {
            $itemValues[$costData->id] = CostDataItemTable::getWorkCategoryValues($costData, $masterItemIds);
            $itemSums[$costData->id]   = CostDataItemTable::getItemSum($costData, $masterItemIds);
        }

        $records = array();

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("
            SELECT p.id, p.description, p.summary_description
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . MasterCostDataItemParticularTable::getInstance()->getTableName() . " ip on ip.master_cost_data_particular_id = p.id
            WHERE p.master_cost_data_id = {$masterCostData->id}
            AND ip.master_cost_data_item_id = {$masterProjectCostingItem->id}
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $costComparisonParticulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT p.id, cd.id as cost_data_id, COALESCE(cdp.value, 0) AS value 
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            JOIN " . MasterCostDataItemParticularTable::getInstance()->getTableName() . " ip on ip.master_cost_data_particular_id = p.id
            WHERE p.master_cost_data_id = {$masterCostData->id}
            AND cd.id IN (".implode(',', $costDataIds).")
            AND ip.master_cost_data_item_id = {$masterProjectCostingItem->id}
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $particularValueRecords = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

        $particularValues = [];

        foreach($particularValueRecords as $particularId => $costDataValues)
        {
            $particularValues[$particularId] = [];

            foreach($costDataValues as $costDataValue)
            {
                $particularValues[$particularId][$costDataValue['cost_data_id']] = $costDataValue['value'];
            }
        }

        foreach($items as $item)
        {
            foreach($costDatas as $costData)
            {
                $item["{$costData->id}_approved_cost"]        = $itemValues[$costData->id][ $item['id'] ]['approved_cost'];
                $item["{$costData->id}_approved_percentage"]  = Utilities::percent($itemValues[$costData->id][$item['id']]['approved_cost'], $itemSums[$costData->id]['approved_sum']);
                $item["{$costData->id}_awarded_cost"]         = $itemValues[$costData->id][ $item['id'] ]['awarded_cost'];
                $item["{$costData->id}_awarded_percentage"]   = Utilities::percent($itemValues[$costData->id][$item['id']]['awarded_cost'], $itemSums[$costData->id]['awarded_sum']);
                $item["{$costData->id}_adjusted_cost"]        = $itemValues[$costData->id][ $item['id'] ]['adjusted_cost'];
                $item["{$costData->id}_adjusted_percentage"]  = Utilities::percent($itemValues[$costData->id][$item['id']]['adjusted_cost'], $itemSums[$costData->id]['adjusted_sum']);
                $item["{$costData->id}_variation_order_cost"] = $itemValues[$costData->id][ $item['id'] ]['variation_order_cost'];
                $item["{$costData->id}_remarks"]              = $itemValues[$costData->id][ $item['id'] ]['remarks'];

                foreach($costComparisonParticulars as $particular)
                {
                    $particularValue = $particularValues[$particular['id']][$costData->id];

                    $item["{$costData->id}_approved_{$particular['id']}"] = Utilities::divide($itemValues[$costData->id][$item['id']]['approved_cost'], $particularValue);
                    $item["{$costData->id}_awarded_{$particular['id']}"] = Utilities::divide($itemValues[$costData->id][$item['id']]['awarded_cost'], $particularValue);
                    $item["{$costData->id}_adjusted_{$particular['id']}"] = Utilities::divide($itemValues[$costData->id][$item['id']]['adjusted_cost'], $particularValue);
                }
            }
            $records[] = $item;
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
        ));

        $summaryRow = array(
            'id'          => 'total',
            'description' => "Total",
            'type'        => "summary",
        );

        foreach($costDatas as $costData)
        {
            $summaryRow["{$costData->id}_uom_symbol"]          = null;
            $summaryRow["{$costData->id}_approved_cost"]       = $approvedCost = $itemSums[$costData->id]['approved_sum'];
            $summaryRow["{$costData->id}_approved_percentage"] = $approvedCost != 0 ? 100 : null;
            $summaryRow["{$costData->id}_awarded_cost"]        = $awardedCost = $itemSums[$costData->id]['awarded_sum'];
            $summaryRow["{$costData->id}_awarded_percentage"]  = $awardedCost != 0 ? 100 : null;
            $summaryRow["{$costData->id}_adjusted_cost"]       = $adjustedCost = $itemSums[$costData->id]['adjusted_sum'];
            $summaryRow["{$costData->id}_adjusted_percentage"] = $adjustedCost != 0 ? 100 : null;
            $summaryRow["{$costData->id}_variation_order_cost"]= $itemSums[$costData->id]['variation_order_sum'];

            foreach($costComparisonParticulars as $particular)
            {
                $summaryRow["{$costData->id}_approved_{$particular['id']}"] = array_sum(array_column($records, "{$costData->id}_approved_{$particular['id']}"));
                $summaryRow["{$costData->id}_awarded_{$particular['id']}"] = array_sum(array_column($records, "{$costData->id}_awarded_{$particular['id']}"));
                $summaryRow["{$costData->id}_adjusted_{$particular['id']}"] = array_sum(array_column($records, "{$costData->id}_adjusted_{$particular['id']}"));
            }
        }

        array_push($records, $summaryRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeElementPreview(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterWorkCategoryItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('parent_id'))
        );

        $masterCostData = $costData->MasterCostData;

        $selectedIds = $request->hasParameter('selected_ids') ? explode(',', $request->getParameter('selected_ids')) : array();

        $costDataIds = array_merge([$costData->id], $selectedIds);

        $items = CostDataItemTable::getItemList($costData, $masterWorkCategoryItem->id);

        $masterItemIds = array_column($items, 'id');

        $costDatas = array($costData);

        if(!empty($selectedIds))
        {
            $costDataCollection = DoctrineQuery::create()
                ->select('*')
                ->from('CostData')
                ->whereIn('id', $selectedIds)
                ->orderBy('created_at ASC')
                ->execute();

            foreach($costDataCollection as $costDataObject)
            {
                $costDatas[] = $costDataObject;
            }
        }

        $itemValues = array();
        $itemSums   = array();

        foreach($costDatas as $costData)
        {
            $itemValues[$costData->id] = CostDataItemTable::getElementValues($costData, $masterWorkCategoryItem, $masterItemIds);
            $itemSums[$costData->id]   = CostDataItemTable::getItemSum($costData, $masterItemIds);
        }

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("
            SELECT p.id, p.description, p.summary_description
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            WHERE p.master_cost_data_id = {$masterCostData->id}
            AND p.is_used_for_cost_comparison = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $summaryParticulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT p.id FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            WHERE cd.id = {$costData->id}
            AND p.is_used_for_cost_comparison = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $costComparisonParticulars = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare("
            SELECT p.id, cd.id as cost_data_id, COALESCE(cdp.value, 0) AS value 
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            WHERE p.master_cost_data_id = {$masterCostData->id}
            AND cd.id IN (".implode(',', $costDataIds).")
            AND p.is_used_for_cost_comparison = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $particularValueRecords = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

        $particularValues = [];

        foreach($particularValueRecords as $particularId => $costDataValues)
        {
            $particularValues[$particularId] = [];

            foreach($costDataValues as $costDataValue)
            {
                $particularValues[$particularId][$costDataValue['cost_data_id']] = $costDataValue['value'];
            }
        }

        $records = array();

        foreach($items as $item)
        {
            foreach($costDatas as $costData)
            {
                $item["{$costData->id}_approved_cost"]        = $itemValues[$costData->id][ $item['id'] ]['approved_cost'];
                $item["{$costData->id}_approved_percentage"]  = $itemValues[$costData->id][ $item['id'] ]['approved_percentage'];
                $item["{$costData->id}_awarded_cost"]         = $itemValues[$costData->id][ $item['id'] ]['awarded_cost'];
                $item["{$costData->id}_awarded_percentage"]   = $itemValues[$costData->id][ $item['id'] ]['awarded_percentage'];
                $item["{$costData->id}_adjusted_cost"]        = $itemValues[$costData->id][ $item['id'] ]['adjusted_cost'];
                $item["{$costData->id}_adjusted_percentage"]  = $itemValues[$costData->id][ $item['id'] ]['adjusted_percentage'];
                $item["{$costData->id}_variation_order_cost"] = $itemValues[$costData->id][ $item['id'] ]['variation_order_cost'];
                $item["{$costData->id}_remarks"]              = $itemValues[$costData->id][ $item['id'] ]['remarks'];

                foreach($costComparisonParticulars as $particularId)
                {
                    $particularValue = $particularValues[$particularId][$costData->id];

                    $item["{$costData->id}_approved_{$particularId}"] = Utilities::divide($itemValues[$costData->id][$item['id']]['approved_cost'], $particularValue);
                    $item["{$costData->id}_awarded_{$particularId}"] = Utilities::divide($itemValues[$costData->id][$item['id']]['awarded_cost'], $particularValue);
                    $item["{$costData->id}_adjusted_{$particularId}"] = Utilities::divide($itemValues[$costData->id][$item['id']]['adjusted_cost'], $particularValue);
                }
            }

            $records[] = $item;
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
        ));

        $summaryRow = array(
            'id'          => 'total',
            'description' => "Total",
            'type'        => "summary",
        );

        foreach($costDatas as $costData)
        {
            $summaryRow["{$costData->id}_uom_symbol"]           = null;
            $summaryRow["{$costData->id}_approved_cost"]        = $approvedCost = $itemSums[$costData->id]['approved_sum'];
            $summaryRow["{$costData->id}_approved_percentage"]  = $approvedCost != 0 ? 100 : null;
            $summaryRow["{$costData->id}_awarded_cost"]         = $awardedCost = $itemSums[$costData->id]['awarded_sum'];
            $summaryRow["{$costData->id}_awarded_percentage"]   = $awardedCost != 0 ? 100 : null;
            $summaryRow["{$costData->id}_adjusted_cost"]        = $adjustedCost = $itemSums[$costData->id]['adjusted_sum'];
            $summaryRow["{$costData->id}_adjusted_percentage"]  = $adjustedCost != 0 ? 100 : null;
            $summaryRow["{$costData->id}_variation_order_cost"] = $itemSums[$costData->id]['variation_order_sum'];

            foreach($summaryParticulars as $particular)
            {
                $summaryRow["{$costData->id}_approved_{$particular['id']}"] = array_sum(array_column($records, "{$costData->id}_approved_{$particular['id']}"));
                $summaryRow["{$costData->id}_awarded_{$particular['id']}"] = array_sum(array_column($records, "{$costData->id}_awarded_{$particular['id']}"));
                $summaryRow["{$costData->id}_adjusted_{$particular['id']}"] = array_sum(array_column($records, "{$costData->id}_adjusted_{$particular['id']}"));
            }
        }

        array_push($records, $summaryRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executePrimeCostRatePreview(sfWebRequest $request)
    {
        $this->forward404Unless(
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id'))
        );

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $selectedIds = $request->hasParameter('selected_ids') ? explode(',', $request->getParameter('selected_ids')) : array();

        $level = $request->getParameter('level');

        $items = CostDataPrimeCostRateTable::getItemList($costData, $request->getParameter('parent_id'));

        $masterItemIds = array_column($items, 'id');

        $costDatas = array($costData);

        if(!empty($selectedIds))
        {
            $costDataCollection = DoctrineQuery::create()
                ->select('*')
                ->from('CostData')
                ->whereIn('id', $selectedIds)
                ->orderBy('created_at ASC')
                ->execute();

            foreach($costDataCollection as $costDataObject)
            {
                $costDatas[] = $costDataObject;
            }
        }

        $itemValues    = array();
        $costDataUnits = array();

        foreach($costDatas as $costData)
        {
            $itemValues[$costData->id]    = CostDataPrimeCostRateTable::getRecordValues($costData, $masterItemIds);
            $costDataUnits[$costData->id] = array_sum(array_column($itemValues[$costData->id], 'units'));
        }

        $records = array();

        foreach($items as $item)
        {
            foreach($costDatas as $costData)
            {
                $item["{$costData->id}_units"]          = $itemValues[$costData->id][ $item['id'] ]['units'];
                $item["{$costData->id}_approved_value"] = $itemValues[$costData->id][ $item['id'] ]['approved_value'];
                $item["{$costData->id}_approved_total"] = $itemValues[$costData->id][ $item['id'] ]['approved_total'];
                $item["{$costData->id}_approved_brand"] = $itemValues[$costData->id][ $item['id'] ]['approved_brand'];
                $item["{$costData->id}_awarded_value"]  = $itemValues[$costData->id][ $item['id'] ]['awarded_value'];
                $item["{$costData->id}_awarded_total"]  = $itemValues[$costData->id][ $item['id'] ]['awarded_total'];
                $item["{$costData->id}_awarded_brand"]  = $itemValues[$costData->id][ $item['id'] ]['awarded_brand'];
            }

            $records[] = $item;
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'uom_id'      => -1,
            'uom_symbol'  => '',
        ));

        $totalRow = array(
            'id'          => 'total',
            'description' => "Total",
            'uom_id'      => -1,
            'uom_symbol'  => '',
            'type'        => 'summary',
        );

        foreach($costDatas as $costData)
        {
            $totalRow["{$costData->id}_units"]          = ($level == 2) ? array_sum(array_column($records, "{$costData->id}_units")) : 0;
            $totalRow["{$costData->id}_approved_total"] = array_sum(array_column($records, "{$costData->id}_approved_total"));
            $totalRow["{$costData->id}_awarded_total"]  = array_sum(array_column($records, "{$costData->id}_awarded_total"));
        }

        $records[] = $totalRow;

        if( $level == 2 )
        {
            $costPerTypeRow = array(
                'id'             => 'cost_per_type',
                'description'    => "Avg Cost/Type",
                'type'           => 'summary',
            );

            foreach($costDatas as $costData)
            {
                $costPerTypeRow["{$costData->id}_approved_total"] = Utilities::divide($totalRow["{$costData->id}_approved_total"], $costDataUnits[$costData->id]);
                $costPerTypeRow["{$costData->id}_awarded_total"]  = Utilities::divide($totalRow["{$costData->id}_awarded_total"], $costDataUnits[$costData->id]);
            }

            $records[] = $costPerTypeRow;

            $stmt = $pdo->prepare("
                SELECT p.id, p.description, p.summary_description, COALESCE(cdp.value, 0) AS value FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
                JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
                LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
                WHERE cd.id = {$costData->id}
                AND p.is_prime_cost_rate_summary_displayed = TRUE
                AND p.deleted_at IS NULL
                ORDER BY p.priority ASC
                ");

            $stmt->execute();

            $particulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($particulars as $particular)
            {
                $particularRow = array(
                    'id'             => 'particular-'.$particular['id'],
                    'description'    => empty($particular['summary_description']) ? "Cost/{$particular['description']}" : $particular['summary_description'],
                    'type'           => 'summary',
                );

                foreach($costDatas as $costData)
                {
                    $particularRow["{$costData->id}_approved_total"] = Utilities::divide($totalRow["{$costData->id}_approved_total"], $particular['value']);
                    $particularRow["{$costData->id}_awarded_total"]  = Utilities::divide($totalRow["{$costData->id}_awarded_total"], $particular['value']);
                }

                $records[] = $particularRow;
            }
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }
}
