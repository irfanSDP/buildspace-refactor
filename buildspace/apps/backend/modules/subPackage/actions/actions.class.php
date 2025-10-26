<?php

/**
 * subPackage actions.
 *
 * @package    buildspace
 * @subpackage subPackage
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subPackageActions extends BaseActions {

    public function executeGetSubPackageList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id'))
        );

        $pdo           = $project->getTable()->getConnection()->getDbh();
        $billItemRates = SubPackageTable::getBillItemRates($project);

        $newSelectedAmounts   = [];
        $subPackageEstAmounts = [];
        $billItemQuantities   = [];

        foreach($billItemRates as $subPackageId => $byResources)
        {
            if(!array_key_exists($subPackageId, $subPackageEstAmounts))
            {
                $subPackageEstAmounts[$subPackageId] = 0;
            }

            foreach($byResources as $resourceId => $byBills)
            {
                foreach($byBills as $elementId => $byItems)
                {
                    foreach($byItems as $itemId => $data)
                    {
                        if(!array_key_exists($itemId, $billItemQuantities))
                        {
                            $billItemQuantities[$itemId] = $data['total_qty'];
                        }

                        if(array_key_exists($subPackageId, $subPackageEstAmounts))
                        {
                            $subPackageEstAmounts[$subPackageId] += $data['total_cost_after_conversion']*$data['total_qty'];
                        }
                    }
                }
                
                unset($billItemRates[$subPackageId][$resourceId]);
            }
        }

        $billItemRates = SubPackageTable::getNoBuildUpBillItems($project);

        foreach($billItemRates as $subPackageId => $byBills)
        {
            if(!array_key_exists($subPackageId, $subPackageEstAmounts))
            {
                $subPackageEstAmounts[$subPackageId] = 0;
            }

            foreach($byBills as $billId => $byItems)
            {
                foreach($byItems as $itemId => $data)
                {

                    if(!array_key_exists($itemId, $billItemQuantities))
                    {
                        $billItemQuantities[$itemId] = $data['total_qty'];
                    }
                    
                    if(array_key_exists($subPackageId, $subPackageEstAmounts))
                    {
                        $subPackageEstAmounts[$subPackageId] += $data['rate']*$data['total_qty'];
                    }
                }

                unset($billItemRates[$subPackageId][$billId]);
            }
        }

        $records = DoctrineQuery::create()
            ->select('s.id, s.name, s.locked, s.selected_company_id')
            ->from('SubPackage s')
            ->andWhere('s.project_structure_id = ?', $project->id)
            ->andWhere('s.locked IS NOT TRUE')
            ->addOrderBy('s.priority ASC')
            ->fetchArray();

        $stmt = $pdo->prepare("SELECT DISTINCT sp.id, i.id AS bill_item_id, x.company_id, rate.rate
        FROM " . SubPackageTable::getInstance()->getTableName() . " sp
        JOIN " . SubPackageCompanyTable::getInstance()->getTableName() . " x ON x.sub_package_id = sp.id AND sp.selected_company_id = x.company_id
        JOIN " . SubPackageBillItemRateTable::getInstance()->getTableName() . " rate ON rate.sub_package_company_id = x.id
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.id = rate.bill_item_id
        LEFT JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON bur.bill_item_id = i.id AND bur.deleted_at IS NULL
        LEFT JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " AS xref ON bur.resource_item_library_id = xref.resource_item_id AND xref.sub_package_id = sp.id
        LEFT JOIN " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " ifc ON ifc.relation_id  = bur.id AND ifc.deleted_at IS NULL
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_RATE . "' AND ifc.final_value <> 0
        LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON bifc.relation_id = i.id
        LEFT JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sifc ON sifc.id = bifc.schedule_of_rate_item_formulated_column_id
        LEFT JOIN " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS si ON si.id = sifc.relation_id
        LEFT JOIN " . SubPackageScheduleOfRateItemTable::getInstance()->getTableName() . " AS xref2 ON si.id = xref2.schedule_of_rate_item_id AND xref2.sub_package_id = sp.id
        LEFT JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
        LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = sp.id
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON e.project_structure_id = bill.id
        WHERE sp.project_structure_id = " . $project->id . " AND sp.selected_company_id IS NOT NULL AND sp.deleted_at IS NULL AND sp.locked IS NOT TRUE
        AND bill.root_id = " . $project->id . " AND bill.deleted_at IS NULL
        AND i.type != " . BillItem::TYPE_ITEM_NOT_LISTED . " AND NOT (xref.sub_package_id IS NULL AND xref2.sub_package_id IS NULL AND spbi.sub_package_id IS NULL)
        AND e.deleted_at IS NULL
        AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL");

        $stmt->execute();

        $selectedAmounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $selectedAmounts as $selectedAmount )
        {
            if ( !array_key_exists($selectedAmount['id'], $newSelectedAmounts) )
            {
                $newSelectedAmounts[$selectedAmount['id']] = array();
            }

            if ( !array_key_exists($selectedAmount['company_id'], $newSelectedAmounts[$selectedAmount['id']]) )
            {
                $newSelectedAmounts[$selectedAmount['id']][$selectedAmount['company_id']] = 0;
            }

            if(array_key_exists($selectedAmount['bill_item_id'], $billItemQuantities) && !empty($selectedAmount['rate']))
            {
                $newSelectedAmounts[$selectedAmount['id']][$selectedAmount['company_id']] += $selectedAmount['rate'] * $billItemQuantities[$selectedAmount['bill_item_id']];

            }
        }

        unset($billItemQuantities, $selectedAmounts);

        $form = new BaseForm();

        foreach ( $records as $key => $record )
        {
            $records[$key]['relation_id']     = $project->id;
            $records[$key]['est_amount']      = array_key_exists($record['id'], $subPackageEstAmounts) ? $subPackageEstAmounts[$record['id']] : 0;
            $records[$key]['selected_amount'] = isset( $newSelectedAmounts[$record['id']][$record['selected_company_id']] ) ? $newSelectedAmounts[$record['id']][$record['selected_company_id']] : 0;
            $records[$key]['_csrf_token']     = $form->getCSRFToken();

            unset( $record );
        }

        unset( $subPackageEstAmounts, $newSelectedAmounts );

        array_push($records, array(
            'id'                  => Constants::GRID_LAST_ROW,
            'name'                => '',
            'relation_id'         => $project->id,
            'est_amount'          => 0,
            'locked'              => false,
            'selected_company_id' => null,
            'selected_amount'     => 0,
            '_csrf_token'         => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetResourcesBySubPackage(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $resources            = $subPackage->getResources();
        $totalCostByResources = $subPackage->getEstimatedTotalByResources();

        $records   = [];
        $totalCost = 0;

        foreach($resources as $id => $name)
        {
            $records[] = [
                'id'    => $id,
                'name'  => $name,
                'total' => array_key_exists($id, $totalCostByResources) ? $totalCostByResources[$id] : 0
            ];

            $totalCost += array_key_exists($id, $totalCostByResources) ? $totalCostByResources[$id] : 0;
        }
        
        $noBuildUpTotal = $subPackage->getEstimatedTotalNoBuildUps();
        
        if($noBuildUpTotal)
        {
            $records[] = [
                'id'    => 'manual',
                'name'  => 'NO BUILD-UP',
                'total' => $noBuildUpTotal
            ];

            $totalCost += $noBuildUpTotal;
        }

        $records[] = [
            'id'    => '-1',
            'name'  => '',
            'total' => $totalCost
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeSubPackageAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $subPackage = new SubPackage();
        $con        = $subPackage->getTable()->getConnection();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $prevSubPackage = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('SubPackage')->find($request->getParameter('prev_item_id')) : null;

            $priority           = $prevSubPackage ? $prevSubPackage->priority + 1 : 0;
            $projectStructureId = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');
                $subPackage->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }
        }
        else
        {
            $this->forward404Unless($nextSubPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('before_id')));

            $priority           = $nextSubPackage->priority;
            $projectStructureId = $nextSubPackage->project_structure_id;
        }

        $items = array();
        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('SubPackage')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('project_structure_id = ?', $projectStructureId)
                ->execute();

            $subPackage->project_structure_id = $projectStructureId;
            $subPackage->priority             = $priority;

            $subPackage->save();

            SubPackageBillLayoutSettingTable::cloneExistingPrintingLayoutSettingsForSubPackage($subPackage->id);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $form = new BaseForm();

            $items[0]['id']                  = $subPackage->id;
            $items[0]['name']                = $subPackage->name;
            $items[0]['locked']              = $subPackage->locked;
            $items[0]['selected_company_id'] = $subPackage->selected_company_id;
            $items[0]['relation_id']         = $projectStructureId;
            $items[0]['est_amount']          = 0;
            $items[0]['_csrf_token']         = $form->getCSRFToken();

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'                  => Constants::GRID_LAST_ROW,
                    'name'                => '',
                    'locked'              => false,
                    'relation_id'         => $projectStructureId,
                    'selected_company_id' => null,
                    'est_amount'          => 0,
                    '_csrf_token'         => $form->getCSRFToken()
                ));
            }
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeSubPackageUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id')));

        $rowData = array();
        $con     = $subPackage->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $subPackage->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $subPackage->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $rowData = array(
                $fieldName => $subPackage->$fieldName,
            );
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeSubPackageDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        try
        {
            $item['id'] = $subPackage->id;
            $subPackage->delete();
            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $item     = array();
            $success  = false;
        }

        // return items need to be in array since grid js expect an array of items for delete operation (delete from store)
        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => array( $item ) ));
    }

    public function executeGetResourceList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $pdo     = $subPackage->getTable()->getConnection()->getDbh();
        $project = $subPackage->ProjectStructure;

        $stmt = $pdo->prepare("SELECT DISTINCT r_lib.id, r_lib.name FROM
        " . ResourceTable::getInstance()->getTableName() . " AS r_lib JOIN
        " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON r.resource_library_id = r_lib.id JOIN
        " . BillBuildUpRateResourceTradeTable::getInstance()->getTableName() . " AS t ON t.build_up_rate_resource_id = r.id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS i ON t.bill_item_id = i.id AND r.bill_item_id = i.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id JOIN
        " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.bill_item_id = i.id AND bur.build_up_rate_resource_id = r.id AND bur.build_up_rate_resource_trade_id = t.id JOIN
        " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON ifc.relation_id = bur.id
        WHERE s.root_id = " . $project->id . " AND bur.resource_item_library_id IS NOT NULL AND r.deleted_at IS NULL AND t.deleted_at IS NULL AND bur.deleted_at IS NULL
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0 AND ifc.deleted_at IS NULL
        AND i.project_revision_deleted_at IS NULL
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL ORDER BY r_lib.id");

        $stmt->execute();

        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

        array_push($resources, array(
            'id'   => Constants::GRID_LAST_ROW,
            'name' => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $resources
        ));
    }

    public function executeGetTradeList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id')) and
            $request->hasParameter('id')
        );

        $pdo     = $subPackage->getTable()->getConnection()->getDbh();
        $project = $subPackage->ProjectStructure;

        /*
         * Query resource obj using PDO instead of Doctrine because we want to get the record even when the resource has been flagged as deleted(soft delete)
         */
        $sth = $pdo->prepare("SELECT id FROM " . ResourceTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('rid'));

        $sth->execute();

        $this->forward404Unless($resource = $sth->fetch(PDO::FETCH_ASSOC));

        $stmt = $pdo->prepare("SELECT DISTINCT r_trade.id, r_trade.description, r_trade.priority FROM
        " . ResourceTradeTable::getInstance()->getTableName() . " r_trade JOIN
        " . BillBuildUpRateResourceTradeTable::getInstance()->getTableName() . " AS t ON r_trade.id = t.resource_trade_library_id JOIN
        " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON t.build_up_rate_resource_id = r.id JOIN
        " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id AND bur.build_up_rate_resource_trade_id = t.id JOIN
        " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON ifc.relation_id = bur.id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id IS NOT NULL
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0 AND ifc.deleted_at IS NULL
        AND r.deleted_at IS NULL AND t.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL ORDER BY r_trade.priority");

        $stmt->execute();

        $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach ( $trades as $key => $trade )
        {
            $trades[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($trades, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $trades
        ));
    }

    public function executeGetResourceItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id')) and
            $request->hasParameter('rid') and $request->hasParameter('tid')
        );

        $pdo           = $subPackage->getTable()->getConnection()->getDbh();
        $project       = $subPackage->ProjectStructure;
        $resourceItems = array();

        /*
         * Query resource obj using PDO instead of Doctrine because we want to get the record even when the resource has been flagged as deleted(soft delete)
         */
        $sth = $pdo->prepare("SELECT id FROM " . ResourceTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('rid'));
        $sth->execute();

        $this->forward404Unless($resource = $sth->fetch(PDO::FETCH_ASSOC));

        /*
        * Query resource trade obj using PDO instead of Doctrine because we want to get the record even when the resource trade has been flagged as deleted(soft delete)
        */
        $sth = $pdo->prepare("SELECT id FROM " . ResourceTradeTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('tid'));
        $sth->execute();

        $this->forward404Unless($trade = $sth->fetch(PDO::FETCH_ASSOC));

        $selectDistinctItemSql = "SELECT DISTINCT bur.id, bur.resource_item_library_id FROM
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
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL";

        $stmt = $pdo->prepare($selectDistinctItemSql);

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
            }

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type, p.uom_id, p.level, p.priority, p.lft, uom.symbol AS uom_symbol
            FROM " . ResourceItemTable::getInstance()->getTableName() . " c
            JOIN " . ResourceItemTable::getInstance()->getTableName() . " p
            ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE c.root_id = p.root_id AND c.type <> " . ResourceItem::TYPE_HEADER . "
            AND c.id IN (" . implode(',', $resourceItemLibraryIds) . ")
            AND c.resource_trade_id = " . $trade['id'] . " AND p.resource_trade_id = " . $trade['id'] . "
            ORDER BY p.root_id, p.priority, p.lft, p.level ASC");

            $stmt->execute();

            $resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $selectedResourceItems = array();

            foreach ( $subPackage->ResourceItems as $selectResourceItem )
            {
                $selectedResourceItems[] = $selectResourceItem->id;
            }

            foreach ( $resourceItems as $key => $resourceItem )
            {
                $resourceItems[$key]['type']     = (string) $resourceItem['type'];
                $resourceItems[$key]['selected'] = in_array($resourceItem['id'], $selectedResourceItems) ? true : false;
                unset( $resourceItem );
            }
        }

        array_push($resourceItems, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'uom_id'      => '-1',
            'uom_symbol'  => '',
            'type'        => (string)ResourceItem::TYPE_WORK_ITEM,
            'selected'    => false,
            'level'       => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $resourceItems
        ));
    }

    public function executeGetResourceDescendants(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('id'))
        );

        try
        {
            $items = DoctrineQuery::create()->select('i.id, i.description, i.type')
                ->from('ResourceItem i')
                ->andWhere('i.root_id = ?', $resourceItem->root_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $resourceItem->lft, $resourceItem->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

            $success  = true;
            $errorMsg = null;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
            $items    = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeGetScheduleOfRateDescendants(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id'))
        );

        try
        {
            $items = DoctrineQuery::create()->select('i.id, i.description, i.type')
                ->from('ScheduleOfRateItem i')
                ->andWhere('i.root_id = ?', $scheduleOfRateItem->root_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $scheduleOfRateItem->lft, $scheduleOfRateItem->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

            $success  = true;
            $errorMsg = null;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
            $items    = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeGetBillItemDescendants(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        try
        {
            $items = DoctrineQuery::create()->select('i.id, i.description, i.type')
                ->from('BillItem i')
                ->andWhere('i.root_id = ?', $billItem->root_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $billItem->lft, $billItem->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

            $success  = true;
            $errorMsg = null;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
            $items    = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeGetTypeUnitDescendants(sfWebRequest $request)
    {
        $explodedColumnSettingIds = explode('-', $request->getParameter('id'));

        $this->forward404Unless(
            $request->isXmlHttpRequest() and (count($explodedColumnSettingIds) == 2)
        );

        $columnSettingId = $explodedColumnSettingIds[1];
        $items           = array();

        try
        {
            //Get BillColumnSetting List
            $billColumnSetting = DoctrineQuery::create()->select('*')
                ->from('BillColumnSetting cs')
                ->where('cs.id = ? ', $columnSettingId)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->limit(1)
                ->fetchOne();

            $count = $billColumnSetting['quantity'];

            for ( $i = 1; $i <= $count; $i ++ )
            {
                array_push($items, array(
                    'id'          => $billColumnSetting['id'] . '-' . $i,
                    'relation_id' => $billColumnSetting['id'],
                    'level'       => 1
                ));
            }

            $success  = true;
            $errorMsg = null;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeGetScheduleOfRateList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $pdo     = $subPackage->getTable()->getConnection()->getDbh();
        $project = $subPackage->ProjectStructure;

        $stmt = $pdo->prepare("SELECT DISTINCT sor_lib.id, sor_lib.name FROM
        " . ScheduleOfRateTable::getInstance()->getTableName() . " AS sor_lib JOIN
        " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " AS sor_t ON sor_t.schedule_of_rate_id = sor_lib.id JOIN
        " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS sor_i ON sor_i.trade_id = sor_t.id JOIN
        " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sor_ifc ON sor_ifc.relation_id = sor_i.id JOIN
        " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON ifc.schedule_of_rate_item_formulated_column_id = sor_ifc.id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS i ON ifc.relation_id = i.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND sor_lib.deleted_at IS NULL AND sor_t.deleted_at IS NULL AND sor_i.deleted_at IS NULL
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_RATE . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0 AND ifc.deleted_at IS NULL
        AND i.project_revision_deleted_at IS NULL
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL ORDER BY sor_lib.id");

        $stmt->execute();

        $scheduleOfRates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        array_push($scheduleOfRates, array(
            'id'   => Constants::GRID_LAST_ROW,
            'name' => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $scheduleOfRates
        ));
    }

    public function executeGetScheduleOfRateTradeList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id')) and
            $request->hasParameter('sorid')
        );

        $pdo     = $subPackage->getTable()->getConnection()->getDbh();
        $project = $subPackage->ProjectStructure;

        /*
         * Query resource obj using PDO instead of Doctrine because we want to get the record even when the resource has been flagged as deleted(soft delete)
         */
        $sth = $pdo->prepare("SELECT id FROM " . ScheduleOfRateTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('sorid'));
        $sth->execute();

        $this->forward404Unless($scheduleOfRate = $sth->fetch(PDO::FETCH_ASSOC));

        $stmt = $pdo->prepare("SELECT DISTINCT sor_t.id, sor_t.description, sor_t.priority FROM
        " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " AS sor_t JOIN
        " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS sor_i ON sor_i.trade_id = sor_t.id JOIN
        " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sor_ifc ON sor_ifc.relation_id = sor_i.id JOIN
        " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON ifc.schedule_of_rate_item_formulated_column_id = sor_ifc.id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS i ON ifc.relation_id = i.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND sor_t.schedule_of_rate_id = " . $scheduleOfRate['id'] . " AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_RATE . "'
        AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0 AND ifc.deleted_at IS NULL
        AND sor_t.deleted_at IS NULL AND sor_i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL ORDER BY sor_t.priority");

        $stmt->execute();

        $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach ( $trades as $key => $trade )
        {
            $trades[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $trade );
        }

        array_push($trades, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'priority'    => 0,
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $trades
        ));
    }

    public function executeGetScheduleOfRateItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id')) and
            $request->hasParameter('sorid') and $request->hasParameter('tid')
        );

        $pdo                 = $subPackage->getTable()->getConnection()->getDbh();
        $project             = $subPackage->ProjectStructure;
        $scheduleOfRateItems = array();

        /*
         * Query resource obj using PDO instead of Doctrine because we want to get the record even when the resource has been flagged as deleted(soft delete)
         */
        $sth = $pdo->prepare("SELECT id FROM " . ScheduleOfRateTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('sorid'));
        $sth->execute();

        $this->forward404Unless($scheduleOfRate = $sth->fetch(PDO::FETCH_ASSOC));

        /*
        * Query resource trade obj using PDO instead of Doctrine because we want to get the record even when the resource trade has been flagged as deleted(soft delete)
        */
        $sth = $pdo->prepare("SELECT id FROM " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('tid'));
        $sth->execute();

        $this->forward404Unless($trade = $sth->fetch(PDO::FETCH_ASSOC));

        $selectDistinctItemSql = "SELECT DISTINCT i.id, sor_i.id AS schedule_of_rate_item_id FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS sor_i JOIN
        " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sor_ifc ON sor_ifc.relation_id = sor_i.id JOIN
        " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON ifc.schedule_of_rate_item_formulated_column_id = sor_ifc.id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS i ON ifc.relation_id = i.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND sor_i.trade_id = " . $trade['id'] . " AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_RATE . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0
        AND sor_i.deleted_at IS NULL AND sor_ifc.deleted_at IS NULL AND ifc.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL";

        $stmt = $pdo->prepare($selectDistinctItemSql);

        $stmt->execute();

        $itemWithScheduleOfRateIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ( count($itemWithScheduleOfRateIds) > 0 )
        {
            $buildUpRateItemIds    = array();
            $scheduleOfRateItemIds = array();

            foreach ( $itemWithScheduleOfRateIds as $record )
            {
                $buildUpRateItemIds[] = $record['id'];

                if ( !in_array($record['schedule_of_rate_item_id'], $scheduleOfRateItemIds) )
                {
                    $scheduleOfRateItemIds[] = $record['schedule_of_rate_item_id'];
                }
            }

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type, p.uom_id, p.level,
            p.priority, p.lft, uom.symbol AS uom_symbol
            FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " c
            JOIN " . ScheduleOfRateItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE c.root_id = p.root_id AND c.type <> " . ScheduleOfRateItem::TYPE_HEADER . "
            AND c.id IN (" . implode(',', $scheduleOfRateItemIds) . ") AND p.trade_id = " . $trade['id'] . "
            AND c.deleted_at IS NULL AND p.deleted_at IS NULL
            ORDER BY p.priority, p.lft, p.level ASC");

            $stmt->execute();

            $scheduleOfRateItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $selectedScheduleOfRateItems = array();

            foreach ( $subPackage->ScheduleOfRateItems as $selectScheduleOfRateItem )
            {
                $selectedScheduleOfRateItems[] = $selectScheduleOfRateItem->id;
            }

            foreach ( $scheduleOfRateItems as $key => $scheduleOfRateItem )
            {
                $scheduleOfRateItems[$key]['type']     = (string) $scheduleOfRateItem['type'];
                $scheduleOfRateItems[$key]['selected'] = in_array($scheduleOfRateItem['id'], $selectedScheduleOfRateItems) ? true : false;
                unset( $scheduleOfRateItem );
            }
        }

        array_push($scheduleOfRateItems, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'uom_id'      => '-1',
            'uom_symbol'  => '',
            'type'        => (string)ScheduleOfRateItem::TYPE_WORK_ITEM,
            'selected'    => false,
            'level'       => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $scheduleOfRateItems
        ));
    }

    public function executeImportScheduleOfRateItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        /*
        * Query resource trade obj using PDO instead of Doctrine because we want to get the record even when the resource trade has been flagged as deleted(soft delete)
        */
        $sth = $pdo->prepare("SELECT id FROM " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('tid'));
        $sth->execute();

        $this->forward404Unless($trade = $sth->fetch(PDO::FETCH_ASSOC));

        $errorMsg            = null;
        $scheduleOfRateItems = array();

        try
        {
            $ids                 = strlen($request->getParameter('ids')) > 0 ? Utilities::array_filter_integer(explode(',', $request->getParameter('ids'))) : array();
            $scheduleOfRateItems = $subPackage->importScheduleOfRateItems($ids, $trade['id']);
            $success             = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'         => $success,
            'errorMsg'        => $errorMsg,
            'items'           => $scheduleOfRateItems,
            'est_amount'      => $subPackage->getEstimationAmount(),
            'selected_amount' => $subPackage->getSelectedCompanySingleUnitTotalAmount()
        ));
    }

    public function executeImportResourceItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        /*
        * Query resource trade obj using PDO instead of Doctrine because we want to get the record even when the resource trade has been flagged as deleted(soft delete)
        */
        $sth = $pdo->prepare("SELECT id FROM " . ResourceTradeTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('tid'));
        $sth->execute();

        $this->forward404Unless($trade = $sth->fetch(PDO::FETCH_ASSOC));

        $errorMsg      = null;
        $resourceItems = array();

        try
        {
            $ids           = strlen($request->getParameter('ids')) > 0 ? Utilities::array_filter_integer(explode(',', $request->getParameter('ids'))) : array();
            $resourceItems = $subPackage->importResourceItems($ids, $trade['id']);
            $success       = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'         => $success,
            'errorMsg'        => $errorMsg,
            'items'           => $resourceItems,
            'est_amount'      => $subPackage->getEstimationAmount(),
            'selected_amount' => $subPackage->getSelectedCompanySingleUnitTotalAmount()
        ));
    }

    public function executeGetResourceItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT ri.id, ri.description, ri.uom_id, uom.symbol AS uom_symbol, ri.priority, ri.lft, ri.level
        FROM " . ResourceItemTable::getInstance()->getTableName() . " AS ri
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " AS uom ON ri.uom_id = uom.id AND uom.deleted_at IS NULL
        JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " AS xref ON xref.resource_item_id = ri.id
        JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON bur.resource_item_library_id = xref.resource_item_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON bur.bill_item_id = i.id
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
        WHERE xref.sub_package_id = " . $subPackage->id . " AND s.root_id = " . $subPackage->project_structure_id . "
        AND bur.deleted_at IS NULL AND e.deleted_at IS NULL
        AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        GROUP BY ri.id, uom.symbol
        ORDER BY ri.root_id, ri.priority, ri.lft, ri.level ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT xref.resource_item_id, bur.bill_item_id, c.id AS bill_column_setting_id, c.use_original_quantity, COALESCE(ifc.final_value, 0) AS final_value
        FROM " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " ifc
        JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON bur.id = ifc.relation_id
        JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " AS xref ON bur.resource_item_library_id = xref.resource_item_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON bur.bill_item_id = i.id
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
        JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " c ON c.project_structure_id = e.project_structure_id
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON c.project_structure_id = s.id
        WHERE xref.sub_package_id = " . $subPackage->id . " AND s.root_id = " . $subPackage->project_structure_id . "
        AND bur.deleted_at IS NULL AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_RATE . "'
        AND s.deleted_at IS NULL AND e.deleted_at IS NULL AND e.deleted_at IS NULL
        AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND c.deleted_at IS NULL AND ifc.deleted_at IS NULL AND ifc.final_value <> 0 ORDER BY bur.bill_item_id");

        $stmt->execute();

        $estimatedRateRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $billColumnSettingQuantities = array();
        $resourceItemEstAmounts      = array();

        foreach ( $estimatedRateRecords as $estimatedRateRecord )
        {
            if ( !array_key_exists($estimatedRateRecord['bill_item_id'] . '-' . $estimatedRateRecord['bill_column_setting_id'], $billColumnSettingQuantities) )
            {
                $billColumnSettingQuantities[$estimatedRateRecord['bill_item_id'] . '-' . $estimatedRateRecord['bill_column_setting_id']] = 0;

                $quantityFieldName = $estimatedRateRecord['use_original_quantity'] ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                $stmt = $pdo->prepare("SELECT COALESCE(fc.final_value, 0) AS value
                FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
                JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
                WHERE r.bill_item_id = " . $estimatedRateRecord['bill_item_id'] . " AND r.bill_column_setting_id = " . $estimatedRateRecord['bill_column_setting_id'] . "
                AND r.include IS TRUE AND fc.column_name = '" . $quantityFieldName . "' AND fc.final_value <> 0
                AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                $stmt->execute();

                $billColumnSettingQuantities[$estimatedRateRecord['bill_item_id'] . '-' . $estimatedRateRecord['bill_column_setting_id']] = $stmt->fetch(PDO::FETCH_COLUMN, 0);
            }

            if ( !array_key_exists($estimatedRateRecord['resource_item_id'], $resourceItemEstAmounts) )
            {
                $resourceItemEstAmounts[$estimatedRateRecord['resource_item_id']] = 0;
            }

            $resourceItemEstAmounts[$estimatedRateRecord['resource_item_id']] += $estimatedRateRecord['final_value'] * $billColumnSettingQuantities[$estimatedRateRecord['bill_item_id'] . '-' . $estimatedRateRecord['bill_column_setting_id']];
        }

        $stmt = $pdo->prepare("SELECT DISTINCT x.company_id, rate.bill_item_id, r.resource_item_id, rate.single_unit_grand_total
        FROM " . SubPackageCompanyTable::getInstance()->getTableName() . " AS x
        JOIN " . SubPackageBillItemRateTable::getInstance()->getTableName() . " AS rate ON rate.sub_package_company_id = x.id
        JOIN " . BillItemTable::getInstance()->getTableName() . " AS i ON rate.bill_item_id = i.id
        JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.bill_item_id = i.id
        JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " r ON r.sub_package_id = x.sub_package_id AND r.resource_item_id = bur.resource_item_library_id
        WHERE x.sub_package_id = " . $subPackage->id . "
        AND bur.resource_item_library_id IS NOT NULL
        AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND bur.deleted_at IS NULL ORDER BY r.resource_item_id ASC");

        $stmt->execute();

        $companyRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subContractorsAmounts = array();
        foreach ( $companyRecords as $companyRecord )
        {
            if ( !array_key_exists($companyRecord['resource_item_id'], $subContractorsAmounts) )
            {
                $subContractorsAmounts[$companyRecord['resource_item_id']] = array();
            }

            if ( !array_key_exists($companyRecord['company_id'], $subContractorsAmounts[$companyRecord['resource_item_id']]) )
            {
                $subContractorsAmounts[$companyRecord['resource_item_id']][$companyRecord['company_id']] = 0;
            }

            $subContractorsAmounts[$companyRecord['resource_item_id']][$companyRecord['company_id']] += $companyRecord['single_unit_grand_total'];
        }

        unset( $billColumnSettingQuantities, $estimatedRateRecords, $companyRecords );

        foreach ( $items as $key => $item )
        {
            $items[$key]['est_amount'] = array_key_exists($item['id'], $resourceItemEstAmounts) ? $resourceItemEstAmounts[$item['id']] : 0;

            if ( array_key_exists($item['id'], $subContractorsAmounts) )
            {
                foreach ( $subContractorsAmounts[$item['id']] as $companyId => $totalAmount )
                {
                    $items[$key]['total_amount-' . $companyId] = $totalAmount;
                }
            }

            unset( $item );
        }

        unset( $resourceItemEstAmounts, $subContractorsAmounts );

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'uom_id'      => -1,
            'uom_symbol'  => '',
            'est_amount'  => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = SubPackageTable::getInstance()->find($request->getParameter('id'))
        );

        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        $sqlFieldCond = '(
            CASE 
                WHEN spsori.sub_package_id is not null THEN spsori.sub_package_id
                WHEN spri.sub_package_id is not null THEN spri.sub_package_id
            ELSE
                spbi.sub_package_id
            END
            ) AS sub_package_id';

        $stmtItem = $pdo->prepare("SELECT DISTINCT c.id AS bill_column_setting_id, c.use_original_quantity, bill.title AS title,
        bill.id AS bill_id, bill.lft, i.id AS bill_item_id, rate.final_value AS final_value, $sqlFieldCond
        FROM " . SubPackageTable::getInstance()->getTableName() . " sp
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.root_id = sp.project_structure_id
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = bill.id
        JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " c ON c.project_structure_id = e.project_structure_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
        LEFT JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " AS spri ON spri.sub_package_id = sp.id
        LEFT JOIN " . SubPackageScheduleOfRateItemTable::getInstance()->getTableName() . " AS spsori ON spsori.sub_package_id = sp.id
        LEFT JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON bur.bill_item_id = i.id AND bur.resource_item_library_id = spri.resource_item_id AND bur.deleted_at IS NULL
        LEFT JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sifc ON sifc.relation_id = spsori.schedule_of_rate_item_id AND sifc.deleted_at IS NULL
        LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS rate ON rate.relation_id = i.id
        LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = sp.id
        WHERE sp.id =" . $subPackage->id . " AND sp.deleted_at IS NULL
        AND bill.deleted_at IS NULL
        AND NOT (spri.sub_package_id IS NULL AND spsori.sub_package_id IS NULL and spbi.sub_package_id IS NULL)
        AND (rate.relation_id = bur.bill_item_id OR rate.schedule_of_rate_item_formulated_column_id = sifc.id OR rate.relation_id = spbi.bill_item_id)
        AND rate.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "' AND rate.deleted_at IS NULL
        AND i.type <> " . BillItem::TYPE_ITEM_NOT_LISTED . " AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND e.deleted_at IS NULL AND c.deleted_at IS NULL ORDER BY bill.lft");

        $stmtItem->execute();

        $records = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

        $billArray = array();
        $billColumnSettingQuantities = array();

        foreach ( $records as $record )
        {
            if ( !array_key_exists($record['bill_id'], $billArray) )
            {
                $billArray[$record['bill_id']] = array(
                    'title'      => $record['title'],
                    'est_amount' => 0
                );
            }

            $quantityFieldName = $record['use_original_quantity'] ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

            $stmt = $pdo->prepare("SELECT COALESCE(fc.final_value, 0) AS value
                FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
                JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
                WHERE r.bill_item_id = " . $record['bill_item_id'] . " AND r.bill_column_setting_id = " . $record['bill_column_setting_id'] . "
                AND r.include IS TRUE AND fc.column_name = '" . $quantityFieldName . "' AND fc.final_value <> 0
                AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

            $stmt->execute();

            $quantityPerType = $stmt->fetch(PDO::FETCH_COLUMN, 0);

            $stmt = $pdo->prepare("SELECT COALESCE(COUNT(id), 0) AS value
                FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " t
                WHERE t.sub_package_id = " . $subPackage->id . "
                AND t.bill_column_setting_id = " . $record['bill_column_setting_id']);

            $stmt->execute();

            $totalAssignedUnits = $stmt->fetch(PDO::FETCH_COLUMN, 0);

            $billColumnSettingQuantities[$record['bill_item_id'] . '-' . $record['bill_column_setting_id']] = $quantityPerType * $totalAssignedUnits;

            unset( $record );
        }

        unset( $records );

        $stmt = $pdo->prepare("SELECT DISTINCT i.id, e.project_structure_id, x.company_id, rate.rate
        FROM " . BillItemTable::getInstance()->getTableName() . " i
        LEFT JOIN " . SubPackageBillItemRateTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = i.id
        LEFT JOIN " . SubPackageCompanyTable::getInstance()->getTableName() . " x ON rate.sub_package_company_id = x.id
        LEFT JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON bur.bill_item_id = i.id AND bur.deleted_at IS NULL
        LEFT JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " AS xref ON bur.resource_item_library_id = xref.resource_item_id AND xref.sub_package_id = " . $subPackage->id . "
        LEFT JOIN " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " ifc ON ifc.relation_id  = bur.id AND ifc.deleted_at IS NULL
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_RATE . "' AND ifc.final_value <> 0
        LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON bifc.relation_id = i.id
        LEFT JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sifc ON sifc.id = bifc.schedule_of_rate_item_formulated_column_id
        LEFT JOIN " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS si ON si.id = sifc.relation_id
        LEFT JOIN " . SubPackageScheduleOfRateItemTable::getInstance()->getTableName() . " AS xref2 ON si.id = xref2.schedule_of_rate_item_id AND xref2.sub_package_id = " . $subPackage->id . "
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON e.project_structure_id = bill.id
        LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = x.sub_package_id
        WHERE bill.root_id = " . $subPackage->project_structure_id . "
        AND bill.deleted_at IS NULL
        AND i.type != " . BillItem::TYPE_ITEM_NOT_LISTED . " AND x.sub_package_id = " . $subPackage->id . " AND NOT (xref.sub_package_id IS NULL AND xref2.sub_package_id IS NULL AND spbi.sub_package_id IS NULL)
        AND e.deleted_at IS NULL
        AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $grandTotalRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subContractorAmounts = array();

        foreach ( $grandTotalRecords as $grandTotalRecord )
        {
            if ( !array_key_exists($grandTotalRecord['project_structure_id'], $subContractorAmounts) )
            {
                $subContractorAmounts[$grandTotalRecord['project_structure_id']] = array();
            }

            if ( !array_key_exists($grandTotalRecord['company_id'], $subContractorAmounts[$grandTotalRecord['project_structure_id']]) )
            {
                $subContractorAmounts[$grandTotalRecord['project_structure_id']][$grandTotalRecord['company_id']] = 0;
            }

            foreach($billColumnSettingQuantities as $idx => $qty)
            {
                if(!empty($qty) && substr( $idx, 0, strlen($grandTotalRecord['id']) + 1 ) === $grandTotalRecord['id'].'-' && !empty($grandTotalRecord['rate']))
                {
                    $subContractorAmounts[$grandTotalRecord['project_structure_id']][$grandTotalRecord['company_id']] += $grandTotalRecord['rate'] * $qty;
                }
            }
        }

        unset($billColumnSettingQuantities);

        $billSplFixedArray = new SplFixedArray(count($billArray) + 1);//plus 1 for last empty row in grid

        $count = 0;

        $totalCostByBills          =  $subPackage->getEstimatedTotalByBills();
        $totalCostNoBuildUpByBills =  $subPackage->getEstimatedTotalNoBuildUpByBills();

        foreach ( $billArray as $id => $bill )
        {
            $estimatedAmount = array_key_exists($id, $totalCostByBills) ? $totalCostByBills[$id] : 0;
            $estimatedAmount += array_key_exists($id, $totalCostNoBuildUpByBills) ? $totalCostNoBuildUpByBills[$id] : 0;

            $bill = array(
                'id'         => $id,
                'title'      => $bill['title'],
                'est_amount' => $estimatedAmount
            );

            foreach ( $subContractorAmounts as $billId => $subContractorAmount )
            {
                if ( $billId == $id )
                {
                    foreach ( $subContractorAmount as $subContractorId => $amount )
                    {
                        $bill['total_amount-' . $subContractorId] = $amount;
                    }

                    unset( $subContractorAmounts[$billId], $subContractorAmount );
                }
            }

            $billSplFixedArray[$count] = $bill;

            unset( $bill );

            $count ++;
        }

        $billSplFixedArray[count($billArray)] = array(
            'id'         => Constants::GRID_LAST_ROW,
            'title'      => "",
            'est_amount' => 0
        );

        unset( $billArray );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $billSplFixedArray->toArray()
        ));
    }

    public function executeGetBillColumnSettings(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        return $this->renderJson($bill->BillColumnSettings->toArray());
    }

    public function executeGetBillElements(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid'))
        );

        $pdo = $subPackage->getTable()->getConnection()->getDbh();

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
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = bill.id
        JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " c ON c.project_structure_id = bill.id
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
        LEFT JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " AS spri ON spri.sub_package_id = sp.id
        LEFT JOIN " . SubPackageScheduleOfRateItemTable::getInstance()->getTableName() . " AS spsori ON spsori.sub_package_id = sp.id
        LEFT JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON bur.bill_item_id = i.id AND bur.resource_item_library_id = spri.resource_item_id AND bur.deleted_at IS NULL
        LEFT JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sifc ON sifc.relation_id = spsori.schedule_of_rate_item_id AND sifc.deleted_at IS NULL
        LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS rate ON rate.relation_id = i.id
        LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = sp.id
        WHERE sp.id =" . $subPackage->id . " AND sp.deleted_at IS NULL
        AND bill.id = " . $bill->id . " AND bill.deleted_at IS NULL
        AND NOT (spri.sub_package_id IS NULL AND spsori.sub_package_id IS NULL and spbi.sub_package_id IS NULL)
        AND (rate.relation_id = bur.bill_item_id OR rate.schedule_of_rate_item_formulated_column_id = sifc.id OR rate.relation_id = spbi.bill_item_id)
        AND rate.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "' AND rate.deleted_at IS NULL
        AND i.type <> " . BillItem::TYPE_ITEM_NOT_LISTED . " AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND e.deleted_at IS NULL AND c.deleted_at IS NULL ORDER BY e.priority");

        $stmtItem->execute();

        $records = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT spc.company_id, r.bill_item_id, r.rate
        FROM " . SubPackageCompanyTable::getInstance()->getTableName() . " AS spc
        JOIN " . SubPackageBillItemRateTable::getInstance()->getTableName() . " AS r ON spc.id = r.sub_package_company_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " AS i ON i.id = r.bill_item_id
        JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id
        WHERE e.project_structure_id = " . $bill->id . " AND spc.sub_package_id = " . $subPackage->id . "
        AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL");

        $stmt->execute();

        $subConRates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $elementArray = array();

        foreach ( $records as $record )
        {
            if ( !array_key_exists($record['element_id'], $elementArray) )
            {
                $elementArray[$record['element_id']] = array(
                    'description'      => $record['description'],
                    'priority'         => $record['priority'],
                    'est_amount_total' => 0
                );
            }

            $quantityFieldName = $record['use_original_quantity'] ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

            $stmt = $pdo->prepare("SELECT SUM(COALESCE(fc.final_value, 0)) AS value
                FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
                JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
                WHERE r.bill_item_id = " . $record['bill_item_id'] . " AND r.bill_column_setting_id = " . $record['bill_column_setting_id'] . "
                AND r.include IS TRUE AND fc.column_name = '" . $quantityFieldName . "' AND fc.final_value <> 0
                AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

            $stmt->execute();

            $quantityPerType = $stmt->fetch(PDO::FETCH_COLUMN, 0);

            $stmt = $pdo->prepare("SELECT COALESCE(COUNT(id), 0) AS value
                FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " t
                WHERE t.sub_package_id = " . $subPackage->id . "
                AND t.bill_column_setting_id = " . $record['bill_column_setting_id']);

            $stmt->execute();

            $totalAssignedUnits = $stmt->fetch(PDO::FETCH_COLUMN, 0);

            $elementArray[$record['element_id']]['est_amount_total'] += $record['final_value'] * ($quantityPerType * $totalAssignedUnits);

            foreach ( $subConRates as $subConRate )
            {
                if ( !array_key_exists('est_amount-bill_column-' . $subConRate['company_id'], $elementArray[$record['element_id']]) )
                {
                    $elementArray[$record['element_id']]['est_amount-bill_column-' . $subConRate['company_id']] = 0;
                }

                if ( $subConRate['bill_item_id'] == $record['bill_item_id'] )
                {
                    $elementArray[$record['element_id']]['est_amount-bill_column-' . $subConRate['company_id']] += $subConRate['rate'] * ($quantityPerType * $totalAssignedUnits);
                }
            }

            unset( $record );
        }

        unset( $records, $subConRates );

        $elementSplFixedArray = new SplFixedArray(count($elementArray) + 1);//plus 1 for last empty row in grid

        $count = 0;

        $totalCostByElements        = $subPackage->getEstimatedTotalByElements($bill);
        $totalCostNoBuildByElements = $subPackage->getEstimatedTotalNoBuildUpByElements($bill);

        foreach ( $elementArray as $id => $element )
        {
            $estimatedAmount = array_key_exists($id, $totalCostByElements) ? $totalCostByElements[$id] : 0;
            $estimatedAmount += array_key_exists($id, $totalCostNoBuildByElements) ? $totalCostNoBuildByElements[$id] : 0;

            $element['id'] = $id;
            $element['est_amount_total'] = $estimatedAmount;

            $elementSplFixedArray[$count] = $element;

            unset( $element );

            $count ++;
        }

        $elementSplFixedArray[count($elementArray)] = array(
            'id'               => Constants::GRID_LAST_ROW,
            'description'      => "",
            'priority'         => 0,
            'est_amount_total' => 0
        );

        unset( $elementArray );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elementSplFixedArray->toArray()
        ));
    }

    public function executeGetBillItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = SubPackageTable::getInstance()->find($request->getParameter('sid')) and
            $element = BillElementTable::getInstance()->find($request->getParameter('eid')) and
            $bill = $element->ProjectStructure and
            $project = ProjectStructureTable::getInstance()->find($bill->root_id)
        );

        $projectMainInfo = $project->MainInformation;
        $pageNoPrefix    = $bill->BillLayoutSetting->page_no_prefix;
        $isPostContract  = false;
        $pdo             = $subPackage->getTable()->getConnection()->getDbh();

        if ( $projectMainInfo->status == ProjectMainInformation::STATUS_POSTCONTRACT )
        {
            $isPostContract = true;
        }

        $stmt = $pdo->prepare("SELECT x.company_id, r.bill_item_id, r.rate, x.sub_package_id
        FROM " . SubPackageCompanyTable::getInstance()->getTableName() . " AS x
        LEFT JOIN " . SubPackageBillItemRateTable::getInstance()->getTableName() . " AS r ON x.id = r.sub_package_company_id
        LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " AS i ON i.id = r.bill_item_id AND i.element_id = " . $element->id . "
        AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        WHERE x.sub_package_id = " . $subPackage->id);

        $stmt->execute();

        $subConRates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $billItems = array();
        $form      = new BaseForm();

        $totalCostByBillItems  = $subPackage->getEstimatedTotalByBillItems($element);
        $totalCostByBillItems += $subPackage->getEstimatedTotalNoBuildUpByBillItems($element);

        if ( !empty($totalCostByBillItems) )
        {
            $billItemIds           = array_keys($totalCostByBillItems);
            $billRefSelector       = 'p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char';
            $postContractJoinTable = null;

            if ( $isPostContract )
            {
                $billRefSelector       = 'pcbir.bill_ref_element_no, pcbir.bill_ref_page_no, pcbir.bill_ref_char';
                $postContractJoinTable = 'JOIN ' . PostContractBillItemRateTable::getInstance()->getTableName() . " pcbir ON (pcbir.bill_item_id = p.id)";
            }

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, uom.symbol AS uom_symbol, p.grand_total, p.grand_total_quantity, p.level, p.priority, p.lft, {$billRefSelector},
                pc.supply_rate AS pc_supply_rate, pc.wastage_percentage AS pc_wastage_percentage,
                pc.wastage_amount AS pc_wastage_amount, pc.labour_for_installation AS pc_labour_for_installation,
                pc.other_cost AS pc_other_cost, pc.profit_percentage AS pc_profit_percentage,
                pc.profit_amount AS pc_profit_amount, pc.total AS pc_total
                FROM " . BillItemTable::getInstance()->getTableName() . " c
                JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt {$postContractJoinTable}
                LEFT JOIN " . BillItemPrimeCostRateTable::getInstance()->getTableName() . " pc ON p.id = pc.bill_item_id AND pc.deleted_at IS NULL
                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE c.root_id = p.root_id AND c.type != " . BillItem::TYPE_ITEM_NOT_LISTED . "
                AND c.id IN (" . implode(',', $billItemIds) . ")
                AND c.element_id = " . $element->id . " AND p.element_id = " . $element->id . " AND c.project_revision_deleted_at IS NULL
                AND c.deleted_at IS NULL AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
                ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

            $stmt->execute();

            $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total = 0;

            foreach ( $billItems as $key => $billItem )
            {
                if(!isset($billItems[$key]['bill_ref']))
                {
                    $billItems[$key]['bill_ref'] = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
                }

                if(!isset($billItems[$key]['total_est_amount']))
                {
                    $billItems[$key]['total_est_amount'] = 0;
                }

                if(!isset($billItems[$key]['total_qty']))
                {
                    $billItems[$key]['total_qty'] = 0;
                }

                if ( !isset($billItems[$key]['rate-value']) )
                {
                    $billItems[$key]['rate-value']  = 0;
                    $billItems[$key]['type']        = (string) $billItem['type'];
                    $billItems[$key]['_csrf_token'] = $form->getCSRFToken();
                }

                $billItems[$key]['rate-value'] = (array_key_exists($billItem['id'], $totalCostByBillItems)) ? $totalCostByBillItems[$billItem['id']]['total_cost_after_conversion'] : 0 ;
                $billItems[$key]['total_est_amount'] += (array_key_exists($billItem['id'], $totalCostByBillItems)) ? $billItems[$key]['rate-value'] * $totalCostByBillItems[$billItem['id']]['total_qty'] : 0;
                $billItems[$key]['total_qty'] += (array_key_exists($billItem['id'], $totalCostByBillItems)) ? $totalCostByBillItems[$billItem['id']]['total_qty'] : 0;
            }

            unset( $totalCostByBillItems );

            foreach($billItems as $key => $billItem)
            {
                foreach ( $subConRates as $subConRate )
                {
                    if ( !array_key_exists('rate-value-' . $subConRate['company_id'], $billItems[$key]) and $billItem['type'] != BillItem::TYPE_HEADER and $billItem['type'] != BillItem::TYPE_HEADER_N and $billItem['type'] != BillItem::TYPE_NOID )
                    {
                        $billItems[$key]['rate-value-' . $subConRate['company_id']]   = 0;
                        $billItems[$key]['total_amount-' . $subConRate['company_id']] = 0;
                    }

                    if ( $subConRate['bill_item_id'] == $billItem['id'] )
                    {
                        $billItems[$key]['rate-value-' . $subConRate['company_id']] = $subConRate['rate'];
                        $billItems[$key]['total_amount-' . $subConRate['company_id']] = $subConRate['rate'] * $billItem['total_qty'];
                    }
                }
            }
        }

        array_push($billItems, array(
            'id'                   => Constants::GRID_LAST_ROW,
            'bill_ref'             => "",
            'priority'             => 0,
            'type'                 => (string) ProjectStructure::getDefaultItemType($element->ProjectStructure->BillType->type),
            'element_id'           => $element->id,
            'grand_total'          => 0,
            'grand_total_quantity' => 0,
            'level'                => 0,
            'uom_id'               => null,
            'uom_symbol'           => null,
            '_csrf_token'          => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $billItems
        ));
    }

    public function executeCompanyForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $form = new ContractorForm();

        $data = array( 'company[_csrf_token]' => $form->getCSRFToken() );

        return $this->renderJson($data);
    }

    public function executeGetSubContractorList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $companies = array();

        $eProjectPDO = Doctrine_Manager::getInstance()->getConnection('eproject_conn')->getDbh();
        Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectCompany')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

        $stmt = $eProjectPDO->prepare("SELECT DISTINCT c.id, c.reference_id FROM ".EProjectCompanyTable::getInstance()->getTableName()." c
        JOIN ".EProjectContractGroupContractGroupCategoryTable::getInstance()->getTableName()." cgc ON c.contract_group_category_id  = cgc.contract_group_category_id
        JOIN ".EProjectContractGroupTable::getInstance()->getTableName()." cg ON cgc.contract_group_id = cg.id
        WHERE cg.group = ".PAM2006::CONTRACTOR." AND c.confirmed IS TRUE");

        $stmt->execute();

        $companyReferenceIdArray = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

        if(!empty($companyReferenceIdArray))
        {
            $pdo  = $subPackage->getTable()->getConnection()->getDbh();
            $form = new BaseForm();
            Doctrine_Manager::getInstance()->getConnectionForComponent('Company')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, 'BS_%s');

            $stmt = $pdo->prepare("SELECT c.id, c.name || ' (' || c.registration_no || ')' as name FROM " . CompanyTable::getInstance()->getTableName() . " c WHERE
            c.id NOT IN (SELECT company_id FROM " . SubPackageCompanyTable::getInstance()->getTableName() . " WHERE sub_package_id = " . $subPackage->id . ")
            AND c.reference_id IN ('".implode("','", $companyReferenceIdArray)."') AND c.deleted_at IS NULL ORDER BY c.name ASC");

            $stmt->execute();

            $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $companies as $key => $company )
            {
                $companies[$key]['_csrf_token'] = $form->getCSRFToken();
            }
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $companies
        ));
    }

    public function executeGetSubContractors(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = SubPackageTable::getInstance()->find($request->getParameter('id'))
        );

        $pdo          = $subPackage->getTable()->getConnection()->getDbh();
        $subConTotals = array();
        $form         = new BaseForm();

        switch ($subPackage->sub_contractor_sort_by)
        {
            case SubPackage::SORT_SUB_CONTRACTOR_NAME_ASC:
                $sqlOrder = "ORDER BY c.name ASC";
                break;
            case SubPackage::SORT_SUB_CONTRACTOR_NAME_DESC:
                $sqlOrder = "ORDER BY c.name DESC";
                break;
            case SubPackage::SORT_SUB_CONTRACTOR_HIGHEST_LOWEST:
            case SubPackage::SORT_SUB_CONTRACTOR_LOWEST_HIGHEST:
                $sqlOrder = "";
                break;
            default:
                throw new Exception('invalid sort option');
        }

        $selectedCompanyId = $subPackage->selected_company_id > 0 ? $subPackage->selected_company_id : - 1;

        $stmt = $pdo->prepare("SELECT c.id, c.name, c.shortname, COALESCE(0, 0) AS total
        FROM " . CompanyTable::getInstance()->getTableName() . " c
        JOIN " . SubPackageCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
        JOIN " . SubPackageTable::getInstance()->getTableName() . " sp ON sp.id = xref.sub_package_id
        WHERE sp.id =" . $subPackage->id . " AND sp.deleted_at IS NULL
        AND c.id <> " . $selectedCompanyId . " AND c.deleted_at IS NULL " . $sqlOrder);

        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $companies = array();

        if (!empty($subPackage->selected_company_id))
        {
            $selectedCompany = $subPackage->SelectedCompany;

            $companies[] = array(
                'id'          => $selectedCompany->id,
                'name'        => $selectedCompany->name,
                'shortname'   => $selectedCompany->shortname,
                'total'       => $selectedCompany->getSubPackageTotalBySubPackageId($subPackage->id),
                '_csrf_token' => $form->getCSRFToken(),
                'selected'    => true
            );

            unset( $selectedCompany );
        }

        foreach ( $records as $key => $record )
        {
            $records[$key]['total']      = CompanyTable::getSubPackageTotalBySubPackageIdAndCompanyId($subPackage->id, $record['id']);
            $subConTotals[$record['id']] = $records[$key]['total'];
        }

        // will start sorting here
        if ( $subPackage->sub_contractor_sort_by == SubPackage::SORT_SUB_CONTRACTOR_HIGHEST_LOWEST )
        {
            arsort($subConTotals);
        }

        if ( $subPackage->sub_contractor_sort_by == SubPackage::SORT_SUB_CONTRACTOR_LOWEST_HIGHEST )
        {
            asort($subConTotals);
        }

        foreach ( $subConTotals as $subConId => $total )
        {
            array_walk($records, function ($record) use (&$companies, $subConId, $form)
            {
                if ( $subConId == $record['id'] )
                {
                    $record['_csrf_token'] = $form->getCSRFToken();
                    $record['selected']    = false;
                    $companies[]           = $record;
                }
            });
        }

        unset( $subConTotals );

        $companies[] = array(
            'id'          => Constants::GRID_LAST_ROW,
            'name'        => '',
            'shortname'   => '',
            '_csrf_token' => $form->getCSRFToken(),
            'selected'    => false
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $companies
        ));
    }

    public function executeSubPackageCompanyForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $form = new SubPackageCompanyForm();

        return $this->renderJson(array(
            'sub_package_company[sub_package_id]' => $subPackage->id,
            'sub_package_company[_csrf_token]'    => $form->getCSRFToken()
        ));
    }

    public function executeSubPackageCompanyAdd(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post')
        );

        $params = $request->getParameter('sub_package_company');

        $subPackageId = $params['sub_package_id'];
        $companyId    = $params['company_id'];

        $this->forward404Unless(
            strlen($subPackageId) > 0 and
            strlen($companyId) > 0 and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($subPackageId) and
            $company = Doctrine_Core::getTable('Company')->find($companyId)
        );

        $subPackageCompanyXref = SubPackageCompanyTable::getBySubPackageIdAndCompanyId($subPackage->id, $company->id);

        $form = new SubPackageCompanyForm($subPackageCompanyXref);

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();
            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors ));
    }

    public function executeSubPackageCompanyDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid'))
        );

        $errorMsg = null;
        try
        {
            $subPackageCompany = SubPackageCompanyTable::getBySubPackageIdAndCompanyId($subPackage->id, $company->id);

            $subPackageCompany->delete();

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeBillItemUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subPackage = SubPackageTable::getInstance()->find($request->getParameter('sid')) and
            $company = CompanyTable::getInstance()->find($request->getParameter('attr_name')) and
            $billItem = BillItemTable::getInstance()->find($request->getParameter('id'))
        );

        $con = $subPackage->getTable()->getConnection();

        $subPackageCompany = SubPackageCompanyTable::getBySubPackageIdAndCompanyId($subPackage->id, $company->id);
        $val               = is_numeric($request->getParameter('val')) ? $request->getParameter('val') : 0;

        try
        {
            $con->beginTransaction();

            if ( !$rate = $subPackageCompany->getRateByBillItemId($billItem->id) )
            {
                $rate                         = new SubPackageBillItemRate();
                $rate->sub_package_company_id = $subPackageCompany->id;
                $rate->bill_item_id           = $billItem->id;
                $rate->save($con);
            }

            $rate->setRate($val);
            $rate->save($con);

            $con->commit();

            $pdo = $subPackage->getTable()->getConnection()->getDbh();

            $element = $billItem->Element;

            $stmt = $pdo->prepare("SELECT DISTINCT c.id AS bill_column_setting_id, c.use_original_quantity
            FROM " . SubPackageCompanyTable::getInstance()->getTableName() . " com
            JOIN " . SubPackageTable::getInstance()->getTableName() . " sp ON com.sub_package_id = sp.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.root_id = sp.project_structure_id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = bill.id
            LEFT JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " c ON c.project_structure_id = e.project_structure_id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
            WHERE sp.id =" . $subPackage->id . " AND sp.deleted_at IS NULL
            AND bill.id = " . $element->project_structure_id . " AND bill.deleted_at IS NULL
            AND e.id = " . $element->id . " AND e.deleted_at IS NULL
            AND i.id = " . $billItem->id . "
            AND c.deleted_at IS NULL ORDER BY c.id");

            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalQty = 0;

            foreach($records as $record)
            {
                $quantityFieldName = $record['use_original_quantity'] ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                $stmt = $pdo->prepare("SELECT SUM(COALESCE(fc.final_value, 0)) AS value
                        FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
                        JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
                        WHERE r.bill_item_id = " . $billItem->id . " AND r.bill_column_setting_id = " . $record['bill_column_setting_id'] . "
                        AND r.include IS TRUE AND fc.column_name = '" . $quantityFieldName . "' AND fc.final_value <> 0
                        AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                $stmt->execute();

                $quantityPerType = $stmt->fetch(PDO::FETCH_COLUMN, 0);

                $totalQty += $quantityPerType;
            }

            $data = array(
                'id'                          => $billItem->id,
                'rate-value-' . $company->id  => $rate->getRate(),
                'total_amount-'. $company->id => $totalQty * $rate->getRate()
            );

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $data     = array();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $data
        ));
    }

    public function executeSortUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid'))
        );

        $con = $subPackage->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            switch ($request->getParameter('opt'))
            {
                case SubPackage::SORT_SUB_CONTRACTOR_NAME_ASC:
                    $sortBy = SubPackage::SORT_SUB_CONTRACTOR_NAME_ASC;
                    break;
                case SubPackage::SORT_SUB_CONTRACTOR_NAME_DESC:
                    $sortBy = SubPackage::SORT_SUB_CONTRACTOR_NAME_DESC;
                    break;
                case SubPackage::SORT_SUB_CONTRACTOR_HIGHEST_LOWEST:
                    $sortBy = SubPackage::SORT_SUB_CONTRACTOR_HIGHEST_LOWEST;
                    break;
                case SubPackage::SORT_SUB_CONTRACTOR_LOWEST_HIGHEST:
                    $sortBy = SubPackage::SORT_SUB_CONTRACTOR_LOWEST_HIGHEST;
                    break;
                default:
                    throw new Exception('invalid sort option');
            }

            $subPackage->sub_contractor_sort_by = $sortBy;
            $subPackage->save($con);

            $con->commit();

            $success  = true;
            $errorMsg = null;
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

    public function executeGetUnitList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid'))
        );

        $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id);

        $pdo                    = $project->getTable()->getConnection()->getDbh();
        $records                = array();
        $billColumnSettingItems = array();

        $stmt = $pdo->prepare("SELECT r.bill_column_setting_id, r.counter
            FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r
            WHERE r.sub_package_id = " . $subPackage->id);

        $stmt->execute();

        $subPackageTypeReferences = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        //Get BillColumnSetting List
        $billColumnSettings = DoctrineQuery::create()->select('cs.id, cs.name, cs.quantity')
            ->from('BillColumnSetting cs')
            ->where('cs.project_structure_id = ? ', array( $bill->id ))
            ->fetchArray();

        foreach ( $billColumnSettings as $column )
        {
            $count = $column['quantity'];

            array_push($records, array(
                'count'       => - 1,
                'id'          => 'type' . '-' . $column['id'],
                'description' => $column['name'],
                'level'       => 0
            ));

            for ( $i = 1; $i <= $count; $i ++ )
            {
                if ( array_key_exists($column['id'], $billColumnSettingItems) && array_key_exists($i, $billColumnSettingItems[$column['id']]) )
                {
                    $record['count']       = $i;
                    $record['id']          = $column['id'] . '-' . $i;
                    $record['description'] = 'Unit ' . $i;
                    $record['level']       = 1;
                }
                else
                {
                    $record['count']       = $i;
                    $record['id']          = $column['id'] . '-' . $i;
                    $record['description'] = 'Unit ' . $i;
                    $record['level']       = 1;
                }

                $record['selected'] = ( array_key_exists($column['id'], $subPackageTypeReferences) and in_array($i, $subPackageTypeReferences[$column['id']]) ) ? true : false;

                array_push($records, $record);

                unset( $record );
            }
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetBillsToAssignUnit(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('spid'))
        );

        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        $sqlFieldCond = '(
            CASE 
                WHEN spsori.sub_package_id is not null THEN spsori.sub_package_id
                WHEN spri.sub_package_id is not null THEN spri.sub_package_id
            ELSE
                spbi.sub_package_id
            END
            ) AS sub_package_id';

        $stmtItem = $pdo->prepare("SELECT DISTINCT bill.title AS title, bill.id AS bill_id, i.id AS bill_item_id, $sqlFieldCond FROM " . SubPackageTable::getInstance()->getTableName() . " sp
        LEFT JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.root_id = sp.project_structure_id
        JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = bill.id
        LEFT JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " AS spri ON spri.sub_package_id = sp.id
        LEFT JOIN " . SubPackageScheduleOfRateItemTable::getInstance()->getTableName() . " AS spsori ON spsori.sub_package_id = sp.id
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = bill.id
        LEFT JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " c ON c.project_structure_id = e.project_structure_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
        LEFT JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON bur.bill_item_id = i.id AND bur.resource_item_library_id = spri.resource_item_id AND bur.deleted_at IS NULL
        LEFT JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sifc ON sifc.relation_id = spsori.schedule_of_rate_item_id AND sifc.deleted_at IS NULL
        LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS rate ON rate.relation_id = i.id
        LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = sp.id
        WHERE sp.id =" . $subPackage->id . " AND sp.deleted_at IS NULL
        AND t.type <> " . BillType::TYPE_PRELIMINARY . " AND t.type <> " . BillType::TYPE_PRIMECOST . " AND bill.deleted_at IS NULL AND t.deleted_at IS NULL
        AND NOT (spri.sub_package_id IS NULL AND spsori.sub_package_id IS NULL AND spbi.id IS NULL)
        AND (rate.relation_id = bur.bill_item_id OR rate.schedule_of_rate_item_formulated_column_id = sifc.id OR rate.relation_id = spbi.bill_item_id)
        AND rate.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "' AND rate.deleted_at IS NULL
        AND i.type <> " . BillItem::TYPE_ITEM_NOT_LISTED . " AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND e.deleted_at IS NULL AND c.deleted_at IS NULL ORDER BY bill.id");

        $stmtItem->execute();

        $records = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

        $billArray = array();

        foreach ( $records as $record )
        {
            if ( !array_key_exists($record['bill_id'], $billArray) )
            {
                $billArray[$record['bill_id']] = array(
                    'title' => $record['title']
                );
            }

            unset( $record );
        }

        unset( $records );

        $billSplFixedArray = new SplFixedArray(count($billArray) + 1);//plus 1 for last empty row in grid

        $count = 0;

        foreach ( $billArray as $id => $bill )
        {
            $bill = array(
                'id'             => $id,
                'title'          => $bill['title'],
                'selected_units' => SubPackageTable::getTotalSelectedUnitsBySubPackageAndBillId($subPackage, $id),
            );

            $billSplFixedArray[$count] = $bill;

            unset( $bill );

            $count ++;
        }

        $billSplFixedArray[count($billArray)] = array(
            'id'             => Constants::GRID_LAST_ROW,
            'title'          => "",
            'selected_units' => "",
        );

        unset( $billArray );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $billSplFixedArray->toArray()
        ));
    }

    public function executeSetSelectedCompany(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid'))
        );

        $con = $subPackage->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            if ( $subPackage->selected_company_id != $company->id )
            {
                $subPackage->selected_company_id = $company->id;

                $select = true;
            }
            else
            {
                $subPackage->selected_company_id = null;

                $select = false;
            }

            $subPackage->save($con);
            $con->commit();

            $success  = true;
            $errorMsg = null;
        } catch (Exception $e)
        {
            $con->rollback();

            $errorMsg = $e->getMessage();
            $select   = false;
            $success  = false;
        }

        return $this->renderJson(array(
            'select'   => $select,
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeAssignUnits(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid'))
        );

        try
        {
            $units = explode(',', $request->getParameter('ids'));

            $subPackage->assignUnits($bill, $units);

            $success  = true;
            $errorMsg = null;
        } catch (Exception $e)
        {
            $success  = false;
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executePushToPostContract(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $success  = null;
        $errorMsg = null;

        try
        {
            $subPackage->cloneBillItemRates();
            $subPackage->locked = true;
            $subPackage->save();

            $claimRevision                            = new SubPackagePostContractClaimRevision();
            $claimRevision->sub_package_id            = $subPackage->id;
            $claimRevision->current_selected_revision = true;
            $claimRevision->version                   = SubPackagePostContractClaimRevision::ORIGINAL_BILL_VERSION;
            $claimRevision->save();
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetSelectedSubConInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $companyName     = "";
        $companyAddress  = "";
        $companyPostCode = "";
        $companyRegion   = "";
        $currency        = $subPackage->ProjectStructure->MainInformation->Currency->currency_code;
        $amount          = 0;

        if ( $subPackage->selected_company_id > 0 )
        {
            $company = $subPackage->SelectedCompany;

            $companyName     = $company->name;
            $companyAddress  = $company->address;
            $companyPostCode = $company->postcode;
            $companyRegion   = $company->region_id > 0 ? $company->Region->country : "";
            $amount          = $company->getSubPackageTotalBySubPackageId($subPackage->id);
        }

        return $this->renderJson(array(
            'name'     => $companyName,
            'address'  => $companyAddress,
            'postcode' => $companyPostCode,
            'region'   => $companyRegion,
            'currency' => $currency,
            'amount'   => number_format($amount, 2, '.', '')
        ));
    }

    public function executeGetImportExportPermission(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackageCompany = SubPackageCompanyTable::getInstance()->findOneBy('sub_package_idAndcompany_id', array( $request->getParameter('id'), $request->getParameter('company_id') ))
        );

        $form = new BaseForm();

        $data['_csrf_token'] = $form->getCSRFToken();

        return $this->renderJson($data);
    }

    public function executeGetAllowedExportedBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackageCompany = SubPackageCompanyTable::getInstance()->findOneBy('sub_package_idAndcompany_id', array( $request->getParameter('id'), $request->getParameter('company_id') ))
        );

        $pdo              = $subPackageCompany->getTable()->getConnection()->getDbh();
        $subPackage       = $subPackageCompany->SubPackage;
        $billArray        = array();
        $usedBillIds      = array();
        $allowedBillTypes = array( BillType::TYPE_PRELIMINARY, BillType::TYPE_PRIMECOST );

        $sqlFieldCond = '(
            CASE 
                WHEN spsori.sub_package_id is not null THEN spsori.sub_package_id
                WHEN spri.sub_package_id is not null THEN spri.sub_package_id
            ELSE
                spbi.sub_package_id
            END
            ) AS sub_package_id';

        $stmtItem = $pdo->prepare("SELECT DISTINCT c.id AS bill_column_setting_id, c.use_original_quantity, bill.title AS title, bill.id AS bill_id, t.type, i.id AS bill_item_id, rate.final_value AS final_value, $sqlFieldCond FROM " . SubPackageTable::getInstance()->getTableName() . " sp
        LEFT JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.root_id = sp.project_structure_id
        JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = bill.id
        LEFT JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " AS spri ON spri.sub_package_id = sp.id
        LEFT JOIN " . SubPackageScheduleOfRateItemTable::getInstance()->getTableName() . " AS spsori ON spsori.sub_package_id = sp.id
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = bill.id
        LEFT JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " c ON c.project_structure_id = e.project_structure_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
        LEFT JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON bur.bill_item_id = i.id AND bur.resource_item_library_id = spri.resource_item_id AND bur.deleted_at IS NULL
        LEFT JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sifc ON sifc.relation_id = spsori.schedule_of_rate_item_id AND sifc.deleted_at IS NULL
        LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS rate ON rate.relation_id = i.id
        LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = sp.id
        WHERE sp.id =" . $subPackageCompany->sub_package_id . " AND sp.deleted_at IS NULL AND bill.deleted_at IS NULL
        AND NOT (spri.sub_package_id IS NULL AND spsori.sub_package_id IS NULL and spbi.sub_package_id IS NULL)
        AND (rate.relation_id = bur.bill_item_id OR rate.schedule_of_rate_item_formulated_column_id = sifc.id OR rate.relation_id = spbi.bill_item_id)
        AND rate.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "' AND rate.final_value <> 0 AND rate.deleted_at IS NULL
        AND i.type <> " . BillItem::TYPE_ITEM_NOT_LISTED . " AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND e.deleted_at IS NULL AND c.deleted_at IS NULL ORDER BY i.id");

        $stmtItem->execute(array());
        $records = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $records as $record )
        {
            if ( array_key_exists($record['bill_id'], $billArray) )
            {
                continue;
            }

            // if normal bill type, need to check it's selected unit(s)
            // if not unit then don't allow to be listed
            if ( !in_array($record['type'], $allowedBillTypes) )
            {
                if ( array_key_exists($record['bill_id'], $usedBillIds) )
                {
                    continue;
                }

                $unitsSelected = SubPackageTable::getTotalSelectedUnitsBySubPackageAndBillId($subPackage, $record['bill_id']);

                $usedBillIds[$record['bill_id']] = $record['bill_id'];

                if ( $unitsSelected == 0 )
                {
                    continue;
                }
            }

            $billArray[$record['bill_id']] = array(
                'id'    => $record['bill_id'],
                'title' => $record['title'],
            );

            unset( $record );
        }

        // empty row
        $billArray[] = array(
            'id'          => Constants::GRID_LAST_ROW,
            'title'       => '',
            'export_link' => '',
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => array_values($billArray),
        ));
    }

    public function executeExportBillToExcelBySupplier(sfWebRequest $request)
    {
        // check for csrf token protection first
        $request->checkCSRFProtection();

        // get subpackage id and selected contractor id
        $this->forward404Unless(
            $subPackageCompany = Doctrine_Core::getTable('SubPackageCompany')->findOneBy('sub_package_idAndcompany_id', array( $request->getParameter('id'), $request->getParameter('company_id') )) AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $errorMsg = null;
        $fileUrl  = null;

        try
        {
            // might do looping for each bill to query bill element and item(s) in order
            // to generate data to export excel
            $sfSubPackageExporter = new sfSubPackageExcelExporter($subPackageCompany, $bill);
            $sfSubPackageExporter->setFileName($request->getParameter('fileName'));
            $sfSubPackageExporter->getBillData();
            $sfSubPackageExporter->generateFile();

            sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url' ));

            $uploadPath = 'uploads' . DIRECTORY_SEPARATOR;

            // Write Excel File
            $fileInfo = $sfSubPackageExporter->fileInfo;
            $success  = true;
            $fileUrl  = public_path($uploadPath . $fileInfo['filename'] . $fileInfo['extension'], true);
        } catch (Exception $e)
        {
            throw $e;
        }

        return $this->renderJson(array( 'errorMsg' => $errorMsg, 'success' => $success, 'fileUrl' => $fileUrl ));
    }

    public function executeImportExcelBillBySupplier(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') AND
            $subPackageCompany = Doctrine_Core::getTable('SubPackageCompany')->findOneBy('sub_package_idAndcompany_id', array( $request->getParameter('subPackageId'), $request->getParameter('companyId') )) AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId'))
        );

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

        try
        {
            $sfImport = new sfSubPackageExcelImport($subPackageCompany, $bill);
            $sfImport->setFileInformation($newName, $ext, $tempUploadPath);
            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $currentUser = $this->getUser()->getGuardUser();

            SubPackageBillItemRateTable::insertImportedSupplierRatesFromExcel($subPackageCompany, $bill, $currentUser, $data);

            $errorMsg = array();
            $success  = true;
        } catch (InvalidArgumentException $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = ( $sfImport->error ) ? false : $success;
        $returnData['errorMsg'] = ( $sfImport->error ) ? $sfImport->errorMsg : $errorMsg;

        return $this->renderJson($returnData);
    }

    public function executeGetSelectionAffectedElementsAndItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid'))
        );

        $newBillIds = array();
        $data       = array();
        $billIds    = json_decode($request->getParameter('bill_ids'), true);

        if ( count($billIds) > 0 )
        {
            $bills = DoctrineQuery::create()
                ->select('b.id')
                ->from('ProjectStructure b')
                ->whereIn('b.id', $billIds)
                ->fetchArray();

            foreach ( $bills as $bill )
            {
                $newBillIds[$bill['id']] = $bill['id'];

                unset( $bill );
            }

            unset( $bills );

            if ( count($newBillIds) > 0 )
            {
                $data = SubPackageTable::getSelectedElementsAndItemsBySubPackageIdAndType($subPackage, 'bill', $newBillIds);
            }
        }

        return $this->renderJson($data);
    }

    public function executeGetAffectedBillAndItemsByElements(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid'))
        );

        $newElementIds = array();
        $data          = array();
        $elementIds    = json_decode($request->getParameter('element_ids'), true);

        if ( count($elementIds) > 0 )
        {
            $elements = DoctrineQuery::create()
                ->select('e.id, e.project_structure_id')
                ->from('BillElement e')
                ->whereIn('e.id', $elementIds)
                ->fetchArray();

            foreach ( $elements as $element )
            {
                $newElementIds[$element['id']] = $element['id'];
            }

            if ( count($newElementIds) > 0 )
            {
                $data = SubPackageTable::getSelectedElementsAndItemsBySubPackageIdAndType($subPackage, 'element', $newElementIds);
            }
        }

        return $this->renderJson($data);
    }

    public function executeGetAffectedBillAndElementsByItem(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid'))
        );

        $newItemIds = array();
        $data       = array();
        $itemIds    = json_decode($request->getParameter('item_ids'), true);

        if ( count($itemIds) > 0 )
        {
            $items = DoctrineQuery::create()
                ->select('i.id')
                ->from('BillItem i')
                ->leftJoin('i.Element e')
                ->whereIn('i.id', $itemIds)
                ->andWhere('e.project_structure_id = ?', $bill->id)
                ->fetchArray();

            foreach ( $items as $item )
            {
                $newItemIds[$item['id']] = $item['id'];
            }

            if ( count($newItemIds) > 0 )
            {
                $data = SubPackageTable::getSelectedElementsAndItemsBySubPackageIdAndType($subPackage, 'item', $newItemIds);
            }
        }

        return $this->renderJson($data);
    }

    public function executeImportContractorRates(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless($request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid'))
        );

        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');
        $success        = null;
        $errorMsg       = null;

        $subPackageCompanyInfo = DoctrineQuery::create()->select('sc.id, sc.sub_package_id, sc.company_id')
            ->from('SubPackageCompany sc')
            ->where('sc.sub_package_id = ?', $subPackage->id)
            ->andWhere('sc.company_id = ?', $company->id)
            ->fetchOne();

        $subPackageCompanyInfo->flushExistingRates();

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

        try
        {
            if ( count($fileToUnzip) )
            {
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

                            if ( SubPackageTable::generateSubPackageUniqueIdByProjectId($subPackage->id, $project->id) != $xmlData->attributes()->uniqueId )
                            {
                                throw new Exception(ProjectMainInformation::ERROR_MSG_WRONG_PROJECT_RATES);
                            }

                            if ( $subPackage->id != $xmlData->attributes()->subPackageId )
                            {
                                throw new Exception(ProjectMainInformation::ERROR_MSG_WRONG_PROJECT_RATES);
                            }

                            if ( $xmlData->attributes()->exportType != ExportedFile::EXPORT_TYPE_SUB_PACKAGE_RATES )
                            {
                                throw new Exception(ExportedFile::ERROR_MSG_WRONG_RATES_FILE);
                            }
                        }
                        else
                        {
                            $projectMainInformation          = $project->MainInformation;
                            $projectArray                    = $project->toArray();
                            $projectArray['MainInformation'] = $projectMainInformation;
                            $importer                        = new sfBuildspaceImportSubPackageBillRatesXML($userId, $projectArray, $subPackage->toArray(), $company->toArray(), $subPackageCompanyInfo->toArray(), null, $file['filename'], $extractDir, null, false);

                            $importer->process();
                        }

                        $count ++;
                    }
                }

                $success = true;
            }
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }


        $data = array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        );

        return $this->renderJson($data);
    }

    public function executeGetProjectBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $tenderAlternative = $project->getAwardedTenderAlternative();

        $tenderAlternativeJoinSql = "";
        $tenderAlternativeWhereSql = "";

        if($tenderAlternative)
        {
            $tenderAlternativeJoinSql = " JOIN ".TenderAlternativeTable::getInstance()->getTableName()." ta ON ta.project_structure_id = p.id
                JOIN ".TenderAlternativeBillTable::getInstance()->getTableName()." tax ON tax.tender_alternative_id = ta.id AND tax.project_structure_id = b.id ";

            $tenderAlternativeWhereSql = " AND ta.id = ".$tenderAlternative->id." AND ta.deleted_at IS NULL AND ta.project_revision_deleted_at IS NULL ";
        }

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT b.id, b.title as name
        FROM " . ProjectStructureTable::getInstance()->getTableName() . " p
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.root_id = p.id
        ".$tenderAlternativeJoinSql."
        WHERE p.id = {$project->id}
        AND b.id != b.root_id
        ".$tenderAlternativeWhereSql."
        AND b.deleted_at IS NULL
        ORDER BY b.lft asc");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        array_push($records, array(
            'id'   => Constants::GRID_LAST_ROW,
            'name' => '',
        ));

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

        $form = new BaseForm();

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')->leftJoin('e.FormulatedColumns fc')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach($elements as $key => $element)
        {
            $elements[ $key ]['_csrf_token'] = $form->getCSRFToken();
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
        );

        array_push($elements, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetBillItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id')) and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $pdo = SubPackageTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id FROM
				" . SubPackageBillItemTable::getInstance()->getTableName() . " ref
				JOIN " . BillItemTable::getInstance()->getTableName() . " i on i.id = ref.bill_item_id 
				WHERE sub_package_id = {$subPackage->id}");

        $stmt->execute();

        $selectedItems = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $items = array();

        $stmt = $pdo->prepare("SELECT c.id, c.description, c.type, c.uom_id, c.lft, c.level,
            c.uom_id, uom.symbol AS uom_symbol
            FROM " . BillItemTable::getInstance()->getTableName() . " c
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id
            WHERE c.element_id = " . $element->id . "
            AND c.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . "
            AND c.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE . "
            AND c.type <> " . BillItem::TYPE_NOID . "
            AND c.deleted_at IS NULL AND c.project_revision_deleted_at IS NULL
            AND uom.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($billItems as $billItem)
        {
            $billItem['type']     = (string)$billItem['type'];
            $billItem['selected'] = in_array($billItem['id'], $selectedItems);
            array_push($items, $billItem);
            unset( $billItem );
        }

        unset( $billItems );

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'type'        => (string)ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'      => '-1',
            'uom_symbol'  => '',
            'level'       => 0,
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeImportBillItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id')) and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('element_id'))
        );

        $errorMsg  = null;
        $billItems = array();

        try
        {
            $billItemIds = strlen($request->getParameter('ids')) > 0 ? Utilities::array_filter_integer(explode(',', $request->getParameter('ids'))) : array();
            $billItems   = $subPackage->importBillItems($billItemIds, $element->id);

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'         => $success,
            'errorMsg'        => $errorMsg,
            'items'           => $billItems,
            'est_amount'      => $subPackage->getEstimationAmount(),
            'selected_amount' => $subPackage->getSelectedCompanySingleUnitTotalAmount()
        ));
    }
}