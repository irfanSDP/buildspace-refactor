<?php

class sfBuildspaceVOSummaryReportGenerator extends sfBuildspaceBQMasterFunction
{
    public $pageTitle;
    public $voIds;
    public $fontSize;
    public $headSettings;

    public $voTotals;

    const CLAIM_PREFIX  = "Valuation No: ";

    const TOTAL_BILL_ITEM_PROPERTY  = 8;
    const ROW_APPROVED              = 5;
    const ROW_OMISSION              = 6;
    const ROW_ADDITION              = 7;

    public function __construct($project = false, $voIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo      = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $this->project  = $project;
        $this->voIds    = $voIds;

        $this->pageTitle         = $pageTitle;
        $this->currency          = $this->project->MainInformation->Currency;
        $this->descriptionFormat = $descriptionFormat;

        $this->setOrientationAndSize();

        $this->printSettings  = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, TRUE);
        $this->fontSize       = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType       = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings   = $this->printSettings['headSettings'];

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $records    = array();
        $itemPages  = array();
        $voTotals   = array();

        if ( count($this->voIds) > 0 )
        {
            $pdo = $this->project->getTable()->getConnection()->getDbh();

            $records = Doctrine_Query::create()
                ->select('vo.id, vo.description, vo.is_approved, vo.updated_at')
                ->from('VariationOrder vo')
                ->where('vo.project_structure_id = ?', $this->project->id)
                ->andWhereIn('vo.id', $this->voIds)
                ->addOrderBy('vo.priority ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            $stmt = $pdo->prepare("SELECT vo.id, COALESCE(COUNT(c.id), 0)
            FROM ".VariationOrderTable::getInstance()->getTableName()." vo
            LEFT JOIN ".VariationOrderClaimTable::getInstance()->getTableName()." c ON c.variation_order_id = vo.id AND c.deleted_at IS NULL
            WHERE vo.project_structure_id = ".$this->project->id." AND vo.deleted_at IS NULL
            GROUP BY vo.id ORDER BY vo.priority");

            $stmt->execute();
            $claimCount = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

            $stmt = $pdo->prepare("SELECT i.variation_order_id, ROUND(COALESCE(SUM(i.total_unit * i.omission_quantity * i.rate), 0), 2) AS omission,
            ROUND(COALESCE(SUM(i.total_unit * i.addition_quantity * i.rate), 0), 2) AS addition,
            ROUND(COALESCE(SUM((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate))), 2) AS nett_omission_addition
            FROM ".VariationOrderItemTable::getInstance()->getTableName()." i
            JOIN ".VariationOrderTable::getInstance()->getTableName()." vo ON i.variation_order_id = vo.id
            WHERE vo.project_structure_id = ".$this->project->id." AND i.type <> ".VariationOrderItem::TYPE_HEADER." AND i.rate <> 0
            AND vo.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY i.variation_order_id");

            $stmt->execute();
            $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT vo.id AS variation_order_id, ROUND(COALESCE(SUM(i.up_to_date_amount), 0), 2) AS amount
            FROM ".VariationOrderTable::getInstance()->getTableName()." vo
            JOIN ".VariationOrderClaimTable::getInstance()->getTableName()." c ON c.variation_order_id = vo.id
            JOIN ".VariationOrderClaimItemTable::getInstance()->getTableName()." i ON i.variation_order_claim_id = c.id
            WHERE vo.project_structure_id = ".$this->project->id."
            AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY vo.id");

            $stmt->execute();
            $upToDateClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $voTotals = array(
                'omission' => 0,
                'addition' => 0
            );

            foreach($records as $key => $record)
            {
                $records[$key]['omission'] = 0;
                $records[$key]['addition'] = 0;

                foreach($quantities as $quantity)
                {
                    if($quantity['variation_order_id'] == $record['id'])
                    {
                        $records[$key]['omission'] = $quantity['omission'];
                        $records[$key]['addition'] = $quantity['addition'];

                        unset($quantity);
                    }
                }

                $voTotals['omission']+=$records[$key]['omission'];
                $voTotals['addition']+=$records[$key]['addition'];

                unset($record);
            }

            unset($claimCount, $quantities, $upToDateClaims);
        }

        if(count($records))
        {
            $this->generateItemPages($records, 1, $itemPages);
        }
        else
        {
            $this->generateItemPages(array(), 1, $itemPages);
        }

        $pages = SplFixedArray::fromArray($itemPages);

        $this->voTotals = $voTotals;

        return $pages;
    }

    public function generateItemPages(Array $voItems, $pageCount, &$itemPages, $counterIndex=0)
    {
        $itemPages[$pageCount] = array();
        $maxRows               = $this->getMaxRows();

        $blankRow                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $blankRow[self::ROW_BILL_ITEM_ID]           = -1;//id
        $blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
        $blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null;//description
        $blankRow[self::ROW_BILL_ITEM_LEVEL]        = null;
        $blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK;
        $blankRow[self::ROW_APPROVED]               = null;
        $blankRow[self::ROW_OMISSION]               = null;
        $blankRow[self::ROW_ADDITION]               = null;

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
        $rowCount = 1;

        $itemIndex    = 1;

        foreach($voItems as $x => $voItem)
        {
            $occupiedRows = Utilities::justify($voItems[$x]['description'], $this->MAX_CHARACTERS);

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
                        $row[self::ROW_BILL_ITEM_ID]    = $voItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_APPROVED]     = (array_key_exists('is_approved', $voItem)) ? ($voItem['is_approved']) ? 1 : 0 : 0;
                        $row[self::ROW_OMISSION]     = (array_key_exists('omission', $voItem)) ? $voItem['omission'] : 0;
                        $row[self::ROW_ADDITION]     = (array_key_exists('addition', $voItem)) ? $voItem['addition'] : 0;
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID] = null;
                        $row[self::ROW_APPROVED]     = null;
                        $row[self::ROW_OMISSION]     = null;
                        $row[self::ROW_ADDITION]     = null;
                    }

                    array_push($itemPages[$pageCount], $row);

                    unset($row);
                }

                //blank row
                array_push($itemPages[$pageCount], $blankRow);

                $rowCount++;//plus one blank row;
                $itemIndex++;

                unset($voItems[$x], $occupiedRows);
            }
            else
            {
                unset($occupiedRows);

                $pageCount++;
                $this->generateItemPages($voItems, $pageCount, $itemPages, $counterIndex);
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
        return $maxRows = 55;
    }
}