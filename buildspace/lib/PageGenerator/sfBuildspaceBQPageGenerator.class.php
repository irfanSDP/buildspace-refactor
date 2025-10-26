<?php

class sfBuildspaceBQPageGenerator extends sfBuildspaceBQMasterFunction
{
    protected $bill;
    protected $pdo;
    protected $fontType;
    protected $fontSize;
    protected $headSettings;
    protected $numberOfBillColumns;
    protected $billElement;
    protected $dontSetLumpSumPercentageAsQtyPerUnit = false;
    protected $publishToPostContract = false;

    public $printGrandTotalQty;

    public function __construct(ProjectStructure $bill, BillElement $element=null)
    {
        $this->bill                     = $bill;
        $this->billElement              = $element;

        $this->pdo                      = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->originalBillRevision     = ProjectRevisionTable::getOriginalProjectRevisionFromBillId($bill->root_id);
        $this->currentBillRevision      = ProjectRevisionTable::getLatestProjectRevisionFromBillId($bill->root_id);

        $this->billStructure            = $this->queryBillStructure();

        //Generate Element Order By Bill
        $this->elementsOrder            = $this->getElementOrder();

        // get bill's printout setting
        $printSettings                  = $this->getPrintSetting();
        $this->printSettings            = $printSettings;

        $this->printGrandTotalQty       = $printSettings['layoutSetting']['printGrandTotalQty'];

        $this->dontSetLumpSumPercentageAsQtyPerUnit = false;

        $project                        = $bill->root_id == $bill->id ? $bill : ProjectStructureTable::getInstance()->find($bill->root_id);
        $numberOfBillColumns            = $this->getBillColumnSettingCount();

        $this->fontType                 = self::setFontType($printSettings['layoutSetting']['fontTypeName']);
        $this->fontSize                 = $printSettings['layoutSetting']['fontSize'];

        $this->headSettings             = $this->printSettings['headSettings'];
        $this->currency                 = $project->MainInformation->Currency;
        $this->numberOfBillColumns      = $numberOfBillColumns;
        $this->orientation              = ($numberOfBillColumns > 1 and !$this->printGrandTotalQty) ? self::ORIENTATION_LANDSCAPE : self::ORIENTATION_PORTRAIT;
        $this->pageFormat               = $this->setPageFormat(self::PAGE_FORMAT_A4);

        $this->setMaxCharactersPerLine($this->printSettings['layoutSetting']['printAmountOnly']);
        $this->setSummaryMaxCharactersPerLine();
    }

