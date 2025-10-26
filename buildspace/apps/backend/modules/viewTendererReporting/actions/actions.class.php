<?php

/**
 * viewTendererReporting actions.
 *
 * @package    buildspace
 * @subpackage viewTendererReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class viewTendererReportingActions extends BaseActions {

	public function executeGetAffectedElementsAndItems(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$billIds = $request->getParameter('bill_ids');

		return $this->renderJson(BillElementTable::getAffectedElementsAndItemsByBillId($billIds));
	}

    // Bill (Element level)
	public function executeGetAffectedBillsAndItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$elementIds = $request->getParameter('element_ids');

		return $this->renderJson(BillElementTable::getAffectedItemsAndBillsByElementId($bill, $elementIds));
	}

    // Bill (Item level)
	public function executeGetAffectedBillsAndElements(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$itemIds = $request->getParameter('itemIds');

		return $this->renderJson(BillElementTable::getAffectedElementsAndBillsByItemIds($itemIds));
	}

    // Schedule of rate (Element level)
    public function executeGetAffectedScheduleOfRateBillsAndItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $elementIds = $request->getParameter('element_ids');

        return $this->renderJson(ScheduleOfRateBillElementTable::getAffectedBillsAndItemsByElementId($bill, $elementIds));
    }

    // Schedule of rate (Item level)
    public function executeGetAffectedScheduleOfRateBillsAndElements(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $itemIds = $request->getParameter('itemIds');

        return $this->renderJson(ScheduleOfRateBillElementTable::getAffectedBillsAndElementsByItemIds($itemIds));
    }

    // Supply of Material (Element level)
    public function executeGetAffectedSupplyOfMaterialBillsAndItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $elementIds = $request->getParameter('element_ids');

        return $this->renderJson(SupplyOfMaterialElementTable::getAffectedBillsAndItemsByElementId($bill, $elementIds));
    }

    // Supply of Material (Item level)
    public function executeGetAffectedSupplyOfMaterialBillsAndElements(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $itemIds = $request->getParameter('itemIds');

        return $this->renderJson(SupplyOfMaterialElementTable::getAffectedBillsAndElementsByItemIds($itemIds));
    }

	public function executeGetPrintingInformation(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$form = new BaseForm();

		return $this->renderJson(array(
			'_csrf_token' => $form->getCSRFToken(),
		));
	}

	// ============================================================================================================================================
	// Printing Preview (Selected Tenderer)
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

			$projectSumTotal = ProjectStructureTable::getOverallTotalForProject($structure->id);
            $contractorRates = TenderCompanyTable::getSelectedContractorBillAmountByProjectId($structure, $records);
            $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($structure);

			foreach ( $records as $key => $record )
			{
				$count = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

				if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
				{
					$records[$key]['bill_type']   = $record['BillType']['type'];
					$records[$key]['bill_status'] = $record['BillType']['status'];
					$records[$key]['grand_total'] = (array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;

					foreach ( $contractorRates as $contractorId => $contractor )
					{
						$records[$key][$contractorId . '-grand_total']           = 0;
						$records[$key][$contractorId . '-difference_amount']     = 0;
						$records[$key][$contractorId . '-difference_percentage'] = 0;

						if ( array_key_exists($record['id'], $contractor['bill']) )
						{
							$records[$key][$contractorId . '-grand_total']           = $contractor['bill'][$record['id']];
							$records[$key][$contractorId . '-difference_amount']     = $records[$key][$contractorId . '-grand_total'] - $records[$key]['grand_total'];
							$records[$key][$contractorId . '-difference_percentage'] = 0;

							if ( $records[$key][$contractorId . '-grand_total'] != 0 )
							{
								$records[$key][$contractorId . '-difference_percentage'] = Utilities::prelimRounding(Utilities::percent($records[$key][$contractorId . '-difference_amount'], $records[$key]['grand_total']));
							}
						}
					}
				}
				else if ( $record['type'] == ProjectStructure::TYPE_ROOT )
				{
					$records[$key]['grand_total'] = $projectSumTotal;

					foreach ( $contractorRates as $contractorId => $contractor )
					{
						$records[$key][$contractorId . '-grand_total'] = $contractor['project_total'];
					}
				}

				$records[$key]['_csrf_token'] = $form->getCSRFToken();

				unset( $records[$key]['BillLayoutSetting'] );
				unset( $records[$key]['BillType'] );
				unset( $records[$key]['BillColumnSettings'] );
			}
		}

		$defaultLastRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'title'       => '',
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
		$form                         = new BaseForm();
		$totalRateByBillColumnSetting = array();
		$data['identifier']           = 'id';
		$data['items']                = array();
		$elements                     = array();

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
			$contractorRates   = TenderCompanyTable::getSelectedContractorElementGrandTotalByBillAndElements($bill, $elements);

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

				$elements[$key]['grand_total'] = $overallTotalAfterMarkup;

				foreach ( $contractorRates as $contractorId => $contractor )
				{
					if ( isset( $contractor['element'][$element['id']] ) )
					{
						$elements[$key][$contractorId . '-grand_total']           = $contractor['element'][$element['id']];
						$elements[$key][$contractorId . '-difference_amount']     = $elements[$key][$contractorId . '-grand_total'] - $overallTotalAfterMarkup;
						$elements[$key][$contractorId . '-difference_percentage'] = 0;

						if ( $elements[$key][$contractorId . '-grand_total'] != 0 )
						{
							$elements[$key][$contractorId . '-difference_percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$key][$contractorId . '-difference_amount'], $overallTotalAfterMarkup));
						}
					}
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
			$request->isXmlHttpRequest() AND
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
				$sql = "SELECT COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
					JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
					WHERE e.id = " . $elementId . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
					AND c.deleted_at IS NULL AND e.deleted_at IS NULL";

				$stmt = $pdo->prepare($sql);
				$stmt->execute();

				$elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
				$elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;

				$markupSettingsInfo['element_markup_percentage'] = $elementMarkupPercentage;
			}

			$contractorRates = TenderCompanyTable::getSelectedContractorRatesByElementId($elementId);
			$contractorIds   = array();

			foreach ( $contractorRates as $contractorId => $rates )
			{
				$contractorIds[] = $contractorId;
			}

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


				$billItem['rate_after_markup'] = $rateAfterMarkup;

				foreach ( $bill->BillColumnSettings as $column )
				{
					$total           = 0;
					$quantityPerUnit = 0;

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

				$billItemNotListed = array();

				if ( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED && count($contractorIds) )
				{
					//Get Bill Item Not Listed From Contractors
					$stmt = $pdo->prepare("SELECT inl.id, r.rate, r.grand_total, tc.company_id, inl.tender_company_id,
						inl.description, uom.id AS uom_id, uom.symbol AS uom_symbol FROM " . TenderBillItemNotListedTable::getInstance()->getTableName() . " inl
						LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON inl.uom_id = uom.id AND uom.deleted_at IS NULL
						LEFT JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.id = inl.tender_company_id
						LEFT JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " r ON r.tender_bill_item_not_listed_id = inl.id
						WHERE inl.bill_item_id = " . $billItem['id'] . " AND tc.company_id IN (" . implode(',', $contractorIds) . ")");

					$stmt->execute();

					$billItemNotListed = $stmt->fetchAll(PDO::FETCH_ASSOC);
				}
				else
				{
					foreach ( $contractorRates as $contractorId => $rates )
					{
						$billItem[$contractorId . '-rate-value']            = 0;
						$billItem[$contractorId . '-difference_amount']     = 0;
						$billItem[$contractorId . '-difference_percentage'] = 0;

						if ( array_key_exists($billItem['id'], $rates) )
						{
							$billItem[$contractorId . '-rate-value']            = $rates[$billItem['id']][0]['rate'];
							$billItem[$contractorId . '-difference_amount']     = $rates[$billItem['id']][0]['rate'] - $billItem['rate_after_markup'];
							$billItem[$contractorId . '-difference_percentage'] = 0;

							if ( $rates[$billItem['id']][0]['rate'] != 0 )
							{
								$billItem[$contractorId . '-difference_percentage'] = Utilities::prelimRounding(Utilities::percent($billItem[$contractorId . '-difference_amount'], $billItem['rate_after_markup']));
							}

							unset( $rates, $contractorRates[$contractorId][$billItem['id']] );
						}
					}
				}

				array_push($items, $billItem);

				if ( count($billItemNotListed) )
				{
					$itemNotListedData = $billItem;

					foreach ( $billItemNotListed as $itemNotListed )
					{
						$stmt = $pdo->prepare("SELECT  inl_q.bill_column_setting_id, inl_q.final_value
							FROM " . TenderBillItemNotListedQuantityTable::getInstance()->getTableName() . " inl_q
							WHERE inl_q.tender_bill_item_not_listed_id = " . $itemNotListed['id']);

						$stmt->execute();

						$itemNotListedQuantities = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

						$grandTotalQty = 0;

						foreach ( $bill->BillColumnSettings as $column )
						{
							if ( array_key_exists($column->id, $itemNotListedQuantities) )
							{
								$typeTotal = $column->quantity * $itemNotListedQuantities[$column->id][0];

								$grandTotalQty += $typeTotal;
							}
						}

						$itemNotListedData['grand_total_quantity'] = $grandTotalQty;
						$itemNotListedData['id']                   = $itemNotListed['company_id'] . '-' . $billItem['id'];
						$itemNotListedData['description']          = $itemNotListed['description'];
						$itemNotListedData['uom_id']               = $itemNotListed['uom_id'];
						$itemNotListedData['uom_symbol']           = $itemNotListed['uom_symbol'];
						$itemNotListedData['rate_after_markup']    = 0;
						$itemNotListedData['grand_total']          = 0;
						$itemNotListedData['linked']               = false;
						$itemNotListedData['level']                = $billItem['level'] + 1;

						foreach ( $contractorRates as $contractorId => $rates )
						{
							$itemNotListedData[$contractorId . '-rate-value']  = 0;
							$itemNotListedData[$contractorId . '-grand_total'] = 0;

							if ( $contractorId == $itemNotListed['company_id'] )
							{
								$itemNotListedData[$contractorId . '-rate-value']  = $itemNotListed['rate'];
								$itemNotListedData[$contractorId . '-grand_total'] = $itemNotListed['grand_total'];

								unset( $rates, $contractorRates[$contractorId][$billItem['id']] );
							}
						}

						array_push($items, $itemNotListedData);
					}
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
			$request->isXmlHttpRequest() AND
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
				$stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
					JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
					WHERE e.id = " . $elementId . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
					AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

				$stmt->execute();

				$elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
				$elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;

				$markupSettingsInfo['element_markup_percentage'] = $elementMarkupPercentage;
			}

			$contractorRates = TenderCompanyTable::getSelectedContractorRatesByElementId($elementId);
			$contractorIds   = array();

			foreach ( $contractorRates as $contractorId => $rates )
			{
				$contractorIds[] = $contractorId;
			}

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


				$billItem['rate_after_markup'] = $rateAfterMarkup;

				foreach ( $bill->BillColumnSettings as $column )
				{
					$total           = 0;
					$quantityPerUnit = 0;

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

				// $billItem['grand_total_after_markup'] = $grandTotalAfterMarkup;

				$billItemNotListed = array();

				if ( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED && count($contractorIds) )
				{
					//Get Bill Item Not Listed From Contractors
					$stmt = $pdo->prepare("SELECT inl.id, r.rate, r.grand_total, tc.company_id, inl.tender_company_id,
						inl.description, uom.id AS uom_id, uom.symbol AS uom_symbol FROM " . TenderBillItemNotListedTable::getInstance()->getTableName() . " inl
						LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON inl.uom_id = uom.id AND uom.deleted_at IS NULL
						LEFT JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.id = inl.tender_company_id
						LEFT JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " r ON r.tender_bill_item_not_listed_id = inl.id
						WHERE inl.bill_item_id = " . $billItem['id'] . " AND tc.company_id IN (" . implode(',', $contractorIds) . ")");

					$stmt->execute();

					$billItemNotListed = $stmt->fetchAll(PDO::FETCH_ASSOC);
				}
				else
				{
					foreach ( $contractorRates as $contractorId => $rates )
					{
						$billItem[$contractorId . '-rate-value']            = 0;
						$billItem[$contractorId . '-grand_total']           = 0;
						$billItem[$contractorId . '-difference_amount']     = 0;
						$billItem[$contractorId . '-difference_percentage'] = 0;

						if ( array_key_exists($billItem['id'], $rates) )
						{
							$billItem[$contractorId . '-rate-value']            = $rates[$billItem['id']][0]['rate'];
							$billItem[$contractorId . '-grand_total']           = $rates[$billItem['id']][0]['grand_total'];
							$billItem[$contractorId . '-difference_amount']     = $rates[$billItem['id']][0]['grand_total'] - $billItem['grand_total'];
							$billItem[$contractorId . '-difference_percentage'] = 0;

							if ( $rates[$billItem['id']][0]['grand_total'] != 0 )
							{
								$billItem[$contractorId . '-difference_percentage'] = Utilities::prelimRounding(Utilities::percent($billItem[$contractorId . '-difference_amount'], $billItem['grand_total']));
							}

							unset( $rates, $contractorRates[$contractorId][$billItem['id']] );
						}
					}
				}

				array_push($items, $billItem);

				if ( count($billItemNotListed) )
				{
					$itemNotListedData = $billItem;

					foreach ( $billItemNotListed as $itemNotListed )
					{
						$stmt = $pdo->prepare("SELECT  inl_q.bill_column_setting_id, inl_q.final_value
							FROM " . TenderBillItemNotListedQuantityTable::getInstance()->getTableName() . " inl_q
							WHERE inl_q.tender_bill_item_not_listed_id = " . $itemNotListed['id']);

						$stmt->execute();

						$itemNotListedQuantities = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

						$grandTotalQty = 0;

						foreach ( $bill->BillColumnSettings as $column )
						{
							if ( array_key_exists($column->id, $itemNotListedQuantities) )
							{
								$typeTotal = $column->quantity * $itemNotListedQuantities[$column->id][0];

								$grandTotalQty += $typeTotal;
							}
						}

						$itemNotListedData['grand_total_quantity'] = $grandTotalQty;
						$itemNotListedData['id']                   = $itemNotListed['company_id'] . '-' . $billItem['id'];
						$itemNotListedData['description']          = $itemNotListed['description'];
						$itemNotListedData['uom_id']               = $itemNotListed['uom_id'];
						$itemNotListedData['uom_symbol']           = $itemNotListed['uom_symbol'];
						$itemNotListedData['rate_after_markup']    = 0;
						$itemNotListedData['grand_total']          = 0;
						$itemNotListedData['linked']               = false;
						$itemNotListedData['level']                = $billItem['level'] + 1;

						foreach ( $contractorRates as $contractorId => $rates )
						{
							$itemNotListedData[$contractorId . '-rate-value']  = 0;
							$itemNotListedData[$contractorId . '-grand_total'] = 0;

							if ( $contractorId == $itemNotListed['company_id'] )
							{
								$itemNotListedData[$contractorId . '-rate-value']  = $itemNotListed['rate'];
								$itemNotListedData[$contractorId . '-grand_total'] = $itemNotListed['grand_total'];

								unset( $rates, $contractorRates[$contractorId][$billItem['id']] );
							}
						}

						array_push($items, $itemNotListedData);
					}
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

	public function executeGetPrintingSelectedElementSummaryPerUnitTypeByTenderer(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$tendererIds = $request->getParameter('tendererIds');
		$elementIds  = json_decode($request->getParameter('elementIds'), true);
		$elements    = array();

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

			$contractorRates             = TenderCompanyTable::getContractorSingleUnitElementGrandTotalByBillAndElementsAndTenderers($bill, $elements, $tendererIds);
			$itemOriginalQuantityByTypes = BillItemTypeReferenceTable::getQtyByBillColumnSettingsIdAndElementIds($bill->BillColumnSettings->toArray(), $elements);
			$itemOriginalRates           = BillItemTable::getItemRatesByElementIds($elements);

			foreach ( $bill->BillColumnSettings as $column )
			{
				$data['unitTypes'][] = array(
					'id'   => $column->id,
					'name' => $column->name
				);

				foreach ( $elements as $key => $element )
				{
					$elementId            = $element['id'];
					$itemQuantities       = $itemOriginalQuantityByTypes[$column->id][$elementId];
					$elementEstimateTotal = 0;

					// will calculate item's estimate amount first
					foreach ( $itemQuantities as $itemId => $itemQuantity )
					{
						$itemRate = isset( $itemOriginalRates[$itemId] ) ? $itemOriginalRates[$itemId] : 0;

						$elementEstimateTotal += $itemQuantity * $itemRate;
					}

					$elements[$key][$column->id . '-estimate_total'] = $elementEstimateTotal;

					// after that only count contractor's total
					foreach ( $contractorRates as $contractorId => $contractor )
					{
						$contractorTotal = 0;

						foreach ( $itemQuantities as $itemId => $itemQuantity )
						{
							if ( !isset( $contractor[$element['id']][$itemId] ) )
							{
								continue;
							}

							$contractorRate = $contractor[$element['id']][$itemId];

							$contractorTotal += $itemQuantity * $contractorRate;
						}

						$elements[$key]["{$column->id}-{$contractorId}-total"] = $contractorTotal;
					}
				}
			}
		}

		$defaultLastRow = array(
			'id'                         => Constants::GRID_LAST_ROW,
			'description'                => '',
			'overall_total_after_markup' => 0,
			'relation_id'                => $bill->id
		);

		array_push($elements, $defaultLastRow);

		$data['item']['identifier'] = 'id';
		$data['item']['items']      = $elements;

		return $this->renderJson($data);
	}
	// ============================================================================================================================================

	// ============================================================================================================================================
    // Printing Preview (All Tenderers) (Marked as selected in Tenderer Setting)
	// ============================================================================================================================================
	public function executeGetContractors(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
		);

		$records       = array();
		$tenderSetting = $project->TenderSetting;
		$contractorIds = json_decode($request->getParameter('contractorIds'), true);
		$sortBy        = $request->getParameter('type');
		$pdo           = $project->getTable()->getConnection()->getDbh();

		$awardedCompanyId = $tenderSetting->awarded_company_id > 0 ? $tenderSetting->awarded_company_id : - 1;

		// strip off awarded company id
		if ( ( $awardedCompanyKey = array_search($awardedCompanyId, $contractorIds) ) !== false )
		{
			unset( $contractorIds[$awardedCompanyKey] );
		}

		switch ($sortBy)
		{
			case TenderSetting::SORT_CONTRACTOR_HIGHEST_LOWEST_TEXT:
				$sqlOrder = "total DESC";
				break;
			case TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST_TEXT:
				$sqlOrder = "total ASC";
				break;
			default:
				throw new Exception('invalid sort option');
		}

		if ( !empty( $contractorIds ) )
		{
			$stmt = $pdo->prepare("SELECT c.id, c.name, xref.id AS tender_company_id, xref.show, COALESCE(SUM(r.grand_total), 0) AS total
			FROM " . CompanyTable::getInstance()->getTableName() . " c
			JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
			LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
			WHERE xref.project_structure_id = " . $project->id . "
			AND c.id IN (" . implode(', ', $contractorIds) . ")
			AND c.deleted_at IS NULL GROUP BY c.id, xref.show, xref.id ORDER BY " . $sqlOrder);

			$stmt->execute();

			$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		$companies = array();

		// only fetch awarded company if selected only
		if ( $tenderSetting->awarded_company_id > 0 AND $awardedCompanyKey !== false )
		{
			$awardedCompany = $tenderSetting->AwardedCompany;

			$company = array(
				'id'      => $awardedCompany->id,
				'name'    => $awardedCompany->name,
				'awarded' => true
			);

			array_push($companies, $company);

			unset( $company, $awardedCompany );
		}

		foreach ( $records as $key => $record )
		{
			$record['awarded'] = false;

			array_push($companies, $record);

			unset( $records[$key], $record );
		}

		return $this->renderJson($companies);
	}

	public function executeGetContractorsForSummaryPerUnitType(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
		);

		$records       = array();
		$tenderSetting = $project->TenderSetting;
		$contractorIds = json_decode($request->getParameter('contractorIds'), true);
		$pdo           = $project->getTable()->getConnection()->getDbh();

		$awardedCompanyId = $tenderSetting->awarded_company_id > 0 ? $tenderSetting->awarded_company_id : - 1;

		// strip off awarded company id
		if ( ( $awardedCompanyKey = array_search($awardedCompanyId, $contractorIds) ) !== false )
		{
			unset( $contractorIds[$awardedCompanyKey] );
		}

		if ( !empty( $contractorIds ) )
		{
			$stmt = $pdo->prepare("SELECT c.id, c.name, xref.id AS tender_company_id, xref.show, COALESCE(SUM(r.grand_total), 0) AS total
			FROM " . CompanyTable::getInstance()->getTableName() . " c
			JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
			LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
			WHERE xref.project_structure_id = " . $project->id . "
			AND c.id IN (" . implode(', ', $contractorIds) . ")
			AND c.deleted_at IS NULL GROUP BY c.id, xref.show, xref.id ORDER BY c.id ASC");

			$stmt->execute();

			$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		$companies = array();

		// only fetch awarded company if selected only
		if ( $tenderSetting->awarded_company_id > 0 AND $awardedCompanyKey !== false )
		{
			$awardedCompany = $tenderSetting->AwardedCompany;

			$company = array(
				'id'      => $awardedCompany->id,
				'name'    => $awardedCompany->name,
				'awarded' => true
			);

			array_push($companies, $company);

			unset( $company, $awardedCompany );
		}

		foreach ( $records as $key => $record )
		{
			$record['awarded'] = false;

			array_push($companies, $record);

			unset( $records[$key], $record );
		}

		return $this->renderJson($companies);
	}

	public function executeGetPrintingSelectedBillByAllTenderer(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
		);

		$tendererIds = json_decode($request->getParameter('tendererIds'), true);
		$billIds     = json_decode($request->getParameter('billIds'), true);
		$records     = array();

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

			$projectSumTotal = ProjectStructureTable::getOverallTotalForProject($structure->id);
			$contractorRates = TenderCompanyTable::getAllContractorBillAmountByProjectIdAndContractors($structure, $records, $tendererIds);
            $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($structure);

			foreach ( $records as $key => $record )
			{
				$count = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

				if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
				{
					$records[$key]['bill_type']   = $record['BillType']['type'];
					$records[$key]['bill_status'] = $record['BillType']['status'];
					$records[$key]['grand_total'] = (array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;

					$tendererCostings = array();

					foreach ( $contractorRates as $contractorId => $contractor )
					{
						$records[$key][$contractorId . '-grand_total']  = 0;
						$records[$key][$contractorId . '-lowest_cost']  = false;
						$records[$key][$contractorId . '-highest_cost'] = false;

						if ( array_key_exists($record['id'], $contractor['bill']) )
						{
							$records[$key][$contractorId . '-grand_total'] = $contractor['bill'][$record['id']];
							$tendererCostings[$contractorId]               = $contractor['bill'][$record['id']];

							unset( $rates, $contractorRates[$contractorId][$record['id']] );
						}
					}

					// if more than 2 tenderers selected then only apply the assignment for the highest
					// and lowest costing from tenderers
					if ( $record['type'] == ProjectStructure::TYPE_BILL AND count($tendererCostings) > 1 )
					{
						// determine which costing from tenderers is highest and lowest
						$minTotalIndex = array_keys($tendererCostings, min($tendererCostings));
						$maxTotalIndex = array_keys($tendererCostings, max($tendererCostings));

						$records[$key][$minTotalIndex[0] . '-lowest_cost']  = true;
						$records[$key][$maxTotalIndex[0] . '-highest_cost'] = true;
					}

					unset( $tendererCostings );
				}
				else if ( $record['type'] == ProjectStructure::TYPE_ROOT )
				{
					$records[$key]['grand_total'] = $projectSumTotal;

					foreach ( $contractorRates as $contractorId => $contractor )
					{
						$records[$key][$contractorId . '-grand_total'] = $contractor['project_total'];
					}
				}

				unset( $records[$key]['BillLayoutSetting'] );
				unset( $records[$key]['BillType'] );
				unset( $records[$key]['BillColumnSettings'] );
			}
		}

		$defaultLastRow = array(
			'id'    => Constants::GRID_LAST_ROW,
			'title' => '',
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

    public function executeGetPrintingSelectedBillRevisionsByAllTenderer(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $tendererIds = json_decode($request->getParameter('tendererIds'), true);
        $billIds     = json_decode($request->getParameter('billIds'), true);
        $records     = array();

        $project = Doctrine_Core::getTable('ProjectStructure')->find($structure->root_id);
        $projectRevisions = ProjectRevisionTable::getRevisions($project);

        $estimateBillGrandTotals = ProjectRevisionTable::getEstimateBillGrandTotalRevisions($structure);
        $tendererBillGrandTotals = ProjectRevisionTable::getTendererBillGrandTotalRevisions($structure, $tendererIds);

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

            $projectSumTotal = ProjectStructureTable::getOverallTotalForProject($structure->id);
            $contractorRates = TenderCompanyTable::getAllContractorBillAmountByProjectIdAndContractors($structure, $records, $tendererIds);
            $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($structure);

            foreach ( $records as $key => $record )
            {
                $count = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

                if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
                {
                    $records[$key]['bill_type']   = $record['BillType']['type'];
                    $records[$key]['bill_status'] = $record['BillType']['status'];
                    $records[$key]['grand_total'] = (array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;

                    // Add addendum estimate grand totals.
                    foreach($projectRevisions as $revisionNumber => $revision)
                    {
                        $records[ $key ][ 'grand_total-revision-' . $revisionNumber ] = $estimateBillGrandTotals[ $revisionNumber ]['bills'][ $record['id'] ];
                    }

                    $tendererCostings = array();

                    foreach ( $contractorRates as $contractorId => $contractor )
                    {
                        $records[$key][$contractorId . '-grand_total']  = 0;
                        $records[$key][$contractorId . '-lowest_cost']  = false;
                        $records[$key][$contractorId . '-highest_cost'] = false;

                        if ( array_key_exists($record['id'], $contractor['bill']) )
                        {
                            $records[$key][$contractorId . '-grand_total'] = $contractor['bill'][$record['id']];
                            $tendererCostings[$contractorId]               = $contractor['bill'][$record['id']];

                            // Add addendum tenderer grand totals.
                            foreach($projectRevisions as $revisionNumber => $revision)
                            {
                                $records[ $key ][ $contractorId . '-grand_total-revision-' . $revisionNumber ] = $tendererBillGrandTotals[ $contractorId ][ $revisionNumber ]['bills'][ $record['id'] ];
                            }

                            unset( $rates, $contractorRates[$contractorId][$record['id']] );
                        }
                    }

                    // if more than 2 tenderers selected then only apply the assignment for the highest
                    // and lowest costing from tenderers
                    if ( $record['type'] == ProjectStructure::TYPE_BILL AND count($tendererCostings) > 1 )
                    {
                        // determine which costing from tenderers is highest and lowest
                        $minTotalIndex = array_keys($tendererCostings, min($tendererCostings));
                        $maxTotalIndex = array_keys($tendererCostings, max($tendererCostings));

                        $records[$key][$minTotalIndex[0] . '-lowest_cost']  = true;
                        $records[$key][$maxTotalIndex[0] . '-highest_cost'] = true;
                    }

                    unset( $tendererCostings );
                }
                else if ( $record['type'] == ProjectStructure::TYPE_ROOT )
                {
                    $records[$key]['grand_total'] = $projectSumTotal;

                    // Add addendum estimate grand totals.
                    foreach($projectRevisions as $revisionNumber => $revision)
                    {
                        $records[ $key ][ 'grand_total-revision-' . $revisionNumber ] = $estimateBillGrandTotals[ $revisionNumber ]['project_total'];
                    }

                    foreach ( $contractorRates as $contractorId => $contractor )
                    {
                        $records[$key][$contractorId . '-grand_total'] = $contractor['project_total'];

                        // Add addendum tenderer grand totals.
                        foreach($projectRevisions as $revisionNumber => $revision)
                        {
                            $records[ $key ][ $contractorId . '-grand_total-revision-' . $revisionNumber ] = $tendererBillGrandTotals[ $contractorId ][ $revisionNumber ]['project_total'];
                        }
                    }
                }

                unset( $records[$key]['BillLayoutSetting'] );
                unset( $records[$key]['BillType'] );
                unset( $records[$key]['BillColumnSettings'] );
            }
        }

        $defaultLastRow = array(
            'id'    => Constants::GRID_LAST_ROW,
            'title' => '',
        );

        array_push($records, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

	public function executeGetPrintingSelectedElementByAllTenderer(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$tendererIds                  = $request->getParameter('tendererIds');
		$elementIds                   = json_decode($request->getParameter('elementIds'), true);
		$elements                     = array();
		$totalRateByBillColumnSetting = array();
		$data['identifier']           = 'id';
		$data['items']                = array();

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
			$contractorRates   = TenderCompanyTable::getContractorElementGrandTotalByBillAndElementsAndTenderers($bill, $elements, $tendererIds);

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

				$elements[$key]['grand_total'] = $overallTotalAfterMarkup;

				$tendererCostings = array();

				foreach ( $contractorRates as $contractorId => $contractor )
				{
					if ( isset( $contractor['element'][$element['id']] ) )
					{
						$elements[$key][$contractorId . '-grand_total'] = $contractor['element'][$element['id']];
					}

					$elements[$key][$contractorId . '-rate-value']   = 0;
					$elements[$key][$contractorId . '-grand_total']  = 0;
					$elements[$key][$contractorId . '-lowest_cost']  = false;
					$elements[$key][$contractorId . '-highest_cost'] = false;

					if ( isset( $contractor['element'][$element['id']] ) )
					{
						$elements[$key][$contractorId . '-grand_total'] = $contractor['element'][$element['id']];
						$tendererCostings[$contractorId]                = $contractor['element'][$element['id']];

						unset( $rates, $contractorRates[$contractorId][$element['id']] );
					}
				}

				// if more than 2 tenderers selected then only apply the assignment for the highest
				// and lowest costing from tenderers
				if ( count($tendererCostings) > 1 )
				{
					// determine which costing from tenderers is highest and lowest
					$minTotalIndex = array_keys($tendererCostings, min($tendererCostings));
					$maxTotalIndex = array_keys($tendererCostings, max($tendererCostings));

					$elements[$key][$minTotalIndex[0] . '-lowest_cost']  = true;
					$elements[$key][$maxTotalIndex[0] . '-highest_cost'] = true;
				}

				unset( $tendererCostings );
			}
		}

		$defaultLastRow = array(
			'id'                         => Constants::GRID_LAST_ROW,
			'description'                => '',
			'overall_total_after_markup' => 0,
			'relation_id'                => $bill->id
		);

		array_push($elements, $defaultLastRow);

		$data['items'] = $elements;

		return $this->renderJson($data);
	}

    public function executeGetPrintingSelectedElementRevisionsByAllTenderer(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $tendererIds                  = $request->getParameter('tendererIds');
        $elementIds                   = json_decode($request->getParameter('elementIds'), true);
        $elements                     = array();
        $totalRateByBillColumnSetting = array();
        $data['identifier']           = 'id';
        $data['items']                = array();

        $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id);
        $projectRevisions = ProjectRevisionTable::getRevisions($project);

        $estimateElementGrandTotals = ProjectRevisionTable::getEstimateElementGrandTotalRevisions($bill);
        $tendererElementGrandTotals = ProjectRevisionTable::getTendererElementGrandTotalRevisions($bill, json_decode($tendererIds));

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
            $contractorRates   = TenderCompanyTable::getContractorElementGrandTotalByBillAndElementsAndTenderers($bill, $elements, $tendererIds);

            //We get All Element Sum Group By Element Here so that we don't have to repeat query within element loop
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

                $elements[$key]['grand_total'] = $overallTotalAfterMarkup;

                // Add addendum estimate grand totals.
                foreach($projectRevisions as $revisionNumber => $revision)
                {
                    $elements[ $key ][ 'grand_total-revision-' . $revisionNumber ] = $estimateElementGrandTotals[ $revisionNumber ]['elements'][ $element['id'] ];
                }

                $tendererCostings = array();

                foreach ( $contractorRates as $contractorId => $contractor )
                {
                    if ( isset( $contractor['element'][$element['id']] ) )
                    {
                        $elements[$key][$contractorId . '-grand_total'] = $contractor['element'][$element['id']];
                    }

                    $elements[$key][$contractorId . '-rate-value']   = 0;
                    $elements[$key][$contractorId . '-grand_total']  = 0;
                    $elements[$key][$contractorId . '-lowest_cost']  = false;
                    $elements[$key][$contractorId . '-highest_cost'] = false;

                    if ( isset( $contractor['element'][$element['id']] ) )
                    {
                        $elements[$key][$contractorId . '-grand_total'] = $contractor['element'][$element['id']];
                        $tendererCostings[$contractorId]                = $contractor['element'][$element['id']];

                        // Add addendum tenderer grand totals.
                        foreach($projectRevisions as $revisionNumber => $revision)
                        {
                            $elements[ $key ][ $contractorId . '-grand_total-revision-' . $revisionNumber ] = $tendererElementGrandTotals[ $contractorId ][ $revisionNumber ]['elements'][ $element['id'] ];
                        }

                        unset( $rates, $contractorRates[$contractorId][$element['id']] );
                    }
                }

                // if more than 2 tenderers selected then only apply the assignment for the highest
                // and lowest costing from tenderers
                if ( count($tendererCostings) > 1 )
                {
                    // determine which costing from tenderers is highest and lowest
                    $minTotalIndex = array_keys($tendererCostings, min($tendererCostings));
                    $maxTotalIndex = array_keys($tendererCostings, max($tendererCostings));

                    $elements[$key][$minTotalIndex[0] . '-lowest_cost']  = true;
                    $elements[$key][$maxTotalIndex[0] . '-highest_cost'] = true;
                }

                unset( $tendererCostings );
            }
        }

        $defaultLastRow = array(
            'id'                         => Constants::GRID_LAST_ROW,
            'description'                => '',
            'overall_total_after_markup' => 0,
            'relation_id'                => $bill->id
        );

        array_push($elements, $defaultLastRow);

        $data['items'] = $elements;

        return $this->renderJson($data);
    }

	public function executeGetPrintingSelectedItemRateByAllTenderer(sfWebRequest $request)
    {
        return self::executeGetSelectedItemByAllTenderers($request);
    }

	public function executeGetPrintingSelectedItemTotalByAllTenderer(sfWebRequest $request)
    {
        return self::executeGetSelectedItemByAllTenderers($request);
    }

    public function executeGetSelectedItemByAllTenderers(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $tendererIds  = $request->getParameter('tendererIds');
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

            $contractorRates = TenderCompanyTable::getContractorRatesByElementIdAndContractorIds($elementId, $tendererIds);
            $contractorIds   = array();

            foreach ( $contractorRates as $contractorId => $rates )
            {
                $contractorIds[] = $contractorId;
            }

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

                $billItem['rate_after_markup'] = $rateAfterMarkup;

                foreach ( $bill->BillColumnSettings as $column )
                {
                    $total           = 0;
                    $quantityPerUnit = 0;

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

                $billItemNotListed = array();

                if ( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED && count($contractorIds) )
                {
                    //Get Bill Item Not Listed From Contractors
                    $stmt = $pdo->prepare("SELECT inl.id, r.rate, r.grand_total, tc.company_id, inl.tender_company_id,
						inl.description, uom.id AS uom_id, uom.symbol AS uom_symbol FROM " . TenderBillItemNotListedTable::getInstance()->getTableName() . " inl
						LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON inl.uom_id = uom.id AND uom.deleted_at IS NULL
						LEFT JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.id = inl.tender_company_id
						LEFT JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " r ON r.tender_bill_item_not_listed_id = inl.id
						WHERE inl.bill_item_id = " . $billItem['id'] . " AND tc.company_id IN (" . implode(',', $contractorIds) . ")");

                    $stmt->execute();

                    $billItemNotListed = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                else
                {
                    $this->setContractorRates($contractorRates, $billItem);

                    $this->flagMinAndMaxCost($contractorRates, $billItem);
                }

                if($billItem['type'] == BillItem::TYPE_ITEM_RATE_ONLY)
                {
                    $billItem['grand_total_quantity'] = 'Rate Only';
                }

                array_push($items, $billItem);

                if ( count($billItemNotListed) )
                {
                    $itemNotListedData = $billItem;

                    foreach ( $billItemNotListed as $itemNotListed )
                    {
                        $stmt = $pdo->prepare("SELECT inl_q.bill_column_setting_id, inl_q.final_value
							FROM " . TenderBillItemNotListedQuantityTable::getInstance()->getTableName() . " inl_q
							WHERE inl_q.tender_bill_item_not_listed_id = " . $itemNotListed['id']);

                        $stmt->execute();

                        $itemNotListedQuantities = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

                        $grandTotalQty = 0;

                        foreach ( $bill->BillColumnSettings as $column )
                        {
                            if ( array_key_exists($column->id, $itemNotListedQuantities) )
                            {
                                $typeTotal = $column->quantity * $itemNotListedQuantities[$column->id][0];

                                $grandTotalQty += $typeTotal;
                            }
                        }

                        $itemNotListedData['grand_total_quantity'] = $grandTotalQty;
                        $itemNotListedData['id']                   = $itemNotListed['company_id'] . '-' . $billItem['id'];
                        $itemNotListedData['description']          = $itemNotListed['description'];
                        $itemNotListedData['uom_id']               = $itemNotListed['uom_id'];
                        $itemNotListedData['uom_symbol']           = $itemNotListed['uom_symbol'];
                        $itemNotListedData['rate_after_markup']    = 0;
                        $itemNotListedData['grand_total']          = 0;
                        $itemNotListedData['linked']               = false;
                        $itemNotListedData['level']                = $billItem['level'] + 1;

                        foreach ( $contractorRates as $contractorId => $rates )
                        {
                            $itemNotListedData[$contractorId . '-rate-value']  = 0;
                            $itemNotListedData[$contractorId . '-grand_total'] = 0;

                            if ( $contractorId == $itemNotListed['company_id'] )
                            {
                                $itemNotListedData[$contractorId . '-rate-value']  = $itemNotListed['rate'];
                                $itemNotListedData[$contractorId . '-grand_total'] = $itemNotListed['grand_total'];

                                unset( $rates, $contractorRates[$contractorId][$billItem['id']] );
                            }
                        }

                        array_push($items, $itemNotListedData);
                    }
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

    public function executeGetSelectedItemRevisionsByAllTenderers(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $tendererIds = $request->getParameter('tendererIds');
        $itemIds = $request->getParameter('itemIds');
        $pdo = $bill->getTable()->getConnection()->getDbh();
        $form = new BaseForm();
        $items = array();
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;

        $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id);
        $projectRevisions = ProjectRevisionTable::getRevisions($project, true);

        list(
            $estimateRateRevisions,
            $estimateTotalRevisions,
            $tendererRateRevisions,
            $tendererTotalRevisions
            ) = ProjectRevisionTable::getRatesAndTotalsRevisions($bill, json_decode($tendererIds, true));

        $itemRateLog = TenderBillItemRateLogTable::getBillItemRateLogs($project);

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

        $billItemsWithDeleted = BillItemTable::getAllBillItemsIncludingDeleted(json_decode($itemIds, true), $bill);

        foreach($elementWithBillItems as $elementId => $billItems)
        {
            /*
             * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
             */
            if( $bill->BillMarkupSetting->element_markup_enabled )
            {
                $stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value
					FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
					JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
					WHERE e.id = " . $elementId . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
					AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

                $stmt->execute();

                $elementMarkupResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $elementMarkupPercentage = $elementMarkupResult ? (float)$elementMarkupResult['value'] : 0;

                $markupSettingsInfo['element_markup_percentage'] = $elementMarkupPercentage;
            }

            $contractorRates = TenderCompanyTable::getContractorRatesByElementIdAndContractorIds($elementId, $tendererIds);
            $contractorIds = array();

            foreach($contractorRates as $contractorId => $rates)
            {
                $contractorIds[] = $contractorId;
            }

            if( isset ( $elements[ $elementId ] ) )
            {
                $items[] = array(
                    'id'          => 'element-' . $elementId,
                    'bill_ref'    => null,
                    'description' => $elements[ $elementId ]['description'],
                    'type'        => 0,
                );
            }

            $deletedItemIds = ProjectRevisionTable::getDeletedItemIds($billItemsWithDeleted[ $elementId ], $billItems);

            foreach($billItemsWithDeleted[ $elementId ] as $billItem)
            {
                // Set to true if item is deleted.
                $billItem['deleted'] = in_array($billItem['id'], $deletedItemIds);

                $rate = 0;
                $rateAfterMarkup = 0;
                $itemMarkupPercentage = 0;
                $grandTotalAfterMarkup = 0;

                $billItem['bill_ref'] = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
                $billItem['type'] = (string)$billItem['type'];
                $billItem['uom_id'] = $billItem['uom_id'] > 0 ? (string)$billItem['uom_id'] : '-1';
                $billItem['relation_id'] = $elementId;
                $billItem['linked'] = false;
                $billItem['_csrf_token'] = $form->getCSRFToken();
                $billItem['project_revision_deleted_at'] = is_null($billItem['project_revision_deleted_at']) ? false : $billItem['project_revision_deleted_at'];

                if( array_key_exists($billItem['id'], $formulatedColumns) )
                {
                    $itemFormulatedColumns = $formulatedColumns[ $billItem['id'] ];

                    foreach($itemFormulatedColumns as $formulatedColumn)
                    {
                        if( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                        {
                            $rate = $formulatedColumn['final_value'];
                        }

                        if( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
                        {
                            $itemMarkupPercentage = $formulatedColumn['final_value'];
                        }
                    }

                    unset( $formulatedColumns[ $billItem['id'] ], $itemFormulatedColumns );

                    $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
                }

                if( isset( $billItem['deleted'] ) && ( ! $billItem['deleted'] ) )
                {
                    $billItem['rate_after_markup'] = $rateAfterMarkup;
                }

                // Add addendum estimate rates.
                foreach($projectRevisions as $revisionNumber => $revision)
                {
                    if( array_key_exists($billItem['id'], $estimateRateRevisions[ $revisionNumber ]) )
                    {
                        $billItem[ 'rate_after_markup_revision-' . $revisionNumber ] = $estimateRateRevisions[ $revisionNumber ][ $billItem['id'] ];
                        $billItem[ 'grand_total_revision-' . $revisionNumber ] = $estimateTotalRevisions[ $revisionNumber ][ $billItem['id'] ];
                    }
                }

                foreach($bill->BillColumnSettings as $column)
                {
                    $total = 0;
                    $quantityPerUnit = 0;

                    if( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($billItem['id'], $quantityPerUnitByColumns[ $column->id ]) )
                    {
                        $quantityPerUnit = $quantityPerUnitByColumns[ $column->id ][ $billItem['id'] ][0];
                        unset( $quantityPerUnitByColumns[ $column->id ][ $billItem['id'] ] );
                    }

                    if( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[ $column->id ]) )
                    {
                        $totalPerUnit = $rateAfterMarkup * $quantityPerUnit;
                        $total = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                        unset( $billItemTypeReferences[ $column->id ][ $billItem['id'] ] );
                    }

                    $grandTotalAfterMarkup += $total;
                }

                $billItemNotListed = array();

                if( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED && count($contractorIds) )
                {
                    //Get Bill Item Not Listed From Contractors
                    $stmt = $pdo->prepare("SELECT inl.id, r.rate, r.grand_total, tc.company_id, inl.tender_company_id, c.name as tenderer,
						inl.description, uom.id AS uom_id, uom.symbol AS uom_symbol FROM " . TenderBillItemNotListedTable::getInstance()->getTableName() . " inl
						LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON inl.uom_id = uom.id AND uom.deleted_at IS NULL
						LEFT JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.id = inl.tender_company_id
						LEFT JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " r ON r.tender_bill_item_not_listed_id = inl.id
						LEFT JOIN " . CompanyTable::getInstance()->getTableName(). " c ON  c.id = tc.company_id AND c.deleted_at IS NULL
						WHERE inl.bill_item_id = " . $billItem['id'] . " AND tc.company_id IN (" . implode(',', $contractorIds) . ")");

                    $stmt->execute();

                    $billItemNotListed = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                else
                {
                    $this->setContractorRates($contractorRates, $billItem);
                    $this->setContractorRatesRevisions($billItem, $projectRevisions, $itemRateLog);

                    $this->flagMinAndMaxCost($contractorRates, $billItem);
                }

                if($billItem['type'] == BillItem::TYPE_ITEM_RATE_ONLY)
                {
                    $billItem['grand_total_quantity'] = 'Rate Only';
                }

                array_push($items, $billItem);

                if( count($billItemNotListed) )
                {
                    $itemNotListedDataTemplate = $billItem;

                    // Remove addendum estimate rates.
                    foreach($projectRevisions as $revisionNumber => $revision)
                    {
                        if( array_key_exists($itemNotListedDataTemplate['id'], $estimateRateRevisions[ $revisionNumber ]) )
                        {
                            unset($itemNotListedDataTemplate[ 'rate_after_markup_revision-' . $revisionNumber ]);
                            unset($itemNotListedDataTemplate[ 'grand_total_revision-' . $revisionNumber ]);
                        }
                    }

                    foreach($billItemNotListed as $itemNotListed)
                    {
                        $itemNotListedData = $itemNotListedDataTemplate;

                        $stmt = $pdo->prepare("SELECT inl_q.bill_column_setting_id, inl_q.final_value
							FROM " . TenderBillItemNotListedQuantityTable::getInstance()->getTableName() . " inl_q
							WHERE inl_q.tender_bill_item_not_listed_id = " . $itemNotListed['id']);

                        $stmt->execute();

                        $itemNotListedQuantities = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

                        $grandTotalQty = 0;

                        foreach($bill->BillColumnSettings as $column)
                        {
                            if( array_key_exists($column->id, $itemNotListedQuantities) )
                            {
                                $typeTotal = $column->quantity * $itemNotListedQuantities[ $column->id ][0];

                                $grandTotalQty += $typeTotal;
                            }
                        }

                        $itemNotListedData['grand_total_quantity'] = $grandTotalQty;
                        $itemNotListedData['id'] = $itemNotListed['company_id'] . '-' . $billItem['id'];
                        $itemNotListedData['description'] = "({$itemNotListed['tenderer']}) {$itemNotListed['description']}";
                        $itemNotListedData['uom_id'] = $itemNotListed['uom_id'];
                        $itemNotListedData['uom_symbol'] = $itemNotListed['uom_symbol'];
                        $itemNotListedData['rate_after_markup'] = 0;
                        $itemNotListedData['grand_total'] = 0;
                        $itemNotListedData['linked'] = false;
                        $itemNotListedData['level'] = $billItem['level'] + 1;

                        foreach($itemRateLog[ $itemNotListed['company_id'] ] as $revisionNumber => $revision)
                        {
                            if( isset( $itemRateLog[ $itemNotListed['company_id'] ][ $revisionNumber ][ $billItem['id'] ] ) )
                            {
                                $itemNotListedData[ $itemNotListed['company_id'] . '-rate-value_revision-' . $revisionNumber ] = $itemRateLog[ $itemNotListed['company_id'] ][ $revisionNumber ][ $billItem['id'] ]['rate'];
                                $itemNotListedData[ $itemNotListed['company_id'] . '-grand_total_revision-' . $revisionNumber ] = $itemRateLog[ $itemNotListed['company_id'] ][ $revisionNumber ][ $billItem['id'] ]['grand_total'];
                            }
                        }

                        array_push($items, $itemNotListedData);
                    }
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
            'type'              => (string)ProjectStructure::getDefaultItemType($bill->BillType->type),
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

    public function executeGetSelectedItemPerUnitByAllTenderers(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $tendererIds  = $request->getParameter('tendererIds');
        $itemIds      = $request->getParameter('itemIds');
        $form         = new BaseForm();
        $items        = array();
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;

        $formulatedColumnConstants             = Utilities::getAllFormulatedColumnConstants('BillItem');
        $billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

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

        foreach($elementWithBillItems as $elementId => $billItems)
        {
            $contractorRates = TenderCompanyTable::getContractorRatesByElementIdAndContractorIds($elementId, $tendererIds);

            $elementRow = array(
                'id'          => 'element-' . $elementId,
                'bill_ref'    => null,
                'description' => $elements[ $elementId ]['description'],
                'type'        => 0,
            );

            array_push($items, $elementRow);

            foreach($billItems as $billItem)
            {
                $rate                  = 0;
                $rateAfterMarkup       = 0;
                $itemMarkupPercentage  = 0;
                $grandTotalAfterMarkup = 0;

                $billItem['bill_ref']                    = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
                $billItem['type']                        = (string)$billItem['type'];
                $billItem['uom_id']                      = $billItem['uom_id'] > 0 ? (string)$billItem['uom_id'] : '-1';
                $billItem['relation_id']                 = $elementId;
                $billItem['linked']                      = false;
                $billItem['markup_rounding_type']        = $bill->BillMarkupSetting->rounding_type;
                $billItem['_csrf_token']                 = $form->getCSRFToken();
                $billItem['project_revision_deleted_at'] = is_null($billItem['project_revision_deleted_at']) ? false : $billItem['project_revision_deleted_at'];
                $billItem['has_note']                    = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;

                foreach($formulatedColumnConstants as $constant)
                {
                    $billItem[ $constant . '-final_value' ]        = 0;
                    $billItem[ $constant . '-value' ]              = '';
                    $billItem[ $constant . '-has_cell_reference' ] = false;
                    $billItem[ $constant . '-has_formula' ]        = false;
                    $billItem[ $constant . '-linked' ]             = false;
                    $billItem[ $constant . '-has_build_up' ]       = false;
                }

                if( array_key_exists($billItem['id'], $formulatedColumns) )
                {
                    $itemFormulatedColumns = $formulatedColumns[ $billItem['id'] ];

                    foreach($itemFormulatedColumns as $formulatedColumn)
                    {
                        $billItem[ $formulatedColumn['column_name'] . '-final_value' ]  = $formulatedColumn['final_value'];
                        $billItem[ $formulatedColumn['column_name'] . '-value' ]        = $formulatedColumn['value'];
                        $billItem[ $formulatedColumn['column_name'] . '-linked' ]       = $formulatedColumn['linked'];
                        $billItem[ $formulatedColumn['column_name'] . '-has_build_up' ] = $formulatedColumn['has_build_up'];
                        $billItem[ $formulatedColumn['column_name'] . '-has_formula' ]  = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

                        if( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                        {
                            $rate = $formulatedColumn['final_value'];
                        }

                        if( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
                        {
                            $itemMarkupPercentage = $formulatedColumn['final_value'];
                        }
                    }

                    unset( $formulatedColumns[ $billItem['id'] ], $itemFormulatedColumns );

                    $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
                }

                $billItem['single_unit_quantity'] = $rateAfterMarkup;

                $this->setContractorRates($contractorRates, $billItem);
                $this->flagMinAndMaxCost($contractorRates, $billItem);

                foreach($bill->BillColumnSettings as $column)
                {
                    $quantityPerUnit = 0;

                    foreach($billItemTypeFormulatedColumnConstants as $constant)
                    {
                        $billItem[ $column->id . '-' . $constant . '-final_value' ]        = 0;
                        $billItem[ $column->id . '-' . $constant . '-value' ]              = '';
                        $billItem[ $column->id . '-' . $constant . '-has_cell_reference' ] = false;
                        $billItem[ $column->id . '-' . $constant . '-has_formula' ]        = false;
                        $billItem[ $column->id . '-' . $constant . '-linked' ]             = false;
                        $billItem[ $column->id . '-' . $constant . '-has_build_up' ]       = false;
                    }

                    if( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($billItem['id'], $quantityPerUnitByColumns[ $column->id ]) )
                    {
                        $quantityPerUnit = $quantityPerUnitByColumns[ $column->id ][ $billItem['id'] ][0];
                        unset( $quantityPerUnitByColumns[ $column->id ][ $billItem['id'] ] );
                    }

                    if( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[ $column->id ]) )
                    {
                        $billItemTypeRef     = $billItemTypeReferences[ $column->id ][ $billItem['id'] ];
                        $include             = $billItemTypeRef['include'] ? 'true' : 'false';
                        $totalQuantity       = $billItemTypeRef['total_quantity'];
                        $quantityPerUnitDiff = $billItemTypeRef['quantity_per_unit_difference'];
                        $totalPerUnit        = number_format($rateAfterMarkup * $quantityPerUnit, 2, '.', '');
                        $total               = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                        unset( $billItemTypeReferences[ $column->id ][ $billItem['id'] ] );

                        if( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
                        {
                            foreach($billItemTypeRefFormulatedColumns[ $billItemTypeRef['id'] ] as $billItemTypeRefFormulatedColumn)
                            {
                                $billItem[ $column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-final_value' ]  = number_format($billItemTypeRefFormulatedColumn['final_value'], 2, '.', '');
                                $billItem[ $column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-value' ]        = BillItemTypeReferenceFormulatedColumnTable::getConvertedValue($billItemTypeRefFormulatedColumn['value']);
                                $billItem[ $column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-linked' ]       = $billItemTypeRefFormulatedColumn['linked'];
                                $billItem[ $column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-has_build_up' ] = $billItemTypeRefFormulatedColumn['has_build_up'];
                                $billItem[ $column->id . '-' . $billItemTypeRefFormulatedColumn['column_name'] . '-has_formula' ]  = $billItemTypeRefFormulatedColumn && $billItemTypeRefFormulatedColumn['value'] != $billItemTypeRefFormulatedColumn['final_value'] ? true : false;

                                foreach(json_decode($tendererIds) as $tendererId)
                                {
                                    if( ! isset( $contractorRates[ $tendererId ][ $billItem['id'] ] ) )
                                    {
                                        continue;
                                    }
                                    $tendererRate                                                    = $contractorRates[ $tendererId ][ $billItem['id'] ][0]['rate'];
                                    $billItem[ $tendererId . '-' . $column->id . '-total_per_unit' ] = $billItemTypeRefFormulatedColumn['final_value'] * $tendererRate;
                                }

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

                    $billItem[ $column->id . '-include' ]                      = $include;
                    $billItem[ $column->id . '-quantity_per_unit_difference' ] = $quantityPerUnitDiff;
                    $billItem[ $column->id . '-total_quantity' ]               = $totalQuantity;
                    $billItem[ $column->id . '-total_per_unit' ]               = $totalPerUnit;
                    $billItem[ $column->id . '-total' ]                        = $total;
                }

                $billItem['grand_total_after_markup'] = $grandTotalAfterMarkup;

                array_push($items, $billItem);
                unset( $billItem );
            }
        }

        unset( $elements );

        $defaultLastRow = array(
            'id'                   => Constants::GRID_LAST_ROW,
            'bill_ref'             => '',
            'description'          => '',
            'type'                 => (string)ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'               => '-1',
            'uom_symbol'           => '',
            'level'                => 0,
            'linked'               => false,
            'single_unit_quantity' => 0,
            'grand_total'          => 0,
            '_csrf_token'          => $form->getCSRFToken()
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetContractorsScheduleOfRate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $records = array();
        $tenderSetting = $project->TenderSetting;
        $contractorIds = json_decode($request->getParameter('contractorIds'), true);
        $sortBy = $request->getParameter('type');
        $pdo = $project->getTable()->getConnection()->getDbh();

        $awardedCompanyId = $tenderSetting->awarded_company_id > 0 ? $tenderSetting->awarded_company_id : -1;

        // strip off awarded company id
        if( ( $awardedCompanyKey = array_search($awardedCompanyId, $contractorIds) ) !== false )
        {
            unset( $contractorIds[ $awardedCompanyKey ] );
        }

        switch($sortBy)
        {
            case TenderSetting::SORT_CONTRACTOR_HIGHEST_LOWEST_TEXT:
                $sqlOrder = "total DESC";
                break;
            case TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST_TEXT:
                $sqlOrder = "total ASC";
                break;
            default:
                throw new Exception('invalid sort option');
        }

        if( ! empty( $contractorIds ) )
        {

            $stmt = $pdo->prepare("
                SELECT
                    companies.id,
                    companies.name,
                    tender_companies.id AS tender_company_id,
                    tender_companies.show,
                COALESCE(SUM(tender_items.contractor_rate), 0) AS total
                FROM " . CompanyTable::getInstance()->getTableName() . " companies
                JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tender_companies ON tender_companies.company_id = companies.id
                LEFT JOIN " . TenderScheduleOfRateTable::getInstance()->getTableName() . " tender_items ON tender_items.tender_company_id = tender_companies.id
                WHERE tender_companies.project_structure_id = " . $project->id . "
                AND companies.id IN (" . implode(', ', $contractorIds) . ")
                AND companies.deleted_at IS NULL
                GROUP BY companies.id, tender_companies.show, tender_companies.id
                ORDER BY " . $sqlOrder);

            $stmt->execute();

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $companies = array();

        // only fetch awarded company if selected only
        if( $tenderSetting->awarded_company_id > 0 AND $awardedCompanyKey !== false )
        {
            $awardedCompany = $tenderSetting->AwardedCompany;

            $company = array(
                'id'      => $awardedCompany->id,
                'name'    => $awardedCompany->name,
                'awarded' => true
            );

            array_push($companies, $company);

            unset( $company, $awardedCompany );
        }

        foreach($records as $key => $record)
        {
            $record['awarded'] = false;

            array_push($companies, $record);

            unset( $records[ $key ], $record );
        }

        return $this->renderJson($companies);
    }

    public function executeGetSelectedScheduleOfRateItemByAllTenderers(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $tendererIds = $request->getParameter('tendererIds');
        $itemIds = $request->getParameter('itemIds');
        $form = new BaseForm();
        $items = array();

        $itemIds = json_decode($itemIds, true);
        $tendererIds = json_decode($tendererIds, true);

        $elementIds = ScheduleOfRateBillItemTable::getInstance()->getElementIds($itemIds);
        $elementIds = ScheduleOfRateBillElementTable::getInstance()->sortBy($elementIds, 'priority');

        $contractorRates = ScheduleOfRateBillItemTable::getInstance()->getContractorRates($itemIds, $tendererIds);

        foreach($elementIds as $elementId)
        {
            $element = ScheduleOfRateBillElementTable::getInstance()->find($elementId);
            $elementRow = array(
                'id'          => 'element-' . $elementId,
                'bill_ref'    => null,
                'description' => $element->description,
                'type'        => 0,
            );
            array_push($items, $elementRow);

            $elementItems = ScheduleOfRateBillItemTable::getInstance()->getMatchingItemsByElementId($elementId, $itemIds);

            $elementItems = ScheduleOfRateBillItemTable::addContractorRates($elementItems, $contractorRates, $tendererIds);

            foreach($elementItems as $elementItem)
            {
                array_push($items, $elementItem);
            }
        }

        $defaultLastRow = array(
            'id'              => Constants::GRID_LAST_ROW,
            'description'     => '',
            'level'           => 0,
            'uom_symbol'      => '',
            'estimation_rate' => 0,
            '_csrf_token'     => $form->getCSRFToken()
        );

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    /**
     * Sets each tenderer's rate and grand total for the bill item.
     *
     * @param $contractorRates
     * @param $billItem
     */
    public function setContractorRates($contractorRates, &$billItem)
    {
        foreach($contractorRates as $contractorId => $rates)
        {
            $billItem[ $contractorId . '-rate-value' ] = 0;
            $billItem[ $contractorId . '-grand_total' ] = 0;

            if( array_key_exists($billItem['id'], $rates) )
            {
                $billItem[ $contractorId . '-rate-value' ] = $rates[ $billItem['id'] ][0]['rate'];
                $billItem[ $contractorId . '-grand_total' ] = $rates[ $billItem['id'] ][0]['grand_total'];
            }
        }
    }

    public function setContractorRatesRevisions(&$billItem, $revisions, $itemRateLog)
    {
        foreach($itemRateLog as $tendererId => $rateRevisions)
        {
            foreach($revisions as $revisionNumber => $revision)
            {
                if( isset($itemRateLog[ $tendererId ][ $revisionNumber ][$billItem['id']]) )
                {
                    $billItem[ $tendererId . '-rate-value_revision-' . $revisionNumber ]  = $itemRateLog[ $tendererId ][ $revisionNumber ][ $billItem['id'] ]['rate'];
                    $billItem[ $tendererId . '-grand_total_revision-' . $revisionNumber ] = $itemRateLog[ $tendererId ][ $revisionNumber ][ $billItem['id'] ]['grand_total'];
                }
            }
        }
    }

    /**
     * Set the flags for minimum and maximum cost for each bill item.
     * Set the flags for whether the rate is the highest or lowest for this bill item.
     *
     * @param $contractorRates
     * @param $billItem
     */
    public function flagMinAndMaxCost($contractorRates, &$billItem)
    {
        $tendererCostings = array();

        foreach($contractorRates as $contractorId => $rates)
        {
            $billItem[ $contractorId . '-lowest_cost' ] = false;
            $billItem[ $contractorId . '-highest_cost' ] = false;

            if( array_key_exists($billItem['id'], $rates) )
            {
                // both
                // $rates[ $billItem['id'] ][0]['rate']
                // and
                // $rates[ $billItem['id'] ][0]['total']
                // are the costs and are directly proportional to each other (i.e. total = rate * quantity).
                // Therefore both can be used to determine the tenderer with the highest and lowest costs.
                // In this case, we use rate.
                $tendererCostings[ $contractorId ] = $rates[ $billItem['id'] ][0]['rate'];

                unset( $rates, $contractorRates[ $contractorId ][ $billItem['id'] ] );
            }
        }

        // if more than 2 tenderers selected then only apply the assignment for the highest and lowest costing from tenderers
        if( $billItem['type'] != BillItem::TYPE_HEADER AND $billItem['type'] != BillItem::TYPE_HEADER_N AND count($tendererCostings) > 1 )
        {
            $minTotalIndex = array_keys($tendererCostings, min($tendererCostings));
            $maxTotalIndex = array_keys($tendererCostings, max($tendererCostings));

            // Only assign as lowest or highest if the value is unique.
            if(count($minTotalIndex) == 1)
            {
                $billItem[ $minTotalIndex[0] . '-lowest_cost' ] = true;
            }
            if(count($maxTotalIndex) == 1)
            {
                $billItem[ $maxTotalIndex[0] . '-highest_cost' ] = true;
            }
        }

        unset( $tendererCostings );
    }

    public function executeGetRevisions(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $revisions = $structure->getProjectRevisions()->toArray();

        return $this->renderJson(array(
            'revisions' => $revisions
        ));
    }
	// ============================================================================================================================================

    public function executeGetSupplyOfMaterialItemRate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $tendererIds  = json_decode($request->getParameter('tendererIds'));
        $itemIds      = json_decode($request->getParameter('itemIds'));

        if(empty($tendererIds)) $tendererIds = [0];
        if(empty($itemIds)) $itemIds = [0];

        $pdo  = $bill->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, e.id as element_id, i.description, i.type, i.lft, i.level, i.supply_rate, i.contractor_supply_rate,
            i.estimated_qty, i.percentage_of_wastage, i.difference, i.amount, uom.id AS uom_id, uom.symbol AS uom_symbol, i.note
            FROM " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " i
            JOIN " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b on b.id = e.project_structure_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE b.id = " . $bill->id . "
            AND i.id IN (" . implode(',', $itemIds) . ")
            AND b.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY b.priority, e.priority, i.priority, i.lft, i.level");

        $stmt->execute();

        $itemRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $itemsByElement = [];
        $elementIds 	= [];

        foreach ( $itemRecords as $item )
        {
            $itemsByElement[$item['element_id']][] = $item;

            $elementIds[$item['element_id']] = $item['element_id'];
        }

        if(empty($elementIds)) $elementIds = [0];

        $stmt = $pdo->prepare("SELECT e.id, e.description
            FROM " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e
            WHERE e.id IN (" . implode(',', $elementIds) . ")
            AND e.deleted_at IS NULL");

        $stmt->execute();

        $elementRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $elements = [];

        foreach($elementRecords as $element)
        {
            $elements[$element['id']] = array(
                'description' => $element['description'],
            );
        }

        $contractorSupplyRates = TenderCompanyTable::getDisplayedContractorSupplyOfMaterialItemRates($itemIds, $tendererIds);

        $output = [];

        foreach ( $itemsByElement as $elementId => $items )
        {
        	if ( isset ( $elements[$elementId] ) )
        	{
        		$output[] = array(
        			'id'          => 'element-' . $elementId,
        			'bill_ref'    => null,
        			'description' => $elements[$elementId]['description'],
        			'type'        => 0,
        		);
        	}

        	foreach($items as $key => $item)
        	{
        		$item['type']        = (string) $item['type'];
        		$item['uom_id']      = $item['uom_id'] > 0 ? (string) $item['uom_id'] : '-1';

        		foreach ( $contractorSupplyRates as $contractorId => $supplyRates )
        		{
        		    if ( array_key_exists($item['id'], $supplyRates) )
        		    {
        		        $item[$contractorId . '-contractor_supply_rate'] = $supplyRates[$item['id']][0]['contractor_supply_rate'];
        		        $item[$contractorId . '-estimated_qty']          = $supplyRates[$item['id']][0]['estimated_qty'];
        		        $item[$contractorId . '-percentage_of_wastage']  = $supplyRates[$item['id']][0]['percentage_of_wastage'];
        		        $item[$contractorId . '-difference']             = $supplyRates[$item['id']][0]['difference'];
        		        $item[$contractorId . '-amount']                 = $supplyRates[$item['id']][0]['amount'];

        		        unset( $supplyRates, $contractorSupplyRates[$contractorId][$item['id']] );
        		    }
        		    else
        		    {
        		        $item[$contractorId . '-contractor_supply_rate'] = 0;
        		        $item[$contractorId . '-estimated_qty']          = 0;
        		        $item[$contractorId . '-percentage_of_wastage']  = 0;
        		        $item[$contractorId . '-difference']             = 0;
        		        $item[$contractorId . '-amount']                 = 0;
        		    }
        		}

        		$output[] = $item;

        		unset(
        		$item['contractor_supply_rate'],
        		$item['estimated_qty'],
        		$item['percentage_of_wastage'],
        		$item['difference'],
        		$item['amount']
        		);
        	}
        }

        $defaultLastRow = array(
            'id'                     => Constants::GRID_LAST_ROW,
            'description'            => '',
            'type'                   => (string)SupplyOfMaterialItem::TYPE_WORK_ITEM,
            'uom_id'                 => '-1',
            'uom_symbol'             => '',
            'level'                  => 0,
            'supply_rate'            => '',
            'contractor_supply_rate' => 0,
            'estimated_qty'          => 0,
            'percentage_of_wastage'  => 0,
            'difference'             =>  0,
            'amount'                 => 0,
        );

        foreach ( $contractorSupplyRates as $contractorId => $supplyRates )
        {
            $defaultLastRow[$contractorId . '-contractor_supply_rate'] = 0;
            $defaultLastRow[$contractorId . '-estimated_qty']          = 0;
            $defaultLastRow[$contractorId . '-percentage_of_wastage']  = 0;
            $defaultLastRow[$contractorId . '-difference']             = 0;
            $defaultLastRow[$contractorId . '-amount']                 = 0;
        }

        array_push($output, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $output
        ));
    }
}