<?php

class sfBuildspaceBQEditorAddendumGenerator extends sfBuildspaceBQAddendumGenerator
{
    protected $editorProjectInfo;

    public function __construct(ProjectStructure $bill, EditorProjectInformation $editorProjectInfo, $elements)
    {
        $this->editorProjectInfo = $editorProjectInfo;
        $this->bill              = $bill;
        $this->billElements      = $elements;

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->projectRevision        = $editorProjectInfo->PrintRevision;
        $this->currentProjectRevision = ProjectRevisionTable::getLatestLockedProjectRevisionFromBillId($bill->root_id);

        $this->billStructure = $this->queryBillStructure();

        // Generate Element Order By Bill
        $this->elementsOrder            = $this->getElementOrder();

        // get bill's printout setting
        $printSettings       = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, true);
        $this->printSettings = $printSettings;

        $this->printGrandTotalQty = $printSettings['layoutSetting']['printGrandTotalQty'];

        $project             = $bill->root_id == $bill->id ? $bill : ProjectStructureTable::getInstance()->find($bill->root_id);
        $numberOfBillColumns = $bill->getBillColumnSettings()->count();

        $this->fontType = self::setFontType($printSettings['layoutSetting']['fontTypeName']);
        $this->fontSize = $printSettings['layoutSetting']['fontSize'];

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

