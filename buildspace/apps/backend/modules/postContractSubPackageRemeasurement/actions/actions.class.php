<?php

/**
 * postContractSubPackageRemeasurement actions.
 *
 * @package    buildspace
 * @subpackage postContractSubPackageRemeasurement
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractSubPackageRemeasurementActions extends BaseActions {

    public function executeGetAllBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
        );

        $pdo                           = $subPackage->getTable()->getConnection()->getDbh();
        $billColumnSettingIds          = array();
        $billColumnSettingIdQuantities = array();
        $billColumnSettings            = array();
        $billIds                       = array();
        $records                       = array();
        $filterByQuery                 = null;

        // get current project filtering mode
        $filterBy = $request->getParameter('opt');

        if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
        {
            $filterByQuery = "AND (t.type = " . BillType::TYPE_PROVISIONAL . " OR i.type = " . BillItem::TYPE_ITEM_PROVISIONAL . ")";
        }

        $stmt = $pdo->prepare("SELECT s.id, s.title, s.type, s.level, t.type AS bill_type, t.status AS bill_status,
        bls.id AS layout_id, COALESCE(SUM(rate.single_unit_grand_total), 0) AS overall_total_after_markup
        FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
        JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
        JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
        WHERE rate.sub_package_id = " . $subPackage->id . " {$filterByQuery} GROUP BY s.id, s.title, s.type, s.level, t.type,
        t.status, bls.id ORDER BY s.id ASC");

        $stmt->execute();
        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ( !empty( $bills ) )
        {
            // get bill column setting(s)
            foreach ( $bills as $bill )
            {
                $billIds[$bill['id']] = $bill['id'];
            }

            // get affected bill column setting based on sub package unit(s) selection
            $stmt = $pdo->prepare("SELECT r.bill_column_setting_id, COALESCE(COUNT(r.id), 0) FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r
            WHERE r.sub_package_id = " . $subPackage->id . " GROUP BY r.bill_column_setting_id");

            $stmt->execute();
            $subPackageTypeReferences = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

            foreach ( $subPackageTypeReferences as $billColumnSettingId => $units )
            {
                // if count is more than 0 then only include
                $billColumnSettingIds[$billColumnSettingId]          = $billColumnSettingId;
                $billColumnSettingIdQuantities[$billColumnSettingId] = $units[0];

                unset( $billColumnSettingId, $units );
            }

            unset( $subPackageTypeReferences );
        }

        // get affected bill column setting based on sub package unit(s) selection
        if ( count($billColumnSettingIds) > 0 )
        {
            $billColumnSettings = DoctrineQuery::create()
                ->from('BillColumnSetting bs')
                ->whereIn('bs.project_structure_id', $billIds)
                ->andWhereIn('bs.id', $billColumnSettingIds)
                ->fetchArray();
        }

        foreach ( $bills as $key => $record )
        {
            $billColumnSettingsIds = array();
            $billFilterBy          = $filterBy;

            if ( $record['bill_type'] == BillType::TYPE_PROVISIONAL )
            {
                $billFilterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
            }

            $billOmission = 0;
            $billAddition = 0;

            foreach ( $billColumnSettings as $billColumnSetting )
            {
                $billColumnSettingsIds[$billColumnSetting['id']] = $billColumnSetting['id'];
            }

            $remeasurementClaims = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByBill($record['id'], $subPackage, $billColumnSettingsIds, $billFilterBy);

            foreach ( $remeasurementClaims as $billColumnSettingKey => $remeasurementClaim )
            {
                $omission = 0;
                $addition = 0;
                $quantity = isset( $billColumnSettingIdQuantities[$billColumnSettingKey] ) ? $billColumnSettingIdQuantities[$billColumnSettingKey] : 0;

                $omission += $remeasurementClaim[0]['omission'];
                $addition += $remeasurementClaim[0]['addition'];

                $billAddition += $addition * $quantity;
                $billOmission += $omission * $quantity;
            }

            $bills[$key]['omission']             = $billOmission;
            $bills[$key]['addition']             = $billAddition;
            $bills[$key]['nettAdditionOmission'] = $billAddition - $billOmission;

            if ( count($remeasurementClaims) > 0 )
            {
                array_push($records, $bills[$key]);
            }

            unset( $remeasurementClaims, $record, $bills['layout_id'], $bills['quantity'] );
        }

        unset( $bills );

        array_push($records, array(
            'id'    => Constants::GRID_LAST_ROW,
            'title' => '',
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetBillTypes(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
        );

        $pdo                           = $subPackage->getTable()->getConnection()->getDbh();
        $filterBy                      = $request->getParameter('opt');
        $billColumnSettingIds          = array();
        $billColumnSettingIdQuantities = array();
        $records                       = array();
        $filterByQuery                 = null;

        // if current bill types is standard but provisional then list all the items associated with it
        if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
        {
            $filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
        }

        if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
        {
            $filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
        }

        // get affected bill column setting based on sub package unit(s) selection
        $stmt = $pdo->prepare("SELECT r.bill_column_setting_id, COALESCE(COUNT(r.id), 0) FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r
        WHERE r.sub_package_id = " . $subPackage->id . " GROUP BY r.bill_column_setting_id");

        $stmt->execute();
        $subPackageTypeReferences = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        foreach ( $subPackageTypeReferences as $billColumnSettingId => $units )
        {
            // if count is more than 0 then only include
            $billColumnSettingIds[$billColumnSettingId]          = $billColumnSettingId;
            $billColumnSettingIdQuantities[$billColumnSettingId] = $units[0];

            unset( $billColumnSettingId, $units );
        }

        unset( $subPackageTypeReferences );

        if ( count($billColumnSettingIds) > 0 )
        {
            $stmt = $pdo->prepare("SELECT DISTINCT bcs.id, bcs.name, bcs.quantity
            FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
            JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
            JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
            JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
            WHERE s.id = " . $bill->id . " AND bcs.id IN (" . implode(',', $billColumnSettingIds) . ")
            AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
            ORDER BY bcs.id ASC");

            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach ( $records as $key => $billType )
        {
            $quantity = isset( $billColumnSettingIdQuantities[$billType['id']] ) ? $billColumnSettingIdQuantities[$billType['id']] : 0;
            $omission = 0;
            $addition = 0;

            $billColumnSetting     = new BillColumnSetting();
            $billColumnSetting->id = $billType['id'];

            $remeasurementClaims = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByElement($bill, $subPackage, $billColumnSetting, $filterBy);

            foreach ( $remeasurementClaims as $remeasurementClaim )
            {
                $omission += $remeasurementClaim[0]['omission'];
                $addition += $remeasurementClaim[0]['addition'];
            }

            $records[$key]['omission']             = $omission * $quantity;
            $records[$key]['addition']             = $addition * $quantity;
            $records[$key]['nettAdditionOmission'] = $records[$key]['addition'] - $records[$key]['omission'];
            $records[$key]['quantity']             = $quantity;

            unset( $billColumnSetting, $billType );
        }

        $defaultLastRow = array(
            'id'   => Constants::GRID_LAST_ROW,
            'name' => '',
        );

        array_push($records, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
            $billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('btId')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($billType->project_structure_id) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
        );

        $pdo           = $subPackage->getTable()->getConnection()->getDbh();
        $filterByQuery = null;
        $filterBy      = $request->getParameter('opt');

        // if current bill types is standard but provisional then list all the items associated with it
        if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
        {
            $filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
        }

        if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
        {
            $filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
        }

        $stmt = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.note, e.priority
        FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
        JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
        JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
        JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
        WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . "
        AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
        ORDER BY e.priority ASC");

        $stmt->execute();
        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // get element remeasurement's claim costing
        $elementTotalRates = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByElement($bill, $subPackage, $billType, $filterBy);

        foreach ( $elements as $key => $element )
        {
            $omission = 0;
            $addition = 0;

            if ( array_key_exists($element['id'], $elementTotalRates) )
            {
                $omission = $elementTotalRates[$element['id']][0]['omission'];
                $addition = $elementTotalRates[$element['id']][0]['addition'];
            }

            $elements[$key]['omission']             = $omission;
            $elements[$key]['addition']             = $addition;
            $elements[$key]['nettAdditionOmission'] = $elements[$key]['addition'] - $elements[$key]['omission'];
            $elements[$key]['has_note']             = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            $elements[$key]['note']                 = (string) $element['note'];
            $elements[$key]['relation_id']          = $bill->id;
        }

        unset( $elementTotalRates );

        $defaultLastRow = array(
            'id'                         => Constants::GRID_LAST_ROW,
            'description'                => '',
            'has_note'                   => false,
            'grand_total'                => 0,
            'original_grand_total'       => 0,
            'overall_total_after_markup' => 0,
            'element_sum_total'          => 0,
            'relation_id'                => $bill->id,
        );

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
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('elementId')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
            $billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('btId')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($billType->project_structure_id) and
            $postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
        );

        $form         = new BaseForm();
        $items        = array();
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
        $filterBy     = $request->getParameter('opt');

        if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
        {
            $filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
        }

        list(
            $billItems, $remeasurementClaims, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
            ) = PostContractBillItemRateTable::getSubPackageRemeasurementItemList($postContract, $subPackage, $billType, $element, $filterBy);

        foreach ( $billItems as $billItem )
        {
            $itemTotal                           = $billItem['rate'] * $billItem['qty_per_unit'];
            $billItem['bill_ref']                = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
            $billItem['type']                    = (string) $billItem['type'];
            $billItem['uom_id']                  = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
            $billItem['relation_id']             = $element->id;
            $billItem['linked']                  = false;
            $billItem['_csrf_token']             = $form->getCSRFToken();
            $billItem['has_note']                = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
            $billItem['item_total']              = Utilities::prelimRounding($itemTotal);
            $billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
            $billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
            $billItem['omission-has_build_up']   = false;
            $billItem['addition-qty_per_unit']   = 0;
            $billItem['addition-total_per_unit'] = 0;
            $billItem['addition-has_build_up']   = false;

            if ( isset( $remeasurementClaims[$billItem['sub_package_post_contract_bill_item_rate_id']] ) )
            {
                $costing = $remeasurementClaims[$billItem['sub_package_post_contract_bill_item_rate_id']];

                $billItem['addition-qty_per_unit']   = Utilities::prelimRounding($costing['qty_per_unit']);
                $billItem['addition-total_per_unit'] = Utilities::prelimRounding($costing['total_per_unit']);
                $billItem['addition-has_build_up']   = $costing['has_build_up'];

                unset( $costing );
            }

            $billItem['nett_addition_omission'] = Utilities::prelimRounding($billItem['addition-total_per_unit'] - $billItem['omission-total_per_unit']);

            if ( array_key_exists($billType->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$billType->id]) )
            {
                $billItemTypeRef = $billItemTypeReferences[$billType->id][$billItem['id']];

                unset( $billItemTypeReferences[$billType->id][$billItem['id']] );

                if ( isset( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] ) )
                {
                    foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
                    {
                        $billItem['omission-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];

                        unset( $billItemTypeRefFormulatedColumn );
                    }
                }
            }

            array_push($items, $billItem);
            unset( $billItem );
        }

        unset( $billItems, $remeasurementClaims );

        $defaultLastRow = array(
            'id'                       => Constants::GRID_LAST_ROW,
            'bill_ref'                 => '',
            'description'              => '',
            'note'                     => '',
            'has_note'                 => false,
            'type'                     => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                   => '-1',
            'uom_symbol'               => '',
            'rate'                     => 0,
            'omission-qty_per_unit'    => 0,
            'omission-total_per_unit'  => 0,
            'addition-qty_per_unit'    => 0,
            'addition-total_per_unit'  => 0,
            'nett_addition_omission'   => 0,
            'relation_id'              => $element->id,
            'level'                    => 0,
            'linked'                   => false,
            'rate_after_markup'        => 0,
            'grand_total_after_markup' => 0,
            '_csrf_token'              => $form->getCSRFToken()
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeRemeasurementItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
            $billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('btId')) and
            $postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $billType->ProjectStructure->root_id) and
            $postContractSubPackageItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('id'))
        );

        $attrName = $request->getParameter('attr_name');
        $value    = is_numeric($request->getParameter('val')) ? $request->getParameter('val') : 0;

        try
        {
            if ( $attrName != 'addition-qty_per_unit' )
            {
                throw new Exception('Invalid column submission !');
            }

            $searchCol   = 'sub_package_post_contract_bill_item_rate_idAndbill_column_setting_id';
            $searchVal   = array( $postContractSubPackageItemRate->id, $billType->id );
            $claimRecord = Doctrine_Core::getTable('PostContractSubPackageRemeasurementClaim')->findOneBy($searchCol, $searchVal);

            // get item remeasurement total per unit
            $totalPerUnit = $value * $postContractSubPackageItemRate->rate;

            $claimRecord = ( $claimRecord ) ? $claimRecord : new PostContractSubPackageRemeasurementClaim();

            if ( $claimRecord->isNew() )
            {
                $claimRecord->sub_package_post_contract_bill_item_rate_id = $postContractSubPackageItemRate->id;
                $claimRecord->bill_column_setting_id                      = $billType->id;
            }

            $claimRecord->qty_per_unit   = Utilities::prelimRounding($value);
            $claimRecord->total_per_unit = Utilities::prelimRounding($totalPerUnit);
            $claimRecord->has_build_up   = false;
            $claimRecord->save();

            // get post contract item total
            $postContractItemTotal = DoctrineQuery::create()
                ->from('PostContractBillItemType e')
                ->where('e.post_contract_id = ?', $postContract->id)
                ->andWhere('e.bill_item_id = ?', $postContractSubPackageItemRate->bill_item_id)
                ->andWhere('e.bill_column_setting_id = ?', $billType->id)
                ->limit(1)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();

            $quantityPerUnit  = ( $postContractItemTotal ) ? $postContractItemTotal['qty_per_unit'] : 0;
            $itemTotalPerUnit = Utilities::prelimRounding($postContractSubPackageItemRate->rate * $quantityPerUnit);

            $item['addition-qty_per_unit']   = $value;
            $item['addition-total_per_unit'] = $totalPerUnit;
            $item['addition-has_build_up']   = $claimRecord->has_build_up;
            $item['nett_addition_omission']  = Utilities::prelimRounding($totalPerUnit - $itemTotalPerUnit);

            $success = true;
        }
        catch (Exception $e)
        {
            $success = false;
            $item    = array();
        }

        return $this->renderJson(array( 'success' => $success, 'item' => $item ));
    }

}