<?php

/**
 * subPackagePostContractPreliminaries actions.
 *
 * @package    buildspace
 * @subpackage subPackagePostContractPreliminaries
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subPackagePostContractPreliminariesActions extends BaseActions {

    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT e.id, e.description, e.note, COALESCE(SUM(rate.single_unit_grand_total), 0) AS overall_total_after_markup
            FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.id =  e.project_structure_id AND b.deleted_at IS NULL
            WHERE b.id = " . $bill->id . " AND rate.sub_package_id = " . $subPackage->id . " GROUP BY e.id ORDER BY e.id ASC");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        $billMarkupSetting = $bill->BillMarkupSetting;

        $markupSettingsInfo = array(
            'bill_markup_enabled'    => $billMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage' => $billMarkupSetting->bill_markup_percentage,
            'element_markup_enabled' => $billMarkupSetting->element_markup_enabled,
            'item_markup_enabled'    => $billMarkupSetting->item_markup_enabled,
            'rounding_type'          => $billMarkupSetting->rounding_type > 0 ? $billMarkupSetting->rounding_type : BillMarkupSetting::ROUNDING_TYPE_DISABLED
        );

        $claimProjectRevision         = SubPackagePostContractClaimRevisionTable::getCurrentProjectRevision($subPackage);
        $selectedClaimProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);

        list(
            $elementBillItems, $initialCostings, $timeBasedCostings, $prevTimeBasedCostings, $workBasedCostings, $prevWorkBasedCostings, $finalCostings, $includeInitialCostings, $includeFinalCostings
            ) = SubPackagePostContractBillItemRateTable::getPrelimElementClaimCosting($subPackage, $elements);

        foreach ( $elements as $key => $element )
        {
            $elements[$key]['markup_rounding_type'] = $markupSettingsInfo['rounding_type'];

            $elementGrandTotals = 0;

            unset( $elements[$key]['FormulatedColumns'] );

            $elements[$key]['has_note']                 = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            $elements[$key]['note']                     = (string) $element['note'];
            $elements[$key]['relation_id']              = $bill->id;
            $elements[$key]['_csrf_token']              = $form->getCSRFToken();
            $elements[$key]['previousClaim-amount']     = 0;
            $elements[$key]['currentClaim-amount']      = 0;
            $elements[$key]['upToDateClaim-amount']     = 0;
            $elements[$key]['grand_total']              = 0;
            $elements[$key]['previousClaim-percentage'] = 0;
            $elements[$key]['currentClaim-percentage']  = 0;
            $elements[$key]['upToDateClaim-percentage'] = 0;

            if ( !isset ( $elementBillItems[$element['id']] ) )
            {
                continue;
            }

            foreach ( $elementBillItems[$element['id']] as $billItem )
            {
                $elementGrandTotals += $billItem['item_total'];

                SubPackagePreliminariesClaimTable::calculateClaimRates($selectedClaimProjectRevision, $billItem, $claimProjectRevision, $initialCostings, $finalCostings, $timeBasedCostings, $workBasedCostings, $prevTimeBasedCostings, $prevWorkBasedCostings, $includeInitialCostings, $includeFinalCostings);

                $elements[$key]['previousClaim-amount'] += $billItem['previousClaim-amount'];
                $elements[$key]['currentClaim-amount'] += $billItem['currentClaim-amount'];
                $elements[$key]['upToDateClaim-amount'] += $billItem['upToDateClaim-amount'];

                unset( $billItem );
            }

            $elements[$key]['grand_total']              = $elementGrandTotals;
            $elements[$key]['previousClaim-percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$key]['previousClaim-amount'], $elementGrandTotals));
            $elements[$key]['currentClaim-percentage']  = Utilities::prelimRounding(Utilities::percent($elements[$key]['currentClaim-amount'], $elementGrandTotals));
            $elements[$key]['upToDateClaim-percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$key]['upToDateClaim-amount'], $elementGrandTotals));
        }

        array_push($elements, array(
            'id'                         => Constants::GRID_LAST_ROW,
            'description'                => '',
            'has_note'                   => false,
            'grand_total'                => 0,
            'original_grand_total'       => 0,
            'overall_total_after_markup' => 0,
            'element_sum_total'          => 0,
            'relation_id'                => $bill->id,
            'markup_rounding_type'       => $billMarkupSetting->rounding_type,
            'previousClaim-percentage'   => 0,
            'previousClaim-amount'       => 0,
            'currentClaim-percentage'    => 0,
            'currentClaim-amount'        => 0,
            'upToDateClaim-percentage'   => 0,
            'upToDateClaim-amount'       => 0,
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
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $form         = new BaseForm();
        $items        = array();
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;

        $claimProjectRevision         = SubPackagePostContractClaimRevisionTable::getCurrentProjectRevision($subPackage);
        $selectedClaimProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $column                       = $bill->BillColumnSettings->toArray();

        list(
            $billItems, $billItemTypeReferences, $billItemTypeRefFormulatedColumns, $initialCostings, $timeBasedCostings, $prevTimeBasedCostings, $workBasedCostings, $prevWorkBasedCostings, $finalCostings, $includeInitialCostings, $includeFinalCostings
            ) = SubPackagePostContractBillItemRateTable::getDataStructureForPrelimBillItemList($subPackage, $element, $bill);

        foreach ( $billItems as $billItem )
        {
            $itemTotal = $billItem['grand_total'];

            $billItem['bill_ref']             = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
            $billItem['type']                 = (string) $billItem['type'];
            $billItem['uom_id']               = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
            $billItem['relation_id']          = $element->id;
            $billItem['linked']               = false;
            $billItem['_csrf_token']          = $form->getCSRFToken();
            $billItem['has_note']             = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
            $billItem['item_total']           = Utilities::prelimRounding($itemTotal);
            $billItem['claim_at_revision_id'] = ( !empty( $billItem['claim_at_revision_id'] ) ) ? $billItem['claim_at_revision_id'] : $claimProjectRevision['id'];

            $billItem['rate']             = Utilities::prelimRounding($billItem['rate']);
            $billItem['qty-qty_per_unit'] = 0;//$billItem['qty'];
            $billItem['qty-has_build_up'] = false;
            $billItem['qty-column_id']    = $column[0]['id'];

            if ( array_key_exists($column[0]['id'], $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$column[0]['id']]) )
            {
                $billItemTypeRef = $billItemTypeReferences[$column[0]['id']][$billItem['id']];

                unset( $billItemTypeReferences[$column[0]['id']][$billItem['id']] );

                if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
                {
                    foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
                    {
                        $billItem['qty-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];
                        $billItem['qty-qty_per_unit'] = $billItemTypeRefFormulatedColumn['final_value'];

                        unset( $billItemTypeRefFormulatedColumn );
                    }
                }

                unset( $billItemTypeRef );
            }

            SubPackagePreliminariesClaimTable::calculateClaimRates($selectedClaimProjectRevision, $billItem, $claimProjectRevision, $initialCostings, $finalCostings, $timeBasedCostings, $workBasedCostings, $prevTimeBasedCostings, $prevWorkBasedCostings, $includeInitialCostings, $includeFinalCostings);

            array_push($items, $billItem);
            unset( $billItem );
        }

        unset( $billItems );

        array_push($items, array(
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
            'qty-qty_per_unit'         => '',
            'grand_total'              => '',
            'linked'                   => false,
            'rate_after_markup'        => 0,
            'grand_total_after_markup' => 0,
            'include_initial'          => 'false',
            'include_final'            => 'false',
            '_csrf_token'              => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeUpdateItemClaim(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('bill_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('sub_package_id')) and
            $subPackagePostContractItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getPostParameter('id'))
        );

        $explodedColumnType = explode('-', $request->getParameter('attr_name'));

        switch ($explodedColumnType[0])
        {
            case SubPackagePostContractBillItemRate::PRELIM_RATE_COLUMN_NAME:
                $modelName = 'SubPackagePostContractBillItemRate';
                break;

            case SubPackagePostContractBillItemRate::PRELIM_INITIAL_CLAIM_COLUMN_NAME:
                $modelName            = 'SubPackagePreliminariesInitialClaim';
                $claimComparisonModel = 'SubPackagePreliminariesFinalClaim';
                break;

            case SubPackagePostContractBillItemRate::PRELIM_FINAL_CLAIM_COLUMN_NAME:
                $modelName            = 'SubPackagePreliminariesFinalClaim';
                $claimComparisonModel = 'SubPackagePreliminariesInitialClaim';
                break;

            case PreliminariesIncludeInitial::COLUMN_NAME:
                $modelName = 'SubPackagePreliminariesIncludeInitial';
                $type      = 'include';
                break;

            case PreliminariesIncludeFinal::COLUMN_NAME:
                $modelName = 'SubPackagePreliminariesIncludeFinal';
                $type      = 'include';
                break;

            default:
                throw new Exception('Invalid column type !');
        }

        if ( !isset ( $type ) OR $type != 'include' )
        {
            if ( $explodedColumnType[0] != SubPackagePostContractBillItemRate::PRELIM_RATE_COLUMN_NAME AND $explodedColumnType[1] != SubPackagePostContractBillItemRate::PRELIM_CLAIM_PERCENTAGE_FIELD_EXT_NAME AND $explodedColumnType[1] != SubPackagePostContractBillItemRate::PRELIM_CLAIM_AMOUNT_FIELD_EXT_NAME )
            {
                throw new Exception('Invalid column type !');
            }
        }

        try
        {
            $value = $request->getParameter('val');

            if ( $explodedColumnType[0] == SubPackagePostContractBillItemRate::PRELIM_RATE_COLUMN_NAME )
            {
                $subPackagePostContractItemRate->rate = $value;
                $subPackagePostContractItemRate->save();

                $subPackagePostContractItemRate->refresh(true);
            }

            $recurringTotal = $subPackagePostContractItemRate->single_unit_grand_total;

            if ( $explodedColumnType[0] != SubPackagePostContractBillItemRate::PRELIM_RATE_COLUMN_NAME )
            {
                // will be creating new record for item claim, if not available
                // else just update and return newly calculated value so that dojo's store can do something with it
                $claimRecord = Doctrine_Core::getTable($modelName)->findOneBy('sub_package_post_contract_bill_item_rate_id', $subPackagePostContractItemRate->id);

                if ( !isset ( $type ) OR $type != 'include' )
                {
                    $claimRecord = ( $claimRecord ) ? $claimRecord : new $modelName;

                    $claimComparison = Doctrine_Core::getTable($claimComparisonModel)->findOneBy('sub_package_post_contract_bill_item_rate_id', $subPackagePostContractItemRate->id);

                    // then deduct with initial
                    if ( $explodedColumnType[1] == SubPackagePostContractBillItemRate::PRELIM_CLAIM_AMOUNT_FIELD_EXT_NAME )
                    {
                        $claimAmount = Utilities::prelimRounding((float) $value);

                        if ( $claimComparison AND ( ( $claimComparison->amount + $claimAmount ) > $recurringTotal ) )
                        {
                            $claimAmount = $recurringTotal - $claimComparison->amount;
                        }

                        // get percentage of the new claim amount
                        $claimPercentage = 100 - Utilities::percent($recurringTotal - $claimAmount, $recurringTotal);
                    }
                    else
                    {
                        $claimPercentage = Utilities::prelimRounding((float) $value);

                        if ( $claimComparison AND ( ( $claimComparison->percentage + $claimPercentage ) > 100 ) )
                        {
                            $claimPercentage = 100 - $claimComparison->percentage;
                        }

                        $claimAmount = ( $claimPercentage / 100 ) * $recurringTotal;
                    }

                    if ( $claimRecord->isNew() )
                    {
                        $claimRecord->sub_package_post_contract_bill_item_rate_id = $subPackagePostContractItemRate->id;
                        $claimRecord->revision_id                                 = (int) $request->getPostParameter('revision_id');
                    }

                    $claimRecord->percentage = $claimPercentage;
                    $claimRecord->amount     = $claimAmount;
                    $claimRecord->save();
                }
                else
                {
                    if ( $value == $modelName::YES )
                    {
                        $claimRecord = ( $claimRecord ) ? $claimRecord : new $modelName;

                        if ( $claimRecord->isNew() )
                        {
                            $claimRecord->sub_package_post_contract_bill_item_rate_id = $subPackagePostContractItemRate->id;
                            $claimRecord->include_at_revision_id                      = (int) $request->getPostParameter('revision_id');
                        }

                        $claimRecord->save();
                    }
                    else if ( $claimRecord )
                    {
                        $claimRecord->delete();
                    }
                }
            }

            $item    = SubPackagePostContractBillItemRateTable::getPrelimItemClaimCosting($subPackagePostContractItemRate, $subPackage, $recurringTotal);
            $success = true;
        }
        catch (Exception $e)
        {
            $success = false;
            $item    = array();
        }

        return $this->renderJson(array( 'success' => $success, 'item' => $item ));
    }

    public function executeGetTimeBasedInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id')) and
            $subPackagePostContractItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('id'))
        );

        $claimViewSelectedProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $searchFields                     = 'sub_package_post_contract_bill_item_rate_idAndrevision_id';
        $searchValue                      = array( $subPackagePostContractItemRate->id, $claimViewSelectedProjectRevision['id'] );

        $record = Doctrine_Core::getTable('SubPackagePreliminariesTimeBasedClaim')->findOneBy($searchFields, $searchValue);
        $record = ( $record ) ? $record : new SubPackagePreliminariesTimeBasedClaim();

        $form = new SubPackagePreliminariesTimeBasedClaimForm($record);

        $data['form'] = array(
            'sub_package_preliminaries_time_based_claim[sub_package_post_contract_bill_item_rate_id]' => $subPackagePostContractItemRate->id,
            'sub_package_preliminaries_time_based_claim[up_to_date_duration]'                         => Utilities::prelimRounding($form->getObject()->up_to_date_duration),
            'sub_package_preliminaries_time_based_claim[total_project_duration]'                      => Utilities::prelimRounding($form->getObject()->total_project_duration),
            'sub_package_preliminaries_time_based_claim[_csrf_token]'                                 => $form->getCSRFToken(),
            'total'                                                                                   => Utilities::prelimRounding($form->getObject()->total * 100),
        );

        return $this->renderJson($data);
    }

    public function executeUpdatedTimeBasedInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id')) and
            $subPackagePostContractItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('id'))
        );

        $claimViewSelectedProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $searchFields                     = 'sub_package_post_contract_bill_item_rate_idAndrevision_id';
        $searchValue                      = array( $subPackagePostContractItemRate->id, $claimViewSelectedProjectRevision['id'] );

        $record = Doctrine_Core::getTable('SubPackagePreliminariesTimeBasedClaim')->findOneBy($searchFields, $searchValue);
        $record = ( $record ) ? $record : new SubPackagePreliminariesTimeBasedClaim();

        $form = new SubPackagePreliminariesTimeBasedClaimForm($record);

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $success = true;
            $errors  = array();

            $recurringTotal = $subPackagePostContractItemRate->single_unit_grand_total;

            $item = SubPackagePostContractBillItemRateTable::getPrelimItemClaimCosting($subPackagePostContractItemRate, $subPackage, $recurringTotal);
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
            $item    = array();
        }

        return $this->renderJson(array( 'success' => $success, 'item' => $item, 'errorMsgs' => $errors ));
    }

    public function executeGetWorkBasedInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id')) and
            $subPackagePostContractItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('id'))
        );

        $claimViewSelectedProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $searchFields                     = 'sub_package_post_contract_bill_item_rate_idAndrevision_id';
        $searchValue                      = array( $subPackagePostContractItemRate->id, $claimViewSelectedProjectRevision['id'] );

        $record = Doctrine_Core::getTable('SubPackagePreliminariesWorkBasedClaim')->findOneBy($searchFields, $searchValue);
        $record = ( $record ) ? $record : new SubPackagePreliminariesWorkBasedClaim();

        $form = new SubPackagePreliminariesWorkBasedClaimForm($record);

        $data['form'] = array(
            'sub_package_preliminaries_work_based_claim[sub_package_post_contract_bill_item_rate_id]' => $subPackagePostContractItemRate->id,
            'sub_package_preliminaries_work_based_claim[builders_work_done]'                          => Utilities::prelimRounding($form->getObject()->builders_work_done),
            'sub_package_preliminaries_work_based_claim[total_builders_work]'                         => Utilities::prelimRounding($form->getObject()->total_builders_work),
            'sub_package_preliminaries_work_based_claim[_csrf_token]'                                 => $form->getCSRFToken(),
            'total'                                                                                   => Utilities::prelimRounding($form->getObject()->total * 100),
        );

        return $this->renderJson($data);
    }

    public function executeUpdatedWorkBasedInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id')) and
            $subPackagePostContractItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('id'))
        );

        $claimViewSelectedProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
        $searchFields                     = 'sub_package_post_contract_bill_item_rate_idAndrevision_id';
        $searchValue                      = array( $subPackagePostContractItemRate->id, $claimViewSelectedProjectRevision['id'] );

        $record = Doctrine_Core::getTable('SubPackagePreliminariesWorkBasedClaim')->findOneBy($searchFields, $searchValue);
        $record = ( $record ) ? $record : new SubPackagePreliminariesWorkBasedClaim();

        $form = new SubPackagePreliminariesWorkBasedClaimForm($record);

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $success = true;
            $errors  = array();

            $recurringTotal = $subPackagePostContractItemRate->single_unit_grand_total;

            $item = SubPackagePostContractBillItemRateTable::getPrelimItemClaimCosting($subPackagePostContractItemRate, $subPackage, $recurringTotal);
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
            $item    = array();
        }

        return $this->renderJson(array( 'success' => $success, 'item' => $item, 'errorMsgs' => $errors ));
    }

}