<?php

/**
 * supplyOfMaterial actions.
 *
 * @package    buildspace
 * @subpackage supplyOfMaterial
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class supplyOfMaterialActions extends BaseActions
{

    public function executeBillForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if ( $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) )
        {
            $parent           = $structure->node->getParent();
            $supplyOfMaterial = $structure->SupplyOfMaterial;
        }
        else
        {
            $parent           = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('parent_id'));
            $supplyOfMaterial = new SupplyOfMaterial();
        }

        $form = new SupplyOfMaterialForm($supplyOfMaterial, array( 'parent' => $parent ));

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
            'supply_of_material[title]'       => $form->getObject()->title,
            'supply_of_material[description]' => $form->getObject()->description,
            'supply_of_material[unit_type]'   => $uomType['id'],
            'unitTypeText'                    => $uomType['name'],
            'supply_of_material[_csrf_token]' => $form->getCSRFToken()
        ));
    }

    public function executeBillUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if ( $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) )
        {
            $parent           = $structure->node->getParent();
            $supplyOfMaterial = $structure->SupplyOfMaterial;
        }
        else
        {
            $parent           = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('parent_id'));
            $supplyOfMaterial = new SupplyOfMaterial();
        }

        $form = new SupplyOfMaterialForm($supplyOfMaterial, array('parent' => $parent));

        if ( $this->isFormValid($request, $form) )
        {
            try
            {
                $supplyOfMaterial = $form->save();

                // clone the global bill printout setting if still new
                if ( $form->isNew() )
                {
                    // get global default printing setting
                    $defaultPrintingSetting = SupplyOfMaterialLayoutSettingTable::getInstance()->find(1);
                    $defaultSetting         = $defaultPrintingSetting->toArray();
                    $billPhraseSetting      = $defaultPrintingSetting->getSOMBillPhrase()->toArray();
                    $headSettings           = $defaultPrintingSetting->getSOMBillHeadSettings()->toArray();

                    SupplyOfMaterialLayoutSettingTable::cloneExistingPrintingLayoutSettingsForBill($supplyOfMaterial, $defaultSetting, $billPhraseSetting, $headSettings);

                    $supplyOfMaterial->refresh();
                }

                $item = array(
                    'id'          => $supplyOfMaterial->ProjectStructure->id,
                    'title'       => $supplyOfMaterial->ProjectStructure->title,
                    'type'        => $supplyOfMaterial->ProjectStructure->type,
                    'level'       => $supplyOfMaterial->ProjectStructure->level,
                    '_csrf_token' => $form->getCSRFToken()
                );

                $parentId = $supplyOfMaterial->ProjectStructure->level > 1 ? $supplyOfMaterial->ProjectStructure->node->getParent()->id : null;
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
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $elements = DoctrineQuery::create()->select('e.id, e.description, e.note')
            ->from('SupplyOfMaterialElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form                      = new BaseForm();

        foreach ( $elements as $key => $element )
        {
            $elements[$key]['has_note']    = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            $elements[$key]['note']        = (string) $element['note'];
            $elements[$key]['total']       = SupplyOfMaterialElementTable::getTotalRateByElementId($element['id']);
            $elements[$key]['relation_id'] = $bill->id;
            $elements[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($elements, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'has_note'    => false,
            'total'       => 0,
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
        $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId'));

        array_push($values, '-1');
        array_push($options, '---');

        $query = DoctrineQuery::create()->select('u.id, u.symbol')
            ->from('UnitOfMeasurement u')
            ->where('u.display IS TRUE')
            ->addOrderBy('u.symbol ASC');

        if ( $structure )
        {
            $query->addWhere('u.type = ?', $structure->SupplyOfMaterial->unit_type);
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

        $element = new SupplyOfMaterialElement();

        $con = $element->getTable()->getConnection();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $previousElement = $request->getParameter('prev_item_id') > 0 ? SupplyOfMaterialElementTable::getInstance()->find($request->getParameter('prev_item_id')) : null;

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
            $this->forward404Unless($nextElement = SupplyOfMaterialElementTable::getInstance()->find($request->getParameter('before_id')));

            $billId   = $nextElement->project_structure_id;
            $priority = $nextElement->priority;
        }

        try
        {
            $con->beginTransaction();

            $bill = Doctrine_Core::getTable('ProjectStructure')->find($billId);

            DoctrineQuery::create()
                ->update('SupplyOfMaterialElement')
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
            $element = Doctrine_Core::getTable('SupplyOfMaterialElement')->find($request->getParameter('id'))
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
            $element = Doctrine_Core::getTable('SupplyOfMaterialElement')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetElement = Doctrine_Core::getTable('SupplyOfMaterialElement')->find(intval($request->getParameter('target_id')));

        if ( !$targetElement )
        {
            $this->forward404Unless($targetElement = Doctrine_Core::getTable('SupplyOfMaterialElement')->find($request->getParameter('prev_item_id')));
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
            $element = Doctrine_Core::getTable('SupplyOfMaterialElement')->find($request->getParameter('id'))
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
            $element = Doctrine_Core::getTable('SupplyOfMaterialElement')->find($request->getParameter('id'))
        );

        $pdo   = $element->getTable()->getConnection()->getDbh();
        $form  = new BaseForm();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.supply_rate, i.contractor_supply_rate,
            i.estimated_qty, i.percentage_of_wastage, i.difference, i.amount, uom.id AS uom_id, uom.symbol AS uom_symbol, i.note
            FROM " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " i
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
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'note'        => '',
            'has_note'    => false,
            'type'        => (string) SupplyOfMaterialItem::TYPE_WORK_ITEM,
            'uom_id'      => '-1',
            'uom_symbol'  => '',
            'relation_id' => $element->id,
            'level'       => 0,
            'supply_rate' => '',
            'contractor_supply_rate' => 0,
            'estimated_qty' => 0,
            'percentage_of_wastage' => 0,
            'difference' =>  0,
            'amount' => 0,
            '_csrf_token' => $form->getCSRFToken()
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
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $items = array();
        $con   = Doctrine_Core::getTable('SupplyOfMaterialItem')->getConnection();

        try
        {
            $con->beginTransaction();

            $previousItem    = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('SupplyOfMaterialItem')->find($request->getParameter('prev_item_id')) : null;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $elementId = $request->getParameter('relation_id');

                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                $item = SupplyOfMaterialItemTable::createItemFromLastRow($previousItem, $elementId, $fieldName, $fieldValue);
            }
            else
            {
                $this->forward404Unless($nextItem = Doctrine_Core::getTable('SupplyOfMaterialItem')->find($request->getParameter('before_id')));

                $elementId = $nextItem->element_id;
                $item      = SupplyOfMaterialItemTable::createItem($nextItem);
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $data = array();

            $form = new BaseForm();

            $item->refresh();

            $data['id']                     = $item->id;
            $data['description']            = $item->description;
            $data['note']                   = $item->note;
            $data['has_note']               = ( $item->note != null && $item->note != '' ) ? true : false;
            $data['type']                   = (string) $item->type;
            $data['uom_id']                 = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $data['uom_symbol']             = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            $data['relation_id']            = $elementId;
            $data['supply_rate']            = $item->supply_rate;
            $data['contractor_supply_rate'] = $item->contractor_supply_rate;
            $data['estimated_qty']          = $item->estimated_qty;
            $data['percentage_of_wastage']  = $item->percentage_of_wastage;
            $data['difference']             = $item->difference;
            $data['amount']                 = $item->amount;
            $data['level']                  = $item->level;
            $data['_csrf_token']            = $form->getCSRFToken();

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'note'        => '',
                    'has_note'    => false,
                    'type'        => (string) SupplyOfMaterialItem::TYPE_WORK_ITEM,
                    'uom_id'      => '-1',
                    'uom_symbol'  => '',
                    'relation_id' => $elementId,
                    'level'       => 0,
                    'supply_rate' => 0,
                    'contractor_supply_rate' => 0,
                    'estimated_qty' => 0,
                    'percentage_of_wastage' => 0,
                    'difference' =>  0,
                    'amount' => 0,
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
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $item = Doctrine_Core::getTable('SupplyOfMaterialItem')->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $numericFields = array('supply_rate', 'contractor_supply_rate', 'estimated_qty', 'percentage_of_wastage');
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

            $rowData['supply_rate']            = $item->supply_rate;
            $rowData['contractor_supply_rate'] = $item->contractor_supply_rate;
            $rowData['estimated_qty']          = $item->estimated_qty;
            $rowData['percentage_of_wastage']  = $item->percentage_of_wastage;
            $rowData['difference']             = $item->difference;
            $rowData['amount']                 = $item->amount;
            $rowData['type']                   = (string) $item->type;
            $rowData['uom_id']                 = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol']             = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('SupplyOfMaterialItem')->find($request->getParameter('id'))
        );

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetItem          = Doctrine_Core::getTable('SupplyOfMaterialItem')->find(intval($request->getParameter('target_id')));

        if ( !$targetItem )
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('SupplyOfMaterialItem')->find($request->getParameter('prev_item_id')));
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
                        ->from('SupplyOfMaterialItem i')
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
                        i.supply_rate, i.contractor_supply_rate, i.estimated_qty, i.percentage_of_wastage, i.difference, i.amount, i.level')
                        ->from('SupplyOfMaterialItem i')
                        ->leftJoin('i.UnitOfMeasurement uom')
                        ->where('i.root_id = ?', $newItem->root_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $newItem->lft, $newItem->rgt ))
                        ->addOrderBy('i.lft')
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

                    foreach ( $children as $key => $child )
                    {
                        $children[$key]['type']                   = (string) $child['type'];
                        $children[$key]['uom_id']                 = $child['uom_id'] > 0 ? (string) $child['uom_id'] : '-1';
                        $children[$key]['uom_symbol']             = $child['uom_id'] > 0 ? $child['UnitOfMeasurement']['symbol'] : '';
                        $children[$key]['relation_id']            = $child['element_id'];
                        $children[$key]['supply_rate']            = $child['supply_rate'];
                        $children[$key]['contractor_supply_rate'] = $child['contractor_supply_rate'];
                        $children[$key]['estimated_qty']          = $child['estimated_qty'];
                        $children[$key]['percentage_of_wastage']  = $child['percentage_of_wastage'];
                        $children[$key]['difference']             = $child['difference'];
                        $children[$key]['amount']                 = $child['amount'];
                        $children[$key]['_csrf_token']            = $form->getCSRFToken();
                    }

                    $data['id']                     = $newItem->id;
                    $data['description']            = $newItem->description;
                    $data['type']                   = (string) $newItem->type;
                    $data['uom_id']                 = $newItem->uom_id > 0 ? (string) $newItem->uom_id : '-1';
                    $data['uom_symbol']             = $newItem->uom_id > 0 ? $newItem->UnitOfMeasurement->symbol : '';
                    $data['supply_rate']            = $newItem->supply_rate;
                    $data['contractor_supply_rate'] = $newItem->contractor_supply_rate;
                    $data['estimated_qty']          = $newItem->estimated_qty;
                    $data['percentage_of_wastage']  = $newItem->percentage_of_wastage;
                    $data['difference']             = $newItem->difference;
                    $data['amount']                 = $newItem->amount;
                    $data['relation_id']            = $newItem->element_id;
                    $data['level']                  = $newItem->level;
                    $data['has_note']               = ( $newItem->note != null && $newItem->note != '' ) ? true : false;
                    $data['note']                   = (string) $newItem->note;
                    $data['_csrf_token']            = $form->getCSRFToken();

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
            $item = Doctrine_Core::getTable('SupplyOfMaterialItem')->find($request->getParameter('id'))
        );

        $items    = array();
        $errorMsg = null;

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('i.id')
                ->from('SupplyOfMaterialItem i')
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

    public function executeItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('SupplyOfMaterialItem')->find($request->getParameter('id'))
        );

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

                $children = DoctrineQuery::create()->select('i.id, i.level')
                    ->from('SupplyOfMaterialItem i')
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
            $item = Doctrine_Core::getTable('SupplyOfMaterialItem')->find($request->getParameter('id'))
        );

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

                $children = DoctrineQuery::create()->select('i.id, i.level')
                    ->from('SupplyOfMaterialItem i')
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
            $settings = Doctrine_Core::getTable('SupplyOfMaterialLayoutSetting')->getPrintingLayoutSettings($id)
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
        $printSettingId = $request->getParameter('somBillLayoutSettingId');
        $contents       = ( is_array($request->getParameter('content')) ) ? $request->getParameter('content') : json_decode($request->getParameter('content'),
            true);
        $type           = $request->getParameter('type');

        // find the project layout setting first
        $masterSetting = Doctrine_Core::getTable('SupplyOfMaterialLayoutSetting')->find($printSettingId);

        // posted fields that will be translated into fields name inside the database
        switch ($type)
        {
            case 'headStyling':
                break;

            case 'fontNumber':
                $setting = $masterSetting;
                $form    = new SupplyOfMaterialLayoutSettingForm();

                $fields = array(
                    'fontTypeName'    => 'font',
                    'fontSize'        => 'size',
                    'amtCommaRemove'  => 'comma_total',
                    'rateCommaRemove' => 'comma_rate',
                );
                break;

            case 'pageFormat':
                $setting = $masterSetting;
                $form    = new SupplyOfMaterialLayoutSettingForm();

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
                $setting = $masterSetting->getSOMBillPhrase();
                $form    = new SupplyOfMaterialLayoutPhraseSettingForm();

                $fields = array(
                    'toCollection'           => 'to_collection',
                    'currencyPrefix'         => 'currency',
                    'collectionInGridPrefix' => 'collection_in_grid',
                );
                break;

            case 'headerFooter':
                $setting = $masterSetting->getSOMBillPhrase();
                $form    = new SupplyOfMaterialLayoutPhraseSettingForm();

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
            $con = Doctrine_Core::getTable('SupplyOfMaterialLayoutHeadSetting')->getConnection();

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
                    $headSetting            = Doctrine_Core::getTable('SupplyOfMaterialLayoutHeadSetting')->find($id);
                    $headSetting            = $headSetting ? $headSetting : new SupplyOfMaterialLayoutHeadSetting();
                    $headSetting->head      = ( isset ( $contents['head'][$key] ) ) ? $contents['head'][$key] : null;
                    $headSetting->bold      = ( isset ( $contents['bold'][$key] ) ) ? true : false;
                    $headSetting->italic    = ( isset ( $contents['italic'][$key] ) ) ? true : false;
                    $headSetting->underline = ( isset ( $contents['underline'][$key] ) ) ? true : false;
                    $headSetting->save();

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

    // simple method to validate forms
    private function isFormCorrect($data, sfForm $form)
    {
        $form->bind($data);

        return $form->isValid() ? true : false;
    }

}