<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $rowCount = 0; $headerCount = 7; ?>
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
        <td class="bqHeadCell" style="min-width:400px;width:auto;">Description</td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Original Amount</td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">Total Markup (%)</td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Total Mark Up (<?php echo trim($currency); ?>)</td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Overall Total</td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">% Project</td>
    </tr>

    <?php
        $firstItem = false;

        for($x=0; $x <= $maxRows; $x++):

            $itemPadding             = 6;
            $itemRow                 = isset( $itemPage[$x] ) ? $itemPage[$x] : false;
            $headerClass             = null;
            $headerStyle             = null;
            $borderTopStyling        = null;

            $originalAmountFontColor = 'color: black;';
            $markUpPercentFontColor  = 'color: black;';
            $totalMarkUpFontColor    = 'color: black;';
            $overallTotalFontColor   = 'color: black;';
            $projectPercentFontColor = 'color: black;';

            $rowCount++;

            $originalAmount   = $itemRow ? $itemRow[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_ORIGINAL_AMOUNT] : 0;
            $markUpPercent    = $itemRow ? $itemRow[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_TOTAL_MARKUP_PERCENT] : 0;
            $totalMarkUp      = $itemRow ? $itemRow[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_TOTAL_MARKUP] : 0;
            $overallTotal     = $itemRow ? $itemRow[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_OVERALL_TOTAL] : 0;
            $projectPercent   = $itemRow ? $itemRow[sfBuildSpaceProjectEstimateSummaryReport::ROW_BILL_PROJECT_PERCENT] : 0;
            $itemId           = $itemRow ? $itemRow[0] : null;
            $fontColorStyling = 'colorBlack';

            if ( $originalAmount < 0 )
            {
                $originalAmountFontColor = 'color: red;';
            }

            if ( $markUpPercent < 0 )
            {
                $markUpPercentFontColor = 'color: red;';
            }

            if ( $totalMarkUp < 0 )
            {
                $totalMarkUpFontColor = 'color: red;';
            }

            if ( $overallTotal < 0 )
            {
                $overallTotalFontColor = 'color: red;';
            }

            if ( $projectPercent < 0 )
            {
                $projectPercentFontColor = 'color: red;';
            }

            if ( ! $firstItem )
            {
                $borderTopStyling = 'border-top: 1px solid black;';
            }

            if ($itemRow and ($itemRow[4] == ProjectStructure::TYPE_LEVEL))
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

            <!-- Original Amount -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $originalAmountFontColor; ?>">
                <?php echo ! $printNoPrice && $originalAmount && $originalAmount != 0 ? number_format($originalAmount, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : null ?>
            </td>

            <!-- Total Mark Up (%) -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $markUpPercentFontColor; ?>">
                <?php echo ! $printNoPrice && $markUpPercent && $markUpPercent != 0 ? number_format($markUpPercent, 2, $priceFormatting[0], $priceFormatting[1]).' %' : null ?>
            </td>

            <!-- Total Mark Up -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $totalMarkUpFontColor; ?>">
                <?php echo ! $printNoPrice && $totalMarkUp && $totalMarkUp != 0 ? number_format($totalMarkUp, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : null ?>
            </td>

            <!-- Overall Total -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $overallTotalFontColor; ?>">
                <?php echo ! $printNoPrice && $overallTotal && $overallTotal != 0 ? number_format($overallTotal, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : null ?>
            </td>

            <!-- % Project -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $projectPercentFontColor; ?>">
                <?php echo ! $printNoPrice && $projectPercent && $projectPercent != 0 ? number_format($projectPercent, 2, $priceFormatting[0], $priceFormatting[1]).' %' : null ?>
            </td>
        </tr>
    <?php

        $firstItem = true;
        unset($itemRow, $itemPage[$x], $amount);
        endfor;

        $originalAmountTotalFontColor = 'color: black;';
        $totalMarkUpTotalFontColor    = 'color: black;';
        $overallTotalTotalFontColor   = 'color: black;';
        $projectPercentTotalFontColor = 'color: black;';

        if ( $originalAmountTotal < 0 )
        {
            $originalAmountTotalFontColor = 'color: red;';
        }

        if ( $totalMarkUpTotal < 0 )
        {
            $totalMarkUpTotalFontColor = 'color: red;';
        }

        if ( $overallTotalTotal < 0 )
        {
            $overallTotalTotalFontColor = 'color: red;';
        }

        if ( $projectPercentTotal < 0 )
        {
            $projectPercentTotalFontColor = 'color: red;';
        }
    ?>

    <?php if ( $lastPage ): ?>
    <?php
        $finalTotalMarkUpPercentTotalFontColor = 'color: black;';

        if (! $printNoPrice && $originalAmountTotal && $originalAmountTotal != 0)
        {
            if ( $finalTotalMarkUpPercent > 100 ) $finalTotalMarkUpPercent = 100;

            $finalTotalMarkUpPercent = number_format($finalTotalMarkUpPercent, 2, $priceFormatting[0], $priceFormatting[1]).' %';
        }

        if ( $finalTotalMarkUpPercent < 0 )
        {
            $totalMarkUpPercentTotalFontColor = 'color: red;';
        }
    ?>
    <tr style="border-top: 1px solid black;">
        <td colspan="<?php echo $headerCount - 5; ?>" style="padding-right: 10px;"><strong>Total</strong></td>
        <td style="border: 1px solid black; <?php echo $originalAmountTotalFontColor; ?>">
            <?php echo ! $printNoPrice && $originalAmountTotal && $originalAmountTotal != 0 ? number_format($originalAmountTotal, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : null; ?>
        </td>
        <td style="border: 1px solid black; <?php echo $finalTotalMarkUpPercentTotalFontColor; ?>"><?php echo $finalTotalMarkUpPercent; ?></td>
        <td style="border: 1px solid black; <?php echo $totalMarkUpTotalFontColor; ?>"><?php echo ! $printNoPrice && $totalMarkUpTotal && $totalMarkUpTotal != 0 ? number_format($totalMarkUpTotal, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : null; ?></td>
        <td style="border: 1px solid black; <?php echo $overallTotalTotalFontColor; ?>"><?php echo ! $printNoPrice && $overallTotalTotal && $overallTotalTotal != 0 ? number_format($overallTotalTotal, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : null; ?></td>
        <td style="border: 1px solid black; <?php echo $projectPercentTotalFontColor; ?>">
            <?php
            $percentage = NULL;

            if (! $printNoPrice && $projectPercentTotal && $projectPercentTotal != 0)
            {
                $percentage = NULL;
                if ( $projectPercentTotal > 100 ) $projectPercentTotal = 100;

                $percentage = number_format($projectPercentTotal, 2, $priceFormatting[0], $priceFormatting[1]).' %';
            }

            echo $percentage;
            ?>
        </td>
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