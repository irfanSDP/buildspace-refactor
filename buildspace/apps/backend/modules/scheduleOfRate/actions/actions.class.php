<?php

/**
 * scheduleOfrate actions.
 *
 * @package    buildspace
 * @subpackage scheduleOfrate
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class scheduleOfRateActions extends BaseActions {

    public function executeScheduleOfRateList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $records = DoctrineQuery::create()->select('r.id, r.name')
            ->from('ScheduleOfRate r')
            ->addOrderBy('r.id ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $records as $key => $record )
        {
            $records[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $records
        ));
    }

    public function executeScheduleOfRateAdd(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $scheduleOfRate = new ScheduleOfRate();
        $con            = $scheduleOfRate->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $count = DoctrineQuery::create()->select('r.id')
                ->from('ScheduleOfRate r')
                ->where('r.name ILIKE ?', 'New SOR%')
                ->addOrderBy('r.id ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->count();

            $scheduleOfRate->name = 'New SOR ' . ( $count + 1 );

            $scheduleOfRate->save($con);

            $con->commit();

            $success = true;

            $form = new BaseForm();

            $item = array(
                'id'          => $scheduleOfRate->id,
                'name'        => $scheduleOfRate->name,
                '_csrf_token' => $form->getCSRFToken()
            );

            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $item     = array();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $item ));
    }

    public function executeScheduleOfRateUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('id')));

        $form = new ScheduleOfRateForm($scheduleOfRate);

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

    public function executeScheduleOfRateDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('id'))
        );

        $items    = array();
        $errorMsg = null;
        try
        {
            $scheduleOfRate->delete();
            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }
    /**** schedule of rate actions end ****/

    /**** trade actions ****/
    public function executeGetTradeList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('id')));

        $records = DoctrineQuery::create()->select('t.id, t.description, t.recalculate_resources_library_status, t.updated_at')
            ->from('ScheduleOfRateTrade t')
            ->andWhere('t.schedule_of_rate_id = ?', $scheduleOfRate->id)
            ->addOrderBy('t.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $records as $key => $record )
        {
            $records[$key]['relation_id'] = $scheduleOfRate->id;
            $records[$key]['updated_at']  = date('d/m/Y H:i', strtotime($record['updated_at']));
            $records[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($records, array(
            'id'                                   => Constants::GRID_LAST_ROW,
            'description'                          => '',
            'relation_id'                          => $scheduleOfRate->id,
            'updated_at'                           => '-',
            'recalculate_resources_library_status' => false,
            '_csrf_token'                          => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeTradeAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $trade = new ScheduleOfRateTrade();
        $con   = $trade->getTable()->getConnection();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $prevTrade = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('prev_item_id')) : null;

            $priority         = $prevTrade ? $prevTrade->priority + 1 : 0;
            $scheduleOfRateId = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');
                $trade->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }
        }
        else
        {
            $this->forward404Unless($nextTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('before_id')));

            $priority         = $nextTrade->priority;
            $scheduleOfRateId = $nextTrade->schedule_of_rate_id;
        }

        $items = array();
        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('ScheduleOfRateTrade')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('schedule_of_rate_id = ?', $scheduleOfRateId)
                ->execute();

            $trade->schedule_of_rate_id = $scheduleOfRateId;
            $trade->priority            = $priority;

            $trade->save();

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item = array();

            $form = new BaseForm();

            $item['id']                                   = $trade->id;
            $item['description']                          = $trade->description;
            $item['relation_id']                          = $scheduleOfRateId;
            $item['updated_at']                           = date('d/m/Y H:i', strtotime($trade->updated_at));
            $item['_csrf_token']                          = $form->getCSRFToken();
            $item['recalculate_resources_library_status'] = $trade->recalculate_resources_library_status;

            array_push($items, $item);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'                                   => Constants::GRID_LAST_ROW,
                    'description'                          => '',
                    'relation_id'                          => $scheduleOfRateId,
                    'recalculate_resources_library_status' => false,
                    'updated_at'                           => '-',
                    '_csrf_token'                          => $form->getCSRFToken()
                ));
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
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeTradeUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $trade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('id')));

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
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeTradeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $trade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('id'))
        );

        $errorMsg = null;

        try
        {
            $item['id'] = $trade->id;
            $trade->delete();
            $success = true;
        }
        catch (Exception $e)
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
            $trade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find(intval($request->getParameter('target_id')));
        if ( !$targetTrade )
        {
            $this->forward404Unless($targetTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('prev_item_id')));
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
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            case 'copy':
                try
                {
                    $newTrade = $trade->copyTo($targetTrade, $lastPosition);

                    $form = new BaseForm();

                    $data['id']                                   = $newTrade->id;
                    $data['description']                          = $newTrade->description;
                    $data['relation_id']                          = $newTrade->schedule_of_rate_id;
                    $data['recalculate_resources_library_status'] = $newTrade->recalculate_resources_library_status;
                    $data['updated_at']                           = date('d/m/Y H:i', strtotime($newTrade->updated_at));
                    $data['_csrf_token']                          = $form->getCSRFToken();

                    $success  = true;
                    $errorMsg = null;
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            default:
                throw new Exception('invalid paste operation');
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() ));
    }

    public function executeTradeRecalculate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $trade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('id'))
        );

        ScheduleOfRateTradeTable::tradeLevelRecalculateTotalCost($trade->id);

        $trade->recalculate_resources_library_status = false;
        $trade->save();

        $trade->refresh();

        $tradeArr = $trade->toArray();

        $tradeArr['updated_at'] = date('d/m/Y H:i', strtotime($trade->updated_at));

        return $this->renderJson(array(
            'success' => true,
            'item'    => $tradeArr,
            'c'       => array()//empty children. we need to return children properties because grid.js expect list of children (in sor item level)
        ));
    }

    /**** trade actions end ****/

    public function executeResourceList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('item_id'))
        );

        $records = DoctrineQuery::create()->select('r.id, r.name, r.resource_library_id')
            ->from('ScheduleOfRateBuildUpRateResource r')
            ->where('r.schedule_of_rate_item_id = ?', $scheduleOfRateItem->id)
            ->addOrderBy('r.id ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach ( $records as $key => $record )
        {
            $records[$key]['total_build_up'] = $scheduleOfRateItem->calculateBuildUpTotalByResourceId($record['id']);
        }

        return $this->renderJson($records);
    }

    /**** item actions ****/
    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $trade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('id'))
        );

        $pdo                       = $trade->getTable()->getConnection()->getDbh();
        $formulatedColumns         = array();
        $form                      = new BaseForm();
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateItem');

        $stmt = $pdo->prepare("SELECT DISTINCT c.id, c.description, c.type, uom.id as uom_id, c.priority, c.lft,
        c.level, c.trade_id, c.level, c.updated_at, c.recalculate_resources_library_status, uom.symbol AS uom_symbol
        FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " c
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
        WHERE c.trade_id = " . $trade->id . " AND c.deleted_at IS NULL
        ORDER BY c.priority, c.lft, c.level ASC");

        $stmt->execute();
        $scheduleOfRateItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value, ifc.has_build_up
        FROM " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " ifc JOIN
        " . ScheduleOfRateItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
        WHERE i.trade_id = " . $trade->id . " AND ifc.deleted_at IS NULL
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

        foreach ( $scheduleOfRateItems as $key => $sorItem )
        {
            $scheduleOfRateItems[$key]['type']        = (string) $sorItem['type'];
            $scheduleOfRateItems[$key]['uom_id']      = $sorItem['uom_id'] > 0 ? (string) $sorItem['uom_id'] : '-1';
            $scheduleOfRateItems[$key]['uom_symbol']  = $sorItem['uom_id'] > 0 ? $sorItem['uom_symbol'] : '';
            $scheduleOfRateItems[$key]['relation_id'] = $trade->id;
            $scheduleOfRateItems[$key]['updated_at']  = date('d/m/Y H:i', strtotime($sorItem['updated_at']));
            $scheduleOfRateItems[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnConstants as $constant )
            {
                $scheduleOfRateItems[$key][$constant . '-final_value']        = 0;
                $scheduleOfRateItems[$key][$constant . '-value']              = '';
                $scheduleOfRateItems[$key][$constant . '-has_cell_reference'] = false;
                $scheduleOfRateItems[$key][$constant . '-has_formula']        = false;
                $scheduleOfRateItems[$key][$constant . '-has_build_up']       = false;
            }

            if ( array_key_exists($sorItem['id'], $formulatedColumns) )
            {
                foreach ( $formulatedColumns[$sorItem['id']] as $formulatedColumn )
                {
                    $columnName                                                     = $formulatedColumn['column_name'];
                    $scheduleOfRateItems[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                    $scheduleOfRateItems[$key][$columnName . '-value']              = $formulatedColumn['value'];
                    $scheduleOfRateItems[$key][$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
                    $scheduleOfRateItems[$key][$columnName . '-has_cell_reference'] = false;
                    $scheduleOfRateItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }
            }

            unset( $formulatedColumns[$sorItem['id']], $sorItem );
        }

        $defaultLastRow = array(
            'id'                                   => Constants::GRID_LAST_ROW,
            'description'                          => '',
            'type'                                 => (string) ScheduleOfRateItem::TYPE_WORK_ITEM,
            'uom_id'                               => '-1',
            'uom_symbol'                           => '',
            'relation_id'                          => $trade->id,
            'updated_at'                           => '-',
            'level'                                => 0,
            '_csrf_token'                          => $form->getCSRFToken(),
            'recalculate_resources_library_status' => false
        );

        foreach ( $formulatedColumnConstants as $constant )
        {
            $defaultLastRow[$constant . '-final_value']        = "";
            $defaultLastRow[$constant . '-value']              = "";
            $defaultLastRow[$constant . '-has_build_up']       = false;
            $defaultLastRow[$constant . '-has_cell_reference'] = false;
            $defaultLastRow[$constant . '-has_formula']        = false;
        }

        array_push($scheduleOfRateItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $scheduleOfRateItems
        ));
    }

    public function executeItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

        $rowData                   = array();
        $affectedNodes             = array();
        $isFormulatedColumn        = false;
        $formulatedColumnTable     = Doctrine_Core::getTable('ScheduleOfRateItemFormulatedColumn');
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateItem');

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

                $formulatedColumn->has_build_up = false;

                $formulatedColumn->save($con);

                $formulatedColumn->refresh();

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
            }

            $item->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item->refresh();

            if ( $isFormulatedColumn )
            {
                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                foreach ( $referencedNodes as $referencedNode )
                {
                    $node = $formulatedColumnTable->find($referencedNode['node_from']);

                    if ( $node )
                    {
                        $affectedNode = array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value
                        );
                        array_push($affectedNodes, $affectedNode);
                    }
                }

                $rowData[$fieldName . "-final_value"]        = $formulatedColumn->final_value;
                $rowData[$fieldName . "-value"]              = $formulatedColumn->value;
                $rowData[$fieldName . '-has_cell_reference'] = $formulatedColumn->hasCellReference();
                $rowData[$fieldName . '-has_formula']        = $formulatedColumn->hasFormula();
                $rowData[$fieldName . '-has_build_up']       = $formulatedColumn->has_build_up;
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
                    $rowData[$constant . '-has_build_up']       = $formulatedColumn ? $formulatedColumn['has_build_up'] : false;
                }
            }

            $rowData['affected_nodes']                       = $affectedNodes;
            $rowData['recalculate_resources_library_status'] = $item->recalculate_resources_library_status;
            $rowData['type']                                 = (string) $item->type;
            $rowData['uom_id']                               = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol']                           = $item->uom_id > 0 ? $item->getUnitOfMeasurement()->symbol : '';
            $rowData['updated_at']                           = date('d/m/Y H:i', strtotime($item->updated_at));
        }
        catch (Exception $e)
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
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateItem');
        $isFormulatedColumn        = false;

        $con = Doctrine_Core::getTable('ScheduleOfRateItem')->getConnection();

        try
        {
            $con->beginTransaction();

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('prev_item_id')) : null;
                $tradeId      = $request->getParameter('relation_id');

                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                $item = ScheduleOfRateItemTable::createItemFromLastRow($previousItem, $tradeId, $fieldName, $fieldValue);

                if ( in_array($fieldName, $formulatedColumnConstants) )
                {
                    $isFormulatedColumn = true;
                }
            }
            else
            {
                $this->forward404Unless($nextItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('before_id')));
                $tradeId = $nextItem->trade_id;

                $item = ScheduleOfRateItemTable::createItem($nextItem, $tradeId);
            }

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new ScheduleOfRateItemFormulatedColumn();
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

            $data['id']                                   = $item->id;
            $data['description']                          = $item->description;
            $data['type']                                 = (string) $item->type;
            $data['uom_id']                               = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $data['uom_symbol']                           = $item->uom_id > 0 ? Doctrine_Core::getTable('UnitOfMeasurement')->find($item->uom_id)->symbol : '';
            $data['relation_id']                          = $tradeId;
            $data['updated_at']                           = date('d/m/Y H:i', strtotime($item->updated_at));
            $data['level']                                = $item->level;
            $data['recalculate_resources_library_status'] = false;
            $data['_csrf_token']                          = $form->getCSRFToken();

            foreach ( $formulatedColumnConstants as $constant )
            {
                $formulatedColumn                        = $item->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $data[$constant . '-final_value']        = $finalValue;
                $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $data[$constant . '-has_cell_reference'] = false;
                $data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                $data[$constant . '-has_build_up']       = $formulatedColumn ? $formulatedColumn['has_build_up'] : false;
            }

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'                                   => Constants::GRID_LAST_ROW,
                    'description'                          => '',
                    'type'                                 => (string) ScheduleOfRateItem::TYPE_WORK_ITEM,
                    'uom_id'                               => '-1',
                    'uom_symbol'                           => '',
                    'relation_id'                          => $tradeId,
                    'updated_at'                           => '-',
                    'level'                                => 0,
                    'recalculate_resources_library_status' => false,
                    '_csrf_token'                          => $form->getCSRFToken()
                );

                foreach ( $formulatedColumnConstants as $constant )
                {
                    $defaultLastRow[$constant . '-final_value']        = "";
                    $defaultLastRow[$constant . '-value']              = "";
                    $defaultLastRow[$constant . '-has_build_up']       = false;
                    $defaultLastRow[$constant . '-has_cell_reference'] = false;
                    $defaultLastRow[$constant . '-has_formula']        = false;
                }

                array_push($items, $defaultLastRow);
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
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;
        $targetItem   = Doctrine_Core::getTable('ScheduleOfRateItem')->find(intval($request->getParameter('target_id')));

        if ( !$targetItem )
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('prev_item_id')));
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

                    $children = DoctrineQuery::create()->select('i.id, i.level')
                        ->from('ScheduleOfRateItem i')
                        ->where('i.root_id = ?', $item->root_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                        ->addOrderBy('i.lft')
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

                    $data['id']    = $item->id;
                    $data['level'] = $item->level;

                    $success  = true;
                    $errorMsg = null;
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            case 'copy':
                try
                {
                    $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateItem');

                    $newItem = $item->copyTo($targetItem, $lastPosition);

                    $form = new BaseForm();

                    $children = DoctrineQuery::create()
                        ->select('i.id, i.description, i.type, i.uom_id, i.trade_id, i.level, i.recalculate_resources_library_status, i.updated_at, uom.symbol, ifc.column_name, ifc.value, ifc.final_value, ifc.has_build_up')
                        ->from('ScheduleOfRateItem i')
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
                        $children[$key]['relation_id'] = $child['trade_id'];
                        $children[$key]['updated_at']  = date('d/m/Y H:i', strtotime($child['updated_at']));
                        $children[$key]['_csrf_token'] = $form->getCSRFToken();

                        foreach ( $formulatedColumnConstants as $constant )
                        {
                            $children[$key][$constant . '-final_value']        = 0;
                            $children[$key][$constant . '-value']              = '';
                            $children[$key][$constant . '-has_cell_reference'] = false;
                            $children[$key][$constant . '-has_formula']        = false;
                            $children[$key][$constant . '-has_build_up']       = false;
                        }

                        foreach ( $child['FormulatedColumns'] as $formulatedColumn )
                        {
                            $columnName                                          = $formulatedColumn['column_name'];
                            $children[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                            $children[$key][$columnName . '-value']              = $formulatedColumn['value'];
                            $children[$key][$columnName . '-has_cell_reference'] = false;
                            $children[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                            $children[$key][$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
                        }

                        unset( $children[$key]['FormulatedColumns'], $children[$key]['UnitOfMeasurement'] );
                    }

                    $data['id']                                   = $newItem->id;
                    $data['description']                          = $newItem->description;
                    $data['type']                                 = (string) $newItem->type;
                    $data['uom_id']                               = $newItem->uom_id > 0 ? (string) $newItem->uom_id : '-1';
                    $data['uom_symbol']                           = $newItem->uom_id > 0 ? $newItem->UnitOfMeasurement->symbol : '';
                    $data['relation_id']                          = $newItem->trade_id;
                    $data['updated_at']                           = date('d/m/Y H:i', strtotime($newItem->updated_at));
                    $data['level']                                = $newItem->level;
                    $data['recalculate_resources_library_status'] = $newItem->recalculate_resources_library_status;
                    $data['_csrf_token']                          = $form->getCSRFToken();

                    foreach ( $formulatedColumnConstants as $constant )
                    {
                        $formulatedColumn                        = $newItem->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                        $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                        $data[$constant . '-final_value']        = $finalValue;
                        $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                        $data[$constant . '-has_cell_reference'] = false;
                        $data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                        $data[$constant . '-has_build_up']       = $formulatedColumn ? $formulatedColumn['has_build_up'] : false;
                    }

                    $success  = true;
                    $errorMsg = null;
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            case 'rate'://paste copied rate cell
                try
                {
                    $targetItem->copyRateCellFromItem($item);

                    $targetItem->refresh(true);

                    if ( $rateColumn = $targetItem->getFormulatedColumnByName(ScheduleOfRateItem::FORMULATED_COLUMN_RATE) )
                    {
                        $data = array(
                            'id'                                   => $targetItem->id,
                            'recalculate_resources_library_status' => $targetItem->recalculate_resources_library_status,
                            'rate-value'                           => $rateColumn->value,
                            'rate-final_value'                     => $rateColumn->final_value,
                            'rate-has_build_up'                    => $rateColumn->has_build_up,
                            'rate-has_cell_reference'              => false,
                            'rate-has_formula'                     => $rateColumn->hasFormula()
                        );

                        $referencedNodes = $rateColumn->getNodesRelatedByColumnName(ScheduleOfRateItem::FORMULATED_COLUMN_RATE);

                        foreach ( $referencedNodes as $key => $referencedNode )
                        {
                            if ( $node = Doctrine_Core::getTable('ScheduleOfRateItemFormulatedColumn')->find($referencedNode['node_from']) )
                            {
                                array_push($children, array(
                                    'id'               => $node->relation_id,
                                    'rate-final_value' => $node->final_value
                                ));
                            }

                            unset( $referencedNodes[$key], $referencedNode );
                        }
                    }

                    $success  = true;
                    $errorMsg = null;
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            default:
                throw new Exception('invalid paste operation');
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => $children ));
    }

    public function executeItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        $con      = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('i.id')
                ->from('ScheduleOfRateItem i')
                ->andWhere('i.root_id = ?', $item->root_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $item->lft, $item->rgt ))
                ->addOrderBy('i.lft')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            $affectedNodes = $item->delete($con);

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();

            $errorMsg      = $e->getMessage();
            $success       = false;
            $affectedNodes = array();
            $items         = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => $affectedNodes ));
    }

    public function executeItemRateDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and
            $item = Doctrine_Core::getTable('scheduleOfRateItem')->find($request->getParameter('id')));

        $errorMsg            = null;
        $scheduleOfRateItems = array();

        try
        {
            $affectedItems = $item->deleteFormulatedColumns();

            $item->deleteBuildUpRates();

            array_push($affectedItems, array( 'id' => $item->id ));

            foreach ( $affectedItems as $item )
            {
                array_push($scheduleOfRateItems, array(
                    'id'                      => $item['id'],
                    'rate-value'              => '',
                    'rate-final_value'        => 0,
                    'rate-has_build_up'       => false,
                    'rate-has_cell_reference' => false,
                    'rate-has_formula'        => false
                ));
            }

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $scheduleOfRateItems ));
    }

    public function executeItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

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
                    ->from('ScheduleOfRateItem i')
                    ->where('i.root_id = ?', $item->root_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                $success = true;
            }
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executeItemOutdent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

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
                    ->from('ScheduleOfRateItem i')
                    ->where('i.root_id = ?', $item->root_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                $success = true;
            }
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executeItemRecalculate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id'))
        );

        $pdo      = $item->getTable()->getConnection()->getDbh();
        $children = array();

        // get schedule of rate build up rate items
        $stmt = $pdo->prepare("SELECT id FROM " . ScheduleOfRateBuildUpRateItemTable::getInstance()->getTableName() . "
        WHERE schedule_of_rate_item_id = " . $item->id . " AND deleted_at IS NULL ORDER BY id ASC");

        $stmt->execute();

        $buildUpRateItemIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        ScheduleOfRateBuildUpRateItemTable::recalculateColumnsWithRowLinking($buildUpRateItemIds);

        foreach ( $buildUpRateItemIds as $buildUpRateItemId )
        {
            ScheduleOfRateBuildUpRateItemTable::calculateTotalById($buildUpRateItemId);
            ScheduleOfRateBuildUpRateItemTable::calculateLineTotalById($buildUpRateItemId);
        }

        // recalculate build up summary
        $item->BuildUpRateSummary->calculateFinalCost();

        // need to update the existing recalculate status to false
        $item->recalculate_resources_library_status = false;
        $item->save();

        $item->refresh();

        // check for existing records under the same trade that has been flagged as recalculated
        // if all records has been recalculate then update the trade recalculate status to false
        $pdo = $item->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT COUNT(id) FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . "
            WHERE trade_id = (SELECT trade_id FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " WHERE id = " . $item->id . ")
            AND recalculate_resources_library_status IS TRUE AND deleted_at IS NULL GROUP BY trade_id");

        $stmt->execute();

        $result = $stmt->fetchColumn(0);

        if ( !$result )
        {
            $stmt = $pdo->prepare("UPDATE " . ScheduleOfRateTradeTable::getInstance()->getTableName() . "
            SET recalculate_resources_library_status = FALSE
            WHERE id = (SELECT trade_id FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " WHERE id = " . $item->id . ")
            AND recalculate_resources_library_status IS TRUE");

            $stmt->execute();
        }

        $formulatedColumns = $item->getFormulatedColumns()->toArray();

        $newItem               = $item->toArray();
        $newItem['updated_at'] = date('d/m/Y H:i', strtotime($newItem['updated_at']));
        $newItem['type']       = (string) $newItem['type'];

        foreach ( $formulatedColumns as $formulatedColumn )
        {
            $columnName = $formulatedColumn['column_name'];

            $newItem[$columnName . '-final_value']        = $formulatedColumn['final_value'];
            $newItem[$columnName . '-value']              = $formulatedColumn['value'];
            $newItem[$columnName . '-has_cell_reference'] = false;
            $newItem[$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            $newItem[$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
        }

        unset( $newItem['BuildUpRates'], $newItem['BuildUpRateSummary'], $newItem['FormulatedColumns'] );

        /*
         * To update any schedule of rate item's rate that linked to this item's rate
         */
        if ( $rateColumn = $item->getFormulatedColumnByName(ScheduleOfRateItem::FORMULATED_COLUMN_RATE) )
        {
            $referencedNodes = $rateColumn->getNodesRelatedByColumnName(ScheduleOfRateItem::FORMULATED_COLUMN_RATE);

            foreach ( $referencedNodes as $key => $referencedNode )
            {
                if ( $node = Doctrine_Core::getTable('ScheduleOfRateItemFormulatedColumn')->find($referencedNode['node_from']) )
                {
                    array_push($children, array(
                        'id'               => $node->relation_id,
                        'rate-final_value' => $node->final_value
                    ));
                }

                unset( $referencedNodes[$key], $referencedNode );
            }
        }

        return $this->renderJson(array(
            'success' => true,
            'item'    => $newItem,
            'c'       => $children
        ));
    }
    /**** item actions end ****/

    /*
     * Add Resource Category feat.
     */

    public function executeGetResourceList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('item_id'))
        );

        $form = new BaseForm();

        $records = DoctrineQuery::create()->select('r.id, r.name')
            ->from('Resource r')
            ->addOrderBy('r.id ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach ( $records as $key => $record )
        {
            $isResourceLibraryExists = $scheduleOfRateItem->isResourceLibraryExistsInBuildUpRate($record['id']);

            $records[$key]['resource_library_exists'] = $isResourceLibraryExists;
            $records[$key]['_csrf_token']             = $form->getCSRFToken();
        }

        array_push($records, array(
            'id'                      => Constants::GRID_LAST_ROW,
            'name'                    => '',
            'resource_library_exists' => false
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeResourceCategoryAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('rid')) and
            $scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('sorid'))
        );

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

        try
        {
            $buildUpRateResource = $scheduleOfRateItem->createBuildUpRateResourceFromResourceLibrary($resource);

            $success               = true;
            $errorMsg              = null;
            $resourceLibraryExists = true;
        }
        catch (Exception $e)
        {
            $buildUpRateResource   = null;
            $resourceLibraryExists = false;
            $errorMsg              = $e->getMessage();
            $success               = false;
        }

        return $this->renderJson(array(
            'resource_library_exists' => $resourceLibraryExists,
            'resource'                => $buildUpRateResource ? $buildUpRateResource->toArray() : null,
            'uom'                     => array(
                'values'  => $values,
                'options' => $options
            ),
            'error_msg'               => $errorMsg,
            'success'                 => $success
        ));
    }

    public function executeResourceCategoryDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('rid')) and
            $scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('sorid'))
        );

        try
        {
            $buildUpRateResource = ScheduleOfRateBuildUpRateResourceTable::getByResourceLibraryIdAndScheduleOfRateItemId($resource->id, $scheduleOfRateItem->id);

            $buildUpRateResourceId = $buildUpRateResource->id;

            if ( $buildUpRateResource )
            {
                $buildUpRateResource->delete();
            }

            /*
             * check after deleting resource, is there any resource left for schedule of rate item
             */
            $count = DoctrineQuery::create()->select('r.id')
                ->from('ScheduleOfRateBuildUpRateResource r')
                ->where('r.schedule_of_rate_item_id = ?', $scheduleOfRateItem->id)
                ->andWhere('r.deleted_at IS NULL')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->count();

            $isLastResource = $count > 0 ? false : true;

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $isLastResource        = false;
            $buildUpRateResourceId = null;
            $errorMsg              = $e->getMessage();
            $success               = false;
        }

        return $this->renderJson(array(
            'rid'              => $buildUpRateResourceId,
            'is_last_resource' => $isLastResource,
            'error_msg'        => $errorMsg,
            'success'          => $success
        ));
    }

    /**** start BuildUpRate ops ***/
    public function executeGetBuildUpRateItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('sor_item_id')) and
            $resource = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateResource')->find($request->getParameter('resource_id'))
        );

        $buildUpRateItems = DoctrineQuery::create()->select('i.id, i.description, i.uom_id, i.total, i.line_total, i.resource_item_library_id, ifc.column_name, ifc.value, ifc.final_value, ifc.linked, uom.symbol')
            ->from('ScheduleOfRateBuildUpRateItem i')
            ->leftJoin('i.FormulatedColumns ifc')
            ->leftJoin('i.UnitOfMeasurement uom')
            ->where('i.schedule_of_rate_item_id = ?', $scheduleOfRateItem->id)
            ->andWhere('i.build_up_rate_resource_id = ?', $resource->id)
            ->addOrderBy('i.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateBuildUpRateItem');

        foreach ( $buildUpRateItems as $key => $buildUpRateItem )
        {
            $buildUpRateItems[$key]['uom_id']      = $buildUpRateItem['uom_id'] > 0 ? (string) $buildUpRateItem['uom_id'] : '-1';
            $buildUpRateItems[$key]['uom_symbol']  = $buildUpRateItem['uom_id'] > 0 ? $buildUpRateItem['UnitOfMeasurement']['symbol'] : '';
            $buildUpRateItems[$key]['relation_id'] = $scheduleOfRateItem->id;
            $buildUpRateItems[$key]['linked']      = $buildUpRateItem['resource_item_library_id'] > 0 ? true : false;
            $buildUpRateItems[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnConstants as $constant )
            {
                $buildUpRateItems[$key][$constant . '-final_value']        = 0;
                $buildUpRateItems[$key][$constant . '-value']              = '';
                $buildUpRateItems[$key][$constant . '-has_cell_reference'] = false;
                $buildUpRateItems[$key][$constant . '-has_formula']        = false;
                $buildUpRateItems[$key][$constant . '-linked']             = false;
            }

            foreach ( $buildUpRateItem['FormulatedColumns'] as $formulatedColumn )
            {
                $columnName                                                  = $formulatedColumn['column_name'];
                $buildUpRateItems[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                $buildUpRateItems[$key][$columnName . '-value']              = $formulatedColumn['value'];
                $buildUpRateItems[$key][$columnName . '-has_cell_reference'] = false;
                $buildUpRateItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

                if ( $columnName == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_RATE or $columnName == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $columnName == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                {
                    $buildUpRateItems[$key][$columnName . '-linked'] = $formulatedColumn['linked'];
                }
            }

            unset( $buildUpRateItem, $buildUpRateItems[$key]['FormulatedColumns'], $buildUpRateItems[$key]['UnitOfMeasurement'] );
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'uom_id'      => '-1',
            'uom_symbol'  => '',
            'relation_id' => $scheduleOfRateItem->id,
            'total'       => '',
            'line_total'  => '',
            'linked'      => false,
            '_csrf_token' => $form->getCSRFToken()
        );

        foreach ( $formulatedColumnConstants as $constant )
        {
            $defaultLastRow[$constant . '-final_value']        = "";
            $defaultLastRow[$constant . '-value']              = "";
            $defaultLastRow[$constant . '-linked']             = false;
            $defaultLastRow[$constant . '-has_cell_reference'] = false;
            $defaultLastRow[$constant . '-has_formula']        = false;
        }

        array_push($buildUpRateItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $buildUpRateItems
        ));
    }

    public function executeGetBuildUpSummary(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

        $count = DoctrineQuery::create()->select('s.id')
            ->from('ScheduleOfRateBuildUpRateSummary s')
            ->where('s.schedule_of_rate_item_id = ?', $item->id)
            ->limit(1)
            ->count();

        if ( $count == 0 )
        {
            $buildUpRateSummary                             = new ScheduleOfRateBuildUpRateSummary();
            $buildUpRateSummary->schedule_of_rate_item_id   = $item->id;
            $buildUpRateSummary->conversion_factor_operator = Constants::ARITHMETIC_OPERATOR_MULTIPLICATION;
            $buildUpRateSummary->save();
        }

        return $this->renderJson(array(
            'apply_conversion_factor'     => $item->BuildUpRateSummary->apply_conversion_factor,
            'conversion_factor_amount'    => $item->BuildUpRateSummary->conversion_factor_amount,
            'conversion_factor_operator'  => $item->BuildUpRateSummary->conversion_factor_operator,
            'total_cost'                  => $item->BuildUpRateSummary->calculateTotalCost(),
            'total_cost_after_conversion' => $item->BuildUpRateSummary->getTotalCostAfterConversion(),
            'markup'                      => $item->BuildUpRateSummary->markup,
            'final_cost'                  => $item->BuildUpRateSummary->calculateFinalCost(),
            'updated_at'                  => date('d/m/Y H:i', strtotime($item->BuildUpRateSummary->updated_at)),
        ));
    }

    public function executeGetConversionFactorUom(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

        $records = DoctrineQuery::create()->select('u.id, u.symbol')
            ->from('UnitOfMeasurement u')
            ->where('u.display IS TRUE')
            ->addOrderBy('u.symbol ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $uomOptions = array(
            array(
                'value' => '-1',
                'label' => '---'
            )
        );

        $buildUpRateSummary = $item->BuildUpRateSummary;

        foreach ( $records as $record )
        {
            $optionArray = array(
                'value' => (string) $record['id'],
                'label' => $record['symbol']
            );

            if ( $buildUpRateSummary->conversion_factor_uom_id == $record['id'] )
            {
                $optionArray['selected'] = true;
            }
            array_push($uomOptions, $optionArray);
        }

        $data['uomOptions'] = $uomOptions;

        return $this->renderJson($data);
    }

    public function executeBuildUpSummaryMarkupUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

        $value = $request->getParameter('value');

        $markup = strlen($value) > 0 ? floatval($value) : 0;

        $item->BuildUpRateSummary->markup = $markup;

        $item->BuildUpRateSummary->save();

        $item->save();

        return $this->renderJson(array(
            'markup'     => $item->BuildUpRateSummary->markup,
            'final_cost' => $item->BuildUpRateSummary->calculateFinalCost()
        ));
    }

    public function executeBuildUpSummaryApplyConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

        $value = $request->getParameter('value');

        $item->BuildUpRateSummary->apply_conversion_factor = $value;

        $item->BuildUpRateSummary->save();

        $item->save();

        $item->BuildUpRateSummary->refresh();

        return $this->renderJson(array(
            'conversion_factor_amount'    => $item->BuildUpRateSummary->conversion_factor_amount,
            'conversion_factor_operator'  => $item->BuildUpRateSummary->conversion_factor_operator,
            'total_cost_after_conversion' => $item->BuildUpRateSummary->getTotalCostAfterConversion(),
            'final_cost'                  => $item->BuildUpRateSummary->calculateFinalCost()
        ));
    }

    public function executeBuildUpSummaryConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

        $buildUpRateSummary = $item->BuildUpRateSummary;

        $val = $request->getParameter('val');

        switch ($request->getParameter('type'))
        {
            case 'amount':
                $conversionFactorAmount                       = strlen($val) > 0 ? floatval($val) : 0;
                $buildUpRateSummary->conversion_factor_amount = $conversionFactorAmount;
                break;
            case 'operator':
                $buildUpRateSummary->conversion_factor_operator = $val;
                break;
            default:
                break;
        }

        $buildUpRateSummary->save();

        $buildUpRateSummary->refresh();

        $item->save();

        return $this->renderJson(array(
            'conversion_factor_amount'    => $buildUpRateSummary->conversion_factor_amount,
            'total_cost_after_conversion' => $buildUpRateSummary->getTotalCostAfterConversion(),
            'conversion_factor_operator'  => $buildUpRateSummary->conversion_factor_operator,
            'final_cost'                  => $buildUpRateSummary->calculateFinalCost()
        ));
    }

    public function executeBuildUpSummaryConversionFactorUomUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

        $buildUpRateSummary = $item->BuildUpRateSummary;

        $uomId = intval($request->getParameter('uom_id'));

        $uomId = $uomId > 0 ? $uomId : null;

        $con = $item->getTable()->getConnection();
        try
        {
            $con->beginTransaction();

            $buildUpRateSummary->conversion_factor_uom_id = $uomId;

            $buildUpRateSummary->save();

            $item->save();

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success ));
    }

    public function executeBuildUpRateItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateItem')->find($request->getParameter('id'))
        );

        $rowData                   = array();
        $con                       = $item->getTable()->getConnection();
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateBuildUpRateItem');

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $affectedNodes      = array();
            $isFormulatedColumn = false;

            if ( in_array($fieldName, $formulatedColumnConstants) )
            {
                $formulatedColumnTable = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateFormulatedColumn');

                $formulatedColumn = $formulatedColumnTable->getByRelationIdAndColumnName($item->id, $fieldName);

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->save($con);

                $formulatedColumn->refresh();

                $isFormulatedColumn = true;
            }
            else
            {
                $fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;

                $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }

            $item->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item->refresh();

            if ( $isFormulatedColumn )
            {
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

                $rowData[$fieldName . "-final_value"]        = $formulatedColumn->final_value;
                $rowData[$fieldName . "-value"]              = $formulatedColumn->value;
                $rowData[$fieldName . '-has_cell_reference'] = $formulatedColumn->hasCellReference();
                $rowData[$fieldName . '-has_formula']        = $formulatedColumn->hasFormula();

                if ( $fieldName == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_RATE or $fieldName == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $fieldName == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                {
                    $rowData[$fieldName . '-linked'] = $formulatedColumn->linked;
                }

                $rowData['total']      = $item->calculateTotal();
                $rowData['line_total'] = $item->calculateLineTotal();
            }
            else
            {
                $rowData[$fieldName] = $item->$fieldName;

                foreach ( $formulatedColumnConstants as $constant )
                {
                    $formulatedColumn                           = $item->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                    $finalValue                                 = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                    $rowData[$constant . '-final_value']        = $finalValue;
                    $rowData[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                    $rowData[$constant . '-has_cell_reference'] = false;
                    $rowData[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

                    if ( $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                    {
                        $rowData[$constant . '-linked'] = $formulatedColumn ? $formulatedColumn['linked'] : false;
                    }
                }
            }

            $rowData['affected_nodes'] = $affectedNodes;
            $rowData['linked']         = $item->resource_item_library_id > 0 ? true : false;
            $rowData['uom_id']         = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol']     = $item->uom_id > 0 ? $item->getUnitOfMeasurement()->symbol : '';

            $totalBuildUp = $item->ScheduleOfRateItem->calculateBuildUpTotalByResourceId($item->build_up_rate_resource_id);
        }
        catch (Exception $e)
        {
            $con->rollback();
            $totalBuildUp = 0;
            $errorMsg     = $e->getMessage();
            $success      = false;
        }

        return $this->renderJson(array(
            'success'        => $success,
            'errorMsg'       => $errorMsg,
            'total_build_up' => $totalBuildUp,
            'data'           => $rowData
        ));
    }

    public function executeBuildUpRateItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $items = array();

        $item = new ScheduleOfRateBuildUpRateItem();

        $con = $item->getTable()->getConnection();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateBuildUpRateItem');

        $isFormulatedColumn = false;

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $this->forward404Unless($resource = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateResource')->find($request->getParameter('resource_id')));

            $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ScheduleOfRateBuildUpRateItem')->find($request->getParameter('prev_item_id')) : null;

            $priority = $previousItem ? $previousItem->priority + 1 : 0;

            $scheduleOfRateItemId = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');

                if ( in_array($fieldName, $formulatedColumnConstants) )
                {
                    $isFormulatedColumn = true;
                }
                else
                {
                    $fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;
                    $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                }
            }

            $resourceId = $resource->id;
        }
        else
        {
            $this->forward404Unless($nextItem = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateItem')->find($request->getParameter('before_id')));

            $scheduleOfRateItemId = $nextItem->schedule_of_rate_item_id;
            $resourceId           = $nextItem->build_up_rate_resource_id;
            $priority             = $nextItem->priority;
        }

        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('ScheduleOfRateBuildUpRateItem')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('schedule_of_rate_item_id = ?', $scheduleOfRateItemId)
                ->andWhere('build_up_rate_resource_id = ?', $resourceId)
                ->execute();

            $item->schedule_of_rate_item_id  = $scheduleOfRateItemId;
            $item->build_up_rate_resource_id = $resourceId;
            $item->priority                  = $priority;

            $item->save($con);

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new ScheduleOfRateBuildUpRateFormulatedColumn();
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

            $data['id']          = $item->id;
            $data['description'] = $item->description;
            $data['uom_id']      = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $data['uom_symbol']  = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            $data['relation_id'] = $scheduleOfRateItemId;
            $data['linked']      = $item->resource_item_library_id > 0 ? true : false;
            $data['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnConstants as $constant )
            {
                $formulatedColumn                        = $item->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $data[$constant . '-final_value']        = $finalValue;
                $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $data[$constant . '-has_cell_reference'] = false;
                $data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

                if ( $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                {
                    $data[$constant . '-linked'] = $formulatedColumn ? $formulatedColumn['linked'] : false;
                }
            }

            $data['total']      = $item->calculateTotal();
            $data['line_total'] = $item->calculateLineTotal();

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'uom_id'      => '-1',
                    'uom_symbol'  => '',
                    'relation_id' => $scheduleOfRateItemId,
                    'total'       => '',
                    'line_total'  => '',
                    'linked'      => false,
                    '_csrf_token' => $form->getCSRFToken()
                );

                foreach ( $formulatedColumnConstants as $constant )
                {
                    $defaultLastRow[$constant . '-final_value']        = "";
                    $defaultLastRow[$constant . '-value']              = "";
                    $defaultLastRow[$constant . '-linked']             = false;
                    $defaultLastRow[$constant . '-has_cell_reference'] = false;
                    $defaultLastRow[$constant . '-has_formula']        = false;
                }

                array_push($items, $defaultLastRow);
            }

            $totalBuildUp = $item->ScheduleOfRateItem->calculateBuildUpTotalByResourceId($item->build_up_rate_resource_id);
        }
        catch (Exception $e)
        {
            $con->rollback();
            $totalBuildUp = 0;
            $errorMsg     = $e->getMessage();
            $success      = false;
        }

        return $this->renderJson(array(
            'success'        => $success,
            'items'          => $items,
            'total_build_up' => $totalBuildUp,
            'errorMsg'       => $errorMsg
        ));
    }

    public function executeBuildUpRateItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $buildUpItem = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;

        try
        {
            $scheduleOfRateItem = $buildUpItem->ScheduleOfRateItem;
            $item['id']         = $buildUpItem->id;
            $resourceId         = $buildUpItem->build_up_rate_resource_id;
            $affectedNodes      = $buildUpItem->delete();

            $scheduleOfRateItem->save();

            $scheduleOfRateItem->refresh();

            $buildUpSummary = array(
                'conversion_factor_amount'    => $scheduleOfRateItem->BuildUpRateSummary->conversion_factor_amount,
                'total_cost_after_conversion' => $scheduleOfRateItem->BuildUpRateSummary->getTotalCostAfterConversion(),
                'final_cost'                  => $scheduleOfRateItem->BuildUpRateSummary->calculateFinalCost()
            );

            $totalBuildUp = $scheduleOfRateItem->calculateBuildUpTotalByResourceId($resourceId);

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg       = $e->getMessage();
            $item           = array();
            $affectedNodes  = array();
            $buildUpSummary = array();
            $totalBuildUp   = 0;
            $success        = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $item, 'affected_nodes' => $affectedNodes, 'build_up_summary' => $buildUpSummary, 'total_build_up' => $totalBuildUp ));
    }

    public function executeBuildUpRateItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $buildUpRateItem = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateItem')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetBuildUpRateItem = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateItem')->find(intval($request->getParameter('target_id')));
        if ( !$targetBuildUpRateItem )
        {
            $this->forward404Unless($targetBuildUpRateItem = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateItem')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetBuildUpRateItem->id == $buildUpRateItem->id )
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
                    $buildUpRateItem->moveTo($targetBuildUpRateItem->priority, $lastPosition);

                    $data['id'] = $buildUpRateItem->id;

                    $totalBuildUp = null;
                    $success      = true;
                    $errorMsg     = null;
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            case 'copy':
                try
                {
                    $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateBuildUpRateItem');
                    $newBuildUpRateItem        = $buildUpRateItem->copyTo($targetBuildUpRateItem, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newBuildUpRateItem->id;
                    $data['description'] = $newBuildUpRateItem->description;
                    $data['uom_id']      = $newBuildUpRateItem->uom_id > 0 ? (string) $newBuildUpRateItem->uom_id : '-1';
                    $data['uom_symbol']  = $newBuildUpRateItem->uom_id > 0 ? $newBuildUpRateItem->UnitOfMeasurement->symbol : '';
                    $data['relation_id'] = $newBuildUpRateItem->schedule_of_rate_item_id;
                    $data['linked']      = $newBuildUpRateItem->resource_item_library_id > 0 ? true : false;
                    $data['_csrf_token'] = $form->getCSRFToken();

                    foreach ( $formulatedColumnConstants as $constant )
                    {
                        $formulatedColumn                        = $newBuildUpRateItem->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                        $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                        $data[$constant . '-final_value']        = $finalValue;
                        $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                        $data[$constant . '-has_cell_reference'] = false;
                        $data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

                        if ( $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                        {
                            $data[$constant . '-linked'] = $formulatedColumn ? $formulatedColumn['linked'] : false;
                        }
                    }

                    $data['total']      = $newBuildUpRateItem->calculateTotal();
                    $data['line_total'] = $newBuildUpRateItem->calculateLineTotal();

                    $totalBuildUp = $newBuildUpRateItem->ScheduleOfRateItem->calculateBuildUpTotalByResourceId($newBuildUpRateItem->build_up_rate_resource_id);

                    $success  = true;
                    $errorMsg = null;
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            default:
                throw new Exception('invalid paste operation');
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data, 'total_build_up' => $totalBuildUp ));
    }

    public function executeImportResourceItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $resource = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateResource')->find($request->getParameter('rid')) and
            $scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        $items    = array();

        try
        {
            $buildUpRateItems = $scheduleOfRateItem->importResourceItems(Utilities::array_filter_integer(explode(',', $request->getParameter('ids'))), $resource);

            $scheduleOfRateItem->save();

            $form                      = new BaseForm();
            $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateBuildUpRateItem');

            foreach ( $buildUpRateItems as $buildUpRateItem )
            {
                $item = array();

                $item['id']          = $buildUpRateItem->id;
                $item['description'] = $buildUpRateItem->description;
                $item['uom_id']      = $buildUpRateItem->uom_id > 0 ? (string) $buildUpRateItem->uom_id : '-1';
                $item['uom_symbol']  = $buildUpRateItem->uom_id > 0 ? $buildUpRateItem->UnitOfMeasurement->symbol : '';
                $item['relation_id'] = $scheduleOfRateItem->id;
                $item['linked']      = $buildUpRateItem->resource_item_library_id > 0 ? true : false;
                $item['_csrf_token'] = $form->getCSRFToken();

                foreach ( $formulatedColumnConstants as $constant )
                {
                    $formulatedColumn                        = $buildUpRateItem->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                    $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                    $item[$constant . '-final_value']        = $finalValue;
                    $item[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                    $item[$constant . '-has_cell_reference'] = false;
                    $item[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

                    if ( $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                    {
                        $item[$constant . '-linked'] = $formulatedColumn ? $formulatedColumn['linked'] : false;
                    }
                }

                $item['total']      = $buildUpRateItem->calculateTotal();
                $item['line_total'] = $buildUpRateItem->calculateLineTotal();

                array_push($items, $item);
            }

            $defaultLastRow = array(
                'id'          => Constants::GRID_LAST_ROW,
                'description' => '',
                'uom_id'      => '-1',
                'uom_symbol'  => '',
                'relation_id' => $scheduleOfRateItem->id,
                'total'       => '',
                'line_total'  => '',
                'linked'      => false,
                '_csrf_token' => $form->getCSRFToken()
            );

            foreach ( $formulatedColumnConstants as $constant )
            {
                $defaultLastRow[$constant . '-final_value']        = "";
                $defaultLastRow[$constant . '-value']              = "";
                $defaultLastRow[$constant . '-linked']             = false;
                $defaultLastRow[$constant . '-has_cell_reference'] = false;
                $defaultLastRow[$constant . '-has_formula']        = false;
            }

            array_push($items, $defaultLastRow);

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
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
        }
        catch (Exception $e)
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
        }
        catch (Exception $e)
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
        }
        catch (Exception $e)
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
        }
        catch (Exception $e)
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
            $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('schedule_of_rate_id')) and
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
        }
        catch (Exception $e)
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
            $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('schedule_of_rate_id'))
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
        $lastPriority = ScheduleOfRateTradeTable::getMaxPriorityByScheduleOfRateId($scheduleOfRate->id);

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
                ScheduleOfRateTradeTable::getInstance()->getTableName(),
                array( 'description', 'schedule_of_rate_id', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
            );

            foreach ( $trades->children() as $importedTrade )
            {
                $tradeId = (int) $importedTrade->id;

                $description = html_entity_decode((string) $importedTrade->description);

                if ( in_array($tradeId, $tradeIds) )
                {

                    $stmt->addRecord(array( $description, $scheduleOfRate->id, $priority, 'NOW()', 'NOW()', $userId, $userId ), $tradeId);

                    array_push($items, array(
                        'id'                                   => $tradeId,
                        'description'                          => $description,
                        'relation_id'                          => $scheduleOfRate->id,
                        'recalculate_resources_library_status' => false,
                        '_csrf_token'                          => '',
                        'updated_at'                           => date('d/m/Y H:i')
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
                            ScheduleOfRateItemTable::getInstance()->getTableName(),
                            array( 'trade_id', 'description', 'type', 'uom_id', 'level', 'root_id', 'lft', 'rgt', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
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
                    if ( $withRate && (int) $importedItem->type != ScheduleOfRateItem::TYPE_HEADER )
                    {
                        $fieldName = ScheduleOfRateItem::FORMULATED_COLUMN_RATE;

                        array_push($ratesToSave, array(
                            $originalItemId, $fieldName, $rate, $rate, 'NOW()', 'NOW()', $userId, $userId
                        ));
                    }
                }

                unset( $importedItem );
            }

            unset( $billItems );

            $stmt->createInsert(
                ScheduleOfRateItemTable::getInstance()->getTableName(),
                array( 'trade_id', 'description', 'type', 'uom_id', 'level', 'root_id', 'lft', 'rgt', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
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
            $stmt->rebuildItemTreeStructureBySorTradeIds('ScheduleOfRateItem', ScheduleOfRateItemTable::getInstance()->getTableName(), $importedTradeToTradeIds, $rootIdToItemIds);

            if ( count($ratesToSave) )
            {
                //Save Qty & Rates
                $stmt->createInsert(
                    ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName(),
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
        }
        catch (Exception $e)
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

    public function executeGetResourceDescendantsForImport(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('id'))
        );

        try
        {
            $items = DoctrineQuery::create()->select('i.id')
                ->from('ResourceItem i')
                ->andWhere('i.root_id = ?', $resourceItem->root_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $resourceItem->lft, $resourceItem->rgt ))
                ->addOrderBy('i.lft')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
            $items    = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
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

    public function executeImportBuildSpaceSORFile(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isMethod('POST') AND
            $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId'))
        );

        session_write_close();

        sfConfig::set('sf_web_debug', false);

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        $fileName       = sfBuildSpaceScheduleOfRateXMLGenerator::XML_FILENAME.'.xml';
        $folderName     = md5(time());
        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR;

        try
        {
            mkdir($tempUploadPath);

            foreach ( $request->getFiles() as $file )
            {
                if ( !is_readable($file['fileUpload']['tmp_name']) )
                {
                    throw new InvalidArgumentException('Uploaded File is not readable.');
                }

                // Later to do some checking Here FileType ETC.
                $newName    = date('dmY_H_i_s');
                $ext        = pathinfo($file['fileUpload']['name'], PATHINFO_EXTENSION);
                $pathToFile = $tempUploadPath . $newName . '.' . $ext;

                //Move Uploaded Files to temp folder
                move_uploaded_file($file['fileUpload']['tmp_name'], $pathToFile);

                $zip = new ZipArchive;
                $res = $zip->open($pathToFile);

                if ( $res !== true )
                {
                    throw new Exception('Invalid BuildSpace Schedule of Rate Library File!');
                }

                $zip->extractTo($tempUploadPath);
                $zip->close();

                break;
            }

            if ( !is_readable($tempUploadPath . $fileName) )
            {
                throw new InvalidArgumentException('Invalid BuildSpace Schedule of Rate Library File!');
            }

            $con->beginTransaction();

            // will get the uploaded file and then unzip
            // check file name and extension
            // begin processing of parsing the xml file and then map it according to the database structure

            //Initiate xmlParser
            $xmlParser = new sfBuildspaceXMLParser(sfBuildSpaceScheduleOfRateXMLGenerator::XML_FILENAME, $tempUploadPath, null, true);

            // read xmlParser
            $xmlParser->read();

            // Get XML Processed Data
            $loadedXML = $xmlParser->getProcessedData();

            $importer = new sfBuildSpaceScheduleOfRateLibraryImporter($scheduleOfRate, $loadedXML, $con);
            $importer->import();

            $con->commit();

            $errorMsg = null;
            $success  = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        Utilities::delTree($tempUploadPath);

        $returnData['success']  = $success;
        $returnData['errorMsg'] = $errorMsg;

        return $this->renderJson($returnData);
    }

    public function executeExportBuildSpaceXML(sfWebRequest $request)
    {
        $this->forward404Unless(
            $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateLibraryId')
        ));

        $filesToZip = array();
        $fileName   = $request->getPostParameter('fileName');
        $conn       = $scheduleOfRate->getTable()->getConnection();

        try
        {
            sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url' ));

            $fileExporter = new sfBuildSpaceScheduleOfRateXMLGenerator($scheduleOfRate, $conn);
            $fileExporter->generateXMLFile();

            array_push($filesToZip, $fileExporter->getFileInformation());

            $sfZipGenerator = new sfZipGenerator($fileName, null, 'sor', true, true);
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
                "attachment; filename*=UTF-8''" . rawurlencode($request->getParameter('fileName')) . "." . $fileInfo['extension']
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

        return $this->renderJson(array( 'success' => false, 'errorMsg' => $errorMsg ));
    }

}