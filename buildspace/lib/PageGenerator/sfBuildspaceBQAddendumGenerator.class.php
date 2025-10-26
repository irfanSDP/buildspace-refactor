<?php

class sfBuildspaceBQAddendumGenerator extends sfBillReferenceGenerator {

    protected $newAffectedPageNos;

    protected $addendumCollectionFromPreviousPage;

    protected $addendumAddonPages = 0;

    protected $currentAddedCollectionPage = 0;

    const addendumMarker = '*';

    public function __construct(ProjectStructure $bill, ProjectRevision $projectRevision, $elements)
    {
        $this->bill         = $bill;
        $this->billElements = $elements;
        $this->pdo          = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->projectRevision        = $projectRevision;
        $this->currentProjectRevision = ProjectRevisionTable::getLatestProjectRevisionFromBillId($bill->root_id);

        $this->billStructure          = $this->queryBillStructure();

        // Generate Element Order By Bill
        $this->elementsOrder       = $this->getElementOrder();

        // get bill's printout setting
        $printSettings             = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, true);
        $this->printSettings       = $printSettings;

        $this->printGrandTotalQty  = $printSettings['layoutSetting']['printGrandTotalQty'];

        $project                   = $bill->root_id == $bill->id ? $bill : ProjectStructureTable::getInstance()->find($bill->root_id);
        $numberOfBillColumns       = $bill->getBillColumnSettings()->count();

        $this->fontType            = self::setFontType($printSettings['layoutSetting']['fontTypeName']);
        $this->fontSize            = $printSettings['layoutSetting']['fontSize'];

        
        $this->headSettings        = $this->printSettings['headSettings'];
        $this->currency            = $project->MainInformation->Currency;
        $this->numberOfBillColumns = $numberOfBillColumns;
        $this->orientation         = ( $numberOfBillColumns > 1 and !$this->printGrandTotalQty ) ? self::ORIENTATION_LANDSCAPE : self::ORIENTATION_PORTRAIT;
        $this->pageFormat          = $this->setPageFormat(self::PAGE_FORMAT_A4);
        $this->collectionPageNo    = $this->getElementCollectionPageNo();
        $this->oldPageNo           = $this->getOldPageNoAndItemPricing();

        $this->setSummaryMaxCharactersPerLine($this->printSettings['layoutSetting']['printAmountOnly']);
        $this->setMaxCharactersPerLine($this->printSettings['layoutSetting']['printAmountOnly']);

        /*
         * We use SplFixedArray as row data structure. We can't use associative array with SPlFixedArray so we rely on indexes to set values.
         */
        $row    = new SplFixedArray(6);
        $row[0] = - 1;//id
        $row[1] = null;//row index
        $row[2] = null;//description
        $row[3] = 0;//level
        $row[4] = self::ROW_TYPE_BLANK;//type
        $row[5] = null;//unit

