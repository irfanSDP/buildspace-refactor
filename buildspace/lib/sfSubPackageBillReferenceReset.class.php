<?php

class sfSubPackageBillReferenceReset extends sfBillReferenceReset
{
    /*Used Only On Push SubPackage*/
    const TOTAL_BILL_ITEM_PROPERTY	  = 11;
    const ROW_BILL_ITEM_PAGE_NO 	  = 9;
    const ROW_BILL_ITEM_ELEMENT_NO 	  = 10;

    protected $subPackageInsert = true;

    public function generateBillItemPages(array $billItems, array $billColumnSettings, $elementInfo, $pageCount, $ancestors, &$itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, $newPage = false)
    {
        $itemPages[$pageCount] = [];
        $layoutSettings        = $this->printSettings['layoutSetting'];
        $maxRows               = $this->getMaxRows();

        $blankRow                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $blankRow[self::ROW_BILL_ITEM_ID]           = -1;//id
        $blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
        $blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null;//description
        $blankRow[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
        $blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK;//type
        $blankRow[self::ROW_BILL_ITEM_UNIT]         = null;//unit
        $blankRow[self::ROW_BILL_ITEM_RATE]         = null;//rate
        $blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//quantity per unit
        $blankRow[self::ROW_BILL_ITEM_INCLUDE]      = null;//include
        $blankRow[self::ROW_BILL_ITEM_PAGE_NO]      = null;//pageNO
        $blankRow[self::ROW_BILL_ITEM_ELEMENT_NO]   = null;

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
        $rowCount = 1;

        /*
         * Always display element description at start of every page.
         */
        $descriptionCont = $pageCount > 1 ? $layoutSettings['contdPrefix'] : null;

        if ( $this->printSettings['layoutSetting']['printContdEndDesc'] )
        {
            $occupiedRows = Utilities::justify($elementInfo['description']." ".$descriptionCont, $this->MAX_CHARACTERS);
        }
        else
        {
            $occupiedRows = Utilities::justify($descriptionCont." ".$elementInfo['description'], $this->MAX_CHARACTERS);
        }

        foreach($occupiedRows as $occupiedRow)
        {
            $row    = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
            $row[self::ROW_BILL_ITEM_ID] = -1;//id
            $row[self::ROW_BILL_ITEM_ROW_IDX] = null;//row index
            $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;//description
            $row[self::ROW_BILL_ITEM_LEVEL] = 0;//level
            $row[self::ROW_BILL_ITEM_TYPE] = self::ROW_TYPE_ELEMENT;//type
            $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
            $row[self::ROW_BILL_ITEM_RATE] = null;//rate
            $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//quantity per unit
            $row[self::ROW_BILL_ITEM_INCLUDE] = null;//include
            $row[self::ROW_BILL_ITEM_PAGE_NO]      = null;//pageNO
            $row[self::ROW_BILL_ITEM_ELEMENT_NO]   = null;

            array_push($itemPages[$pageCount], $row);
        }

        //blank row
        array_push($itemPages[$pageCount], $blankRow);

        $rowCount += count($occupiedRows)+1;//plus one blank row

        $itemIndex    = 1;
        $counterIndex = 0;//display item's index in BQ

        foreach($billItems as $x => $billItem)
        {
            $ancestors = $billItem['level'] == 0 ? array() : $ancestors;

            if ($billItem['type'] == BillItem::TYPE_HEADER OR $billItem['type'] == BillItem::TYPE_HEADER_N)
            {
                $row    = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
                //$row[self::ROW_BILL_ITEM_ID] = $billItem['id'];//id temporary disable
                $row[self::ROW_BILL_ITEM_ROW_IDX] = null;//row index
                $row[self::ROW_BILL_ITEM_DESCRIPTION] = $billItem['description'];//description
                $row[self::ROW_BILL_ITEM_LEVEL] = $billItem['level'];//level
                $row[self::ROW_BILL_ITEM_TYPE] = $billItem['type'];//type
                $row[self::ROW_BILL_ITEM_UNIT] = $billItem['lft']; //set lft info (only for ancestor)
                $row[self::ROW_BILL_ITEM_RATE] = $billItem['rgt']; //set rgt info (only for ancestor)
                $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $billItem['root_id']; //set root_id info (only for )
                $row[self::ROW_BILL_ITEM_INCLUDE] = null;//include
                $row[self::ROW_BILL_ITEM_PAGE_NO] = null;//pageNO
                $row[self::ROW_BILL_ITEM_ELEMENT_NO] = null;

                $ancestors[$billItem['level']] = $row;

                $ancestors = array_splice($ancestors, 0, $billItem['level']+1);
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
                        $rowCount++;
                        continue;
                    }

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
                        foreach($occupiedRows as $occupiedRow)
                        {
                            $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
                            $row[self::ROW_BILL_ITEM_ID] = $ancestor[self::ROW_BILL_ITEM_ID];//id
                            $row[self::ROW_BILL_ITEM_ROW_IDX] = null;//row index
                            $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;//description
                            $row[self::ROW_BILL_ITEM_LEVEL] = $ancestor[self::ROW_BILL_ITEM_LEVEL];//level
                            $row[self::ROW_BILL_ITEM_TYPE] = $ancestor[self::ROW_BILL_ITEM_TYPE];//type
                            $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
                            $row[self::ROW_BILL_ITEM_RATE] = null;//rate
                            $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//qty per unit
                            $row[self::ROW_BILL_ITEM_INCLUDE] = true;//include
                            $row[self::ROW_BILL_ITEM_PAGE_NO] = null;//pageNO
                            $row[self::ROW_BILL_ITEM_ELEMENT_NO] = null;

                            array_push($itemPages[$pageCount], $row);

                            $rowCount++;
                        }

                        //blank row
                        array_push($itemPages[$pageCount], $blankRow);
                        $rowCount++;

                        unset($occupiedRow, $occupiedRows, $ancestor);
                    }
                }
            }

