<?php

/**
 * resourceLibraryReporting actions.
 *
 * @package    buildspace
 * @subpackage resourceLibraryReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class resourceLibraryReportingActions extends BaseActions {

	public function executeGetAffectedItemsBySelectedTrades(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getPostParameter('libraryId'))
		);

		$tradeIds    = json_decode($request->getPostParameter('trade_ids'), true);
		$newTradeIds = array();
		$data        = array();
		$pdo         = $resource->getTable()->getConnection()->getDbh();

		if ( !empty( $tradeIds ) )
		{
			$trades = Doctrine_Query::create()
				->select('rt.id')
				->from('ResourceTrade rt')
				->whereIn('rt.id', $tradeIds)
				->andWhere('rt.resource_id = ?', array( $resource->id ))
				->orderBy('rt.priority ASC')
				->fetchArray();

			foreach ( $trades as $trade )
			{
				$newTradeIds[] = $trade['id'];

				unset( $trade );
			}

			unset( $trades );
		}

		if ( !empty( $newTradeIds ) )
		{
			// get affected item(s) by trades
			$stmt = $pdo->prepare("SELECT c.id, c.resource_trade_id, c.priority, c.lft, c.level
			FROM " . ResourceItemTable::getInstance()->getTableName() . " c
			WHERE c.resource_trade_id IN (" . implode(',', $newTradeIds) . ")
			AND c.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level");

			$stmt->execute();
			$resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$data = $this->massageCheckBoxResourceItemsData($resourceItems, $data);

			unset( $resourceItems );
		}

		return $this->renderJson($data);
	}

	public function executeGetAffectedTradesBySelectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getPostParameter('libraryId'))
		);

		$itemIds = json_decode($request->getPostParameter('item_ids'), true);
		$data    = array();
		$pdo     = $resource->getTable()->getConnection()->getDbh();

		if ( !empty( $itemIds ) )
		{
			// get affected item(s) by trades
			$stmt = $pdo->prepare("SELECT c.id, c.resource_trade_id, c.priority, c.lft, c.level
			FROM " . ResourceItemTable::getInstance()->getTableName() . " c
			JOIN " . ResourceTradeTable::getInstance()->getTableName() . " rt ON c.resource_trade_id = rt.id AND rt.deleted_at IS NULL
			WHERE rt.resource_id = " . $resource->id . " AND c.id IN (" . implode(',', $itemIds) . ")
			AND c.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level ASC");

			$stmt->execute();
			$resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$data = $this->massageCheckBoxResourceItemsData($resourceItems, $data);

			unset( $resourceItems );
		}

		return $this->renderJson($data);
	}

	/**
	 * @param $resourceItems
	 * @param $data
	 * @return mixed
	 */
	private function massageCheckBoxResourceItemsData(array $resourceItems, array $data)
	{
		foreach ( $resourceItems as $resourceItem )
		{
			$data[$resourceItem['resource_trade_id']][] = $resourceItem['id'];

			unset( $resourceItem );
		}

		return $data;
	}

	// ==============================================================================================================
	// Print Preview
	// ==============================================================================================================
	public function executeGetPrintPreviewSelectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getPostParameter('libraryId'))
		);

		$itemIds = (array) json_decode($request->getPostParameter('item_ids'), true);

		list(
			$trades, $items, $formulatedColumns
			) = ResourceItemTable::getSelectedItemsByItemIds($resource, $itemIds);

		// loop each available trade(s) first, so that we can assign each header before every item(s)
		$data = $this->massagePrintPreviewDataForItemLevel($trades, $items, $formulatedColumns);

		unset( $trades );

		$data['identifier'] = 'id';
		$data['items'][]    = $this->generateDefaultRow();

		return $this->renderJson($data);
	}

	public function executeGetPrintPreviewSelectedItemsWithSupplierRates(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getPostParameter('libraryId'))
		);

		$itemIds = (array) json_decode($request->getPostParameter('item_ids'), true);

		list(
			$trades, $items, $formulatedColumns
			) = ResourceItemTable::getSelectedItemsWithSupplierRatesByItemIds($resource, $itemIds);

		// loop each available trade(s) first, so that we can assign each header before every item(s)
		$data = $this->massagePrintPreviewDataForItemLevel($trades, $items, $formulatedColumns);

		unset( $trades );

		$data['identifier'] = 'id';
		$data['items'][]    = $this->generateDefaultRow();

		return $this->renderJson($data);
	}

	/**
	 * @return array
	 */
	private function generateDefaultRow()
	{
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ResourceItem');

		$defaultLastRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => '',
			'type'        => (string) ResourceItem::TYPE_WORK_ITEM,
			'uom_id'      => '-1',
			'uom_symbol'  => '',
			'level'       => 0,
		);

		foreach ( $formulatedColumnConstants as $constant )
		{
			$defaultLastRow[$constant . '-final_value']        = "";
			$defaultLastRow[$constant . '-value']              = "";
			$defaultLastRow[$constant . '-has_build_up']       = false;
			$defaultLastRow[$constant . '-has_cell_reference'] = false;
			$defaultLastRow[$constant . '-has_formula']        = false;
		}

		return $defaultLastRow;
	}

	/**
	 * @param $trades
	 * @param $items
	 * @param $formulatedColumns
	 * @return array
	 */
	private function massagePrintPreviewDataForItemLevel(array $trades, array $items, array $formulatedColumns)
	{
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ResourceItem');
		$data                      = array();
		$rateColumn                = ResourceItem::FORMULATED_COLUMN_RATE;

		foreach ( $trades as $trade )
		{
			$tradeId = $trade['id'];

			if ( !isset( $items[$tradeId] ) )
			{
				continue;
			}

			$tradeRow = array(
				'id'          => "trade-{$tradeId}",
				'description' => $trade['description'],
				'type'        => - 1,
				'uom_id'      => '-1',
				'uom_symbol'  => '',
				'level'       => 0,
			);

			foreach ( $formulatedColumnConstants as $constant )
			{
				$tradeRow[$constant . '-final_value']        = "";
				$tradeRow[$constant . '-value']              = "";
				$tradeRow[$constant . '-has_build_up']       = false;
				$tradeRow[$constant . '-has_cell_reference'] = false;
				$tradeRow[$constant . '-has_formula']        = false;
			}

			$data['items'][] = $tradeRow;

			unset( $tradeRow );

			// only attach trade header if there is item(s) available
			$tradeItems = $items[$tradeId];

			foreach ( $tradeItems as $resourceItem )
			{
				$resourceItem['type']       = (string) $resourceItem['type'];
				$resourceItem['uom_id']     = $resourceItem['uom_id'] > 0 ? (string) $resourceItem['uom_id'] : '-1';
				$resourceItem['uom_symbol'] = $resourceItem['uom_id'] > 0 ? $resourceItem['uom_symbol'] : '';

				foreach ( $formulatedColumnConstants as $constant )
				{
					$resourceItem[$constant . '-final_value']        = 0;
					$resourceItem[$constant . '-value']              = '';
					$resourceItem[$constant . '-has_cell_reference'] = false;
					$resourceItem[$constant . '-has_formula']        = false;
					$resourceItem[$constant . '-has_build_up']       = false;
				}

				if ( array_key_exists($resourceItem['id'], $formulatedColumns) )
				{
					foreach ( $formulatedColumns[$resourceItem['id']] as $formulatedColumn )
					{
						$columnName                                        = $formulatedColumn['column_name'];
						$resourceItem[$columnName . '-final_value']        = $formulatedColumn['final_value'];
						$resourceItem[$columnName . '-value']              = $formulatedColumn['value'];
						$resourceItem[$columnName . '-has_cell_reference'] = false;
						$resourceItem[$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
					}
				}

				$resourceItem[$rateColumn . '-has_build_up'] = ( $resourceItem['resource_item_selected_rate_id'] ) ? true : false;

				$data['items'][] = $resourceItem;

				unset( $formulatedColumns[$resourceItem['id']], $resourceItem );
			}

			unset( $tradeItems, $trade );
		}

		return $data;
	}
	// ==============================================================================================================

	// ==============================================================================================================
	// Report Generator
	// ==============================================================================================================
	public function executePrintingSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$resource = Doctrine_Core::getTable('Resource')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds                   = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle         = $request->getParameter('printingPageTitle');
		$descriptionFormat         = $request->getParameter('descriptionFormat');
		$priceFormat               = $request->getParameter('priceFormat');
		$printNoCents              = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice              = false;
		$stylesheet                = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ResourceItem');
		$tradeInfo                 = array();

		list(
			$trades, $items, $formulatedColumns
			) = ResourceItemTable::getSelectedItemsByItemIds($resource, $itemIds);

		if ( empty( $items ) )
		{
			$this->message     = 'Error';
			$this->explanation = 'Nothing can be printed because there were no selected item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		$reportPrintGenerator = new sfResourceLibraryItemReportGenerator($descriptionFormat);
		$currency             = $reportPrintGenerator->getCurrency();
		$pageCount            = 1;

		$reportPrintGenerator->setOrientationAndSize('landscape');

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

		foreach ( $trades as $trade )
		{
			$tradeInfo[$trade['id']] = $trade['description'];

			$tradeItems = $items[$trade['id']];

			if ( empty( $tradeItems ) )
			{
				continue;
			}

			foreach ( $tradeItems as $key => $tradeItem )
			{
				$tradeItems[$key]['type']       = (string) $tradeItem['type'];
				$tradeItems[$key]['uom_id']     = $tradeItem['uom_id'] > 0 ? (string) $tradeItem['uom_id'] : '-1';
				$tradeItems[$key]['uom_symbol'] = $tradeItem['uom_id'] > 0 ? $tradeItem['uom_symbol'] : '';

				foreach ( $formulatedColumnConstants as $constant )
				{
					$tradeItems[$key][$constant . '-final_value']        = 0;
					$tradeItems[$key][$constant . '-value']              = '';
					$tradeItems[$key][$constant . '-has_cell_reference'] = false;
					$tradeItems[$key][$constant . '-has_formula']        = false;
					$tradeItems[$key][$constant . '-has_build_up']       = false;
				}

				if ( array_key_exists($tradeItem['id'], $formulatedColumns) )
				{
					foreach ( $formulatedColumns[$tradeItem['id']] as $formulatedColumn )
					{
						$columnName                                            = $formulatedColumn['column_name'];
						$tradeItems[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
						$tradeItems[$key][$columnName . '-value']              = $formulatedColumn['value'];
						$tradeItems[$key][$columnName . '-has_cell_reference'] = false;
						$tradeItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
					}
				}

				unset( $formulatedColumns[$tradeItem['id']], $tradeItem );
			}

			// need to pass build up qty item(s) into generator to correctly generate the printout page
			$reportPrintGenerator->setItems($tradeItems);

			$pages   = $reportPrintGenerator->generatePages();
			$maxRows = $reportPrintGenerator->getMaxRows();

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			foreach ( $pages as $page )
			{
				if ( count($page) == 0 )
				{
					continue;
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'lastPage'                   => false,
					'itemPage'                   => $page,
					'totalRate'                  => 0,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'pageCount'                  => $pageCount,
					'elementTitle'               => $resource->name,
					'printingPageTitle'          => $printingPageTitle,
					'billDescription'            => $trade['description'],
					'columnDescription'          => null,
					'formulatedColumnConstants'  => $formulatedColumnConstants,
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
					'reportPrintGenerator'       => $reportPrintGenerator,
				);

				$layout .= $this->getPartial('selectedItemsReport', $billItemsLayoutParams);

				$pdfGen->addPage($layout);

				unset( $layout, $page );

				$pageCount ++;
			}
		}

		unset( $items );

		return $pdfGen->send();
	}

	public function executePrintingSelectedItemsWithSupplierRates(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$resource = Doctrine_Core::getTable('Resource')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds                   = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle         = $request->getParameter('printingPageTitle');
		$descriptionFormat         = $request->getParameter('descriptionFormat');
		$priceFormat               = $request->getParameter('priceFormat');
		$printNoCents              = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice              = false;
		$stylesheet                = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ResourceItem');

		list(
			$trades, $items, $formulatedColumns
			) = ResourceItemTable::getSelectedItemsWithSupplierRatesByItemIds($resource, $itemIds);

		if ( empty( $items ) )
		{
			$this->message     = 'Error';
			$this->explanation = 'Nothing can be printed because there were no selected item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		list( $supplierRatesData, $selectedRatesData ) = RFQItemRateTable::getSupplierRatesByItems($items);

		$reportPrintGenerator = new sfResourceLibrarySupplierRatesReportGenerator($descriptionFormat);
		$currency             = $reportPrintGenerator->getCurrency();
		$pageCount            = 1;

		$reportPrintGenerator->setOrientationAndSize('landscape');

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

		foreach ( $trades as $trade )
		{
			if ( empty( $items[$trade['id']] ) )
			{
				continue;
			}

			$tradeItems = $items[$trade['id']];

			foreach ( $tradeItems as $resourceItem )
			{
				$billItemPageCount                 = 1;
				$resourceItemId                    = $resourceItem['id'];
				$resourceItemSelectedRateId        = $resourceItem['resource_item_selected_rate_id'];
				$supplierRatesByItemId             = array();
				$selectedSupplierRatesBySelectedId = array();
				$totalRate                         = 0;

				// only generate print-out for item level only
				if ( $resourceItem['type'] == ResourceItem::TYPE_HEADER )
				{
					continue;
				}

				$resourceItem['type']       = (string) $resourceItem['type'];
				$resourceItem['uom_id']     = $resourceItem['uom_id'] > 0 ? (string) $resourceItem['uom_id'] : '-1';
				$resourceItem['uom_symbol'] = $resourceItem['uom_id'] > 0 ? $resourceItem['uom_symbol'] : '';

				if ( array_key_exists($resourceItem['id'], $formulatedColumns) )
				{
					foreach ( $formulatedColumns[$resourceItem['id']] as $formulatedColumn )
					{
						if ( $formulatedColumn['column_name'] != BQItem::FORMULATED_COLUMN_RATE )
						{
							continue;
						}

						$totalRate = $formulatedColumn['final_value'];
					}
				}

				if ( isset( $supplierRatesData[$resourceItemId] ) )
				{
					$supplierRatesByItemId = $supplierRatesData[$resourceItemId];

					unset( $supplierRatesData[$resourceItemId] );
				}

				if ( isset( $selectedRatesData[$resourceItemSelectedRateId] ) )
				{
					$selectedSupplierRatesBySelectedId = $selectedRatesData[$resourceItemSelectedRateId];

					unset( $selectedRatesData[$resourceItemId] );
				}

				// need to pass supplier rate(s) into generator to correctly generate the printout page
				$reportPrintGenerator->setSupplierRates($supplierRatesByItemId);

				$reportPrintGenerator->setSelectedSupplierRatesInfo($selectedSupplierRatesBySelectedId);

				$pages            = $reportPrintGenerator->generatePages();
				$resourceItemInfo = $reportPrintGenerator->setupResourceItemHeader($resourceItem);
				$maxRows          = $reportPrintGenerator->getMaxRows();

				if ( !( $pages instanceof SplFixedArray ) )
				{
					continue;
				}

				foreach ( $pages as $page )
				{
					if ( count($page) == 0 )
					{
						continue;
					}

					$lastPage = ( $billItemPageCount == $pages->count() ) ? true : false;

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$layoutParams = array(
						'sortingType'                => ResourceItemSelectedRateTable::getSortingTypeText($resourceItem['sorting_type']),
						'billItemRateValue'          => $totalRate,
						'lastPage'                   => $lastPage,
						'billItemInfos'              => $resourceItemInfo,
						'billItemUOM'                => $resourceItem['uom_symbol'],
						'itemPage'                   => $page,
						'totalRate'                  => $totalRate,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'pageCount'                  => $pageCount,
						'elementTitle'               => $resource->name,
						'printingPageTitle'          => $printingPageTitle,
						'billDescription'            => $trade['description'],
						'columnDescription'          => null,
						'formulatedColumnConstants'  => $formulatedColumnConstants,
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
						'reportPrintGenerator'       => $reportPrintGenerator,
					);

					$layout .= $this->getPartial('supplierRatesReport', $layoutParams);

					$pdfGen->addPage($layout);

					unset( $layout, $page );

					$pageCount ++;
					$billItemPageCount ++;
				}

				unset( $formulatedColumns[$resourceItem['id']], $resourceItem );
			}

			unset( $tradeItems, $items[$trade['id']] );
		}

		return $pdfGen->send();
	}
	// ==============================================================================================================

}