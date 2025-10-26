<?php

/**
 * requestForQuotation actions.
 *
 * @package    buildspace
 * @subpackage requestForQuotation
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class requestForQuotationActions extends BaseActions {

    // ==========================================================================================================================================
    // RFQ Form
    // ==========================================================================================================================================
    public function executeGetProjectListings(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => ProjectStructureTable::getAllProjects()
        ));
    }

    public function executeGetRFQListing(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $data['items'] = array();

        $rfqs = DoctrineQuery::create()
            ->select('r.id, r.prefix, r.rfq_count, p.id, p.title, r.type, r.status, r.created_at, s.id as supplier_id, c.id as creator_id, pr.name')
            ->from('RFQ r')
            ->leftJoin('r.Creator c')
            ->leftJoin('c.Profile pr')
            ->leftJoin('r.RequestForQuotationSuppliers s')
            ->leftJoin('r.Project p')
            ->addOrderBy('r.id DESC')
            ->fetchArray();

        foreach ( $rfqs as $rfq )
        {
            $form     = new BaseForm();
            $rfqCount = Utilities::generateRFQReferenceNo($rfq['type'], $rfq['rfq_count']);

            $data['items'][] = array(
                'id'                 => $rfq['id'],
                'rfq_no'             => $rfq['prefix'] . $rfqCount,
                'project_id'         => isset( $rfq['Project'] ) ? $rfq['Project']['id'] : null,
                'project'            => isset( $rfq['Project'] ) ? $rfq['Project']['title'] : '-',
                'status'             => RFQTable::getStatusText($rfq['status']),
                'type'               => RFQTable::getTypeText($rfq['type']),
                'type_id'            => $rfq['type'],
                'date_issued'        => date('d/m/Y H:i', strtotime($rfq['created_at'])),
                'issued_by'          => ( $rfq['Creator']['Profile']['name'] ) ? $rfq['Creator']['Profile']['name'] : '-',
                'number_of_supplier' => count($rfq['RequestForQuotationSuppliers']),
                '_csrf_token'        => $form->getCSRFToken(),
            );
        }

        $data['items'][] = array(
            'id'                 => null,
            'rfq_no'             => null,
            'project'            => null,
            'status'             => null,
            'type'               => RFQTable::getTypeText(),
            'date_issued'        => '-',
            'issued_by'          => null,
            'number_of_supplier' => null,
            '_csrf_token'        => null,
        );

        $data['identifier'] = 'id';

        return $this->renderJson($data);
    }

    public function executeGetRequestForQuotationInformation(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $rfq  = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId'));
        $rfq  = ( $rfq ) ? $rfq : new RFQ();
        $form = new RFQForm($rfq);

        $data['requestForQuotationForm'] = array(
            'rfq[prefix]'        => $form->getObject()->prefix,
            'rfq[type]'          => $form->getObject()->type,
            'rfq[region_id]'     => $form->getObject()->region_id ? : 0,
            'rfq[sub_region_id]' => $form->getObject()->sub_region_id ? : 0,
            'rfq[status]'        => $form->getObject()->status,
            'rfq[_csrf_token]'   => $form->getCSRFToken(),
        );

        // get form selections value
        $data['rfqTypeSelections'] = RFQTable::typeSelections();

        return $this->renderJson($data);
    }

    public function executeUpdateRFQInformation(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() AND $request->isMethod('post'));

        $rfq  = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId'));
        $rfq  = ( $rfq ) ? $rfq : new RFQ();
        $form = new RFQForm($rfq);

        if ( $this->isFormValid($request, $form) )
        {
            $rfq     = $form->save();
            $id      = $rfq->getId();
            $success = true;
            $errors  = array();
        }
        else
        {
            $id      = $request->getPostParameter('rfqId');
            $errors  = $form->getErrors();
            $success = false;
        }

        $data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors );

        return $this->renderJson($data);
    }

    public function executeDeleteRFQInformation(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId'))
        );

        try
        {
            $rfq->delete();

            $success = true;
        } catch (Exception $e)
        {
            $success = false;
        }

        $data = array( 'rfqId' => $request->getPostParameter('rfqId'), 'success' => $success );

        return $this->renderJson($data);
    }

    public function executeGetCurrentProjectRFQNo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectStructureId'))
        );

        $rfq              = RFQTable::getLatestRFQByProject($project);
        $rfqProjectPrefix = ( $rfq ) ? $rfq['prefix'] : null;
        $rfqProjectCount  = RFQProjectTable::getLatestRFQNoCountByProjectId($project);

        return $this->renderJson(array(
            'rfqPrefix' => $rfqProjectPrefix,
            'rfqCount'  => sprintf(sfConfig::get('app_rfq_project_ref_no_zero_fill_length'), $rfqProjectCount),
        ));
    }
    // ==========================================================================================================================================

    // ==========================================================================================================================================
    // Resource RFQ No
    // ==========================================================================================================================================
    public function executeGetResourceRFQNo(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $rfqResourceCount = RFQTable::getLatestRFQNoCountByTypeResource();

        return $this->renderJson(array( 'rfqCount' => sprintf(sfConfig::get('app_rfq_resource_ref_no_zero_fill_length'), $rfqResourceCount) ));
    }
    // ==========================================================================================================================================

    // ==========================================================================================================================================
    // WorkArea RFQ Item Listing
    // ==========================================================================================================================================
    public function executeGetRFQItemListing(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId'))
        );

        $rfqItemsId     = array();
        $rfqFromDbItems = array();
        $rfqTreeItems   = array();

        // get associated RFQ's Item listing
        $rfqItems = RFQItemTable::getItemListingByRFQId($rfq->id);

        foreach ( $rfqItems as $rfqItem )
        {
            $rfqItemsId[$rfqItem['resource_item_id']] = $rfqItem['resource_item_id'];

            $rfqFromDbItems[$rfqItem['resource_item_id']] = array(
                'rfqItemId'       => $rfqItem['id'],
                'quantity'        => number_format((float) $rfqItem['quantity'], 2, '.', ''),
                'rfqItemRemarkId' => $rfqItem['remark_id'],
                'remarks'         => $rfqItem['remark'],
            );
        }

        if ( count($rfqItemsId) > 0 )
        {
            $rfqTreeItems = RFQItemTable::getHierarchyItemListingFromResourceLibraryByRFQItemIds($rfqItemsId, $rfqFromDbItems);

            unset( $rfqFromDbItems );
        }

        // default empty row
        $emptyRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'uom'         => null,
            'quantity'    => null,
            'remarks'     => null,
        );

        array_push($rfqTreeItems, $emptyRow);

        $form = new BaseForm();

        // assign csrf token to each available item
        foreach ( $rfqTreeItems as $key => $rfqTreeItem )
        {
            $rfqTreeItems[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        $data = array(
            'identifier' => 'id',
            'items'      => $rfqTreeItems,
        );

        return $this->renderJson($data);
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

        foreach ( $records as $key => $record )
        {
            $resources[] = array(
                'id'          => $record['id'],
                'description' => $record['name'],
            );
        }

        // default last row
        $defaultLastRow = array(
            'id'          => null,
            'description' => null,
        );

        array_push($resources, $defaultLastRow);

        $data = array(
            'identifier' => 'id',
            'items'      => $resources
        );

        return $this->renderJson($data);
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

        foreach ( $records as $key => $record )
        {
            $resources[] = array(
                'id'          => $record['id'],
                'description' => $record['description'],
                'updated_at'  => $record['updated_at'],
                '_csrf_token' => $form->getCSRFToken(),
            );
        }

        // default last row
        $defaultLastRow = array(
            'id'          => null,
            'description' => null,
            'updated_at'  => '-',
            '_csrf_token' => null,
        );

        array_push($resources, $defaultLastRow);

        $data = array(
            'identifier' => 'id',
            'items'      => $resources
        );

        return $this->renderJson($data);
    }

    public function executeGetResourceItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) AND
            $trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('tradeId'))
        );

        $pdo           = $trade->getTable()->getConnection()->getDbh();
        $resourceItems = array();
        $rfqItemsId    = array();
        $form          = new BaseForm();

        // get associated RFQ's Item listing
        $rfqItems = RFQItemTable::getItemListingByRFQId($rfq->id);

        foreach ( $rfqItems as $rfqItem )
        {
            $rfqItemsId[] = $rfqItem['resource_item_id'];
        }

        $sql = "SELECT i.resource_trade_id, i.id FROM " . ResourceItemTable::getInstance()->getTableName() . " i
            WHERE i.id = i.root_id AND i.resource_trade_id = " . $trade->id . " AND i.deleted_at IS NULL ORDER BY i.priority";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array());

        $roots = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        if ( array_key_exists($trade->id, $roots) )
        {
            $rootIds         = $roots[$trade->id];
            $implodedRootIds = implode(',', $rootIds);

            if ( count($rfqItemsId) > 0 )
            {
                $implodedRfqItemsId = implode(', ', $rfqItemsId);

                $notInQuery = 'AND c.id NOT IN (' . $implodedRfqItemsId . ')';
            }
            else
            {
                $notInQuery = '';
            }

            $stmt = $pdo->prepare("SELECT DISTINCT c.id, c.description, c.type::text, uom.id as uom_id, c.lft, c.level,
            c.resource_trade_id, c.level, c.updated_at, uom.symbol AS uom_symbol,
            c.priority, c.lft, c.level
            FROM " . ResourceItemTable::getInstance()->getTableName() . " p
            JOIN " . ResourceItemTable::getInstance()->getTableName() . " c
            ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id
            AND uom.deleted_at IS NULL WHERE p.id IN (" . $implodedRootIds . ") " . $notInQuery . " AND p.id = c.root_id
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

        $data = array(
            'identifier' => 'id',
            'items'      => $resourceItems
        );

        return $this->renderJson($data);
    }

    public function executeCopyResourceItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId'))
        );

        $errorMsg = null;
        $items    = array();

        try
        {
            $ids = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));

            $rfq->copyResourceItems($ids);

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $data = array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items );

        return $this->renderJson($data);
    }

    public function executeCopyProjectResourceItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) AND
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

        try
        {
            $ids = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));

            $rfq->copyProjectResourceItems($ids, $resource['id'], $project->id);

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $data = array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items );

        return $this->renderJson($data);
    }

    public function executeUpdateRFQItemQuantity(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfqItem = Doctrine_Core::getTable('RFQItem')->findOneBy('idAndrequest_for_quotation_id', array(
                $request->getPostParameter('rfqItemId'),
                $request->getPostParameter('rfqId')
            ))
        );

        $success  = false;
        $errorMsg = array();
        $items    = array();

        try
        {
            $value = ( is_numeric($request->getPostParameter('val')) ) ? $request->getPostParameter('val') : 0;

            $rfqItem->quantity = number_format((float) $value, 2, '.', '');
            $rfqItem->save();

            $success = true;

            $items[] = array(
                'id'       => $rfqItem->resource_item_id,
                'quantity' => $rfqItem->quantity,
            );
        } catch (Exception $e)
        {
            $errorMsg = $e;
        }

        $data = array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items );

        return $this->renderJson($data);
    }

    public function executeDeleteRFQItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) AND
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
        );

        $items    = array();
        $errorMsg = array();

        try
        {
            $affectedNodes = RFQItemTable::deleteLikeResourceLibraryTree($rfq, $resourceItem);
            $success       = true;
        } catch (Exception $e)
        {
            $errorMsg      = $e->getMessage();
            $affectedNodes = array();
            $success       = false;
        }

        $data = array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => $affectedNodes );

        return $this->renderJson($data);
    }

    public function executeGetResources(sfWebRequest $request)
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
        WHERE s.root_id = " . $project->id . " AND r.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0 AND ifc.deleted_at IS NULL
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL AND r_lib.deleted_at IS NULL ORDER BY r_lib.id");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $emptyRow = array(
            'id'   => Constants::GRID_LAST_ROW,
            'name' => '',
        );

        array_push($records, $emptyRow);

        $data = array(
            'identifier' => 'id',
            'items'      => $records,
        );

        return $this->renderJson($data);
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
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0 AND ifc.deleted_at IS NULL
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

        $data = array(
            'identifier' => 'id',
            'items'      => $results,
        );

        return $this->renderJson($data);
    }

    public function executeGetProjectResourceItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) and
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
        WHERE s.root_id = " . $project->id . " AND r.resource_library_id = " . $resource['id'] . " AND t.resource_trade_library_id = " . $trade['id'] . "
        AND bur.resource_item_library_id IS NOT NULL
        AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0
        AND r.deleted_at IS NULL AND t.deleted_at IS NULL AND bur.deleted_at IS NULL AND ifc.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL");

        $stmt->execute(array());

        $buildUpRateItemWithResourceItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ( count($buildUpRateItemWithResourceItemIds) > 0 )
        {
            $buildUpRateItemIds     = array();
            $resourceItemLibraryIds = array();
            $rfqItemsId             = array();

            foreach ( $buildUpRateItemWithResourceItemIds as $record )
            {
                $buildUpRateItemIds[] = $record['id'];

                if ( !in_array($record['resource_item_library_id'], $resourceItemLibraryIds) )
                {
                    $resourceItemLibraryIds[] = $record['resource_item_library_id'];
                }
            }

            // get associated RFQ's Item listing
            $rfqItems = RFQItemTable::getItemListingByRFQId($rfq->id);

            foreach ( $rfqItems as $rfqItem )
            {
                $rfqItemsId[] = $rfqItem['resource_item_id'];
            }

            $totalCostAndQuantityByResourceItems = ResourceItemTable::calculateTotalForResourceAnalysis($resourceItemLibraryIds, $resource['id'], $project->id);

            $implodedResourceItems = implode(',', $resourceItemLibraryIds);
            $notInQuery            = '';

            if ( count($rfqItemsId) > 0 )
            {
                $implodedRfqItemsId = implode(', ', $rfqItemsId);

                $notInQuery = 'AND c.id NOT IN (' . $implodedRfqItemsId . ')';
            }

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type::text, p.uom_id, p.level, p.priority, p.lft, uom.symbol AS uom_symbol
            FROM " . ResourceItemTable::getInstance()->getTableName() . " c
            JOIN " . ResourceItemTable::getInstance()->getTableName() . " p
            ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE c.root_id = p.root_id AND c.type <> " . ResourceItem::TYPE_HEADER . "
            AND c.id IN (" . $implodedResourceItems . ") " . $notInQuery . "
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

        $data = array(
            'identifier' => 'id',
            'items'      => $results,
        );

        return $this->renderJson($data);
    }

    public function executeGetRFQItemRemarkInformation(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $rfqItemRemark = Doctrine_Core::getTable('RFQItemRemark')->find($request->getParameter('rfqItemRemarkId'));
        $rfqItemRemark = ( $rfqItemRemark ) ? $rfqItemRemark : new RFQItemRemark();

        $form = new RFQItemRemarkForm($rfqItemRemark);

        $data['rfqItemRemarkForm'] = array(
            'rfq_item_remark[_csrf_token]' => $form->getCSRFToken(),
            '_csrf_token'                  => $form->getCSRFToken(),
        );

        return $this->renderJson($data);
    }

    public function executeUpdateRFQItemRemark(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $rfqItem = Doctrine_Core::getTable('RFQItem')->find($request->getParameter('rfqItemId'))
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

        $data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors );

        return $this->renderJson($data);
    }

    public function executeUpdateSelectedPreviousRFQItemRemark(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $rfqItem = Doctrine_Core::getTable('RFQItem')->find($request->getParameter('rfqItemId'))
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

            if ( $rfqItem->remark_id == $id )
            {
                $rfqItem->remark_id = null;

                $data = array(
                    'rfqItemRemarkId' => $id,
                    'remarks'         => "",
                );
            }
            else
            {
                $rfqItem->remark_id = $id;

                $data = array(
                    'rfqItemRemarkId' => $id,
                    'remarks'         => $form->getObject()->description,
                );
            }

            $rfqItem->save();
        }
        else
        {
            $id      = $request->getPostParameter('rfqItemRemarkId');
            $errors  = $form->getErrors();
            $success = false;
        }

        $data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors, 'data' => $data );

        return $this->renderJson($data);
    }

    public function executeGetPreviousRFQItemRemarks(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
        );

        $pdo = Doctrine_Manager::getInstance()->connection();

        $rfqItem = Doctrine_Core::getTable('RFQItem')->find($request->getParameter('rfqItemId'));

        $stmt = $pdo->prepare("SELECT DISTINCT(rfqri.id), rfqri.description FROM " . RFQItemRemarkTable::getInstance()->getTableName() . " rfqri
        JOIN " . RFQItemTable::getInstance()->getTableName() . " rfqi ON rfqri.resource_item_id = rfqi.resource_item_id
        WHERE rfqi.resource_item_id = " . $resourceItem->id . "
        AND rfqri.deleted_at IS NULL
        ORDER BY rfqri.id DESC");
        $stmt->execute(array());

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach ( $results as $key => $result )
        {
            $selected = ( $result['id'] == $rfqItem->remark_id ) ? true : false;

            $results[$key]['selected']    = $selected;
            $results[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        $emptyRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
        );

        array_push($results, $emptyRow);

        $data = array(
            'identifier' => 'id',
            'items'      => array_values($results),
        );

        return $this->renderJson($data);
    }

    public function executeDeleteRFQItemRemark(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfqItemRemark = Doctrine_Core::getTable('RFQItemRemark')->find($request->getPostParameter('rfqItemRemarkId'))
        );

        try
        {
            $rfqItemRemark->delete();

            $success = true;
        } catch (Exception $e)
        {
            $success = false;
        }

        $data = array( 'rfqId' => $request->getPostParameter('rfqItemRemarkId'), 'success' => $success );

        return $this->renderJson($data);
    }
    // ==========================================================================================================================================

    // ==========================================================================================================================================
    // WorkArea RFQ Information
    // ==========================================================================================================================================
    public function executeGetRFQWorkareaInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId'))
        );

        $form     = new BaseForm();
        $rfqCount = Utilities::generateRFQReferenceNo($rfq->type, $rfq->rfq_count);

        $data['requestForQuotationForm'] = array(
            'rfq[project_name]'    => $rfq->Project->title,
            'rfq[site_address]'    => $rfq->Project->MainInformation->site_address,
            'rfq[rfqNo]'           => $rfq->prefix . $rfqCount,
            'typeText'             => RFQTable::getTypeText($rfq->type),
            'rfq[region_name]'     => $rfq->Region->country,
            'rfq[sub_region_name]' => $rfq->SubRegion->name,
            'status'               => (string) $rfq->status,
            'rfq[status]'          => RFQTable::getStatusText($rfq->status),
            '_csrf_token'          => $form->getCSRFToken(),
            'typeId'               => $rfq->type,
        );

        $data['rfqStatusSelections'] = RFQTable::statusSelections();

        return $this->renderJson($data);
    }

    public function executeUpdateRFQWorkareaInformation(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId'))
        );

        try
        {
            $rfq->status = $request->getPostParameter('status');
            $rfq->save();

            $id      = $rfq->id;
            $success = true;
            $errors  = array();
        } catch (Exception $e)
        {
            $id      = $request->getPostParameter('rfqId');
            $errors  = $e;
            $success = false;
        }

        $data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors );

        return $this->renderJson($data);
    }
    // ==========================================================================================================================================

    // ==========================================================================================================================================
    // WorkArea RFQ Supplier Listing
    // ==========================================================================================================================================
    public function executeGetCompanyListing(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId'))
        );

        // get previous selected supplier ID if available
        $suppliers   = $rfq->getRequestForQuotationSuppliers();

        $query = DoctrineQuery::create()
            ->select('c.id, c.name, c.reference_id, c.registration_no, r.country, sr.name, c.updated_at')
            ->from('Company c')
            ->leftJoin('c.Region r')
            ->leftJoin('c.SubRegion sr')
            ->where('c.reference_id IS NOT NULL');

        if ( !empty($suppliers) )
        {
            $query->whereNotIn('c.id', array_column($suppliers->toArray(), 'company_id'));
        }

        $companies = $query->orderBy('c.name ASC')->fetchArray();

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
            $items[] = array(
                'id'              => $company['id'],
                'company_name'    => $company['name'],
                'registration_no' => $company['registration_no'],
                'business_type'   => isset($contractGroupCategories[$company['reference_id']]) ? $contractGroupCategories[$company['reference_id']] : '',
                'country'         => $company['Region']['country'],
                'state'           => $company['SubRegion']['name']
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

    public function executeGetRFQSupplierListing(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId'))
        );

        $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId'));

        $items = array();

        $suppliers = DoctrineQuery::create()
            ->select('p.id, p.status, m.id, m.name, m.registration_no, p.updated_at')
            ->from('RFQSupplier p')
            ->leftJoin('p.Company m')
            ->where('p.request_for_quotation_id = ?', $rfq->id)
            ->addOrderBy('p.id DESC')
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $form = new BaseForm();

        foreach ( $suppliers as $supplier )
        {
            $items[] = array(
                'id'              => $supplier['id'],
                'supplier_id'     => $supplier['Company']['id'],
                'supplier_name'   => $supplier['Company']['name'],
                'registration_no' => $supplier['Company']['registration_no'],
                'status'          => RFQSupplierTable::getStatusText($supplier['status']),
                'updated_at'      => date('d/m/Y H:i', strtotime($supplier['updated_at'])),
                '_csrf_token'     => $form->getCSRFToken(),
            );
        }

        $items[] = array(
            'id'              => Constants::GRID_LAST_ROW,
            'supplier_id'     => null,
            'supplier_name'   => null,
            'registration_no' => null,
            'status'          => null,
            'updated_at'      => '-',
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeSaveCurrentSelectedSupplierInfo(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getPostParameter('rfqId'))
        );

        $companyIds = $request->getPostParameter('companyIds');
        $ids        = array();
        $errors     = array();
        $success    = false;

        try
        {
            foreach ( $companyIds as $companyId )
            {
                $rfqSupplier = Doctrine_Core::getTable('RFQSupplier')->findOneBy('request_for_quotation_idAndcompany_id', array(
                    $request->getPostParameter('rfqId'),
                    $companyId
                ));

                $rfqSupplier = ( $rfqSupplier ) ? $rfqSupplier : new RFQSupplier();

                $rfqSupplier->request_for_quotation_id = $rfq->id;
                $rfqSupplier->company_id               = $companyId;
                $rfqSupplier->save();

                $ids[]   = $rfqSupplier->id;
            }

            $success = true;
        }
        catch (Exception $e)
        {
            $errors  = $e;
        }



        return $this->renderJson(array(
            'ids'       => $ids,
            'success'   => $success,
            'errorMsgs' => $errors
        ));
    }

    public function executeUpdateSupplierStatus(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getPostParameter('rfqId')) AND
            $rfqSupplier = Doctrine_Core::getTable('RFQSupplier')->find($request->getPostParameter('supplierId'))
        );

        try
        {
            $rfqSupplier->status = $request->getPostParameter('status');
            $rfqSupplier->save();

            $id      = $rfqSupplier->id;
            $success = true;
            $errors  = array();
            $status  = RFQSupplierTable::getStatusText($rfqSupplier->status);
        } catch (Exception $e)
        {
            $id      = $request->getPostParameter('rfqId');
            $errors  = $e;
            $success = false;
            $status  = null;
        }

        $data = array( 'id' => $id, 'success' => $success, 'status' => $status, 'errorMsgs' => $errors );

        return $this->renderJson($data);
    }

    public function executeDeleteSupplierInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getPostParameter('rfqId')) AND
            $rfqSupplier = Doctrine_Core::getTable('RFQSupplier')->find($request->getPostParameter('supplierId'))
        );

        $rfqSupplierId = $rfqSupplier->id;

        try
        {
            $rfqSupplier->delete();

            $success = true;
        } catch (Exception $e)
        {
            $success = false;
        }

        $data = array( 'rfqSupplierId' => $rfqSupplierId, 'success' => $success );

        return $this->renderJson($data);
    }

    public function executeGetRFQSupplierItemRates(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) AND
            $supplier = Doctrine_Core::getTable('RFQSupplier')->find($request->getParameter('supplierId'))
        );

        $rfqItemsId     = array();
        $rfqFromDbItems = array();
        $rfqTreeItems   = array();

        // get associated RFQ's Item listing
        $rfqItems = RFQItemTable::getItemAndRatesListingByRFQAndSupplier($rfq, $supplier);

        foreach ( $rfqItems as $rfqItem )
        {
            $rfqItemsId[$rfqItem['resource_item_id']] = $rfqItem['resource_item_id'];

            $rfqFromDbItems[$rfqItem['resource_item_id']] = array(
                'rfqItemId'       => $rfqItem['id'],
                'quantity'        => number_format((float) $rfqItem['quantity'], 2, '.', ''),
                'rate_id'         => $rfqItem['rate_id'],
                'rate'            => number_format((float) $rfqItem['rate'], 2, '.', ''),
                'rfqItemRemarkId' => $rfqItem['remark_id'],
                'remarks'         => $rfqItem['remark'],
            );
        }

        if ( count($rfqItemsId) > 0 )
        {
            $implodedItemIds = implode(', ', $rfqItemsId);

            $pdo = Doctrine_Manager::getInstance()->connection();

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type, p.uom_id, p.level, p.priority,
            p.lft, uom.symbol AS uom, rt.priority as rt_priority, r.id as resource_id
            FROM " . ResourceItemTable::getInstance()->getTableName() . " c
            JOIN " . ResourceItemTable::getInstance()->getTableName() . " p
            ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN " . ResourceTradeTable::getInstance()->getTableName() . " rt on p.resource_trade_id = rt.id AND rt.deleted_at IS NULL
            JOIN " . ResourceTable::getInstance()->getTableName() . " r on rt.resource_id = r.id AND r.deleted_at IS NULL
            WHERE c.root_id = p.root_id AND c.type <> " . ResourceItem::TYPE_HEADER . "
            AND c.id IN (" . $implodedItemIds . ")
            AND c.deleted_at IS NULL AND p.deleted_at IS NULL
            ORDER BY r.id, rt.priority, p.priority, p.lft, p.level ASC");
            $stmt->execute(array());

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $results as $result )
            {
                if ( isset ( $rfqFromDbItems[$result['id']] ) )
                {
                    $rfqItemId       = $rfqFromDbItems[$result['id']]['rfqItemId'];
                    $quantity        = $rfqFromDbItems[$result['id']]['quantity'];
                    $rfqItemRemarkId = $rfqFromDbItems[$result['id']]['rfqItemRemarkId'];
                    $remarks         = $rfqFromDbItems[$result['id']]['remarks'];
                    $rateId          = $rfqFromDbItems[$result['id']]['rate_id'];
                    $rate            = $rfqFromDbItems[$result['id']]['rate'];
                }
                else
                {
                    $rfqItemId       = - 1;
                    $quantity        = 0;
                    $rfqItemRemarkId = - 1;
                    $remarks         = null;
                    $rateId          = - 1;
                    $rate            = - 1;
                }

                $rfqTreeItems[] = array(
                    'rfqItemId'       => $rfqItemId,
                    'id'              => $result['id'],
                    'root_id'         => $result['root_id'],
                    'description'     => $result['description'],
                    'type'            => $result['type'],
                    'uom_id'          => $result['uom_id'],
                    'uom'             => $result['uom'],
                    'level'           => $result['level'],
                    'priority'        => $result['priority'],
                    'lft'             => $result['lft'],
                    'quantity'        => $quantity,
                    'rfqItemRemarkId' => $rfqItemRemarkId,
                    'rateId'          => $rateId,
                    'rate'            => $rate,
                    'remarks'         => $remarks,
                );
            }

            unset( $results, $rfqItemsId, $rfqFromDbItems );
        }

        // default empty row
        $emptyRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'uom'         => null,
            'quantity'    => null,
            'remarks'     => null,
        );

        array_push($rfqTreeItems, $emptyRow);

        $form = new BaseForm();

        // assign csrf token to each available item
        foreach ( $rfqTreeItems as $key => $rfqTreeItem )
        {
            $rfqTreeItems[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        $data = array(
            'identifier' => 'id',
            'items'      => $rfqTreeItems,
        );

        return $this->renderJson($data);
    }

    public function executeUpdateRFQSupplierItemRate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $rfq = Doctrine_Core::getTable('RFQ')->find($request->getParameter('rfqId')) AND
            $supplier = Doctrine_Core::getTable('RFQSupplier')->find($request->getParameter('supplierId')) AND
            $rfqItem = Doctrine_Core::getTable('RFQItem')->find($request->getParameter('rfqItemId'))
        );

        $items       = array();
        $rfqItemRate = Doctrine_Core::getTable('RFQItemRate')->find($request->getParameter('rateId'));
        $rfqItemRate = ( $rfqItemRate ) ? $rfqItemRate : new RFQItemRate();

        try
        {
            $val = $request->getPostParameter('val');

            if ( $rfqItemRate->isNew() )
            {
                $rfqItemRate->request_for_quotation_item_id     = $rfqItem->id;
                $rfqItemRate->request_for_quotation_supplier_id = $supplier->id;
            }

            $rfqItemRate->rate = $val;
            $rfqItemRate->save();

            $id      = $rfqItemRate->id;
            $success = true;
            $errors  = array();

            $items[] = array(
                'rateId' => $id,
                'rate'   => number_format((float) $val, 2, '.', ''),
            );
        } catch (Exception $e)
        {
            $id      = $request->getPostParameter('rateId');
            $errors  = $e;
            $success = false;
        }

        $data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors, 'items' => $items );

        return $this->renderJson($data);
    }

    // ==========================================================================================================================================

}