<?php use sfBuildspaceBQPageGenerator as PageGenerator; ?>
<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php
            $headerCount = 8;

            if($printQty)
                $headerCount++;
        ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('bqReportHeader', array('reportTitle' => $reportTitle,'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">Bill Ref</td>
        <td class="bqHeadCell" style="min-width:400px;width:400px;"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell" style="min-width:70px;width:70px;">Unit</td>
        <?php if($printQty) : ?>
            <td class="bqHeadCell" style="min-width:80px;width:80px;"><?php echo $qtyHeader; ?></td>
        <?php endif; ?>

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
                        echo "";
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
    $billColumnSettingId = $billColumnSettings[0]['id'];//single type will always return 1 bill column setting


    for($x=0; $x <= $maxRows; $x++):
        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        if ( $itemRow[4] == PageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if ( $printElementInGridOnce AND $itemRow[4] == PageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rowCount++;

        if($itemRow[4] == BillItem::TYPE_ITEM_NOT_LISTED)
        {
            $rate = $itemRow ? $itemRow[6][0] : null;
        }
        else
        {
            $rate = $itemRow ? $itemRow[6] : null;
        }

        $itemId = $itemRow ? $itemRow[0] : null;

        if($itemRow && is_array($itemRow[7]))
        {
            $quantity = 0;

            foreach($billColumnSettings as $column)
            {
                $qtyField = array_key_exists($column['id'], $itemRow[7]) ? $itemRow[7][$column['id']] : 0;
                $quantity+= ($qtyField * $column['quantity']);
            }
        }
        else
        {
            $quantity = 0;
        }

        if ($itemRow and ($itemRow[4] == BillItem::TYPE_HEADER OR $itemRow[4] == BillItem::TYPE_HEADER_N))
        {
            $headerClass = 'bqHead'.$itemRow[3];
            $headerStyle = null;
        }
        elseif($itemRow and $itemRow[4] == PageGenerator::ROW_TYPE_ELEMENT)
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

        if ( $indentItem AND $itemRow AND ($itemRow[4] != PageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER AND $itemRow[4] != BillItem::TYPE_HEADER_N) )
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
                <?php if($itemRow[4] == PageGenerator::ROW_TYPE_PC_RATE):?>
                <?php include_partial('printBQ/primeCostRateTable', array('currency'=>$currency, 'itemRow'=>$itemRow, 'priceFormatting'=> $priceFormatting, 'printNoPrice' => $printNoPrice)) ?>
                <?php else:?>
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
                <?php endif?>
            </td>
            <td class="bqUnitCell">
                <?php echo $itemRow ? $itemRow[ PageGenerator::ROW_BILL_ITEM_UNIT ] : '&nbsp;' ?>
            </td>

            <?php if ( $itemRow[4] == BillItem::TYPE_ITEM_RATE_ONLY ): ?>
                <?php include_partial('printReport/bqReportComparisonItem/itemRateOnly', array(
                    'itemRow'                  => $itemRow,
                    'rate'                     => $rate,
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

            <?php elseif ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM ): ?>
                <?php
                $amount = 0;

                if ($rate && $rate != 0)
                {
                    $amount      = $rate * 1;
                    $totalAmount += $amount;
                }

                include_partial('printReport/bqReportComparisonItem/itemLSAmt', array(
                    'itemRow'                  => $itemRow,
                    'rate'                     => $rate,
                    'amount'                   => $amount,
                    'quantity'                 => Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])),
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
                    'amtCommaRemove'           => $amtCommaRemove,
                    'printQty'                 => $printQty
                ));
            ?>

            <?php elseif ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE ): ?>
                <?php
                $amount = PageGenerator::gridCurrencyRoundingFormat($rate * 1);
                $totalAmount += $amount;

                include_partial('printReport/bqReportComparisonItem/itemLSExclude', array(
                    'itemRow'                  => $itemRow,
                    'quantity'                 => Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])),
                    'rate'                     => $rate,
                    'amount'                   => number_format($amount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]),
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

            <?php elseif ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT ): ?>
                <?php
                $amount = 0;

                if($rate && $rate != 0)
                {
                    $amount      = PageGenerator::gridCurrencyRoundingFormat($rate);
                    $totalAmount += $amount;
                }

                include_partial('printReport/bqReportComparisonItem/itemLSPercent', array(
                    'itemRow'                  => $itemRow,
                    'quantity'                 => Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])),
                    'rate'                     => $rate,
                    'amount'                   => number_format($amount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]),
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

                <?php if($printQty) : ?>
                    <td class="bqQtyCell"><?php echo $quantity && $quantity != 0 ? Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])) : null?></td>
                <?php endif ?>

                <?php if ( $itemRow[4] == PageGenerator::ROW_TYPE_PC_RATE ): ?>
                    <td class="bqRateCell">&nbsp;</td>
                <?php else: ?>
                        <td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>

                    <?php if ( $itemRow[4] == BillItem::TYPE_ITEM_NOT_LISTED ): ?>
                        <td class="bqRateCell">
                            <?php
                                $contractorRate = $itemRow ? $itemRow[6][1] : null;
                            ?>

                            <?php echo ! $printNoPrice  && $itemId > 0 && $contractorRate != 0 ? number_format($contractorRate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                        </td>
                    <?php else: ?>

                        <?php if($participate) : ?>
                            <td class="bqRateCell"><?php echo ! $printNoPrice  && $itemId > 0 && $rationalizedRates && array_key_exists($itemId, $rationalizedRates) && $rationalizedRates[$itemId] != 0 ? number_format($rationalizedRates[$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                        <?php else:?>
                            <td class="bqRateCell"><?php echo ! $printNoPrice  && $itemId > 0 && $selectedRates && array_key_exists($itemId, $selectedRates) && $selectedRates[$itemId] != 0 ? number_format($selectedRates[$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                        <?php endif; ?>
                    <?php endif;?>

                <?php endif; ?>


                <?php if ( !($itemRow[4] == BillItem::TYPE_ITEM_NOT_LISTED) ): ?>
                    <?php if($participate) : ?>
                        <td class="bqRateCell" style="text-align:center;"><?php echo ! $printNoPrice  && $itemId > 0 && $rate > 0 && $rationalizedRates && array_key_exists($itemId, $rationalizedRates) && $rationalizedRates[$itemId] != 0 ? number_format(($rationalizedRates[$itemId] - $rate) / $rate * 100, 2).' %' : null ?></td>
                    <?php else:?>
                        <td class="bqRateCell" style="text-align:center;"><?php echo ! $printNoPrice  && $itemId > 0 && $rate > 0 && $selectedRates && array_key_exists($itemId, $selectedRates) && $selectedRates[$itemId] != 0 ? number_format(($selectedRates[$itemId] - $rate) / $rate * 100, 2).' %' : null ?></td>
                    <?php endif; ?>

                    <?php if($participate) : ?>
                        <td class="bqRateCell"><?php echo ! $printNoPrice  && $itemId > 0 && $rationalizedRates && array_key_exists($itemId, $rationalizedRates) && $rationalizedRates[$itemId] != 0 ? number_format(($rationalizedRates[$itemId] - $rate), $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                    <?php else:?>
                        <td class="bqRateCell"><?php echo ! $printNoPrice  && $itemId > 0 && $selectedRates && array_key_exists($itemId, $selectedRates) && $selectedRates[$itemId] != 0 ? number_format(($selectedRates[$itemId] - $rate), $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                    <?php endif; ?>
                <?php else:?>
                    <td class="bqRateCell">&nbsp;</td>
                    <td class="bqRateCell">&nbsp;</td>
                <?php endif; ?>

            <?php endif; ?>
        </tr>
            <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>

        <?php if($printGrandTotal) : ?>
            <tr>
                <td class="footer" style="padding-right:5px;" colspan="<?php echo ($printQty) ? 4 : 3 ;?>">
                    <?php echo $elementHeaderDescription; ?> (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount">
                    <?php echo ($estimateElementTotal) ? number_format($estimateElementTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
                <?php if ( $participate ): ?>
                    <td class="footerSumAmount">
                        <?php echo ($rationalizedElementTotal) ? number_format($rationalizedElementTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                    </td>
                    <td class="footerSumAmount" style="text-align:center;">
                        <?php echo ($rationalizedElementTotal && $estimateElementTotal > 0) ? number_format(($rationalizedElementTotal - $estimateElementTotal) / $estimateElementTotal * 100, 2).' %' : null; ?>
                    </td>
                    <td class="footerSumAmount">
                        <?php echo ($rationalizedElementTotal) ? number_format($rationalizedElementTotal - $estimateElementTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                    </td>
                <?php else: ?>
                    <td class="footerSumAmount">
                        <?php echo ($selectedElementTotal) ? number_format($selectedElementTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                    </td>
                    <td class="footerSumAmount" style="text-align:center;">
                        <?php echo ($selectedElementTotal && $estimateElementTotal > 0) ? number_format(($selectedElementTotal - $estimateElementTotal) / $estimateElementTotal * 100, 2).' %' : null; ?>
                    </td>
                    <td class="footerSumAmount">
                        <?php echo ($selectedElementTotal) ? number_format($selectedElementTotal - $estimateElementTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                    </td>
                <?php endif;?>
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
    <tr>
</table>
</body>
</html>

