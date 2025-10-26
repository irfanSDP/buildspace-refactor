<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php
            $headerCount = 8;

            if($printQty)
                $headerCount++;

            if($printPercentage)
                $headerCount++;
        ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('printReport/postContractReportHeader', array('reportTitle' => $reportTitle, 'headerDescription' => $headerDescription, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <?php
            $span = 1;

            if($printQty)
                $span++;

            if($printPercentage)
                $span++;
        ?>
        <td class="bqHeadCell" rowspan="2" style="min-width:60px;width:60px;">No.</td>
        <td class="bqHeadCell" rowspan="2" style="min-width:280px;width:280px;"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell" rowspan="2" style="min-width:60px;width:60px;"><?php echo "Qty"; ?></td>
        <td class="bqHeadCell" rowspan="2" style="min-width:60px;width:60px;"><?php echo "Unit"; ?></td>
        <td class="bqHeadCell" rowspan="2" style="min-width:70px;width:70px;"><?php echo "Rate"; ?></td>
        <td class="bqHeadCell" rowspan="2" style="min-width:100px;width:100px;"><?php echo "Contract Amount"; ?></td>
        <td class="bqHeadCell" colspan="<?php echo $span; ?>"><?php echo "Work Done"; ?></td>
    </tr>
    <tr>

        <?php if($printQty) : ?>
            <td class="bqHeadCell" style="min-width:60px;width:60px;"><?php echo "Qty"; ?></td>
        <?php endif; ?>

        <?php if($printPercentage) : ?>
            <td class="bqHeadCell" style="min-width:60px;width:60px;"><?php echo "%"; ?></td>
        <?php endif; ?>

        <td class="bqHeadCell" style="min-width:100px;width:100px;"><?php echo "Amount"; ?></td>
    </tr>
    <?php

    $rowCount = 0;
    $totalAmount = 0;

    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        $rowCount++;

        $rate                = $itemRow ? $itemRow[sfBuildspacePostContractReportPageItemGenerator::ROW_BILL_ITEM_RATE] : NULL;
        $contractAmount      = $itemRow ? $itemRow[sfBuildspacePostContractReportPageItemGenerator::ROW_BILL_ITEM_CONTRACT_AMOUNT] : NULL;

        $unit                = $itemRow ? $itemRow[sfBuildspacePostContractReportPageItemGenerator::ROW_BILL_ITEM_UNIT] : NULL;
        $qty                 = $itemRow ? $itemRow[sfBuildspacePostContractReportPageItemGenerator::ROW_BILL_ITEM_QTY_PER_UNIT] : NULL;

        $workdone_percentage = $itemRow ? $itemRow[sfBuildspacePostContractReportPageItemGenerator::ROW_CLAIM_WORKDONE]['up_to_date_percentage'] : NULL;
        $workdone_qty        = $itemRow ? $itemRow[sfBuildspacePostContractReportPageItemGenerator::ROW_CLAIM_WORKDONE]['up_to_date_qty'] : NULL;
        $workdone_amount     = $itemRow ? $itemRow[sfBuildspacePostContractReportPageItemGenerator::ROW_CLAIM_WORKDONE]['up_to_date_amount'] : NULL;
        $itemId              = $itemRow ? $itemRow[0] : NULL;

        if ($itemRow and ($itemRow[4] == BillItem::TYPE_HEADER OR $itemRow[4] == BillItem::TYPE_HEADER_N))
        {
            $headerClass = 'bqHead'.$itemRow[3];
            $headerStyle = null;
        }
        elseif($itemRow and $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT)
        {
            $headerClass = 'elementHeader';
            $headerStyle = 'font-style: italic;';

            if ( $alignElementTitleToTheLeft )
            {
                $headerClass .= ' alignLeft';
            }
            else
            {
                $headerClass .= ' alignCenter';
            }
        }
        else
        {
            $headerClass = null;
            $headerStyle = null;
        }

        if ( $indentItem AND $itemRow AND ($itemRow[4] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER AND $itemRow[4] != BillItem::TYPE_HEADER_N) )
        {
            $itemPadding = 15;
        }
        else
        {
            $itemPadding = 6;
        }

        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>

            <?php if ( $itemRow[4] == BillItem::TYPE_ITEM_RATE_ONLY ): ?>
                <td class="bqRateCell">
                    &nbsp;
                </td>
                <td class="bqRateCell">
                    &nbsp;
                </td>
                <td class="bqRateCell">
                    &nbsp;
                </td>
                <td class="bqRateCell">
                    &nbsp;
                </td>
                <td class="bqRateCell">
                    &nbsp;
                </td>
                <td class="bqRateCell">
                    &nbsp;
                </td>
                <td class="bqRateCell">
                    &nbsp;
                </td>
            ?>

            <?php else: ?>

                <td class="bqRateCell">
                    <?php echo $qty && $qty != 0 ? Utilities::number_clean(number_format($qty, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])) : null?>
                </td>

                <td class="bqRateCell" style="text-align: center;">
                    <?php echo $unit ? $unit : null?>
                </td>

                <td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>

                <?php if ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM || $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE || $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT ): ?>
                    <td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                <?php else: ?>
                    <td class="bqRateCell"><?php echo ! $printNoPrice && $contractAmount && $contractAmount != 0 ? number_format($contractAmount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                <?php endif; ?>

                <?php if($printQty): ?>
                    <?php if ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM || $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE || $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT ): ?>
                        <td class="bqRateCell">
                            <?php echo $workdone_percentage && $workdone_percentage != 0 ? $workdone_percentage.'%' : null ?>
                        </td>
                    <?php else: ?>
                        <td class="bqRateCell">
                            <?php echo $workdone_qty && $workdone_qty != 0 ? Utilities::number_clean(number_format($workdone_qty, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])) : null ?>
                        </td>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if($printPercentage): ?>
                    <td class="bqRateCell">
                        <?php echo $workdone_percentage && $workdone_percentage != 0 ? number_format($workdone_percentage,2).'%' : null ?>
                    </td>
                <?php endif; ?>

                <td class="bqRateCell">
                    <?php echo ! $printNoPrice && $workdone_amount && $workdone_amount != 0 ? number_format($workdone_amount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                </td>

            <?php endif; ?>
        </tr>
            <?php unset($itemPage[$x], $amount);?>

        <?php endfor; ?>

        <?php if($printGrandTotal) : ?>
            <tr>
                <td class="footer" style="padding-right:5px;" colspan="<?php echo 5; ?>">
                    <?php echo "Total "; ?> (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount">
                    <?php echo ($elementTotals && array_key_exists('total_per_unit', $elementTotals) && $elementTotals['total_per_unit'] != 0) ?  number_format($elementTotals['total_per_unit'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>

                <?php if($printQty): ?>
                    <td class="footerSumAmount">
                        <?php echo ($elementTotals && array_key_exists('up_to_date_percentage', $elementTotals) && $elementTotals['up_to_date_percentage'] != 0) ?  number_format($elementTotals['up_to_date_percentage'],2).'%' : null ;?>
                    </td>
                <?php endif; ?>

                <?php if($printPercentage): ?>
                    <td class="footerSumAmount">
                        <?php echo ($elementTotals && array_key_exists('up_to_date_percentage', $elementTotals) && $elementTotals['up_to_date_percentage'] != 0) ?  number_format($elementTotals['up_to_date_percentage'],2).'%' : null ;?>
                    </td>
                <?php endif; ?>

                <td class="footerSumAmount">
                    <?php echo ($elementTotals && array_key_exists('up_to_date_amount', $elementTotals) && $elementTotals['up_to_date_amount'] != 0) ?  number_format($elementTotals['up_to_date_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
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
        <?php endif;?>
</table>
</body>
</html>

