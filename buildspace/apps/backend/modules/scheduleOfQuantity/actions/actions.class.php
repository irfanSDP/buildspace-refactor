<?php

/**
 * scheduleOfQuantity actions.
 *
 * @package    buildspace
 * @subpackage scheduleOfQuantity
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class scheduleOfQuantityActions extends BaseActions {

    public function executeGetScheduleOfQuantityList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')));

        $records = DoctrineQuery::create()->select('soq.id, soq.title, soq.updated_at')
            ->from('ScheduleOfQuantity soq')
            ->andWhere('soq.project_structure_id = ?', $project->id)
            ->addOrderBy('soq.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        foreach ( $records as $key => $record )
        {
            $records[$key]['relation_id'] = $project->id;
            $records[$key]['updated_at']  = date('d/m/Y H:i', strtotime($record['updated_at']));
            $records[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'title'       => '',
            'relation_id' => $project->id,
            'updated_at'  => '-',
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeScheduleOfQuantityAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $scheduleOfQuantity = new ScheduleOfQuantity();
        $con                = $scheduleOfQuantity->getTable()->getConnection();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $prevScheduleOfQuantity = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ScheduleOfQuantity')->find($request->getParameter('prev_item_id')) : null;

            $priority  = $prevScheduleOfQuantity ? $prevScheduleOfQuantity->priority + 1 : 0;
            $projectId = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');
                $scheduleOfQuantity->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }

        }
        else
        {
            $this->forward404Unless($nextScheduleOfQuantity = Doctrine_Core::getTable('ScheduleOfQuantity')->find($request->getParameter('before_id')));

            $priority  = $nextScheduleOfQuantity->priority;
            $projectId = $nextScheduleOfQuantity->project_structure_id;
        }

        $items = array();
        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('ScheduleOfQuantity')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('project_structure_id = ?', $projectId)
                ->execute();

            $scheduleOfQuantity->project_structure_id = $projectId;
            $scheduleOfQuantity->priority             = $priority;
            $scheduleOfQuantity->identifier_type      = ScheduleOfQuantity::IDENTIFIER_TYPE_MANUAL;
            $scheduleOfQuantity->save();

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item = array();

            $form = new BaseForm();

            $item['id']          = $scheduleOfQuantity->id;
            $item['title']       = $scheduleOfQuantity->title;
            $item['relation_id'] = $projectId;
            $item['updated_at']  = date('d/m/Y H:i', strtotime($scheduleOfQuantity->updated_at));
            $item['_csrf_token'] = $form->getCSRFToken();

            array_push($items, $item);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'title'       => '',
                    'relation_id' => $projectId,
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

    public function executeScheduleOfQuantityUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $scheduleOfQuantity = Doctrine_Core::getTable('ScheduleOfQuantity')->find($request->getParameter('id')));

        $rowData = array();
        $con     = $scheduleOfQuantity->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $scheduleOfQuantity->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $scheduleOfQuantity->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $rowData = array(
                $fieldName   => $scheduleOfQuantity->$fieldName,
                'updated_at' => date('d/m/Y H:i', strtotime($scheduleOfQuantity->updated_at))
            );
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeScheduleOfQuantityDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $scheduleOfQuantity = Doctrine_Core::getTable('ScheduleOfQuantity')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        try
        {
            $item['id'] = $scheduleOfQuantity->id;

            $scheduleOfQuantity->delete();

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

    public function executeScheduleOfQuantityPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $scheduleOfQuantity = Doctrine_Core::getTable('ScheduleOfQuantity')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetScheduleOfQuantity = Doctrine_Core::getTable('ScheduleOfQuantity')->find(intval($request->getParameter('target_id')));

        if ( !$targetScheduleOfQuantity )
        {
            $this->forward404Unless($targetScheduleOfQuantity = Doctrine_Core::getTable('ScheduleOfQuantity')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetScheduleOfQuantity->id == $scheduleOfQuantity->id )
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
                    $scheduleOfQuantity->moveTo($targetScheduleOfQuantity->priority, $lastPosition);

                    $data['id']         = $scheduleOfQuantity->id;
                    $data['updated_at'] = date('d/m/Y H:i', strtotime($scheduleOfQuantity->updated_at));

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
                    $newScheduleOfQuantity = $scheduleOfQuantity->copyTo($targetScheduleOfQuantity, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newScheduleOfQuantity->id;
                    $data['title']       = $newScheduleOfQuantity->title;
                    $data['relation_id'] = $newScheduleOfQuantity->project_structure_id;
                    $data['updated_at']  = date('d/m/Y H:i', strtotime($newScheduleOfQuantity->updated_at));
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

    public function executeGetTradeList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $scheduleOfQuantity = Doctrine_Core::getTable('ScheduleOfQuantity')->find($request->getParameter('sid')));

        $records = DoctrineQuery::create()->select('t.id, t.description, t.schedule_of_quantity_id AS relation_id, t.updated_at')
            ->from('ScheduleOfQuantityTrade t')
            ->andWhere('t.schedule_of_quantity_id = ?', $scheduleOfQuantity->id)
            ->addOrderBy('t.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        foreach ( $records as $key => $record )
        {
            $records[$key]['updated_at']  = date('d/m/Y H:i', strtotime($record['updated_at']));
            $records[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'relation_id' => $scheduleOfQuantity->id,
            'updated_at'  => '-',
            '_csrf_token' => $form->getCSRFToken()
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

        $scheduleOfQuantityTrade = new ScheduleOfQuantityTrade();
        $con                     = $scheduleOfQuantityTrade->getTable()->getConnection();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $prevScheduleOfQuantityTrade = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ScheduleOfQuantityTrade')->find($request->getParameter('prev_item_id')) : null;

            $priority             = $prevScheduleOfQuantityTrade ? $prevScheduleOfQuantityTrade->priority + 1 : 0;
            $scheduleOfQuantityId = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');
                $scheduleOfQuantityTrade->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }

        }
        else
        {
            $this->forward404Unless($nextScheduleOfQuantityTrade = Doctrine_Core::getTable('ScheduleOfQuantityTrade')->find($request->getParameter('before_id')));

            $priority             = $nextScheduleOfQuantityTrade->priority;
            $scheduleOfQuantityId = $nextScheduleOfQuantityTrade->schedule_of_quantity_id;
        }

        $items = array();
        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('ScheduleOfQuantityTrade')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('schedule_of_quantity_id = ?', $scheduleOfQuantityId)
                ->execute();

            $scheduleOfQuantityTrade->schedule_of_quantity_id = $scheduleOfQuantityId;
            $scheduleOfQuantityTrade->priority                = $priority;

            $scheduleOfQuantityTrade->save();

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item = array();

            $form = new BaseForm();

            $item['id']          = $scheduleOfQuantityTrade->id;
            $item['description'] = $scheduleOfQuantityTrade->description;
            $item['relation_id'] = $scheduleOfQuantityId;
            $item['updated_at']  = date('d/m/Y H:i', strtotime($scheduleOfQuantityTrade->updated_at));
            $item['_csrf_token'] = $form->getCSRFToken();

            array_push($items, $item);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'relation_id' => $scheduleOfQuantityId,
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

    public function executeTradeUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $scheduleOfQuantityTrade = Doctrine_Core::getTable('ScheduleOfQuantityTrade')->find($request->getParameter('id'))
        );

        $rowData = array();
        $con     = $scheduleOfQuantityTrade->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $scheduleOfQuantityTrade->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $scheduleOfQuantityTrade->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $rowData = array(
                $fieldName   => $scheduleOfQuantityTrade->$fieldName,
                'updated_at' => date('d/m/Y H:i', strtotime($scheduleOfQuantityTrade->updated_at))
            );
        } catch (Exception $e)
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

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $scheduleOfQuantityTrade = Doctrine_Core::getTable('ScheduleOfQuantityTrade')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        try
        {
            $item['id'] = $scheduleOfQuantityTrade->id;

            $scheduleOfQuantityTrade->delete();

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
            $scheduleOfQuantityTrade = Doctrine_Core::getTable('ScheduleOfQuantityTrade')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetScheduleOfQuantityTrade = Doctrine_Core::getTable('ScheduleOfQuantityTrade')->find(intval($request->getParameter('target_id')));

        if ( !$targetScheduleOfQuantityTrade )
        {
            $this->forward404Unless($targetScheduleOfQuantityTrade = Doctrine_Core::getTable('ScheduleOfQuantityTrade')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetScheduleOfQuantityTrade->id == $scheduleOfQuantityTrade->id )
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
                    $scheduleOfQuantityTrade->moveTo($targetScheduleOfQuantityTrade->priority, $lastPosition);

                    $data['id']         = $scheduleOfQuantityTrade->id;
                    $data['updated_at'] = date('d/m/Y H:i', strtotime($scheduleOfQuantityTrade->updated_at));

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
                    $newScheduleOfQuantityTrade = $scheduleOfQuantityTrade->copyTo($targetScheduleOfQuantityTrade, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newScheduleOfQuantityTrade->id;
                    $data['description'] = $newScheduleOfQuantityTrade->description;
                    $data['relation_id'] = $newScheduleOfQuantityTrade->schedule_of_quantity_id;
                    $data['updated_at']  = date('d/m/Y H:i', strtotime($newScheduleOfQuantityTrade->updated_at));
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

    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $trade = Doctrine_Core::getTable('ScheduleOfQuantityTrade')->find($request->getParameter('tid')));

        $pdo = $trade->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.schedule_of_quantity_trade_id AS relation_id, i.third_party_identifier,
            i.identifier_type, uom.id AS uom_id, uom.symbol AS uom_symbol, i.updated_at
            FROM " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.schedule_of_quantity_trade_id = " . $trade->id . " AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value, ifc.linked, ifc.has_build_up
            FROM " . ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName() . " ifc JOIN
            " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
            WHERE i.schedule_of_quantity_trade_id = " . $trade->id . " AND i.deleted_at IS NULL
            AND ifc.deleted_at IS NULL AND ifc.final_value <> 0");

        $stmt->execute();

        $itemFormulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formulatedColumns = array();

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

        $form = new BaseForm();

        foreach ( $items as $key => $item )
        {
            $items[$key]['type']               = (string) $item['type'];
            $items[$key]['uom_id']             = $item['uom_id'] > 0 ? (string) $item['uom_id'] : '-1';
            $items[$key]['editable_total']     = ( $item['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($item['id'], true) : 0;
            $items[$key]['non_editable_total'] = ( $item['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($item['id'], false) : 0;
            $items[$key]['_csrf_token']        = $form->getCSRFToken();
            $items[$key]['updated_at']         = date('d/m/Y H:i', strtotime($item['updated_at']));

            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value']        = 0;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value']              = '';
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference'] = false;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula']        = false;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked']             = false;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up']       = false;

            if ( array_key_exists($item['id'], $formulatedColumns) )
            {
                $itemFormulatedColumns = $formulatedColumns[$item['id']];

                foreach ( $itemFormulatedColumns as $formulatedColumn )
                {
                    $items[$key][$formulatedColumn['column_name'] . '-final_value']  = $formulatedColumn['final_value'];
                    $items[$key][$formulatedColumn['column_name'] . '-value']        = $formulatedColumn['value'];
                    $items[$key][$formulatedColumn['column_name'] . '-linked']       = $formulatedColumn['linked'];
                    $items[$key][$formulatedColumn['column_name'] . '-has_build_up'] = $formulatedColumn['has_build_up'];
                    $items[$key][$formulatedColumn['column_name'] . '-has_formula']  = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }

                unset( $formulatedColumns[$item['id']], $itemFormulatedColumns );
            }
        }

        array_push($items, array(
            'id'                                                                       => Constants::GRID_LAST_ROW,
            'description'                                                              => '',
            'type'                                                                     => (string) ScheduleOfQuantityItem::TYPE_WORK_ITEM,
            'uom_id'                                                                   => '-1',
            'uom_symbol'                                                               => '',
            'relation_id'                                                              => $trade->id,
            'identifier_type'                                                          => ScheduleOfQuantity::IDENTIFIER_TYPE_MANUAL,
            'updated_at'                                                               => '-',
            '_csrf_token'                                                              => $form->getCSRFToken(),
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value'        => 0,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value'              => '',
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference' => false,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula'        => false,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked'             => false,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up'       => false
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $trade = Doctrine_Core::getTable('ScheduleOfQuantityTrade')->find($request->getParameter('relation_id'))
        );

        $items              = array();
        $nextItem           = null;
        $fieldName          = null;
        $fieldValue         = null;
        $isFormulatedColumn = false;

        $con = ScheduleOfQuantityTable::getInstance()->getConnection();

        try
        {
            $con->beginTransaction();

            $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('prev_item_id')) : null;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                $fieldAttr = explode('-', $fieldName);
                $fieldName = count($fieldAttr) > 1 ? $fieldAttr[1] : $fieldAttr[0];

                $item = ScheduleOfQuantityItemTable::createItemFromLastRow($previousItem, $trade, $fieldName, $fieldValue);

                if ( $fieldName == ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY )
                {
                    $isFormulatedColumn = true;
                }
            }
            else
            {
                $this->forward404Unless($nextItem = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('before_id')));

                $trade = $nextItem->Trade;
                $item  = ScheduleOfQuantityItemTable::createItem($nextItem, $trade);
            }

            if ( $isFormulatedColumn && $fieldName )
            {
                $formulatedColumn              = new ScheduleOfQuantityItemFormulatedColumn();
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

            $data['id']                 = $item->id;
            $data['description']        = $item->description;
            $data['type']               = (string) $item->type;
            $data['uom_id']             = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $data['uom_symbol']         = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            $data['relation_id']        = $trade->id;
            $data['editable_total']     = 0;
            $data['non_editable_total'] = 0;
            $data['updated_at']         = date('d/m/Y H:i', strtotime($item->updated_at));
            $data['level']              = $item->level;
            $data['_csrf_token']        = $form->getCSRFToken();
            $data['identifier_type']    = ScheduleOfQuantity::IDENTIFIER_TYPE_MANUAL;

            $formulatedColumn                                                                 = $item->getFormulatedColumnByName(ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY);
            $finalValue                                                                       = $formulatedColumn ? $formulatedColumn->final_value : 0;
            $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value']        = $finalValue;
            $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value']              = $formulatedColumn ? $formulatedColumn->value : '';
            $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference'] = false;
            $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
            $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked']             = $formulatedColumn ? $formulatedColumn->linked : false;
            $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up']       = $formulatedColumn ? $formulatedColumn->has_build_up : false;

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'                                                                       => Constants::GRID_LAST_ROW,
                    'description'                                                              => '',
                    'type'                                                                     => (string) ScheduleOfQuantityItem::TYPE_WORK_ITEM,
                    'uom_id'                                                                   => '-1',
                    'uom_symbol'                                                               => '',
                    'relation_id'                                                              => $trade->id,
                    'identifier_type'                                                          => ScheduleOfQuantity::IDENTIFIER_TYPE_MANUAL,
                    'updated_at'                                                               => '-',
                    '_csrf_token'                                                              => $form->getCSRFToken(),
                    ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value'        => 0,
                    ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value'              => '',
                    ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference' => false,
                    ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula'        => false,
                    ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked'             => false,
                    ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up'       => false
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

    public function executeItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('id')));

        $rowData            = array();
        $affectedNodes      = array();
        $isFormulatedColumn = false;

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldAttr  = explode('-', $request->getParameter('attr_name'));
            $fieldName  = count($fieldAttr) > 1 ? $fieldAttr[1] : $fieldAttr[0];
            $fieldValue = trim($request->getParameter('val'));
            $fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;

            if ( $fieldName == ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY )
            {
                $formulatedColumn = ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getByRelationIdAndColumnName($item->id, $fieldName);

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->linked = false;

                $formulatedColumn->has_build_up = false;

                $formulatedColumn->save($con);

                $formulatedColumn->refresh();

                $isFormulatedColumn = true;
            }
            else
            {
                if ( $fieldName == 'type' or $fieldName == 'uom_id' )
                {
                    /*
                     * By changing type or uom, some of the values probably need to be zero out thus not only it will affects item's quantity but
                     * it probably will affects other items that are linked to it (if any). That's why we need to get the affected items
                     * from this operation and returns it so our frontend javascript can updates it.
                     */
                    $affectedNodes = $item->{'update' . sfInflector::camelize($fieldName)}($fieldValue);
                }
                else
                {
                    $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                }

                $rowData[$fieldName] = $item->{$fieldName};
            }

            $item->save($con);

            $con->commit();

            $item->refresh(true);

            $success = true;

            $errorMsg = null;

            if ( $isFormulatedColumn )
            {
                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                foreach ( $referencedNodes as $referencedNode )
                {
                    if ( $node = ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->find($referencedNode['node_from']) )
                    {
                        array_push($affectedNodes, array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value
                        ));
                    }
                }
            }

            $formulatedColumn                                                                    = $item->getFormulatedColumnByName(ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY);
            $finalValue                                                                          = $formulatedColumn ? $formulatedColumn->final_value : 0;
            $rowData[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value']        = $finalValue;
            $rowData[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value']              = $formulatedColumn ? $formulatedColumn->value : '';
            $rowData[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference'] = false;
            $rowData[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
            $rowData[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked']             = $formulatedColumn ? $formulatedColumn->linked : false;
            $rowData[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up']       = $formulatedColumn ? $formulatedColumn->has_build_up : false;

            $rowData['affected_nodes']     = $affectedNodes;
            $rowData['linked']             = false;
            $rowData['type']               = (string) $item->type;
            $rowData['uom_id']             = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol']         = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            $rowData['editable_total']     = ( $item->type != ScheduleOfQuantityItem::TYPE_HEADER ) ? $item->getBuildUpTotalByCanEditStatus(true) : 0;
            $rowData['non_editable_total'] = ( $item->type != ScheduleOfQuantityItem::TYPE_HEADER ) ? $item->getBuildUpTotalByCanEditStatus(false) : 0;
            $rowData['updated_at']         = date('d/m/Y H:i', strtotime($item->updated_at));
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('id'))
        );

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('i.id')
                ->from('ScheduleOfQuantityItem i')
                ->where('i.root_id = ?', $item->root_id)
                ->andWhere('i.schedule_of_quantity_trade_id = ?', $item->schedule_of_quantity_trade_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $item->lft, $item->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

            $affectedNodes = $item->delete($con);

            $con->commit();

            $success  = true;
            $errorMsg = null;
        } catch (Exception $e)
        {
            $con->rollback();

            $errorMsg      = $e->getMessage();
            $success       = false;
            $items         = array();
            $affectedNodes = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => $affectedNodes ));
    }

    public function executeItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('id'))
        );

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetItem = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find(intval($request->getParameter('target_id')));

        if ( !$targetItem )
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetItem->root_id == $item->root_id and $targetItem->lft >= $item->lft and $targetItem->rgt <= $item->rgt )
        {
            $results = array( 'success' => false, 'errorMsg' => "cannot move item into itself", 'data' => $data, 'c' => array() );

            return $this->renderJson($results);
        }

        switch ($request->getParameter('type'))
        {
            case 'cut':
                try
                {
                    $item->moveTo($targetItem, $lastPosition);

                    $children = DoctrineQuery::create()->select('i.id, i.level')
                        ->from('ScheduleOfQuantityItem i')
                        ->where('i.root_id = ?', $item->root_id)
                        ->andWhere('i.schedule_of_quantity_trade_id = ?', $item->schedule_of_quantity_trade_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                        ->addOrderBy('i.lft')
                        ->fetchArray();

                    $data['id']    = $item->id;
                    $data['level'] = $item->level;

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
                    $newItem = $item->copyTo($targetItem, $lastPosition);
                    $form    = new BaseForm();

                    $children = DoctrineQuery::create()->select('i.id, i.description, i.type, i.uom_id, uom.symbol, i.schedule_of_quantity_trade_id AS relation_id, i.level')
                        ->from('ScheduleOfQuantityItem i')
                        ->leftJoin('i.UnitOfMeasurement uom')
                        ->where('i.root_id = ?', $newItem->root_id)
                        ->andWhere('i.schedule_of_quantity_trade_id = ?', $newItem->schedule_of_quantity_trade_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $newItem->lft, $newItem->rgt ))
                        ->addOrderBy('i.lft')
                        ->fetchArray();

                    foreach ( $children as $key => $child )
                    {
                        $children[$key]['type']               = (string) $child['type'];
                        $children[$key]['uom_id']             = $child['uom_id'] > 0 ? (string) $child['uom_id'] : '-1';
                        $children[$key]['uom_symbol']         = $child['uom_id'] > 0 ? $child['UnitOfMeasurement']['symbol'] : '';
                        $children[$key]['editable_total']     = ( $child['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($child['id'], true) : 0;
                        $children[$key]['non_editable_total'] = ( $child['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($child['id'], false) : 0;
                        $children[$key]['_csrf_token']        = $form->getCSRFToken();

                        unset( $children[$key]['UnitOfMeasurement'] );

                        $formulatedColumn                                                                           = ScheduleOfQuantityItemTable::getFormulatedColumnByRelationIdAndColumnName($child['id'], ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY, Doctrine_Core::HYDRATE_ARRAY);
                        $finalValue                                                                                 = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                        $children[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value']        = $finalValue;
                        $children[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                        $children[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference'] = false;
                        $children[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                        $children[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked']             = $formulatedColumn ? $formulatedColumn['linked'] : false;
                        $children[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up']       = $formulatedColumn ? $formulatedColumn['has_build_up'] : false;
                    }

                    $data['id']                 = $newItem->id;
                    $data['description']        = $newItem->description;
                    $data['type']               = (string) $newItem->type;
                    $data['uom_id']             = $newItem->uom_id > 0 ? (string) $newItem->uom_id : '-1';
                    $data['uom_symbol']         = $newItem->uom_id > 0 ? $newItem->UnitOfMeasurement->symbol : '';
                    $data['relation_id']        = $newItem->schedule_of_quantity_trade_id;
                    $data['level']              = $newItem->level;
                    $data['editable_total']     = ( $data['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($data['id'], true) : 0;
                    $data['non_editable_total'] = ( $data['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($data['id'], false) : 0;
                    $data['_csrf_token']        = $form->getCSRFToken();
                    $data['identifier_type']    = ScheduleOfQuantity::IDENTIFIER_TYPE_MANUAL;
                    $data['updated_at']         = date('d/m/Y H:i', strtotime($newItem->updated_at));

                    $formulatedColumn                                                                 = ScheduleOfQuantityItemTable::getFormulatedColumnByRelationIdAndColumnName($newItem->id, ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY, Doctrine_Core::HYDRATE_ARRAY);
                    $finalValue                                                                       = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                    $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value']        = $finalValue;
                    $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                    $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference'] = false;
                    $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                    $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked']             = $formulatedColumn ? $formulatedColumn['linked'] : false;
                    $data[ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up']       = $formulatedColumn ? $formulatedColumn['has_build_up'] : false;

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

    public function executeItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('id'))
        );

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();
        try
        {
            if ( $item->indent() )
            {
                $data['id']    = $item->id;
                $data['level'] = $item->level;

                $children = DoctrineQuery::create()->select('i.id, i.level')
                    ->from('ScheduleOfQuantityItem i')
                    ->where('i.root_id = ?', $item->root_id)
                    ->andWhere('i.schedule_of_quantity_trade_id = ?', $item->schedule_of_quantity_trade_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->fetchArray();

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

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('id'))
        );

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();
        try
        {
            if ( $item->outdent() )
            {
                $data['id']    = $item->id;
                $data['level'] = $item->level;

                $children = DoctrineQuery::create()->select('i.id, i.level')
                    ->from('ScheduleOfQuantityItem i')
                    ->where('i.root_id = ?', $item->root_id)
                    ->andWhere('i.schedule_of_quantity_trade_id = ?', $item->schedule_of_quantity_trade_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->fetchArray();

                $success = true;
            }
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executeHasImportedItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('id'))
        );

        if ( $item->identifier_type == ScheduleOfQuantity::IDENTIFIER_TYPE_MANUAL )
        {
            $hasImportedItems = false;//manual items does not have imported build up items
        }
        else
        {
            $count = DoctrineQuery::create()->select('i.id')
                ->from('ScheduleOfQuantityBuildUpItem i')
                ->where('i.schedule_of_quantity_item_id = ?', $item->id)
                ->andWhere('i.can_edit = ?', false)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->count();

            $hasImportedItems = $count > 0 ? true : false;
        }

        return $this->renderText(json_encode($hasImportedItems));
    }

    public function executeGetBuildUpItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $scheduleOfQuantityItem = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('id')) and
            $request->hasParameter('t')
        );

        $canEditItems = $request->getParameter('t') == "m" ? true : false;

        $buildUpQuantityItems = DoctrineQuery::create()->select('i.id, i.description, i.sign, i.total, i.schedule_of_quantity_item_id AS relation_id, ifc.column_name, ifc.value, ifc.final_value')
            ->from('ScheduleOfQuantityBuildUpItem i')
            ->leftJoin('i.FormulatedColumns ifc')
            ->where('i.schedule_of_quantity_item_id = ?', $scheduleOfQuantityItem->id)
            ->andWhere('i.can_edit = ?', $canEditItems)
            ->addOrderBy('i.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        $formulatedColumnNames = ScheduleOfQuantityBuildUpItemTable::getFormulatedColumnNames($scheduleOfQuantityItem->UnitOfMeasurement);

        foreach ( $buildUpQuantityItems as $key => $buildUpQuantityItem )
        {
            $buildUpQuantityItems[$key]['sign']        = (string) $buildUpQuantityItem['sign'];
            $buildUpQuantityItems[$key]['sign_symbol'] = ScheduleOfQuantityBuildUpItemTable::getSignTextBySign($buildUpQuantityItem['sign']);
            $buildUpQuantityItems[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnNames as $constant )
            {
                $buildUpQuantityItems[$key][$constant . '-final_value']        = 0;
                $buildUpQuantityItems[$key][$constant . '-value']              = '';
                $buildUpQuantityItems[$key][$constant . '-has_cell_reference'] = false;
                $buildUpQuantityItems[$key][$constant . '-has_formula']        = false;
            }

            foreach ( $buildUpQuantityItem['FormulatedColumns'] as $formulatedColumn )
            {
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-final_value']        = $formulatedColumn['final_value'];
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-value']              = $formulatedColumn['value'];
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-has_cell_reference'] = false;
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            unset( $buildUpQuantityItem, $buildUpQuantityItems[$key]['FormulatedColumns'] );
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'sign'        => (string) ScheduleOfQuantityBuildUpItem::SIGN_POSITIVE,
            'sign_symbol' => ScheduleOfQuantityBuildUpItem::SIGN_POSITIVE_TEXT,
            'relation_id' => $scheduleOfQuantityItem->id,
            'total'       => 0,
            '_csrf_token' => $form->getCSRFToken()
        );

        foreach ( $formulatedColumnNames as $columnName )
        {
            $defaultLastRow[$columnName . '-final_value']        = 0;
            $defaultLastRow[$columnName . '-value']              = "";
            $defaultLastRow[$columnName . '-has_cell_reference'] = false;
            $defaultLastRow[$columnName . '-has_formula']        = false;
        }

        array_push($buildUpQuantityItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $buildUpQuantityItems
        ));
    }

    public function executeBuildUpItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $items = array();

        $item = new ScheduleOfQuantityBuildUpItem();

        $con = $item->getTable()->getConnection();

        $isFormulatedColumn = false;

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $this->forward404Unless($scheduleOfQuantityItem = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('relation_id')));

            $formulatedColumnNames = ScheduleOfQuantityBuildUpItemTable::getFormulatedColumnNames($scheduleOfQuantityItem->UnitOfMeasurement);

            $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ScheduleOfQuantityBuildUpItem')->find($request->getParameter('prev_item_id')) : null;

            $priority = $previousItem ? $previousItem->priority + 1 : 0;

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');

                if ( in_array($fieldName, $formulatedColumnNames) )
                {
                    $isFormulatedColumn = true;
                }
                else
                {
                    $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                }
            }
        }
        else
        {
            $this->forward404Unless($nextItem = Doctrine_Core::getTable('ScheduleOfQuantityBuildUpItem')->find($request->getParameter('before_id')));

            $scheduleOfQuantityItem = $nextItem->ScheduleOfQuantityItem;
            $formulatedColumnNames  = ScheduleOfQuantityBuildUpItemTable::getFormulatedColumnNames($scheduleOfQuantityItem->UnitOfMeasurement);

            $priority = $nextItem->priority;
        }

        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('ScheduleOfQuantityBuildUpItem')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('schedule_of_quantity_item_id = ?', $scheduleOfQuantityItem->id)
                ->andWhere('can_edit = ?', true)
                ->execute();

            $item->schedule_of_quantity_item_id = $scheduleOfQuantityItem->id;
            $item->can_edit                     = true;
            $item->priority                     = $priority;

            $item->save($con);

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new ScheduleOfQuantityBuildUpFormulatedColumn();
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
            $data['sign']        = (string) $item->sign;
            $data['sign_symbol'] = $item->getSignText();
            $data['total']       = $item->calculateTotal();
            $data['relation_id'] = $scheduleOfQuantityItem->id;
            $data['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnNames as $columnName )
            {
                $formulatedColumn                          = $item->getFormulatedColumnByName($columnName, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                                = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $data[$columnName . '-final_value']        = $finalValue;
                $data[$columnName . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $data[$columnName . '-has_cell_reference'] = false;
                $data[$columnName . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'sign'        => (string) ScheduleOfQuantityBuildUpItem::SIGN_POSITIVE,
                    'sign_symbol' => ScheduleOfQuantityBuildUpItem::SIGN_POSITIVE_TEXT,
                    'total'       => 0,
                    'relation_id' => $scheduleOfQuantityItem->id,
                    '_csrf_token' => $form->getCSRFToken()
                );

                foreach ( $formulatedColumnNames as $columnName )
                {
                    $defaultLastRow[$columnName . '-final_value']        = "";
                    $defaultLastRow[$columnName . '-value']              = "";
                    $defaultLastRow[$columnName . '-has_cell_reference'] = false;
                    $defaultLastRow[$columnName . '-has_formula']        = false;
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
            'success'        => $success,
            'items'          => $items,
            'errorMsg'       => $errorMsg,
            'total_build_up' => ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($scheduleOfQuantityItem->id, true)
        ));
    }

    public function executeBuildUpItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('ScheduleOfQuantityBuildUpItem')->find($request->getParameter('id')) and
            $item->can_edit
        );

        $rowData = array();
        $con     = $item->getTable()->getConnection();

        $scheduleOfQuantityItem = $item->ScheduleOfQuantityItem;

        $formulatedColumnNames = ScheduleOfQuantityBuildUpItemTable::getFormulatedColumnNames($scheduleOfQuantityItem->UnitOfMeasurement);

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $affectedNodes      = array();
            $isFormulatedColumn = false;

            if ( in_array($fieldName, $formulatedColumnNames) )
            {
                $formulatedColumnTable = Doctrine_Core::getTable('ScheduleOfQuantityBuildUpFormulatedColumn');

                $formulatedColumn = $formulatedColumnTable->getByRelationIdAndColumnName($item->id, $fieldName);

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->save($con);

                $formulatedColumn->refresh();

                $isFormulatedColumn = true;
            }
            else
            {
                $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                $item->save($con);
            }

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
                        array_push($affectedNodes, array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value,
                            'total'                     => $node->BuildUpQuantityItem->calculateTotal()
                        ));
                    }
                }
            }
            else
            {
                $rowData[$fieldName] = $item->$fieldName;
            }

            foreach ( $formulatedColumnNames as $columnName )
            {
                $formulatedColumn                             = $item->getFormulatedColumnByName($columnName, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                                   = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $rowData[$columnName . '-final_value']        = $finalValue;
                $rowData[$columnName . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $rowData[$columnName . '-has_cell_reference'] = false;
                $rowData[$columnName . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            $rowData['sign']           = (string) $item->sign;
            $rowData['sign_symbol']    = $item->getSignText();
            $rowData['total']          = $item->calculateTotal();
            $rowData['affected_nodes'] = $affectedNodes;
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'        => $success,
            'errorMsg'       => $errorMsg,
            'data'           => $rowData,
            'total_build_up' => ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($scheduleOfQuantityItem->id, true)
        ));
    }

    public function executeBuildUpItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $buildUpItem = Doctrine_Core::getTable('ScheduleOfQuantityBuildUpItem')->find($request->getParameter('id')) and
            $buildUpItem->can_edit
        );

        $scheduleOfQuantityItemId = $buildUpItem->schedule_of_quantity_item_id;

        try
        {
            $item['id']    = $buildUpItem->id;
            $affectedNodes = $buildUpItem->delete();

            ScheduleOfQuantityBuildUpItemTable::updateScheduleOfQuantityAmountById($scheduleOfQuantityItemId);

            $success  = true;
            $errorMsg = null;
        } catch (Exception $e)
        {
            $errorMsg      = $e->getMessage();
            $item          = array();
            $affectedNodes = array();
            $success       = false;
        }

        return $this->renderJson(array(
            'success'        => $success,
            'errorMsg'       => $errorMsg,
            'item'           => $item,
            'affected_nodes' => $affectedNodes,
            'total_build_up' => ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($scheduleOfQuantityItemId, true)
        ));
    }

    public function executeBuildUpItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $buildUpItem = Doctrine_Core::getTable('ScheduleOfQuantityBuildUpItem')->find($request->getParameter('id')) and
            $buildUpItem->can_edit
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetBuildUpItem = Doctrine_Core::getTable('ScheduleOfQuantityBuildUpItem')->find(intval($request->getParameter('target_id')));
        if ( !$targetBuildUpItem )
        {
            $this->forward404Unless($targetBuildUpItem = Doctrine_Core::getTable('ScheduleOfQuantityBuildUpItem')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetBuildUpItem->id == $buildUpItem->id )
        {
            $results = array( 'success' => false, 'errorMsg' => "cannot move item into itself", 'data' => $data, 'c' => array() );

            return $this->renderJson($results);
        }

        switch ($request->getParameter('type'))
        {
            case 'cut':
                try
                {
                    $buildUpItem->moveTo($targetBuildUpItem->priority, $lastPosition);

                    $data['id'] = $buildUpItem->id;

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
                    $formulatedColumnNames = ScheduleOfQuantityBuildUpItemTable::getFormulatedColumnNames($buildUpItem->ScheduleOfQuantityItem->UnitOfMeasurement);
                    $newBuildUpItem        = $buildUpItem->copyTo($targetBuildUpItem, $lastPosition);

                    $form = new BaseForm();

                    $data['id']             = $newBuildUpItem->id;
                    $data['description']    = $newBuildUpItem->description;
                    $data['sign']           = (string) $newBuildUpItem->sign;
                    $data['sign_symbol']    = $newBuildUpItem->getSigntext();
                    $data['total_build_up'] = ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($buildUpItem->schedule_of_quantity_item_id, true);
                    $data['relation_id']    = $newBuildUpItem->schedule_of_quantity_item_id;
                    $data['total']          = $newBuildUpItem->calculateTotal();
                    $data['_csrf_token']    = $form->getCSRFToken();

                    foreach ( $formulatedColumnNames as $constant )
                    {
                        $formulatedColumn                        = $newBuildUpItem->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
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

        return $this->renderJson(array(
            'success'        => $success,
            'errorMsg'       => $errorMsg,
            'data'           => $data,
            'total_build_up' => ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($buildUpItem->schedule_of_quantity_item_id, true)
        ));
    }

    public function executeLinkToBillItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('bid')) and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bcid')) and
            $request->hasParameter('type') and $request->hasParameter('ids')
        );

        try
        {
            switch ($request->getParameter('type'))
            {
                case BillBuildUpQuantityItem::QUANTITY_PER_UNIT_ORIGINAL:
                    $type      = BillBuildUpQuantityItem::QUANTITY_PER_UNIT_ORIGINAL;
                    $fieldName = 'quantity_per_unit';
                    break;
                case BillBuildUpQuantityItem::QUANTITY_PER_UNIT_REMEASUREMENT:
                    $type      = BillBuildUpQuantityItem::QUANTITY_PER_UNIT_REMEASUREMENT;
                    $fieldName = 'quantity_per_unit_remeasurement';
                    break;
                default:
                    throw new Exception("invalid quantity type");
            }

            $totalQuantity = $billItem->linkToScheduleOfQuantities($billColumnSetting, explode(',', $request->getParameter('ids')), $type);

            $billItem->updateBillItemTotalColumns();

            $item = array(
                'id'                         => $billItem->id,
                $fieldName . '-final_value'  => $totalQuantity,
                $fieldName . '-value'        => $totalQuantity,
                $fieldName . '-has_build_up' => true
            );

            $errorMsg = null;
            $success  = true;
        } catch (Exception $e)
        {
            $item     = array();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'item'     => $item
        ));
    }

}