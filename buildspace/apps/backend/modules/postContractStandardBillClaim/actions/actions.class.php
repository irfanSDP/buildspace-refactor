<?php

/**
 * postContractStandardBillClaim actions.
 *
 * @package    buildspace
 * @subpackage postContractStandardBillClaim
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractStandardBillClaimActions extends BaseActions {

    public function executeGetTypeList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
        );

        $records                = array();
        $billColumnSettingItems = array();

        $revision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

        //Get Type List
        $typeItems = DoctrineQuery::create()
            ->select('t.id, t.post_contract_id, t.bill_column_setting_id, t.counter, t.new_name')
            ->from('PostContractStandardClaimTypeReference t')
            ->leftJoin('t.BillColumnSetting cs')
            ->where('t.post_contract_id = ? AND cs.project_structure_id = ?', array( $project->PostContract->id, $bill->id ))
            ->fetchArray();

        $typeItemGrandTotals            = PostContractTable::getTotalClaimRateGroupByTypeRef($bill->id, $revision, $project->PostContract->id);
        $variationOrderOmittedBillItems = VariationOrderItemTable::getNumberOfOmittedBillItems($project, $bill);

        foreach ( $typeItems as $item )
        {
            $billColumnSettingItems[$item['bill_column_setting_id']][$item['counter']] = array(
                'id'       => $item['id'],
                'new_name' => $item['new_name']
            );

            $billColumnSettingItems[ $item['bill_column_setting_id'] ][ $item['counter'] ]['up_to_date_amount']          = 0;
            $billColumnSettingItems[ $item['bill_column_setting_id'] ][ $item['counter'] ]['up_to_date_percentage']      = 0;
            $billColumnSettingItems[ $item['bill_column_setting_id'] ][ $item['counter'] ]['up_to_date_qty']             = 0;
            $billColumnSettingItems[ $item['bill_column_setting_id'] ][ $item['counter'] ]['imported_up_to_date_amount'] = 0;

            if ( array_key_exists($item['id'], $typeItemGrandTotals) )
            {
                $billColumnSettingItems[ $item['bill_column_setting_id'] ][ $item['counter'] ]['up_to_date_amount']          = $typeItemGrandTotals[ $item['id'] ][0]['up_to_date_amount'];
                $billColumnSettingItems[ $item['bill_column_setting_id'] ][ $item['counter'] ]['up_to_date_percentage']      = $typeItemGrandTotals[ $item['id'] ][0]['up_to_date_percentage'];
                $billColumnSettingItems[ $item['bill_column_setting_id'] ][ $item['counter'] ]['up_to_date_qty']             = $typeItemGrandTotals[ $item['id'] ][0]['up_to_date_qty'];
                $billColumnSettingItems[ $item['bill_column_setting_id'] ][ $item['counter'] ]['imported_up_to_date_amount'] = $typeItemGrandTotals[ $item['id'] ][0]['imported_up_to_date_amount'];
            }
        }

        //Get BillColumnSetting List
        $billColumnSettings = DoctrineQuery::create()
            ->select('cs.*, SUM(type_ref.total_per_unit) AS total_per_unit')
            ->from('BillColumnSetting cs')
            ->leftJoin('cs.PostContractBillItemTypeReferences type_ref')
            ->where('cs.project_structure_id = ? ', array( $bill->id ))
            ->groupBy('cs.id')
            ->fetchArray();

        foreach ( $billColumnSettings as $column )
        {
            $count = $column['quantity'];

            array_push($records, array(
                'count'            => -1,
                'id'               => 'type' . '-' . $column['id'],
                'description'      => $column['name'],
                'new_name'         => '',
                'vo_omitted_items' => '',
                'level'            => 0
            ));

            for ( $i = 1; $i <= $count; $i ++ )
            {
                $record['count']            = $i;
                $record['id']               = $column['id'] . '-' . $i;
                $record['description']      = 'Unit ' . $i;
                $record['new_name']         = '';
                $record['relation_id']      = $column['id'];
                $record['relation_name']    = $column['name'];
                $record['total_per_unit']   = $column['total_per_unit'];
                $record['level']            = 1;
                $record['vo_omitted_items'] = "";

                if ( array_key_exists($column['id'], $billColumnSettingItems) && array_key_exists($i, $billColumnSettingItems[$column['id']]) )
                {
                    $totalPerUnit = $column['total_per_unit'];

                    $totalAmount = $billColumnSettingItems[$column['id']][$i]['up_to_date_amount'];

                    $importedTotalAmount = $billColumnSettingItems[$column['id']][$i]['imported_up_to_date_amount'];

                    $percentage = ( $totalPerUnit > 0 ) ? ( $totalAmount / $totalPerUnit ) * 100 : 0;

                    $importedPercentage = ( $totalPerUnit > 0 ) ? ( $importedTotalAmount / $totalPerUnit ) * 100 : 0;

                    $record['description']                    = ( $billColumnSettingItems[$column['id']][$i]['new_name'] != null ) ? $billColumnSettingItems[$column['id']][$i]['new_name'] : 'Unit ' . ( $i );
                    $record['new_name']                       = '';
                    $record['up_to_date_amount']              = $totalAmount;
                    $record['up_to_date_qty']                 = $billColumnSettingItems[$column['id']][$i]['up_to_date_qty'];
                    $record['up_to_date_percentage']          = $percentage;
                    $record['imported_up_to_date_percentage'] = $importedPercentage;
                    $record['imported_up_to_date_amount']     = $importedTotalAmount;
                    $record['total_per_unit']                 = $totalPerUnit;
                    $record['relation_id']                    = $column['id'];
                    $record['relation_name']                  = $column['name'];
                    $record['level']                          = 1;
                    $record['vo_omitted_items']               = $variationOrderOmittedBillItems[$billColumnSettingItems[$column['id']][$i]['id']] ?? "";
                }

                array_push($records, $record);

                unset( $record );
            }
        }

        array_push($records, array(
            'count'            => -1,
            'id'               => 'defaultEmptyRow',
            'description'      => '',
            'new_name'         => '',
            'level'            => 0,
            'vo_omitted_items' => ''
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

        $typeItem = DoctrineQuery::create()
            ->select('t.id, t.post_contract_id, t.bill_column_setting_id, t.counter, t.new_name')
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
            $typeItem = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
        );

        $postContract = $project->PostContract;

        $revision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description, e.note')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        $elementGrandTotals             = PostContractTable::getTotalClaimRateGroupByElement($bill->id, $typeItem, $revision, $postContract->id);
        $variationOrderOmittedBillItems = VariationOrderItemTable::getNumberOfOmittedBillItems($project, $bill, $typeItem->counter);

        foreach ( $elements as $key => $element )
        {
            $elementId = $element['id'];

            if ( array_key_exists($elementId, $elementGrandTotals) )
            {
                $prevAmount             = $elementGrandTotals[ $elementId ][0]['prev_amount'];
                $currentAmount          = $elementGrandTotals[ $elementId ][0]['current_amount'];
                $totalPerUnit           = $elementGrandTotals[ $elementId ][0]['total_per_unit'];
                $upToDateAmount         = $elementGrandTotals[ $elementId ][0]['up_to_date_amount'];
                $importedUpToDateAmount = $elementGrandTotals[ $elementId ][0]['imported_up_to_date_amount'];

                $elements[ $key ]['total_per_unit']                 = $totalPerUnit;
                $elements[ $key ]['prev_percentage']                = ( $totalPerUnit != 0 ) ? number_format(( $prevAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                $elements[ $key ]['prev_amount']                    = $prevAmount;
                $elements[ $key ]['current_percentage']             = ( $totalPerUnit != 0 ) ? number_format(( $currentAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                $elements[ $key ]['current_amount']                 = $currentAmount;
                $elements[ $key ]['up_to_date_percentage']          = ( $totalPerUnit != 0 ) ? number_format(( $upToDateAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                $elements[ $key ]['up_to_date_amount']              = $upToDateAmount;
                $elements[ $key ]['up_to_date_qty']                 = $elementGrandTotals[ $elementId ][0]['up_to_date_qty'];
                $elements[ $key ]['imported_up_to_date_percentage'] = ( $totalPerUnit != 0 ) ? number_format(( $importedUpToDateAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
                $elements[ $key ]['imported_up_to_date_amount']     = $importedUpToDateAmount;
            }

            $elements[ $key ]['has_note']          = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            $elements[ $key ]['claim_type_ref_id'] = $typeItem->id;
            $elements[ $key ]['relation_id']       = $bill->id;
            $elements[ $key ]['vo_omitted_items']  = $variationOrderOmittedBillItems[ $element['id'] ] ?? "";
            $elements[ $key ]['_csrf_token']       = $form->getCSRFToken();
        }

        array_push($elements, array(
            'id'                         => Constants::GRID_LAST_ROW,
            'description'                => '',
            'total_per_unit'             => 0,
            'prev_percentage'            => 0,
            'prev_amount'                => 0,
            'current_percentage'         => 0,
            'current_amount'             => 0,
            'up_to_date_percentage'      => 0,
            'up_to_date_amount'          => 0,
            'up_to_date_qty'             => 0,
            'imported_up_to_date_amount' => 0,
            'claim_type_ref_id'          => -1,
            'vo_omitted_items'           => '',
            'relation_id'                => $bill->id,
            '_csrf_token'                => $form->getCSRFToken()
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
            $typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
        );

        $form         = new BaseForm();
        $items        = array();
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;

        $revision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

        list(
            $billItems
            ) = BillItemTable::getDataStructureForStandardClaimBillItemList($element, $bill, $revision, $project->PostContract->id, $typeRef);

        $omittedAtVariationOrders = array();

        foreach(BillItemTable::getOmittedAtVariationOrders(Utilities::arrayValueRecursive('id', $billItems), $typeRef->counter) as $record)
        {
            $omittedAtVariationOrders[$record['bill_item_id']] = $record['description'];
        }

        foreach ( $billItems as $billItem )
        {
            $billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
            $billItem['type']                      = (string) $billItem['type'];
            $billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
            $billItem['relation_id']               = $element->id;
            $billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
            $billItem['has_note']                  = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
            $billItem['omitted_at_vo']             = $omittedAtVariationOrders[$billItem['id']] ?? "";
            $billItem['_csrf_token']               = $form->getCSRFToken();
            $billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

            unset( $billItem['has_build_up'] );

            array_push($items, $billItem);

            unset( $billItem );
        }

        unset( $billItems );

        array_push($items, array(
            'id'                         => Constants::GRID_LAST_ROW,
            'bill_ref'                   => '',
            'description'                => '',
            'type'                       => (string)ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                     => '-1',
            'uom_symbol'                 => '',
            'relation_id'                => $element->id,
            'level'                      => 0,
            'qty_per_unit'               => 0,
            'total_per_unit'             => 0,
            'prev_percentage'            => 0,
            'prev_amount'                => 0,
            'current_percentage'         => 0,
            'current_amount'             => 0,
            'up_to_date_percentage'      => 0,
            'up_to_date_amount'          => 0,
            'up_to_date_qty'             => 0,
            'imported_up_to_date_amount' => 0,
            'include'                    => 1,
            'omitted_at_vo'              => "",
            '_csrf_token'                => $form->getCSRFToken()
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
            $claimTypeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
        );

        $item       = [];
        $errorMsg   = null;
        $fieldName  = $request->getParameter('attr_name');
        $fieldValue = ( is_numeric($request->getParameter('val')) ) ? $request->getParameter('val') : 0;

        $postContract = $project->PostContract;

        $revision = $postContract->getInProgressClaimRevision();

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
                ->from('PostContractStandardClaim c')
                ->where('c.claim_type_ref_id = ? AND c.bill_item_id = ? AND c.revision_id = ? ', array( $claimTypeRef->id, $billItem->id, $revision->id ))
                ->fetchOne();

            if ( !$claimItem )
            {
                $claimItem = new PostContractStandardClaim();
                $claimItem->setClaimTypeRefId($claimTypeRef->id);
                $claimItem->setBillItemId($billItem->id);
                $claimItem->setRevisionId($revision->id);
            }

            if ( $fieldName == 'rate' )
            {
                $billItemRate = DoctrineQuery::create()->select('*')
                    ->from('PostContractBillItemRate c')
                    ->where('c.bill_item_id = ?', $billItem->id)
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
                $claimItem->calculateClaimColumn($fieldName, $fieldValue, $revision);

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

            $openClaimRevision = $postContract->getOpenClaimRevision();

            if($openClaimRevision->ClaimCertificate->id)
            {
                $openClaimRevision->ClaimCertificate->save();
            }
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
            $claimTypeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
        );

        $item       = array();
        $errorMsg   = null;
        $fieldName  = $request->getParameter('attr_name');
        $fieldValue = ( is_numeric($request->getParameter('val')) ) ? $request->getParameter('val') : 0;

        $postContract = $project->PostContract;

        $revision = $postContract->getInProgressClaimRevision();

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
            PostContractStandardClaimTable::updateClaimByElement($fieldName, $fieldValue, $element->id, $claimTypeRef, $postContract->id, $revision);

            $openClaimRevision = $postContract->getOpenClaimRevision();

            if($openClaimRevision->ClaimCertificate->id)
            {
                $openClaimRevision->ClaimCertificate->save();
            }

            $item = PostContractTable::getTotalClaimRateByElementId($element->id, $claimTypeRef, $revision, $postContract->id);

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
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('id'))
        );
        $pdo             = $billColumnSetting->getTable()->getConnection()->getDbh();
        $excludedCounter = $request->getParameter('exc_count');

        $stmt = $pdo->prepare("SELECT ptype_c.counter, ptype_c.new_name FROM " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " ptype_c
            WHERE ptype_c.bill_column_setting_id = " . $billColumnSetting->id);

        $stmt->execute();

        $claimTypeRef = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        $items = array();

        for ( $i = 1; $i <= $billColumnSetting->quantity; $i ++ )
        {
            if ( $i != $excludedCounter )
            {
                if ( array_key_exists($i, $claimTypeRef) && ( $claimTypeRef[$i][0] != null || $claimTypeRef[$i][0] != '' ) )
                {
                    array_push($items, array(
                        'count'       => $i,
                        'description' => $claimTypeRef[$i][0]
                    ));
                }
                else
                {
                    array_push($items, array(
                        'count'       => $i,
                        'description' => PostContractStandardClaimTypeReference::LABEL_TYPE_UNIT . ' ' . $i
                    ));
                }
            }
        }

        //Default Empty Row
        array_push($items, array(
            'count'       => - 1,
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
            $mainTypeItem = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
        );

        $counters = explode(',', $request->getParameter('counters'));
        if(empty($counters))
        {
            return $this->renderJson(['success'=>false]);
        }

        $postContract = $project->PostContract;

        $revision = $postContract->getInProgressClaimRevision();

        if(!$revision)
        {
            return $this->renderJson([
                'success'  => false,
                'errorMsg' => "No In Progress Claim Revision"
            ]);
        }

        $pdo = PostContractStandardClaimTypeReferenceTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT t.counter, t.id
            FROM ".PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName()." t
            WHERE t.post_contract_id = ".$postContract->id."
            AND t.bill_column_setting_id = ".$billColumnSetting->id."
            AND t.counter IN (".implode(',', $counters).")
            ORDER BY t.counter ASC");

        $stmt->execute();

        $existingTypeItems = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $unitsWithoutItemTypes = array_diff($counters, array_keys($existingTypeItems));

        if(!empty($unitsWithoutItemTypes))
        {
            $insertValues  = [];
            $questionMarks = [];
            $userId        = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');

            foreach($unitsWithoutItemTypes as $unit)
            {
                $data = [
                    intval($postContract->id),
                    intval($billColumnSetting->id),
                    intval($unit)
                ];

                $insertValues = array_merge($insertValues, $data);

                $questionMarks[] = '(' . implode(',', array_fill(0, count($data), '?')) . ')';
            }

            if(!empty($insertValues))
            {
                $stmt = $pdo->prepare("INSERT INTO " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . "
                (post_contract_id, bill_column_setting_id, counter)
                VALUES " . implode(',', $questionMarks));

                $stmt->execute($insertValues);
            }

            $stmt = $pdo->prepare("SELECT t.counter, t.id
                FROM ".PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName()." t
                WHERE t.post_contract_id = ".$postContract->id."
                AND t.bill_column_setting_id = ".$billColumnSetting->id."
                AND t.counter IN (".implode(',', $counters).")
                ORDER BY t.counter ASC");

            $stmt->execute();

            //refetch existing item types
            $existingTypeItems = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        foreach($existingTypeItems as $counter => $typeItemId)
        {
            PostContractStandardClaimTypeReferenceTable::cloneUpToDateAmountByIdAndRevision($postContract->id, $revision, $mainTypeItem->toArray(), $typeItemId);
        }

        $openClaimRevision = $postContract->getOpenClaimRevision();

        if($openClaimRevision && $openClaimRevision->ClaimCertificate)
        {
            $openClaimRevision->ClaimCertificate->save();
        }

        return $this->renderJson(['success'=>true]);
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
