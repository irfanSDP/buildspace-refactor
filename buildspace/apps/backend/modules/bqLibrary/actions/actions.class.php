<?php

/**
 * bqLibrary actions.
 *
 * @package    buildspace
 * @subpackage bqLibrary
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class bqLibraryActions extends BaseActions
{
    public function executeLibraryList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $records = DoctrineQuery::create()->select('r.id, r.name')
            ->from('BQLibrary r')
            ->addOrderBy('r.id ASC')
            ->fetchArray();

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

    public function executeLibraryAdd(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $bqLibrary = new BQLibrary();
        $con       = $bqLibrary->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $count = DoctrineQuery::create()->select('r.id')
                ->from('BQLibrary r')
                ->where('r.name ILIKE ?', 'New Library%')
                ->addOrderBy('r.id ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->count();

            $bqLibrary->name = 'New Library ' . ( $count + 1 );

            $bqLibrary->save($con);

            $con->commit();

            $success = true;

            $form = new BaseForm();

            $item = array(
                'id'          => $bqLibrary->id,
                'name'        => $bqLibrary->name,
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

    public function executeLibraryUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getParameter('id')));

        $form = new BQLibraryForm($bqLibrary);

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

    public function executeLibraryDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getParameter('id'))
        );

        $items    = array();
        $errorMsg = null;

        try
        {
            $bqLibrary->delete();
            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }
    /**** Bq Library actions end ****/

    /**** element actions ****/
    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getParameter('id')));

        $records = DoctrineQuery::create()->select('t.id, t.description, t.updated_at')
            ->from('BQElement t')
            ->andWhere('t.library_id = ?', $bqLibrary->id)
            ->addOrderBy('t.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        foreach ( $records as $key => $record )
        {
            $records[$key]['relation_id'] = $bqLibrary->id;
            $records[$key]['updated_at']  = date('d/m/Y H:i', strtotime($record['updated_at']));
            $records[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'relation_id' => $bqLibrary->id,
            'updated_at'  => '-',
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeElementAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $element = new BQElement();
        $con     = $element->getTable()->getConnection();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $prevElement = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('BQElement')->find($request->getParameter('prev_item_id')) : null;

            $priority    = $prevElement ? $prevElement->priority + 1 : 0;
            $bqLibraryId = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');
                $element->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }
        }
        else
        {
            $this->forward404Unless($nextElement = Doctrine_Core::getTable('BQElement')->find($request->getParameter('before_id')));

            $priority    = $nextElement->priority;
            $bqLibraryId = $nextElement->library_id;
        }

        $items = array();
        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('BQElement')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('library_id = ?', $bqLibraryId)
                ->execute();

            $element->library_id = $bqLibraryId;
            $element->priority   = $priority;

            $element->save();

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item = array();

            $form = new BaseForm();

            $item['id']          = $element->id;
            $item['description'] = $element->description;
            $item['relation_id'] = $bqLibraryId;
            $item['updated_at']  = date('d/m/Y H:i', strtotime($element->updated_at));
            $item['_csrf_token'] = $form->getCSRFToken();

            array_push($items, $item);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'relation_id' => $bqLibraryId,
                    'updated_at'  => '-',
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

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $element = Doctrine_Core::getTable('BQElement')->find($request->getParameter('id')));

        $rowData = array();
        $con     = $element->getTable()->getConnection();

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

            $rowData = array(
                $fieldName   => $element->$fieldName,
                'updated_at' => date('d/m/Y H:i', strtotime($element->updated_at))
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

    public function executeElementDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $element = Doctrine_Core::getTable('BQElement')->find($request->getParameter('id'))
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
            $errorMsg = $e->getMessage();
            $item     = array();
            $success  = false;
        }

        // return items need to be in array since grid js expect an array of items for delete operation (delete from store)
        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => array( $item ) ));
    }

    public function executeElementPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BQElement')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetElement = Doctrine_Core::getTable('BQElement')->find(intval($request->getParameter('target_id')));

        if ( !$targetElement )
        {
            $this->forward404Unless($targetElement = Doctrine_Core::getTable('BQElement')->find($request->getParameter('prev_item_id')));
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

                    $data['id']         = $element->id;
                    $data['updated_at'] = date('d/m/Y H:i', strtotime($element->updated_at));

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
                    $newElement = $element->copyTo($targetElement, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newElement->id;
                    $data['description'] = $newElement->description;
                    $data['relation_id'] = $newElement->library_id;
                    $data['updated_at']  = date('d/m/Y H:i', strtotime($newElement->updated_at));
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

    /**** element actions end ****/

    public function executeResourceList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bqItem = Doctrine_Core::getTable('BQItem')->find($request->getParameter('item_id'))
        );

        $records = DoctrineQuery::create()->select('r.id, r.name, r.resource_library_id')
            ->from('BQLibraryBuildUpRateResource r')
            ->where('r.bq_item_id = ?', $bqItem->id)
            ->addOrderBy('r.id ASC')
            ->fetchArray();

        foreach ( $records as $key => $record )
        {
            $records[$key]['total_build_up'] = $bqItem->calculateBuildUpTotalByResourceId($record['id']);
        }

        return $this->renderJson($records);
    }

    /*
     * Add Resource Category feat.
     */
    public function executeGetResourceList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bqItem = Doctrine_Core::getTable('BQItem')->find($request->getParameter('item_id'))
        );

        $form = new BaseForm();

        $records = DoctrineQuery::create()->select('r.id, r.name')
            ->from('Resource r')
            ->addOrderBy('r.id ASC')
            ->fetchArray();

        foreach ( $records as $key => $record )
        {
            $isResourceLibraryExists = $bqItem->isResourceLibraryExistsInBuildUpRate($record['id']);

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
            $bqItem = Doctrine_Core::getTable('BQItem')->find($request->getParameter('bqid'))
        );

        $options = array();
        $values  = array();

        array_push($values, '-1');
        array_push($options, '---');

        $records = DoctrineQuery::create()->select('u.id, u.symbol')
            ->from('UnitOfMeasurement u')
            ->where('u.display IS TRUE')
            ->addOrderBy('u.symbol ASC')
            ->fetchArray();

        foreach ( $records as $record )
        {
            array_push($values, (string) $record['id']); //damn, dojo store handles ids in string format
            array_push($options, $record['symbol']);
        }

        try
        {
            $buildUpRateResource = $bqItem->createBuildUpRateResourceFromResourceLibrary($resource);

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
            $bqItem = Doctrine_Core::getTable('BQItem')->find($request->getParameter('bqid'))
        );

        try
        {
            $buildUpRateResource = BQLibraryBuildUpRateResourceTable::getByResourceLibraryIdAndBQItemId($resource->id, $bqItem->id);

            $buildUpRateResourceId = $buildUpRateResource->id;

            if ( $buildUpRateResource )
            {
                $buildUpRateResource->delete();
            }

            /*
             * check after deleting resource, is there any resource left for schedule of rate item
             */
            $count = DoctrineQuery::create()->select('r.id')
                ->from('BQLibraryBuildUpRateResource r')
                ->where('r.bq_item_id = ?', $bqItem->id)
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

    /**** item actions ****/
    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BQElement')->find($request->getParameter('id'))
        );

        $pdo                       = $element->getTable()->getConnection()->getDbh();
        $formulatedColumns         = array();
        $form                      = new BaseForm();
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQItem');

        $stmt = $pdo->prepare("SELECT DISTINCT c.id, c.description, c.type, uom.id as uom_id, c.priority, c.lft,
        c.level, c.element_id, c.updated_at, uom.symbol AS uom_symbol
        FROM " . BQItemTable::getInstance()->getTableName() . " c
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
        WHERE c.element_id = " . $element->id . " AND c.deleted_at IS NULL
        ORDER BY c.priority, c.lft, c.level");

        $stmt->execute();
        $bqItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value, ifc.has_build_up
        FROM " . BQItemFormulatedColumnTable::getInstance()->getTableName() . " ifc JOIN
        " . BQItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
        WHERE i.element_id = " . $element->id . " AND ifc.deleted_at IS NULL
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

        foreach ( $bqItems as $key => $bqItem )
        {
            $bqItems[$key]['type']        = (string) $bqItem['type'];
            $bqItems[$key]['uom_id']      = $bqItem['uom_id'] > 0 ? (string) $bqItem['uom_id'] : '-1';
            $bqItems[$key]['uom_symbol']  = $bqItem['uom_id'] > 0 ? $bqItem['uom_symbol'] : '';
            $bqItems[$key]['relation_id'] = $element->id;
            $bqItems[$key]['updated_at']  = date('d/m/Y H:i', strtotime($bqItem['updated_at']));
            $bqItems[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnConstants as $constant )
            {
                $bqItems[$key][$constant . '-final_value']        = 0;
                $bqItems[$key][$constant . '-value']              = '';
                $bqItems[$key][$constant . '-has_cell_reference'] = false;
                $bqItems[$key][$constant . '-has_formula']        = false;
                $bqItems[$key][$constant . '-has_build_up']       = false;
            }

            if ( array_key_exists($bqItem['id'], $formulatedColumns) )
            {
                foreach ( $formulatedColumns[$bqItem['id']] as $formulatedColumn )
                {
                    $columnName                                         = $formulatedColumn['column_name'];
                    $bqItems[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                    $bqItems[$key][$columnName . '-value']              = $formulatedColumn['value'];
                    $bqItems[$key][$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
                    $bqItems[$key][$columnName . '-has_cell_reference'] = false;
                    $bqItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }
            }

            unset( $formulatedColumns[$bqItem['id']], $bqItem );
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'type'        => (string) BQItem::TYPE_WORK_ITEM,
            'uom_id'      => '-1',
            'uom_symbol'  => '',
            'relation_id' => $element->id,
            'updated_at'  => '-',
            'level'       => 0,
            '_csrf_token' => $form->getCSRFToken()
        );

        foreach ( $formulatedColumnConstants as $constant )
        {
            $defaultLastRow[$constant . '-final_value']        = "";
            $defaultLastRow[$constant . '-value']              = "";
            $defaultLastRow[$constant . '-has_build_up']       = false;
            $defaultLastRow[$constant . '-has_cell_reference'] = false;
            $defaultLastRow[$constant . '-has_formula']        = false;
        }


        array_push($bqItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $bqItems
        ));
    }

    public function executeItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

        $rowData                   = array();
        $affectedNodes             = array();
        $isFormulatedColumn        = false;
        $formulatedColumnTable     = Doctrine_Core::getTable('BQItemFormulatedColumn');
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQItem');

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
                        array_push($affectedNodes, array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value
                        ));
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

            $rowData['affected_nodes'] = $affectedNodes;
            $rowData['type']           = (string) $item->type;
            $rowData['uom_id']         = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol']     = $item->uom_id > 0 ? $item->getUnitOfMeasurement()->symbol : '';
            $rowData['updated_at']     = date('d/m/Y H:i', strtotime($item->updated_at));
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
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQItem');
        $isFormulatedColumn        = false;

        $con = Doctrine_Core::getTable('BQItem')->getConnection();

        try
        {
            $con->beginTransaction();

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('BQItem')->find($request->getParameter('prev_item_id')) : null;
                $elementId    = $request->getParameter('relation_id');

                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                $item = BQItemTable::createItemFromLastRow($previousItem, $elementId, $fieldName, $fieldValue);

                if ( in_array($fieldName, $formulatedColumnConstants) )
                {
                    $isFormulatedColumn = true;
                }
            }
            else
            {
                $this->forward404Unless($nextItem = Doctrine_Core::getTable('BQItem')->find($request->getParameter('before_id')));

                $elementId = $nextItem->element_id;

                $item = BQItemTable::createItem($nextItem, $elementId);
            }

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new BQItemFormulatedColumn();
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
            $data['uom_symbol']  = $item->uom_id > 0 ? Doctrine_Core::getTable('UnitOfMeasurement')->find($item->uom_id)->symbol : '';
            $data['relation_id'] = $elementId;
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
                $data[$constant . '-has_build_up']       = $formulatedColumn ? $formulatedColumn['has_build_up'] : false;
            }

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'type'        => (string) BQItem::TYPE_WORK_ITEM,
                    'uom_id'      => '-1',
                    'uom_symbol'  => '',
                    'relation_id' => $elementId,
                    'updated_at'  => '-',
                    'level'       => 0,
                    '_csrf_token' => $form->getCSRFToken()
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

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetItem = Doctrine_Core::getTable('BQItem')->find(intval($request->getParameter('target_id')));

        if ( !$targetItem )
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('BQItem')->find($request->getParameter('prev_item_id')));
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
                        ->from('BQItem i')
                        ->where('i.root_id = ?', $item->root_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                        ->addOrderBy('i.lft')
                        ->fetchArray();

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
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            case 'copy':
                try
                {
                    $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQItem');

                    $newItem = $item->copyTo($targetItem, $lastPosition);

                    $form = new BaseForm();

                    $children = DoctrineQuery::create()->select('i.id, i.description, i.type, i.uom_id, i.element_id, i.level, i.updated_at, uom.symbol, ifc.column_name, ifc.value, ifc.final_value, ifc.has_build_up')
                        ->from('BQItem i')
                        ->leftJoin('i.FormulatedColumns ifc')
                        ->leftJoin('i.UnitOfMeasurement uom')
                        ->where('i.root_id = ?', $newItem->root_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $newItem->lft, $newItem->rgt ))
                        ->addOrderBy('i.lft')
                        ->fetchArray();

                    foreach ( $children as $key => $child )
                    {
                        $children[$key]['type']        = (string) $child['type'];
                        $children[$key]['uom_id']      = $child['uom_id'] > 0 ? (string) $child['uom_id'] : '-1';
                        $children[$key]['uom_symbol']  = $child['uom_id'] > 0 ? $child['UnitOfMeasurement']['symbol'] : '';
                        $children[$key]['relation_id'] = $child['element_id'];
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

                        unset( $children[$key]['FormulatedColumns'] );
                    }

                    $data['id']          = $newItem->id;
                    $data['description'] = $newItem->description;
                    $data['type']        = (string) $newItem->type;
                    $data['uom_id']      = $newItem->uom_id > 0 ? (string) $newItem->uom_id : '-1';
                    $data['uom_symbol']  = $newItem->uom_id > 0 ? $newItem->UnitOfMeasurement->symbol : '';
                    $data['relation_id'] = $newItem->element_id;
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

                    if ( $rateColumn = $targetItem->getFormulatedColumnByName(BQItem::FORMULATED_COLUMN_RATE) )
                    {
                        $data = array(
                            'id'                      => $targetItem->id,
                            'rate-value'              => $rateColumn->value,
                            'rate-final_value'        => $rateColumn->final_value,
                            'rate-has_build_up'       => $rateColumn->has_build_up,
                            'rate-has_cell_reference' => $rateColumn->hasCellReference(),
                            'rate-has_formula'        => $rateColumn->hasFormula()
                        );

                        $referencedNodes = $rateColumn->getNodesRelatedByColumnName(BQItem::FORMULATED_COLUMN_RATE);

                        foreach ( $referencedNodes as $key => $referencedNode )
                        {
                            if ( $node = Doctrine_Core::getTable('BQItemFormulatedColumn')->find($referencedNode['node_from']) )
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
            $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id'))
        );

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('i.id')
                ->from('BQItem i')
                ->where('i.root_id = ?', $item->root_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $item->lft, $item->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

            $affectedNodes = $item->delete($con);

            $con->commit();

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();

            $items         = array();
            $affectedNodes = array();
            $errorMsg      = $e->getMessage();
            $success       = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => $affectedNodes ));
    }

    public function executeItemRateDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and
            $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

        $errorMsg = null;
        $bqItems  = array();

        try
        {
            $affectedItems = $item->deleteFormulatedColumns();

            $item->deleteBuildUpRates();

            array_push($affectedItems, array( 'id' => $item->id ));

            foreach ( $affectedItems as $item )
            {
                array_push($bqItems, array(
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

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $bqItems ));
    }

    public function executeItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

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
                    ->from('BQItem i')
                    ->where('i.root_id = ?', $item->root_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->fetchArray();

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

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

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
                    ->from('BQItem i')
                    ->where('i.root_id = ?', $item->root_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->fetchArray();

                $success = true;
            }
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }
    /**** item actions end ****/

    /**** start BuildUpRate ops ***/
    public function executeGetBuildUpRateItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bqItem = Doctrine_Core::getTable('BQItem')->find($request->getParameter('item_id')) and
            $resource = Doctrine_Core::getTable('BQLibraryBuildUpRateResource')->find($request->getParameter('resource_id'))
        );

        $buildUpRateItems = DoctrineQuery::create()->select('i.id, i.description, i.uom_id, i.total, i.line_total, i.resource_item_library_id, i.updated_at, ifc.column_name, ifc.value, ifc.final_value, ifc.linked, uom.symbol')
            ->from('BQLibraryBuildUpRateItem i')
            ->leftJoin('i.FormulatedColumns ifc')
            ->leftJoin('i.UnitOfMeasurement uom')
            ->where('i.bq_item_id = ?', $bqItem->id)
            ->andWhere('i.build_up_rate_resource_id = ?', $resource->id)
            ->addOrderBy('i.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQLibraryBuildUpRateItem');

        foreach ( $buildUpRateItems as $key => $buildUpRateItem )
        {
            $buildUpRateItems[$key]['uom_id']      = $buildUpRateItem['uom_id'] > 0 ? (string) $buildUpRateItem['uom_id'] : '-1';
            $buildUpRateItems[$key]['uom_symbol']  = $buildUpRateItem['uom_id'] > 0 ? $buildUpRateItem['UnitOfMeasurement']['symbol'] : '';
            $buildUpRateItems[$key]['relation_id'] = $bqItem->id;
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

                if ( $columnName == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_RATE or $columnName == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $columnName == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
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
            'relation_id' => $bqItem->id,
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
        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

        $query = DoctrineQuery::create()->select('s.id')
            ->from('BQLibraryBuildUpRateSummary s')
            ->where('s.bq_item_id = ?', $item->id)
            ->limit(1);

        if ( $query->count() == 0 )
        {
            $buildUpRateSummary                             = new BQLibraryBuildUpRateSummary();
            $buildUpRateSummary->bq_item_id                 = $item->id;
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
        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

        $records = DoctrineQuery::create()->select('u.id, u.symbol')
            ->from('UnitOfMeasurement u')
            ->where('u.display IS TRUE')
            ->addOrderBy('u.symbol ASC')
            ->fetchArray();

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

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

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

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

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

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

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

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id')));

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
            $item = Doctrine_Core::getTable('BQLibraryBuildUpRateItem')->find($request->getParameter('id'))
        );

        $rowData = array();
        $con     = $item->getTable()->getConnection();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQLibraryBuildUpRateItem');

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $affectedNodes      = array();
            $isFormulatedColumn = false;

            if ( in_array($fieldName, $formulatedColumnConstants) )
            {
                $formulatedColumnTable = Doctrine_Core::getTable('BQLibraryBuildUpRateFormulatedColumn');

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

                if ( $fieldName == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_RATE or $fieldName == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $fieldName == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
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

                    if ( $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                    {
                        $rowData[$constant . '-linked'] = $formulatedColumn ? $formulatedColumn['linked'] : false;
                    }
                }
            }

            $rowData['affected_nodes'] = $affectedNodes;
            $rowData['linked']         = $item->resource_item_library_id > 0 ? true : false;
            $rowData['uom_id']         = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol']     = $item->uom_id > 0 ? $item->getUnitOfMeasurement()->symbol : '';

            $totalBuildUp = $item->BQItem->calculateBuildUpTotalByResourceId($item->build_up_rate_resource_id);
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

        $item = new BQLibraryBuildUpRateItem();

        $con = $item->getTable()->getConnection();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQLibraryBuildUpRateItem');

        $isFormulatedColumn = false;

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $this->forward404Unless($resource = Doctrine_Core::getTable('BQLibraryBuildUpRateResource')->find($request->getParameter('resource_id')));

            $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('BQLibraryBuildUpRateItem')->find($request->getParameter('prev_item_id')) : null;

            $priority = $previousItem ? $previousItem->priority + 1 : 0;
            $bqItemId = $request->getParameter('relation_id');

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
            $this->forward404Unless($nextItem = Doctrine_Core::getTable('BQLibraryBuildUpRateItem')->find($request->getParameter('before_id')));

            $bqItemId   = $nextItem->bq_item_id;
            $resourceId = $nextItem->build_up_rate_resource_id;
            $priority   = $nextItem->priority;
        }

        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('BQLibraryBuildUpRateItem')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('bq_item_id = ?', $bqItemId)
                ->andWhere('build_up_rate_resource_id = ?', $resourceId)
                ->execute();

            $item->bq_item_id                = $bqItemId;
            $item->build_up_rate_resource_id = $resourceId;
            $item->priority                  = $priority;

            $item->save($con);

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new BQLibraryBuildUpRateFormulatedColumn();
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
            $data['relation_id'] = $bqItemId;
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

                if ( $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
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
                    'relation_id' => $bqItemId,
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

            $totalBuildUp = $item->BQItem->calculateBuildUpTotalByResourceId($item->build_up_rate_resource_id);
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
            $buildUpItem = Doctrine_Core::getTable('BQLibraryBuildUpRateItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        try
        {
            $bqItem        = $buildUpItem->BQItem;
            $item['id']    = $buildUpItem->id;
            $resourceId    = $buildUpItem->build_up_rate_resource_id;
            $affectedNodes = $buildUpItem->delete();

            $bqItem->save();
            $bqItem->refresh();

            $buildUpSummary = array(
                'conversion_factor_amount'    => $bqItem->BuildUpRateSummary->conversion_factor_amount,
                'total_cost_after_conversion' => $bqItem->BuildUpRateSummary->getTotalCostAfterConversion(),
                'final_cost'                  => $bqItem->BuildUpRateSummary->calculateFinalCost()
            );

            $totalBuildUp = $bqItem->calculateBuildUpTotalByResourceId($resourceId);

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

        return $this->renderJson(array(
            'success'          => $success,
            'errorMsg'         => $errorMsg,
            'item'             => $item,
            'affected_nodes'   => $affectedNodes,
            'build_up_summary' => $buildUpSummary,
            'total_build_up'   => $totalBuildUp
        ));
    }

    public function executeBuildUpRateItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $buildUpRateItem = Doctrine_Core::getTable('BQLibraryBuildUpRateItem')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetBuildUpRateItem = Doctrine_Core::getTable('BQLibraryBuildUpRateItem')->find(intval($request->getParameter('target_id')));

        if ( !$targetBuildUpRateItem )
        {
            $this->forward404Unless($targetBuildUpRateItem = Doctrine_Core::getTable('BQLibraryBuildUpRateItem')->find($request->getParameter('prev_item_id')));
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

                    $data['id']         = $buildUpRateItem->id;
                    $data['updated_at'] = date('d/m/Y H:i', strtotime($buildUpRateItem->updated_at));

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
                    $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQLibraryBuildUpRateItem');
                    $newBuildUpRateItem        = $buildUpRateItem->copyTo($targetBuildUpRateItem, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newBuildUpRateItem->id;
                    $data['description'] = $newBuildUpRateItem->description;
                    $data['uom_id']      = $newBuildUpRateItem->uom_id > 0 ? (string) $newBuildUpRateItem->uom_id : '-1';
                    $data['uom_symbol']  = $newBuildUpRateItem->uom_id > 0 ? $newBuildUpRateItem->UnitOfMeasurement->symbol : '';
                    $data['relation_id'] = $newBuildUpRateItem->bq_item_id;
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

                        if ( $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                        {
                            $data[$constant . '-linked'] = $formulatedColumn ? $formulatedColumn['linked'] : false;
                        }
                    }

                    $data['total']      = $newBuildUpRateItem->calculateTotal();
                    $data['line_total'] = $newBuildUpRateItem->calculateLineTotal();

                    $totalBuildUp = $newBuildUpRateItem->BQItem->calculateBuildUpTotalByResourceId($newBuildUpRateItem->build_up_rate_resource_id);

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
            $resource = Doctrine_Core::getTable('BQLibraryBuildUpRateResource')->find($request->getParameter('rid')) and
            $bqItem = Doctrine_Core::getTable('BQItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        $items    = array();
        try
        {
            $buildUpRateItems = $bqItem->importResourceItems(Utilities::array_filter_integer(explode(',', $request->getParameter('ids'))), $resource);

            $bqItem->save();

            $form                      = new BaseForm();
            $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQLibraryBuildUpRateItem');

            foreach ( $buildUpRateItems as $buildUpRateItem )
            {
                $item = array();

                $item['id']          = $buildUpRateItem->id;
                $item['description'] = $buildUpRateItem->description;
                $item['uom_id']      = $buildUpRateItem->uom_id > 0 ? (string) $buildUpRateItem->uom_id : '-1';
                $item['uom_symbol']  = $buildUpRateItem->uom_id > 0 ? $buildUpRateItem->UnitOfMeasurement->symbol : '';
                $item['relation_id'] = $bqItem->id;
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

                    if ( $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == BQLibraryBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
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
                'relation_id' => $bqItem->id,
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

    public function executeGetResourceDescendantsForImport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('id'))
        );

        try
        {
            $items = DoctrineQuery::create()->select('i.id')
                ->from('ResourceItem i')
                ->andWhere('i.root_id = ?', $resourceItem->root_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $resourceItem->lft, $resourceItem->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

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
            $sfImport = new sfBuildspaceExcelParser($newName, $ext, $tempUploadPath, true, false);//startRead(${filename}, ${uploadPath}, ${extension}, ${generateXML})

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

    public function executeImportBuildspaceExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $errorMsg       = null;
        $pathToFile     = null;

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

        $sfImport = new sfBQLibraryImportBuildspaceExcel($pathToFile);

        try
        {
            $sfImport->process();

            $data = $sfImport->getPreviewFormatData();

            $returnData = array(
                'filename'  => $newName . '.' . $ext,
                'extension' => $ext,
                'Elements'  => array(),
                'columns'   => $data['bill_info']['bill_column_settings']
            );

            foreach ( $data['elements'] as $element )
            {
                $elementInfo = array(
                    'id'          => $element['info']['id'],
                    'description' => $element['info']['description'],
                    'count'       => $element['info']['count'],
                    'error'       => $element['info']['error']
                );

                array_push($returnData['Elements'], $elementInfo);

                $items                                                = $element['items'];
                $returnData['ElementsToItem'][$element['info']['id']] = array();

                foreach ( $items as $item )
                {
                    array_push($returnData['ElementsToItem'][$element['info']['id']], $item);
                }
            }

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $returnData['success']  = $success;
        $returnData['errorMsg'] = $errorMsg;

        return $this->renderJson($returnData);
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
            $sfImport = new sfImportExcelBuildsoft($newName, $ext, $tempUploadPath, true);//startRead(${filename}, ${uploadPath}, ${extension}, ${generateXML})

            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $returnData = array(
                'filename'   => $sfImport->sfExportXML->filename,
                'uploadPath' => $sfImport->sfExportXML->uploadPath,
                'extension'  => $sfImport->sfExportXML->extension,
                'excelType'  => $sfImport->excelType,
                'Elements'   => array()
            );

            foreach ( $data as $element )
            {
                array_push($returnData['Elements'], array(
                    'id'          => $element['id'],
                    'description' => $element['description'],
                    'count'       => $sfImport->elementItemCount[$element['id']],
                    'error'       => $sfImport->elementErrorCount[$element['id']]
                ));

                $items                                        = $element['_child'];
                $returnData['ElementsToItem'][$element['id']] = array();

                foreach ( $items as $item )
                {
                    array_push($returnData['ElementsToItem'][$element['id']], $item);
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
            $sfImport = new sfImportExcelPricelist($newName, $ext, $tempUploadPath, true);//startRead(${filename}, ${uploadPath}, ${extension}, ${generateXML})

            $sfImport->startRead();

            $data = $sfImport->getProcessedData();

            $returnData = array(
                'filename'   => $sfImport->sfExportXML->filename,
                'uploadPath' => $sfImport->sfExportXML->uploadPath,
                'extension'  => $sfImport->sfExportXML->extension,
                'excelType'  => $sfImport->excelType,
                'Elements'   => array()
            );

            foreach ( $data as $element )
            {
                array_push($returnData['Elements'], array(
                    'id'          => $element['id'],
                    'description' => $element['description'],
                    'count'       => $sfImport->elementItemCount[$element['id']],
                    'error'       => $sfImport->elementErrorCount[$element['id']]
                ));

                $items                                        = $element['_child'];
                $returnData['ElementsToItem'][$element['id']] = array();

                foreach ( $items as $item )
                {
                    array_push($returnData['ElementsToItem'][$element['id']], $item);
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
            $library = Doctrine_Core::getTable('BQLibrary')->find($request->getParameter('library_id')) and
            ( $request->getParameter('filename') )
        );

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $errorMsg       = null;
        $filename       = $request->getParameter('filename');
        $extension      = $request->getParameter('extension');

        try
        {
            $sfImport = new sfImportExcelNormal($filename, $extension, $tempUploadPath, true);//startRead(${filename}, ${uploadPath}, ${extension}, ${generateXML})

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
                'Elements'   => array()
            );

            foreach ( $data as $element )
            {
                if ( empty( $element['_child'] ) )
                {
                    continue;
                }

                array_push($returnData['Elements'], array(
                    'id'          => $element['id'],
                    'description' => $element['description'],
                    'count'       => $sfImport->elementItemCount[$element['id']],
                    'error'       => $sfImport->elementErrorCount[$element['id']]
                ));

                $items                                        = $element['_child'];
                $returnData['ElementsToItem'][$element['id']] = array();

                foreach ( $items as $item )
                {
                    array_push($returnData['ElementsToItem'][$element['id']], $item);
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
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $library = Doctrine_Core::getTable('BQLibrary')->find($request->getParameter('library_id'))
        );

        sfConfig::set('sf_web_debug', false);

        set_time_limit(0);

        $tempUploadPath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $fileInfo = new SplFileInfo($request->getParameter('filename'));

        $filename = $fileInfo->getBasename('.' . $fileInfo->getExtension());

        $errorMsg = null;

        //explode Element to imports
        $elementIds = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
        $withRate   = ( $request->getParameter('with_rate') === 'true' );

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        try
        {
            if ( !is_readable($tempUploadPath . $filename.'.'.$request->getParameter('extension')) )
            {
                throw new Exception('Uploaded file ' . $filename.'.'.$request->getParameter('extension') . ' is unreadable');
            }

            $con->getDbh()->beginTransaction();

            switch(strtolower($request->getParameter('extension')))
            {
                case 'xls':
                case 'xlsx':
                    $sfImport = new sfBQLibraryImportBuildspaceExcel($tempUploadPath . $filename.'.'.$request->getParameter('extension'));

                    $sfImport->process();

                    $sfImport->saveIntoLibrary($library, $elementIds, $withRate, $con);

                    break;
                case 'xml':
                    $sfImport = new sfBQLibraryImportXML($library, $filename, $tempUploadPath, $request->getParameter('extension'), $withRate);

                    $sfImport->process($elementIds);

                    $sfImport->save($con);

                    break;
                default:
                    throw new Exception('Unsupported file format');
            }

            $con->getDbh()->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->getDbh()->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
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
            ->fetchArray();

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

    public function executeImportBuildspaceZipFile(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isMethod('POST') AND
            $bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getParameter('bqLibraryId'))
        );

        session_write_close();

        sfConfig::set('sf_web_debug', false);

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        $fileName       = sfBuildSpaceBQLibraryXMLGenerator::XML_FILENAME.'.xml';
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

                if ( $res != true )
                {
                    throw new Exception('Invalid BuildSpace BQ Library File!');
                }

                $zip->extractTo($tempUploadPath);
                $zip->close();

                break;
            }

            if ( !is_readable($tempUploadPath . $fileName) )
            {
                throw new InvalidArgumentException('Invalid BuildSpace BQ Library File!');
            }

            $con->beginTransaction();

            // will get the uploaded file and then unzip
            // check file name and extension
            // begin processing of parsing the xml file and then map it according to the database structure

            //Initiate xmlParser
            $xmlParser = new sfBuildspaceXMLParser(sfBuildSpaceBQLibraryXMLGenerator::XML_FILENAME, $tempUploadPath, null, true);

            // read xmlParser
            $xmlParser->read();

            // Get XML Processed Data
            $loadedXML = $xmlParser->getProcessedData();

            $importer = new sfBuildSpaceBQLibraryBSImporter($bqLibrary, $loadedXML, $con);
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
            $bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getParameter('bqLibraryId')
            ));

        $filesToZip = array();
        $fileName   = $request->getPostParameter('fileName');
        $conn       = $bqLibrary->getTable()->getConnection();

        try
        {
            sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url' ));

            $fileExporter = new sfBuildSpaceBQLibraryXMLGenerator($bqLibrary, $conn);
            $fileExporter->generateXMLFile();

            array_push($filesToZip, $fileExporter->getFileInformation());

            $sfZipGenerator = new sfZipGenerator($fileName, null, null, true, true);
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