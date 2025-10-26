<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php
            $headerCount = 6;
        ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('bqReportHeader', array('reportTitle' => $reportTitle, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">No</td>
        <td class="bqHeadCell" style="min-width:480px;width:480px;"><?php echo $descHeader; ?></td>

        <td class="bqHeadCell" style="min-width:100px;width:100px;"><?php echo "Estimate"; ?></td>

        <?php if ( $participate ): ?>
            <td class="bqHeadCell" style="min-width:100px;width:100px;"><?php echo "Rationalized"; ?></td>
        <?php else: ?>
            <td class="bqHeadCell" style="min-width:100px;width:100px;">
                <span style="color:red;">* </span>
                <span style="color:blue;">
                <?php
                    if($selectedTenderer)
                    {
                        if(strlen($selectedTenderer['shortname']))
                        {
                            echo $selectedTenderer['shortname'];
                        }
                        else
                        {
                            echo (strlen($selectedTenderer['name']) > 15) ? substr($selectedTenderer['name'],0,12).'...' : $selectedTenderer['name'];
                        }
                    }
                    else
                    {
                        echo "&nbsp;";
                    }
                ?>
                </span>
            </td>
        <?php endif;?>

        <td class="bqHeadCell" style="min-width:120px;width:120px;"><?php echo "Difference (%)"; ?></td>
        <td class="bqHeadCell" style="min-width:120px;width:120px;"><?php echo "Difference (Amount)"; ?></td>

    </tr>
    <?php
    /*
     * 0 - id
     * 1 - row index
     * 2 - description
     * 3 - level
     * 4 - type
     * 5 - unit
     */
    $rowCount = 0;
    $totalAmount = 0;

    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        $rowCount++;

        $rate = $itemRow ? $itemRow[6] : null;

        $itemId = $itemRow ? $itemRow[0] : null;
        $headerClass = null;
        $itemPadding = 6;
        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>

            <td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
            <td class="bqRateCell">
                <?php if($participate) : ?>
                    <?php echo ! $printNoPrice  && $itemId > 0 && $rationalizedElementTotals && array_key_exists($itemId, $rationalizedElementTotals) && $rationalizedElementTotals[$itemId] != 0 ? number_format($rationalizedElementTotals[$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                <?php else:?>
                    <?php echo ! $printNoPrice  && $itemId > 0 && $selectedElementTotals && array_key_exists($itemId, $selectedElementTotals) && $selectedElementTotals[$itemId] != 0 ? number_format($selectedElementTotals[$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                <?php endif; ?>

                <?php if($participate) : ?>
                    <td class="bqRateCell" style="text-align:center;"><?php echo ! $printNoPrice  && $itemId > 0 && $rate > 0 && $rationalizedElementTotals && array_key_exists($itemId, $rationalizedElementTotals) && $rationalizedElementTotals[$itemId] != 0 ? number_format(($rationalizedElementTotals[$itemId] - $rate) / $rate * 100, 2).' %' : null ?></td>
                <?php else:?>
                    <td class="bqRateCell" style="text-align:center;"><?php echo ! $printNoPrice  && $itemId > 0 && $rate > 0 && $selectedElementTotals && array_key_exists($itemId, $selectedElementTotals) && $selectedElementTotals[$itemId] != 0 ? number_format(($selectedElementTotals[$itemId] - $rate) / $rate * 100, 2).' %' : null ?></td>
                <?php endif; ?>

                <?php if($participate) : ?>
                    <td class="bqRateCell"><?php echo ! $printNoPrice  && $itemId > 0 && $rationalizedElementTotals && array_key_exists($itemId, $rationalizedElementTotals) && $rationalizedElementTotals[$itemId] != 0 ? number_format(($rationalizedElementTotals[$itemId] - $rate), $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                <?php else:?>
                    <td class="bqRateCell"><?php echo ! $printNoPrice  && $itemId > 0 && $selectedElementTotals && array_key_exists($itemId, $selectedElementTotals) && $selectedElementTotals[$itemId] != 0 ? number_format(($selectedElementTotals[$itemId] - $rate), $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                <?php endif; ?>
            </td>
        </tr>
        <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>
        <?php if($printGrandTotal) : ?>
            <tr>
                <td class="footer" style="padding-right:5px;" colspan="<?php echo 2; ?>">
                    <?php echo "Total "; ?> (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount">
                    <?php echo ($estimateBillGrandTotal && array_key_exists('value', $estimateBillGrandTotal) && $estimateBillGrandTotal['value'] != 0) ?  number_format($estimateBillGrandTotal['value'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>
                <?php if($participate) : ?>
                    <td class="footerSumAmount">
                        <?php echo ($rationalizedBillGrandTotal && array_key_exists('value', $rationalizedBillGrandTotal) && $rationalizedBillGrandTotal['value'] != 0) ?  number_format($rationalizedBillGrandTotal['value'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                    </td>
                    <td class="footerSumAmount" style="text-align:center;">
                        <?php echo ($rationalizedBillGrandTotal && $estimateBillGrandTotal && array_key_exists('value', $rationalizedBillGrandTotal) && array_key_exists('value', $estimateBillGrandTotal) && $estimateBillGrandTotal['value'] > 0) ?  number_format(($rationalizedBillGrandTotal['value'] - $estimateBillGrandTotal['value']) / $estimateBillGrandTotal['value'] * 100, 2).'%' : null ;?>
                    </td>
                    <td class="footerSumAmount">
                        <?php echo ($rationalizedBillGrandTotal && $estimateBillGrandTotal && array_key_exists('value', $rationalizedBillGrandTotal) && array_key_exists('value', $estimateBillGrandTotal)) ?  number_format($rationalizedBillGrandTotal['value'] - $estimateBillGrandTotal['value'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                    </td>
                <?php else:?>
                    <td class="footerSumAmount">
                        <?php echo ($selectedBillGrandTotal && $selectedBillGrandTotal['value'] != 0) ?  number_format($selectedBillGrandTotal['value'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                    </td>
                    <td class="footerSumAmount" style="text-align:center;">
                        <?php echo ($selectedBillGrandTotal && $estimateBillGrandTotal && array_key_exists('value', $selectedBillGrandTotal) && array_key_exists('value', $estimateBillGrandTotal) && $estimateBillGrandTotal['value'] > 0) ?  number_format(($selectedBillGrandTotal['value'] - $estimateBillGrandTotal['value']) / $estimateBillGrandTotal['value'] * 100, 2).'%' : null ;?>
                    </td>
                    <td class="footerSumAmount">
                        <?php echo ($selectedBillGrandTotal && $estimateBillGrandTotal && array_key_exists('value', $selectedBillGrandTotal) && array_key_exists('value', $estimateBillGrandTotal)) ?  number_format($selectedBillGrandTotal['value'] - $estimateBillGrandTotal['value'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
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

