<?php namespace PCK\Reports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PCK\Helpers\StringOperations;
use PCK\WeightedNode\WeightedNode;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class WeightedNodeFormExcelGenerator extends ReportGenerator {

    const COLUMN_WIDTH = 40;
    const COLUMN_FORM_INFO_1 = 'B';
    const COLUMN_FORM_INFO_2 = 'C';
    const COLUMN_FORM_INFO_3 = 'D';
    const COLUMN_FORM_INFO_4 = 'E';

    protected $colFirst = 'B';
    protected $currentColumn;
    protected $spreadsheetTitle;
    protected $worksheetTitle;

    public function __construct()
    {
        parent::__construct();

        $this->spreadsheet->removeSheetByIndex(0);

        $this->spreadsheet->getProperties()->setCreator("Buildspace");
    }

    public function setSpreadsheetTitle($spreadsheetTitle)
    {
        $this->spreadsheetTitle = $spreadsheetTitle;
    }

    public function addWorkSheet($forms, $workSheetName)
    {
        $workSheetName = $this->sanitizeWorkSheetTitle(substr($workSheetName, 0, 31));

        $this->activeSheet = new Worksheet($this->spreadsheet, $workSheetName);

        $this->spreadsheet->addSheet($this->activeSheet);

        $this->generateWorksheetTitle();

        $this->currentRow = 3;

        $maxDepth = 0;

        foreach($forms as $form)
        {
            $lastInfoRow = $this->generateFormInformation($this->currentRow, $form);

            $this->currentRow = $lastInfoRow;

            $this->increment($this->currentRow, 2);

            $this->currentRow = $this->generateExcelNodeData($this->colFirst, $this->currentRow, $form->weightedNode);

            $this->increment($this->currentRow, 4);

            $maxDepth = max($maxDepth, WeightedNode::where('root_id', '=', $form->weighted_node_id)->max('depth'));
        }

        $totalNumberOfColumns = $maxDepth + 2; // +1 because index starts at 0, +2 for score column (name).

        if($totalNumberOfColumns < 4) $totalNumberOfColumns = 4; // At least all 4 columns in the header should be a reasonable width.

        $column = $this->colFirst;

        for($i = 0; $i < $totalNumberOfColumns; $i++)
        {
            $this->activeSheet->getColumnDimension($column++)->setWidth(self::COLUMN_WIDTH);
        }
    }

    public function generate()
    {
        return $this->output($this->spreadsheet, $this->spreadsheetTitle);
    }

    public function saveTo($filepath = null)
    {
        return $this->save($this->spreadsheet, $filepath);
    }

    public function generateWorksheetTitle()
    {
        $this->activeSheet->setCellValue("B1", $this->worksheetTitle);

        $this->activeSheet->mergeCells("B1:E1");

        $this->activeSheet->getStyle("B1:E1")->applyFromArray(array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        ));
    }

    /**
     * return int lastRow
     */
    abstract protected function generateFormInformation($startRow, $form);

    public function generateExcelNodeData($startColumn, $startRow, WeightedNode $node)
    {
        $this->activeSheet->setCellValue("{$startColumn}{$startRow}", $node->name);

        $nextColumn = $this->getNextColumn($startColumn);

        $row = $startRow;

        if(!$node->scores->isEmpty())
        {
            $scoreValueColumn = $this->getNextColumn($nextColumn);

            foreach($node->scores as $key => $score)
            {
                $this->activeSheet->setCellValue("{$nextColumn}{$row}", $score->name);

                $this->activeSheet->setCellValue("{$scoreValueColumn}{$row}", $score->value);

                $this->activeSheet->getStyle("{$nextColumn}{$row}:{$scoreValueColumn}{$row}")->applyFromArray($this->getScoreStyle($node->is_excluded, $score->is_selected));

                if($key < ($node->scores->count()-1)) $row++;
            }
        }
        elseif(!$node->children->isEmpty())
        {
            foreach($node->children as $key => $child)
            {
                $lastRow = $this->generateExcelNodeData($nextColumn, $row, $child);

                $row = $lastRow;

                if($key < ($node->children->count()-1)) $row++;
            }
        }

        $lastRow = $row;

        $this->activeSheet->mergeCells("{$startColumn}{$startRow}:{$startColumn}{$lastRow}");

        $this->activeSheet->getStyle("{$startColumn}{$startRow}:{$startColumn}{$lastRow}")->applyFromArray($this->getNodeStyle($node->is_excluded));

        return $lastRow;
    }

    public function getNodeStyle($isExcluded = false)
    {
        $fillColor = $isExcluded ? 'e9eae3' : 'ffffff';

        return array(
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'startColor' => array('rgb' => $fillColor)
            ),
            'borders'   => array(
                'allBorders' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        );
    }

    public function getScoreStyle($isExcluded = false, $isSelected = false)
    {
        $fillColor = $isExcluded ? 'e9eae3' : 'ffffff';

        if(!$isExcluded && $isSelected) $fillColor = 'b6d7a8';

        return array(
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'startColor' => array('rgb' => $fillColor)
            ),
            'borders'   => array(
                'allBorders' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
            ),
            'font'      => array(
                'bold' => $isSelected
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        );
    }

    public function getFormInformationLabelStyle()
    {
        return array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        );
    }

    public function getFormInformationDataStyle()
    {
        return array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        );
    }
}