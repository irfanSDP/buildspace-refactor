<?php

/**
 * billBuildUpFloorArea actions.
 *
 * @package    buildspace
 * @subpackage billBuildUpFloorArea
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class billBuildUpFloorAreaActions extends BaseActions {

    public function executeGetBuildUpFloorAreaItemList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

        $buildUpFloorAreaItems = DoctrineQuery::create()->select('*')
            ->from('BillBuildUpFloorAreaItem i')
            ->where('i.bill_column_setting_id = ?', $billColumnSetting->id)
            ->addOrderBy('i.priority ASC')
            ->execute();

        $form = new BaseForm();

        $items = array();

        $formulatedColumnNames = Utilities::getAllFormulatedColumnConstants('BillBuildUpFloorAreaItem');

        foreach ( $buildUpFloorAreaItems as $buildUpFloorAreaItem )
        {
            $item                = array();
            $item['id']          = $buildUpFloorAreaItem->id;
            $item['description'] = $buildUpFloorAreaItem->description;
            $item['sign']        = (string) $buildUpFloorAreaItem->sign;
            $item['sign_symbol'] = $buildUpFloorAreaItem->getSigntext();
            $item['total']       = $buildUpFloorAreaItem->calculateTotal();
            $item['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnNames as $constant )
            {
                $formulatedColumn                        = $buildUpFloorAreaItem->getFormulatedColumnByName($constant);
                $finalValue                              = $formulatedColumn ? $formulatedColumn->final_value : 0;
                $item[$constant . '-final_value']        = $finalValue;
                $item[$constant . '-value']              = $formulatedColumn ? $formulatedColumn->value : '';
                $item[$constant . '-has_cell_reference'] = $formulatedColumn ? $formulatedColumn->hasCellReference() : false;
                $item[$constant . '-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
            }

            array_push($items, $item);
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'sign'        => (string) BillBuildUpFloorAreaItem::SIGN_POSITIVE,
            'sign_symbol' => BillBuildUpFloorAreaItem::SIGN_POSITIVE_TEXT,
            'total'       => '',
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

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeBuildUpFloorAreaItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $items = array();

        $item = new BillBuildUpFloorAreaItem();

        $con = $item->getTable()->getConnection();

        $isFormulatedColumn = false;

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

            $formulatedColumnNames = Utilities::getAllFormulatedColumnConstants('BillBuildUpFloorAreaItem');

            $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('BillBuildUpFloorAreaItem')->find($request->getParameter('prev_item_id')) : null;

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
            $this->forward404Unless($nextItem = Doctrine_Core::getTable('BillBuildUpFloorAreaItem')->find($request->getParameter('before_id')));

            $billColumnSetting     = $nextItem->BillColumnSetting;
            $formulatedColumnNames = Utilities::getAllFormulatedColumnConstants('BillBuildUpFloorAreaItem');

            $priority = $nextItem->priority;
        }

        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('BillBuildUpFloorAreaItem')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('bill_column_setting_id = ?', $billColumnSetting->id)
                ->execute();

            $item->bill_column_setting_id = $billColumnSetting->id;
            $item->priority               = $priority;

            $item->save($con);

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new BillBuildUpFloorAreaFormulatedColumn();
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
            $data['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnNames as $columnName )
            {
                $formulatedColumn                          = $item->getFormulatedColumnByName($columnName);
                $finalValue                                = $formulatedColumn ? $formulatedColumn->final_value : 0;
                $data[$columnName . '-final_value']        = $finalValue;
                $data[$columnName . '-value']              = $formulatedColumn ? $formulatedColumn->value : '';
                $data[$columnName . '-has_cell_reference'] = $formulatedColumn ? $formulatedColumn->hasCellReference() : false;
                $data[$columnName . '-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
            }

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'sign'        => (string) BillBuildUpFloorAreaItem::SIGN_POSITIVE,
                    'sign_symbol' => BillBuildUpFloorAreaItem::SIGN_POSITIVE_TEXT,
                    'total'       => '',
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

    public function executeBuildUpFloorAreaItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $buildUpFloorAreaItem = Doctrine_Core::getTable('BillBuildUpFloorAreaItem')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetBuildUpFloorAreaItem = Doctrine_Core::getTable('BillBuildUpFloorAreaItem')->find(intval($request->getParameter('target_id')));
        if ( !$targetBuildUpFloorAreaItem )
        {
            $this->forward404Unless($targetBuildUpFloorAreaItem = Doctrine_Core::getTable('BillBuildUpFloorAreaItem')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetBuildUpFloorAreaItem->id == $buildUpFloorAreaItem->id )
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
                    $buildUpFloorAreaItem->moveTo($targetBuildUpFloorAreaItem->priority, $lastPosition);

                    $data['id'] = $buildUpFloorAreaItem->id;

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
                    $formulatedColumnNames   = Utilities::getAllFormulatedColumnConstants('BillBuildUpFloorAreaItem');
                    $newBuildUpFloorAreaItem = $buildUpFloorAreaItem->copyTo($targetBuildUpFloorAreaItem, $lastPosition);

                    $form                = new BaseForm();
                    $data['id']          = $newBuildUpFloorAreaItem->id;
                    $data['description'] = $newBuildUpFloorAreaItem->description;
                    $data['sign']        = (string) $newBuildUpFloorAreaItem->sign;
                    $data['sign_symbol'] = $newBuildUpFloorAreaItem->getSigntext();
                    $data['total']       = $newBuildUpFloorAreaItem->calculateTotal();
                    $data['_csrf_token'] = $form->getCSRFToken();

                    foreach ( $formulatedColumnNames as $constant )
                    {
                        $formulatedColumn                        = $newBuildUpFloorAreaItem->getFormulatedColumnByName($constant);
                        $finalValue                              = $formulatedColumn ? $formulatedColumn->final_value : 0;
                        $data[$constant . '-final_value']        = $finalValue;
                        $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn->value : '';
                        $data[$constant . '-has_cell_reference'] = $formulatedColumn ? $formulatedColumn->hasCellReference() : false;
                        $data[$constant . '-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
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

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data ));
    }

    public function executeBuildUpFloorAreaItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BillBuildUpFloorAreaItem')->find($request->getParameter('id')));

        $rowData = array();
        $con     = $item->getTable()->getConnection();

        $formulatedColumnNames = Utilities::getAllFormulatedColumnConstants('BillBuildUpFloorAreaItem');

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $affectedNodes      = array();
            $isFormulatedColumn = false;

            if ( in_array($fieldName, $formulatedColumnNames) )
            {
                $formulatedColumnTable = Doctrine_Core::getTable('BillBuildUpFloorAreaFormulatedColumn');

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
                        $total = $node->BuildUpFloorAreaItem->calculateTotal();

                        array_push($affectedNodes, array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value,
                            'total'                     => $total
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
                $formulatedColumn                             = $item->getFormulatedColumnByName($columnName);
                $finalValue                                   = $formulatedColumn ? $formulatedColumn->final_value : 0;
                $rowData[$columnName . '-final_value']        = $finalValue;
                $rowData[$columnName . '-value']              = $formulatedColumn ? $formulatedColumn->value : '';
                $rowData[$columnName . '-has_cell_reference'] = $formulatedColumn ? $formulatedColumn->hasCellReference() : false;
                $rowData[$columnName . '-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
            }

            $rowData['sign']           = (string) $item->sign;
            $rowData['sign_symbol']    = $item->getSignText();
            $rowData['total']          = $item->calculateTotal();
            $rowData['affected_nodes'] = $affectedNodes;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $rowData
        ));
    }

    public function executeBuildUpFloorAreaItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $buildUpItem = Doctrine_Core::getTable('BillBuildUpFloorAreaItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        try
        {
            $item['id']    = $buildUpItem->id;
            $affectedNodes = $buildUpItem->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg      = $e->getMessage();
            $item          = array();
            $affectedNodes = array();
            $success       = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $item, 'affected_nodes' => $affectedNodes ));
    }

    public function executeGetBuildUpSummary(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

        BillBuildUpFloorAreaSummaryTable::createByBillColumnSettingId($billColumnSetting->id);

        $buildUpFloorAreaSummary = BillBuildUpFloorAreaSummaryTable::getByBillColumnSettingId($billColumnSetting->id);

        $form = new BaseForm();

        return $this->renderJson(array(
            'apply_conversion_factor'    => $buildUpFloorAreaSummary->apply_conversion_factor,
            'conversion_factor_amount'   => number_format($buildUpFloorAreaSummary->conversion_factor_amount, 2, '.', ''),
            'conversion_factor_operator' => $buildUpFloorAreaSummary->conversion_factor_operator,
            'rounding_type'              => $buildUpFloorAreaSummary->rounding_type,
            '_csrf_token'                => $form->getCSRFToken(),
            'total_floor_area'           => number_format($buildUpFloorAreaSummary->calculateTotalFloorArea(), 2, '.', ''),
            'final_floor_area'           => number_format($buildUpFloorAreaSummary->getTotalFloorAreaAfterConversion(), 2, '.', '')
        ));
    }

    public function executeBuildUpSummaryApplyConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

        $value = $request->getParameter('value');

        $buildUpFloorAreaSummary = BillBuildUpFloorAreaSummaryTable::getByBillColumnSettingId($billColumnSetting->id);

        $buildUpFloorAreaSummary->apply_conversion_factor = $value;
        $buildUpFloorAreaSummary->save();

        $buildUpFloorAreaSummary->refresh();

        return $this->renderJson(array(
            'conversion_factor_amount'   => number_format($buildUpFloorAreaSummary->conversion_factor_amount, 2, '.', ''),
            'conversion_factor_operator' => $buildUpFloorAreaSummary->conversion_factor_operator,
            'total_floor_area'           => number_format($buildUpFloorAreaSummary->calculateTotalFloorArea(), 2, '.', ''),
            'final_floor_area'           => number_format($buildUpFloorAreaSummary->getTotalFloorAreaAfterConversion(), 2, '.', '')
        ));
    }

    public function executeBuildUpSummaryConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

        $buildUpFloorAreaSummary = BillBuildUpFloorAreaSummaryTable::getByBillColumnSettingId($billColumnSetting->id);

        $val = $request->getParameter('val');

        switch ($request->getParameter('token'))
        {
            case 'amount':
                $conversionFactorAmount                            = strlen($val) > 0 ? floatval($val) : 0;
                $buildUpFloorAreaSummary->conversion_factor_amount = $conversionFactorAmount;
                break;
            case 'operator':
                $buildUpFloorAreaSummary->conversion_factor_operator = $val;
                break;
            default:
                break;
        }

        $buildUpFloorAreaSummary->save();
        $buildUpFloorAreaSummary->refresh();

        return $this->renderJson(array(
            'conversion_factor_amount'   => number_format($buildUpFloorAreaSummary->conversion_factor_amount, 2, '.', ''),
            'final_floor_area'           => number_format($buildUpFloorAreaSummary->getTotalFloorAreaAfterConversion(), 2, '.', ''),
            'conversion_factor_operator' => $buildUpFloorAreaSummary->conversion_factor_operator
        ));
    }

    public function executeBuildUpSummaryRoundingUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

        $buildUpFloorAreaSummary = BillBuildUpFloorAreaSummaryTable::getByBillColumnSettingId($billColumnSetting->id);

        $buildUpFloorAreaSummary->rounding_type = $request->getParameter('rounding_type');

        $buildUpFloorAreaSummary->save();
        $buildUpFloorAreaSummary->refresh();

        return $this->renderJson(array(
            'final_floor_area' => number_format($buildUpFloorAreaSummary->getTotalFloorAreaAfterConversion(), 2, '.', ''),
            'rounding_type'    => $buildUpFloorAreaSummary->rounding_type
        ));
    }

}