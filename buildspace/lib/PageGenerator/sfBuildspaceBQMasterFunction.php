<?php

abstract class sfBuildspaceBQMasterFunction {

    const TOTAL_BILL_ITEM_PROPERTY    = 13;
    const ROW_BILL_ITEM_ID            = 0;
    const ROW_BILL_ITEM_ROW_IDX       = 1;
    const ROW_BILL_ITEM_DESCRIPTION   = 2;
    const ROW_BILL_ITEM_LEVEL         = 3;
    const ROW_BILL_ITEM_TYPE          = 4;
    const ROW_BILL_ITEM_UNIT          = 5;
    const ROW_BILL_ITEM_RATE          = 6;
    const ROW_BILL_ITEM_QTY_PER_UNIT  = 7;
    const ROW_BILL_ITEM_INCLUDE       = 8;
    const ROW_BILL_ITEM_LEFT          = 10;
    const ROW_BILL_ITEM_RIGHT         = 11;
    const ROW_BILL_ITEM_TOTAL         = 12;

    const ROW_TYPE_SUMMARY_PAGE_TITLE = -0;
    const ROW_TYPE_ELEMENT            = -1;
    const ROW_TYPE_BLANK              = -2;
    const ROW_TYPE_PC_RATE            = -4;
    const ROW_TYPE_LAST_COLLECTION    = -8;
    const ROW_TYPE_COLLECTION_TITLE   = -16;

    const EXCEL_TYPE_SINGLE           = 1;
    const EXCEL_TYPE_MULTIPLE         = 2;

    const PAGE_FORMAT_A3              = 'A3';
    const PAGE_FORMAT_A4              = 'A4';

    const ORIENTATION_PORTRAIT        = 'Portrait';
    const ORIENTATION_LANDSCAPE       = 'Landscape';

    const DESC_FORMAT_FULL_LINE       = "fullLineDescription";
    const DESC_FORMAT_ONE_LINE        = "oneLineDescription";

    const ELEMENT_DESC_FIRST_ROW      = "START";
    const ELEMENT_DESC_LAST_ROW       = "END";

    const DESCRIPTION_ELLIPSIS        = "...";

    const PC_RATE_TABLE_SIZE          = 7;

    public $newBillRef                = [];
    public $printSettings;

    public $elementsOrder = [];
    public $billReferenceToUpdate = [];
    public $itemIdsToRemoveReference = [];
    public $pageFormat;
    public $descriptionFormat;
    public $billStructure;
    public $MAX_CHARACTERS;
    public $orientation;
    public $currency;
    protected $project;

    protected $currentElementId;
    protected $currentElement;
    public $rowCount;

    public $totalPage = 0;

    protected $saveOriginalBillInformation = false;
    protected $pagesContainers             = [];

    public function getPageFormat()
    {
        return $this->pageFormat;
    }

    public function getBillStructure()
    {
        return $this->billStructure;
    }

    public function getTableHeaderDescriptionPrefix()
    {
        return $this->printSettings['phrase']['descHeader'];
    }

    public function getTableHeaderUnitPrefix()
    {
        return $this->printSettings['phrase']['unitHeader'];
    }

    public function getTableHeaderQtyPrefix()
    {
        return $this->printSettings['phrase']['qtyHeader'];
    }

    public function getTableHeaderRatePrefix()
    {
        return $this->printSettings['phrase']['rateHeader'];
    }

    public function getTableHeaderAmtPrefix()
    {
        return $this->printSettings['phrase']['amtHeader'];
    }

    public function getSummaryHeaderDescription()
    {
        return $this->printSettings['phrase']['summaryPrefix'];
    }

    public function getTableHeaderSummaryPageNoPrefix()
    {
        return $this->printSettings['phrase']['summaryPageNoPrefix'];
    }

    public function getTopLeftFirstRowHeader()
    {
        return $this->printSettings['phrase']['topLeftRow1'];
    }

    public function getTopLeftSecondRowHeader()
    {
        return $this->printSettings['phrase']['topLeftRow2'];
    }

    public function getTopRightFirstRowHeader()
    {
        return $this->printSettings['phrase']['topRightRow1'];
    }

    public function getBottomLeftFirstRowHeader()
    {
        return $this->printSettings['phrase']['botLeftRow1'];
    }

    public function getBottomLeftSecondRowHeader()
    {
        return $this->printSettings['phrase']['botLeftRow2'];
    }

    public function getTotalPerUnitPrefix()
    {
        return $this->printSettings['phrase']['totalPerUnitPrefix'];
    }

    public function getTotalPerTypePrefix()
    {
        return $this->printSettings['phrase']['totalPerTypePrefix'];
    }

    public function getTotalUnitPrefix()
    {
        return $this->printSettings['phrase']['totalUnitPrefix'];
    }

    public function getTenderPrefix()
    {
        return $this->printSettings['phrase']['tenderPrefix'];
    }

