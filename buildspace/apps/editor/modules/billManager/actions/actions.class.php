<?php

class billManagerActions extends BaseActions
{
    public function executeGetBillInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();
        $project = ProjectStructureTable::getInstance()->find($bill->root_id);

        $editorProjectInfo = $company->getEditorProjectInformationByProject($project);

        $data['printable_project_revision'] = [
            'revision'=> $editorProjectInfo->PrintRevision->revision,
            'version' => $editorProjectInfo->PrintRevision->version
        ];

        $currentRevision = ProjectRevisionTable::getLatestLockedProjectRevisionFromBillId($bill->root_id, Doctrine_Core::HYDRATE_ARRAY);

        $currentBillVersion = $currentRevision ? $currentRevision['version'] : 0;

        $data['current_bill_version'] = $currentBillVersion;

        $data['bill_type'] = array(
            'id'   => $bill->BillType->id,
            'type' => $bill->BillType->type
        );

        $data['column_settings'] = DoctrineQuery::create()->select('c.id, c.name, c.quantity, c.is_hidden, c.total_floor_area_m2, c.total_floor_area_ft2, c.floor_area_has_build_up, c.floor_area_use_metric, c.floor_area_display_metric, c.show_estimated_total_cost, c.remeasurement_quantity_enabled, c.use_original_quantity')
            ->from('BillColumnSetting c')
            ->where('c.project_structure_id = ?', $bill->id)
            ->addOrderBy('c.id ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        $data['_csrf_token'] = $form->getCSRFToken();

        return $this->renderJson($data);
    }

    public function executeBillPropertiesForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

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

    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();
        $form    = new BaseForm();

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $elementSumByBillColumnSetting = array();

        //we get sum of elements total by bill column setting so we won't keep on calling the same query in element list loop
        foreach ( $bill->BillColumnSettings as $column )
        {
            //Get Element Total Rates
            $elementTotalRates                          = $company->getTotalItemRateByAndBillColumnSettingIdGroupByElement($bill, $column);
            $elementSumByBillColumnSetting[$column->id] = $elementTotalRates['grandTotalElement'];
            $totalRateByBillColumnSetting[$column->id]  = $elementTotalRates['elementToRates'];
            unset( $column );
        }

        //Get Element Grand Totals
        $elementGrandTotals    = $company->getElementGrandTotalByBillIdGroupByElement($bill->id);
        $elementsWithAddendums = $bill->getElementsWithAddendums();
        $latestProjectRevision = $bill->getRoot()->getLatestProjectRevision();

        foreach ( $elements as $key => $element )
        {
            $originalGrandTotal      = 0;
            $overallTotalAfterMarkup = 0;
            $elementSumTotal         = 0;

            foreach ( $bill->BillColumnSettings as $column )
            {
                $billElementTypeRef = BillElementTypeReferenceTable::getByElementIdAndBillColumnSettingId($element['id'], $column->id);

                $total        = $totalRateByBillColumnSetting[$column->id][$element['id']];
                $totalPerUnit = $total / $column->quantity;

                $elements[$key][$column->id . '-total_per_unit'] = $totalPerUnit;
                $elements[$key][$column->id . '-total']          = $total;
                $elements[$key][$column->id . '-total_cost']     = $column->getTotalCostPerFloorArea($totalPerUnit);

                $elements[$key][$column->id . '-element_sum_total'] = $elementSumByBillColumnSetting[$column->id];

                $originalGrandTotal += $totalRateByBillColumnSetting[$column->id][$element['id']];
                $overallTotalAfterMarkup += $total;
                $elementSumTotal += $elements[$key][$column->id . '-element_sum_total'];

                unset( $column );
            }

            $elements[$key]['is_add_latest_rev'] = 0;
            $elements[$key]['addendum_version']  = null;
            if(array_key_exists($element['id'], $elementsWithAddendums))
            {
                $latestAddendum = end($elementsWithAddendums[$element['id']]);
                $elements[$key]['is_add_latest_rev'] = (int)($latestAddendum['version']==$latestProjectRevision->version);
                $elements[$key]['addendum_version']  = (int)$latestAddendum['version'];
                unset($elementsWithAddendums[$element['id']]);
            }

            $elements[$key]['grand_total']                = array_key_exists($element['id'], $elementGrandTotals) ? $elementGrandTotals[$element['id']] : 0;
            $elements[$key]['element_sum_total']          = $elementSumTotal;
            $elements[$key]['relation_id']                = $bill->id;
            $elements[$key]['_csrf_token']                = $form->getCSRFToken();
        }

        $defaultLastRow = array(
            'id'                => Constants::GRID_LAST_ROW,
            'description'       => '',
            'grand_total'       => 0,
            'element_sum_total' => 0,
            'relation_id'       => $bill->id,
            'is_add_latest_rev' => 0,
            'addendum_version'  => null,
            '_csrf_token'       => $form->getCSRFToken()
        );

