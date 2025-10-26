<?php

/**
 * postContractSubPackageStandardBill actions.
 *
 * @package    buildspace
 * @subpackage postContractSubPackageStandardBill
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractSubPackageStandardBillActions extends BaseActions {

    public function executeGetPrintingPreviewDataByTypes(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        $pdo                = $project->getTable()->getConnection()->getDbh();
        $elementGrandTotals = array();
        $revision           = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $elements           = $this->getAffectedElements($pdo, $subPackage, $bill);

        // Get Type List
        $stmt = $pdo->prepare("SELECT type_ref.id, stype.bill_column_setting_id, type_ref.new_name,
		stype.sub_package_id, stype.counter, type_ref.post_contract_id
		FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
		JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . "
		type_ref ON type_ref.bill_column_setting_id = stype.bill_column_setting_id AND type_ref.counter = stype.counter
		JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
		WHERE cs.project_structure_id = " . $bill->id . " AND stype.sub_package_id = " . $subPackage->id . "
		ORDER BY stype.bill_column_setting_id, stype.counter ASC");

        $stmt->execute();

        $typeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $typeItems as $typeItem )
        {
            $object                         = new PostContractStandardClaimTypeReference();
            $object->id                     = $typeItem['id'];
            $object->bill_column_setting_id = $typeItem['bill_column_setting_id'];
            $object->post_contract_id       = $typeItem['post_contract_id'];

            $elementGrandTotals[$typeItem['bill_column_setting_id']][] = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($bill->id, $object, $revision, $subPackage->id);

            unset( $object );
        }

        foreach ( $bill->BillColumnSettings as $billColumnSetting )
        {
            $defaultElementsTotal = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByTypeRef($bill->id, $revision, $project->PostContract->id, $subPackage->id);
            $typeQuantityCounter  = 0;

            foreach ( $elements as $key => $element )
            {
                $elements[$key][$billColumnSetting['id'] . '-grand_total']                  = 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_percentage']        = 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] = 0;

                $elements[$key]['has_note'] = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            }

            // use PostContractStandardClaimTypeReference's if available
            if ( isset( $elementGrandTotals[$billColumnSetting['id']] ) )
            {
                foreach ( $elementGrandTotals[$billColumnSetting['id']] as $elementGrandTotal )
                {
                    foreach ( $elements as $key => $element )
                    {
                        $elementId = $element['id'];

                        if ( isset( $elementGrandTotal[$elementId] ) )
                        {
                            $elements[$key][$billColumnSetting['id'] . '-grand_total'] += $elementGrandTotal[$elementId][0]['total_per_unit'];
                            $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] += $elementGrandTotal[$elementId][0]['up_to_date_amount'];
                        }
                    }

                    $typeQuantityCounter ++;
                }
            }

            // assign element total for unit that haven't been instantiate yet.
            while ($typeQuantityCounter < $billColumnSetting['quantity'])
            {
                foreach ( $elements as $key => $element )
                {
                    if ( isset( $defaultElementsTotal[$element['id']] ) )
                    {
                        $elements[$key][$billColumnSetting['id'] . '-grand_total'] += $defaultElementsTotal[$element['id']][0]['total_per_unit'];
                    }
                }

                $typeQuantityCounter ++;
            }

            foreach ( $elements as $key => $element )
            {
                if ( $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] > 0 )
                {
                    $elements[$key][$billColumnSetting['id'] . '-type_total_percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'], $elements[$key][$billColumnSetting['id'] . '-grand_total']));
                }
            }

            unset( $billColumnSetting );
        }

        $defaultLastRow = array(
            'id'                    => Constants::GRID_LAST_ROW,
            'description'           => '',
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'claim_type_ref_id'     => - 1,
            'relation_id'           => $bill->id,
        );

        array_push($elements, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetPrintingPreviewDataByUnitsWithClaim(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        $pdo                = $project->getTable()->getConnection()->getDbh();
        $elementGrandTotals = array();
        $billColumns        = array();
        $gridStructure      = array();
        $revision           = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $elements           = $this->getAffectedElements($pdo, $subPackage, $bill);

        // Get Type List
        $stmt = $pdo->prepare("SELECT type_ref.id, stype.bill_column_setting_id, cs.name as bill_column_name,
		type_ref.new_name, stype.sub_package_id, stype.counter, type_ref.post_contract_id
		FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
		JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " type_ref
		ON type_ref.bill_column_setting_id = stype.bill_column_setting_id AND type_ref.counter = stype.counter
		JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
		WHERE cs.project_structure_id = " . $bill->id . " AND stype.sub_package_id = " . $subPackage->id . "
		ORDER BY stype.bill_column_setting_id, stype.counter ASC");

        $stmt->execute();
        $typeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $typeItems as $typeItem )
        {
            $object                         = new PostContractStandardClaimTypeReference();
            $object->id                     = $typeItem['id'];
            $object->bill_column_setting_id = $typeItem['bill_column_setting_id'];
            $object->post_contract_id       = $typeItem['post_contract_id'];

            $elementGrandTotals[$typeItem['bill_column_setting_id']][$typeItem['id']] = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($bill->id, $object, $revision, $subPackage->id);

            // get only type with up to date claims
            foreach ( $elementGrandTotals[$typeItem['bill_column_setting_id']][$typeItem['id']] as $elementGrandTotal )
            {
                if ( isset( $elementGrandTotal[0] ) AND !empty($elementGrandTotal[0]['up_to_date_amount']) )
                {
                    $billColumns[$typeItem['bill_column_setting_id']] = array(
                        'id'   => $typeItem['bill_column_setting_id'],
                        'name' => $typeItem['bill_column_name'],
                    );

                    // to be use from the front-end to generate dynamic table columns
                    $gridStructure[$typeItem['bill_column_setting_id']][] = array(
                        'id'       => $typeItem['id'],
                        'new_name' => ( $typeItem['new_name'] ) ? $typeItem['new_name'] : 'Unit ' . $typeItem['counter'],
                    );

                    break;
                }
            }

            unset( $object );
        }

        unset( $typeItems );

        foreach ( $bill->BillColumnSettings as $billColumnSetting )
        {
            $defaultElementsTotal = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByTypeRef($bill->id, $revision, $project->PostContract->id, $subPackage->id);
            $typeQuantityCounter  = 0;

            // assign default variable
            foreach ( $elements as $key => $element )
            {
                $elements[$key][$billColumnSetting['id'] . '-grand_total']                  = 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_percentage']        = 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] = 0;

                $elements[$key]['has_note'] = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            }

            // use PostContractStandardClaimTypeReference's if available
            if ( isset( $elementGrandTotals[$billColumnSetting['id']] ) )
            {
                foreach ( $elementGrandTotals[$billColumnSetting['id']] as $typeId => $elementGrandTotal )
                {
                    foreach ( $elements as $key => $element )
                    {
                        if ( isset( $elementGrandTotal[$element['id']] ) )
                        {
                            $elements[$key][$typeId . '-unit_total_percentage'] = $elementGrandTotal[$element['id']][0]['up_to_date_percentage'];
                            $elements[$key][$billColumnSetting['id'] . '-grand_total'] += $elementGrandTotal[$element['id']][0]['total_per_unit'];
                            $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] += $elementGrandTotal[$element['id']][0]['up_to_date_amount'];
                        }
                    }

                    $typeQuantityCounter ++;
                }
            }

            // assign element total for unit that haven't been instantiate yet.
            while ($typeQuantityCounter < $billColumnSetting['quantity'])
            {
                foreach ( $elements as $key => $element )
                {
                    if ( isset( $defaultElementsTotal[$element['id']] ) )
                    {
                        $elements[$key][$billColumnSetting['id'] . '-grand_total'] += $defaultElementsTotal[$element['id']][0]['total_per_unit'];
                    }
                }

                $typeQuantityCounter ++;
            }

            // calculate percentage
            foreach ( $elements as $elementKey => $element )
            {
                // by element's overall
                if ( $elements[$elementKey][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] > 0 )
                {
                    $elements[$elementKey][$billColumnSetting['id'] . '-type_total_percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$elementKey][$billColumnSetting['id'] . '-type_total_up_to_date_amount'], $elements[$elementKey][$billColumnSetting['id'] . '-grand_total']));
                }
            }

            unset( $billColumnSetting );
        }

        array_push($elements, array(
            'id'                    => Constants::GRID_LAST_ROW,
            'description'           => '',
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'claim_type_ref_id'     => - 1,
            'relation_id'           => $bill->id,
        ));

        $data['billColumns'] = $billColumns;

        $data['gridStructure'] = $gridStructure;

        $data['items'] = array(
            'identifier' => 'id',
            'items'      => $elements
        );

        return $this->renderJson($data);
    }

    public function executeGetPrintingPreviewDataBySelectedUnits(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        $typeIds            = json_decode($request->getParameter('itemIds'), true);
        $typeIdsFiltered    = array();
        $typeItems          = array();
        $billColumns        = array();
        $gridStructure      = array();
        $elementGrandTotals = array();
        $pdo                = $project->getTable()->getConnection()->getDbh();
        $revision           = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $elements           = $this->getAffectedElements($pdo, $subPackage, $bill);

        foreach ( $typeIds as $typeId )
        {
            $explodedTypeId = explode('-', $typeId);

            if ( count($explodedTypeId) > 1 )
            {
                $billColumnSettingId = $explodedTypeId[0];
                $count               = $explodedTypeId[1];

                if ( is_numeric($billColumnSettingId) AND is_numeric($count) )
                {
                    $typeIdsFiltered[] = array( $billColumnSettingId, $count );
                }
            }

            unset( $explodedTypeId );
        }

        if ( count($typeIdsFiltered) > 0 )
        {
            $dynamicWhere = array();

            foreach ( $typeIdsFiltered as $typeId )
            {
                $dynamicWhere[] = "(stype.bill_column_setting_id = {$typeId[0]} AND stype.counter = {$typeId[1]})";
            }

            $stmt = $pdo->prepare("SELECT DISTINCT type_ref.id, stype.bill_column_setting_id, cs.name as bill_column_name,
			type_ref.new_name, stype.sub_package_id, stype.counter, type_ref.post_contract_id
			FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
			JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " type_ref
			ON type_ref.bill_column_setting_id = stype.bill_column_setting_id AND type_ref.counter = stype.counter
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
			WHERE cs.project_structure_id = " . $bill->id . " AND stype.sub_package_id = " . $subPackage->id . "
			AND (" . implode(' OR ', $dynamicWhere) . ")
			ORDER BY stype.bill_column_setting_id, stype.counter ASC");

            $stmt->execute();

            $typeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            unset( $dynamicWhere );
        }

        foreach ( $typeItems as $typeItem )
        {
            $object                         = new PostContractStandardClaimTypeReference();
            $object->id                     = $typeItem['id'];
            $object->bill_column_setting_id = $typeItem['bill_column_setting_id'];
            $object->post_contract_id       = $typeItem['post_contract_id'];

            $elementGrandTotals[$typeItem['bill_column_setting_id']][$typeItem['id']] = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($bill->id, $object, $revision, $subPackage->id);

            $billColumns[$typeItem['bill_column_setting_id']] = array(
                'id'   => $typeItem['bill_column_setting_id'],
                'name' => $typeItem['bill_column_name'],
            );

            // to be use from the front-end to generate dynamic table columns
            $gridStructure[$typeItem['bill_column_setting_id']][] = array(
                'id'       => $typeItem['id'],
                'new_name' => ( $typeItem['new_name'] ) ? $typeItem['new_name'] : 'Unit ' . $typeItem['counter'],
            );

            unset( $object );
        }

        unset($typeItems);

        foreach ( $bill->BillColumnSettings as $billColumnSetting )
        {
            // assign default variable
            foreach ( $elements as $key => $element )
            {
                $elements[$key][$billColumnSetting['id'] . '-grand_total']                  = 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_percentage']        = 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] = 0;

                $elements[$key]['has_note'] = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            }

            // use PostContractStandardClaimTypeReference's if available
            if ( isset( $elementGrandTotals[$billColumnSetting['id']] ) )
            {
                foreach ( $elementGrandTotals[$billColumnSetting['id']] as $typeId => $elementGrandTotal )
                {
                    foreach ( $elements as $key => $element )
                    {
                        if ( isset( $elementGrandTotal[$element['id']] ) )
                        {
                            $elements[$key][$typeId . '-unit_total_percentage'] = $elementGrandTotal[$element['id']][0]['up_to_date_percentage'];
                            $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] += $elementGrandTotal[$element['id']][0]['up_to_date_amount'];
                        }
                    }
                }
            }

            unset( $billColumnSetting );
        }

        if($elements)
        {
            // Get Type List for all units so we can get contract amount
            $stmt = $pdo->prepare("SELECT type_ref.id, stype.bill_column_setting_id, cs.name as bill_column_name,
                type_ref.new_name, stype.sub_package_id, stype.counter, type_ref.post_contract_id
                FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
                JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " type_ref
                ON type_ref.bill_column_setting_id = stype.bill_column_setting_id AND type_ref.counter = stype.counter
                JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
                WHERE cs.project_structure_id = " . $bill->id . " AND stype.sub_package_id = " . $subPackage->id . "
                ORDER BY stype.bill_column_setting_id, stype.counter ASC");

            $stmt->execute();

            $typeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $elementGrandTotals = array();

            foreach ( $typeItems as $typeItem )
            {
                $obj                         = new PostContractStandardClaimTypeReference();
                $obj->id                     = $typeItem['id'];
                $obj->bill_column_setting_id = $typeItem['bill_column_setting_id'];
                $obj->post_contract_id       = $typeItem['post_contract_id'];

                $elementGrandTotals[$typeItem['bill_column_setting_id']][$typeItem['id']] = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($bill->id, $obj, $revision, $subPackage->id);
            }

            foreach ( $bill->BillColumnSettings as $billColumnSetting )
            {
                // use PostContractStandardClaimTypeReference's if available
                if ( isset( $elementGrandTotals[$billColumnSetting['id']] ) )
                {
                    foreach ( $elementGrandTotals[$billColumnSetting['id']] as $elementGrandTotal )
                    {
                        foreach ( $elements as $key => $element )
                        {
                            if ( isset( $elementGrandTotal[$element['id']] ) )
                            {
                                $elements[$key][$billColumnSetting['id'] . '-grand_total'] += $elementGrandTotal[$element['id']][0]['total_per_unit'];
                            }
                        }
                    }
                }

                // calculate percentage
                foreach ( $elements as $elementKey => $element )
                {
                    // by element's overall
                    if ( $elements[$elementKey][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] > 0 )
                    {
                        $elements[$elementKey][$billColumnSetting['id'] . '-type_total_percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$elementKey][$billColumnSetting['id'] . '-type_total_up_to_date_amount'], $elements[$elementKey][$billColumnSetting['id'] . '-grand_total']));
                    }
                }

                unset( $billColumnSetting );
            }
        }

        array_push($elements, array(
            'id'                    => Constants::GRID_LAST_ROW,
            'description'           => '',
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'claim_type_ref_id'     => - 1,
            'relation_id'           => $bill->id,
        ));

        $data['billColumns'] = $billColumns;

        $data['gridStructure'] = $gridStructure;

        $data['items'] = array(
            'identifier' => 'id',
            'items'      => $elements
        );

        return $this->renderJson($data);
    }

    public function executeGetPrintingSelectedElementClaims(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeItem = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        $pdo        = $subPackage->getTable()->getConnection()->getDbh();
        $revision   = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $elements   = array();
        $elementIds = json_decode($request->getParameter('itemIds'), true);

        if ( count($elementIds) > 0 )
        {
            $stmt = $pdo->prepare("SELECT e.id, e.description, e.note, COALESCE(SUM(rate.single_unit_grand_total), 0) AS overall_total_after_markup
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.id =  e.project_structure_id AND b.deleted_at IS NULL
			WHERE e.id IN (" . implode(', ', $elementIds) . ") AND b.id = " . $bill->id . "
			AND rate.sub_package_id = " . $subPackage->id . " GROUP BY e.id ORDER BY e.id ASC");

            $stmt->execute();
            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $elementGrandTotals = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($bill->id, $typeItem, $revision, $subPackage->id);

            foreach ( $elements as $key => $element )
            {
                $elementId = $element['id'];

                if ( array_key_exists($elementId, $elementGrandTotals) )
                {
                    $prevAmount     = $elementGrandTotals[$elementId][0]['prev_amount'];
                    $currentAmount  = $elementGrandTotals[$elementId][0]['current_amount'];
                    $totalPerUnit   = $elementGrandTotals[$elementId][0]['total_per_unit'];
                    $upToDateAmount = $elementGrandTotals[$elementId][0]['up_to_date_amount'];

                    $elements[$key]['total_per_unit']        = $totalPerUnit;
                    $elements[$key]['prev_percentage']       = ( $totalPerUnit > 0 ) ? number_format(( $prevAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                    $elements[$key]['prev_amount']           = $prevAmount;
                    $elements[$key]['current_percentage']    = ( $totalPerUnit > 0 ) ? number_format(( $currentAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                    $elements[$key]['current_amount']        = $currentAmount;
                    $elements[$key]['up_to_date_percentage'] = ( $totalPerUnit > 0 ) ? number_format(( $upToDateAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                    $elements[$key]['up_to_date_amount']     = $upToDateAmount;
                    $elements[$key]['up_to_date_qty']        = $elementGrandTotals[$elementId][0]['up_to_date_qty'];
                }
            }
        }

        $defaultLastRow = array(
            'id'                    => Constants::GRID_LAST_ROW,
            'description'           => '',
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
        );

        array_push($elements, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetPrintingElementWorkDoneOnlyClaims(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeItem = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        $pdo      = $subPackage->getTable()->getConnection()->getDbh();
        $items    = array();
        $revision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $elements = $this->getAffectedElements($pdo, $subPackage, $bill);

        $elementGrandTotals = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($bill->id, $typeItem, $revision, $subPackage->id);

        foreach ( $elements as $key => $element )
        {
            $elementId                               = $element['id'];
            $elements[$key]['up_to_date_percentage'] = 0;
            $elements[$key]['up_to_date_amount']     = 0;
            $elements[$key]['up_to_date_qty']        = 0;

            if ( array_key_exists($elementId, $elementGrandTotals) )
            {
                $totalPerUnit   = $elementGrandTotals[$elementId][0]['total_per_unit'];
                $upToDateAmount = $elementGrandTotals[$elementId][0]['up_to_date_amount'];

                $elements[$key]['total_per_unit']        = $totalPerUnit;
                $elements[$key]['up_to_date_percentage'] = ( $totalPerUnit > 0 ) ? number_format(( $upToDateAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                $elements[$key]['up_to_date_amount']     = $upToDateAmount;
                $elements[$key]['up_to_date_qty']        = $elementGrandTotals[$elementId][0]['up_to_date_qty'];
            }

            if ( $elements[$key]['up_to_date_amount'] > 0 )
            {
                $items[] = $elements[$key];
            }
        }

        unset( $elements );

        $defaultLastRow = array(
            'id'                    => Constants::GRID_LAST_ROW,
            'description'           => '',
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetPrintingSelectedItemClaims(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        $items            = array();
        $pageNoPrefix     = $bill->BillLayoutSetting->page_no_prefix;
        $revision         = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $itemIds          = $request->getParameter('itemIds');
        $affectedElements = SubPackageTable::getAffectedElementIdsByItemIds($itemIds, $subPackage, $bill);

        foreach ( $affectedElements as $elementId => $affectedElement )
        {
            $items[] = array(
                'id'          => 'element-' . $elementId,
                'bill_ref'    => null,
                'description' => $affectedElement['description'],
                'type'        => 0,
            );

            $element     = new BillElement();
            $element->id = $elementId;

            $billItems = SubPackageTable::getDataStructureForSubPackageStandardClaimBillItemListByItemIds($element, $revision, $subPackage->id, $typeRef, $itemIds);

            foreach ( $billItems as $billItem )
            {
                $billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
                $billItem['type']                      = (string) $billItem['type'];
                $billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
                $billItem['relation_id']               = $element->id;
                $billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
                $billItem['has_note']                  = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
                $billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

                unset( $billItem['has_build_up'] );

                array_push($items, $billItem);

                unset( $billItem );
            }

            unset( $billItems, $element );
        }

        $defaultLastRow = array(
            'id'                    => Constants::GRID_LAST_ROW,
            'bill_ref'              => '',
            'description'           => '',
            'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                => '-1',
            'uom_symbol'            => '',
            'level'                 => 0,
            'qty_per_unit'          => 0,
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'include'               => 1,
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetPrintingPreviewItemsWithCurrentClaim(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        $pdo              = $subPackage->getTable()->getConnection()->getDbh();
        $items            = array();
        $pageNoPrefix     = $bill->BillLayoutSetting->page_no_prefix;
        $revision         = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $affectedElements = $this->getAffectedElements($pdo, $subPackage, $bill);

        foreach ( $affectedElements as $affectedElement )
        {
            $addedTopElementHeader = false;
            $elementId             = $affectedElement['id'];

            $element     = new BillElement();
            $element->id = $elementId;

            $billItems = SubPackageTable::getDataForPrintingPreviewItemsByColumn($element, $revision, $subPackage, $typeRef, 'current_amount');

            foreach ( $billItems as $billItem )
            {
                if ( !$addedTopElementHeader )
                {
                    $items[] = array(
                        'id'          => 'element-' . $elementId,
                        'bill_ref'    => null,
                        'description' => $affectedElement['description'],
                        'type'        => 0,
                    );

                    $addedTopElementHeader = true;
                }

                $billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
                $billItem['type']                      = (string) $billItem['type'];
                $billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
                $billItem['relation_id']               = $element->id;
                $billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
                $billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

                unset( $billItem['has_build_up'] );

                array_push($items, $billItem);

                unset( $billItem );
            }

            unset( $billItems, $element );
        }

        $defaultLastRow = array(
            'id'                    => Constants::GRID_LAST_ROW,
            'bill_ref'              => '',
            'description'           => '',
            'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                => '-1',
            'uom_symbol'            => '',
            'level'                 => 0,
            'qty_per_unit'          => 0,
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'include'               => 1,
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetPrintingPreviewItemsWithClaim(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        $pdo              = $subPackage->getTable()->getConnection()->getDbh();
        $items            = array();
        $pageNoPrefix     = $bill->BillLayoutSetting->page_no_prefix;
        $revision         = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $affectedElements = $this->getAffectedElements($pdo, $subPackage, $bill);

        foreach ( $affectedElements as $affectedElement )
        {
            $addedTopElementHeader = false;
            $elementId             = $affectedElement['id'];

            $element     = new BillElement();
            $element->id = $elementId;

            $billItems = SubPackageTable::getDataForPrintingPreviewItemsByColumn($element, $revision, $subPackage, $typeRef, 'up_to_date_amount');

            foreach ( $billItems as $billItem )
            {
                if ( !$addedTopElementHeader )
                {
                    $items[] = array(
                        'id'          => 'element-' . $elementId,
                        'bill_ref'    => null,
                        'description' => $affectedElement['description'],
                        'type'        => 0,
                    );

                    $addedTopElementHeader = true;
                }

                $billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
                $billItem['type']                      = (string) $billItem['type'];
                $billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
                $billItem['relation_id']               = $element->id;
                $billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
                $billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

                unset( $billItem['has_build_up'] );

                array_push($items, $billItem);

                unset( $billItem );
            }

            unset( $billItems, $element );
        }

        $defaultLastRow = array(
            'id'                    => Constants::GRID_LAST_ROW,
            'bill_ref'              => '',
            'description'           => '',
            'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                => '-1',
            'uom_symbol'            => '',
            'level'                 => 0,
            'qty_per_unit'          => 0,
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'include'               => 1,
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetPrintingPreviewItemsWorkDoneWithQty(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        $pdo              = $subPackage->getTable()->getConnection()->getDbh();
        $items            = array();
        $pageNoPrefix     = $bill->BillLayoutSetting->page_no_prefix;
        $revision         = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $affectedElements = $this->getAffectedElements($pdo, $subPackage, $bill);

        foreach ( $affectedElements as $affectedElement )
        {
            $addedTopElementHeader = false;
            $elementId             = $affectedElement['id'];

            $element     = new BillElement();
            $element->id = $elementId;

            $billItems = SubPackageTable::getDataForPrintingPreviewItemsByColumn($element, $revision, $subPackage, $typeRef, 'up_to_date_amount');

            foreach ( $billItems as $billItem )
            {
                if ( !$addedTopElementHeader )
                {
                    $items[] = array(
                        'id'          => 'element-' . $elementId,
                        'bill_ref'    => null,
                        'description' => $affectedElement['description'],
                        'type'        => 0,
                    );

                    $addedTopElementHeader = true;
                }

                $billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
                $billItem['type']                      = (string) $billItem['type'];
                $billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
                $billItem['relation_id']               = $element->id;
                $billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
                $billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

                unset( $billItem['has_build_up'] );

                array_push($items, $billItem);

                unset( $billItem );
            }

            unset( $billItems, $element );
        }

        $defaultLastRow = array(
            'id'                    => Constants::GRID_LAST_ROW,
            'bill_ref'              => '',
            'description'           => '',
            'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                => '-1',
            'uom_symbol'            => '',
            'level'                 => 0,
            'qty_per_unit'          => 0,
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'include'               => 1,
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetPrintingPreviewItemsWorkDoneWithPercentage(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        $pdo              = $subPackage->getTable()->getConnection()->getDbh();
        $items            = array();
        $pageNoPrefix     = $bill->BillLayoutSetting->page_no_prefix;
        $revision         = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $affectedElements = $this->getAffectedElements($pdo, $subPackage, $bill);

        foreach ( $affectedElements as $affectedElement )
        {
            $addedTopElementHeader = false;
            $elementId             = $affectedElement['id'];

            $element     = new BillElement();
            $element->id = $elementId;

            $billItems = SubPackageTable::getDataForPrintingPreviewItemsByColumn($element, $revision, $subPackage, $typeRef, 'up_to_date_amount');

            foreach ( $billItems as $billItem )
            {
                if ( !$addedTopElementHeader )
                {
                    $items[] = array(
                        'id'          => 'element-' . $elementId,
                        'bill_ref'    => null,
                        'description' => $affectedElement['description'],
                        'type'        => 0,
                    );

                    $addedTopElementHeader = true;
                }

                $billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
                $billItem['type']                      = (string) $billItem['type'];
                $billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
                $billItem['relation_id']               = $element->id;
                $billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
                $billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

                unset( $billItem['has_build_up'] );

                array_push($items, $billItem);

                unset( $billItem );
            }

            unset( $billItems, $element );
        }

        $defaultLastRow = array(
            'id'                    => Constants::GRID_LAST_ROW,
            'bill_ref'              => '',
            'description'           => '',
            'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                => '-1',
            'uom_symbol'            => '',
            'level'                 => 0,
            'qty_per_unit'          => 0,
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'include'               => 1,
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    // =======================================================================================================================================
    // Print Report
    // =======================================================================================================================================
    public function executePrintElementByTypes(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');

        $priceFormat  = $request->getParameter('priceFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageElementByTypeGenerator($subPackage, $bill, $printingPageTitle, $descriptionFormat);
        $pages                = $reportPrintGenerator->generatePages();
        $maxRows              = $reportPrintGenerator->getMaxRows();
        $currency             = $reportPrintGenerator->getCurrency();
        $withoutPrice         = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportPrintGenerator->getMarginTop(),
            'margin-right'   => $reportPrintGenerator->getMarginRight(),
            'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
            'margin-left'    => $reportPrintGenerator->getMarginLeft(),
            'page-size'      => $reportPrintGenerator->getPageSize(),
            'orientation'    => $reportPrintGenerator->getOrientation()
        );

        $stylesheet = $this->getBQStyling();

        $pdfGen    = new WkHtmlToPdf($params);
        $totalPage = count($pages) - 1;
        $pageCount = 1;

        if ( $pages instanceof SplFixedArray )
        {
            $printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

            foreach ( $pages as $page )
            {
                if ( empty( $page ) )
                {
                    continue;
                }

                $layout = $this->getPartial('printReport/pageLayout', array(
                    'stylesheet'    => $stylesheet,
                    'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                ));

                $billItemsLayoutParams = array(
                    'itemPage'                   => $page,
                    'maxRows'                    => $maxRows + 2,
                    'currency'                   => $currency,
                    'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
                    'pageCount'                  => $pageCount,
                    'totalPage'                  => $totalPage,
                    'printGrandTotal'            => $printGrandTotal,
                    'typeTotals'                 => $reportPrintGenerator->typeTotals,
                    'withUnit'                   => false,
                    'elementTypeTotals'          => $reportPrintGenerator->elementTotals,
                    'billColumnSettings'         => $bill->BillColumnSettings->toArray(),
                    'reportTitle'                => $printingPageTitle,
                    'topLeftRow1'                => $project->title,
                    'topLeftRow2'                => $bill->title,
                    'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                    'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
                    'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
                    'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
                    'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
                    'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
                    'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
                    'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                    'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                    'printNoPrice'               => $withoutPrice,
                    'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
                    'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
                    'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
                    'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                    'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                    'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
                    'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                    'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
                    'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                    'indentItem'                 => $reportPrintGenerator->getIndentItem(),
                    'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                    'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                    'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
                    'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                );

                $layout .= $this->getPartial('printReport/postContractStandardClaimReportElementTypes', $billItemsLayoutParams);

                unset( $page );

                $pdfGen->addPage($layout);

                $pageCount ++;
            }
        }

        return $pdfGen->send();
    }

    public function executePrintElementWithClaimByTypes(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');

        $priceFormat  = $request->getParameter('priceFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageElementByTypeWithClaimGenerator($subPackage, $bill, $printingPageTitle, $descriptionFormat);
        $pages                = $reportPrintGenerator->generatePages();
        $maxRows              = $reportPrintGenerator->getMaxRows();
        $currency             = $reportPrintGenerator->getCurrency();
        $withoutPrice         = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportPrintGenerator->getMarginTop(),
            'margin-right'   => $reportPrintGenerator->getMarginRight(),
            'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
            'margin-left'    => $reportPrintGenerator->getMarginLeft(),
            'page-size'      => $reportPrintGenerator->getPageSize(),
            'orientation'    => $reportPrintGenerator->getOrientation()
        );

        $stylesheet = $this->getBQStyling();

        $pdfGen = new WkHtmlToPdf($params);

        $totalPage = count($pages) - 1;

        $pageCount = 1;

        if ( $pages instanceof SplFixedArray )
        {
            $printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

            foreach ( $pages as $page )
            {
                if ( count($page) )
                {
                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page,
                        'maxRows'                    => $maxRows + 2,
                        'currency'                   => $currency,
                        'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $totalPage,
                        'printGrandTotal'            => $printGrandTotal,
                        'typeTotals'                 => $reportPrintGenerator->typeTotals,
                        'withUnit'                   => true,
                        'unitNames'                  => $reportPrintGenerator->unitNames,
                        'elementTypeTotals'          => $reportPrintGenerator->elementTotals,
                        'billColumnSettings'         => $bill->BillColumnSettings->toArray(),
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => $project->title,
                        'topLeftRow2'                => $bill->title,
                        'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportPrintGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('printReport/postContractStandardClaimReportElementTypesSelectedUnit', $billItemsLayoutParams);

                    unset( $page );

                    $pdfGen->addPage($layout);

                    $pageCount ++;
                }
            }
        }

        return $pdfGen->send();
    }

    public function executePrintElementWithClaimByTypesBySelectedUnits(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $typeIds           = json_decode($request->getParameter('selectedRows'), true);

        $priceFormat  = $request->getParameter('priceFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageElementByTypeSelectedUnits($subPackage, $bill, $printingPageTitle, $descriptionFormat);

        $pages        = $reportPrintGenerator->generatePages($typeIds);
        $maxRows      = $reportPrintGenerator->getMaxRows();
        $currency     = $reportPrintGenerator->getCurrency();
        $withoutPrice = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportPrintGenerator->getMarginTop(),
            'margin-right'   => $reportPrintGenerator->getMarginRight(),
            'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
            'margin-left'    => $reportPrintGenerator->getMarginLeft(),
            'page-size'      => $reportPrintGenerator->getPageSize(),
            'orientation'    => $reportPrintGenerator->getOrientation()
        );

        $stylesheet = $this->getBQStyling();

        $pdfGen = new WkHtmlToPdf($params);

        $totalPage = count($pages) - 1;

        $pageCount = 1;

        if ( $pages instanceof SplFixedArray )
        {
            foreach ( $pages as $page )
            {
                $printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

                if ( count($page) )
                {
                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page,
                        'maxRows'                    => $maxRows + 2,
                        'currency'                   => $currency,
                        'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $totalPage,
                        'printGrandTotal'            => $printGrandTotal,
                        'typeTotals'                 => $reportPrintGenerator->typeTotals,
                        'withUnit'                   => true,
                        'unitNames'                  => $reportPrintGenerator->unitNames,
                        'elementTypeTotals'          => $reportPrintGenerator->elementTotals,
                        'billColumnSettings'         => $bill->BillColumnSettings->toArray(),
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => $project->title,
                        'topLeftRow2'                => $bill->title,
                        'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportPrintGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('printReport/postContractStandardClaimReportElementTypesSelectedUnit', $billItemsLayoutParams);

                    unset( $page );

                    $pdfGen->addPage($layout);

                    $pageCount ++;
                }
            }
        }

        return $pdfGen->send();
    }

    public function executePrintStandardBillSelectedElement(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $elementIds        = json_decode($request->getParameter('selectedRows'), true);

        $priceFormat  = $request->getParameter('priceFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $reportPrintGenerator = new sfBuildspacePostContractSubPackageStandardBillReportPageElementGenerator($subPackage, $bill, $elementIds, $printingPageTitle, $descriptionFormat);
        $pages                = $reportPrintGenerator->generatePages($typeRef);
        $maxRows              = $reportPrintGenerator->getMaxRows();
        $currency             = $reportPrintGenerator->getCurrency();
        $withoutPrice         = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportPrintGenerator->getMarginTop(),
            'margin-right'   => $reportPrintGenerator->getMarginRight(),
            'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
            'margin-left'    => $reportPrintGenerator->getMarginLeft(),
            'page-size'      => $reportPrintGenerator->getPageSize(),
            'orientation'    => $reportPrintGenerator->getOrientation()
        );

        $stylesheet = $this->getBQStyling();

        $pdfGen = new WkHtmlToPdf($params);

        $totalPage = count($pages) - 1;

        $pageCount = 1;

        $typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

        if ( $pages instanceof SplFixedArray )
        {
            foreach ( $pages as $page )
            {
                $printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

                if ( count($page) )
                {
                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page,
                        'maxRows'                    => $maxRows + 2,
                        'currency'                   => $currency,
                        'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $totalPage,
                        'printGrandTotal'            => $printGrandTotal,
                        'typeTotals'                 => $reportPrintGenerator->typeTotals,
                        'workdoneOnly'               => false,
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => $project->title,
                        'topLeftRow2'                => $bill->title . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle,
                        'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportPrintGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('printReport/postContractStandardClaimReportElement', $billItemsLayoutParams);

                    unset( $page );

                    $pdfGen->addPage($layout);

                    $pageCount ++;
                }
            }
        }

        return $pdfGen->send();
    }

    public function executePrintStandardBillElementWorkDoneOnly(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $pdo               = $bill->getTable()->getConnection()->getDbh();
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $priceFormat       = $request->getParameter('priceFormat');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
        $elementIds        = array();
        $elements          = $this->getAffectedElements($pdo, $subPackage, $bill);

        foreach ( $elements as $element )
        {
            $elementIds[] = $element['id'];
        }

        $reportPrintGenerator = new sfBuildspacePostContractSubPackageStandardBillReportPageElementGenerator($subPackage, $bill, $elementIds, $printingPageTitle, $descriptionFormat);

        $pages        = $reportPrintGenerator->generatePages($typeRef);
        $maxRows      = $reportPrintGenerator->getMaxRows();
        $currency     = $reportPrintGenerator->getCurrency();
        $withoutPrice = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportPrintGenerator->getMarginTop(),
            'margin-right'   => $reportPrintGenerator->getMarginRight(),
            'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
            'margin-left'    => $reportPrintGenerator->getMarginLeft(),
            'page-size'      => $reportPrintGenerator->getPageSize(),
            'orientation'    => $reportPrintGenerator->getOrientation()
        );

        $stylesheet = $this->getBQStyling();

        $pdfGen = new WkHtmlToPdf($params);

        $totalPage = count($pages) - 1;

        $pageCount = 1;

        $typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

        if ( $pages instanceof SplFixedArray )
        {
            foreach ( $pages as $page )
            {
                $printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

                if ( count($page) )
                {
                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page,
                        'maxRows'                    => $maxRows,
                        'currency'                   => $currency,
                        'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $totalPage,
                        'printGrandTotal'            => $printGrandTotal,
                        'typeTotals'                 => $reportPrintGenerator->typeTotals,
                        'workdoneOnly'               => true,
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => $project->title,
                        'topLeftRow2'                => $bill->title . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle,
                        'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportPrintGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('printReport/postContractStandardClaimReportElement', $billItemsLayoutParams);

                    unset( $page );

                    $pdfGen->addPage($layout);

                    $pageCount ++;
                }
            }
        }

        return $pdfGen->send();
    }

    public function executePrintStandardBillSelectedItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $itemIds           = json_decode($request->getParameter('selectedRows'), true);
        $priceFormat       = $request->getParameter('priceFormat');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
        $affectedElements  = SubPackageTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows'), $subPackage, $bill);

        $reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageItemGenerator($subPackage, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat);
        $elementPages         = $reportPrintGenerator->generatePages($typeRef);
        $maxRows              = $reportPrintGenerator->getMaxRows();
        $currency             = $reportPrintGenerator->getCurrency();
        $withoutPrice         = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportPrintGenerator->getMarginTop(),
            'margin-right'   => $reportPrintGenerator->getMarginRight(),
            'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
            'margin-left'    => $reportPrintGenerator->getMarginLeft(),
            'page-size'      => $reportPrintGenerator->getPageSize(),
            'orientation'    => $reportPrintGenerator->getOrientation()
        );

        $stylesheet = $this->getBQStyling();

        $pdfGen = new WkHtmlToPdf($params);

        $pageCount = 1;

        $typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

        foreach ( $elementPages as $elementId => $pages )
        {
            for ( $i = 1; $i <= $pages['item_pages']->count(); $i ++ )
            {
                if ( $pages['item_pages'] instanceof SplFixedArray and $pages['item_pages']->offsetExists($i) )
                {
                    $printGrandTotal = ( ( $i + 1 ) == $pages['item_pages']->count() ) ? true : false;

                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $pages['item_pages']->offsetGet($i),
                        'maxRows'                    => $maxRows + 2,
                        'currency'                   => $currency,
                        'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $reportPrintGenerator->totalPage,
                        'elementTotals'              => ( array_key_exists($elementId, $reportPrintGenerator->elementTotals) ) ? $reportPrintGenerator->elementTotals[$elementId] : array(),
                        'printGrandTotal'            => $printGrandTotal,
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
                        'topLeftRow2'                => $bill->title . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle,
                        'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportPrintGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('printReport/postContractStandardClaimReportItem', $billItemsLayoutParams);

                    $pages['item_pages']->offsetUnset($i);

                    $pdfGen->addPage($layout);

                    $pageCount ++;
                }
            }
        }

        return $pdfGen->send();
    }

    public function executePrintStandardBillItemWithCurrentClaim(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $pdo               = $bill->getTable()->getConnection()->getDbh();
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $priceFormat       = $request->getParameter('priceFormat');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
        $affectedElements  = $this->getAffectedElements($pdo, $subPackage, $bill);

        $reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageItemWithCurrentClaimGenerator($subPackage, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);
        $pages                = $reportPrintGenerator->generatePages($typeRef);
        $maxRows              = $reportPrintGenerator->getMaxRows();
        $currency             = $reportPrintGenerator->getCurrency();
        $withoutPrice         = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportPrintGenerator->getMarginTop(),
            'margin-right'   => $reportPrintGenerator->getMarginRight(),
            'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
            'margin-left'    => $reportPrintGenerator->getMarginLeft(),
            'page-size'      => $reportPrintGenerator->getPageSize(),
            'orientation'    => $reportPrintGenerator->getOrientation()
        );

        $stylesheet = $this->getBQStyling();

        $pdfGen = new WkHtmlToPdf($params);

        $pageCount = 1;

        $typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

        foreach ( $pages as $key => $page )
        {
            for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
            {
                if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;

                    $layout = $this->getPartial('printReport/pageLayout', array(
                            'stylesheet'    => $stylesheet,
                            'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                        )
                    );

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'maxRows'                    => $maxRows,
                        'currency'                   => $currency,
                        'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $reportPrintGenerator->totalPage,
                        'printGrandTotal'            => $printGrandTotal,
                        'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
                        'topLeftRow2'                => $bill->title . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle,
                        'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportPrintGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('printReport/postContractStandardClaimReportItem', $billItemsLayoutParams);

                    $page['item_pages']->offsetUnset($i);

                    $pdfGen->addPage($layout);

                    $pageCount ++;
                }
            }
        }

        return $pdfGen->send();
    }

    public function executePrintStandardBillItemWithClaim(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $pdo               = $bill->getTable()->getConnection()->getDbh();
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $priceFormat       = $request->getParameter('priceFormat');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
        $affectedElements  = $this->getAffectedElements($pdo, $subPackage, $bill);

        $reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageItemWithClaimGenerator($subPackage, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);
        $pages                = $reportPrintGenerator->generatePages($typeRef);
        $maxRows              = $reportPrintGenerator->getMaxRows();
        $currency             = $reportPrintGenerator->getCurrency();
        $withoutPrice         = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportPrintGenerator->getMarginTop(),
            'margin-right'   => $reportPrintGenerator->getMarginRight(),
            'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
            'margin-left'    => $reportPrintGenerator->getMarginLeft(),
            'page-size'      => $reportPrintGenerator->getPageSize(),
            'orientation'    => $reportPrintGenerator->getOrientation()
        );

        $stylesheet = $this->getBQStyling();

        $pdfGen = new WkHtmlToPdf($params);

        $pageCount = 1;

        $typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

        foreach ( $pages as $key => $page )
        {
            for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
            {
                if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;

                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'maxRows'                    => $maxRows,
                        'currency'                   => $currency,
                        'printGrandTotal'            => $printGrandTotal,
                        'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $reportPrintGenerator->totalPage,
                        'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
                        'topLeftRow2'                => $bill->title . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle,
                        'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportPrintGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('printReport/postContractStandardClaimReportItem', $billItemsLayoutParams);

                    $page['item_pages']->offsetUnset($i);

                    $pdfGen->addPage($layout);

                    $pageCount ++;
                }
            }
        }

        return $pdfGen->send();
    }

    public function executePrintStandardBillItemWorkdoneOnlyWithQty(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $pdo               = $bill->getTable()->getConnection()->getDbh();
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $priceFormat       = $request->getParameter('priceFormat');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
        $affectedElements  = $this->getAffectedElements($pdo, $subPackage, $bill);

        $reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageItemWithClaimGenerator($subPackage, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);
        $pages                = $reportPrintGenerator->generatePages($typeRef);
        $maxRows              = $reportPrintGenerator->getMaxRows();
        $currency             = $reportPrintGenerator->getCurrency();
        $withoutPrice         = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportPrintGenerator->getMarginTop(),
            'margin-right'   => $reportPrintGenerator->getMarginRight(),
            'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
            'margin-left'    => $reportPrintGenerator->getMarginLeft(),
            'page-size'      => $reportPrintGenerator->getPageSize(),
            'orientation'    => $reportPrintGenerator->getOrientation()
        );

        $stylesheet = $this->getBQStyling();

        $pdfGen = new WkHtmlToPdf($params);

        $pageCount = 1;

        $typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;


        foreach ( $pages as $key => $page )
        {
            for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
            {
                if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;

                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'maxRows'                    => $maxRows,
                        'currency'                   => $currency,
                        'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $reportPrintGenerator->totalPage,
                        'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
                        'printGrandTotal'            => $printGrandTotal,
                        'printQty'                   => true,
                        'printPercentage'            => false,
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
                        'topLeftRow2'                => $bill->title . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle,
                        'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportPrintGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('printReport/postContractStandardClaimReportItemWorkdoneOnly', $billItemsLayoutParams);

                    $page['item_pages']->offsetUnset($i);

                    $pdfGen->addPage($layout);

                    $pageCount ++;
                }
            }
        }

        return $pdfGen->send();
    }

    public function executePrintStandardBillItemWorkdoneOnlyWithPercentage(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $pdo               = $bill->getTable()->getConnection()->getDbh();
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $priceFormat       = $request->getParameter('priceFormat');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
        $affectedElements  = $this->getAffectedElements($pdo, $subPackage, $bill);

        $reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageItemWithClaimGenerator($subPackage, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);
        $pages                = $reportPrintGenerator->generatePages($typeRef);
        $maxRows              = $reportPrintGenerator->getMaxRows();
        $currency             = $reportPrintGenerator->getCurrency();
        $withoutPrice         = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportPrintGenerator->getMarginTop(),
            'margin-right'   => $reportPrintGenerator->getMarginRight(),
            'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
            'margin-left'    => $reportPrintGenerator->getMarginLeft(),
            'page-size'      => $reportPrintGenerator->getPageSize(),
            'orientation'    => $reportPrintGenerator->getOrientation()
        );

        $stylesheet = $this->getBQStyling();

        $pdfGen = new WkHtmlToPdf($params);

        $pageCount = 1;

        $typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

        foreach ( $pages as $key => $page )
        {
            for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
            {
                if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;

                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'maxRows'                    => $maxRows,
                        'currency'                   => $currency,
                        'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $reportPrintGenerator->totalPage,
                        'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
                        'printGrandTotal'            => $printGrandTotal,
                        'printQty'                   => false,
                        'printPercentage'            => true,
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
                        'topLeftRow2'                => $bill->title . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle,
                        'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportPrintGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('printReport/postContractStandardClaimReportItemWorkdoneOnly', $billItemsLayoutParams);

                    $page['item_pages']->offsetUnset($i);

                    $pdfGen->addPage($layout);

                    $pageCount ++;
                }
            }
        }

        return $pdfGen->send();
    }

    private function getAffectedElements(PDO $pdo, SubPackage $subPackage, ProjectStructure $bill)
    {
        $stmt = $pdo->prepare("SELECT e.id, e.description, e.note
		FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
		JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
		JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
		JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.id =  e.project_structure_id AND b.deleted_at IS NULL
		WHERE b.id = " . $bill->id . " AND rate.sub_package_id = " . $subPackage->id . " GROUP BY e.id ORDER BY e.priority ASC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // =======================================================================================================================================

}