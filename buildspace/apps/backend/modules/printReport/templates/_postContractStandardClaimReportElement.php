<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php
            if($workdoneOnly)
            {
                $headerCount = 5;
            }
            else
            {
                $headerCount = 9;
            }
        ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('printReport/postContractReportHeader', array('reportTitle' => $reportTitle, 'headerDescription' => $headerDescription, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" rowspan="2" style="min-width:40px;width:40px;">No.</td>

        <?php if(!$workdoneOnly) : ?>
            <td class="bqHeadCell" rowspan="2" style="min-width:280px;width:280px;"><?php echo $descHeader; ?></td>
        <?php else: ?>
            <td class="bqHeadCell" rowspan="2" style="min-width:340px;width:340px;"><?php echo $descHeader; ?></td>
        <?php endif; ?>

        <?php if(!$workdoneOnly) : ?>
            <td class="bqHeadCell" rowspan="2" style="min-width:80px;width:80px;"><?php echo "Contract Amount"; ?></td>
        <?php else: ?>
            <td class="bqHeadCell" rowspan="2" style="min-width:120px;width:120px;"><?php echo "Contract Amount"; ?></td>
        <?php endif; ?>

        <?php if(!$workdoneOnly) : ?>
            <td class="bqHeadCell" colspan="2"><?php echo "Previous Payment"; ?></td>
        <?php endif; ?>
        <td class="bqHeadCell" colspan="2"><?php echo "Work Done"; ?></td>
        <?php if(!$workdoneOnly) : ?>
            <td class="bqHeadCell" colspan="2"><?php echo "Current Payment"; ?></td>
        <?php endif; ?>
    </tr>
    <tr>
        <?php if(!$workdoneOnly) : ?>
            <td class="bqHeadCell" style="min-width:40px;width:40px;"><?php echo "%"; ?></td>
            <td class="bqHeadCell" style="min-width:80px;width:80px;"><?php echo "Amount"; ?></td>
        <?php endif; ?>

        <?php if(!$workdoneOnly) : ?>
            <td class="bqHeadCell" style="min-width:40px;width:40px;"><?php echo "%"; ?></td>
            <td class="bqHeadCell" style="min-width:80px;width:80px;"><?php echo "Amount"; ?></td>
        <?php else: ?>
            <td class="bqHeadCell" style="min-width:100px;width:100px;"><?php echo "%"; ?></td>
            <td class="bqHeadCell" style="min-width:150px;width:150px;"><?php echo "Amount"; ?></td>
        <?php endif; ?>

        <?php if(!$workdoneOnly) : ?>
            <td class="bqHeadCell" style="min-width:40px;width:40px;"><?php echo "%"; ?></td>
            <td class="bqHeadCell" style="min-width:80px;width:80px;"><?php echo "Amount"; ?></td>
        <?php endif; ?>
    </tr>
    <?php

    $rowCount = 0;
    $totalAmount = 0;

    // $totalItemToPrint = count($itemPage);
    // $endRow = false;

    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        $rowCount++;

        $contractAmount  = $itemRow ? $itemRow[sfBuildspacePostContractReportPageElementGenerator::ROW_BILL_ITEM_CONTRACT_AMOUNT] : null;

        $prev_percentage = $itemRow ? $itemRow[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_PREVIOUS]['prev_percentage'] : null;
        $prev_amount     = $itemRow ? $itemRow[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_PREVIOUS]['prev_amount'] : null;

        $current_percentage = $itemRow ? $itemRow[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_CURRENT]['current_percentage'] : null;
        $current_amount     = $itemRow ? $itemRow[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_CURRENT]['current_amount'] : null;

        $workdone_percentage = $itemRow ? $itemRow[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE]['up_to_date_percentage'] : null;
        $workdone_amount     = $itemRow ? $itemRow[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE]['up_to_date_amount'] : null;

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
                <?php include_partial('printReport/bqReportComparisonItem/itemRateOnly', array(
                    'itemRow'                  => $itemRow,
                    'rate'                     => number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]),
                    'rationalizedRates'        => $rationalizedRates,
                    'selectedRates'            => $selectedRates,
                    'itemId'                   => $itemId,
                    'participate'              => $participate,
                    'rateCommaRemove'          => $rateCommaRemove,
                    'priceFormatting'          => $priceFormatting,
                    'printNoPrice'             => $printNoPrice,
                    'toggleColumnArrangement'  => $toggleColumnArrangement,
                    'printDollarAndCentColumn' => $printDollarAndCentColumn,
                    'printAmountOnly'          => $printAmountOnly,
                    'printQty'                 => $printQty
                ));
            ?>

            <?php else: ?>

                <td class="bqRateCell"><?php echo ! $printNoPrice && $contractAmount && $contractAmount != 0 ? number_format($contractAmount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>

                <?php if(!$workdoneOnly) : ?>
                    <td class="bqRateCell" style="text-align:right;">
                        <?php echo ! $printNoPrice && $prev_percentage && $prev_percentage != 0 ? number_format($prev_percentage,2).'%' : null ?>
                    </td>
                    <td class="bqRateCell">
                        <?php echo ! $printNoPrice && $prev_amount && $prev_amount != 0 ? number_format($prev_amount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                    </td>
                <?php endif; ?>

                <td class="bqRateCell" style="text-align:right;">
                    <?php echo ! $printNoPrice && $workdone_percentage && $workdone_percentage != 0 ? number_format($workdone_percentage,2).'%' : null ?>
                </td>
                <td class="bqRateCell">
                    <?php echo ! $printNoPrice && $workdone_amount && $workdone_amount != 0 ? number_format($workdone_amount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                </td>

                <?php if(!$workdoneOnly) : ?>
                    <td class="bqRateCell" style="text-align:right;">
                        <?php echo ! $printNoPrice && $current_percentage && $current_percentage != 0 ?  number_format($current_percentage,2).'%' : null ?>
                    </td>
                    <td class="bqRateCell">
                        <?php echo ! $printNoPrice && $current_amount && $current_amount != 0 ? number_format($current_amount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                    </td>
                <?php endif; ?>

            <?php endif; ?>
        </tr>
            <?php unset($itemPage[$x], $amount);?>

        <?php endfor; ?>

        <?php if($printGrandTotal) : ?>
            <?php if(!$workdoneOnly) : ?>
                <tr>
                    <td class="footer" style="padding-right:5px;" colspan="<?php echo 2; ?>">
                        <?php echo "Total "; ?> (<?php echo $currency->currency_code; ?>) :
                    </td>
                    <td class="footerSumAmount">
                        <?php echo ($typeTotals && array_key_exists('total_per_unit', $typeTotals) && $typeTotals['total_per_unit'] != 0) ?  number_format($typeTotals['total_per_unit'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                    </td>
                    <td class="footerSumAmount" style="text-align:right;">
                        <?php echo ($typeTotals && array_key_exists('prev_percentage', $typeTotals) && $typeTotals['prev_percentage'] != 0) ?  number_format($typeTotals['prev_percentage'],2).'%' : null ;?>
                    </td>
                    <td class="footerSumAmount">
                        <?php echo ($typeTotals && array_key_exists('prev_amount', $typeTotals) && $typeTotals['prev_amount'] != 0) ?  number_format($typeTotals['prev_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                    </td>
                    <td class="footerSumAmount" style="text-align:right;">
                        <?php echo ($typeTotals && array_key_exists('up_to_date_percentage', $typeTotals) && $typeTotals['up_to_date_percentage'] != 0) ?  number_format($typeTotals['up_to_date_percentage'],2).'%' : null ;?>
                    </td>
                    <td class="footerSumAmount">
                        <?php echo ($typeTotals && array_key_exists('up_to_date_amount', $typeTotals) && $typeTotals['up_to_date_amount'] != 0) ?  number_format($typeTotals['up_to_date_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                    </td>
                    <td class="footerSumAmount" style="text-align:right;">
                        <?php echo ($typeTotals && array_key_exists('current_percentage', $typeTotals) && $typeTotals['current_percentage'] != 0) ?  number_format($typeTotals['current_percentage'],2).'%' : null ;?>
                    </td>
                    <td class="footerSumAmount">
                        <?php echo ($typeTotals && array_key_exists('current_amount', $typeTotals) && $typeTotals['current_amount'] != 0) ?  number_format($typeTotals['current_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                    </td>
                </tr>
            <?php else: ?>
                <tr>
                    <td class="footer" style="padding-right:5px;" colspan="<?php echo 2; ?>">
                        <?php echo "Total"; ?> (<?php echo $currency->currency_code; ?>) :
                    </td>
                    <td class="footerSumAmount">
                        <?php echo ($typeTotals && array_key_exists('total_per_unit', $typeTotals) && $typeTotals['total_per_unit'] != 0) ?  number_format($typeTotals['total_per_unit'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                    </td>
                     <td class="footerSumAmount" style="text-align:right;">
                        <?php echo ($typeTotals && array_key_exists('up_to_date_percentage', $typeTotals) && $typeTotals['up_to_date_percentage'] != 0) ?  number_format($typeTotals['up_to_date_percentage'],2).'%' : null ;?>
                    </td>
                    <td class="footerSumAmount">
                        <?php echo ($typeTotals && array_key_exists('up_to_date_amount', $typeTotals) && $typeTotals['up_to_date_amount'] != 0) ?  number_format($typeTotals['up_to_date_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                    </td>
                </tr>
            <?php endif; ?>
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
    <tr>
</table>
</body>
</html>