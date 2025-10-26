<?php

/**
 * printReport actions.
 *
 * @package    buildspace
 * @subpackage printReport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class printReportActions extends BaseActions {

	public function executePrintBill(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = ProjectStructureTable::getInstance()->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$billIds           = json_decode($request->getParameter('selectedRows'), true);

		$sortingType = $request->getParameter('sortingType');

		$reportPrintGenerator = new sfBuildspaceReportBillPageGenerator($project, $tendererIds, $billIds, $sortingType, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$template    = ( $sortingType ) ? 'printReport/bqReportBill' : 'printReport/bqReportComparisonBill';
		$participate = ( $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED ) ? true : false;

		$totalPage = count($pages) - 1;
		$pageCount = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $key => $page )
			{
				$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

				if ( empty( $page ) )
				{
					continue;
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'                      => $page,
					'maxRows'                       => $maxRows + 2,
					'currency'                      => $currency,
					'printGrandTotal'               => $printGrandTotal,
					'participate'                   => $participate,
					'tenderers'                     => $reportPrintGenerator->tenderers,
					'tenderersBillTotals'           => $reportPrintGenerator->contractorBillGrandTotals,
					'selectedTenderer'              => $reportPrintGenerator->selectedTenderer,
					'selectedBillTotals'            => $reportPrintGenerator->selectedBillTotals,
					'rationalizedBillTotals'        => $reportPrintGenerator->rationalizedBillTotals,
					'estimateProjectGrandTotal'     => $reportPrintGenerator->getEstimateProjectGrandTotal(),
					'selectedProjectGrandTotal'     => $reportPrintGenerator->getSelectedProjectGrandTotal(),
					'rationalizedProjectGrandTotal' => $reportPrintGenerator->getRationalizedProjectGrandTotal(),
					'contractorProjectGrandTotals'  => $reportPrintGenerator->getContractorProjectGrandTotals(),
					'pageCount'                     => $pageCount,
					'totalPage'                     => $totalPage,
					'reportTitle'                   => $printingPageTitle,
					'topLeftRow1'                   => '',
					'topLeftRow2'                   => $project->title,
					'botLeftRow1'                   => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
					'botLeftRow2'                   => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
					'descHeader'                    => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'                    => $reportPrintGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'                    => $reportPrintGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'                     => $reportPrintGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'                     => $reportPrintGenerator->getTableHeaderAmtPrefix(),
					'toCollection'                  => $reportPrintGenerator->getToCollectionPrefix(),
					'priceFormatting'               => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
					'printNoPrice'                  => $withoutPrice,
					'toggleColumnArrangement'       => $reportPrintGenerator->getToggleColumnArrangement(),
					'printElementTitle'             => $reportPrintGenerator->getPrintElementTitle(),
					'printDollarAndCentColumn'      => $reportPrintGenerator->getPrintDollarAndCentColumn(),
					'currencyFormat'                => $reportPrintGenerator->getCurrencyFormat(),
					'rateCommaRemove'               => $reportPrintGenerator->getRateCommaRemove(),
					'qtyCommaRemove'                => $reportPrintGenerator->getQtyCommaRemove(),
					'amtCommaRemove'                => $reportPrintGenerator->getAmtCommaRemove(),
					'printAmountOnly'               => $reportPrintGenerator->getPrintAmountOnly(),
					'printElementInGridOnce'        => $reportPrintGenerator->getPrintElementInGridOnce(),
					'indentItem'                    => $reportPrintGenerator->getIndentItem(),
					'printElementInGrid'            => $reportPrintGenerator->getPrintElementInGrid(),
					'pageNoPrefix'                  => $reportPrintGenerator->getPageNoPrefix(),
					'printDateOfPrinting'           => $reportPrintGenerator->getPrintDateOfPrinting(),
					'alignElementTitleToTheLeft'    => $reportPrintGenerator->getAlignElementToLeft(),
				);

				$layout .= $this->getPartial($template, $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedElement(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$elementIds        = json_decode($request->getParameter('selectedRows'), true);

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$sortingType = $request->getParameter('sortingType');

		$reportPrintGenerator = new sfBuildspaceReportElementPageGenerator($bill, $tendererIds, $elementIds, $sortingType, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$template    = ( $sortingType ) ? 'printReport/bqReportElement' : 'printReport/bqReportComparisonElement';
		$project     = ProjectStructureTable::getInstance()->find($bill->root_id);
		$participate = ( $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED ) ? true : false;

		$totalPage = count($pages) - 1;
		$pageCount = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $key => $page )
			{
				$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

				if ( empty( $page ) )
				{
					continue;
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'printGrandTotal'            => $printGrandTotal,
					'participate'                => $participate,
					'tenderers'                  => $reportPrintGenerator->tenderers,
					'tenderersElementTotals'     => $reportPrintGenerator->contractorElementGrandTotals,
					'selectedTenderer'           => $reportPrintGenerator->selectedTenderer,
					'selectedElementTotals'      => $reportPrintGenerator->selectedElementTotals,
					'rationalizedElementTotals'  => $reportPrintGenerator->rationalizedElementTotals,
					'estimateBillGrandTotal'     => $reportPrintGenerator->getEstimateBillGrandTotal(),
					'selectedBillGrandTotal'     => $reportPrintGenerator->getSelectedBillGrandTotal(),
					'rationalizedBillGrandTotal' => $reportPrintGenerator->getRationalizedBillGrandTotal(),
					'contractorBillGrandTotals'  => $reportPrintGenerator->getContractorBillGrandTotals(),
					'pageCount'                  => $pageCount,
					'totalPage'                  => $totalPage,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => '',
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

				$layout .= $this->getPartial($template, $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$element = BillElementTable::getInstance()->find($request->getParameter('elementId'));

		$billColumnSettings = $bill->getBillColumnSettings()->toArray();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$sortingType = $request->getParameter('sortingType');

		$reportPrintGenerator = new sfBuildspaceReportPageGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

		$pages                      = $reportPrintGenerator->generatePages();
		$maxRows                    = $reportPrintGenerator->getMaxRows();
		$currency                   = $reportPrintGenerator->getCurrency();
		$contractorElementTotals    = $reportPrintGenerator->getContractorElementGrandTotals();
		$estimateElementGrandTotals = $reportPrintGenerator->getEstimateElementGrandTotals();
		$withoutPrice               = false;
		$stylesheet                 = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

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
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $page['item_pages']->offsetGet($i),
						'billColumnSettings'         => $billColumnSettings,
						'maxRows'                    => $maxRows + 2,
						'printGrandTotal'            => $printGrandTotal,
						'contractorElementTotals'    => ( $printGrandTotal && array_key_exists($key, $contractorElementTotals) ) ? $contractorElementTotals[$key] : array(),
						'estimateElementTotal'       => ( $printGrandTotal && array_key_exists($key, $estimateElementGrandTotals) ) ? $estimateElementGrandTotals[$key] : 0,
						'currency'                   => $currency,
						'elementHeaderDescription'   => $page['description'],
						'elementCount'               => $page['element_count'],
						'tenderers'                  => $reportPrintGenerator->tenderers,
						'tendererRates'              => $reportPrintGenerator->contractorRates,
						'printQty'                   => true,
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
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

					$layout .= $this->getPartial('printReport/bqReport', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintItemTotal(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$element            = BillElementTable::getInstance()->find($request->getParameter('elementId'));
		$billColumnSettings = $bill->getBillColumnSettings()->toArray();

        // Printing page title, can be null as user may not want to print page title
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        // only applicable if this has is being used for all Tenderers
		$sortingType = $request->getParameter('sortingType');

		$reportPrintGenerator = new sfBuildspaceReportPageItemTotalGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

		$pages                      = $reportPrintGenerator->generatePages();
		$maxRows                    = $reportPrintGenerator->getMaxRows();
		$currency                   = $reportPrintGenerator->getCurrency();
		$contractorElementTotals    = $reportPrintGenerator->getContractorElementGrandTotals();
		$estimateElementGrandTotals = $reportPrintGenerator->getEstimateElementGrandTotals();
        $withoutPrice               = false;
        $stylesheet                 = $this->getBQStyling();

        $pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

        $pdfGen->setOptions([
            'margin-top' => 8,
            'margin-right' => 10,
            'margin-bottom' => 3,
            'margin-left' => 14
        ]);

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
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $page['item_pages']->offsetGet($i),
						'billColumnSettings'         => $billColumnSettings,
						'maxRows'                    => $maxRows + 2,
						'printGrandTotal'            => $printGrandTotal,
						'contractorElementTotals'    => ( $printGrandTotal && array_key_exists($key, $contractorElementTotals) ) ? $contractorElementTotals[$key] : array(),
						'estimateElementTotal'       => ( $printGrandTotal && array_key_exists($key, $estimateElementGrandTotals) ) ? $estimateElementGrandTotals[$key] : 0,
						'currency'                   => $currency,
						'elementHeaderDescription'   => $page['description'],
						'elementCount'               => $page['element_count'],
						'tenderers'                  => $reportPrintGenerator->tenderers,
						'tendererRates'              => $reportPrintGenerator->contractorRates,
						'printQty'                   => false,
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
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

					$layout .= $this->getPartial('printReport/bqReport', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

    /**
     * Prints item rate and total of the estimate (by project owner) and tenderers.
     *
     * @param sfWebRequest $request
     *
     * @return bool
     * @throws sfError404Exception
     */
    public function executePrintItemRateAndTotal(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
        );

        session_write_close();

        $element = BillElementTable::getInstance()->find($request->getParameter('elementId'));
        $billColumnSettings = $bill->getBillColumnSettings()->toArray();

        // Printing page title, can be null as user may not want to print page title
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
        $itemIds           = json_decode($request->getParameter('selectedRows'), true);

        $priceFormat  = $request->getParameter('priceFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        // only applicable for all Tenderers
        $sortingType = $request->getParameter('sortingType');

        $reportPrintGenerator = new sfBuildspaceReportItemRateAndTotalPageGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

        $pages                      = $reportPrintGenerator->generatePages();
        $maxRows                    = $reportPrintGenerator->getMaxRows();
        $currency                   = $reportPrintGenerator->getCurrency();
        $contractorElementTotals    = $reportPrintGenerator->getContractorElementGrandTotals();
        $estimateElementGrandTotals = $reportPrintGenerator->getEstimateElementGrandTotals();
        $withoutPrice               = false;
        $stylesheet                 = $this->getBQStyling();

        $pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

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
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'billColumnSettings'         => $billColumnSettings,
                        'maxRows'                    => $maxRows + 2,
                        'printGrandTotal'            => $printGrandTotal,
                        'contractorElementTotals'    => ( $printGrandTotal && array_key_exists($key, $contractorElementTotals) ) ? $contractorElementTotals[$key] : array(),
                        'estimateElementTotal'       => ( $printGrandTotal && array_key_exists($key, $estimateElementGrandTotals) ) ? $estimateElementGrandTotals[$key] : 0,
                        'currency'                   => $currency,
                        'elementHeaderDescription'   => $page['description'],
                        'elementCount'               => $page['element_count'],
                        'tenderers'                  => $reportPrintGenerator->tenderers,
                        'tendererRates'              => $reportPrintGenerator->contractorRates,
                        'tendererTotals'             => $reportPrintGenerator->contractorTotals,
                        'printQty'                   => true,
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $reportPrintGenerator->totalPage,
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

                    $layout .= $this->getPartial('printReport/bqReportItemRateAndTotal', $billItemsLayoutParams);

                    $page['item_pages']->offsetUnset($i);

                    $pdfGen->addPage($layout);

                    $pageCount ++;
                }
            }
        }

        return $pdfGen->send();
    }

    public function executePrintScheduleOfRateItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
        );

        session_write_close();

        $element = ScheduleOfRateBillElementTable::getInstance()->find($request->getParameter('elementId'));

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tendererIds = json_decode($request->getParameter('selectedTenderers'), true);
        $itemIds = json_decode($request->getParameter('selectedRows'), true);

        $priceFormat = $request->getParameter('priceFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $sortingType = $request->getParameter('sortingType');

        $reportPrintGenerator = new sfBuildspaceReportScheduleOfRateItemPageGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

        $pages = $reportPrintGenerator->generatePages();

        $stylesheet = $this->getBQStyling();

        $pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

        $pageCount = 1;

        foreach($pages as $key => $page)
        {
            for($i = 1; $i <= $page['item_pages']->count(); $i++)
            {
                if( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'maxRows'                    => $reportPrintGenerator->getMaxRows() + 2, // +2 rows for footer.
                        'currency'                   => $reportPrintGenerator->getCurrency(),
                        'elementHeaderDescription'   => $page['description'],
                        'elementCount'               => $page['element_count'],
                        'tenderers'                  => $reportPrintGenerator->tenderers,
                        'tendererRates'              => $reportPrintGenerator->contractorRates,
                        'pageCount'                  => $pageCount,
                        'totalPages'                 => $reportPrintGenerator->totalPages,
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
                        'topLeftRow2'                => $bill->title,
                        'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => false,
                        'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
                        'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
                        'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
                        'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
                        'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('printReport/scheduleOfRateReport', $billItemsLayoutParams);

                    $page['item_pages']->offsetUnset($i);

                    $pdfGen->addPage($layout);

                    $pageCount++;
                }
            }
        }

        return $pdfGen->send();
    }

	public function executePrintSelectedItemRate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$element            = BillElementTable::getInstance()->find($request->getParameter('elementId'));
		$billColumnSettings = $bill->getBillColumnSettings()->toArray();

		// Printing page title, can be null as user may don't want to print page title
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		// only applicable if this has is being used as All Tenderers
		$sortingType = $request->getParameter('sortingType');

		$reportPrintGenerator = new sfBuildspaceReportPageItemComparisonGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;

		$project     = ProjectStructureTable::getInstance()->find($bill->root_id);
		$participate = ( $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED ) ? true : false;

		$params = array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => 8,
			'margin-right'   => 10,
			'margin-bottom'  => 3,
			'margin-left'    => 14,
			'page-size'      => $reportPrintGenerator->getPageSize(),
			'orientation'    => $reportPrintGenerator->getOrientation()
		);

		$estimateElementGrandTotals     = $reportPrintGenerator->getEstimateElementGrandTotals();
		$selectedElementGrandTotals     = $reportPrintGenerator->selectedElementGrandTotal;
		$rationalizedElementGrandTotals = $reportPrintGenerator->rationalizedElementGrandTotal;
		$stylesheet                     = $this->getBQStyling();

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
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $page['item_pages']->offsetGet($i),
						'billColumnSettings'         => $billColumnSettings,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'elementHeaderDescription'   => $page['description'],
						'elementCount'               => $page['element_count'],
						'selectedTenderer'           => $reportPrintGenerator->selectedTenderer,
						'selectedRates'              => $reportPrintGenerator->selectedRates,
						'rationalizedRates'          => $reportPrintGenerator->rationalizedRates,
						'participate'                => $participate,
						'printQty'                   => true,
						'printGrandTotal'            => $printGrandTotal,
						'estimateElementTotal'       => ( $printGrandTotal && array_key_exists($key, $estimateElementGrandTotals) ) ? $estimateElementGrandTotals[$key] : 0,
						'selectedElementTotal'       => ( $printGrandTotal && array_key_exists($key, $selectedElementGrandTotals) ) ? $selectedElementGrandTotals[$key] : 0,
						'rationalizedElementTotal'   => ( $printGrandTotal && array_key_exists($key, $rationalizedElementGrandTotals) ) ? $rationalizedElementGrandTotals[$key] : 0,
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
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

					$layout .= $this->getPartial('printReport/bqReportComparison', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedItemTotal(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$element            = BillElementTable::getInstance()->find($request->getParameter('elementId'));
		$billColumnSettings = $bill->getBillColumnSettings()->toArray();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$sortingType = $request->getParameter('sortingType');

		$reportPrintGenerator = new sfBuildspaceReportPageItemTotalComparisonGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;

		$project     = ProjectStructureTable::getInstance()->find($bill->root_id);
		$participate = ( $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED ) ? true : false;

		$params = array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => 8,
			'margin-right'   => 10,
			'margin-bottom'  => 3,
			'margin-left'    => 14,
			'page-size'      => $reportPrintGenerator->getPageSize(),
			'orientation'    => $reportPrintGenerator->getOrientation()
		);

		$estimateElementGrandTotals     = $reportPrintGenerator->getEstimateElementGrandTotals();
		$selectedElementGrandTotals     = $reportPrintGenerator->selectedElementGrandTotal;
		$rationalizedElementGrandTotals = $reportPrintGenerator->rationalizedElementGrandTotal;
		$stylesheet                     = $this->getBQStyling();

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
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $page['item_pages']->offsetGet($i),
						'billColumnSettings'         => $billColumnSettings,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'elementHeaderDescription'   => $page['description'],
						'elementCount'               => $page['element_count'],
						'selectedTenderer'           => $reportPrintGenerator->selectedTenderer,
						'selectedRates'              => $reportPrintGenerator->selectedRates,
						'rationalizedRates'          => $reportPrintGenerator->rationalizedRates,
						'participate'                => $participate,
						'printQty'                   => false,
						'printGrandTotal'            => $printGrandTotal,
						'estimateElementTotal'       => ( $printGrandTotal && array_key_exists($key, $estimateElementGrandTotals) ) ? $estimateElementGrandTotals[$key] : 0,
						'selectedElementTotal'       => ( $printGrandTotal && array_key_exists($key, $selectedElementGrandTotals) ) ? $selectedElementGrandTotals[$key] : 0,
						'rationalizedElementTotal'   => ( $printGrandTotal && array_key_exists($key, $rationalizedElementGrandTotals) ) ? $rationalizedElementGrandTotals[$key] : 0,
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
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

					$layout .= $this->getPartial('printReport/bqReportComparison', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$affectedElements = BillElementTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows'));

		$reportPrintGenerator = new sfBuildspacePostContractReportPageItemGenerator($project, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat);

		$elementPages = $reportPrintGenerator->generatePages($typeRef);
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

		foreach ( $elementPages as $elementId => $pages )
		{
			for ( $i = 1; $i <= $pages['item_pages']->count(); $i ++ )
			{
				if ( $pages['item_pages'] instanceof SplFixedArray and $pages['item_pages']->offsetExists($i) )
				{
					$printGrandTotal = ( ( $i + 1 ) == $pages['item_pages']->count() ) ? true : false;

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$topLeftRow2 = $bill->title;

					if ( $printWithoutUnit )
					{
						$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
					}

					$billItemsLayoutParams = array(
						'itemPage'                   => $pages['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'elementTotals'              => ( array_key_exists($elementId, $reportPrintGenerator->elementTotals) ) ? $reportPrintGenerator->elementTotals[$elementId] : array(),
						'printGrandTotal'            => $printGrandTotal,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
						'topLeftRow2'                => $topLeftRow2,
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

					$layout .= $this->getPartial('printReport/postContractStandardClaimReportItem', $billItemsLayoutParams);

					$pages['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractSelectedElement(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$elementIds        = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$reportPrintGenerator = new sfBuildspacePostContractReportPageElementGenerator($project, $bill, $elementIds, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages($typeRef);
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$totalPage = count($pages) - 1;

		$pageCount = 1;

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $key => $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$topLeftRow2 = $bill->title;

				if ( $printWithoutUnit )
				{
					$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
				}

				$billItemsLayoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
					'pageCount'                  => $pageCount,
					'totalPage'                  => $totalPage,
					'printGrandTotal'            => $printGrandTotal,
					'typeTotals'                 => $reportPrintGenerator->typeTotals,
					'workdoneOnly'               => false,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $project->title,
					'topLeftRow2'                => $topLeftRow2,
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

				$layout .= $this->getPartial('printReport/postContractStandardClaimReportElement', $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractElementWorkdoneOnly(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$pdo = $bill->getTable()->getConnection()->getDbh();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$stmt = $pdo->prepare("SELECT e.id FROM " . BillElementTable::getInstance()->getTableName() . " e
			WHERE e.project_structure_id = " . $bill->id . " AND e.deleted_at IS NULL ORDER BY e.priority ASC");
		$stmt->execute();

		$elementIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

		$reportPrintGenerator = new sfBuildspacePostContractReportPageElementGenerator($project, $bill, $elementIds, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages($typeRef);
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$totalPage = count($pages) - 1;

		$pageCount = 1;

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $key => $page )
			{
				$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

				if ( empty( $page ) )
				{
					continue;
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$topLeftRow2 = $bill->title;

				if ( $printWithoutUnit )
				{
					$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
				}

				$billItemsLayoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 1,
					'currency'                   => $currency,
					'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
					'pageCount'                  => $pageCount,
					'totalPage'                  => $totalPage,
					'printGrandTotal'            => $printGrandTotal,
					'typeTotals'                 => $reportPrintGenerator->typeTotals,
					'workdoneOnly'               => true,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $project->title,
					'topLeftRow2'                => $topLeftRow2,
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

				$layout .= $this->getPartial('printReport/postContractStandardClaimReportElement', $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractElementByTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspacePostContractReportPageElementByTypeGenerator($project, $bill, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$totalPage = count($pages) - 1;

		$pageCount = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $key => $page )
			{
				$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

				if ( empty( $page ) )
				{
					continue;
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
					'pageCount'                  => $pageCount,
					'totalPage'                  => $totalPage,
					'printGrandTotal'            => $printGrandTotal,
					'typeTotals'                 => $reportPrintGenerator->typeTotals,
					'withUnit'                   => false,
					'elementTypeTotals'          => $reportPrintGenerator->elementTotals,
					'billColumnSettings'         => $bill->BillColumnSettings->toArray(),
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $project->title,
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

				$layout .= $this->getPartial('printReport/postContractStandardClaimReportElementTypes', $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractElementWithClaimByTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspacePostContractReportPageElementByTypeWithClaimGenerator($project, $bill, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$totalPage = count($pages) - 1;

		$pageCount = 1;

		if ( $pages instanceof SplFixedArray )
		{
			$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

			foreach ( $pages as $key => $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
					'pageCount'                  => $pageCount,
					'totalPage'                  => $totalPage,
					'printGrandTotal'            => $printGrandTotal,
					'typeTotals'                 => $reportPrintGenerator->typeTotals,
					'withUnit'                   => true,
					'unitNames'                  => $reportPrintGenerator->unitNames,
					'elementTypeTotals'          => $reportPrintGenerator->elementTotals,
					'billColumnSettings'         => $reportPrintGenerator->billColumnSettings,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $project->title,
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

				$layout .= $this->getPartial('printReport/postContractStandardClaimReportElementTypes', $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractElementWithClaimByTypesBySelectedUnits(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$typeIds           = json_decode($request->getParameter('selectedRows'), true);

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspacePostContractReportPageElementByTypeSelectedUnits($project, $bill, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages($typeIds);
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$totalPage = count($pages) - 1;

		$pageCount = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $key => $page )
			{
				$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;

				if ( empty( $page ) )
				{
					continue;
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
					'pageCount'                  => $pageCount,
					'totalPage'                  => $totalPage,
					'printGrandTotal'            => $printGrandTotal,
					'typeTotals'                 => $reportPrintGenerator->typeTotals,
					'withUnit'                   => true,
					'unitNames'                  => $reportPrintGenerator->unitNames,
					'elementTypeTotals'          => $reportPrintGenerator->elementTotals,
					'billColumnSettings'         => $reportPrintGenerator->billColumnSettings,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $project->title,
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

				$layout .= $this->getPartial('printReport/postContractStandardClaimReportElementTypesSelectedUnit', $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractItemWithCurrentClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractReportPageItemWithCurrentClaimGenerator($project, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages($typeRef);
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

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
					));

					$topLeftRow2 = $bill->title;

					if ( $printWithoutUnit )
					{
						$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
					}

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
						'topLeftRow2'                => $topLeftRow2,
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

					$layout .= $this->getPartial('printReport/postContractStandardClaimReportItem', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractItemWithClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractReportPageItemWithClaimGenerator($project, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages($typeRef);
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

		foreach ( $pages as $key => $page )
		{
			for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
			{
				if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
				{
					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$topLeftRow2 = $bill->title;

					if ( $printWithoutUnit )
					{
						$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
					}

					$billItemsLayoutParams = array(
						'itemPage'                   => $page['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'printGrandTotal'            => true,
						'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
						'topLeftRow2'                => $topLeftRow2,
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

					$layout .= $this->getPartial('printReport/postContractStandardClaimReportItem', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractItemWorkdoneOnlyWithQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractReportPageItemWithClaimGenerator($project, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages($typeRef);
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

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
					));

					$topLeftRow2 = $bill->title;

					if ( $printWithoutUnit )
					{
						$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
					}

					$billItemsLayoutParams = array(
						'itemPage'                   => $page['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
						'printGrandTotal'            => $printGrandTotal,
						'printQty'                   => true,
						'printPercentage'            => false,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
						'topLeftRow2'                => $topLeftRow2,
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

					$layout .= $this->getPartial('printReport/postContractStandardClaimReportItemWorkdoneOnly', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractItemWorkdoneOnlyWithPercentage(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractReportPageItemWithClaimGenerator($project, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages($typeRef);
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

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
					));

					$topLeftRow2 = $bill->title;

					if ( $printWithoutUnit )
					{
						$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
					}

					$billItemsLayoutParams = array(
						'itemPage'                   => $page['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'],
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'elementTotals'              => $reportPrintGenerator->elementTotals[$key],
						'printGrandTotal'            => $printGrandTotal,
						'printQty'                   => false,
						'printPercentage'            => true,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
						'topLeftRow2'                => $topLeftRow2,
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

					$layout .= $this->getPartial('printReport/postContractStandardClaimReportItemWorkdoneOnly', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractPrelimSelectedItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$affectedElements  = BillElementTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows'));
		$type              = null;

		$reportPrintGenerator = new sfBuildspacePostContractPrelimReportPageItemGenerator($project, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

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
					));

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

					$layout .= $this->getPartial('printReport/postContractPrelimClaimReportItem', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintPostContractPrelimSelectedItemWithClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$affectedElements  = BillElementTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows'));
		$type              = null;

		$reportPrintGenerator = new sfBuildspacePostContractPrelimReportPageItemGenerator($project, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

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
					));

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

	public function executePrintPostContractPrelimAllItemWithClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
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

		$reportPrintGenerator = new sfBuildspacePostContractPrelimReportPageAllItemGenerator($project, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

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
					));

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

	public function executePrintPostContractPrelimAllItemWithClaimMoreThanZero(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
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

		$reportPrintGenerator = new sfBuildspacePostContractPrelimReportPageAllItemGeneratorMoreThanZero($project, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

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
					));

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

	public function executePrintSelectedResourceItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->hasParameter('resourceId') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeIds          = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceResourceItemGenerator($project, $request->getParameter('resourceId'), $tradeIds, $printingPageTitle, $descriptionFormat);

		$tradePages   = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		foreach ( $tradePages as $tradeId => $pages )
		{
			for ( $i = 1; $i <= $pages['item_pages']->count(); $i ++ )
			{
				if ( $pages['item_pages'] instanceof SplFixedArray and $pages['item_pages']->offsetExists($i) )
				{
					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $pages['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => '',
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'tradeTotals'                => '',
						'printGrandTotal'            => false,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => $reportPrintGenerator->resource['name'],
						'topLeftRow2'                => $project->title,
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

					$layout .= $this->getPartial('printReport/tradeReportItem', $billItemsLayoutParams);

					$pages['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedTradeBillItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) AND
			$trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('tradeId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeItemIds      = json_decode($request->getParameter('selectedTradeItemIds'), true);
		$billItemIds       = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceResourceTradeItemGenerator($project, $request->getParameter('resourceId'), $tradeItemIds, $billItemIds, $printingPageTitle, $descriptionFormat);

		$resourcePages = $reportPrintGenerator->generatePages();
		$maxRows       = $reportPrintGenerator->getMaxRows();
		$currency      = $reportPrintGenerator->getCurrency();
		$withoutPrice  = false;
		$stylesheet    = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		foreach ( $resourcePages as $resourceId => $elements )
		{
			foreach ( $elements as $billElementId => $pages )
			{
				for ( $i = 1; $i <= $pages['item_pages']->count(); $i ++ )
				{
					if ( $pages['item_pages'] instanceof SplFixedArray and $pages['item_pages']->offsetExists($i) )
					{
						$layout = $this->getPartial('printReport/pageLayout', array(
							'stylesheet'    => $stylesheet,
							'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
						));

						$billItemsLayoutParams = array(
							'itemPage'                   => $pages['item_pages']->offsetGet($i),
							'maxRows'                    => $maxRows + 2,
							'currency'                   => $currency,
							'headerDescription'          => $reportPrintGenerator->resource['name'],
							'pageCount'                  => $pageCount,
							'totalPage'                  => $reportPrintGenerator->totalPage,
							'tradeTotals'                => '',
							'printGrandTotal'            => false,
							'reportTitle'                => $printingPageTitle,
							'topLeftRow1'                => ( array_key_exists($billElementId, $reportPrintGenerator->billElementIdToDescription) ) ? $reportPrintGenerator->billElementIdToDescription[$billElementId] : '',
							'topLeftRow2'                => ( array_key_exists($resourceId, $reportPrintGenerator->resourceIdToDescription) ) ? $reportPrintGenerator->resourceIdToDescription[$resourceId] : '',
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

						$layout .= $this->getPartial('printReport/billTradeReportItem', $billItemsLayoutParams);

						$pages['item_pages']->offsetUnset($i);

						$pdfGen->addPage($layout);

						$pageCount ++;
					}
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedScheduleOfRateTradeItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) AND
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeIds          = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceScheduleOfRateItemGenerator($project, $scheduleOfRate, $tradeIds, $printingPageTitle, $descriptionFormat);

		$tradePages   = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		foreach ( $tradePages as $tradeId => $pages )
		{
			for ( $i = 1; $i <= $pages['item_pages']->count(); $i ++ )
			{
				if ( $pages['item_pages'] instanceof SplFixedArray and $pages['item_pages']->offsetExists($i) )
				{
					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $pages['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => '',
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'tradeTotals'                => '',
						'printGrandTotal'            => false,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => $reportPrintGenerator->scheduleOfRate['name'],
						'topLeftRow2'                => $project->title,
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

					$layout .= $this->getPartial('printReport/scheduleOfRateTradeReportItem', $billItemsLayoutParams);

					$pages['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedScheduleOfRateTradeItemsWithSelectedTendererRates(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) AND
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeIds          = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceScheduleOfRateWithSelectedTendererRatesItemGenerator($project, $scheduleOfRate, $tradeIds, $printingPageTitle, $descriptionFormat);

		$tradePages   = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		foreach ( $tradePages as $tradeId => $pages )
		{
			for ( $i = 1; $i <= $pages['item_pages']->count(); $i ++ )
			{
				if ( $pages['item_pages'] instanceof SplFixedArray and $pages['item_pages']->offsetExists($i) )
				{
					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $pages['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => '',
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'tradeTotals'                => '',
						'printGrandTotal'            => false,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => $reportPrintGenerator->scheduleOfRate['name'],
						'topLeftRow2'                => $project->title,
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

					$layout .= $this->getPartial('printReport/scheduleOfRateTradeReportItem', $billItemsLayoutParams);

					$pages['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedScheduleOfRateTradeBillItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId')) and
			$scheduleOfRateTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('tradeId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeItemIds      = json_decode($request->getParameter('tradeItemIds'), true);
		$billItemIds       = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceScheduleOfRateTradeItemGenerator($project, $scheduleOfRate, $scheduleOfRateTrade, $tradeItemIds, $billItemIds, $printingPageTitle, $descriptionFormat);

		$scheduleOfRatePages = $reportPrintGenerator->generatePages();
		$maxRows             = $reportPrintGenerator->getMaxRows();
		$currency            = $reportPrintGenerator->getCurrency();
		$withoutPrice        = false;
		$stylesheet          = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		foreach ( $scheduleOfRatePages as $tradeId => $elements )
		{
			foreach ( $elements as $billElementId => $pages )
			{
				for ( $i = 1; $i <= $pages['item_pages']->count(); $i ++ )
				{
					if ( $pages['item_pages'] instanceof SplFixedArray and $pages['item_pages']->offsetExists($i) )
					{
						$layout = $this->getPartial('printReport/pageLayout', array(
							'stylesheet'    => $stylesheet,
							'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
						));

						$billItemsLayoutParams = array(
							'itemPage'                   => $pages['item_pages']->offsetGet($i),
							'maxRows'                    => $maxRows + 2,
							'currency'                   => $currency,
							'headerDescription'          => $reportPrintGenerator->scheduleOfRate['name'],
							'pageCount'                  => $pageCount,
							'totalPage'                  => $reportPrintGenerator->totalPage,
							'tradeTotals'                => '',
							'printGrandTotal'            => false,
							'reportTitle'                => $printingPageTitle,
							'topLeftRow1'                => ( array_key_exists($billElementId, $reportPrintGenerator->billElementIdToDescription) ) ? $reportPrintGenerator->billElementIdToDescription[$billElementId] : '',
							'topLeftRow2'                => ( array_key_exists($tradeId, $reportPrintGenerator->tradeIdToDescription) ) ? $reportPrintGenerator->tradeIdToDescription[$tradeId] : '',
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

						$layout .= $this->getPartial('printReport/scheduleOfRateBillTradeReportItem', $billItemsLayoutParams);

						$pages['item_pages']->offsetUnset($i);

						$pdfGen->addPage($layout);

						$pageCount ++;
					}
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedScheduleOfRateTradeBillItemWithSelectedTendererRates(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId')) and
			$scheduleOfRateTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('tradeId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeItemIds      = json_decode($request->getParameter('tradeItemIds'), true);
		$billItemIds       = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceScheduleOfRateTradeItemWithSelectedTendererRatesGenerator($project, $scheduleOfRate, $scheduleOfRateTrade, $tradeItemIds, $billItemIds, $printingPageTitle, $descriptionFormat);

		$scheduleOfRatePages = $reportPrintGenerator->generatePages();
		$maxRows             = $reportPrintGenerator->getMaxRows();
		$currency            = $reportPrintGenerator->getCurrency();
		$withoutPrice        = false;
		$stylesheet          = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount = 1;

		foreach ( $scheduleOfRatePages as $tradeId => $elements )
		{
			foreach ( $elements as $billElementId => $pages )
			{
				for ( $i = 1; $i <= $pages['item_pages']->count(); $i ++ )
				{
					if ( $pages['item_pages'] instanceof SplFixedArray and $pages['item_pages']->offsetExists($i) )
					{
						$layout = $this->getPartial('printReport/pageLayout', array(
							'stylesheet'    => $stylesheet,
							'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
						));

						$billItemsLayoutParams = array(
							'itemPage'                   => $pages['item_pages']->offsetGet($i),
							'maxRows'                    => $maxRows + 2,
							'currency'                   => $currency,
							'headerDescription'          => $reportPrintGenerator->scheduleOfRate['name'],
							'pageCount'                  => $pageCount,
							'totalPage'                  => $reportPrintGenerator->totalPage,
							'tradeTotals'                => '',
							'printGrandTotal'            => false,
							'reportTitle'                => $printingPageTitle,
							'topLeftRow1'                => ( array_key_exists($billElementId, $reportPrintGenerator->billElementIdToDescription) ) ? $reportPrintGenerator->billElementIdToDescription[$billElementId] : '',
							'topLeftRow2'                => ( array_key_exists($tradeId, $reportPrintGenerator->tradeIdToDescription) ) ? $reportPrintGenerator->tradeIdToDescription[$tradeId] : '',
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

						$layout .= $this->getPartial('printReport/scheduleOfRateBillTradeReportItem', $billItemsLayoutParams);

						$pages['item_pages']->offsetUnset($i);

						$pdfGen->addPage($layout);

						$pageCount ++;
					}
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedVO(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$voIds             = json_decode($request->getParameter('selectedRows'), true);

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceVOSummaryReportGenerator($project, $voIds, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$totalPage = count($pages) - 1;

		$pageCount = 1;

		$generateSignaturePage = false;

		$voFooterSettings = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $page )
			{
				if ( count($page) == 0 )
				{
					continue;
				}

				$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;
				$isLastPage      = ( $pageCount == $pages->count() - 1 ) ? true : false;

				if ( $isLastPage )
				{
					if ( ( $maxRows + 2 ) - count($page) > 15 )
					{
						$isLastPage = true;
						$maxRows    = ( $maxRows + 2 ) - 18;
					}
					else
					{
						$isLastPage            = false;
						$generateSignaturePage = true;
					}
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'headerDescription'          => '',
					'voTotals'                   => $reportPrintGenerator->voTotals,
					'pageCount'                  => $pageCount,
					'totalPage'                  => $totalPage,
					'printGrandTotal'            => $printGrandTotal,
					'workdoneOnly'               => false,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $project->title,
					'topLeftRow2'                => '',
					'isLastPage'                 => $isLastPage,
					'left_text'                  => $voFooterSettings->left_text,
					'right_text'                 => $voFooterSettings->right_text,
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

				$layout .= $this->getPartial('printReport/voSummaryReport', $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}

			if ( $generateSignaturePage )
			{
				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'          => array(),
					'maxRows'           => ( $maxRows + 2 ) - 15,
					'headerDescription' => '',
					'pageCount'         => $pageCount,
					'totalPage'         => $totalPage,
					'printGrandTotal'   => false,
					'workdoneOnly'      => false,
					'reportTitle'       => $printingPageTitle,
					'topLeftRow1'       => $project->title,
					'topLeftRow2'       => '',
					'isLastPage'        => true,
					'left_text'         => $voFooterSettings->left_text,
					'right_text'        => $voFooterSettings->right_text,
					'descHeader'        => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'        => $reportPrintGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'        => $reportPrintGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'         => $reportPrintGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'         => $reportPrintGenerator->getTableHeaderAmtPrefix(),
					'indentItem'        => $reportPrintGenerator->getIndentItem()
				);

				$layout .= $this->getPartial('printReport/voSummaryBlankPage', $billItemsLayoutParams);

				$pdfGen->addPage($layout);
			}
		}

		return $pdfGen->send();
	}

	public function executePrintVOWithClaims(sfWebRequest $request)
	{
		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceVOWithClaimsReportGenerator($project, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$totalPage = count($pages) - 1;

		$pageCount = 1;

		$generateSignaturePage = false;

		$voFooterSettings = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $page )
			{
				$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;
				$isLastPage      = ( $pageCount == $pages->count() - 1 ) ? true : false;

				if ( count($page) == 0 )
				{
					continue;
				}

				if ( $isLastPage )
				{
					if ( ( $maxRows + 2 ) - count($page) > 15 )
					{
						$isLastPage = true;
						$maxRows    = ( $maxRows + 2 ) - 18;
					}
					else
					{
						$isLastPage            = false;
						$generateSignaturePage = true;
					}
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'headerDescription'          => '',
					'pageCount'                  => $pageCount,
					'totalPage'                  => $totalPage,
					'printGrandTotal'            => $printGrandTotal,
					'workdoneOnly'               => false,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $project->title,
					'topLeftRow2'                => '',
					'voTotals'                   => $reportPrintGenerator->voTotals,
					'isLastPage'                 => $isLastPage,
					'left_text'                  => $voFooterSettings->left_text,
					'right_text'                 => $voFooterSettings->right_text,
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

				$layout .= $this->getPartial('printReport/voSummaryReportWithClaims', $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}

			if ( $generateSignaturePage )
			{
				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'          => array(),
					'maxRows'           => ( $maxRows + 2 ) - 15,
					'headerDescription' => '',
					'pageCount'         => $pageCount,
					'totalPage'         => $totalPage,
					'printGrandTotal'   => false,
					'workdoneOnly'      => false,
					'reportTitle'       => $printingPageTitle,
					'topLeftRow1'       => $project->title,
					'topLeftRow2'       => '',
					'isLastPage'        => true,
					'left_text'         => $voFooterSettings->left_text,
					'right_text'        => $voFooterSettings->right_text,
					'descHeader'        => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'        => $reportPrintGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'        => $reportPrintGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'         => $reportPrintGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'         => $reportPrintGenerator->getTableHeaderAmtPrefix(),
					'indentItem'        => $reportPrintGenerator->getIndentItem()
				);

				$layout .= $this->getPartial('printReport/voSummaryReportWithClaimsBlankPage', $billItemsLayoutParams);

				$pdfGen->addPage($layout);
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedVOItemsDialog(sfWebRequest $request)
	{
		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceVOItemsReportGenerator($project, $itemIds, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount             = 1;
		$groupCount            = 1;
		$generateSignaturePage = false;

		$voFooterSettings = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);
		$variationTotals  = $reportPrintGenerator->variationTotals;

		foreach ( $pages as $key => $page )
		{
			$lastGroup = ( $groupCount == count($pages) ) ? true : false;

			for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
			{
				if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
				{
					$printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;
					$isLastPage      = ( ( $i + 1 ) == $page['item_pages']->count() && $lastGroup ) ? true : false;

					$ipItem = $page['item_pages']->offsetGet($i);

					if ( $isLastPage )
					{
						if ( ( $maxRows + 2 ) - count($ipItem) > 15 )
						{
							$isLastPage = true;
							$maxRows    = ( $maxRows + 2 ) - 18;
						}
						else
						{
							$isLastPage            = false;
							$generateSignaturePage = true;
						}
					}

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $ipItem,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => '',
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'printGrandTotal'            => $printGrandTotal,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => $project->title,
						'topLeftRow2'                => '',
						'variationTotal'             => ( $printGrandTotal && array_key_exists($key, $variationTotals) ) ? $variationTotals[$key] : 0,
						'isLastPage'                 => $isLastPage,
						'left_text'                  => $voFooterSettings->left_text,
						'right_text'                 => $voFooterSettings->right_text,
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

					$layout .= $this->getPartial('printReport/voItemReport', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}

			$groupCount ++;
		}

		if ( $generateSignaturePage )
		{
			$layout = $this->getPartial('printReport/pageLayout', array(
				'stylesheet'    => $stylesheet,
				'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
			));

			$billItemsLayoutParams = array(
				'itemPage'               => array(),
				'maxRows'                => ( $maxRows + 2 ) - 15,
				'headerDescription'      => '',
				'pageCount'              => $pageCount,
				'totalPage'              => 0,
				'printGrandTotal'        => false,
				'workdoneOnly'           => false,
				'reportTitle'            => $printingPageTitle,
				'topLeftRow1'            => $project->title,
				'topLeftRow2'            => '',
				'isLastPage'             => true,
				'left_text'              => $voFooterSettings->left_text,
				'right_text'             => $voFooterSettings->right_text,
				'printElementInGridOnce' => $reportPrintGenerator->getPrintElementInGridOnce(),
				'descHeader'             => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
				'unitHeader'             => $reportPrintGenerator->getTableHeaderUnitPrefix(),
				'rateHeader'             => $reportPrintGenerator->getTableHeaderRatePrefix(),
				'qtyHeader'              => $reportPrintGenerator->getTableHeaderQtyPrefix(),
				'amtHeader'              => $reportPrintGenerator->getTableHeaderAmtPrefix(),
				'indentItem'             => $reportPrintGenerator->getIndentItem()
			);

			$layout .= $this->getPartial('printReport/voItemReportBlankPage', $billItemsLayoutParams);

			$pdfGen->addPage($layout);
		}

		return $pdfGen->send();
	}

	public function executePrintVOItemsWithClaims(sfWebRequest $request)
	{
		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceVOItemsWithClaimReportGenerator($project, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		$pageCount             = 1;
		$groupCount            = 1;
		$generateSignaturePage = false;

		$voFooterSettings = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);
		$variationTotals  = $reportPrintGenerator->variationTotals;

		foreach ( $pages as $key => $page )
		{
			$lastGroup = ( $groupCount == count($pages) ) ? true : false;

			for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
			{
				if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
				{
					$printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;
					$isLastPage      = ( ( $i + 1 ) == $page['item_pages']->count() && $lastGroup ) ? true : false;

					if ( $isLastPage )
					{
						if ( ( $maxRows + 2 ) - count($page) > 15 )
						{
							$isLastPage = true;
							$maxRows    = ( $maxRows + 2 ) - 18;
						}
						else
						{
							$isLastPage            = false;
							$generateSignaturePage = true;
						}
					}

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $page['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => '',
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'printGrandTotal'            => $printGrandTotal,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => $project->title,
						'topLeftRow2'                => '',
						'variationTotal'             => ( $printGrandTotal && array_key_exists($key, $variationTotals) ) ? $variationTotals[$key] : 0,
						'isLastPage'                 => $isLastPage,
						'left_text'                  => $voFooterSettings->left_text,
						'right_text'                 => $voFooterSettings->right_text,
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

					$layout .= $this->getPartial('printReport/voItemReportWithClaim', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}

			$groupCount ++;
		}

		if ( $generateSignaturePage )
		{
			$layout = $this->getPartial('printReport/pageLayout', array(
				'stylesheet'    => $stylesheet,
				'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
			));

			$billItemsLayoutParams = array(
				'itemPage'          => array(),
				'maxRows'           => ( $maxRows + 2 ) - 15,
				'headerDescription' => '',
				'pageCount'         => $pageCount,
				'printGrandTotal'   => false,
				'workdoneOnly'      => false,
				'reportTitle'       => $printingPageTitle,
				'topLeftRow1'       => $project->title,
				'topLeftRow2'       => '',
				'isLastPage'        => true,
				'left_text'         => $voFooterSettings->left_text,
				'right_text'        => $voFooterSettings->right_text,
				'descHeader'        => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
				'unitHeader'        => $reportPrintGenerator->getTableHeaderUnitPrefix(),
				'rateHeader'        => $reportPrintGenerator->getTableHeaderRatePrefix(),
				'qtyHeader'         => $reportPrintGenerator->getTableHeaderQtyPrefix(),
				'amtHeader'         => $reportPrintGenerator->getTableHeaderAmtPrefix(),
				'indentItem'        => $reportPrintGenerator->getIndentItem()
			);

			$layout .= $this->getPartial('printReport/voItemReportBlankPage', $billItemsLayoutParams);

			$pdfGen->addPage($layout);
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedVOItemsWithBuildUpQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$itemIds    = json_decode($request->getParameter('selectedRows'), true);
		$stylesheet = $this->getBQStyling();
		$data       = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;
		$printedPages      = false;

		if ( !empty( $itemIds ) )
		{
			list(
				$data, $variationOrders,
				$buildUpItemsSummaries, $unitsDimensions, $buildUpItemsWithType
				) = VariationOrderItemTable::getVOItemsStructure($project, $itemIds);
		}

		if ( empty( $data ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = 'Nothing can be printed because there are no item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		list(
			$soqItemsData, $soqFormulatedColumns, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems
			) = ScheduleOfQuantityVariationOrderItemXrefTable::getSelectedItemsBuildUpQuantity($project, $data);

		$reportPrintGenerator = new sfBuildSpaceVariationOrderBuildUpItemGenerator($project, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$currency  = $reportPrintGenerator->getCurrency();
		$pageCount = 1;

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		// first level will be looping variation order, then bill associated with it
		foreach ( $variationOrders as $variationOrder )
		{
			$voItems = ( isset( $data[$variationOrder['id']] ) ) ? $data[$variationOrder['id']] : array();

			foreach ( $voItems as $voItem )
			{
				$dimensions = array();
				$voItemId   = $voItem['id'];

				// for each VO's item, get build up qty's columns definitions
				foreach ( $unitsDimensions as $unitsDimension )
				{
					if ( $voItem['uom_id'] != $unitsDimension['unit_of_measurement_id'] )
					{
						continue;
					}

					$dimensions[] = $unitsDimension['Dimension'];
				}

				// set available dimension
				$reportPrintGenerator->setAvailableTableHeaderDimensions($dimensions);

				// set max row based on dimension(s) that this current item is using
				$maxRows = $reportPrintGenerator->getMaxRows();

				// get voItem(s) build up if available
				foreach ( VariationOrderBuildUpQuantityItem::getBuildUpQtyTypes() as $variationOrderBuildUpQtyType )
				{
					// only allow existing type omission or addition to be available to be printed
					if ( $variationOrderBuildUpQtyType == VariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY AND !$voItem['has_omission'] )
					{
						continue;
					}

					if ( $variationOrderBuildUpQtyType == VariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY AND !$voItem['has_addition'] )
					{
						continue;
					}

					$columnPageCount = 1;
					$voQtyTypeText   = VariationOrderBuildUpQuantityItemTable::getTypeText($variationOrderBuildUpQtyType);
					$soqBuildUpItems = array();

					$voItemsBuildUpItems = isset( $buildUpItemsWithType[$variationOrderBuildUpQtyType][$voItemId] ) ? $buildUpItemsWithType[$variationOrderBuildUpQtyType][$voItemId] : array();

					$quantityPerUnit            = $voItem[strtolower($voQtyTypeText) . '_quantity'];
					$buildUpQuantitySummaryInfo = array();

					if ( isset( $soqItemsData[$variationOrderBuildUpQtyType][$voItemId] ) )
					{
						$soqBuildUpItems = $soqItemsData[$variationOrderBuildUpQtyType][$voItemId];

						unset( $soqItemsData[$variationOrderBuildUpQtyType][$voItemId] );
					}

					if ( isset( $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItemId] ) )
					{
						$buildUpQuantitySummaryInfo = $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItemId];

						unset( $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItemId] );
					}

					// don't generate page that has no manual build up and soq build up item(s)
					if ( count($voItemsBuildUpItems) == 0 AND count($soqBuildUpItems) == 0 )
					{
						unset( $voItemsBuildUpItems, $soqBuildUpItems, $buildUpQuantitySummaryInfo );

						continue;
					}

					// will inject to the generator to correctly generate printout
					// need to pass build up qty item(s) into generator to correctly generate the printout page
					$reportPrintGenerator->setBuildUpQuantityItems($voItemsBuildUpItems);

					$reportPrintGenerator->setSOQBuildUpQuantityItems($soqBuildUpItems);

					$reportPrintGenerator->getSOQFormulatedColumn($soqFormulatedColumns);

					$reportPrintGenerator->setManualBuildUpQuantityMeasurements($manualBuildUpQuantityItems);
					$reportPrintGenerator->setImportedBuildUpQuantityMeasurements($importedBuildUpQuantityItems);

					$pages        = $reportPrintGenerator->generatePages();
					$billItemInfo = $reportPrintGenerator->setupBillItemHeader($voItem, $voItem['bill_ref']);

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

						$lastPage = ( $columnPageCount == $pages->count() - 1 ) ? true : false;

						$printedPages = true;

						$layout = $this->getPartial('printReport/pageLayout', array(
							'stylesheet'    => $stylesheet,
							'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
						));

						$billItemsLayoutParams = array(
							'buildUpQuantitySummary'     => $buildUpQuantitySummaryInfo,
							'lastPage'                   => $lastPage,
							'totalQtyPerColumnSetting'   => $quantityPerUnit,
							'billItemInfos'              => $billItemInfo,
							'billItemUOM'                => $voItem['uom_symbol'],
							'itemPage'                   => $page,
							'maxRows'                    => $maxRows + 2,
							'currency'                   => $currency,
							'pageCount'                  => $pageCount,
							'elementTitle'               => $project->title,
							'printingPageTitle'          => $printingPageTitle,
							'billDescription'            => "{$variationOrder['description']} > {$voQtyTypeText}",
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

					unset( $pages, $billItemInfo );
				}

				unset( $voItem );
			}

			unset( $variationOrder, $voItems );
		}

		unset( $variationOrders, $soqItemsData, $soqFormulatedColumns, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems );

		if ( !$printedPages )
		{
			$this->message     = 'ERROR';
			$this->explanation = 'Sorry, there are no page(s) of omission or addition to be printed.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedElementSummaryPerUnitTypeByTenderer(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$elementIds        = json_decode($request->getParameter('selectedRows'), true);

		$priceFormat  = $request->getParameter('priceFormat');
		$printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildSpaceReportElementByTendererAndType($bill, $tendererIds, $elementIds, $printingPageTitle, $descriptionFormat);

		$pages        = $reportPrintGenerator->generatePages();
		$maxRows      = $reportPrintGenerator->getMaxRows();
		$currency     = $reportPrintGenerator->getCurrency();
		$withoutPrice = false;
		$pdfGen       = $this->createNewPDFGenerator($reportPrintGenerator);

		$template    = 'printReport/bqReportElementByTypeAndContractors';
		$project     = ProjectStructureTable::getInstance()->find($bill->root_id);
		$participate = ( $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED ) ? true : false;

		$totalPage = count($pages) - 1;
		$pageCountIncludeTypes = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $reportPrintGenerator->billColumnSettings as $billColumnSetting )
			{
				$pageCount = 1;

				foreach ( $pages as $key => $page )
				{
					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $this->getBQStyling(),
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$printGrandTotal = ( $pageCount === $pages->count() - 1 );

					if ( empty( $page ) )
					{
						continue;
					}

					$billItemsLayoutParams = array(
						'itemPage'                   => $page,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'printGrandTotal'            => $printGrandTotal,
						'participate'                => $participate,
						'billColumnSetting'          => $billColumnSetting,
						'tenderers'                  => $reportPrintGenerator->tenderers,
						'pageCount'                  => $pageCountIncludeTypes,
						'totalPage'                  => $totalPage,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => $billColumnSetting->name,
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
						'estimateOverAllTotal'       => $reportPrintGenerator->estimateOverAllTotal,
						'contractorOverAllTotal'     => $reportPrintGenerator->contractorOverAllTotal,
					);

					$layout .= $this->getPartial($template, $billItemsLayoutParams);

					unset( $page );

					$pdfGen->addPage($layout);

					$pageCount ++;

					$pageCountIncludeTypes++;
				}
			}
		}

		return $pdfGen->send();
	}

    public function executePrintItemRateAndTotalPerUnit(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
        );

        session_write_close();

        $elementIds = array();
        foreach(BillElementTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows')) as $elementId => $element)
        {
            $elementIds[] = $elementId;
        }

        // Printing page title, can be null as user may not want to print page title
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
        $itemIds           = json_decode($request->getParameter('selectedRows'), true);
        $priceFormat       = $request->getParameter('priceFormat');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        // only applicable for all Tenderers
        $sortingType = $request->getParameter('sortingType');

        $reportPrintGenerator = new sfBuildspaceReportItemRateAndTotalPerUnitPageGenerator($bill, $tendererIds, $elementIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

        list(
            $elements, $elementsWithBillItems, $formulatedColumns, $quantityPerUnitByColumns,
            $billItemTypeReferences, $billItemTypeRefFormulatedColumns
            ) = BillItemTable::getPrintingPreviewDataStructureForBillItemList($request->getParameter('selectedRows'), $bill);

        $reportPrintGenerator->setParameters($elements, $elementsWithBillItems, $formulatedColumns, $quantityPerUnitByColumns, $billItemTypeReferences, $billItemTypeRefFormulatedColumns);
        $pages        = $reportPrintGenerator->generatePages();
        $maxRows      = $reportPrintGenerator->getMaxRows();
        $currency     = $reportPrintGenerator->getCurrency();
        $withoutPrice = false;
        $pdfGen       = $this->createNewPDFGenerator($reportPrintGenerator);

        $template              = 'printReport/bqReportItemRateAndTotalPerUnit';
        $pageCountIncludeTypes = 1;
        $pageCount             = 0;

        foreach($reportPrintGenerator->billColumnSettings as $billColumnSetting)
        {
            foreach($pages as $elementId => $element)
            {
                $pageCountPerElement = 0;

                foreach($element['item_pages'] as $i => $itemPage)
                {
                    if( ! ( $element['item_pages'] instanceof SplFixedArray ) || empty( $itemPage ) )
                    {
                        continue;
                    }

                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $this->getBQStyling(),
                        'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
                    ));

                    $pageCount++;
                    $pageCountPerElement++;

                    $printGrandTotal = ( $pageCountPerElement === $element['item_pages']->count() - 1 );

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $itemPage,
                        'elementId'                  => $elementId,
                        'maxRows'                    => $maxRows,
                        'currency'                   => $currency,
                        'printGrandTotal'            => $printGrandTotal,
                        'billColumnSetting'          => $billColumnSetting,
                        'tenderers'                  => $reportPrintGenerator->tenderers,
                        'pageCount'                  => $pageCountIncludeTypes,
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => $billColumnSetting->name,
                        'topLeftRow2'                => $bill->title,
                        'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
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
                        'estimateOverAllTotal'       => $reportPrintGenerator->estimateElementTotals,
                        'contractorOverAllTotal'     => $reportPrintGenerator->contractorElementTotals,
                        'contractorRates'            => $reportPrintGenerator->contractorRates,
                    );

                    $layout .= $this->getPartial($template, $billItemsLayoutParams);

                    $pdfGen->addPage($layout);

                    $pageCountIncludeTypes++;
                }
            }
        }

        return $pdfGen->send();
    }

}