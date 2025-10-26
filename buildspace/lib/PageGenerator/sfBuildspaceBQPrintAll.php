<?php

class sfBuildSpaceBQPrintAll {

    public $pdfGenerator = null;

    public function __construct($request, ProjectStructure $projectStructure, $elements = null)
    {
        sfProjectConfiguration::getActive()->loadHelpers('Partial');

        $this->stylesheet          = file_get_contents(sfConfig::get('sf_web_dir').'/css/printBQ.css');
        $this->projectStructure    = $projectStructure;
        $this->elements            = $elements;
        $this->billColumnSettings  = $this->projectStructure->getBillColumnSettings()->toArray();
        $this->numberOfBillColumns = $projectStructure->getBillColumnSettings()->count();

        $this->printGrandTotalQty  = $projectStructure->BillLayoutSetting->print_grand_total_quantity;

        $this->orientation         = ($this->numberOfBillColumns > 1 and !$this->printGrandTotalQty) ? sfBuildspaceBQPageGenerator::ORIENTATION_LANDSCAPE : sfBuildspaceBQPageGenerator::ORIENTATION_PORTRAIT;

        $this->request             = $request;

        $this->selectedRevision    = ProjectRevisionTable::getCurrentSelectedProjectRevisionFromBillId($projectStructure->root_id);
        $this->currentRevision     = ProjectRevisionTable::getLatestProjectRevisionFromBillId($projectStructure->root_id);
    }

    public function getOrientation()
    {
        return $this->orientation;
    }