    public function getOrientation()
    {
        return $this->orientation;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getSummaryPageNumberingPrefix($pageNo)
    {
        return $this->printSettings['phrase']["summaryPageNumbering[{$pageNo}]"];
    }

    public function getToCollectionPrefix()
    {
        return $this->printSettings['phrase']['toCollection'];
    }

    public function getCloseGridConfiguration()
    {
        return $this->printSettings['layoutSetting']['closeGrid'];
    }

    public function getPriceFormatting()
    {
        if ( $this->printSettings['layoutSetting']['priceFormat'] == 'opposite' )
        {
            $priceFormatting = array(',', '.');
        }
        else
        {
            $priceFormatting = array('.', ',');
        }

        if ( $this->printSettings['layoutSetting']['printNoCents'] )
        {
            array_push($priceFormatting, 0);
        }
        else
        {
            array_push($priceFormatting, 2);
        }

        return $priceFormatting;
    }

    public function getPrintNoPrice()
    {
        return $this->printSettings['layoutSetting']['printNoPrice'];
    }

    public function getPrintFullDecimal()
    {
        return $this->printSettings['layoutSetting']['printFullDecimal'];
    }

    public function getToggleColumnArrangement()
    {
        return $this->printSettings['layoutSetting']['toggleArgment'];
    }

    public function getPrintElementTitle()
    {
        return $this->printSettings['layoutSetting']['printElementTitle'];
    }

    public function getPrintDollarAndCentColumn()
    {
        return $this->printSettings['layoutSetting']['printDollarCents'];
    }

    public function getCurrencyFormat()
    {
        return array($this->printSettings['phrase']['currencyPrefix'], $this->printSettings['phrase']['centPrefix']);
    }

    public function getRateCommaRemove()
    {
        return $this->printSettings['layoutSetting']['rateCommaRemove'];
    }

    public function getQtyCommaRemove()
    {
        return $this->printSettings['layoutSetting']['qtyCommaRemove'];
    }

    public function getAmtCommaRemove()
    {
        return $this->printSettings['layoutSetting']['amtCommaRemove'];
    }

    public function getPrintAmountOnly()
    {
        return $this->printSettings['layoutSetting']['printAmountOnly'];
    }

    public function getPrintElementInGridOnce()
    {
        return $this->printSettings['layoutSetting']['printElementInGridOnce'];
    }

    public function getIndentItem()
    {
        return $this->printSettings['layoutSetting']['indentItem'];
    }

    public function getPrintElementInGrid()
    {
        return $this->printSettings['layoutSetting']['printElementInGrid'];
    }

    public function getSummaryInGridPrefix()
    {
        return $this->printSettings['phrase']['summaryInGridPrefix'];
    }

    public function getSummaryNextPageBringForwardPrefix()
    {
        return 'Brought forward from';
    }

    public function getCollectionNextPageBringForwardPrefix()
    {
        return 'Brought forward from';
    }

    public function getPageNoPrefix()
    {
        return $this->printSettings['layoutSetting']['pageNoPrefix'];
    }

    public static function gridCurrencyRoundingFormat($rate)
    {
        return number_format($rate, 2, '.', '');
    }

    public function getPrintDateOfPrinting()
    {
        return $this->printSettings['layoutSetting']['printDateOfPrinting'];
    }

    public function getAlignElementToLeft()
    {
        return $this->printSettings['layoutSetting']['alignElementTitleToTheLeft'];
    }

    public function getElementOrder()
    {
        $stmt = $this->pdo->prepare("SELECT e.id, e.description FROM ".BillElementTable::getInstance()->getTableName()." e
            WHERE e.project_structure_id = ".$this->bill->id." AND e.deleted_at IS NULL ORDER BY e.priority ASC");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $elementsOrder = array();
        $counter = 1;

        foreach($result as $element)
        {
            $elementsOrder[$element['id']] = array(
                'description' => $element['description'],
                'order' => $counter
            );

            $counter++;
        }

        return $elementsOrder;
    }

    public function setFontType($fontType)
    {
        switch($fontType)
        {
            case 'Courier':
                return 'Courier New Cyr';
            default:
                return 'Arial Monospaced MT Std';
        }
    }

    public function generatePrimeCostRateRows($billItemId)
    {
        $primeCostRate = BillItemPrimeCostRateTable::getByBillItemId($billItemId, Doctrine_Core::HYDRATE_ARRAY);

        $header                                                     = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $header[self::ROW_BILL_ITEM_ID]                             = null;//id
        $header[self::ROW_BILL_ITEM_ROW_IDX]                        = null;//row index
        $header[self::ROW_BILL_ITEM_DESCRIPTION]                    = 'Rate Per No.';//description
        $header[self::ROW_BILL_ITEM_LEVEL]                          = -1;//level -1 means pc rate header
        $header[self::ROW_BILL_ITEM_TYPE]                           = self::ROW_TYPE_PC_RATE;//type
        $header[self::ROW_BILL_ITEM_UNIT]                           = null;//unit
        $header[self::ROW_BILL_ITEM_RATE]                           = null;//rate
        $header[self::ROW_BILL_ITEM_QTY_PER_UNIT]                   = null;//amount
        $header[self::ROW_BILL_ITEM_INCLUDE]                        = null;//include

        $supplyRateArr                                              = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $supplyRateArr[self::ROW_BILL_ITEM_ID]                      = null;//id
        $supplyRateArr[self::ROW_BILL_ITEM_ROW_IDX]                 = null;//row index
        $supplyRateArr[self::ROW_BILL_ITEM_DESCRIPTION]             = BillItem::ITEM_TYPE_PC_SUPPLIER_RATE_TEXT;//description
        $supplyRateArr[self::ROW_BILL_ITEM_LEVEL]                   = 0;//level -1 means pc rate header
        $supplyRateArr[self::ROW_BILL_ITEM_TYPE]                    = self::ROW_TYPE_PC_RATE;//type
        $supplyRateArr[self::ROW_BILL_ITEM_UNIT]                    = null;//unit
        $supplyRateArr[self::ROW_BILL_ITEM_RATE]                    = null;//rate
        $supplyRateArr[self::ROW_BILL_ITEM_QTY_PER_UNIT]            = $primeCostRate ? $primeCostRate['supply_rate'] : 0;//amount
        $supplyRateArr[self::ROW_BILL_ITEM_INCLUDE]                 = null;//include

        $wastageArr                                                 = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $wastageArr[self::ROW_BILL_ITEM_ID]                         = null;//id
        $wastageArr[self::ROW_BILL_ITEM_ROW_IDX]                    = null;//row index
        $wastageArr[self::ROW_BILL_ITEM_DESCRIPTION]                = 'Wastage';//description
        $wastageArr[self::ROW_BILL_ITEM_LEVEL]                      = 0;//level -1 means pc rate header
        $wastageArr[self::ROW_BILL_ITEM_TYPE]                       = self::ROW_TYPE_PC_RATE;//type
        $wastageArr[self::ROW_BILL_ITEM_UNIT]                       = null;//unit
        $wastageArr[self::ROW_BILL_ITEM_RATE]                       = $primeCostRate ? $primeCostRate['wastage_percentage'] : 0;//rate
        $wastageArr[self::ROW_BILL_ITEM_QTY_PER_UNIT]               = $primeCostRate ? $primeCostRate['wastage_amount'] : 0;//amount
        $wastageArr[self::ROW_BILL_ITEM_INCLUDE]                    = null;//include

        $labourForInstallationArr                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $labourForInstallationArr[self::ROW_BILL_ITEM_ID]           = null;//id
        $labourForInstallationArr[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
        $labourForInstallationArr[self::ROW_BILL_ITEM_DESCRIPTION]  = 'Labour for Installation';//description
        $labourForInstallationArr[self::ROW_BILL_ITEM_LEVEL]        = 0;//level -1 means pc rate header
        $labourForInstallationArr[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_PC_RATE;//type
        $labourForInstallationArr[self::ROW_BILL_ITEM_UNIT]         = null;//unit
        $labourForInstallationArr[self::ROW_BILL_ITEM_RATE]         = null;//rate
        $labourForInstallationArr[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $primeCostRate ? $primeCostRate['labour_for_installation'] : 0;//amount
        $labourForInstallationArr[self::ROW_BILL_ITEM_INCLUDE]      = null;//include

        $otherCostArr                                               = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $otherCostArr[self::ROW_BILL_ITEM_ID]                       = null;//id
        $otherCostArr[self::ROW_BILL_ITEM_ROW_IDX]                  = null;//row index
        $otherCostArr[self::ROW_BILL_ITEM_DESCRIPTION]              = 'Other Cost';//description
        $otherCostArr[self::ROW_BILL_ITEM_LEVEL]                    = 0;//level -1 means pc rate header
        $otherCostArr[self::ROW_BILL_ITEM_TYPE]                     = self::ROW_TYPE_PC_RATE;//type
        $otherCostArr[self::ROW_BILL_ITEM_UNIT]                     = null;//unit
        $otherCostArr[self::ROW_BILL_ITEM_RATE]                     = null;//rate
        $otherCostArr[self::ROW_BILL_ITEM_QTY_PER_UNIT]             = $primeCostRate ? $primeCostRate['other_cost'] : 0;//amount
        $otherCostArr[self::ROW_BILL_ITEM_INCLUDE]                  = null;//include

        $profitArr                                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $profitArr[self::ROW_BILL_ITEM_ID]                          = null;//id
        $profitArr[self::ROW_BILL_ITEM_ROW_IDX]                     = null;//row index
        $profitArr[self::ROW_BILL_ITEM_DESCRIPTION]                 = 'Profit';//description
        $profitArr[self::ROW_BILL_ITEM_LEVEL]                       = 0;//level -1 means pc rate header
        $profitArr[self::ROW_BILL_ITEM_TYPE]                        = self::ROW_TYPE_PC_RATE;//type
        $profitArr[self::ROW_BILL_ITEM_UNIT]                        = null;//unit
        $profitArr[self::ROW_BILL_ITEM_RATE]                        = $primeCostRate ? $primeCostRate['profit_percentage'] : 0;//rate
        $profitArr[self::ROW_BILL_ITEM_QTY_PER_UNIT]                = $primeCostRate ? $primeCostRate['profit_amount'] : 0;//amount
        $profitArr[self::ROW_BILL_ITEM_INCLUDE]                     = null;//include

        $totalArr                                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $totalArr[self::ROW_BILL_ITEM_ID]                           = null;//id
        $totalArr[self::ROW_BILL_ITEM_ROW_IDX]                      = null;//row index
        $totalArr[self::ROW_BILL_ITEM_DESCRIPTION]                  = 'Total';//description
        $totalArr[self::ROW_BILL_ITEM_LEVEL]                        = -2;//level -2 means pc rate total
        $totalArr[self::ROW_BILL_ITEM_TYPE]                         = self::ROW_TYPE_PC_RATE;//type
        $totalArr[self::ROW_BILL_ITEM_UNIT]                         = null;//unit
        $totalArr[self::ROW_BILL_ITEM_RATE]                         = null;//rate
        $totalArr[self::ROW_BILL_ITEM_QTY_PER_UNIT]                 = $primeCostRate ? $primeCostRate['total'] : 0;
        $totalArr[self::ROW_BILL_ITEM_INCLUDE]                      = null;//include

        $rows = new SplFixedArray(self::PC_RATE_TABLE_SIZE);
        $rows->offsetSet(0, $header);
        $rows->offsetSet(1, $supplyRateArr);
        $rows->offsetSet(2, $wastageArr);
        $rows->offsetSet(3, $labourForInstallationArr);
        $rows->offsetSet(4, $otherCostArr);
        $rows->offsetSet(5, $profitArr);
        $rows->offsetSet(6, $totalArr);

        unset($primeCostRate);

        return $rows;
    }

    public function generateBillReference($billItem, $counterIndex, $pageCount, $saveNewRef = false)
    {
        if($billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID && !($billItem['isContinuedDescription'] ?? false))
        {
            $char = Utilities::generateCharFromNumber($counterIndex, $this->printSettings['layoutSetting']['includeIandO']);

            $elementNo = $this->elementsOrder[$billItem['element_id']]['order'];

            if($saveNewRef)
            {
                $this->newBillRef[$billItem['id']] = [
                    'elementNo' => $elementNo,
                    'char'      => $char,
                    'pageCount' => $pageCount
                ];
            }

            if($char != $billItem['bill_ref_char'] || $elementNo != $billItem['bill_ref_element_no'] || $pageCount != $billItem['bill_ref_page_no'])
            {
                $this->billReferenceToUpdate[$billItem['id']] = [];

                $this->billReferenceToUpdate[$billItem['id']] = [
                    'elementId' => $billItem['element_id'],
                    'elementNo' => $elementNo,
                    'char'      => $char,
                    'pageCount' => $pageCount
                ];
            }
        }
        else
        {
            if( $billItem['bill_ref_char'] != null || $billItem['bill_ref_element_no'] != null || $billItem['bill_ref_page_no'] != null )
            {
                array_push($this->itemIdsToRemoveReference, $billItem['id']);
            }
        }
    }

    public function getNewBillRef()
    {
        return $this->newBillRef;
    }

    protected function addendumCollectionPageFormatter($addendumCollectionPageNos, $previousCollectionPageNo)
    {
        $newData               = array();
        $newCollectionPageData = array();

        if ( isset ( $addendumCollectionPageNos ) )
        {
            foreach ( $addendumCollectionPageNos as $addendumCollectionPageNo )
            {
                $newCollectionPageNoFormat = $addendumCollectionPageNo['pageNo'];

                $newData[] = array( 'pageNo' => $newCollectionPageNoFormat );
            }
        }

        $mergePageNos  = array_merge($newData, $previousCollectionPageNo);
        $uniquePageNos = array_map("unserialize", array_unique(array_map("serialize", $mergePageNos)));

        // sort the page in ascending order
        self::sort_by('pageNo', $uniquePageNos);

        foreach ( $uniquePageNos as $uniquePageNo )
        {
            $newCollectionPageData["{$uniquePageNo['pageNo']}"] = NULL;
        }

        return $newCollectionPageData;
    }

    protected function addendumCollectionPageNoFormatter($pageNum, $newPage = false)
    {
        $addendumCount = substr_count($pageNum, '*');
        $pageCount     = preg_replace("/[^0-9]/", "", $pageNum);

        if ( $newPage )
        {
            $pageCount = $pageCount + $this->currentAddedCollectionPage;
        }

        return $pageCount.str_repeat('*', $addendumCount);
    }

    protected function sort_by($field, &$arr, $sorting=SORT_ASC, $case_insensitive=true)
    {
        if(is_array($arr) && (count($arr)>0) && ( ( is_array($arr[0]) && isset($arr[0][$field]) ) || ( is_object($arr[0]) && isset($arr[0]->$field) ) ) )
        {
            if($case_insensitive==true)
            {
                $strcmp_fn = "strnatcasecmp";
            }
            else
            {
                $strcmp_fn = "strnatcmp";
            }

            if($sorting==SORT_ASC)
            {
                $fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return '.$strcmp_fn.'($a->'.$field.', $b->'.$field.');
                    }else if(is_array($a) && is_array($b)){
                        return '.$strcmp_fn.'($a["'.$field.'"], $b["'.$field.'"]);
                    }else return 0;
                ');
            }
            else
            {
                $fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return '.$strcmp_fn.'($b->'.$field.', $a->'.$field.');
                    }else if(is_array($a) && is_array($b)){
                        return '.$strcmp_fn.'($b["'.$field.'"], $a["'.$field.'"]);
                    }else return 0;
                ');
            }

            usort($arr, $fn);

            return true;
        }

        return false;
    }

    public static function getProjectSummaryLayoutStyling()
    {
        /* Experimental, Temporary Manually add Layout styling as project doesnt have layout setting */

        $printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, TRUE);
        
        $pageFormat = array(
            'page_format' => sfBuildspaceBQMasterFunction::PAGE_FORMAT_A4,
            'minimum-font-size' => $printSettings['layoutSetting']['fontSize'],
            'width' => 595,
            'height' => 800,
            'pdf_margin_top' => 8,
            'pdf_margin_right' => 4,
            'pdf_margin_bottom' => 1,
            'pdf_margin_left' => 24
        );

        $headStyling = "";

        $elementHeaderStyling = ".elementHeader {text-decoration: underline;font-weight: bold;font-style: normal;color: #000;}";

        foreach($printSettings['headSettings'] as $headSetting)
        {
            $textDecoration = $headSetting['underline'] ? 'underline' : 'none';
            $fontWeight     = $headSetting['bold'] ? 'bold' : 'normal';
            $fontStyle      = $headSetting['italic'] ? 'italic' : 'normal';
            $head           = $headSetting['head'] - 1;

            $headStyling .= '.bqHead'.$head.' {font-weight:'.$fontWeight.';text-decoration: '.$textDecoration.';font-style: '.$fontStyle.';}';
        }

        $styling = '
            body {font-family: "'.$printSettings['layoutSetting']['fontTypeName'].'";font-size:'.$printSettings['layoutSetting']['fontSize'].'px;}
            pre {font-family: "'.$printSettings['layoutSetting']['fontTypeName'].'";}
            .headerTable {font-size: '.$printSettings['layoutSetting']['fontSize'].'px;}
            .footer-table {font-size: '.$printSettings['layoutSetting']['fontSize'].'px;}
            .mainTable {font-size: '.$printSettings['layoutSetting']['fontSize'].'px;min-height:'.$pageFormat['height'].'px;max-height:'.$pageFormat['height'].'px;}';

        $bottomFooterStyling = array();

        if ( $printSettings['phrase']['footHeadBold'] )
        {
            $bottomFooterStyling[] = 'font-weight: bold;';
        }

        if ( $printSettings['phrase']['footHeadUnderline'] )
        {
            $bottomFooterStyling[] = 'text-decoration: underline;';
        }

        if ( $printSettings['phrase']['footHeadItalic'] )
        {
            $bottomFooterStyling[] = 'font-style: italic;';
        }

        $bottomFooterStyling = '.leftFooter {text-align:left; '. implode(" ", $bottomFooterStyling) .'} ';

        $topHeadStyling = array();

        if ( $printSettings['phrase']['eleHeadBold'] )
        {
            $topHeadStyling[] = 'font-weight: bold;';
        }

        if ( $printSettings['phrase']['eleHeadUnderline'] )
        {
            $topHeadStyling[] = 'text-decoration: underline;';
        }

        if ( $printSettings['phrase']['eleHeadItalic'] )
        {
            $topHeadStyling[] = 'font-style: italic;';
        }

        $topHeadStylings = '.leftHeader {text-align:left; '. implode(" ", $topHeadStyling) .'} ';
        $topHeadStylings .= '.rightHeader {text-align:right; '. implode(" ", $topHeadStyling) .'}';

        $styling .= $topHeadStylings.$headStyling.$elementHeaderStyling.$bottomFooterStyling;

        return $styling;
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
            $fontWeight     = $headSetting['bold'] ? 'bold' : 'normal';
            $fontStyle      = $headSetting['italic'] ? 'italic' : 'normal';
            $head           = $headSetting['head'] - 1;

            $headStyling .= '.bqHead'.$head.' {font-weight:'.$fontWeight.';text-decoration: '.$textDecoration.';font-style: '.$fontStyle.';}';
        }

        $style = '
            body {font-family: "'.$this->fontType.'";font-size:'.$this->fontSize.'px;}
            pre {font-family: "'.$this->fontType.'";}
            .headerTable {font-size: '.$this->fontSize.'px;}
            .fulljustify {
                text-align: justify;
            }
            .fulljustify:after {
                content: "";
                display: inline-block;
                width: 100%;
            }
            .footer-table {font-size: '.$this->fontSize.'px;}
            .mainTable {font-size: '.$this->fontSize.'px;min-height:'.$pageFormat['height'].'px;max-height:'.$pageFormat['height'].'px;}';

        $topHeadStyling      = $this->topHeadStyling();
        $bottomFooterStyling = $this->bottomFooterStyling();

        $style .= $topHeadStyling.$headStyling.$elementHeaderStyling.$bottomFooterStyling;

        return $style;
    }

    public function topHeadStyling()
    {
        $style = array();

        if ( $this->printSettings['phrase']['eleHeadBold'] )
        {
            $style[] = 'font-weight: bold;';
        }

        if ( $this->printSettings['phrase']['eleHeadUnderline'] )
        {
            $style[] = 'text-decoration: underline;';
        }

        if ( $this->printSettings['phrase']['eleHeadItalic'] )
        {
            $style[] = 'font-style: italic;';
        }

        $styles = '.leftHeader {text-align:left; '. implode(" ", $style) .'} ';
        $styles .= '.rightHeader {text-align:right; '. implode(" ", $style) .'}';

        return $styles;
    }

    public function generatePriceFormat($priceFormat, $printNoCents)
    {
        if ( $priceFormat == 'opposite' )
        {
            $priceFormatting = array(',', '.');
        }
        else
        {
            $priceFormatting = array('.', ',');
        }

        if ( $printNoCents )
        {
            array_push($priceFormatting, 0);
        }
        else
        {
            array_push($priceFormatting, 2);
        }

        return $priceFormatting;
    }

    public function bottomFooterStyling()
    {
        $style = array();

        if ( $this->printSettings['phrase']['footHeadBold'] )
        {
            $style[] = 'font-weight: bold;';
        }

        if ( $this->printSettings['phrase']['footHeadUnderline'] )
        {
            $style[] = 'text-decoration: underline;';
        }

        if ( $this->printSettings['phrase']['footHeadItalic'] )
        {
            $style[] = 'font-style: italic;';
        }

        return '.leftFooter {text-align:left; '. implode(" ", $style) .'} ';
    }

    public function unsetHeadThatIsSameLevelWithItemOnNextPage(&$ancestors, $billItem)
    {
        if ( $billItem['type'] != BillItem::TYPE_HEADER OR $billItem['type'] != BillItem::TYPE_HEADER_N )
        {
            $itemLevel = $billItem['level'];

            if($itemLevel > 0 && count($ancestors) > 0)
            {
                foreach($ancestors as $level => $ancestor)
                {
                    if($level > $itemLevel)
                    {
                        $isChild = ($ancestors[$level][self::ROW_BILL_ITEM_QTY_PER_UNIT] = $billItem['root_id'] && $billItem['lft'] > $ancestors[$level][self::ROW_BILL_ITEM_UNIT] && $billItem['rgt'] < $ancestors[$level][self::ROW_BILL_ITEM_RATE]) ? true : false;

                        if(!$isChild)
                            unset($ancestors[$level]);
                    }
                }
            }

            if (isset($ancestors[$itemLevel]) && $billItem['id'] != $ancestors[$itemLevel][self::ROW_BILL_ITEM_ID])
            {
                unset($ancestors[$itemLevel]);
            }
        }
    }

    public function calculateBQItemDescription(Array $billItem)
    {
        return ($billItem['type'] == BillItem::TYPE_ITEM_HTML_EDITOR or $billItem['type'] == BillItem::TYPE_NOID) ? Utilities::justifyHtmlString($billItem['description'], $this->MAX_CHARACTERS) : Utilities::justify($billItem['description'], $this->MAX_CHARACTERS);
    }

    public function generateBillRefString($billItem, $prefix = false) 
    {   
        if($billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N 
            && $billItem['bill_ref_element_no'] != '' 
            && $billItem['bill_ref_char'] != ''
            && $billItem['bill_ref_page_no'] != '')
        {
            $prefix = ($prefix) ? $prefix : $this->printSettings['layoutSetting']['pageNoPrefix'];

            return BillItemTable::generateBillRef($prefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
        }

        return '';
    }

    public function getMarginTop()
    {
        return $this->pageFormat['pdf_margin_top'];
    }

    public function getMarginBottom()
    {
        return $this->pageFormat['pdf_margin_bottom'];
    }

    public function getMarginLeft()
    {
        return $this->pageFormat['pdf_margin_left'];
    }

    public function getMarginRight()
    {
        return $this->pageFormat['pdf_margin_right'];
    }

    public function getPrintSettings()
    {
        return $this->printSettings;
    }

    public function getPageSize()
    {
        return $this->pageFormat['page_format'];
    }

    public function getNewLineStyle()
    {
        return array(
            'borders' => array(
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'outline'  => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'top'      => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                ),
                'bottom'   => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                )
            )
        );
    }

