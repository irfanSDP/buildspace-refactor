<?php

/**
 * scheduleOfRateExportExcelReporting actions.
 *
 * @package    buildspace
 * @subpackage scheduleOfRateExportExcelReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class scheduleOfRateExportExcelReportingActions extends BaseActions {

	public function executeExportExcelSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds           = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		list(
			$trades, $items, $formulatedColumns
			) = ScheduleOfRateItemTable::getSelectedItemsByItemIds($scheduleOfRate, $itemIds);

		$reportPrintGenerator = new sfScheduleOfRateItemReportGenerator($scheduleOfRate, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$excelGenerator = new sfScheduleOfRateItemExcelExporterGenerator($printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $items ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

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

				$excelGenerator->process($page, false, $printingPageTitle, null, $scheduleOfRate['name'] . ' > ' . $trade['description'], $printNoCents, null);

				unset( $page );
			}

			unset( $tradeItems, $trade );
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executeExportExcelSelectedItemsWithBuildUpRates(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getPostParameter('libraryId'))
		);

		session_write_close();

		$itemIds           = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$tradeInfo         = array();

		list(
			$trades, $items, $formulatedColumns
			) = ScheduleOfRateItemTable::getSelectedItemsWithBuildUpRateByItemIds($scheduleOfRate, $itemIds);

		$reportPrintGenerator = new sfScheduleOfRateItemBuildUpQtyReportGenerator($scheduleOfRate, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('landscape');

		$excelGenerator = new sfScheduleOfRateItemWithBuildUpRateExcelExporterGenerator($printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $items ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		list(
			$resourceTrades, $buildUpQuantityItems, $billBuildUpQuantitySummaries
			) = ScheduleOfRateBuildUpRateItemTable::getBuildUpRateItemsWithSummaryByItemIds($items);

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

				$sorItem['total_rate'] = $totalRate;

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

				$pages       = $reportPrintGenerator->generatePages();
				$sorItemInfo = $reportPrintGenerator->setupBillItemHeader($sorItem);

				$excelGenerator->setSORItem($sorItem);
				$excelGenerator->setScheduleOfRateItemInfo($sorItemInfo);
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

					$excelGenerator->process($page, false, $printingPageTitle, null, $scheduleOfRate['name'] . ' > ' . $trade['description'], $printNoCents, null);

					unset( $page );
				}

				unset( $formulatedColumns[$sorItem['id']], $sorItem );
			}

			unset( $tradeItems );
		}

		unset( $items );

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

    /**
     * Generates an excel file for exporting Item rate and total.
     *
     * @param sfWebRequest $request
     *
     * @return string
     * @throws sfError404Exception
     */
    public function executeExportScheduleOfRateTendererReport(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
        );

        session_write_close();

        $element = BillElementTable::getInstance()->find($request->getParameter('elementId'));
        $project = ProjectStructureTable::getInstance()->find($bill->root_id);

        //Setup Necessary parameter
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tendererIds = json_decode($request->getParameter('selectedTenderers'), true);
        $itemIds = json_decode($request->getParameter('selectedRows'), true);
        $sortingType = $request->getParameter('sortingType');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        //Generate Item Pages
        $reportPrintGenerator = new sfBuildspaceReportScheduleOfRateItemPageGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

        $topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
        $pages = $reportPrintGenerator->generatePages();

        $sfItemExport = new sfScheduleOfRateBillItemExcelReportGenerator($project, $bill, null, null, $reportPrintGenerator->printSettings);

        $sfItemExport->setParameter($reportPrintGenerator->tenderers, $reportPrintGenerator->contractorRates);

        $sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPages);

        return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
    }

}