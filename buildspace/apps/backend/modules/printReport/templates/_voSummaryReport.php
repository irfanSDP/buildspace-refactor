<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $headerCount = 4; ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('printReport/postContractReportHeader', array('reportTitle' => $reportTitle, 'headerDescription' => $headerDescription, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" rowspan="2" style="min-width:40px;width:40px;">
            No.
        </td>
        <td class="bqHeadCell" rowspan="2" style="min-width:400px;width:400px;">
            <?php echo $descHeader; ?>
        </td>
        <td class="bqHeadCell" colspan="2">
            <?php echo "Amount"; ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:130px;width:130px;">
            <?php echo "Omission" ?>
        </td>
        <td class="bqHeadCell" style="min-width:130px;width:130px;">
            <?php echo "Addition"; ?>
        </td>
    </tr>
    <?php

    $rowCount = 0;
    $totalAmount = 0;

    for($x=0; $x <= $maxRows; $x++):
        
        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        $rowCount++;

        $isApprove  = $itemRow ? $itemRow[sfBuildspaceVOSummaryReportGenerator::ROW_APPROVED] : null;
        $omission   = $itemRow ? $itemRow[sfBuildspaceVOSummaryReportGenerator::ROW_OMISSION] : null;
        $addition   = $itemRow ? $itemRow[sfBuildspaceVOSummaryReportGenerator::ROW_ADDITION] : null;

        $itemId = $itemRow ? $itemRow[0] : null;

        $headerClass = null;
        $headerStyle = null;

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
            <td class="bqCounterCell">
                <?php echo $itemRow ? $itemRow[1] : '&nbsp;'?>
            </td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>

            <td class="bqRateCell">
                <?php echo ($itemRow[0] > 0) ? (! $printNoPrice && $omission && $omission != 0) ? sprintf('(%s)',number_format($omission, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1])) : '-' : null ?>
            </td>
            <td class="bqRateCell">
                <?php echo ($itemRow[0] > 0) ? (! $printNoPrice && $addition && $addition != 0) ? number_format($addition, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : '-' : null ?>
            </td>
        </tr>
            <?php unset($itemPage[$x], $amount);?>

        <?php endfor; ?>

        <?php if($printGrandTotal) : ?>
            <tr>
                <td class="footer" style="padding-right:5px;" colspan="2">
                    Total: (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount">
                    <?php echo (array_key_exists('omission', $voTotals)) ? sprintf('(%s)',number_format($voTotals['omission'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1])) : null; ?>
                </td>
                <td class="footerSumAmount">
                    <?php echo (array_key_exists('addition', $voTotals)) ? number_format($voTotals['addition'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
            </tr>
            <tr>
                <td style="padding-right:5px;" colspan="2">
                    Nett Omission/Addition: (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount" colspan="2">
                    <?php echo (array_key_exists('addition', $voTotals) && array_key_exists('addition', $voTotals)) ? number_format($voTotals['addition'] - $voTotals['omission'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
                    Page <?php echo $pageCount; ?> of <?php echo $totalPage; ?>
                </td>
            </tr>
        <?php endif;?>

        <?php if($isLastPage): ?>
            <tr>
                <td colspan="<?php echo $headerCount; ?>">
                <?php
                    include_partial('printReport/footerLayout', array(
                        'leftText' => $left_text,
                        'rightText' => $right_text
                    ));
                ?>
                </td>
            </tr>
        <?php endif; ?>
</table>
</body>
</html>

