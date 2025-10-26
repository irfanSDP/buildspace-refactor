<?php

/**
 * billManager actions.
 *
 * @package    buildspace
 * @subpackage billManager
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class billManagerActions extends BaseActions {

    public function executeGetColumnSettingCount(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        return $this->renderJson(array( 'c' => $bill->BillColumnSettings->count() ));
    }

    public function executeShowHideBillColumnSetting(sfWebRequest $request)
    {
        $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('id'));

        $con = $billColumnSetting->getTable()->getConnection();

        $billColumnSetting->is_hidden = $request->getParameter('is_hidden');

        $billColumnSetting->save($con);

        return $this->renderJson(array(
            'id'        => $billColumnSetting->id,
            'title'     => $billColumnSetting->name,
            'type'      => $billColumnSetting->quantity,
            'is_hidden' => $billColumnSetting->is_hidden
        ));
    }

    public function executeBillPropertiesForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $billMarkupSettingForm = new BillMarkupSettingForm();
        $billColumnSettingForm = new BillColumnSettingForm();
        $billSettingForm       = new BillSettingForm();

        $data['column_settings'] = array();
        $data['markup_settings'] = array(
            'bill_markup_enabled'    => $structure->BillMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage' => number_format($structure->BillMarkupSetting->bill_markup_percentage, 2, '.', ''),
            'bill_markup_amount'     => number_format($structure->BillMarkupSetting->bill_markup_amount, 2, '.', ''),
            'element_markup_enabled' => $structure->BillMarkupSetting->element_markup_enabled,
            'item_markup_enabled'    => $structure->BillMarkupSetting->item_markup_enabled,
            'rounding_type'          => $structure->BillMarkupSetting->rounding_type
        );

        $data['bill_setting'] = array(
            'bill_setting[title]'                           => $structure->BillSetting->title,
            'bill_setting[description]'                     => $structure->BillSetting->description,
            'bill_setting[unit_type]'                       => $structure->BillSetting->unit_type,
            'billType'                                      => $structure->BillType->type,
            'unit_type_text'                                => $structure->BillSetting->UnitOfMeasurementType->name,
            'bill_setting[build_up_rate_rounding_type]'     => $structure->BillSetting->build_up_rate_rounding_type,
            'bill_setting[build_up_quantity_rounding_type]' => $structure->BillSetting->build_up_quantity_rounding_type,
            'bill_setting[_csrf_token]'                     => $billSettingForm->getCSRFToken()
        );

        $records = DoctrineQuery::create()->select('c.id, c.name, c.quantity, c.is_hidden, c.remeasurement_quantity_enabled, c.floor_area_has_build_up, c.total_floor_area_m2, c.total_floor_area_ft2, c.floor_area_use_metric, c.floor_area_display_metric, c.show_estimated_total_cost, c.use_original_quantity')
            ->from('BillColumnSetting c')
            ->where('c.project_structure_id = ?', $structure->id)
            ->addOrderBy('c.id ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $data['column_settings'] = $records;

        $data['markup_settings_csrf_token'] = $billMarkupSettingForm->getCSRFToken();
        $data['column_settings_csrf_token'] = $billColumnSettingForm->getCSRFToken();

        return $this->renderJson($data);
    }

    public function executeBillPropertiesUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $parent      = $structure->node->getParent();
        $billSetting = $structure->BillSetting;

        $form = new BillSettingForm($billSetting, array( 'parent' => $parent ));

        if ( $this->isFormValid($request, $form) )
        {
            $billSetting = $form->save();
            $form        = new BaseForm();

            $item = array(
                'id'                              => $structure->id,
                'title'                           => $billSetting->title,
                'description'                     => $billSetting->description,
                'type'                            => $structure->type,
                'unit_type'                       => $billSetting->unit_type,
                'build_up_rate_rounding_type'     => $billSetting->build_up_rate_rounding_type,
                'build_up_quantity_rounding_type' => $billSetting->build_up_quantity_rounding_type,
                '_csrf_token'                     => $form->getCSRFToken()
            );

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $item    = array();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'item' => $item ));
    }

    public function executeBillMarkupSettingUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')));

        $form = new BillMarkupSettingForm($projectStructure->BillMarkupSetting, array( 'type' => $request->getParameter('type') ));

        if ( $this->isFormValid($request, $form) )
        {
            $billMarkupSetting = $form->save();

            $item = array(
                'bill_markup_enabled'    => $billMarkupSetting->bill_markup_enabled,
                'bill_markup_percentage' => number_format($billMarkupSetting->bill_markup_percentage, 2, '.', ''),
                'bill_markup_amount'     => number_format($billMarkupSetting->bill_markup_amount, 2, '.', ''),
                'element_markup_enabled' => $billMarkupSetting->element_markup_enabled,
                'item_markup_enabled'    => $billMarkupSetting->item_markup_enabled,
                'rounding_type'          => $billMarkupSetting->rounding_type
            );

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors = $form->getErrors();

            $item    = array();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'data' => $item ));
    }

    public function executeGetBillColumnSetting(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('id')));

        return $this->renderJson(array( 'item' => array(
            'id'                             => $billColumnSetting->id,
            'name'                           => $billColumnSetting->name,
            'quantity'                       => $billColumnSetting->quantity,
            'remeasurement_quantity_enabled' => $billColumnSetting->remeasurement_quantity_enabled,
            'total_floor_area_m2'            => $billColumnSetting->total_floor_area_m2,
            'total_floor_area_ft2'           => $billColumnSetting->total_floor_area_ft2,
            'floor_area_has_build_up'        => $billColumnSetting->floor_area_has_build_up,
            'floor_area_use_metric'          => $billColumnSetting->floor_area_use_metric,
            'floor_area_display_metric'      => $billColumnSetting->floor_area_display_metric,
            'show_estimated_total_cost'      => $billColumnSetting->show_estimated_total_cost,
            'use_original_quantity'          => $billColumnSetting->use_original_quantity,
            'is_hidden'                      => $billColumnSetting->is_hidden
        ) ));
    }

    public function executeBillColumnSettingUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('id'));
        $form              = new BillColumnSettingForm($billColumnSetting);

        if ( $this->isFormValid($request, $form) )
        {
            $billColumnSetting = $form->save();

            $item = array(
                'id'                             => $billColumnSetting->id,
                'name'                           => $billColumnSetting->name,
                'quantity'                       => $billColumnSetting->quantity,
                'remeasurement_quantity_enabled' => $billColumnSetting->remeasurement_quantity_enabled,
                'total_floor_area_m2'            => $billColumnSetting->total_floor_area_m2,
                'total_floor_area_ft2'           => $billColumnSetting->total_floor_area_ft2,
                'floor_area_has_build_up'        => $billColumnSetting->floor_area_has_build_up,
                'floor_area_use_metric'          => $billColumnSetting->floor_area_use_metric,
                'floor_area_display_metric'      => $billColumnSetting->floor_area_display_metric,
                'show_estimated_total_cost'      => $billColumnSetting->show_estimated_total_cost,
                'use_original_quantity'          => $billColumnSetting->use_original_quantity,
                'is_hidden'                      => $billColumnSetting->is_hidden
            );

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors = $form->getErrors();

            $item    = array();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'item' => $item ));
    }

    public function executeBillColumnSettingDelete(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('id')));

        $errorMsg = null;

        try
        {
            $billColumnSetting->delete();
            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    /*** start bill element actions ***/
    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $elements = DoctrineQuery::create()->select('e.id, e.description, e.note, e.project_revision_id, fc.column_name, fc.value, fc.final_value')
            ->from('BillElement e')->leftJoin('e.FormulatedColumns fc')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $currentlyEditingProjectRevision = ProjectRevisionTable::getCurrentlyEditingProjectRevisionFromBillId($bill->getRoot()->id);

        $form                      = new BaseForm();
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillElement');

        $billMarkupSetting = $bill->BillMarkupSetting;

        //We get All Element Sum Group By Element Here so that we don't have to reapeat query within element loop
        $markupSettingsInfo = array(
            'bill_markup_enabled'    => $billMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage' => $billMarkupSetting->bill_markup_percentage,
            'element_markup_enabled' => $billMarkupSetting->element_markup_enabled,
            'item_markup_enabled'    => $billMarkupSetting->item_markup_enabled,
            'rounding_type'          => $billMarkupSetting->rounding_type > 0 ? $billMarkupSetting->rounding_type : BillMarkupSetting::ROUNDING_TYPE_DISABLED
        );

        $elementSumByBillColumnSetting = array();

        //we get sum of elements total by bill column setting so we won't keep on calling the same query in element list loop
        foreach ( $bill->BillColumnSettings as $column )
        {
            //Get Element Total Rates
            $ElementTotalRates                          = ProjectStructureTable::getTotalItemRateByAndBillColumnSettingIdGroupByElement($bill, $column);
            $elementSumByBillColumnSetting[$column->id] = $ElementTotalRates['grandTotalElement'];
            $totalRateByBillColumnSetting[$column->id]  = $ElementTotalRates['elementToRates'];
            unset( $column );
        }

        $elementGrandTotals    = ProjectStructureTable::getElementGrandTotalByBillIdGroupByElement($bill->id);//Get Element Grand Totals
        $elementsWithAddendums = $bill->getElementsWithAddendums();
        $latestProjectRevision = $bill->getRoot()->getLatestProjectRevision();
        $noMarkupElements      = BillElementTable::getMarkupNonEditableElements($bill);

        foreach ( $elements as $key => $element )
        {
            $elements[$key]['markup_rounding_type'] = $markupSettingsInfo['rounding_type'];
            $elements[$key]['markup_disabled']      = in_array($element['id'], $noMarkupElements);

            $originalGrandTotal      = 0;
            $overallTotalAfterMarkup = 0;
            $elementSumTotal         = 0;

            foreach ( $bill->BillColumnSettings as $column )
            {
                $billElementTypeRef = BillElementTypeReferenceTable::getByElementIdAndBillColumnSettingId($element['id'], $column->id);

                $total        = $totalRateByBillColumnSetting[$column->id][$element['id']][0]['total_rate_after_markup'];
                $totalPerUnit = $total / $column->quantity;

                $elements[$key][$column->id . '-total_per_unit'] = $totalPerUnit;
                $elements[$key][$column->id . '-total']          = $total;
                $elements[$key][$column->id . '-total_cost']     = $column->getTotalCostPerFloorArea($totalPerUnit);

                $elements[$key][$column->id . '-estimated_cost']                  = ( $billElementTypeRef ) ? $billElementTypeRef->calculateEstimatedCost() : '';
                $elements[$key][$column->id . '-estimated_cost_per_metre_square'] = ( $billElementTypeRef ) ? $billElementTypeRef->estimated_cost_per_metre_square : '';
                $elements[$key][$column->id . '-element_sum_total']               = $elementSumByBillColumnSetting[$column->id];

                $originalGrandTotal += $totalRateByBillColumnSetting[$column->id][$element['id']][0]['original_total_rate'];
                $overallTotalAfterMarkup += $total;
                $elementSumTotal += $elements[$key][$column->id . '-element_sum_total'];

                unset( $column );
            }

            foreach ( $formulatedColumnConstants as $constant )
            {
                $elements[$key][$constant . '-final_value']        = 0;
                $elements[$key][$constant . '-value']              = '';
                $elements[$key][$constant . '-has_cell_reference'] = false;
                $elements[$key][$constant . '-has_formula']        = false;
            }

            foreach ( $element['FormulatedColumns'] as $formulatedColumn )
            {
                $columnName                                          = $formulatedColumn['column_name'];
                $elements[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                $elements[$key][$columnName . '-value']              = $formulatedColumn['value'];
                $elements[$key][$columnName . '-has_cell_reference'] = false;
                $elements[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            unset( $elements[$key]['FormulatedColumns'] );

            $elements[$key]['is_add_latest_rev'] = 0;
            $elements[$key]['addendum_version']  = null;
            if(array_key_exists($element['id'], $elementsWithAddendums))
            {
                $latestAddendum = end($elementsWithAddendums[$element['id']]);
                $elements[$key]['is_add_latest_rev'] = (int)($latestAddendum['version']==$latestProjectRevision->version);
                $elements[$key]['addendum_version']  = (int)$latestAddendum['version'];
                unset($elementsWithAddendums[$element['id']]);
            }

            $elements[$key]['has_note']                   = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            $elements[$key]['note']                       = (string) $element['note'];
            $elements[$key]['original_grand_total']       = $originalGrandTotal;
            $elements[$key]['grand_total']                = array_key_exists($element['id'], $elementGrandTotals) ? $elementGrandTotals[$element['id']][0]['grand_total_after_markup'] : 0;
            $elements[$key]['overall_total_after_markup'] = $overallTotalAfterMarkup;
            $elements[$key]['element_sum_total']          = $elementSumTotal;
            $elements[$key]['relation_id']                = $bill->id;
            $elements[$key]['_csrf_token']                = $form->getCSRFToken();

            if($bill->getRoot()->MainInformation->status == ProjectMainInformation::STATUS_TENDERING)
            {
                $elements[$key]['editable'] = $element['project_revision_id'] == $currentlyEditingProjectRevision->id;
            }
        }

        $defaultLastRow = [
            'id'                         => Constants::GRID_LAST_ROW,
            'description'                => '',
            'is_add_latest_rev'          => 0,
            'addendum_version'           => null,
            'has_note'                   => false,
            'grand_total'                => 0,
            'original_grand_total'       => 0,
            'overall_total_after_markup' => 0,
            'element_sum_total'          => 0,
            'relation_id'                => $bill->id,
            'markup_rounding_type'       => $billMarkupSetting->rounding_type,
            '_csrf_token'                => $form->getCSRFToken()
        ];

        if($bill->getRoot()->MainInformation->status == ProjectMainInformation::STATUS_TENDERING)
        {
            $defaultLastRow['editable'] = ProjectRevisionTable::getLatestProjectRevisionFromBillId($bill->getRoot()->id)->locked_status == false;
        }

        foreach ( $bill->BillColumnSettings as $column )
        {
            $defaultLastRow[$column->id . '-total_cost']                      = 0;
            $defaultLastRow[$column->id . '-estimated_cost']                  = 0;
            $defaultLastRow[$column->id . '-estimated_cost_per_metre_square'] = 0;
            $defaultLastRow[$column->id . '-total_per_unit']                  = 0;
            $defaultLastRow[$column->id . '-total']                           = 0;
            $defaultLastRow[$column->id . '-element_sum_total']               = 0;
        }

        foreach ( $formulatedColumnConstants as $constant )
        {
            $defaultLastRow[$constant . '-final_value']        = 0;
            $defaultLastRow[$constant . '-value']              = 0;
            $defaultLastRow[$constant . '-has_cell_reference'] = false;
            $defaultLastRow[$constant . '-has_formula']        = false;
        }

        array_push($elements, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeElementUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')));

        $rowData                   = array();
        $affectedNodes             = array();
        $isFormulatedColumn        = false;
        $formulatedColumnTable     = Doctrine_Core::getTable('BillElementFormulatedColumn');
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillElement');

        $con = $element->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $fieldAttr = explode('-', $fieldName);
            $fieldName = count($fieldAttr) > 1 ? $fieldAttr[1] : $fieldAttr[0];

            if ( count($fieldAttr) > 1 )
            {
                $columnId = $fieldAttr[0];

                $billElementTypeRef = BillElementTypeReferenceTable::getByElementIdAndBillColumnSettingId($element->id, $columnId);

                if ( !$billElementTypeRef )
                {
                    $billElementTypeRef                         = new BillElementTypeReference();
                    $billElementTypeRef->bill_element_id        = $element->id;
                    $billElementTypeRef->bill_column_setting_id = $columnId;

                    $billElementTypeRef->save();

                    $billElementTypeRef->refresh();
                }

                $billElementTypeRef->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

                $billElementTypeRef->save($con);

                $con->commit();

                $success = true;

                $errorMsg = null;

            }
            else
            {
                if ( in_array($fieldName, $formulatedColumnConstants) )
                {
                    $formulatedColumn = $formulatedColumnTable->getByRelationIdAndColumnName($element->id, $fieldName);

                    $formulatedColumn->setFormula($fieldValue);

                    $formulatedColumn->save($con);

                    $formulatedColumn->refresh();

                    $isFormulatedColumn = true;

                    //Update Type Total Column
                    if ( $fieldName == BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE || $fieldName == BillElement::FORMULATED_COLUMN_MARKUP_AMOUNT )
                    {
                        $element->updateAllItemTotalAfterMarkup();
                    }
                }
                else
                {
                    $element->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

                    $element->save($con);
                }

                $con->commit();

                $success = true;

                $errorMsg = null;

                $element->refresh();

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
                }
                else
                {
                    $rowData[$fieldName] = $element->{$fieldName};
                }
            }

            $originalGrandTotal      = 0;
            $overallTotalAfterMarkup = 0;
            $elementSumTotal         = 0;
            $totalPerUnit            = 0;
            $bill                    = $element->ProjectStructure;

            $elementSumByBillColumnSetting = array();

            //we get sum of elements total by bill column setting so we won't keep on calling the same query in element list loop
            foreach ( $bill->BillColumnSettings as $column )
            {
                //Get Element Total Rates
                $ElementTotalRates                          = ProjectStructureTable::getTotalItemRateByAndBillColumnSettingIdGroupByElement($bill, $column);
                $elementSumByBillColumnSetting[$column->id] = $ElementTotalRates['grandTotalElement'];
                $totalRateByBillColumnSetting[$column->id]  = $ElementTotalRates['elementToRates'];

                $billElementTypeRef = BillElementTypeReferenceTable::getByElementIdAndBillColumnSettingId($element->id, $column->id);
                $total              = array_key_exists($element->id, $totalRateByBillColumnSetting[$column->id]) ? $totalRateByBillColumnSetting[$column->id][$element->id][0]['total_rate_after_markup'] : 0;

                $rowData[$column->id . '-estimated_cost']                  = $billElementTypeRef ? $billElementTypeRef->calculateEstimatedCost() : 0;
                $rowData[$column->id . '-estimated_cost_per_metre_square'] = $billElementTypeRef ? $billElementTypeRef->estimated_cost_per_metre_square : 0;

                $totalPerUnit = $total / $column->quantity;

                $rowData[$column->id . '-total_per_unit']    = $totalPerUnit;
                $rowData[$column->id . '-total']             = $total;
                $rowData[$column->id . '-element_sum_total'] = $elementSumByBillColumnSetting[$column->id];

                $originalGrandTotal += array_key_exists($element->id, $totalRateByBillColumnSetting[$column->id]) ? $totalRateByBillColumnSetting[$column->id][$element->id][0]['original_total_rate'] : 0;
                $overallTotalAfterMarkup += $total;
                $elementSumTotal += $rowData[$column->id . '-element_sum_total'];

                unset( $column );
            }

            $elementMarkupPercentage = BillElementTable::getFormulatedColumnByRelationIdAndColumnName($element->id, BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE, Doctrine_Core::HYDRATE_ARRAY);

            $markupSettingsInfo = array(
                'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
                'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
                'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
                'element_markup_percentage' => $elementMarkupPercentage ? $elementMarkupPercentage['final_value'] : 0,
                'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
                'rounding_type'             => $bill->BillMarkupSetting->rounding_type
            );

            foreach ( $formulatedColumnConstants as $constant )
            {
                $formulatedColumn                           = $element->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                                 = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $rowData[$constant . '-final_value']        = $finalValue;
                $rowData[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $rowData[$constant . '-has_cell_reference'] = false;
                $rowData[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

                unset( $formulatedColumn );
            }

            /*
             * We need to get other elements(that have items) total because we need to update the %job on other elements
             */
            $elements = DoctrineQuery::create()->select('e.id')
                ->from('BillElement e')->leftJoin('e.Items i')
                ->where('e.project_structure_id = ?', $bill->id)
                ->andWhere('e.id <> ?', $element->id)
                ->andWhere('i.id > 0')
                ->addOrderBy('e.priority ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            $otherElements = array();

            $elementGrandTotals = ProjectStructureTable::getElementGrandTotalByBillIdGroupByElement($bill->id);

            foreach ( $elements as $otherElement )
            {
                $overallTotalAfterMarkupOther = 0;
                $elementSumTotalOther         = 0;
                $elementMarkupPercentage      = BillElementTable::getFormulatedColumnByRelationIdAndColumnName($otherElement['id'], BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE, Doctrine_Core::HYDRATE_ARRAY);

                $markupSettingsInfo['element_markup_percentage'] = $elementMarkupPercentage ? $elementMarkupPercentage['final_value'] : 0;

                foreach ( $bill->BillColumnSettings as $column )
                {
                    $total        = array_key_exists($otherElement['id'], $totalRateByBillColumnSetting[$column->id]) ? $totalRateByBillColumnSetting[$column->id][$otherElement['id']][0]['total_rate_after_markup'] : 0;
                    $totalPerUnit = $totalPerUnit / $column->quantity;

                    $otherData[$column->id . '-total']             = $total;
                    $otherData[$column->id . '-element_sum_total'] = $elementSumByBillColumnSetting[$column->id];

                    $overallTotalAfterMarkupOther += $total;
                    $elementSumTotalOther += $otherData[$column->id . '-element_sum_total'];
                }

                $otherData['id']                         = $otherElement['id'];
                $otherData['overall_total_after_markup'] = $overallTotalAfterMarkupOther;
                $otherData['element_sum_total']          = $elementSumTotalOther;

                array_push($otherElements, $otherData);
                unset( $column );
            }

            $rowData['original_grand_total']       = $originalGrandTotal;
            $rowData['grand_total']                = array_key_exists($element->id, $elementGrandTotals) ? $elementGrandTotals[$element->id][0]['grand_total_after_markup'] : 0;
            $rowData['overall_total_after_markup'] = $overallTotalAfterMarkup;
            $rowData['element_sum_total']          = $elementSumTotal;
            $rowData['affected_nodes']             = $affectedNodes;
            $rowData['other_elements']             = $otherElements;
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeElementAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $items = array();

        $element = new BillElement();

        $con = $element->getTable()->getConnection();

        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillElement');

        $isFormulatedColumn = false;
        $fieldAttr          = array();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $previousElement = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('BillElement')->find($request->getParameter('prev_item_id')) : null;

            $priority = $previousElement ? $previousElement->priority + 1 : 0;
            $billId   = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');

                $fieldAttr = explode('-', $fieldName);
                $fieldName = count($fieldAttr) > 1 ? $fieldAttr[1] : $fieldAttr[0];

                if ( !( count($fieldAttr) > 1 ) )
                {
                    if ( in_array($fieldName, $formulatedColumnConstants) )
                    {
                        $isFormulatedColumn = true;
                    }
                    else
                    {
                        $element->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                    }
                }
            }
        }
        else
        {
            $this->forward404Unless($nextElement = Doctrine_Core::getTable('BillElement')->find($request->getParameter('before_id')));

            $billId   = $nextElement->project_structure_id;
            $priority = $nextElement->priority;
        }

        try
        {
            $con->beginTransaction();

            $bill              = Doctrine_Core::getTable('ProjectStructure')->find($billId);
            $billMarkupSetting = $bill->BillMarkupSetting;

            DoctrineQuery::create()
                ->update('BillElement')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('project_structure_id = ?', $billId)
                ->execute();

            $element->project_structure_id = $billId;
            $element->priority             = $priority;

            // in pre-tender stage, bind it to the first project revision
            // the first project revision is always locked
            if($bill->getRoot()->MainInformation->status == ProjectMainInformation::STATUS_PRETENDER)
            {
                $originalProjectRevision = ProjectRevisionTable::getOriginalProjectRevisionFromBillId($bill->getRoot()->id);
                $element->project_revision_id = $originalProjectRevision->id;
            }
            // in tendering stage, bind it to the latest editing project revision
            if($bill->getRoot()->MainInformation->status == ProjectMainInformation::STATUS_TENDERING)
            {
                $currentlyEditingProjectRevision = ProjectRevisionTable::getCurrentlyEditingProjectRevisionFromBillId($bill->getRoot()->id);

                if($currentlyEditingProjectRevision)
                {
                    $element->project_revision_id = $currentlyEditingProjectRevision->id;
                }
            }

            $element->save($con);

            //if input inserted on column type
            if ( count($fieldAttr) > 1 )
            {
                $columnId           = $fieldAttr[0];
                $billElementTypeRef = BillElementTypeReferenceTable::getByElementIdAndBillColumnSettingId($element->id, $columnId);
                if ( !$billElementTypeRef )
                {
                    $billElementTypeRef                         = new BillElementTypeReference();
                    $billElementTypeRef->bill_element_id        = $element->id;
                    $billElementTypeRef->bill_column_setting_id = $columnId;
                }
                $billElementTypeRef->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                $billElementTypeRef->save();
            }

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new BillElementFormulatedColumn();
                $formulatedColumn->relation_id = $element->id;
                $formulatedColumn->column_name = $fieldName;

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->save();
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $data = array();

            $form = new BaseForm();

            $currentlyEditingProjectRevision = ProjectRevisionTable::getCurrentlyEditingProjectRevisionFromBillId($bill->getRoot()->id);

            $data['id']                         = $element->id;
            $data['description']                = $element->description;
            $data['has_note']                   = ( $element->note != null && $element->note != '' ) ? true : false;
            $data['note']                       = (string) $element->note;
            $data['grand_total']                = 0;
            $data['original_grand_total']       = 0;
            $data['overall_total_after_markup'] = 0;
            $data['element_sum_total']          = 0;
            $data['relation_id']                = $billId;
            $data['markup_rounding_type']       = $billMarkupSetting->rounding_type;
            $data['_csrf_token']                = $form->getCSRFToken();

            if($bill->getRoot()->MainInformation->status == ProjectMainInformation::STATUS_TENDERING)
            {
                $data['editable'] = $element['project_revision_id'] == $currentlyEditingProjectRevision->id;
            }

            foreach ( $bill->BillColumnSettings as $column )
            {
                $billElementTypeRef = BillElementTypeReferenceTable::getByElementIdAndBillColumnSettingId($element->id, $column->id);
                //create new Bill Element Type Reference if not exist
                if ( !$billElementTypeRef )
                {
                    $billElementTypeRef                         = new BillElementTypeReference();
                    $billElementTypeRef->bill_element_id        = $element->id;
                    $billElementTypeRef->bill_column_setting_id = $column->id;
                    $billElementTypeRef->save();
                }

                $data[$column->id . '-total_cost']                      = $billElementTypeRef->total_cost;
                $data[$column->id . '-estimated_cost']                  = $billElementTypeRef->estimated_cost;
                $data[$column->id . '-estimated_cost_per_metre_square'] = $billElementTypeRef->estimated_cost_per_metre_square;
                $data[$column->id . '-total_per_unit']                  = 0;
                $data[$column->id . '-total']                           = 0;
                $data[$column->id . '-element_sum_total']               = 0;
            }

            foreach ( $formulatedColumnConstants as $constant )
            {
                $formulatedColumn                        = $element->getFormulatedColumnByName($constant);
                $finalValue                              = $formulatedColumn ? $formulatedColumn->final_value : 0;
                $data[$constant . '-final_value']        = $finalValue;
                $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn->value : '';
                $data[$constant . '-has_cell_reference'] = $formulatedColumn ? $formulatedColumn->hasCellReference() : false;
                $data[$constant . '-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
            }

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'                         => Constants::GRID_LAST_ROW,
                    'description'                => '',
                    'note'                       => '',
                    'has_note'                   => false,
                    'grand_total'                => 0,
                    'original_grand_total'       => 0,
                    'overall_total_after_markup' => 0,
                    'element_sum_total'          => 0,
                    'relation_id'                => $billId,
                    'markup_rounding_type'       => $billMarkupSetting->rounding_type,
                    '_csrf_token'                => $form->getCSRFToken()
                );

                if($bill->getRoot()->MainInformation->status == ProjectMainInformation::STATUS_TENDERING)
                {
                    $defaultLastRow['editable'] = ProjectRevisionTable::getLatestProjectRevisionFromBillId($bill->getRoot()->id)->locked_status == false;
                }

                foreach ( $bill->BillColumnSettings as $column )
                {
                    $defaultLastRow[$column->id . '-total_cost']                      = 0;
                    $defaultLastRow[$column->id . '-estimated_cost']                  = 0;
                    $defaultLastRow[$column->id . '-estimated_cost_per_metre_square'] = 0;
                    $defaultLastRow[$column->id . '-total_per_unit']                  = 0;
                    $defaultLastRow[$column->id . '-total']                           = 0;
                    $defaultLastRow[$column->id . '-element_sum_total']               = 0;
                }

                foreach ( $formulatedColumnConstants as $constant )
                {
                    $defaultLastRow[$constant . '-final_value']        = 0;
                    $defaultLastRow[$constant . '-value']              = 0;
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

    public function executeElementPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')));

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $currentBQAddendumId = $request->getParameter('currentBQAddendumId');
        $targetElement       = Doctrine_Core::getTable('BillElement')->find(intval($request->getParameter('target_id')));

        if ( !$targetElement )
        {
            $this->forward404Unless($targetElement = Doctrine_Core::getTable('BillElement')->find($request->getParameter('prev_item_id')));
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
                    $newElement = $element->copyTo($targetElement, $lastPosition, $currentBQAddendumId);

                    $form                      = new BaseForm();
                    $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillElement');

                    $bill              = $element->ProjectStructure;
                    $billMarkupSetting = $bill->BillMarkupSetting;

                    $data['markup_rounding_type'] = $billMarkupSetting->rounding_type;

                    foreach ( $bill->BillColumnSettings as $column )
                    {
                        $data[$column->id . '-total_per_unit'] = 0;
                        $data[$column->id . '-total']          = 0;
                        $data[$column->id . '-total_cost']     = 0;

                        $data[$column->id . '-estimated_cost']                  = '';
                        $data[$column->id . '-estimated_cost_per_metre_square'] = '';
                        $data[$column->id . '-element_sum_total']               = 0;

                        unset( $column );
                    }

                    foreach ( $formulatedColumnConstants as $constant )
                    {
                        $data[$constant . '-final_value']        = 0;
                        $data[$constant . '-value']              = '';
                        $data[$constant . '-has_cell_reference'] = false;
                        $data[$constant . '-has_formula']        = false;
                        unset( $formulatedColumn );
                    }

                    $data['id']                         = $newElement->id;
                    $data['description']                = $newElement->description;
                    $data['note']                       = $newElement->note;
                    $data['has_note']                   = ( $newElement->note != null && $newElement->note != '' ) ? true : false;
                    $data['original_grand_total']       = 0;
                    $data['grand_total']                = 0;
                    $data['overall_total_after_markup'] = 0;
                    $data['element_sum_total']          = 0;
                    $data['relation_id']                = $newElement->project_structure_id;
                    $data['_csrf_token']                = $form->getCSRFToken();

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

    public function executeElementDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        try
        {
            $item['id']    = $element->id;
            $affectedNodes = $element->delete();
            $success       = true;
        } catch (Exception $e)
        {
            $errorMsg      = $e->getMessage();
            $item          = array();
            $affectedNodes = array();
            $success       = false;
        }

        // return items need to be in array since grid js expect an array of items for delete operation (delete from store)
        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => array( $item ), 'affected_nodes' => $affectedNodes ));
    }

    public function executeElementNoteUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $billElement = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')));

        $itemNote = $request->getParameter('item_note');

        $item     = null;
        $errorMsg = null;

        try
        {
            $billElement->note = $itemNote;

            $billElement->save();

            $item = array(
                'id'       => $billElement->id,
                'note'     => $billElement->note,
                'has_note' => ( $billElement->note != null && $billElement->note != '' ) ? true : false
            );

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'item'     => $item
        ));
    }
    /*** end bill element actions ***/

    /*** start bill item actions ***/
    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $pdo                                   = $bill->getTable()->getConnection()->getDbh();
        $form                                  = new BaseForm();
        $items                                 = array();
        $elementMarkupPercentage               = 0;
        $pageNoPrefix                          = $bill->BillLayoutSetting->page_no_prefix;
        $formulatedColumnConstants             = Utilities::getAllFormulatedColumnConstants('BillItem');
        $billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

        /*
         * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
         */
        if ( $bill->BillMarkupSetting->element_markup_enabled )
        {
            $stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
                JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
                WHERE e.id = " . $element->id . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
                AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

            $stmt->execute();

            $elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
            $elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;
        }

        $roundingType = $bill->BillMarkupSetting->rounding_type;

        $markupSettingsInfo = array(
            'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
            'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
            'element_markup_percentage' => $elementMarkupPercentage,
            'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
            'rounding_type'             => $roundingType
        );

        list(
            $billItems, $formulatedColumns, $quantityPerUnitByColumns,
            $billItemTypeReferences, $billItemTypeRefFormulatedColumns
            ) = BillItemTable::getDataStructureForBillItemList($element, $bill);

        foreach ( $billItems as $billItem )
        {
            $rate                  = 0;
            $rateAfterMarkup       = 0;
            $itemMarkupPercentage  = 0;
            $grandTotalAfterMarkup = 0;

            $billItem['bill_ref']                    = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
            $billItem['type']                        = (string) $billItem['type'];
            $billItem['uom_id']                      = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
            $billItem['relation_id']                 = $element->id;
            $billItem['linked']                      = false;
            $billItem['markup_rounding_type']        = $roundingType;
            $billItem['_csrf_token']                 = $form->getCSRFToken();
            $billItem['project_revision_deleted_at'] = is_null($billItem['project_revision_deleted_at']) ? false : $billItem['project_revision_deleted_at'];
            $billItem['has_note']                    = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;

            foreach ( $formulatedColumnConstants as $constant )
            {
                $billItem[$constant . '-final_value']        = 0;
                $billItem[$constant . '-value']              = '';
                $billItem[$constant . '-has_cell_reference'] = false;
                $billItem[$constant . '-has_formula']        = false;
                $billItem[$constant . '-linked']             = false;
                $billItem[$constant . '-has_build_up']       = false;
            }

            if ( array_key_exists($billItem['id'], $formulatedColumns) )
            {
                $itemFormulatedColumns = $formulatedColumns[$billItem['id']];

                foreach ( $itemFormulatedColumns as $formulatedColumn )
                {
                    $billItem[$formulatedColumn['column_name'] . '-final_value']  = $formulatedColumn['final_value'];
                    $billItem[$formulatedColumn['column_name'] . '-value']        = $formulatedColumn['value'];
                    $billItem[$formulatedColumn['column_name'] . '-linked']       = $formulatedColumn['linked'];
                    $billItem[$formulatedColumn['column_name'] . '-has_build_up'] = $formulatedColumn['has_build_up'];
                    $billItem[$formulatedColumn['column_name'] . '-has_formula']  = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

                    if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                    {
                        $rate = $formulatedColumn['final_value'];
                    }

                    if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
                    {
                        $itemMarkupPercentage = $formulatedColumn['final_value'];
                    }
                }

                unset( $formulatedColumns[$billItem['id']], $itemFormulatedColumns );

                $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
            }


            $billItem['rate_after_markup'] = $rateAfterMarkup;

            foreach ( $bill->BillColumnSettings as $column )
            {
                $quantityPerUnit = 0;

                foreach ( $billItemTypeFormulatedColumnConstants as $constant )
                {
                    $billItem[$column->id . '-' . $constant . '-final_value']        = 0;
                    $billItem[$column->id . '-' . $constant . '-value']              = '';
                    $billItem[$column->id . '-' . $constant . '-has_cell_reference'] = false;
                    $billItem[$column->id . '-' . $constant . '-has_formula']        = false;
                    $billItem[$column->id . '-' . $constant . '-linked']             = false;
                    $billItem[$column->id . '-' . $constant . '-has_build_up']       = false;
                }

                if ( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($billItem['id'], $quantityPerUnitByColumns[$column->id]) )
                {
                    $quantityPerUnit = $quantityPerUnitByColumns[$column->id][$billItem['id']][0];
                    unset( $quantityPerUnitByColumns[$column->id][$billItem['id']] );
                }

                if ( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$column->id]) )
                {
                    $billItemTypeRef     = $billItemTypeReferences[$column->id][$billItem['id']];
                    $include             = $billItemTypeRef['include'] ? 'true' : 'false';
                    $totalQuantity       = $billItemTypeRef['total_quantity'];
                    $quantityPerUnitDiff = $billItemTypeRef['quantity_per_unit_difference'];
                    $totalPerUnit        = number_format($rateAfterMarkup * $quantityPerUnit, 2, '.', '');
                    $total               = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                    unset( $billItemTypeReferences[$column->id][$billItem['id']] );

                    if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
                    {
                        foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
                        {
                            $billItem[$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-final_value']  = number_format($billItemTypeRefFormulatedColumn['final_value'], 2, '.', '');
                            $billItem[$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-value']        = BillItemTypeReferenceFormulatedColumnTable::getConvertedValue($billItemTypeRefFormulatedColumn['value']);
                            $billItem[$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-linked']       = $billItemTypeRefFormulatedColumn['linked'];
                            $billItem[$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];
                            $billItem[$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-has_formula']  = $billItemTypeRefFormulatedColumn && $billItemTypeRefFormulatedColumn['value'] != $billItemTypeRefFormulatedColumn['final_value'] ? true : false;

                            unset( $billItemTypeRefFormulatedColumn );
                        }
                    }
                }
                else
                {
                    $include             = 'true';//default value is true
                    $totalQuantity       = 0;
                    $quantityPerUnitDiff = 0;
                    $totalPerUnit        = 0;
                    $total               = 0;
                }

                $grandTotalAfterMarkup += $total;

                $billItem[$column->id . '-include']                      = $include;
                $billItem[$column->id . '-quantity_per_unit_difference'] = $quantityPerUnitDiff;
                $billItem[$column->id . '-total_quantity']               = $totalQuantity;
                $billItem[$column->id . '-total_per_unit']               = $totalPerUnit;
                $billItem[$column->id . '-total']                        = $total;
            }

            $billItem['grand_total_after_markup'] = $grandTotalAfterMarkup;

            array_push($items, $billItem);
            unset( $billItem );
        }

        unset( $billItems );

        $defaultLastRow = array(
            'id'                       => Constants::GRID_LAST_ROW,
            'bill_ref'                 => '',
            'description'              => '',
            'note'                     => '',
            'has_note'                 => false,
            'type'                     => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                   => '-1',
            'uom_symbol'               => '',
            'relation_id'              => $element->id,
            'level'                    => 0,
            'grand_total_quantity'     => '',
            'grand_total'              => '',
            'linked'                   => false,
            'rate_after_markup'        => 0,
            'version'                  => - 1,
            'grand_total_after_markup' => 0,
            'markup_rounding_type'     => $roundingType,
            '_csrf_token'              => $form->getCSRFToken()
        );

        foreach ( $formulatedColumnConstants as $constant )
        {
            $defaultLastRow[$constant . '-final_value']        = 0;
            $defaultLastRow[$constant . '-value']              = 0;
            $defaultLastRow[$constant . '-linked']             = false;
            $defaultLastRow[$constant . '-has_build_up']       = false;
            $defaultLastRow[$constant . '-has_cell_reference'] = false;
            $defaultLastRow[$constant . '-has_formula']        = false;
        }

        foreach ( $bill->BillColumnSettings as $column )
        {
            $defaultLastRow[$column->id . '-include']                      = 'true';
            $defaultLastRow[$column->id . '-quantity_per_unit_difference'] = 0;
            $defaultLastRow[$column->id . '-total_quantity']               = 0;
            $defaultLastRow[$column->id . '-total_per_unit']               = 0;
            $defaultLastRow[$column->id . '-total']                        = 0;

            foreach ( $billItemTypeFormulatedColumnConstants as $constant )
            {
                $defaultLastRow[$column->id . '-' . $constant . '-final_value']        = 0;
                $defaultLastRow[$column->id . '-' . $constant . '-value']              = 0;
                $defaultLastRow[$column->id . '-' . $constant . '-has_cell_reference'] = false;
                $defaultLastRow[$column->id . '-' . $constant . '-has_formula']        = false;
                $defaultLastRow[$column->id . '-' . $constant . '-linked']             = false;
                $defaultLastRow[$column->id . '-' . $constant . '-has_build_up']       = false;
            }
        }

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

        $rowData                               = array();
        $affectedNodes                         = array();
        $isFormulatedColumn                    = false;
        $isBillItemTypeFormulatedColumn        = false;
        $formulatedColumnConstants             = Utilities::getAllFormulatedColumnConstants('BillItem');
        $billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldAttr  = explode('-', $request->getParameter('attr_name'));
            $fieldName  = count($fieldAttr) > 1 ? $fieldAttr[1] : $fieldAttr[0];
            $fieldValue = trim($request->getParameter('val'));
            $fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;

            if ( count($fieldAttr) > 1 )
            {
                $columnId = $fieldAttr[0];

                $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($item->id, $columnId);

                if ( !$billItemTypeRef )
                {
                    $billItemTypeRef                         = new BillItemTypeReference();
                    $billItemTypeRef->bill_item_id           = $item->id;
                    $billItemTypeRef->bill_column_setting_id = $columnId;
                    $billItemTypeRef->save();

                    $billItemTypeRef->refresh();
                }

                if ( in_array($fieldName, $billItemTypeFormulatedColumnConstants) )
                {
                    $item->setTypeFormulatedColumn($billItemTypeRef, $fieldName, $fieldValue, $con);

                    $isBillItemTypeFormulatedColumn = true;
                }
                else
                {
                    if ( $fieldName == 'include' )
                    {
                        $fieldValue = $fieldValue == 'true' ? true : false;
                    }
                    $billItemTypeRef->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                    $affectedItems = $billItemTypeRef->save($con);

                    if ( is_array($affectedItems) and array_key_exists('affected_bill_item_type_references', $affectedItems) and count($affectedItems['affected_bill_item_type_references']) > 0 )
                    {
                        $affectedNodes['affected_bill_item_type_references'] = $affectedItems['affected_bill_item_type_references'];
                    }
                }
            }
            else
            {
                if ( in_array($fieldName, $formulatedColumnConstants) )
                {
                    $formulatedColumn = BillItemFormulatedColumnTable::getInstance()->getByRelationIdAndColumnName($item->id, $fieldName);

                    $formulatedColumn->setFormula($fieldValue);

                    $formulatedColumn->linked = false;

                    $formulatedColumn->has_build_up = false;

                    $formulatedColumn->save($con);

                    $formulatedColumn->refresh();

                    $isFormulatedColumn = true;

                    //Update Type Total Column
                    if ( $fieldName == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE || $fieldName == BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT )
                    {
                        $item->updateTypeTotalAmount();
                    }
                }
                else
                {
                    if ( $fieldName == 'type' )
                    {
                        /*
                         * By changing type, some of the values probably need to be zero out thus not only it will affects item's value (rate, quantity, etc)
                         * it probably will affects other items that are linked to it (if any). That's why we need to get the affected items
                         * from this operation and returns it so our frontend javascript can updates it.
                         */
                        $affectedItems = $item->{'update' . sfInflector::camelize($fieldName)}($fieldValue);

                        if ( array_key_exists('affected_bill_items', $affectedItems) and count($affectedItems['affected_bill_items']) > 0 )
                        {
                            $affectedNodes['affected_bill_items'] = $affectedItems['affected_bill_items'];
                        }

                        if ( array_key_exists('affected_bill_item_type_references', $affectedItems) and count($affectedItems['affected_bill_item_type_references']) > 0 )
                        {
                            $affectedNodes['affected_bill_item_type_references'] = $affectedItems['affected_bill_item_type_references'];
                        }

                        // insert default empty row for item-type LS%
                        if ( $fieldValue == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT )
                        {
                            BillItemLumpSumPercentageTable::insertDefaultEmptyRow($item);
                        }
                    }
                    elseif ( $fieldName == 'uom_id' )
                    {
                        $item->updateUnitOfMeasurement($fieldValue);
                    }
                    else
                    {
                        $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                    }

                    $item->save($con);

                    $rowData[$fieldName] = $item->{$fieldName};
                }
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item->updateBillItemTotalColumns();

            /*
            * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
            */
            $elementMarkupResult = DoctrineQuery::create()->select('c.final_value')
                ->from('BillElementFormulatedColumn c')->leftJoin('c.BillElement e')->leftJoin('e.ProjectStructure p')->leftJoin('p.BillMarkupSetting s')
                ->where('c.relation_id = ?', $item->element_id)
                ->andWhere('c.column_name = ?', BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE)
                ->andWhere('s.element_markup_enabled IS TRUE')
                ->limit(1)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();

            $elementMarkupPercentage = $elementMarkupResult ? $elementMarkupResult['final_value'] : 0;

            $markupSettingsInfo = array(
                'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
                'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
                'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
                'element_markup_percentage' => $elementMarkupPercentage,
                'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
                'rounding_type'             => $bill->BillMarkupSetting->rounding_type
            );

            if ( $isFormulatedColumn )
            {
                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                foreach ( $referencedNodes as $referencedNode )
                {
                    $node = BillItemFormulatedColumnTable::getInstance()->find($referencedNode['node_from']);

                    if ( $node )
                    {
                        $affectedBillItem = $node->BillItem;
                        $affectedBillItem->updateBillItemTotalColumns();
                        $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($affectedBillItem->id, $markupSettingsInfo);
                        $markupAmountColumn = $affectedBillItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                        $affectedNode = array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value,
                            'grand_total_quantity'      => $affectedBillItem->grand_total_quantity,
                            'rate_after_markup'         => $rateAfterMarkup,
                            'markup_amount-value'       => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                            'markup_amount-final_value' => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                            'grand_total'               => $affectedBillItem->grand_total
                        );

                        $grandTotalAfterMarkup = 0;
                        foreach ( $bill->BillColumnSettings as $column )
                        {
                            $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($affectedBillItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                            if ( $billItemTypeRef )
                            {
                                $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                                $quantity = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);

                                $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                                $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                                $columnId                                    = $billItemTypeRef['bill_column_setting_id'];
                                $affectedNode[$columnId . '-total_per_unit'] = $totalPerUnit;
                                $affectedNode[$columnId . '-total']          = $total;

                                $grandTotalAfterMarkup += $total;
                            }
                        }
                        $affectedNode['grand_total_after_markup'] = $grandTotalAfterMarkup;
                        array_push($affectedNodes, $affectedNode);
                    }
                    unset( $affectedBillItem );
                }
            }
            elseif ( $isBillItemTypeFormulatedColumn )//to get affected nodes when updating the formulated column
            {
                $formulatedColumn = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef->id, $fieldName);

                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                $column = $billItemTypeRef->BillColumnSetting;

                foreach ( $referencedNodes as $referencedNode )
                {
                    $node = Doctrine_Core::getTable('BillItemTypeReferenceFormulatedColumn')->find($referencedNode['node_from']);

                    if ( $node )
                    {
                        $billItemTypeRefNode = $node->BillItemTypeReference;
                        $affectedBillItem    = $billItemTypeRefNode->BillItem;
                        $affectedBillItem->updateBillItemTotalColumns();

                        $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($affectedBillItem->id, $markupSettingsInfo);
                        $markupAmountColumn = $affectedBillItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                        $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                        $quantity = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRefNode->id, $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);

                        $totalPerUnit = $quantity ? number_format($rateAfterMarkup * $quantity['final_value'], 2, '.', '') : 0;
                        $total        = $totalPerUnit * $column->quantity;

                        $affectedNode = array(
                            'id'                                            => $billItemTypeRefNode->bill_item_id,
                            $column->id . '-' . $fieldName . '-final_value' => number_format($node->final_value, 2, '.', ''),
                            $column->id . '-quantity_per_unit_difference'   => $billItemTypeRefNode->quantity_per_unit_difference,
                            $column->id . '-total_quantity'                 => $billItemTypeRefNode->total_quantity,
                            $column->id . '-total_per_unit'                 => $totalPerUnit,
                            $column->id . '-total'                          => $total,
                            'grand_total_quantity'                          => $affectedBillItem->grand_total_quantity,
                            'rate_after_markup'                             => $rateAfterMarkup,
                            'grand_total'                                   => $affectedBillItem->grand_total,
                            'markup_amount-value'                           => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                            'markup_amount-final_value'                     => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                            'grand_total_after_markup'                      => $affectedBillItem->getGrandTotalAfterMarkup()
                        );
                        array_push($affectedNodes, $affectedNode);
                    }

                    unset( $billItemTypeRefNode, $affectedBillItem );
                }
            }

            $rate                 = 0;
            $itemMarkupPercentage = 0;
            foreach ( $formulatedColumnConstants as $constant )
            {
                $formulatedColumn                           = $item->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                                 = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $rowData[$constant . '-final_value']        = $finalValue;
                $rowData[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $rowData[$constant . '-has_cell_reference'] = false;
                $rowData[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                $rowData[$constant . '-linked']             = $formulatedColumn ? $formulatedColumn['linked'] : false;
                $rowData[$constant . '-has_build_up']       = $formulatedColumn ? $formulatedColumn['has_build_up'] : false;

                if ( $formulatedColumn && $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                {
                    $rate = $formulatedColumn['final_value'];
                }

                if ( $formulatedColumn && $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
                {
                    $itemMarkupPercentage = $formulatedColumn['final_value'];
                }
            }

            $rateAfterMarkup       = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
            $grandTotalAfterMarkup = 0;

            foreach ( $bill->BillColumnSettings as $column )
            {
                $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($item->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                if ( $billItemTypeRef )
                {
                    $include             = $billItemTypeRef['include'] ? 'true' : 'false';
                    $quantity            = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                    $totalQuantity       = $billItemTypeRef['total_quantity'];
                    $quantityPerUnitDiff = $billItemTypeRef['quantity_per_unit_difference'];
                    $totalPerUnit        = $quantity ? number_format($rateAfterMarkup * $quantity['final_value'], 2, '.', '') : 0;
                    $total               = $totalPerUnit * $column->quantity;
                }
                else
                {
                    $include             = 'true';//default value is true
                    $totalQuantity       = 0;
                    $quantityPerUnitDiff = 0;
                    $totalPerUnit        = 0;
                    $total               = 0;
                }

                $grandTotalAfterMarkup += $total;

                $rowData[$column->id . '-include']                      = $include;
                $rowData[$column->id . '-quantity_per_unit_difference'] = $quantityPerUnitDiff;
                $rowData[$column->id . '-total_quantity']               = $totalQuantity;
                $rowData[$column->id . '-total_per_unit']               = $totalPerUnit;
                $rowData[$column->id . '-total']                        = $total;

                foreach ( $billItemTypeFormulatedColumnConstants as $constant )
                {
                    $formulatedColumn                                               = $billItemTypeRef ? BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $constant) : false;
                    $finalValue                                                     = $formulatedColumn ? $formulatedColumn->final_value : 0;
                    $rowData[$column->id . '-' . $constant . '-final_value']        = number_format($finalValue, 2, '.', '');
                    $rowData[$column->id . '-' . $constant . '-value']              = $formulatedColumn ? $formulatedColumn->getConvertedValue() : '';
                    $rowData[$column->id . '-' . $constant . '-has_cell_reference'] = $formulatedColumn ? $formulatedColumn->hasCellReference() : false;
                    $rowData[$column->id . '-' . $constant . '-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
                    $rowData[$column->id . '-' . $constant . '-linked']             = $formulatedColumn ? $formulatedColumn->linked : false;
                    $rowData[$column->id . '-' . $constant . '-has_build_up']       = $formulatedColumn ? $formulatedColumn->has_build_up : false;
                }
            }

            $rowData['affected_nodes']           = $affectedNodes;
            $rowData['grand_total_quantity']     = $item->grand_total_quantity;
            $rowData['grand_total']              = $item->grand_total;
            $rowData['linked']                   = false;
            $rowData['rate_after_markup']        = $rateAfterMarkup;
            $rowData['grand_total_after_markup'] = $grandTotalAfterMarkup;
            $rowData['type']                     = (string) $item->type;
            $rowData['uom_id']                   = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol']               = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            $rowData['version']                  = $item->ProjectRevision->version;

            /* setting up prime cost rate value */
            $rowData['pc_supply_rate']             = number_format($item->PrimeCostRate->supply_rate, 2, '.', '');
            $rowData['pc_wastage_percentage']      = number_format($item->PrimeCostRate->wastage_percentage, 2, '.', '');
            $rowData['pc_wastage_amount']          = number_format($item->PrimeCostRate->wastage_amount, 2, '.', '');
            $rowData['pc_labour_for_installation'] = number_format($item->PrimeCostRate->labour_for_installation, 2, '.', '');
            $rowData['pc_other_cost']              = number_format($item->PrimeCostRate->other_cost, 2, '.', '');
            $rowData['pc_profit_percentage']       = number_format($item->PrimeCostRate->profit_percentage, 2, '.', '');
            $rowData['pc_profit_amount']           = number_format($item->PrimeCostRate->profit_amount, 2, '.', '');
            $rowData['pc_total']                   = number_format($item->PrimeCostRate->total, 2, '.', '');

            $item->Element->updateAllItemTotalAfterMarkup();
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

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')));

        $items                                 = array();
        $nextItem                              = null;
        $fieldName                             = null;
        $fieldValue                            = null;
        $formulatedColumnConstants             = Utilities::getAllFormulatedColumnConstants('BillItem');
        $billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');
        $isFormulatedColumn                    = false;
        $roundingType                          = $bill->BillMarkupSetting->rounding_type;
        $currentBQAddendumId                   = $request->getParameter('currentBQAddendumId');

        $con = Doctrine_Core::getTable('BillItem')->getConnection();

        try
        {
            $con->beginTransaction();

            $projectRevision = Doctrine_Core::getTable('ProjectRevision')->find($currentBQAddendumId);
            $previousItem    = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('BillItem')->find($request->getParameter('prev_item_id')) : null;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $elementId = $request->getParameter('relation_id');

                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                $fieldAttr = explode('-', $fieldName);
                $fieldName = count($fieldAttr) > 1 ? $fieldAttr[1] : $fieldAttr[0];

                $item = BillItemTable::createItemFromLastRow($previousItem, $elementId, $fieldName, $fieldValue, $currentBQAddendumId);

                if ( count($fieldAttr) > 1 )
                {
                    $columnId = $fieldAttr[0];

                    $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($item->id, $columnId);

                    if ( !$billItemTypeRef )
                    {
                        $billItemTypeRef                         = new BillItemTypeReference();
                        $billItemTypeRef->bill_item_id           = $item->id;
                        $billItemTypeRef->bill_column_setting_id = $columnId;
                        $billItemTypeRef->save();

                        $billItemTypeRef->refresh();
                    }

                    if ( in_array($fieldName, $billItemTypeFormulatedColumnConstants) )
                    {
                        $item->setTypeFormulatedColumn($billItemTypeRef, $fieldName, $fieldValue, $con);
                    }
                    else
                    {
                        if ( $fieldName == 'include' )
                        {
                            $fieldValue = $fieldValue == 'true' ? true : false;
                        }
                        $billItemTypeRef->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                        $billItemTypeRef->save($con);
                    }
                }
                elseif ( in_array($fieldName, $formulatedColumnConstants) )
                {
                    $isFormulatedColumn = true;
                }
            }
            else
            {
                // if current project revision is currently in addendum mode, then proceed with checking original's bill printout
                // item row status
                $this->forward404Unless($nextItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('before_id')));

                $elementId = $nextItem->element_id;
                $item      = BillItemTable::createItem($nextItem, $currentBQAddendumId);
            }

            if ( $isFormulatedColumn && $fieldName )
            {
                $formulatedColumn              = new BillItemFormulatedColumn();
                $formulatedColumn->relation_id = $item->id;
                $formulatedColumn->column_name = $fieldName;

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->save();
            }

            /**
             * related codes to generate bill pages is located in ProjectRevision.class.php save() function
             * class sfBillAddendumReferenceGenerator.class.php
             */
            if ( $projectRevision->version > 0 )
            {
                $affectedItemId = 0;

                // allow newly created item at last row to be following the previous item if available
                if ( $previousItem instanceof BillItem )
                {
                    $affectedItemId = $previousItem->id;
                }

                if ( $nextItem instanceof BillItem and $nextItem->type != BillItem::TYPE_HEADER && $nextItem->type != BillItem::TYPE_HEADER_N )
                {
                    $affectedItemId = $nextItem->id;
                }

                // check for bill page record
                // only update affected page item if bill page record is found
                // this is a temporary measure
                $affectedPage = Doctrine_Query::create()
                    ->select('i.bill_page_id')
                    ->from('BillPageItem i')
                    ->where('i.bill_item_id = ?', $affectedItemId)
                    ->orderBy('i.bill_page_id DESC')
                    ->limit(1)
                    ->fetchOne();

                if($affectedPage)
                {
                    BillPageItemTable::updateAffectedPageItem($affectedItemId, $item, $currentBQAddendumId);
                }
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $data = array();

            $form = new BaseForm();

            $item->refresh();

            $data['id']                          = $item->id;
            $data['bill_ref']                    = '';
            $data['description']                 = $item->description;
            $data['note']                        = $item->note;
            $data['has_note']                    = ( $item->note != null && $item->note != '' ) ? true : false;
            $data['type']                        = (string) $item->type;
            $data['version']                     = $item->ProjectRevision->version;
            $data['uom_id']                      = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $data['uom_symbol']                  = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            $data['relation_id']                 = $elementId;
            $data['updated_at']                  = date('d/m/Y H:i', strtotime($item->updated_at));
            $data['linked']                      = false;
            $data['rate_after_markup']           = 0;
            $data['grand_total_after_markup']    = 0;
            $data['markup_rounding_type']        = $roundingType;
            $data['grand_total_quantity']        = $item->grand_total_quantity;
            $data['grand_total']                 = $item->grand_total;
            $data['level']                       = $item->level;
            $data['_csrf_token']                 = $form->getCSRFToken();
            $data['project_revision_deleted_at'] = false;

            /* setting up prime cost rate value */
            $data['pc_supply_rate']             = number_format($item->PrimeCostRate->supply_rate, 2, '.', '');
            $data['pc_wastage_percentage']      = number_format($item->PrimeCostRate->wastage_percentage, 2, '.', '');
            $data['pc_wastage_amount']          = number_format($item->PrimeCostRate->wastage_amount, 2, '.', '');
            $data['pc_labour_for_installation'] = number_format($item->PrimeCostRate->labour_for_installation, 2, '.', '');
            $data['pc_other_cost']              = number_format($item->PrimeCostRate->other_cost, 2, '.', '');
            $data['pc_profit_percentage']       = number_format($item->PrimeCostRate->profit_percentage, 2, '.', '');
            $data['pc_profit_amount']           = number_format($item->PrimeCostRate->profit_amount, 2, '.', '');
            $data['pc_total']                   = number_format($item->PrimeCostRate->total, 2, '.', '');

            foreach ( $formulatedColumnConstants as $constant )
            {
                $formulatedColumn                        = $item->getFormulatedColumnByName($constant);
                $finalValue                              = $formulatedColumn ? $formulatedColumn->final_value : 0;
                $data[$constant . '-final_value']        = $finalValue;
                $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn->value : '';
                $data[$constant . '-has_cell_reference'] = false;
                $data[$constant . '-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
                $data[$constant . '-linked']             = $formulatedColumn ? $formulatedColumn->linked : false;
                $data[$constant . '-has_build_up']       = $formulatedColumn ? $formulatedColumn->has_build_up : false;
            }

            foreach ( $bill->BillColumnSettings as $column )
            {
                $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($item->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                if ( $billItemTypeRef )
                {
                    $include = $billItemTypeRef['include'] ? 'true' : 'false';
                }
                else
                {
                    $include = 'true';//default value is true
                }

                $data[$column->id . '-include']                      = $include;
                $data[$column->id . '-quantity_per_unit_difference'] = $billItemTypeRef ? $billItemTypeRef['quantity_per_unit_difference'] : '';
                $data[$column->id . '-total_quantity']               = $billItemTypeRef ? $billItemTypeRef['total_quantity'] : '';
                $data[$column->id . '-total_per_unit']               = 0;
                $data[$column->id . '-total']                        = 0;

                foreach ( $billItemTypeFormulatedColumnConstants as $constant )
                {
                    $formulatedColumn                                            = $billItemTypeRef ? BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $constant) : false;
                    $finalValue                                                  = $formulatedColumn ? $formulatedColumn->final_value : 0;
                    $data[$column->id . '-' . $constant . '-final_value']        = $finalValue;
                    $data[$column->id . '-' . $constant . '-value']              = $formulatedColumn ? $formulatedColumn->value : '';
                    $data[$column->id . '-' . $constant . '-has_cell_reference'] = $formulatedColumn ? $formulatedColumn->hasCellReference() : false;
                    $data[$column->id . '-' . $constant . '-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
                    $data[$column->id . '-' . $constant . '-linked']             = $formulatedColumn ? $formulatedColumn->linked : false;
                    $data[$column->id . '-' . $constant . '-has_build_up']       = $formulatedColumn ? $formulatedColumn->has_build_up : false;
                }
            }

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'                          => Constants::GRID_LAST_ROW,
                    'bill_ref'                    => '',
                    'description'                 => '',
                    'note'                        => '',
                    'has_note'                    => false,
                    'type'                        => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
                    'uom_id'                      => '-1',
                    'uom_symbol'                  => '',
                    'relation_id'                 => $elementId,
                    'updated_at'                  => '-',
                    'level'                       => 0,
                    'grand_total_quantity'        => 0,
                    'grand_total'                 => 0,
                    'linked'                      => false,
                    'rate_after_markup'           => 0,
                    'grand_total_after_markup'    => 0,
                    'version'                     => - 1,
                    'markup_rounding_type'        => $roundingType,
                    '_csrf_token'                 => $form->getCSRFToken(),
                    'project_revision_deleted_at' => false,
                );

                foreach ( $formulatedColumnConstants as $constant )
                {
                    $defaultLastRow[$constant . '-final_value']        = 0;
                    $defaultLastRow[$constant . '-value']              = 0;
                    $defaultLastRow[$constant . '-linked']             = false;
                    $defaultLastRow[$constant . '-has_build_up']       = false;
                    $defaultLastRow[$constant . '-has_cell_reference'] = false;
                    $defaultLastRow[$constant . '-has_formula']        = false;
                }

                foreach ( $bill->BillColumnSettings as $column )
                {
                    $defaultLastRow[$column->id . '-include']                      = 'true';
                    $defaultLastRow[$column->id . '-quantity_per_unit_difference'] = 0;
                    $defaultLastRow[$column->id . '-total_quantity']               = 0;
                    $defaultLastRow[$column->id . '-total_per_unit']               = 0;
                    $defaultLastRow[$column->id . '-total']                        = 0;

                    foreach ( $billItemTypeFormulatedColumnConstants as $constant )
                    {
                        $defaultLastRow[$column->id . '-' . $constant . '-final_value']        = 0;
                        $defaultLastRow[$column->id . '-' . $constant . '-value']              = 0;
                        $defaultLastRow[$column->id . '-' . $constant . '-has_cell_reference'] = false;
                        $defaultLastRow[$column->id . '-' . $constant . '-has_formula']        = false;
                        $defaultLastRow[$column->id . '-' . $constant . '-linked']             = false;
                        $defaultLastRow[$column->id . '-' . $constant . '-has_build_up']       = false;
                    }
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

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $currentBQAddendumId = $request->getParameter('currentBQAddendumId');
        $targetItem          = Doctrine_Core::getTable('BillItem')->find(intval($request->getParameter('target_id')));

        if ( !$targetItem )
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('prev_item_id')));
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
                        ->from('BillItem i')
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
                    $newItem                               = $item->copyTo($targetItem, $lastPosition, $currentBQAddendumId);
                    $bill                                  = $item->Element->ProjectStructure;
                    $latestProjectRevision                 = ProjectRevisionTable::getLatestProjectRevisionFromBillId($bill->root_id, Doctrine_Core::HYDRATE_ARRAY);
                    $roundingType                          = $bill->BillMarkupSetting->rounding_type;
                    $formulatedColumnConstants             = Utilities::getAllFormulatedColumnConstants('BillItem');
                    $billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');
                    $form                                  = new BaseForm();
                    $billItemTypeReferences                = array();
                    $billItemTypeRefFormulatedColumns      = array();
                    $quantityPerUnitByColumns              = array();

                    /*
                    * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
                    */
                    $elementMarkupResult = DoctrineQuery::create()->select('COALESCE(c.final_value, 0)')
                        ->from('BillElementFormulatedColumn c')->leftJoin('c.BillElement e')->leftJoin('e.ProjectStructure p')->leftJoin('p.BillMarkupSetting s')
                        ->where('c.relation_id = ?', $item->element_id)
                        ->andWhere('c.column_name = ?', BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE)
                        ->andWhere('s.element_markup_enabled IS TRUE')
                        ->limit(1)
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->fetchOne();

                    $elementMarkupPercentage = $elementMarkupResult ? $elementMarkupResult['final_value'] : 0;

                    $markupSettingsInfo = array(
                        'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
                        'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
                        'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
                        'element_markup_percentage' => $elementMarkupPercentage,
                        'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
                        'rounding_type'             => $roundingType
                    );

                    $children = DoctrineQuery::create()->select('i.id, i.description, i.note, i.type, i.uom_id, uom.symbol, i.element_id, i.grand_total_quantity, i.grand_total, i.level, pc.supply_rate, pc.wastage_percentage, pc.wastage_amount, pc.labour_for_installation, pc.other_cost, pc.profit_percentage, pc.profit_amount, pc.total')
                        ->from('BillItem i')
                        ->leftJoin('i.PrimeCostRate pc')
                        ->leftJoin('i.UnitOfMeasurement uom')
                        ->where('i.root_id = ?', $newItem->root_id)
                        ->andWhere('i.lft > ? AND i.rgt < ?', array( $newItem->lft, $newItem->rgt ))
                        ->addOrderBy('i.lft')
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

                    $billItemIds = ( $children and count($children) > 0 ) ? Utilities::arrayValueRecursive('id', $children) : array();

                    array_push($billItemIds, $newItem->id);

                    if ( is_array($billItemIds) and count($billItemIds) > 0 )
                    {
                        $pdo = $newItem->getTable()->getConnection()->getDbh();

                        $implodedItemIds = implode(',', $billItemIds);

                        foreach ( $bill->BillColumnSettings as $column )
                        {
                            $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                            $stmt = $pdo->prepare("SELECT r.bill_item_id, COALESCE(fc.final_value, 0) AS value FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
                            JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
                            WHERE r.bill_item_id IN (" . $implodedItemIds . ") AND r.bill_column_setting_id = " . $column->id . "
                            AND r.include IS TRUE AND fc.column_name = '" . $quantityFieldName . "' AND fc.final_value <> 0
                            AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                            $stmt->execute();

                            $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

                            $quantityPerUnitByColumns[$column->id] = $quantities;

                            $stmt = $pdo->prepare("SELECT r.id, r.bill_item_id, r.include, r.total_quantity, r.quantity_per_unit_difference
                            FROM " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r
                            WHERE r.bill_item_id IN (" . $implodedItemIds . ") AND r.bill_column_setting_id = " . $column->id . "
                            AND r.deleted_at IS NULL");

                            $stmt->execute();

                            $billItemTypeRefs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            $billItemTypeRefIds = count($billItemTypeRefs) > 0 ? new SplFixedArray(count($billItemTypeRefs)) : null;

                            foreach ( $billItemTypeRefs as $idx => $billItemTypeReference )
                            {
                                if ( !array_key_exists($column->id, $billItemTypeReferences) )
                                {
                                    $billItemTypeReferences[$column->id] = array();
                                }

                                $billItemTypeReferences[$column->id][$billItemTypeReference['bill_item_id']] = $billItemTypeReference;

                                $billItemTypeRefIds[$idx] = $billItemTypeReference['id'];
                            }

                            if ( $billItemTypeRefIds instanceof SplFixedArray )
                            {
                                $stmt = $pdo->prepare("SELECT fc.relation_id, fc.value, fc.final_value, fc.column_name, fc.linked, fc.has_build_up
                                FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
                                WHERE fc.relation_id IN (" . implode(',', $billItemTypeRefIds->toArray()) . ") AND fc.deleted_at IS NULL");

                                $stmt->execute();

                                $billItemTypeRefFormulatedColumnFetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ( $billItemTypeRefFormulatedColumnFetch as $fc )
                                {
                                    if ( !array_key_exists($fc['relation_id'], $billItemTypeRefFormulatedColumns) )
                                    {
                                        $billItemTypeRefFormulatedColumns[$fc['relation_id']] = array();
                                    }

                                    array_push($billItemTypeRefFormulatedColumns[$fc['relation_id']], $fc);

                                    unset( $fc );
                                }

                                unset( $billItemTypeRefFormulatedColumnFetch );
                            }

                            unset( $billItemTypeRefs );
                        }
                    }

                    foreach ( $children as $key => $child )
                    {
                        $children[$key]['type']                        = (string) $child['type'];
                        $children[$key]['uom_id']                      = $child['uom_id'] > 0 ? (string) $child['uom_id'] : '-1';
                        $children[$key]['uom_symbol']                  = $child['uom_id'] > 0 ? $child['UnitOfMeasurement']['symbol'] : '';
                        $children[$key]['relation_id']                 = $child['element_id'];
                        $children[$key]['linked']                      = false;
                        $children[$key]['bill_ref']                    = '';
                        $children[$key]['markup_rounding_type']        = $roundingType;
                        $children[$key]['_csrf_token']                 = $form->getCSRFToken();
                        $children[$key]['version']                     = $latestProjectRevision['version'];
                        $children[$key]['project_revision_deleted_at'] = null;

                        /* setting up prime cost rate value */
                        $children[$key]['pc_supply_rate']             = $child['PrimeCostRate'] ? $child['PrimeCostRate']['supply_rate'] : 0;
                        $children[$key]['pc_wastage_percentage']      = $child['PrimeCostRate'] ? $child['PrimeCostRate']['wastage_percentage'] : 0;
                        $children[$key]['pc_wastage_amount']          = $child['PrimeCostRate'] ? $child['PrimeCostRate']['wastage_amount'] : 0;
                        $children[$key]['pc_labour_for_installation'] = $child['PrimeCostRate'] ? $child['PrimeCostRate']['labour_for_installation'] : 0;
                        $children[$key]['pc_other_cost']              = $child['PrimeCostRate'] ? $child['PrimeCostRate']['other_cost'] : 0;
                        $children[$key]['pc_profit_percentage']       = $child['PrimeCostRate'] ? $child['PrimeCostRate']['profit_percentage'] : 0;
                        $children[$key]['pc_profit_amount']           = $child['PrimeCostRate'] ? $child['PrimeCostRate']['profit_amount'] : 0;
                        $children[$key]['pc_total']                   = $child['PrimeCostRate'] ? $child['PrimeCostRate']['total'] : 0;

                        unset( $children[$key]['PrimeCostRate'], $children[$key]['UnitOfMeasurement'] );

                        $rate                 = 0;
                        $itemMarkupPercentage = 0;
                        foreach ( $formulatedColumnConstants as $constant )
                        {
                            $formulatedColumn                                  = BillItemTable::getFormulatedColumnByRelationIdAndColumnName($child['id'], $constant, Doctrine_Core::HYDRATE_ARRAY);
                            $finalValue                                        = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                            $children[$key][$constant . '-final_value']        = $finalValue;
                            $children[$key][$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                            $children[$key][$constant . '-has_cell_reference'] = false;
                            $children[$key][$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                            $children[$key][$constant . '-linked']             = $formulatedColumn ? $formulatedColumn['linked'] : false;
                            $children[$key][$constant . '-has_build_up']       = $formulatedColumn ? $formulatedColumn['has_build_up'] : false;

                            if ( $formulatedColumn && $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                            {
                                $rate = $formulatedColumn['final_value'];
                            }

                            if ( $formulatedColumn && $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
                            {
                                $itemMarkupPercentage = $formulatedColumn['final_value'];
                            }
                        }

                        $rateAfterMarkup                     = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
                        $children[$key]['rate_after_markup'] = $rateAfterMarkup;

                        foreach ( $bill->BillColumnSettings as $column )
                        {
                            foreach ( $billItemTypeFormulatedColumnConstants as $constant )
                            {
                                $children[$key][$column->id . '-' . $constant . '-final_value']        = 0;
                                $children[$key][$column->id . '-' . $constant . '-value']              = '';
                                $children[$key][$column->id . '-' . $constant . '-has_cell_reference'] = false;
                                $children[$key][$column->id . '-' . $constant . '-has_formula']        = false;
                                $children[$key][$column->id . '-' . $constant . '-linked']             = false;
                                $children[$key][$column->id . '-' . $constant . '-has_build_up']       = false;
                            }

                            $quantityPerUnit = 0;

                            if ( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($child['id'], $quantityPerUnitByColumns[$column->id]) )
                            {
                                $quantityPerUnit = $quantityPerUnitByColumns[$column->id][$child['id']][0];
                                unset( $quantityPerUnitByColumns[$column->id][$child['id']] );
                            }

                            if ( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($child['id'], $billItemTypeReferences[$column->id]) )
                            {
                                $billItemTypeRef     = $billItemTypeReferences[$column->id][$child['id']];
                                $include             = $billItemTypeRef['include'] ? 'true' : 'false';
                                $totalQuantity       = $billItemTypeRef['total_quantity'];
                                $quantityPerUnitDiff = $billItemTypeRef['quantity_per_unit_difference'];
                                $totalPerUnit        = number_format($rateAfterMarkup * $quantityPerUnit, 2, '.', '');
                                $total               = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                                unset( $billItemTypeReferences[$column->id][$child['id']] );

                                if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
                                {
                                    foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
                                    {
                                        $children[$key][$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-final_value']  = number_format($billItemTypeRefFormulatedColumn['final_value'], 2, '.', '');
                                        $children[$key][$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-value']        = BillItemTypeReferenceFormulatedColumnTable::getConvertedValue($billItemTypeRefFormulatedColumn['value']);
                                        $children[$key][$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-linked']       = $billItemTypeRefFormulatedColumn['linked'];
                                        $children[$key][$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];
                                        $children[$key][$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-has_formula']  = $billItemTypeRefFormulatedColumn && $billItemTypeRefFormulatedColumn['value'] != $billItemTypeRefFormulatedColumn['final_value'] ? true : false;

                                        unset( $billItemTypeRefFormulatedColumn );
                                    }
                                }
                            }
                            else
                            {
                                $include             = 'true';//default value is true
                                $totalQuantity       = 0;
                                $quantityPerUnitDiff = 0;
                                $totalPerUnit        = 0;
                                $total               = 0;
                            }

                            $children[$key][$column->id . '-include']                      = $include;
                            $children[$key][$column->id . '-quantity_per_unit_difference'] = $quantityPerUnitDiff;
                            $children[$key][$column->id . '-total_quantity']               = $totalQuantity;
                            $children[$key][$column->id . '-total_per_unit']               = $totalPerUnit;
                            $children[$key][$column->id . '-total']                        = $total;
                        }

                        $children[$key]['grand_total_after_markup'] = 0;
                    }

                    $data['id']                          = $newItem->id;
                    $data['bill_ref']                    = '';
                    $data['description']                 = $newItem->description;
                    $data['type']                        = (string) $newItem->type;
                    $data['uom_id']                      = $newItem->uom_id > 0 ? (string) $newItem->uom_id : '-1';
                    $data['uom_symbol']                  = $newItem->uom_id > 0 ? $newItem->UnitOfMeasurement->symbol : '';
                    $data['grand_total_quantity']        = $newItem->grand_total_quantity;
                    $data['relation_id']                 = $newItem->element_id;
                    $data['linked']                      = false;
                    $data['markup_rounding_type']        = $roundingType;
                    $data['level']                       = $newItem->level;
                    $data['has_note']                    = ( $newItem->note != null && $newItem->note != '' ) ? true : false;
                    $data['note']                        = (string) $newItem->note;
                    $data['_csrf_token']                 = $form->getCSRFToken();
                    $data['version']                     = $latestProjectRevision['version'];
                    $data['project_revision_deleted_at'] = null;

                    /* setting up prime cost rate value */
                    $data['pc_supply_rate']             = $newItem->PrimeCostRate ? $newItem->PrimeCostRate->supply_rate : 0;
                    $data['pc_wastage_percentage']      = $newItem->PrimeCostRate ? $newItem->PrimeCostRate->wastage_percentage : 0;
                    $data['pc_wastage_amount']          = $newItem->PrimeCostRate ? $newItem->PrimeCostRate->wastage_amount : 0;
                    $data['pc_labour_for_installation'] = $newItem->PrimeCostRate ? $newItem->PrimeCostRate->labour_for_installation : 0;
                    $data['pc_other_cost']              = $newItem->PrimeCostRate ? $newItem->PrimeCostRate->other_cost : 0;
                    $data['pc_profit_percentage']       = $newItem->PrimeCostRate ? $newItem->PrimeCostRate->profit_percentage : 0;
                    $data['pc_profit_amount']           = $newItem->PrimeCostRate ? $newItem->PrimeCostRate->profit_amount : 0;
                    $data['pc_total']                   = $newItem->PrimeCostRate ? $newItem->PrimeCostRate->total : 0;

                    $rate                 = 0;
                    $itemMarkupPercentage = 0;
                    foreach ( $formulatedColumnConstants as $constant )
                    {
                        $formulatedColumn                        = BillItemTable::getFormulatedColumnByRelationIdAndColumnName($newItem->id, $constant, Doctrine_Core::HYDRATE_ARRAY);
                        $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                        $data[$constant . '-final_value']        = $finalValue;
                        $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                        $data[$constant . '-has_cell_reference'] = false;
                        $data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                        $data[$constant . '-linked']             = $formulatedColumn ? $formulatedColumn['linked'] : false;
                        $data[$constant . '-has_build_up']       = $formulatedColumn ? $formulatedColumn['has_build_up'] : false;

                        if ( $formulatedColumn && $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                        {
                            $rate = $formulatedColumn['final_value'];
                        }

                        if ( $formulatedColumn && $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
                        {
                            $itemMarkupPercentage = $formulatedColumn['final_value'];
                        }
                    }

                    $rateAfterMarkup           = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
                    $data['rate_after_markup'] = $rateAfterMarkup;

                    foreach ( $bill->BillColumnSettings as $column )
                    {
                        foreach ( $billItemTypeFormulatedColumnConstants as $constant )
                        {
                            $data[$column->id . '-' . $constant . '-final_value']        = 0;
                            $data[$column->id . '-' . $constant . '-value']              = '';
                            $data[$column->id . '-' . $constant . '-has_cell_reference'] = false;
                            $data[$column->id . '-' . $constant . '-has_formula']        = false;
                            $data[$column->id . '-' . $constant . '-linked']             = false;
                            $data[$column->id . '-' . $constant . '-has_build_up']       = false;
                        }

                        $quantityPerUnit = 0;

                        if ( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($newItem->id, $quantityPerUnitByColumns[$column->id]) )
                        {
                            $quantityPerUnit = $quantityPerUnitByColumns[$column->id][$newItem->id][0];
                            unset( $quantityPerUnitByColumns[$column->id][$newItem->id] );
                        }

                        if ( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($newItem->id, $billItemTypeReferences[$column->id]) )
                        {
                            $billItemTypeRef     = $billItemTypeReferences[$column->id][$newItem->id];
                            $include             = $billItemTypeRef['include'] ? 'true' : 'false';
                            $totalQuantity       = $billItemTypeRef['total_quantity'];
                            $quantityPerUnitDiff = $billItemTypeRef['quantity_per_unit_difference'];
                            $totalPerUnit        = number_format($rateAfterMarkup * $quantityPerUnit, 2, '.', '');
                            $total               = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                            unset( $billItemTypeReferences[$column->id][$newItem->id] );

                            if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
                            {
                                foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
                                {
                                    $data[$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-final_value']  = number_format($billItemTypeRefFormulatedColumn['final_value'], 2, '.', '');
                                    $data[$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-value']        = BillItemTypeReferenceFormulatedColumnTable::getConvertedValue($billItemTypeRefFormulatedColumn['value']);
                                    $data[$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-linked']       = $billItemTypeRefFormulatedColumn['linked'];
                                    $data[$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];
                                    $data[$column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-has_formula']  = $billItemTypeRefFormulatedColumn && $billItemTypeRefFormulatedColumn['value'] != $billItemTypeRefFormulatedColumn['final_value'] ? true : false;

                                    unset( $billItemTypeRefFormulatedColumn );
                                }
                            }
                        }
                        else
                        {
                            $include             = 'true';//default value is true
                            $totalQuantity       = 0;
                            $quantityPerUnitDiff = 0;
                            $totalPerUnit        = 0;
                            $total               = 0;
                        }

                        $data[$column->id . '-include']                      = $include;
                        $data[$column->id . '-quantity_per_unit_difference'] = $quantityPerUnitDiff;
                        $data[$column->id . '-total_quantity']               = $totalQuantity;
                        $data[$column->id . '-total_per_unit']               = $totalPerUnit;
                        $data[$column->id . '-total']                        = $total;
                    }

                    $data['grand_total_after_markup'] = 0;

                    unset( $billItemTypeReferences, $billItemTypeRefFormulatedColumns, $quantityPerUnitByColumns );

                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }

                break;
            case 'qty'://paste copied quantity cell
                $this->forward404Unless($billColumnSetting = BillColumnSettingTable::getInstance()->find($request->getParameter('bill_column_setting_id')) and
                    $targetBillColumnSetting = BillColumnSettingTable::getInstance()->find($request->getParameter('target_bill_column_setting_id')));

                $billItemTypeRef = $item->getBillItemTypeReferenceByColumnSettingId($billColumnSetting->id, Doctrine_Core::HYDRATE_ARRAY);

                try
                {
                    $targetItem->copyQuantityCellFromItem($billItemTypeRef['id'], $targetBillColumnSetting->id, BillItem::COPY_QUANTITY_CELL_ORIGINAL);

                    $targetItem->refresh(true);

                    $bill         = $item->Element->ProjectStructure;
                    $roundingType = $bill->BillMarkupSetting->rounding_type;

                    /*
                    * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
                    */
                    $elementMarkupResult = DoctrineQuery::create()->select('COALESCE(c.final_value, 0)')
                        ->from('BillElementFormulatedColumn c')->leftJoin('c.BillElement e')->leftJoin('e.ProjectStructure p')->leftJoin('p.BillMarkupSetting s')
                        ->where('c.relation_id = ?', $item->element_id)
                        ->andWhere('c.column_name = ?', BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE)
                        ->andWhere('s.element_markup_enabled IS TRUE')
                        ->limit(1)
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->fetchOne();

                    $elementMarkupPercentage = $elementMarkupResult ? $elementMarkupResult['final_value'] : 0;

                    $markupSettingsInfo = array(
                        'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
                        'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
                        'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
                        'element_markup_percentage' => $elementMarkupPercentage,
                        'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
                        'rounding_type'             => $roundingType
                    );

                    $targetItem->updateBillItemTotalColumns();

                    $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($targetItem->id, $markupSettingsInfo);
                    $markupAmountColumn = $targetItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                    $data = array(
                        'id'                        => $targetItem->id,
                        'uom_id'                    => $targetItem->uom_id > 0 ? (string) $targetItem->uom_id : '-1',
                        'uom_symbol'                => $targetItem->uom_id > 0 ? $targetItem->UnitOfMeasurement->symbol : '',
                        'markup_amount-value'       => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                        'markup_amount-final_value' => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                        'grand_total'               => $targetItem->grand_total,
                        'grand_total_after_markup'  => $targetItem->getGrandTotalAfterMarkup(),
                        'grand_total_quantity'      => $targetItem->grand_total_quantity
                    );

                    $grandTotalAfterMarkup = 0;

                    $billItemTypeRef                 = BillItemTypeReferenceTable::getByItemIdAndColumnId($targetItem->id, $targetBillColumnSetting->id, Doctrine_Core::HYDRATE_ARRAY);
                    $formulatedColumnQuantityPerUnit = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT);

                    $data[$targetBillColumnSetting->id . '-quantity_per_unit-value']              = BillItemTypeReferenceFormulatedColumnTable::getConvertedValue($formulatedColumnQuantityPerUnit->value);
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit-final_value']        = $formulatedColumnQuantityPerUnit->final_value;
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit-has_build_up']       = $formulatedColumnQuantityPerUnit->has_build_up;
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit-linked']             = $formulatedColumnQuantityPerUnit->linked;
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit-has_cell_reference'] = $formulatedColumnQuantityPerUnit->hasCellReference();
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit-has_formula']        = $formulatedColumnQuantityPerUnit->hasFormula();
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit_difference']         = $billItemTypeRef['quantity_per_unit_difference'];
                    $data[$targetBillColumnSetting->id . '-total_quantity']                       = $billItemTypeRef['total_quantity'];

                    foreach ( $bill->BillColumnSettings as $column )
                    {
                        $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($targetItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                        if ( $billItemTypeRef )
                        {
                            $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                            $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                            $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                            $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                            $columnId                            = $billItemTypeRef['bill_column_setting_id'];
                            $data[$columnId . '-total_per_unit'] = $totalPerUnit;
                            $data[$columnId . '-total']          = $total;

                            $grandTotalAfterMarkup += $total;
                        }
                    }

                    $data['grand_total_after_markup'] = $grandTotalAfterMarkup;

                    $referencedNodes = $formulatedColumnQuantityPerUnit->getNodesRelatedByColumnName(BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT);

                    foreach ( $referencedNodes as $k => $referencedNode )
                    {
                        if ( $node = Doctrine_Core::getTable('BillItemTypeReferenceFormulatedColumn')->find($referencedNode['node_from']) )
                        {
                            $affectedBillItem = $node->BillItemTypeReference->BillItem;
                            $affectedBillItem->updateBillItemTotalColumns();

                            $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($affectedBillItem->id, $markupSettingsInfo);
                            $markupAmountColumn = $affectedBillItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                            $affectedNode = array(
                                'id'                        => $affectedBillItem->id,
                                'uom_id'                    => $affectedBillItem->uom_id > 0 ? (string) $affectedBillItem->uom_id : '-1',
                                'uom_symbol'                => $affectedBillItem->uom_id > 0 ? $affectedBillItem->UnitOfMeasurement->symbol : '',
                                'markup_amount-value'       => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                                'markup_amount-final_value' => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                                'grand_total'               => $affectedBillItem->grand_total,
                                'grand_total_after_markup'  => $affectedBillItem->getGrandTotalAfterMarkup(),
                                'grand_total_quantity'      => $affectedBillItem->grand_total_quantity
                            );

                            $billItemTypeRef                 = BillItemTypeReferenceTable::getByItemIdAndColumnId($affectedBillItem->id, $targetBillColumnSetting->id, Doctrine_Core::HYDRATE_ARRAY);
                            $formulatedColumnQuantityPerUnit = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT);

                            $affectedNode[$targetBillColumnSetting->id . '-quantity_per_unit-value']       = BillItemTypeReferenceFormulatedColumnTable::getConvertedValue($formulatedColumnQuantityPerUnit->value);
                            $affectedNode[$targetBillColumnSetting->id . '-quantity_per_unit-final_value'] = $formulatedColumnQuantityPerUnit->final_value;
                            $affectedNode[$targetBillColumnSetting->id . '-quantity_per_unit_difference']  = $billItemTypeRef['quantity_per_unit_difference'];
                            $affectedNode[$targetBillColumnSetting->id . '-total_quantity']                = $billItemTypeRef['total_quantity'];

                            $grandTotalAfterMarkup = 0;
                            foreach ( $bill->BillColumnSettings as $column )
                            {
                                $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($affectedBillItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                                if ( $billItemTypeRef )
                                {
                                    $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                                    $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                                    $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                                    $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                                    $columnId                                    = $billItemTypeRef['bill_column_setting_id'];
                                    $affectedNode[$columnId . '-total_per_unit'] = $totalPerUnit;
                                    $affectedNode[$columnId . '-total']          = $total;

                                    $grandTotalAfterMarkup += $total;
                                }
                            }

                            $affectedNode['grand_total_after_markup'] = $grandTotalAfterMarkup;

                            array_push($children, $affectedNode);

                            unset( $affectedBillItem );
                        }

                        unset( $referencedNodes[$k], $referencedNode );
                    }

                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $success  = false;
                    $errorMsg = $e->getMessage();
                }

                break;
            case 'qty_remeasurement'://paste copied remeasurement quantity cell
                $this->forward404Unless($billColumnSetting = BillColumnSettingTable::getInstance()->find($request->getParameter('bill_column_setting_id')) and
                    $targetBillColumnSetting = BillColumnSettingTable::getInstance()->find($request->getParameter('target_bill_column_setting_id')));

                $billItemTypeRef = $item->getBillItemTypeReferenceByColumnSettingId($billColumnSetting->id, Doctrine_Core::HYDRATE_ARRAY);

                try
                {
                    $targetItem->copyQuantityCellFromItem($billItemTypeRef['id'], $targetBillColumnSetting->id, BillItem::COPY_QUANTITY_CELL_REMEASUREMENT);

                    $targetItem->refresh(true);

                    $bill         = $item->Element->ProjectStructure;
                    $roundingType = $bill->BillMarkupSetting->rounding_type;

                    /*
                    * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
                    */
                    $elementMarkupResult = DoctrineQuery::create()->select('COALESCE(c.final_value, 0)')
                        ->from('BillElementFormulatedColumn c')->leftJoin('c.BillElement e')->leftJoin('e.ProjectStructure p')->leftJoin('p.BillMarkupSetting s')
                        ->where('c.relation_id = ?', $item->element_id)
                        ->andWhere('c.column_name = ?', BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE)
                        ->andWhere('s.element_markup_enabled IS TRUE')
                        ->limit(1)
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->fetchOne();

                    $elementMarkupPercentage = $elementMarkupResult ? $elementMarkupResult['final_value'] : 0;

                    $markupSettingsInfo = array(
                        'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
                        'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
                        'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
                        'element_markup_percentage' => $elementMarkupPercentage,
                        'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
                        'rounding_type'             => $roundingType
                    );

                    $targetItem->updateBillItemTotalColumns();

                    $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($targetItem->id, $markupSettingsInfo);
                    $markupAmountColumn = $targetItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                    $data = array(
                        'id'                        => $targetItem->id,
                        'uom_id'                    => $targetItem->uom_id > 0 ? (string) $targetItem->uom_id : '-1',
                        'uom_symbol'                => $targetItem->uom_id > 0 ? $targetItem->UnitOfMeasurement->symbol : '',
                        'markup_amount-value'       => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                        'markup_amount-final_value' => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                        'grand_total'               => $targetItem->grand_total,
                        'grand_total_after_markup'  => $targetItem->getGrandTotalAfterMarkup(),
                        'grand_total_quantity'      => $targetItem->grand_total_quantity
                    );

                    $grandTotalAfterMarkup = 0;

                    $billItemTypeRef                 = BillItemTypeReferenceTable::getByItemIdAndColumnId($targetItem->id, $targetBillColumnSetting->id, Doctrine_Core::HYDRATE_ARRAY);
                    $formulatedColumnQuantityPerUnit = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT);

                    $data[$targetBillColumnSetting->id . '-quantity_per_unit_remeasurement-value']              = BillItemTypeReferenceFormulatedColumnTable::getConvertedValue($formulatedColumnQuantityPerUnit->value);
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit_remeasurement-final_value']        = $formulatedColumnQuantityPerUnit->final_value;
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit_remeasurement-has_build_up']       = $formulatedColumnQuantityPerUnit->has_build_up;
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit_remeasurement-linked']             = $formulatedColumnQuantityPerUnit->linked;
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit_remeasurement-has_cell_reference'] = $formulatedColumnQuantityPerUnit->hasCellReference();
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit_remeasurement-has_formula']        = $formulatedColumnQuantityPerUnit->hasFormula();
                    $data[$targetBillColumnSetting->id . '-quantity_per_unit_difference']                       = $billItemTypeRef['quantity_per_unit_difference'];
                    $data[$targetBillColumnSetting->id . '-total_quantity']                                     = $billItemTypeRef['total_quantity'];

                    foreach ( $bill->BillColumnSettings as $column )
                    {
                        $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($targetItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                        if ( $billItemTypeRef )
                        {
                            $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                            $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                            $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                            $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                            $columnId                            = $billItemTypeRef['bill_column_setting_id'];
                            $data[$columnId . '-total_per_unit'] = $totalPerUnit;
                            $data[$columnId . '-total']          = $total;

                            $grandTotalAfterMarkup += $total;
                        }
                    }

                    $data['grand_total_after_markup'] = $grandTotalAfterMarkup;

                    $referencedNodes = $formulatedColumnQuantityPerUnit->getNodesRelatedByColumnName(BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT);

                    foreach ( $referencedNodes as $k => $referencedNode )
                    {
                        if ( $node = Doctrine_Core::getTable('BillItemTypeReferenceFormulatedColumn')->find($referencedNode['node_from']) )
                        {
                            $affectedBillItem = $node->BillItemTypeReference->BillItem;
                            $affectedBillItem->updateBillItemTotalColumns();

                            $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($affectedBillItem->id, $markupSettingsInfo);
                            $markupAmountColumn = $affectedBillItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                            $affectedNode = array(
                                'id'                        => $affectedBillItem->id,
                                'uom_id'                    => $affectedBillItem->uom_id > 0 ? (string) $affectedBillItem->uom_id : '-1',
                                'uom_symbol'                => $affectedBillItem->uom_id > 0 ? $affectedBillItem->UnitOfMeasurement->symbol : '',
                                'markup_amount-value'       => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                                'markup_amount-final_value' => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                                'grand_total'               => $affectedBillItem->grand_total,
                                'grand_total_after_markup'  => $affectedBillItem->getGrandTotalAfterMarkup(),
                                'grand_total_quantity'      => $affectedBillItem->grand_total_quantity
                            );

                            $billItemTypeRef                 = BillItemTypeReferenceTable::getByItemIdAndColumnId($affectedBillItem->id, $targetBillColumnSetting->id, Doctrine_Core::HYDRATE_ARRAY);
                            $formulatedColumnQuantityPerUnit = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT);

                            $affectedNode[$targetBillColumnSetting->id . '-quantity_per_unit_remeasurement-value']       = BillItemTypeReferenceFormulatedColumnTable::getConvertedValue($formulatedColumnQuantityPerUnit->value);
                            $affectedNode[$targetBillColumnSetting->id . '-quantity_per_unit_remeasurement-final_value'] = $formulatedColumnQuantityPerUnit->final_value;
                            $affectedNode[$targetBillColumnSetting->id . '-quantity_per_unit_difference']                = $billItemTypeRef['quantity_per_unit_difference'];
                            $affectedNode[$targetBillColumnSetting->id . '-total_quantity']                              = $billItemTypeRef['total_quantity'];

                            $grandTotalAfterMarkup = 0;
                            foreach ( $bill->BillColumnSettings as $column )
                            {
                                $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($affectedBillItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                                if ( $billItemTypeRef )
                                {
                                    $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                                    $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                                    $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                                    $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                                    $columnId                                    = $billItemTypeRef['bill_column_setting_id'];
                                    $affectedNode[$columnId . '-total_per_unit'] = $totalPerUnit;
                                    $affectedNode[$columnId . '-total']          = $total;

                                    $grandTotalAfterMarkup += $total;
                                }
                            }

                            $affectedNode['grand_total_after_markup'] = $grandTotalAfterMarkup;

                            array_push($children, $affectedNode);
                            unset( $affectedBillItem );
                        }

                        unset( $referencedNodes[$k], $referencedNode );
                    }

                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $success  = false;
                    $errorMsg = $e->getMessage();
                }

                break;
            case 'rate'://paste copied rate cell
                try
                {
                    $targetItem->copyRateCellFromItem($item);

                    $targetItem->refresh(true);

                    $bill         = $item->Element->ProjectStructure;
                    $roundingType = $bill->BillMarkupSetting->rounding_type;

                    /*
                    * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
                    */
                    $elementMarkupResult = DoctrineQuery::create()->select('COALESCE(c.final_value, 0)')
                        ->from('BillElementFormulatedColumn c')->leftJoin('c.BillElement e')->leftJoin('e.ProjectStructure p')->leftJoin('p.BillMarkupSetting s')
                        ->where('c.relation_id = ?', $item->element_id)
                        ->andWhere('c.column_name = ?', BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE)
                        ->andWhere('s.element_markup_enabled IS TRUE')
                        ->limit(1)
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->fetchOne();

                    $elementMarkupPercentage = $elementMarkupResult ? $elementMarkupResult['final_value'] : 0;

                    $markupSettingsInfo = array(
                        'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
                        'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
                        'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
                        'element_markup_percentage' => $elementMarkupPercentage,
                        'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
                        'rounding_type'             => $roundingType
                    );

                    $rateColumn = $targetItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_RATE);

                    if ( $rateColumn )
                    {
                        $targetItem->updateBillItemTotalColumns();

                        $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($targetItem->id, $markupSettingsInfo);
                        $markupAmountColumn = $targetItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                        $data = array(
                            'id'                        => $targetItem->id,
                            'rate-value'                => $rateColumn->value,
                            'rate-final_value'          => $rateColumn->final_value,
                            'rate-has_build_up'         => $rateColumn->has_build_up,
                            'rate-linked'               => $rateColumn->linked,
                            'rate-has_cell_reference'   => $rateColumn->hasCellReference(),
                            'rate-has_formula'          => $rateColumn->hasFormula(),
                            'rate_after_markup'         => $rateAfterMarkup,
                            'markup_amount-value'       => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                            'markup_amount-final_value' => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                            'grand_total'               => $targetItem->grand_total
                        );

                        /* setting up prime cost rate value */
                        $data['pc_supply_rate']             = $targetItem->PrimeCostRate ? $targetItem->PrimeCostRate->supply_rate : 0;
                        $data['pc_wastage_percentage']      = $targetItem->PrimeCostRate ? $targetItem->PrimeCostRate->wastage_percentage : 0;
                        $data['pc_wastage_amount']          = $targetItem->PrimeCostRate ? $targetItem->PrimeCostRate->wastage_amount : 0;
                        $data['pc_labour_for_installation'] = $targetItem->PrimeCostRate ? $targetItem->PrimeCostRate->labour_for_installation : 0;
                        $data['pc_other_cost']              = $targetItem->PrimeCostRate ? $targetItem->PrimeCostRate->other_cost : 0;
                        $data['pc_profit_percentage']       = $targetItem->PrimeCostRate ? $targetItem->PrimeCostRate->profit_percentage : 0;
                        $data['pc_profit_amount']           = $targetItem->PrimeCostRate ? $targetItem->PrimeCostRate->profit_amount : 0;
                        $data['pc_total']                   = $targetItem->PrimeCostRate ? $targetItem->PrimeCostRate->total : 0;

                        $grandTotalAfterMarkup = 0;
                        foreach ( $bill->BillColumnSettings as $column )
                        {
                            $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($targetItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                            if ( $billItemTypeRef )
                            {
                                $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                                $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                                $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                                $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                                $columnId                            = $billItemTypeRef['bill_column_setting_id'];
                                $data[$columnId . '-total_per_unit'] = $totalPerUnit;
                                $data[$columnId . '-total']          = $total;

                                $grandTotalAfterMarkup += $total;
                            }
                        }

                        $data['grand_total_after_markup'] = $grandTotalAfterMarkup;

                        $referencedNodes = $rateColumn->getNodesRelatedByColumnName(BillItem::FORMULATED_COLUMN_RATE);

                        foreach ( $referencedNodes as $k => $referencedNode )
                        {
                            if ( $node = Doctrine_Core::getTable('BillItemFormulatedColumn')->find($referencedNode['node_from']) )
                            {
                                $affectedBillItem = $node->BillItem;
                                $affectedBillItem->updateBillItemTotalColumns();

                                $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($affectedBillItem->id, $markupSettingsInfo);
                                $markupAmountColumn = $affectedBillItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                                $affectedNode = array(
                                    'id'                        => $node->relation_id,
                                    'rate-final_value'          => $node->final_value,
                                    'rate_after_markup'         => $rateAfterMarkup,
                                    'markup_amount-value'       => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                                    'markup_amount-final_value' => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                                    'grand_total'               => $affectedBillItem->grand_total
                                );

                                $affectedNode['pc_supply_rate']             = $affectedBillItem->PrimeCostRate ? $affectedBillItem->PrimeCostRate->supply_rate : 0;
                                $affectedNode['pc_wastage_percentage']      = $affectedBillItem->PrimeCostRate ? $affectedBillItem->PrimeCostRate->wastage_percentage : 0;
                                $affectedNode['pc_wastage_amount']          = $affectedBillItem->PrimeCostRate ? $affectedBillItem->PrimeCostRate->wastage_amount : 0;
                                $affectedNode['pc_labour_for_installation'] = $affectedBillItem->PrimeCostRate ? $affectedBillItem->PrimeCostRate->labour_for_installation : 0;
                                $affectedNode['pc_other_cost']              = $affectedBillItem->PrimeCostRate ? $affectedBillItem->PrimeCostRate->other_cost : 0;
                                $affectedNode['pc_profit_percentage']       = $affectedBillItem->PrimeCostRate ? $affectedBillItem->PrimeCostRate->profit_percentage : 0;
                                $affectedNode['pc_profit_amount']           = $affectedBillItem->PrimeCostRate ? $affectedBillItem->PrimeCostRate->profit_amount : 0;
                                $affectedNode['pc_total']                   = $affectedBillItem->PrimeCostRate ? $affectedBillItem->PrimeCostRate->total : 0;

                                $grandTotalAfterMarkup = 0;
                                foreach ( $bill->BillColumnSettings as $column )
                                {
                                    $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($affectedBillItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                                    if ( $billItemTypeRef )
                                    {
                                        $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                                        $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                                        $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                                        $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                                        $columnId                                    = $billItemTypeRef['bill_column_setting_id'];
                                        $affectedNode[$columnId . '-total_per_unit'] = $totalPerUnit;
                                        $affectedNode[$columnId . '-total']          = $total;

                                        $grandTotalAfterMarkup += $total;
                                    }
                                }

                                $affectedNode['grand_total_after_markup'] = $grandTotalAfterMarkup;

                                array_push($children, $affectedNode);

                                unset( $affectedBillItem );
                            }
                            unset( $referencedNodes[$k], $referencedNode );
                        }
                    }

                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $success  = false;
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

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and
            $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and
            $request->hasParameter('method') and $request->hasParameter('addendum_id')
        );

        $element             = $item->Element;
        $items               = array();
        $affectedNodes       = array();
        $errorMsg            = null;
        $deleteMethod        = $request->getParameter('method');
        $currentBQAddendumId = $request->getParameter('addendum_id');

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            switch ($deleteMethod)
            {
                case 'normal':

                    $items = DoctrineQuery::create()->select('i.id')
                        ->from('BillItem i')
                        ->where('i.root_id = ?', $item->root_id)
                        ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $item->lft, $item->rgt ))
                        ->addOrderBy('i.lft')
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

                    $affectedNodes = $item->delete($con);

                    break;
                case 'strikeThroughDelete':

                    $bill    = $element->ProjectStructure;
                    $itemIds = $item->strikeThroughDelete($currentBQAddendumId);

                    $formulatedColumnConstants             = Utilities::getAllFormulatedColumnConstants('BillItem');
                    $billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

                    /*
                     * Since all information were deleted. We can just set everything to a default 'empty' data
                     */
                    $data = array();
                    foreach ( $formulatedColumnConstants as $constant )
                    {
                        $data[$constant . '-final_value']        = 0;
                        $data[$constant . '-value']              = '';
                        $data[$constant . '-has_cell_reference'] = false;
                        $data[$constant . '-has_formula']        = false;
                        $data[$constant . '-linked']             = false;
                        $data[$constant . '-has_build_up']       = false;
                    }

                    foreach ( $bill->BillColumnSettings as $column )
                    {
                        $data[$column->id . '-quantity_per_unit_difference'] = 0;
                        $data[$column->id . '-total_quantity']               = 0;
                        $data[$column->id . '-total_per_unit']               = 0;
                        $data[$column->id . '-total']                        = 0;

                        foreach ( $billItemTypeFormulatedColumnConstants as $constant )
                        {
                            $data[$column->id . '-' . $constant . '-final_value']        = 0;
                            $data[$column->id . '-' . $constant . '-value']              = '';
                            $data[$column->id . '-' . $constant . '-has_cell_reference'] = false;
                            $data[$column->id . '-' . $constant . '-has_formula']        = false;
                            $data[$column->id . '-' . $constant . '-linked']             = false;
                            $data[$column->id . '-' . $constant . '-has_build_up']       = false;
                        }
                    }

                    foreach ( $itemIds as $itemId )
                    {
                        array_push($items, array_merge(array(
                            'id'                          => $itemId,
                            'bill_ref'                    => null,
                            'project_revision_deleted_at' => true,
                            'grand_total'                 => 0,
                            'grand_total_quantity'        => 0
                        ), $data));
                    }
                    break;
                default:
                    throw new Exception('invalid deleted method');
            }

            $con->commit();

            $element->updateMarkupAmount();
            $success = true;
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => $affectedNodes ));
    }

    public function executeItemRateDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and $request->isMethod('post') and
            $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $element   = $item->Element;
        $bill      = $element->ProjectStructure;
        $errorMsg  = null;
        $billItems = array();

        try
        {
            if ( $item->type == BillItem::TYPE_ITEM_PC_RATE )
            {
                Doctrine_Query::create()
                    ->delete('BillItemPrimeCostRate pc')
                    ->where('pc.bill_item_id = ?', $item->id)
                    ->execute();

                $item->refreshRelated('PrimeCostRate');
            }

            $items = $item->deleteFormulatedColumns();

            $item->deleteBuildUpRates();

            $item->updateTypeTotalAmount();

            $item->updateBillItemTotalColumns();

            $element->updateMarkupAmount();

            array_push($items, array( 'id' => $item->id ));

            foreach ( $items as $item )
            {
                $data = array(
                    'id'                        => $item['id'],
                    'rate-value'                => '',
                    'rate-final_value'          => 0,
                    'rate-has_build_up'         => false,
                    'rate-linked'               => false,
                    'rate-has_cell_reference'   => false,
                    'rate-has_formula'          => false,
                    'rate_after_markup'         => 0,
                    'markup_amount-value'       => 0,
                    'markup_amount-final_value' => 0,
                    'grand_total'               => 0
                );

                /* setting up prime cost rate value */
                $data['pc_supply_rate']             = 0;
                $data['pc_wastage_percentage']      = 0;
                $data['pc_wastage_amount']          = 0;
                $data['pc_labour_for_installation'] = 0;
                $data['pc_other_cost']              = 0;
                $data['pc_profit_percentage']       = 0;
                $data['pc_profit_amount']           = 0;
                $data['pc_total']                   = 0;

                foreach ( $bill->BillColumnSettings as $column )
                {
                    $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($item['id'], $column->id, Doctrine_Core::HYDRATE_ARRAY);

                    if ( $billItemTypeRef )
                    {
                        $columnId                            = $billItemTypeRef['bill_column_setting_id'];
                        $data[$columnId . '-total_per_unit'] = 0;
                        $data[$columnId . '-total']          = 0;
                    }
                }

                $data['grand_total_after_markup'] = 0;

                array_push($billItems, $data);
            }

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $billItems ));
    }

    public function executeItemQuantityDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and
            $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')));

        $element  = $item->Element;
        $errorMsg = null;

        try
        {
            $billItemTypeReference = BillItemTypeReferenceTable::getByItemIdAndColumnId($item->id, $billColumnSetting->id);

            if ( $request->getParameter('type') == 'qty' )
            {
                $columnName              = BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT;
                $billBuildUpQuantityType = BillBuildUpQuantityItem::QUANTITY_PER_UNIT_ORIGINAL;
                $fieldName               = 'quantity_per_unit';
            }
            else
            {
                $columnName              = BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;
                $billBuildUpQuantityType = BillBuildUpQuantityItem::QUANTITY_PER_UNIT_REMEASUREMENT;
                $fieldName               = 'quantity_per_unit_remeasurement';
            }

            $items = $billItemTypeReference->deleteFormulatedColumnByColumnName($columnName);

            BillItemTable::deleteBuildUpQuantityByItemIdAndColumnSettingId($item->id, $billColumnSetting->id, $billBuildUpQuantityType);

            $element->updateMarkupAmount();

            $item->updateBillItemTotalColumns();
            $item->updateTypeTotalAmount();
            $item->refresh();

            $quantityFieldName       = $billColumnSetting->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;
            $billMarkupSetting       = $billColumnSetting->ProjectStructure->BillMarkupSetting;
            $elementMarkupPercentage = $element->getFormulatedColumnByName(BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE, Doctrine_Core::HYDRATE_ARRAY);
            $elementMarkupPercentage = $elementMarkupPercentage ? $elementMarkupPercentage['final_value'] : 0;

            $rateAfterMarkup = BillItemTable::calculateRateAfterMarkupById($item->id, array(
                'bill_markup_enabled'       => $billMarkupSetting->bill_markup_enabled,
                'bill_markup_percentage'    => $billMarkupSetting->bill_markup_percentage,
                'element_markup_enabled'    => $billMarkupSetting->element_markup_enabled,
                'element_markup_percentage' => $elementMarkupPercentage,
                'item_markup_enabled'       => $billMarkupSetting->item_markup_enabled,
                'rounding_type'             => $billMarkupSetting->rounding_type
            ));

            $data['id']                       = $item->id;
            $data['grand_total_quantity']     = $item->grand_total_quantity;
            $data['grand_total']              = $item->grand_total;
            $data['grand_total_after_markup'] = $item->getGrandTotalAfterMarkup();

            $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeReference->id, $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
            $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
            $total        = $totalPerUnit * $billColumnSetting->quantity;

            $data[$billColumnSetting->id . '-quantity_per_unit_difference'] = $billItemTypeReference->quantity_per_unit_difference;
            $data[$billColumnSetting->id . '-total_quantity']               = $billItemTypeReference->total_quantity;
            $data[$billColumnSetting->id . '-total_per_unit']               = $totalPerUnit;
            $data[$billColumnSetting->id . '-total']                        = $total;

            $data[$billColumnSetting->id . '-' . $fieldName . '-final_value']        = 0;
            $data[$billColumnSetting->id . '-' . $fieldName . '-value']              = 0;
            $data[$billColumnSetting->id . '-' . $fieldName . '-linked']             = false;
            $data[$billColumnSetting->id . '-' . $fieldName . '-has_build_up']       = false;
            $data[$billColumnSetting->id . '-' . $fieldName . '-has_cell_reference'] = false;
            $data[$billColumnSetting->id . '-' . $fieldName . '-has_formula']        = 0;

            array_push($items, $data);

            $success = true;
        } catch (Exception $e)
        {
            $items    = array();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

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
                    ->from('BillItem i')
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

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

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
                    ->from('BillItem i')
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

    /**** item actions end ****/

    public function executeItemNoteUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')));

        $item     = null;
        $errorMsg = null;

        try
        {
            $billItem->note = $request->getParameter('item_note');

            $billItem->save();

            $item = array(
                'id'       => $billItem->id,
                'note'     => $billItem->note,
                'has_note' => ( $billItem->note != null && $billItem->note != '' ) ? true : false
            );

            $success = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'item'     => $item
        ));
    }

    public function executePrimeCostRateForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));

        $form = new BillItemPrimeCostRateForm($billItem->PrimeCostRate);

        return $this->renderJson(array(
            'bill_item_prime_cost_rate[supply_rate]'             => number_format($form->getObject()->supply_rate, 2, '.', ''),
            'bill_item_prime_cost_rate[wastage_percentage]'      => number_format($form->getObject()->wastage_percentage, 3, '.', ''),
            'bill_item_prime_cost_rate[wastage_amount]'          => number_format($form->getObject()->wastage_amount, 2, '.', ''),
            'bill_item_prime_cost_rate[labour_for_installation]' => number_format($form->getObject()->labour_for_installation, 2, '.', ''),
            'bill_item_prime_cost_rate[other_cost]'              => number_format($form->getObject()->other_cost, 2, '.', ''),
            'bill_item_prime_cost_rate[profit_percentage]'       => number_format($form->getObject()->profit_percentage, 3, '.', ''),
            'bill_item_prime_cost_rate[profit_amount]'           => number_format($form->getObject()->profit_amount, 2, '.', ''),
            'bill_item_prime_cost_rate[total]'                   => number_format($form->getObject()->total, 2, '.', ''),
            'bill_item_prime_cost_rate[_csrf_token]'             => $form->getCSRFToken()
        ));
    }

    public function executePrimeCostRateUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));

        $form = new BillItemPrimeCostRateForm($billItem->PrimeCostRate);

        if ( $this->isFormValid($request, $form) )
        {
            $primeCostRate = $form->save();

            $billItem = $primeCostRate->BillItem;

            $billItem->updateBillItemTotalColumns();

            $formulatedColumn   = $billItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_RATE);
            $markupAmountColumn = $billItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

            $item = array(
                'rate-final_value'           => $formulatedColumn->final_value,
                'rate-value'                 => $formulatedColumn->value,
                'rate-has_cell_reference'    => false,
                'rate-has_formula'           => false,
                'rate-linked'                => $formulatedColumn->linked,
                'rate-has_build_up'          => $formulatedColumn->has_build_up,
                'pc_supply_rate'             => $primeCostRate->supply_rate,
                'pc_wastage_percentage'      => $primeCostRate->wastage_percentage,
                'pc_wastage_amount'          => $primeCostRate->wastage_amount,
                'pc_labour_for_installation' => $primeCostRate->labour_for_installation,
                'pc_other_cost'              => $primeCostRate->other_cost,
                'pc_profit_percentage'       => $primeCostRate->profit_percentage,
                'pc_profit_amount'           => $primeCostRate->profit_amount,
                'pc_total'                   => $primeCostRate->total,
                'markup_amount-value'        => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                'markup_amount-final_value'  => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                'grand_total'                => $billItem->grand_total
            );

            $elementMarkupPercentage = $billItem->Element->getFormulatedColumnByName(BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE, Doctrine_Core::HYDRATE_ARRAY);
            $elementMarkupPercentage = $elementMarkupPercentage ? $elementMarkupPercentage['final_value'] : 0;

            $billMarkupSetting = $billItem->Element->ProjectStructure->BillMarkupSetting;

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

            foreach ( $billItem->BillItemTypeReferences as $billItemTypeRef )
            {
                $column            = $billItemTypeRef->BillColumnSetting;
                $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef->id, $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                $total        = $totalPerUnit * $column->quantity;

                $item[$column->id . '-total_per_unit'] = $totalPerUnit;
                $item[$column->id . '-total']          = $total;

                $grandTotalAfterMarkup += $total;
            }

            $item['grand_total_after_markup'] = $grandTotalAfterMarkup;
            $item['rate_after_markup']        = $rateAfterMarkup;

            $bill = $billItem->Element->ProjectStructure;

            $affectedNodes = array();

            $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName(BillItem::FORMULATED_COLUMN_RATE);

            foreach ( $referencedNodes as $referencedNode )
            {
                $node = Doctrine_Core::getTable('BillItemFormulatedColumn')->find($referencedNode['node_from']);

                if ( $node )
                {
                    $affectedBillItem = $node->BillItem;
                    $affectedBillItem->updateBillItemTotalColumns();

                    $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($affectedBillItem->id, $markupSettingsInfo);
                    $markupAmountColumn = $affectedBillItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                    $affectedNode = array(
                        'id'                        => $node->relation_id,
                        'rate-final_value'          => $node->final_value,
                        'rate_after_markup'         => $rateAfterMarkup,
                        'markup_amount-value'       => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                        'markup_amount-final_value' => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                        'grand_total'               => $affectedBillItem->grand_total
                    );

                    $grandTotalAfterMarkup = 0;
                    foreach ( $bill->BillColumnSettings as $column )
                    {
                        $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($affectedBillItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                        if ( $billItemTypeRef )
                        {
                            $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                            $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                            $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                            $total        = $totalPerUnit * $column->quantity;

                            $columnId                                    = $billItemTypeRef['bill_column_setting_id'];
                            $affectedNode[$columnId . '-total_per_unit'] = $totalPerUnit;
                            $affectedNode[$columnId . '-total']          = $total;

                            $grandTotalAfterMarkup += $total;
                        }
                    }

                    $affectedNode['grand_total_after_markup'] = $grandTotalAfterMarkup;

                    array_push($affectedNodes, $affectedNode);
                }
                unset( $affectedBillItem );
            }

            $data    = array(
                'item'           => $item,
                'affected_nodes' => $affectedNodes
            );
            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $data    = array();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'data' => $data ));
    }

    public function executeLumpSumPercentageForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));

        $form = new BillItemLumpSumPercentageForm($billItem->LumpSumPercentage);

        return $this->renderJson(array(
            'bill_item_lump_sum_percentage[rate]'        => number_format($form->getObject()->rate, 2, '.', ''),
            'bill_item_lump_sum_percentage[percentage]'  => number_format($form->getObject()->percentage, 2, '.', ''),
            'bill_item_lump_sum_percentage[amount]'      => number_format($form->getObject()->amount, 2, '.', ''),
            'bill_item_lump_sum_percentage[_csrf_token]' => $form->getCSRFToken()
        ));
    }

    public function executeLumpSumPercentageUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));

        $form = new BillItemLumpSumPercentageForm($billItem->LumpSumPercentage);

        if ( $this->isFormValid($request, $form) )
        {
            $lumpSumPercentage = $form->save();

            $billItem = $lumpSumPercentage->BillItem;

            $billItem->updateBillItemTotalColumns();

            $formulatedColumn   = $billItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_RATE);
            $markupAmountColumn = $billItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

            $item = array(
                'rate-final_value'          => $formulatedColumn->final_value,
                'rate-value'                => $formulatedColumn->value,
                'rate-has_cell_reference'   => false,
                'rate-has_formula'          => false,
                'rate-linked'               => $formulatedColumn->linked,
                'rate-has_build_up'         => $formulatedColumn->has_build_up,
                'markup_amount-value'       => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                'markup_amount-final_value' => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                'grand_total'               => $billItem->grand_total
            );

            $elementMarkupPercentage = $billItem->Element->getFormulatedColumnByName(BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE, Doctrine_Core::HYDRATE_ARRAY);
            $elementMarkupPercentage = $elementMarkupPercentage ? $elementMarkupPercentage['final_value'] : 0;

            $billMarkupSetting = $billItem->Element->ProjectStructure->BillMarkupSetting;

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

            foreach ( $billItem->BillItemTypeReferences as $billItemTypeRef )
            {
                $column            = $billItemTypeRef->BillColumnSetting;
                $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef->id, $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                $total        = $totalPerUnit * $column->quantity;

                $item[$column->id . '-total_per_unit'] = $totalPerUnit;
                $item[$column->id . '-total']          = $total;

                $grandTotalAfterMarkup += $total;
            }

            $item['grand_total_after_markup'] = $grandTotalAfterMarkup;
            $item['rate_after_markup']        = $rateAfterMarkup;

            $bill = $billItem->Element->ProjectStructure;

            $affectedNodes = array();

            $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName(BillItem::FORMULATED_COLUMN_RATE);

            foreach ( $referencedNodes as $referencedNode )
            {
                $node = Doctrine_Core::getTable('BillItemFormulatedColumn')->find($referencedNode['node_from']);

                if ( $node )
                {
                    $affectedBillItem = $node->BillItem;
                    $affectedBillItem->updateBillItemTotalColumns();

                    $rateAfterMarkup    = BillItemTable::calculateRateAfterMarkupById($affectedBillItem->id, $markupSettingsInfo);
                    $markupAmountColumn = $affectedBillItem->getFormulatedColumnByName(BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT, Doctrine_Core::HYDRATE_ARRAY);

                    $affectedNode = array(
                        'id'                        => $node->relation_id,
                        'rate-final_value'          => $node->final_value,
                        'rate_after_markup'         => $rateAfterMarkup,
                        'markup_amount-value'       => $markupAmountColumn ? $markupAmountColumn['value'] : 0,
                        'markup_amount-final_value' => $markupAmountColumn ? $markupAmountColumn['final_value'] : 0,
                        'grand_total'               => $affectedBillItem->grand_total
                    );

                    $grandTotalAfterMarkup = 0;
                    foreach ( $bill->BillColumnSettings as $column )
                    {
                        $billItemTypeRef = BillItemTypeReferenceTable::getByItemIdAndColumnId($affectedBillItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                        if ( $billItemTypeRef )
                        {
                            $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                            $quantity     = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                            $totalPerUnit = $quantity ? $rateAfterMarkup * $quantity['final_value'] : 0;
                            $total        = $totalPerUnit * $column->quantity;

                            $columnId                                    = $billItemTypeRef['bill_column_setting_id'];
                            $affectedNode[$columnId . '-total_per_unit'] = $totalPerUnit;
                            $affectedNode[$columnId . '-total']          = $total;

                            $grandTotalAfterMarkup += $total;
                        }
                    }

                    $affectedNode['grand_total_after_markup'] = $grandTotalAfterMarkup;

                    array_push($affectedNodes, $affectedNode);
                }
                unset( $affectedBillItem );
            }

            $data    = array(
                'item'           => $item,
                'affected_nodes' => $affectedNodes
            );
            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $data    = array();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'data' => $data ));
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
            $query->addWhere('u.type = ?', $structure->BillSetting->unit_type);
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

    public function executeGetBillInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and 
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $data['markup_settings'] = array(
            'bill_markup_enabled'    => $structure->BillMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage' => number_format($structure->BillMarkupSetting->bill_markup_percentage, 2, '.', ''),
            'bill_markup_amount'     => number_format($structure->BillMarkupSetting->bill_markup_amount, 2, '.', ''),
            'element_markup_enabled' => $structure->BillMarkupSetting->element_markup_enabled,
            'item_markup_enabled'    => $structure->BillMarkupSetting->item_markup_enabled,
            'rounding_type'          => $structure->BillMarkupSetting->rounding_type
        );

        $data['bill_type'] = array(
            'id'   => $structure->BillType->id,
            'type' => $structure->BillType->type
        );

        $currentlyEditingProjectRevision = ProjectRevisionTable::getCurrentlyEditingProjectRevisionFromBillId($structure->getRoot()->id);
        $isBillLocked = true;

        if($currentlyEditingProjectRevision && $currentlyEditingProjectRevision->id == $structure->project_revision_id)
        {
            $isBillLocked = false;
        }

        $data['bill'] = [
            'project' => $structure->getRoot()->id,
            'id' => $structure->id,
            'project_revision_id' => $structure->ProjectRevision->id,
            'project_revision' => $structure->ProjectRevision->revision,
            'is_bill_locked'   => $isBillLocked,
        ];

        $data['column_settings'] = DoctrineQuery::create()->select('c.id, c.name, c.quantity, c.is_hidden, c.total_floor_area_m2, c.total_floor_area_ft2, c.floor_area_has_build_up, c.floor_area_use_metric, c.floor_area_display_metric, c.show_estimated_total_cost, c.remeasurement_quantity_enabled, c.use_original_quantity')
            ->from('BillColumnSetting c')
            ->where('c.project_structure_id = ?', $structure->id)
            ->addOrderBy('c.id ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        // get current BQ's Addendum Version
        $data['project_revision_status'] = DoctrineQuery::create()->select('br.revision, br.locked_status, br.version')
            ->from('ProjectRevision br')
            ->where('br.project_structure_id = ?', $structure->root_id)
            ->addOrderBy('br.id DESC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->limit(1)
            ->fetchOne();

        // get selected printable BQ's Addendum Version
        $data['printable_project_revision_status'] = DoctrineQuery::create()->select('br.revision, br.locked_status, br.version')
            ->from('ProjectRevision br')
            ->where('br.project_structure_id = ?', $structure->root_id)
            ->andWhere('br.current_selected_revision = ?', true)
            ->addOrderBy('br.id DESC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->limit(1)
            ->fetchOne();

        // addendum printing csrf protection
        $addendumCSRF = new BaseForm();

        $data['bqCSRFToken'] = $addendumCSRF->getCSRFToken();

        return $this->renderJson($data);
    }

}