        $this->defaultRow = $row;
    }

    protected function updateBillReferences()
    {
        $pdo = $this->pdo;

        if ( count($this->billReferenceToUpdate) > 0 )
        {
            $charCaseStatement      = '';
            $elementNoCaseStatement = '';
            $pageNoCaseStatement    = '';
            $itemIds                = [];

            foreach ( $this->billReferenceToUpdate as $itemId => $value )
            {
                $charCaseStatement .= " WHEN " . $itemId . " THEN ('" . $value['char'] . "')";

                $elementNoCaseStatement .= " WHEN " . $itemId . " THEN (" . $value['elementNo'] . ")";

                $pageNoCaseStatement .= " WHEN " . $itemId . " THEN ('" . $value['pageCount'] . "')";

                array_push($itemIds, $itemId);
            }

            $stmt = $pdo->prepare("UPDATE " . BillItemTable::getInstance()->getTableName() . " SET
            bill_ref_char = (CASE id" . $charCaseStatement . " END),
            bill_ref_element_no = (CASE id" . $elementNoCaseStatement . " END),
            bill_ref_page_no = (CASE id" . $pageNoCaseStatement . " END) WHERE
            id IN (" . implode(',', $itemIds) . ")");

            $stmt->execute(array());
        }

        if ( count($this->itemIdsToRemoveReference) > 0 )
        {
            $stmt = $pdo->prepare("UPDATE " . BillItemTable::getInstance()->getTableName() . " SET
            bill_ref_char = NULL, bill_ref_element_no = NULL, bill_ref_page_no = NULL
            WHERE id IN (" . implode(',', $this->itemIdsToRemoveReference) . ")");

            $stmt->execute(array());
        }
    }

    protected function getOldPageNoAndItemPricing()
    {
        $data              = [];
        $billElementIds    = [];
        $overallTotal      = 0;
        $oldPageIds        = [];
        $bill              = $this->bill;
        $pdo               = $this->pdo;
        $billElements      = $this->elementsOrder;
        $billMarkupSetting = $this->bill->BillMarkupSetting;
        $previousElementId = null;
        $previousPageNum   = null;
        $isNew             = true;

        foreach ( $billElements as $elementId => $billElement )
        {
            array_push($billElementIds, $elementId);
        }

        if ( count($billElementIds) == 0 )
        {
            return $data;
        }

        $valueColumn = ( $billMarkupSetting->element_markup_enabled OR $billMarkupSetting->item_markup_enabled ) ? BillItemTypeReference::GRAND_TOTAL_AFTER_MARKUP : BillItemTypeReference::GRAND_TOTAL;

        $stmt = $pdo->prepare('SELECT b.id, b.page_no, b.element_id, b.new_revision_id FROM ' . BillPageTable::getInstance()->getTableName() . ' b
        WHERE b.element_id IN (' . implode(', ', $billElementIds) . ') AND b.revision_id < ' . $this->projectRevision->id . ' ORDER BY b.page_no ASC');

        $stmt->execute();

        $affectedPages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $affectedPages as $affectedPage )
        {
            // get non affected page and store them so that it can be use to generate record in collection page
            if ( is_null($affectedPage['new_revision_id']) OR $affectedPage['new_revision_id'] > $this->projectRevision->id )
            {
                $data[$affectedPage['element_id']][$affectedPage['id']]['pageNo'] = $affectedPage['page_no'];

                array_push($oldPageIds, $affectedPage['id']);
            }
        }

        if ( count($oldPageIds) > 0 )
        {
            if ( $this->printGrandTotalQty )
            {
                $stmt = $pdo->prepare('SELECT bi.element_id, bpi.bill_page_id, SUM(COALESCE(bi.grand_total_after_markup, 0)) as total
                    FROM ' . BillPageItemTable::getInstance()->getTableName() . ' bpi
                    LEFT JOIN ' . BillItemTable::getInstance()->getTableName() . ' bi ON bi.id = bpi.bill_item_id
                    WHERE bpi.bill_page_id IN (' . implode(', ', $oldPageIds) . ')
                    AND bi.deleted_at IS NULL
                    GROUP BY bi.element_id, bpi.bill_page_id
                    ORDER BY bi.element_id, bpi.bill_page_id ASC');

                $stmt->execute();

                $affectedPages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ( $affectedPages as $affectedPage )
                {
                    if ( $isNew )
                    {
                        $isNew = false;

                        $previousElementId = $affectedPage['element_id'];
                        $previousPageNum   = $affectedPage['bill_page_id'];
                    }

                    if ( $previousElementId != $affectedPage['element_id'] )
                    {
                        $previousElementId = $affectedPage['element_id'];
                        $previousPageNum   = $affectedPage['bill_page_id'];

                        $overallTotal = 0;
                    }

                    if ( $previousPageNum != $affectedPage['bill_page_id'] )
                    {
                        $previousPageNum = $affectedPage['bill_page_id'];

                        $overallTotal = 0;
                    }

                    $overallTotal += $affectedPage['total'];

                    $data[$affectedPage['element_id']][$affectedPage['bill_page_id']]['pageAmt'] = $overallTotal;
                }
            }
            else
            {
                foreach ( $bill->BillColumnSettings as $column )
                {
                    $stmt = $pdo->prepare('SELECT DISTINCT (bibr.id), bi.element_id, bpi.bill_page_id, bibr.bill_column_setting_id, COALESCE(bibr.' . $valueColumn . ', 0) as total
                    FROM ' . BillPageItemTable::getInstance()->getTableName() . ' bpi
                    LEFT JOIN ' . BillItemTable::getInstance()->getTableName() . ' bi ON bi.id = bpi.bill_item_id
                    LEFT JOIN ' . BillItemTypeReferenceTable::getInstance()->getTableName() . ' bibr ON bibr.bill_item_id = bi.id
                    WHERE bpi.bill_page_id IN (' . implode(', ', $oldPageIds) . ') AND bibr.bill_column_setting_id = ' . $column->id . ' AND bibr.include IS TRUE
                    AND bi.deleted_at IS NULL AND bibr.deleted_at IS NULL
                    ORDER BY bi.element_id, bpi.bill_page_id ASC');

                    $stmt->execute();

                    $affectedPages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ( $affectedPages as $affectedPage )
                    {
                        if ( $isNew )
                        {
                            $isNew = false;

                            $previousElementId = $affectedPage['element_id'];
                            $previousPageNum   = $affectedPage['bill_page_id'];
                        }

                        if ( $previousElementId != $affectedPage['element_id'] )
                        {
                            $previousElementId = $affectedPage['element_id'];
                            $previousPageNum   = $affectedPage['bill_page_id'];

                            unset( $overallTotal );
                        }

                        if ( $previousPageNum != $affectedPage['bill_page_id'] )
                        {
                            $previousPageNum = $affectedPage['bill_page_id'];

                            unset( $overallTotal );
                        }

                        if ( isset( $overallTotal ) )
                        {
                            $overallTotal += $affectedPage['total'] / $column->quantity;
                        }
                        else
                        {
                            $overallTotal = $affectedPage['total'] / $column->quantity;
                        }

                        $data[$affectedPage['element_id']][$affectedPage['bill_page_id']]['pageAmt'][$column->id] = $overallTotal;
                    }

                    unset( $overallTotal );
                }
            }
        }

        return $data;
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

        if ( count($billElementIds) > 0 )
        {
            $stmt = $pdo->prepare('SELECT b.id, b.element_id, b.page_no FROM ' . BillCollectionPageTable::getInstance()->getTableName() . ' b
            WHERE b.element_id IN (' . implode(', ', $billElementIds) . ') AND b.revision_id <= ' . $this->projectRevision->id);

            $stmt->execute();

            $collectionPages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $collectionPages as $collectionPage )
            {
                $data[$collectionPage['element_id']] = $collectionPage['page_no'];
            }
        }

        return $data;
    }

    // for each element, there is single or multi page that will occur changes if edited in addendum mode
    // so the query filtering will be based on pages
    protected function queryBillStructure()
    {
        $pdo           = $this->pdo;
        $bill          = $this->bill;
        $billElements  = $this->billElements;
        $billStructure = [];

        foreach ( $billElements as $billElement )
        {
            $elementSqlPart = $billElement instanceof BillElement ? "AND e.id = " . $billElement->id : null;

            $stmt = $pdo->prepare("SELECT e.id, e.description FROM " . BillElementTable::getInstance()->getTableName() . " e
            WHERE e.project_structure_id = " . $bill->id . " " . $elementSqlPart . " AND e.deleted_at IS NULL ORDER BY e.priority");
            $stmt->execute();


            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $elements as $element )
            {
                $result = [
                    'id'          => $element['id'],
                    'description' => $element['description'],
                    'items'       => []
                ];

                // loop each addendum affected page's item
                foreach ( $billElement['BillPages'] as $billPage )
                {
                    $pageItemIds = [];

                    foreach ( $billPage['Items'] as $item )
                    {
                        array_push($pageItemIds, $item['bill_item_id']);
                    }

                    $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.element_id, p.description, p.type,
                    COALESCE(p.grand_total_after_markup, 0) AS grand_total_after_markup, p.uom_id, p.level, p.priority,
                    p.lft, p.rgt, uom.symbol AS uom, p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char, p.uom_id
                    FROM " . BillItemTable::getInstance()->getTableName() . " c
                    JOIN " . BillItemTable::getInstance()->getTableName() . " p
                    ON c.lft BETWEEN p.lft AND p.rgt
                    LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                    WHERE c.root_id = p.root_id
                    AND c.id IN (" . implode(',', $pageItemIds) . ") AND p.element_id = " . $element['id'] . "
                    AND c.deleted_at IS NULL AND (p.deleted_at_project_revision_id < " . $this->projectRevision->id . "
                    OR (p.deleted_at_project_revision_id != " . $this->projectRevision->id . " OR p.deleted_at_project_revision_id IS NULL))
                    AND p.deleted_at IS NULL ORDER BY p.priority, p.lft, p.level");

                    $stmt->execute();

                    $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $result['pages'][$billPage['page_no']]['items'] = $billItems;

                    array_push($billStructure, $result);
                }

                unset( $element );
            }
        }

        return $billStructure;
    }

    protected function getItemQuantities()
    {
        $pdo  = $this->pdo;
        $bill = $this->bill;

        $billStructure   = $this->billStructure;
        $implodedItemIds = null;
        $result          = [];

        foreach ( $billStructure as $element )
        {
            foreach ( $element['pages'] as $pageContent )
            {
                if ( count($pageContent['items']) == 0 )
                {
                    continue;//we skip pageContent with empty items
                }

                $itemIds = Utilities::arrayValueRecursive('id', $pageContent['items']);

                if ( is_array($itemIds) )
                {
                    $implodedItemIds .= implode(',', $itemIds);
                    $implodedItemIds .= ",";
                }
            }
        }

        $implodedItemIds = rtrim($implodedItemIds, ",");

        if ( $this->printGrandTotalQty )
        {
            if ( !empty( $implodedItemIds ) )
            {
                $stmt = $this->pdo->prepare("SELECT i.id, i.grand_total_quantity AS value FROM " . BillItemTable::getInstance()->getTableName() . " i
                WHERE i.id IN (" . $implodedItemIds . ") AND i.grand_total_quantity <> 0 AND i.deleted_at IS NULL");

                $stmt->execute();

                $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

                $result = $quantities;

                unset( $quantities );
            }
        }
        else
        {
            foreach ( $bill->BillColumnSettings as $column )
            {
                $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                if ( !empty( $implodedItemIds ) )
                {
                    $stmt = $pdo->prepare("SELECT r.bill_item_id, COALESCE(fc.final_value, 0) AS value FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
                JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
                WHERE r.bill_item_id IN (" . $implodedItemIds . ") AND r.bill_column_setting_id = " . $column->id . "
                AND r.include IS TRUE AND fc.column_name = '" . $quantityFieldName . "' AND fc.final_value <> 0
                AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                    $stmt->execute();

                    $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

                    $result[$column->id] = $quantities;
                }
                else
                {
                    $result[$column->id] = 0;
                }
            }
        }

        return $result;
    }

    protected function getItemIncludeStatus()
    {
        $pdo  = $this->pdo;
        $bill = $this->bill;

        $billStructure   = $this->billStructure;
        $implodedItemIds = null;
        $result          = [];

        foreach ( $billStructure as $idx => $element )
        {
            foreach ( $element['pages'] as $pageNo => $pageContent )
            {
                if ( count($pageContent['items']) == 0 )
                {
                    continue;//we skip pageContent with empty items
                }

                $itemIds = Utilities::arrayValueRecursive('id', $pageContent['items']);

                if ( is_array($itemIds) )
                {
                    $implodedItemIds .= implode(',', $itemIds);
                    $implodedItemIds .= ",";
                }
            }
        }

        $implodedItemIds = rtrim($implodedItemIds, ",");

        foreach ( $bill->BillColumnSettings as $column )
        {
            if ( !empty( $implodedItemIds ) )
            {
                $stmt = $pdo->prepare("SELECT r.bill_item_id, r.include FROM " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r
                WHERE r.bill_item_id IN (" . $implodedItemIds . ") AND r.bill_column_setting_id = " . $column->id . "
                AND r.deleted_at IS NULL");
                $stmt->execute();

                $includeStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $result[$column->id] = $includeStatus;
            }
            else
            {
                $result[$column->id] = null;
            }
        }

        return $result;
    }

    protected function getLumpSumPercent()
    {
        $pdo  = $this->pdo;
        $bill = $this->bill;

        $stmt = $pdo->prepare("SELECT i.id, c.percentage FROM " . BillItemLumpSumPercentageTable::getInstance()->getTableName() . " c
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON (c.bill_item_id = i.id AND i.type = " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . ")
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            WHERE e.project_structure_id = " . $bill->id . "
            AND c.deleted_at IS NULL AND (i.deleted_at_project_revision_id < " . $this->projectRevision->id . " OR (i.deleted_at_project_revision_id != " . $this->projectRevision->id . " OR i.deleted_at_project_revision_id IS NULL)) AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $lumpSumpPercents = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        return $lumpSumpPercents;
    }

    protected function getRatesAfterMarkup()
    {
        $pdo                  = $this->pdo;
        $bill                 = $this->bill;
        $elementMarkupResults = [];

        $billMarkupSetting = $bill->BillMarkupSetting;

        $rateInfoColumns = [];

        if ( $bill->BillMarkupSetting->element_markup_enabled )
        {
            $stmt = $pdo->prepare("SELECT e.id, COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
                JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
                WHERE e.project_structure_id = " . $bill->id . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
                AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

            $stmt->execute();

            $elementMarkupResults = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
        }

        $stmt = $pdo->prepare("SELECT c.relation_id, i.element_id, c.column_name, COALESCE(c.final_value, 0) AS value FROM " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " c
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON c.relation_id = i.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            WHERE e.project_structure_id = " . $bill->id . " AND (c.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "' OR c.column_name = '" . BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "')
            AND c.deleted_at IS NULL AND (i.deleted_at_project_revision_id < " . $this->projectRevision->id . " OR (i.deleted_at_project_revision_id != " . $this->projectRevision->id . " OR i.deleted_at_project_revision_id IS NULL))
            AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $columns as $column )
        {
            $rateInfoColumns[$column['relation_id']]['markup_setting_info'] = array(
                'bill_markup_enabled'       => $billMarkupSetting->bill_markup_enabled,
                'bill_markup_percentage'    => $billMarkupSetting->bill_markup_percentage,
                'element_markup_enabled'    => $billMarkupSetting->element_markup_enabled,
                'element_markup_percentage' => array_key_exists($column['element_id'], $elementMarkupResults) ? $elementMarkupResults[$column['element_id']][0] : 0,
                'item_markup_enabled'       => $billMarkupSetting->item_markup_enabled,
                'rounding_type'             => $billMarkupSetting->rounding_type
            );

            $rateInfoColumns[$column['relation_id']][$column['column_name']] = $column['value'];
        }

        $result = [];

        foreach ( $rateInfoColumns as $itemId => $column )
        {
            $markupPercentage = array_key_exists(BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE, $column) ? $column[BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE] : 0;
            $rate             = array_key_exists(BillItem::FORMULATED_COLUMN_RATE, $column) ? $column[BillItem::FORMULATED_COLUMN_RATE] : 0;

            $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $markupPercentage, $column['markup_setting_info']);

            $result[$itemId] = number_format($rateAfterMarkup, 2, '.', '');
        }

        return $result;
    }

    public function generatePages()
    {
        $billStructure         = $this->billStructure;
        $billColumnSettings    = $this->bill->BillColumnSettings;
        $elementsOrder         = $this->elementsOrder;
        $billElements          = $this->billElements;
        $pages                 = [];
        $summaryPage           = [];
        $pageNumberDescription = 'Page No. ';
        $ratesAfterMarkup      = $this->getRatesAfterMarkup();
        $lumpSumPercents       = $this->getLumpSumPercent();
        $itemQuantities        = $this->getItemQuantities();
        $itemIncludeStatus     = $this->getItemIncludeStatus();

        foreach ( $elementsOrder as $elementId => $elementOrder )
        {
            $this->addendumCollectionPageCount = $this->collectionPageNo[$elementId];

            if ( $this->currentProjectRevision->version == $this->projectRevision->version )
            {
                $this->addendumCollectionPageCount = $this->addendumCollectionPageCount . self::addendumMarker;
            }

            $affectedElement = false;

            foreach ( $billElements as $billElement )
            {
                if ( $billElement['id'] == $elementId )
                {
                    $affectedElement = true;
                    break;
                }
            }

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

            // create collection page no in order to allow BQ Addendum's Summary page to generate a complete list
            // of summary
            if ( !$affectedElement )
            {
                // remove star from unaffected collection page no
                $this->addendumCollectionPageCount = $this->collectionPageNo[$elementId];

                $itemPages       = [];
                $collectionPages = [];

                $elementInfo = [
                    'id'            => $elementId,
                    'description'   => $elementOrder['description'],
                    'element_count' => $elementOrder['order'],
                ];

                $this->currentAddedCollectionPage = 0;

                $this->generateCollectionPages($elementInfo, $billColumnSettings->toArray(), $itemPages, $pageNumberDescription, 0, count($itemPages), $collectionPages, $totalAmount);

                $lastCollectionPage = end($collectionPages);

                $lastRow = [];

                foreach ( $lastCollectionPage as $collectionPageKey => $row )
                {
                    if ( is_integer($collectionPageKey) )
                    {
                        if ( is_null($row[0]) or strlen($row[0]) == 0 or $row[1] == self::ROW_TYPE_ELEMENT or $row[1] == self::ROW_TYPE_BLANK )
                        {
                            continue;
                        }

                        $lastRow = array(
                            'description' => str_replace($pageNumberDescription, "", $row[0]),
                            'amount'      => $lastCollectionPage['total_amount'],
                            'page_count'  => $elementOrder['order'] . '/' . $lastCollectionPage['page_count']
                        );
                    }
                    else
                    {
                    }
                }

                $summaryPage[$elementOrder['order']] = array(
                    'description'          => $elementOrder['description'],
                    'last_collection_page' => $lastRow,
                    'page_count'           => $elementOrder['order'] . '/' . $lastCollectionPage['page_count']
                );

                unset( $itemPages, $collectionPages, $lastCollectionPage );

                continue;
            }

            foreach ( $billStructure as $billStructureKey => $element )
            {
                // only use the current element's detail to generate BQ Addendum's pages
                // if available
                if ( $element['id'] != $elementId )
                {
                    continue;
                }

                $itemPages       = [];
                $collectionPages = [];
                $elemCount       = $elementsOrder[$element['id']]['order'];

                $elementInfo = [
                    'id'            => $element['id'],
                    'description'   => $element['description'],
                    'element_count' => $elemCount,
                ];

                foreach ( $element['pages'] as $pageNo => $pageContent )
                {
                    $this->addendumPageCount  = $pageNo . self::addendumMarker;
                    $this->addendumAddonPages = 0;

                    $this->generateBillItemPages($pageContent['items'], $billColumnSettings->toArray(), $elementInfo, 0, array(), $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities);
                }

                $this->currentAddedCollectionPage = 0;

                $this->generateCollectionPages($elementInfo, $billColumnSettings->toArray(), $itemPages, $pageNumberDescription, 0, count($itemPages), $collectionPages, $totalAmount);

                $lastCollectionPage = end($collectionPages);

                foreach ( $lastCollectionPage as $collectionPageKey => $row )
                {
                    if ( is_integer($collectionPageKey) )
                    {
                        if ( is_null($row[0]) or strlen($row[0]) == 0 or $row[1] == self::ROW_TYPE_ELEMENT or $row[1] == self::ROW_TYPE_BLANK )
                        {
                            continue;
                        }

                        $lastRow = array(
                            'description' => str_replace($pageNumberDescription, "", $row[0]),
                            'amount'      => $lastCollectionPage['total_amount'],
                            'page_count'  => $elemCount . '/' . $lastCollectionPage['page_count']
                        );
                    }
                    else
                    {
                    }
                }

                $summaryPage[$elemCount] = array(
                    'description'          => $element['description'],
                    'last_collection_page' => $lastRow,
                    'page_count'           => $elemCount . '/' . $lastCollectionPage['page_count']
                );

                $pages[$element['id']] = array(
                    'description'      => $element['description'],
                    'element_count'    => $elemCount,
                    'item_pages'       => $itemPages,
                    'collection_pages' => $collectionPages
                );

                unset( $elementInfo, $billStructure[$billStructureKey], $itemPages, $collectionPages, $lastCollectionPage );
            }
        }

        if ( $this->currentProjectRevision->version == $this->projectRevision->version )
        {
            // Update Bill References
            $this->updateBillReferences();
        }

        // sort the summary page element listing into it's own order
        ksort($summaryPage);

        $summaryPages = array();
        $this->generateSummaryPage($summaryPage, $billColumnSettings->toArray(), 1, $summaryPages, $totalPerUnit);

        $pages['summary_pages'] = $summaryPages;

        return $pages;
    }

    /*
     * Summary page indexes
     * 0 - description
     * 1 - type
     * 2 - total amount per unit for each elements
     * 3 - last collection page number
     *
     */
    public function generateSummaryPage($rows, $billColumnSettings, $pageCount, &$summaryPages, Array $totalPerUnit, $continuePage = false)
    {
        $billColumnSettings       = $this->bill->BillColumnSettings->toArray();
        $summaryPages[$pageCount] = array();

        $maxRows = $this->getSummaryMaxRows() - 16;

        $blankRow    = new SplFixedArray(4);
        $blankRow[0] = null;
        $blankRow[1] = self::ROW_TYPE_BLANK;
        $blankRow[2] = null;
        $blankRow[3] = null;

        //blank row
        array_push($summaryPages[$pageCount], $blankRow);//starts with a blank row

        $contdPrefix       = !$continuePage ? null : " {$this->printSettings['layoutSetting']['contdPrefix']}";
        $summaryPageHeader = new SplFixedArray(4);

        if ( $this->printSettings['layoutSetting']['printContdEndDesc'] )
        {
            $summaryPageHeader[0] = $this->printSettings['phrase']['summaryInGridPrefix'] . $contdPrefix;
        }
        else
        {
            $summaryPageHeader[0] = $contdPrefix . ' ' . $this->printSettings['phrase']['summaryInGridPrefix'];
        }

        $summaryPageHeader[1] = self::ROW_TYPE_SUMMARY_PAGE_TITLE;
        $summaryPageHeader[2] = null;
        $summaryPageHeader[3] = null;

        //blank row
        array_push($summaryPages[$pageCount], $summaryPageHeader);//starts with a blank row

        if ( $continuePage )
        {
            //blank row
            array_push($summaryPages[$pageCount], $blankRow);//starts with a blank row

            $bringForwardHeader    = new SplFixedArray(4);
            $bringForwardHeader[0] = trim(self::getSummaryNextPageBringForwardPrefix() . ' ' . self::getSummaryPageNumberingPrefix($pageCount - 1));
            $bringForwardHeader[1] = self::ROW_TYPE_SUMMARY_PAGE_TITLE;
            $bringForwardHeader[2] = $totalPerUnit;
            $bringForwardHeader[3] = null;

            //blank row
            array_push($summaryPages[$pageCount], $bringForwardHeader);//starts with a blank row
        }

        //blank row
        array_push($summaryPages[$pageCount], $blankRow);//starts with a blank row

        $rowCount = ( $continuePage ) ? 5 : 3;

        foreach ( $rows as $idx => $row )
        {
            $occupiedRows = Utilities::justify($row['description'], $this->SUMMARY_MAX_CHARACTERS);
            $rowCount += $occupiedRows->count();

            if ( $rowCount <= $maxRows )
            {
                foreach ( $occupiedRows as $key => $occupiedRow )
                {
                    $elementRow    = new SplFixedArray(4);
                    $elementRow[0] = $occupiedRow;
                    $elementRow[1] = self::ROW_TYPE_ELEMENT;
                    $elementRow[2] = null;
                    $elementRow[3] = null;

                    if ( $key == count($occupiedRows) - 1 )
                    {
                        $totalAmount = array();

                        if ( $this->printGrandTotalQty )
                        {
                            $totalAmount[0] = is_array($row['last_collection_page']['amount']) ? 0 : self::gridCurrencyRoundingFormat($row['last_collection_page']['amount']);
                            $totalPerUnit[0] += $totalAmount[0];
                        }
                        else
                        {
                            foreach ( $billColumnSettings as $billColumnSetting )
                            {
                                $totalAmount[$billColumnSetting['id']] = self::gridCurrencyRoundingFormat(array_key_exists($billColumnSetting['id'], $row['last_collection_page']['amount']) ? $row['last_collection_page']['amount'][$billColumnSetting['id']] : 0);
                                $totalPerUnit[$billColumnSetting['id']] += $totalAmount[$billColumnSetting['id']];
                            }
                        }

                        $elementRow[2] = $totalAmount;
                        $elementRow[3] = trim($this->printSettings['layoutSetting']['pageNoPrefix'] . $row['last_collection_page']['page_count']);
                    }

                    array_push($summaryPages[$pageCount], $elementRow);
                }

                // sum all amount and we need to carry forward total amount to the next page
                if ( $this->printGrandTotalQty )
                {
                    $summaryPages[$pageCount]['total_per_unit'][0] = self::gridCurrencyRoundingFormat(array_key_exists(0, $totalPerUnit) ? $totalPerUnit[0] : 0);
                }
                else
                {
                    foreach ( $billColumnSettings as $billColumnSetting )
                    {
                        $summaryPages[$pageCount]['total_per_unit'][$billColumnSetting['id']] = self::gridCurrencyRoundingFormat(array_key_exists($billColumnSetting['id'], $totalPerUnit) ? $totalPerUnit[$billColumnSetting['id']] : 0);
                    }
                }

                unset( $rows[$idx], $row );

                //blank row
                array_push($summaryPages[$pageCount], $blankRow);

                $rowCount ++;//plus one blank row;
            }
            else
            {
                $pageCount ++;
                $this->generateSummaryPage($rows, $billColumnSettings, $pageCount, $summaryPages, $totalPerUnit, true);
                break;
            }
        }
    }

    /*
     * We use SplFixedArray as data structure to boost performance. Since associative array cannot be use in SplFixedArray, we have to use indexes
     * to get values. Below are indexes and what they represent as their values
     *
     * $row:
     * 0 - description
     * 1 - type
     * 2 - amount
     */
    protected function generateCollectionPages($elementInfo, Array $billColumnSettings, $itemPages, $pageNumberDescription, $pageCount, $totalItemPages, Array &$collectionPages, Array $totalAmount, $continuePage = false)
    {
        // if current page is new page from previous page then increase the page no counter
        // else then use back the old page no from previous revision
        if ( $continuePage )
        {
            $pageCount = self::addendumCollectionPageNoFormatter($this->addendumCollectionPageCount, true);
        }
        else
        {
            $pageCount = $this->addendumCollectionPageCount;
        }

        $billColumnSettings                          = $this->bill->BillColumnSettings->toArray();
        $collectionPages[$pageCount]                 = array();
        $collectionPages[$pageCount]['total_amount'] = array();
        $collectionPages[$pageCount]['page_count']   = $pageCount;
        $pageKey                                     = 1;
        $maxRows                                     = $this->getMaxRows() - 4;//less 4 rows for collection page
        $currentAffectedPageNo                       = array();

        $blankRow    = new SplFixedArray(3);
        $blankRow[0] = null;
        $blankRow[1] = self::ROW_TYPE_BLANK;
        $blankRow[2] = null;

        //blank row
        array_push($collectionPages[$pageCount], $blankRow);//starts with a blank row

        $rowCount = 1;

        $contdPrefix = $continuePage ? " {$this->printSettings['layoutSetting']['contdPrefix']}" : null;

        /*
        * Always display element description at start of collection page.
        */
        if ( $this->printSettings['layoutSetting']['printContdEndDesc'] )
        {
            $occupiedRows = Utilities::justify($elementInfo['description'] . $contdPrefix, $this->MAX_CHARACTERS);
        }
        else
        {
            $occupiedRows = Utilities::justify($contdPrefix . ' ' . $elementInfo['description'], $this->MAX_CHARACTERS);
        }

        foreach ( $occupiedRows as $occupiedRow )
        {
            $elementRow    = new SplFixedArray(3);
            $elementRow[0] = $occupiedRow;
            $elementRow[1] = self::ROW_TYPE_ELEMENT;
            $elementRow[2] = null;

            array_push($collectionPages[$pageCount], $elementRow);
        }

        //blank row
        array_push($collectionPages[$pageCount], $blankRow);

        //Collection title
        $collectionTitle = new SplFixedArray(3);

        if ( $this->printSettings['layoutSetting']['printContdEndDesc'] )
        {
            $collectionTitle[0] = $this->printSettings['phrase']['collectionInGridPrefix'] . $contdPrefix;
        }
        else
        {
            $collectionTitle[0] = $contdPrefix . ' ' . $this->printSettings['phrase']['collectionInGridPrefix'];
        }

        $collectionTitle[1] = self::ROW_TYPE_COLLECTION_TITLE;
        $collectionTitle[2] = null;

        array_push($collectionPages[$pageCount], $collectionTitle);

        //blank row
        array_push($collectionPages[$pageCount], $blankRow);

        if ( $continuePage )
        {
            $lastPageCount = $this->addendumCollectionFromPreviousPage;

            //Collection total from previous page
            $fromPreviousPage    = new SplFixedArray(3);
            $fromPreviousPage[0] = trim(self::getCollectionNextPageBringForwardPrefix() . ' ' . trim(self::getPageNoPrefix() . "{$elementInfo['element_count']}/{$lastPageCount}"));
            $fromPreviousPage[1] = self::ROW_TYPE_COLLECTION_TITLE;
            $fromPreviousPage[2] = $totalAmount;

            // sum all amount and we need to carry forward total amount to the next page
            if ( $this->printGrandTotalQty )
            {
                $collectionPages[$pageCount]['total_amount'] = self::gridCurrencyRoundingFormat(array_key_exists(0, $totalAmount) ? $totalAmount[0] : 0);
            }
            else
            {
                foreach ( $billColumnSettings as $billColumnSetting )
                {
                    $collectionPages[$pageCount]['total_amount'][$billColumnSetting['id']] = $totalAmount[$billColumnSetting['id']];
                }
            }

            array_push($collectionPages[$pageCount], $fromPreviousPage);

            //blank row
            array_push($collectionPages[$pageCount], $blankRow);

            $rowCount += $occupiedRows->count() + 3 + 2;//plus one blank row & collection title
        }
        else
        {
            $rowCount += $occupiedRows->count() + 3;//plus one blank row & collection title
        }

        // filter page no first before submit to processing part
        foreach ( $itemPages as $pageKey => $itemPage )
        {
            array_push($currentAffectedPageNo, array( 'pageNo' => $pageKey ));
        }

        if ( isset ( $this->oldPageNo[$elementInfo['id']] ) )
        {
            $oldPageNo = $this->oldPageNo[$elementInfo['id']];
        }
        else
        {
            $oldPageNo = array();
        }

        if ( !$continuePage )
        {
            $this->newAffectedPageNos = self::addendumCollectionPageFormatter($oldPageNo, $currentAffectedPageNo);
        }

        foreach ( $this->newAffectedPageNos as $pageKey => $newAffectedPageNo )
        {
            $usePreviousRevisionAmt = false;
            $previousPageAmt        = array();

            $occupiedRows = Utilities::justify($pageNumberDescription . " " . self::getPageNoPrefix() . $elementInfo['element_count'] . "/" . $pageKey, $this->MAX_CHARACTERS);
            $rowCount += $occupiedRows->count();

            foreach ( $oldPageNo as $oldPageNoInfo )
            {
                if ( $oldPageNoInfo['pageNo'] == $pageKey )
                {
                    $usePreviousRevisionAmt = true;
                    $previousPageAmt        = isset( $oldPageNoInfo['pageAmt'] ) ? $oldPageNoInfo['pageAmt'] : array();
                    break;
                }
            }

            if ( $rowCount <= $maxRows )
            {
                $occupiedRowsCount       = count($occupiedRows);
                $billColumnSettingsCount = count($billColumnSettings);

                foreach ( $occupiedRows as $key => $occupiedRow )
                {
                    $row    = new SplFixedArray(3);
                    $row[0] = $occupiedRow;
                    $row[1] = null;
                    $row[2] = new SplFixedArray($billColumnSettingsCount);

                    if ( $key + 1 == $occupiedRowsCount )
                    {
                        if ( $this->printGrandTotalQty )
                        {
                            $amount = 0;

                            // if current page is unaffected page, then use the page's current costing total
                            if ( $usePreviousRevisionAmt )
                            {
                                if ( !is_array($previousPageAmt) )
                                {
                                    $amount = $previousPageAmt;
                                }
                                else
                                {
                                    $amount = 0;
                                }
                            }
                            else
                            {
                                if ( isset( $itemPages[$pageKey] ) )
                                {
                                    foreach ( $itemPages[$pageKey] as $itemRow )
                                    {
                                        if ( $itemRow[self::ROW_BILL_ITEM_ID] && $itemRow[self::ROW_BILL_ITEM_ID] > 0 && $itemRow[self::ROW_BILL_ITEM_RATE] && $itemRow[self::ROW_BILL_ITEM_RATE] != 0 )
                                        {
                                            if ( $itemRow[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM or $itemRow[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT or $itemRow[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE )
                                            {
                                                $itemAmount = self::gridCurrencyRoundingFormat((float) $itemRow[self::ROW_BILL_ITEM_RATE]);
                                                $amount += $itemAmount;
                                            }
                                            else
                                            {
                                                $itemAmount = self::gridCurrencyRoundingFormat((float) $itemRow[self::ROW_BILL_ITEM_RATE] * $itemRow[self::ROW_BILL_ITEM_QTY_PER_UNIT]);
                                                $amount += $itemAmount;
                                            }
                                        }
                                        else
                                        {
                                            continue;
                                        }

                                        unset( $itemRow );
                                    }
                                }
                            }

                            $row[2] = $amount;
                            $totalAmount[0] += $amount;
                        }
                        else
                        {
                            foreach ( $billColumnSettings as $idx => $billColumnSetting )
                            {
                                $amount = 0;

                                // if current page is unaffected page, then use the page's current costing total
                                if ( $usePreviousRevisionAmt )
                                {
                                    if ( isset ( $previousPageAmt[$billColumnSetting['id']] ) )
                                    {
                                        $amount = $previousPageAmt[$billColumnSetting['id']];
                                    }
                                    else
                                    {
                                        $amount = 0;
                                    }
                                }
                                else
                                {
                                    if ( isset( $itemPages[$pageKey] ) )
                                    {
                                        foreach ( $itemPages[$pageKey] as $itemRow )
                                        {
                                            if ( $itemRow[self::ROW_BILL_ITEM_ID] && $itemRow[self::ROW_BILL_ITEM_ID] > 0 && $itemRow[self::ROW_BILL_ITEM_RATE] && $itemRow[self::ROW_BILL_ITEM_RATE] != 0 )
                                            {
                                                if ( $itemRow[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT or $itemRow[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE )
                                                {
                                                    $itemAmount = self::gridCurrencyRoundingFormat((float) $itemRow[self::ROW_BILL_ITEM_RATE]);
                                                    $amount += $itemAmount;
                                                }
                                                else
                                                {
                                                    $itemQuantity = is_array($itemRow[self::ROW_BILL_ITEM_QTY_PER_UNIT]) && array_key_exists($billColumnSetting['id'], $itemRow[self::ROW_BILL_ITEM_QTY_PER_UNIT]) ? $itemRow[self::ROW_BILL_ITEM_QTY_PER_UNIT][$billColumnSetting['id']] : 0;
                                                    $itemAmount   = self::gridCurrencyRoundingFormat((float) $itemRow[self::ROW_BILL_ITEM_RATE] * $itemQuantity);
                                                    $amount += $itemAmount;
                                                }
                                            }
                                            else
                                            {
                                                continue;
                                            }

                                            unset( $itemRow );
                                        }
                                    }
                                }

                                $totalAmount[$billColumnSetting['id']] += $amount;
                                $billColumnAmount    = new SplFixedArray(2);
                                $billColumnAmount[0] = $billColumnSetting['id'];//key 0 is bill column setting id
                                $billColumnAmount[1] = $amount;//key 1 is amount
                                $row[2][$idx]        = $billColumnAmount;

                                unset( $billColumnSetting );
                            }
                        }
                    }

                    array_push($collectionPages[$pageCount], $row);

                    unset( $occupiedRow );
                }

                //blank row
                array_push($collectionPages[$pageCount], $blankRow);

                // sum all amount and we need to carry forward total amount to the next page
                if ( $this->printGrandTotalQty )
                {
                    if ( $usePreviousRevisionAmt )
                    {
                        if ( !is_array($previousPageAmt) )
                        {
                            $collectionPageAmt = $previousPageAmt;
                        }
                        else
                        {
                            $collectionPageAmt = 0;
                        }

                        if ( is_array($collectionPages[$pageCount]['total_amount']) )
                        {
                            $collectionPages[$pageCount]['total_amount'] = $collectionPageAmt;
                        }
                        else
                        {
                            $collectionPages[$pageCount]['total_amount'] += $collectionPageAmt;
                        }
                    }
                    else
                    {
                        $collectionPageAmt = self::gridCurrencyRoundingFormat(array_key_exists(0, $totalAmount) ? $totalAmount[0] : 0);

                        $collectionPages[$pageCount]['total_amount'] = $collectionPageAmt;
                    }
                }
                else
                {
                    foreach ( $billColumnSettings as $billColumnSetting )
                    {
                        // if current page is unaffected page, then use the page's current costing total
                        if ( $usePreviousRevisionAmt )
                        {
                            if ( isset( $previousPageAmt[$billColumnSetting['id']] ) )
                            {
                                $collectionPageAmt = $previousPageAmt[$billColumnSetting['id']];
                            }
                            else
                            {
                                $collectionPageAmt = 0;
                            }

                            if ( !isset ( $collectionPages[$pageCount]['total_amount'][$billColumnSetting['id']] ) )
                            {
                                $collectionPages[$pageCount]['total_amount'][$billColumnSetting['id']] = $collectionPageAmt;
                            }
                            else
                            {
                                $collectionPages[$pageCount]['total_amount'][$billColumnSetting['id']] += $collectionPageAmt;
                            }
                        }
                        else
                        {
                            $collectionPageAmt = self::gridCurrencyRoundingFormat(array_key_exists($billColumnSetting['id'], $totalAmount) ? $totalAmount[$billColumnSetting['id']] : 0);

                            $collectionPages[$pageCount]['total_amount'][$billColumnSetting['id']] = $collectionPageAmt;
                        }
                    }
                }

                $rowCount ++;//plus one blank row;

                unset( $this->newAffectedPageNos[$pageKey], $itemPages[$pageKey], $row );
            }
            else
            {
                $this->addendumCollectionFromPreviousPage = $pageCount;

                $this->currentAddedCollectionPage ++;

                $this->generateCollectionPages($elementInfo, $billColumnSettings, $itemPages, $pageNumberDescription, $pageCount, $totalItemPages, $collectionPages, $totalAmount, true);
                break;
            }
        }

        unset( $itemPages );

        if ( $pageKey == $totalItemPages )//add a blank row at end of collection page
        {
            //blank row
            array_push($collectionPages[$pageCount], $blankRow);
        }
    }

    /*
     * We use SplFixedArray as data structure to boost performance. Since associative array cannot be use in SplFixedArray, we have to use indexes
     * to get values. Below are indexes and what they represent as their values
     *
     * $row:
     * 0 - id
     * 1 - row index
     * 2 - description
     * 3 - level
     * 4 - type
     * 5 - unit
     * 6 - rate
     * 7 - quantity per unit by bill column settings
     */
    protected function generateBillItemPages(array $billItems, array $billColumnSettings, $elementInfo, $pageCount, $ancestors, &$itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, $newPage = false)
    {
        $pageCount = $this->addendumPageCount;

        if ( $this->addendumAddonPages > 0 )
        {
            $pageCount = $this->addendumPageCount . '+' . $this->addendumAddonPages;
        }

        $itemPages[$pageCount] = [];
        $layoutSettings        = $this->printSettings['layoutSetting'];
        $maxRows               = $this->getMaxRows();

        $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
        $rowCount = 1;

        /*
         * Always display element description at start of every page.
         */
        $descriptionCont = ( $pageCount > 1 or $this->addendumAddonPages > 0 ) ? $layoutSettings['contdPrefix'] : null;

        $occupiedRows = $this->addElementDescription($elementInfo, $pageCount, $itemPages, $descriptionCont);

        $rowCount += $occupiedRows->count();

        //blank row
        $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
        $rowCount++;

        $itemIndex    = 1;
        $counterIndex = 0;//display item's index in BQ

        while (list($x, $billItem) = each($billItems))
        {
            $ancestors = $billItem['level'] == 0 ? [] : $ancestors;

            if (($billItem['type'] == BillItem::TYPE_HEADER || $billItem['type'] == BillItem::TYPE_HEADER_N) && ($billItem['rgt'] - $billItem['lft'] > 1 ))
            {
                $row                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
                $row[self::ROW_BILL_ITEM_ID]           = $billItem['id'];//id
                $row[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
                $row[self::ROW_BILL_ITEM_DESCRIPTION]  = $billItem['description'];//description
                $row[self::ROW_BILL_ITEM_LEVEL]        = $billItem['level'];//level
                $row[self::ROW_BILL_ITEM_TYPE]         = $billItem['type'];//type
                $row[self::ROW_BILL_ITEM_UNIT]         = $billItem['lft']; //set lft info (only for ancestor)
                $row[self::ROW_BILL_ITEM_RATE]         = $billItem['rgt']; //set rgt info (only for ancestor)
                $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $billItem['root_id']; //set root_id info (only for )
                $row[self::ROW_BILL_ITEM_INCLUDE]      = null;//include

                $ancestors[$billItem['level']] = $row;

                $ancestors = array_splice($ancestors, 0, $billItem['level'] + 1);

                unset($row);
            }

            /*
             * To get all ancestors from previous page so we can display it as continued headers
             * before we print out the item
             */
            if ( ( $pageCount > 1 or $this->addendumAddonPages > 0 ) and $itemIndex == 1 and $billItem['level'] != 0 )
            {
                // if detected current looped item is same level with ancestor, then overwrite it
                $this->unsetHeadThatIsSameLevelWithItemOnNextPage($ancestors, $billItem);

                foreach ( $ancestors as $ancestor )
                {
                    if ( $ancestor[self::ROW_BILL_ITEM_ID] == $billItem['id'] )
                    {
                        continue;
                    }

                    $occupiedRows = $this->generateAncestorOccupiedRows($ancestor, $pageCount, $layoutSettings);
                    $availableRows = $maxRows - $rowCount;

                    if(($occupiedRows->count()+4) > $availableRows)
                    {
                        /*
                         * For ancestor we need to spare 4 rows that is to consider
                         * a. (at least) 1 line of the child's description
                         * b. 1 line for bottom ellipsis (if it's a truncated description)
                         * c. 1 line for top ellipsis (if it's a continued descriptoin from previous page)
                         * d. 1 line for blank row
                         * 
                         * This needs to be considered since we will reprint ancestors as parents for all items
                         * underneath it and given the rules that headers description cannot be truncated
                         */
                        throw new PageGeneratorException(PageGeneratorException::ERROR_INSUFFICIENT_ROW, [
                            'id'             => $billItem['id'],
                            'page_number'    => $pageCount,
                            'page_items'     => $itemPages[$pageCount], 
                            'rows_available' => $availableRows,
                            'max_rows'       => $maxRows,
                            'occupied_rows'  => $occupiedRows
                        ]);
                    }

                    foreach($occupiedRows as $occupiedRow)
                    {
                        $this->addRowToItemPage($itemPages[$pageCount], $ancestor[self::ROW_BILL_ITEM_ID], null, $occupiedRow, $ancestor[self::ROW_BILL_ITEM_LEVEL], $ancestor[self::ROW_BILL_ITEM_TYPE]);
                        $rowCount++;
                    }
                    
                    //blank row
                    $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
                    $rowCount++;

                    unset($occupiedRow, $occupiedRows, $ancestor);
                }
            }

            if($billItem['type'] == BillItem::TYPE_HEADER_N and !$newPage)
            {
                $occupiedRows = null;
                unset($occupiedRows);

                reset($billItems);
                $this->addendumAddonPages++;

                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                break;
            }

            $occupiedRows      = $this->calculateBQItemDescription($billItem);
            $totalOccupiedRows = ($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE) ? (self::PC_RATE_TABLE_SIZE+$occupiedRows->count()) : $occupiedRows->count();
            $availableRows     = ($maxRows - $rowCount);
            $availableRows     = ($availableRows < 0 ) ? 0 : $availableRows;
            $isLastChunk       = true;

            if($totalOccupiedRows > $availableRows)
            {
                /*
                 * If item description cannot fit into page we have to determine either to truncate the description or move the item to the next page.
                 * Item will be truncated if it is the ONLY item in the page (the whole description is too long to fit into a page) else we just
                 * push the item to the next page.
                 * 
                 */
                if($itemIndex > 1)
                {
                    $occupiedRows = null;
                    unset($occupiedRows);

                    reset($billItems);
                    $this->addendumAddonPages++;

                    $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                    break;
                }
                else
                {
                    try
                    {
                        list($availableRows, $isLastChunk) = $this->breakDownItemDescription($billItems, $billItem, $occupiedRows, $availableRows);
                        $totalOccupiedRows = ($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE && $isLastChunk) ? (self::PC_RATE_TABLE_SIZE+$occupiedRows->count()) : $occupiedRows->count();
                    }
                    catch(PageGeneratorException $e)
                    {
                        throw new PageGeneratorException($e->getMessage(), [
                            'id'             => $billItem['id'],
                            'page_number'    => $pageCount,
                            'page_items'     => $itemPages[$pageCount], 
                            'rows_available' => $availableRows,
                            'max_rows'       => $maxRows,
                            'occupied_rows'  => $occupiedRows
                        ]);
                    }
                }
            }

            if($isLastChunk && ($billItem['isContinuedDescription'] ?? false))
            {
                $billItem['isContinuingDescription'] = false;
                $this->addEllipses($billItem, $occupiedRows);
                
                $totalOccupiedRows = ($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE && $isLastChunk) ? (self::PC_RATE_TABLE_SIZE+$occupiedRows->count()) : $occupiedRows->count();
                
                if($totalOccupiedRows > $availableRows)
                {
                    throw new PageGeneratorException(PageGeneratorException::ERROR_INSUFFICIENT_ROW, [
                        'id'             => $billItem['id'],
                        'page_number'    => $pageCount,
                        'page_items'     => $itemPages[$pageCount], 
                        'rows_available' => $availableRows,
                        'max_rows'       => $maxRows,
                        'occupied_rows'  => $occupiedRows
                    ]);
                }
            }

            $primeCostRateRows = null;
            if($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE && $isLastChunk)
            {
                if($totalOccupiedRows > $availableRows)
                {
                    throw new PageGeneratorException(PageGeneratorException::ERROR_PC_RATE_INSUFFICIENT_ROW, [
                        'id'             => $billItem['id'],
                        'page_number'    => $pageCount,
                        'page_items'     => $itemPages[$pageCount], 
                        'rows_available' => $availableRows,
                        'max_rows'       => $maxRows,
                        'occupied_rows'  => $occupiedRows
                    ]);
                }

                $primeCostRateRows = $this->generatePrimeCostRateRows($billItem['id']);
            }

            reset($billItems);
            $x = key($billItems);//reset current $billItems iteration key to a latest key from the truncated item*/

            if($availableRows >= $totalOccupiedRows) // If can fit in remaining space of current page.
            {
                $rowCount += $occupiedRows->count();

                foreach ( $occupiedRows as $key => $occupiedRow )
                {
                    if ( $key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N )
                    {
                        $counterIndex ++;
                    }

                    $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                    $row[self::ROW_BILL_ITEM_ROW_IDX]     = ( $key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID && !($billItem['isContinuedDescription'] ?? false)) ? Utilities::generateCharFromNumber($counterIndex, $this->printSettings['layoutSetting']['includeIandO']) : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = (!empty($occupiedRow) && !in_array($billItem['type'], [BillItem::TYPE_ITEM_HTML_EDITOR, BillItem::TYPE_NOID]) ) ? Utilities::inlineJustify($occupiedRow, $this->MAX_CHARACTERS) : $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL]       = $billItem['level'];
                    $row[self::ROW_BILL_ITEM_TYPE]        = $billItem['type'];

                    //Generate Bill Ref
                    if($key+1 == $occupiedRows->count())
                    {
                        $this->generateBillReference($billItem, $counterIndex, $pageCount);
                    }

                    if($this->saveOriginalBillInformation && !($billItem['isContinuedDescription'] ?? false) && ($key + 1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N))
                    {
                        $this->savePageItemReference($elementInfo, $pageCount, $key, $occupiedRows, $billItem);
                    }

                    if($isLastChunk && $key + 1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N )
                    {
                        $row[self::ROW_BILL_ITEM_ID]   = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom'];
                        $row[self::ROW_BILL_ITEM_RATE] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $ratesAfterMarkup) ? $ratesAfterMarkup[$billItem['id']] : 0);

                        $quantityPerUnit = [];
                        $includeStatus   = null;

                        if ( $this->printGrandTotalQty )
                        {
                            /*
                             * this is actually not a quantity per unit but grand total quantity instead. But we just assign it to the same variable name so it can be used
                             * for case where print grand total qty is disabled.
                             */
                            $quantityPerUnit = array_key_exists($billItem['id'], $itemQuantities) ? $itemQuantities[$billItem['id']][0] : 0;

                            $row[self::ROW_BILL_ITEM_INCLUDE] = $includeStatus;

                            if ( $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT )
                            {
                                $quantityPerUnit = array_key_exists($billItem['id'], $lumpSumPercents) ? $lumpSumPercents[$billItem['id']][0] : 0;
                            }

                            if ( $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM or $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT or $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE )
                            {
                                $row[self::ROW_BILL_ITEM_RATE] = self::gridCurrencyRoundingFormat($billItem['grand_total_after_markup']);
                            }
                        }
                        else
                        {
                            foreach ( $billColumnSettings as $billColumnSetting )
                            {
                                $itemQuantity = (array_key_exists($billColumnSetting['id'], $itemQuantities) && array_key_exists($billItem['id'], $itemQuantities[$billColumnSetting['id']])) ? $itemQuantities[$billColumnSetting['id']][$billItem['id']][self::ROW_BILL_ITEM_ID] : 0;

                                $quantityPerUnit[$billColumnSetting['id']] = $itemQuantity;

                                $includeStatus[$billColumnSetting['id']] = array_key_exists($billItem['id'], $itemIncludeStatus[$billColumnSetting['id']]) ? $itemIncludeStatus[$billColumnSetting['id']][$billItem['id']] : true;

                                if ( $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT )
                                {
                                    $quantityPerUnit[$billColumnSetting['id']] = array_key_exists($billItem['id'], $lumpSumPercents) ? $lumpSumPercents[$billItem['id']][self::ROW_BILL_ITEM_ID] : 0;
                                }
                            }

                            $row[self::ROW_BILL_ITEM_INCLUDE] = $includeStatus;
                        }

                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $quantityPerUnit;
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID]           = null;
                        $row[self::ROW_BILL_ITEM_UNIT]         = null;//unit
                        $row[self::ROW_BILL_ITEM_RATE]         = null;//rate
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//qty per unit
                        $row[self::ROW_BILL_ITEM_INCLUDE]      = true;// include
                    }

                    $this->addRowToItemPage(
                        $itemPages[$pageCount],
                        $row[self::ROW_BILL_ITEM_ID],
                        $row[self::ROW_BILL_ITEM_ROW_IDX],
                        $row[self::ROW_BILL_ITEM_DESCRIPTION],
                        $row[self::ROW_BILL_ITEM_LEVEL],
                        $row[self::ROW_BILL_ITEM_TYPE],
                        $row[self::ROW_BILL_ITEM_UNIT],
                        $row[self::ROW_BILL_ITEM_RATE],
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT],
                        $row[self::ROW_BILL_ITEM_INCLUDE]
                    );

                    unset($row);
                }

                if($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE && $primeCostRateRows)
                {
                    foreach($primeCostRateRows as $primeCostRateRow)
                    {
                        $this->addRowToItemPage(
                            $itemPages[$pageCount],
                            $primeCostRateRow[self::ROW_BILL_ITEM_ID],
                            $primeCostRateRow[self::ROW_BILL_ITEM_ROW_IDX],
                            $primeCostRateRow[self::ROW_BILL_ITEM_DESCRIPTION],
                            $primeCostRateRow[self::ROW_BILL_ITEM_LEVEL],
                            $primeCostRateRow[self::ROW_BILL_ITEM_TYPE],
                            $primeCostRateRow[self::ROW_BILL_ITEM_UNIT],
                            $primeCostRateRow[self::ROW_BILL_ITEM_RATE],
                            $primeCostRateRow[self::ROW_BILL_ITEM_QTY_PER_UNIT],
                            $primeCostRateRow[self::ROW_BILL_ITEM_INCLUDE]
                        );

                        $rowCount++;
                    }
                }

                //blank row
                $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
                $rowCount ++;//plus one blank row;

                $itemIndex ++;
                $newPage = false;

                unset($billItems[$x], $occupiedRows);

                reset($billItems);
            }
            else
            {
                reset($billItems);
                $this->addendumAddonPages++;

                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                break;
            }
        }
    }

    protected function generateAncestorOccupiedRows($ancestor, $pageCount, $layoutSettings)
    {
        $descriptionCont = ( $pageCount > 1 or $this->addendumAddonPages > 0 ) ? $layoutSettings['contdPrefix'] : null;

        if ( $this->printSettings['layoutSetting']['printContdEndDesc'] )
        {
            $occupiedRows = Utilities::justify($ancestor[self::ROW_BILL_ITEM_DESCRIPTION]." ".$descriptionCont, $this->MAX_CHARACTERS);
        }
        else
        {
            $occupiedRows = Utilities::justify($descriptionCont." ".$ancestor[self::ROW_BILL_ITEM_DESCRIPTION], $this->MAX_CHARACTERS);
        }

        return $occupiedRows;
    }
}
