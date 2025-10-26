<?php

/**
 * rationalizeRateReport actions.
 *
 * @package    buildspace
 * @subpackage rationalizeRateReport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class rationalizeRateReportActions extends BaseActions {

	public function executeGetAffectedElementsAndItems(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$billIds = $request->getParameter('bill_ids');

		return $this->renderJson(BillElementTable::getAffectedElementsAndItemsByBillId($billIds));
	}

	public function executeGetAffectedBillsAndItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$elementIds = $request->getParameter('element_ids');

		return $this->renderJson(BillElementTable::getAffectedItemsAndBillsByElementId($bill, $elementIds));
	}

	// for item's level
	public function executeGetAffectedBillsAndElements(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$itemIds = $request->getParameter('itemIds');

		return $this->renderJson(BillElementTable::getAffectedElementsAndBillsByItemIds($itemIds));
	}

	// ============================================================================================================================================
	// Printing Preview
	// ============================================================================================================================================
	public function executeGetPrintingSelectedBillByTenderer(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
		);

		$billIds = json_decode($request->getParameter('billIds'), true);
		$form    = new BaseForm();
		$records = array();

		if ( !empty( $billIds ) )
		{
			$records = DoctrineQuery::create()
				->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
				->from('ProjectStructure s')
				->leftJoin('s.BillType t')
				->leftJoin('s.BillColumnSettings c')
				->leftJoin('s.BillLayoutSetting bls')
				->whereIn('s.id', $billIds)
				->andWhere('s.root_id = ?', $structure->id)
				->andWhere('s.type <= ?', ProjectStructure::TYPE_BILL)
				->addOrderBy('s.lft ASC')
				->fetchArray();

			$count = 0;

			$projectSumTotal   = ProjectStructureTable::getOverallTotalForProject($structure->id);
			$rationalizedRates = TenderBillItemRationalizedRatesTable::getOverallBillTotalByProject($structure->id);
            $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($structure);

			foreach ( $records as $key => $record )
			{
				$count = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

				if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
				{
					$records[$key]['bill_type']                               = $record['BillType']['type'];
					$records[$key]['bill_status']                             = $record['BillType']['status'];
					$records[$key]['overall_total_after_markup']              = (array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;
					$records[$key]['rationalized_overall_total_after_markup'] = 0;
					$records[$key]['rationalized-difference_percentage']      = 0;

					if ( array_key_exists($record['id'], $rationalizedRates['bill']) )
					{
						$records[$key]['rationalized_overall_total_after_markup'] = $rationalizedRates['bill'][$record['id']];
					}

					$records[$key]['rationalized-difference_amount'] = $records[$key]['rationalized_overall_total_after_markup'] - $records[$key]['overall_total_after_markup'];

					if ( $records[$key]['rationalized_overall_total_after_markup'] != 0 )
					{
						$records[$key]['rationalized-difference_percentage'] = Utilities::prelimRounding(Utilities::percent($records[$key]['rationalized-difference_amount'], $records[$key]['overall_total_after_markup']));
					}
				}
				else if ( $record['type'] == ProjectStructure::TYPE_ROOT )
				{
					$records[$key]['overall_total_after_markup']              = $projectSumTotal;
					$records[$key]['rationalized_overall_total_after_markup'] = $rationalizedRates['project_total'];
				}

				$records[$key]['_csrf_token'] = $form->getCSRFToken();

				unset( $records[$key]['BillLayoutSetting'] );
				unset( $records[$key]['BillType'] );
				unset( $records[$key]['BillColumnSettings'] );
			}
		}

		$defaultLastRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => '',
			'_csrf_token' => $form->getCSRFToken()
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	public function executeGetPrintingSelectedElementByTenderer(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$elementIds                   = json_decode($request->getParameter('elementIds'), true);
		$elements                     = array();
		$form                         = new BaseForm();
		$data['identifier']           = 'id';
		$data['items']                = array();
		$totalRateByBillColumnSetting = array();

		if ( !empty( $elementIds ) )
		{
			$elements = DoctrineQuery::create()
				->select('e.id, e.description, fc.column_name, fc.value, fc.final_value')
				->from('BillElement e')
				->leftJoin('e.FormulatedColumns fc')
				->where('e.project_structure_id = ?', $bill->id)
				->andWhereIn('e.id', $elementIds)
				->addOrderBy('e.priority ASC')
				->fetchArray();

			$billMarkupSetting = $bill->BillMarkupSetting;
			$rationalizedRates = TenderBillItemRationalizedRatesTable::getRationalizedGrandTotalByBillId($bill->id);

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

			foreach ( $elements as $key => $element )
			{
				$elements[$key]['markup_rounding_type'] = $markupSettingsInfo['rounding_type'];
				$overallTotalAfterMarkup                = 0;

				foreach ( $bill->BillColumnSettings as $column )
				{
					$total = $totalRateByBillColumnSetting[$column->id][$element['id']][0]['total_rate_after_markup'];
					$overallTotalAfterMarkup += $total;

					unset( $column );
				}

				unset( $elements[$key]['FormulatedColumns'] );

				$elements[$key]['rationalized_overall_total_after_markup'] = 0;

				if ( array_key_exists($element['id'], $rationalizedRates['element']) )
				{
					$elements[$key]['rationalized_overall_total_after_markup'] = $rationalizedRates['element'][$element['id']];
				}

				$elements[$key]['grand_total'] = $overallTotalAfterMarkup;

				$elements[$key]['rationalized-difference_amount']     = $elements[$key]['rationalized_overall_total_after_markup'] - $overallTotalAfterMarkup;
				$elements[$key]['rationalized-difference_percentage'] = 0;

				if ( $elements[$key]['rationalized_overall_total_after_markup'] != 0 )
				{
					$elements[$key]['rationalized-difference_percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$key]['rationalized-difference_amount'], $overallTotalAfterMarkup));
				}

				$elements[$key]['_csrf_token'] = $form->getCSRFToken();
			}
		}

		$defaultLastRow = array(
			'id'                         => Constants::GRID_LAST_ROW,
			'description'                => '',
			'overall_total_after_markup' => 0,
			'relation_id'                => $bill->id,
			'_csrf_token'                => $form->getCSRFToken()
		);

		array_push($elements, $defaultLastRow);

		$data['items'] = $elements;

		return $this->renderJson($data);
	}

	public function executeGetPrintingSelectedItemRateByTenderer(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$itemIds      = $request->getParameter('itemIds');
		$pdo          = $bill->getTable()->getConnection()->getDbh();
		$form         = new BaseForm();
		$items        = array();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;

		$markupSettingsInfo = array(
			'bill_markup_enabled'    => $bill->BillMarkupSetting->bill_markup_enabled,
			'bill_markup_percentage' => $bill->BillMarkupSetting->bill_markup_percentage,
			'element_markup_enabled' => $bill->BillMarkupSetting->element_markup_enabled,
			'item_markup_enabled'    => $bill->BillMarkupSetting->item_markup_enabled,
			'rounding_type'          => $bill->BillMarkupSetting->rounding_type,
		);

		list(
			$elements, $elementWithBillItems, $formulatedColumns, $quantityPerUnitByColumns,
			$billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = BillItemTable::getPrintingPreviewDataStructureForBillItemList($itemIds, $bill);

		foreach ( $elementWithBillItems as $elementId => $billItems )
		{
			/*
			 * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
			 */
			if ( $bill->BillMarkupSetting->element_markup_enabled )
			{
				$stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value
					FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
					JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
					WHERE e.id = " . $elementId . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
					AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

				$stmt->execute();

				$elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
				$elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;

				$markupSettingsInfo['element_markup_percentage'] = $elementMarkupPercentage;
			}

			$rationalizedRates = TenderBillItemRationalizedRatesTable::getAllRationalizedRatesByElementId($elementId);

			//Get Rationalized BillItemNotListed
			$stmt = $pdo->prepare("SELECT r.bill_item_id, r.description, uom.id AS uom_id, uom.symbol AS uom_symbol
				FROM " . TenderBillItemNotListedRationalizedTable::getInstance()->getTableName() . " r
				LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON r.bill_item_id = i.id AND i.deleted_at IS NULL
				LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON r.uom_id = uom.id AND uom.deleted_at IS NULL
				LEFT JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id AND e.deleted_at IS NULL
				WHERE e.id = " . $elementId);

			$stmt->execute();

			$rationalizedItems = array_map('current', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC));

			//Get Rationalized BillItemNotListed
			$stmt = $pdo->prepare("SELECT r.bill_item_id, q.bill_column_setting_id, COALESCE(q.final_value,0) as value
				FROM " . TenderBillItemNotListedRationalizedQuantityTable::getInstance()->getTableName() . " q
				LEFT JOIN " . TenderBillItemNotListedRationalizedTable::getInstance()->getTableName() . " r ON r.id = q.tender_bill_not_listed_item_rationalized_id
				LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON r.bill_item_id = i.id AND i.deleted_at IS NULL
				LEFT JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id AND e.deleted_at IS NULL
				WHERE e.id = " . $elementId);

			$stmt->execute();

			$rationalizedQuantities = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

			if ( count($rationalizedQuantities) && count($rationalizedItems) )
			{
				foreach ( $rationalizedItems as $itemId => $item )
				{
					if ( array_key_exists($itemId, $rationalizedQuantities) )
					{
						foreach ( $rationalizedQuantities[$itemId] as $k => $qty )
						{
							$rationalizedItems[$itemId]['quantities'][$qty['bill_column_setting_id']] = $qty['value'];
						}
					}

					if ( array_key_exists($itemId, $rationalizedRates) )
					{
						$rationalizedItems[$itemId]['rate']        = $rationalizedRates[$itemId][0]['rate'];
						$rationalizedItems[$itemId]['grand_total'] = $rationalizedRates[$itemId][0]['grand_total'];
					}
				}
			}

			unset( $rationalizedQuantities );
			$rationalizedCount = 0;

			if ( isset ( $elements[$elementId] ) )
			{
				$items[] = array(
					'id'          => 'element-' . $elementId,
					'bill_ref'    => null,
					'description' => $elements[$elementId]['description'],
					'type'        => 0,
				);
			}

			foreach ( $billItems as $billItem )
			{
				$rate                  = 0;
				$rateAfterMarkup       = 0;
				$itemMarkupPercentage  = 0;
				$grandTotalAfterMarkup = 0;
				$rationalizedNotListed = false;
				$rationalizedCount ++;

				$billItem['bill_ref']                    = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                        = (string) $billItem['type'];
				$billItem['uom_id']                      = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['relation_id']                 = $elementId;
				$billItem['linked']                      = false;
				$billItem['_csrf_token']                 = $form->getCSRFToken();
				$billItem['project_revision_deleted_at'] = is_null($billItem['project_revision_deleted_at']) ? false : $billItem['project_revision_deleted_at'];

				if ( array_key_exists($billItem['id'], $formulatedColumns) )
				{
					$itemFormulatedColumns = $formulatedColumns[$billItem['id']];

					foreach ( $itemFormulatedColumns as $formulatedColumn )
					{
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

				$billItem['rate_after_markup']                     = $rateAfterMarkup;
				$billItem['rationalized_grand_total_after_markup'] = 0;
				$billItem['rationalized_rate-value']               = 0;

				foreach ( $bill->BillColumnSettings as $column )
				{
					$quantityPerUnit = 0;
					$total           = 0;

					if ( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($billItem['id'], $quantityPerUnitByColumns[$column->id]) )
					{
						$quantityPerUnit = $quantityPerUnitByColumns[$column->id][$billItem['id']][0];
						unset( $quantityPerUnitByColumns[$column->id][$billItem['id']] );
					}

					if ( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$column->id]) )
					{
						$totalPerUnit = $rateAfterMarkup * $quantityPerUnit;
						$total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

						unset( $billItemTypeReferences[$column->id][$billItem['id']] );
					}

					$grandTotalAfterMarkup += $total;
				}

				if ( array_key_exists($billItem['id'], $rationalizedRates) )
				{
					$billItem['rationalized_grand_total_after_markup'] = $rationalizedRates[$billItem['id']][0]['grand_total'];
					$billItem['rationalized_rate-value']               = $rationalizedRates[$billItem['id']][0]['rate'];
				}

				$billItem['rationalized_grand_total_quantity'] = $billItem['grand_total_quantity'];

				if ( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED )
				{
					$billItem['rationalized_grand_total_quantity']     = 0;
					$billItem['rationalized_rate-value']               = 0;
					$billItem['rationalized_grand_total_after_markup'] = 0;
					$billItem['rationalized_grand_total_quantity']     = 0;

					if ( count($rationalizedItems) && array_key_exists($billItem['id'], $rationalizedItems) )
					{
						$rationalizedNotListed = $billItem;

						$rationalizedNotListed['grand_total_after_markup'] = 0;
						$rationalizedNotListed['rate_after_markup']        = 0;
						$rationalizedNotListed['grand_total_quantity']     = 0;
						$rationalizedNotListed['rationalized_rate-value']  = 0;

						$totalQty = 0;

						foreach ( $bill->BillColumnSettings as $column )
						{
							if ( array_key_exists($column->id, $rationalizedItems[$billItem['id']]['quantities']) )
							{
								$quantityPerUnit = $rationalizedItems[$billItem['id']]['quantities'][$column->id];
							}
							else
							{
								$quantityPerUnit = 0;
							}

							$totalQty += $quantityPerUnit * $column['quantity'];
						}

						$rationalizedNotListed['rationalized_grand_total_quantity']     = $totalQty;
						$rationalizedNotListed['id']                                    = $billItem['id'] . '-' . $rationalizedCount;
						$rationalizedNotListed['description']                           = $rationalizedItems[$billItem['id']]['description'];
						$rationalizedNotListed['uom_id']                                = $rationalizedItems[$billItem['id']]['uom_id'];
						$rationalizedNotListed['uom_symbol']                            = $rationalizedItems[$billItem['id']]['uom_symbol'];
						$rationalizedNotListed['level']                                 = $rationalizedNotListed['level'] + 1;
						$rationalizedNotListed['rationalized_rate-value']               = $rationalizedItems[$billItem['id']]['rate'];
						$rationalizedNotListed['rationalized_grand_total_after_markup'] = $rationalizedItems[$billItem['id']]['grand_total'];
					}
				}

				$billItem['rationalized-difference_percentage'] = 0;
				$billItem['rationalized-difference_amount']     = $billItem['rationalized_rate-value'] - $billItem['rate_after_markup'];

				if ( $billItem['rationalized_rate-value'] != 0 )
				{
					$billItem['rationalized-difference_percentage'] = Utilities::prelimRounding(Utilities::percent($billItem['rationalized-difference_amount'], $billItem['rate_after_markup']));
				}

				array_push($items, $billItem);

				if ( $rationalizedNotListed )
				{
					array_push($items, $rationalizedNotListed);
				}

				unset( $billItem );
			}

			unset( $element );
		}

		unset( $elements );

		$defaultLastRow = array(
			'id'                => Constants::GRID_LAST_ROW,
			'bill_ref'          => '',
			'description'       => '',
			'type'              => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'            => '-1',
			'uom_symbol'        => '',
			'level'             => 0,
			'linked'            => false,
			'rate_after_markup' => 0,
			'grand_total'       => 0,
			'_csrf_token'       => $form->getCSRFToken()
		);

		array_push($items, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}

	public function executeGetPrintingSelectedItemTotalByTenderer(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$itemIds      = $request->getParameter('itemIds');
		$pdo          = $bill->getTable()->getConnection()->getDbh();
		$form         = new BaseForm();
		$items        = array();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;

		$markupSettingsInfo = array(
			'bill_markup_enabled'    => $bill->BillMarkupSetting->bill_markup_enabled,
			'bill_markup_percentage' => $bill->BillMarkupSetting->bill_markup_percentage,
			'element_markup_enabled' => $bill->BillMarkupSetting->element_markup_enabled,
			'item_markup_enabled'    => $bill->BillMarkupSetting->item_markup_enabled,
			'rounding_type'          => $bill->BillMarkupSetting->rounding_type,
		);

		list(
			$elements, $elementWithBillItems, $formulatedColumns, $quantityPerUnitByColumns,
			$billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = BillItemTable::getPrintingPreviewDataStructureForBillItemList($itemIds, $bill);

		foreach ( $elementWithBillItems as $elementId => $billItems )
		{
			/*
			 * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
			 */
			if ( $bill->BillMarkupSetting->element_markup_enabled )
			{
				$stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value
					FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
					JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
					WHERE e.id = " . $elementId . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
					AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

				$stmt->execute();

				$elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
				$elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;

				$markupSettingsInfo['element_markup_percentage'] = $elementMarkupPercentage;
			}

			$rationalizedRates = TenderBillItemRationalizedRatesTable::getAllRationalizedRatesByElementId($elementId);

			//Get Rationalized BillItemNotListed
			$stmt = $pdo->prepare("SELECT r.bill_item_id, r.description, uom.id AS uom_id, uom.symbol AS uom_symbol
				FROM " . TenderBillItemNotListedRationalizedTable::getInstance()->getTableName() . " r
				LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON r.bill_item_id = i.id AND i.deleted_at IS NULL
				LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON r.uom_id = uom.id AND uom.deleted_at IS NULL
				LEFT JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id AND e.deleted_at IS NULL
				WHERE e.id = " . $elementId);

			$stmt->execute();

			$rationalizedItems = array_map('current', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC));

			//Get Rationalized BillItemNotListed
			$stmt = $pdo->prepare("SELECT r.bill_item_id, q.bill_column_setting_id, COALESCE(q.final_value,0) as value
				FROM " . TenderBillItemNotListedRationalizedQuantityTable::getInstance()->getTableName() . " q
				LEFT JOIN " . TenderBillItemNotListedRationalizedTable::getInstance()->getTableName() . " r ON r.id = q.tender_bill_not_listed_item_rationalized_id
				LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON r.bill_item_id = i.id AND i.deleted_at IS NULL
				LEFT JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id AND e.deleted_at IS NULL
				WHERE e.id = " . $elementId);

			$stmt->execute();

			$rationalizedQuantities = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

			if ( count($rationalizedQuantities) && count($rationalizedItems) )
			{
				foreach ( $rationalizedItems as $itemId => $item )
				{
					if ( array_key_exists($itemId, $rationalizedQuantities) )
					{
						foreach ( $rationalizedQuantities[$itemId] as $k => $qty )
						{
							$rationalizedItems[$itemId]['quantities'][$qty['bill_column_setting_id']] = $qty['value'];
						}
					}

					if ( array_key_exists($itemId, $rationalizedRates) )
					{
						$rationalizedItems[$itemId]['rate']        = $rationalizedRates[$itemId][0]['rate'];
						$rationalizedItems[$itemId]['grand_total'] = $rationalizedRates[$itemId][0]['grand_total'];
					}
				}
			}

			unset( $rationalizedQuantities );
			$rationalizedCount = 0;

			if ( isset ( $elements[$elementId] ) )
			{
				$items[] = array(
					'id'          => 'element-' . $elementId,
					'bill_ref'    => null,
					'description' => $elements[$elementId]['description'],
					'type'        => 0,
				);
			}

			foreach ( $billItems as $billItem )
			{
				$rate                  = 0;
				$rateAfterMarkup       = 0;
				$itemMarkupPercentage  = 0;
				$grandTotalAfterMarkup = 0;
				$rationalizedNotListed = false;
				$rationalizedCount ++;

				$billItem['bill_ref']                    = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                        = (string) $billItem['type'];
				$billItem['uom_id']                      = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['relation_id']                 = $elementId;
				$billItem['linked']                      = false;
				$billItem['_csrf_token']                 = $form->getCSRFToken();
				$billItem['project_revision_deleted_at'] = is_null($billItem['project_revision_deleted_at']) ? false : $billItem['project_revision_deleted_at'];

				if ( array_key_exists($billItem['id'], $formulatedColumns) )
				{
					$itemFormulatedColumns = $formulatedColumns[$billItem['id']];

					foreach ( $itemFormulatedColumns as $formulatedColumn )
					{
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

				$billItem['rate_after_markup']                  = $rateAfterMarkup;
				$billItem['rationalized_grand_total']           = 0;
				$billItem['rationalized_rate-value']            = 0;
				$billItem['rationalized-difference_percentage'] = 0;

				foreach ( $bill->BillColumnSettings as $column )
				{
					$quantityPerUnit = 0;
					$total           = 0;

					if ( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($billItem['id'], $quantityPerUnitByColumns[$column->id]) )
					{
						$quantityPerUnit = $quantityPerUnitByColumns[$column->id][$billItem['id']][0];
						unset( $quantityPerUnitByColumns[$column->id][$billItem['id']] );
					}

					if ( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$column->id]) )
					{
						$totalPerUnit = $rateAfterMarkup * $quantityPerUnit;
						$total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

						unset( $billItemTypeReferences[$column->id][$billItem['id']] );
					}

					$grandTotalAfterMarkup += $total;
				}

				$billItem['grand_total_after_markup'] = $grandTotalAfterMarkup;

				if ( array_key_exists($billItem['id'], $rationalizedRates) )
				{
					$billItem['rationalized_grand_total'] = $rationalizedRates[$billItem['id']][0]['grand_total'];
					$billItem['rationalized_rate-value']  = $rationalizedRates[$billItem['id']][0]['rate'];
				}

				$billItem['rationalized_grand_total_quantity'] = $billItem['grand_total_quantity'];

				if ( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED )
				{
					$billItem['rationalized_grand_total_quantity'] = 0;

					if ( count($rationalizedItems) && array_key_exists($billItem['id'], $rationalizedItems) )
					{
						$rationalizedNotListed = $billItem;

						$rationalizedNotListed['grand_total_after_markup'] = 0;
						$rationalizedNotListed['rate_after_markup']        = 0;
						$rationalizedNotListed['grand_total_quantity']     = 0;
						$rationalizedNotListed['rationalized_rate-value']  = 0;

						$totalQty = 0;

						foreach ( $bill->BillColumnSettings as $column )
						{
							$quantityPerUnit = 0;

							if ( array_key_exists($column->id, $rationalizedItems[$billItem['id']]['quantities']) )
							{
								$quantityPerUnit = $rationalizedItems[$billItem['id']]['quantities'][$column->id];
							}

							$totalQty += $quantityPerUnit * $column['quantity'];
						}

						$rationalizedNotListed['rationalized_grand_total_quantity'] = $totalQty;
						$rationalizedNotListed['id']                                = $billItem['id'] . '-' . $rationalizedCount;
						$rationalizedNotListed['description']                       = $rationalizedItems[$billItem['id']]['description'];
						$rationalizedNotListed['uom_id']                            = $rationalizedItems[$billItem['id']]['uom_id'];
						$rationalizedNotListed['uom_symbol']                        = $rationalizedItems[$billItem['id']]['uom_symbol'];
						$rationalizedNotListed['level']                             = $rationalizedNotListed['level'] + 1;
						$rationalizedNotListed['rationalized_rate-value']           = $rationalizedItems[$billItem['id']]['rate'];
						$rationalizedNotListed['rationalized_grand_total']          = $rationalizedItems[$billItem['id']]['grand_total'];
					}
				}

				$billItem['rationalized-difference_amount'] = $billItem['rationalized_grand_total'] - $billItem['grand_total_after_markup'];

				if ( $billItem['rationalized_grand_total'] != 0 )
				{
					$billItem['rationalized-difference_percentage'] = Utilities::prelimRounding(Utilities::percent($billItem['rationalized-difference_amount'], $billItem['grand_total_after_markup']));
				}

				array_push($items, $billItem);

				if ( $rationalizedNotListed )
				{
					array_push($items, $rationalizedNotListed);
				}

				unset( $billItem );
			}

			unset( $billItems, $rationalizedItems, $rationalizedQuantities );
		}

		unset( $elements, $elementWithBillItems );

		$defaultLastRow = array(
			'id'                => Constants::GRID_LAST_ROW,
			'bill_ref'          => '',
			'description'       => '',
			'type'              => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'            => '-1',
			'uom_symbol'        => '',
			'level'             => 0,
			'linked'            => false,
			'rate_after_markup' => 0,
			'grand_total'       => 0,
			'_csrf_token'       => $form->getCSRFToken()
		);

		array_push($items, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}
	// ============================================================================================================================================

}