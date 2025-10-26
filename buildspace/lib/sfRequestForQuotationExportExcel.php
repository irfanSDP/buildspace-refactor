<?php

class sfRequestForQuotationExportExcel extends sfBuildspaceRFQExcelGenerator
{
    public $fileInfo;
    public $elementMarkupEnabled = false;
    public $itemMarkupEnabled    = false;
    public $MAX_CHARACTERS       = 120;

    public function __construct(RFQ $rfq, RFQSupplier $rfqSupplier, myCompanyProfile $currentCompany, sfGuardUserProfile $currentUser, $savePath = NULL, $filename)
    {
        $this->rfq            = $rfq;
        $this->rfqSupplier    = $rfqSupplier;
        $this->currentCompany = $currentCompany;
        $this->currentUser    = $currentUser;

        $this->pdo = RFQTable::getInstance()->getConnection()->getDbh();
        $savePath  = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';

        parent::__construct($rfq, $savePath, $filename);
    }

    public function process(Array $rfqItems, $withRate = true, $withQuantity = true)
    {
        // Initiate Excel
        parent::setExcelParameter(TRUE, $withRate, $withQuantity);

        $rfqReferenceNo = $this->rfq->prefix.Utilities::generateRFQReferenceNo($this->rfq->type, $this->rfq->rfq_count);

        $this->createBill($rfqReferenceNo);

        $itemPages       = array();
        $collectionPages = array();

        $elementInfo = array(
            'description'   => $rfqReferenceNo,
            'element_count' => 1
        );

        $this->generateBillItemPages($rfqItems, $elementInfo, 1, array(), $itemPages);

        $description = '';
        $char        = '';

        foreach($itemPages as $pageNo => $page)
        {
            $this->createNewPage($pageNo);

            foreach($page as $item)
            {
                $itemType = $item[4];

                switch($itemType)
                {
                    case self::ROW_TYPE_BLANK:
                        if($description != '' && $prevItemType != '')
                        {
                            if ($prevItemType == ResourceItem::TYPE_HEADER)
                            {
                                parent::newItem();
                                parent::setItemHead( $description,  $prevItemType, $item[3], true );
                            }

                            $description = '';
                        }
                    break;

                    case self::ROW_TYPE_ELEMENT:
                        parent::setElement(array('description' => $item[2]));
                    break;

                    case ResourceItem::TYPE_HEADER:
                        $description.= $item[2];
                        $prevItemType = $item[4];
                    break;

                    default:
                        $description.= $item[2];

                        $char.= $item[1];

                        if($item[0])
                        {
                            parent::newItem();

                            parent::setItem( $item[0], $item[1], $description, $item[10], $itemType , $item[3]);

                            parent::setUnit( $item[5] );

                            parent::setChar( $char );

                            parent::setRate( $item[6] );

                            parent::setQuantity( $item[7] );

                            parent::setAmount();

                            $description = '';

                            $char = '';
                        }
                    break;
                }
            }
        }

        unset($itemPages, $collectionPages);

        // Create Closing footer
        parent::createFooter();

        // write to Excel File
        $this->fileInfo = parent::writeExcel();
    }

