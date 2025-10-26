<?php

/**
 * bqLibraryExportExcelReporting actions.
 *
 * @package    buildspace
 * @subpackage bqLibraryExportExcelReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class bqLibraryExportExcelReportingActions extends BaseActions {

	public function executeExportExcelSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds           = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		list(
			$elements, $items, $formulatedColumns
			) = BQItemTable::getSelectedItemsByItemIds($bqLibrary, $itemIds);

		unset( $formulatedColumns );

		$reportPrintGenerator = new sfBQLibraryItemReportGenerator($bqLibrary, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$excelGenerator = new sfBQLibraryItemUnitExcelExporterGenerator($printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $items ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

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

			$pages = $reportPrintGenerator->generatePages();

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

				$excelGenerator->process($page, false, $printingPageTitle, null, $bqLibrary['name'] . ' > ' . $element['description'], $printNoCents, null);

				unset( $page );
			}

			unset( $elementItems, $element );
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executeExportExcelSelectedItemsWithRate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds           = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		list(
			$elements, $items, $formulatedColumns
			) = BQItemTable::getSelectedItemsWithRatesByItemIds($bqLibrary, $itemIds);

		$reportPrintGenerator = new sfBQLibraryItemReportGenerator($bqLibrary, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$excelGenerator = new sfBQLibraryItemUnitExcelExporterGenerator($printingPageTitle, $reportPrintGenerator->getPrintSettings(), true);

		if ( empty( $items ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

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

			$pages = $reportPrintGenerator->generatePages();

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

				$excelGenerator->process($page, false, $printingPageTitle, null, $bqLibrary['name'] . ' > ' . $element['description'], $printNoCents, null);

				unset( $page );
			}

			unset( $elementItems, $element );
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executeExportExcelSelectedItemsWithBuildUpRates(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bqLibrary = Doctrine_Core::getTable('BQLibrary')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds           = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$elementInfo       = array();

		list(
			$elements, $items, $formulatedColumns
			) = BQItemTable::getSelectedItemsWithBuildUpRatesByItemIds($bqLibrary, $itemIds);

		$reportPrintGenerator = new sfBQLibraryItemBuildUpRateReportGenerator($bqLibrary, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('landscape');

		$excelGenerator = new sfBQLibraryItemWithBuildUpRateExcelExporterGenerator($printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $items ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		list(
			$resourceTrades, $buildUpQuantityItems, $billBuildUpQuantitySummaries
			) = BQLibraryBuildUpRateItemTable::getBuildUpRateItemsWithSummaryByItemIds($items);

		foreach ( $elements as $element )
		{
			$elementInfo[$element['id']] = $element['description'];
		}

		foreach ( $items as $elementItems )
		{
			foreach ( $elementItems as $bqItem )
			{
				$elementDesc                  = $elementInfo[$bqItem['element_id']];
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

				$bqItem['total_rate'] = $totalRate;

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

				$pages      = $reportPrintGenerator->generatePages();
				$bqItemInfo = $reportPrintGenerator->setupBillItemHeader($bqItem);

				$excelGenerator->setBQLibraryItem($bqItem);
				$excelGenerator->setBQLibraryItemInfo($bqItemInfo);
				$excelGenerator->setBuildUpRateSummaryInfo($buildUpQuantitySummaryInfo);

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

					$lastPage = ( $billItemPageCount == $pages->count() ) ? true : false;

					$excelGenerator->setIsLastPage($lastPage);

					$excelGenerator->process($page, false, $printingPageTitle, null, $bqLibrary['name'] . ' > ' . $elementDesc, $printNoCents, null);

					unset( $page );

					$billItemPageCount ++;
				}

				unset( $formulatedColumns[$bqItem['id']], $bqItem );
			}

			unset( $elementItems );
		}

		unset( $items );

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

}