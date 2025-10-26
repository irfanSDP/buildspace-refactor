<?php

/**
 * projectAnalyzer actions.
 *
 * @package    buildspace
 * @subpackage projectAnalyzer
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectAnalyzerActions extends BaseActions {

    public function executeGetAnalysisStatus(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $resourceAnalysisStatusCount = DoctrineQuery::create()->select('COUNT(b.id) AS count')
            ->from('BillType b')->leftJoin('b.ProjectStructure s')
            ->where('s.root_id = ?', $project->id)
            ->andWhere('b.status BETWEEN ? AND ?', array( BillType::STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM, BillType::STATUS_RESOURCE_ANALYSIS_RECALCULATE_BILL ))
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->limit(1)
            ->fetchOne();

        $scheduleOfRateAnalysisStatusCount = DoctrineQuery::create()->select('COUNT(b.id) AS count')
            ->from('BillType b')->leftJoin('b.ProjectStructure s')
            ->where('s.root_id = ?', $project->id)
            ->andWhere('b.status BETWEEN ? AND ?', array( BillType::STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ITEM, BillType::STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_BILL ))
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->limit(1)
            ->fetchOne();

        $scheduleOfQuantityStatusCount = DoctrineQuery::create()->select('COUNT(b.id) AS count')
            ->from('BillType b')->leftJoin('b.ProjectStructure s')
            ->where('s.root_id = ?', $project->id)
            ->andWhere('b.status BETWEEN ? AND ?', array( BillType::STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ITEM, BillType::STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_BILL ))
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->limit(1)
            ->fetchOne();

        $enableScheduleOfRateAnalysis = true;
        $enableResourceAnalysis       = true;
        $enableScheduleOfQuantity     = true;

        if ( $scheduleOfRateAnalysisStatusCount['count'] > 0 or $scheduleOfQuantityStatusCount['count'] > 0 )
        {
            $enableResourceAnalysis = false;
        }

        if ( $resourceAnalysisStatusCount['count'] > 0 or $scheduleOfQuantityStatusCount['count'] > 0 )
        {
            $enableScheduleOfRateAnalysis = false;
        }

        if ( $scheduleOfRateAnalysisStatusCount['count'] > 0 or $resourceAnalysisStatusCount['count'] > 0 )
        {
            $enableScheduleOfQuantity = false;
        }

        return $this->renderJson(array(
            'enable_schedule_of_rate_analysis' => $enableScheduleOfRateAnalysis,
            'enable_resource_analysis'         => $enableResourceAnalysis,
            'enable_schedule_of_qty'           => $enableScheduleOfQuantity
        ));
    }

    public function executeGetResources(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $totalCostByResources = ResourceTable::calculateTotalForResourceAnalysis($project);
        
        $stmt = $pdo->prepare("SELECT DISTINCT r_lib.id, r_lib.name FROM
        " . ResourceTable::getInstance()->getTableName() . " AS r_lib JOIN
        " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON r.resource_library_id = r_lib.id JOIN
        " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
        " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON ifc.relation_id = bur.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND r.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL
        AND ifc.final_value <> 0 AND ifc.deleted_at IS NULL
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL ORDER BY r_lib.id");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sumTotalCost = 0;
        $items        = [];

        foreach ( $records as $record )
        {
            $totalCost = array_key_exists($record['id'], $totalCostByResources) ? $totalCostByResources[$record['id']] : 0;

            if ( $totalCost == 0 )
            {
                continue;
            }

            $record['total_cost'] = $totalCost;

            array_push($items, $record);

            $sumTotalCost += $totalCost;
        }

        unset( $records );

        //default empty row
        array_push($items, array(
            'id'         => Constants::GRID_LAST_ROW,
            'name'       => '',
            'total_cost' => 0
        ));

        return $this->renderJson(array(
            'identifier'     => 'id',
            'items'          => $items,
            'sum_total_cost' => $sumTotalCost
        ));
    }

    public function executeGetResourceTrades(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $request->hasParameter('id')
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        /*
         * Query resource obj using PDO instead of Doctrine because we want to get the record even when the resource has been flagged as deleted(soft delete)
         */
        $sth = $pdo->prepare("SELECT id FROM " . ResourceTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('id'));
        $sth->execute();

        $this->forward404Unless($resource = $sth->fetch(PDO::FETCH_ASSOC));

        $totalCostByTrades = ResourceTradeTable::calculateTotalForResourceAnalysis($resource['id'], $project);

        $stmt = $pdo->prepare("SELECT DISTINCT r_trade.id, r_trade.description, r_trade.priority FROM
        " . ResourceTradeTable::getInstance()->getTableName() . " r_trade JOIN
        " . BillBuildUpRateResourceTradeTable::getInstance()->getTableName() . " AS t ON r_trade.id = t.resource_trade_library_id JOIN
        " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON t.build_up_rate_resource_id = r.id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS i ON r.bill_item_id = i.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id JOIN
        " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id AND bur.build_up_rate_resource_trade_id = t.id JOIN
        " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON ifc.relation_id = bur.id
        WHERE s.root_id = " . $project->id . " AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id IS NOT NULL
        AND bur.bill_item_id = i.id
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0 AND ifc.deleted_at IS NULL
        AND r.deleted_at IS NULL AND t.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL ORDER BY r_trade.priority");

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unSortedStmt = $pdo->prepare("SELECT COUNT(bur.id) AS count FROM
        " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r JOIN
        " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON r.id = bur.build_up_rate_resource_id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
        WHERE bur.build_up_rate_resource_trade_id IS NULL
        AND bur.resource_item_library_id IS NULL
        AND r.resource_library_id = " . $resource['id'] . " AND s.root_id = " . $project->id . " AND r.deleted_at IS NULL AND bur.deleted_at IS NULL
        AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL");

        $unSortedStmt->execute();

        $unSortedCount = $unSortedStmt->fetchColumn();

        $sumTotalCost = 0;

        foreach ( $results as $key => $result )
        {
            $totalCost = array_key_exists($result['id'], $totalCostByTrades) ? $totalCostByTrades[$result['id']] : 0;

            $results[$key]['total_cost'] = $totalCost;

            $sumTotalCost += $totalCost;
        }

        if ( $unSortedCount > 0 )
        {
            $totalCost = $totalCostByTrades['unsorted'];

            array_push($results, array(
                'id'          => 'unsorted',
                'description' => 'UNSORTED',
                'total_cost'  => $totalCost
            ));

            $sumTotalCost += $totalCost;
        }

        //default empty row
        array_push($results, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'total_qty'   => 0,
            'total_cost'  => 0
        ));

        return $this->renderJson(array(
            'identifier'     => 'id',
            'items'          => $results,
            'sum_total_cost' => $sumTotalCost
        ));
    }

    public function executeGetResourceItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $request->hasParameter('rid') and $request->hasParameter('id')
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        /*
         * Query resource obj using PDO instead of Doctrine because we want to get the record even when the resource has been flagged as deleted(soft delete)
         */
        $sth = $pdo->prepare("SELECT id FROM " . ResourceTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('rid'));
        $sth->execute();

        $this->forward404Unless($resource = $sth->fetch(PDO::FETCH_ASSOC));

        $results                   = array();
        $formulatedColumnConstants = array( BillBuildUpRateItem::FORMULATED_COLUMN_RATE, BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE );

        $sumTotalQuantity = 0;
        $sumTotalCost     = 0;

        $claimQuantities = $project->PostContract->exists() ? PostContractStandardClaimTable::getClaimQuantities($project->PostContract) : array();
        $totalResourceClaimQuantities = array();

        if ( $request->getParameter('id') == 'unsorted' )
        {
            $stmt = $pdo->prepare("SELECT DISTINCT bur.id FROM
            " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc JOIN
            " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON ifc.relation_id = bur.id JOIN
            " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON bur.build_up_rate_resource_id = r.id JOIN
            " . BillItemTable::getInstance()->getTableName() . " AS i ON r.bill_item_id = i.id JOIN
            " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
            " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $project->id . " AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id IS NULL
            AND bur.build_up_rate_resource_trade_id IS NULL
            AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0
            AND bur.deleted_at IS NULL AND ifc.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL");

            $stmt->execute();

            $itemIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            if ( count($itemIds) > 0 )
            {
                $items = DoctrineQuery::create()->select('i.id, i.description, i.uom_id, uom.symbol, ifc.column_name, ifc.final_value, i.bill_item_id')
                    ->from('BillBuildUpRateItem i')
                    ->leftJoin('i.FormulatedColumns ifc')
                    ->leftJoin('i.UnitOfMeasurement uom')
                    ->whereIn('i.id', $itemIds)
                    ->andWhere('i.deleted_at IS NULL')
                    ->andWhere('ifc.deleted_at IS NULL')
                    ->orderBy('i.priority')
                    ->fetchArray();

                foreach ( $items as $key => $item )
                {
                    $item['uom_symbol'] = $item['uom_id'] > 0 ? $item['UnitOfMeasurement']['symbol'] : '';

                    foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
                    {
                        $item[$formulatedColumnConstant . '-value']       = '';
                        $item[$formulatedColumnConstant . '-final_value'] = number_format(0, 2, '.', '');
                        $item[$formulatedColumnConstant . '-linked']      = false;
                        $item[$formulatedColumnConstant . '-has_formula'] = false;
                    }

                    foreach ( $item['FormulatedColumns'] as $formulatedColumn )
                    {
                        $columnName = $formulatedColumn['column_name'];

                        if ( !in_array($columnName, $formulatedColumnConstants) )
                        {
                            continue;
                        }

                        $item[$columnName . '-value']       = $formulatedColumn['final_value'];
                        $item[$columnName . '-final_value'] = $formulatedColumn['final_value'];
                    }

                    unset( $item['FormulatedColumns'], $item['UnitOfMeasurement'] );

                    list( $totalQuantity, $totalCost ) = BillBuildUpRateItemTable::calculateTotalForResourceAnalysis($item['id'], $resource['id'], $project->id);

                    $item['total_qty']     = $totalQuantity;
                    $item['total_cost']    = $totalCost;
                    $item['multi-rate']    = false;
                    $item['multi-wastage'] = false;

                    $item['claim_quantity'] = ($claimQuantities[null][$item['bill_item_id']] ?? 0);
                    $item['claim_amount'] = $item['claim_quantity'] * $item[BillBuildUpRateItem::FORMULATED_COLUMN_RATE.'-final_value'];

                    $sumTotalQuantity += $totalQuantity;
                    $sumTotalCost += $totalCost;

                    array_push($results, $item);
                    unset( $items[$key] );
                }
            }
        }
        else
        {
            /*
            * Query resource trade obj using PDO instead of Doctrine because we want to get the record even when the resource trade has been flagged as deleted(soft delete)
            */
            $sth = $pdo->prepare("SELECT id FROM " . ResourceTradeTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('id'));
            $sth->execute();

            $this->forward404Unless($trade = $sth->fetch(PDO::FETCH_ASSOC));

            $stmt = $pdo->prepare("SELECT DISTINCT bur.id, bur.resource_item_library_id, bur.bill_item_id FROM
            " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r JOIN
            " . BillBuildUpRateResourceTradeTable::getInstance()->getTableName() . " AS t ON t.build_up_rate_resource_id = r.id JOIN
            " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_trade_id = t.id JOIN
            " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bur.id = ifc.relation_id JOIN
            " . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
            " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
            " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $project->id . " AND r.resource_library_id = " . $resource['id'] . " AND t.resource_trade_library_id = " . $trade['id'] . "
            AND bur.resource_item_library_id IS NOT NULL
            AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0
            AND r.deleted_at IS NULL AND t.deleted_at IS NULL AND bur.deleted_at IS NULL AND ifc.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL");

            $stmt->execute();

            $buildUpRateItemWithResourceItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ( count($buildUpRateItemWithResourceItemIds) > 0 )
            {
                $buildUpRateItemIds     = array();
                $resourceItemLibraryIds = array();

                foreach ( $buildUpRateItemWithResourceItemIds as $record )
                {
                    $buildUpRateItemIds[] = $record['id'];

                    if ( !in_array($record['resource_item_library_id'], $resourceItemLibraryIds) )
                    {
                        $resourceItemLibraryIds[] = $record['resource_item_library_id'];
                    }

                    foreach($claimQuantities as $resourceItemLibraryId => $claimQuantity)
                    {
                        if($record['resource_item_library_id'] != $resourceItemLibraryId) continue;

                        $totalResourceClaimQuantities[$record['resource_item_library_id']] = ($totalResourceClaimQuantities[$record['resource_item_library_id']] ?? 0) + ($claimQuantity[$record['bill_item_id']] ?? 0);
                    }
                }

                $totalCostAndQuantityByResourceItems = ResourceItemTable::calculateTotalForResourceAnalysis($resourceItemLibraryIds, $resource['id'], $project->id);

                $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type, p.uom_id, p.level, p.priority, p.lft, uom.symbol AS uom_symbol
                FROM " . ResourceItemTable::getInstance()->getTableName() . " c
                JOIN " . ResourceItemTable::getInstance()->getTableName() . " p
                ON c.lft BETWEEN p.lft AND p.rgt
                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE c.root_id = p.root_id AND c.type <> " . ResourceItem::TYPE_HEADER . "
                AND c.id IN (" . implode(',', $resourceItemLibraryIds) . ")
                AND c.resource_trade_id = " . $trade['id'] . " AND p.resource_trade_id = " . $trade['id'] . "
                AND p.deleted_at IS NULL AND c.deleted_at IS NULL
                ORDER BY p.root_id, p.priority, p.lft, p.level ASC");

                $stmt->execute();

                $resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("SELECT bur.resource_item_library_id, ifc.column_name, ifc.final_value FROM
                " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc JOIN
                " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON ifc.relation_id = bur.id JOIN
                " . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
                " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
                " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
                WHERE s.root_id = " . $project->id . " AND bur.id IN (" . implode(',', array_unique($buildUpRateItemIds)) . ")
                AND (ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_RATE . "' OR ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE . "')
                AND ifc.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
                AND i.deleted_at IS NULL AND e.deleted_at IS NULL
                GROUP BY bur.resource_item_library_id, ifc.column_name, ifc.final_value
                ORDER BY bur.resource_item_library_id");

                $stmt->execute();

                $formulatedColumnNames = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

                $stmt->execute();
                $formulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ( $resourceItems as $key => $item )
                {
                    $multiRate     = false;
                    $multiWastage  = false;
                    $totalQuantity = 0;
                    $totalCost     = 0;

                    foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
                    {
                        $item[$formulatedColumnConstant . '-value']       = '';
                        $item[$formulatedColumnConstant . '-final_value'] = number_format(0, 2, '.', '');
                        $item[$formulatedColumnConstant . '-linked']      = false;
                        $item[$formulatedColumnConstant . '-has_formula'] = false;
                    }

                    if ( array_key_exists($item['id'], $formulatedColumnNames) )
                    {
                        $columnNames = array_count_values($formulatedColumnNames[$item['id']]);

                        if ( array_key_exists(ResourceItem::FORMULATED_COLUMN_RATE, $columnNames) && $columnNames[ResourceItem::FORMULATED_COLUMN_RATE] > 1 )
                        {
                            $item[ResourceItem::FORMULATED_COLUMN_RATE . '-value']       = '';
                            $item[ResourceItem::FORMULATED_COLUMN_RATE . '-final_value'] = 0;
                            $multiRate                                                   = true;
                        }
                        else
                        {
                            foreach ( $formulatedColumns as $formulatedColumn )
                            {
                                $columnName = $formulatedColumn['column_name'];
                                if ( $formulatedColumn['resource_item_library_id'] == $item['id'] and $columnName == ResourceItem::FORMULATED_COLUMN_RATE )
                                {
                                    $finalValue                         = $formulatedColumn['final_value'] ? $formulatedColumn['final_value'] : number_format(0, 2, '.', '');
                                    $item[$columnName . '-value']       = $finalValue;
                                    $item[$columnName . '-final_value'] = $finalValue;

                                    break 1;
                                }
                            }
                        }

                        if ( array_key_exists(ResourceItem::FORMULATED_COLUMN_WASTAGE, $columnNames) && $columnNames[ResourceItem::FORMULATED_COLUMN_WASTAGE] > 1 )
                        {
                            $item[ResourceItem::FORMULATED_COLUMN_WASTAGE . '-value']       = '';
                            $item[ResourceItem::FORMULATED_COLUMN_WASTAGE . '-final_value'] = 0;
                            $multiWastage                                                   = true;
                        }
                        else
                        {
                            foreach ( $formulatedColumns as $formulatedColumn )
                            {
                                $columnName = $formulatedColumn['column_name'];
                                if ( $formulatedColumn['resource_item_library_id'] == $item['id'] and $columnName == ResourceItem::FORMULATED_COLUMN_WASTAGE )
                                {
                                    $finalValue                         = $formulatedColumn['final_value'] ? $formulatedColumn['final_value'] : number_format(0, 2, '.', '');
                                    $item[$columnName . '-value']       = $finalValue;
                                    $item[$columnName . '-final_value'] = $finalValue;

                                    break 1;
                                }
                            }
                        }
                    }

                    if ( $item['type'] == ResourceItem::TYPE_WORK_ITEM && array_key_exists($item['id'], $totalCostAndQuantityByResourceItems) )
                    {
                        $totalCost     = $totalCostAndQuantityByResourceItems[$item['id']]['total_cost'];
                        $totalQuantity = $totalCostAndQuantityByResourceItems[$item['id']]['total_quantity'];
                    }

                    $item['claim_quantity'] = ($totalResourceClaimQuantities[$item['id']] ?? 0);
                    $item['claim_amount'] = $item['claim_quantity'] * $item[BillBuildUpRateItem::FORMULATED_COLUMN_RATE.'-final_value'];

                    $item['total_qty']     = $totalQuantity;
                    $item['total_cost']    = $totalCost;
                    $item['multi-rate']    = $multiRate;
                    $item['multi-wastage'] = $multiWastage;

                    $sumTotalQuantity += $totalQuantity;
                    $sumTotalCost += $totalCost;

                    array_push($results, $item);
                    unset( $resourceItems[$key] );
                }
            }
        }

        $emptyRow = array(
            'id'            => Constants::GRID_LAST_ROW,
            'description'   => '',
            'uom_symbol'    => '',
            'total_qty'     => 0,
            'total_cost'    => 0,
            'multi-rate'    => false,
            'multi-wastage' => false
        );

        foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
        {
            $emptyRow[$formulatedColumnConstant . '-value']       = '';
            $emptyRow[$formulatedColumnConstant . '-final_value'] = 0;
            $emptyRow[$formulatedColumnConstant . '-linked']      = false;
            $emptyRow[$formulatedColumnConstant . '-has_formula'] = false;
        }

        array_push($results, $emptyRow);

        return $this->renderJson(array(
            'identifier'     => 'id',
            'items'          => $results,
            'sum_total_qty'  => $sumTotalQuantity,
            'sum_total_cost' => $sumTotalCost
        ));
    }

    public function executeResourceItemUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $request->hasParameter('unsorted') and $request->hasParameter('attr_name')
        );

        $affectedNodes = array();
        $val           = is_numeric($request->getParameter('val')) ? $request->getParameter('val') : 0;
        $pdo           = $project->getTable()->getConnection()->getDbh();

        switch ($request->getParameter('unsorted'))
        {
            case 'true':
                $this->forward404Unless($billBuildUpRateItem = BillBuildUpRateItemTable::getInstance()->find($request->getParameter('id')));

                $fieldName        = $request->getParameter('attr_name');
                $formulatedColumn = $billBuildUpRateItem->getFormulatedColumnByName($fieldName);

                if ( $formulatedColumn )
                {
                    $formulatedColumn->setFormula($val);
                    $formulatedColumn->save();

                    $formulatedColumn->BuildUpRateItem->calculateTotal();
                    $formulatedColumn->BuildUpRateItem->calculateLineTotal();

                    $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                    foreach ( $referencedNodes as $referencedNode )
                    {
                        $node = BillBuildUpRateFormulatedColumnTable::getInstance()->find($referencedNode['node_from']);

                        if ( $node )
                        {
                            $node->BuildUpRateItem->calculateTotal();
                            $node->BuildUpRateItem->calculateLineTotal();

                            $billBuildUpRateItem->BillItem->updateBillItemTotalColumns();

                            array_push($affectedNodes, array(
                                'id'                        => $node->relation_id,
                                $fieldName . '-value'       => $node->final_value,
                                $fieldName . '-final_value' => $node->final_value
                            ));
                        }
                    }

                    $billBuildUpRateItem->BillItem->BuildUpRateSummary->calculateFinalCost();// we call this method to update bill item's rate

                    $stmt = $pdo->prepare("UPDATE " . BillTypeTable::getInstance()->getTableName() . " SET status = " . BillType::STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM . " WHERE
                    project_structure_id IN (SELECT DISTINCT e.project_structure_id FROM " . BillElementTable::getInstance()->getTableName() . " e JOIN
                    " . BillItemTable::getInstance()->getTableName() . " i ON e.id = i.element_id JOIN
                    " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON i.id = bur.bill_item_id JOIN
                    " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " fc ON bur.id = fc.relation_id WHERE fc.id = " . $formulatedColumn->id . "
                    AND fc.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
                    AND i.deleted_at IS NULL AND e.deleted_at IS NULL)");

                    $stmt->execute();

                    $data = array(
                        'id'                        => $billBuildUpRateItem->id,
                        $fieldName . '-value'       => $formulatedColumn->final_value,
                        $fieldName . '-final_value' => $formulatedColumn->final_value,
                        'affected_nodes'            => $affectedNodes
                    );
                }
                break;
            case 'false':
                $this->forward404Unless($resourceItem = ResourceItemTable::getInstance()->find($request->getParameter('id')));

                $fieldName = $request->getParameter('attr_name');

                $resourceItem->updateBuildUpRateFromAnalysis($val, $fieldName, $project);

                $data = array(
                    'id'                        => $resourceItem->id,
                    $fieldName . '-value'       => number_format($val, 5, '.', ''),
                    $fieldName . '-final_value' => number_format($val, 5, '.', ''),
                    'affected_nodes'            => array()
                );
                break;
            default:
                throw new Exception('invalid unsorted case');
        }

        return $this->renderJson(array(
            'success' => true,
            'data'    => $data
        ));
    }

    public function executeGetBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $request->hasParameter('rid') and $request->hasParameter('tid') and $request->hasParameter('id')
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $claimQuantities = $project->PostContract->exists() ? PostContractStandardClaimTable::getClaimQuantities($project->PostContract) : array();

        /*
         * Query resource obj using PDO instead of Doctrine because we want to get the record even when the resource has been flagged as deleted(soft delete)
         */
        $sth = $pdo->prepare("SELECT id FROM " . ResourceTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('rid'));
        $sth->execute();

        $this->forward404Unless($resource = $sth->fetch(PDO::FETCH_ASSOC));

        $formulatedColumnConstants = array(
            BillBuildUpRateItem::FORMULATED_COLUMN_RATE,
            BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY,
            BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE
        );

        if ( $request->getParameter('tid') == 'unsorted' )
        {
            $this->forward404Unless($buildUpRateItem = Doctrine_Core::getTable('BillBuildUpRateItem')->find($request->getParameter('id')));

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.grand_total, p.grand_total_quantity, p.level, p.priority, p.lft
            FROM " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur
            JOIN " . BillItemTable::getInstance()->getTableName() . " c ON bur.bill_item_id = c.id AND bur.deleted_at IS NULL
            JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $project->id . " AND  c.root_id = p.root_id AND c.element_id = p.element_id AND c.type <> " . BillItem::TYPE_HEADER . "
            AND bur.id = " . $buildUpRateItem->id . "
            AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
            AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL
            ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalCostAndQuantity = BillItemTable::calculateTotalForResourceAnalysis($resource['id'], null, $buildUpRateItem->id, true);

            /*
             * get rate and wastage from build up rate item
             */
            $stmt = $pdo->prepare("SELECT bur.bill_item_id, bur.uom_id, uom.symbol as uom_symbol, ifc.column_name, ifc.value, ifc.final_value, ifc.linked
            FROM " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc
            JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON ifc.relation_id = bur.id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " AS uom ON bur.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE bur.id = " . $buildUpRateItem->id . "
            AND ifc.column_name NOT IN ('" . BillBuildUpRateItem::FORMULATED_COLUMN_NUMBER . "', '" . BillBuildUpRateItem::FORMULATED_COLUMN_CONSTANT . "')
            AND ifc.deleted_at IS NULL AND bur.deleted_at IS NULL ORDER BY bur.bill_item_id");

            $stmt->execute();

            $formulatedColumnRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /*
             * select bills
             */
            $stmt = $pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s JOIN
            " . BillElementTable::getInstance()->getTableName() . " AS e ON e.project_structure_id = s.id JOIN
            " . BillItemTable::getInstance()->getTableName() . " AS i ON i.element_id = e.id JOIN
            " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.bill_item_id = i.id
            WHERE s.root_id = " . $project->id . " AND bur.id = " . $buildUpRateItem->id . "
            AND s.deleted_at IS NULL AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND bur.deleted_at IS NULL ORDER BY s.lft ASC");

            $stmt->execute();

            $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /*
             * select elements
             */
            $selectDistinctElementSql = "SELECT DISTINCT e.id, e.description, e.priority FROM
            " . BillElementTable::getInstance()->getTableName() . " AS e JOIN
            " . BillItemTable::getInstance()->getTableName() . " AS i ON i.element_id = e.id JOIN
            " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON r.bill_item_id = i.id JOIN
            " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id
            WHERE e.project_structure_id = :bill_id AND r.resource_library_id = " . $resource['id'] . " AND bur.id = " . $buildUpRateItem->id . "
            AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND bur.deleted_at IS NULL ORDER BY e.priority ASC";

            $resourceItemId = null;
        }
        else
        {
            /*
            * Query resource item obj using PDO instead of Doctrine because we want to get the record even when the resource item has been flagged as deleted(soft delete)
            */
            $stmtItem = $pdo->prepare("SELECT id FROM " . ResourceItemTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('id'));
            $stmtItem->execute();

            $this->forward404Unless($resourceItem = $stmtItem->fetch(PDO::FETCH_ASSOC));

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.grand_total, p.grand_total_quantity, p.level, p.priority, p.lft, bur.resource_item_library_id
            FROM " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r
            JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id AND r.deleted_at IS NULL
            JOIN " . BillItemTable::getInstance()->getTableName() . " c  ON bur.bill_item_id = c.id AND bur.deleted_at IS NULL
            JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $project->id . " AND c.root_id = p.root_id AND c.element_id = p.element_id
            AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id = " . $resourceItem['id'] . "
            AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
            AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL
            ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalCostAndQuantity = BillItemTable::calculateTotalForResourceAnalysis($resource['id'], $resourceItem['id'], null, false);

            /*
            * get rate and wastage from build up rate item
            */
            $stmt = $pdo->prepare("SELECT bur.bill_item_id, bur.uom_id, uom.symbol AS uom_symbol, ifc.column_name, ifc.value, ifc.final_value, ifc.linked
            FROM " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc
            JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON ifc.relation_id = bur.id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " AS uom ON bur.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON bur.build_up_rate_resource_id = r.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " AS i ON r.bill_item_id = i.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $project->id . " AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id = " . $resourceItem['id'] . "
            AND ifc.column_name NOT IN ('" . BillBuildUpRateItem::FORMULATED_COLUMN_NUMBER . "', '" . BillBuildUpRateItem::FORMULATED_COLUMN_CONSTANT . "')
            AND s.deleted_at IS NULL AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND ifc.deleted_at IS NULL AND bur.deleted_at IS NULL ORDER BY bur.bill_item_id");

            $stmt->execute();

            $formulatedColumnRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /*
             * select bills
             */
            $stmt = $pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s JOIN
            " . BillElementTable::getInstance()->getTableName() . " AS e ON e.project_structure_id = s.id JOIN
            " . BillItemTable::getInstance()->getTableName() . " AS i ON i.element_id = e.id JOIN
            " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON r.bill_item_id = i.id JOIN
            " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id
            WHERE s.root_id = " . $project->id . " AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id = " . $resourceItem['id'] . "
            AND s.deleted_at IS NULL AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND bur.deleted_at IS NULL ORDER BY s.lft ASC");

            $stmt->execute();

            $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /*
             * select elements
             */
            $selectDistinctElementSql = "SELECT DISTINCT e.id, e.description, e.priority FROM
            " . BillElementTable::getInstance()->getTableName() . " AS e JOIN
            " . BillItemTable::getInstance()->getTableName() . " AS i ON i.element_id = e.id JOIN
            " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON r.bill_item_id = i.id JOIN
            " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id
            WHERE e.project_structure_id = :bill_id AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id = " . $resourceItem['id'] . "
            AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND bur.deleted_at IS NULL ORDER BY e.priority ASC";

            $buildUpRateItemId = null;
        }

        $sumTotalQuantity = 0;
        $sumTotalCost     = 0;
        $results          = array();
        $form             = new BaseForm();

        $formulatedColumns = array();

        foreach ( $formulatedColumnRecords as $k => $formulatedColumn )
        {
            if ( !array_key_exists($formulatedColumn['bill_item_id'], $formulatedColumns) )
            {
                $formulatedColumns[$formulatedColumn['bill_item_id']] = array();
            }

            $columnName = $formulatedColumn['column_name'];

            $formulatedColumns[$formulatedColumn['bill_item_id']][] = array(
                'column_name'                => $columnName,
                'uom_symbol'                 => $formulatedColumn['uom_symbol'],
                $columnName . '-value'       => $formulatedColumn['final_value'],
                $columnName . '-final_value' => $formulatedColumn['final_value'],
                $columnName . '-linked'      => $formulatedColumn['linked']
            );

            unset( $formulatedColumn, $formulatedColumnRecords[$k] );
        }

        foreach ( $bills as $bill )
        {
            $stmt = $pdo->prepare($selectDistinctElementSql);

            $stmt->execute(array(
                'bill_id' => $bill['id']
            ));

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $elements as $element )
            {
                $result = array(
                    'id'          => 'bill-' . $bill['id'] . '-elem' . $element['id'],
                    'description' => $bill['title'] . " > " . $element['description'],
                    'type'        => - 1,
                    'level'       => 0,
                    'uom_id'      => - 1,
                    'uom_symbol'  => ''
                );

                foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
                {
                    $result[$formulatedColumnConstant . '-value']        = '';
                    $result[$formulatedColumnConstant . '-final_value']  = 0;
                    $result[$formulatedColumnConstant . '-linked']       = false;
                    $result[$formulatedColumnConstant . '-has_formula']  = false;
                    $result[$formulatedColumnConstant . '-has_build_up'] = false;
                }

                $result['claim_quantity'] = 0;
                $result['claim_amount']   = 0;

                array_push($results, $result);

                $billItem = array( 'id' => - 1 );

                foreach ( $items as $key => $item )
                {
                    if ( $billItem['id'] != $item['id'] && $item['element_id'] == $element['id'] )
                    {
                        $billItem['id']                   = $item['id'];
                        $billItem['description']          = $item['description'];
                        $billItem['type']                 = $item['type'];
                        $billItem['grand_total']          = $item['grand_total'];
                        $billItem['grand_total_quantity'] = $item['grand_total_quantity'];
                        $billItem['level']                = $item['level'];
                        $billItem['uom_symbol']           = '';
                        $billItem['_csrf_token']          = $form->getCSRFToken();

                        foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
                        {
                            $billItem[$formulatedColumnConstant . '-value']        = '';
                            $billItem[$formulatedColumnConstant . '-final_value']  = 0;
                            $billItem[$formulatedColumnConstant . '-linked']       = false;
                            $billItem[$formulatedColumnConstant . '-has_formula']  = false;
                            $billItem[$formulatedColumnConstant . '-has_build_up'] = false;
                        }

                        if ( array_key_exists($item['id'], $formulatedColumns) )
                        {
                            foreach ( $formulatedColumns[$item['id']] as $formulatedColumn )
                            {
                                $columnName             = $formulatedColumn['column_name'];
                                $billItem['uom_symbol'] = $formulatedColumn['uom_symbol'];

                                $billItem[$columnName . '-value']       = $formulatedColumn[$columnName . '-value'];
                                $billItem[$columnName . '-final_value'] = $formulatedColumn[$columnName . '-final_value'];
                                $billItem[$columnName . '-linked']      = $formulatedColumn[$columnName . '-linked'];
                            }

                            unset( $formulatedColumn, $formulatedColumns[$item['id']] );
                        }

                        $resourceItemLibraryId = ($item['resource_item_library_id'] ?? null);
                        $billItem['claim_quantity'] = ($claimQuantities[$resourceItemLibraryId][$item['id']] ?? 0);
                        $billItem['claim_amount'] = $billItem['claim_quantity'] * $billItem[BillBuildUpRateItem::FORMULATED_COLUMN_RATE.'-final_value'];

                        if ( array_key_exists($item['id'], $totalCostAndQuantity) and $item['grand_total_quantity'] != '' and $item['grand_total_quantity'] != 0 and $item['type'] != BillItem::TYPE_HEADER and $item['type'] != BillItem::TYPE_HEADER_N and $item['type'] != BillItem::TYPE_NOID )
                        {
                            $totalCost     = $totalCostAndQuantity[$item['id']]['total_cost'];
                            $totalQuantity = $totalCostAndQuantity[$item['id']]['total_quantity'];

                            unset( $totalCostAndQuantity[$item['id']] );
                        }
                        else
                        {
                            $totalQuantity = 0;
                            $totalCost     = 0;
                        }

                        $billItem['total_qty']  = $totalQuantity;
                        $billItem['total_cost'] = $totalCost;

                        $sumTotalQuantity += $totalQuantity;
                        $sumTotalCost += $totalCost;

                        array_push($results, $billItem);

                        unset( $item, $items[$key] );
                    }
                }
            }
        }

        $emptyRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'type'        => BillItem::TYPE_WORK_ITEM,
            'level'       => 0,
            'uom_id'      => - 1,
            'uom_symbol'  => '',
            'total_qty'   => 0,
            'total_cost'  => 0
        );

        foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
        {
            $emptyRow[$formulatedColumnConstant . '-value']        = '';
            $emptyRow[$formulatedColumnConstant . '-final_value']  = 0;
            $emptyRow[$formulatedColumnConstant . '-linked']       = false;
            $emptyRow[$formulatedColumnConstant . '-has_formula']  = false;
            $emptyRow[$formulatedColumnConstant . '-has_build_up'] = false;
        }

        array_push($results, $emptyRow);

        return $this->renderJson(array(
            'identifier'     => 'id',
            'items'          => $results,
            'sum_total_qty'  => $sumTotalQuantity,
            'sum_total_cost' => $sumTotalCost
        ));
    }

    public function executeBillItemUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and
            $request->hasParameter('unsorted') and $request->hasParameter('attr_name')
        );

        $affectedNodes = array();
        $data          = array();
        $fieldName     = $request->getParameter('attr_name');
        $val           = is_numeric($request->getParameter('val')) ? $request->getParameter('val') : 0;
        $pdo           = $project->getTable()->getConnection()->getDbh();

        $billBuildUpRateItemId = false;

        switch ($request->getParameter('unsorted'))
        {
            case 'true':
                $this->forward404Unless($billBuildUpRateItem = BillBuildUpRateItemTable::getInstance()->find($request->getParameter('rid')));

                $formulatedColumn = BillBuildUpRateFormulatedColumnTable::getInstance()->getByRelationIdAndColumnName($billBuildUpRateItem->id, $fieldName);
                $formulatedColumn->setFormula($val);
                $formulatedColumn->save();

                $formulatedColumn->refresh();

                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                foreach ( $referencedNodes as $referencedNode )
                {
                    $node = BillBuildUpRateFormulatedColumnTable::getInstance()->find($referencedNode['node_from']);

                    if ( $node )
                    {
                        $node->BuildUpRateItem->calculateTotal();
                        $node->BuildUpRateItem->calculateLineTotal();

                        array_push($affectedNodes, array(
                            'id'                        => $node->BuildUpRateItem->bill_item_id,
                            $fieldName . '-value'       => $node->final_value,
                            $fieldName . '-final_value' => $node->final_value
                        ));
                    }
                }

                BillBuildUpRateItemTable::calculateTotalById($billBuildUpRateItem->id);
                BillBuildUpRateItemTable::calculateLineTotalById($billBuildUpRateItem->id);

                $billBuildUpRateItemId = $billBuildUpRateItem->id;

                $data = array(
                    'id'                        => $billItem->id,
                    $fieldName . '-value'       => $formulatedColumn->final_value,
                    $fieldName . '-final_value' => $formulatedColumn->final_value,
                    'affected_nodes'            => $affectedNodes
                );

                break;
            case 'false':
                $this->forward404Unless($resourceItem = ResourceItemTable::getInstance()->find($request->getParameter('rid')));

                $billBuildUpRateItem = BillBuildUpRateItemTable::getByBillItemIdAndResourceItemId($billItem->id, $resourceItem->id, Doctrine_Core::HYDRATE_ARRAY);

                if ( $billBuildUpRateItem )
                {
                    $formulatedColumn = BillBuildUpRateFormulatedColumnTable::getInstance()->getByRelationIdAndColumnName($billBuildUpRateItem['id'], $fieldName);

                    $formulatedColumn->setFormula($val);

                    $formulatedColumn->parentSave();

                    $formulatedColumn->refresh();

                    $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                    foreach ( $referencedNodes as $referencedNode )
                    {
                        $node = BillBuildUpRateFormulatedColumnTable::getInstance()->find($referencedNode['node_from']);

                        if ( $node )
                        {
                            $node->BuildUpRateItem->calculateTotal();
                            $node->BuildUpRateItem->calculateLineTotal();
                        }
                    }

                    BillBuildUpRateItemTable::calculateTotalById($billBuildUpRateItem['id']);
                    BillBuildUpRateItemTable::calculateLineTotalById($billBuildUpRateItem['id']);

                    $billBuildUpRateItemId = $billBuildUpRateItem['id'];

                    $data = array(
                        'id'                        => $billItem->id,
                        $fieldName . '-value'       => $formulatedColumn->final_value,
                        $fieldName . '-final_value' => $formulatedColumn->final_value
                    );
                }
                break;
            default:
                throw new Exception('invalid unsorted case');
        }

        /*
        * Insert into recalculate bill item table so we can track the affected bil items and do recalculation
        * after analysis updates.
        */
        $stmt = $pdo->prepare("INSERT INTO " . RecalculateBillItemTable::getInstance()->getTableName() . "
                (bill_item_id, type, created_at, updated_at)
                SELECT i.id, " . RecalculateBillItem::TYPE_RESOURCE_ANALYSIS . ", NOW(), NOW() FROM " . BillItemTable::getInstance()->getTableName() . " AS i WHERE
                i.id = " . $billItem->id . " AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND
                NOT EXISTS (SELECT 1 FROM " . RecalculateBillItemTable::getInstance()->getTableName() . " AS r
                WHERE r.bill_item_id = i.id AND r.type = " . RecalculateBillItem::TYPE_RESOURCE_ANALYSIS . ")");

        $stmt->execute();

        $stmt = $pdo->prepare("UPDATE " . BillTypeTable::getInstance()->getTableName() . " SET status = " . BillType::STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM . " WHERE
            project_structure_id IN (SELECT DISTINCT e.project_structure_id FROM " . BillElementTable::getInstance()->getTableName() . " e JOIN
            " . BillItemTable::getInstance()->getTableName() . " i ON e.id = i.element_id JOIN
            " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON i.id = bur.bill_item_id WHERE bur.id = " . $billBuildUpRateItemId . "
            AND bur.deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL)");

        $stmt->execute();

        return $this->renderJson(array(
            'success' => true,
            'data'    => $data
        ));
    }

    public function executeGetValuesFromResourceAndProject(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('rid'))
        );

        $pdo        = $project->getTable()->getConnection()->getDbh();
        $columnName = $request->getParameter('cname');

        $stmt = $pdo->prepare("SELECT ifc.column_name, ifc.final_value FROM
            " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc JOIN
            " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON ifc.relation_id = bur.id JOIN
            " . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
            " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
            " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $project->id . " AND bur.resource_item_library_id = " . $resourceItem->id . "
            AND ifc.column_name = '" . $columnName . "'
            AND ifc.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.deleted_at IS NULL AND s.deleted_at IS NULL AND e.deleted_at IS NULL
            GROUP BY bur.resource_item_library_id, ifc.column_name, ifc.final_value ORDER BY ifc.final_value");

        $stmt->execute();

        $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = array();

        $form = new BaseForm();

        foreach ( $rates as $key => $rate )
        {
            $item['id']                         = $key;
            $item[$columnName . '-value']       = $rate['final_value'];
            $item[$columnName . '-final_value'] = $rate['final_value'];
            $item[$columnName . '-linked']      = false;
            $item[$columnName . '-has_formula'] = false;

            $sql = "SELECT COUNT(i.id) FROM
                " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc JOIN
                " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON ifc.relation_id = bur.id JOIN
                " . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
                " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
                " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
                WHERE s.root_id = " . $project->id . " AND bur.resource_item_library_id = " . $resourceItem->id . "
                AND ifc.column_name = '" . $columnName . "' AND ";

            if ( $rate['final_value'] )
            {
                $sql .= "ifc.final_value = " . $rate['final_value'] . " ";
            }
            else
            {
                $sql .= "ifc.final_value IS NULL ";
            }

            $sql .= "AND ifc.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.deleted_at IS NULL AND s.deleted_at IS NULL AND e.deleted_at IS NULL";

            $stmt = $pdo->prepare($sql);

            $stmt->execute();

            $count = $stmt->fetchColumn();

            $item['no_bill_items'] = $count;
            $item['_csrf_token']   = $form->getCSRFToken();

            array_push($items, $item);
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeResourceMultiValueUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('rid')) and
            $request->hasParameter('cname') and $request->hasParameter('oval')
        );

        $originalVal = is_numeric($request->getParameter('oval')) ? $request->getParameter('oval') : 0;
        $val         = is_numeric($request->getParameter('val')) ? $request->getParameter('val') : 0;

        $pdo       = $project->getTable()->getConnection()->getDbh();
        $fieldName = $request->getParameter('cname');

        $resourceItem->updateBuildUpRateFromAnalysis($val, $fieldName, $project, true, $originalVal);

        $stmt = $pdo->prepare("SELECT ifc.final_value FROM
            " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc JOIN
            " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON ifc.relation_id = bur.id JOIN
            " . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
            " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
            " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $project->id . " AND bur.resource_item_library_id = " . $resourceItem->id . "
            AND ifc.column_name = '" . $fieldName . "'
            AND ifc.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.deleted_at IS NULL AND s.deleted_at IS NULL AND e.deleted_at IS NULL
            GROUP BY bur.resource_item_library_id, ifc.column_name, ifc.final_value ORDER BY bur.resource_item_library_id");

        $stmt->execute();

        $columnCount = $stmt->rowCount();

        return $this->renderJson(array(
            'data'           => array(
                'id'                        => $request->getParameter('id'),
                $fieldName . '-value'       => number_format($val, 5, '.', ''),
                $fieldName . '-final_value' => number_format($val, 5, '.', '')
            ),
            'resource_store' => array(
                'multi' => $columnCount > 1 ? true : false,
                'data'  => array(
                    'id'                        => $resourceItem->id,
                    'multi-' . $fieldName       => $columnCount > 1 ? true : false,
                    $fieldName . '-value'       => number_format($val, 5, '.', ''),
                    $fieldName . '-final_value' => number_format($val, 5, '.', '')
                )
            )
        ));
    }

    public function executeRecalculate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $request->hasParameter('level')
        );

        $billType = $bill->BillType;

        $billType->recalculateByLevel($request->getParameter('level'));

        return $this->renderJson(array(
            'success'     => true,
            'bill_status' => $billType->status,
            'item'        => array(
                'id'                         => $bill->id,
                'bill_status'                => $billType->status,
                'original_total'             => ProjectStructureTable::getOverallOriginalTotalByBillId($bill->id),
                'overall_total_after_markup' => ProjectStructureTable::getOverallTotalAfterMarkupByBillId($bill->id)
            )
        ));
    }

    public function executeGetScheduleOfRates(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT id, name FROM " . ScheduleOfRateTable::getInstance()->getTableName() . " WHERE id IN
        (SELECT DISTINCT t.schedule_of_rate_id FROM " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " AS t JOIN
        " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS i ON t.id = i.trade_id JOIN
        " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON i.id = ifc.relation_id JOIN
        " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS bi ON bifc.relation_id = bi.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "' AND bifc.schedule_of_rate_item_formulated_column_id IS NOT NULL
        AND i.deleted_at IS NULL AND t.deleted_at IS NULL
        AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
        AND be.deleted_at IS NULL AND s.deleted_at IS NULL) AND deleted_at IS NULL ORDER BY id");

        $stmt->execute();

        $unSortedStmt = $pdo->prepare("SELECT COUNT(bi.id)
        FROM " . BillItemTable::getInstance()->getTableName() . " AS bi
        JOIN " . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND bi.id NOT IN (
            SELECT DISTINCT bifc.relation_id FROM " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " bifc
            WHERE bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "' AND bifc.schedule_of_rate_item_formulated_column_id IS NOT NULL AND bifc.deleted_at IS NULL
        )
        AND bi.type <> " . BillItem::TYPE_HEADER . " AND bi.type <> " . BillItem::TYPE_NOID . " AND bi.type <> " . BillItem::TYPE_ITEM_PC_RATE . " AND bi.type <> " . BillItem::TYPE_HEADER_N . "
        AND bi.type <> " . BillItem::TYPE_ITEM_LUMP_SUM . " AND bi.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . " AND bi.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE . " AND bi.type <> " . BillItem::TYPE_ITEM_RATE_ONLY . "
        AND bi.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND be.deleted_at IS NULL AND s.deleted_at IS NULL");

        $unSortedStmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sumTotalCost = 0;

        $form = new BaseForm();

        foreach($results as $key => $result)
        {
            $totalCost = ScheduleOfRateTable::calculateTotalCostForAnalysis($result['id'], $project->id);

            $results[ $key ]['total_cost']  = $totalCost;
            $results[ $key ]['_csrf_token'] = $form->getCSRFToken();

            $sumTotalCost += $totalCost;
        }

        $unSortedCount = $unSortedStmt->fetchColumn();

        if ( $unSortedCount > 0 )
        {
            $records   = BillElementTable::calculateTotalForScheduleOfRateAnalysisByProject($project);
            $totalCost = 0;

            foreach ( $records as $record )
            {
                $totalCost += $record[0];
                unset( $record );
            }

            unset( $records );

            array_push($results, array(
                'id'         => 'unsorted',
                'name'       => 'UNSORTED',
                'total_cost' => $totalCost
            ));
        }

        array_push($results, array(
            'id'         => Constants::GRID_LAST_ROW,
            'name'       => '',
            'total_cost' => 0
        ));

        return $this->renderJson(array(
            'identifier'     => 'id',
            'items'          => $results,
            'sum_total_cost' => $sumTotalCost
        ));
    }

    public function executeGetScheduleOfRateTrades(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('id'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $sumTotalCost = 0;

        $stmt = $pdo->prepare("SELECT id, description FROM " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " WHERE id IN
        (SELECT DISTINCT t.id FROM " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " AS t JOIN
        " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS i ON t.id = i.trade_id JOIN
        " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON i.id = ifc.relation_id JOIN
        " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS bi ON bifc.relation_id = bi.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND t.schedule_of_rate_id = " . $scheduleOfRate->id . " AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
        AND i.deleted_at IS NULL
        AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
        AND be.deleted_at IS NULL AND s.deleted_at IS NULL) AND schedule_of_rate_id = " . $scheduleOfRate->id . " AND deleted_at IS NULL ORDER BY priority");

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach ( $results as $key => $result )
        {
            $totalCost = ScheduleOfRateTradeTable::calculateTotalCostForAnalysis($result['id'], $project->id);

            $results[ $key ]['total_cost']  = $totalCost;
            $results[ $key ]['_csrf_token'] = $form->getCSRFToken();

            $sumTotalCost += $totalCost;
        }

        array_push($results, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'total_cost'  => 0
        ));

        return $this->renderJson(array(
            'identifier'     => 'id',
            'items'          => $results,
            'sum_total_cost' => $sumTotalCost
        ));
    }

    public function executeGetScheduleOfRateItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('sorid')) and
            $trade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('id'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $sumTotalQuantity = 0;
        $sumTotalCost     = 0;

        $stmt = $pdo->prepare("SELECT DISTINCT i.id FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS i JOIN
        " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON i.id = ifc.relation_id JOIN
        " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS bi ON bifc.relation_id = bi.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND i.trade_id = " . $trade->id . "
        AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
        AND i.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
        AND bi.deleted_at IS NULL AND be.deleted_at IS NULL AND s.deleted_at IS NULL");

        $stmt->execute();

        $scheduleOfRateItemIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type, p.uom_id,
        p.level, p.priority, p.lft, uom.symbol AS uom_symbol
        FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " c
        JOIN " . ScheduleOfRateItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
        WHERE c.root_id = p.root_id AND c.type <> " . ScheduleOfRateItem::TYPE_HEADER . "
        AND c.id IN (" . implode(',', $scheduleOfRateItemIds) . ") AND p.trade_id = " . $trade->id . "
        AND c.deleted_at IS NULL AND p.deleted_at IS NULL
        ORDER BY p.priority, p.lft, p.level ASC");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $records as $key => $record )
        {
            $multiItemMarkup = false;
            $multiRate       = false;
            $totalQty        = 0;
            $totalCost       = 0;

            foreach ( array( 'rate', 'item_markup' ) as $formulatedColumnConstant )
            {
                $records[$key][$formulatedColumnConstant . '-value']       = '';
                $records[$key][$formulatedColumnConstant . '-final_value'] = 0;
                $records[$key][$formulatedColumnConstant . '-linked']      = false;
                $records[$key][$formulatedColumnConstant . '-has_formula'] = false;
                $records[$key][$formulatedColumnConstant . '-has_build_up'] = false;
            }

            /*
            * getting bill item markup and sor rate
            */
            if ( $record['type'] == ScheduleOfRateItem::TYPE_WORK_ITEM )
            {
                $stmt = $pdo->prepare("SELECT DISTINCT COALESCE(markup_column.final_value, 0) AS value
                FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
                JOIN " . BillElementTable::getInstance()->getTableName() . " AS be ON be.project_structure_id = s.id
                JOIN " . BillItemTable::getInstance()->getTableName() . " AS bi ON bi.element_id = be.id
                JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " markup_column ON markup_column.relation_id = bi.id
                JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " ifc
                ON markup_column.relation_id = ifc.relation_id
                JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sorifc
                ON ifc.schedule_of_rate_item_formulated_column_id = sorifc.id
                WHERE s.root_id = " . $project->id . " AND sorifc.relation_id = " . $record['id'] . " AND markup_column.column_name = '" . BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
                AND s.deleted_at IS NULL AND be.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL
                AND markup_column.deleted_at IS NULL AND ifc.deleted_at IS NULL AND sorifc.deleted_at IS NULL");

                $stmt->execute();

                if ( $stmt->rowCount() > 1 )
                {
                    $multiItemMarkup = true;
                }
                else
                {
                    $markup = $stmt->fetch(PDO::FETCH_ASSOC);

                    $records[$key]['item_markup-value']       = $markup['value'];
                    $records[$key]['item_markup-final_value'] = $markup['value'];
                }

                $stmt = $pdo->prepare("SELECT DISTINCT COALESCE(ifc.final_value, 0) AS value, sorifc.has_build_up
                FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
                JOIN " . BillElementTable::getInstance()->getTableName() . " AS be ON be.project_structure_id = s.id
                JOIN " . BillItemTable::getInstance()->getTableName() . " AS bi ON bi.element_id = be.id
                JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " ifc ON ifc.relation_id = bi.id
                JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sorifc
                ON ifc.schedule_of_rate_item_formulated_column_id = sorifc.id
                WHERE s.root_id = " . $project->id . " AND sorifc.relation_id = " . $record['id'] . " AND ifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
                AND s.deleted_at IS NULL AND be.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL
                AND ifc.deleted_at IS NULL AND sorifc.deleted_at IS NULL");

                $stmt->execute();

                $rate = $stmt->fetch(PDO::FETCH_ASSOC);

                $records[$key]['rate-has_build_up'] = $rate['has_build_up'];

                if ( $stmt->rowCount() > 1 )
                {
                    $multiRate = true;
                }
                else
                {
                    $records[$key]['rate-value']       = $rate['value'];
                    $records[$key]['rate-final_value'] = $rate['value'];
                }

                list( $totalQty, $totalCost ) = ScheduleOfRateItemTable::calculateTotalCostForAnalysis($record['id'], $project->id);
            }

            $records[$key]['view_bill_item_all']      = $record['id'];
            $records[$key]['view_bill_item_drill_in'] = $record['id'];
            $records[$key]['multi-rate']              = $multiRate;
            $records[$key]['multi-item_markup']       = $multiItemMarkup;
            $records[$key]['total_qty']               = $totalQty;
            $records[$key]['total_cost']              = $totalCost;
        }

        $emptyRow = array(
            'id'                      => Constants::GRID_LAST_ROW,
            'description'             => '',
            'uom_id'                  => - 1,
            'uom_symbol'              => '',
            'multi-rate'              => false,
            'multi-item_markup'       => false,
            'total_qty'               => 0,
            'total_cost'              => 0,
            'view_bill_item_all'      => - 1,
            'view_bill_item_drill_in' => - 1
        );

        foreach ( array( 'rate', 'item_markup' ) as $formulatedColumnConstant )
        {
            $emptyRow[$formulatedColumnConstant . '-value']       = '';
            $emptyRow[$formulatedColumnConstant . '-final_value'] = 0;
            $emptyRow[$formulatedColumnConstant . '-linked']      = false;
            $emptyRow[$formulatedColumnConstant . '-has_formula'] = false;
        }

        array_push($records, $emptyRow);

        return $this->renderJson(array(
            'identifier'     => 'id',
            'items'          => $records,
            'sum_total_qty'  => $sumTotalQuantity,
            'sum_total_cost' => $sumTotalCost
        ));
    }

    public function executeScheduleOfRateItemUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $scheduleOfRateItem = ScheduleOfRateItemTable::getInstance()->find($request->getParameter('id')) and
            $request->hasParameter('unsorted') and $request->hasParameter('attr_name')
        );

        $val = is_numeric($request->getParameter('val')) ? $request->getParameter('val') : 0;

        $fieldName = $request->getParameter('attr_name');

        switch($fieldName)
        {
            case "rate":
                $scheduleOfRateItem->updateBillItemRatesFromAnalysis($val, $project);
                $multiField = 'multi-rate';
                break;
            case "item_markup":
                $scheduleOfRateItem->updateBillItemMarkupFromAnalysis($val, $project);
                $multiField = 'multi-item_markup';
                break;
            default:
                $multiField = "";
                break;
        }

        return $this->renderJson(array(
            'success' => true,
            'data'    => array(
                'id'                        => $scheduleOfRateItem->id,
                $fieldName . '-value'       => $val,
                $fieldName . '-final_value' => $val,
                $multiField                 => false,
                'affected_nodes'            => array()
            )
        ));
    }

    public function executeGetSorBillElements(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo          = $project->getTable()->getConnection()->getDbh();
        $sumTotalCost = 0;
        $results      = array();

        /*
        * select bills
        */
        $stmt = $pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft
        FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
        JOIN " . BillElementTable::getInstance()->getTableName() . " AS be ON s.id = be.project_structure_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id
        WHERE s.root_id = " . $project->id . " AND bi.id NOT IN (
            SELECT DISTINCT relation_id FROM " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " WHERE schedule_of_rate_item_formulated_column_id IS NOT NULL AND deleted_at IS NULL
        )
        AND bi.type <> " . BillItem::TYPE_HEADER . " AND bi.type <> " . BillItem::TYPE_NOID . " AND bi.type <> " . BillItem::TYPE_ITEM_PC_RATE . " AND bi.type <> " . BillItem::TYPE_HEADER_N . "
        AND bi.type <> " . BillItem::TYPE_ITEM_LUMP_SUM . " AND bi.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . " AND bi.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE . " AND bi.type <> " . BillItem::TYPE_ITEM_RATE_ONLY . "
        AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL AND s.deleted_at IS NULL ORDER BY s.lft ASC");

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalCostRecords = BillElementTable::calculateTotalForScheduleOfRateAnalysisByProject($project);

        foreach ( $bills as $bill )
        {
            array_push($results, array(
                'id'          => 'bill-' . $bill['id'],
                'description' => $bill['title'],
                'type'        => - 1
            ));

            //selectDistinctElementSql
            $stmt = $pdo->prepare("SELECT DISTINCT be.id, be.description, be.priority
            FROM " . BillElementTable::getInstance()->getTableName() . " AS be
            JOIN " . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id
            WHERE be.project_structure_id = " . $bill['id'] . " AND bi.id NOT IN (
                SELECT DISTINCT relation_id FROM " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " WHERE schedule_of_rate_item_formulated_column_id IS NOT NULL AND deleted_at IS NULL
            )
            AND bi.type <> " . BillItem::TYPE_HEADER . " AND bi.type <> " . BillItem::TYPE_NOID . " AND bi.type <> " . BillItem::TYPE_ITEM_PC_RATE . " AND bi.type <> " . BillItem::TYPE_HEADER_N . "
            AND bi.type <> " . BillItem::TYPE_ITEM_LUMP_SUM . " AND bi.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . " AND bi.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE . " AND bi.type <> " . BillItem::TYPE_ITEM_RATE_ONLY . "
            AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL ORDER BY be.priority ASC");

            $stmt->execute();

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $elements as $element )
            {
                $element['type'] = 1;//to display in grid with different color

                if ( array_key_exists($element['id'], $totalCostRecords) )
                {
                    $totalCost = $totalCostRecords[$element['id']][0];

                    unset( $totalCostRecords[$element['id']] );

                    $sumTotalCost += $totalCost;
                }
                else
                {
                    $totalCost = 0;
                }

                $element['total_cost'] = $totalCost;

                array_push($results, $element);
            }

            unset( $elements, $bill );
        }

        array_push($results, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'type'        => 1
        ));

        return $this->renderJson(array(
            'identifier'     => 'id',
            'items'          => $results,
            'sum_total_cost' => $sumTotalCost
        ));
    }

    public function executeGetSorBillItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('sid')) and
            $scheduleOfRateTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('tid')) and
            $scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id'))
        );

        $pdo              = $project->getTable()->getConnection()->getDbh();
        $sumTotalQuantity = 0;
        $sumTotalCost     = 0;

        $formulatedColumnConstants = array(
            BillItem::FORMULATED_COLUMN_RATE,
            BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE
        );

        $results = array();
        $form    = new BaseForm();

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.grand_total, p.grand_total_quantity, p.level, p.priority, e.priority AS element_priority, p.lft, uom.symbol AS uom_symbol
        FROM " . BillItemTable::getInstance()->getTableName() . " c
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " AS uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
        JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON c.id = bifc.relation_id
        JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
        JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
        JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON p.element_id = e.id
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND c.root_id = p.root_id AND c.element_id = p.element_id
        AND ifc.relation_id = " . $scheduleOfRateItem->id . " AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
        AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
        AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
        AND e.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND s.deleted_at IS NULL
        ORDER BY e.priority, p.priority, p.lft, p.level ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT bifc.relation_id, bifc.column_name, bifc.final_value, bifc.linked, bifc.has_build_up
        FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
        JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON s.id = e.project_structure_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " i  ON i.element_id = e.id
        JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON i.id = bifc.relation_id
        JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc2 ON bifc.relation_id = bifc2.relation_id
        JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc2.schedule_of_rate_item_formulated_column_id = ifc.id
        WHERE s.root_id = " . $project->id . "
        AND ifc.relation_id = " . $scheduleOfRateItem->id . " AND bifc.column_name <> '" . BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT . "'
        AND s.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND e.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bifc2.deleted_at IS NULL
        ORDER BY ifc.relation_id ASC");

        $stmt->execute();

        $formulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /*
        * select bills
        */
        $stmt = $pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS be ON s.id = be.project_structure_id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id JOIN
        " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON bi.id = bifc.relation_id JOIN
        " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
        WHERE s.root_id = " . $project->id . " AND ifc.relation_id = " . $scheduleOfRateItem->id . "
        AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
        AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
        AND s.deleted_at IS NULL ORDER BY s.lft ASC");

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $contractorRates = array();
        $companies       = array();

        if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
        {
            $tenderSetting = $project->TenderSetting;

            switch ($tenderSetting->contractor_sort_by)
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

            $awardedCompanyId = $tenderSetting->awarded_company_id > 0 ? $tenderSetting->awarded_company_id : - 1;

            $stmt = $pdo->prepare("SELECT c.id, c.name, COALESCE(SUM(r.grand_total), 0) AS total
            FROM " . CompanyTable::getInstance()->getTableName() . " c
            JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
            LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
            WHERE xref.project_structure_id = " . $project->id . "
            AND c.id <> " . $awardedCompanyId . " AND xref.show IS TRUE
            AND c.deleted_at IS NULL GROUP BY c.id ORDER BY " . $sqlOrder);

            $stmt->execute();

            $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ( $tenderSetting->awarded_company_id > 0 )
            {
                $awardedCompany = $tenderSetting->AwardedCompany;

                array_unshift($companies, array(
                    'id'   => $awardedCompany->id,
                    'name' => $awardedCompany->name
                ));

                unset( $awardedCompany );
            }

            $stmt = $pdo->prepare("SELECT tc.company_id, r.bill_item_id, r.rate FROM " . TenderBillItemRateTable::getInstance()->getTableName() . " r
            JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON r.tender_company_id = tc.id
            WHERE tc.project_structure_id = " . $project->id . " AND tc.show IS TRUE");

            $stmt->execute();

            $contractorRateRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $contractorRateRecords as $record )
            {
                if ( !array_key_exists($record['company_id'], $contractorRates) )
                {
                    $contractorRates[$record['company_id']] = array();
                }

                if ( !array_key_exists($record['bill_item_id'], $contractorRates[$record['company_id']]) )
                {
                    $contractorRates[$record['company_id']][$record['bill_item_id']] = 0;
                }

                $contractorRates[$record['company_id']][$record['bill_item_id']] = $record['rate'];

                unset( $record );
            }

            unset( $contractorRateRecords );
        }

        foreach ( $bills as $bill )
        {
            $stmt = $pdo->prepare("SELECT DISTINCT be.id, be.description, be.priority FROM
            " . BillElementTable::getInstance()->getTableName() . " AS be JOIN
            " . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id JOIN
            " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON bi.id = bifc.relation_id JOIN
            " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
            WHERE be.project_structure_id = " . $bill['id'] . " AND ifc.relation_id = " . $scheduleOfRateItem->id . "
            AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
            AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
            ORDER BY be.priority ASC");

            $stmt->execute();

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $elements as $element )
            {
                $result = array(
                    'id'          => 'bill-' . $bill['id'] . '-elem' . $element['id'],
                    'description' => $bill['title'] . " > " . $element['description'],
                    'type'        => - 1,
                    'level'       => 0,
                    'uom_id'      => - 1,
                    'uom_symbol'  => ''
                );

                foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
                {
                    $result[$formulatedColumnConstant . '-value']       = '';
                    $result[$formulatedColumnConstant . '-final_value'] = 0;
                    $result[$formulatedColumnConstant . '-linked']      = false;
                    $result[$formulatedColumnConstant . '-has_formula'] = false;
                }

                if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
                {
                    foreach ( $companies as $company )
                    {
                        $result['contractor_rate-' . $company['id'] . '-value']       = '';
                        $result['contractor_rate-' . $company['id'] . '-final_value'] = number_format(0, 2, '.', '');
                        $result['contractor_rate-' . $company['id'] . '-has_formula'] = false;
                    }
                }

                array_push($results, $result);

                $billItem = array( 'id' => - 1 );

                foreach ( $items as $k => $item )
                {
                    if ( $billItem['id'] != $item['id'] && $item['element_id'] == $element['id'] )
                    {
                        $billItem['id']                   = $item['id'];
                        $billItem['description']          = $item['description'];
                        $billItem['type']                 = $item['type'];
                        $billItem['grand_total']          = $item['grand_total'];
                        $billItem['grand_total_quantity'] = $item['grand_total_quantity'];
                        $billItem['level']                = $item['level'];
                        $billItem['uom_symbol']           = $item['uom_id'] > 0 ? $item['uom_symbol'] : '';
                        $billItem['_csrf_token']          = $form->getCSRFToken();

                        foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
                        {
                            $billItem[$formulatedColumnConstant . '-value']        = '';
                            $billItem[$formulatedColumnConstant . '-final_value']  = 0;
                            $billItem[$formulatedColumnConstant . '-linked']       = false;
                            $billItem[$formulatedColumnConstant . '-has_formula']  = false;
                            $billItem[$formulatedColumnConstant . '-has_build_up'] = false;
                        }

                        foreach ( $formulatedColumns as $key => $formulatedColumn )
                        {
                            if ( $formulatedColumn['relation_id'] == $item['id'] )
                            {
                                $columnName                              = $formulatedColumn['column_name'];
                                $billItem[$columnName . '-value']        = $formulatedColumn['final_value'];
                                $billItem[$columnName . '-final_value']  = $formulatedColumn['final_value'];
                                $billItem[$columnName . '-linked']       = $formulatedColumn['linked'];
                                $billItem[$columnName . '-has_formula']  = false;
                                $billItem[$columnName . '-has_build_up'] = $formulatedColumn['has_build_up'];

                                unset( $formulatedColumn, $formulatedColumns[$key] );
                            }
                        }

                        if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
                        {
                            foreach ( $companies as $company )
                            {
                                if ( array_key_exists($company['id'], $contractorRates) and array_key_exists($item['id'], $contractorRates[$company['id']]) )
                                {
                                    $rate = $contractorRates[$company['id']][$item['id']];
                                }
                                else
                                {
                                    $rate = number_format(0, 2, '.', '');
                                }

                                $billItem['contractor_rate-' . $company['id'] . '-value']       = $rate;
                                $billItem['contractor_rate-' . $company['id'] . '-final_value'] = $rate;
                                $billItem['contractor_rate-' . $company['id'] . '-has_formula'] = false;
                            }
                        }

                        array_push($results, $billItem);

                        unset( $items[$k], $item );
                    }
                }
            }
        }

        $emptyRow = array(
            'id'                   => Constants::GRID_LAST_ROW,
            'description'          => '',
            'type'                 => BillItem::TYPE_WORK_ITEM,
            'level'                => 0,
            'uom_id'               => - 1,
            'uom_symbol'           => '',
            'grand_total_quantity' => 0,
            'grand_total'          => 0
        );

        foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
        {
            $emptyRow[$formulatedColumnConstant . '-value']        = '';
            $emptyRow[$formulatedColumnConstant . '-final_value']  = 0;
            $emptyRow[$formulatedColumnConstant . '-linked']       = false;
            $emptyRow[$formulatedColumnConstant . '-has_formula']  = false;
            $emptyRow[$formulatedColumnConstant . '-has_build_up'] = false;
        }

        if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
        {
            foreach ( $companies as $company )
            {
                $emptyRow['contractor_rate-' . $company['id'] . '-value']       = '';
                $emptyRow['contractor_rate-' . $company['id'] . '-final_value'] = number_format(0, 2, '.', '');
                $emptyRow['contractor_rate-' . $company['id'] . '-has_formula'] = false;
            }
        }

        unset( $companies, $contractorRates );

        array_push($results, $emptyRow);

        return $this->renderJson(array(
            'identifier'     => 'id',
            'items'          => $results,
            'sum_total_qty'  => $sumTotalQuantity,
            'sum_total_cost' => $sumTotalCost
        ));
    }

    public function executeGetUnsortedSorBillItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('eid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($element->ProjectStructure->root_id)
        );

        $pdo              = $element->getTable()->getConnection()->getDbh();
        $sumTotalQuantity = 0;
        $sumTotalCost     = 0;

        $formulatedColumnConstants = array(
            BillItem::FORMULATED_COLUMN_RATE,
            BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE
        );

        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type, p.uom_id, p.grand_total, p.grand_total_quantity, p.level, p.priority, p.lft, uom.symbol AS uom_symbol
        FROM " . BillItemTable::getInstance()->getTableName() . " c
        JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " AS uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
        WHERE c.id NOT IN (
            SELECT DISTINCT ifc.relation_id FROM " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc WHERE ifc.schedule_of_rate_item_formulated_column_id IS NOT NULL AND deleted_at IS NULL
        )
        AND c.element_id = " . $element->id . " AND p.element_id = " . $element->id . " AND c.root_id = p.root_id AND c.element_id = p.element_id
        AND c.type <> " . BillItem::TYPE_HEADER . " AND c.type <> " . BillItem::TYPE_NOID . " AND c.type <> " . BillItem::TYPE_ITEM_PC_RATE . " AND c.type <> " . BillItem::TYPE_HEADER_N . "
        AND c.type <> " . BillItem::TYPE_ITEM_LUMP_SUM . " AND c.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . " AND c.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE . " AND c.type <> " . BillItem::TYPE_ITEM_RATE_ONLY . "
        AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
        AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
        ORDER BY p.priority, p.lft, p.level ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT bifc.relation_id, bifc.column_name, bifc.final_value, bifc.linked, bifc.has_build_up
        FROM " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON bifc.relation_id = i.id
        JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id
        WHERE e.id = " . $element->id . "
        AND i.type <> " . BillItem::TYPE_HEADER . " AND i.type <> " . BillItem::TYPE_NOID . " AND i.type <> " . BillItem::TYPE_ITEM_PC_RATE . " AND i.type <> " . BillItem::TYPE_HEADER_N . "
        AND i.type <> " . BillItem::TYPE_ITEM_LUMP_SUM . " AND i.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . " AND i.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE . " AND i.type <> " . BillItem::TYPE_ITEM_RATE_ONLY . "
        AND bifc.schedule_of_rate_item_formulated_column_id IS NULL
        AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND e.deleted_at IS NULL AND bifc.deleted_at IS NULL
        ORDER BY i.id ASC");

        $stmt->execute();

        $formulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $contractorRates = array();
        $companies       = array();

        if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
        {
            $tenderSetting = $project->TenderSetting;

            switch ($tenderSetting->contractor_sort_by)
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

            $awardedCompanyId = $tenderSetting->awarded_company_id > 0 ? $tenderSetting->awarded_company_id : - 1;

            $stmt = $pdo->prepare("SELECT c.id, c.name, COALESCE(SUM(r.grand_total), 0) AS total
            FROM " . CompanyTable::getInstance()->getTableName() . " c
            JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
            LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
            WHERE xref.project_structure_id = " . $project->id . "
            AND c.id <> " . $awardedCompanyId . " AND xref.show IS TRUE
            AND c.deleted_at IS NULL GROUP BY c.id ORDER BY " . $sqlOrder);

            $stmt->execute();

            $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ( $tenderSetting->awarded_company_id > 0 )
            {
                $awardedCompany = $tenderSetting->AwardedCompany;

                array_unshift($companies, array(
                    'id'   => $awardedCompany->id,
                    'name' => $awardedCompany->name
                ));

                unset( $awardedCompany );
            }

            $stmt = $pdo->prepare("SELECT tc.company_id, r.bill_item_id, r.rate FROM " . TenderBillItemRateTable::getInstance()->getTableName() . " r
            JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON r.tender_company_id = tc.id
            WHERE tc.project_structure_id = " . $project->id . " AND tc.show IS TRUE");

            $stmt->execute();

            $contractorRateRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $contractorRateRecords as $record )
            {
                if ( !array_key_exists($record['company_id'], $contractorRates) )
                {
                    $contractorRates[$record['company_id']] = array();
                }

                if ( !array_key_exists($record['bill_item_id'], $contractorRates[$record['company_id']]) )
                {
                    $contractorRates[$record['company_id']][$record['bill_item_id']] = 0;
                }

                $contractorRates[$record['company_id']][$record['bill_item_id']] = $record['rate'];

                unset( $record );
            }

            unset( $contractorRateRecords );
        }

        foreach ( $items as $key => $item )
        {
            $items[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
            {
                $items[$key][$formulatedColumnConstant . '-value']        = '';
                $items[$key][$formulatedColumnConstant . '-final_value']  = 0;
                $items[$key][$formulatedColumnConstant . '-linked']       = false;
                $items[$key][$formulatedColumnConstant . '-has_formula']  = false;
                $items[$key][$formulatedColumnConstant . '-has_build_up'] = false;
            }

            foreach ( $formulatedColumns as $k => $formulatedColumn )
            {
                if ( $formulatedColumn['relation_id'] == $item['id'] )
                {
                    $columnName                                 = $formulatedColumn['column_name'];
                    $items[$key][$columnName . '-value']        = $formulatedColumn['final_value'];
                    $items[$key][$columnName . '-final_value']  = $formulatedColumn['final_value'];
                    $items[$key][$columnName . '-linked']       = $formulatedColumn['linked'];
                    $items[$key][$columnName . '-has_formula']  = false;
                    $items[$key][$columnName . '-has_build_up'] = $formulatedColumn['has_build_up'];

                    unset( $formulatedColumn, $formulatedColumns[$k] );
                }
            }

            if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
            {
                foreach ( $companies as $company )
                {
                    if ( array_key_exists($company['id'], $contractorRates) and array_key_exists($item['id'], $contractorRates[$company['id']]) )
                    {
                        $rate = $contractorRates[$company['id']][$item['id']];
                    }
                    else
                    {
                        $rate = number_format(0, 2, '.', '');
                    }

                    $items[$key]['contractor_rate-' . $company['id'] . '-value']       = $rate;
                    $items[$key]['contractor_rate-' . $company['id'] . '-final_value'] = $rate;
                    $items[$key]['contractor_rate-' . $company['id'] . '-has_formula'] = false;
                }
            }
        }

        $emptyRow = array(
            'id'                   => Constants::GRID_LAST_ROW,
            'description'          => '',
            'type'                 => BillItem::TYPE_WORK_ITEM,
            'level'                => 0,
            'uom_id'               => - 1,
            'uom_symbol'           => '',
            'grand_total_quantity' => 0,
            'grand_total'          => 0
        );

        foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
        {
            $emptyRow[$formulatedColumnConstant . '-value']        = '';
            $emptyRow[$formulatedColumnConstant . '-final_value']  = 0;
            $emptyRow[$formulatedColumnConstant . '-linked']       = false;
            $emptyRow[$formulatedColumnConstant . '-has_formula']  = false;
            $emptyRow[$formulatedColumnConstant . '-has_build_up'] = false;
        }

        if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
        {
            foreach ( $companies as $company )
            {
                $emptyRow['contractor_rate-' . $company['id'] . '-value']       = '';
                $emptyRow['contractor_rate-' . $company['id'] . '-final_value'] = number_format(0, 2, '.', '');
                $emptyRow['contractor_rate-' . $company['id'] . '-has_formula'] = false;
            }
        }

        unset( $companies, $contractorRates );

        array_push($items, $emptyRow);

        return $this->renderJson(array(
            'identifier'     => 'id',
            'items'          => $items,
            'sum_total_qty'  => $sumTotalQuantity,
            'sum_total_cost' => $sumTotalCost
        ));
    }

    public function executeBuildUpRateResourceList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $records = $billItem->getBuildUpRateResourceList();

        foreach ( $records as $key => $record )
        {
            $records[$key]['total_build_up'] = $billItem->calculateBuildUpTotalByResourceId($record['id']);

            unset( $record );
        }

        return $this->renderJson($records);
    }

    public function executeGetBuildUpRateItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and
            $resource = Doctrine_Core::getTable('BillBuildUpRateResource')->find($request->getParameter('resource_id'))
        );

        $form = new BaseForm();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillBuildUpRateItem');

        $records = array();

        foreach($billItem->getBuildUpRateItemList($resource) as $item)
        {
            $item['_csrf_token'] = $form->getCSRFToken();

            array_push($records, $item);
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'uom_id'      => '-1',
            'uom_symbol'  => '',
            'relation_id' => $billItem->id,
            'total'       => '',
            'line_total'  => '',
            'linked'      => false,
            '_csrf_token' => $form->getCSRFToken()
        );

        foreach($formulatedColumnConstants as $constant)
        {
            $defaultLastRow[ $constant . '-final_value' ] = "";
            $defaultLastRow[ $constant . '-value' ]       = "";
            $defaultLastRow[ $constant . '-linked' ]      = false;
            $defaultLastRow[ $constant . '-has_formula' ] = false;
        }

        array_push($records, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeBuildUpRateItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('BillBuildUpRateItem')->find($request->getParameter('id'))
        );

        $rowData = array();
        $con     = $item->getTable()->getConnection();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillBuildUpRateItem');
        $totalBuildUp              = 0;

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $affectedNodes = array();

            if ( in_array($fieldName, $formulatedColumnConstants) )
            {
                $formulatedColumnTable = Doctrine_Core::getTable('BillBuildUpRateFormulatedColumn');

                $formulatedColumn = $formulatedColumnTable->getByRelationIdAndColumnName($item->id, $fieldName);

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->parentSave($con);

                $formulatedColumn->refresh();

                $con->commit();

                $item->refresh();

                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                foreach ( $referencedNodes as $referencedNode )
                {
                    $node = $formulatedColumnTable->find($referencedNode['node_from']);

                    if ( $node )
                    {
                        $total     = $node->BuildUpRateItem->calculateTotal();
                        $lineTotal = $node->BuildUpRateItem->calculateLineTotal();

                        array_push($affectedNodes, array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value,
                            'total'                     => $total,
                            'line_total'                => $lineTotal
                        ));
                    }
                }

                $rowData[$fieldName . "-final_value"] = $formulatedColumn->final_value;
                $rowData[$fieldName . "-value"]       = $formulatedColumn->value;

                $rowData['total']      = $item->calculateTotal();
                $rowData['line_total'] = $item->calculateLineTotal();

                $rowData['affected_nodes'] = $affectedNodes;

                $totalBuildUp = $item->BillItem->calculateBuildUpTotalByResourceId($item->build_up_rate_resource_id);
            }

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'        => $success,
            'errorMsg'       => $errorMsg,
            'total_build_up' => $totalBuildUp,
            'data'           => $rowData
        ));
    }

    public function executeScheduleOfRateBillItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $rowData       = array();
        $affectedNodes = array();

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldAttr        = explode('-', $request->getParameter('attr_name'));
            $isContractorRate = false;

            if ( count($fieldAttr) > 1 )
            {
                if ( $fieldAttr[0] == 'contractor_rate' )
                {
                    $project          = Doctrine_Core::getTable('ProjectStructure')->find($item->Element->ProjectStructure->root_id);
                    $isContractorRate = true;

                    $this->forward404Unless(
                        $contractor = Doctrine_Core::getTable('Company')->find($fieldAttr[1]) and
                        $tenderCompany = TenderCompanyTable::getByProjectIdAndCompanyId($project->id, $contractor->id)
                    );

                    if ( !$contractorRate = $tenderCompany->getBillItemRateByBillItemId($item->id) )
                    {
                        $contractorRate                    = new TenderBillItemRate();
                        $contractorRate->tender_company_id = $tenderCompany;
                        $contractorRate->bill_item_id      = $item->id;
                        $contractorRate->save($con);
                    }
                }
                else
                {
                    $fieldName = $fieldAttr[1];
                }
            }
            else
            {
                $fieldName = $fieldAttr[0];
            }

            $fieldValue = trim($request->getParameter('val'));

            $pattern = '/r[\d{1,}]+/i';

            $match = preg_match_all($pattern, $fieldValue, $matches, PREG_PATTERN_ORDER);

            $fieldValue = $match ? 0 : $fieldValue;//don't allow row linking in here, so we just set value to 0 if there any

            if ( $isContractorRate )
            {
                $rate                 = (double) $fieldValue;
                $contractorRate->rate = number_format($rate, 2, '.', '');

                $contractorRate->save($con);
            }
            else
            {
                $formulatedColumn = BillItemFormulatedColumnTable::getInstance()->getByRelationIdAndColumnName($item->id, $fieldName);

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->parentSave($con);

                $formulatedColumn->refresh();
            }

            $con->commit();

            if ( $isContractorRate )
            {
                $rowData['id']                                                  = $item->id;
                $rowData['contractor_rate-' . $contractor->id . '-value']       = $contractorRate->rate;
                $rowData['contractor_rate-' . $contractor->id . '-final_value'] = $contractorRate->rate;
            }
            else
            {
                $rowData['id']                        = $item->id;
                $rowData[$fieldName . '-value']       = $formulatedColumn->final_value;
                $rowData[$fieldName . '-final_value'] = $formulatedColumn->final_value;

                $item->updateBillItemTotalColumns();

                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                foreach ( $referencedNodes as $referencedNode )
                {
                    $node = BillItemFormulatedColumnTable::getInstance()->find($referencedNode['node_from']);

                    if ( $node )
                    {
                        $affectedBillItem = $node->BillItem;
                        $affectedBillItem->updateBillItemTotalColumns();

                        array_push($affectedNodes, array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value,
                            'grand_total_quantity'      => $affectedBillItem->grand_total_quantity,
                            'grand_total'               => $affectedBillItem->grand_total
                        ));
                    }

                    unset( $affectedBillItem, $node );
                }

                $rowData['grand_total'] = $item->grand_total;
            }

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeGetContractors(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $tenderSetting = $project->TenderSetting;

        $pdo = $project->getTable()->getConnection()->getDbh();

        switch ($tenderSetting->contractor_sort_by)
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

        $awardedCompanyId = $tenderSetting->awarded_company_id > 0 ? $tenderSetting->awarded_company_id : - 1;

        $stmt = $pdo->prepare("SELECT c.id, c.name, COALESCE(SUM(r.grand_total), 0) AS total
        FROM " . CompanyTable::getInstance()->getTableName() . " c
        JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
        LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
        WHERE xref.project_structure_id = " . $project->id . "
        AND c.id <> " . $awardedCompanyId . " AND xref.show IS TRUE
        AND c.deleted_at IS NULL GROUP BY c.id ORDER BY " . $sqlOrder);

        $stmt->execute();

        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $companies as $key => $company )
        {
            $companies[$key]['awarded'] = false;

            unset( $company );
        }

        if ( $tenderSetting->awarded_company_id > 0 )
        {
            $awardedCompany = $tenderSetting->AwardedCompany;

            array_unshift($companies, array(
                'id'      => $awardedCompany->id,
                'name'    => $awardedCompany->name,
                'awarded' => true
            ));

            unset( $awardedCompany );
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $companies
        ));
    }
}
