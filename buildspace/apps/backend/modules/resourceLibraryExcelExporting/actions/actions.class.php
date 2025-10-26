<?php

/**
 * resourceLibraryExcelExporting actions.
 *
 * @package    buildspace
 * @subpackage resourceLibraryExcelExporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class resourceLibraryExcelExportingActions extends BaseActions {

	public function executeExportExcelSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$resource = Doctrine_Core::getTable('Resource')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds                   = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle         = $request->getParameter('printingPageTitle');
		$descriptionFormat         = $request->getParameter('descriptionFormat');
		$printNoCents              = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ResourceItem');
		$tradeInfo                 = array();

		list(
			$trades, $items, $formulatedColumns
			) = ResourceItemTable::getSelectedItemsByItemIds($resource, $itemIds);

		$reportPrintGenerator = new sfResourceLibraryItemReportGenerator($descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('landscape');

		$excelGenerator = new sfResourceLibraryItemExcelExporterGenerator($printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $items ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		$pageCount = 1;

		foreach ( $trades as $trade )
		{
			$tradeId             = $trade['id'];
			$tradeInfo[$tradeId] = $trade['description'];

			$tradeItems = $items[$tradeId];

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

				$excelGenerator->process($page, false, $printingPageTitle, null, $resource['name'] . ' > ' . $trade['description'], $printNoCents, null);

				unset( $layout, $page );

				$pageCount ++;
			}
		}

		unset( $items );

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executeExportExcelSelectedItemsWithSupplierRates(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$resource = Doctrine_Core::getTable('Resource')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds           = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		list(
			$trades, $items, $formulatedColumns
			) = ResourceItemTable::getSelectedItemsWithSupplierRatesByItemIds($resource, $itemIds);

		$reportPrintGenerator = new sfResourceLibrarySupplierRatesReportGenerator($descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('landscape');

		$excelGenerator = new sfResourceLibrarySupplierRateExcelExporterGenerator($printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $items ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		list( $supplierRatesData, $selectedRatesData ) = RFQItemRateTable::getSupplierRatesByItems($items);

		foreach ( $trades as $trade )
		{
			if ( empty( $items[$trade['id']] ) )
			{
				continue;
			}

			$tradeItems = $items[$trade['id']];

			foreach ( $tradeItems as $resourceItem )
			{
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

				$resourceItem['total_rate'] = $totalRate;

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

				if ( !( $pages instanceof SplFixedArray ) )
				{
					continue;
				}

				$excelGenerator->setResourceItem($resourceItem);
				$excelGenerator->setResourceItemInfo($resourceItemInfo);

				foreach ( $pages as $page )
				{
					if ( empty( $page ) )
					{
						continue;
					}

					$excelGenerator->process($page, false, $printingPageTitle, null, $resource['name'] . ' > ' . $trade['description'], $printNoCents, null);

					unset( $page );
				}

				unset( $formulatedColumns[$resourceItem['id']], $resourceItem );
			}

			unset( $tradeItems, $items[$trade['id']] );
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

}