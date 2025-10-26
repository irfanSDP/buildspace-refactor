<?php

/**
* stockOut actions.
*
* @package    buildspace
* @subpackage stockOut
* @author     1337 developers
* @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
*/
class stockOutActions extends BaseActions
{
    public function executeGetProjectListingWithStockIns(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest()
        );

        $projects = StockInInvoiceTable::getProjectsThatHasInvoice();

        $projects[] = array(
            'id'         => Constants::GRID_LAST_ROW,
            'title'      => null,
            'status'     => null,
            'status_id'  => null,
            'state'      => null,
            'country'    => null,
            'created_by' => null,
            'created_at' => null,
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $projects,
        ));
    }

    public function executeGetResourceWithStockInsByProject(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
        );

        $resources = ResourceTable::getRecordsThatHasStockInsByProject($project);

        $resources[] = array(
            'id'         => Constants::GRID_LAST_ROW,
            'name'       => null,
            'total_cost' => 0,
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $resources,
        ));
    }

    public function executeGetResourceTradeWithStockInsByProject(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
            $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('resourceId'))
        );

        $resourceTrades = ResourceTradeTable::getRecordsThatHasStockInsByProject($project, $resource);

        $resourceTrades[] = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'total_cost'  => 0,
        );

        $form = new BaseForm();

        foreach ( $resourceTrades as $key => $resourceTrade )
        {
            $resourceTrades[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $resourceTrade );
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $resourceTrades,
        ));
    }

    public function executeGetItemListingsWithDeliveryOrder(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
            $resourceTrade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('resourceTradeId'))
        );

        $items = ResourceItemTable::getRecordsWithDeliveryOrderQuantities($project, $resourceTrade);

        $items[] = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'total_cost'  => 0,
            'do_quantity' => 0,
            'uom_id'      => - 1,
            'uom_symbol'  => null,
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items,
        ));
    }

    public function executeGetInvoiceCostingsByResourceItem(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
        );

        $items   = array();
        $results = StockInInvoiceItemTable::getRecordsFilteredByInvoiceByProjectAndResourceItem($project, $resourceItem);

        foreach ( $results as $result )
        {
            unset( $result['Invoice'] );

            $result['supplier_name'] = empty( $result['supplier_name'] ) ? '-' : $result['supplier_name'];
            $result['invoice_date']  = is_null($result['invoice_date']) ? null : date('d/m/Y', strtotime($result['invoice_date']));

            $items[] = $result;

            unset( $result );
        }

        unset( $results );

        $items[] = array(
            'id'            => Constants::GRID_LAST_ROW,
            'invoice_no'    => null,
            'supplier_name' => null,
            'issued_by'     => null,
            'invoice_date'  => null,
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items,
        ));
    }

    public function executeGetDeliveryOrderCostingsByResourceItem(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
        );

        $items   = array();
        $results = StockInDeliveryOrderItemQuantityTable::getRecordsFilteredByDeliveryOrderByProjectAndResourceItem($project, $resourceItem);

        foreach ( $results as $result )
        {
            unset( $result['StockInDeliveryOrder'] );

            $result['supplier_name']       = empty( $result['supplier_name'] ) ? '-' : $result['supplier_name'];
            $result['delivery_order_date'] = is_null($result['delivery_order_date']) ? null : date('d/m/Y', strtotime($result['delivery_order_date']));

            $items[] = $result;

            unset( $result );
        }

        unset( $results );

        $items[] = array(
            'id'                  => Constants::GRID_LAST_ROW,
            'invoice_no'          => null,
            'delivery_order_no'   => null,
            'supplier_name'       => null,
            'delivery_order_date' => null,
            'remark'              => null,
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items,
        ));
    }

    public function executeGetStockOutQtyByResourceItem(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
        );

        $items   = array();
        $results = StockOutUsedQuantityItemQuantityTable::getRecordsFilteredByDeliveryOrderByProjectAndResourceItem($project, $resourceItem);

        foreach ( $results as $result )
        {
            unset( $result['StockOutUsedQuantity'] );

            $result['running_number'] = Utilities::generateStockOutNo($result['running_number']);
            $result['creator_name']   = empty( $result['creator_name'] ) ? '-' : $result['creator_name'];
            $result['stock_out_date'] = is_null($result['stock_out_date']) ? null : date('d/m/Y', strtotime($result['stock_out_date']));

            $items[] = $result;

            unset( $result );
        }

        unset( $results );

        $items[] = array(
            'id'             => Constants::GRID_LAST_ROW,
            'running_number' => null,
            'creator_name'   => null,
            'stock_out_date' => null,
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items,
        ));
    }

    public function executeCopyDeliveryOrderItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('POST') and
            $usedQuantity = Doctrine_Core::getTable('StockOutUsedQuantity')->find($request->getPostParameter('stockOutUsedQuantityId'))
        );

        $errorMsg = null;
        $conn     = $usedQuantity->getTable()->getConnection();

        try
        {
            $conn->beginTransaction();

            $ids = Utilities::array_filter_integer(explode(',', $request->getPostParameter('ids')));

            $usedQuantity->copyDeliveryOrderItems($ids);

            $success = true;

            $conn->commit();
        }
        catch (Exception $e)
        {
            $conn->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeGetStockOutListings(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
        );

        $items = array();

        $stockOuts = Doctrine_Query::create()
            ->select('souq.id, souq.running_number, souq.stock_out_date, c.id as creator_id, p.name as creator_name')
            ->from('StockOutUsedQuantity souq')
            ->leftJoin('souq.Creator c')
            ->leftJoin('c.Profile p')
            ->where('souq.project_structure_id = ?', array( $project->id ))
            ->orderBy('souq.id DESC')
            ->fetchArray();

        $form = new BaseForm();

        foreach ( $stockOuts as $stockOut )
        {
            $items[] = array(
                'id'             => $stockOut['id'],
                'running_number' => Utilities::generateStockOutNo($stockOut['running_number']),
                'stock_out_date' => date('d/m/Y', strtotime($stockOut['stock_out_date'])),
                'created_by'     => $stockOut['creator_name'],
                '_csrf_token'    => $form->getCSRFToken(),
            );

            unset( $stockOut );
        }

        unset( $stockOuts );

        $items[] = array(
            'id'             => Constants::GRID_LAST_ROW,
            'running_number' => null,
            'stock_out_date' => null,
            'created_by'     => null,
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items,
        ));
    }

    public function executeGetStockOutResourceItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $usedQuantity = Doctrine_Core::getTable('StockOutUsedQuantity')->find($request->getParameter('stockOutUsedQuantityId'))
        );

        $items = StockOutUsedQuantityItemQuantityTable::getResourceItemsTreeStructure($usedQuantity);

        $items[] = array(
            'id'         => Constants::GRID_LAST_ROW,
            'uom_id'     => - 1,
            'uom_symbol' => null,
        );

        $form = new BaseForm();

        foreach ( $items as $key => $item )
        {
            $items[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items,
        ));
    }

    public function executeUpdateStockOutQuantityItemInformation(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $request->hasParameter('field_name')
        );

        $invoiceItem = Doctrine_Core::getTable('StockOutUsedQuantityItemQuantity')->findOneBy('stock_out_used_quantity_idAndresource_item_id', array(
            $request->getPostParameter('stockOutUsedQuantityId'),
            $request->getPostParameter('resourceItemId')
        ));

        $invoiceItem = $invoiceItem ? : new StockOutUsedQuantityItemQuantity();

        if ( $invoiceItem->isNew() )
        {
            $invoiceItem->stock_out_used_quantity_id = $request->getPostParameter('stockOutUsedQuantityId');
            $invoiceItem->resource_item_id           = $request->getPostParameter('resourceItemId');

            $invoiceItem->save();
        }

        $success   = false;
        $errorMsg  = array();
        $items     = array();
        $fieldName = $request->getParameter('field_name');

        $allowedFields = array( StockOutUsedQuantityItemQuantity::QUANTITY );

        try
        {
            if ( !in_array($fieldName, $allowedFields) )
            {
                throw new InvalidArgumentException('Invalid field submitted!');
            }

            $invoiceItem->{$fieldName} = 0;//reset the quantity so we can calculate available qty and validate it with the qty value that need to be set
            $invoiceItem->save();

            $project = $invoiceItem->StockOutUsedQuantity->Project;

            $newDOQuantities = StockInDeliveryOrderItemQuantityTable::getOverAllItemQuantitiesFilterByResourceItemIdByProject($project, [$invoiceItem->resource_item_id]);
            $newSOQuantities = StockOutUsedQuantityItemQuantityTable::getOverAllItemQuantitiesFilterByResourceItemIdByProject($project, [$invoiceItem->resource_item_id]);

            if ( isset($newDOQuantities[$invoiceItem->resource_item_id]) )
            {
                $doQuantity = $newDOQuantities[$invoiceItem->resource_item_id];
            }

            if ( isset($newSOQuantities[$invoiceItem->resource_item_id]) )
            {
                $soQuantity = $newSOQuantities[$invoiceItem->resource_item_id];
            }

            $availableQuantity = $doQuantity - $soQuantity;

            $value = ( is_numeric($request->getPostParameter('val')) ) ? $request->getPostParameter('val') : 0;

            $value = ((float)$value > $availableQuantity) ? $availableQuantity : $value;

            $invoiceItem->{$fieldName} = round((float) $value, 2);
            $invoiceItem->save();

            $success = true;

            $newDOQuantities = StockInDeliveryOrderItemQuantityTable::getOverAllItemQuantitiesFilterByResourceItemIdByProject($project, [$invoiceItem->resource_item_id]);
            $newSOQuantities = StockOutUsedQuantityItemQuantityTable::getOverAllItemQuantitiesFilterByResourceItemIdByProject($project, [$invoiceItem->resource_item_id]);

            if ( isset($newDOQuantities[$invoiceItem->resource_item_id]) )
            {
                $doQuantity = $newDOQuantities[$invoiceItem->resource_item_id];
            }

            if ( isset($newSOQuantities[$invoiceItem->resource_item_id]) )
            {
                $soQuantity = $newSOQuantities[$invoiceItem->resource_item_id];
            }

            $availableQuantity = $doQuantity - $soQuantity;//latest available qty after qty value saved

            $items[] = array(
                'id'                          => $invoiceItem->resource_item_id,
                'stock_used_quantity_item_id' => $invoiceItem->id,
                'quantity'                    => number_format($invoiceItem->quantity, 2, '.', ''),
                'available_quantity'          => number_format($availableQuantity, 2, '.', '')
            );
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeDeleteStockOutQuantityUsedItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $usedQuantity = Doctrine_Core::getTable('StockOutUsedQuantity')->find($request->getParameter('stockOutUsedQuantityId')) and
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
        );

        $errorMsg = array();

        try
        {
            StockOutUsedQuantityItemQuantityTable::deleteLikeResourceLibraryTree($usedQuantity, $resourceItem);
            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeGetStockOutFormInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
        );

        $stockOut = Doctrine_Core::getTable('StockOutUsedQuantity')->find($request->getParameter('stockOutUsedQuantityId'));
        $stockOut = $stockOut ? : new StockOutUsedQuantity();

        $form   = new StockOutUsedQuantityForm($stockOut);
        $object = $form->getObject();

        $data['form'] = array(
            'stock_out_used_quantity[project_structure_id]' => $project->id,
            'stock_out_used_quantity[running_number]'       => $object->isNew() ? $object->generateStockOutNo($project) : $object->running_number,
            'stock_out_used_quantity[stock_out_date]'       => $object->stock_out_date ? date('Y-m-d', strtotime($object->stock_out_date)) : date('Y-m-d'),
            'stock_out_used_quantity[_csrf_token]'          => $form->getCSRFToken(),
        );

        return $this->renderJson($data);
    }

    public function executeSaveStockOutFormInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('POST') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
        );

        $stockOut = Doctrine_Core::getTable('StockOutUsedQuantity')->find($request->getParameter('stockOutUsedQuantityId'));
        $stockOut = $stockOut ? : new StockOutUsedQuantity();

        $form = new StockOutUsedQuantityForm($stockOut);

        if ( $this->isFormValid($request, $form) )
        {
            $stockOut = $form->save();

            $id      = $stockOut->getId();
            $success = true;
            $errors  = array();
        }
        else
        {
            $id      = $request->getPostParameter('stockOutUsedQuantityId');
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeDeleteStockOutUsedQuantity(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('POST') and
            $stockOut = Doctrine_Core::getTable('StockOutUsedQuantity')->find($request->getParameter('stockOutUsedQuantityId'))
        );

        $errorMsg = array();

        try
        {
            $stockOut->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeGetCopyItemListingsWithDeliveryOrder(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $stockOutUsedQuantity = Doctrine_Core::getTable('StockOutUsedQuantity')->find($request->getParameter('stockOutUsedQuantityId')) and
            $resourceTrade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('resourceTradeId'))
        );

        $items = ResourceItemTable::getImportRecordsWithDeliveryOrderQuantities($stockOutUsedQuantity, $resourceTrade);

        $items[] = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'total_cost'  => 0,
            'do_quantity' => 0,
            'uom_id'      => - 1,
            'uom_symbol'  => null,
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items,
        ));
    }

}