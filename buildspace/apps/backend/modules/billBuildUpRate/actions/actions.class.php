<?php

/**
 * billBuildUpRate actions.
 *
 * @package    buildspace
 * @subpackage billBuildUpRate
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class billBuildUpRateActions extends BaseActions {

    public function executeGetBuildUpRateItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('bill_item_id')) and
            $resource = Doctrine_Core::getTable('BillBuildUpRateResource')->find($request->getParameter('resource_id'))
        );

        $buildUpRateItems = DoctrineQuery::create()->select('i.id, i.description, i.uom_id, i.total, i.line_total, i.resource_item_library_id, ifc.column_name, ifc.value, ifc.final_value, ifc.linked, uom.symbol')
            ->from('BillBuildUpRateItem i')
            ->leftJoin('i.FormulatedColumns ifc')
            ->leftJoin('i.UnitOfMeasurement uom')
            ->where('i.bill_item_id = ?', $billItem->id)
            ->andWhere('i.build_up_rate_resource_id = ?', $resource->id)
            ->addOrderBy('i.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillBuildUpRateItem');

        foreach ( $buildUpRateItems as $key => $buildUpRateItem )
        {
            $buildUpRateItems[$key]['uom_id']      = $buildUpRateItem['uom_id'] > 0 ? (string) $buildUpRateItem['uom_id'] : '-1';
            $buildUpRateItems[$key]['uom_symbol']  = $buildUpRateItem['uom_id'] > 0 ? $buildUpRateItem['UnitOfMeasurement']['symbol'] : '';
            $buildUpRateItems[$key]['relation_id'] = $billItem->id;
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

                if ( $columnName == BillBuildUpRateItem::FORMULATED_COLUMN_RATE or $columnName == BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $columnName == BillBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                {
                    $buildUpRateItems[$key][$columnName . '-linked'] = $formulatedColumn['linked'];
                }
            }

            $buildUpRateItems[$key]['total']      = BillBuildUpRateItemTable::calculateTotalById($buildUpRateItem['id']);
            $buildUpRateItems[$key]['line_total'] = BillBuildUpRateItemTable::calculateLineTotalById($buildUpRateItem['id']);

            unset( $buildUpRateItem, $buildUpRateItems[$key]['FormulatedColumns'], $buildUpRateItems[$key]['UnitOfMeasurement'] );
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'uom_id'      => '-1',
            'uom_symbol'  => '',
            'relation_id' => $billItem->id,
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

        $data = array(
            'identifier' => 'id',
            'items'      => $buildUpRateItems
        );

        return $this->renderJson($data);
    }

    public function executeGetBuildUpSummary(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

        return $this->renderJson(array(
            'apply_conversion_factor'     => $billItem->BuildUpRateSummary->apply_conversion_factor,
            'conversion_factor_amount'    => number_format($billItem->BuildUpRateSummary->conversion_factor_amount, 2, '.', ''),
            'conversion_factor_operator'  => $billItem->BuildUpRateSummary->conversion_factor_operator,
            'total_cost'                  => number_format($billItem->BuildUpRateSummary->calculateTotalCost(), 2, '.', ''),
            'total_cost_after_conversion' => number_format($billItem->BuildUpRateSummary->getTotalCostAfterConversion(), 2, '.', ''),
            'markup'                      => number_format($billItem->BuildUpRateSummary->markup, 2, '.', ''),
            'final_cost'                  => number_format($billItem->BuildUpRateSummary->calculateFinalCost(), 2, '.', ''),
            'rounding_type'               => ( $billItem->BuildUpRateSummary->rounding_type ) ? $billItem->BuildUpRateSummary->rounding_type : $billItem->Element->ProjectStructure->BillSetting->build_up_rate_rounding_type
        ));
    }

    public function executeBuildUpSummaryRoundingUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

        $buildUpRateSummary = $billItem->BuildUpRateSummary;

        $buildUpRateSummary->rounding_type = $request->getParameter('rounding_type');

        $buildUpRateSummary->save();

        return $this->renderJson(array(
            'final_cost'    => number_format($buildUpRateSummary->calculateFinalCost(), 2, '.', ''),
            'rounding_type' => $buildUpRateSummary->rounding_type
        ));
    }

    public function executeGetConversionFactorUom(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

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

    public function executeBuildUpSummaryApplyConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

        $item->BuildUpRateSummary->apply_conversion_factor = $request->getParameter('value');
        $item->BuildUpRateSummary->save();

        $item->BuildUpRateSummary->refresh();

        return $this->renderJson(array(
            'conversion_factor_amount'    => number_format($item->BuildUpRateSummary->conversion_factor_amount, 2, '.', ''),
            'conversion_factor_operator'  => $item->BuildUpRateSummary->conversion_factor_operator,
            'total_cost_after_conversion' => number_format($item->BuildUpRateSummary->getTotalCostAfterConversion(), 2, '.', ''),
            'final_cost'                  => number_format($item->BuildUpRateSummary->calculateFinalCost(), 2, '.', '')
        ));
    }

    public function executeBuildUpSummaryMarkupUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

        $value = $request->getParameter('value');

        $markup = strlen($value) > 0 ? floatval($value) : 0;

        $item->BuildUpRateSummary->markup = $markup;
        $item->BuildUpRateSummary->save();

        return $this->renderJson(array(
            'markup'     => number_format($item->BuildUpRateSummary->markup, 2, '.', ''),
            'final_cost' => number_format($item->BuildUpRateSummary->calculateFinalCost(), 2, '.', '')
        ));
    }

    public function executeBuildUpSummaryConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

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
        
        return $this->renderJson(array(
            'conversion_factor_amount'    => number_format($buildUpRateSummary->conversion_factor_amount, 2, '.', ''),
            'total_cost_after_conversion' => number_format($buildUpRateSummary->getTotalCostAfterConversion(), 2, '.', ''),
            'conversion_factor_operator'  => $buildUpRateSummary->conversion_factor_operator,
            'final_cost'                  => number_format($buildUpRateSummary->calculateFinalCost(), 2, '.', '')
        ));
    }

    public function executeBuildUpSummaryConversionFactorUomUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

        $buildUpRateSummary = $item->BuildUpRateSummary;

        $uomId = intval($request->getParameter('uom_id'));

        $uomId = $uomId > 0 ? $uomId : null;

        $con = $item->getTable()->getConnection();
        try
        {
            $con->beginTransaction();

            $buildUpRateSummary->conversion_factor_uom_id = $uomId;
            $buildUpRateSummary->save();

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $success = false;
        }

        $data = array( 'success' => $success );

        return $this->renderJson($data);
    }

    public function executeBuildUpRateItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BillBuildUpRateItem')->find($request->getParameter('id')));

        $rowData = array();
        $con     = $item->getTable()->getConnection();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillBuildUpRateItem');

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $affectedNodes      = array();
            $isFormulatedColumn = false;

            if ( in_array($fieldName, $formulatedColumnConstants) )
            {
                $formulatedColumnTable = Doctrine_Core::getTable('BillBuildUpRateFormulatedColumn');

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

                        $affectedNode = array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value,
                            'total'                     => $total,
                            'line_total'                => $lineTotal
                        );
                        array_push($affectedNodes, $affectedNode);
                    }
                }

                $rowData[$fieldName . "-final_value"]        = $formulatedColumn->final_value;
                $rowData[$fieldName . "-value"]              = $formulatedColumn->value;
                $rowData[$fieldName . '-has_cell_reference'] = $formulatedColumn->hasCellReference();
                $rowData[$fieldName . '-has_formula']        = $formulatedColumn->hasFormula();

                if ( $fieldName == BillBuildUpRateItem::FORMULATED_COLUMN_RATE or $fieldName == BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $fieldName == BillBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
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

                    if ( $constant == BillBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == BillBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                    {
                        $rowData[$constant . '-linked'] = $formulatedColumn ? $formulatedColumn['linked'] : false;
                    }
                }
            }

            $rowData['affected_nodes'] = $affectedNodes;
            $rowData['linked']         = $item->resource_item_library_id > 0 ? true : false;
            $rowData['uom_id']         = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol']     = $item->uom_id > 0 ? $item->getUnitOfMeasurement()->symbol : '';

            $totalBuildUp = $item->BillItem->calculateBuildUpTotalByResourceId($item->build_up_rate_resource_id);

            $item->BillItem->Element->updateAllItemTotalAfterMarkup();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $totalBuildUp = 0;
            $errorMsg     = $e->getMessage();
            $success      = false;
        }

        $data = array(
            'success'        => $success,
            'errorMsg'       => $errorMsg,
            'total_build_up' => $totalBuildUp,
            'data'           => $rowData );

        return $this->renderJson($data);
    }

    public function executeBuildUpRateItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $items = array();

        $item = new BillBuildUpRateItem();

        $con = $item->getTable()->getConnection();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillBuildUpRateItem');

        $isFormulatedColumn = false;

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $this->forward404Unless($resource = Doctrine_Core::getTable('BillBuildUpRateResource')->find($request->getParameter('resource_id')));

            $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('BillBuildUpRateItem')->find($request->getParameter('prev_item_id')) : null;

            $priority   = $previousItem ? $previousItem->priority + 1 : 0;
            $billItemId = $request->getParameter('relation_id');

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
            $this->forward404Unless($nextItem = Doctrine_Core::getTable('BillBuildUpRateItem')->find($request->getParameter('before_id')));

            $billItemId = $nextItem->bill_item_id;
            $resourceId = $nextItem->build_up_rate_resource_id;
            $priority   = $nextItem->priority;
        }

        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('BillBuildUpRateItem')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('bill_item_id = ?', $billItemId)
                ->andWhere('build_up_rate_resource_id = ?', $resourceId)
                ->execute();

            $item->bill_item_id              = $billItemId;
            $item->build_up_rate_resource_id = $resourceId;
            $item->priority                  = $priority;

            $item->save($con);

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new BillBuildUpRateFormulatedColumn();
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
            $data['uom_symbol']  = $item->uom_id > 0 ? Doctrine_Core::getTable('UnitOfMeasurement')->find($item->uom_id)->symbol : '';
            $data['relation_id'] = $billItemId;
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

                if ( $constant == BillBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == BillBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
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
                    'relation_id' => $billItemId,
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

            $totalBuildUp = $item->BillItem->calculateBuildUpTotalByResourceId($item->build_up_rate_resource_id);
        }
        catch (Exception $e)
        {
            $con->rollback();
            $totalBuildUp = 0;
            $errorMsg     = $e->getMessage();
            $success      = false;
        }

        $results = array(
            'success'        => $success,
            'items'          => $items,
            'total_build_up' => $totalBuildUp,
            'errorMsg'       => $errorMsg
        );

        return $this->renderJson($results);
    }

    public function executeBuildUpRateItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $buildUpItem = Doctrine_Core::getTable('BillBuildUpRateItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        try
        {
            $billItem      = $buildUpItem->BillItem;
            $item['id']    = $buildUpItem->id;
            $resourceId    = $buildUpItem->build_up_rate_resource_id;
            $affectedNodes = $buildUpItem->delete();

            $billItem->refresh();

            $totalBuildUp = $billItem->calculateBuildUpTotalByResourceId($resourceId);

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg      = $e->getMessage();
            $item          = array();
            $affectedNodes = array();
            $totalBuildUp  = 0;
            $success       = false;
        }
        $data = array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $item, 'affected_nodes' => $affectedNodes, 'total_build_up' => $totalBuildUp );

        return $this->renderJson($data);
    }

    public function executeBuildUpRateItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $buildUpRateItem = Doctrine_Core::getTable('BillBuildUpRateItem')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $totalBuildUp = 0;
        $success      = false;
        $errorMsg     = null;

        $targetBuildUpRateItem = Doctrine_Core::getTable('BillBuildUpRateItem')->find(intval($request->getParameter('target_id')));
        if ( !$targetBuildUpRateItem )
        {
            $this->forward404Unless($targetBuildUpRateItem = Doctrine_Core::getTable('BillBuildUpRateItem')->find($request->getParameter('prev_item_id')));
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
                    $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillBuildUpRateItem');
                    $newBuildUpRateItem        = $buildUpRateItem->copyTo($targetBuildUpRateItem, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newBuildUpRateItem->id;
                    $data['description'] = $newBuildUpRateItem->description;
                    $data['uom_id']      = $newBuildUpRateItem->uom_id > 0 ? (string) $newBuildUpRateItem->uom_id : '-1';
                    $data['uom_symbol']  = $newBuildUpRateItem->uom_id > 0 ? Doctrine_Core::getTable('UnitOfMeasurement')->find($newBuildUpRateItem->uom_id)->symbol : '';
                    $data['relation_id'] = $newBuildUpRateItem->bill_item_id;
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

                        if ( $constant == BillBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == BillBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
                        {
                            $data[$constant . '-linked'] = $formulatedColumn ? $formulatedColumn['linked'] : false;
                        }
                    }

                    $data['total']      = $newBuildUpRateItem->calculateTotal();
                    $data['line_total'] = $newBuildUpRateItem->calculateLineTotal();

                    $totalBuildUp = $newBuildUpRateItem->BillItem->calculateBuildUpTotalByResourceId($newBuildUpRateItem->build_up_rate_resource_id);

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

        $results = array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data, 'total_build_up' => $totalBuildUp );

        return $this->renderJson($results);
    }

    public function executeImportResourceItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $resource = Doctrine_Core::getTable('BillBuildUpRateResource')->find($request->getParameter('rid')) and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        $items    = array();
        try
        {
            $ids              = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
            $buildUpRateItems = $billItem->importResourceItems($ids, $resource);

            $form                      = new BaseForm();
            $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillBuildUpRateItem');

            foreach ( $buildUpRateItems as $buildUpRateItem )
            {
                $item = array();

                $item['id']          = $buildUpRateItem->id;
                $item['description'] = $buildUpRateItem->description;
                $item['uom_id']      = $buildUpRateItem->uom_id > 0 ? (string) $buildUpRateItem->uom_id : '-1';
                $item['uom_symbol']  = $buildUpRateItem->uom_id > 0 ? $buildUpRateItem->UnitOfMeasurement->symbol : '';
                $item['relation_id'] = $billItem->id;
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

                    if ( $constant == BillBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == BillBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
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
                'relation_id' => $billItem->id,
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
        $data = array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items );

        return $this->renderJson($data);
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

        $data = array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items );

        return $this->renderJson($data);
    }

    public function executeResourceList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('item_id')));

        $records = DoctrineQuery::create()->select('r.id, r.name, r.resource_library_id')
            ->from('BillBuildUpRateResource r')
            ->where('r.bill_item_id = ?', $billItem->id)
            ->addOrderBy('r.id ASC')
            ->fetchArray();

        foreach ( $records as $key => $record )
        {
            $records[$key]['total_build_up'] = $billItem->calculateBuildUpTotalByResourceId($record['id']);

            unset( $record );
        }

        return $this->renderJson($records);
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

        $data = array(
            'values'  => $values,
            'options' => $options
        );

        return $this->renderJson($data);
    }

    /*
     * Add Resource Category feat.
     */

    public function executeGetResourceList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('item_id'))
        );

        $form = new BaseForm();

        $records = DoctrineQuery::create()->select('r.id, r.name')
            ->from('Resource r')
            ->addOrderBy('r.id ASC')
            ->fetchArray();

        foreach ( $records as $key => $record )
        {
            $isResourceLibraryExists = $billItem->isResourceLibraryExistsInBuildUpRate($record['id']);

            $records[$key]['resource_library_exists'] = $isResourceLibraryExists;
            $records[$key]['_csrf_token']             = $form->getCSRFToken();
        }

        $defaultLastRow = array(
            'id'                      => Constants::GRID_LAST_ROW,
            'name'                    => '',
            'resource_library_exists' => false
        );

        array_push($records, $defaultLastRow);

        $data = array(
            'identifier' => 'id',
            'items'      => $records
        );

        return $this->renderJson($data);
    }

    public function executeResourceCategoryAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('rid')) and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('bid'))
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

        $unitOfMeasurements = array(
            'values'  => $values,
            'options' => $options
        );

        $ProjectRevision  = ProjectRevisionTable::getLatestProjectRevisionFromBillId($billItem->Element->ProjectStructure->root_id, Doctrine_Core::HYDRATE_ARRAY);
        $billVersion      = $ProjectRevision['version'];
        $billLockedStatus = $ProjectRevision['locked_status'];

        try
        {
            $buildUpRateResource = $billItem->createBuildUpRateResourceFromResourceLibrary($resource);

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

        $data = array(
            'resource_library_exists' => $resourceLibraryExists,
            'resource'                => $buildUpRateResource ? $buildUpRateResource->toArray() : null,
            'bill_version'            => $billVersion,
            'bill_locked_status'      => $billLockedStatus,
            'uom'                     => $unitOfMeasurements,
            'error_msg'               => $errorMsg,
            'success'                 => $success
        );

        return $this->renderJson($data);
    }

    public function executeResourceCategoryDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('rid')) and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('bid'))
        );

        try
        {
            $buildUpRateResource = BillBuildUpRateResourceTable::getByResourceLibraryIdAndBillItemId($resource->id, $billItem->id);

            $buildUpRateResourceId = $buildUpRateResource->id;

            if ( $buildUpRateResource )
            {
                $buildUpRateResource->delete();
            }

            /*
             * check after deleting resource, is there any resource left for bill item
             */
            $query = DoctrineQuery::create()->select('r.id')
                ->from('BillBuildUpRateResource r')
                ->where('r.bill_item_id = ?', $billItem->id)
                ->andWhere('r.deleted_at IS NULL')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

            $isLastResource = $query->count() > 0 ? false : true;

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

        $data = array(
            'rid'              => $buildUpRateResourceId,
            'is_last_resource' => $isLastResource,
            'error_msg'        => $errorMsg,
            'success'          => $success
        );

        return $this->renderJson($data);
    }

}
