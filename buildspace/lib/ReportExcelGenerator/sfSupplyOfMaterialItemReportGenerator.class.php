<?php

class sfSupplyOfMaterialReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colDescription = "B";
    public $colUnit        = "C";
    public $colSupplyRate  = "D";
    public $tenderers;
    public $contractorTotals = [];

    const COL_NAME_SUPPLY_RATE                   = "Supply Rate";
    const COL_NAME_ESTIMATED_QTY                 = "Estimated Qty";
    const COL_NAME_PERCENTAGE_OF_WASTAGE_ALLOWED = "% of Wastage Allowed";
    const COL_NAME_CONTRACTOR_RATE               = "Contractor Rate";
    const COL_NAME_DIFFERENCE                    = "Difference";

    function __construct($project, $bill, $itemIds, $tendererIds, $savePath = null, $filename = null, $printQty = false)
    {
        $this->project = $project;
        $this->bill = $bill;

        $filename = ( $filename ) ? $filename : $this->bill->title . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->itemIds     = $itemIds;
        $this->tendererIds = $tendererIds;
        $this->tenderers   = $this->getTenderers();

        $this->contractorRates = TenderCompanyTable::getDisplayedContractorSupplyOfMaterialItemRates($itemIds, $tendererIds);

        foreach($this->tendererIds as $tendererId)
        {
            $this->contractorTotals[$tendererId] = 0;
        }

        $printSettings  = SupplyOfMaterialLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->SupplyOfMaterialLayoutSetting->id, TRUE);

        parent::__construct($project, $savePath, $filename, $printSettings);
    }

    public function getTenderers()
    {
        $tenderers = array();

        if(count($this->tendererIds))
        {
            $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, t.original_tender_value AS grand_total
                FROM ".TenderSettingTable::getInstance()->getTableName()." t
                JOIN ".CompanyTable::getInstance()->getTableName()." c ON c.id = t.awarded_company_id
                WHERE t.project_structure_id = ".$this->bill->root_id." AND c.id IN (".implode(',', $this->tendererIds).")");

            $stmt->execute();
            $selectedTenderer = $stmt->fetch(PDO::FETCH_ASSOC);

            if($selectedTenderer)
            {
                $selectedTenderer['selected'] = true;

                array_push($tenderers, $selectedTenderer);
            }

            $companySqlStatement = ($selectedTenderer['id'] > 0) ? "AND c.id <> ".$selectedTenderer['id'] : null;

            $tenderSetting = $this->project->TenderSetting->toArray();

            switch($tenderSetting['contractor_sort_by'])
            {
                case TenderSetting::SORT_CONTRACTOR_NAME_ASC:
                    $sqlOrder = "c.name ASC";
                    break;
                case TenderSetting::SORT_CONTRACTOR_NAME_DESC:
                    $sqlOrder = "c.name DESC";
                    break;
                case TenderSetting::SORT_CONTRACTOR_HIGHEST_LOWEST:
                    $sqlOrder = "total DESC";
                    break;
                case TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST:
                    $sqlOrder = "total ASC";
                    break;
                default:
                    throw new Exception('invalid sort option');
            }

            if(count($this->tendererIds))
            {
                $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, xref.id AS tender_company_id, xref.show, COALESCE(SUM(r.amount), 0) AS total
                FROM ".CompanyTable::getInstance()->getTableName()." c
                JOIN ".TenderCompanyTable::getInstance()->getTableName()." xref ON xref.company_id = c.id
                LEFT JOIN ".TenderSupplyOfMaterialRateTable::getInstance()->getTableName()." r ON r.tender_company_id = xref.id
                WHERE xref.project_structure_id = ".$this->project->id."
                AND c.id IN (".implode(', ', $this->tendererIds).") {$companySqlStatement}
                AND c.deleted_at IS NULL GROUP BY c.id, xref.show, xref.id ORDER BY ".$sqlOrder);

                $stmt->execute();

                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach($result as $contractor)
                {
                    array_push($tenderers, $contractor);
                }
            }
        }

        return $tenderers;
    }

    public function createHeader($new = false)
    {
        $this->currentRow++;

        $firstHeaderRow = $this->currentRow;

        $this->currentRow++;

        $secondHeaderRow = $this->currentRow;

        $this->activeSheet->setCellValue($this->colDescription . $firstHeaderRow, self::COL_NAME_DESCRIPTION);
        $this->mergeRows($this->colDescription, $firstHeaderRow);

        $this->activeSheet->setCellValue($this->colUnit . $firstHeaderRow, self::COL_NAME_UNIT);
        $this->mergeRows($this->colUnit, $firstHeaderRow);

        $this->activeSheet->setCellValue($this->colSupplyRate . $firstHeaderRow, self::COL_NAME_SUPPLY_RATE);
        $this->mergeRows($this->colSupplyRate, $firstHeaderRow);

        $currentColumn = $this->colSupplyRate;

        if( count($this->tenderers) )
        {
            foreach($this->tenderers as $tenderer)
            {
                ++$currentColumn;

                $tendererFirstColumn = $currentColumn;

                $tendererName = ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'];

                if( isset( $tenderer['selected'] ) AND $tenderer['selected'] )
                {
                    // set the selected tenderer a blue marker
                    $objRichText = new PHPExcel_RichText();
                    $objBold = $objRichText->createTextRun(( ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'] ));
                    $objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

                    $tendererName = $objRichText;
                }

                $this->activeSheet->setCellValue($currentColumn . $firstHeaderRow, $tendererName);

                $this->activeSheet->setCellValue($currentColumn . $secondHeaderRow, self::COL_NAME_ESTIMATED_QTY);
                $this->activeSheet->getColumnDimension($currentColumn)->setWidth(15);

                ++$currentColumn;

                $this->activeSheet->setCellValue($currentColumn . $secondHeaderRow, self::COL_NAME_PERCENTAGE_OF_WASTAGE_ALLOWED);
                $this->activeSheet->getColumnDimension($currentColumn)->setWidth(15);

                ++$currentColumn;

                $this->activeSheet->setCellValue($currentColumn . $secondHeaderRow, self::COL_NAME_CONTRACTOR_RATE);
                $this->activeSheet->getColumnDimension($currentColumn)->setWidth(15);

                ++$currentColumn;

                $this->activeSheet->setCellValue($currentColumn . $secondHeaderRow, self::COL_NAME_DIFFERENCE);
                $this->activeSheet->getColumnDimension($currentColumn)->setWidth(15);

                ++$currentColumn;

                $this->activeSheet->setCellValue($currentColumn . $secondHeaderRow, self::COL_NAME_AMOUNT);
                $this->activeSheet->getColumnDimension($currentColumn)->setWidth(15);

                $this->activeSheet->mergeCells($tendererFirstColumn . $firstHeaderRow . ':' . $currentColumn . $firstHeaderRow);
            }
        }

        //Set header styling
        $this->activeSheet->getStyle($this->colDescription . $firstHeaderRow . ':' . $currentColumn . $firstHeaderRow)->applyFromArray($this->getColumnHeaderStyle());
        // For merged header rows
        $this->activeSheet->getStyle($this->colDescription . $secondHeaderRow . ':' . $currentColumn . $secondHeaderRow)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
        $this->activeSheet->getColumnDimension($this->colUnit)->setWidth(13);
        $this->activeSheet->getColumnDimension($this->colSupplyRate)->setWidth(15);
    }

    public function generateReport($header, $subTitle, $topLeftTitle, $withoutCents)
    {
        $this->setExcelParameter(false, $withoutCents);
        $this->createSheet($header, $subTitle, $topLeftTitle);
        $this->createHeader();
        $this->processContents();
        $this->printGrandTotal();
        $this->generateExcelFile();
    }

    public function processContents()
    {
        if(empty($this->itemIds))
        {
            $this->newLine(true);

            $this->currentRow++;

            return;
        }

        $stmt = $this->pdo->prepare("SELECT i.id, e.id as element_id, i.description, i.type, i.lft, i.level, i.supply_rate, i.contractor_supply_rate,
            i.estimated_qty, i.percentage_of_wastage, i.difference, i.amount, uom.id AS uom_id, uom.symbol AS uom_symbol, i.note
            FROM " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " i
            JOIN " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b on b.id = e.project_structure_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE b.id = " . $this->bill->id . "
            AND i.id IN (" . implode(',', $this->itemIds) . ")
            AND b.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY b.priority, e.priority, i.priority, i.lft, i.level");

        $stmt->execute();

        $itemRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $itemsByElement = [];
        $elementIds     = [];

        foreach ( $itemRecords as $item )
        {
            $itemsByElement[$item['element_id']][] = $item;

            $elementIds[$item['element_id']] = $item['element_id'];
        }

        if(empty($elementIds)) $elementIds = [0];

        $stmt = $this->pdo->prepare("SELECT e.id, e.description
            FROM " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e
            WHERE e.id IN (" . implode(',', $elementIds) . ")
            AND e.deleted_at IS NULL");

        $stmt->execute();

        $elementRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $elements = [];

        foreach($elementRecords as $element)
        {
            $elements[$element['id']] = array(
                'description' => $element['description'],
            );
        }
        $output = [];

        foreach ( $itemsByElement as $elementId => $items )
        {
            if ( isset ( $elements[$elementId] ) )
            {
                $this->setElement(array( 'description' => $elements[$elementId]['description'] ));
            }

            foreach($items as $item)
            {
                $this->newItem();

                $this->setItem($item['description'], $item['type'], $item['level']);

                $this->processItems($item);
            }
        }

        $this->newLine(true);

        $this->currentRow++;
    }

    public function processItems($item)
    {
        parent::setUnit($item['uom_symbol']);

        parent::setValue($this->colSupplyRate, $item['supply_rate']);

        $currentColumn = $this->colSupplyRate;

        if( count($this->tenderers) )
        {
            $tendererCounter = 1;

            foreach($this->tenderers as $tenderer)
            {
                ++$currentColumn;
                $estimatedQtyColumn = $currentColumn;
                ++$currentColumn;
                $percentageOfWastageAllowedColumn = $currentColumn;
                ++$currentColumn;
                $contractorRateColumn = $currentColumn;
                ++$currentColumn;
                $differenceColumn = $currentColumn;
                ++$currentColumn;
                $amountColumn = $currentColumn;

                if(array_key_exists($tenderer['id'], $this->contractorRates) && array_key_exists($item['id'], $this->contractorRates[$tenderer['id']]))
                {
                    parent::setValue($estimatedQtyColumn, $this->getNonZeroValue($this->contractorRates[$tenderer['id']][$item['id']][0]['estimated_qty']));
                    parent::setValue($percentageOfWastageAllowedColumn, $this->getNonZeroValue($this->contractorRates[$tenderer['id']][$item['id']][0]['percentage_of_wastage']));
                    parent::setValue($contractorRateColumn, $this->getNonZeroValue($this->contractorRates[$tenderer['id']][$item['id']][0]['contractor_supply_rate']));
                    parent::setValue($differenceColumn, $this->getNonZeroValue($this->contractorRates[$tenderer['id']][$item['id']][0]['difference']));
                    parent::setValue($amountColumn, $this->getNonZeroValue($this->contractorRates[$tenderer['id']][$item['id']][0]['amount']));

                    $this->contractorTotals[$tenderer['id']] += $this->contractorRates[$tenderer['id']][$item['id']][0]['amount'];
                }

                $tendererCounter++;
            }
        }
    }

    /**
     * Prints the text 'Total' in the desired cell.
     *
     * @param bool $title
     */
    public function printTotalText($title = false)
    {
        $this->activeSheet->setCellValue($this->colSupplyRate . $this->currentRow, "Total:");
    }

    /**
     * Sets the value for the grand total.
     *
     * @param $style
     */
    public function printGrandTotalValue($style)
    {
        $currentColumn = $this->colSupplyRate;

        foreach($this->tenderers as $tenderer)
        {
            ++$currentColumn;

            $tendererFirstColumn = $currentColumn;

            ++$currentColumn;
            ++$currentColumn;
            ++$currentColumn;
            ++$currentColumn;

            $this->setValue($tendererFirstColumn, $this->contractorTotals[$tenderer['id']]);
            $this->activeSheet->mergeCells($tendererFirstColumn . $this->currentRow . ':' . $currentColumn . $this->currentRow);
        }

        $this->activeSheet->getStyle($this->colSupplyRate . $this->currentRow . ":" . $currentColumn . $this->currentRow)
            ->applyFromArray($style);
    }

    /**
     * Starts the bill counter.
     * This sets the first row, currentElementNo,  and currentRow to the starting.
     * Also determines the first and last column.
     */
    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colDescription;

        if( count($this->tenderers) )
        {
            $currentColumn = $this->colSupplyRate;

            foreach($this->tenderers as $tenderer)
            {
                ++$currentColumn;
                ++$currentColumn;
                ++$currentColumn;
                ++$currentColumn;
                ++$currentColumn;
            }

            $this->lastCol = $currentColumn;
        }
        else
        {
            $this->lastCol = $this->colSupplyRate;
        }
    }

    /**
     * Prints the row for the grand total.
     */
    public function printGrandTotal()
    {
        if(empty($this->tendererIds)) return;

        $newLineStyle = array(
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
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                )
            )
        );

        $totalStyle = array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'wrapText'   => true
            )
        );

        $this->activeSheet->getStyle($this->colSupplyRate . $this->currentRow)->applyFromArray($totalStyle);

        $this->printTotalText();

        $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );

        $this->printGrandTotalValue($newLineStyle);

        $this->currentRow++;
    }
}