            if ($billItem['type'] == BillItem::TYPE_HEADER_N and !$newPage)
            {
                unset($occupiedRows);

                $pageCount++;
                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                break;
            }

            /*
            * Create extra rows for BillItem::TYPE_ITEM_PC_RATE;
            */
            if ($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE)
            {
                $primeCostRateRows = $this->generatePrimeCostRateRows($billItem['id']);
                $rowCount += count($primeCostRateRows);
            }

            $occupiedRows = $this->calculateBQItemDescription($billItem);
            $rowCount += count($occupiedRows);

            if($rowCount <= $maxRows)
            {
                foreach($occupiedRows as $key => $occupiedRow)
                {
                    if($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID)
                    {
                        $counterIndex++;
                    }

                    $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                    $row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID) ? $billItem['bill_ref_char'] : null;
                    $row[self::ROW_BILL_ITEM_PAGE_NO]  = ($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID) ? $billItem['bill_ref_page_no'] : null;
                    $row[self::ROW_BILL_ITEM_ELEMENT_NO]  = ($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID) ? $billItem['bill_ref_element_no'] : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL] = $billItem['level'];
                    $row[self::ROW_BILL_ITEM_TYPE] = $billItem['type'];

                    //Generate Bill Ref
                    if($key+1 == $occupiedRows->count())
                    {
                        $this->generateBillReference($billItem, $counterIndex, $pageCount);
                    }

                    $this->savePageItemReference($elementInfo, $pageCount, $key, $occupiedRows, $billItem);

                    // will need to include ITEM NO ID also so that it can be correctly transferred
                    // as an item to post contract module
                    if ( $key+1 == $occupiedRows->count() )
                    {
                        $row[self::ROW_BILL_ITEM_ID] = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom'];
                        $row[self::ROW_BILL_ITEM_RATE] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $ratesAfterMarkup) ? $ratesAfterMarkup[$billItem['id']] : 0);

                        $quantityPerUnit = array();

                        foreach($billColumnSettings as $billColumnSetting)
                        {
                            $itemQuantity = array_key_exists($billItem['id'], $itemQuantities[$billColumnSetting['id']]) ? $itemQuantities[$billColumnSetting['id']][$billItem['id']][0] : 0;

                            $quantityPerUnit[$billColumnSetting['id']] = $itemQuantity;

                            $includeStatus[$billColumnSetting['id']] = array_key_exists($billItem['id'], $itemIncludeStatus[$billColumnSetting['id']]) ? $itemIncludeStatus[$billColumnSetting['id']][$billItem['id']] : null;

                        }

                        $row[self::ROW_BILL_ITEM_INCLUDE] = $includeStatus;

                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $quantityPerUnit;
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID]           = null;
                        $row[self::ROW_BILL_ITEM_UNIT]         = null; //unit
                        $row[self::ROW_BILL_ITEM_RATE]         = null; //rate
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null; //qty per unit
                        $row[self::ROW_BILL_ITEM_INCLUDE]      = true; // include

                        if ( $key+1 == $occupiedRows->count() && $billItem['type'] == BillItem::TYPE_NOID )
                        {
                            $row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom'];//unit
                        }
                    }

                    array_push($itemPages[$pageCount], $row);

                    unset($row);
                }

                if ($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE)
                {
                    foreach($primeCostRateRows as $primeCostRateRow)
                    {
                        array_push($itemPages[$pageCount], $primeCostRateRow);
                    }
                }

                //blank row
                array_push($itemPages[$pageCount], $blankRow);

                $rowCount++;//plus one blank row;
                $itemIndex++;
                $newPage = false;

                unset($billItems[$x], $row);
            }
            else
            {
                $pageCount++;

                $this->lastPageCount = $pageCount;

                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                break;
            }
        }
    }
}