    public function getTotalStyle()
    {
        return array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'wrapText'   => true
            )
        );
    }

    /**
     * Adds a row to the item page.
     *
     * @param $arrayOfRows
     * @param $id
     * @param $rowIdx
     * @param $description
     * @param $type
     * @param $unit
     * @param $rate
     * @param $qtyPerUnit
     * @param $itemInclude
     */
    protected function addRowToItemPage(&$arrayOfRows, $id, $rowIdx, $description, $level, $type, $unit=null, $rate=null, $qtyPerUnit=null, $itemInclude=null, $lft=null, $rgt=null)
    {
        $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

        $row[self::ROW_BILL_ITEM_ID] = $id;
        $row[self::ROW_BILL_ITEM_ROW_IDX] = $rowIdx;
        $row[self::ROW_BILL_ITEM_DESCRIPTION] = (!empty($description) && !in_array($type, [BillItem::TYPE_ITEM_HTML_EDITOR, BillItem::TYPE_NOID])) ? Utilities::inlineJustify($description, $this->MAX_CHARACTERS) : $description;
        $row[self::ROW_BILL_ITEM_LEVEL] = $level;
        $row[self::ROW_BILL_ITEM_TYPE] = $type;
        $row[self::ROW_BILL_ITEM_UNIT] = $unit;
        $row[self::ROW_BILL_ITEM_RATE] = $rate;
        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $qtyPerUnit;
        $row[self::ROW_BILL_ITEM_INCLUDE] = $itemInclude;
        $row[self::ROW_BILL_ITEM_LEFT] = $lft;
        $row[self::ROW_BILL_ITEM_RIGHT] = $rgt;

        array_push($arrayOfRows, $row);

        unset( $row );
    }

    /**
     * Adds a blank row to the item page.
     *
     * @param $arrayOfRows
     */
    protected function addBlankRowToItemPage(&$arrayOfRows)
    {
        $this->addRowToItemPage($arrayOfRows, -1, null, null, 0, self::ROW_TYPE_BLANK);
    }

    /**
     * Adds the element description.
     *
     * @param $elementInfo
     * @param $pageCount
     * @param $itemPages
     * @param $descriptionCont
     *
     * @return array
     */
    protected function addElementDescription($elementInfo, $pageCount, &$itemPages, $descriptionCont)
    {
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
            $this->addRowToItemPage($itemPages[ $pageCount ], -1, null, $occupiedRow, 0, self::ROW_TYPE_ELEMENT, null, null);
        }

        return $occupiedRows;
    }

    protected function splitDescription($record)
    {
        $descriptionRows = ( isset( $record['type'] ) && ( $record['type'] == BillItem::TYPE_ITEM_HTML_EDITOR or $record['type'] == BillItem::TYPE_NOID ) ) ? Utilities::justifyHtmlString($record['description'], $this->MAX_CHARACTERS) : Utilities::justify($record['description'], $this->MAX_CHARACTERS);

        if( $this->descriptionFormat == self::DESC_FORMAT_ONE_LINE )
        {
            $oneLineDesc        = $descriptionRows[0];
            $descriptionRows    = new SplFixedArray(1);
            $descriptionRows[0] = $oneLineDesc;
        }

        return $descriptionRows;
    }

    /**
     * Returns true if a new page should be started.
     *
     * @param $billItem
     * @param $key
     *
     * @return bool
     */
    protected function startNewPage($billItem, $key)
    {
        // If key != 0 (i.e. Item is not first on the page).
        $newHeader = ( ( $billItem['type'] == BillItem::TYPE_HEADER_N ) && $key != 0 );

        $pageOverflow = ( ( $this->rowCount + count(self::splitDescription($billItem)) ) >= $this->getMaxRows() );

        return $pageOverflow || $newHeader;
    }

    /**
     * Each class should set their own max rows value.
     *
     * @return int
     */
    protected function getMaxRows()
    {
        return 0;
    }

    protected function truncateAncestorsDescription(&$ancestors, $pageCount, $layoutSettings, $maxRows = 3)
    {
        foreach($ancestors as $key => $ancestor)
        {
            $occupiedRows = $this->generateAncestorOccupiedRows($ancestor, $pageCount, $layoutSettings);
            $occupiedRows = $occupiedRows->toArray();

            if(count($occupiedRows) > $maxRows)
            {
                $occupiedRows = array_splice($occupiedRows, 0, $maxRows - 1); // 1 row for ellipsis
                array_push($occupiedRows, self::DESCRIPTION_ELLIPSIS);
            }

            $ancestors[$key][self::ROW_BILL_ITEM_DESCRIPTION] = implode(' ', $occupiedRows);
        }
    }

    protected function generateAncestorOccupiedRows($ancestor, $pageCount, $layoutSettings)
    {
        $descriptionCont = $pageCount > 1 ? $layoutSettings['contdPrefix'] : null;

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

    protected function breakDownItemDescription(Array &$billItems, Array &$billItem, SplFixedArray &$occupiedRows, $rowsAvailable)
    {
        $isLastChunk = false;

        $rowsAvailable = ($rowsAvailable < 0) ? 0 : $rowsAvailable;

        if($rowsAvailable==0)
            return [$rowsAvailable, $isLastChunk];
        
        // Break description into chunks.
        $occupiedRowsArray = $occupiedRows->toArray();
        
        $numberOfEllipsisLines = (array_key_exists('isContinuedDescription', $billItem)) ?  1 : 0;
        $numberOfEllipsisLines = (array_key_exists('isContinuingDescription', $billItem)) ? ($numberOfEllipsisLines+1) : $numberOfEllipsisLines;

        if($occupiedRows->count() > $rowsAvailable)
        {
            switch($billItem['type'])
            {
                case BillItem::TYPE_HEADER:
                case BillItem::TYPE_HEADER_N:
                    throw new PageGeneratorException(PageGeneratorException::ERROR_HEADER_TOO_LONG, [
                        'id'             => $billItem['id'],
                        'rows_available' => $rowsAvailable,
                        'occupied_rows'  => $occupiedRows->count()
                    ]);
                    break;
                default:
                    $billItem['isContinuingDescription'] = true;
                    /*
                     * No ellipsis means it is the first time for the item to be truncated. We have to check for the rows availablity.
                     * We need to spare 1 row for the ellipsis so for a condition where the available rows left less than 2 we need to
                     * set the available rows to 1. If we have ellipsis then just minus out from available rows so the ellipsis rows
                     */
                    if(!$numberOfEllipsisLines || $rowsAvailable <= 2)
                    {
                        $numberOfEllipsisLines = 1;
                    }
            }
        }

        $rowsAvailable -= $numberOfEllipsisLines;

        $rowsAvailable = ($rowsAvailable < 0) ? 0 : $rowsAvailable;

        if($rowsAvailable==0)
            return [$rowsAvailable, $isLastChunk];

        $currentPageOccupiedRows = array_splice($occupiedRowsArray, 0, $rowsAvailable);
        
        if($billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && !$occupiedRowsArray)
        {
            $isLastChunk = true;
            
            //don't need to display ellipsis for the last chunk of truncated description
            if($numberOfEllipsisLines > 0 && array_key_exists('isContinuingDescription', $billItem))
            {
                $numberOfEllipsisLines = ($numberOfEllipsisLines > 0) ? $numberOfEllipsisLines - 1 : $numberOfEllipsisLines;

                $rowsAvailable += $numberOfEllipsisLines;//reset rowsAvailable since we made changes to the number of ellipses
                unset($billItem['isContinuingDescription']);
            }
        }

        $occupiedRows = SplFixedArray::fromArray($currentPageOccupiedRows);

        if($numberOfEllipsisLines)
        {
            $this->addEllipses($billItem, $occupiedRows);

            $rowsAvailable += $numberOfEllipsisLines;

            $currentOccupiedRowsArray = $occupiedRows->toArray();
            $currentPageOccupiedRows = array_splice($currentOccupiedRowsArray, 0, $rowsAvailable);
        }

        // Update bill item object to use first chunk of description.
        $billItem['description'] = implode(" ", $currentPageOccupiedRows);

        if($occupiedRowsArray)
        {
            // Create new item for continued description.
            $continuedBillItem = $billItem;
            $continuedBillItem['description'] = implode(" ", $occupiedRowsArray);
            $continuedBillItem['isContinuedDescription'] = true;
            
            // Add continued description item to the bill item array.
            array_splice($billItems, 0, 1, [$billItem, $continuedBillItem]);
        }

        return [$rowsAvailable, $isLastChunk];
    }

    protected function addEllipses($item, SplFixedArray &$occupiedRows)
    {
        if($item['type'] == BillItem::TYPE_HEADER || $item['type'] == BillItem::TYPE_HEADER_N)
            return;

        if($item['isContinuingDescription'] ?? false)
        {
            $occupiedRows->setSize($occupiedRows->getSize()+1);

            $occupiedRows[$occupiedRows->getSize()-1] = self::DESCRIPTION_ELLIPSIS;
        }

        if($item['isContinuedDescription'] ?? false)
        {
            $occupiedRows = $occupiedRows->toArray();

            array_unshift($occupiedRows, self::DESCRIPTION_ELLIPSIS);

            $occupiedRows = SplFixedArray::fromArray($occupiedRows);
        }
    }

    protected function getItemIncludeStatus()
    {
        $implodedItemIds = null;
        $result = array();

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

        foreach($this->bill->BillColumnSettings->toArray() as $column)
        {
            if ( ! empty($implodedItemIds) )
            {
                $stmt = $this->pdo->prepare("SELECT r.bill_item_id, r.include FROM ".BillItemTypeReferenceTable::getInstance()->getTableName()." r
                WHERE r.bill_item_id IN (".$implodedItemIds.") AND r.bill_column_setting_id = ".$column['id']."
                AND r.deleted_at IS NULL");

                $stmt->execute();

                $includeStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $result[$column['id']] = $includeStatus;
            }
            else
            {
                $result[$column['id']] = null;
            }
        }

        return $result;
    }
}

/*
 * Custom Exception class that accept an array of data so it can be used when the exception is trapped
 */
class PageGeneratorException extends Exception 
{
    const ERROR_HEADER_TOO_LONG = "HEADER DESCRIPTION IS TOO LONG";
    const ERROR_PC_RATE_INSUFFICIENT_ROW = "INSUFFICIENT PC RATE ROWS";
    const ERROR_INSUFFICIENT_ROW = "INSUFFICIENT ROWS";

    private $_data = [];

    public function __construct($message, Array $data) 
    {
        $this->_data = $data;
        parent::__construct($message);
    }

    public function getData()
    {
        return $this->_data;
    }
}