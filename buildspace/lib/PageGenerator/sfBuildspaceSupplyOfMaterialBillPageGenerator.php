<?php

class sfBuildspaceSupplyOfMaterialBillPageGenerator extends sfBuildspaceBQMasterFunction {
    const TOTAL_BILL_ITEM_PROPERTY = 16;

    const ROW_BILL_ITEM_ID = 0;
    const ROW_BILL_ITEM_ROW_IDX = 1;
    const ROW_BILL_ITEM_DESCRIPTION = 2;
    const ROW_BILL_ITEM_LEVEL = 3;
    const ROW_BILL_ITEM_TYPE = 4;
    const ROW_BILL_ITEM_UNIT = 5;
    const ROW_BILL_ITEM_SUPPLY_RATE = 6;
    const ROW_BILL_ITEM_QTY_PER_UNIT = 7;
    const ROW_BILL_ITEM_INCLUDE = 8;
    const ROW_BILL_ITEM_ESTIMATED_QTY = 9;
    const ROW_BILL_ITEM_LEFT = 10;
    const ROW_BILL_ITEM_RIGHT = 11;
    const ROW_BILL_ITEM_PERCENTAGE_WASTAGE = 12;
    const ROW_BILL_ITEM_CONTRACTOR_SUPPLY_RATE = 13;
    const ROW_BILL_ITEM_DIFFERENCE = 14;
    const ROW_BILL_ITEM_AMOUNT = 15;

    const TOTAL_COLLECTION_ROW_PROPERTIES = 3;

    const ROW_COLLECTION_DESCRIPTION = 0;
    const ROW_COLLECTION_TYPE = 1;
    const ROW_COLLECTION_AMOUNT = 2;

    public $bill;
    public $pdo;
    public $fontType;
    public $fontSize;
    public $headSettings;
    public $numberOfBillColumns;
    public $defaultRow;

    private $MAX_CHARACTERS_FOR_PROJECT_TITLE = 125;

    private $projectTitleRows = array();

    public function __construct(ProjectStructure $bill, SupplyOfMaterialElement $element=null)
    {
        $this->bill = $bill;
        $this->billElement = $element;

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $this->billStructure = $this->queryBillStructure();

        // Generate Element Order By Bill
        $this->elementsOrder = $this->getElementOrder();

        // get bill's printout setting
        $this->printSettings = $this->getPrintSetting();

        $this->project = $bill->root_id == $bill->id ? $bill : ProjectStructureTable::getInstance()->find($bill->root_id);

        $this->fontType = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->fontSize = $this->printSettings['layoutSetting']['fontSize'];

        $this->headSettings = $this->printSettings['headSettings'];
        $this->currency = $this->project->MainInformation->Currency;
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->pageFormat = $this->setPageFormat(self::PAGE_FORMAT_A4);

        $this->setItemDescriptionMaxCharactersPerLine();

        /*
         * We use SplFixedArray as row data structure. We can't use associative array with SPlFixedArray so we rely on indexes to set values.
         */
        $row = new SplFixedArray(6);
        $row[ self::ROW_BILL_ITEM_ID ] = -1;//id
        $row[ self::ROW_BILL_ITEM_ROW_IDX ] = null;//row index
        $row[ self::ROW_BILL_ITEM_DESCRIPTION ] = null;//description
        $row[ self::ROW_BILL_ITEM_LEVEL ] = 0;//level
        $row[ self::ROW_BILL_ITEM_TYPE ] = self::ROW_TYPE_BLANK;//type
        $row[ self::ROW_BILL_ITEM_UNIT ] = null;//unit

        $this->defaultRow = $row;
    }

    public function setItemDescriptionMaxCharactersPerLine()
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

    public function setProjectTitleMaxCharactersPerLine()
    {
        switch($this->fontSize)
        {
            case 10 :
                $this->MAX_CHARACTERS_FOR_PROJECT_TITLE = 125;
                break;
            case 11:
                $this->MAX_CHARACTERS_FOR_PROJECT_TITLE = 118;
                break;
            case 12:
                $this->MAX_CHARACTERS_FOR_PROJECT_TITLE = 117;
                break;
            default:
                $this->MAX_CHARACTERS_FOR_PROJECT_TITLE = 117;
                break;

        }
    }

    public function getMaxRows()
    {
        return 72 - count($this->projectTitleRows);
    }

