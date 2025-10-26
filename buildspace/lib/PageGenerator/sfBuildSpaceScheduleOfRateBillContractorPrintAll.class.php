<?php

class sfBuildSpaceScheduleOfRateBillContractorPrintAll extends sfBuildspaceScheduleOfRateBillPrintAll {

    public $pdfGenerator = null;
    public $tenderCompany;

    public function __construct($request, ProjectStructure $projectStructure, $elements, TenderCompany $tenderCompany)
    {
        sfProjectConfiguration::getActive()->loadHelpers('Partial');

        $this->stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
        $this->projectStructure = $projectStructure;
        $this->tenderCompany = $tenderCompany;
        $this->elements = $elements;
        $this->orientation = sfBuildspaceScheduleOfRateBillPageGenerator::ORIENTATION_PORTRAIT;

        $this->request = $request;

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();
    }

    public function generateFullBQPrintoutPages($sendToBrowser = true)
    {
        self::generateBillItemAndCollectionPages($sendToBrowser);
    }

    /**
     * Generates and adds Item Pages for all elements in the bill.
     *
     * @param $sendToBrowser
     * @param $elementJustForPageGenerator
     */
    public function generateAndAddBillItemPages($sendToBrowser, $elementJustForPageGenerator)
    {
        $bqPageGenerator = new sfBuildspaceScheduleOfRateBillContractorPageGenerator($this->projectStructure, $elementJustForPageGenerator, $this->tenderCompany);

        $pages = $bqPageGenerator->generatePages();

        $billItemsLayout = 'singleTypeBillItemsLayout';
        $maxRows = $bqPageGenerator->getMaxRows();
        $currency = $bqPageGenerator->getCurrency();
        $withoutPrice = false;
        $printFullDecimal = true;

        if( $sendToBrowser )
        {
            foreach($pages as $key => $page)
            {
                $this->addItemPages($page, $bqPageGenerator, $billItemsLayout, $maxRows, $currency, $withoutPrice, $printFullDecimal);

                unset( $pages[ $key ] );
            }

            unset( $elementJustForPageGenerator, $pages, $bqPageGenerator );
        }
    }
}