    protected function updateBillReferences()
    {
        if(count($this->billReferenceToUpdate) > 0)
        {
            $charCaseStatement = '';
            $elementNoCaseStatement = '';
            $pageNoCaseStatement = '';
            $itemIds = [];

            foreach ($this->billReferenceToUpdate as $itemId => $value)
            {
                $charCaseStatement.=" WHEN ".$itemId." THEN ('".$value['char']."')";

                $elementNoCaseStatement.=" WHEN ".$itemId." THEN (".$value['elementNo'].")";

                $pageNoCaseStatement.=" WHEN ".$itemId." THEN (".$value['pageCount'].")";

                array_push($itemIds, $itemId);
            }

            $stmt = $this->pdo->prepare("UPDATE ".BillItemTable::getInstance()->getTableName()." SET
            bill_ref_char = (CASE id".$charCaseStatement." END),
            bill_ref_element_no = (CASE id".$elementNoCaseStatement." END),
            bill_ref_page_no = (CASE id".$pageNoCaseStatement." END) WHERE
            id IN (".implode(',', $itemIds).")");

            $stmt->execute();
        }

        if(count($this->itemIdsToRemoveReference) > 0)
        {
            $stmt = $this->pdo->prepare("UPDATE ".BillItemTable::getInstance()->getTableName()." SET
            bill_ref_char = NULL, bill_ref_element_no = NULL, bill_ref_page_no = NULL
            WHERE id IN (".implode(',', $this->itemIdsToRemoveReference).")");

            $stmt->execute();
        }
    }

    protected function setSummaryMaxCharactersPerLine()
    {
        switch($this->fontSize)
        {
            case 10 :
                $this->SUMMARY_MAX_CHARACTERS = 58;
                break;
            case 11:
                $this->SUMMARY_MAX_CHARACTERS = 49;
                break;
            case 12:
                $this->SUMMARY_MAX_CHARACTERS = 48;
                break;
            default:
                $this->SUMMARY_MAX_CHARACTERS = 48;
                break;
        }
    }

    public function getSummaryMaxRows()
    {
        $pageFormat = $this->getPageFormat();

        switch($pageFormat['page_format'])
        {
            case self::PAGE_FORMAT_A4:
                switch($this->numberOfBillColumns)
                {
                    case 3:
                        $maxRows = 47;
                        break;
                    case 4:
                        $maxRows = 53;
                        break;
                    case 5:
                        $maxRows = 60;
                        break;
                    default:
                        $maxRows = 46;
                }
                $maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 70 : $maxRows;
                break;
            default:
                switch($this->numberOfBillColumns)
                {
                    case 3:
                        $maxRows = 47;
                        break;
                    case 4:
                        $maxRows = 53;
                        break;
                    case 5:
                        $maxRows = 60;
                        break;
                    default:
                        $maxRows = 46;
                }
                $maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 70 : $maxRows;
        }

        return $maxRows;
    }

    protected function setMaxCharactersPerLine($printAmountOnly = false)
    {
        if ( $printAmountOnly )
        {
            switch($this->fontSize)
            {
                case 10 :
                    $this->MAX_CHARACTERS = 73;
                    break;
                case 11:
                    $this->MAX_CHARACTERS = 63;
                    break;
                case 12:
                    $this->MAX_CHARACTERS = 63;
                    break;
                default:
                    $this->MAX_CHARACTERS = 63;
                    break;

            }
        }
        else
        {
            switch($this->fontSize)
            {
                case 10 :
                    $this->MAX_CHARACTERS = 51;
                    break;
                case 11:
                    $this->MAX_CHARACTERS = 44;
                    break;
                case 12:
                    $this->MAX_CHARACTERS = 43;
                    break;
                default:
                    $this->MAX_CHARACTERS = 43;
                    break;

            }
        }
    }

    public function getMaxRows()
    {
        $pageFormat = $this->getPageFormat();

        switch($pageFormat['page_format'])
        {
            case self::PAGE_FORMAT_A4:
                switch($this->numberOfBillColumns)
                {
                    case 3:
                        $maxRows = 36;
                        break;
                    case 4:
                        $maxRows = 43;
                        break;
                    case 5:
                        $maxRows = 51;
                        break;
                    default:
                        $maxRows = 36;
                }
                $maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 56 : $maxRows;
                break;
            default:
                switch($this->numberOfBillColumns)
                {
                    case 3:
                        $maxRows = 36;
                        break;
                    case 4:
                        $maxRows = 43;
                        break;
                    case 5:
                        $maxRows = 51;
                        break;
                    default:
                        $maxRows = 36;
                }
                $maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 56 : $maxRows;
        }

        return $maxRows;
    }

    protected function setPageFormat($format)
    {
        switch(strtoupper($format))
        {
            /*
             *  For now we only handle A4 format. If there's necessity to handle other page
             * format we need to add to this method
             */
            case 'A4' :
                $width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
                $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
                $pf = array(
                    'page_format' => self::PAGE_FORMAT_A4,
                    'minimum-font-size' => $this->fontSize,
                    'width' => $width,
                    'height' => $height,
                    'pdf_margin_top' => 8,
                    'pdf_margin_right' => 4,
                    'pdf_margin_bottom' => 1,
                    'pdf_margin_left' => 24
                );
                break;
            // DEFAULT ISO A4
            default:
                $width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
                $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
                $pf = array(
                    'page_format' => self::PAGE_FORMAT_A4,
                    'minimum-font-size' => $this->fontSize,
                    'width' => $width,
                    'height' => $height,
                    'pdf_margin_top' => 8,
                    'pdf_margin_right' => 4,
                    'pdf_margin_bottom' => 3,
                    'pdf_margin_left' => 24
                );
        }
        return $pf;
    }

    protected function queryBillStructure()
    {
        $billStructure = [];

        $elementSqlPart = $this->billElement instanceof BillElement ? "AND e.id = ".$this->billElement->id : null;

        $stmt = $this->pdo->prepare("SELECT e.id, e.description FROM ".BillElementTable::getInstance()->getTableName()." e
        WHERE e.project_structure_id = ".$this->bill->id." ".$elementSqlPart." AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $result = [
                'id'          => $element['id'],
                'description' => $element['description'],
                'items'       => []
            ];

            $stmt = $this->pdo->prepare("SELECT c.id, c.description, c.element_id, c.type,
                COALESCE(c.grand_total_after_markup, 0) AS grand_total_after_markup, c.bill_ref_element_no,
                c.bill_ref_page_no, c.bill_ref_char, c.uom_id, c.lft, c.rgt, c.root_id, c.level, uom.symbol AS uom
                FROM ".BillItemTable::getInstance()->getTableName()." c
                LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE c.element_id = ".$element['id']." AND c.project_revision_deleted_at IS NULL
                AND c.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level");

            $stmt->execute();

            $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result['items'] = $billItems;

            array_push($billStructure, $result);

            unset($element, $billItems);
        }

        return $billStructure;
    }

    protected function getItemQuantities()
    {
        $implodedItemIds = null;
        $result = [];

        foreach($this->billStructure as $element)
        {
            if(count($element['items']) == 0)
                continue;//we skip element with empty items

            $itemIds = Utilities::arrayValueRecursive('id', $element['items']);

            if(is_array($itemIds))
            {
                $implodedItemIds .= implode(',', $itemIds);
                $implodedItemIds .= ",";
            }

            unset($element, $itemIds);
        }

        $implodedItemIds = rtrim($implodedItemIds, ",");

        if($this->printGrandTotalQty)
        {
            if ( ! empty($implodedItemIds) )
            {
                $stmt = $this->pdo->prepare("SELECT i.id, i.grand_total_quantity AS value FROM ".BillItemTable::getInstance()->getTableName()." i
                WHERE i.id IN (".$implodedItemIds.") AND i.grand_total_quantity <> 0 AND i.deleted_at IS NULL");

                $stmt->execute();

                $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                $result = $quantities;

                unset($quantities);
            }
        }
        else
        {
            foreach($this->bill->BillColumnSettings->toArray() as $column)
            {
                $quantityFieldName = $column['use_original_quantity'] ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                if ( ! empty($implodedItemIds) )
                {
                    $stmt = $this->pdo->prepare("SELECT r.bill_item_id, COALESCE(fc.final_value, 0) AS value FROM ".BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName()." fc
                    JOIN ".BillItemTypeReferenceTable::getInstance()->getTableName()." r ON fc.relation_id = r.id
                    WHERE r.bill_item_id IN (".$implodedItemIds.") AND r.bill_column_setting_id = ".$column['id']."
                    AND r.include IS TRUE AND fc.column_name = '".$quantityFieldName."' AND fc.final_value <> 0
                    AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                    $stmt->execute();

                    $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                    $result[$column['id']] = $quantities;

                    unset($quantities);
                }
                else
                {
                    $result[$column['id']] = 0;
                }
            }
        }

        return $result;
    }

    protected function getLumpSumPercent()
    {
        $stmt = $this->pdo->prepare("SELECT i.id, c.percentage FROM ".BillItemLumpSumPercentageTable::getInstance()->getTableName()." c
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON (c.bill_item_id = i.id AND i.type = ".BillItem::TYPE_ITEM_LUMP_SUM_PERCENT.")
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            WHERE e.project_structure_id = ".$this->bill->id."
            AND c.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
    }

    protected function getRatesAfterMarkup()
    {
        $elementMarkupResults = [];
        $rateInfoColumns      = [];

        if($this->bill->BillMarkupSetting->element_markup_enabled)
        {
            $stmt = $this->pdo->prepare("SELECT e.id, COALESCE(c.final_value, 0) as value FROM ".BillElementFormulatedColumnTable::getInstance()->getTableName()." c
                JOIN ".BillElementTable::getInstance()->getTableName()." e ON c.relation_id = e.id
                WHERE e.project_structure_id = ".$this->bill->id." AND c.column_name = '".BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE."'
                AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

            $stmt->execute();

            $elementMarkupResults = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
        }

        $stmt = $this->pdo->prepare("SELECT c.relation_id, i.element_id, c.column_name, COALESCE(c.final_value, 0) AS value FROM ".BillItemFormulatedColumnTable::getInstance()->getTableName()." c
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON c.relation_id = i.id
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            WHERE e.project_structure_id = ".$this->bill->id." AND (c.column_name = '".BillItem::FORMULATED_COLUMN_RATE."' OR c.column_name = '".BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE."')
            AND c.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($columns as $column)
        {
            $markupSettingsInfo = array(
                'bill_markup_enabled'       => $this->bill->BillMarkupSetting->bill_markup_enabled,
                'bill_markup_percentage'    => $this->bill->BillMarkupSetting->bill_markup_percentage,
                'element_markup_enabled'    => $this->bill->BillMarkupSetting->element_markup_enabled,
                'element_markup_percentage' => array_key_exists($column['element_id'], $elementMarkupResults) ? $elementMarkupResults[$column['element_id']][0] : 0,
                'item_markup_enabled'       => $this->bill->BillMarkupSetting->item_markup_enabled,
                'rounding_type'             => $this->bill->BillMarkupSetting->rounding_type
            );

            $rateInfoColumns[$column['relation_id']]['markup_setting_info'] = $markupSettingsInfo;
            $rateInfoColumns[$column['relation_id']][$column['column_name']] = $column['value'];

            unset($column);
        }

        $result = [];

        foreach($rateInfoColumns as $itemId => $column)
        {
            $markupPercentage = array_key_exists(BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE, $column) ? $column[BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE] : 0;
            $rate = array_key_exists(BillItem::FORMULATED_COLUMN_RATE, $column) ? $column[BillItem::FORMULATED_COLUMN_RATE] : 0;

            $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $markupPercentage, $column['markup_setting_info']);

            $result[$itemId] = number_format($rateAfterMarkup, 2, '.', '');
        }

        unset($columns, $rateInfoColumns);

        return $result;
    }

    public function generatePages()
    {
        $billStructure         = $this->billStructure;
        $billColumnSettings    = $this->getBillColumnSettings();
        $billElement           = $this->billElement;
        $pages                 = [];
        $summaryPage           = [];
        $pageNumberDescription = 'Page No. ';
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

        if($billElement instanceof BillElement)
        {
            $stmt = $this->pdo->prepare("SELECT e.id FROM ".BillElementTable::getInstance()->getTableName()." e
            WHERE e.project_structure_id = ".$this->bill->id." AND e.deleted_at IS NULL ORDER BY e.priority");

            $stmt->execute();

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
            $elementCount = [];

            foreach($elements as $element)
            {
                $elementCount[$element['id']] = $count++;

                unset($element);
            }

            unset($elements);
        }
        else
        {
            $elementCount = 1;
        }

        foreach($billStructure as $element)
        {
            $itemPages = [];
            $collectionPages = [];
            $lastRow = [];

            $elemCount = $billElement instanceof BillElement ? $elementCount[$element['id']] : $elementCount;

            $elementInfo = [
                'id'            => $element['id'],
                'description'   => $element['description'],
                'element_count' => $elemCount
            ];

            $this->generateBillItemPages($element['items'], $billColumnSettings, $elementInfo, 1, [], $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities);

            $this->generateCollectionPages($elementInfo, $billColumnSettings, $itemPages, $pageNumberDescription, count($itemPages)+1, count($itemPages), $collectionPages, $totalAmount);

            $lastCollectionPage = end($collectionPages);

            foreach($lastCollectionPage as $key => $row)
            {
                if(is_integer($key))
                {
                    if(is_null($row[0]) or strlen($row[0]) == 0 or $row[1] == self::ROW_TYPE_ELEMENT or $row[1] == self::ROW_TYPE_BLANK)
                        continue;

                    $lastRow = array(
                        'description' => str_replace($pageNumberDescription, "", $row[0]),
                        'amount' => $lastCollectionPage['total_amount'],
                        'page_count' => $elemCount.'/'.$lastCollectionPage['page_count']
                    );
                }
                else
                {
                }
            }

            $summaryPage[$element['id']] = [
                'description'          => $element['description'],
                'last_collection_page' => $lastRow
            ];

            $page = [
                'description'      => $element['description'],
                'element_count'    => $elemCount,
                'item_pages'       => SplFixedArray::fromArray($itemPages),
                'collection_pages' => $collectionPages
            ];

            $pages[$element['id']] = $page;

            if(!$billElement instanceof BillElement)
                $elementCount++;

            unset($itemPages, $collectionPages, $element);
        }

        $summaryPages = [];
        $this->generateSummaryPage($summaryPage, $billColumnSettings, 1, $summaryPages, $totalPerUnit);

        // if current revision is original bill then only update the bill ref
        if ( $this->currentBillRevision->version == ProjectRevision::ORIGINAL_BILL_VERSION )
        {
            //Update Bill References
            $this->updateBillReferences();
        }

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
    protected function generateSummaryPage($rows, $billColumnSettings, $pageCount, &$summaryPages, Array $totalPerUnit, $continuePage = false)
    {
        $summaryPages[$pageCount] = [];

        $maxRows = $this->getSummaryMaxRows() - 16;

        $blankRow    = new SplFixedArray(4);
        $blankRow[0] = null;
        $blankRow[1] = self::ROW_TYPE_BLANK;
        $blankRow[2] = null;
        $blankRow[3] = null;

        //blank row
        array_push($summaryPages[$pageCount], $blankRow);//starts with a blank row

        $contdPrefix       = ! $continuePage ? null : " {$this->printSettings['layoutSetting']['contdPrefix']}";
        $summaryPageHeader = new SplFixedArray(4);

        if ( $this->printSettings['layoutSetting']['printContdEndDesc'] )
        {
            $summaryPageHeader[0] = $this->printSettings['phrase']['summaryInGridPrefix'].$contdPrefix;
        }
        else
        {
            $summaryPageHeader[0] = $contdPrefix.' '.$this->printSettings['phrase']['summaryInGridPrefix'];
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
            $bringForwardHeader[0] = trim(self::getSummaryNextPageBringForwardPrefix().' '.self::getSummaryPageNumberingPrefix($pageCount-1));
            $bringForwardHeader[1] = self::ROW_TYPE_SUMMARY_PAGE_TITLE;
            $bringForwardHeader[2] = $totalPerUnit;
            $bringForwardHeader[3] = null;

            //blank row
            array_push($summaryPages[$pageCount], $bringForwardHeader);//starts with a blank row
        }

        //blank row
        array_push($summaryPages[$pageCount], $blankRow);//starts with a blank row

        $rowCount = ( $continuePage ) ? 5 : 3;

        foreach($rows as $idx => $row)
        {
            $occupiedRows = Utilities::justify($row['description'], $this->SUMMARY_MAX_CHARACTERS);
            $rowCount += $occupiedRows->count();

            if($rowCount <= $maxRows)
            {
                foreach($occupiedRows as $key => $occupiedRow)
                {
                    $elementRow    = new SplFixedArray(4);
                    $elementRow[0] = $occupiedRow;
                    $elementRow[1] = self::ROW_TYPE_ELEMENT;
                    $elementRow[2] = null;
                    $elementRow[3] = null;

                    if ($key == count($occupiedRows)-1)
                    {
                        $totalAmount = array();

                        if($this->printGrandTotalQty)
                        {
                            $totalAmount[0] = is_array($row['last_collection_page']['amount']) ? 0 : self::gridCurrencyRoundingFormat($row['last_collection_page']['amount']);
                            $totalPerUnit[0] += $totalAmount[0];
                        }
                        else
                        {
                            foreach($billColumnSettings as $billColumnSetting)
                            {
                                $totalAmount[$billColumnSetting['id']] = self::gridCurrencyRoundingFormat(array_key_exists($billColumnSetting['id'], $row['last_collection_page']['amount']) ? $row['last_collection_page']['amount'][$billColumnSetting['id']] : 0);
                                $totalPerUnit[$billColumnSetting['id']] += $totalAmount[$billColumnSetting['id']];
                            }
                        }

                        $elementRow[2] = $totalAmount;
                        $elementRow[3] = trim($this->printSettings['layoutSetting']['pageNoPrefix'].$row['last_collection_page']['page_count']);
                    }

                    array_push($summaryPages[$pageCount], $elementRow);
                }

                // sum all amount and we need to carry forward total amount to the next page
                if($this->printGrandTotalQty)
                {
                    $summaryPages[$pageCount]['total_per_unit'][0] = self::gridCurrencyRoundingFormat(array_key_exists(0, $totalPerUnit) ? $totalPerUnit[0] : 0);
                }
                else
                {
                    foreach($billColumnSettings as $billColumnSetting)
                    {
                        $summaryPages[$pageCount]['total_per_unit'][$billColumnSetting['id']] = self::gridCurrencyRoundingFormat(array_key_exists($billColumnSetting['id'], $totalPerUnit) ? $totalPerUnit[$billColumnSetting['id']] : 0);
                    }
                }

                unset($rows[$idx], $row);

                //blank row
                array_push($summaryPages[$pageCount], $blankRow);

                $rowCount++;//plus one blank row;
            }
            else
            {
                $pageCount++;
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
        $collectionPages[$pageCount]                 = [];
        $collectionPages[$pageCount]['total_amount'] = [];
        $pageKey                                     = 1;
        $maxRows                                     = $this->getMaxRows()-4;//less 4 rows for collection page

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
            $occupiedRows = Utilities::justify($elementInfo['description'].$contdPrefix, $this->MAX_CHARACTERS);
        }
        else
        {
            $occupiedRows = Utilities::justify($contdPrefix.' '.$elementInfo['description'], $this->MAX_CHARACTERS);
        }

        foreach($occupiedRows as $occupiedRow)
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
            $collectionTitle[0] = $this->printSettings['phrase']['collectionInGridPrefix'].$contdPrefix;
        }
        else
        {
            $collectionTitle[0] = $contdPrefix.' '.$this->printSettings['phrase']['collectionInGridPrefix'];
        }

        $collectionTitle[1] = self::ROW_TYPE_COLLECTION_TITLE;
        $collectionTitle[2] = null;

        array_push($collectionPages[$pageCount], $collectionTitle);

        //blank row
        array_push($collectionPages[$pageCount], $blankRow);

        if ( $continuePage )
        {
            $lastPageCount = $pageCount - 1;

            //Collection total from previous page
            $fromPreviousPage    = new SplFixedArray(3);
            $fromPreviousPage[0] = trim(self::getCollectionNextPageBringForwardPrefix().' '.trim(self::getPageNoPrefix()."{$elementInfo['element_count']}/{$lastPageCount}"));
            $fromPreviousPage[1] = self::ROW_TYPE_COLLECTION_TITLE;
            $fromPreviousPage[2] = $totalAmount;

            array_push($collectionPages[$pageCount], $fromPreviousPage);

            //blank row
            array_push($collectionPages[$pageCount], $blankRow);

            $rowCount += $occupiedRows->count() + 3 + 2;//plus one blank row & collection title
        }
        else
        {
            $rowCount += $occupiedRows->count() + 3;//plus one blank row & collection title
        }

        foreach($itemPages as $pageKey => $itemPage)
        {
            $occupiedRows = Utilities::justify($pageNumberDescription." ".self::getPageNoPrefix().$elementInfo['element_count']."/".$pageKey, $this->MAX_CHARACTERS);
            $rowCount += $occupiedRows->count();

            if($rowCount <= $maxRows)
            {
                foreach($occupiedRows as $key => $occupiedRow)
                {
                    $row = new SplFixedArray(3);
                    $row[0] = $occupiedRow;
                    $row[1] = null;
                    $row[2] = $this->printGrandTotalQty ? null : new SplFixedArray(count($billColumnSettings));

                    if($key+1 == count($occupiedRows))
                    {
                        if($this->printGrandTotalQty)
                        {
                            $amount = 0;
                            foreach($itemPage as $itemRow)
                            {
                                if($itemRow[self::ROW_BILL_ITEM_ID] && $itemRow[self::ROW_BILL_ITEM_ID] > 0 && $itemRow[self::ROW_BILL_ITEM_RATE] && $itemRow[self::ROW_BILL_ITEM_RATE] != 0)
                                {
                                    if($itemRow[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM or $itemRow[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT or $itemRow[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE )
                                    {
                                        $itemAmount = self::gridCurrencyRoundingFormat((float) $itemRow[self::ROW_BILL_ITEM_RATE]);
                                        $amount += $itemAmount;
                                    }
                                    else
                                    {
                                        $itemAmount = self::gridCurrencyRoundingFormat((float)$itemRow[self::ROW_BILL_ITEM_RATE] * $itemRow[self::ROW_BILL_ITEM_QTY_PER_UNIT]);
                                        $amount += $itemAmount;
                                    }
                                }

                                unset($itemRow);
                            }

                            $row[2] = $amount;

                            $totalAmount[0] += $amount;
                        }
                        else
                        {
                            foreach($billColumnSettings as $idx => $billColumnSetting)
                            {
                                $amount = 0;
                                foreach($itemPage as $itemRow)
                                {
                                    if($itemRow[self::ROW_BILL_ITEM_ID] && $itemRow[self::ROW_BILL_ITEM_ID] > 0 && $itemRow[self::ROW_BILL_ITEM_RATE] && $itemRow[self::ROW_BILL_ITEM_RATE] != 0)
                                    {
                                        if ( $itemRow[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT OR $itemRow[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE )
                                        {
                                            $itemAmount = self::gridCurrencyRoundingFormat( (float) $itemRow[self::ROW_BILL_ITEM_RATE] );
                                            $amount += $itemAmount;
                                        }
                                        else
                                        {
                                            $itemQuantity = is_array($itemRow[self::ROW_BILL_ITEM_QTY_PER_UNIT]) && array_key_exists($billColumnSetting['id'], $itemRow[self::ROW_BILL_ITEM_QTY_PER_UNIT]) ? $itemRow[self::ROW_BILL_ITEM_QTY_PER_UNIT][$billColumnSetting['id']] : 0;
                                            $itemAmount = self::gridCurrencyRoundingFormat((float)$itemRow[self::ROW_BILL_ITEM_RATE] * $itemQuantity);
                                            $amount += $itemAmount;
                                        }
                                    }
                                    else
                                    {
                                        continue;
                                    }

                                    unset($itemRow);
                                }

                                $billColumnAmount = new SplFixedArray(2);
                                $billColumnAmount[0] = $billColumnSetting['id'];//key 0 is bill column setting id
                                $billColumnAmount[1] = $amount;//key 1 is amount
                                $row[2][$idx] = $billColumnAmount;

                                $totalAmount[$billColumnSetting['id']] += $amount;

                                unset($billColumnSetting);
                            }
                        }
                    }

                    array_push($collectionPages[$pageCount], $row);

                    unset($occupiedRow);
                }

                //blank row
                $collectionPages[$pageCount]['page_count'] = $pageCount;
                array_push($collectionPages[$pageCount], $blankRow);

                // sum all amount and we need to carry forward total amount to the next page
                if($this->printGrandTotalQty)
                {
                    $collectionPages[$pageCount]['total_amount'] = self::gridCurrencyRoundingFormat(array_key_exists(0, $totalAmount) ? $totalAmount[0] : 0);
                }
                else
                {
                    foreach($billColumnSettings as $billColumnSetting)
                    {
                        $collectionPages[$pageCount]['total_amount'][$billColumnSetting['id']] = self::gridCurrencyRoundingFormat(array_key_exists($billColumnSetting['id'], $totalAmount) ? $totalAmount[$billColumnSetting['id']] : 0);
                    }
                }

                $rowCount++;//plus one blank row;

                unset($itemPages[$pageKey], $row);
            }
            else
            {
                $pageCount++;
                $this->generateCollectionPages($elementInfo, $billColumnSettings, $itemPages, $pageNumberDescription, $pageCount, $totalItemPages, $collectionPages, $totalAmount, true);
                break;
            }
        }

        unset($itemPages);

        if($pageKey == $totalItemPages)//add a blank row at end of collection page
        {
            //blank row
            array_push($collectionPages[$pageCount], $blankRow);
        }
    }

    /*
     * We use SplFixedArray as data structure to boost performance. Since associative array cannot be used in SplFixedArray, we have to use indexes
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
     * 8 - include (bill column types)
     */
    protected function generateBillItemPages(Array $billItems, Array $billColumnSettings, $elementInfo, $pageCount, $ancestors, &$itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, $newPage = false)
    {
        $itemPages[$pageCount] = [];
        $layoutSettings        = $this->printSettings['layoutSetting'];
        $maxRows               = $this->getMaxRows();

        //starts with a blank row
        $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
        $rowCount = 1;

        /*
         * Always display element description at start of every page.
         */
        $descriptionCont = $pageCount > 1 ? $layoutSettings['contdPrefix'] : null;

        $occupiedRows = $this->addElementDescription($elementInfo, $pageCount, $itemPages, $descriptionCont);

        $rowCount += $occupiedRows->count();

        //blank row
        $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
        $rowCount++;

        $elementRowCount = $rowCount;

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

                // Add item to ancestors.
                $ancestors[$billItem['level']] = $row;

                // Remove all items after current item in ancestors.
                $ancestors = array_splice($ancestors, 0, $billItem['level']+1);

                unset($row);
            }
            
            /*
             * To get all ancestors from previous page so we can display it as continued headers
             * before we print out the item
             */
            if($pageCount > 1 and $itemIndex == 1 and $billItem['level'] != 0 )
            {
                // if detected current looped item is same level with ancestor, then overwrite it
                $this->unsetHeadThatIsSameLevelWithItemOnNextPage($ancestors, $billItem);

                foreach($ancestors as $ancestor)
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
                $pageCount++;

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
                    $pageCount++;

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

                foreach($occupiedRows as $key => $occupiedRow)
                {
                    if($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID)
                    {
                        $counterIndex++;
                    }

                    $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                    $row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID && !($billItem['isContinuedDescription'] ?? false)) ? Utilities::generateCharFromNumber($counterIndex, $this->printSettings['layoutSetting']['includeIandO']) : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = (!empty($occupiedRow) && !in_array($billItem['type'], [BillItem::TYPE_ITEM_HTML_EDITOR, BillItem::TYPE_NOID]) ) ? Utilities::inlineJustify($occupiedRow, $this->MAX_CHARACTERS) : $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL] = $billItem['level'];
                    $row[self::ROW_BILL_ITEM_TYPE]  = $billItem['type'];
                    $row[self::ROW_BILL_ITEM_LEFT]  = $billItem['lft'];
                    $row[self::ROW_BILL_ITEM_RIGHT] = $billItem['rgt'];

                    //Generate Bill Ref
                    if($key+1 == $occupiedRows->count())
                    {
                        $this->generateBillReference($billItem, $counterIndex, $pageCount);
                    }

                    if($this->saveOriginalBillInformation && !($billItem['isContinuedDescription'] ?? false) && ($key + 1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N))
                    {
                        $this->savePageItemReference($elementInfo, $pageCount, $key, $occupiedRows, $billItem);
                    }

                    /*
                     * When publishing to Post Contract, All finalized items will be cloned into post_contract_bill_item_rates including HEADER and NOID ITEM. In sfBillReferenceReset class
                     * it will traverse through the item pages and checks for items with not null rate column. All items with the not null rate column(including rate with value 0) will be
                     * cloned into post_contract_bill_item_rates. This logic is different from normal page generator logic where it will only set the rate to a non header items.
                     */
                    $allowHeaderItem = ($this->publishToPostContract) ? true : ($billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N);
                    // If last row of certain type of items.
                    if($isLastChunk && $key+1 == $occupiedRows->count() && $allowHeaderItem)
                    {
                        $row[self::ROW_BILL_ITEM_ID]   = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom'];
                        $row[self::ROW_BILL_ITEM_RATE] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $ratesAfterMarkup) ? $ratesAfterMarkup[$billItem['id']] : 0);

                        $quantityPerUnit = [];
                        $includeStatus = null;

                        if($this->printGrandTotalQty)
                        {
                            /*
                             * this is actually not a quantity per unit but grand total quantity instead. But we just assign it to the same variable name so it can be used
                             * for case where print grand total qty is disabled.
                             */
                            $quantityPerUnit = array_key_exists($billItem['id'], $itemQuantities) ? $itemQuantities[$billItem['id']][0] : 0;

                            $row[self::ROW_BILL_ITEM_INCLUDE] = $includeStatus;

                            if ( $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT && !$this->dontSetLumpSumPercentageAsQtyPerUnit)
                            {
                                if(isset($this->tenderCompany))
                                {
                                    $quantityPerUnit = array_key_exists($billItem['id'], $lumpSumPercents) ? $lumpSumPercents[$billItem['id']][$this->tenderCompany->id] : 0;
                                }
                                else
                                {
                                    $quantityPerUnit = array_key_exists($billItem['id'], $lumpSumPercents) ? $lumpSumPercents[$billItem['id']][0] : 0;
                                }
                            }

                            if($billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM or $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT or $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE)
                            {
                                $row[self::ROW_BILL_ITEM_RATE] = array_key_exists('grand_total_after_markup', $billItem) ? self::gridCurrencyRoundingFormat($billItem['grand_total_after_markup']) : 0;
                            }
                        }
                        else
                        {
                            foreach($billColumnSettings as $billColumnSetting)
                            {
                                $itemQuantity = (array_key_exists($billColumnSetting['id'], $itemQuantities) && array_key_exists($billItem['id'], $itemQuantities[$billColumnSetting['id']])) ? $itemQuantities[$billColumnSetting['id']][$billItem['id']][0] : 0;

                                $quantityPerUnit[$billColumnSetting['id']] = $itemQuantity;

                                $includeStatus[$billColumnSetting['id']] = array_key_exists($billItem['id'], $itemIncludeStatus[$billColumnSetting['id']]) ? $itemIncludeStatus[$billColumnSetting['id']][$billItem['id']] : true;

                                if ( $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT && !$this->dontSetLumpSumPercentageAsQtyPerUnit)
                                {
                                    if(isset($this->tenderCompany))
                                    {
                                        $quantityPerUnit[$billColumnSetting['id']] = array_key_exists($billItem['id'], $lumpSumPercents) ? $lumpSumPercents[$billItem['id']][$this->tenderCompany->id] : 0;
                                    }
                                    else
                                    {
                                        $quantityPerUnit[$billColumnSetting['id']] = array_key_exists($billItem['id'], $lumpSumPercents) ? $lumpSumPercents[$billItem['id']][0] : 0;
                                    }
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
                        $row[self::ROW_BILL_ITEM_INCLUDE],
                        $row[self::ROW_BILL_ITEM_LEFT],
                        $row[self::ROW_BILL_ITEM_RIGHT]
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
                $rowCount++;

                $itemIndex++;
                $newPage = false;

                unset($billItems[$x], $occupiedRows);

                reset($billItems);
            }
            else // If can't fit in remaining space of current page.
            {
                reset($billItems);
                $pageCount++;

                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                break;
            }
        }
    }

    public function getPrintSetting()
    {
        return BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($this->bill->BillLayoutSetting->id, TRUE);
    }

    public function getBillColumnSettingCount()
    {
        return $this->bill->getBillColumnSettings()->count();
    }

    public function getBillColumnSettings()
    {
        return $this->bill->BillColumnSettings->toArray();
    }

    protected function getAncestorRowCount($ancestors, $billItem, $pageCount, $layoutSettings)
    {
        $numberOfRows = 0;

        foreach($ancestors as $ancestor)
        {
            if ( $ancestor[self::ROW_BILL_ITEM_ID] == $billItem['id'] ) continue;

            $descriptionCont = $pageCount > 1 ? $layoutSettings['contdPrefix'] : null;

            if ( $this->printSettings['layoutSetting']['printContdEndDesc'] )
            {
                $occupiedRows = Utilities::justify($ancestor[self::ROW_BILL_ITEM_DESCRIPTION]." ".$descriptionCont, $this->MAX_CHARACTERS);
            }
            else
            {
                $occupiedRows = Utilities::justify($descriptionCont." ".$ancestor[self::ROW_BILL_ITEM_DESCRIPTION], $this->MAX_CHARACTERS);
            }

            if ($ancestor[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_HEADER OR $ancestor[self::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_HEADER_N)
            {
                $numberOfRows += (count($occupiedRows));

                //blank row
                $numberOfRows++;

                unset($occupiedRows, $ancestor);
            }
        }

        return $numberOfRows;
    }

    /**
     * @param $elementInfo
     * @param $pageCount
     * @param $key
     * @param $occupiedRows
     * @param $billItem
     */
    protected function savePageItemReference($elementInfo, $pageCount, $key, $occupiedRows, $billItem)
    {
        $this->pagesContainers[$elementInfo['id']][$pageCount][] = $billItem['id'];
    }
}