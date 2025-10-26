<?php

/**
 * subPackagePostContractStandardBillClaim actions.
 *
 * @package    buildspace
 * @subpackage subPackagePostContractStandardBillClaim
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subPackagePostContractStandardBillClaimActions extends BaseActions {

    public function executeGetTypeList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $records = array();

        $revision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);

        $stmt = $pdo->prepare("SELECT stype.bill_column_setting_id AS id, type_ref.id as type_ref_id, type_ref.new_name, stype.sub_package_id, stype.bill_column_setting_id, stype.counter
            FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
            LEFT JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " type_ref ON type_ref.bill_column_setting_id = stype.bill_column_setting_id AND type_ref.counter = stype.counter
            LEFT JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
            WHERE cs.project_structure_id = " . $bill->id . " AND stype.sub_package_id = " . $subPackage->id . " ORDER BY stype.bill_column_setting_id, stype.counter ASC");

        $stmt->execute();

        $columnSettingTypeRefs = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT cs.id, cs.name, cs.quantity, cs.use_original_quantity, SUM(COALESCE(ROUND(rate.rate * type.qty_per_unit, 2) ,0)) AS total_per_unit
            FROM " . BillColumnSettingTable::getInstance()->getTableName() . " cs
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.id = cs.project_structure_id AND bill.deleted_at IS NULL
            JOIN " . SubPackageTable::getInstance()->getTableName() . " sp ON bill.root_id = sp.project_structure_id
            JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON pc.project_structure_id = sp.project_structure_id
            LEFT JOIN " . PostContractBillItemTypeTable::getInstance()->getTableName() . " type ON type.post_contract_id = pc.id AND type.bill_column_setting_id = cs.id
            LEFT JOIN " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = type.bill_item_id AND rate.sub_package_id = sp.id
            WHERE bill.id = " . $bill->id . " AND cs.deleted_at IS NULL AND sp.deleted_at IS NULL AND sp.id = " . $subPackage->id . " GROUP BY cs.id ORDER BY cs.id ASC");

        $stmt->execute();

        $billColumnSettings = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        $typeItemGrandTotals = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByTypeRef($bill->id, $revision, $project->PostContract->id, $subPackage->id);

        foreach ( $columnSettingTypeRefs as $columnId => $typeRefs )
        {
            $column = ( array_key_exists($columnId, $billColumnSettings) ) ? $billColumnSettings[$columnId][0] : false;

            if ( $column )
            {
                array_push($records, array(
                    'count'       => - 1,
                    'id'          => 'type' . '-' . $columnId,
                    'description' => $column['name'],
                    'new_name'    => '',
                    'level'       => 0
                ));

                foreach ( $typeRefs as $typeRef )
                {
                    $grandTotal = false;

                    if(isset($typeItemGrandTotals[$typeRef['type_ref_id']]))
                    {
                        $grandTotal = $typeItemGrandTotals[$typeRef['type_ref_id']][0];
                        unset($typeItemGrandTotals[$typeRef['type_ref_id']]);
                    }

                    array_push($records, array(
                        'count'                 => $typeRef['counter'],
                        'id'                    => $columnId . '-' . $typeRef['counter'],
                        'description'           => ( strlen($typeRef['new_name']) ) ? $typeRef['new_name'] : 'Unit ' . $typeRef['counter'],
                        'new_name'              => '',
                        'level'                 => 1,
                        'relation_name'         => $column['name'],
                        'total_per_unit'        => $column['total_per_unit'],
                        'up_to_date_amount'     => ( $grandTotal ) ? $grandTotal['up_to_date_amount'] : 0,
                        'up_to_date_qty'        => ( $grandTotal ) ? $grandTotal['up_to_date_qty'] : 0,
                        'up_to_date_percentage' => ( $grandTotal && $column['total_per_unit'] != 0 ) ? ($grandTotal['up_to_date_amount'] / $column['total_per_unit']) * 100 : 0,
                        'relation_id'           => $columnId
                    ));
                }
            }
        }

        array_push($records, array(
            'count'       => - 1,
            'id'          => 'defaultEmptyRow',
            'description' => '',
            'new_name'    => '',
            'level'       => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeUpdateTypeList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')));

        $counter      = $request->getParameter('counter');
        $typeId       = $request->getParameter('type_id');
        $postContract = $project->PostContract;

        $errorMsg = null;

        $con  = $project->getTable()->getConnection();
        $item = array();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $typeItem = DoctrineQuery::create()->select('t.id, t.post_contract_id, t.bill_column_setting_id, t.counter, t.new_name')
                ->from('PostContractStandardClaimTypeReference t')
                ->where('t.post_contract_id = ? AND t.bill_column_setting_id = ? AND t.counter = ?', array( $postContract->id, $typeId, $counter ))
                ->fetchOne();

            if ( !$typeItem )
            {
                $typeItem = new PostContractStandardClaimTypeReference();

                $typeItem->post_contract_id       = $postContract->id;
                $typeItem->bill_column_setting_id = $typeId;
                $typeItem->counter                = $counter;

                $typeItem->save($con);
            }

            $typeItem->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $typeItem->save($con);

            $item['id']          = $typeItem->id;
            $item['counter']     = $typeItem->counter;
            $item['description'] = ( $typeItem->new_name != null && $typeItem->new_name != '' ) ? $typeItem->new_name : 'Unit ' . $typeItem->counter;
            $item['new_name']    = '';

            $success = true;
            $con->commit();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'item'     => $item,
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetTypeItem(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('type_id'))
        );

        $counter = $request->getParameter('counter');

        $typeItem = DoctrineQuery::create()->select('t.id, t.post_contract_id, t.bill_column_setting_id, t.counter, t.new_name')
            ->from('PostContractStandardClaimTypeReference t')
            ->where('t.post_contract_id = ? AND t.bill_column_setting_id = ? AND t.counter = ?', array( $project->PostContract->id, $billColumnSetting->id, $counter ))
            ->fetchOne();

        if ( !$typeItem )
        {
            $typeItem                         = new PostContractStandardClaimTypeReference();
            $typeItem->post_contract_id       = $project->PostContract->id;
            $typeItem->bill_column_setting_id = $billColumnSetting->id;
            $typeItem->counter                = $counter;

            $typeItem->save();
        }

        $record['count']         = $typeItem->counter;
        $record['id']            = $typeItem->id;
        $record['description']   = PostContractStandardClaimTypeReference::LABEL_TYPE_UNIT . ' ' . $typeItem->counter;
        $record['new_name']      = ( $typeItem->new_name != null ) ? $typeItem->new_name : '';
        $record['relation_id']   = $typeItem->bill_column_setting_id;
        $record['relation_name'] = $billColumnSetting->name;

        return $this->renderJson($record);
    }

    public function executeGetBillElementList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $typeItem = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        $revision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);

        $stmt = $pdo->prepare("SELECT e.id, e.description, e.note, COALESCE(SUM(rate.single_unit_grand_total), 0) AS overall_total_after_markup
            FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.id =  e.project_structure_id AND b.deleted_at IS NULL
            WHERE b.id = " . $bill->id . " AND rate.sub_package_id = " . $subPackage->id . " GROUP BY e.id ORDER BY e.id ASC");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

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
                $elements[$key]['prev_percentage']       = ( $totalPerUnit != 0 ) ? number_format(( $prevAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                $elements[$key]['prev_amount']           = $prevAmount;
                $elements[$key]['current_percentage']    = ( $totalPerUnit != 0 ) ? number_format(( $currentAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                $elements[$key]['current_amount']        = $currentAmount;
                $elements[$key]['up_to_date_percentage'] = ( $totalPerUnit != 0 ) ? number_format(( $upToDateAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                $elements[$key]['up_to_date_amount']     = $upToDateAmount;
                $elements[$key]['up_to_date_qty']        = $elementGrandTotals[$elementId][0]['up_to_date_qty'];
            }

            $elements[$key]['has_note']          = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            $elements[$key]['claim_type_ref_id'] = $typeItem->id;
            $elements[$key]['relation_id']       = $bill->id;
            $elements[$key]['_csrf_token']       = $form->getCSRFToken();
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
            '_csrf_token'           => $form->getCSRFToken()
        ));

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
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $form         = new BaseForm();
        $items        = array();
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;

        $revision  = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $billItems = BillItemTable::getDataStructureForSubPackageStandardClaimBillItemList($element, $revision, $subPackage->id, $typeRef);

        foreach ( $billItems as $billItem )
        {
            $billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
            $billItem['type']                      = (string) $billItem['type'];
            $billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
            $billItem['relation_id']               = $element->id;
            $billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
            $billItem['has_note']                  = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
            $billItem['_csrf_token']               = $form->getCSRFToken();
            $billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

            unset( $billItem['has_build_up'] );

            array_push($items, $billItem);

            unset( $billItem );
        }

        unset( $billItems );

        array_push($items, array(
            'id'                    => Constants::GRID_LAST_ROW,
            'bill_ref'              => '',
            'description'           => '',
            'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                => '-1',
            'uom_symbol'            => '',
            'relation_id'           => $element->id,
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
            '_csrf_token'           => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeStandardBillClaimUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $claimTypeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $item       = array();
        $errorMsg   = null;
        $fieldName  = $request->getParameter('attr_name');
        $fieldValue = ( is_numeric($request->getParameter('val')) ) ? $request->getParameter('val') : 0;

        $revision = $subPackage->getOpenClaimRevision();

        if(!$revision)
        {
            return $this->renderJson([
                'item'     => null,
                'success'  => false,
                'errorMsg' => "No In Progress Claim Revision"
            ]);
        }

        try
        {
            $claimItem = DoctrineQuery::create()->select('*')
                ->from('SubPackagePostContractStandardClaim c')
                ->where('c.claim_type_ref_id = ? AND c.bill_item_id = ? AND c.revision_id = ? ',
                    array( $claimTypeRef->id, $billItem->id, $revision->id ))
                ->fetchOne();

            if ( !$claimItem )
            {
                $claimItem = new SubPackagePostContractStandardClaim();
                $claimItem->setClaimTypeRefId($claimTypeRef->id);
                $claimItem->setBillItemId($billItem->id);
                $claimItem->setRevisionId($revision->id);
            }

            if ( $fieldName == 'rate' )
            {
                $billItemRate = DoctrineQuery::create()->select('*')
                    ->from('SubPackagePostContractBillItemRate c')
                    ->where('c.bill_item_id = ? AND c.sub_package_id = ? ', array( $billItem->id, $subPackage->id ))
                    ->fetchOne();

                $billItemRate->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

                $billItemRate->save();

                $item = array(
                    'id'             => $claimItem->id,
                    'rate'           => $billItemRate->rate,
                    'total_per_unit' => $billItemRate->rate
                );
            }
            else
            {
                $claimItem->calculateClaimColumn($fieldName, $fieldValue, $revision, $subPackage->id);

                $item = array(
                    'id'                    => $claimItem->id,
                    'current_percentage'    => $claimItem->current_percentage,
                    'current_amount'        => $claimItem->current_amount,
                    'up_to_date_percentage' => $claimItem->up_to_date_percentage,
                    'up_to_date_amount'     => $claimItem->up_to_date_amount,
                    'up_to_date_qty'        => $claimItem->up_to_date_qty
                );
            }

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'item'     => ( $item ) ? $item : null,
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeStandardBillClaimElementUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $claimTypeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $item       = array();
        $errorMsg   = null;
        $fieldName  = $request->getParameter('attr_name');
        $fieldValue = ( is_numeric($request->getParameter('val')) ) ? $request->getParameter('val') : 0;

        $revision = $subPackage->getOpenClaimRevision();

        if(!$revision)
        {
            return $this->renderJson([
                'item'     => null,
                'success'  => false,
                'errorMsg' => "No In Progress Claim Revision"
            ]);
        }
        
        try
        {
            SubPackagePostContractStandardClaimTable::updateClaimByElement($fieldName, $fieldValue, $element->id, $claimTypeRef, $subPackage->id, $revision);

            $item = SubPackagePostContractStandardClaimTable::getTotalClaimRateByElementId($element->id, $claimTypeRef, $revision, $subPackage->id);

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'item'     => ( $item ) ? $item : null,
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetUnitList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );
        $pdo             = $billColumnSetting->getTable()->getConnection()->getDbh();
        $excludedCounter = $request->getParameter('exc_count');

        $stmt = $pdo->prepare("SELECT stype.counter, ptype_c.new_name
            FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
            LEFT JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " ptype_c
            ON stype.bill_column_setting_id = ptype_c.bill_column_setting_id AND stype.counter = ptype_c.counter
            WHERE stype.sub_package_id = " . $subPackage->id . " AND stype.bill_column_setting_id = " . $billColumnSetting->id);

        $stmt->execute();

        $claimTypeRef = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        $items = array();

        for ( $i = 1; $i <= $billColumnSetting->quantity; $i ++ )
        {
            if ( $i == $excludedCounter )
            {
                continue;
            }

            if ( array_key_exists($i, $claimTypeRef) )
            {
                array_push($items, array(
                    'count'       => $i,
                    'description' => ( ( $claimTypeRef[$i][0] != null || $claimTypeRef[$i][0] != '' ) ) ? $claimTypeRef[$i][0] : PostContractStandardClaimTypeReference::LABEL_TYPE_UNIT . ' ' . $i
                ));
            }
        }

        //Default Empty Row
        array_push($items, array(
            'count'       => Constants::GRID_LAST_ROW,
            'description' => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'count',
            'items'      => $items
        ));
    }

    public function executeApplyToOtherUnit(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id')) and
            $mainTypeItem = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $typeItem     = array();
        $counters     = explode(',', $request->getParameter('counters'));
        $postContract = $project->PostContract;
        $revision     = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);

        foreach ( $counters as $counter )
        {
            $typeItem = PostContractStandardClaimTypeReferenceTable::getTypeReferenceByCounterAndColumnId($postContract->id, $billColumnSetting->id, $counter);

            if ( !$typeItem )
            {
                $typeItem                         = new PostContractStandardClaimTypeReference();
                $typeItem->post_contract_id       = $postContract->id;
                $typeItem->bill_column_setting_id = $billColumnSetting->id;
                $typeItem->counter                = $counter;

                $typeItem->save();
                $typeItem = $typeItem->toArray();
            }

            SubPackagePostContractStandardClaimTable::cloneUpToDateAmountByIdAndRevision($subPackage, $revision, $mainTypeItem->toArray(), $typeItem);
        }

        return $this->renderJson($typeItem);
    }

    public function executeLumpSumPercentageForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
            $item = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $pdo          = $project->getTable()->getConnection()->getDbh();
        $postContract = $project->PostContract;

        $joinSql = '';

        if ( $postContract->selected_type_rate == PostContract::RATE_TYPE_CONTRACTOR )
        {
            $stmt = $pdo->prepare("SELECT c.id, c.name, c.shortname, tc.id AS tender_company_id
                FROM " . TenderSettingTable::getInstance()->getTableName() . " t
                LEFT JOIN " . CompanyTable::getInstance()->getTableName() . " c ON c.id = t.awarded_company_id
                LEFT JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.company_id = c.id AND tc.project_structure_id = " . $project->id . "
                WHERE t.project_structure_id = " . $project->id);

            $stmt->execute();

            $selectedTenderer = $stmt->fetch(PDO::FETCH_ASSOC);

            $joinSql .= "LEFT JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = r.bill_item_id AND rate.tender_company_id = " . $selectedTenderer['tender_company_id'] . "
                LEFT JOIN " . TenderBillItemLumpSumPercentageTable::getInstance()->getTableName() . " ls ON ls.tender_bill_item_rate_id = rate.id";
        }
        else if ( $postContract->selected_type_rate == PostContract::RATE_TYPE_RATIONALIZED )
        {
            $joinSql .= "LEFT JOIN " . TenderBillItemRationalizedRatesTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = r.bill_item_id
                LEFT JOIN " . TenderBillItemRationalizedLumpSumPercentageTable::getInstance()->getTableName() . " ls ON ls.tender_bill_item_rationalized_rates_id = rate.id";
        }
        else
        {
            $joinSql .= "LEFT JOIN " . BillItemLumpSumPercentageTable::getInstance()->getTableName() . " ls ON ls.bill_item_id = r.bill_item_id";
        }

        $stmt = $pdo->prepare("SELECT COALESCE(r.rate, 0) AS rate, ls.percentage
            FROM " . PostContractBillItemRateTable::getInstance()->getTableName() . " r " . $joinSql . "
            WHERE r.bill_item_id = " . $item->id . " AND r.post_contract_id = " . $postContract->id);

        $stmt->execute();

        $billItem = $stmt->fetch(PDO::FETCH_ASSOC);

        $form = new BillItemLumpSumPercentageForm();

        return $this->renderJson(array(
            'bill_item_lump_sum_percentage[rate]'        => number_format($billItem['rate'] / ( $billItem['percentage'] / 100 ), 2, '.', ''),
            'bill_item_lump_sum_percentage[percentage]'  => number_format($billItem['percentage'], 2, '.', ''),
            'bill_item_lump_sum_percentage[amount]'      => number_format($billItem['rate'], 2, '.', ''),
            'bill_item_lump_sum_percentage[_csrf_token]' => $form->getCSRFToken()
        ));
    }

    public function executeLumpSumPercentageUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        $item     = array();

        try
        {
            $billItemRate = DoctrineQuery::create()->select('*')
                ->from('PostContractBillItemRate c')
                ->where('c.bill_item_id = ?', $billItem->id)
                ->fetchOne();

            $billItemRate->setRate($request->getParameter('rate'));
            $billItemRate->save();

            $item['rate']           = $billItemRate->rate;
            $item['total_per_unit'] = $billItemRate->rate;

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errorMsg, 'data' => $item ));
    }

}