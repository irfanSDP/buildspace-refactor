<?php

class sfBillAddendumReferenceGenerator extends sfBuildspaceBQAddendumGenerator
{
    protected $newAffectedPageNos;
    protected $addendumCollectionFromPreviousPage;

    public $fileInfo;
    public $elementMarkupEnabled          = false;
    public $itemMarkupEnabled             = false;
    public $collectionPageNo              = [];
    public $addendumCollectionPageNo      = [];
    public $pagesContainers               = [];

    protected $addendumAddonPages         = 0;
    protected $currentAddedCollectionPage = 0;

    function __construct(ProjectStructure $project, ProjectRevision $lastLatestRevision, $savePath = null, $filename = null )
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->project         = $project;
        $this->projectRevision = $lastLatestRevision;

        $this->saveOriginalBillInformation = true;
    }

    public function process()
    {
         //Get List of Bill
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

         foreach ($bills as $bill)
        {
            $this->bill                     = $bill;
            $this->printSettings            = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, TRUE);
            $this->printGrandTotalQty       = $this->printSettings['layoutSetting']['printGrandTotalQty'];
            $this->elementsOrder            = $this->getElementOrder();
            $this->billReferenceToUpdate    = [];
            $this->itemIdsToRemoveReference = [];
            $this->billColumnSettings       = $this->getBillColumnSettings();

            $numberOfBillColumns            = $this->getBillColumnSettingCount();

            $this->fontType                 = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
            $this->fontSize                 = $this->printSettings['layoutSetting']['fontSize'];

            $this->headSettings             = $this->printSettings['headSettings'];
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
        $elementCount           = $this->elementsOrder;
        $billStructure          = $this->billStructure;
        $billColumnSettings     = $bill->BillColumnSettings;

        $pageNumberDescription  = trim("Page No. " .self::getPageNoPrefix())." ";
        $ratesAfterMarkup       = $this->getRatesAfterMarkup();
        $lumpSumPercents        = $this->getLumpSumPercent();
        $itemQuantities         = $this->getItemQuantities();
        $itemIncludeStatus      = $this->getItemIncludeStatus();
        $this->collectionPageNo = $this->getElementCollectionPageNo();
        $this->oldPageNo        = $this->getOldPageNo();

        if ( $this->printGrandTotalQty )
        {
            $totalAmount[0]  = 0;
            $totalPerUnit[0] = 0;
        }
        else
        {
            foreach ( $billColumnSettings as $billColumnSetting )
            {
                $totalAmount[$billColumnSetting['id']]  = 0;
                $totalPerUnit[$billColumnSetting['id']] = 0;
            }
        }

        foreach($billStructure as $key => $element)
        {
            $itemPages       = [];
            $collectionPages = [];
            $elemCount       = $elementCount[$element['id']]['order'];

            $elementInfo = [
                'id'            => $element['id'],
                'description'   => $element['description'],
                'element_count' => $elemCount
            ];

            $this->addendumCollectionPageCount = $this->collectionPageNo[$element['id']].sfBuildspaceBQAddendumGenerator::addendumMarker;

            foreach ( $element['pages'] as $pageNo => $pageContent )
            {
                $this->addendumPageCount  = $pageNo.sfBuildspaceBQAddendumGenerator::addendumMarker;
                $this->addendumAddonPages = 0;

                $this->generateBillItemPages($pageContent['items'], $billColumnSettings->toArray(), $elementInfo, 0, [], $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities);
            }

            $this->currentAddedCollectionPage = 0;

            $this->generateCollectionPages($elementInfo, $billColumnSettings->toArray(), $itemPages, $pageNumberDescription, 0, count($itemPages), $collectionPages, $totalAmount);

            foreach ( $collectionPages as $pageCount => $collectionPage )
            {
                // will save the page no for collection page for current element
                $this->addendumCollectionPageNo[$element['id']][] = array(
                    'pageCount'    => $pageCount,
                );

                // get the first collection page no's information only
                break;
            }

            unset($billStructure[$key], $element);
        }

        $this->updateBillReferences();
    }

    protected function getElementCollectionPageNo()
    {
        $data           = [];
        $billElementIds = [];
        $pdo            = $this->pdo;
        $billElements   = $this->elementsOrder;

        foreach ( $billElements as $elementId => $billElement )
        {
            array_push($billElementIds, $elementId);
        }

        if (count($billElementIds) > 0)
        {
            $stmt = $pdo->prepare('SELECT b.id, b.element_id, b.page_no FROM '.BillCollectionPageTable::getInstance()->getTableName().' b
                WHERE b.element_id IN ('.implode(', ', $billElementIds).')');

            $stmt->execute();

            $collectionPages = $stmt->fetchAll();

            foreach ( $collectionPages as $collectionPage )
            {
                $data[$collectionPage['element_id']] = $collectionPage['page_no'];
            }
        }

        return $data;
    }

    protected function getOldPageNo()
    {
        $data           = [];
        $elementIds     = [];
        $billElementIds = [];
        $pdo            = $this->pdo;
        $billElements   = $this->elementsOrder;

        foreach ( $billElements as $elementId => $billElement )
        {
            array_push($billElementIds, $elementId);
        }

        if ( count($billElementIds) > 0 )
        {
            $stmt = $pdo->prepare('SELECT b.id, b.element_id, b.page_no, b.new_revision_id, b.total_amount FROM '.BillPageTable::getInstance()->getTableName().' b
            WHERE b.element_id IN ('.implode(', ', $billElementIds).')');

            $stmt->execute();

            $affectedPages = $stmt->fetchAll();

            foreach ( $affectedPages as $affectedPage )
            {
                // get non affected page and store them so that it can be use to generate record in
                // collection page
                if ( is_null ( $affectedPage['new_revision_id'] ) )
                {
                    $data[$affectedPage['element_id']][] = array( 'pageNo' => $affectedPage['page_no'], 'pageAmt' => $affectedPage['total_amount'] );

                    continue;
                }

                // if current affected page is not the current version, then don't process it
                // might be from previous version
                if ( $affectedPage['new_revision_id'] != $this->projectRevision->id )
                {
                }
            }
        }

        return $data;
    }

    protected function queryBillStructure()
    {
        $counter       = 0;
        $billStructure = [];

        // get affected element and page no
        $elements = DoctrineQuery::create()
            ->select('e.id, e.description, bp.id, bp.page_no, i.bill_item_id')
            ->from('BillElement e')
            ->leftJoin('e.BillPages bp')
            ->leftJoin('bp.Items i')
            ->where('e.project_structure_id = ?', $this->bill->id)
            ->andWhere('bp.new_revision_id = ?', $this->projectRevision->id)
            ->addOrderBy('e.priority, bp.page_no ASC')
            ->execute();

        foreach($elements as $element)
        {
            $billStructure[$counter] = [
                'id'          => $element['id'],
                'description' => $element['description'],
                'items'       => []
            ];

            // loop each addendum affected page's item
            foreach ( $element['BillPages'] as $billPage )
            {
                $pageItemIds = [];

                foreach ( $billPage['Items'] as $item )
                {
                    array_push($pageItemIds, $item['bill_item_id']);
                }

                $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.element_id, p.description, p.type,
                    COALESCE(p.grand_total_after_markup, 0) AS grand_total_after_markup, p.uom_id, p.level, p.priority,
                    p.lft, p.rgt, uom.symbol AS uom, p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char, p.uom_id
                    FROM ".BillItemTable::getInstance()->getTableName()." c
                    JOIN ".BillItemTable::getInstance()->getTableName()." p
                    ON c.lft BETWEEN p.lft AND p.rgt
                    LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                    WHERE c.root_id = p.root_id AND c.type <> ".BillItem::TYPE_HEADER."
                    AND c.id IN (".implode(',', $pageItemIds).") AND p.element_id = ".$element['id']."
                    AND c.deleted_at IS NULL AND (p.deleted_at_project_revision_id < ".$this->projectRevision->id." OR (p.deleted_at_project_revision_id != ".$this->projectRevision->id." OR p.deleted_at_project_revision_id IS NULL)) AND p.deleted_at IS NULL
                    ORDER BY p.priority, p.lft, p.level");

                $stmt->execute();
                $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $billStructure[$counter]['pages'][$billPage['page_no']]['items'] = $billItems;
            }

            unset($element);

            $counter++;
        }

        return $billStructure;
    }

    protected function updateBillReferences()
    {
        $pdo = $this->pdo;

        parent::updateBillReferences();

        $this->saveBillAddendumCollectionPageInformation($this->addendumCollectionPageNo);

        if ( count($this->pagesContainers) > 0 )
        {
            $this->saveBillAddendumPageInformation($this->pagesContainers);
        }
    }

    protected function saveBillAddendumCollectionPageInformation(Array $collectionPages)
    {
        $pdo       = $this->pdo;
        $statement = $pdo->prepare('INSERT INTO '.BillCollectionPageTable::getInstance()->getTableName().' (element_id, revision_id, page_no, created_at, updated_at)
                        VALUES
                        (:element_id, :revision_id, :page_no, :created_at, :updated_at) RETURNING id');

        foreach ( $collectionPages as $elementId => $collectionPageInfos )
        {
            foreach ( $collectionPageInfos as $collectionPageInfo )
            {
                $statement->bindValue(':element_id', $elementId);
                $statement->bindValue(':revision_id', $this->projectRevision->id);
                $statement->bindValue(':page_no', $collectionPageInfo['pageCount']);
                $statement->bindValue(':created_at', 'NOW()');
                $statement->bindValue(':updated_at', 'NOW()');
                $statement->execute();

                $result = $statement->fetch(PDO::FETCH_ASSOC);
            }
        }
    }

    protected function saveBillAddendumPageInformation(Array $pagesContainers)
    {
        $billPageItems = array();
        $pdo           = $this->pdo;
        $sql           = 'INSERT INTO '.BillPageTable::getInstance()->getTableName().' (element_id, page_no, revision_id, created_at, updated_at) VALUES (:element_id, :page_no, :revision_id, :created_at, :updated_at) RETURNING id';
        $statement     = $pdo->prepare('INSERT INTO '.BillPageTable::getInstance()->getTableName().' (element_id, page_no, revision_id, created_at, updated_at)
                            VALUES
                            (:element_id, :page_no, :revision_id, :created_at, :updated_at) RETURNING id');

        foreach ( $pagesContainers as $elementId => $pagesContainer )
        {
            foreach ( $pagesContainer as $pageNo => $items )
            {
                $statement->bindValue(':element_id', $elementId);
                $statement->bindValue(':page_no', $pageNo);
                $statement->bindValue(':revision_id', $this->projectRevision->id);
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
            $pdo = $this->pdo;
            $sqlArr = array();
            $sql = 'INSERT INTO '.BillPageItemTable::getInstance()->getTableName().' (bill_page_id, bill_item_id, created_at, updated_at) VALUES ';

            foreach ( $billPageItems as $item )
            {
                $sqlArr[] = '(' . $item['bill_page_id'] . ", " . $item['bill_item_id'] . ", NOW(), NOW())";
            }

            $sql .= implode(", ", $sqlArr);

            $statement = $pdo->prepare($sql);
            $result    = $statement->execute();
        }

        unset($pagesContainers);

        // clear the page's information in order to accomodate other bill's information
        $this->pagesContainers = array();
    }

}