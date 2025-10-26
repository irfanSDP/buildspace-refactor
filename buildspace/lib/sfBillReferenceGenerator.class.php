<?php

class sfBillReferenceGenerator extends sfBuildspaceBQPageGenerator
{
    public    $fileInfo;
    public    $elementMarkupEnabled        = false;
    public    $itemMarkupEnabled           = false;
    public    $collectionPageNo            = [];
    public    $lastPageCount               = 0;

    function __construct(ProjectStructure $project, $saveOriginalBillInformation=false)
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->project                     = $project;
        $this->projectRevisions            = $project->ProjectRevisions;
        $this->saveOriginalBillInformation = $saveOriginalBillInformation;

        $this->pagesContainers  = [];
        $this->collectionPageNo = [];
    }

    public function process()
    {
        $bills = DoctrineQuery::create()
            ->select()
            ->from('ProjectStructure s')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->leftJoin('s.BillMarkupSetting m')
            ->where('s.lft >= ? AND s.rgt <= ?', array($this->project->lft, $this->project->rgt))
            ->andWhere('s.root_id = ?', $this->project->id)
            ->andWhere('s.type = ?', ProjectStructure::TYPE_BILL)
            ->addOrderBy('s.lft ASC')
            ->execute();
        
        foreach($bills as $bill)
        {
            $this->bill                     = $bill;
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
        
        $this->updateBillReferences();
    }

    protected function updateBillReferences()
    {
        if($this->saveOriginalBillInformation)
        {
            $this->saveOriginalBillCollectionPageInformation($this->collectionPageNo);

            if(!empty($this->pagesContainers))
            {
                $this->saveOriginalBillPageInformation($this->pagesContainers);
            }
        }
        else
        {
            parent::updateBillReferences();
        }
    }

    protected function saveOriginalBillCollectionPageInformation(Array $collectionPages)
    {
        $revisionId = $this->projectRevisions[0]['id'];
        $statement  = $this->pdo->prepare("INSERT INTO ".BillCollectionPageTable::getInstance()->getTableName()." (element_id, revision_id, page_no, created_at, updated_at) VALUES (:element_id, :revision_id, :page_no, :created_at, :updated_at) RETURNING id");

        foreach ( $collectionPages as $elementId => $collectionPageInfos )
        {
            foreach ( $collectionPageInfos as $collectionPageInfo )
            {
                $statement->bindValue(':element_id', $elementId);
                $statement->bindValue(':revision_id', $revisionId);
                $statement->bindValue(':page_no', $collectionPageInfo['pageCount']);
                $statement->bindValue(':created_at', 'NOW()');
                $statement->bindValue(':updated_at', 'NOW()');
                
                $statement->execute();

                $result = $statement->fetch(PDO::FETCH_ASSOC);
            }
        }
    }

    protected function saveOriginalBillPageInformation(Array $pagesContainers)
    {
        $billPageItems = [];
        $statement     = $this->pdo->prepare("INSERT INTO ".BillPageTable::getInstance()->getTableName()." (element_id, page_no, revision_id, created_at, updated_at) VALUES (:element_id, :page_no, :revision_id, :created_at, :updated_at) RETURNING id");

        foreach ( $pagesContainers as $elementId => $pagesContainer )
        {
            foreach ( $pagesContainer as $pageNo => $items )
            {
                $statement->bindValue(':element_id', $elementId);
                $statement->bindValue(':page_no', $pageNo);
                $statement->bindValue(':revision_id', $this->projectRevisions[0]['id']);
                $statement->bindValue(':created_at', 'NOW()');
                $statement->bindValue(':updated_at', 'NOW()');
                $statement->execute();
                
                $result = $statement->fetch(PDO::FETCH_ASSOC);

                foreach ( $result as $id )
                {
                    foreach ( $items as $itemId )
                    {
                        array_push($billPageItems, array('bill_page_id' => $id, 'bill_item_id' => $itemId));
                    }
                }
            }
        }

        if ( count($billPageItems) > 0 )
        {
            $sql = "INSERT INTO ".BillPageItemTable::getInstance()->getTableName()." (bill_page_id, bill_item_id, created_at, updated_at) VALUES ";

            foreach ( $billPageItems as $item )
            {
                $sql_arr[] = '(' . $item['bill_page_id'] . ", " . $item['bill_item_id'] . ", NOW(), NOW())";
            }

            $sql .= implode(", ", $sql_arr);

            $statement = $this->pdo->prepare($sql);
            $result    = $statement->execute();
        }

        unset($pagesContainers, $this->pagesContainers);
    }
}
