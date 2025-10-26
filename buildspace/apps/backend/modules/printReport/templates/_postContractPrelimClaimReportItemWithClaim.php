<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $headerCount = 9; ?>
        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('printReport/postContractReportHeader', array('reportTitle' => $reportTitle, 'headerDescription' => $headerDescription, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" rowspan="2" style="min-width:80px;width:80px;">Bill Ref</td>
        <td class="bqHeadCell" rowspan="2" style="min-width:300px;width:300px;"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell" rowspan="2" style="min-width:80px;width:80px;"><?php echo "Contract Amount"; ?></td>
        <td class="bqHeadCell" rowspan="2" style="min-width:80px;width:80px;"><?php echo "Initial Payment"; ?></td>
        <td class="bqHeadCell" rowspan="2" style="min-width:80px;width:80px;"><?php echo "Recurring Payment"; ?></td>
        <td class="bqHeadCell" rowspan="2" style="min-width:80px;width:80px;"><?php echo "Final Payment"; ?></td>
        <td class="bqHeadCell" colspan="2"><?php echo "Total Payment"; ?></td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:60px;width:60px;"><?php echo "%"; ?></td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;"><?php echo "Amount"; ?></td>
    </tr>
    <?php

    $rowCount = 0;
    $totalAmount = 0;

    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        $rowCount++;

        $rate = $itemRow ? $itemRow[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_BILL_ITEM_RATE] : null;
        $contractAmount  = $itemRow ? $itemRow[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_BILL_ITEM_CONTRACT_AMOUNT] : null;

        $initial_percentage   = $itemRow ? $itemRow[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_INITIAL]['percentage'] : null;
        $initial_amount       = $itemRow ? $itemRow[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_INITIAL]['amount'] : null;

        $recurring_percentage = $itemRow ? $itemRow[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_RECURRING]['percentage'] : null;
        $recurring_amount     = $itemRow ? $itemRow[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_RECURRING]['amount'] : null;

        $final_percentage     = $itemRow ? $itemRow[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_FINAL]['percentage'] : null;
        $final_amount         = $itemRow ? $itemRow[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_FINAL]['amount'] : null;

        $total_percentage     = $itemRow ? $itemRow[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_TOTAL]['percentage'] : null;
        $total_amount         = $itemRow ? $itemRow[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_TOTAL]['amount'] : null;

        $itemId = $itemRow ? $itemRow[0] : null;

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
            ?>

            <?php else: ?>

                <?php if ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM || $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE || $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT ): ?>
                    <td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                <?php else: ?>
                    <td class="bqRateCell"><?php echo ! $printNoPrice && $contractAmount && $contractAmount != 0 ? number_format($contractAmount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                <?php endif; ?>

                <td class="bqRateCell">
                    <?php echo ! $printNoPrice && $initial_amount && $initial_amount != 0 ? number_format($initial_amount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                </td>
                <td class="bqRateCell">
                    <?php echo ! $printNoPrice && $recurring_amount && $recurring_amount != 0 ? number_format($recurring_amount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                </td>
                <td class="bqRateCell">
                    <?php echo ! $printNoPrice && $final_amount && $final_amount != 0 ? number_format($final_amount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                </td>

                <td class="bqRateCell">
                    <?php echo ! $printNoPrice && $total_percentage && $total_percentage != 0 ? number_format($total_percentage,2).'%' : null ?>
                </td>
                <td class="bqRateCell">
                    <?php echo ! $printNoPrice && $total_amount && $total_amount != 0 ? number_format($total_amount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                </td>

            <?php endif; ?>
        </tr>
            <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>

        <?php if($printGrandTotal) : ?>
            <tr>
                <td class="footer" style="padding-right:5px;" colspan="<?php echo 2; ?>">
                    <?php echo "Total"; ?> (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount">
                    <?php echo ($elementTotals && $elementTotals['grand_total'] != 0) ?  number_format($elementTotals['grand_total'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>
                <td class="footerSumAmount">
                    <?php echo ($elementTotals && $elementTotals['initial-amount'] != 0) ?  number_format($elementTotals['initial-amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>
                <td class="footerSumAmount">
                    <?php echo ($elementTotals && $elementTotals['recurring-amount'] != 0) ?  number_format($elementTotals['recurring-amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>
                <td class="footerSumAmount">
                    <?php echo ($elementTotals && $elementTotals['final-amount'] != 0) ?  number_format($elementTotals['final-amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>

                <?php if ( $type == 'currentClaim-amount' ): ?>
                <td class="footerSumAmount">
                    <?php echo ($elementTotals && $elementTotals['currentClaim-percentage'] != 0) ?  number_format($elementTotals['currentClaim-percentage'],2).'%' : null ;?>
                </td>
                <td class="footerSumAmount">
                    <?php echo ($elementTotals && $elementTotals['currentClaim-amount'] != 0) ?  number_format($elementTotals['currentClaim-amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>
                <?php else: ?>
                <td class="footerSumAmount">
                    <?php echo ($elementTotals && $elementTotals['upToDateClaim-percentage'] != 0) ?  number_format($elementTotals['upToDateClaim-percentage'],2).'%' : null ;?>
                </td>
                <td class="footerSumAmount">
                    <?php echo ($elementTotals && $elementTotals['upToDateClaim-amount'] != 0) ?  number_format($elementTotals['upToDateClaim-amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>
                <?php endif; ?>
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