        foreach ( $bill->BillColumnSettings as $column )
        {
            $defaultLastRow[$column->id . '-total_cost']                      = 0;
            $defaultLastRow[$column->id . '-total_per_unit']                  = 0;
            $defaultLastRow[$column->id . '-total']                           = 0;
            $defaultLastRow[$column->id . '-element_sum_total']               = 0;
        }

        array_push($elements, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }
    
    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($element->project_structure_id)
        );
        
        $pdo          = $bill->getTable()->getConnection()->getDbh();
        $user         = $this->getUser()->getGuardUser();
        $company      = $user->Profile->getEProjectUser()->Company->getBsCompany();
        $form         = new BaseForm();
        $items        = [];
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;

        $company->recalibrateEditorItemQty($element);

        list(
            $billItems, $rates, $quantityPerUnitByColumns,
            $billItemTypeReferences, $billItemTypeRefFormulatedColumns,
            $editorBillItemTypeReferences, $editorNotListedItems
        ) = $company->getEditorBillItems($element);

        foreach ( $billItems as $billItem )
        {
            $rate = 0;

            $billItem['bill_ref'] = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
            $billItem['type']     = (string) $billItem['type'];

            if($billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED)
            {
                if(array_key_exists($billItem['id'], $editorNotListedItems))
                {
                    $billItem['description'] = $editorNotListedItems[$billItem['id']]['description'];
                    $billItem['uom_id']      = $editorNotListedItems[$billItem['id']]['uom_id'] > 0 ? (string) $editorNotListedItems[$billItem['id']]['uom_id'] : '-1';
                    $billItem['uom_symbol']  = $editorNotListedItems[$billItem['id']]['uom_symbol'];

                }
                else
                {
                    $billItem['uom_id']      = '-1';
                    $billItem['uom_symbol']  = '';
                }
            }
            else
            {
                $billItem['uom_id'] = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
            }

            $billItem['relation_id']                 = $element->id;
            $billItem['linked']                      = false;
            $billItem['_csrf_token']                 = $form->getCSRFToken();
            $billItem['project_revision_deleted_at'] = is_null($billItem['project_revision_deleted_at']) ? false : $billItem['project_revision_deleted_at'];

            $billItem[BillItem::FORMULATED_COLUMN_RATE . '-final_value'] = 0;
            $billItem[BillItem::FORMULATED_COLUMN_RATE . '-value']       = '';
            $billItem[BillItem::FORMULATED_COLUMN_RATE . '-linked']      = false;
            $billItem[BillItem::FORMULATED_COLUMN_RATE . '-has_formula'] = false;

            if ( array_key_exists($billItem['id'], $rates) )
            {
                $billItem[BillItem::FORMULATED_COLUMN_RATE . '-final_value']  = $rates[$billItem['id']]['final_value'];
                $billItem[BillItem::FORMULATED_COLUMN_RATE . '-value']        = $rates[$billItem['id']]['value'];
                $billItem[BillItem::FORMULATED_COLUMN_RATE . '-linked']       = $rates[$billItem['id']]['linked'];
                $billItem[BillItem::FORMULATED_COLUMN_RATE . '-has_formula']  = $rates[$billItem['id']]['value'] != $rates[$billItem['id']]['final_value'] ? true : false;

                $rate = $rates[$billItem['id']]['final_value'];

                unset( $rates[$billItem['id']] );
            }

            foreach ( $bill->BillColumnSettings as $column )
            {
                $quantityPerUnit = 0;

                $billItem[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-final_value']        = 0;
                $billItem[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value']              = '';
                $billItem[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_cell_reference'] = false;
                $billItem[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_formula']        = false;

                $totalQuantity = 0;
                $totalPerUnit  = 0;
                $total         = 0;

                if($billItem['type'] != BillItem::TYPE_ITEM_NOT_LISTED && (!array_key_exists($column->id, $editorBillItemTypeReferences) || ( array_key_exists($column->id, $editorBillItemTypeReferences) && !array_key_exists($billItem['id'], $editorBillItemTypeReferences[$column->id]))))
                {
                    if ( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($billItem['id'], $quantityPerUnitByColumns[$column->id]) )
                    {
                        $quantityPerUnit = $quantityPerUnitByColumns[$column->id][$billItem['id']][0];
                        unset( $quantityPerUnitByColumns[$column->id][$billItem['id']] );
                    }

                    if ( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$column->id]) )
                    {
                        $billItemTypeRef     = $billItemTypeReferences[$column->id][$billItem['id']];
                        $totalQuantity       = $billItemTypeRef['total_quantity'];
                        $totalPerUnit        = number_format($rate * $quantityPerUnit, 2, '.', '');
                        $total               = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                        unset( $billItemTypeReferences[$column->id][$billItem['id']] );

                        if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
                        {
                            foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
                            {
                                $billItem[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-final_value']  = number_format($billItemTypeRefFormulatedColumn['final_value'], 2, '.', '');
                                $billItem[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value']        = BillItemTypeReferenceFormulatedColumnTable::getConvertedValue($billItemTypeRefFormulatedColumn['value']);
                                $billItem[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_formula']  = $billItemTypeRefFormulatedColumn && $billItemTypeRefFormulatedColumn['value'] != $billItemTypeRefFormulatedColumn['final_value'] ? true : false;

                                unset( $billItemTypeRefFormulatedColumn );
                            }
                        }
                    }
                }

                if ( array_key_exists($column->id, $editorBillItemTypeReferences) && array_key_exists($billItem['id'], $editorBillItemTypeReferences[$column->id]) )
                {
                    $billItemTypeRef = $editorBillItemTypeReferences[$column->id][$billItem['id']];
                    $totalQuantity   = $billItemTypeRef['total_quantity'];
                    $totalPerUnit    = number_format($rate * $billItemTypeRef['quantity_per_unit'], 2, '.', '');
                    $total           = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                    unset( $editorBillItemTypeReferences[$column->id][$billItem['id']] );

                    $billItem[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-final_value']  = number_format($billItemTypeRef['quantity_per_unit'], 2, '.', '');
                    $billItem[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value']        = number_format($billItemTypeRef['quantity_per_unit'], 2, '.', '');
                    $billItem[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_formula']  = false;
                }

                $billItem[$column->id . '-total_quantity'] = $totalQuantity;
                $billItem[$column->id . '-total_per_unit'] = $totalPerUnit;
                $billItem[$column->id . '-total']          = $total;
            }

            array_push($items, $billItem);

            unset( $billItem );
        }

        unset( $billItems );

        $defaultLastRow = array(
            'id'                   => Constants::GRID_LAST_ROW,
            'bill_ref'             => '',
            'description'          => '',
            'type'                 => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'               => '-1',
            'uom_symbol'           => '',
            'relation_id'          => $element->id,
            'level'                => 0,
            'grand_total_quantity' => '',
            'grand_total'          => '',
            'linked'               => false,
            '_csrf_token'          => $form->getCSRFToken()
        );

        $defaultLastRow[BillItem::FORMULATED_COLUMN_RATE . '-final_value'] = 0;
        $defaultLastRow[BillItem::FORMULATED_COLUMN_RATE . '-value']       = 0;
        $defaultLastRow[BillItem::FORMULATED_COLUMN_RATE . '-linked']      = false;
        $defaultLastRow[BillItem::FORMULATED_COLUMN_RATE . '-has_formula'] = false;

        foreach ( $bill->BillColumnSettings as $column )
        {
            $defaultLastRow[$column->id . '-include']        = 'true';
            $defaultLastRow[$column->id . '-total_quantity'] = 0;
            $defaultLastRow[$column->id . '-total_per_unit'] = 0;
            $defaultLastRow[$column->id . '-total']          = 0;

            $defaultLastRow[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-final_value']        = 0;
            $defaultLastRow[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value']              = 0;
            $defaultLastRow[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_cell_reference'] = false;
            $defaultLastRow[$column->id . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_formula']        = false;
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

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $user          = $this->getUser()->getGuardUser();
        $company       = $user->Profile->getEProjectUser()->Company->getBsCompany();
        $bill          = $item->Element->ProjectStructure;
        $notListedItem = null;

        $rowData            = array();
        $affectedNodes      = array();
        $isFormulatedColumn = false;

        $con = $item->getTable()->getConnection();

        $billItemInfo = $company->getEditorBillItemInfoByBillItem($item);

        if(!$billItemInfo)
        {
            $billItemInfo = new EditorBillItemInfo();
            $billItemInfo->bill_item_id = $item->id;
            $billItemInfo->company_id = $company->id;

            $billItemInfo->save($con);
        }

        foreach ( $bill->BillColumnSettings as $column )
        {
            $billItemTypeRef       = BillItemTypeReferenceTable::getByItemIdAndColumnId($item->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);
            $editorBillItemTypeRef = EditorBillItemTypeReferenceTable::getByItemIdAndColumnId($billItemInfo->id, $column->id);

            if ( !$editorBillItemTypeRef )
            {
                $quantity      = 0;
                $totalQuantity = 0;

                if($item->type != BillItem::TYPE_ITEM_NOT_LISTED && $billItemTypeRef)
                {
                    $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                    $fc            = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                    $quantity      = $fc ? $fc['final_value'] : 0;
                    $totalQuantity = $billItemTypeRef['total_quantity'];
                }

                $editorBillItemTypeRef = new EditorBillItemTypeReference();
                $editorBillItemTypeRef->bill_item_info_id = $billItemInfo->id;
                $editorBillItemTypeRef->bill_column_setting_id = $column->id;
                $editorBillItemTypeRef->quantity_per_unit = $quantity;
                $editorBillItemTypeRef->total_quantity = $totalQuantity;
            }

            $editorBillItemTypeRef->save($con);
        }

        if($item->type == BillItem::TYPE_ITEM_NOT_LISTED)
        {
            $notListedItem = $company->getEditorBillItemNotListedByBillItem($item);

            if(!$notListedItem)
            {
                $notListedItem = new EditorBillItemNotListed();
                $notListedItem->bill_item_id = $item->id;
                $notListedItem->company_id = $company->id;
                $notListedItem->description = $item->description;

                $notListedItem->save($con);
            }
        }

        try
        {
            $con->beginTransaction();

            $fieldAttr  = explode('-', $request->getParameter('attr_name'));
            $fieldName  = count($fieldAttr) > 1 ? $fieldAttr[1] : $fieldAttr[0];
            $fieldValue = trim($request->getParameter('val'));
            $fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;

            if($fieldName == BillItem::FORMULATED_COLUMN_RATE)
            {
                $formulatedColumn = EditorBillItemFormulatedColumnTable::getInstance()->getByRelationIdAndColumnName($billItemInfo->id, BillItem::FORMULATED_COLUMN_RATE);

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->linked = false;

                $formulatedColumn->save($con);

                $formulatedColumn->refresh();

                $isFormulatedColumn = true;
            }
            elseif($item->type == BillItem::TYPE_ITEM_NOT_LISTED && count($fieldAttr) > 1)
            {
                //update qty
                $columnId = $fieldAttr[0];

                $billItemTypeRef = EditorBillItemTypeReferenceTable::getByItemIdAndColumnId($billItemInfo->id, $columnId);

                if ( !$billItemTypeRef )
                {
                    $billItemTypeRef                         = new EditorBillItemTypeReference();
                    $billItemTypeRef->bill_item_info_id      = $billItemInfo->id;
                    $billItemTypeRef->bill_column_setting_id = $columnId;
                    $billItemTypeRef->save($con);

                    $billItemTypeRef->refresh();
                }

                $billItemTypeRef->quantity_per_unit = (float)$fieldValue;

                $billItemTypeRef->save($con);

                $billItemTypeRef->refresh();

                $rowData[$columnId . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-final_value']  = number_format($billItemTypeRef->quantity_per_unit, 2, '.', '');
                $rowData[$columnId . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value']        = number_format($billItemTypeRef->quantity_per_unit, 2, '.', '');
            }
            elseif($item->type == BillItem::TYPE_ITEM_NOT_LISTED && ($fieldName == 'uom_id' || $fieldName == 'description'))
            {
                $fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;

                $notListedItem->{$fieldName} = $fieldValue;
                $notListedItem->save($con);

                $rowData[$fieldName] = $notListedItem->{$fieldName};
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $billItemInfo->updateBillItemTotalColumns();

            if ( $isFormulatedColumn )
            {
                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                foreach ( $referencedNodes as $referencedNode )
                {
                    $node = EditorBillItemFormulatedColumnTable::getInstance()->find($referencedNode['node_from']);

                    if ( $node )
                    {
                        $affectedBillItem = $node->EditorBillItemInfo;
                        $affectedBillItem->updateBillItemTotalColumns();

                        $itemRateResult = DoctrineQuery::create()->select('c.final_value')
                            ->from('EditorBillItemFormulatedColumn c')
                            ->leftJoin('c.EditorBillItemInfo i')
                            ->where('c.relation_id = ?', EditorBillItemInfo)
                            ->andWhere('c.column_name = ?', BillItem::FORMULATED_COLUMN_RATE)
                            ->limit(1)
                            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->fetchOne();

                        $rate = $itemRateResult ? $itemRateResult['final_value'] : 0;

                        $affectedNode = array(
                            'id'                                              => $node->relation_id,
                            BillItem::FORMULATED_COLUMN_RATE . '-final_value' => $node->final_value,
                            'grand_total_quantity'                            => $affectedBillItem->grand_total_quantity,
                            'grand_total'                                     => $affectedBillItem->grand_total
                        );

                        foreach ( $bill->BillColumnSettings as $column )
                        {
                            $billItemTypeRef = EditorBillItemTypeReferenceTable::getByItemIdAndColumnId($affectedBillItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                            if ( $billItemTypeRef )
                            {
                                $quantity     = $billItemTypeRef->quantity_per_unit;
                                $totalPerUnit = $rate * $billItemTypeRef->quantity_per_unit;
                                $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                                $columnId                                    = $billItemTypeRef['bill_column_setting_id'];
                                $affectedNode[$columnId . '-total_per_unit'] = $totalPerUnit;
                                $affectedNode[$columnId . '-total']          = $total;
                            }
                        }

                        array_push($affectedNodes, $affectedNode);
                    }
                    unset( $affectedBillItem );
                }
            }

            $formulatedColumn = EditorBillItemFormulatedColumnTable::getInstance()->getByRelationIdAndColumnName($billItemInfo->id, BillItem::FORMULATED_COLUMN_RATE);

            $finalValue                                                 = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
            $rowData[BillItem::FORMULATED_COLUMN_RATE . '-final_value'] = $finalValue;
            $rowData[BillItem::FORMULATED_COLUMN_RATE . '-value']       = $formulatedColumn ? $formulatedColumn['value'] : '';
            $rowData[BillItem::FORMULATED_COLUMN_RATE . '-has_formula'] = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            $rowData[BillItem::FORMULATED_COLUMN_RATE . '-linked']      = $formulatedColumn ? $formulatedColumn['linked'] : false;

            $rate = $formulatedColumn ? number_format($formulatedColumn['final_value'], 2, '.', '') : 0;

            foreach ( $bill->BillColumnSettings as $column )
            {
                $billItemTypeRef = EditorBillItemTypeReferenceTable::getByItemIdAndColumnId($billItemInfo->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                if ( $billItemTypeRef )
                {
                    $quantity      = $billItemTypeRef['quantity_per_unit'];
                    $totalQuantity = $billItemTypeRef['total_quantity'];
                    $totalPerUnit  = $quantity ? number_format($rate * $quantity, 2, '.', '') : 0;
                    $total         = $totalPerUnit * $column->quantity;
                }
                else
                {
                    $totalQuantity = 0;
                    $totalPerUnit  = 0;
                    $total         = 0;
                }

                $rowData[$column->id . '-total_quantity'] = $totalQuantity;
                $rowData[$column->id . '-total_per_unit'] = $totalPerUnit;
                $rowData[$column->id . '-total']          = $total;
            }

            $billItemInfo->refresh();

            $rowData['affected_nodes']       = $affectedNodes;
            $rowData['grand_total_quantity'] = $billItemInfo->grand_total_quantity;
            $rowData['grand_total']          = $billItemInfo->grand_total;
            $rowData['linked']               = false;
            $rowData['type']                 = (string) $item->type;

            if($notListedItem)
            {
                $rowData['uom_id']               = $notListedItem->uom_id > 0 ? (string) $notListedItem->uom_id : '-1';
                $rowData['uom_symbol']           = $notListedItem->uom_id > 0 ? $notListedItem->UnitOfMeasurement->symbol : '';
            }
            else{
                $rowData['uom_id']               = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
                $rowData['uom_symbol']           = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            }

            $rowData['version']              = $item->ProjectRevision->version;

            /* setting up prime cost rate value */
            $rowData['pc_supply_rate']             = number_format($item->PrimeCostRate->supply_rate, 2, '.', '');
            $rowData['pc_wastage_percentage']      = number_format($item->PrimeCostRate->wastage_percentage, 2, '.', '');
            $rowData['pc_wastage_amount']          = number_format($item->PrimeCostRate->wastage_amount, 2, '.', '');
            $rowData['pc_labour_for_installation'] = number_format($item->PrimeCostRate->labour_for_installation, 2, '.', '');
            $rowData['pc_other_cost']              = number_format($item->PrimeCostRate->other_cost, 2, '.', '');
            $rowData['pc_profit_percentage']       = number_format($item->PrimeCostRate->profit_percentage, 2, '.', '');
            $rowData['pc_profit_amount']           = number_format($item->PrimeCostRate->profit_amount, 2, '.', '');
            $rowData['pc_total']                   = number_format($item->PrimeCostRate->total, 2, '.', '');
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeGetUnits(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $options   = array();
        $values    = array();

        array_push($values, '-1');
        array_push($options, '---');

        $records = DoctrineQuery::create()->select('u.id, u.symbol')
            ->from('UnitOfMeasurement u')
            ->where('u.display IS TRUE')
            ->addOrderBy('u.symbol ASC')
            ->addWhere('u.type = ?', $bill->BillSetting->unit_type)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

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

    public function executePrimeCostRateForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();

        $billItemInfo = $company->getEditorBillItemInfoByBillItem($billItem);

        if(!$billItemInfo)
        {
            $billItemInfo = new EditorBillItemInfo();
            $billItemInfo->bill_item_id = $billItem->id;
            $billItemInfo->company_id = $company->id;

            $billItemInfo->save();

            foreach ( $billItem->Element->ProjectStructure->BillColumnSettings as $column )
            {
                $billItemTypeRef       = BillItemTypeReferenceTable::getByItemIdAndColumnId($billItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);
                $editorBillItemTypeRef = EditorBillItemTypeReferenceTable::getByItemIdAndColumnId($billItemInfo->id, $column->id);

                if ( !$editorBillItemTypeRef )
                {
                    $quantity      = 0;
                    $totalQuantity = 0;

                    if($billItemTypeRef)
                    {
                        $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                        $fc            = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                        $quantity      = $fc ? $fc['final_value'] : 0;
                        $totalQuantity = $billItemTypeRef['total_quantity'];
                    }

                    $editorBillItemTypeRef = new EditorBillItemTypeReference();
                    $editorBillItemTypeRef->bill_item_info_id = $billItemInfo->id;
                    $editorBillItemTypeRef->bill_column_setting_id = $column->id;
                    $editorBillItemTypeRef->quantity_per_unit = $quantity;
                    $editorBillItemTypeRef->total_quantity = $totalQuantity;
                }

                $editorBillItemTypeRef->save();
            }

            $editorPrimeCostRate =  new EditorBillItemPrimeCostRate();
            $editorPrimeCostRate->bill_item_info_id = $billItemInfo;
            $editorPrimeCostRate->supply_rate = $billItem->PrimeCostRate->supply_rate;
            $editorPrimeCostRate->total = $billItem->PrimeCostRate->supply_rate;

            $editorPrimeCostRate->save();
        }
        else
        {
            $editorPrimeCostRate = $billItemInfo->PrimeCostRate;
        }

        $form = new EditorBillItemPrimeCostRateForm($editorPrimeCostRate);

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
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();

        $billItemInfo = $company->getEditorBillItemInfoByBillItem($billItem);

        $form = new EditorBillItemPrimeCostRateForm($billItemInfo->PrimeCostRate);

        if ( $this->isFormValid($request, $form) )
        {
            $primeCostRate = $form->save();

            $billItemInfo     = $primeCostRate->EditorBillItemInfo;
            $formulatedColumn = EditorBillItemFormulatedColumnTable::getInstance()->getByRelationIdAndColumnName($billItemInfo->id, BillItem::FORMULATED_COLUMN_RATE);

            $billItemInfo->refresh();

            $item = [
                'rate-final_value'           => $formulatedColumn->final_value,
                'rate-value'                 => $formulatedColumn->value,
                'rate-has_formula'           => false,
                'rate-linked'                => $formulatedColumn->linked,
                'pc_supply_rate'             => $primeCostRate->supply_rate,
                'pc_wastage_percentage'      => $primeCostRate->wastage_percentage,
                'pc_wastage_amount'          => $primeCostRate->wastage_amount,
                'pc_labour_for_installation' => $primeCostRate->labour_for_installation,
                'pc_other_cost'              => $primeCostRate->other_cost,
                'pc_profit_percentage'       => $primeCostRate->profit_percentage,
                'pc_profit_amount'           => $primeCostRate->profit_amount,
                'pc_total'                   => $primeCostRate->total,
                'grand_total'                => $billItemInfo->grand_total
            ];

            $rate = $formulatedColumn ? number_format($formulatedColumn['final_value'], 2, '.', '') : 0;

            foreach ( $billItem->Element->ProjectStructure->BillColumnSettings as $column )
            {
                $billItemTypeRef = EditorBillItemTypeReferenceTable::getByItemIdAndColumnId($billItemInfo->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                if ( $billItemTypeRef )
                {
                    $quantity      = $billItemTypeRef['quantity_per_unit'];
                    $totalQuantity = $billItemTypeRef['total_quantity'];
                    $totalPerUnit  = $quantity ? number_format($rate * $quantity, 2, '.', '') : 0;
                    $total         = $totalPerUnit * $column->quantity;
                }
                else
                {
                    $totalQuantity = 0;
                    $totalPerUnit  = 0;
                    $total         = 0;
                }

                $item[$column->id . '-total_per_unit'] = $totalPerUnit;
                $item[$column->id . '-total']          = $total;
            }

            $data = [
                'item' => $item
            ];

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
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $user         = $this->getUser()->getGuardUser();
        $company      = $user->Profile->getEProjectUser()->Company->getBsCompany();
        $billItemInfo = $company->getEditorBillItemInfoByBillItem($billItem);

        if(!$billItemInfo)
        {
            $billItemInfo = new EditorBillItemInfo();
            $billItemInfo->bill_item_id = $billItem->id;
            $billItemInfo->company_id = $company->id;

            $billItemInfo->save();

            foreach ( $billItem->Element->ProjectStructure->BillColumnSettings as $column )
            {
                $billItemTypeRef       = BillItemTypeReferenceTable::getByItemIdAndColumnId($billItem->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);
                $editorBillItemTypeRef = EditorBillItemTypeReferenceTable::getByItemIdAndColumnId($billItemInfo->id, $column->id);

                if ( !$editorBillItemTypeRef )
                {
                    $quantity      = 0;
                    $totalQuantity = 0;

                    if($billItemTypeRef)
                    {
                        $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                        $fc            = BillItemTypeReferenceTable::getFormulatedColumnByRelationIdAndColumnName($billItemTypeRef['id'], $quantityFieldName, Doctrine_Core::HYDRATE_ARRAY);
                        $quantity      = $fc ? $fc['final_value'] : 0;
                        $totalQuantity = $billItemTypeRef['total_quantity'];
                    }

                    $editorBillItemTypeRef = new EditorBillItemTypeReference();
                    $editorBillItemTypeRef->bill_item_info_id = $billItemInfo->id;
                    $editorBillItemTypeRef->bill_column_setting_id = $column->id;
                    $editorBillItemTypeRef->quantity_per_unit = $quantity;
                    $editorBillItemTypeRef->total_quantity = $totalQuantity;
                }

                $editorBillItemTypeRef->save();
            }

            $lumpSumPercentage =  new EditorBillItemLumpSumPercentage();
            $lumpSumPercentage->bill_item_info_id = $billItemInfo;
            $lumpSumPercentage->rate = $billItem->LumpSumPercentage->rate;

            $lumpSumPercentage->save();
        }
        else
        {
            $lumpSumPercentage = $billItemInfo->LumpSumPercentage;
        }

        $form = new EditorBillItemLumpSumPercentageForm($lumpSumPercentage);

        return $this->renderJson(array(
            'bill_item_lump_sum_percentage[rate]'        => number_format($form->getObject()->rate, 2, '.', ''),
            'bill_item_lump_sum_percentage[percentage]'  => number_format($form->getObject()->percentage, 2, '.', ''),
            'bill_item_lump_sum_percentage[amount]'      => number_format($form->getObject()->amount, 2, '.', ''),
            'bill_item_lump_sum_percentage[_csrf_token]' => $form->getCSRFToken()
        ));
    }

    public function executeLumpSumPercentageUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $user         = $this->getUser()->getGuardUser();
        $company      = $user->Profile->getEProjectUser()->Company->getBsCompany();
        $billItemInfo = $company->getEditorBillItemInfoByBillItem($billItem);

        $form = new EditorBillItemLumpSumPercentageForm($billItemInfo->LumpSumPercentage);

        if ( $this->isFormValid($request, $form) )
        {
            $lumpSumPercentage = $form->save();

            $billItemInfo     = $lumpSumPercentage->EditorBillItemInfo;
            $formulatedColumn = EditorBillItemFormulatedColumnTable::getInstance()->getByRelationIdAndColumnName($billItemInfo->id, BillItem::FORMULATED_COLUMN_RATE);

            $billItemInfo->refresh();

            $item = [
                'rate-final_value'           => $formulatedColumn->final_value,
                'rate-value'                 => $formulatedColumn->value,
                'rate-has_formula'           => false,
                'rate-linked'                => $formulatedColumn->linked,
                'grand_total'                => $billItemInfo->grand_total
            ];

            $rate = $formulatedColumn ? number_format($formulatedColumn['final_value'], 2, '.', '') : 0;

            foreach ( $billItem->Element->ProjectStructure->BillColumnSettings as $column )
            {
                $billItemTypeRef = EditorBillItemTypeReferenceTable::getByItemIdAndColumnId($billItemInfo->id, $column->id, Doctrine_Core::HYDRATE_ARRAY);

                if ( $billItemTypeRef )
                {
                    $quantity      = $billItemTypeRef['quantity_per_unit'];
                    $totalQuantity = $billItemTypeRef['total_quantity'];
                    $totalPerUnit  = $quantity ? number_format($rate * $quantity, 2, '.', '') : 0;
                    $total         = $totalPerUnit * $column->quantity;
                }
                else
                {
                    $totalQuantity = 0;
                    $totalPerUnit  = 0;
                    $total         = 0;
                }

                $item[$column->id . '-total_per_unit'] = $totalPerUnit;
                $item[$column->id . '-total']          = $total;
            }

            $data = [
                'item' => $item
            ];

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

    public function executeSubmitTender(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot()
        );

        $user     = $this->getUser()->getGuardUser();
        $company  = $user->Profile->getEProjectUser()->Company->getBsCompany();
        $eproject = $project->MainInformation->getEProjectProject();

        try
        {
            $filesToZip = [];

            $count = 0;

            $projectData = ProjectStructureTable::getProjectInformationByProjectId($project->id);
            $projectData['structure']['tender_amount'] = $company->getEditorOverallTotalForProject($project->id);
            $projectData['structure']['tender_amount_except_prime_cost_provisional'] = $company->getEditorOverallTotalForProjectWithoutPrimeCostAndProvisionalBill($project->id);
            $projectData['structure']['tender_som_amount'] = 0;
            $projectData['structure']['id'] = $project->id;

            unset( $projectData['structure']['tender_origin_id'], $projectData['mainInformation']['id'] );
            
            $sfProjectExport = new sfBuildspaceExportEditorProjectXML($company, $count . "_" . $project->id, $project->MainInformation->unique_id, ExportedFile::EXPORT_TYPE_RATES);

            $currentRevision = ProjectRevisionTable::getLatestLockedProjectRevisionFromBillId($project->id, Doctrine_Core::HYDRATE_ARRAY);

            $sfProjectExport->process($projectData['structure'], $projectData['mainInformation'], null, array( $currentRevision ), $projectData['tenderAlternatives'], true);

            array_push($filesToZip, $sfProjectExport->getFileInformation());

            foreach ($projectData['breakdown'] as $k => $structure)
            {
                $count ++;

                $sfBillExport = null;
                $billData     = null;

                if ($structure['type'] == ProjectStructure::TYPE_BILL)
                {
                    $billData = $this->getBillRates($structure['id'], $company);

                    $sfBillExport = new sfBuildspaceExportEditorBillRatesXML($count . '_' . $structure['title'],
                        $sfProjectExport->uploadPath, $structure['id']);
                }

                if(is_object($sfBillExport) and is_array($billData))
                {
                    $sfBillExport->process($billData, true);

                    array_push($filesToZip, $sfBillExport->getFileInformation());

                    unset( $sfBillExport, $structure, $billData );
                }
            }

            $filename = "Rates_" . $project->id . "-".$company->id."-".date('Ymdhis');

            $sfZipGenerator = new sfZipGenerator($filename, null, 'tr', true, true);

            $sfZipGenerator->createZip($filesToZip);

            $fileInfo = $sfZipGenerator->getFileInfo();

            $response = $eproject->submitTendererRates($user, $fileInfo['pathToFile']);

            if($tender = $eproject->getLatestTender())
            {
                $isSelectedTenderer = $tender->currently_selected_tenderer_id === $company->getEProjectCompany()->id;

                if($isSelectedTenderer)
                {
                    $proc = new BackgroundProcess("exec php ".Utilities::getEProjectArtisanPath()." project:create-award-recommendation-bill-details ".$tender->id." 2>&1 ");
                    $proc->run();
                }
            }

            $success = $response['success'];
            $errorMsg = $response['errorMessage'];
        }
        catch (Exception $e)
        {
            $success = false;
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'errorMsg' => $errorMsg,
            'success'  => $success
        ));
    }

    protected function getBillRates($billId, Company $company)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $billStructure = array(
            'total_amount'     => $company->getEditorOverallTotalByBillId($billId),
            'elementsAndItems' => []
        );

        $stmt = $pdo->prepare("SELECT element.id, COALESCE(SUM(info.grand_total),0) AS grand_total
            FROM " . BillElementTable::getInstance()->getTableName() . " as element
            JOIN " . BillItemTable::getInstance()->getTableName() . " AS item ON item.element_id = element.id
            JOIN " . EditorBillItemInfoTable::getInstance()->getTableName() . " info ON info.bill_item_id = item.id
            WHERE item.project_revision_deleted_at IS NULL AND info.company_id = ".$company->id."
            AND item.type <> ".BillItem::TYPE_HEADER." AND item.type <> ".BillItem::TYPE_NOID." AND item.type <> ".BillItem::TYPE_HEADER_N."
            AND element.project_structure_id = " . $billId . "
            AND item.deleted_at IS NULL
            AND element.deleted_at IS NULL GROUP BY element.id ORDER BY element.id");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($elements as $elementId => $elementGrandTotal)
        {
            $result = [
                'id'           => $elementId,
                'total_amount' => $elementGrandTotal,
                'items'        => []
            ];

            $stmt = $pdo->prepare("SELECT item.id, info.id AS bill_info_id, item.type, item.uom_id, item.description, COALESCE(info.grand_total,0) AS grand_total, COALESCE(ifc.final_value,0) AS rate
                FROM " . EditorBillItemInfoTable::getInstance()->getTableName() . " info
                JOIN " . BillItemTable::getInstance()->getTableName() . " AS item ON info.bill_item_id = item.id
                LEFT JOIN " . EditorBillItemFormulatedColumnTable::getInstance()->getTableName() . " ifc ON ifc.relation_id = info.id AND ifc.deleted_at IS NULL AND ifc.column_name ='" . BillItem::FORMULATED_COLUMN_RATE . "'
                JOIN " . BillElementTable::getInstance()->getTableName() . " AS element ON item.element_id = element.id
                WHERE info.company_id = ".$company->id." AND element.id = " . $elementId . "
                AND item.type <> ".BillItem::TYPE_HEADER." AND item.type <> ".BillItem::TYPE_NOID." AND item.type <> ".BillItem::TYPE_HEADER_N."
                AND item.project_revision_deleted_at IS NULL
                AND item.deleted_at IS NULL
                AND element.deleted_at IS NULL");

            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT i.bill_item_id, i.description, i.uom_id AS uom_id
            FROM " . EditorBillItemNotListedTable::getInstance()->getTableName() . " i
            JOIN " . BillItemTable::getInstance()->getTableName() . " itm ON itm.id = i.bill_item_id
            WHERE i.company_id = ".$company->id." AND itm.element_id = " . $elementId . "
            AND itm.deleted_at IS NULL");

            $stmt->execute();

            $editorNotListedItems = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

            if(!empty($editorNotListedItems))
            {
                foreach($items as $key => $item)
                {
                    if(array_key_exists($item['id'], $editorNotListedItems))
                    {
                        $items[$key]['description'] = $editorNotListedItems[$item['id']]['description'];
                        $items[$key]['uom_id'] = $editorNotListedItems[$item['id']]['uom_id'];
                    }
                }
            }

            $result['items'] = $items;

            array_push($billStructure['elementsAndItems'], $result);
        }

        return $billStructure;
    }

    public function executeGetAddendumInfoByBill(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $addendums = $bill->getAddendumInfo();

        return $this->renderJson($addendums);
    }

    public function executeGetAddendumInfoByElement(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id'))
        );

        $addendums = $element->getAddendumInfo();

        return $this->renderJson($addendums);
    }
}