    private function generateBillItemPages(Array $billItems, $elementInfo, $pageCount, $ancestors, &$itemPages, $newPage = false)
    {
        $itemPages[$pageCount] = array();

        $blankRow    = new SplFixedArray(9);
        $blankRow[0] = -1;//id
        $blankRow[1] = null;//row index
        $blankRow[2] = null;//description
        $blankRow[3] = 0;//level
        $blankRow[4] = self::ROW_TYPE_BLANK;//type
        $blankRow[5] = null;//unit
        $blankRow[6] = null;//rate
        $blankRow[7] = null;//quantity per unit
        $blankRow[8] = null;//include

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
        $rowCount = 1;

        /*
         * Always display element description at start of every page.
         */
        $descriptionCont = NULL;
        $occupiedRows    = Utilities::justify($descriptionCont." ".$elementInfo['description'], $this->MAX_CHARACTERS);

        foreach($occupiedRows as $occupiedRow)
        {
            $row    = new SplFixedArray(9);
            $row[0] = -1;//id
            $row[1] = null;//row index
            $row[2] = $occupiedRow;//description
            $row[3] = 0;//level
            $row[4] = self::ROW_TYPE_ELEMENT;//type
            $row[5] = null;//unit
            $row[6] = null;//rate
            $row[7] = null;//quantity per unit
            $row[8] = null;//include

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

            if ($billItem['type'] == ResourceItem::TYPE_HEADER)
            {
                $row    = new SplFixedArray(9);
                $row[0] = $billItem['id'];//id
                $row[1] = null;//row index
                $row[2] = $billItem['description'];//description
                $row[3] = $billItem['level'];//level
                $row[4] = $billItem['type'];//type
                $row[5] = $billItem['lft'];//unit
                $row[6] = $billItem['rgt'];//rate
                $row[7] = $billItem['root_id'];//qty per unit
                $row[8] = null;//include

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
                    if ( $ancestor[0] == $billItem['id'] )
                    {
                        $rowCount++;
                        continue;
                    }

                    $descriptionCont = NULL;

                    if ( $this->printSettings['layoutSetting']['printContdEndDesc'] )
                    {
                        $occupiedRows = Utilities::justify($ancestor[2]." ".$descriptionCont, $this->MAX_CHARACTERS);
                    }
                    else
                    {
                        $occupiedRows = Utilities::justify($descriptionCont." ".$ancestor[2], $this->MAX_CHARACTERS);
                    }

                    if ($ancestor[4] == ResourceItem::TYPE_HEADER)
                    {
                        foreach($occupiedRows as $occupiedRow)
                        {
                            $row = new SplFixedArray(9);
                            $row[0] = $ancestor[0];//id
                            $row[1] = null;//row index
                            $row[2] = $occupiedRow;//description
                            $row[3] = $ancestor[3];//level
                            $row[4] = $ancestor[4];//type
                            $row[5] = null;//unit
                            $row[6] = null;//rate
                            $row[7] = null;//qty per unit
                            $row[8] = true;//include

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

            $occupiedRows = Utilities::justify($billItems[$x]['description'], $this->MAX_CHARACTERS);
            $rowCount += count($occupiedRows);

            foreach($occupiedRows as $key => $occupiedRow)
            {
                if($key == 0 && $billItem['type'] != ResourceItem::TYPE_HEADER && $billItem['type'] != ResourceItem::TYPE_NOID)
                {
                    $counterIndex++;
                }

                $row = new SplFixedArray(11);

                $row[1] = ($key == 0 && $billItem['type'] != ResourceItem::TYPE_HEADER && $billItem['type'] != ResourceItem::TYPE_NOID) ? $counterIndex : null;
                $row[2] = $occupiedRow;
                $row[3] = $billItem['level'];
                $row[4] = $billItem['type'];
                $row[9] = NULL;

                if($key+1 == $occupiedRows->count() && $billItem['type'] != ResourceItem::TYPE_HEADER && $billItem['type'] != ResourceItem::TYPE_NOID)
                {
                    $row[0]  = $billItem['rfqItemId'];
                    $row[5]  = $billItem['uom'];
                    $row[6]  = 0;
                    $row[7]  = array($billItem['quantity']);
                    $row[8]  = true;
                    $row[10] = $billItem['remarks'];
                }
                else
                {
                    $row[0] = null;
                    $row[5] = null;//unit
                    $row[6] = null;//rate
                    $row[7] = null;//qty per unit
                    $row[8] = true;// include

                    if ( $key+1 == $occupiedRows->count() && $billItem['type'] == ResourceItem::TYPE_NOID )
                    {
                        $row[5] = $billItem['uom'];//unit
                    }
                }

                array_push($itemPages[$pageCount], $row);
            }

            //blank row
            array_push($itemPages[$pageCount], $blankRow);

            $rowCount++;//plus one blank row;
            $itemIndex++;
            unset($billItems[$x], $row);
        }
    }
}