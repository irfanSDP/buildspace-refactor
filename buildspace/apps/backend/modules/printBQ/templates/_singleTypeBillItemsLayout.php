<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php if ( ! $printAmountOnly ): ?>
            <?php if ( $printDollarAndCentColumn ): ?>
            <td colspan="7">
            <?php else: ?>
            <td colspan="6">
            <?php endif; ?>
        <?php else: ?>
            <?php if ( $printDollarAndCentColumn ): ?>
            <td colspan="4">
            <?php else: ?>
            <td colspan="3">
            <?php endif; ?>
        <?php endif; ?>
            <?php include_partial('singleTypeHeader', array('printAmountOnly' => $printAmountOnly, 'topLeftRow1' => $topLeftRow1, 'topRightRow1' => $topRightRow1, 'topLeftRow2' => $topLeftRow2, 'elementHeaderDescription' => $elementHeaderDescription, 'printElementTitle' => $printElementTitle, 'printDollarAndCentColumn' => $printDollarAndCentColumn)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:35px;width:35px;">Item</td>

        <?php if ( ! $printAmountOnly ): ?>
            <td class="bqHeadCell" style="min-width:320px;width:320px;"><?php echo $descHeader; ?></td>

            <?php if ( $toggleColumnArrangement ): ?>
            <td class="bqHeadCell" style="min-width:70px;width:70px;"><?php echo $qtyHeader; ?></td>
            <td class="bqHeadCell" style="min-width:50px;width:50px;"><?php echo $unitHeader; ?></td>
            <?php else: ?>
            <td class="bqHeadCell" style="min-width:50px;width:50px;"><?php echo $unitHeader; ?></td>
            <td class="bqHeadCell" style="min-width:70px;width:70px;"><?php echo $qtyHeader; ?></td>
            <?php endif; ?>

            <td class="bqHeadCell" style="min-width:80px;width:80px;"><?php echo $rateHeader; ?></td>
        <?php else: ?>
            <td class="bqHeadCell" style="min-width:455px;width:455px;"><?php echo $descHeader; ?></td>
        <?php endif; ?>

        <?php if ( ! $printAmountOnly ): ?>
            <?php if ( $printDollarAndCentColumn ): ?>
            <td class="bqHeadCell" style="min-width:90px;width:90px;"><?php echo $currencyFormat[0]; ?></td>
            <td class="bqHeadCell" style="min-width:25px;width:25px;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $currencyFormat[1]; ?></td>
            <?php else: ?>
            <td class="bqHeadCell" style="min-width:115px;width:115px;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $amtHeader; ?></td>
            <?php endif; ?>
        <?php else: ?>
            <?php if ( $printDollarAndCentColumn ): ?>
            <td class="bqHeadCell" style="min-width:140px;width:140px;"><?php echo $currencyFormat[0]; ?></td>
            <td class="bqHeadCell" style="min-width:40px;width:40px;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $currencyFormat[1]; ?></td>
            <?php else: ?>
            <td class="bqHeadCell" style="min-width:180px;width:180px;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $amtHeader; ?></td>
            <?php endif; ?>
        <?php endif; ?>
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

    for($x=0; $x < $maxRows; $x++):
        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        if ( $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT and ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if ( $printElementInGridOnce and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT and $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rowCount++;
        $rate = $itemRow ? $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_RATE] : null;

        if($printGrandTotalQty)
        {
            $quantity = ($itemRow and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_ID] > 0) ? $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT] : 0;
        }
        else
        {
            $quantity = $itemRow && is_array($itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT]) && array_key_exists($billColumnSettingId, $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT]) ? $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT][$billColumnSettingId] : 0;
        }

        if ($itemRow and ($itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_HEADER OR $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_HEADER_N))
        {
            $headerClass = 'bqHead'.$itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_LEVEL];
        }
        elseif($itemRow and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT)
        {
            $headerClass = 'elementHeader';

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
        }

        if ( $indentItem and $itemRow and ($itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] != BillItem::TYPE_HEADER and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] != BillItem::TYPE_HEADER_N) )
        {
            $itemPadding = 15;
        }
        else
        {
            $itemPadding = 6;
        }
        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_ROW_IDX] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php if($itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == sfBuildspaceBQPageGenerator::ROW_TYPE_PC_RATE):?>
                <?php include_partial('printBQ/primeCostRateTable', array('printAmountOnly' => $printAmountOnly, 'currency'=>$currency, 'itemRow'=>$itemRow, 'priceFormatting'=> $priceFormatting, 'printNoPrice' => $printNoPrice, 'printFullDecimal' => $printFullDecimal, 'rateCommaRemove' => $rateCommaRemove )) ?>
                <?php else:?>
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'">'.trim(preg_replace('/\s+/', ' ', $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_DESCRIPTION])).'</pre>' : null?>
                <?php endif?>
            </td>

            <?php if ( $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_RATE_ONLY ): ?>
                <?php include_partial('printBQ/singleItemTypes/singleTypeItemRateOnly', array(
                    'itemRow'                  => $itemRow,
                    'rate'                     => $rate,
                    'priceFormatting'          => $priceFormatting,
                    'printNoPrice'             => $printNoPrice,
                    'printFullDecimal'         => $printFullDecimal,
                    'toggleColumnArrangement'  => $toggleColumnArrangement,
                    'printDollarAndCentColumn' => $printDollarAndCentColumn,
                    'printAmountOnly'          => $printAmountOnly,
                    'rateCommaRemove'          => $rateCommaRemove,
                    'closeGrid'                => $closeGrid,
                ));
            ?>

            <?php elseif ( $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM ): ?>
                <?php
                $amount = 0;

                if ($rate && $rate != 0)
                {
                    $amount = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($rate * 1);
                    $totalAmount += $amount;
                }

                include_partial('printBQ/singleItemTypes/singleTypeItemLSAmt', array(
                    'itemRow'                  => $itemRow,
                    'rate'                     => 0,
                    'amount'                   => $amount,
                    'priceFormatting'          => $priceFormatting,
                    'printNoPrice'             => $printNoPrice,
                    'printFullDecimal'         => $printFullDecimal,
                    'toggleColumnArrangement'  => $toggleColumnArrangement,
                    'printDollarAndCentColumn' => $printDollarAndCentColumn,
                    'printAmountOnly'          => $printAmountOnly,
                    'amtCommaRemove'           => $amtCommaRemove,
                    'closeGrid'                => $closeGrid,
                ));
            ?>

            <?php elseif ( $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE ): ?>
                <?php
                $amount = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($rate * 1);
                $totalAmount += $amount;

                include_partial('printBQ/singleItemTypes/singleTypeItemLSExclude', array(
                    'itemRow'                  => $itemRow,
                    'quantity'                 => Utilities::number_clean($quantity),
                    'amount'                   => $amount,
                    'priceFormatting'          => $priceFormatting,
                    'printNoPrice'             => $printNoPrice,
                    'printFullDecimal'         => $printFullDecimal,
                    'toggleColumnArrangement'  => $toggleColumnArrangement,
                    'printDollarAndCentColumn' => $printDollarAndCentColumn,
                    'printAmountOnly'          => $printAmountOnly,
                    'amtCommaRemove'           => $amtCommaRemove,
                    'closeGrid'                => $closeGrid,
                ));
            ?>

            <?php elseif ( $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT ): ?>
                <?php
                $amount = 0;

                if($rate && $rate != 0)
                {
                    $amount      = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($rate);
                    $totalAmount += $amount;
                }

                include_partial('printBQ/singleItemTypes/singleTypeItemLSPercent', array(
                    'itemRow'                  => $itemRow,
                    'quantity'                 => Utilities::number_clean($quantity),
                    'amount'                   => $amount,
                    'priceFormatting'          => $priceFormatting,
                    'printNoPrice'             => $printNoPrice,
                    'printFullDecimal'         => $printFullDecimal,
                    'toggleColumnArrangement'  => $toggleColumnArrangement,
                    'printDollarAndCentColumn' => $printDollarAndCentColumn,
                    'printAmountOnly'          => $printAmountOnly,
                    'amtCommaRemove'           => $amtCommaRemove,
                    'closeGrid'                => $closeGrid,
                ));
            ?>

            <?php else: ?>

                <?php if ( ! $printAmountOnly ): ?>
                    <?php if ( $toggleColumnArrangement ): ?>
                    <td class="bqQtyCell">
                        <?php echo $quantity && $quantity != 0 ? Utilities::number_clean($quantity, array(
                            'decimal_points'     => $priceFormatting[0],
                            'thousand_separator' => $qtyCommaRemove ? '' : $priceFormatting[1]
                        )) : null; ?>
                    </td>
                    <td class="bqUnitCell"><?php echo $itemRow ? $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_UNIT] : '&nbsp;'?></td>
                    <?php else: ?>
                    <td class="bqUnitCell"><?php echo $itemRow ? $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_UNIT] : '&nbsp;'?></td>
                    <td class="bqQtyCell">
                        <?php echo $quantity && $quantity != 0 ? Utilities::number_clean($quantity, array(
                            'decimal_points'     => $priceFormatting[0],
                            'thousand_separator' => $qtyCommaRemove ? '' : $priceFormatting[1],
                            'display_scientific' => array(
                                'displayFull' => $printFullDecimal,
                                'charLength' => 6
                            )
                        )) : null; ?>
                    </td>
                    <?php endif; ?>

                    <?php if ( $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == sfBuildspaceBQPageGenerator::ROW_TYPE_PC_RATE ): ?>
                    <td class="bqRateCell">&nbsp;</td>
                    <?php else: ?>
                    <td class="bqRateCell">
                        <?php echo ! $printNoPrice && $rate && $rate != 0 ?  Utilities::displayScientific($rate, 9, array( 
                            'decimal_places' => $priceFormatting[2],
                            'decimal_points' => $priceFormatting[0],
                            'thousand_separator' => $rateCommaRemove ? '' : $priceFormatting[1]
                        ), $printFullDecimal) : null ?>
                    </td>
                    <?php endif; ?>
                <?php endif; ?>

                <?php
                if($rate && $rate != 0 && $quantity && $quantity != 0)
                {
                    $amount      = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($rate * $quantity);
                    $totalAmount += $amount;

                    if($printDollarAndCentColumn)
                    {
                        $amount = number_format($amount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]);
                    }
                    else
                    {
                        $amount = Utilities::displayScientific($amount, ($printAmountOnly) ? 18 : 12, array( 
                            'decimal_places' => $priceFormatting[2],
                            'decimal_points' => $priceFormatting[0],
                            'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
                        ), $printFullDecimal);
                    }
                }
                ?>

                <?php if ( $printDollarAndCentColumn ): $amount = (isset($amount) && ! is_array($amount)) ? explode($priceFormatting[0], $amount) : null; ?>
                <td class="bqAmountCell"><?php echo ! $printNoPrice && isset($amount[0]) ? Utilities::displayScientific($amount[0], 10, array( 
                        'decimal_places' => 0,
                        'decimal_points' => $priceFormatting[0],
                        'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
                    ), $printFullDecimal) : null; ?>
                </td>
                <td class="bqAmountCell" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo ! $printNoPrice && isset($amount[1]) ? $amount[1] : null; ?></td>
                <?php else: ?>
                <td class="bqAmountCell" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
                    <?php echo ! $printNoPrice && isset($amount) ? $amount : null; 
                    ?>
                </td>
                <?php endif; ?>

            <?php endif; ?>
        </tr>
            <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>
    <tr>
        <td class="bqCounterCell">&nbsp;</td>
        <td class="bqDescriptionCell"></td>

        <?php if ( ! $printAmountOnly ): ?>
            <td class="bqUnitCell"></td>
            <td class="bqQtyCell"></td>
            <td class="bqRateCell"></td>
        <?php endif; ?>

        <?php if ( $printDollarAndCentColumn ): ?>
        <td class="bqAmountCell"></td>
        <td class="bqAmountCell" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"></td>
        <?php else: ?>
        <td class="bqAmountCell" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"></td>
        <?php endif; ?>
    </tr>
    <tr>
        <?php if ( ! $printAmountOnly ): ?>
            <td class="footer" style="padding-right:5px;" colspan="5"><?php echo $toCollection; ?> (<?php echo $currency->currency_code; ?>)</td>
        <?php else: ?>
            <td class="footer" colspan="2" style="padding-right:5px;"><?php echo $toCollection; ?> (<?php echo $currency->currency_code; ?>)</td>
        <?php endif; ?>

        <?php 

            $totalAmount = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($totalAmount); 

            if($printDollarAndCentColumn)
            {
                $totalAmount = number_format($totalAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]);
            }
            else
            {
                $totalAmount = Utilities::displayScientific($totalAmount, ($printAmountOnly) ? 20 : 11, array( 
                    'decimal_places' => $priceFormatting[2],
                    'decimal_points' => $priceFormatting[0],
                    'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
                ), $printFullDecimal); 
            }
        ?>

        <?php if ( $printDollarAndCentColumn ): $totalAmount = explode($priceFormatting[0], $totalAmount); ?>
        <td class="footerSumAmount">
            <?php echo ! $printNoPrice ? Utilities::displayScientific($totalAmount[0], 10, array( 
                'decimal_places' => 0,
                'decimal_points' => $priceFormatting[0],
                'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
            ), $printFullDecimal) : null; ?>
        </td>
        <td class="footerSumAmount" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo ! $printNoPrice && isset($totalAmount[1]) ? $totalAmount[1] : null; ?></td>
        <?php else: ?>
        <td class="footerSumAmount" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo ! $printNoPrice ? $totalAmount : null; ?></td>
        <?php endif; ?>
    </tr>
    <tr>
        <?php if ( ! $printAmountOnly ): ?>
            <?php if ( $printDollarAndCentColumn ): ?>
            <td colspan="7">
            <?php else: ?>
            <td colspan="6">
            <?php endif; ?>
        <?php else: ?>
            <?php if ( $printDollarAndCentColumn ): ?>
            <td colspan="4">
            <?php else: ?>
            <td colspan="3">
            <?php endif; ?>
        <?php endif; ?>
            <table cellpadding="0" cellspacing="0" class="footer-table" style="width: 100%;">
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo (strlen($botLeftRow1) > 32) ? substr($botLeftRow1,0,32).'...' : $botLeftRow1; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">&nbsp;</td>
                    <td style="width: 40%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo (strlen($botLeftRow2) > 32) ? substr($botLeftRow2,0,32).'...' : $botLeftRow2; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">
                        <?php echo trim("{$pageNoPrefix}{$elementCount}/{$pageCount}"); ?>
                    </td>
                    <td style="width: 40%; text-align: right;"><?php echo $printDateOfPrinting ? '<span style="font-weight:bold;">Date of Printing: </span><span style="font-style: italic;">'.date('d/M/Y').'</span>' : '&nbsp;'; ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>