    protected function setPageFormat($format)
    {
        return array(
            'page_format'       => self::PAGE_FORMAT_A4,
            'minimum-font-size' => $this->fontSize,
            'width'             => $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800,
            'height'            => $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595,
            'pdf_margin_top'    => 8,
            'pdf_margin_right'  => 4,
            'pdf_margin_bottom' => 3,
            'pdf_margin_left'   => 24
        );
    }

    public function queryBillStructure()
    {
        $billStructure = array();

        $elementSqlPart = $this->billElement instanceof SupplyOfMaterialElement ? "AND e.id = " . $this->billElement->id : null;

        $stmt = $this->pdo->prepare("SELECT e.id, e.description FROM " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = " . $this->bill->id . " " . $elementSqlPart . " AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $result = array(
                'id'          => $element['id'],
                'description' => $element['description'],
                'items'       => array()
            );

            $stmt = $this->pdo->prepare("SELECT c.id, c.description, c.element_id, c.type, c.estimated_qty, c.percentage_of_wastage, c.contractor_supply_rate, c.difference, c.amount,
                COALESCE(c.supply_rate, 0) AS supply_rate, c.uom_id, c.lft, c.rgt, c.root_id, c.level, uom.symbol AS uom
                FROM " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " c
                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE c.element_id = " . $element['id'] . "
                AND c.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level");

            $stmt->execute();

            $result['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            array_push($billStructure, $result);

            unset( $element, $billItems );
        }

        return $billStructure;
    }

    /**
     * Generates all item pages for all elements in the bill.
     * Also returns each page's page number and each page's total amount.
     *
     * @return array
     */
    public function generatePages()
    {
        $billStructure = $this->billStructure;
        $billColumnSettings = array();
        $billElement = $this->billElement;
        $pages = array();
        $elementCount = 1;

        if( $billElement instanceof SupplyOfMaterialElement )
        {
            $stmt = $this->pdo->prepare("SELECT e.id FROM " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e
            WHERE e.project_structure_id = " . $this->bill->id . " AND e.deleted_at IS NULL ORDER BY e.priority");

            $stmt->execute();

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
            $elementCount = array();

            foreach($elements as $element)
            {
                $elementCount[ $element['id'] ] = $count++;

                unset( $element );
            }

            unset( $elements );
        }

        // calculate rows needed by Project's title
        $this->calculateRowNeededByProjectTitle();

        $billPageNumbersAndPageAmounts = array();

        foreach($billStructure as $element)
        {
            $itemPages = array();
            $collectionPages = array();

            $elemCount = $billElement instanceof SupplyOfMaterialElement ? $elementCount[ $element['id'] ] : $elementCount;

            $elementInfo = array(
                'description'   => $element['description'],
                'element_count' => $elemCount
            );

            $this->generateBillItemPages($element['items'], $billColumnSettings, $elementInfo, 1, array(), $itemPages);

            $elementPageNumbersAndPageAmounts = $this->getElementPageNumberAndAmount($elementInfo, $itemPages);

            foreach($elementPageNumbersAndPageAmounts as $pageNumberAndPageAmount)
            {
                array_push($billPageNumbersAndPageAmounts, $pageNumberAndPageAmount);
            }

            $page = array(
                'description'      => $element['description'],
                'element_count'    => $elemCount,
                'item_pages'       => SplFixedArray::fromArray($itemPages),
                'collection_pages' => $collectionPages,
            );

            $pages[ $element['id'] ] = $page;

            if( ! $billElement instanceof SupplyOfMaterialElement )
            {
                $elementCount++;
            }

            unset( $itemPages, $collectionPages, $element );
        }

        if( count($pages) < 1 )
        {
            $this->addEmptyItemPage($pages, 1);
        }

        return array(
            'pages'                         => $pages,
            'billPageNumbersAndPageAmounts' => $billPageNumbersAndPageAmounts,
        );
    }

    /**
     * Get the page number and the total page amount for each page for the current element.
     *
     * @param       $elementInfo
     * @param       $itemPages
     *
     * @return array
     */
    public function getElementPageNumberAndAmount($elementInfo, $itemPages)
    {
        $elementPageNumbersAndTheirAmounts = array();
        foreach($itemPages as $pageKey => $itemPage)
        {
            $pageNumber = $elementInfo['element_count'] . "/" . $pageKey;
            $totalPageAmount = 0;
            foreach($itemPage as $itemRow)
            {
                $rowAmount = $itemRow[ self::ROW_BILL_ITEM_AMOUNT ];
                $totalPageAmount += $rowAmount;
            }
            array_push($elementPageNumbersAndTheirAmounts, array(
                'pageNumber' => $pageNumber,
                'pageAmount' => $totalPageAmount,
            ));
        }

        return $elementPageNumbersAndTheirAmounts;
    }

    /**
     * Adds a row with the specified data to the collection page.
     *
     * @param $arrayOfRows
     * @param $rowDescription
     * @param $rowType
     * @param $rowAmount
     */
    public function addRowToCollectionPage(&$arrayOfRows, $rowDescription, $rowType, $rowAmount)
    {
        $row = new SplFixedArray(self::TOTAL_COLLECTION_ROW_PROPERTIES);
        $row[ self::ROW_COLLECTION_DESCRIPTION ] = $rowDescription;
        $row[ self::ROW_COLLECTION_TYPE ] = $rowType;
        $row[ self::ROW_COLLECTION_AMOUNT ] = $rowAmount;

        array_push($arrayOfRows, $row);

        unset( $row );
    }

    /**
     * Adds a blank row to the collection page.
     *
     * @param $arrayOfRows
     */
    public function addBlankRowToCollectionPage(&$arrayOfRows)
    {
        $this->addRowToCollectionPage($arrayOfRows, null, self::ROW_TYPE_BLANK, null);
    }

    /**
     * Generates the collection pages.
     *
     * We use SplFixedArray as data structure to boost performance. Since associative array cannot be use in SplFixedArray, we have to use indexes
     * to get values. Below are indexes and what they represent as their values
     *
     * $row:
     * 0 - description
     * 1 - type
     * 2 - amount
     *
     * @param       $allElementsPageNumbersAndPageTotal
     * @param       $pageNumberDescription
     * @param       $pageNumber
     * @param array $collectionPages
     * @param       $previousCollectionPageAmount
     * @param bool  $continuePage
     */
    public function generateCollectionPages(&$allElementsPageNumbersAndPageTotal, $pageNumberDescription, $pageNumber, Array &$collectionPages, $previousCollectionPageAmount, $continuePage = false)
    {
        $collectionPages[ $pageNumber ] = array();
        $collectionPages[ $pageNumber ]['total_amount'] = 0;
        $totalCollectionPageAmount = 0;
        $totalCollectionPageAmount += $previousCollectionPageAmount;

        $maxRows = self::getMaxRows() - 15; //15 less rows for collection page

        $this->addBlankRowToCollectionPage($collectionPages[ $pageNumber ]);
        $rowCount = 1;

        $this->addBlankRowToCollectionPage($collectionPages[ $pageNumber ]);

        $this->addCollectionTitleRow($pageNumber, $collectionPages, $continuePage);

        $this->addBlankRowToCollectionPage($collectionPages[ $pageNumber ]);

        if( $continuePage )
        {
            $previousPageNumber = $pageNumber - 1;

            //Collection total from previous page
            $description = trim(self::getCollectionNextPageBringForwardPrefix() . ' ' . trim(self::getCollectionPageNoPrefix() . " " . $previousPageNumber));
            $type = self::ROW_TYPE_COLLECTION_TITLE;
            $previousPageAmount = $previousCollectionPageAmount; // total amount from previous page

            $this->addRowToCollectionPage($collectionPages[ $pageNumber ], $description, $type, $previousPageAmount);

            $this->addBlankRowToCollectionPage($collectionPages[ $pageNumber ]);

            $rowCount += 3 + 2; //plus one blank row & collection title
        }
        else
        {
            $rowCount += 3; //plus one blank row & collection title
        }

        // content
        foreach($allElementsPageNumbersAndPageTotal as $pageNumberAndPageTotal)
        {
            $occupiedRows = Utilities::justify($pageNumberDescription . " " . self::getPageNoPrefix() . $pageNumberAndPageTotal['pageNumber'],
                $this->MAX_CHARACTERS);
            $rowCount += $occupiedRows->count();

            if( $rowCount <= $maxRows )
            {
                foreach($occupiedRows as $key => $occupiedRow)
                {
                    $this->addRowToCollectionPage($collectionPages[ $pageNumber ], $occupiedRow, null, $pageNumberAndPageTotal['pageAmount']);

                    $totalCollectionPageAmount += $pageNumberAndPageTotal['pageAmount'];

                    unset( $occupiedRow );
                }

                //remove pageNumberAndPageTotal from array to avoid it from being processed (printed etc.) again.
                array_shift($allElementsPageNumbersAndPageTotal);

                $collectionPages[ $pageNumber ]['page_count'] = $pageNumber;

                $this->addBlankRowToCollectionPage($collectionPages[ $pageNumber ]);

                $rowCount++; //plus one blank row;
            }
            else
            {
                $nextPageCount = $pageNumber + 1;

                $this->generateCollectionPages($allElementsPageNumbersAndPageTotal, $pageNumberDescription, $nextPageCount, $collectionPages, $totalCollectionPageAmount, true);
                break;
            }
        }

        $collectionPages[ $pageNumber ]['total_amount'] = $totalCollectionPageAmount;
    }

    /**
     * Adds a blank row to the item page.
     *
     * @param $arrayOfRows
     */
    public function addBlankRowToItemPage(&$arrayOfRows)
    {
        $this->addRowToItemPage($arrayOfRows, -1, null, null, 0, self::ROW_TYPE_BLANK, null, null, null, null, null, null, null, null, null, null, null);
    }

    /**
     * Adds a row to the item page.
     *
     * @param $arrayOfRows
     * @param $id
     * @param $rowIdx
     * @param $description
     * @param $level
     * @param $type
     * @param $unit
     * @param $supplyRate
     * @param $qtyPerUnit
     * @param $include
     * @param $estimatedQty
     * @param $left
     * @param $right
     * @param $percentageWastage
     * @param $contractorSupplyRate
     * @param $difference
     * @param $amount
     */
    protected function addRowToItemPage(&$arrayOfRows, $id, $rowIdx, $description, $level, $type, $unit=null, $supplyRate=null, $qtyPerUnit=null, $include=null, $estimatedQty=null, $left=null, $right=null, $percentageWastage=null, $contractorSupplyRate=null, $difference=null, $amount=null)
    {
        $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $row[ self::ROW_BILL_ITEM_ID ] = $id;
        $row[ self::ROW_BILL_ITEM_ROW_IDX ] = $rowIdx;
        $row[ self::ROW_BILL_ITEM_DESCRIPTION ] = (!empty($description)) ? Utilities::inlineJustify($description, $this->MAX_CHARACTERS) : $description;
        $row[ self::ROW_BILL_ITEM_LEVEL ] = $level;
        $row[ self::ROW_BILL_ITEM_TYPE ] = $type;
        $row[ self::ROW_BILL_ITEM_UNIT ] = $unit;
        $row[ self::ROW_BILL_ITEM_SUPPLY_RATE ] = $supplyRate;
        $row[ self::ROW_BILL_ITEM_QTY_PER_UNIT ] = $qtyPerUnit;
        $row[ self::ROW_BILL_ITEM_INCLUDE ] = $include;
        $row[ self::ROW_BILL_ITEM_ESTIMATED_QTY ] = $estimatedQty;
        $row[ self::ROW_BILL_ITEM_LEFT ] = $left;
        $row[ self::ROW_BILL_ITEM_RIGHT ] = $right;
        $row[ self::ROW_BILL_ITEM_PERCENTAGE_WASTAGE ] = $percentageWastage;
        $row[ self::ROW_BILL_ITEM_CONTRACTOR_SUPPLY_RATE ] = $contractorSupplyRate;
        $row[ self::ROW_BILL_ITEM_DIFFERENCE ] = $difference;
        $row[ self::ROW_BILL_ITEM_AMOUNT ] = $amount;

        array_push($arrayOfRows, $row);

        unset( $row );
    }

    /**
     * Generates the bill item pages for an element.
     *
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
     * 9 - estimated quantity
     * 12 - percentage wastage
     * 13 - contractor rate
     * 14 - difference
     * 15 - amount
     *
     * @param array $billItems
     * @param array $billColumnSettings
     * @param       $elementInfo
     * @param       $pageCount
     * @param       $ancestors
     * @param       $itemPages
     * @param bool  $newPage
     */
    public function generateBillItemPages(Array $billItems, Array $billColumnSettings, $elementInfo, $pageCount, $ancestors, &$itemPages, $newPage = false)
    {
        $itemPages[ $pageCount ] = [];
        $layoutSettings = $this->printSettings['layoutSetting'];
        $maxRows = self::getMaxRows();

        $bringForward = false;

        //starts with a blank row
        $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
        $rowCount = 1;

        /*
         * Always display element description at start of every page.
         */
        $descriptionCont = $pageCount > 1 ? $layoutSettings['contdPrefix'] : null;

        if( $this->printSettings['layoutSetting']['printContdEndDesc'] )
        {
            $occupiedRows = Utilities::justify($elementInfo['description'] . " " . $descriptionCont, $this->MAX_CHARACTERS);
        }
        else
        {
            $occupiedRows = Utilities::justify($descriptionCont . " " . $elementInfo['description'], $this->MAX_CHARACTERS);
        }

        foreach($occupiedRows as $occupiedRow)
        {
            $this->addRowToItemPage($itemPages[ $pageCount ], -1, null, $occupiedRow, 0, self::ROW_TYPE_ELEMENT, null, null, null, null, null, null, null, null, null, null, null);
        }
        $rowCount += $occupiedRows->count();

        $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
        $rowCount ++;//plus one blank row

        $itemIndex = 1;
        $counterIndex = 0;//display item's index in BQ

        while (list($x, $billItem) = each($billItems))
        {
            $ancestors = $billItem['level'] == 0 ? array() : $ancestors;

            if (($billItem['type'] == SupplyOfMaterialItem::TYPE_HEADER || $billItem['type'] == SupplyOfMaterialItem::TYPE_HEADER_N) && ($billItem['rgt'] - $billItem['lft'] > 1 ))
            {
                $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
                $row[ self::ROW_BILL_ITEM_ID ] = $billItem['id'];//id
                $row[ self::ROW_BILL_ITEM_ROW_IDX ] = null;//row index
                $row[ self::ROW_BILL_ITEM_DESCRIPTION ] = $billItem['description'];//description
                $row[ self::ROW_BILL_ITEM_LEVEL ] = $billItem['level'];//level
                $row[ self::ROW_BILL_ITEM_TYPE ] = $billItem['type'];//type
                $row[ self::ROW_BILL_ITEM_UNIT ] = $billItem['lft']; //set lft info (only for ancestor)
                $row[ self::ROW_BILL_ITEM_SUPPLY_RATE ] = $billItem['rgt']; //set rgt info (only for ancestor)
                $row[ self::ROW_BILL_ITEM_QTY_PER_UNIT ] = $billItem['root_id']; //set root_id info (only for )

                $ancestors[ $billItem['level'] ] = $row;

                $ancestors = array_splice($ancestors, 0, $billItem['level'] + 1);

                unset( $row );
            }

            /*
             * To get all ancestors from previous page so we can display it as continued headers
             * before we print out the item
             */
            if( $pageCount > 1 and $itemIndex == 1 and $billItem['level'] != 0 )
            {
                // if detected current looped item is same level with ancestor, then overwrite it
                $this->unsetHeadThatIsSameLevelWithItemOnNextPage($ancestors, $billItem);

                foreach($ancestors as $ancestor)
                {
                    if( $ancestor[ self::ROW_BILL_ITEM_ID ] == $billItem['id'] )
                    {
                        continue;
                    }

                    $descriptionCont = $pageCount > 1 ? $layoutSettings['contdPrefix'] : null;

                    if( $this->printSettings['layoutSetting']['printContdEndDesc'] )
                    {
                        $occupiedRows = Utilities::justify($ancestor[ self::ROW_BILL_ITEM_DESCRIPTION ] . " " . $descriptionCont, $this->MAX_CHARACTERS);
                    }
                    else
                    {
                        $occupiedRows = Utilities::justify($descriptionCont . " " . $ancestor[ self::ROW_BILL_ITEM_DESCRIPTION ], $this->MAX_CHARACTERS);
                    }

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
                        $this->addRowToItemPage($itemPages[ $pageCount ], $ancestor[ self::ROW_BILL_ITEM_ID ], null, $occupiedRow, $ancestor[ self::ROW_BILL_ITEM_LEVEL ], $ancestor[ self::ROW_BILL_ITEM_TYPE ], null, null, null, null, null, null, null, null, null, null, null);
                        $rowCount++;
                    }

                    $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
                    $rowCount++;

                    unset( $occupiedRow, $occupiedRows, $ancestor );
                }
            }

            if( $billItem['type'] == SupplyOfMaterialItem::TYPE_HEADER_N and ! $newPage )
            {
                unset( $occupiedRows );

                reset($billItems);
                $pageCount++;

                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, true);
                break;
            }

            $occupiedRows      = $this->calculateBQItemDescription($billItem);
            $totalOccupiedRows = $occupiedRows->count();
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

                    $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, true);
                    break;
                }
                else
                {
                    try
                    {
                        list($availableRows, $isLastChunk) = $this->breakDownItemDescription($billItems, $billItem, $occupiedRows, $availableRows);
                        $totalOccupiedRows = $occupiedRows->count();
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
                
                $totalOccupiedRows = $occupiedRows->count();
                
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
            
            reset($billItems);
            $x = key($billItems);//reset current $billItems iteration key to a latest key from the truncated item*/

            if($availableRows >= $occupiedRows->count()) // If can fit in remaining space of current page.
            {
                $rowCount += $occupiedRows->count();

                foreach($occupiedRows as $key => $occupiedRow)
                {
                    if( $key == 0 && $billItem['type'] != SupplyOfMaterialItem::TYPE_HEADER && $billItem['type'] != SupplyOfMaterialItem::TYPE_HEADER_N )
                    {
                        $counterIndex++;
                    }

                    $rowIdx = ( $key == 0 && $billItem['type'] != SupplyOfMaterialItem::TYPE_HEADER && $billItem['type'] != SupplyOfMaterialItem::TYPE_HEADER_N ) ? Utilities::generateCharFromNumber($counterIndex,
                        $this->printSettings['layoutSetting']['includeIandO']) : null;

                    $description = $occupiedRow;
                    $level = $billItem['level'];
                    $type = $billItem['type'];
                    $id = null;
                    $unit = null;
                    $supplyRate = null;
                    $qtyPerUnit = null;
                    $estimatedQty = null;
                    $percentageWastage = null;
                    $contractorSupplyRate = null;
                    $difference = null;
                    $amount = null;

                    if($isLastChunk && $key + 1 == $occupiedRows->count() && $billItem['type'] != SupplyOfMaterialItem::TYPE_HEADER && $billItem['type'] != SupplyOfMaterialItem::TYPE_HEADER_N )
                    {
                        $id = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $unit = $billItem['uom'];
                        $supplyRate = $billItem['supply_rate'];
                        $estimatedQty = $billItem['estimated_qty'];
                        $percentageWastage = $billItem['percentage_of_wastage'];
                        $contractorSupplyRate = $billItem['contractor_supply_rate'];
                        $difference = $billItem['difference'];
                        $amount = $billItem['amount'];
                    }

                    $this->addRowToItemPage($itemPages[ $pageCount ], $id, $rowIdx, $description, $level, $type, $unit, $supplyRate, $qtyPerUnit, null, $estimatedQty, null, null, $percentageWastage, $contractorSupplyRate, $difference, $amount);
                }

                $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
                $rowCount++;//plus one blank row;

                $itemIndex++;
                $newPage = false;

                unset($billItems[$x], $occupiedRows );
                reset($billItems);
            }
            else
            {
                reset($billItems);
                $pageCount++;

                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, true);
                break;
            }
        }
    }

