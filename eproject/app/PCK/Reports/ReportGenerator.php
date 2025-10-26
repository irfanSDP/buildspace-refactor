<?php namespace PCK\Reports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportGenerator {

    protected $currentRow = 1;
    protected $spreadsheet;
    protected $activeSheet;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->activeSheet = $this->spreadsheet->getActiveSheet();
    }

    protected function protectWorkSheet($password)
    {
        $protection = $this->spreadsheet->getActiveSheet()->getProtection();
        $protection->setSheet(true);
        $protection->setSort(true);
        $protection->setInsertRows(true);
        $protection->setFormatCells(true);
        $protection->setPassword($password);
    }

    protected function protectWorkBook($password)
    {
        $security = $this->spreadsheet->getSecurity();
        $security->setLockWindows(true);
        $security->setLockStructure(true);
        $security->setWorkbookPassword($password);
    }

    protected function output(Spreadsheet $spreadsheet, $filename = null)
    {
        $filepath = $this->save($spreadsheet);

        return \PCK\Helpers\Files::download($filepath, "{$filename}.".\PCK\Helpers\Files::EXTENSION_EXCEL);
    }

    protected function save(Spreadsheet $spreadsheet, $filepath = null)
    {
        $writer = new Xlsx($spreadsheet);

        $filepath = $filepath ?? \PCK\Helpers\Files::getTmpFileUri();

        $writer->save($filepath);

        return $filepath;
    }

    protected function sanitizeWorkSheetTitle($title)
    {
        return str_replace(Worksheet::getInvalidCharacters(), ' ', $title);
    }

    /**
     * Merges the a given number of rows.
     * (rowSpan="x").
     *
     * @param     $column
     * @param     $row
     * @param int $numberOfRows
     */
    public function mergeRows($column, $row, $numberOfRows = 1)
    {
        $nextRow = $row;
        for($i = 0; $i < $numberOfRows; $i++)
        {
            $nextRow = $this->getNextRow($nextRow);
        }
        $lastRowToMerge = $nextRow;
        $this->activeSheet->mergeCells($column . $row . ':' . $column . $lastRowToMerge);
    }

    /**
     * Merges the a given number of columns.
     * (colSpan="x").
     *
     * @param     $column
     * @param     $row
     * @param int $numberOfColumns
     */
    public function mergeColumns($column, $row, $numberOfColumns = 1)
    {
        $nextColumn = $column;
        for($i = 0; $i < $numberOfColumns; $i++)
        {
            $nextColumn = $this->getNextColumn($nextColumn);
        }
        $lastColumnToMerge = $nextColumn;

        $this->activeSheet->mergeCells($column . $row . ':' . $lastColumnToMerge . $row);
    }

    /**
     * Gets the next row without changing the value of the current row.
     * Typically we do a ++$row to get the value for the next row.
     * With this we can get the value of the next row and still keep the original row value.
     *
     * @param $currentRow
     *
     * @return mixed
     */
    public function getNextRow($currentRow)
    {
        $temporaryRow = $currentRow;
        $nextRow      = ++$temporaryRow;
        unset( $temporaryRow );

        return $nextRow;
    }

    /**
     * Gets the next column without changing the value of the current column.
     * Typically we do a ++$column to get the value for the next column.
     * With this we can get the value of the next column and still keep the original column value.
     *
     * @param $currentColumn
     *
     * @return mixed
     */
    public function getNextColumn($currentColumn)
    {
        $temporaryColumn = $currentColumn;
        $nextColumn      = ++$temporaryColumn;
        unset( $temporaryColumn );

        return $nextColumn;
    }

    public function getPreviousColumn($currentColumn, $steps = 1)
    {
        if( $currentColumn == "A" ) return $currentColumn;

        $columnNumber = Coordinate::columnIndexFromString($currentColumn) - $steps;
        return Coordinate::stringFromColumnIndex($columnNumber - 1);
    }

    /**
     * Increments the value.
     *
     * @param $value
     * @param $incrementBy
     */
    public function increment(&$value, $incrementBy = 1)
    {
        for($i = 0; $i < $incrementBy; $i++) $value++;
    }

    /**
     * Adds Header columns.
     * Column width is set to 15.
     * Returns the number of columns and the Column Map of the newly created headers.
     *
     * @param $columnDefinition string|array    Use strings for column names, and arrays for sibling headers.
     * @param $startColumn      string          The column the headers starts.
     * @param $startRow         string          The row the headers start.
     *
     * @return array    Total number of columns and the Column Map.
     */
    protected function addHeaderColumns($columnDefinition, $startColumn, $startRow)
    {
        $columnMap = array();

        if( ! is_array($columnDefinition) )
        {
            $this->activeSheet->setCellValue($startColumn . $startRow, $columnDefinition);
            $this->activeSheet->getStyle($startColumn . $startRow)->applyFromArray($this->getColumnHeaderStyle());
            $this->activeSheet->getColumnDimension($startColumn)->setWidth(15);

            $columnMap[ $columnDefinition ] = $startColumn;

            return array(
                'numberOfColumns' => 1,
                'map'             => $columnMap
            );
        }

        $numberOfColumns  = 0;
        $childStartColumn = $startColumn;
        foreach($columnDefinition as $parentColumn => $childColumns)
        {
            $nextStartRow = $startRow;
            if( is_array($childColumns) )
            {
                $nextStartRow = $this->getNextRow($startRow);
            }
            $childrenLengthAndMap = $this->addHeaderColumns($childColumns, $childStartColumn, $nextStartRow);
            $childrenLength       = $childrenLengthAndMap['numberOfColumns'];
            $childrenColumnMap    = $childrenLengthAndMap['map'];

            if( is_array($childColumns) )
            {
                $this->activeSheet->setCellValue($childStartColumn . $startRow, $parentColumn);
                $this->mergeColumns($childStartColumn, $startRow, $childrenLength - 1);

                $lastColumn = $childStartColumn;
                $this->increment($lastColumn, $childrenLength - 1);

                $this->activeSheet->getStyle($childStartColumn . $startRow . ':' . $lastColumn . $startRow)->applyFromArray($this->getColumnHeaderStyle());

                $columnMap[ $parentColumn ] = $childrenColumnMap;
            }
            else
            {
                reset($childrenColumnMap);
                $columnName = key($childrenColumnMap);

                $columnMap[ $columnName ] = $childrenColumnMap[ $columnName ];
            }

            $numberOfColumns += $childrenLength;
            $this->increment($childStartColumn, $childrenLength);
        }

        return array(
            'numberOfColumns' => $numberOfColumns,
            'map'             => $columnMap
        );
    }

    protected function setAmount($coordinates, $value)
    {
        $this->activeSheet->setCellValue($coordinates, $value);

        $this->activeSheet->getStyle($coordinates)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $this->activeSheet->getStyle($coordinates)->applyFromArray($this->getAmountStyle());
    }

    public function getAmountStyle()
    {
        return array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical'   => Alignment::VERTICAL_CENTER
            )
        );
    }

    public function getItemRowStyle()
    {
        return array(
            'borders'   => array(
                'left'     => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
                'right'    => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
                'vertical' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            )

        );
    }

    public function getLastItemRowStyle()
    {
        $lastItemRowStyle                      = $this->getItemRowStyle();
        $lastItemRowStyle['borders']['bottom'] = array(
            'borderStyle' => Border::BORDER_THIN,
            'color'       => array( 'argb' => '000000' ),
        );

        return $lastItemRowStyle;
    }

    public function getColumnHeaderStyle()
    {
        return array(
            'borders'   => array(
                'allBorders' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
            ),
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            )

        );
    }

    public function getTitleStyle()
    {
        return array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER
            )
        );
    }

}