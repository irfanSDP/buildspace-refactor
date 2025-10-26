<?php

/**
 * tenderingReport actions.
 *
 * @package    buildspace
 * @subpackage tenderingReport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class tenderingReportActions extends BaseActions {

	public function executeGetAffectedItemsByElements(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$pdo        = $bill->getTable()->getConnection()->getDbh();
		$data       = array();
		$elementIds = Utilities::array_filter_integer(json_decode($request->getParameter('element_ids'), true));

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

	public function executePrintSelectedItemsQtyIncludingQty2(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$pdo                                   = $bill->getTable()->getConnection()->getDbh();
		$itemIds                               = $request->getParameter('selectedRows');
		$pageNoPrefix                          = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType                          = $bill->BillMarkupSetting->rounding_type;
		$billColumnSettings                    = $bill->BillColumnSettings;
		$stylesheet                            = $this->getBQStyling();
		$billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;

		list(
			$elements, $billItems, $formulatedColumns, $quantityPerUnitByColumns,
			$billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = BillItemTable::getDataStructureForBillItemListBySelectedItemIds($bill, $itemIds);

		if ( empty( $billItems ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = "Nothing can be printed because there are no item(s) selection detected.";

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		$reportPrintGenerator = new sfBillItemQtyIncludingQty2ReportGenerator($bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$pdfGen    = $this->createNewPDFGenerator($reportPrintGenerator);
		$maxRows   = $reportPrintGenerator->getMaxRows();
		$currency  = $reportPrintGenerator->getCurrency();
		$pageCount = 1;

		foreach ( $elements as $element )
		{
			if ( !isset( $billItems[$element['id']] ) )
			{
				continue;
			}

			$currentItems            = array();
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

			$markupSettingsInfo = $this->generateMarkupSettingsInfo($bill, $elementMarkupPercentage, $roundingType);

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

				foreach ( $billColumnSettings as $column )
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

					$include             = 'true';
					$totalQuantity       = 0;
					$quantityPerUnitDiff = 0;
					$totalPerUnit        = 0;
					$total               = 0;

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

				$currentItems[] = $billItem;

				unset( $billItem );
			}

			foreach ( $billColumnSettings as $column )
			{
				$columnPageCount = 1;

				$reportPrintGenerator->setCurrentColumn($column);
				$reportPrintGenerator->setItems($currentItems);

				// start importing data into report print generator
				$pages = $reportPrintGenerator->generatePages();

				foreach ( $pages as $page )
				{
					if ( empty( $page ) )
					{
						continue;
					}

					$lastPage = ( $columnPageCount == $pages->count() - 1 ) ? true : false;

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'lastPage'                   => $lastPage,
						'itemPage'                   => $page,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'pageCount'                  => $pageCount,
						'elementTitle'               => $element['description'],
						'printingPageTitle'          => $printingPageTitle,
						'billDescription'            => "{$bill->title} > {$column['name']}",
						'columnDescription'          => null,
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

					$layout .= $this->getPartial('itemQtyIncludingQty2Report', $billItemsLayoutParams);

					$pdfGen->addPage($layout);

					unset( $layout, $page );

					$pageCount ++;
					$columnPageCount ++;
				}
			}

			unset( $billItems[$element['id']], $element );
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedItemsAmountIncludingQty2(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$pdo                                   = $bill->getTable()->getConnection()->getDbh();
		$itemIds                               = $request->getParameter('selectedRows');
		$pageNoPrefix                          = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType                          = $bill->BillMarkupSetting->rounding_type;
		$billColumnSettings                    = $bill->BillColumnSettings;
		$stylesheet                            = $this->getBQStyling();
		$billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;

		list(
			$elements, $billItems, $formulatedColumns, $quantityPerUnitByColumns,
			$billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = BillItemTable::getDataStructureForBillItemListBySelectedItemIds($bill, $itemIds);

		if ( empty( $billItems ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = "Nothing can be printed because there are no item(s) selection detected.";

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		$reportPrintGenerator = new sfBillItemAmountIncludingQty2ReportGenerator($project, $bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$pdfGen    = $this->createNewPDFGenerator($reportPrintGenerator);
		$maxRows   = $reportPrintGenerator->getMaxRows();
		$currency  = $reportPrintGenerator->getCurrency();
		$pageCount = 1;

		foreach ( $elements as $element )
		{
			if ( !isset( $billItems[$element['id']] ) )
			{
				continue;
			}

			$currentItems            = array();
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

			$markupSettingsInfo = $this->generateMarkupSettingsInfo($bill, $elementMarkupPercentage, $roundingType);

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

				foreach ( $billColumnSettings as $column )
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

				$currentItems[] = $billItem;

				unset( $billItem );
			}

			foreach ( $billColumnSettings as $column )
			{
				$columnPageCount = 1;

				$reportPrintGenerator->setCurrentColumn($column);
				$reportPrintGenerator->setItems($currentItems);

				// start importing data into report print generator
				$pages = $reportPrintGenerator->generatePages();

				foreach ( $pages as $page )
				{
					if ( empty( $page ) )
					{
						continue;
					}

					$lastPage = ( $columnPageCount == $pages->count() - 1 ) ? true : false;

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'currentQtyOneAmount'        => $reportPrintGenerator->currentQtyOneAmount,
						'currentQtyTwoAmount'        => $reportPrintGenerator->currentQtyTwoAmount,
						'lastPage'                   => $lastPage,
						'itemPage'                   => $page,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'pageCount'                  => $pageCount,
						'elementTitle'               => $element['description'],
						'printingPageTitle'          => $printingPageTitle,
						'billDescription'            => "{$bill->title} > {$column['name']}",
						'columnDescription'          => null,
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

					$layout .= $this->getPartial('itemAmountIncludingQty2Report', $billItemsLayoutParams);

					$pdfGen->addPage($layout);

					unset( $layout, $page );

					$pageCount ++;
					$columnPageCount ++;
				}
			}

			unset( $billItems[$element['id']], $element );
		}

		return $pdfGen->send();
	}

	public function executeExportExcelSelectedItemsQtyIncludingQty2(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$pdo                                   = $bill->getTable()->getConnection()->getDbh();
		$itemIds                               = $request->getParameter('selectedRows');
		$pageNoPrefix                          = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType                          = $bill->BillMarkupSetting->rounding_type;
		$billColumnSettings                    = $bill->BillColumnSettings;
		$billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		list(
			$elements, $billItems, $formulatedColumns, $quantityPerUnitByColumns,
			$billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = BillItemTable::getDataStructureForBillItemListBySelectedItemIds($bill, $itemIds);

		$reportPrintGenerator = new sfBillItemQtyIncludingQty2ReportGenerator($bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$sfItemReportGenerator = new sfProjectItemQtyIncludingQty2ExcelGenerator(
			$project,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		if ( empty( $billItems ) )
		{
			$sfItemReportGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
		}

		foreach ( $elements as $element )
		{
			if ( !isset( $billItems[$element['id']] ) )
			{
				continue;
			}

			$currentItems            = array();
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

			$markupSettingsInfo = $this->generateMarkupSettingsInfo($bill, $elementMarkupPercentage, $roundingType);

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

				foreach ( $billColumnSettings as $column )
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

					$include             = 'true';
					$totalQuantity       = 0;
					$quantityPerUnitDiff = 0;
					$totalPerUnit        = 0;
					$total               = 0;

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

				$currentItems[] = $billItem;

				unset( $billItem );
			}

			foreach ( $billColumnSettings as $column )
			{
				$reportPrintGenerator->setCurrentColumn($column);
				$reportPrintGenerator->setItems($currentItems);

				// start importing data into report print generator
				$pages = $reportPrintGenerator->generatePages();

				$sfItemReportGenerator->process($pages, false, $printingPageTitle, $element['description'], "{$bill->title} > {$column['name']}", $printNoCents, $reportPrintGenerator->totalPage);
			}

			unset( $billItems[$element['id']], $element );
		}

		$sfItemReportGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
	}

	public function executeExportExcelSelectedItemsAmountIncludingQty2(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$pdo                                   = $bill->getTable()->getConnection()->getDbh();
		$itemIds                               = $request->getParameter('selectedRows');
		$pageNoPrefix                          = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType                          = $bill->BillMarkupSetting->rounding_type;
		$billColumnSettings                    = $bill->BillColumnSettings;
		$billItemTypeFormulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillItemTypeReference');

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		list(
			$elements, $billItems, $formulatedColumns, $quantityPerUnitByColumns,
			$billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = BillItemTable::getDataStructureForBillItemListBySelectedItemIds($bill, $itemIds);

		$reportPrintGenerator = new sfBillItemAmountIncludingQty2ReportGenerator($project, $bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$sfItemReportGenerator = new sfProjectItemAmtIncludingQty2ExcelGenerator(
			$project,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		if ( empty( $billItems ) )
		{
			$sfItemReportGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
		}

		foreach ( $elements as $element )
		{
			if ( !isset( $billItems[$element['id']] ) )
			{
				continue;
			}

			$currentItems            = array();
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

			$markupSettingsInfo = $this->generateMarkupSettingsInfo($bill, $elementMarkupPercentage, $roundingType);

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

				foreach ( $billColumnSettings as $column )
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

				$currentItems[] = $billItem;

				unset( $billItem );
			}

			foreach ( $billColumnSettings as $column )
			{
				$reportPrintGenerator->setCurrentColumn($column);
				$reportPrintGenerator->setItems($currentItems);

				// start importing data into report print generator
				$pages = $reportPrintGenerator->generatePages();

				$sfItemReportGenerator->setQty1TotalAmount($reportPrintGenerator->currentQtyOneAmount);
				$sfItemReportGenerator->setQty2TotalAmount($reportPrintGenerator->currentQtyTwoAmount);

				$sfItemReportGenerator->process($pages, false, $printingPageTitle, $element['description'], "{$bill->title} > {$column['name']}", $printNoCents, $reportPrintGenerator->totalPage);
			}

			unset( $billItems[$element['id']], $element );
		}

		$sfItemReportGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
	}

	private function generateMarkupSettingsInfo(ProjectStructure $bill, $elementMarkupPercentage, $roundingType)
	{
		return array(
			'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
			'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
			'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
			'element_markup_percentage' => $elementMarkupPercentage,
			'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
			'rounding_type'             => $roundingType
		);
	}

}