    /**
     * Adds a row for the Collection Title
     *
     * @param $pageCount
     * @param $collectionPages
     * @param $continuePage
     */
    public function addCollectionTitleRow($pageCount, &$collectionPages, $continuePage)
    {
        $contdPrefix = $continuePage ? " {$this->printSettings['layoutSetting']['contdPrefix']}" : null;

        if( $this->printSettings['layoutSetting']['printContdEndDesc'] )
        {
            $description = $this->printSettings['phrase']['collectionInGridPrefix'] . $contdPrefix;
        }
        else
        {
            $description = $contdPrefix . ' ' . $this->printSettings['phrase']['collectionInGridPrefix'];
        }

        $this->addRowToCollectionPage($collectionPages[ $pageCount ], $description, self::ROW_TYPE_COLLECTION_TITLE, null);
    }

    /**
     * Adds an empty item page.
     *
     * @param $pages
     * @param $elementCount
     */
    public function addEmptyItemPage(&$pages, $elementCount)
    {
        $itemPages = array();
        $itemPages[1] = array();
        $this->addBlankRowToItemPage($itemPages[1]);

        $page = array(
            'description'   => null,
            'element_count' => $elementCount,
            'item_pages'    => SplFixedArray::fromArray($itemPages),
        );
        array_push($pages, $page);
    }

