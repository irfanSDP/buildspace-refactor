<?php namespace PCK\Reports;

use Carbon\Carbon;
use PCK\ProjectReport\ProjectReport;
use PCK\ProjectReport\ProjectReportColumn;
use PCK\ProjectReport\ProjectReportColumnRepository;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProjectReportDashboardExcelGenerator extends ReportGenerator {
    protected $colFirst = 'B';
    protected $currentColumn;
    protected $sheetTitle;

    protected $template;
    protected $rowRecords;

    private $sortedRowRecordTemplate = [];

    protected $maxHeaderDepth;
    protected $columnDefinitions;

    protected $allColumnHeaderKeys;

    const HEADER_FIRST_ROW = 3;

    public function setSpreadsheetTitle($sheetTitle)
    {
        $this->sheetTitle = $sheetTitle;
    }

    public function setTemplate(ProjectReport $template)
    {
        $this->template = $template;
    }

    // sets the records in the correct order
    public function setRowRecords($rowRecords)
    {
        $data = [];

        $count = 0;

        foreach($rowRecords as $rowRecord)
        {
            $data[$rowRecord['projectReportId']]['number'] = ++$count;

            foreach($rowRecord['rowData'] as $key => $value)
            {
                $data[$rowRecord['projectReportId']][$key] = $value;
            }

            $data[$rowRecord['projectReportId']]['approved_date'] = $rowRecord['approvedDate'];
            $data[$rowRecord['projectReportId']]['remarks'] = $rowRecord['remarks'];
        }

        $this->rowRecords = $data;
    }

    private function getColumnDefinitions()
    {
        $columnRepository  = \App::make(ProjectReportColumnRepository::class);
        $columnDefinitions = $columnRepository->getColumnDefinitions($this->template);

        array_unshift($columnDefinitions, [
            'title'      => trans('general.no'),
            'identifier' => 'number',
            'type'       => ProjectReportColumn::COLUMN_CUSTOM,
            'depth'      => 0,
        ]);

        array_push($columnDefinitions, [
            'title'      => trans('projectReport.approvedDate'),
            'identifier' => 'approved_date',
            'type'       => ProjectReportColumn::COLUMN_CUSTOM,
            'depth'      => 0,
        ]);

        array_push($columnDefinitions, [
            'title'      => trans('general.remarks'),
            'identifier' => 'remarks',
            'type'       => ProjectReportColumn::COLUMN_CUSTOM,
            'depth'      => 0,
        ]);

        return $columnDefinitions;
    }

    public function generateTitle()
    {
        $this->activeSheet->setCellValue("B1", $this->sheetTitle);

        $this->activeSheet->mergeCells("B1:G1");

        $this->activeSheet->getStyle("B1")->applyFromArray(array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            )
        ));

        $this->currentRow = 3;
    }

    private function calculateNumberOfColumnsToMerge($columnDefinitions)
    {
        foreach($columnDefinitions as &$columnDefinition)
        {
            if($columnDefinition['type'] === ProjectReportColumn::COLUMN_GROUP)
            {
                $columnsToMerge = 0;

                foreach($columnDefinition['children'] as &$children)
                {
                    $columnsToMerge += $this->calculateNumberOfColumnsToMergeRecursively($children);
                }

                $columnDefinition['exportInfo']['columnsToMerge'] = $columnsToMerge;
            }
            else
            {
                $columnDefinition['exportInfo']['columnsToMerge'] = 1;
            }
        }

        return $columnDefinitions;
    }

    private function calculateNumberOfColumnsToMergeRecursively(&$columnDefinition)
    {
        $columnsToMerge = 0;

        if($columnDefinition['type'] === ProjectReportColumn::COLUMN_GROUP)
        {
            foreach($columnDefinition['children'] as &$children)
            {
                $columnsToMerge += $this->calculateNumberOfColumnsToMergeRecursively($children);
            }

            $columnDefinition['exportInfo']['columnsToMerge'] = $columnsToMerge;
        }
        else
        {
            $columnsToMerge = 1;
            $columnDefinition['exportInfo']['columnsToMerge'] = $columnsToMerge;
        }

        return $columnsToMerge;
    }

    private function calculateNumberOfRowsToMerge($columnDefinitions)
    {
        foreach($columnDefinitions as &$columnDefinition)
        {
            if(!array_key_exists('children', $columnDefinition) && $columnDefinition['type'] !== ProjectReportColumn::COLUMN_GROUP)
            {
                $columnDefinition['exportInfo']['rowsToMerge'] = $this->maxHeaderDepth;
                continue;
            }

            $currentRowCount = 1;

            $columnDefinition['exportInfo']['rowsToMerge'] = 1;

            foreach($columnDefinition['children'] as &$childColumnDefinition)
            {
                $this->calculateNumberOfRowsToMergeRecursively($childColumnDefinition, $currentRowCount);
            }
        }

        return $columnDefinitions;
    }

    private function calculateNumberOfRowsToMergeRecursively(&$columnDefinition, $currentRowCount)
    {
        if(!array_key_exists('children', $columnDefinition) && $columnDefinition['type'] !== ProjectReportColumn::COLUMN_GROUP)
        {
            $columnDefinition['exportInfo']['rowsToMerge'] = $this->maxHeaderDepth - $currentRowCount;
            return;
        }

        ++$currentRowCount;

        $columnDefinition['exportInfo']['rowsToMerge'] = 1;

        foreach($columnDefinition['children'] as &$childColumnDefinition)
        {
            $this->calculateNumberOfRowsToMergeRecursively($childColumnDefinition, $currentRowCount);
        }
    }
    
    private function calculateEndColumn($startColumn, $numberOfColumns)
    {
        if($numberOfColumns <= 1) return $startColumn;

        $endColumn = $startColumn;

        for($i = 0; $i < $numberOfColumns; $i++)
        {
            $endColumn = $this->getNextColumn($endColumn);
        }

        return $endColumn;
    }

    /**
     * Calculates the following info
     * - startColumn
     * - endColumn
     * - startRow
     * - endRow
     */
    private function calculateColumnAndRowProperties($columnDefinitions)
    {
        $currentColumn = $this->colFirst;

        foreach($columnDefinitions as &$columnDefinition)
        {
            if(!array_key_exists('children', $columnDefinition) && $columnDefinition['type'] !== ProjectReportColumn::COLUMN_GROUP)
            {
                $columnDefinition['exportInfo']['column']['start'] = $currentColumn;
                $columnDefinition['exportInfo']['column']['end']   = $currentColumn;
            }
            else
            {
                $childCurrentColumn = $currentColumn;

                $columnDefinition['exportInfo']['column']['start'] = $childCurrentColumn;

                $childrenCount = count($columnDefinition['children']);

                foreach($columnDefinition['children'] as $index => &$childColumnDefinition)
                {
                    $childCurrentColumn = $this->calculateColumnAndRowPropertiesRecursively($childColumnDefinition, $childCurrentColumn, (($childrenCount - 1) === $index));

                    $currentColumn = $childCurrentColumn;
                }

                $columnDefinition['exportInfo']['column']['end'] = $childCurrentColumn;
            }

            $columnDefinition['exportInfo']['row']['start'] = self::HEADER_FIRST_ROW + $columnDefinition['depth'];
            $columnDefinition['exportInfo']['row']['end']   = self::HEADER_FIRST_ROW + $columnDefinition['depth'] + ($columnDefinition['exportInfo']['rowsToMerge'] - 1);

            $currentColumn = $this->getNextColumn($currentColumn);
        }

        return $columnDefinitions;
    }

    private function calculateColumnAndRowPropertiesRecursively(&$columnDefinition, $currentColumn, $isLastChild)
    {
        if(!array_key_exists('children', $columnDefinition) && $columnDefinition['type'] !== ProjectReportColumn::COLUMN_GROUP)
        {
            $columnDefinition['exportInfo']['column']['start'] = $currentColumn;
            $columnDefinition['exportInfo']['column']['end']   = $currentColumn;
        }
        else
        {
            $childCurrentColumn = $currentColumn;

            $columnDefinition['exportInfo']['column']['start'] = $childCurrentColumn;

            $childrenCount = count($columnDefinition['children']);

            foreach($columnDefinition['children'] as $index => &$childColumnDefinition)
            {
                $childCurrentColumn = $this->calculateColumnAndRowPropertiesRecursively($childColumnDefinition, $childCurrentColumn, (($childrenCount - 1) === $index));

                $currentColumn = $childCurrentColumn;
            }

            $columnDefinition['exportInfo']['column']['end'] = $childCurrentColumn;
        }

        $columnDefinition['exportInfo']['row']['start'] = self::HEADER_FIRST_ROW + $columnDefinition['depth'];
        $columnDefinition['exportInfo']['row']['end']   = self::HEADER_FIRST_ROW + $columnDefinition['depth'] + ($columnDefinition['exportInfo']['rowsToMerge'] - 1);

        if(!$isLastChild)
        {
            $currentColumn = $this->getNextColumn($currentColumn);
        }

        return $currentColumn;
    }

    private function initColumnHeaders()
    {
        $columnDefinitions = $this->getColumnDefinitions();
        $columnDefinitions = $this->calculateNumberOfColumnsToMerge($columnDefinitions);
        $columnDefinitions = $this->calculateNumberOfRowsToMerge($columnDefinitions);
        $columnDefinitions = $this->calculateColumnAndRowProperties($columnDefinitions);

        $this->columnDefinitions = $columnDefinitions;
    }

    public function generateHeaders()
    {
        $columnDefinitionMap = $this->generateColumnDefinitionMap();

        $this->addHeaderColumns($columnDefinitionMap, $this->colFirst, $this->currentRow);
    }

    private function generateColumnDefinitionMap()
    {
        $columnDefinitionMap = [];

        foreach($this->columnDefinitions as $columnDefinition)
        {
            if($columnDefinition['type'] === ProjectReportColumn::COLUMN_GROUP && array_key_exists('children', $columnDefinition))
            {
                foreach($columnDefinition['children'] as $childColumnDefinition)
                {
                    $childData = $this->generateColumnDefinitionMapRecursively(($childColumnDefinition));

                    if(is_array($childData))
                    {
                        $columnDefinitionMap[$columnDefinition['title']][$childColumnDefinition['title']] = $childData;
                    }
                    else
                    {
                        $columnDefinitionMap[$columnDefinition['title']][] = $childData;
                    }
                }
            }
            else
            {
                $columnDefinitionMap[] = $columnDefinition['title'];
            }
        }

        return $columnDefinitionMap;
    }

    private function generateColumnDefinitionMapRecursively($columnDefinition)
    {
        $columnDefinitionMap = [];

        if($columnDefinition['type'] === ProjectReportColumn::COLUMN_GROUP && array_key_exists('children', $columnDefinition))
        {
            foreach($columnDefinition['children'] as $childColumnDefinition)
            {
                $childData = $this->generateColumnDefinitionMapRecursively(($childColumnDefinition));

                if(is_array($childData))
                {
                    $columnDefinitionMap[$childColumnDefinition['title']] = $childData;
                }
                else
                {
                    $columnDefinitionMap[] = $childData;
                }
            }
        }
        else
        {
            $columnDefinitionMap = $columnDefinition['title'];
        }

        return $columnDefinitionMap;
    }

    private function formatHeaders($columnDefinitions)
    {
        foreach($columnDefinitions as $columnDefinition)
        {
            $startRow    = $columnDefinition['exportInfo']['row']['start'];
            $endRow      = $columnDefinition['exportInfo']['row']['end'];
            $startColumn = $columnDefinition['exportInfo']['column']['start'];
            $endColumn   = $columnDefinition['exportInfo']['column']['end'];
            $rowsToMerge = $columnDefinition['exportInfo']['rowsToMerge'];

            if($rowsToMerge > 1)
            {
                $this->mergeRows($startColumn, $startRow, $rowsToMerge - 1);
            }

            if($columnDefinition['identifier'] === 'number')
            {
                $this->activeSheet->getColumnDimension($startColumn)->setWidth(10);
            }
            else if($columnDefinition['identifier'] === 'approved_date')
            {
                $this->activeSheet->getColumnDimension($startColumn)->setWidth(50);
            }
            else
            {
                $this->activeSheet->getColumnDimension($startColumn)->setAutoSize(true);
            }

            $this->activeSheet->getStyle("{$startColumn}{$startRow}")->applyFromArray($this->getHeaderStyle());

            if(!array_key_exists('children', $columnDefinition) && $columnDefinition['type'] != ProjectReportColumn::COLUMN_GROUP)
            {
                $this->sortedRowRecordTemplate[$columnDefinition['identifier']] = $columnDefinition['title'];

                continue;
            }

            $this->formatHeaders($columnDefinition['children']);
        }
    }

    private function process($sortedRowRecords)
    {
        foreach($sortedRowRecords as $key => $record)
        {
            $rowPosition = 'middle';

            if($key === 0)
            {
                $rowPosition = 'top';
            }
            else if($key === (count($sortedRowRecords) - 1))
            {
                $rowPosition = 'bottom';
            }

            $currentColumn = $this->colFirst;

            foreach($record as $identifier => $value)
            {
                $this->activeSheet->setCellValue("{$currentColumn}{$this->currentRow}", $value);
                $this->activeSheet->getStyle("{$currentColumn}{$this->currentRow}")->getAlignment()->setWrapText(true);
                $this->activeSheet->getStyle("{$currentColumn}{$this->currentRow}")->applyFromArray($this->getRowStyle($rowPosition));

                if(in_array($identifier, ['number', 'approved_date']))
                {
                    $this->activeSheet->getStyle("{$currentColumn}{$this->currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                ++$currentColumn;
            }

            ++$this->currentRow;
        }
    }

    public function generate()
    {
        $this->spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(false);

        $this->maxHeaderDepth = ProjectReportColumn::getMaxDepth($this->template);

        $this->generateTitle();
        $this->initColumnHeaders();
        $this->generateHeaders();
        $this->formatHeaders($this->columnDefinitions);

        $this->currentRow = self::HEADER_FIRST_ROW + $this->maxHeaderDepth;

        $this->process($this->rowRecords);

        return $this->output($this->spreadsheet, $this->sheetTitle);
    }

    protected function getHeaderStyle()
    {
        return array(
            'font'      => array(
                'bold' => true
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

    protected function getRowStyle($position = 'middle')
    {
        $style = array(
            'borders'   => array(
                'left' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
                'right' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => array( 'argb' => '000000' ),
                ),
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_TOP,
                'wrapText'   => true
            )
        );

        if($position == 'top')
        {
            $style['borders']['top'] = array(
                'borderStyle' => Border::BORDER_THIN,
                'color'       => array( 'argb' => '000000' ),
            );
        }
        elseif($position == 'bottom')
        {
            $style['borders']['bottom'] = array(
                'borderStyle' => Border::BORDER_THIN,
                'color'       => array( 'argb' => '000000' ),
            );
        }

        return $style;
    }
}