    public function getOldPageNoAndItemPricing()
    {
        $data              = array();
        $billElementIds    = array();
        $overallTotal      = 0;
        $oldPageIds        = array();
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

        $stmt = $pdo->prepare('SELECT b.id, b.page_no, b.element_id, b.new_revision_id
            FROM ' . BillPageTable::getInstance()->getTableName() . ' b
            WHERE b.element_id IN (' . implode(', ', $billElementIds) . ') AND b.revision_id < ' . $this->projectRevision->id . '
            ORDER BY b.page_no ASC');

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
                $stmt = $pdo->prepare('SELECT i.element_id, bpi.bill_page_id, SUM(COALESCE(bi.grand_total, 0)) as total
                    FROM ' . BillPageItemTable::getInstance()->getTableName() . ' bpi
                    JOIN ' . EditorBillItemInfoTable::getInstance()->getTableName() . ' bi ON bi.bill_item_id = bpi.bill_item_id AND bi.company_id = '.$this->editorProjectInfo->company_id.'
                    JOIN ' . BillItemTable::getInstance()->getTableName() .' i ON bi.bill_item_id = i.id
                    WHERE bpi.bill_page_id IN (' . implode(', ', $oldPageIds) . ')
                    AND i.deleted_at IS NULL
                    GROUP BY i.element_id, bpi.bill_page_id
                    ORDER BY i.element_id, bpi.bill_page_id ASC');

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
                    $stmt = $pdo->prepare('SELECT DISTINCT (bibr.id), bi.element_id, bpi.bill_page_id, bibr.bill_column_setting_id, COALESCE(bibr.grand_total, 0) as total
                    FROM ' . BillPageItemTable::getInstance()->getTableName() . ' bpi
                    JOIN ' . BillItemTable::getInstance()->getTableName() . ' bi ON bi.id = bpi.bill_item_id
                    JOIN ' . EditorBillItemInfoTable::getInstance()->getTableName() . ' info ON bi.id = info.bill_item_id AND info.company_id = '.$this->editorProjectInfo->company_id.'
                    JOIN ' . EditorBillItemTypeReferenceTable::getInstance()->getTableName() . ' bibr ON bibr.bill_item_info_id = info.id
                    WHERE bpi.bill_page_id IN (' . implode(', ', $oldPageIds) . ') AND bibr.bill_column_setting_id = ' . $column->id . '
                    AND bi.deleted_at IS NULL
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

    public function getElementCollectionPageNo()
    {
        $data           = array();
        $billElementIds = array();
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
    public function queryBillStructure()
    {
        $pdo           = $this->pdo;
        $bill          = $this->bill;
        $billElements  = $this->billElements;
        $billStructure = array();

        $sqlFieldCond = '(
            CASE p.type WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.' THEN
                CASE inl.description IS NULL
                WHEN TRUE
                THEN null
                ELSE inl.description
                END
            ELSE
                p.description
                END
            ) AS description,
            (CASE p.type WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.' THEN
                CASE
                inl_uom.symbol IS NULL
                WHEN TRUE
                THEN null
                ELSE inl_uom.symbol
                END
            ELSE
                uom.symbol
                END
            ) AS uom,
            (CASE p.type WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.' THEN
                CASE
                inl_uom.id IS NULL
                WHEN TRUE
                THEN null
                ELSE inl_uom.id
                END
            ELSE
                uom.id
                END
            ) AS uom_id';

        $nlTable = "LEFT JOIN ".EditorBillItemNotListedTable::getInstance()->getTableName()." inl ON inl.bill_item_id = info.bill_item_id AND inl.company_id = ".$this->editorProjectInfo->company_id."
            LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." inl_uom ON inl.uom_id = inl_uom.id AND inl_uom.deleted_at IS NULL";

        foreach ( $billElements as $billElement )
        {
            $elementSqlPart = $billElement instanceof BillElement ? "AND e.id = " . $billElement->id : null;

            $stmt = $pdo->prepare("SELECT e.id, e.description
                FROM " . BillElementTable::getInstance()->getTableName() . " e
                WHERE e.project_structure_id = " . $bill->id . " " . $elementSqlPart . "
                AND e.deleted_at IS NULL ORDER BY e.priority");

            $stmt->execute();


            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $elements as $element )
            {
                $result = array(
                    'id'          => $element['id'],
                    'description' => $element['description']
                );

                // loop each addendum affected page's item
                foreach ( $billElement['BillPages'] as $billPage )
                {
                    $pageItemIds = array();

                    foreach ( $billPage['Items'] as $item )
                    {
                        array_push($pageItemIds, $item['bill_item_id']);
                    }

                    $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.element_id, p.type,
                    COALESCE(info.grand_total, 0) AS grand_total_after_markup, p.level, p.priority,
                    p.lft, p.rgt, p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char, ".$sqlFieldCond."
                    FROM " . BillItemTable::getInstance()->getTableName() . " c
                    JOIN " . BillItemTable::getInstance()->getTableName() . " p
                    ON c.lft BETWEEN p.lft AND p.rgt
                    LEFT JOIN ".EditorBillItemInfoTable::getInstance()->getTableName()." info ON p.id = info.bill_item_id AND info.company_id = ".$this->editorProjectInfo->company_id."
                    LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                    ".$nlTable."
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

    public function getItemQuantities()
    {
        $pdo  = $this->pdo;
        $bill = $this->bill;

        $billStructure   = $this->billStructure;
        $implodedItemIds = null;
        $result          = array();

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
                $sqlFieldCond = '(
                    CASE i.type
                        WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.'
                        THEN
                            COALESCE(SUM(inl_qty.quantity_per_unit * column_settings.quantity), 0)
                        ELSE
                            COALESCE(i.grand_total_quantity, 0)
                    END
                    ) AS value';

                $nlTable = "LEFT JOIN ".EditorBillItemInfoTable::getInstance()->getTableName()." item_info ON item_info.bill_item_id = i.id AND item_info.company_id = ".$this->editorProjectInfo->company_id."
                    LEFT JOIN ".EditorBillItemTypeReferenceTable::getInstance()->getTableName()." inl_qty ON inl_qty.bill_item_info_id = item_info.id AND inl_qty.bill_column_setting_id = column_settings.id";

                $stmt = $this->pdo->prepare("SELECT i.id, ".$sqlFieldCond."
                    FROM " . BillItemTable::getInstance()->getTableName() . " i
                    JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
                    JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " column_settings ON column_settings.project_structure_id = e.project_structure_id
                    ".$nlTable."
                    WHERE i.id IN (" . $implodedItemIds . ")
                    AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND column_settings.deleted_at IS NULL
                    GROUP BY i.id
                    ORDER BY i.root_id, i.priority, i.lft, i.level");

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
                if ( !empty( $implodedItemIds ) )
                {
                    $quantityFieldName = $column->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                    $stmt = $pdo->prepare("SELECT r.bill_item_id, COALESCE(fc.final_value, 0) AS value
                        FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
                        JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
                        WHERE r.bill_item_id IN (" . $implodedItemIds . ") AND r.bill_column_setting_id = " . $column->id . "
                        AND r.include IS TRUE AND fc.column_name = '" . $quantityFieldName . "' AND fc.final_value <> 0
                        AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                    $stmt->execute();

                    $originalQuantities = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

                    $stmt = $pdo->prepare("SELECT info.bill_item_id, COALESCE(r.quantity_per_unit, 0) AS value
                        FROM " . EditorBillItemTypeReferenceTable::getInstance()->getTableName() . " r
                        JOIN " . EditorBillItemInfoTable::getInstance()->getTableName() . " info ON r.bill_item_info_id = info.id AND info.company_id = ".$this->editorProjectInfo->company_id."
                        WHERE info.bill_item_id IN (" . $implodedItemIds . ") AND r.bill_column_setting_id = " . $column->id . "
                        AND quantity_per_unit <> 0");

                    $stmt->execute();

                    $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

                    foreach($originalQuantities as $billItemId => $quantity)
                    {
                        if(array_key_exists($billItemId, $quantities))
                        {
                            $originalQuantities[$billItemId] = $quantities[$billItemId];
                            unset($quantities[$billItemId]);
                        }
                    }

                    if(!empty($quantities))//add bill item qty which does not exists in bill item type ref (mostly not listed items)
                    {
                        $originalQuantities += $quantities;
                    }

                    $result[$column->id] = $originalQuantities;
                }
                else
                {
                    $result[$column->id] = 0;
                }
            }
        }

        return $result;
    }

    public function getLumpSumPercent()
    {
        $stmt = $this->pdo->prepare("SELECT i.id, c.percentage
            FROM " . EditorBillItemLumpSumPercentageTable::getInstance()->getTableName() . " c
            JOIN " . EditorBillItemInfoTable::getInstance()->getTableName() . " info ON c.bill_item_info_id = info.id AND info.company_id = ".$this->editorProjectInfo->company_id."
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON (info.bill_item_id = i.id AND i.type = " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . ")
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            WHERE e.project_structure_id = " . $this->bill->id . "
            AND (i.deleted_at_project_revision_id < " . $this->projectRevision->id . " OR (i.deleted_at_project_revision_id != " . $this->projectRevision->id . " OR i.deleted_at_project_revision_id IS NULL)) AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $lumpSumpPercents = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        return $lumpSumpPercents;
    }

    public function getRatesAfterMarkup()
    {
        $pdo  = $this->pdo;
        $bill = $this->bill;

        $stmt = $pdo->prepare("SELECT i.id, COALESCE(fc.final_value, 0) AS value
            FROM " . EditorBillItemFormulatedColumnTable::getInstance()->getTableName() . " fc
            JOIN " . EditorBillItemInfoTable::getInstance()->getTableName() . " info ON fc.relation_id = info.id AND info.company_id = ".$this->editorProjectInfo->company_id."
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON info.bill_item_id = i.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            WHERE e.project_structure_id = " . $bill->id . "
            AND fc.deleted_at IS NULL AND (i.deleted_at_project_revision_id < " . $this->projectRevision->id . " OR (i.deleted_at_project_revision_id != " . $this->projectRevision->id . " OR i.deleted_at_project_revision_id IS NULL))
            AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ( $records as $itemId => $value )
        {
            $records[$itemId] = number_format($value, 2, '.', '');
        }

        return $records;
    }
}
