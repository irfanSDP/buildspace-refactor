<?php 

class sfBuildspacePostContractReportPageElementGenerator extends sfBuildspaceBQMasterFunction
{
    public $pageTitle;
    public $sortingType;
    public $elementIds;
    public $fontSize;
    public $headSettings;
    public $affectedElements;
    public $revision;
    public $typeRef;

    const CLAIM_PREFIX  = "Valuation No: ";

    const TOTAL_BILL_ITEM_PROPERTY      = 12;
    const ROW_CLAIM_PREVIOUS            = 9;
    const ROW_CLAIM_WORKDONE            = 10;
    const ROW_CLAIM_CURRENT             = 11;
    const ROW_BILL_ITEM_CONTRACT_AMOUNT = 8;

    public function __construct($project = false, $bill, $elementIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo         = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $this->bill        = $bill;
        $this->project     = $project;
        $project = ($project instanceof ProjectStructure) ? $project : ProjectStructureTable::getInstance()->find($bill->root_id);

        $this->elementIds           = $elementIds;
        
        $this->pageTitle         = $pageTitle;
        $this->currency          = $project->MainInformation->Currency;
        $this->descriptionFormat = $descriptionFormat;

        $this->setOrientationAndSize();

        $this->printSettings  = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, TRUE);
        $this->fontSize       = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType       = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings   = $this->printSettings['headSettings'];

