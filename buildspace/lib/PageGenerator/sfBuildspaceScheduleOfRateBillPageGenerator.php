<?php

class sfBuildspaceScheduleOfRateBillPageGenerator extends sfBuildspaceBQMasterFunction
{
    public $bill;
    public $pdo;
    public $fontType;
    public $fontSize;
    public $headSettings;
    public $numberOfBillColumns;
    public $defaultRow;

    private $MAX_CHARACTERS_FOR_PROJECT_TITLE = 125;

    private $projectTitleRows = [];

    public function __construct(ProjectStructure $bill, ScheduleOfRateBillElement $element=null)
    {
        $this->bill = $bill;
        $this->billElement = $element;

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->project = $bill->root_id == $bill->id ? $bill : ProjectStructureTable::getInstance()->find($bill->root_id);

        $this->billStructure = $this->queryBillStructure();

        // Generate Element Order By Bill
        $this->elementsOrder = $this->getElementOrder();

        // get bill's printout setting
        $this->printSettings = $this->getPrintSetting();

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
        $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $row[ self::ROW_BILL_ITEM_ID ] = -1;//id
        $row[ self::ROW_BILL_ITEM_ROW_IDX ] = null; //row index
        $row[ self::ROW_BILL_ITEM_DESCRIPTION ] = null; //description
        $row[ self::ROW_BILL_ITEM_LEVEL ] = 0; //level
        $row[ self::ROW_BILL_ITEM_TYPE ] = self::ROW_TYPE_BLANK; //type
        $row[ self::ROW_BILL_ITEM_UNIT ] = null; //unit
        $row[ self::ROW_BILL_ITEM_RATE ] = 5; //rate

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
        return 72 - count($this->projectTitleRows) - 16;
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

        $elementSqlPart = $this->billElement instanceof ScheduleOfRateBillElement ? "AND e.id = " . $this->billElement->id : null;

        $stmt = $this->pdo->prepare("SELECT e.id, e.description FROM " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " e WHERE e.project_structure_id = " . $this->bill->id . " " . $elementSqlPart . " AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $result = array(
                'id'          => $element['id'],
                'description' => $element['description'],
                'items'       => array()
            );

            $rateColumnName = 'estimation_rate';
            if( $this->getProjectMainInformationStatus() == ProjectMainInformation::STATUS_IMPORT )
            {
                $rateColumnName = 'contractor_rate';
            }

            $stmt = $this->pdo->prepare("SELECT c.id, c.description, c.element_id, c.type, c." . $rateColumnName . " as rate,
                c.uom_id, c.lft, c.rgt, c.root_id, c.level, uom.symbol AS uom
                FROM " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " c
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

        if( $billElement instanceof ScheduleOfRateBillElement )
        {
            $stmt = $this->pdo->prepare("SELECT e.id FROM " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " e
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

        foreach($billStructure as $element)
        {
            $itemPages = array();

            $elemCount = $billElement instanceof ScheduleOfRateBillElement ? $elementCount[ $element['id'] ] : $elementCount;

            $elementInfo = array(
                'description'   => $element['description'],
                'element_count' => $elemCount
            );

            $this->generateBillItemPagesForElement($element['items'], $billColumnSettings, $elementInfo, 1, array(), $itemPages);

            $page = array(
                'description'   => $element['description'],
                'element_count' => $elemCount,
                'item_pages'    => SplFixedArray::fromArray($itemPages),
            );

            $pages[ $element['id'] ] = $page;

            if( ! $billElement instanceof ScheduleOfRateBillElement )
            {
                $elementCount++;
            }

            unset( $itemPages, $element );
        }

        if( count($pages) < 1 )
        {
            $this->addEmptyPage($pages, 1);
        }

        return $pages;
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
     *
     * @param array $billItems
     * @param array $billColumnSettings
     * @param       $elementInfo
     * @param       $pageCount
     * @param       $ancestors
     * @param       $itemPages
     * @param bool  $newPage
     */
    public function generateBillItemPagesForElement(Array $billItems, Array $billColumnSettings, $elementInfo, $pageCount, $ancestors, &$itemPages, $newPage = false)
    {
        $itemPages[$pageCount] = [];
        $layoutSettings        = $this->printSettings['layoutSetting'];
        $maxRows               = self::getMaxRows();

        //starts with a blank row
        $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
        $rowCount = 1;

        /*
         * Always display element description at start of every page.
         */
        $descriptionCont = $pageCount > 1 ? $layoutSettings['contdPrefix'] : null;

        $occupiedRows = $this->addElementDescription($elementInfo, $pageCount, $itemPages, $descriptionCont);
        $rowCount += count($occupiedRows);

        $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
        $rowCount++;

        $itemIndex = 1;
        $counterIndex = 0; //display item's index in BQ

        while (list($x, $billItem) = each($billItems))
        {
            $ancestors = $billItem['level'] == 0 ? [] : $ancestors;

            if (($billItem['type'] == ScheduleOfRateBillItem::TYPE_HEADER || $billItem['type'] == ScheduleOfRateBillItem::TYPE_HEADER_N) && ($billItem['rgt'] - $billItem['lft'] > 1 ))
            {
                $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
                $row[ self::ROW_BILL_ITEM_ID ]          = $billItem['id'];//id
                $row[ self::ROW_BILL_ITEM_ROW_IDX ]     = null;//row index
                $row[ self::ROW_BILL_ITEM_DESCRIPTION ] = $billItem['description'];//description
                $row[ self::ROW_BILL_ITEM_LEVEL ]       = $billItem['level'];//level
                $row[ self::ROW_BILL_ITEM_TYPE ]        = $billItem['type'];//type
                $row[ self::ROW_BILL_ITEM_UNIT ]        = $billItem['lft']; //set lft info (only for ancestor)
                $row[ self::ROW_BILL_ITEM_RATE ]        = $billItem['rgt']; //set rgt info (only for ancestor)
                $row[self::ROW_BILL_ITEM_QTY_PER_UNIT]  = $billItem['root_id']; //set root_id info (only for )

                // Add item to ancestors.
                $ancestors[$billItem['level']] = $row;

                // Remove all items after current item in ancestors.
                $ancestors = array_splice($ancestors, 0, $billItem['level']+1);

                unset( $row );
            }

            /*
             * To get all ancestors from previous page so we can display it as continued headers
             * before we print out the item
             */
            if($pageCount > 1 and $itemIndex == 1 and $billItem['level'] != 0)
            {
                // if detected current looped item is same level with ancestor, then overwrite it
                $this->unsetHeadThatIsSameLevelWithItemOnNextPage($ancestors, $billItem);

                foreach($ancestors as $ancestor)
                {
                    if( $ancestor[ self::ROW_BILL_ITEM_ID ] == $billItem['id'] )
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

            if( $billItem['type'] == ScheduleOfRateBillItem::TYPE_HEADER_N and ! $newPage )
            {
                unset( $occupiedRows );

                reset($billItems);
                $pageCount++;
                
                $this->generateBillItemPagesForElement($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, true);
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

                    $this->generateBillItemPagesForElement($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, true);
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

            if($availableRows >= $totalOccupiedRows) // If can fit in remaining space of current page.
            {
                $rowCount += $occupiedRows->count();

                foreach($occupiedRows as $key => $occupiedRow)
                {
                    if( $key == 0 && $billItem['type'] != ScheduleOfRateBillItem::TYPE_HEADER && $billItem['type'] != ScheduleOfRateBillItem::TYPE_HEADER_N )
                    {
                        $counterIndex++;
                    }

                    $rowIdx = ( $key == 0 && $billItem['type'] != ScheduleOfRateBillItem::TYPE_HEADER && $billItem['type'] != ScheduleOfRateBillItem::TYPE_HEADER_N ) ? Utilities::generateCharFromNumber($counterIndex,
                        $this->printSettings['layoutSetting']['includeIandO']) : null;

                    $description = $occupiedRow;
                    $level = $billItem['level'];
                    $type = $billItem['type'];
                    $id = null;
                    $unit = null;
                    $rate = null;

                    if($isLastChunk && $key + 1 == $occupiedRows->count() && $billItem['type'] != ScheduleOfRateBillItem::TYPE_HEADER && $billItem['type'] != ScheduleOfRateBillItem::TYPE_HEADER_N )
                    {
                        $id   = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $unit = $billItem['uom'];
                        $rate = $billItem['rate'];
                    }

                    $this->addRowToItemPage($itemPages[ $pageCount ], $id, $rowIdx, $description, $level, $type, $unit, $rate);
                }

                //blank row
                $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
                $rowCount++;

                $itemIndex++;
                $newPage = false;

                unset($billItems[$x], $occupiedRows);

                reset($billItems);
            }
            else
            {
                reset($billItems);
                $pageCount++;

                $this->generateBillItemPagesForElement($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, true);
                break;
            }
        }
    }

    /**
     * Adds an empty page.
     *
     * @param $pages
     * @param $elementCount
     *
     * @return mixed
     */
    public function addEmptyPage(&$pages, $elementCount)
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
        return ScheduleOfRateBillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($this->bill->ScheduleOfRateBillLayoutSetting->id, true);
    }

    public function getElementOrder()
    {
        $stmt = $this->pdo->prepare("SELECT e.id, e.description FROM " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " e
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
     * Returns the project status (id).
     *
     * @return int
     */
    public function getProjectMainInformationStatus()
    {
        return $this->project->getMainInformation()->status;
    }

}