    private function calculateBringForwardItem($counter, $billItems, $maxRows, $rowCount, $prevHead = false, $count = 0)
    {
        $count++;

        $nextItem = $billItems[ $counter ];

        $nextItemOccupiedRows = Utilities::justify($nextItem['description'], $this->MAX_CHARACTERS);

        $bringForward = ( $rowCount + count($nextItemOccupiedRows) + 1 >= $maxRows ) ? true : false;

        if( $bringForward )
        {
            return true;
        }
        else
        {
            //Experimental: Set limit up to 3 header to prevent infinite loop if header's to big for page
            if( $count >= 1 )
            {
                return $bringForward;
            }
            else
            {
                if( $nextItem['type'] == SupplyOfMaterialItem::TYPE_HEADER )
                {
                    return self::calculateBringForwardItem($counter + 1, $billItems, $maxRows,
                        $rowCount + count($nextItemOccupiedRows) + 1, true, $count);
                }
                else
                {
                    return $bringForward;
                }
            }
        }
    }

    public function getPrintSetting()
    {
        return SupplyOfMaterialLayoutSettingTable::getInstance()->getPrintingLayoutSettings($this->bill->SupplyOfMaterialLayoutSetting->id,
            true);
    }

    public function getElementOrder()
    {
        $stmt = $this->pdo->prepare("SELECT e.id, e.description FROM " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e
            WHERE e.project_structure_id = " . $this->bill->id . " AND e.deleted_at IS NULL ORDER BY e.priority ASC");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $elementsOrder = array();
        $counter = 1;

        foreach($result as $element)
        {
            $elementsOrder[ $element['id'] ] = array(
                'description' => $element['description'],
                'order'       => $counter
            );

            $counter++;
        }

        return $elementsOrder;
    }

    private function calculateRowNeededByProjectTitle()
    {
        $this->setProjectTitleMaxCharactersPerLine();

        $projectTitle = $this->project->title;

        $this->projectTitleRows = Utilities::justify($projectTitle, $this->MAX_CHARACTERS_FOR_PROJECT_TITLE);
    }

    public function getProjectTitleRows()
    {
        return $this->projectTitleRows;
    }

    // styles for bill's layout
    // will be changed depending on type of paper and orientation
    public function getLayoutStyling()
    {
        $pageFormat = $this->pageFormat;
        $headStyling = "";
        $headSettings = $this->headSettings;

        $elementHeaderStyling = ".elementHeader {text-decoration: underline;font-weight: bold;font-style: normal;color: #000;}";

        foreach($headSettings as $headSetting)
        {
            $textDecoration = $headSetting['underline'] ? 'underline' : 'none';
            $fontWeight = $headSetting['bold'] ? 'bold' : 'normal';
            $fontStyle = $headSetting['italic'] ? 'italic' : 'normal';
            $head = $headSetting['head'] - 1;

            $headStyling .= '.bqHead' . $head . ' {font-weight:' . $fontWeight . ';text-decoration: ' . $textDecoration . ';font-style: ' . $fontStyle . ';}';
        }

        $style = '
            body {font-family: "' . $this->fontType . '";font-size:' . $this->fontSize . 'px;}
            pre {font-family: "' . $this->fontType . '";}
            .headerTable {font-size: ' . $this->fontSize . 'px;}
            .fulljustify {
                text-align: justify;
            }
            .fulljustify:after {
                content: "";
                display: inline-block;
                width: 100%;
            }
            .footer-table {font-size: ' . $this->fontSize . 'px;}
            .mainTable {font-size: ' . $this->fontSize . 'px;min-height:' . $pageFormat['height'] . 'px;max-height:' . $pageFormat['height'] . 'px;}';

        $topHeadStyling = self::topHeadStyling();

        $style .= $topHeadStyling . $headStyling . $elementHeaderStyling;

        return $style;
    }

    public function getPriceFormatting()
    {
        $priceFormatting = array( '.', ',' );

        if( $this->printSettings['layoutSetting']['priceFormat'] == 'opposite' )
        {
            $priceFormatting = array( ',', '.' );
        }

        array_push($priceFormatting, 2);

        return $priceFormatting;
    }

    public function getCurrencyFormat()
    {
        return array( $this->printSettings['phrase']['currencyPrefix'], null );
    }

    /**
     * Returns the page number prefix for the collection pages.
     *
     * @return string
     */
    public function getCollectionPageNoPrefix()
    {
        return "somc";
    }

    /**
     * Returns the project status (id).
     *
     * @return int
     */
    public function getProjectMainInformationStatus()
    {
        return $this->project->getMainInformation()->status;
    }

}