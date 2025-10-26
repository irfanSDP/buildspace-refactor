<?php

/**
 * billBuildUpQuantity actions.
 *
 * @package    buildspace
 * @subpackage billBuildUpQuantity
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class billBuildUpQuantityActions extends BaseActions {

    public function executeGetBuildUpQuantityItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('bill_item_id')) and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
        );

        $buildUpQuantityItems = DoctrineQuery::create()->select('i.id, i.description, i.sign, i.total, ifc.column_name, ifc.value, ifc.final_value')
            ->from('BillBuildUpQuantityItem i')
            ->leftJoin('i.FormulatedColumns ifc')
            ->where('i.bill_item_id = ?', $billItem->id)
            ->andWhere('i.bill_column_setting_id = ?', $billColumnSetting->id)
            ->andWhere('i.type = ?', $request->getParameter('type'))
            ->addOrderBy('i.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        $formulatedColumnNames = BillBuildUpQuantityItemTable::getFormulatedColumnNames($billItem->UnitOfMeasurement);

        foreach ( $buildUpQuantityItems as $key => $buildUpQuantityItem )
        {
            $buildUpQuantityItems[$key]['sign']        = (string) $buildUpQuantityItem['sign'];
            $buildUpQuantityItems[$key]['sign_symbol'] = BillBuildUpQuantityItemTable::getSignTextBySign($buildUpQuantityItem['sign']);
            $buildUpQuantityItems[$key]['relation_id'] = $billItem->id;
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
            'sign'        => (string) BillBuildUpQuantityItem::SIGN_POSITIVE,
            'sign_symbol' => BillBuildUpQuantityItem::SIGN_POSITIVE_TEXT,
            'relation_id' => $billItem->id,
            'total'       => '',
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

        $data = array(
            'identifier' => 'id',
            'items'      => $buildUpQuantityItems
        );

        return $this->renderJson($data);
    }

    public function executeGetBuildUpSummary(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->hasParameter('type') and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
        );

        $buildUpQuantitySummary = BillBuildUpQuantitySummaryTable::createByBillItemIdAndBillColumnSettingId($billItem->id, $billColumnSetting->id, $request->getParameter('type'));

        return $this->renderJson(array(
            'apply_conversion_factor'    => $buildUpQuantitySummary->apply_conversion_factor,
            'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
            'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator,
            'linked_total_quantity'      => number_format($buildUpQuantitySummary->linked_total_quantity, 2, '.', ''),
            'total_quantity'             => number_format($buildUpQuantitySummary->calculateTotalQuantity(), 2, '.', ''),
            'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
            'rounding_type'              => ( $buildUpQuantitySummary->rounding_type ) ? $buildUpQuantitySummary->rounding_type : $billItem->Element->ProjectStructure->BillSetting->build_up_quantity_rounding_type
        ));
    }


    public function executeBuildUpSummaryRoundingUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

        $buildUpQuantitySummary = BillBuildUpQuantitySummaryTable::getByBillItemIdAndBillColumnSettingId($billItem->id, $billColumnSetting->id, $request->getParameter('type'));

        $buildUpQuantitySummary->rounding_type = $request->getParameter('rounding_type');

        $buildUpQuantitySummary->save();
        $buildUpQuantitySummary->refresh();

        return $this->renderJson(array(
            'total_quantity' => number_format($buildUpQuantitySummary->calculateTotalQuantity(), 2, '.', ''),
            'final_quantity' => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
            'rounding_type'  => $buildUpQuantitySummary->rounding_type
        ));
    }

    public function executeBuildUpSummaryApplyConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

        $value = $request->getParameter('value');

        $buildUpQuantitySummary = BillBuildUpQuantitySummaryTable::getByBillItemIdAndBillColumnSettingId($billItem->id, $billColumnSetting->id, $request->getParameter('type'));

        $buildUpQuantitySummary->apply_conversion_factor = $value;
        $buildUpQuantitySummary->save();

        $buildUpQuantitySummary->refresh();

        return $this->renderJson(array(
            'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
            'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator,
            'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', '')
        ));
    }

    public function executeBuildUpSummaryConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

        $buildUpQuantitySummary = BillBuildUpQuantitySummaryTable::getByBillItemIdAndBillColumnSettingId($billItem->id, $billColumnSetting->id, $request->getParameter('type'));

        $val = $request->getParameter('val');

        switch ($request->getParameter('token'))
        {
            case 'amount':
                $conversionFactorAmount                           = strlen($val) > 0 ? floatval($val) : 0;
                $buildUpQuantitySummary->conversion_factor_amount = $conversionFactorAmount;
                break;
            case 'operator':
                $buildUpQuantitySummary->conversion_factor_operator = $val;
                break;
            default:
                break;
        }

        $buildUpQuantitySummary->save();
        $buildUpQuantitySummary->refresh();

        return $this->renderJson(array(
            'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
            'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
            'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator
        ));
    }

    public function executeBuildUpQuantityItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $items = array();

        $item = new BillBuildUpQuantityItem();

        $con = $item->getTable()->getConnection();

        $isFormulatedColumn = false;

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $this->forward404Unless($billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('relation_id')) and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

            $formulatedColumnNames = BillBuildUpQuantityItemTable::getFormulatedColumnNames($billItem->UnitOfMeasurement);

            $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('BillBuildUpQuantityItem')->find($request->getParameter('prev_item_id')) : null;

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
            $this->forward404Unless($nextItem = Doctrine_Core::getTable('BillBuildUpQuantityItem')->find($request->getParameter('before_id')));

            $billItem              = $nextItem->BillItem;
            $billColumnSetting     = $nextItem->BillColumnSetting;
            $formulatedColumnNames = BillBuildUpQuantityItemTable::getFormulatedColumnNames($billItem->UnitOfMeasurement);

            $priority = $nextItem->priority;
        }

        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('BillBuildUpQuantityItem')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('bill_item_id = ?', $billItem->id)
                ->andWhere('bill_column_setting_id = ?', $billColumnSetting->id)
                ->andWhere('type = ?', $request->getParameter('type'))
                ->execute();

            $item->bill_item_id           = $billItem->id;
            $item->bill_column_setting_id = $billColumnSetting->id;
            $item->priority               = $priority;
            $item->type                   = $request->getParameter('type');

            $item->save($con);

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new BillBuildUpQuantityFormulatedColumn();
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
            $data['relation_id'] = $billItem->id;
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
                    'sign'        => (string) BillBuildUpQuantityItem::SIGN_POSITIVE,
                    'sign_symbol' => BillBuildUpQuantityItem::SIGN_POSITIVE_TEXT,
                    'relation_id' => $billItem->id,
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

    public function executeBuildUpQuantityItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $item = Doctrine_Core::getTable('BillBuildUpQuantityItem')->find($request->getParameter('id')));

        $rowData = array();
        $con     = $item->getTable()->getConnection();

        $billItem = $item->BillItem;

        $formulatedColumnNames = BillBuildUpQuantityItemTable::getFormulatedColumnNames($billItem->UnitOfMeasurement);

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $affectedNodes      = array();
            $isFormulatedColumn = false;

            if ( in_array($fieldName, $formulatedColumnNames) )
            {
                $formulatedColumnTable = Doctrine_Core::getTable('BillBuildUpQuantityFormulatedColumn');

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

            $billItem->Element->updateAllItemTotalAfterMarkup();
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

    public function executeBuildUpQuantityItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $buildUpItem = Doctrine_Core::getTable('BillBuildUpQuantityItem')->find($request->getParameter('id'))
        );

        try
        {
            $item['id']    = $buildUpItem->id;
            $affectedNodes = $buildUpItem->delete();

            $success  = true;
            $errorMsg = null;
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

    public function executeBuildUpQuantityItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $buildUpQuantityItem = Doctrine_Core::getTable('BillBuildUpQuantityItem')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetBuildUpQuantityItem = Doctrine_Core::getTable('BillBuildUpQuantityItem')->find(intval($request->getParameter('target_id')));
        if ( !$targetBuildUpQuantityItem )
        {
            $this->forward404Unless($targetBuildUpQuantityItem = Doctrine_Core::getTable('BillBuildUpQuantityItem')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetBuildUpQuantityItem->id == $buildUpQuantityItem->id )
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
                    $buildUpQuantityItem->moveTo($targetBuildUpQuantityItem->priority, $lastPosition);

                    $data['id'] = $buildUpQuantityItem->id;

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
                    $formulatedColumnNames  = BillBuildUpQuantityItemTable::getFormulatedColumnNames($buildUpQuantityItem->BillItem->UnitOfMeasurement);
                    $newBuildUpQuantityItem = $buildUpQuantityItem->copyTo($targetBuildUpQuantityItem, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newBuildUpQuantityItem->id;
                    $data['description'] = $newBuildUpQuantityItem->description;
                    $data['sign']        = (string) $newBuildUpQuantityItem->sign;
                    $data['sign_symbol'] = $newBuildUpQuantityItem->getSigntext();
                    $data['relation_id'] = $newBuildUpQuantityItem->bill_item_id;
                    $data['total']       = $newBuildUpQuantityItem->calculateTotal();
                    $data['_csrf_token'] = $form->getCSRFToken();

                    foreach ( $formulatedColumnNames as $constant )
                    {
                        $formulatedColumn                        = $newBuildUpQuantityItem->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                        $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                        $data[$constant . '-final_value']        = $finalValue;
                        $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                        $data[$constant . '-has_cell_reference'] = false;
                        $data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
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

    public function executeGetDimensionColumnStructure(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $unitOfMeasurement = Doctrine_Core::getTable('UnitOfMeasurement')->find($request->getParameter('uom_id')));

        $xrefs = Doctrine_Core::getTable('UnitOfMeasurementDimensions')
            ->createQuery('m')
            ->leftJoin('m.Dimension d')
            ->addWhere('m.unit_of_measurement_id = ?', $unitOfMeasurement->id)
            ->orderBy('m.priority')
            ->fetchArray();

        $items = array();

        foreach ( $xrefs as $reference )
        {
            $data['title']      = $reference['Dimension']['name'];
            $data['field_name'] = $reference['Dimension']['id'] . '-dimension_column-value';

            array_push($items, $data);
        }

        return $this->renderJson($items);
    }

    public function executeGetAffectedBillItems(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

        $billItem->updateBillItemTotalColumns();

        $bill                    = $billItem->Element->ProjectStructure;
        $elementMarkupPercentage = $billItem->Element->getFormulatedColumnByName(BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE, Doctrine_Core::HYDRATE_ARRAY);
        $elementMarkupPercentage = $elementMarkupPercentage ? $elementMarkupPercentage['final_value'] : 0;

        $billMarkupSetting = $bill->BillMarkupSetting;

        $markupSettingsInfo = array(
            'bill_markup_enabled'       => $billMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage'    => $billMarkupSetting->bill_markup_percentage,
            'element_markup_enabled'    => $billMarkupSetting->element_markup_enabled,
            'element_markup_percentage' => $elementMarkupPercentage,
            'item_markup_enabled'       => $billMarkupSetting->item_markup_enabled,
            'rounding_type'             => $billMarkupSetting->rounding_type
        );

        $grandTotalAfterMarkup = 0;
        $rateAfterMarkup       = BillItemTable::calculateRateAfterMarkupById($billItem->id, $markupSettingsInfo);
        $markupAmountColumn    = $billItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

        $items = array();

        $item['id']                        = $billItem->id;
        $item['grand_total_quantity']      = $billItem->grand_total_quantity;
        $item['grand_total']               = $billItem->grand_total;
        $item['markup_amount-value']       = $markupAmountColumn ? $markupAmountColumn['value'] : 0;
        $item['markup_amount-final_value'] = $markupAmountColumn ? $markupAmountColumn['final_value'] : 0;
        $item['rate_after_markup']         = $rateAfterMarkup;

        $billItemTypeFormulatedColumns = array(
            BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT,
            BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT
        );

        $referencedNodes = array();

        foreach ( $bill->BillColumnSettings as $column )
        {
            $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

            $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($billItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

            $quantity     = $billItemTypeRef ? BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY) : false;
            $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
            $total        = $totalPerUnit * $column->quantity;

            $item[$column->id . '-total_quantity']               = $billItemTypeRef ? $billItemTypeRef['total_quantity'] : '';
            $item[$column->id . '-quantity_per_unit_difference'] = $billItemTypeRef ? $billItemTypeRef['quantity_per_unit_difference'] : '';
            $item[$column->id . '-total_per_unit']               = $totalPerUnit;
            $item[$column->id . '-total']                        = $total;

            $grandTotalAfterMarkup += $total;

            foreach ( $billItemTypeFormulatedColumns as $columnName )
            {
                $formulatedColumn                                        = $billItemTypeRef ? BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $columnName) : false;
                $finalValue                                              = $formulatedColumn ? $formulatedColumn->final_value : null;
                $item[$column->id . '-' . $columnName . '-final_value']  = $finalValue;
                $item[$column->id . '-' . $columnName . '-value']        = $formulatedColumn ? $formulatedColumn->getConvertedValue() : '';
                $item[$column->id . '-' . $columnName . '-has_build_up'] = $formulatedColumn ? $formulatedColumn->has_build_up : false;

                if ( $formulatedColumn )
                {
                    $referencedNodes += $formulatedColumn->getNodesRelatedByColumnName($columnName);
                }
            }
        }

        $item['grand_total_after_markup'] = $grandTotalAfterMarkup;

        array_push($items, $item);

        $formulatedColumnTable = Doctrine_Core::getTable('BillItemTypeReferenceFormulatedColumn');

        foreach ( $referencedNodes as $referencedNode )
        {
            $node = $formulatedColumnTable->find($referencedNode['node_from']);

            if ( $node )
            {
                $grandTotalAfterMarkup = 0;

                $billItemNode = $node->BillItemTypeReference->BillItem;
                $billItemNode->updateBillItemTotalColumns();

                $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($billItemNode->id, $markupSettingsInfo);
                $markupAmountColumn = $billItemNode->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                $item['id']                        = $billItemNode->id;
                $item['grand_total_quantity']      = $billItemNode->grand_total_quantity;
                $item['grand_total']               = $billItemNode->grand_total;
                $item['markup_amount-value']       = $markupAmountColumn ? $markupAmountColumn['value'] : 0;
                $item['markup_amount-final_value'] = $markupAmountColumn ? $markupAmountColumn['final_value'] : 0;
                $item['rate_after_markup']         = $rateAfterMarkup;

                foreach ( $bill->BillColumnSettings as $column )
                {
                    $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;
                    $billItemTypeRef   = BillItemTypeReferenceTable::getByItemIdAndColumnId($billItemNode->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                    if ( $billItemTypeRef && $billItemTypeRef['include'] )//we only update columns where 'include' is true
                    {
                        $quantity     = $billItemTypeRef ? BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY) : false;
                        $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                        $total        = $totalPerUnit * $column->quantity;

                        $item[$column->id . '-total_quantity']               = $billItemTypeRef ? $billItemTypeRef['total_quantity'] : '';
                        $item[$column->id . '-quantity_per_unit_difference'] = $billItemTypeRef ? $billItemTypeRef['quantity_per_unit_difference'] : '';
                        $item[$column->id . '-total_per_unit']               = $totalPerUnit;
                        $item[$column->id . '-total']                        = $total;

                        $grandTotalAfterMarkup += $total;

                        foreach ( $billItemTypeFormulatedColumns as $columnName )
                        {
                            $formulatedColumn                                        = $billItemTypeRef ? BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $columnName) : false;
                            $finalValue                                              = $formulatedColumn ? $formulatedColumn->final_value : null;
                            $item[$column->id . '-' . $columnName . '-final_value']  = $finalValue;
                            $item[$column->id . '-' . $columnName . '-value']        = $formulatedColumn ? $formulatedColumn->getConvertedValue() : '';
                            $item[$column->id . '-' . $columnName . '-has_build_up'] = $formulatedColumn ? $formulatedColumn->has_build_up : false;
                        }
                    }
                }

                $item['grand_total_after_markup'] = $grandTotalAfterMarkup;
                array_push($items, $item);
            }
        }

        return $this->renderJson(array( 'items' => $items ));
    }

    public function executeGetLinkInfo(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bcid')) and
            $request->hasParameter('t')

        );

        $scheduleOfQuantityLinks = ScheduleOfQuantityBillItemXrefTable::getInstance()
            ->createQuery('x')->select('x.schedule_of_quantity_item_id')
            ->where('x.bill_item_id = ?', $billItem->id)
            ->andWhere('x.bill_column_setting_id = ?', $billColumnSetting->id)
            ->andWhere('x.type = ?', $request->getParameter('t'))
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->count();

        $hasLinkedQty = $scheduleOfQuantityLinks > 0 ? true : false;

        return $this->renderJson(array( 'has_linked_qty' => $hasLinkedQty ));
    }

    public function executeGetScheduleOfQuantities(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bcid')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case BillBuildUpQuantityItem::QUANTITY_PER_UNIT_ORIGINAL:
                $type = BillBuildUpQuantityItem::QUANTITY_PER_UNIT_ORIGINAL;
                break;
            case BillBuildUpQuantityItem::QUANTITY_PER_UNIT_REMEASUREMENT:
                $type = BillBuildUpQuantityItem::QUANTITY_PER_UNIT_REMEASUREMENT;
                break;
            default:
                throw new Exception("invalid quantity type");
        }

        $pdo = $billItem->getTable()->getConnection()->getDbh();

        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.description, p.type, p.lft, p.level, p.priority, p.third_party_identifier, p.schedule_of_quantity_trade_id,
            p.identifier_type, p.uom_id AS uom_id, uom.symbol AS uom_symbol
            FROM " . ScheduleOfQuantityBillItemXrefTable::getInstance()->getTableName() . " xref
            JOIN " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " c ON c.id = xref.schedule_of_quantity_item_id
            JOIN " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN " . ScheduleOfQuantityTradeTable::getInstance()->getTableName() . " trade ON p.schedule_of_quantity_trade_id = trade.id
            JOIN " . ScheduleOfQuantityTable::getInstance()->getTableName() . " soq ON trade.schedule_of_quantity_id = soq.id
            WHERE xref.bill_item_id = " . $billItem->id . " AND xref.bill_column_setting_id = " . $billColumnSetting->id . " AND xref.type = " . $type . "
            AND c.root_id = p.root_id AND c.schedule_of_quantity_trade_id = p.schedule_of_quantity_trade_id AND c.deleted_at IS NULL AND p.deleted_at IS NULL
            AND soq.project_structure_id = " . $billColumnSetting->ProjectStructure->root_id . " AND trade.deleted_at IS NULL AND soq.deleted_at IS NULL
            ORDER BY p.schedule_of_quantity_trade_id, p.priority, p.lft, p.level ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value, ifc.linked, ifc.has_build_up
            FROM " . ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName() . " ifc
            JOIN " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
            JOIN " . ScheduleOfQuantityBillItemXrefTable::getInstance()->getTableName() . " xref ON i.id = xref.schedule_of_quantity_item_id
            WHERE xref.bill_item_id = " . $billItem->id . " AND xref.bill_column_setting_id = " . $billColumnSetting->id . " AND xref.type = " . $type . " AND i.deleted_at IS NULL
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

        foreach ( $items as $key => $item )
        {
            $items[$key]['type']               = (string) $item['type'];
            $items[$key]['uom_id']             = $item['uom_id'] > 0 ? (string) $item['uom_id'] : '-1';
            $items[$key]['editable_total']     = ( $item['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($item['id'], true) : 0;
            $items[$key]['non_editable_total'] = ( $item['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($item['id'], false) : 0;
            $items[$key]['_csrf_token']        = $form->getCSRFToken();

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
            'updated_at'                                                               => '-',
            '_csrf_token'                                                              => $form->getCSRFToken(),
            'editable_total'                                                           => 0,
            'non_editable_total'                                                       => 0,
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

    public function executeScheduleOfQuantityDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $scheduleOfQuantityItem = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('id')) and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('bid')) and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bcid')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case BillBuildUpQuantityItem::QUANTITY_PER_UNIT_ORIGINAL:
                $type = BillBuildUpQuantityItem::QUANTITY_PER_UNIT_ORIGINAL;
                break;
            case BillBuildUpQuantityItem::QUANTITY_PER_UNIT_REMEASUREMENT:
                $type = BillBuildUpQuantityItem::QUANTITY_PER_UNIT_REMEASUREMENT;
                break;
            default:
                throw new Exception("invalid quantity type");
        }

        try
        {
            $item['id']    = $scheduleOfQuantityItem->id;
            $affectedNodes = $scheduleOfQuantityItem->unlinkFromBillBuildUpQuantity($billItem->id, $billColumnSetting->id, $type);

            $buildUpQuantitySummary = BillBuildUpQuantitySummaryTable::createByBillItemIdAndBillColumnSettingId($billItem->id, $billColumnSetting->id, $type);

            $buildUpQuantitySummary->linked_total_quantity = $billItem->getScheduleOfQuantitiesTotalAmount($billColumnSetting, $type);

            $buildUpQuantitySummary->save();

            $success  = true;
            $errorMsg = null;
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

    public function executeGetScheduleOfQuantityInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $scheduleOfQuantityItem = Doctrine_Core::getTable('ScheduleOfQuantityItem')->find($request->getParameter('id'))
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => array( array(
                'id'    => 'soq_' . $scheduleOfQuantityItem->Trade->schedule_of_quantity_id,
                'title' => $scheduleOfQuantityItem->Trade->ScheduleOfQuantity->title,
                'type'  => 1,
                'level' => 0
            ), array(
                'id'    => 'trade_' . $scheduleOfQuantityItem->schedule_of_quantity_trade_id,
                'title' => $scheduleOfQuantityItem->Trade->description,
                'type'  => 2,
                'level' => 1
            ) )
        ));
    }

}
