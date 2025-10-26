<?php

class sfBuildspaceReportItemRateAndTotalPerUnitPageGenerator extends sfBuildspaceBQMasterFunction {

    public $tendererIds;
    public $tenderers;
    public $itemIds;
    public $pageTitle;
    public $elementIds;
    public $fontSize;
    public $headSettings;
    public $elementsWithBillItems;
    public $formulatedColumns;
    public $quantityPerUnitByColumns;
    public $billItemTypeReferences;
    public $billItemTypeRefFormulatedColumns;
    public $markupSettingsInfo;

    public $estimateElementTotals   = array();
    public $contractorElementTotals = array();

    public function __construct(ProjectStructure $bill, $tendererIds, $elementIds, $itemIds, $sortingType, $pageTitle, $desc, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->bill    = $bill;
        $this->project = ProjectStructureTable::getInstance()->find($bill->root_id);

        $this->elementIds         = $elementIds;
        $this->sortingType        = $sortingType;
        $this->pageTitle          = $pageTitle;
        $this->currency           = $this->project->MainInformation->Currency;
        $this->tendererIds        = isset( $tendererIds ) ? $tendererIds : array();
        $this->tenderers          = $this->getTenderers();
        $this->billColumnSettings = $bill->BillColumnSettings;
        $this->elements           = $this->getElements();

        $this->descriptionFormat = $descriptionFormat;

        $this->setOrientationAndSize();

        $this->elementsOrder = $this->getElementOrder();
        $this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, true);
        $this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings  = $this->printSettings['headSettings'];

        $this->contractorRates             = TenderCompanyTable::getContractorSingleUnitElementGrandTotalByBillAndElementsAndTenderers($bill, $this->elements, $tendererIds);
        $this->itemOriginalQuantityByTypes = BillItemTypeReferenceTable::getQtyByBillColumnSettingsIdAndElementIds($bill->BillColumnSettings->toArray(), $this->elements);
        $this->itemOriginalRates           = BillItemTable::getItemRatesByElementIds($this->elements);

        self::setMaxCharactersPerLine();

        $this->calculateTotalByElementAndTypes($this->contractorRates, $this->itemOriginalQuantityByTypes, $this->itemOriginalRates);

