<?php

/**
 * purchaseOrder actions.
 *
 * @package    buildspace
 * @subpackage purchaseOrder
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class purchaseOrderActions extends BaseActions
{
    public function executeGetPurchaseOrderListings(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form = new BaseForm();
        $data = array();

        $purchaseOrders = DoctrineQuery::create()
            ->select('r.id, r.prefix, r.po_count, p.id, p.title, poi.status, r.created_at, c.id as creator_id, pr.name')
            ->from('PurchaseOrder r')
            ->leftJoin('r.Creator c')
            ->leftJoin('c.Profile pr')
            ->leftJoin('r.PurchaseOrderInformation poi')
            ->leftJoin('r.Project p')
            ->addOrderBy('r.id DESC')
            ->fetchArray();

        foreach ( $purchaseOrders as $purchaseOrder )
        {
            $status  = $purchaseOrder['PurchaseOrderInformation']['status'] ? : PurchaseOrderInformation::STATUS_PENDING;
            $poCount = Utilities::generatePurchaseOrderReferenceNo($purchaseOrder['po_count']);

            $data[] = array(
                'id'          => $purchaseOrder['id'],
                'po_no'       => $purchaseOrder['prefix'] . $poCount,
                'project_id'  => isset( $purchaseOrder['Project'] ) ? $purchaseOrder['Project']['id'] : null,
                'project'     => isset( $purchaseOrder['Project'] ) ? $purchaseOrder['Project']['title'] : '-',
                'status'      => PurchaseOrderInformationTable::getStatusText($status),
                'date_issued' => date('d/m/Y H:i', strtotime($purchaseOrder['created_at'])),
                'issued_by'   => ( $purchaseOrder['Creator']['Profile']['name'] ) ? $purchaseOrder['Creator']['Profile']['name'] : '-',
                '_csrf_token' => $form->getCSRFToken(),
            );

            unset( $user );
        }

        $data[] = array(
            'id'          => Constants::GRID_LAST_ROW,
            'po_no'       => null,
            'project'     => null,
            'status'      => null,
            'date_issued' => '-',
            'issued_by'   => '-',
            '_csrf_token' => $form->getCSRFToken(),
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items' => $data
        ));
    }

    public function executeGetPurchaseOrderFormInformation(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'));
        $purchaseOrder = ( $purchaseOrder ) ? $purchaseOrder : new PurchaseOrder();
        $form          = new PurchaseOrderForm($purchaseOrder);

        return $this->renderJson(array(
            'purchaseOrderForm' => array(
                'purchase_order[prefix]'        => $form->getObject()->prefix,
                'purchase_order[region_id]'     => $form->getObject()->region_id ? : 0,
                'purchase_order[sub_region_id]' => $form->getObject()->sub_region_id ? : 0,
                'purchase_order[_csrf_token]'   => $form->getCSRFToken(),
            )
        ));
    }

    public function executeDeletePurchaseInformation(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
        );

        $conn = $purchaseOrder->getTable()->getConnection();

        try
        {
            $conn->beginTransaction();

            $purchaseOrder->delete($conn);

            $success = true;

            $conn->commit();
        }
        catch (Exception $e)
        {
            $conn->rollback();

            $success = false;
        }

        return $this->renderJson(array( 'poId' => $request->getPostParameter('poId'), 'success' => $success ));
    }

    public function executeGetProjectListings(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => ProjectStructureTable::getAllProjects()
        ));
    }

    public function executeGetCurrentProjectPurchaseOrderNoAndSupplierInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectStructureId'))
        );

        $purchaseOrder   = PurchaseOrderTable::getLatestPurchaseByProject($project);
        $poProjectPrefix = ( $purchaseOrder ) ? $purchaseOrder['prefix'] : null;
        $poProjectCount  = PurchaseOrderProjectTable::getLatestPurchaseOrderNoCountByProjectId($project);
        $poUsedSuppliers = PurchaseOrderProjectTable::getAssignedSuppliersByProject($project);

        return $this->renderJson(array(
            'poUsedSuppliers' => $poUsedSuppliers,
            'poPrefix'        => $poProjectPrefix,
            'poCount'         => sprintf(sfConfig::get('app_rfq_project_ref_no_zero_fill_length'), $poProjectCount),
        ));
    }

    public function executeSavePurchaseOrderInformation(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() AND $request->isMethod('post'));

        $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'));
        $purchaseOrder = ( $purchaseOrder ) ? $purchaseOrder : new PurchaseOrder();
        $form          = new PurchaseOrderForm($purchaseOrder);

        if ( $this->isFormValid($request, $form) )
        {
            $purchaseOrder = $form->save();

            $id      = $purchaseOrder->getId();
            $success = true;
            $errors  = array();
        }
        else
        {
            $id      = $request->getPostParameter('poId');
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeGetWorkAreaInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
        );

        $form          = new PurchaseOrderInformationForm();
        $baseForm      = new BaseForm();
        $poInformation = $purchaseOrder->PurchaseOrderInformation;
        $poCount       = Utilities::generatePurchaseOrderReferenceNo($purchaseOrder->po_count);
        $subPackageId  = ( $poInformation->sub_package_id ) ? $poInformation->sub_package_id : PurchaseOrderInformation::SUB_CON_MAIN_ID;

        $data['purchaseOrderForm'] = array(
            'purchase_order_information[project_name]'        => $purchaseOrder->Project->title,
            'purchase_order_information[price_format]'        => $poInformation->price_format,
            'purchase_order_information[print_without_cents]' => $poInformation->print_without_cents,
            'purchase_order_information[ref]'                 => $poInformation->ref,
            'purchase_order_information[quo_ref]'             => $poInformation->quo_ref,
            'purchase_order_information[attention_to]'        => $poInformation->attention_to,
            'purchase_order_information[ship_to_1]'           => $poInformation->ship_to_1,
            'purchase_order_information[ship_to_2]'           => $poInformation->ship_to_2,
            'purchase_order_information[ship_to_3]'           => $poInformation->ship_to_3,
            'purchase_order_information[currency_id]'         => (string) $poInformation->currency_id,
            'purchase_order_information[poNo]'                => $purchaseOrder->prefix . $poCount,
            'purchase_order_information[company_address_1]'   => $poInformation->company_address_1,
            'purchase_order_information[company_address_2]'   => $poInformation->company_address_2,
            'purchase_order_information[company_address_3]'   => $poInformation->company_address_3,
            'purchase_order_information[supplier_address_1]'  => $poInformation->supplier_address_1,
            'purchase_order_information[supplier_address_2]'  => $poInformation->supplier_address_2,
            'purchase_order_information[supplier_address_3]'  => $poInformation->supplier_address_3,
            'purchase_order_information[note]'                => $poInformation->note,
            'purchase_order_information[signature]'           => $poInformation->signature,
            'region_name'                                     => $purchaseOrder->Region->country,
            'sub_region_name'                                 => $purchaseOrder->SubRegion->name,
            'purchase_order_information[status]'              => (string) $poInformation->status,
            'sub_package_id'                                  => (string) $subPackageId,
            'currentAssignedSupplier'                         => ( $purchaseOrder->PurchaseOrderSupplier->Company->name ) ? : '-',
            '_csrf_token'                                     => $form->getCSRFToken(),
            'printing_csrf_token'                             => $baseForm->getCSRFToken(),
        );

        $counter = 1;
        $taxes   = PurchaseOrderTaxTable::getAvailableTaxes($poInformation);

        foreach ( $taxes as $tax )
        {
            $taxPercentage                                           = (float) $tax->percentage;
            $data['purchaseOrderForm']["tax_name[{$counter}]"]       = empty( $tax->tax_name ) ? null : $tax->tax_name;
            $data['purchaseOrderForm']["tax_percentage[{$counter}]"] = empty( $taxPercentage ) ? null : Utilities::prelimRounding($taxPercentage);

            $counter ++;
        }

        $data['subPackageSelections'] = SubPackageTable::subConSelections($purchaseOrder->Project);
        $data['poStatusSelections']   = PurchaseOrderInformationTable::statusSelections();
        $data['currencySelections']   = CurrencyTable::selections();

        return $this->renderJson($data);
    }

    public function executeUpdateWorkAreaInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
        );

        $form = new PurchaseOrderInformationForm($purchaseOrder->PurchaseOrderInformation);

        if ( $this->isFormValid($request, $form) )
        {
            $poInformation = $form->save();
            $id            = $poInformation->getId();
            $success       = true;
            $errors        = array();

            // return null if user selected MAIN as the sub package id
            if ( $request->getPostParameter('sub_package_id') == PurchaseOrderInformation::SUB_CON_MAIN_ID )
            {
                $poInformation->setSubPackageId(null);
            }
            else
            {
                $poInformation->setSubPackageId($request->getPostParameter('sub_package_id'));
            }

            $poInformation->save();

            $this->savePurchaseOrderTaxes($request, $poInformation);
        }
        else
        {
            $id      = $request->getPostParameter('rfqItemRemarkId');
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeGetItemListing(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
        );

        $poItemsId     = array();
        $poFromDbItems = array();
        $poTreeItems   = array();

        // get associated PO's Item listing
        $poItems = PurchaseOrderItemTable::getItemListingByPurchaseOrder($purchaseOrder);

        foreach ( $poItems as $poItem )
        {
            $poItemsId[$poItem['resource_item_id']] = $poItem['resource_item_id'];

            $poFromDbItems[$poItem['resource_item_id']] = array(
                'poItemId'       => $poItem['id'],
                'quantity'       => number_format((float) $poItem['quantity'], 2, '.', ''),
                'rates'          => number_format((float) $poItem['rates'], 2, '.', ''),
                'poItemRemarkId' => $poItem['remark_id'],
                'remarks'        => $poItem['remark'],
            );
        }

        if ( !empty( $poItemsId ) )
        {
            $poTreeItems = PurchaseOrderItemTable::getHierarchyItemListingFromResourceLibraryByPOItemIds($poItemsId, $poFromDbItems);

            unset( $poFromDbItems );
        }

        // default empty row
        $emptyRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'uom'         => null,
            'quantity'    => null,
            'remarks'     => null,
        );

        array_push($poTreeItems, $emptyRow);

        $form = new BaseForm();

        // assign csrf token to each available item
        foreach ( $poTreeItems as $key => $rfqTreeItem )
        {
            $poTreeItems[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $poTreeItems,
        ));
    }

    public function executeGetProjectResources(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT DISTINCT r_lib.id, r_lib.name FROM
        " . ResourceTable::getInstance()->getTableName() . " AS r_lib JOIN
        " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON r.resource_library_id = r_lib.id JOIN
        " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
        " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON ifc.relation_id = bur.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND r.deleted_at IS NULL AND bur.deleted_at IS NULL
        AND i.project_revision_deleted_at IS NULL
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL
        AND ifc.final_value <> 0 AND ifc.deleted_at IS NULL
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL AND r_lib.deleted_at IS NULL ORDER BY r_lib.id");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $emptyRow = array(
            'id'   => Constants::GRID_LAST_ROW,
            'name' => '',
        );

        array_push($records, $emptyRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records,
        ));
    }

    public function executeGetProjectResourceTrades(sfWebRequest $request)
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
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL
        AND ifc.final_value <> 0 AND ifc.deleted_at IS NULL
        AND r.deleted_at IS NULL AND t.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL AND r_trade.deleted_at IS NULL ORDER BY r_trade.priority");

        $stmt->execute(array());

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach ( $results as $key => $result )
        {
            $results[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        $emptyRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            '_csrf_token' => null,
        );

        array_push($results, $emptyRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $results,
        ));
    }

    public function executeGetProjectResourceItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId')) and
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

        $results = array();

        /*
        * Query resource trade obj using PDO instead of Doctrine because we want to get the record even when the resource trade has been flagged as deleted(soft delete)
        */
        $sth = $pdo->prepare("SELECT id FROM " . ResourceTradeTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('id'));

        $sth->execute();

        $this->forward404Unless($trade = $sth->fetch(PDO::FETCH_ASSOC));

        $stmt = $pdo->prepare("SELECT DISTINCT bur.id, bur.resource_item_library_id FROM
        " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r JOIN
        " . BillBuildUpRateResourceTradeTable::getInstance()->getTableName() . " AS t ON t.build_up_rate_resource_id = r.id JOIN
        " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_trade_id = t.id JOIN
        " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bur.id = ifc.relation_id JOIN
        " . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
        " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
        " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND r.resource_library_id = " . $resource['id'] . "
        AND t.resource_trade_library_id = " . $trade['id'] . " AND bur.resource_item_library_id IS NOT NULL
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL
        AND ifc.final_value <> 0 AND r.deleted_at IS NULL AND t.deleted_at IS NULL AND bur.deleted_at IS NULL
        AND ifc.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL");

        $stmt->execute(array());

        $buildUpRateItemWithResourceItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ( !empty( $buildUpRateItemWithResourceItemIds ) )
        {
            $buildUpRateItemIds     = array();
            $resourceItemLibraryIds = array();
            $poItemsId              = array();

            foreach ( $buildUpRateItemWithResourceItemIds as $record )
            {
                $buildUpRateItemIds[] = $record['id'];

                if ( !in_array($record['resource_item_library_id'], $resourceItemLibraryIds) )
                {
                    $resourceItemLibraryIds[] = $record['resource_item_library_id'];
                }
            }

            // get associated PO's Item listing
            $poItems = PurchaseOrderItemTable::getItemListingByPurchaseOrder($purchaseOrder);

            foreach ( $poItems as $poItem )
            {
                $poItemsId[] = $poItem['resource_item_id'];
            }

            $totalCostAndQuantityByResourceItems = ResourceItemTable::calculateTotalForResourceAnalysis($resourceItemLibraryIds, $resource['id'], $project->id);

            $notInQuery = '';

            if ( !empty( $poItemsId ) )
            {
                $implodedPOItemsId = implode(', ', $poItemsId);

                $notInQuery = 'AND c.id NOT IN (' . $implodedPOItemsId . ')';
            }

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type::text, p.uom_id, p.level,
            p.priority, p.lft, uom.symbol AS uom_symbol
            FROM " . ResourceItemTable::getInstance()->getTableName() . " c
            JOIN " . ResourceItemTable::getInstance()->getTableName() . " p
            ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE c.root_id = p.root_id AND c.type <> " . ResourceItem::TYPE_HEADER . "
            AND c.id IN (" . implode(',', $resourceItemLibraryIds) . ") " . $notInQuery . "
            AND c.resource_trade_id = " . $trade['id'] . " AND p.resource_trade_id = " . $trade['id'] . "
            AND c.deleted_at IS NULL AND p.deleted_at IS NULL
            ORDER BY p.priority, p.lft, p.level ASC");

            $stmt->execute(array());

            $resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $resourceItems as $key => $item )
            {
                $totalQuantity = 0;

                if ( $item['type'] == ResourceItem::TYPE_WORK_ITEM && array_key_exists($item['id'], $totalCostAndQuantityByResourceItems) )
                {
                    $totalQuantity = $totalCostAndQuantityByResourceItems[$item['id']]['total_quantity'];
                }

                $item['total_qty'] = $totalQuantity;

                array_push($results, $item);

                unset( $resourceItems[$key] );
            }
        }

        $emptyRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'type'        => (string) ResourceItem::TYPE_WORK_ITEM,
            'description' => '',
            'uom_symbol'  => '',
            'total_qty'   => 0,
        );

        array_push($results, $emptyRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $results,
        ));
    }

    public function executeCopyResourceItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
        );

        $errorMsg = null;
        $items    = array();
        $conn     = $purchaseOrder->getTable()->getConnection();

        try
        {
            $conn->beginTransaction();

            $ids = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));

            $purchaseOrder->copyResourceItems($ids);

            $success = true;

            $conn->commit();
        }
        catch (Exception $e)
        {
            $conn->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeCopyProjectResourceItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId')) AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) AND
            $request->hasParameter('ids')
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        /*
         * Query resource obj using PDO instead of Doctrine because we want to get the record even when the resource has been flagged as deleted(soft delete)
         */
        $sth = $pdo->prepare("SELECT id FROM " . ResourceTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('resourceId'));

        $sth->execute();

        $this->forward404Unless($resource = $sth->fetch(PDO::FETCH_ASSOC));

        $errorMsg = null;
        $items    = array();
        $conn     = $purchaseOrder->getTable()->getConnection();

        try
        {
            $conn->beginTransaction();

            $ids = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));

            $resourceObject     = new Resource();
            $resourceObject->id = $resource['id'];

            $purchaseOrder->copyProjectResourceItems($ids, $project, $resourceObject);

            $success = true;

            $conn->commit();

            $resourceObject->free(true);

            unset( $resourceObject );
        }
        catch (Exception $e)
        {
            $conn->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeGetResourceList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $resources = array();

        $records = DoctrineQuery::create()
            ->select('r.id, r.name')
            ->from('Resource r')
            ->addOrderBy('r.id ASC')
            ->fetchArray();

        foreach ( $records as $record )
        {
            $resources[] = array(
                'id'          => $record['id'],
                'description' => $record['name'],
            );

            unset( $record );
        }

        unset( $records );

        // default last row
        $defaultLastRow = array(
            'id'          => null,
            'description' => null,
        );

        array_push($resources, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $resources
        ));
    }

    public function executeGetResourceTradeList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('resourceId'))
        );

        $resources = array();

        $records = DoctrineQuery::create()
            ->select('t.id, t.description, t.updated_at')
            ->from('ResourceTrade t')
            ->andWhere('t.resource_id = ?', $resource->id)
            ->addOrderBy('t.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        foreach ( $records as $record )
        {
            $resources[] = array(
                'id'          => $record['id'],
                'description' => $record['description'],
                'updated_at'  => $record['updated_at'],
                '_csrf_token' => $form->getCSRFToken(),
            );

            unset( $record );
        }

        unset( $records );

        // default last row
        $defaultLastRow = array(
            'id'          => null,
            'description' => null,
            'updated_at'  => '-',
            '_csrf_token' => $form->getCSRFToken(),
        );

        array_push($resources, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $resources
        ));
    }

    public function executeGetResourceItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId')) AND
            $trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('tradeId'))
        );

        $pdo           = $trade->getTable()->getConnection()->getDbh();
        $resourceItems = array();
        $poItemsId     = array();
        $form          = new BaseForm();

        // get associated RFQ's Item listing
        $poItems = PurchaseOrderItemTable::getItemListingByPurchaseOrder($purchaseOrder);

        foreach ( $poItems as $poItem )
        {
            $poItemsId[] = $poItem['resource_item_id'];
        }

        $stmt = $pdo->prepare("SELECT i.resource_trade_id, i.id FROM " . ResourceItemTable::getInstance()->getTableName() . " i
        WHERE i.id = i.root_id AND i.resource_trade_id = " . $trade->id . " AND i.deleted_at IS NULL ORDER BY i.priority");

        $stmt->execute(array());

        $roots = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        if ( array_key_exists($trade->id, $roots) )
        {
            $notInQuery = '';
            $rootIds    = $roots[$trade->id];

            if ( !empty( $poItemsId ) )
            {
                $notInQuery = 'AND c.id NOT IN (' . implode(', ', $poItemsId) . ')';
            }

            $stmt = $pdo->prepare("SELECT DISTINCT c.id, c.description, c.type::text, uom.id as uom_id, c.lft, c.level,
            c.resource_trade_id, c.level, c.updated_at, uom.symbol AS uom_symbol,
            c.priority, c.lft, c.level
            FROM " . ResourceItemTable::getInstance()->getTableName() . " p
            JOIN " . ResourceItemTable::getInstance()->getTableName() . " c
            ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id
            AND uom.deleted_at IS NULL WHERE p.id IN (" . implode(',', $rootIds) . ") " . $notInQuery . " AND p.id = c.root_id
            AND c.deleted_at IS NULL AND p.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level ASC");

            $stmt->execute(array());

            $resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'type'        => (string) ResourceItem::TYPE_WORK_ITEM,
            'uom_symbol'  => '',
            'uom_id'      => '-1',
            'relation_id' => $trade->id,
            'updated_at'  => '-',
            'level'       => 0,
            '_csrf_token' => $form->getCSRFToken()
        );

        array_push($resourceItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $resourceItems
        ));
    }

    public function executeGetRequestForQuotationByPurchaseOrder(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
        );

        $form = new BaseForm();
        $data = array();

        $rfqs = DoctrineQuery::create()
            ->select('r.id, r.prefix, r.rfq_count, r.type, r.status, r.created_at, s.id')
            ->from('RFQ r')
            ->where('r.project_structure_id = ?', array( $purchaseOrder->project_structure_id ))
            ->addOrderBy('r.id DESC')
            ->fetchArray();

        foreach ( $rfqs as $rfq )
        {
            $rfqCount = Utilities::generateRFQReferenceNo($rfq['type'], $rfq['rfq_count']);

            $data[] = array(
                'id'          => $rfq['id'],
                'rfq_no'      => $rfq['prefix'] . $rfqCount,
                'status'      => RFQTable::getStatusText($rfq['status']),
                'date_issued' => date('d/m/Y H:i', strtotime($rfq['created_at'])),
                '_csrf_token' => $form->getCSRFToken(),
            );

            unset( $rfq );
        }

        unset( $rfqs );

        $data[] = array(
            'id'          => null,
            'rfq_no'      => null,
            'status'      => null,
            'date_issued' => '-',
            '_csrf_token' => null,
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items' => $data
        ));
    }

    public function executeGetRequestForQuotationItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) and
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
        );

        $pdo     = $purchaseOrder->getTable()->getConnection()->getDbh();
        $results = array();

        // will check for tagged item from Resource that is currently used by this project
        $taggedResourceItems = PurchaseOrderItemTable::getResourceItemIdsByPurchaseOrder($purchaseOrder);

        // will check for tagged item from RFQ that is currently used by this project
        $taggedRFQItems = RFQItemTable::getResourceItemIdsTaggedByProject($purchaseOrder->Project);

        if ( !empty( $taggedRFQItems ) )
        {
            $notInQuery = '';

            if ( !empty( $taggedResourceItems ) )
            {
                $notInQuery = 'AND c.id NOT IN (' . implode(', ', $taggedResourceItems) . ')';
            }

            $stmt = $pdo->prepare("SELECT DISTINCT rfqi.id, p.root_id, p.description, p.type::text, p.uom_id,
            rfqi.quantity, rt.priority as trade_priority, p.level,
            p.priority, p.lft, uom.symbol AS uom_symbol, r.id as r_id
            FROM " . ResourceItemTable::getInstance()->getTableName() . " c
            JOIN " . ResourceItemTable::getInstance()->getTableName() . " p
            ON c.lft BETWEEN p.lft AND p.rgt
            JOIN " . RFQItemTable::getInstance()->getTableName() . " rfqi ON rfqi.resource_item_id = p.id
            JOIN " . RFQTable::getInstance()->getTableName() . " rfq ON rfq.id = rfqi.request_for_quotation_id
            JOIN " . ResourceTradeTable::getInstance()->getTableName() . " rt ON rt.id = p.resource_trade_id AND rt.deleted_at IS NULL
            JOIN " . ResourceTable::getInstance()->getTableName() . " r on rt.resource_id = r.id AND r.deleted_at IS NULL
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE c.root_id = p.root_id AND c.type <> " . ResourceItem::TYPE_HEADER . "
            AND c.id IN (" . implode(', ', $taggedRFQItems) . ") " . $notInQuery . "
            AND rfq.id = " . $rfq->id . " AND rfq.project_structure_id = " . $purchaseOrder->project_structure_id . "
            AND c.deleted_at IS NULL AND p.deleted_at IS NULL
            ORDER BY r.id, rt.priority, p.priority, p.lft, p.level ASC");

            $stmt->execute(array());

            $resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $resourceItems as $key => $item )
            {
                array_push($results, $item);

                unset( $resourceItems[$key] );
            }

            unset( $resourceItems );
        }

        $results[] = array(
            'id'          => Constants::GRID_LAST_ROW,
            'type'        => (string) ResourceItem::TYPE_WORK_ITEM,
            'description' => '',
            'uom_symbol'  => '',
            'total_qty'   => 0,
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $results,
        ));
    }

    public function executeCopyRequestForQuotationItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) and
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
        );

        $errorMsg = null;
        $items    = array();
        $conn     = $purchaseOrder->getTable()->getConnection();

        try
        {
            $conn->beginTransaction();

            $ids = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));

            $purchaseOrder->copyRequestForQuotationItems($rfq, $ids);

            $success = true;

            $conn->commit();
        }
        catch (Exception $e)
        {
            $conn->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeDeleteItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId')) AND
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
        );

        $items    = array();
        $errorMsg = array();

        try
        {
            $affectedNodes = PurchaseOrderItemTable::deleteLikeResourceLibraryTree($purchaseOrder, $resourceItem);
            $success       = true;
        }
        catch (Exception $e)
        {
            $errorMsg      = $e->getMessage();
            $affectedNodes = array();
            $success       = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => $affectedNodes ));
    }

    public function executeUpdateItemInformation(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $request->hasParameter('field_name') and
            $poItem = Doctrine_Core::getTable('PurchaseOrderItem')->findOneBy('idAndpurchase_order_id', array(
                $request->getPostParameter('poItemId'),
                $request->getPostParameter('poId')
            ))
        );

        $success       = false;
        $errorMsg      = array();
        $items         = array();
        $allowedFields = array( PurchaseOrderItem::QUANTITY, PurchaseOrderItem::RATES );
        $fieldName     = $request->getParameter('field_name');

        try
        {
            if ( !in_array($fieldName, $allowedFields) )
            {
                throw new InvalidArgumentException('Invalid field submitted !');
            }

            $value = ( is_numeric($request->getPostParameter('val')) ) ? $request->getPostParameter('val') : 0;

            $poItem->{$fieldName} = number_format((float) $value, 2, '.', '');
            $poItem->save();

            $success = true;

            $items[] = array(
                'id'       => $poItem->resource_item_id,
                'quantity' => $poItem->quantity,
                'rates'    => $poItem->rates,
            );
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeUpdateRFQItemRemark(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $purchaseOrderItem = Doctrine_Core::getTable('PurchaseOrderItem')->find($request->getParameter('poItemId'))
        );

        $resourceItemId = $request->getPostParameter('resourceItemId');
        $description    = $request->getPostParameter('rfq_item_remark[description]');

        $rfqItemRemark = Doctrine_Core::getTable('RFQItemRemark')->findOneBy('resource_item_idAnddescription', array( $resourceItemId, $description ));
        $rfqItemRemark = ( $rfqItemRemark ) ? $rfqItemRemark : new RFQItemRemark();

        $form = new RFQItemRemarkForm($rfqItemRemark);

        if ( $this->isFormValid($request, $form) )
        {
            $rfqItemRemark = $form->save();
            $id            = $rfqItemRemark->getId();
            $success       = true;
            $errors        = array();
        }
        else
        {
            $id      = $request->getPostParameter('rfqItemRemarkId');
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeGetPreviousRFQItemRemarks(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
        );

        $pdo    = $resourceItem->getTable()->getConnection()->getDbh();
        $poItem = Doctrine_Core::getTable('PurchaseOrderItem')->find($request->getParameter('poItemId'));

        $stmt = $pdo->prepare("SELECT DISTINCT(rfqri.id), rfqri.description FROM " . RFQItemRemarkTable::getInstance()->getTableName() . " rfqri
        JOIN " . PurchaseOrderItemTable::getInstance()->getTableName() . " poi ON rfqri.resource_item_id = poi.resource_item_id
        WHERE poi.resource_item_id = " . $resourceItem->id . "
        AND rfqri.deleted_at IS NULL
        ORDER BY rfqri.id DESC");

        $stmt->execute(array());

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach ( $results as $key => $result )
        {
            $selected = ( $result['id'] == $poItem->remark_id ) ? true : false;

            $results[$key]['selected']    = $selected;
            $results[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        $emptyRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
        );

        array_push($results, $emptyRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => array_values($results),
        ));
    }

    public function executeUpdateSelectedPreviousRFQItemRemark(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $purchaseOrderItem = Doctrine_Core::getTable('PurchaseOrderItem')->find($request->getParameter('poItemId'))
        );

        $data = array();

        $resourceItemId = $request->getPostParameter('rfq_item_remark[resource_item_id]');
        $description    = $request->getPostParameter('rfq_item_remark[description]');

        $this->forward404Unless($rfqItemRemark = Doctrine_Core::getTable('RFQItemRemark')->findOneBy('resource_item_idAnddescription', array( $resourceItemId, $description )));

        $form = new RFQItemRemarkForm($rfqItemRemark);

        if ( $this->isFormValid($request, $form) )
        {
            $id      = $rfqItemRemark->id;
            $success = true;
            $errors  = array();

            if ( $purchaseOrderItem->remark_id == $id )
            {
                $purchaseOrderItem->remark_id = null;

                $data = array(
                    'poItemRemarkId' => $id,
                    'remarks'        => "",
                );
            }
            else
            {
                $purchaseOrderItem->remark_id = $id;

                $data = array(
                    'poItemRemarkId' => $id,
                    'remarks'        => $form->getObject()->description,
                );
            }

            $purchaseOrderItem->save();
        }
        else
        {
            $id      = $request->getPostParameter('poItemRemarkId');
            $errors  = $form->getErrors();
            $success = false;
        }
        
        return $this->renderJson(array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors, 'data' => $data ));
    }

    public function executeGetCompanyListing(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
        );

        // get previous selected supplier ID if available
        $supplier = $purchaseOrder->PurchaseOrderSupplier;

        $data['data']['items'] = array();

        $companies = DoctrineQuery::create()
            ->select('c.id, c.name, c.reference_id, c.registration_no, r.country, sr.name, c.updated_at')
            ->from('Company c')
            ->leftJoin('c.Region r')
            ->leftJoin('c.SubRegion sr')
            ->orderBy('c.id')
            ->fetchArray();

        if(!empty($companies))
        {
            $contractGroupCategories = array();

            $EProjectCompanies = DoctrineQuery::create()
                ->select('c.id, c.reference_id, g.id, g.name')
                ->from('EProjectCompany c')
                ->leftJoin('c.ContractGroupCategory g')
                ->whereIn('c.reference_id', array_column($companies, 'reference_id'))
                ->orderBy('c.name ASC')
                ->fetchArray();

            foreach($EProjectCompanies as $EProjectCompany)
            {
                $contractGroupCategories[$EProjectCompany['reference_id']] = $EProjectCompany['ContractGroupCategory']['name'];
            }
        }

        $items = array();

        foreach ( $companies as $company )
        {
            $selected = false;

            if ( $company['id'] == $supplier->company_id )
            {
                $selected = true;
            }

            $items[] = array(
                'id'              => $company['id'],
                'company_name'    => $company['name'],
                'registration_no' => $company['registration_no'],
                'business_type'   => isset($contractGroupCategories[$company['reference_id']]) ? $contractGroupCategories[$company['reference_id']] : '',
                'country'         => $company['Region']['country'],
                'state'           => $company['SubRegion']['name'],
                'selected'        => $selected
            );
        }

        // empty row
        $items[] = array(
            'id'              => Constants::GRID_LAST_ROW,
            'company_name'    => null,
            'registration_no' => null,
            'business_type'   => null,
            'country'         => null,
            'state'           => null
        );

        $form = new BaseForm();

        return $this->renderJson(array(
            'data' => array(
                'identifier' => 'id',
                'items'      => $items
            ),
            '_csrf_token' => $form->getCSRFToken()
        ));
    }

    public function executeSaveCurrentSelectedSupplierInfo(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getPostParameter('poId'))
        );

        $companyIds      = $request->getPostParameter('companyIds');
        $ids             = array();
        $supplierName    = null;
        $supplierAddress = array();

        foreach ( $companyIds as $companyId )
        {
            $poSupplier = $purchaseOrder->PurchaseOrderSupplier;

            try
            {
                $poSupplier->purchase_order_id = $purchaseOrder->id;
                $poSupplier->company_id        = $companyId;
                $poSupplier->save();

                $poSupplier->refresh(true);

                $poInformation = $purchaseOrder->PurchaseOrderInformation;

                // will also save a reference of company's address to PurchaseOrderInformation
                PurchaseOrderInformationTable::setCompanyAddress($poInformation, $poSupplier);

                $poInformation->refresh();

                $supplierName = $poSupplier->Company->name;

                $supplierAddress = array(
                    $poInformation->supplier_address_1,
                    $poInformation->supplier_address_2,
                    $poInformation->supplier_address_3,
                );

                $ids[]   = $poSupplier->id;
                $success = true;
                $errors  = array();
            }
            catch (Exception $e)
            {
                $ids[]   = $request->getPostParameter('rfqId');
                $errors  = $e;
                $success = false;
            }

            $data = array( 'supplierName' => $supplierName, 'supplierAddress' => $supplierAddress, 'success' => $success, 'errorMsgs' => $errors );
        }

        return $this->renderJson($data);
    }

    public function executeUpdateCompanyInformation(sfWebRequest $request)
    {
        $poId = $request->getParameter('poId');

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($poId)
        );

        $company         = Doctrine_Core::getTable('Company')->find($request->getParameter('companyId'));
        $company         = ( $company ) ? $company : new Company();
        $isNew           = $company->isNew();
        $form            = new CompanyForm($company);
        $supplierName    = null;
        $supplierAddress = array();

        if ( $this->isFormValid($request, $form) )
        {
            $company = $form->save();
            $id      = $company->getId();
            $success = true;
            $errors  = array();

            // for PO add new Company's form
            if ( $isNew )
            {
                $poSupplier             = $purchaseOrder->PurchaseOrderSupplier;
                $poSupplier->company_id = $id;
                $poSupplier->save();

                $poSupplier->refresh(true);

                $poInformation = $purchaseOrder->PurchaseOrderInformation;

                // will also save a reference of company's address to PurchaseOrderInformation
                PurchaseOrderInformationTable::setCompanyAddress($poInformation, $poSupplier);

                $supplierName = $poSupplier->Company->name;

                $supplierAddress = array(
                    $poInformation->supplier_address_1,
                    $poInformation->supplier_address_2,
                    $poInformation->supplier_address_3,
                );
            }
        }
        else
        {
            $id      = $request->getPostParameter('id');
            $errors  = $form->getErrors();
            $success = false;
            $isNew   = false;
        }

        $data = array( 'id' => $id, 'isNew' => $isNew, 'companyName' => $form->getObject()->name, 'supplierName' => $supplierName, 'supplierAddress' => $supplierAddress, 'success' => $success, 'errorMsgs' => $errors );

        return $this->renderJson($data);
    }

    public function executeGeneratePrintout(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
        );

        $poTreeItems   = array();
        $poCount       = Utilities::generatePurchaseOrderReferenceNo($purchaseOrder->po_count);
        $stylesheet    = file_get_contents(sfConfig::get('sf_web_dir') . '/css/purchaseOrder.css');
        $poInformation = $purchaseOrder->PurchaseOrderInformation;
        $poTaxes       = $poInformation->PurchaseOrderTaxes;
        $poSupplier    = $purchaseOrder->PurchaseOrderSupplier;
        $myCompanyInfo = Doctrine_Core::getTable('myCompanyProfile')->find(1);
        $currencyCode  = $poInformation->Currency->currency_code;

        // get associated PO's Item listing
        $poItems = PurchaseOrderItemTable::getItemListingByPurchaseOrder($purchaseOrder);

        foreach ( $poItems as $poItem )
        {
            $poItemsId[$poItem['resource_item_id']] = $poItem['resource_item_id'];

            $quantity = number_format((float) $poItem['quantity'], 2, '.', '');
            $rates    = number_format((float) $poItem['rates'], 2, '.', '');
            $amount   = number_format((float) $quantity * $rates, 2, '.', '');

            $poFromDbItems[$poItem['resource_item_id']] = array(
                'poItemId'       => $poItem['id'],
                'quantity'       => $quantity,
                'rates'          => $rates,
                'amount'         => $amount,
                'poItemRemarkId' => $poItem['remark_id'],
                'remarks'        => $poItem['remark'],
            );
        }

        if ( !empty( $poItemsId ) )
        {
            $poTreeItems = PurchaseOrderItemTable::getHierarchyItemListingFromResourceLibraryByPOItemIds($poItemsId, $poFromDbItems);

            unset( $poFromDbItems );
        }

        $summaryPageGenerator = new sfBuildspacePurchaseOrderGenerator($purchaseOrder, $poTreeItems);

        $page = $summaryPageGenerator->generatePage();

        $pdfGen = new WkHtmlToPdf(array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => 8,
            'margin-right'   => 7,
            'margin-bottom'  => 3,
            'margin-left'    => 7,
            'page-size'      => 'A4',
            'orientation'    => "Portrait"
        ));

        $grandTotal = $page['po_grand_total_before_tax'];

        $printWithoutCents = $poInformation->print_without_cents ? 0 : 2;
        $priceFormat       = $poInformation->price_format == 'opposite' ? array( ',', '.' ) : array( '.', ',' );

        if ( $page['summary_items'] instanceof SplFixedArray )
        {
            $lastPageCount = count($page['summary_items']);

            foreach ( $page['summary_items'] as $pageCount => $summaryItems )
            {
                $continuePage = false;

                $layout = $this->getPartial('purchaseOrder/pageLayout', array(
                    'title'      => 'Purchase Order',
                    'stylesheet' => $stylesheet
                ));

                $pageCount += 1;

                $isLastPage = $pageCount == $lastPageCount ? true : false;
                $maxRow     = $summaryPageGenerator->MAX_ROWS;

                if ( !$isLastPage )
                {
                    $maxRow = $summaryPageGenerator->DEFAULT_MAX_ROWS;
                }

                if ( $pageCount > 1 )
                {
                    $continuePage = true;
                }

                $layout .= $this->getPartial('purchaseOrder/itemPageLayout', array(
                    'myCompanyName'     => ( $myCompanyInfo ) ? $myCompanyInfo->name : "&nbsp;",
                    'supplierName'      => $poSupplier->Company->name,
                    'date'              => $purchaseOrder->updated_at,
                    'poReferenceNo'     => $purchaseOrder->prefix . $poCount,
                    'itemPage'          => $summaryItems,
                    'isLastPage'        => $isLastPage,
                    'MAX_ROWS'          => $maxRow,
                    'pageTotal'         => $page['sum_amount_pages'][$pageCount - 1],
                    'continuePage'      => $continuePage,
                    'pageCount'         => $pageCount,
                    'lastPageCount'     => $lastPageCount,
                    'poTaxes'           => $poTaxes,
                    'grandTotal'        => $grandTotal,
                    'poInformation'     => $poInformation,
                    'currencyCode'      => $currencyCode,
                    'priceFormat'       => $priceFormat,
                    'printWithoutCents' => $printWithoutCents,
                ));

                unset( $summaryItems );

                $pdfGen->addPage($layout);
            }
        }

        // ... send to client as file download
        return $pdfGen->send();
    }

    private function savePurchaseOrderTaxes(sfWebRequest $request, PurchaseOrderInformation $purchaseOrderInformation)
    {
        $counter = 1;
        $taxes   = PurchaseOrderTaxTable::getAvailableTaxes($purchaseOrderInformation);

        foreach ( $taxes as $tax )
        {
            $taxNameReq    = $request->getPostParameter("tax_name[" . $counter . "]");
            $taxName       = empty( $taxNameReq ) ? null : $taxNameReq;
            $taxPercentage = (float) $request->getPostParameter("tax_percentage[{$counter}]");

            $tax->tax_name   = $taxName;
            $tax->percentage = $taxPercentage;
            $tax->save();

            $counter ++;
        }

        $taxCount = $taxes->count();

        if ( empty( $taxCount ) )
        {
            foreach ( range(1, PurchaseOrderTax::ROW_LIMIT) as $range )
            {
                $taxNameReq    = $request->getPostParameter("tax_name[" . $range . "]");
                $taxName       = empty( $taxNameReq ) ? null : $taxNameReq;
                $taxPercentage = (float) $request->getPostParameter("tax_percentage[" . $range . "]");

                $tax                                = new PurchaseOrderTax();
                $tax->purchase_order_information_id = $purchaseOrderInformation->id;
                $tax->tax_name                      = $taxName;
                $tax->percentage                    = $taxPercentage;
                $tax->save();
            }
        }
    }
}