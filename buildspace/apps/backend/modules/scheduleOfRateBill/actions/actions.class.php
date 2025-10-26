<?php

/**
 * scheduleOfRateBill actions.
 *
 * @package    buildspace
 * @subpackage scheduleOfRateBill
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class scheduleOfRateBillActions extends BaseActions
{
    public function executeBillForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if ( $structure = ProjectStructureTable::getInstance()->find($request->getParameter('id')) )
        {
            $parent             = $structure->node->getParent();
            $scheduleOfRateBill = $structure->ScheduleOfRateBill;
        }
        else
        {
            $parent             = ProjectStructureTable::getInstance()->find($request->getParameter('parent_id'));
            $scheduleOfRateBill = new ScheduleOfRateBill();
        }

        $form = new ScheduleOfRateBillForm($scheduleOfRateBill, array( 'parent' => $parent ));

        if(!$form->getObject()->unit_type)
        {
            $uomType = DoctrineQuery::create()
                ->select('u.id, u.name')
                ->from('UnitOfMeasurementType u')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();
        }
        else
        {
            $uomType = $form->getObject()->UnitOfMeasurementType->toArray();
        }

        return $this->renderJson(array(
            'schedule_of_rate_bill[title]'       => $form->getObject()->title,
            'schedule_of_rate_bill[description]' => $form->getObject()->description,
            'schedule_of_rate_bill[unit_type]'   => $uomType['id'],
            'unitTypeText'                       => $uomType['name'],
            'schedule_of_rate_bill[_csrf_token]' => $form->getCSRFToken()
        ));
    }

    public function executeBillUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if ( $structure = ProjectStructureTable::getInstance()->find($request->getParameter('id')) )
        {
            $parent             = $structure->node->getParent();
            $scheduleOfRateBill = $structure->ScheduleOfRateBill;
        }
        else
        {
            $parent             = ProjectStructureTable::getInstance()->find($request->getParameter('parent_id'));
            $scheduleOfRateBill = new ScheduleOfRateBill();
        }

        $form = new ScheduleOfRateBillForm($scheduleOfRateBill, array('parent' => $parent));

        if ( $this->isFormValid($request, $form) )
        {
            try
            {
                $scheduleOfRateBill = $form->save();

                // clone the global bill printout setting if still new
                if ( $form->isNew() )
                {
                    /*// get global default printing setting
                    $defaultPrintingSetting = SupplyOfMaterialLayoutSettingTable::getInstance()->find(1);
                    $defaultSetting         = $defaultPrintingSetting->toArray();
                    $billPhraseSetting      = $defaultPrintingSetting->getSOMBillPhrase()->toArray();
                    $headSettings           = $defaultPrintingSetting->getSOMBillHeadSettings()->toArray();

                    SupplyOfMaterialLayoutSettingTable::cloneExistingPrintingLayoutSettingsForBill($scheduleOfRateBill, $defaultSetting, $billPhraseSetting, $headSettings);
                    */
                    $scheduleOfRateBill->refresh();
                }

                $item = array(
                    'id'          => $scheduleOfRateBill->ProjectStructure->id,
                    'title'       => $scheduleOfRateBill->ProjectStructure->title,
                    'type'        => $scheduleOfRateBill->ProjectStructure->type,
                    'level'       => $scheduleOfRateBill->ProjectStructure->level,
                    '_csrf_token' => $form->getCSRFToken()
                );

                $parentId = $scheduleOfRateBill->ProjectStructure->level > 1 ? $scheduleOfRateBill->ProjectStructure->node->getParent()->id : null;
                $errors   = null;
                $success  = true;
            }
            catch (Exception $e)
            {
                $errors   = $e->getMessage();
                $item     = array();
                $parentId = null;
                $success  = false;
            }
        }
        else
        {
            $errors   = $form->getErrors();
            $item     = array();
            $parentId = null;
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'item' => $item, 'parent_id' => $parentId ));
    }

    /*** start element actions ***/
    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('id'))
        );

        $elements = DoctrineQuery::create()->select('e.id, e.description, e.note')
            ->from('ScheduleOfRateBillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $elements as $key => $element )
        {
            $elements[$key]['has_note']    = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            $elements[$key]['note']        = (string) $element['note'];
            $elements[$key]['relation_id'] = $bill->id;
            $elements[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($elements, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'has_note'    => false,
            'relation_id' => $bill->id,
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetUnits(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $options   = array();
        $values    = array();
        $structure = ProjectStructureTable::getInstance()->find($request->getParameter('billId'));

        array_push($values, '-1');
        array_push($options, '---');

        $query = DoctrineQuery::create()->select('u.id, u.symbol')
            ->from('UnitOfMeasurement u')
            ->where('u.display IS TRUE')
            ->addOrderBy('u.symbol ASC');

        if ( $structure )
        {
            $query->addWhere('u.type = ?', $structure->ScheduleOfRateBill->unit_type);
        }

        $query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

        $records = $query->execute();

        foreach ( $records as $record )
        {
            array_push($values, (string) $record['id']); //damn, dojo store handles ids in string format
            array_push($options, $record['symbol']);
        }

        unset( $records );

        return $this->renderJson(array(
            'values'  => $values,
            'options' => $options
        ));
    }

    public function executeElementAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $items = array();

        $element = new ScheduleOfRateBillElement();

        $con = $element->getTable()->getConnection();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $previousElement = $request->getParameter('prev_item_id') > 0 ? ScheduleOfRateBillElementTable::getInstance()->find($request->getParameter('prev_item_id')) : null;

            $priority = $previousElement ? $previousElement->priority + 1 : 0;
            $billId   = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');

                $element->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }
        }
        else
        {
            $this->forward404Unless($nextElement = ScheduleOfRateBillElementTable::getInstance()->find($request->getParameter('before_id')));

            $billId   = $nextElement->project_structure_id;
            $priority = $nextElement->priority;
        }

        try
        {
            $con->beginTransaction();

            $bill = ProjectStructureTable::getInstance()->find($billId);

            DoctrineQuery::create()
                ->update('ScheduleOfRateBillElement')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('project_structure_id = ?', $bill->id)
                ->execute();

            $element->project_structure_id = $bill->id;
            $element->priority             = $priority;

            $element->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $data = array();

            $form = new BaseForm();

            $data['id']          = $element->id;
            $data['description'] = $element->description;
            $data['has_note']    = ( $element->note != null && $element->note != '' ) ? true : false;
            $data['note']        = (string) $element->note;
            $data['total']       = 0;
            $data['relation_id'] = $billId;
            $data['_csrf_token'] = $form->getCSRFToken();

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'note'        => '',
                    'has_note'    => false,
                    'total'       => 0,
                    'relation_id' => $bill->id,
                    '_csrf_token' => $form->getCSRFToken()
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

    public function executeElementUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $element = ScheduleOfRateBillElementTable::getInstance()->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $element->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $element->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $element->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $rowData['description'] = $element->description;
            $rowData['has_note']    = ( $element->note != null && $element->note != '' ) ? true : false;
            $rowData['note']        = (string) $element->note;
            $rowData['total']       = 0;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeElementPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $element = ScheduleOfRateBillElementTable::getInstance()->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetElement = ScheduleOfRateBillElementTable::getInstance()->find(intval($request->getParameter('target_id')));

        if ( !$targetElement )
        {
            $this->forward404Unless($targetElement = ScheduleOfRateBillElementTable::getInstance()->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetElement->id == $element->id )
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
                    $element->moveTo($targetElement->priority, $lastPosition);

                    $data['id'] = $element->id;

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
                    $newElement = $element->copyTo($targetElement, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newElement->id;
                    $data['description'] = $newElement->description;
                    $data['note']        = $newElement->note;
                    $data['has_note']    = ( $newElement->note != null && $newElement->note != '' ) ? true : false;
                    $data['total']       = 0;
                    $data['relation_id'] = $newElement->project_structure_id;
                    $data['_csrf_token'] = $form->getCSRFToken();

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

    public function executeElementDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $element = ScheduleOfRateBillElementTable::getInstance()->find($request->getParameter('id'))
        );

        $errorMsg = null;
        try
        {
            $item['id'] = $element->id;

            $element->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg      = $e->getMessage();
            $item          = array();
            $success       = false;
        }

        // return items need to be in array since grid js expect an array of items for delete operation (delete from store)
        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => array( $item )));
    }

    /*** start item actions ***/
    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = ScheduleOfRateBillElementTable::getInstance()->find($request->getParameter('id'))
        );

        $pdo   = $element->getTable()->getConnection()->getDbh();
        $form  = new BaseForm();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.estimation_rate, i.contractor_rate,
            i.difference, uom.id AS uom_id, uom.symbol AS uom_symbol, i.note
            FROM " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.element_id = " . $element->id . " AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $items as $key => $item )
        {
            $items[$key]['type']        = (string)$item['type'];
            $items[$key]['uom_id']      = $item['uom_id'] > 0 ? (string) $item['uom_id'] : '-1';
            $items[$key]['relation_id'] = $element->id;
            $items[$key]['linked']      = false;
            $items[$key]['has_note']    = ( $item['note'] != null && $item['note'] != '' ) ? true : false;
            $items[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($items, array(
            'id'              => Constants::GRID_LAST_ROW,
            'description'     => '',
            'note'            => '',
            'has_note'        => false,
            'type'            => (string) ScheduleOfRateBillItem::TYPE_WORK_ITEM,
            'uom_id'          => '-1',
            'uom_symbol'      => '',
            'relation_id'     => $element->id,
            'level'           => 0,
            'estimation_rate' => '',
            'contractor_rate' => 0,
            'difference'      =>  0,
            '_csrf_token'     => $form->getCSRFToken()
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
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bill_id'))
        );

        $items = array();
        $con   = $bill->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $previousItem = $request->getParameter('prev_item_id') > 0 ? ScheduleOfRateBillItemTable::getInstance()->find($request->getParameter('prev_item_id')) : null;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $elementId = $request->getParameter('relation_id');

                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                $item = ScheduleOfRateBillItemTable::createItemFromLastRow($previousItem, $elementId, $fieldName, $fieldValue);
            }
            else
            {
                $this->forward404Unless($nextItem = ScheduleOfRateBillItemTable::getInstance()->find($request->getParameter('before_id')));

                $elementId = $nextItem->element_id;
                $item      = ScheduleOfRateBillItemTable::createItem($nextItem);
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $data = array();

            $form = new BaseForm();

            $item->refresh();

            $data['id']              = $item->id;
            $data['description']     = $item->description;
            $data['note']            = $item->note;
            $data['has_note']        = ( $item->note != null && $item->note != '' ) ? true : false;
            $data['type']            = (string) $item->type;
            $data['uom_id']          = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $data['uom_symbol']      = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            $data['relation_id']     = $elementId;
            $data['estimation_rate'] = $item->estimation_rate;
            $data['contractor_rate'] = $item->contractor_rate;
            $data['difference']      = $item->difference;
            $data['level']           = $item->level;
            $data['_csrf_token']     = $form->getCSRFToken();

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'note'        => '',
                    'has_note'    => false,
                    'type'        => (string) ScheduleOfRateBillItem::TYPE_WORK_ITEM,
                    'uom_id'      => '-1',
                    'uom_symbol'  => '',
                    'relation_id' => $elementId,
                    'level'       => 0,
                    'estimation_rate' => 0,
                    'contractor_rate' => 0,
                    'difference' =>  0,
                    '_csrf_token' => $form->getCSRFToken()
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

    public function executeItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bill_id')) and
            $item = ScheduleOfRateBillItemTable::getInstance()->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $numericFields = array('estimation_rate', 'contractor_rate');
            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? trim($request->getParameter('val')) : null;
            $fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;
            $fieldValue = ( in_array($fieldName, $numericFields) and !is_numeric($fieldValue) ) ? 0 : $fieldValue;

            switch($fieldName)
            {
                case 'type':
                    $item->updateType($fieldValue);
                    break;
                default:
                    $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }

            $item->save($con);

            $rowData[$fieldName] = $item->{$fieldName};

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item->refresh();

            $rowData['estimation_rate'] = $item->estimation_rate;
            $rowData['contractor_rate'] = $item->contractor_rate;
            $rowData['difference']      = $item->difference;
            $rowData['type']            = (string) $item->type;
            $rowData['uom_id']          = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol']      = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
        }
        catch (Exception $e)
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
            $item = ScheduleOfRateBillItemTable::getInstance()->find($request->getParameter('id'))
        );

        $items    = array();
        $errorMsg = null;

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('i.id')
                ->from('ScheduleOfRateBillItem i')
                ->where('i.root_id = ?', $item->root_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $item->lft, $item->rgt ))
                ->addOrderBy('i.lft')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            $item->delete($con);

            $con->commit();

            $success = true;
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items));
    }

    public function executeItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = ScheduleOfRateBillItemTable::getInstance()->find($request->getParameter('id'))
        );

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetItem = ScheduleOfRateBillItemTable::getInstance()->find(intval($request->getParameter('target_id')));

        if ( !$targetItem )
        {
            $this->forward404Unless($targetItem = ScheduleOfRateBillItemTable::getInstance()->find($request->getParameter('prev_item_id')));
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
                        ->from('ScheduleOfRateBillItem i')
                        ->where('i.root_id = ?', $item->root_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                        ->addOrderBy('i.lft')
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

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

                    $children = DoctrineQuery::create()->select('i.id, i.description, i.note, i.type, i.uom_id, uom.symbol, i.element_id,
                        i.estimation_rate, i.contractor_rate, i.difference, i.level')
                        ->from('ScheduleOfRateBillItem i')
                        ->leftJoin('i.UnitOfMeasurement uom')
                        ->where('i.root_id = ?', $newItem->root_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $newItem->lft, $newItem->rgt ))
                        ->addOrderBy('i.lft')
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

                    foreach ( $children as $key => $child )
                    {
                        $children[$key]['type']            = (string) $child['type'];
                        $children[$key]['uom_id']          = $child['uom_id'] > 0 ? (string) $child['uom_id'] : '-1';
                        $children[$key]['uom_symbol']      = $child['uom_id'] > 0 ? $child['UnitOfMeasurement']['symbol'] : '';
                        $children[$key]['relation_id']     = $child['element_id'];
                        $children[$key]['estimation_rate'] = $child['estimation_rate'];
                        $children[$key]['contractor_rate'] = $child['contractor_rate'];
                        $children[$key]['difference']      = $child['difference'];
                        $children[$key]['_csrf_token']     = $form->getCSRFToken();
                    }

                    $data['id']              = $newItem->id;
                    $data['description']     = $newItem->description;
                    $data['type']            = (string) $newItem->type;
                    $data['uom_id']          = $newItem->uom_id > 0 ? (string) $newItem->uom_id : '-1';
                    $data['uom_symbol']      = $newItem->uom_id > 0 ? $newItem->UnitOfMeasurement->symbol : '';
                    $data['estimation_rate'] = $newItem->estimation_rate;
                    $data['contractor_rate'] = $newItem->contractor_rate;
                    $data['difference']      = $newItem->difference;
                    $data['relation_id']     = $newItem->element_id;
                    $data['level']           = $newItem->level;
                    $data['has_note']        = ( $newItem->note != null && $newItem->note != '' ) ? true : false;
                    $data['note']            = (string) $newItem->note;
                    $data['_csrf_token']     = $form->getCSRFToken();

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

    public function executeItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = ScheduleOfRateBillItemTable::getInstance()->find($request->getParameter('id'))
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
                    ->from('ScheduleOfRateBillItem i')
                    ->where('i.root_id = ?', $item->root_id)
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

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = ScheduleOfRateBillItemTable::getInstance()->find($request->getParameter('id'))
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
                    ->from('ScheduleOfRateBillItem i')
                    ->where('i.root_id = ?', $item->root_id)
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

    public function executeGetBillPrintOutSettings(sfWebRequest $request)
    {
        // will revert to default setting's id if there is not ID available
        $id = $request->getParameter('id', 1);

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $settings = ScheduleOfRateBillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($id)
        );

        return $this->renderJson($settings);
    }

    public function executeSaveBillPrintOutSettings(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        // check the request and see whether it is a post request or not
        // if not then redirect to 404 page
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $heads          = array();
        $printSettingId = $request->getParameter('sorBillLayoutSettingId');
        $contents       = ( is_array($request->getParameter('content')) ) ? $request->getParameter('content') : json_decode($request->getParameter('content'),
            true);
        $type           = $request->getParameter('type');

        // find the project layout setting first
        $masterSetting = ScheduleOfRateBillLayoutSettingTable::getInstance()->find($printSettingId);

        // posted fields that will be translated into fields name inside the database
        switch ($type)
        {
            case 'headStyling':
                break;

            case 'fontNumber':
                $setting = $masterSetting;
                $form    = new ScheduleOfRateBillLayoutSettingForm();

                $fields = array(
                    'fontTypeName'    => 'font',
                    'fontSize'        => 'size',
                    'amtCommaRemove'  => 'comma_total',
                    'rateCommaRemove' => 'comma_rate',
                );
                break;

            case 'pageFormat':
                $setting = $masterSetting;
                $form    = new ScheduleOfRateBillLayoutSettingForm();

                $fields = array(
                    'priceFormat'                => 'priceFormat',
                    'printElementInGrid'         => 'print_element_grid',
                    'printElementInGridOnce'     => 'print_element_grid_once',
                    'printContdEndDesc'          => 'add_cont',
                    'includeIandO'               => 'includeIAndOForBillRef',
                    'contdPrefix'                => 'contd',
                    'pageNoPrefix'               => 'page_no_prefix',
                    'alignElementTitleToTheLeft' => 'align_element_to_left',
                );
                break;

            case 'summaryPhrases':
                $setting = $masterSetting->getScheduleOfRateBillLayoutPhrase();
                $form    = new ScheduleOfRateBillLayoutPhraseSettingForm();

                $fields = array(
                    'toCollection'           => 'to_collection',
                    'currencyPrefix'         => 'currency',
                    'collectionInGridPrefix' => 'collection_in_grid',
                );
                break;

            case 'headerFooter':
                $setting = $masterSetting->getScheduleOfRateBillLayoutPhrase();
                $form    = new ScheduleOfRateBillLayoutPhraseSettingForm();

                $fields = array(
                    'eleHeadBold'      => 'element_header_bold',
                    'eleHeadUnderline' => 'element_header_underline',
                    'eleHeadItalic'    => 'element_header_italic',
                    'topLeftRow1'      => 'element_note_top_left_row1',
                    'topLeftRow2'      => 'element_note_top_left_row2',
                    'topRightRow1'     => 'element_note_top_right_row1',
                );
                break;
        }

        // insertion method will be based on which type of data will be entered
        // will be separate to normal insertion and dynamic insertion
        if ($type !== 'headStyling' AND $type !== 'reserveWords')
        {
            foreach ($fields as $key => $field)
            {
                $value             = ( array_key_exists($key, $contents) ) ? $contents[$key] : false;
                $setting->{$field} = ( empty( $value ) ) ? false : $value;

                // store the database field name and value to be validated later
                $validateData[$field] = $value;
            }

            if ($this->isFormCorrect($validateData, $form))
            {
                try
                {
                    $setting->save();
                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                    $success  = false;
                }
            }
            else
            {
                $errorMsg = $form->getErrors();
                $success  = false;
            }
        }
        elseif ($type === 'headStyling' AND !empty ( $contents ))
        {
            $con = $masterSetting->getTable()->getConnection();

            try
            {
                $con->beginTransaction();

                foreach ($contents['id'] as $key => $id)
                {
                    // search existing id, if got then update only
                    // else, insert new record for the time being
                    // will be returning the ID as well for javascript
                    // to post the correct ID when the submit button is
                    // pressed again
                    $headSetting            = ScheduleOfRateBillLayoutHeadSettingTable::getInstance()->find($id);
                    $headSetting            = $headSetting ? $headSetting : new ScheduleOfRateBillLayoutHeadSetting();
                    $headSetting->head      = ( isset ( $contents['head'][$key] ) ) ? $contents['head'][$key] : null;
                    $headSetting->bold      = ( isset ( $contents['bold'][$key] ) ) ? true : false;
                    $headSetting->italic    = ( isset ( $contents['italic'][$key] ) ) ? true : false;
                    $headSetting->underline = ( isset ( $contents['underline'][$key] ) ) ? true : false;

                    $headSetting->save($con);

                    $heads[$headSetting->head] = $headSetting->id;

                    $headSetting->free();
                }

                $con->commit();
                $success  = true;
                $errorMsg = null;
            }
            catch (Exception $e)
            {
                $con->rollback();
                $errorMsg = $e->getMessage();
                $success  = false;
            }
        }

        $data = array( 'heads' => $heads, 'success' => $success, 'error' => $errorMsg );

        return $this->renderJson($data);
    }

    public function executeExportExcelByElement(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $bill->type == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL and
            strlen($request->getParameter('filename')) > 0 and
            strlen($request->getParameter('eids')) > 0
        );

        $elementIds = explode(',', $request->getParameter('eids'));

        //Initiate sfBillExport
        $sfBillExport = new sfScheduleOfRateBillExportExcel($bill);

        //process
        $sfBillExport->process($elementIds, null);

        $errorMsg = null;

        try
        {
            $tmpFile = $sfBillExport->write('Excel2007');

            $fileSize     = filesize($tmpFile);
            $fileContents = file_get_contents($tmpFile);
            $mimeType     = Utilities::mimeContentType($tmpFile);

            unlink($tmpFile);

            $this->getResponse()->clearHttpHeaders();
            $this->getResponse()->setStatusCode(200);
            $this->getResponse()->setContentType($mimeType);
            $this->getResponse()->setHttpHeader(
                "Content-Disposition",
                "attachment; filename*=UTF-8''" . rawurlencode($request->getParameter('filename')) . ".xlsx"
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
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'errorMsg' => $errorMsg, 'success' => $success ));
    }

    public function executeImportBuildSpaceExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $errorMsg       = null;

        $success    = true;
        $newName    = null;
        $ext        = null;
        $pathToFile = null;

        foreach ($request->getFiles() as $file)
        {
            if (is_readable($file['tmp_name']))
            {
                // Later to do some checking Here FileType ETC.
                $newName    = date('dmY_H_i_s');
                $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile = $tempUploadPath . $newName . '.' . $ext;

                //Move Uploaded Files to temp folder
                move_uploaded_file($file['tmp_name'], $pathToFile);
            }
            else
            {
                $success = false;
            }
        }

        if ($success && $newName && $ext && $pathToFile)
        {
            try
            {
                $allowed = array( 'xls', 'xlsx', 'XLS', 'XLSX' );

                if (!in_array($ext, $allowed))
                {
                    throw new Exception('Invalid file type');
                }

                $sfImport = new sfImportScheduleOfRateBillExcelBuildspace($pathToFile);

                $sfImport->process();

                $data = $sfImport->getPreviewFormatData();

                $returnData = array(
                    'filename'  => $newName,
                    'extension' => $ext,
                    'elements'  => array(),
                );

                foreach ($data['elements'] as $idx => $item)
                {
                    $returnData['elements'][$idx] = $item['info'];
                    $returnData['items'][$idx]    = $item['items'];
                }

                $success = true;
            } catch (Exception $e)
            {
                $errorMsg = $e->getMessage();
                $success  = false;
            }
        }

        $returnData['success']  = $success;
        $returnData['errorMsg'] = $errorMsg;

        return $this->renderJson($returnData);
    }

    public function executeSaveImportedBuildSpaceExcel(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bill_id'))
        );

        sfConfig::set('sf_web_debug', false);

        set_time_limit(0);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $filename       = $request->getParameter('filename');
        $pathToFile     = $tempUploadPath . $filename;

        $errorMsg = null;

        //explode Element to imports
        $elementIds = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
        $withRate   = ( $request->getParameter('with_rate') == 'true' ) ? true : false;
        $asNewBill  = ( $request->getParameter('as_new') == 'true' ) ? true : false;

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        try
        {
            if (!is_readable($pathToFile))
            {
                throw new Exception('Uploaded file ' . $filename . ' is unreadable');
            }

            $con->beginTransaction();

            $sfImport = new sfImportScheduleOfRateBillExcelBuildspace($pathToFile);

            $sfImport->process();

            if ($asNewBill)
            {
                $sfImport->saveAsNewBill($bill, $elementIds, $withRate, $con);
            }
            else
            {
                $sfImport->saveIntoBill($bill, $elementIds, $withRate, $con);
            }

            $con->commit();

            $success = true;

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

    public function executePreviewImportedFile(sfWebRequest $request)
    {
        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid'))
        );

        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $success        = null;
        $errorMsg       = null;
        $columns        = array();

        foreach ($request->getFiles() as $file)
        {
            if (is_readable($file['tmp_name']))
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
            $sfImport = new sfBuildspaceExcelParser($newName, $ext, $tempUploadPath, true, false);

            $data = $sfImport->processPreviewData();

            $colData = $sfImport->colSlugArray;

            $returnData = array(
                'fileName'    => $sfImport->filename,
                'extension'   => $sfImport->extension,
                'excelType'   => $sfImport->excelType,
                'preview'     => true,
                'previewData' => $data,
                'colData'     => $colData
            );

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = ( $sfImport->error ) ? false : $success;
        $returnData['errorMsg'] = ( $sfImport->error ) ? $sfImport->errorMsg : $errorMsg;
        $returnData['columns']  = $columns;

        return $this->renderJson($returnData);
    }

    public function executeImportExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bill_id')) and
            ( $request->getParameter('filename') )
        );

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $errorMsg       = null;
        $filename       = $request->getParameter('filename');
        $extension      = $request->getParameter('extension');
        $columns        = array();

        try
        {
            $sfImport = new sfImportExcelNormal($filename, $extension, $tempUploadPath, true);

            //Set Col Item Value Based on Selection
            $sfImport->colDescriptionFrom = $request->getParameter('colDescriptionFrom');
            $sfImport->colDescriptionTo   = $request->getParameter('colDescriptionTo');
            $sfImport->colUnit            = $request->getParameter('colUnit');
            $sfImport->colRate            = null;
            $sfImport->colQty             = null;

            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $returnData = array(
                'filename'   => $sfImport->sfExportXML->filename,
                'uploadPath' => $sfImport->sfExportXML->uploadPath,
                'extension'  => $sfImport->sfExportXML->extension,
                'excelType'  => $sfImport->excelType,
                'elements'   => array()
            );

            foreach ($data as $element)
            {
                if (empty( $element['_child'] ))
                {
                    continue;
                }

                array_push($returnData['elements'], array(
                    'id'          => $element['id'],
                    'description' => $element['description'],
                    'count'       => $sfImport->elementItemCount[$element['id']],
                    'error'       => $sfImport->elementErrorCount[$element['id']]
                ));

                $items                               = $element['_child'];
                $returnData['items'][$element['id']] = array();

                foreach ($items as $item)
                {
                    array_push($returnData['items'][$element['id']], $item);
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
        $returnData['columns']  = $columns;

        return $this->renderJson($returnData);
    }

    public function executeSaveImportedExcel(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bill_id'))
        );

        sfConfig::set('sf_web_debug', false);

        set_time_limit(0);

        $errorMsg = null;

        //explode Element to imports
        $elementIds = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));

        //get XML Temporary File Information
        $filename   = $request->getParameter('filename');
        $uploadPath = $request->getParameter('uploadPath');

        //Initiate xmlParser
        $xmlParser = new sfBuildspaceXMLParser($filename, $uploadPath, null, true);

        //read xmlParser
        $xmlParser->read();

        //Get XML Processed Data
        $loadedXML    = $xmlParser->getProcessedData();
        $billElements = $loadedXML->ELEMENTS;
        $billItems    = $loadedXML->ITEMS;

        //get Last Priority for current Element
        //Get Current User Information
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');

        //Get First Bill Item Type
        $priority = ScheduleOfRateBillElementTable::getMaxPriorityByBillId($bill->id) + 1;

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        try
        {
            $con->beginTransaction();

            $stmt = new sfImportExcelStatementGenerator();

            $stmt->createInsert(
                ScheduleOfRateBillElementTable::getInstance()->getTableName(),
                array(
                    'description',
                    'project_structure_id',
                    'priority',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by'
                )
            );

            foreach ($billElements->children() as $importedElement)
            {
                $elementId = (int) $importedElement->id;

                $description = html_entity_decode((string) $importedElement->description);

                if (in_array($elementId, $elementIds))
                {
                    $stmt->addRecord(
                        array( $description, $bill->id, $priority, 'NOW()', 'NOW()', $userId, $userId ),
                        $elementId
                    );

                    $priority ++;
                }

                unset( $importedElement );
            }

            unset( $billElements );

            $stmt->save();

            $importedElementToElementIds = $stmt->returningIds;

            $importedItemToItemIds     = array();
            $rootOriginalIdsToPriority = array();
            $originalItemsToSave       = array();
            $originalItemIdsToRootId   = array();
            $currentPriority           = - 1;
            $currentElementId          = null;

            // will get existing unit first
            $unitGenerator = new ScheduleOfQuantityUnitGetter($con);

            $availableUnits = $unitGenerator->getAvailableUnitOfMeasurements();

            //Process Root Items
            foreach ($billItems->children() as $importedItem)
            {
                $asRoot = null;

                if (in_array($importedItem->elementId, $elementIds))
                {
                    $elementId   = $importedElementToElementIds[(int) $importedItem->elementId];
                    $description = html_entity_decode((string) $importedItem->description);

                    if ( !isset( $importedItem->new_symbol ) or strlen($importedItem->new_symbol) > 10)//any char more than 10 chars will be considered as non uom symbol
                    {
                        $uomId = ( (int) $importedItem->uom_id > 0 ) ? (int) $importedItem->uom_id : null;
                    }
                    else
                    {
                        if (!isset( $availableUnits[strtolower($importedItem->new_symbol)] ))
                        {
                            // we will insert the new uom symbol
                            $availableUnits = $unitGenerator->insertNewUnitOfMeasurementWithoutDimension($availableUnits,
                                $importedItem->new_symbol);
                        }

                        $uomId = $availableUnits[strtolower($importedItem->new_symbol)];
                    }

                    $type           = (int) $importedItem->type;
                    $level          = (int) $importedItem->level;
                    $originalItemId = (int) $importedItem->id;

                    if ((int) $importedItem->level == 0)
                    {
                        if ($elementId != $currentElementId)
                        {
                            $currentPriority  = 0;
                            $currentElementId = $elementId;
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

                    if ($asRoot)
                    {
                        $stmt->createInsert(
                            ScheduleOfRateBillItemTable::getInstance()->getTableName(),
                            array(
                                'element_id',
                                'description',
                                'type',
                                'uom_id',
                                'level',
                                'root_id',
                                'lft',
                                'rgt',
                                'priority',
                                'created_at',
                                'updated_at',
                                'created_by',
                                'updated_by',
                            )
                        );

                        $stmt->addRecord(array(
                            $elementId,
                            trim($description),
                            $type,
                            $uomId,
                            $level,
                            $rootId,
                            1,
                            2,
                            $priority,
                            'NOW()',
                            'NOW()',
                            $userId,
                            $userId,
                        ));

                        $stmt->save();

                        $returningId = $stmt->returningIds[0];

                        $stmt->setAsRoot(false, $returningId);

                        $importedItemToItemIds[(string) $importedItem->id] = $itemId = $returningId;
                    }
                    else
                    {
                        $originalItemsToSave[$originalItemId] = array(
                            $elementId,
                            trim($description),
                            $type,
                            $uomId,
                            $level,
                            $originalRootId,
                            1,
                            2,
                            $priority,
                            'NOW()',
                            'NOW()',
                            $userId,
                            $userId,
                        );
                    }
                }

                unset( $importedItem );
            }

            unset( $billItems );

            $stmt->createInsert(
                ScheduleOfRateBillItemTable::getInstance()->getTableName(),
                array(
                    'element_id',
                    'description',
                    'type',
                    'uom_id',
                    'level',
                    'root_id',
                    'lft',
                    'rgt',
                    'priority',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by',
                )
            );

            $originalRootIdToItemIds = array();

            function checkRootId(&$originalItemIdsToRootId, $itemRootId)
            {
                if (array_key_exists($itemRootId, $originalItemIdsToRootId))
                {
                    $originalRootId = $originalItemIdsToRootId[$itemRootId];

                    return $originalRootId = checkRootId($originalItemIdsToRootId, $originalRootId);
                }

                return $itemRootId;
            }

            if (count($originalItemsToSave))
            {
                $rootIdKey   = 5;
                $priorityKey = 8;

                foreach ($originalItemsToSave as $originalItemId => $item)
                {
                    $itemRootId = $item[$rootIdKey];

                    $originalRootIdToItemIds[$itemRootId][] = $originalItemId;

                    $originalRootId = checkRootId($originalItemIdsToRootId, $itemRootId);

                    $rootId   = $importedItemToItemIds[$originalRootId];
                    $priority = $rootOriginalIdsToPriority[$originalRootId];

                    $item[$rootIdKey]   = $rootId;
                    $item[$priorityKey] = $priority;

                    $stmt->addRecord($item, $originalItemId);

                    unset( $item );
                }

                $stmt->save();

                $importedItemToItemIds = $importedItemToItemIds + $stmt->returningIds;
            }

            $rootIdToItemIds = array();

            foreach ($originalRootIdToItemIds as $rootId => $itemIds)
            {
                $newRootId = $importedItemToItemIds[$rootId];

                foreach ($itemIds as $key => $itemId)
                {
                    $rootIdToItemIds[$newRootId][$key] = $importedItemToItemIds[$itemId];
                }

                unset( $itemIds );
            }

            unset( $originalRootIdToItemIds );

            /* Experimental */
            //Rebuilding Back After Tree Insert
            $stmt->rebuildItemTreeStructureByElementIds('ScheduleOfRateBillItem',
                ScheduleOfRateBillItemTable::getInstance()->getTableName(),
                $importedElementToElementIds, $rootIdToItemIds);

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
            'errorMsg' => $errorMsg
        ));
    }

    // simple method to validate forms
    private function isFormCorrect($data, sfForm $form)
    {
        $form->bind($data);

        return $form->isValid() ? true : false;
    }

    public function executePrintBill(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id')
            ->from('ScheduleOfRateBillElement e')
            ->where('e.project_structure_id = ?', $projectStructure->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $bqPrintOutGenerator = new sfBuildspaceScheduleOfRateBillPrintAll($request, $projectStructure, $elements);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        try
        {
            $bqPrintOutGenerator->generateFullPrintoutPages();
        }
        catch(PageGeneratorException $e)
        {
            $data = $e->getData();
            $e = new PageGeneratorException($e->getMessage(), $data['data']);

            return $this->pageGeneratorExceptionView($e, $data['bqPageGenerator']);
        }

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintContractorsRate(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid')) and
            $tenderCompany = TenderCompanyTable::getInstance()->find($request->getParameter('tcid'))
        );

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id')
            ->from('ScheduleOfRateBillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $bqPrintOutGenerator = new sfBuildSpaceScheduleOfRateBillContractorPrintAll($request, $bill, $elements, $tenderCompany);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        try
        {
            $bqPrintOutGenerator->generateFullPrintoutPages();
        }
        catch(PageGeneratorException $e)
        {
            $data = $e->getData();
            $e = new PageGeneratorException($e->getMessage(), $data['data']);

            return $this->pageGeneratorExceptionView($e, $data['bqPageGenerator']);
        }
        
        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    private function getPrintBOQPageLayoutSettings($bqPageGenerator)
    {
        $orientation = $bqPageGenerator->getOrientation();

        // for portrait printout
        $marginTop   = 8;
        $marginLeft  = 24;
        $marginRight = 4;

        // for landscape printout
        if ($orientation == sfBuildspaceBQMasterFunction::ORIENTATION_LANDSCAPE)
        {
            $marginLeft  = 12;
            $marginRight = 12;
        }

        return array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $marginTop,
            'margin-right'   => $marginRight,
            'margin-bottom'  => 3,
            'margin-left'    => $marginLeft,
            'page-size'      => 'A4',
            'orientation'    => $orientation,
        );
    }

    protected function pageGeneratorExceptionView(PageGeneratorException $e, sfBuildspaceScheduleOfRateBillPageGenerator $bqPageGenerator)
    {
        $data = $e->getData();

        $this->errorMessage  = $e->getMessage();
        $this->stylesheet    = $this->getBQStyling();
        $this->layoutStyling = $bqPageGenerator->getLayoutStyling();
        $this->pageNumber    = $data['page_number'];
        $this->pageItems     = $data['page_items'];
        $this->billItem      = ScheduleOfRateBillItemTable::getInstance()->find($data['id']);
        $this->occupiedRows  = $data['occupied_rows'];
        $this->maxRows       = $data['max_rows'];

        $pdo     = $this->billItem->getTable()->getConnection()->getDbh();
        $element = $this->billItem->Element;

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.estimation_rate, i.contractor_rate,
            i.difference, uom.id AS uom_id, uom.symbol AS uom_symbol, i.note
            FROM " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.element_id = " . $element->id . " AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $key = array_search($this->billItem->id, array_column($billItems, 'id'));

        $this->rowIdxInBillManager = $key+1;

        $this->setTemplate('pageGeneratorException');

        return sfView::SUCCESS;
    }
}
