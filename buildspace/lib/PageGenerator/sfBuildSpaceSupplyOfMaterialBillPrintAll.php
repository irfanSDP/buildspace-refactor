<?php

class sfBuildSpaceSupplyOfMaterialBillPrintAll {

    public $pdfGenerator;

    public function __construct($request, ProjectStructure $projectStructure, Array $elements = null)
    {
        sfProjectConfiguration::getActive()->loadHelpers('Partial');

        $this->request = $request;
        $this->stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
        $this->projectStructure = $projectStructure;
        $this->elements = $elements;
        $this->orientation = sfBuildspaceBQPageGenerator::ORIENTATION_PORTRAIT;
    }

    public function getOrientation()
    {
        return $this->orientation;
    }

    public function setPdfGenerator($pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    public function generateFullPrintoutPages($sendToBrowser = true)
    {
        self::generateBillItemAndCollectionPages($sendToBrowser);
    }

    /**
     * Generates and adds item and collection pages, i.e. creating the pdf document for the supply of material bill.
     *
     * @param $sendToBrowser
     */
    public function generateBillItemAndCollectionPages($sendToBrowser)
    {
        if( count($this->elements) > 0 )
        {
            $elementJustForPageGenerator = $this->elements[0];
        }
        else
        {
            $elementJustForPageGenerator = null;
        }

        $billPageNumbersAndPageTotal = $this->generateAndAddBillItemPages($sendToBrowser, $elementJustForPageGenerator);

        $this->generateAndAddCollectionPages($sendToBrowser, $elementJustForPageGenerator, $billPageNumbersAndPageTotal);
    }

    /**
     * Generates the all collection pages for the bill and adds them to the pdf document.
     *
     * @param $sendToBrowser
     * @param $elementJustForPageGenerator
     * @param $allElementsPageNumbersAndPageTotal
     */
    public function generateAndAddCollectionPages($sendToBrowser, $elementJustForPageGenerator, $allElementsPageNumbersAndPageTotal)
    {
        $element = null;
        if($elementJustForPageGenerator)
        {
            $element = Doctrine_Core::getTable('SupplyOfMaterialElement')->find($elementJustForPageGenerator['id']);
        }

        $bqPageGenerator = new sfBuildspaceSupplyOfMaterialBillPageGenerator($this->projectStructure, $element);

        $collectionPages = array();
        $pageNumberDescription = 'Page No. ';

        $bqPageGenerator->generateCollectionPages($allElementsPageNumbersAndPageTotal, $pageNumberDescription, 1, $collectionPages, 0, false);
        $pages = array();
        array_push($pages, array( 'collection_pages' => $collectionPages ));

        $collectionPageLayout = 'singleTypeCollectionPage';
        $maxRows = $bqPageGenerator->getMaxRows();
        $currency = $bqPageGenerator->getCurrency();

        $withoutPrice = $this->collectionPrintWithoutPrice($bqPageGenerator);

        $printFullDecimal = true;

        if( $this->request->getParameter('currentModule') AND $this->request->getParameter('currentModule') == 'tendering' )
        {
            $withoutPrice = ( $this->request->getParameter('withPrice') ) ? false : true;
        }

        if( $sendToBrowser )
        {
            foreach($pages as $pageKey => $page)
            {
                $this->addCollectionPages($page, $bqPageGenerator, $collectionPageLayout, $maxRows, $currency, $withoutPrice, $printFullDecimal);

                unset( $element, $pages, $bqPageGenerator );
            }
        }
    }

    /**
     * Adds the generated collection pages to the pdf document.
     *
     * @param $page
     * @param $bqPageGenerator
     * @param $collectionPageLayout
     * @param $maxRows
     * @param $currency
     * @param $withoutPrice
     * @param $printFullDecimal
     */
    public function addCollectionPages($page, $bqPageGenerator, $collectionPageLayout, $maxRows, $currency, $withoutPrice, $printFullDecimal)
    {
        // get last collection's page page no.
        end($page['collection_pages']);
        $lastCollectionPageNo = key($page['collection_pages']);

        foreach($page['collection_pages'] as $pageNo => $collectionPage)
        {
            $isLastPage = ( $lastCollectionPageNo == $pageNo );

            $layout = get_partial('supplyOfMaterialBill/pageLayout', array(
                'stylesheet'    => $this->stylesheet,
                'layoutStyling' => $bqPageGenerator->getLayoutStyling()
            ));

            $layout .= get_partial('supplyOfMaterialBill/' . $collectionPageLayout, array(
                'descHeader'                 => 'Description',
                'amtHeader'                  => 'Amount',
                'collectionPage'             => $collectionPage,
                'maxRows'                    => $maxRows - 15, //15 less rows for collection page
                'currency'                   => $currency,
                'pageCount'                  => $pageNo,
                'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                'printNoPrice'               => $withoutPrice,
                'printFullDecimal'           => $printFullDecimal,
                'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                'printElementInGrid'         => $bqPageGenerator->getPrintElementInGrid(),
                'isLastPage'                 => $isLastPage,
                'pageNoPrefix'               => $bqPageGenerator->getCollectionPageNoPrefix(),
                'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                'printElementTitle'          => false,
                'printGrandTotalQty'         => true,
            ));

            // Add page from URL
            $this->pdfGenerator->addPage($layout);

            unset( $layout, $collectionPage, $page['collection_pages'][ $pageNo ] );
        }

    }

    /**
     * Adds an item page to the pdf document.
     *
     * @param $page
     * @param $i
     * @param $bqPageGenerator
     * @param $billItemsLayout
     * @param $maxRows
     * @param $currency
     * @param $withoutPrice
     * @param $printFullDecimal
     *
     * @return string
     */
    public function addItemPage($page, $i, $bqPageGenerator, $billItemsLayout, $maxRows, $currency, $withoutPrice, $printFullDecimal)
    {
        if( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
        {
            $layout = get_partial('supplyOfMaterialBill/pageLayout', array(
                'stylesheet'    => $this->stylesheet,
                'layoutStyling' => $bqPageGenerator->getLayoutStyling()
            ));

            $layout .= get_partial('supplyOfMaterialBill/' . $billItemsLayout, array(
                'projectTitleRows'           => $bqPageGenerator->getProjectTitleRows(),
                'itemPage'                   => $page['item_pages']->offsetGet($i),
                'maxRows'                    => $maxRows,
                'currency'                   => $currency,
                'elementHeaderDescription'   => $page['description'],
                'elementCount'               => $page['element_count'],
                'pageCount'                  => $i,
                'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                'topRightRow1'               => $this->projectStructure->title,
                'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                'printNoPrice'               => $withoutPrice,
                'printFullDecimal'           => $printFullDecimal,
                'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                'rateCommaRemove'            => $bqPageGenerator->getRateCommaRemove(),
                'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                'printElementInGridOnce'     => $bqPageGenerator->getPrintElementInGridOnce(),
                'printElementInGrid'         => $bqPageGenerator->getPrintElementInGrid(),
                'pageNoPrefix'               => $bqPageGenerator->getPageNoPrefix(),
                'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
            ));

            $page['item_pages']->offsetUnset($i);

            // Add page from URL
            $this->pdfGenerator->addPage($layout);

            unset( $layout );

        }
    }

    /**
     * Adds all generated item pages to the pdf document.
     *
     * @param $page
     * @param $bqPageGenerator
     * @param $billItemsLayout
     * @param $maxRows
     * @param $currency
     * @param $withoutPrice
     * @param $printFullDecimal
     */
    public function addItemPages($page, $bqPageGenerator, $billItemsLayout, $maxRows, $currency, $withoutPrice, $printFullDecimal)
    {
        for($i = 1; $i <= $page['item_pages']->count(); $i++)
        {
            $this->addItemPage($page, $i, $bqPageGenerator, $billItemsLayout, $maxRows, $currency, $withoutPrice, $printFullDecimal);
        }
    }

    /**
     * Generates and adds Item Pages for all elements in the bill.
     *
     * @param $sendToBrowser
     * @param $elementJustForPageGenerator
     */
    public function generateAndAddBillItemPages($sendToBrowser, $elementJustForPageGenerator)
    {
        $element = null;
        if($elementJustForPageGenerator)
        {
            $element = Doctrine_Core::getTable('SupplyOfMaterialElement')->find($elementJustForPageGenerator['id']);
        }

        $bqPageGenerator = new sfBuildspaceSupplyOfMaterialBillPageGenerator($this->projectStructure, $element);

        try
        {
            $pagesResults = $bqPageGenerator->generatePages();
        }
        catch(PageGeneratorException $e)
        {
            throw new PageGeneratorException($e->getMessage(), [
                'data'            => $e->getData(),
                'bqPageGenerator' => $bqPageGenerator
            ]);
        } 

        $pages = $pagesResults['pages'];
        $billPageNumbersAndPageTotal = $pagesResults['billPageNumbersAndPageAmounts'];

        $billItemsLayout = 'singleTypeBillItemsLayout';
        $maxRows = $bqPageGenerator->getMaxRows();
        $currency = $bqPageGenerator->getCurrency();
        $withoutPrice = false;
        $printFullDecimal = true;

        if( $this->request->getParameter('currentModule') AND $this->request->getParameter('currentModule') == 'tendering' )
        {
            $withoutPrice = ( $this->request->getParameter('withPrice') ) ? false : true;
        }

        if( $sendToBrowser )
        {
            foreach($pages as $key => $page)
            {
                $this->addItemPages($page, $bqPageGenerator, $billItemsLayout, $maxRows, $currency, $withoutPrice, $printFullDecimal);

                unset( $pages[ $key ] );
            }

            unset( $elementJustForPageGenerator, $pages, $bqPageGenerator );
        }

        return $billPageNumbersAndPageTotal;
    }

    /**
     * Returns false (i.e. collection page printout WILL have price included) if the project status is participated.
     *
     * @param $bqPageGenerator
     *
     * @return bool
     */
    public function collectionPrintWithoutPrice($bqPageGenerator)
    {
        $withoutPrice = true;

        if( $bqPageGenerator->getProjectMainInformationStatus() == ProjectMainInformation::STATUS_IMPORT )
        {
            $withoutPrice = false;
        }

        return $withoutPrice;
    }
}