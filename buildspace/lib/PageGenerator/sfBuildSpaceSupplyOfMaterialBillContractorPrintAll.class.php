<?php

class sfBuildSpaceSupplyOfMaterialBillContractorPrintAll extends sfBuildSpaceSupplyOfMaterialBillPrintAll{

    public $pdfGenerator = null;
    public $tenderCompany;

    public function __construct($request, ProjectStructure $projectStructure, $elements, TenderCompany $tenderCompany)
    {
        sfProjectConfiguration::getActive()->loadHelpers('Partial');

        $this->stylesheet          = file_get_contents(sfConfig::get('sf_web_dir').'/css/printBQ.css');
        $this->projectStructure    = $projectStructure;
        $this->tenderCompany       = $tenderCompany;
        $this->elements            = $elements;

        $this->orientation = sfBuildspaceSupplyOfMaterialBillPageGenerator::ORIENTATION_PORTRAIT;

        $this->request             = $request;

        $this->pdo                 = ProjectStructureTable::getInstance()->getConnection()->getDbh();
    }

    public function generateFullBQPrintoutPages($sendToBrowser = true)
    {
        self::generateBillItemAndCollectionPages($sendToBrowser);
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
        $bqPageGenerator = new sfBuildspaceSupplyOfMaterialBillPageGenerator($this->projectStructure,
            $elementJustForPageGenerator);

        $collectionPages = array();
        $pageNumberDescription = 'Page No. ';

        $bqPageGenerator->generateCollectionPages($allElementsPageNumbersAndPageTotal, $pageNumberDescription, 1, $collectionPages, 0, false);
        $pages = array();
        array_push($pages, array( 'collection_pages' => $collectionPages ));

        $collectionPageLayout = 'singleTypeCollectionPage';
        $maxRows = $bqPageGenerator->getMaxRows();
        $currency = $bqPageGenerator->getCurrency();

        $withoutPrice = false;

        $printFullDecimal = true;

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
     * Generates and adds Item Pages for all elements in the bill.
     *
     * @param $sendToBrowser
     * @param $elementJustForPageGenerator
     */
    public function generateAndAddBillItemPages($sendToBrowser, $elementJustForPageGenerator)
    {
        $bqPageGenerator = new sfBuildspaceSupplyOfMaterialBillContractorPageGenerator($this->projectStructure, $elementJustForPageGenerator, $this->tenderCompany);

        $pagesResults = $bqPageGenerator->generatePages();
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
}