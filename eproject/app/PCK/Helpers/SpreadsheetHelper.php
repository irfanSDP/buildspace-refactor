<?php namespace PCK\Helpers;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Color;

class SpreadsheetHelper {

    const BORDER_ALL     = "allBorders";
    const BORDER_OUTLINE = "outline";

    /**
     * @deprecated Use the ReportGenerator class or define it in the calling class.
     *
     * @param $start starting column, index is 1-based
     * @param $end   ending column, index is 1-based
     * @param $row   row index, index is 1-based
     */
    public static function cellsToMergeByColsRow($start = -1, $end = -1, $row = -1)
    {
        $merge = null;

        if( $start >= 0 && $end >= 0 && $row >= 0 )
        {
            $start = Coordinate::stringFromColumnIndex($start);
            $end   = Coordinate::stringFromColumnIndex($end);
            $merge = "$start{$row}:$end{$row}";
        }

        return $merge;
    }

    /**
     * gets Range by starting column, starting row, and ending column, ending row
     * example: "A1:F10"
     * @deprecated Use the ReportGenerator class or define it in the calling class.
     *
     * @param $startCol starting column, index is 1-based
     * @param $startRow starting row, index is 1-based
     * @param $endCol   ending column, index is 1-based
     * @param $endRow   ending row, index is 1-based
     */
    public static function getRangeByColumnAndRow($startCol, $startRow, $endCol, $endRow)
    {
        $range = null;

        if( $startCol >= 1 && $startRow >= 1 && $endCol >= 1 && $endRow >= 1 )
        {
            $start = Coordinate::stringFromColumnIndex($startCol) . $startRow;
            $end   = Coordinate::stringFromColumnIndex($endCol) . $endRow;
            $range = "$start:$end";
        }

        return $range;
    }

    /**
     * get cell style array formatter
     * @deprecated Use the ReportGenerator class or define it in the calling class.
     *
     * @param $isFontBold sets font weight, bolder or regular
     * @param $fontColor  sets the font of the font, accepts constants of Color class of PHPSpreadsheet package or hexadecimal with '#' omitted
     * @param $horizontal alignment of cell, accepts constants of Alignment class of PHPSpreadsheet package
     * @param $fill       type of a cell's background color, accepts constants of Fill class of PHPSpreadsheet package
     * @param $fill       color of a cell's backgrond color, accpets constants of Color class of PHPSpreadsheet package or hexadecimal with '#' omitted
     */
    public static function getCellStyleArrayFormatter($isFontBold, $fontColor, $horizontalAlignment, $fillType, $fillColor)
    {
        return [
            'font'      => [
                'bold'  => $isFontBold,
                'color' => [
                    'argb' => $fontColor,
                ],
            ],
            'alignment' => [
                'horizontal' => $horizontalAlignment,
            ],
            'fill'      => [
                'fillType' => $fillType,
                'color'    => [
                    'argb' => $fillColor,
                ],
            ],
        ];
    }

    /**
     * get border style array formatter
     * @deprecated Use the ReportGenerator class or define it in the calling class.
     *
     * @param $borderType  sets the border type, use constants of this class
     * @param $borderStyle sets the style of border, use constants of Border class of PHPSpreadsheet package
     * @param $color       sets the color of the border, use constants of Color class of PHPSpreadsheet package
     */
    public static function getBorderStyleArrayFormatter($borderType, $borderStyle, $color = Color::COLOR_BLACK)
    {
        return [
            'borders' => [
                $borderType => [
                    'borderStyle' => $borderStyle,
                    'color'       => [ 'argb' => $color ],
                ],
            ],
        ];
    }

    public static function loadSpreadsheet($inputFileName, $readDataOnly = true)
    {
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

        $reader->setReadDataOnly($readDataOnly);

        return $reader->load($inputFileName);
    }
}