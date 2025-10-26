<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $rowCount = 0; $headerCount = 6; ?>
        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('bqReportHeader', array(
                'reportTitle'  => $printingPageTitle,
                'topLeftRow1'  => NULL,
                'topRightRow1' => $columnDescription,
                'topLeftRow2'  => $projectTitle,
                'topRightRow2' => $billDescription,
            ));
            ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">No</td>
        <td class="bqHeadCell" style="min-width:400px;width:auto;">Element Description</td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">%</td>

        <?php if ( $billColumnSetting['floor_area_display_metric'] ): ?>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Cost/m2</td>
        <?php else: ?>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Cost/ft2</td>
        <?php endif; ?>

        <td class="bqHeadCell" style="min-width:100px;width:100px;">Total</td>
    </tr>

    <?php
        $firstItem = false;

        for($x=0; $x <= $maxRows; $x++):

            $itemPadding      = 6;
            $itemRow          = isset($itemPage[$x]) ? $itemPage[$x] : false;
            $headerClass      = null;
            $headerStyle      = null;
            $borderTopStyling = null;

            // column's formula color if available
            $numberColor   = 'color: black;';
            $constantColor = 'color: black;';
            $qtyColor      = 'color: black;';
            $rateColor     = 'color: black;';
            $wastageColor  = 'color: black;';

            $rowCount++;

            $percentage       = $itemRow ? $itemRow[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_PERCENTAGE] : 0;
            $totalCost        = $itemRow ? $itemRow[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_TOTAL_COST] : 0;
            $total            = $itemRow ? $itemRow[sfBillElementEstimateSummaryByTypeReportGenerator::ROW_BILL_ITEM_TOTAL_PER_UNIT] : 0;

            $itemId           = $itemRow ? $itemRow[0] : null;
            $fontColorStyling = 'colorBlack';

            if ( ! $firstItem )
            {
                $borderTopStyling = 'border-top: 1px solid black;';
            }

            if ($itemRow and ($itemRow[4] == ResourceItem::TYPE_HEADER))
            {
                $headerClass = 'bqHead1';
                $headerStyle = 'text-decoration:underline;';
            }
        ?>
        <tr>
            <td class="bqCounterCell" style="<?php echo $borderTopStyling; ?>"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell" style="padding-left: <?php echo $itemPadding; ?>px; <?php echo $borderTopStyling; ?>">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>

            <!-- % -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $numberColor; ?>">
                <?php echo ! $printNoPrice && $percentage && $percentage != 0 ? number_format($percentage, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]).' %' : null ?>
            </td>

            <!-- Cost m2 or ft2 -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $constantColor; ?>">
                <?php echo ! $printNoPrice && $totalCost && $totalCost != 0 ? number_format($totalCost, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>

            <!-- Total -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $qtyColor; ?>">
                <?php echo ! $printNoPrice && $total && $total != 0 ? number_format($total, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
        </tr>
    <?php $firstItem = true; unset($itemRow, $itemPage[$x], $amount); endfor; ?>

    <?php if ( $lastPage ): ?>
    <tr style="border-top: 1px solid black;">
        <td colspan="<?php echo $headerCount - 4; ?>" style="padding-right: 10px;"><strong>Total</strong></td>
        <td style="border: 1px solid black;">
            <?php
            $percentage = NULL;

            if (! $printNoPrice && $percentageTotal && $percentageTotal != 0)
            {
                $percentage = NULL;
                if ( $percentageTotal > 100 ) $percentageTotal = 100;

                $percentage = number_format($percentageTotal, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]).' %';
            }

            echo $percentage;
            ?>
        </td>
        <td style="border: 1px solid black;"><?php echo ! $printNoPrice && $totalCostTotal && $totalCostTotal != 0 ? number_format($totalCostTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
        <td style="border: 1px solid black;"><?php echo ! $printNoPrice && $overallElementTotal && $overallElementTotal != 0 ? number_format($overallElementTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
    </tr>
    <tr>
        <td style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
            Page <?php echo $pageCount; ?>
        </td>
    </tr>
    <?php else: ?>
    <tr>
        <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
            Page <?php echo $pageCount; ?>
        </td>
    </tr>
    <?php endif; ?>
</table>
</body>
</html>