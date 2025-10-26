<?php

/**
 * bqLibraryReporting actions.
 *
 * @package    buildspace
 * @subpackage bqLibraryReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class bqLibraryReportingActions extends BaseActions {

	public function executeGetAffectedItemsBySelectedElements(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
		);

		$elementIds    = json_decode($request->getPostParameter('trade_ids'), true);
		$newElementIds = array();
		$data          = array();
		$pdo           = $bqLibrary->getTable()->getConnection()->getDbh();

		if ( !empty( $elementIds ) )
		{
			$elements = Doctrine_Query::create()
				->select('rt.id')
				->from('BQElement rt')
				->whereIn('rt.id', $elementIds)
				->andWhere('rt.library_id = ?', array( $bqLibrary->id ))
				->orderBy('rt.priority ASC')
				->fetchArray();

			foreach ( $elements as $element )
			{
				$newElementIds[] = $element['id'];

				unset( $element );
			}

			unset( $elements );
		}

		if ( !empty( $newElementIds ) )
		{
			// get affected item(s) by trades
			$stmt = $pdo->prepare("SELECT c.id, c.element_id
			FROM " . BQItemTable::getInstance()->getTableName() . " c
			WHERE c.element_id IN (" . implode(',', $newElementIds) . ")
			AND c.deleted_at IS NULL ORDER BY c.priority, c.lft");

			$stmt->execute();
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$data = $this->massageCheckBoxBQLibraryItemsData($items, $data);

			unset( $items );
		}

		return $this->renderJson($data);
	}

	public function executeGetAffectedElementsBySelectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
		);

		$itemIds = json_decode($request->getPostParameter('item_ids'), true);
		$data    = array();
		$pdo     = $bqLibrary->getTable()->getConnection()->getDbh();

		if ( !empty( $itemIds ) )
		{
			// get affected item(s) by trades
			$stmt = $pdo->prepare("SELECT c.id, c.element_id
			FROM " . BQItemTable::getInstance()->getTableName() . " c
			JOIN " . BQElementTable::getInstance()->getTableName() . " rt ON c.element_id = rt.id AND rt.deleted_at IS NULL
			WHERE rt.library_id = " . $bqLibrary->id . " AND c.id IN (" . implode(',', $itemIds) . ")
			AND c.deleted_at IS NULL ORDER BY c.priority, c.lft");

			$stmt->execute();
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$data = $this->massageCheckBoxBQLibraryItemsData($items, $data);

			unset( $items );
		}

		return $this->renderJson($data);
	}

	/**
	 * @param $items
	 * @param $data
	 * @return mixed
	 */
	private function massageCheckBoxBQLibraryItemsData(array $items, array $data)
	{
		foreach ( $items as $item )
		{
			$data[$item['element_id']][] = $item['id'];

			unset( $item );
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
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
		);

		$itemIds = (array) json_decode($request->getPostParameter('item_ids'), true);

		list(
			$elements, $items, $formulatedColumns
			) = BQItemTable::getSelectedItemsByItemIds($bqLibrary, $itemIds);

		// loop each available trade(s) first, so that we can assign each header before every item(s)
		$data = $this->massagePrintPreviewDataForItemLevel($elements, $items, $formulatedColumns);

		unset( $elements );

		$data['identifier'] = 'id';
		$data['items'][]    = $this->generateDefaultRow();

		return $this->renderJson($data);
	}

	public function executeGetPrintPreviewSelectedItemsWithRate(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
		);

		$itemIds = (array) json_decode($request->getPostParameter('item_ids'), true);

		list(
			$elements, $items, $formulatedColumns
			) = BQItemTable::getSelectedItemsWithRatesByItemIds($bqLibrary, $itemIds);

		// loop each available trade(s) first, so that we can assign each header before every item(s)
		$data = $this->massagePrintPreviewDataForItemLevel($elements, $items, $formulatedColumns);

		unset( $elements );

		$data['identifier'] = 'id';
		$data['items'][]    = $this->generateDefaultRow();

		return $this->renderJson($data);
	}

	public function executeGetPrintPreviewSelectedItemsWithBuildUpRates(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
		);

		$itemIds = (array) json_decode($request->getPostParameter('item_ids'), true);

		list(
			$elements, $items, $formulatedColumns
			) = BQItemTable::getSelectedItemsWithBuildUpRatesByItemIds($bqLibrary, $itemIds);

		// loop each available trade(s) first, so that we can assign each header before every item(s)
		$data = $this->massagePrintPreviewDataForItemLevel($elements, $items, $formulatedColumns);

		unset( $elements );

		$data['identifier'] = 'id';
		$data['items'][]    = $this->generateDefaultRow();

		return $this->renderJson($data);
	}

	/**
	 * @return array
	 */
	private function generateDefaultRow()
	{
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQItem');

		$defaultLastRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => '',
			'type'        => (string) BQItem::TYPE_WORK_ITEM,
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
	 * @param $elements
	 * @param $items
	 * @param $formulatedColumns
	 * @return array
	 */
	private function massagePrintPreviewDataForItemLevel(array $elements, array $items, array $formulatedColumns)
	{
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQItem');
		$data                      = array();

		foreach ( $elements as $element )
		{
			$elementId = $element['id'];

			if ( !isset( $items[$elementId] ) )
			{
				continue;
			}

			$tradeRow = array(
				'id'          => "element-{$elementId}",
				'description' => $element['description'],
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
			$elementItems = $items[$elementId];

			foreach ( $elementItems as $bqLibraryItem )
			{
				$bqLibraryItem['type']       = (string) $bqLibraryItem['type'];
				$bqLibraryItem['uom_id']     = $bqLibraryItem['uom_id'] > 0 ? (string) $bqLibraryItem['uom_id'] : '-1';
				$bqLibraryItem['uom_symbol'] = $bqLibraryItem['uom_id'] > 0 ? $bqLibraryItem['uom_symbol'] : '';

				foreach ( $formulatedColumnConstants as $constant )
				{
					$bqLibraryItem[$constant . '-final_value']        = 0;
					$bqLibraryItem[$constant . '-value']              = '';
					$bqLibraryItem[$constant . '-has_cell_reference'] = false;
					$bqLibraryItem[$constant . '-has_formula']        = false;
					$bqLibraryItem[$constant . '-has_build_up']       = false;
				}

				if ( array_key_exists($bqLibraryItem['id'], $formulatedColumns) )
				{
					foreach ( $formulatedColumns[$bqLibraryItem['id']] as $formulatedColumn )
					{
						$columnName                                         = $formulatedColumn['column_name'];
						$bqLibraryItem[$columnName . '-final_value']        = $formulatedColumn['final_value'];
						$bqLibraryItem[$columnName . '-value']              = $formulatedColumn['value'];
						$bqLibraryItem[$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
						$bqLibraryItem[$columnName . '-has_cell_reference'] = false;
						$bqLibraryItem[$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
					}
				}

				$data['items'][] = $bqLibraryItem;

				unset( $formulatedColumns[$bqLibraryItem['id']], $bqLibraryItem );
			}

			unset( $elementItems, $element );
		}

		return $data;
	}
	// ==============================================================================================================

	// ==============================================================================================================
	// Report Generation
	// ==============================================================================================================
	public function executePrintingSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds                   = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle         = $request->getParameter('printingPageTitle');
		$descriptionFormat         = $request->getParameter('descriptionFormat');
		$priceFormat               = $request->getParameter('priceFormat');
		$printNoCents              = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice              = false;
		$stylesheet                = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQItem');

		list(
			$elements, $items, $formulatedColumns
			) = BQItemTable::getSelectedItemsByItemIds($bqLibrary, $itemIds);

		unset( $formulatedColumns );

		if ( empty( $items ) )
		{
			$this->message     = 'Error';
			$this->explanation = 'Nothing can be printed because there were no selected item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		$reportPrintGenerator = new sfBQLibraryItemReportGenerator($bqLibrary, $descriptionFormat);
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

		foreach ( $elements as $element )
		{
			$elementId = $element['id'];
			$newItems  = array();

			if ( !isset( $items[$elementId] ) )
			{
				continue;
			}

			unset( $tradeRow );

			// only attach trade header if there is item(s) available
			$elementItems = $items[$elementId];

			foreach ( $elementItems as $bqItem )
			{
				$bqItem['type']       = (string) $bqItem['type'];
				$bqItem['uom_id']     = $bqItem['uom_id'] > 0 ? (string) $bqItem['uom_id'] : '-1';
				$bqItem['uom_symbol'] = $bqItem['uom_id'] > 0 ? $bqItem['uom_symbol'] : '';
				$bqItem['rate']       = 0;

				$newItems[] = $bqItem;

				unset( $bqItem );
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
				if ( count($page) == 0 )
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
					'topLeftRow1'                => $bqLibrary->name,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow2'                => $element['description'],
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

				$layout .= $this->getPartial('itemsWithoutRateReport', $billItemsLayoutParams);

				$pdfGen->addPage($layout);

				unset( $layout, $page );

				$pageCount ++;
			}

			unset( $elementItems, $element );
		}

		return $pdfGen->send();
	}

	public function executePrintingSelectedItemsWithRate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds                   = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle         = $request->getParameter('printingPageTitle');
		$descriptionFormat         = $request->getParameter('descriptionFormat');
		$priceFormat               = $request->getParameter('priceFormat');
		$printNoCents              = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice              = false;
		$stylesheet                = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BQItem');

		list(
			$elements, $items, $formulatedColumns
			) = BQItemTable::getSelectedItemsWithRatesByItemIds($bqLibrary, $itemIds);

		if ( empty( $items ) )
		{
			$this->message     = 'Error';
			$this->explanation = 'Nothing can be printed because there were no selected item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		$reportPrintGenerator = new sfBQLibraryItemReportGenerator($bqLibrary, $descriptionFormat);
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

		foreach ( $elements as $element )
		{
			$elementId = $element['id'];
			$newItems  = array();

			if ( !isset( $items[$elementId] ) )
			{
				continue;
			}

			unset( $tradeRow );

			// only attach trade header if there is item(s) available
			$elementItems = $items[$elementId];

			foreach ( $elementItems as $bqItem )
			{
				$totalRate = 0;

				if ( array_key_exists($bqItem['id'], $formulatedColumns) )
				{
					foreach ( $formulatedColumns[$bqItem['id']] as $formulatedColumn )
					{
						if ( $formulatedColumn['column_name'] != BQItem::FORMULATED_COLUMN_RATE )
						{
							continue;
						}

						$totalRate = $formulatedColumn['final_value'];
					}
				}

				$bqItem['type']       = (string) $bqItem['type'];
				$bqItem['uom_id']     = $bqItem['uom_id'] > 0 ? (string) $bqItem['uom_id'] : '-1';
				$bqItem['uom_symbol'] = $bqItem['uom_id'] > 0 ? $bqItem['uom_symbol'] : '';
				$bqItem['rate']       = $totalRate;

				$newItems[] = $bqItem;

				unset( $formulatedColumns[$bqItem['id']], $bqItem );
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
				if ( count($page) == 0 )
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
					'topLeftRow1'                => $bqLibrary->name,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow2'                => $element['description'],
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

				$layout .= $this->getPartial('scheduleOfRateReporting/itemsReport', $billItemsLayoutParams);

				$pdfGen->addPage($layout);

				unset( $layout, $page );

				$pageCount ++;
			}

			unset( $elementItems, $element );
		}

		return $pdfGen->send();
	}

	public function executePrintingPreviewSelectedItemsWithBuildUpRates(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
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
		$elementInfo               = array();

		list(
			$elements, $items, $formulatedColumns
			) = BQItemTable::getSelectedItemsWithBuildUpRatesByItemIds($bqLibrary, $itemIds);

		if ( empty( $items ) )
		{
			$this->message     = 'Error';
			$this->explanation = 'Nothing can be printed because there were no selected item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		list(
			$resourceTrades, $buildUpQuantityItems, $billBuildUpQuantitySummaries
			) = BQLibraryBuildUpRateItemTable::getBuildUpRateItemsWithSummaryByItemIds($items);

		$reportPrintGenerator = new sfBQLibraryItemBuildUpRateReportGenerator($bqLibrary, $descriptionFormat);
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

		foreach ( $elements as $element )
		{
			$elementInfo[$element['id']] = $element['description'];
		}

		foreach ( $items as $elementItems )
		{
			foreach ( $elementItems as $bqItem )
			{
				$billItemPageCount            = 1;
				$sorItemId                    = $bqItem['id'];
				$billItemResourceTrades       = array();
				$buildUpItemsByResourceTrades = array();
				$buildUpQuantitySummaryInfo   = array();
				$totalRate                    = 0;

				// only generate print-out for item level only
				if ( $bqItem['type'] == BQItem::TYPE_HEADER )
				{
					continue;
				}

				$bqItem['type']       = (string) $bqItem['type'];
				$bqItem['uom_id']     = $bqItem['uom_id'] > 0 ? (string) $bqItem['uom_id'] : '-1';
				$bqItem['uom_symbol'] = $bqItem['uom_id'] > 0 ? $bqItem['uom_symbol'] : '';

				if ( array_key_exists($bqItem['id'], $formulatedColumns) )
				{
					foreach ( $formulatedColumns[$bqItem['id']] as $formulatedColumn )
					{
						if ( $formulatedColumn['column_name'] != BQItem::FORMULATED_COLUMN_RATE )
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
				$billItemInfo = $reportPrintGenerator->setupBillItemHeader($bqItem);
				$maxRows      = $reportPrintGenerator->getMaxRows();

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

					$billItemsLayoutParams = array(
						'billItemRateValue'          => $totalRate,
						'buildUpQuantitySummary'     => $buildUpQuantitySummaryInfo,
						'lastPage'                   => $lastPage,
						'billItemInfos'              => $billItemInfo,
						'billItemUOM'                => $bqItem['uom_symbol'],
						'itemPage'                   => $page,
						'totalRate'                  => $totalRate,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'pageCount'                  => $pageCount,
						'elementTitle'               => $bqLibrary->name,
						'printingPageTitle'          => $printingPageTitle,
						'billDescription'            => $elementInfo[$bqItem['element_id']],
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

					$layout .= $this->getPartial('scheduleOfRateReporting/buildUpRateReport', $billItemsLayoutParams);

					$pdfGen->addPage($layout);

					unset( $layout, $page );

					$pageCount ++;
					$billItemPageCount ++;
				}

				unset( $formulatedColumns[$bqItem['id']], $bqItem );
			}

			unset( $elementItems );
		}

		unset( $items );

		return $pdfGen->send();
	}
	// ==============================================================================================================

}