        $this->markupSettingsInfo = array(
            'bill_markup_enabled'    => $bill->BillMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage' => $bill->BillMarkupSetting->bill_markup_percentage,
            'element_markup_enabled' => $bill->BillMarkupSetting->element_markup_enabled,
            'item_markup_enabled'    => $bill->BillMarkupSetting->item_markup_enabled,
            'rounding_type'          => $bill->BillMarkupSetting->rounding_type,
        );
    }

    public function setParameters($elements, $elementsWithBillItems, $formulatedColumns, $quantityPerUnitByColumns, $billItemTypeReferences, $billItemTypeRefFormulatedColumns)
    {
        $this->elements                         = $elements;
        $this->elementsWithBillItems            = $elementsWithBillItems;
        $this->formulatedColumns                = $formulatedColumns;
        $this->quantityPerUnitByColumns         = $quantityPerUnitByColumns;
        $this->billItemTypeReferences           = $billItemTypeReferences;
        $this->billItemTypeRefFormulatedColumns = $billItemTypeRefFormulatedColumns; // stores: Final value
    }

    public function generatePages()
    {
        ini_set('memory_limit','512M');

        $pages        = array();
        $elementCount = 0;

        //if no items are selected
        if( empty( $this->elementsWithBillItems ) )
        {
            $itemPages = array();
            $this->generateEmptyPage($itemPages);
            $pages[0] = array(
                'description'   => "N/a",
                'element_count' => 1,
                'item_pages'    => SplFixedArray::fromArray($itemPages)
            );

            return $pages;
        }

        foreach($this->elementsWithBillItems as $elementId => $elementBillItems)
        {
            $elementCount++;
            $itemPages              = array();
            $this->currentElementId = $elementId;

            $this->startElementTotals($elementId);

            $this->generateBillElementPages($elementBillItems, 1, array(), $itemPages);

            $pages[ $elementId ] = array(
                'description'   => $this->elements[ $elementId ]['description'],
                'element_count' => $elementCount,
                'item_pages'    => SplFixedArray::fromArray($itemPages),
            );
        }

        unset( $itemPages, $elements );

        return $pages;
    }

    protected function addRow(&$itemPage, $id, $rowIdx, $description, $level, $type, $unit, $rate, $quantityPerUnit, $total)
    {
        $row                                     = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $row[ self::ROW_BILL_ITEM_ID ]           = $id;
        $row[ self::ROW_BILL_ITEM_ROW_IDX ]      = $rowIdx;
        $row[ self::ROW_BILL_ITEM_DESCRIPTION ]  = $description;
        $row[ self::ROW_BILL_ITEM_LEVEL ]        = $level;
        $row[ self::ROW_BILL_ITEM_TYPE ]         = $type;
        $row[ self::ROW_BILL_ITEM_UNIT ]         = $unit;
        $row[ self::ROW_BILL_ITEM_RATE ]         = $rate;
        $row[ self::ROW_BILL_ITEM_QTY_PER_UNIT ] = $quantityPerUnit;
        $row[ self::ROW_BILL_ITEM_TOTAL ]        = $total;

        array_push($itemPage, $row);

        $this->rowCount++;
    }

    protected function addBlankRow(&$itemPage)
    {
        self::addRow($itemPage, -1, null, null, 0, self::ROW_TYPE_BLANK, null, null, null, null);
    }

    protected function generateEmptyPage(&$itemPages)
    {
        $this->rowCount = 0;
        $itemPages[1]   = array();
        self::addBlankRow($itemPages[1]);
        $this->totalPage++;
    }

    protected function generateBillElementPages(array $billItems, $pageCount, $ancestors, &$itemPages)
    {
        $this->totalPage++;
        $this->rowCount = 0;
        $elementTotals  = array();

        $itemIndex = 1;

        $itemPages[ $pageCount ] = array();
        $ancestors               = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

        self::addBlankRow($itemPages[ $pageCount ]);

        self::addElement($itemPages[ $pageCount ]);

        self::addBlankRow($itemPages[ $pageCount ]);

        foreach($ancestors as $k => $row)
        {
            array_push($itemPages[ $pageCount ], $row);
            $this->rowCount += 1;
            unset( $row );
        }

        $ancestors = array();
        
        while (list($x, $billItem) = each($billItems))
        {
            $occupiedRows      = $this->calculateBQItemDescription($billItem);
            $totalOccupiedRows = ($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE) ? (self::PC_RATE_TABLE_SIZE+$occupiedRows->count()) : $occupiedRows->count();
            $availableRows     = ($this->getMaxRows() - $this->rowCount);
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

                    $this->generateBillElementPages($billItems, $pageCount, $ancestors, $itemPages);
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
            
            if($availableRows >= $totalOccupiedRows)
            {
                self::addItem($itemPages[ $pageCount ], $billItem);

                self::addBlankRow($itemPages[ $pageCount ]);

                $itemIndex++;

                unset($billItems[$x], $occupiedRows);

                reset($billItems);
            }
            else
            {
                $occupiedRows = null;
                unset($occupiedRows);

                reset($billItems);
                $pageCount++;

                $this->generateBillElementPages($billItems, $pageCount, $ancestors, $itemPages);
                break;
            }
        }
    }

    protected function addElement(&$itemPage)
    {
        foreach(self::splitDescription($this->elements[ $this->currentElementId ]) as $descriptionRow)
        {
            self::addRow($itemPage, -1, null, $descriptionRow, 0, self::ROW_TYPE_ELEMENT, null, null, null, null);
        }
    }

    protected function addItem(&$itemPage, $billItem)
    {
        foreach($descriptionRows = self::splitDescription($billItem) as $key => $descriptionRow)
        {
            $id       = null;
            $rowIndex = ( $key == 0 ) ? BillItemTable::getBillRef($billItem) : null;
            $level    = $billItem['level'];
            $type     = $billItem['type'];
            $unit     = null;

            $rateAfterMarkup      = 0;
            $totals               = array();
            $quantityPerUnitArray = array();

            // If is last descriptionRow
            if( $key + 1 === $descriptionRows->count() )
            {
                $id   = $billItem['id'];
                $unit = $billItem['uom_symbol'];

                $rateAfterMarkup = 0;

                if( array_key_exists($billItem['id'], $this->formulatedColumns) )
                {
                    $rate                 = 0;
                    $itemMarkupPercentage = 0;

                    if( $this->formulatedColumns[ $billItem['id'] ][0]['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                    {
                        $rate = $this->formulatedColumns[ $billItem['id'] ][0]['final_value'];
                    }
                    if( $this->formulatedColumns[ $billItem['id'] ][0]['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
                    {
                        $itemMarkupPercentage = $this->formulatedColumns[ $billItem['id'] ][0]['final_value'];
                    }

                    $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $this->markupSettingsInfo);
                }

                foreach($this->billColumnSettings as $column)
                {
                    if( ! isset( $this->quantityPerUnitByColumns[ $column->id ][ $billItem['id'] ] ) )
                    {
                        continue;
                    }

                    $quantityPerUnit = $this->quantityPerUnitByColumns[ $column->id ][ $billItem['id'] ][0];

                    $totalPerUnit = round($rateAfterMarkup * $quantityPerUnit, 2);

                    $totals[ $column->id ]               = $totalPerUnit;
                    $quantityPerUnitArray[ $column->id ] = $quantityPerUnit;

                    $this->updateElementTotals($column, $totalPerUnit, $billItem['id'], $quantityPerUnit);
                }
            }

            self::addRow($itemPage, $id, $rowIndex, $descriptionRow, $level, $type, $unit, $rateAfterMarkup, $quantityPerUnitArray, $totals);
        }
    }

    protected function setOrientationAndSize($orientation = false, $pageFormat = false)
    {
        if( $orientation )
        {
            $this->orientation = $orientation;
            $this->setPageFormat($this->generatePageFormat(( $pageFormat ) ? $pageFormat : self::PAGE_FORMAT_A4));
        }
        else
        {
            $count = count($this->tendererIds);

            if( $count <= 4 )
            {
                $this->orientation = ( $count <= 1 ) ? self::ORIENTATION_PORTRAIT : self::ORIENTATION_LANDSCAPE;
                $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
            }
            else
            {
                $this->orientation = self::ORIENTATION_LANDSCAPE;
                $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A3));
            }
        }
    }

    protected function setPageFormat($pageFormat)
    {
        $this->pageFormat = $pageFormat;
    }

    protected function generatePageFormat($format)
    {
        switch(strtoupper($format))
        {
            /*
                *  For now we only handle A4 format. If there's necessity to handle other page
                * format we need to add to this method
                */
            case self::PAGE_FORMAT_A4 :
                $width  = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
                $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
                $pf     = array(
                    'page_format'       => self::PAGE_FORMAT_A4,
                    'minimum-font-size' => $this->fontSize,
                    'width'             => $width,
                    'height'            => $height,
                    'pdf_margin_top'    => 8,
                    'pdf_margin_right'  => 10,
                    'pdf_margin_bottom' => 3,
                    'pdf_margin_left'   => 10
                );
                break;
            case self::PAGE_FORMAT_A3 :
                $width  = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 1000;
                $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 1000 : 800;
                $pf     = array(
                    'page_format'       => self::PAGE_FORMAT_A3,
                    'minimum-font-size' => $this->fontSize,
                    'width'             => $width,
                    'height'            => $height,
                    'pdf_margin_top'    => 8,
                    'pdf_margin_right'  => 10,
                    'pdf_margin_bottom' => 3,
                    'pdf_margin_left'   => 10
                );
                break;
            // DEFAULT ISO A4
            default:
                $width  = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
                $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
                $pf     = array(
                    'page_format'       => self::PAGE_FORMAT_A4,
                    'minimum-font-size' => $this->fontSize,
                    'width'             => $width,
                    'height'            => $height,
                    'pdf_margin_top'    => 8,
                    'pdf_margin_right'  => 10,
                    'pdf_margin_bottom' => 3,
                    'pdf_margin_left'   => 10
                );
        }

        return $pf;
    }

    protected function setMaxCharactersPerLine()
    {
        $this->MAX_CHARACTERS = 56;

        if( $this->fontSize == 10 )
        {
            $this->MAX_CHARACTERS = 64;
        }
    }

    public function getMaxRows()
    {
        $pageFormat = $this->getPageFormat();

        switch($pageFormat['page_format'])
        {
            case self::PAGE_FORMAT_A4:
                if( $this->orientation == self::ORIENTATION_PORTRAIT )
                {
                    if( count($this->tenderers) )
                    {
                        if( count($this->tenderers) <= 1 )
                        {
                            $maxRows = 54;
                        }
                        else
                        {
                            $maxRows = 64;
                        }
                    }
                    else
                    {
                        $maxRows = 54;
                    }
                }
                else
                {
                    $maxRows = 34;
                }
                break;
            default:
                $maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 108 : 54;
        }

        return $maxRows;
    }

    public function getElements()
    {
        if( count($this->elementIds) === 0 )
        {
            return array();
        }

        return DoctrineQuery::create()
            ->select('e.id, e.description, fc.column_name, fc.value, fc.final_value')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $this->bill->id)
            ->andWhereIn('e.id', $this->elementIds)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();
    }

    public function getTenderers()
    {
        $tenderer = array();

        if( count($this->tendererIds) )
        {
            $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, t.original_tender_value AS grand_total
            FROM " . TenderSettingTable::getInstance()->getTableName() . " t
            JOIN " . CompanyTable::getInstance()->getTableName() . " c ON c.id = t.awarded_company_id
            WHERE t.project_structure_id = " . $this->bill->root_id . " AND c.id IN (" . implode(',', $this->tendererIds) . ")");

            $stmt->execute();
            $selectedTenderer = $stmt->fetch(PDO::FETCH_ASSOC);

            if( $selectedTenderer )
            {
                $selectedTenderer['selected'] = true;

                $tenderer[] = $selectedTenderer;
            }

            $companySqlStatement = ( $selectedTenderer['id'] > 0 ) ? "AND c.id <> " . $selectedTenderer['id'] : null;

            if( count($this->elementIds) )
            {
                $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, xref.id AS tender_company_id, xref.show,
				COALESCE(SUM(r.grand_total), 0) AS total
				FROM " . CompanyTable::getInstance()->getTableName() . " c
				JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
				LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
				WHERE xref.project_structure_id = " . $this->project->id . "
				AND c.id IN (" . implode(', ', $this->tendererIds) . ") {$companySqlStatement}
				AND c.deleted_at IS NULL GROUP BY c.id, xref.show, xref.id ORDER BY c.id ASC");

                $stmt->execute();

                foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $contractor)
                {
                    $tenderer[] = $contractor;
                }
            }
        }

        return $tenderer;
    }

    private function calculateTotalByElementAndTypes($contractorRates, $itemOriginalQuantityByTypes, $itemOriginalRates)
    {
        foreach($this->billColumnSettings as $column)
        {
            $data['unitTypes'][] = array(
                'id'   => $column->id,
                'name' => $column->name
            );

            foreach($this->elements as $key => $element)
            {
                $elementId            = $element['id'];
                $itemQuantities       = (array_key_exists($column->id, $itemOriginalQuantityByTypes) && array_key_exists($elementId, $itemOriginalQuantityByTypes)) ? $itemOriginalQuantityByTypes[ $column->id ][ $elementId ] : [];
                $elementEstimateTotal = 0;

                // will calculate item's estimate amount first
                foreach($itemQuantities as $itemId => $itemQuantity)
                {
                    $itemRate = isset( $itemOriginalRates[ $itemId ] ) ? $itemOriginalRates[ $itemId ] : 0;

                    $elementEstimateTotal += $itemQuantity * $itemRate;
                }

                $this->elements[ $key ]['estimate_total'][ $column->id ] = $elementEstimateTotal;

                // after that only count contractor's total
                foreach($contractorRates as $contractorId => $contractor)
                {
                    $contractorTotal = 0;

                    foreach($itemQuantities as $itemId => $itemQuantity)
                    {
                        if( ! isset( $contractor[ $element['id'] ][ $itemId ] ) )
                        {
                            continue;
                        }

                        $contractorRate = $contractor[ $element['id'] ][ $itemId ];

                        $contractorTotal += $itemQuantity * $contractorRate;
                    }

                    $this->elements[ $key ]['contractor_total'][ $column->id ][ $contractorId ] = $contractorTotal;
                }
            }
        }
    }

    private function startElementTotals($elementId)
    {
        // Set element Total to 0.
        foreach($this->billColumnSettings as $column)
        {
            $this->estimateElementTotals[ $elementId ][ $column->id ] = 0;

            foreach($this->tendererIds as $key => $tendererId)
            {
                $this->contractorElementTotals[ $tendererId ][ $elementId ][ $column->id ] = 0;
            }
        }
    }

    private function updateElementTotals($column, $totalPerUnit, $billId, $quantityPerUnit)
    {
        // Update estimate element totals.
        $this->estimateElementTotals[ $this->currentElementId ][ $column->id ] += $totalPerUnit;

        // Update contractor element totals.
        foreach($this->tendererIds as $key => $tendererId)
        {
            if( isset( $this->contractorRates[ $tendererId ][ $this->currentElementId ][ $billId ] ) )
            {
                $this->contractorElementTotals[ $tendererId ][ $this->currentElementId ][ $column->id ] += $this->contractorRates[ $tendererId ][ $this->currentElementId ][ $billId ] * $quantityPerUnit;
            }
        }
    }

}