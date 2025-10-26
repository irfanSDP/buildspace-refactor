<?php $headerCount = 4; ?>
<?php $descriptionWidth = 400; ?>

<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('printReport/bqReportHeader', array('reportTitle' => $reportTitle, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:80px;width:80px;" rowspan="2">No</td>
        <td class="bqHeadCell" style="min-width:80px;width:<?php echo $descriptionWidth; ?>px" rowspan="2"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell" style="min-width:200px;width:200px;" colspan="2">Amount</td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Omission</td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Addition</td>
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
    $rowCount    = 0;
    $totalAmount = 0;

    for($x=0; $x <= $maxRows; $x++):

        $itemPadding  = 0;

        $itemRow      = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;
        $itemPadding  = 6;
        $headerStyle  = null;

        if ( $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if ( $printElementInGridOnce AND $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rowCount++;

        $itemId   = $itemRow ? $itemRow[0] : null;
        $omission = $itemRow ? $itemRow[sfRemeasurementTypeReportGenerator::ROW_BILL_ITEM_OMISSION] : null;
        $addition = $itemRow ? $itemRow[sfRemeasurementTypeReportGenerator::ROW_BILL_ITEM_ADDITION] : null;

        $counter  = 1;
        $quantity = 0;

        if ( $indentItem AND $itemRow AND ($itemRow[4] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER AND $itemRow[4] != BillItem::TYPE_HEADER_N) )
        {
            $itemPadding = 15;
        }
        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php if($itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_PC_RATE):?>
                <?php include_partial('printBQ/bqReportItem/primeCostRateTable', array('currency'=>$currency, 'itemRow'=>$itemRow, 'priceFormatting'=> $priceFormatting, 'printNoPrice' => $printNoPrice)) ?>
                <?php else:?>
                <?php $preClass = 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
                <?php endif?>
            </td>

            <td class="bqRateCell"><?php echo ! $printNoPrice && $omission && $omission != 0 ? '('.number_format($omission, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]).')' : null ?></td>
            <td class="bqRateCell"><?php echo ! $printNoPrice && $addition && $addition != 0 ? number_format($addition, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
        </tr>
        <?php unset($itemPage[$x], $amount);?>
    <?php endfor; ?>

    <?php if($lastPage) : ?>
        <tr>
            <td class="footer" style="padding-right:5px;" colspan=2>
                Total (<?php echo $currency; ?>) :
            </td>
            <td class="footerSumAmount">
                <?php echo ($totalOmission > 0) ? '('.number_format($totalOmission, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]).')' : NULL; ?>
            </td>
            <td class="footerSumAmount">
                <?php echo ($totalAddition > 0) ? number_format($totalAddition, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : NULL; ?>
            </td>
        </tr>
        <tr>
            <td style="padding-right:5px;" colspan=2>
                Nett Addition / Omission (<?php echo $currency; ?>) :
            </td>
            <td class="footerSumAmount" colspan="2">
                <?php $nett = $totalAddition - $totalOmission;  echo ($nett != 0) ? number_format($nett, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : NULL; ?>
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
    <?php endif;?>
</table>
</body>
</html>