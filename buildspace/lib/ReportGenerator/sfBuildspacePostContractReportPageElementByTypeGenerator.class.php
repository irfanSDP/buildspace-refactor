<?php 

class sfBuildspacePostContractReportPageElementByTypeGenerator extends sfBuildspaceBQMasterFunction
{
    public $pageTitle;
    public $fontSize;
    public $headSettings;
    public $affectedElements;
    public $revision;
    public $typeTotals;
    public $elementTotals;

    const CLAIM_PREFIX  = "Valuation No: ";

    public function __construct($project = false, $bill, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo         = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $this->bill        = $bill;
        $this->project     = $project;
        $project = ($project instanceof ProjectStructure) ? $project : ProjectStructureTable::getInstance()->find($bill->root_id);

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

    public function generatePages($ids = null)
    {
        $pageNumberDescription      = 'Page No. ';
        $pages                      = array();
        $bill                       = $this->bill;
        $postContract               = $this->project->PostContract;
        $project                    = $this->project;
        $elementGrandTotals         = array();
        
        $this->revision = $revision  = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);

        $elements = DoctrineQuery::create()->select('e.id, e.description')
          ->from('BillElement e')
          ->where('e.project_structure_id = ?', $bill->id)
          ->addOrderBy('e.priority ASC')
          ->fetchArray();

        $elements = (count($elements)) ? $elements : array();

        $typeItems = DoctrineQuery::create()
            ->select('t.id, t.post_contract_id, t.bill_column_setting_id, t.counter, t.new_name')
            ->from('PostContractStandardClaimTypeReference t')
            ->leftJoin('t.BillColumnSetting cs')
            ->where('t.post_contract_id = ? AND cs.project_structure_id = ?', array($project->PostContract->id, $bill->id))
            ->orderBy('t.counter ASC')
            ->fetchArray();

        foreach ( $typeItems as $typeItem )
        {
            $typeItemObject                         = new stdClass();
            $typeItemObject->id                     = $typeItem['id'];
            $typeItemObject->bill_column_setting_id = $typeItem['bill_column_setting_id'];

            $elementGrandTotals[$typeItem['bill_column_setting_id']][] = PostContractTable::getTotalClaimRateGroupByElement($bill->id, $typeItemObject, $revision, $postContract->id);

            unset($typeItemObject);
        }

        $elementTotals    = array();//$elements;
        $this->typeTotals = array();

        foreach ( $bill->BillColumnSettings as $billColumnSetting )
        {
            $defaultElementsTotal = PostContractTable::getTotalPerUnitGroupByElement( $bill->id, $billColumnSetting['id'], $project->PostContract->id );
            $typeQuantityCounter = 0;

            $this->typeTotals[$billColumnSetting['id']]['total_per_unit'] = 0;
            $this->typeTotals[$billColumnSetting['id']]['up_to_date_percentage'] = 0;
            $this->typeTotals[$billColumnSetting['id']]['up_to_date_amount'] = 0;

            foreach($elements as $key => $element)
            {
                $elementId = $element['id'];

                $elementTotals[$elementId][$billColumnSetting['id']]['grand_total']                  = 0;
                $elementTotals[$elementId][$billColumnSetting['id']]['type_total_percentage']        = 0;
                $elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'] = 0;
            }

            // use PostContractStandardClaimTypeReference's if available
            if ( isset($elementGrandTotals[$billColumnSetting['id']]) )
            {
                foreach ( $elementGrandTotals[$billColumnSetting['id']] as $elementGrandTotal )
                {
                    foreach($elements as $key => $element)
                    {
                        $elementId = $element['id'];

                        if ( isset($elementGrandTotal[$elementId]) )
                        {
                            $elementTotals[$elementId][$billColumnSetting['id']]['grand_total']   += $elementGrandTotal[$elementId][0]['total_per_unit'];
                            $elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'] += $elementGrandTotal[$elementId][0]['up_to_date_amount'];
                        }
                    }

                    $typeQuantityCounter++;
                }
            }

            // assign element total for unit that haven't been instantiate yet.
            while ( $typeQuantityCounter < $billColumnSetting['quantity'] )
            {
                foreach($elements as $key => $element)
                {
                    $elementId = $element['id'];

                    if ( isset($defaultElementsTotal[$element['id']]) )
                    {
                        $elementTotals[$elementId][$billColumnSetting['id']]['grand_total'] += $defaultElementsTotal[$element['id']][0]['total_per_unit'];
                    }
                }

                $typeQuantityCounter++;
            }

            foreach($elements as $key => $element)
            {
                $elementId = $element['id'];

                if ( $elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'] > 0 )
                {
                    $elementTotals[$elementId][$billColumnSetting['id']]['type_total_percentage'] = Utilities::prelimRounding(Utilities::percent($elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'], $elementTotals[$elementId][$billColumnSetting['id']]['grand_total']));
                }

                $this->typeTotals[$billColumnSetting['id']]['up_to_date_amount'] += $elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'];
                $this->typeTotals[$billColumnSetting['id']]['total_per_unit']+= $elementTotals[$elementId][$billColumnSetting['id']]['grand_total'];
            }

            $this->typeTotals[$billColumnSetting['id']]['up_to_date_percentage'] = ($this->typeTotals[$billColumnSetting['id']]['total_per_unit'] > 0) ? ($this->typeTotals[$billColumnSetting['id']]['up_to_date_amount']/$this->typeTotals[$billColumnSetting['id']]['total_per_unit'] * 100) : 0;

            unset($billColumnSetting);
        }

        $this->elementTotals = $elementTotals;

        $this->generateBillElementPages($elements, 1, array(), $itemPages);

        $pages = SplFixedArray::fromArray($itemPages);

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
                        $row[self::ROW_BILL_ITEM_RATE]  = null;
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT]  = null;
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID] = null;
                        $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//unit
                        $row[self::ROW_BILL_ITEM_RATE] = null;//unit
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
        $this->orientation = self::ORIENTATION_LANDSCAPE;
        $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
    }

    public function setPageFormat( $pageFormat )
    {
        $this->pageFormat = $pageFormat;
    }

    protected function generatePageFormat($format)
    {
        $width = 800;

        $height = 595;

        return array(
            'page_format' => self::PAGE_FORMAT_A4,
            'minimum-font-size' => $this->fontSize,
            'width' => $width,
            'height' => $height,
            'pdf_margin_top' => 8,
            'pdf_margin_right' => 8,
            'pdf_margin_bottom' => 3,
            'pdf_margin_left' => 8
        );
    }

    public function setMaxCharactersPerLine()
    {
        $this->MAX_CHARACTERS = 48;
    }

    public function getMaxRows()
    {
        return $maxRows = 33;
    }
}