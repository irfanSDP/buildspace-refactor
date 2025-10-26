<?php

/**
 * projectBuilderBuildUpItemsReports actions.
 *
 * @package    buildspace
 * @subpackage projectBuilderBuildUpItemsReports
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectBuilderBuildUpItemsReportsActions extends BaseActions {

	public function executePrintSelectedItemsWithBuildUpQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$itemIds            = $request->getParameter('selectedRows');
		$pageNoPrefix       = $bill->BillLayoutSetting->page_no_prefix;
		$billColumnSettings = $bill->BillColumnSettings;
		$stylesheet         = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;

		list(
			$billItems, $unitsDimensions, $buildUpQuantityItems, $billBuildUpQuantitySummaries, $quantityPerUnitByColumns
			) = BillItemTable::getSelectedItemsWithBuildUpQuantity($bill, $billColumnSettings, $itemIds);

		if ( empty($billItems) )
		{
			$this->message     = 'ERROR';
			$this->explanation = "Nothing can be printed because there are no item(s) selection detected.";

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		list(
			$soqItemsData, $soqFormulatedColumns, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems
			) = ScheduleOfQuantityBillItemXrefTable::getSelectedItemsBuildUpQuantity($project, $billColumnSettings, $billItems);

		$reportPrintGenerator = new sfBillItemBuildUpQtyReportGenerator($bill, $descriptionFormat);
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

		$currency  = $reportPrintGenerator->getCurrency();
		$pageCount = 1;

		foreach ( $billItems as $billItem )
		{
			$billRef    = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
			$dimensions = array();

			// get dimension based on bill item's UOM ID
			foreach ( $unitsDimensions as $unitsDimension )
			{
				if ( $billItem['uom_id'] != $unitsDimension['unit_of_measurement_id'] )
				{
					continue;
				}

				$dimensions[] = $unitsDimension['Dimension'];
			}

			// set available dimension
			$reportPrintGenerator->setAvailableTableHeaderDimensions($dimensions);

			$maxRows = $reportPrintGenerator->getMaxRows();

			foreach ( $billColumnSettings as $billColumnSetting )
			{
				$columnPageCount            = 1;
				$quantityPerUnit            = 0;
				$buildUpItems               = array();
				$soqBuildUpItems            = array();
				$buildUpQuantitySummaryInfo = array();
				$billItemId                 = $billItem['id'];
				$billColumnSettingId        = $billColumnSetting['id'];

				if ( isset( $buildUpQuantityItems[$billColumnSettingId][$billItemId] ) )
				{
					$buildUpItems = $buildUpQuantityItems[$billColumnSettingId][$billItemId];

					unset( $buildUpQuantityItems[$billColumnSettingId][$billItemId] );
				}

				if ( isset( $soqItemsData[$billColumnSettingId][$billItemId] ) )
				{
					$soqBuildUpItems = $soqItemsData[$billColumnSettingId][$billItemId];

					unset( $soqItemsData[$billColumnSettingId][$billItemId] );
				}

				if ( isset( $billBuildUpQuantitySummaries[$billColumnSettingId][$billItemId] ) )
				{
					$buildUpQuantitySummaryInfo = $billBuildUpQuantitySummaries[$billColumnSettingId][$billItemId];

					unset( $billBuildUpQuantitySummaries[$billColumnSettingId][$billItemId] );
				}

				if ( empty( $buildUpItems ) AND empty( $soqBuildUpItems ) )
				{
					continue;
				}

				// need to pass build up qty item(s) into generator to correctly generate the printout page
				$reportPrintGenerator->setBuildUpQuantityItems($buildUpItems);

				$reportPrintGenerator->setSOQBuildUpQuantityItems($soqBuildUpItems);

				$reportPrintGenerator->getSOQFormulatedColumn($soqFormulatedColumns);

				$reportPrintGenerator->setManualBuildUpQuantityMeasurements($manualBuildUpQuantityItems);
				$reportPrintGenerator->setImportedBuildUpQuantityMeasurements($importedBuildUpQuantityItems);

				$pages        = $reportPrintGenerator->generatePages();
				$billItemInfo = $reportPrintGenerator->setupBillItemHeader($billItem, $billRef);

				if ( array_key_exists($billColumnSetting->id, $quantityPerUnitByColumns) && array_key_exists($billItemId, $quantityPerUnitByColumns[$billColumnSetting->id]) )
				{
					$quantityPerUnit = $quantityPerUnitByColumns[$billColumnSetting->id][$billItemId][0];

					unset( $quantityPerUnitByColumns[$billColumnSetting->id][$billItemId] );
				}

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

					$lastPage = ( $columnPageCount == $pages->count() - 1 ) ? true : false;

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'buildUpQuantitySummary'     => $buildUpQuantitySummaryInfo,
						'lastPage'                   => $lastPage,
						'totalQtyPerColumnSetting'   => $quantityPerUnit,
						'billItemInfos'              => $billItemInfo,
						'billItemUOM'                => $billItem['UnitOfMeasurement']['symbol'],
						'itemPage'                   => $page,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'pageCount'                  => $pageCount,
						'elementTitle'               => $billItem['Element']['description'],
						'printingPageTitle'          => $printingPageTitle,
						'billDescription'            => "{$bill->title} > {$billColumnSetting['name']}",
						'columnDescription'          => null,
						'dimensions'                 => $dimensions,
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

					$layout .= $this->getPartial('buildUpQtyReport', $billItemsLayoutParams);

					$pdfGen->addPage($layout);

					unset( $layout, $page );

					$pageCount ++;
					$columnPageCount ++;
				}
			}

			unset( $billItem );
		}

		unset( $billItems );

		return $pdfGen->send();
	}

	public function executeExportExcelSelectedItemsWithBuildUpQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$itemIds            = $request->getParameter('selectedRows');
		$pageNoPrefix       = $bill->BillLayoutSetting->page_no_prefix;
		$billColumnSettings = $bill->BillColumnSettings;

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		list(
			$billItems, $unitsDimensions, $buildUpQuantityItems, $billBuildUpQuantitySummaries, $quantityPerUnitByColumns
			) = BillItemTable::getSelectedItemsWithBuildUpQuantity($bill, $billColumnSettings, $itemIds);

		list(
			$soqItemsData, $soqFormulatedColumns, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems
			) = ScheduleOfQuantityBillItemXrefTable::getSelectedItemsBuildUpQuantity($project, $billColumnSettings, $billItems);

		$reportPrintGenerator = new sfBillItemBuildUpQtyReportGenerator($bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$excelGenerator = new sfBillItemBuildUpQtyExcelExporterGenerator($project, $printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $billItems ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		foreach ( $billItems as $billItem )
		{
			$billRef    = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
			$dimensions = array();

			// get dimension based on bill item's UOM ID
			foreach ( $unitsDimensions as $unitsDimension )
			{
				if ( $billItem['uom_id'] != $unitsDimension['unit_of_measurement_id'] )
				{
					continue;
				}

				$dimensions[] = $unitsDimension['Dimension'];
			}

			// set available dimension
			$reportPrintGenerator->setAvailableTableHeaderDimensions($dimensions);
			$excelGenerator->setColumnDimensions($dimensions);

			foreach ( $billColumnSettings as $billColumnSetting )
			{
				$quantityPerUnit            = 0;
				$buildUpItems               = array();
				$soqBuildUpItems            = array();
				$buildUpQuantitySummaryInfo = array();
				$billItemId                 = $billItem['id'];
				$billColumnSettingId        = $billColumnSetting['id'];

				if ( isset( $buildUpQuantityItems[$billColumnSettingId][$billItemId] ) )
				{
					$buildUpItems = $buildUpQuantityItems[$billColumnSettingId][$billItemId];

					unset( $buildUpQuantityItems[$billColumnSettingId][$billItemId] );
				}

				if ( isset( $soqItemsData[$billColumnSettingId][$billItemId] ) )
				{
					$soqBuildUpItems = $soqItemsData[$billColumnSettingId][$billItemId];

					unset( $soqItemsData[$billColumnSettingId][$billItemId] );
				}

				if ( isset( $billBuildUpQuantitySummaries[$billColumnSettingId][$billItemId] ) )
				{
					$buildUpQuantitySummaryInfo = $billBuildUpQuantitySummaries[$billColumnSettingId][$billItemId];

					unset( $billBuildUpQuantitySummaries[$billColumnSettingId][$billItemId] );
				}

				if ( empty( $buildUpItems ) AND empty( $soqBuildUpItems ) )
				{
					continue;
				}

				// need to pass build up qty item(s) into generator to correctly generate the printout page
				$reportPrintGenerator->setBuildUpQuantityItems($buildUpItems);

				$reportPrintGenerator->setSOQBuildUpQuantityItems($soqBuildUpItems);

				$reportPrintGenerator->getSOQFormulatedColumn($soqFormulatedColumns);

				$reportPrintGenerator->setManualBuildUpQuantityMeasurements($manualBuildUpQuantityItems);
				$reportPrintGenerator->setImportedBuildUpQuantityMeasurements($importedBuildUpQuantityItems);

				$pages        = $reportPrintGenerator->generatePages();
				$billItemInfo = $reportPrintGenerator->setupBillItemHeader($billItem, $billRef);

				if ( array_key_exists($billColumnSetting->id, $quantityPerUnitByColumns) && array_key_exists($billItem['id'], $quantityPerUnitByColumns[$billColumnSetting->id]) )
				{
					$quantityPerUnit = $quantityPerUnitByColumns[$billColumnSetting->id][$billItem['id']][0];
					unset( $quantityPerUnitByColumns[$billColumnSetting->id][$billItem['id']] );
				}

				$excelGenerator->setBillItemUOM($billItem['UnitOfMeasurement']['symbol']);
				$excelGenerator->setBuildUpQuantitySummaryInfo($buildUpQuantitySummaryInfo);
				$excelGenerator->setBillItemInfo($billItemInfo);
				$excelGenerator->setQuantityPerUnit($quantityPerUnit);

				$excelGenerator->process($pages, false, $printingPageTitle, $billItem['Element']['description'], "{$bill->title} > {$billColumnSetting['name']}", $printNoCents, null);
			}

			unset( $billItem );
		}

		unset( $billItems );

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executePrintSelectedItemsWithBuildUpRate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$itemIds      = $request->getParameter('selectedRows');
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$stylesheet   = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;

		list(
			$billItems, $resourceTrades, $buildUpRateItems, $billItemRateFormulatedColumn, $billBuildUpRateSummaries
			) = BillItemTable::getPrintingSelectedItemsWithBuildUpRates($bill, $itemIds);

		$reportPrintGenerator = new sfBillItemBuildUpRateReportGenerator($bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('Landscape');

		if ( empty( $billItems ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = "Nothing can be printed because there are no item(s) selection detected.";

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

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

		$currency  = $reportPrintGenerator->getCurrency();
		$pageCount = 1;

		// will loop on bill item's level first
		foreach ( $billItems as $billItem )
		{
			// after in bill item's loop, use generator to create page by page based on bill item
			$billItemId                   = $billItem['id'];
			$billItemPageCount            = 1;
			$billItemResourceTrades       = array();
			$buildUpItemsByResourceTrades = array();
			$billItemRateValue            = 0;
			$buildUpQuantitySummaryInfo   = array();

			$billRef = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);

			// get associated resource trade(s)
			if ( isset ( $resourceTrades[$billItemId] ) )
			{
				$billItemResourceTrades = $resourceTrades[$billItemId];

				unset( $resourceTrades[$billItemId] );
			}

			// get associated build up item(s)
			if ( isset ( $buildUpRateItems[$billItemId] ) )
			{
				$buildUpItemsByResourceTrades = $buildUpRateItems[$billItemId];

				unset( $buildUpRateItems[$billItemId] );
			}

			if ( isset ( $billItemRateFormulatedColumn[$billItemId] ) )
			{
				$billItemRateValue = $billItemRateFormulatedColumn[$billItemId]['final_value'];

				unset( $billItemRateFormulatedColumn[$billItemId] );
			}

			if ( isset ( $billBuildUpRateSummaries[$billItemId] ) )
			{
				$buildUpQuantitySummaryInfo = $billBuildUpRateSummaries[$billItemId];

				unset( $billBuildUpRateSummaries[$billItemId] );
			}

			$reportPrintGenerator->setResourceTrades($billItemResourceTrades);
			$reportPrintGenerator->setBuildUpItems($buildUpItemsByResourceTrades);

			$pages        = $reportPrintGenerator->generatePages();
			$billItemInfo = $reportPrintGenerator->setupBillItemHeader($billItem, $billRef);
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
					'billItemRateValue'          => $billItemRateValue,
					'buildUpQuantitySummary'     => $buildUpQuantitySummaryInfo,
					'lastPage'                   => $lastPage,
					'billItemInfos'              => $billItemInfo,
					'billItemUOM'                => $billItem['UnitOfMeasurement']['symbol'],
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'pageCount'                  => $pageCount,
					'elementTitle'               => $billItem['Element']['description'],
					'printingPageTitle'          => $printingPageTitle,
					'billDescription'            => $bill->title,
					'columnDescription'          => null,
					'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
					'printNoPrice'               => $withoutPrice,
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

				$layout .= $this->getPartial('buildUpRateReport', $billItemsLayoutParams);

				$pdfGen->addPage($layout);

				unset( $layout, $page );

				$pageCount ++;
				$billItemPageCount ++;
			}

			unset( $billItem );
		}

		return $pdfGen->send();
	}

	public function executeExportExcelSelectedItemsWithBuildUpRate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$itemIds           = $request->getParameter('selectedRows');
		$pageNoPrefix      = $bill->BillLayoutSetting->page_no_prefix;
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		list(
			$billItems, $resourceTrades, $buildUpRateItems, $billItemRateFormulatedColumn, $billBuildUpRateSummaries
			) = BillItemTable::getPrintingSelectedItemsWithBuildUpRates($bill, $itemIds);

		$reportPrintGenerator = new sfBillItemBuildUpRateReportGenerator($bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('Landscape');

		$excelGenerator = new sfBillItemBuildUpRateExcelExporterGenerator($project, $printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $billItems ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		// will loop on bill item's level first
		foreach ( $billItems as $billItem )
		{
			// after in bill item's loop, use generator to create page by page based on bill item
			$billItemId                   = $billItem['id'];
			$billItemRateValue            = 0;
			$billItemResourceTrades       = array();
			$buildUpItemsByResourceTrades = array();
			$buildUpQuantitySummaryInfo   = array();

			$billRef = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);

			// get associated resource trade(s)
			if ( isset ( $resourceTrades[$billItemId] ) )
			{
				$billItemResourceTrades = $resourceTrades[$billItemId];

				unset( $resourceTrades[$billItemId] );
			}

			// get associated build up item(s)
			if ( isset ( $buildUpRateItems[$billItemId] ) )
			{
				$buildUpItemsByResourceTrades = $buildUpRateItems[$billItemId];

				unset( $buildUpRateItems[$billItemId] );
			}

			if ( isset ( $billItemRateFormulatedColumn[$billItemId] ) )
			{
				$billItemRateValue = $billItemRateFormulatedColumn[$billItemId]['final_value'];

				unset( $billItemRateFormulatedColumn[$billItemId] );
			}

			if ( isset ( $billBuildUpRateSummaries[$billItemId] ) )
			{
				$buildUpQuantitySummaryInfo = $billBuildUpRateSummaries[$billItemId];

				unset( $billBuildUpRateSummaries[$billItemId] );
			}

			$reportPrintGenerator->setResourceTrades($billItemResourceTrades);
			$reportPrintGenerator->setBuildUpItems($buildUpItemsByResourceTrades);

			$pages        = $reportPrintGenerator->generatePages();
			$billItemInfo = $reportPrintGenerator->setupBillItemHeader($billItem, $billRef);

			$excelGenerator->setBillItemUOM($billItem['UnitOfMeasurement']['symbol']);
			$excelGenerator->setBuildUpQuantitySummaryInfo($buildUpQuantitySummaryInfo);
			$excelGenerator->setBillItemInfo($billItemInfo);
			$excelGenerator->setRate($billItemRateValue);

			$excelGenerator->process($pages, false, $printingPageTitle, $billItem['Element']['description'], "{$bill->title}", $printNoCents, null);

			unset( $billItem );
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executePrintSelectedItemsWithMarkupBuildUpRate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$pdo          = $bill->getTable()->getConnection()->getDbh();
		$itemIds      = $request->getParameter('selectedRows');
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType = $bill->BillMarkupSetting->rounding_type;
		$stylesheet   = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;
		$elementIds        = array();

		list(
			$billItems, $resourceTrades, $buildUpRateItems, $billItemRateFormulatedColumn, $billBuildUpRateSummaries
			) = BillItemTable::getPrintingSelectedItemsWithBuildUpRates($bill, $itemIds);

		if ( empty( $billItems ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = "Nothing can be printed because there are no item(s) selection detected.";

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		$formulatedColumns = BillItemTable::getFormulatedColumnsByItems($billItems);

		$reportPrintGenerator = new sfBillItemBuildUpRateWithMarkupReportGenerator($bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('Landscape');

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

		$currency  = $reportPrintGenerator->getCurrency();
		$pageCount = 1;

		foreach ( $billItems as $billItem )
		{
			$elementIds[$billItem['element_id']] = $billItem['element_id'];

			unset( $billItem );
		}

		foreach ( $elementIds as $elementId )
		{
			$elementMarkupPercentage = 0;

			if ( $bill->BillMarkupSetting->element_markup_enabled )
			{
				$stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
				JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
				WHERE e.id = " . $elementId . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
				AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

				$stmt->execute();
				$elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
				$elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;

				unset( $elementMarkupResult );
			}

			$markupSettingsInfo = array(
				'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
				'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
				'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
				'element_markup_percentage' => $elementMarkupPercentage,
				'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
				'rounding_type'             => $roundingType
			);

			// will loop on bill item's level first
			foreach ( $billItems as $billItem )
			{
				if ( $billItem['element_id'] != $elementId )
				{
					continue;
				}

				// after in bill item's loop, use generator to create page by page based on bill item
				$billItemId                   = $billItem['id'];
				$billItemPageCount            = 1;
				$billItemResourceTrades       = array();
				$buildUpItemsByResourceTrades = array();
				$buildUpRateSummaryInfo       = array();
				$itemMarkupPercentage         = 0;
				$rate                         = 0;
				$rateAfterMarkup              = 0;

				$billRef = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);

				if ( array_key_exists($billItemId, $formulatedColumns) )
				{
					$itemFormulatedColumns = $formulatedColumns[$billItemId];

					foreach ( $itemFormulatedColumns as $formulatedColumn )
					{
						if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
						{
							$rate = $formulatedColumn['final_value'];
						}

						if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
						{
							$itemMarkupPercentage = $formulatedColumn['final_value'];
						}
					}

					unset( $itemFormulatedColumns );

					$rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
				}

				// get associated resource trade(s)
				if ( isset ( $resourceTrades[$billItemId] ) )
				{
					$billItemResourceTrades = $resourceTrades[$billItemId];

					unset( $resourceTrades[$billItemId] );
				}

				// get associated build up item(s)
				if ( isset ( $buildUpRateItems[$billItemId] ) )
				{
					$buildUpItemsByResourceTrades = $buildUpRateItems[$billItemId];

					unset( $buildUpRateItems[$billItemId] );
				}

				if ( isset ( $billBuildUpRateSummaries[$billItemId] ) )
				{
					$buildUpRateSummaryInfo = $billBuildUpRateSummaries[$billItemId];

					unset( $billBuildUpRateSummaries[$billItemId] );
				}

				$reportPrintGenerator->setItemMarkUpPercentage($itemMarkupPercentage);
				$reportPrintGenerator->setMarkupSettingsInfo($markupSettingsInfo);
				$reportPrintGenerator->setBuildUpRateInfo($buildUpRateSummaryInfo);

				$reportPrintGenerator->setResourceTrades($billItemResourceTrades);
				$reportPrintGenerator->setBuildUpItems($buildUpItemsByResourceTrades);

				$pages        = $reportPrintGenerator->generatePages();
				$billItemInfo = $reportPrintGenerator->setupBillItemHeader($billItem, $billRef);
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
						'billItemRateValue'          => $rateAfterMarkup,
						'lastPage'                   => $lastPage,
						'billItemInfos'              => $billItemInfo,
						'billItemUOM'                => $billItem['UnitOfMeasurement']['symbol'],
						'itemPage'                   => $page,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'pageCount'                  => $pageCount,
						'elementTitle'               => $billItem['Element']['description'],
						'printingPageTitle'          => $printingPageTitle,
						'billDescription'            => $bill->title,
						'columnDescription'          => null,
						'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
						'printNoPrice'               => $withoutPrice,
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

					$layout .= $this->getPartial('buildUpRateMarkupReport', $billItemsLayoutParams);

					$pdfGen->addPage($layout);

					unset( $layout, $page );

					$pageCount ++;
					$billItemPageCount ++;
				}

				unset( $billItem, $buildUpRateSummaryInfo, $itemMarkupPercentage );
			}

			unset( $elementId );
		}

		unset( $elementIds );

		return $pdfGen->send();
	}

	public function executeExportExcelSelectedItemsWithMarkupBuildUpRate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$request->hasParameter('selectedRows') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$pdo          = $bill->getTable()->getConnection()->getDbh();
		$itemIds      = $request->getParameter('selectedRows');
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType = $bill->BillMarkupSetting->rounding_type;

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$elementIds        = array();

		list(
			$billItems, $resourceTrades, $buildUpRateItems, $billItemRateFormulatedColumn, $billBuildUpRateSummaries
			) = BillItemTable::getPrintingSelectedItemsWithBuildUpRates($bill, $itemIds);

		$reportPrintGenerator = new sfBillItemBuildUpRateWithMarkupReportGenerator($bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('Landscape');

		$excelGenerator = new sfBillItemMarkUpBuildUpRateExcelExporterGenerator($project, $printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $billItems ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		$formulatedColumns = BillItemTable::getFormulatedColumnsByItems($billItems);

		foreach ( $billItems as $billItem )
		{
			$elementIds[$billItem['element_id']] = $billItem['element_id'];

			unset( $billItem );
		}

		foreach ( $elementIds as $elementId )
		{
			$elementMarkupPercentage = 0;

			if ( $bill->BillMarkupSetting->element_markup_enabled )
			{
				$stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
				JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
				WHERE e.id = " . $elementId . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
				AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

				$stmt->execute();
				$elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
				$elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;

				unset( $elementMarkupResult );
			}

			$markupSettingsInfo = array(
				'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
				'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
				'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
				'element_markup_percentage' => $elementMarkupPercentage,
				'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
				'rounding_type'             => $roundingType
			);

			// will loop on bill item's level first
			foreach ( $billItems as $billItem )
			{
				if ( $billItem['element_id'] != $elementId )
				{
					continue;
				}

				// after in bill item's loop, use generator to create page by page based on bill item
				$billItemId                   = $billItem['id'];
				$billItemResourceTrades       = array();
				$buildUpItemsByResourceTrades = array();
				$buildUpRateSummaryInfo       = array();
				$itemMarkupPercentage         = 0;
				$rate                         = 0;
				$rateAfterMarkup              = 0;

				$billRef = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);

				if ( array_key_exists($billItemId, $formulatedColumns) )
				{
					$itemFormulatedColumns = $formulatedColumns[$billItemId];

					foreach ( $itemFormulatedColumns as $formulatedColumn )
					{
						if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
						{
							$rate = $formulatedColumn['final_value'];
						}

						if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
						{
							$itemMarkupPercentage = $formulatedColumn['final_value'];
						}
					}

					unset( $itemFormulatedColumns );

					$rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
				}

				// get associated resource trade(s)
				if ( isset ( $resourceTrades[$billItemId] ) )
				{
					$billItemResourceTrades = $resourceTrades[$billItemId];

					unset( $resourceTrades[$billItemId] );
				}

				// get associated build up item(s)
				if ( isset ( $buildUpRateItems[$billItemId] ) )
				{
					$buildUpItemsByResourceTrades = $buildUpRateItems[$billItemId];

					unset( $buildUpRateItems[$billItemId] );
				}

				if ( isset ( $billBuildUpRateSummaries[$billItemId] ) )
				{
					$buildUpRateSummaryInfo = $billBuildUpRateSummaries[$billItemId];

					unset( $billBuildUpRateSummaries[$billItemId] );
				}

				$reportPrintGenerator->setItemMarkUpPercentage($itemMarkupPercentage);
				$reportPrintGenerator->setMarkupSettingsInfo($markupSettingsInfo);
				$reportPrintGenerator->setBuildUpRateInfo($buildUpRateSummaryInfo);

				$reportPrintGenerator->setResourceTrades($billItemResourceTrades);
				$reportPrintGenerator->setBuildUpItems($buildUpItemsByResourceTrades);

				$pages        = $reportPrintGenerator->generatePages();
				$billItemInfo = $reportPrintGenerator->setupBillItemHeader($billItem, $billRef);

				$excelGenerator->setBillItemUOM($billItem['UnitOfMeasurement']['symbol']);
				$excelGenerator->setBuildUpQuantitySummaryInfo($buildUpRateSummaryInfo);
				$excelGenerator->setBillItemInfo($billItemInfo);
				$excelGenerator->setRate($rateAfterMarkup);

				$excelGenerator->process($pages, false, $printingPageTitle, $billItem['Element']['description'], "{$bill->title}", $printNoCents, null);

				unset( $billItem, $buildUpRateSummaryInfo, $itemMarkupPercentage );
			}

			unset( $elementId );
		}

		unset( $elementIds );

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

}