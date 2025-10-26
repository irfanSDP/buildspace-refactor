    <?php if ( ! $printAmountOnly ): ?>
        <?php if ( $toggleColumnArrangement ): ?>
        <td class="bqQtyCell"><?php echo (! empty($itemRow[0])) ? 'RATE ONLY' : null; ?></td>
        <td class="bqUnitCell"><?php echo (! empty($itemRow[0])) ? $itemRow[5] : '&nbsp;'?></td>
        <?php else: ?>
        <td class="bqUnitCell"><?php echo (! empty($itemRow[0])) ? $itemRow[5] : '&nbsp;'?></td>
        <td class="bqQtyCell"><?php echo (! empty($itemRow[0])) ? 'RATE ONLY' : null; ?></td>
        <?php endif; ?>

        <td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? Utilities::displayScientific($rate, 10, array( 
                'decimal_places' => $priceFormatting[2],
                'decimal_points' => $priceFormatting[0],
                'thousand_separator' => $rateCommaRemove ? '' : $priceFormatting[1]
            ), $printFullDecimal) : null ?>
        </td>
    <?php endif; ?>

    <?php if ( $printDollarAndCentColumn ): ?>
    <td class="bqAmountCell" style="text-align: center;"><?php echo (! empty($itemRow[0])) ? '-' : null; ?></td>
    <td class="bqAmountCell" style="text-align: center;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo (! empty($itemRow[0])) ? '-' : null; ?></td>
    <?php else: ?>
    <td class="bqAmountCell" style="text-align: center;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo (! empty($itemRow[0])) ? '-' : null; ?></td>
    <?php endif; ?>