<?php

/**
 * scheduleOfRateReporting actions.
 *
 * @package    buildspace
 * @subpackage scheduleOfRateReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class scheduleOfRateReportingActions extends BaseActions {

	public function executeGetAffectedItemsBySelectedTrades(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getPostParameter('libraryId'))
		);

		$tradeIds    = json_decode($request->getPostParameter('trade_ids'), true);
		$newTradeIds = array();
		$data        = array();
		$pdo         = $scheduleOfRate->getTable()->getConnection()->getDbh();

		if ( !empty( $tradeIds ) )
		{
			$trades = Doctrine_Query::create()
				->select('rt.id')
				->from('ScheduleOfRateTrade rt')
				->whereIn('rt.id', $tradeIds)
				->andWhere('rt.schedule_of_rate_id = ?', array( $scheduleOfRate->id ))
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
			$stmt = $pdo->prepare("SELECT c.id, c.trade_id, c.priority, c.lft, c.level
			FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " c
			WHERE c.trade_id IN (" . implode(',', $newTradeIds) . ")
			AND c.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level ASC");

			$stmt->execute();
			$sorItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$data = $this->massageCheckBoxScheduleOfRateItemsData($sorItems, $data);

			unset( $sorItems );
		}

		return $this->renderJson($data);
	}

	public function executeGetAffectedTradesBySelectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getPostParameter('libraryId'))
		);

		$itemIds = json_decode($request->getPostParameter('item_ids'), true);
		$data    = array();
		$pdo     = $scheduleOfRate->getTable()->getConnection()->getDbh();

		if ( !empty( $itemIds ) )
		{
			// get affected item(s) by trades
			$stmt = $pdo->prepare("SELECT c.id, c.trade_id, c.priority, c.lft, c.level
			FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " c
			JOIN " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " rt ON c.trade_id = rt.id AND rt.deleted_at IS NULL
			WHERE rt.schedule_of_rate_id = " . $scheduleOfRate->id . " AND c.id IN (" . implode(',', $itemIds) . ")
			AND c.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level ASC");

			$stmt->execute();
			$sorItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$data = $this->massageCheckBoxScheduleOfRateItemsData($sorItems, $data);

			unset( $sorItems );
		}

		return $this->renderJson($data);
	}

	/**
	 * @param $resourceItems
	 * @param $data
	 * @return mixed
	 */
	private function massageCheckBoxScheduleOfRateItemsData(array $resourceItems, array $data)
	{
		foreach ( $resourceItems as $resourceItem )
		{
			$data[$resourceItem['trade_id']][] = $resourceItem['id'];

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
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getPostParameter('libraryId'))
		);

		$itemIds = (array) json_decode($request->getPostParameter('item_ids'), true);

		list(
			$trades, $items, $formulatedColumns
			) = ScheduleOfRateItemTable::getSelectedItemsByItemIds($scheduleOfRate, $itemIds);

		// loop each available trade(s) first, so that we can assign each header before every item(s)
		$data = $this->massagePrintPreviewDataForItemLevel($trades, $items, $formulatedColumns);

		unset( $trades );

		$data['identifier'] = 'id';
		$data['items'][]    = $this->generateDefaultRow();

		return $this->renderJson($data);
	}

	public function executeGetPrintPreviewSelectedItemsWithBuildUpRates(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getPostParameter('libraryId'))
		);

		$itemIds = (array) json_decode($request->getPostParameter('item_ids'), true);

		list(
			$trades, $items, $formulatedColumns
			) = ScheduleOfRateItemTable::getSelectedItemsWithBuildUpRateByItemIds($scheduleOfRate, $itemIds);

		// loop each available trade(s) first, so that we can assign each header before every item(s)
		$data = $this->massagePrintPreviewDataForItemLevel($trades, $items, $formulatedColumns);

		unset( $trades );

		$data['identifier'] = 'id';
		$data['items'][]    = $this->generateDefaultRow();

		return $this->renderJson($data);
	}

	/**
	 * @param $trades
	 * @param $items
	 * @param $formulatedColumns
	 * @return array
	 */
	private function massagePrintPreviewDataForItemLevel(array $trades, array $items, array $formulatedColumns)
	{
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateItem');
		$data                      = array();

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

			foreach ( $tradeItems as $sorItem )
			{
				$sorItem['type']       = (string) $sorItem['type'];
				$sorItem['uom_id']     = $sorItem['uom_id'] > 0 ? (string) $sorItem['uom_id'] : '-1';
				$sorItem['uom_symbol'] = $sorItem['uom_id'] > 0 ? $sorItem['uom_symbol'] : '';

				foreach ( $formulatedColumnConstants as $constant )
				{
					$sorItem[$constant . '-final_value']        = 0;
					$sorItem[$constant . '-value']              = '';
					$sorItem[$constant . '-has_cell_reference'] = false;
					$sorItem[$constant . '-has_formula']        = false;
					$sorItem[$constant . '-has_build_up']       = false;
				}

				if ( array_key_exists($sorItem['id'], $formulatedColumns) )
				{
					foreach ( $formulatedColumns[$sorItem['id']] as $formulatedColumn )
					{
						$columnName                                   = $formulatedColumn['column_name'];
						$sorItem[$columnName . '-final_value']        = $formulatedColumn['final_value'];
						$sorItem[$columnName . '-value']              = $formulatedColumn['value'];
						$sorItem[$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
						$sorItem[$columnName . '-has_cell_reference'] = false;
						$sorItem[$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
					}
				}

				$data['items'][] = $sorItem;

				unset( $formulatedColumns[$sorItem['id']], $sorItem );
			}

			unset( $tradeItems, $trade );
		}

		return $data;
	}

	/**
	 * @return array
	 */
	private function generateDefaultRow()
	{
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateItem');

		$defaultLastRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => '',
			'type'        => (string) ScheduleOfRateItem::TYPE_WORK_ITEM,
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
	// ==============================================================================================================

	// ==============================================================================================================
	// Report Generation
	// ==============================================================================================================
	public function executePrintingSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds                   = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle         = $request->getParameter('printingPageTitle');
		$descriptionFormat         = $request->getParameter('descriptionFormat');
		$priceFormat               = $request->getParameter('priceFormat');
		$printNoCents              = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice              = false;
		$stylesheet                = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateItem');

		list(
			$trades, $items, $formulatedColumns
			) = ScheduleOfRateItemTable::getSelectedItemsByItemIds($scheduleOfRate, $itemIds);

		if ( empty( $items ) )
		{
			$this->message     = 'Error';
			$this->explanation = 'Nothing can be printed because there were no selected item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::SUCCESS;
		}

		$reportPrintGenerator = new sfScheduleOfRateItemReportGenerator($scheduleOfRate, $descriptionFormat);
		$currency             = $reportPrintGenerator->getCurrency();
		$pageCount            = 1;

		$reportPrintGenerator->setOrientationAndSize('portrait');

		$pdfGen = new WkHtmlToPdf(array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => $reportPrintGenerator->getMarginTop(),
			'margin-right'   => 7,
			'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
			'margin-left'    => 25,
			'page-size'      => $reportPrintGenerator->getPageSize(),
			'orientation'    => $reportPrintGenerator->getOrientation(),
		));

		foreach ( $trades as $trade )
		{
			$tradeId  = $trade['id'];
			$newItems = array();

			if ( !isset( $items[$tradeId] ) )
			{
				continue;
			}

			unset( $tradeRow );

			// only attach trade header if there is item(s) available
			$tradeItems = $items[$tradeId];

			foreach ( $tradeItems as $sorItem )
			{
				$totalRate = 0;

				if ( array_key_exists($sorItem['id'], $formulatedColumns) )
				{
					foreach ( $formulatedColumns[$sorItem['id']] as $formulatedColumn )
					{
						if ( $formulatedColumn['column_name'] != ScheduleOfRateItem::FORMULATED_COLUMN_RATE )
						{
							continue;
						}

						$totalRate = $formulatedColumn['final_value'];
					}
				}

				$sorItem['type']       = (string) $sorItem['type'];
				$sorItem['uom_id']     = $sorItem['uom_id'] > 0 ? (string) $sorItem['uom_id'] : '-1';
				$sorItem['uom_symbol'] = $sorItem['uom_id'] > 0 ? $sorItem['uom_symbol'] : '';
				$sorItem['rate']       = $totalRate;

				$newItems[] = $sorItem;

				unset( $formulatedColumns[$sorItem['id']], $sorItem );
			}

			$reportPrintGenerator->setItems($newItems);

			$pages   = $reportPrintGenerator->generatePages();
			$maxRows = $reportPrintGenerator->getMaxRows();

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			foreach ( $pages as $page )
			{
				if ( empty($page) )
				{
					continue;
				}

				$lastPage = false;

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
					'printGrandTotal'            => false,
					'headerDescription'          => null,
					'topLeftRow1'                => $scheduleOfRate->name,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow2'                => $trade['description'],
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

				$layout .= $this->getPartial('itemsReport', $billItemsLayoutParams);

				$pdfGen->addPage($layout);

				unset( $layout, $page );

				$pageCount ++;
			}

			unset( $tradeItems, $trade );
		}

		return $pdfGen->send();
	}

	public function executePrintingPreviewSelectedItemsWithBuildUpRates(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds                   = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle         = $request->getParameter('printingPageTitle');
		$descriptionFormat         = $request->getParameter('descriptionFormat');
		$priceFormat               = $request->getParameter('priceFormat');
		$printNoCents              = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice              = false;
		$stylesheet                = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateItem');
		$tradeInfo                 = array();

		list(
			$trades, $items, $formulatedColumns
			) = ScheduleOfRateItemTable::getSelectedItemsWithBuildUpRateByItemIds($scheduleOfRate, $itemIds);

		if ( empty( $items ) )
		{
			$this->message     = 'Error';
			$this->explanation = 'Nothing can be printed because there were no selected item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::SUCCESS;
		}

		list(
			$resourceTrades, $buildUpQuantityItems, $billBuildUpQuantitySummaries
			) = ScheduleOfRateBuildUpRateItemTable::getBuildUpRateItemsWithSummaryByItemIds($items);

		$reportPrintGenerator = new sfScheduleOfRateItemBuildUpQtyReportGenerator($scheduleOfRate, $descriptionFormat);
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
		}

		foreach ( $items as $tradeItems )
		{
			foreach ( $tradeItems as $sorItem )
			{
				$billItemPageCount            = 1;
				$sorItemId                    = $sorItem['id'];
				$billItemResourceTrades       = array();
				$buildUpItemsByResourceTrades = array();
				$buildUpQuantitySummaryInfo   = array();
				$totalRate                    = 0;

				// only generate print-out for item level only
				if ( $sorItem['type'] == ScheduleOfRateItem::TYPE_HEADER )
				{
					continue;
				}

				$sorItem['type']       = (string) $sorItem['type'];
				$sorItem['uom_id']     = $sorItem['uom_id'] > 0 ? (string) $sorItem['uom_id'] : '-1';
				$sorItem['uom_symbol'] = $sorItem['uom_id'] > 0 ? $sorItem['uom_symbol'] : '';

				if ( array_key_exists($sorItem['id'], $formulatedColumns) )
				{
					foreach ( $formulatedColumns[$sorItem['id']] as $formulatedColumn )
					{
						if ( $formulatedColumn['column_name'] != ScheduleOfRateItem::FORMULATED_COLUMN_RATE )
						{
							continue;
						}

						$totalRate = $formulatedColumn['final_value'];
					}
				}

				// get associated resource trade(s)
				if ( isset ( $resourceTrades[$sorItemId] ) )
				{
					$billItemResourceTrades = $resourceTrades[$sorItemId];

					unset( $resourceTrades[$sorItemId] );
				}

				if ( isset( $buildUpQuantityItems[$sorItemId] ) )
				{
					$buildUpItemsByResourceTrades = $buildUpQuantityItems[$sorItemId];

					unset( $buildUpQuantityItems[$sorItemId] );
				}

				if ( isset( $billBuildUpQuantitySummaries[$sorItemId] ) )
				{
					$buildUpQuantitySummaryInfo = $billBuildUpQuantitySummaries[$sorItemId];

					unset( $billBuildUpQuantitySummaries[$sorItemId] );
				}

				$reportPrintGenerator->setResourceTrades($billItemResourceTrades);

				// need to pass build up qty item(s) into generator to correctly generate the printout page
				$reportPrintGenerator->setBuildUpQuantityItems($buildUpItemsByResourceTrades);

				$pages        = $reportPrintGenerator->generatePages();
				$billItemInfo = $reportPrintGenerator->setupBillItemHeader($sorItem);
				$maxRows      = $reportPrintGenerator->getMaxRows();

				if ( !( $pages instanceof SplFixedArray ) )
				{
					continue;
				}

				foreach ( $pages as $page )
				{
					if ( empty($page) )
					{
						continue;
					}

					$lastPage = ( $billItemPageCount == $pages->count() ) ? true : false;

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'billItemRateValue'          => $totalRate,
						'buildUpQuantitySummary'     => $buildUpQuantitySummaryInfo,
						'lastPage'                   => $lastPage,
						'billItemInfos'              => $billItemInfo,
						'billItemUOM'                => $sorItem['uom_symbol'],
						'itemPage'                   => $page,
						'totalRate'                  => $totalRate,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'pageCount'                  => $pageCount,
						'elementTitle'               => $scheduleOfRate->name,
						'printingPageTitle'          => $printingPageTitle,
						'billDescription'            => $tradeInfo[$sorItem['trade_id']],
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

					$layout .= $this->getPartial('buildUpRateReport', $billItemsLayoutParams);

					$pdfGen->addPage($layout);

					unset( $layout, $page );

					$pageCount ++;
					$billItemPageCount ++;
				}

				unset( $formulatedColumns[$sorItem['id']], $sorItem );
			}

			unset( $tradeItems );
		}

		unset( $items );

		return $pdfGen->send();
	}
	// ==============================================================================================================

}