    public function setPdfGenerator(WkHtmlToPdf $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    public function generateFullBQPrintoutPages($sendToBrowser = true)
    {
        self::generateSummaryPages($sendToBrowser);
        self::generateBillItemAndCollectionPages($sendToBrowser);
    }

    public function generateSummaryPages($sendToBrowser)
    {
        $bqPageGenerator = new sfBuildspaceBQByRevisionPageGenerator($this->projectStructure);

        try
        {
            $pages = $bqPageGenerator->generatePages();
        }
        catch(PageGeneratorException $e)
        {
            throw new PageGeneratorException($e->getMessage(), [
                'data'            => $e->getData(),
                'bqPageGenerator' => $bqPageGenerator
            ]);
        }

        $maxRows           = $bqPageGenerator->getSummaryMaxRows() - 16;
        $summaryPages      = $pages['summary_pages'];
        $summaryPageLayout = (count($this->billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty) ? 'multiTypeSummaryPage' : 'singleTypeSummaryPage';
        $currency          = $bqPageGenerator->getCurrency();
        $withoutPrice      = $bqPageGenerator->getPrintNoPrice();
        $printFullDecimal  = $bqPageGenerator->getPrintFullDecimal();

        if ( $this->request->getParameter('currentModule') AND $this->request->getParameter('currentModule') == 'tendering' )
        {
            $withoutPrice = ($this->request->getParameter('withPrice')) ? false : true;
        }

        if ( $sendToBrowser )
        {
            foreach($summaryPages as $pageNo => $summaryPage)
            {
                $isLastPage = $pageNo == count($summaryPages) ? true : false;

                $layout = get_partial('printBQ/pageLayout', array(
                        'stylesheet' => $this->stylesheet,
                        'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                    )
                );

                $layout .= get_partial('printBQ/'.$summaryPageLayout, array(
                    'summaryPage'                => $summaryPage,
                    'billColumnSettings'         => $this->billColumnSettings,
                    'currency'                   => $currency,
                    'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                    'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                    'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                    'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                    'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                    'summaryHeaderDescription'   => $bqPageGenerator->getSummaryHeaderDescription(),
                    'totalPerUnitPrefix'         => $bqPageGenerator->getTotalPerUnitPrefix(),
                    'totalPerTypePrefix'         => $bqPageGenerator->getTotalPerTypePrefix(),
                    'totalUnitPrefix'            => $bqPageGenerator->getTotalUnitPrefix(),
                    'tenderPrefix'               => $bqPageGenerator->getTenderPrefix(),
                    'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                    'summaryPageNoPrefix'        => $bqPageGenerator->getTableHeaderSummaryPageNoPrefix(),
                    'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                    'maxRows'                    => $maxRows,
                    'pageNo'                     => $bqPageGenerator->getSummaryPageNumberingPrefix($pageNo),
                    'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                    'printNoPrice'               => $withoutPrice,
                    'printFullDecimal'           => $printFullDecimal,
                    'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                    'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                    'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                    'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                    'isLastPage'                 => $isLastPage,
                    'summaryInGridPrefix'        => $bqPageGenerator->getSummaryInGridPrefix(),
                    'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                    'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                    'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                    'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                ));

                $this->pdfGenerator->addPage($layout);

                unset($layout, $summaryPage);
            }

            unset($bqPageGenerator, $summaryPages);
        }
    }

    public function generateBillItemAndCollectionPages($sendToBrowser)
    {
        foreach ( $this->elements as $element )
        {
            $bqPageGenerator = new sfBuildspaceBQByRevisionPageGenerator($this->projectStructure, $element);
            
            try
            {
                $pages = $bqPageGenerator->generatePages();
            }
            catch(PageGeneratorException $e)
            {
                throw new PageGeneratorException($e->getMessage(), [
                    'data'            => $e->getData(),
                    'bqPageGenerator' => $bqPageGenerator
                ]);
            }

            $billItemsLayout      = (count($this->billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty) ? 'multiTypeBillItemsLayout' : 'singleTypeBillItemsLayout';
            $collectionPageLayout = (count($this->billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty) ? 'multiTypeCollectionPage' : 'singleTypeCollectionPage';
            $maxRows              = $bqPageGenerator->getMaxRows();
            $currency             = $bqPageGenerator->getCurrency();
            $withoutPrice         = $bqPageGenerator->getPrintNoPrice();
            $printFullDecimal     = $bqPageGenerator->getPrintFullDecimal();

            if ( $this->request->getParameter('currentModule') AND $this->request->getParameter('currentModule') == 'tendering' )
            {
                $withoutPrice = ($this->request->getParameter('withPrice')) ? false : true;
            }

            if ( $sendToBrowser )
            {
                foreach($pages as $key => $page)
                {
                    if($key == 'summary_pages')
                        continue;

                    for($i=1;$i<=$page['item_pages']->count(); $i++)
                    {
                        if($page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i))
                        {
                            $layout = get_partial('printBQ/pageLayout', array(
                                'stylesheet' => $this->stylesheet,
                                'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                                )
                            );

                            $layout .= get_partial('printBQ/'.$billItemsLayout, array(
                                'itemPage'                   => $page['item_pages']->offsetGet($i),
                                'billColumnSettings'         => $this->billColumnSettings,
                                'maxRows'                    => $maxRows,
                                'currency'                   => $currency,
                                'elementHeaderDescription'   => $page['description'],
                                'elementCount'               => $page['element_count'],
                                'pageCount'                  => $i,
                                'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                                'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                                'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                                'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                                'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                                'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                                'unitHeader'                 => $bqPageGenerator->getTableHeaderUnitPrefix(),
                                'rateHeader'                 => $bqPageGenerator->getTableHeaderRatePrefix(),
                                'qtyHeader'                  => $bqPageGenerator->getTableHeaderQtyPrefix(),
                                'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                                'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                                'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                                'printNoPrice'               => $withoutPrice,
                                'printFullDecimal'           => $printFullDecimal,
                                'toggleColumnArrangement'    => $bqPageGenerator->getToggleColumnArrangement(),
                                'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                                'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                                'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                                'rateCommaRemove'            => $bqPageGenerator->getRateCommaRemove(),
                                'qtyCommaRemove'             => $bqPageGenerator->getQtyCommaRemove(),
                                'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                                'printAmountOnly'            => $bqPageGenerator->getPrintAmountOnly(),
                                'printElementInGridOnce'     => $bqPageGenerator->getPrintElementInGridOnce(),
                                'indentItem'                 => $bqPageGenerator->getIndentItem(),
                                'printElementInGrid'         => $bqPageGenerator->getPrintElementInGrid(),
                                'pageNoPrefix'               => $bqPageGenerator->getPageNoPrefix(),
                                'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                                'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                                'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                                'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                            ));

                            $page['item_pages']->offsetUnset($i);

                            // Add page from URL
                            $this->pdfGenerator->addPage($layout);

                            unset($layout);
                        }
                    }

                    // get last collection's page page no.
                    end($page['collection_pages']);
                    $lastCollectionPageNo = key($page['collection_pages']);

                    foreach($page['collection_pages'] as $pageNo => $collectionPage)
                    {
                        $isLastPage = ($lastCollectionPageNo == $pageNo);

                        $layout = get_partial('printBQ/pageLayout', array(
                                'stylesheet' => $this->stylesheet,
                                'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                            )
                        );

                        $layout .= get_partial('printBQ/'.$collectionPageLayout, array(
                            'collectionPage'             => $collectionPage,
                            'billColumnSettings'         => $this->billColumnSettings,
                            'maxRows'                    => count($this->billColumnSettings) > 1 ? $maxRows-4 : $maxRows,//less 4 rows for collection page
                            'currency'                   => $currency,
                            'elementHeaderDescription'   => $page['description'],
                            'elementCount'               => $page['element_count'],
                            'pageCount'                  => $pageNo,
                            'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                            'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                            'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                            'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                            'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                            'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                            'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                            'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                            'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                            'printNoPrice'               => $withoutPrice,
                            'printFullDecimal'           => $printFullDecimal,
                            'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                            'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                            'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                            'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                            'printElementInGrid'         => $bqPageGenerator->getPrintElementInGrid(),
                            'isLastPage'                 => $isLastPage,
                            'pageNoPrefix'               => $bqPageGenerator->getPageNoPrefix(),
                            'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                            'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                            'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                            'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                        ));

                        // Add page from URL
                        $this->pdfGenerator->addPage($layout);

                        unset($layout, $collectionPage, $page['collection_pages'][$pageNo]);
                    }

                    unset($pages[$key]);
                }

                unset($element, $pages, $bqPageGenerator);
            }
        }
    }

}