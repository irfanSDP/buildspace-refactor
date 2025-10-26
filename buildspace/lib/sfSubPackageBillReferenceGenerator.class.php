<?php

class sfSubPackageBillReferenceGenerator extends sfBillReferenceGenerator
{
    function __construct(ProjectStructure $bill)
    {
        $project = $bill->getRoot();

        $this->bill = $bill;

        parent::__construct($project);
    }

    public function process()
    {
        $bill = $this->bill;
        
        $this->printSettings            = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, true);
        $this->elementsOrder            = $this->getElementOrder();
        $this->billReferenceToUpdate    = [];
        $this->itemIdsToRemoveReference = [];
        $this->billColumnSettings       = $this->getBillColumnSettings();

        $numberOfBillColumns            = $bill->getBillColumnSettings()->count();

        $this->fontType                 = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->fontSize                 = $this->printSettings['layoutSetting']['fontSize'];

        $this->headSettings             = $this->printSettings['headSettings'];
        $this->printGrandTotalQty       = $this->printSettings['layoutSetting']['printGrandTotalQty'];
        $this->currency                 = $this->project->MainInformation->Currency;
        $this->numberOfBillColumns      = $numberOfBillColumns;
        $this->orientation              = ($numberOfBillColumns > 1 and !$this->printGrandTotalQty) ? self::ORIENTATION_LANDSCAPE : self::ORIENTATION_PORTRAIT;
        $this->pageFormat               = $this->setPageFormat(self::PAGE_FORMAT_A4);

        self::setMaxCharactersPerLine($this->printSettings['layoutSetting']['printAmountOnly']);

        $this->billStructure = $this->queryBillStructure();

        $this->processBill($bill);
    }

    protected function processBill(ProjectStructure $bill)
    {
        $elementCount          = $this->elementsOrder;
        $billStructure         = $this->billStructure;
        $billColumnSettings    = $bill->BillColumnSettings;

        $pageNumberDescription = trim("Page No. " .self::getPageNoPrefix())." ";
        $ratesAfterMarkup      = $this->getRatesAfterMarkup();
        $lumpSumPercents       = $this->getLumpSumPercent();
        $itemQuantities        = $this->getItemQuantities();
        $itemIncludeStatus     = $this->getItemIncludeStatus();

        if($this->printGrandTotalQty)
        {
            $totalAmount[0] = 0;
            $totalPerUnit[0] = 0;
        }
        else
        {
            foreach($billColumnSettings as $billColumnSetting)
            {
                $totalAmount[$billColumnSetting['id']] = 0;
                $totalPerUnit[$billColumnSetting['id']] = 0;
            }
        }

        foreach($billStructure as $element)
        {
            $this->lastPageCount = 1;
            $itemPages           = [];
            $collectionPages     = [];
            $elemCount           = $elementCount[$element['id']]['order'];

            $elementInfo = [
                'id'            => $element['id'],
                'description'   => $element['description'],
                'element_count' => $elemCount
            ];

            $this->generateBillItemPages($element['items'], $billColumnSettings->toArray(), $elementInfo, 1, [], $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities);

            $this->generateCollectionPages($elementInfo, $billColumnSettings->toArray(), $itemPages, $pageNumberDescription, count($itemPages)+1, count($itemPages), $collectionPages, $totalAmount);

            foreach($collectionPages as $pageCount => $collectionPage)
            {
                // will save the page no for collection page for current element
                $this->collectionPageNo[$element['id']][] = [
                    'pageCount' => $pageCount,
                ];

                // just get the first collection page no only
                break;
            }
        }
    }
}