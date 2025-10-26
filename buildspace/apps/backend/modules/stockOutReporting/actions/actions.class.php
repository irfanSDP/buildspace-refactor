<?php

/**
 * stockOutReporting actions.
 *
 * @package    buildspace
 * @subpackage stockOutReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class stockOutReportingActions extends BaseActions {

	public function executeGetAffectedItemsByResourceTrade(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('resourceId'))
		);

		$resourceTradeIds = $request->getParameter('resource_trade_ids');

		return $this->renderJson(ResourceTradeTable::getAffectedItemIdsThatHasStockInsByProjectAndResourceTradeIds($project, $resource, $resourceTradeIds));
	}

	public function executeGetAffectedResourceTradeByItem(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
			$resourceTrade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('resourceTradeId'))
		);

		$resourceItems = $request->getParameter('resource_item_ids');

		return $this->renderJson(ResourceItemTable::getAffectedTradeIdsThatHasStockInsByProjectAndResourceItemIds($resourceItems));
	}

	public function executeGetPrintPreviewStockOutItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('resourceId'))
		);

		$data     = array();
		$tradeIds = array();
		$items    = StockOutReport::getRecordsWithDeliveryOrderQuantities($project, $request->getParameter('item_ids'));

		foreach ( $items as $resourceTradeId => $item )
		{
			// keep a copy of available resource trade id
			$tradeIds[$resourceTradeId] = $resourceTradeId;

			unset( $item );
		}

		if ( !empty( $tradeIds ) )
		{
			$resourceTrades = ResourceTradeTable::getRecordsByIds($tradeIds);

			foreach ( $resourceTrades as $resourceTrade )
			{
				if ( !isset( $items[$resourceTrade['id']] ) )
				{
					continue;
				}

				$data[] = array(
					'id'          => "resourceTrade-{$resourceTrade['id']}",
					'description' => $resourceTrade['description'],
					'type'        => 0,
					'total_cost'  => 0,
					'do_quantity' => 0,
					'uom_id'      => - 1,
					'uom_symbol'  => null,
				);

				foreach ( $items[$resourceTrade['id']] as $item )
				{
					$data[] = $item;

					unset( $item );
				}

				unset( $items[$resourceTrade['id']] );
			}
		}

		// empty row
		$data[] = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => null,
			'total_cost'  => 0,
			'do_quantity' => 0,
			'uom_id'      => - 1,
			'uom_symbol'  => null,
		);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data,
		));
	}

	public function executePrintSelectedItem(sfWebRequest $request)
	{
		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('projectId')) and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getPostParameter('resourceId'))
		);

		session_write_close();

		$stylesheet        = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
		$tradeIds          = array();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;

		$items = StockOutReport::getRecordsWithDeliveryOrderQuantities($project, $request->getParameter('selectedRows'));

		if ( empty( $items ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = 'Nothing can be printed because there are no item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		$reportPrintGenerator = new sfBuildSpaceStockOutItemReportPageGenerator($descriptionFormat);
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

		foreach ( $items as $resourceTradeId => $item )
		{
			// keep a copy of available resource trade id
			$tradeIds[$resourceTradeId] = $resourceTradeId;

			unset( $item );
		}

		foreach ( ResourceTradeTable::getRecordsByIds($tradeIds) as $trade )
		{
			$tradeInfo[$trade['id']] = $trade['description'];

			// need to pass build up qty item(s) into generator to correctly generate the printout page
			$reportPrintGenerator->setItems($items[$trade['id']]);

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

		unset( $variationOrder, $voItems );

		return $pdfGen->send();
	}

	public function executeExportExcelForSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('projectId')) and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getPostParameter('resourceId'))
		);

		session_write_close();

		$tradeIds          = array();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$items                = StockOutReport::getRecordsWithDeliveryOrderQuantities($project, $request->getParameter('selectedRows'));
		$reportPrintGenerator = new sfBuildSpaceStockOutItemReportPageGenerator($descriptionFormat);

		$sfItemReportGenerator = new sfBuildSpaceStockOutItemReportExcelGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		if ( empty( $items ) )
		{
			$sfItemReportGenerator->finishExportProcess();

			return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
		}

		foreach ( $items as $resourceTradeId => $item )
		{
			// keep a copy of available resource trade id
			$tradeIds[$resourceTradeId] = $resourceTradeId;

			unset( $item );
		}

		$sfItemReportGenerator->setExcelParameter(false, $printNoCents);
		$sfItemReportGenerator->setActiveSheet(0);
		$sfItemReportGenerator->startBillCounter();

		foreach ( ResourceTradeTable::getRecordsByIds($tradeIds) as $trade )
		{
			$tradeInfo[$trade['id']] = $trade['description'];

			// need to pass build up qty item(s) into generator to correctly generate the printout page
			$reportPrintGenerator->setItems($items[$trade['id']]);

			$pages = $reportPrintGenerator->generatePages();

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			$sfItemReportGenerator->process($pages, false, $printingPageTitle, '', "{$resource['name']} - {$trade['description']}", $printNoCents);
		}

		$sfItemReportGenerator->finishExportProcess();

		return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
	}

}