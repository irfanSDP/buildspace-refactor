<?php

abstract class sfCostDataStandardComparisonExcelReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colDescription = "B";

    const COL_NAME_CONTRACT_SUM    = 'Contract Sum';
    const COL_NAME_BUDGET          = 'Budget';
    const COL_NAME_ADJUSTED_SUM    = 'Adjusted Sum';
    const COL_NAME_VARIATION_ORDER = 'Variation Order';
    const COL_NAME_TOTAL_AMOUNT    = 'Total Amount';
    const COL_NAME_REMARKS         = 'Remarks';

    const HEADER_TEXT_COST = 'Cost';

    const ROW_TOTAL = 'Total';

    protected $title;
    protected $costData;
    protected $allCostDataValues        = array();
    protected $arrayOfBenchmarkCostData = array();
    protected $items                    = array();
    protected $allColumns               = array();
    protected $sums                     = array();
    protected $parentItemId;
    protected $billHeader               = 'Cost Data Comparison Report';
    protected $topLeftTitle             = null;
    protected $subtitle                 = null;
    protected $textColumns              = array();
    protected $itemValues               = array();
    protected $masterCostData;

    function __construct($costData = null, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : $costData->name . '-' . $this->subtitle . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->costData = $costData;

        $this->masterCostData = $costData->MasterCostData;

        $this->subtitle = "{$this->subtitle}";

        $this->textColumns = array( 'description', 'remarks' );

        parent::__construct($costData, $savePath, $filename, array());
    }

    public function setParameters(array $benchMarkCostDataIds, $parentItemId)
    {
        foreach($benchMarkCostDataIds as $id)
        {
            $this->arrayOfBenchmarkCostData[] = Doctrine_Core::getTable('CostData')->find($id);
        }

        $this->parentItemId = $parentItemId;

        if( $parentItem = Doctrine_Core::getTable('MasterCostDataItem')->find($this->parentItemId) ) $this->topLeftTitle = $parentItem->description;
    }

    public function setTitle($title = null)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Text' ));

        $invalidTitleChars = array( '[', ']', '*', '/', '\\', '?', ':' );

        if( $title )
        {
            return $this->activeSheet->setTitle(truncate_text(str_replace($invalidTitleChars, "_", $title)));
        }

        return $this->activeSheet->setTitle(truncate_text(str_replace($invalidTitleChars, "_", $this->costData->name . '-' . date('dmY H_i_s'))));
    }

    abstract protected function getItemValues(CostData $costData);

    protected function getItemList()
    {
        $this->items = CostDataItemTable::getItemList($this->costData, $this->parentItemId);
    }

    protected function getItemSums()
    {
        return array();
    }

    protected function getColumnValues()
    {
        $this->items = Utilities::setAttributeAsKey($this->items, 'id', false);

        $this->itemValues[$this->costData->id] = $this->getItemValues($this->costData);

        foreach($this->arrayOfBenchmarkCostData as $costData)
        {
            $this->allCostDataValues[ $costData->id ] = array();

            $values = $this->itemValues[$costData->id] = $this->getItemValues($costData);

            foreach($this->items as $itemId => $item)
            {
                $this->allCostDataValues[ $costData->id ][ $itemId ] = $values[ $itemId ];
            }
        }

        foreach($this->itemValues[$this->costData->id] as $itemId => $itemValues)
        {
            $this->itemValues[$this->costData->id][ $itemId ]['description'] = $this->items[ $itemId ]['description'];
        }

        $this->allCostDataValues[ $this->costData->id ] = $this->itemValues[$this->costData->id];
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colDescription;
        $this->lastCol    = $this->colDescription;

        $numberOfColumnsToAdd = count($this->allColumns) - 1; // Minus the description column (start column is already the description column).

        self::increment($this->lastCol, $numberOfColumnsToAdd);
    }

    protected function processRows()
    {
        $this->itemType = null; // For description row styling.

        foreach($this->items as $itemId => $item)
        {
            $this->newLine();
            $this->newLine();

            $this->currentCol = $this->colDescription;

            foreach($this->allColumns as $column)
            {
                $columnInfo = explode('.', $column);
                $costDataId = $columnInfo[0];
                $columnName = $columnInfo[1];

                if( in_array($columnName, $this->textColumns) )
                {
                    $this->activeSheet->setCellValue($this->currentCol . $this->currentRow, $this->allCostDataValues[ $costDataId ][ $itemId ][ $columnName ]);
                    $this->setItemStyle();
                }
                elseif( $columnName == "uom_symbol" )
                {
                    $this->activeSheet->setCellValue($this->colUnit . $this->currentRow, $item['uom_symbol']);
                    $this->activeSheet->getStyle($this->colUnit . $this->currentRow)->applyFromArray($this->getUnitStyle());
                }
                else
                {
                    $value = $this->allCostDataValues[ $costDataId ][ $itemId ][ $columnName ];

                    if( $value == 0 ) $value = null;

                    parent::setValue($this->currentCol, $value);

                    if( ! isset( $this->sums[ $costDataId ][ $columnName ] ) ) $this->sums[ $costDataId ][ $columnName ] = 0;

                    $this->sums[ $costDataId ][ $columnName ] += $this->allCostDataValues[ $costDataId ][ $itemId ][ $columnName ];
                }

                $this->currentCol++;
            }
        }
    }

    public function generateReport()
    {
        $this->defineColumnStructure();
        $this->getItemList();
        $this->getItemSums();
        $this->getColumnValues();
        $this->startBillCounter();
        $this->createHeader();
        $this->setBillHeader($this->billHeader, $this->topLeftTitle, $this->subtitle);
        $this->processRows();
        $this->createFooter(true);
        $this->generateExcelFile();
    }

    public function createFooter($printGrandTotal = false)
    {
        $this->newLine(true);

        $this->currentRow++;

        if( $printGrandTotal )
        {
            $this->printGrandTotal();
        }

        $this->currentRow += 2;
    }

    public function printGrandTotal()
    {
        $this->activeSheet->getStyle($this->colDescription . $this->currentRow)->applyFromArray($this->getTotalStyle());

        $this->printTotalText();

        $this->printGrandTotalValue($this->getNewLineStyle(true));

        $this->currentRow++;
    }

    public function printGrandTotalValue($style)
    {
        $this->currentCol = $this->colDescription;
        $this->currentCol++;
        $firstTotalCol = $this->currentCol;

        $allColumns = $this->allColumns;

        // Remove description column.
        array_shift($allColumns);

        foreach($allColumns as $column)
        {
            $columnInfo = explode('.', $column);
            $costDataId = $columnInfo[0];
            $columnName = $columnInfo[1];

            $value = $this->sums[ $costDataId ][ $columnName ] ?? 0;

            if( $value == 0 ) $value = null;

            parent::setValue($this->currentCol, $value);
            $this->currentCol++;
        }

        $this->activeSheet->getStyle($firstTotalCol . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);
    }

} 