<?php

/**
 * postContractSubPackagePreliminaries actions.
 *
 * @package    buildspace
 * @subpackage postContractSubPackagePreliminaries
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractSubPackagePreliminariesActions extends BaseActions {

	public function executeGetPrintingSelectedItem(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$itemIds                      = $request->getParameter('itemIds');
		$items                        = array();
		$pageNoPrefix                 = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType                 = $bill->BillMarkupSetting->rounding_type;
		$column                       = $bill->BillColumnSettings->toArray();
		$claimProjectRevision         = SubPackagePostContractClaimRevisionTable::getCurrentProjectRevision($subPackage);
		$selectedClaimProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
		$elements                     = BillElementTable::getAffectedElementIdsByItemIds($itemIds);

		foreach ( $elements as $elementId => $element )
		{
			$generatedHeader = false;

			$fakeObjectElement     = new BillElement();
			$fakeObjectElement->id = $elementId;

			list(
				$billItems, $billItemTypeReferences, $billItemTypeRefFormulatedColumns, $initialCostings, $timeBasedCostings, $prevTimeBasedCostings, $workBasedCostings, $prevWorkBasedCostings, $finalCostings, $includeInitialCostings, $includeFinalCostings
				) = SubPackagePostContractBillItemRateTable::getPrintPreviewDataStructureForPrelimBillItemList($subPackage, $fakeObjectElement, $bill, $itemIds);

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

				$billItem['bill_ref']             = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                 = (string) $billItem['type'];
				$billItem['uom_id']               = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['linked']               = false;
				$billItem['has_note']             = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
				$billItem['item_total']           = Utilities::prelimRounding($billItem['grand_total']);
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

							unset( $billItemTypeRefFormulatedColumn );
						}
					}

					unset( $billItemTypeRef );
				}

				SubPackagePreliminariesClaimTable::calculateClaimRates($selectedClaimProjectRevision, $billItem, $claimProjectRevision, $initialCostings, $finalCostings, $timeBasedCostings, $workBasedCostings, $prevTimeBasedCostings, $prevWorkBasedCostings, $includeInitialCostings, $includeFinalCostings);

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

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}

	public function executeGetPrintingItemWithCurrentClaimMoreThanZero(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$items                = array();
		$pageNoPrefix         = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType         = $bill->BillMarkupSetting->rounding_type;
		$column               = $bill->BillColumnSettings->toArray();
		$claimProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentProjectRevision($subPackage);

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
				) = SubPackagePostContractBillItemRateTable::getPrintingPreviewDataStructureForPrelimBillItemListByClaimType($subPackage, $fakeObjectElement, $bill, 'currentClaim-amount');

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
				$billItem['qty-qty_per_unit'] = 0;
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

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}

	public function executeGetPrintingAllItemClaims(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$items                = array();
		$pageNoPrefix         = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType         = $bill->BillMarkupSetting->rounding_type;
		$column               = $bill->BillColumnSettings->toArray();
		$claimProjectRevision = SubPackagePostContractClaimRevisionTable::getCurrentProjectRevision($subPackage);

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
				) = SubPackagePostContractBillItemRateTable::getPrintingPreviewDataStructureForPrelimBillItemListByClaimType($subPackage, $fakeObjectElement, $bill, 'upToDateClaim-amount');

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
				$billItem['qty-qty_per_unit'] = 0;
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

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}

	public function executePrintPrelimSelectedItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);

		$priceFormat      = $request->getParameter('priceFormat');
		$printNoCents     = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$affectedElements = BillElementTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows'));

		$reportPrintGenerator = new sfBuildspacePostContractSubPackagePrelimReportPageItemGenerator($subPackage, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, null);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;

		if ( empty( $pages ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = 'Sorry, there are no item(s) to be printed.';

			$this->setTemplate('nothingToPrint');

			return sfView::SUCCESS;
		}

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

		$stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

		$pdfGen = new WkHtmlToPdf($params);

		$pageCount = 1;

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
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'printGrandTotal'            => $printGrandTotal,
						'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
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

					$layout .= $this->getPartial('printReport/postContractPrelimClaimReportItem', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPrelimSelectedItemWithClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$affectedElements  = BillElementTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows'));
		$type              = null;

		$reportPrintGenerator = new sfBuildspacePostContractSubPackagePrelimReportPageItemGenerator($subPackage, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;

		if ( empty( $pages ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = 'Sorry, there are no item(s) to be printed.';

			$this->setTemplate('nothingToPrint');

			return sfView::SUCCESS;
		}

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

		$stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

		$pdfGen = new WkHtmlToPdf($params);

		$pageCount = 1;

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
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'printGrandTotal'            => $printGrandTotal,
						'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
						'topLeftRow2'                => $bill->title,
						'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
						'reportTitle'                => $printingPageTitle,
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
						'type'                       => $type,
					);

					$layout .= $this->getPartial('printReport/postContractPrelimClaimReportItemWithClaim', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPrelimAllItemWithClaimMoreThanZero(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$type              = 'currentClaim-amount';

		// get available bill element(s)
		$affectedElements = Doctrine_Query::create()
			->select('e.id, e.description')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->orderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractSubPackagePrelimReportPageAllItemGeneratorMoreThanZero($subPackage, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;

		if ( empty( $pages ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = 'Sorry, there are no item(s) to be printed.';

			$this->setTemplate('nothingToPrint');

			return sfView::SUCCESS;
		}

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

		$stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

		$pdfGen = new WkHtmlToPdf($params);

		$pageCount = 1;

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
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'printGrandTotal'            => $printGrandTotal,
						'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
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
						'type'                       => $type,
					);

					$layout .= $this->getPartial('printReport/postContractPrelimClaimReportItemWithClaim', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractPrelimAllItemWithClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$type              = 'upToDateClaim-amount';

		// get available bill element(s)
		$affectedElements = Doctrine_Query::create()
			->select('e.id, e.description')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->orderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractSubPackagePrelimReportPageAllItemGenerator($subPackage, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;

		if ( empty( $pages ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = 'Sorry, there are no item(s) to be printed.';

			$this->setTemplate('nothingToPrint');

			return sfView::SUCCESS;
		}

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

		$stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

		$pdfGen = new WkHtmlToPdf($params);

		$pageCount = 1;

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
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'printGrandTotal'            => $printGrandTotal,
						'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
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
						'type'                       => $type,
					);

					$layout .= $this->getPartial('printReport/postContractPrelimClaimReportItemWithClaim', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

}