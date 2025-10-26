<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $headerCount = 7; ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('tradeReportHeader',
                array(
                    'reportTitle'       => $reportTitle,
                    'headerDescription' => $headerDescription,
                    'topLeftRow1'       => $topLeftRow1,
                    'topLeftRow2'       => $topLeftRow2
                )
            );
            ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:70px;width:70px;">No.</td>
        <td class="bqHeadCell" style="min-width:280px;width:280px;">Description</td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">Factor</td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Length</td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Width</td>
        <td class="bqHeadCell" style="min-width:140px;width:140px;">Total</td>
        <td class="bqHeadCell" style="min-width:40px;width:40px;">(+/-)</td>
    </tr>
    <?php

        $rowCount = 0;

        $totalAmount = 0;

        for($x=0; $x <= $maxRows; $x++):

            $itemPadding = 6;
            $itemRow     = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;
            $headerClass = null;
            $headerStyle = null;

            $rowCount++;

            $factor           = $itemRow ? $itemRow[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_FACTOR] : null;
            $length           = $itemRow ? $itemRow[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_LENGTH] : null;
            $width            = $itemRow ? $itemRow[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_WIDTH] : null;
            $total            = $itemRow ? $itemRow[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_TOTAL] : null;
            $sign             = $itemRow ? $itemRow[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_SIGN] : null;
            $itemId           = $itemRow ? $itemRow[0] : null;
            $fontColorStyling = ($sign == '-') ? 'colorRed' : 'colorBlack';

            if ($itemRow and ($itemRow[4] == ResourceItem::TYPE_HEADER))
            {
                $headerClass = 'bqHead'.$itemRow[3];
                $headerStyle = null;
            }
        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $fontColorStyling; ?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>
            <td class="bqRateCell <?php echo $fontColorStyling; ?>">
                <?php echo ! $printNoPrice && $factor && $factor != 0 ? number_format($factor, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
            <td class="bqQtyCell <?php echo $fontColorStyling; ?>">
                <?php echo ! $printNoPrice && $length && $length != 0 ? number_format($length, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
            <td class="bqRateCell <?php echo $fontColorStyling; ?>">
                <?php echo ! $printNoPrice && $width && $width != 0 ? number_format($width, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
            <td class="bqRateCell <?php echo $fontColorStyling; ?>">
                <?php echo ! $printNoPrice && $total && $total != 0 ? number_format($total, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
            <td class="bqUnitCell <?php echo $fontColorStyling; ?>">
                <?php echo ($itemRow[sfFloorAreaPrintOutGenerator::ROW_BILL_ITEM_ID]) ? $sign : "&nbsp;"; ?>
            </td>
        </tr>
            <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>

    <?php if ( $lastPage ): ?>
    <tr>
        <td class="footer" style="padding-right:5px;" colspan="5">Total Floor Area</td>
        <td class="<?php echo ($summary[0]['total_floor_area'] < 0) ? 'colorRed' : 'colorBlack' ?> footerSumAmount">
            <?php echo ! $printNoPrice && $summary[0]['total_floor_area'] ? number_format($summary[0]['total_floor_area'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
        </td>
        <td class="footer">&nbsp;</td>
    </tr>
    <tr>
        <td class="footer" style="padding-right:5px; border-top: none;" colspan="5">Conversion Factor</td>
        <td class="footerSumAmount"><?php echo $summary[0]['conversion_factor_operator']; ?></td>
    </tr>
    <tr>
        <td class="footer" style="padding-right:5px; border-top: none;" colspan="5">Conversion Factor Amount</td>
        <td class="<?php echo ($summary[0]['conversion_factor_amount'] < 0) ? 'colorRed' : 'colorBlack' ?> footerSumAmount">
            <?php echo ! $printNoPrice && $summary[0]['conversion_factor_amount'] ? number_format($summary[0]['conversion_factor_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
        </td>
    </tr>
    <tr>
        <td class="footer" style="padding-right:5px; border-top: none;" colspan="5">Final Floor Area</td>
        <td class="<?php echo ($summary[0]['final_floor_area'] < 0) ? 'colorRed' : 'colorBlack' ?> footerSumAmount">
            <?php echo ! $printNoPrice && $summary[0]['final_floor_area'] ? number_format($summary[0]['final_floor_area'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
        </td>
    </tr>
    <tr>
        <td style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
            Page <?php echo $pageCount; ?> of <?php echo $totalPage; ?>
        </td>
    </tr>
    <?php else: ?>
    <tr>
        <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
            Page <?php echo $pageCount; ?> of <?php echo $totalPage; ?>
        </td>
    </tr>
    <?php endif; ?>
</table>
</body>
</html>