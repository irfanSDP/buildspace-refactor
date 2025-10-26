<?php

/**
 * postContractPreliminaries actions.
 *
 * @package    buildspace
 * @subpackage postContractPreliminaries
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractPreliminariesActions extends BaseActions {

	public function executeGetElementList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$elements = DoctrineQuery::create()
			->select('e.id, e.description, e.note, fc.column_name, fc.value, fc.final_value')
			->from('BillElement e')
			->leftJoin('e.FormulatedColumns fc')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		$form              = new BaseForm();
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

		$claimProjectRevision         = PostContractClaimRevisionTable::getCurrentProjectRevision($postContract);
		$selectedClaimProjectRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract, false);

		$importedClaimAmounts = PostContractImportedPreliminaryClaimTable::getImportedItemsClaimAmounts($selectedClaimProjectRevision->id);

		$variationOrderOmittedBillItems = VariationOrderItemTable::getNumberOfOmittedBillItems($bill->getRoot(), $bill, 1);

		// Get Preliminaries Costing
		list(
			$elementBillItems, $initialCostings, $timeBasedCostings, $prevTimeBasedCostings, $workBasedCostings, $prevWorkBasedCostings, $finalCostings, $includeInitialCostings, $includeFinalCostings
			) = PostContractBillItemRateTable::getPrelimElementClaimCosting($selectedClaimProjectRevision, $elements);

		foreach ( $elements as $key => $element )
		{
			$elements[$key]['markup_rounding_type'] = $markupSettingsInfo['rounding_type'];

			$elementGrandTotals = 0;

			unset( $elements[$key]['FormulatedColumns'] );

			$elements[$key]['has_note']                   = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
			$elements[$key]['note']                       = (string) $element['note'];
			$elements[$key]['relation_id']                = $bill->id;
			$elements[$key]['vo_omitted_items']           = $variationOrderOmittedBillItems[$element['id']] ?? "";
			$elements[$key]['_csrf_token']                = $form->getCSRFToken();
			$elements[$key]['previousClaim-amount']       = 0;
			$elements[$key]['currentClaim-amount']        = 0;
			$elements[$key]['upToDateClaim-amount']       = 0;
			$elements[$key]['grand_total']                = 0;
			$elements[$key]['previousClaim-percentage']   = 0;
			$elements[$key]['currentClaim-percentage']    = 0;
			$elements[$key]['upToDateClaim-percentage']   = 0;
			$elements[$key]['imported_up_to_date_amount'] = 0;

			if ( !isset ( $elementBillItems[$element['id']] ) )
			{
				continue;
			}

			foreach ( $elementBillItems[$element['id']] as $billItem )
			{
				$elementGrandTotals += $billItem['item_total'];

				PreliminariesClaimTable::calculateClaimRates($selectedClaimProjectRevision->toArray(), $billItem, $claimProjectRevision, $initialCostings, $finalCostings, $timeBasedCostings, $workBasedCostings, $prevTimeBasedCostings, $prevWorkBasedCostings, $includeInitialCostings, $includeFinalCostings);

				$elements[$key]['previousClaim-amount'] += $billItem['previousClaim-amount'];
				$elements[$key]['currentClaim-amount'] += $billItem['currentClaim-amount'];
				$elements[$key]['upToDateClaim-amount'] += $billItem['upToDateClaim-amount'];
				$elements[$key]['imported_up_to_date_amount'] += $importedClaimAmounts[$billItem['id']] ?? 0;

				unset( $billItem );
			}

			$elements[$key]['grand_total']              = $elementGrandTotals;
			$elements[$key]['previousClaim-percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$key]['previousClaim-amount'], $elementGrandTotals));
			$elements[$key]['currentClaim-percentage']  = Utilities::prelimRounding(Utilities::percent($elements[$key]['currentClaim-amount'], $elementGrandTotals));
			$elements[$key]['upToDateClaim-percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$key]['upToDateClaim-amount'], $elementGrandTotals));
		}

		$defaultLastRow = array(
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
		);

		array_push($elements, $defaultLastRow);

		$data = array(
			'identifier' => 'id',
			'items'      => $elements
		);

		return $this->renderJson($data);
	}

	public function executeGetItemList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$form                         = new BaseForm();
		$items                        = array();
		$pageNoPrefix                 = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType                 = $bill->BillMarkupSetting->rounding_type;
		$claimProjectRevision         = PostContractClaimRevisionTable::getCurrentProjectRevision($postContract);
		$selectedClaimProjectRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);
		$column                       = $bill->BillColumnSettings->toArray();

		list(
			$billItems, $billItemTypeReferences, $billItemTypeRefFormulatedColumns, $initialCostings, $timeBasedCostings, $prevTimeBasedCostings, $workBasedCostings, $prevWorkBasedCostings, $finalCostings, $includeInitialCostings, $includeFinalCostings
			) = PostContractBillItemRateTable::getDataStructureForPrelimBillItemList($postContract, $element, $bill);

		$importedClaimAmounts = PostContractImportedPreliminaryClaimTable::getImportedItemsClaimAmounts($selectedClaimProjectRevision['id']);

		$omittedAtVariationOrders = array();

		foreach(BillItemTable::getOmittedAtVariationOrders(Utilities::arrayValueRecursive('id', $billItems), 1) as $record)
		{
			$omittedAtVariationOrders[$record['bill_item_id']] = $record['description'];
		}

		foreach ( $billItems as $billItem )
		{
			$itemTotal                        = $billItem['rate'] * $billItem['qty'];
			$billItem['bill_ref']             = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
			$billItem['type']                 = (string) $billItem['type'];
			$billItem['uom_id']               = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
			$billItem['relation_id']          = $element->id;
			$billItem['linked']               = false;
			$billItem['markup_rounding_type'] = $roundingType;
			$billItem['_csrf_token']          = $form->getCSRFToken();
			$billItem['has_note']             = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
			$billItem['item_total']           = Utilities::prelimRounding($itemTotal);
			$billItem['claim_at_revision_id'] = ( !empty( $billItem['claim_at_revision_id'] ) ) ? $billItem['claim_at_revision_id'] : $claimProjectRevision['id'];
			$billItem['omitted_at_vo']        = $omittedAtVariationOrders[$billItem['id']] ?? "";

			$billItem['rate']             = Utilities::prelimRounding($billItem['rate']);
			$billItem['qty-qty_per_unit'] = $billItem['qty'];
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

						unset( $billItemTypeRefFormulatedColumn );
					}
				}

				unset( $billItemTypeRef );
			}

			PreliminariesClaimTable::calculateClaimRates($selectedClaimProjectRevision, $billItem, $claimProjectRevision, $initialCostings, $finalCostings, $timeBasedCostings, $workBasedCostings, $prevTimeBasedCostings, $prevWorkBasedCostings, $includeInitialCostings, $includeFinalCostings);

			$billItem['imported_up_to_date_amount'] = $importedClaimAmounts[ $billItem['id'] ] ?? 0;

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
			'qty-qty_per_unit'         => '',
			'grand_total'              => '',
			'linked'                   => false,
			'rate_after_markup'        => 0,
			'grand_total_after_markup' => 0,
			'markup_rounding_type'     => $roundingType,
			'include_initial'          => 'false',
			'include_final'            => 'false',
			'_csrf_token'              => $form->getCSRFToken()
		);

		array_push($items, $defaultLastRow);

		$data = array(
			'identifier' => 'id',
			'items'      => $items
		);

		return $this->renderJson($data);
	}

	public function executeUpdateItemClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id) and
			$postContractItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getPostParameter('id'))
		);

		$explodedColumnType = explode('-', $request->getParameter('attr_name'));

		switch ($explodedColumnType[0])
		{
			case PostContractBillItemRate::PRELIM_RATE_COLUMN_NAME:
				$modelName = 'PostContractBillItemRate';
				break;

			case PostContractBillItemRate::PRELIM_INITIAL_CLAIM_COLUMN_NAME:
				$modelName            = 'PreliminariesInitialClaim';
				$claimComparisonModel = 'PreliminariesFinalClaim';
				break;

			case PostContractBillItemRate::PRELIM_FINAL_CLAIM_COLUMN_NAME:
				$modelName            = 'PreliminariesFinalClaim';
				$claimComparisonModel = 'PreliminariesInitialClaim';
				break;

			case PreliminariesIncludeInitial::COLUMN_NAME:
				$modelName = 'PreliminariesIncludeInitial';
				$type      = 'include';
				break;

			case PreliminariesIncludeFinal::COLUMN_NAME:
				$modelName = 'PreliminariesIncludeFinal';
				$type      = 'include';
				break;

			default:
				throw new Exception('Invalid column type !');
		}

		if ( !isset ( $type ) OR $type != 'include' )
		{
			if ( $explodedColumnType[0] != PostContractBillItemRate::PRELIM_RATE_COLUMN_NAME AND $explodedColumnType[1] != PostContractBillItemRate::PRELIM_CLAIM_PERCENTAGE_FIELD_EXT_NAME AND $explodedColumnType[1] != PostContractBillItemRate::PRELIM_CLAIM_AMOUNT_FIELD_EXT_NAME )
			{
				throw new Exception('Invalid column type !');
			}
		}

		try
		{
			$value = $request->getParameter('val');

			if ( $explodedColumnType[0] == PostContractBillItemRate::PRELIM_RATE_COLUMN_NAME )
			{
				$postContractItemRate->rate = $value;
				$postContractItemRate->save();

				$postContractItemRate->refresh(true);
			}

			// get item's recurring total
			$qty            = $postContractItemRate->BillItem->grand_total_quantity;
			$recurringTotal = $qty * $postContractItemRate->rate;

			if ( $explodedColumnType[0] != PostContractBillItemRate::PRELIM_RATE_COLUMN_NAME )
			{
				// will be creating new record for item claim, if not available
				// else just update and return newly calculated value so that dojo's store can do something with it
				$claimRecord = Doctrine_Core::getTable($modelName)->findOneBy('post_contract_bill_item_rate_id', $postContractItemRate->id);

				if ( !isset ( $type ) OR $type != 'include' )
				{
					$claimRecord = ( $claimRecord ) ? $claimRecord : new $modelName;

					$claimComparison = Doctrine_Core::getTable($claimComparisonModel)->findOneBy('post_contract_bill_item_rate_id', $postContractItemRate->id);

					// then deduct with initial
					if ( $explodedColumnType[1] == PostContractBillItemRate::PRELIM_CLAIM_AMOUNT_FIELD_EXT_NAME )
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
						$claimRecord->post_contract_bill_item_rate_id = $postContractItemRate->id;
						$claimRecord->revision_id                     = (int) $request->getPostParameter('revision_id');
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
							$claimRecord->post_contract_bill_item_rate_id = $postContractItemRate->id;
							$claimRecord->include_at_revision_id          = (int) $request->getPostParameter('revision_id');
						}

						$claimRecord->save();
					}
					else if ( $claimRecord )
					{
						$claimRecord->delete();
					}
				}
			}

			$item    = PostContractBillItemRateTable::getPrelimItemClaimCosting($postContractItemRate, $postContract, $recurringTotal);

			$openClaimRevision = $postContract->getOpenClaimRevision();

			if($openClaimRevision->ClaimCertificate->id)
			{
				$openClaimRevision->ClaimCertificate->save();
			}

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
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id) and
			$postContractItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('id'))
		);

		$claimViewSelectedProjectRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);
		$searchFields                     = 'post_contract_bill_item_rate_idAndrevision_id';
		$searchValue                      = array( $postContractItemRate->id, $claimViewSelectedProjectRevision['id'] );

		$record = Doctrine_Core::getTable('PreliminariesTimeBasedClaim')->findOneBy($searchFields, $searchValue);
		$record = ( $record ) ? $record : new PreliminariesTimeBasedClaim();

		$form = new PreliminariesTimeBasedClaimForm($record);

		$data['form'] = array(
			'preliminaries_time_based_claim[post_contract_bill_item_rate_id]' => $postContractItemRate->id,
			'preliminaries_time_based_claim[up_to_date_duration]'             => Utilities::prelimRounding($form->getObject()->up_to_date_duration),
			'preliminaries_time_based_claim[total_project_duration]'          => Utilities::prelimRounding($form->getObject()->total_project_duration),
			'preliminaries_time_based_claim[_csrf_token]'                     => $form->getCSRFToken(),
			'total'                                                           => Utilities::prelimRounding($form->getObject()->total * 100),
		);

		return $this->renderJson($data);
	}

	public function executeUpdatedTimeBasedInformation(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id) and
			$postContractItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('id'))
		);

		$claimViewSelectedProjectRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);
		$searchFields                     = 'post_contract_bill_item_rate_idAndrevision_id';
		$searchValue                      = array( $postContractItemRate->id, $claimViewSelectedProjectRevision['id'] );

		$record = Doctrine_Core::getTable('PreliminariesTimeBasedClaim')->findOneBy($searchFields, $searchValue);
		$record = ( $record ) ? $record : new PreliminariesTimeBasedClaim();

		$form = new PreliminariesTimeBasedClaimForm($record);

		if ( $this->isFormValid($request, $form) )
		{
			$form->save();

			$success = true;
			$errors  = array();

			// get item's recurring total
			$qty            = $postContractItemRate->BillItem->grand_total_quantity;
			$recurringTotal = $qty * $postContractItemRate->rate;

			$item = PostContractBillItemRateTable::getPrelimItemClaimCosting($postContractItemRate, $postContract, $recurringTotal);

			$openClaimRevision = $postContract->getOpenClaimRevision();

			if($openClaimRevision->ClaimCertificate->id)
			{
				$openClaimRevision->ClaimCertificate->save();
			}
		}
		else
		{
			$errors  = $form->getErrors();
			$success = false;
			$item    = array();
		}

		$data = array( 'success' => $success, 'item' => $item, 'errorMsgs' => $errors );

		return $this->renderJson($data);
	}

	public function executeGetWorkBasedInformation(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id) and
			$postContractItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('id'))
		);

		$claimViewSelectedProjectRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);
		$searchFields                     = 'post_contract_bill_item_rate_idAndrevision_id';
		$searchValue                      = array( $postContractItemRate->id, $claimViewSelectedProjectRevision['id'] );

		$record = Doctrine_Core::getTable('PreliminariesWorkBasedClaim')->findOneBy($searchFields, $searchValue);
		$record = ( $record ) ? $record : new PreliminariesWorkBasedClaim();

		$form = new PreliminariesWorkBasedClaimForm($record);

		$data['form'] = array(
			'preliminaries_work_based_claim[post_contract_bill_item_rate_id]' => $postContractItemRate->id,
			'preliminaries_work_based_claim[builders_work_done]'              => Utilities::prelimRounding($form->getObject()->builders_work_done),
			'preliminaries_work_based_claim[total_builders_work]'             => Utilities::prelimRounding($form->getObject()->total_builders_work),
			'preliminaries_work_based_claim[_csrf_token]'                     => $form->getCSRFToken(),
			'total'                                                           => Utilities::prelimRounding($form->getObject()->total * 100),
		);

		return $this->renderJson($data);
	}

	public function executeUpdatedWorkBasedInformation(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id) and
			$postContractItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('id'))
		);

		$claimViewSelectedProjectRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);
		$searchFields                     = 'post_contract_bill_item_rate_idAndrevision_id';
		$searchValue                      = array( $postContractItemRate->id, $claimViewSelectedProjectRevision['id'] );

		$record = Doctrine_Core::getTable('PreliminariesWorkBasedClaim')->findOneBy($searchFields, $searchValue);
		$record = ( $record ) ? $record : new PreliminariesWorkBasedClaim();

		$form = new PreliminariesWorkBasedClaimForm($record);

		if ( $this->isFormValid($request, $form) )
		{
			$form->save();

			$success = true;
			$errors  = array();

			// get item's recurring total
			$qty            = $postContractItemRate->BillItem->grand_total_quantity;
			$recurringTotal = $qty * $postContractItemRate->rate;

			$item = PostContractBillItemRateTable::getPrelimItemClaimCosting($postContractItemRate, $postContract, $recurringTotal);

			$openClaimRevision = $postContract->getOpenClaimRevision();

			if($openClaimRevision->ClaimCertificate->id)
			{
				$openClaimRevision->ClaimCertificate->save();
			}
		}
		else
		{
			$errors  = $form->getErrors();
			$success = false;
			$item    = array();
		}

		$data = array( 'success' => $success, 'item' => $item, 'errorMsgs' => $errors );

		return $this->renderJson($data);
	}

	public function executeLumpSumPercentageForm(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$billItem = DoctrineQuery::create()
			->select('i.id, r.rate, ls.percentage')
			->from('BillItem i')
			->leftJoin('i.PostContractRates r')
			->leftJoin('i.LumpSumPercentage ls')
			->where('i.id = ?', $request->getParameter('id'))
			->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
			->fetchOne();

		$form = new BaseForm();

		$data = array(
			'bill_item_lump_sum_percentage[rate]'        => Utilities::prelimRounding($billItem['PostContractRates'][0]['rate'] / ( $billItem['LumpSumPercentage']['percentage'] / 100 )),
			'bill_item_lump_sum_percentage[percentage]'  => Utilities::prelimRounding($billItem['LumpSumPercentage']['percentage']),
			'bill_item_lump_sum_percentage[amount]'      => Utilities::prelimRounding($billItem['PostContractRates'][0]['rate']),
			'bill_item_lump_sum_percentage[_csrf_token]' => $form->getCSRFToken()
		);

		return $this->renderJson($data);
	}

	public function executeLumpSumPercentageUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
		);

		$errorMsg = null;
		$item     = array();

		try
		{
			$postContractItemRate = DoctrineQuery::create()->select('*')
				->from('PostContractBillItemRate c')
				->where('c.bill_item_id = ?', $billItem->id)
				->fetchOne();

			$postContractItemRate->setRate($request->getParameter('rate'));
			$postContractItemRate->save();

			$postContractItemRate->refresh(true);

			// get item's recurring total
			$recurringTotal = $postContractItemRate->BillItem->grand_total_quantity * $postContractItemRate->rate;

			$item    = PostContractBillItemRateTable::getPrelimItemClaimCosting($postContractItemRate, $postContractItemRate->PostContract, $recurringTotal);
			$success = true;
		}
		catch (Exception $e)
		{
			$errorMsg = $e->getMessage();
			$success  = false;
		}

		$data = array( 'success' => $success, 'errors' => $errorMsg, 'data' => $item );

		return $this->renderJson($data);
	}

	// =========================================================================================================================================
	// Check Box Selection
	// =========================================================================================================================================
	public function executeGetAffectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$elementIds = $request->getParameter('element_ids');

		$data = BillElementTable::getAffectedItemsAndBillsByElementId($bill, $elementIds);

		return $this->renderJson($data);
	}

	public function executeGetAffectedElements(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$itemIds = $request->getParameter('itemIds');

		$data = BillElementTable::getAffectedElementsAndBillsByItemIds($itemIds);

		return $this->renderJson($data);
	}
	// =========================================================================================================================================

	// =========================================================================================================================================
	// Printing Preview
	// =========================================================================================================================================
	public function executeGetPrintingSelectedItem(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$itemIds                      = $request->getParameter('itemIds');
		$items                        = array();
		$pageNoPrefix                 = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType                 = $bill->BillMarkupSetting->rounding_type;
		$column                       = $bill->BillColumnSettings->toArray();
		$claimProjectRevision         = PostContractClaimRevisionTable::getCurrentProjectRevision($postContract);
		$selectedClaimProjectRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);
		$elements                     = BillElementTable::getAffectedElementIdsByItemIds($itemIds);

		foreach ( $elements as $elementId => $element )
		{
			$generatedHeader = false;

			$fakeObjectElement     = new BillElement();
			$fakeObjectElement->id = $elementId;

			list(
				$billItems, $billItemTypeReferences, $billItemTypeRefFormulatedColumns, $initialCostings, $timeBasedCostings, $prevTimeBasedCostings, $workBasedCostings, $prevWorkBasedCostings, $finalCostings, $includeInitialCostings, $includeFinalCostings
				) = PostContractBillItemRateTable::getPrintingPreviewDataStructureForPrelimBillItemList($postContract, $fakeObjectElement, $bill, $itemIds);

			unset( $fakeObjectElement );

			foreach ( $billItems as $billItem )
			{
				if ( !$generatedHeader )
				{
					$items[] = array(
						'id'          => 'element-' . $elementId,
						'bill_ref'    => null,
						'description' => $element['description'],
						'type'        => 0,
					);

					$generatedHeader = true;
				}

				$billItem['item_total'] = $billItem['rate'] * $billItem['qty'];

				$billItem['bill_ref']             = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                 = (string) $billItem['type'];
				$billItem['uom_id']               = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['relation_id']          = $elementId;
				$billItem['linked']               = false;
				$billItem['markup_rounding_type'] = $roundingType;
				$billItem['has_note']             = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
				$billItem['item_total']           = Utilities::prelimRounding($billItem['item_total']);
				$billItem['claim_at_revision_id'] = ( !empty( $billItem['claim_at_revision_id'] ) ) ? $billItem['claim_at_revision_id'] : $claimProjectRevision['id'];

				$billItem['rate']             = Utilities::prelimRounding($billItem['rate']);
				$billItem['qty-qty_per_unit'] = $billItem['qty'];
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

							unset( $billItemTypeRefFormulatedColumn );
						}
					}

					unset( $billItemTypeRef );
				}

				PreliminariesClaimTable::calculateClaimRates($selectedClaimProjectRevision, $billItem, $claimProjectRevision, $initialCostings, $finalCostings, $timeBasedCostings, $workBasedCostings, $prevTimeBasedCostings, $prevWorkBasedCostings, $includeInitialCostings, $includeFinalCostings);

				if ( isset( $billItem['id'] ) )
				{
					$items[] = $billItem;
				}

				unset( $billItem );
			}

			unset( $billItems );
		}

		$defaultLastRow = array(
			'id'                       => Constants::GRID_LAST_ROW,
			'bill_ref'                 => '',
			'description'              => '',
			'note'                     => '',
			'has_note'                 => false,
			'type'                     => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                   => '-1',
			'uom_symbol'               => '',
			'level'                    => 0,
			'qty-qty_per_unit'         => '',
			'grand_total'              => '',
			'linked'                   => false,
			'rate_after_markup'        => 0,
			'grand_total_after_markup' => 0,
			'markup_rounding_type'     => $roundingType,
			'include_initial'          => 'false',
			'include_final'            => 'false',
		);

		array_push($items, $defaultLastRow);

		$data = array(
			'identifier' => 'id',
			'items'      => $items
		);

		return $this->renderJson($data);
	}

	public function executeGetPrintingItemWithCurrentClaimMoreThanZero(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$items                = array();
		$pageNoPrefix         = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType         = $bill->BillMarkupSetting->rounding_type;
		$column               = $bill->BillColumnSettings->toArray();
		$claimProjectRevision = PostContractClaimRevisionTable::getCurrentProjectRevision($postContract);

		// get available bill element(s)
		$elements = Doctrine_Query::create()
			->select('e.id, e.description')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->orderBy('e.priority ASC')
			->fetchArray();

		foreach ( $elements as $element )
		{
			$addElementTopHeader = false;
			$elementId           = $element['id'];

			$fakeObjectElement     = new BillElement();
			$fakeObjectElement->id = $elementId;

			list(
				$billItems, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
				) = PostContractBillItemRateTable::getPrintingPreviewDataStructureForPrelimBillItemListByClaimType($postContract, $fakeObjectElement, $bill, 'currentClaim-amount');

			unset( $fakeObjectElement );

			foreach ( $billItems as $billItem )
			{
				if ( !$addElementTopHeader )
				{
					$items[] = array(
						'id'          => 'element-' . $elementId,
						'bill_ref'    => null,
						'description' => $element['description'],
						'type'        => 0,
					);

					$addElementTopHeader = true;
				}

				$billItem['bill_ref']             = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                 = (string) $billItem['type'];
				$billItem['uom_id']               = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['relation_id']          = $elementId;
				$billItem['linked']               = false;
				$billItem['markup_rounding_type'] = $roundingType;
				$billItem['has_note']             = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
				$billItem['claim_at_revision_id'] = ( !empty( $billItem['claim_at_revision_id'] ) ) ? $billItem['claim_at_revision_id'] : $claimProjectRevision['id'];

				$billItem['rate']             = Utilities::prelimRounding($billItem['rate']);
				$billItem['qty-qty_per_unit'] = $billItem['qty'];
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

							unset( $billItemTypeRefFormulatedColumn );
						}
					}

					unset( $billItemTypeRef );
				}

				array_push($items, $billItem);

				unset( $billItem );
			}

			unset( $billItems );
		}

		$defaultLastRow = array(
			'id'                       => Constants::GRID_LAST_ROW,
			'bill_ref'                 => '',
			'description'              => '',
			'note'                     => '',
			'has_note'                 => false,
			'type'                     => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                   => '-1',
			'uom_symbol'               => '',
			'level'                    => 0,
			'qty-qty_per_unit'         => '',
			'grand_total'              => '',
			'linked'                   => false,
			'rate_after_markup'        => 0,
			'grand_total_after_markup' => 0,
			'markup_rounding_type'     => $roundingType,
			'include_initial'          => 'false',
			'include_final'            => 'false',
		);

		array_push($items, $defaultLastRow);

		$data = array(
			'identifier' => 'id',
			'items'      => $items
		);

		return $this->renderJson($data);
	}

	public function executeGetPrintingAllItemClaims(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$items                = array();
		$pageNoPrefix         = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType         = $bill->BillMarkupSetting->rounding_type;
		$column               = $bill->BillColumnSettings->toArray();
		$claimProjectRevision = PostContractClaimRevisionTable::getCurrentProjectRevision($postContract);

		// get available bill element(s)
		$elements = Doctrine_Query::create()
			->select('e.id, e.description')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->orderBy('e.priority ASC')
			->fetchArray();

		foreach ( $elements as $element )
		{
			$addElementTopHeader = false;
			$elementId           = $element['id'];

			$fakeObjectElement     = new BillElement();
			$fakeObjectElement->id = $elementId;

			list(
				$billItems, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
				) = PostContractBillItemRateTable::getPrintingPreviewDataStructureForPrelimBillItemListByClaimType($postContract, $fakeObjectElement, $bill, 'upToDateClaim-amount');

			unset( $fakeObjectElement );

			foreach ( $billItems as $billItem )
			{
				if ( !$addElementTopHeader )
				{
					$items[] = array(
						'id'          => 'element-' . $elementId,
						'bill_ref'    => null,
						'description' => $element['description'],
						'type'        => 0,
					);

					$addElementTopHeader = true;
				}

				$billItem['bill_ref']             = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                 = (string) $billItem['type'];
				$billItem['uom_id']               = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['relation_id']          = $elementId;
				$billItem['linked']               = false;
				$billItem['markup_rounding_type'] = $roundingType;
				$billItem['has_note']             = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
				$billItem['claim_at_revision_id'] = ( !empty( $billItem['claim_at_revision_id'] ) ) ? $billItem['claim_at_revision_id'] : $claimProjectRevision['id'];

				$billItem['rate']             = Utilities::prelimRounding($billItem['rate']);
				$billItem['qty-qty_per_unit'] = $billItem['qty'];
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

							unset( $billItemTypeRefFormulatedColumn );
						}
					}

					unset( $billItemTypeRef );
				}

				array_push($items, $billItem);

				unset( $billItem );
			}

			unset( $billItems );
		}

		$defaultLastRow = array(
			'id'                       => Constants::GRID_LAST_ROW,
			'bill_ref'                 => '',
			'description'              => '',
			'note'                     => '',
			'has_note'                 => false,
			'type'                     => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                   => '-1',
			'uom_symbol'               => '',
			'level'                    => 0,
			'qty-qty_per_unit'         => '',
			'grand_total'              => '',
			'linked'                   => false,
			'rate_after_markup'        => 0,
			'grand_total_after_markup' => 0,
			'markup_rounding_type'     => $roundingType,
			'include_initial'          => 'false',
			'include_final'            => 'false',
		);

		array_push($items, $defaultLastRow);

		$data = array(
			'identifier' => 'id',
			'items'      => $items
		);

		return $this->renderJson($data);
	}

	// =========================================================================================================================================

}