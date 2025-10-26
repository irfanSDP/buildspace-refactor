<?php

/**
 * stockInReporting actions.
 *
 * @package    buildspace
 * @subpackage stockInReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class stockInReportingActions extends BaseActions {

	public function executeGetPrintPreviewStockOutItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		$data = array();
		list( $invoiceItemIds, $invoiceFromDbItems ) = $this->getInvoiceItemList($invoice, $request->getParameter('item_ids'));

		if ( !empty( $invoiceItemIds ) )
		{
			$tradeIds         = array();
			$doItemQuantities = StockInDeliveryOrderItemQuantityTable::getItemQuantitiesByStockInInvoice($invoice);

			$items = StockInInvoiceItemTable::getHierarchyInvoiceItemListingFromResourceLibraryByStockInItemIds($invoiceItemIds, $invoiceFromDbItems, $doItemQuantities);

			foreach ( $items as $item )
			{
				$tradeIds[$item['resource_trade_id']] = $item['resource_trade_id'];
			}

			foreach ( ResourceTradeTable::getRecordsByIds($tradeIds) as $trade )
			{
				$tradeId = $trade['id'];

				// will neglect resourceName first
				$data[] = array(
					'id'          => "resourceTrade-{$tradeId}",
					'description' => $trade['description'],
					'uom'         => null,
					'remarks'     => null,
					'type'        => 0,
				);

				foreach ( $items as $itemKey => $item )
				{
					if ( $item['resource_trade_id'] != $tradeId )
					{
						continue;
					}

					$data[] = $item;

					unset( $items[$itemKey] );
				}
			}
		}

		// empty row
		$data[] = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => null,
			'uom'         => null,
			'remarks'     => null,
		);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data,
		));
	}

	public function executePrintPreviewStockOutItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		session_write_close();

		$stylesheet        = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;

		list( $invoiceItemIds, $invoiceFromDbItems ) = $this->getInvoiceItemList($invoice, $request->getParameter('selectedRows'));

		if ( empty( $invoiceItemIds ) )
		{
			return $this->nothingToBePrintedError();
		}

		$orderedItems     = array();
		$tradeIds         = array();
		$doItemQuantities = StockInDeliveryOrderItemQuantityTable::getItemQuantitiesByStockInInvoice($invoice);

		$items = StockInInvoiceItemTable::getHierarchyInvoiceItemListingFromResourceLibraryByStockInItemIds($invoiceItemIds, $invoiceFromDbItems, $doItemQuantities, true);

		foreach ( $items as $item )
		{
			$tradeIds[$item['resource_trade_id']]       = $item['resource_trade_id'];
			$orderedItems[$item['resource_trade_id']][] = $item;

			unset( $item );
		}

		unset( $items );

		$reportPrintGenerator = new sfBuildSpaceStockInInvoiceItemReportPageGenerator($descriptionFormat);
		$currency             = $reportPrintGenerator->getCurrency();
		$maxRows              = $reportPrintGenerator->getMaxRows();
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

		foreach ( ResourceTradeTable::getRecordsByIds($tradeIds) as $trade )
		{
			$resourceName     = $trade['Resource']['name'];
			$tradeId          = $trade['id'];
			$tradeDescription = $trade['description'];

			$reportPrintGenerator->setItems($orderedItems[$tradeId]);

			$pages = $reportPrintGenerator->generatePages();

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
					'elementTitle'               => "{$invoice->invoice_no}",
					'printingPageTitle'          => $printingPageTitle,
					'billDescription'            => "{$resourceName} - {$tradeDescription}",
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

				$layout .= $this->getPartial('selectedInvoiceItemsReport', $billItemsLayoutParams);

				$pdfGen->addPage($layout);

				unset( $layout, $page );

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executeExportExcelForSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		list( $invoiceItemIds, $invoiceFromDbItems ) = $this->getInvoiceItemList($invoice, $request->getParameter('selectedRows'));

		$orderedItems     = array();
		$tradeIds         = array();
		$doItemQuantities = StockInDeliveryOrderItemQuantityTable::getItemQuantitiesByStockInInvoice($invoice);

		$items = StockInInvoiceItemTable::getHierarchyInvoiceItemListingFromResourceLibraryByStockInItemIds($invoiceItemIds, $invoiceFromDbItems, $doItemQuantities, true);

		$reportPrintGenerator = new sfBuildSpaceStockInInvoiceItemReportPageGenerator($descriptionFormat);

		$sfItemReportGenerator = new sfBuildSpaceStockInItemReportExcelGenerator(
			$invoice->Project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		if ( empty( $invoiceItemIds ) )
		{
			$sfItemReportGenerator->finishExportProcess();

			return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
		}

		if ( empty( $items ) )
		{
			$sfItemReportGenerator->finishExportProcess();

			return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
		}

		foreach ( $items as $item )
		{
			$tradeIds[$item['resource_trade_id']]       = $item['resource_trade_id'];
			$orderedItems[$item['resource_trade_id']][] = $item;

			unset( $item );
		}

		unset( $items );

		$sfItemReportGenerator->setExcelParameter(false, $printNoCents);
		$sfItemReportGenerator->setActiveSheet(0);
		$sfItemReportGenerator->startBillCounter();

		foreach ( ResourceTradeTable::getRecordsByIds($tradeIds) as $trade )
		{
			$resourceName     = $trade['Resource']['name'];
			$tradeId          = $trade['id'];
			$tradeDescription = $trade['description'];

			$reportPrintGenerator->setItems($orderedItems[$tradeId]);

			$pages = $reportPrintGenerator->generatePages();

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			$sfItemReportGenerator->process($pages, false, $printingPageTitle, '', "{$resourceName} - {$tradeDescription}", $printNoCents);
		}

		$sfItemReportGenerator->finishExportProcess();

		return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
	}

	public function executeGetPrintPreviewStockOutDOItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$deliveryOrder = Doctrine_Core::getTable('StockInDeliveryOrder')->find($request->getParameter('deliveryOrderId'))
		);

		$itemIds            = json_decode($request->getParameter('item_ids'));
		$data               = array();
		$invoiceItemIds     = array();
		$invoiceFromDbItems = array();

		$deliveryOrderItems = StockInDeliveryOrderItemQuantityTable::getItemQuantitiesByStockInDeliveryOrder($deliveryOrder, $itemIds, true);

		foreach ( $deliveryOrderItems as $deliveryOrderItem )
		{
			$invoiceItemIds[$deliveryOrderItem['resource_item_id']] = $deliveryOrderItem['resource_item_id'];

			$invoiceQuantity = number_format((float) $deliveryOrderItem['invoice_quantity'], 2, '.', '');
			$doQuantity      = number_format((float) $deliveryOrderItem['delivery_order_quantity'], 2, '.', '');

			$invoiceFromDbItems[$deliveryOrderItem['resource_item_id']] = array(
				'stockInItemId'       => $deliveryOrderItem['id'],
				'qtyId'               => $deliveryOrderItem['qtyid'],
				'invoiceQuantity'     => $invoiceQuantity,
				'doQuantity'          => $doQuantity,
				'stockInItemRemarkId' => $deliveryOrderItem['remark_id'],
				'remarks'             => $deliveryOrderItem['remark'],
			);
		}

		if ( !empty( $invoiceItemIds ) )
		{
			$items    = StockInInvoiceItemTable::getHierarchyDeliveryOrderItemListingFromResourceLibraryByStockInItemIds($invoiceItemIds, $invoiceFromDbItems);
			$tradeIds = array();

			foreach ( $items as $item )
			{
				$tradeIds[$item['resource_trade_id']] = $item['resource_trade_id'];
			}

			foreach ( ResourceTradeTable::getRecordsByIds($tradeIds) as $trade )
			{
				$data[] = array(
					'id'          => 'resourceTrade-' . $trade['id'],
					'description' => $trade['description'],
					'uom'         => null,
					'remarks'     => null,
					'type'        => 0,
				);

				foreach ( $items as $itemKey => $item )
				{
					if ( $item['resource_trade_id'] != $trade['id'] )
					{
						continue;
					}

					$data[] = $item;

					unset( $item, $items[$itemKey] );
				}
			}

			unset( $items );
		}

		// empty row
		$data[] = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => null,
			'uom'         => null,
			'remarks'     => null,
		);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data,
		));
	}

	public function executePrintPreviewStockOutDOItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$deliveryOrder = Doctrine_Core::getTable('StockInDeliveryOrder')->find($request->getParameter('deliveryOrderId'))
		);

		$itemIds            = $request->getParameter('selectedRows') ? json_decode($request->getParameter('selectedRows')) : array();
		$stylesheet         = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
		$printingPageTitle  = $request->getParameter('printingPageTitle');
		$descriptionFormat  = $request->getParameter('descriptionFormat');
		$priceFormat        = $request->getParameter('priceFormat');
		$printNoCents       = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice       = false;
		$data               = array();
		$invoiceItemIds     = array();
		$invoiceFromDbItems = array();

		$deliveryOrderItems = StockInDeliveryOrderItemQuantityTable::getItemQuantitiesByStockInDeliveryOrder($deliveryOrder, $itemIds, true);

		foreach ( $deliveryOrderItems as $deliveryOrderItem )
		{
			$invoiceItemIds[$deliveryOrderItem['resource_item_id']] = $deliveryOrderItem['resource_item_id'];

			$invoiceQuantity = number_format((float) $deliveryOrderItem['invoice_quantity'], 2, '.', '');
			$doQuantity      = number_format((float) $deliveryOrderItem['delivery_order_quantity'], 2, '.', '');

			$invoiceFromDbItems[$deliveryOrderItem['resource_item_id']] = array(
				'stockInItemId'       => $deliveryOrderItem['id'],
				'qtyId'               => $deliveryOrderItem['qtyid'],
				'invoiceQuantity'     => $invoiceQuantity,
				'doQuantity'          => $doQuantity,
				'stockInItemRemarkId' => $deliveryOrderItem['remark_id'],
				'remarks'             => $deliveryOrderItem['remark'],
			);
		}

		unset( $deliveryOrderItems );

		if ( empty( $invoiceItemIds ) )
		{
			return $this->nothingToBePrintedError();
		}

		$items    = StockInInvoiceItemTable::getHierarchyDeliveryOrderItemListingFromResourceLibraryByStockInItemIds($invoiceItemIds, $invoiceFromDbItems);
		$tradeIds = array();

		foreach ( $items as $item )
		{
			$tradeIds[$item['resource_trade_id']] = $item['resource_trade_id'];
			$data[$item['resource_trade_id']][]   = $item;

			unset( $item );
		}

		unset( $items );

		$reportPrintGenerator = new sfBuildSpaceStockInDOItemReportPageGenerator($descriptionFormat);
		$currency             = $reportPrintGenerator->getCurrency();
		$maxRows              = $reportPrintGenerator->getMaxRows();
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
			'margin-right'   => $reportPrintGenerator->getMarginRight(),
			'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
			'margin-left'    => $reportPrintGenerator->getMarginLeft(),
			'page-size'      => $reportPrintGenerator->getPageSize(),
			'orientation'    => $reportPrintGenerator->getOrientation(),
		));

		foreach ( ResourceTradeTable::getRecordsByIds($tradeIds) as $trade )
		{
			$tradeId          = $trade['id'];
			$tradeDescription = $trade['description'];
			$resourceName     = $trade['Resource']['name'];

			$reportPrintGenerator->setItems($data[$tradeId]);

			$pages = $reportPrintGenerator->generatePages();

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
					'elementTitle'               => "{$deliveryOrder->Invoice->invoice_no} -> {$deliveryOrder->delivery_order_no}",
					'printingPageTitle'          => $printingPageTitle,
					'billDescription'            => "{$resourceName} - {$tradeDescription}",
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

				$layout .= $this->getPartial('selectedDOItemsReport', $billItemsLayoutParams);

				$pdfGen->addPage($layout);

				unset( $layout, $page );

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executeExportExcelForStockOutDOItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$deliveryOrder = Doctrine_Core::getTable('StockInDeliveryOrder')->find($request->getParameter('deliveryOrderId'))
		);

		$itemIds            = $request->getParameter('selectedRows') ? json_decode($request->getParameter('selectedRows')) : array();
		$printingPageTitle  = $request->getParameter('printingPageTitle');
		$descriptionFormat  = $request->getParameter('descriptionFormat');
		$printNoCents       = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$invoiceItemIds     = array();
		$invoiceFromDbItems = array();
		$data               = array();

		$deliveryOrderItems = StockInDeliveryOrderItemQuantityTable::getItemQuantitiesByStockInDeliveryOrder($deliveryOrder, $itemIds, true);

		foreach ( $deliveryOrderItems as $deliveryOrderItem )
		{
			$invoiceItemIds[$deliveryOrderItem['resource_item_id']] = $deliveryOrderItem['resource_item_id'];

			$invoiceQuantity = number_format((float) $deliveryOrderItem['invoice_quantity'], 2, '.', '');
			$doQuantity      = number_format((float) $deliveryOrderItem['delivery_order_quantity'], 2, '.', '');

			$invoiceFromDbItems[$deliveryOrderItem['resource_item_id']] = array(
				'stockInItemId'       => $deliveryOrderItem['id'],
				'qtyId'               => $deliveryOrderItem['qtyid'],
				'invoiceQuantity'     => $invoiceQuantity,
				'doQuantity'          => $doQuantity,
				'stockInItemRemarkId' => $deliveryOrderItem['remark_id'],
				'remarks'             => $deliveryOrderItem['remark'],
			);
		}

		unset( $deliveryOrderItems );

		$reportPrintGenerator = new sfBuildSpaceStockInDOItemReportPageGenerator($descriptionFormat);

		$reportPrintGenerator->setOrientationAndSize('portrait');

		$sfItemReportGenerator = new sfBuildSpaceStockInDOItemReportExcelGenerator(
			$deliveryOrder->Invoice->Project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		if ( empty( $invoiceItemIds ) )
		{
			// will return empty excel file to be downloaded
			$sfItemReportGenerator->finishExportProcess();

			return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
		}

		$items    = StockInInvoiceItemTable::getHierarchyDeliveryOrderItemListingFromResourceLibraryByStockInItemIds($invoiceItemIds, $invoiceFromDbItems);
		$tradeIds = array();

		foreach ( $items as $item )
		{
			$tradeIds[$item['resource_trade_id']] = $item['resource_trade_id'];
			$data[$item['resource_trade_id']][]   = $item;

			unset( $item );
		}

		unset( $items );

		$sfItemReportGenerator->setExcelParameter(false, $printNoCents);
		$sfItemReportGenerator->setActiveSheet(0);
		$sfItemReportGenerator->startBillCounter();

		foreach ( ResourceTradeTable::getRecordsByIds($tradeIds) as $trade )
		{
			$tradeId          = $trade['id'];
			$tradeDescription = $trade['description'];
			$resourceName     = $trade['Resource']['name'];

			$reportPrintGenerator->setItems($data[$tradeId]);

			$pages = $reportPrintGenerator->generatePages();

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			$sfItemReportGenerator->process($pages, false, $printingPageTitle, "{$deliveryOrder->Invoice->invoice_no} -> {$deliveryOrder->delivery_order_no}", "{$resourceName} - {$tradeDescription}", $printNoCents);
		}

		$sfItemReportGenerator->finishExportProcess();

		return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
	}

	private function getInvoiceItemList(StockInInvoice $invoice, $itemIds)
	{
		$invoiceItemIds     = array();
		$invoiceFromDbItems = array();

		$itemIds = $itemIds ? : '[]';

		$invoiceItems = StockInInvoiceItemTable::getItemListingByStockInInvoice($invoice, $itemIds);

		foreach ( $invoiceItems as $invoiceItem )
		{
			$invoiceItemIds[$invoiceItem['resource_item_id']] = $invoiceItem['resource_item_id'];

			$quantity           = number_format((float) $invoiceItem['quantity'], 2, '.', '');
			$rates              = number_format((float) $invoiceItem['rates'], 2, '.', '');
			$totalWithoutTax    = number_format((float) $invoiceItem['total_without_tax'], 2, '.', '');
			$total              = number_format((float) $invoiceItem['total'], 2, '.', '');
			$discountPercentage = number_format((float) $invoiceItem['discount_percentage'], 2, '.', '');
			$taxPercentage      = number_format((float) $invoiceItem['tax_percentage'], 2, '.', '');

			$invoiceFromDbItems[$invoiceItem['resource_item_id']] = array(
				'stockInItemId'       => $invoiceItem['id'],
				'quantity'            => $quantity,
				'rates'               => $rates,
				'discount_percentage' => $discountPercentage,
				'tax_percentage'      => $taxPercentage,
				'total_without_tax'   => $totalWithoutTax,
				'total'               => $total,
				'stockInItemRemarkId' => $invoiceItem['remark_id'],
				'remarks'             => $invoiceItem['remark'],
			);
		}

		return array( $invoiceItemIds, $invoiceFromDbItems );
	}

	private function nothingToBePrintedError()
	{
		$this->message     = 'ERROR';
		$this->explanation = 'Nothing can be printed because there are no item(s) detected.';

		$this->setTemplate('nothingToPrint');

		return sfView::ERROR;
	}

}