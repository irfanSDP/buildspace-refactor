<?php

class sfBuildSpaceBQEditorAddendumPrintAll extends sfBuildSpaceBQAddendumPrintAll
{
    protected $editorProjectInfo;

    public function __construct(ProjectStructure $bill, EditorProjectInformation $editorProjectInfo, $elements = null)
    {
        parent::__construct( $bill, $editorProjectInfo->PrintRevision, $elements );

        $this->editorProjectInfo = $editorProjectInfo;
    }

    public function generateFullBQPrintoutPages($withPrice = false, $sendToBrowser = true)
    {
        self::generateAddendumSummaryAndBillItemAndCollectionPages($withPrice, $sendToBrowser);
    }

    public function generateAddendumSummaryAndBillItemAndCollectionPages($withPrice, $sendToBrowser)
    {
        if(count($this->elements->toArray()) == 0)
        {
            throw new Exception('Sorry, currently there are no changes detected for Bill :: '.$this->projectStructure->title);
        }
        
        $bqPageGenerator      = new sfBuildspaceBQEditorAddendumGenerator($this->projectStructure, $this->editorProjectInfo, $this->elements);
        $summaryPageLayout    = (count($this->billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty) ? 'multiTypeSummaryPage' : 'singleTypeSummaryPage';
        $pages                = $bqPageGenerator->generatePages();
        $billItemsLayout      = (count($this->billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty) ? 'multiTypeBillItemsLayout' : 'singleTypeBillItemsLayout';
        $collectionPageLayout = (count($this->billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty) ? 'multiTypeCollectionPage' : 'singleTypeCollectionPage';
        $currency             = $bqPageGenerator->getCurrency();
        $printFullDecimal     = $bqPageGenerator->getPrintFullDecimal();

        if ( $sendToBrowser )
        {
            foreach($pages['summary_pages'] as $pageNo => $summaryPage)
            {
                $maxRows = $bqPageGenerator->getSummaryMaxRows() - 16;

                $isLastPage = $pageNo == count($pages['summary_pages']) ? true : false;

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
                    'printNoPrice'               => $withPrice,
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

                unset($layout);
            }

            foreach($pages as $key => $page)
            {
                $maxRows = $bqPageGenerator->getMaxRows();

                if ($key == 'summary_pages') continue;

                foreach ( $page['item_pages'] as $pageNo => $pageInfo )
                {
                    $layout = get_partial('printBQ/pageLayout', array(
                        'stylesheet' => $this->stylesheet,
                        'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                        )
                    );

                    $layout .= get_partial('printBQ/'.$billItemsLayout, array(
                        'itemPage'                   => $pageInfo,
                        'billColumnSettings'         => $this->billColumnSettings,
                        'maxRows'                    => $maxRows,
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
                        'unitHeader'                 => $bqPageGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $bqPageGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $bqPageGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                        'printNoPrice'               => $withPrice,
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

                    // Add page from URL
                    $this->pdfGenerator->addPage($layout);

                    unset($layout);
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
                        'printNoPrice'               => $withPrice,
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
