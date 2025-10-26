<?php

/**
 * resourceLibrary actions.
 *
 * @package    buildspace
 * @subpackage resourceLibrary
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class resourceLibraryActions extends BaseActions {

    public function executeResourceList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $records = DoctrineQuery::create()->select('r.id, r.name')
            ->from('Resource r')
            ->addOrderBy('r.id ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $records as $key => $record )
        {
            $records[$key]['can_be_deleted'] = ResourceTable::linkToSoR($record['id']) ? false : true;
            $records[$key]['_csrf_token']    = $form->getCSRFToken();

            unset( $record );
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $records
        ));
    }

    public function executeResourceAdd(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $resource = new Resource();
        $con      = $resource->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $count = DoctrineQuery::create()->select('r.id')
                ->from('Resource r')
                ->where('r.name ILIKE ?', 'New Resource%')
                ->addOrderBy('r.id ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->count();

            $resource->name = 'New Resource ' . ( $count + 1 );

            $resource->save($con);

            $con->commit();

            $success = true;

            $form = new BaseForm();

            $item = array(
                'id'             => $resource->id,
                'name'           => $resource->name,
                'can_be_deleted' => true,
                '_csrf_token'    => $form->getCSRFToken()
            );

            $errorMsg = null;
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $item     = array();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $item ));
    }

    public function executeResourceUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('id')));

        $form = new ResourceForm($resource);

        $fieldName  = $request->getParameter('attr');
        $fieldValue = $request->getParameter('val');

        $request->setParameter($form->getName(), array(
            $fieldName                => $fieldValue,
            $form::getCSRFFieldName() => $form->getCSRFToken()
        ));

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

    public function executeResourceDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('id'))
        );

        $items    = array();
        $errorMsg = null;
        try
        {
            $resource->delete();
            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }
    /**** resource actions end ****/

    /**** trade actions ****/
    public function executeGetTradeList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('id')));

        $records = DoctrineQuery::create()->select('t.id, t.description, t.updated_at')
            ->from('ResourceTrade t')
            ->andWhere('t.resource_id = ?', $resource->id)
            ->addOrderBy('t.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $records as $key => $record )
        {
            $records[$key]['relation_id'] = $resource->id;
            $records[$key]['updated_at']  = date('d/m/Y H:i', strtotime($record['updated_at']));
            $records[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $record );
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'relation_id' => $resource->id,
            'updated_at'  => '-',
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeTradeUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('id')));

        $rowData = array();
        $con     = $trade->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $trade->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $trade->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $rowData = array(
                $fieldName   => $trade->$fieldName,
                'updated_at' => date('d/m/Y H:i', strtotime($trade->updated_at))
            );
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeTradeAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $trade = new ResourceTrade();
        $con   = $trade->getTable()->getConnection();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $prevTrade = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('prev_item_id')) : null;

            $priority   = $prevTrade ? $prevTrade->priority + 1 : 0;
            $resourceId = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');
                $trade->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }
        }
        else
        {
            $this->forward404Unless($nextTrade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('before_id')));

            $priority   = $nextTrade->priority;
            $resourceId = $nextTrade->resource_id;
        }

        $items = array();
        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('ResourceTrade')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('resource_id = ?', $resourceId)
                ->execute();

            $trade->resource_id = $resourceId;
            $trade->priority    = $priority;

            $trade->save();

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item = array();

            $form = new BaseForm();

            $item['id']          = $trade->id;
            $item['description'] = $trade->description;
            $item['relation_id'] = $resourceId;
            $item['updated_at']  = date('d/m/Y H:i', strtotime($trade->updated_at));
            $item['_csrf_token'] = $form->getCSRFToken();

            array_push($items, $item);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'relation_id' => $resourceId,
                    'updated_at'  => '-',
                    '_csrf_token' => $form->getCSRFToken()
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

    public function executeTradeLinkCheckBeforeDelete(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('get') and
            $trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('id'))
        );

        $linkedItems = false;

        $affectedLinkedSOR = $trade->checkRelatedScheduleOfRateBuildUpRates();

        if ( $affectedLinkedSOR > 0 )
        {
            $linkedItems = true;
        }

        $data = array( 'success' => true, 'linkedItems' => $linkedItems );

        return $this->renderJson($data);
    }

    public function executeTradeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        try
        {
            $item['id'] = $trade->id;
            $trade->delete();
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

    public function executeTradePaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetTrade = Doctrine_Core::getTable('ResourceTrade')->find(intval($request->getParameter('target_id')));
        if ( !$targetTrade )
        {
            $this->forward404Unless($targetTrade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetTrade->id == $trade->id )
        {
            $errorMsg = "cannot move item into itself";
            $results  = array( 'success' => false, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() );

            return $this->renderJson($results);
        }

        switch ($request->getParameter('type'))
        {
            case 'cut':
                try
                {
                    $trade->moveTo($targetTrade->priority, $lastPosition);

                    $data['id']         = $trade->id;
                    $data['updated_at'] = date('d/m/Y H:i', strtotime($trade->updated_at));

                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            case 'copy':
                try
                {
                    $newTrade = $trade->copyTo($targetTrade, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newTrade->id;
                    $data['description'] = $newTrade->description;
                    $data['relation_id'] = $newTrade->resource_id;
                    $data['updated_at']  = date('d/m/Y H:i', strtotime($newTrade->updated_at));
                    $data['_csrf_token'] = $form->getCSRFToken();

                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            default:
                throw new Exception('invalid paste operation');
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() ));
    }
    /**** trade actions end ****/

    /**** item actions ****/
    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('id'))
        );

        $pdo                       = $trade->getTable()->getConnection()->getDbh();
        $formulatedColumns         = array();
        $form                      = new BaseForm();
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ResourceItem');

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, uom.id as uom_id, i.lft, i.level,
        i.resource_trade_id, i.level, i.updated_at, uom.symbol AS uom_symbol, risr.id as resource_item_selected_rate_id
        FROM " . ResourceItemTable::getInstance()->getTableName() . " i
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
        LEFT JOIN " . ResourceItemSelectedRateTable::getInstance()->getTableName() . " risr ON i.id = risr.resource_item_id
        WHERE i.resource_trade_id = " . $trade->id . " AND i.deleted_at IS NULL ORDER BY i.priority, i.lft, i.level ASC");

        $stmt->execute();
        $resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value
        FROM " . ResourceItemFormulatedColumnTable::getInstance()->getTableName() . " ifc JOIN
        " . ResourceItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
        WHERE i.resource_trade_id = " . $trade->id . " AND ifc.deleted_at IS NULL
        AND i.deleted_at IS NULL AND ifc.final_value <> 0");

        $stmt->execute();
        $itemFormulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $itemFormulatedColumns as $itemFormulatedColumn )
        {
            if ( !array_key_exists($itemFormulatedColumn['relation_id'], $formulatedColumns) )
            {
                $formulatedColumns[$itemFormulatedColumn['relation_id']] = array();
            }

            array_push($formulatedColumns[$itemFormulatedColumn['relation_id']], $itemFormulatedColumn);

            unset( $itemFormulatedColumn );
        }

        unset( $itemFormulatedColumns );

        foreach ( $resourceItems as $key => $resourceItem )
        {
            $resourceItems[$key]['type']                                             = (string) $resourceItem['type'];
            $resourceItems[$key]['uom_id']                                           = $resourceItem['uom_id'] > 0 ? (string) $resourceItem['uom_id'] : '-1';
            $resourceItems[$key]['uom_symbol']                                       = $resourceItem['uom_id'] > 0 ? $resourceItem['uom_symbol'] : '';
            $resourceItems[$key]['relation_id']                                      = $trade->id;
            $resourceItems[$key]['updated_at']                                       = date('d/m/Y H:i', strtotime($resourceItem['updated_at']));
            $resourceItems[$key]['_csrf_token']                                      = $form->getCSRFToken();
            $resourceItems[$key][BillItem::FORMULATED_COLUMN_RATE . '-has_build_up'] = ( $resourceItem['resource_item_selected_rate_id'] ) ? true : false;

            foreach ( $formulatedColumnConstants as $constant )
            {
                $resourceItems[$key][$constant . '-final_value']        = 0;
                $resourceItems[$key][$constant . '-value']              = '';
                $resourceItems[$key][$constant . '-has_cell_reference'] = false;
                $resourceItems[$key][$constant . '-has_formula']        = false;
            }

            if ( array_key_exists($resourceItem['id'], $formulatedColumns) )
            {
                foreach ( $formulatedColumns[$resourceItem['id']] as $formulatedColumn )
                {
                    $columnName                                               = $formulatedColumn['column_name'];
                    $resourceItems[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                    $resourceItems[$key][$columnName . '-value']              = $formulatedColumn['value'];
                    $resourceItems[$key][$columnName . '-has_cell_reference'] = false;
                    $resourceItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }
            }

            unset( $formulatedColumns[$resourceItem['id']], $resourceItem );
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

        foreach ( $formulatedColumnConstants as $constant )
        {
            $defaultLastRow[$constant . '-final_value']        = "";
            $defaultLastRow[$constant . '-value']              = "";
            $defaultLastRow[$constant . '-has_cell_reference'] = false;
            $defaultLastRow[$constant . '-has_formula']        = false;
        }

        array_push($resourceItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $resourceItems
        ));
    }

    public function executeGetItemSupplierRateList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
        );

        // get supplier's rate if available
        $resourceItemRates = RFQItemRateTable::getSupplierRatesByResourceItem($resourceItem);

        foreach ( $resourceItemRates as $key => $resourceItemRate )
        {
            $resourceItemRates[$key]['rate_last_updated_at'] = date('d/m/Y H:i', strtotime($resourceItemRate['rate_last_updated_at']));
        }

        if ( count($resourceItemRates) == 0 )
        {
            $resourceItemRates[] = array(
                'id'                                       => Constants::GRID_LAST_ROW,
                'request_for_quotation_item_id'            => null,
                'request_for_quotation_supplier_id'        => null,
                'rate'                                     => null,
                'rate_last_updated_at'                     => '-',
                'company_id'                               => null,
                'company_name'                             => null,
                'request_for_quotation_rate_item_id'       => null,
                'request_for_quotation_rate_item_quantity' => null,
                'request_for_quotation_id'                 => null,
                'project_structure_id'                     => null,
                'project_title'                            => null,
                'country'                                  => null,
                'state'                                    => null,
                'remarks'                                  => null,
            );
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $resourceItemRates,
        ));
    }

    public function executeItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('id')));

        $rowData                   = array();
        $affectedNodes             = array();
        $isFormulatedColumn        = false;
        $formulatedColumnTable     = Doctrine_Core::getTable('ResourceItemFormulatedColumn');
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ResourceItem');

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            if ( in_array($fieldName, $formulatedColumnConstants) )
            {
                $formulatedColumn = $formulatedColumnTable->getByRelationIdAndColumnName($item->id, $fieldName);

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->save($con);

                $formulatedColumn->refresh();

                $formulatedColumn->updateLinkedSorValues($con);

                $selectionOfRate = Doctrine_Core::getTable('ResourceItemSelectedRate')->findOneBy('resource_item_id', array( $item->id ));

                // unattach selection of rate record if available
                if ( $fieldName == BillItem::FORMULATED_COLUMN_RATE AND $selectionOfRate )
                {
                    ResourceSelectionOfRateRfqItemRateTable::deleteByResourceItemSelectionOfRateId($selectionOfRate->id);

                    $selectionOfRate->delete();

                    $removedSelectionOfRate = true;
                }

                $isFormulatedColumn = true;
            }
            else
            {
                $fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;

                if ( $fieldName == 'type' )
                {
                    $returnVal = $item->{'update' . sfInflector::camelize($fieldName)}($fieldValue);
                }
                else
                {
                    $returnVal = $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                }

                //changing type will probably affects any related node to item's rate
                if ( is_array($returnVal) )
                {
                    $affectedNodes = $returnVal;
                }

                $item->save($con);
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item->refresh();

            if ( $isFormulatedColumn )
            {
                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                foreach ( $referencedNodes as $k => $referencedNode )
                {
                    if ( $node = $formulatedColumnTable->find($referencedNode['node_from']) )
                    {
                        array_push($affectedNodes, array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value
                        ));
                    }

                    unset( $referencedNodes[$k], $referencedNode );
                }

                $rowData[$fieldName . "-final_value"]        = $formulatedColumn->final_value;
                $rowData[$fieldName . "-value"]              = $formulatedColumn->value;
                $rowData[$fieldName . '-has_cell_reference'] = false;
                $rowData[$fieldName . '-has_formula']        = $formulatedColumn->hasFormula();

                if ( isset ( $removedSelectionOfRate ) )
                {
                    $rowData[BillItem::FORMULATED_COLUMN_RATE . '-has_build_up'] = false;
                }
            }
            else
            {
                $rowData[$fieldName] = $item->{$fieldName};

                foreach ( $formulatedColumnConstants as $constant )
                {
                    $formulatedColumn                           = $item->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                    $finalValue                                 = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                    $rowData[$constant . '-final_value']        = $finalValue;
                    $rowData[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                    $rowData[$constant . '-has_cell_reference'] = false;
                    $rowData[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }
            }

            // update timestamp for item and trade level in resources library
            $item->save();

            $item->refresh();

            $rowData['affected_nodes'] = $affectedNodes;
            $rowData['type']           = (string) $item->type;
            $rowData['uom_id']         = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol']     = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            $rowData['updated_at']     = date('d/m/Y H:i', strtotime($item->updated_at));
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $items                     = array();
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ResourceItem');
        $isFormulatedColumn        = false;
        $con                       = Doctrine_Core::getTable('ResourceItem')->getConnection();

        try
        {
            $con->beginTransaction();

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('prev_item_id')) : null;
                $tradeId      = $request->getParameter('relation_id');

                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                $item = ResourceItemTable::createItemFromLastRow($previousItem, $tradeId, $fieldName, $fieldValue);

                if ( in_array($fieldName, $formulatedColumnConstants) )
                {
                    $isFormulatedColumn = true;
                }
            }
            else
            {
                $this->forward404Unless($nextItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('before_id')));
                $tradeId = $nextItem->resource_trade_id;

                $item = ResourceItemTable::createItem($nextItem, $tradeId);
            }

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new ResourceItemFormulatedColumn();
                $formulatedColumn->relation_id = $item->id;
                $formulatedColumn->column_name = $fieldName;

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->save();
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $data = array();

            $form = new BaseForm();

            $item->refresh();

            $data['id']          = $item->id;
            $data['description'] = $item->description;
            $data['type']        = (string) $item->type;
            $data['uom_id']      = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $data['uom_symbol']  = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            $data['relation_id'] = $tradeId;
            $data['updated_at']  = date('d/m/Y H:i', strtotime($item->updated_at));
            $data['level']       = $item->level;
            $data['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnConstants as $constant )
            {
                $formulatedColumn                        = $item->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $data[$constant . '-final_value']        = $finalValue;
                $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $data[$constant . '-has_cell_reference'] = false;
                $data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'type'        => (string) ResourceItem::TYPE_WORK_ITEM,
                    'uom_symbol'  => '',
                    'uom_id'      => '-1',
                    'relation_id' => $tradeId,
                    'updated_at'  => '-',
                    'level'       => 0,
                    '_csrf_token' => $form->getCSRFToken()
                );
                foreach ( $formulatedColumnConstants as $constant )
                {
                    $defaultLastRow[$constant . '-final_value']        = "";
                    $defaultLastRow[$constant . '-value']              = "";
                    $defaultLastRow[$constant . '-has_cell_reference'] = false;
                    $defaultLastRow[$constant . '-has_formula']        = false;
                }
                array_push($items, $defaultLastRow);
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

    public function executeItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('id')));

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetItem = Doctrine_Core::getTable('ResourceItem')->find(intval($request->getParameter('target_id')));
        if ( !$targetItem )
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetItem->root_id == $item->root_id and $targetItem->lft >= $item->lft and $targetItem->rgt <= $item->rgt )
        {
            $errorMsg = "cannot move item into itself";
            $results  = array( 'success' => false, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() );

            return $this->renderJson($results);
        }

        switch ($request->getParameter('type'))
        {
            case 'cut':
                try
                {
                    $item->moveTo($targetItem, $lastPosition);

                    $children = DoctrineQuery::create()->select('i.id, i.level, i.updated_at')
                        ->from('ResourceItem i')
                        ->andWhere('i.root_id = ?', $item->root_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                        ->addOrderBy('i.lft')
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

                    foreach ( $children as $key => $child )
                    {
                        $children[$key]['updated_at'] = date('d/m/Y H:i', strtotime($child['updated_at']));
                        unset( $child );
                    }

                    $data['id']         = $item->id;
                    $data['level']      = $item->level;
                    $data['updated_at'] = date('d/m/Y H:i', strtotime($item->updated_at));

                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            case 'copy':
                try
                {
                    $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ResourceItem');

                    $newItem = $item->copyTo($targetItem, $lastPosition);

                    $form = new BaseForm();

                    $children = DoctrineQuery::create()->select('i.id, i.description, i.type, i.uom_id, i.resource_trade_id, i.level, i.updated_at, uom.symbol, ifc.column_name, ifc.value, ifc.final_value')
                        ->from('ResourceItem i')
                        ->leftJoin('i.FormulatedColumns ifc')
                        ->leftJoin('i.UnitOfMeasurement uom')
                        ->andWhere('i.root_id = ?', $newItem->root_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $newItem->lft, $newItem->rgt ))
                        ->addOrderBy('i.lft')
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

                    foreach ( $children as $key => $child )
                    {
                        $children[$key]['type']        = (string) $child['type'];
                        $children[$key]['uom_id']      = $child['uom_id'] > 0 ? (string) $child['uom_id'] : '-1';
                        $children[$key]['uom_symbol']  = $child['uom_id'] > 0 ? $child['UnitOfMeasurement']['symbol'] : '';
                        $children[$key]['relation_id'] = $child['resource_trade_id'];
                        $children[$key]['updated_at']  = date('d/m/Y H:i', strtotime($child['updated_at']));
                        $children[$key]['_csrf_token'] = $form->getCSRFToken();

                        foreach ( $formulatedColumnConstants as $constant )
                        {
                            $children[$key][$constant . '-final_value']        = 0;
                            $children[$key][$constant . '-value']              = '';
                            $children[$key][$constant . '-has_cell_reference'] = false;
                            $children[$key][$constant . '-has_formula']        = false;
                        }

                        foreach ( $child['FormulatedColumns'] as $formulatedColumn )
                        {
                            $columnName                                          = $formulatedColumn['column_name'];
                            $children[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                            $children[$key][$columnName . '-value']              = $formulatedColumn['value'];
                            $children[$key][$columnName . '-has_cell_reference'] = false;
                            $children[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                        }

                        unset( $children[$key]['FormulatedColumns'], $children[$key]['UnitOfMeasurement'] );
                    }

                    $data['id']          = $newItem->id;
                    $data['description'] = $newItem->description;
                    $data['type']        = (string) $newItem->type;
                    $data['uom_id']      = $newItem->uom_id > 0 ? (string) $newItem->uom_id : '-1';
                    $data['uom_symbol']  = $newItem->uom_id > 0 ? $newItem->UnitOfMeasurement->symbol : '';
                    $data['relation_id'] = $newItem->resource_trade_id;
                    $data['updated_at']  = date('d/m/Y H:i', strtotime($newItem->updated_at));
                    $data['level']       = $newItem->level;
                    $data['_csrf_token'] = $form->getCSRFToken();

                    foreach ( $formulatedColumnConstants as $constant )
                    {
                        $formulatedColumn                        = $newItem->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                        $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                        $data[$constant . '-final_value']        = $finalValue;
                        $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                        $data[$constant . '-has_cell_reference'] = false;
                        $data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                    }

                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            default:
                throw new Exception('invalid paste operation');
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => $children ));
    }

    public function executeItemLinkCheckBeforeDelete(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('get') and
            $item = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('id'))
        );

        $sorLinkedItems = false;
        $hasRowLinking  = false;

        $affectedLinkedSOR = $item->checkRelatedScheduleOfRateBuildUpRates();

        if ( $affectedLinkedSOR > 0 )
        {
            $sorLinkedItems = true;
        }

        if ( !$sorLinkedItems )
        {
            $hasRowLinking = $item->getItemRowLinkingStatus();
        }

        $success = true;

        return $this->renderJson(array( 'success' => $success, 'sorLinkedItems' => $sorLinkedItems, 'hasRowLinking' => $hasRowLinking ));
    }

    public function executeItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('id'))
        );

        $items    = array();
        $errorMsg = null;
        $con      = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $tradeLevel = $item->ResourceTrade;

            $items = DoctrineQuery::create()->select('i.id')
                ->from('ResourceItem i')
                ->andWhere('i.root_id = ?', $item->root_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $item->lft, $item->rgt ))
                ->addOrderBy('i.lft')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            $affectedNodes = $item->delete($con);

            // update timestamp for trade level in resources library
            $tradeLevel->updated_at = 'NOW()';
            $tradeLevel->save();

            $con->commit();

            $success = true;
        } catch (Exception $e)
        {
            $con->rollback();

            $errorMsg      = $e->getMessage();
            $affectedNodes = array();
            $success       = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => $affectedNodes ));
    }

    public function executeItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('id')));

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();
        try
        {
            if ( $item->indent() )
            {
                $data['id']         = $item->id;
                $data['level']      = $item->level;
                $data['updated_at'] = date('d/m/Y H:i', strtotime($item->updated_at));

                $children = DoctrineQuery::create()->select('i.id, i.level')
                    ->from('ResourceItem i')
                    ->andWhere('i.root_id = ?', $item->root_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                $success = true;
            }
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executeItemOutdent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('id')));

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();
        try
        {
            if ( $item->outdent() )
            {
                $data['id']         = $item->id;
                $data['level']      = $item->level;
                $data['updated_at'] = date('d/m/Y H:i', strtotime($item->updated_at));

                $children = DoctrineQuery::create()->select('i.id, i.level')
                    ->from('ResourceItem i')
                    ->andWhere('i.root_id = ?', $item->root_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                $success = true;
            }
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    /**** item actions end ****/

    public function executeGetBuildUpRatesToolTipInformation(sfWebRequest $request)
    {
        $type = $request->getParameter('type');

        switch ($type)
        {
            case 'sor':
                $className = 'ScheduleOfRateBuildUpRateItem';
                break;

            case 'bqLibrary':
                $className = 'BQLibraryBuildUpRateItem';
                break;

            case 'bill':
                $className = 'BillBuildUpRateItem';
                break;

            default:
                $this->forward404();
        }

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable($className)->find($request->getParameter('id')));

        $success      = false;
        $resourceItem = $item->ResourceItemLibrary;
        $ancestors    = $resourceItem->getAncestors(true);

        if ( !$ancestors )
        {
            $ancestors = array();
        }

        // get trade level as well
        $tradeLevel = array( $resourceItem->getResourceTrade()->toArray() );

        // merge trade level and item level
        $ancestors = array_merge($tradeLevel, $ancestors);

        if ( $ancestors )
        {
            $success = true;
        }

        return $this->renderJson(array(
            'success' => $success,
            'items'   => $ancestors,
        ));
    }

    public function executePreviewImportedFile(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $success        = null;
        $errorMsg       = null;

        foreach ( $request->getFiles() as $file )
        {
            if ( is_readable($file['tmp_name']) )
            {
                // Later to do some checking Here FileType ETC.
                //generate new Temporary Name
                $newName    = date('dmY_H_i_s');
                $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile = $tempUploadPath . $newName . '.' . $ext;

                //Move Uploaded Files to temp folder
                move_uploaded_file($file['tmp_name'], $pathToFile);

            }
        }

        try
        {
            $sfImport = new sfBuildspaceLibraryExcelParser($newName, $ext, $tempUploadPath, true, false);//startRead(${filename}, ${uploadPath}, ${extension}, ${generateXML})

            $data = $sfImport->processPreviewData();

            $returnData = array(
                'fileName'    => $sfImport->filename,
                'extension'   => $sfImport->extension,
                'excelType'   => $sfImport->excelType,
                'preview'     => true,
                'previewData' => $data,
                'colData'     => $sfImport->colSlugArray
            );

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = ( $sfImport->error ) ? false : $success;
        $returnData['errorMsg'] = ( $sfImport->error ) ? $sfImport->errorMsg : $errorMsg;

        return $this->renderJson($returnData);
    }

    public function executeDeleteTempFile(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $uploadPath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp';
        $filename   = $request->getParameter('filename');
        $extension  = $request->getParameter('extension');
        $pathToFile = $uploadPath . DIRECTORY_SEPARATOR . $filename . '.' . $extension;
        $errorMsg   = null;

        try
        {
            if ( is_readable($pathToFile) )
            {
                unlink($pathToFile);
            }

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'errorMsg' => $errorMsg, 'success' => $success ));
    }

    public function executeImportBuildsoftExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

        $errorMsg = null;

        foreach ( $request->getFiles() as $file )
        {
            if ( is_readable($file['tmp_name']) )
            {
                // Later to do some checking Here FileType ETC.
                //generate new Temporary Name
                $newName    = date('dmY_H_i_s');
                $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile = $tempUploadPath . $newName . '.' . $ext;

                //Move Uploaded Files to temp folder
                move_uploaded_file($file['tmp_name'], $pathToFile);

            }
        }

        try
        {
            $sfImport = new sfLibraryImportExcelBuildsoft($newName, $ext, $tempUploadPath, true);

            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $returnData = array(
                'filename'   => $sfImport->sfExportXML->filename,
                'uploadPath' => $sfImport->sfExportXML->uploadPath,
                'extension'  => $sfImport->sfExportXML->extension,
                'excelType'  => $sfImport->excelType,
                'Trades'     => array()
            );

            foreach ( $data as $kTrade => $trade )
            {
                array_push($returnData['Trades'], array(
                    'id'          => $trade['id'],
                    'description' => $trade['description'],
                    'count'       => $sfImport->tradeItemCount[$trade['id']],
                    'error'       => $sfImport->tradeErrorCount[$trade['id']]
                ));

                $items                                    = $trade['_child'];
                $returnData['TradesToItem'][$trade['id']] = array();

                foreach ( $items as $kItem => $item )
                {
                    array_push($returnData['TradesToItem'][$trade['id']], $item);
                }
            }

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = ( $sfImport->error ) ? false : $success;
        $returnData['errorMsg'] = ( $sfImport->error ) ? $sfImport->errorMsg : $errorMsg;

        return $this->renderJson($returnData);
    }

    public function executeImportPricelist(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $errorMsg       = null;

        foreach ( $request->getFiles() as $file )
        {
            if ( is_readable($file['tmp_name']) )
            {
                // Later to do some checking Here FileType ETC.
                //generate new Temporary Name
                $newName    = date('dmY_H_i_s');
                $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile = $tempUploadPath . $newName . '.' . $ext;

                //Move Uploaded Files to temp folder
                move_uploaded_file($file['tmp_name'], $pathToFile);

            }
        }

        try
        {
            $sfImport = new sfLibraryImportExcelPricelist($newName, $ext, $tempUploadPath, true);//startRead(${filename}, ${uploadPath}, ${extension}, ${generateXML})

            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $returnData = array(
                'filename'   => $sfImport->sfExportXML->filename,
                'uploadPath' => $sfImport->sfExportXML->uploadPath,
                'extension'  => $sfImport->sfExportXML->extension,
                'excelType'  => $sfImport->excelType,
                'Trades'     => array()
            );

            foreach ( $data as $kTrade => $trade )
            {
                array_push($returnData['Trades'], array(
                    'id'          => $trade['id'],
                    'description' => $trade['description'],
                    'count'       => $sfImport->tradeItemCount[$trade['id']],
                    'error'       => $sfImport->tradeErrorCount[$trade['id']]
                ));

                $items                                    = $trade['_child'];
                $returnData['TradesToItem'][$trade['id']] = array();

                foreach ( $items as $kItem => $item )
                {
                    array_push($returnData['TradesToItem'][$trade['id']], $item);
                }
            }

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = ( $sfImport->error ) ? false : $success;
        $returnData['errorMsg'] = ( $sfImport->error ) ? $sfImport->errorMsg : $errorMsg;

        return $this->renderJson($returnData);
    }

    public function executeImportExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('resource_id')) and
            ( $request->getParameter('filename') )
        );

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $errorMsg       = null;
        $filename       = $request->getParameter('filename');
        $extension      = $request->getParameter('extension');

        try
        {
            $sfImport = new sfLibraryImportExcelNormal($filename, $extension, $tempUploadPath, true);//startRead(${filename}, ${uploadPath}, ${extension}, ${generateXML})

            //Set Col Item Value Based on Selection
            $sfImport->colItem            = $request->getParameter('colItem');
            $sfImport->colDescriptionFrom = $request->getParameter('colDescriptionFrom');
            $sfImport->colDescriptionTo   = $request->getParameter('colDescriptionTo');
            $sfImport->colUnit            = $request->getParameter('colUnit');
            $sfImport->colRate            = $request->getParameter('colRate');
            $sfImport->colQty             = $request->getParameter('colQty');
            $sfImport->colAmount          = $request->getParameter('colAmount');

            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $returnData = array(
                'filename'   => $sfImport->sfExportXML->filename,
                'uploadPath' => $sfImport->sfExportXML->uploadPath,
                'extension'  => $sfImport->sfExportXML->extension,
                'excelType'  => $sfImport->excelType,
                'Trades'     => array()
            );

            foreach ( $data as $kTrade => $trade )
            {
                if ( empty( $trade['_child'] ) )
                {
                    continue;
                }

                array_push($returnData['Trades'], array(
                    'id'          => $trade['id'],
                    'description' => $trade['description'],
                    'count'       => $sfImport->tradeItemCount[$trade['id']],
                    'error'       => $sfImport->tradeErrorCount[$trade['id']]
                ));

                $items                                    = $trade['_child'];
                $returnData['TradesToItem'][$trade['id']] = array();

                foreach ( $items as $kItem => $item )
                {
                    array_push($returnData['TradesToItem'][$trade['id']], $item);
                }
            }

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = ( $sfImport->error ) ? false : $success;
        $returnData['errorMsg'] = ( $sfImport->error ) ? $sfImport->errorMsg : $errorMsg;

        return $this->renderJson($returnData);
    }

    public function executeSaveImportedExcel(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('resource_id'))
        );

        set_time_limit(0);

        $errorMsg = null;

        //explode Element to imports
        $tradeIds = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));

        $withRate = ( $request->getParameter('with_rate') == 'true' ) ? true : false;

        //get XML Temporary File Information
        $filename   = $request->getParameter('filename');
        $uploadPath = $request->getParameter('uploadPath');

        //Initiate xmlParser
        $xmlParser = new sfBuildspaceXMLParser($filename, $uploadPath, null, true);

        //read xmlParser
        $xmlParser->read();

        //Get XML Processed Data
        $loadedXML     = $xmlParser->getProcessedData();
        $trades        = $loadedXML->TRADES;
        $resourceItems = $loadedXML->ITEMS;

        //get Last Priority for current Element
        $lastPriority = ResourceTradeTable::getMaxPriorityByResourceId($resource->id);

        //Get Current User Information
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');

        $importedTradeToTradeIds = null;
        $priority                = $lastPriority + 1;

        $con   = Doctrine_Manager::getInstance()->getCurrentConnection();
        $items = array();

        try
        {
            $con->beginTransaction();

            $stmt = new sfImportExcelStatementGenerator();

            $stmt->createInsert(
                ResourceTradeTable::getInstance()->getTableName(),
                array( 'description', 'resource_id', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
            );

            foreach ( $trades->children() as $importedTrade )
            {
                $tradeId = (int) $importedTrade->id;

                $description = html_entity_decode((string) $importedTrade->description);

                if ( in_array($tradeId, $tradeIds) )
                {

                    $stmt->addRecord(array( $description, $resource->id, $priority, 'NOW()', 'NOW()', $userId, $userId ), $tradeId);

                    array_push($items, array(
                        'id'          => $tradeId,
                        'description' => $description,
                        'relation_id' => $resource->id,
                        '_csrf_token' => '',
                        'updated_at'  => date('d/m/Y H:i')
                    ));

                    $priority ++;
                }

                unset( $importedTrade );
            }

            unset( $trades );

            $stmt->save();

            $importedTradeToTradeIds = $stmt->returningIds;

            //reassign tradeId
            foreach ( $items as $k => $item )
            {
                $items[$k]['id'] = $importedTradeToTradeIds[$item['id']];
            }

            $importedItemToItemIds     = array();
            $rootOriginalIdsToPriority = array();
            $originalItemsToSave       = array();
            $originalItemIdsToRootId   = array();
            $ratesToSave               = array();
            $currentPriority           = - 1;
            $currentElementId          = null;

            // will get existing unit first
            $unitGenerator = new ScheduleOfQuantityUnitGetter($con);

            $availableUnits = $unitGenerator->getAvailableUnitOfMeasurements();

            //Process Root Items
            foreach ( $resourceItems->children() as $importedItem )
            {
                $previousItem = null;
                $asRoot       = null;

                if ( in_array($importedItem->tradeId, $tradeIds) )
                {
                    $tradeId     = $importedTradeToTradeIds[(int) $importedItem->tradeId];
                    $description = html_entity_decode((string) $importedItem->description);

                    if ( !isset( $importedItem->new_symbol ) or strlen($importedItem->new_symbol) > 10)//any char more than 10 chars will be considered as non uom symbol
                    {
                        $uomId = ( (int) $importedItem->uom_id > 0 ) ? (int) $importedItem->uom_id : null;
                    }
                    else
                    {
                        if ( !isset( $availableUnits[strtolower($importedItem->new_symbol)] ) )
                        {
                            // we will insert the new uom symbol
                            $availableUnits = $unitGenerator->insertNewUnitOfMeasurementWithoutDimension($availableUnits, $importedItem->new_symbol);
                        }

                        $uomId = $availableUnits[strtolower($importedItem->new_symbol)];
                    }


                    $type           = (int) $importedItem->type;
                    $level          = (int) $importedItem->level;
                    $originalItemId = (int) $importedItem->id;

                    if ( (int) $importedItem->level == 0 )
                    {
                        if ( $tradeId != $currentElementId )
                        {
                            $currentPriority  = 0;
                            $currentElementId = $tradeId;
                        }
                        else
                        {
                            $currentPriority ++;
                        }
                        //Set As Root and set root Id to null
                        $asRoot                                     = true;
                        $rootId                                     = null;
                        $rootOriginalIdsToPriority[$originalItemId] = $priority = $currentPriority;
                    }
                    else
                    {
                        $rootId                                   = null;
                        $originalRootId                           = (int) $importedItem->root_id;
                        $originalItemIdsToRootId[$originalItemId] = $originalRootId;
                    }

                    $rate = ( $importedItem->{'rate-value'} && $importedItem->{'rate-value'} != '' ) ? number_format((float) $importedItem->{'rate-value'}, 2, '.', '') : null;

                    if ( $asRoot )
                    {
                        $stmt->createInsert(
                            ResourceItemTable::getInstance()->getTableName(),
                            array( 'resource_trade_id', 'description', 'type', 'uom_id', 'level', 'root_id', 'lft', 'rgt', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
                        );

                        $stmt->addRecord(array( $tradeId, $description, $type, $uomId, $level, $rootId, 1, 2, $priority, 'NOW()', 'NOW()', $userId, $userId ));

                        $stmt->save();

                        $returningId = $stmt->returningIds[0];

                        $stmt->setAsRoot(false, $returningId);

                        $importedItemToItemIds[(string) $importedItem->id] = $itemId = $returningId;
                    }
                    else
                    {
                        $originalItemsToSave[$originalItemId] = array( $tradeId, $description, $type, $uomId, $level, $originalRootId, 1, 2, $priority, 'NOW()', 'NOW()', $userId, $userId );
                    }

                    //Save Rate if Not Header && option withrate true
                    if ( $withRate && (int) $importedItem->type != ResourceItem::TYPE_HEADER )
                    {
                        $fieldName = ResourceItem::FORMULATED_COLUMN_RATE;

                        array_push($ratesToSave, array(
                            $originalItemId, $fieldName, $rate, $rate, 'NOW()', 'NOW()', $userId, $userId
                        ));
                    }
                }

                unset( $importedItem );
            }

            unset( $billItems );

            $stmt->createInsert(
                ResourceItemTable::getInstance()->getTableName(),
                array( 'resource_trade_id', 'description', 'type', 'uom_id', 'level', 'root_id', 'lft', 'rgt', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
            );

            $originalRootIdToItemIds = array();

            function checkRootId(&$originalItemIdsToRootId, $itemRootId)
            {
                if ( array_key_exists($itemRootId, $originalItemIdsToRootId) )
                {
                    $originalRootId = $originalItemIdsToRootId[$itemRootId];

                    return $originalRootId = checkRootId($originalItemIdsToRootId, $originalRootId);
                }
                else
                {
                    return $itemRootId;
                }
            }

            if ( count($originalItemsToSave) )
            {
                foreach ( $originalItemsToSave as $originalItemId => $item )
                {
                    $itemRootId = $item[5];

                    $originalRootIdToItemIds[$itemRootId][] = $originalItemId;

                    $originalRootId = checkRootId($originalItemIdsToRootId, $itemRootId);

                    $rootId   = $importedItemToItemIds[$originalRootId];
                    $priority = $rootOriginalIdsToPriority[$originalRootId];

                    $item[5] = $rootId;
                    $item[8] = $priority;

                    $stmt->addRecord($item, $originalItemId);

                    unset( $item );
                }

                $stmt->save();

                $importedItemToItemIds = $importedItemToItemIds + $stmt->returningIds;
            }

            $rootIdToItemIds = array();

            foreach ( $originalRootIdToItemIds as $rootId => $itemIds )
            {
                $newRootId = $importedItemToItemIds[$rootId];

                foreach ( $itemIds as $key => $itemId )
                {
                    $rootIdToItemIds[$newRootId][$key] = $importedItemToItemIds[$itemId];
                }

                unset( $itemIds );
            }

            unset( $originalRootIdToItemIds );

            /* Experimental */
            //Rebuilding Back After Tree Insert
            $stmt->rebuildItemTreeStructureByResourceTradeIds('ResourceItem', ResourceItemTable::getInstance()->getTableName(), $importedTradeToTradeIds, $rootIdToItemIds);


            if ( count($ratesToSave) )
            {
                //Save Qty & Rates
                $stmt->createInsert(
                    ResourceItemFormulatedColumnTable::getInstance()->getTableName(),
                    array( 'relation_id', 'column_name', 'value', 'final_value', 'created_at', 'updated_at', 'created_by', 'updated_by' )
                );

                foreach ( $ratesToSave as $rate )
                {
                    $rate[0] = $importedItemToItemIds[$rate[0]];

                    $stmt->addRecord($rate);

                    unset( $rate );
                }

                $stmt->save();
            }

            unset( $ratesToSave );

            $con->commit();

            $success = true;
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        //end xmlParser
        $xmlParser->endReader();

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'items'    => $items
        ));
    }


    public function executeGetUnits(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $options = array();
        $values  = array();

        array_push($values, '-1');
        array_push($options, '---');

        $records = DoctrineQuery::create()->select('u.id, u.symbol')
            ->from('UnitOfMeasurement u')
            ->where('u.display IS TRUE')
            ->addOrderBy('u.symbol ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach ( $records as $record )
        {
            array_push($values, (string) $record['id']); //damn, dojo store handles ids in string format
            array_push($options, $record['symbol']);
        }

        return $this->renderJson(array(
            'values'  => $values,
            'options' => $options
        ));
    }

    public function executeGetSelectedRatesFromRFQ(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $selection          = Doctrine_Core::getTable('ResourceItemSelectedRate')->findOneBy('resource_item_id', array( $request->getParameter('resourceItemId') ));
        $selection          = ( $selection ) ? $selection : new ResourceItemSelectedRate();
        $form               = new ResourceItemSelectedRateForm($selection);
        $previousRFQRatesId = array();

        if ( !$selection->isNew() )
        {
            $resourceItem         = $selection->getResourceItem();
            $rateFormulatedColumn = $resourceItem->getFormulatedColumnByName(ResourceItem::FORMULATED_COLUMN_RATE);
            $previousRFQRate      = $rateFormulatedColumn->final_value;

            // get previous selected item's rate
            $selectedRFQItemRates = Doctrine_Query::create()
                ->select('p.request_for_quotation_item_rate_id')
                ->from('ResourceSelectionOfRateRfqItemRate p')
                ->where('p.resource_item_selection_of_rate_id = ?', $selection->id)
                ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            foreach ( $selectedRFQItemRates as $selectedRFQItemRate )
            {
                $id = $selectedRFQItemRate['request_for_quotation_item_rate_id'];

                $previousRFQRatesId[$id] = $id;
            }
        }
        else
        {
            $previousRFQRate = 0;
        }

        // default sorting order to average if no record exist yet
        $sortingType = ( !$selection->isNew() ) ? $form->getObject()->sorting_type : 1;

        $data['formInformation'] = array(
            'sorting_type'       => $sortingType,
            'sorting_type_text'  => ResourceItemSelectedRateTable::getSortingTypeText($sortingType),
            'previousRFQRatesId' => $previousRFQRatesId,
            'previousRFQRate'    => number_format($previousRFQRate, 2),
            '_csrf_token'        => $form->getCSRFToken()
        );

        return $this->renderJson($data);
    }

    public function executeUpdateSelectedRatesFromRFQ(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post')
        );

        $selection = Doctrine_Core::getTable('ResourceItemSelectedRate')->findOneBy('resource_item_id', array( $request->getPostParameter('resourceItemId') ));
        $selection = ( $selection ) ? $selection : new ResourceItemSelectedRate();
        $form      = new ResourceItemSelectedRateForm($selection);

        if ( $this->isFormValid($request, $form) )
        {
            $selection->refresh(true);

            $resource        = $form->save();
            $id              = $resource->getId();
            $rateDisplayType = ResourceItemSelectedRateTable::getSortingTypeText($form->getObject()->sorting_type);
            $errors          = null;
            $success         = true;

            $resourceItem         = $selection->getResourceItem();
            $rateFormulatedColumn = $resourceItem->getFormulatedColumnByName(ResourceItem::FORMULATED_COLUMN_RATE);
            $newRate              = $rateFormulatedColumn->final_value;
        }
        else
        {
            $id              = $request->getPostParameter('selectionId');
            $errors          = $form->getErrors();
            $rateDisplayType = null;
            $success         = false;
            $newRate         = 0;
        }

        return $this->renderJson(array( 'success' => $success, 'id' => $id, 'rateDisplayType' => $rateDisplayType, 'newRate' => number_format($newRate, 2), 'errors' => $errors ));
    }

}