        self::setMaxCharactersPerLine();
    }

    public function generatePages($typeRef)
    {
        $pageNumberDescription      = 'Page No. ';
        $pages                      = array();
        $typeItem = $this->typeRef  = $typeRef;
        $bill                       = $this->bill;
        $postContract               = $this->project->PostContract;
        $billStructure              = array();
        $elements                   = array();
        
        $this->revision = $revision  = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);

        if ( count($this->elementIds) > 0 )
        {
            $elements = DoctrineQuery::create()
            ->select('e.id, e.description, e.note')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->andWhereIn('e.id', $this->elementIds)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

            $elementGrandTotals = PostContractTable::getTotalClaimRateGroupByElement($bill->id, $typeItem, $revision, $postContract->id);
        }

        foreach($elements as $key => $element)
        {
            $elementId = $element['id'];

            if(array_key_exists($elementId, $elementGrandTotals))
            {
                $prevAmount = $elementGrandTotals[$elementId][0]['prev_amount'];
                $currentAmount = $elementGrandTotals[$elementId][0]['current_amount'];
                $prevPercentage = $elementGrandTotals[$elementId][0]['prev_percentage'];
                $totalPerUnit = $elementGrandTotals[$elementId][0]['total_per_unit'];
                $upToDateAmount = $elementGrandTotals[$elementId][0]['up_to_date_amount'];

                $elements[$key]['total_per_unit']        = $totalPerUnit;
                $elements[$key]['prev_percentage']       = ($totalPerUnit > 0) ? number_format(($prevAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
                $elements[$key]['prev_amount']           = $prevAmount;
                $elements[$key]['current_percentage']    = ($totalPerUnit > 0) ? number_format(($currentAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
                $elements[$key]['current_amount']        = $currentAmount;
                $elements[$key]['up_to_date_percentage'] = ($totalPerUnit > 0) ? number_format(($upToDateAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
                $elements[$key]['up_to_date_amount']     = $upToDateAmount;
                $elements[$key]['up_to_date_qty']        = $elementGrandTotals[$elementId][0]['up_to_date_qty'];
            }

            $elements[$key]['has_note']          = ($element['note'] != null && $element['note'] != '') ? true : false;
            $elements[$key]['claim_type_ref_id'] = $typeItem->id;
            $elements[$key]['relation_id']       = $bill->id;
        }

        $this->generateBillElementPages($elements, 1, array(), $itemPages);

        $pages = SplFixedArray::fromArray($itemPages);

        $this->typeTotals = (count($this->elementIds)) ? PostContractTable::getTotalClaimRateByTypeAndElementIds($bill->id, $this->elementIds, $typeItem, $revision, $postContract->id) : array();

        return $pages;
    }

    public function generateBillElementPages(Array $billElements, $pageCount, $ancestors, &$itemPages, $newPage = false)
    {
        $itemPages[$pageCount] = array();
        $layoutSettings        = $this->printSettings['layoutSetting'];
        $maxRows               = $this->getMaxRows();
        $ancestors = (is_array($ancestors) && count($ancestors)) ? $ancestors : array();

        $blankRow                                       = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $blankRow[self::ROW_BILL_ITEM_ID]               = -1;//id
        $blankRow[self::ROW_BILL_ITEM_ROW_IDX]          = null;//row index
        $blankRow[self::ROW_BILL_ITEM_DESCRIPTION]      = null;//description
        $blankRow[self::ROW_BILL_ITEM_LEVEL]            = 0;//level
        $blankRow[self::ROW_BILL_ITEM_TYPE]             = self::ROW_TYPE_BLANK;//type
        $blankRow[self::ROW_BILL_ITEM_UNIT]             = null;//unit
        $blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//unit
        $blankRow[self::ROW_BILL_ITEM_RATE] = null;//unit
        $blankRow[self::ROW_BILL_ITEM_CONTRACT_AMOUNT]  = null;
        $blankRow[self::ROW_CLAIM_WORKDONE]             = null;
        $blankRow[self::ROW_CLAIM_PREVIOUS]             = null;
        $blankRow[self::ROW_CLAIM_CURRENT]              = null;

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
        $rowCount = 1;

        foreach($ancestors as $k => $row)
        {
            array_push($itemPages[$pageCount], $row);
            $rowCount += 1;
            unset($row); 
        }

        $ancestors = array();
        $itemIndex    = 1;
        $counterIndex = 0;//display item's index in BQ

        foreach($billElements as $x => $billElement)
        {
            $occupiedRows = Utilities::justify($billElements[$x]['description'], $this->MAX_CHARACTERS);

            if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
            {
                $oneLineDesc = $occupiedRows[0];
                $occupiedRows = new SplFixedArray(1);
                $occupiedRows[0] = $oneLineDesc;
            }

            $rowCount += count($occupiedRows);

            if($rowCount <= $maxRows)
            {
                foreach($occupiedRows as $key => $occupiedRow)
                {
                    if($key == 0)
                    {
                        $counterIndex++;
                    }
                    
                    $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                    $row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0) ? $counterIndex : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL] = null;
                    $row[self::ROW_BILL_ITEM_TYPE]  = null;

                    if($key+1 == $occupiedRows->count())
                    {  
                        $row[self::ROW_BILL_ITEM_ID]    = $billElement['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT]  = null;
                        $row[self::ROW_BILL_ITEM_CONTRACT_AMOUNT] = self::gridCurrencyRoundingFormat((array_key_exists('total_per_unit', $billElement)) ? $billElement['total_per_unit'] : 0);
                        $row[self::ROW_BILL_ITEM_RATE]  = null;
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT]  = null;
                        $row[self::ROW_CLAIM_WORKDONE]  = array(
                            'up_to_date_percentage' => (array_key_exists('up_to_date_percentage', $billElement)) ? $billElement['up_to_date_percentage'] : 0, 
                            'up_to_date_amount' => (array_key_exists('up_to_date_amount', $billElement)) ? $billElement['up_to_date_amount'] : 0, 
                            'up_to_date_qty' => (array_key_exists('up_to_date_qty', $billElement)) ? $billElement['up_to_date_qty'] : 0
                        );
                        $row[self::ROW_CLAIM_PREVIOUS]  = array(
                            'prev_percentage' => (array_key_exists('prev_percentage', $billElement)) ? $billElement['prev_percentage'] : 0, 
                            'prev_amount' => (array_key_exists('prev_amount', $billElement)) ? $billElement['prev_amount'] : 0
                        );
                        $row[self::ROW_CLAIM_CURRENT]   = array(
                            'current_percentage' => (array_key_exists('prev_percentage', $billElement)) ? $billElement['current_percentage'] : 0,
                            'current_amount' => (array_key_exists('prev_percentage', $billElement)) ? $billElement['current_amount'] : 0
                        );

                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID] = null;
                        $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//unit
                        $row[self::ROW_BILL_ITEM_RATE] = null;//unit
                        $row[self::ROW_BILL_ITEM_CONTRACT_AMOUNT]  = null;
                        $row[self::ROW_CLAIM_WORKDONE]             = null;
                        $row[self::ROW_CLAIM_PREVIOUS]             = null;
                        $row[self::ROW_CLAIM_CURRENT]              = null;
                    }

                    array_push($itemPages[$pageCount], $row);

                    unset($row);
                }

                //blank row
                array_push($itemPages[$pageCount], $blankRow);

                $rowCount++;//plus one blank row;
                $itemIndex++;
                $newPage = false;

                unset($billElements[$x], $occupiedRows);
            }
            else
            {
                unset($occupiedRows);

                $pageCount++;
                $this->generateBillElementPages($billElements, $pageCount, $ancestors, $itemPages, true);
                break;
            }
        }
    }

    protected function setOrientationAndSize($orientation = false, $pageFormat = false)
    {
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
    }

    public function setPageFormat( $pageFormat )
    {
        $this->pageFormat = $pageFormat;
    }

    protected function generatePageFormat($format)
    {
        $width = 595;

        $height = 800;

        return $pf = array(
           'page_format' => self::PAGE_FORMAT_A4,
           'minimum-font-size' => $this->fontSize,
           'width' => $width,
           'height' => $height,
           'pdf_margin_top' => 8,
           'pdf_margin_right' => 10,
           'pdf_margin_bottom' => 3,
           'pdf_margin_left' => 10
        );
    }

    public function setMaxCharactersPerLine()
    {
        $this->MAX_CHARACTERS = 48;
    }

    public function getMaxRows()
    {
        return $maxRows = 60;
    }
}