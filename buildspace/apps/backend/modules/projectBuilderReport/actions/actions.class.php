<?php

/**
 * projectBuilderReport actions.
 *
 * @package    buildspace
 * @subpackage projectBuilderReport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectBuilderReportActions extends BaseActions {

	public function executeMainInfoForm(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest() and $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

		$data = array(
			'title'         => $structure->title,
			'description'   => $structure->MainInformation->description,
			'region'        => ProjectMainInformation::getCountryNameById($structure->MainInformation->region_id),
			'subregion'     => ProjectMainInformation::getStateNameById($structure->MainInformation->subregion_id),
			'work_category' => ProjectMainInformation::getWorkCategoryById($structure->MainInformation->work_category_id),
			'site_address'  => $structure->MainInformation->site_address,
			'client'        => $structure->MainInformation->client,
			'start_date'    => $structure->MainInformation->start_date ? date('Y-m-d', strtotime($structure->MainInformation->start_date)) : date('Y-m-d')
		);

		if ( $structure->MainInformation->currency_id )
		{
			$data['currency'] = $structure->MainInformation->Currency->currency_code;
		}

		$data['eProjectReference'] = ($structure->MainInformation->EProjectProject) ? $structure->MainInformation->EProjectProject->reference : null;
		$data['isProjectOwner']    = ( $structure->created_by == $this->getUser()->getGuardUser()->getId() ) ? true : false;
		$data['isSuperAdmin']      = $this->getUser()->getGuardUser()->getIsSuperAdmin();

		return $this->renderJson($data);
	}

	public function executeGetAffectedItemsByElements(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$pdo        = $bill->getTable()->getConnection()->getDbh();
		$data       = array();
		$elementIds = json_decode($request->getParameter('element_ids'), true);

		if ( !empty( $elementIds ) )
		{
			$stmt = $pdo->prepare("SELECT i.id, i.element_id, i.priority, i.lft, i.level
			FROM " . BillItemTable::getInstance()->getTableName() . " i
			WHERE i.element_id IN (" . implode(',', $elementIds) . ") AND i.project_revision_deleted_at IS NULL
			AND i.deleted_at IS NULL ORDER BY i.priority, i.lft, i.level");

			$stmt->execute(array());
			$billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $billItems as $billItem )
			{
				$data[$billItem['element_id']][] = $billItem['id'];
			}
		}

		return $this->renderJson($data);
	}

	public function executeGetAffectedElementsByItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$pdo     = $bill->getTable()->getConnection()->getDbh();
		$data    = array();
		$itemIds = json_decode($request->getParameter('item_ids'), true);

		if ( !empty( $itemIds ) )
		{
			$stmt = $pdo->prepare("SELECT i.id, i.element_id, i.priority, i.lft, i.level
			FROM " . BillItemTable::getInstance()->getTableName() . " i
			WHERE i.id IN (" . implode(',', $itemIds) . ") AND i.project_revision_deleted_at IS NULL
			AND i.deleted_at IS NULL ORDER BY i.priority, i.lft, i.level");

			$stmt->execute(array());
			$billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $billItems as $billItem )
			{
				$data[$billItem['element_id']][] = $billItem['id'];
			}
		}

		return $this->renderJson($data);
	}

	public function executeGetPrintPreviewSelectedItemsWithBuildUpQty(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('itemIds') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$pdo                                   = $bill->getTable()->getConnection()->getDbh();
		$records                               = array();
		$itemIds                               = $request->getParameter('itemIds');
		$pageNoPrefix                          = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType                          = $bill->BillMarkupSetting->rounding_type;
		$formulatedColumnConstants             = Utilities::getAllFormulatedColumnConstants('BillItem');
		$billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

		list(
			$elementIds, $billItems, $formulatedColumns, $quantityPerUnitByColumns,
			$billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = BillItemTable::getSelectedItemsWithBuildUpQty($bill, $itemIds);

		if ( !empty( $billItems ) )
		{
			$stmt = $pdo->prepare("SELECT e.id, e.description FROM " . BillElementTable::getInstance()->getTableName() . " e
			WHERE e.id IN (" . implode(',', $elementIds) . ") AND e.deleted_at IS NULL ORDER BY e.priority ASC");

			$stmt->execute(array());
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $elements as $element )
			{
				$elementMarkupPercentage = 0;
				$generatedHeader         = false;

				/*
				 * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
				 */
				if ( $bill->BillMarkupSetting->element_markup_enabled )
				{
					$sql = "SELECT COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
						JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
						WHERE e.id = " . $element['id'] . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
						AND c.deleted_at IS NULL AND e.deleted_at IS NULL";

					$stmt = $pdo->prepare($sql);
					$stmt->execute();

					$elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
					$elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;
				}

				$markupSettingsInfo = array(
					'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
					'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
					'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
					'element_markup_percentage' => $elementMarkupPercentage,
					'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
					'rounding_type'             => $roundingType
				);

				foreach ( $billItems as $billItem )
				{
					if ( $billItem['element_id'] == $element['id'] )
					{
						if ( !$generatedHeader )
						{
							$generatedHeader = true;

							$headerRow = array(
								'id'                   => 'element-' . $element['id'],
								'bill_ref'             => '',
								'description'          => $element['description'],
								'type'                 => - 1,
								'uom_id'               => - 1,
								'uom_symbol'           => '',
								'grand_total_quantity' => 0,
								'sign_symbol'          => '',
							);

							foreach ( $formulatedColumnConstants as $constant )
							{
								$headerRow[$constant . '-final_value']        = 0;
								$headerRow[$constant . '-value']              = 0;
								$headerRow[$constant . '-linked']             = false;
								$headerRow[$constant . '-has_build_up']       = false;
								$headerRow[$constant . '-has_cell_reference'] = false;
								$headerRow[$constant . '-has_formula']        = false;
							}

							foreach ( $bill->BillColumnSettings as $column )
							{
								$headerRow[$column->id . '-include']                      = 'true';
								$headerRow[$column->id . '-quantity_per_unit_difference'] = 0;
								$headerRow[$column->id . '-total_quantity']               = 0;
								$headerRow[$column->id . '-total_per_unit']               = 0;
								$headerRow[$column->id . '-total']                        = 0;

								foreach ( $billItemTypeFormulatedColumnConstants as $constant )
								{
									$headerRow[$column->id . '-' . $constant . '-final_value']        = 0;
									$headerRow[$column->id . '-' . $constant . '-value']              = 0;
									$headerRow[$column->id . '-' . $constant . '-has_cell_reference'] = false;
									$headerRow[$column->id . '-' . $constant . '-has_formula']        = false;
									$headerRow[$column->id . '-' . $constant . '-linked']             = false;
									$headerRow[$column->id . '-' . $constant . '-has_build_up']       = false;
								}
							}

							$records[] = $headerRow;
						}

						$rate                  = 0;
						$rateAfterMarkup       = 0;
						$itemMarkupPercentage  = 0;
						$grandTotalAfterMarkup = 0;

						$billItem['bill_ref']             = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
						$billItem['type']                 = (string) $billItem['type'];
						$billItem['uom_id']               = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
						$billItem['relation_id']          = $element['id'];
						$billItem['linked']               = false;
						$billItem['markup_rounding_type'] = $roundingType;

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
							$include             = 'true';//default value is true
							$totalQuantity       = 0;
							$quantityPerUnitDiff = 0;
							$totalPerUnit        = 0;
							$total               = 0;
							$quantityPerUnit     = 0;

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

							$grandTotalAfterMarkup += $total;

							$billItem[$column->id . '-include']                      = $include;
							$billItem[$column->id . '-quantity_per_unit_difference'] = $quantityPerUnitDiff;
							$billItem[$column->id . '-total_quantity']               = $totalQuantity;
							$billItem[$column->id . '-total_per_unit']               = $totalPerUnit;
							$billItem[$column->id . '-total']                        = $total;
						}

						$billItem['grand_total_after_markup'] = $grandTotalAfterMarkup;

						array_push($records, $billItem);
						unset( $billItem );
					}
				}

				unset( $element );
			}

			unset( $billItems, $elements );
		}

		// empty row
		$defaultLastRow = array(
			'id'                   => Constants::GRID_LAST_ROW,
			'bill_ref'             => '',
			'description'          => '',
			'type'                 => BillItem::TYPE_WORK_ITEM,
			'uom_id'               => - 1,
			'uom_symbol'           => '',
			'grand_total_quantity' => 0,
			'sign_symbol'          => '',
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

		$records[] = $defaultLastRow;

		$data['identifier'] = 'id';
		$data['items']      = $records;

		return $this->renderJson($data);
	}

	public function executeGetPrintPreviewSelectedItemsQtyIncludingQty2(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('itemIds') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$pdo                                   = $bill->getTable()->getConnection()->getDbh();
		$itemIds                               = $request->getParameter('itemIds');
		$pageNoPrefix                          = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType                          = $bill->BillMarkupSetting->rounding_type;
		$items                                 = array();
		$billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

		list(
			$elements, $billItems, $formulatedColumns, $quantityPerUnitByColumns,
			$billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = BillItemTable::getDataStructureForBillItemListBySelectedItemIds($bill, $itemIds);

		foreach ( $elements as $element )
		{
			if ( !isset( $billItems[$element['id']] ) )
			{
				continue;
			}

			$elementMarkupPercentage = 0;

			if ( $bill->BillMarkupSetting->element_markup_enabled )
			{
				$stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value
				FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
				JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
				WHERE e.id = " . $element['id'] . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
				AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

				$stmt->execute();

				$elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
				$elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;
			}

			$markupSettingsInfo = array(
				'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
				'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
				'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
				'element_markup_percentage' => $elementMarkupPercentage,
				'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
				'rounding_type'             => $roundingType
			);

			$headerRow = array(
				'id'                   => 'element-' . $element['id'],
				'bill_ref'             => '',
				'description'          => $element['description'],
				'type'                 => - 1,
				'uom_id'               => - 1,
				'uom_symbol'           => '',
				'grand_total_quantity' => 0,
				'sign_symbol'          => '',
			);

			foreach ( $bill->BillColumnSettings as $column )
			{
				$headerRow[$column->id . '-include']                      = 'true';
				$headerRow[$column->id . '-quantity_per_unit_difference'] = 0;
				$headerRow[$column->id . '-total_quantity']               = 0;
				$headerRow[$column->id . '-total_per_unit']               = 0;
				$headerRow[$column->id . '-total']                        = 0;

				foreach ( $billItemTypeFormulatedColumnConstants as $constant )
				{
					$headerRow[$column->id . '-' . $constant . '-final_value']        = 0;
					$headerRow[$column->id . '-' . $constant . '-value']              = 0;
					$headerRow[$column->id . '-' . $constant . '-has_cell_reference'] = false;
					$headerRow[$column->id . '-' . $constant . '-has_formula']        = false;
					$headerRow[$column->id . '-' . $constant . '-linked']             = false;
					$headerRow[$column->id . '-' . $constant . '-has_build_up']       = false;
				}
			}

			$items[] = $headerRow;

			foreach ( $billItems[$element['id']] as $billItem )
			{
				$rate                  = 0;
				$rateAfterMarkup       = 0;
				$itemMarkupPercentage  = 0;
				$grandTotalAfterMarkup = 0;

				$billItem['bill_ref'] = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']     = (string) $billItem['type'];
				$billItem['uom_id']   = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['linked']   = false;

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

					$include               = 'true';
					$totalQuantity         = 0;
					$quantityPerUnitDiff   = 0;
					$totalPerUnit          = 0;
					$total                 = 0;
					$remeasureTotalPerUnit = 0;

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

						$remeasureTotalPerUnit = number_format($rateAfterMarkup * $billItem[$column->id . '-quantity_per_unit_remeasurement-final_value'], 2, '.', '');
					}

					$grandTotalAfterMarkup += $total;

					$billItem[$column->id . '-include']                      = $include;
					$billItem[$column->id . '-quantity_per_unit_difference'] = $quantityPerUnitDiff;
					$billItem[$column->id . '-total_quantity']               = $totalQuantity;
					$billItem[$column->id . '-total_per_unit']               = $totalPerUnit;
					$billItem[$column->id . '-remeasure_total_per_unit']     = $remeasureTotalPerUnit;
					$billItem[$column->id . '-total_per_unit_difference']    = Utilities::percent($remeasureTotalPerUnit - $totalPerUnit, $totalPerUnit);
					$billItem[$column->id . '-total']                        = $total;
				}

				$billItem['grand_total_after_markup'] = $grandTotalAfterMarkup;

				array_push($items, $billItem);

				unset( $billItem );
			}

			unset( $billItems[$element['id']], $element );
		}

		unset( $elements, $billItems );

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
			'grand_total_quantity'     => '',
			'grand_total'              => '',
			'linked'                   => false,
			'rate_after_markup'        => 0,
			'version'                  => - 1,
			'grand_total_after_markup' => 0,
		);

		foreach ( $bill->BillColumnSettings as $column )
		{
			$defaultLastRow[$column->id . '-include']                      = 'true';
			$defaultLastRow[$column->id . '-quantity_per_unit_difference'] = 0;
			$defaultLastRow[$column->id . '-total_per_unit_difference']    = 0;
			$defaultLastRow[$column->id . '-total_quantity']               = 0;
			$defaultLastRow[$column->id . '-total_per_unit']               = 0;
			$defaultLastRow[$column->id . '-remeasure_total_per_unit']     = 0;
			$defaultLastRow[$column->id . '-total_per_unit_difference']    = 0;
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

	public function executeGetPrintPreviewSelectedItemsWithBuildUpRates(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('itemIds') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$pdo                                   = $bill->getTable()->getConnection()->getDbh();
		$records                               = array();
		$itemIds                               = $request->getParameter('itemIds');
		$pageNoPrefix                          = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType                          = $bill->BillMarkupSetting->rounding_type;
		$formulatedColumnConstants             = Utilities::getAllFormulatedColumnConstants('BillItem');
		$billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

		list(
			$elementIds, $billItems, $formulatedColumns
			) = BillItemTable::getSelectedItemsWithBuildUpRates($bill, $itemIds);

		if ( !empty( $billItems ) )
		{
			$stmt = $pdo->prepare("SELECT e.id, e.description FROM " . BillElementTable::getInstance()->getTableName() . " e
			WHERE e.id IN (" . implode(',', $elementIds) . ") AND e.deleted_at IS NULL ORDER BY e.priority ASC");

			$stmt->execute(array());
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $elements as $element )
			{
				$elementId               = $element['id'];
				$elementMarkupPercentage = 0;
				$generatedHeader         = false;

				/*
				 * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
				 */
				if ( $bill->BillMarkupSetting->element_markup_enabled )
				{
					$stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
					JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
					WHERE e.id = " . $element['id'] . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
					AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

					$stmt->execute();

					$elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
					$elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;
				}

				$markupSettingsInfo = array(
					'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
					'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
					'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
					'element_markup_percentage' => $elementMarkupPercentage,
					'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
					'rounding_type'             => $roundingType
				);

				foreach ( $billItems as $billItem )
				{
					if ( $billItem['element_id'] != $elementId )
					{
						continue;
					}

					$billItemId = $billItem['id'];

					if ( !$generatedHeader )
					{
						$generatedHeader = true;

						$headerRow = array(
							'id'                   => 'element-' . $elementId,
							'bill_ref'             => '',
							'description'          => $element['description'],
							'type'                 => - 1,
							'uom_id'               => - 1,
							'uom_symbol'           => '',
							'grand_total_quantity' => 0,
							'sign_symbol'          => '',
							'rate-after_markup'    => 0,
						);

						foreach ( $formulatedColumnConstants as $constant )
						{
							$headerRow[$constant . '-final_value']        = 0;
							$headerRow[$constant . '-value']              = 0;
							$headerRow[$constant . '-linked']             = false;
							$headerRow[$constant . '-has_build_up']       = false;
							$headerRow[$constant . '-has_cell_reference'] = false;
							$headerRow[$constant . '-has_formula']        = false;
						}

						foreach ( $bill->BillColumnSettings as $column )
						{
							$headerRow[$column->id . '-include']                      = 'true';
							$headerRow[$column->id . '-quantity_per_unit_difference'] = 0;
							$headerRow[$column->id . '-total_per_unit_difference']    = 0;
							$headerRow[$column->id . '-remeasure_total_per_unit']     = 0;
							$headerRow[$column->id . '-total_quantity']               = 0;
							$headerRow[$column->id . '-total_per_unit']               = 0;
							$headerRow[$column->id . '-total_per_unit_difference']    = 0;
							$headerRow[$column->id . '-total']                        = 0;

							foreach ( $billItemTypeFormulatedColumnConstants as $constant )
							{
								$headerRow[$column->id . '-' . $constant . '-final_value']        = 0;
								$headerRow[$column->id . '-' . $constant . '-value']              = 0;
								$headerRow[$column->id . '-' . $constant . '-has_cell_reference'] = false;
								$headerRow[$column->id . '-' . $constant . '-has_formula']        = false;
								$headerRow[$column->id . '-' . $constant . '-linked']             = false;
								$headerRow[$column->id . '-' . $constant . '-has_build_up']       = false;
							}
						}

						$records[] = $headerRow;

						unset( $headerRow );
					}

					$rate                  = 0;
					$rateAfterMarkup       = 0;
					$itemMarkupPercentage  = 0;
					$grandTotalAfterMarkup = 0;

					$billItem['bill_ref'] = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
					$billItem['type']     = (string) $billItem['type'];
					$billItem['uom_id']   = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';

					foreach ( $formulatedColumnConstants as $constant )
					{
						$billItem[$constant . '-final_value']        = 0;
						$billItem[$constant . '-value']              = '';
						$billItem[$constant . '-has_cell_reference'] = false;
						$billItem[$constant . '-has_formula']        = false;
						$billItem[$constant . '-linked']             = false;
						$billItem[$constant . '-has_build_up']       = false;
					}

					if ( array_key_exists($billItemId, $formulatedColumns) )
					{
						$itemFormulatedColumns = $formulatedColumns[$billItemId];

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

						unset( $formulatedColumns[$billItemId], $itemFormulatedColumns );

						$rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
					}

					$billItem['rate-after_markup']        = $rateAfterMarkup;
					$billItem['grand_total_after_markup'] = $grandTotalAfterMarkup;

					$records[] = $billItem;

					unset( $billItem );
				}

				unset( $element );
			}

			unset( $billItems, $elements );
		}

		// empty row
		$defaultLastRow = array(
			'id'                   => Constants::GRID_LAST_ROW,
			'bill_ref'             => '',
			'description'          => '',
			'type'                 => BillItem::TYPE_WORK_ITEM,
			'uom_id'               => - 1,
			'uom_symbol'           => '',
			'grand_total_quantity' => 0,
			'sign_symbol'          => '',
			'rate_after_markup'    => 0,
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

		$records[] = $defaultLastRow;

		$data['identifier'] = 'id';
		$data['items']      = $records;

		return $this->renderJson($data);
	}

	public function executePrintSelectedElementsEstimateSummaryByTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$elementIds                    = json_decode($request->getParameter('selectedRows'), true);
		$billColumnSettings            = $bill->BillColumnSettings;
		$elementSumByBillColumnSetting = array();
		$totalRateByBillColumnSetting  = array();
		$newAllElements                = array();
		$elements                      = array();
		$elementsOrdering              = 1;
		$pageCount                     = 1;

		$stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;

		if ( !empty( $elementIds ) )
		{
			$allElements = DoctrineQuery::create()
				->select('e.id, e.description, e.priority')
				->from('BillElement e')
				->andWhere('e.project_structure_id = ?', $bill->id)
				->addOrderBy('e.priority ASC')
				->fetchArray();

			foreach ( $allElements as $allElement )
			{
				$newAllElements[$allElement['id']] = $elementsOrdering;

				$elementsOrdering ++;
			}

			unset( $allElements );

			$elements = DoctrineQuery::create()
				->select('e.id, e.description, e.priority')
				->from('BillElement e')
				->whereIn('e.id', $elementIds)
				->andWhere('e.project_structure_id = ?', $bill->id)
				->addOrderBy('e.priority ASC')
				->fetchArray();

			//we get sum of elements total by bill column setting so we won't keep on calling the same query in element list loop
			foreach ( $billColumnSettings as $column )
			{
				//Get Element Total Rates
				$elementTotalRates = ProjectStructureTable::getTotalItemRateByAndBillColumnSettingIdGroupByElement($bill, $column);

				$elementSumByBillColumnSetting[$column->id] = $elementTotalRates['grandTotalElement'];
				$totalRateByBillColumnSetting[$column->id]  = $elementTotalRates['elementToRates'];

				unset( $column, $elementTotalRates );
			}

			foreach ( $elements as $elementKey => $element )
			{
				// assign normal ordering for element(s)
				if ( isset ( $newAllElements[$element['id']] ) )
				{
					$elements[$elementKey]['priority'] = $newAllElements[$element['id']];

					unset( $newAllElements[$element['id']] );
				}

				foreach ( $billColumnSettings as $column )
				{
					$total        = $totalRateByBillColumnSetting[$column->id][$element['id']][0]['total_rate_after_markup'];
					$totalPerUnit = $total / $column->quantity;

					$elements[$elementKey][$column->id . '-total_per_unit']    = $totalPerUnit;
					$elements[$elementKey][$column->id . '-total']             = $total;
					$elements[$elementKey][$column->id . '-total_cost']        = $column->getTotalCostPerFloorArea($totalPerUnit);
					$elements[$elementKey][$column->id . '-element_sum_total'] = $elementSumByBillColumnSetting[$column->id];

					unset( $column );
				}

				unset( $element );
			}
		}

		$reportPrintGenerator = new sfBillElementEstimateSummaryByTypeReportGenerator($bill);
		$reportPrintGenerator->setOrientationAndSize('portrait');
		$maxRows  = $reportPrintGenerator->getMaxRows();
		$currency = $reportPrintGenerator->getCurrency();

		$pdfGen = new WkHtmlToPdf(array(
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
			'orientation'    => $reportPrintGenerator->getOrientation(),
		));

		foreach ( $billColumnSettings as $column )
		{
			$billColumnPageCount = 2;

			$reportPrintGenerator->setElements($elements);
			$reportPrintGenerator->setBillColumn($column);

			$pages = $reportPrintGenerator->generatePages();

			if ( $pages instanceof SplFixedArray )
			{
				foreach ( $pages as $key => $page )
				{
					$lastPage = ( $billColumnPageCount == $pages->count() ) ? true : false;

					if ( empty( $page ) )
					{
						continue;
					}

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'percentageTotal'            => $reportPrintGenerator->getPercentageTotal(),
						'totalCostTotal'             => $reportPrintGenerator->getTotalCostTotal(),
						'overallElementTotal'        => $reportPrintGenerator->getOverallElementTotal(),
						'lastPage'                   => $lastPage,
						'billColumnSetting'          => $column,
						'itemPage'                   => $page,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'pageCount'                  => $pageCount,
						'projectTitle'               => $project->title,
						'printingPageTitle'          => $printingPageTitle,
						'billDescription'            => "{$bill->title} > {$column->name}",
						'columnDescription'          => null,
						'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
						'printNoPrice'               => $withoutPrice,
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

					$layout .= $this->getPartial('elementEstimateSummaryByType', $billItemsLayoutParams);

					$pdfGen->addPage($layout);

					unset( $layout, $page );

					$pageCount ++;
					$billColumnPageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintProjectEstimateSummary(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') and
			$structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		$records = DoctrineQuery::create()
			->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
			->from('ProjectStructure s')
			->leftJoin('s.BillType t')
			->leftJoin('s.BillColumnSettings c')
			->leftJoin('s.BillLayoutSetting bls')
			->where('s.lft >= ? AND s.rgt <= ?', array( $structure->lft, $structure->rgt ))
			->andWhere('s.root_id = ?', $structure->id)
			->andWhere('s.type <= ?', ProjectStructure::TYPE_BILL)
			->addOrderBy('s.lft ASC')
			->fetchArray();

		$count             = 0;
		$pageCount         = 2;
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;
		$projectSumTotal   = ProjectStructureTable::getOverallTotalForProject($structure->id);
		$stylesheet        = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

		$overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($structure);

		foreach ( $records as $key => $record )
		{
			$records[$key]['billLayoutSettingId'] = ( isset( $record['BillLayoutSetting']['id'] ) ) ? $record['BillLayoutSetting']['id'] : null;
			$count                                = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

			if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
			{
				$records[$key]['bill_type']   = $record['BillType']['type'];
				$records[$key]['bill_status'] = $record['BillType']['status'];
			}

			$records[$key]['count']                      = $record['type'] == ProjectStructure::TYPE_BILL ? $count : null;
			$records[$key]['original_total']             = $record['type'] == ProjectStructure::TYPE_BILL ? ProjectStructureTable::getOverallOriginalTotalByBillId($record['id']) : 0;
			$records[$key]['overall_total_after_markup'] = ($record['type'] == ProjectStructure::TYPE_BILL && array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;
			$records[$key]['bill_sum_total']             = $projectSumTotal;

			unset( $records[$key]['BillLayoutSetting'] );
			unset( $records[$key]['BillType'] );
			unset( $records[$key]['BillColumnSettings'] );
		}

		if ( isset ( $records[0] ) )
		{
			// always unset the first record (Project Name)
			unset( $records[0] );
		}

		// will pass records into generator to be process into printout
		$reportPrintGenerator = new sfBuildSpaceProjectEstimateSummaryReport($structure);
		$reportPrintGenerator->setOrientationAndSize('portrait');
		$maxRows  = $reportPrintGenerator->getMaxRows();
		$currency = $reportPrintGenerator->getCurrency();

		$reportPrintGenerator->setBillRecords($records);

		$pages = $reportPrintGenerator->generatePages();

		$pdfGen = new WkHtmlToPdf(array(
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
			'orientation'    => $reportPrintGenerator->getOrientation(),
		));

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $key => $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$lastPage = ( $pageCount == $pages->count() ) ? true : false;

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'lastPage'                   => $lastPage,
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'pageCount'                  => $pageCount - 1,
					'projectTitle'               => $structure->title,
					'printingPageTitle'          => $printingPageTitle,
					'billDescription'            => null,
					'columnDescription'          => null,
					'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
					'printNoPrice'               => $withoutPrice,
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
					'originalAmountTotal'        => $reportPrintGenerator->originalAmountTotal,
					'totalMarkUpPercent'         => $reportPrintGenerator->totalMarkUpPercent,
					'totalMarkUpTotal'           => $reportPrintGenerator->totalMarkUpTotal,
					'overallTotalTotal'          => $reportPrintGenerator->overallTotalTotal,
					'projectPercentTotal'        => $reportPrintGenerator->projectPercentTotal,
					'finalTotalMarkUpPercent'    => $reportPrintGenerator->finalTotalMarkUpPercent,
				);

				$layout .= $this->getPartial('projectEstimateSummary', $billItemsLayoutParams);

				$pdfGen->addPage($layout);

				unset( $layout, $page );

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executeExportExcelProjectEstimateSummary(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$bills = $project->getBills();

		$count                          = 0;
		$pageCount                      = 2;
		$printingPageTitle              = $request->getParameter('printingPageTitle');
		$printNoCents                   = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$projectSumTotal                = ProjectStructureTable::getOverallTotalForProject($project->id);
		$overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);

		foreach ( $bills as $key => $record )
		{
			$bills[$key]['billLayoutSettingId'] = ( isset( $record['BillLayoutSetting']['id'] ) ) ? $record['BillLayoutSetting']['id'] : null;
			$count                              = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

			if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($bills[$key]['BillType']) )
			{
				$bills[$key]['bill_type']   = $record['BillType']['type'];
				$bills[$key]['bill_status'] = $record['BillType']['status'];
			}

			$bills[$key]['count']                      = $record['type'] == ProjectStructure::TYPE_BILL ? $count : null;
			$bills[$key]['original_total']             = $record['type'] == ProjectStructure::TYPE_BILL ? ProjectStructureTable::getOverallOriginalTotalByBillId($record['id']) : 0;
			$bills[$key]['overall_total_after_markup'] = ($record['type'] == ProjectStructure::TYPE_BILL && array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;
			$bills[$key]['bill_sum_total']             = $projectSumTotal;

			unset( $bills[$key]['BillLayoutSetting'] );
			unset( $bills[$key]['BillType'] );
			unset( $bills[$key]['BillColumnSettings'] );
		}

		if ( isset ( $bills[0] ) )
		{
			// always unset the first record (Project Name)
			unset( $bills[0] );
		}

		// will pass records into generator to be process into printout
		$reportPrintGenerator = new sfBuildSpaceProjectEstimateSummaryReport($project);
		$reportPrintGenerator->setOrientationAndSize('portrait');
		$reportPrintGenerator->setBillRecords($bills);

		$pages = $reportPrintGenerator->generatePages();

		$sfItemReportGenerator = new sfBuildSpacePBEstimateSummaryReportExcelGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemReportGenerator->currency = $project->MainInformation->Currency->currency_code;

		if ( !( $pages instanceof SplFixedArray ) )
		{
			// return empty excel file to be downloaded
			$sfItemReportGenerator->finishExportProcess();

			// return download excel's response
			return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
		}

		foreach ( $pages as $key => $page )
		{
			$lastPage = ( $pageCount == $pages->count() ) ? true : false;

			if ( empty( $page ) )
			{
				continue;
			}

			$sfItemReportGenerator->isLastPage($lastPage);

			$sfItemReportGenerator->process($pages, false, $printingPageTitle, '', $project->title, $printNoCents);

			unset( $page );

			$pageCount ++;
		}

		$sfItemReportGenerator->finishExportProcess();

		// return download excel's response
		return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
	}

	public function executeExportExcelSelectedElementsEstimateSummaryByTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$elementIds                    = json_decode($request->getParameter('selectedRows'), true);
		$printingPageTitle             = $request->getParameter('printingPageTitle');
		$descriptionFormat             = $request->getParameter('descriptionFormat');
		$printNoCents                  = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$billColumnSettings            = $bill->BillColumnSettings;
		$elementSumByBillColumnSetting = array();
		$totalRateByBillColumnSetting  = array();
		$elementsPriority              = array();
		$elements                      = array();
		$elementsOrdering              = 1;
		$pageCount                     = 1;

		// assign priorities to all element(s) that are currently associated with the current bill
		foreach ( BillElementTable::getElementsByProjectStructure($bill) as $allElement )
		{
			$elementsPriority[$allElement['id']] = $elementsOrdering;

			$elementsOrdering ++;
		}

		if ( !empty( $elementIds ) )
		{
			$elements = DoctrineQuery::create()
				->select('e.id, e.description, e.priority')
				->from('BillElement e')
				->whereIn('e.id', $elementIds)
				->andWhere('e.project_structure_id = ?', $bill->id)
				->addOrderBy('e.priority ASC')
				->fetchArray();

			//we get sum of elements total by bill column setting so we won't keep on calling the same query in element list loop
			foreach ( $billColumnSettings as $column )
			{
				//Get Element Total Rates
				$elementTotalRates = ProjectStructureTable::getTotalItemRateByAndBillColumnSettingIdGroupByElement($bill, $column);

				$elementSumByBillColumnSetting[$column->id] = $elementTotalRates['grandTotalElement'];
				$totalRateByBillColumnSetting[$column->id]  = $elementTotalRates['elementToRates'];

				unset( $column, $elementTotalRates );
			}

			foreach ( $elements as $elementKey => $element )
			{
				// assign normal ordering for element(s)
				if ( isset ( $elementsPriority[$element['id']] ) )
				{
					$elements[$elementKey]['priority'] = $elementsPriority[$element['id']];

					unset( $elementsPriority[$element['id']] );
				}

				foreach ( $billColumnSettings as $column )
				{
					$total        = $totalRateByBillColumnSetting[$column->id][$element['id']][0]['total_rate_after_markup'];
					$totalPerUnit = $total / $column->quantity;

					$elements[$elementKey][$column->id . '-total_per_unit']    = $totalPerUnit;
					$elements[$elementKey][$column->id . '-total']             = $total;
					$elements[$elementKey][$column->id . '-total_cost']        = $column->getTotalCostPerFloorArea($totalPerUnit);
					$elements[$elementKey][$column->id . '-element_sum_total'] = $elementSumByBillColumnSetting[$column->id];

					unset( $column );
				}

				unset( $element );
			}
		}

		$reportPrintGenerator = new sfBillElementEstimateSummaryByTypeReportGenerator($bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$sfItemReportGenerator = new sfBuildSpacePBSelectedElementsEstimateSummaryByTypesReportExcelGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		foreach ( $billColumnSettings as $column )
		{
			$billColumnPageCount = 2;

			$reportPrintGenerator->setElements($elements);
			$reportPrintGenerator->setBillColumn($column);

			$pages      = $reportPrintGenerator->generatePages();
			$costMetric = $column['floor_area_display_metric'] ? 'Cost/m2' : 'Cost/ft2';

			$sfItemReportGenerator->setCostMetric($costMetric);

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			foreach ( $pages as $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$lastPage = ( $billColumnPageCount == $pages->count() ) ? true : false;

				$sfItemReportGenerator->isLastPage($lastPage);

				$sfItemReportGenerator->process($pages, false, $printingPageTitle, '', "{$bill->title} > {$column->name}", $printNoCents);

				unset( $page );

				$pageCount ++;
				$billColumnPageCount ++;
			}
		}

		$sfItemReportGenerator->finishExportProcess();

		// return download excel's response
		return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
	}

}